@extends('layouts.admin')

@section('title', $fund->nama_reksa_dana . ' - Detail Reksa Dana')

@section('content')
<div x-data="reksaDanaShow()">

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-muted mb-3">
        <a href="{{ route('admin.daftar-reksa-dana.index') }}" class="hover:text-primary transition">Daftar Reksa Dana</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary font-medium">{{ $fund->nama_reksa_dana }}</span>
    </div>
    <h1 class="page-title">{{ $fund->nama_reksa_dana }}</h1>
    <form method="POST" action="{{ route('admin.daftar-reksa-dana.export-investment-manager', $fund) }}" class="mt-3">
        @csrf
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">
            Export ke Data Manajer Investasi
        </button>
    </form>
    <div class="flex flex-wrap gap-3 mt-2 text-sm text-muted">
        @if($fund->kode_reksa_dana)<span class="font-mono text-xs bg-[#f1f5f9] px-2 py-1 rounded">{{ $fund->kode_reksa_dana }}</span>@endif
        @if($fund->nama_manajer_investasi)<span>{{ $fund->nama_manajer_investasi }}</span>@endif
        @if($fund->jenis)<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">{{ $fund->jenis }}</span>@endif
        @if($fund->risk_category)<span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $fund->risk_category == 'Rendah' ? 'bg-green-100 text-green-700' : ($fund->risk_category == 'Sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $fund->risk_category }}</span>@endif
        @if($fund->tanggal_nab)<span>Data: {{ $fund->tanggal_nab->format('d M Y') }}</span>@endif
    </div>
    <div class="flex items-center gap-3 mt-3 flex-wrap">
        <span class="text-xs font-semibold text-muted">Data Portfolio:</span>
        <div class="flex items-center gap-2 flex-wrap">
            <select x-model="portfolioMonth" @change="applyPeriodFilter()" class="border border-line rounded-lg px-3 py-1.5 text-xs text-muted">
                <option value="">Bulan</option>
                @foreach(range(1, 12) as $m)
                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                @endforeach
            </select>
            <select x-model="portfolioYear" @change="applyPeriodFilter()" class="border border-line rounded-lg px-3 py-1.5 text-xs text-muted">
                <option value="">Tahun</option>
                @foreach(range(now()->year, now()->year - 10) as $y)
                <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <template x-if="portfolioMonth && portfolioYear">
            <span class="px-2.5 py-1 bg-accent/10 text-accent rounded-full text-[11px] font-semibold" x-text="periodLabel"></span>
        </template>
        <button type="button" x-show="portfolioMonth && portfolioYear" @click="resetPeriod()"
            class="px-2.5 py-1 text-[11px] text-muted hover:text-primary border border-line rounded-lg transition">Reset</button>
        <button @click="savePortfolio" :disabled="portfolioSaving"
            class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
            <span x-text="portfolioSaving ? 'Menyimpan...' : 'Simpan Portfolio'"></span>
        </button>
        <template x-if="portfolioSuccess">
            <span class="text-xs text-green-600 font-semibold" x-text="portfolioSuccess"></span>
        </template>
        <template x-if="portfolioError">
            <span class="text-xs text-red-600 font-semibold" x-text="portfolioError"></span>
        </template>
    </div>
</div>

@if($periodActive)
<div class="mb-4 px-4 py-3 bg-accent/5 border border-accent/20 rounded-xl text-sm text-primary flex items-center gap-2 flex-wrap">
    <svg class="w-4 h-4 text-accent shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    @php $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
    <span>Menampilkan data periode <strong>{{ $monthNames[$periodMonth - 1] ?? '-' }} {{ $periodYear }}</strong> di semua tab.</span>
    @unless($periodHasSnapshotData)
        <span class="text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">Belum ada data tersimpan untuk periode ini.</span>
    @endunless
</div>
@endif

{{-- Ringkasan --}}
@php
    $formatAum = function ($aumVal) {
        if ($aumVal === null) return '—';
        if ($aumVal >= 1_000_000_000_000) return 'Rp' . number_format($aumVal / 1_000_000_000_000, 2, ',', '.') . 'T';
        if ($aumVal >= 1_000_000_000) return 'Rp' . number_format($aumVal / 1_000_000_000, 1, ',', '.') . 'M';
        if ($aumVal >= 1_000_000) return 'Rp' . number_format($aumVal / 1_000_000, 1, ',', '.') . 'jt';
        return 'Rp' . number_format($aumVal, 0, ',', '.');
    };
    $unitPenyertaan = $displayAum !== null && $displayNab !== null && $displayNab > 0 ? $displayAum / $displayNab : null;
    $maxUnit = $fund->harga()->max('unit_participation');
@endphp
<div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">NAV / NAB-UP</p>
        <p class="text-sm font-bold text-primary">{{ $displayNab !== null ? number_format($displayNab, 4, ',', '.') : '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">Return Bulanan</p>
        <p class="text-sm font-bold {{ $displayReturnMonthly !== null ? ($displayReturnMonthly >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $displayReturnMonthly !== null ? number_format($displayReturnMonthly, 2, ',', '.') . '%' : '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">Return YTD</p>
        <p class="text-sm font-bold {{ $displayReturnYtd !== null ? ($displayReturnYtd >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $displayReturnYtd !== null ? number_format($displayReturnYtd, 2, ',', '.') . '%' : '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">Return Tahunan</p>
        <p class="text-sm font-bold {{ $displayReturnYearly !== null ? ($displayReturnYearly >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $displayReturnYearly !== null ? number_format($displayReturnYearly, 2, ',', '.') . '%' : '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">AUM</p>
        <p class="text-sm font-bold text-primary">{{ $formatAum($displayAum) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">Unit Penyertaan</p>
        <p class="text-sm font-bold text-primary">{{ $unitPenyertaan !== null ? number_format($unitPenyertaan, 0, ',', '.') : '—' }}</p>
    </div>
</div>

{{-- Tab Navigation --}}
<div class="flex items-center gap-1 mb-6 border-b border-line overflow-x-auto">
    <button @click="setTab('snapshot')" :class="tab === 'snapshot' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Snapshot</button>
    <button @click="setTab('grafik')" :class="tab === 'grafik' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Grafik dan Data</button>
    <button @click="setTab('risiko')" :class="tab === 'risiko' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Risiko</button>
    <button @click="setTab('biaya')" :class="tab === 'biaya' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Biaya</button>
    <button @click="setTab('portofolio')" :class="tab === 'portofolio' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Portofolio</button>
    <button @click="setTab('pdf-prospektus')" :class="tab === 'pdf-prospektus' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">PDF Prospektus</button>
    <button @click="setTab('pdf-ffs')" :class="tab === 'pdf-ffs' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">PDF FFS</button>
    <button @click="setTab('ekstrak-dokumen')" :class="tab === 'ekstrak-dokumen' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Daftar Ekstrak Dokumen</button>
</div>

{{-- TAB: SNAPSHOT --}}
<div x-show="tab === 'snapshot'" x-cloak>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Informasi Reksa Dana --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                <h2 class="font-bold text-white text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Informasi Reksa Dana
                </h2>
                <div class="flex items-center gap-2">
                    <button @click="toggleLock('info')" :disabled="toggling"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                        :class="isLocked('info') ? 'bg-amber-400/30 text-amber-100 hover:bg-amber-400/40' : 'bg-white/20 text-white hover:bg-white/30'">
                        <template x-if="isLocked('info')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></template>
                        <template x-if="!isLocked('info')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></template>
                        <span x-text="isLocked('info') ? 'Terkunci' : 'Lock Parser'"></span>
                    </button>
                    <a href="{{ route('admin.daftar-reksa-dana.edit', $fund) }}"
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                </div>
            </div>
            <div class="divide-y divide-line">
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Nama Reksa Dana</span><span class="text-sm">{{ $fund->nama_reksa_dana }}</span></div>
                @if($fund->kode_reksa_dana)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kode Reksa Dana</span><span class="text-sm font-mono">{{ $fund->kode_reksa_dana }}</span></div>@endif
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kode ISIN</span><span class="text-sm font-mono">{{ $fund->isin_code ?: '-' }}</span></div>
                @if($fund->investmentManager || $fund->nama_manajer_investasi)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Manajer Investasi</span><span class="text-sm">@if($fund->investmentManager)<a href="{{ route('admin.investment-managers.show', $fund->investmentManager) }}" class="text-accent hover:underline">{{ $fund->nama_manajer_investasi }}</a>@else{{ $fund->nama_manajer_investasi }}@endif</span></div>@endif
                @if($fund->custodian_bank)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Bank Kustodian</span><span class="text-sm">{{ $fund->custodian_bank }}</span></div>@endif
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Tanggal Efektif</span><span class="text-sm">{{ $fund->launch_date?->format('d M Y') ?: '-' }}</span></div>
                @php $launchDate = $fund->launch_date; @endphp
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Tanggal Peluncuran</span><span class="text-sm">{{ $launchDate?->format('d M Y') ?: '-' }}</span></div>
                @if($fund->tujuan_investasi)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Tujuan Investasi</span><span class="text-sm">{{ $fund->tujuan_investasi }}</span></div>@endif
                @if($fund->kebijakan_investasi)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kebijakan Investasi</span><span class="text-sm">{{ $fund->kebijakan_investasi }}</span></div>@endif
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Benchmark Tolak Ukur</span><span class="text-sm">{{ $fund->benchmark ?: '-' }}</span></div>
                @if($fund->display_mata_uang)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Mata Uang</span><span class="text-sm">{{ $fund->display_mata_uang }}</span></div>@endif
                @if($fund->jenis)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kategori</span><span class="text-sm">{{ $fund->kategori_label ?: $fund->jenis }}</span></div>@endif
                @if($fund->jenis)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Jenis Reksa Dana</span><span class="text-sm">{{ $fund->jenis }}</span></div>@endif
                @if($fund->kategori_produk)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kategori Produk</span><span class="text-sm">{{ $fund->kategori_produk }}</span></div>@endif
                @if($fund->display_kelas)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kelas</span><span class="text-sm">{{ $fund->display_kelas }}</span></div>@endif
                @if($fund->is_etf !== null)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">ETF</span><span class="text-sm">{{ $fund->is_etf ? 'Ya' : 'Tidak' }}</span></div>@endif
                @if($fund->is_index !== null)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Index Fund</span><span class="text-sm">{{ $fund->is_index ? 'Ya' : 'Tidak' }}</span></div>@endif
                @if($fund->conservative_category)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kategori Konservatif</span><span class="text-sm">{{ $fund->conservative_category }}</span></div>@endif
                @if($fund->dividend !== null)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Dividen</span><span class="text-sm">{{ $fund->dividend ? 'Ya' : 'Tidak' }}</span></div>@endif
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Maks. Unit Penyertaan</span><span class="text-sm font-bold text-primary">{{ $maxUnit ? number_format($maxUnit, 0, ',', '.') : '—' }}</span></div>
            </div>
        </div>

        {{-- Ringkasan Kinerja --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                <h2 class="font-bold text-white text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Ringkasan Kinerja
                </h2>
                <button @click="openEdit('ringkasan')"
                    class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </button>
            </div>
            <div class="divide-y divide-line">
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">NAV / NAB-UP</span><span class="text-sm font-bold text-primary">{{ $displayNab !== null ? number_format($displayNab, 4, ',', '.') : '—' }}</span></div>
                @if(!$periodActive)
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Return Harian</span><span class="text-sm font-bold {{ $displayReturnDaily !== null ? ($displayReturnDaily >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $displayReturnDaily !== null ? number_format($displayReturnDaily, 2, ',', '.') . '%' : '—' }}</span></div>
                @endif
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Return 1 Bulan</span><span class="text-sm font-bold {{ $displayReturnMonthly !== null ? ($displayReturnMonthly >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $displayReturnMonthly !== null ? number_format($displayReturnMonthly, 2, ',', '.') . '%' : '—' }}</span></div>
                @if($periodActive)
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Return YTD</span><span class="text-sm font-bold {{ $displayReturnYtd !== null ? ($displayReturnYtd >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $displayReturnYtd !== null ? number_format($displayReturnYtd, 2, ',', '.') . '%' : '—' }}</span></div>
                @endif
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Return Tahunan</span><span class="text-sm font-bold {{ $displayReturnYearly !== null ? ($displayReturnYearly >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $displayReturnYearly !== null ? number_format($displayReturnYearly, 2, ',', '.') . '%' : '—' }}</span></div>
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-36 shrink-0">AUM</span>
                    <span class="text-sm font-bold text-primary">{{ $formatAum($displayAum) }}</span>
                </div>
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Unit Penyertaan</span><span class="text-sm font-bold text-primary">{{ $unitPenyertaan !== null ? number_format($unitPenyertaan, 0, ',', '.') : '—' }}</span></div>
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Maks. Unit Penyertaan</span><span class="text-sm font-bold text-primary">{{ $maxUnit ? number_format($maxUnit, 0, ',', '.') : '—' }}</span></div>
            </div>
        </div>
    </div>

    {{-- Deskripsi --}}
    @if($fund->description)
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm">Deskripsi Reksa Dana</h2>
        </div>
        <div class="px-6 py-4 text-sm whitespace-pre-line">{{ $fund->description }}</div>
    </div>
    @endif

    {{-- Komite Investasi & Tim Pengelola --}}
    @php
        $committees = $fund->managementTeams->where('type', 'committee');
        $investmentManagers = $fund->managementTeams->where('type', 'investment_manager');
        $mi = $fund->investmentManager;
    @endphp
    @if($committees->isNotEmpty() || $investmentManagers->isNotEmpty() || $mi)
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm">Komite Investasi & Tim Pengelola</h2>
        </div>
        @if($committees->isNotEmpty())
        <div class="px-6 py-3 border-b border-line bg-[#f8fafc]">
            <h3 class="font-semibold text-primary text-xs">Komite Investasi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach($committees as $mt)
                    <tr class="hover:bg-[#f8fafc]"><td class="px-4 py-3 text-xs"><button type="button" @click="openPerson({{ Js::from($mt->name) }})" class="text-accent hover:underline text-left font-semibold">{{ $mt->name }}</button></td><td class="px-4 py-3 text-xs text-muted">{{ $mt->position ?? '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @elseif($mi && $mi->investment_committee)
        @php $icLines = preg_split('/\n+/', trim($mi->investment_committee)); @endphp
        <div class="px-6 py-3 border-b border-line bg-[#f8fafc]">
            <h3 class="font-semibold text-primary text-xs">Komite Investasi (dari Manajer Investasi)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach($icLines as $line)
                    @php
                        $line = trim($line); if(!$line) continue;
                        $parts = preg_split('/\s*(?:-|:|–)\s*/', $line, 2);
                        $name = trim($parts[0]); $pos = trim($parts[1] ?? '');
                    @endphp
                    <tr class="hover:bg-[#f8fafc]"><td class="px-4 py-3 text-xs"><button type="button" @click="openPerson({{ Js::from($name) }})" class="text-accent hover:underline text-left font-semibold">{{ $name }}</button></td><td class="px-4 py-3 text-xs text-muted">{{ $pos ?: '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        @if($investmentManagers->isNotEmpty())
        <div class="px-6 py-3 border-b border-line bg-[#f8fafc]">
            <h3 class="font-semibold text-primary text-xs">Tim Pengelola Investasi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach($investmentManagers as $mt)
                    <tr class="hover:bg-[#f8fafc]"><td class="px-4 py-3 text-xs"><button type="button" @click="openPerson({{ Js::from($mt->name) }})" class="text-accent hover:underline text-left font-semibold">{{ $mt->name }}</button></td><td class="px-4 py-3 text-xs text-muted">{{ $mt->position ?? '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @elseif($mi && $mi->investment_management_team)
        @php $tmlLines = preg_split('/\n+/', trim($mi->investment_management_team)); @endphp
        <div class="px-6 py-3 border-b border-line bg-[#f8fafc]">
            <h3 class="font-semibold text-primary text-xs">Tim Pengelola Investasi (dari Manajer Investasi)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach($tmlLines as $line)
                    @php
                        $line = trim($line); if(!$line) continue;
                        $parts = preg_split('/\s*(?:-|:|–)\s*/', $line, 2);
                        $name = trim($parts[0]); $pos = trim($parts[1] ?? '');
                    @endphp
                    <tr class="hover:bg-[#f8fafc]"><td class="px-4 py-3 text-xs"><button type="button" @click="openPerson({{ Js::from($name) }})" class="text-accent hover:underline text-left font-semibold">{{ $name }}</button></td><td class="px-4 py-3 text-xs text-muted">{{ $pos ?: '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif
    @if($committees->isEmpty() && $investmentManagers->isEmpty() && !$mi && !$fund->description)
    <div class="py-12 text-center text-muted bg-white rounded-2xl border border-line mt-6">
        <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm">Informasi reksa dana belum tersedia.</p>
    </div>
    @endif
</div>

{{-- TAB: GRAFIK DAN DATA --}}
<div x-show="tab === 'grafik'" x-cloak>
    @php
        $rangeOptions = ['1m'=>'1 Bulan','3m'=>'3 Bulan','6m'=>'6 Bulan','ytd'=>'YTD','1y'=>'1 Tahun','3y'=>'3 Tahun','5y'=>'5 Tahun','all'=>'All'];
        $aumPointCount = collect($chartData['aum']['series'])->sum(fn($series) => count($series['data']));
        $upPointCount = collect($chartData['up']['series'])->sum(fn($series) => count($series['data']));
        $navPointCount = collect($chartData['nav']['series'])->sum(fn($series) => count($series['data']));
    @endphp

    <div class="mb-4 space-y-3">
        <div class="flex flex-wrap items-center gap-2">
            @foreach($rangeOptions as $k=>$l)
                <a href="{{ route('admin.daftar-reksa-dana.show', array_merge(['reksaDana' => $fund, 'tab' => 'grafik', 'range' => $k], $periodQuery)) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $range === $k && !request()->filled('from_date') && !request()->filled('to_date') ? 'bg-primary text-white' : 'border border-line text-muted hover:bg-[#f1f5f9]' }}">{{ $l }}</a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.daftar-reksa-dana.show', $fund) }}"
            class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="tab" value="grafik">
            @if($periodActive)
                <input type="hidden" name="month" value="{{ $periodMonth }}">
                <input type="hidden" name="year" value="{{ $periodYear }}">
            @endif
            <div>
                <label class="block text-xs text-muted mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                    class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            </div>
            <div>
                <label class="block text-xs text-muted mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                    class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            </div>
            <button type="submit"
                class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Terapkan</button>
            @if(request()->filled('from_date') || request()->filled('to_date'))
                <a href="{{ route('admin.daftar-reksa-dana.show', array_merge(['reksaDana' => $fund, 'tab' => 'grafik', 'range' => $range], $periodQuery)) }}"
                    class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>
            @endif
        </form>
    </div>

    @if(!$chartData['has_data'])
    <div class="py-12 text-center text-muted bg-white rounded-2xl border border-line">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        <p class="font-medium">Belum terdapat data historis untuk ditampilkan.</p>
    </div>
    @else
    <div class="space-y-6">
        @if($aumPointCount > 0)
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">AUM Bulanan</h3>
                <div id="chartAum" class="min-h-[320px]"></div>
            </div>
        @endif
        @if($upPointCount > 0)
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">Total UP Bulanan</h3>
                <div id="chartUp" class="min-h-[320px]"></div>
            </div>
        @endif
        @if($navPointCount > 0)
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">NAB/UP Harian</h3>
                <div id="chartNav" class="min-h-[340px]"></div>
            </div>
        @endif
    </div>

    {{-- Tabel Historis --}}
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
            <h2 class="font-bold text-white text-sm">Riwayat NAV / AUM / Unit Penyertaan</h2>
            <div class="flex items-center gap-2">
                <button @click="toggleLock('ringkasan')" :disabled="toggling"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                    :class="isLocked('ringkasan') ? 'bg-amber-400/30 text-amber-100 hover:bg-amber-400/40' : 'bg-white/20 text-white hover:bg-white/30'">
                    <template x-if="isLocked('ringkasan')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></template>
                    <template x-if="!isLocked('ringkasan')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></template>
                    <span x-text="isLocked('ringkasan') ? 'Terkunci' : 'Lock Parser'"></span>
                </button>
                <button @click="openEdit('ringkasan')"
                    class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Tanggal</th><th class="px-4 py-3 font-semibold text-right">NAB/UP</th><th class="px-4 py-3 font-semibold text-right">AUM</th><th class="px-4 py-3 font-semibold text-right">Unit Penyertaan</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @forelse($navHistory as $nh)
                    <tr class="hover:bg-[#f8fafc] transition-colors">
                        <td class="px-4 py-3 text-xs text-muted">{{ $nh->tanggal->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-xs text-right font-semibold text-primary tabular-nums">{{ number_format($nh->nab_per_unit, 4, ',', '.') }}</td>
                        <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $nh->aum ? 'Rp'.number_format($nh->aum, 0, ',', '.') : '—' }}</td>
                        <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $nh->unit_participation ? number_format($nh->unit_participation, 0, ',', '.') : '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-muted"><p class="font-medium">Belum ada data historis</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartData = @json($chartData);
        const formatNumber = value => Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 4 });
        const formatRupiah = value => {
            const n = Number(value || 0);
            if (Math.abs(n) >= 1_000_000_000_000) return 'Rp ' + (n / 1_000_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' T';
            if (Math.abs(n) >= 1_000_000_000) return 'Rp ' + (n / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' M';
            return 'Rp ' + n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
        };
        const formatUnit = value => {
            const n = Number(value || 0);
            if (Math.abs(n) >= 1_000_000_000) return (n / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Miliar';
            if (Math.abs(n) >= 1_000_000) return (n / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Juta';
            return n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
        };
        const baseOptions = (series, formatter, csvName) => ({
            chart: {
                type: 'line',
                height: 320,
                toolbar: { show: true, tools: { download: true, selection: true, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true }, export: { csv: { filename: csvName }, png: { filename: csvName } } },
                zoom: { enabled: true, type: 'x' }
            },
            series,
            stroke: { curve: 'smooth', width: 2.5 },
            markers: { size: 3, hover: { size: 5 } },
            dataLabels: { enabled: false },
            legend: { show: true, position: 'top', horizontalAlign: 'left' },
            xaxis: { type: 'datetime', labels: { datetimeUTC: false, format: 'dd MMM yyyy' } },
            yaxis: { labels: { formatter } },
            tooltip: { shared: true, x: { format: 'dd MMM yyyy' }, y: { formatter } },
            grid: { borderColor: '#e2e8f0' },
            colors: ['#2563eb', '#059669'],
        });

        if (document.getElementById('chartAum')) {
            new ApexCharts(document.getElementById('chartAum'), baseOptions(chartData.aum.series, formatRupiah, 'aum-bulanan-reksa-dana')).render();
        }
        if (document.getElementById('chartUp')) {
            new ApexCharts(document.getElementById('chartUp'), baseOptions(chartData.up.series, formatUnit, 'total-up-bulanan-reksa-dana')).render();
        }
        if (document.getElementById('chartNav')) {
            new ApexCharts(document.getElementById('chartNav'), baseOptions(chartData.nav.series, formatNumber, 'nab-up-harian-reksa-dana')).render();
        }
    });
    </script>
    @endif
</div>

{{-- TAB: RISIKO --}}
<div x-show="tab === 'risiko'" x-cloak>
    <div class="space-y-6">
        {{-- Risk Category --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                <h2 class="font-bold text-white text-sm">Tingkat Risiko</h2>
                <div class="flex items-center gap-2">
                    <button @click="toggleLock('risiko')" :disabled="toggling"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                        :class="isLocked('risiko') ? 'bg-amber-400/30 text-amber-100 hover:bg-amber-400/40' : 'bg-white/20 text-white hover:bg-white/30'">
                        <template x-if="isLocked('risiko')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></template>
                        <template x-if="!isLocked('risiko')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></template>
                        <span x-text="isLocked('risiko') ? 'Terkunci' : 'Lock Parser'"></span>
                    </button>
                    <button @click="openEdit('risiko')"
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </button>
                </div>
            </div>
            @if($fund->risk_category || $fund->conservative_category)
            <div class="divide-y divide-line">
                @if($fund->risk_category)
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-40 shrink-0">Risk Category</span>
                    @php
                        $riskLabel = match($fund->risk_category) {
                            'Rendah' => 'Risiko Rendah',
                            'Sedang' => 'Risiko Menengah',
                            'Tinggi' => 'Risiko Tinggi',
                            default => $fund->risk_category,
                        };
                    @endphp
                    <span class="text-sm px-2 py-0.5 rounded-full text-xs font-semibold {{ $fund->risk_category == 'Rendah' ? 'bg-green-100 text-green-700' : ($fund->risk_category == 'Sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $riskLabel }}</span>
                </div>
                @endif
                @if($fund->conservative_category)
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-40 shrink-0">Kategori Konservatif</span>
                    <span class="text-sm">{{ $fund->conservative_category }}</span>
                </div>
                @endif
            </div>
            @else
            <div class="py-12 text-center text-muted text-sm">
                <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                Data risiko belum tersedia.
            </div>
            @endif
        </div>

        {{-- Risk Metrics (Pasardana) --}}
        @php
            $hasRiskMetrics = collect($riskMetrics)->filter()->isNotEmpty();
        @endphp
        @if($hasRiskMetrics)
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                <h2 class="font-bold text-white text-sm">Metrik Risiko</h2>
                <div class="flex items-center gap-2">
                    <button @click="toggleLock('risiko')" :disabled="toggling"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                        :class="isLocked('risiko') ? 'bg-amber-400/30 text-amber-100 hover:bg-amber-400/40' : 'bg-white/20 text-white hover:bg-white/30'">
                        <template x-if="isLocked('risiko')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></template>
                        <template x-if="!isLocked('risiko')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></template>
                        <span x-text="isLocked('risiko') ? 'Terkunci' : 'Lock Parser'"></span>
                    </button>
                    <button @click="openEdit('risiko')"
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                            <th class="px-4 py-3 font-semibold">Metrik</th>
                            <th class="px-4 py-3 font-semibold text-right">1 Tahun</th>
                            <th class="px-4 py-3 font-semibold text-right">3 Tahun</th>
                            <th class="px-4 py-3 font-semibold text-right">5 Tahun</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @if($riskMetrics['sharpe_ratio_1y'] !== null || $riskMetrics['sharpe_ratio_3y'] !== null || $riskMetrics['sharpe_ratio_5y'] !== null)
                        <tr class="hover:bg-[#f8fafc]">
                            <td class="px-4 py-3 text-xs font-semibold">Sharpe Ratio</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['sharpe_ratio_1y'] !== null ? number_format($riskMetrics['sharpe_ratio_1y'], 4, ',', '.') : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['sharpe_ratio_3y'] !== null ? number_format($riskMetrics['sharpe_ratio_3y'], 4, ',', '.') : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['sharpe_ratio_5y'] !== null ? number_format($riskMetrics['sharpe_ratio_5y'], 4, ',', '.') : '—' }}</td>
                        </tr>
                        @endif
                        @if($riskMetrics['stdev_1y'] !== null || $riskMetrics['stdev_3y'] !== null || $riskMetrics['stdev_5y'] !== null)
                        <tr class="hover:bg-[#f8fafc]">
                            <td class="px-4 py-3 text-xs font-semibold">Std. Deviasi</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['stdev_1y'] !== null ? number_format($riskMetrics['stdev_1y'] * 100, 2, ',', '.') . '%' : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['stdev_3y'] !== null ? number_format($riskMetrics['stdev_3y'] * 100, 2, ',', '.') . '%' : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['stdev_5y'] !== null ? number_format($riskMetrics['stdev_5y'] * 100, 2, ',', '.') . '%' : '—' }}</td>
                        </tr>
                        @endif
                        @if($riskMetrics['beta_1y'] !== null || $riskMetrics['beta_3y'] !== null || $riskMetrics['beta_5y'] !== null)
                        <tr class="hover:bg-[#f8fafc]">
                            <td class="px-4 py-3 text-xs font-semibold">Beta</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['beta_1y'] !== null ? number_format($riskMetrics['beta_1y'], 4, ',', '.') : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['beta_3y'] !== null ? number_format($riskMetrics['beta_3y'], 4, ',', '.') : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['beta_5y'] !== null ? number_format($riskMetrics['beta_5y'], 4, ',', '.') : '—' }}</td>
                        </tr>
                        @endif
                        @if($riskMetrics['max_drawdown_1y'] !== null || $riskMetrics['max_drawdown_3y'] !== null || $riskMetrics['max_drawdown_5y'] !== null)
                        <tr class="hover:bg-[#f8fafc]">
                            <td class="px-4 py-3 text-xs font-semibold">Max Drawdown</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['max_drawdown_1y'] !== null ? number_format($riskMetrics['max_drawdown_1y'] * 100, 2, ',', '.') . '%' : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['max_drawdown_3y'] !== null ? number_format($riskMetrics['max_drawdown_3y'] * 100, 2, ',', '.') . '%' : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['max_drawdown_5y'] !== null ? number_format($riskMetrics['max_drawdown_5y'] * 100, 2, ',', '.') . '%' : '—' }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- TAB: BIAYA --}}
<div x-show="tab === 'biaya'" x-cloak>
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
            <h2 class="font-bold text-white text-sm">Informasi Biaya</h2>
            <div class="flex items-center gap-2">
                <button @click="toggleLock('biaya')" :disabled="toggling"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                    :class="isLocked('biaya') ? 'bg-amber-400/30 text-amber-100 hover:bg-amber-400/40' : 'bg-white/20 text-white hover:bg-white/30'">
                    <template x-if="isLocked('biaya')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></template>
                    <template x-if="!isLocked('biaya')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></template>
                    <span x-text="isLocked('biaya') ? 'Terkunci' : 'Lock Parser'"></span>
                </button>
                <button @click="openEdit('biaya')"
                    class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </button>
            </div>
        </div>
        @php
            $_sub = $fees?->subscription_fee ?? ($periodActive ? null : $fund->subscription_fee);
            $_red = $fees?->redemption_fee ?? ($periodActive ? null : $fund->redemption_fee);
            $_swi = $fees?->switching_fee ?? ($periodActive ? null : $fund->switching_fee);
            $_mgmt = $fees?->management_fee ?? ($periodActive ? null : $fund->management_fee);
            $_cust = $fees?->custodian_fee ?? ($periodActive ? null : $fund->custodian_fee);
            $_exp = $fees?->expense_ratio ?? ($periodActive ? null : $fund->expense_ratio);
            $_im = $fees?->investment_manager_fee ?? ($periodActive ? null : $fund->investment_manager_fee);
            $hasFeeData = $_sub || $_red || $_swi || $_mgmt || $_cust || $_exp || $_im
                || (!$periodActive && ($fund->minimum_subscription || $fund->minimum_topup || $fund->minimum_redemption));
        @endphp
        @if($hasFeeData)
        <div class="divide-y divide-line">
            @if($_sub)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Subscription Fee</span><span class="text-sm">{{ number_format($_sub, 2, ',', '.') }}%</span></div>@endif
            @if($_red)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Redemption Fee</span><span class="text-sm">{{ number_format($_red, 2, ',', '.') }}%</span></div>@endif
            @if($_swi)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Switching Fee</span><span class="text-sm">{{ number_format($_swi, 2, ',', '.') }}%</span></div>@endif
            @if($_mgmt)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Management Fee</span><span class="text-sm">{{ number_format($_mgmt, 2, ',', '.') }}%</span></div>@endif
            @if($_cust)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Custodian Fee</span><span class="text-sm">{{ number_format($_cust, 2, ',', '.') }}%</span></div>@endif
            @if($_exp)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Expense Ratio</span><span class="text-sm">{{ number_format($_exp, 4, ',', '.') }}%</span></div>@endif
            @if($_im)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">IM Fee</span><span class="text-sm">{{ $_im }}</span></div>@endif
            @if(!$periodActive && $fund->minimum_subscription)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Minimum Pembelian</span><span class="text-sm">Rp{{ number_format($fund->minimum_subscription, 0, ',', '.') }}</span></div>@endif
            @if(!$periodActive && $fund->minimum_topup)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Minimum Top Up</span><span class="text-sm">Rp{{ number_format($fund->minimum_topup, 0, ',', '.') }}</span></div>@endif
            @if(!$periodActive && $fund->minimum_redemption)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Minimum Redemption</span><span class="text-sm">Rp{{ number_format($fund->minimum_redemption, 0, ',', '.') }}</span></div>@endif
        </div>
        @else
        <div class="py-12 text-center text-muted text-sm">
            <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @if($periodActive)
                @php $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
                Data biaya belum tersedia untuk periode {{ $monthNames[$periodMonth - 1] ?? '-' }} {{ $periodYear }}.
            @else
                Data biaya belum tersedia.
            @endif
        </div>
        @endif
    </div>
</div>

{{-- TAB: PORTOFOLIO --}}
<div x-show="tab === 'portofolio'" x-cloak>
    @php
        $showAa = $periodActive ? $allocation : $aaTimeline->last();
        $showTopHoldings = $periodActive ? $holdings : $topHoldings;
        $hasAnyData = $periodActive
            ? ($allocation !== null || $holdings->isNotEmpty())
            : ($aaTimeline->isNotEmpty() || $topHoldings->isNotEmpty());
    @endphp
    @if(!$hasAnyData)
    <div class="py-12 text-center text-muted bg-white rounded-2xl border border-line">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        @if($periodActive)
            @php $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
            <p class="font-medium">Data portofolio belum tersedia untuk periode {{ $monthNames[$periodMonth - 1] ?? '-' }} {{ $periodYear }}.</p>
        @else
            <p class="font-medium">Data portofolio belum tersedia.</p>
        @endif
    </div>
    @else
    <div class="space-y-6">
        {{-- Alokasi Aset --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                <h2 class="font-bold text-white text-sm">Alokasi Aset</h2>
                <div class="flex items-center gap-2">
                    <button @click="openEdit('portofolio')"
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </button>
                </div>
            </div>
            @php
                $latestAa = $showAa;
            @endphp
            @if($latestAa)
            <div class="divide-y divide-line">
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-36 shrink-0">Saham</span>
                    <span class="text-sm font-bold text-primary">{{ number_format($latestAa->equity_percent ?? 0, 2, ',', '.') }}%</span>
                </div>
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-36 shrink-0">Obligasi</span>
                    <span class="text-sm font-bold text-primary">{{ number_format($latestAa->bond_percent ?? 0, 2, ',', '.') }}%</span>
                </div>
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-36 shrink-0">Pasar Uang</span>
                    <span class="text-sm font-bold text-primary">{{ number_format($latestAa->money_market_percent ?? 0, 2, ',', '.') }}%</span>
                </div>
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-36 shrink-0">Kas</span>
                    <span class="text-sm font-bold text-primary">{{ number_format($latestAa->cash_percent ?? 0, 2, ',', '.') }}%</span>
                </div>
            </div>
            @else
            <div class="py-8 text-center text-muted text-sm">
                <svg class="w-8 h-8 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
                Data alokasi aset belum tersedia.
            </div>
            @endif
        </div>

        {{-- Asset Allocation Pie --}}
        @if($showAa)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">Asset Allocation ({{ $showAa->period_date->format('d M Y') }})</h3>
                <div style="height: 280px;"><canvas id="chartAaPie"></canvas></div>
            </div>
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">Top Holdings</h3>
                @if($showTopHoldings->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-3 py-2 font-semibold">Efek</th><th class="px-3 py-2 font-semibold">Jenis</th><th class="px-3 py-2 font-semibold text-right">Bobot</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @foreach($showTopHoldings as $th)
                            <tr class="hover:bg-[#f8fafc]"><td class="px-3 py-2 text-xs font-semibold">{{ $th->security_name }}</td><td class="px-3 py-2 text-xs text-muted">{{ $th->security_type ?? '—' }}</td><td class="px-3 py-2 text-xs text-right font-semibold">{{ number_format($th->weight_percent, 2, ',', '.') }}%</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-muted text-center py-8">Belum ada data top holdings.</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Timeline Asset Allocation (Stacked Bar) --}}
        @if($aaTimeline->isNotEmpty())
        <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
            <h3 class="font-bold text-primary text-sm mb-4">Timeline Asset Allocation</h3>
            <div style="height: 300px;"><canvas id="chartAaTimeline"></canvas></div>
        </div>
        @endif

        {{-- Timeline Portfolio Composition --}}
        @if($portfolioTimeline->isNotEmpty())
        @php
            $ptLabels = $portfolioTimeline->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M Y'));
            $allSecurities = $portfolioTimeline->flatMap(fn($items) => $items->pluck('security_name'))->unique()->values();
            $currentSecurities = $showTopHoldings->pluck('security_name')->values();
            $ptColors = ['#2563eb','#059669','#d97706','#dc2626','#7c3aed','#0891b2','#db2777','#65a30d','#ca8a04','#ea580c','#4f46e5','#0d9488'];
        @endphp
        <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-primary text-sm">Timeline Portfolio Composition</h3>
                <button @click="openPortfolioLegendModal()" class="px-3 py-1.5 bg-accent/10 text-accent rounded-lg text-xs font-semibold hover:bg-accent/20 transition">Detail</button>
            </div>
            <div style="height: 300px;"><canvas id="chartPtTimeline"></canvas></div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        {{-- Asset Allocation Pie --}}
        @if($showAa)
        new Chart(document.getElementById('chartAaPie'), {
            type: 'pie',
            data: {
                labels: ['Saham', 'Obligasi', 'Pasar Uang', 'Kas'],
                datasets: [{
                    data: [{{ $showAa->equity_percent ?? 0 }}, {{ $showAa->bond_percent ?? 0 }}, {{ $showAa->money_market_percent ?? 0 }}, {{ $showAa->cash_percent ?? 0 }}],
                    backgroundColor: ['#2563eb', '#059669', '#d97706', '#6b7280'],
                    borderWidth: 1,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
        @endif

        {{-- Timeline AA --}}
        @if($aaTimeline->isNotEmpty())
        new Chart(document.getElementById('chartAaTimeline'), {
            type: 'bar',
            data: {
                labels: @json($aaLabels),
                datasets: [
                    { label: 'Saham', data: @json($aaTimeline->pluck('equity_percent')), backgroundColor: '#2563eb' },
                    { label: 'Obligasi', data: @json($aaTimeline->pluck('bond_percent')), backgroundColor: '#059669' },
                    { label: 'Pasar Uang', data: @json($aaTimeline->pluck('money_market_percent')), backgroundColor: '#d97706' },
                    { label: 'Kas', data: @json($aaTimeline->pluck('cash_percent')), backgroundColor: '#6b7280' },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, ticks: { callback: val => val + '%' } }
                }
            }
        });
        @endif

        {{-- Timeline Portfolio Composition --}}
        @if($portfolioTimeline->isNotEmpty())
        const datasets = [];
        const securities = {!! json_encode($allSecurities) !!};
        const ptLabels = {!! json_encode($ptLabels) !!};
        const ptRaw = {!! json_encode($portfolioTimeline->map(fn($items) => $items->keyBy('security_name')->map(fn($i) => $i->weight_percent))) !!};
        const currentSecurities = {!! json_encode($currentSecurities) !!};
        const ptColors = @json($ptColors);

        securities.forEach((sec, i) => {
            datasets.push({
                label: sec,
                data: ptLabels.map((_, idx) => {
                    const periodKey = Object.keys(ptRaw)[idx];
                    return ptRaw[periodKey] && ptRaw[periodKey][sec] ? ptRaw[periodKey][sec] : 0;
                }),
                backgroundColor: ptColors[i % ptColors.length] || '#94a3b8',
            });
        });

        new Chart(document.getElementById('chartPtTimeline'), {
            type: 'bar',
            data: { labels: ptLabels, datasets: datasets },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            filter: function(item) {
                                return currentSecurities.includes(item.text);
                            }
                        }
                    }
                },
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, ticks: { callback: val => val + '%' } }
                }
            }
        });
        @endif
    });
    </script>
    @endif
</div>

{{-- FFS Preview Modal --}}
<div id="modal-ffs-preview" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4" @click.self="closeFfsPreviewModal()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-line p-4 flex items-center justify-between rounded-t-2xl">
            <h3 class="font-bold text-primary">Preview Data FFS</h3>
            <button type="button" @click="closeFfsPreviewModal()" class="p-1 hover:bg-[#f1f5f9] rounded-lg transition text-muted">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="ffs-preview-content" class="p-6 space-y-6">
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            </div>
        </div>
    </div>
</div>

{{-- TAB: PDF PROSPEKTUS --}}
<div x-show="tab === 'pdf-prospektus'" x-cloak x-data="documentTabData('prospektus')">
    @include('admin.daftar-reksa-dana.partials.tab-pdf-document', ['fund' => $fund, 'docType' => 'prospektus'])
</div>

{{-- TAB: DAFTAR EKSTRAK DOKUMEN --}}
<div x-show="tab === 'ekstrak-dokumen'" x-cloak>
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm">Daftar Ekstrak Dokumen</h2>
        </div>
        @if($extractionResults->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Tanggal Ekstrak</th><th class="px-4 py-3 font-semibold">Nama Dokumen</th><th class="px-4 py-3 font-semibold">Periode</th><th class="px-4 py-3 font-semibold text-center">Aksi</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach($extractionResults as $er)
                    <tr class="hover:bg-[#f8fafc]">
                        <td class="px-4 py-3 text-xs text-muted">{{ $er->created_at?->format('d M Y H:i') ?: '—' }}</td>
                        <td class="px-4 py-3 text-xs font-semibold">{{ $er->document?->original_name ?: '—' }}</td>
                        <td class="px-4 py-3 text-xs text-muted">{{ $er->ffs_month ? str_pad($er->ffs_month, 2, '0', STR_PAD_LEFT) . '/' . $er->ffs_year : '—' }}</td>
                        <td class="px-4 py-3 text-xs text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="openExtractionDetail({{ $er->id }})"
                                    class="px-3 py-1.5 bg-accent/10 text-accent rounded-lg text-xs font-semibold hover:bg-accent/20 transition">Lihat Data Ekstrak</button>
                                <button @click="deleteExtraction({{ $er->id }})"
                                    class="px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-xs font-semibold hover:bg-red-100 transition">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="py-12 text-center text-muted">
            <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm font-medium">Belum ada data ekstrak dokumen.</p>
        </div>
        @endif
    </div>
</div>

{{-- Modal Lihat Data Ekstrak --}}
<div x-show="extractionModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
    <div class="absolute inset-0 bg-black/40" @click="extractionModal.open = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-2xl max-h-[85vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <div>
                <p class="text-xs text-muted">Detail Ekstrak</p>
                <h2 class="font-bold text-primary" x-text="extractionModal.documentName || 'Data Ekstrak'"></h2>
            </div>
            <button type="button" @click="extractionModal.open = false" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
        </div>
        <div class="p-6">
            <template x-if="extractionModal.loading">
                <div class="flex items-center justify-center py-8">
                    <svg class="animate-spin h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </div>
            </template>
            <template x-if="extractionModal.error">
                <div class="px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700" x-text="extractionModal.error"></div>
            </template>
            <template x-if="!extractionModal.loading && !extractionModal.error">
                <div>
                    <div class="mb-3 flex items-center gap-2">
                        <span class="text-xs text-muted">Tanggal Ekstrak:</span>
                        <span class="text-xs font-semibold" x-text="extractionModal.createdAt || '-'"></span>
                    </div>
                    <div class="space-y-3" x-show="extractionModal.items.length > 0">
                        <template x-for="(item, idx) in extractionModal.items" :key="idx">
                            <div>
                                <template x-if="item.type === 'simple'">
                                    <div class="flex items-start gap-3 px-4 py-2.5 bg-[#f8fafc] rounded-lg border border-line">
                                        <span class="text-xs font-semibold text-muted w-36 shrink-0" x-text="item.key"></span>
                                        <span class="text-xs" x-text="item.value"></span>
                                    </div>
                                </template>
                                <template x-if="item.type === 'list'">
                                    <div class="bg-[#f8fafc] rounded-lg border border-line p-3">
                                        <p class="text-xs font-semibold text-muted mb-2" x-text="item.key"></p>
                                        <div class="flex flex-wrap gap-1">
                                            <template x-for="(v, vi) in item.values" :key="vi">
                                                <span class="px-2 py-0.5 bg-white rounded text-xs" x-text="v"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="item.type === 'table'">
                                    <div class="bg-[#f8fafc] rounded-lg border border-line p-3 overflow-x-auto">
                                        <p class="text-xs font-semibold text-muted mb-2" x-text="item.key"></p>
                                        <table class="w-full text-xs">
                                            <thead><tr class="bg-white">
                                                <template x-for="(h, hi) in item.headers" :key="hi">
                                                    <th class="px-2 py-1.5 text-left font-semibold text-muted" x-text="h"></th>
                                                </template>
                                            </tr></thead>
                                            <tbody>
                                                <template x-for="(row, ri) in item.rows" :key="ri">
                                                    <tr class="border-t border-line">
                                                        <template x-for="(cell, ci) in row" :key="ci">
                                                            <td class="px-2 py-1.5" x-text="cell ?? '—'"></td>
                                                        </template>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div x-show="extractionModal.items.length === 0" class="py-8 text-center text-muted text-sm">Tidak ada data ekstrak.</div>
                </div>
            </template>
        </div>
    </div>
</div>

{{-- Modal Portfolio Legend Detail --}}
<div x-show="portfolioLegendModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
    <div class="absolute inset-0 bg-black/40" @click="portfolioLegendModal.open = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-lg max-h-[85vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <h2 class="font-bold text-primary">Detail Portfolio Composition</h2>
            <button type="button" @click="portfolioLegendModal.open = false" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
        </div>
        <div class="p-6">
            @if($showTopHoldings->isNotEmpty())
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-3 py-2 font-semibold">Efek</th><th class="px-3 py-2 font-semibold">Jenis</th><th class="px-3 py-2 font-semibold text-right">Bobot</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach($showTopHoldings as $th)
                    <tr class="hover:bg-[#f8fafc]">
                        <td class="px-3 py-2 text-xs font-semibold flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full inline-block shrink-0" style="background-color: {{ $ptColors[$loop->index % count($ptColors)] }}"></span>
                            {{ $th->security_name }}
                        </td>
                        <td class="px-3 py-2 text-xs text-muted">{{ $th->security_type ?? '—' }}</td>
                        <td class="px-3 py-2 text-xs text-right font-semibold">{{ number_format($th->weight_percent, 2, ',', '.') }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-sm text-muted text-center py-8">Belum ada data portfolio terkini.</p>
            @endif
        </div>
    </div>
</div>

<div x-show="personModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
    <div class="absolute inset-0 bg-black/40" @click="personModal.open = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-3xl max-h-[85vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <div>
                <p class="text-xs text-muted">Detail keterkaitan</p>
                <h2 class="font-bold text-primary" x-text="personModal.data?.name || 'Memuat...'"></h2>
            </div>
            <button type="button" @click="personModal.open = false" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
        </div>
        <div class="p-6 space-y-6">
            <div x-show="personModal.loading" class="text-sm text-muted">Memuat data...</div>
            <div x-show="personModal.error" x-text="personModal.error" class="px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700"></div>
            <template x-if="personModal.data">
                <div class="space-y-6">
                    <div>
                        <h3 class="font-bold text-primary text-sm mb-3">Reksa Dana yang Pernah Diikuti</h3>
                        <template x-if="personModal.data.funds.length === 0"><p class="text-sm text-muted">Belum ada data Reksa Dana terkait.</p></template>
                        <div class="overflow-x-auto" x-show="personModal.data.funds.length > 0">
                            <table class="w-full text-sm"><thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-3 py-2">Reksa Dana</th><th class="px-3 py-2">Kode</th><th class="px-3 py-2">Peran</th><th class="px-3 py-2">Jabatan</th><th class="px-3 py-2">Sumber</th></tr></thead><tbody class="divide-y divide-line"><template x-for="row in personModal.data.funds" :key="row.name + row.role + row.position"><tr><td class="px-3 py-2 font-semibold" x-text="row.name"></td><td class="px-3 py-2 font-mono text-xs" x-text="row.code || '-'"></td><td class="px-3 py-2" x-text="row.role || '-'"></td><td class="px-3 py-2 text-muted" x-text="row.position || '-'"></td><td class="px-3 py-2 text-xs text-muted" x-text="row.source || '-'"></td></tr></template></tbody></table>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-primary text-sm mb-3">Manajer Investasi Terkait</h3>
                        <template x-if="personModal.data.managers.length === 0"><p class="text-sm text-muted">Belum ada data Manajer Investasi terkait.</p></template>
                        <div class="overflow-x-auto" x-show="personModal.data.managers.length > 0">
                            <table class="w-full text-sm"><thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-3 py-2">Manajer Investasi</th><th class="px-3 py-2">Peran</th><th class="px-3 py-2">Jabatan</th><th class="px-3 py-2">Sumber</th></tr></thead><tbody class="divide-y divide-line"><template x-for="row in personModal.data.managers" :key="row.name + row.role + row.position"><tr><td class="px-3 py-2 font-semibold" x-text="row.name"></td><td class="px-3 py-2" x-text="row.role || '-'"></td><td class="px-3 py-2 text-muted" x-text="row.position || '-'"></td><td class="px-3 py-2 text-xs text-muted" x-text="row.source || '-'"></td></tr></template></tbody></table>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-primary text-sm mb-3">Berita Utama Terkait</h3>
                        <template x-if="personModal.data.news.length === 0"><p class="text-sm text-muted">Belum ada berita terkait.</p></template>
                        <div class="space-y-2" x-show="personModal.data.news.length > 0">
                            <template x-for="item in personModal.data.news" :key="item.url || item.title">
                                <a :href="item.url" target="_blank" class="block border border-line rounded-xl px-4 py-3 hover:border-accent transition"><p class="text-sm font-semibold text-primary" x-text="item.title"></p><p class="text-xs text-muted mt-1"><span x-text="item.source || '-'"></span> <span x-show="item.published_at">-</span> <span x-text="item.published_at || ''"></span></p></a>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

{{-- Modal Edit Ringkasan Kinerja --}}
<div x-show="editModal === 'ringkasan'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
    @click.self="editModal = null">
    <div class="absolute inset-0 bg-black/40" @click="editModal = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-lg max-h-[85vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <h3 class="font-bold text-primary">Edit Ringkasan Kinerja</h3>
            <button @click="editModal = null" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
        </div>
        <form @submit.prevent="submitEdit('ringkasan')" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">NAB/UP</label>
                <input type="number" step="0.0001" x-model="editData.nab_per_unit" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Tanggal NAB</label>
                <input type="date" x-model="editData.tanggal_nab" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">AUM</label>
                <input type="number" step="0.01" x-model="editData.aum" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Total Unit Penyertaan</label>
                <input type="number" step="0.01" x-model="editData.total_unit" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="editModal = null" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-emerald-700 rounded-lg hover:bg-emerald-800">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Risiko --}}
<div x-show="editModal === 'risiko'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
    @click.self="editModal = null">
    <div class="absolute inset-0 bg-black/40" @click="editModal = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-lg max-h-[85vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <h3 class="font-bold text-primary">Edit Risiko</h3>
            <button @click="editModal = null" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
        </div>
        <form @submit.prevent="submitEdit('risiko')" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Risk Category</label>
                <select x-model="editData.risk_category" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    <option value="">—</option>
                    <option value="Rendah">Rendah</option>
                    <option value="Sedang">Sedang</option>
                    <option value="Tinggi">Tinggi</option>
                </select>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Sharpe 1Th</label>
                    <input type="number" step="0.0001" x-model="editData.sharpe_ratio_1y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Sharpe 3Th</label>
                    <input type="number" step="0.0001" x-model="editData.sharpe_ratio_3y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Sharpe 5Th</label>
                    <input type="number" step="0.0001" x-model="editData.sharpe_ratio_5y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Std Dev 1Th</label>
                    <input type="number" step="0.0001" x-model="editData.stdev_1y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Std Dev 3Th</label>
                    <input type="number" step="0.0001" x-model="editData.stdev_3y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Std Dev 5Th</label>
                    <input type="number" step="0.0001" x-model="editData.stdev_5y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Beta 1Th</label>
                    <input type="number" step="0.0001" x-model="editData.beta_1y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Beta 3Th</label>
                    <input type="number" step="0.0001" x-model="editData.beta_3y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Beta 5Th</label>
                    <input type="number" step="0.0001" x-model="editData.beta_5y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Max DD 1Th</label>
                    <input type="number" step="0.0001" x-model="editData.max_drawdown_1y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Max DD 3Th</label>
                    <input type="number" step="0.0001" x-model="editData.max_drawdown_3y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Max DD 5Th</label>
                    <input type="number" step="0.0001" x-model="editData.max_drawdown_5y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="editModal = null" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-emerald-700 rounded-lg hover:bg-emerald-800">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Biaya --}}
<div x-show="editModal === 'biaya'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
    @click.self="editModal = null">
    <div class="absolute inset-0 bg-black/40" @click="editModal = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-lg max-h-[85vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <h3 class="font-bold text-primary">Edit Biaya</h3>
            <button @click="editModal = null" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
        </div>
        <form @submit.prevent="submitEdit('biaya')" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Subscription Fee (%)</label>
                    <input type="number" step="0.01" x-model="editData.subscription_fee" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Redemption Fee (%)</label>
                    <input type="number" step="0.01" x-model="editData.redemption_fee" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Switching Fee (%)</label>
                    <input type="number" step="0.01" x-model="editData.switching_fee" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Management Fee (%)</label>
                    <input type="number" step="0.01" x-model="editData.management_fee" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Custodian Fee (%)</label>
                    <input type="number" step="0.01" x-model="editData.custodian_fee" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Expense Ratio (%)</label>
                    <input type="number" step="0.0001" x-model="editData.expense_ratio" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">IM Fee</label>
                <input type="text" x-model="editData.investment_manager_fee" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Min Pembelian (Rp)</label>
                    <input type="number" step="1" x-model="editData.minimum_subscription" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Min Top Up (Rp)</label>
                    <input type="number" step="1" x-model="editData.minimum_topup" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Min Redemption (Rp)</label>
                    <input type="number" step="1" x-model="editData.minimum_redemption" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="editModal = null" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-emerald-700 rounded-lg hover:bg-emerald-800">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Portofolio --}}
<div x-show="editModal === 'portofolio'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
    @click.self="editModal = null">
    <div class="absolute inset-0 bg-black/40" @click="editModal = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-2xl max-h-[85vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <h3 class="font-bold text-primary">Edit Portofolio</h3>
            <button @click="editModal = null" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
        </div>
        <form @submit.prevent="savePortfolio()" class="p-6 space-y-4">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Saham (%)</label>
                    <input type="number" step="0.01" x-model="portfolioSaham" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Obligasi (%)</label>
                    <input type="number" step="0.01" x-model="portfolioObligasi" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Pasar Uang (%)</label>
                    <input type="number" step="0.01" x-model="portfolioPasarUang" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kas (%)</label>
                    <input type="number" step="0.01" x-model="portfolioKas" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Top Holdings <span class="text-[10px] text-muted/60">(format: NamaEfek:Bobot:Jenis, tiap baris 1 efek)</span></label>
                <textarea x-model="portfolioTopHoldings" rows="5" class="w-full border border-line rounded-lg px-3 py-2 text-sm font-mono"></textarea>
            </div>
            <div class="border-t border-line pt-4">
                <h4 class="font-bold text-primary text-xs mb-3">Ringkasan Kinerja</h4>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">NAB/UP</label>
                        <input type="number" step="0.0001" x-model="portfolioNabPerUnit" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Tanggal NAB</label>
                        <input type="date" x-model="portfolioTanggalNab" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">AUM</label>
                        <input type="number" step="0.01" x-model="portfolioAum" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Total Unit</label>
                        <input type="number" step="0.01" x-model="portfolioTotalUnit" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Return YTD</label>
                        <input type="number" step="0.0001" x-model="portfolioReturnYtd" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Return 1 Thn</label>
                        <input type="number" step="0.0001" x-model="portfolioReturn1y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Return 1 Bln</label>
                        <input type="number" step="0.0001" x-model="portfolioReturn1m" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Return Inception</label>
                        <input type="number" step="0.0001" x-model="portfolioReturnInception" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <div>
                    <template x-if="portfolioSuccess">
                        <span class="text-xs text-green-600 font-semibold" x-text="portfolioSuccess"></span>
                    </template>
                    <template x-if="portfolioError">
                        <span class="text-xs text-red-600 font-semibold" x-text="portfolioError"></span>
                    </template>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="editModal = null; portfolioSuccess = null; portfolioError = null" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-gray-50">Batal</button>
                    <button type="submit" :disabled="portfolioSaving" class="px-4 py-2 text-sm text-white bg-emerald-700 rounded-lg hover:bg-emerald-800 disabled:opacity-50">
                        <span x-text="portfolioSaving ? 'Menyimpan...' : 'Simpan'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>

<script>
function reksaDanaShow() {
    return {
        tab: @js(request('tab', 'snapshot')),
        periodMonthNames: @js(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']),
        get periodLabel() {
            if (!this.portfolioMonth || !this.portfolioYear) return '';
            const month = this.periodMonthNames[parseInt(this.portfolioMonth, 10) - 1] || '-';
            return `${month} ${this.portfolioYear}`;
        },
        setTab(name) {
            this.tab = name;
            const url = new URL(window.location);
            url.searchParams.set('tab', name);
            window.history.replaceState({}, '', url);
        },
        applyPeriodFilter() {
            if (!this.portfolioMonth || !this.portfolioYear) return;
            const url = new URL(@js(route('admin.daftar-reksa-dana.show', $fund)), window.location.origin);
            url.searchParams.set('tab', this.tab);
            url.searchParams.set('month', this.portfolioMonth);
            url.searchParams.set('year', this.portfolioYear);
            const current = new URL(window.location);
            ['range', 'from_date', 'to_date'].forEach((key) => {
                const val = current.searchParams.get(key);
                if (val) url.searchParams.set(key, val);
            });
            window.location.href = url.toString();
        },
        resetPeriod() {
            const url = new URL(@js(route('admin.daftar-reksa-dana.show', $fund)), window.location.origin);
            url.searchParams.set('tab', this.tab);
            const current = new URL(window.location);
            ['range', 'from_date', 'to_date'].forEach((key) => {
                const val = current.searchParams.get(key);
                if (val) url.searchParams.set(key, val);
            });
            window.location.href = url.toString();
        },
        personModal: { open: false, loading: false, error: null, data: null },
        parserLocks: @js($fund->parser_locks ?? []),
        toggling: false,
        editModal: null,
        editData: {},
        editSaving: false,
        openEdit(section) {
            this.editData = {};
            if (section === 'ringkasan') {
                this.editData = { nab_per_unit: @js($displayNab), tanggal_nab: @js($periodActive && $snapshot?->period_date ? $snapshot->period_date->format('Y-m-d') : ($periodActive && $nav?->tanggal ? $nav->tanggal->format('Y-m-d') : $fund->tanggal_nab?->format('Y-m-d'))), aum: @js($displayAum), total_unit: @js($displayTotalUnit) };
            } else if (section === 'risiko') {
                this.editData = {
                    risk_category: @js($fund->risk_category),
                    sharpe_ratio_1y: @js($riskMetrics['sharpe_ratio_1y']), sharpe_ratio_3y: @js($riskMetrics['sharpe_ratio_3y']), sharpe_ratio_5y: @js($riskMetrics['sharpe_ratio_5y']),
                    stdev_1y: @js($riskMetrics['stdev_1y']), stdev_3y: @js($riskMetrics['stdev_3y']), stdev_5y: @js($riskMetrics['stdev_5y']),
                    beta_1y: @js($riskMetrics['beta_1y']), beta_3y: @js($riskMetrics['beta_3y']), beta_5y: @js($riskMetrics['beta_5y']),
                    max_drawdown_1y: @js($riskMetrics['max_drawdown_1y']), max_drawdown_3y: @js($riskMetrics['max_drawdown_3y']), max_drawdown_5y: @js($riskMetrics['max_drawdown_5y']),
                };
            } else if (section === 'biaya') {
                this.editData = {
                    subscription_fee: @js($fees?->subscription_fee ?? ($periodActive ? null : $fund->subscription_fee)),
                    redemption_fee: @js($fees?->redemption_fee ?? ($periodActive ? null : $fund->redemption_fee)),
                    switching_fee: @js($fees?->switching_fee ?? ($periodActive ? null : $fund->switching_fee)),
                    management_fee: @js($fees?->management_fee ?? ($periodActive ? null : $fund->management_fee)),
                    custodian_fee: @js($fees?->custodian_fee ?? ($periodActive ? null : $fund->custodian_fee)),
                    expense_ratio: @js($fees?->expense_ratio ?? ($periodActive ? null : $fund->expense_ratio)),
                    investment_manager_fee: @js($fees?->investment_manager_fee ?? ($periodActive ? null : $fund->investment_manager_fee)),
                    minimum_subscription: @js($periodActive ? null : $fund->minimum_subscription),
                    minimum_topup: @js($periodActive ? null : $fund->minimum_topup),
                    minimum_redemption: @js($periodActive ? null : $fund->minimum_redemption),
                };
            } else if (section === 'portofolio') {
                if (!this.portfolioMonth || !this.portfolioYear) return;
            }
            this.editModal = section;
        },
        async submitEdit(section) {
            if (this.editSaving) return;
            this.editSaving = true;
            try {
                const res = await fetch(@js(route('admin.daftar-reksa-dana.update-info', $fund)), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json' },
                    body: JSON.stringify(this.editData),
                });
                const json = await res.json();
                if (json.success) location.reload();
            } catch (e) {}
            this.editSaving = false;
        },
        isLocked(section) { return this.parserLocks.includes(section) },
        async toggleLock(section) {
            if (this.toggling) return;
            this.toggling = true;
            try {
                const res = await fetch(@js(route('admin.daftar-reksa-dana.toggle-parser-lock', $fund)), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json' },
                    body: JSON.stringify({ section }),
                });
                const json = await res.json();
                if (json.success) this.parserLocks = json.parser_locks;
            } catch (e) {}
            this.toggling = false;
        },
        async openPerson(name) {
            this.personModal = { open: true, loading: true, error: null, data: null };
            try {
                const url = @js(route('admin.investment-person-roles.detail')) + '?name=' + encodeURIComponent(name);
                const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                if (!res.ok) {
                    this.personModal.error = json.message || 'Gagal mengambil detail.';
                    return;
                }
                this.personModal.data = json;
            } catch (e) {
                this.personModal.error = e.message;
            } finally {
                this.personModal.loading = false;
            }
        },
        portfolioMonth: @js($periodMonth ?? ''),
        portfolioYear: @js($periodYear ?? ''),
        portfolioSaving: false,
        portfolioSaham: @js($allocation?->equity_percent ?? 0),
        portfolioObligasi: @js($allocation?->bond_percent ?? 0),
        portfolioPasarUang: @js($allocation?->money_market_percent ?? 0),
        portfolioKas: @js($allocation?->cash_percent ?? 0),
        portfolioTopHoldings: @js($holdings->map(fn($h) => trim("{$h->security_name}:{$h->weight_percent}:{$h->security_type}"))->implode("\n")),
        portfolioNabPerUnit: @js($displayNab),
        portfolioTanggalNab: @js($periodActive && $snapshot?->period_date ? $snapshot->period_date->format('Y-m-d') : ($periodActive && $nav?->tanggal ? $nav->tanggal->format('Y-m-d') : $fund->tanggal_nab?->format('Y-m-d'))),
        portfolioAum: @js($displayAum),
        portfolioTotalUnit: @js($displayTotalUnit),
        portfolioReturnYtd: @js($snapshot?->return_ytd ?? ($periodActive ? null : $fund->return_ytd)),
        portfolioReturn1y: @js($snapshot?->return_1y ?? ($periodActive ? null : $fund->return_1y)),
        portfolioReturn1m: @js($snapshot?->return_1m ?? ($periodActive ? null : $fund->return_1m)),
        portfolioReturnInception: @js($snapshot?->return_inception ?? ($periodActive ? null : $fund->return_inception)),
        portfolioSuccess: null,
        portfolioError: null,
        loadPortfolioData() {},
        async savePortfolio() {
            if (this.portfolioSaving) return;
            this.portfolioSaving = true;
            this.portfolioSuccess = null;
            this.portfolioError = null;
            try {
                const res = await fetch(@js(route('admin.daftar-reksa-dana.save-portfolio', $fund)), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json' },
                    body: JSON.stringify({
                        month: this.portfolioMonth,
                        year: this.portfolioYear,
                        saham: this.portfolioSaham,
                        obligasi: this.portfolioObligasi,
                        pasar_uang: this.portfolioPasarUang,
                        kas: this.portfolioKas,
                        top_holdings: this.portfolioTopHoldings,
                        nab_per_unit: this.portfolioNabPerUnit,
                        tanggal_nab: this.portfolioTanggalNab,
                        aum: this.portfolioAum,
                        total_unit: this.portfolioTotalUnit,
                        return_ytd: this.portfolioReturnYtd,
                        return_1y: this.portfolioReturn1y,
                        return_1m: this.portfolioReturn1m,
                        return_inception: this.portfolioReturnInception,
                    }),
                });
                const json = await res.json();
                if (json.success) {
                    this.portfolioSuccess = json.message || 'Data portfolio berhasil disimpan.';
                    setTimeout(() => location.reload(), 1000);
                } else {
                    this.portfolioError = json.message || 'Gagal menyimpan.';
                }
            } catch (e) {
                this.portfolioError = e.message;
            }
            this.portfolioSaving = false;
        },
        ffsPreviewLoading: false,
        ffsPreviewData: null,
        ffsPreviewError: null,
        ffsPreviewSaving: false,
        ffsPreviewSuccess: null,
        async showFfsPreview(docId) {
            try {
                this.ffsPreviewLoading = true;
                this.ffsPreviewData = null;
                this.ffsPreviewError = null;
                const formData = new FormData();
                formData.append('_token', @js(csrf_token()));
                const res = await fetch(`/admin/daftar-reksa-dana/documents/${docId}/parse-ffs`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': @js(csrf_token()) },
                    body: formData,
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.error || 'Gagal parse FFS.');
                this.ffsPreviewData = json;
                this.openFfsPreviewModal();
            } catch (e) {
                this.ffsPreviewError = e.message;
            } finally {
                this.ffsPreviewLoading = false;
            }
        },
        openFfsPreviewModal() {
            document.getElementById('modal-ffs-preview').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            this.renderFfsPreviewContent();
        },
        closeFfsPreviewModal() {
            document.getElementById('modal-ffs-preview').classList.add('hidden');
            document.body.style.overflow = '';
            this.ffsPreviewData = null;
        },
        async saveFfsPreview() {
            if (!this.ffsPreviewData?.preview) return;
            this.ffsPreviewSaving = true;
            this.ffsPreviewError = null;
            this.ffsPreviewSuccess = null;
            try {
                const formData = new FormData();
                formData.append('_token', @js(csrf_token()));
                formData.append('reksa_dana_id', @js($fund->id));
                formData.append('month', this.ffsPreviewData.period?.month || this.portfolioMonth);
                formData.append('year', this.ffsPreviewData.period?.year || this.portfolioYear);
                formData.append('snapshot', JSON.stringify(this.ffsPreviewData.data?.snapshot || {}));
                formData.append('risk', JSON.stringify(this.ffsPreviewData.data?.risk || {}));
                formData.append('fees', JSON.stringify(this.ffsPreviewData.data?.fees || {}));
                formData.append('allocation', JSON.stringify(this.ffsPreviewData.data?.allocation || {}));
                formData.append('holdings', JSON.stringify(this.ffsPreviewData.data?.holdings || []));
                const res = await fetch(@js(route('admin.daftar-reksa-dana.save-ffs-period', $fund)), {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': @js(csrf_token()) },
                    body: formData,
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.error || json.message || 'Gagal menyimpan data FFS.');
                this.ffsPreviewSuccess = json.message;
                setTimeout(() => {
                    this.closeFfsPreviewModal();
                    window.location.reload();
                }, 1000);
            } catch (e) {
                this.ffsPreviewError = e.message;
            } finally {
                this.ffsPreviewSaving = false;
            }
        },
        renderFfsPreviewContent() {
            const content = document.getElementById('ffs-preview-content');
            if (!this.ffsPreviewData) {
                content.innerHTML = '<div class="text-center py-12 text-muted">Memuat preview...</div>';
                return;
            }
            const data = this.ffsPreviewData.data || {};
            const period = this.ffsPreviewData.period || {};
            const extracted = this.ffsPreviewData.extracted || [];
            let html = `<div class="space-y-6"><div class="flex items-center justify-between mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg"><h4 class="font-semibold text-primary">Preview Data FFS</h4><span class="text-xs text-muted">Periode: ${period.month ? period.month + '/' + period.year : 'Belum ditentukan'}</span></div><div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg"><p class="text-xs text-yellow-700"><strong>⚠️ Preview Only:</strong> Data ini diekstrak dari FFS oleh AI. Mohon review dan validasi setiap field sebelum menyimpan.</p></div>`;
            if (data.snapshot) {
                html += `<div class="bg-white border border-line rounded-lg p-4"><h5 class="font-semibold text-primary mb-3">Ringkasan (Snapshot)</h5><div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">${this.renderPreviewField('NAB/UP', data.snapshot?.nab_per_unit, 'Rp')}${this.renderPreviewField('AUM', data.snapshot?.aum, 'Rp')}${this.renderPreviewField('Total Unit', data.snapshot?.total_unit)}${this.renderPreviewField('Return 1M', data.snapshot?.return_1m, '%')}${this.renderPreviewField('Return 3M', data.snapshot?.return_3m, '%')}${this.renderPreviewField('Return YTD', data.snapshot?.return_ytd, '%')}${this.renderPreviewField('Return 1Y', data.snapshot?.return_1y, '%')}${this.renderPreviewField('Return 3Y', data.snapshot?.return_3y, '%')}${this.renderPreviewField('Return Inception', data.snapshot?.return_inception, '%')}</div></div>`;
            }
            if (data.risk) {
                html += `<div class="bg-white border border-line rounded-lg p-4"><h5 class="font-semibold text-primary mb-3">Metrik Risiko</h5><div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">${this.renderPreviewField('Sharpe 1M', data.risk?.sharpe_ratio?.['1m'])}${this.renderPreviewField('Sharpe 1Y', data.risk?.sharpe_ratio?.['1y'])}${this.renderPreviewField('Stdev 1M', data.risk?.stdev?.['1m'], '%')}${this.renderPreviewField('Stdev 1Y', data.risk?.stdev?.['1y'], '%')}${this.renderPreviewField('Beta 1M', data.risk?.beta?.['1m'])}${this.renderPreviewField('Beta 1Y', data.risk?.beta?.['1y'])}${this.renderPreviewField('MaxDD 1M', data.risk?.max_drawdown?.['1m'], '%')}${this.renderPreviewField('MaxDD 1Y', data.risk?.max_drawdown?.['1y'], '%')}</div></div>`;
            }
            if (data.fees) {
                html += `<div class="bg-white border border-line rounded-lg p-4"><h5 class="font-semibold text-primary mb-3">Biaya & Fee</h5><div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">${this.renderPreviewField('Mgmt Fee', data.fees?.management_fee, '%')}${this.renderPreviewField('Custodian Fee', data.fees?.custodian_fee, '%')}${this.renderPreviewField('Expense Ratio', data.fees?.expense_ratio, '%')}${this.renderPreviewField('Sub Fee', data.fees?.subscription_fee, '%')}${this.renderPreviewField('Red Fee', data.fees?.redemption_fee, '%')}${this.renderPreviewField('Switch Fee', data.fees?.switching_fee, '%')}${this.renderPreviewField('IM Fee', data.fees?.investment_manager_fee, '%')}</div></div>`;
            }
            if (data.allocation) {
                html += `<div class="bg-white border border-line rounded-lg p-4"><h5 class="font-semibold text-primary mb-3">Alokasi Aset</h5><div class="grid grid-cols-4 gap-3 text-sm">${this.renderPreviewField('Saham', data.allocation?.equity_percent, '%')}${this.renderPreviewField('Obligasi', data.allocation?.bond_percent, '%')}${this.renderPreviewField('Pasar Uang', data.allocation?.money_market_percent, '%')}${this.renderPreviewField('Kas', data.allocation?.cash_percent, '%')}</div></div>`;
            }
            if (data.holdings?.length) {
                html += `<div class="bg-white border border-line rounded-lg p-4"><h5 class="font-semibold text-primary mb-3">Top Holdings (${data.holdings.length} efek)</h5><div class="overflow-x-auto"><table class="w-full text-xs"><thead class="bg-[#f8fafc]"><tr><th class="px-2 py-1 text-left font-semibold text-muted">Efek</th><th class="px-2 py-1 text-left font-semibold text-muted">Jenis</th><th class="px-2 py-1 text-right font-semibold text-muted">Bobot</th></tr></thead><tbody class="divide-y divide-line">${data.holdings.map(h => `<tr><td class="px-2 py-1 font-medium">${h.security_name}</td><td class="px-2 py-1 text-muted">${h.security_type || '—'}</td><td class="px-2 py-1 text-right font-semibold">${Number(h.weight_percent || 0).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}%</td></tr>`).join('')}</tbody></table></div></div>`;
            }
            if (this.ffsPreviewData.extracted?.length) {
                html += `<div class="bg-white border border-line rounded-lg p-4"><h5 class="font-semibold text-primary mb-2">Data yang Terektrak (${extracted.length} item)</h5><div class="flex flex-wrap gap-1">${extracted.map(e => `<span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded-full">${e}</span>`).join('')}</div></div>`;
            }
            const saveLabel = this.ffsPreviewSaving ? 'Menyimpan...' : 'Simpan ke Periode';
            html += `<div class="flex justify-end gap-2 pt-4 border-t border-line"><button type="button" data-ffs-cancel class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-gray-50">Batal</button><button type="button" data-ffs-save class="px-4 py-2 text-sm text-white bg-emerald-700 rounded-lg hover:bg-emerald-800 disabled:opacity-50 flex items-center gap-1.5" ${this.ffsPreviewSaving ? 'disabled' : ''}>${saveLabel}</button></div>`;
            content.innerHTML = html;
            content.querySelector('[data-ffs-cancel]')?.addEventListener('click', () => this.closeFfsPreviewModal());
            content.querySelector('[data-ffs-save]')?.addEventListener('click', () => this.saveFfsPreview());
        },
        renderPreviewField(label, value, suffix = '') {
            if (value === null || value === undefined || value === '') return '';
            const formatted = typeof value === 'number' ? value.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : value;
            return `<div><p class="text-xs text-muted">${label}</p><p class="font-semibold text-primary">${formatted}${suffix}</p></div>`;
        },
        portfolioLegendModal: { open: false },
        openPortfolioLegendModal() { this.portfolioLegendModal.open = true; },
        extractionModal: { open: false, loading: false, error: null, documentName: null, createdAt: null, items: [] },
        async openExtractionDetail(id) {
            this.extractionModal = { open: true, loading: true, error: null, documentName: null, createdAt: null, items: [] };
            try {
                const res = await fetch(`/admin/daftar-reksa-dana/extraction-results/${id}/view`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.error || 'Gagal mengambil data.');
                this.extractionModal.documentName = json.document_name;
                this.extractionModal.createdAt = json.created_at;
                const data = json.extracted_data || {};
                const labelize = s => s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                const isEmpty = v => v === null || v === undefined || v === '' || (Array.isArray(v) && v.length === 0) || (typeof v === 'object' && !Array.isArray(v) && Object.keys(v).length === 0);
                this.extractionModal.items = Object.entries(data)
                    .filter(([, v]) => !isEmpty(v))
                    .map(([key, val]) => {
                        const label = labelize(key);
                        if (Array.isArray(val)) {
                            if (val.length === 0) return null;
                            if (typeof val[0] === 'object' && val[0] !== null) {
                                const headers = [...new Set(val.flatMap(Object.keys))];
                                const rows = val.map(obj => headers.map(h => {
                                    const v = obj[h];
                                    if (v === null || v === undefined || v === '') return null;
                                    if (typeof v === 'number') return Number(v).toLocaleString('id-ID');
                                    if (typeof v === 'boolean') return v ? 'Ya' : 'Tidak';
                                    if (typeof v === 'object') return JSON.stringify(v);
                                    return String(v);
                                }));
                                return { type: 'table', key: label, headers, rows };
                            }
                            const values = val.map(v => v === null || v === undefined ? '—' : (typeof v === 'object' ? JSON.stringify(v) : String(v)));
                            return { type: 'list', key: label, values };
                        }
                        if (typeof val === 'object' && val !== null) {
                            const headers = Object.keys(val);
                            const rows = [headers.map(h => {
                                const v = val[h];
                                if (v === null || v === undefined || v === '') return null;
                                if (typeof v === 'object') return JSON.stringify(v);
                                return typeof v === 'number' ? Number(v).toLocaleString('id-ID') : String(v);
                            })];
                            return { type: 'table', key: label, headers, rows };
                        }
                        const display = typeof val === 'number' ? Number(val).toLocaleString('id-ID') : String(val);
                        return { type: 'simple', key: label, value: display };
                    })
                    .filter(Boolean);
            } catch (e) {
                this.extractionModal.error = e.message;
            } finally {
                this.extractionModal.loading = false;
            }
        },
        async deleteExtraction(id) {
            if (!confirm('Hapus data ekstrak ini?')) return;
            try {
                const res = await fetch(`/admin/daftar-reksa-dana/extraction-results/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': @js(csrf_token()) },
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.error || 'Gagal menghapus.');
                window.location.reload();
            } catch (e) {
                alert(e.message);
            }
        },
    };
}

function documentTabData(defaultDocType) {
    return {
        docType: defaultDocType,
        selectedPartitionIds: [],
        selectedPageContent: null,
        loading: false,
        error: null,
        success: null,
        loadingFfs: {},
        ffsSuccess: {},
        ffsError: {},
        pageContentCache: {},
        partitionsByDoc: @json($fund->documents->mapWithKeys(fn($d) => [$d->id => $d->partitions->map(fn($p) => ['id' => $p->id, 'start_page' => $p->start_page, 'end_page' => $p->end_page])->keyBy('id')])),

        partitionModal: {
            open: false,
            editing: null,
            nama: '',
            start: 1,
            end: 10,
            documentId: null,
            error: null,
            saving: false,
        },

        isPageInSelectedPartition(docId, pageParse) {
            const partitions = this.partitionsByDoc[docId];
            if (!partitions) return false;
            return this.selectedPartitionIds.some(pid => {
                const p = partitions[pid];
                return p && pageParse >= p.start_page && pageParse <= p.end_page;
            });
        },

        async showPageContent(docId, pageId) {
            const cacheKey = docId + '_' + pageId;
            if (this.pageContentCache[cacheKey]) {
                this.selectedPageContent = this.pageContentCache[cacheKey];
                return;
            }

            try {
                const url = `/admin/daftar-reksa-dana/documents/${docId}/parsed-pages`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                if (!res.ok) throw new Error(json.error || 'Gagal mengambil data.');

                const page = json.pages.find(p => p.id === pageId);
                if (page) {
                    this.selectedPageContent = '<pre class="text-xs whitespace-pre-wrap">' + this.escapeHtml(page.text_content) + '</pre>';
                    this.pageContentCache[cacheKey] = this.selectedPageContent;
                }
            } catch (e) {
                this.selectedPageContent = '<span class="text-red-600">' + this.escapeHtml(e.message) + '</span>';
            }
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        openPartitionModal(docId, editingPartition = null) {
            this.partitionModal = {
                open: true,
                editing: editingPartition,
                nama: editingPartition ? editingPartition.nama_partisi : '',
                start: editingPartition ? editingPartition.start_page : 1,
                end: editingPartition ? editingPartition.end_page : 10,
                documentId: docId,
                error: null,
                saving: false,
            };
        },

        async savePartition() {
            const pm = this.partitionModal;
            if (!pm.nama || !pm.start || !pm.end) {
                pm.error = 'Semua field harus diisi.';
                return;
            }
            if (parseInt(pm.start) > parseInt(pm.end)) {
                pm.error = 'Halaman mulai harus lebih kecil atau sama dengan halaman selesai.';
                return;
            }

            pm.saving = true;
            pm.error = null;

            try {
                const url = pm.editing
                    ? `/admin/daftar-reksa-dana/partitions/${pm.editing.id}/update`
                    : `/admin/daftar-reksa-dana/partitions`;
                const method = pm.editing ? 'POST' : 'POST';

                const body = new FormData();
                body.append('_token', '{{ csrf_token() }}');
                body.append('document_id', pm.documentId);
                body.append('nama_partisi', pm.nama);
                body.append('start_page', pm.start);
                body.append('end_page', pm.end);

                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body,
                });

                const json = await res.json();
                if (!res.ok) throw new Error(json.error || json.message || 'Gagal menyimpan partisi.');

                pm.open = false;
                window.location.reload();
            } catch (e) {
                pm.error = e.message;
            } finally {
                pm.saving = false;
            }
        },

        async deletePartition(partitionId) {
            if (!confirm('Hapus partisi ini?')) return;

            try {
                const res = await fetch(`/admin/daftar-reksa-dana/partitions/${partitionId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                });
                if (!res.ok) throw new Error('Gagal menghapus partisi.');
                window.location.reload();
            } catch (e) {
                alert(e.message);
            }
        },

        async handleParseFfs(docId) {
            this.loadingFfs[docId] = true;
            this.ffsSuccess[docId] = null;
            this.ffsError[docId] = null;

            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');

                const res = await fetch(`/admin/daftar-reksa-dana/documents/${docId}/parse-ffs`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData,
                });

                const json = await res.json();
                if (!res.ok) throw new Error(json.error || 'Gagal parse FFS.');

                this.ffsSuccess[docId] = json.message;
            } catch (e) {
                this.ffsError[docId] = e.message;
            } finally {
                this.loadingFfs[docId] = false;
            }
        },

        async handleParseSimpan(docId) {
            if (this.selectedPartitionIds.length === 0) return;

            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('reksa_dana_id', '{{ $fund->id }}');
                formData.append('document_id', docId);
                this.selectedPartitionIds.forEach(pid => formData.append('partition_ids[]', pid));

                const res = await fetch('{{ route('admin.daftar-reksa-dana.extract-data') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData,
                });

                const json = await res.json();
                if (!res.ok) throw new Error(json.error || 'Gagal mengekstrak data.');

                this.success = json.message;
            } catch (e) {
                this.error = e.message;
            } finally {
                this.loading = false;
            }
        },

        uploadFfsOpen: false,
        uploadFfsFile: null,
        uploadFfsMonth: '',
        uploadFfsYear: '',
        uploadFfsLoading: false,
        uploadFfsError: null,
        uploadFfsSuccess: null,

        async uploadFfs() {
            if (!this.uploadFfsFile || !this.uploadFfsMonth || !this.uploadFfsYear) return;
            this.uploadFfsLoading = true;
            this.uploadFfsError = null;
            this.uploadFfsSuccess = null;
            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('reksa_dana_id', '{{ $fund->id }}');
                formData.append('document_type', 'ffs');
                formData.append('file', this.uploadFfsFile);
                formData.append('ffs_month', this.uploadFfsMonth);
                formData.append('ffs_year', this.uploadFfsYear);

                const res = await fetch('{{ route('admin.daftar-reksa-dana.documents.store') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData,
                });

                const json = await res.json();
                if (!res.ok) throw new Error(json.error || json.errors?.[Object.keys(json.errors || {})[0]]?.[0] || 'Gagal upload.');

                this.uploadFfsSuccess = json.message;
                this.uploadFfsFile = null;
                this.uploadFfsMonth = '';
                this.uploadFfsYear = '';
                this.uploadFfsOpen = false;
                setTimeout(() => window.location.reload(), 1500);
            } catch (e) {
                this.uploadFfsError = e.message;
            } finally {
                this.uploadFfsLoading = false;
            }
        },
};
    }
</script>

@endsection
