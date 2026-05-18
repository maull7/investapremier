<?php

namespace App\Services;

use App\Models\DataSourceLink;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Upaya unduh otomatis dari URL export (jika situs mengizinkan akses HTTP + session).
 * Banyak situs (PasarDana) membutuhkan login browser — fallback: user unduh manual lalu upload.
 */
class DataSourceAutoDownloadService
{
    public function downloadToTempFile(DataSourceLink $link): string
    {
        $exportUrl = $link->urls
            ->first(fn ($u) => $this->looksLikeDownloadUrl($u->url));

        if (!$exportUrl) {
            throw new \RuntimeException(
                'Tidak ada URL unduh (CSV/XLS) pada sumber ini. Tambahkan URL "Download" di Admin → Daftar Reksa Dana → Link Website.'
            );
        }

        if ($link->jenis_akses !== 'public' && (!$link->login_username || !$link->login_password)) {
            throw new \RuntimeException(
                'Sumber butuh login. Isi username & password di Admin → Link Website, atau unduh file manual dari link lalu upload di sini.'
            );
        }

        $response = Http::timeout(60)
            ->withOptions(['allow_redirects' => true])
            ->when($link->login_username, function ($http) use ($link) {
                return $http->withBasicAuth(
                    (string) $link->login_username,
                    (string) $link->login_password
                );
            })
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; InvestaPremier/1.0)',
                'Accept' => 'text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,*/*',
            ])
            ->get($exportUrl->url);

        if (!$response->successful()) {
            throw new \RuntimeException(
                'Gagal mengunduh otomatis (HTTP ' . $response->status() . '). Buka link situs, login, unduh file manual, lalu gunakan tombol "Isi Form dari File".'
            );
        }

        $body = $response->body();
        if (strlen($body) < 10 || $this->looksLikeHtml($body)) {
            throw new \RuntimeException(
                'Respons bukan file data (kemungkinan halaman login). Unduh file manual dari situs, lalu upload di sini.'
            );
        }

        $ext = $this->guessExtension($exportUrl->url, $response->header('Content-Type'));
        $path = 'tmp/web-scrape-' . $link->id . '-' . now()->format('YmdHis') . '.' . $ext;
        Storage::disk('local')->put($path, $body);

        return Storage::disk('local')->path($path);
    }

    protected function looksLikeDownloadUrl(string $url): bool
    {
        $lower = strtolower($url);

        return str_contains($lower, 'download')
            || str_contains($lower, 'export')
            || str_contains($lower, '.csv')
            || str_contains($lower, '.xls');
    }

    protected function looksLikeHtml(string $body): bool
    {
        $start = strtolower(ltrim(substr($body, 0, 200)));

        return str_starts_with($start, '<!doctype')
            || str_starts_with($start, '<html')
            || str_contains($start, '<body');
    }

    protected function guessExtension(string $url, ?string $contentType): string
    {
        if (str_contains(strtolower($url), '.xlsx') || str_contains((string) $contentType, 'spreadsheetml')) {
            return 'xlsx';
        }
        if (str_contains(strtolower($url), '.xls')) {
            return 'xls';
        }

        return 'csv';
    }
}
