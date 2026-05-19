<?php

namespace App\Http\Controllers;

use App\Exports\AnalisaTemplateExport;
use App\Imports\AnalisaImport;
use App\Jobs\AnalisaAiJob;
use App\Models\AnalisaReksaDana;
use App\Models\DataSourceLink;
use App\Models\ReksaDana;
use App\Models\StockPrice;
use App\Services\AnalisaPayloadBuilder;
use App\Services\DataSourceAutoDownloadService;
use App\Services\FfsParserService;
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

    protected function indexRoute(): string
    {
        return 'user.analisa.index';
    }

    protected function formRoutes(): array
    {
        $prefix = $this->isAdminContext ? 'admin.analisa-rd' : 'user.analisa';

        return array_merge([
            'layout'          => $this->isAdminContext ? 'layouts.admin' : 'layouts.user',
            'store'           => route("{$prefix}.store"),
            'template'        => route("{$prefix}.template"),
            'cancel'          => $this->isAdminContext ? route('admin.reksa-dana.index') : route('user.analisa.index'),
            'parse_pdf'       => route("{$prefix}.parse-pdf"),
            'preview_ai'      => route("{$prefix}.preview-ai"),
            'preview_ai_plus' => route("{$prefix}.preview-ai-plus"),
            'parse_web_file'  => route("{$prefix}.parse-web-file"),
            'scrape_web'      => $this->isAdminContext
                ? url('admin/analisa-rd/scrape-web-data')
                : url('user/analisa/scrape-web-data'),
            'scrape_url'      => $this->isAdminContext
                ? url('admin/analisa-rd/scrape-url')
                : url('user/analisa/scrape-url'),
        ]);
    }

    public function index()
    {
        $analisas = AnalisaReksaDana::where('user_id', auth()->id())
            ->latest()->get();

        return view('analisa.index', compact('analisas'));
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
        return $this->isAdminContext ? 'admin.analisa-rd.create' : 'user.analisa.create';
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
        return view('analisa.create', array_merge(
            ['formRoutes' => $this->formRoutes()],
            $this->dataSourceLinkContext(),
        ));
    }

    public function downloadTemplate()
    {
        return Excel::download(new AnalisaTemplateExport(), 'template-analisa-reksa-dana.xlsx');
    }

    public function previewAi(Request $request, GroqService $groq)
    {
        $request->validate([
            'nama_reksa_dana'  => 'required|string|max:255',
            'jenis_reksa_dana' => 'required|in:Saham,Pendapatan Tetap,Campuran,Pasar Uang',
        ]);

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
                'message' => 'Gagal membuat Analisa AI: '.$e->getMessage(),
            ], 422);
        }
    }

    public function previewAiPlus(Request $request, GroqService $groq)
    {
        $request->validate([
            'nama_reksa_dana'  => 'required|string|max:255',
            'jenis_reksa_dana' => 'required|in:Saham,Pendapatan Tetap,Campuran,Pasar Uang',
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
                'message' => 'Gagal membuat Analisa AI Plus: '.$e->getMessage(),
            ], 422);
        }
    }

    public function parsePdf(Request $request, FfsParserService $ffsParser, GroqService $groq)
    {
        set_time_limit(120);

        $request->validate([
            'file_pdf' => 'required|file|mimes:pdf|max:10240',
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
        if ($data['total_aum']) $extracted[] = 'Total AUM';
        if ($data['sektor']) $extracted[] = count($data['sektor']) . ' Sektor';
        if ($data['efek']) $extracted[] = count($data['efek']) . ' Efek';
        if ($data['kinerja']) $extracted[] = count($data['kinerja']) . ' Bulan Kinerja';
        if ($data['obligasi']) $extracted[] = count($data['obligasi']) . ' Obligasi';
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
            'nama_reksa_dana'      => 'required|string|max:255',
            'jenis_reksa_dana'     => 'required|in:Saham,Pendapatan Tetap,Campuran,Pasar Uang',
            'total_aum'            => 'nullable|numeric|min:0',
            'total_marcap_10_efek' => 'nullable|numeric|min:0',
            'input_mode'           => 'required|in:manual,excel,pdf,ai,ai-plus,link-website',
            'pdf_file'             => 'nullable|string',
            'ai_narasi'            => 'nullable|string',
            'ai_output'            => 'nullable|string',
            'ai_narasi_plus'       => 'nullable|string',
            'ai_output_plus'       => 'nullable|string',
        ]);

        if (in_array($request->input_mode, ['ai', 'ai-plus', 'link-website'], true)) {
            $request->merge(['input_mode' => 'manual']);
        }

        if ($request->input_mode === 'excel') {
            return $this->storeFromExcel($request);
        }

        return $this->storeFromManual($request);
    }

    private function storeFromManual(Request $request)
    {
        $request->validate([
            'sektor'                    => 'nullable|array',
            'sektor.*.nama_sektor'      => 'nullable|string',
            'sektor.*.bobot'            => 'nullable|numeric',
            'efek'                      => 'nullable|array',
            'efek.*.kode_efek'          => 'nullable|string',
            'efek.*.nama_efek'          => 'nullable|string',
            'efek.*.bobot'              => 'nullable|numeric',
            'efek.*.kontribusi_kinerja' => 'nullable|numeric',
            'efek.*.market_cap'         => 'nullable|numeric',
            'kinerja'                   => 'nullable|array',
            'kinerja.*.periode'         => 'nullable|date',
            'kinerja.*.return_pct'      => 'nullable|numeric',
            'obligasi'                  => 'nullable|array',
            'obligasi.*.kode_obligasi'  => 'nullable|string',
            'obligasi.*.nama_obligasi'  => 'nullable|string',
            'obligasi.*.bobot'          => 'nullable|numeric',
            'obligasi.*.durasi'         => 'nullable|numeric',
            'obligasi.*.rating'         => 'nullable|string',
            'bank'                      => 'nullable|array',
            'bank.*.nama_bank'          => 'nullable|string',
            'bank.*.bobot'              => 'nullable|numeric',
            'bank.*.car'                => 'nullable|numeric',
            'bank.*.npl'                => 'nullable|numeric',
            'bank.*.klasifikasi_risiko' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $pdfPath = $this->resolvePdfPath($request->pdf_file);

            $analisa = AnalisaReksaDana::create([
                'user_id'              => auth()->id(),
                'nama_reksa_dana'      => $request->nama_reksa_dana,
                'jenis_reksa_dana'     => $request->jenis_reksa_dana,
                'total_aum'            => $request->total_aum,
                'total_marcap_10_efek' => $request->total_marcap_10_efek,
                'status'               => 'submitted',
                'pdf_path'             => $pdfPath,
            ]);

            $sektor   = collect($request->sektor)->filter(fn($r) => !empty($r['nama_sektor']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $efek     = collect($request->efek)->filter(fn($r) => !empty($r['kode_efek']) && !empty($r['nama_efek']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $kinerja  = collect($request->kinerja)->filter(fn($r) => !empty($r['periode']) && isset($r['return_pct']) && $r['return_pct'] !== '')->values()->all();
            $obligasi = collect($request->obligasi)->filter(fn($r) => !empty($r['kode_obligasi']) && !empty($r['nama_obligasi']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $bank     = collect($request->bank)->filter(fn($r) => !empty($r['nama_bank']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();

            if ($sektor)   $analisa->sektor()->createMany($sektor);
            if ($efek)     $analisa->efek()->createMany($efek);
            if ($kinerja)  $analisa->kinerja()->createMany($kinerja);
            if ($obligasi) $analisa->obligasi()->createMany($obligasi);
            if ($bank)     $analisa->bank()->createMany($bank);

            $this->persistAiFromRequest($request, $analisa);
        });

        return redirect()->route($this->indexRoute())->with('success', 'Data analisa berhasil disubmit. Narasi AI sedang diproses.');
    }

    private function storeFromExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        DB::transaction(function () use ($request) {
            $pdfPath = $this->resolvePdfPath($request->pdf_file);

            $analisa = AnalisaReksaDana::create([
                'user_id'              => auth()->id(),
                'nama_reksa_dana'      => $request->nama_reksa_dana,
                'jenis_reksa_dana'     => $request->jenis_reksa_dana,
                'total_aum'            => $request->total_aum,
                'total_marcap_10_efek' => $request->total_marcap_10_efek,
                'status'               => 'submitted',
                'pdf_path'             => $pdfPath,
            ]);

            Excel::import(new AnalisaImport($analisa), $request->file('file_excel'));

            $this->persistAiFromRequest($request, $analisa);
        });

        return redirect()->route($this->indexRoute())->with('success', 'Data analisa berhasil diimport dari Excel. Narasi AI sedang diproses.');
    }

    private function persistAiFromRequest(Request $request, AnalisaReksaDana $analisa): void
    {
        $analisa->load(['sektor', 'efek', 'kinerja', 'obligasi', 'bank']);

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

    private function resolvePdfPath(?string $pdfFile): ?string
    {
        if (!$pdfFile) return null;

        $tempPath = 'analisa-pdfs/' . basename($pdfFile);

        if (!Storage::disk('public')->exists($tempPath)) {
            return null;
        }

        return $tempPath;
    }

    public function show(AnalisaReksaDana $analisa)
    {
        abort_if($analisa->user_id !== auth()->id(), 403);
        $analisa->load(['sektor', 'efek', 'kinerja', 'obligasi', 'bank']);

        return view('analisa.show', compact('analisa'));
    }

    public function exportPdf(AnalisaReksaDana $analisa)
    {
        abort_if($analisa->user_id !== auth()->id(), 403);
        $analisa->load(['user', 'sektor', 'efek', 'kinerja', 'obligasi', 'bank']);

        $pdf = Pdf::loadView('analisa.pdf', compact('analisa'))
            ->setPaper('a4', 'portrait');

        $filename = 'analisa-'.str($analisa->nama_reksa_dana)->slug().'-'.now()->format('Ymd').'.pdf';

        return $pdf->download($filename);
    }

    public function downloadPdf(AnalisaReksaDana $analisa)
    {
        abort_if($analisa->user_id !== auth()->id(), 403);

        if (!$analisa->pdf_path || !Storage::disk('public')->exists($analisa->pdf_path)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        return Storage::disk('public')->download($analisa->pdf_path, 'ffs-'.str($analisa->nama_reksa_dana)->slug().'.pdf');
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
