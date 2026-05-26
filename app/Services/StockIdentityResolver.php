<?php

namespace App\Services;

use App\Models\Stock;

class StockIdentityResolver
{
    public function enrich(array $data, ?string $filenameHint = null): array
    {
        $stock = $this->findStock(
            $data['kode_saham'] ?? $this->extractCodeFromText($filenameHint),
            $data['nama_perusahaan'] ?? null
        );

        if (!$stock) {
            return $data;
        }

        if (empty($data['kode_saham'])) {
            $data['kode_saham'] = $stock->kode;
        }

        if (empty($data['sektor'])) {
            $data['sektor'] = $stock->sektor;
        }

        if (empty($data['nama_perusahaan']) && !empty($stock->nama)) {
            $data['nama_perusahaan'] = $stock->nama;
        }

        return $data;
    }

    private function findStock(?string $code, ?string $companyName): ?Stock
    {
        $code = strtoupper(trim((string) $code));
        if ($code !== '') {
            $stock = Stock::where('kode', $code)->first();
            if ($stock) {
                return $stock;
            }
        }

        $needle = $this->normalizeName($companyName);
        if ($needle === '') {
            return null;
        }

        return Stock::query()
            ->get()
            ->sortByDesc(fn (Stock $stock) => $this->nameScore($needle, $this->normalizeName($stock->nama)))
            ->first(fn (Stock $stock) => $this->nameScore($needle, $this->normalizeName($stock->nama)) >= 65);
    }

    private function extractCodeFromText(?string $text): ?string
    {
        if (!$text || !preg_match('/\b([A-Z]{4})\b/i', $text, $match)) {
            return null;
        }

        return strtoupper($match[1]);
    }

    private function normalizeName(?string $name): string
    {
        $name = strtolower((string) $name);
        $name = preg_replace('/\b(pt|tbk|bk|persero|the|company|limited|ltd)\b/i', ' ', $name);
        $name = preg_replace('/[^a-z0-9]+/i', ' ', $name);

        return trim(preg_replace('/\s+/', ' ', $name));
    }

    private function nameScore(string $a, string $b): int
    {
        if ($a === '' || $b === '') {
            return 0;
        }

        similar_text($a, $b, $percent);

        if (str_contains($a, $b) || str_contains($b, $a)) {
            $percent = max($percent, 90);
        }

        return (int) round($percent);
    }
}
