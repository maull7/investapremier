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

    protected $description = 'Generate kode_reksa_dana (17-char KSEI format) for records missing it';

    private const SEQ_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

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

        $this->line("Processing {$total} records...");

        // Pre-load existing 17-char codes from DB for duplicate detection
        $usedCodes = ReksaDana::whereNotNull('kode_reksa_dana')
            ->whereRaw('LENGTH(kode_reksa_dana) = 17')
            ->pluck('kode_reksa_dana')
            ->flip()
            ->toArray();

        $pending = [];

        foreach ($records as $record) {
            $code = $parser->generateFromRecord($record);
            if (!$code) {
                $skipped++;
                continue;
            }

            $code = $this->resolveDuplicate($code, $usedCodes);

            $usedCodes[$code] = true;
            $pending[] = [$record, $code];
        }

        if ($this->option('dry-run')) {
            foreach ($pending as [$record, $code]) {
                $this->line("[DRY-RUN] {$record->id}: {$record->nama_reksa_dana} -> {$code}");
            }
            $generated = count($pending);
        } else {
            foreach ($pending as [$record, $code]) {
                $record->kode_reksa_dana = $code;
                try {
                    $record->save();
                    $generated++;
                } catch (\Throwable $e) {
                    $skipped++;
                    if ($skipped <= 10) {
                        $this->warn("  Error {$record->id}: {$e->getMessage()}");
                    }
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
            ]
        );

        if ($this->option('dry-run')) {
            $this->warn('Dry-run mode: no changes were saved.');
        }

        return 0;
    }

    private function resolveDuplicate(string $code, array &$usedCodes): string
    {
        if (!isset($usedCodes[$code])) {
            return $code;
        }

        $seq = 0;
        $abbr4 = substr($code, 9, 4);  // original 4-char abbreviation

        while (isset($usedCodes[$code])) {
            $attempt = $this->sequenceFromAbbr(substr($code, 0, 9), $abbr4, substr($code, 13), $seq);
            if ($attempt === null) {
                return $code;
            }
            $code = $attempt;
            $seq++;
        }

        return $code;
    }

    private function sequenceFromAbbr(string $prefix, string $abbr4, string $suffix, int $n): ?string
    {
        $chars = self::SEQ_CHARS;
        $len = strlen($chars); // 36

        if ($n < $len) {
            return $prefix . substr($abbr4, 0, 3) . $chars[$n] . $suffix;
        }

        $n -= $len;
        $idx1 = intdiv($n, $len);
        $idx2 = $n % $len;
        if ($idx1 >= $len) return null;

        return $prefix . substr($abbr4, 0, 2) . $chars[$idx1] . $chars[$idx2] . $suffix;
    }
}
