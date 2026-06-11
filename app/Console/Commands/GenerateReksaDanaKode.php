<?php

namespace App\Console\Commands;

use App\Models\ReksaDana;
use App\Services\KodeReksaDanaParser;
use Illuminate\Console\Command;

class GenerateReksaDanaKode extends Command
{
    protected $signature = 'reksa-dana:generate-kode
        {--dry-run : Only count, no updates}
        {--force : Regenerate even if kode_reksa_dana already exists}';

    protected $description = 'Generate kode_reksa_dana (16-char OJK format) for records missing it';

    public function handle(KodeReksaDanaParser $parser): int
    {
        $query = ReksaDana::query();

        if (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('kode_reksa_dana')->orWhere('kode_reksa_dana', '');
            });
        }

        $records = $query->get();
        $total = $records->count();
        $generated = 0;
        $skipped = 0;
        $errors = [];

        $this->line("Processing {$total} records...");

        foreach ($records as $record) {
            $code = $parser->generateFromRecord($record);
            if (!$code) {
                $skipped++;
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("[DRY-RUN] {$record->id}: {$record->nama_reksa_dana} -> {$code}");
                $generated++;
                continue;
            }

            $record->kode_reksa_dana = $code;
            try {
                $record->save();
                $generated++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "{$record->id}: {$e->getMessage()}";
                if (count($errors) <= 10) {
                    $this->warn("  Error {$record->id}: {$e->getMessage()}");
                }
            }
        }

        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Total processed', $total],
                ['Generated', $generated],
                ['Skipped (incomplete data)', $skipped],
                ['Errors', count($errors)],
            ]
        );

        if ($this->option('dry-run')) {
            $this->warn('Dry-run mode: no changes were saved.');
        }

        return 0;
    }
}
