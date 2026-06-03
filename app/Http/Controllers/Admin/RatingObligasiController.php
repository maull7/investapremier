<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RatingObligasiTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\RatingObligasiImport;
use App\Models\RatingObligasi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RatingObligasiController extends Controller
{
    public function index()
    {
        $ratings = RatingObligasi::orderBy('urutan')->orderBy('kode')->get();
        return view('admin.rating-obligasi.index', compact('ratings'));
    }

    public function create()
    {
        return view('admin.rating-obligasi.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:rating_obligasi,kode',
            'nama' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
            'urutan' => 'nullable|integer|min:0',
        ]);

        RatingObligasi::create($data);

        return redirect()->route('admin.rating-obligasi.index')
            ->with('success', "Rating {$data['kode']} berhasil ditambahkan.");
    }

    public function edit(RatingObligasi $ratingObligasi)
    {
        return view('admin.rating-obligasi.form', compact('ratingObligasi'));
    }

    public function update(Request $request, RatingObligasi $ratingObligasi)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:rating_obligasi,kode,' . $ratingObligasi->id,
            'nama' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
            'urutan' => 'nullable|integer|min:0',
        ]);

        $ratingObligasi->update($data);

        return redirect()->route('admin.rating-obligasi.index')
            ->with('success', "Rating {$data['kode']} berhasil disimpan.");
    }

    public function destroy(RatingObligasi $ratingObligasi)
    {
        $kode = $ratingObligasi->kode;
        $ratingObligasi->delete();

        return redirect()->route('admin.rating-obligasi.index')
            ->with('success', "Rating {$kode} berhasil dihapus.");
    }

    public function downloadTemplate()
    {
        return Excel::download(new RatingObligasiTemplateExport, 'template-rating-obligasi.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);

        $import = new RatingObligasiImport;
        Excel::import($import, $request->file('file'));

        $message = "{$import->imported} data rating obligasi berhasil diimport.";
        if ($import->imported === 0) {
            $message = 'Tidak ada data yang diimport. Pastikan format file sesuai template.';
        }

        return redirect()->route('admin.rating-obligasi.index')
            ->with('success', $message);
    }
}
