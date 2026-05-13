@extends('layouts.user')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-muted mb-1">
                <a href="{{ route('user.analisa.index') }}" class="hover:text-primary">Analisa RD</a>
                <span>/</span>
                <span>{{ $analisa->nama_reksa_dana }}</span>
            </div>
            <h1 class="text-xl font-bold text-primary">{{ $analisa->nama_reksa_dana }}</h1>
            <p class="text-sm text-muted mt-0.5">{{ $analisa->jenis_reksa_dana }} &bull; Disubmit {{ $analisa->created_at->format('d M Y') }}</p>
        </div>
        @php
            $badge = match($analisa->status) {
                'draft'     => 'bg-gray-100 text-gray-600',
                'submitted' => 'bg-yellow-100 text-yellow-700',
                'reviewed'  => 'bg-green-100 text-green-700',
            };
            $label = match($analisa->status) {
                'draft'     => 'Draft',
                'submitted' => 'Menunggu Review',
                'reviewed'  => 'Sudah Direview',
            };
        @endphp
        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
    </div>

    {{-- Tombol PDF --}}
    <div class="flex justify-end">
        <a href="{{ route('user.analisa.pdf', $analisa) }}" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export PDF
        </a>
    </div>

    @if($analisa->catatan_admin)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-700">
        <p class="font-medium mb-1">Catatan Admin:</p>
        <p>{{ $analisa->catatan_admin }}</p>
    </div>
    @endif

    {{-- Narasi AI --}}
    @if($analisa->ai_narasi)
    <div class="bg-white rounded-xl border border-line p-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-lg">🤖</span>
            <h3 class="font-semibold text-primary">Analisa AI</h3>
            <span class="ml-auto text-xs text-muted bg-[#f1f5f9] px-2 py-1 rounded-full">Powered by Groq</span>
        </div>
        <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $analisa->ai_narasi }}</div>
    </div>
    @else
    <div class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 flex items-center gap-3 text-sm text-muted">
        <svg class="w-5 h-5 animate-spin shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        Narasi AI sedang diproses... Refresh halaman beberapa saat lagi.
    </div>
    @endif

    {{-- Metric Cards: RAR, Sharpe, Liquidity, Durasi --}}
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
        {{-- Chart: Komposisi Sektor --}}
        @if($analisa->sektor->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Komposisi per Sektor</h3>
            <canvas id="chartSektor" height="220"></canvas>
        </div>
        @endif

        {{-- Chart: Kinerja Bulanan --}}
        @if($analisa->kinerja->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Return Bulanan (%)</h3>
            <canvas id="chartKinerja" height="220"></canvas>
        </div>
        @endif
    </div>

    {{-- Attribution: 10 Efek Terbesar --}}
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
                        <td class="px-4 py-2.5 text-right {{ $efek->kontribusi_kinerja > 0 ? 'text-green-600' : ($efek->kontribusi_kinerja < 0 ? 'text-red-600' : 'text-muted') }}">
                            {{ $efek->kontribusi_kinerja !== null ? ($efek->kontribusi_kinerja > 0 ? '+' : '').$efek->kontribusi_kinerja.'%' : '-' }}
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            @if($efek->top_10)
                                <span class="inline-flex w-5 h-5 items-center justify-center bg-primary text-white rounded-full text-xs">✓</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Durasi & Rating Risk: Obligasi --}}
        @if($analisa->obligasi->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Durasi & Rating Risk — Obligasi</h3>
            <table class="w-full text-sm">
                <thead class="bg-[#f8fafc] border-b border-line">
                    <tr>
                        <th class="text-left px-3 py-2 font-semibold text-primary">Obligasi</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary">Bobot</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary">Durasi</th>
                        <th class="text-center px-3 py-2 font-semibold text-primary">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($analisa->obligasi as $ob)
                    <tr>
                        <td class="px-3 py-2">
                            <div class="font-medium">{{ $ob->nama_obligasi }}</div>
                            <div class="text-xs text-muted">{{ $ob->kode_obligasi }}</div>
                        </td>
                        <td class="px-3 py-2 text-right">{{ number_format($ob->bobot, 2) }}%</td>
                        <td class="px-3 py-2 text-right">{{ $ob->durasi ? $ob->durasi.' thn' : '-' }}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ $ob->rating ?? '-' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Bank Risk --}}
        @if($analisa->bank->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Bank Risk — CAR & NPL</h3>
            <table class="w-full text-sm">
                <thead class="bg-[#f8fafc] border-b border-line">
                    <tr>
                        <th class="text-left px-3 py-2 font-semibold text-primary">Bank</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary">CAR</th>
                        <th class="text-right px-3 py-2 font-semibold text-primary">NPL</th>
                        <th class="text-center px-3 py-2 font-semibold text-primary">Risiko</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($analisa->bank as $bank)
                    <tr>
                        <td class="px-3 py-2 font-medium">{{ $bank->nama_bank }}</td>
                        <td class="px-3 py-2 text-right">{{ $bank->car ? $bank->car.'%' : '-' }}</td>
                        <td class="px-3 py-2 text-right">{{ $bank->npl ? $bank->npl.'%' : '-' }}</td>
                        <td class="px-3 py-2 text-center">
                            @php
                                $riskColor = match($bank->klasifikasi_risiko) {
                                    'Rendah'  => 'bg-green-100 text-green-700',
                                    'Sedang'  => 'bg-yellow-100 text-yellow-700',
                                    'Tinggi'  => 'bg-red-100 text-red-700',
                                    default   => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $riskColor }}">
                                {{ $bank->klasifikasi_risiko ?? '-' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if($analisa->sektor->isNotEmpty())
new Chart(document.getElementById('chartSektor'), {
    type: 'doughnut',
    data: {
        labels: {!! $analisa->sektor->pluck('nama_sektor') !!},
        datasets: [{
            data: {!! $analisa->sektor->pluck('bobot') !!},
            backgroundColor: ['#1e3a5f','#2563eb','#3b82f6','#60a5fa','#93c5fd','#bfdbfe','#dbeafe','#eff6ff','#f0f9ff','#e0f2fe'],
        }]
    },
    options: { plugins: { legend: { position: 'right' } }, cutout: '60%' }
});
@endif

@if($analisa->kinerja->isNotEmpty())
new Chart(document.getElementById('chartKinerja'), {
    type: 'bar',
    data: {
        labels: {!! $analisa->kinerja->sortBy('periode')->map(fn($k) => $k->periode->format('M Y')) !!},
        datasets: [{
            label: 'Return (%)',
            data: {!! $analisa->kinerja->sortBy('periode')->pluck('return_pct') !!},
            backgroundColor: (ctx) => ctx.raw >= 0 ? '#22c55e' : '#ef4444',
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { ticks: { callback: v => v + '%' } } }
    }
});
@endif
</script>
@endpush
@endsection
