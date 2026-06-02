<?php

namespace App\Services;

use App\Models\Stock;

class AIAnalysisService
{
    public function __construct(private readonly GroqService $ai)
    {
    }

    public function generateNewsFromAI(int $stockId): int
    {
        $stock = Stock::with('profile')->findOrFail($stockId);
        $namaPerusahaan = $stock->nama ?? $stock->profile?->company_name ?? $stock->kode;

        $prompt = <<<PROMPT
Kamu adalah jurnalis keuangan Indonesia. Buatkan 10 berita fiksi yang realistis tentang saham {$stock->kode} ({$namaPerusahaan}) dari berbagai media terkemuka Indonesia dan internasional.

Media yang digunakan (satu berita per media):
1. Kontan
2. Bisnis Indonesia
3. CNBC Indonesia
4. Detik Finance
5. Tempo Bisnis
6. IDX Channel
7. Investor Daily
8. Bloomberg
9. Reuters
10. The Edge Markets

Format output HANYA JSON array, tidak ada teks lain sebelum atau sesudah:
[
  {
    "title": "judul berita",
    "source": "nama media",
    "published_at": "YYYY-MM-DD",
    "summary": "ringkasan 2-3 kalimat dalam bahasa Indonesia"
  }
]

Gunakan tanggal dalam 30 hari terakhir dari hari ini. Konten harus relevan dengan kondisi perusahaan, industri, dan pasar saham Indonesia. Jangan menyebut berita ini fiktif.
PROMPT;

        $raw = $this->ai->callAi([
            ['role' => 'system', 'content' => 'Kamu adalah jurnalis keuangan. Balas HANYA dengan JSON array valid, tanpa markdown, tanpa penjelasan.'],
            ['role' => 'user', 'content' => $prompt],
        ], 60, 0.7);

        // Ekstrak JSON dari respons
        preg_match('/\[.*\]/s', $raw, $matches);
        $items = json_decode($matches[0] ?? $raw, true);

        if (!is_array($items) || empty($items)) {
            throw new \RuntimeException('Gagal mem-parse respons AI. Coba lagi.');
        }

        $count = 0;
        foreach ($items as $item) {
            if (empty($item['title'])) continue;
            \App\Models\StockNews::create([
                'stock_id'     => $stockId,
                'title'        => $item['title'],
                'source'       => $item['source'] ?? null,
                'published_at' => isset($item['published_at']) ? \Carbon\Carbon::parse($item['published_at'])->toDateTimeString() : now(),
                'summary'      => $item['summary'] ?? null,
                'ai_summary'   => null,
            ]);
            $count++;
        }

        return $count;
    }

    public function summarizeStockNews($stockId): string
    {
        $stock = Stock::with('news')->findOrFail($stockId);
        $items = $stock->news->map(fn ($news) => [
            'judul' => $news->title,
            'media' => $news->source,
            'tanggal' => optional($news->published_at)->format('Y-m-d'),
            'ringkasan' => $news->summary,
        ])->values()->all();

        if ($items === []) {
            throw new \RuntimeException('Berita terkait belum tersedia.');
        }

        $summary = $this->summarize('berita saham ' . $stock->kode, $items);
        $stock->news()->update(['ai_summary' => $summary]);

        return $summary;
    }

    public function summarizeBrokerResearch($stockId): string
    {
        $stock = Stock::with('brokerResearches')->findOrFail($stockId);
        $items = $stock->brokerResearches->map(fn ($research) => [
            'broker' => $research->broker_name,
            'tanggal' => optional($research->research_date)->format('Y-m-d'),
            'rating' => $research->rating,
            'target_price' => $research->target_price,
        ])->values()->all();

        if ($items === []) {
            throw new \RuntimeException('Riset broker terkait belum tersedia.');
        }

        $summary = $this->summarize('riset broker saham ' . $stock->kode, $items);
        $stock->brokerResearches()->update(['ai_summary' => $summary]);

        return $summary;
    }

    private function summarize(string $topic, array $items): string
    {
        return $this->ai->callAi([
            [
                'role' => 'system',
                'content' => 'Kamu adalah analis saham Indonesia. Buat ringkasan singkat, faktual, dan mudah dipahami dalam Bahasa Indonesia. Jangan menambah fakta yang tidak tersedia.',
            ],
            [
                'role' => 'user',
                'content' => "Buat AI summary untuk {$topic} dari data JSON berikut:\n" . json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ],
        ], 90, 0.2);
    }
}
