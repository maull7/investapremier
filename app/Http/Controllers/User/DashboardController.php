<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\PortfolioAggregationService;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->isAdvisor()) {
            return redirect()->route('user.advisor.dashboard');
        }

        $data = app(PortfolioAggregationService::class)->aggregate(auth()->user());
        return view('dashboard.user', $data);
    }
}
