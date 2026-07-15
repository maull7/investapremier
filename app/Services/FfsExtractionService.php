<?php

namespace App\Services;

use App\Models\FfsExtractionResult;
use App\Models\HargaReksaDana;
use App\Models\MutualFundAssetAllocation;
use App\Models\MutualFundPortfolioComposition;
use App\Models\ReksaDana;
use App\Models\ReksaDanaDocument;

class FfsExtractionService
{
    public function __construct(
        private GroqService $groqService,
        private ProspektusParserService $parserService,
    ) {
    }

    public function extractAndSave(ReksaDanaDocument $document, ?int $userId = null, array $parserLocks = []): array
    {
        if ($document->document_type !== ReksaDanaDocument::TYPE_FFS) {
            throw new \RuntimeException('Dokumen bukan tipe FFS.');
        }

        if ($document->parsedPages->isEmpty()) {
            throw new \RuntimeException('Dokumen FFS belum diparse. Lakukan parse dokumen terlebih dahulu.');
        }

        $fullText = $this->parserService->getAllText($document);

        if (empty(trim($fullText))) {
            throw new \RuntimeException('Teks dokumen FFS kosong.');
        }

        $aiResult = $this->groqService->parseFfsPdf($fullText, null);

        $result = FfsExtractionResult::updateOrCreate(
            [
                'reksa_dana_document_id' => $document->id,
            ],
            [
                'reksa_dana_id' => $document->reksa_dana_id,
                'created_by'    => $userId,
                'ffs_month'     => $aiResult['ffs_bulan'] ?? $document->ffs_month,
                'ffs_year'      => $aiResult['ffs_tahun'] ?? $document->ffs_year,
                'tanggal_data'  => $this->normalizeDate($aiResult['tanggal_data'] ?? null),
                'extracted_data' => $aiResult,
            ]
        );

        $this->syncToTables($document, $aiResult, $parserLocks);

        return [
            'saved'    => true,
            'result'   => $result,
            'fields'   => array_keys($aiResult),
            'ai_data'  => $aiResult,
        ];
    }

    private function syncToTables(ReksaDanaDocument $document, array $data, array $parserLocks = []): void
    {
        $fund = ReksaDana::find($document->reksa_dana_id);
        if (!$fund) return;

        $tanggalData = $this->normalizeDate($data['tanggal_data'] ?? null) ?? now()->toDateString();

        $updates = [];

        $lockedRingkasan = in_array('ringkasan', $parserLocks);
        $lockedBiaya = in_array('biaya', $parserLocks);
        $lockedInfo = in_array('info', $parserLocks);

        if (!$lockedRingkasan) {
            if (isset($data['total_aum'])) $updates['aum'] = $data['total_aum'];
            if (isset($data['total_aum'])) $updates['aum_published_date'] = $tanggalData;
            if (isset($data['nab_per_unit'])) $updates['nab_per_unit'] = $data['nab_per_unit'];
            if (isset($data['nab_per_unit'])) $updates['tanggal_nab'] = $tanggalData;
            if (isset($data['unit_penyertaan'])) $updates['total_unit'] = $data['unit_penyertaan'];
            if (isset($data['return_ytd'])) $updates['return_ytd'] = $this->toDecimal($data['return_ytd']);
            if (isset($data['return_1y'])) $updates['return_1y'] = $this->toDecimal($data['return_1y']);
            if (isset($data['return_1m'])) $updates['return_1m'] = $this->toDecimal($data['return_1m']);
            if (isset($data['total_return'])) $updates['return_inception'] = $this->toDecimal($data['total_return']);
            if (isset($data['return_5y'])) $updates['return_5y'] = $this->toDecimal($data['return_5y']);
        }

        if (!$lockedBiaya) {
            if (isset($data['management_fee'])) $updates['management_fee'] = $data['management_fee'];
            if (isset($data['custodian_fee'])) $updates['custodian_fee'] = $data['custodian_fee'];
            if (isset($data['subscription_fee'])) $updates['subscription_fee'] = $data['subscription_fee'];
            if (isset($data['redemption_fee'])) $updates['redemption_fee'] = $data['redemption_fee'];
            if (isset($data['switching_fee'])) $updates['switching_fee'] = $data['switching_fee'];
            if (isset($data['expense_ratio'])) $updates['expense_ratio'] = $data['expense_ratio'];
        }

        if (!$lockedInfo) {
            if (isset($data['benchmark'])) $updates['benchmark'] = $data['benchmark'];
            if (!empty($data['isin_code'])) $updates['isin_code'] = $data['isin_code'];
            if (isset($data['tujuan_investasi'])) $updates['tujuan_investasi'] = $data['tujuan_investasi'];
            if (isset($data['kebijakan_investasi'])) $updates['kebijakan_investasi'] = $data['kebijakan_investasi'];
            if (isset($data['bank_kustodian'])) $updates['custodian_bank'] = $data['bank_kustodian'];
            if (isset($data['tanggal_peluncuran'])) $updates['launch_date'] = $data['tanggal_peluncuran'];
            if (isset($data['mata_uang'])) $updates['mata_uang'] = $data['mata_uang'];
            if (isset($data['risk_category'])) $updates['risk_category'] = $data['risk_category'];
            if (isset($data['risk_descriptions']) && is_array($data['risk_descriptions'])) {
                $updates['risk_description'] = implode("\n", $data['risk_descriptions']);
            }
            if (isset($data['sharpe_ratio'])) $updates['sharpe_ratio_1y'] = $data['sharpe_ratio'];
            if (isset($data['standard_deviation'])) $updates['stdev_1y'] = $data['standard_deviation'];
            if (isset($data['beta'])) $updates['beta_1y'] = $data['beta'];
            if (isset($data['max_drawdown'])) $updates['max_drawdown_1y'] = $this->toDecimal($data['max_drawdown']);
        }

        if (!$lockedRingkasan) {
            $updates['last_fund_factsheet'] = $tanggalData;
            $updates['last_updated_portfolio'] = $tanggalData;
        }

        if (!empty($updates)) {
            $fund->update($updates);
        }

        if (!$lockedRingkasan) {
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
        }

        if (!empty($data['alokasi_aset']) && is_array($data['alokasi_aset'])) {
            $aa = MutualFundAssetAllocation::updateOrCreate(
                [
                    'reksa_dana_id' => $fund->id,
                    'period_date' => $tanggalData,
                ],
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
        if (!empty($data['efek']) && is_array($data['efek'])) {
            foreach ($data['efek'] as $item) {
                $holdings[] = [
                    'security_name' => $item['kode_efek'] ?: $item['nama_efek'],
                    'security_type' => 'Saham',
                    'weight_percent' => $item['bobot'] ?? null,
                ];
            }
        }
        if (!empty($data['obligasi']) && is_array($data['obligasi'])) {
            foreach ($data['obligasi'] as $item) {
                $holdings[] = [
                    'security_name' => $item['kode_obligasi'] ?: $item['nama_obligasi'],
                    'security_type' => 'Obligasi',
                    'weight_percent' => $item['bobot'] ?? null,
                ];
            }
        }
        if (!empty($data['sukuk']) && is_array($data['sukuk'])) {
            foreach ($data['sukuk'] as $item) {
                $holdings[] = [
                    'security_name' => $item['kode_sukuk'] ?: $item['nama_sukuk'],
                    'security_type' => 'Sukuk',
                    'weight_percent' => $item['bobot'] ?? null,
                ];
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

    private function toDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '' || $value === false) return null;
        // AI returns raw percentage (5.5 = 5.5%), DB stores Pasardana format (0.055)
        return (float) $value / 100;
    }

    private function normalizeDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Tangani format umum: YYYY-MM-DD, DD/MM/YYYY, dsb.
        $date = trim($date);
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);
        if ($parsed && $parsed->format('Y-m-d') === $date) {
            return $date;
        }

        $parsed = \DateTime::createFromFormat('d/m/Y', $date);
        if ($parsed) {
            return $parsed->format('Y-m-d');
        }

        $parsed = \DateTime::createFromFormat('d-m-Y', $date);
        if ($parsed) {
            return $parsed->format('Y-m-d');
        }

        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }
}
