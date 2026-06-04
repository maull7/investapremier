@extends('layouts.user')

@section('title', 'Daftar Obligasi - InvestaPremier')

@section('content')
<div class="mb-6">
    <h1 class="page-title">Daftar Obligasi</h1>
    <p class="page-sub">Informasi obligasi harga referensi dan keuangan emiten</p>
</div>

{{-- Tabs --}}
<div class="mb-5">
    <div class="flex items-center gap-1 bg-[#f1f5f9] rounded-xl p-1 w-fit">
        <a href="{{ route('user.obligasi.index', ['tab' => 'harga-referensi']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'harga-referensi' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
            Obligasi Harga Referensi
        </a>
        <a href="{{ route('user.obligasi.index', ['tab' => 'bond']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'bond' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
            Keuangan Emiten
        </a>
    </div>
</div>

@if($tab === 'harga-referensi')
    {{-- Search --}}
    <div class="mb-5">
        <form method="GET" action="{{ route('user.obligasi.index') }}">
            <input type="hidden" name="tab" value="harga-referensi">
            <div class="flex items-center gap-3">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-10 pr-4 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                           placeholder="Cari kode, nama, atau emiten...">
                </div>
                <button type="submit" class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Cari</button>
                @if(request('search'))<a href="{{ route('user.obligasi.index', ['tab' => 'harga-referensi']) }}" class="px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>@endif
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-head">
            <h2 class="th-title">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Obligasi Harga Referensi
            </h2>
            <div class="flex items-center gap-2">
                <span class="th-meta">Tampilkan:</span>
                <form method="GET" action="{{ route('user.obligasi.index') }}">
                    <input type="hidden" name="tab" value="harga-referensi">
                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                    <select name="per_page" onchange="this.form.submit()"
                            class="text-xs bg-white/10 text-white border border-white/20 rounded-lg px-2 py-1 focus:outline-none cursor-pointer">
                        @foreach([10, 25, 50] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </form>
                <span class="th-meta">{{ $hargaReferensi->total() }} total</span>
            </div>
        </div>

        @if($hargaReferensi->isEmpty())
        <div class="py-16 text-center text-muted">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="font-medium">Belum ada data</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                        <th class="px-4 py-3.5 font-semibold">Kode</th>
                        <th class="px-4 py-3.5 font-semibold">Nama / Emiten</th>
                        <th class="px-4 py-3.5 font-semibold">Rating</th>
                        <th class="px-4 py-3.5 font-semibold">Kupon</th>
                        <th class="px-4 py-3.5 font-semibold">Jatuh Tempo</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Harga (%)</th>
                        <th class="px-4 py-3.5 font-semibold text-right">YTM</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($hargaReferensi as $o)
                    <tr class="hover:bg-[#f8fafc] transition-colors">
                        <td class="px-4 py-3"><div class="w-fit px-2.5 py-1 rounded-lg bg-primary/10 text-primary font-bold text-xs">{{ $o->kode }}</div></td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-primary leading-snug text-sm">{{ $o->nama ?: '-' }}</p>
                            <p class="text-xs text-muted">{{ $o->emiten ?: '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $o->rating ?: '-' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $o->kupon !== null ? number_format($o->kupon, 2) . '%' : '-' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $o->jatuh_tempo ? $o->jatuh_tempo->format('d/m/Y') : '-' }}</td>
                        <td class="px-4 py-3 text-right text-xs">{{ $o->harga_persen !== null ? number_format($o->harga_persen, 2) : '-' }}</td>
                        <td class="px-4 py-3 text-right text-xs">{{ $o->ytm !== null ? number_format($o->ytm, 4) . '%' : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($hargaReferensi->hasPages())
        <div class="px-6 py-4 border-t border-line flex items-center justify-between gap-4 text-sm">
            <p class="text-muted text-xs">Menampilkan {{ $hargaReferensi->firstItem() }}–{{ $hargaReferensi->lastItem() }} dari {{ $hargaReferensi->total() }}</p>
            <div class="flex items-center gap-1">
                @if($hargaReferensi->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
                @else
                <a href="{{ $hargaReferensi->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
                @endif
                @php $cur=$hargaReferensi->currentPage();$last=$hargaReferensi->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
                @if($s>1)
                    <a href="{{ $hargaReferensi->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                    @if($s>2)<span class="px-1 text-muted text-xs">…</span>@endif
                @endif
                @foreach($hargaReferensi->getUrlRange($s,$e) as $page => $url)
                <a href="{{ $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
                @endforeach
                @if($e<$last)
                    @if($e<$last-1)<span class="px-1 text-muted text-xs">…</span>@endif
                    <a href="{{ $hargaReferensi->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                @endif
                @if($hargaReferensi->hasMorePages())
                <a href="{{ $hargaReferensi->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
                @else
                <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next →</span>
                @endif
            </div>
        </div>
        @endif
        @endif
    </div>
@else
    {{-- Search Bond --}}
    <div class="mb-5">
        <form method="GET" action="{{ route('user.obligasi.index') }}">
            <input type="hidden" name="tab" value="bond">
            <div class="flex items-center gap-3">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-10 pr-4 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                           placeholder="Cari kode atau periode...">
                </div>
                <button type="submit" class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Cari</button>
                @if(request('search'))<a href="{{ route('user.obligasi.index', ['tab' => 'bond']) }}" class="px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>@endif
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-head">
            <h2 class="th-title">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Keuangan Emiten
            </h2>
            <div class="flex items-center gap-2">
                <span class="th-meta">Tampilkan:</span>
                <form method="GET" action="{{ route('user.obligasi.index') }}">
                    <input type="hidden" name="tab" value="bond">
                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                    <select name="per_page" onchange="this.form.submit()"
                            class="text-xs bg-white/10 text-white border border-white/20 rounded-lg px-2 py-1 focus:outline-none cursor-pointer">
                        @foreach([10, 25, 50] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </form>
                <span class="th-meta">{{ $bonds->total() }} total</span>
            </div>
        </div>

        @if($bonds->isEmpty())
        <div class="py-16 text-center text-muted">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="font-medium">Belum ada data</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                        <th class="px-4 py-3.5 font-semibold">Kode</th>
                        <th class="px-4 py-3.5 font-semibold">Periode</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Total Asset</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Total Liabilities</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Equity</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Net Revenue</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Net Income</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($bonds as $o)
                    <tr class="hover:bg-[#f8fafc] transition-colors">
                        <td class="px-4 py-3"><div class="w-fit px-2.5 py-1 rounded-lg bg-primary/10 text-primary font-bold text-xs">{{ $o->kode }}</div></td>
                        <td class="px-4 py-3 text-xs">{{ $o->periode }}</td>
                        <td class="px-4 py-3 text-right text-xs">{{ $o->total_asset ? number_format($o->total_asset, 0, ',', '.') : '-' }}</td>
                        <td class="px-4 py-3 text-right text-xs">{{ $o->total_liabilities ? number_format($o->total_liabilities, 0, ',', '.') : '-' }}</td>
                        <td class="px-4 py-3 text-right text-xs">{{ $o->equity ? number_format($o->equity, 0, ',', '.') : '-' }}</td>
                        <td class="px-4 py-3 text-right text-xs">{{ $o->net_revenue ? number_format($o->net_revenue, 0, ',', '.') : '-' }}</td>
                        <td class="px-4 py-3 text-right text-xs">{{ $o->net_income ? number_format($o->net_income, 0, ',', '.') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($bonds->hasPages())
        <div class="px-6 py-4 border-t border-line flex items-center justify-between gap-4 text-sm">
            <p class="text-muted text-xs">Menampilkan {{ $bonds->firstItem() }}–{{ $bonds->lastItem() }} dari {{ $bonds->total() }}</p>
            <div class="flex items-center gap-1">
                @if($bonds->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
                @else
                <a href="{{ $bonds->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
                @endif
                @php $cur=$bonds->currentPage();$last=$bonds->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
                @if($s>1)
                    <a href="{{ $bonds->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                    @if($s>2)<span class="px-1 text-muted text-xs">…</span>@endif
                @endif
                @foreach($bonds->getUrlRange($s,$e) as $page => $url)
                <a href="{{ $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
                @endforeach
                @if($e<$last)
                    @if($e<$last-1)<span class="px-1 text-muted text-xs">…</span>@endif
                    <a href="{{ $bonds->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                @endif
                @if($bonds->hasMorePages())
                <a href="{{ $bonds->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
                @else
                <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next →</span>
                @endif
            </div>
        </div>
        @endif
        @endif
    </div>
@endif
@endsection
