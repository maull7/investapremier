<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\AnalisaLapkeuController;
use App\Models\AnalisaSaham;
use Illuminate\Http\Request;

class AnalisaSahamController extends AnalisaLapkeuController
{
    protected string $productLabel = 'Saham';

    protected function getModel(): string { return AnalisaSaham::class; }
    protected function indexRouteName(): string { return 'user.analisa-saham.index'; }
    protected function routePrefix(): string { return 'user.analisa-saham'; }
    protected function createView(): string { return 'analisa-saham.create'; }
    protected function indexView(): string { return 'analisa-saham.index'; }
    protected function showView(): string { return 'analisa-saham.show'; }
    protected function pdfView(): string { return 'analisa-saham.pdf'; }
    protected function namaField(): string { return 'nama_perusahaan'; }

    protected function validateBasicFields(Request $request): array
    {
        return [
            'nama_perusahaan' => 'required|string|max:255',
            'kode_saham' => 'nullable|string|max:20',
            'sektor' => 'nullable|string|max:100',
            'mata_uang' => 'nullable|string|max:10',
            'periode' => 'nullable|string|max:20',
        ];
    }

    public function store(Request $request)
    {
        $request->validate(array_merge($this->validateBasicFields($request), [
            'input_mode' => 'required|in:manual,excel',
            'pdf_lapkeu' => 'nullable|file|mimes:pdf|max:10240',
        ]));

        $data = array_merge(
            $this->extractLapkeuData($request),
            [
                'user_id' => auth()->id(),
                'nama_perusahaan' => $request->nama_perusahaan,
                'kode_saham' => $request->kode_saham,
                'sektor' => $request->sektor,
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

        $analisa = AnalisaSaham::create($data);

        if ($request->filled('ai_narasi') && $request->filled('ai_output')) {
            $analisa->update([
                'ai_narasi' => $request->ai_narasi,
                'ai_output' => json_decode($request->ai_output, true) ?: [],
            ]);
        }

        if ($request->filled('ai_narasi_plus') && $request->filled('ai_output_plus')) {
            $analisa->update([
                'ai_narasi_plus' => $request->ai_narasi_plus,
                'ai_output_plus' => json_decode($request->ai_output_plus, true) ?: [],
            ]);
        }

        return redirect()->route($this->indexRouteName())
            ->with('success', 'Data analisa saham berhasil disubmit.');
    }

    public function show($analisa)
    {
        $analisa = AnalisaSaham::findOrFail($analisa);
        abort_if($analisa->user_id !== auth()->id(), 403);

        return view($this->showView(), [
            'analisa' => $analisa,
            'productLabel' => $this->productLabel,
            'indexRoute' => $this->indexRouteName(),
            'pdfRoute' => $this->routePrefix() . '.pdf',
            'downloadRoute' => $this->routePrefix() . '.download-lapkeu',
            'destroyRoute' => $this->routePrefix() . '.destroy',
        ]);
    }

    public function exportPdf($id)
    {
        $analisa = AnalisaSaham::with('user')->findOrFail($id);
        abort_if($analisa->user_id !== auth()->id(), 403);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($this->pdfView(), compact('analisa'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('analisa-saham-' . str($analisa->nama_perusahaan)->slug() . '-' . now()->format('Ymd') . '.pdf');
    }

    public function downloadLapkeu($id)
    {
        $analisa = AnalisaSaham::findOrFail($id);
        abort_if($analisa->user_id !== auth()->id(), 403);

        if (!$analisa->pdf_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($analisa->pdf_path)) {
            abort(404, 'File PDF Lapkeu tidak ditemukan.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download(
            $analisa->pdf_path,
            'lapkeu-' . str($analisa->nama_perusahaan)->slug() . '.pdf'
        );
    }

    public function destroy($id)
    {
        $analisa = AnalisaSaham::findOrFail($id);
        abort_if($analisa->user_id !== auth()->id(), 403);
        abort_if($analisa->status === 'reviewed', 403, 'Data yang sudah direview tidak dapat dihapus.');

        if ($analisa->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($analisa->pdf_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($analisa->pdf_path);
        }

        $analisa->delete();

        return redirect()->route($this->indexRouteName())->with('success', 'Data analisa saham berhasil dihapus.');
    }
}
