<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataSourceLink;
use App\Models\DataSourceSyncLog;
use App\Models\ReksaDana;
use App\Imports\HargaReksaDanaImport;
use App\Imports\HarianReksaDanaImport;
use App\Exports\HargaReksaDanaTemplateExport;
use App\Exports\HarianReksaDanaTemplateExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DaftarReksaDanaController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'harga');

        // Tab Harga (master)
        $hargaQuery = ReksaDana::latest();
        if ($request->jenis) $hargaQuery->where('jenis', $request->jenis);
        if ($request->search) $hargaQuery->where('nama_reksa_dana', 'like', '%' . $request->search . '%');
        $reksaDanas = $hargaQuery->paginate(20, ['*'], 'harga_page')->withQueryString();

        // Tab Harian
        $harianQuery = \App\Models\HargaReksaDana::with('reksaDana')->latest('tanggal');
        if ($request->search) {
            $harianQuery->whereHas('reksaDana', fn($q) => $q->where('nama_reksa_dana', 'like', '%' . $request->search . '%'));
        }
        $harian = $harianQuery->paginate(20, ['*'], 'harian_page')->withQueryString();

        $dataSourceLinks = collect();
        $syncLogs = collect();
        $reksaDanaList = collect();
        $editingLink = null;

        if ($tab === 'link-website') {
            $linkQuery = DataSourceLink::global()->with(['reksaDana', 'urls'])->latest();
            if ($request->search) {
                $linkQuery->where(function ($q) use ($request) {
                    $q->where('nama_sumber', 'like', '%' . $request->search . '%')
                        ->orWhereHas('reksaDana', fn ($r) => $r->where('nama_reksa_dana', 'like', '%' . $request->search . '%'));
                });
            }
            if ($request->jenis_akses) {
                $linkQuery->where('jenis_akses', $request->jenis_akses);
            }
            $dataSourceLinks = $linkQuery->paginate(15, ['*'], 'link_page')->withQueryString();

            $syncLogs = DataSourceSyncLog::with(['link.reksaDana', 'user'])
                ->latest()
                ->paginate(10, ['*'], 'log_page')
                ->withQueryString();

            $reksaDanaList = ReksaDana::orderBy('nama_reksa_dana')->get(['id', 'nama_reksa_dana']);

            if ($request->edit) {
                $editingLink = DataSourceLink::with('urls')->find($request->edit);
            }
        }

        return view('admin.daftar-reksa-dana.index', compact(
            'reksaDanas', 'harian', 'tab',
            'dataSourceLinks', 'syncLogs', 'reksaDanaList', 'editingLink',
        ));
    }

    public function uploadHarga(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);
        Excel::import(new HargaReksaDanaImport(), $request->file('file'));
        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
            ->with('success', 'Data harga reksa dana berhasil diupload.');
    }

    public function uploadHarian(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);
        Excel::import(new HarianReksaDanaImport(), $request->file('file'));
        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
            ->with('success', 'Data harian reksa dana berhasil diupload.');
    }

    public function downloadTemplateHarga()
    {
        return Excel::download(new HargaReksaDanaTemplateExport(), 'template-harga-reksa-dana.xlsx');
    }

    public function downloadTemplateHarian()
    {
        return Excel::download(new HarianReksaDanaTemplateExport(), 'template-harian-reksa-dana.xlsx');
    }
}
