<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ObligasiHargaReferensi;
use App\Models\ObligasiBond;
use App\Models\ExtractionBatch;
use App\Exports\ObligasiHargaReferensiTemplateExport;
use App\Exports\ObligasiBondTemplateExport;
use App\Imports\ObligasiHargaReferensiImport;
use App\Imports\ObligasiBondImport;
use App\Jobs\SyncObligasiFromIdxJob;
use App\Models\SyncRun;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Support\ActivityLogger;

class ObligasiController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'harga-referensi');
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;

        $hargaReferensi = collect();
        $bonds = collect();
        $extractionBatches = collect();
        $detailBatch = null;

        if ($tab === 'bond') {
            $query = ObligasiBond::latest();
            if ($request->search) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('kode', 'like', "%{$s}%")
                      ->orWhere('periode', 'like', "%{$s}%");
                });
            }
            $bonds = $query->paginate($perPage)->withQueryString();
        } elseif ($tab === 'hasil-ekstrak') {
            $extractionBatches = ExtractionBatch::with('creator')
                ->where('data_type', 'bond_data')
                ->latest()
                ->paginate(10, ['*'], 'batch_page')
                ->withQueryString();

            $detailBatch = $request->filled('detail_batch')
                ? ExtractionBatch::with('bondData')
                    ->where('data_type', 'bond_data')
                    ->find($request->integer('detail_batch'))
                : null;
        } else {
            $query = ObligasiHargaReferensi::latest();
            if ($request->search) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('kode', 'like', "%{$s}%")
                      ->orWhere('nama', 'like', "%{$s}%")
                      ->orWhere('emiten', 'like', "%{$s}%");
                });
            }
            $hargaReferensi = $query->paginate($perPage)->withQueryString();
        }

        $extractionRanges = $this->buildRangeOptions(ObligasiHargaReferensi::count());

        return view('admin.obligasi.index', compact('tab', 'perPage', 'hargaReferensi', 'bonds', 'extractionBatches', 'detailBatch', 'extractionRanges'));
    }

    public function createHargaReferensi()
    {
        return view('admin.obligasi.form-harga-referensi');
    }

    public function storeHargaReferensi(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:obligasi_harga_referensi,kode',
            'nama' => 'nullable|string|max:255',
            'tanggal_terbit' => 'nullable|date',
            'emiten' => 'nullable|string|max:255',
            'sektor' => 'nullable|string|max:255',
            'sub_sektor' => 'nullable|string|max:255',
            'industri' => 'nullable|string|max:255',
            'sub_industri' => 'nullable|string|max:255',
            'denominasi' => 'nullable|string|max:50',
            'rating' => 'nullable|string|max:50',
            'syariah' => 'nullable|boolean',
            'kupon' => 'nullable|numeric',
            'jatuh_tempo' => 'nullable|date',
            'harga_persen' => 'nullable|numeric',
            'ttm' => 'nullable|numeric',
            'ytm' => 'nullable|numeric',
            'current_yield' => 'nullable|numeric',
            'total_val' => 'nullable|numeric',
            'outstanding_amount' => 'nullable|numeric',
        ]);

        $hargaReferensi = ObligasiHargaReferensi::create($data);

        ActivityLogger::log(
            'Membuat Harga Referensi',
            "Harga referensi {$hargaReferensi->kode} berhasil ditambahkan",
            'success',
            $hargaReferensi,
        );

        return redirect()->route('admin.obligasi.index', ['tab' => 'harga-referensi'])
            ->with('success', 'Obligasi harga referensi berhasil ditambahkan.');
    }

    public function editHargaReferensi(ObligasiHargaReferensi $obligasiHargaReferensi)
    {
        return view('admin.obligasi.form-harga-referensi', ['obligasi' => $obligasiHargaReferensi]);
    }

    public function updateHargaReferensi(Request $request, ObligasiHargaReferensi $obligasiHargaReferensi)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:obligasi_harga_referensi,kode,' . $obligasiHargaReferensi->id,
            'nama' => 'nullable|string|max:255',
            'tanggal_terbit' => 'nullable|date',
            'emiten' => 'nullable|string|max:255',
            'sektor' => 'nullable|string|max:255',
            'sub_sektor' => 'nullable|string|max:255',
            'industri' => 'nullable|string|max:255',
            'sub_industri' => 'nullable|string|max:255',
            'denominasi' => 'nullable|string|max:50',
            'rating' => 'nullable|string|max:50',
            'syariah' => 'nullable|boolean',
            'kupon' => 'nullable|numeric',
            'jatuh_tempo' => 'nullable|date',
            'harga_persen' => 'nullable|numeric',
            'ttm' => 'nullable|numeric',
            'ytm' => 'nullable|numeric',
            'current_yield' => 'nullable|numeric',
            'total_val' => 'nullable|numeric',
            'outstanding_amount' => 'nullable|numeric',
        ]);

        $obligasiHargaReferensi->update($data);

        ActivityLogger::log(
            'Memperbarui Harga Referensi',
            "Harga referensi {$obligasiHargaReferensi->kode} berhasil diperbarui",
            'success',
            $obligasiHargaReferensi,
        );

        return redirect()->route('admin.obligasi.index', ['tab' => 'harga-referensi'])
            ->with('success', 'Obligasi harga referensi berhasil diperbarui.');
    }

    public function destroyHargaReferensi(ObligasiHargaReferensi $obligasiHargaReferensi)
    {
        ActivityLogger::log(
            'Menghapus Harga Referensi',
            "Harga referensi {$obligasiHargaReferensi->kode} berhasil dihapus",
            'success',
            $obligasiHargaReferensi,
        );

        $obligasiHargaReferensi->delete();
        return redirect()->route('admin.obligasi.index', ['tab' => 'harga-referensi'])
            ->with('success', 'Obligasi harga referensi berhasil dihapus.');
    }

    public function createBond()
    {
        return view('admin.obligasi.form-bond');
    }

    public function storeBond(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20',
            'periode' => 'required|string|max:10',
            'current_asset' => 'nullable|numeric',
            'current_liabilities' => 'nullable|numeric',
            'total_asset' => 'nullable|numeric',
            'total_liabilities' => 'nullable|numeric',
            'retained_earning' => 'nullable|numeric',
            'equity' => 'nullable|numeric',
            'interest_expense' => 'nullable|numeric',
            'laba_operasional' => 'nullable|numeric',
            'cash_equivalents' => 'nullable|numeric',
            'account_receivable' => 'nullable|numeric',
            'inventories' => 'nullable|numeric',
            'other_current_asset' => 'nullable|numeric',
            'fixed_asset' => 'nullable|numeric',
            'other_non_current_asset' => 'nullable|numeric',
            'account_payable' => 'nullable|numeric',
            'accruals' => 'nullable|numeric',
            'short_term_loans' => 'nullable|numeric',
            'current_maturities_of_long_term_loans' => 'nullable|numeric',
            'other_current_liabilities' => 'nullable|numeric',
            'long_term_loans' => 'nullable|numeric',
            'employee_benefits' => 'nullable|numeric',
            'other_non_current_liabilities' => 'nullable|numeric',
            'total_non_current_liabilities' => 'nullable|numeric',
            'share_capital' => 'nullable|numeric',
            'additional_paid_in_capital' => 'nullable|numeric',
            'others' => 'nullable|numeric',
            'non_controlling_interest' => 'nullable|numeric',
            'total_equity_equity_to_parent_entity' => 'nullable|numeric',
            'net_revenue' => 'nullable|numeric',
            'cost_of_good_sold' => 'nullable|numeric',
            'gross_income' => 'nullable|numeric',
            'operational_expense' => 'nullable|numeric',
            'other_income_expense' => 'nullable|numeric',
            'income_before_tax' => 'nullable|numeric',
            'taxes' => 'nullable|numeric',
            'ebit' => 'nullable|numeric',
            'ebitda' => 'nullable|numeric',
            'net_income_attributable_to_non_controlling_interest' => 'nullable|numeric',
            'net_income' => 'nullable|numeric',
            'cash_flows_operating_activities' => 'nullable|numeric',
            'cash_flows_investment' => 'nullable|numeric',
            'cash_flows_financing' => 'nullable|numeric',
        ]);

        $bond = ObligasiBond::updateOrCreate(
            ['kode' => $data['kode'], 'periode' => $data['periode']],
            $data
        );

        ActivityLogger::log(
            'Membuat Bond',
            "Bond {$bond->kode} periode {$bond->periode} berhasil ditambahkan",
            'success',
            $bond,
        );

        return redirect()->route('admin.obligasi.index', ['tab' => 'bond'])
            ->with('success', 'Keuangan emiten berhasil ditambahkan.');
    }

    public function editBond(ObligasiBond $obligasiBond)
    {
        return view('admin.obligasi.form-bond', ['obligasi' => $obligasiBond]);
    }

    public function updateBond(Request $request, ObligasiBond $obligasiBond)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20',
            'periode' => 'required|string|max:10',
            'current_asset' => 'nullable|numeric',
            'current_liabilities' => 'nullable|numeric',
            'total_asset' => 'nullable|numeric',
            'total_liabilities' => 'nullable|numeric',
            'retained_earning' => 'nullable|numeric',
            'equity' => 'nullable|numeric',
            'interest_expense' => 'nullable|numeric',
            'laba_operasional' => 'nullable|numeric',
            'cash_equivalents' => 'nullable|numeric',
            'account_receivable' => 'nullable|numeric',
            'inventories' => 'nullable|numeric',
            'other_current_asset' => 'nullable|numeric',
            'fixed_asset' => 'nullable|numeric',
            'other_non_current_asset' => 'nullable|numeric',
            'account_payable' => 'nullable|numeric',
            'accruals' => 'nullable|numeric',
            'short_term_loans' => 'nullable|numeric',
            'current_maturities_of_long_term_loans' => 'nullable|numeric',
            'other_current_liabilities' => 'nullable|numeric',
            'long_term_loans' => 'nullable|numeric',
            'employee_benefits' => 'nullable|numeric',
            'other_non_current_liabilities' => 'nullable|numeric',
            'total_non_current_liabilities' => 'nullable|numeric',
            'share_capital' => 'nullable|numeric',
            'additional_paid_in_capital' => 'nullable|numeric',
            'others' => 'nullable|numeric',
            'non_controlling_interest' => 'nullable|numeric',
            'total_equity_equity_to_parent_entity' => 'nullable|numeric',
            'net_revenue' => 'nullable|numeric',
            'cost_of_good_sold' => 'nullable|numeric',
            'gross_income' => 'nullable|numeric',
            'operational_expense' => 'nullable|numeric',
            'other_income_expense' => 'nullable|numeric',
            'income_before_tax' => 'nullable|numeric',
            'taxes' => 'nullable|numeric',
            'ebit' => 'nullable|numeric',
            'ebitda' => 'nullable|numeric',
            'net_income_attributable_to_non_controlling_interest' => 'nullable|numeric',
            'net_income' => 'nullable|numeric',
            'cash_flows_operating_activities' => 'nullable|numeric',
            'cash_flows_investment' => 'nullable|numeric',
            'cash_flows_financing' => 'nullable|numeric',
        ]);

        $obligasiBond->update($data);

        ActivityLogger::log(
            'Memperbarui Bond',
            "Bond {$obligasiBond->kode} periode {$obligasiBond->periode} berhasil diperbarui",
            'success',
            $obligasiBond,
        );

        return redirect()->route('admin.obligasi.index', ['tab' => 'bond'])
            ->with('success', 'Keuangan emiten berhasil diperbarui.');
    }

    public function destroyBond(ObligasiBond $obligasiBond)
    {
        ActivityLogger::log(
            'Menghapus Bond',
            "Bond {$obligasiBond->kode} periode {$obligasiBond->periode} berhasil dihapus",
            'success',
            $obligasiBond,
        );

        $obligasiBond->delete();
        return redirect()->route('admin.obligasi.index', ['tab' => 'bond'])
            ->with('success', 'Keuangan emiten berhasil dihapus.');
    }

    public function downloadTemplateHargaReferensi()
    {
        return Excel::download(new ObligasiHargaReferensiTemplateExport, 'template-obligasi-harga-referensi.xlsx');
    }

    public function downloadTemplateBond()
    {
        return Excel::download(new ObligasiBondTemplateExport, 'template-obligasi-bond.xlsx');
    }

    public function importHargaReferensi(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);
        $import = new ObligasiHargaReferensiImport;
        Excel::import($import, $request->file('file'));

        ActivityLogger::log(
            'Import Harga Referensi',
            "{$import->imported} data harga referensi berhasil diimport",
            'success',
        );

        return redirect()->route('admin.obligasi.index', ['tab' => 'harga-referensi'])
            ->with('success', "{$import->imported} data obligasi harga referensi berhasil diimport.");
    }

    public function importBond(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);
        $import = new ObligasiBondImport;
        Excel::import($import, $request->file('file'));

        ActivityLogger::log(
            'Import Bond',
            "{$import->imported} data bond berhasil diimport",
            'success',
        );

        return redirect()->route('admin.obligasi.index', ['tab' => 'bond'])
            ->with('success', "{$import->imported} data keuangan emiten berhasil diimport.");
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

    /**
     * One-click sync obligasi. Dispatch job (non-blocking).
     * Job akan pake BackendSyncService kalau BACKEND_SYNC_URL dikonfigurasi.
     */
    public function syncFromIdx(Request $request)
    {
        $inflight = SyncRun::where('type', SyncRun::TYPE_OBLIGASI_IDX_PHEI)
            ->whereIn('status', [SyncRun::STATUS_QUEUED, SyncRun::STATUS_RUNNING])
            ->latest()
            ->first();

        if ($inflight) {
            $payload = [
                'run_id' => $inflight->id,
                'status' => $inflight->status,
                'message' => 'Sync sedang berjalan, melanjutkan polling.',
                'reused' => true,
            ];
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($payload);
            }
            return redirect()->route('admin.obligasi.index', ['tab' => 'harga-referensi'])
                ->with('sync_run_id', $inflight->id);
        }

        $run = SyncRun::create([
            'type' => SyncRun::TYPE_OBLIGASI_IDX_PHEI,
            'status' => SyncRun::STATUS_QUEUED,
            'current_step' => 'queued',
            'current_step_label' => 'Menunggu worker mengambil job dari antrian',
            'progress_percent' => 0,
            'user_id' => $request->user()?->id,
        ]);

        SyncObligasiFromIdxJob::dispatch($run->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'run_id' => $run->id,
                'status' => $run->status,
                'message' => 'Sync dimulai, pantau progres via polling.',
            ]);
        }

        return redirect()->route('admin.obligasi.index', ['tab' => 'harga-referensi'])
            ->with('sync_run_id', $run->id)
            ->with('success', 'Sync obligasi dimulai. Modal akan menampilkan progress real-time.');
    }

    /**
     * JSON endpoint polled by the frontend modal to track sync progress.
     */
    public function syncStatus(SyncRun $run)
    {
        return response()->json([
            'id' => $run->id,
            'type' => $run->type,
            'status' => $run->status,
            'current_step' => $run->current_step,
            'current_step_label' => $run->current_step_label,
            'progress_percent' => $run->progress_percent,
            'message' => $run->message,
            'errors' => $run->errors,
            'stats' => $run->stats,
            'is_terminal' => $run->isTerminal(),
            'started_at' => $run->started_at?->toIso8601String(),
            'completed_at' => $run->completed_at?->toIso8601String(),
        ]);
    }
}
