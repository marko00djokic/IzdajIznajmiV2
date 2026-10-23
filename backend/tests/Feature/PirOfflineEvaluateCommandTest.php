<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase;
use Tests\CreatesApplication;

class PirOfflineEvaluateCommandTest extends TestCase
{
    use CreatesApplication;

    public function test_command_rejects_changed_inputs_and_repeats_ranking(): void
    {
        $root = dirname(__DIR__, 3).'/docs/01-project/pir/research/';
        $dataset = $root.'dataset-v1.json';
        $scenarios = $root.'scenarios-v1.json';
        $manifest = $root.'manifest-v1.json';
        $one = tempnam(sys_get_temp_dir(), 'pir-one-');
        $two = tempnam(sys_get_temp_dir(), 'pir-two-');
        $changed = tempnam(sys_get_temp_dir(), 'pir-changed-');
        try {
            $options = ['--dataset' => $dataset, '--scenarios' => $scenarios, '--manifest' => $manifest];
            $this->artisan('pir:evaluate', $options + ['--output' => $one])->assertExitCode(0);
            $this->artisan('pir:evaluate', $options + ['--output' => $two])->assertExitCode(0);
            $first = json_decode(file_get_contents($one), true, flags: JSON_THROW_ON_ERROR);
            $second = json_decode(file_get_contents($two), true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame($first['comparison'], $second['comparison']);
            $this->assertSame(['executed_at', 'duration_ms'], array_keys($first['run_metadata']));
            unset($first['run_metadata'], $second['run_metadata']);
            $this->assertSame($first, $second);
            $this->assertSame(hash_file('sha256', $dataset), $first['checksums_sha256']['dataset-v1.json']);
            $this->assertSame(hash_file('sha256', $scenarios), $first['checksums_sha256']['scenarios-v1.json']);
            $this->assertSame('v1', $first['dataset_version']);
            $this->assertSame('v1', $first['scenario_version']);
            $this->assertSame('pir-hybrid-v1', $first['model_version']);
            $this->assertArrayHasKey('commit', $first);
            $this->assertSame([0, 0.5], $first['parameters']['lambda_sensitivity']);
            $this->assertSame(['S1', 'S2'], array_keys($first['comparison']));
            foreach ($first['comparison'] as $comparison) {
                foreach (['baseline', 'legacy', 'hybrid'] as $method) {
                    $ranking = $comparison[$method]['ranking'];
                    $this->assertCount($comparison['candidate_count'], $ranking);
                    $this->assertSame(range(1, count($ranking)), array_column($ranking, 'rank'));
                    $codes = array_column($ranking, 'listing_code');
                    sort($codes, SORT_STRING);
                    $this->assertSame($comparison['candidate_codes'], $codes);
                }
            }
            $this->assertStringContainsString('proxy', $first['comparison']['S1']['baseline']['description']);
            $this->assertStringContainsString('candidate generation', $first['comparison']['S1']['legacy']['description']);
            $this->assertStringNotContainsString('nDCG', json_encode($first));
            $this->assertStringNotContainsString('Precision@3', json_encode($first));
            $this->assertStringNotContainsString('H1', json_encode($first));
            file_put_contents($changed, file_get_contents($dataset).' ');
            $this->artisan('pir:evaluate', ['--dataset' => $changed, '--scenarios' => $scenarios, '--manifest' => $manifest, '--output' => $one])->assertExitCode(1);
            file_put_contents($changed, file_get_contents($scenarios).' ');
            $this->artisan('pir:evaluate', ['--dataset' => $dataset, '--scenarios' => $changed, '--manifest' => $manifest, '--output' => $one])->assertExitCode(1);
        } finally {
            unlink($one);
            unlink($two);
            unlink($changed);
        }
    }
}
