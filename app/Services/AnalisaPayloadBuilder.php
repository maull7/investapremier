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
            'total_aum'            => $request->input('total_aum'),
            'unit_penyertaan'      => $request->input('unit_penyertaan'),
            'nab_per_unit'         => $request->input('nab_per_unit'),
            'total_marcap_10_efek' => $request->input('total_marcap_10_efek'),
            'tanggal_data'         => $request->input('tanggal_data'),
            'ffs_bulan'            => $request->input('ffs_bulan'),
            'ffs_tahun'            => $request->input('ffs_tahun'),
        ]);

        $analisa->setRelation('sektor', collect($request->input('sektor', []))
            ->filter(fn ($r) => !empty($r['nama_sektor']) && ($r['bobot'] ?? '') !== '')
            ->map(fn ($r) => new AnalisaSektor([
                'nama_sektor' => $r['nama_sektor'],
                'bobot'       => $r['bobot'],
            ])));

        $analisa->setRelation('efek', collect($request->input('efek', []))
            ->filter(fn ($r) => !empty($r['kode_efek']) && !empty($r['nama_efek']))
            ->map(fn ($r) => new AnalisaEfek([
                'kode_efek'           => $r['kode_efek'],
                'nama_efek'           => $r['nama_efek'],
                'sektor'              => $r['sektor'] ?? null,
                'bobot'               => $r['bobot'] ?? null,
                'kontribusi_kinerja'  => $r['kontribusi_kinerja'] ?? null,
                'market_cap'          => $r['market_cap'] ?? null,
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
