<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunDataExtractionJob;
use App\Models\ExtractionBatch;
use App\Models\ObligasiHargaReferensi;
use App\Models\Stock;
use App\Models\StockPrice;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtractionBatchController extends Controller
{
    public function storeStock(Request $request)
    {
        return $this->storeBatch($request, 'stock_daily_transaction', 'admin.saham.index');
    }

    public function storeBond(Request $request)
    {
        return $this->storeBatch($request, 'bond_data', 'admin.obligasi.index');
    }

    public function retry(ExtractionBatch $extractionBatch)
    {
        $extractionBatch->update([
            'status' => 'pending',
            'error_message' => null,
            'total_records' => 0,
            'started_at' => null,
            'finished_at' => null,
        ]);

        $extractionBatch->stockDailyTransactions()->delete();
        $extractionBatch->bondData()->delete();

        RunDataExtractionJob::dispatch($extractionBatch->id);

        return back()->with('success', "Batch #{$extractionBatch->id} dijalankan ulang.");
    }

    public function save(Request $request, ExtractionBatch $extractionBatch)
    {
        $data = $request->validate([
            'duplicate_action' => 'required|in:skip,update',
        ]);

        if ($extractionBatch->status !== 'success') {
            return back()->with('error', 'Hanya batch sukses yang dapat disimpan ke database utama.');
        }

        $result = DB::transaction(fn () => match ($extractionBatch->data_type) {
            'stock_daily_transaction' => $this->saveStockRows($extractionBatch, $data['duplicate_action']),
            'bond_data' => $this->saveBondRows($extractionBatch, $data['duplicate_action']),
            default => ['saved' => 0, 'updated' => 0, 'skipped' => 0],
        });

        ActivityLogger::log(
            'Simpan Hasil Ekstrak',
            "Batch #{$extractionBatch->id}: {$result['saved']} baru, {$result['updated']} update, {$result['skipped']} skip",
            'success',
            $extractionBatch,
            $result,
        );

        return back()->with('success', "Hasil ekstrak disimpan. Baru: {$result['saved']}, update: {$result['updated']}, skip: {$result['skipped']}.");
    }

    public function destroy(ExtractionBatch $extractionBatch)
    {
        $id = $extractionBatch->id;
        $extractionBatch->delete();

        return back()->with('success', "Hasil ekstrak batch #{$id} dihapus.");
    }

    private function storeBatch(Request $request, string $dataType, string $redirectRoute)
    {
        $data = $request->validate([
            'data_date' => 'required|date',
            'range' => 'required|string',
        ]);

        [$rangeStart, $rangeEnd] = array_pad(array_map('intval', explode('-', $data['range'], 2)), 2, 0);
        $total = $dataType === 'stock_daily_transaction'
            ? Stock::count()
            : ObligasiHargaReferensi::count();

        if ($rangeStart < 1 || $rangeEnd < $rangeStart || $rangeEnd > $total) {
            return back()->with('error', 'Range data tidak valid.');
        }

        $batch = ExtractionBatch::create([
            'data_type' => $dataType,
            'source' => $dataType === 'stock_daily_transaction' ? 'IDX Market / Yahoo Finance' : 'PHEI / IDX',
            'data_date' => $data['data_date'],
            'identifier' => null,
            'range_start' => $rangeStart,
            'range_end' => $rangeEnd,
            'range_label' => "{$rangeStart}-{$rangeEnd}",
            'status' => 'pending',
            'created_by' => $request->user()?->id,
        ]);

        RunDataExtractionJob::dispatch($batch->id);

        ActivityLogger::log('Mulai Ekstrak Data', "Batch ekstraksi #{$batch->id} dibuat", 'success', $batch);

        return redirect()->route($redirectRoute, [
            'tab' => 'hasil-ekstrak',
            'detail_batch' => $batch->id,
        ])->with('success', 'Ekstraksi dijalankan lewat queue. Pantau status di tab Hasil Ekstrak.');
    }

    private function saveStockRows(ExtractionBatch $batch, string $duplicateAction): array
    {
        $result = ['saved' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($batch->stockDailyTransactions as $row) {
            $stock = Stock::firstOrCreate(
                ['kode' => strtoupper($row->stock_code)],
                ['nama' => strtoupper($row->stock_code)]
            );

            $existing = StockPrice::where('kode_efek', strtoupper($row->stock_code))
                ->whereDate('tanggal', $row->data_date)
                ->where('sumber', $row->source)
                ->first();

            if ($existing && $duplicateAction === 'skip') {
                $result['skipped']++;
                continue;
            }

            $payload = [
                'stock_id' => $stock->id,
                'kode_efek' => strtoupper($row->stock_code),
                'nama_efek' => $stock->nama,
                'jenis' => 'Saham',
                'harga' => $row->close,
                'open' => $row->open,
                'high' => $row->high,
                'low' => $row->low,
                'close' => $row->close,
                'volume' => $row->volume,
                'tanggal' => $row->data_date,
                'sumber' => $row->source,
            ];

            if ($existing) {
                $existing->update($payload);
                $result['updated']++;
            } else {
                StockPrice::updateOrCreate(
                    ['kode_efek' => strtoupper($row->stock_code), 'tanggal' => $row->data_date, 'sumber' => $row->source],
                    $payload
                );
                $result['saved']++;
            }

            $stock->update([
                'harga_terbaru' => $row->close,
                'harga_pembukaan' => $row->open,
                'harga_tertinggi' => $row->high,
                'harga_terendah' => $row->low,
                'volume' => $row->volume,
                'market_capital' => $row->market_cap ?: $stock->market_capital,
                'last_update' => $row->data_date,
            ]);
        }

        return $result;
    }

    private function saveBondRows(ExtractionBatch $batch, string $duplicateAction): array
    {
        $result = ['saved' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($batch->bondData as $row) {
            $existing = ObligasiHargaReferensi::where('kode', strtoupper($row->bond_code))->first();

            if ($existing && $duplicateAction === 'skip') {
                $result['skipped']++;
                continue;
            }

            $payload = [
                'kode' => strtoupper($row->bond_code),
                'nama' => $row->bond_name,
                'emiten' => $row->issuer,
                'rating' => $row->rating,
                'kupon' => $row->coupon,
                'jatuh_tempo' => $row->maturity_date,
                'harga_persen' => $row->fair_price,
                'ytm' => $row->yield,
            ];

            ObligasiHargaReferensi::updateOrCreate(['kode' => strtoupper($row->bond_code)], $payload);
            $existing ? $result['updated']++ : $result['saved']++;
        }

        return $result;
    }
}
