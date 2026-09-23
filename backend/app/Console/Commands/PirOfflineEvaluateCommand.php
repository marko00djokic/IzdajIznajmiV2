<?php

namespace App\Console\Commands;

use App\Services\PirOfflineEvaluator;
use Illuminate\Console\Command;
use JsonException;
use RuntimeException;
use Throwable;

class PirOfflineEvaluateCommand extends Command
{
    protected $signature = 'pir:evaluate {--dataset= : Absolute or relative dataset JSON path} {--scenarios= : Scenario JSON path} {--manifest= : Checksum manifest JSON path} {--output= : Output JSON path}';

    protected $description = 'Evaluate frozen PIR ranking inputs offline without database access.';

    public function handle(PirOfflineEvaluator $evaluator): int
    {
        try {
            foreach (['dataset', 'scenarios', 'manifest', 'output'] as $option) {
                if (! is_string($this->option($option)) || $this->option($option) === '') {
                    throw new RuntimeException('Missing --'.$option);
                }
            }
            $datasetPath = $this->option('dataset');
            $scenariosPath = $this->option('scenarios');
            $manifestPath = $this->option('manifest');
            $outputPath = $this->option('output');
            $manifest = $this->decode($manifestPath);
            $files = ['dataset-v1.json' => $datasetPath, 'scenarios-v1.json' => $scenariosPath];
            $checksums = [];
            foreach ($files as $name => $path) {
                $bytes = @file_get_contents($path);
                if ($bytes === false) {
                    throw new RuntimeException('Cannot read '.$path);
                }
                $checksums[$name] = hash('sha256', $bytes);
                if (! hash_equals((string) ($manifest['files'][$name] ?? ''), $checksums[$name])) {
                    throw new RuntimeException('SHA-256 mismatch: '.$name);
                }
            }
            $dataset = $this->decode($datasetPath);
            $scenarios = $this->decode($scenariosPath);
            if (($manifest['dataset_version'] ?? null) !== ($dataset['dataset_version'] ?? null) || ($manifest['scenario_version'] ?? null) !== ($scenarios['scenario_version'] ?? null)) {
                throw new RuntimeException('Manifest version mismatch');
            }
            $started = microtime(true);
            $ranking = $evaluator->evaluate($dataset, $scenarios);
            $commit = trim((string) @shell_exec('git -C '.escapeshellarg(base_path()).' rev-parse HEAD 2>/dev/null'));
            $result = [
                'dataset_version' => $dataset['dataset_version'],
                'scenario_version' => $scenarios['scenario_version'],
                'model_version' => PirOfflineEvaluator::MODEL_VERSION,
                'checksums_sha256' => $checksums,
                'commit' => $commit !== '' ? $commit : null,
                'parameters' => ['lambda_primary' => 0.25, 'lambda_sensitivity' => [0, 0.5], 'scenario_ids' => ['S1', 'S2']],
                'comparison' => $ranking,
                'run_metadata' => ['executed_at' => now()->toIso8601String(), 'duration_ms' => round((microtime(true) - $started) * 1000, 3)],
            ];
            $json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            if (@file_put_contents($outputPath, $json) === false) {
                throw new RuntimeException('Cannot write output '.$outputPath);
            }
            $this->info('PIR evaluation written to '.$outputPath);

            return self::SUCCESS;
        } catch (Throwable $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }
    }

    private function decode(string $path): array
    {
        $bytes = @file_get_contents($path);
        if ($bytes === false) {
            throw new RuntimeException('Cannot read '.$path);
        }
        try {
            $value = json_decode($bytes, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $error) {
            throw new RuntimeException('Invalid JSON in '.$path, previous: $error);
        }
        if (! is_array($value)) {
            throw new RuntimeException('Expected JSON object in '.$path);
        }

        return $value;
    }
}
