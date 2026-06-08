<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitLink;
use App\Models\HargaUnitLink;
use App\Exports\UnitLinkTemplateExport;
use App\Exports\HargaUnitLinkTemplateExport;
use App\Imports\UnitLinkImport;
use App\Imports\HargaUnitLinkImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Support\ActivityLogger;

class UnitLinkController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'unit-links');
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;

        $unitLinks = collect();
        $hargaUnitLinks = collect();

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
        } elseif ($tab === 'unit-prices') {
            $query = HargaUnitLink::with('unitLink')->latest('datetime');
            if ($request->search) {
                $s = $request->search;
                $query->whereHas('unitLink', fn($q) => $q->where('unit_link', 'like', "%{$s}%"));
            }
            $hargaUnitLinks = $query->paginate($perPage)->withQueryString();
        }

        return view('admin.unit-link.index', compact('tab', 'perPage', 'unitLinks', 'hargaUnitLinks'));
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

        $unitLink = UnitLink::create($data);

        ActivityLogger::log(
            'Membuat Unit Link',
            "Unit link {$unitLink->unit_link} berhasil ditambahkan",
            'success',
            $unitLink,
        );

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

        ActivityLogger::log(
            'Memperbarui Unit Link',
            "Unit link {$unitLink->unit_link} berhasil diperbarui",
            'success',
            $unitLink,
        );

        return redirect()->route('admin.unit-link.index', ['tab' => 'unit-links'])
            ->with('success', 'Unit link berhasil diperbarui.');
    }

    public function destroy(UnitLink $unitLink)
    {
        ActivityLogger::log(
            'Menghapus Unit Link',
            "Unit link {$unitLink->unit_link} berhasil dihapus",
            'success',
            $unitLink,
        );

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

        ActivityLogger::log(
            'Import Unit Link',
            "{$import->imported} data unit link berhasil diimport",
            'success',
        );

        return redirect()->route('admin.unit-link.index', ['tab' => 'unit-links'])
            ->with('success', "{$import->imported} data unit link berhasil diimport.");
    }

    public function downloadTemplateHarga()
    {
        return Excel::download(new HargaUnitLinkTemplateExport, 'template-harga-unit-link.xlsx');
    }

    public function importHarga(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);
        $import = new HargaUnitLinkImport;
        Excel::import($import, $request->file('file'));

        ActivityLogger::log(
            'Import Harga Unit Link',
            "{$import->imported} data harga unit link berhasil diimport",
            'success',
        );

        return redirect()->route('admin.unit-link.index', ['tab' => 'unit-prices'])
            ->with('success', "{$import->imported} data harga unit link berhasil diimport.");
    }

    public function storeHarga(Request $request)
    {
        $data = $request->validate([
            'unit_link_id' => 'required|exists:unit_links,id',
            'datetime'     => 'required|date',
            'harga_median' => 'required|numeric',
            'sell_buy_low' => 'nullable|numeric',
            'sell_buy_high' => 'nullable|numeric',
        ]);

        $harga = HargaUnitLink::create($data);

        ActivityLogger::log(
            'Membuat Harga Unit Link',
            "Harga unit link untuk unit_link_id {$harga->unit_link_id} berhasil ditambahkan",
            'success',
            $harga,
        );

        return redirect()->route('admin.unit-link.index', ['tab' => 'unit-prices'])
            ->with('success', 'Harga unit link berhasil ditambahkan.');
    }

    public function updateHarga(Request $request, HargaUnitLink $hargaUnitLink)
    {
        $data = $request->validate([
            'unit_link_id'  => 'required|exists:unit_links,id',
            'datetime'      => 'required|date',
            'harga_median'  => 'required|numeric',
            'sell_buy_low'  => 'nullable|numeric',
            'sell_buy_high' => 'nullable|numeric',
        ]);

        $hargaUnitLink->update($data);

        ActivityLogger::log(
            'Memperbarui Harga Unit Link',
            "Harga unit link id {$hargaUnitLink->id} berhasil diperbarui",
            'success',
            $hargaUnitLink,
        );

        return redirect()->route('admin.unit-link.index', ['tab' => 'unit-prices'])
            ->with('success', 'Harga unit link berhasil diperbarui.');
    }

    public function destroyHarga(HargaUnitLink $hargaUnitLink)
    {
        ActivityLogger::log(
            'Menghapus Harga Unit Link',
            "Harga unit link id {$hargaUnitLink->id} berhasil dihapus",
            'success',
            $hargaUnitLink,
        );

        $hargaUnitLink->delete();
        return redirect()->route('admin.unit-link.index', ['tab' => 'unit-prices'])
            ->with('success', 'Harga unit link berhasil dihapus.');
    }
}
