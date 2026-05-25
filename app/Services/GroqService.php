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

    public function generateNarasiAnalisaStructured(AnalisaReksaDana $analisa, string $productType = 'reksa_dana'): array
    {
        $prompt = $this->buildStructuredPrompt($analisa, $productType);
        $systemKey = $productType === 'unit_link' ? 'system_analisa_unit_link' : 'system_analisa';
        $systemPrompt = \App\Models\AiPrompt::get($systemKey, 'Kamu adalah analis investasi profesional Indonesia yang ahli dalam analisa Reksa Dana. Gunakan Bahasa Indonesia yang baik. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.');

        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->post($this->url, [
                'model'       => $this->model,
                'temperature' => 0.3,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $prompt],
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
        $systemPrompt = \App\Models\AiPrompt::get('system_analisa_plus', 'Kamu adalah analis investasi senior Indonesia yang ahli analisa mendalam Reksa Dana. Gunakan Bahasa Indonesia. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.');

        $response = Http::withToken($this->apiKey)
            ->timeout(120)
            ->post($this->url, [
                'model'       => $this->model,
                'temperature' => 0.3,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $prompt],
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

    public function generateNarasiLapkeuStructured(array $data, string $instrumen = 'Saham'): array
    {
        $fmt = fn($v) => $v !== null ? number_format((float)$v, 2, ',', '.') : 'N/A';

        $lines = [];
        $lines[] = "INSTRUMEN: {$instrumen}";
        if (!empty($data['nama'])) $lines[] = "Nama: {$data['nama']}";
        if (!empty($data['kode'])) $lines[] = "Kode: {$data['kode']}";
        if (!empty($data['periode'])) $lines[] = "Periode: {$data['periode']}";
        if (!empty($data['mata_uang'])) $lines[] = "Mata Uang: {$data['mata_uang']}";

        if ($instrumen === 'Obligasi') {
            if (!empty($data['rating'])) $lines[] = "Rating: {$data['rating']}";
            if ($data['kupon'] ?? null) $lines[] = "Kupon: {$data['kupon']}%";
            if ($data['ytm'] ?? null) $lines[] = "YTM: {$data['ytm']}%";
        }

        $lines[] = "";
        $lines[] = "NERACA (Balance Sheet)";
        $lines[] = "Total Aset: {$fmt($data['total_asset'] ?? null)}";
        $lines[] = "  - Aset Lancar: {$fmt($data['current_asset'] ?? null)}";
        $lines[] = "    - Kas & Setara Kas: {$fmt($data['cash_equivalents'] ?? null)}";
        $lines[] = "    - Piutang Usaha: {$fmt($data['account_receivable'] ?? null)}";
        $lines[] = "    - Persediaan: {$fmt($data['inventories'] ?? null)}";
        $lines[] = "  - Aset Tidak Lancar: {$fmt($data['fixed_asset'] ?? null)}";
        $lines[] = "Total Liabilitas: {$fmt($data['total_liabilities'] ?? null)}";
        $lines[] = "  - Liabilitas Jangka Pendek: {$fmt($data['current_liabilities'] ?? null)}";
        $lines[] = "  - Liabilitas Jangka Panjang: {$fmt($data['long_term_loans'] ?? null)}";
        $lines[] = "Total Ekuitas: {$fmt($data['equity'] ?? null)}";

        $lines[] = "";
        $lines[] = "LABA RUGI (Income Statement)";
        $lines[] = "Pendapatan Bersih: {$fmt($data['net_revenue'] ?? null)}";
        $lines[] = "Laba Kotor: {$fmt($data['gross_income'] ?? null)}";
        $lines[] = "EBIT: {$fmt($data['ebit'] ?? null)}";
        $lines[] = "EBITDA: {$fmt($data['ebitda'] ?? null)}";
        $lines[] = "Beban Bunga: {$fmt($data['interest_expense'] ?? null)}";
        $lines[] = "Laba Bersih: {$fmt($data['net_income'] ?? null)}";
        $lines[] = "EPS: {$fmt($data['eps'] ?? null)}";

        $lines[] = "";
        $lines[] = "ARUS KAS";
        $lines[] = "Operasional: {$fmt($data['cash_flows_operating_activities'] ?? null)}";
        $lines[] = "Investasi: {$fmt($data['cash_flows_investment'] ?? null)}";
        $lines[] = "Pendanaan: {$fmt($data['cash_flows_financing'] ?? null)}";

        $dataSection = implode("\n", $lines);

        $prompt = <<<PROMPT
{$dataSection}

Berdasarkan data laporan keuangan {$instrumen} di atas, buatkan analisa keuangan dalam format JSON dengan struktur EXACT berikut:
{
  "ringkasan_utama": "Ringkasan kondisi keuangan dalam 2-3 paragraf",
  "analisa_neraca": "Analisa struktur aset, liabilitas, dan ekuitas — leverage, likuiditas, solvabilitas",
  "analisa_laba_rugi": "Analisa profitabilitas: margin kotor, margin EBIT, net margin, tren laba",
  "analisa_arus_kas": "Analisa kualitas arus kas operasional vs laba, capex, free cash flow",
  "rasio_keuangan": {
    "current_ratio": null,
    "debt_to_equity": null,
    "net_profit_margin": null,
    "roe": null
  },
  "rekomendasi": "Kesimpulan dan rekomendasi singkat berdasarkan kondisi keuangan"
}

PETUNJUK:
- Hitung rasio jika data tersedia: current_ratio = current_asset/current_liabilities, DER = total_liabilities/equity, net margin = net_income/net_revenue * 100, ROE = net_income/equity * 100
- Set null jika tidak bisa dihitung
- Gunakan Bahasa Indonesia yang baik
- Output HANYA JSON valid tanpa markdown
PROMPT;

        $promptKey = $instrumen === 'Obligasi' ? 'system_analisa_obligasi' : 'system_analisa_saham';
        $systemPrompt = \App\Models\AiPrompt::get($promptKey, "Kamu adalah analis keuangan profesional Indonesia yang ahli menganalisa laporan keuangan {$instrumen}. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.");

        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->post($this->url, [
                'model'       => $this->model,
                'temperature' => 0.3,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $prompt],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Groq API error: ' . $response->body());
        }

        $raw = $response->json('choices.0.message.content', '');
        $parsed = self::parseJsonOutput($raw);

        $narasiParts = [];
        foreach (['ringkasan_utama', 'analisa_neraca', 'analisa_laba_rugi', 'analisa_arus_kas', 'rekomendasi'] as $key) {
            if (!empty($parsed[$key])) $narasiParts[] = $parsed[$key];
        }

        return [
            'raw'    => implode("\n\n", $narasiParts),
            'parsed' => $parsed,
        ];
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
        $instruksi = \App\Models\AiPrompt::get('instruksi_analisa_plus', <<<DEFAULT
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
DEFAULT);

        return $data . "\n\n" . $instruksi;
    }

    private function buildPrompt(AnalisaReksaDana $analisa): string
    {
        return $this->buildDataSection($analisa)."\n\nBerikan analisa yang mencakup:\n1. Ringkasan kinerja keseluruhan\n2. Analisa risiko (liquidity, durasi, rating, bank)\n3. Kekuatan dan kelemahan portofolio\n4. Rekomendasi singkat untuk investor";
    }

    private function buildStructuredPrompt(AnalisaReksaDana $analisa, string $productType = 'reksa_dana'): string
    {
        $data = $this->buildDataSection($analisa);
        $instruksiKey = $productType === 'unit_link' ? 'instruksi_analisa_unit_link' : 'instruksi_analisa';
        $instruksi = \App\Models\AiPrompt::get($instruksiKey, <<<DEFAULT
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
DEFAULT);

        return $data . "\n\n" . $instruksi;
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

    public function generateNarasiLapkeuPlusStructured(array $data, string $instrumen = 'Saham'): array
    {
        $fmt = fn($v) => $v !== null ? number_format((float)$v, 2, ',', '.') : 'N/A';

        $lines = [];
        $lines[] = "INSTRUMEN: {$instrumen}";
        if (!empty($data['nama'])) $lines[] = "Nama: {$data['nama']}";
        if (!empty($data['kode'])) $lines[] = "Kode: {$data['kode']}";
        if (!empty($data['periode'])) $lines[] = "Periode: {$data['periode']}";
        if (!empty($data['mata_uang'])) $lines[] = "Mata Uang: {$data['mata_uang']}";

        if ($instrumen === 'Obligasi') {
            if (!empty($data['rating'])) $lines[] = "Rating: {$data['rating']}";
            if ($data['kupon'] ?? null) $lines[] = "Kupon: {$data['kupon']}%";
            if ($data['ytm'] ?? null) $lines[] = "YTM: {$data['ytm']}%";
        }

        $lines[] = "";
        $lines[] = "NERACA (Balance Sheet)";
        $lines[] = "Total Aset: {$fmt($data['total_asset'] ?? null)}";
        $lines[] = "  - Aset Lancar: {$fmt($data['current_asset'] ?? null)}";
        $lines[] = "    - Kas & Setara Kas: {$fmt($data['cash_equivalents'] ?? null)}";
        $lines[] = "    - Piutang Usaha: {$fmt($data['account_receivable'] ?? null)}";
        $lines[] = "    - Persediaan: {$fmt($data['inventories'] ?? null)}";
        $lines[] = "  - Aset Tidak Lancar: {$fmt($data['fixed_asset'] ?? null)}";
        $lines[] = "Total Liabilitas: {$fmt($data['total_liabilities'] ?? null)}";
        $lines[] = "  - Liabilitas Jangka Pendek: {$fmt($data['current_liabilities'] ?? null)}";
        $lines[] = "  - Liabilitas Jangka Panjang: {$fmt($data['long_term_loans'] ?? null)}";
        $lines[] = "Total Ekuitas: {$fmt($data['equity'] ?? null)}";
        $lines[] = "";
        $lines[] = "LABA RUGI (Income Statement)";
        $lines[] = "Pendapatan Bersih: {$fmt($data['net_revenue'] ?? null)}";
        $lines[] = "Laba Kotor: {$fmt($data['gross_income'] ?? null)}";
        $lines[] = "EBIT: {$fmt($data['ebit'] ?? null)}";
        $lines[] = "EBITDA: {$fmt($data['ebitda'] ?? null)}";
        $lines[] = "Beban Bunga: {$fmt($data['interest_expense'] ?? null)}";
        $lines[] = "Laba Bersih: {$fmt($data['net_income'] ?? null)}";
        $lines[] = "EPS: {$fmt($data['eps'] ?? null)}";
        $lines[] = "";
        $lines[] = "ARUS KAS";
        $lines[] = "Operasional: {$fmt($data['cash_flows_operating_activities'] ?? null)}";
        $lines[] = "Investasi: {$fmt($data['cash_flows_investment'] ?? null)}";
        $lines[] = "Pendanaan: {$fmt($data['cash_flows_financing'] ?? null)}";

        $dataSection = implode("\n", $lines);

        $prompt = <<<PROMPT
{$dataSection}

Berdasarkan data laporan keuangan {$instrumen} lengkap di atas, buatkan analisa MENDALAM (Analisa AI Plus) dalam format JSON dengan struktur EXACT berikut:
{
  "ringkasan_utama": "Ringkasan eksekutif kondisi keuangan dalam 2-3 paragraf dengan metrik kunci",
  "analisa_neraca": "Analisa mendalam struktur aset, liabilitas, dan ekuitas — leverage, likuiditas, solvabilitas",
  "analisa_laba_rugi": "Analisa mendalam profitabilitas: margin kotor, margin EBIT, net margin, tren laba, efisiensi operasional",
  "analisa_arus_kas": "Analisa mendalam kualitas arus kas operasional vs laba, capex, free cash flow, siklus konversi kas",
  "rasio_keuangan": {
    "current_ratio": null,
    "debt_to_equity": null,
    "net_profit_margin": null,
    "roe": null
  },
  "analisa_likuiditas": "Analisa likuiditas jangka pendek dan kemampuan memenuhi kewajiban",
  "analisa_solvabilitas": "Analisa struktur modal dan kemampuan membayar utang jangka panjang",
  "analisa_profitabilitas": "Analisa efisiensi dan profitabilitas perusahaan secara keseluruhan",
  "rekomendasi": "Kesimpulan dan rekomendasi investasi spesifik berdasarkan kondisi keuangan"
}

PETUNJUK:
- Hitung rasio jika data tersedia: current_ratio = current_asset/current_liabilities, DER = total_liabilities/equity, net margin = net_income/net_revenue * 100, ROE = net_income/equity * 100
- Set null jika tidak bisa dihitung
- Gunakan Bahasa Indonesia yang baik dan profesional
- Output HANYA JSON valid tanpa markdown
PROMPT;

        $promptKey = $instrumen === 'Obligasi' ? 'system_analisa_obligasi_plus' : 'system_analisa_saham_plus';
        $systemPrompt = \App\Models\AiPrompt::get($promptKey, "Kamu adalah analis keuangan senior Indonesia yang ahli analisa mendalam laporan keuangan {$instrumen}. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.");

        $response = Http::withToken($this->apiKey)
            ->timeout(120)
            ->post($this->url, [
                'model'       => $this->model,
                'temperature' => 0.3,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $prompt],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Groq API error: ' . $response->body());
        }

        $raw = $response->json('choices.0.message.content', '');
        $parsed = self::parseJsonOutput($raw);

        $narasiParts = [];
        foreach (['ringkasan_utama', 'analisa_neraca', 'analisa_laba_rugi', 'analisa_arus_kas', 'analisa_likuiditas', 'analisa_solvabilitas', 'analisa_profitabilitas', 'rekomendasi'] as $key) {
            if (!empty($parsed[$key])) $narasiParts[] = $parsed[$key];
        }

        return [
            'raw'    => implode("\n\n", $narasiParts),
            'parsed' => $parsed,
        ];
    }

    public function parseLapkeuPdf(string $pdfText, string $instrumen = 'Saham'): array
    {
        $text = mb_substr($pdfText, 0, 20000);

        $isObligasi = $instrumen === 'Obligasi';
        $nameKey    = $isObligasi ? 'nama_obligasi' : 'nama_perusahaan';
        $codeKey    = $isObligasi ? 'kode_obligasi' : 'kode_saham';
        if ($isObligasi) {
            $extraFields = <<<EXTRA
  "{$nameKey}": "string atau null",
  "{$codeKey}": "string atau null",
  "nama_emiten": "string atau null",
  "rating": "string atau null (misal AAA, AA, A, BBB, dll)",
  "kupon": angka atau null,
  "ytm": angka atau null,
EXTRA;
        } else {
            $extraFields = <<<EXTRA
  "{$nameKey}": "string atau null",
  "{$codeKey}": "string atau null",
  "sektor": "string atau null",
EXTRA;
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->post($this->url, [
                'model'       => $this->model,
                'temperature' => 0.1,
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => "Kamu adalah parser laporan keuangan {$instrumen} Indonesia. Ekstrak data keuangan dari teks PDF dan kembalikan HANYA JSON valid tanpa teks lain.",
                    ],
                    [
                        'role'    => 'user',
                        'content' => <<<PROMPT
Ekstrak data laporan keuangan dari teks berikut. Kembalikan HANYA JSON valid:

{
{$extraFields}
  "periode": "string misal Q4 2024 atau null",
  "mata_uang": "IDR atau USD atau null",
  "total_asset": angka atau null,
  "current_asset": angka atau null,
  "cash_equivalents": angka atau null,
  "account_receivable": angka atau null,
  "inventories": angka atau null,
  "fixed_asset": angka atau null,
  "total_liabilities": angka atau null,
  "current_liabilities": angka atau null,
  "long_term_loans": angka atau null,
  "equity": angka atau null,
  "net_revenue": angka atau null,
  "gross_income": angka atau null,
  "laba_operasional": angka atau null,
  "ebit": angka atau null,
  "ebitda": angka atau null,
  "interest_expense": angka atau null,
  "income_before_tax": angka atau null,
  "taxes": angka atau null,
  "net_income": angka atau null,
  "eps": angka atau null,
  "cash_flows_operating_activities": angka atau null,
  "cash_flows_investment": angka atau null,
  "cash_flows_financing": angka atau null
}

ATURAN:
- Prioritaskan periode laporan terbaru yang tersedia.
- Revenue/Pendapatan map ke net_revenue.
- Net Profit/Laba Bersih map ke net_income.
- Total Liability/Liabilitas map ke total_liabilities.
- Cash Flow map ke tiga field arus kas jika tersedia.
- EPS/laba per saham map ke eps dan boleh bernilai desimal.
- Semua angka laporan posisi keuangan, laba rugi, dan arus kas dalam satuan penuh (bukan juta/miliar), null jika tidak ada.
- Jika dokumen menyatakan "dalam jutaan Rupiah", kalikan angka dengan 1.000.000; jika "dalam ribuan Rupiah", kalikan dengan 1.000.
- Output HANYA JSON.

TEKS PDF:
{$text}
PROMPT,
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Groq API error: ' . $response->body());
        }

        $raw = $response->json('choices.0.message.content', '');
        return self::normalizeLapkeuPdfData(self::parseJsonOutput($raw), $isObligasi);
    }

    private static function normalizeLapkeuPdfData(array $data, bool $isObligasi): array
    {
        $aliases = [
            'net_revenue' => ['revenue', 'pendapatan', 'pendapatan_bersih', 'sales', 'net_sales'],
            'net_income' => ['net_profit', 'laba_bersih', 'profit_for_the_year', 'laba_tahun_berjalan'],
            'total_asset' => ['total_assets', 'total_aset'],
            'total_liabilities' => ['total_liability', 'total_liabilitas', 'liabilities'],
            'equity' => ['total_equity', 'ekuitas', 'total_ekuitas'],
            'cash_flows_operating_activities' => ['operating_cash_flow', 'cash_flow_from_operations', 'arus_kas_operasi'],
            'cash_flows_investment' => ['investing_cash_flow', 'cash_flow_from_investing', 'arus_kas_investasi'],
            'cash_flows_financing' => ['financing_cash_flow', 'cash_flow_from_financing', 'arus_kas_pendanaan'],
            'eps' => ['earnings_per_share', 'laba_per_saham'],
        ];

        foreach ($aliases as $target => $keys) {
            if (array_key_exists($target, $data) && $data[$target] !== null && $data[$target] !== '') {
                continue;
            }

            foreach ($keys as $key) {
                if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                    $data[$target] = $data[$key];
                    break;
                }
            }
        }

        $allowed = [
            $isObligasi ? 'nama_obligasi' : 'nama_perusahaan',
            $isObligasi ? 'kode_obligasi' : 'kode_saham',
            'nama_emiten', 'rating', 'kupon', 'ytm', 'sektor',
            'periode', 'mata_uang',
            'total_asset', 'current_asset', 'cash_equivalents', 'account_receivable',
            'inventories', 'fixed_asset', 'total_liabilities', 'current_liabilities',
            'long_term_loans', 'equity', 'net_revenue', 'gross_income',
            'laba_operasional', 'ebit', 'ebitda', 'interest_expense',
            'income_before_tax', 'taxes', 'net_income', 'eps',
            'cash_flows_operating_activities', 'cash_flows_investment',
            'cash_flows_financing',
        ];

        $normalized = [];
        foreach ($allowed as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $normalized[$field] = in_array($field, ['periode', 'mata_uang', 'nama_perusahaan', 'kode_saham', 'nama_obligasi', 'kode_obligasi', 'nama_emiten', 'rating', 'sektor'], true)
                ? ($data[$field] !== '' ? $data[$field] : null)
                : self::normalizeNumericValue($data[$field]);
        }

        return $normalized;
    }

    private static function normalizeNumericValue(mixed $value): float|int|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        $isNegative = str_starts_with($value, '(') && str_ends_with($value, ')');
        $value = trim($value, '() ');
        $value = preg_replace('/[^\d,\.\-]/', '', $value);

        if ($value === '' || $value === '-') {
            return null;
        }

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $lastComma = strrpos($value, ',');
            $lastDot = strrpos($value, '.');
            $value = $lastComma > $lastDot
                ? str_replace('.', '', str_replace(',', '.', $value))
                : str_replace(',', '', $value);
        } elseif (substr_count($value, ',') === 1 && !str_contains($value, '.')) {
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }

        if (!is_numeric($value)) {
            return null;
        }

        $number = (float) $value;
        return $isNegative ? -$number : $number;
    }
}
