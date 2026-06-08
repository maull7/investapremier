<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalisaEfek;
use App\Models\AnalisaObligasi;
use App\Models\Stock;
use App\Models\SekuritasInformasi;
use Illuminate\Http\Request;

class ReksaDanaHoldingsController extends Controller
{
    public function efek(Request $request, string $kode)
    {
        $stock = Stock::where('kode', $kode)->first();
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 25;

        $holdings = AnalisaEfek::with('analisa')
            ->where('kode_efek', $kode)
            ->when($request->search, function ($q, $s) {
                $q->whereHas('analisa', function ($q2) use ($s) {
                    $q2->where('nama_reksa_dana', 'like', "%{$s}%")
                        ->orWhere('kode_reksa_dana', 'like', "%{$s}%");
                });
            })
            ->orderBy('bobot', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.sekuritas.holdings', [
            'type' => 'efek',
            'kode' => $kode,
            'nama' => $stock?->nama ?? $kode,
            'holdings' => $holdings,
            'perPage' => $perPage,
        ]);
    }

    public function obligasi(Request $request, string $kode)
    {
        $sekuritas = SekuritasInformasi::where('kode_obligasi', $kode)->first();
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 25;

        $holdings = AnalisaObligasi::with('analisa')
            ->where('kode_obligasi', $kode)
            ->when($request->search, function ($q, $s) {
                $q->whereHas('analisa', function ($q2) use ($s) {
                    $q2->where('nama_reksa_dana', 'like', "%{$s}%")
                        ->orWhere('kode_reksa_dana', 'like', "%{$s}%");
                });
            })
            ->orderBy('bobot', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.sekuritas.holdings', [
            'type' => 'obligasi',
            'kode' => $kode,
            'nama' => $sekuritas?->nama_obligasi ?? $kode,
            'holdings' => $holdings,
            'perPage' => $perPage,
        ]);
    }
}
