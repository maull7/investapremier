<?php

namespace App\Http\Controllers;

use App\Models\StockPrice;
use App\Services\FfsParserService;
use App\Services\GroqService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnalisaFfsVisionController extends Controller
{
    public function parsePdf(Request $request, GroqService $groq, FfsParserService $parser)
    {
        ignore_user_abort(true);
        set_time_limit(600);

        $request->validate([
            'file_pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        $file = $request->file('file_pdf');

        try {
            $ai = $groq->parseFfsPdfVision($file->getPathname(), $file->getClientOriginalName());
            $data = $parser->normalizeAiParseResult($ai);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal scan PDF dengan AI Vision: ' . $e->getMessage(),
                'data' => null,
            ], 422);
        }

        $filename = 'ffs-vision-' . now()->format('Ymd-His') . '-' . Str::random(8) . '.pdf';
        $storedPath = $file->storeAs('analisa-pdfs', $filename, 'public');

        if ($storedPath && !empty($data['efek'])) {
            $tanggal = now()->subDay()->toDateString();
            foreach ($data['efek'] as $efek) {
                if (!empty($efek['kode_efek'])) {
                    StockPrice::updateOrCreate(
                        ['kode_efek' => strtoupper($efek['kode_efek']), 'tanggal' => $tanggal],
                        [
                            'nama_efek' => $efek['nama_efek'] ?? null,
                            'jenis' => 'Saham',
                            'harga' => $efek['harga'] ?? 0,
                            'sumber' => 'PDF FFS Vision',
                        ]
                    );
                }
            }
        }

        $extracted = $this->extractedSummary($data);
        $success = count($extracted) > 0;

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'Scan AI berhasil mengekstrak: ' . implode(', ', $extracted) . '.'
                : 'AI Vision tidak menemukan data yang cocok dari PDF ini.',
            'data' => $data,
            'pdf_file' => $storedPath,
        ]);
    }

    private function extractedSummary(array $data): array
    {
        $extracted = [];
        if (!empty($data['nama_reksa_dana'])) $extracted[] = 'Nama RD';
        if (!empty($data['jenis_reksa_dana'])) $extracted[] = 'Jenis RD';
        if (!empty($data['kategori'])) $extracted[] = 'Kategori';
        if (!empty($data['total_aum'])) $extracted[] = 'Total AUM';
        if (!empty($data['unit_penyertaan'])) $extracted[] = 'Unit Penyertaan';
        if (!empty($data['nab_per_unit'])) $extracted[] = 'NAB/UP';
        if (!empty($data['tanggal_data'])) $extracted[] = 'Tanggal Data';
        if (!empty($data['alokasi_aset'])) $extracted[] = count($data['alokasi_aset']) . ' Alokasi Aset';
        if (!empty($data['sektor'])) $extracted[] = count($data['sektor']) . ' Sektor';
        if (!empty($data['efek'])) $extracted[] = count($data['efek']) . ' Efek';
        if (!empty($data['kinerja'])) $extracted[] = count($data['kinerja']) . ' Bulan Kinerja';
        if (!empty($data['obligasi'])) $extracted[] = count($data['obligasi']) . ' Obligasi';
        if (!empty($data['bank'])) $extracted[] = count($data['bank']) . ' Bank';

        return $extracted;
    }
}
