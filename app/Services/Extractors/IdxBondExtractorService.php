<?php

namespace App\Services\Extractors;

use Illuminate\Support\Facades\Http;

class IdxBondExtractorService implements ExtractorInterface
{
    public function extract(array $parameters): array
    {
        $endpoint = config('services.extraction.sources.idx.bond_url');
        if (blank($endpoint)) {
            throw new \RuntimeException('Endpoint publik IDX untuk data obligasi belum dikonfigurasi atau membutuhkan otorisasi.');
        }

        $codes = array_values(array_filter(array_map('strtoupper', $parameters['codes'] ?? [])));
        $response = Http::acceptJson()
            ->withHeaders(['User-Agent' => config('idx.user_agent', 'Mozilla/5.0')])
            ->timeout((int) config('services.extraction.timeout', 20))
            ->retry((int) config('services.extraction.retry', 3), (int) config('services.extraction.retry_sleep_ms', 500))
            ->get($endpoint, [
                'date' => $parameters['data_date'] ?? null,
                'codes' => !empty($codes) ? implode(',', $codes) : null,
            ]);

        if ($response->status() === 401 || $response->status() === 403) {
            throw new \RuntimeException('Akses IDX membutuhkan otorisasi. Ekstraksi dihentikan.');
        }

        if ($response->failed()) {
            throw new \RuntimeException('IDX gagal diakses: HTTP ' . $response->status());
        }

        return collect($response->json('data', $response->json() ?? []))->map(fn (array $row) => [
            'bond_code' => $row['bond_code'] ?? $row['kode'] ?? $row['code'] ?? null,
            'bond_name' => $row['bond_name'] ?? $row['nama'] ?? $row['name'] ?? null,
            'issuer' => $row['issuer'] ?? $row['emiten'] ?? null,
            'maturity_date' => $row['maturity_date'] ?? $row['jatuh_tempo'] ?? null,
            'coupon' => $row['coupon'] ?? $row['kupon'] ?? null,
            'rating' => $row['rating'] ?? null,
            'yield' => $row['yield'] ?? $row['ytm'] ?? null,
            'fair_price' => $row['fair_price'] ?? $row['harga_wajar'] ?? $row['harga_persen'] ?? null,
            'data_date' => $row['data_date'] ?? $parameters['data_date'] ?? null,
            'source' => 'IDX',
            'raw_payload' => $row,
        ])->filter(function (array $row) use ($codes) {
            if (!filled($row['bond_code'])) {
                return false;
            }
            if (empty($codes)) {
                return true;
            }
            return in_array(strtoupper($row['bond_code']), $codes, true);
        })->values()->all();
    }
}
