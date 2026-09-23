<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class PirOfflineEvaluator
{
    public const CRITERIA = ['price', 'zone', 'area', 'rooms', 'facilities'];

    public const MODEL_VERSION = 'pir-hybrid-v1';

    public function __construct(private readonly LegacyRecommendationScorer $legacyScorer) {}

    public function evaluate(array $dataset, array $scenarios): array
    {
        $this->validateDataset($dataset);
        $this->require(($scenarios['scenario_version'] ?? null) === 'v1', 'Invalid scenario_version');
        $this->require(($scenarios['model_version'] ?? null) === self::MODEL_VERSION, 'Invalid model_version');
        $this->require(($scenarios['lambda_primary'] ?? null) === 0.25, 'Primary lambda must be 0.25');
        $this->require(($scenarios['lambda_sensitivity'] ?? null) === [0, 0.5], 'Sensitivity lambdas must be [0, 0.5]');
        $this->require(count($scenarios['scenarios'] ?? []) === 2, 'Expected S1 and S2');
        $ids = array_column($scenarios['scenarios'], 'id');
        $this->require($ids === ['S1', 'S2'], 'Scenario IDs must be S1 and S2');
        foreach ($scenarios['scenarios'] as $scenario) {
            $this->validateScenario($scenario);
        }

        $results = [];
        foreach ($scenarios['scenarios'] as $scenario) {
            $candidates = array_values(array_filter($dataset['listings'], fn (array $row) => $this->passesHardFilters($row, $scenario['hard_filters'])));
            $this->require(count($candidates) >= 12 && count($candidates) <= 18, $scenario['id'].' needs 12–18 candidates');
            $asOf = CarbonImmutable::parse($scenario['as_of']);
            $baseline = $candidates;
            usort($baseline, fn ($a, $b) => strcmp($b['published_at'], $a['published_at']) ?: strcmp($a['listing_code'], $b['listing_code']));
            $legacy = array_map(fn ($row) => [
                'listing_code' => $row['listing_code'],
                'score' => $this->legacyScorer->score([
                    'city' => $row['city'], 'price' => $row['price_monthly_eur'], 'rooms' => $row['rooms'],
                    'area' => $row['area_m2'], 'facilities' => $row['facilities'],
                    'created_at' => CarbonImmutable::parse($row['published_at']),
                ], $scenario['legacy_profile'], [], $asOf),
            ], $candidates);
            usort($legacy, fn ($a, $b) => ($b['score'] <=> $a['score']) ?: strcmp($a['listing_code'], $b['listing_code']));
            $results[$scenario['id']] = [
                'candidate_count' => count($candidates),
                'candidate_codes' => $this->sortedCodes($candidates),
                'baseline' => ['description' => 'Offline published_at proxy for search created_at DESC', 'ranking' => $this->rank(array_map(fn ($row) => ['listing_code' => $row['listing_code']], $baseline))],
                'legacy' => ['description' => 'Score-only fixed heuristic; production candidate generation and source bonuses omitted', 'ranking' => $this->rank($legacy)],
                'hybrid' => ['lambda' => 0.25, 'ranking' => $this->rank($this->hybridRank($candidates, $scenario, 0.25))],
                'sensitivity' => [
                    '0' => $this->rank($this->hybridRank($candidates, $scenario, 0)),
                    '0.5' => $this->rank($this->hybridRank($candidates, $scenario, 0.5)),
                ],
            ];
        }

        return $results;
    }

    public function validateDataset(array $dataset): void
    {
        $rows = $dataset['listings'] ?? null;
        $this->require(($dataset['dataset_version'] ?? null) === 'v1' && is_array($rows) && count($rows) >= 24 && count($rows) <= 36, 'Invalid dataset version or listing count');
        $codes = [];
        foreach ($rows as $row) {
            $code = $row['listing_code'] ?? null;
            $this->require(is_string($code) && preg_match('/^PIR-[0-9]{3}$/', $code) === 1 && ! isset($codes[$code]), 'Invalid or duplicate listing_code');
            $codes[$code] = true;
            $this->require(($row['dataset_version'] ?? null) === 'v1', 'Invalid listing dataset_version');
            $this->require(($row['data_status'] ?? null) === 'synthetic', 'Only synthetic data is allowed in v1');
            $this->require(($row['source'] ?? null) === 'author-generated fixture' && ($row['collected_at'] ?? null) === '2026-09-23', 'Invalid provenance');
            $this->require(($row['image_license'] ?? null) === 'none' && ($row['image_source'] ?? null) === 'none', 'Images are not permitted');
            $this->require(! array_intersect(array_keys($row), ['owner', 'owner_name', 'email', 'phone', 'address', 'contact']), 'Personal/contact fields are not permitted');
            $this->require(is_numeric($row['price_monthly_eur'] ?? null) && is_finite((float) $row['price_monthly_eur']) && $row['price_monthly_eur'] > 0, 'Invalid price');
            $this->require(in_array($row['city'] ?? null, ['Beograd', 'Niš', 'Novi Sad'], true) && is_string($row['zone'] ?? null) && $row['zone'] !== '', 'Invalid location');
            $this->require(in_array($row['status'] ?? null, ['active', 'inactive'], true), 'Invalid status');
            $this->require(is_string($row['published_at'] ?? null) && strtotime($row['published_at']) !== false, 'Invalid published_at');
            foreach (['rooms', 'area_m2'] as $field) {
                $this->require(($row[$field] ?? null) === null || (is_numeric($row[$field]) && $row[$field] > 0), 'Invalid '.$field);
            }
            $this->require(is_array($row['facilities'] ?? null) && count($row['facilities']) === count(array_unique($row['facilities'])), 'Invalid facilities');
            $missing = array_values(array_filter(['rooms', 'area_m2'], fn ($field) => ($row[$field] ?? null) === null));
            $this->require(($row['missing_fields'] ?? null) === $missing, 'Incorrect missing_fields');
            $this->require(array_key_exists('lat', $row) && array_key_exists('lng', $row) && array_key_exists('rating', $row) && array_key_exists('ratings_count', $row) && array_key_exists('notes', $row), 'Missing schema field');
        }
        $this->require(count(array_unique(array_column($rows, 'city'))) === 3, 'Dataset needs all three cities');
    }

    public function validateScenario(array $scenario): void
    {
        $hard = $scenario['hard_filters'] ?? [];
        $this->require(is_array($hard) && array_keys($hard) === ['city', 'status', 'max_price_monthly_eur', 'required_facilities'], 'Invalid hard-filter schema');
        $this->require(in_array($hard['city'], ['Beograd', 'Niš', 'Novi Sad'], true) && $hard['status'] === 'active' && is_numeric($hard['max_price_monthly_eur']) && $hard['max_price_monthly_eur'] > 0, 'Invalid hard-filter value');
        $this->require(is_array($hard['required_facilities']) && count($hard['required_facilities']) === count(array_unique($hard['required_facilities'])), 'Invalid required facilities');
        foreach ($hard['required_facilities'] as $facility) {
            $this->require(is_string($facility) && $facility !== '', 'Invalid required facility');
        }
        foreach (['explicit_weights', 'behavior_weights'] as $field) {
            $weights = $scenario[$field] ?? null;
            $this->require(is_array($weights) && array_keys($weights) === self::CRITERIA, 'Unknown or missing criterion in '.$field);
            foreach ($weights as $weight) {
                $this->require((is_int($weight) || is_float($weight)) && is_finite((float) $weight) && $weight >= 0, 'Invalid weight');
            }
            $this->require(abs(array_sum($weights) - 1) <= 1e-9, 'Weights must sum to 1');
        }
        $normal = $scenario['normalization'] ?? [];
        $this->require(is_numeric($normal['price_floor_eur'] ?? null) && is_numeric($normal['price_ceiling_eur'] ?? null) && $normal['price_floor_eur'] < $normal['price_ceiling_eur'] && $normal['price_ceiling_eur'] === $hard['max_price_monthly_eur'], 'Invalid price thresholds');
        $this->require(is_array($normal['zone_scores'] ?? null) && ! empty($normal['zone_scores']), 'Invalid zones');
        foreach ($normal['zone_scores'] as $score) {
            $this->require(is_numeric($score) && $score >= 0 && $score <= 1, 'Invalid zone utility');
        }
        $this->require(($normal['area_target_m2'] ?? 0) > 0 && ($normal['rooms_target'] ?? 0) > 0 && ! empty($normal['preferred_facilities']) && is_array($normal['preferred_facilities']), 'Invalid normalization targets');
        $this->require(is_array($scenario['legacy_profile'] ?? null) && is_array($scenario['legacy_profile']['cities'] ?? null), 'Invalid legacy profile');
        $this->require(is_string($scenario['as_of'] ?? null) && strtotime($scenario['as_of']) !== false, 'Invalid as_of');
    }

    public function passesHardFilters(array $row, array $hard): bool
    {
        return ($row['city'] ?? null) === $hard['city']
            && ($row['status'] ?? null) === $hard['status']
            && is_numeric($row['price_monthly_eur'] ?? null)
            && $row['price_monthly_eur'] <= $hard['max_price_monthly_eur']
            && is_array($row['facilities'] ?? null)
            && count(array_diff($hard['required_facilities'], $row['facilities'])) === 0;
    }

    public function hybridRank(array $candidates, array $scenario, float $lambda): array
    {
        $this->require(is_finite($lambda) && $lambda >= 0 && $lambda <= 1, 'Lambda outside [0,1]');
        $this->validateScenario($scenario);
        $weights = [];
        foreach (self::CRITERIA as $criterion) {
            $weights[$criterion] = (1 - $lambda) * $scenario['explicit_weights'][$criterion] + $lambda * $scenario['behavior_weights'][$criterion];
        }
        $ranked = [];
        foreach ($candidates as $row) {
            $utilities = $this->utilities($row, $scenario['normalization']);
            $missing = array_keys(array_filter($utilities, fn ($v) => $v === null));
            $availableWeight = array_sum(array_diff_key($weights, array_flip($missing)));
            $this->require($availableWeight > 0, 'No available soft criteria');
            $effective = [];
            $contributions = [];
            foreach (self::CRITERIA as $criterion) {
                $effective[$criterion] = $utilities[$criterion] === null ? 0.0 : $weights[$criterion] / $availableWeight;
                $contributions[$criterion] = $utilities[$criterion] === null ? null : 100 * $effective[$criterion] * $utilities[$criterion];
            }
            $score = array_sum(array_filter($contributions, fn ($v) => $v !== null));
            $reasons = array_keys(array_filter($contributions, fn ($v) => $v !== null && $v > 0));
            usort($reasons, fn ($a, $b) => ($contributions[$b] <=> $contributions[$a]) ?: strcmp($a, $b));
            $ranked[] = [
                'listing_code' => $row['listing_code'], 'model_version' => self::MODEL_VERSION,
                'score' => round($score, 6), 'missing_fields' => $missing,
                'original_weights' => $this->roundValues($weights), 'effective_weights' => $this->roundValues($effective),
                'utilities' => $this->roundValues($utilities), 'contributions' => $this->roundValues($contributions),
                'reasons' => array_slice($reasons, 0, 3),
            ];
        }
        usort($ranked, fn ($a, $b) => ($b['score'] <=> $a['score']) ?: (count($a['missing_fields']) <=> count($b['missing_fields'])) ?: strcmp($a['listing_code'], $b['listing_code']));

        return $ranked;
    }

    public function utilities(array $row, array $normal): array
    {
        $clamp = fn ($value) => max(0.0, min(1.0, (float) $value));
        $price = $row['price_monthly_eur'] ?? null;
        $area = $row['area_m2'] ?? null;
        $rooms = $row['rooms'] ?? null;
        $facilities = $row['facilities'] ?? null;
        $preferred = $normal['preferred_facilities'];

        return [
            'price' => $price === null ? null : $clamp(($normal['price_ceiling_eur'] - $price) / ($normal['price_ceiling_eur'] - $normal['price_floor_eur'])),
            'zone' => $normal['zone_scores'][$row['zone'] ?? ''] ?? null,
            'area' => $area === null ? null : $clamp($area / $normal['area_target_m2']),
            'rooms' => $rooms === null ? null : $clamp($rooms / $normal['rooms_target']),
            'facilities' => $facilities === null ? null : $clamp(count(array_intersect($preferred, $facilities)) / count($preferred)),
        ];
    }

    private function roundValues(array $values): array
    {
        return array_map(fn ($value) => $value === null ? null : round((float) $value, 6), $values);
    }

    private function rank(array $rows): array
    {
        foreach ($rows as $index => &$row) {
            $row = ['rank' => $index + 1] + $row;
        }

        return $rows;
    }

    private function sortedCodes(array $rows): array
    {
        $codes = array_column($rows, 'listing_code');
        sort($codes, SORT_STRING);

        return $codes;
    }

    private function require(bool $valid, string $message): void
    {
        if (! $valid) {
            throw new InvalidArgumentException($message);
        }
    }
}
