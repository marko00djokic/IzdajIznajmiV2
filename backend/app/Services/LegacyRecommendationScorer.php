<?php

namespace App\Services;

use Carbon\CarbonInterface;

class LegacyRecommendationScorer
{
    public function score(array $listing, array $profile, array $sourceTags, CarbonInterface $asOf): float
    {
        $score = 0.0;

        $city = $listing['city'] ?? null;
        if ($city) {
            $topCities = collect($profile['cities']);
            $index = $topCities->search(fn ($item) => $item['city'] === $city);
            if ($index === 0) {
                $score += 30;
            } elseif ($index !== false) {
                $score += 15;
            }
        }

        $price = $listing['price'] ?? null;
        $priceMin = $profile['priceMin'] ?? null;
        $priceMax = $profile['priceMax'] ?? null;
        if ($price !== null && $priceMin !== null && $priceMax !== null) {
            if ($price >= $priceMin && $price <= $priceMax) {
                $score += 20;
            } else {
                $mid = ($priceMin + $priceMax) / 2;
                if ($mid > 0 && abs($price - $mid) / $mid <= 0.2) {
                    $score += 10;
                }
            }
        }

        $roomsTarget = $profile['rooms'] ?? null;
        $rooms = $listing['rooms'] ?? $listing['beds'] ?? null;
        if ($roomsTarget !== null && $rooms !== null && abs($rooms - $roomsTarget) <= 1) {
            $score += 10;
        }

        $area = $listing['area'] ?? null;
        $areaMin = $profile['areaMin'] ?? null;
        $areaMax = $profile['areaMax'] ?? null;
        if ($area !== null && $areaMin !== null && $areaMax !== null && $area >= $areaMin && $area <= $areaMax) {
            $score += 10;
        }

        $amenities = $profile['amenities'] ?? [];
        if (! empty($amenities)) {
            $overlap = array_intersect($amenities, $listing['facilities'] ?? []);
            if (! empty($overlap)) {
                $score += 15 * (count($overlap) / max(count($amenities), 1));
            }
        }

        $createdAt = $listing['created_at'] ?? null;
        if ($createdAt instanceof CarbonInterface && $createdAt->greaterThan($asOf->copy()->subDays(14))) {
            $score += 5;
        }

        foreach ($sourceTags as $tag) {
            $score += match ($tag) {
                'similar_view' => 12,
                'saved_search' => 10,
                'recent_search' => 6,
                'fresh' => 5,
                default => 0,
            };
        }

        return $score;
    }
}
