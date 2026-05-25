<?php

namespace App\Http\Controllers;

use App\Models\AnalisaSaham;
use App\Models\AnalisaSahamBrokerResearchDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnalisaSahamBrokerResearchController extends Controller
{
    public function store(Request $request, AnalisaSaham $analisa)
    {
        $this->authorizeAccess($request, $analisa);

        $validated = $request->validate([
            'broker' => 'required|string|max:100',
            'documents' => 'required|array|min:1',
            'documents.*' => 'required|file|mimes:pdf,docx|max:5120',
        ]);

        foreach ($request->file('documents', []) as $file) {
            $filename = now()->format('Ymd-His') . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('riset-broker/' . $analisa->id, $filename, 'public');

            $analisa->brokerResearchDocuments()->create([
                'uploaded_by' => $request->user()->id,
                'broker' => $validated['broker'],
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return back()
            ->with('success', 'Dokumen riset broker berhasil diupload.')
            ->with('active_tab', 'riset-broker');
    }

    public function view(Request $request, AnalisaSaham $analisa, AnalisaSahamBrokerResearchDocument $document)
    {
        $this->authorizeAccess($request, $analisa);
        $this->ensureDocumentBelongsToAnalisa($analisa, $document);
        $this->ensureFileExists($document);

        $headers = [
            'Content-Type' => $document->mime_type ?: Storage::disk('public')->mimeType($document->file_path),
            'Content-Disposition' => 'inline; filename="' . addslashes($document->original_name) . '"',
        ];

        return response()->file(Storage::disk('public')->path($document->file_path), $headers);
    }

    public function download(Request $request, AnalisaSaham $analisa, AnalisaSahamBrokerResearchDocument $document)
    {
        $this->authorizeAccess($request, $analisa);
        $this->ensureDocumentBelongsToAnalisa($analisa, $document);
        $this->ensureFileExists($document);

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    public function destroy(Request $request, AnalisaSaham $analisa, AnalisaSahamBrokerResearchDocument $document)
    {
        $this->authorizeAccess($request, $analisa);
        $this->ensureDocumentBelongsToAnalisa($analisa, $document);

        $document->deleteStoredFile();
        $document->delete();

        return back()
            ->with('success', 'Dokumen riset broker berhasil dihapus.')
            ->with('active_tab', 'riset-broker');
    }

    private function authorizeAccess(Request $request, AnalisaSaham $analisa): void
    {
        if ($request->routeIs('admin.*')) {
            return;
        }

        abort_if($analisa->user_id !== $request->user()->id, 403);
    }

    private function ensureDocumentBelongsToAnalisa(AnalisaSaham $analisa, AnalisaSahamBrokerResearchDocument $document): void
    {
        abort_if($document->analisa_saham_id !== $analisa->id, 404);
    }

    private function ensureFileExists(AnalisaSahamBrokerResearchDocument $document): void
    {
        abort_if(!$document->file_path || !Storage::disk('public')->exists($document->file_path), 404, 'Dokumen riset broker tidak ditemukan.');
    }
}
