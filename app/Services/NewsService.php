<?php

namespace App\Services;

use App\Models\Stock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsService
{
    public function fetchNews(Stock $stock, int $maxPerSource = 6): array
    {
        $yahoo = $this->fetchYahooNews($stock);
        $google = $this->fetchGoogleNews($stock);

        $all = array_merge($yahoo, $google);

        usort($all, fn($a, $b) => strcmp($b['publishedAt'] ?? '', $a['publishedAt'] ?? ''));

        return $all;
    }

    public function fetchYahooNews(Stock $stock): array
    {
        try {
            $symbol = $this->yahooSymbol($stock->kode);

            $response = Http::withHeaders([
                'accept'     => 'application/json',
                'User-Agent' => config('idx.user_agent', 'Mozilla/5.0'),
            ])->timeout(10)->get(config('services.yahoo_finance.search_url'), [
                'q'           => $symbol,
                'quotesCount' => 0,
                'newsCount'   => 6,
                'region'      => 'ID',
                'lang'        => 'id-ID',
            ]);

            if ($response->failed()) return [];

            return collect($response->json('news', []))
                ->map(fn($item) => [
                    'title'       => $item['title'] ?? null,
                    'source'      => $item['publisher'] ?? 'Yahoo Finance',
                    'url'         => $item['link'] ?? null,
                    'publishedAt' => isset($item['providerPublishTime'])
                        ? Carbon::createFromTimestamp($item['providerPublishTime'])->toIso8601String()
                        : null,
                    'sourceType'  => 'yahoo',
                ])
                ->filter(fn($item) => filled($item['title']))
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('[NewsService] Yahoo news gagal: ' . $e->getMessage());
            return [];
        }
    }

    public function fetchGoogleNews(Stock $stock): array
    {
        try {
            $query = urlencode("{$stock->kode} saham IDX");
            $url = "https://news.google.com/rss/search?q={$query}&hl=id&gl=ID&ceid=ID:id";

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
            ])->timeout(10)->get($url);

            if ($response->failed()) return [];

            $xml = simplexml_load_string($response->body());
            if (!$xml || !isset($xml->channel->item)) return [];

            $items = [];
            foreach ($xml->channel->item as $item) {
                $title = (string) $item->title;
                $link = (string) $item->link;
                $pubDate = (string) $item->pubDate;
                $source = (string) $item->source;

                if (!filled($title)) continue;

                $items[] = [
                    'title'       => $title,
                    'source'      => $source ?: 'Google News',
                    'url'         => $link ?: null,
                    'publishedAt' => $pubDate
                        ? Carbon::parse($pubDate)->toIso8601String()
                        : null,
                    'sourceType'  => 'google',
                ];
            }

            return array_slice($items, 0, 6);
        } catch (\Throwable $e) {
            Log::warning('[NewsService] Google News gagal: ' . $e->getMessage());
            return [];
        }
    }

    private function yahooSymbol(string $kode): string
    {
        $kode = strtoupper($kode);

        if (in_array($kode, ['AALI', 'ADRO', 'AKRA', 'ANTM', 'ASII', 'BBCA', 'BBNI', 'BBRI', 'BDMN',
            'BMRI', 'BRPT', 'BTPS', 'CPIN', 'EXCL', 'GGRM', 'HMSP', 'ICBP', 'INDF', 'INDY',
            'INKP', 'INTP', 'ITMG', 'JPFA', 'JSMR', 'KLBF', 'LPKR', 'LSIP', 'MEDC', 'MIKA',
            'MNCN', 'PGAS', 'PTBA', 'PTPP', 'PWON', 'SCMA', 'SMGR', 'SMMA', 'SRIL', 'TBIG',
            'TINS', 'TLKM', 'TOWR', 'TPIA', 'UNTR', 'UNVR', 'WIKA', 'WSKT', 'WTON'])) {
            return $kode . '.JK';
        }

        if (preg_match('/^[A-Z]+$/', $kode) && strlen($kode) <= 5) {
            return $kode . '.JK';
        }

        return $kode;
    }
}
