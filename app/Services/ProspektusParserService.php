<?php

namespace App\Services;

use App\Models\ReksaDanaDocument;
use App\Models\DocumentParsedPage;
use App\Models\DocumentPartition;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class ProspektusParserService
{
    public function __construct(
        private GroqService $groqService,
    ) {}

    public function parseDocument(
        ReksaDanaDocument $document,
        int $tocStartPage,
        int $tocEndPage,
        bool $generatePartitions = false,
        ?int $userId = null,
    ): array {
        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            throw new \RuntimeException('File dokumen tidak ditemukan.');
        }

        $parser = new Parser();
        $pdf = $parser->parseFile(Storage::disk('public')->path($document->file_path));
        $pages = $pdf->getPages();

        $totalPages = count($pages);
        $parsedCount = 0;
        $partitionsCreated = 0;

        DocumentParsedPage::where('reksa_dana_document_id', $document->id)->delete();

        // Ekstrak teks daftar isi dan generate partisi otomatis via AI
        if ($generatePartitions && $tocEndPage > 0 && $tocStartPage <= $tocEndPage && $tocStartPage <= $totalPages) {
            $tocText = $this->extractTocText($pages, $tocStartPage, $tocEndPage);
            $chapters = $this->extractChaptersFromToc($tocText);
            $partitionsCreated = $this->createPartitionsFromChapters($document, $chapters, $tocEndPage, $totalPages, $userId);
        }

        $parseNumber = 0;

        for ($i = 0; $i < $totalPages; $i++) {
            $pdfPageNumber = $i + 1;

            if ($pdfPageNumber <= $tocEndPage) {
                continue;
            }

            $parseNumber++;
            $text = $this->cleanText($pages[$i]->getText());

            if (empty(trim($text))) {
                continue;
            }

            DocumentParsedPage::create([
                'reksa_dana_document_id' => $document->id,
                'page_pdf'               => $pdfPageNumber,
                'page_parse'             => $parseNumber,
                'text_content'           => $text,
            ]);

            $parsedCount++;
        }

        return [
            'total_pages'        => $totalPages,
            'parsed_count'       => $parsedCount,
            'toc_start'          => $tocStartPage,
            'toc_end'            => $tocEndPage,
            'partitions_created' => $partitionsCreated,
        ];
    }

    private function extractTocText(array $pages, int $tocStartPage, int $tocEndPage): string
    {
        $texts = [];
        for ($i = $tocStartPage - 1; $i < min($tocEndPage, count($pages)); $i++) {
            $texts[] = $this->cleanText($pages[$i]->getText());
        }
        return implode("\n\n", array_filter($texts));
    }

    private function extractChaptersFromToc(string $tocText): array
    {
        if (empty(trim($tocText))) {
            return [];
        }

        $truncatedText = mb_substr($tocText, 0, 60000);
        $systemPrompt = <<<PROMPT
Kamu adalah parser DAFTAR ISI prospektus reksa dana Indonesia.

TUGAS:
Ekstrak semua BAB/section utama dari daftar isi dan tentukan nomor halaman PDF fisik yang sebenarnya.

ATURAN PENTING:
1. Kembalikan HANYA JSON array valid.
2. Jangan menambahkan markdown, penjelasan, atau teks lain.
3. Format setiap item:

{
  "nama_bab": "BAB I - Definisi",
  "halaman_pdf": 12
}

4. "halaman_pdf" HARUS berupa nomor halaman fisik PDF sebenarnya (index halaman dokumen), BUKAN nomor halaman yang tercetak pada isi dokumen.

5. Jika daftar isi menampilkan:
   - BAB I ........ 1
   - BAB II ....... 5
   - BAB III ...... 12

   dan halaman pertama isi dokumen dimulai pada halaman PDF ke-10,
   maka hasilnya:

   [
     {"nama_bab":"BAB I - Definisi","halaman_pdf":10},
     {"nama_bab":"BAB II - Tujuan dan Kebijakan Investasi","halaman_pdf":14},
     {"nama_bab":"BAB III - Manajer Investasi","halaman_pdf":21}
   ]

6. Tentukan offset halaman dengan membandingkan:
   - nomor halaman pada daftar isi
   - posisi daftar isi di PDF
   - halaman pertama isi dokumen setelah daftar isi

7. Pastikan urutan halaman_pdf selalu meningkat sesuai urutan kemunculan BAB.

8. Jangan menghasilkan dua BAB dengan halaman_pdf yang sama kecuali memang tercantum demikian di dokumen.

9. Jangan mengarang BAB yang tidak ada.

10. Jika halaman tidak dapat dipastikan, gunakan:

{
  "nama_bab": "...",
  "halaman_pdf": null
}

11. Untuk nama bab:
    - Pertahankan nomor BAB yang ada.
    - Gunakan format:
      "BAB I - Judul"
      "BAB II - Judul"
      "BAB III - Judul"

12. Abaikan:
    - Sampul
    - Halaman kosong
    - Daftar Isi
    - Lampiran
    kecuali memang merupakan BAB utama.

OUTPUT:
JSON array valid saja.
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "TEKS DAFTAR ISI:\n" . $truncatedText],
        ];

        try {
            $rawResponse = $this->groqService->callAi($messages, 120, 0.2);
            return $this->parseChapterJson($rawResponse);
        } catch (\Throwable $e) {
            \Log::warning('[TOC AI] Gagal ekstrak bab: ' . $e->getMessage());
            return [];
        }
    }

    private function parseChapterJson(string $response): array
    {
        $cleaned = trim($response);
        $cleaned = preg_replace('/^```(?:json)?\s*\n|\n```\s*$/', '', $cleaned);

        $decoded = json_decode($cleaned, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\[[\s\S]*\]/', $cleaned, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function normalizeChapterName(string $name): string
    {
        // Normalisasi variasi penulisan bab
        $name = preg_replace('/^(BAB)\s+(\w+)/iu', '$1 $2', $name);

        // Jika format "BAB I Judul" tanpa strip, tambahkan strip
        if (preg_match('/^(BAB\s+\w+)\s+(.+)$/iu', $name, $matches)) {
            $number = trim($matches[1]);
            $title = trim($matches[2]);

            // Hindari double strip
            $title = ltrim($title, '-–— ');

            return $number . ' - ' . $title;
        }

        return $name;
    }

    private function createPartitionsFromChapters(
        ReksaDanaDocument $document,
        array $chapters,
        int $tocEndPage,
        int $totalPages,
        ?int $userId
    ): int {
        $validChapters = collect($chapters)
            ->filter(fn($c) => !empty($c['nama_bab']))
            ->map(fn($c) => [
                'nama_bab'    => $this->normalizeChapterName(trim($c['nama_bab'])),
                'halaman_pdf' => is_numeric($c['halaman_pdf'] ?? null) ? (int) $c['halaman_pdf'] : null,
            ])
            ->filter(fn($c) => $c['halaman_pdf'] === null || $c['halaman_pdf'] > $tocEndPage)
            ->sortBy('halaman_pdf')
            ->values();

        if ($validChapters->isEmpty()) {
            return 0;
        }

        // Hapus partisi auto-generated sebelumnya agar tidak duplikat
        DocumentPartition::where('reksa_dana_document_id', $document->id)
            ->where('source', 'toc_ai')
            ->delete();

        $created = 0;
        $totalParsePages = $totalPages - $tocEndPage;

        foreach ($validChapters as $index => $chapter) {
            $startPagePdf = $chapter['halaman_pdf'];
            $startPageParse = max(1, $startPagePdf - $tocEndPage);

            // Tentukan halaman akhir partisi
            $endPageParse = $totalParsePages;
            $endPagePdf = $totalPages;
            $nextChapter = $validChapters->get($index + 1);
            if ($nextChapter && is_numeric($nextChapter['halaman_pdf'])) {
                $endPagePdf = $nextChapter['halaman_pdf'] - 1;
                $endPageParse = max(1, $endPagePdf - $tocEndPage);
            }

            if ($startPageParse > $endPageParse) {
                continue;
            }

            DocumentPartition::create([
                'reksa_dana_document_id' => $document->id,
                'created_by'             => $userId,
                'nama_partisi'           => $chapter['nama_bab'],
                'start_page'             => $startPageParse,
                'end_page'               => $endPageParse,
                'start_page_pdf'         => $startPagePdf,
                'end_page_pdf'           => $endPagePdf,
                'source'                 => 'toc_ai',
            ]);

            $created++;
        }

        return $created;
    }

    public function getTextForPages(ReksaDanaDocument $document, int $startPage, int $endPage): string
    {
        $pages = DocumentParsedPage::where('reksa_dana_document_id', $document->id)
            ->whereBetween('page_parse', [$startPage, $endPage])
            ->orderBy('page_parse')
            ->get();

        return $pages->pluck('text_content')->implode("\n\n");
    }

    public function getAllText(ReksaDanaDocument $document): string
    {
        $pages = DocumentParsedPage::where('reksa_dana_document_id', $document->id)
            ->orderBy('page_parse')
            ->get();

        return $pages->pluck('text_content')->implode("\n\n");
    }

    private function cleanText(string $text): string
    {
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }
}
