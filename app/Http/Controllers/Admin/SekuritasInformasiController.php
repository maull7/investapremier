<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SekuritasInformasi;
use App\Exports\SekuritasInformasiTemplateExport;
use App\Imports\SekuritasInformasiImport;
use App\Support\ActivityLogger;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SekuritasInformasiController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->get('tab'), ['government', 'corporate']) ? $request->tab : 'government';
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;

        $query = SekuritasInformasi::where('type', $tab);

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('kode_obligasi', 'like', "%{$s}%")
                    ->orWhere('nama_obligasi', 'like', "%{$s}%")
                    ->orWhere('isin_code', 'like', "%{$s}%");
            });
        }

        $data = $query->latest()->paginate($perPage)->withQueryString();

        return view('admin.sekuritas-informasi.index', compact('data', 'perPage', 'tab'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:government,corporate',
            'kode_obligasi' => [
                'required',
                'string',
                'max:20',
                Rule::unique('sekuritas_informasi')->where('type', $request->type),
            ],
            'nama_obligasi' => 'required|string|max:255',
            'isin_code' => 'nullable|string|max:30',
            'currency' => 'nullable|string|max:10',
            'outstanding_amount' => 'nullable|numeric',
            'coupon' => 'nullable|numeric',
            'maturity_date' => 'nullable|date',
        ]);

        $item = SekuritasInformasi::create($request->all());

        $typeLabel = SekuritasInformasi::typeLabel($request->type);

        ActivityLogger::log(
            "Membuat {$typeLabel}",
            "{$item->kode_obligasi} - {$item->nama_obligasi} berhasil ditambahkan",
            'success',
            $item,
        );

        $redirectTab = $request->type === 'government' ? 'government' : 'corporate';

        return redirect()->route('admin.sekuritas-informasi.index', ['tab' => $redirectTab])
            ->with('success', "Data {$typeLabel} berhasil ditambahkan.");
    }

    public function update(Request $request, SekuritasInformasi $sekuritasInformasi)
    {
        $request->validate([
            'kode_obligasi' => [
                'required',
                'string',
                'max:20',
                Rule::unique('sekuritas_informasi')->where('type', $sekuritasInformasi->type)->ignore($sekuritasInformasi->id),
            ],
            'nama_obligasi' => 'required|string|max:255',
            'isin_code' => 'nullable|string|max:30',
            'currency' => 'nullable|string|max:10',
            'outstanding_amount' => 'nullable|numeric',
            'coupon' => 'nullable|numeric',
            'maturity_date' => 'nullable|date',
        ]);

        $sekuritasInformasi->update($request->all());

        $typeLabel = SekuritasInformasi::typeLabel($sekuritasInformasi->type);

        ActivityLogger::log(
            "Mengubah {$typeLabel}",
            "{$sekuritasInformasi->kode_obligasi} - {$sekuritasInformasi->nama_obligasi} berhasil diperbarui",
            'success',
            $sekuritasInformasi,
        );

        $redirectTab = $sekuritasInformasi->type === 'government' ? 'government' : 'corporate';

        return redirect()->route('admin.sekuritas-informasi.index', ['tab' => $redirectTab])
            ->with('success', "Data {$typeLabel} berhasil diperbarui.");
    }

    public function destroy(SekuritasInformasi $sekuritasInformasi)
    {
        $typeLabel = SekuritasInformasi::typeLabel($sekuritasInformasi->type);
        $redirectTab = $sekuritasInformasi->type === 'government' ? 'government' : 'corporate';

        ActivityLogger::log(
            "Menghapus {$typeLabel}",
            "{$sekuritasInformasi->kode_obligasi} - {$sekuritasInformasi->nama_obligasi} berhasil dihapus",
            'success',
            $sekuritasInformasi,
        );

        $sekuritasInformasi->delete();

        return redirect()->route('admin.sekuritas-informasi.index', ['tab' => $redirectTab])
            ->with('success', "Data {$typeLabel} berhasil dihapus.");
    }

    public function downloadTemplate()
    {
        return Excel::download(new SekuritasInformasiTemplateExport, 'template-sekuritas.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'type' => 'required|in:government,corporate',
        ]);

        $import = new SekuritasInformasiImport($request->type);
        Excel::import($import, $request->file('file'));

        $typeLabel = SekuritasInformasi::typeLabel($request->type);
        $redirectTab = $request->type === 'government' ? 'government' : 'corporate';

        ActivityLogger::log(
            "Import {$typeLabel}",
            "{$import->imported} data {$typeLabel} berhasil diimport dari Excel",
            'success',
        );

        return redirect()->route('admin.sekuritas-informasi.index', ['tab' => $redirectTab])
            ->with('success', "{$import->imported} data {$typeLabel} berhasil diimport dari Excel.");
    }
}
