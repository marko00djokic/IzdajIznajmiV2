<?php

namespace Tests\Unit;

use App\Services\LegacyRecommendationScorer;
use App\Services\PirOfflineEvaluator;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PirOfflineEvaluatorTest extends TestCase
{
    private PirOfflineEvaluator $evaluator;

    private array $dataset;

    private array $scenarios;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new PirOfflineEvaluator(new LegacyRecommendationScorer);
        $root = dirname(__DIR__, 3).'/docs/01-project/pir/research/';
        $this->dataset = json_decode(file_get_contents($root.'dataset-v1.json'), true, flags: JSON_THROW_ON_ERROR);
        $this->scenarios = json_decode(file_get_contents($root.'scenarios-v1.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_frozen_inputs_and_candidate_sets(): void
    {
        $rows = $this->dataset['listings'];
        $this->assertCount(30, $rows);
        $this->assertCount(30, array_unique(array_column($rows, 'listing_code')));
        foreach ($rows as $row) {
            $this->assertSame([
                'dataset_version', 'listing_code', 'price_monthly_eur', 'city', 'zone', 'lat', 'lng',
                'rooms', 'area_m2', 'facilities', 'rating', 'ratings_count', 'published_at', 'status',
                'source', 'collected_at', 'data_status', 'image_license', 'image_source', 'missing_fields', 'notes',
            ], array_keys($row));
            $this->assertSame('synthetic', $row['data_status']);
            $this->assertSame('author-generated fixture', $row['source']);
            $this->assertSame('2026-09-23', $row['collected_at']);
            $this->assertSame('none', $row['image_license']);
            $this->assertSame('none', $row['image_source']);
            $this->assertEmpty(array_intersect(array_keys($row), ['owner', 'owner_name', 'email', 'phone', 'address', 'contact']));
            $this->assertDoesNotMatchRegularExpression('/@|https?:\/\/|\+?[0-9][0-9 ()-]{7,}/', $row['notes']);
        }
        $result = $this->evaluator->evaluate($this->dataset, $this->scenarios);
        $this->assertSame(['S1', 'S2'], array_keys($result));
        foreach ($result as $id => $comparison) {
            $this->assertGreaterThanOrEqual(12, $comparison['candidate_count']);
            $this->assertLessThanOrEqual(18, $comparison['candidate_count']);
            foreach (['baseline', 'legacy', 'hybrid'] as $method) {
                $ranking = $comparison[$method]['ranking'];
                $codes = array_column($ranking, 'listing_code');
                sort($codes);
                $this->assertSame($comparison['candidate_codes'], $codes, $id.' '.$method);
                $this->assertSame(range(1, count($ranking)), array_column($ranking, 'rank'));
            }
            $scenario = $this->scenarios['scenarios'][$id === 'S1' ? 0 : 1];
            foreach ($rows as $row) {
                $this->assertSame(in_array($row['listing_code'], $comparison['candidate_codes']), $this->evaluator->passesHardFilters($row, $scenario['hard_filters']));
            }
        }
        $this->assertSame(16, $result['S1']['candidate_count']);
        $this->assertSame(13, $result['S2']['candidate_count']);
    }

    public function test_frozen_scenarios_have_pareto_tradeoffs(): void
    {
        $this->assertSame(0.25, $this->scenarios['lambda_primary']);
        $this->assertSame([0, 0.5], $this->scenarios['lambda_sensitivity']);
        foreach ($this->scenarios['scenarios'] as $scenario) {
            $this->assertSame(PirOfflineEvaluator::CRITERIA, array_keys($scenario['explicit_weights']));
            $this->assertSame(PirOfflineEvaluator::CRITERIA, array_keys($scenario['behavior_weights']));
            $this->assertSame('2026-09-23T12:00:00+00:00', $scenario['as_of']);
            $this->assertSame($scenario['hard_filters']['max_price_monthly_eur'], $scenario['normalization']['price_ceiling_eur']);
            $candidates = array_values(array_filter($this->dataset['listings'], fn ($row) => $this->evaluator->passesHardFilters($row, $scenario['hard_filters'])));
            $utilities = array_map(fn ($row) => $this->evaluator->utilities($row, $scenario['normalization']), $candidates);
            $maxima = [];
            foreach (PirOfflineEvaluator::CRITERIA as $criterion) {
                $maxima[$criterion] = max(array_filter(array_column($utilities, $criterion), fn ($value) => $value !== null));
            }
            foreach ($utilities as $values) {
                $belowMaximum = false;
                foreach (PirOfflineEvaluator::CRITERIA as $criterion) {
                    $belowMaximum = $belowMaximum || $values[$criterion] === null || $values[$criterion] < $maxima[$criterion];
                }
                $this->assertTrue($belowMaximum, $scenario['id'].' must not have a candidate best on every criterion');
            }
        }
    }

    public function test_invalid_dataset_and_scenario_are_rejected(): void
    {
        $cases = [];
        $data = $this->dataset;
        $data['listings'][1]['listing_code'] = $data['listings'][0]['listing_code'];
        $cases[] = [$data, $this->scenarios];
        $data = $this->dataset;
        $data['listings'][0]['email'] = 'person@example.com';
        $cases[] = [$data, $this->scenarios];
        $data = $this->dataset;
        $data['listings'][0]['price_monthly_eur'] = -1;
        $cases[] = [$data, $this->scenarios];
        $data = $this->dataset;
        $data['listings'][0]['data_status'] = 'real';
        $cases[] = [$data, $this->scenarios];
        $data = $this->dataset;
        $data['listings'][0]['zone'] = null;
        $cases[] = [$data, $this->scenarios];
        $data = $this->dataset;
        unset($data['listings'][0]['published_at']);
        $cases[] = [$data, $this->scenarios];
        $data = $this->dataset;
        foreach ($data['listings'] as &$row) {
            if ($this->evaluator->passesHardFilters($row, $this->scenarios['scenarios'][0]['hard_filters'])) {
                $row['price_monthly_eur'] = 500;
                $row['zone'] = 'Centar';
                $row['rooms'] = 2;
                $row['area_m2'] = 50;
                $row['facilities'] = ['Wifi', 'Garage'];
                $row['missing_fields'] = [];
            }
        }
        unset($row);
        try {
            $this->evaluator->evaluate($data, $this->scenarios);
            $this->fail('Dataset without Pareto tradeoffs was accepted');
        } catch (InvalidArgumentException $error) {
            $this->assertStringContainsString('best on every criterion', $error->getMessage());
        }
        foreach (['unknown', 'negative', 'nonnumeric', 'sum', 'hard', 'facility'] as $kind) {
            $scenario = $this->scenarios;
            if ($kind === 'unknown') {
                $scenario['scenarios'][0]['explicit_weights']['unknown'] = 0;
            } elseif ($kind === 'negative') {
                $scenario['scenarios'][0]['explicit_weights']['price'] = -1;
            } elseif ($kind === 'nonnumeric') {
                $scenario['scenarios'][0]['behavior_weights']['price'] = '0.25';
            } elseif ($kind === 'sum') {
                $scenario['scenarios'][0]['explicit_weights']['price'] = 0.1;
            } elseif ($kind === 'facility') {
                $scenario['scenarios'][0]['hard_filters']['required_facilities'] = [''];
            } else {
                $scenario['scenarios'][0]['hard_filters']['max_price_monthly_eur'] = -1;
            }
            $cases[] = [$this->dataset, $scenario];
        }
        foreach ($cases as [$dataset, $scenarios]) {
            try {
                $this->evaluator->evaluate($dataset, $scenarios);
                $this->fail('Invalid input was accepted');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
        foreach ([-0.01, 1.01] as $lambda) {
            try {
                $this->evaluator->hybridRank([], $this->scenarios['scenarios'][0], $lambda);
                $this->fail('Invalid lambda was accepted');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_hard_filters_and_fixed_utility_boundaries(): void
    {
        $scenario = $this->scenarios['scenarios'][1];
        $hard = $scenario['hard_filters'];
        $normal = $scenario['normalization'];
        $row = $this->dataset['listings'][1];
        $this->assertTrue($this->evaluator->passesHardFilters($row, $hard));
        foreach (['city' => 'Niš', 'status' => null, 'price_monthly_eur' => 851, 'facilities' => null] as $field => $value) {
            $changed = $row;
            $changed[$field] = $value;
            $this->assertFalse($this->evaluator->passesHardFilters($changed, $hard), $field);
        }
        $row['facilities'] = ['Heating'];
        $this->assertFalse($this->evaluator->passesHardFilters($row, $hard));
        $row['facilities'] = ['Wifi'];
        $row['price_monthly_eur'] = 850;
        $this->assertTrue($this->evaluator->passesHardFilters($row, $hard));
        $this->assertSame(0.0, $this->evaluator->utilities($row, $normal)['price']);
        $row['price_monthly_eur'] = 350;
        $row['area_m2'] = 45;
        $row['rooms'] = 3;
        $this->assertSame(1.0, $this->evaluator->utilities($row, $normal)['price']);
        $this->assertSame(1.0, $this->evaluator->utilities($row, $normal)['area']);
        $this->assertSame(1.0, $this->evaluator->utilities($row, $normal)['rooms']);
        $row['price_monthly_eur'] = 200;
        $row['area_m2'] = 90;
        $row['rooms'] = 6;
        $this->assertSame(1.0, $this->evaluator->utilities($row, $normal)['price']);
        $this->assertSame(1.0, $this->evaluator->utilities($row, $normal)['area']);
        $this->assertSame(1.0, $this->evaluator->utilities($row, $normal)['rooms']);
        $row['zone'] = 'Unknown';
        $this->assertNull($this->evaluator->utilities($row, $normal)['zone']);
    }

    public function test_hybrid_contributions_missing_values_and_stable_order(): void
    {
        $scenario = $this->scenarios['scenarios'][0];
        $row = $this->dataset['listings'][0];
        $ranking = $this->evaluator->hybridRank([$row], $scenario, 0.25);
        $item = $ranking[0];
        $this->assertEqualsWithDelta(0.325, $item['original_weights']['price'], 0.000001);
        $expected = 100 * (0.325 * ((800 - 420) / (800 - 350)) + 0.2875 + 0.1625 * (28 / 55) + 0.1 * (1 / 2) + 0.125 * (1 / 2));
        $this->assertEqualsWithDelta($expected, $item['score'], 0.000001);
        $this->assertEqualsWithDelta($item['score'], array_sum($item['contributions']), 0.00001);
        $this->assertLessThanOrEqual(3, count($item['reasons']));
        $row['area_m2'] = null;
        $missing = $this->evaluator->hybridRank([$row], $scenario, 0.25)[0];
        $this->assertContains('area', $missing['missing_fields']);
        $this->assertNull($missing['contributions']['area']);
        $this->assertEqualsWithDelta(1, array_sum($missing['effective_weights']), 0.00001);
        $a = $this->dataset['listings'][0];
        $a['listing_code'] = 'PIR-901';
        $b = $a;
        $b['listing_code'] = 'PIR-900';
        $first = $this->evaluator->hybridRank([$a, $b], $scenario, 0.25);
        $second = $this->evaluator->hybridRank([$b, $a], $scenario, 0.25);
        $this->assertSame(['PIR-900', 'PIR-901'], array_column($first, 'listing_code'));
        $this->assertSame($first, $second);
        $better = $a;
        $better['listing_code'] = 'PIR-999';
        $better['price_monthly_eur'] = 500.0;
        $worse = $better;
        $worse['listing_code'] = 'PIR-000';
        $worse['price_monthly_eur'] = 500.000001;
        $precise = $this->evaluator->hybridRank([$worse, $better], $scenario, 0.25);
        $this->assertSame(['PIR-999', 'PIR-000'], array_column($precise, 'listing_code'));
        $this->assertSame($precise[0]['score'], $precise[1]['score']);

        $complete = $a;
        $complete['listing_code'] = 'PIR-903';
        $complete['price_monthly_eur'] = 350;
        $complete['zone'] = 'Centar';
        $complete['area_m2'] = 55;
        $complete['rooms'] = 2;
        $complete['facilities'] = ['Garage', 'Wifi'];
        $incomplete = $complete;
        $incomplete['listing_code'] = 'PIR-899';
        $incomplete['area_m2'] = null;
        $tied = $this->evaluator->hybridRank([$incomplete, $complete], $scenario, 0.25);
        $this->assertSame(['PIR-903', 'PIR-899'], array_column($tied, 'listing_code'));
    }

    public function test_hybrid_numeric_example_and_missing_renormalization(): void
    {
        $scenario = $this->scenarios['scenarios'][0];
        $row = $this->dataset['listings'][0];
        $row['price_monthly_eur'] = 575;
        $row['zone'] = 'Centar';
        $row['area_m2'] = 27.5;
        $row['rooms'] = 1;
        $row['facilities'] = ['Garage'];
        $result = $this->evaluator->hybridRank([$row], $scenario, 0.25)[0];
        $this->assertSame(64.375, $result['score']);
        $this->assertSame(['price' => 0.325, 'zone' => 0.2875, 'area' => 0.1625, 'rooms' => 0.1, 'facilities' => 0.125], $result['original_weights']);
        $this->assertSame(['price' => 16.25, 'zone' => 28.75, 'area' => 8.125, 'rooms' => 5.0, 'facilities' => 6.25], $result['contributions']);
        $this->assertSame(['zone', 'price', 'area'], $result['reasons']);
        $row['area_m2'] = null;
        $missing = $this->evaluator->hybridRank([$row], $scenario, 0.25)[0];
        $this->assertSame(['area'], $missing['missing_fields']);
        $this->assertNull($missing['utilities']['area']);
        $this->assertNull($missing['contributions']['area']);
        $this->assertEqualsWithDelta(0.325 / 0.8375, $missing['effective_weights']['price'], 0.000001);
        $this->assertEqualsWithDelta(67.164179, $missing['score'], 0.000001);
        $this->assertEqualsWithDelta($missing['score'], array_sum($missing['contributions']), 0.00001);
    }

    public function test_offline_legacy_scores_every_filtered_candidate_at_fixed_time(): void
    {
        $comparisons = $this->evaluator->evaluate($this->dataset, $this->scenarios);
        $scorer = new LegacyRecommendationScorer;
        foreach ($this->scenarios['scenarios'] as $scenario) {
            $comparison = $comparisons[$scenario['id']];
            $actual = array_column($comparison['legacy']['ranking'], 'score', 'listing_code');
            $codes = array_keys($actual);
            sort($codes, SORT_STRING);
            $this->assertSame($comparison['candidate_codes'], $codes);
            foreach ($this->dataset['listings'] as $row) {
                if (! $this->evaluator->passesHardFilters($row, $scenario['hard_filters'])) {
                    continue;
                }
                $expected = $scorer->score([
                    'city' => $row['city'], 'price' => $row['price_monthly_eur'], 'rooms' => $row['rooms'],
                    'area' => $row['area_m2'], 'facilities' => $row['facilities'],
                    'created_at' => CarbonImmutable::parse($row['published_at']),
                ], $scenario['legacy_profile'], [], CarbonImmutable::parse($scenario['as_of']));
                $this->assertSame($expected, $actual[$row['listing_code']]);
            }
        }
    }

    public function test_fixed_utility_boundaries_and_pareto_tradeoffs(): void
    {
        foreach ($this->scenarios['scenarios'] as $scenario) {
            $normal = $scenario['normalization'];
            $row = $this->dataset['listings'][0];
            $row['price_monthly_eur'] = $normal['price_floor_eur'];
            $row['area_m2'] = $normal['area_target_m2'];
            $row['rooms'] = $normal['rooms_target'];
            $row['facilities'] = $normal['preferred_facilities'];
            $this->assertSame(1.0, $this->evaluator->utilities($row, $normal)['price']);
            $this->assertSame(1.0, $this->evaluator->utilities($row, $normal)['area']);
            $this->assertSame(1.0, $this->evaluator->utilities($row, $normal)['rooms']);
            $this->assertSame(1.0, $this->evaluator->utilities($row, $normal)['facilities']);
            $row['price_monthly_eur'] = $normal['price_ceiling_eur'];
            $this->assertSame(0.0, $this->evaluator->utilities($row, $normal)['price']);
            $candidates = array_values(array_filter($this->dataset['listings'], fn ($item) => $this->evaluator->passesHardFilters($item, $scenario['hard_filters'])));
            $tradeoff = false;
            foreach ($candidates as $left) {
                foreach ($candidates as $right) {
                    if ($left['listing_code'] === $right['listing_code']) {
                        continue;
                    }
                    $a = array_filter($this->evaluator->utilities($left, $normal), fn ($value) => $value !== null);
                    $b = array_filter($this->evaluator->utilities($right, $normal), fn ($value) => $value !== null);
                    $shared = array_intersect_key($a, $b);
                    $tradeoff = $tradeoff || (count($shared) === 5 && count(array_filter(array_keys($shared), fn ($key) => $a[$key] > $b[$key])) > 0 && count(array_filter(array_keys($shared), fn ($key) => $a[$key] < $b[$key])) > 0);
                }
            }
            $this->assertTrue($tradeoff, $scenario['id'].' needs a Pareto tradeoff');
        }
        $hard = $this->scenarios['scenarios'][1]['hard_filters'];
        $row = $this->dataset['listings'][0];
        $row['city'] = null;
        $this->assertFalse($this->evaluator->passesHardFilters($row, $hard));
        $row['city'] = 'Beograd';
        $row['facilities'] = null;
        $this->assertFalse($this->evaluator->passesHardFilters($row, $hard));
    }

    public function test_legacy_formula_branches(): void
    {
        $scorer = new LegacyRecommendationScorer;
        $profile = $this->scenarios['scenarios'][0]['legacy_profile'];
        $asOf = CarbonImmutable::parse('2026-09-23T12:00:00+00:00');
        $listing = ['city' => 'Beograd', 'price' => 500, 'rooms' => 2, 'area' => 50, 'facilities' => ['Wifi', 'Garage'], 'created_at' => $asOf->subDay()];
        $this->assertSame(102.0, $scorer->score($listing, $profile, ['similar_view'], $asOf));
        $this->assertSame(90.0, $scorer->score($listing, $profile, [], $asOf));
        $this->assertSame(85.0, $scorer->score($listing, $profile, [], $asOf->addDays(30)));
        $this->assertSame(123.0, $scorer->score($listing, $profile, ['similar_view', 'saved_search', 'recent_search', 'fresh'], $asOf));
    }

    public function test_legacy_scorer_keeps_each_original_branch(): void
    {
        $scorer = new LegacyRecommendationScorer;
        $asOf = CarbonImmutable::parse('2026-09-23T12:00:00+00:00');
        $profile = [
            'cities' => [['city' => 'Beograd'], ['city' => 'Niš']], 'priceMin' => 450, 'priceMax' => 600,
            'rooms' => 2, 'areaMin' => 30, 'areaMax' => 60, 'amenities' => ['Wifi', 'Garage'],
        ];
        $blank = ['city' => null, 'price' => null, 'rooms' => null, 'beds' => null, 'area' => null, 'facilities' => [], 'created_at' => null];
        $score = fn ($changes, $tags = [], $time = null) => $scorer->score(array_replace($blank, $changes), $profile, $tags, $time ?? $asOf);
        $this->assertSame(30.0, $score(['city' => 'Beograd']));
        $this->assertSame(15.0, $score(['city' => 'Niš']));
        $this->assertSame(0.0, $score(['city' => 'Novi Sad']));
        $this->assertSame(20.0, $score(['price' => 450]));
        $this->assertSame(10.0, $score(['price' => 440]));
        $this->assertSame(0.0, $score(['price' => 701]));
        $this->assertSame(10.0, $score(['rooms' => 3]));
        $this->assertSame(10.0, $score(['beds' => 2]));
        $this->assertSame(0.0, $score(['rooms' => 4]));
        $this->assertSame(10.0, $score(['area' => 30]));
        $this->assertSame(0.0, $score(['area' => 61]));
        $this->assertSame(7.5, $score(['facilities' => ['Wifi']]));
        $this->assertSame(15.0, $score(['facilities' => ['Wifi', 'Garage']]));
        $this->assertSame(5.0, $score(['created_at' => $asOf->subDays(13)]));
        $this->assertSame(0.0, $score(['created_at' => $asOf->subDays(14)]));
        $this->assertSame(12.0, $score([], ['similar_view']));
        $this->assertSame(10.0, $score([], ['saved_search']));
        $this->assertSame(6.0, $score([], ['recent_search']));
        $this->assertSame(5.0, $score([], ['fresh']));
        $this->assertSame(0.0, $score([], ['unknown']));
    }
}
