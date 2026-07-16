<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PerencanaanInvestasi;
use App\Support\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdvisorController extends Controller
{
    public function index()
    {
        $advisors = User::where('role', 'advisor')->withCount('clients')->latest()->paginate(20);
        return view('admin.advisor.index', compact('advisors'));
    }

    public function create()
    {
        return view('admin.advisor.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone'    => 'nullable|string|max:20',
            'is_active'=> 'boolean',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => 'advisor',
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log('Tambah Advisor', "Advisor {$user->name} telah ditambahkan", 'success');

        return redirect()->route('admin.advisors.index')->with('success', 'Advisor berhasil ditambahkan.');
    }

    public function edit(User $advisor)
    {
        if ($advisor->role !== 'advisor') abort(404);
        return view('admin.advisor.edit', compact('advisor'));
    }

    public function update(Request $request, User $advisor)
    {
        if ($advisor->role !== 'advisor') abort(404);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $advisor->id,
            'password'  => 'nullable|min:8|confirmed',
            'phone'     => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($validated['password']) {
            $data['password'] = Hash::make($validated['password']);
        }

        $advisor->update($data);
 
         ActivityLogger::log('Update Advisor', "Advisor {$advisor->name} telah diupdate", 'success');
 
         return redirect()->route('admin.advisors.index')->with('success', 'Advisor berhasil diupdate.');
     }
 
     public function approve(User $advisor)
     {
         if ($advisor->role !== 'advisor') abort(404);
 
         $advisor->update(['is_active' => true]);
 
         ActivityLogger::log('Approve Advisor', "Pendaftaran advisor {$advisor->name} telah disetujui", 'success');
 
         return redirect()->route('admin.advisors.index')->with('success', "Advisor {$advisor->name} berhasil disetujui.");
     }
 
     public function clients(User $advisor)
    {
        if ($advisor->role !== 'advisor') abort(404);

        $clients = User::where('advisor_id', $advisor->id)
            ->with('memberProfile')
            ->withCount('perencanaanInvestasi')
            ->latest()
            ->paginate(20);

        return view('admin.advisor.clients', compact('advisor', 'clients'));
    }

    public function destroy(User $advisor)
    {
        if ($advisor->role !== 'advisor') abort(404);

        User::where('advisor_id', $advisor->id)->update(['advisor_id' => null]);

        $advisor->delete();

        ActivityLogger::log('Hapus Advisor', "Advisor {$advisor->name} telah dihapus", 'success');

        return redirect()->route('admin.advisors.index')->with('success', 'Advisor berhasil dihapus.');
    }

    public function planDetail(User $advisor, PerencanaanInvestasi $plan)
    {
        // Admin can view any client's plan
        $plan->load('portofolioItems', 'progressCheckins', 'user.memberProfile');
        return view('admin.advisor.clients.plan-detail', compact('advisor', 'plan'));
    }

    public function planPdf(User $advisor, PerencanaanInvestasi $plan)
    {
        $plan->load('portofolioItems', 'progressCheckins', 'user.memberProfile');
        $pdf = Pdf::loadView('perencanaan-investasi.pdf', compact('plan'));
        return $pdf->download('Perencanaan_Investasi_' . str_replace(' ', '_', $plan->kategori_perencanaan) . '.pdf');
    }
}
