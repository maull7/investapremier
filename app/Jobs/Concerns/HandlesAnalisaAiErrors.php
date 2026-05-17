<?php

namespace App\Jobs\Concerns;

use App\Models\AnalisaReksaDana;
use App\Services\AnalisaAiValidator;
use Illuminate\Support\Str;

trait HandlesAnalisaAiErrors
{
    protected function friendlyError(\Throwable $e): string
    {
        $msg = $e->getMessage();

        if (str_contains($msg, 'undefined method') && str_contains($msg, 'generateAnalisaPlusStructured')) {
            return 'Fitur Analisa AI Plus belum aktif di server. Jalankan deploy terbaru lalu restart worker: php artisan horizon:terminate';
        }

        if (str_contains($msg, 'undefined method') && str_contains($msg, 'generateNarasiAnalisaStructured')) {
            return 'Fitur Analisa AI belum aktif di server. Jalankan deploy terbaru lalu restart worker: php artisan horizon:terminate';
        }

        if (str_contains($msg, 'Groq API error')) {
            return 'Layanan AI gagal merespons. Periksa GROQ_API_KEY atau coba lagi nanti. ('.Str::limit($msg, 120).')';
        }

        return 'Gagal memproses: '.Str::limit($msg, 220);
    }

    protected function groqKeyError(): ?string
    {
        if (!config('services.groq.key')) {
            return 'API Groq belum dikonfigurasi. Set GROQ_API_KEY di file .env.';
        }

        return null;
    }

    protected function validateForStandardAi(AnalisaReksaDana $analisa): ?string
    {
        if (blank($analisa->nama_reksa_dana) || blank($analisa->jenis_reksa_dana)) {
            return 'Data reksa dana tidak lengkap (nama/jenis kosong).';
        }

        return null;
    }

    protected function validateForPlusAi(AnalisaReksaDana $analisa): ?string
    {
        if ($err = $this->validateForStandardAi($analisa)) {
            return $err;
        }

        if (!AnalisaAiValidator::hasPlusManualData($analisa)) {
            return AnalisaAiValidator::plusIncompleteMessage($analisa);
        }

        return null;
    }

    protected function markStandardFailed(AnalisaReksaDana $analisa, string $message): void
    {
        $analisa->update([
            'ai_narasi' => null,
            'ai_output' => [
                'error'   => true,
                'message' => $message,
            ],
        ]);
    }

    protected function markPlusFailed(AnalisaReksaDana $analisa, string $message): void
    {
        $analisa->update([
            'ai_narasi_plus' => null,
            'ai_output_plus' => [
                'error'   => true,
                'message' => $message,
            ],
        ]);
    }
}
