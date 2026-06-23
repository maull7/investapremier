<?php

namespace App\Services;

use App\Models\InvestmentManager;

class KodeReksaDanaParser
{
    public const DEFAULT_CLASS_NAME = '-';
    public const DEFAULT_CURRENCY_NAME = '-';

    /**
     * KSEI 17-char format:
     * Pos 1-5:  Kode MI
     * Pos 6:    Jenis RD (A-H)
     * Pos 7:    Kategori (0=Konvensional, S=Syariah)
     * Pos 8:    Index (0=Non Index, I=Index)
     * Pos 9:    ETF (0=Non ETF, E=ETF)
     * Pos 10-13: 4 huruf singkatan nama RD
     * Pos 14-16: Kelas 3 digit (A00/A10/A1K/B00/C00/000)
     * Pos 17:   Mata Uang (0=IDR, 1=USD)
     */

    const JENIS_MAP = [
        'A' => 'Pasar Uang',
        'B' => 'Pendapatan Tetap',
        'C' => 'Campuran',
        'D' => 'Saham',
        'E' => 'Terproteksi',
        'F' => 'Global',
        'G' => 'Penyertaan terbatas',
        'H' => 'DIRE-DINFRA',
    ];

    const JENIS_REVERSE = [
        'Pasar Uang' => 'A',
        'Pendapatan Tetap' => 'B',
        'Campuran' => 'C',
        'Saham' => 'D',
        'Terproteksi' => 'E',
        'Global' => 'F',
        'Penyertaan terbatas' => 'G',
        'DIRE-DINFRA' => 'H',
    ];

    const JENIS_NORMALIZE = [
        'Capital Protected Fund' => 'Terproteksi',
        'Indeks' => 'Saham',
        'Global Fund' => 'Global',
        'Portofolio Sendiri' => 'Campuran',
        'Infrastruktur' => 'Penyertaan terbatas',
        'Exchanged Traded Fund' => 'Saham',
        'Sukuk Based Fund' => 'Pendapatan Tetap',
        'D.I.R.E.' => 'DIRE-DINFRA',
        'RD - Terproteksi' => 'Terproteksi',
        'RD - Saham' => 'Saham',
        'RD - Fixed Income' => 'Pendapatan Tetap',
        'RD - Syariah - Fixed Income' => 'Pendapatan Tetap',
        'RD - Syariah - Terproteksi' => 'Terproteksi',
    ];

    const KATEGORI_MAP = [
        '0' => 'Konvensional',
        'S' => 'Syariah',
    ];

    const KATEGORI_REVERSE = [
        'Konvensional' => '0',
        'Syariah' => 'S',
    ];

    const KATEGORI_PRODUK_NORMALIZE = [
        'Protected Fund' => 'Konvensional',
        'Fixed Income Fund' => 'Konvensional',
        'Equity Fund' => 'Konvensional',
        'Money Market Fund' => 'Konvensional',
        'Mixed Asset Fund' => 'Konvensional',
        'Index Fund' => 'Konvensional',
        'Global Fund' => 'Konvensional',
        'Own Portofolio' => 'Konvensional',
        'Sukuk Based Fund' => 'Syariah',
        'RD - Terproteksi' => 'Konvensional',
        'RD - Fixed Income' => 'Konvensional',
        'RD - Saham' => 'Konvensional',
        'Syariah Fixed Income' => 'Syariah',
        'Syariah Terproteksi' => 'Syariah',
        'pasar_uang' => 'Konvensional',
        'campuran' => 'Konvensional',
        'pendapatan_tetap' => 'Konvensional',
        'saham' => 'Konvensional',
    ];

    const KELAS_MAP = [
        '000' => 'Tidak Ada',
        'A00' => 'Kelas A',
        'A10' => 'Kelas A1',
        'A1K' => 'Kelas A1K',
        'B00' => 'Kelas B',
        'C00' => 'Kelas C',
    ];

    const KELAS_REVERSE = [
        'Tidak Ada' => '000',
        'Kelas A' => 'A00',
        'Kelas A1' => 'A10',
        'Kelas A1K' => 'A1K',
        'Kelas B' => 'B00',
        'Kelas C' => 'C00',
    ];

    const MATA_UANG_MAP = [
        '0' => 'IDR',
        '1' => 'USD',
    ];

    const MATA_UANG_REVERSE = [
        'IDR' => '0',
        'USD' => '1',
    ];

    public function parse(string $kode): array
    {
        $kode = strtoupper(trim($kode));
        if (strlen($kode) < 17) {
            return $this->defaultResult($kode);
        }

        $manager = $this->resolveManager($kode);
        $jenisCode = $kode[5];
        $kategoriCode = $kode[6];         // 0 or S
        $indexFlag = $kode[7];             // 0 or I
        $etfFlag = $kode[8];              // 0 or E
        $namaAbbr = substr($kode, 9, 4);  // 4 huruf singkatan
        $kelasCode = substr($kode, 13, 3); // 3 digit kelas
        $mataUangCode = $kode[16];         // 0 or 1

        $jenis = self::JENIS_MAP[$jenisCode] ?? null;
        $kategoriProduk = self::KATEGORI_MAP[$kategoriCode] ?? null;
        $kelas = self::KELAS_MAP[$kelasCode] ?? self::DEFAULT_CLASS_NAME;
        $mataUang = self::MATA_UANG_MAP[$mataUangCode] ?? self::DEFAULT_CURRENCY_NAME;

        return [
            'investment_manager_id' => $manager?->id,
            'nama_manajer_investasi' => $manager?->name,
            'jenis' => $jenis,
            'kategori_produk' => $kategoriProduk,
            'kategori' => $this->parseKategori($kategoriProduk, $indexFlag, $etfFlag),
            'kelas' => $kelas,
            'mata_uang' => $mataUang,
            'kode_mi' => substr($kode, 0, 5),
            'nama_abbreviation' => $namaAbbr,
            'class_code' => $kelasCode,
            'class_name' => $kelas,
            'currency_code' => $mataUangCode,
            'currency_name' => $mataUang,
            'is_valid_length' => true,
        ];
    }

    public function isValidKode(?string $kode): bool
    {
        if (empty($kode)) return false;

        $kode = strtoupper(trim($kode));
        if (strlen($kode) !== 17) return false;

        $parsed = $this->parse($kode);
        return !empty($parsed['is_valid_length']) && !empty($parsed['jenis']);
    }

    public function parseClass(string $kode): string
    {
        $kode = strtoupper(trim($kode));
        if (strlen($kode) < 17) {
            return self::DEFAULT_CLASS_NAME;
        }
        $kelasCode = substr($kode, 13, 3);
        return self::KELAS_MAP[$kelasCode] ?? self::DEFAULT_CLASS_NAME;
    }

    public function parseCurrency(string $kode): string
    {
        $kode = strtoupper(trim($kode));
        if (strlen($kode) < 17) {
            return self::DEFAULT_CURRENCY_NAME;
        }
        return self::MATA_UANG_MAP[$kode[16]] ?? self::DEFAULT_CURRENCY_NAME;
    }

    public function parseClassCode(string $kode): ?string
    {
        $kode = strtoupper(trim($kode));
        return strlen($kode) >= 17 ? substr($kode, 13, 3) : null;
    }

    public function parseCurrencyCode(string $kode): ?string
    {
        $kode = strtoupper(trim($kode));
        return strlen($kode) >= 17 ? $kode[16] : null;
    }

    public function resolveClassName(?string $stored, string $kode): string
    {
        return $this->resolveStoredValue($stored, $this->parseClass($kode), self::KELAS_MAP);
    }

    public function resolveCurrencyName(?string $stored, string $kode): string
    {
        return $this->resolveStoredValue($stored, $this->parseCurrency($kode), self::MATA_UANG_MAP);
    }

    public function isParsedClassValid(string $className): bool
    {
        return in_array($className, self::KELAS_MAP, true);
    }

    public function isParsedCurrencyValid(string $currencyName): bool
    {
        return in_array($currencyName, self::MATA_UANG_MAP, true);
    }

    public function databaseAttributes(string $kode): array
    {
        $parsed = $this->parse($kode);
        $attributes = [];

        foreach (['investment_manager_id', 'nama_manajer_investasi', 'jenis', 'kategori_produk'] as $key) {
            if (!empty($parsed[$key])) {
                $attributes[$key] = $parsed[$key];
            }
        }

        if (!empty($parsed['kategori'])) {
            $attributes['kategori'] = $parsed['kategori'];
        }

        if ($this->isParsedClassValid($parsed['class_name'])) {
            $attributes['kelas'] = $parsed['class_name'];
        }

        if ($this->isParsedCurrencyValid($parsed['currency_name'])) {
            $attributes['mata_uang'] = $parsed['currency_name'];
        }

        return $attributes;
    }

    public function generate(
        string $kodeMi,
        string $jenis,
        string $kategoriProduk,
        bool $isIndex,
        bool $isEtf,
        string $namaAbbreviation,
        string $kelas,
        string $mataUang,
    ): ?string {
        $kodeMi = strtoupper(str_pad(trim($kodeMi), 5, ' ', STR_PAD_RIGHT));
        $kodeMi = substr($kodeMi, 0, 5);

        $jenisCode = self::JENIS_REVERSE[$jenis] ?? null;
        $kategoriCode = self::KATEGORI_REVERSE[$kategoriProduk] ?? null;
        $kelasCode = self::KELAS_REVERSE[$kelas] ?? null;
        $mataUangCode = self::MATA_UANG_REVERSE[$mataUang] ?? null;

        if ($jenisCode === null || $kategoriCode === null || $kelasCode === null || $mataUangCode === null) {
            return null;
        }

        $indexFlag = $isIndex ? 'I' : '0';
        $etfFlag = $isEtf ? 'E' : '0';

        // 4 huruf singkatan nama, uppercase, pad right jika kurang
        $abbr = strtoupper(substr($namaAbbreviation, 0, 4));
        $abbr = str_pad($abbr, 4, substr($abbr, -1), STR_PAD_RIGHT);

        return $kodeMi . $jenisCode . $kategoriCode . $indexFlag . $etfFlag . $abbr . $kelasCode . $mataUangCode;
    }

    public function normalizeJenis(string $jenis): string
    {
        return self::JENIS_NORMALIZE[$jenis] ?? $jenis;
    }

    public function normalizeKategoriProduk(string $kategoriProduk): string
    {
        return self::KATEGORI_PRODUK_NORMALIZE[$kategoriProduk] ?? $kategoriProduk;
    }

    /**
     * Generate abbreviation dari nama reksa dana: 4 huruf depan dari nama
     * sebelum kata "Kelas" (jika ada), gabungkan huruf besar dari kata-kata.
     * Jika hanya 3 huruf, huruf terakhir diulang.
     */
    public static function abbreviateNama(string $namaReksaDana): string
    {
        // Hapus bagian "Kelas ..." di akhir
        $nama = preg_replace('/\s+kelas\s+.*/i', '', trim($namaReksaDana));

        // Ambil huruf pertama dari setiap kata (max 4)
        $words = preg_split('/[\s\-]+/', $nama);
        $letters = '';
        foreach ($words as $word) {
            $word = preg_replace('/[^A-Za-z]/', '', $word);
            if ($word !== '') {
                $letters .= strtoupper($word[0]);
            }
            if (strlen($letters) >= 4) break;
        }

        // Jika kurang dari 4, ulang huruf terakhir
        if (strlen($letters) < 4 && strlen($letters) > 0) {
            $letters = str_pad($letters, 4, substr($letters, -1), STR_PAD_RIGHT);
        }

        return substr($letters, 0, 4);
    }

    public function generateFromRecord(\App\Models\ReksaDana $record): ?string
    {
        $manager = $record->investmentManager;
        if (!$manager || !filled($manager->kode_mi)) {
            return null;
        }

        if (!filled($record->jenis) || !filled($record->kategori_produk)) {
            return null;
        }

        $jenis = $this->normalizeJenis($record->jenis);
        $kategoriProduk = $this->normalizeKategoriProduk($record->kategori_produk);
        $kelas = filled($record->kelas) ? $record->kelas : 'Tidak Ada';
        $mataUang = filled($record->mata_uang) ? $record->mata_uang : 'IDR';
        $namaAbbr = self::abbreviateNama($record->nama_reksa_dana ?? '');

        $isIndex = false;
        $isEtf = false;
        if (is_array($record->kategori)) {
            $isIndex = in_array('Index', $record->kategori) || in_array('index', $record->kategori);
            $isEtf = in_array('ETF', $record->kategori) || in_array('etf', $record->kategori);
        }

        return $this->generate(
            kodeMi: $manager->kode_mi,
            jenis: $jenis,
            kategoriProduk: $kategoriProduk,
            isIndex: $isIndex,
            isEtf: $isEtf,
            namaAbbreviation: $namaAbbr,
            kelas: $kelas,
            mataUang: $mataUang,
        );
    }

    private function resolveManager(string $kode): ?InvestmentManager
    {
        $managers = InvestmentManager::whereNotNull('kode_mi')
            ->where('kode_mi', '!=', '')
            ->orderByRaw('LENGTH(kode_mi) DESC')
            ->get(['id', 'name', 'kode_mi']);

        $best = null;
        $bestLen = 0;

        foreach ($managers as $m) {
            $mi = strtoupper(trim($m->kode_mi));
            $len = strlen($mi);
            if ($len > $bestLen && str_starts_with($kode, $mi)) {
                $suffix = substr($kode, $len);
                if (strlen($suffix) >= 2 && isset(self::JENIS_MAP[$suffix[0]])) {
                    $best = $m;
                    $bestLen = $len;
                }
            }
        }

        return $best;
    }

    private function parseKategori(?string $kategoriProduk, string $indexFlag, string $etfFlag): array
    {
        $kategori = [];

        // Selalu sertakan Konvensional/Syariah
        if ($kategoriProduk === 'Konvensional' || $kategoriProduk === 'Syariah') {
            $kategori[] = $kategoriProduk;
        }

        // Tambah flag Index/ETF jika ada
        if ($indexFlag === 'I') {
            $kategori[] = 'Index';
        }

        if ($etfFlag === 'E') {
            $kategori[] = 'ETF';
        }

        return $kategori;
    }

    private function resolveStoredValue(?string $stored, string $parsed, array $validMap): string
    {
        $stored = trim((string) $stored);
        $validValues = array_values($validMap);

        if ($parsed === self::DEFAULT_CLASS_NAME || $parsed === self::DEFAULT_CURRENCY_NAME) {
            return $parsed;
        }

        if ($stored === '' || !in_array($stored, $validValues, true) || $stored !== $parsed) {
            return $parsed;
        }

        return $stored;
    }

    private function defaultResult(string $kode): array
    {
        return [
            'investment_manager_id' => null,
            'nama_manajer_investasi' => null,
            'jenis' => null,
            'kategori_produk' => null,
            'kategori' => [],
            'kelas' => self::DEFAULT_CLASS_NAME,
            'mata_uang' => self::DEFAULT_CURRENCY_NAME,
            'kode_mi' => strlen($kode) >= 5 ? substr($kode, 0, 5) : null,
            'nama_abbreviation' => null,
            'class_code' => null,
            'class_name' => self::DEFAULT_CLASS_NAME,
            'currency_code' => null,
            'currency_name' => self::DEFAULT_CURRENCY_NAME,
            'is_valid_length' => false,
        ];
    }
}
