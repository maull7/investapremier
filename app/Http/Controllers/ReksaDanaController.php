<?php

namespace App\Http\Controllers;

use App\Models\AnalisaReksaDana;
use Illuminate\Http\Request;

class ReksaDanaController extends Controller
{
    public function index(Request $request)
    {
        $query = AnalisaReksaDana::query()->whereNotNull('ai_narasi');

        if ($request->jenis) {
            $query->where('jenis_reksa_dana', $request->jenis);
        }

        $reksaDanas = $query->orderBy('nama_reksa_dana')->paginate(20);

        return view('reksa-dana.index', compact('reksaDanas'));
    }
}
