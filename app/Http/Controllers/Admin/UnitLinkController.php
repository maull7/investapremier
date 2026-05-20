<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitLink;
use App\Exports\UnitLinkTemplateExport;
use App\Imports\UnitLinkImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class UnitLinkController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'unit-links');
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;

        $unitLinks = collect();

        if ($tab === 'unit-links') {
            $query = UnitLink::latest();
            if ($request->search) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('unit_link', 'like', "%{$s}%")
                      ->orWhere('asuransi', 'like', "%{$s}%")
                      ->orWhere('jenis', 'like', "%{$s}%");
                });
            }
            $unitLinks = $query->paginate($perPage)->withQueryString();
        }

        return view('admin.unit-link.index', compact('tab', 'perPage', 'unitLinks'));
    }

    public function create()
    {
        return view('admin.unit-link.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_link' => 'required|string|max:255|unique:unit_links,unit_link',
            'asuransi' => 'nullable|string|max:255',
            'jenis' => 'nullable|string|max:255',
            'tipe' => 'nullable|string|max:255',
            'mata_uang' => 'nullable|string|max:10',
            'median_price' => 'nullable|numeric',
            'buy_price' => 'nullable|numeric',
            'sell_price' => 'nullable|numeric',
            'last_update' => 'nullable|date',
        ]);

        UnitLink::create($data);

        return redirect()->route('admin.unit-link.index', ['tab' => 'unit-links'])
            ->with('success', 'Unit link berhasil ditambahkan.');
    }

    public function edit(UnitLink $unitLink)
    {
        return view('admin.unit-link.form', ['unitLink' => $unitLink]);
    }

    public function update(Request $request, UnitLink $unitLink)
    {
        $data = $request->validate([
            'unit_link' => 'required|string|max:255|unique:unit_links,unit_link,' . $unitLink->id,
            'asuransi' => 'nullable|string|max:255',
            'jenis' => 'nullable|string|max:255',
            'tipe' => 'nullable|string|max:255',
            'mata_uang' => 'nullable|string|max:10',
            'median_price' => 'nullable|numeric',
            'buy_price' => 'nullable|numeric',
            'sell_price' => 'nullable|numeric',
            'last_update' => 'nullable|date',
        ]);

        $unitLink->update($data);

        return redirect()->route('admin.unit-link.index', ['tab' => 'unit-links'])
            ->with('success', 'Unit link berhasil diperbarui.');
    }

    public function destroy(UnitLink $unitLink)
    {
        $unitLink->delete();
        return redirect()->route('admin.unit-link.index', ['tab' => 'unit-links'])
            ->with('success', 'Unit link berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new UnitLinkTemplateExport, 'template-unit-link.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);
        $import = new UnitLinkImport;
        Excel::import($import, $request->file('file'));
        return redirect()->route('admin.unit-link.index', ['tab' => 'unit-links'])
            ->with('success', "{$import->imported} data unit link berhasil diimport.");
    }
}
