<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PerencanaanInvestasi;
use App\Models\PortofolioItem;
use App\Models\AiPrompt;
use App\Models\ReksaDana;
use App\Models\Stock;
use App\Models\ObligasiHargaReferensi;
use App\Models\HargaReksaDana;
use App\Models\StockPrice;
use App\Models\ProgressCheckin;
use App\Models\MemberPortfolio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PerencanaanInvestasiController extends Controller
{
    public function index(Request $request)
    {
        $plans = PerencanaanInvestasi::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        $jenisFilter = $request->get('jenis_portfolio');

        $portfolioQuery = \App\Models\MemberPortfolio::where('user_id', auth()->id());

        if ($jenisFilter) {
            $portfolioQuery->where('jenis', $jenisFilter);
        }

        $portfolioItems = $portfolioQuery->latest()->get();

        $portfolioItems->each(function ($item) {
            $item->penerbit = match ($item->jenis) {
                'Saham' => \App\Models\Stock::where('kode', $item->nama_efek)->value('nama'),
                'Reksa Dana', 'Reksadana' => \App\Models\ReksaDana::where('nama_reksa_dana', $item->nama_efek)->value('nama_manajer_investasi'),
                'Obligasi' => \App\Models\ObligasiHargaReferensi::where('nama', $item->nama_efek)->value('nama'),
                default => null,
            } ?? '-';
        });

        return view('perencanaan-investasi.index', compact('plans', 'portfolioItems', 'jenisFilter'));
    }

    public function create()
    {
        $memberPortfolios = MemberPortfolio::where('user_id', auth()->id())->latest()->get();

        return view('perencanaan-investasi.form', [
            'portofolioItems' => collect(),
            'memberPortfolios' => $memberPortfolios,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_perencanaan' => 'required|string|max:255',
            'kebutuhan_dana' => 'nullable|numeric',
            'target_waktu_tahun' => 'nullable|integer|min:1|max:100',
            'dana_tersedia' => 'nullable|numeric',
            'investasi_per_bulan' => 'nullable|numeric',
            'sumber_dana' => 'nullable|string',
            'profil_risiko' => 'nullable|string',
            'usia_anak' => 'nullable|string|max:50',
            'target_pendidikan' => 'nullable|string|max:255',
            'tipe_pendidikan' => 'nullable|string|max:255',
            'lokasi_pendidikan' => 'nullable|string|max:255',
            'estimasi_biaya_saat_ini' => 'nullable|numeric',
            'pemenuhan_dana' => 'nullable|numeric',
        ]);

        if ($validated['kategori_perencanaan'] === 'Lainnya' && $request->filled('kategori_custom')) {
            $validated['kategori_perencanaan'] = $request->kategori_custom;
        }

        $validated['user_id'] = auth()->id();

        $plan = PerencanaanInvestasi::create($validated);

        $this->syncPortofolioItems($plan, $request);

        try {
            $result = $this->generateAiAnalysis($plan);
            if ($result) {
                $plan->update([
                    'ai_narasi' => $result['raw'],
                    'ai_output' => $result['parsed'],
                ]);
            }
        } catch (\Throwable $e) {
            $plan->update([
                'ai_output' => ['error' => true, 'message' => $e->getMessage()],
            ]);
        }

        return redirect()->route('user.perencanaan-investasi.show', $plan)
            ->with('success', 'Rencana investasi berhasil dibuat.');
    }

    public function show(PerencanaanInvestasi $perencanaanInvestasi)
    {
        $owner = $perencanaanInvestasi->user;
        if ($owner->id !== auth()->id() && $owner->advisor_id !== auth()->id()) abort(403);
        $plan = $perencanaanInvestasi;
        $plan->load('portofolioItems', 'progressCheckins');
        $checkins = $plan->progressCheckins()->latest()->get();
        $latestCheckin = $checkins->first();
        return view('perencanaan-investasi.show', compact('plan', 'checkins', 'latestCheckin'));
    }

    public function edit(PerencanaanInvestasi $perencanaanInvestasi)
    {
        if ($perencanaanInvestasi->user_id !== auth()->id()) abort(403);
        $perencanaanInvestasi->load('portofolioItems');
        return view('perencanaan-investasi.form', [
            'plan' => $perencanaanInvestasi,
            'portofolioItems' => $perencanaanInvestasi->portofolioItems,
        ]);
    }

    public function update(Request $request, PerencanaanInvestasi $perencanaanInvestasi)
    {
        if ($perencanaanInvestasi->user_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'kategori_perencanaan' => 'required|string|max:255',
            'kebutuhan_dana' => 'nullable|numeric',
            'target_waktu_tahun' => 'nullable|integer|min:1|max:100',
            'dana_tersedia' => 'nullable|numeric',
            'investasi_per_bulan' => 'nullable|numeric',
            'sumber_dana' => 'nullable|string',
            'profil_risiko' => 'nullable|string',
            'usia_anak' => 'nullable|string|max:50',
            'target_pendidikan' => 'nullable|string|max:255',
            'tipe_pendidikan' => 'nullable|string|max:255',
            'lokasi_pendidikan' => 'nullable|string|max:255',
            'estimasi_biaya_saat_ini' => 'nullable|numeric',
            'pemenuhan_dana' => 'nullable|numeric',
        ]);

        if ($validated['kategori_perencanaan'] === 'Lainnya' && $request->filled('kategori_custom')) {
            $validated['kategori_perencanaan'] = $request->kategori_custom;
        }

        $perencanaanInvestasi->update($validated);

        $this->syncPortofolioItems($perencanaanInvestasi, $request);

        try {
            $result = $this->generateAiAnalysis($perencanaanInvestasi);
            if ($result) {
                $perencanaanInvestasi->update([
                    'ai_narasi' => $result['raw'],
                    'ai_output' => $result['parsed'],
                ]);
            }
        } catch (\Throwable $e) {
            $perencanaanInvestasi->update([
                'ai_output' => ['error' => true, 'message' => $e->getMessage()],
            ]);
        }

        return redirect()->route('user.perencanaan-investasi.show', $perencanaanInvestasi)
            ->with('success', 'Rencana investasi berhasil diperbarui.');
    }

    public function destroy(PerencanaanInvestasi $perencanaanInvestasi)
    {
        if ($perencanaanInvestasi->user_id !== auth()->id()) abort(403);
        $perencanaanInvestasi->portofolioItems()->delete();
        $perencanaanInvestasi->delete();
        return redirect()->route('user.perencanaan-investasi.index')
            ->with('success', 'Rencana investasi berhasil dihapus.');
    }

    public function checkinStore(Request $request, PerencanaanInvestasi $perencanaanInvestasi)
    {
        if ($perencanaanInvestasi->user_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'dana_terkumpul' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:500',
        ]);

        ProgressCheckin::create([
            'perencanaan_investasi_id' => $perencanaanInvestasi->id,
            'user_id' => auth()->id(),
            'dana_terkumpul' => $validated['dana_terkumpul'],
            'catatan' => $validated['catatan'] ?? null,
            'tanggal_checkin' => now(),
        ]);

        return redirect()->route('user.perencanaan-investasi.show', $perencanaanInvestasi)
            ->with('success', 'Check-in progress berhasil dicatat.');
    }

    public function exportPdf(PerencanaanInvestasi $perencanaanInvestasi)
    {
        if ($perencanaanInvestasi->user_id !== auth()->id()) abort(403);
        $perencanaanInvestasi->load('portofolioItems', 'progressCheckins');

        $pdf = Pdf::loadView('perencanaan-investasi.pdf', [
            'plan' => $perencanaanInvestasi,
        ]);

        $filename = 'Perencanaan_Investasi_' . str_replace(' ', '_', $perencanaanInvestasi->kategori_perencanaan) . '.pdf';
        return $pdf->download($filename);
    }

    public function regenerateAi(PerencanaanInvestasi $perencanaanInvestasi)
    {
        if ($perencanaanInvestasi->user_id !== auth()->id()) abort(403);

        try {
            $result = $this->generateAiAnalysis($perencanaanInvestasi);
            $perencanaanInvestasi->update([
                'ai_narasi' => $result['raw'],
                'ai_output' => $result['parsed'],
            ]);
        } catch (\Throwable $e) {
            $perencanaanInvestasi->update([
                'ai_output' => ['error' => true, 'message' => $e->getMessage()],
            ]);
        }

        return redirect()->route('user.perencanaan-investasi.show', $perencanaanInvestasi)
            ->with('success', 'Analisa AI berhasil diperbarui.');
    }

    public function getProduk(Request $request)
    {
        $jenis = $request->query('jenis');

        return match ($jenis) {
            'Reksa Dana' => ReksaDana::select('id', 'nama_reksa_dana as nama', 'nab_per_unit as harga')
                ->orderBy('nama_reksa_dana')->get(),
            'Saham' => Stock::select('id', 'kode as nama', 'harga_terbaru as harga')
                ->orderBy('kode')->get(),
            'Obligasi' => ObligasiHargaReferensi::select('id', 'nama', 'harga_persen as harga')
                ->orderBy('nama')->get(),
            default => collect(),
        };
    }

    public function getHarga(Request $request)
    {
        $jenis = $request->query('jenis');
        $produkType = $request->query('produk_type');
        $produkId = $request->query('produk_id');

        return match ($jenis) {
            'Kas/Deposito' => response()->json(['harga' => 1]),
            'Reksa Dana' => $this->getReksaDanaHarga((int) $produkId),
            'Saham' => $this->getSahamHarga($produkId),
            'Obligasi' => $this->getObligasiHarga((int) $produkId),
            default => response()->json(['harga' => null]),
        };
    }

    public function getGrafik(Request $request)
    {
        $jenis = $request->query('jenis');
        $produkType = $request->query('produk_type');
        $produkId = $request->query('produk_id');
        $nama = $request->query('nama');

        $jenis = match (true) {
            in_array(strtolower($jenis), ['reksa dana', 'reksadana']) => 'Reksa Dana',
            in_array(strtolower($jenis), ['saham']) => 'Saham',
            in_array(strtolower($jenis), ['obligasi']) => 'Obligasi',
            in_array(strtolower($jenis), ['kas/deposito', 'kas', 'deposito']) => 'Kas/Deposito',
            default => $jenis,
        };

        $lookupId = $produkId;
        if (empty($lookupId) && !empty($nama)) {
            $lookupId = match ($jenis) {
                'Reksa Dana' => \App\Models\ReksaDana::where('nama_reksa_dana', $nama)->value('id'),
                'Saham' => \App\Models\Stock::where('kode', $nama)->value('kode'),
                'Obligasi' => \App\Models\ObligasiHargaReferensi::where('nama', $nama)->value('id'),
                default => null,
            } ?? $nama;
        }

        $data = match ($jenis) {
            'Reksa Dana' => $this->grafikReksaDana((int) ($lookupId ?: 0)),
            'Saham' => $this->grafikSaham($lookupId ?? ''),
            'Obligasi' => $this->grafikObligasi((int) ($lookupId ?: 0)),
            'Kas/Deposito' => $this->grafikKas(),
            default => ['labels' => [], 'values' => [], 'label' => ''],
        };

        return response()->json($data);
    }

    public function getRekomendasi(Request $request)
    {
        $items = PortofolioItem::where('user_id', auth()->id())
            ->when($request->query('plan_id'), fn($q, $id) => $q->where('perencanaan_investasi_id', $id))
            ->get();

        $rekomendasi = $items->map(function ($item) {
            return $this->generateRekomendasi($item);
        });

        return response()->json(['items' => $rekomendasi]);
    }

    private function syncPortofolioItems(PerencanaanInvestasi $plan, Request $request): void
    {
        $items = $request->input('portofolio_items', []);

        $plan->portofolioItems()->delete();

        foreach ($items as $item) {
            if (empty($item['jenis']) || empty($item['nama_produk'])) continue;

            $nominal = str_replace(['.', ','], ['', '.'], $item['nominal'] ?? '0');
            $harga = str_replace(['.', ','], ['', '.'], $item['harga_akuisisi'] ?? '0');
            $nominal = (float) $nominal;
            $harga = (float) $harga;

            $plan->portofolioItems()->create([
                'user_id' => auth()->id(),
                'jenis' => $item['jenis'],
                'produk_type' => $item['produk_type'] ?? null,
                'produk_id' => !empty($item['produk_id']) ? $item['produk_id'] : null,
                'nama_produk' => $item['nama_produk'],
                'nominal' => $nominal,
                'harga_akuisisi' => $harga,
                'nilai' => $nominal * $harga,
            ]);
        }
    }

    private function getReksaDanaHarga(int $id)
    {
        $rd = ReksaDana::find($id);
        return response()->json(['harga' => $rd ? (float) $rd->nab_per_unit : null]);
    }

    private function getSahamHarga($kode)
    {
        $stock = Stock::where('kode', $kode)->first();
        return response()->json(['harga' => $stock ? (float) $stock->harga_terbaru : null]);
    }

    private function getObligasiHarga(int $id)
    {
        $ob = ObligasiHargaReferensi::find($id);
        return response()->json(['harga' => $ob ? (float) $ob->harga_persen : null]);
    }

    private function grafikReksaDana($id): array
    {
        $hargas = HargaReksaDana::where('reksa_dana_id', (int) $id)
            ->where('tanggal', '>=', now()->subYear())
            ->orderBy('tanggal')
            ->get();

        if ($hargas->isNotEmpty()) {
            return [
                'labels' => $hargas->pluck('tanggal')->map(fn($d) => $d->format('d M Y')),
                'values' => $hargas->pluck('nab_per_unit')->map(fn($v) => (float) $v),
                'label' => 'NAB/UP',
            ];
        }

        $rd = ReksaDana::find((int) $id);
        $nab = $rd ? (float) $rd->nab_per_unit : 1000;
        $labels = [];
        $values = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $labels[] = $m->format('M Y');
            $values[] = $nab;
        }
        return ['labels' => $labels, 'values' => $values, 'label' => 'NAB/UP (simulasi)'];
    }

    private function grafikSaham($kode): array
    {
        $prices = StockPrice::where('kode_efek', strtoupper($kode))
            ->where('tanggal', '>=', now()->subYear())
            ->orderBy('tanggal')
            ->get();

        if ($prices->isNotEmpty()) {
            return [
                'labels' => $prices->pluck('tanggal')->map(fn($d) => $d->format('d M Y')),
                'values' => $prices->pluck('harga')->map(fn($v) => (float) $v),
                'label' => 'Harga Saham',
            ];
        }

        $stock = Stock::where('kode', $kode)->first();
        $harga = $stock ? (float) $stock->harga_terbaru : 1000;
        $labels = [];
        $values = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $labels[] = $m->format('M Y');
            $values[] = $harga;
        }
        return ['labels' => $labels, 'values' => $values, 'label' => 'Harga Saham (simulasi)'];
    }

    private function grafikObligasi($id): array
    {
        $ob = ObligasiHargaReferensi::find((int) $id);
        if (!$ob) return ['labels' => [], 'values' => [], 'label' => ''];

        $prices = StockPrice::where('kode_efek', strtoupper($ob->kode))
            ->where('tanggal', '>=', now()->subYear())
            ->orderBy('tanggal')
            ->get();

        if ($prices->isEmpty()) {
            $labels = [];
            $values = [];
            for ($i = 11; $i >= 0; $i--) {
                $m = now()->subMonths($i);
                $labels[] = $m->format('M Y');
                $values[] = (float) $ob->harga_persen;
            }
            return ['labels' => $labels, 'values' => $values, 'label' => 'Harga Obligasi (%)'];
        }

        return [
            'labels' => $prices->pluck('tanggal')->map(fn($d) => $d->format('d M Y')),
            'values' => $prices->pluck('harga')->map(fn($v) => (float) $v),
            'label' => 'Harga Obligasi',
        ];
    }

    private function grafikKas(): array
    {
        $labels = [];
        $values = [];
        $bunga = 0.03;
        $saldo = 1000000;
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $labels[] = $m->format('M Y');
            $values[] = round($saldo, 2);
            $saldo += $saldo * $bunga / 12;
        }
        return ['labels' => $labels, 'values' => $values, 'label' => 'Simulasi Pertumbuhan (3% p.a.)'];
    }

    private function generateRekomendasi(PortofolioItem $item): array
    {
        $rekomendasi = 'Hold untuk jangka panjang.';
        $analisa = 'Data fundamental perlu dianalisa lebih lanjut.';

        if ($item->jenis === 'Saham') {
            $stock = Stock::where('kode', $item->nama_produk)->first();
            if ($stock && $stock->harga_terbaru > 0 && $item->harga_akuisisi > 0) {
                $change = (($stock->harga_terbaru - $item->harga_akuisisi) / $item->harga_akuisisi) * 100;
                if ($change < -10) {
                    $rekomendasi = 'Waspada. Pertimbangkan cut loss jika fundamental memburuk.';
                    $analisa = 'Harga turun ' . number_format(abs($change), 1) . '% dari harga akuisisi.';
                } elseif ($change > 15) {
                    $rekomendasi = 'Ambil untung sebagian (take profit).';
                    $analisa = 'Harga naik ' . number_format($change, 1) . '% dari harga akuisisi.';
                } else {
                    $rekomendasi = 'Hold. Lakukan averaging jika harga turun signifikan.';
                    $analisa = 'Harga bergerak ' . number_format($change, 1) . '% dari harga akuisisi.';
                }
            }
        } elseif ($item->jenis === 'Reksa Dana') {
            $rekomendasi = 'Hold. Evaluasi NAB secara berkala.';
            $analisa = 'Kinerja reksa dana perlu dipantau rutin.';
        } elseif ($item->jenis === 'Obligasi') {
            $rekomendasi = 'Hold hingga jatuh tempo untuk hasil optimal.';
            $analisa = 'Obligasi memberikan pendapatan tetap (kupon).';
        } elseif ($item->jenis === 'Kas/Deposito') {
            $rekomendasi = 'Suitable untuk dana darurat.';
            $analisa = 'Likuiditas tinggi dengan return rendah.';
        }

        return [
            'id' => $item->id,
            'nama_produk' => $item->nama_produk,
            'jenis' => $item->jenis,
            'nilai' => $item->nilai,
            'nominal' => $item->nominal,
            'harga_akuisisi' => $item->harga_akuisisi,
            'analisa' => $analisa,
            'rekomendasi' => $rekomendasi,
        ];
    }

    private function generateAiAnalysis(PerencanaanInvestasi $plan): ?array
    {
        if (!config('services.openai.key') && !config('services.groq.key')) return null;

        $groq = app(\App\Services\GroqService::class);
        $systemPrompt = AiPrompt::get('system_perencanaan_investasi', 'Kamu adalah AI Financial Planning Assistant yang bertugas menganalisa perencanaan investasi pengguna. Hitung proyeksi kebutuhan dana, estimasi nilai investasi, dan berikan rekomendasi strategi.');

        $data = $this->buildDataSection($plan);
        $instruksi = AiPrompt::get('instruksi_perencanaan_investasi', <<<JSON
{
  "ringkasan": "string",
  "analisis_keuangan": {
    "total_kebutuhan": "string",
    "dana_saat_ini": "string",
    "defisit": "string",
    "investasi_bulanan": "string"
  },
  "proyeksi": {
    "nilai_terkumpul": "string",
    "ketercapaian": "string",
    "gap_dana": "string"
  },
  "asumsi": {
    "inflasi": "string",
    "return_investasi": "string"
  },
  "rekomendasi_strategi": ["string"],
  "alokasi_aset": [
    {"jenis": "string", "persentase": "string", "keterangan": "string"}
  ],
  "rekomendasi_portofolio": [
    {
      "nama_efek": "string",
      "jenis": "string",
      "analisa": "string",
      "rekomendasi": "string",
      "aksi": "Beli" atau "Tahan" atau "Jual"
    }
  ],
  "rekomendasi_investor": "string",
  "catatan_risiko": "string"
}
JSON
        );

        $raw = $groq->callAi([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $data . "\n\n" . $instruksi],
        ], 90, 0.3);

        $parsed = $this->parseJsonOutput($raw);

        return ['raw' => $raw, 'parsed' => $parsed];
    }

    private function buildDataSection(PerencanaanInvestasi $plan): string
    {
        $plan->load('portofolioItems');

        $lines = [];
        $lines[] = "DATA PERENCANAAN INVESTASI";
        $lines[] = "Kategori: {$plan->kategori_perencanaan}";
        $lines[] = "Kebutuhan Dana: Rp " . number_format($plan->kebutuhan_dana ?? 0, 0, ',', '.');
        $lines[] = "Target Waktu: {$plan->target_waktu_tahun} tahun";
        $lines[] = "Dana Tersedia: Rp " . number_format($plan->dana_tersedia ?? 0, 0, ',', '.');
        $lines[] = "Investasi per Bulan: Rp " . number_format($plan->investasi_per_bulan ?? 0, 0, ',', '.');
        $lines[] = "Sumber Dana: {$plan->sumber_dana}";
        $lines[] = "Profil Risiko: {$plan->profil_risiko}";

        if ($plan->usia_anak) $lines[] = "Usia Anak: {$plan->usia_anak}";
        if ($plan->target_pendidikan) $lines[] = "Target Pendidikan: {$plan->target_pendidikan}";
        if ($plan->tipe_pendidikan) $lines[] = "Tipe Pendidikan: {$plan->tipe_pendidikan}";
        if ($plan->lokasi_pendidikan) $lines[] = "Lokasi Pendidikan: {$plan->lokasi_pendidikan}";
        if ($plan->estimasi_biaya_saat_ini) $lines[] = "Estimasi Biaya Saat Ini: Rp " . number_format($plan->estimasi_biaya_saat_ini, 0, ',', '.');
        if ($plan->pemenuhan_dana) $lines[] = "Pemenuhan Dana: Rp " . number_format($plan->pemenuhan_dana, 0, ',', '.');

        if ($plan->portofolioItems->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "DATA PORTOFOLIO SAAT INI:";
            foreach ($plan->portofolioItems as $item) {
                $lines[] = "- {$item->jenis}: {$item->nama_produk} | Nominal: " . number_format($item->nominal, 0, ',', '.') . " | Harga: Rp " . number_format($item->harga_akuisisi, 0, ',', '.') . " | Nilai: Rp " . number_format($item->nilai, 0, ',', '.');
            }
            $total = $plan->portofolioItems->sum('nilai');
            $lines[] = "Total Nilai Portofolio: Rp " . number_format($total, 0, ',', '.');
        }

        $lines[] = "";

        return implode("\n", $lines);
    }

    private function parseJsonOutput(string $raw): array
    {
        $raw = trim($raw);

        if (str_starts_with($raw, '```')) {
            $raw = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $raw);
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        preg_match('/\{[\s\S]*\}/', $raw, $matches);
        if (!empty($matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return [];
    }
}
