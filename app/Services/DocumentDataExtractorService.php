<?php

namespace App\Services;

use App\Models\ReksaDanaDocument;
use App\Models\DocumentPartition;
use App\Models\ReksaDana;

class DocumentDataExtractorService
{
    public function __construct(
        private ProspektusParserService $parserService,
        private GroqService $groqService,
    ) {}

    public function extractReksaDanaData(
        ReksaDana $reksaDana,
        ReksaDanaDocument $document,
        array $partitions
    ): array {
        if (empty($partitions)) {
            throw new \RuntimeException('Tidak ada partisi yang dipilih.');
        }

        $texts = [];
        foreach ($partitions as $partition) {
            $texts[] = $this->parserService->getTextForPages($document, $partition->start_page, $partition->end_page);
        }

        $text = implode("\n\n", array_filter($texts));

        if (empty(trim($text))) {
            throw new \RuntimeException('Tidak ada teks yang tersedia untuk partisi yang dipilih. Lakukan parse dokumen terlebih dahulu.');
        }

        $truncatedText = mb_substr($text, 0, 60000);

        $systemPrompt = $this->buildReksaDanaPrompt($reksaDana);
        $userMessage = "Berikut adalah teks dokumen prospektus/FFS reksa dana. Ekstrak data yang diminta dan kembalikan HANYA JSON valid tanpa teks lain.\n\nDOKUMEN:\n" . $truncatedText;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ];

        $rawResponse = $this->groqService->callAi($messages, 120, 0.2);

        $data = $this->parseJsonResponse($rawResponse);

        return $this->mapAndSaveReksaDana($reksaDana, $data);
    }

    private function buildReksaDanaPrompt(ReksaDana $reksaDana): string
    {
        $fieldsToExtract = collect();

        if (empty($reksaDana->launch_date)) $fieldsToExtract->push('tanggal_efektif (tanggal efektif reksa dana)', 'tanggal_peluncuran (tanggal peluncuran)');
        if (empty($reksaDana->custodian_bank)) $fieldsToExtract->push('bank_kustodian (nama bank kustodian)');
        if (empty($reksaDana->benchmark)) $fieldsToExtract->push('benchmark (indeks acuan)');
        if (empty($reksaDana->mata_uang) || $reksaDana->mata_uang === 'IDR') $fieldsToExtract->push('mata_uang (IDR/USD)');
        if (empty($reksaDana->tujuan_investasi)) $fieldsToExtract->push('tujuan_investasi (tujuan investasi reksa dana)');
        if (empty($reksaDana->kebijakan_investasi)) $fieldsToExtract->push('kebijakan_investasi (kebijakan investasi)');
        if (empty($reksaDana->jenis)) $fieldsToExtract->push('jenis_reksa_dana (Saham/Pendapatan Tetap/Campuran/Pasar Uang/dll)');
        if (empty($reksaDana->kategori) || $reksaDana->kategori === [] || $reksaDana->kategori === null) $fieldsToExtract->push('kategori_reksa_dana (Konvensional/Syariah, array)');
        if (empty($reksaDana->risk_category)) $fieldsToExtract->push('risk_category (Konservatif/Moderat/Agresif)');

        if (empty($reksaDana->management_fee)) $fieldsToExtract->push('management_fee (biaya manajemen dalam persen, hanya angka)');
        if (empty($reksaDana->custodian_fee)) $fieldsToExtract->push('custodian_fee (biaya kustodian dalam persen, hanya angka)');
        if (empty($reksaDana->subscription_fee)) $fieldsToExtract->push('subscription_fee (biaya pembelian dalam persen, hanya angka)');
        if (empty($reksaDana->redemption_fee)) $fieldsToExtract->push('redemption_fee (biaya penjualan dalam persen, hanya angka)');
        if (empty($reksaDana->switching_fee)) $fieldsToExtract->push('switching_fee (biaya switching dalam persen, hanya angka)');

        $fieldList = $fieldsToExtract->isNotEmpty()
            ? $fieldsToExtract->take(20)->implode(', ')
            : 'tanggal_efektif, tanggal_peluncuran, bank_kustodian, benchmark, mata_uang, tujuan_investasi, kebijakan_investasi, jenis_reksa_dana, kategori_reksa_dana, management_fee, custodian_fee, subscription_fee, redemption_fee, switching_fee, risk_category';

        return <<<PROMPT
Kamu adalah parser dokumen prospektus dan Fund Fact Sheet (FFS) reksa dana Indonesia.
TUGAS: Ekstrak field berikut dari teks dokumen yang diberikan.

FIELD YANG DIEKSTRAK:
{$fieldList}

ATURAN PENTING:
1. Hanya ekstrak data yang benar-benar ada di dokumen. Jika tidak ditemukan, beri nilai null.
2. Untuk fee (biaya), jangan hanya lihat tabel ringkasan. Cari di seluruh dokumen termasuk catatan kaki.
3. Tanggal harus dalam format YYYY-MM-DD. Jika hanya tahun, gunakan YYYY-01-01.
4. Kategori harus array string, contoh: ["Konvensional"] atau ["Syariah"].
5. JANGAN membuat data. Jika tidak ada, gunakan null.
6. Kembalikan HANYA JSON valid tanpa teks lain, tanpa markdown code block.

Contoh output:
{"tanggal_efektif":"2020-06-15","management_fee":2.0,"jenis_reksa_dana":"Saham",...}
PROMPT;
    }

    public function extractInvestmentManagerDataFromPartitions(
        ReksaDanaDocument $document,
        array $partitions
    ): array {
        if (empty($partitions)) {
            throw new \RuntimeException('Tidak ada partisi yang dipilih.');
        }

        // Gabungkan teks dari semua partisi terpilih
        $texts = [];
        foreach ($partitions as $partition) {
            $text = $this->parserService->getTextForPages($document, $partition->start_page, $partition->end_page);
            if (!empty(trim($text))) {
                $texts[] = $text;
            }
        }

        $combinedText = implode("\n\n", $texts);

        if (empty(trim($combinedText))) {
            throw new \RuntimeException('Tidak ada teks yang tersedia untuk partisi terpilih. Lakukan parse dokumen terlebih dahulu.');
        }

        $truncatedText = mb_substr($combinedText, 0, 15000);

        $systemPrompt = <<<PROMPT
Kamu adalah parser dokumen prospektus reksa dana Indonesia. Ekstrak data Manajer Investasi dari teks dokumen.

FIELD YANG DIEKSTRAK:
Informasi Umum:
- nama (nama Manajer Investasi)
- alamat (alamat lengkap)
- website (URL website)
- email (email)
- telepon (nomor telepon)

Profil:
- deskripsi (deskripsi/profil perusahaan, paragraf panjang)
- sejarah (sejarah perusahaan)
- visi_misi (visi dan misi)

Organ/Struktur (string, format "Nama - Jabatan" per baris, pisahkan dengan newline):
- komisaris_utama
- komisaris
- direktur_utama
- direktur
- pemegang_saham
- komite_investasi
- tim_pengelola_investasi
- dewan_pengawas_syariah
- pihak_terafiliasi

Tim Pengelola (array of objects):
- tim_pengelola: [{nama, jabatan, pengalaman, sertifikasi}] — sertifikasi seperti WMI, WPPE, WPEE, CFA, dll.

ATURAN:
1. Hanya ekstrak data yang benar-benar ada di dokumen. Jika tidak ditemukan, beri nilai null.
2. JANGAN membuat data.
3. Kembalikan HANYA JSON valid tanpa teks lain, tanpa markdown code block.
4. Pastikan string JSON menggunakan tanda kutip ganda ("), bukan kutip tunggal.
5. Tidak ada trailing comma setelah item terakhir.

Contoh output:
{"nama":"PT Manajer Investasi Tbk","alamat":"Jl. Sudirman Kav 52-53","website":"https://www.example.com","email":"info@example.com","telepon":"021-1234567","deskripsi":"...","sejarah":"...","visi_misi":"...","komisaris_utama":"John Doe - Komisaris Utama","komisaris":"Jane Doe - Komisaris Independen","direktur_utama":"Bob Smith - Direktur Utama","direktur":"Alice Smith - Direktur","pemegang_saham":"PT Induk - 99%","komite_investasi":"Charlie - Ketua","tim_pengelola_investasi":"David - Manajer Investasi","dewan_pengawas_syariah":"Ustadz Ahmad - Ketua DPS","pihak_terafiliasi":"PT Terafiliasi - Hubungan","tim_pengelola":[{"nama":"John Doe","jabatan":"Direktur Utama","pengalaman":"20 tahun di pasar modal","sertifikasi":"WMI, WPPE"}]}
PROMPT;

        $userMessage = "Berikut adalah teks dokumen prospektus reksa dana:\n\n" . $truncatedText;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ];

        $attempts = 0;
        $lastError = '';
        $rawResponse = '';
        while ($attempts < 2) {
            $attempts++;
            try {
                $rawResponse = $this->groqService->callAi($messages, 120, 0.2);
                return $this->parseJsonResponse($rawResponse);
            } catch (\RuntimeException $e) {
                $lastError = $e->getMessage();
                if ($attempts >= 2) throw $e;
                $messages[] = ['role' => 'assistant', 'content' => $rawResponse];
                $messages[] = ['role' => 'user', 'content' => 'Respons sebelumnya tidak valid JSON. Tolong koreksi dan kembalikan HANYA JSON valid tanpa teks lain. Pastikan semua string menggunakan kutip ganda dan tidak ada trailing comma.'];
            }
        }

        throw new \RuntimeException($lastError);
    }

    public function extractInvestmentManagerData(
        ReksaDanaDocument $document,
        DocumentPartition $partition
    ): array {
        return $this->extractInvestmentManagerDataFromPartitions($document, [$partition]);
    }

    public function getPartitionText(ReksaDanaDocument $document, DocumentPartition $partition): string
    {
        return $this->parserService->getTextForPages($document, $partition->start_page, $partition->end_page);
    }

    private function mapAndSaveReksaDana(ReksaDana $reksaDana, array $data): array
    {
        $mapping = [
            'launch_date'        => ['tanggal_efektif', 'tanggal_peluncuran', 'launch_date'],
            'custodian_bank'     => ['bank_kustodian', 'custodian_bank'],
            'benchmark'          => ['benchmark'],
            'mata_uang'          => ['mata_uang'],
            'tujuan_investasi'   => ['tujuan_investasi'],
            'kebijakan_investasi' => ['kebijakan_investasi'],
            'jenis'              => ['jenis_reksa_dana', 'jenis'],
            'kategori'           => ['kategori_reksa_dana', 'kategori'],
            'risk_category'      => ['risk_category'],
            'management_fee'     => ['management_fee'],
            'custodian_fee'      => ['custodian_fee'],
            'subscription_fee'   => ['subscription_fee'],
            'redemption_fee'     => ['redemption_fee'],
            'switching_fee'      => ['switching_fee'],
        ];

        $updates = [];
        $extracted = [];

        foreach ($mapping as $dbField => $jsonKeys) {
            if (!empty($reksaDana->{$dbField})) {
                continue;
            }

            foreach ($jsonKeys as $key) {
                $value = $data[$key] ?? null;
                if ($value === null) continue;

                if ($dbField === 'launch_date' && is_string($value)) {
                    $value = $this->normalizeDate($value);
                }

                if ($dbField === 'kategori') {
                    if (is_string($value)) {
                        $value = [$value];
                    }
                    if (!is_array($value)) continue;
                    $value = array_values(array_filter($value, fn($v) => is_string($v) && in_array($v, ['Konvensional', 'Syariah', 'index', 'ETF', 'Index'])));
                    if (empty($value)) continue;
                    $value = array_map(fn($v) => $v === 'Index' ? 'index' : $v, $value);
                }

                if (in_array($dbField, ['management_fee', 'custodian_fee', 'subscription_fee', 'redemption_fee', 'switching_fee'])) {
                    if (is_numeric($value)) {
                        $value = (float) $value;
                    } elseif (is_string($value)) {
                        $value = (float) str_replace(['%', ' '], '', $value);
                    }
                    if ($value <= 0) continue;
                }

                $updates[$dbField] = $value;
                $extracted[$key] = $value;
                break;
            }
        }

        if (!empty($updates)) {
            $reksaDana->update($updates);
        }

        return [
            'extracted'  => $extracted,
            'saved'      => array_keys($updates),
            'skipped'    => array_diff(array_keys($mapping), array_keys($updates)),
        ];
    }

    private function normalizeDate(string $value): ?string
    {
        if (preg_match('/^\d{4}$/', $value)) {
            return $value . '-01-01';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }
        if (preg_match('/^\d{2}[-\/]\d{2}[-\/]\d{4}$/', $value)) {
            return date('Y-m-d', strtotime(str_replace('/', '-', $value)));
        }
        $ts = strtotime($value);
        return $ts ? date('Y-m-d', $ts) : $value;
    }

    private function parseJsonResponse(string $response): array
    {
        $cleaned = trim($response);
        $cleaned = preg_replace('/^```(?:json)?\s*\n|\n```\s*$/', '', $cleaned);

        $decoded = json_decode($cleaned, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{[\s\S]*\}/', $cleaned, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // Try to fix common JSON issues
        $fixed = $this->fixCommonJsonIssues($cleaned);
        $decoded = json_decode($fixed, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        throw new \RuntimeException('Gagal mem-parse respons AI. Respons tidak valid JSON.');
    }

    private function fixCommonJsonIssues(string $json): string
    {
        // Single quotes to double quotes
        $json = preg_replace("/(\w+)':/", '$1":', $json);
        $json = preg_replace("/:'\s*([^']*?)'/", ':"$1"', $json);
        // Remove trailing commas before } and ]
        $json = preg_replace('/,\s*([}\]])/', '$1', $json);
        // Replace Python None/True/False with JSON null/true/false
        $json = str_replace([': None', ': True', ': False', ', None', ', True', ', False'], [': null', ': true', ': false', ', null', ', true', ', false'], $json);
        return $json;
    }
}
