<?php

namespace App\Http\Controllers;

use App\Models\ReksaDana;
use Illuminate\Http\Request;

class ReksaDanaController extends Controller
{
    private const JENIS_OPTIONS = ['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'];
    private const KATEGORI_OPTIONS = ['Konvensional', 'Syariah', 'index', 'ETF'];

    public function index(Request $request)
    {
        $query = ReksaDana::orderBy('nama_reksa_dana');

        if ($request->filled('jenis')) {
            $query->whereIn('jenis', (array) $request->jenis);
        }

        if ($request->filled('kategori')) {
            $kategoriFilter = (array) $request->kategori;
            $query->where(function ($q) use ($kategoriFilter) {
                foreach ($kategoriFilter as $k) {
                    $q->whereJsonContains('kategori', $k);
                }
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_reksa_dana', 'like', "%{$s}%")
                  ->orWhere('kode_reksa_dana', 'like', "%{$s}%");
            });
        }

        $reksaDanas = $query->paginate(20)->withQueryString();

        return view('reksa-dana.index', [
            'reksaDanas'      => $reksaDanas,
            'jenisOptions'    => self::JENIS_OPTIONS,
            'kategoriOptions' => self::KATEGORI_OPTIONS,
        ]);
    }

    public function edit(ReksaDana $reksaDana)
    {
        abort(404);
    }

    public function update(Request $request, ReksaDana $reksaDana)
    {
        abort(404);
    }

    public function destroy(ReksaDana $reksaDana)
    {
        abort(404);
    }
}
