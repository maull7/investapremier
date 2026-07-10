<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ParserClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.parse_api_python.url', 'http://localhost:5000'), '/');
        $this->timeout = (int) config('services.parse_api_python.timeout', 300);
    }

    public function enabled(): bool
    {
        return (bool) config('services.parse_api_python.enabled', false);
    }

    public function extractAllSections(string $pdfPath, array $pageGroups): array
    {
        if (!$this->enabled()) {
            return [];
        }

        $sectionMap = [
            'cover' => 'cover',
            'fund_info' => 'fund_info',
            'financial_statements' => 'financial_statements',
            'portfolio' => 'portfolio',
            'performance' => 'performance',
        ];

        $requestPageGroups = [];
        foreach ($sectionMap as $sectionKey => $sectionType) {
            $pages = $pageGroups[$sectionKey] ?? [];
            if (empty($pages)) continue;
            $requestPageGroups[$sectionType] = array_map(fn($p) => $p + 1, $pages);
        }

        if (empty($requestPageGroups)) {
            return [];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->attach('file', file_get_contents($pdfPath), basename($pdfPath))
                ->post("{$this->baseUrl}/api/extract-prospektus", [
                    'page_groups' => json_encode($requestPageGroups),
                ]);

            if (!$response->successful()) {
                Log::warning('[PARSER] parse_api_python error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $result = $response->json();

            if (!($result['success'] ?? false)) {
                Log::warning('[PARSER] parse_api_python returned not success', ['result' => $result]);
                return [];
            }

            $allData = $result['data'] ?? [];
            $normalized = [];

            foreach ($sectionMap as $sectionKey => $sectionType) {
                $sectionData = $allData[$sectionType] ?? [];
                if (empty($sectionData)) continue;
                $normalized[$sectionKey] = $this->normalizeResult($sectionData, $sectionType);
            }

            return $normalized;
        } catch (\Throwable $e) {
            Log::warning('[PARSER] Gagal panggil parse_api_python', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function normalizeResult(array $data, string $sectionType): array
    {
        $flattened = [];

        if (isset($data['matched'])) {
            foreach ($data['matched'] as $item) {
                $field = $item['label'] ?? null;
                if (!$field) continue;

                $values = $item['values'] ?? [];
                $firstValue = reset($values);

                if ($firstValue !== false && $firstValue !== null) {
                    $flattened[$field] = $firstValue;
                }
            }
        } else {
            $flattened = $data;
        }

        return $flattened;
    }
}
