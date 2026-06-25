<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncInvestmentManagerFromPasardanaJob;
use App\Jobs\SyncInvestmentManagerPeriodsJob;
use App\Models\InvestmentManager;
use App\Models\InvestmentManagerPeriod;
use App\Models\InvestmentManagerProspektus;
use App\Models\ReksaDana;
use App\Models\ReksaDanaDocument;
use App\Models\DocumentPartition;
use App\Models\SyncRun;
use App\Exports\InvestmentManagerTemplateExport;
use App\Imports\InvestmentManagerImport;
use App\Services\GroqService;
use App\Services\InvestmentPersonService;
use App\Services\ReksaDanaChartDataService;
use App\Services\DocumentDataExtractorService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\ActivityLogger;
use Smalot\PdfParser\Parser;

class InvestmentManagerController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'daftar');
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

        $recentSyncRuns = SyncRun::where('type', SyncRun::TYPE_MI_PASARDANA)->latest()->paginate(15, ['*'], 'runs_page');
        $selectedRunId = $request->integer('selected_run') ?: $recentSyncRuns->first()?->id;
        $selectedRun = $selectedRunId ? SyncRun::find($selectedRunId) : null;
        $changesUrl = $selectedRun ? route('admin.investment-managers.sync-pasardana.changes', $selectedRun) : null;
        $detailTypes = [];

        $lastSyncRun = SyncRun::where('type', SyncRun::TYPE_MI_PASARDANA)
            ->where('status', SyncRun::STATUS_COMPLETED)
            ->latest()
            ->first();

        return view('admin.investment-managers.index', compact('tab', 'managers', 'perPage', 'tahunList', 'recentSyncRuns', 'selectedRun', 'changesUrl', 'detailTypes', 'lastSyncRun'));
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
            ->with(['documents' => fn($q) => $q->where('document_type', 'prospektus')->with(['parsedPages', 'partitions'])->orderByDesc('ffs_year')])
            ->orderBy('nama_reksa_dana')
            ->get();

        // Reksa dana yang dikelola MI ini dan punya prospektus
        $managerFundsWithProspektus = ReksaDana::where('nama_manajer_investasi', $manager->name)
            ->whereHas('documents', fn($q) => $q->where('document_type', 'prospektus'))
            ->with(['documents' => fn($q) => $q->where('document_type', 'prospektus')->with(['parsedPages', 'partitions'])->orderByDesc('ffs_year')])
            ->orderBy('nama_reksa_dana')
            ->get();

        $prospektusHistory = InvestmentManagerProspektus::where('investment_manager_id', $manager->id)
            ->with('reksaDana')
            ->orderBy('tahun')
            ->orderBy('created_at')
            ->get();

        return view('admin.investment-managers.show', compact(
            'manager', 'fundsWithProspektus', 'managerFundsWithProspektus', 'range', 'chartData', 'governanceSections',
            'pasardanaGovernance', 'prospektusHistory',
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

    public function extractProspektus(Request $request, InvestmentManager $investmentManager, GroqService $groq)
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

            $data = $request->boolean('use_ai')
                ? $groq->parseProspektusPdf($text)
                : $this->parseProspektusText($text);

            return response()->json([
                'success'     => true,
                'data'        => $data,
                'ai_used'     => $request->boolean('use_ai'),
                'raw_preview' => substr($text, 0, 2000),
            ]);
        } catch (\Throwable $e) {
            // Fallback: if AI fails, try regex
            if ($request->boolean('use_ai')) {
                try {
                    $parser = new Parser();
                    $pdf = $parser->parseFile(Storage::disk('public')->path($doc->file_path));
                    $text = $pdf->getText();
                    $data = $this->parseProspektusText($text);

                    return response()->json([
                        'success'     => true,
                        'data'        => $data,
                        'ai_used'     => false,
                        'ai_error'    => $e->getMessage(),
                        'raw_preview' => substr($text, 0, 2000),
                    ]);
                } catch (\Throwable $e2) {
                    return response()->json(['error' => 'Gagal memproses prospektus: ' . $e2->getMessage()], 500);
                }
            }

            return response()->json(['error' => 'Gagal membaca PDF: ' . $e->getMessage()], 500);
        }
    }

    public function saveProspektus(Request $request, InvestmentManager $investmentManager, InvestmentPersonService $personService)
    {
        $validated = $request->validate([
            'address'                => 'nullable|string|max:500',
            'phone'                  => 'nullable|string|max:100',
            'email'                  => 'nullable|string|max:255',
            'website'                => 'nullable|string|max:255',
            'commissioner_president' => 'nullable|string|max:255',
            'commissioners'          => 'nullable|string',
            'director_president'     => 'nullable|string|max:255',
            'directors'              => 'nullable|string',
            'shareholders'           => 'nullable|string',
            'investment_committee'    => 'nullable|string',
            'investment_management_team' => 'nullable|string',
            'description'            => 'nullable|string',
            'reksa_dana_id'          => 'nullable|integer|exists:reksa_dana,id',
            'tahun'                  => 'nullable|integer',
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

        // Source tracking
        $update['source'] = 'prospektus';
        $update['last_updated_at'] = now()->toDateString();
        if ($request->filled('reksa_dana_id')) {
            $update['prospektus_source_reksa_dana_id'] = $request->integer('reksa_dana_id');
        }
        if ($request->filled('tahun')) {
            $update['prospektus_source_tahun'] = $request->integer('tahun');
        }

        // Preserve history (raw extraction per year)
        $historyData = collect($validated)->except('reksa_dana_id', 'tahun')->filter()->toArray();
        if (!empty($historyData)) {
            InvestmentManagerProspektus::create([
                'investment_manager_id' => $investmentManager->id,
                'reksa_dana_id'         => $request->integer('reksa_dana_id') ?: $investmentManager->prospektus_source_reksa_dana_id,
                'tahun'                 => $request->integer('tahun') ?: $investmentManager->prospektus_source_tahun,
                'data'                  => $historyData,
            ]);
        }

        $investmentManager->update($update);
        $personService->syncInvestmentManager($investmentManager->refresh(), 'prospektus');

        ActivityLogger::log(
            'Menyimpan Prospektus',
            "Prospektus untuk {$investmentManager->name} berhasil disimpan (sumber: reksa_dana_id={$request->reksa_dana_id}, tahun={$request->tahun})",
            'success',
            $investmentManager,
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.investment-managers.show', $investmentManager)
            ->with('success', 'Data prospektus berhasil disimpan.');
    }

    public function extractFromPartition(Request $request, InvestmentManager $investmentManager, DocumentDataExtractorService $extractor, InvestmentPersonService $personService)
    {
        $validated = $request->validate([
            'document_id'  => 'required|exists:reksa_dana_documents,id',
            'partition_id' => 'required|exists:document_partitions,id',
            'tahun'        => 'required|integer|min:2000|max:2100',
        ]);

        $document = ReksaDanaDocument::findOrFail($validated['document_id']);
        $partition = DocumentPartition::findOrFail($validated['partition_id']);

        try {
            $data = $extractor->extractInvestmentManagerData($document, $partition);

            $update = array_filter([
                'address'                => $data['alamat'] ?? null,
                'phone'                  => $data['telepon'] ?? null,
                'email'                  => $data['email'] ?? null,
                'website'                => $data['website'] ?? null,
                'description'            => $data['deskripsi'] ?? null,
            ], fn($v) => $v !== null && $v !== '');

            if (!empty($data['website']) && !preg_match('/^https?:\/\//i', $data['website'])) {
                $update['website'] = 'https://' . $data['website'];
            }

            if (!empty($data['tim_pengelola']) && is_array($data['tim_pengelola'])) {
                $teamText = collect($data['tim_pengelola'])
                    ->map(fn($t) => trim(($t['nama'] ?? '') . ($t['jabatan'] ?? '' ? ' - ' . ($t['jabatan'] ?? '') : '')))
                    ->filter()
                    ->implode("\n");
                if ($teamText) {
                    $update['investment_management_team'] = $teamText;
                }
            }

            $update['source'] = 'prospektus';
            $update['last_updated_at'] = now()->toDateString();
            if ($document->ffs_year) {
                $update['prospektus_source_tahun'] = $document->ffs_year;
            }

            if (!empty($update)) {
                $investmentManager->update($update);
                $personService->syncInvestmentManager($investmentManager->refresh(), 'prospektus');
            }

            $historyData = $data;
            if (!empty($historyData)) {
                InvestmentManagerProspektus::create([
                    'investment_manager_id' => $investmentManager->id,
                    'reksa_dana_id'         => $document->reksa_dana_id,
                    'tahun'                 => $validated['tahun'],
                    'data'                  => $historyData,
                ]);
            }

            ActivityLogger::log(
                'Ekstrak dari Partisi',
                "Data dari partisi '{$partition->nama_partisi}' dokument {$document->original_name} berhasil disimpan ke {$investmentManager->name}",
                'success',
                $investmentManager,
            );

            return response()->json([
                'success'       => true,
                'message'       => 'Data berhasil diekstrak dan disimpan.',
                'data'          => $data,
                'saved_fields'  => array_keys($update),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Gagal mengekstrak: ' . $e->getMessage()], 500);
        }
    }

    public function extractProspektusData(Request $request, InvestmentManager $investmentManager, DocumentDataExtractorService $extractor, InvestmentPersonService $personService)
    {
        $validated = $request->validate([
            'document_id'   => 'required|exists:reksa_dana_documents,id',
            'partition_ids' => 'required|array|min:1',
            'partition_ids.*' => 'integer|exists:document_partitions,id',
            'tahun'         => 'nullable|integer|min:2000|max:2100',
        ]);

        $document = ReksaDanaDocument::findOrFail($validated['document_id']);
        $partitions = DocumentPartition::where('reksa_dana_document_id', $document->id)
            ->whereIn('id', $validated['partition_ids'])
            ->orderBy('start_page')
            ->get();

        try {
            $data = $extractor->extractInvestmentManagerDataFromPartitions($document, $partitions->all());

            $update = array_filter([
                'address'     => $data['alamat'] ?? null,
                'phone'       => $data['telepon'] ?? null,
                'email'       => $data['email'] ?? null,
                'website'     => $data['website'] ?? null,
                'description' => $data['deskripsi'] ?? null,
            ], fn($v) => $v !== null && $v !== '');

            if (!empty($data['website']) && !preg_match('/^https?:\/\//i', $data['website'])) {
                $update['website'] = 'https://' . $data['website'];
            }

            if (!empty($data['tim_pengelola']) && is_array($data['tim_pengelola'])) {
                $teamText = collect($data['tim_pengelola'])
                    ->map(fn($t) => trim(($t['nama'] ?? '') . ($t['jabatan'] ?? '' ? ' - ' . ($t['jabatan'] ?? '') : '')))
                    ->filter()
                    ->implode("\n");
                if ($teamText) {
                    $update['investment_management_team'] = $teamText;
                }
            }

            $update['source'] = 'prospektus';
            $update['last_updated_at'] = now()->toDateString();
            if ($document->ffs_year) {
                $update['prospektus_source_tahun'] = $document->ffs_year;
            }
            $update['prospektus_source_reksa_dana_id'] = $document->reksa_dana_id;

            if (!empty($update)) {
                $investmentManager->update($update);
                $personService->syncInvestmentManager($investmentManager->refresh(), 'prospektus');
            }

            $historyData = $data;
            if (!empty($historyData)) {
                InvestmentManagerProspektus::create([
                    'investment_manager_id' => $investmentManager->id,
                    'reksa_dana_id'         => $document->reksa_dana_id,
                    'tahun'                 => $validated['tahun'] ?? $document->ffs_year,
                    'data'                  => $historyData,
                ]);
            }

            ActivityLogger::log(
                'Parse Prospektus ke MI',
                "Data dari {$partitions->count()} partisi dokumen {$document->original_name} berhasil disimpan ke {$investmentManager->name}",
                'success',
                $investmentManager,
            );

            return response()->json([
                'success'      => true,
                'message'      => 'Data Manajer Investasi berhasil diekstrak dan disimpan.',
                'data'         => $data,
                'saved_fields' => array_keys($update),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Gagal mengekstrak: ' . $e->getMessage()], 500);
        }
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
        if (empty(config('services.backend_sync.url'))) {
            $msg = 'Fitur sync Pasardana dinonaktifkan (BACKEND_SYNC_URL tidak dikonfigurasi).';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $msg], 503);
            }
            return redirect()->route('admin.investment-managers.index')->with('error', $msg);
        }

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
            ->with('success', 'Sync MI + Relasi dari Pasardana dimulai.');
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

    public function syncChanges(SyncRun $run, \Illuminate\Http\Request $request)
    {
        $query = \App\Models\SyncChangeLog::where('sync_run_id', $run->id);

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        $changes = $query->orderBy('created_at')->orderBy('id')
            ->paginate($request->per_page ?? 50);

        return response()->json($changes);
    }
}
