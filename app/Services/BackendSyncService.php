<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BackendSyncService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.backend_sync.url', ''), '/');
        $this->timeout = (int) config('services.backend_sync.timeout', 600);
    }

    public function isAvailable(): bool
    {
        return $this->baseUrl !== '';
    }

    public function health(): array
    {
        return $this->get('/api/health');
    }

    /**
     * Fetch all saham data from backend GET /api/saham.
     * Returns array of items ready for upsertStocks().
     * Throws RuntimeException when backend is unreachable or returns error.
     */
    public function fetchSahamData(): array
    {
        $res = $this->get('/api/saham');

        if (!($res['success'] ?? false)) {
            throw new \RuntimeException($res['message'] ?? 'Backend API saham tidak merespon. Pastikan backend sync sudah jalan.');
        }

        $data = $res['data'] ?? [];

        $numericFields = [
            'harga_terbaru', 'harga_pembukaan', 'harga_penutupan_sebelumnya',
            'harga_tertinggi', 'harga_terendah', 'perubahan_persen',
            'volume', 'nilai', 'frekuensi', 'jumlah_saham', 'market_capital',
        ];

        foreach ($data as &$item) {
            if (isset($item['value']) && !isset($item['nilai'])) {
                $item['nilai'] = $item['value'];
            }

            // Cast numeric fields to float so parseIdrNumber() doesn't misinterpret
            // dots as thousands separators (IDX-Indonesian format).
            foreach ($numericFields as $field) {
                if (isset($item[$field]) && $item[$field] !== '' && $item[$field] !== null) {
                    $item[$field] = (float) $item[$field];
                }
            }

            unset($item['id'], $item['created_at'], $item['updated_at'], $item['last_update']);
        }

        return $data;
    }

    /**
     * Fetch all obligasi data from backend GET /api/obligasi.
     * Returns array of items ready for upsertBonds().
     * Throws RuntimeException when backend is unreachable or returns error.
     */
    public function fetchObligasiData(): array
    {
        $res = $this->get('/api/obligasi');

        if (!($res['success'] ?? false)) {
            throw new \RuntimeException($res['message'] ?? 'Backend API obligasi tidak merespon. Pastikan backend sync sudah jalan.');
        }

        $data = $res['data'] ?? [];

        foreach ($data as &$item) {
            unset($item['id'], $item['created_at'], $item['updated_at']);
        }

        return $data;
    }

    public function syncSaham(bool $skipEnrich = false): array
    {
        $url = '/api/sync/saham';
        if ($skipEnrich) {
            $url .= '?skipEnrich=true';
        }
        return $this->post($url);
    }

    public function syncObligasi(): array
    {
        return $this->post('/api/sync/obligasi');
    }

    public function getStocks(array $params = []): array
    {
        return $this->get('/api/saham', $params);
    }

    public function getStock(string $kode): array
    {
        return $this->get("/api/saham/{$kode}");
    }

    public function getBonds(array $params = []): array
    {
        return $this->get('/api/obligasi', $params);
    }

    public function getBond(string $kode): array
    {
        return $this->get("/api/obligasi/{$kode}");
    }

    public function getSyncStatus(): array
    {
        return $this->get('/api/sync/status');
    }

    private function get(string $path, array $params = []): array
    {
        if (!$this->isAvailable()) {
            return ['success' => false, 'message' => 'Backend sync URL tidak dikonfigurasi.'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($this->baseUrl . $path, $params);

            if ($response->successful()) {
                return $response->json() ?: ['success' => false, 'message' => 'Response kosong'];
            }

            Log::warning('BackendSync GET failed', [
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => "Backend sync error (HTTP {$response->status()}): " . $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('BackendSync GET exception', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke backend sync: ' . $e->getMessage(),
            ];
        }
    }

    private function post(string $path, array $data = []): array
    {
        if (!$this->isAvailable()) {
            return ['success' => false, 'message' => 'Backend sync URL tidak dikonfigurasi.'];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Accept' => 'application/json'])
                ->post($this->baseUrl . $path, $data);

            if ($response->successful()) {
                return $response->json() ?: ['success' => false, 'message' => 'Response kosong'];
            }

            Log::warning('BackendSync POST failed', [
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => "Backend sync error (HTTP {$response->status()}): " . $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('BackendSync POST exception', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke backend sync: ' . $e->getMessage(),
            ];
        }
    }
}
