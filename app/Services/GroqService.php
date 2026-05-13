<?php

namespace App\Services;

use App\Models\AnalisaReksaDana;
use Illuminate\Support\Facades\Http;

class GroqService
{
    private string $apiKey;
    private string $model;
    private string $url;

    public function __construct()
    {
        $this->apiKey = config('services.groq.key');
        $this->model  = config('services.groq.model');
        $this->url    = config('services.groq.url');
    }

    public function generateNarasiAnalisa(AnalisaReksaDana $analisa): string
    {
        $prompt = $this->buildPrompt($analisa);

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post($this->url, [
                'model'       => $this->model,
                'temperature' => 0.7,
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'Kamu adalah analis investasi profesional Indonesia yang ahli dalam analisa Reksa Dana. Berikan analisa yang jelas, informatif, dan mudah dipahami investor. Gunakan Bahasa Indonesia yang baik. Format output menggunakan teks biasa tanpa markdown.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Groq API error: '.$response->body());
        }

        return $response->json('choices.0.message.content', '');
    }

    private function buildPrompt(AnalisaReksaDana $analisa): string
    {
        $lines = [];
        $lines[] = "Buatkan narasi analisa lengkap untuk Reksa Dana berikut:";
        $lines[] = "";
        $lines[] = "INFORMASI REKSA DANA";
        $lines[] = "Nama: {$analisa->nama_reksa_dana}";
        $lines[] = "Jenis: {$analisa->jenis_reksa_dana}";
        $lines[] = "Total AUM: ".($analisa->total_aum ? 'Rp '.number_format($analisa->total_aum, 0, ',', '.') : 'N/A');

        // Metrik kinerja
        $lines[] = "";
        $lines[] = "METRIK KINERJA";
        $lines[] = "Sharpe Ratio: ".($analisa->sharpe_ratio ?? 'N/A');
        $lines[] = "RAR (Risk-Adjusted Return): ".($analisa->rar ?? 'N/A');
        $lines[] = "Liquidity Ratio (AUM/MarCap): ".($analisa->liquidity_ratio ? number_format($analisa->liquidity_ratio * 100, 2).'%' : 'N/A');
        $lines[] = "Durasi Rata-rata Obligasi: ".($analisa->durasi_rata_rata ? $analisa->durasi_rata_rata.' tahun' : 'N/A');

        // Sektor
        if ($analisa->sektor->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "KOMPOSISI SEKTOR";
            foreach ($analisa->sektor->sortByDesc('bobot') as $s) {
                $lines[] = "- {$s->nama_sektor}: {$s->bobot}%";
            }
        }

        // 10 Efek Terbesar
        $top10 = $analisa->efek->where('top_10', true)->sortByDesc('bobot');
        if ($top10->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "10 EFEK TERBESAR";
            foreach ($top10 as $e) {
                $kontribusi = $e->kontribusi_kinerja !== null
                    ? ($e->kontribusi_kinerja >= 0 ? '+' : '').$e->kontribusi_kinerja.'%'
                    : 'N/A';
                $lines[] = "- {$e->kode_efek} ({$e->nama_efek}): bobot {$e->bobot}%, kontribusi {$kontribusi}";
            }
        }

        // Kinerja bulanan
        if ($analisa->kinerja->isNotEmpty()) {
            $returns = $analisa->kinerja->pluck('return_pct')->toArray();
            $avg = round(array_sum($returns) / count($returns), 4);
            $positif = count(array_filter($returns, fn($r) => $r > 0));
            $lines[] = "";
            $lines[] = "KINERJA BULANAN ({$analisa->kinerja->count()} bulan)";
            $lines[] = "Return rata-rata: {$avg}%";
            $lines[] = "Bulan positif: {$positif} dari {$analisa->kinerja->count()}";
        }

        // Obligasi
        if ($analisa->obligasi->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "OBLIGASI DALAM PORTOFOLIO";
            foreach ($analisa->obligasi as $ob) {
                $lines[] = "- {$ob->nama_obligasi} (Rating: {$ob->rating}, Durasi: {$ob->durasi} thn, Bobot: {$ob->bobot}%)";
            }
        }

        // Bank
        if ($analisa->bank->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "BANK DALAM PORTOFOLIO";
            foreach ($analisa->bank as $b) {
                $lines[] = "- {$b->nama_bank}: CAR {$b->car}%, NPL {$b->npl}%, Risiko: {$b->klasifikasi_risiko}";
            }
        }

        $lines[] = "";
        $lines[] = "Berikan analisa yang mencakup:";
        $lines[] = "1. Ringkasan kinerja keseluruhan";
        $lines[] = "2. Analisa risiko (liquidity, durasi, rating, bank)";
        $lines[] = "3. Kekuatan dan kelemahan portofolio";
        $lines[] = "4. Rekomendasi singkat untuk investor";

        return implode("\n", $lines);
    }
}
