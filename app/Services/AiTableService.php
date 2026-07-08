<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

class AiTableService
{
    public function extractTables(string $pdfPath, array $partitions): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $pages = $pdf->getPages();
        $totalPages = count($pages);

        $results = [];

        foreach ($partitions as $part) {
            $start = max(1, (int) ($part['start'] ?? 1));
            $end = min((int) ($part['end'] ?? $totalPages), $totalPages);
            $partId = $part['id'] ?? null;

            $pageTexts = [];
            for ($i = $start - 1; $i < $end && $i < $totalPages; $i++) {
                if (isset($pages[$i])) {
                    $text = $pages[$i]->getText();
                    if (trim($text)) {
                        $pageTexts[] = "--- PAGE " . ($i + 1) . " ---\n" . $text;
                    }
                }
            }

            $fullText = implode("\n\n", $pageTexts);

            if (!trim($fullText)) {
                $results[] = [
                    'id' => $partId,
                    'pages' => "{$start}-{$end}",
                    'tables' => [],
                    'raw_tables' => [],
                    'fields' => [],
                ];
                continue;
            }

            if (mb_strlen($fullText) > 50000) {
                $fullText = mb_substr($fullText, 0, 50000);
            }

            $tables = $this->callOpenAI($fullText);

            $results[] = [
                'id' => $partId,
                'pages' => "{$start}-{$end}",
                'tables' => $tables,
                'raw_tables' => [[
                    'page' => "{$start}-{$end}",
                    'table_name' => '',
                    'headers' => [],
                    'rows' => [],
                ]],
                'fields' => [],
            ];
        }

        return $results;
    }

    private function callOpenAI(string $text): array
    {
        $prompt = "You are a financial table creator. Given text from a mutual fund annual report (Laporan Tahunan Reksa Dana), create structured tables.\n\nReturn a JSON object with a \"tables\" array. Each table has:\n- \"table_name\": name of the table (used to identify table type)\n- \"headers\": array of column names\n- \"rows\": array of rows, each row is an array of cell values\n\nCreate portfolio tables AND financial statement tables when you find relevant data:\n\n=== Portfolio Tables ===\n1. \"Portofolio Efek\" — portfolio of equity instruments (saham). Determine columns dynamically from the text. May include: Nama Efek, Kode, Sektor, Jumlah Lembar Saham, Harga Perolehan, Nilai Pasar, Bobot %, % NAB, Return 1Y. Include ALL rows found.\n2. \"Obligasi\" — headers: [\"Kode\", \"Nama Obligasi\", \"Bobot %\", \"Nilai Pasar\", \"YTM\", \"Kupon\", \"Jatuh Tempo\", \"Penerbit\", \"Rating\"]\n3. \"Sektor\" — headers: [\"Sektor\", \"Bobot %\"]\n4. \"Sukuk\" — headers: [\"Kode\", \"Nama\", \"Jenis\", \"Bobot %\", \"Yield\", \"Jatuh Tempo\", \"Rating\"]\n5. \"Bank\" — headers: [\"Nama Bank\", \"Jenis\", \"Bobot %\", \"Nilai Pasar\", \"Tingkat Bunga\", \"Jangka Waktu\"]\n\n=== Financial Statement Tables ===\n6. \"Aset\" — financial position assets. Determine year columns from the text (e.g. \"2026\", \"+ Tahun Sebelumnya\").\n   Headers: [\"Item\", \"<tahun>\", \"<tahun sebelumnya>\"]. Rows include: Portofolio Efek, Instrumen Pasar Uang, Total Portofolio Efek, Kas dan Bank, Piutang Bunga, Piutang Dividen, Piutang Lain-lain, Piutang Transaksi Efek, Piutang Bunga dan Dividen, Total Aset.\n7. \"Liabilitas\" — financial position liabilities.\n   Headers: [\"Item\", \"<tahun>\", \"<tahun sebelumnya>\"]. Rows include: Utang Pajak, Utang Lain-lain, Uang Muka Diterima, Liabilitas Pembelian Kembali, Beban Akrual, Liabilitas Atas Biaya, Pembelian Kembali Unit Penyertaan, Utang Pajak Lainnya, Total Liabilitas, Nilai Aset Bersih.\n8. \"Pendapatan\" — income/revenue.\n   Headers: [\"Item\", \"<tahun>\"]. Rows include: Pendapatan Bunga, Pendapatan Dividen, Pendapatan Investasi, Keuntungan Terealisasi, Keuntungan Belum Terealisasi, Pendapatan Lainnya, Total Pendapatan.\n9. \"Beban\" — expenses.\n   Headers: [\"Item\", \"<tahun>\"]. Rows include: Beban Manajer Investasi, Beban Kustodian, Beban Investasi, Beban Pengelolaan Investasi, Beban Lain-lain, Total Beban, Laba/(Rugi) Sebelum Pajak, Beban Pajak Penghasilan, Laba/(Rugi) Tahun Berjalan, Penghasilan Komprehensif Lain, Total Penghasilan Komprehensif.\n10. \"Arus Kas Operasi\" — operating cash flow.\n    Headers: [\"Item\", \"<tahun>\"]. Rows include: Arus Kas Operasi, Pembelian Efek Ekuitas, Penjualan Efek Ekuitas, Penerimaan Bunga Deposito, Penerimaan Bunga Jasa Giro, Penerimaan Dividen Kas, Pembayaran Jasa Pengelolaan, Pembayaran Jasa Kustodian, Pembayaran Beban Lain, Kas Bersih Aktivitas Operasi.\n11. \"Arus Kas Pendanaan\" — financing cash flow.\n    Headers: [\"Item\", \"<tahun>\"]. Rows include: Arus Kas Pendanaan, Penerimaan Penjualan Unit, Pembayaran Pembelian Kembali Unit, Kas Bersih Aktivitas Pendanaan, Kenaikan Kas dan Setara Kas, Kas dan Setara Kas Awal Tahun, Kas dan Setara Kas Akhir Tahun, Kas, Deposito Berjangka, Total Kas dan Setara Kas.\n12. \"Pengukuran Nilai Wajar\" — fair value measurement hierarchy.\n    Headers: [\"Item\", \"<tahun>\", \"<tahun sebelumnya>\"]. Rows: Level 1, Level 2, Level 3, Jumlah.\n13. \"Informasi Lainnya\" — other information / supplementary data.\n    Headers: [\"Item\", \"<tahun>\"]. Rows: Total Hasil Investasi (%), Hasil Investasi Setelah Biaya Pemasaran (%), Biaya Operasi (%), Portfolio Turnover Ratio, Persentase Penghasilan Kena Pajak (%).\n\nRules:\n- Determine year column names dynamically from the text. Common patterns: \"2026\", \"+ Tahun Sebelumnya\", \"2025\", \"Tahun Berjalan\".\n- For financial statement tables, use only 1-2 year columns as found in the source.\n- Do NOT add a \"Catatan\" / \"Note\" column.\n- Create ALL tables that have data in the text. Omit table types with no data.\n- Include every row from the source, not just a sample.\n- Use plain string values for all cells.\n- If a value is not found in the text, use an empty string \"\".\n- Return {\"tables\": []} if no table data is found.";

        $response = Http::withToken(config('services.openai.key'))
            ->timeout(300)
            ->post(config('services.openai.url'), [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You create organized portfolio and financial statement tables from mutual fund annual report text. Return only valid JSON.'],
                    ['role' => 'user', 'content' => "Create tables from this annual report text:\n\n{$text}\n\n{$prompt}"],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.1,
                'max_tokens' => 8000,
            ]);

        if (!$response->successful()) {
            \Log::warning('[AI-TABLE] OpenAI call failed: ' . $response->body());
            return [];
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '{}';
        $parsed = json_decode($content, true);

        return $parsed['tables'] ?? [];
    }
}
