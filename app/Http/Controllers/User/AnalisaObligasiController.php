<?php

namespace App\Http\Controllers\User;

use App\Enums\AnalisaDataSource;
use App\Enums\AnalisaType;
use App\Http\Controllers\AnalisaLapkeuController;
use App\Models\AnalisaObligasiKeuangan;
use App\Services\KeuanganEmitenService;
use App\Services\FinancialDataResolverService;
use App\Services\GroqService;
use App\Services\ShadowRatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class AnalisaObligasiController extends AnalisaLapkeuController
{
    protected string $productLabel = 'Obligasi';

    protected function getModel(): string { return AnalisaObligasiKeuangan::class; }
    protected function indexRouteName(): string { return 'user.analisa-obligasi.index'; }
    protected function routePrefix(): string { return 'user.analisa-obligasi'; }
    protected function createView(): string { return 'analisa-obligasi.create'; }
    protected function indexView(): string { return 'analisa-obligasi.index'; }
    protected function showView(): string { return 'analisa-obligasi.show'; }
    protected function pdfView(): string { return 'analisa-obligasi.pdf'; }
    protected function namaField(): string { return 'nama_obligasi'; }

    protected function validateBasicFields(Request $request): array
    {
        return [
            'nama_obligasi' => 'nullable|string|max:255',
            'kode_obligasi' => 'nullable|string|max:50',
            'nama_emiten' => 'nullable|string|max:255',
            'rating' => 'nullable|string|max:20',
            'official_rating' => 'nullable|string|max:20',
            'kupon' => 'nullable|numeric|min:0|max:100',
            'ytm' => 'nullable|numeric|min:0|max:100',
            'tenor_bulan' => 'nullable|integer|min:1',
            'mata_uang' => 'nullable|string|max:10',
            'periode' => 'nullable|string|max:20',
            'info_nama_obligasi' => 'nullable|string|max:255',
            'info_ytm' => 'nullable|numeric|min:0|max:100',
            'harga_obligasi' => 'nullable|numeric|min:0',
            'q1_obligasi' => 'nullable|numeric',
            'q2_obligasi' => 'nullable|numeric',
            'q3_obligasi' => 'nullable|numeric',
            'q4_obligasi' => 'nullable|numeric',
            'info_nominal_penerbitan' => 'nullable|numeric|min:0',
            'sektor' => 'nullable|string|max:100',
            'nominal_penerbit' => 'nullable|numeric|min:0',
            'tanggal_terbit' => 'nullable|date',
            'tanggal_jatuh_tempo' => 'nullable|date',
            'tanpa_jaminan' => 'nullable|boolean',
            'dengan_jaminan' => 'nullable|boolean',
            'periode_dari' => 'nullable|integer|digits:4',
            'periode_sampai' => 'nullable|integer|digits:4|gte:periode_dari',
            'jenis_analisa' => 'nullable|in:' . AnalisaType::ANALISA_PERIODE->value . ',' . AnalisaType::ANALISA_TAHUNAN->value,
            'tahun' => 'nullable|digits:4',
        ];
    }

    public function store(Request $request)
    {
        $request->validate(array_merge($this->validateBasicFields($request), [
            'input_mode' => 'required|in:manual,excel',
            'pdf_lapkeu' => 'nullable|file|mimes:pdf|max:20480',
        ]));

        $preparedData = $this->prepareObligasiAnalysisData($request);

        $data = array_merge(
            $preparedData,
            [
                'user_id' => auth()->id(),
                'nama_obligasi' => $request->nama_obligasi,
                'kode_obligasi' => $request->kode_obligasi,
                'nama_emiten' => $request->nama_emiten,
                'rating' => $request->rating,
                'official_rating' => $request->official_rating,
                'mata_uang' => $request->mata_uang,
                'kupon' => $request->kupon,
                'ytm' => $request->ytm,
                'sektor' => $request->sektor,
                'info_nama_obligasi' => $request->info_nama_obligasi,
                'info_ytm' => $request->info_ytm,
                'harga_obligasi' => $request->harga_obligasi,
                'q1_obligasi' => $request->q1_obligasi,
                'q2_obligasi' => $request->q2_obligasi,
                'q3_obligasi' => $request->q3_obligasi,
                'q4_obligasi' => $request->q4_obligasi,
                'info_nominal_penerbitan' => $request->info_nominal_penerbitan,
                'nominal_penerbit' => $request->nominal_penerbit,
                'tanggal_terbit' => $request->tanggal_terbit,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'tanpa_jaminan' => $request->boolean('tanpa_jaminan'),
                'dengan_jaminan' => $request->boolean('dengan_jaminan'),
                'periode_dari' => $request->periode_dari,
                'periode_sampai' => $request->periode_sampai,
                'tenor_bulan' => $request->tenor_bulan,
                'jenis_analisa' => $request->input('jenis_analisa', AnalisaType::ANALISA_PERIODE->value),
                'status' => 'submitted',
            ]
        );

        if ($request->hasFile('pdf_lapkeu')) {
            $file = $request->file('pdf_lapkeu');
            $filename = 'lapkeu-' . now()->format('Ymd-His') . '-' . \Illuminate\Support\Str::random(8) . '.pdf';
            $data['pdf_path'] = $file->storeAs('lapkeu-pdfs', $filename, 'public');
        } elseif ($request->filled('pdf_lapkeu_path')) {
            $path = 'lapkeu-pdfs/' . basename($request->pdf_lapkeu_path);
            $data['pdf_path'] = \Illuminate\Support\Facades\Storage::disk('public')->exists($path) ? $path : null;
        }

        $analisa = AnalisaObligasiKeuangan::create($data);

        $this->persistLapkeuAiFromRequest($request, $analisa);

        $this->calculateShadowRating($analisa);
        $this->calculateYtmSpread($analisa);

        return redirect()->route($this->routePrefix() . '.show', $analisa->id)
            ->with('success', 'Data analisa obligasi berhasil disubmit. Analisa AI sedang diproses.');
    }

    public function show($id)
    {
        $analisa = AnalisaObligasiKeuangan::findOrFail($id);
        abort_if($analisa->user_id !== auth()->id(), 403);

        return view($this->showView(), [
            'analisa' => $analisa,
            'productLabel' => $this->productLabel,
            'indexRoute' => $this->indexRouteName(),
            'pdfRoute' => $this->routePrefix() . '.pdf',
            'downloadRoute' => $this->routePrefix() . '.download-lapkeu',
            'destroyRoute' => $this->routePrefix() . '.destroy',
            'checkAiStatusRoute' => $this->routePrefix() . '.check-ai-status',
        ]);
    }

    public function exportPdf($id)
    {
        $analisa = AnalisaObligasiKeuangan::with('user')->findOrFail($id);
        abort_if($analisa->user_id !== auth()->id(), 403);

        $pdf = Pdf::loadView($this->pdfView(), compact('analisa'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('analisa-obligasi-' . str($analisa->nama_obligasi)->slug() . '-' . now()->format('Ymd') . '.pdf');
    }

    public function downloadLapkeu($id)
    {
        $analisa = AnalisaObligasiKeuangan::findOrFail($id);
        abort_if($analisa->user_id !== auth()->id(), 403);

        if (!$analisa->pdf_path || !Storage::disk('public')->exists($analisa->pdf_path)) {
            abort(404, 'File PDF Lapkeu tidak ditemukan.');
        }

        return Storage::disk('public')->download(
            $analisa->pdf_path,
            'lapkeu-' . str($analisa->nama_obligasi)->slug() . '.pdf'
        );
    }

    public function destroy($id)
    {
        $analisa = AnalisaObligasiKeuangan::findOrFail($id);
        abort_if($analisa->user_id !== auth()->id(), 403);
        abort_if($analisa->status === 'reviewed', 403, 'Data yang sudah direview tidak dapat dihapus.');

        if ($analisa->pdf_path && Storage::disk('public')->exists($analisa->pdf_path)) {
            Storage::disk('public')->delete($analisa->pdf_path);
        }

        $analisa->delete();

        return redirect()->route($this->indexRouteName())->with('success', 'Data analisa obligasi berhasil dihapus.');
    }

    public function lookupKeuanganEmiten(Request $request, KeuanganEmitenService $service)
    {
        $request->validate([
            'kode_obligasi' => 'required|string|max:50',
            'jenis_analisa' => 'nullable|in:' . AnalisaType::ANALISA_PERIODE->value . ',' . AnalisaType::ANALISA_TAHUNAN->value,
            'periode' => 'nullable|digits:6',
            'tahun' => 'nullable|digits:4',
        ]);

        $jenisAnalisa = $request->input('jenis_analisa', AnalisaType::ANALISA_PERIODE->value);
        $kode = $request->input('kode_obligasi');

        if ($jenisAnalisa === AnalisaType::ANALISA_TAHUNAN->value) {
            if (!$request->filled('tahun')) {
                return response()->json([
                    'found' => false,
                    'message' => 'Isi Tahun dengan format YYYY.',
                ], 422);
            }

            $records = $service->getByYear($kode, $request->tahun);
            if ($records->isEmpty()) {
                return response()->json([
                    'found' => false,
                    'message' => "Data Keuangan Emiten {$kode} untuk tahun {$request->tahun} tidak ditemukan.",
                ], 404);
            }

            $latest = $records->last();

            return response()->json([
                'found' => true,
                'message' => "{$records->count()} data Keuangan Emiten ditemukan dan siap diproses.",
                'data' => array_merge($this->mapKeuanganEmitenRecord($latest), [
                    'kode_obligasi' => strtoupper($kode),
                    'tahun' => $request->tahun,
                    'data_tahunan' => $records->map(fn ($record) => $this->mapKeuanganEmitenRecord($record))->values()->all(),
                ]),
            ]);
        }

        if (!$request->filled('periode')) {
            return response()->json([
                'found' => false,
                'message' => 'Isi Periode LapKeu dengan format YYYYMM.',
            ], 422);
        }

        $record = $service->getByPeriod($kode, $request->periode);
        if (!$record) {
            return response()->json([
                'found' => false,
                'message' => "Data Keuangan Emiten {$kode} periode {$request->periode} tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'found' => true,
            'message' => 'Data Keuangan Emiten ditemukan dan sudah dimasukkan ke form analisa.',
            'data' => array_merge($this->mapKeuanganEmitenRecord($record), [
                'kode_obligasi' => strtoupper($kode),
            ]),
        ]);
    }

    public function resolveAiPlusData(Request $request, FinancialDataResolverService $resolver)
    {
        $resolved = $this->resolvedFinancialData($request, $resolver);

        return response()->json([
            'complete' => $resolver->isComplete($resolved),
            'missing' => $resolver->missingFields($resolved),
            'data' => $resolved,
        ]);
    }

    public function previewAiPlus(Request $request, GroqService $groq)
    {
        $request->validate(['nama_obligasi' => 'required|string|max:255']);

        $resolver = app(FinancialDataResolverService::class);
        $resolved = $this->resolvedFinancialData($request, $resolver);
        if (!$resolver->isComplete($resolved)) {
            $missing = $resolver->missingFields($resolved);

            return response()->json([
                'success' => false,
                'message' => 'Data Analisa AI Plus belum lengkap: ' . implode(', ', $missing) . '.',
                'missing' => $missing,
                'resolved_data' => $resolved,
            ], 422);
        }

        try {
            $data = array_merge($this->analysisDataFromRequest($request), $resolver->toAnalysisData($resolved), [
                'nama' => $request->nama_obligasi,
                'kode' => $request->kode_obligasi,
                'periode' => $request->periode,
                'mata_uang' => $request->mata_uang,
                'rating' => $request->rating,
                'official_rating' => $request->official_rating,
                'kupon' => $request->kupon,
                'ytm' => $request->ytm,
                'tenor_bulan' => $request->tenor_bulan,
                'kode_obligasi' => $request->kode_obligasi,
            ]);

            $tempModel = new AnalisaObligasiKeuangan();
            $fillable = $tempModel->getFillable();
            $tempModel->forceFill(array_intersect_key($data, array_flip($fillable)));

            $shadowService = app(ShadowRatingService::class);
            $shadowResult = $shadowService->calculate($tempModel);

            $data = array_merge($data, [
                'shadow_rating' => $shadowResult['shadow_rating'],
                'shadow_score' => $shadowResult['shadow_score'],
                'shadow_confidence' => $shadowResult['shadow_confidence'],
                'rating_source' => $request->official_rating ? 'official' : ($request->rating ? 'manual' : 'shadow'),
            ]);

            $ytmSpread = $shadowService->calculateYtmSpread($tempModel);
            if ($ytmSpread['ytm_normal'] !== null) {
                $data = array_merge($data, $ytmSpread);
            }

            return response()->json([
                'success' => true,
                'message' => 'Analisa AI Plus berhasil dibuat.',
                'data' => $groq->generateNarasiLapkeuPlusStructured($data, $this->instrumentType()),
                'resolved_data' => $resolved,
                'shadow_rating' => $shadowResult,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat Analisa AI Plus: ' . $e->getMessage(),
            ], 422);
        }
    }

    protected function resolvedFinancialData(Request $request, FinancialDataResolverService $resolver): array
    {
        $sources = json_decode((string) $request->input('financial_data_sources', '{}'), true);

        return $resolver->resolveObligationData($request->input('kode_obligasi'), [
            'draft' => $this->extractLapkeuData($request),
            'sources' => is_array($sources) ? $sources : [],
            'pdf_path' => $request->input('pdf_lapkeu_path'),
            'periode' => $request->input('periode'),
        ]);
    }

    protected function prepareObligasiAnalysisData(Request $request): array
    {
        $jenisAnalisa = $request->input('jenis_analisa', AnalisaType::ANALISA_PERIODE->value);
        $kode = $request->input('kode_obligasi');

        if (!$kode || !in_array($jenisAnalisa, [AnalisaType::ANALISA_PERIODE->value, AnalisaType::ANALISA_TAHUNAN->value], true)) {
            return $this->extractLapkeuData($request);
        }

        $service = app(KeuanganEmitenService::class);

        if ($jenisAnalisa === AnalisaType::ANALISA_TAHUNAN->value && $request->filled('tahun')) {
            $records = $service->getByYear($kode, $request->tahun);

            if ($records->isNotEmpty()) {
                $latest = $records->last();

                return array_merge(
                    $this->mapKeuanganEmitenRecord($latest),
                    [
                        'periode' => $latest->periode,
                        'tahun' => $request->tahun,
                        'sumber_data' => AnalisaDataSource::DATABASE_KEUANGAN_EMITEN->value,
                        'data_tahunan' => $records->map(fn ($record) => $this->mapKeuanganEmitenRecord($record))->values()->all(),
                    ]
                );
            }
        }

        if ($jenisAnalisa === AnalisaType::ANALISA_PERIODE->value && $request->filled('periode')) {
            $record = $service->getByPeriod($kode, $request->periode);

            if ($record) {
                return array_merge(
                    $this->mapKeuanganEmitenRecord($record),
                    [
                        'periode' => $record->periode,
                        'sumber_data' => AnalisaDataSource::DATABASE_KEUANGAN_EMITEN->value,
                    ]
                );
            }
        }

        return array_merge(
            $this->extractLapkeuData($request),
            ['sumber_data' => AnalisaDataSource::UPLOAD_EXCEL->value]
        );
    }

    protected function mapKeuanganEmitenRecord($record): array
    {
        $data = $record->only([
            'kode', 'periode',
            'current_asset', 'cash_equivalents', 'account_receivable', 'inventories',
            'other_current_asset', 'fixed_asset', 'other_non_current_asset', 'total_asset',
            'current_liabilities', 'account_payable', 'accruals', 'short_term_loans',
            'current_maturities_of_long_term_loans', 'other_current_liabilities',
            'long_term_loans', 'other_non_current_liabilities',
            'total_non_current_liabilities', 'total_liabilities',
            'share_capital', 'additional_paid_in_capital', 'retained_earning', 'others',
            'non_controlling_interest', 'total_equity_equity_to_parent_entity', 'equity',
            'net_revenue', 'cost_of_good_sold', 'gross_income', 'operational_expense',
            'laba_operasional', 'other_income_expense', 'interest_expense', 'income_before_tax',
            'taxes', 'ebit', 'ebitda', 'net_income_attributable_to_non_controlling_interest',
            'net_income', 'cash_flows_operating_activities', 'cash_flows_investment',
            'cash_flows_financing',
        ]);

        unset($data['kode']);

        return $data;
    }

    protected function calculateShadowRating(AnalisaObligasiKeuangan $analisa): void
    {
        try {
            $service = app(ShadowRatingService::class);
            $result = $service->calculate($analisa);

            $analisa->update([
                'shadow_rating' => $result['shadow_rating'],
                'shadow_score' => $result['shadow_score'],
                'shadow_confidence' => $result['shadow_confidence'],
                'rating_source' => $this->determineRatingSource($analisa, $result),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Shadow rating calculation failed', [
                'analisa_id' => $analisa->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function calculateYtmSpread(AnalisaObligasiKeuangan $analisa): void
    {
        try {
            $service = app(ShadowRatingService::class);
            $result = $service->calculateYtmSpread($analisa);

            if ($result['ytm_normal'] !== null) {
                $analisa->update([
                    'ytm_normal' => $result['ytm_normal'],
                    'ytm_spread' => $result['ytm_spread'],
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('YTM spread calculation failed', [
                'analisa_id' => $analisa->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function determineRatingSource(AnalisaObligasiKeuangan $analisa, array $shadowResult): ?string
    {
        if ($analisa->official_rating) return 'official';
        if ($analisa->rating) return 'manual';
        if (!empty($shadowResult['shadow_rating'])) return 'shadow';
        return null;
    }
}
