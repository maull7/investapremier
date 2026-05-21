<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\AnalisaAiJob;
use App\Models\AnalisaReksaDana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReksaDanaController extends Controller
{
    public function index(Request $request)
    {
        $query = AnalisaReksaDana::with('user')->latest();

        if ($request->jenis) {
            $query->where('jenis_reksa_dana', $request->jenis);
        }

        if ($request->kategori) {
            $query->whereJsonContains('kategori', $request->kategori);
        }

        $reksaDanas = $query->paginate(20)->withQueryString();

        return view('admin.reksa-dana.index', compact('reksaDanas'));
    }

    public function bulkAnalisa(Request $request)
    {
        $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer|exists:analisa_reksa_dana,id']);

        $count = 0;
        foreach ($request->ids as $id) {
            $analisa = AnalisaReksaDana::find($id);
            if ($analisa && $analisa->pdf_path) {
                AnalisaAiJob::dispatch($analisa->id);
                $count++;
            }
        }

        return back()->with('success', "{$count} reksa dana sedang diproses analisa FFS.");
    }

    public function downloadPdf(AnalisaReksaDana $reksaDana)
    {
        if (!$reksaDana->pdf_path || !Storage::disk('public')->exists($reksaDana->pdf_path)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        return Storage::disk('public')->download(
            $reksaDana->pdf_path,
            'ffs-' . str($reksaDana->nama_reksa_dana)->slug() . '.pdf'
        );
    }
}
