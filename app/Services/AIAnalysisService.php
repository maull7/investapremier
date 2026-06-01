<?php

namespace App\Services;

use App\Models\Stock;

class AIAnalysisService
{
    public function __construct(private readonly GroqService $ai)
    {
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
