<?php

namespace App\Http\Controllers;

use App\Exports\AnalisaTemplateExport;
use App\Exports\AnalisaExcelExport;
use App\Imports\AnalisaImport;
use App\Imports\AnalisaImportPreview;
use App\Imports\LegacyFormatReader;
use App\Jobs\AnalisaAiJob;
use App\Models\AnalisaReksaDana;
use App\Models\DataSourceLink;
use App\Models\ReksaDana;
use App\Models\ReksaDanaDocument;
use App\Models\FfsExtractionResult;
use App\Models\StockPrice;
use App\Services\AnalisaPayloadBuilder;
use App\Services\BankDataService;
use App\Services\BondMarketService;
use App\Services\DataSourceAutoDownloadService;
use App\Services\FfsParserService;
use App\Services\IdxMarketService;
use App\Services\AiTableService;
use App\Services\PageClassifierService;
use App\Services\ProspektusPipelineService;
use App\Services\ProspektusValidator;
use App\Services\WebDataFileParserService;
use App\Services\WebScraperService;
use App\Services\AnalisaAiValidator;
use App\Services\GroqService;
use App\Jobs\AnalisaAiPlusJob;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class AnalisaController extends Controller
{
    protected bool $isAdminContext = false;
    protected string $productType = 'reksa_dana';
    protected string $productLabel = 'Reksa Dana';

    protected function indexRoute(): string
    {
        if ($this->productType === 'unit_link') {
            return $this->isAdminContext ? 'admin.unit-link-ffs.index' : 'user.unit-link-analisa.index';
        }
        return $this->isAdminContext ? 'admin.reksa-dana.index' : 'user.analisa.index';
    }

    protected function formRoutes(): array
    {
        $prefix = match (true) {
            $this->isAdminContext && $this->productType === 'unit_link' => 'admin.analisa-ul',
            $this->isAdminContext                                        => 'admin.analisa-rd',
            $this->productType === 'unit_link'                          => 'user.unit-link-analisa',
            default                                                      => 'user.analisa',
        };

        $cancelRoute = match (true) {
            $this->isAdminContext && $this->productType === 'unit_link' => route('admin.unit-link-ffs.index'),
            $this->isAdminContext                                        => route('admin.reksa-dana.index'),
            $this->productType === 'unit_link'                          => route('user.unit-link-analisa.index'),
            default                                                      => route('user.analisa.index'),
        };

        $scrapeWebBase = match (true) {
            $this->isAdminContext && $this->productType === 'unit_link' => url('admin/analisa-ul/scrape-web-data'),
            $this->isAdminContext                                        => url('admin/analisa-rd/scrape-web-data'),
            $this->productType === 'unit_link'                          => url('user/unit-link-analisa/scrape-web-data'),
            default                                                      => url('user/analisa/scrape-web-data'),
        };

        $scrapeUrlBase = match (true) {
            $this->isAdminContext && $this->productType === 'unit_link' => url('admin/analisa-ul/scrape-url'),
            $this->isAdminContext                                        => url('admin/analisa-rd/scrape-url'),
            $this->productType === 'unit_link'                          => url('user/unit-link-analisa/scrape-url'),
            default                                                      => url('user/analisa/scrape-url'),
        };

        return array_merge([
            'layout'          => $this->isAdminContext ? 'layouts.admin' : 'layouts.user',
            'store'           => route("{$prefix}.store"),
            'template'        => route("{$prefix}.template"),
            'cancel'          => $cancelRoute,
            'parse_pdf'              => route("{$prefix}.parse-pdf"),
            'parse_pdf_vision'      => \Illuminate\Support\Facades\Route::has("{$prefix}.parse-pdf-vision") ? route("{$prefix}.parse-pdf-vision") : null,
            'parse_prospektus_pdf'  => \Illuminate\Support\Facades\Route::has("{$prefix}.parse-prospektus-pdf") ? route("{$prefix}.parse-prospektus-pdf") : null,
            'preview_ai'      => route("{$prefix}.preview-ai"),
            'preview_ai_plus' => route("{$prefix}.preview-ai-plus"),
            'lookup_kode'     => \Illuminate\Support\Facades\Route::has("{$prefix}.lookup-kode") ? route("{$prefix}.lookup-kode") : null,
            'existing_documents' => \Illuminate\Support\Facades\Route::has("{$prefix}.existing-documents") ? route("{$prefix}.existing-documents") : null,
            'parse_existing_document' => \Illuminate\Support\Facades\Route::has("{$prefix}.parse-existing-document") ? route("{$prefix}.parse-existing-document") : null,
            'lookup_sektor'   => \Illuminate\Support\Facades\Route::has("{$prefix}.lookup-sektor") ? route("{$prefix}.lookup-sektor") : null,
            'lookup_ihsg'     => \Illuminate\Support\Facades\Route::has("{$prefix}.lookup-ihsg") ? route("{$prefix}.lookup-ihsg") : null,
            'lookup_return'   => \Illuminate\Support\Facades\Route::has("{$prefix}.lookup-return") ? route("{$prefix}.lookup-return") : null,
            'lookup_bond_return' => \Illuminate\Support\Facades\Route::has("{$prefix}.lookup-bond-return") ? route("{$prefix}.lookup-bond-return") : null,
            'lookup_bank_data' => \Illuminate\Support\Facades\Route::has("{$prefix}.lookup-bank-data") ? route("{$prefix}.lookup-bank-data") : null,
            'lookup_sukuk_return' => \Illuminate\Support\Facades\Route::has("{$prefix}.lookup-sukuk-return") ? route("{$prefix}.lookup-sukuk-return") : null,
            'lookup_kode_efek' => \Illuminate\Support\Facades\Route::has("{$prefix}.lookup-kode-efek") ? route("{$prefix}.lookup-kode-efek") : null,
            'lookup_period_data' => \Illuminate\Support\Facades\Route::has("{$prefix}.lookup-period-data") ? route("{$prefix}.lookup-period-data") : null,
            'get_financial_data' => \Illuminate\Support\Facades\Route::has("{$prefix}.get-financial-data") ? route("{$prefix}.get-financial-data") : null,
            'lookup_nav_history' => \Illuminate\Support\Facades\Route::has("{$prefix}.lookup-nav-history") ? route("{$prefix}.lookup-nav-history") : null,
            'parse_web_file'      => route("{$prefix}.parse-web-file"),
            'import_excel_preview' => route("{$prefix}.import-excel-preview"),
            'scrape_web'          => $scrapeWebBase,
            'scrape_url'      => $scrapeUrlBase,
        ]);
    }

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'analisa');
        $bulanIndonesia = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        if ($tab === 'prospektus') {
            $query = ReksaDana::with(['documents' => fn($q) => $q->where('document_type', 'prospektus')
                ->when($request->filled('prospektus_year'), fn($qq) => $qq->where('ffs_year', $request->prospektus_year))
                ->orderBy('ffs_year', 'desc')->orderBy('ffs_month', 'desc')])
                ->whereHas('documents', fn($q) => $q->where('document_type', 'prospektus')
                    ->when($request->filled('prospektus_year'), fn($qq) => $qq->where('ffs_year', $request->prospektus_year)));

            $reksaDanas = $query->orderBy('nama_reksa_dana')->paginate(20);

            $tahunList = ReksaDanaDocument::where('document_type', 'prospektus')
                ->whereNotNull('ffs_year')->distinct()->orderBy('ffs_year', 'desc')->pluck('ffs_year');

            return view('analisa.index', compact('reksaDanas', 'tahunList', 'bulanIndonesia') + ['tab' => 'prospektus', 'pageTitle' => 'Monitor Reksa Dana', 'pageSub' => 'Daftar reksa dana dengan dokumen Prospektus']);
        }

        if ($tab === 'ffs') {
            $query = ReksaDana::with(['documents' => fn($q) => $q->where('document_type', 'ffs')
                ->when($request->filled('ffs_bulan'), fn($qq) => $qq->where('ffs_month', $request->ffs_bulan))
                ->when($request->filled('ffs_tahun'), fn($qq) => $qq->where('ffs_year', $request->ffs_tahun))
                ->orderBy('ffs_year', 'desc')->orderBy('ffs_month', 'desc')])
                ->whereHas('documents', fn($q) => $q->where('document_type', 'ffs')
                    ->when($request->filled('ffs_bulan'), fn($qq) => $qq->where('ffs_month', $request->ffs_bulan))
                    ->when($request->filled('ffs_tahun'), fn($qq) => $qq->where('ffs_year', $request->ffs_tahun)));

            $reksaDanas = $query->orderBy('nama_reksa_dana')->paginate(20);

            $reksaDanas = $query->orderBy('nama_reksa_dana')->paginate(20);

            $tahunList = ReksaDanaDocument::where('document_type', 'ffs')
                ->whereNotNull('ffs_year')->distinct()->orderBy('ffs_year', 'desc')->pluck('ffs_year');

            return view('analisa.index', compact('reksaDanas', 'tahunList', 'bulanIndonesia') + ['tab' => 'ffs', 'pageTitle' => 'Monitor Reksa Dana', 'pageSub' => 'Daftar reksa dana dengan dokumen FFS']);
        }

        $publishedAnalisas = AnalisaReksaDana::where('is_published', true)
            ->where('product_type', $this->productType)
            ->with('user')
            ->latest('published_at')->paginate(20);

        $analisas = AnalisaReksaDana::where('user_id', auth()->id())
            ->where('product_type', $this->productType)
            ->latest()->get();

        $createRoute = $this->productType === 'unit_link'
            ? route('user.unit-link-analisa.create')
            : route('user.analisa.create');

        $pageTitle = 'Monitor Reksa Dana';
        $pageSub = 'Hasil analisa reksa dana yang telah dipublikasikan';

        return view('analisa.index', compact('publishedAnalisas', 'analisas', 'pageTitle', 'pageSub', 'bulanIndonesia'))
            ->with('productLabel', $this->productLabel)
            ->with('createRoute', $createRoute)
            ->with('tab', 'analisa');
    }

    protected function linkRoutes(): array
    {
        // Link pribadi per user (bukan admin global) — selalu lewat route user.*
        return [
            'store'   => 'user.data-source-links.store',
            'update'  => 'user.data-source-links.update',
            'destroy' => 'user.data-source-links.destroy',
            'upload'  => 'user.data-source-links.upload',
        ];
    }

    protected function linkPageRoute(): string
    {
        return match (true) {
            $this->isAdminContext && $this->productType === 'unit_link' => 'admin.analisa-ul.create',
            $this->isAdminContext                                        => 'admin.analisa-rd.create',
            $this->productType === 'unit_link'                          => 'user.unit-link-analisa.create',
            default                                                      => 'user.analisa.create',
        };
    }

    protected function dataSourceLinkContext(): array
    {
        $dataSourceLinks = DataSourceLink::with('urls')
            ->forUser(auth()->id())
            ->where('is_active', true)
            ->latest()
            ->get();

        $editingLink = request('edit')
            ? DataSourceLink::forUser(auth()->id())->with('urls')->find(request('edit'))
            : null;

        return [
            'dataSourceLinks' => $dataSourceLinks,
            'editingLink'     => $editingLink,
            'linkRoutes'      => $this->linkRoutes(),
            'linkPageRoute'   => $this->linkPageRoute(),
        ];
    }

    public function create()
    {
        $resumeAnalisa = null;
        $resumeMode = null;
        $ffsPembandingOptions = [];

        if ($resumeId = request('resume')) {
            $analisa = AnalisaReksaDana::with(['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasiAset'])
                ->when(!$this->isAdminContext, fn($query) => $query->where('user_id', auth()->id()))
                ->where('product_type', $this->productType)
                ->find($resumeId);
            if ($analisa) {
                $resumeAnalisa = $this->serializeAnalisaForForm($analisa);
                $resumeMode = $analisa->mode ?: 'manual';
                $ffsPembandingOptions = $this->getFfsPembandingOptions(
                    $analisa->reksa_dana_id,
                    $analisa->ffs_bulan,
                    $analisa->ffs_tahun,
                    $analisa->kode_reksa_dana
                );
            }
        }

        return view('analisa.create', array_merge(
            ['formRoutes' => $this->formRoutes(), 'productLabel' => $this->productLabel],
            $this->dataSourceLinkContext(),
            compact('resumeAnalisa', 'resumeMode', 'ffsPembandingOptions'),
        ));
    }

    public function resume()
    {
        $lastDraft = AnalisaReksaDana::query()
            ->when(!$this->isAdminContext, fn($query) => $query->where('user_id', auth()->id()))
            ->where('product_type', $this->productType)
            ->where('status', 'input_manual')
            ->latest()
            ->first();

        $createRoute = $this->isAdminContext ? 'admin.analisa-rd.create' : 'user.analisa.create';

        if ($lastDraft) {
            return redirect()->route($createRoute, ['resume' => $lastDraft->id]);
        }

        return redirect()->route($createRoute);
    }

    public function lookupKode(Request $request)
    {
        $request->validate([
            'kode_reksa_dana' => 'required|string|max:20',
        ]);

        $kode = strtoupper(trim($request->kode_reksa_dana));
        $master = ReksaDana::with('investmentManager')->whereRaw('UPPER(kode_reksa_dana) = ?', [$kode])->first();
        $lastAnalisa = AnalisaReksaDana::with(['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasiAset'])
            ->where('product_type', $this->productType)
            ->whereRaw('UPPER(kode_reksa_dana) = ?', [$kode])
            ->latest()
            ->first();

        $ffsPembandingOptions = $master ? $this->getFfsPembandingOptions($master->id) : [];

        return response()->json([
            'found' => (bool) ($master || $lastAnalisa),
            'master' => $master ? [
                'kode_reksa_dana'     => $master->kode_reksa_dana,
                'nama_reksa_dana'     => $master->nama_reksa_dana,
                'jenis_reksa_dana'    => $master->jenis,
                'kategori'            => $master->kategori ?? [],
                'benchmark'           => $master->benchmark,
                'tujuan_investasi'    => $master->tujuan_investasi,
                'kebijakan_investasi' => $master->kebijakan_investasi,
                'nab_per_unit'        => $master->nab_per_unit,
                'tanggal_data'        => $master->tanggal_nab?->format('Y-m-d'),
                'manajer_investasi'    => $master->nama_manajer_investasi ?? $master->investmentManager?->name,
                'bank_kustodian'       => $master->custodian_bank,
                'tanggal_peluncuran'   => $master->launch_date?->format('Y-m-d'),
                'mata_uang'            => $master->mata_uang,
                'management_fee'       => $master->management_fee,
                'custodian_fee'        => $master->custodian_fee,
                'total_aum'            => $master->aum,
                'unit_penyertaan'      => $master->total_unit,
                'return_1m'             => $master->return_1m,
                'return_ytd'            => $master->return_ytd,
                'return_1y'             => $master->return_1y,
                'expense_ratio'        => $master->expense_ratio,
            ] : null,
            'last_analisa' => $lastAnalisa ? $this->serializeAnalisaForForm($lastAnalisa) : null,
            'ffs_pembanding_options' => $ffsPembandingOptions,
        ]);
    }

    public function lookupSektor(Request $request, IdxMarketService $idx)
    {
        $request->validate([
            'kode_efek'  => 'required|string|max:20',
            'tanggal'    => 'nullable|date',
        ]);

        $kode = strtoupper(trim($request->kode_efek));
        $sektor = $idx->getStockSector($kode);

        return response()->json([
            'found'  => $sektor !== null,
            'sektor' => $sektor,
        ]);
    }

    public function lookupIhsg(Request $request, IdxMarketService $idx)
    {
        $request->validate([
            'kode_efek' => 'required|string|max:20',
            'tanggal'   => 'required|date',
        ]);

        $kode = strtoupper(trim($request->kode_efek));
        $kontribusi = $idx->getIHSGContribution($kode, $request->tanggal);

        return response()->json([
            'found'         => $kontribusi !== null,
            'kontribusi'    => $kontribusi,
        ]);
    }

    public function lookupReturn(Request $request, IdxMarketService $idx)
    {
        $request->validate([
            'kode_efek' => 'required|string|max:20',
            'tanggal'   => 'required|date',
        ]);

        $kode = strtoupper(trim($request->kode_efek));
        $tanggal = $request->tanggal;

        return response()->json([
            'found'     => true,
            'return_1m' => $idx->getStockReturn($kode, $tanggal, 1),
            'return_3m' => $idx->getStockReturn($kode, $tanggal, 3),
            'return_6m' => $idx->getStockReturn($kode, $tanggal, 6),
            'return_1y' => $idx->getStockReturn($kode, $tanggal, 12),
        ]);
    }

    public function lookupBondReturn(Request $request, BondMarketService $bond)
    {
        $request->validate([
            'kode_obligasi' => 'required|string|max:20',
            'tanggal'       => 'required|date',
        ]);

        $kode = strtoupper(trim($request->kode_obligasi));
        $tanggal = $request->tanggal;

        return response()->json([
            'found'     => true,
            'return_1m' => $bond->getBondReturn($kode, $tanggal, 1),
            'return_3m' => $bond->getBondReturn($kode, $tanggal, 3),
            'return_6m' => $bond->getBondReturn($kode, $tanggal, 6),
            'return_1y' => $bond->getBondReturn($kode, $tanggal, 12),
        ]);
    }

    public function lookupSukukReturn(Request $request)
    {
        $request->validate([
            'kode_sukuk' => 'required|string|max:20',
        ]);

        return response()->json([
            'found' => false,
        ]);
    }

    public function lookupBankData(Request $request, BankDataService $bankData)
    {
        $request->validate([
            'nama_bank' => 'required|string|max:255',
            'tanggal'   => 'required|date',
        ]);

        $nama = trim($request->nama_bank);
        $tanggal = $request->tanggal;

        return response()->json([
            'found'             => true,
            'car'               => $bankData->getCar($nama, $tanggal),
            'npl'               => $bankData->getNpl($nama, $tanggal),
            'klasifikasi_risiko' => $bankData->getKlasifikasiRisiko($nama, $tanggal),
        ]);
    }

    public function downloadTemplate()
    {
        $publicPath = public_path('storage/import/format template.xlsx');
        $resourcePath = resource_path('templates/format template.xlsx');

        // Pastikan file tersedia di public/storage/import (production safe).
        // File asli disimpan di resources/templates agar ikut terdeploy via git.
        if (!file_exists($publicPath) && file_exists($resourcePath)) {
            $dir = dirname($publicPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            copy($resourcePath, $publicPath);
        }

        if (file_exists($publicPath)) {
            return response()->download($publicPath, 'format template.xlsx');
        }

        return Excel::download(new AnalisaTemplateExport(), 'template-analisa-reksa-dana.xlsx');
    }

    public function previewAi(Request $request, GroqService $groq)
    {
        $request->validate([
            'nama_reksa_dana' => 'required|string|max:255',
        ]);

        // Default jenis jika tidak diisi
        if (!$request->filled('jenis_reksa_dana')) {
            $request->merge(['jenis_reksa_dana' => 'Saham']);
        }

        try {
            $analisa = AnalisaPayloadBuilder::fromRequest($request);
            $result = $groq->generateNarasiAnalisaStructured($analisa);

            return response()->json([
                'success' => true,
                'message' => 'Analisa AI berhasil dibuat.',
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat Analisa AI: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function previewAiPlus(Request $request, GroqService $groq)
    {
        $request->validate([
            'nama_reksa_dana'  => 'required|string|max:255',
            'jenis_reksa_dana' => 'required|in:Saham,Pendapatan Tetap,Campuran,Pasar Uang,Terproteksi,Global,DIRE-DINFRA,Penyertaan terbatas',
        ]);

        $analisa = AnalisaPayloadBuilder::fromRequest($request);

        $plusCheck = AnalisaAiValidator::assessPlusManualData($analisa);
        if (!$plusCheck['can_run']) {
            return response()->json([
                'success' => false,
                'message' => $plusCheck['message'],
                'missing' => $plusCheck['missing'],
            ], 422);
        }

        try {
            $result = $groq->generateAnalisaPlusStructured($analisa);

            return response()->json([
                'success' => true,
                'message' => 'Analisa AI Plus berhasil dibuat.',
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat Analisa AI Plus: ' . $e->getMessage(),
            ], 422);
        }
    }

    private function mergePartitionFields(array $partitions): array
    {
        $fields = [];
        foreach ($partitions as $part) {
            foreach ($part['fields'] ?? [] as $key => $value) {
                if (!array_key_exists($key, $fields) && $value !== null && $value !== '') {
                    $fields[$key] = $value;
                }
            }
        }
        return $fields;
    }

    private function enrichKodeEfek(array &$data): void
    {
        $resolver = app(\App\Services\StockIdentityResolver::class);

        foreach ($data['efek'] ?? [] as &$efek) {
            if (!empty($efek['kode_efek']) || empty($efek['nama_efek'])) {
                continue;
            }
            $enriched = $resolver->enrich([
                'kode_saham' => '',
                'nama_perusahaan' => $efek['nama_efek'],
                'sektor' => $efek['sektor'] ?? '',
            ]);
            if ($enriched['kode_saham']) {
                $efek['kode_efek'] = $enriched['kode_saham'];
                $efek['sektor'] = $efek['sektor'] ?: $enriched['sektor'];
            }
        }
        unset($efek);

        foreach ($data['_raw_tables'] ?? [] as &$partition) {
            foreach ($partition['tables'] ?? [] as &$table) {
                if (($table['table_name'] ?? '') !== 'Portofolio Efek') continue;

                $kodeIdx = null;
                $namaIdx = null;
                foreach ($table['headers'] ?? [] as $i => $h) {
                    $h = trim((string) $h);
                    if (in_array($h, ['Kode', 'Kode Efek', 'Ticker', 'Symbol', 'Kode Saham', 'ISIN'])) {
                        $kodeIdx = $i;
                    }
                    if (in_array($h, ['Nama Efek', 'Nama', 'Nama Saham'])) {
                        $namaIdx = $i;
                    }
                }
                if ($kodeIdx === null || $namaIdx === null) continue;

                foreach ($table['rows'] as &$row) {
                    if (!empty($row[$kodeIdx] ?? '')) continue;
                    $nama = $row[$namaIdx] ?? '';
                    if (empty($nama)) continue;

                    $enriched = $resolver->enrich([
                        'kode_saham' => '',
                        'nama_perusahaan' => $nama,
                        'sektor' => '',
                    ]);
                    if ($enriched['kode_saham']) {
                        $row[$kodeIdx] = $enriched['kode_saham'];
                    }
                }
                unset($row);
            }
            unset($table);
        }
        unset($partition);
    }

    public function parsePdf(Request $request, FfsParserService $ffsParser, GroqService $groq, AiTableService $aiTable)
    {
        ignore_user_abort(true);
        set_time_limit(600);

        $request->validate([
            'file_pdf' => 'required|file|max:10240',
            'document_type' => 'nullable|string|in:ffs,prospektus,laporan_tahunan,laporan_keuangan,informasi_lainnya,portofolio_efek,pengukuran_nilai_wajar,bs_is_cf_pup',
            'parse_mode' => 'nullable|string|in:ai,table,hybrid',
        ]);

        $file = $request->file('file_pdf');
        $path = $file->getPathname();
        $documentType = $request->input('document_type');
        $parseMode = $request->input('parse_mode', 'ai');

        try {
            if ($parseMode === 'table') {
                $partitions = $aiTable->extractTables($path, [['id' => 1, 'start' => 1, 'end' => 999]]);
                $data = array_merge(
                    $this->mergePartitionFields($partitions),
                    ['_raw_tables' => $partitions]
                );
            } elseif ($parseMode === 'hybrid') {
                $data = $ffsParser->parseWithPageRangesHybrid($path, $groq, [['start_page' => 1, 'end_page' => 999, 'section_type' => 'auto']], $aiTable);
            } else {
                $data = $ffsParser->parseWithAi($path, $groq, $documentType);
            }

            $this->enrichKodeEfek($data);
        } catch (\Throwable $e) {
            if (connection_aborted()) {
                \Log::warning('[PARSE-PDF] Koneksi terputus oleh klien/proxy sebelum ekstraksi selesai. Data mungkin tidak lengkap.');
            }
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca PDF: ' . $e->getMessage(),
                'data' => null,
            ], 422);
        }

        $filename = 'ffs-' . now()->format('Ymd-His') . '-' . Str::random(8) . '.pdf';
        $storedPath = $file->storeAs('analisa-pdfs', $filename, 'public');

        if ($storedPath && !empty($data['efek'])) {
            $tanggal = now()->subDay()->toDateString();
            foreach ($data['efek'] as $efek) {
                if (!empty($efek['kode_efek'])) {
                    $harga = !empty($efek['harga']) ? $efek['harga'] : null;
                    StockPrice::updateOrCreate(
                        ['kode_efek' => strtoupper($efek['kode_efek']), 'tanggal' => $tanggal],
                        [
                            'nama_efek' => $efek['nama_efek'] ?? null,
                            'jenis' => 'Saham',
                            'harga' => $harga ?? 0,
                            'sumber' => 'PDF FFS',
                        ]
                    );
                }
            }
        }

        $exportResult = $this->exportAndReimport($data);

        // Admin context: return preview mode (don't auto-save)
        if ($this->isAdminContext) {
            $extracted = $this->buildExtractedList($data);

            return response()->json([
                'success' => true,
                'preview' => true,
                'message' => 'Data berhasil diekstrak dari FFS. Silakan review lalu klik Simpan.',
                'extracted' => $extracted,
                'period' => $this->extractPeriodFromData($data),
                'data' => $exportResult['data'],
                'export_file' => $exportResult['export_file'],
                'pdf_file' => $storedPath,
            ]);
        }

        if ($storedPath && !empty($data['efek'])) {
            $tanggal = now()->subDay()->toDateString();
            foreach ($data['efek'] as $efek) {
                if (!empty($efek['kode_efek'])) {
                    $harga = !empty($efek['harga']) ? $efek['harga'] : null;
                    StockPrice::updateOrCreate(
                        ['kode_efek' => strtoupper($efek['kode_efek']), 'tanggal' => $tanggal],
                        [
                            'nama_efek' => $efek['nama_efek'] ?? null,
                            'jenis' => 'Saham',
                            'harga' => $harga ?? 0,
                            'sumber' => 'PDF FFS',
                        ]
                    );
}
        }

        $exportResult = $this->exportAndReimport($data);

        // Admin context: return preview mode (don't auto-save)
        if ($this->isAdminContext) {
            $extracted = $this->buildExtractedList($data);
            $period = $this->extractPeriodFromData($data);

            return response()->json([
                'success' => true,
                'preview' => true,
                'message' => 'Data berhasil diekstrak dari FFS. Silakan review lalu klik Simpan.',
                'extracted' => $extracted,
                'period' => $period,
                'data' => $exportResult['data'],
                'export_file' => $exportResult['export_file'],
                'pdf_file' => $storedPath,
            ]);
        }

        if ($storedPath && !empty($data['efek'])) {
            $tanggal = now()->subDay()->toDateString();
            foreach ($data['efek'] as $efek) {
                if (!empty($efek['kode_efek'])) {
                    $harga = !empty($efek['harga']) ? $efek['harga'] : null;
                    StockPrice::updateOrCreate(
                        ['kode_efek' => strtoupper($efek['kode_efek']), 'tanggal' => $tanggal],
                        [
                            'nama_efek' => $efek['nama_efek'] ?? null,
                            'jenis' => 'Saham',
                            'harga' => $harga ?? 0,
                            'sumber' => 'PDF FFS',
                        ]
                    );
                }
            }
        }

        $extracted = $this->buildExtractedList($data);
        $success = count($extracted) > 0;
        $message = $success
            ? 'Berhasil mengekstrak: ' . implode(', ', $extracted) . '.'
            : 'Tidak dapat mengekstrak data dari PDF ini. Format mungkin tidak didukung.';

        return response()->json([
            'success' => $success,
            'message' => $message,
            'warning' => $success ? 'Data hasil ekstraksi AI bisa saja tidak akurat. Mohon periksa dan validasi setiap field sebelum menyimpan.' : null,
            'data' => $exportResult['data'],
            'export_file' => $exportResult['export_file'],
            'pdf_file' => $storedPath,
        ]);
    }
    }

    public function getExistingDocuments(Request $request)
    {
        try {
            $request->validate([
                'kode_reksa_dana' => 'nullable|string|max:20',
                'jenis_laporan' => 'nullable|in:kalender_ffs,laporan_tahunan',
                'ffs_bulan' => 'nullable|integer|min:1|max:12',
                'ffs_tahun' => 'nullable|integer|min:2000|max:2100',
                'tahun_laporan' => 'nullable|integer|min:2000|max:2100',
            ]);

            $query = ReksaDanaDocument::with(['reksaDana', 'uploader']);

            if ($kode = $request->kode_reksa_dana) {
                $kode = strtoupper(trim($kode));
                $query->whereHas('reksaDana', fn($q) => $q->where('kode_reksa_dana', $kode));
            }

            $jenisLaporan = $request->jenis_laporan ?: 'laporan_tahunan';

            if ($jenisLaporan === 'laporan_tahunan') {
                $query->whereIn('document_type', [
                    ReksaDanaDocument::TYPE_LAPORAN_TAHUNAN,
                    ReksaDanaDocument::TYPE_PROSPECTUS,
                ]);
                if ($request->tahun_laporan) {
                    $query->where('ffs_year', $request->tahun_laporan);
                }
            } else {
                $query->where('document_type', ReksaDanaDocument::TYPE_FFS);
                if ($request->ffs_bulan) {
                    $query->where('ffs_month', $request->ffs_bulan);
                }
                if ($request->ffs_tahun) {
                    $query->where('ffs_year', $request->ffs_tahun);
                }
            }

            $documents = $query->orderByDesc('ffs_year')
                ->orderByDesc('ffs_month')
                ->get()
                ->map(fn($doc) => [
                    'id' => $doc->id,
                    'document_type' => $doc->document_type,
                    'original_name' => $doc->original_name,
                    'file_path' => $doc->file_path,
                    'label' => $this->getDocumentLabel($doc),
                    'ffs_month' => $doc->ffs_month,
                    'ffs_year' => $doc->ffs_year,
                    'tahun_laporan' => $doc->ffs_year,
                    'reksa_dana_nama' => $doc->reksaDana?->nama_reksa_dana,
                    'reksa_dana_kode' => $doc->reksaDana?->kode_reksa_dana,
                    'uploaded_at' => $doc->created_at?->format('d/m/Y') ?? '',
                    'uploader_name' => $doc->uploader?->name,
                    'file_size' => $doc->file_size,
                    'notes' => $doc->notes,
                    'url' => $doc->file_path && Storage::disk('public')->exists($doc->file_path)
                        ? Storage::disk('public')->url($doc->file_path) : null,
                ]);

            return response()->json([
                'found' => $documents->isNotEmpty(),
                'documents' => $documents,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'found' => false,
                'documents' => [],
                'error' => 'Parameter tidak valid.',
            ], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal mengambil dokumen tersimpan', [
                'error' => $e->getMessage(),
                'params' => $request->only(['kode_reksa_dana', 'jenis_laporan', 'ffs_bulan', 'ffs_tahun', 'tahun_laporan']),
            ]);

            return response()->json([
                'found' => false,
                'documents' => [],
                'error' => 'Gagal mengambil data dokumen.',
            ]);
        }
    }

    public function lookupPeriodData(Request $request)
    {
        try {
            $request->validate([
                'kode_reksa_dana' => 'required|string|max:20',
                'ffs_bulan' => 'required|integer|min:1|max:12',
                'ffs_tahun' => 'required|integer|min:2000|max:2100',
            ]);

            $kode = strtoupper(trim($request->kode_reksa_dana));

            $analisa = AnalisaReksaDana::with(['sektor', 'efek', 'alokasiAset'])
                ->where('product_type', $this->productType)
                ->whereRaw('UPPER(kode_reksa_dana) = ?', [$kode])
                ->where('ffs_bulan', $request->ffs_bulan)
                ->where('ffs_tahun', $request->ffs_tahun)
                ->latest()
                ->first();

            if (!$analisa) {
                return response()->json(['found' => false, 'data' => null]);
            }

            return response()->json([
                'found' => true,
                'data' => [
                    'total_aum' => $analisa->total_aum,
                    'unit_penyertaan' => $analisa->unit_penyertaan,
                    'nab_per_unit' => $analisa->nab_per_unit,
                    'return_1m' => $analisa->return_1m,
                    'return_ytd' => $analisa->return_ytd,
                    'return_1y' => $analisa->return_1y,
                    'biaya_operasi' => $analisa->biaya_operasi,
                    'portfolio_turnover_ratio' => $analisa->portfolio_turnover_ratio,
                    'management_fee' => $analisa->management_fee,
                    'custodian_fee' => $analisa->custodian_fee,
                    'total_marcap_10_efek' => $analisa->total_marcap_10_efek,
                    'sektor' => $analisa->sektor->map(fn($s) => ['nama_sektor' => $s->nama_sektor, 'bobot' => $s->bobot])->values(),
                    'efek' => $analisa->efek->map(fn($e) => [
                        'kode_efek' => $e->kode_efek,
                        'nama_efek' => $e->nama_efek,
                        'sektor' => $e->sektor,
                        'bobot' => $e->bobot,
                        'bobot_seharusnya' => $e->bobot_seharusnya,
                        'nilai_pasar' => $e->nilai_pasar,
                        'return_1m' => $e->return_1m,
                        'return_3m' => $e->return_3m,
                        'return_6m' => $e->return_6m,
                        'return_1y' => $e->return_1y,
                        'ihsg_contribution' => $e->ihsg_contribution,
                        'kontribusi_return' => $e->kontribusi_return,
                        'effect_type' => $e->effect_type,
                        'top_10' => $e->top_10,
                        'harga_perolehan' => $e->harga_perolehan,
                        'persen_nab' => $e->persen_nab,
                        'market_cap' => $e->market_cap,
                        'jumlah_lembar' => $e->jumlah_lembar,
                        'harga_perolehan_rata_rata' => $e->harga_perolehan_rata_rata,
                        'kontribusi_kinerja' => $e->kontribusi_kinerja,
                    ])->values(),
                    'alokasi_aset' => $analisa->alokasiAset->map(fn($a) => ['nama_aset' => $a->nama_aset, 'persentase' => $a->persentase])->values(),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['found' => false, 'data' => null, 'error' => 'Parameter tidak valid.'], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal mengambil data periode', [
                'error' => $e->getMessage(),
                'params' => $request->only(['kode_reksa_dana', 'ffs_bulan', 'ffs_tahun']),
            ]);
            return response()->json(['found' => false, 'data' => null, 'error' => 'Gagal mengambil data periode.']);
        }
    }

    private function getDocumentLabel(ReksaDanaDocument $doc): string
    {
        return match ($doc->document_type) {
            ReksaDanaDocument::TYPE_LAPORAN_TAHUNAN => 'Laporan Tahunan ' . ($doc->ffs_year ?? ''),
            ReksaDanaDocument::TYPE_PROSPECTUS => 'Prospektus ' . ($doc->ffs_year ?? ''),
            ReksaDanaDocument::TYPE_FFS => 'FFS '
                . ($doc->ffs_month ? \Carbon\Carbon::create()->month($doc->ffs_month)->format('M') . ' ' : '')
                . ($doc->ffs_year ?? ''),
            default => $doc->original_name,
        };
    }

    public function parseExistingDocument(Request $request, FfsParserService $ffsParser, GroqService $groq, AiTableService $aiTable)
    {
        ignore_user_abort(true);
        set_time_limit(600);

        $request->merge([
            'jenis_laporan' => 'laporan_tahunan',
            'ffs_bulan' => null,
            'ffs_tahun' => null,
            'tahun_laporan' => $request->tahun_laporan ?: now()->year,
        ]);

        $request->validate([
            'document_id' => 'required|exists:reksa_dana_documents,id',
            'document_type' => 'nullable|string|in:ffs,prospektus,laporan_tahunan,laporan_keuangan,informasi_lainnya,portofolio_efek,pengukuran_nilai_wajar,bs_is_cf_pup',
            'page_ranges' => 'nullable|array',
            'page_ranges.*.start_page' => 'required_with:page_ranges|integer|min:1',
            'page_ranges.*.end_page' => 'required_with:page_ranges|integer|min:1',
            'page_ranges.*.section_type' => 'nullable|string|in:auto,informasi_lainnya,portofolio_efek,pengukuran_nilai_wajar,bs_is_cf_pup',
            'parse_mode' => 'nullable|string|in:ai,table,hybrid',
        ]);

        $document = ReksaDanaDocument::findOrFail($request->document_id);
        $documentType = $request->input('document_type');
        $pageRanges = $request->input('page_ranges');
        $parseMode = $request->input('parse_mode', 'ai');

        if (!empty($pageRanges)) {
            \Log::info('[PARSE-EXISTING] Page ranges mode', [
                'document_id' => $request->document_id,
                'ranges' => $pageRanges,
                'parse_mode' => $parseMode,
            ]);
        }

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File PDF dokumen tidak ditemukan di penyimpanan.',
                'data' => null,
            ], 404);
        }

        $fullPath = Storage::disk('public')->path($document->file_path);

        try {
            if ($parseMode === 'table') {
                $partitions = [];
                foreach ($pageRanges ?? [['start_page' => 1, 'end_page' => 999]] as $i => $range) {
                    $partitions[] = [
                        'id' => $i + 1,
                        'start' => (int) ($range['start_page'] ?? 1),
                        'end' => (int) ($range['end_page'] ?? 999),
                        'section_type' => $range['section_type'] ?? 'auto',
                    ];
                }
                $partitions = $aiTable->extractTables($fullPath, $partitions);
                $data = array_merge(
                    $this->mergePartitionFields($partitions),
                    ['_raw_tables' => $partitions]
                );
            } elseif ($parseMode === 'hybrid' && !empty($pageRanges)) {
                $data = $ffsParser->parseWithPageRangesHybrid($fullPath, $groq, $pageRanges, $aiTable);
            } elseif (!empty($pageRanges)) {
                $data = $ffsParser->parseWithPageRanges($fullPath, $groq, $pageRanges);
            } else {
                $data = $ffsParser->parseWithAi($fullPath, $groq, $documentType);
            }

            $this->enrichKodeEfek($data);

            // Simpan hasil ekstraksi agar tidak hilang jika koneksi terputus
            try {
                FfsExtractionResult::updateOrCreate(
                    [
                        'reksa_dana_document_id' => $document->id,
                        'created_by' => auth()->id(),
                    ],
                    [
                        'reksa_dana_id' => $document->reksa_dana_id,
                        'extracted_data' => $data,
                    ]
                );
            } catch (\Throwable $e) {
                \Log::warning('[PARSE-EXISTING] Gagal menyimpan hasil ekstraksi: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            if (connection_aborted()) {
                \Log::warning('[PARSE-EXISTING] Koneksi terputus oleh klien/proxy sebelum ekstraksi selesai.');
            }
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca PDF: ' . $e->getMessage(),
                'data' => null,
            ], 422);
        }

        if (!empty($data['efek'])) {
            $tanggal = now()->subDay()->toDateString();
            foreach ($data['efek'] as $efek) {
                if (!empty($efek['kode_efek'])) {
                    $harga = !empty($efek['harga']) ? $efek['harga'] : null;
                    StockPrice::updateOrCreate(
                        ['kode_efek' => strtoupper($efek['kode_efek']), 'tanggal' => $tanggal],
                        [
                            'nama_efek' => $efek['nama_efek'] ?? null,
                            'jenis' => 'Saham',
                            'harga' => $harga ?? 0,
                            'sumber' => 'PDF Dokumen Tersimpan',
                        ]
                    );
                }
            }
        }

        $extracted = [];
        if (!empty($data['nama_reksa_dana'])) $extracted[] = 'Nama RD';
        if (!empty($data['jenis_reksa_dana'])) $extracted[] = 'Jenis RD';
        if (!empty($data['manajer_investasi'])) $extracted[] = 'MI';
        if (!empty($data['total_aum'])) $extracted[] = 'Total AUM';
        if (!empty($data['unit_penyertaan'])) $extracted[] = 'Unit Penyertaan';
        if (!empty($data['nab_per_unit'])) $extracted[] = 'NAB/UP';
        if (!empty($data['tanggal_data'])) $extracted[] = 'Tanggal Data';
        if (!empty($data['alokasi_aset'])) $extracted[] = count($data['alokasi_aset']) . ' Alokasi Aset';
        if (!empty($data['sektor'])) $extracted[] = count($data['sektor']) . ' Sektor';
        if (!empty($data['efek'])) $extracted[] = count($data['efek']) . ' Efek';
        if (!empty($data['kinerja'])) $extracted[] = count($data['kinerja']) . ' Bulan Kinerja';
        if (!empty($data['obligasi'])) $extracted[] = count($data['obligasi']) . ' Obligasi';
        if (!empty($data['bank'])) $extracted[] = count($data['bank']) . ' Bank';
        // Keuangan
        if (!empty($data['total_aset'])) $extracted[] = 'Total Aset';
        if (!empty($data['total_liabilitas'])) $extracted[] = 'Total Liabilitas';
        if (!empty($data['laba_bersih'])) $extracted[] = 'Laba Bersih';
        if (!empty($data['total_beban'])) $extracted[] = 'Total Beban';
        if (!empty($data['laba_sebelum_pajak'])) $extracted[] = 'Laba Sblm Pajak';
        if (!empty($data['laba_bersih_tahun_berjalan'])) $extracted[] = 'Laba Bersih Thn Brjln';
        if (!empty($data['arus_kas_operasi'])) $extracted[] = 'Arus Kas Operasi';
        if (!empty($data['total_hasil_investasi'])) $extracted[] = 'Total Hasil Investasi';
        if (!empty($data['fair_value_level_1']) || !empty($data['fair_value_level_2']) || !empty($data['fair_value_level_3'])) $extracted[] = 'Fair Value';
        if (!empty($data['portofolio_efek']) || !empty($data['instrumen_pasar_uang']) || !empty($data['piutang_transaksi_efek']) || !empty($data['piutang_bunga_dan_dividen']) || !empty($data['uang_muka_diterima'])) $extracted[] = 'Aset Detail';
        if (!empty($data['liabilitas_pembelian_kembali']) || !empty($data['beban_akrual']) || !empty($data['liabilitas_atas_biaya']) || !empty($data['pembelian_kembali_unit_penyertaan']) || !empty($data['utang_pajak_lainnya'])) $extracted[] = 'Liabilitas Detail';
        if (!empty($data['pendapatan_investasi']) || !empty($data['pendapatan_lainnya'])) $extracted[] = 'Pendapatan Detail';
        if (!empty($data['beban_investasi']) || !empty($data['beban_pengelolaan_investasi'])) $extracted[] = 'Beban Detail';
        if (!empty($data['pembelian_efek_ekuitas']) || !empty($data['penjualan_efek_ekuitas']) || !empty($data['penerimaan_bunga_deposito']) || !empty($data['penerimaan_bunga_jasa_giro']) || !empty($data['penerimaan_dividen_kas'])) $extracted[] = 'Penerimaan Detail';
        if (!empty($data['pembayaran_jasa_pengelolaan']) || !empty($data['pembayaran_jasa_kustodian']) || !empty($data['pembayaran_beban_lain_arus']) || !empty($data['kas_bersih_aktivitas_operasi']) || !empty($data['penerimaan_penjualan_unit']) || !empty($data['pembayaran_pembelian_kembali_unit']) || !empty($data['kas_bersih_aktivitas_pendanaan']) || !empty($data['kenaikan_kas_setara_kas'])) $extracted[] = 'Detail Arus Kas';

        if (!empty($data['_raw_tables'])) {
            $totalTabel = array_sum(array_map(fn($p) => count($p['tables'] ?? []), $data['_raw_tables']));
            if ($totalTabel > 0) {
                $extracted[] = "{$totalTabel} tabel";
            } elseif (!empty($data['_raw_tables'])) {
                $extracted[] = count($data['_raw_tables']) . ' partisi';
            }
        }

        $success = count($extracted) > 0;
        $message = $success
            ? 'Berhasil mengekstrak: ' . implode(', ', $extracted) . '.'
            : 'Tidak dapat mengekstrak data dari PDF ini.';

        if (!empty($pageRanges) && $success) {
            $message .= ' (' . count($pageRanges) . ' partisi halaman)';
        }

        $exportResult = $this->exportAndReimport($data);

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $exportResult['data'],
            'export_file' => $exportResult['export_file'],
            'document_label' => $this->getDocumentLabel($document),
        ]);
    }

    public function parseProspektusPdf(Request $request, ProspektusPipelineService $pipeline)
    {
        ignore_user_abort(true);
        set_time_limit(600);

        $request->validate([
            'file_pdf' => 'required|file|max:10240',
        ]);

        $file = $request->file('file_pdf');
        $path = $file->getPathname();

        try {
            $result = $pipeline->process($path, 'prospektus');
        } catch (\Throwable $e) {
            if (connection_aborted()) {
                \Log::warning('[PROSPEKTUS] Koneksi terputus oleh klien/proxy sebelum pipeline selesai.');
            }
            \Log::error('[PROSPEKTUS] Pipeline error: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses Prospektus PDF: ' . $e->getMessage(),
                'data' => null,
            ], 422);
        }

        $filename = 'prospektus-' . now()->format('Ymd-His') . '-' . Str::random(8) . '.pdf';
        $storedPath = $file->storeAs('analisa-pdfs', $filename, 'public');

        $data = $result['data'];
        $validation = $result['validation'];

        $sectionLabels = [
            'cover' => 'Sampul',
            'mi_profile' => 'Profil MI',
            'fund_info' => 'Info RD',
            'financial_statements' => 'Laporan Keuangan',
            'portfolio' => 'Portofolio',
            'performance' => 'Kinerja',
            'risk' => 'Risiko',
        ];

        $extractedSections = [];
        foreach ($result['section_results'] as $section => $sectionData) {
            if (!empty($sectionData)) {
                $label = $sectionLabels[$section] ?? $section;
                $fields = array_keys(array_filter($sectionData, fn($v) => $v !== null && $v !== '' && $v !== []));
                if (!empty($fields)) {
                    $extractedSections[] = $label . ' (' . implode(', ', array_slice($fields, 0, 5)) . ')';
                }
            }
        }

        $success = !empty($extractedSections);

        $message = $success
            ? 'Berhasil mengekstrak dari Prospektus: ' . implode('; ', $extractedSections) . '.'
            : 'Tidak dapat mengekstrak data dari Prospektus PDF ini. Format mungkin tidak didukung.';

        if ($validation && !empty($validation['warnings'])) {
            $message .= ' Peringatan: ' . implode(', ', $validation['warnings']);
        }

        $exportResult = $this->exportAndReimport($data);

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $exportResult['data'],
            'export_file' => $exportResult['export_file'],
            'pdf_file' => $storedPath,
            'validation' => $validation,
            'classifications' => $result['classifications'],
        ]);
    }

    public function parseExistingProspektus(Request $request, ProspektusPipelineService $pipeline)
    {
        ignore_user_abort(true);
        set_time_limit(600);

        $request->merge([
            'jenis_laporan' => 'laporan_tahunan',
            'ffs_bulan' => null,
            'ffs_tahun' => null,
            'tahun_laporan' => $request->tahun_laporan ?: now()->year,
        ]);

        $request->validate([
            'document_id' => 'required|exists:reksa_dana_documents,id',
        ]);

        $document = ReksaDanaDocument::findOrFail($request->document_id);

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File PDF dokumen tidak ditemukan di penyimpanan.',
                'data' => null,
            ], 404);
        }

        $fullPath = Storage::disk('public')->path($document->file_path);

        try {
            $result = $pipeline->process($fullPath, 'prospektus');
        } catch (\Throwable $e) {
            if (connection_aborted()) {
                \Log::warning('[PROSPEKTUS-EXISTING] Koneksi terputus oleh klien/proxy sebelum pipeline selesai.');
            }
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca Prospektus PDF: ' . $e->getMessage(),
                'data' => null,
            ], 422);
        }

        $data = $result['data'];
        $validation = $result['validation'];

        $extracted = [];
        if (!empty($data['nama_reksa_dana'])) $extracted[] = 'Nama RD';
        if (!empty($data['jenis_reksa_dana'])) $extracted[] = 'Jenis RD';
        if (!empty($data['manajer_investasi'])) $extracted[] = 'MI';
        if (!empty($data['total_aum'])) $extracted[] = 'Total AUM';
        if (!empty($data['unit_penyertaan'])) $extracted[] = 'Unit Penyertaan';
        if (!empty($data['nab_per_unit'])) $extracted[] = 'NAB/UP';
        if (!empty($data['tanggal_data'])) $extracted[] = 'Tanggal Data';
        if (!empty($data['total_aset'])) $extracted[] = 'Neraca';
        if (!empty($data['alokasi_aset'])) $extracted[] = count($data['alokasi_aset']) . ' Alokasi Aset';
        if (!empty($data['sektor'])) $extracted[] = count($data['sektor']) . ' Sektor';
        if (!empty($data['efek'])) $extracted[] = count($data['efek']) . ' Efek';
        if (!empty($data['kinerja'])) $extracted[] = count($data['kinerja']) . ' Bulan Kinerja';
        if (!empty($data['obligasi'])) $extracted[] = count($data['obligasi']) . ' Obligasi';
        if (!empty($data['bank'])) $extracted[] = count($data['bank']) . ' Bank';

        $success = count($extracted) > 0;

        $exportResult = $this->exportAndReimport($data);

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'Berhasil mengekstrak Prospektus: ' . implode(', ', $extracted) . '.'
                : 'Tidak dapat mengekstrak data dari Prospektus PDF ini.',
            'data' => $exportResult['data'],
            'export_file' => $exportResult['export_file'],
            'document_label' => $this->getDocumentLabel($document),
            'validation' => $validation,
        ]);
    }

    public function parseWebFile(Request $request, WebDataFileParserService $parser)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $data = $parser->parse($request->file('file')->getPathname());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage(),
                'data' => null,
            ], 422);
        }

        return response()->json($this->webFileParseResponse($data, true));
    }

    public function importExcelPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        try {
            $tmpPath = $request->file('file')->getPathname();
            $ext = $request->file('file')->getClientOriginalExtension();

            $data = [];

            // 1. Baca sheet portfolio (Sektor, Efek, Kinerja, Obligasi, Sukuk, Bank)
            try {
                $import = new AnalisaImportPreview;
                Excel::import($import, $tmpPath, null, $ext === 'xls' ? \Maatwebsite\Excel\Excel::XLS : \Maatwebsite\Excel\Excel::XLSX);
                $data = $import->getData();
            } catch (\Throwable $e) {
                $data = [];
            }

            // 2. Selalu baca juga sheet laporan keuangan (Posisi Keuangan, Laba Rugi, Arus Kas, Ringkasan)
            // dan merge ke data agar laporan tahunan ikut terisi.
            try {
                $legacy = new LegacyFormatReader;
                $legacyData = $legacy->read($tmpPath);
                if (!empty($legacyData)) {
                    $data = array_merge($legacyData, $data);
                }
            } catch (\Throwable $e) {
                // legacy reader gagal = abaikan, tetap pakai data dari preview
            }

            $hasData = collect($data)->flatten()->isNotEmpty();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage(),
                'data' => null,
            ], 422);
        }

        return response()->json($this->webFileParseResponse($data, true));
    }

    public function scrapeWebData(Request $request, DataSourceAutoDownloadService $downloader, WebDataFileParserService $parser)
    {
        $request->validate([
            'data_source_link_id' => 'required|exists:data_source_links,id',
        ]);

        $link = DataSourceLink::forUser(auth()->id())
            ->with('urls')
            ->findOrFail($request->data_source_link_id);

        try {
            $tempPath = $downloader->downloadToTempFile($link);
            $data = $parser->parse($tempPath);
            @unlink($tempPath);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        }

        return response()->json($this->webFileParseResponse($data, true));
    }

    public function scrapeUrl(Request $request, WebScraperService $scraper)
    {
        $request->validate(['url' => 'required|url|max:2048']);

        try {
            $result = $scraper->scrapeUrl($request->url);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'data' => null], 422);
        }

        $response = $this->webFileParseResponse($result['data'], true);
        $response['message'] = $result['message'];
        $response['type'] = $result['type'];
        if (isset($result['raw_tables'])) {
            $response['raw_tables'] = $result['raw_tables'];
        }

        return response()->json($response);
    }

    protected function webFileParseResponse(array $data, bool $doExport = false): array
    {
        $exportFile = null;
        if ($doExport) {
            $export = $this->exportAndReimport($data);
            $data = $export['data'];
            $exportFile = $export['export_file'] ?? null;
        }

        $extracted = [];
        if (!empty($data['nama_reksa_dana'])) {
            $extracted[] = 'Nama RD';
        }
        if (!empty($data['total_aum'])) {
            $extracted[] = 'Total AUM';
        }
        if (!empty($data['unit_penyertaan'])) {
            $extracted[] = 'Unit Penyertaan';
        }
        if (!empty($data['nab_per_unit'])) {
            $extracted[] = 'NAB/UP';
        }
        if (!empty($data['tanggal_data'])) {
            $extracted[] = 'Tanggal Data';
        }
        if (!empty($data['alokasi_aset'])) {
            $extracted[] = count($data['alokasi_aset']) . ' Alokasi Aset';
        }
        if (!empty($data['sektor'])) {
            $extracted[] = count($data['sektor']) . ' Sektor';
        }
        if (!empty($data['efek'])) {
            $extracted[] = count($data['efek']) . ' Efek';
        }
        if (!empty($data['kinerja'])) {
            $extracted[] = count($data['kinerja']) . ' Kinerja';
        }
        if (!empty($data['obligasi'])) {
            $extracted[] = count($data['obligasi']) . ' Obligasi';
        }
        if (!empty($data['sukuk'])) {
            $extracted[] = count($data['sukuk']) . ' Sukuk';
        }
        if (!empty($data['bank'])) {
            $extracted[] = count($data['bank']) . ' Bank';
        }
        // ponytail: also accept financial-statement-only files (LegacyFormatReader)
        $tahunanYears = $data['data_tahunan']['years'] ?? [];
        if (empty($extracted) && !empty($tahunanYears)) {
            $extracted[] = count($tahunanYears) . ' tahun data laporan keuangan';
        }

        $success = count($extracted) > 0;

        return [
            'success' => $success,
            'message' => $success
                ? 'Data siap diisi ke form: ' . implode(', ', $extracted) . '.'
                : 'File terbaca tetapi tidak ada data yang cocok. Pastikan format Excel template analisa atau export situs yang benar.',
            'data' => $data,
            'export_file' => $exportFile,
        ];
    }

    private function exportAndReimport(array $data): array
    {
        $dir = storage_path('app/public/export');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'analisa-export-' . now()->format('Ymd-His') . '-' . uniqid() . '.xlsx';
        $path = $dir . '/' . $filename;

        try {
            $export = new AnalisaExcelExport;
            $export->export($data, $path);

            $reader = new LegacyFormatReader;
            $reimported = $reader->read($path);

            $merged = array_merge($data, $reimported);

            return [
                'data' => $merged,
                'export_file' => Storage::url('export/' . $filename),
            ];
        } catch (\Throwable $e) {
            return [
                'data' => $data,
                'export_file' => null,
            ];
        }
    }

    public function store(Request $request)
    {
        $request->merge([
            'jenis_laporan' => $request->jenis_laporan ?: 'laporan_tahunan',
            'tahun_laporan' => $request->tahun_laporan ?: now()->year,
            'tanggal_data' => $request->filled('tanggal_data') ? $request->tanggal_data : null,
        ]);

        $request->validate([
            'kode_reksa_dana'      => 'nullable|string|max:20',
            'nama_reksa_dana'      => 'required|string|max:255',
            'jenis_reksa_dana'     => 'required|in:Saham,Pendapatan Tetap,Campuran,Pasar Uang,Terproteksi,Global,DIRE-DINFRA,Penyertaan terbatas',
            'benchmark'            => 'nullable|string|max:255',
            'manajer_investasi'    => 'nullable|string|max:255',
            'bank_kustodian'       => 'nullable|string|max:255',
            'tanggal_peluncuran'   => 'nullable|date',
            'tujuan_investasi'     => 'nullable|string',
            'kebijakan_investasi'  => 'nullable|string',
            'total_aum'            => 'nullable|numeric|min:0',
            'unit_penyertaan'      => 'nullable|numeric|min:0',
            'nab_per_unit'         => 'required_if:input_mode,manual,lengkap|nullable|numeric|min:0',
            'total_marcap_10_efek' => 'nullable|numeric|min:0',
            'tanggal_data'         => 'nullable|date',
            'return_ytd'           => 'nullable|numeric',
            'return_1y'            => 'nullable|numeric',
            'total_return'         => 'nullable|numeric',
            'biaya_operasi'        => 'nullable|numeric',
            'portfolio_turnover_ratio' => 'nullable|numeric',
            'management_fee'       => 'nullable|numeric',
            'custodian_fee'        => 'nullable|numeric',
            // Laporan Keuangan
            'total_aset'           => 'nullable|numeric',
            'total_liabilitas'     => 'nullable|numeric',
            'nilai_aset_bersih'  => 'nullable|numeric',
            'kas_dan_bank'         => 'nullable|numeric',
            'piutang_bunga'        => 'nullable|numeric',
            'piutang_dividen'      => 'nullable|numeric',
            'piutang_lain'         => 'nullable|numeric',
            'utang_pajak'          => 'nullable|numeric',
            'utang_lain'           => 'nullable|numeric',
            'pendapatan_bunga'     => 'nullable|numeric',
            'pendapatan_dividen'   => 'nullable|numeric',
            'gain_realized'        => 'nullable|numeric',
            'gain_unrealized'      => 'nullable|numeric',
            'beban_mi'             => 'nullable|numeric',
            'beban_kustodian'      => 'nullable|numeric',
            'beban_lain'           => 'nullable|numeric',
            'laba_bersih'          => 'nullable|numeric',
            'arus_kas_operasi'     => 'nullable|numeric',
            'arus_kas_pendanaan'   => 'nullable|numeric',
            'kas_awal_tahun'       => 'nullable|numeric',
            'kas_akhir_tahun'      => 'nullable|numeric',
            'kas'                  => 'nullable|numeric',
            'portofolio_efek'      => 'nullable|numeric',
            'instrumen_pasar_uang' => 'nullable|numeric',
            'piutang_transaksi_efek' => 'nullable|numeric',
            'piutang_bunga_dan_dividen' => 'nullable|numeric',
            'uang_muka_diterima'   => 'nullable|numeric',
            'liabilitas_pembelian_kembali' => 'nullable|numeric',
            'beban_akrual'         => 'nullable|numeric',
            'liabilitas_atas_biaya' => 'nullable|numeric',
            'pembelian_kembali_unit_penyertaan' => 'nullable|numeric',
            'utang_pajak_lainnya'  => 'nullable|numeric',
            'pendapatan_investasi' => 'nullable|numeric',
            'pendapatan_lainnya'   => 'nullable|numeric',
            'total_pendapatan'   => 'nullable|numeric',
            'beban_investasi'      => 'nullable|numeric',
            'beban_pengelolaan_investasi' => 'nullable|numeric',
            'total_beban'            => 'nullable|numeric',
            'laba_sebelum_pajak'     => 'nullable|numeric',
            'beban_pajak_penghasilan' => 'nullable|numeric',
            'laba_bersih_tahun_berjalan' => 'nullable|numeric',
            'penghasilan_komprehensif_lain_setelah_pajak' => 'nullable|numeric',
            'penghasilan_komprehensif_tahun_berjalan' => 'nullable|numeric',
            'pembelian_efek_ekuitas' => 'nullable|numeric',
            'penjualan_efek_ekuitas' => 'nullable|numeric',
            'penerimaan_bunga_deposito' => 'nullable|numeric',
            'penerimaan_bunga_jasa_giro' => 'nullable|numeric',
            'penerimaan_dividen_kas' => 'nullable|numeric',
            'pembayaran_jasa_pengelolaan' => 'nullable|numeric',
            'pembayaran_jasa_kustodian' => 'nullable|numeric',
            'pembayaran_beban_lain_arus' => 'nullable|numeric',
            'kas_bersih_aktivitas_operasi' => 'nullable|numeric',
            'penerimaan_penjualan_unit' => 'nullable|numeric',
            'pembayaran_pembelian_kembali_unit' => 'nullable|numeric',
            'kas_bersih_aktivitas_pendanaan' => 'nullable|numeric',
            'kenaikan_kas_setara_kas' => 'nullable|numeric',
            'total_hasil_investasi' => 'nullable|numeric',
            'hasil_investasi_setelah_biaya' => 'nullable|numeric',
            'persentase_pph'       => 'nullable|numeric',
            'fair_value_level_1'   => 'nullable|numeric',
            'fair_value_level_2'   => 'nullable|numeric',
            'fair_value_level_3'   => 'nullable|numeric',
            'unit_milik_investor'  => 'nullable|numeric',
            'unit_milik_mi'        => 'nullable|numeric',
            'total_unit_beredar'   => 'nullable|numeric',
            'fee_cost_to_performance' => 'nullable|numeric',
            'pendapatan_terhadap_nab' => 'nullable|numeric',
            'beban_terhadap_pendapatan' => 'nullable|numeric',
            'pengelolaan_investasi_terhadap_pendapatan' => 'nullable|numeric',
            'transaction_profit_terhadap_nab' => 'nullable|numeric',
            'ffs_bulan'            => 'required_if:jenis_laporan,kalender_ffs|nullable|integer|min:1|max:12',
            'ffs_tahun'            => 'required_if:jenis_laporan,kalender_ffs|nullable|integer|min:2000|max:2100',
            'jenis_laporan'        => 'nullable|in:kalender_ffs,laporan_tahunan',
            'periode_awal'         => 'nullable|digits:6',
            'periode_akhir'        => 'nullable|digits:6',
            'tahun_laporan'        => 'nullable|integer|min:2000|max:2100',
            'input_mode'           => 'nullable|in:manual,lengkap,excel,pdf,ai,ai-plus,link-website',
            'pdf_file'             => 'nullable|string',
            'ai_narasi'            => 'nullable|string',
            'ai_output'            => 'nullable|string',
            'ai_narasi_plus'       => 'nullable|string',
            'ai_output_plus'       => 'nullable|string',
            'resume_id'            => 'nullable|integer|exists:analisa_reksa_dana,id',
        ]);

        $submittedMode = $request->input('input_mode') ?: 'manual';
        if (in_array($request->input_mode, ['lengkap', 'ai', 'ai-plus', 'link-website'], true)) {
            $request->merge(['input_mode' => 'manual']);
        }
        $request->merge(['saved_mode' => $submittedMode]);

        if ($request->input_mode === 'excel') {
            return $this->storeFromExcel($request);
        }

        return $this->storeFromManual($request);
    }

    private function storeFromManual(Request $request)
    {
        $isSimpan = $request->boolean('simpan');

        $request->validate([
            'sektor'                    => 'nullable|array',
            'sektor.*.nama_sektor'      => 'nullable|string',
            'sektor.*.bobot'            => 'nullable|numeric',
            'efek'                      => 'nullable|array',
            'efek.*.kode_efek'          => 'nullable|string',
            'efek.*.nama_efek'          => 'nullable|string',
            'efek.*.sektor'             => 'nullable|string',
            'efek.*.bobot'              => 'nullable|numeric',
            'efek.*.kontribusi_kinerja' => 'nullable|numeric',
            'efek.*.market_cap'         => 'nullable|numeric',
            'efek.*.nilai_pasar'        => 'nullable|numeric',
            'efek.*.bobot_seharusnya'   => 'nullable|numeric',
            'efek.*.kontribusi_return'  => 'nullable|numeric',
            'efek.*.return_1m'          => 'nullable|numeric',
            'efek.*.return_3m'          => 'nullable|numeric',
            'efek.*.return_6m'          => 'nullable|numeric',
            'efek.*.return_1y'          => 'nullable|numeric',
            'efek.*.ihsg_contribution'  => 'nullable|numeric',
            'efek.*.effect_type'        => 'nullable|string',
            'kinerja'                   => 'nullable|array',
            'kinerja.*.periode'         => 'nullable|date',
            'kinerja.*.return_pct'      => 'nullable|numeric',
            'obligasi'                  => 'nullable|array',
            'obligasi.*.kode_obligasi'  => 'nullable|string',
            'obligasi.*.nama_obligasi'  => 'nullable|string',
            'obligasi.*.bobot'          => 'nullable|numeric',
            'obligasi.*.durasi'         => 'nullable|numeric',
            'obligasi.*.rating'         => 'nullable|string',
            'obligasi.*.nilai_pasar'    => 'nullable|numeric',
            'obligasi.*.return_1m'      => 'nullable|numeric',
            'obligasi.*.return_3m'      => 'nullable|numeric',
            'obligasi.*.return_6m'      => 'nullable|numeric',
            'obligasi.*.return_1y'      => 'nullable|numeric',
            'sukuk'                     => 'nullable|array',
            'sukuk.*.kode_sukuk'        => 'nullable|string',
            'sukuk.*.nama_sukuk'        => 'nullable|string',
            'sukuk.*.jenis_sukuk'       => 'nullable|string|in:Negara,Korporasi',
            'sukuk.*.bobot'             => 'nullable|numeric|min:0|max:100',
            'sukuk.*.yield'             => 'nullable|numeric',
            'sukuk.*.jatuh_tempo'       => 'nullable|string|max:10',
            'sukuk.*.rating'            => 'nullable|string|max:20',
            'bank'                      => 'nullable|array',
            'bank.*.nama_bank'          => 'nullable|string',
            'bank.*.bobot'              => 'nullable|numeric',
            'bank.*.car'                => 'nullable|numeric',
            'bank.*.npl'                => 'nullable|numeric',
            'bank.*.klasifikasi_risiko' => 'nullable|string',
            'bank.*.jenis_bank'         => 'nullable|string|in:Bank Nasional,Bank Asing,BPD,BPR',
            'bank.*.nilai_pasar'        => 'nullable|numeric',
            'bank.*.return_1m'          => 'nullable|numeric',
            'bank.*.return_3m'          => 'nullable|numeric',
            'bank.*.return_6m'          => 'nullable|numeric',
            'bank.*.return_1y'          => 'nullable|numeric',
            'alokasi_aset'                  => 'nullable|array',
            'alokasi_aset.*.nama_aset'      => 'nullable|string',
            'alokasi_aset.*.persentase'     => 'nullable|numeric',
            'likuiditas'                    => 'nullable|array',
            'likuiditas.*.kategori'         => 'nullable|string',
            'likuiditas.*.kode_efek'        => 'nullable|string',
            'likuiditas.*.nama_efek'        => 'nullable|string',
            'likuiditas.*.rata_volume_transaksi_harian' => 'nullable|numeric',
            'likuiditas.*.volume_terendah'  => 'nullable|numeric',
            'likuiditas.*.volume_saham'     => 'nullable|numeric',
            'likuiditas.*.skenario_20_persen_reds' => 'nullable|numeric',
            'likuiditas.*.skenario_reds_closing_10' => 'nullable|numeric',
            'likuiditas.*.rasio_likuiditas_harian'  => 'nullable|numeric',
            'likuiditas.*.rasio_likuiditas' => 'nullable|numeric',
            'keuangan'                      => 'nullable|array',
            'keuangan.*.kategori'           => 'nullable|string',
            'keuangan.*.kode_efek'          => 'nullable|string',
            'keuangan.*.nama_efek'          => 'nullable|string',
            'keuangan.*.per'                => 'nullable|numeric',
            'keuangan.*.pbv'                => 'nullable|numeric',
            'keuangan.*.roe'                => 'nullable|numeric',
            'keuangan.*.roa'                => 'nullable|numeric',
            'keuangan.*.npm'                => 'nullable|numeric',
            'keuangan.*.ev_ebitda'          => 'nullable|numeric',
            'keuangan.*.der'                => 'nullable|numeric',
            'keuangan.*.current_ratio'      => 'nullable|numeric',
            'keuangan.*.aktivitas_lancar'   => 'nullable|numeric',
            'keuangan.*.gross_profit_margin' => 'nullable|numeric',
            'keuangan.*.operating_profit_margin' => 'nullable|numeric',
            'keuangan.*.ytm'                => 'nullable|numeric',
            'keuangan.*.rating'             => 'nullable|string',
            'keuangan.*.kupon'              => 'nullable|numeric',
            'keuangan.*.tenor'              => 'nullable|numeric',
            'keuangan.*.durasi'             => 'nullable|numeric',
            'keuangan.*.shadow_rating'      => 'nullable|string',
            'keuangan.*.npl'                => 'nullable|numeric',
            'keuangan.*.car'                => 'nullable|numeric',
            'keuangan.*.ldr'                => 'nullable|numeric',
            'keuangan.*.nim'                => 'nullable|numeric',
            'keuangan.*.cir'                => 'nullable|numeric',
        ]);

        $this->validateAlokasiAsetTotal($request);

        DB::transaction(function () use ($request, $isSimpan) {
            $pdfPath = $this->resolvePdfPath($request->pdf_file);

            $payload = [
                'user_id'              => auth()->id(),
                'product_type'         => $this->productType,
                'kode_reksa_dana'      => $request->kode_reksa_dana ? strtoupper($request->kode_reksa_dana) : null,
                'reksa_dana_id'        => $this->resolveReksaDanaId($request->kode_reksa_dana, $request->nama_reksa_dana),
                'nama_reksa_dana'      => $request->nama_reksa_dana,
                'jenis_reksa_dana'     => $request->jenis_reksa_dana,
                'kategori'             => $request->kategori ?? [],
                'benchmark'            => $request->benchmark,
                'manajer_investasi'    => $request->manajer_investasi,
                'bank_kustodian'       => $request->bank_kustodian,
                'tanggal_peluncuran'   => $request->tanggal_peluncuran,
                'tujuan_investasi'     => $request->tujuan_investasi,
                'kebijakan_investasi'  => $request->kebijakan_investasi,
                'total_aum'            => $request->total_aum,
                'unit_penyertaan'      => $request->unit_penyertaan,
                'nab_per_unit'         => $request->nab_per_unit,
                'total_marcap_10_efek' => $request->total_marcap_10_efek,
                'tanggal_data'         => $request->tanggal_data,
                'ffs_bulan'            => $request->ffs_bulan,
                'ffs_tahun'            => $request->ffs_tahun,
                'jenis_laporan'        => $request->jenis_laporan ?: 'laporan_tahunan',
                'periode_awal'         => $request->periode_awal,
                'periode_akhir'        => $request->periode_akhir,
                'tahun_laporan'        => $request->tahun_laporan,
                'return_ytd'           => $request->return_ytd,
                'return_1y'            => $request->return_1y,
                'total_return'         => $request->total_return,
                'biaya_operasi'        => $request->biaya_operasi,
                'portfolio_turnover_ratio' => $request->portfolio_turnover_ratio,
                'management_fee'       => $request->management_fee,
                'custodian_fee'        => $request->custodian_fee,
                'investment_manager_fee' => $request->investment_manager_fee,
                'total_aset'           => $request->total_aset,
                'total_liabilitas'     => $request->total_liabilitas,
                'nilai_aset_bersih'  => $request->nilai_aset_bersih,
                'kas_dan_bank'         => $request->kas_dan_bank,
                'piutang_bunga'        => $request->piutang_bunga,
                'piutang_dividen'      => $request->piutang_dividen,
                'piutang_lain'         => $request->piutang_lain,
                'utang_pajak'          => $request->utang_pajak,
                'utang_lain'           => $request->utang_lain,
                'pendapatan_bunga'     => $request->pendapatan_bunga,
                'pendapatan_dividen'   => $request->pendapatan_dividen,
                'gain_realized'        => $request->gain_realized,
                'gain_unrealized'      => $request->gain_unrealized,
                'beban_mi'             => $request->beban_mi,
                'beban_kustodian'      => $request->beban_kustodian,
                'beban_lain'           => $request->beban_lain,
                'laba_bersih'          => $request->laba_bersih,
                'arus_kas_operasi'     => $request->arus_kas_operasi,
                'arus_kas_pendanaan'   => $request->arus_kas_pendanaan,
                'kas_awal_tahun'       => $request->kas_awal_tahun,
                'kas_akhir_tahun'      => $request->kas_akhir_tahun,
                'kas'                  => $request->kas,
                'portofolio_efek'      => $request->portofolio_efek,
                'instrumen_pasar_uang' => $request->instrumen_pasar_uang,
                'piutang_transaksi_efek' => $request->piutang_transaksi_efek,
                'piutang_bunga_dan_dividen' => $request->piutang_bunga_dan_dividen,
                'uang_muka_diterima'   => $request->uang_muka_diterima,
                'liabilitas_pembelian_kembali' => $request->liabilitas_pembelian_kembali,
                'beban_akrual'         => $request->beban_akrual,
                'liabilitas_atas_biaya' => $request->liabilitas_atas_biaya,
                'pembelian_kembali_unit_penyertaan' => $request->pembelian_kembali_unit_penyertaan,
                'utang_pajak_lainnya'  => $request->utang_pajak_lainnya,
                'pendapatan_investasi' => $request->pendapatan_investasi,
                'pendapatan_lainnya'   => $request->pendapatan_lainnya,
                'total_pendapatan'   => $request->total_pendapatan,
                'beban_investasi'      => $request->beban_investasi,
                'beban_pengelolaan_investasi' => $request->beban_pengelolaan_investasi,
                'total_beban'            => $request->total_beban,
                'laba_sebelum_pajak'     => $request->laba_sebelum_pajak,
                'beban_pajak_penghasilan' => $request->beban_pajak_penghasilan,
                'laba_bersih_tahun_berjalan' => $request->laba_bersih_tahun_berjalan,
                'penghasilan_komprehensif_lain_setelah_pajak' => $request->penghasilan_komprehensif_lain_setelah_pajak,
                'penghasilan_komprehensif_tahun_berjalan' => $request->penghasilan_komprehensif_tahun_berjalan,
                'pembelian_efek_ekuitas' => $request->pembelian_efek_ekuitas,
                'penjualan_efek_ekuitas' => $request->penjualan_efek_ekuitas,
                'penerimaan_bunga_deposito' => $request->penerimaan_bunga_deposito,
                'penerimaan_bunga_jasa_giro' => $request->penerimaan_bunga_jasa_giro,
                'penerimaan_dividen_kas' => $request->penerimaan_dividen_kas,
                'pembayaran_jasa_pengelolaan' => $request->pembayaran_jasa_pengelolaan,
                'pembayaran_jasa_kustodian' => $request->pembayaran_jasa_kustodian,
                'pembayaran_beban_lain_arus' => $request->pembayaran_beban_lain_arus,
                'kas_bersih_aktivitas_operasi' => $request->kas_bersih_aktivitas_operasi,
                'penerimaan_penjualan_unit' => $request->penerimaan_penjualan_unit,
                'pembayaran_pembelian_kembali_unit' => $request->pembayaran_pembelian_kembali_unit,
                'kas_bersih_aktivitas_pendanaan' => $request->kas_bersih_aktivitas_pendanaan,
                'kenaikan_kas_setara_kas' => $request->kenaikan_kas_setara_kas,
                'total_hasil_investasi' => $request->total_hasil_investasi,
                'hasil_investasi_setelah_biaya' => $request->hasil_investasi_setelah_biaya,
                'persentase_pph'       => $request->persentase_pph,
                'fair_value_level_1'  => $request->fair_value_level_1,
                'fair_value_level_2'  => $request->fair_value_level_2,
                'fair_value_level_3'  => $request->fair_value_level_3,
                'unit_milik_investor' => $request->unit_milik_investor,
                'unit_milik_mi'       => $request->unit_milik_mi,
                'total_unit_beredar'  => $request->total_unit_beredar,
                'fee_cost_to_performance' => $request->fee_cost_to_performance,
                'pendapatan_terhadap_nab' => $request->pendapatan_terhadap_nab,
                'beban_terhadap_pendapatan' => $request->beban_terhadap_pendapatan,
                'pengelolaan_investasi_terhadap_pendapatan' => $request->pengelolaan_investasi_terhadap_pendapatan,
                'transaction_profit_terhadap_nab' => $request->transaction_profit_terhadap_nab,
                'data_tahunan'         => $this->parseDataTahunan($request->data_tahunan),
                'status'               => $isSimpan ? 'input_manual' : 'submitted',
                'mode'                 => $request->input('saved_mode', $request->input_mode ?: 'manual'),
                'pdf_path'             => $pdfPath,
            ];

            $analisa = $this->resumeDraftFromRequest($request);
            if ($analisa) {
                unset($payload['user_id']);
                $analisa->update($payload);
                $analisa->sektor()->delete();
                $analisa->efek()->delete();
                $analisa->kinerja()->delete();
                $analisa->obligasi()->delete();
                $analisa->sukuk()->delete();
                $analisa->bank()->delete();
                $analisa->alokasiAset()->delete();
                $analisa->pasarUang()->delete();
                $analisa->piutangBungaDetail()->delete();
                $analisa->likuiditas()->delete();
                $analisa->keuangan()->delete();
            } else {
                $analisa = AnalisaReksaDana::create($payload);
            }

            $sektor   = collect($request->sektor)->filter(fn($r) => !empty($r['nama_sektor']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $efek     = collect($request->efek)->filter(fn($r) => !empty($r['nama_efek']) && (isset($r['bobot']) && $r['bobot'] !== ''))->values()->all();
            $kinerja  = collect($request->kinerja)->filter(fn($r) => !empty($r['periode']) && isset($r['return_pct']) && $r['return_pct'] !== '')->values()->all();
            $obligasi = collect($request->obligasi)->filter(fn($r) => !empty($r['kode_obligasi']) && !empty($r['nama_obligasi']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $sukuk    = collect($request->sukuk)->filter(fn($r) => !empty($r['kode_sukuk']) && !empty($r['nama_sukuk']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $bank     = collect($request->bank)->filter(fn($r) => !empty($r['nama_bank']) && (isset($r['bobot']) && $r['bobot'] !== '' || !empty($r['saldo'])))->values()->all();
            $alokasiAset = $this->filteredAlokasiAset($request);
            $pasarUang = collect($request->pasar_uang ?? [])->filter(fn($r) => !empty($r['nama_instrumen']))->values()->all();
            $piutangBungaDetail = collect($request->piutang_bunga_detail ?? [])->filter(fn($r) => !empty($r['jenis_instrumen']) && !empty($r['jumlah']))->values()->all();
            $likuiditas = collect($request->likuiditas ?? [])->filter(fn($r) => !empty($r['kode_efek']))->values()->all();
            $keuangan   = collect($request->keuangan ?? [])
                ->filter(fn($r) => !empty($r['kode_efek']))
                ->map(fn($r) => array_merge($r, ['nama_efek' => $r['nama_efek'] ?? $r['kode_efek']]))
                ->values()->all();

            if ($sektor)   $analisa->sektor()->createMany($sektor);
            if ($efek)     $analisa->efek()->createMany($efek);
            if ($kinerja)  $analisa->kinerja()->createMany($kinerja);
            if ($obligasi) $analisa->obligasi()->createMany($obligasi);
            if ($sukuk)    $analisa->sukuk()->createMany($sukuk);
            if ($bank)     $analisa->bank()->createMany($bank);
            if ($alokasiAset) $analisa->alokasiAset()->createMany($alokasiAset);
            if ($pasarUang) $analisa->pasarUang()->createMany($pasarUang);
            if ($piutangBungaDetail) $analisa->piutangBungaDetail()->createMany($piutangBungaDetail);
            if ($likuiditas) $analisa->likuiditas()->createMany($likuiditas);
            if ($keuangan)   $analisa->keuangan()->createMany($keuangan);

            if (!$isSimpan) {
                $this->persistAiFromRequest($request, $analisa);
            }
        });

        $exportUrl = $this->generateExportExcel($request);

        if ($isSimpan) {
            return redirect()->route($this->indexRoute())->with('success', 'Data berhasil disimpan sebagai Input Manual.')->with('export_file', $exportUrl);
        }

        return redirect()->route($this->indexRoute())->with('success', 'Data analisa berhasil disubmit. Narasi AI sedang diproses.')->with('export_file', $exportUrl);
    }

    private function generateExportExcel(Request $request): ?string
    {
        $dir = storage_path('app/public/export');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = 'analisa-export-' . now()->format('Ymd-His') . '-' . uniqid() . '.xlsx';
        $path = $dir . '/' . $filename;

        try {
            $export = new AnalisaExcelExport;
            $export->export($request->all(), $path);
            return Storage::url('export/' . $filename);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseDataTahunan($value): ?array
    {
        if (empty($value)) {
            return null;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : null;
        }
        if (is_array($value)) {
            return $value;
        }
        return null;
    }

    private function storeFromExcel(Request $request)
    {
        $isSimpan = $request->boolean('simpan');

        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        DB::transaction(function () use ($request, $isSimpan) {
            $pdfPath = $this->resolvePdfPath($request->pdf_file);

            $payload = [
                'user_id'              => auth()->id(),
                'product_type'         => $this->productType,
                'kode_reksa_dana'      => $request->kode_reksa_dana ? strtoupper($request->kode_reksa_dana) : null,
                'reksa_dana_id'        => $this->resolveReksaDanaId($request->kode_reksa_dana, $request->nama_reksa_dana),
                'nama_reksa_dana'      => $request->nama_reksa_dana,
                'jenis_reksa_dana'     => $request->jenis_reksa_dana,
                'kategori'             => $request->kategori ?? [],
                'benchmark'            => $request->benchmark,
                'manajer_investasi'    => $request->manajer_investasi,
                'bank_kustodian'       => $request->bank_kustodian,
                'tanggal_peluncuran'   => $request->tanggal_peluncuran,
                'tujuan_investasi'     => $request->tujuan_investasi,
                'kebijakan_investasi'  => $request->kebijakan_investasi,
                'total_aum'            => $request->total_aum,
                'unit_penyertaan'      => $request->unit_penyertaan,
                'nab_per_unit'         => $request->nab_per_unit,
                'total_marcap_10_efek' => $request->total_marcap_10_efek,
                'tanggal_data'         => $request->tanggal_data,
                'ffs_bulan'            => $request->ffs_bulan,
                'ffs_tahun'            => $request->ffs_tahun,
                'jenis_laporan'        => $request->jenis_laporan ?: 'laporan_tahunan',
                'periode_awal'         => $request->periode_awal,
                'periode_akhir'        => $request->periode_akhir,
                'tahun_laporan'        => $request->tahun_laporan,
                'return_ytd'           => $request->return_ytd,
                'return_1y'            => $request->return_1y,
                'total_return'         => $request->total_return,
                'biaya_operasi'        => $request->biaya_operasi,
                'portfolio_turnover_ratio' => $request->portfolio_turnover_ratio,
                'management_fee'       => $request->management_fee,
                'custodian_fee'        => $request->custodian_fee,
                'investment_manager_fee' => $request->investment_manager_fee,
                'total_aset'           => $request->total_aset,
                'total_liabilitas'     => $request->total_liabilitas,
                'nilai_aset_bersih'  => $request->nilai_aset_bersih,
                'kas_dan_bank'         => $request->kas_dan_bank,
                'piutang_bunga'        => $request->piutang_bunga,
                'piutang_dividen'      => $request->piutang_dividen,
                'piutang_lain'         => $request->piutang_lain,
                'utang_pajak'          => $request->utang_pajak,
                'utang_lain'           => $request->utang_lain,
                'pendapatan_bunga'     => $request->pendapatan_bunga,
                'pendapatan_dividen'   => $request->pendapatan_dividen,
                'gain_realized'        => $request->gain_realized,
                'gain_unrealized'      => $request->gain_unrealized,
                'beban_mi'             => $request->beban_mi,
                'beban_kustodian'      => $request->beban_kustodian,
                'beban_lain'           => $request->beban_lain,
                'laba_bersih'          => $request->laba_bersih,
                'arus_kas_operasi'     => $request->arus_kas_operasi,
                'arus_kas_pendanaan'   => $request->arus_kas_pendanaan,
                'kas_awal_tahun'       => $request->kas_awal_tahun,
                'kas_akhir_tahun'      => $request->kas_akhir_tahun,
                'kas'                  => $request->kas,
                'portofolio_efek'      => $request->portofolio_efek,
                'instrumen_pasar_uang' => $request->instrumen_pasar_uang,
                'piutang_transaksi_efek' => $request->piutang_transaksi_efek,
                'piutang_bunga_dan_dividen' => $request->piutang_bunga_dan_dividen,
                'uang_muka_diterima'   => $request->uang_muka_diterima,
                'liabilitas_pembelian_kembali' => $request->liabilitas_pembelian_kembali,
                'beban_akrual'         => $request->beban_akrual,
                'liabilitas_atas_biaya' => $request->liabilitas_atas_biaya,
                'pembelian_kembali_unit_penyertaan' => $request->pembelian_kembali_unit_penyertaan,
                'utang_pajak_lainnya'  => $request->utang_pajak_lainnya,
                'pendapatan_investasi' => $request->pendapatan_investasi,
                'pendapatan_lainnya'   => $request->pendapatan_lainnya,
                'total_pendapatan'   => $request->total_pendapatan,
                'beban_investasi'      => $request->beban_investasi,
                'beban_pengelolaan_investasi' => $request->beban_pengelolaan_investasi,
                'total_beban'            => $request->total_beban,
                'laba_sebelum_pajak'     => $request->laba_sebelum_pajak,
                'beban_pajak_penghasilan' => $request->beban_pajak_penghasilan,
                'laba_bersih_tahun_berjalan' => $request->laba_bersih_tahun_berjalan,
                'penghasilan_komprehensif_lain_setelah_pajak' => $request->penghasilan_komprehensif_lain_setelah_pajak,
                'penghasilan_komprehensif_tahun_berjalan' => $request->penghasilan_komprehensif_tahun_berjalan,
                'pembelian_efek_ekuitas' => $request->pembelian_efek_ekuitas,
                'penjualan_efek_ekuitas' => $request->penjualan_efek_ekuitas,
                'penerimaan_bunga_deposito' => $request->penerimaan_bunga_deposito,
                'penerimaan_bunga_jasa_giro' => $request->penerimaan_bunga_jasa_giro,
                'penerimaan_dividen_kas' => $request->penerimaan_dividen_kas,
                'pembayaran_jasa_pengelolaan' => $request->pembayaran_jasa_pengelolaan,
                'pembayaran_jasa_kustodian' => $request->pembayaran_jasa_kustodian,
                'pembayaran_beban_lain_arus' => $request->pembayaran_beban_lain_arus,
                'kas_bersih_aktivitas_operasi' => $request->kas_bersih_aktivitas_operasi,
                'penerimaan_penjualan_unit' => $request->penerimaan_penjualan_unit,
                'pembayaran_pembelian_kembali_unit' => $request->pembayaran_pembelian_kembali_unit,
                'kas_bersih_aktivitas_pendanaan' => $request->kas_bersih_aktivitas_pendanaan,
                'kenaikan_kas_setara_kas' => $request->kenaikan_kas_setara_kas,
                'total_hasil_investasi' => $request->total_hasil_investasi,
                'hasil_investasi_setelah_biaya' => $request->hasil_investasi_setelah_biaya,
                'persentase_pph'       => $request->persentase_pph,
                'fair_value_level_1'  => $request->fair_value_level_1,
                'fair_value_level_2'  => $request->fair_value_level_2,
                'fair_value_level_3'  => $request->fair_value_level_3,
                'unit_milik_investor' => $request->unit_milik_investor,
                'unit_milik_mi'       => $request->unit_milik_mi,
                'total_unit_beredar'  => $request->total_unit_beredar,
                'fee_cost_to_performance' => $request->fee_cost_to_performance,
                'pendapatan_terhadap_nab' => $request->pendapatan_terhadap_nab,
                'beban_terhadap_pendapatan' => $request->beban_terhadap_pendapatan,
                'pengelolaan_investasi_terhadap_pendapatan' => $request->pengelolaan_investasi_terhadap_pendapatan,
                'transaction_profit_terhadap_nab' => $request->transaction_profit_terhadap_nab,
                'data_tahunan'         => $this->parseDataTahunan($request->data_tahunan),
                'status'               => $isSimpan ? 'input_manual' : 'submitted',
                'mode'                 => $request->input('saved_mode', $request->input_mode ?: 'excel'),
                'pdf_path'             => $pdfPath,
            ];

            $analisa = $this->resumeDraftFromRequest($request);
            if ($analisa) {
                unset($payload['user_id']);
                $analisa->update($payload);
                $analisa->sektor()->delete();
                $analisa->efek()->delete();
                $analisa->kinerja()->delete();
                $analisa->obligasi()->delete();
                $analisa->sukuk()->delete();
                $analisa->bank()->delete();
                $analisa->alokasiAset()->delete();
                $analisa->pasarUang()->delete();
                $analisa->piutangBungaDetail()->delete();
            } else {
                $analisa = AnalisaReksaDana::create($payload);
            }

            Excel::import(new AnalisaImport($analisa), $request->file('file_excel'));

            if (!$isSimpan) {
                $this->persistAiFromRequest($request, $analisa);
            }
        });

        if ($isSimpan) {
            return redirect()->route($this->indexRoute())->with('success', 'Data berhasil disimpan sebagai Input Manual.');
        }

        return redirect()->route($this->indexRoute())->with('success', 'Data analisa berhasil diimport dari Excel. Narasi AI sedang diproses.');
    }

    private function persistAiFromRequest(Request $request, AnalisaReksaDana $analisa): void
    {
        $analisa->load(['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasiAset', 'likuiditas', 'keuangan']);

        if ($request->filled('ai_narasi') && $request->filled('ai_output')) {
            $analisa->update([
                'ai_narasi' => $request->ai_narasi,
                'ai_output' => json_decode($request->ai_output, true) ?: [],
            ]);
        } else {
            AnalisaAiJob::dispatch($analisa->id);
        }

        if ($request->filled('ai_narasi_plus') && $request->filled('ai_output_plus')) {
            $analisa->update([
                'ai_narasi_plus' => $request->ai_narasi_plus,
                'ai_output_plus' => json_decode($request->ai_output_plus, true) ?: [],
            ]);
        } elseif (AnalisaAiValidator::hasPlusManualData($analisa)) {
            AnalisaAiPlusJob::dispatch($analisa->id);
        } else {
            $analisa->update([
                'ai_output_plus' => [
                    'error'   => true,
                    'message' => AnalisaAiValidator::plusIncompleteMessage($analisa),
                    'missing' => AnalisaAiValidator::plusMissingSections($analisa),
                ],
            ]);
        }
    }

    private function resumeDraftFromRequest(Request $request): ?AnalisaReksaDana
    {
        if (!$request->filled('resume_id')) {
            return null;
        }

        return AnalisaReksaDana::where('product_type', $this->productType)
            ->when(!$this->isAdminContext, fn($query) => $query->where('user_id', auth()->id()))
            ->find($request->integer('resume_id'));
    }

    private function resolveReksaDanaId(?string $kode, ?string $nama): ?int
    {
        if ($kode) {
            $rd = ReksaDana::where('kode_reksa_dana', strtoupper($kode))->first();
            if ($rd) return $rd->id;
        }

        if ($nama) {
            $rd = ReksaDana::where('nama_reksa_dana', $nama)->first();
            if ($rd) return $rd->id;
        }

        return null;
    }

    private function resolvePdfPath(?string $pdfFile): ?string
    {
        if (!$pdfFile) return null;

        $tempPath = 'analisa-pdfs/' . basename($pdfFile);

        if (!Storage::disk('public')->exists($tempPath)) {
            return null;
        }

        return $tempPath;
    }

    private function filteredAlokasiAset(Request $request): array
    {
        return collect($request->alokasi_aset ?? [])
            ->filter(fn($r) => !empty($r['nama_aset']) && isset($r['persentase']) && $r['persentase'] !== '')
            ->map(fn($r) => [
                'nama_aset' => $r['nama_aset'],
                'persentase' => $r['persentase'],
            ])
            ->values()
            ->all();
    }

    private function validateAlokasiAsetTotal(Request $request): void
    {
        $alokasiAset = $this->filteredAlokasiAset($request);
        if ($alokasiAset === []) {
            return;
        }

        $total = collect($alokasiAset)->sum(fn($r) => (float) $r['persentase']);
        if (abs($total - 100) > 0.01) {
            validator([], [])->after(function ($validator) use ($total) {
                $validator->errors()->add('alokasi_aset', 'Total Alokasi Aset harus 100%. Total saat ini: ' . number_format($total, 2, ',', '.') . '%.');
            })->validate();
        }
    }

    private function getFfsPembandingOptions($reksaDanaId, $excludeMonth = null, $excludeYear = null, $kodeReksaDana = null): array
    {
        if (!$reksaDanaId && $kodeReksaDana) {
            $master = ReksaDana::where('kode_reksa_dana', $kodeReksaDana)->first();
            $reksaDanaId = $master?->id;
        }
        if (!$reksaDanaId) return [];

        $docQuery = ReksaDanaDocument::where('reksa_dana_id', $reksaDanaId)
            ->where('document_type', 'ffs')
            ->whereHas('ffsExtractionResults');

        if ($excludeMonth && $excludeYear) {
            $docQuery->where(function ($q) use ($excludeMonth, $excludeYear) {
                $q->where('ffs_month', '!=', $excludeMonth)
                    ->orWhere('ffs_year', '!=', $excludeYear);
            });
        }

        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return $docQuery->orderBy('ffs_year')->orderBy('ffs_month')->get()
            ->map(function ($doc) use ($months) {
                $extraction = $doc->ffsExtractionResults()->latest('tanggal_data')->first();
                if (!$extraction) return null;

                $ed = $extraction->extracted_data ?: [];
                $efekList = $ed['daftar_efek'] ?? $ed['efek'] ?? [];
                return [
                    'id' => $extraction->id,
                    'label' => ($months[$doc->ffs_month] ?? '') . ' ' . $doc->ffs_year,
                    'efek' => collect($efekList)
                        ->filter(fn($e) => !empty($e['kode_efek']))
                        ->map(fn($e) => [
                            'kode_efek' => $e['kode_efek'],
                            'bobot_seharusnya' => $e['bobot_seharusnya'] ?? null,
                            'kontribusi_return' => $e['kontribusi_return'] ?? null,
                        ])
                        ->values(),
                ];
            })->filter()->values()->toArray();
    }

    private function serializeAnalisaForForm(AnalisaReksaDana $analisa): array
    {
        return [
            'id'                    => $analisa->id,
            'kode_reksa_dana'      => $analisa->kode_reksa_dana,
            'nama_reksa_dana'      => $analisa->nama_reksa_dana,
            'jenis_reksa_dana'     => $analisa->jenis_reksa_dana,
            'kategori'             => $analisa->kategori ?? [],
            'benchmark'            => $analisa->benchmark,
            'manajer_investasi'    => $analisa->manajer_investasi,
            'bank_kustodian'       => $analisa->bank_kustodian,
            'tanggal_peluncuran'   => $analisa->tanggal_peluncuran?->format('Y-m-d'),
            'tujuan_investasi'     => $analisa->tujuan_investasi,
            'kebijakan_investasi'  => $analisa->kebijakan_investasi,
            'total_aum'            => $analisa->total_aum,
            'unit_penyertaan'      => $analisa->unit_penyertaan,
            'nab_per_unit'         => $analisa->nab_per_unit,
            'total_marcap_10_efek' => $analisa->total_marcap_10_efek,
            'tanggal_data'         => $analisa->tanggal_data?->format('Y-m-d'),
            'ffs_bulan'            => $analisa->ffs_bulan,
            'ffs_tahun'            => $analisa->ffs_tahun,
            'jenis_laporan'        => $analisa->jenis_laporan,
            'periode_awal'         => $analisa->periode_awal,
            'periode_akhir'        => $analisa->periode_akhir,
            'tahun_laporan'        => $analisa->tahun_laporan,
            'mode'                 => $analisa->mode,
            'return_ytd'           => $analisa->return_ytd,
            'return_1y'            => $analisa->return_1y,
            'total_return'         => $analisa->total_return,
            'biaya_operasi'        => $analisa->biaya_operasi,
            'portfolio_turnover_ratio' => $analisa->portfolio_turnover_ratio,
            'management_fee'       => $analisa->management_fee,
            'custodian_fee'        => $analisa->custodian_fee,
            'sektor'               => $analisa->sektor->map(fn($s) => ['nama_sektor' => $s->nama_sektor, 'bobot' => $s->bobot])->values(),
            'efek'                 => $analisa->efek->map(fn($e) => [
                'kode_efek' => $e->kode_efek,
                'nama_efek' => $e->nama_efek,
                'sektor' => $e->sektor,
                'bobot' => $e->bobot,
                'bobot_seharusnya' => $e->bobot_seharusnya,
                'kontribusi_kinerja' => $e->kontribusi_kinerja,
                'market_cap' => $e->market_cap,
                'nilai_pasar' => $e->nilai_pasar,
                'return_1m' => $e->return_1m,
                'return_3m' => $e->return_3m,
                'return_6m' => $e->return_6m,
                'return_1y' => $e->return_1y,
                'ihsg_contribution' => $e->ihsg_contribution,
                'kontribusi_return' => $e->kontribusi_return,
                'effect_type' => $e->effect_type,
                'top_10' => $e->top_10,
                'harga_perolehan' => $e->harga_perolehan,
                'persen_nab' => $e->persen_nab,
            ])->values(),
            'kinerja'              => $analisa->kinerja->map(fn($k) => ['periode' => \Carbon\Carbon::parse($k->periode)->format('Y-m'), 'return_pct' => $k->return_pct])->values(),
            'obligasi'             => $analisa->obligasi->map(fn($o) => [
                'kode_obligasi' => $o->kode_obligasi,
                'nama_obligasi' => $o->nama_obligasi,
                'bobot' => $o->bobot,
                'durasi' => $o->durasi,
                'rating' => $o->rating,
                'nilai_pasar' => $o->nilai_pasar,
                'return_1m' => $o->return_1m,
                'return_3m' => $o->return_3m,
                'return_6m' => $o->return_6m,
                'return_1y' => $o->return_1y,
                'ytm' => $o->ytm,
                'kupon' => $o->kupon,
                'tanggal_jatuh_tempo' => $o->tanggal_jatuh_tempo?->format('Y-m-d'),
                'penerbit' => $o->penerbit,
                'persen_nab' => $o->persen_nab,
            ])->values(),
            'sukuk'                => $analisa->sukuk->map(fn($s) => [
                'kode_sukuk' => $s->kode_sukuk,
                'nama_sukuk' => $s->nama_sukuk,
                'jenis_sukuk' => $s->jenis_sukuk,
                'bobot' => $s->bobot,
                'yield' => $s->yield,
                'jatuh_tempo' => $s->jatuh_tempo,
                'rating' => $s->rating,
                'persen_nab' => $s->persen_nab,
            ])->values(),
            'bank'                 => $analisa->bank->map(fn($b) => [
                'nama_bank' => $b->nama_bank,
                'bobot' => $b->bobot,
                'car' => $b->car,
                'npl' => $b->npl,
                'klasifikasi_risiko' => $b->klasifikasi_risiko,
                'jenis_bank' => $b->jenis_bank,
                'nilai_pasar' => $b->nilai_pasar,
                'return_1m' => $b->return_1m,
                'return_3m' => $b->return_3m,
                'return_6m' => $b->return_6m,
                'return_1y' => $b->return_1y,
                'tingkat_bunga' => $b->tingkat_bunga,
                'jangka_waktu' => $b->jangka_waktu,
                'persen_nab' => $b->persen_nab,
            ])->values(),
            'alokasi_aset'         => $analisa->alokasiAset->map(fn($a) => ['nama_aset' => $a->nama_aset, 'persentase' => $a->persentase])->values(),
            // Laporan Tahunan fields
            'mata_uang'               => $analisa->mata_uang,
            'total_aset'              => $analisa->total_aset,
            'total_liabilitas'        => $analisa->total_liabilitas,
            'nilai_aset_bersih'       => $analisa->nilai_aset_bersih,
            'kas_dan_bank'            => $analisa->kas_dan_bank,
            'piutang_bunga'           => $analisa->piutang_bunga,
            'piutang_dividen'         => $analisa->piutang_dividen,
            'piutang_lain'            => $analisa->piutang_lain,
            'utang_pajak'             => $analisa->utang_pajak,
            'utang_lain'              => $analisa->utang_lain,
            'pendapatan_bunga'        => $analisa->pendapatan_bunga,
            'pendapatan_dividen'      => $analisa->pendapatan_dividen,
            'gain_realized'           => $analisa->gain_realized,
            'gain_unrealized'         => $analisa->gain_unrealized,
            'beban_mi'                => $analisa->beban_mi,
            'beban_kustodian'         => $analisa->beban_kustodian,
            'beban_lain'              => $analisa->beban_lain,
            'laba_bersih'             => $analisa->laba_bersih,
            'arus_kas_operasi'        => $analisa->arus_kas_operasi,
            'arus_kas_pendanaan'      => $analisa->arus_kas_pendanaan,
            'kas_awal_tahun'          => $analisa->kas_awal_tahun,
            'kas_akhir_tahun'         => $analisa->kas_akhir_tahun,
            'kas'                     => $analisa->kas,
            'portofolio_efek'         => $analisa->portofolio_efek,
            'instrumen_pasar_uang'    => $analisa->instrumen_pasar_uang,
            'piutang_transaksi_efek'  => $analisa->piutang_transaksi_efek,
            'piutang_bunga_dan_dividen' => $analisa->piutang_bunga_dan_dividen,
            'uang_muka_diterima'      => $analisa->uang_muka_diterima,
            'liabilitas_pembelian_kembali' => $analisa->liabilitas_pembelian_kembali,
            'beban_akrual'            => $analisa->beban_akrual,
            'liabilitas_atas_biaya'   => $analisa->liabilitas_atas_biaya,
            'pembelian_kembali_unit_penyertaan' => $analisa->pembelian_kembali_unit_penyertaan,
            'utang_pajak_lainnya'     => $analisa->utang_pajak_lainnya,
            'pendapatan_investasi'    => $analisa->pendapatan_investasi,
            'pendapatan_lainnya'      => $analisa->pendapatan_lainnya,
            'total_pendapatan'         => $analisa->total_pendapatan,
            'beban_investasi'         => $analisa->beban_investasi,
            'beban_pengelolaan_investasi' => $analisa->beban_pengelolaan_investasi,
            'total_beban'            => $analisa->total_beban,
            'laba_sebelum_pajak'     => $analisa->laba_sebelum_pajak,
            'beban_pajak_penghasilan' => $analisa->beban_pajak_penghasilan,
            'laba_bersih_tahun_berjalan' => $analisa->laba_bersih_tahun_berjalan,
            'penghasilan_komprehensif_lain_setelah_pajak' => $analisa->penghasilan_komprehensif_lain_setelah_pajak,
            'penghasilan_komprehensif_tahun_berjalan' => $analisa->penghasilan_komprehensif_tahun_berjalan,
            'pembelian_efek_ekuitas'  => $analisa->pembelian_efek_ekuitas,
            'penjualan_efek_ekuitas'  => $analisa->penjualan_efek_ekuitas,
            'penerimaan_bunga_deposito' => $analisa->penerimaan_bunga_deposito,
            'penerimaan_bunga_jasa_giro' => $analisa->penerimaan_bunga_jasa_giro,
            'penerimaan_dividen_kas'  => $analisa->penerimaan_dividen_kas,
            'pembayaran_jasa_pengelolaan' => $analisa->pembayaran_jasa_pengelolaan,
            'pembayaran_jasa_kustodian' => $analisa->pembayaran_jasa_kustodian,
            'pembayaran_beban_lain_arus' => $analisa->pembayaran_beban_lain_arus,
            'kas_bersih_aktivitas_operasi' => $analisa->kas_bersih_aktivitas_operasi,
            'penerimaan_penjualan_unit' => $analisa->penerimaan_penjualan_unit,
            'pembayaran_pembelian_kembali_unit' => $analisa->pembayaran_pembelian_kembali_unit,
            'kas_bersih_aktivitas_pendanaan' => $analisa->kas_bersih_aktivitas_pendanaan,
            'kenaikan_kas_setara_kas' => $analisa->kenaikan_kas_setara_kas,
            'total_hasil_investasi'   => $analisa->total_hasil_investasi,
            'hasil_investasi_setelah_biaya' => $analisa->hasil_investasi_setelah_biaya,
            'persentase_pph'          => $analisa->persentase_pph,
            'fair_value_level_1'      => $analisa->fair_value_level_1,
            'fair_value_level_2'      => $analisa->fair_value_level_2,
            'fair_value_level_3'      => $analisa->fair_value_level_3,
            'unit_milik_investor'     => $analisa->unit_milik_investor,
            'unit_milik_mi'           => $analisa->unit_milik_mi,
            'total_unit_beredar'      => $analisa->total_unit_beredar,
            'pasar_uang'              => $analisa->pasarUang->map(fn($p) => [
                'nama_instrumen' => $p->nama_instrumen,
                'jenis_instrumen' => $p->jenis_instrumen,
                'nilai' => $p->nilai,
                'persentase' => $p->persentase,
            ])->values(),
            'piutang_bunga_detail'    => $analisa->piutangBungaDetail->map(fn($p) => [
                'jenis_instrumen' => $p->jenis_instrumen,
                'jumlah' => $p->jumlah,
                'persentase' => $p->persentase,
            ])->values(),
            'likuiditas'              => $analisa->likuiditas->map(fn($l) => [
                'kategori' => $l->kategori,
                'kode_efek' => $l->kode_efek,
                'nama_efek' => $l->nama_efek,
                'rata_volume_transaksi_harian' => $l->rata_volume_transaksi_harian,
                'volume_terendah' => $l->volume_terendah,
                'volume_saham' => $l->volume_saham,
                'skenario_20_persen_reds' => $l->skenario_20_persen_reds,
                'skenario_reds_closing_10' => $l->skenario_reds_closing_10,
                'rasio_likuiditas_harian' => $l->rasio_likuiditas_harian,
                'rasio_likuiditas' => $l->rasio_likuiditas,
            ])->values(),
            'keuangan'                => $analisa->keuangan->map(fn($k) => [
                'kategori' => $k->kategori,
                'kode_efek' => $k->kode_efek,
                'nama_efek' => $k->nama_efek,
                'per' => $k->per,
                'pbv' => $k->pbv,
                'roe' => $k->roe,
                'roa' => $k->roa,
                'npm' => $k->npm,
                'ev_ebitda' => $k->ev_ebitda,
                'der' => $k->der,
                'current_ratio' => $k->current_ratio,
                'aktivitas_lancar' => $k->aktivitas_lancar,
                'gross_profit_margin' => $k->gross_profit_margin,
                'operating_profit_margin' => $k->operating_profit_margin,
                'ytm' => $k->ytm,
                'rating' => $k->rating,
                'kupon' => $k->kupon,
                'tenor' => $k->tenor,
                'durasi' => $k->durasi,
                'shadow_rating' => $k->shadow_rating,
                'npl' => $k->npl,
                'car' => $k->car,
                'ldr' => $k->ldr,
                'nim' => $k->nim,
                'cir' => $k->cir,
            ])->values(),
            'data_tahunan'            => $analisa->data_tahunan ?? null,
        ];
    }

    public function edit(AnalisaReksaDana $analisa)
    {
        abort_if(!$this->isAdminContext && $analisa->user_id !== auth()->id(), 403);
        abort_if(!$this->isAdminContext && $analisa->status === 'reviewed', 403, 'Data yang sudah direview tidak dapat diedit.');
        abort_if($analisa->product_type !== $this->productType, 404);

        $analisa->load(['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasiAset', 'likuiditas', 'keuangan']);

        $formRoutes = array_merge($this->formRoutes(), [
            'update' => $this->isAdminContext
                ? route('admin.analisa-rd.update', $analisa)
                : route('user.analisa.update', $analisa),
            'cancel' => $this->isAdminContext
                ? route('admin.analisa.index')
                : route('user.analisa.index'),
        ]);

        $resumeAnalisa = $this->serializeAnalisaForForm($analisa);
        $resumeMode = $analisa->mode ?: 'manual';
        $isEditMode = true;
        $ffsPembandingOptions = $this->getFfsPembandingOptions(
            $analisa->reksa_dana_id,
            $analisa->ffs_bulan,
            $analisa->ffs_tahun,
            $analisa->kode_reksa_dana
        );

        return view('analisa.create', array_merge(
            ['formRoutes' => $formRoutes, 'productLabel' => $this->productLabel, 'isEditMode' => true],
            $this->dataSourceLinkContext(),
            compact('resumeAnalisa', 'resumeMode', 'ffsPembandingOptions'),
        ));
    }

    public function update(Request $request, AnalisaReksaDana $analisa)
    {
        abort_if(!$this->isAdminContext && $analisa->user_id !== auth()->id(), 403);
        abort_if(!$this->isAdminContext && $analisa->status === 'reviewed', 403, 'Data yang sudah direview tidak dapat diedit.');
        abort_if($analisa->product_type !== $this->productType, 404);

        $request->merge([
            'jenis_laporan' => $request->jenis_laporan ?: 'laporan_tahunan',
            'tahun_laporan' => $request->tahun_laporan ?: now()->year,
            'tanggal_data' => $request->filled('tanggal_data') ? $request->tanggal_data : null,
        ]);

        $request->validate([
            'kode_reksa_dana'      => 'nullable|string|max:20',
            'nama_reksa_dana'      => 'required|string|max:255',
            'jenis_reksa_dana'     => 'required|in:Saham,Pendapatan Tetap,Campuran,Pasar Uang,Terproteksi,Global,DIRE-DINFRA,Penyertaan terbatas',
            'kategori'             => 'nullable|array',
            'kategori.*'           => 'in:Konvensional,Syariah,index,ETF',
            'benchmark'            => 'nullable|string|max:255',
            'manajer_investasi'    => 'nullable|string|max:255',
            'bank_kustodian'       => 'nullable|string|max:255',
            'tanggal_peluncuran'   => 'nullable|date',
            'tujuan_investasi'     => 'nullable|string',
            'kebijakan_investasi'  => 'nullable|string',
            'total_aum'            => 'nullable|numeric|min:0',
            'unit_penyertaan'      => 'nullable|numeric|min:0',
            'nab_per_unit'         => 'nullable|numeric|min:0',
            'total_marcap_10_efek' => 'nullable|numeric|min:0',
            'tanggal_data'         => 'nullable|date',
            'ffs_bulan'            => 'nullable|integer|min:1|max:12',
            'ffs_tahun'            => 'nullable|integer|min:2000|max:2100',
            'jenis_laporan'        => 'nullable|in:kalender_ffs,laporan_tahunan',
            'periode_awal'         => 'nullable|digits:6',
            'periode_akhir'        => 'nullable|digits:6',
            'tahun_laporan'        => 'nullable|integer|min:2000|max:2100',
            'return_ytd'           => 'nullable|numeric',
            'return_1y'            => 'nullable|numeric',
            'total_return'         => 'nullable|numeric',
            'biaya_operasi'        => 'nullable|numeric',
            'portfolio_turnover_ratio' => 'nullable|numeric',
            'management_fee'       => 'nullable|numeric',
            'custodian_fee'        => 'nullable|numeric',
            'total_aset'           => 'nullable|numeric',
            'total_liabilitas'     => 'nullable|numeric',
            'nilai_aset_bersih'  => 'nullable|numeric',
            'kas_dan_bank'         => 'nullable|numeric',
            'piutang_bunga'        => 'nullable|numeric',
            'piutang_dividen'      => 'nullable|numeric',
            'piutang_lain'         => 'nullable|numeric',
            'utang_pajak'          => 'nullable|numeric',
            'utang_lain'           => 'nullable|numeric',
            'pendapatan_bunga'     => 'nullable|numeric',
            'pendapatan_dividen'   => 'nullable|numeric',
            'gain_realized'        => 'nullable|numeric',
            'gain_unrealized'      => 'nullable|numeric',
            'beban_mi'             => 'nullable|numeric',
            'beban_kustodian'      => 'nullable|numeric',
            'beban_lain'           => 'nullable|numeric',
            'laba_bersih'          => 'nullable|numeric',
            'arus_kas_operasi'     => 'nullable|numeric',
            'arus_kas_pendanaan'   => 'nullable|numeric',
            'kas_awal_tahun'       => 'nullable|numeric',
            'kas_akhir_tahun'      => 'nullable|numeric',
            'kas'                  => 'nullable|numeric',
            'portofolio_efek'      => 'nullable|numeric',
            'instrumen_pasar_uang' => 'nullable|numeric',
            'piutang_transaksi_efek' => 'nullable|numeric',
            'piutang_bunga_dan_dividen' => 'nullable|numeric',
            'uang_muka_diterima'   => 'nullable|numeric',
            'liabilitas_pembelian_kembali' => 'nullable|numeric',
            'beban_akrual'         => 'nullable|numeric',
            'liabilitas_atas_biaya' => 'nullable|numeric',
            'pembelian_kembali_unit_penyertaan' => 'nullable|numeric',
            'utang_pajak_lainnya'  => 'nullable|numeric',
            'pendapatan_investasi' => 'nullable|numeric',
            'pendapatan_lainnya'   => 'nullable|numeric',
            'total_pendapatan'   => 'nullable|numeric',
            'beban_investasi'      => 'nullable|numeric',
            'beban_pengelolaan_investasi' => 'nullable|numeric',
            'total_beban'            => 'nullable|numeric',
            'laba_sebelum_pajak'     => 'nullable|numeric',
            'beban_pajak_penghasilan' => 'nullable|numeric',
            'laba_bersih_tahun_berjalan' => 'nullable|numeric',
            'penghasilan_komprehensif_lain_setelah_pajak' => 'nullable|numeric',
            'penghasilan_komprehensif_tahun_berjalan' => 'nullable|numeric',
            'pembelian_efek_ekuitas' => 'nullable|numeric',
            'penjualan_efek_ekuitas' => 'nullable|numeric',
            'penerimaan_bunga_deposito' => 'nullable|numeric',
            'penerimaan_bunga_jasa_giro' => 'nullable|numeric',
            'penerimaan_dividen_kas' => 'nullable|numeric',
            'pembayaran_jasa_pengelolaan' => 'nullable|numeric',
            'pembayaran_jasa_kustodian' => 'nullable|numeric',
            'pembayaran_beban_lain_arus' => 'nullable|numeric',
            'kas_bersih_aktivitas_operasi' => 'nullable|numeric',
            'penerimaan_penjualan_unit' => 'nullable|numeric',
            'pembayaran_pembelian_kembali_unit' => 'nullable|numeric',
            'kas_bersih_aktivitas_pendanaan' => 'nullable|numeric',
            'kenaikan_kas_setara_kas' => 'nullable|numeric',
            'total_hasil_investasi' => 'nullable|numeric',
            'hasil_investasi_setelah_biaya' => 'nullable|numeric',
            'persentase_pph'       => 'nullable|numeric',
            'fair_value_level_1'   => 'nullable|numeric',
            'fair_value_level_2'   => 'nullable|numeric',
            'fair_value_level_3'   => 'nullable|numeric',
            'unit_milik_investor'  => 'nullable|numeric',
            'unit_milik_mi'        => 'nullable|numeric',
            'total_unit_beredar'   => 'nullable|numeric',
            'fee_cost_to_performance' => 'nullable|numeric',
            'pendapatan_terhadap_nab' => 'nullable|numeric',
            'beban_terhadap_pendapatan' => 'nullable|numeric',
            'pengelolaan_investasi_terhadap_pendapatan' => 'nullable|numeric',
            'transaction_profit_terhadap_nab' => 'nullable|numeric',
            'input_mode'           => 'nullable|in:manual,lengkap,excel,pdf,ai,ai-plus,link-website',
        ]);

        $this->validateAlokasiAsetTotal($request);

        $request->validate([
            'efek.*.nilai_pasar'        => 'nullable|numeric',
            'efek.*.bobot_seharusnya'   => 'nullable|numeric',
            'efek.*.kontribusi_return'  => 'nullable|numeric',
            'efek.*.return_1m'          => 'nullable|numeric',
            'efek.*.return_3m'          => 'nullable|numeric',
            'efek.*.return_6m'          => 'nullable|numeric',
            'efek.*.return_1y'          => 'nullable|numeric',
            'efek.*.ihsg_contribution'  => 'nullable|numeric',
            'obligasi.*.nilai_pasar'   => 'nullable|numeric',
            'obligasi.*.return_1m'     => 'nullable|numeric',
            'obligasi.*.return_3m'     => 'nullable|numeric',
            'obligasi.*.return_6m'     => 'nullable|numeric',
            'obligasi.*.return_1y'     => 'nullable|numeric',
            'sukuk.*.bobot'            => 'nullable|numeric|min:0|max:100',
            'sukuk.*.yield'            => 'nullable|numeric',
            'sukuk.*.jatuh_tempo'      => 'nullable|string|max:10',
            'sukuk.*.rating'           => 'nullable|string|max:20',
            'bank.*.jenis_bank'        => 'nullable|string|in:Bank Nasional,Bank Asing,BPD,BPR',
            'bank.*.nilai_pasar'       => 'nullable|numeric',
            'bank.*.return_1m'         => 'nullable|numeric',
            'bank.*.return_3m'         => 'nullable|numeric',
            'bank.*.return_6m'         => 'nullable|numeric',
            'bank.*.return_1y'         => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($request, $analisa) {
            $analisa->update([
                'kode_reksa_dana'      => $request->kode_reksa_dana ? strtoupper($request->kode_reksa_dana) : null,
                'nama_reksa_dana'      => $request->nama_reksa_dana,
                'jenis_reksa_dana'     => $request->jenis_reksa_dana,
                'kategori'             => $request->kategori ?? [],
                'benchmark'            => $request->benchmark,
                'manajer_investasi'    => $request->manajer_investasi,
                'bank_kustodian'       => $request->bank_kustodian,
                'tanggal_peluncuran'   => $request->tanggal_peluncuran,
                'tujuan_investasi'     => $request->tujuan_investasi,
                'kebijakan_investasi'  => $request->kebijakan_investasi,
                'total_aum'            => $request->total_aum,
                'unit_penyertaan'      => $request->unit_penyertaan,
                'nab_per_unit'         => $request->nab_per_unit,
                'total_marcap_10_efek' => $request->total_marcap_10_efek,
                'tanggal_data'         => $request->tanggal_data,
                'ffs_bulan'            => $request->ffs_bulan,
                'ffs_tahun'            => $request->ffs_tahun,
                'jenis_laporan'        => 'laporan_tahunan',
                'periode_awal'         => $request->periode_awal,
                'periode_akhir'        => $request->periode_akhir,
                'tahun_laporan'        => $request->tahun_laporan,
                'return_ytd'           => $request->return_ytd,
                'return_1y'            => $request->return_1y,
                'total_return'         => $request->total_return,
                'biaya_operasi'        => $request->biaya_operasi,
                'portfolio_turnover_ratio' => $request->portfolio_turnover_ratio,
                'management_fee'       => $request->management_fee,
                'custodian_fee'        => $request->custodian_fee,
                'total_aset'           => $request->total_aset,
                'total_liabilitas'     => $request->total_liabilitas,
                'nilai_aset_bersih'  => $request->nilai_aset_bersih,
                'kas_dan_bank'         => $request->kas_dan_bank,
                'piutang_bunga'        => $request->piutang_bunga,
                'piutang_dividen'      => $request->piutang_dividen,
                'piutang_lain'         => $request->piutang_lain,
                'utang_pajak'          => $request->utang_pajak,
                'utang_lain'           => $request->utang_lain,
                'pendapatan_bunga'     => $request->pendapatan_bunga,
                'pendapatan_dividen'   => $request->pendapatan_dividen,
                'gain_realized'        => $request->gain_realized,
                'gain_unrealized'      => $request->gain_unrealized,
                'beban_mi'             => $request->beban_mi,
                'beban_kustodian'      => $request->beban_kustodian,
                'beban_lain'           => $request->beban_lain,
                'laba_bersih'          => $request->laba_bersih,
                'arus_kas_operasi'     => $request->arus_kas_operasi,
                'arus_kas_pendanaan'   => $request->arus_kas_pendanaan,
                'kas_awal_tahun'       => $request->kas_awal_tahun,
                'kas_akhir_tahun'      => $request->kas_akhir_tahun,
                'kas'                  => $request->kas,
                'portofolio_efek'      => $request->portofolio_efek,
                'instrumen_pasar_uang' => $request->instrumen_pasar_uang,
                'piutang_transaksi_efek' => $request->piutang_transaksi_efek,
                'piutang_bunga_dan_dividen' => $request->piutang_bunga_dan_dividen,
                'uang_muka_diterima'   => $request->uang_muka_diterima,
                'liabilitas_pembelian_kembali' => $request->liabilitas_pembelian_kembali,
                'beban_akrual'         => $request->beban_akrual,
                'liabilitas_atas_biaya' => $request->liabilitas_atas_biaya,
                'pembelian_kembali_unit_penyertaan' => $request->pembelian_kembali_unit_penyertaan,
                'utang_pajak_lainnya'  => $request->utang_pajak_lainnya,
                'pendapatan_investasi' => $request->pendapatan_investasi,
                'pendapatan_lainnya'   => $request->pendapatan_lainnya,
                'total_pendapatan'   => $request->total_pendapatan,
                'beban_investasi'      => $request->beban_investasi,
                'beban_pengelolaan_investasi' => $request->beban_pengelolaan_investasi,
                'total_beban'            => $request->total_beban,
                'laba_sebelum_pajak'     => $request->laba_sebelum_pajak,
                'beban_pajak_penghasilan' => $request->beban_pajak_penghasilan,
                'laba_bersih_tahun_berjalan' => $request->laba_bersih_tahun_berjalan,
                'penghasilan_komprehensif_lain_setelah_pajak' => $request->penghasilan_komprehensif_lain_setelah_pajak,
                'penghasilan_komprehensif_tahun_berjalan' => $request->penghasilan_komprehensif_tahun_berjalan,
                'pembelian_efek_ekuitas' => $request->pembelian_efek_ekuitas,
                'penjualan_efek_ekuitas' => $request->penjualan_efek_ekuitas,
                'penerimaan_bunga_deposito' => $request->penerimaan_bunga_deposito,
                'penerimaan_bunga_jasa_giro' => $request->penerimaan_bunga_jasa_giro,
                'penerimaan_dividen_kas' => $request->penerimaan_dividen_kas,
                'pembayaran_jasa_pengelolaan' => $request->pembayaran_jasa_pengelolaan,
                'pembayaran_jasa_kustodian' => $request->pembayaran_jasa_kustodian,
                'pembayaran_beban_lain_arus' => $request->pembayaran_beban_lain_arus,
                'kas_bersih_aktivitas_operasi' => $request->kas_bersih_aktivitas_operasi,
                'penerimaan_penjualan_unit' => $request->penerimaan_penjualan_unit,
                'pembayaran_pembelian_kembali_unit' => $request->pembayaran_pembelian_kembali_unit,
                'kas_bersih_aktivitas_pendanaan' => $request->kas_bersih_aktivitas_pendanaan,
                'kenaikan_kas_setara_kas' => $request->kenaikan_kas_setara_kas,
                'total_hasil_investasi' => $request->total_hasil_investasi,
                'hasil_investasi_setelah_biaya' => $request->hasil_investasi_setelah_biaya,
                'persentase_pph'       => $request->persentase_pph,
                'fair_value_level_1'  => $request->fair_value_level_1,
                'fair_value_level_2'  => $request->fair_value_level_2,
                'fair_value_level_3'  => $request->fair_value_level_3,
                'unit_milik_investor' => $request->unit_milik_investor,
                'unit_milik_mi'       => $request->unit_milik_mi,
                'total_unit_beredar'  => $request->total_unit_beredar,
                'fee_cost_to_performance' => $request->fee_cost_to_performance,
                'pendapatan_terhadap_nab' => $request->pendapatan_terhadap_nab,
                'beban_terhadap_pendapatan' => $request->beban_terhadap_pendapatan,
                'pengelolaan_investasi_terhadap_pendapatan' => $request->pengelolaan_investasi_terhadap_pendapatan,
                'transaction_profit_terhadap_nab' => $request->transaction_profit_terhadap_nab,
                'mode'                 => $request->input_mode ?: 'manual',
                'data_tahunan'         => $this->parseDataTahunan($request->data_tahunan),
            ]);

            $sektor   = collect($request->sektor ?? [])->filter(fn($r) => !empty($r['nama_sektor']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $efek     = collect($request->efek ?? [])->filter(fn($r) => !empty($r['nama_efek']) && (isset($r['bobot']) && $r['bobot'] !== ''))->values()->all();
            $kinerja  = collect($request->kinerja ?? [])->filter(fn($r) => !empty($r['periode']) && isset($r['return_pct']) && $r['return_pct'] !== '')->values()->all();
            $obligasi = collect($request->obligasi ?? [])->filter(fn($r) => !empty($r['kode_obligasi']) && !empty($r['nama_obligasi']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $sukuk    = collect($request->sukuk ?? [])->filter(fn($r) => !empty($r['kode_sukuk']) && !empty($r['nama_sukuk']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $bank     = collect($request->bank ?? [])->filter(fn($r) => !empty($r['nama_bank']) && (isset($r['bobot']) && $r['bobot'] !== '' || !empty($r['saldo'])))->values()->all();
            $alokasiAset = $this->filteredAlokasiAset($request);
            $pasarUang = collect($request->pasar_uang ?? [])->filter(fn($r) => !empty($r['nama_instrumen']))->values()->all();
            $piutangBungaDetail = collect($request->piutang_bunga_detail ?? [])->filter(fn($r) => !empty($r['jenis_instrumen']) && !empty($r['jumlah']))->values()->all();
            $likuiditas = collect($request->likuiditas ?? [])->filter(fn($r) => !empty($r['kode_efek']))->values()->all();
            $keuangan   = collect($request->keuangan ?? [])
                ->filter(fn($r) => !empty($r['kode_efek']))
                ->map(fn($r) => array_merge($r, ['nama_efek' => $r['nama_efek'] ?? $r['kode_efek']]))
                ->values()->all();

            $analisa->sektor()->delete();
            $analisa->efek()->delete();
            $analisa->kinerja()->delete();
            $analisa->obligasi()->delete();
            $analisa->sukuk()->delete();
            $analisa->bank()->delete();
            $analisa->alokasiAset()->delete();
            $analisa->pasarUang()->delete();
            $analisa->piutangBungaDetail()->delete();
            $analisa->likuiditas()->delete();
            $analisa->keuangan()->delete();

            if ($sektor)   $analisa->sektor()->createMany($sektor);
            if ($efek)     $analisa->efek()->createMany($efek);
            if ($kinerja)  $analisa->kinerja()->createMany($kinerja);
            if ($obligasi) $analisa->obligasi()->createMany($obligasi);
            if ($sukuk)    $analisa->sukuk()->createMany($sukuk);
            if ($bank)     $analisa->bank()->createMany($bank);
            if ($alokasiAset) $analisa->alokasiAset()->createMany($alokasiAset);
            if ($pasarUang) $analisa->pasarUang()->createMany($pasarUang);
            if ($piutangBungaDetail) $analisa->piutangBungaDetail()->createMany($piutangBungaDetail);
            if ($likuiditas) $analisa->likuiditas()->createMany($likuiditas);
            if ($keuangan)   $analisa->keuangan()->createMany($keuangan);
        });

        return redirect()
            ->route($this->isAdminContext ? 'admin.analisa.index' : 'user.analisa.index')
            ->with('success', 'Data analisa berhasil diperbarui.');
    }

    public function show(AnalisaReksaDana $analisa)
    {
        abort_if(
            $analisa->user_id !== auth()->id() && !$analisa->is_published,
            403
        );
        $analisa->load(['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasiAset', 'likuiditas', 'keuangan']);

        return view('analisa.show', compact('analisa'));
    }

    public function exportPdf(AnalisaReksaDana $analisa)
    {
        abort_if(
            $analisa->user_id !== auth()->id() && !$analisa->is_published,
            403
        );
        $analisa->load(['user', 'sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasiAset', 'likuiditas', 'keuangan']);

        $pdf = Pdf::loadView('analisa.pdf', compact('analisa'))
            ->setPaper('a4', 'portrait');

        $filename = 'analisa-' . str($analisa->nama_reksa_dana)->slug() . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    public function downloadPdf(AnalisaReksaDana $analisa)
    {
        abort_if(
            $analisa->user_id !== auth()->id() && !$analisa->is_published,
            403
        );

        if (!$analisa->pdf_path || !Storage::disk('public')->exists($analisa->pdf_path)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        return Storage::disk('public')->download($analisa->pdf_path, 'ffs-' . str($analisa->nama_reksa_dana)->slug() . '.pdf');
    }

    public function destroy(AnalisaReksaDana $analisa)
    {
        abort_if($analisa->user_id !== auth()->id(), 403);
        abort_if($analisa->status === 'reviewed', 403, 'Data yang sudah direview tidak dapat dihapus.');

        if ($analisa->pdf_path && Storage::disk('public')->exists($analisa->pdf_path)) {
            Storage::disk('public')->delete($analisa->pdf_path);
        }

        $analisa->delete();

        return redirect()->route('user.analisa.index')->with('success', 'Data analisa dihapus.');
    }

    private function buildExtractedList(array $data): array
    {
        $extracted = [];
        if (!empty($data['nama_reksa_dana'])) $extracted[] = 'Nama RD';
        if (!empty($data['jenis_reksa_dana'])) $extracted[] = 'Jenis RD';
        if (!empty($data['manajer_investasi'])) $extracted[] = 'MI';
        if (!empty($data['total_aum'])) $extracted[] = 'Total AUM';
        if (!empty($data['unit_penyertaan'])) $extracted[] = 'Unit Penyertaan';
        if (!empty($data['nab_per_unit'])) $extracted[] = 'NAB/UP';
        if (!empty($data['return_ytd'])) $extracted[] = 'Return YTD';
        if (!empty($data['tanggal_data'])) $extracted[] = 'Tanggal Data';
        if (!empty($data['total_aset'])) $extracted[] = 'Neraca';
        if (!empty($data['laba_bersih'])) $extracted[] = 'Laba Bersih';
        if (!empty($data['total_beban'])) $extracted[] = 'Total Beban';
        if (!empty($data['laba_sebelum_pajak'])) $extracted[] = 'Laba Sblm Pajak';
        if (!empty($data['laba_bersih_tahun_berjalan'])) $extracted[] = 'Laba Bersih Thn Brjln';
        if (!empty($data['penghasilan_komprehensif_lain_setelah_pajak'])) $extracted[] = 'PK Lain Stlh Pajak';
        if (!empty($data['penghasilan_komprehensif_tahun_berjalan'])) $extracted[] = 'PK Thn Brjln';
        if (!empty($data['nilai_aset_bersih'])) $extracted[] = 'Nilai Aset Bersih';
        if (!empty($data['total_pendapatan'])) $extracted[] = 'Total Pendapatan';
        if (!empty($data['kas'])) $extracted[] = 'Kas';
        if (!empty($data['arus_kas_operasi'])) $extracted[] = 'Arus Kas';
        if (!empty($data['total_hasil_investasi'])) $extracted[] = 'Rasio';
        if (!empty($data['fair_value_level_1'])) $extracted[] = 'Fair Value';
        if (!empty($data['unit_milik_investor'])) $extracted[] = 'Unit MI';
        if (!empty($data['portofolio_efek']) || !empty($data['instrumen_pasar_uang']) || !empty($data['piutang_transaksi_efek']) || !empty($data['piutang_bunga_dan_dividen']) || !empty($data['uang_muka_diterima'])) $extracted[] = 'Aset Detail';
        if (!empty($data['liabilitas_pembelian_kembali']) || !empty($data['beban_akrual']) || !empty($data['liabilitas_atas_biaya']) || !empty($data['pembelian_kembali_unit_penyertaan']) || !empty($data['utang_pajak_lainnya'])) $extracted[] = 'Liabilitas Detail';
        if (!empty($data['pendapatan_investasi']) || !empty($data['pendapatan_lainnya'])) $extracted[] = 'Pendapatan Detail';
        if (!empty($data['beban_investasi']) || !empty($data['beban_pengelolaan_investasi'])) $extracted[] = 'Beban Detail';
        if (!empty($data['pembelian_efek_ekuitas']) || !empty($data['penjualan_efek_ekuitas']) || !empty($data['penerimaan_bunga_deposito']) || !empty($data['penerimaan_bunga_jasa_giro']) || !empty($data['penerimaan_dividen_kas'])) $extracted[] = 'Penerimaan Detail';
        if (!empty($data['pembayaran_jasa_pengelolaan']) || !empty($data['pembayaran_jasa_kustodian']) || !empty($data['pembayaran_beban_lain_arus']) || !empty($data['kas_bersih_aktivitas_operasi']) || !empty($data['penerimaan_penjualan_unit']) || !empty($data['pembayaran_pembelian_kembali_unit']) || !empty($data['kas_bersih_aktivitas_pendanaan']) || !empty($data['kenaikan_kas_setara_kas'])) $extracted[] = 'Detail Arus Kas';
        if (!empty($data['alokasi_aset'])) $extracted[] = count($data['alokasi_aset']) . ' Alokasi Aset';
        if (!empty($data['sektor'])) $extracted[] = count($data['sektor']) . ' Sektor';
        if (!empty($data['efek'])) $extracted[] = count($data['efek']) . ' Efek';
        if (!empty($data['kinerja'])) $extracted[] = count($data['kinerja']) . ' Bulan Kinerja';
        if (!empty($data['obligasi'])) $extracted[] = count($data['obligasi']) . ' Obligasi';
        if (!empty($data['sukuk'])) $extracted[] = count($data['sukuk']) . ' Sukuk';
        if (!empty($data['bank'])) $extracted[] = count($data['bank']) . ' Bank';
        if (!empty($data['pasar_uang'])) $extracted[] = count($data['pasar_uang']) . ' Pasar Uang';
        if (!empty($data['piutang_bunga_detail'])) $extracted[] = count($data['piutang_bunga_detail']) . ' Piutang Bunga';
        if (!empty($data['_raw_tables'])) {
            $totalTabel = array_sum(array_map(fn($p) => count($p['tables'] ?? []), $data['_raw_tables']));
            if ($totalTabel > 0) {
                $extracted[] = "{$totalTabel} tabel";
            } elseif (!empty($data['_raw_tables'])) {
                $extracted[] = count($data['_raw_tables']) . ' partisi';
            }
        }
        return $extracted;
    }

    private function extractPeriodFromData(array $data): ?array
    {
        // Try to extract period from various fields
        $month = $data['ffs_bulan'] ?? $data['month'] ?? null;
        $year = $data['ffs_tahun'] ?? $data['year'] ?? null;
        
        // Try to extract from tanggal_data
        if (empty($month) || empty($year)) {
            if (!empty($data['tanggal_data'])) {
                $date = \Carbon\Carbon::parse($data['tanggal_data']);
                $month = $month ?? $date->month;
                $year = $year ?? $date->year;
            }
        }
        
        // Try to extract from tanggal_nab
        if (empty($month) || empty($year)) {
            if (!empty($data['tanggal_nab'])) {
                $date = \Carbon\Carbon::parse($data['tanggal_nab']);
                $month = $month ?? $date->month;
                $year = $year ?? $date->year;
            }
        }
        
        if ($month && $year) {
            return ['month' => (int)$month, 'year' => (int)$year];
        }
        
        return null;
    }
}
