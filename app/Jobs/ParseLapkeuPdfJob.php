<?php

namespace App\Jobs;

use App\Models\LapkeuPdfExtraction;
use App\Services\GroqService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class ParseLapkeuPdfJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 300;
    public int $backoff = 30;

    public function __construct(private int $extractionId)
    {
        $this->onQueue('ai');
    }

    public function handle(GroqService $groq): void
    {
        $extraction = LapkeuPdfExtraction::findOrFail($this->extractionId);

        $extraction->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            if (!Storage::disk('public')->exists($extraction->file_path)) {
                throw new \RuntimeException('File PDF laporan keuangan tidak ditemukan di storage.');
            }

            $parser = new PdfParser();
            $pdf = $parser->parseFile(Storage::disk('public')->path($extraction->file_path));
            $text = trim($pdf->getText());

            if (mb_strlen($text) < 200) {
                throw new \RuntimeException('PDF berhasil diupload, tetapi teks laporan keuangan tidak terbaca. Gunakan PDF laporan keuangan yang memiliki text layer, bukan hasil scan gambar.');
            }

            $data = $groq->parseLapkeuPdf($text, $extraction->instrumen);
            $data['pdf_lapkeu_path'] = basename($extraction->file_path);

            $extraction->update([
                'status' => 'completed',
                'result_data' => $data,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('ParseLapkeuPdfJob gagal', [
                'extraction_id' => $this->extractionId,
                'error' => $e->getMessage(),
            ]);

            $extraction->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }
}
