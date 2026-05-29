<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ObligasiHargaReferensi;
use App\Models\ObligasiBond;
use App\Exports\ObligasiHargaReferensiTemplateExport;
use App\Exports\ObligasiBondTemplateExport;
use App\Imports\ObligasiHargaReferensiImport;
use App\Imports\ObligasiBondImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ObligasiController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'harga-referensi');
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;

        $hargaReferensi = collect();
        $bonds = collect();

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

        return view('admin.obligasi.index', compact('tab', 'perPage', 'hargaReferensi', 'bonds'));
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

        ObligasiHargaReferensi::create($data);

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

        return redirect()->route('admin.obligasi.index', ['tab' => 'harga-referensi'])
            ->with('success', 'Obligasi harga referensi berhasil diperbarui.');
    }

    public function destroyHargaReferensi(ObligasiHargaReferensi $obligasiHargaReferensi)
    {
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

        ObligasiBond::updateOrCreate(
            ['kode' => $data['kode'], 'periode' => $data['periode']],
            $data
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

        return redirect()->route('admin.obligasi.index', ['tab' => 'bond'])
            ->with('success', 'Keuangan emiten berhasil diperbarui.');
    }

    public function destroyBond(ObligasiBond $obligasiBond)
    {
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
        return redirect()->route('admin.obligasi.index', ['tab' => 'harga-referensi'])
            ->with('success', "{$import->imported} data obligasi harga referensi berhasil diimport.");
    }

    public function importBond(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);
        $import = new ObligasiBondImport;
        Excel::import($import, $request->file('file'));
        return redirect()->route('admin.obligasi.index', ['tab' => 'bond'])
            ->with('success', "{$import->imported} data keuangan emiten berhasil diimport.");
    }
}
