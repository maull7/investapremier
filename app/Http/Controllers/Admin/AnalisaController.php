<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalisaReksaDana;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AnalisaController extends Controller
{
    public function index(Request $request)
    {
        $query = AnalisaReksaDana::with('user')->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $analisas = $query->paginate(20);

        return view('admin.analisa.index', compact('analisas'));
    }

    public function show(AnalisaReksaDana $analisa)
    {
        $analisa->load(['user', 'sektor', 'efek', 'kinerja', 'obligasi', 'bank']);

        return view('admin.analisa.show', compact('analisa'));
    }

    public function exportPdf(AnalisaReksaDana $analisa)
    {
        $analisa->load(['user', 'sektor', 'efek', 'kinerja', 'obligasi', 'bank']);

        $pdf = Pdf::loadView('analisa.pdf', compact('analisa'))
            ->setPaper('a4', 'portrait');

        $filename = 'analisa-'.str($analisa->nama_reksa_dana)->slug().'-'.now()->format('Ymd').'.pdf';

        return $pdf->download($filename);
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
}
