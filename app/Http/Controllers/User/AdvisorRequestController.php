<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdvisorClientRequest;
use App\Models\User;
use App\Notifications\AdvisorConnectionRequest;
use App\Notifications\UserBreakAdvisor;
use Illuminate\Http\Request;

class AdvisorRequestController extends Controller
{
    public function index()
    {
        $requests = AdvisorClientRequest::where('client_id', auth()->id())
            ->with('advisor')
            ->latest()
            ->get();

        $approvedAdvisor = auth()->user()->advisor;

        return view('advisor.request.index', compact('requests', 'approvedAdvisor'));
    }

    public function create()
    {
        $connectedAdvisorId = auth()->user()->advisor_id;

        $existingIds = AdvisorClientRequest::where('client_id', auth()->id())
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('advisor_id');

        $excludeIds = $existingIds->merge([$connectedAdvisorId, auth()->id()])->unique()->filter();

        $advisors = User::where('role', 'advisor')
            ->where('is_active', true)
            ->whereNotIn('id', $excludeIds)
            ->orderBy('name')
            ->get();

        return view('advisor.request.create', compact('advisors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'advisor_id' => 'required|exists:users,id',
        ]);

        $advisor = User::findOrFail($validated['advisor_id']);

        if ($advisor->role !== 'advisor' || !$advisor->is_active) {
            return back()->with('error', 'Advisor tidak tersedia.');
        }

        $exists = AdvisorClientRequest::where('client_id', auth()->id())
            ->where('advisor_id', $advisor->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Permintaan koneksi sudah pernah dikirim ke advisor ini.');
        }

        AdvisorClientRequest::create([
            'client_id'  => auth()->id(),
            'advisor_id' => $advisor->id,
            'status'     => 'pending',
        ]);

        $advisor->notify(new AdvisorConnectionRequest(auth()->user()));

        return redirect()->route('user.clients.requests.index')->with('success', 'Permintaan koneksi berhasil dikirim. Menunggu persetujuan advisor.');
    }

    public function cancel(AdvisorClientRequest $request)
    {
        if ($request->client_id !== auth()->id()) abort(403);
        if ($request->status !== 'pending') return back()->with('error', 'Permintaan sudah diproses.');

        $request->delete();

        return back()->with('success', 'Permintaan koneksi dibatalkan.');
    }
    public function breakConnection(AdvisorClientRequest $request)
    {
        if ($request->client_id !== auth()->id()) abort(403);
        if ($request->status !== 'approved') return back()->with('error', 'Tidak ada koneksi yang dapat diputus.');
        $advisor = User::findOrFail($request->advisor_id);

        $request->delete();
        $user = auth()->user();
        $user->advisor_id = null;
        $user->save();
        $advisor->notify(new UserBreakAdvisor(auth()->user()));
        return back()->with('success', 'Koneksi dengan advisor telah diputus.');
    }
}
