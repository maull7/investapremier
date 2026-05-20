<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ObligasiHargaReferensi;
use App\Models\ObligasiBond;
use Illuminate\Http\Request;

class ObligasiController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'harga-referensi');
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;

        $hargaReferensi = collect();
        $bonds = collect();

        if ($tab === 'bond') {
            $query = ObligasiBond::latest();
            if ($request->search) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('kode', 'like', "%{$s}%")
                      ->orWhere('periode', 'like', "%{$s}%");
                });
            }
            $bonds = $query->paginate($perPage)->withQueryString();
        } else {
            $query = ObligasiHargaReferensi::latest();
            if ($request->search) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('kode', 'like', "%{$s}%")
                      ->orWhere('nama', 'like', "%{$s}%")
                      ->orWhere('emiten', 'like', "%{$s}%");
                });
            }
            $hargaReferensi = $query->paginate($perPage)->withQueryString();
        }

        return view('obligasi.index', compact('tab', 'perPage', 'hargaReferensi', 'bonds'));
    }
}
