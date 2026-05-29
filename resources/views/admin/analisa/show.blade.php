@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'data' }">
    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-muted mb-1">
                <a href="{{ route('admin.analisa.index') }}" class="hover:text-primary">Monitor Analisa</a>
                <span>/</span>
                <span>{{ $analisa->nama_reksa_dana }}</span>
            </div>
            <h1 class="text-xl font-bold text-primary">{{ $analisa->nama_reksa_dana }}</h1>
            <p class="text-sm text-muted mt-0.5">
                {{ $analisa->jenis_reksa_dana }} &bull;
                Disubmit oleh <strong>{{ $analisa->user->name }}</strong> pada {{ $analisa->created_at->format('d M Y') }}
                @if ($analisa->ffs_bulan && $analisa->ffs_tahun)
                    &bull;
                    Kalender FFS:
                    <strong>{{ ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$analisa->ffs_bulan - 1] }}
                    {{ $analisa->ffs_tahun }}</strong>
                @endif
            </p>
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
        <div class="flex items-center gap-2">
            @if($analisa->pdf_path)
                <a href="{{ route('admin.analisa.download-ffs', $analisa) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    FFS PDF
                </a>
            @endif
            <a href="{{ route('admin.analisa.pdf', $analisa) }}" target="_blank"
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

    {{-- Informasi Keuangan --}}
    <div class="bg-white rounded-xl border border-line p-6">
        <h3 class="font-semibold text-primary mb-4">Informasi Keuangan</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 text-sm">
            <div>
                <p class="text-muted text-xs">Total AUM</p>
                <p class="font-medium mt-0.5">{{ $analisa->total_aum ? 'Rp '.number_format($analisa->total_aum, 0, ',', '.') : '-' }}</p>
            </div>
            <div>
                <p class="text-muted text-xs">Total MarCap 10 Saham Terbesar</p>
                <p class="font-medium mt-0.5">{{ $analisa->total_marcap_10_efek ? 'Rp '.number_format($analisa->total_marcap_10_efek, 0, ',', '.') : '-' }}</p>
            </div>
            <div>
                <p class="text-muted text-xs">NAB/UP</p>
                <p class="font-medium mt-0.5">{{ $analisa->nab_per_unit ? number_format($analisa->nab_per_unit, 6, ',', '.') : '-' }}</p>
            </div>
            <div>
                <p class="text-muted text-xs">Jumlah Unit Penyertaan</p>
                <p class="font-medium mt-0.5">{{ $analisa->unit_penyertaan ? number_format($analisa->unit_penyertaan, 4, ',', '.') : '-' }}</p>
            </div>
            <div>
                <p class="text-muted text-xs">Kalender FFS</p>
                <p class="font-medium mt-0.5">
                    @if ($analisa->ffs_bulan && $analisa->ffs_tahun)
                        {{ ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$analisa->ffs_bulan - 1] }}
                        {{ $analisa->ffs_tahun }}
                    @else
                        -
                    @endif
                </p>
            </div>
            <div>
                <p class="text-muted text-xs">Tanggal Data</p>
                <p class="font-medium mt-0.5">{{ $analisa->tanggal_data ? $analisa->tanggal_data->format('d M Y') : '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    @if($analisa->sektor->isNotEmpty() || $analisa->kinerja->isNotEmpty() || $analisa->sukuk->isNotEmpty())
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if($analisa->sektor->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Komposisi per Sektor</h3>
            <canvas id="chartSektorAdmin" height="220"></canvas>
        </div>
        @endif
        @if($analisa->kinerja->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Return Bulanan (%)</h3>
            <canvas id="chartKinerjaAdmin" height="220"></canvas>
        </div>
        @endif
        @if($analisa->sukuk->isNotEmpty())
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Komposisi Sukuk</h3>
            <canvas id="chartSukukKomposisi" height="220"></canvas>
        </div>
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Negara vs Korporasi</h3>
            <canvas id="chartSukukJenis" height="220"></canvas>
        </div>
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Distribusi Rating Sukuk</h3>
            <canvas id="chartSukukRating" height="220"></canvas>
        </div>
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Distribusi Jatuh Tempo</h3>
            <canvas id="chartSukukJatuhTempo" height="220"></canvas>
        </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Data Detail --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Alokasi Aset --}}
            @if($analisa->alokasiAset->isNotEmpty())
            <div class="bg-white rounded-xl border border-line p-6">
                <h3 class="font-semibold text-primary mb-3">Alokasi Aset</h3>
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-3 py-2 font-semibold text-primary">Jenis Aset</th>
                            <th class="text-right px-3 py-2 font-semibold text-primary">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($analisa->alokasiAset as $aa)
                        <tr>
                            <td class="px-3 py-2">{{ $aa->nama_aset }}</td>
                            <td class="px-3 py-2 text-right font-mono">{{ number_format($aa->persentase, 2) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

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
            <div class="bg-white rounded-xl border border-line p-6 overflow-x-auto">
                <h3 class="font-semibold text-primary mb-3">Daftar Efek</h3>
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-2 py-2 font-semibold text-primary text-xs">Kode</th>
                            <th class="text-left px-2 py-2 font-semibold text-primary text-xs">Nama Efek</th>
                            <th class="text-left px-2 py-2 font-semibold text-primary text-xs">Sektor</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Bobot %</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Kontribusi % IHSG</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Nilai Pasar</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Return 1M</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Return 3M</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Return 6M</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Return 1 Thn</th>
                            <th class="text-center px-2 py-2 font-semibold text-primary text-xs">Top 10</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($analisa->efek->sortByDesc('bobot') as $e)
                        <tr>
                            <td class="px-2 py-2 font-mono text-xs">{{ $e->kode_efek }}</td>
                            <td class="px-2 py-2 text-xs">{{ $e->nama_efek }}</td>
                            <td class="px-2 py-2 text-muted text-xs">{{ $e->sektor ?? '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ number_format($e->bobot, 2) }}%</td>
                            <td class="px-2 py-2 text-right font-mono text-xs {{ ($e->kontribusi_kinerja ?? 0) > 0 ? 'text-green-600' : (($e->kontribusi_kinerja ?? 0) < 0 ? 'text-red-600' : '') }}">
                                {{ $e->kontribusi_kinerja !== null ? ($e->kontribusi_kinerja > 0 ? '+' : '').number_format($e->kontribusi_kinerja, 2).'%' : '-' }}
                            </td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $e->nilai_pasar ? 'Rp '.number_format($e->nilai_pasar, 0, ',', '.') : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $e->return_1m !== null ? number_format($e->return_1m, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $e->return_3m !== null ? number_format($e->return_3m, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $e->return_6m !== null ? number_format($e->return_6m, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $e->return_1y !== null ? number_format($e->return_1y, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-center text-xs">
                                @if($e->top_10)
                                    <span class="text-green-600 font-bold">&#10003; Ya</span>
                                @else
                                    <span class="text-muted">&#10007; Tidak</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Obligasi --}}
            @if($analisa->obligasi->isNotEmpty())
            <div class="bg-white rounded-xl border border-line p-6 overflow-x-auto">
                <h3 class="font-semibold text-primary mb-3">Obligasi</h3>
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-2 py-2 font-semibold text-primary text-xs">Kode</th>
                            <th class="text-left px-2 py-2 font-semibold text-primary text-xs">Nama Obligasi</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Bobot %</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Nilai Pasar</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Return 1M</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Return 3M</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Return 6M</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Return 1 Thn</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Durasi</th>
                            <th class="text-center px-2 py-2 font-semibold text-primary text-xs">Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($analisa->obligasi as $ob)
                        <tr>
                            <td class="px-2 py-2 font-mono text-xs">{{ $ob->kode_obligasi }}</td>
                            <td class="px-2 py-2 text-xs">{{ $ob->nama_obligasi }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ number_format($ob->bobot, 2) }}%</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $ob->nilai_pasar ? 'Rp '.number_format($ob->nilai_pasar, 0, ',', '.') : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $ob->return_1m !== null ? number_format($ob->return_1m, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $ob->return_3m !== null ? number_format($ob->return_3m, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $ob->return_6m !== null ? number_format($ob->return_6m, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $ob->return_1y !== null ? number_format($ob->return_1y, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $ob->durasi ? $ob->durasi.' thn' : '-' }}</td>
                            <td class="px-2 py-2 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ $ob->rating ?? '-' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Sukuk --}}
            @if($analisa->sukuk->isNotEmpty())
            <div class="bg-white rounded-xl border border-line p-6 overflow-x-auto">
                <h3 class="font-semibold text-primary mb-3">Sukuk</h3>
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-2 py-2 font-semibold text-primary text-xs">Kode</th>
                            <th class="text-left px-2 py-2 font-semibold text-primary text-xs">Nama Sukuk</th>
                            <th class="text-left px-2 py-2 font-semibold text-primary text-xs">Jenis</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Bobot %</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Yield %</th>
                            <th class="text-center px-2 py-2 font-semibold text-primary text-xs">Jatuh Tempo</th>
                            <th class="text-center px-2 py-2 font-semibold text-primary text-xs">Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($analisa->sukuk as $s)
                        <tr>
                            <td class="px-2 py-2 font-mono text-xs">{{ $s->kode_sukuk }}</td>
                            <td class="px-2 py-2 text-xs">{{ $s->nama_sukuk }}</td>
                            <td class="px-2 py-2 text-muted text-xs">{{ $s->jenis_sukuk ?? '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ number_format($s->bobot, 2) }}%</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $s->yield !== null ? number_format($s->yield, 4).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-center font-mono text-xs">{{ $s->jatuh_tempo ?? '-' }}</td>
                            <td class="px-2 py-2 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ $s->rating ?? '-' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Bank --}}
            @if($analisa->bank->isNotEmpty())
            <div class="bg-white rounded-xl border border-line p-6 overflow-x-auto">
                <h3 class="font-semibold text-primary mb-3">Bank</h3>
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-2 py-2 font-semibold text-primary text-xs">Nama Bank</th>
                            <th class="text-left px-2 py-2 font-semibold text-primary text-xs">Jenis Bank</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Bobot %</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Nilai Pasar</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">CAR %</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">NPL %</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Return 1M</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Return 3M</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Return 6M</th>
                            <th class="text-right px-2 py-2 font-semibold text-primary text-xs">Return 1 Thn</th>
                            <th class="text-center px-2 py-2 font-semibold text-primary text-xs">Klasifikasi Risiko</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($analisa->bank as $bank)
                        <tr>
                            <td class="px-2 py-2 text-xs font-medium">{{ $bank->nama_bank }}</td>
                            <td class="px-2 py-2 text-muted text-xs">{{ $bank->jenis_bank ?? '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ number_format($bank->bobot, 2) }}%</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $bank->nilai_pasar ? 'Rp '.number_format($bank->nilai_pasar, 0, ',', '.') : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $bank->car ? number_format($bank->car, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $bank->npl ? number_format($bank->npl, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $bank->return_1m !== null ? number_format($bank->return_1m, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $bank->return_3m !== null ? number_format($bank->return_3m, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $bank->return_6m !== null ? number_format($bank->return_6m, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-right font-mono text-xs">{{ $bank->return_1y !== null ? number_format($bank->return_1y, 2).'%' : '-' }}</td>
                            <td class="px-2 py-2 text-center">
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
                    <form method="POST" action="{{ route('admin.analisa.review', $analisa) }}">
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
                <h3 class="font-semibold text-primary">Info Keuangan</h3>
                <div class="flex justify-between">
                    <span class="text-muted">Total AUM</span>
                    <span class="font-medium">{{ $analisa->total_aum ? 'Rp '.number_format($analisa->total_aum, 0, ',', '.') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Total MarCap 10 Saham Terbesar</span>
                    <span class="font-medium">{{ $analisa->total_marcap_10_efek ? 'Rp '.number_format($analisa->total_marcap_10_efek, 0, ',', '.') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">NAB/UP</span>
                    <span class="font-medium">{{ $analisa->nab_per_unit ? number_format($analisa->nab_per_unit, 6, ',', '.') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Unit Penyertaan</span>
                    <span class="font-medium">{{ $analisa->unit_penyertaan ? number_format($analisa->unit_penyertaan, 4, ',', '.') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Kalender FFS</span>
                    <span class="font-medium">
                        @if ($analisa->ffs_bulan && $analisa->ffs_tahun)
                            {{ ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$analisa->ffs_bulan - 1] }}
                            {{ $analisa->ffs_tahun }}
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Tanggal Data</span>
                    <span class="font-medium">{{ $analisa->tanggal_data ? $analisa->tanggal_data->format('d M Y') : '-' }}</span>
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
new Chart(document.getElementById('chartSektorAdmin'), {
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
new Chart(document.getElementById('chartKinerjaAdmin'), {
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

@if($analisa->sukuk->isNotEmpty())
@php
    $sukukLabels = $analisa->sukuk->pluck('nama_sukuk');
    $sukukBobot = $analisa->sukuk->pluck('bobot');
    $sukukColors = ['#1e3a5f','#2563eb','#3b82f6','#60a5fa','#93c5fd','#bfdbfe','#dbeafe','#eff6ff'];
    $jenisCounts = $analisa->sukuk->groupBy('jenis_sukuk')->map->count();
    $ratingCounts = $analisa->sukuk->groupBy('rating')->map->count();
    $jatuhTempoLabels = $analisa->sukuk->pluck('jatuh_tempo')->filter()->values();
@endphp
new Chart(document.getElementById('chartSukukKomposisi'), {
    type: 'doughnut',
    data: {
        labels: {!! $sukukLabels !!},
        datasets: [{ data: {!! $sukukBobot !!}, backgroundColor: {!! $sukukColors !!} }]
    },
    options: { plugins: { legend: { position: 'right' } }, cutout: '60%' }
});
new Chart(document.getElementById('chartSukukJenis'), {
    type: 'doughnut',
    data: {
        labels: {!! $jenisCounts->keys() !!},
        datasets: [{ data: {!! $jenisCounts->values() !!}, backgroundColor: ['#2563eb','#60a5fa'] }]
    },
    options: { plugins: { legend: { position: 'right' } }, cutout: '60%' }
});
new Chart(document.getElementById('chartSukukRating'), {
    type: 'bar',
    data: {
        labels: {!! $ratingCounts->keys() !!},
        datasets: [{ label: 'Jumlah', data: {!! $ratingCounts->values() !!}, backgroundColor: '#3b82f6' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
new Chart(document.getElementById('chartSukukJatuhTempo'), {
    type: 'bar',
    data: {
        labels: {!! $jatuhTempoLabels !!},
        datasets: [{ label: 'Yield (%)', data: {!! $analisa->sukuk->whereNotNull('jatuh_tempo')->pluck('yield') !!}, backgroundColor: '#1e3a5f' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => v + '%' } } } }
});
@endif
</script>
@endpush
@endsection
