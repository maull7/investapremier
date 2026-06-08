<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\AnalisaAiJob;
use App\Models\AnalisaReksaDana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\ActivityLogger;

class UnitLinkFfsController extends Controller
{
    public function index(Request $request)
    {
        $query = AnalisaReksaDana::with('user')
            ->where('product_type', 'unit_link')
            ->latest();

        if ($request->jenis) {
            $query->where('jenis_reksa_dana', $request->jenis);
        }

        $analisas = $query->paginate(20)->withQueryString();

        return view('admin.unit-link-ffs.index', compact('analisas'));
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

        ActivityLogger::log(
            'Bulk Analisa Unit Link',
            "{$count} unit link sedang diproses analisa FFS",
            'success',
        );

        return back()->with('success', "{$count} unit link sedang diproses analisa FFS.");
    }

    public function downloadPdf(AnalisaReksaDana $analisa)
    {
        if (!$analisa->pdf_path || !Storage::disk('public')->exists($analisa->pdf_path)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        return Storage::disk('public')->download(
            $analisa->pdf_path,
            'ffs-' . str($analisa->nama_reksa_dana)->slug() . '.pdf'
        );
    }
}
