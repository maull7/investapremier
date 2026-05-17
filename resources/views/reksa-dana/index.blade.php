@extends('layouts.user')

@section('title', 'Daftar Reksa Dana')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-primary">Daftar Reksa Dana</h1>
    <p class="text-muted text-sm mt-1">Informasi dan analisa reksa dana yang tersedia</p>
</div>

{{-- Filter Jenis --}}
<div class="flex gap-2 text-xs mb-5 flex-wrap">
    @foreach(['', 'Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang'] as $j)
    <a href="{{ route('user.reksa-dana.index', $j ? ['jenis' => $j] : []) }}"
       class="px-3 py-1.5 rounded-lg border transition {{ request('jenis') === $j || (!request('jenis') && $j === '') ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
        {{ $j ?: 'Semua' }}
    </a>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide border-b border-line">
                    <th class="px-5 py-3.5 font-semibold w-10">No</th>
                    <th class="px-5 py-3.5 font-semibold">Nama Reksa Dana</th>
                    <th class="px-5 py-3.5 font-semibold">Jenis</th>
                    <th class="px-5 py-3.5 font-semibold">Kategori</th>
                    <th class="px-5 py-3.5 font-semibold">Mata Uang</th>
                    <th class="px-5 py-3.5 font-semibold text-right">AUM</th>
                    <th class="px-5 py-3.5 font-semibold text-right">UP</th>
                    <th class="px-5 py-3.5 font-semibold text-right">
                        <span class="flex items-center justify-end gap-1">
                            Return 1M
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse($reksaDanas as $rd)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-5 py-3.5 text-muted text-xs">{{ $reksaDanas->firstItem() + $loop->index }}</td>
                    <td class="px-5 py-3.5">
                        <div class="font-semibold text-primary">{{ $rd->nama_reksa_dana }}</div>
                    </td>
                    <td class="px-5 py-3.5">
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
                    <td class="px-5 py-3.5 text-muted text-xs">{{ $rd->kategori ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-muted text-xs">{{ $rd->mata_uang ?? 'IDR' }}</td>
                    <td class="px-5 py-3.5 text-right text-xs text-muted">
                        {{ $rd->total_aum ? 'Rp ' . number_format($rd->total_aum, 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-right text-xs text-muted">
                        {{ $rd->unit_penyertaan ? number_format($rd->unit_penyertaan, 2, ',', '.') : '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-right text-xs font-semibold">
                        @if($rd->return_1m !== null)
                            <span class="{{ $rd->return_1m >= 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $rd->return_1m >= 0 ? '+' : '' }}{{ number_format($rd->return_1m, 2) }}%
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-muted">
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
            @foreach($reksaDanas->getUrlRange(1, $reksaDanas->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $reksaDanas->currentPage() ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
            @endforeach
            @if($reksaDanas->hasMorePages())
            <a href="{{ $reksaDanas->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
