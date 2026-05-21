@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'data' }">
    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-muted mb-1">
                <a href="{{ route('admin.unit-link-analisa.index') }}" class="hover:text-primary">Monitor Analisa Unit Link</a>
                <span>/</span>
                <span>{{ $analisa->nama_reksa_dana }}</span>
            </div>
            <h1 class="text-xl font-bold text-primary">{{ $analisa->nama_reksa_dana }}</h1>
            <p class="text-sm text-muted mt-0.5">
                {{ $analisa->jenis_reksa_dana }} &bull;
                Disubmit oleh <strong>{{ $analisa->user->name }}</strong> pada {{ $analisa->created_at->format('d M Y') }}
            </p>
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
        <div class="flex items-center gap-2">
            @if($analisa->pdf_path)
                <a href="{{ route('admin.unit-link-analisa.download-ffs', $analisa) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    FFS PDF
                </a>
            @endif
            <a href="{{ route('admin.unit-link-analisa.pdf', $analisa) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
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
            <div class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted">Narasi AI belum tersedia atau sedang diproses.</div>
        @endif
    </div>

    <div x-show="activeTab==='ai-plus'">
        @php $aiPlusOut = $analisa->ai_output_plus ?? []; @endphp
        @if(!empty($aiPlusOut['error']))
            @include('analisa.partials.ai-panel', ['title' => 'Analisa AI Plus', 'variant' => 'plus', 'ai' => $aiPlusOut, 'narasi' => null])
        @elseif($analisa->ai_narasi_plus)
            @include('analisa.partials.ai-panel', ['title' => 'Analisa AI Plus', 'variant' => 'plus', 'ai' => $aiPlusOut, 'narasi' => $analisa->ai_narasi_plus])
        @else
            <div class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted">Analisa AI Plus belum tersedia atau sedang diproses.</div>
        @endif
    </div>

    <div x-show="activeTab==='data'" class="space-y-6">

    {{-- Metric Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $metrics = [
                ['label' => 'Sharpe Ratio', 'value' => $analisa->sharpe_ratio ?? '-'],
                ['label' => 'RAR', 'value' => $analisa->rar ?? '-'],
                ['label' => 'Liquidity Ratio', 'value' => $analisa->liquidity_ratio ? number_format($analisa->liquidity_ratio * 100, 2).'%' : '-'],
                ['label' => 'Durasi Rata-rata', 'value' => $analisa->durasi_rata_rata ? $analisa->durasi_rata_rata.' thn' : '-'],
            ];
        @endphp
        @foreach($metrics as $m)
        <div class="bg-white rounded-xl border border-line p-5">
            <p class="text-xs text-muted">{{ $m['label'] }}</p>
            <p class="text-2xl font-bold text-primary mt-1">{{ $m['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Charts --}}
    @if($analisa->sektor->isNotEmpty() || $analisa->kinerja->isNotEmpty())
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
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Data Detail --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Sektor --}}
            @if($analisa->sektor->isNotEmpty())
            <div class="bg-white rounded-xl border border-line p-6">
                <h3 class="font-semibold text-primary mb-3">Komposisi Sektor</h3>
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-3 py-2 font-semibold text-primary">Sektor</th>
                            <th class="text-right px-3 py-2 font-semibold text-primary">Bobot (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($analisa->sektor->sortByDesc('bobot') as $s)
                        <tr>
                            <td class="px-3 py-2">{{ $s->nama_sektor }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($s->bobot, 2) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Efek --}}
            @if($analisa->efek->isNotEmpty())
            <div class="bg-white rounded-xl border border-line p-6">
                <h3 class="font-semibold text-primary mb-3">Daftar Efek</h3>
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-3 py-2 font-semibold text-primary">Kode</th>
                            <th class="text-left px-3 py-2 font-semibold text-primary">Nama</th>
                            <th class="text-right px-3 py-2 font-semibold text-primary">Bobot</th>
                            <th class="text-right px-3 py-2 font-semibold text-primary">Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($analisa->efek->sortByDesc('bobot') as $e)
                        <tr>
                            <td class="px-3 py-2 font-mono text-xs">{{ $e->kode_efek }}</td>
                            <td class="px-3 py-2">{{ $e->nama_efek }} @if($e->top_10)<span class="ml-1 text-xs text-primary font-medium">★</span>@endif</td>
                            <td class="px-3 py-2 text-right">{{ number_format($e->bobot, 2) }}%</td>
                            <td class="px-3 py-2 text-right {{ $e->kontribusi_kinerja > 0 ? 'text-green-600' : ($e->kontribusi_kinerja < 0 ? 'text-red-600' : '') }}">
                                {{ $e->kontribusi_kinerja !== null ? ($e->kontribusi_kinerja > 0 ? '+' : '').$e->kontribusi_kinerja.'%' : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Obligasi --}}
            @if($analisa->obligasi->isNotEmpty())
            <div class="bg-white rounded-xl border border-line p-6">
                <h3 class="font-semibold text-primary mb-3">Obligasi (Durasi & Rating Risk)</h3>
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
                                <div>{{ $ob->nama_obligasi }}</div>
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

            {{-- Bank --}}
            @if($analisa->bank->isNotEmpty())
            <div class="bg-white rounded-xl border border-line p-6">
                <h3 class="font-semibold text-primary mb-3">Bank Risk</h3>
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-3 py-2 font-semibold text-primary">Bank</th>
                            <th class="text-right px-3 py-2 font-semibold text-primary">Bobot</th>
                            <th class="text-right px-3 py-2 font-semibold text-primary">CAR</th>
                            <th class="text-right px-3 py-2 font-semibold text-primary">NPL</th>
                            <th class="text-center px-3 py-2 font-semibold text-primary">Risiko</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($analisa->bank as $bank)
                        <tr>
                            <td class="px-3 py-2 font-medium">{{ $bank->nama_bank }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($bank->bobot, 2) }}%</td>
                            <td class="px-3 py-2 text-right">{{ $bank->car ? $bank->car.'%' : '-' }}</td>
                            <td class="px-3 py-2 text-right">{{ $bank->npl ? $bank->npl.'%' : '-' }}</td>
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
        </div>

        {{-- Sidebar: Form Review --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-line p-6">
                <h3 class="font-semibold text-primary mb-4">Review Analisa</h3>

                @if($analisa->status === 'reviewed')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-700 mb-4">
                        ✓ Sudah direview
                    </div>
                    @if($analisa->catatan_admin)
                    <div class="text-sm">
                        <p class="font-medium text-primary mb-1">Catatan:</p>
                        <p class="text-muted">{{ $analisa->catatan_admin }}</p>
                    </div>
                    @endif
                @else
                    <form method="POST" action="{{ route('admin.unit-link-analisa.review', $analisa) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-primary mb-1">Catatan (opsional)</label>
                            <textarea name="catatan_admin" rows="4"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20"
                                placeholder="Tambahkan catatan untuk user...">{{ old('catatan_admin') }}</textarea>
                        </div>
                        <button type="submit"
                            class="w-full px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition">
                            Tandai Sudah Direview
                        </button>
                    </form>
                @endif
            </div>

            {{-- Info AUM --}}
            <div class="bg-white rounded-xl border border-line p-6 text-sm space-y-3">
                <h3 class="font-semibold text-primary">Info AUM</h3>
                <div class="flex justify-between">
                    <span class="text-muted">Total AUM</span>
                    <span class="font-medium">{{ $analisa->total_aum ? 'Rp '.number_format($analisa->total_aum, 0, ',', '.') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">MarCap 10 Efek</span>
                    <span class="font-medium">{{ $analisa->total_marcap_10_efek ? 'Rp '.number_format($analisa->total_marcap_10_efek, 0, ',', '.') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    </div>{{-- end activeTab data --}}
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
