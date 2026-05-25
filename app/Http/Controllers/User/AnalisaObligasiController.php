<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\AnalisaLapkeuController;
use App\Models\AnalisaObligasiKeuangan;
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
            'nama_obligasi' => 'required|string|max:255',
            'kode_obligasi' => 'nullable|string|max:50',
            'nama_emiten' => 'nullable|string|max:255',
            'rating' => 'nullable|string|max:20',
            'kupon' => 'nullable|numeric|min:0|max:100',
            'ytm' => 'nullable|numeric|min:0|max:100',
            'mata_uang' => 'nullable|string|max:10',
            'periode' => 'nullable|string|max:20',
        ];
    }

    public function store(Request $request)
    {
        $request->validate(array_merge($this->validateBasicFields($request), [
            'input_mode' => 'required|in:manual,excel',
            'pdf_lapkeu' => 'nullable|file|mimes:pdf|max:20480',
        ]));

        $data = array_merge(
            $this->extractLapkeuData($request),
            [
                'user_id' => auth()->id(),
                'nama_obligasi' => $request->nama_obligasi,
                'kode_obligasi' => $request->kode_obligasi,
                'nama_emiten' => $request->nama_emiten,
                'rating' => $request->rating,
                'kupon' => $request->kupon,
                'ytm' => $request->ytm,
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
}
