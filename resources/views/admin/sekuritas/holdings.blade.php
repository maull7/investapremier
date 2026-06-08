@extends('layouts.admin')

@section('title', $type === 'efek' ? "Reksa Dana Pemegang {$kode}" : "Reksa Dana Pemegang {$kode}")

@section('content')
<div x-data="{}">

    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-muted mb-1">
            <a href="{{ $type === 'efek' ? route('admin.saham.index') : route('admin.sekuritas-informasi.index', ['tab' => request('tab', 'government')]) }}"
                class="hover:text-primary transition">
                {{ $type === 'efek' ? 'Daftar Saham' : 'Sekuritas Informasi' }}
            </a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-primary font-semibold">{{ $kode }}</span>
        </div>
        <h1 class="page-title">Reksa Dana Pemegang {{ $type === 'efek' ? 'Saham' : 'Obligasi' }}: {{ $kode }}</h1>
        <p class="page-sub">{{ $nama }}</p>
    </div>

    <div class="mb-5">
        <form method="GET">
            <div class="flex items-center gap-3">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full pl-10 pr-4 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="Cari nama reksa dana...">
                </div>
                <button type="submit"
                    class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Cari</button>
                @if (request('search'))
                    <a href="{{ route($type === 'efek' ? 'admin.sekuritas.efek' : 'admin.sekuritas.obligasi', $kode) }}"
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
                Reksa Dana dengan {{ $type === 'efek' ? 'Saham' : 'Obligasi' }} {{ $kode }}
            </h2>
            <div class="flex items-center gap-2">
                <span class="th-meta">Tampilkan:</span>
                <form method="GET">
                    @if (request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    <select name="per_page" onchange="this.form.submit()"
                        class="text-xs bg-white text-slate-500 border border-white/20 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-secondary/50">
                        @foreach ([10, 25, 50] as $n)
                            <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </form>
                <span class="th-meta">{{ $holdings->total() }} total</span>
            </div>
        </div>

        @if ($holdings->isEmpty())
            <div class="py-16 text-center text-muted">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="font-medium">Tidak ditemukan</p>
                <p class="text-sm mt-1">Tidak ada reksa dana yang memiliki {{ $type === 'efek' ? 'saham' : 'obligasi' }} ini</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                            <th class="px-4 py-3.5 font-semibold">Kode RD</th>
                            <th class="px-4 py-3.5 font-semibold">Nama Reksa Dana</th>
                            <th class="px-4 py-3.5 font-semibold text-right">
                                {{ $type === 'efek' ? 'Kode Efek' : 'Kode Obligasi' }}
                            </th>
                            <th class="px-4 py-3.5 font-semibold text-right">Bobot (%)</th>
                            <th class="px-4 py-3.5 font-semibold text-right">Nilai Pasar</th>
                            <th class="px-4 py-3.5 font-semibold text-right">% NAB</th>
                            <th class="px-4 py-3.5 font-semibold text-right">Tanggal Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($holdings as $h)
                            <tr class="hover:bg-[#f8fafc] transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs font-semibold">{{ $h->analisa?->kode_reksa_dana ?: '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-primary leading-snug">{{ $h->analisa?->nama_reksa_dana ?: '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-xs text-muted font-semibold">{{ $type === 'efek' ? $h->kode_efek : $h->kode_obligasi }}</span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">
                                    {{ $h->bobot !== null ? number_format($h->bobot, 3, ',', '.') . '%' : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ $h->nilai_pasar ? 'Rp' . number_format($h->nilai_pasar, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right text-xs">
                                    {{ $h->persen_nab !== null ? number_format($h->persen_nab, 3, ',', '.') . '%' : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right text-xs text-muted">
                                    {{ $h->analisa?->tanggal_data ? $h->analisa->tanggal_data->format('d/m/Y') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($holdings->hasPages())
                <div class="px-6 py-4 border-t border-line flex items-center justify-between gap-4 text-sm">
                    <p class="text-muted text-xs">
                        Menampilkan {{ $holdings->firstItem() }}–{{ $holdings->lastItem() }} dari {{ $holdings->total() }} data
                    </p>
                    <div class="flex items-center gap-1">
                        @if ($holdings->onFirstPage())
                            <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
                        @else
                            <a href="{{ $holdings->previousPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
                        @endif

                        @php
                            $current = $holdings->currentPage();
                            $last = $holdings->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp
                        @if ($start > 1)
                            <a href="{{ $holdings->url(1) }}"
                                class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                            @if ($start > 2)
                                <span class="px-1 text-muted text-xs">…</span>
                            @endif
                        @endif
                        @foreach ($holdings->getUrlRange($start, $end) as $page => $url)
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
                            <a href="{{ $holdings->url($last) }}"
                                class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                        @endif

                        @if ($holdings->hasMorePages())
                            <a href="{{ $holdings->nextPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
                        @else
                            <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next →</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
