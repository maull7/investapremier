<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PerencanaanInvestasi;
use App\Models\AiPrompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PerencanaanInvestasiController extends Controller
{
    public function index()
    {
        $plans = PerencanaanInvestasi::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('perencanaan-investasi.index', compact('plans'));
    }

    public function create()
    {
        return view('perencanaan-investasi.form');
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
        if ($perencanaanInvestasi->user_id !== auth()->id()) abort(403);
        return view('perencanaan-investasi.show', ['plan' => $perencanaanInvestasi]);
    }

    public function edit(PerencanaanInvestasi $perencanaanInvestasi)
    {
        if ($perencanaanInvestasi->user_id !== auth()->id()) abort(403);
        return view('perencanaan-investasi.form', ['plan' => $perencanaanInvestasi]);
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

        return redirect()->route('user.perencanaan-investasi.show', $perencanaanInvestasi)
            ->with('success', 'Rencana investasi berhasil diperbarui.');
    }

    public function destroy(PerencanaanInvestasi $perencanaanInvestasi)
    {
        if ($perencanaanInvestasi->user_id !== auth()->id()) abort(403);
        $perencanaanInvestasi->delete();
        return redirect()->route('user.perencanaan-investasi.index')
            ->with('success', 'Rencana investasi berhasil dihapus.');
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

    private function generateAiAnalysis(PerencanaanInvestasi $plan): ?array
    {
        $apiKey = config('services.groq.key');
        $model = config('services.groq.model');
        $url = config('services.groq.url');

        if (!$apiKey) return null;

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
  "rekomendasi_investor": "string",
  "catatan_risiko": "string"
}
JSON
        );

        $response = Http::withToken($apiKey)
            ->timeout(90)
            ->post($url, [
                'model' => $model,
                'temperature' => 0.3,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $data . "\n\n" . $instruksi],
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception('AI API error: ' . $response->body());
        }

        $body = $response->json();
        $raw = $body['choices'][0]['message']['content'] ?? '';
        $parsed = $this->parseJsonOutput($raw);

        return ['raw' => $raw, 'parsed' => $parsed];
    }

    private function buildDataSection(PerencanaanInvestasi $plan): string
    {
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
