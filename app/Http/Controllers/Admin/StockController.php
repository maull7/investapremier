<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\ExtractionBatch;
use App\Exports\StocksTemplateExport;
use App\Imports\StocksImport;
use App\Support\ActivityLogger;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'daftar');
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
        $extractionBatches = ExtractionBatch::with('creator')
            ->where('data_type', 'stock_daily_transaction')
            ->latest()
            ->paginate(10, ['*'], 'batch_page')
            ->withQueryString();
        $extractionRanges = $this->buildRangeOptions(Stock::count());
        $detailBatch = $request->filled('detail_batch')
            ? ExtractionBatch::with([
                'stockDailyTransactions',
            ])->where('data_type', 'stock_daily_transaction')->find($request->integer('detail_batch'))
            : null;

        return view('admin.stocks.index', compact('stocks', 'perPage', 'tab', 'extractionBatches', 'detailBatch', 'extractionRanges'));
    }

    public function create()
    {
        return view('admin.stocks.form');
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

        $stock = Stock::create($request->all());

        ActivityLogger::log(
            'Membuat Saham',
            "Saham {$stock->kode} - {$stock->nama} berhasil ditambahkan",
            'success',
            $stock,
        );

        return redirect()->route('admin.saham.index')->with('success', 'Saham berhasil ditambahkan.');
    }

    public function edit(Stock $stock)
    {
        return view('admin.stocks.form', compact('stock'));
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

        ActivityLogger::log(
            'Mengubah Saham',
            "Saham {$stock->kode} - {$stock->nama} berhasil diperbarui",
            'success',
            $stock,
        );

        return redirect()->route('admin.saham.index')->with('success', 'Saham berhasil diperbarui.');
    }

    public function destroy(Stock $stock)
    {
        ActivityLogger::log(
            'Menghapus Saham',
            "Saham {$stock->kode} - {$stock->nama} berhasil dihapus",
            'success',
            $stock,
        );
        $stock->delete();
        return redirect()->route('admin.saham.index')->with('success', 'Saham berhasil dihapus.');
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

        ActivityLogger::log(
            'Import Saham',
            "{$import->imported} data saham berhasil diimport dari Excel",
            'success',
        );

        return redirect()->route('admin.saham.index')
            ->with('success', "{$import->imported} data saham berhasil diimport dari Excel.");
    }

    private function buildRangeOptions(int $total, int $step = 20): array
    {
        $ranges = [];

        for ($start = 1; $start <= $total; $start += $step) {
            $end = min($start + $step - 1, $total);
            $ranges[] = [
                'value' => "{$start}-{$end}",
                'label' => "{$start} - {$end}",
            ];
        }

        return $ranges;
    }
}
