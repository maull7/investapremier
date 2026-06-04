@extends('layouts.user')

@section('title', 'Daftar Saham - InvestaPremier')

@section('content')
    <div x-data="{ deleteId: null, deleteText: '', showImport: false }">

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="page-title">Daftar Saham</h1>
                <p class="page-sub">Informasi saham yang tercatat di bursa</p>
            </div>
            {{-- <div class="flex items-center gap-2">
        <a href="{{ route('user.saham.template') }}"
           class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Template Excel
        </a>
        <button @click="showImport = true"
                class="btn-outline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import Excel
        </button>
        <a href="{{ route('user.saham.create') }}"
           class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Saham
        </a>
    </div> --}}
        </div>

        @if (session('success'))
            <div
                class="alert-success">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Search --}}
        <div class="mb-5">
            <form method="GET" action="{{ route('user.saham.index') }}">
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
                        <a href="{{ route('user.saham.index') }}"
                            class="px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-card">
            <div
                class="table-head">
                <h2 class="th-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Daftar Saham
                </h2>
                <div class="flex items-center gap-2">
                    <span class="th-meta">Tampilkan:</span>
                    <form method="GET" action="{{ route('user.saham.index') }}">
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
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <div
                                            class="w-14 h-7 rounded-lg bg-primary/10 text-primary font-bold text-xs grid place-items-center">
                                            {{ $s->kode }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('user.saham.show', $s) }}"
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
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
                                            <a href="{{ route('user.saham.edit', $s) }}"
                                                class="p-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <button type="button"
                                                @click="deleteId = {{ $s->id }}; deleteText = '{{ addslashes($s->kode . ' - ' . Str::limit($s->nama, 60)) }}'"
                                                class="p-2 rounded-lg text-muted hover:text-red-500 hover:bg-red-50 transition"
                                                title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
                            Menampilkan {{ $stocks->firstItem() }}–{{ $stocks->lastItem() }} dari {{ $stocks->total() }}
                            saham
                        </p>
                        <div class="flex items-center gap-1">
                            @if ($stocks->onFirstPage())
                                <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
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
                                <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next →</span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>

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

                {{-- <form method="POST" action="{{ route('user.saham.import') }}" enctype="multipart/form-data">
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
                <a href="{{ route('user.saham.template') }}" class="text-xs text-accent hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
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
        </form> --}}
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
                    <form method="POST" :action="`/user/saham/${deleteId}`">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
