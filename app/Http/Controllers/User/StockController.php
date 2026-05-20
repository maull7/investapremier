<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Exports\StocksTemplateExport;
use App\Imports\StocksImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;
        $query = Stock::latest();

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('kode', 'like', "%{$s}%")
                  ->orWhere('nama', 'like', "%{$s}%")
                  ->orWhere('sektor', 'like', "%{$s}%");
            });
        }

        $stocks = $query->paginate($perPage)->withQueryString();

        return view('saham.index', compact('stocks', 'perPage'));
    }

    public function create()
    {
        return view('saham.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:10|unique:stocks,kode',
            'nama' => 'required|string|max:255',
            'sektor' => 'nullable|string|max:255',
            'sub_industri' => 'nullable|string|max:255',
            'harga_terbaru' => 'nullable|numeric',
            'harga_penutupan_sebelumnya' => 'nullable|numeric',
            'harga_pembukaan' => 'nullable|numeric',
            'harga_tertinggi' => 'nullable|numeric',
            'harga_terendah' => 'nullable|numeric',
            'volume' => 'nullable|numeric',
            'value' => 'nullable|numeric',
            'frekuensi' => 'nullable|numeric',
            'jumlah_saham' => 'nullable|numeric',
            'market_capital' => 'nullable|numeric',
            'last_update' => 'nullable|date',
        ]);

        Stock::create($request->all());

        return redirect()->route('user.saham.index')->with('success', 'Saham berhasil ditambahkan.');
    }

    public function edit(Stock $stock)
    {
        return view('saham.form', compact('stock'));
    }

    public function update(Request $request, Stock $stock)
    {
        $request->validate([
            'kode' => 'required|string|max:10|unique:stocks,kode,' . $stock->id,
            'nama' => 'required|string|max:255',
            'sektor' => 'nullable|string|max:255',
            'sub_industri' => 'nullable|string|max:255',
            'harga_terbaru' => 'nullable|numeric',
            'harga_penutupan_sebelumnya' => 'nullable|numeric',
            'harga_pembukaan' => 'nullable|numeric',
            'harga_tertinggi' => 'nullable|numeric',
            'harga_terendah' => 'nullable|numeric',
            'volume' => 'nullable|numeric',
            'value' => 'nullable|numeric',
            'frekuensi' => 'nullable|numeric',
            'jumlah_saham' => 'nullable|numeric',
            'market_capital' => 'nullable|numeric',
            'last_update' => 'nullable|date',
        ]);

        $stock->update($request->all());

        return redirect()->route('user.saham.index')->with('success', 'Saham berhasil diperbarui.');
    }

    public function destroy(Stock $stock)
    {
        $stock->delete();
        return redirect()->route('user.saham.index')->with('success', 'Saham berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new StocksTemplateExport, 'template-saham.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);

        $import = new StocksImport;
        Excel::import($import, $request->file('file'));

        return redirect()->route('user.saham.index')
            ->with('success', "{$import->imported} data saham berhasil diimport dari Excel.");
    }
}
