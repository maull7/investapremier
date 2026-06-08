<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AnalisaLapkeuController;
use App\Models\AnalisaSaham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;

class MonitorAnalisaSahamController extends AnalisaLapkeuController
{
    protected bool $isAdminContext = true;
    protected string $productLabel = 'Saham';

    protected function getModel(): string
    {
        return AnalisaSaham::class;
    }
    protected function indexRouteName(): string
    {
        return 'admin.analisa-saham.index';
    }
    protected function routePrefix(): string
    {
        return 'admin.analisa-saham';
    }
    protected function createView(): string
    {
        return 'analisa-saham.create';
    }
    protected function indexView(): string
    {
        return 'admin.analisa-saham.index';
    }
    protected function showView(): string
    {
        return 'admin.analisa-saham.show';
    }
    protected function pdfView(): string
    {
        return 'analisa-saham.pdf';
    }
    protected function namaField(): string
    {
        return 'nama_perusahaan';
    }

    protected function validateBasicFields(Request $request): array
    {
        return [
            'nama_perusahaan' => 'required|string|max:255',
        ];
    }

    public function index(?Request $request = null)
    {
        $request ??= request();
        $query = AnalisaSaham::with('user')->latest();

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
        $analisa = AnalisaSaham::with(['user', 'brokerResearchDocuments.uploader'])->findOrFail($id);

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
        $analisa = AnalisaSaham::with('user')->findOrFail($id);

        $pdf = Pdf::loadView($this->pdfView(), compact('analisa'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('analisa-saham-' . str($analisa->nama_perusahaan)->slug() . '-' . now()->format('Ymd') . '.pdf');
    }

    public function downloadLapkeu($id)
    {
        $analisa = AnalisaSaham::findOrFail($id);

        if (!$analisa->pdf_path || !Storage::disk('public')->exists($analisa->pdf_path)) {
            abort(404, 'File PDF Lapkeu tidak ditemukan.');
        }

        return Storage::disk('public')->download(
            $analisa->pdf_path,
            'lapkeu-' . str($analisa->nama_perusahaan)->slug() . '.pdf'
        );
    }

    public function review(Request $request, $id)
    {
        $analisa = AnalisaSaham::findOrFail($id);
        $request->validate(['catatan_admin' => 'nullable|string|max:1000']);

        $analisa->update([
            'status' => 'reviewed',
            'catatan_admin' => $request->catatan_admin,
        ]);

        ActivityLogger::log(
            'Review Analisa Saham',
            "Analisa saham {$analisa->nama_perusahaan} telah direview",
            'success',
            $analisa,
        );

        return back()->with('success', 'Data analisa saham telah ditandai sebagai reviewed.');
    }

    public function destroy($id)
    {
        $analisa = AnalisaSaham::findOrFail($id);

        ActivityLogger::log(
            'Menghapus Analisa Saham',
            "Analisa saham {$analisa->nama_perusahaan} berhasil dihapus",
            'success',
            $analisa,
        );

        if ($analisa->pdf_path && Storage::disk('public')->exists($analisa->pdf_path)) {
            Storage::disk('public')->delete($analisa->pdf_path);
        }

        $analisa->delete();

        return redirect()->route($this->indexRouteName())->with('success', 'Data analisa saham berhasil dihapus.');
    }
}
