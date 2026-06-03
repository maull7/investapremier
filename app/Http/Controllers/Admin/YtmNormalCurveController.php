<?php

namespace App\Http\Controllers\Admin;

use App\Exports\YtmNormalCurveTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\YtmNormalCurveImport;
use App\Models\RatingObligasi;
use App\Models\YtmNormalCurve;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class YtmNormalCurveController extends Controller
{
    public function index()
    {
        $curves = YtmNormalCurve::with('rating')->orderBy('tenor_bulan')->orderBy('rating_id')->get();
        $grouped = $curves->groupBy(fn ($c) => $c->rating->kode . ' - ' . $c->rating->nama);
        $ratings = RatingObligasi::orderBy('urutan')->orderBy('kode')->get();
        return view('admin.ytm-normal-curve.index', compact('grouped', 'ratings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rating_id' => 'required|exists:rating_obligasi,id',
            'tenor_bulan' => 'required|integer|min:1',
            'ytm_normal' => 'required|numeric|min:0|max:100',
        ]);

        $exists = YtmNormalCurve::where('rating_id', $data['rating_id'])
            ->where('tenor_bulan', $data['tenor_bulan'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'tenor_bulan' => 'Kombinasi Rating dan Tenor sudah ada.',
            ]);
        }

        YtmNormalCurve::create($data);

        return redirect()->route('admin.ytm-normal-curve.index')
            ->with('success', 'Data YTM Normal Curve berhasil ditambahkan.');
    }

    public function edit(YtmNormalCurve $ytmNormalCurve)
    {
        $ratings = RatingObligasi::orderBy('urutan')->orderBy('kode')->get();
        return view('admin.ytm-normal-curve.form', compact('ytmNormalCurve', 'ratings'));
    }

    public function update(Request $request, YtmNormalCurve $ytmNormalCurve)
    {
        $data = $request->validate([
            'rating_id' => 'required|exists:rating_obligasi,id',
            'tenor_bulan' => 'required|integer|min:1',
            'ytm_normal' => 'required|numeric|min:0|max:100',
        ]);

        $exists = YtmNormalCurve::where('rating_id', $data['rating_id'])
            ->where('tenor_bulan', $data['tenor_bulan'])
            ->where('id', '!=', $ytmNormalCurve->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'tenor_bulan' => 'Kombinasi Rating dan Tenor sudah ada.',
            ]);
        }

        $ytmNormalCurve->update($data);

        return redirect()->route('admin.ytm-normal-curve.index')
            ->with('success', 'Data YTM Normal Curve berhasil disimpan.');
    }

    public function destroy(YtmNormalCurve $ytmNormalCurve)
    {
        $ytmNormalCurve->delete();

        return redirect()->route('admin.ytm-normal-curve.index')
            ->with('success', 'Data YTM Normal Curve berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new YtmNormalCurveTemplateExport, 'template-ytm-normal-curve.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);

        $import = new YtmNormalCurveImport;
        Excel::import($import, $request->file('file'));

        $message = "{$import->imported} data YTM Normal Curve berhasil diimport.";
        if ($import->imported === 0) {
            $message = 'Tidak ada data yang diimport. Pastikan format file sesuai template.';
        }

        if (!empty($import->errors)) {
            $message .= ' ' . implode('. ', array_slice($import->errors, 0, 5));
        }

        return redirect()->route('admin.ytm-normal-curve.index')
            ->with('success', $message);
    }
}
