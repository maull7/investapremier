<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentManager;
use App\Models\InvestmentManagerPeriod;
use App\Models\ReksaDana;
use App\Models\ReksaDanaDocument;
use App\Exports\InvestmentManagerTemplateExport;
use App\Imports\InvestmentManagerImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class InvestmentManagerController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;
        $query = InvestmentManager::with('periods');

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('kode_mi', 'like', "%{$s}%")
                  ->orWhere('kode_ojk', 'like', "%{$s}%");
            });
        }

        if ($request->filled('mata_uang')) {
            $query->whereHas('periods', function ($q) use ($request) {
                $q->where('mata_uang', $request->mata_uang);
            });
        }

        if ($request->filled('tahun')) {
            $query->whereHas('periods', function ($q) use ($request) {
                $q->where('tahun', $request->tahun);
            });
        }

        if ($request->filled('kuartal')) {
            $query->whereHas('periods', function ($q) use ($request) {
                $q->where('kuartal', $request->kuartal);
            });
        }

        $managers = $query->orderBy('name')->paginate($perPage)->withQueryString();

        $tahunList = InvestmentManagerPeriod::select('tahun')->distinct()->whereNotNull('tahun')->orderBy('tahun', 'desc')->pluck('tahun');

        return view('admin.investment-managers.index', compact('managers', 'perPage', 'tahunList'));
    }

    public function show($id)
    {
        $manager = InvestmentManager::with('periods')->findOrFail($id);
        $manager->load('funds');

        $chartPeriods = $manager->periods()
            ->when(request('chart_tahun'), fn($q, $v) => $q->where('tahun', $v))
            ->when(request('chart_kuartal'), fn($q, $v) => $q->where('kuartal', $v))
            ->when(request('chart_mata_uang'), fn($q, $v) => $q->where('mata_uang', $v))
            ->orderBy('period_date')
            ->get();

        $chartLabels = $chartPeriods->pluck('period_date')->map(fn($d) => $d->format('M Y'));
        $chartAum = $chartPeriods->pluck('aum');
        $chartUp = $chartPeriods->pluck('up');

        $tahunList = InvestmentManagerPeriod::select('tahun')->distinct()->whereNotNull('tahun')->orderBy('tahun', 'desc')->pluck('tahun');

        // Semua reksa dana yang punya prospektus (global, tidak difilter per MI)
        $fundsWithProspektus = ReksaDana::whereHas('documents', fn($q) => $q->where('document_type', 'prospektus'))
            ->with(['documents' => fn($q) => $q->where('document_type', 'prospektus')->orderByDesc('ffs_year')])
            ->orderBy('nama_reksa_dana')
            ->get();

        return view('admin.investment-managers.show', compact(
            'manager', 'chartPeriods', 'chartLabels', 'chartAum', 'chartUp', 'tahunList',
            'fundsWithProspektus'
        ));
    }

    public function extractProspektus(Request $request, InvestmentManager $investmentManager)
    {
        $request->validate([
            'reksa_dana_id' => 'required|integer|exists:reksa_dana,id',
            'tahun'         => 'required|integer',
        ]);

        $doc = ReksaDanaDocument::where('reksa_dana_id', $request->reksa_dana_id)
            ->where('document_type', 'prospektus')
            ->where('ffs_year', $request->tahun)
            ->first();

        if (!$doc || !Storage::disk('public')->exists($doc->file_path)) {
            return response()->json(['error' => 'Dokumen prospektus tidak ditemukan.'], 404);
        }

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile(Storage::disk('public')->path($doc->file_path));
            $text = $pdf->getText();

            $data = $this->parseProspektusText($text);

            return response()->json(['success' => true, 'data' => $data, 'raw_preview' => substr($text, 0, 2000)]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Gagal membaca PDF: ' . $e->getMessage()], 500);
        }
    }

    public function saveProspektus(Request $request, InvestmentManager $investmentManager)
    {
        $validated = $request->validate([
            'address'                => 'nullable|string|max:500',
            'phone'                  => 'nullable|string|max:100',
            'email'                  => 'nullable|email|max:255',
            'website'                => 'nullable|url|max:255',
            'commissioner_president' => 'nullable|string|max:255',
            'commissioners'          => 'nullable|string',
            'director_president'     => 'nullable|string|max:255',
            'directors'              => 'nullable|string',
            'shareholders'           => 'nullable|string',
            'description'            => 'nullable|string',
        ]);

        $investmentManager->update(array_filter($validated, fn($v) => $v !== null && $v !== ''));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.investment-managers.show', $investmentManager)
            ->with('success', 'Data prospektus berhasil disimpan.');
    }

    private function parseProspektusText(string $text): array
    {
        $data = [
            'address'                => null,
            'phone'                  => null,
            'email'                  => null,
            'website'                => null,
            'commissioner_president' => null,
            'commissioners'          => null,
            'director_president'     => null,
            'directors'              => null,
            'shareholders'           => null,
            'description'            => null,
        ];

        // Alamat
        if (preg_match('/(?:Alamat|Berkedudukan di|Beralamat di)[:\s]+(.+?)(?:\n|Telepon|Tel\.|Fax|Email)/si', $text, $m)) {
            $data['address'] = trim($m[1]);
        }

        // Telepon
        if (preg_match('/(?:Telepon|Tel\.?|Phone)[:\s]+([\d\s\-\+\(\)]+)/i', $text, $m)) {
            $data['phone'] = trim($m[1]);
        }

        // Email
        if (preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $text, $m)) {
            $data['email'] = trim($m[0]);
        }

        // Website
        if (preg_match('/(?:www\.|https?:\/\/)[a-zA-Z0-9.\-\/]+/i', $text, $m)) {
            $data['website'] = trim($m[0]);
            if (!preg_match('/^https?:\/\//', $data['website'])) {
                $data['website'] = 'https://' . $data['website'];
            }
        }

        // Komisaris Utama
        if (preg_match('/Komisaris Utama[:\s]+([^\n,;]+)/i', $text, $m)) {
            $data['commissioner_president'] = trim($m[1]);
        }

        // Komisaris (semua komisaris non-utama)
        if (preg_match_all('/Komisaris(?:\s+Independen)?[:\s]+([^\n,;]+)/i', $text, $ms)) {
            $data['commissioners'] = implode("\n", array_map('trim', $ms[1]));
        }

        // Direktur Utama
        if (preg_match('/Direktur Utama[:\s]+([^\n,;]+)/i', $text, $m)) {
            $data['director_president'] = trim($m[1]);
        }

        // Direktur
        if (preg_match_all('/Direktur(?!\s+Utama)[:\s]+([^\n,;]+)/i', $text, $ms)) {
            $data['directors'] = implode("\n", array_map('trim', $ms[1]));
        }

        // Pemegang Saham
        if (preg_match('/(?:Pemegang Saham|Komposisi Saham)[:\s\n]+(.+?)(?:\n\n|\d{4}|Dewan|Direksi)/si', $text, $m)) {
            $data['shareholders'] = trim($m[1]);
        }

        // Deskripsi (kalimat pertama setelah "Manajer Investasi" atau "Pengelolaan Investasi")
        if (preg_match('/(?:Manajer Investasi|Pengelolaan Investasi)[^\n]*\n([^\n]{40,})/i', $text, $m)) {
            $data['description'] = trim($m[1]);
        }

        return array_map(fn($v) => $v ? mb_substr(trim($v), 0, 500) : null, $data);
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
