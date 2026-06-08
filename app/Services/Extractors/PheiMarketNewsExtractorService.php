<?php

namespace App\Services\Extractors;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class PheiMarketNewsExtractorService implements ExtractorInterface
{
    public function extract(array $parameters): array
    {
        $endpoint = config('services.extraction.sources.phei.news_url');
        if (blank($endpoint)) {
            throw new \RuntimeException('Endpoint berita PHEI belum dikonfigurasi atau tidak tersedia publik.');
        }

        $response = Http::withHeaders(['User-Agent' => config('idx.user_agent', 'Mozilla/5.0')])
            ->timeout((int) config('services.extraction.timeout', 20))
            ->retry((int) config('services.extraction.retry', 3), (int) config('services.extraction.retry_sleep_ms', 500))
            ->get($endpoint, ['date' => $parameters['data_date'] ?? null]);

        if ($response->status() === 401 || $response->status() === 403) {
            throw new \RuntimeException('Akses berita PHEI membutuhkan otorisasi. Ekstraksi dihentikan.');
        }

        if ($response->failed()) {
            throw new \RuntimeException('Berita PHEI gagal diakses: HTTP ' . $response->status());
        }

        $json = $response->json();
        if (is_array($json)) {
            return collect($json['data'] ?? $json)->map(fn (array $row) => [
                'news_date' => $row['news_date'] ?? $row['tanggal'] ?? $parameters['data_date'] ?? null,
                'title' => $row['title'] ?? $row['judul'] ?? null,
                'url' => $row['url'] ?? $row['link'] ?? null,
                'source' => 'PHEI',
                'raw_payload' => $row,
            ])->filter(fn (array $row) => filled($row['title']) && filled($row['url']))->values()->all();
        }

        return $this->extractLinksFromHtml($response->body(), $endpoint, $parameters['data_date'] ?? null);
    }

    private function extractLinksFromHtml(string $html, string $baseUrl, ?string $date): array
    {
        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER);

        return collect($matches)->map(function (array $match) use ($baseUrl, $date) {
            $title = trim(strip_tags($match[2]));
            $url = $this->absoluteUrl($match[1], $baseUrl);

            return [
                'news_date' => $date ? Carbon::parse($date)->toDateTimeString() : null,
                'title' => $title,
                'url' => $url,
                'source' => 'PHEI',
                'raw_payload' => ['href' => $match[1]],
            ];
        })->filter(fn (array $row) => filled($row['title']) && filled($row['url']))->values()->all();
    }

    private function absoluteUrl(string $url, string $baseUrl): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }
}
