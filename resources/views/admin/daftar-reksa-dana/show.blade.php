@extends('layouts.admin')

@section('title', $fund->nama_reksa_dana . ' - Detail Reksa Dana')

@section('content')
<div x-data="{ tab: 'snapshot' }">

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-muted mb-3">
        <a href="{{ route('admin.daftar-reksa-dana.index') }}" class="hover:text-primary transition">Daftar Reksa Dana</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary font-medium">{{ $fund->nama_reksa_dana }}</span>
    </div>
    <h1 class="page-title">{{ $fund->nama_reksa_dana }}</h1>
    <div class="flex flex-wrap gap-3 mt-2 text-sm text-muted">
        @if($fund->kode_reksa_dana)<span class="font-mono text-xs bg-[#f1f5f9] px-2 py-1 rounded">{{ $fund->kode_reksa_dana }}</span>@endif
        @if($fund->nama_manajer_investasi)<span>{{ $fund->nama_manajer_investasi }}</span>@endif
        @if($fund->jenis)<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">{{ $fund->jenis }}</span>@endif
        @if($fund->risk_category)<span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $fund->risk_category == 'Rendah' ? 'bg-green-100 text-green-700' : ($fund->risk_category == 'Sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $fund->risk_category }}</span>@endif
        @if($fund->tanggal_nab)<span>Data: {{ $fund->tanggal_nab->format('d M Y') }}</span>@endif
    </div>
</div>

{{-- Ringkasan --}}
<div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">NAV / NAB-UP</p>
        <p class="text-sm font-bold text-primary">{{ $latestNav ? number_format($latestNav->nab_per_unit, 4, ',', '.') : ($fund->nab_per_unit ? number_format($fund->nab_per_unit, 4, ',', '.') : '—') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">Return Harian</p>
        <p class="text-sm font-bold {{ $returnDaily !== null ? ($returnDaily >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $returnDaily !== null ? number_format($returnDaily, 2, ',', '.') . '%' : '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">Return Bulanan</p>
        <p class="text-sm font-bold {{ $returnMonthly !== null ? ($returnMonthly >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $returnMonthly !== null ? number_format($returnMonthly, 2, ',', '.') . '%' : '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">Return Tahunan</p>
        <p class="text-sm font-bold {{ $returnYearly !== null ? ($returnYearly >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $returnYearly !== null ? number_format($returnYearly, 2, ',', '.') . '%' : '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">AUM</p>
        <p class="text-sm font-bold text-primary">{{ $latestNav && $latestNav->aum ? 'Rp' . number_format($latestNav->aum, 0, ',', '.') : '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">Unit Penyertaan</p>
        <p class="text-sm font-bold text-primary">{{ $latestNav && $latestNav->unit_participation ? number_format($latestNav->unit_participation, 0, ',', '.') : '—' }}</p>
    </div>
</div>

{{-- Tab Navigation --}}
<div class="flex items-center gap-1 mb-6 border-b border-line overflow-x-auto">
    <button @click="tab = 'snapshot'" :class="tab === 'snapshot' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Snapshot</button>
    <button @click="tab = 'grafik'" :class="tab === 'grafik' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Grafik dan Data</button>
    <button @click="tab = 'risiko'" :class="tab === 'risiko' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Risiko</button>
    <button @click="tab = 'biaya'" :class="tab === 'biaya' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Biaya</button>
    <button @click="tab = 'portofolio'" :class="tab === 'portofolio' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Portofolio</button>
</div>

{{-- TAB: SNAPSHOT --}}
<div x-show="tab === 'snapshot'" x-cloak>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Informasi Reksa Dana --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
                <h2 class="font-bold text-white text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Informasi Reksa Dana
                </h2>
            </div>
            <div class="divide-y divide-line">
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Nama Reksa Dana</span><span class="text-sm">{{ $fund->nama_reksa_dana }}</span></div>
                @if($fund->kode_reksa_dana)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kode Reksa Dana</span><span class="text-sm font-mono">{{ $fund->kode_reksa_dana }}</span></div>@endif
                @if($fund->nama_manajer_investasi)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Manajer Investasi</span><span class="text-sm">{{ $fund->nama_manajer_investasi }}</span></div>@endif
                @if($fund->custodian_bank)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Bank Kustodian</span><span class="text-sm">{{ $fund->custodian_bank }}</span></div>@endif
                @if($fund->launch_date)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Tanggal Peluncuran</span><span class="text-sm">{{ $fund->launch_date->format('d M Y') }}</span></div>@endif
                @if($fund->mata_uang)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Mata Uang</span><span class="text-sm">{{ $fund->mata_uang }}</span></div>@endif
                @if($fund->jenis)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kategori</span><span class="text-sm">{{ $fund->kategori_label ?: $fund->jenis }}</span></div>@endif
                @if($fund->jenis)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Jenis Reksa Dana</span><span class="text-sm">{{ $fund->jenis }}</span></div>@endif
            </div>
        </div>

        {{-- Ringkasan Kinerja --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
                <h2 class="font-bold text-white text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Ringkasan Kinerja
                </h2>
            </div>
            <div class="divide-y divide-line">
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">NAV / NAB-UP</span><span class="text-sm font-bold text-primary">{{ $latestNav ? number_format($latestNav->nab_per_unit, 4, ',', '.') : ($fund->nab_per_unit ? number_format($fund->nab_per_unit, 4, ',', '.') : '—') }}</span></div>
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Return Harian</span><span class="text-sm font-bold {{ $returnDaily !== null ? ($returnDaily >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $returnDaily !== null ? number_format($returnDaily, 2, ',', '.') . '%' : '—' }}</span></div>
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Return Bulanan</span><span class="text-sm font-bold {{ $returnMonthly !== null ? ($returnMonthly >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $returnMonthly !== null ? number_format($returnMonthly, 2, ',', '.') . '%' : '—' }}</span></div>
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Return Tahunan</span><span class="text-sm font-bold {{ $returnYearly !== null ? ($returnYearly >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $returnYearly !== null ? number_format($returnYearly, 2, ',', '.') . '%' : '—' }}</span></div>
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">AUM</span><span class="text-sm font-bold text-primary">{{ $latestNav && $latestNav->aum ? 'Rp' . number_format($latestNav->aum, 0, ',', '.') : '—' }}</span></div>
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Unit Penyertaan</span><span class="text-sm font-bold text-primary">{{ $latestNav && $latestNav->unit_participation ? number_format($latestNav->unit_participation, 0, ',', '.') : '—' }}</span></div>
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

    {{-- Management Team --}}
    @php $committees = $fund->managementTeams->where('type', 'committee'); @endphp
    @php $investmentManagers = $fund->managementTeams->where('type', 'investment_manager'); @endphp
    @if($committees->isNotEmpty())
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm">Komite Investasi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach($committees as $mt)
                    <tr class="hover:bg-[#f8fafc]"><td class="px-4 py-3 text-xs">{{ $mt->name }}</td><td class="px-4 py-3 text-xs text-muted">{{ $mt->position ?? '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @if($investmentManagers->isNotEmpty())
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm">Tim Pengelola Investasi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach($investmentManagers as $mt)
                    <tr class="hover:bg-[#f8fafc]"><td class="px-4 py-3 text-xs">{{ $mt->name }}</td><td class="px-4 py-3 text-xs text-muted">{{ $mt->position ?? '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @if($fund->managementTeams->isEmpty() && !$fund->description)
    <div class="py-12 text-center text-muted bg-white rounded-2xl border border-line mt-6">
        <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm">Informasi reksa dana belum tersedia.</p>
    </div>
    @endif
</div>

{{-- TAB: GRAFIK DAN DATA --}}
<div x-show="tab === 'grafik'" x-cloak>
    {{-- Filter Range --}}
    <div class="mb-4">
        <form method="GET" action="{{ route('admin.daftar-reksa-dana.show', $fund) }}">
            <div class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="grafik">
                <span class="text-xs font-semibold text-muted mr-1">Range:</span>
                @foreach(['1m'=>'1B','3m'=>'3B','6m'=>'6B','ytd'=>'YTD','1y'=>'1T','3y'=>'3T','5y'=>'5T','all'=>'All'] as $k=>$l)
                <a href="{{ route('admin.daftar-reksa-dana.show', array_merge(['reksaDana' => $fund, 'range' => $k])) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $range === $k ? 'bg-primary text-white' : 'border border-line text-muted hover:bg-[#f1f5f9]' }}">{{ $l }}</a>
                @endforeach
            </div>
        </form>
    </div>

    @if($navLabels->isEmpty())
    <div class="py-12 text-center text-muted bg-white rounded-2xl border border-line">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        <p class="font-medium">Data grafik belum tersedia.</p>
    </div>
    @else
    <div class="space-y-6">
        {{-- Grafik NAV --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
            <h3 class="font-bold text-primary text-sm mb-4">Grafik NAV</h3>
            <div style="height: 300px;"><canvas id="chartNav"></canvas></div>
        </div>

        {{-- Grafik AUM & UP --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">AUM</h3>
                <div style="height: 250px;"><canvas id="chartAum"></canvas></div>
            </div>
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">Unit Penyertaan</h3>
                <div style="height: 250px;"><canvas id="chartUp"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Tabel Historis --}}
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm">Riwayat NAV / AUM / Unit Penyertaan</h2>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function fmt(v) { return v.toLocaleString('id-ID'); }
        const navLabels = {!! json_encode($navLabels) !!};
        const navData = {!! json_encode($navValues) !!};
        const aumData = {!! json_encode($aumValues) !!};
        const upData = {!! json_encode($upValues) !!};

        const lineOpts = {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => fmt(ctx.parsed.y) } } },
            scales: { y: { ticks: { callback: val => fmt(val) } } }
        };

        new Chart(document.getElementById('chartNav'), {
            type: 'line',
            data: { labels: navLabels, datasets: [{ data: navData, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.1)', fill: true, tension: 0.3, pointRadius: 2 }] },
            options: lineOpts
        });

        new Chart(document.getElementById('chartAum'), {
            type: 'bar',
            data: { labels: navLabels, datasets: [{ data: aumData, backgroundColor: 'rgba(37,99,235,0.7)', borderRadius: 3 }] },
            options: { ...lineOpts, indexAxis: 'y' }
        });

        new Chart(document.getElementById('chartUp'), {
            type: 'bar',
            data: { labels: navLabels, datasets: [{ data: upData, backgroundColor: 'rgba(5,150,105,0.7)', borderRadius: 3 }] },
            options: { ...lineOpts, indexAxis: 'y' }
        });
    });
    </script>
    @endif
</div>

{{-- TAB: RISIKO --}}
<div x-show="tab === 'risiko'" x-cloak>
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm">Informasi Risiko</h2>
        </div>
        @if($fund->risk_category || $fund->description)
        <div class="divide-y divide-line">
            @if($fund->risk_category)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Risk Category</span>
                <span class="text-sm">{{ $fund->risk_category }}</span>
            </div>
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Tingkat Risiko</span>
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
            @if($fund->description)
            <div class="px-6 py-3.5">
                <span class="text-xs font-semibold text-muted block mb-1">Catatan Risiko</span>
                <span class="text-sm whitespace-pre-line">{{ $fund->description }}</span>
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
</div>

{{-- TAB: BIAYA --}}
<div x-show="tab === 'biaya'" x-cloak>
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm">Informasi Biaya</h2>
        </div>
        @if($fund->subscription_fee || $fund->redemption_fee || $fund->switching_fee || $fund->management_fee || $fund->custodian_fee || $fund->minimum_subscription || $fund->minimum_topup || $fund->minimum_redemption)
        <div class="divide-y divide-line">
            @if($fund->subscription_fee)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Subscription Fee</span><span class="text-sm">{{ number_format($fund->subscription_fee, 2, ',', '.') }}%</span></div>@endif
            @if($fund->redemption_fee)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Redemption Fee</span><span class="text-sm">{{ number_format($fund->redemption_fee, 2, ',', '.') }}%</span></div>@endif
            @if($fund->switching_fee)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Switching Fee</span><span class="text-sm">{{ number_format($fund->switching_fee, 2, ',', '.') }}%</span></div>@endif
            @if($fund->management_fee)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Management Fee</span><span class="text-sm">{{ number_format($fund->management_fee, 2, ',', '.') }}%</span></div>@endif
            @if($fund->custodian_fee)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Custodian Fee</span><span class="text-sm">{{ number_format($fund->custodian_fee, 2, ',', '.') }}%</span></div>@endif
            @if($fund->minimum_subscription)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Minimum Pembelian</span><span class="text-sm">Rp{{ number_format($fund->minimum_subscription, 0, ',', '.') }}</span></div>@endif
            @if($fund->minimum_topup)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Minimum Top Up</span><span class="text-sm">Rp{{ number_format($fund->minimum_topup, 0, ',', '.') }}</span></div>@endif
            @if($fund->minimum_redemption)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Minimum Redemption</span><span class="text-sm">Rp{{ number_format($fund->minimum_redemption, 0, ',', '.') }}</span></div>@endif
        </div>
        @else
        <div class="py-12 text-center text-muted text-sm">
            <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Data biaya belum tersedia.
        </div>
        @endif
    </div>
</div>

{{-- TAB: PORTOFOLIO --}}
<div x-show="tab === 'portofolio'" x-cloak>
    @if($aaTimeline->isEmpty() && $topHoldings->isEmpty())
    <div class="py-12 text-center text-muted bg-white rounded-2xl border border-line">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        <p class="font-medium">Data portofolio belum tersedia.</p>
    </div>
    @else
    <div class="space-y-6">
        {{-- Asset Allocation Pie --}}
        @if($latestAa = $aaTimeline->last())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">Asset Allocation ({{ $latestAa->period_date->format('M Y') }})</h3>
                <div style="height: 280px;"><canvas id="chartAaPie"></canvas></div>
            </div>
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">Top Holdings</h3>
                @if($topHoldings->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-3 py-2 font-semibold">Efek</th><th class="px-3 py-2 font-semibold">Jenis</th><th class="px-3 py-2 font-semibold text-right">Bobot</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @foreach($topHoldings as $th)
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
            $ptLabels = $portfolioTimeline->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('M Y'));
            $allSecurities = $portfolioTimeline->flatMap(fn($items) => $items->pluck('security_name'))->unique();
            $ptColors = ['#2563eb','#059669','#d97706','#dc2626','#7c3aed','#0891b2','#db2777','#65a30d','#ca8a04','#ea580c','#4f46e5','#0d9488'];
        @endphp
        <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
            <h3 class="font-bold text-primary text-sm mb-4">Timeline Portfolio Composition</h3>
            <div style="height: 300px;"><canvas id="chartPtTimeline"></canvas></div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        {{-- Asset Allocation Pie --}}
        @if($latestAa = $aaTimeline->last())
        new Chart(document.getElementById('chartAaPie'), {
            type: 'pie',
            data: {
                labels: ['Saham', 'Obligasi', 'Pasar Uang', 'Kas'],
                datasets: [{
                    data: [{{ $latestAa->equity_percent ?? 0 }}, {{ $latestAa->bond_percent ?? 0 }}, {{ $latestAa->money_market_percent ?? 0 }}, {{ $latestAa->cash_percent ?? 0 }}],
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
                labels: {!! json_encode($aaLabels) !!},
                datasets: [
                    { label: 'Saham', data: {!! json_encode($aaTimeline->pluck('equity_percent')) !!}, backgroundColor: '#2563eb' },
                    { label: 'Obligasi', data: {!! json_encode($aaTimeline->pluck('bond_percent')) !!}, backgroundColor: '#059669' },
                    { label: 'Pasar Uang', data: {!! json_encode($aaTimeline->pluck('money_market_percent')) !!}, backgroundColor: '#d97706' },
                    { label: 'Kas', data: {!! json_encode($aaTimeline->pluck('cash_percent')) !!}, backgroundColor: '#6b7280' },
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
        const securities = {!! json_encode($allSecurities->values()) !!};
        const ptLabels = {!! json_encode($ptLabels) !!};
        const ptRaw = {!! json_encode($portfolioTimeline->map(fn($items) => $items->keyBy('security_name')->map(fn($i) => $i->weight_percent))) !!};

        securities.forEach((sec, i) => {
            datasets.push({
                label: sec,
                data: ptLabels.map((_, idx) => {
                    const periodKey = Object.keys(ptRaw)[idx];
                    return ptRaw[periodKey] && ptRaw[periodKey][sec] ? ptRaw[periodKey][sec] : 0;
                }),
                backgroundColor: '{!! json_encode($ptColors) !!}'[i % 12] || '#94a3b8',
            });
        });

        new Chart(document.getElementById('chartPtTimeline'), {
            type: 'bar',
            data: { labels: ptLabels, datasets: datasets },
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
    });
    </script>
    @endif
</div>

</div>
@endsection
