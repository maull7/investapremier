<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\AnalisaAiJob;
use App\Models\AnalisaReksaDana;
use Illuminate\Http\Request;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Storage;

class ReksaDanaController extends Controller
{
    private const JENIS_OPTIONS = ['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'];
    private const KATEGORI_OPTIONS = ['Konvensional', 'Syariah', 'index', 'ETF'];

    public function index(Request $request)
    {
        $query = AnalisaReksaDana::with('user')
            ->where('product_type', 'reksa_dana')
            ->latest();

        if ($request->filled('jenis')) {
            $query->whereIn('jenis_reksa_dana', (array) $request->jenis);
        }

        if ($request->filled('kategori')) {
            $kategoriFilter = (array) $request->kategori;
            $query->where(function ($q) use ($kategoriFilter) {
                foreach ($kategoriFilter as $k) {
                    $q->whereJsonContains('kategori', $k);
                }
            });
        }

        $reksaDanas = $query->paginate(20)->withQueryString();

        return view('admin.reksa-dana.index', [
            'reksaDanas'      => $reksaDanas,
            'jenisOptions'    => self::JENIS_OPTIONS,
            'kategoriOptions' => self::KATEGORI_OPTIONS,
        ]);
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
            'Bulk Analisa Reksa Dana',
            "{$count} reksa dana sedang diproses analisa FFS",
            'success',
        );

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
