@extends('layouts.admin')

@section('title', 'Daftar Saham - InvestaPremier')

@section('content')
    <div x-data="{
        deleteId: null,
        deleteText: '',
        showImport: false,
        showExtraction: false,
        isSyncing: false,
        syncStep: 0,
        startSync() {
            this.isSyncing = true;
            this.syncStep = 0;
            this._t1 = setTimeout(() => { this.syncStep = 1 }, 15000);
            this._t2 = setTimeout(() => { this.syncStep = 2 }, 30000);
        }
    }">

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="page-title">Daftar Saham</h1>
                <p class="page-sub">Kelola data saham yang tercatat di bursa</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.saham.template') }}" class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Template Excel
                </a>
                <button @click="showImport = true" class="btn-outline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Import Excel
                </button>
                {{-- <button @click="showExtraction = true"
                    class="btn-outline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4h18M6 8h12m-9 4h6m-8 4h10m-7 4h4" />
                    </svg>
                    Ekstrak Data
                </button> --}}
                <form method="POST" action="{{ route('admin.saham.sync-idx') }}" @submit="startSync()">
                    @csrf
                    <button type="submit" class="btn-outline" :disabled="isSyncing"
                        :class="isSyncing ? 'opacity-50 cursor-not-allowed' : ''"
                        title="Tarik master saham + harga real-time dari IDX dan langsung simpan ke database">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            :class="isSyncing ? 'animate-spin' : ''">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sync dari IDX
                    </button>
                </form>
                <a href="{{ route('admin.saham.create') }}" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Saham
                </a>
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
            <div
                class="mb-5 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-5">
            <div class="flex items-center gap-1 bg-[#f1f5f9] rounded-xl p-1 w-fit">
                <a href="{{ route('admin.saham.index', ['tab' => 'daftar']) }}"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'daftar' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
                    Daftar Saham
                </a>
                <a href="{{ route('admin.saham.index', ['tab' => 'hasil-ekstrak']) }}"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'hasil-ekstrak' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
                    Hasil Ekstrak
                </a>
            </div>
        </div>

        @if ($tab === 'daftar')
            {{-- Search --}}
            <div class="mb-5">
                <form method="GET" action="{{ route('admin.saham.index') }}">
                    <input type="hidden" name="tab" value="daftar">
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1 max-w-md">
                            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="w-full pl-10 pr-4 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                                placeholder="Cari kode, nama, atau sektor...">
                        </div>
                        <button type="submit"
                            class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Cari</button>
                        @if (request('search'))
                            <a href="{{ route('admin.saham.index') }}"
                                class="px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="table-card">
                <div class="table-head">
                    <h2 class="th-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Daftar Saham
                    </h2>
                    <div class="flex items-center gap-2">
                        <span class="th-meta">Tampilkan:</span>
                        <form method="GET" action="{{ route('admin.saham.index') }}">
                            @if (request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            <select name="per_page" onchange="this.form.submit()"
                                class="text-xs bg-white text-slate-500 border border-white/20 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-secondary/50">
                                @foreach ([10, 25, 50] as $n)
                                    <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>
                                        {{ $n }}</option>
                                @endforeach
                            </select>
                        </form>
                        <span class="th-meta">{{ $stocks->total() }} total</span>
                    </div>
                </div>

                @if ($stocks->isEmpty())
                    <div class="py-16 text-center text-muted">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <p class="font-medium">Belum ada data saham</p>
                        <p class="text-sm mt-1">Klik "Tambah Saham" atau import dari Excel untuk mulai mengisi data</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                                    <th class="px-4 py-3.5 font-semibold">Kode</th>
                                    <th class="px-4 py-3.5 font-semibold">Nama</th>
                                    <th class="px-4 py-3.5 font-semibold">Sektor</th>
                                    <th class="px-4 py-3.5 font-semibold text-right">Harga Terbaru</th>
                                    <th class="px-4 py-3.5 font-semibold text-right">Perubahan</th>
                                    <th class="px-4 py-3.5 font-semibold text-right">Volume</th>
                                    <th class="px-4 py-3.5 font-semibold text-right">Market Cap</th>
                                    <th class="px-4 py-3.5 font-semibold text-right">Last Update</th>
                                    <th class="px-4 py-3.5 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($stocks as $s)
                                    @php
                                        $change =
                                            $s->harga_terbaru && $s->harga_penutupan_sebelumnya
                                                ? $s->harga_terbaru - $s->harga_penutupan_sebelumnya
                                                : null;
                                        $changePct =
                                            $change && $s->harga_penutupan_sebelumnya
                                                ? ($change / $s->harga_penutupan_sebelumnya) * 100
                                                : null;
                                        $isUp = $change && $change > 0;
                                    @endphp
                                    <tr class="hover:bg-[#f8fafc] transition-colors">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.sekuritas.efek', $s->kode) }}"
                                                class="w-14 h-7 rounded-lg bg-primary/10 text-primary font-bold text-xs grid place-items-center hover:bg-primary/20 transition">
                                                {{ $s->kode }}</a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.saham.show', $s) }}"
                                                class="font-semibold text-primary leading-snug hover:underline">{{ $s->nama }}</a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-xs text-muted">{{ $s->sektor }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold">
                                            {{ $s->harga_terbaru ? number_format($s->harga_terbaru, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if ($change !== null)
                                                <span
                                                    class="inline-flex items-center gap-1 text-xs font-semibold {{ $isUp ? 'text-green-600' : 'text-red-600' }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="{{ $isUp ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}" />
                                                    </svg>
                                                    {{ number_format(abs($change), 0, ',', '.') }}
                                                    ({{ number_format(abs($changePct), 2) }}%)
                                                </span>
                                            @else
                                                <span class="text-xs text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right text-xs text-muted">
                                            {{ $s->volume ? number_format($s->volume, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-xs text-muted">
                                            {{ $s->market_capital ? 'Rp' . number_format($s->market_capital, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-xs text-muted">
                                            {{ $s->last_update ? $s->last_update->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <a href="{{ route('admin.saham.edit', $s) }}"
                                                    class="p-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition"
                                                    title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                <button type="button"
                                                    @click="deleteId = {{ $s->id }}; deleteText = '{{ addslashes($s->kode . ' - ' . Str::limit($s->nama, 60)) }}'"
                                                    class="p-2 rounded-lg text-muted hover:text-red-500 hover:bg-red-50 transition"
                                                    title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if ($stocks->hasPages())
                        <div class="px-6 py-4 border-t border-line flex items-center justify-between gap-4 text-sm">
                            <p class="text-muted text-xs">
                                Menampilkan {{ $stocks->firstItem() }}–{{ $stocks->lastItem() }} dari
                                {{ $stocks->total() }}
                                saham
                            </p>
                            <div class="flex items-center gap-1">
                                @if ($stocks->onFirstPage())
                                    <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">←
                                        Prev</span>
                                @else
                                    <a href="{{ $stocks->previousPageUrl() }}"
                                        class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">←
                                        Prev</a>
                                @endif

                                @php
                                    $current = $stocks->currentPage();
                                    $last = $stocks->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $current + 2);
                                @endphp
                                @if ($start > 1)
                                    <a href="{{ $stocks->url(1) }}"
                                        class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                                    @if ($start > 2)
                                        <span class="px-1 text-muted text-xs">…</span>
                                    @endif
                                @endif
                                @foreach ($stocks->getUrlRange($start, $end) as $page => $url)
                                    <a href="{{ $url }}"
                                        class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition
                      {{ $page == $current ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">
                                        {{ $page }}
                                    </a>
                                @endforeach
                                @if ($end < $last)
                                    @if ($end < $last - 1)
                                        <span class="px-1 text-muted text-xs">…</span>
                                    @endif
                                    <a href="{{ $stocks->url($last) }}"
                                        class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                                @endif

                                @if ($stocks->hasMorePages())
                                    <a href="{{ $stocks->nextPageUrl() }}"
                                        class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next
                                        →</a>
                                @else
                                    <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next
                                        →</span>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        @else
            <div class="grid lg:grid-cols-[minmax(0,1fr)_minmax(420px,0.9fr)] gap-5">
                <div class="table-card">
                    <div class="table-head">
                        <h2 class="th-title">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                            Batch Ekstraksi
                        </h2>
                        <span class="th-meta">{{ $extractionBatches->total() }} total</span>
                    </div>

                    @if ($extractionBatches->isEmpty())
                        <div class="py-16 text-center text-muted">
                            <p class="font-medium">Belum ada hasil ekstrak</p>
                            <p class="text-sm mt-1">Klik "Ekstrak Data" untuk membuat batch baru.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                                        <th class="px-4 py-3.5 font-semibold">Batch</th>
                                        <th class="px-4 py-3.5 font-semibold">Jenis</th>
                                        <th class="px-4 py-3.5 font-semibold">Rentang</th>
                                        <th class="px-4 py-3.5 font-semibold">Sumber</th>
                                        <th class="px-4 py-3.5 font-semibold">Tanggal</th>
                                        <th class="px-4 py-3.5 font-semibold">Status</th>
                                        <th class="px-4 py-3.5 font-semibold text-right">Records</th>
                                        <th class="px-4 py-3.5 font-semibold text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @foreach ($extractionBatches as $batch)
                                        @php
                                            $statusClass = match ($batch->status) {
                                                'success' => 'bg-green-50 text-green-700 border-green-200',
                                                'failed' => 'bg-red-50 text-red-700 border-red-200',
                                                'processing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                default => 'bg-slate-50 text-slate-600 border-slate-200',
                                            };
                                            $typeLabel = 'Transaksi Harian Saham';
                                        @endphp
                                        <tr class="hover:bg-[#f8fafc] transition-colors">
                                            <td class="px-4 py-3 font-semibold text-primary">#{{ $batch->id }}</td>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-primary">{{ $typeLabel }}</div>
                                                <div class="text-xs text-muted">Semua saham</div>
                                            </td>
                                            <td class="px-4 py-3 text-muted">
                                                {{ $batch->range_label ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-muted">{{ $batch->source }}</td>
                                            <td class="px-4 py-3 text-muted">
                                                {{ $batch->data_date?->format('d/m/Y') ?: '-' }}</td>
                                            <td class="px-4 py-3">
                                                <span
                                                    class="inline-flex px-2.5 py-1 rounded-full border text-xs font-semibold {{ $statusClass }}">
                                                    {{ ucfirst($batch->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right">{{ number_format($batch->total_records) }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('admin.saham.index', ['tab' => 'hasil-ekstrak', 'detail_batch' => $batch->id]) }}"
                                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-primary bg-primary/10 hover:bg-primary/15 transition">
                                                        Lihat Detail
                                                    </a>
                                                    <form method="POST"
                                                        action="{{ route('admin.saham.extraction-batches.retry', $batch) }}">
                                                        @csrf
                                                        <button
                                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-muted border border-line hover:text-primary transition">
                                                            Retry
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($extractionBatches->hasPages())
                            <div class="px-6 py-4 border-t border-line">
                                {{ $extractionBatches->links() }}
                            </div>
                        @endif
                    @endif
                </div>

                <div class="table-card">
                    <div class="table-head">
                        <h2 class="th-title">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12H9m12 0A9 9 0 113 12a9 9 0 0118 0z" />
                            </svg>
                            Preview Hasil
                        </h2>
                        @if ($detailBatch)
                            <span class="th-meta">Batch #{{ $detailBatch->id }}</span>
                        @endif
                    </div>

                    @if (!$detailBatch)
                        <div class="py-16 text-center text-muted">
                            <p class="font-medium">Pilih batch untuk melihat preview</p>
                        </div>
                    @else
                        <div class="p-4 border-b border-line">
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($detailBatch->status === 'success')
                                    <form method="POST"
                                        action="{{ route('admin.saham.extraction-batches.save', $detailBatch) }}"
                                        class="flex items-center gap-2">
                                        @csrf
                                        <select name="duplicate_action"
                                            class="text-xs border border-line rounded-lg px-2 py-2 focus:outline-none focus:ring-2 focus:ring-accent/30">
                                            <option value="skip">Skip duplicate</option>
                                            <option value="update">Update duplicate</option>
                                        </select>
                                        <button
                                            class="px-3 py-2 bg-accent text-white rounded-lg text-xs font-semibold hover:bg-accent/90 transition">
                                            Simpan ke Database
                                        </button>
                                    </form>
                                @endif
                                <form method="POST"
                                    action="{{ route('admin.saham.extraction-batches.destroy', $detailBatch) }}">
                                    @csrf @method('DELETE')
                                    <button
                                        class="px-3 py-2 border border-red-200 text-red-600 rounded-lg text-xs font-semibold hover:bg-red-50 transition">
                                        Hapus Hasil Ekstrak
                                    </button>
                                </form>
                            </div>
                            @if ($detailBatch->error_message)
                                <p class="mt-3 text-xs text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2">
                                    {{ $detailBatch->error_message }}
                                </p>
                            @endif
                        </div>

                        <div class="overflow-x-auto max-h-[520px]">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="bg-[#f8fafc] text-left text-muted uppercase">
                                        <th class="px-3 py-2">Kode</th>
                                        <th class="px-3 py-2">Tanggal</th>
                                        <th class="px-3 py-2 text-right">Open</th>
                                        <th class="px-3 py-2 text-right">High</th>
                                        <th class="px-3 py-2 text-right">Low</th>
                                        <th class="px-3 py-2 text-right">Close</th>
                                        <th class="px-3 py-2 text-right">Volume</th>
                                        <th class="px-3 py-2">Source</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @forelse ($detailBatch->stockDailyTransactions as $row)
                                        <tr>
                                            <td class="px-3 py-2 font-semibold">{{ $row->stock_code }}</td>
                                            <td class="px-3 py-2">{{ $row->data_date?->format('d/m/Y') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format((float) $row->open, 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right">{{ number_format((float) $row->high, 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right">{{ number_format((float) $row->low, 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right">{{ number_format((float) $row->close, 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                {{ $row->volume ? number_format($row->volume) : '-' }}</td>
                                            <td class="px-3 py-2">{{ $row->source }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-3 py-6 text-center text-muted" colspan="8">Tidak ada detail.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Modal Import Excel --}}
        <div x-show="showImport" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/40" @click="showImport = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
                x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <h3 class="font-bold text-primary text-base mb-1">Import Saham dari Excel</h3>
                <p class="text-muted text-sm mb-4">Upload file Excel sesuai format template. Data dengan kode yang sama
                    akan diperbarui.</p>

                <form method="POST" action="{{ route('admin.saham.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div
                        class="border-2 border-dashed border-line rounded-xl p-6 text-center mb-4 hover:border-accent/40 transition">
                        <svg class="w-8 h-8 mx-auto text-muted mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <label class="cursor-pointer">
                            <span class="text-sm font-semibold text-accent">Pilih file</span>
                            <span class="text-sm text-muted"> atau drag & drop</span>
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden" required>
                        </label>
                        <p class="text-xs text-muted mt-1">Format: .xlsx, .xls, .csv</p>
                    </div>
                    @error('file')
                        <p class="text-red-500 text-xs mb-3">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center justify-between">
                        <a href="{{ route('admin.saham.template') }}"
                            class="text-xs text-accent hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download template
                        </a>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="showImport = false"
                                class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition">
                                Upload & Import
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Ekstrak Data Saham --}}
        <div x-show="showExtraction" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/40" @click="showExtraction = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
                x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <h3 class="font-bold text-primary text-base mb-1">Ekstrak Transaksi Saham</h3>
                <p class="text-muted text-sm mb-4">Semua kode saham akan diproses lewat Horizon. IDX Market dicoba lebih
                    dulu jika endpoint tersedia, lalu fallback ke Yahoo Finance.</p>

                <form method="POST" action="{{ route('admin.saham.extraction-batches.store') }}">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1.5">Tanggal data</label>
                        <input type="date" name="data_date" value="{{ now()->toDateString() }}"
                            class="w-full border border-line rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30"
                            required>
                    </div>
                    <div class="mt-4">
                        <label class="block text-xs font-semibold text-primary mb-1.5">Rentang data</label>
                        <select name="range"
                            class="w-full border border-line rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30"
                            required>
                            @foreach ($extractionRanges as $range)
                                <option value="{{ $range['value'] }}">{{ $range['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-4 rounded-xl border border-line bg-[#f8fafc] px-4 py-3 text-xs text-muted">
                        Data masuk tabel staging dulu. Database utama baru berubah setelah tombol Simpan ke Database diklik
                        dari tab Hasil Ekstrak.
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button type="button" @click="showExtraction = false"
                            class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition">
                            Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Konfirmasi Hapus --}}
        <div x-show="deleteId !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/40" @click="deleteId = null"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
                x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 grid place-items-center shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-primary text-base">Hapus Saham?</h3>
                        <p class="page-sub">Data saham berikut akan dihapus permanen:</p>
                        <p class="mt-2 text-sm text-primary font-medium bg-[#f8fafc] rounded-lg px-3 py-2 border border-line"
                            x-text="deleteText"></p>
                        <p class="text-xs text-red-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" @click="deleteId = null"
                        class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
                        Batal
                    </button>
                    <form method="POST" :action="`/admin/saham/${deleteId}`">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Loading: Sync dari IDX --}}
        <div x-show="isSyncing" x-cloak
            class="fixed inset-0 z-[60] bg-white/95 backdrop-blur-sm grid place-items-center px-4"
            x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div class="text-center max-w-md">
                <div class="w-14 h-14 border-4 border-accent border-t-transparent rounded-full mx-auto mb-6 animate-spin">
                </div>
                <h3 class="text-lg font-bold text-primary mb-1">Sinkronisasi dari IDX</h3>
                <p class="text-sm text-muted mb-5">Tarik data master saham + harga real-time dan simpan ke database.</p>

                <ol class="text-left space-y-2 max-w-sm mx-auto">
                    <li class="flex items-center gap-3 text-sm">
                        <span class="w-6 h-6 rounded-full grid place-items-center text-[11px] font-bold shrink-0"
                            :class="syncStep > 0 ? 'bg-green-100 text-green-700' : (syncStep === 0 ? 'bg-accent text-white' :
                                'bg-slate-100 text-muted')">
                            <template x-if="syncStep > 0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </template>
                            <template x-if="syncStep <= 0"><span>1</span></template>
                        </span>
                        <span
                            :class="syncStep === 0 ? 'text-primary font-semibold' : (syncStep > 0 ? 'text-muted line-through' :
                                'text-muted')">
                            Mengambil master daftar saham IDX
                        </span>
                    </li>
                    <li class="flex items-center gap-3 text-sm">
                        <span class="w-6 h-6 rounded-full grid place-items-center text-[11px] font-bold shrink-0"
                            :class="syncStep > 1 ? 'bg-green-100 text-green-700' : (syncStep === 1 ? 'bg-accent text-white' :
                                'bg-slate-100 text-muted')">
                            <template x-if="syncStep > 1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </template>
                            <template x-if="syncStep <= 1"><span>2</span></template>
                        </span>
                        <span
                            :class="syncStep === 1 ? 'text-primary font-semibold' : (syncStep > 1 ? 'text-muted line-through' :
                                'text-muted')">
                            Mengambil data harga &amp; volume hari ini
                        </span>
                    </li>
                    <li class="flex items-center gap-3 text-sm">
                        <span class="w-6 h-6 rounded-full grid place-items-center text-[11px] font-bold shrink-0"
                            :class="syncStep === 2 ? 'bg-accent text-white' : 'bg-slate-100 text-muted'">
                            <span>3</span>
                        </span>
                        <span :class="syncStep === 2 ? 'text-primary font-semibold' : 'text-muted'">
                            Menyimpan ke database
                        </span>
                    </li>
                </ol>

                <p class="text-xs text-muted mt-6">Proses ini memakan waktu 30-60 detik. Jangan tutup tab ini.</p>
            </div>
        </div>

    </div>
@endsection
