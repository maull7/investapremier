@extends('layouts.admin')

@section('title', 'Manajer Investasi - InvestaPremier')

@section('content')
<div x-data="{
    showImport: false,
    isSyncing: false,
    syncRunId: null,
    syncStep: 'queued',
    syncStepLabel: 'Menunggu worker...',
    syncProgress: 0,
    syncStatus: 'queued',
    syncMessage: '',
    syncErrors: [],
    syncPollTimer: null,

    syncPollUrl: '/admin/investment-managers/sync-pasardana/status',
    syncLabel: 'Sinkronisasi MI dari Pasardana',

    init() {
        const initialRunId = @json(session('sync_run_id'));
        if (initialRunId) {
            this.startPolling(initialRunId);
        }
    },

    async submitSync(event) {
        event.preventDefault();
        const form = event.target;
        this.syncPollUrl = form.dataset.pollUrl || '/admin/investment-managers/sync-pasardana/status';
        this.syncLabel = form.dataset.syncLabel || 'Sinkronisasi MI dari Pasardana';
        this.isSyncing = true;
        this.syncStep = 'queued';
        this.syncStepLabel = 'Mengirim job ke antrian...';
        this.syncProgress = 0;
        this.syncStatus = 'queued';
        this.syncErrors = [];
        this.syncMessage = '';

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (!data.run_id) throw new Error('Server tidak mengembalikan run_id');
            this.startPolling(data.run_id);
        } catch (e) {
            this.isSyncing = false;
            alert('Gagal memulai sync: ' + e.message);
        }
    },

    startPolling(runId) {
        this.syncRunId = runId;
        this.isSyncing = true;
        this.poll();
    },

    async poll() {
        if (!this.syncRunId) return;
        try {
            const res = await fetch(`${this.syncPollUrl}/${this.syncRunId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const d = await res.json();
            this.syncStep = d.current_step || 'queued';
            this.syncStepLabel = d.current_step_label || '...';
            this.syncProgress = d.progress_percent || 0;
            this.syncStatus = d.status;
            this.syncMessage = d.message || '';
            this.syncErrors = d.errors || [];

            if (d.is_terminal) {
                this.syncProgress = 100;
                setTimeout(() => { window.location.reload(); }, 2500);
                return;
            }
            this.syncPollTimer = setTimeout(() => this.poll(), 2000);
        } catch (e) {
            this.syncPollTimer = setTimeout(() => this.poll(), 5000);
        }
    },

    cancelSync() {
        if (this.syncPollTimer) clearTimeout(this.syncPollTimer);
        this.isSyncing = false;
        this.syncRunId = null;
    }
}">

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="page-title">Manajer Investasi</h1>
        <p class="page-sub">Kelola data manajer investasi beserta AUM dan UP per periode</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.investment-managers.template') }}"
           class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Template
        </a>
        <button @click="showImport = true"
                class="btn-outline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import
        </button>
        <form method="POST" action="{{ route('admin.investment-managers.sync-pasardana') }}"
              @submit="submitSync($event)"
              data-poll-url="{{ url('admin/investment-managers/sync-pasardana/status') }}"
              data-sync-label="Sinkronisasi MI + Relasi dari Pasardana">
            @csrf
            <button type="submit" class="btn-outline" :disabled="isSyncing"
                :class="isSyncing ? 'opacity-50 cursor-not-allowed' : ''"
                title="Tarik data manajer investasi + update relasi MI-RD dari Pasardana API">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    :class="isSyncing ? 'animate-spin' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Sync Manajer Investasi
            </button>
        </form>
        <form method="POST" action="{{ route('admin.investment-managers.sync-periods') }}"
              @submit="submitSync($event)"
              data-poll-url="{{ url('admin/investment-managers/sync-periods/status') }}"
              data-sync-label="Sinkronisasi AUM Periode MI dari Data Harian">
            @csrf
            <button type="submit" class="btn-outline" :disabled="isSyncing"
                :class="isSyncing ? 'opacity-50 cursor-not-allowed' : ''"
                title="Hitung AUM periode dari data harga harian reksa dana">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    :class="isSyncing ? 'animate-spin' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Sync Period AUM
            </button>
        </form>
        <a href="{{ route('admin.investment-managers.create') }}"
           class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert-success">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="mb-5">
    <div class="flex items-center gap-1 bg-[#f1f5f9] rounded-xl p-1 w-fit">
        <a href="{{ route('admin.investment-managers.index', ['tab' => 'daftar']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'daftar' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
            Daftar Manajer Investasi
        </a>
        <a href="{{ route('admin.investment-managers.index', ['tab' => 'riwayat-sync']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'riwayat-sync' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
            Riwayat Sync
        </a>
    </div>
</div>

@if($tab === 'daftar')
<div class="mb-5">
    <form method="GET" action="{{ route('admin.investment-managers.index') }}">
        <div class="flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                       placeholder="Cari nama / kode MI / kode OJK...">
            </div>
            <select name="mata_uang" class="px-3 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                <option value="">Mata Uang</option>
                <option value="IDR" {{ request('mata_uang') == 'IDR' ? 'selected' : '' }}>IDR</option>
                <option value="USD" {{ request('mata_uang') == 'USD' ? 'selected' : '' }}>USD</option>
            </select>
            <select name="tahun" class="px-3 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                <option value="">Tahun</option>
                @foreach($tahunList as $th)
                <option value="{{ $th }}" {{ request('tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                @endforeach
            </select>
            <select name="kuartal" class="px-3 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                <option value="">Kuartal</option>
                @foreach([1,2,3,4] as $q)
                <option value="{{ $q }}" {{ request('kuartal') == $q ? 'selected' : '' }}>Q{{ $q }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Cari</button>
            @if(request()->anyFilled(['search','mata_uang','tahun','kuartal']))
            <a href="{{ route('admin.investment-managers.index') }}" class="px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>
            @endif
            <button type="button" disabled class="px-4 py-2.5 border border-line text-muted/50 rounded-xl text-sm font-semibold cursor-not-allowed">Download</button>
            <button type="button" disabled class="px-4 py-2.5 border border-line text-muted/50 rounded-xl text-sm font-semibold cursor-not-allowed">Custom</button>
        </div>
    </form>
</div>

@if ($lastSyncRun)
    <div class="mb-4 px-5 py-2 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-2 text-xs text-blue-800">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <span class="font-semibold">Sinkronisasi Terakhir :</span>
        <span>{{ $lastSyncRun->completed_at ? $lastSyncRun->completed_at->format('d M Y H:i') : $lastSyncRun->created_at->format('d M Y H:i') }} WIB</span>
        @if ($lastSyncRun->stats && isset($lastSyncRun->stats['total']))
            <span class="text-blue-600">({{ number_format($lastSyncRun->stats['total']) }} data)</span>
        @endif
    </div>
@endif

@if($managers->isEmpty())
    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    <p class="font-medium">Belum ada data</p>
    <p class="text-sm mt-1">Klik "Tambah" atau import dari Excel</p>
</div>
@else
@foreach($managers as $m)
<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm mb-4">
    <div class="px-5 py-3 border-b border-line flex items-center justify-between bg-gradient-to-r from-primary to-primary-light">
        <h3 class="font-bold text-white flex items-center gap-2 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <a href="{{ route('admin.investment-managers.show', $m) }}" class="hover:underline text-white">
                {{ $m->name }}
            </a>
            @if($m->kode_mi)
                <span class="text-xs bg-white/20 text-white px-2 py-0.5 rounded font-mono">{{ $m->kode_mi }}</span>
            @endif
            @if($m->kode_ojk)
                <span class="text-xs bg-yellow-300/20 text-yellow-200 px-2 py-0.5 rounded font-mono">{{ $m->kode_ojk }}</span>
            @endif
        </h3>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.investment-managers.edit', $m) }}"
               class="text-xs text-white/70 hover:text-white transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Manajer
            </a>
        </div>
    </div>
    @if($m->periods->isEmpty())
    <div class="py-8 text-center text-muted text-sm">Belum ada data periode. Import Excel untuk menambahkan periode.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                    <th class="px-4 py-3 font-semibold">Periode</th>
                    <th class="px-4 py-3 font-semibold text-right">AUM (Rp)</th>
                    <th class="px-4 py-3 font-semibold text-right">UP / Unit Penyertaan</th>
                    <th class="px-4 py-3 font-semibold text-right w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach($m->periods->sortBy('period_date') as $p)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-primary/5 text-primary font-semibold text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $p->period_date->format('d M Y') }}
                        </span>
                        @if($p->mata_uang)
                            <span class="text-xs text-muted ml-1">{{ $p->mata_uang }}</span>
                        @endif
                        @if($p->tahun)
                            <span class="text-xs text-muted ml-1">{{ $p->tahun }} Q{{ $p->kuartal }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-xs font-semibold text-primary tabular-nums">
                        {{ $p->aum ? 'Rp' . number_format($p->aum, 0, ',', '.') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums">
                        {{ $p->up ? number_format($p->up, 2, ',', '.') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.investment-managers.period-destroy', $p) }}"
                              onsubmit="return confirm('Hapus periode {{ $p->period_date->format('d M Y') }} untuk {{ addslashes($m->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="p-1.5 rounded-lg text-muted hover:text-red-500 hover:bg-red-50 transition" title="Hapus periode">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endforeach

<div class="flex items-center justify-between gap-4 text-sm">
    <div class="flex items-center gap-2">
        <span class="text-muted text-xs">Tampilkan:</span>
        <form method="GET" action="{{ route('admin.investment-managers.index') }}">
            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            @if(request('mata_uang')) <input type="hidden" name="mata_uang" value="{{ request('mata_uang') }}"> @endif
            @if(request('tahun')) <input type="hidden" name="tahun" value="{{ request('tahun') }}"> @endif
            @if(request('kuartal')) <input type="hidden" name="kuartal" value="{{ request('kuartal') }}"> @endif
            <select name="per_page" onchange="this.form.submit()"
                    class="text-xs border border-line rounded-lg px-2 py-1 focus:outline-none cursor-pointer">
                @foreach([10, 25, 50] as $n)
                <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </form>
        <span class="text-muted text-xs">{{ $managers->total() }} manajer</span>
    </div>
    @if($managers->hasPages())
    <div class="flex items-center gap-1">
        @if($managers->onFirstPage())
        <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
        @else
        <a href="{{ $managers->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
        @endif
        @foreach($managers->getUrlRange(1, $managers->lastPage()) as $page => $url)
        <a href="{{ $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $managers->currentPage() ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
        @endforeach
        @if($managers->hasMorePages())
        <a href="{{ $managers->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
        @else
        <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next →</span>
        @endif
    </div>
    @endif
</div>
@endif
@else
<div class="grid lg:grid-cols-[minmax(0,1fr)_minmax(420px,0.9fr)] gap-5">
    <div class="table-card">
        <div class="table-head">
            <h2 class="th-title">Riwayat Sync Pasardana</h2>
            <span class="th-meta">{{ $recentSyncRuns->total() }} total</span>
        </div>

        @if($recentSyncRuns->isEmpty())
            <div class="py-16 text-center text-muted">
                <p class="font-medium">Belum ada riwayat sync</p>
                <p class="text-sm mt-1">Klik "Sync Manajer Investasi" untuk memulai sinkronisasi.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                            <th class="px-4 py-3 font-semibold">#</th>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Progress</th>
                            <th class="px-4 py-3 font-semibold">Pesan</th>
                            <th class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($recentSyncRuns as $run)
                            @php
                                $statusClass = match ($run->status) {
                                    'completed' => 'bg-green-50 text-green-700 border-green-200',
                                    'failed' => 'bg-red-50 text-red-700 border-red-200',
                                    'running' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    default => 'bg-slate-50 text-slate-600 border-slate-200',
                                };
                                $isSelected = $selectedRun && $selectedRun->id === $run->id;
                            @endphp
                            <tr class="hover:bg-[#f8fafc] transition-colors {{ $isSelected ? 'bg-primary/5' : '' }}">
                                <td class="px-4 py-3 font-mono text-xs">#{{ $run->id }}</td>
                                <td class="px-4 py-3 text-xs text-muted">{{ $run->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded border text-xs font-semibold {{ $statusClass }}">
                                        {{ $run->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs">{{ $run->progress_percent }}%</td>
                                <td class="px-4 py-3 text-xs text-muted max-w-[200px] truncate">{{ $run->message ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.investment-managers.index', ['tab' => 'riwayat-sync', 'selected_run' => $run->id]) }}"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $isSelected ? 'bg-primary text-white' : 'bg-primary/10 text-primary hover:bg-primary/20' }} transition">
                                        {{ $isSelected ? 'Dipilih' : 'Lihat' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($recentSyncRuns->hasPages())
                <div class="px-6 py-4 border-t border-line">
                    {{ $recentSyncRuns->links() }}
                </div>
            @endif
        @endif
    </div>

    <div class="table-card">
        <div class="table-head">
            <h2 class="th-title">
                Perubahan Data
                @if($selectedRun)
                    <span class="text-xs text-white/70 font-normal ml-2">Run #{{ $selectedRun->id }}</span>
                @endif
            </h2>
        </div>

        @if($selectedRun && $changesUrl)
            <div class="p-4">
                @include('admin.components.sync-changes-list', [
                    'changesUrl' => $changesUrl,
                    'detailTypes' => $detailTypes,
                ])
            </div>
        @else
            <div class="py-16 text-center text-muted">
                <p class="font-medium">Pilih run untuk melihat perubahan</p>
            </div>
        @endif
    </div>
</div>
@endif

{{-- Modal Loading: Sync dari Pasardana (server-polled progress) --}}
<div x-show="isSyncing" x-cloak
    class="fixed inset-0 z-[60] bg-white/95 backdrop-blur-sm grid place-items-center px-4"
    x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100">
    <div class="text-center max-w-lg w-full">
        <template x-if="syncStatus !== 'completed' && syncStatus !== 'failed'">
            <div class="w-14 h-14 border-4 border-accent border-t-transparent rounded-full mx-auto mb-6 animate-spin"></div>
        </template>
        <template x-if="syncStatus === 'completed'">
            <div class="w-14 h-14 rounded-full mx-auto mb-6 bg-green-100 grid place-items-center">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </template>
        <template x-if="syncStatus === 'failed'">
            <div class="w-14 h-14 rounded-full mx-auto mb-6 bg-red-100 grid place-items-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
        </template>

        <h3 class="text-lg font-bold text-primary mb-1">
            <span x-show="syncStatus === 'completed'">Sync Selesai</span>
            <span x-show="syncStatus === 'failed'">Sync Gagal</span>
            <span x-show="syncStatus !== 'completed' && syncStatus !== 'failed'" x-text="syncLabel"></span>
        </h3>
        <p class="text-sm text-muted mb-2" x-text="syncStepLabel"></p>
        <p class="text-xs text-muted mb-5">
            Run ID: <span class="font-mono" x-text="syncRunId"></span> &middot; Status: <span class="font-semibold" x-text="syncStatus"></span>
        </p>

        <div class="max-w-sm mx-auto mb-5">
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full transition-all duration-500 ease-out"
                    :style="`width: ${syncProgress}%`"
                    :class="syncStatus === 'failed' ? 'bg-red-500' : (syncStatus === 'completed' ? 'bg-green-500' : 'bg-accent')"></div>
            </div>
            <p class="text-xs text-muted mt-1.5"><span x-text="syncProgress"></span>%</p>
        </div>

        <template x-if="syncMessage">
            <div class="mt-2 mx-auto max-w-md text-left rounded-xl border px-4 py-3 text-xs"
                :class="syncStatus === 'failed' ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800'">
                <p x-text="syncMessage"></p>
            </div>
        </template>

        <template x-if="syncErrors && syncErrors.length">
            <div class="mt-3 mx-auto max-w-md text-left rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                <p class="font-semibold mb-1">Catatan / Error</p>
                <ul class="list-disc pl-4 space-y-0.5">
                    <template x-for="err in syncErrors" :key="err">
                        <li x-text="err"></li>
                    </template>
                </ul>
            </div>
        </template>

        <p class="text-xs text-muted mt-6" x-show="syncStatus !== 'completed' && syncStatus !== 'failed'">
            Job berjalan di background. Aman jika kamu tutup tab &mdash; progress akan dilanjutkan oleh worker.
        </p>

        <button type="button" @click="cancelSync()" x-show="syncStatus === 'failed'"
            class="mt-4 px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">
            Tutup
        </button>
    </div>
</div>

{{-- Modal Import --}}
<div x-show="showImport" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40" @click="showImport = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <h3 class="font-bold text-primary text-base mb-1">Import Manajer Investasi</h3>
        <p class="text-muted text-sm mb-4">Upload file Excel sesuai format template. Data akan ditambahkan atau diperbarui.</p>
        <form method="POST" action="{{ route('admin.investment-managers.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="border-2 border-dashed border-line rounded-xl p-6 text-center mb-4 hover:border-accent/40 transition">
                <svg class="w-8 h-8 mx-auto text-muted mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <label class="cursor-pointer">
                    <span class="text-sm font-semibold text-accent">Pilih file</span>
                    <span class="text-sm text-muted"> atau drag & drop</span>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden" required>
                </label>
                <p class="text-xs text-muted mt-1">Format: .xlsx, .xls, .csv</p>
            </div>
            @error('file')<p class="text-red-500 text-xs mb-3">{{ $message }}</p>@enderror
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.investment-managers.template') }}" class="text-xs text-accent hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download template
                </a>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showImport = false" class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition">Upload & Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

</div>
@endsection
