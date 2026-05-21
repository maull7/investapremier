<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\User\AnalisaObligasiController as BaseAnalisaObligasiController;

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
            'pdf_lapkeu' => 'nullable|file|mimes:pdf|max:10240',
        ]));

        $data = array_merge(
            $this->extractLapkeuData($request),
            [
                'user_id'      => auth()->id(),
                'nama_obligasi'=> $request->nama_obligasi,
                'kode_obligasi'=> $request->kode_obligasi,
                'nama_emiten'  => $request->nama_emiten,
                'rating'       => $request->rating,
                'kupon'        => $request->kupon,
                'ytm'          => $request->ytm,
                'status'       => 'reviewed',
            ]
        );

        if ($request->hasFile('pdf_lapkeu')) {
            $file = $request->file('pdf_lapkeu');
            $filename = 'lapkeu-' . now()->format('Ymd-His') . '-' . \Illuminate\Support\Str::random(8) . '.pdf';
            $data['pdf_path'] = $file->storeAs('lapkeu-pdfs', $filename, 'public');
        }

        $analisa = \App\Models\AnalisaObligasiKeuangan::create($data);

        if ($request->filled('ai_narasi') && $request->filled('ai_output')) {
            $analisa->update([
                'ai_narasi' => $request->ai_narasi,
                'ai_output' => json_decode($request->ai_output, true) ?: [],
            ]);
        }

        return redirect()->route($this->indexRouteName())
            ->with('success', 'Data analisa obligasi berhasil disimpan.');
    }

    public function destroy($id)
    {
        $analisa = \App\Models\AnalisaObligasiKeuangan::findOrFail($id);

        if ($analisa->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($analisa->pdf_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($analisa->pdf_path);
        }

        $analisa->delete();

        return redirect()->route($this->indexRouteName())->with('success', 'Data analisa obligasi berhasil dihapus.');
    }
}
