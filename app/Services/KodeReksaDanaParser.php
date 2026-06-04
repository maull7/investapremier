<?php

namespace App\Services;

use App\Models\InvestmentManager;

class KodeReksaDanaParser
{
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

    const KATEGORI_PRODUK_MAP = [
        '0' => 'Konvensional',
        '1' => 'Syariah',
        'I' => 'Index',
        'E' => 'ETF',
    ];

    const KELAS_MAP = [
        '00' => null,
        'A0' => 'Kelas A',
        'A1' => 'Kelas A1',
        'B0' => 'Kelas B',
        'C0' => 'Kelas C',
    ];

    const MATA_UANG_MAP = [
        '0' => 'IDR',
        '1' => 'USD',
    ];

    const INDEX_MAP = [
        '0' => false,
        '1' => true,
    ];

    const ETF_MAP = [
        '0' => false,
        '1' => true,
    ];

    public function parse(string $kode): ?array
    {
        $kode = strtoupper(trim($kode));
        if ($kode === '') return null;

        $manager = $this->resolveManager($kode);
        if (!$manager) return null;

        $suffix = substr($kode, strlen($manager->kode_mi));
        if (strlen($suffix) < 2) return null;

        $jenis = self::JENIS_MAP[$suffix[0]] ?? null;
        if (!$jenis) return null;

        $kategoriProduk = self::KATEGORI_PRODUK_MAP[$suffix[1]] ?? null;
        if (!$kategoriProduk) return null;

        $rest = substr($suffix, 2);
        $result = [
            'investment_manager_id' => $manager->id,
            'nama_manajer_investasi' => $manager->name,
            'jenis' => $jenis,
            'kategori_produk' => $kategoriProduk,
            'kategori' => [],
            'kelas' => null,
            'mata_uang' => 'IDR',
            'kode_mi' => $manager->kode_mi,
        ];

        if ($this->isNewFormat($rest)) {
            $indexFlag = $rest[0] ?? '0';
            $etfFlag = $rest[1] ?? '0';
            $kelasCode = ($rest[2] ?? '0') . ($rest[3] ?? '0');
            $mataUangCode = $rest[4] ?? '0';

            $kategori = [];
            if (self::INDEX_MAP[$indexFlag] ?? false) {
                $kategori[] = 'Index';
            }
            if (self::ETF_MAP[$etfFlag] ?? false) {
                $kategori[] = 'ETF';
            }
            if (empty($kategori) && $kategoriProduk === 'Konvensional') {
                $kategori = ['Konvensional'];
            } elseif (empty($kategori) && $kategoriProduk === 'Syariah') {
                $kategori = ['Syariah'];
            }

            $result['kategori'] = $kategori;
            $result['kelas'] = self::KELAS_MAP[$kelasCode] ?? null;
            $result['mata_uang'] = self::MATA_UANG_MAP[$mataUangCode] ?? 'IDR';
        } else {
            $kategori = [];
            if ($kategoriProduk === 'Konvensional') {
                $kategori = ['Konvensional'];
            } elseif ($kategoriProduk === 'Syariah') {
                $kategori = ['Syariah'];
            } elseif ($kategoriProduk === 'Index') {
                $kategori = ['Index'];
            } elseif ($kategoriProduk === 'ETF') {
                $kategori = ['ETF'];
            }
            $result['kategori'] = $kategori;
        }

        return $result;
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

    private function isNewFormat(string $rest): bool
    {
        if (strlen($rest) < 5) return false;

        $indexFlag = $rest[0] ?? '0';
        $etfFlag = $rest[1] ?? '0';
        $kelasCode = ($rest[2] ?? '0') . ($rest[3] ?? '0');
        $mataUangCode = $rest[4] ?? '0';

        if (!isset(self::INDEX_MAP[$indexFlag])) return false;
        if (!isset(self::ETF_MAP[$etfFlag])) return false;
        if (!isset(self::KELAS_MAP[$kelasCode])) return false;
        if (!isset(self::MATA_UANG_MAP[$mataUangCode])) return false;

        // Jika semua field pakai default (0/0/00/0), ini ambigu dengan old-format
        // Anggap new-format hanya jika minimal satu field non-default
        if ($indexFlag === '0' && $etfFlag === '0' && $kelasCode === '00' && $mataUangCode === '0') {
            return false;
        }

        return true;
    }
}
