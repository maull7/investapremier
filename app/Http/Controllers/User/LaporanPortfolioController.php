<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\PortfolioAggregationService;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanPortfolioController extends Controller
{
    public function exportPdf()
    {
        $user = auth()->user();
        $portfolio = app(PortfolioAggregationService::class)->aggregate($user);

        $pdf = Pdf::loadView('laporan-portfolio.pdf', compact('user', 'portfolio'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Laporan_Portfolio_' . str_replace(' ', '_', $user->name) . '.pdf';
        return $pdf->download($filename);
    }
}
