<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'csv:import';

    /**
     * The console command description.
     */
    protected $description = 'Import CSV data into the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $map = [
            'batteries' => storage_path('app/private/batteries copy.csv'),
            'connectors' => storage_path('app/private/connectors copy.csv'),
            'solar_panels' => storage_path('app/private/solar_panels copy.csv'),
        ];

        foreach ($map as $table => $path) {
            if (! file_exists($path)) {
                $this->info("CSV not found: {$path} — skipping {$table}");

                continue;
            }

            $handle = fopen($path, 'r');
            if ($handle === false) {
                $this->error("Failed to open {$path}");

                continue;
            }

            $header = fgetcsv($handle);
            if ($header === false) {
                fclose($handle);

                continue;
            }

            // normalize header: trim and remove BOM, keep names exactly as in CSV
            $header = array_map(function ($h) {
                $h = (string) $h;
                $h = preg_replace('/^\xEF\xBB\xBF/', '', $h); // strip BOM

                return trim($h);
            }, $header);

            $tableColumns = Schema::getColumnListing($table);

            $rows = [];
            $line = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $line++;
                if (count($row) === 0) {
                    continue;
                }

                $data = @array_combine($header, $row);
                if (! is_array($data)) {
                    $this->warn("Skipping malformed CSV row {$line} in {$path} (column count mismatch)");

                    continue;
                }

                foreach ($data as $k => $v) {
                    $v = trim((string) $v);
                    if ($v === '') {
                        $data[$k] = null;

                        continue;
                    }
                    $lower = strtolower($k);
                    if ($lower === 'price') {
                        $data[$k] = (float) str_replace([',', '$'], '', $v);

                        continue;
                    }
                    if (in_array($lower, ['capacity', 'power', 'power_output', 'wattage', 'voltage'], true)) {
                        // ensure numeric cast where appropriate
                        $num = filter_var($v, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        $data[$k] = $num === '' ? null : (float) $num;

                        continue;
                    }
                    $data[$k] = $v;
                }

                // only keep columns that exist in DB (exact match)
                $filtered = [];
                foreach ($data as $k => $v) {
                    if (in_array($k, $tableColumns, true)) {
                        $filtered[$k] = $v;
                    }
                }

                if (empty($filtered)) {
                    continue;
                }

                if (isset($filtered['id'])) {
                    $filtered['id'] = (int) $filtered['id'];
                }

                $rows[] = $filtered;
            }

            fclose($handle);

            if (! empty($rows)) {
                try {
                    DB::transaction(function () use ($table, $rows) {
                        foreach (array_chunk($rows, 100) as $chunk) {
                            // insertOrIgnore prevents the seeder from failing on duplicate primary keys
                            DB::table($table)->insertOrIgnore($chunk);
                        }
                    });

                    // Clear manufacturers cache for this table so UI picks up new values
                    Cache::forget("manufacturers:{$table}");

                    $this->info("Imported {$path} -> {$table} (".count($rows).' rows)');
                } catch (\Throwable $e) {
                    // Log and continue to next file; we don't want one bad file to stop the whole seeder
                    $this->error("Failed to import {$path} -> {$table}: {$e->getMessage()}");
                }
            }
        }

        return Command::SUCCESS;
    }
}