<?php

namespace App\Services;

use App\Models\ReksaDana;

class KodeGeneratorService
{
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
        'Syariah'      => '1',
        'Index'        => 'I',
        'ETF'          => 'E',
    ];

    const BASE36_CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function generateKodeReksaDana(string $kodeMi, string $jenis, ?string $kategoriProduk = null): string
    {
        $jenisCode = self::JENIS_MAP[$jenis] ?? strtoupper(substr($jenis, 0, 1));
        $kategoriCode = $kategoriProduk ? (self::KATEGORI_MAP[$kategoriProduk] ?? '0') : '0';
        $prefix = strtoupper($kodeMi) . $jenisCode . $kategoriCode;

        $last = ReksaDana::where('kode_reksa_dana', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(kode_reksa_dana) DESC')
            ->orderBy('kode_reksa_dana', 'desc')
            ->value('kode_reksa_dana');

        if ($last) {
            $lastSeq = substr($last, strlen($prefix));
            $nextSeq = $this->base36Increment($lastSeq);
        } else {
            $nextSeq = '001';
        }

        return $prefix . $nextSeq;
    }

    private function base36Increment(string $base36): string
    {
        $len = strlen($base36);
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 36 + strpos(self::BASE36_CHARS, $base36[$i]);
        }
        $num++;

        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $result = self::BASE36_CHARS[$num % 36] . $result;
            $num = intdiv($num, 36);
        }

        return $result;
    }
}
