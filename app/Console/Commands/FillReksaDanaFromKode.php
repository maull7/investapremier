<?php

namespace App\Console\Commands;

use App\Models\InvestmentManager;
use App\Models\ReksaDana;
use App\Services\KodeReksaDanaParser;
use Illuminate\Console\Command;

class FillReksaDanaFromKode extends Command
{
    protected $signature = 'reksa-dana:fill-from-kode
        {--dry-run : Count only, no updates}
        {--force : Re-fill even if fields already populated}';

    protected $description = 'Fill missing data fields (jenis, kategori_produk, kelas, etc.) from existing 17-char kode_reksa_dana';

    private array $stats = [
        'processed' => 0,
        'filled' => 0,
        'skipped_no_kode' => 0,
        'skipped_invalid_kode' => 0,
        'skipped_complete' => 0,
        'mi_matched' => 0,
        'errors' => 0,
    ];

    public function handle(KodeReksaDanaParser $parser): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $records = ReksaDana::whereNotNull('kode_reksa_dana')
            ->where('kode_reksa_dana', '!=', '')
            ->get();

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $rd) {
            $this->stats['processed']++;

            if (!$parser->isValidKode($rd->kode_reksa_dana)) {
                $this->stats['skipped_invalid_kode']++;
                $bar->advance();
                continue;
            }

            if (!$force && $this->isComplete($rd)) {
                $this->stats['skipped_complete']++;
                $bar->advance();
                continue;
            }

            $parsed = $parser->databaseAttributes($rd->kode_reksa_dana);
            if (empty($parsed)) {
                $this->stats['skipped_invalid_kode']++;
                $bar->advance();
                continue;
            }

            $updateData = [];

            $fields = ['nama_manajer_investasi', 'jenis', 'kategori_produk', 'kelas', 'mata_uang'];
            foreach ($fields as $field) {
                if (($force || empty($rd->{$field})) && !empty($parsed[$field])) {
                    $updateData[$field] = $parsed[$field];
                }
            }

            if (!empty($parsed['kategori'])) {
                if ($force || empty($rd->kategori) || !is_array($rd->kategori) || count($rd->kategori) === 0) {
                    $updateData['kategori'] = $parsed['kategori'];
                }
            }

            $miId = $this->resolveOrCreateMi($rd, $parser, $parsed);
            if ($miId && ($force || empty($rd->investment_manager_id))) {
                $updateData['investment_manager_id'] = $miId;
                $this->stats['mi_matched']++;
            }

            if (empty($updateData)) {
                $bar->advance();
                continue;
            }

            $this->stats['filled']++;

            if (!$dryRun) {
                try {
                    $rd->update($updateData);
                } catch (\Throwable $e) {
                    $this->stats['errors']++;
                    if ($this->stats['errors'] <= 10) {
                        $this->warn("  Error {$rd->id}: {$e->getMessage()}");
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total processed', $this->stats['processed']],
                ['Filled (data updated)', $this->stats['filled']],
                ['MI matched from kode', $this->stats['mi_matched']],
                ['Skipped (already complete)', $this->stats['skipped_complete']],
                ['Skipped (invalid kode)', $this->stats['skipped_invalid_kode']],
                ['Errors', $this->stats['errors']],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry-run mode: no changes were saved.');
        }

        return 0;
    }

    private function resolveOrCreateMi(ReksaDana $rd, KodeReksaDanaParser $parser, array $parsed): ?int
    {
        if (!empty($parsed['investment_manager_id'])) {
            return $parsed['investment_manager_id'];
        }

        $kodeMi = $parsed['kode_mi'] ?? null;
        if ($kodeMi) {
            $mi = InvestmentManager::where('kode_mi', $kodeMi)->first();
            if ($mi) {
                if (!empty($parsed['nama_manajer_investasi'])) {
                    $rd->nama_manajer_investasi = $parsed['nama_manajer_investasi'];
                }
                return $mi->id;
            }
        }

        $namaMi = $rd->nama_manajer_investasi ?? $parsed['nama_manajer_investasi'] ?? null;
        if ($namaMi) {
            $mi = $this->findMiByName($namaMi);
            if ($mi) {
                if (!$mi->kode_mi && $kodeMi) {
                    $mi->update(['kode_mi' => $kodeMi]);
                }
                return $mi->id;
            }

            $normalized = $this->normalizeMiName($namaMi);
            if ($normalized) {
                $mi = InvestmentManager::create([
                    'name' => $normalized,
                    'kode_mi' => $kodeMi,
                ]);
                return $mi->id;
            }
        }

        return null;
    }

    private function findMiByName(string $name): ?InvestmentManager
    {
        $mi = InvestmentManager::where('name', $name)->first();
        if ($mi) return $mi;

        $normalized = $this->normalizeMiName($name);
        if ($normalized) {
            $mi = InvestmentManager::where('name', $normalized)->first();
            if ($mi) return $mi;
        }

        $alt = $this->alternativeName($name);
        if ($alt) {
            $mi = InvestmentManager::where('name', $alt)->first();
            if ($mi) return $mi;
        }

        if ($normalized) {
            $alt2 = $this->alternativeName($normalized);
            if ($alt2) {
                $mi = InvestmentManager::where('name', $alt2)->first();
                if ($mi) return $mi;
            }
        }

        return null;
    }

    private function normalizeMiName(string $name): string
    {
        $name = trim($name);
        if (empty($name) || $name === 'TEST' || $name === '-') {
            return '';
        }

        $name = preg_replace('/\s+/', ' ', $name);

        if (!str_contains($name, ',') && !str_contains($name, 'PT') && !str_contains($name, 'Tbk')) {
            $name .= ', PT';
        }

        return $name;
    }

    private function alternativeName(string $name): string
    {
        if (str_contains($name, ', PT')) {
            return str_replace(', PT', ', PT', $name);
        }
        if (str_contains($name, 'PT ')) {
            return preg_replace('/^PT\s+/', '', $name) . ', PT';
        }
        return '';
    }

    private function isComplete(ReksaDana $rd): bool
    {
        if (empty($rd->jenis)) return false;
        if (empty($rd->kategori_produk)) return false;
        if (empty($rd->kelas)) return false;
        if (empty($rd->mata_uang)) return false;
        if (empty($rd->investment_manager_id)) return false;
        if (empty($rd->kategori) || !is_array($rd->kategori) || count($rd->kategori) === 0) return false;

        return true;
    }
}
