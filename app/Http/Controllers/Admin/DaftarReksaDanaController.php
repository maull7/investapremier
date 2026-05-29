<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataSourceLink;
use App\Models\DataSourceSyncLog;
use App\Models\HargaReksaDana;
use App\Models\ReksaDana;
use App\Imports\HargaReksaDanaImport;
use App\Imports\HarianReksaDanaImport;
use App\Exports\HargaReksaDanaTemplateExport;
use App\Exports\HarianReksaDanaTemplateExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DaftarReksaDanaController extends Controller
{
    private const JENIS_OPTIONS = ['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'];
    private const KATEGORI_OPTIONS = ['Konvensional', 'Syariah', 'index', 'ETF'];
    private const KATEGORI_PRODUK_OPTIONS = ['Konvensional', 'Syariah', 'Index', 'ETF'];

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
        $reksaDanaOptions = ReksaDana::orderBy('nama_reksa_dana')->get(['id', 'kode_reksa_dana', 'nama_reksa_dana']);
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
            'reksaDanaOptions',
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

    // ======================= CRUD HARGA (ReksaDana) =======================

    public function storeHarga(Request $request)
    {
        $validated = $request->validate([
            'kode_reksa_dana'       => 'nullable|string|max:20|unique:reksa_dana,kode_reksa_dana',
            'nama_reksa_dana'       => 'required|string|max:255',
            'nama_manajer_investasi'=> 'required|string|max:255',
            'jenis'                 => 'required|string|in:' . implode(',', self::JENIS_OPTIONS),
            'kategori'              => 'nullable|array',
            'kategori.*'            => 'string|in:' . implode(',', self::KATEGORI_OPTIONS),
            'kategori_produk'       => 'nullable|string|in:' . implode(',', self::KATEGORI_PRODUK_OPTIONS),
            'benchmark'             => 'nullable|string|max:255',
            'tujuan_investasi'      => 'nullable|string',
            'kebijakan_investasi'   => 'nullable|string',
            'mata_uang'             => 'nullable|string|max:10',
            'nab_per_unit'          => 'nullable|numeric',
            'tanggal_nab'           => 'nullable|date',
        ]);

        $validated['kategori'] = $validated['kategori'] ?? [];
        $validated['mata_uang'] = $validated['mata_uang'] ?? 'IDR';

        ReksaDana::create($validated);

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
            ->with('success', 'Reksa dana berhasil ditambahkan.');
    }

    public function updateHarga(Request $request, ReksaDana $reksaDana)
    {
        $validated = $request->validate([
            'kode_reksa_dana'       => 'nullable|string|max:20|unique:reksa_dana,kode_reksa_dana,' . $reksaDana->id,
            'nama_reksa_dana'       => 'required|string|max:255',
            'nama_manajer_investasi'=> 'required|string|max:255',
            'jenis'                 => 'required|string|in:' . implode(',', self::JENIS_OPTIONS),
            'kategori'              => 'nullable|array',
            'kategori.*'            => 'string|in:' . implode(',', self::KATEGORI_OPTIONS),
            'kategori_produk'       => 'nullable|string|in:' . implode(',', self::KATEGORI_PRODUK_OPTIONS),
            'benchmark'             => 'nullable|string|max:255',
            'tujuan_investasi'      => 'nullable|string',
            'kebijakan_investasi'   => 'nullable|string',
            'mata_uang'             => 'nullable|string|max:10',
            'nab_per_unit'          => 'nullable|numeric',
            'tanggal_nab'           => 'nullable|date',
        ]);

        $validated['kategori'] = $validated['kategori'] ?? [];

        $reksaDana->update($validated);

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
            ->with('success', 'Reksa dana berhasil diperbarui.');
    }

    public function destroyHarga(ReksaDana $reksaDana)
    {
        $reksaDana->delete();

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
            ->with('success', 'Reksa dana berhasil dihapus.');
    }

    // ======================= CRUD HARIAN (HargaReksaDana) =======================

    public function storeHarian(Request $request)
    {
        $validated = $request->validate([
            'reksa_dana_id' => 'required|exists:reksa_dana,id',
            'tanggal'       => 'required|date',
            'nab_per_unit'  => 'required|numeric',
        ]);

        $exists = HargaReksaDana::where('reksa_dana_id', $validated['reksa_dana_id'])
            ->where('tanggal', $validated['tanggal'])
            ->exists();

        if ($exists) {
            return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
                ->with('error', 'Data untuk reksa dana dan tanggal tersebut sudah ada.');
        }

        HargaReksaDana::create($validated);

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
            ->with('success', 'Data harian berhasil ditambahkan.');
    }

    public function updateHarian(Request $request, HargaReksaDana $hargaReksaDana)
    {
        $validated = $request->validate([
            'reksa_dana_id' => 'required|exists:reksa_dana,id',
            'tanggal'       => 'required|date',
            'nab_per_unit'  => 'required|numeric',
        ]);

        $exists = HargaReksaDana::where('reksa_dana_id', $validated['reksa_dana_id'])
            ->where('tanggal', $validated['tanggal'])
            ->where('id', '!=', $hargaReksaDana->id)
            ->exists();

        if ($exists) {
            return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
                ->with('error', 'Data untuk reksa dana dan tanggal tersebut sudah ada.');
        }

        $hargaReksaDana->update($validated);

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
            ->with('success', 'Data harian berhasil diperbarui.');
    }

    public function destroyHarian(HargaReksaDana $hargaReksaDana)
    {
        $hargaReksaDana->delete();

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
            ->with('success', 'Data harian berhasil dihapus.');
    }
}
