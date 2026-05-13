<?php

namespace App\Http\Controllers;

use App\Exports\AnalisaTemplateExport;
use App\Imports\AnalisaImport;
use App\Jobs\AnalisaAiJob;
use App\Models\AnalisaReksaDana;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AnalisaController extends Controller
{
    public function index()
    {
        $analisas = AnalisaReksaDana::where('user_id', auth()->id())
            ->latest()->get();

        return view('analisa.index', compact('analisas'));
    }

    public function create()
    {
        return view('analisa.create');
    }

    public function downloadTemplate()
    {
        return Excel::download(new AnalisaTemplateExport(), 'template-analisa-reksa-dana.xlsx');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_reksa_dana'      => 'required|string|max:255',
            'jenis_reksa_dana'     => 'required|in:Saham,Pendapatan Tetap,Campuran,Pasar Uang',
            'total_aum'            => 'nullable|numeric|min:0',
            'total_marcap_10_efek' => 'nullable|numeric|min:0',
            'input_mode'           => 'required|in:manual,excel',
        ]);

        if ($request->input_mode === 'excel') {
            return $this->storeFromExcel($request);
        }

        return $this->storeFromManual($request);
    }

    private function storeFromManual(Request $request)
    {
        $request->validate([
            'sektor'                    => 'nullable|array',
            'sektor.*.nama_sektor'      => 'required|string',
            'sektor.*.bobot'            => 'required|numeric',
            'efek'                      => 'nullable|array',
            'efek.*.kode_efek'          => 'required|string',
            'efek.*.nama_efek'          => 'required|string',
            'efek.*.bobot'              => 'required|numeric',
            'efek.*.kontribusi_kinerja' => 'nullable|numeric',
            'efek.*.market_cap'         => 'nullable|numeric',
            'kinerja'                   => 'nullable|array|min:2',
            'kinerja.*.periode'         => 'required|date',
            'kinerja.*.return_pct'      => 'required|numeric',
            'obligasi'                  => 'nullable|array',
            'obligasi.*.kode_obligasi'  => 'required|string',
            'obligasi.*.nama_obligasi'  => 'required|string',
            'obligasi.*.bobot'          => 'required|numeric',
            'obligasi.*.durasi'         => 'nullable|numeric',
            'obligasi.*.rating'         => 'nullable|string',
            'bank'                      => 'nullable|array',
            'bank.*.nama_bank'          => 'required|string',
            'bank.*.bobot'              => 'required|numeric',
            'bank.*.car'                => 'nullable|numeric',
            'bank.*.npl'                => 'nullable|numeric',
            'bank.*.klasifikasi_risiko' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $analisa = AnalisaReksaDana::create([
                'user_id'              => auth()->id(),
                'nama_reksa_dana'      => $request->nama_reksa_dana,
                'jenis_reksa_dana'     => $request->jenis_reksa_dana,
                'total_aum'            => $request->total_aum,
                'total_marcap_10_efek' => $request->total_marcap_10_efek,
                'status'               => 'submitted',
            ]);

            if ($request->sektor)   $analisa->sektor()->createMany($request->sektor);
            if ($request->efek)     $analisa->efek()->createMany($request->efek);
            if ($request->kinerja)  $analisa->kinerja()->createMany($request->kinerja);
            if ($request->obligasi) $analisa->obligasi()->createMany($request->obligasi);
            if ($request->bank)     $analisa->bank()->createMany($request->bank);

            AnalisaAiJob::dispatch($analisa->id);
        });

        return redirect()->route('user.analisa.index')->with('success', 'Data analisa berhasil disubmit. Narasi AI sedang diproses.');
    }

    private function storeFromExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        DB::transaction(function () use ($request) {
            $analisa = AnalisaReksaDana::create([
                'user_id'              => auth()->id(),
                'nama_reksa_dana'      => $request->nama_reksa_dana,
                'jenis_reksa_dana'     => $request->jenis_reksa_dana,
                'total_aum'            => $request->total_aum,
                'total_marcap_10_efek' => $request->total_marcap_10_efek,
                'status'               => 'submitted',
            ]);

            Excel::import(new AnalisaImport($analisa), $request->file('file_excel'));

            AnalisaAiJob::dispatch($analisa->id);
        });

        return redirect()->route('user.analisa.index')->with('success', 'Data analisa berhasil diimport dari Excel. Narasi AI sedang diproses.');
    }

    public function show(AnalisaReksaDana $analisa)
    {
        abort_if($analisa->user_id !== auth()->id(), 403);
        $analisa->load(['sektor', 'efek', 'kinerja', 'obligasi', 'bank']);

        return view('analisa.show', compact('analisa'));
    }

    public function exportPdf(AnalisaReksaDana $analisa)
    {
        abort_if($analisa->user_id !== auth()->id(), 403);
        $analisa->load(['user', 'sektor', 'efek', 'kinerja', 'obligasi', 'bank']);

        $pdf = Pdf::loadView('analisa.pdf', compact('analisa'))
            ->setPaper('a4', 'portrait');

        $filename = 'analisa-'.str($analisa->nama_reksa_dana)->slug().'-'.now()->format('Ymd').'.pdf';

        return $pdf->download($filename);
    }

    public function destroy(AnalisaReksaDana $analisa)
    {
        abort_if($analisa->user_id !== auth()->id(), 403);
        abort_if($analisa->status === 'reviewed', 403, 'Data yang sudah direview tidak dapat dihapus.');

        $analisa->delete();

        return redirect()->route('user.analisa.index')->with('success', 'Data analisa dihapus.');
    }
}
