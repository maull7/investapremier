<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\AdvisorClientRequest;
use App\Models\PerencanaanInvestasi;
use App\Models\User;
use App\Notifications\AdvisorConnectionResponse;
use App\Services\PortfolioAggregationService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'terdaftar');

        $clients = collect();
        $pendingRequests = collect();
        $rejectedRequests = collect();

        if ($tab === 'terdaftar') {
            $clients = User::where('advisor_id', auth()->id())
                ->with('memberProfile')
                ->withCount('perencanaanInvestasi')
                ->latest()
                ->paginate(20);
        } elseif ($tab === 'tertunda') {
            $pendingRequests = AdvisorClientRequest::where('advisor_id', auth()->id())
                ->where('status', 'pending')
                ->with('client.memberProfile')
                ->latest()
                ->paginate(20);
        } elseif ($tab === 'ditolak') {
            $rejectedRequests = AdvisorClientRequest::where('advisor_id', auth()->id())
                ->where('status', 'rejected')
                ->with('client.memberProfile')
                ->latest()
                ->paginate(20);
        }

        return view('advisor.client.index', compact('tab', 'clients', 'pendingRequests', 'rejectedRequests'));
    }

    public function approve(AdvisorClientRequest $request)
    {
        if ($request->advisor_id !== auth()->id()) abort(403);
        if ($request->status !== 'pending') return back()->with('error', 'Permintaan sudah diproses.');

        $client = $request->client;

        $request->update(['status' => 'approved']);
        $client->update(['advisor_id' => $request->advisor_id]);

        AdvisorClientRequest::where('client_id', $client->id)
            ->where('id', '!=', $request->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        if ($client) {
            $client->notify(new AdvisorConnectionResponse(auth()->user(), 'approved'));
        }

        return back()->with('success', 'Permintaan koneksi dari ' . ($client->name ?? 'User') . ' berhasil disetujui.');
    }

    public function reject(AdvisorClientRequest $request)
    {
        if ($request->advisor_id !== auth()->id()) abort(403);
        if ($request->status !== 'pending') return back()->with('error', 'Permintaan sudah diproses.');

        $client = $request->client;

        $request->update(['status' => 'rejected']);

        if ($client) {
            $client->notify(new AdvisorConnectionResponse(auth()->user(), 'rejected'));
        }

        return back()->with('success', 'Permintaan koneksi ditolak.');
    }

    public function show(User $client)
    {
        if ($client->advisor_id !== auth()->id()) abort(403);

        $client->load('memberProfile');

        $perencanaan = PerencanaanInvestasi::where('user_id', $client->id)
            ->latest()
            ->paginate(10);

        $portfolioSummary = app(PortfolioAggregationService::class)->aggregate($client);

        return view('advisor.client.show', compact('client', 'perencanaan', 'portfolioSummary'));
    }

    public function destroy(User $client)
    {
        if ($client->advisor_id !== auth()->id()) abort(403);

        AdvisorClientRequest::where('advisor_id', auth()->id())
            ->where('client_id', $client->id)
            ->delete();

        $client->update(['advisor_id' => null]);

        return redirect()->route('user.clients.index')->with('success', 'Klien berhasil dihapus dari daftar.');
    }
}
