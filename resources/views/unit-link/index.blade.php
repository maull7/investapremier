@extends('layouts.user')

@section('title', 'Unit Link - InvestaPremier')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-primary">Unit Link</h1>
    <p class="text-muted text-sm mt-1">Data unit link dan harga</p>
</div>

{{-- Tabs --}}
<div class="mb-5">
    <div class="flex items-center gap-1 bg-[#f1f5f9] rounded-xl p-1 w-fit">
        <a href="{{ route('user.unit-link.index', ['tab' => 'unit-links']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'unit-links' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
            Unit Links
        </a>
        <a href="{{ route('user.unit-link.index', ['tab' => 'unit-prices']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'unit-prices' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
            Unit Prices
        </a>
    </div>
</div>

@if($tab === 'unit-links')
<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-primary to-primary-light">
        <h2 class="font-bold text-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Unit Links
        </h2>
        <div class="flex items-center gap-2">
            <span class="text-xs text-white/60">Tampilkan:</span>
            <form method="GET" action="{{ route('user.unit-link.index') }}">
                <input type="hidden" name="tab" value="unit-links">
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                <select name="per_page" onchange="this.form.submit()"
                        class="text-xs bg-white/10 text-white border border-white/20 rounded-lg px-2 py-1 focus:outline-none cursor-pointer">
                    @foreach([10, 25, 50] as $n)
                    <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
            <span class="text-xs text-white/60">{{ $unitLinks->total() }} total</span>
        </div>
    </div>

    @if($unitLinks->isEmpty())
    <div class="py-16 text-center text-muted">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        <p class="font-medium">Belum ada data</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                    <th class="px-4 py-3.5 font-semibold">Unit Link</th>
                    <th class="px-4 py-3.5 font-semibold">Asuransi</th>
                    <th class="px-4 py-3.5 font-semibold">Jenis</th>
                    <th class="px-4 py-3.5 font-semibold">Tipe</th>
                    <th class="px-4 py-3.5 font-semibold">Mata Uang</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Median Price</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Buy Price</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Sell Price</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Last Update</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach($unitLinks as $u)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-4 py-3 font-semibold text-primary text-sm">{{ $u->unit_link }}</td>
                    <td class="px-4 py-3 text-xs">{{ $u->asuransi ?: '-' }}</td>
                    <td class="px-4 py-3 text-xs">{{ $u->jenis ?: '-' }}</td>
                    <td class="px-4 py-3 text-xs">{{ $u->tipe ?: '-' }}</td>
                    <td class="px-4 py-3 text-xs">
                        <span class="px-2 py-0.5 rounded bg-primary/5 text-primary font-semibold">{{ $u->mata_uang ?: '-' }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums">{{ $u->median_price !== null ? number_format($u->median_price, 4, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums">{{ $u->buy_price !== null ? number_format($u->buy_price, 4, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums">{{ $u->sell_price !== null ? number_format($u->sell_price, 4, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs">{{ $u->last_update ? $u->last_update->format('d M Y') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($unitLinks->hasPages())
    <div class="px-6 py-4 border-t border-line flex items-center justify-between gap-4 text-sm">
        <p class="text-muted text-xs">Menampilkan {{ $unitLinks->firstItem() }}–{{ $unitLinks->lastItem() }} dari {{ $unitLinks->total() }}</p>
        <div class="flex items-center gap-1">
            @if($unitLinks->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
            @else
            <a href="{{ $unitLinks->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
            @endif
            @php $cur=$unitLinks->currentPage();$last=$unitLinks->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s>1)
                <a href="{{ $unitLinks->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                @if($s>2)<span class="px-1 text-muted text-xs">…</span>@endif
            @endif
            @foreach($unitLinks->getUrlRange($s,$e) as $page => $url)
            <a href="{{ $url }}"
               class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
            @endforeach
            @if($e<$last)
                @if($e<$last-1)<span class="px-1 text-muted text-xs">…</span>@endif
                <a href="{{ $unitLinks->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
            @endif
            @if($unitLinks->hasMorePages())
            <a href="{{ $unitLinks->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
            @else
            <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next →</span>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>
@else
<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-primary to-primary-light">
        <h2 class="font-bold text-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            Unit Prices
        </h2>
    </div>
    <div class="py-16 text-center text-muted">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        <p class="font-medium">Belum ada data</p>
        <p class="text-sm mt-1">Fitur Unit Prices akan segera tersedia</p>
    </div>
</div>
@endif
@endsection
