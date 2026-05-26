<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UnitLink;
use App\Models\HargaUnitLink;
use Illuminate\Http\Request;

class UnitLinkController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'unit-links');
        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;

        $unitLinks = collect();
        $hargaUnitLinks = collect();

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
        } elseif ($tab === 'unit-prices') {
            $query = HargaUnitLink::with('unitLink')->latest('datetime');
            if ($request->search) {
                $s = $request->search;
                $query->whereHas('unitLink', fn($q) => $q->where('unit_link', 'like', "%{$s}%"));
            }
            $hargaUnitLinks = $query->paginate($perPage)->withQueryString();
        }

        return view('unit-link.index', compact('tab', 'perPage', 'unitLinks', 'hargaUnitLinks'));
    }
}
