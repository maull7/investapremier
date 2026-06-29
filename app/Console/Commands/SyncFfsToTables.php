<?php

namespace App\Console\Commands;

use App\Models\FfsExtractionResult;
use App\Models\ReksaDana;
use App\Models\HargaReksaDana;
use App\Models\MutualFundAssetAllocation;
use App\Models\MutualFundPortfolioComposition;
use Illuminate\Console\Command;

class SyncFfsToTables extends Command
{
    protected $signature = 'ffs:sync
        {--id= : Sync specific FFS extraction result ID}
        {--fund-id= : Sync all FFS for a specific fund ID}
        {--dry-run : Preview only, no changes}';

    protected $description = 'Sync existing FFS extraction results to related tables';

    public function handle(): int
    {
        $query = FfsExtractionResult::with('document');

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }
        if ($fundId = $this->option('fund-id')) {
            $query->where('reksa_dana_id', $fundId);
        }

        $results = $query->get();

        if ($results->isEmpty()) {
            $this->warn('No FFS extraction results found.');
            return 0;
        }

        $this->info("Found {$results->count()} FFS extraction(s).");
        $synced = 0;
        $errors = 0;

        foreach ($results as $extraction) {
            $data = $extraction->extracted_data;
            if (!is_array($data) || empty($data)) {
                $this->warn("  [{$extraction->id}] extracted_data is empty, skipping.");
                continue;
            }

            if (!$extraction->document) {
                $this->warn("  [{$extraction->id}] document not found, skipping.");
                continue;
            }

            $fund = ReksaDana::find($extraction->reksa_dana_id);
            if (!$fund) {
                $this->warn("  [{$extraction->id}] fund not found, skipping.");
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  [{$extraction->id}] Would sync fund #{$fund->id} {$fund->nama_reksa_dana}");
                $synced++;
                continue;
            }

            try {
                $this->syncOne($fund, $extraction, $data);
                $this->info("  [{$extraction->id}] Synced: {$fund->nama_reksa_dana}");
                $synced++;
            } catch (\Throwable $e) {
                $this->error("  [{$extraction->id}] Error: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Synced', $synced],
                ['Errors', $errors],
            ]
        );

        return $errors > 0 ? 1 : 0;
    }

    private function syncOne(ReksaDana $fund, FfsExtractionResult $extraction, array $data): void
    {
        $tanggalData = $this->normalizeDate($data['tanggal_data'] ?? null) ?? now()->toDateString();
        $updates = [];

        if (isset($data['total_aum'])) $updates['aum'] = $data['total_aum'];
        if (isset($data['total_aum'])) $updates['aum_published_date'] = $tanggalData;
        if (isset($data['nab_per_unit'])) $updates['nab_per_unit'] = $data['nab_per_unit'];
        if (isset($data['nab_per_unit'])) $updates['tanggal_nab'] = $tanggalData;
        if (isset($data['unit_penyertaan'])) $updates['total_unit'] = $data['unit_penyertaan'];
        if (isset($data['return_ytd'])) $updates['return_ytd'] = $this->toDecimal($data['return_ytd']);
        if (isset($data['return_1y'])) $updates['return_1y'] = $this->toDecimal($data['return_1y']);
        if (isset($data['return_1m'])) $updates['return_1m'] = $this->toDecimal($data['return_1m']);
        if (isset($data['total_return'])) $updates['return_inception'] = $this->toDecimal($data['total_return']);
        if (isset($data['management_fee'])) $updates['management_fee'] = $data['management_fee'];
        if (isset($data['custodian_fee'])) $updates['custodian_fee'] = $data['custodian_fee'];
        if (isset($data['benchmark'])) $updates['benchmark'] = $data['benchmark'];
        if (isset($data['tujuan_investasi'])) $updates['tujuan_investasi'] = $data['tujuan_investasi'];
        if (isset($data['kebijakan_investasi'])) $updates['kebijakan_investasi'] = $data['kebijakan_investasi'];
        if (isset($data['bank_kustodian'])) $updates['custodian_bank'] = $data['bank_kustodian'];
        if (isset($data['tanggal_peluncuran'])) $updates['launch_date'] = $data['tanggal_peluncuran'];
        if (isset($data['mata_uang'])) $updates['mata_uang'] = $data['mata_uang'];
        $updates['last_fund_factsheet'] = $tanggalData;
        $updates['last_updated_portfolio'] = $tanggalData;

        if (!empty($updates)) {
            $fund->update($updates);
        }

        $hargaData = [
            'nab_per_unit' => $data['nab_per_unit'] ?? null,
            'aum' => $data['total_aum'] ?? null,
            'unit_participation' => $data['unit_penyertaan'] ?? null,
        ];
        if (array_filter($hargaData, fn($v) => $v !== null)) {
            HargaReksaDana::updateOrCreate(
                ['reksa_dana_id' => $fund->id, 'tanggal' => $tanggalData],
                $hargaData
            );
        }

        if (!empty($data['alokasi_aset']) && is_array($data['alokasi_aset'])) {
            $aa = MutualFundAssetAllocation::updateOrCreate(
                ['reksa_dana_id' => $fund->id, 'period_date' => $tanggalData],
                []
            );
            foreach ($data['alokasi_aset'] as $item) {
                $nama = strtolower($item['nama_aset'] ?? '');
                $persen = $item['persentase'] ?? null;
                if ($persen === null) continue;
                if (str_contains($nama, 'saham') || str_contains($nama, 'ekuitas') || str_contains($nama, 'equity')) {
                    $aa->equity_percent = $persen;
                } elseif (str_contains($nama, 'obligasi') || str_contains($nama, 'pendapatan tetap') || str_contains($nama, 'fixed income') || str_contains($nama, 'efek utang') || str_contains($nama, 'bond')) {
                    $aa->bond_percent = $persen;
                } elseif (str_contains($nama, 'uang') || str_contains($nama, 'money market') || str_contains($nama, 'pasar uang') || str_contains($nama, 'deposito')) {
                    $aa->money_market_percent = $persen;
                } elseif (str_contains($nama, 'kas') || str_contains($nama, 'cash') || str_contains($nama, 'bank')) {
                    $aa->cash_percent = $persen;
                }
            }
            $aa->save();
        }

        $holdings = [];
        foreach (['efek' => 'Saham', 'obligasi' => 'Obligasi', 'sukuk' => 'Sukuk'] as $key => $type) {
            if (!empty($data[$key]) && is_array($data[$key])) {
                foreach ($data[$key] as $item) {
                    $codeField = $key === 'efek' ? 'kode_efek' : ($key === 'obligasi' ? 'kode_obligasi' : 'kode_sukuk');
                    $nameField = $key === 'efek' ? 'nama_efek' : ($key === 'obligasi' ? 'nama_obligasi' : 'nama_sukuk');
                    $holdings[] = [
                        'security_name' => $item[$codeField] ?: $item[$nameField],
                        'security_type' => $type,
                        'weight_percent' => $item['bobot'] ?? null,
                    ];
                }
            }
        }
        if (!empty($data['bank']) && is_array($data['bank'])) {
            foreach ($data['bank'] as $item) {
                $holdings[] = [
                    'security_name' => $item['nama_bank'] ?? 'Bank',
                    'security_type' => 'Deposito',
                    'weight_percent' => $item['bobot'] ?? null,
                ];
            }
        }

        if (!empty($holdings)) {
            MutualFundPortfolioComposition::where('reksa_dana_id', $fund->id)
                ->where('period_date', $tanggalData)
                ->delete();
            foreach ($holdings as $h) {
                MutualFundPortfolioComposition::create([
                    'reksa_dana_id' => $fund->id,
                    'period_date' => $tanggalData,
                    'security_name' => $h['security_name'],
                    'security_type' => $h['security_type'],
                    'weight_percent' => $h['weight_percent'],
                ]);
            }
        }
    }

    private function normalizeDate(?string $date): ?string
    {
        if (empty($date)) return null;
        $date = trim($date);
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);
        if ($parsed && $parsed->format('Y-m-d') === $date) return $date;
        $parsed = \DateTime::createFromFormat('d/m/Y', $date);
        if ($parsed) return $parsed->format('Y-m-d');
        $parsed = \DateTime::createFromFormat('d-m-Y', $date);
        if ($parsed) return $parsed->format('Y-m-d');
        $timestamp = strtotime($date);
        if ($timestamp !== false) return date('Y-m-d', $timestamp);
        return null;
    }

    private function toDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '' || $value === false) return null;
        return (float) $value / 100;
    }
}
