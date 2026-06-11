<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncInvestmentManagerFromPasardanaJob;
use App\Jobs\SyncInvestmentManagerPeriodsJob;
use App\Models\InvestmentManager;
use App\Models\InvestmentManagerPeriod;
use App\Models\ReksaDana;
use App\Models\ReksaDanaDocument;
use App\Models\SyncRun;
use App\Exports\InvestmentManagerTemplateExport;
use App\Imports\InvestmentManagerImport;
use App\Services\InvestmentPersonService;
use App\Services\ReksaDanaChartDataService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\ActivityLogger;
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

    public function show($id, ReksaDanaChartDataService $chartDataService, InvestmentPersonService $personService)
    {
        $manager = InvestmentManager::with('periods')->findOrFail($id);
        $manager->load('funds');
        $governanceSections = $personService->sectionsForManager($manager);

        // Pasardana governance data (stored as JSON in text columns)
        $pasardanaGovernance = [];
        foreach (['directors', 'commissioners', 'shareholders'] as $field) {
            $value = $manager->{$field};
            if (filled($value) && $this->isJson($value)) {
                $items = json_decode($value, true);
                $pasardanaGovernance[$field] = collect($items)->map(fn($i) => [
                    'name' => $i['nama'] ?? $i['name'] ?? '-',
                    'position' => $i['jabatan'] ?? $i['position'] ?? $i['type'] ?? '-',
                ])->toArray();
            }
        }

        $range = request('range', '1y');
        $chartData = $chartDataService->forManager(
            $manager,
            $range,
            request('from_date'),
            request('to_date')
        );

        // Semua reksa dana yang punya prospektus (global, tidak difilter per MI)
        $fundsWithProspektus = ReksaDana::whereHas('documents', fn($q) => $q->where('document_type', 'prospektus'))
            ->with(['documents' => fn($q) => $q->where('document_type', 'prospektus')->orderByDesc('ffs_year')])
            ->orderBy('nama_reksa_dana')
            ->get();

        return view('admin.investment-managers.show', compact(
            'manager', 'fundsWithProspektus', 'range', 'chartData', 'governanceSections',
            'pasardanaGovernance',
        ));
    }

    private function isJson(string $value): bool
    {
        if (empty($value)) return false;
        $first = trim($value)[0] ?? '';
        if ($first !== '[' && $first !== '{') return false;
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
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

    public function saveProspektus(Request $request, InvestmentManager $investmentManager, InvestmentPersonService $personService)
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
            'investment_committee'    => 'nullable|string',
            'investment_management_team' => 'nullable|string',
            'description'            => 'nullable|string',
        ]);

        $update = array_filter($validated, fn($v) => $v !== null && $v !== '');
        foreach ([
            'commissioners',
            'directors',
            'shareholders',
            'investment_committee',
            'investment_management_team',
        ] as $field) {
            if (array_key_exists($field, $update)) {
                $update[$field] = $this->mergePeopleText($investmentManager->{$field}, $update[$field], $personService);
            }
        }

        $investmentManager->update($update);
        $personService->syncInvestmentManager($investmentManager->refresh(), 'prospektus');

        ActivityLogger::log(
            'Menyimpan Prospektus',
            "Prospektus untuk {$investmentManager->name} berhasil disimpan",
            'success',
            $investmentManager,
        );

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
            'investment_committee'    => null,
            'investment_management_team' => null,
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

        if (preg_match('/(?:Komite Investasi|Investment Committee)[:\s\n]+(.+?)(?:\n\n|Tim Pengelola|Pengelola Investasi|Dewan|Direksi|BAB\s+[IVX]+)/si', $text, $m)) {
            $data['investment_committee'] = trim($m[1]);
        }

        if (preg_match('/(?:Tim Pengelola Investasi|Pengelola Investasi|Investment Management Team|Portfolio Manager)[:\s\n]+(.+?)(?:\n\n|Komite Investasi|Dewan|Direksi|BAB\s+[IVX]+)/si', $text, $m)) {
            $data['investment_management_team'] = trim($m[1]);
        }

        // Deskripsi (kalimat pertama setelah "Manajer Investasi" atau "Pengelolaan Investasi")
        if (preg_match('/(?:Manajer Investasi|Pengelolaan Investasi)[^\n]*\n([^\n]{40,})/i', $text, $m)) {
            $data['description'] = trim($m[1]);
        }

        return array_map(fn($v) => $v ? mb_substr(trim($v), 0, 2000) : null, $data);
    }

    private function mergePeopleText(?string $old, string $new, InvestmentPersonService $personService): string
    {
        $items = collect($personService->parsePeople($old))
            ->merge($personService->parsePeople($new))
            ->unique(fn ($item) => $personService->normalizeName($item['name']))
            ->map(fn ($item) => trim($item['name'] . ($item['position'] ? ' - ' . $item['position'] : '')))
            ->values();

        return $items->implode("\n");
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

        $manager = InvestmentManager::create($request->only('name', 'kode_mi'));

        ActivityLogger::log(
            'Membuat Manajer Investasi',
            "Manajer investasi {$manager->name} berhasil ditambahkan",
            'success',
            $manager,
        );

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

        ActivityLogger::log(
            'Memperbarui Manajer Investasi',
            "Manajer investasi {$investmentManager->name} berhasil diperbarui",
            'success',
            $investmentManager,
        );

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
        ActivityLogger::log(
            'Menghapus Manajer Investasi',
            "Manajer investasi {$investmentManager->name} berhasil dihapus",
            'success',
            $investmentManager,
        );

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

        ActivityLogger::log(
            'Import Manajer Investasi',
            "{$import->imported} data manajer investasi berhasil diimport",
            'success',
        );

        return redirect()->route('admin.investment-managers.index')
            ->with('success', "{$import->imported} data manajer investasi berhasil diimport.");
    }

    public function destroyPeriod(InvestmentManagerPeriod $investmentManagerPeriod)
    {
        ActivityLogger::log(
            'Menghapus Periode Manajer Investasi',
            "Periode manajer investasi berhasil dihapus",
            'success',
            $investmentManagerPeriod,
        );

        $investmentManagerPeriod->delete();
        return redirect()->route('admin.investment-managers.index')
            ->with('success', 'Periode berhasil dihapus.');
    }

    public function syncFromPasardana(Request $request)
    {
        $inflight = SyncRun::where('type', SyncRun::TYPE_MI_PASARDANA)
            ->whereIn('status', [SyncRun::STATUS_QUEUED, SyncRun::STATUS_RUNNING])
            ->where('updated_at', '>=', now()->subMinutes(10))
            ->latest()
            ->first();

        if ($inflight) {
            $payload = [
                'run_id' => $inflight->id,
                'status' => $inflight->status,
                'reused' => true,
            ];
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($payload);
            }
            return redirect()->route('admin.investment-managers.index')
                ->with('sync_run_id', $inflight->id);
        }

        $run = SyncRun::create([
            'type' => SyncRun::TYPE_MI_PASARDANA,
            'status' => SyncRun::STATUS_QUEUED,
            'current_step' => 'queued',
            'current_step_label' => 'Menunggu worker mengambil job dari antrian',
            'progress_percent' => 0,
            'user_id' => $request->user()?->id,
        ]);

        SyncInvestmentManagerFromPasardanaJob::dispatch($run->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'run_id' => $run->id,
                'status' => $run->status,
            ]);
        }

        return redirect()->route('admin.investment-managers.index')
            ->with('sync_run_id', $run->id)
            ->with('success', 'Sync MI dari Pasardana dimulai.');
    }

    public function syncPeriods(Request $request)
    {
        $inflight = SyncRun::where('type', SyncRun::TYPE_MI_PERIOD)
            ->whereIn('status', [SyncRun::STATUS_QUEUED, SyncRun::STATUS_RUNNING])
            ->where('updated_at', '>=', now()->subMinutes(10))
            ->latest()
            ->first();

        if ($inflight) {
            $payload = [
                'run_id' => $inflight->id,
                'status' => $inflight->status,
                'reused' => true,
            ];
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($payload);
            }
            return redirect()->route('admin.investment-managers.index')
                ->with('sync_run_id', $inflight->id);
        }

        $run = SyncRun::create([
            'type' => SyncRun::TYPE_MI_PERIOD,
            'status' => SyncRun::STATUS_QUEUED,
            'current_step' => 'queued',
            'current_step_label' => 'Menunggu worker mengambil job dari antrian',
            'progress_percent' => 0,
            'user_id' => $request->user()?->id,
        ]);

        SyncInvestmentManagerPeriodsJob::dispatch($run->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'run_id' => $run->id,
                'status' => $run->status,
            ]);
        }

        return redirect()->route('admin.investment-managers.index')
            ->with('sync_run_id', $run->id)
            ->with('success', 'Sync periode AUM MI dari data harian dimulai.');
    }

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
