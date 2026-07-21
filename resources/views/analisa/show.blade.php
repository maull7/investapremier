@extends('layouts.user')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'data' }">
    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-muted mb-1">
                <a href="{{ route('user.analisa.index') }}" class="hover:text-primary">Analisa RD</a>
                <span>/</span>
                <span>{{ $analisa->nama_reksa_dana }}</span>
            </div>
            <h1 class="page-title">{{ $analisa->nama_reksa_dana }}</h1>
            <p class="page-sub">{{ $analisa->jenis_reksa_dana }} &bull; Disubmit {{ $analisa->created_at->format('d M Y') }}</p>
        </div>
        @php
            $badge = match($analisa->status) {
                'draft'     => 'bg-gray-100 text-gray-600',
                'submitted' => 'bg-yellow-100 text-yellow-700',
                'reviewed'  => 'bg-green-100 text-green-700',
                default     => 'bg-slate-100 text-slate-600',
            };
            $label = match($analisa->status) {
                'draft'     => 'Draft',
                'submitted' => 'Menunggu Review',
                'reviewed'  => 'Sudah Direview',
                default     => ucfirst($analisa->status ?? 'Unknown'),
            };
        @endphp
        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
    </div>

    <div class="flex justify-end gap-2">
        @if($analisa->pdf_path)
            <a href="{{ route('user.analisa.download-ffs', $analisa) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">
                FFS PDF
            </a>
        @endif
        <a href="{{ route('user.analisa.pdf', $analisa) }}" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">
            Export PDF
        </a>
    </div>

    @if($analisa->catatan_admin)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-700">
        <p class="font-medium mb-1">Catatan Admin:</p>
        <p>{{ $analisa->catatan_admin }}</p>
    </div>
    @endif

    <div class="flex gap-1 border-b border-line overflow-x-auto">
        <button type="button" @click="activeTab='data'"
            :class="activeTab==='data' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
            class="px-4 py-2.5 text-sm whitespace-nowrap transition">Data & Grafik</button>
        <button type="button" @click="activeTab='ai'"
            :class="activeTab==='ai' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
            class="px-4 py-2.5 text-sm whitespace-nowrap transition">Analisa AI</button>
        <button type="button" @click="activeTab='ai-plus'"
            :class="activeTab==='ai-plus' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
            class="px-4 py-2.5 text-sm whitespace-nowrap transition">Analisa AI Plus</button>
    </div>

    <div x-show="activeTab==='ai'">
        @php $aiOut = $analisa->ai_output ?? []; @endphp
        @if(!empty($aiOut['error']))
            @include('analisa.partials.ai-panel', ['title' => 'Analisa AI', 'variant' => 'standard', 'ai' => $aiOut, 'narasi' => null])
        @elseif($analisa->ai_narasi)
            @include('analisa.partials.ai-panel', ['title' => 'Analisa AI', 'variant' => 'standard', 'ai' => $aiOut, 'narasi' => $analisa->ai_narasi])
        @else
            <div class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted">Narasi AI sedang diproses... Refresh halaman.</div>
        @endif
    </div>

    <div x-show="activeTab==='ai-plus'">
        @php $aiPlusOut = $analisa->ai_output_plus ?? []; @endphp
        @if(!empty($aiPlusOut['error']))
            @include('analisa.partials.ai-panel', ['title' => 'Analisa AI Plus', 'variant' => 'plus', 'ai' => $aiPlusOut, 'narasi' => null])
        @elseif($analisa->ai_narasi_plus)
            @include('analisa.partials.ai-panel', ['title' => 'Analisa AI Plus', 'variant' => 'plus', 'ai' => $aiPlusOut, 'narasi' => $analisa->ai_narasi_plus])
        @else
            <div class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted">Analisa AI Plus sedang diproses... Refresh halaman.</div>
        @endif
    </div>

    <div x-show="activeTab==='data'" class="space-y-6">

    {{-- Info Keuangan --}}
    @if($analisa->total_aum || $analisa->total_marcap_10_efek || $analisa->nab_per_unit || $analisa->unit_penyertaan)
    <div class="bg-white rounded-xl border border-line p-6 space-y-4">
        <h3 class="font-semibold text-primary">Info Keuangan</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-muted text-xs block">Total AUM</span>
                <span class="font-medium text-primary">Rp {{ number_format($analisa->total_aum, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-muted text-xs block">Total MarCap 10 Saham Terbesar</span>
                <span class="font-medium text-primary">Rp {{ number_format($analisa->total_marcap_10_efek, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-muted text-xs block">NAB/UP</span>
                <span class="font-medium text-primary">{{ number_format($analisa->nab_per_unit, 2, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-muted text-xs block">Unit Penyertaan</span>
                <span class="font-medium text-primary">{{ number_format($analisa->unit_penyertaan, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-muted text-xs block">Kalender FFS</span>
                <span class="font-medium text-primary">
                    @if($analisa->ffs_bulan && $analisa->ffs_tahun)
                        {{ ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$analisa->ffs_bulan - 1] }} {{ $analisa->ffs_tahun }}
                    @elseif($analisa->jenis_laporan === 'laporan_tahunan' && $analisa->tahun_laporan)
                        Laporan Tahunan {{ $analisa->tahun_laporan }}
                    @else
                        —
                    @endif
                </span>
            </div>
            <div>
                <span class="text-muted text-xs block">Tanggal Data</span>
                <span class="font-medium text-primary">{{ $analisa->tanggal_data ? \Carbon\Carbon::parse($analisa->tanggal_data)->format('d M Y') : '—' }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Kinerja --}}
    @if($analisa->return_1m || $analisa->return_ytd || $analisa->return_1y)
    <div class="bg-white rounded-xl border border-line p-6">
        <h3 class="font-semibold text-primary mb-4">Kinerja</h3>
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-muted text-xs">Return 1 Bulan</p>
                <p class="font-medium mt-0.5">{{ number_format($analisa->return_1m, 2) }}%</p>
            </div>
            <div>
                <p class="text-muted text-xs">Return YTD</p>
                <p class="font-medium mt-0.5">{{ number_format($analisa->return_ytd, 2) }}%</p>
            </div>
            <div>
                <p class="text-muted text-xs">Return 1 Tahun</p>
                <p class="font-medium mt-0.5">{{ number_format($analisa->return_1y, 2) }}%</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $metrics = [
                ['label' => 'Sharpe Ratio', 'value' => $analisa->sharpe_ratio ?? '-', 'desc' => 'Return per unit risiko'],
                ['label' => 'RAR', 'value' => $analisa->rar ?? '-', 'desc' => 'Risk-Adjusted Return'],
                ['label' => 'Liquidity Ratio', 'value' => $analisa->liquidity_ratio ? number_format($analisa->liquidity_ratio * 100, 2).'%' : '-', 'desc' => 'AUM / MarCap 10 Efek'],
                ['label' => 'Durasi Rata-rata', 'value' => $analisa->durasi_rata_rata ? $analisa->durasi_rata_rata.' thn' : '-', 'desc' => 'Weighted Avg Duration'],
            ];
        @endphp
        @foreach($metrics as $m)
        <div class="bg-white rounded-xl border border-line p-5">
            <p class="text-xs text-muted">{{ $m['label'] }}</p>
            <p class="text-2xl font-bold text-primary mt-1">{{ $m['value'] }}</p>
            <p class="text-xs text-muted mt-1">{{ $m['desc'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if($analisa->sektor->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Komposisi per Sektor</h3>
            <canvas id="chartSektor" height="220"></canvas>
        </div>
        @endif
        @if($analisa->kinerja->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Return Bulanan (%)</h3>
            <canvas id="chartKinerja" height="220"></canvas>
        </div>
        @endif
    </div>

    {{-- Laporan Keuangan --}}
    @if($analisa->total_aset || $analisa->total_liabilitas || $analisa->kas_dan_bank || $analisa->piutang_bunga || $analisa->piutang_dividen || $analisa->piutang_lain || $analisa->utang_pajak || $analisa->utang_lain)
    <div class="bg-white rounded-xl border border-line p-6">
        <h3 class="font-semibold text-primary mb-4">Laporan Keuangan — Neraca</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            @if($analisa->total_aset)<div><p class="text-muted text-xs">Total Aset</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->total_aset, 0, ',', '.') }}</p></div>@endif
            @if($analisa->total_liabilitas)<div><p class="text-muted text-xs">Total Liabilitas</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->total_liabilitas, 0, ',', '.') }}</p></div>@endif
            @if($analisa->kas_dan_bank)<div><p class="text-muted text-xs">Kas dan Bank</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->kas_dan_bank, 0, ',', '.') }}</p></div>@endif
            @if($analisa->piutang_bunga)<div><p class="text-muted text-xs">Piutang Bunga</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->piutang_bunga, 0, ',', '.') }}</p></div>@endif
            @if($analisa->piutang_dividen)<div><p class="text-muted text-xs">Piutang Dividen</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->piutang_dividen, 0, ',', '.') }}</p></div>@endif
            @if($analisa->piutang_lain)<div><p class="text-muted text-xs">Piutang Lain</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->piutang_lain, 0, ',', '.') }}</p></div>@endif
            @if($analisa->utang_pajak)<div><p class="text-muted text-xs">Utang Pajak</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->utang_pajak, 0, ',', '.') }}</p></div>@endif
            @if($analisa->utang_lain)<div><p class="text-muted text-xs">Utang Lain</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->utang_lain, 0, ',', '.') }}</p></div>@endif
        </div>
    </div>
    @endif

    @if($analisa->pendapatan_bunga || $analisa->pendapatan_dividen || $analisa->gain_realized || $analisa->gain_unrealized || $analisa->beban_mi || $analisa->beban_kustodian || $analisa->beban_lain || $analisa->laba_bersih)
    <div class="bg-white rounded-xl border border-line p-6">
        <h3 class="font-semibold text-primary mb-4">Laporan Keuangan — Laba Rugi</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            @if($analisa->pendapatan_bunga)<div><p class="text-muted text-xs">Pendapatan Bunga</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->pendapatan_bunga, 0, ',', '.') }}</p></div>@endif
            @if($analisa->pendapatan_dividen)<div><p class="text-muted text-xs">Pendapatan Dividen</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->pendapatan_dividen, 0, ',', '.') }}</p></div>@endif
            @if($analisa->gain_realized)<div><p class="text-muted text-xs">Gain Realized</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->gain_realized, 0, ',', '.') }}</p></div>@endif
            @if($analisa->gain_unrealized)<div><p class="text-muted text-xs">Gain Unrealized</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->gain_unrealized, 0, ',', '.') }}</p></div>@endif
            @if($analisa->beban_mi)<div><p class="text-muted text-xs">Beban MI</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->beban_mi, 0, ',', '.') }}</p></div>@endif
            @if($analisa->beban_kustodian)<div><p class="text-muted text-xs">Beban Kustodian</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->beban_kustodian, 0, ',', '.') }}</p></div>@endif
            @if($analisa->beban_lain)<div><p class="text-muted text-xs">Beban Lain</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->beban_lain, 0, ',', '.') }}</p></div>@endif
            @if($analisa->laba_bersih)<div><p class="text-muted text-xs">Laba Bersih</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->laba_bersih, 0, ',', '.') }}</p></div>@endif
        </div>
    </div>
    @endif

    @if($analisa->arus_kas_operasi || $analisa->arus_kas_pendanaan || $analisa->kas_awal_tahun || $analisa->kas_akhir_tahun)
    <div class="bg-white rounded-xl border border-line p-6">
        <h3 class="font-semibold text-primary mb-4">Laporan Keuangan — Arus Kas</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            @if($analisa->arus_kas_operasi)<div><p class="text-muted text-xs">Arus Kas Operasi</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->arus_kas_operasi, 0, ',', '.') }}</p></div>@endif
            @if($analisa->arus_kas_pendanaan)<div><p class="text-muted text-xs">Arus Kas Pendanaan</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->arus_kas_pendanaan, 0, ',', '.') }}</p></div>@endif
            @if($analisa->kas_awal_tahun)<div><p class="text-muted text-xs">Kas Awal Tahun</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->kas_awal_tahun, 0, ',', '.') }}</p></div>@endif
            @if($analisa->kas_akhir_tahun)<div><p class="text-muted text-xs">Kas Akhir Tahun</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->kas_akhir_tahun, 0, ',', '.') }}</p></div>@endif
        </div>
    </div>
    @endif

    {{-- Rasio Keuangan Lengkap --}}
    @if($analisa->total_hasil_investasi || $analisa->hasil_investasi_setelah_biaya || $analisa->persentase_pph)
    <div class="bg-white rounded-xl border border-line p-6">
        <h3 class="font-semibold text-primary mb-4">Rasio Keuangan</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            @if($analisa->total_hasil_investasi)<div><p class="text-muted text-xs">Total Hasil Investasi</p><p class="font-medium mt-0.5">{{ number_format($analisa->total_hasil_investasi, 2) }}%</p></div>@endif
            @if($analisa->hasil_investasi_setelah_biaya)<div><p class="text-muted text-xs">Hasil Investasi (Setelah Biaya)</p><p class="font-medium mt-0.5">{{ number_format($analisa->hasil_investasi_setelah_biaya, 2) }}%</p></div>@endif
            @if($analisa->persentase_pph)<div><p class="text-muted text-xs">PPH</p><p class="font-medium mt-0.5">{{ number_format($analisa->persentase_pph, 2) }}%</p></div>@endif
        </div>
    </div>
    @endif

    {{-- Fair Value --}}
    @if($analisa->fair_value_level_1 || $analisa->fair_value_level_2 || $analisa->fair_value_level_3)
    <div class="bg-white rounded-xl border border-line p-6">
        <h3 class="font-semibold text-primary mb-4">Fair Value / Pengukuran Nilai Wajar</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            @if($analisa->fair_value_level_1)<div><p class="text-muted text-xs">Level 1</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->fair_value_level_1, 0, ',', '.') }}</p></div>@endif
            @if($analisa->fair_value_level_2)<div><p class="text-muted text-xs">Level 2</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->fair_value_level_2, 0, ',', '.') }}</p></div>@endif
            @if($analisa->fair_value_level_3)<div><p class="text-muted text-xs">Level 3</p><p class="font-medium mt-0.5">Rp {{ number_format($analisa->fair_value_level_3, 0, ',', '.') }}</p></div>@endif
        </div>
    </div>
    @endif

    {{-- Unit Penyertaan --}}
    @if($analisa->unit_milik_investor || $analisa->unit_milik_mi || $analisa->total_unit_beredar)
    <div class="bg-white rounded-xl border border-line p-6">
        <h3 class="font-semibold text-primary mb-4">Unit Penyertaan</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            @if($analisa->unit_milik_investor)<div><p class="text-muted text-xs">Unit Milik Investor</p><p class="font-medium mt-0.5">{{ number_format($analisa->unit_milik_investor, 4) }}</p></div>@endif
            @if($analisa->unit_milik_mi)<div><p class="text-muted text-xs">Unit Milik MI</p><p class="font-medium mt-0.5">{{ number_format($analisa->unit_milik_mi, 4) }}</p></div>@endif
            @if($analisa->total_unit_beredar)<div><p class="text-muted text-xs">Total Unit Beredar</p><p class="font-medium mt-0.5">{{ number_format($analisa->total_unit_beredar, 4) }}</p></div>@endif
        </div>
    </div>
    @endif

    {{-- Analisa Pengelolaan Investasi / Portofolio --}}
    @if($analisa->fee_cost_to_performance !== null || $analisa->pendapatan_terhadap_nab !== null || $analisa->beban_terhadap_pendapatan !== null || $analisa->pengelolaan_investasi_terhadap_pendapatan !== null || $analisa->transaction_profit_terhadap_nab !== null)
    <div class="bg-white rounded-xl border border-line p-6">
        <h3 class="font-semibold text-primary mb-4">Analisa Pengelolaan Investasi / Portofolio</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#f8fafc] border-b border-line">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-semibold text-primary">Analisa</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-primary">Hasil</th>
                    </tr>
                </thead>
                <tbody>
                    @if($analisa->fee_cost_to_performance !== null)
                    <tr>
                        <td class="px-4 py-2 text-muted">Fee Cost to Performance</td>
                        <td class="px-4 py-2 text-right font-medium">{{ number_format($analisa->fee_cost_to_performance, 4) }}</td>
                    </tr>
                    @endif
                    @if($analisa->pendapatan_terhadap_nab !== null)
                    <tr>
                        <td class="px-4 py-2 text-muted">Pendapatan terhadap NAB</td>
                        <td class="px-4 py-2 text-right font-medium">{{ number_format($analisa->pendapatan_terhadap_nab, 4) }}</td>
                    </tr>
                    @endif
                    @if($analisa->beban_terhadap_pendapatan !== null)
                    <tr>
                        <td class="px-4 py-2 text-muted">Beban terhadap Pendapatan</td>
                        <td class="px-4 py-2 text-right font-medium">{{ number_format($analisa->beban_terhadap_pendapatan, 4) }}</td>
                    </tr>
                    @endif
                    @if($analisa->pengelolaan_investasi_terhadap_pendapatan !== null)
                    <tr>
                        <td class="px-4 py-2 text-muted">Pengelolaan Investasi terhadap Pendapatan</td>
                        <td class="px-4 py-2 text-right font-medium">{{ number_format($analisa->pengelolaan_investasi_terhadap_pendapatan, 4) }}</td>
                    </tr>
                    @endif
                    @if($analisa->transaction_profit_terhadap_nab !== null)
                    <tr>
                        <td class="px-4 py-2 text-muted">Transaction Profit terhadap NAB</td>
                        <td class="px-4 py-2 text-right font-medium">{{ number_format($analisa->transaction_profit_terhadap_nab, 4) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($analisa->efek->isNotEmpty())
    <div class="bg-white rounded-xl border border-line p-6">
        <h3 class="font-semibold text-primary mb-4">Attribution Analysis — Daftar Efek</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#f8fafc] border-b border-line">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-semibold text-primary">Kode</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-primary">Nama Efek</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-primary">Sektor</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-primary">Bobot (%)</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-primary">Nilai Pasar</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-primary">Harga Perolehan</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-primary">% thd NAB</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-primary">Kontribusi Kinerja</th>
                        <th class="text-center px-4 py-2.5 font-semibold text-primary">Top 10</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($analisa->efek->sortByDesc('bobot') as $efek)
                    <tr class="hover:bg-[#f8fafc]">
                        <td class="px-4 py-2.5 font-mono text-xs">{{ $efek->kode_efek }}</td>
                        <td class="px-4 py-2.5">{{ $efek->nama_efek }}</td>
                        <td class="px-4 py-2.5 text-muted">{{ $efek->sektor ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-right">{{ number_format($efek->bobot, 2) }}%</td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs">{{ $efek->nilai_pasar ? 'Rp '.number_format($efek->nilai_pasar, 0, ',', '.') : '-' }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs">{{ $efek->harga_perolehan ? 'Rp '.number_format($efek->harga_perolehan, 0, ',', '.') : '-' }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs">{{ $efek->persen_nab !== null ? number_format($efek->persen_nab, 2).'%' : '-' }}</td>
                        <td class="px-4 py-2.5 text-right">{{ $efek->kontribusi_kinerja !== null ? ($efek->kontribusi_kinerja > 0 ? '+' : '').$efek->kontribusi_kinerja.'%' : '-' }}</td>
                        <td class="px-4 py-2.5 text-center">@if($efek->top_10) ✓ @endif</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if($analisa->obligasi->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6 overflow-x-auto">
            <h3 class="font-semibold text-primary mb-4">Durasi & Rating Risk — Obligasi</h3>
            <table class="w-full text-sm">
                <thead class="bg-[#f8fafc] border-b border-line">
                    <tr>
                        <th class="text-left px-3 py-2 font-semibold text-primary text-xs">Kode</th>
                        <th class="text-left px-3 py-2 font-semibold text-primary text-xs">Nama Obligasi</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">Bobot %</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">Durasi</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">YTM %</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">Kupon %</th>
                        <th class="text-center px-3 py-2 font-semibold text-primary text-xs">Jatuh Tempo</th>
                        <th class="text-left px-3 py-2 font-semibold text-primary text-xs">Penerbit</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">% thd NAB</th>
                        <th class="text-center px-3 py-2 font-semibold text-primary text-xs">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($analisa->obligasi as $ob)
                    <tr class="hover:bg-[#f8fafc]">
                        <td class="px-3 py-2 font-mono text-xs">{{ $ob->kode_obligasi }}</td>
                        <td class="px-3 py-2 text-xs">{{ $ob->nama_obligasi }}</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ number_format($ob->bobot, 2) }}%</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ $ob->durasi ? $ob->durasi.' thn' : '-' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ $ob->ytm !== null ? number_format($ob->ytm, 2).'%' : '-' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ $ob->kupon !== null ? number_format($ob->kupon, 2).'%' : '-' }}</td>
                        <td class="px-3 py-2 text-center font-mono text-xs">{{ $ob->tanggal_jatuh_tempo ? $ob->tanggal_jatuh_tempo->format('d/m/Y') : ($ob->jatuh_tempo ?? '-') }}</td>
                        <td class="px-3 py-2 text-xs">{{ $ob->penerbit ?? '-' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ $ob->persen_nab !== null ? number_format($ob->persen_nab, 2).'%' : '-' }}</td>
                        <td class="px-3 py-2 text-center"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ $ob->rating ?? '-' }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        @if($analisa->bank->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6 overflow-x-auto">
            <h3 class="font-semibold text-primary mb-4">Bank Risk — CAR & NPL</h3>
            <table class="w-full text-sm">
                <thead class="bg-[#f8fafc] border-b border-line">
                    <tr>
                        <th class="text-left px-3 py-2 font-semibold text-primary text-xs">Nama Bank</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">Bobot %</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">Tingkat Bunga %</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">Jangka Waktu (hari)</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">% thd NAB</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">CAR %</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">NPL %</th>
                        <th class="text-center px-3 py-2 font-semibold text-primary text-xs">Klasifikasi Risiko</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($analisa->bank as $bank)
                    <tr class="hover:bg-[#f8fafc]">
                        <td class="px-3 py-2 text-xs font-medium">{{ $bank->nama_bank }}</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ number_format($bank->bobot, 2) }}%</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ $bank->tingkat_bunga !== null ? number_format($bank->tingkat_bunga, 2).'%' : '-' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ $bank->jangka_waktu ?? '-' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ $bank->persen_nab !== null ? number_format($bank->persen_nab, 2).'%' : '-' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ $bank->car ? number_format($bank->car, 2).'%' : '-' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ $bank->npl ? number_format($bank->npl, 2).'%' : '-' }}</td>
                        <td class="px-3 py-2 text-center">
                            @php
                                $rc = match($bank->klasifikasi_risiko) {
                                    'Rendah' => 'bg-green-100 text-green-700',
                                    'Sedang' => 'bg-yellow-100 text-yellow-700',
                                    'Tinggi' => 'bg-red-100 text-red-700',
                                    default  => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $rc }}">{{ $bank->klasifikasi_risiko ?? '-' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        @if($analisa->sukuk->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6 overflow-x-auto">
            <h3 class="font-semibold text-primary mb-4">Sukuk — Yield & Rating</h3>
            <table class="w-full text-sm">
                <thead class="bg-[#f8fafc] border-b border-line">
                    <tr>
                        <th class="text-left px-3 py-2 font-semibold text-primary text-xs">Kode</th>
                        <th class="text-left px-3 py-2 font-semibold text-primary text-xs">Nama Sukuk</th>
                        <th class="text-left px-3 py-2 font-semibold text-primary text-xs">Jenis</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">Bobot %</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">Yield %</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary text-xs">% thd NAB</th>
                        <th class="text-center px-3 py-2 font-semibold text-primary text-xs">Jatuh Tempo</th>
                        <th class="text-center px-3 py-2 font-semibold text-primary text-xs">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($analisa->sukuk as $s)
                    <tr class="hover:bg-[#f8fafc]">
                        <td class="px-3 py-2 font-mono text-xs">{{ $s->kode_sukuk }}</td>
                        <td class="px-3 py-2 text-xs">{{ $s->nama_sukuk }}</td>
                        <td class="px-3 py-2 text-muted text-xs">{{ $s->jenis_sukuk ?? '-' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ number_format($s->bobot, 2) }}%</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ $s->yield !== null ? number_format($s->yield, 4).'%' : '-' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-xs">{{ $s->persen_nab !== null ? number_format($s->persen_nab, 2).'%' : '-' }}</td>
                        <td class="px-3 py-2 text-center font-mono text-xs">{{ $s->jatuh_tempo ?? '-' }}</td>
                        <td class="px-3 py-2 text-center"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ $s->rating ?? '-' }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if($analisa->sektor->isNotEmpty())
new Chart(document.getElementById('chartSektor'), {
    type: 'doughnut',
    data: {
        labels: @json($analisa->sektor->pluck('nama_sektor')),
        datasets: [{ data: @json($analisa->sektor->pluck('bobot')), backgroundColor: ['#1e3a5f','#2563eb','#3b82f6','#60a5fa','#93c5fd'] }]
    },
    options: { plugins: { legend: { position: 'right' } }, cutout: '60%' }
});
@endif
@if($analisa->kinerja->isNotEmpty())
new Chart(document.getElementById('chartKinerja'), {
    type: 'bar',
    data: {
        labels: @json($analisa->kinerja->sortBy('periode')->map(fn($k) => $k->periode->format('M Y'))->values()),
        datasets: [{ label: 'Return (%)', data: @json($analisa->kinerja->sortBy('periode')->pluck('return_pct')), backgroundColor: (ctx) => ctx.raw >= 0 ? '#22c55e' : '#ef4444' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => v + '%' } } } }
});
@endif
</script>
@endpush
@endsection
