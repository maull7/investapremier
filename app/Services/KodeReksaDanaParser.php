<?php

namespace App\Services;

use App\Models\InvestmentManager;

class KodeReksaDanaParser
{
    public const DEFAULT_CLASS_NAME = '-';
    public const DEFAULT_CURRENCY_NAME = '-';

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

    const KATEGORI_PRODUK_MAP = [
        '0' => 'Konvensional',
        '1' => 'Syariah',
        'I' => 'Index',
        'E' => 'ETF',
    ];

    const KATEGORI_PRODUK_REVERSE = [
        'Konvensional' => '0',
        'Syariah' => '1',
        'Index' => 'I',
        'ETF' => 'E',
    ];

    const KELAS_MAP = [
        '00' => 'Tidak Ada',
        'A0' => 'Kelas A',
        'A1' => 'Kelas A1',
        'B0' => 'Kelas B',
        'C0' => 'Kelas C',
    ];

    const KELAS_REVERSE = [
        'Tidak Ada' => '00',
        'Kelas A' => 'A0',
        'Kelas A1' => 'A1',
        'Kelas B' => 'B0',
        'Kelas C' => 'C0',
    ];

    const MATA_UANG_MAP = [
        '0' => 'IDR',
        '1' => 'USD',
    ];

    const MATA_UANG_REVERSE = [
        'IDR' => '0',
        'USD' => '1',
    ];

    const INDEX_MAP = [
        '0' => false,
        '1' => true,
    ];

    const ETF_MAP = [
        '0' => false,
        '1' => true,
    ];

    public function parse(string $kode): array
    {
        $kode = strtoupper(trim($kode));
        if (strlen($kode) < 16) {
            return $this->defaultResult($kode);
        }

        $manager = $this->resolveManager($kode);
        $jenisCode = substr($kode, 5, 1);
        $kategoriProdukCode = substr($kode, 6, 1);
        $indexFlag = substr($kode, 7, 1);
        $etfFlag = substr($kode, 8, 1);
        $kelasCode = $this->parseClassCode($kode);
        $mataUangCode = $this->parseCurrencyCode($kode);

        $jenis = self::JENIS_MAP[$jenisCode] ?? null;
        $kategoriProduk = self::KATEGORI_PRODUK_MAP[$kategoriProdukCode] ?? null;

        $result = [
            'investment_manager_id' => $manager?->id,
            'nama_manajer_investasi' => $manager?->name,
            'jenis' => $jenis,
            'kategori_produk' => $kategoriProduk,
            'kategori' => $this->parseKategori($kategoriProduk, $indexFlag, $etfFlag),
            'kelas' => $this->parseClass($kode),
            'mata_uang' => $this->parseCurrency($kode),
            'kode_mi' => substr($kode, 0, 5),
            'class_code' => $kelasCode,
            'class_name' => $this->parseClass($kode),
            'currency_code' => $mataUangCode,
            'currency_name' => $this->parseCurrency($kode),
            'is_valid_length' => true,
        ];

        return $result;
    }

    public function parseClass(string $kode): string
    {
        if (strlen(trim($kode)) < 16) {
            return self::DEFAULT_CLASS_NAME;
        }

        return self::KELAS_MAP[$this->parseClassCode($kode)] ?? self::DEFAULT_CLASS_NAME;
    }

    public function parseCurrency(string $kode): string
    {
        if (strlen(trim($kode)) < 16) {
            return self::DEFAULT_CURRENCY_NAME;
        }

        return self::MATA_UANG_MAP[$this->parseCurrencyCode($kode)] ?? self::DEFAULT_CURRENCY_NAME;
    }

    public function parseClassCode(string $kode): ?string
    {
        $kode = strtoupper(trim($kode));
        return strlen($kode) >= 16 ? substr($kode, 13, 2) : null;
    }

    public function parseCurrencyCode(string $kode): ?string
    {
        $kode = strtoupper(trim($kode));
        return strlen($kode) >= 16 ? substr($kode, 15, 1) : null;
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
        bool $isEft,
        string $kelas,
        string $mataUang,
        ?int $seqNumber = null,
    ): ?string {
        $kodeMi = strtoupper(trim($kodeMi));
        $jenisCode = self::JENIS_REVERSE[$jenis] ?? null;
        $kategoriProdukCode = self::KATEGORI_PRODUK_REVERSE[$kategoriProduk] ?? null;
        $kelasCode = self::KELAS_REVERSE[$kelas] ?? null;
        $mataUangCode = self::MATA_UANG_REVERSE[$mataUang] ?? null;

        if (!$jenisCode || !$kategoriProdukCode || !$kelasCode || !$mataUangCode) {
            return null;
        }

        $indexFlag = $isIndex ? '1' : '0';
        $etfFlag = $isEft ? '1' : '0';

        $prefix = $kodeMi . $jenisCode . $kategoriProdukCode . $indexFlag . $etfFlag;

        if ($seqNumber === null) {
            $seqNumber = $this->nextSequenceNumber($prefix);
        }

        if ($seqNumber < 0 || $seqNumber > 9999) {
            return null;
        }

        $seq = str_pad((string) $seqNumber, 4, '0', STR_PAD_LEFT);

        return $prefix . $seq . $kelasCode . $mataUangCode;
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

        $kelas = filled($record->kelas) ? $record->kelas : 'Tidak Ada';
        $mataUang = filled($record->mata_uang) ? $record->mata_uang : 'IDR';

        return $this->generate(
            kodeMi: $manager->kode_mi,
            jenis: $record->jenis,
            kategoriProduk: $record->kategori_produk,
            isIndex: (bool) $record->is_index,
            isEft: (bool) $record->is_etf,
            kelas: $kelas,
            mataUang: $mataUang,
        );
    }

    private function nextSequenceNumber(string $prefix): int
    {
        $existing = \App\Models\ReksaDana::where('kode_reksa_dana', 'like', $prefix . '%')
            ->whereRaw('LENGTH(kode_reksa_dana) = 16')
            ->pluck('kode_reksa_dana');

        $max = 0;
        foreach ($existing as $code) {
            $seq = (int) substr($code, 9, 4);
            if ($seq > $max) {
                $max = $seq;
            }
        }

        return $max + 1;
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

        if (self::INDEX_MAP[$indexFlag] ?? false) {
            $kategori[] = 'Index';
        }

        if (self::ETF_MAP[$etfFlag] ?? false) {
            $kategori[] = 'ETF';
        }

        if ($kategori) {
            return $kategori;
        }

        return match ($kategoriProduk) {
            'Konvensional' => ['Konvensional'],
            'Syariah' => ['Syariah'],
            'Index' => ['Index'],
            'ETF' => ['ETF'],
            default => [],
        };
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
            'class_code' => null,
            'class_name' => self::DEFAULT_CLASS_NAME,
            'currency_code' => null,
            'currency_name' => self::DEFAULT_CURRENCY_NAME,
            'is_valid_length' => false,
        ];
    }
}
