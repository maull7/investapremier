<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataSourceLink;
use App\Models\DataSourceSyncLog;
use App\Models\HargaReksaDana;
use App\Models\ReksaDana;
use App\Models\ReksaDanaDocument;
use App\Imports\HargaReksaDanaImport;
use App\Imports\HarianReksaDanaImport;
use App\Exports\HargaReksaDanaTemplateExport;
use App\Exports\HarianReksaDanaTemplateExport;
use App\Services\KodeReksaDanaParser;
use App\Services\ReksaDanaChartDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DaftarReksaDanaController extends Controller
{
    private const JENIS_OPTIONS = ['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'];
    private const KATEGORI_OPTIONS = ['Konvensional', 'Syariah', 'index', 'ETF'];
    private const KATEGORI_PRODUK_OPTIONS = ['Konvensional', 'Syariah', 'Index', 'ETF'];

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'harga');

        $hargaQuery = ReksaDana::latest();
        if ($request->jenis) $hargaQuery->where('jenis', $request->jenis);
        if ($request->search) $hargaQuery->where('nama_reksa_dana', 'like', '%' . $request->search . '%');
        if ($request->harga_tanggal) $hargaQuery->whereDate('tanggal_nab', $request->harga_tanggal);
        $reksaDanas = $hargaQuery->paginate(20, ['*'], 'harga_page')->withQueryString();

        $harianTanggal = $request->get('harian_tanggal');

        if ($harianTanggal) {
            // Filter spesifik tanggal: tampilkan semua data pada tanggal tersebut
            $harianQuery = HargaReksaDana::with('reksaDana')
                ->where('tanggal', $harianTanggal)
                ->orderBy('reksa_dana_id');
        } else {
            // Default: tampilkan data tanggal terakhir per reksa dana
            $latestPerRd = HargaReksaDana::selectRaw('reksa_dana_id, MAX(tanggal) as max_tanggal')
                ->groupBy('reksa_dana_id');

            $harianQuery = HargaReksaDana::with('reksaDana')
                ->joinSub($latestPerRd, 'latest', function ($join) {
                    $join->on('harga_reksa_dana.reksa_dana_id', '=', 'latest.reksa_dana_id')
                        ->whereColumn('harga_reksa_dana.tanggal', 'latest.max_tanggal');
                })
                ->select('harga_reksa_dana.*')
                ->orderByDesc('harga_reksa_dana.tanggal')
                ->orderBy('harga_reksa_dana.reksa_dana_id');
        }

        if ($request->search) {
            $harianQuery->whereHas('reksaDana', fn($q) => $q->where('nama_reksa_dana', 'like', '%' . $request->search . '%'));
        }
        $harian = $harianQuery->paginate(20, ['*'], 'harian_page')->withQueryString();

        $dataSourceLinks = collect();
        $syncLogs = collect();
        $reksaDanaList = collect();
        $reksaDanaOptions = ReksaDana::orderBy('nama_reksa_dana')->get(['id', 'kode_reksa_dana', 'nama_reksa_dana', 'nama_manajer_investasi', 'jenis']);

        $editingLink = null;
        $documents = collect();
        $documentFunds = collect();

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

        if ($tab === 'prospektus-ffs') {
            $documentFundQuery = ReksaDana::with(['documents.uploader'])->latest();
            if ($request->search) {
                $documentFundQuery->where(function ($query) use ($request) {
                    $query
                    ->where('nama_reksa_dana', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_reksa_dana', 'like', '%' . $request->search . '%');
                });
            }

            $documentFunds = $documentFundQuery->paginate(20, ['*'], 'document_page')->withQueryString();

        }

        $hargaTanggal = $request->get('harga_tanggal');

        return view('admin.daftar-reksa-dana.index', compact(
            'reksaDanas', 'harian', 'tab',
            'dataSourceLinks', 'syncLogs', 'reksaDanaList', 'editingLink',
            'reksaDanaOptions',
            'documents', 'documentFunds',
            'harianTanggal', 'hargaTanggal',
        ));
    }

    public function storeDocument(Request $request)
    {
        $validated = $request->validate([
            'reksa_dana_id' => 'required|exists:reksa_dana,id',
            'document_type' => 'required|in:prospektus,ffs',
            'prospektus_year' => 'required_if:document_type,prospektus|nullable|integer|min:2000|max:2100',
            'ffs_month' => 'required_if:document_type,ffs|nullable|integer|min:1|max:12',
            'ffs_year' => 'required_if:document_type,ffs|nullable|integer|min:2000|max:2100',
            'file' => 'required|file|mimes:pdf|max:20480',
            'notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $filename = now()->format('Ymd-His') . '-' . Str::random(10) . '.pdf';
        $path = $file->storeAs('reksa-dana-documents/' . $validated['reksa_dana_id'], $filename, 'public');

        // Untuk prospektus, simpan tahun ke ffs_year
        if ($validated['document_type'] === 'prospektus' && !empty($validated['prospektus_year'])) {
            $validated['ffs_year'] = $validated['prospektus_year'];
        }
        unset($validated['prospektus_year']);

        ReksaDanaDocument::create([
            ...$validated,
            'uploaded_by' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs'])
            ->with('success', 'Dokumen berhasil diupload.');
    }

    public function viewDocument(ReksaDanaDocument $document)
    {
        $this->ensureDocumentExists($document);

        return response()->file(Storage::disk('public')->path($document->file_path), [
            'Content-Type' => $document->mime_type ?: 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . addslashes($document->original_name) . '"',
        ]);
    }

    public function downloadDocument(ReksaDanaDocument $document)
    {
        $this->ensureDocumentExists($document);

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    public function destroyDocument(ReksaDanaDocument $document)
    {
        $document->deleteStoredFile();
        $document->delete();

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs'])
            ->with('success', 'Dokumen berhasil dihapus.');
    }

    private function ensureDocumentExists(ReksaDanaDocument $document): void
    {
        abort_if(!$document->file_path || !Storage::disk('public')->exists($document->file_path), 404, 'Dokumen tidak ditemukan.');
    }

    public function show($id, ReksaDanaChartDataService $chartDataService)
    {
        $fund = ReksaDana::with([
            'harga' => fn($q) => $q->orderBy('tanggal'),
            'assetAllocations' => fn($q) => $q->orderBy('period_date'),
            'portfolioCompositions' => fn($q) => $q->orderBy('period_date'),
            'managementTeams',
        ])->findOrFail($id);

        $range = request('range', '1y');
        $chartData = $chartDataService->forFund(
            $fund,
            $range,
            request('from_date'),
            request('to_date')
        );

        $navHistoryQuery = $fund->harga()->orderBy('tanggal');
        if ($chartData['from']) {
            $navHistoryQuery->whereDate('tanggal', '>=', $chartData['from']);
        }
        if ($chartData['to']) {
            $navHistoryQuery->whereDate('tanggal', '<=', $chartData['to']);
        }

        $navHistory = $navHistoryQuery->get();
        $navLabels = $navHistory->pluck('tanggal')->map(fn($d) => $d->format('d M Y'));
        $navValues = $navHistory->pluck('nab_per_unit');
        $aumValues = $navHistory->pluck('aum');
        $upValues = $navHistory->pluck('unit_participation');

        $aaTimeline = $fund->assetAllocations()->orderBy('period_date')->get();
        $aaLabels = $aaTimeline->pluck('period_date')->map(fn($d) => $d->format('M Y'));

        $latestPeriodDate = $fund->portfolioCompositions()->max('period_date');
        $topHoldings = collect();
        if ($latestPeriodDate) {
            $topHoldings = $fund->portfolioCompositions()
                ->where('period_date', $latestPeriodDate)
                ->orderByDesc('weight_percent')
                ->get();
        }

        $portfolioTimeline = $fund->portfolioCompositions()
            ->selectRaw('reksa_dana_id, period_date, security_name, security_type, weight_percent')
            ->orderBy('period_date')
            ->get()
            ->groupBy('period_date');

        $latestNav = $navHistory->last();
        $firstNav = $navHistory->first();
        $returnDaily = null;
        $returnMonthly = null;
        $returnYearly = null;

        if ($latestNav && $firstNav && $firstNav->nab_per_unit > 0) {
            $returnYearly = (($latestNav->nab_per_unit - $firstNav->nab_per_unit) / $firstNav->nab_per_unit) * 100;
        }

        $prevDayNav = null;
        if ($latestNav) {
            $prevDayNav = $fund->harga()->where('tanggal', '<', $latestNav->tanggal)->orderByDesc('tanggal')->first();
        }
        if ($latestNav && $prevDayNav && $prevDayNav->nab_per_unit > 0) {
            $returnDaily = (($latestNav->nab_per_unit - $prevDayNav->nab_per_unit) / $prevDayNav->nab_per_unit) * 100;
        }

        $prevMonthNav = $fund->harga()->where('tanggal', '<=', now()->subMonth())->orderByDesc('tanggal')->first();
        if ($latestNav && $prevMonthNav && $prevMonthNav->nab_per_unit > 0) {
            $returnMonthly = (($latestNav->nab_per_unit - $prevMonthNav->nab_per_unit) / $prevMonthNav->nab_per_unit) * 100;
        }

        return view('admin.daftar-reksa-dana.show', compact(
            'fund', 'navHistory', 'navLabels', 'navValues', 'aumValues', 'upValues',
            'aaTimeline', 'aaLabels', 'topHoldings', 'portfolioTimeline',
            'latestNav', 'returnDaily', 'returnMonthly', 'returnYearly', 'range',
            'chartData',
        ));
    }

    public function uploadHarga(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        $import = new HargaReksaDanaImport();
        Excel::import($import, $request->file('file'));

        if ($import->imported === 0) {
            $msg = 'Tidak ada data yang berhasil diimport. ';
            if ($import->skipped > 0) {
                $msg .= $import->skipped . ' baris dilewati (nama_reksa_dana tidak boleh kosong).';
            } else {
                $msg .= 'Periksa kembali format file excel anda.';
            }
            return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
                ->with('error', $msg);
        }

        $msg = $import->imported . ' data berhasil diupload.';
        if ($import->skipped > 0) {
            $msg .= ' (' . $import->skipped . ' baris dilewati)';
        }
        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
            ->with('success', $msg);
    }

    public function uploadHarian(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        $import = new HarianReksaDanaImport();
        Excel::import($import, $request->file('file'));

        if ($import->imported === 0) {
            $msg = 'Tidak ada data yang berhasil diimport. ';
            if ($import->skipped > 0) {
                $msg .= $import->skipped . ' baris dilewati. Pastikan nama_reksa_dana, tanggal, dan nab_per_unit terisi dengan benar.';
            } else {
                $msg .= 'Periksa kembali format file excel anda.';
            }
            return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
                ->with('error', $msg);
        }

        $msg = $import->imported . ' data berhasil diupload.';
        if ($import->skipped > 0) {
            $msg .= ' (' . $import->skipped . ' baris dilewati)';
        }
        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
            ->with('success', $msg);
    }

    public function downloadTemplateHarga()
    {
        return Excel::download(new HargaReksaDanaTemplateExport(), 'template-harga-reksa-dana.xlsx');
    }

    public function downloadTemplateHarian()
    {
        return Excel::download(new HarianReksaDanaTemplateExport(), 'template-harian-reksa-dana.xlsx');
    }

    public function storeHarga(Request $request)
    {
        if ($request->has('kategori') && is_string($request->input('kategori'))) {
            $decoded = json_decode($request->input('kategori'), true);
            if (is_array($decoded)) {
                $request->merge(['kategori' => $decoded]);
            } elseif ($request->input('kategori') === '') {
                $request->merge(['kategori' => []]);
            }
        }

        $validated = $request->validate([
            'kode_reksa_dana'       => 'nullable|string|max:20|unique:reksa_dana,kode_reksa_dana',
            'nama_reksa_dana'       => 'required|string|max:255',
            'nama_manajer_investasi'=> 'nullable|string|max:255',
            'jenis'                 => 'nullable|string|in:' . implode(',', self::JENIS_OPTIONS),
            'kategori'              => 'nullable|array',
            'kategori.*'            => 'string|in:' . implode(',', self::KATEGORI_OPTIONS),
            'kategori_produk'       => 'nullable|string|in:' . implode(',', self::KATEGORI_PRODUK_OPTIONS),
            'kelas'                 => 'nullable|string|max:10',
            'benchmark'             => 'nullable|string|max:255',
            'tujuan_investasi'      => 'nullable|string',
            'kebijakan_investasi'   => 'nullable|string',
            'mata_uang'             => 'nullable|string|max:10',
            'nab_per_unit'          => 'nullable|numeric',
            'tanggal_nab'           => 'nullable|date',
        ]);

        if (!empty($validated['kode_reksa_dana'])) {
            $validated = array_merge($validated, app(KodeReksaDanaParser::class)->databaseAttributes($validated['kode_reksa_dana']));
        }

        $validated['kategori'] = $validated['kategori'] ?? [];
        $validated['mata_uang'] = $validated['mata_uang'] ?? 'IDR';

        $reksaDana = ReksaDana::create($validated);

        if (!empty($validated['nab_per_unit']) && !empty($validated['tanggal_nab'])) {
            HargaReksaDana::updateOrCreate(
                ['reksa_dana_id' => $reksaDana->id, 'tanggal' => $validated['tanggal_nab']],
                ['nab_per_unit' => $validated['nab_per_unit']]
            );
        }

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
            ->with('success', 'Reksa dana berhasil ditambahkan.');
    }

    public function updateHarga(Request $request, ReksaDana $reksaDana)
    {
        // Decode kategori jika dikirim sebagai JSON string dari JS
        if ($request->has('kategori') && is_string($request->input('kategori'))) {
            $decoded = json_decode($request->input('kategori'), true);
            if (is_array($decoded)) {
                $request->merge(['kategori' => $decoded]);
            } elseif ($request->input('kategori') === '') {
                $request->merge(['kategori' => []]);
            }
        }

        $validated = $request->validate([
            'kode_reksa_dana'       => 'nullable|string|max:20|unique:reksa_dana,kode_reksa_dana,' . $reksaDana->id,
            'nama_reksa_dana'       => 'required|string|max:255',
            'nama_manajer_investasi'=> 'nullable|string|max:255',
            'jenis'                 => 'nullable|string|in:' . implode(',', self::JENIS_OPTIONS),
            'kategori'              => 'nullable|array',
            'kategori.*'            => 'string',
            'kategori_produk'       => 'nullable|string|in:' . implode(',', self::KATEGORI_PRODUK_OPTIONS),
            'kelas'                 => 'nullable|string|max:10',
            'benchmark'             => 'nullable|string|max:255',
            'tujuan_investasi'      => 'nullable|string',
            'kebijakan_investasi'   => 'nullable|string',
            'mata_uang'             => 'nullable|string|max:10',
            'nab_per_unit'          => 'nullable|numeric',
            'tanggal_nab'           => 'nullable|date',
        ]);

        if (!empty($validated['kode_reksa_dana'])) {
            $validated = array_merge($validated, app(KodeReksaDanaParser::class)->databaseAttributes($validated['kode_reksa_dana']));
        }

        $validated['kategori'] = $validated['kategori'] ?? [];

        $reksaDana->update($validated);

        if (!empty($validated['nab_per_unit']) && !empty($validated['tanggal_nab'])) {
            HargaReksaDana::updateOrCreate(
                ['reksa_dana_id' => $reksaDana->id, 'tanggal' => $validated['tanggal_nab']],
                ['nab_per_unit' => $validated['nab_per_unit']]
            );
        }

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
            ->with('success', 'Reksa dana berhasil diperbarui.');
    }

    public function destroyHarga(ReksaDana $reksaDana)
    {
        $reksaDana->delete();

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harga'])
            ->with('success', 'Reksa dana berhasil dihapus.');
    }

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

        ReksaDana::where('id', $validated['reksa_dana_id'])->update([
            'nab_per_unit' => $validated['nab_per_unit'],
            'tanggal_nab'  => $validated['tanggal'],
        ]);

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

        ReksaDana::where('id', $validated['reksa_dana_id'])->update([
            'nab_per_unit' => $validated['nab_per_unit'],
            'tanggal_nab'  => $validated['tanggal'],
        ]);

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
            ->with('success', 'Data harian berhasil diperbarui.');
    }

    public function destroyHarian(HargaReksaDana $hargaReksaDana)
    {
        $hargaReksaDana->delete();

        return redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'harian'])
            ->with('success', 'Data harian berhasil dihapus.');
    }

    public function parseKode(Request $request)
    {
        $kode = $request->get('kode');
        if (!$kode) {
            return response()->json(['error' => 'Kode tidak boleh kosong'], 422);
        }

        $parsed = app(KodeReksaDanaParser::class)->parse($kode);

        if (!$parsed) {
            return response()->json(['error' => 'Kode Reksa Dana tidak valid / Manajer Investasi tidak ditemukan'], 422);
        }

        return response()->json($parsed);
    }
}
