<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\InvestmentManager;
use Illuminate\Http\Request;

class InvestmentManagerController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;
        $query = InvestmentManager::with('periods');

        if ($request->search) {
            $s = $request->search;
            $query->where('name', 'like', "%{$s}%");
        }

        $managers = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return view('investment-managers.index', compact('managers', 'perPage'));
    }
}
