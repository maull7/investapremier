<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberProfile;
use App\Models\StockPrice;
use App\Models\User;

class MemberController extends Controller
{
    public function index()
    {
        $members = MemberProfile::with('user')->latest()->paginate(15);
        return view('admin.members.index', compact('members'));
    }

    public function show(MemberProfile $member)
    {
        $member->load(['user', 'portfolios']);

        // Ambil harga terbaru (T-1) untuk semua efek di portfolio
        $kodeEfeks = $member->portfolios
            ->pluck('nama_efek')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $stockPrices = StockPrice::hargaTerbaruBulk($kodeEfeks);

        return view('admin.members.show', compact('member', 'stockPrices'));
    }

    public function approve(MemberProfile $member)
    {
        $member->update(['status' => 'approved']);
        $member->user->update(['is_member' => true]);
        return back()->with('success', "Pendaftaran {$member->user->name} telah disetujui.");
    }

    public function reject(MemberProfile $member)
    {
        $member->update(['status' => 'rejected']);
        $member->user->update(['is_member' => false]);
        return back()->with('success', "Pendaftaran {$member->user->name} telah ditolak.");
    }
}
