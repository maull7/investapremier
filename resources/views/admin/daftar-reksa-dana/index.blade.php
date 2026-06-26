@extends('layouts.admin')

@section('title', 'Daftar Reksa Dana')

@section('content')
    <div x-data="{
        isSyncing: false,
        syncRunId: null,
        syncStep: 'queued',
        syncStepLabel: 'Menunggu worker...',
        syncProgress: 0,
        syncStatus: 'queued',
        syncMessage: '',
        syncErrors: [],
        syncPollUrl: '/admin/daftar-reksa-dana/sync-pasardana/status',
        syncPollTimer: null,

        init() {
            const initialRunId = @json(session('sync_run_id'));
            if (initialRunId) {
                this.startPolling(initialRunId);
            }
        },

        async submitSync(event) {
            event.preventDefault();
            const form = event.target;
            this.syncPollUrl = form.dataset.pollUrl || '/admin/daftar-reksa-dana/sync-pasardana/status';
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
            <h1 class="page-title">Daftar Reksa Dana</h1>
            <p class="page-sub">Master data reksa dana beserta riwayat harga harian</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.daftar-reksa-dana.sync-pasardana') }}" @submit="submitSync($event)"
                  data-poll-url="{{ url('admin/daftar-reksa-dana/sync-pasardana/status') }}">
                @csrf
                <button type="submit" class="btn-outline" :disabled="isSyncing"
                    :class="isSyncing ? 'opacity-50 cursor-not-allowed' : ''"
                    title="Sync reksa dana + harga harian dari Pasardana API">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        :class="isSyncing ? 'animate-spin' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Sync Reksa Dana
                </button>
            </form>
            <form method="POST" action="{{ route('admin.daftar-reksa-dana.replace-rewrite') }}" @submit="submitSync($event)"
                  data-poll-url="{{ url('admin/daftar-reksa-dana/sync-pasardana/status') }}">
                @csrf
                <button type="submit" class="btn-secondary" :disabled="isSyncing"
                    :class="isSyncing ? 'opacity-50 cursor-not-allowed' : ''"
                    onclick="return confirm('Yakin ingin menjalankan Bersihkan & Perbaiki Data? Operasi ini akan mendeteksi dan membersihkan data duplikat serta mengisi Kategori Produk yang kosong.')"
                    title="Deteksi duplikat, bersihkan data, dan perbaiki Kategori Produk">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    Bersihkan & Perbaiki Data
                </button>
            </form>
            <form method="POST" action="{{ route('admin.daftar-reksa-dana.sync-all-pasardana') }}" @submit="submitSync($event)"
                  data-poll-url="{{ url('admin/daftar-reksa-dana/sync-all-pasardana/status') }}">
                @csrf
                <button type="submit" class="btn-primary" :disabled="isSyncing"
                    :class="isSyncing ? 'opacity-50 cursor-not-allowed' : ''"
                    title="Sync MI + RD + Relasi + Harga Harian sekaligus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        :class="isSyncing ? 'animate-spin' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Sync All Pasardana
                </button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="flex gap-1 mb-5 border-b border-line">
        <a href="{{ route('admin.daftar-reksa-dana.index', array_merge(request()->except('tab', 'harga_page', 'harian_page'), ['tab' => 'harga'])) }}"
            class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px {{ $tab === 'harga' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-primary' }}">
            Harga Reksa Dana
        </a>
        <a href="{{ route('admin.daftar-reksa-dana.index', array_merge(request()->except('tab', 'harga_page', 'harian_page', 'link_page', 'log_page', 'edit'), ['tab' => 'harian'])) }}"
            class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px {{ $tab === 'harian' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-primary' }}">
            Harian Reksa Dana
        </a>
        <a href="{{ route('admin.daftar-reksa-dana.index', array_merge(request()->except('tab', 'harga_page', 'harian_page', 'link_page', 'log_page', 'edit'), ['tab' => 'link-website'])) }}"
            class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px {{ $tab === 'link-website' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-muted hover:text-primary' }}">
            Link Website
        </a>
        <a href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs']) }}"
            class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px {{ $tab === 'prospektus-ffs' ? 'border-emerald-700 text-emerald-700' : 'border-transparent text-muted hover:text-primary' }}">
            Prospektus dan FFS
        </a>
        <a href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'riwayat-sync']) }}"
            class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px {{ $tab === 'riwayat-sync' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-primary' }}">
            Riwayat Sync
        </a>
    </div>

    {{-- ===================== TAB HARGA ===================== --}}
    @if ($tab === 'harga')

        {{-- Upload Panel --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-5">
            <div
                class="px-5 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                <h2 class="font-bold text-white text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Upload Harga Reksa Dana
                </h2>
                <a href="{{ route('admin.daftar-reksa-dana.template-harga') }}"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download Template
                </a>
            </div>
            <div class="p-5">
                <p class="text-xs text-muted mb-3">Kolom: <code class="bg-[#f1f5f9] px-1 rounded">kode_reksa_dana (opsional)
                        |
                        nama_reksa_dana | nama_manajer_investasi | jenis | kategori | kategori_produk
                        (Konvensional/Syariah/Index/ETF) | mata_uang | nab_per_unit | tanggal_nab</code>
                    — jika kode dikosongkan, akan digenerate otomatis dari kode MI + jenis + kategori produk.</p>
                <form method="POST" action="{{ route('admin.daftar-reksa-dana.upload-harga') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_redirect_tab" value="harga">
                    <div class="flex gap-2">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="flex-1 text-xs border border-line rounded-lg px-3 py-2 text-muted file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-primary/10 file:text-primary">
                        <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary/90 transition whitespace-nowrap">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Harga --}}
        <div class="table-card">
            <div class="table-head">
                <h2 class="font-bold text-white text-sm">Daftar Reksa Dana ({{ $reksaDanas->total() }} total)</h2>
                <div class="flex gap-2">
                    <button type="button" onclick="openModal('modal-harga-create')"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Baru
                    </button>
                    <form method="GET" action="{{ route('admin.daftar-reksa-dana.index') }}" class="flex gap-2">
                        <input type="hidden" name="tab" value="harga">
                        @if (request('jenis'))
                            <input type="hidden" name="jenis" value="{{ request('jenis') }}">
                        @endif
                        @if (request('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif
                        @if (request('direction'))
                            <input type="hidden" name="direction" value="{{ request('direction') }}">
                        @endif
                        <input type="date" name="harga_tanggal" value="{{ $hargaTanggal }}"
                            class="text-xs border border-white/30 bg-white/10 text-white rounded-lg px-3 py-1.5 focus:outline-none focus:bg-white/20 [color-scheme:dark]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..."
                            class="text-xs border border-white/30 bg-white/10 text-white placeholder-white/50 rounded-lg px-3 py-1.5 w-44 focus:outline-none focus:bg-white/20">
                        <button type="submit"
                            class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">Cari</button>
                        @if ($hargaTanggal || request('search') || request('jenis'))
                            <a href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'harga']) }}"
                                class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-semibold transition">Reset</a>
                        @endif
                    </form>
                </div>
            </div>

            @if ($lastSyncRun)
                <div class="px-5 py-2 bg-blue-50 border-b border-blue-200 flex items-center gap-2 text-xs text-blue-800">
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
            {{-- Filter Jenis --}}
            <div class="px-6 py-3 border-b border-line flex gap-2 text-xs flex-wrap">
                @foreach (['', 'Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'] as $j)
                    <a href="{{ route('admin.daftar-reksa-dana.index', array_merge(request()->except('jenis', 'harga_page'), ['tab' => 'harga'], $j ? ['jenis' => $j] : [])) }}"
                        class="px-3 py-1.5 rounded-lg border transition {{ request('jenis') === $j || (!request('jenis') && $j === '') ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                        {{ $j ?: 'Semua' }}
                    </a>
                @endforeach
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                            <th class="px-4 py-3.5 font-semibold">Kode</th>
                            <th class="px-4 py-3.5 font-semibold">
                                <a href="{{ route('admin.daftar-reksa-dana.index', array_merge(request()->except('sort', 'direction', 'harga_page'), ['tab' => 'harga', 'sort' => 'nama_reksa_dana', 'direction' => request('sort', 'nama_reksa_dana') === 'nama_reksa_dana' && request('direction', 'asc') === 'asc' ? 'desc' : 'asc'])) }}"
                                   class="flex items-center gap-1 hover:text-primary whitespace-nowrap">
                                    Nama Reksa Dana
                                    @if(request('sort', 'nama_reksa_dana') === 'nama_reksa_dana')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction', 'asc') === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3.5 font-semibold">Manajer Investasi</th>
                            <th class="px-4 py-3.5 font-semibold">Jenis</th>
                            <th class="px-4 py-3.5 font-semibold">Kategori Produk</th>
                            <th class="px-4 py-3.5 font-semibold">Kelas</th>
                            <th class="px-4 py-3.5 font-semibold">Kategori</th>
                            <th class="px-4 py-3.5 font-semibold">Mata Uang</th>
                            <th class="px-4 py-3.5 font-semibold text-right">NAB/UP</th>
                            <th class="px-4 py-3.5 font-semibold">Tanggal NAB/UP</th>
                            <th class="px-4 py-3.5 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($reksaDanas as $rd)
                            <tr class="hover:bg-[#f8fafc] transition-colors">
                                <td class="px-4 py-3.5 font-mono text-xs text-muted">{{ $rd->kode_reksa_dana ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 font-semibold text-primary">
                                    <a href="{{ route('admin.daftar-reksa-dana.show', $rd) }}"
                                        class="hover:underline text-primary">{{ $rd->nama_reksa_dana }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-muted text-xs">{{ $rd->nama_manajer_investasi }}</td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $jenisColor = match ($rd->jenis) {
                                            'Saham' => 'bg-blue-100 text-blue-700',
                                            'Pendapatan Tetap' => 'bg-amber-100 text-amber-700',
                                            'Campuran' => 'bg-purple-100 text-purple-700',
                                            default => 'bg-green-100 text-green-700',
                                        };
                                    @endphp
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $jenisColor }}">{{ $rd->jenis }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if ($rd->kategori_produk)
                                        @php
                                            $kpColor = match ($rd->kategori_produk) {
                                                'Konvensional' => 'bg-green-100 text-green-700',
                                                'Syariah' => 'bg-emerald-100 text-emerald-700',
                                                'Index' => 'bg-blue-100 text-blue-700',
                                                'ETF' => 'bg-purple-100 text-purple-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $kpColor }}">{{ $rd->kategori_produk }}</span>
                                    @else
                                        <span class="text-xs text-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-xs text-muted">{{ $rd->display_kelas }}</td>
                                <td class="px-4 py-3.5 text-xs text-muted">
                                    @if (is_array($rd->kategori) && count($rd->kategori))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($rd->kategori as $kat)
                                                <span
                                                    class="px-1.5 py-0.5 bg-[#f1f5f9] rounded text-[11px]">{{ $kat }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-xs text-muted">{{ $rd->display_mata_uang }}</td>
                                <td class="px-4 py-3.5 text-right text-xs font-semibold text-primary">
                                    {{ $rd->nab_per_unit ? number_format($rd->nab_per_unit, 4, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-xs text-muted">
                                    {{ $rd->tanggal_nab ? $rd->tanggal_nab->format('d M Y') : '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" onclick='openEditHarga(@json($rd))'
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form method="POST"
                                            action="{{ route('admin.daftar-reksa-dana.harga.destroy', $rd) }}"
                                            class="inline"
                                            onsubmit="return confirm('Yakin ingin menghapus {{ $rd->nama_reksa_dana }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-12 text-center text-muted">
                                    <p class="font-medium">Belum ada data</p>
                                    <p class="text-xs mt-1">Upload file excel menggunakan form di atas</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($reksaDanas->hasPages())
                <div class="px-6 py-4 border-t border-line flex items-center justify-between text-sm">
                    <p class="text-muted text-xs">{{ $reksaDanas->firstItem() }}–{{ $reksaDanas->lastItem() }} dari
                        {{ $reksaDanas->total() }}</p>
                    <div class="flex items-center gap-1">
                        @if (!$reksaDanas->onFirstPage())
                            <a href="{{ $reksaDanas->previousPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">←
                                Prev</a>
                        @endif
                        @php
                            $cur = $reksaDanas->currentPage();
                            $last = $reksaDanas->lastPage();
                            $s = max(1, $cur - 2);
                            $e = min($last, $cur + 2);
                        @endphp
                        @if ($s > 1)
                            <a href="{{ $reksaDanas->url(1) }}"
                                class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                            @if ($s > 2)
                                <span class="px-1 text-muted text-xs">…</span>
                            @endif
                        @endif
                        @foreach ($reksaDanas->getUrlRange($s, $e) as $page => $url)
                            <a href="{{ $url }}"
                                class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
                        @endforeach
                        @if ($e < $last)
                            @if ($e < $last - 1)
                                <span class="px-1 text-muted text-xs">…</span>
                            @endif
                            <a href="{{ $reksaDanas->url($last) }}"
                                class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                        @endif
                        @if ($reksaDanas->hasMorePages())
                            <a href="{{ $reksaDanas->nextPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next
                                →</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- ===================== TAB HARIAN ===================== --}}
    @elseif($tab === 'harian')
        {{-- Upload Panel --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-5">
            <div
                class="px-5 py-4 border-b border-line bg-gradient-to-r from-accent to-accent/80 flex items-center justify-between">
                <h2 class="font-bold text-white text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Upload Harian Reksa Dana
                </h2>
                <a href="{{ route('admin.daftar-reksa-dana.template-harian') }}"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download Template
                </a>
            </div>
            <div class="p-5">
                <p class="text-xs text-muted mb-3">Kolom: <code class="bg-[#f1f5f9] px-1 rounded">nama_reksa_dana |
                        tanggal | nab_per_unit | total_dana_kelolaan | unit_penyertaan</code></p>
                <form method="POST" action="{{ route('admin.daftar-reksa-dana.upload-harian') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="flex gap-2">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="flex-1 text-xs border border-line rounded-lg px-3 py-2 text-muted file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-accent/10 file:text-accent">
                        <button type="submit"
                            class="px-4 py-2 bg-accent text-white rounded-lg text-xs font-semibold hover:bg-accent/90 transition whitespace-nowrap">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Harian --}}
        <div class="table-card">
            <div
                class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-accent to-accent/80">
                <h2 class="font-bold text-white text-sm">Riwayat Harian ({{ $harian->total() }} data)</h2>
                <div class="flex gap-2">
                    <button type="button" onclick="openModal('modal-harian-create')"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Baru
                    </button>
                    <form method="GET" action="{{ route('admin.daftar-reksa-dana.index') }}" class="flex gap-2">
                        <input type="hidden" name="tab" value="harian">
                        @if (request('harian_sort'))
                            <input type="hidden" name="harian_sort" value="{{ request('harian_sort') }}">
                        @endif
                        @if (request('harian_direction'))
                            <input type="hidden" name="harian_direction" value="{{ request('harian_direction') }}">
                        @endif
                        <input type="date" name="harian_tanggal" value="{{ $harianTanggal }}"
                            class="text-xs border border-white/30 bg-white/10 text-white rounded-lg px-3 py-1.5 focus:outline-none focus:bg-white/20 [color-scheme:dark]">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama reksa dana..."
                            class="text-xs border border-white/30 bg-white/10 text-white placeholder-white/50 rounded-lg px-3 py-1.5 w-44 focus:outline-none focus:bg-white/20">
                        <button type="submit"
                            class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">Cari</button>
                        @if ($harianTanggal || request('search'))
                            <a href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'harian']) }}"
                                class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-semibold transition">Reset</a>
                        @endif
                    </form>
                </div>
            </div>
            @if ($lastSyncRun)
                <div class="px-5 py-2 bg-orange-50 border-b border-orange-200 flex items-center gap-2 text-xs text-orange-800">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="font-semibold">Sinkronisasi Terakhir :</span>
                    <span>{{ $lastSyncRun->completed_at ? $lastSyncRun->completed_at->format('d M Y H:i') : $lastSyncRun->created_at->format('d M Y H:i') }} WIB</span>
                    @if ($lastSyncRun->stats && isset($lastSyncRun->stats['total']))
                        <span class="text-orange-600">({{ number_format($lastSyncRun->stats['total']) }} data)</span>
                    @endif
                </div>
            @endif
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                            <th class="px-4 py-3.5 font-semibold">Tanggal</th>
                            <th class="px-4 py-3.5 font-semibold">Kode</th>
                            <th class="px-4 py-3.5 font-semibold">
                                <a href="{{ route('admin.daftar-reksa-dana.index', array_merge(request()->except('harian_sort', 'harian_direction', 'harian_page'), ['tab' => 'harian', 'harian_sort' => 'reksa_dana.nama_reksa_dana', 'harian_direction' => request('harian_sort', 'reksa_dana.nama_reksa_dana') === 'reksa_dana.nama_reksa_dana' && request('harian_direction', 'asc') === 'asc' ? 'desc' : 'asc'])) }}"
                                   class="flex items-center gap-1 hover:text-primary whitespace-nowrap">
                                    Reksadana
                                    @if(request('harian_sort', 'reksa_dana.nama_reksa_dana') === 'reksa_dana.nama_reksa_dana')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('harian_direction', 'asc') === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3.5 font-semibold text-right">NAB/UP</th>
                            <th class="px-4 py-3.5 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($harian as $h)
                            <tr class="hover:bg-[#f8fafc] transition-colors">
                                <td class="px-4 py-3.5 text-xs text-muted">{{ $h->tanggal->format('d M Y') }}</td>
                                <td class="px-4 py-3.5 font-mono text-xs text-muted">
                                    {{ $h->reksaDana->kode_reksa_dana ?? '—' }}</td>
                                <td class="px-4 py-3.5 font-semibold text-primary text-sm">
                                    <a href="{{ $h->reksaDana ? route('admin.daftar-reksa-dana.show', $h->reksaDana) : '#' }}"
                                        class="hover:underline text-primary">{{ $h->reksaDana->nama_reksa_dana ?? '—' }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-right text-xs font-semibold text-primary">
                                    {{ number_format($h->nab_per_unit, 4, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" onclick='openEditHarian(@json($h))'
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form method="POST"
                                            action="{{ route('admin.daftar-reksa-dana.harian.destroy', $h) }}"
                                            class="inline"
                                            onsubmit="return confirm('Yakin ingin menghapus data harian ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-muted">
                                    <p class="font-medium">Belum ada data harian</p>
                                    <p class="text-xs mt-1">Upload file excel menggunakan form di atas</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($harian->hasPages())
                <div class="px-6 py-4 border-t border-line flex items-center justify-between text-sm">
                    <p class="text-muted text-xs">{{ $harian->firstItem() }}–{{ $harian->lastItem() }} dari
                        {{ $harian->total() }}</p>
                    <div class="flex items-center gap-1">
                        @if (!$harian->onFirstPage())
                            <a href="{{ $harian->previousPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">←
                                Prev</a>
                        @endif
                        @php
                            $cur = $harian->currentPage();
                            $last = $harian->lastPage();
                            $s = max(1, $cur - 2);
                            $e = min($last, $cur + 2);
                        @endphp
                        @if ($s > 1)
                            <a href="{{ $harian->url(1) }}"
                                class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                            @if ($s > 2)
                                <span class="px-1 text-muted text-xs">…</span>
                            @endif
                        @endif
                        @foreach ($harian->getUrlRange($s, $e) as $page => $url)
                            <a href="{{ $harian->currentPage() == $page ? '#' : $url }}"
                                class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-accent text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
                        @endforeach
                        @if ($e < $last)
                            @if ($e < $last - 1)
                                <span class="px-1 text-muted text-xs">…</span>
                            @endif
                            <a href="{{ $harian->url($last) }}"
                                class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                        @endif
                        @if ($harian->hasMorePages())
                            <a href="{{ $harian->nextPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next
                                →</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- ===================== TAB LINK WEBSITE ===================== --}}
    @elseif($tab === 'link-website')
        @include('admin.daftar-reksa-dana.partials.tab-link-website')
    @elseif($tab === 'prospektus-ffs')
        @include('admin.daftar-reksa-dana.partials.tab-prospektus-ffs')
    @elseif($tab === 'riwayat-sync')
    <div class="grid lg:grid-cols-[minmax(0,1fr)_minmax(420px,0.9fr)] gap-5">
        <div class="table-card">
            <div class="table-head">
                <h2 class="th-title">Riwayat Sync Pasardana</h2>
                <span class="th-meta">{{ $recentSyncRuns->total() }} total</span>
            </div>

            @if($recentSyncRuns->isEmpty())
                <div class="py-16 text-center text-muted">
                    <p class="font-medium">Belum ada riwayat sync</p>
                    <p class="text-sm mt-1">Klik "Sync Reksa Dana" untuk memulai sinkronisasi.</p>
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
                                        <a href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'riwayat-sync', 'selected_run' => $run->id]) }}"
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

        <div class="table-card" x-data="{ syncDetailTab: 'changes' }">
            <div class="table-head">
                <h2 class="th-title">
                    Perubahan Data
                    @if($selectedRun)
                        <span class="text-xs text-white/70 font-normal ml-2">Run #{{ $selectedRun->id }}</span>
                    @endif
                </h2>
                @if($selectedRun && !$selectedRun->applied_at)
                    @php
                        $hasPending = \App\Models\SyncChangeLog::where('sync_run_id', $selectedRun->id)
                            ->where('entity_type', 'rd')
                            ->where('change_type', 'created')
                            ->whereNotNull('pending_data')
                            ->exists();
                    @endphp
                    @if($hasPending)
                        <form method="POST" action="{{ route('admin.daftar-reksa-dana.sync-pasardana.apply', $selectedRun) }}"
                            onsubmit="return confirm('Yakin ingin menambahkan semua reksa dana baru dari sync ini ke database?')">
                            @csrf
                            <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Terapkan RD Baru
                            </button>
                        </form>
                    @endif
                @elseif($selectedRun && $selectedRun->applied_at)
                    <span class="text-xs text-white/70 font-normal">✓ Applied {{ $selectedRun->applied_at->format('d M Y H:i') }}</span>
                @endif
            </div>

            @if($selectedRun && $changesUrl)
                {{-- Sub-tabs --}}
                @php
                    $pendingRdList = \App\Models\SyncChangeLog::where('sync_run_id', $selectedRun->id)
                        ->where('entity_type', 'rd')
                        ->where('change_type', 'created')
                        ->whereNotNull('pending_data')
                        ->get();
                @endphp

                @if($pendingRdList->isNotEmpty())
                <div class="flex gap-1 px-4 pt-3 border-b border-line">
                    <button @click="syncDetailTab = 'changes'"
                        class="px-4 py-2 text-xs font-semibold border-b-2 -mb-px transition"
                        :class="syncDetailTab === 'changes' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-primary'">
                        Perubahan
                    </button>
                    <button @click="syncDetailTab = 'pending'"
                        class="px-4 py-2 text-xs font-semibold border-b-2 -mb-px transition"
                        :class="syncDetailTab === 'pending' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-muted hover:text-emerald-600'">
                        RD Baru ({{ $pendingRdList->count() }})
                    </button>
                </div>
                @endif

                {{-- Tab: Perubahan --}}
                <div x-show="syncDetailTab === 'changes'" class="p-4">
                    @include('admin.components.sync-changes-list', [
                        'changesUrl' => $changesUrl,
                        'detailTypes' => $detailTypes,
                    ])
                </div>

                {{-- Tab: RD Baru --}}
                @if($pendingRdList->isNotEmpty())
                <div x-show="syncDetailTab === 'pending'" x-cloak class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                                    <th class="px-4 py-3 font-semibold">#</th>
                                    <th class="px-4 py-3 font-semibold">Nama Reksa Dana</th>
                                    <th class="px-4 py-3 font-semibold">Jenis</th>
                                    <th class="px-4 py-3 font-semibold">MI</th>
                                    <th class="px-4 py-3 font-semibold">NAB/UP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach($pendingRdList as $i => $prd)
                                    @php $pData = $prd->pending_data; @endphp
                                    <tr class="hover:bg-[#f8fafc] transition-colors">
                                        <td class="px-4 py-2.5 text-xs text-muted">{{ $i + 1 }}</td>
                                        <td class="px-4 py-2.5 text-xs font-medium text-primary">{{ $pData['nama_reksa_dana'] ?? '-' }}</td>
                                        <td class="px-4 py-2.5 text-xs text-muted">{{ $pData['jenis'] ?? '-' }}</td>
                                        <td class="px-4 py-2.5 text-xs text-muted max-w-[150px] truncate">{{ $pData['nama_manajer_investasi'] ?? '-' }}</td>
                                        <td class="px-4 py-2.5 text-xs font-mono">{{ isset($pData['nab_per_unit']) ? number_format($pData['nab_per_unit'], 2) : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(!$selectedRun->applied_at)
                        <p class="mt-3 text-xs text-amber-600">
                            <svg class="w-3.5 h-3.5 inline -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            Data ini belum masuk ke database. Klik "Terapkan RD Baru" untuk menambahkannya.
                        </p>
                    @else
                        <p class="mt-3 text-xs text-emerald-600">✓ Semua RD baru sudah diterapkan pada {{ $selectedRun->applied_at->format('d M Y H:i') }}</p>
                    @endif
                </div>
                @endif
            @else
                <div class="py-16 text-center text-muted">
                    <p class="font-medium">Pilih run untuk melihat perubahan</p>
                </div>
            @endif
        </div>
    </div>
    @endif
    {{-- ===================== MODAL HARGA CREATE ===================== --}}
    <div id="modal-harga-create" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4"
        onclick="if(event.target===this)closeModal('modal-harga-create')">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-line sticky top-0 bg-white z-10">
                <h3 class="font-bold text-primary">Tambah Reksa Dana</h3>
                <button type="button" onclick="closeModal('modal-harga-create')"
                    class="p-1 hover:bg-[#f1f5f9] rounded-lg transition">
                    <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.daftar-reksa-dana.harga.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Kode Reksa Dana</label>
                        <input type="text" name="kode_reksa_dana" id="create-harga-kode"
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20"
                            oninput="fillFromKode(this.value, 'create')">
                        <p id="create-harga-kode-error" class="text-xs text-red-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Nama Reksa Dana <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="nama_reksa_dana" id="create-harga-nama" required
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Nama Manajer Investasi</label>
                    <input type="text" name="nama_manajer_investasi" id="create-harga-mi" readonly
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Jenis</label>
                        <input type="text" name="jenis" id="create-harga-jenis" readonly
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Kategori Produk</label>
                        <input type="text" name="kategori_produk" id="create-harga-kp" readonly
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kelas</label>
                    <input type="text" name="kelas" id="create-harga-kelas" readonly
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kategori</label>
                    <div id="create-harga-kategori-display"
                        class="text-sm text-gray-600 px-3 py-2 bg-gray-50 rounded-lg border border-line min-h-[38px]">—
                    </div>
                    <input type="hidden" name="kategori" id="create-harga-kategori">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Mata Uang</label>
                    <input type="text" name="mata_uang" id="create-harga-matauang" readonly value="IDR"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Benchmark</label>
                        <input type="text" name="benchmark"
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">NAB/UP</label>
                        <input type="number" step="0.000001" name="nab_per_unit"
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Tanggal NAB</label>
                    <input type="date" name="tanggal_nab"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modal-harga-create')"
                        class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-primary rounded-lg hover:bg-primary/90 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL HARGA EDIT ===================== --}}
    <div id="modal-harga-edit" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4"
        onclick="if(event.target===this)closeModal('modal-harga-edit')">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-line sticky top-0 bg-white z-10">
                <h3 class="font-bold text-primary">Edit Reksa Dana</h3>
                <button type="button" onclick="closeModal('modal-harga-edit')"
                    class="p-1 hover:bg-[#f1f5f9] rounded-lg transition">
                    <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="" class="p-6 space-y-4" id="form-harga-edit">
                @csrf
                @method('POST')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Kode Reksa Dana</label>
                        <input type="text" name="kode_reksa_dana" id="edit-harga-kode"
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20"
                            oninput="fillFromKode(this.value, 'edit')">
                        <p id="edit-harga-kode-error" class="text-xs text-red-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Nama Reksa Dana <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="nama_reksa_dana" id="edit-harga-nama" required
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Nama Manajer Investasi</label>
                    <input type="text" name="nama_manajer_investasi" id="edit-harga-mi" readonly
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Jenis</label>
                        <input type="text" name="jenis" id="edit-harga-jenis" readonly
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Kategori Produk</label>
                        <input type="text" name="kategori_produk" id="edit-harga-kp" readonly
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kelas</label>
                    <input type="text" name="kelas" id="edit-harga-kelas" readonly
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kategori</label>
                    <div id="edit-harga-kategori-display"
                        class="text-sm text-gray-600 px-3 py-2 bg-gray-50 rounded-lg border border-line min-h-[38px]">
                    </div>
                    <input type="hidden" name="kategori" id="edit-harga-kategori">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Mata Uang</label>
                    <input type="text" name="mata_uang" id="edit-harga-matauang" readonly
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Benchmark</label>
                        <input type="text" name="benchmark" id="edit-harga-benchmark"
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">NAB/UP</label>
                        <input type="number" step="0.000001" name="nab_per_unit" id="edit-harga-nab"
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Tanggal NAB</label>
                    <input type="date" name="tanggal_nab" id="edit-harga-tgl-nab"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modal-harga-edit')"
                        class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-primary rounded-lg hover:bg-primary/90 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL HARIAN CREATE ===================== --}}
    <div id="modal-harian-create" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4"
        onclick="if(event.target===this)closeModal('modal-harian-create')">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-line">
                <h3 class="font-bold text-primary">Tambah Data Harian</h3>
                <button type="button" onclick="closeModal('modal-harian-create')"
                    class="p-1 hover:bg-[#f1f5f9] rounded-lg transition">
                    <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.daftar-reksa-dana.harian.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Reksa Dana <span
                            class="text-red-500">*</span></label>
                    <select name="reksa_dana_id" required
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/20">
                        <option value="">— Pilih Reksa Dana —</option>
                        @foreach ($reksaDanaOptions as $rd)
                            <option value="{{ $rd->id }}">
                                {{ $rd->kode_reksa_dana ? '[' . $rd->kode_reksa_dana . '] ' : '' }}{{ $rd->nama_reksa_dana }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Tanggal <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" required
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">NAB/UP <span
                                class="text-red-500">*</span></label>
                        <input type="number" step="0.000001" name="nab_per_unit" required
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/20">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modal-harian-create')"
                        class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-accent rounded-lg hover:bg-accent/90 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL HARIAN EDIT ===================== --}}
    <div id="modal-harian-edit" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4"
        onclick="if(event.target===this)closeModal('modal-harian-edit')">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-line">
                <div>
                    <h3 class="font-bold text-primary">Edit Data Harian</h3>
                    <p class="text-xs text-muted mt-0.5">
                        Kode: <span id="edit-harian-info-kode" class="font-semibold text-primary">—</span>
                        &nbsp;·&nbsp; Tanggal: <span id="edit-harian-info-tanggal"
                            class="font-semibold text-primary">—</span>
                    </p>
                </div>
                <button type="button" onclick="closeModal('modal-harian-edit')"
                    class="p-1 hover:bg-[#f1f5f9] rounded-lg transition">
                    <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="" class="p-6 space-y-4" id="form-harian-edit">
                @csrf
                @method('POST')
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Reksa Dana <span
                            class="text-red-500">*</span></label>
                    <select name="reksa_dana_id" id="edit-harian-rd" required
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/20">
                        @foreach ($reksaDanaOptions as $rd)
                            <option value="{{ $rd->id }}">
                                {{ $rd->kode_reksa_dana ? '[' . $rd->kode_reksa_dana . '] ' : '' }}{{ $rd->nama_reksa_dana }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Kode</label>
                        <input type="text" id="edit-harian-kode" readonly
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm bg-[#f8fafc] text-muted">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Tanggal <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" id="edit-harian-tanggal" required
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">NAB/UP <span
                                class="text-red-500">*</span></label>
                        <input type="number" step="0.000001" name="nab_per_unit" id="edit-harian-nab" required
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/20">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modal-harian-edit')"
                        class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-accent rounded-lg hover:bg-accent/90 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== JAVASCRIPT ===================== --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function openModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        }

        function closeModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        }

        // --- Event delegation: Parse Document buttons ---
        document.addEventListener('click', function(e) {
            const parseBtn = e.target.closest('.btn-parse-document');
            if (parseBtn) {
                const docId = parseBtn.dataset.parseDoc;
                const docName = parseBtn.dataset.parseName;
                const docType = parseBtn.dataset.parseType;
                const docCount = parseInt(parseBtn.dataset.parseCount) || 0;

                // FFS langsung parse tanpa modal
                if (docType === 'ffs') {
                    parseDocumentDirect(docId, docName, docType, docCount);
                    return;
                }

                document.getElementById('parse-doc-filename').textContent = docName;
                document.getElementById('parse-doc-id').value = docId;
                if (docCount > 0) {
                    document.getElementById('parse-badge').classList.remove('hidden');
                    document.getElementById('parse-badge-count').textContent = docCount;
                } else {
                    document.getElementById('parse-badge').classList.add('hidden');
                }
                document.getElementById('parse-result').classList.add('hidden');
                document.getElementById('parse-error').classList.add('hidden');
                document.getElementById('parse-success').classList.add('hidden');
                openModal('modal-document-parse');
                return;
            }

            const editBtn = e.target.closest('.btn-edit-document');
            if (editBtn) {
                const docId = editBtn.dataset.editDoc;
                const docName = editBtn.dataset.editName;
                const docType = editBtn.dataset.editType;
                const docMonth = editBtn.dataset.editFfsMonth;
                const docYear = editBtn.dataset.editFfsYear;
                const docNotes = editBtn.dataset.editNotes;
                document.getElementById('edit-doc-filename').textContent = docName;
                document.getElementById('form-document-edit').action = '{{ route('admin.daftar-reksa-dana.documents.update', '_docid_') }}'.replace('_docid_', docId);
                document.getElementById('edit-doc-notes').value = docNotes || '';
                const typeSelect = document.querySelector('#form-document-edit select[name="document_type"]');
                if (typeSelect) typeSelect.value = docType;
                if (docType === 'ffs') {
                    const ffsMonthEl = document.getElementById('edit-doc-ffs-month');
                    const ffsYearEl = document.getElementById('edit-doc-ffs-year');
                    const prospektusYearEl = document.getElementById('edit-doc-prospektus-year');
                    if (ffsMonthEl) ffsMonthEl.value = docMonth || '';
                    if (ffsYearEl) ffsYearEl.value = docYear || '';
                    if (prospektusYearEl) prospektusYearEl.value = docYear || '';
                } else {
                    const ffsMonthEl = document.getElementById('edit-doc-ffs-month');
                    const ffsYearEl = document.getElementById('edit-doc-ffs-year');
                    const prospektusYearEl = document.getElementById('edit-doc-prospektus-year');
                    if (ffsMonthEl) ffsMonthEl.value = '';
                    if (ffsYearEl) ffsYearEl.value = docYear || '';
                    if (prospektusYearEl) prospektusYearEl.value = docYear || '';
                }
                openModal('modal-document-edit');
                return;
            }
        });

        // --- Parse submit handler ---
        document.getElementById('btn-submit-parse').addEventListener('click', async function() {
            const btn = this;
            const form = document.getElementById('form-document-parse');
            const errorEl = document.getElementById('parse-error');
            const successEl = document.getElementById('parse-success');
            const loadingEl = document.getElementById('parse-loading-el');

            btn.disabled = true;
            btn.querySelector('span').textContent = 'Memproses...';
            errorEl.classList.add('hidden');
            successEl.classList.add('hidden');
            loadingEl.classList.remove('hidden');

            try {
                const formData = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': formData.get('_token') },
                    body: formData,
                });
                const json = await res.json();
                loadingEl.classList.add('hidden');
                if (!res.ok) {
                    errorEl.textContent = json.error || 'Gagal memparse dokumen.';
                    errorEl.classList.remove('hidden');
                    return;
                }
                document.getElementById('parse-result').classList.remove('hidden');
                document.getElementById('parse-result-count').textContent = json.data.parsed_count;
                document.getElementById('parse-result-total').textContent = json.data.total_pages;

                // Tampilkan info partisi yang dibuat
                const partitionInfoEl = document.getElementById('parse-result-partitions');
                if (partitionInfoEl) {
                    if (json.data.partitions_created > 0) {
                        partitionInfoEl.textContent = json.data.partitions_created + ' partisi dibuat dari daftar isi.';
                        partitionInfoEl.classList.remove('hidden');
                    } else {
                        partitionInfoEl.classList.add('hidden');
                    }
                }

                successEl.textContent = json.message;
                successEl.classList.remove('hidden');
                setTimeout(() => { window.location.reload(); }, 2000);
            } catch (e) {
                loadingEl.classList.add('hidden');
                errorEl.textContent = e.message;
                errorEl.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.querySelector('span').textContent = 'Parse';
            }
        });

        // --- Parse langsung tanpa modal (untuk FFS) ---
        async function parseDocumentDirect(docId, docName, docType, docCount) {
            if (!confirm('Parse dokumen FFS "' + docName + '" sekarang?')) return;

            const buttons = document.querySelectorAll('.btn-parse-document[data-parse-doc="' + docId + '"]');
            const originalHtml = [];
            buttons.forEach((btn, index) => {
                originalHtml[index] = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="text-[10px]">Memproses...</span>';
            });

            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('document_id', docId);

                const res = await fetch('{{ route('admin.daftar-reksa-dana.documents.parse') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData,
                });

                const json = await res.json();
                if (!res.ok) throw new Error(json.error || 'Gagal memparse dokumen.');

                alert(json.message);
                window.location.reload();
            } catch (e) {
                alert('Gagal parse FFS: ' + e.message);
            } finally {
                buttons.forEach((btn, index) => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml[index] || 'Parse';
                });
            }
        }

        function fillFromKode(kode, prefix) {
            const errorEl = document.getElementById(prefix + '-harga-kode-error');
            if (!kode || kode.length < 16) {
                errorEl.classList.add('hidden');
                return;
            }

            fetch('{{ route('admin.daftar-reksa-dana.parse-kode') }}?kode=' + encodeURIComponent(kode))
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        errorEl.textContent = data.error;
                        errorEl.classList.remove('hidden');
                        // Create: clear fields. Edit: biarkan fallback dari data
                        if (prefix === 'create') {
                            document.getElementById(prefix + '-harga-mi').value = '';
                            document.getElementById(prefix + '-harga-jenis').value = '';
                            document.getElementById(prefix + '-harga-kp').value = '';
                            document.getElementById(prefix + '-harga-kelas').value = '';
                            document.getElementById(prefix + '-harga-matauang').value = 'IDR';
                            document.getElementById(prefix + '-harga-kategori-display').textContent = '—';
                            document.getElementById(prefix + '-harga-kategori').value = '[]';
                        }
                        return;
                    }
                    errorEl.classList.add('hidden');

                    document.getElementById(prefix + '-harga-mi').value = data.nama_manajer_investasi || '';
                    document.getElementById(prefix + '-harga-jenis').value = data.jenis || '';
                    document.getElementById(prefix + '-harga-kp').value = data.kategori_produk || '';

                    // Hanya timpa kelas/mata_uang jika hasil parse valid (bukan default '-')
                    if (data.class_name && data.class_name !== '-' && data.class_name !== '—') {
                        document.getElementById(prefix + '-harga-kelas').value = data.class_name;
                    }
                    if (data.currency_name && data.currency_name !== '-' && data.currency_name !== '—') {
                        document.getElementById(prefix + '-harga-matauang').value = data.currency_name;
                    }

                    const kategori = Array.isArray(data.kategori) ? data.kategori : [];
                    document.getElementById(prefix + '-harga-kategori-display').textContent = kategori.length ? kategori
                        .join(', ') : '—';
                    document.getElementById(prefix + '-harga-kategori').value = JSON.stringify(kategori);
                })
                .catch(() => {});
        }

        function openEditHarga(data) {
            const form = document.getElementById('form-harga-edit');
            form.action = '{{ route('admin.daftar-reksa-dana.harga.update', 'REPLACE_ID') }}'.replace('REPLACE_ID', data
                .id);

            document.getElementById('edit-harga-kode').value = data.kode_reksa_dana || '';
            document.getElementById('edit-harga-nama').value = data.nama_reksa_dana;
            document.getElementById('edit-harga-benchmark').value = data.benchmark || '';
            document.getElementById('edit-harga-nab').value = data.nab_per_unit || '';
            document.getElementById('edit-harga-tgl-nab').value = (data.tanggal_nab || '').substring(0, 10);

            const kategori = Array.isArray(data.kategori) ? data.kategori : [];
            document.getElementById('edit-harga-kategori-display').textContent = kategori.length ? kategori.join(', ') :
            '—';
            document.getElementById('edit-harga-kategori').value = JSON.stringify(kategori);

            // Fallback: isi dari database dulu
            document.getElementById('edit-harga-mi').value = data.nama_manajer_investasi || '';
            document.getElementById('edit-harga-jenis').value = data.jenis || '';
            document.getElementById('edit-harga-kp').value = data.kategori_produk || '';
            const displayKelas = data.display_kelas || data.kelas || '';
            document.getElementById('edit-harga-kelas').value = displayKelas;
            const displayMataUang = data.display_mata_uang || data.mata_uang || 'IDR';
            document.getElementById('edit-harga-matauang').value = displayMataUang;

            // Generate dari parsing kode — timpa fallback jika kode valid
            if (data.kode_reksa_dana) {
                fillFromKode(data.kode_reksa_dana, 'edit');
            }

            openModal('modal-harga-edit');
        }

        function openEditHarian(data) {
            const form = document.getElementById('form-harian-edit');
            form.action = '{{ route('admin.daftar-reksa-dana.harian.update', 'REPLACE_ID') }}'.replace('REPLACE_ID', data
                .id);

            document.getElementById('edit-harian-rd').value = data.reksa_dana_id;
            document.getElementById('edit-harian-tanggal').value = (data.tanggal || '').substring(0, 10);
            document.getElementById('edit-harian-nab').value = data.nab_per_unit;
            document.getElementById('edit-harian-kode').value = data.reksa_dana?.kode_reksa_dana || '—';

            // Update header info juga
            document.getElementById('edit-harian-info-kode').textContent = data.reksa_dana?.kode_reksa_dana || '—';
            document.getElementById('edit-harian-info-tanggal').textContent = (data.tanggal || '').substring(0, 10);

            openModal('modal-harian-edit');
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.fixed.inset-0.z-50').forEach(m => {
                    if (!m.classList.contains('hidden')) m.classList.add('hidden');
                });
            }
        });
    });
    </script>

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
                <span x-show="syncStatus !== 'completed' && syncStatus !== 'failed'">Sinkronisasi RD dari Pasardana</span>
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
</div>

{{-- Modal Parse Dokumen --}}
<div id="modal-document-parse" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4" onclick="if(event.target===this)closeModal('modal-document-parse')">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-line sticky top-0 bg-white z-10">
            <div>
                <h3 class="font-bold text-primary">Parse Dokumen</h3>
                <p class="text-xs text-muted mt-0.5">Nama file: <span id="parse-doc-filename" class="font-semibold text-primary">—</span></p>
            </div>
            <button type="button" onclick="closeModal('modal-document-parse')" class="p-1 hover:bg-[#f1f5f9] rounded-lg transition">
                <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-sm text-muted">Proses ini mengekstrak teks dari setiap halaman PDF setelah daftar isi. Hasil disimpan ke database.</p>
            <div id="parse-loading-el" class="hidden text-xs text-muted">Menghitung total halaman...</div>
            <span id="parse-badge" class="hidden inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-semibold">Sudah diparse: <span id="parse-badge-count">0</span> halaman</span>
            <form id="form-document-parse" action="{{ route('admin.daftar-reksa-dana.documents.parse') }}" method="POST" onsubmit="return false;">
                @csrf
                <input type="hidden" name="document_id" id="parse-doc-id" value="">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Halaman Daftar Isi Mulai *</label>
                        <input type="number" name="toc_start_page" min="1" value="1" required class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Halaman Daftar Isi Selesai *</label>
                        <input type="number" name="toc_end_page" min="1" value="4" required class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="flex items-start gap-2 pt-1">
                        <input type="checkbox" name="generate_partitions" id="generate_partitions" value="1" checked class="mt-0.5 w-4 h-4 text-emerald-700 border-line rounded focus:ring-emerald-700">
                        <label for="generate_partitions" class="text-sm text-primary cursor-pointer select-none">
                            Otomatis buat partisi dari daftar isi (AI)
                            <span class="block text-xs text-muted font-normal">AI akan membaca daftar isi dan membuat partisi per bab secara otomatis.</span>
                        </label>
                    </div>
                </div>
                <div id="parse-error" class="hidden mt-4 px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700"></div>
                <div id="parse-success" class="hidden mt-4 px-4 py-3 rounded-xl text-sm bg-green-50 border border-green-200 text-green-700"></div>
                <div id="parse-result" class="hidden mt-4 bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-emerald-700">Parse Selesai</p>
                    <p class="text-xs text-muted mt-1"><span id="parse-result-count">0</span> halaman teks dari <span id="parse-result-total">0</span> total halaman PDF.</p>
                    <p id="parse-result-partitions" class="text-xs text-emerald-700 font-medium mt-1 hidden"></p>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeModal('modal-document-parse')" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</button>
                    <button type="button" id="btn-submit-parse" class="px-4 py-2 text-sm text-white bg-emerald-700 rounded-lg hover:bg-emerald-800 transition disabled:opacity-50"><span>Parse</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
