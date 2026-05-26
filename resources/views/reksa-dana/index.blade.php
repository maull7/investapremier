@extends('layouts.user')

@section('title', 'Daftar Reksa Dana')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-primary">Daftar Reksa Dana</h1>
    <p class="text-muted text-sm mt-1">Informasi dan analisa reksa dana yang tersedia</p>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-5">
    {{ session('success') }}
</div>
@endif

<form method="GET" action="{{ route('user.reksa-dana.index') }}" class="mb-5 space-y-3">
    {{-- Filter Jenis --}}
    <div>
        <div class="flex items-center gap-2 text-xs mb-2">
            <span class="font-semibold text-muted">Jenis:</span>
            <a href="{{ route('user.reksa-dana.index') }}"
               class="px-3 py-1.5 rounded-lg border transition {{ !request('jenis') ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
               Semua
            </a>
        </div>
        <div class="flex gap-2 text-xs flex-wrap">
            @foreach($jenisOptions as $j)
            <label class="flex items-center gap-1.5 cursor-pointer px-3 py-1.5 rounded-lg border transition has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary border-line text-muted hover:bg-[#f1f5f9]">
                <input type="checkbox" name="jenis[]" value="{{ $j }}"
                    {{ in_array($j, (array) request('jenis')) ? 'checked' : '' }}
                    class="sr-only"
                    onchange="this.closest('form').submit();">
                {{ $j }}
            </label>
            @endforeach
        </div>
    </div>

    {{-- Filter Kategori --}}
    <div>
        <div class="flex items-center gap-2 text-xs mb-2">
            <span class="font-semibold text-muted">Kategori:</span>
            <a href="{{ route('user.reksa-dana.index') }}"
               class="px-3 py-1.5 rounded-lg border transition {{ !request('kategori') ? 'bg-accent text-white border-accent' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
               Semua
            </a>
        </div>
        <div class="flex gap-2 text-xs flex-wrap">
            @foreach($kategoriOptions as $k)
            <label class="flex items-center gap-1.5 cursor-pointer px-3 py-1.5 rounded-lg border transition has-[:checked]:bg-accent has-[:checked]:text-white has-[:checked]:border-accent border-line text-muted hover:bg-[#f1f5f9]">
                <input type="checkbox" name="kategori[]" value="{{ $k }}"
                    {{ in_array($k, (array) request('kategori')) ? 'checked' : '' }}
                    class="sr-only"
                    onchange="this.closest('form').submit();">
                {{ $k }}
            </label>
            @endforeach
        </div>
    </div>
</form>

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
                    <th class="px-5 py-3.5 font-semibold">Tanggal Data</th>
                    <th class="px-5 py-3.5 font-semibold text-right">AUM</th>
                    <th class="px-5 py-3.5 font-semibold text-right">UP</th>
                    <th class="px-5 py-3.5 font-semibold text-right">
                        <span class="flex items-center justify-end gap-1">
                            Return 1M
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        </span>
                    </th>
                    <th class="px-5 py-3.5 font-semibold"></th>
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
                    <td class="px-5 py-3.5 text-muted text-xs">
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
                    <td class="px-5 py-3.5 text-muted text-xs">{{ $rd->mata_uang ?? 'IDR' }}</td>
                    <td class="px-5 py-3.5 text-muted text-xs">{{ $rd->tanggal_data ? $rd->tanggal_data->format('d/m/Y') : '—' }}</td>
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
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('user.reksa-dana.edit', $rd) }}"
                               class="p-1.5 rounded-lg text-muted hover:text-blue-600 hover:bg-blue-50 transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('user.reksa-dana.destroy', $rd) }}"
                                  onsubmit="return confirm('Hapus data reksa dana ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-muted hover:text-red-600 hover:bg-red-50 transition" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-6 py-12 text-center text-muted">
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
@endsection
