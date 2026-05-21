<?php

namespace App\Http\Controllers;

use App\Services\GroqService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;

abstract class AnalisaLapkeuController extends Controller
{
    protected bool $isAdminContext = false;
    protected string $productLabel = '';

    abstract protected function getModel(): string;
    abstract protected function indexRouteName(): string;
    abstract protected function routePrefix(): string;
    abstract protected function createView(): string;
    abstract protected function indexView(): string;
    abstract protected function showView(): string;
    abstract protected function pdfView(): string;
    abstract protected function namaField(): string;
    abstract protected function validateBasicFields(Request $request): array;

    protected function instrumentType(): string
    {
        return $this->productLabel ?: 'Saham';
    }

    protected function layout(): string
    {
        return $this->isAdminContext ? 'layouts.admin' : 'layouts.user';
    }

    public function index(Request $request = null)
    {
        $model = $this->getModel();
        $items = $model::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view($this->indexView(), [
            'items' => $items,
            'productLabel' => $this->productLabel,
            'createRoute' => route($this->routePrefix() . '.create'),
        ]);
    }

    public function adminIndex(Request $request)
    {
        $model = $this->getModel();
        $query = $model::with('user')->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(20);

        return view($this->indexView(), [
            'items' => $items,
            'productLabel' => $this->productLabel,
        ]);
    }

    public function create()
    {
        $prefix = $this->routePrefix();
        return view($this->createView(), [
            'layout'         => $this->layout(),
            'productLabel'   => $this->productLabel,
            'storeRoute'     => route($prefix . '.store'),
            'templateRoute'  => route($prefix . '.template'),
            'cancelRoute'    => route($this->indexRouteName()),
            'routePrefix'    => $prefix,
            'previewAiRoute' => route($prefix . '.preview-ai'),
            'parsePdfRoute'  => route($prefix . '.parse-pdf'),
        ]);
    }

    public function parsePdf(Request $request, GroqService $groq)
    {
        $request->validate(['file_pdf' => 'required|file|max:10240']);

        $file = $request->file('file_pdf');

        // Pastikan ekstensi PDF
        if (strtolower($file->getClientOriginalExtension()) !== 'pdf') {
            return response()->json(['success' => false, 'message' => 'File harus berformat PDF.'], 422);
        }

        try {
            $parser = new PdfParser();
            $pdf    = $parser->parseFile($file->getPathname());
            $text   = $pdf->getText();
        } catch (\Throwable $e) {
            \Log::error('[parsePdf] Gagal membaca PDF: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membaca PDF: ' . $e->getMessage()]);
        }

        try {
            $data = $groq->parseLapkeuPdf($text, $this->instrumentType());
        } catch (\Throwable $e) {
            \Log::error('[parsePdf] Gagal parse AI: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal parse AI: ' . $e->getMessage()]);
        }

        // Simpan PDF sementara
        $filename   = 'lapkeu-' . now()->format('Ymd-His') . '-' . Str::random(8) . '.pdf';
        $storedPath = $file->storeAs('lapkeu-pdfs', $filename, 'public');
        $data['pdf_lapkeu_path'] = basename($storedPath);

        return response()->json(['success' => true, 'message' => 'PDF berhasil diparse.', 'data' => $data]);
    }

    public function store(Request $request)
    {
        $rules = array_merge($this->validateBasicFields($request), [
            'input_mode' => 'required|in:manual,excel',
        ]);

        $request->validate($rules);

        $model = $this->getModel();
        $data = array_merge(
            $this->extractLapkeuData($request),
            ['user_id' => auth()->id(), 'status' => 'submitted']
        );

        if ($request->filled('pdf_lapkeu_path')) {
            $path = 'lapkeu-pdfs/' . basename($request->pdf_lapkeu_path);
            $data['pdf_path'] = Storage::disk('public')->exists($path) ? $path : null;
        }

        $analisa = $model::create($data);

        if ($request->filled('ai_narasi') && $request->filled('ai_output')) {
            $analisa->update([
                'ai_narasi' => $request->ai_narasi,
                'ai_output' => json_decode($request->ai_output, true) ?: [],
            ]);
        }

        return redirect()->route($this->indexRouteName())
            ->with('success', 'Data analisa berhasil disubmit.');
    }

    public function show($analisa)
    {
        $model = $this->getModel();
        $analisa = $model::findOrFail($analisa);

        if (!$this->isAdminContext) {
            abort_if($analisa->user_id !== auth()->id(), 403);
        }

        return view($this->showView(), [
            'analisa' => $analisa,
            'productLabel' => $this->productLabel,
            'indexRoute' => $this->indexRouteName(),
            'pdfRoute' => $this->routePrefix() . '.pdf',
            'downloadRoute' => $this->routePrefix() . '.download-lapkeu',
            'reviewRoute' => $this->isAdminContext ? $this->routePrefix() . '.review' : null,
            'destroyRoute' => $this->routePrefix() . '.destroy',
        ]);
    }

    public function exportPdf($analisa)
    {
        $model = $this->getModel();
        $analisa = $model::findOrFail($analisa);

        if (!$this->isAdminContext) {
            abort_if($analisa->user_id !== auth()->id(), 403);
        }

        $analisa->load('user');

        $pdf = Pdf::loadView($this->pdfView(), compact('analisa'))
            ->setPaper('a4', 'portrait');

        $namaField = $this->namaField();
        $filename = 'analisa-' . str($analisa->$namaField)->slug() . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    public function downloadLapkeu($analisa)
    {
        $model = $this->getModel();
        $analisa = $model::findOrFail($analisa);

        if (!$this->isAdminContext) {
            abort_if($analisa->user_id !== auth()->id(), 403);
        }

        if (!$analisa->pdf_path || !Storage::disk('public')->exists($analisa->pdf_path)) {
            abort(404, 'File PDF Lapkeu tidak ditemukan.');
        }

        $namaField = $this->namaField();
        return Storage::disk('public')->download(
            $analisa->pdf_path,
            'lapkeu-' . str($analisa->$namaField)->slug() . '.pdf'
        );
    }

    public function review(Request $request, $analisa)
    {
        $model = $this->getModel();
        $analisa = $model::findOrFail($analisa);

        $request->validate(['catatan_admin' => 'nullable|string|max:1000']);

        $analisa->update([
            'status' => 'reviewed',
            'catatan_admin' => $request->catatan_admin,
        ]);

        return back()->with('success', 'Data analisa telah ditandai sebagai reviewed.');
    }

    public function destroy($analisa)
    {
        $model = $this->getModel();
        $analisa = $model::findOrFail($analisa);

        if (!$this->isAdminContext) {
            abort_if($analisa->user_id !== auth()->id(), 403);
            abort_if($analisa->status === 'reviewed', 403, 'Data yang sudah direview tidak dapat dihapus.');
        }

        if ($analisa->pdf_path && Storage::disk('public')->exists($analisa->pdf_path)) {
            Storage::disk('public')->delete($analisa->pdf_path);
        }

        $analisa->delete();

        return redirect()->route($this->indexRouteName())->with('success', 'Data analisa berhasil dihapus.');
    }

    public function previewAi(Request $request, GroqService $groq)
    {
        $basicRules = $this->validateBasicFields($request);
        $nameField = array_key_first($basicRules);
        $request->validate([$nameField => 'required|string|max:255']);

        try {
            $data = array_merge(
                $this->extractLapkeuData($request),
                [
                    'nama'      => $request->input($nameField),
                    'kode'      => $request->input('kode_saham') ?? $request->input('kode_obligasi'),
                    'periode'   => $request->input('periode'),
                    'mata_uang' => $request->input('mata_uang'),
                    'rating'    => $request->input('rating'),
                    'kupon'     => $request->input('kupon'),
                    'ytm'       => $request->input('ytm'),
                ]
            );

            $result = $groq->generateNarasiLapkeuStructured($data, $this->instrumentType());

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

    public function downloadTemplate()
    {
        return response()->download(
            public_path('templates/template-analisa-lapkeu.xlsx'),
            'template-analisa-lapkeu.xlsx'
        );
    }

    protected function extractLapkeuData(Request $request): array
    {
        $fields = [
            'mata_uang', 'periode', 'catatan',
            'current_asset', 'cash_equivalents', 'account_receivable', 'inventories',
            'other_current_asset', 'fixed_asset', 'other_non_current_asset', 'total_asset',
            'current_liabilities', 'account_payable', 'accruals', 'short_term_loans',
            'current_maturities_of_long_term_loans', 'other_current_liabilities',
            'long_term_loans', 'employee_benefits', 'other_non_current_liabilities',
            'total_non_current_liabilities', 'total_liabilities',
            'share_capital', 'additional_paid_in_capital', 'retained_earning', 'others',
            'non_controlling_interest', 'total_equity_equity_to_parent_entity', 'equity',
            'net_revenue', 'cost_of_good_sold', 'gross_income', 'operational_expense',
            'laba_operasional', 'other_income_expense', 'interest_expense', 'income_before_tax',
            'taxes', 'ebit', 'ebitda', 'net_income_attributable_to_non_controlling_interest',
            'net_income', 'cash_flows_operating_activities', 'cash_flows_investment',
            'cash_flows_financing',
        ];

        $data = [];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field) !== '' ? $request->input($field) : null;
            }
        }
        return $data;
    }
}
