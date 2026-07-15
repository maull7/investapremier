<?php

namespace App\Services;

use App\Models\AnalisaReksaDana;

class GroqService
{
    private string $openaiKey;
    private string $openaiModel;
    private string $openaiUrl;

    private string $groqKey;
    private string $groqModel;
    private string $groqUrl;

    public function __construct()
    {
        $this->openaiKey   = config('services.openai.key');
        $this->openaiModel = config('services.openai.model', 'gpt-4.1-mini');
        $this->openaiUrl   = config('services.openai.url');

        $this->groqKey   = config('services.groq.key');
        $this->groqModel = config('services.groq.model', 'llama-3.3-70b-versatile');
        $this->groqUrl   = config('services.groq.url');
    }

    public function callAi(array $messages, int $timeout = 90, float $temperature = 0.3): string
    {
        // Try Groq first (cheaper/faster)
        if ($this->groqKey) {
            try {
                \Log::info('[AI] Mencoba Groq: ' . $this->groqModel);
                $response = \Illuminate\Support\Facades\Http::withToken($this->groqKey)
                    ->timeout($timeout + 30)
                    ->post($this->groqUrl, [
                        'model'       => $this->groqModel,
                        'temperature' => $temperature,
                        'max_tokens'  => 16000,
                        'messages'    => $messages,
                    ]);

                if ($response->successful()) {
                    return $response->json('choices.0.message.content', '');
                }

                $body = $response->body();
                if ($response->status() === 429 || str_contains($body, 'rate_limit_exceeded') || str_contains($body, 'Request too large')) {
                    \Log::warning('[AI] Groq rate/token limit, fallback ke OpenAI.');
                    throw new \RuntimeException('rate_limit_exceeded');
                }

                throw new \RuntimeException('AI API error: ' . $body);
            } catch (\RuntimeException $e) {
                if ($e->getMessage() !== 'rate_limit_exceeded') {
                    throw $e;
                }
                // rate limit — fall through to OpenAI
            } catch (\Throwable $e) {
                \Log::warning('[AI] Groq exception: ' . $e->getMessage() . ', fallback ke OpenAI.');
            }
        }

        // Fallback to OpenAI (with retry on rate limit)
        if ($this->openaiKey) {
            for ($attempt = 1; $attempt <= 2; $attempt++) {
                \Log::info("[AI] Menggunakan OpenAI (fallback, attempt {$attempt}): " . $this->openaiModel);
                $response = \Illuminate\Support\Facades\Http::withToken($this->openaiKey)
                    ->timeout($timeout)
                    ->post($this->openaiUrl, [
                        'model'       => $this->openaiModel,
                        'temperature' => $temperature,
                        'max_tokens'  => 16000,
                        'messages'    => $messages,
                    ]);

                if ($response->successful()) {
                    $content = $response->json('choices.0.message.content', '');
                    if (!empty(trim($content))) {
                        return $content;
                    }
                }

                if ($attempt === 1 && ($response->status() === 429 || str_contains($response->body(), 'rate_limit_exceeded'))) {
                    \Log::warning('[AI] OpenAI rate limit, retry dalam 3 detik...');
                    sleep(3);
                    continue;
                }

                throw new \RuntimeException('AI API error: ' . $response->body());
            }
        }

        throw new \RuntimeException('AI API error: Tidak ada API key yang tersedia.');
    }

    public function parseFfsPdf(string $pdfText, ?string $documentType = null): array
    {
        $text = mb_substr($pdfText, 0, 60000);

        $systemPrompt = 'Kamu adalah parser dokumen Fund Fact Sheet (FFS) reksa dana Indonesia. Ekstrak data dari teks PDF dan kembalikan HANYA JSON valid tanpa teks lain. Perhatikan kode ISIN (International Securities Identification Number) yang biasanya tercantum di bagian informasi umum reksa dana, dekat dengan kode reksa dana.';

        if ($documentType === 'informasi_lainnya') {
            $systemPrompt = 'Kamu adalah parser dokumen reksa dana Indonesia. Fokus ekstrak informasi umum: nama reksa dana, jenis reksa dana, manajer investasi, bank kustodian, benchmark, tujuan investasi, kebijakan investasi, tanggal peluncuran, mata uang, management fee, custodian fee, return YTD, return 1 tahun, total AUM, NAB/UP, unit penyertaan, tanggal data, ffs_bulan, ffs_tahun. Kembalikan HANYA JSON valid tanpa teks lain.';
        } elseif ($documentType === 'portofolio_efek') {
            $systemPrompt = 'Kamu adalah parser dokumen portofolio efek reksa dana Indonesia. Ekstrak SEMUA holdings (jangan hanya top 10, ambil seluruh daftar): daftar efek saham (kode, nama, sektor, bobot, nilai pasar, harga perolehan, persen NAB, jumlah lembar), daftar obligasi (kode, nama, bobot, YTM, kupon, jatuh tempo, penerbit, rating, nilai nominal, nilai pasar), daftar sukuk, daftar deposito bank, kas di bank, instrumen pasar uang, piutang bunga, komposisi sektor, alokasi aset, kinerja bulanan. Kembalikan HANYA JSON valid tanpa teks lain.';
        } elseif ($documentType === 'pengukuran_nilai_wajar') {
            $systemPrompt = 'Kamu adalah parser dokumen Pengukuran Nilai Wajar reksa dana Indonesia. Fokus ekstrak: fair_value_level_1 (angka rupiah), fair_value_level_2 (angka rupiah), fair_value_level_3 (angka rupiah), unit_milik_investor (jumlah unit), unit_milik_mi (jumlah unit), total_unit_beredar (jumlah unit). Kembalikan HANYA JSON valid tanpa teks lain.';
        } elseif ($documentType === 'bs_is_cf_pup') {
            $systemPrompt = 'Kamu adalah parser Laporan Keuangan reksa dana Indonesia (Balance Sheet/Neraca, Income Statement/Laba Rugi, Cash Flow/Arus Kas, Perubahan Unit Penyertaan). Fokus ekstrak: total_aset, total_liabilitas, kas_dan_bank, piutang_bunga, piutang_dividen, piutang_lain, utang_pajak, utang_lain, pendapatan_bunga, pendapatan_dividen, gain_realized, gain_unrealized, beban_mi, beban_kustodian, beban_lain, laba_bersih, arus_kas_operasi, arus_kas_pendanaan, kas_awal_tahun, kas_akhir_tahun, total_hasil_investasi, hasil_investasi_setelah_biaya, biaya_operasi, portfolio_turnover_ratio, persentase_pph, portofolio_efek, instrumen_pasar_uang, piutang_transaksi_efek, piutang_bunga_dan_dividen, uang_muka_diterima, liabilitas_pembelian_kembali, beban_akrual, liabilitas_atas_biaya, pembelian_kembali_unit_penyertaan, utang_pajak_lainnya, pendapatan_investasi, pendapatan_lainnya, beban_investasi, beban_pengelolaan_investasi, pembelian_efek_ekuitas, penjualan_efek_ekuitas, penerimaan_bunga_deposito, penerimaan_bunga_jasa_giro, penerimaan_dividen_kas, pembayaran_jasa_pengelolaan, pembayaran_jasa_kustodian, pembayaran_beban_lain_arus, kas_bersih_aktivitas_operasi, penerimaan_penjualan_unit, pembayaran_pembelian_kembali_unit, kas_bersih_aktivitas_pendanaan, kenaikan_kas_setara_kas. Kembalikan HANYA JSON valid tanpa teks lain.';
        } elseif ($documentType === 'prospektus') {
            $systemPrompt = 'Kamu adalah parser dokumen Prospektus reksa dana Indonesia. Fokus ekstrak: tujuan investasi, kebijakan investasi, benchmark, management fee, custodian fee, tanggal peluncuran, mata uang, bank kustodian, manajer investasi. Kembalikan HANYA JSON valid tanpa teks lain.';
        } elseif ($documentType === 'laporan_tahunan') {
            $systemPrompt = 'Kamu adalah parser Laporan Tahunan reksa dana Indonesia. Fokus ekstrak: neraca (total aset, liabilitas, kas, piutang, utang), laba rugi (pendapatan, beban, laba bersih), arus kas, rasio keuangan (total hasil investasi, biaya operasi, portfolio turnover). Kembalikan HANYA JSON valid tanpa teks lain.';
        } elseif ($documentType === 'laporan_keuangan') {
            $systemPrompt = 'Kamu adalah parser Laporan Keuangan Audited reksa dana Indonesia. Fokus ekstrak: fair value level 1/2/3, unit milik investor/MI/total, neraca detail, laba rugi detail, arus kas detail, persentase PPh. Kembalikan HANYA JSON valid tanpa teks lain.';
        }

        $messages = [
            [
                'role'    => 'system',
                'content' => $systemPrompt,
            ],
            [
                'role'    => 'user',
                'content' => <<<PROMPT
Ekstrak data dari teks Fund Fact Sheet berikut. Kembalikan HANYA JSON valid dengan struktur PERSIS seperti ini:

{
  "nama_reksa_dana": "string atau null",
  "isin_code": "string kode ISIN (International Securities Identification Number) atau null",
  "jenis_reksa_dana": "Saham" atau "Pendapatan Tetap" atau "Campuran" atau "Pasar Uang" atau null,
  "kategori": ["Konvensional", "Syariah", "index", "ETF"],
  "manajer_investasi": "string nama MI atau null",
  "bank_kustodian": "string nama bank kustodian atau null",
  "tanggal_peluncuran": "YYYY-MM-DD tanggal peluncuran reksa dana atau null",
  "mata_uang": "string mata uang misal IDR, USD atau null",
  "benchmark": "string nama benchmark/index acuan atau null",
  "tujuan_investasi": "string tujuan investasi atau null",
  "kebijakan_investasi": "string kebijakan investasi atau null",
  "total_aum": angka rupiah penuh atau null,
  "unit_penyertaan": angka jumlah unit penyertaan atau null,
  "nab_per_unit": angka NAB/UP atau null,
  "total_marcap_10_efek": angka rupiah penuh atau null,
  "tanggal_data": "YYYY-MM-DD atau null",
  "ffs_bulan": angka bulan 1-12 atau null,
  "ffs_tahun": angka tahun 4 digit atau null,
  "return_ytd": angka persen return YTD atau null,
  "return_5y": angka persen return 5 tahun atau null,
  "return_1y": angka persen return 1 tahun atau null,
  "return_1m": angka persen return 1 bulan atau null,
  "total_return": angka persen total return atau null,
  "biaya_operasi": angka persen biaya operasi atau null,
  "portfolio_turnover_ratio": angka portfolio turnover ratio atau null,
  "management_fee": angka persen management fee atau null,
  "custodian_fee": angka persen custodian fee atau null,
  "subscription_fee": angka persen subscription fee/biaya pembelian atau null,
  "redemption_fee": angka persen redemption fee/biaya penjualan atau null,
  "switching_fee": angka persen switching fee atau null,
  "expense_ratio": angka persen expense ratio/biaya operasional total atau null,
  "risk_category": "Rendah" atau "Sedang" atau "Tinggi" atau null,
  "risk_descriptions": ["string deskripsi risiko seperti Risiko Perubahan Kondisi Ekonomi dan Politik, Risiko Kredit, dll"],
  "sharpe_ratio": angka sharpe ratio atau null,
  "standard_deviation": angka standard deviation/stdev atau null,
  "beta": angka beta atau null,
  "max_drawdown": angka persen max drawdown atau null,
  "total_aset": angka rupiah penuh total aset atau null,
  "kas_dan_bank": angka rupiah penuh kas dan bank atau null,
  "piutang_bunga": angka rupiah penuh piutang bunga atau null,
  "piutang_dividen": angka rupiah penuh piutang dividen atau null,
  "piutang_lain": angka rupiah penuh piutang lain-lain atau null,
  "utang_pajak": angka rupiah penuh utang pajak atau null,
  "utang_lain": angka rupiah penuh utang lain-lain atau null,
  "pendapatan_bunga": angka rupiah penuh pendapatan bunga atau null,
  "pendapatan_dividen": angka rupiah penuh pendapatan dividen atau null,
  "gain_realized": angka rupiah penuh gain realized atau null,
  "gain_unrealized": angka rupiah penuh gain unrealized atau null,
  "beban_mi": angka rupiah penuh beban manajer investasi atau null,
  "beban_kustodian": angka rupiah penuh beban kustodian atau null,
  "beban_lain": angka rupiah penuh beban lain-lain atau null,
  "laba_bersih": angka rupiah penuh laba bersih atau null,
  "total_beban": angka rupiah penuh total beban atau null,
  "laba_sebelum_pajak": angka rupiah penuh laba sebelum pajak atau null,
  "beban_pajak_penghasilan": angka rupiah penuh beban pajak penghasilan atau null,
  "laba_bersih_tahun_berjalan": angka rupiah penuh laba bersih tahun berjalan atau null,
  "penghasilan_komprehensif_lain": angka rupiah penuh penghasilan komprehensif lain atau null,
  "penghasilan_komprehensif_lain_setelah_pajak": angka rupiah penuh penghasilan komprehensif lain setelah pajak atau null,
  "penghasilan_komprehensif_tahun_berjalan": angka rupiah penuh penghasilan komprehensif tahun berjalan atau null,
  "arus_kas_operasi": angka rupiah penuh arus kas operasi atau null,
  "arus_kas_pendanaan": angka rupiah penuh arus kas pendanaan atau null,
  "kas_awal_tahun": angka rupiah penuh kas awal tahun atau null,
  "kas_akhir_tahun": angka rupiah penuh kas akhir tahun atau null,
  "portofolio_efek": angka rupiah penuh portofolio efek atau null,
  "instrumen_pasar_uang": angka rupiah penuh instrumen pasar uang atau null,
  "piutang_transaksi_efek": angka rupiah penuh piutang transaksi efek atau null,
  "piutang_bunga_dan_dividen": angka rupiah penuh piutang bunga dan dividen atau null,
  "uang_muka_diterima": angka rupiah penuh uang muka diterima atau null,
  "liabilitas_pembelian_kembali": angka rupiah penuh liabilitas pembelian kembali atau null,
  "beban_akrual": angka rupiah penuh beban akrual atau null,
  "liabilitas_atas_biaya": angka rupiah penuh liabilitas atas biaya atau null,
  "pembelian_kembali_unit_penyertaan": angka rupiah penuh pembelian kembali unit penyertaan atau null,
  "utang_pajak_lainnya": angka rupiah penuh utang pajak lainnya atau null,
  "pendapatan_investasi": angka rupiah penuh pendapatan investasi atau null,
  "pendapatan_lainnya": angka rupiah penuh pendapatan lainnya atau null,
  "beban_investasi": angka rupiah penuh beban investasi atau null,
  "beban_pengelolaan_investasi": angka rupiah penuh beban pengelolaan investasi atau null,
  "pembelian_efek_ekuitas": angka rupiah penuh pembelian efek ekuitas atau null,
  "penjualan_efek_ekuitas": angka rupiah penuh penjualan efek ekuitas atau null,
  "penerimaan_bunga_deposito": angka rupiah penuh penerimaan bunga deposito atau null,
  "penerimaan_bunga_jasa_giro": angka rupiah penuh penerimaan bunga jasa giro atau null,
  "penerimaan_dividen_kas": angka rupiah penuh penerimaan dividen kas atau null,
  "pembayaran_jasa_pengelolaan": angka rupiah penuh pembayaran jasa pengelolaan atau null,
  "pembayaran_jasa_kustodian": angka rupiah penuh pembayaran jasa kustodian atau null,
  "pembayaran_beban_lain_arus": angka rupiah penuh pembayaran beban lain arus atau null,
  "kas_bersih_aktivitas_operasi": angka rupiah penuh kas bersih aktivitas operasi atau null,
  "penerimaan_penjualan_unit": angka rupiah penuh penerimaan penjualan unit atau null,
  "pembayaran_pembelian_kembali_unit": angka rupiah penuh pembayaran pembelian kembali unit atau null,
  "kas_bersih_aktivitas_pendanaan": angka rupiah penuh kas bersih aktivitas pendanaan atau null,
  "kenaikan_kas_setara_kas": angka rupiah penuh kenaikan kas dan setara kas atau null,
  "total_hasil_investasi": angka persen total hasil investasi atau null,
  "hasil_investasi_setelah_biaya": angka persen hasil investasi setelah biaya pemasaran atau null,
  "persentase_pph": angka persen penghasilan kena pajak atau null,
  "fair_value_level_1": angka rupiah penuh fair value level 1 atau null,
  "fair_value_level_2": angka rupiah penuh fair value level 2 atau null,
  "fair_value_level_3": angka rupiah penuh fair value level 3 atau null,
  "unit_milik_investor": angka jumlah unit milik investor atau null,
  "unit_milik_mi": angka jumlah unit milik manajer investasi atau null,
  "total_unit_beredar": angka jumlah total unit beredar atau null,
  "alokasi_aset": [
    {"nama_aset": "Saham/Obligasi/Pasar Uang/Kas/Deposito/lainnya", "persentase": angka_persen}
  ],
  "sektor": [
    {"nama_sektor": "string", "bobot": angka_persen}
  ],
  "efek": [
    {
      "kode_efek": "string misal BBCA",
      "nama_efek": "string nama lengkap",
      "sektor": "string nama sektor efek ini atau kosong",
      "bobot": angka_persen,
      "kontribusi_kinerja": angka_persen_kontribusi_ihsg_atau_null,
      "ihsg_contribution": angka_persen_kontribusi_terhadap_ihsg_atau_null,
      "market_cap": angka_rupiah_penuh_atau_null,
      "nilai_pasar": angka_rupiah_penuh_nilai_pasar_efek_atau_null,
      "harga_perolehan": angka_rupiah_penuh_harga_perolehan_atau_null,
      "persen_nab": angka_persen_terhadap_nab_atau_null,
      "return_1m": angka_persen_return_1_bulan_atau_null,
      "return_3m": angka_persen_return_3_bulan_atau_null,
      "return_6m": angka_persen_return_6_bulan_atau_null,
      "return_1y": angka_persen_return_1_tahun_atau_null,
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
      "nilai_pasar": angka_rupiah_penuh_nilai_pasar_atau_null,
      "durasi": angka_tahun_atau_null,
      "ytm": angka_persen_yield_to_maturity_atau_null,
      "kupon": angka_persen_kupon_atau_null,
      "tanggal_jatuh_tempo": "YYYY-MM-DD tanggal jatuh tempo obligasi atau null",
      "penerbit": "string nama penerbit obligasi atau null",
      "persen_nab": angka_persen_terhadap_nab_atau_null,
      "rating": "AAA" atau "AA+" atau "AA" atau "AA-" atau "A+" atau "A" atau "A-" atau "BBB+" atau "BBB" atau "BBB-" atau "BB" atau "B" atau "CCC" atau "D" atau null
    }
  ],
  "sukuk": [
    {
      "kode_sukuk": "string misal SR019, PBS037, atau kosong",
      "nama_sukuk": "string nama lengkap",
      "jenis_sukuk": "Negara" atau "Korporasi" atau null,
      "bobot": angka_persen,
      "yield": angka_persen_imbal_hasil_atau_null,
      "jatuh_tempo": "string tahun misal 2028 atau null",
      "persen_nab": angka_persen_terhadap_nab_atau_null,
      "rating": "AAA" atau "AA+" atau "AA" atau "AA-" atau "A+" atau null
    }
  ],
  "bank": [
    {
      "nama_bank": "string",
      "jenis_bank": "string jenis bank atau null",
      "bobot": angka_persen_atau_null,
      "nilai_pasar": angka_rupiah_penuh_nilai_pasar_atau_null,
      "tingkat_bunga": angka_persen_tingkat_bunga_atau_null,
      "jangka_waktu": angka_jangka_waktu_hari_atau_null,
      "persen_nab": angka_persen_terhadap_nab_atau_null,
      "car": angka_persen_atau_null,
      "npl": angka_persen_atau_null,
      "klasifikasi_risiko": "Rendah" atau "Sedang" atau "Tinggi" atau null
    }
  ]
}

ATURAN:
- nama_reksa_dana harus nama produk reksa dana yang spesifik, bukan "Fund Fact Sheet", bukan nama manajer investasi, dan bukan nama bank kustodian.
- kategori boleh berisi lebih dari satu nilai jika dokumen menyebut Syariah, ETF, Index/Indeks, atau Konvensional.
- kode_efek diisi ticker BEI (misal "BBCA") jika tersedia di dokumen; jika dokumen hanya menampilkan nama PT tanpa ticker, isi kode_efek kosong "" dan nama_efek = nama PT lengkap.
- Array "efek" HANYA untuk saham/ekuitas (Efek Ekuitas/Equity/Shares). JANGAN masukkan obligasi/bonds ke array efek.
- Array "obligasi" untuk efek utang/bonds/debt instruments (FR0072, Berkelanjutan Waskita, dll). JANGAN masukkan saham ke array obligasi.
- sektor pada daftar efek wajib diisi jika sektor efek tersedia di dokumen atau dapat disimpulkan kuat dari tabel sektor/top holdings.
- kontribusi_kinerja adalah kontribusi terhadap IHSG/kinerja portofolio dalam persen jika tersedia.
- market_cap dalam Rupiah penuh jika tersedia.
- kode_obligasi dan nama_obligasi harus dipisah; contoh kode FR0091, PBS036, INDON34.
- Untuk bank, isi bobot/CAR/NPL/klasifikasi jika ada. Jika hanya ada nama dan bobot deposito/kas di bank, tetap isi nama_bank dan bobot.
- total_aum dan total_marcap_10_efek dalam Rupiah penuh (misal 1.5 triliun = 1500000000000)
- unit_penyertaan adalah jumlah unit penyertaan/units outstanding jika tersedia.
- nab_per_unit adalah NAB/UP atau NAV per unit jika tersedia.
- alokasi_aset adalah asset allocation/komposisi aset seperti Saham, Obligasi, Pasar Uang, Kas, Deposito. Jangan isi dari komposisi sektor kecuali tabelnya memang asset allocation.
- ffs_bulan dan ffs_tahun mengikuti tanggal/periode data FFS jika tersedia.
- bobot dalam persen (misal 12.5, bukan 0.125)
- Ekstrak SEMUA efek/saham yang ada di dokumen, bukan hanya top 10. Jika ada 50 saham, masukkan semua 50. Begitu juga obligasi dan sukuk, masukkan SEMUA.
- periode kinerja format YYYY-MM (misal "2024-03")
- Jika data tidak ada gunakan null atau array kosong []
- Output HANYA JSON valid, tanpa penjelasan, tanpa markdown

TEKS FFS:
{$text}
PROMPT,
            ],
        ];

        $raw = $this->callAi($messages, 180, 0.1);
        return self::parseJsonOutput($raw);
    }

    /**
     * Strict prospectus financial extraction engine for "Parse semua partisi".
     */
    public function parseProspectusFinancialStrict(string $text, ?string $documentType = null): array
    {
        $text = mb_substr($text, 0, 60000);

        $systemPrompt = <<<'PROMPT'
# SYSTEM PROMPT - Prospectus Financial Extractor

Anda adalah Financial Prospectus Extraction Engine.

Tugas Anda BUKAN menganalisis atau merangkum dokumen.

Tugas Anda hanya mengekstrak data secara akurat dari section prospektus yang diberikan.

## Tujuan

Ekstrak informasi ke dalam JSON sesuai schema yang diberikan.

---

# Aturan Utama

1. Jangan mengarang data.
2. Jangan menebak nilai.
3. Jika data tidak ditemukan, gunakan null.
4. Jangan mengubah angka.
5. Jangan menghitung ulang total.
6. Jangan menyimpulkan.
7. Jangan memperbaiki angka.
8. Jangan menghilangkan item yang ditemukan.
9. Output HARUS berupa JSON valid.
10. Jangan menambahkan penjelasan apa pun di luar JSON.

---

# Aturan Mengenai Tahun

Prospektus dapat memiliki kolom tahun yang berbeda-beda, misalnya:

* 2025 | 2024
* 2024 | 2023
* 2023 | 2022

AI HARUS:

* Mendeteksi seluruh kolom tahun secara otomatis.
* Jangan meng-hardcode tahun.
* Gunakan tahun yang ditemukan sebagai key JSON.

Contoh:

Input

Item | 2025 | 2024

Output

{
"years":[2025,2024]
}

---

# Aturan Mengenai Kolom Catatan

Prospektus sering memiliki kolom seperti:

Catatan

Notes

No.

Referensi

atau angka seperti

24

31

34

12

dst.

SELURUH nilai tersebut HARUS DIABAIKAN.

Kolom tersebut bukan nilai keuangan.

Contoh

Kas dan Bank | 24 | 767.000.672 | 177.644.486

Hasil

{
"cash_bank":{
"2025":767000672,
"2024":177644486
}
}

BUKAN

{
"cash_bank":24
}

---

# Aturan Mengenai Angka

Konversikan seluruh angka menjadi number.

Hilangkan:

* titik ribuan
* spasi
* pemisah format

Contoh

67.399.852.053

menjadi

67399852053

Jika angka negatif ditulis:

(1.250.000)

ubah menjadi

-1250000

Jika angka menggunakan minus

-1.250.000

ubah menjadi

-1250000

---

# Aturan Mengenai Item

Gunakan nama item sesuai schema.

Jika ditemukan sinonim, lakukan normalisasi.

Contoh

Kas
Kas dan Bank
Kas pada Bank
Kas & Bank

→ cash_bank

Portofolio Efek
Efek
Investasi pada Efek

→ portfolio_effect

---

# Aturan Mengenai Tabel

Jika section berupa tabel:

* identifikasi header terlebih dahulu
* identifikasi kolom tahun
* identifikasi nama item
* abaikan kolom catatan
* ekstrak seluruh nilai

Jangan membaca tabel dari kiri ke kanan secara sembarangan.

Gunakan struktur tabel.

---

# Aturan Mengenai Portofolio

Jika menemukan daftar portofolio seperti:

Obligasi
Saham
Sukuk
Deposito
Efek Beragun Aset
Reksa Dana

Ekstrak seluruh item menjadi array.

Contoh

[
{
"type":"Obligasi",
"name":"FR0101",
"percentage":8.52,
"fair_value":23500000000
}
]

Jangan membatasi jumlah item.

---

# Aturan Mengenai Section

Fokus HANYA pada section yang diberikan.

Jangan mencari data di luar section.

Jangan menggabungkan informasi dari section lain.

---

# Aturan Confidence

Untuk setiap field tambahkan confidence.

Contoh

{
"cash_bank":{
"2025":767000672,
"2024":177644486,
"confidence":0.99
}
}

Jika AI tidak yakin karena format ambigu, turunkan confidence.

---

# Validasi Sebelum Menghasilkan Output

Sebelum mengembalikan JSON lakukan pengecekan:

* Apakah tahun sudah benar?
* Apakah kolom Catatan sudah diabaikan?
* Apakah angka sudah benar?
* Apakah item sudah sesuai schema?
* Apakah ada nilai yang tertukar dengan nomor catatan?
* Apakah output JSON valid?

Jika tidak yakin terhadap suatu nilai, isi null dan turunkan confidence.

Prioritaskan akurasi daripada kelengkapan.
PROMPT;

        $schemaDocType = $documentType ?: 'laporan_tahunan';

        $userPrompt = <<<PROMPT
Ekstrak data dari teks prospektus/reksa dana berikut. Kembalikan HANYA JSON valid dengan struktur PERSIS seperti ini:

{
  "years": [tahun_terbaru, tahun_sebelumnya],

  "nama_reksa_dana": "string atau null",
  "jenis_reksa_dana": "Saham" atau "Pendapatan Tetap" atau "Campuran" atau "Pasar Uang" atau null,
  "kategori": ["Konvensional"|"Syariah"|"index"|"ETF"],
  "manajer_investasi": "string atau null",
  "bank_kustodian": "string atau null",
  "tanggal_peluncuran": "YYYY-MM-DD atau null",
  "mata_uang": "string atau null",
  "benchmark": "string atau null",
  "tujuan_investasi": "string atau null",
  "kebijakan_investasi": "string atau null",
  "total_aum": angka rupiah penuh atau null,
  "unit_penyertaan": angka jumlah unit penyertaan atau null,
  "nab_per_unit": angka NAB/UP atau null,
  "total_marcap_10_efek": angka rupiah penuh atau null,
  "tanggal_data": "YYYY-MM-DD atau null",
  "ffs_bulan": angka bulan 1-12 atau null,
  "ffs_tahun": angka tahun 4 digit atau null,
  "return_ytd": angka persen return YTD atau null,
  "return_1y": angka persen return 1 tahun atau null,
  "total_return": angka persen total return atau null,
  "biaya_operasi": angka persen biaya operasi atau null,
  "portfolio_turnover_ratio": angka portfolio turnover ratio atau null,
  "management_fee": angka persen management fee atau null,
  "custodian_fee": angka persen custodian fee atau null,

  "aset": {
    "total_aset": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "kas_dan_bank": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "piutang_transaksi_efek": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "piutang_bunga": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "piutang_dividen": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "piutang_lain": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "portofolio_efek": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "instrumen_pasar_uang": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "total_unit_beredar": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "nab_per_unit": {"2025": angka, "2024": angka, "confidence": 0.0-1.0}
  },
  "liabilitas": {
    "total_liabilitas": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "uang_muka_diterima": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "liabilitas_pembelian_kembali": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "beban_akrual": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "liabilitas_atas_biaya": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "utang_pajak": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "utang_lain": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "nilai_aset_bersih": {"2025": angka, "2024": angka, "confidence": 0.0-1.0}
  },
  "laba_rugi": {
    "pendapatan_bunga": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "pendapatan_dividen": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "pendapatan_lainnya": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "gain_realized": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "gain_unrealized": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "total_pendapatan": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "beban_pengelolaan_investasi": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "beban_kustodian": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "beban_lain": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "total_beban": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "laba_sebelum_pajak": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "beban_pajak_penghasilan": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "laba_bersih_tahun_berjalan": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "penghasilan_komprehensif_lain": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "penghasilan_komprehensif_tahun_berjalan": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "laba_bersih": {"2025": angka, "2024": angka, "confidence": 0.0-1.0}
  },
  "arus_kas": {
    "penerimaan_bunga_deposito": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "penerimaan_dividen_kas": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "penjualan_efek_ekuitas": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "pembelian_efek_ekuitas": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "beban_investasi": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "arus_kas_operasi": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "penerimaan_penjualan_unit": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "pembayaran_pembelian_kembali_unit": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "arus_kas_pendanaan": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "kas_awal_tahun": {"2025": angka, "2024": angka, "confidence": 0.0-1.0},
    "kas_akhir_tahun": {"2025": angka, "2024": angka, "confidence": 0.0-1.0}
  },
  "perubahan_aset_bersih": {
    "penghasilan_komprehensif_tahun_berjalan": {"2025": angka, "2024": angka, "confidence": 0.0-1.0}
  },

  "alokasi_aset": [{"nama_aset": "string", "persentase": angka}],
  "sektor": [{"nama_sektor": "string", "bobot": angka_persen}],
  "efek": [{"kode_efek":"string", "nama_efek":"string", "sektor":"string", "bobot":angka_persen, "kontribusi_kinerja":angka_persen, "ihsg_contribution":angka_persen, "market_cap":angka, "nilai_pasar":angka, "harga_perolehan":angka, "persen_nab":angka_persen, "return_1m":angka_persen, "return_3m":angka_persen, "return_6m":angka_persen, "return_1y":angka_persen, "top_10": boolean}],
  "kinerja": [{"periode": "YYYY-MM", "return_pct": angka}],
  "obligasi": [{"kode_obligasi":"string", "nama_obligasi":"string", "bobot":angka_persen, "nilai_pasar":angka, "durasi":angka, "ytm":angka_persen, "kupon":angka_persen, "tanggal_jatuh_tempo":"YYYY-MM-DD", "penerbit":"string", "persen_nab":angka_persen, "rating":"string"}],
  "sukuk": [{"kode_sukuk":"string", "nama_sukuk":"string", "jenis_sukuk":"Negara"|"Korporasi", "bobot":angka_persen, "yield":angka_persen, "jatuh_tempo":"string", "persen_nab":angka_persen, "rating":"string"}],
  "bank": [{"nama_bank":"string", "jenis_bank":"string", "bobot":angka_persen, "nilai_pasar":angka, "tingkat_bunga":angka_persen, "jangka_waktu":angka, "persen_nab":angka_persen, "car":angka_persen, "npl":angka_persen, "klasifikasi_risiko":"Rendah"|"Sedang"|"Tinggi"}]
}

ATURAN PENTING:
- Section type saat ini: {$schemaDocType}
- Fokus HANYA pada section yang diberikan.
- Deteksi kolom tahun secara otomatis dari teks/tabel; isi "years" dengan tahun yang ditemukan (terbaru dulu).
- ABAIKAN kolom Catatan/Notes/No./Referensi.
- Konversi angka: hilangkan titik ribuan, ubah (1.234) menjadi negatif, ubah -1.234 menjadi negatif.
- Angka persen dalam desimal (12,5% = 12.5).
- Setiap field finansial WAJIB year-based dengan confidence.
- Jika data tidak ada untuk suatu tahun, isi null.
- Jika data tidak ada gunakan null atau array kosong [].
- Output HANYA JSON valid, tanpa penjelasan, tanpa markdown.

TEKS:
{$text}
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $raw = $this->callAi($messages, 180, 0.1);
        return self::parseJsonOutput($raw);
    }

    /**
     * Slot 1: Informasi Lainnya - minimal schema (info umum RD)
     */
    public function parseInformasiLainnya(string $text): array
    {
        $text = mb_substr($text, 0, 15000);
        $messages = [
            ['role' => 'system', 'content' => 'Ekstrak informasi umum reksa dana dari teks. Kembalikan HANYA JSON valid.'],
            [
                'role' => 'user',
                'content' => <<<PROMPT
Ekstrak data dari teks berikut. Kembalikan HANYA JSON:

{
  "management_fee": angka persen atau null,
  "custodian_fee": angka persen atau null,
  "total_aum": angka rupiah penuh atau null,
  "unit_penyertaan": angka atau null,
  "nab_per_unit": angka atau null,
  "tanggal_data": "YYYY-MM-DD atau null",
  "ffs_bulan": angka 1-12 atau null,
  "ffs_tahun": angka tahun atau null,
  "return_ytd": angka persen atau null,
  "return_1y": angka persen atau null,
  "total_return": angka persen atau null,
  "biaya_operasi": angka persen atau null,
  "portfolio_turnover_ratio": angka atau null
}

ATURAN: angka rupiah dalam bentuk penuh (1.5 triliun = 1500000000000). Jika tidak ada, null.

ATURAN WAJIB:

1. JANGAN MENEBAK.
   Jika informasi tidak ditemukan secara eksplisit pada teks, isi null.

2. Gunakan HANYA informasi dari teks yang diberikan.

3. Semua angka HARUS berupa JSON Number.
   Jangan gunakan string untuk angka.

   BENAR:
   {
     "total_return": 4.5
   }

   SALAH:
   {
     "total_return": "4.5%"
   }

4. Konversi format angka Indonesia.

   Contoh:
   1.234,56 -> 1234.56
   4,50% -> 4.5
   0,24 -> 0.24

5. Nilai negatif dalam tanda kurung harus dikonversi menjadi negatif.

   Contoh:
   (2,98%) -> -2.98
   (10,50) -> -10.5

6. management_fee dan custodian_fee:

   Cari variasi istilah berikut:
   - Management Fee
   - Imbal Jasa Manajer Investasi
   - Fee Manajer Investasi
   - Jasa Pengelolaan
   - Custodian Fee
   - Imbal Jasa Bank Kustodian
   - Fee Kustodian

   Simpan sebagai angka persen.

7. total_aum:

   Cari istilah:
   - Total AUM
   - Dana Kelolaan
   - Nilai Aktiva Bersih
   - Total NAB
   - Asset Under Management

   Konversi ke angka penuh.

   Contoh:
   Rp 1,5 Triliun -> 1500000000000
   Rp 250 Miliar -> 250000000000
   Rp 15 Juta -> 15000000

8. unit_penyertaan:

   Cari:
   - Unit Penyertaan Beredar
   - Jumlah Unit Penyertaan
   - Outstanding Units

   Simpan sebagai angka.

9. nab_per_unit:

   Cari:
   - NAB/UP
   - NAB per Unit
   - Nilai Aktiva Bersih per Unit Penyertaan
   - NAV per Unit

   Simpan sebagai angka.

10. tanggal_data:

    Gunakan tanggal laporan yang paling relevan terhadap data yang diekstrak.

    Prioritas:
    - Tanggal Fund Fact Sheet
    - Per tanggal ...
    - As of ...
    - Tanggal tabel rasio keuangan

    Format:
    YYYY-MM-DD

11. ffs_bulan dan ffs_tahun:

    Jika ditemukan Fund Fact Sheet:

    Contoh:
    "Fund Fact Sheet Januari 2025"

    hasil:
    {
      "ffs_bulan": 1,
      "ffs_tahun": 2025
    }

12. return_ytd:

    Cari label:
    - YTD
    - Year To Date
    - Kinerja YTD

13. return_1y:

    Cari label:
    - 1 Tahun
    - 1Y
    - One Year
    - Return 1 Tahun

14. total_return:

    Cari label:
    - Total hasil investasi
    - Total Return
    - Kinerja investasi
    - Return investasi

    Jika terdapat tabel rasio keuangan:
    "Total hasil investasi"
    gunakan nilai tersebut.

15. biaya_operasi:

    Cari label:
    - Biaya operasi
    - Operating expenses
    - Expense ratio

16. portfolio_turnover_ratio:

    Cari label:
    - Perputaran portofolio
    - Portfolio turnover
    - Portfolio turnover ratio

    Konversi:
    0,24 : 1 -> 0.24
    3,88 : 1 -> 3.88

17. Jika terdapat beberapa tahun data:

    Gunakan tahun TERBARU.

    Contoh:

    2025 = 4,50%
    2024 = (2,98%)

    hasil:
    {
      "total_return": 4.5,
      "tanggal_data": "2025-12-31"
    }

18. Jika ada lebih dari satu kandidat nilai untuk suatu field,
    pilih nilai yang paling dekat dengan tanggal_data.

19. Jangan membuat field tambahan.

20. Output harus berupa JSON yang valid dan dapat langsung diparsing oleh json_decode().


TEKS:
{$text}
PROMPT,
            ],
        ];
        return self::parseJsonOutput($this->callAi($messages, 60, 0.1));
    }

    /**
     * Slot 2: Portofolio Efek - minimal schema (daftar holdings)
     */
    public function parsePortofolioEfek(string $text): array
    {
        $text = mb_substr($text, 0, 60000);
        $messages = [
            ['role' => 'system', 'content' => 'Ekstrak portofolio efek/holdings reksa dana dari teks. Kembalikan HANYA JSON valid.'],
            [
                'role' => 'user',
                'content' => <<<PROMPT
Ekstrak portofolio dari teks berikut. Kembalikan HANYA JSON:

{
  "alokasi_aset": [{"nama_aset":"string","persentase":angka}],
  "sektor": [{"nama_sektor":"string","bobot":angka_persen}],
  "efek": [{"kode_efek":"string atau kosong","nama_efek":"string nama lengkap PT","sektor":"string","bobot":angka_persen,"nilai_pasar":angka_atau_null,"harga_perolehan":angka_atau_null,"persen_nab":angka_atau_null,"return_1m":angka_persen_return_1_bulan_atau_null,"return_3m":angka_persen_return_3_bulan_atau_null,"return_6m":angka_persen_return_6_bulan_atau_null,"return_1y":angka_persen_return_1_tahun_atau_null,"ihsg_contribution":angka_persen_kontribusi_ihsg_atau_null,"top_10":true/false,"jumlah_lembar":angka_atau_null,"harga_perolehan_rata_rata":angka_rp_per_lembar_atau_null}],
  "obligasi": [{"kode_obligasi":"string misal FR0072","nama_obligasi":"string nama lengkap","bobot":angka_persen,"nilai_pasar":angka_atau_null,"ytm":angka_atau_null,"kupon":angka_atau_null,"tanggal_jatuh_tempo":"YYYY-MM-DD atau null","penerbit":"string atau null","rating":"string atau null","persen_nab":angka_atau_null,"nilai_nominal":angka_atau_null,"harga_perolehan_rata_rata":angka_persen_atau_null,"suku_bunga":angka_persen_per_tahun_atau_null}],
  "sukuk": [{"kode_sukuk":"string","nama_sukuk":"string","jenis_sukuk":"Negara/Korporasi/null","bobot":angka_persen,"yield":angka_atau_null,"jatuh_tempo":"string atau null","persen_nab":angka_atau_null,"rating":"string atau null","nilai_nominal":angka_atau_null,"harga_perolehan_rata_rata":angka_persen_atau_null,"nilai_wajar":angka_atau_null,"tingkat_bagi_hasil":angka_persen_atau_null}],
  "bank": [{"nama_bank":"string","jenis_bank":"string atau null","bobot":angka_atau_null,"nilai_pasar":angka_atau_null,"tingkat_bunga":angka_atau_null,"jangka_waktu":angka_hari_atau_null,"persen_nab":angka_atau_null,"saldo":angka_rp_atau_null}],
  "pasar_uang": [{"nama_instrumen":"string","jenis_instrumen":"Deposito berjangka/dll","nilai_tercatat":angka_atau_null,"suku_bunga":angka_persen_atau_null,"jatuh_tempo":"YYYY-MM-DD atau null","persen_nab":angka_atau_null}],
  "piutang_bunga_detail": [{"jenis_instrumen":"Efek utang/Sukuk/Kas di bank/Instrumen pasar uang","jumlah":angka_rp}],
  "kinerja": [{"periode":"YYYY-MM","return_pct":angka}]
}

ATURAN:
- bobot/persen_nab dalam persen (12.5 bukan 0.125).
- KLASIFIKASI SECTION DI PDF:
  * "Efek Ekuitas" / "Equity Instruments" / "Saham" / "Shares" → masukkan ke array "efek"
  * "Efek Utang" / "Debt Instruments" / "Obligasi" / "Bonds" → masukkan ke array "obligasi"
  * "Sukuk" / "SBSN" → masukkan ke array "sukuk"
  * "Instrumen Pasar Uang" / "Money Market" / "Deposito" → masukkan ke array "pasar_uang"
  * "Kas di Bank" / "Cash in Banks" → masukkan ke array "bank"
  * "Piutang Bunga" / "Interest Receivable" → masukkan ke array "piutang_bunga_detail"
- EFEK (array "efek") = HANYA saham/ekuitas. nama_efek = nama PT lengkap (misal "PT Bank Central Asia Tbk"). kode_efek = ticker BEI jika diketahui (misal "BBCA"), kosong "" jika tidak ada. jumlah_lembar = number of shares. harga_perolehan_rata_rata = average cost per share (Rp). nilai_pasar = total fair market value (Rp). persen_nab = percentage to total investment portfolios.
- OBLIGASI (array "obligasi") = efek utang/bonds/surat utang. kode_obligasi = kode seri (FR0072, FR0090, dll). nama_obligasi = nama lengkap. nilai_nominal = nominal value (Rp). harga_perolehan_rata_rata = average cost dalam % (misal 99.62, 111.20). nilai_pasar = fair value (Rp). suku_bunga = interest rate per annum %. tanggal_jatuh_tempo = maturity date. persen_nab = percentage to total.
- SUKUK: kode_sukuk = PBS017, dll. tingkat_bagi_hasil = profit sharing ratio %.
- "Kas di Bank": masukkan ke "bank" dengan jenis_bank="Kas" dan field saldo.
- "Piutang Bunga": masukkan ke piutang_bunga_detail.
- "Instrumen Pasar Uang": masukkan ke pasar_uang.
- PENTING: Ambil hanya data tahun TERBARU jika ada 2 tahun (2025 dan 2024). Prioritaskan tahun paling baru.
- PENTING: Ekstrak SEMUA item, bukan hanya top 10. Tapi masukkan semua total shaham. Jika ada data obligasi hanya 10 obligasi, masukkan SEMUA. Jangan ambil beberapa atau yang top saja.
- JANGAN campur data obligasi ke dalam array efek, atau sebaliknya.
- Jika tidak ada, null atau [].

TEKS:
{$text}
PROMPT,
            ],
        ];
        return self::parseJsonOutput($this->callAi($messages, 180, 0.1));
    }

    /**
     * Slot 3: Pengukuran Nilai Wajar - fair value + unit penyertaan
     */
    public function parsePengukuranNilaiWajar(string $text): array
    {
        $text = mb_substr($text, 0, 15000);
        $messages = [
            ['role' => 'system', 'content' => 'Ekstrak data pengukuran nilai wajar dan unit penyertaan reksa dana dari laporan keuangan. Kembalikan HANYA JSON valid.'],
            [
                'role' => 'user',
                'content' => <<<PROMPT
Ekstrak dari teks berikut. Kembalikan HANYA JSON:

{
  "fair_value_level_1": angka rupiah penuh atau null,
  "fair_value_level_2": angka rupiah penuh atau null,
  "fair_value_level_3": angka rupiah penuh atau null,
  "unit_penyertaan": angka jumlah unit penyertaan beredar atau null,
  "unit_milik_investor": angka unit milik pemegang unit/investor atau null,
  "unit_milik_mi": angka unit milik manajer investasi atau null,
  "total_unit_beredar": angka total unit penyertaan beredar atau null
}

ATURAN:
- Angka rupiah dalam bentuk penuh (misal 500 miliar = 500000000000).
- Unit penyertaan biasanya angka besar (jutaan-miliaran unit).
- "Level 1" = instrumen dengan harga pasar aktif. "Level 2" = teknik valuasi. "Level 3" = input tidak dapat diobservasi.
- Keyword yang mungkin muncul: "Pengukuran Nilai Wajar", "Fair Value Hierarchy", "Unit Penyertaan Beredar", "Pemegang Unit Penyertaan", "Manajer Investasi".
- Jika tidak ditemukan, null.

TEKS:
{$text}
PROMPT,
            ],
        ];
        return self::parseJsonOutput($this->callAi($messages, 30, 0.1));
    }

    /**
     * Slot 4: BS, IS, CF, dan PUP - laporan keuangan lengkap
     */
    public function parseBsIsCfPup(string $text): array
    {
        $text = mb_substr($text, 0, 30000);
        $messages = [
            ['role' => 'system', 'content' => 'Ekstrak laporan keuangan reksa dana Indonesia: Neraca (Balance Sheet), Laba Rugi (Income Statement), Arus Kas (Cash Flow), dan Perubahan Unit Penyertaan (PUP). Kembalikan HANYA JSON valid.'],
            [
                'role' => 'user',
                'content' => <<<PROMPT
Ekstrak laporan keuangan dari teks berikut. Kembalikan HANYA JSON:

{
  "total_aset": angka rupiah penuh atau null,
  "total_liabilitas": angka rupiah penuh atau null,
  "kas_dan_bank": angka rupiah penuh atau null,
  "piutang_bunga": angka rupiah penuh atau null,
  "piutang_dividen": angka rupiah penuh atau null,
  "piutang_lain": angka rupiah penuh atau null,
  "utang_pajak": angka rupiah penuh atau null,
  "utang_lain": angka rupiah penuh atau null,
  "pendapatan_bunga": angka rupiah penuh atau null,
  "pendapatan_dividen": angka rupiah penuh atau null,
  "gain_realized": angka rupiah penuh atau null,
  "gain_unrealized": angka rupiah penuh atau null,
  "beban_mi": angka rupiah penuh atau null,
  "beban_kustodian": angka rupiah penuh atau null,
  "beban_lain": angka rupiah penuh atau null,
  "laba_bersih": angka rupiah penuh atau null,
  "arus_kas_operasi": angka rupiah penuh atau null,
  "arus_kas_pendanaan": angka rupiah penuh atau null,
  "kas_awal_tahun": angka rupiah penuh atau null,
  "kas_akhir_tahun": angka rupiah penuh atau null,
  "portofolio_efek": angka rupiah penuh atau null,
  "instrumen_pasar_uang": angka rupiah penuh atau null,
  "piutang_transaksi_efek": angka rupiah penuh atau null,
  "piutang_bunga_dan_dividen": angka rupiah penuh atau null,
  "uang_muka_diterima": angka rupiah penuh atau null,
  "liabilitas_pembelian_kembali": angka rupiah penuh atau null,
  "beban_akrual": angka rupiah penuh atau null,
  "liabilitas_atas_biaya": angka rupiah penuh atau null,
  "pembelian_kembali_unit_penyertaan": angka rupiah penuh atau null,
  "utang_pajak_lainnya": angka rupiah penuh atau null,
  "pendapatan_investasi": angka rupiah penuh atau null,
  "pendapatan_lainnya": angka rupiah penuh atau null,
  "beban_investasi": angka rupiah penuh atau null,
  "beban_pengelolaan_investasi": angka rupiah penuh atau null,
  "pembelian_efek_ekuitas": angka rupiah penuh atau null,
  "penjualan_efek_ekuitas": angka rupiah penuh atau null,
  "penerimaan_bunga_deposito": angka rupiah penuh atau null,
  "penerimaan_bunga_jasa_giro": angka rupiah penuh atau null,
  "penerimaan_dividen_kas": angka rupiah penuh atau null,
  "pembayaran_jasa_pengelolaan": angka rupiah penuh atau null,
  "pembayaran_jasa_kustodian": angka rupiah penuh atau null,
  "pembayaran_beban_lain_arus": angka rupiah penuh atau null,
  "kas_bersih_aktivitas_operasi": angka rupiah penuh atau null,
  "penerimaan_penjualan_unit": angka rupiah penuh atau null,
  "pembayaran_pembelian_kembali_unit": angka rupiah penuh atau null,
  "kas_bersih_aktivitas_pendanaan": angka rupiah penuh atau null,
  "kenaikan_kas_setara_kas": angka rupiah penuh atau null,
  "total_hasil_investasi": angka persen atau null,
  "hasil_investasi_setelah_biaya": angka persen atau null,
  "biaya_operasi": angka persen atau null,
  "portfolio_turnover_ratio": angka rasio atau null,
  "persentase_pph": angka persen atau null,
  "unit_penyertaan": angka jumlah unit penyertaan beredar atau null,
  "unit_milik_investor": angka unit milik pemegang unit atau null,
  "unit_milik_mi": angka unit milik manajer investasi atau null,
  "total_unit_beredar": angka total unit beredar atau null,
  "jumlah_pendapatan_bersih": Angka rupiah atau null,
  "laba_sebelum_pajak": Angka rupiah atau null,
  "nilai_aset_bersih": Angka rupiah atau null,
  "beban_pajak": Angka rupiah atau null,
}

ATURAN:
- Angka rupiah dalam bentuk penuh (500 juta = 500000000, 1.5 triliun = 1500000000000).
- Persen sebagai angka (5.2 bukan 0.052).
- portfolio_turnover_ratio biasanya angka desimal kecil (misal 0.45 atau 1.23).
- Keyword BS: "Jumlah Aset", "Jumlah Liabilitas", "Piutang bunga", "Piutang dividen", "Utang lain-lain".
- Keyword IS: "Keuntungan investasi yang telah direalisasi/belum direalisasi", "Beban pengelolaan investasi", "Beban kustodian", "Kenaikan/penurunan aset bersih".
- Keyword CF: "Arus kas dari aktivitas operasi/pendanaan", "Kas dan setara kas awal/akhir".
- Keyword PUP: "Unit penyertaan beredar", "Pemegang unit penyertaan", "Manajer investasi".
- Keyword Rasio: "Total Hasil Investasi", "Hasil Investasi Setelah Memperhitungkan Biaya Pemasaran", "Biaya Operasi", "Portfolio Turnover Ratio", "Persentase Penghasilan Kena Pajak".
- Jika tidak ditemukan, null.

PERHITUNGAN WAJIB

Jika field berikut tidak ditemukan secara eksplisit pada dokumen, WAJIB dihitung dari data laporan keuangan yang tersedia.

1. total_hasil_investasi

Cari:
- Jumlah Pendapatan Bersih
- Total Pendapatan Bersih
- Pendapatan Investasi Bersih
- Net Investment Income
- Jumlah Penghasilan Bersih

Cari:
- Nilai Aset Bersih
- Aset Bersih
- Net Assets

Rumus:

total_hasil_investasi =
(jumlah_pendapatan_bersih / nilai_aset_bersih) * 100

2. hasil_investasi_setelah_biaya

Cari:
- Laba Sebelum Pajak
- Profit Before Tax
- Income Before Tax

Cari:
- Nilai Aset Bersih
- Net Assets

Rumus:

hasil_investasi_setelah_biaya =
(laba_sebelum_pajak / nilai_aset_bersih) * 100

3. persentase_pph

Cari:
- Beban Pajak
- Tax Expense

Cari:
- Jumlah Pendapatan Bersih
- Net Investment Income

Rumus:

persentase_pph =
(beban_pajak / jumlah_pendapatan_bersih) * 100

ATURAN PERHITUNGAN

- Gunakan data tahun terbaru.
- Jika seluruh angka yang diperlukan tersedia, WAJIB lakukan perhitungan.
- Jangan mengembalikan null jika rumus dapat dihitung.
- Bulatkan ke 2 angka desimal.
- Hasil berupa number JSON, bukan string.

CONTOH:

Pendapatan Bersih = 4.500.000.000
Nilai Aset Bersih = 70.000.000.000

Maka:

total_hasil_investasi = 6.43

Laba Sebelum Pajak = 2.500.000.000
Nilai Aset Bersih = 70.000.000.000

Maka:

hasil_investasi_setelah_biaya = 3.57

TEKS:
{$text}
PROMPT,
            ],
        ];
        return self::parseJsonOutput($this->callAi($messages, 90, 0.1));
    }

    public function parseProspektusPdf(string $pdfText): array
    {
        $text = mb_substr($pdfText, 0, 10000);
        $messages = [
            [
                'role'    => 'system',
                'content' => 'Kamu adalah parser dokumen Prospektus Reksa Dana Indonesia. Ekstrak data Manajer Investasi dari teks prospektus dan kembalikan HANYA JSON valid tanpa teks lain.',
            ],
            [
                'role'    => 'user',
                'content' => <<<PROMPT
Ekstrak data Manajer Investasi dari teks Prospektus Reksa Dana berikut. Kembalikan HANYA JSON valid dengan struktur PERSIS ini:

{
  "address": "alamat lengkap manajer investasi atau null",
  "phone": "nomor telepon atau null",
  "email": "alamat email atau null",
  "website": "URL website atau null",
  "commissioner_president": "nama Komisaris Utama atau null",
  "commissioners": "daftar komisaris lain, pisahkan dengan newline (\\n). Format: Nama - Jabatan. Atau null jika tidak ada.",
  "director_president": "nama Direktur Utama atau null",
  "directors": "daftar direktur lain, pisahkan dengan newline (\\n). Format: Nama - Jabatan. Atau null jika tidak ada.",
  "shareholders": "daftar pemegang saham/newline. Format: Nama - Persentase. Atau null jika tidak ada.",
  "investment_committee": "daftar komite investasi/newline. Format: Nama - Jabatan. Atau null jika tidak ada.",
  "investment_management_team": "daftar tim pengelola investasi/newline. Format: Nama - Jabatan. Atau null jika tidak ada.",
  "dewan_pengawas_syariah": "daftar Dewan Pengawas Syariah/newline. Format: Nama - Jabatan. Atau null jika tidak ada.",
  "dewan_pengawas_syariah": "daftar Dewan Pengawas Syariah/newline. Format: Nama - Jabatan. Atau null jika tidak ada.",
  "description": "deskripsi singkat manajer investasi (1-2 kalimat pertama) atau null"
}

ATURAN:
- Hanya ekstrak data yang terkait Manajer Investasi (penerbit reksa dana), BUKAN data produk reksa dana-nya.
- address: ambil alamat lengkap MI (biasanya setelah "Alamat" atau "Berkedudukan").
- Untuk commissioners, directors, shareholders, investment_committee, investment_management_team, dewan_pengawas_syariah: tulis setiap orang dalam format "Nama - Jabatan" per baris.
- Jika suatu data tidak tersedia di teks, gunakan null (jangan string kosong).
- Output HANYA JSON valid, tanpa markdown, tanpa penjelasan.

TEKS PROSPEKTUS:
{$text}
PROMPT,
            ],
        ];

        $raw = $this->callAi($messages, 120, 0.1);
        return self::parseJsonOutput($raw);
    }

    public function parseProspektusSection(string $section, string $text): array
    {
        $text = mb_substr($text, 0, 60000);

        $method = match ($section) {
            'cover' => 'prospektus_cover',
            'mi_profile' => 'prospektus_mi_profile',
            'fund_info' => 'prospektus_fund_info',
            'financial_statements' => 'prospektus_financial_statements',
            'portfolio' => 'prospektus_portfolio',
            'performance' => 'prospektus_performance',
            'risk' => 'prospektus_risk',
            default => 'prospektus_general',
        };

        $prompts = $this->getSectionPrompts();
        $systemKey = $prompts[$method]['system'] ?? 'prospektus_default';
        $fields = $prompts[$method]['fields'] ?? '{}';
        $rules = $prompts[$method]['rules'] ?? '';

        $systemPrompt = $this->getProspektusSystemPrompt($systemKey, $method);

        $userPrompt = <<<PROMPT
Berikut adalah teks dari bagian {$section} dari dokumen Prospektus Reksa Dana.

{$rules}

Ekstrak data dari teks berikut dan kembalikan HANYA JSON valid dengan struktur PERSIS ini (gunakan null jika tidak ada data):

{$fields}

PERHATIAN:
- Perhatikan label-label tabel seperti "Total Aset", "JUMLAH ASET", "ASET LANCAR", "Total Liabilitas", "JUMLAH LIABILITAS", "Laba Bersih", "Pendapatan Bunga", dll.
- Angka dalam Rupiah penuh (misal 1,5 triliun = 1500000000000). Jangan gunakan titik sebagai pemisah ribuan dalam output JSON.
- Persentase dalam angka desimal biasa (misal 12,5 untuk 12,5%). Jangan gunakan koma sebagai pemisah desimal dalam output JSON.
- Jika tidak ada data, gunakan null untuk scalar atau [] untuk array.
- Output HANYA JSON valid tanpa teks lain, tanpa markdown.

TEKS:
{$text}
PROMPT;

        $raw = $this->callAi([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ], 120, 0.1);

        return self::parseJsonOutput($raw);
    }

    private function getProspektusSystemPrompt(string $key, string $method): string
    {
        $prompts = [
            'prospektus_default' => 'Kamu adalah parser dokumen Prospektus Reksa Dana Indonesia. Ekstrak data dari teks yang diberikan dan kembalikan HANYA JSON valid tanpa teks lain.',
            'prospektus_cover' => 'Kamu adalah parser dokumen Prospektus Reksa Dana Indonesia. Ekstrak informasi sampul/halaman judul prospektus.',
            'prospektus_mi_profile' => 'Kamu adalah parser data Manajer Investasi Indonesia. Ekstrak profil perusahaan Manajer Investasi dari teks prospektus.',
            'prospektus_fund_info' => 'Kamu adalah parser data Reksa Dana Indonesia. Ekstrak informasi produk reksa dana dari teks prospektus.',
            'prospektus_financial_statements' => 'Kamu adalah parser laporan keuangan Reksa Dana Indonesia. Ekstrak data laporan keuangan (neraca, laba rugi, arus kas) dari teks prospektus.',
            'prospektus_portfolio' => 'Kamu adalah parser portofolio Reksa Dana Indonesia. Ekstrak data portofolio, alokasi aset, sektor, efek, obligasi, sukuk, dan bank dari teks prospektus.',
            'prospektus_performance' => 'Kamu adalah parser kinerja Reksa Dana Indonesia. Ekstrak data kinerja historis dan return dari teks prospektus.',
            'prospektus_risk' => 'Kamu adalah parser profil risiko Reksa Dana Indonesia. Ekstrak informasi risiko dari teks prospektus.',
        ];

        return $prompts[$key] ?? $prompts['prospektus_default'];
    }

    private function getSectionPrompts(): array
    {
        return [
            'prospektus_cover' => [
                'system' => 'prospektus_cover',
                'fields' => <<<'FIELDS'
{
  "nama_reksa_dana": "string nama reksa dana atau null",
  "jenis_reksa_dana": "Saham/Pendapatan Tetap/Campuran/Pasar Uang/Terproteksi atau null",
  "manajer_investasi": "string nama manajer investasi atau null",
  "bank_kustodian": "string nama bank kustodian atau null",
  "tahun_prospektus": "angka tahun prospektus (YYYY) atau null"
}
FIELDS,
                'rules' => 'Ambil hanya dari halaman sampul/cover. Nama reksa dana biasanya judul besar di halaman pertama.',
            ],
            'prospektus_mi_profile' => [
                'system' => 'prospektus_mi_profile',
                'fields' => <<<'FIELDS'
{
  "manajer_investasi": "string nama lengkap manajer investasi atau null",
  "alamat_mi": "string alamat lengkap manajer investasi atau null",
  "telepon_mi": "string nomor telepon atau null",
  "email_mi": "string alamat email atau null",
  "website_mi": "string URL website atau null",
  "komisaris_utama": "string nama komisaris utama atau null",
  "direktur_utama": "string nama direktur utama atau null",
  "daftar_komisaris": "string daftar komisaris lain, pisahkan dengan newline (\\n). Format: Nama - Jabatan. Atau null.",
  "daftar_direksi": "string daftar direksi, pisahkan dengan newline (\\n). Format: Nama - Jabatan. Atau null.",
  "daftar_pemegang_saham": "string daftar pemegang saham, pisahkan dengan newline (\\n). Format: Nama - Persentase. Atau null.",
  "deskripsi_mi": "string deskripsi singkat manajer investasi (1-3 kalimat) atau null"
}
FIELDS,
                'rules' => 'Hanya ekstrak data yang terkait Manajer Investasi (perusahaan penerbit reksa dana), BUKAN data produk reksa dana-nya.',
            ],
            'prospektus_fund_info' => [
                'system' => 'prospektus_fund_info',
                'fields' => <<<'FIELDS'
{
  "nama_reksa_dana": "string nama produk reksa dana atau null",
  "jenis_reksa_dana": "Saham/Pendapatan Tetap/Campuran/Pasar Uang/Terproteksi/Global/DIRE-DINFRA/Penyertaan terbatas atau null",
  "kategori": ["Konvensional", "Syariah", "index", "ETF"],
  "manajer_investasi": "string nama MI atau null",
  "bank_kustodian": "string nama bank kustodian atau null",
  "tanggal_peluncuran": "YYYY-MM-DD tanggal peluncuran atau null",
  "mata_uang": "string mata uang misal IDR, USD atau null",
  "benchmark": "string nama benchmark/index acuan atau null",
  "tujuan_investasi": "string tujuan investasi atau null",
  "kebijakan_investasi": "string kebijakan investasi atau null",
  "total_aum": angka rupiah penuh atau null,
  "unit_penyertaan": angka jumlah unit penyertaan atau null,
  "nab_per_unit": angka NAB/UP atau null,
  "total_marcap_10_efek": angka rupiah penuh atau null,
  "tanggal_data": "YYYY-MM-DD tanggal data atau null",
  "return_ytd": angka persen return YTD atau null,
  "return_1y": angka persen return 1 tahun atau null,
  "total_return": angka persen total return atau null,
  "biaya_operasi": angka persen biaya operasi atau null,
  "portfolio_turnover_ratio": angka portfolio turnover ratio atau null,
  "management_fee": angka persen management fee atau null,
  "custodian_fee": angka persen custodian fee atau null
}
FIELDS,
                'rules' => 'Ekstrak informasi produk reksa dana. ffs_bulan dan ffs_tahun bisa diambil dari tanggal_data jika disebutkan.',
            ],
            'prospektus_financial_statements' => [
                'system' => 'prospektus_financial_statements',
                'fields' => <<<'FIELDS'
{
  "total_aset": angka rupiah penuh total aset atau null,
  "total_liabilitas": angka rupiah penuh total liabilitas atau null,
  "kas_dan_bank": angka rupiah penuh kas dan bank atau null,
  "piutang_bunga": angka rupiah penuh piutang bunga atau null,
  "piutang_dividen": angka rupiah penuh piutang dividen atau null,
  "piutang_lain": angka rupiah penuh piutang lain-lain atau null,
  "utang_pajak": angka rupiah penuh utang pajak atau null,
  "utang_lain": angka rupiah penuh utang lain-lain atau null,
  "pendapatan_bunga": angka rupiah penuh pendapatan bunga atau null,
  "pendapatan_dividen": angka rupiah penuh pendapatan dividen atau null,
  "gain_realized": angka rupiah penuh gain realized atau null,
  "gain_unrealized": angka rupiah penuh gain unrealized atau null,
  "beban_mi": angka rupiah penuh beban manajer investasi atau null,
  "beban_kustodian": angka rupiah penuh beban kustodian atau null,
  "beban_lain": angka rupiah penuh beban lain-lain atau null,
  "laba_bersih": angka rupiah penuh laba bersih atau null,
  "total_beban": angka rupiah penuh total beban atau null,
  "laba_sebelum_pajak": angka rupiah penuh laba sebelum pajak atau null,
  "beban_pajak_penghasilan": angka rupiah penuh beban pajak penghasilan atau null,
  "laba_bersih_tahun_berjalan": angka rupiah penuh laba bersih tahun berjalan atau null,
  "penghasilan_komprehensif_lain": angka rupiah penuh penghasilan komprehensif lain atau null,
  "penghasilan_komprehensif_lain_setelah_pajak": angka rupiah penuh penghasilan komprehensif lain setelah pajak atau null,
  "penghasilan_komprehensif_tahun_berjalan": angka rupiah penuh penghasilan komprehensif tahun berjalan atau null,
  "arus_kas_operasi": angka rupiah penuh arus kas operasi atau null,
  "arus_kas_pendanaan": angka rupiah penuh arus kas pendanaan atau null,
  "kas_awal_tahun": angka rupiah penuh kas awal tahun atau null,
  "kas_akhir_tahun": angka rupiah penuh kas akhir tahun atau null,
  "portofolio_efek": angka rupiah penuh portofolio efek atau null,
  "instrumen_pasar_uang": angka rupiah penuh instrumen pasar uang atau null,
  "piutang_transaksi_efek": angka rupiah penuh piutang transaksi efek atau null,
  "piutang_bunga_dan_dividen": angka rupiah penuh piutang bunga dan dividen atau null,
  "uang_muka_diterima": angka rupiah penuh uang muka diterima atau null,
  "liabilitas_pembelian_kembali": angka rupiah penuh liabilitas pembelian kembali atau null,
  "beban_akrual": angka rupiah penuh beban akrual atau null,
  "liabilitas_atas_biaya": angka rupiah penuh liabilitas atas biaya atau null,
  "pembelian_kembali_unit_penyertaan": angka rupiah penuh pembelian kembali unit penyertaan atau null,
  "utang_pajak_lainnya": angka rupiah penuh utang pajak lainnya atau null,
  "pendapatan_investasi": angka rupiah penuh pendapatan investasi atau null,
  "pendapatan_lainnya": angka rupiah penuh pendapatan lainnya atau null,
  "beban_investasi": angka rupiah penuh beban investasi atau null,
  "beban_pengelolaan_investasi": angka rupiah penuh beban pengelolaan investasi atau null,
  "pembelian_efek_ekuitas": angka rupiah penuh pembelian efek ekuitas atau null,
  "penjualan_efek_ekuitas": angka rupiah penuh penjualan efek ekuitas atau null,
  "penerimaan_bunga_deposito": angka rupiah penuh penerimaan bunga deposito atau null,
  "penerimaan_bunga_jasa_giro": angka rupiah penuh penerimaan bunga jasa giro atau null,
  "penerimaan_dividen_kas": angka rupiah penuh penerimaan dividen kas atau null,
  "pembayaran_jasa_pengelolaan": angka rupiah penuh pembayaran jasa pengelolaan atau null,
  "pembayaran_jasa_kustodian": angka rupiah penuh pembayaran jasa kustodian atau null,
  "pembayaran_beban_lain_arus": angka rupiah penuh pembayaran beban lain arus atau null,
  "kas_bersih_aktivitas_operasi": angka rupiah penuh kas bersih aktivitas operasi atau null,
  "penerimaan_penjualan_unit": angka rupiah penuh penerimaan penjualan unit atau null,
  "pembayaran_pembelian_kembali_unit": angka rupiah penuh pembayaran pembelian kembali unit atau null,
  "kas_bersih_aktivitas_pendanaan": angka rupiah penuh kas bersih aktivitas pendanaan atau null,
  "kenaikan_kas_setara_kas": angka rupiah penuh kenaikan kas dan setara kas atau null,
  "total_hasil_investasi": angka persen total hasil investasi atau null,
  "hasil_investasi_setelah_biaya": angka persen hasil investasi setelah biaya pemasaran atau null,
  "persentase_pph": angka persen penghasilan kena pajak atau null,
  "fair_value_level_1": angka rupiah penuh fair value level 1 atau null,
  "fair_value_level_2": angka rupiah penuh fair value level 2 atau null,
  "fair_value_level_3": angka rupiah penuh fair value level 3 atau null,
  "unit_milik_investor": angka jumlah unit milik investor atau null,
  "unit_milik_mi": angka jumlah unit milik manajer investasi atau null,
  "total_unit_beredar": angka jumlah total unit beredar atau null
}
FIELDS,
                'rules' => 'Ekstrak data laporan keuangan dari teks yang mengandung Neraca (Laporan Posisi Keuangan), Laba Rugi (Laporan Penghasilan Komprehensif), Arus Kas, dan Catatan atas Laporan Keuangan.

Cari label-label berikut di tabel:
- NERACA: Total Aset/JUMLAH ASET, Total Liabilitas/JUMLAH LIABILITAS, Kas dan Bank/Kas & Setara Kas, Piutang Bunga, Piutang Dividen, Piutang Lain-lain, Utang Pajak, Utang Lain-lain
- LABA RUGI: Pendapatan Bunga, Pendapatan Dividen, Gain Realized, Gain Unrealized/Laba Belum Realisasi, Beban MI/Beban Manajer Investasi, Beban Kustodian, Beban Lain-lain, Total Beban, Laba Sebelum Pajak, Beban Pajak Penghasilan, Laba Bersih, Laba Bersih Tahun Berjalan, Penghasilan Komprehensif Lain, Penghasilan Komprehensif Tahun Berjalan
- ARUS KAS: Arus Kas Operasi/Kas dari Aktivitas Operasi, Arus Kas Pendanaan/Kas dari Aktivitas Pendanaan, Kas Awal Tahun, Kas Akhir Tahun
- RASIO: Total Hasil Investasi, Biaya Operasi/Rasio Biaya, Portfolio Turnover, Persentase PPH
- FAIR VALUE: Level 1/Tingkat 1, Level 2/Tingkat 2, Level 3/Tingkat 3
- UNIT: Unit Milik Investor, Unit Milik MI, Total Unit Beredar

Format tabel umum di prospektus Indonesia:
- Kolom: Nama Akun | (Rp) atau dalam ribuan/jutaan
- Label biasanya rata kiri, angka rata kanan
- "Jumlah" atau "Total" menandakan subtotal
- Perhatikan apakah angka dalam Rupiah penuh, ribuan (000-an), atau jutaan

Angka dalam Rupiah penuh (misal 1.5 triliun = 1500000000000). Persen dalam desimal (misal 12.5 untuk 12.5%).',
            ],
            'prospektus_portfolio' => [
                'system' => 'prospektus_portfolio',
                'fields' => <<<'FIELDS'
{
  "alokasi_aset": [
    {"nama_aset": "Saham/Obligasi/Pasar Uang/Kas/Deposito/lainnya", "persentase": angka_persen}
  ],
  "sektor": [
    {"nama_sektor": "string", "bobot": angka_persen}
  ],
  "efek": [
    {
      "kode_efek": "string misal BBCA",
      "nama_efek": "string nama lengkap",
      "sektor": "string nama sektor atau kosong",
      "bobot": angka_persen,
      "kontribusi_kinerja": angka_persen_atau_null,
      "market_cap": angka_rupiah_penuh_atau_null,
      "nilai_pasar": angka_rupiah_penuh_atau_null,
      "harga_perolehan": angka_rupiah_penuh_atau_null,
      "persen_nab": angka_persen_terhadap_nab_atau_null,
      "top_10": true jika masuk 10 efek terbesar
    }
  ],
  "obligasi": [
    {
      "kode_obligasi": "string misal FR0091",
      "nama_obligasi": "string nama lengkap",
      "bobot": angka_persen,
      "nilai_pasar": angka_rupiah_penuh_atau_null,
      "durasi": angka_tahun_atau_null,
      "ytm": angka_persen_yield_to_maturity_atau_null,
      "kupon": angka_persen_kupon_atau_null,
      "tanggal_jatuh_tempo": "YYYY-MM-DD atau null",
      "penerbit": "string nama penerbit atau null",
      "persen_nab": angka_persen_terhadap_nab_atau_null,
      "rating": "AAA/AA+/AA/AA-/A+/A/A-/BBB+/BBB/BBB-/BB/B/CCC/D atau null"
    }
  ],
  "sukuk": [
    {
      "kode_sukuk": "string misal SR019, PBS037",
      "nama_sukuk": "string nama lengkap",
      "jenis_sukuk": "Negara atau Korporasi atau null",
      "bobot": angka_persen,
      "yield": angka_persen_atau_null,
      "jatuh_tempo": "string tahun misal 2028 atau null",
      "persen_nab": angka_persen_terhadap_nab_atau_null,
      "rating": "AAA/AA+/AA/AA-/A+ atau null"
    }
  ],
  "bank": [
    {
      "nama_bank": "string",
      "jenis_bank": "string jenis bank atau null",
      "bobot": angka_persen_atau_null,
      "nilai_pasar": angka_rupiah_penuh_atau_null,
      "tingkat_bunga": angka_persen_atau_null,
      "jangka_waktu": angka_jangka_waktu_hari_atau_null,
      "persen_nab": angka_persen_terhadap_nab_atau_null,
      "car": angka_persen_atau_null,
      "npl": angka_persen_atau_null,
      "klasifikasi_risiko": "Rendah/Sedang/Tinggi atau null"
    }
  ]
}
FIELDS,
                'rules' => 'Ekstrak data portofolio. bobot dalam persen (misal 12.5, bukan 0.125). Alokasi aset adalah komposisi jenis aset (saham, obligasi, dll) bukan sektor.',
            ],
            'prospektus_performance' => [
                'system' => 'prospektus_performance',
                'fields' => <<<'FIELDS'
{
  "kinerja": [
    {"periode": "YYYY-MM", "return_pct": angka}
  ],
  "return_ytd": angka_persen_return_YTD_atau_null,
  "return_1y": angka_persen_return_1_tahun_atau_null,
  "total_return": angka_persen_total_return_atau_null
}
FIELDS,
                'rules' => 'Ekstrak data kinerja historis. Periode format YYYY-MM (misal 2024-03). Return dalam persen.',
            ],
            'prospektus_risk' => [
                'system' => 'prospektus_risk',
                'fields' => <<<'FIELDS'
{
  "profil_risiko": "string deskripsi profil risiko atau null",
  "tingkat_risiko": "Rendah/Sedang/Tinggi atau null",
  "risiko_utama": "string deskripsi risiko utama atau null"
}
FIELDS,
                'rules' => 'Ekstrak informasi profil risiko reksa dana.',
            ],
        ];
    }

    public function parseFfsPdfVision(string $pdfPath, ?string $filename = null): array
    {
        $prompt = <<<PROMPT
Baca PDF Fund Fact Sheet berikut seperti analis yang melihat halaman PDF langsung. Ekstrak data dan kembalikan HANYA JSON valid dengan struktur PERSIS seperti ini:

{
  "nama_reksa_dana": "string atau null",
  "jenis_reksa_dana": "Saham" atau "Pendapatan Tetap" atau "Campuran" atau "Pasar Uang" atau null,
  "kategori": ["Konvensional", "Syariah", "index", "ETF"],
  "manajer_investasi": "string nama MI atau null",
  "bank_kustodian": "string nama bank kustodian atau null",
  "tanggal_peluncuran": "YYYY-MM-DD tanggal peluncuran reksa dana atau null",
  "mata_uang": "string mata uang misal IDR, USD atau null",
  "benchmark": "string nama benchmark/index acuan atau null",
  "tujuan_investasi": "string tujuan investasi atau null",
  "kebijakan_investasi": "string kebijakan investasi atau null",
  "total_aum": angka rupiah penuh atau null,
  "unit_penyertaan": angka jumlah unit penyertaan atau null,
  "nab_per_unit": angka NAB/UP atau null,
  "total_marcap_10_efek": angka rupiah penuh atau null,
  "tanggal_data": "YYYY-MM-DD atau null",
  "ffs_bulan": angka bulan 1-12 atau null,
  "ffs_tahun": angka tahun 4 digit atau null,
  "return_ytd": angka persen return YTD atau null,
  "return_1y": angka persen return 1 tahun atau null,
  "total_return": angka persen total return atau null,
  "biaya_operasi": angka persen biaya operasi atau null,
  "portfolio_turnover_ratio": angka portfolio turnover ratio atau null,
  "management_fee": angka persen management fee atau null,
  "custodian_fee": angka persen custodian fee atau null,
  "total_aset": angka rupiah penuh total aset atau null,
  "total_liabilitas": angka rupiah penuh total liabilitas atau null,
  "kas_dan_bank": angka rupiah penuh kas dan bank atau null,
  "piutang_bunga": angka rupiah penuh piutang bunga atau null,
  "piutang_dividen": angka rupiah penuh piutang dividen atau null,
  "piutang_lain": angka rupiah penuh piutang lain-lain atau null,
  "utang_pajak": angka rupiah penuh utang pajak atau null,
  "utang_lain": angka rupiah penuh utang lain-lain atau null,
  "pendapatan_bunga": angka rupiah penuh pendapatan bunga atau null,
  "pendapatan_dividen": angka rupiah penuh pendapatan dividen atau null,
  "gain_realized": angka rupiah penuh gain realized atau null,
  "gain_unrealized": angka rupiah penuh gain unrealized atau null,
  "beban_mi": angka rupiah penuh beban manajer investasi atau null,
  "beban_kustodian": angka rupiah penuh beban kustodian atau null,
  "beban_lain": angka rupiah penuh beban lain-lain atau null,
  "laba_bersih": angka rupiah penuh laba bersih atau null,
  "total_beban": angka rupiah penuh total beban atau null,
  "laba_sebelum_pajak": angka rupiah penuh laba sebelum pajak atau null,
  "beban_pajak_penghasilan": angka rupiah penuh beban pajak penghasilan atau null,
  "laba_bersih_tahun_berjalan": angka rupiah penuh laba bersih tahun berjalan atau null,
  "penghasilan_komprehensif_lain": angka rupiah penuh penghasilan komprehensif lain atau null,
  "penghasilan_komprehensif_lain_setelah_pajak": angka rupiah penuh penghasilan komprehensif lain setelah pajak atau null,
  "penghasilan_komprehensif_tahun_berjalan": angka rupiah penuh penghasilan komprehensif tahun berjalan atau null,
  "arus_kas_operasi": angka rupiah penuh arus kas operasi atau null,
  "arus_kas_pendanaan": angka rupiah penuh arus kas pendanaan atau null,
  "kas_awal_tahun": angka rupiah penuh kas awal tahun atau null,
  "kas_akhir_tahun": angka rupiah penuh kas akhir tahun atau null,
  "portofolio_efek": angka rupiah penuh portofolio efek atau null,
  "instrumen_pasar_uang": angka rupiah penuh instrumen pasar uang atau null,
  "piutang_transaksi_efek": angka rupiah penuh piutang transaksi efek atau null,
  "piutang_bunga_dan_dividen": angka rupiah penuh piutang bunga dan dividen atau null,
  "uang_muka_diterima": angka rupiah penuh uang muka diterima atau null,
  "liabilitas_pembelian_kembali": angka rupiah penuh liabilitas pembelian kembali atau null,
  "beban_akrual": angka rupiah penuh beban akrual atau null,
  "liabilitas_atas_biaya": angka rupiah penuh liabilitas atas biaya atau null,
  "pembelian_kembali_unit_penyertaan": angka rupiah penuh pembelian kembali unit penyertaan atau null,
  "utang_pajak_lainnya": angka rupiah penuh utang pajak lainnya atau null,
  "pendapatan_investasi": angka rupiah penuh pendapatan investasi atau null,
  "pendapatan_lainnya": angka rupiah penuh pendapatan lainnya atau null,
  "beban_investasi": angka rupiah penuh beban investasi atau null,
  "beban_pengelolaan_investasi": angka rupiah penuh beban pengelolaan investasi atau null,
  "pembelian_efek_ekuitas": angka rupiah penuh pembelian efek ekuitas atau null,
  "penjualan_efek_ekuitas": angka rupiah penuh penjualan efek ekuitas atau null,
  "penerimaan_bunga_deposito": angka rupiah penuh penerimaan bunga deposito atau null,
  "penerimaan_bunga_jasa_giro": angka rupiah penuh penerimaan bunga jasa giro atau null,
  "penerimaan_dividen_kas": angka rupiah penuh penerimaan dividen kas atau null,
  "pembayaran_jasa_pengelolaan": angka rupiah penuh pembayaran jasa pengelolaan atau null,
  "pembayaran_jasa_kustodian": angka rupiah penuh pembayaran jasa kustodian atau null,
  "pembayaran_beban_lain_arus": angka rupiah penuh pembayaran beban lain arus atau null,
  "kas_bersih_aktivitas_operasi": angka rupiah penuh kas bersih aktivitas operasi atau null,
  "penerimaan_penjualan_unit": angka rupiah penuh penerimaan penjualan unit atau null,
  "pembayaran_pembelian_kembali_unit": angka rupiah penuh pembayaran pembelian kembali unit atau null,
  "kas_bersih_aktivitas_pendanaan": angka rupiah penuh kas bersih aktivitas pendanaan atau null,
  "kenaikan_kas_setara_kas": angka rupiah penuh kenaikan kas dan setara kas atau null,
  "total_hasil_investasi": angka persen total hasil investasi atau null,
  "hasil_investasi_setelah_biaya": angka persen hasil investasi setelah biaya pemasaran atau null,
  "persentase_pph": angka persen penghasilan kena pajak atau null,
  "fair_value_level_1": angka rupiah penuh fair value level 1 atau null,
  "fair_value_level_2": angka rupiah penuh fair value level 2 atau null,
  "fair_value_level_3": angka rupiah penuh fair value level 3 atau null,
  "unit_milik_investor": angka jumlah unit milik investor atau null,
  "unit_milik_mi": angka jumlah unit milik manajer investasi atau null,
  "total_unit_beredar": angka jumlah total unit beredar atau null,
  "alokasi_aset": [{"nama_aset": "string", "persentase": angka_persen}],
  "sektor": [{"nama_sektor": "string", "bobot": angka_persen}],
  "efek": [{
    "kode_efek": "string",
    "nama_efek": "string",
    "sektor": "string atau kosong",
    "bobot": angka_persen,
    "kontribusi_kinerja": angka_persen_kontribusi_ihsg_atau_null,
    "ihsg_contribution": angka_persen_kontribusi_ihsg_atau_null,
    "market_cap": angka_rupiah_penuh_atau_null,
    "nilai_pasar": angka_rupiah_penuh_nilai_pasar_efek_atau_null,
    "harga_perolehan": angka_rupiah_penuh_harga_perolehan_atau_null,
    "persen_nab": angka_persen_terhadap_nab_atau_null,
    "return_1m": angka_persen_atau_null,
    "return_3m": angka_persen_atau_null,
    "return_6m": angka_persen_atau_null,
    "return_1y": angka_persen_atau_null,
    "top_10": true
  }],
  "kinerja": [{"periode": "YYYY-MM", "return_pct": angka}],
  "obligasi": [{
    "kode_obligasi": "string",
    "nama_obligasi": "string",
    "bobot": angka_persen,
    "nilai_pasar": angka_rupiah_penuh_nilai_pasar_atau_null,
    "durasi": angka_tahun_atau_null,
    "ytm": angka_persen_yield_to_maturity_atau_null,
    "kupon": angka_persen_kupon_atau_null,
    "tanggal_jatuh_tempo": "YYYY-MM-DD tanggal jatuh tempo obligasi atau null",
    "penerbit": "string nama penerbit obligasi atau null",
    "persen_nab": angka_persen_terhadap_nab_atau_null,
    "rating": "string atau null"
  }],
  "sukuk": [{
    "kode_sukuk": "string",
    "nama_sukuk": "string",
    "jenis_sukuk": "Negara atau Korporasi atau null",
    "bobot": angka_persen,
    "yield": angka_persen_atau_null,
    "jatuh_tempo": "string tahun atau null",
    "persen_nab": angka_persen_terhadap_nab_atau_null,
    "rating": "string atau null"
  }],
  "bank": [{
    "nama_bank": "string",
    "jenis_bank": "string jenis bank atau null",
    "bobot": angka_persen_atau_null,
    "nilai_pasar": angka_rupiah_penuh_nilai_pasar_atau_null,
    "tingkat_bunga": angka_persen_tingkat_bunga_atau_null,
    "jangka_waktu": angka_jangka_waktu_hari_atau_null,
    "persen_nab": angka_persen_terhadap_nab_atau_null,
    "car": angka_persen_atau_null,
    "npl": angka_persen_atau_null,
    "klasifikasi_risiko": "Rendah" atau "Sedang" atau "Tinggi" atau null
  }]
}

ATURAN:
- Gunakan tampilan halaman PDF, tabel, header, footnote, dan teks kecil jika terbaca.
- nama_reksa_dana harus nama produk yang spesifik, bukan judul "Fund Fact Sheet".
- Isi kode efek, sektor, kontribusi IHSG/kinerja, market cap, kode/nama obligasi, dan data bank jika terlihat.
- Isi unit penyertaan, NAB/UP, periode/tanggal data, dan alokasi aset jika terlihat.
- Jika data tidak ada gunakan null atau array kosong [].
- Output HANYA JSON valid, tanpa markdown.
PROMPT;

        $raw = $this->callOpenAiPdf($pdfPath, $filename, $prompt, 300, 0.1);

        return self::parseJsonOutput($raw);
    }

    public function generateNarasiAnalisa(AnalisaReksaDana $analisa): string
    {
        $prompt = $this->buildPrompt($analisa);

        return $this->callAi([
            [
                'role'    => 'system',
                'content' => 'Kamu adalah analis investasi profesional Indonesia yang ahli dalam analisa Reksa Dana. Berikan analisa yang jelas, informatif, dan mudah dipahami investor. Gunakan Bahasa Indonesia yang baik. Format output menggunakan teks biasa tanpa markdown.',
            ],
            [
                'role'    => 'user',
                'content' => $prompt,
            ],
        ], 60, 0.7);
    }

    public function generateNarasiAnalisaStructured(AnalisaReksaDana $analisa, string $productType = 'reksa_dana'): array
    {
        $prompt = $this->buildStructuredPrompt($analisa, $productType);
        $systemKey = $productType === 'unit_link' ? 'system_analisa_unit_link' : 'system_analisa';
        $systemPrompt = \App\Models\AiPrompt::get($systemKey, 'Kamu adalah analis investasi profesional Indonesia yang ahli dalam analisa Reksa Dana. Gunakan Bahasa Indonesia yang baik. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.');

        $raw = $this->callAi([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $prompt],
        ], 90, 0.3);

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

        $raw = $this->callAi([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $prompt],
        ], 120, 0.3);

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
        if (!empty($data['data_tahunan']) && is_array($data['data_tahunan'])) {
            return $this->generateNarasiLapkeuTahunanStructured($data, $instrumen, false);
        }

        $fmt = fn($v) => $v !== null ? number_format((float)$v, 2, ',', '.') : 'N/A';

        $lines = [];
        $lines[] = "INSTRUMEN: {$instrumen}";
        if (!empty($data['nama'])) $lines[] = "Nama: {$data['nama']}";
        if (!empty($data['kode'])) $lines[] = "Kode: {$data['kode']}";
        if (!empty($data['periode'])) $lines[] = "Periode: {$data['periode']}";
        if (!empty($data['mata_uang'])) $lines[] = "Mata Uang: {$data['mata_uang']}";

        if ($instrumen === 'Obligasi') {
            if (!empty($data['rating'])) $lines[] = "Rating: {$data['rating']}";
            if (!empty($data['official_rating'])) $lines[] = "Official Rating: {$data['official_rating']}";
            if (!empty($data['shadow_rating'])) $lines[] = "Shadow Rating: {$data['shadow_rating']} (Score: {$data['shadow_score']})";
            if ($data['kupon'] ?? null) $lines[] = "Kupon: {$data['kupon']}%";
            if ($data['ytm'] ?? null) $lines[] = "YTM: {$data['ytm']}%";
            if ($data['ytm_normal'] ?? null) $lines[] = "YTM Normal: {$data['ytm_normal']}%";
            if ($data['ytm_spread'] ?? null) $lines[] = "YTM Spread: {$data['ytm_spread']}%";
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

        $raw = $this->callAi([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $prompt],
        ], 90, 0.3);

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
            $parts[] = "**Analisa Risiko**\n" . $data['analisa_risiko'];
        }

        if (!empty($data['rekomendasi_investor'])) {
            $parts[] = "**Rekomendasi**\n" . $data['rekomendasi_investor'];
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
  "analisa_risiko": "Analisa risiko obligasi, sukuk, bank, durasi, rating, konsentrasi sektor",
  "analisa_sukuk": "Analisa mendalam sukuk: komposisi Negara vs Korporasi, profil yield, distribusi rating, jatuh tempo, kontribusi terhadap portofolio, dan risiko syariah",
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
- Gunakan semua data sektor, efek, kinerja, obligasi, sukuk, dan bank yang tersedia
- Jika metrik tidak bisa dihitung, jelaskan di narasi dan set null di metrik_saran
- Output HANYA JSON valid tanpa markdown
DEFAULT);

        return $data . "\n\n" . $instruksi;
    }

    private function buildPrompt(AnalisaReksaDana $analisa): string
    {
        return $this->buildDataSection($analisa) . "\n\nBerikan analisa yang mencakup:\n1. Ringkasan kinerja keseluruhan\n2. Analisa risiko (liquidity, durasi, rating, bank)\n3. Kekuatan dan kelemahan portofolio\n4. Rekomendasi singkat untuk investor";
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
  "analisa_risiko": "Analisa risiko likuiditas, durasi, rating obligasi, sukuk, dan bank dalam 1-2 paragraf",
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
        $lines[] = "Benchmark: " . ($analisa->benchmark ?: 'N/A');
        $lines[] = "Manajer Investasi: " . ($analisa->manajer_investasi ?: 'N/A');
        $lines[] = "Bank Kustodian: " . ($analisa->bank_kustodian ?: 'N/A');
        $lines[] = "Tanggal Peluncuran: " . ($analisa->tanggal_peluncuran?->format('d/m/Y') ?: 'N/A');
        $lines[] = "Mata Uang: " . ($analisa->mata_uang ?: 'N/A');
        $lines[] = "Total AUM: " . ($analisa->total_aum ? 'Rp ' . number_format($analisa->total_aum, 0, ',', '.') : 'N/A');

        if ($analisa->total_aset !== null) {
            $lines[] = "";
            $lines[] = "LAPORAN KEUANGAN";
            $lines[] = "--- Neraca ---";
            $lines[] = "Total Aset: Rp " . number_format($analisa->total_aset, 0, ',', '.');
            $lines[] = "Total Liabilitas: Rp " . number_format($analisa->total_liabilitas ?? 0, 0, ',', '.');
            $lines[] = "Kas dan Bank: Rp " . number_format($analisa->kas_dan_bank ?? 0, 0, ',', '.');
            $lines[] = "Piutang Bunga: Rp " . number_format($analisa->piutang_bunga ?? 0, 0, ',', '.');
            $lines[] = "Piutang Dividen: Rp " . number_format($analisa->piutang_dividen ?? 0, 0, ',', '.');
            $lines[] = "Piutang Lain-lain: Rp " . number_format($analisa->piutang_lain ?? 0, 0, ',', '.');
            $lines[] = "Utang Pajak: Rp " . number_format($analisa->utang_pajak ?? 0, 0, ',', '.');
            $lines[] = "Utang Lain-lain: Rp " . number_format($analisa->utang_lain ?? 0, 0, ',', '.');
            $lines[] = "--- Laba Rugi ---";
            $lines[] = "Pendapatan Bunga: Rp " . number_format($analisa->pendapatan_bunga ?? 0, 0, ',', '.');
            $lines[] = "Pendapatan Dividen: Rp " . number_format($analisa->pendapatan_dividen ?? 0, 0, ',', '.');
            $lines[] = "Gain Realized: Rp " . number_format($analisa->gain_realized ?? 0, 0, ',', '.');
            $lines[] = "Gain Unrealized: Rp " . number_format($analisa->gain_unrealized ?? 0, 0, ',', '.');
            $lines[] = "Beban Manajer Investasi: Rp " . number_format($analisa->beban_mi ?? 0, 0, ',', '.');
            $lines[] = "Beban Kustodian: Rp " . number_format($analisa->beban_kustodian ?? 0, 0, ',', '.');
            $lines[] = "Beban Lain-lain: Rp " . number_format($analisa->beban_lain ?? 0, 0, ',', '.');
            $lines[] = "Laba Bersih: Rp " . number_format($analisa->laba_bersih ?? 0, 0, ',', '.');
            $lines[] = "--- Arus Kas ---";
            $lines[] = "Arus Kas Operasi: Rp " . number_format($analisa->arus_kas_operasi ?? 0, 0, ',', '.');
            $lines[] = "Arus Kas Pendanaan: Rp " . number_format($analisa->arus_kas_pendanaan ?? 0, 0, ',', '.');
            $lines[] = "Kas Awal Tahun: Rp " . number_format($analisa->kas_awal_tahun ?? 0, 0, ',', '.');
            $lines[] = "Kas Akhir Tahun: Rp " . number_format($analisa->kas_akhir_tahun ?? 0, 0, ',', '.');
            $lines[] = "--- Rasio ---";
            if ($analisa->total_hasil_investasi !== null) $lines[] = "Total Hasil Investasi: {$analisa->total_hasil_investasi}%";
            if ($analisa->hasil_investasi_setelah_biaya !== null) $lines[] = "Hasil Investasi Setelah Biaya Pemasaran: {$analisa->hasil_investasi_setelah_biaya}%";
            if ($analisa->biaya_operasi !== null) $lines[] = "Biaya Operasi: {$analisa->biaya_operasi}%";
            if ($analisa->portfolio_turnover_ratio !== null) $lines[] = "Portfolio Turnover Ratio: {$analisa->portfolio_turnover_ratio}";
            if ($analisa->persentase_pph !== null) $lines[] = "Persentase Penghasilan Kena Pajak: {$analisa->persentase_pph}%";
            $lines[] = "--- Fair Value ---";
            $lines[] = "Level 1: Rp " . number_format($analisa->fair_value_level_1 ?? 0, 0, ',', '.');
            $lines[] = "Level 2: Rp " . number_format($analisa->fair_value_level_2 ?? 0, 0, ',', '.');
            $lines[] = "Level 3: Rp " . number_format($analisa->fair_value_level_3 ?? 0, 0, ',', '.');
            $lines[] = "--- Unit Penyertaan ---";
            if ($analisa->unit_milik_investor !== null) $lines[] = "Unit Milik Investor: {$analisa->unit_milik_investor}";
            if ($analisa->unit_milik_mi !== null) $lines[] = "Unit Milik Manajer Investasi: {$analisa->unit_milik_mi}";
            if ($analisa->total_unit_beredar !== null) $lines[] = "Total Unit Beredar: {$analisa->total_unit_beredar}";
        }

        $lines[] = "";
        $lines[] = "METRIK KINERJA";
        $lines[] = "Sharpe Ratio: " . ($analisa->sharpe_ratio ?? 'N/A');
        $lines[] = "RAR (Risk-Adjusted Return): " . ($analisa->rar ?? 'N/A');
        $lines[] = "Liquidity Ratio (AUM/MarCap): " . ($analisa->liquidity_ratio ? number_format($analisa->liquidity_ratio * 100, 2) . '%' : 'N/A');
        $lines[] = "Durasi Rata-rata Obligasi: " . ($analisa->durasi_rata_rata ? $analisa->durasi_rata_rata . ' tahun' : 'N/A');

        if ($analisa->relationLoaded('alokasiAset') && $analisa->alokasiAset->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "ALOKASI ASET / PORTOFOLIO";
            foreach ($analisa->alokasiAset->sortByDesc('persentase') as $aset) {
                $lines[] = "- {$aset->nama_aset}: {$aset->persentase}%";
            }
        }

        if ($analisa->sektor->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "KOMPOSISI SEKTOR";
            foreach ($analisa->sektor->sortByDesc('bobot') as $s) {
                $lines[] = "- {$s->nama_sektor}: {$s->bobot}%";
            }
        }

        $top10 = $analisa->efek->where('top_10', true)->sortByDesc('bobot');
        $efekAcuan = $top10->isNotEmpty()
            ? $top10
            : $analisa->efek->sortByDesc('bobot');
        if ($efekAcuan->isNotEmpty()) {
            $lines[] = "";
            $lines[] = $top10->isNotEmpty() ? "10 EFEK TERBESAR" : "DAFTAR EFEK";
            foreach ($efekAcuan as $e) {
                $kontribusi = $e->kontribusi_kinerja !== null
                    ? ($e->kontribusi_kinerja >= 0 ? '+' : '') . $e->kontribusi_kinerja . '%'
                    : 'N/A';
                $detail = "- {$e->kode_efek} ({$e->nama_efek}): bobot {$e->bobot}%, sektor " . ($e->sektor ?: 'N/A') . ", kontribusi IHSG {$kontribusi}";
                if ($e->nilai_pasar !== null) {
                    $detail .= ', nilai pasar Rp ' . number_format((float) $e->nilai_pasar, 0, ',', '.');
                }
                $returns = [];
                foreach (['return_1m' => '1M', 'return_3m' => '3M', 'return_6m' => '6M', 'return_1y' => '1Y'] as $key => $label) {
                    if ($e->{$key} !== null) {
                        $returns[] = "{$label}: {$e->{$key}}%";
                    }
                }
                if ($returns !== []) {
                    $detail .= ', return ' . implode(', ', $returns);
                }
                $lines[] = $detail;
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

        if ($analisa->sukuk->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "SUKUK DALAM PORTOFOLIO";
            foreach ($analisa->sukuk as $s) {
                $lines[] = "- {$s->nama_sukuk} (Jenis: {$s->jenis_sukuk}, Rating: {$s->rating}, Yield: {$s->yield}%, Jatuh Tempo: {$s->jatuh_tempo}, Bobot: {$s->bobot}%)";
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
        if (!empty($data['data_tahunan']) && is_array($data['data_tahunan'])) {
            return $this->generateNarasiLapkeuTahunanStructured($data, $instrumen, true);
        }

        $fmt = fn($v) => $v !== null ? number_format((float)$v, 2, ',', '.') : 'N/A';

        $lines = [];
        $lines[] = "INSTRUMEN: {$instrumen}";
        if (!empty($data['nama'])) $lines[] = "Nama: {$data['nama']}";
        if (!empty($data['kode'])) $lines[] = "Kode: {$data['kode']}";
        if (!empty($data['periode'])) $lines[] = "Periode: {$data['periode']}";
        if (!empty($data['mata_uang'])) $lines[] = "Mata Uang: {$data['mata_uang']}";

        if ($instrumen === 'Obligasi') {
            if (!empty($data['rating'])) $lines[] = "Rating: {$data['rating']}";
            if (!empty($data['official_rating'])) $lines[] = "Official Rating: {$data['official_rating']}";
            if (!empty($data['shadow_rating'])) $lines[] = "Shadow Rating: {$data['shadow_rating']} (Score: {$data['shadow_score']}, Confidence: {$data['shadow_confidence']}%)";
            if (!empty($data['rating_source'])) $lines[] = "Rating Source: {$data['rating_source']}";
            if ($data['kupon'] ?? null) $lines[] = "Kupon: {$data['kupon']}%";
            if ($data['ytm'] ?? null) $lines[] = "YTM: {$data['ytm']}%";
            if ($data['ytm_normal'] ?? null) $lines[] = "YTM Normal: {$data['ytm_normal']}%";
            if ($data['ytm_spread'] ?? null) $lines[] = "YTM Spread: {$data['ytm_spread']}%";
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

        $raw = $this->callAi([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $prompt],
        ], 120, 0.3);

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

    private function generateNarasiLapkeuTahunanStructured(array $data, string $instrumen, bool $plus): array
    {
        $fmt = fn($v) => $v !== null && $v !== '' ? number_format((float) $v, 2, ',', '.') : 'N/A';

        $lines = [];
        $lines[] = "INSTRUMEN: {$instrumen}";
        if (!empty($data['nama'])) $lines[] = "Nama: {$data['nama']}";
        if (!empty($data['kode'])) $lines[] = "Kode Emiten: {$data['kode']}";
        if (!empty($data['tahun'])) $lines[] = "Tahun Analisa: {$data['tahun']}";
        if (!empty($data['mata_uang'])) $lines[] = "Mata Uang: {$data['mata_uang']}";
        if ($instrumen === 'Obligasi') {
            if (!empty($data['rating'])) $lines[] = "Rating: {$data['rating']}";
            if ($data['kupon'] ?? null) $lines[] = "Kupon: {$data['kupon']}%";
            if ($data['ytm'] ?? null) $lines[] = "YTM: {$data['ytm']}%";
        }

        $lines[] = "";
        $lines[] = "DATA LAPORAN KEUANGAN TAHUNAN";

        foreach ($data['data_tahunan'] as $record) {
            $periode = $record['periode'] ?? '-';
            $lines[] = "Periode {$periode}:";
            $lines[] = "  Total Aset: {$fmt($record['total_asset'] ?? null)}";
            $lines[] = "  Total Liabilitas: {$fmt($record['total_liabilities'] ?? null)}";
            $lines[] = "  Total Ekuitas: {$fmt($record['equity'] ?? null)}";
            $lines[] = "  Pendapatan Bersih: {$fmt($record['net_revenue'] ?? null)}";
            $lines[] = "  Laba Operasional: {$fmt($record['laba_operasional'] ?? null)}";
            $lines[] = "  Beban Bunga: {$fmt($record['interest_expense'] ?? null)}";
            $lines[] = "  Laba Bersih: {$fmt($record['net_income'] ?? null)}";
            $lines[] = "  Arus Kas Operasi: {$fmt($record['cash_flows_operating_activities'] ?? null)}";
        }

        $dataSection = implode("\n", $lines);
        $depth = $plus ? 'mendalam' : 'ringkas namun komprehensif';

        $prompt = <<<PROMPT
{$dataSection}

Berdasarkan seluruh data laporan keuangan {$instrumen} dalam satu tahun di atas, buat analisa tahunan {$depth} dalam format JSON dengan struktur EXACT berikut:
{
  "ringkasan_utama": "Ringkasan kondisi keuangan tahunan",
  "tren_pertumbuhan_aset": "Tren pertumbuhan aset antar periode",
  "tren_pertumbuhan_pendapatan": "Tren pendapatan antar periode",
  "tren_laba_bersih": "Tren laba bersih antar periode",
  "perubahan_struktur_modal": "Perubahan komposisi liabilitas dan ekuitas",
  "perubahan_leverage": "Perubahan leverage dan debt-to-equity jika data tersedia",
  "perubahan_liabilitas": "Perubahan liabilitas dan indikasi risiko utang",
  "konsistensi_kinerja": "Konsistensi kinerja perusahaan selama tahun berjalan",
  "risiko_penurunan_performa": "Risiko penurunan performa yang terlihat dari data",
  "kesehatan_keuangan_emiten": "Penilaian kesehatan keuangan emiten",
  "kesimpulan_outlook": "Kesimpulan dan outlook"
}

PETUNJUK:
- Bandingkan data berdasarkan urutan periode.
- Hitung perubahan persentase jika angka awal dan akhir tersedia.
- Jelaskan risiko kredit dan kesehatan keuangan emiten dengan Bahasa Indonesia profesional.
- Output HANYA JSON valid tanpa markdown.
PROMPT;

        $promptKey = $instrumen === 'Obligasi'
            ? ($plus ? 'system_analisa_obligasi_plus' : 'system_analisa_obligasi')
            : ($plus ? 'system_analisa_saham_plus' : 'system_analisa_saham');
        $systemPrompt = \App\Models\AiPrompt::get($promptKey, "Kamu adalah analis keuangan senior Indonesia. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.");

        $raw = $this->callAi([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $prompt],
        ], $plus ? 120 : 90, 0.3);

        $parsed = self::parseJsonOutput($raw);
        $narasiParts = [];

        foreach (
            [
                'ringkasan_utama',
                'tren_pertumbuhan_aset',
                'tren_pertumbuhan_pendapatan',
                'tren_laba_bersih',
                'perubahan_struktur_modal',
                'perubahan_leverage',
                'perubahan_liabilitas',
                'konsistensi_kinerja',
                'risiko_penurunan_performa',
                'kesehatan_keuangan_emiten',
                'kesimpulan_outlook',
            ] as $key
        ) {
            if (!empty($parsed[$key])) $narasiParts[] = $parsed[$key];
        }

        return [
            'raw' => implode("\n\n", $narasiParts),
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

        $raw = $this->callAi([
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
  "other_current_asset": angka atau null,
  "fixed_asset": angka atau null,
  "other_non_current_asset": angka atau null,
  "total_liabilities": angka atau null,
  "current_liabilities": angka atau null,
  "account_payable": angka atau null,
  "accruals": angka atau null,
  "short_term_loans": angka atau null,
  "current_maturities_of_long_term_loans": angka atau null,
  "other_current_liabilities": angka atau null,
  "long_term_loans": angka atau null,
  "other_non_current_liabilities": angka atau null,
  "total_non_current_liabilities": angka atau null,
  "share_capital": angka atau null,
  "additional_paid_in_capital": angka atau null,
  "retained_earning": angka atau null,
  "others": angka atau null,
  "non_controlling_interest": angka atau null,
  "total_equity_equity_to_parent_entity": angka atau null,
  "equity": angka atau null,
  "net_revenue": angka atau null,
  "cost_of_good_sold": angka atau null,
  "gross_income": angka atau null,
  "operational_expense": angka atau null,
  "laba_operasional": angka atau null,
  "other_income_expense": angka atau null,
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
- Aset Lancar Lainnya map ke other_current_asset.
- Aset Tidak Lancar map ke fixed_asset jika labelnya aset tetap/non-current fixed assets; Aset Tidak Lancar Lainnya map ke other_non_current_asset.
- Utang usaha map ke account_payable, beban akrual map ke accruals, pinjaman jangka pendek map ke short_term_loans.
- Bagian lancar pinjaman jangka panjang map ke current_maturities_of_long_term_loans.
- Liabilitas jangka pendek/lancar lainnya map ke other_current_liabilities.
- Liabilitas jangka panjang lainnya map ke other_non_current_liabilities.
- Modal saham map ke share_capital, tambahan modal disetor map ke additional_paid_in_capital, saldo laba map ke retained_earning.
- Cash Flow map ke tiga field arus kas jika tersedia.
- EPS/laba per saham map ke eps dan boleh bernilai desimal.
- Semua angka laporan posisi keuangan, laba rugi, dan arus kas dalam satuan penuh (bukan juta/miliar), null jika tidak ada.
- Jika dokumen menyatakan "dalam jutaan Rupiah", kalikan angka dengan 1.000.000; jika "dalam ribuan Rupiah", kalikan dengan 1.000.
- Output HANYA JSON.

TEKS PDF:
{$text}
PROMPT,
            ],
        ], 90, 0.1);

        return self::normalizeLapkeuPdfData(self::parseJsonOutput($raw), $isObligasi);
    }

    public function parseLapkeuPdfVision(string $pdfPath, string $instrumen = 'Saham', ?string $filename = null): array
    {
        $isObligasi = $instrumen === 'Obligasi';
        $nameKey    = $isObligasi ? 'nama_obligasi' : 'nama_perusahaan';
        $codeKey    = $isObligasi ? 'kode_obligasi' : 'kode_saham';
        $extraFields = $isObligasi
            ? <<<EXTRA
  "{$nameKey}": "string atau null",
  "{$codeKey}": "string atau null",
  "nama_emiten": "string atau null",
  "rating": "string atau null",
  "kupon": angka atau null,
  "ytm": angka atau null,
EXTRA
            : <<<EXTRA
  "{$nameKey}": "string atau null",
  "{$codeKey}": "string atau null",
  "sektor": "string atau null",
EXTRA;

        $prompt = <<<PROMPT
Baca PDF laporan keuangan {$instrumen} ini seperti analis yang melihat halaman PDF langsung. Fokus pada periode laporan terbaru. Kembalikan HANYA JSON valid:

{
{$extraFields}
  "periode": "string misal Q4 2024 atau null",
  "mata_uang": "IDR atau USD atau null",
  "total_asset": angka atau null,
  "current_asset": angka atau null,
  "cash_equivalents": angka atau null,
  "account_receivable": angka atau null,
  "inventories": angka atau null,
  "other_current_asset": angka atau null,
  "fixed_asset": angka atau null,
  "other_non_current_asset": angka atau null,
  "total_liabilities": angka atau null,
  "current_liabilities": angka atau null,
  "account_payable": angka atau null,
  "accruals": angka atau null,
  "short_term_loans": angka atau null,
  "current_maturities_of_long_term_loans": angka atau null,
  "other_current_liabilities": angka atau null,
  "long_term_loans": angka atau null,
  "other_non_current_liabilities": angka atau null,
  "total_non_current_liabilities": angka atau null,
  "share_capital": angka atau null,
  "additional_paid_in_capital": angka atau null,
  "retained_earning": angka atau null,
  "others": angka atau null,
  "non_controlling_interest": angka atau null,
  "total_equity_equity_to_parent_entity": angka atau null,
  "equity": angka atau null,
  "net_revenue": angka atau null,
  "cost_of_good_sold": angka atau null,
  "gross_income": angka atau null,
  "operational_expense": angka atau null,
  "laba_operasional": angka atau null,
  "other_income_expense": angka atau null,
  "ebit": angka atau null,
  "ebitda": angka atau null,
  "interest_expense": angka atau null,
  "income_before_tax": angka atau null,
  "taxes": angka atau null,
  "net_income_attributable_to_non_controlling_interest": angka atau null,
  "net_income": angka atau null,
  "eps": angka atau null,
  "cash_flows_operating_activities": angka atau null,
  "cash_flows_investment": angka atau null,
  "cash_flows_financing": angka atau null
}

ATURAN:
- Baca tabel neraca, laba rugi, arus kas, catatan satuan, dan header periode dari PDF.
- Semua angka dalam satuan penuh. Jika PDF menyebut "dalam jutaan Rupiah", kalikan 1.000.000; jika "dalam ribuan Rupiah", kalikan 1.000.
- Jika ada beberapa periode, ambil kolom periode terbaru.
- Output HANYA JSON valid, tanpa markdown.
PROMPT;

        $raw = $this->callOpenAiPdf($pdfPath, $filename, $prompt, 300, 0.1);

        return self::normalizeLapkeuPdfData(self::parseJsonOutput($raw), $isObligasi);
    }

    public function parseProspektusFinancialVision(string $pdfPath, ?string $filename = null): array
    {
        $systemPrompt = 'Kamu adalah data entry clerk yang membaca dokumen PDF reksa dana. Tugasmu hanya membaca angka dari tabel dan mengembalikannya dalam format JSON. Ini murni tugas administratif pengolahan data, bukan rekomendasi investasi.';

        $userPrompt = <<<PROMPT
Baca halaman PDF prospektus reksa dana ini. Cari tabel LAPORAN KEUANGAN (neraca, laba rugi, arus kas). Salin angka-angka berikut dalam Rupiah penuh ke JSON:

{
  "total_aset": angka atau null,
  "total_liabilitas": angka atau null,
  "kas_dan_bank": angka atau null,
  "piutang_bunga": angka atau null,
  "piutang_dividen": angka atau null,
  "piutang_lain": angka atau null,
  "utang_pajak": angka atau null,
  "utang_lain": angka atau null,
  "pendapatan_bunga": angka atau null,
  "pendapatan_dividen": angka atau null,
  "gain_realized": angka atau null,
  "gain_unrealized": angka atau null,
  "beban_mi": angka atau null,
  "beban_kustodian": angka atau null,
  "beban_lain": angka atau null,
  "laba_bersih": angka atau null,
  "arus_kas_operasi": angka atau null,
  "arus_kas_pendanaan": angka atau null,
  "kas_awal_tahun": angka atau null,
  "kas_akhir_tahun": angka atau null,
  "portofolio_efek": angka atau null,
  "instrumen_pasar_uang": angka atau null,
  "piutang_transaksi_efek": angka atau null,
  "piutang_bunga_dan_dividen": angka atau null,
  "uang_muka_diterima": angka atau null,
  "liabilitas_pembelian_kembali": angka atau null,
  "beban_akrual": angka atau null,
  "liabilitas_atas_biaya": angka atau null,
  "pembelian_kembali_unit_penyertaan": angka atau null,
  "utang_pajak_lainnya": angka atau null,
  "pendapatan_investasi": angka atau null,
  "pendapatan_lainnya": angka atau null,
  "beban_investasi": angka atau null,
  "beban_pengelolaan_investasi": angka atau null,
  "pembelian_efek_ekuitas": angka atau null,
  "penjualan_efek_ekuitas": angka atau null,
  "penerimaan_bunga_deposito": angka atau null,
  "penerimaan_bunga_jasa_giro": angka atau null,
  "penerimaan_dividen_kas": angka atau null,
  "pembayaran_jasa_pengelolaan": angka atau null,
  "pembayaran_jasa_kustodian": angka atau null,
  "pembayaran_beban_lain_arus": angka atau null,
  "kas_bersih_aktivitas_operasi": angka atau null,
  "penerimaan_penjualan_unit": angka atau null,
  "pembayaran_pembelian_kembali_unit": angka atau null,
  "kas_bersih_aktivitas_pendanaan": angka atau null,
  "kenaikan_kas_setara_kas": angka atau null,
  "total_hasil_investasi": angka persen (misal 12.5) atau null,
  "hasil_investasi_setelah_biaya": angka persen atau null,
  "persentase_pph": angka persen atau null,
  "fair_value_level_1": angka atau null,
  "fair_value_level_2": angka atau null,
  "fair_value_level_3": angka atau null,
  "unit_milik_investor": angka atau null,
  "unit_milik_mi": angka atau null,
  "total_unit_beredar": angka atau null
}

ATURAN:
- Jika ada tulisan "dalam jutaan", kalikan 1.000.000; jika "dalam ribuan", kalikan 1.000
- gain_unrealized = laba/(rugi) yang belum direalisasi
- gain_realized = laba/(rugi) yang telah direalisasi
- beban_mi = beban manajer investasi / beban investasi
- Persen dalam desimal: 12,5% = 12.5
- Ambil kolom periode terbaru (paling kanan) jika ada beberapa periode
- Output HANYA JSON, tanpa teks lain, tanpa markdown
PROMPT;

        $raw = $this->callOpenAiPdf($pdfPath, $filename, $userPrompt, 300, 0.1, 'gpt-4o', $systemPrompt);
        $data = self::parseJsonOutput($raw);

        \Log::info('[PROSPEKTUS_VISION] Hasil vision extraction', [
            'fields' => array_keys(array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== [])),
        ]);

        return $data;
    }

    private function callOpenAiPdf(string $pdfPath, ?string $filename, string $prompt, int $timeout = 180, float $temperature = 0.1, ?string $model = null, ?string $systemPrompt = null): string
    {
        if (!$this->openaiKey) {
            throw new \RuntimeException('OpenAI API key belum tersedia untuk scan PDF vision.');
        }

        $bytes = file_get_contents($pdfPath);
        if ($bytes === false || $bytes === '') {
            throw new \RuntimeException('File PDF tidak dapat dibaca untuk scan AI.');
        }

        $messages = [];
        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }
        $messages[] = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'file',
                    'file' => [
                        'filename' => $filename ?: basename($pdfPath),
                        'file_data' => 'data:application/pdf;base64,' . base64_encode($bytes),
                    ],
                ],
                [
                    'type' => 'text',
                    'text' => $prompt,
                ],
            ],
        ];

        $response = \Illuminate\Support\Facades\Http::withToken($this->openaiKey)
            ->timeout($timeout)
            ->post($this->openaiUrl, [
                'model' => $model ?: $this->openaiModel,
                'temperature' => $temperature,
                'messages' => $messages,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenAI PDF vision error: ' . $response->body());
        }

        $content = $response->json('choices.0.message.content', '');
        \Log::info('[OPENAI_PDF] Raw response preview: ' . mb_substr($content, 0, 500));
        return $content;
    }

    private static function normalizeLapkeuPdfData(array $data, bool $isObligasi): array
    {
        $aliases = [
            'net_revenue' => ['revenue', 'pendapatan', 'pendapatan_bersih', 'sales', 'net_sales'],
            'net_income' => ['net_profit', 'laba_bersih', 'profit_for_the_year', 'laba_tahun_berjalan'],
            'total_asset' => ['total_assets', 'total_aset'],
            'current_asset' => ['aset_lancar', 'total_aset_lancar', 'current_assets'],
            'other_current_asset' => ['aset_lancar_lainnya', 'other_current_assets', 'other_current_asset'],
            'fixed_asset' => ['aset_tetap', 'non_current_assets', 'aset_tidak_lancar', 'fixed_assets'],
            'other_non_current_asset' => ['aset_tidak_lancar_lainnya', 'other_non_current_assets', 'other_non_current_asset'],
            'total_liabilities' => ['total_liability', 'total_liabilitas', 'liabilities'],
            'current_liabilities' => ['liabilitas_jangka_pendek', 'liabilitas_lancar', 'current_liability', 'current_liabilities'],
            'account_payable' => ['utang_usaha', 'trade_payables', 'account_payables', 'accounts_payable'],
            'short_term_loans' => ['pinjaman_jangka_pendek', 'short_term_borrowings', 'short_term_debt'],
            'current_maturities_of_long_term_loans' => ['bagian_lancar_pinjaman_jangka_panjang', 'current_maturities'],
            'other_current_liabilities' => ['liabilitas_lancar_lainnya', 'other_current_liabilities'],
            'long_term_loans' => ['pinjaman_jangka_panjang', 'long_term_debt', 'long_term_borrowings'],
            'other_non_current_liabilities' => ['liabilitas_jangka_panjang_lainnya', 'other_non_current_liabilities'],
            'total_non_current_liabilities' => ['total_liabilitas_jangka_panjang', 'non_current_liabilities'],
            'equity' => ['total_equity', 'ekuitas', 'total_ekuitas'],
            'share_capital' => ['modal_saham', 'share_capital'],
            'additional_paid_in_capital' => ['tambahan_modal_disetor', 'additional_paid_in_capital'],
            'retained_earning' => ['saldo_laba', 'retained_earnings'],
            'non_controlling_interest' => ['kepentingan_non_pengendali', 'non_controlling_interests'],
            'total_equity_equity_to_parent_entity' => ['ekuitas_entitas_induk', 'equity_attributable_to_parent'],
            'cost_of_good_sold' => ['beban_pokok_penjualan', 'cost_of_goods_sold', 'cost_of_good_sold'],
            'operational_expense' => ['beban_operasional', 'operating_expenses'],
            'other_income_expense' => ['pendapatan_beban_lain_lain', 'other_income_expenses'],
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
            'nama_emiten',
            'rating',
            'kupon',
            'ytm',
            'sektor',
            'periode',
            'mata_uang',
            'total_asset',
            'current_asset',
            'cash_equivalents',
            'account_receivable',
            'inventories',
            'other_current_asset',
            'fixed_asset',
            'other_non_current_asset',
            'total_liabilities',
            'current_liabilities',
            'account_payable',
            'accruals',
            'short_term_loans',
            'current_maturities_of_long_term_loans',
            'other_current_liabilities',
            'long_term_loans',
            'other_non_current_liabilities',
            'total_non_current_liabilities',
            'share_capital',
            'additional_paid_in_capital',
            'retained_earning',
            'others',
            'non_controlling_interest',
            'total_equity_equity_to_parent_entity',
            'equity',
            'net_revenue',
            'cost_of_good_sold',
            'gross_income',
            'operational_expense',
            'laba_operasional',
            'other_income_expense',
            'ebit',
            'ebitda',
            'interest_expense',
            'income_before_tax',
            'taxes',
            'net_income',
            'eps',
            'cash_flows_operating_activities',
            'cash_flows_investment',
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
            $parts = explode(',', $value);
            $value = strlen(end($parts)) === 3 ? str_replace(',', '', $value) : str_replace(',', '.', $value);
        } elseif (substr_count($value, '.') > 1 && !str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
        } elseif (substr_count($value, '.') === 1 && !str_contains($value, ',')) {
            $parts = explode('.', $value);
            $value = strlen(end($parts)) === 3 ? str_replace('.', '', $value) : $value;
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
