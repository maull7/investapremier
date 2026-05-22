<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AnalisaLapkeuController;
use App\Models\AnalisaObligasiKeuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class MonitorAnalisaObligasiController extends AnalisaLapkeuController
{
    protected bool $isAdminContext = true;
    protected string $productLabel = 'Obligasi';

    protected function getModel(): string { return AnalisaObligasiKeuangan::class; }
    protected function indexRouteName(): string { return 'admin.analisa-obligasi.index'; }
    protected function routePrefix(): string { return 'admin.analisa-obligasi'; }
    protected function createView(): string { return 'analisa-obligasi.create'; }
    protected function indexView(): string { return 'admin.analisa-obligasi.index'; }
    protected function showView(): string { return 'admin.analisa-obligasi.show'; }
    protected function pdfView(): string { return 'analisa-obligasi.pdf'; }
    protected function namaField(): string { return 'nama_obligasi'; }

    protected function validateBasicFields(Request $request): array
    {
        return [
            'nama_obligasi' => 'required|string|max:255',
        ];
    }

    public function index(?Request $request = null)
    {
        $request ??= request();
        $query = AnalisaObligasiKeuangan::with('user')->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(20);

        return view($this->indexView(), [
            'items' => $items,
            'productLabel' => $this->productLabel,
        ]);
    }

    public function show($id)
    {
        $analisa = AnalisaObligasiKeuangan::with('user')->findOrFail($id);

        return view($this->showView(), [
            'analisa' => $analisa,
            'productLabel' => $this->productLabel,
            'indexRoute' => $this->indexRouteName(),
            'pdfRoute' => $this->routePrefix() . '.pdf',
            'downloadRoute' => $this->routePrefix() . '.download-lapkeu',
            'reviewRoute' => $this->routePrefix() . '.review',
            'destroyRoute' => $this->routePrefix() . '.destroy',
            'checkAiStatusRoute' => $this->routePrefix() . '.check-ai-status',
        ]);
    }

    public function exportPdf($id)
    {
        $analisa = AnalisaObligasiKeuangan::with('user')->findOrFail($id);

        $pdf = Pdf::loadView($this->pdfView(), compact('analisa'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('analisa-obligasi-' . str($analisa->nama_obligasi)->slug() . '-' . now()->format('Ymd') . '.pdf');
    }

    public function downloadLapkeu($id)
    {
        $analisa = AnalisaObligasiKeuangan::findOrFail($id);

        if (!$analisa->pdf_path || !Storage::disk('public')->exists($analisa->pdf_path)) {
            abort(404, 'File PDF Lapkeu tidak ditemukan.');
        }

        return Storage::disk('public')->download(
            $analisa->pdf_path,
            'lapkeu-' . str($analisa->nama_obligasi)->slug() . '.pdf'
        );
    }

    public function review(Request $request, $id)
    {
        $analisa = AnalisaObligasiKeuangan::findOrFail($id);
        $request->validate(['catatan_admin' => 'nullable|string|max:1000']);

        $analisa->update([
            'status' => 'reviewed',
            'catatan_admin' => $request->catatan_admin,
        ]);

        return back()->with('success', 'Data analisa obligasi telah ditandai sebagai reviewed.');
    }

    public function destroy($id)
    {
        $analisa = AnalisaObligasiKeuangan::findOrFail($id);

        if ($analisa->pdf_path && Storage::disk('public')->exists($analisa->pdf_path)) {
            Storage::disk('public')->delete($analisa->pdf_path);
        }

        $analisa->delete();

        return redirect()->route($this->indexRouteName())->with('success', 'Data analisa obligasi berhasil dihapus.');
    }
}
