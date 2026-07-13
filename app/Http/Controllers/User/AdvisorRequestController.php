<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdvisorClientRequest;
use App\Notifications\AdvisorConnectionResponse;
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

    public function approve(AdvisorClientRequest $request)
    {
        if ($request->client_id !== auth()->id()) abort(403);
        if ($request->status !== 'pending') return back()->with('error', 'Permintaan sudah diproses.');

        $advisor = $request->advisor;

        $request->update(['status' => 'approved']);
        auth()->user()->update(['advisor_id' => $request->advisor_id]);

        AdvisorClientRequest::where('client_id', auth()->id())
            ->where('id', '!=', $request->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        if ($advisor) {
            $advisor->notify(new AdvisorConnectionResponse(auth()->user(), 'approved'));
        }

        return back()->with('success', 'Permintaan advisor berhasil disetujui.');
    }

    public function reject(AdvisorClientRequest $request)
    {
        if ($request->client_id !== auth()->id()) abort(403);
        if ($request->status !== 'pending') return back()->with('error', 'Permintaan sudah diproses.');

        $advisor = $request->advisor;

        $request->update(['status' => 'rejected']);

        if ($advisor) {
            $advisor->notify(new AdvisorConnectionResponse(auth()->user(), 'rejected'));
        }

        return back()->with('success', 'Permintaan advisor ditolak.');
    }
}
