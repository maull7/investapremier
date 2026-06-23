<?php

namespace App\Services;

use App\Models\ReksaDana;

class KodeGeneratorService
{
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
        'Pasar Uang'          => 'A',
        'Pendapatan Tetap'    => 'B',
        'Campuran'            => 'C',
        'Saham'               => 'D',
        'Terproteksi'         => 'E',
        'Global'              => 'F',
        'Penyertaan terbatas' => 'G',
        'DIRE-DINFRA'         => 'H',
    ];

    const KATEGORI_MAP = [
        'Konvensional' => '0',
        'Syariah'      => 'S',
    ];

    const KELAS_MAP = [
        null          => '000',
        'Tidak Ada'   => '000',
        'Kelas A'     => 'A00',
        'Kelas A1'    => 'A10',
        'Kelas A1K'   => 'A1K',
        'Kelas B'     => 'B00',
        'Kelas C'     => 'C00',
    ];

    public function generateKodeReksaDana(
        string $kodeMi,
        string $jenis,
        ?string $kategoriProduk = null,
        ?string $kelas = null,
        array $kategori = [],
        string $mataUang = 'IDR',
        string $namaReksaDana = ''
    ): string {
        $kodeMi = strtoupper(str_pad(trim($kodeMi), 5, ' ', STR_PAD_RIGHT));
        $kodeMi = substr($kodeMi, 0, 5);

        $jenisCode = self::JENIS_MAP[$jenis] ?? strtoupper(substr($jenis, 0, 1));
        $kategoriCode = self::KATEGORI_MAP[$kategoriProduk ?? 'Konvensional'] ?? '0';

        $indexFlag = (in_array('Index', $kategori) || in_array('index', $kategori)) ? 'I' : '0';
        $etfFlag = (in_array('ETF', $kategori) || in_array('etf', $kategori)) ? 'E' : '0';

        $abbr = KodeReksaDanaParser::abbreviateNama($namaReksaDana);

        $kelasCode = self::KELAS_MAP[$kelas] ?? '000';
        $mataUangCode = $mataUang === 'USD' ? '1' : '0';

        return $kodeMi . $jenisCode . $kategoriCode . $indexFlag . $etfFlag . $abbr . $kelasCode . $mataUangCode;
    }
}
