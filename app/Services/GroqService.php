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

    public function parseFfsPdf(string $pdfText): array
    {
        $text = mb_substr($pdfText, 0, 8000);
        $messages = [
            [
                'role'    => 'system',
                'content' => 'Kamu adalah parser dokumen Fund Fact Sheet (FFS) reksa dana Indonesia. Ekstrak data dari teks PDF dan kembalikan HANYA JSON valid tanpa teks lain.',
            ],
            [
                'role'    => 'user',
                'content' => <<<PROMPT
Ekstrak data dari teks Fund Fact Sheet berikut. Kembalikan HANYA JSON valid dengan struktur PERSIS seperti ini:

{
  "nama_reksa_dana": "string atau null",
  "jenis_reksa_dana": "Saham" atau "Pendapatan Tetap" atau "Campuran" atau "Pasar Uang" atau null,
  "total_aum": angka rupiah penuh atau null,
  "total_marcap_10_efek": angka rupiah penuh atau null,
  "sektor": [
    {"nama_sektor": "string", "bobot": angka_persen}
  ],
  "efek": [
    {
      "kode_efek": "string misal BBCA",
      "nama_efek": "string nama lengkap",
      "sektor": "string nama sektor efek ini atau kosong",
      "bobot": angka_persen,
      "kontribusi_kinerja": angka_persen_atau_null,
      "market_cap": angka_rupiah_penuh_atau_null,
      "top_10": true jika masuk 10 efek terbesar
    }
  ],
  "kinerja": [
    {"periode": "YYYY-MM", "return_pct": angka}
  ],
  "obligasi": [
    {
      "kode_obligasi": "string misal FR0091 atau kosong",
      "nama_obligasi": "string nama lengkap",
      "bobot": angka_persen,
      "durasi": angka_tahun_atau_null,
      "rating": "AAA" atau "AA+" atau "AA" atau "AA-" atau "A+" atau "A" atau "A-" atau "BBB+" atau "BBB" atau "BBB-" atau "BB" atau "B" atau "CCC" atau "D" atau null
    }
  ],
  "bank": [
    {
      "nama_bank": "string",
      "bobot": angka_persen_atau_null,
      "car": angka_persen_atau_null,
      "npl": angka_persen_atau_null,
      "klasifikasi_risiko": "Rendah" atau "Sedang" atau "Tinggi" atau null
    }
  ]
}

ATURAN:
- total_aum dan total_marcap_10_efek dalam Rupiah penuh (misal 1.5 triliun = 1500000000000)
- bobot dalam persen (misal 12.5, bukan 0.125)
- periode kinerja format YYYY-MM (misal "2024-03")
- Jika data tidak ada gunakan null atau array kosong []
- Output HANYA JSON valid, tanpa penjelasan, tanpa markdown

TEKS FFS:
{$text}
PROMPT,
            ],
        ];

        // Coba OpenAI dulu
        $openaiKey = config('services.openai.key');
        if ($openaiKey) {
            try {
                \Log::info('[FFS Parser] Menggunakan OpenAI: ' . config('services.openai.model', 'gpt-4o-mini'));
                $response = Http::withToken($openaiKey)
                    ->timeout(60)
                    ->post(config('services.openai.url'), [
                        'model'       => config('services.openai.model', 'gpt-4o-mini'),
                        'temperature' => 0.1,
                        'messages'    => $messages,
                    ]);

                if ($response->successful()) {
                    $raw = $response->json('choices.0.message.content', '');
                    $parsed = self::parseJsonOutput($raw);
                    if (!empty($parsed)) {
                        \Log::info('[FFS Parser] OpenAI berhasil, ' . count($parsed) . ' field diekstrak.');
                        return $parsed;
                    }
                }
                \Log::warning('[FFS Parser] OpenAI gagal atau hasil kosong, fallback ke Groq. Status: ' . $response->status());
            } catch (\Throwable $e) {
                \Log::warning('[FFS Parser] OpenAI exception: ' . $e->getMessage() . ', fallback ke Groq.');
            }
        }

        // Fallback ke Groq
        \Log::info('[FFS Parser] Menggunakan Groq: ' . $this->model);
        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->post($this->url, [
                'model'       => $this->model,
                'temperature' => 0.1,
                'messages'    => $messages,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Groq API error: ' . $response->body());
        }

        $raw = $response->json('choices.0.message.content', '');
        return self::parseJsonOutput($raw);
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

    public function generateNarasiAnalisaStructured(AnalisaReksaDana $analisa): array
    {
        $prompt = $this->buildStructuredPrompt($analisa);

        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->post($this->url, [
                'model'       => $this->model,
                'temperature' => 0.3,
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'Kamu adalah analis investasi profesional Indonesia yang ahli dalam analisa Reksa Dana. Gunakan Bahasa Indonesia yang baik. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.',
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

        $raw = $response->json('choices.0.message.content', '');
        $parsed = $this->parseJsonOutput($raw);

        return [
            'raw'    => $this->buildNarasiFromStructured($parsed),
            'parsed' => $parsed,
        ];
    }

    public function generateAnalisaPlusStructured(AnalisaReksaDana $analisa): array
    {
        $prompt = $this->buildPlusStructuredPrompt($analisa);

        $response = Http::withToken($this->apiKey)
            ->timeout(120)
            ->post($this->url, [
                'model'       => $this->model,
                'temperature' => 0.3,
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'Kamu adalah analis investasi senior Indonesia yang ahli analisa mendalam Reksa Dana. Gunakan Bahasa Indonesia. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.',
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

        $raw = $response->json('choices.0.message.content', '');
        $parsed = $this->parseJsonOutput($raw);

        return [
            'raw'    => $this->buildNarasiFromPlusStructured($parsed),
            'parsed' => $parsed,
        ];
    }

    public static function parseJsonOutput(string $raw): array
    {
        $raw = trim($raw);

        if (str_starts_with($raw, '```')) {
            $raw = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $raw);
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        preg_match('/\{.*\}/s', $raw, $matches);
        if (!empty($matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return [];
    }

    private function buildNarasiFromStructured(array $data): string
    {
        $parts = [];

        if (!empty($data['ringkasan_utama'])) {
            $parts[] = $data['ringkasan_utama'];
        }

        if (!empty($data['analisa_risiko'])) {
            $parts[] = "**Analisa Risiko**\n".$data['analisa_risiko'];
        }

        if (!empty($data['rekomendasi_investor'])) {
            $parts[] = "**Rekomendasi**\n".$data['rekomendasi_investor'];
        }

        return implode("\n\n", $parts);
    }

    private function buildNarasiFromPlusStructured(array $data): string
    {
        $parts = [];

        foreach (['ringkasan_utama', 'analisa_kinerja', 'analisa_risiko', 'analisa_likuiditas', 'rekomendasi_investor'] as $key) {
            if (!empty($data[$key])) {
                $parts[] = $data[$key];
            }
        }

        return implode("\n\n", $parts);
    }

    private function buildPlusStructuredPrompt(AnalisaReksaDana $analisa): string
    {
        $data = $this->buildDataSection($analisa);

        return <<<PROMPT
{$data}

Berdasarkan data Input Manual lengkap di atas, buatkan analisa mendalam (Analisa AI Plus) dalam format JSON:
{
  "ringkasan_utama": "Ringkasan eksekutif 2-3 paragraf dengan metrik kunci",
  "analisa_kinerja": "Analisa kinerja bulanan, Sharpe, RAR, dan tren return",
  "analisa_risiko": "Analisa risiko obligasi, bank, durasi, rating, konsentrasi sektor",
  "analisa_likuiditas": "Analisa likuiditas portofolio dan rasio AUM vs MarCap 10 efek",
  "rekomendasi_investor": "Rekomendasi investasi spesifik berdasarkan profil risiko",
  "metrik_saran": {
    "sharpe_ratio": null,
    "rar": null,
    "liquidity_ratio": null,
    "durasi_rata_rata": null
  }
}

PETUNJUK:
- Gunakan semua data sektor, efek, kinerja, obligasi, dan bank yang tersedia
- Jika metrik tidak bisa dihitung, jelaskan di narasi dan set null di metrik_saran
- Output HANYA JSON valid tanpa markdown
PROMPT;
    }

    private function buildPrompt(AnalisaReksaDana $analisa): string
    {
        return $this->buildDataSection($analisa)."\n\nBerikan analisa yang mencakup:\n1. Ringkasan kinerja keseluruhan\n2. Analisa risiko (liquidity, durasi, rating, bank)\n3. Kekuatan dan kelemahan portofolio\n4. Rekomendasi singkat untuk investor";
    }

    private function buildStructuredPrompt(AnalisaReksaDana $analisa): string
    {
        $data = $this->buildDataSection($analisa);

        return <<<PROMPT
{$data}

Berdasarkan data di atas, buatkan analisa dalam format JSON dengan struktur EXACT berikut (jangan tambah atau kurangi field):
{
  "ringkasan_utama": "Ringkasan kinerja keseluruhan dalam 2-3 paragraf, mencakup return, komposisi sektor, dan posisi portfolio secara umum",
  "alokasi_aset": [
    {"kategori": "Nama Sektor/Kategori Aset", "persentase": 25.5, "keterangan": "Penjelasan singkat tentang alokasi ini"}
  ],
  "daftar_efek": [
    {"kode_efek": "BBCA", "nama_efek": "Bank Central Asia Tbk.", "sektor": "Keuangan", "bobot": 12.5, "kontribusi_kinerja": 2.3}
  ],
  "analisa_risiko": "Analisa risiko likuiditas, durasi, rating obligasi, dan bank dalam 1-2 paragraf",
  "rekomendasi_investor": "Rekomendasi singkat untuk investor berdasarkan profil risiko dan kondisi portfolio"
}

PETUNJUK PENTING:
- Isi `alokasi_aset` dengan data komposisi sektor yang sudah diberikan
- Isi `daftar_efek` dengan data efek yang sudah diberikan
- Gunakan Bahasa Indonesia yang baik dan benar
- Output HANYA JSON valid, tanpa teks lain, tanpa markdown
- Pastikan JSON bisa diparse dengan json_decode()
PROMPT;
    }

    private function buildDataSection(AnalisaReksaDana $analisa): string
    {
        $lines = [];
        $lines[] = "INFORMASI REKSA DANA";
        $lines[] = "Nama: {$analisa->nama_reksa_dana}";
        $lines[] = "Jenis: {$analisa->jenis_reksa_dana}";
        $lines[] = "Total AUM: ".($analisa->total_aum ? 'Rp '.number_format($analisa->total_aum, 0, ',', '.') : 'N/A');

        $lines[] = "";
        $lines[] = "METRIK KINERJA";
        $lines[] = "Sharpe Ratio: ".($analisa->sharpe_ratio ?? 'N/A');
        $lines[] = "RAR (Risk-Adjusted Return): ".($analisa->rar ?? 'N/A');
        $lines[] = "Liquidity Ratio (AUM/MarCap): ".($analisa->liquidity_ratio ? number_format($analisa->liquidity_ratio * 100, 2).'%' : 'N/A');
        $lines[] = "Durasi Rata-rata Obligasi: ".($analisa->durasi_rata_rata ? $analisa->durasi_rata_rata.' tahun' : 'N/A');

        if ($analisa->sektor->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "KOMPOSISI SEKTOR";
            foreach ($analisa->sektor->sortByDesc('bobot') as $s) {
                $lines[] = "- {$s->nama_sektor}: {$s->bobot}%";
            }
        }

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

        if ($analisa->kinerja->isNotEmpty()) {
            $returns = $analisa->kinerja->pluck('return_pct')->toArray();
            $avg = round(array_sum($returns) / count($returns), 4);
            $positif = count(array_filter($returns, fn($r) => $r > 0));
            $lines[] = "";
            $lines[] = "KINERJA BULANAN ({$analisa->kinerja->count()} bulan)";
            $lines[] = "Return rata-rata: {$avg}%";
            $lines[] = "Bulan positif: {$positif} dari {$analisa->kinerja->count()}";
        }

        if ($analisa->obligasi->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "OBLIGASI DALAM PORTOFOLIO";
            foreach ($analisa->obligasi as $ob) {
                $lines[] = "- {$ob->nama_obligasi} (Rating: {$ob->rating}, Durasi: {$ob->durasi} thn, Bobot: {$ob->bobot}%)";
            }
        }

        if ($analisa->bank->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "BANK DALAM PORTOFOLIO";
            foreach ($analisa->bank as $b) {
                $lines[] = "- {$b->nama_bank}: CAR {$b->car}%, NPL {$b->npl}%, Risiko: {$b->klasifikasi_risiko}";
            }
        }

        return implode("\n", $lines);
    }
}
