<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesAnalisaAiErrors;
use App\Models\AnalisaReksaDana;
use App\Services\GroqService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class AnalisaAiJob implements ShouldQueue
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
        return [(new WithoutOverlapping("analisa-ai-{$this->analisaId}"))->expireAfter(180)];
    }

    public function handle(GroqService $groq): void
    {
        $analisa = AnalisaReksaDana::with(['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank'])
            ->findOrFail($this->analisaId);

        if ($msg = $this->validateForStandardAi($analisa)) {
            $this->markStandardFailed($analisa, $msg);

            return;
        }

        if ($msg = $this->groqKeyError()) {
            $this->markStandardFailed($analisa, $msg);

            return;
        }

        if (!method_exists($groq, 'generateNarasiAnalisaStructured')) {
            $this->markStandardFailed($analisa, 'Fitur Analisa AI belum tersedia di server. Restart Horizon setelah deploy.');

            return;
        }

        try {
            $result = $groq->generateNarasiAnalisaStructured($analisa);

            if (empty($result['parsed']) && empty($result['raw'])) {
                $this->markStandardFailed($analisa, 'Respons AI kosong atau tidak valid. Silakan coba lagi.');

                return;
            }

            $analisa->update([
                'ai_narasi' => $result['raw'],
                'ai_output' => $result['parsed'],
            ]);
        } catch (\Throwable $e) {
            Log::error('AnalisaAiJob gagal', [
                'analisa_id' => $this->analisaId,
                'error'      => $e->getMessage(),
            ]);

            $this->markStandardFailed($analisa, $this->friendlyError($e));

            throw $e;
        }
    }

    public function failed(?\Throwable $e): void
    {
        $analisa = AnalisaReksaDana::find($this->analisaId);
        if (!$analisa) {
            return;
        }

        $output = $analisa->ai_output ?? [];
        if (!empty($output['error'])) {
            return;
        }

        $this->markStandardFailed($analisa, $e ? $this->friendlyError($e) : 'Analisa AI gagal setelah beberapa percobaan.');
    }
}
