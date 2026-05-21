<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalisaReksaDana;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MonitorAnalisaUlController extends Controller
{
    public function index(Request $request)
    {
        $query = AnalisaReksaDana::with('user')
            ->where('product_type', 'unit_link')
            ->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $analisas = $query->paginate(20);

        return view('admin.unit-link-analisa.index', compact('analisas'));
    }

    public function show(AnalisaReksaDana $analisa)
    {
        $analisa->load(['user', 'sektor', 'efek', 'kinerja', 'obligasi', 'bank']);

        return view('admin.unit-link-analisa.show', compact('analisa'));
    }

    public function exportPdf(AnalisaReksaDana $analisa)
    {
        $analisa->load(['user', 'sektor', 'efek', 'kinerja', 'obligasi', 'bank']);

        $pdf = Pdf::loadView('analisa.pdf', compact('analisa'))
            ->setPaper('a4', 'portrait');

        $filename = 'analisa-' . str($analisa->nama_reksa_dana)->slug() . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    public function downloadPdf(AnalisaReksaDana $analisa)
    {
        if (!$analisa->pdf_path || !Storage::disk('public')->exists($analisa->pdf_path)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        return Storage::disk('public')->download($analisa->pdf_path, 'ffs-' . str($analisa->nama_reksa_dana)->slug() . '.pdf');
    }

    public function review(Request $request, AnalisaReksaDana $analisa)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string|max:1000',
        ]);

        $analisa->update([
            'status'        => 'reviewed',
            'catatan_admin' => $request->catatan_admin,
        ]);

        return back()->with('success', 'Data analisa telah ditandai sebagai reviewed.');
    }

    public function destroy(AnalisaReksaDana $analisa)
    {
        if ($analisa->pdf_path && Storage::disk('public')->exists($analisa->pdf_path)) {
            Storage::disk('public')->delete($analisa->pdf_path);
        }

        $analisa->delete();

        return redirect()->route('admin.unit-link-analisa.index')->with('success', 'Data analisa berhasil dihapus.');
    }
}
