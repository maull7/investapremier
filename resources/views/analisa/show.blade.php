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
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Durasi & Rating Risk — Obligasi</h3>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-line">
                    @foreach($analisa->obligasi as $ob)
                    <tr>
                        <td class="px-3 py-2">{{ $ob->nama_obligasi }} <span class="text-xs text-muted">{{ $ob->kode_obligasi }}</span></td>
                        <td class="px-3 py-2 text-right">{{ number_format($ob->bobot, 2) }}%</td>
                        <td class="px-3 py-2 text-right">{{ $ob->durasi ? $ob->durasi.' thn' : '-' }}</td>
                        <td class="px-3 py-2 text-center">{{ $ob->rating ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        @if($analisa->bank->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Bank Risk — CAR & NPL</h3>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-line">
                    @foreach($analisa->bank as $bank)
                    <tr>
                        <td class="px-3 py-2">{{ $bank->nama_bank }}</td>
                        <td class="px-3 py-2 text-right">{{ $bank->car ? $bank->car.'%' : '-' }}</td>
                        <td class="px-3 py-2 text-right">{{ $bank->npl ? $bank->npl.'%' : '-' }}</td>
                        <td class="px-3 py-2 text-center">{{ $bank->klasifikasi_risiko ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        @if($analisa->sukuk->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Sukuk — Yield & Rating</h3>
            <table class="w-full text-sm">
                <thead class="bg-[#f8fafc] border-b border-line">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-semibold text-primary">Nama Sukuk</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-primary">Jenis</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-primary">Bobot (%)</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-primary">Yield (%)</th>
                        <th class="text-center px-4 py-2.5 font-semibold text-primary">Jatuh Tempo</th>
                        <th class="text-center px-4 py-2.5 font-semibold text-primary">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($analisa->sukuk as $s)
                    <tr class="hover:bg-[#f8fafc]">
                        <td class="px-4 py-2.5">{{ $s->nama_sukuk }} <span class="text-xs text-muted">{{ $s->kode_sukuk }}</span></td>
                        <td class="px-4 py-2.5 text-muted">{{ $s->jenis_sukuk ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-right">{{ number_format($s->bobot, 2) }}%</td>
                        <td class="px-4 py-2.5 text-right">{{ $s->yield !== null ? number_format($s->yield, 4).'%' : '-' }}</td>
                        <td class="px-4 py-2.5 text-center">{{ $s->jatuh_tempo ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-center">{{ $s->rating ?? '-' }}</td>
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
        labels: {!! $analisa->sektor->pluck('nama_sektor') !!},
        datasets: [{ data: {!! $analisa->sektor->pluck('bobot') !!}, backgroundColor: ['#1e3a5f','#2563eb','#3b82f6','#60a5fa','#93c5fd'] }]
    },
    options: { plugins: { legend: { position: 'right' } }, cutout: '60%' }
});
@endif
@if($analisa->kinerja->isNotEmpty())
new Chart(document.getElementById('chartKinerja'), {
    type: 'bar',
    data: {
        labels: {!! $analisa->kinerja->sortBy('periode')->map(fn($k) => $k->periode->format('M Y')) !!},
        datasets: [{ label: 'Return (%)', data: {!! $analisa->kinerja->sortBy('periode')->pluck('return_pct') !!}, backgroundColor: (ctx) => ctx.raw >= 0 ? '#22c55e' : '#ef4444' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => v + '%' } } } }
});
@endif
</script>
@endpush
@endsection
