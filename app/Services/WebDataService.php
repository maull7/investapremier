<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WebDataService
{
    protected array $sources = [
        'bareksa' => 'https://www.bareksa.com',
        'kontan' => 'https://investasi.kontan.co.id',
        'ojk' => 'https://reksadana.ojk.go.id',
    ];

    public function searchNamaReksaDana(string $nama): ?array
    {
        $results = [];

        foreach ($this->sources as $source => $url) {
            try {
                $response = Http::timeout(10)->get("{$url}/search", [
                    'q' => $nama . ' reksa dana',
                ]);

                if ($response->successful()) {
                    $results[$source] = $this->parseSearchResult($source, $response->body());
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $results;
    }

    public function searchDataReksaDana(string $nama): array
    {
        $data = [
            'nama_reksa_dana' => null,
            'jenis_reksa_dana' => null,
            'total_aum' => null,
            'sektor' => [],
            'efek' => [],
            'kinerja' => [],
            'obligasi' => [],
            'bank' => [],
        ];

        // Try OJK Reksadana data
        try {
            $url = "https://reksadana.ojk.go.id/api/reksadana/search?q=" . urlencode($nama);
            $response = Http::timeout(15)->get($url);
            if ($response->successful()) {
                $json = $response->json();
                // Parse OJK API response
            }
        } catch (\Exception $e) {
            // Fallback to other sources
        }

        return $data;
    }

    public function searchFundPerformance(string $nama): array
    {
        $kinerja = [];

        // Try to get performance data from public APIs
        try {
            $url = "https://www.bareksa.com/api/v1/funds/search?name=" . urlencode($nama);
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                // Parse performance data
            }
        } catch (\Exception $e) {
            // Log or skip
        }

        return $kinerja;
    }

    protected function parseSearchResult(string $source, string $html): array
    {
        // Basic HTML parsing to extract relevant data
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        $dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        $xpath = new \DOMXPath($dom);

        $results = [];

        // Common selectors for search result items
        $selectors = [
            '//div[contains(@class, "search-result")]//a',
            '//h3/a',
            '//div[contains(@class, "title")]/a',
        ];

        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes && $nodes->length > 0) {
                foreach ($nodes as $node) {
                    $results[] = [
                        'title' => trim($node->textContent),
                        'url' => $node->getAttribute('href'),
                    ];
                }
                break;
            }
        }

        return $results;
    }
}
