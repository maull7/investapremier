<?php

namespace App\Http\Controllers\Admin;

use App\Exports\YtmNormalCurveTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\YtmNormalCurveImport;
use App\Models\PheiCreditSpreadMatrix;
use App\Models\RatingObligasi;
use App\Models\YtmNormalCurve;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\ActivityLogger;

class YtmNormalCurveController extends Controller
{
    public function index()
    {
        $curves = YtmNormalCurve::with('rating')->orderBy('tenor_bulan')->orderBy('rating_id')->get();
        $grouped = $curves->groupBy(fn ($c) => $c->rating->kode . ' - ' . $c->rating->nama);
        $ratings = RatingObligasi::orderBy('urutan')->orderBy('kode')->get();
        return view('admin.ytm-normal-curve.index', compact('grouped', 'ratings'));
    }

    public function chartData()
    {
        $latestMatrixDate = PheiCreditSpreadMatrix::max('data_date');
        if ($latestMatrixDate) {
            $matrices = PheiCreditSpreadMatrix::whereDate('data_date', $latestMatrixDate)
                ->orderBy('tenor_bulan')
                ->get();

            $categories = $matrices->pluck('tenor_bulan')->map(fn ($tenor) => (int) $tenor)->values();

            return response()->json([
                'series' => [
                    ['name' => 'AAA', 'data' => $matrices->pluck('rating_aaa')->map(fn ($v) => $v !== null ? (float) $v : null)->values()],
                    ['name' => 'AA', 'data' => $matrices->pluck('rating_aa')->map(fn ($v) => $v !== null ? (float) $v : null)->values()],
                    ['name' => 'A', 'data' => $matrices->pluck('rating_a')->map(fn ($v) => $v !== null ? (float) $v : null)->values()],
                    ['name' => 'BBB', 'data' => $matrices->pluck('rating_bbb')->map(fn ($v) => $v !== null ? (float) $v : null)->values()],
                ],
                'categories' => $categories,
                'data_date' => $latestMatrixDate,
                'source' => 'PHEI Credit Spread Matrix',
            ]);
        }

        $curves = YtmNormalCurve::with('rating')
            ->select('ytm_normal_curves.*')
            ->join('rating_obligasi', 'rating_obligasi.id', '=', 'ytm_normal_curves.rating_id')
            ->orderBy('rating_obligasi.urutan')
            ->orderBy('rating_obligasi.kode')
            ->orderBy('ytm_normal_curves.tenor_bulan')
            ->get();

        $categories = $curves->pluck('tenor_bulan')
            ->unique()
            ->sort()
            ->values()
            ->map(fn ($tenor) => (int) $tenor);

        $series = $curves
            ->groupBy('rating_id')
            ->map(function ($ratingCurves) use ($categories) {
                $firstCurve = $ratingCurves->first();
                $valuesByTenor = $ratingCurves->keyBy('tenor_bulan');

                return [
                    'name' => $firstCurve->rating->kode,
                    'data' => $categories->map(function ($tenor) use ($valuesByTenor) {
                        $curve = $valuesByTenor->get($tenor);

                        return $curve ? (float) $curve->ytm_normal : null;
                    })->values(),
                ];
            })
            ->values();

        return response()->json([
            'series' => $series,
            'categories' => $categories,
        ]);
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

        $curve = YtmNormalCurve::create($data);

        ActivityLogger::log(
            'Membuat YTM Normal Curve',
            "YTM Normal Curve untuk rating id {$curve->rating_id} tenor {$curve->tenor_bulan} bulan berhasil ditambahkan",
            'success',
            $curve,
        );

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

        ActivityLogger::log(
            'Memperbarui YTM Normal Curve',
            "YTM Normal Curve untuk rating id {$ytmNormalCurve->rating_id} tenor {$ytmNormalCurve->tenor_bulan} bulan berhasil diperbarui",
            'success',
            $ytmNormalCurve,
        );

        return redirect()->route('admin.ytm-normal-curve.index')
            ->with('success', 'Data YTM Normal Curve berhasil disimpan.');
    }

    public function destroy(YtmNormalCurve $ytmNormalCurve)
    {
        ActivityLogger::log(
            'Menghapus YTM Normal Curve',
            "YTM Normal Curve untuk rating id {$ytmNormalCurve->rating_id} tenor {$ytmNormalCurve->tenor_bulan} bulan berhasil dihapus",
            'success',
            $ytmNormalCurve,
        );

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

        ActivityLogger::log(
            'Import YTM Normal Curve',
            "{$import->imported} data YTM Normal Curve berhasil diimport",
            'success',
        );

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
