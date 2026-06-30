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

        DocumentParsedPage::where('reksa_dana_document_id', $document->id)->delete();

        // Parse semua halaman dulu (sekali ekstrak, simpan ke DB)
        $parseNumber = 0;
        $parsedCount = 0;

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

        // Bikin partisi dari teks yang sudah tersimpan di DB (tanpa ekstrak ulang dari PDF)
        $warnings = [];
        $partitionsCreated = 0;
        if ($generatePartitions && $tocEndPage > 0 && $tocStartPage <= $tocEndPage && $tocStartPage <= $totalPages) {
            $tocText = $this->extractTocText($pages, $tocStartPage, $tocEndPage);
            $chapters = $this->extractChaptersFromToc($tocText);
            $headingsMap = $this->scanHeadingsFromDatabase($document, $tocEndPage);
            $partitionResult = $this->createPartitionsFromChapters($document, $chapters, $tocEndPage, $totalPages, $userId, $headingsMap);
            $partitionsCreated = $partitionResult['created'];
            $warnings = $partitionResult['warnings'];
        }

        return [
            'total_pages'        => $totalPages,
            'parsed_count'       => $parsedCount,
            'toc_start'          => $tocStartPage,
            'toc_end'            => $tocEndPage,
            'partitions_created' => $partitionsCreated,
            'warnings'           => $warnings,
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
Ekstrak semua BAB/section utama dari daftar isi beserta nomor halaman isi yang tercetak di daftar isi.

ATURAN PENTING:
1. Kembalikan HANYA JSON array valid.
2. Jangan menambahkan markdown, penjelasan, atau teks lain.
3. Format setiap item:

{
  "nama_bab": "BAB I - Definisi",
  "halaman_isi": 1
}

4. "halaman_isi" adalah nomor halaman yang TERCETAK di daftar isi (biasanya angka kecil seperti 1, 5, 12), BUKAN nomor halaman fisik PDF.

5. Contoh: jika daftar isi menampilkan:
   - BAB I ........ 1
   - BAB II ....... 5
   - BAB III ...... 12

   maka hasilnya:

   [
     {"nama_bab":"BAB I - Definisi","halaman_isi":1},
     {"nama_bab":"BAB II - Tujuan dan Kebijakan Investasi","halaman_isi":5},
     {"nama_bab":"BAB III - Manajer Investasi","halaman_isi":12}
   ]

6. Pastikan urutan halaman_isi selalu meningkat sesuai urutan kemunculan BAB.

7. Jangan menghasilkan dua BAB dengan halaman_isi yang sama kecuali memang tercantum demikian di dokumen.

8. Jangan mengarang BAB yang tidak ada.

9. Jika halaman tidak tercantum di daftar isi, gunakan:

{
  "nama_bab": "...",
  "halaman_isi": null
}

10. Untuk nama bab:
    - Pertahankan nomor BAB yang ada.
    - Gunakan format:
      "BAB I - Judul"
      "BAB II - Judul"
      "BAB III - Judul"

11. Abaikan:
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
            return $this->normalizeChapterKeys($decoded);
        }

        if (preg_match('/\[[\s\S]*\]/', $cleaned, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeChapterKeys($decoded);
            }
        }

        return [];
    }

    private function normalizeChapterKeys(array $chapters): array
    {
        return array_map(fn($c) => [
            'nama_bab'    => $c['nama_bab'] ?? '',
            'halaman_pdf' => $c['halaman_pdf'] ?? $c['halaman_isi'] ?? null,
        ], $chapters);
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

    private function extractBabNumber(string $nama_bab): ?string
    {
        if (preg_match('/\bBAB\s+([IVXLCDM]+)\b/iu', $nama_bab, $m)) {
            return strtoupper($m[1]);
        }
        return null;
    }

    private function scanHeadingsFromDatabase(ReksaDanaDocument $document, int $tocEndPage): array
    {
        $headings = [];
        $pages = DocumentParsedPage::where('reksa_dana_document_id', $document->id)
            ->orderBy('page_pdf')
            ->get(['page_pdf', 'text_content']);

        foreach ($pages as $page) {
            $lines = explode("\n", $page->text_content);
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (preg_match('/^BAB\s+([IVXLCDM]+)/iu', $trimmed, $m)) {
                    $numeral = strtoupper($m[1]);
                    if (!isset($headings[$numeral])) {
                        $headings[$numeral] = $page->page_pdf;
                    }
                    break;
                }
            }
        }

        return $headings;
    }

    private function createPartitionsFromChapters(
        ReksaDanaDocument $document,
        array $chapters,
        int $tocEndPage,
        int $totalPages,
        ?int $userId,
        array $headingsMap = [],
    ): array {
        $validChapters = collect($chapters)
            ->filter(fn($c) => !empty($c['nama_bab']))
            ->map(fn($c) => [
                'nama_bab'    => $this->normalizeChapterName(trim($c['nama_bab'])),
                'halaman_pdf' => is_numeric($c['halaman_pdf'] ?? null) ? (int) $c['halaman_pdf'] : null,
            ])
            ->values();

        if ($validChapters->isEmpty()) {
            return ['created' => 0, 'warnings' => []];
        }

        $firstContentPage = $tocEndPage + 1;

        $warnings = [];

        // Hapus partisi auto-generated sebelumnya agar tidak duplikat
        DocumentPartition::where('reksa_dana_document_id', $document->id)
            ->where('source', 'toc_ai')
            ->delete();

        $created = 0;
        $totalParsePages = $totalPages - $tocEndPage;
        $previousPdfPage = null;

        foreach ($validChapters as $index => $chapter) {
            $startPagePdf = $chapter['halaman_pdf'];

            // Content-based matching: cari "BAB X" di halaman aktual
            $babNum = $this->extractBabNumber($chapter['nama_bab']);
            $contentPage = $babNum && isset($headingsMap[$babNum]) ? $headingsMap[$babNum] : null;

            if ($contentPage !== null) {
                if ($startPagePdf !== null && $contentPage !== $startPagePdf) {
                    $warnings[] = "{$chapter['nama_bab']}: estimasi AI halaman {$startPagePdf}, content-scan menemukan di halaman {$contentPage}";
                }
                $startPagePdf = $contentPage;
            } elseif ($startPagePdf === null) {
                $startPagePdf = $firstContentPage;
                $warnings[] = "{$chapter['nama_bab']}: halaman tidak ditemukan, fallback ke halaman {$firstContentPage}";
            } elseif ($startPagePdf < $firstContentPage) {
                $startPagePdf = $firstContentPage;
            }

            // Pastikan urutan tidak tumpang tindih
            if ($previousPdfPage !== null && $startPagePdf <= $previousPdfPage) {
                $startPagePdf = $previousPdfPage + 1;
                $warnings[] = "{$chapter['nama_bab']}: halaman disesuaikan ke {$startPagePdf} untuk menghindari tumpang tindih";
            }

            $startPageParse = $startPagePdf - $tocEndPage;

            // Tentukan halaman akhir partisi
            $endPageParse = $totalParsePages;
            $endPagePdf = $totalPages;
            $nextChapter = $validChapters->get($index + 1);

            if ($nextChapter) {
                $nextStart = null;
                $nextBabNum = $this->extractBabNumber($nextChapter['nama_bab']);
                $nextContentPage = $nextBabNum && isset($headingsMap[$nextBabNum]) ? $headingsMap[$nextBabNum] : null;

                if ($nextContentPage !== null) {
                    $nextStart = $nextContentPage;
                } elseif (is_numeric($nextChapter['halaman_pdf'] ?? null)) {
                    $nextStart = max((int) $nextChapter['halaman_pdf'], $firstContentPage);
                }

                if ($nextStart !== null) {
                    $endPagePdf = $nextStart - 1;
                    $endPageParse = max(1, $endPagePdf - $tocEndPage);
                }
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

            $previousPdfPage = $startPagePdf;
            $created++;
        }

        foreach ($warnings as $warning) {
            \Log::warning("[TOC Partisi] {$warning}");
        }

        return [
            'created'  => $created,
            'warnings' => $warnings,
        ];
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
