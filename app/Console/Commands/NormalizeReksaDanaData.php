<?php

namespace App\Console\Commands;

use App\Models\InvestmentManager;
use App\Models\ReksaDana;
use App\Services\KodeReksaDanaParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeReksaDanaData extends Command
{
    protected $signature = 'reksa-dana:normalize
        {--dry-run : Count only, no changes}
        {--force-kode-mi : Regenerate MI kode_mi even if already exists}';

    protected $description = 'Normalize ReksaDana data: MI linkage, kode_mi, jenis, kategori_produk, then generate kode';

    private array $stats = [
        'mi_linked' => 0,
        'mi_created' => 0,
        'kode_mi_generated' => 0,
        'jenis_normalized' => 0,
        'kp_normalized' => 0,
        'kode_generated' => 0,
        'errors' => 0,
    ];

    public function handle(KodeReksaDanaParser $parser): int
    {
        $dryRun = $this->option('dry-run');

        $this->line('=== Step 1: Link records to Investment Managers by name ===');
        $this->linkRecordsToMi($dryRun);

        $this->line('=== Step 2: Create missing Investment Manager records ===');
        $this->createMissingMi($dryRun);

        $this->line('=== Step 3: Generate kode_mi for MIs without one ===');
        $this->generateKodeMi($parser, $dryRun);

        $this->line('=== Step 4: Normalize jenis values ===');
        $this->normalizeJenis($dryRun);

        $this->line('=== Step 5: Normalize kategori_produk values ===');
        $this->normalizeKategoriProduk($dryRun);

        $this->line('=== Step 6: Generate kode_reksa_dana ===');
        $this->generateKode($parser, $dryRun);

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['MI linked (by name)', $this->stats['mi_linked']],
                ['MI created', $this->stats['mi_created']],
                ['MI kode_mi generated', $this->stats['kode_mi_generated']],
                ['Jenis normalized', $this->stats['jenis_normalized']],
                ['Kategori produk normalized', $this->stats['kp_normalized']],
                ['Kode generated', $this->stats['kode_generated']],
                ['Errors', $this->stats['errors']],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry-run mode: no changes were saved.');
        }

        return 0;
    }

    private function linkRecordsToMi(bool $dryRun): void
    {
        $records = ReksaDana::whereNull('kode_reksa_dana')
            ->whereNull('investment_manager_id')
            ->whereNotNull('nama_manajer_investasi')
            ->where('nama_manajer_investasi', '!=', '')
            ->get(['id', 'nama_reksa_dana', 'nama_manajer_investasi']);

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $rd) {
            $mi = $this->findMiByName($rd->nama_manajer_investasi);
            if ($mi) {
                $this->stats['mi_linked']++;
                if (!$dryRun) {
                    $rd->investment_manager_id = $mi->id;
                    $rd->save();
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function createMissingMi(bool $dryRun): void
    {
        $names = ReksaDana::whereNull('kode_reksa_dana')
            ->whereNull('investment_manager_id')
            ->whereNotNull('nama_manajer_investasi')
            ->where('nama_manajer_investasi', '!=', '')
            ->distinct()
            ->pluck('nama_manajer_investasi');

        $bar = $this->output->createProgressBar($names->count());
        $bar->start();

        foreach ($names as $name) {
            if ($this->findMiByName($name)) {
                $bar->advance();
                continue;
            }

            $normalizedName = $this->normalizeMiName($name);
            if (!$normalizedName) {
                $bar->advance();
                continue;
            }

            $this->stats['mi_created']++;
            if (!$dryRun) {
                $mi = InvestmentManager::create([
                    'name' => $normalizedName,
                    'kode_mi' => null,
                ]);

                ReksaDana::whereNull('kode_reksa_dana')
                    ->whereNull('investment_manager_id')
                    ->where('nama_manajer_investasi', $name)
                    ->update(['investment_manager_id' => $mi->id]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function generateKodeMi(KodeReksaDanaParser $parser, bool $dryRun): void
    {
        $miIds = ReksaDana::whereNull('kode_reksa_dana')
            ->whereNotNull('investment_manager_id')
            ->distinct()
            ->pluck('investment_manager_id');

        $misl = InvestmentManager::where(function ($q) {
            $q->whereNull('kode_mi')->orWhere('kode_mi', '');
        });

        if (!$this->option('force-kode-mi')) {
            $misl->whereIn('id', $miIds);
        }

        $misl = $misl->get();

        $bar = $this->output->createProgressBar($misl->count());
        $bar->start();

        foreach ($misl as $mi) {
            $code = $this->generateKodeMiFromName($mi);
            if (!$code) {
                $this->stats['errors']++;
                $bar->advance();
                continue;
            }

            $this->stats['kode_mi_generated']++;
            if (!$dryRun) {
                $mi->kode_mi = $code;
                $mi->save();
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function normalizeJenis(bool $dryRun): void
    {
        $nonStandard = array_keys(KodeReksaDanaParser::JENIS_NORMALIZE);
        $records = ReksaDana::whereNull('kode_reksa_dana')
            ->whereIn('jenis', $nonStandard)
            ->get(['id', 'jenis']);

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $rd) {
            $normalized = KodeReksaDanaParser::JENIS_NORMALIZE[$rd->jenis] ?? null;
            if ($normalized && $normalized !== $rd->jenis) {
                $this->stats['jenis_normalized']++;
                if (!$dryRun) {
                    $rd->jenis = $normalized;
                    $rd->save();
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function normalizeKategoriProduk(bool $dryRun): void
    {
        $nonStandard = array_keys(KodeReksaDanaParser::KATEGORI_PRODUK_NORMALIZE);
        $records = ReksaDana::whereNull('kode_reksa_dana')
            ->whereIn('kategori_produk', $nonStandard)
            ->get(['id', 'kategori_produk']);

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $rd) {
            $normalized = KodeReksaDanaParser::KATEGORI_PRODUK_NORMALIZE[$rd->kategori_produk] ?? null;
            if ($normalized && $normalized !== $rd->kategori_produk) {
                $this->stats['kp_normalized']++;
                if (!$dryRun) {
                    $rd->kategori_produk = $normalized;
                    $rd->save();
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function generateKode(KodeReksaDanaParser $parser, bool $dryRun): void
    {
        $records = ReksaDana::whereNull('kode_reksa_dana')
            ->whereNotNull('investment_manager_id')
            ->get();

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $rd) {
            $code = $parser->generateFromRecord($rd);
            if ($code) {
                $this->stats['kode_generated']++;
                if (!$dryRun) {
                    $rd->kode_reksa_dana = $code;
                    try {
                        $rd->save();
                    } catch (\Throwable $e) {
                        $this->stats['errors']++;
                        $this->warn("  Error saving kode for {$rd->id}: {$e->getMessage()}");
                    }
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function findMiByName(string $name): ?InvestmentManager
    {
        $normalized = $this->normalizeMiName($name);
        if (!$normalized) {
            return null;
        }

        $mi = InvestmentManager::where('name', $name)->first();
        if ($mi) {
            return $mi;
        }

        $mi = InvestmentManager::where('name', $normalized)->first();
        if ($mi) {
            return $mi;
        }

        $alt = $this->alternativeName($name);
        if ($alt) {
            $mi = InvestmentManager::where('name', $alt)->first();
            if ($mi) {
                return $mi;
            }
        }

        $alt2 = $this->alternativeName($normalized);
        if ($alt2) {
            $mi = InvestmentManager::where('name', $alt2)->first();
            if ($mi) {
                return $mi;
            }
        }

        return InvestmentManager::whereRaw("REPLACE(REPLACE(name, ', ', ','), 'PT ', '') = ?", [
            str_replace([', ', 'PT '], [',', ''], $normalized),
        ])->orWhereRaw("REPLACE(REPLACE(name, ', ', ','), 'PT ', '') = ?", [
            str_replace([', ', 'PT '], [',', ''], $name),
        ])->first();
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

    private function generateKodeMiFromName(InvestmentManager $mi): ?string
    {
        $name = $mi->name;
        $name = preg_replace('/[,\s]+/', ' ', $name);
        $name = preg_replace('/\b(PT|Tbk|Ltd|Inc|Limited|Investment|Manajemen|Management|Asset|Aset|Capital|Indonesia|Select|Global)\b/i', '', $name);
        $name = preg_replace('/\s+/', '', $name);
        $name = strtoupper(trim($name));

        if (strlen($name) < 2) {
            $name = 'MI';
        }

        $prefix = substr($name, 0, 3);
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }

        $existing = InvestmentManager::where('kode_mi', 'like', $prefix . '%')
            ->pluck('kode_mi');

        $seq = 1;
        while ($existing->contains($prefix . str_pad((string) $seq, 2, '0', STR_PAD_LEFT))) {
            $seq++;
            if ($seq > 99) {
                $prefix = substr($name, 0, 2) . 'X';
                $seq = 1;
            }
            if ($seq > 999) {
                return null;
            }
        }

        return $prefix . str_pad((string) $seq, 2, '0', STR_PAD_LEFT);
    }
}
