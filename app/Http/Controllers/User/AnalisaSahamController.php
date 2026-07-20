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
            'periode_dari' => 'nullable|integer|digits:4',
            'periode_sampai' => 'nullable|integer|digits:4|gte:periode_dari',
            'nama_saham' => 'nullable|string|max:255',
            'jumlah_lembar_saham' => 'nullable|numeric|min:0',
            'harga_saham' => 'nullable|numeric|min:0',
            'q1_saham' => 'nullable|numeric',
            'q2_saham' => 'nullable|numeric',
            'q3_saham' => 'nullable|numeric',
            'q4_saham' => 'nullable|numeric',
            'kapitalisasi_pasar' => 'nullable|numeric|min:0',
        ];
    }

    public function store(Request $request)
    {
        $request->validate(array_merge($this->validateBasicFields($request), [
            'input_mode' => 'required|in:manual,excel',
            'pdf_lapkeu' => 'nullable|file|mimes:pdf|max:20480',
            'broker_research' => 'nullable|array',
            'broker_research.*.broker' => 'nullable|required_with:broker_research.*.document|string|max:100',
            'broker_research.*.document' => 'nullable|file|mimes:pdf,docx|max:5120',
        ]));

        $data = array_merge(
            $this->extractLapkeuData($request),
            [
                'user_id' => auth()->id(),
                'nama_perusahaan' => $request->nama_perusahaan,
                'kode_saham' => $request->kode_saham,
                'sektor' => $request->sektor,
                'mata_uang' => $request->mata_uang,
                'nama_saham' => $request->nama_saham,
                'jumlah_lembar_saham' => $request->jumlah_lembar_saham,
                'harga_saham' => $request->harga_saham,
                'q1_saham' => $request->q1_saham,
                'q2_saham' => $request->q2_saham,
                'q3_saham' => $request->q3_saham,
                'q4_saham' => $request->q4_saham,
                'kapitalisasi_pasar' => $request->kapitalisasi_pasar,
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
        $this->persistInitialBrokerResearchDocument($request, $analisa);

        $this->persistLapkeuAiFromRequest($request, $analisa);

        return redirect()->route($this->routePrefix() . '.show', $analisa->id)
            ->with('success', 'Data analisa saham berhasil disubmit. Analisa AI sedang diproses.');
    }

    public function show($analisa)
    {
        $analisa = AnalisaSaham::with('brokerResearchDocuments.uploader')->findOrFail($analisa);
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

    protected function persistInitialBrokerResearchDocument(Request $request, AnalisaSaham $analisa): void
    {
        if (!$request->hasFile('broker_research')) {
            return;
        }

        $documents = $request->file('broker_research', []);
        $brokers = $request->input('broker_research', []);

        foreach ($documents as $index => $document) {
            $file = $document['document'] ?? null;

            if (!$file) {
                continue;
            }

            $filename = now()->format('Ymd-His') . '-' . \Illuminate\Support\Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('riset-broker/' . $analisa->id, $filename, 'public');

            $analisa->brokerResearchDocuments()->create([
                'uploaded_by' => $request->user()->id,
                'broker' => $brokers[$index]['broker'] ?? '',
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }
}
