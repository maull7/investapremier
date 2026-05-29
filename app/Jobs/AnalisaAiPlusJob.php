<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesAnalisaAiErrors;
use App\Models\AnalisaReksaDana;
use App\Services\GroqService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class AnalisaAiPlusJob implements ShouldQueue
{
    use HandlesAnalisaAiErrors;
    use Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(private int $analisaId)
    {
        $this->onQueue('ai');
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping("analisa-ai-plus-{$this->analisaId}"))->expireAfter(180)];
    }

    public function handle(GroqService $groq): void
    {
        $analisa = AnalisaReksaDana::with(['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank'])
            ->findOrFail($this->analisaId);

        if ($msg = $this->validateForPlusAi($analisa)) {
            $this->markPlusFailed($analisa, $msg);

            return;
        }

        if ($msg = $this->groqKeyError()) {
            $this->markPlusFailed($analisa, $msg);

            return;
        }

        if (!method_exists($groq, 'generateAnalisaPlusStructured')) {
            $this->markPlusFailed($analisa, 'Fitur Analisa AI Plus belum tersedia di server. Restart Horizon setelah deploy.');

            return;
        }

        try {
            $result = $groq->generateAnalisaPlusStructured($analisa);

            if (empty($result['parsed']) && empty($result['raw'])) {
                $this->markPlusFailed($analisa, 'Respons AI kosong atau tidak valid. Silakan coba lagi dari form.');

                return;
            }

            $analisa->update([
                'ai_narasi_plus' => $result['raw'],
                'ai_output_plus' => $result['parsed'],
            ]);
        } catch (\Throwable $e) {
            Log::error('AnalisaAiPlusJob gagal', [
                'analisa_id' => $this->analisaId,
                'error'      => $e->getMessage(),
            ]);

            $this->markPlusFailed($analisa, $this->friendlyError($e));

            throw $e;
        }
    }

    public function failed(?\Throwable $e): void
    {
        $analisa = AnalisaReksaDana::find($this->analisaId);
        if (!$analisa) {
            return;
        }

        $output = $analisa->ai_output_plus ?? [];
        if (!empty($output['error'])) {
            return;
        }

        $this->markPlusFailed($analisa, $e ? $this->friendlyError($e) : 'Analisa AI Plus gagal setelah beberapa percobaan.');
    }
}
