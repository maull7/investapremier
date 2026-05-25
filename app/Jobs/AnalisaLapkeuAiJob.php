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

class AnalisaLapkeuAiJob implements ShouldQueue
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
        return [(new WithoutOverlapping("lapkeu-ai-{$this->analisaId}"))->expireAfter(180)];
    }

    public function handle(GroqService $groq): void
    {
        $analisa = $this->resolveModel();

        if ($msg = $this->groqKeyError()) {
            $this->markStandardFailed($analisa, $msg);
            return;
        }

        if ($msg = $this->checkMethodExists($groq, 'generateNarasiLapkeuStructured')) {
            $this->markStandardFailed($analisa, $msg);
            return;
        }

        try {
            $data = $this->buildData($analisa);
            $result = $groq->generateNarasiLapkeuStructured($data, $this->instrumen);

            if (empty($result['parsed']) && empty($result['raw'])) {
                $this->markStandardFailed($analisa, 'Respons AI kosong atau tidak valid. Silakan coba lagi.');
                return;
            }

            $analisa->update([
                'ai_narasi' => $result['raw'],
                'ai_output' => $result['parsed'],
            ]);
        } catch (\Throwable $e) {
            Log::error('AnalisaLapkeuAiJob gagal', [
                'analisa_id' => $this->analisaId,
                'instrumen'  => $this->instrumen,
                'error'      => $e->getMessage(),
            ]);

            $this->markStandardFailed($analisa, $this->friendlyError($e));
            throw $e;
        }
    }

    public function failed(?\Throwable $e): void
    {
        $analisa = $this->resolveModel(false);
        if (!$analisa) return;

        $output = $analisa->ai_output ?? [];
        if (!empty($output['error'])) return;

        $this->markStandardFailed($analisa, $e ? $this->friendlyError($e) : 'Analisa AI gagal setelah beberapa percobaan.');
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
            if (!is_null($analisa->{$field} ?? null)) {
                $data[$field] = $analisa->{$field};
            }
        }

        if ($this->instrumen === 'Obligasi') {
            $data['rating'] = $analisa->rating;
            $data['kupon']  = $analisa->kupon;
            $data['ytm']    = $analisa->ytm;
        }

        return $data;
    }
}
