<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\AdvisorClientRequest;
use App\Models\PerencanaanInvestasi;
use App\Models\User;
use App\Notifications\AdvisorConnectionRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function create()
    {
        $existingClientIds = User::where('advisor_id', auth()->id())->pluck('id');

        $pendingIds = AdvisorClientRequest::where('advisor_id', auth()->id())
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('client_id');

        $excludeIds = $existingClientIds->merge($pendingIds)->unique()->push(auth()->id());

        $users = User::where('role', 'user')
            ->whereNotIn('id', $excludeIds)
            ->with('memberProfile')
            ->orderBy('name')
            ->get();

        return view('advisor.client.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:users,id',
        ]);

        $client = User::findOrFail($validated['client_id']);

        if ($client->advisor_id || $client->role !== 'user') {
            return back()->with('error', 'User tidak tersedia untuk dijadikan klien.');
        }

        $exists = AdvisorClientRequest::where('advisor_id', auth()->id())
            ->where('client_id', $client->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Permintaan koneksi sudah pernah dikirim ke user ini.');
        }

        AdvisorClientRequest::create([
            'advisor_id' => auth()->id(),
            'client_id'  => $client->id,
            'status'     => 'pending',
        ]);

        $client->notify(new AdvisorConnectionRequest(auth()->user()));

        return redirect()->route('user.clients.index')->with('success', 'Permintaan koneksi berhasil dikirim. Menunggu persetujuan klien.');
    }

    public function show(User $client)
    {
        if ($client->advisor_id !== auth()->id()) abort(403);

        $client->load('memberProfile');

        $perencanaan = PerencanaanInvestasi::where('user_id', $client->id)
            ->latest()
            ->paginate(10);

        return view('advisor.client.show', compact('client', 'perencanaan'));
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
