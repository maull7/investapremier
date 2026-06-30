<?php

namespace App\Http\Controllers;

use App\Services\GroqService;
use App\Services\StockIdentityResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnalisaLapkeuVisionController extends Controller
{
    public function parseSahamPdf(Request $request, GroqService $groq, StockIdentityResolver $stockIdentityResolver)
    {
        return $this->parsePdf($request, $groq, 'Saham', $stockIdentityResolver);
    }

    public function parseObligasiPdf(Request $request, GroqService $groq)
    {
        return $this->parsePdf($request, $groq, 'Obligasi');
    }

    private function parsePdf(Request $request, GroqService $groq, string $instrumen, ?StockIdentityResolver $stockIdentityResolver = null)
    {
        ignore_user_abort(true);
        set_time_limit(600);

        $request->validate([
            'file_pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        $file = $request->file('file_pdf');

        try {
            $data = $groq->parseLapkeuPdfVision(
                $file->getPathname(),
                $instrumen,
                $file->getClientOriginalName()
            );
            if ($instrumen === 'Saham' && $stockIdentityResolver) {
                $data = $stockIdentityResolver->enrich($data, $file->getClientOriginalName());
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal scan PDF dengan AI Vision: ' . $e->getMessage(),
                'data' => null,
            ], 422);
        }

        $filename = 'lapkeu-vision-' . now()->format('Ymd-His') . '-' . Str::random(8) . '.pdf';
        $storedPath = $file->storeAs('lapkeu-pdfs', $filename, 'public');
        $data['pdf_lapkeu_path'] = basename($storedPath);

        $extracted = $this->extractedSummary($data, $instrumen);
        $success = count($extracted) > 0;

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'Scan AI berhasil mengekstrak: ' . implode(', ', $extracted) . '.'
                : 'AI Vision tidak menemukan data laporan keuangan yang cocok dari PDF ini.',
            'data' => $data,
        ]);
    }

    private function extractedSummary(array $data, string $instrumen): array
    {
        $extracted = [];
        $nameKey = $instrumen === 'Obligasi' ? 'nama_obligasi' : 'nama_perusahaan';
        $codeKey = $instrumen === 'Obligasi' ? 'kode_obligasi' : 'kode_saham';

        if (!empty($data[$nameKey])) $extracted[] = 'Nama';
        if (!empty($data[$codeKey])) $extracted[] = 'Kode';
        if (!empty($data['sektor'])) $extracted[] = 'Sektor';
        if (!empty($data['periode'])) $extracted[] = 'Periode';
        if (!empty($data['total_asset'])) $extracted[] = 'Total Aset';
        if (!empty($data['total_liabilities'])) $extracted[] = 'Total Liabilitas';
        if (!empty($data['equity'])) $extracted[] = 'Ekuitas';
        if (!empty($data['net_revenue'])) $extracted[] = 'Pendapatan';
        if (!empty($data['net_income'])) $extracted[] = 'Laba Bersih';
        if (!empty($data['cash_flows_operating_activities'])) $extracted[] = 'Arus Kas';

        return $extracted;
    }
}
