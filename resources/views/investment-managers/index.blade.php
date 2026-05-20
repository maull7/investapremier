@extends('layouts.user')

@section('title', 'Manajer Investasi - InvestaPremier')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-primary">Manajer Investasi</h1>
    <p class="text-muted text-sm mt-1">Data AUM dan UP manajer investasi per periode</p>
</div>

<div class="mb-5">
    <form method="GET" action="{{ route('user.investment-managers.index') }}">
        <div class="flex items-center gap-3">
            <div class="relative flex-1 max-w-md">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                       placeholder="Cari nama manajer investasi...">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Cari</button>
            @if(request('search'))<a href="{{ route('user.investment-managers.index') }}" class="px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>@endif
        </div>
    </form>
</div>

@if($managers->isEmpty())
<div class="py-16 text-center text-muted bg-white rounded-2xl border border-line">
    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    <p class="font-medium">Belum ada data</p>
</div>
@else
@foreach($managers as $m)
<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm mb-4">
    <div class="px-5 py-3 border-b border-line bg-gradient-to-r from-primary to-primary-light">
        <h3 class="font-bold text-white flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            {{ $m->name }}
        </h3>
    </div>
    @if($m->periods->isEmpty())
    <div class="py-8 text-center text-muted text-sm">Belum ada data periode.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                    <th class="px-4 py-3 font-semibold">Periode</th>
                    <th class="px-4 py-3 font-semibold text-right">AUM (Rp)</th>
                    <th class="px-4 py-3 font-semibold text-right">UP</th>
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
                    </td>
                    <td class="px-4 py-3 text-right text-xs font-semibold text-primary tabular-nums">
                        {{ $p->aum ? 'Rp' . number_format($p->aum, 0, ',', '.') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums">
                        {{ $p->up ? number_format($p->up, 2, ',', '.') : '-' }}
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
        <form method="GET" action="{{ route('user.investment-managers.index') }}">
            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
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
@endsection