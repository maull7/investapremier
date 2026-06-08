<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InvestmentPersonService;
use Illuminate\Http\Request;

class InvestmentPersonRoleController extends Controller
{
    public function show(Request $request, InvestmentPersonService $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
        ]);

        return response()->json($service->detail($validated['name']));
    }
}
