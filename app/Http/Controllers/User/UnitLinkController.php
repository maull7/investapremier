<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UnitLink;
use Illuminate\Http\Request;

class UnitLinkController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'unit-links');
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;

        $unitLinks = collect();

        if ($tab === 'unit-links') {
            $query = UnitLink::latest();
            if ($request->search) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('unit_link', 'like', "%{$s}%")
                      ->orWhere('asuransi', 'like', "%{$s}%")
                      ->orWhere('jenis', 'like', "%{$s}%");
                });
            }
            $unitLinks = $query->paginate($perPage)->withQueryString();
        }

        return view('unit-link.index', compact('tab', 'perPage', 'unitLinks'));
    }
}
