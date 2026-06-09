<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ObligasiHargaReferensi;
use App\Models\Stock;
use App\Services\Extractors\IdxAiDataExtractorService;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IdxAiExtractionController extends Controller
{
    public function __construct(
        private IdxAiDataExtractorService $extractor,
    ) {}

    public function index(Request $request)
    {
        $type = in_array($request->get('type'), ['saham', 'obligasi']) ? $request->type : 'saham';

        $results = [];
        $fetchResult = null;
        $lastUrl = '';
        $lastMergeUrl = '';
        $mergeStats = null;
        $token = $request->get('token');

        if ($token) {
            $cacheKey = "idx_ai_extract_{$token}";
            $cached = Cache::get($cacheKey, []);
            if ($cached) {
                $results = $cached['data'] ?? [];
                $fetchResult = $cached['fetch'] ?? null;
                $lastUrl = $cached['url'] ?? '';
                $lastMergeUrl = $cached['merge_url'] ?? '';
                $mergeStats = $cached['merge_stats'] ?? null;
            }
        }

        $existingCodes = [];
        if (!empty($results)) {
            $codes = collect($results)->pluck('kode')->filter()->map(fn($v) => strtoupper(trim($v)))->values();
            if ($codes->isNotEmpty()) {
                if ($type === 'obligasi') {
                    $existingCodes = ObligasiHargaReferensi::whereIn('kode', $codes)->pluck('kode')
                        ->map(fn($v) => strtoupper(trim($v)))->flip()->all();
                } else {
                    $existingCodes = Stock::whereIn('kode', $codes)->pluck('kode')
                        ->map(fn($v) => strtoupper(trim($v)))->flip()->all();
                }
            }
        }

        [$paginated, $resultsTotal] = $this->paginateResults($results);

        return view('admin.idx-ai-extraction.index', compact(
            'type', 'paginated', 'resultsTotal', 'fetchResult', 'lastUrl', 'lastMergeUrl',
            'mergeStats', 'existingCodes', 'token'
        ));
    }

    public function extract(Request $request)
    {
        $data = $request->validate([
            'url' => 'required|string|max:500',
            'type' => 'required|in:saham,obligasi',
            'raw_content' => 'nullable|string',
            'merge_url' => 'nullable|string|max:500',
        ]);

        $type = $data['type'];

        $result = $this->extractor->extract($data['url'], $type, $data['raw_content'], $data['merge_url'] ?? null);

        if (!$result['success']) {
            return back()
                ->withInput()
                ->with('error', $result['message']);
        }

        $token = Str::random(32);
        $cacheKey = "idx_ai_extract_{$token}";
        Cache::put($cacheKey, [
            'data' => $result['data'],
            'fetch' => $result,
            'url' => $data['url'],
            'merge_url' => $data['merge_url'] ?? null,
            'merge_stats' => $result['merge_stats'] ?? null,
        ], now()->addMinutes(30));

        return redirect()
            ->route('admin.idx-ai-extraction.index', ['type' => $type, 'token' => $token])
            ->with('success', $result['message']);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:saham,obligasi',
            'selected' => 'required|array|min:1',
            'selected.*' => 'integer',
            'token' => 'required|string',
        ]);

        $type = $data['type'];
        $cacheKey = "idx_ai_extract_{$data['token']}";
        $cached = Cache::get($cacheKey, []);

        if (empty($cached)) {
            return redirect()
                ->route('admin.idx-ai-extraction.index', ['type' => $type])
                ->with('error', 'Sesi ekstraksi telah kedaluwarsa. Silakan ekstrak ulang.');
        }

        $items = $cached['data'] ?? [];
        $selected = $data['selected'];
        $result = ['saved' => 0, 'updated' => 0, 'skipped' => 0];

        DB::transaction(function () use ($items, $selected, $type, &$result) {
            foreach ($selected as $idx) {
                if (!isset($items[$idx])) continue;
                $item = $items[$idx];
                $kode = strtoupper(trim($item['kode'] ?? ''));
                if (!$kode) continue;

                if ($type === 'obligasi') {
                    $this->saveBondRow($item, $kode, $result);
                } else {
                    $this->saveStockRow($item, $kode, $result);
                }
            }
        });

        $label = $type === 'obligasi' ? 'Obligasi' : 'Saham';
        ActivityLogger::log(
            "Simpan Ekstrak AI {$label}",
            "{$result['saved']} baru, {$result['updated']} update, {$result['skipped']} skip",
            'success',
        );

        Cache::forget($cacheKey);

        return redirect()
            ->route('admin.idx-ai-extraction.index', ['type' => $type])
            ->with('success', "Data {$label} disimpan. Baru: {$result['saved']}, Update: {$result['updated']}, Skip: {$result['skipped']}.");
    }

    private function paginateResults(array $results, int $perPage = 50): array
    {
        $total = count($results);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $total > 0 ? array_slice($results, ($page - 1) * $perPage, $perPage, true) : [];
        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        return [$paginator, $total];
    }

    private function saveStockRow(array $item, string $kode, array &$result): void
    {
        $existing = Stock::where('kode', $kode)->first();
        $payload = [
            'kode' => $kode,
            'nama' => $item['nama'] ?? $kode,
            'sektor' => $item['sektor'] ?? null,
            'sub_industri' => $item['sub_industri'] ?? null,
            'harga_terbaru' => $this->parseNumber($item['harga_terbaru'] ?? null),
            'perubahan_persen' => $item['perubahan_persen'] ?? null,
            'volume' => $this->parseNumber($item['volume'] ?? null),
            'value' => $this->parseNumber($item['nilai'] ?? null),
            'frekuensi' => $this->parseNumber($item['frekuensi'] ?? null),
            'harga_tertinggi' => $this->parseNumber($item['harga_tertinggi'] ?? null),
            'harga_terendah' => $this->parseNumber($item['harga_terendah'] ?? null),
            'jumlah_saham' => $this->parseNumber($item['jumlah_saham'] ?? null),
            'last_update' => now(),
        ];

        if ($existing) {
            // Only update fields that have actual values
            $existing->update($payload);
            $result['updated']++;
        } else {
            Stock::create($payload);
            $result['saved']++;
        }
    }

    private function saveBondRow(array $item, string $kode, array &$result): void
    {
        $existing = ObligasiHargaReferensi::where('kode', $kode)->first();
        $payload = [
            'kode' => $kode,
            'nama' => $item['nama'] ?? null,
            'emiten' => $item['emiten'] ?? null,
            'rating' => $item['rating'] ?? null,
            'kupon' => $this->parseNumber($item['kupon'] ?? null),
            'jatuh_tempo' => !empty($item['jatuh_tempo']) ? $item['jatuh_tempo'] : null,
            'harga_persen' => $this->parseNumber($item['harga_persen'] ?? null),
            'ytm' => $this->parseNumber($item['ytm'] ?? null),
            'current_yield' => $this->parseNumber($item['current_yield'] ?? null),
            'syariah' => filter_var($item['syariah'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'denominasi' => $item['denominasi'] ?? 'IDR',
        ];

        if ($existing) {
            $existing->update($payload);
            $result['updated']++;
        } else {
            ObligasiHargaReferensi::create($payload);
            $result['saved']++;
        }
    }

    private function parseNumber($value): ?float
    {
        if ($value === null || $value === '' || $value === 'null') return null;
        if (is_numeric($value)) return (float) $value;
        $clean = preg_replace('/[^\d,\-]/', '', (string) $value);
        $clean = str_replace(',', '.', $clean);
        return is_numeric($clean) ? (float) $clean : null;
    }
}
