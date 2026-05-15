<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScoreClassification;
use Illuminate\Http\Request;

class ScoreClassificationController extends Controller
{
    public function index()
    {
        $classifications = ScoreClassification::orderBy('sort_order')->get();
        return view('admin.score-classifications.index', compact('classifications'));
    }

    public function update(Request $request, ScoreClassification $scoreClassification)
    {
        $data = $request->validate([
            'min_score'             => 'required|integer|min:1',
            'max_score'             => 'required|integer|gt:min_score',
            'alloc_pasar_uang'      => 'required|integer|min:0|max:100',
            'alloc_pendapatan_tetap'=> 'required|integer|min:0|max:100',
            'alloc_campuran'        => 'required|integer|min:0|max:100',
            'alloc_saham'           => 'required|integer|min:0|max:100',
        ]);

        $total = $data['alloc_pasar_uang'] + $data['alloc_pendapatan_tetap']
               + $data['alloc_campuran'] + $data['alloc_saham'];

        if ($total !== 100) {
            return back()->withErrors(['alloc' => "Total alokasi harus 100%. Saat ini: {$total}%"])->withInput();
        }

        $scoreClassification->update($data);

        return back()->with('success', "Klasifikasi {$scoreClassification->profile_name} berhasil diperbarui.");
    }
}
