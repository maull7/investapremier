@extends('layouts.admin')

@section('title', 'Daftar Reksa Dana')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="page-title">Daftar Reksa Dana</h1>
        <p class="page-sub">Seluruh reksa dana yang tersedia di platform</p>
    </div>
</div>

@if(session('success'))
<div class="alert-success">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

<form method="GET" action="{{ route('admin.reksa-dana.index') }}" id="filter-form" class="hidden"></form>

<form method="POST" action="{{ route('admin.reksa-dana.bulk-analisa') }}" x-data="{ checked: [] }">
    @csrf

    <div class="table-card">
        {{-- Header + Tombol Analisa FFS --}}
        <div class="table-head">
            <h2 class="th-title">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Daftar Reksa Dana
            </h2>
            <div class="flex items-center gap-3">
                <span class="th-meta">{{ $reksaDanas->total() }} total</span>
                <button type="submit"
                    x-bind:disabled="checked.length === 0"
                    x-bind:class="checked.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-accent/90'"
                    class="flex items-center gap-2 px-4 py-2 bg-accent text-white rounded-lg text-sm font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Analisa FFS (<span x-text="checked.length">0</span>)
                </button>
            </div>
        </div>

        {{-- Filter Jenis --}}
        <div class="px-6 py-3 border-b border-line">
            <div class="flex items-center gap-2 text-xs mb-2">
                <span class="font-semibold text-muted">Jenis:</span>
                <a href="{{ route('admin.reksa-dana.index') }}"
                   class="px-3 py-1.5 rounded-lg border transition {{ !request('jenis') ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                   Semua
                </a>
            </div>
            <div class="flex gap-2 text-xs flex-wrap">
                @foreach($jenisOptions ?? ['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'] as $j)
                <label class="flex items-center gap-1.5 cursor-pointer px-3 py-1.5 rounded-lg border transition has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary border-line text-muted hover:bg-[#f1f5f9]">
                    <input type="checkbox" name="jenis[]" value="{{ $j }}"
                        {{ in_array($j, (array) request('jenis')) ? 'checked' : '' }}
                        class="sr-only" form="filter-form"
                        onchange="document.getElementById('filter-form')?.submit();">
                    {{ $j }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Filter Kategori --}}
        <div class="px-6 py-3 border-b border-line">
            <div class="flex items-center gap-2 text-xs mb-2">
                <span class="font-semibold text-muted">Kategori:</span>
                <a href="{{ route('admin.reksa-dana.index') }}"
                   class="px-3 py-1.5 rounded-lg border transition {{ !request('kategori') ? 'bg-accent text-white border-accent' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                   Semua
                </a>
            </div>
            <div class="flex gap-2 text-xs flex-wrap">
                @foreach($kategoriOptions ?? ['Konvensional', 'Syariah', 'index', 'ETF'] as $k)
                <label class="flex items-center gap-1.5 cursor-pointer px-3 py-1.5 rounded-lg border transition has-[:checked]:bg-accent has-[:checked]:text-white has-[:checked]:border-accent border-line text-muted hover:bg-[#f1f5f9]">
                    <input type="checkbox" name="kategori[]" value="{{ $k }}"
                        {{ in_array($k, (array) request('kategori')) ? 'checked' : '' }}
                        class="sr-only" form="filter-form"
                        onchange="document.getElementById('filter-form')?.submit();">
                    {{ $k }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                        <th class="px-4 py-3.5 w-10">
                            <input type="checkbox" class="rounded border-line"
                                @change="checked = $event.target.checked ? [...$el.closest('table').querySelectorAll('input[name=\'ids[]\']')].map(i => i.value) : []">
                        </th>
                        <th class="px-4 py-3.5 font-semibold">Nama Reksa Dana</th>
                        <th class="px-4 py-3.5 font-semibold">Jenis</th>
                        <th class="px-4 py-3.5 font-semibold">Kategori</th>
                        <th class="px-4 py-3.5 font-semibold">Mata Uang</th>
                        <th class="px-4 py-3.5 font-semibold">Tanggal Data</th>
                        <th class="px-4 py-3.5 font-semibold text-right">AUM</th>
                        <th class="px-4 py-3.5 font-semibold text-right">UP</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Return 1M</th>
                        <th class="px-4 py-3.5 font-semibold">Status AI</th>
                        <th class="px-4 py-3.5 font-semibold text-center">PDF FFS</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse($reksaDanas as $rd)
                    <tr class="hover:bg-[#f8fafc] transition-colors">
                        <td class="px-4 py-3.5">
                            @if($rd->pdf_path)
                            <input type="checkbox" name="ids[]" value="{{ $rd->id }}"
                                x-model="checked" class="rounded border-line">
                            @else
                            <input type="checkbox" disabled class="rounded border-line opacity-30" title="Tidak ada PDF FFS">
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-primary">{{ $rd->nama_reksa_dana }}</div>
                            <div class="text-xs text-muted">{{ $rd->user->name }}</div>
                        </td>
                        <td class="px-4 py-3.5">
                            @php
                                $jenisColor = match($rd->jenis_reksa_dana) {
                                    'Saham' => 'bg-blue-100 text-blue-700',
                                    'Pendapatan Tetap' => 'bg-amber-100 text-amber-700',
                                    'Campuran' => 'bg-purple-100 text-purple-700',
                                    default => 'bg-green-100 text-green-700',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $jenisColor }}">{{ $rd->jenis_reksa_dana }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-muted text-xs">
                            @if($rd->kategori)
                                <div class="flex flex-wrap gap-1">
                                    @foreach((array)$rd->kategori as $kat)
                                        <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded text-xs">{{ $kat }}</span>
                                    @endforeach
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-muted text-xs">{{ $rd->display_mata_uang }}</td>
                        <td class="px-4 py-3.5 text-muted text-xs">{{ $rd->tanggal_data ? $rd->tanggal_data->format('d/m/Y') : '—' }}</td>
                        <td class="px-4 py-3.5 text-right text-xs text-muted">
                            {{ $rd->total_aum ? 'Rp ' . number_format($rd->total_aum, 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-right text-xs text-muted">
                            {{ $rd->unit_penyertaan ? number_format($rd->unit_penyertaan, 2, ',', '.') : '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-right text-xs font-semibold">
                            @if($rd->return_1m !== null)
                                <span class="{{ $rd->return_1m >= 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $rd->return_1m >= 0 ? '+' : '' }}{{ number_format($rd->return_1m, 2) }}%
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            @if($rd->ai_narasi)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Selesai</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Belum</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($rd->pdf_path)
                                <a href="{{ route('admin.reksa-dana.pdf', $rd) }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-semibold hover:bg-red-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    PDF
                                </a>
                            @else
                                <span class="text-muted text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <a href="{{ route('admin.analisa.show', $rd) }}"
                               class="px-3 py-1.5 border border-line text-muted rounded-lg text-xs font-semibold hover:text-primary hover:border-primary/30 transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-6 py-12 text-center text-muted">
                            <p class="font-medium">Belum ada data reksa dana</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reksaDanas->hasPages())
        <div class="px-6 py-4 border-t border-line flex items-center justify-between text-sm">
            <p class="text-muted text-xs">Menampilkan {{ $reksaDanas->firstItem() }}–{{ $reksaDanas->lastItem() }} dari {{ $reksaDanas->total() }}</p>
            <div class="flex items-center gap-1">
                @if(!$reksaDanas->onFirstPage())
                <a href="{{ $reksaDanas->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
                @endif
                @php $cur=$reksaDanas->currentPage();$last=$reksaDanas->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
                @if($s>1)
                    <a href="{{ $reksaDanas->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                    @if($s>2)<span class="px-1 text-muted text-xs">…</span>@endif
                @endif
                @foreach($reksaDanas->getUrlRange($s,$e) as $page => $url)
                <a href="{{ $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
                @endforeach
                @if($e<$last)
                    @if($e<$last-1)<span class="px-1 text-muted text-xs">…</span>@endif
                    <a href="{{ $reksaDanas->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                @endif
                @if($reksaDanas->hasMorePages())
                <a href="{{ $reksaDanas->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
                @endif
            </div>
        </div>
        @endif
    </div>
</form>
@endsection
