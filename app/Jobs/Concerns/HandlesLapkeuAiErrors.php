<?php

namespace App\Jobs\Concerns;

use Illuminate\Support\Str;

trait HandlesLapkeuAiErrors
{
    protected function friendlyError(\Throwable $e): string
    {
        $msg = $e->getMessage();

        if (str_contains($msg, 'undefined method') && str_contains($msg, 'generateNarasiLapkeuPlusStructured')) {
            return 'Fitur Analisa AI Plus belum aktif di server. Jalankan deploy terbaru lalu restart worker: php artisan horizon:terminate';
        }

        if (str_contains($msg, 'undefined method') && str_contains($msg, 'generateNarasiLapkeuStructured')) {
            return 'Fitur Analisa AI belum aktif di server. Jalankan deploy terbaru lalu restart worker: php artisan horizon:terminate';
        }

        if (str_contains($msg, 'AI API error')) {
            return 'Layanan AI gagal merespons. Periksa OPENAI_API_KEY atau GROQ_API_KEY atau coba lagi nanti. ('.Str::limit($msg, 120).')';
        }

        return 'Gagal memproses: '.Str::limit($msg, 220);
    }

    protected function groqKeyError(): ?string
    {
        if (!config('services.openai.key') && !config('services.groq.key')) {
            return 'API AI belum dikonfigurasi. Set OPENAI_API_KEY (atau GROQ_API_KEY sebagai cadangan) di file .env.';
        }

        return null;
    }

    protected function markStandardFailed($analisa, string $message): void
    {
        $analisa->update([
            'ai_narasi' => null,
            'ai_output' => [
                'error'   => true,
                'message' => $message,
            ],
        ]);
    }

    protected function markPlusFailed($analisa, string $message): void
    {
        $analisa->update([
            'ai_narasi_plus' => null,
            'ai_output_plus' => [
                'error'   => true,
                'message' => $message,
            ],
        ]);
    }

    protected function checkMethodExists(object $groq, string $method): ?string
    {
        if (!method_exists($groq, $method)) {
            $label = match ($method) {
                'generateNarasiLapkeuStructured' => 'Analisa AI',
                'generateNarasiLapkeuPlusStructured' => 'Analisa AI Plus',
                default => 'Fitur AI',
            };

            return "{$label} belum tersedia di server. Restart Horizon setelah deploy.";
        }

        return null;
    }
}
