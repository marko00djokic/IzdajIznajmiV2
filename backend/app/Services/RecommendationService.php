<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingEvent;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RecommendationService
{
    private const MAX_RECOMMENDATIONS = 30;

    private const MAX_VIEW_EVENTS = 20;

    private const MAX_SAVED_SEARCHES = 10;

    private const MAX_SIMILAR_SEEDS = 3;

    private const MAX_SIMILAR_PER_SEED = 4;

    private const MAX_SAVED_SEARCH_RESULTS = 5;

    private const MAX_RECENT_SEARCH_RESULTS = 5;

    private const MAX_FRESH_RESULTS = 8;

    private const CACHE_MINUTES = 7;

    public function __construct(
        private readonly ListingSearchService $searchService,
        private readonly SimilarListingsService $similarService,
        private readonly SearchFilterSnapshotService $snapshotService,
        private readonly LegacyRecommendationScorer $legacyScorer,
    ) {}

    /**
     * @return array{items: Collection<int, Listing>, reasons: array<string, array<int, string>>, meta: array<string, int>}
     */
    public function recommendFor(User $user, array $filters, int $page, int $perPage): array
    {
        $cacheKey = $this->cacheKey($user->id, $filters);

        $payload = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_MINUTES), function () use ($user, $filters) {
            return $this->buildPayload($user, $filters);
        });

        $ids = $payload['ids'] ?? [];
        $reasons = $payload['reasons'] ?? [];
        $totalEstimate = count($ids);

        $offset = max($page - 1, 0) * $perPage;
        $pageIds = array_slice($ids, $offset, $perPage);

        if (empty($pageIds)) {
            return [
                'items' => collect(),
                'reasons' => [],
                'meta' => [
                    'page' => $page,
                    'perPage' => $perPage,
                    'total_estimate' => $totalEstimate,
                ],
            ];
        }

        $items = $this->baseListingQuery()
            ->whereIn('id', $pageIds)
            ->get()
            ->sortBy(fn (Listing $listing) => array_search($listing->id, $pageIds, true))
            ->values();

        $pageReasons = array_intersect_key($reasons, array_flip($pageIds));
        foreach ($items as $listing) {
            $key = (string) $listing->id;
            if (isset($pageReasons[$key]) && ! empty($pageReasons[$key])) {
                $listing->setAttribute('why', $pageReasons[$key]);
            }
        }

        return [
            'items' => $items,
            'reasons' => $pageReasons,
            'meta' => [
                'page' => $page,
                'perPage' => $perPage,
                'total_estimate' => $totalEstimate,
            ],
        ];
    }

    /**
     * @return array{ids: array<int, int>, reasons: array<string, array<int, string>>}
     */
    private function buildPayload(User $user, array $filters): array
    {
        $viewEvents = ListingEvent::query()
            ->where('user_id', $user->id)
            ->where('event_type', ListingEvent::TYPE_VIEW)
            ->latest('created_at')
            ->limit(self::MAX_VIEW_EVENTS)
            ->get();

        $viewedListingIds = $viewEvents->pluck('listing_id')->unique()->values()->all();

        $viewedListings = Listing::query()
            ->with('facilities:id,name')
            ->whereIn('id', $viewedListingIds)
            ->get()
            ->keyBy('id');

        $savedSearches = SavedSearch::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit(self::MAX_SAVED_SEARCHES)
            ->get();

        $recentSnapshots = $this->snapshotService->recent($user, 5);

        $profile = $this->buildProfile($viewedListings->values(), $savedSearches, $recentSnapshots);

        $candidates = [];
        $sourceTags = [];
        $sourceReasons = [];

        $seedListings = $viewEvents
            ->map(fn ($event) => $viewedListings->get($event->listing_id))
            ->filter()
            ->unique('id')
            ->take(self::MAX_SIMILAR_SEEDS);

        foreach ($seedListings as $seed) {
            $similar = $this->similarService->similarTo($seed, self::MAX_SIMILAR_PER_SEED);
            foreach ($similar as $candidate) {
                $this->addCandidate($candidates, $sourceTags, $sourceReasons, $candidate, 'similar_view', [
                    'Because you viewed '.$seed->title,
                ]);
            }
        }

        foreach ($savedSearches as $search) {
            $results = $this->listingsForFilters($search->filters ?? [], self::MAX_SAVED_SEARCH_RESULTS);
            $reason = $search->name ? 'Matches your saved search: '.$search->name : 'Matches your saved search';
            foreach ($results as $candidate) {
                $this->addCandidate($candidates, $sourceTags, $sourceReasons, $candidate, 'saved_search', [$reason]);
            }
        }

        foreach ($recentSnapshots as $snapshot) {
            $results = $this->listingsForFilters($snapshot->filters ?? [], self::MAX_RECENT_SEARCH_RESULTS);
            foreach ($results as $candidate) {
                $this->addCandidate($candidates, $sourceTags, $sourceReasons, $candidate, 'recent_search', [
                    'Matches your recent search',
                ]);
            }
        }

        $topCity = $profile['cities'][0]['city'] ?? null;
        if ($topCity) {
            $freshListings = $this->freshListingsInCity($topCity, $filters, self::MAX_FRESH_RESULTS);
            foreach ($freshListings as $candidate) {
                $this->addCandidate($candidates, $sourceTags, $sourceReasons, $candidate, 'fresh', [
                    'New in '.$topCity,
                ]);
            }
        }

        if (empty($candidates)) {
            $fallback = $this->fallbackListings($filters, self::MAX_RECOMMENDATIONS);
            foreach ($fallback as $candidate) {
                $this->addCandidate($candidates, $sourceTags, $sourceReasons, $candidate, 'fallback', []);
            }
        }

        $filtered = collect($candidates)
            ->filter(fn (Listing $listing) => $this->matchesRequestFilters($listing, $filters))
            ->filter(fn (Listing $listing) => (int) $listing->owner_id !== (int) $user->id)
            ->values();

        $scored = $filtered->map(function (Listing $listing) use ($profile, $sourceTags) {
            $score = $this->scoreListing($listing, $profile, $sourceTags[$listing->id] ?? []);
            $listing->setAttribute('recommendation_score', $score);

            return $listing;
        })->sortByDesc(fn (Listing $listing) => $listing->getAttribute('recommendation_score') ?? 0);

        $final = $scored->take(self::MAX_RECOMMENDATIONS)->values();
        $reasons = [];

        foreach ($final as $listing) {
            $listingId = (int) $listing->id;
            $reasonList = $this->buildReasons(
                $listing,
                $profile,
                $sourceReasons[$listingId] ?? []
            );
            $reasons[(string) $listingId] = $reasonList;
            if (! empty($reasonList)) {
                $listing->setAttribute('why', $reasonList);
            }
        }

        $ids = $final->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        return [
            'ids' => $ids,
            'reasons' => $reasons,
        ];
    }

    /**
     * @param  array<int, Listing>  $candidates
     * @param  array<int, array<int, string>>  $sourceTags
     * @param  array<int, array<int, string>>  $sourceReasons
     */
    private function addCandidate(array &$candidates, array &$sourceTags, array &$sourceReasons, Listing $listing, string $tag, array $reasons): void
    {
        $listingId = (int) $listing->id;
        $candidates[$listingId] = $listing;
        $sourceTags[$listingId] = array_values(array_unique(array_merge($sourceTags[$listingId] ?? [], [$tag])));
        if (! empty($reasons)) {
            $sourceReasons[$listingId] = array_values(array_unique(array_merge($sourceReasons[$listingId] ?? [], $reasons)));
        }
    }

    private function baseListingQuery()
    {
        return $this->searchService->baseQuery()
            ->where('status', ListingStatusService::STATUS_ACTIVE);
    }

    private function listingsForFilters(array $filters, int $limit): Collection
    {
        $filters = $this->sanitizeFilters($filters);
        $query = $this->baseListingQuery();
        $this->searchService->applyFilters($query, $filters, false);

        return $query->limit($limit)->get();
    }

    private function freshListingsInCity(string $city, array $filters, int $limit): Collection
    {
        $filters = $this->sanitizeFilters($filters);
        $query = $this->baseListingQuery();
        $this->searchService->applyFilters($query, $filters, false);
        $query->where('city', $city)->latest('created_at');

        return $query->limit($limit)->get();
    }

    private function fallbackListings(array $filters, int $limit): Collection
    {
        $filters = $this->sanitizeFilters($filters);
        $query = $this->baseListingQuery();
        $this->searchService->applyFilters($query, $filters, false);

        return $query->latest('created_at')->limit($limit)->get();
    }

    private function scoreListing(Listing $listing, array $profile, array $sourceTags): float
    {
        return $this->legacyScorer->score([
            'city' => $listing->city,
            'price' => $listing->price_per_month,
            'rooms' => $listing->rooms,
            'beds' => $listing->beds,
            'area' => $listing->area,
            'facilities' => $listing->facilities?->pluck('name')->all() ?? [],
            'created_at' => $listing->created_at,
        ], $profile, $sourceTags, now());
    }

    private function buildReasons(Listing $listing, array $profile, array $sourceReasons): array
    {
        $reasons = $sourceReasons;

        $city = $listing->city;
        if ($city && collect($profile['cities'])->first(fn ($item) => $item['city'] === $city)) {
            $reasons[] = 'Popular in '.$city;
        }

        $price = $listing->price_per_month;
        $priceMin = $profile['priceMin'] ?? null;
        $priceMax = $profile['priceMax'] ?? null;
        if ($price !== null && $priceMin !== null && $priceMax !== null && $price >= $priceMin && $price <= $priceMax) {
            $reasons[] = 'Fits your budget';
        }

        $amenities = $profile['amenities'] ?? [];
        if (! empty($amenities)) {
            $listingAmenities = $listing->facilities?->pluck('name')->all() ?? [];
            $overlap = array_intersect($amenities, $listingAmenities);
            if (! empty($overlap)) {
                $reasons[] = 'Has amenities you like';
            }
        }

        $roomsTarget = $profile['rooms'] ?? null;
        $rooms = $listing->rooms ?? $listing->beds;
        if ($roomsTarget !== null && $rooms !== null && abs($rooms - $roomsTarget) <= 1) {
            $reasons[] = 'Similar size';
        }

        return array_slice(array_values(array_unique($reasons)), 0, 3);
    }

    private function matchesRequestFilters(Listing $listing, array $filters): bool
    {
        if (! empty($filters['category']) && $filters['category'] !== 'all') {
            if ($listing->category !== $filters['category']) {
                return false;
            }
        }

        if (! empty($filters['city']) && $listing->city !== $filters['city']) {
            return false;
        }

        if (! empty($filters['instantBook']) && ! $listing->instant_book) {
            return false;
        }

        if ($this->hasValue($filters['rating'] ?? null) && $listing->rating < (float) $filters['rating']) {
            return false;
        }

        $price = $listing->price_per_month;
        if ($this->hasValue($filters['priceMin'] ?? null) && $price !== null && $price < (int) $filters['priceMin']) {
            return false;
        }
        if ($this->hasValue($filters['priceMax'] ?? null) && $price !== null && $price > (int) $filters['priceMax']) {
            return false;
        }

        if ($this->hasValue($filters['guests'] ?? null) && $listing->beds < (int) $filters['guests']) {
            return false;
        }

        $roomsTarget = $filters['rooms'] ?? null;
        $rooms = $listing->rooms ?? $listing->beds;
        if ($this->hasValue($roomsTarget) && $rooms !== null && $rooms < (int) $roomsTarget) {
            return false;
        }

        $area = $listing->area;
        if ($this->hasValue($filters['areaMin'] ?? null) && $area !== null && $area < (int) $filters['areaMin']) {
            return false;
        }
        if ($this->hasValue($filters['areaMax'] ?? null) && $area !== null && $area > (int) $filters['areaMax']) {
            return false;
        }

        $amenities = $filters['amenities'] ?? $filters['facilities'] ?? [];
        if (! empty($amenities)) {
            $listingAmenities = $listing->facilities?->pluck('name')->all() ?? [];
            foreach ((array) $amenities as $amenity) {
                if (! in_array($amenity, $listingAmenities, true)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function buildProfile(Collection $viewedListings, Collection $savedSearches, Collection $snapshots): array
    {
        $cityCounts = [];
        $prices = [];
        $priceMins = [];
        $priceMaxes = [];
        $rooms = [];
        $areas = [];
        $amenityCounts = [];

        foreach ($viewedListings as $listing) {
            if ($listing->city) {
                $cityCounts[$listing->city] = ($cityCounts[$listing->city] ?? 0) + 1;
            }
            if ($listing->price_per_month) {
                $prices[] = (float) $listing->price_per_month;
            }
            $roomsValue = $listing->rooms ?? $listing->beds;
            if ($roomsValue) {
                $rooms[] = (int) $roomsValue;
            }
            if ($listing->area) {
                $areas[] = (float) $listing->area;
            }
            foreach ($listing->facilities?->pluck('name')->all() ?? [] as $facility) {
                $amenityCounts[$facility] = ($amenityCounts[$facility] ?? 0) + 1;
            }
        }

        foreach ($savedSearches as $search) {
            $this->ingestFilterSignals($search->filters ?? [], $cityCounts, $priceMins, $priceMaxes, $rooms, $areas, $amenityCounts);
        }

        foreach ($snapshots as $snapshot) {
            $this->ingestFilterSignals($snapshot->filters ?? [], $cityCounts, $priceMins, $priceMaxes, $rooms, $areas, $amenityCounts);
        }

        $medianPrice = $this->median($prices);
        $priceMin = $this->median($priceMins) ?? ($medianPrice ? $medianPrice * 0.8 : null);
        $priceMax = $this->median($priceMaxes) ?? ($medianPrice ? $medianPrice * 1.2 : null);
        if ($priceMin !== null && $priceMax !== null && $priceMin > $priceMax) {
            [$priceMin, $priceMax] = [$priceMax, $priceMin];
        }

        $medianArea = $this->median($areas);
        $areaMin = $medianArea ? $medianArea * 0.8 : null;
        $areaMax = $medianArea ? $medianArea * 1.2 : null;
        if ($areaMin !== null && $areaMax !== null && $areaMin > $areaMax) {
            [$areaMin, $areaMax] = [$areaMax, $areaMin];
        }

        $topCities = collect($cityCounts)
            ->map(fn ($count, $city) => ['city' => $city, 'count' => $count])
            ->sortByDesc('count')
            ->values()
            ->take(3)
            ->all();

        $topAmenities = collect($amenityCounts)
            ->map(fn ($count, $amenity) => ['amenity' => $amenity, 'count' => $count])
            ->sortByDesc('count')
            ->values()
            ->take(5)
            ->pluck('amenity')
            ->all();

        return [
            'cities' => $topCities,
            'priceMin' => $priceMin ? round($priceMin, 2) : null,
            'priceMax' => $priceMax ? round($priceMax, 2) : null,
            'rooms' => $this->median($rooms),
            'areaMin' => $areaMin ? round($areaMin, 2) : null,
            'areaMax' => $areaMax ? round($areaMax, 2) : null,
            'amenities' => $topAmenities,
        ];
    }

    private function ingestFilterSignals(
        array $filters,
        array &$cityCounts,
        array &$priceMins,
        array &$priceMaxes,
        array &$rooms,
        array &$areas,
        array &$amenityCounts
    ): void {
        $city = $filters['city'] ?? $filters['location'] ?? $filters['q'] ?? null;
        if (is_string($city) && $city !== '') {
            $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
        }

        if (isset($filters['priceMin']) && is_numeric($filters['priceMin'])) {
            $priceMins[] = (float) $filters['priceMin'];
        }
        if (isset($filters['priceMax']) && is_numeric($filters['priceMax'])) {
            $priceMaxes[] = (float) $filters['priceMax'];
        }
        if (isset($filters['rooms']) && is_numeric($filters['rooms'])) {
            $rooms[] = (int) $filters['rooms'];
        }
        if (isset($filters['areaMin']) && is_numeric($filters['areaMin'])) {
            $areas[] = (float) $filters['areaMin'];
        }
        if (isset($filters['areaMax']) && is_numeric($filters['areaMax'])) {
            $areas[] = (float) $filters['areaMax'];
        }

        $amenities = $filters['amenities'] ?? $filters['facilities'] ?? [];
        if (! empty($amenities)) {
            foreach ((array) $amenities as $amenity) {
                if (! is_string($amenity) || $amenity === '') {
                    continue;
                }
                $amenityCounts[$amenity] = ($amenityCounts[$amenity] ?? 0) + 1;
            }
        }
    }

    private function median(array $values): ?float
    {
        $values = array_values(array_filter($values, fn ($value) => $value !== null));
        if (empty($values)) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $count = count($values);
        $middle = (int) floor(($count - 1) / 2);
        if ($count % 2) {
            return (float) $values[$middle];
        }

        return ((float) $values[$middle] + (float) $values[$middle + 1]) / 2;
    }

    private function hasValue(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    private function cacheKey(int $userId, array $filters): string
    {
        return 'recommendations:user:'.$userId.':'.md5(json_encode($filters));
    }

    private function sanitizeFilters(array $filters): array
    {
        if (array_key_exists('status', $filters)) {
            unset($filters['status']);
        }

        return $filters;
    }
}
