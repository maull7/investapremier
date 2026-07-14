<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Services\PortfolioAggregationService;

class DashboardController extends Controller
{
    public function index()
    {
        $data = app(PortfolioAggregationService::class)->aggregateAdvisorClients(auth()->user());
        return view('advisor.dashboard.index', $data);
    }
}
