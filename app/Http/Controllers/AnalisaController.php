<?php

namespace App\Http\Controllers;

use App\Exports\AnalisaTemplateExport;
use App\Imports\AnalisaImport;
use App\Jobs\AnalisaAiJob;
use App\Models\AnalisaReksaDana;
use App\Models\DataSourceLink;
use App\Models\ReksaDana;
use App\Models\ReksaDanaDocument;
use App\Models\StockPrice;
use App\Services\AnalisaPayloadBuilder;
use App\Services\BankDataService;
use App\Services\BondMarketService;
use App\Services\DataSourceAutoDownloadService;
use App\Services\FfsParserService;
use App\Services\IdxMarketService;
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
            'parse_pdf'       => route("{$prefix}.parse-pdf"),
            'parse_pdf_vision' => \Illuminate\Support\Facades\Route::has("{$prefix}.parse-pdf-vision") ? route("{$prefix}.parse-pdf-vision") : null,
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
            'parse_web_file'  => route("{$prefix}.parse-web-file"),
            'scrape_web'      => $scrapeWebBase,
            'scrape_url'      => $scrapeUrlBase,
        ]);
    }

    public function index()
    {
        $analisas = AnalisaReksaDana::where('user_id', auth()->id())
            ->where('product_type', $this->productType)
            ->latest()->get();

        $createRoute = $this->productType === 'unit_link'
            ? route('user.unit-link-analisa.create')
            : route('user.analisa.create');

        return view('analisa.index', compact('analisas'))
            ->with('productLabel', $this->productLabel)
            ->with('createRoute', $createRoute);
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

        if ($resumeId = request('resume')) {
            $analisa = AnalisaReksaDana::with(['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasiAset'])
                ->when(!$this->isAdminContext, fn($query) => $query->where('user_id', auth()->id()))
                ->where('product_type', $this->productType)
                ->find($resumeId);
            if ($analisa) {
                $resumeAnalisa = $this->serializeAnalisaForForm($analisa);
                $resumeMode = $analisa->mode ?: 'manual';
            }
        }

        return view('analisa.create', array_merge(
            ['formRoutes' => $this->formRoutes(), 'productLabel' => $this->productLabel],
            $this->dataSourceLinkContext(),
            compact('resumeAnalisa', 'resumeMode'),
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
        $master = ReksaDana::whereRaw('UPPER(kode_reksa_dana) = ?', [$kode])->first();
        $lastAnalisa = AnalisaReksaDana::with(['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasiAset'])
            ->where('product_type', $this->productType)
            ->whereRaw('UPPER(kode_reksa_dana) = ?', [$kode])
            ->latest()
            ->first();

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
            ] : null,
            'last_analisa' => $lastAnalisa ? $this->serializeAnalisaForForm($lastAnalisa) : null,
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

    public function parsePdf(Request $request, FfsParserService $ffsParser, GroqService $groq)
    {
        set_time_limit(120);

        $request->validate([
            'file_pdf' => 'required|file|max:10240',
        ]);

        $file = $request->file('file_pdf');
        $path = $file->getPathname();

        try {
            $data = $ffsParser->parseWithAi($path, $groq);
        } catch (\Throwable $e) {
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

        $extracted = [];
        if ($data['nama_reksa_dana']) $extracted[] = 'Nama RD';
        if ($data['jenis_reksa_dana']) $extracted[] = 'Jenis RD';
        if ($data['manajer_investasi']) $extracted[] = 'MI';
        if ($data['total_aum']) $extracted[] = 'Total AUM';
        if (!empty($data['unit_penyertaan'])) $extracted[] = 'Unit Penyertaan';
        if (!empty($data['nab_per_unit'])) $extracted[] = 'NAB/UP';
        if (!empty($data['return_ytd'])) $extracted[] = 'Return YTD';
        if (!empty($data['tanggal_data'])) $extracted[] = 'Tanggal Data';
        if (!empty($data['alokasi_aset'])) $extracted[] = count($data['alokasi_aset']) . ' Alokasi Aset';
        if ($data['sektor']) $extracted[] = count($data['sektor']) . ' Sektor';
        if ($data['efek']) $extracted[] = count($data['efek']) . ' Efek';
        if ($data['kinerja']) $extracted[] = count($data['kinerja']) . ' Bulan Kinerja';
        if ($data['obligasi']) $extracted[] = count($data['obligasi']) . ' Obligasi';
        if ($data['sukuk']) $extracted[] = count($data['sukuk']) . ' Sukuk';
        if ($data['bank']) $extracted[] = count($data['bank']) . ' Bank';

        $success = count($extracted) > 0;

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'Berhasil mengekstrak: ' . implode(', ', $extracted) . '.'
                : 'Tidak dapat mengekstrak data dari PDF ini. Format mungkin tidak didukung.',
            'data' => $data,
            'pdf_file' => $storedPath,
        ]);
    }

    public function getExistingDocuments(Request $request)
    {
        $request->validate([
            'kode_reksa_dana' => 'nullable|string|max:20',
            'jenis_laporan' => 'nullable|in:kalender_ffs,laporan_tahunan',
            'ffs_bulan' => 'nullable|integer|min:1|max:12',
            'ffs_tahun' => 'nullable|integer|min:2000|max:2100',
            'tahun_laporan' => 'nullable|digits:4',
        ]);

        $query = ReksaDanaDocument::with(['reksaDana', 'uploader']);

        // Filter by kode if provided
        if ($kode = $request->kode_reksa_dana) {
            $kode = strtoupper(trim($kode));
            $query->whereHas('reksaDana', fn($q) => $q->whereRaw('UPPER(kode_reksa_dana) = ?', [$kode]));
        }

        $jenisLaporan = $request->jenis_laporan;

        if ($jenisLaporan === 'laporan_tahunan') {
            $query->whereIn('document_type', [
                ReksaDanaDocument::TYPE_LAPORAN_TAHUNAN,
                ReksaDanaDocument::TYPE_PROSPECTUS,
            ]);
            if ($request->tahun_laporan) {
                $tahun = $request->tahun_laporan;
                $query->where(function ($q) use ($tahun) {
                    $q->where(function ($q2) use ($tahun) {
                        $q2->where('document_type', ReksaDanaDocument::TYPE_LAPORAN_TAHUNAN)
                          ->where('tahun_laporan', $tahun);
                    })->orWhere(function ($q2) use ($tahun) {
                        $q2->where('document_type', ReksaDanaDocument::TYPE_PROSPECTUS)
                          ->where('ffs_year', $tahun);
                    });
                });
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
                'tahun_laporan' => $doc->tahun_laporan,
                'reksa_dana_nama' => $doc->reksaDana?->nama_reksa_dana,
                'reksa_dana_kode' => $doc->reksaDana?->kode_reksa_dana,
                'uploaded_at' => $doc->created_at->format('d/m/Y'),
                'uploader_name' => $doc->uploader?->name,
                'file_size' => $doc->file_size,
            ]);

        return response()->json([
            'found' => $documents->isNotEmpty(),
            'documents' => $documents,
        ]);
    }

    private function getDocumentLabel(ReksaDanaDocument $doc): string
    {
        return match ($doc->document_type) {
            ReksaDanaDocument::TYPE_LAPORAN_TAHUNAN => 'Laporan Tahunan ' . ($doc->tahun_laporan ?? ''),
            ReksaDanaDocument::TYPE_PROSPECTUS => 'Prospektus ' . ($doc->ffs_year ?? ''),
            ReksaDanaDocument::TYPE_FFS => 'FFS '
                . ($doc->ffs_month ? \Carbon\Carbon::create()->month($doc->ffs_month)->format('M') . ' ' : '')
                . ($doc->ffs_year ?? ''),
            default => $doc->original_name,
        };
    }

    public function parseExistingDocument(Request $request, FfsParserService $ffsParser, GroqService $groq)
    {
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
            set_time_limit(120);
            $data = $ffsParser->parseWithAi($fullPath, $groq);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca PDF: ' . $e->getMessage(),
                'data' => null,
            ], 422);
        }

        $extracted = [];
        if ($data['nama_reksa_dana']) $extracted[] = 'Nama RD';
        if ($data['jenis_reksa_dana']) $extracted[] = 'Jenis RD';
        if ($data['manajer_investasi']) $extracted[] = 'MI';
        if ($data['total_aum']) $extracted[] = 'Total AUM';
        if (!empty($data['unit_penyertaan'])) $extracted[] = 'Unit Penyertaan';
        if (!empty($data['nab_per_unit'])) $extracted[] = 'NAB/UP';
        if (!empty($data['tanggal_data'])) $extracted[] = 'Tanggal Data';
        if (!empty($data['alokasi_aset'])) $extracted[] = count($data['alokasi_aset']) . ' Alokasi Aset';
        if ($data['sektor']) $extracted[] = count($data['sektor']) . ' Sektor';
        if ($data['efek']) $extracted[] = count($data['efek']) . ' Efek';
        if ($data['kinerja']) $extracted[] = count($data['kinerja']) . ' Bulan Kinerja';
        if ($data['obligasi']) $extracted[] = count($data['obligasi']) . ' Obligasi';
        if ($data['bank']) $extracted[] = count($data['bank']) . ' Bank';

        return response()->json([
            'success' => count($extracted) > 0,
            'message' => count($extracted) > 0
                ? 'Berhasil mengekstrak: ' . implode(', ', $extracted) . '.'
                : 'Tidak dapat mengekstrak data dari PDF ini.',
            'data' => $data,
            'document_label' => $this->getDocumentLabel($document),
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

        return response()->json($this->webFileParseResponse($data));
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

        return response()->json($this->webFileParseResponse($data));
    }

    public function scrapeUrl(Request $request, WebScraperService $scraper)
    {
        $request->validate(['url' => 'required|url|max:2048']);

        try {
            $result = $scraper->scrapeUrl($request->url);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'data' => null], 422);
        }

        $response = $this->webFileParseResponse($result['data']);
        $response['message'] = $result['message'];
        $response['type'] = $result['type'];
        if (isset($result['raw_tables'])) {
            $response['raw_tables'] = $result['raw_tables'];
        }

        return response()->json($response);
    }

    protected function webFileParseResponse(array $data): array
    {
        $extracted = [];
        if ($data['nama_reksa_dana']) {
            $extracted[] = 'Nama RD';
        }
        if ($data['total_aum']) {
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
        if ($data['sektor']) {
            $extracted[] = count($data['sektor']) . ' Sektor';
        }
        if ($data['efek']) {
            $extracted[] = count($data['efek']) . ' Efek';
        }
        if ($data['kinerja']) {
            $extracted[] = count($data['kinerja']) . ' Kinerja';
        }
        if ($data['obligasi']) {
            $extracted[] = count($data['obligasi']) . ' Obligasi';
        }
        if ($data['sukuk']) {
            $extracted[] = count($data['sukuk']) . ' Sukuk';
        }
        if ($data['bank']) {
            $extracted[] = count($data['bank']) . ' Bank';
        }

        $success = count($extracted) > 0;

        return [
            'success' => $success,
            'message' => $success
                ? 'Data siap diisi ke form: ' . implode(', ', $extracted) . '.'
                : 'File terbaca tetapi tidak ada data yang cocok. Pastikan format Excel template analisa atau export situs yang benar.',
            'data' => $data,
        ];
    }

    public function store(Request $request)
    {
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
            'total_hasil_investasi' => 'nullable|numeric',
            'hasil_investasi_setelah_biaya' => 'nullable|numeric',
            'persentase_pph'       => 'nullable|numeric',
            'fair_value_level_1'   => 'nullable|numeric',
            'fair_value_level_2'   => 'nullable|numeric',
            'fair_value_level_3'   => 'nullable|numeric',
            'unit_milik_investor'  => 'nullable|numeric',
            'unit_milik_mi'        => 'nullable|numeric',
            'total_unit_beredar'   => 'nullable|numeric',
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
            'alokasi_aset'              => 'nullable|array',
            'alokasi_aset.*.nama_aset'  => 'nullable|string',
            'alokasi_aset.*.persentase' => 'nullable|numeric',
        ]);

        $this->validateAlokasiAsetTotal($request);

        DB::transaction(function () use ($request, $isSimpan) {
            $pdfPath = $this->resolvePdfPath($request->pdf_file);

            $payload = [
                'user_id'              => auth()->id(),
                'product_type'         => $this->productType,
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
                'jenis_laporan'        => $request->jenis_laporan ?: 'kalender_ffs',
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
                'total_hasil_investasi' => $request->total_hasil_investasi,
                'hasil_investasi_setelah_biaya' => $request->hasil_investasi_setelah_biaya,
                'persentase_pph'       => $request->persentase_pph,
                'fair_value_level_1'  => $request->fair_value_level_1,
                'fair_value_level_2'  => $request->fair_value_level_2,
                'fair_value_level_3'  => $request->fair_value_level_3,
                'unit_milik_investor' => $request->unit_milik_investor,
                'unit_milik_mi'       => $request->unit_milik_mi,
                'total_unit_beredar'  => $request->total_unit_beredar,
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
            } else {
                $analisa = AnalisaReksaDana::create($payload);
            }

            $sektor   = collect($request->sektor)->filter(fn($r) => !empty($r['nama_sektor']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $efek     = collect($request->efek)->filter(fn($r) => !empty($r['kode_efek']) && !empty($r['nama_efek']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $kinerja  = collect($request->kinerja)->filter(fn($r) => !empty($r['periode']) && isset($r['return_pct']) && $r['return_pct'] !== '')->values()->all();
            $obligasi = collect($request->obligasi)->filter(fn($r) => !empty($r['kode_obligasi']) && !empty($r['nama_obligasi']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $sukuk    = collect($request->sukuk)->filter(fn($r) => !empty($r['kode_sukuk']) && !empty($r['nama_sukuk']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $bank     = collect($request->bank)->filter(fn($r) => !empty($r['nama_bank']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $alokasiAset = $this->filteredAlokasiAset($request);

            if ($sektor)   $analisa->sektor()->createMany($sektor);
            if ($efek)     $analisa->efek()->createMany($efek);
            if ($kinerja)  $analisa->kinerja()->createMany($kinerja);
            if ($obligasi) $analisa->obligasi()->createMany($obligasi);
            if ($sukuk)    $analisa->sukuk()->createMany($sukuk);
            if ($bank)     $analisa->bank()->createMany($bank);
            if ($alokasiAset) $analisa->alokasiAset()->createMany($alokasiAset);

            if (!$isSimpan) {
                $this->persistAiFromRequest($request, $analisa);
            }
        });

        if ($isSimpan) {
            return redirect()->route($this->indexRoute())->with('success', 'Data berhasil disimpan sebagai Input Manual.');
        }

        return redirect()->route($this->indexRoute())->with('success', 'Data analisa berhasil disubmit. Narasi AI sedang diproses.');
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
                'jenis_laporan'        => $request->jenis_laporan ?: 'kalender_ffs',
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
                'total_hasil_investasi' => $request->total_hasil_investasi,
                'hasil_investasi_setelah_biaya' => $request->hasil_investasi_setelah_biaya,
                'persentase_pph'       => $request->persentase_pph,
                'fair_value_level_1'  => $request->fair_value_level_1,
                'fair_value_level_2'  => $request->fair_value_level_2,
                'fair_value_level_3'  => $request->fair_value_level_3,
                'unit_milik_investor' => $request->unit_milik_investor,
                'unit_milik_mi'       => $request->unit_milik_mi,
                'total_unit_beredar'  => $request->total_unit_beredar,
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
        $analisa->load(['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasiAset']);

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
                'kontribusi_kinerja' => $e->kontribusi_kinerja,
                'market_cap' => $e->market_cap,
                'nilai_pasar' => $e->nilai_pasar,
                'return_1m' => $e->return_1m,
                'return_3m' => $e->return_3m,
                'return_6m' => $e->return_6m,
                'return_1y' => $e->return_1y,
                'ihsg_contribution' => $e->ihsg_contribution,
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
            'total_hasil_investasi'   => $analisa->total_hasil_investasi,
            'hasil_investasi_setelah_biaya' => $analisa->hasil_investasi_setelah_biaya,
            'persentase_pph'          => $analisa->persentase_pph,
            'fair_value_level_1'      => $analisa->fair_value_level_1,
            'fair_value_level_2'      => $analisa->fair_value_level_2,
            'fair_value_level_3'      => $analisa->fair_value_level_3,
            'unit_milik_investor'     => $analisa->unit_milik_investor,
            'unit_milik_mi'           => $analisa->unit_milik_mi,
            'total_unit_beredar'      => $analisa->total_unit_beredar,
        ];
    }

    public function edit(AnalisaReksaDana $analisa)
    {
        abort_if(!$this->isAdminContext && $analisa->user_id !== auth()->id(), 403);
        abort_if(!$this->isAdminContext && $analisa->status === 'reviewed', 403, 'Data yang sudah direview tidak dapat diedit.');
        abort_if($analisa->product_type !== $this->productType, 404);

        $analisa->load(['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasiAset']);

        $editData = [
            'sektor'   => $analisa->sektor->map(fn($s) => ['nama_sektor' => $s->nama_sektor, 'bobot' => $s->bobot])->values(),
            'efek'     => $analisa->efek->map(fn($e) => [
                'kode_efek' => $e->kode_efek,
                'nama_efek' => $e->nama_efek,
                'sektor' => $e->sektor,
                'bobot' => $e->bobot,
                'kontribusi_kinerja' => $e->kontribusi_kinerja,
                'market_cap' => $e->market_cap,
                'nilai_pasar' => $e->nilai_pasar,
                'return_1m' => $e->return_1m,
                'return_3m' => $e->return_3m,
                'return_6m' => $e->return_6m,
                'return_1y' => $e->return_1y,
                'ihsg_contribution' => $e->ihsg_contribution,
                'effect_type' => $e->effect_type,
                'top_10' => $e->top_10,
                'harga_perolehan' => $e->harga_perolehan,
                'persen_nab' => $e->persen_nab,
            ])->values(),
            'kinerja'  => $analisa->kinerja->map(fn($k) => ['periode' => \Carbon\Carbon::parse($k->periode)->format('Y-m'), 'return_pct' => $k->return_pct])->values(),
            'obligasi' => $analisa->obligasi->map(fn($o) => [
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
            'sukuk'    => $analisa->sukuk->map(fn($s) => [
                'kode_sukuk' => $s->kode_sukuk,
                'nama_sukuk' => $s->nama_sukuk,
                'jenis_sukuk' => $s->jenis_sukuk,
                'bobot' => $s->bobot,
                'yield' => $s->yield,
                'jatuh_tempo' => $s->jatuh_tempo,
                'rating' => $s->rating,
                'persen_nab' => $s->persen_nab,
            ])->values(),
            'bank'     => $analisa->bank->map(fn($b) => [
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
            'alokasi_aset' => $analisa->alokasiAset->map(fn($a) => ['nama_aset' => $a->nama_aset, 'persentase' => $a->persentase])->values(),
        ];

        $formRoutes = array_merge($this->formRoutes(), [
            'update' => $this->isAdminContext
                ? route('admin.analisa-rd.update', $analisa)
                : route('user.analisa.update', $analisa),
            'cancel' => $this->isAdminContext
                ? route('admin.analisa.index')
                : route('user.analisa.index'),
        ]);

        return view('analisa.edit', compact('analisa', 'editData', 'formRoutes'));
    }

    public function update(Request $request, AnalisaReksaDana $analisa)
    {
        abort_if(!$this->isAdminContext && $analisa->user_id !== auth()->id(), 403);
        abort_if(!$this->isAdminContext && $analisa->status === 'reviewed', 403, 'Data yang sudah direview tidak dapat diedit.');
        abort_if($analisa->product_type !== $this->productType, 404);

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
            'total_hasil_investasi' => 'nullable|numeric',
            'hasil_investasi_setelah_biaya' => 'nullable|numeric',
            'persentase_pph'       => 'nullable|numeric',
            'fair_value_level_1'   => 'nullable|numeric',
            'fair_value_level_2'   => 'nullable|numeric',
            'fair_value_level_3'   => 'nullable|numeric',
            'unit_milik_investor'  => 'nullable|numeric',
            'unit_milik_mi'        => 'nullable|numeric',
            'total_unit_beredar'   => 'nullable|numeric',
            'input_mode'           => 'nullable|in:manual,lengkap,excel,pdf,ai,ai-plus,link-website',
        ]);

        $this->validateAlokasiAsetTotal($request);

        $request->validate([
            'efek.*.nilai_pasar'       => 'nullable|numeric',
            'efek.*.return_1m'         => 'nullable|numeric',
            'efek.*.return_3m'         => 'nullable|numeric',
            'efek.*.return_6m'         => 'nullable|numeric',
            'efek.*.return_1y'         => 'nullable|numeric',
            'efek.*.ihsg_contribution' => 'nullable|numeric',
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
                'jenis_laporan'        => $request->jenis_laporan ?: 'kalender_ffs',
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
                'total_hasil_investasi' => $request->total_hasil_investasi,
                'hasil_investasi_setelah_biaya' => $request->hasil_investasi_setelah_biaya,
                'persentase_pph'       => $request->persentase_pph,
                'fair_value_level_1'  => $request->fair_value_level_1,
                'fair_value_level_2'  => $request->fair_value_level_2,
                'fair_value_level_3'  => $request->fair_value_level_3,
                'unit_milik_investor' => $request->unit_milik_investor,
                'unit_milik_mi'       => $request->unit_milik_mi,
                'total_unit_beredar'  => $request->total_unit_beredar,
                'mode'                 => $request->input_mode ?: 'manual',
            ]);

            $sektor   = collect($request->sektor ?? [])->filter(fn($r) => !empty($r['nama_sektor']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $efek     = collect($request->efek ?? [])->filter(fn($r) => !empty($r['kode_efek']) && !empty($r['nama_efek']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $kinerja  = collect($request->kinerja ?? [])->filter(fn($r) => !empty($r['periode']) && isset($r['return_pct']) && $r['return_pct'] !== '')->values()->all();
            $obligasi = collect($request->obligasi ?? [])->filter(fn($r) => !empty($r['kode_obligasi']) && !empty($r['nama_obligasi']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $sukuk    = collect($request->sukuk ?? [])->filter(fn($r) => !empty($r['kode_sukuk']) && !empty($r['nama_sukuk']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $bank     = collect($request->bank ?? [])->filter(fn($r) => !empty($r['nama_bank']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $alokasiAset = $this->filteredAlokasiAset($request);

            $analisa->sektor()->delete();
            $analisa->efek()->delete();
            $analisa->kinerja()->delete();
            $analisa->obligasi()->delete();
            $analisa->sukuk()->delete();
            $analisa->bank()->delete();
            $analisa->alokasiAset()->delete();

            if ($sektor)   $analisa->sektor()->createMany($sektor);
            if ($efek)     $analisa->efek()->createMany($efek);
            if ($kinerja)  $analisa->kinerja()->createMany($kinerja);
            if ($obligasi) $analisa->obligasi()->createMany($obligasi);
            if ($sukuk)    $analisa->sukuk()->createMany($sukuk);
            if ($bank)     $analisa->bank()->createMany($bank);
            if ($alokasiAset) $analisa->alokasiAset()->createMany($alokasiAset);
        });

        return redirect()
            ->route($this->isAdminContext ? 'admin.analisa.index' : 'user.analisa.index')
            ->with('success', 'Data analisa berhasil diperbarui.');
    }

    public function show(AnalisaReksaDana $analisa)
    {
        abort_if($analisa->user_id !== auth()->id(), 403);
        $analisa->load(['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasiAset']);

        return view('analisa.show', compact('analisa'));
    }

    public function exportPdf(AnalisaReksaDana $analisa)
    {
        abort_if($analisa->user_id !== auth()->id(), 403);
        $analisa->load(['user', 'sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasiAset']);

        $pdf = Pdf::loadView('analisa.pdf', compact('analisa'))
            ->setPaper('a4', 'portrait');

        $filename = 'analisa-' . str($analisa->nama_reksa_dana)->slug() . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    public function downloadPdf(AnalisaReksaDana $analisa)
    {
        abort_if($analisa->user_id !== auth()->id(), 403);

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
}
