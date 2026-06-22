<?php

namespace App\Services;

use App\Models\AnalisaBank;
use App\Models\AnalisaAlokasiAset;
use App\Models\AnalisaEfek;
use App\Models\AnalisaKinerjaBulanan;
use App\Models\AnalisaObligasi;
use App\Models\AnalisaReksaDana;
use App\Models\AnalisaSektor;
use App\Models\AnalisaSukuk;
use Illuminate\Http\Request;

class AnalisaPayloadBuilder
{
    public static function fromRequest(Request $request): AnalisaReksaDana
    {
        $analisa = new AnalisaReksaDana([
            'nama_reksa_dana'      => $request->input('nama_reksa_dana', 'Preview'),
            'jenis_reksa_dana'     => $request->input('jenis_reksa_dana', 'Saham'),
            'benchmark'            => $request->input('benchmark'),
            'manajer_investasi'    => $request->input('manajer_investasi'),
            'bank_kustodian'       => $request->input('bank_kustodian'),
            'tanggal_peluncuran'   => $request->input('tanggal_peluncuran'),
            'mata_uang'            => $request->input('mata_uang'),
            'total_aum'            => $request->input('total_aum'),
            'unit_penyertaan'      => $request->input('unit_penyertaan'),
            'nab_per_unit'         => $request->input('nab_per_unit'),
            'total_marcap_10_efek' => $request->input('total_marcap_10_efek'),
            'tanggal_data'         => $request->input('tanggal_data'),
            'ffs_bulan'            => $request->input('ffs_bulan'),
            'ffs_tahun'            => $request->input('ffs_tahun'),
            'jenis_laporan'        => $request->input('jenis_laporan', 'kalender_ffs'),
            'tahun_laporan'        => $request->input('tahun_laporan'),
            'total_aset'           => $request->input('total_aset'),
            'total_liabilitas'     => $request->input('total_liabilitas'),
            'kas_dan_bank'         => $request->input('kas_dan_bank'),
            'piutang_bunga'        => $request->input('piutang_bunga'),
            'piutang_dividen'      => $request->input('piutang_dividen'),
            'piutang_lain'         => $request->input('piutang_lain'),
            'utang_pajak'          => $request->input('utang_pajak'),
            'utang_lain'           => $request->input('utang_lain'),
            'pendapatan_bunga'     => $request->input('pendapatan_bunga'),
            'pendapatan_dividen'   => $request->input('pendapatan_dividen'),
            'gain_realized'        => $request->input('gain_realized'),
            'gain_unrealized'      => $request->input('gain_unrealized'),
            'beban_mi'             => $request->input('beban_mi'),
            'beban_kustodian'      => $request->input('beban_kustodian'),
            'beban_lain'           => $request->input('beban_lain'),
            'laba_bersih'          => $request->input('laba_bersih'),
            'arus_kas_operasi'     => $request->input('arus_kas_operasi'),
            'arus_kas_pendanaan'   => $request->input('arus_kas_pendanaan'),
            'kas_awal_tahun'       => $request->input('kas_awal_tahun'),
            'kas_akhir_tahun'      => $request->input('kas_akhir_tahun'),
            'total_hasil_investasi' => $request->input('total_hasil_investasi'),
            'hasil_investasi_setelah_biaya' => $request->input('hasil_investasi_setelah_biaya'),
            'biaya_operasi'        => $request->input('biaya_operasi'),
            'portfolio_turnover_ratio' => $request->input('portfolio_turnover_ratio'),
            'persentase_pph'       => $request->input('persentase_pph'),
            'fair_value_level_1'   => $request->input('fair_value_level_1'),
            'fair_value_level_2'   => $request->input('fair_value_level_2'),
            'fair_value_level_3'   => $request->input('fair_value_level_3'),
            'unit_milik_investor'  => $request->input('unit_milik_investor'),
            'unit_milik_mi'        => $request->input('unit_milik_mi'),
            'total_unit_beredar'   => $request->input('total_unit_beredar'),
        ]);

        $analisa->setRelation('sektor', collect($request->input('sektor', []))
            ->filter(fn ($r) => !empty($r['nama_sektor']) && ($r['bobot'] ?? '') !== '')
            ->map(fn ($r) => new AnalisaSektor([
                'nama_sektor' => $r['nama_sektor'],
                'bobot'       => $r['bobot'],
            ])));

        $analisa->setRelation('efek', collect($request->input('efek', []))
            ->filter(fn ($r) => !empty($r['nama_efek']))
            ->map(fn ($r) => new AnalisaEfek([
                'kode_efek'           => $r['kode_efek'],
                'nama_efek'           => $r['nama_efek'],
                'sektor'              => $r['sektor'] ?? null,
                'bobot'               => $r['bobot'] ?? null,
                'kontribusi_kinerja'  => $r['kontribusi_kinerja'] ?? null,
                'market_cap'          => $r['market_cap'] ?? null,
                'nilai_pasar'         => $r['nilai_pasar'] ?? null,
                'return_1m'           => $r['return_1m'] ?? null,
                'return_3m'           => $r['return_3m'] ?? null,
                'return_6m'           => $r['return_6m'] ?? null,
                'return_1y'           => $r['return_1y'] ?? null,
                'ihsg_contribution'   => $r['ihsg_contribution'] ?? null,
                'effect_type'         => $r['effect_type'] ?? null,
                'top_10'              => !empty($r['top_10']),
            ])));

        $analisa->setRelation('kinerja', collect($request->input('kinerja', []))
            ->filter(fn ($r) => !empty($r['periode']) && ($r['return_pct'] ?? '') !== '')
            ->map(fn ($r) => new AnalisaKinerjaBulanan([
                'periode'    => $r['periode'],
                'return_pct' => $r['return_pct'],
            ])));

        $analisa->setRelation('sukuk', collect($request->input('sukuk', []))
            ->filter(fn ($r) => !empty($r['kode_sukuk']) && !empty($r['nama_sukuk']))
            ->map(fn ($r) => new AnalisaSukuk([
                'kode_sukuk'  => $r['kode_sukuk'],
                'nama_sukuk'  => $r['nama_sukuk'],
                'jenis_sukuk' => $r['jenis_sukuk'] ?? null,
                'bobot'       => $r['bobot'] ?? null,
                'yield'       => $r['yield'] ?? null,
                'jatuh_tempo' => $r['jatuh_tempo'] ?? null,
                'rating'      => $r['rating'] ?? null,
            ])));

        $analisa->setRelation('obligasi', collect($request->input('obligasi', []))
            ->filter(fn ($r) => !empty($r['kode_obligasi']) && !empty($r['nama_obligasi']))
            ->map(fn ($r) => new AnalisaObligasi([
                'kode_obligasi'  => $r['kode_obligasi'],
                'nama_obligasi'  => $r['nama_obligasi'],
                'bobot'          => $r['bobot'] ?? null,
                'durasi'         => $r['durasi'] ?? null,
                'rating'         => $r['rating'] ?? null,
            ])));

        $analisa->setRelation('bank', collect($request->input('bank', []))
            ->filter(fn ($r) => !empty($r['nama_bank']) && ($r['bobot'] ?? '') !== '')
            ->map(fn ($r) => new AnalisaBank([
                'nama_bank'           => $r['nama_bank'],
                'bobot'               => $r['bobot'],
                'car'                 => $r['car'] ?? null,
                'npl'                 => $r['npl'] ?? null,
                'klasifikasi_risiko'  => $r['klasifikasi_risiko'] ?? null,
            ])));

        $analisa->setRelation('alokasiAset', collect($request->input('alokasi_aset', []))
            ->filter(fn ($r) => !empty($r['nama_aset']) && ($r['persentase'] ?? '') !== '')
            ->map(fn ($r) => new AnalisaAlokasiAset([
                'nama_aset'  => $r['nama_aset'],
                'persentase' => $r['persentase'],
            ])));

        return $analisa;
    }
}
