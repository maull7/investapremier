<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentManager;
use App\Models\InvestmentManagerPeriod;
use App\Exports\InvestmentManagerTemplateExport;
use App\Imports\InvestmentManagerImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class InvestmentManagerController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;
        $query = InvestmentManager::with('periods');

        if ($request->search) {
            $s = $request->search;
            $query->where('name', 'like', "%{$s}%");
        }

        $managers = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return view('admin.investment-managers.index', compact('managers', 'perPage'));
    }

    public function create()
    {
        return view('admin.investment-managers.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255|unique:investment_managers,name',
            'kode_mi' => 'nullable|string|max:10|unique:investment_managers,kode_mi',
        ]);

        InvestmentManager::create($request->only('name', 'kode_mi'));

        return redirect()->route('admin.investment-managers.index')
            ->with('success', 'Manajer investasi berhasil ditambahkan.');
    }

    public function edit(InvestmentManager $investmentManager)
    {
        $investmentManager->load('periods');
        return view('admin.investment-managers.form', ['manager' => $investmentManager]);
    }

    public function update(Request $request, InvestmentManager $investmentManager)
    {
        $request->validate([
            'name'    => 'required|string|max:255|unique:investment_managers,name,' . $investmentManager->id,
            'kode_mi' => 'nullable|string|max:10|unique:investment_managers,kode_mi,' . $investmentManager->id,
        ]);

        $investmentManager->update($request->only('name', 'kode_mi'));

        $periods = $request->input('periods', []);
        foreach ($periods as $periodId => $data) {
            if (isset($data['_delete']) && $data['_delete']) {
                InvestmentManagerPeriod::where('id', $periodId)->where('investment_manager_id', $investmentManager->id)->delete();
                continue;
            }
            if (!empty($data['period_date'])) {
                InvestmentManagerPeriod::updateOrCreate(
                    [
                        'id' => is_numeric($periodId) ? $periodId : null,
                        'investment_manager_id' => $investmentManager->id,
                    ],
                    [
                        'period_date' => $data['period_date'],
                        'aum' => $data['aum'] ?? null,
                        'up' => $data['up'] ?? null,
                    ]
                );
            }
        }

        return redirect()->route('admin.investment-managers.index')
            ->with('success', 'Manajer investasi berhasil diperbarui.');
    }

    public function destroy(InvestmentManager $investmentManager)
    {
        $investmentManager->delete();
        return redirect()->route('admin.investment-managers.index')
            ->with('success', 'Manajer investasi berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new InvestmentManagerTemplateExport, 'template-manajer-investasi.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);

        $import = new InvestmentManagerImport;
        Excel::import($import, $request->file('file'));

        return redirect()->route('admin.investment-managers.index')
            ->with('success', "{$import->imported} data manajer investasi berhasil diimport.");
    }

    public function destroyPeriod(InvestmentManagerPeriod $investmentManagerPeriod)
    {
        $investmentManagerPeriod->delete();
        return redirect()->route('admin.investment-managers.index')
            ->with('success', 'Periode berhasil dihapus.');
    }
}
