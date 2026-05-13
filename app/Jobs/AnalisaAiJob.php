<?php

namespace App\Jobs;

use App\Models\AnalisaReksaDana;
use App\Services\GroqService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnalisaAiJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 30;
    public string $queue = 'ai';

    public function __construct(private int $analisaId) {}

    public function handle(GroqService $groq): void
    {
        $analisa = AnalisaReksaDana::with(['sektor', 'efek', 'kinerja', 'obligasi', 'bank'])
            ->findOrFail($this->analisaId);

        $narasi = $groq->generateNarasiAnalisa($analisa);

        $analisa->update(['ai_narasi' => $narasi]);
    }
}
