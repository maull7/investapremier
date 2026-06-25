<?php

namespace App\Services;

use App\Models\FfsExtractionResult;
use App\Models\ReksaDana;
use App\Models\ReksaDanaDocument;

class FfsExtractionService
{
    public function __construct(
        private GroqService $groqService,
        private ProspektusParserService $parserService,
    ) {
    }

    public function extractAndSave(ReksaDanaDocument $document, ?int $userId = null): array
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

        return [
            'saved'    => true,
            'result'   => $result,
            'fields'   => array_keys($aiResult),
            'ai_data'  => $aiResult,
        ];
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
