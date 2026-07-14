@extends('layouts.admin')

@section('title', 'Admin Dashboard — InvestaPremier')

@section('content')
<style>
.stat-card{border-radius:16px;padding:22px 24px;position:relative;overflow:hidden;color:#fff}
.stat-card .sc-label{font-size:11px;font-weight:600;opacity:.8;text-transform:uppercase;letter-spacing:.07em;margin-bottom:8px}
.stat-card .sc-val{font-size:32px;font-weight:800;letter-spacing:-.025em;line-height:1}
.stat-card .sc-sub{font-size:12px;opacity:.75;margin-top:5px}
.stat-card .sc-ico{position:absolute;right:-8px;bottom:-8px;opacity:.12}
.stat-card .sc-ico svg{width:72px;height:72px;stroke:currentColor;fill:none;stroke-width:1.2}
.g-green{background:linear-gradient(135deg,#16a34a 0%,#22c55e 100%);box-shadow:0 8px 24px rgba(22,163,74,.28)}
.g-navy{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);box-shadow:0 8px 24px rgba(15,23,42,.22)}
.g-amber{background:linear-gradient(135deg,#b45309 0%,#f59e0b 100%);box-shadow:0 8px 24px rgba(180,83,9,.22)}
.g-rose{background:linear-gradient(135deg,#be123c 0%,#f43f5e 100%);box-shadow:0 8px 24px rgba(190,18,60,.22)}

.sec-hd{font-size:15px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:8px}
.sec-hd svg{width:18px;height:18px;stroke:#16a34a;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

.badge-role{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600}
.badge-admin{background:#fef9c3;color:#854d0e}
.badge-user{background:#dcfce7;color:#15803d}

.status-badge{display:inline-flex;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600}
.s-approved{background:#dcfce7;color:#15803d}
.s-pending{background:#fef9c3;color:#854d0e}
.s-rejected{background:#fee2e2;color:#b91c1c}
</style>

{{-- Header --}}
<div class="flex items-center justify-between mb-7">
    <div>
        <h1 class="text-2xl font-bold text-gray-900" style="letter-spacing:-.02em">Admin Dashboard</h1>
        <p class="text-gray-500 text-sm mt-1">Overview sistem InvestaPremier</p>
    </div>
    <a href="{{ route('admin.members.index') }}"
       class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-9px text-sm font-semibold text-white transition"
       style="background:linear-gradient(135deg,#16a34a,#22c55e);border-radius:9px;box-shadow:0 2px 10px rgba(22,163,74,.28)">
        <svg style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Kelola Member
    </a>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
    <div class="stat-card g-green">
        <div class="sc-label">Total Users</div>
        <div class="sc-val">{{ $totalUsers }}</div>
        <div class="sc-sub">Semua pengguna terdaftar</div>
        <div class="sc-ico"><svg viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
    </div>
    <div class="stat-card g-navy">
        <div class="sc-label">Total Members</div>
        <div class="sc-val">{{ $totalMembers }}</div>
        <div class="sc-sub">User yang di-upgrade</div>
        <div class="sc-ico"><svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
    </div>
    <div class="stat-card g-amber">
        <div class="sc-label">Administrator</div>
        <div class="sc-val">{{ $totalAdmins }}</div>
        <div class="sc-sub">Admin platform aktif</div>
        <div class="sc-ico"><svg viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
    </div>
    <div class="stat-card g-rose">
        <div class="sc-label">Pending Member</div>
        <div class="sc-val">{{ $pendingMembers }}</div>
        <div class="sc-sub">Butuh review admin</div>
        <div class="sc-ico"><svg viewBox="0 0 24 24"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
    </div>
</div>

{{-- Main grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Recent users table --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
             style="background:linear-gradient(135deg,#0f172a,#1a2744)">
            <h2 class="sec-hd" style="color:#fff">
                <svg style="stroke:#4ade80" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Recent Users
            </h2>
            <span class="text-xs font-semibold" style="color:rgba(255,255,255,.5)">{{ $totalUsers }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left border-b border-gray-100">
                        <th class="px-5 py-3 text-xs font-600 text-gray-500 uppercase tracking-wide font-semibold">User</th>
                        <th class="px-5 py-3 text-xs font-600 text-gray-500 uppercase tracking-wide font-semibold">Email</th>
                        <th class="px-5 py-3 text-xs font-600 text-gray-500 uppercase tracking-wide font-semibold">Role</th>
                        <th class="px-5 py-3 text-xs font-600 text-gray-500 uppercase tracking-wide font-semibold">Status</th>
                        <th class="px-5 py-3 text-xs font-600 text-gray-500 uppercase tracking-wide font-semibold">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentUsers as $u)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full grid place-items-center text-xs font-bold uppercase text-white flex-shrink-0"
                                     style="background:{{ $u->role === 'admin' ? 'linear-gradient(135deg,#b45309,#f59e0b)' : 'linear-gradient(135deg,#16a34a,#22c55e)' }}">
                                    {{ substr($u->name,0,2) }}
                                </div>
                                <span class="font-semibold text-gray-900 text-sm">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 text-sm">{{ $u->email }}</td>
                        <td class="px-5 py-3.5">
                            <span class="badge-role {{ $u->role === 'admin' ? 'badge-admin' : 'badge-user' }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:currentColor"></span>
                                {{ $u->role }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($u->isMember())
                                <span class="status-badge s-approved">✓ Member</span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $u->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Platform Stats --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
        <div class="sec-hd mb-5">
            <svg viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Platform Stats
        </div>
        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="text-gray-700">Member Rate</span>
                    <span class="font-bold text-gray-900">{{ $totalUsers > 0 ? round(($totalMembers/$totalUsers)*100) : 0 }}%</span>
                </div>
                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full" style="background:linear-gradient(90deg,#16a34a,#22c55e);width:{{ $totalUsers > 0 ? ($totalMembers/$totalUsers)*100 : 0 }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="text-gray-700">Admin Ratio</span>
                    <span class="font-bold text-gray-900">{{ $totalUsers > 0 ? round(($totalAdmins/$totalUsers)*100) : 0 }}%</span>
                </div>
                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full" style="background:linear-gradient(90deg,#b45309,#f59e0b);width:{{ $totalUsers > 0 ? ($totalAdmins/$totalUsers)*100 : 0 }}%"></div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 mt-5 pt-5 border-t border-gray-100">
            <div class="rounded-xl p-3 text-center" style="background:#f0fdf4">
                <div class="text-2xl font-extrabold text-green-600">{{ $totalUsers }}</div>
                <div class="text-xs text-gray-500 mt-1">Total Users</div>
            </div>
            <div class="rounded-xl p-3 text-center" style="background:#ecfdf5">
                <div class="text-2xl font-extrabold text-green-700">{{ $totalMembers }}</div>
                <div class="text-xs text-gray-500 mt-1">Members</div>
            </div>
        </div>
    </div>
</div>

{{-- Second row --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-7">
    {{-- Pendaftaran Member --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="sec-hd">
                <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Pendaftaran Member Terbaru
            </div>
            <a href="{{ route('admin.members.index') }}"
               class="text-xs font-semibold text-green-600 hover:text-green-700 hover:underline">Lihat semua</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentApplicants as $member)
            <div class="px-6 py-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full text-white text-xs font-bold grid place-items-center flex-shrink-0"
                         style="background:linear-gradient(135deg,#16a34a,#22c55e)">
                        {{ substr($member->user->name,0,2) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 text-sm">{{ $member->user->name }}</div>
                        <div class="text-xs text-gray-400">{{ $member->user->email }}</div>
                    </div>
                </div>
                <span class="status-badge {{ match($member->status) { 'approved'=>'s-approved','rejected'=>'s-rejected',default=>'s-pending' } }}">
                    {{ ucfirst($member->status) }}
                </span>
            </div>
            @empty
            <div class="px-6 py-10 text-center text-sm text-gray-400">Belum ada data pendaftaran.</div>
            @endforelse
        </div>
    </div>

    {{-- Distribusi Profil Investasi --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="sec-hd">
                <svg viewBox="0 0 24 24"><path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                Distribusi Profil Investasi
            </div>
            <p class="text-xs text-gray-400 mt-1 ml-7">Ringkasan hasil kuis pengguna</p>
        </div>
        <div class="p-6 space-y-4">
            @foreach($profileStats as $item)
            @php $pct = $totalQuizResults > 0 ? round(($item['total']/$totalQuizResults)*100) : 0; @endphp
            <div>
                <div class="flex items-center justify-between text-sm mb-1.5">
                    <span class="font-medium text-gray-700">{{ $item['profile'] }}</span>
                    <span class="text-gray-500 text-xs">{{ $item['total'] }} user ({{ $pct }}%)</span>
                </div>
                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full" style="background:linear-gradient(90deg,#16a34a,#22c55e);width:{{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Portfolio Summary Section --}}
<div class="flex items-center justify-between mb-5 mt-8">
    <div class="sec-hd">
        <svg viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        Portfolio Summary
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex-shrink-0 grid place-items-center" style="background:#f0fdf4">
            <svg style="width:22px;height:22px;stroke:#16a34a;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-gray-900">{{ $totalAum > 0 ? 'Rp ' . number_format($totalAum / 1000000, 1) . 'M' : 'Rp 0' }}</div>
            <div class="text-xs text-gray-400 mt-0.5 uppercase tracking-wide font-semibold">Total AUM</div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex-shrink-0 grid place-items-center" style="background:#f0fdf4">
            <svg style="width:22px;height:22px;stroke:#16a34a;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/></svg>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-gray-900">{{ $totalPortfolioItems }}</div>
            <div class="text-xs text-gray-400 mt-0.5 uppercase tracking-wide font-semibold">Total Portfolio Items</div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex-shrink-0 grid place-items-center" style="background:#f0fdf4">
            <svg style="width:22px;height:22px;stroke:#16a34a;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-gray-900">{{ $usersWithPortfolio }}</div>
            <div class="text-xs text-gray-400 mt-0.5 uppercase tracking-wide font-semibold">Users with Portfolio</div>
        </div>
    </div>
</div>

{{-- Portfolio Distribution & Top Portfolios --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="sec-hd">
                <svg viewBox="0 0 24 24"><path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                Distribusi Aset Portfolio
            </div>
            <p class="text-xs text-gray-400 mt-1 ml-7">Seluruh portfolio user</p>
        </div>
        <div class="p-6 space-y-4">
            @forelse($allJenis as $jenis => $total)
            @php $pct = $totalAum > 0 ? round(($total / $totalAum) * 100) : 0; @endphp
            <div>
                <div class="flex items-center justify-between text-sm mb-1.5">
                    <span class="font-medium text-gray-700">{{ $jenis }}</span>
                    <span class="text-gray-500 text-xs">Rp {{ number_format($total / 1000000, 1) }}M ({{ $pct }}%)</span>
                </div>
                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full" style="background:linear-gradient(90deg,#16a34a,#22c55e);width:{{ $pct }}%"></div>
                </div>
            </div>
            @empty
            <div class="text-center py-6 text-sm text-gray-400">Belum ada data portfolio.</div>
            @endforelse
        </div>
    </div>

    {{-- Top Portfolios --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="sec-hd">
                <svg viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Top Portfolios
            </div>
            <p class="text-xs text-gray-400 mt-1 ml-7">5 portfolio terbesar</p>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($topPortfolios as $i => $p)
            <div class="px-6 py-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full text-white text-xs font-bold grid place-items-center flex-shrink-0"
                         style="background:linear-gradient(135deg,#16a34a,#22c55e)">
                        {{ substr($p['name'], 0, 2) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 text-sm">{{ $p['name'] }}</div>
                        <div class="text-xs text-gray-400">Portfolio #{{ $i + 1 }}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-gray-900">Rp {{ number_format($p['total'] / 1000000, 1) }}M</div>
                </div>
            </div>
            @empty
            <div class="px-6 py-10 text-center text-sm text-gray-400">Belum ada data portfolio.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Analisa Section --}}
<div class="flex items-center justify-between mb-5 mt-8">
    <div class="sec-hd">
        <svg viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Analisa Reksa Dana
    </div>
    <a href="{{ route('admin.analisa.index') }}" class="text-sm font-semibold text-green-600 hover:underline">Lihat Semua →</a>
</div>

{{-- Analisa stat cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex-shrink-0 grid place-items-center" style="background:#f0fdf4">
            <svg style="width:22px;height:22px;stroke:#16a34a;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/></svg>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-gray-900">{{ $totalAnalisa }}</div>
            <div class="text-xs text-gray-400 mt-0.5 uppercase tracking-wide font-semibold">Total Analisa</div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex-shrink-0 grid place-items-center" style="background:#fefce8">
            <svg style="width:22px;height:22px;stroke:#ca8a04;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-amber-600">{{ $analisaPending }}</div>
            <div class="text-xs text-gray-400 mt-0.5 uppercase tracking-wide font-semibold">Menunggu Review</div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex-shrink-0 grid place-items-center" style="background:#f0fdf4">
            <svg style="width:22px;height:22px;stroke:#16a34a;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-green-600">{{ $analisaReviewed }}</div>
            <div class="text-xs text-gray-400 mt-0.5 uppercase tracking-wide font-semibold">Sudah Direview</div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Submission per Bulan (6 Bulan Terakhir)</h3>
        <canvas id="chartAnalisaBulan" height="180"></canvas>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Distribusi Jenis Reksa Dana</h3>
        @if($analisaPerJenis->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">Belum ada data.</p>
        @else
            <canvas id="chartAnalisaJenis" height="180"></canvas>
        @endif
    </div>
</div>

{{-- Analisa table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="sec-hd">
            <svg viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Submission Terbaru
        </div>
        <a href="{{ route('admin.analisa.index', ['status'=>'submitted']) }}" class="text-xs font-semibold text-green-600 hover:underline">Lihat yang pending →</a>
    </div>
    @if($recentAnalisa->isEmpty())
        <div class="p-8 text-center text-sm text-gray-400">Belum ada submission analisa.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">User</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Reksa Dana</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jenis</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($recentAnalisa as $a)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5 text-gray-700 font-medium">{{ $a->user->name }}</td>
                    <td class="px-5 py-3.5 font-semibold text-gray-900">{{ $a->nama_reksa_dana }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $a->jenis_reksa_dana }}</td>
                    <td class="px-5 py-3.5">
                        @php $sc = match($a->status) { 'submitted'=>'s-pending','reviewed'=>'s-approved',default=>'status-badge' }; @endphp
                        <span class="status-badge {{ $sc }}">
                            {{ match($a->status) { 'submitted'=>'Pending','reviewed'=>'Reviewed',default=>'Draft' } }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $a->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.analisa.show', $a) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 hover:text-green-700 hover:underline">
                            Detail
                            <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Poppins', sans-serif";
Chart.defaults.font.size = 12;

new Chart(document.getElementById('chartAnalisaBulan'), {
    type: 'bar',
    data: {
        labels: {!! $analisaPerBulan->keys()->map(fn($b) => \Carbon\Carbon::parse($b.'-01')->format('M Y')) !!},
        datasets: [{
            label: 'Submission',
            data: {!! $analisaPerBulan->values() !!},
            backgroundColor: 'rgba(22,163,74,.85)',
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } }
        }
    }
});

@if($analisaPerJenis->isNotEmpty())
new Chart(document.getElementById('chartAnalisaJenis'), {
    type: 'doughnut',
    data: {
        labels: {!! $analisaPerJenis->keys() !!},
        datasets: [{
            data: {!! $analisaPerJenis->values() !!},
            backgroundColor: ['#16a34a','#22c55e','#4ade80','#86efac','#bbf7d0'],
            borderWidth: 0,
        }]
    },
    options: {
        plugins: { legend: { position: 'right' } },
        cutout: '58%'
    }
});
@endif
</script>
@endpush
@endsection
