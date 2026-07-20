<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\User\AnalisaObligasiController as BaseAnalisaObligasiController;
use App\Support\ActivityLogger;

class AnalisaObligasiController extends BaseAnalisaObligasiController
{
    protected bool $isAdminContext = true;

    protected function indexRouteName(): string { return 'admin.analisa-obligasi.index'; }
    protected function routePrefix(): string { return 'admin.analisa-obligasi'; }

    public function show($id)
    {
        $analisa = \App\Models\AnalisaObligasiKeuangan::with('user')->findOrFail($id);

        return view($this->showView(), [
            'analisa'      => $analisa,
            'productLabel' => $this->productLabel,
            'indexRoute'   => $this->indexRouteName(),
            'pdfRoute'     => $this->routePrefix() . '.pdf',
            'downloadRoute'=> $this->routePrefix() . '.download-lapkeu',
            'reviewRoute'  => $this->routePrefix() . '.review',
            'destroyRoute' => $this->routePrefix() . '.destroy',
        ]);
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate(array_merge($this->validateBasicFields($request), [
            'input_mode' => 'required|in:manual,excel',
            'pdf_lapkeu' => 'nullable|file|mimes:pdf|max:20480',
        ]));

        $data = array_merge(
            $this->prepareObligasiAnalysisData($request),
            [
                'user_id'         => auth()->id(),
                'nama_obligasi'   => $request->nama_obligasi,
                'kode_obligasi'   => $request->kode_obligasi,
                'nama_emiten'     => $request->nama_emiten,
                'rating'          => $request->rating,
                'official_rating' => $request->official_rating,
                'mata_uang'       => $request->mata_uang,
                'kupon'           => $request->kupon,
                'ytm'             => $request->ytm,
                'sektor'          => $request->sektor,
                'info_nama_obligasi' => $request->info_nama_obligasi,
                'info_ytm'        => $request->info_ytm,
                'harga_obligasi'  => $request->harga_obligasi,
                'q1_obligasi'     => $request->q1_obligasi,
                'q2_obligasi'     => $request->q2_obligasi,
                'q3_obligasi'     => $request->q3_obligasi,
                'q4_obligasi'     => $request->q4_obligasi,
                'info_nominal_penerbitan' => $request->info_nominal_penerbitan,
                'nominal_penerbit' => $request->nominal_penerbit,
                'tanggal_terbit'  => $request->tanggal_terbit,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'tanpa_jaminan'   => $request->boolean('tanpa_jaminan'),
                'dengan_jaminan'  => $request->boolean('dengan_jaminan'),
                'periode_dari'    => $request->periode_dari,
                'periode_sampai'  => $request->periode_sampai,
                'tenor_bulan'     => $request->tenor_bulan,
                'jenis_analisa'   => $request->input('jenis_analisa', \App\Enums\AnalisaType::ANALISA_PERIODE->value),
                'status'          => 'reviewed',
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

        $analisa = \App\Models\AnalisaObligasiKeuangan::create($data);

        $this->persistLapkeuAiFromRequest($request, $analisa);

        $this->calculateShadowRating($analisa);
        $this->calculateYtmSpread($analisa);

        ActivityLogger::log(
            'Membuat Analisa Obligasi',
            "Analisa obligasi {$analisa->nama_obligasi} berhasil dibuat",
            'success',
            $analisa,
        );

        return redirect()->route($this->routePrefix() . '.show', $analisa->id)
            ->with('success', 'Data analisa obligasi berhasil disimpan. Analisa AI sedang diproses.');
    }

    public function destroy($id)
    {
        $analisa = \App\Models\AnalisaObligasiKeuangan::findOrFail($id);

        ActivityLogger::log(
            'Menghapus Analisa Obligasi',
            "Analisa obligasi {$analisa->nama_obligasi} berhasil dihapus",
            'success',
            $analisa,
        );

        if ($analisa->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($analisa->pdf_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($analisa->pdf_path);
        }

        $analisa->delete();

        return redirect()->route($this->indexRouteName())->with('success', 'Data analisa obligasi berhasil dihapus.');
    }
}
