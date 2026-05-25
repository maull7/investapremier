<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesLapkeuAiErrors;
use App\Models\AnalisaSaham;
use App\Models\AnalisaObligasiKeuangan;
use App\Services\GroqService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class AnalisaLapkeuAiPlusJob implements ShouldQueue
{
    use HandlesLapkeuAiErrors;
    use Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        private int $analisaId,
        private string $instrumen,
    ) {
        $this->onQueue('ai');
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping("lapkeu-ai-plus-{$this->analisaId}"))->expireAfter(180)];
    }

    public function handle(GroqService $groq): void
    {
        $analisa = $this->resolveModel();

        if ($msg = $this->groqKeyError()) {
            $this->markPlusFailed($analisa, $msg);
            return;
        }

        if ($msg = $this->checkMethodExists($groq, 'generateNarasiLapkeuPlusStructured')) {
            $this->markPlusFailed($analisa, $msg);
            return;
        }

        $data = $this->buildData($analisa);
        $plusCheck = \App\Http\Controllers\AnalisaLapkeuController::assessPlusManualData($data, $this->instrumen);

        if (!$plusCheck['ready']) {
            $this->markPlusFailed($analisa, 'Data laporan keuangan tidak lengkap untuk Analisa AI Plus. ' . ($plusCheck['message'] ?? ''));
            return;
        }

        try {
            $result = $groq->generateNarasiLapkeuPlusStructured($data, $this->instrumen);

            if (empty($result['parsed']) && empty($result['raw'])) {
                $this->markPlusFailed($analisa, 'Respons AI Plus kosong atau tidak valid. Silakan coba lagi.');
                return;
            }

            $analisa->update([
                'ai_narasi_plus' => $result['raw'],
                'ai_output_plus' => $result['parsed'],
            ]);
        } catch (\Throwable $e) {
            Log::error('AnalisaLapkeuAiPlusJob gagal', [
                'analisa_id' => $this->analisaId,
                'instrumen'  => $this->instrumen,
                'error'      => $e->getMessage(),
            ]);

            $this->markPlusFailed($analisa, $this->friendlyError($e));
            throw $e;
        }
    }

    public function failed(?\Throwable $e): void
    {
        $analisa = $this->resolveModel(false);
        if (!$analisa) return;

        $output = $analisa->ai_output_plus ?? [];
        if (!empty($output['error'])) return;

        $this->markPlusFailed($analisa, $e ? $this->friendlyError($e) : 'Analisa AI Plus gagal setelah beberapa percobaan.');
    }

    private function resolveModel(bool $throw = true): AnalisaSaham|AnalisaObligasiKeuangan|null
    {
        $class = $this->instrumen === 'Obligasi' ? AnalisaObligasiKeuangan::class : AnalisaSaham::class;

        if ($throw) return $class::findOrFail($this->analisaId);

        return $class::find($this->analisaId);
    }

    private function buildData($analisa): array
    {
        $lapkeuFields = [
            'mata_uang', 'periode', 'catatan',
            'current_asset', 'cash_equivalents', 'account_receivable', 'inventories',
            'other_current_asset', 'fixed_asset', 'other_non_current_asset', 'total_asset',
            'current_liabilities', 'account_payable', 'accruals', 'short_term_loans',
            'current_maturities_of_long_term_loans', 'other_current_liabilities',
            'long_term_loans', 'other_non_current_liabilities',
            'total_non_current_liabilities', 'total_liabilities',
            'share_capital', 'additional_paid_in_capital', 'retained_earning', 'others',
            'non_controlling_interest', 'total_equity_equity_to_parent_entity', 'equity',
            'net_revenue', 'cost_of_good_sold', 'gross_income', 'operational_expense',
            'laba_operasional', 'other_income_expense', 'interest_expense', 'income_before_tax',
            'taxes', 'ebit', 'ebitda', 'net_income_attributable_to_non_controlling_interest',
            'net_income', 'eps', 'cash_flows_operating_activities', 'cash_flows_investment',
            'cash_flows_financing',
        ];

        $data = ['nama' => $analisa->nama_perusahaan ?? $analisa->nama_obligasi];

        if ($this->instrumen === 'Obligasi') {
            $data['kode'] = $analisa->kode_obligasi;
        } else {
            $data['kode'] = $analisa->kode_saham;
        }

        foreach ($lapkeuFields as $field) {
            $data[$field] = $analisa->{$field} ?? null;
        }

        if ($this->instrumen === 'Obligasi') {
            $data['rating'] = $analisa->rating;
            $data['kupon']  = $analisa->kupon;
            $data['ytm']    = $analisa->ytm;
        }

        $data['nama_perusahaan'] = $analisa->nama_perusahaan;
        $data['total_asset']     = $analisa->total_asset;
        $data['total_liabilities'] = $analisa->total_liabilities;
        $data['equity']          = $analisa->equity;
        $data['net_revenue']     = $analisa->net_revenue;
        $data['net_income']      = $analisa->net_income;

        return $data;
    }
}
