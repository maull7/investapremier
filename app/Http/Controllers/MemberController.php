<?php

namespace App\Http\Controllers;

use App\Models\MemberProfile;
use App\Models\MemberPortfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function create()
    {
        $profile = MemberProfile::where('user_id', Auth::id())->first();
        $portfolios = MemberPortfolio::where('user_id', Auth::id())->get()
            ->map(fn($p) => [
                'jenis'             => $p->jenis,
                'nama_efek'         => $p->nama_efek,
                'mulai_kepemilikan' => $p->mulai_kepemilikan?->format('Y-m-d'),
                'jumlah'            => $p->jumlah,
            ])->values()->toArray();
        return view('member.form', compact('profile', 'portfolios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_telepon'               => 'required|string|max:20',
            'jenis_kelamin'            => 'required|in:Laki-laki,Perempuan',
            'kewarganegaraan'          => 'required|in:WNI,WNA',
            'agama'                    => 'required|string',
            'pekerjaan'                => 'required|string',
            'rata_rata_penghasilan'    => 'required|string',
            'pembukaan_rekening_efek'  => 'required|in:Pribadi,Pihak Lainnya',
            'jenis_investasi'          => 'required|array|min:1',
            'sumber_dana'              => 'required|array|min:1',
            'tujuan_investasi'         => 'required|array|min:1',
            'portfolios.*.nama_efek'   => 'required_with:portfolios.*.jenis|string',
            'portfolios.*.jenis'       => 'nullable|in:Dana,Saham,Obligasi',
            'portfolios.*.mulai_kepemilikan' => 'nullable|date',
            'portfolios.*.jumlah'      => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            MemberProfile::updateOrCreate(
                ['user_id' => Auth::id()],
                array_merge(
                    $request->only('agama', 'pekerjaan', 'rata_rata_penghasilan', 'pembukaan_rekening_efek', 'maksud_tujuan_lain', 'no_telepon', 'jenis_kelamin', 'kewarganegaraan'),
                    [
                        'jenis_investasi'  => $request->jenis_investasi,
                        'sumber_dana'      => $request->sumber_dana,
                        'tujuan_investasi' => $request->tujuan_investasi,
                        'status'           => 'pending',
                    ]
                )
            );

            MemberPortfolio::where('user_id', Auth::id())->delete();

            foreach (($request->portfolios ?? []) as $p) {
                if (!empty($p['jenis']) && !empty($p['nama_efek'])) {
                    MemberPortfolio::create([
                        'user_id'          => Auth::id(),
                        'jenis'            => $p['jenis'],
                        'nama_efek'        => $p['nama_efek'],
                        'mulai_kepemilikan'=> $p['mulai_kepemilikan'] ?? null,
                        'jumlah'           => $p['jumlah'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('member.create')
            ->with('success', 'Pendaftaran member berhasil dikirim. Menunggu persetujuan admin.');
    }
}
