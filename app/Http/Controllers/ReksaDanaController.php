<?php

namespace App\Http\Controllers;

use App\Models\AnalisaReksaDana;
use Illuminate\Http\Request;

class ReksaDanaController extends Controller
{
    private const JENIS_OPTIONS = ['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'];
    private const KATEGORI_OPTIONS = ['Konvensional', 'Syariah', 'index', 'ETF'];

    public function index(Request $request)
    {
        $query = AnalisaReksaDana::query()->where('user_id', auth()->id())->whereIn('status', ['submitted', 'input_manual']);

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

        $reksaDanas = $query->orderBy('nama_reksa_dana')->paginate(20)->withQueryString();

        return view('reksa-dana.index', [
            'reksaDanas'      => $reksaDanas,
            'jenisOptions'    => self::JENIS_OPTIONS,
            'kategoriOptions' => self::KATEGORI_OPTIONS,
        ]);
    }

    public function edit(AnalisaReksaDana $reksaDana)
    {
        $reksaDana->load(['sektor', 'efek', 'kinerja', 'obligasi', 'bank']);

        $editData = [
            'sektor'   => $reksaDana->sektor->map(fn($s) => ['nama_sektor' => $s->nama_sektor, 'bobot' => $s->bobot])->values(),
            'efek'     => $reksaDana->efek->map(fn($e) => ['kode_efek' => $e->kode_efek, 'nama_efek' => $e->nama_efek, 'sektor' => $e->sektor, 'bobot' => $e->bobot, 'kontribusi_kinerja' => $e->kontribusi_kinerja, 'market_cap' => $e->market_cap, 'top_10' => $e->top_10])->values(),
            'kinerja'  => $reksaDana->kinerja->map(fn($k) => ['periode' => \Carbon\Carbon::parse($k->periode)->format('Y-m'), 'return_pct' => $k->return_pct])->values(),
            'obligasi' => $reksaDana->obligasi->map(fn($o) => ['kode_obligasi' => $o->kode_obligasi, 'nama_obligasi' => $o->nama_obligasi, 'bobot' => $o->bobot, 'durasi' => $o->durasi, 'rating' => $o->rating])->values(),
            'bank'     => $reksaDana->bank->map(fn($b) => ['nama_bank' => $b->nama_bank, 'bobot' => $b->bobot, 'car' => $b->car, 'npl' => $b->npl, 'klasifikasi_risiko' => $b->klasifikasi_risiko])->values(),
        ];

        return view('reksa-dana.edit', [
            'reksaDana'       => $reksaDana,
            'editData'        => $editData,
            'jenisOptions'    => self::JENIS_OPTIONS,
            'kategoriOptions' => self::KATEGORI_OPTIONS,
        ]);
    }

    public function update(Request $request, AnalisaReksaDana $reksaDana)
    {
        $request->validate([
            'nama_reksa_dana'      => 'required|string|max:255',
            'jenis_reksa_dana'     => 'required|in:' . implode(',', self::JENIS_OPTIONS),
            'kategori'             => 'nullable|array',
            'kategori.*'           => 'in:Konvensional,Syariah,index,ETF',
            'mata_uang'            => 'nullable|string|max:10',
            'total_aum'            => 'nullable|numeric|min:0',
            'total_marcap_10_efek' => 'nullable|numeric|min:0',
            'tanggal_data'         => 'nullable|date',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $reksaDana) {
            $reksaDana->update(array_merge(
                $request->only(['nama_reksa_dana', 'jenis_reksa_dana', 'mata_uang', 'total_aum', 'total_marcap_10_efek', 'tanggal_data']),
                ['kategori' => $request->kategori ?? []]
            ));

            $sektor   = collect($request->sektor ?? [])->filter(fn($r) => !empty($r['nama_sektor']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $efek     = collect($request->efek ?? [])->filter(fn($r) => !empty($r['kode_efek']) && !empty($r['nama_efek']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $kinerja  = collect($request->kinerja ?? [])->filter(fn($r) => !empty($r['periode']) && isset($r['return_pct']) && $r['return_pct'] !== '')->values()->all();
            $obligasi = collect($request->obligasi ?? [])->filter(fn($r) => !empty($r['kode_obligasi']) && !empty($r['nama_obligasi']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();
            $bank     = collect($request->bank ?? [])->filter(fn($r) => !empty($r['nama_bank']) && isset($r['bobot']) && $r['bobot'] !== '')->values()->all();

            $reksaDana->sektor()->delete();
            $reksaDana->efek()->delete();
            $reksaDana->kinerja()->delete();
            $reksaDana->obligasi()->delete();
            $reksaDana->bank()->delete();

            if ($sektor)   $reksaDana->sektor()->createMany($sektor);
            if ($efek)     $reksaDana->efek()->createMany($efek);
            if ($kinerja)  $reksaDana->kinerja()->createMany($kinerja);
            if ($obligasi) $reksaDana->obligasi()->createMany($obligasi);
            if ($bank)     $reksaDana->bank()->createMany($bank);
        });

        return redirect()->route('user.reksa-dana.index')->with('success', 'Data reksa dana berhasil diperbarui.');
    }

    public function destroy(AnalisaReksaDana $reksaDana)
    {
        $reksaDana->delete();

        return redirect()->route('user.reksa-dana.index')->with('success', 'Data reksa dana berhasil dihapus.');
    }
}
