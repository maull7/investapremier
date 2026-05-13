@extends('layouts.admin')

@section('title', 'Admin Dashboard - InvestaPremier')

@section('content')
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <div
                class="w-10 h-10 rounded-xl bg-gradient-to-br from-accent to-accent-light text-white grid place-items-center text-lg shadow-lg shadow-accent/20">
                ◔</div>
            <div>
                <h1 class="text-2xl font-bold text-primary">Admin Dashboard</h1>
                <p class="text-muted text-sm">Overview sistem InvestaPremier</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-5 mb-8">
        <div class="relative bg-white rounded-2xl border border-line p-6 shadow-sm overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mt-8 -mr-8"></div>
            <div class="absolute bottom-0 left-0 w-20 h-20 bg-accent/5 rounded-full -mb-6 -ml-6"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 text-muted text-xs uppercase tracking-wide font-semibold mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                        Total Users
                    </div>
                    <div class="text-3xl font-extrabold text-primary mt-1">{{ $totalUsers }}</div>
                    <div class="text-xs text-muted mt-1">Semua pengguna terdaftar</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-primary/10 grid place-items-center text-primary shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="relative bg-white rounded-2xl border border-line p-6 shadow-sm overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-accent/10 rounded-full -mt-8 -mr-8"></div>
            <div class="absolute bottom-0 left-0 w-20 h-20 bg-accent/5 rounded-full -mb-6 -ml-6"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 text-muted text-xs uppercase tracking-wide font-semibold mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Total Members
                    </div>
                    <div class="text-3xl font-extrabold text-accent mt-1">{{ $totalMembers }}</div>
                    <div class="text-xs text-muted mt-1">User yang sudah di-upgrade</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-accent/10 grid place-items-center text-accent shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="relative bg-white rounded-2xl border border-line p-6 shadow-sm overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gold/10 rounded-full -mt-8 -mr-8"></div>
            <div class="absolute bottom-0 left-0 w-20 h-20 bg-amber-400/5 rounded-full -mb-6 -ml-6"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 text-muted text-xs uppercase tracking-wide font-semibold mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Admin
                    </div>
                    <div class="text-3xl font-extrabold text-gold mt-1">{{ $totalAdmins }}</div>
                    <div class="text-xs text-muted mt-1">Administrator platform</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gold/10 grid place-items-center text-gold shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="relative bg-white rounded-2xl border border-line p-6 shadow-sm overflow-hidden">
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-muted text-xs uppercase tracking-wide font-semibold mb-1">Pending Member</div>
                    <div class="text-3xl font-extrabold text-amber-600 mt-1">{{ $pendingMembers }}</div>
                    <div class="text-xs text-muted mt-1">Butuh review admin</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-100 grid place-items-center text-amber-600 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M3.055 11a9 9 0 1117.89 0 9 9 0 01-17.89 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
            <div
                class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-primary to-primary-light">
                <h2 class="font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                    Recent Users
                </h2>
                <span class="text-xs text-white/60 font-medium">{{ $totalUsers }} total</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                            <th class="px-6 py-3.5 font-semibold">User</th>
                            <th class="px-6 py-3.5 font-semibold">Email</th>
                            <th class="px-6 py-3.5 font-semibold">Role</th>
                            <th class="px-6 py-3.5 font-semibold">Status</th>
                            <th class="px-6 py-3.5 font-semibold">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($recentUsers as $u)
                            <tr class="hover:bg-[#f8fafc] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full {{ $u->role === 'admin' ? 'bg-gold/20 text-gold' : 'bg-accent/20 text-accent' }} grid place-items-center text-xs font-bold uppercase">
                                            {{ substr($u->name, 0, 2) }}</div>
                                        <span class="font-semibold text-primary">{{ $u->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-muted">{{ $u->email }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $u->role === 'admin' ? 'bg-gold/10 text-gold' : 'bg-accent/10 text-accent' }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $u->role === 'admin' ? 'bg-gold' : 'bg-accent' }}"></span>
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($u->isMember())
                                        <span class="inline-flex items-center gap-1 text-accent font-semibold text-xs">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Member
                                        </span>
                                    @else
                                        <span class="text-muted text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-muted text-xs">{{ $u->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-line p-6 shadow-sm">
            <h2 class="font-bold text-primary flex items-center gap-2 mb-5">
                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Platform Stats
            </h2>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm text-primary mb-1.5"><span>User Rate</span><span
                            class="font-semibold">{{ $totalUsers > 0 ? round(($totalMembers / $totalUsers) * 100) : 0 }}%</span>
                    </div>
                    <div class="h-2 bg-[#e2e8f0] rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-accent to-accent-light rounded-full"
                            style="width: {{ $totalUsers > 0 ? ($totalMembers / $totalUsers) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm text-primary mb-1.5"><span>Admin Ratio</span><span
                            class="font-semibold">{{ $totalUsers > 0 ? round(($totalAdmins / $totalUsers) * 100) : 0 }}%</span>
                    </div>
                    <div class="h-2 bg-[#e2e8f0] rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-gold to-amber-400 rounded-full"
                            style="width: {{ $totalUsers > 0 ? ($totalAdmins / $totalUsers) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="pt-4 border-t border-line mt-4">
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="bg-[#f8fafc] rounded-xl p-3">
                            <div class="text-lg font-extrabold text-primary">{{ $totalUsers }}</div>
                            <div class="text-xs text-muted">Total Users</div>
                        </div>
                        <div class="bg-[#f8fafc] rounded-xl p-3">
                            <div class="text-lg font-extrabold text-accent">{{ $totalMembers }}</div>
                            <div class="text-xs text-muted">Members</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-line flex items-center justify-between">
                <h2 class="font-bold text-primary">Pendaftaran Member Terbaru</h2>
                <a href="{{ route('admin.members.index') }}"
                    class="text-xs font-semibold text-accent hover:underline">Lihat semua</a>
            </div>
            <div class="divide-y divide-line">
                @forelse ($recentApplicants as $member)
                    <div class="px-6 py-4 flex items-center justify-between gap-3">
                        <div>
                            <div class="font-semibold text-primary">{{ $member->user->name }}</div>
                            <div class="text-xs text-muted">{{ $member->user->email }}</div>
                        </div>
                        <span
                            class="text-xs px-2.5 py-1 rounded-full font-semibold
                        {{ $member->status === 'approved' ? 'bg-green-100 text-green-700' : ($member->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ ucfirst($member->status) }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-sm text-muted">Belum ada data pendaftaran.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-line">
                <h2 class="font-bold text-primary">Distribusi Profil Investasi</h2>
                <p class="text-xs text-muted mt-1">Ringkasan hasil kuis pengguna.</p>
            </div>
            <div class="p-6 space-y-4">
                @foreach ($profileStats as $item)
                    @php
                        $percentage = $totalQuizResults > 0 ? round(($item['total'] / $totalQuizResults) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1.5">
                            <span class="font-medium text-primary">{{ $item['profile'] }}</span>
                            <span class="text-muted">{{ $item['total'] }} user ({{ $percentage }}%)</span>
                        </div>
                        <div class="h-2.5 bg-[#e2e8f0] rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-accent to-accent-light rounded-full"
                                style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== SECTION ANALISA ===== --}}
    <div class="mt-8 mb-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-primary/10 grid place-items-center text-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-primary">Analisa Reksa Dana</h2>
            <p class="text-xs text-muted">Statistik submission analisa dari user</p>
        </div>
        <a href="{{ route('admin.analisa.index') }}" class="ml-auto text-sm text-primary hover:underline">Lihat Semua →</a>
    </div>

    {{-- Stat cards analisa --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <div class="bg-white rounded-2xl border border-line p-6 shadow-sm">
            <p class="text-xs text-muted uppercase tracking-wide font-semibold mb-1">Total Analisa</p>
            <p class="text-3xl font-extrabold text-primary">{{ $totalAnalisa }}</p>
            <p class="text-xs text-muted mt-1">Semua submission</p>
        </div>
        <div class="bg-white rounded-2xl border border-line p-6 shadow-sm">
            <p class="text-xs text-muted uppercase tracking-wide font-semibold mb-1">Menunggu Review</p>
            <p class="text-3xl font-extrabold text-yellow-600">{{ $analisaPending }}</p>
            <p class="text-xs text-muted mt-1">Perlu ditindaklanjuti</p>
        </div>
        <div class="bg-white rounded-2xl border border-line p-6 shadow-sm">
            <p class="text-xs text-muted uppercase tracking-wide font-semibold mb-1">Sudah Direview</p>
            <p class="text-3xl font-extrabold text-green-600">{{ $analisaReviewed }}</p>
            <p class="text-xs text-muted mt-1">Selesai diproses</p>
        </div>
    </div>

    {{-- Chart + Tabel --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Chart submission per bulan --}}
        <div class="bg-white rounded-2xl border border-line p-6 shadow-sm">
            <h3 class="font-semibold text-primary mb-4 text-sm">Submission per Bulan (6 Bulan Terakhir)</h3>
            <canvas id="chartAnalisaBulan" height="180"></canvas>
        </div>

        {{-- Chart distribusi jenis RD --}}
        <div class="bg-white rounded-2xl border border-line p-6 shadow-sm">
            <h3 class="font-semibold text-primary mb-4 text-sm">Distribusi Jenis Reksa Dana</h3>
            @if($analisaPerJenis->isEmpty())
                <p class="text-sm text-muted text-center py-8">Belum ada data.</p>
            @else
                <canvas id="chartAnalisaJenis" height="180"></canvas>
            @endif
        </div>
    </div>

    {{-- Tabel submission terbaru --}}
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <h3 class="font-semibold text-primary text-sm">Submission Terbaru</h3>
            <a href="{{ route('admin.analisa.index', ['status' => 'submitted']) }}" class="text-xs text-primary hover:underline">Lihat yang pending →</a>
        </div>
        @if($recentAnalisa->isEmpty())
            <div class="p-8 text-center text-sm text-muted">Belum ada submission analisa.</div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-[#f8fafc] border-b border-line">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-primary text-xs">User</th>
                    <th class="text-left px-5 py-3 font-semibold text-primary text-xs">Reksa Dana</th>
                    <th class="text-left px-5 py-3 font-semibold text-primary text-xs">Jenis</th>
                    <th class="text-left px-5 py-3 font-semibold text-primary text-xs">Status</th>
                    <th class="text-left px-5 py-3 font-semibold text-primary text-xs">Tanggal</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach($recentAnalisa as $a)
                <tr class="hover:bg-[#f8fafc] transition">
                    <td class="px-5 py-3">{{ $a->user->name }}</td>
                    <td class="px-5 py-3 font-medium">{{ $a->nama_reksa_dana }}</td>
                    <td class="px-5 py-3 text-muted">{{ $a->jenis_reksa_dana }}</td>
                    <td class="px-5 py-3">
                        @php $sc = match($a->status) { 'submitted' => 'bg-yellow-100 text-yellow-700', 'reviewed' => 'bg-green-100 text-green-700', default => 'bg-gray-100 text-gray-600' }; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">
                            {{ match($a->status) { 'submitted' => 'Pending', 'reviewed' => 'Reviewed', default => 'Draft' } }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-muted">{{ $a->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.analisa.show', $a) }}" class="text-xs text-primary hover:underline">Detail</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chartAnalisaBulan'), {
    type: 'bar',
    data: {
        labels: {!! $analisaPerBulan->keys()->map(fn($b) => \Carbon\Carbon::parse($b.'-01')->format('M Y')) !!},
        datasets: [{
            label: 'Submission',
            data: {!! $analisaPerBulan->values() !!},
            backgroundColor: '#2563eb',
            borderRadius: 4,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

@if($analisaPerJenis->isNotEmpty())
new Chart(document.getElementById('chartAnalisaJenis'), {
    type: 'doughnut',
    data: {
        labels: {!! $analisaPerJenis->keys() !!},
        datasets: [{
            data: {!! $analisaPerJenis->values() !!},
            backgroundColor: ['#1e3a5f','#2563eb','#3b82f6','#60a5fa'],
        }]
    },
    options: { plugins: { legend: { position: 'right' } }, cutout: '55%' }
});
@endif
</script>
@endpush

@endsection
