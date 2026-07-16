<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AnalisaController;
use App\Models\Stock;
use App\Models\ObligasiBond;
use Illuminate\Http\Request;

class AnalisaRdController extends AnalisaController
{
    protected bool $isAdminContext = true;

    protected function indexRoute(): string
    {
        return 'admin.reksa-dana.index';
    }

    public function lookupKodeEfek(Request $request)
    {
        $kode = strtoupper(trim($request->input('kode', '')));
        
        if (empty($kode)) {
            return response()->json(['found' => false, 'message' => 'Kode efek kosong']);
        }

        $stock = Stock::where('kode', $kode)->first();
        if ($stock) {
            return response()->json([
                'found' => true,
                'type' => 'saham',
                'data' => [
                    'kode_efek' => $stock->kode,
                    'nama_efek' => $stock->nama,
                    'sektor' => $stock->sektor,
                ]
            ]);
        }

        $bond = ObligasiBond::where('kode', $kode)->first();
        if ($bond) {
            return response()->json([
                'found' => true,
                'type' => 'obligasi',
                'data' => [
                    'kode_efek' => $bond->kode,
                    'nama_efek' => $kode,
                ]
            ]);
        }

        return response()->json(['found' => false, 'message' => 'Kode efek tidak ditemukan']);
    }

    public function getFinancialData(Request $request)
    {
        $kode = strtoupper(trim($request->input('kode', '')));
        $type = $request->input('type', 'saham');
        
        if (empty($kode)) {
            return response()->json(['found' => false]);
        }

        if ($type === 'saham') {
            $stock = Stock::where('kode', $kode)->first();
            if ($stock) {
                return response()->json([
                    'found' => true,
                    'data' => [
                        'kode_efek' => $stock->kode,
                        'nama_efek' => $stock->nama,
                        'per' => null,
                        'pbv' => null,
                        'roe' => null,
                        'roa' => null,
                        'npm' => null,
                        'ev_ebitda' => null,
                        'der' => null,
                        'current_ratio' => null,
                        'aktivitas_lancar' => null,
                        'gross_profit_margin' => null,
                        'operating_profit_margin' => null,
                    ]
                ]);
            }
        } elseif ($type === 'obligasi') {
            $bond = ObligasiBond::where('kode', $kode)->first();
            if ($bond) {
                return response()->json([
                    'found' => true,
                    'data' => [
                        'kode_efek' => $bond->kode,
                        'nama_efek' => $kode,
                        'ytm' => null,
                        'rating' => null,
                        'kupon' => null,
                        'tenor' => null,
                        'durasi' => null,
                        'shadow_rating' => null,
                        'der' => null,
                        'current_ratio' => null,
                        'aktivitas_lancar' => null,
                        'gross_profit_margin' => null,
                        'operating_profit_margin' => null,
                    ]
                ]);
            }
        }

        return response()->json(['found' => false]);
    }
}
