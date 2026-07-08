@extends('layouts.admin')

@section('title', 'Monitor Unit Link FFS')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="page-title">Monitor Unit Link FFS</h1>
        <p class="page-sub">Unit Link yang disubmit user untuk dianalisa</p>
    </div>
</div>

@if(session('success'))
<div class="alert-success">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

@if (session('export_file'))
<div class="mt-2">
    <a href="{{ session('export_file') }}" class="btn-primary btn-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Download Excel
    </a>
</div>
@endif

<form method="POST" action="{{ route('admin.unit-link-ffs.bulk-analisa') }}" x-data="{ checked: [] }">
    @csrf

    <div class="table-card">
        {{-- Header + Tombol Analisa FFS --}}
        <div class="table-head">
            <h2 class="th-title">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Unit Link FFS
            </h2>
            <div class="flex items-center gap-3">
                <span class="th-meta">{{ $analisas->total() }} total</span>
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
        <div class="px-6 py-3 border-b border-line flex gap-2 text-xs flex-wrap">
            @foreach(['', 'Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'] as $j)
            <a href="{{ route('admin.unit-link-ffs.index', array_filter(['jenis' => $j ?: null])) }}"
               class="px-3 py-1.5 rounded-lg border transition {{ request('jenis') === $j || (!request('jenis') && $j === '') ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                {{ $j ?: 'Semua Jenis' }}
            </a>
            @endforeach
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                        <th class="px-4 py-3.5 w-10">
                            <input type="checkbox" class="rounded border-line"
                                @change="checked = $event.target.checked ? [...$el.closest('table').querySelectorAll('input[name=\'ids[]\']')].map(i => i.value) : []">
                        </th>
                        <th class="px-4 py-3.5 font-semibold">Nama Unit Link</th>
                        <th class="px-4 py-3.5 font-semibold">Jenis</th>
                        <th class="px-4 py-3.5 font-semibold">Mata Uang</th>
                        <th class="px-4 py-3.5 font-semibold text-right">AUM</th>
                        <th class="px-4 py-3.5 font-semibold">Status AI</th>
                        <th class="px-4 py-3.5 font-semibold text-center">PDF FFS</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse($analisas as $analisa)
                    <tr class="hover:bg-[#f8fafc] transition-colors">
                        <td class="px-4 py-3.5">
                            @if($analisa->pdf_path)
                            <input type="checkbox" name="ids[]" value="{{ $analisa->id }}"
                                x-model="checked" class="rounded border-line">
                            @else
                            <input type="checkbox" disabled class="rounded border-line opacity-30" title="Tidak ada PDF FFS">
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-primary">{{ $analisa->nama_reksa_dana }}</div>
                            <div class="text-xs text-muted">{{ $analisa->user->name }}</div>
                        </td>
                        <td class="px-4 py-3.5">
                            @php
                                $jenisColor = match($analisa->jenis_reksa_dana) {
                                    'Saham' => 'bg-blue-100 text-blue-700',
                                    'Pendapatan Tetap' => 'bg-amber-100 text-amber-700',
                                    'Campuran' => 'bg-purple-100 text-purple-700',
                                    default => 'bg-green-100 text-green-700',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $jenisColor }}">{{ $analisa->jenis_reksa_dana }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-muted text-xs">{{ $analisa->mata_uang ?? 'IDR' }}</td>
                        <td class="px-4 py-3.5 text-right text-xs text-muted">
                            {{ $analisa->total_aum ? 'Rp ' . number_format($analisa->total_aum, 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-4 py-3.5">
                            @if($analisa->ai_narasi)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Selesai</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Belum</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($analisa->pdf_path)
                                <a href="{{ route('admin.unit-link-ffs.pdf', $analisa) }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-semibold hover:bg-red-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    PDF
                                </a>
                            @else
                                <span class="text-muted text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <a href="{{ route('admin.unit-link-analisa.show', $analisa) }}"
                               class="px-3 py-1.5 border border-line text-muted rounded-lg text-xs font-semibold hover:text-primary hover:border-primary/30 transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-muted">
                            <p class="font-medium">Belum ada data unit link FFS</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($analisas->hasPages())
        <div class="px-6 py-4 border-t border-line flex items-center justify-between text-sm">
            <p class="text-muted text-xs">Menampilkan {{ $analisas->firstItem() }}–{{ $analisas->lastItem() }} dari {{ $analisas->total() }}</p>
            <div class="flex items-center gap-1">
                @if(!$analisas->onFirstPage())
                <a href="{{ $analisas->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
                @endif
                @php $cur=$analisas->currentPage();$last=$analisas->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
                @if($s>1)
                    <a href="{{ $analisas->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                    @if($s>2)<span class="px-1 text-muted text-xs">…</span>@endif
                @endif
                @foreach($analisas->getUrlRange($s,$e) as $page => $url)
                <a href="{{ $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
                @endforeach
                @if($e<$last)
                    @if($e<$last-1)<span class="px-1 text-muted text-xs">…</span>@endif
                    <a href="{{ $analisas->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                @endif
                @if($analisas->hasMorePages())
                <a href="{{ $analisas->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
                @endif
            </div>
        </div>
        @endif
    </div>
</form>
@endsection
