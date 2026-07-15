<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalisaReksaDana;
use App\Models\ReksaDana;
use App\Models\ReksaDanaDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Storage;

class AnalisaController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'analisa');

        if ($tab === 'prospektus') {
            return $this->indexProspektus($request);
        }

        if ($tab === 'ffs') {
            return $this->indexFfs($request);
        }

        return $this->indexAnalisa($request);
    }

    protected function indexAnalisa(Request $request)
    {
        $query = ReksaDana::with([
            'documents' => fn($q) => $q->where('document_type', 'ffs')->orderBy('ffs_year', 'desc')->orderBy('ffs_month', 'desc'),
            'analisa' => fn($q) => $q->where('product_type', 'reksa_dana')->with('user')->latest(),
        ]);

        if ($request->status) {
            if ($request->status === 'original') {
                $query->whereDoesntHave('analisa', fn($q) => $q->where('product_type', 'reksa_dana'));
            } else {
                $query->whereHas('analisa', fn($q) => $q->where('product_type', 'reksa_dana')->where('status', $request->status));
            }
        }

        if ($request->kategori) {
            $query->where(function ($q) use ($request) {
                $q->whereJsonContains('kategori', $request->kategori)
                  ->orWhereHas('analisa', fn($qq) => $qq->where('product_type', 'reksa_dana')->whereJsonContains('kategori', $request->kategori));
            });
        }

        if ($request->filled('ffs_bulan')) {
            $query->whereHas('analisa', fn($q) => $q->where('product_type', 'reksa_dana')->where('ffs_bulan', $request->ffs_bulan));
        }

        if ($request->filled('ffs_tahun')) {
            $query->whereHas('analisa', fn($q) => $q->where('product_type', 'reksa_dana')->where('ffs_tahun', $request->ffs_tahun));
        }

        if ($request->filled('mode')) {
            $query->whereHas('analisa', fn($q) => $q->where('product_type', 'reksa_dana')->where('mode', $request->mode));
        }

        if ($request->filled('is_published')) {
            $pub = $request->is_published === '1';
            $query->whereHas('analisa', fn($q) => $q->where('product_type', 'reksa_dana')->where('is_published', $pub));
        }

        $sort = $request->get('sort', 'nama_reksa_dana');
        $direction = $request->get('direction', 'asc');

        $reksaDanas = $query->orderBy($sort, $direction)->paginate(20);

        $tahunList = AnalisaReksaDana::where('product_type', 'reksa_dana')
            ->whereNotNull('ffs_tahun')
            ->distinct()->orderBy('ffs_tahun', 'desc')->pluck('ffs_tahun');

        $bulanIndonesia = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        return view('admin.analisa.index', compact('reksaDanas', 'tahunList', 'bulanIndonesia') + ['tab' => 'analisa']);
    }

    protected function indexProspektus(Request $request)
    {
        $query = ReksaDana::with([
            'documents' => fn($q) => $q->where('document_type', 'prospektus')->orderBy('ffs_year', 'desc')->orderBy('ffs_month', 'desc')->take(1),
        ])->whereHas('documents', fn($q) => $q->where('document_type', 'prospektus'));

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_reksa_dana', 'like', "%{$s}%")
                  ->orWhere('kode_reksa_dana', 'like', "%{$s}%");
            });
        }

        $reksaDanas = $query->orderBy('nama_reksa_dana')->paginate(20);

        $bulanIndonesia = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        return view('admin.analisa.index', compact('reksaDanas', 'bulanIndonesia') + ['tab' => 'prospektus']);
    }

    protected function indexFfs(Request $request)
    {
        $query = ReksaDana::with([
            'documents' => fn($q) => $q->where('document_type', 'ffs')->orderBy('ffs_year', 'desc')->orderBy('ffs_month', 'desc')->take(1),
        ])->whereHas('documents', fn($q) => $q->where('document_type', 'ffs'));

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_reksa_dana', 'like', "%{$s}%")
                  ->orWhere('kode_reksa_dana', 'like', "%{$s}%");
            });
        }

        if ($request->filled('ffs_bulan')) {
            $query->whereHas('documents', fn($q) => $q->where('document_type', 'ffs')->where('ffs_month', $request->ffs_bulan));
        }

        if ($request->filled('ffs_tahun')) {
            $query->whereHas('documents', fn($q) => $q->where('document_type', 'ffs')->where('ffs_year', $request->ffs_tahun));
        }

        $reksaDanas = $query->orderBy('nama_reksa_dana')->paginate(20);

        $tahunList = ReksaDanaDocument::where('document_type', 'ffs')
            ->whereNotNull('ffs_year')
            ->distinct()
            ->orderBy('ffs_year', 'desc')
            ->pluck('ffs_year');

        $bulanIndonesia = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        return view('admin.analisa.index', compact('reksaDanas', 'tahunList', 'bulanIndonesia') + ['tab' => 'ffs']);
    }

    public function show(AnalisaReksaDana $analisa)
    {
        $analisa->load(['user', 'sektor', 'efek', 'kinerja', 'obligasi', 'bank', 'alokasiAset']);

        return view('admin.analisa.show', compact('analisa'));
    }

    public function publish(AnalisaReksaDana $analisa)
    {
        $published = !$analisa->is_published;

        $analisa->update([
            'is_published' => $published,
            'published_at' => $published ? now() : null,
        ]);

        $msg = $published
            ? "Analisa {$analisa->nama_reksa_dana} telah dipublikasikan"
            : "Analisa {$analisa->nama_reksa_dana} telah ditarik dari publikasi";

        ActivityLogger::log('Publikasi Analisa Reksa Dana', $msg, 'success', $analisa);

        return back()->with('success', $msg);
    }

    public function exportPdf(AnalisaReksaDana $analisa)
    {
        $analisa->load(['user', 'sektor', 'efek', 'kinerja', 'obligasi', 'bank']);

        $pdf = Pdf::loadView('analisa.pdf', compact('analisa'))
            ->setPaper('a4', 'portrait');

        $filename = 'analisa-'.str($analisa->nama_reksa_dana)->slug().'-'.now()->format('Ymd').'.pdf';

        return $pdf->download($filename);
    }

    public function downloadPdf(AnalisaReksaDana $analisa)
    {
        if (!$analisa->pdf_path || !Storage::disk('public')->exists($analisa->pdf_path)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        return Storage::disk('public')->download($analisa->pdf_path, 'ffs-'.str($analisa->nama_reksa_dana)->slug().'.pdf');
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

        ActivityLogger::log(
            'Review Analisa Reksa Dana',
            "Analisa {$analisa->nama_reksa_dana} telah ditandai sebagai reviewed",
            'success',
            $analisa,
        );

        return back()->with('success', 'Data analisa telah ditandai sebagai reviewed.');
    }

    public function destroy(AnalisaReksaDana $analisa)
    {
        ActivityLogger::log(
            'Menghapus Analisa Reksa Dana',
            "Analisa {$analisa->nama_reksa_dana} berhasil dihapus",
            'success',
            $analisa,
        );

        if ($analisa->pdf_path && Storage::disk('public')->exists($analisa->pdf_path)) {
            Storage::disk('public')->delete($analisa->pdf_path);
        }

        $analisa->delete();

        return redirect()->route('admin.analisa.index')->with('success', 'Data analisa berhasil dihapus.');
    }
}
