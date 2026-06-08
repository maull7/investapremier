<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\User\AnalisaSahamController as BaseAnalisaSahamController;
use App\Support\ActivityLogger;

class AnalisaSahamController extends BaseAnalisaSahamController
{
    protected bool $isAdminContext = true;

    protected function indexRouteName(): string { return 'admin.analisa-saham.index'; }
    protected function routePrefix(): string { return 'admin.analisa-saham'; }

    public function show($analisa)
    {
        $model = $this->getModel();
        $analisa = $model::with(['user', 'brokerResearchDocuments.uploader'])->findOrFail($analisa);

        return view($this->showView(), [
            'analisa'      => $analisa,
            'productLabel' => $this->productLabel,
            'indexRoute'   => $this->indexRouteName(),
            'pdfRoute'     => $this->routePrefix() . '.pdf',
            'downloadRoute'=> $this->routePrefix() . '.download-lapkeu',
            'reviewRoute'  => $this->routePrefix() . '.review',
            'destroyRoute' => $this->routePrefix() . '.destroy',
            'checkAiStatusRoute' => $this->routePrefix() . '.check-ai-status',
        ]);
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate(array_merge($this->validateBasicFields($request), [
            'input_mode' => 'required|in:manual,excel',
            'pdf_lapkeu' => 'nullable|file|mimes:pdf|max:20480',
            'broker_research' => 'nullable|array',
            'broker_research.*.broker' => 'nullable|required_with:broker_research.*.document|string|max:100',
            'broker_research.*.document' => 'nullable|file|mimes:pdf,docx|max:5120',
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
        $this->persistInitialBrokerResearchDocument($request, $analisa);

        $this->persistLapkeuAiFromRequest($request, $analisa);

        ActivityLogger::log(
            'Membuat Analisa Saham',
            "Analisa saham {$data['nama_perusahaan']} berhasil dibuat",
            'success',
            $analisa,
        );

        return redirect()->route($this->routePrefix() . '.show', $analisa->id)
            ->with('success', 'Data analisa saham berhasil disimpan. Analisa AI sedang diproses.');
    }

    public function destroy($id)
    {
        $model = $this->getModel();
        $analisa = $model::findOrFail($id);

        ActivityLogger::log(
            'Menghapus Analisa Saham',
            "Analisa saham {$analisa->nama_perusahaan} berhasil dihapus",
            'success',
            $analisa,
        );

        if ($analisa->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($analisa->pdf_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($analisa->pdf_path);
        }

        $analisa->delete();

        return redirect()->route($this->indexRouteName())->with('success', 'Data analisa saham berhasil dihapus.');
    }
}
