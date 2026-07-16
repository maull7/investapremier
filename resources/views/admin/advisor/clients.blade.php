@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('admin.advisors.index') }}" class="text-sm text-muted hover:text-primary">&larr; Kembali ke Daftar Advisor</a>
        <h1 class="page-title mt-2">Daftar Klien: {{ $advisor->name }}</h1>
        <p class="page-sub">Total {{ $clients->total() }} klien</p>
    </div>

    <div class="table-card">
        @if ($clients->isEmpty())
            <div class="p-12 text-center text-muted text-sm">Advisor ini belum memiliki klien.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Nama Klien</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Email</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Usia</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Pekerjaan</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Profil Risiko</th>
                            <th class="text-right px-5 py-3 font-semibold text-primary">Total Aset</th>
                            <th class="text-right px-5 py-3 font-semibold text-primary">Target Aset</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($clients as $c)
                            @php
                                $profile = $c->memberProfile;
                                $usia = $profile?->tanggal_lahir ? $profile->tanggal_lahir->age : null;
                                $latestPlan = $c->perencanaanInvestasi()->latest()->first();
                            @endphp
                            <tr class="hover:bg-[#f8fafc] transition">
                                <td class="px-5 py-3.5 font-medium text-primary">{{ $c->name }}</td>
                                <td class="px-5 py-3.5 text-muted">{{ $c->email }}</td>
                                <td class="px-5 py-3.5 text-muted">{{ $usia ? $usia . ' thn' : '—' }}</td>
                                <td class="px-5 py-3.5 text-muted">{{ $profile?->pekerjaan ?? '—' }}</td>
                                <td class="px-5 py-3.5">{{ $profile?->profil_risiko ?? ($latestPlan?->profil_risiko ?? '—') }}</td>
                                <td class="px-5 py-3.5 text-right font-semibold">{{ $latestPlan?->dana_tersedia ? 'Rp ' . number_format($latestPlan->dana_tersedia, 0, ',', '.') : '—' }}</td>
                                <td class="px-5 py-3.5 text-right font-semibold text-accent">{{ $latestPlan?->kebutuhan_dana ? 'Rp ' . number_format($latestPlan->kebutuhan_dana, 0, ',', '.') : '—' }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    @if ($latestPlan)
                                        <a href="{{ route('admin.advisors.clients.plan.show', ['advisor' => $advisor, 'plan' => $latestPlan]) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-accent border border-accent/30 rounded-lg hover:bg-accent/5 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Detail
                                        </a>
                                    @else
                                        <span class="text-muted text-xs">Belum ada rencana</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-line">{{ $clients->links() }}</div>
        @endif
    </div>
</div>
@endsection