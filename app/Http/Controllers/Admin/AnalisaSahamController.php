<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\User\AnalisaSahamController as BaseAnalisaSahamController;

class AnalisaSahamController extends BaseAnalisaSahamController
{
    protected bool $isAdminContext = true;

    protected function indexRouteName(): string { return 'admin.analisa-saham.index'; }
    protected function routePrefix(): string { return 'admin.analisa-saham'; }

    public function show($analisa)
    {
        $model = $this->getModel();
        $analisa = $model::with('user')->findOrFail($analisa);

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
                'user_id'        => auth()->id(),
                'nama_perusahaan'=> $request->nama_perusahaan,
                'kode_saham'     => $request->kode_saham,
                'sektor'         => $request->sektor,
                'status'         => 'reviewed',
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

        $model = $this->getModel();
        $analisa = $model::create($data);

        if ($request->filled('ai_narasi') && $request->filled('ai_output')) {
            $analisa->update([
                'ai_narasi' => $request->ai_narasi,
                'ai_output' => json_decode($request->ai_output, true) ?: [],
            ]);
        }

        if ($request->filled('ai_narasi_plus') && $request->filled('ai_output_plus')) {
            $analisa->update([
                'ai_narasi_plus' => $request->ai_narasi_plus,
                'ai_output_plus' => json_decode($request->ai_output_plus, true) ?: [],
            ]);
        }

        return redirect()->route($this->indexRouteName())
            ->with('success', 'Data analisa saham berhasil disimpan.');
    }

    public function destroy($id)
    {
        $model = $this->getModel();
        $analisa = $model::findOrFail($id);

        if ($analisa->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($analisa->pdf_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($analisa->pdf_path);
        }

        $analisa->delete();

        return redirect()->route($this->indexRouteName())->with('success', 'Data analisa saham berhasil dihapus.');
    }
}
