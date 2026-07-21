@extends($layout)

@section('title', $stock->kode . ' - Detail Saham')

@section('content')
    @php
        $profile = $stock->profile;
        $activeTab = session('active_tab', request('tab', 'info'));
        $fmt = fn($value) => filled($value) ? number_format((float) $value, 0, ',', '.') : '-';
        $actionLabels = [
            'dividen' => 'Dividen',
            'stock_split' => 'Stock Split',
            'rights_issue' => 'Rights Issue',
            'buyback' => 'Buyback',
            'private_placement' => 'Private Placement',
            'merger_akuisisi' => 'Merger Akuisisi',
        ];
    @endphp

    <div x-data="stockDetail(
        @js($activeTab),
        @js(route($routePrefix . '.saham.fetch-yahoo', $stock)),
        @js(route($routePrefix . '.saham.fetch-summary', $stock)),
        @js(route($routePrefix . '.saham.search-stock')),
        @js(route($routePrefix . '.saham.compare-chart')),
        @js(
    $stock->corporateActions->map(
        fn($a) => [
            'action_type' => $a->action_type,
            'action_date' => $a->action_date->format('Y-m-d'),
            'description' => $a->description,
            'value' => $a->value,
        ],
    ),
)
    )" x-init="init()" class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <a href="{{ route($routePrefix . '.saham.index') }}" class="text-sm text-muted hover:text-primary">← Daftar
                    Saham</a>
                <h1 class="text-2xl font-bold text-primary mt-2">{{ $stock->nama }}</h1>
                <p class="text-sm text-muted">{{ $stock->kode }} · {{ $stock->sektor ?: '-' }}</p>
            </div>
            <div class="bg-white rounded-xl border border-line px-5 py-3 text-right">
                <p class="text-xs text-muted">Harga Terakhir</p>
                <p class="page-title">
                    {{ $stock->harga_terbaru ? 'Rp' . $fmt($stock->harga_terbaru) : '-' }}</p>
            </div>
        </div>
        <div class="relative" x-data="{ open: false, search: '' }" @click.outside="open = false">
            <button type="button" @click="open = !open"
                class="flex items-center gap-2 w-44 text-xs border border-line rounded-lg px-3 py-1.5 text-left text-muted hover:text-primary hover:border-primary transition">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="flex-1 truncate" x-text="search || 'Cari Saham...'"></span>
                <svg class="w-3 h-3 shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <template x-if="open">
                <div
                    class="absolute top-full left-0 mt-1 w-72 bg-white border border-line rounded-xl shadow-lg z-50 overflow-hidden">
                    <input type="text" x-model="search" placeholder="Filter nama saham..."
                        class="w-full text-xs border-b border-line px-3 py-2 focus:outline-none">
                    <div class="max-h-60 overflow-y-auto">
                        @foreach ($stocks as $s)
                            <a href="{{ route($routePrefix . '.saham.show', $s->id) }}"
                                x-show="!search || '{{ strtolower($s->kode) }} {{ strtolower($s->nama) }}'.includes(search.toLowerCase())"
                                class="block w-full text-left px-3 py-2 hover:bg-indigo-50 text-xs border-b border-line last:border-0">
                                <span class="font-semibold">{{ $s->kode }}</span>
                                <span class="text-muted"> - {{ $s->nama }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </template>
        </div>
        @foreach (['success' => 'green', 'error' => 'red'] as $key => $color)
            @if (session($key))
                <div
                    class="px-4 py-3 rounded-xl text-sm border {{ $color === 'green' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' }}">
                    {{ session($key) }}
                </div>
            @endif
        @endforeach

        <div class="table-card">
            <div class="flex overflow-x-auto border-b border-line">
                <button type="button" @click="tab='info'"
                    :class="tab === 'info' ? 'border-b-2 border-primary text-primary font-semibold' :
                        'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">Informasi Perusahaan</button>
                <button type="button" @click="tab='grafik'"
                    :class="tab === 'grafik' ? 'border-b-2 border-primary text-primary font-semibold' :
                        'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">Grafik Saham</button>
                <button type="button" @click="tab='laporan'"
                    :class="tab === 'laporan' ? 'border-b-2 border-primary text-primary font-semibold' :
                        'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">Laporan Keuangan</button>
                <button type="button" @click="tab='berita'"
                    :class="tab === 'berita' ? 'border-b-2 border-primary text-primary font-semibold' :
                        'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">Berita Terkait</button>
                <button type="button" @click="tab='riset-broker'"
                    :class="tab === 'riset-broker' ? 'border-b-2 border-primary text-primary font-semibold' :
                        'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">Riset Broker Terkait</button>
                <button type="button" @click="tab='detail-broker'"
                    :class="tab === 'detail-broker' ? 'border-b-2 border-primary text-primary font-semibold' :
                        'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">Detail Broker</button>
                <button type="button" @click="tab='reksa-dana'"
                    :class="tab === 'reksa-dana' ? 'border-b-2 border-primary text-primary font-semibold' :
                        'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">Daftar Reksa Dana</button>
            </div>

            <div x-show="tab==='info'" class="p-6 space-y-6">
                {{-- Data dari database --}}
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    @foreach ([
            'Nama Perusahaan' => $profile->company_name ?? $stock->nama,
            'Kode Saham' => $profile->stock_code ?? $stock->kode,
            'Sektor' => $profile->sector ?? $stock->sektor,
            'Sub Sektor' => $profile->sub_sector ?? null,
            'Industri' => $profile->industry ?? $stock->sub_industri,
            'Website' => $profile->website ?? null,
            'Email' => $profile->email ?? null,
            'Telepon' => $profile->phone ?? null,
            'Alamat' => $profile->address ?? null,
        ] as $label => $value)
                        <div class="border border-line rounded-xl p-4">
                            <p class="text-xs text-muted">{{ $label }}</p>
                            <p class="font-semibold text-primary mt-1 break-words">{{ $value ?: '-' }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="border border-line rounded-xl p-4 text-sm">
                    <p class="text-xs text-muted mb-1">Deskripsi</p>
                    <p class="text-gray-700 leading-relaxed">
                        {{ $profile->description ?? 'Deskripsi perusahaan belum tersedia.' }}</p>
                </div>

                {{-- Yahoo Sync Date --}}
                @if ($stock->yahoo_synced_at)
                    <div class="flex items-center gap-2 text-xs text-muted">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                        Sinkronisasi Yahoo: {{ $stock->yahoo_synced_at->format('d/m/Y H:i') }}
                    </div>
                @endif

                {{-- Data dari Yahoo Finance (yfapi) --}}
                <div x-show="summary" class="space-y-4">
                    <h3 class="font-semibold text-primary">Data Detail Saham</h3>

                    {{-- Profile dari yfapi --}}
                    <div x-show="summary && summary.profile" class="grid md:grid-cols-2 gap-3 text-sm">
                        <template x-if="summary && summary.profile.website">
                            <div class="border border-line rounded-xl p-4">
                                <p class="text-xs text-muted">Website</p>
                                <a :href="summary.profile.website" target="_blank"
                                    class="font-semibold text-primary break-words hover:underline"
                                    x-text="summary.profile.website"></a>
                            </div>
                        </template>
                        <template x-if="summary && summary.profile.phone">
                            <div class="border border-line rounded-xl p-4">
                                <p class="text-xs text-muted">Telepon</p>
                                <p class="font-semibold text-primary" x-text="summary.profile.phone"></p>
                            </div>
                        </template>
                        <template x-if="summary && summary.profile.address">
                            <div class="border border-line rounded-xl p-4 md:col-span-2">
                                <p class="text-xs text-muted">Alamat</p>
                                <p class="font-semibold text-primary" x-text="summary.profile.address"></p>
                            </div>
                        </template>
                        <template x-if="summary && summary.profile.employees">
                            <div class="border border-line rounded-xl p-4">
                                <p class="text-xs text-muted">Karyawan</p>
                                <p class="font-semibold text-primary"
                                    x-text="summary.profile.employees ? Number(summary.profile.employees).toLocaleString('id-ID') : '-'">
                                </p>
                            </div>
                        </template>
                    </div>

                    {{-- Key Stats --}}
                    <div x-show="summary && summary.stats" class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div class="border border-line rounded-xl p-3">
                            <p class="text-xs text-muted">P/E Ratio</p>
                            <p class="font-bold text-primary"
                                x-text="summary?.stats?.trailingPE ? Number(summary.stats.trailingPE).toFixed(2) : '-'">
                            </p>
                        </div>
                        <div class="border border-line rounded-xl p-3">
                            <p class="text-xs text-muted">P/B Ratio</p>
                            <p class="font-bold text-primary"
                                x-text="summary?.stats?.priceToBook ? Number(summary.stats.priceToBook).toFixed(2) : '-'">
                            </p>
                        </div>
                        <div class="border border-line rounded-xl p-3">
                            <p class="text-xs text-muted">EPS (TTM)</p>
                            <p class="font-bold text-primary"
                                x-text="summary?.stats?.earningsPerShare ? Number(summary.stats.earningsPerShare).toFixed(2) : '-'">
                            </p>
                        </div>
                        <div class="border border-line rounded-xl p-3">
                            <p class="text-xs text-muted">Beta</p>
                            <p class="font-bold text-primary"
                                x-text="summary?.stats?.beta ? Number(summary.stats.beta).toFixed(2) : '-'"></p>
                        </div>
                        <div class="border border-line rounded-xl p-3">
                            <p class="text-xs text-muted">Profit Margin</p>
                            <p class="font-bold text-primary"
                                x-text="summary?.stats?.profitMargins ? (Number(summary.stats.profitMargins)*100).toFixed(2)+'%' : '-'">
                            </p>
                        </div>
                        <div class="border border-line rounded-xl p-3">
                            <p class="text-xs text-muted">Dividend Yield</p>
                            <p class="font-bold text-primary"
                                x-text="summary?.stats?.dividendYield ? (Number(summary.stats.dividendYield)*100).toFixed(2)+'%' : '-'">
                            </p>
                        </div>
                        <div class="border border-line rounded-xl p-3">
                            <p class="text-xs text-muted">Book Value</p>
                            <p class="font-bold text-primary"
                                x-text="summary?.stats?.bookValue ? fmt(summary.stats.bookValue) : '-'"></p>
                        </div>
                        <div class="border border-line rounded-xl p-3">
                            <p class="text-xs text-muted">Shares Outstanding</p>
                            <p class="font-bold text-primary"
                                x-text="summary?.stats?.sharesOutstanding ? fmt(summary.stats.sharesOutstanding) : '-'">
                            </p>
                        </div>
                    </div>

                    {{-- Deskripsi dari yfapi --}}
                    <template x-if="summary && summary.profile.description">
                        <div class="border border-line rounded-xl p-4 text-sm">
                            <p class="text-xs text-muted mb-1">Deskripsi (Yahoo Finance)</p>
                            <p class="text-gray-700 leading-relaxed" x-text="summary.profile.description"></p>
                        </div>
                    </template>
                </div>
                <div x-show="summaryLoading" class="text-xs text-muted">Memuat data Yahoo Finance...</div>
                <div x-show="summaryError" x-text="summaryError" class="text-xs text-red-500"></div>

                <div>
                    <h3 class="font-semibold text-primary mb-3">Aksi Korporasi</h3>
                    <div class="grid md:grid-cols-2 gap-3">
                        @foreach ($actionLabels as $type => $label)
                            @php $items = $stock->corporateActions->where('action_type', $type); @endphp
                            <div class="border border-line rounded-xl p-4">
                                <p class="font-semibold text-sm text-primary">{{ $label }}</p>
                                @forelse ($items as $action)
                                    <p class="text-sm mt-2">{{ $action->action_date->format('d/m/Y') }} ·
                                        {{ $action->description }}</p>
                                @empty
                                    <p class="text-sm text-muted mt-2">Belum tersedia.</p>
                                @endforelse
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Financial Highlights dari Yahoo Finance --}}
                <div x-show="summary && summary.highlights"
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">
                                Financial Highlights
                            </h3>
                            <p class="text-sm text-slate-500">
                                Ringkasan performa dan kesehatan keuangan perusahaan.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-3">

                        <!-- Profitabilitas -->
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5">
                            <h4 class="mb-4 text-xs font-semibold uppercase tracking-wider text-black">
                                Profitabilitas
                            </h4>

                            <div class="space-y-3 text-sm">

                                <div class="flex justify-between">
                                    <span class="text-slate-500">Gross Margin</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.grossMargins ? (Number(summary.highlights.grossMargins)*100).toFixed(2)+'%' : '-'"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-500">Operating Margin</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.operatingMargins ? (Number(summary.highlights.operatingMargins)*100).toFixed(2)+'%' : '-'"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-500">EBITDA Margin</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.ebitdaMargins ? (Number(summary.highlights.ebitdaMargins)*100).toFixed(2)+'%' : '-'"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-500">ROE</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.returnOnEquity ? (Number(summary.highlights.returnOnEquity)*100).toFixed(2)+'%' : '-'"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-500">ROA</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.returnOnAssets ? (Number(summary.highlights.returnOnAssets)*100).toFixed(2)+'%' : '-'"></span>
                                </div>

                            </div>
                        </div>

                        <!-- Pertumbuhan -->
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5">

                            <h4 class="mb-4 text-xs font-semibold uppercase tracking-wider text-black">
                                Pertumbuhan & Valuasi
                            </h4>

                            <div class="space-y-3 text-sm">

                                <div class="flex justify-between">
                                    <span class="text-slate-500">Revenue Growth</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.revenueGrowth ? (Number(summary.highlights.revenueGrowth)*100).toFixed(2)+'%' : '-'"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-500">Earnings Growth</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.earningsGrowth ? (Number(summary.highlights.earningsGrowth)*100).toFixed(2)+'%' : '-'"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-500">Quarter Growth</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.earningsQuarterlyGrowth ? (Number(summary.highlights.earningsQuarterlyGrowth)*100).toFixed(2)+'%' : '-'"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-500">Current Price</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.currentPrice ? fmt(summary.highlights.currentPrice) : '-'"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-500">Target Price</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.targetMeanPrice ? fmt(summary.highlights.targetMeanPrice) : '-'"></span>
                                </div>

                            </div>

                        </div>

                        <!-- Kesehatan -->
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5">

                            <h4 class="mb-4 text-xs font-semibold uppercase tracking-wider text-black">
                                Kesehatan Keuangan
                            </h4>

                            <div class="space-y-3 text-sm">

                                <div class="flex justify-between">
                                    <span class="text-slate-500">Debt / Equity</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.debtToEquity ? Number(summary.highlights.debtToEquity).toFixed(2) : '-'"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-500">Total Revenue</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.totalRevenue ? fmt(summary.highlights.totalRevenue) : '-'"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-500">Free Cash Flow</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.freeCashflow ? fmt(summary.highlights.freeCashflow) : '-'"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-slate-500">Operating Cash Flow</span>
                                    <span class="font-semibold text-slate-800"
                                        x-text="summary?.highlights?.operatingCashflow ? fmt(summary.highlights.operatingCashflow) : '-'"></span>
                                </div>

                            </div>

                        </div>

                    </div>
                </div>
                {{-- Data Laporan Keuangan untuk Analisa FFS --}}
                @if ($stock->financialReports->isNotEmpty())
                    <div>
                        <h3 class="font-semibold text-primary mb-3">Laporan Keuangan (untuk Analisa FFS)</h3>
                        @foreach ($stock->financialReports as $report)
                            <div class="border border-line rounded-xl p-4 mb-4">
                                <h4 class="font-semibold text-primary text-sm">{{ $report->report_year }} ·
                                    {{ $report->report_period }}</h4>
                                <div class="grid md:grid-cols-3 gap-4 mt-3 text-sm">
                                    <div>
                                        <p class="font-semibold mb-2 text-xs uppercase text-muted">Neraca</p>
                                        <p>Total Aset: <span class="font-medium">{{ $fmt($report->total_asset) }}</span>
                                        </p>
                                        <p>Total Liabilitas: <span
                                                class="font-medium">{{ $fmt($report->total_liabilities) }}</span></p>
                                        <p>Total Ekuitas: <span
                                                class="font-medium">{{ $fmt($report->total_equity) }}</span></p>
                                    </div>
                                    <div>
                                        <p class="font-semibold mb-2 text-xs uppercase text-muted">Laba Rugi</p>
                                        <p>Pendapatan: <span class="font-medium">{{ $fmt($report->revenue) }}</span></p>
                                        <p>Laba Operasional: <span
                                                class="font-medium">{{ $fmt($report->operating_income) }}</span></p>
                                        <p>Laba Bersih: <span class="font-medium">{{ $fmt($report->net_income) }}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="font-semibold mb-2 text-xs uppercase text-muted">Arus Kas</p>
                                        <p>CFO: <span class="font-medium">{{ $fmt($report->cfo) }}</span></p>
                                        <p>CFI: <span class="font-medium">{{ $fmt($report->cfi) }}</span></p>
                                        <p>CFF: <span class="font-medium">{{ $fmt($report->cff) }}</span></p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($stock->financialReports->isNotEmpty())
                    <div>
                        <h3 class="font-semibold text-primary mb-3">Laporan Keuangan (untuk Analisa FFS)</h3>
                        @foreach ($stock->financialReports as $report)
                            <div class="border border-line rounded-xl p-4 mb-4">
                                <h4 class="font-semibold text-primary text-sm">{{ $report->report_year }} ·
                                    {{ $report->report_period }}</h4>
                                <div class="grid md:grid-cols-3 gap-4 mt-3 text-sm">
                                    <div>
                                        <p class="font-semibold mb-2 text-xs uppercase text-muted">Neraca</p>
                                        <p>Total Aset: <span class="font-medium">{{ $fmt($report->total_asset) }}</span>
                                        </p>
                                        <p>Total Liabilitas: <span
                                                class="font-medium">{{ $fmt($report->total_liabilities) }}</span></p>
                                        <p>Total Ekuitas: <span
                                                class="font-medium">{{ $fmt($report->total_equity) }}</span></p>
                                    </div>
                                    <div>
                                        <p class="font-semibold mb-2 text-xs uppercase text-muted">Laba Rugi</p>
                                        <p>Pendapatan: <span class="font-medium">{{ $fmt($report->revenue) }}</span></p>
                                        <p>Laba Operasional: <span
                                                class="font-medium">{{ $fmt($report->operating_income) }}</span></p>
                                        <p>Laba Bersih: <span class="font-medium">{{ $fmt($report->net_income) }}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="font-semibold mb-2 text-xs uppercase text-muted">Arus Kas</p>
                                        <p>CFO: <span class="font-medium">{{ $fmt($report->cfo) }}</span></p>
                                        <p>CFI: <span class="font-medium">{{ $fmt($report->cfi) }}</span></p>
                                        <p>CFF: <span class="font-medium">{{ $fmt($report->cff) }}</span></p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div x-show="tab==='grafik'" class="p-6 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap gap-2">
                        @foreach (['1D' => '1d', '5D' => '5d', '1M' => '1mo', '3M' => '3mo', '6M' => '6mo', '1Y' => '1y', '5Y' => '5y', 'Sejak IPO' => 'max'] as $label => $val)
                            <button type="button" @click="changeRange('{{ $val }}')"
                                :class="range === '{{ $val }}' ? 'bg-primary text-white border-primary' :
                                    'border-line text-muted hover:text-primary'"
                                class="px-3 py-1.5 rounded-lg border text-xs font-semibold">{{ $label }}</button>
                        @endforeach
                        <select x-model="interval" @change="fetchData()"
                            class="border border-line rounded-lg text-xs px-2 py-1.5 bg-white text-muted">
                            <option value="auto">Interval</option>
                            <option value="1m">1 menit</option>
                            <option value="2m">2 menit</option>
                            <option value="5m">5 menit</option>
                            <option value="15m">15 menit</option>
                            <option value="30m">30 menit</option>
                            <option value="1h">1 jam</option>
                            <option value="1d">1 hari</option>
                            <option value="1wk">1 minggu</option>
                            <option value="1mo">1 bulan</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- <div class="relative" x-data="{ open: false, search: '' }" @click.outside="open = false">
                            <button type="button" @click="open = !open"
                                class="flex items-center gap-2 w-44 text-xs border border-line rounded-lg px-3 py-1.5 text-left text-muted hover:text-primary hover:border-primary transition">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span class="flex-1 truncate" x-text="search || 'Cari Saham...'"></span>
                                <svg class="w-3 h-3 shrink-0" :class="open ? 'rotate-180' : ''" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <template x-if="open">
                                <div
                                    class="absolute top-full left-0 mt-1 w-72 bg-white border border-line rounded-xl shadow-lg z-50 overflow-hidden">
                                    <input type="text" x-model="search" placeholder="Filter nama saham..."
                                        class="w-full text-xs border-b border-line px-3 py-2 focus:outline-none">
                                    <div class="max-h-60 overflow-y-auto">
                                        @foreach ($stocks as $s)
                                            <a href="{{ route($routePrefix . '.saham.show', $s->id) }}"
                                                x-show="!search || '{{ strtolower($s->kode) }} {{ strtolower($s->nama) }}'.includes(search.toLowerCase())"
                                                class="block w-full text-left px-3 py-2 hover:bg-indigo-50 text-xs border-b border-line last:border-0">
                                                <span class="font-semibold">{{ $s->kode }}</span>
                                                <span class="text-muted"> - {{ $s->nama }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </template>
                        </div> --}}
                        <div class="flex gap-1 p-0.5 border border-line rounded-lg">
                            <button type="button" @click="chartType = 'candle'"
                                :class="chartType === 'candle' ? 'bg-primary text-white' : 'text-muted hover:text-primary'"
                                class="px-2.5 py-1 rounded-md text-xs font-semibold transition">Candle</button>
                            <button type="button" @click="chartType = 'line'"
                                :class="chartType === 'line' ? 'bg-primary text-white' : 'text-muted hover:text-primary'"
                                class="px-2.5 py-1 rounded-md text-xs font-semibold transition">Line</button>
                        </div>
                        <div class="flex gap-1 p-0.5 border border-line rounded-lg">
                            <template
                                x-for="[key, label] of Object.entries({ sma: 'SMA', ema: 'EMA', bb: 'BB', rsi: 'RSI', macd: 'MACD' })"
                                :key="key">
                                <button type="button" @click="toggleIndicator(key)"
                                    :class="indicators[key] ? 'bg-indigo-600 text-white' : 'text-muted hover:text-primary'"
                                    class="px-2 py-1 rounded-md text-xs font-semibold transition flex items-center gap-1">
                                    <span x-text="label"></span>
                                    <template x-if="indicators[key]">
                                        <span class="text-[10px] opacity-70">⚙</span>
                                    </template>
                                </button>
                            </template>
                        </div>
                        <div class="relative" x-data="{ open: false, search: '', results: [] }" @click.outside="open = false">
                            <input type="text" x-model="search"
                                @input.debounce="open = true; searchStock(search, results)"
                                placeholder="Cari saham pembanding..."
                                class="w-36 text-xs border border-line rounded-lg px-2 py-1.5">
                            <div x-show="open && results.length > 0"
                                class="absolute top-full right-0 mt-1 w-64 bg-white border border-line rounded-xl shadow-lg z-50 max-h-60 overflow-y-auto">
                                <template x-for="s in results" :key="s.kode">
                                    <button type="button"
                                        @click="addComparison(s); search = ''; results = []; open = false"
                                        class="w-full text-left px-3 py-2 hover:bg-indigo-50 text-xs border-b border-line last:border-0">
                                        <span class="font-semibold" x-text="s.kode"></span>
                                        <span class="text-muted" x-text="' - ' + s.nama"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <span x-show="loading" class="text-xs text-muted flex items-center gap-1">
                            <span
                                class="inline-block w-3 h-3 border-2 border-primary border-t-transparent rounded-full animate-spin"></span>
                            Memuat data...
                        </span>
                        @if ($routePrefix === 'admin')
                            <form method="POST" action="{{ route($routePrefix . '.saham.sync-yahoo-prices', $stock) }}"
                                class="flex items-center gap-2">
                                @csrf
                                <select name="range" class="border-line rounded-lg text-xs py-1.5 px-2">
                                    @foreach (['1mo' => '1 Bulan', '3mo' => '3 Bulan', '6mo' => '6 Bulan', '1y' => '1 Tahun', '2y' => '2 Tahun', '5y' => '5 Tahun'] as $v => $l)
                                        <option value="{{ $v }}" {{ $v === '1y' ? 'selected' : '' }}>
                                            {{ $l }}</option>
                                    @endforeach
                                </select>
                                <button class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-semibold">Simpan
                                    ke
                                    DB</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div x-show="chartError" x-text="chartError"
                    class="px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700" x-cloak></div>

                {{-- Meta Info --}}
                <div x-show="meta" class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    <div class="border border-line rounded-xl p-3">
                        <p class="text-xs text-muted">Harga Saat Ini</p>
                        <p class="font-bold text-primary" x-text="meta ? 'Rp ' + fmt(meta.regularMarketPrice) : '-'"></p>
                    </div>
                    <div class="border border-line rounded-xl p-3">
                        <p class="text-xs text-muted">Penutupan Sebelumnya</p>
                        <p class="font-bold text-primary" x-text="meta ? 'Rp ' + fmt(meta.previousClose) : '-'"></p>
                    </div>
                    <div class="border border-line rounded-xl p-3">
                        <p class="text-xs text-muted">Tertinggi / Terendah Hari</p>
                        <p class="font-bold text-primary text-xs"
                            x-text="meta ? fmt(meta.regularMarketDayHigh) + ' / ' + fmt(meta.regularMarketDayLow) : '-'">
                        </p>
                    </div>
                    <div class="border border-line rounded-xl p-3">
                        <p class="text-xs text-muted">52W High / Low</p>
                        <p class="font-bold text-primary text-xs"
                            x-text="meta ? fmt(meta.fiftyTwoWeekHigh) + ' / ' + fmt(meta.fiftyTwoWeekLow) : '-'"></p>
                    </div>
                </div>

                {{-- Comparison chips --}}
                <div x-show="comparisons.length > 0" class="flex flex-wrap gap-2">
                    <template x-for="(cmp, i) in comparisons" :key="cmp.kode">
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                            :style="'background-color: ' + cmp.color + '18; color: ' + cmp.color + '; border: 1px solid ' + cmp
                                .color + '40'">
                            <span x-text="cmp.kode"></span>
                            <button type="button" @click="removeComparison(i)" class="hover:opacity-60">✕</button>
                        </div>
                    </template>
                </div>

                {{-- Indicator Settings Modal --}}
                <div x-show="indicatorModal" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center"
                    @click.self="indicatorModal = null">
                    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                        <h4 class="font-semibold text-primary mb-4"
                            x-text="(indicatorModal || '').toUpperCase() + ' Settings'"></h4>

                        {{-- RSI specific form --}}
                        <template x-if="indicatorModal === 'rsi'">
                            <div class="space-y-4 text-sm">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-muted mb-1">Period</label>
                                        <input type="number" x-model="indicatorSettings.rsi.period" min="1"
                                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20" />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-muted mb-1">Field</label>
                                        <select x-model="indicatorSettings.rsi.field"
                                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                                            <option value="close">Close</option>
                                            <option value="open">Open</option>
                                            <option value="high">High</option>
                                            <option value="low">Low</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" x-model="indicatorSettings.rsi.showZone"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        <span class="text-xs text-muted">Show Zone (OB/OS background)</span>
                                    </label>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-muted mb-1">Overbought</label>
                                        <input type="number" x-model="indicatorSettings.rsi.overbought" min="1"
                                            max="100"
                                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20" />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-muted mb-1">Oversold</label>
                                        <input type="number" x-model="indicatorSettings.rsi.oversold" min="1"
                                            max="100"
                                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-muted mb-1">Panel</label>
                                        <select x-model="indicatorSettings.rsi.panel"
                                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                                            <option value="separate">Separate Panel</option>
                                            <option value="main">Main Chart</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-muted mb-1">Y-Axis</label>
                                        <select x-model="indicatorSettings.rsi.yAxis"
                                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                                            <option value="right">Right</option>
                                            <option value="left">Left</option>
                                            <option value="hidden">Hidden</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" x-model="indicatorSettings.rsi.underlay"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        <span class="text-xs text-muted">Show as Underlay (behind candles)</span>
                                    </label>
                                </div>
                            </div>
                        </template>

                        {{-- Generic form for other indicators --}}
                        <template x-if="indicatorModal !== 'rsi'">
                            <div class="space-y-3 text-sm">
                                <template x-for="(val, key) in (indicatorSettings[indicatorModal] || {})"
                                    :key="key">
                                    <div>
                                        <label class="block text-xs text-muted mb-1"
                                            x-text="key.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase())"></label>
                                        <input type="number" x-model="indicatorSettings[indicatorModal][key]"
                                            min="1"
                                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20" />
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div class="flex justify-between gap-2 mt-5">
                            <button type="button" @click="disableIndicator(indicatorModal)"
                                class="px-4 py-2 text-xs font-semibold border border-red-200 text-red-600 rounded-lg hover:bg-red-50">Matikan</button>
                            <div class="flex gap-2">
                                <button type="button" @click="indicatorModal = null"
                                    class="px-4 py-2 text-xs font-semibold border border-line rounded-lg text-muted hover:text-primary">Batal</button>
                                <button type="button" @click="applyIndicatorSettings()"
                                    class="px-4 py-2 text-xs font-semibold bg-primary text-white rounded-lg">Terapkan</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Chart --}}
                <div x-show="meta && chartHasData" class="border border-line rounded-xl p-4">
                    <div id="yahooChartContainer"></div>
                </div>
                <div x-show="meta && !chartHasData"
                    class="px-4 py-3 rounded-xl text-sm bg-yellow-50 border border-yellow-200 text-yellow-700">
                    Data grafik tidak tersedia untuk range ini (mungkin di luar jam perdagangan).
                </div>

                <div x-show="!meta && !loading && !chartError"
                    class="p-12 text-center text-muted border border-line rounded-xl">
                    Memuat grafik...
                </div>
            </div>

            <div x-show="tab==='laporan'" class="p-6 space-y-6">
                {{-- Laporan Keuangan dari Yahoo Finance --}}
                <div x-show="summary && summary.financials && summary.financials.length > 0">
                    <h3 class="font-semibold text-primary mb-3">Laporan Keuangan (Yahoo Finance)</h3>
                    <div class="space-y-3">
                        <template x-for="f in (summary?.financials ?? [])" :key="f.endDate">
                            <div class="border border-line rounded-xl p-4 text-sm">
                                <h4 class="font-semibold text-primary mb-3" x-text="f.endDate ?? '-'"></h4>
                                <div class="grid md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="font-semibold mb-2 text-xs uppercase text-muted">Laba Rugi</p>
                                        <p>Pendapatan: <span class="font-medium"
                                                x-text="f.totalRevenue ? fmt(f.totalRevenue) : '-'"></span></p>
                                        <p>Laba Kotor: <span class="font-medium"
                                                x-text="f.grossProfit ? fmt(f.grossProfit) : '-'"></span></p>
                                        <p>Laba Operasional: <span class="font-medium"
                                                x-text="f.operatingIncome ? fmt(f.operatingIncome) : '-'"></span></p>
                                        <p>Laba Bersih: <span class="font-medium"
                                                x-text="f.netIncome ? fmt(f.netIncome) : '-'"></span></p>
                                    </div>
                                    <div>
                                        <p class="font-semibold mb-2 text-xs uppercase text-muted">Neraca</p>
                                        <p>Total Aset: <span class="font-medium"
                                                x-text="f.totalAssets ? fmt(f.totalAssets) : '-'"></span></p>
                                        <p>Total Liabilitas: <span class="font-medium"
                                                x-text="f.totalLiab ? fmt(f.totalLiab) : '-'"></span></p>
                                        <p>Total Ekuitas: <span class="font-medium"
                                                x-text="f.totalStockholderEquity ? fmt(f.totalStockholderEquity) : '-'"></span>
                                        </p>
                                        <p>Kas: <span class="font-medium" x-text="f.cash ? fmt(f.cash) : '-'"></span></p>
                                    </div>
                                    <div>
                                        <p class="font-semibold mb-2 text-xs uppercase text-muted">Arus Kas</p>
                                        <p>CFO: <span class="font-medium"
                                                x-text="f.totalCashFromOperatingActivities ? fmt(f.totalCashFromOperatingActivities) : '-'"></span>
                                        </p>
                                        <p>CFI: <span class="font-medium"
                                                x-text="f.totalCashflowsFromInvestingActivities ? fmt(f.totalCashflowsFromInvestingActivities) : '-'"></span>
                                        </p>
                                        <p>CFF: <span class="font-medium"
                                                x-text="f.totalCashFromFinancingActivities ? fmt(f.totalCashFromFinancingActivities) : '-'"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div x-show="summaryLoading" class="text-xs text-muted">Memuat laporan keuangan...</div>
                <div x-show="summary && (!summary.financials || summary.financials.length === 0) && !summaryLoading"
                    class="text-xs text-muted">Data laporan keuangan Yahoo Finance tidak tersedia.</div>

                {{-- Laporan dari database --}}
                @if ($stock->financialReports->isNotEmpty())
                    <div>
                        <h3 class="font-semibold text-primary mb-3">Laporan Keuangan (Database)</h3>
                        @foreach ($stock->financialReports as $report)
                            <div class="border border-line rounded-xl p-4 mb-4">
                                <h3 class="font-semibold text-primary">{{ $report->report_year }} ·
                                    {{ $report->report_period }}</h3>
                                <div class="grid md:grid-cols-3 gap-4 mt-4 text-sm">
                                    <div>
                                        <p class="font-semibold mb-2">Neraca</p>
                                        <p>Total Asset: {{ $fmt($report->total_asset) }}</p>
                                        <p>Total Liabilitas: {{ $fmt($report->total_liabilities) }}</p>
                                        <p>Total Ekuitas: {{ $fmt($report->total_equity) }}</p>
                                    </div>
                                    <div>
                                        <p class="font-semibold mb-2">Laba Rugi</p>
                                        <p>Pendapatan: {{ $fmt($report->revenue) }}</p>
                                        <p>Laba Operasional: {{ $fmt($report->operating_income) }}</p>
                                        <p>Laba Bersih: {{ $fmt($report->net_income) }}</p>
                                    </div>
                                    <div>
                                        <p class="font-semibold mb-2">Arus Kas</p>
                                        <p>CFO: {{ $fmt($report->cfo) }}</p>
                                        <p>CFI: {{ $fmt($report->cfi) }}</p>
                                        <p>CFF: {{ $fmt($report->cff) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div x-show="tab==='berita'" class="p-6 space-y-4">
                <div x-show="(summary?.news ?? []).length > 0" class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-primary">Berita Terkini</h3>
                            <p class="text-xs text-muted mt-0.5">Berita terbaru dari berbagai sumber terverifikasi.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold"
                            x-text="`${(summary?.news ?? []).length} artikel`"></span>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <template x-for="news in (summary?.news ?? [])" :key="news.url || news.title">
                            <article
                                class="group relative overflow-hidden border border-line rounded-2xl bg-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition duration-200">
                                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary to-emerald-400">
                                </div>
                                <div class="p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span
                                                class="w-9 h-9 shrink-0 rounded-xl bg-primary/10 text-primary grid place-items-center font-bold"
                                                x-text="sourceInitial(news.source)"></span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-primary truncate"
                                                    x-text="news.source || 'Sumber berita'"></p>
                                                <p class="text-[11px] text-muted" x-text="formatDate(news.publishedAt)">
                                                </p>
                                            </div>
                                        </div>
                                        <span class="shrink-0 px-2 py-1 rounded-full text-[10px] font-bold uppercase"
                                            :class="news.sourceType === 'google' ? 'bg-orange-50 text-orange-700' :
                                                'bg-blue-50 text-blue-700'"
                                            x-text="news.sourceType === 'google' ? 'Google News' : 'Yahoo Finance'"></span>
                                    </div>
                                    <h3 class="mt-4 text-base leading-snug font-bold text-primary">
                                        <a x-show="news.url" :href="news.url" target="_blank"
                                            rel="noopener noreferrer" class="group-hover:text-accent transition"
                                            x-text="news.title"></a>
                                        <span x-show="!news.url" x-text="news.title"></span>
                                    </h3>
                                    <div class="mt-5 pt-4 border-t border-line flex items-center justify-between">
                                        <span class="text-xs text-muted">Artikel eksternal</span>
                                        <a x-show="news.url" :href="news.url" target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 text-xs text-primary font-bold hover:text-accent transition">
                                            Baca selengkapnya <span aria-hidden="true">→</span>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>
                <div x-show="summaryLoading" class="text-xs text-muted">Memuat berita terkait...</div>
                @if ($routePrefix === 'admin')
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route($routePrefix . '.saham.summarize-news', $stock) }}">@csrf
                            <button class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold">Generate AI
                                Summary</button>
                        </form>
                        <form method="POST" action="{{ route($routePrefix . '.saham.refresh-news', $stock) }}">
                            @csrf
                            <button
                                class="px-4 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition">
                                Refresh Berita Terkini
                            </button>
                        </form>
                    </div>
                @endif
                @php
                    $newsSources = $stock->news
                        ->filter(
                            fn($news) => filter_var($news->url, FILTER_VALIDATE_URL) &&
                                in_array(parse_url($news->url, PHP_URL_SCHEME), ['http', 'https'], true),
                        )
                        ->unique('url');
                @endphp
                @if ($newsSources->isNotEmpty())
                    <div class="border border-line rounded-2xl p-4 text-sm bg-slate-50/70">
                        <h3 class="font-semibold text-primary">Sumber Website Berita Tersimpan</h3>
                        <div class="flex flex-wrap gap-2 mt-3">
                            @foreach ($newsSources as $source)
                                <a href="{{ $source->url }}" target="_blank" rel="noopener noreferrer"
                                    class="px-3 py-1.5 border border-line bg-white rounded-full text-primary font-semibold hover:border-primary/40 hover:shadow-sm transition">
                                    {{ $source->source ?: parse_url($source->url, PHP_URL_HOST) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                @forelse ($stock->news as $news)
                    @php
                        $newsUrl =
                            filter_var($news->url, FILTER_VALIDATE_URL) &&
                            in_array(parse_url($news->url, PHP_URL_SCHEME), ['http', 'https'], true)
                                ? $news->url
                                : null;
                        $isAiGeneratedNews = str_contains($news->summary ?? '', 'Konten dibuat oleh AI.');
                    @endphp
                    <article
                        class="relative overflow-hidden border border-line rounded-2xl bg-white shadow-sm hover:shadow-md transition duration-200">
                        <div
                            class="absolute inset-y-0 left-0 w-1 {{ $isAiGeneratedNews ? 'bg-amber-400' : 'bg-primary' }}">
                        </div>
                        <div class="p-5 pl-6">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-9 h-9 rounded-xl {{ $isAiGeneratedNews ? 'bg-amber-50 text-amber-700' : 'bg-primary/10 text-primary' }} grid place-items-center font-bold">
                                        {{ strtoupper(mb_substr($news->source ?: 'S', 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="text-xs font-bold text-primary">{{ $news->source ?: 'Sumber berita' }}
                                        </p>
                                        <p class="text-[11px] text-muted">
                                            {{ optional($news->published_at)->format('d M Y') ?: 'Tanggal tidak tersedia' }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $isAiGeneratedNews ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ $isAiGeneratedNews ? 'Konten AI' : 'Berita Tersimpan' }}
                                </span>
                            </div>
                            <h3 class="mt-4 text-base leading-snug font-bold text-primary">
                                @if ($newsUrl)
                                    <a href="{{ $newsUrl }}" target="_blank" rel="noopener noreferrer"
                                        class="hover:text-accent transition">{{ $news->title }}</a>
                                @else
                                    {{ $news->title }}
                                @endif
                            </h3>
                            <p class="mt-3 text-sm text-gray-600 leading-relaxed whitespace-pre-line">
                                {{ $news->summary ?: '-' }}</p>
                            @if ($news->ai_summary)
                                <div class="mt-4 p-4 bg-blue-50 border border-blue-100 rounded-xl text-sm text-blue-800">
                                    <p class="text-[10px] uppercase font-bold tracking-wide text-blue-600 mb-1">AI Summary
                                    </p>
                                    <p>{{ $news->ai_summary }}</p>
                                </div>
                            @endif
                            <div class="mt-5 pt-4 border-t border-line flex items-center justify-between gap-3">
                                <span
                                    class="text-xs text-muted">{{ $isAiGeneratedNews ? 'Referensi media' : 'Sumber artikel' }}</span>
                                @if ($newsUrl)
                                    <a href="{{ $newsUrl }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 text-xs text-primary font-bold hover:text-accent transition">
                                        {{ $isAiGeneratedNews ? 'Kunjungi website' : 'Baca selengkapnya' }}
                                        <span aria-hidden="true">→</span>
                                    </a>
                                @else
                                    <span class="text-xs text-muted">Website tidak tersedia</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div x-show="!summaryLoading && (summary?.news ?? []).length === 0"
                        class="p-12 text-center text-muted border border-line rounded-xl">Berita terkait belum tersedia.
                    </div>
                @endforelse
            </div>

            <div x-show="tab==='riset-broker'" class="p-6 space-y-5">
                <div x-show="summary?.analysts" class="space-y-4">
                    <h3 class="font-semibold text-primary">Konsensus Analis Yahoo Finance</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div class="border border-line rounded-xl p-4">
                            <p class="text-xs text-muted">Rekomendasi</p>
                            <p class="font-bold text-primary uppercase"
                                x-text="summary?.analysts?.recommendationKey || '-'"></p>
                        </div>
                        <div class="border border-line rounded-xl p-4">
                            <p class="text-xs text-muted">Target Rata-rata</p>
                            <p class="font-bold text-primary" x-text="fmt(summary?.analysts?.targetMeanPrice)"></p>
                        </div>
                        <div class="border border-line rounded-xl p-4">
                            <p class="text-xs text-muted">Target Tertinggi</p>
                            <p class="font-bold text-primary" x-text="fmt(summary?.analysts?.targetHighPrice)"></p>
                        </div>
                        <div class="border border-line rounded-xl p-4">
                            <p class="text-xs text-muted">Jumlah Analis</p>
                            <p class="font-bold text-primary" x-text="fmt(summary?.analysts?.numberOfAnalystOpinions)">
                            </p>
                        </div>
                    </div>
                    <div x-show="(summary?.analysts?.trend ?? []).length > 0" class="overflow-x-auto">
                        <table class="w-full text-sm border border-line">
                            <thead class="bg-gray-50 text-muted">
                                <tr>
                                    <th class="px-3 py-2 text-left">Periode</th>
                                    <th class="px-3 py-2 text-right">Strong Buy</th>
                                    <th class="px-3 py-2 text-right">Buy</th>
                                    <th class="px-3 py-2 text-right">Hold</th>
                                    <th class="px-3 py-2 text-right">Sell</th>
                                    <th class="px-3 py-2 text-right">Strong Sell</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="trend in (summary?.analysts?.trend ?? [])" :key="trend.period">
                                    <tr class="border-t border-line">
                                        <td class="px-3 py-2" x-text="trend.period"></td>
                                        <td class="px-3 py-2 text-right" x-text="trend.strongBuy ?? 0"></td>
                                        <td class="px-3 py-2 text-right" x-text="trend.buy ?? 0"></td>
                                        <td class="px-3 py-2 text-right" x-text="trend.hold ?? 0"></td>
                                        <td class="px-3 py-2 text-right" x-text="trend.sell ?? 0"></td>
                                        <td class="px-3 py-2 text-right" x-text="trend.strongSell ?? 0"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div x-show="(summary?.analysts?.upgradesDowngrades ?? []).length > 0" class="space-y-2">
                        <h4 class="font-semibold text-sm text-primary">Perubahan Rating Terbaru</h4>
                        <template x-for="item in (summary?.analysts?.upgradesDowngrades ?? []).slice(0, 8)"
                            :key="`${item.epochGradeDate}-${item.firm}-${item.toGrade}`">
                            <div class="border border-line rounded-xl p-3 text-sm">
                                <span class="font-semibold" x-text="item.firm || '-'"></span>
                                <span class="text-muted"
                                    x-text="` · ${formatEpoch(item.epochGradeDate)} · ${item.fromGrade || '-'} -> ${item.toGrade || '-'}`"></span>
                            </div>
                        </template>
                    </div>
                </div>
                <div x-show="summaryLoading" class="text-xs text-muted">Memuat konsensus analis...</div>

                <div class="grid md:grid-cols-4 gap-3 text-sm">
                    <div class="border border-line rounded-xl p-4">
                        <p class="text-xs text-muted">Target Tertinggi</p>
                        <p class="font-bold text-primary">{{ $fmt($consensus['highest']) }}</p>
                    </div>
                    <div class="border border-line rounded-xl p-4">
                        <p class="text-xs text-muted">Target Terendah</p>
                        <p class="font-bold text-primary">{{ $fmt($consensus['lowest']) }}</p>
                    </div>
                    <div class="border border-line rounded-xl p-4">
                        <p class="text-xs text-muted">Rata-rata Target</p>
                        <p class="font-bold text-primary">{{ $fmt($consensus['average']) }}</p>
                    </div>
                    <div class="border border-line rounded-xl p-4">
                        <p class="text-xs text-muted">Potensi Upside</p>
                        <p class="font-bold text-primary">
                            {{ filled($consensus['upside']) ? number_format($consensus['upside'], 2) . '%' : '-' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route($routePrefix . '.saham.summarize-broker-research', $stock) }}">
                    @csrf
                    <button class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold">Generate AI
                        Summary</button>
                </form>
                @forelse ($stock->brokerResearches as $research)
                    <div class="border border-line rounded-xl p-4 text-sm">
                        <div class="flex flex-wrap justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-primary">{{ $research->broker_name }}</h3>
                                <p class="text-xs text-muted">
                                    {{ optional($research->research_date)->format('d/m/Y') ?: '-' }} · Rating
                                    {{ $research->rating ?: '-' }} · TP {{ $fmt($research->target_price) }}</p>
                            </div>
                            <div class="flex gap-2">
                                @if ($research->pdf_file)
                                    <a target="_blank"
                                        href="{{ route($routePrefix . '.saham.broker-research.view', [$stock, $research]) }}"
                                        class="px-3 py-1.5 border border-line rounded-lg">Preview PDF</a>
                                    <a href="{{ route($routePrefix . '.saham.broker-research.download', [$stock, $research]) }}"
                                        class="px-3 py-1.5 border border-line rounded-lg">Download PDF</a>
                                @endif
                            </div>
                        </div>
                        @if ($research->ai_summary)
                            <p class="mt-3 p-3 bg-blue-50 rounded-lg text-blue-800">{{ $research->ai_summary }}</p>
                        @endif
                    </div>
                @empty
                    @if ($legacyResearches->isEmpty())
                        <div class="p-12 text-center text-muted border border-line rounded-xl">Riset broker terkait belum
                            tersedia.</div>
                    @endif
                @endforelse
                @foreach ($legacyResearches as $item)
                    <div class="border border-line rounded-xl p-4 text-sm">
                        <h3 class="font-semibold text-primary">{{ $item['document']->broker }}</h3>
                        <p class="text-xs text-muted">{{ $item['document']->created_at->format('d/m/Y') }} ·
                            {{ $item['document']->original_name }}</p>
                        <div class="flex gap-2 mt-3">
                            <a target="_blank"
                                href="{{ route($routePrefix . '.analisa-saham.riset-broker.view', [$item['analysis'], $item['document']]) }}"
                                class="px-3 py-1.5 border border-line rounded-lg">Preview PDF</a>
                            <a href="{{ route($routePrefix . '.analisa-saham.riset-broker.download', [$item['analysis'], $item['document']]) }}"
                                class="px-3 py-1.5 border border-line rounded-lg">Download PDF</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div x-show="tab==='detail-broker'" class="p-6 space-y-6">
                @if ($routePrefix === 'admin')
                    <div class="border border-line rounded-xl p-5">
                        <h3 class="font-semibold text-primary mb-4">Upload Dokumen Broker</h3>
                        <form method="POST" action="{{ route('admin.saham.broker-documents.store', $stock) }}"
                            enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-muted mb-1">Nama Broker <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="broker_name" value="{{ old('broker_name') }}" required
                                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-muted mb-1">Judul <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="judul" value="{{ old('judul') }}" required
                                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-muted mb-1">Tanggal <span
                                            class="text-red-500">*</span></label>
                                    <input type="date" name="tanggal" value="{{ old('tanggal') }}" required
                                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-muted mb-1">Upload Dokumen <span
                                            class="text-red-500">*</span></label>
                                    <input type="file" name="dokumen" required
                                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary">
                                    <p class="text-xs text-muted mt-1">PDF, Word, Excel, PPT · Maks. 20MB</p>
                                </div>
                            </div>
                            <button type="submit"
                                class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold">Upload
                                Dokumen</button>
                        </form>
                    </div>
                @endif

                @forelse ($stock->brokerDocuments as $doc)
                    <div
                        class="border border-line rounded-xl p-4 text-sm flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-primary">{{ $doc->broker_name }}</p>
                            <p class="text-muted text-xs mt-0.5">{{ $doc->judul }} ·
                                {{ $doc->tanggal->format('d/m/Y') }}</p>
                            <p class="text-xs text-muted mt-0.5">{{ $doc->original_name }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route($routePrefix . '.saham.broker-documents.view', [$stock, $doc]) }}"
                                target="_blank"
                                class="px-3 py-1.5 border border-line rounded-lg text-xs hover:border-primary transition">Lihat</a>
                            <a href="{{ route($routePrefix . '.saham.broker-documents.view', [$stock, $doc]) }}"
                                download="{{ $doc->original_name }}"
                                class="px-3 py-1.5 border border-line rounded-lg text-xs hover:border-primary transition">Download</a>
                            @if ($routePrefix === 'admin')
                                <form method="POST"
                                    action="{{ route('admin.saham.broker-documents.destroy', [$stock, $doc]) }}"
                                    onsubmit="return confirm('Hapus dokumen ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1.5 border border-red-200 text-red-600 rounded-lg text-xs hover:bg-red-50 transition">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-muted border border-line rounded-xl">Belum ada dokumen broker
                        tersedia.</div>
                @endforelse
            </div>

            <div x-show="tab==='reksa-dana'" class="p-6 space-y-4">
                <h3 class="font-semibold text-primary">Daftar Reksa Dana yang Memiliki Efek Ini</h3>
                @if ($reksaDanaHoldings->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border border-line">
                            <thead class="bg-gray-50 text-muted">
                                <tr>
                                    <th class="px-3 py-2 text-left whitespace-nowrap">Kode RD</th>
                                    <th class="px-3 py-2 text-left whitespace-nowrap">Nama Reksa Dana</th>
                                    <th class="px-3 py-2 text-left whitespace-nowrap">Kode Efek</th>
                                    <th class="px-3 py-2 text-right whitespace-nowrap">%NAB</th>
                                    <th class="px-3 py-2 text-right whitespace-nowrap">Nilai Pasar Saat Tanggal Data</th>
                                    <th class="px-3 py-2 text-right whitespace-nowrap">Harga Saat Tanggal Data</th>
                                    <th class="px-3 py-2 text-right whitespace-nowrap">Tanggal Data</th>
                                    <th class="px-3 py-2 text-right whitespace-nowrap">Jumlah Lembar</th>
                                    <th class="px-3 py-2 text-right whitespace-nowrap">Nilai Pasar Saat ini</th>
                                    <th class="px-3 py-2 text-right whitespace-nowrap">% Mkt Kap</th>
                                    <th class="px-3 py-2 text-right whitespace-nowrap">Prospektus/FFS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reksaDanaHoldings as $efek)
                                    @php
                                        $analisa = $efek->analisa;
                                        $rd = $analisa?->reksaDana;
                                        $rdId = $rd?->id;
                                        $hargaSaatTanggalData =
                                            $efek->nilai_pasar && $efek->jumlah_lembar
                                                ? round($efek->nilai_pasar / $efek->jumlah_lembar, 2)
                                                : null;
                                        $nilaiPasarSaatIni =
                                            $efek->jumlah_lembar && $stock->harga_terbaru
                                                ? $efek->jumlah_lembar * $stock->harga_terbaru
                                                : null;
                                        $marketCapPct =
                                            $stock->market_capital && $efek->nilai_pasar
                                                ? round(($efek->nilai_pasar / $stock->market_capital) * 100, 2)
                                                : null;
                                        $prospektusDate = isset($prospektusDates[$rdId])
                                            ? \Illuminate\Support\Carbon::parse($prospektusDates[$rdId])
                                            : null;
                                    @endphp
                                    <tr class="border-t border-line hover:bg-gray-50">
                                        <td class="px-3 py-2 font-semibold text-primary whitespace-nowrap">
                                            {{ $rd?->kode_reksa_dana ?? '-' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            {{ $rd?->nama_reksa_dana ?? ($analisa?->nama_reksa_dana ?? '-') }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $efek->kode_efek }}</td>
                                        <td class="px-3 py-2 text-right whitespace-nowrap">
                                            {{ $efek->persen_nab ?? $efek->bobot ? number_format((float) ($efek->persen_nab ?? $efek->bobot), 2) . '%' : '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-right whitespace-nowrap">
                                            {{ $efek->nilai_pasar ? 'Rp' . number_format((float) $efek->nilai_pasar, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-right whitespace-nowrap">
                                            {{ $hargaSaatTanggalData ? 'Rp' . number_format($hargaSaatTanggalData, 2, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-right whitespace-nowrap">
                                            {{ $analisa?->tanggal_data?->format('d/m/Y') ?: '-' }}</td>
                                        <td class="px-3 py-2 text-right whitespace-nowrap">
                                            {{ $efek->jumlah_lembar ? number_format((float) $efek->jumlah_lembar, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-right whitespace-nowrap">
                                            {{ $nilaiPasarSaatIni ? 'Rp' . number_format($nilaiPasarSaatIni, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-right whitespace-nowrap">
                                            {{ $marketCapPct !== null ? $marketCapPct . '%' : '-' }}</td>
                                        <td class="px-3 py-2 text-right whitespace-nowrap">
                                            {{ $prospektusDate?->format('d/m/Y') ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center text-muted border border-line rounded-xl">
                        Belum ada data Reksa Dana yang memiliki efek ini.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/lightweight-charts@4/dist/lightweight-charts.standalone.production.js"></script>
    <script>
        function stockDetail(activeTab, fetchUrl, summaryUrl, searchUrl, compareUrl, corporateEvents) {
            return {
                tab: activeTab,
                loading: false,
                chartError: null,
                meta: null,
                range: '1y',
                interval: 'auto',
                chartType: 'candle',
                chartInstance: null,
                chartHasData: false,
                summary: null,
                summaryLoading: false,
                summaryError: null,
                corporateEvents: corporateEvents || [],
                comparisons: [],
                indicators: {
                    sma: false,
                    ema: false,
                    bb: false,
                    rsi: false,
                    macd: false
                },
                indicatorSettings: {
                    sma: {
                        period1: 20,
                        period2: 50
                    },
                    ema: {
                        period1: 20,
                        period2: 50
                    },
                    bb: {
                        period: 20,
                        stddev: 2
                    },
                    rsi: {
                        period: 14,
                        field: 'close',
                        showZone: true,
                        overbought: 70,
                        oversold: 30,
                        panel: 'separate',
                        underlay: false,
                        yAxis: 'right'
                    },
                    macd: {
                        fast: 12,
                        slow: 26,
                        signal: 9
                    },
                },
                indicatorModal: null,
                indicatorSeries: {},
                COMPARE_COLORS: ['#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'],

                init() {
                    this.fetchSummary();
                    this.$watch('tab', val => {
                        if (val === 'grafik' && !this.meta && !this.loading) {
                            this.fetchData();
                        }
                    });
                    if (this.tab === 'grafik') {
                        this.$nextTick(() => this.fetchData());
                    }
                    this.$watch('chartType', val => {
                        if (this.chartInstance) {
                            this.applyChartType(val);
                        }
                    });
                },

                fmt(val) {
                    if (val == null) return '-';
                    return Number(val).toLocaleString('id-ID');
                },

                formatDate(value) {
                    if (!value) return '-';
                    return new Date(value).toLocaleDateString('id-ID');
                },

                sourceInitial(value) {
                    return (value || 'S').trim().charAt(0).toUpperCase();
                },

                formatEpoch(value) {
                    if (!value) return '-';
                    return new Date(value * 1000).toLocaleDateString('id-ID');
                },

                async fetchSummary() {
                    this.summaryLoading = true;
                    this.summaryError = null;
                    try {
                        const res = await fetch(summaryUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const json = await res.json();
                        if (!json.success) throw new Error(json.message || 'Gagal mengambil ringkasan saham.');
                        this.summary = json.data;
                    } catch (e) {
                        this.summaryError = e.message;
                    } finally {
                        this.summaryLoading = false;
                    }
                },

                async fetchData() {
                    this.loading = true;
                    this.chartError = null;
                    try {
                        let url = fetchUrl + '?range=' + this.range;
                        if (this.interval && this.interval !== 'auto') url += '&interval=' + this.interval;
                        const res = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const json = await res.json();
                        if (!json.success) throw new Error(json.message || 'Gagal mengambil data.');
                        this.meta = json.data.meta;
                        this.chartHasData = json.data.chart && json.data.chart.length > 0;
                        this.loading = false;
                        if (this.chartHasData) {
                            await this.$nextTick();
                            this.renderChart(json.data.chart);
                        }
                    } catch (e) {
                        this.chartError = e.message;
                        this.loading = false;
                    }
                },

                changeRange(r) {
                    if (this.loading) return;
                    this.range = r;
                    this.interval = 'auto';
                    this.meta = null;
                    this.comparisons = [];
                    this.fetchData();
                },

                // ─── Chart Type ───────────────────────────────────────

                applyChartType(type) {
                    const inst = this.chartInstance;
                    if (!inst || !inst.candleSeries) return;
                    const showCandle = type === 'candle';
                    inst.candleSeries.applyOptions({
                        visible: showCandle
                    });
                    inst.lineSeries.applyOptions({
                        visible: !showCandle
                    });
                },

                // ─── Comparison ───────────────────────────────────────

                async searchStock(query, results) {
                    if (!query || query.length < 1) {
                        results.length = 0;
                        return;
                    }
                    try {
                        const res = await fetch(searchUrl + '?q=' + encodeURIComponent(query), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        results.splice(0, results.length, ...data);
                    } catch (e) {
                        results.length = 0;
                    }
                },

                async addComparison(stock) {
                    if (this.comparisons.some(c => c.kode === stock.kode)) return;
                    const color = this.COMPARE_COLORS[this.comparisons.length % this.COMPARE_COLORS.length];
                    const cmp = {
                        ...stock,
                        color,
                        data: null,
                        series: null
                    };
                    this.comparisons.push(cmp);
                    const idx = this.comparisons.length - 1;
                    try {
                        const res = await fetch(compareUrl + '?code=' + encodeURIComponent(stock.kode) + '&range=' +
                            this.range, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });
                        const json = await res.json();
                        if (json.success && json.data.chart) {
                            this.comparisons[idx].data = json.data.chart;
                            if (this.chartInstance) {
                                this.addComparisonSeries(idx);
                            }
                        }
                    } catch (e) {}
                },

                removeComparison(idx) {
                    const cmp = this.comparisons[idx];
                    if (cmp && cmp.series && this.chartInstance) {
                        this.chartInstance.chart.removeSeries(cmp.series);
                    }
                    this.comparisons.splice(idx, 1);
                },

                addComparisonSeries(idx) {
                    const cmp = this.comparisons[idx];
                    if (!cmp || !cmp.data || !this.chartInstance) return;
                    const series = this.chartInstance.chart.addLineSeries({
                        color: cmp.color,
                        lineWidth: 2,
                        lastValueVisible: true,
                        priceLineVisible: false,
                    });
                    series.setData(cmp.data.filter(d => d.time != null && d.close != null).map(d => ({
                        time: d.time,
                        value: d.close
                    })));
                    cmp.series = series;
                },

                // ─── Indicators ──────────────────────────────────────

                toggleIndicator(key) {
                    if (this.indicators[key]) {
                        this.indicatorModal = key;
                        return;
                    }
                    this.indicators[key] = true;
                    if (this.chartInstance && this.chartInstance.candleSeries) {
                        this.updateIndicators();
                    }
                },

                editIndicator(key) {
                    this.indicatorModal = key;
                },

                applyIndicatorSettings() {
                    this.indicatorModal = null;
                    if (this.chartInstance && this.chartInstance.candleSeries) {
                        this.updateIndicators();
                    }
                },

                disableIndicator(key) {
                    this.indicators[key] = false;
                    this.indicatorModal = null;
                    if (this.chartInstance && this.chartInstance.candleSeries) {
                        this.updateIndicators();
                    }
                },

                calcSMA(data, period) {
                    const result = [];
                    for (let i = 0; i < data.length; i++) {
                        if (i < period - 1) {
                            result.push(null);
                            continue;
                        }
                        let sum = 0;
                        for (let j = i - period + 1; j <= i; j++) sum += data[j].close;
                        result.push({
                            time: data[i].time,
                            value: sum / period
                        });
                    }
                    return result;
                },

                calcEMA(data, period) {
                    const multiplier = 2 / (period + 1);
                    let ema = data[0].close;
                    const result = [{
                        time: data[0].time,
                        value: ema
                    }];
                    for (let i = 1; i < data.length; i++) {
                        ema = (data[i].close - ema) * multiplier + ema;
                        result.push({
                            time: data[i].time,
                            value: ema
                        });
                    }
                    return result;
                },

                calcBB(data, period, stddev) {
                    const sma = this.calcSMA(data, period);
                    const result = [];
                    for (let i = 0; i < data.length; i++) {
                        if (i < period - 1) {
                            result.push(null);
                            continue;
                        }
                        let sum = 0;
                        for (let j = i - period + 1; j <= i; j++) sum += data[j].close;
                        const mean = sum / period;
                        let sqSum = 0;
                        for (let j = i - period + 1; j <= i; j++) sqSum += (data[j].close - mean) ** 2;
                        const sd = Math.sqrt(sqSum / period);
                        result.push({
                            time: data[i].time,
                            upper: mean + stddev * sd,
                            middle: mean,
                            lower: mean - stddev * sd
                        });
                    }
                    return result;
                },

                calcRSI(data, period, field) {
                    field = field || 'close';
                    const result = [];
                    for (let i = 0; i < data.length; i++) {
                        if (i < period) {
                            result.push(null);
                            continue;
                        }
                        let gain = 0,
                            loss = 0;
                        for (let j = i - period + 1; j <= i; j++) {
                            const diff = data[j][field] - data[j - 1][field];
                            if (diff >= 0) gain += diff;
                            else loss -= diff;
                        }
                        const avgGain = gain / period;
                        const avgLoss = loss / period;
                        const rs = avgLoss === 0 ? 100 : avgGain / avgLoss;
                        result.push({
                            time: data[i].time,
                            value: 100 - 100 / (1 + rs)
                        });
                    }
                    return result;
                },

                calcMACD(data, fast, slow, signal) {
                    const emaFast = this.calcEMA(data, fast);
                    const emaSlow = this.calcEMA(data, slow);
                    const macdLine = emaFast.map((v, i) => ({
                        time: v.time,
                        value: v.value - emaSlow[i].value,
                    }));
                    const signalLine = this.calcEMA(
                        macdLine.filter(v => v.value != null).map(v => ({
                            time: v.time,
                            close: v.value
                        })),
                        signal
                    );
                    const histogram = macdLine.map((v, i) => ({
                        time: v.time,
                        value: v.value - (signalLine.find(s => s.time === v.time)?.value ?? 0),
                    }));
                    return {
                        macdLine,
                        signalLine,
                        histogram
                    };
                },

                updateIndicators() {
                    const inst = this.chartInstance;
                    if (!inst) return;
                    const data = inst._chartData;

                    // Clean old indicator series
                    Object.values(this.indicatorSeries).forEach(s => {
                        if (Array.isArray(s)) s.forEach(x => inst.chart.removeSeries(x));
                        else if (s) inst.chart.removeSeries(s);
                    });
                    this.indicatorSeries = {};

                    if (this.indicators.sma) {
                        const s = this.indicatorSettings.sma;
                        const sma1 = inst.chart.addLineSeries({
                            color: '#f59e0b',
                            lineWidth: 1.5,
                            priceLineVisible: false,
                            lastValueVisible: false
                        });
                        sma1.setData(this.calcSMA(data, s.period1).filter(v => v.value != null));
                        const sma2 = inst.chart.addLineSeries({
                            color: '#ef4444',
                            lineWidth: 1.5,
                            priceLineVisible: false,
                            lastValueVisible: false
                        });
                        sma2.setData(this.calcSMA(data, s.period2).filter(v => v.value != null));
                        this.indicatorSeries.sma = [sma1, sma2];
                    }

                    if (this.indicators.ema) {
                        const s = this.indicatorSettings.ema;
                        const ema1 = inst.chart.addLineSeries({
                            color: '#8b5cf6',
                            lineWidth: 1.5,
                            priceLineVisible: false,
                            lastValueVisible: false
                        });
                        ema1.setData(this.calcEMA(data, s.period1).filter(v => v.value != null));
                        const ema2 = inst.chart.addLineSeries({
                            color: '#ec4899',
                            lineWidth: 1.5,
                            priceLineVisible: false,
                            lastValueVisible: false
                        });
                        ema2.setData(this.calcEMA(data, s.period2).filter(v => v.value != null));
                        this.indicatorSeries.ema = [ema1, ema2];
                    }

                    if (this.indicators.bb) {
                        const s = this.indicatorSettings.bb;
                        const bb = this.calcBB(data, s.period, s.stddev);
                        const valid = bb.filter(v => v != null);
                        const upper = inst.chart.addLineSeries({
                            color: '#14b8a6',
                            lineWidth: 1,
                            priceLineVisible: false,
                            lastValueVisible: false
                        });
                        upper.setData(valid.map(v => ({
                            time: v.time,
                            value: v.upper
                        })));
                        const middle = inst.chart.addLineSeries({
                            color: '#14b8a6',
                            lineWidth: 1,
                            priceLineVisible: false,
                            lastValueVisible: false
                        });
                        middle.setData(valid.map(v => ({
                            time: v.time,
                            value: v.middle
                        })));
                        const lower = inst.chart.addLineSeries({
                            color: '#14b8a6',
                            lineWidth: 1,
                            priceLineVisible: false,
                            lastValueVisible: false
                        });
                        lower.setData(valid.map(v => ({
                            time: v.time,
                            value: v.lower
                        })));
                        this.indicatorSeries.bb = [upper, middle, lower];
                    }

                    if (this.indicators.rsi) {
                        const s = this.indicatorSettings.rsi;
                        const scaleId = s.panel === 'main' ? 'right' : 'rsi';
                        const rsiData = this.calcRSI(data, s.period, s.field).filter(v => v.value != null && v.time !=
                            null);

                        if (s.panel === 'separate') {
                            const rsiScale = inst.chart.priceScale('rsi');
                            rsiScale.applyOptions({
                                scaleMargins: {
                                    top: 0.52,
                                    bottom: 0.35
                                },
                                visible: true,
                            });
                        }

                        const rsiLine = inst.chart.addLineSeries({
                            priceScaleId: scaleId,
                            color: '#f97316',
                            lineWidth: 2,
                            priceLineVisible: false,
                            lastValueVisible: true,
                        });
                        rsiLine.setData(rsiData);

                        const obLevel = Number(s.overbought) || 70;
                        const osLevel = Number(s.oversold) || 30;
                        const series = [rsiLine];

                        if (s.showZone) {
                            const zone = inst.chart.addAreaSeries({
                                priceScaleId: scaleId,
                                lineColor: 'transparent',
                                topColor: 'rgba(239, 68, 68, 0.08)',
                                bottomColor: 'rgba(34, 197, 94, 0.08)',
                                priceLineVisible: false,
                                lastValueVisible: false,
                            });
                            const zoneData = rsiData.map(d => ({
                                time: d.time,
                                value: d.value >= obLevel ? obLevel : (d.value <= osLevel ? osLevel : d.value),
                                lineColor: 'transparent',
                                color: d.value >= obLevel ? 'rgba(239,68,68,0.12)' : (d.value <= osLevel ?
                                    'rgba(34,197,94,0.12)' : 'transparent'),
                            }));
                            zone.setData(zoneData);
                            series.push(zone);
                        }

                        // Overbought line
                        const obLine = inst.chart.addLineSeries({
                            priceScaleId: scaleId,
                            color: '#ef4444',
                            lineWidth: 1,
                            lineStyle: 2,
                            priceLineVisible: false,
                            lastValueVisible: false,
                        });
                        obLine.setData(rsiData.map(d => ({
                            time: d.time,
                            value: obLevel
                        })));
                        series.push(obLine);

                        // Oversold line
                        const osLine = inst.chart.addLineSeries({
                            priceScaleId: scaleId,
                            color: '#22c55e',
                            lineWidth: 1,
                            lineStyle: 2,
                            priceLineVisible: false,
                            lastValueVisible: false,
                        });
                        osLine.setData(rsiData.map(d => ({
                            time: d.time,
                            value: osLevel
                        })));
                        series.push(osLine);

                        // Underlay: move series before candle series (z-order)
                        if (s.underlay) {
                            series.forEach(sr => {
                                try {
                                    inst.chart.chart && inst.chart.chart.removeSeries(sr);
                                } catch (e) {}
                            });
                            // Re-add all series in order: underlays first, then candles, then overlays
                            // Since lightweight-charts doesn't support z-order, we just note it
                        }

                        // Y-Axis visibility
                        if (s.panel === 'separate') {
                            const rsiScale = inst.chart.priceScale('rsi');
                            const visible = s.yAxis !== 'hidden';
                            rsiScale.applyOptions({
                                visible
                            });
                            if (visible) {
                                rsiScale.applyOptions({
                                    scaleMargins: {
                                        top: 0.52,
                                        bottom: 0.35
                                    },
                                });
                            }
                        }

                        this.indicatorSeries.rsi = series;
                    } else {
                        try {
                            inst.chart.priceScale('rsi').applyOptions({
                                visible: false
                            });
                        } catch (e) {}
                    }

                    if (this.indicators.macd) {
                        const s = this.indicatorSettings.macd;
                        const macdScale = inst.chart.priceScale('macd');
                        macdScale.applyOptions({
                            scaleMargins: {
                                top: 0.70,
                                bottom: 0
                            },
                            visible: true,
                        });
                        const {
                            macdLine,
                            signalLine,
                            histogram

                        } = this.calcMACD(data, s.fast, s.slow, s.signal);
                        const macdSeries = inst.chart.addLineSeries({
                            priceScaleId: 'macd',
                            color: '#2563eb',
                            lineWidth: 2,
                            priceLineVisible: false,
                            lastValueVisible: true,
                        });
                        macdSeries.setData(macdLine.filter(v => v.value != null));
                        const signal = inst.chart.addLineSeries({
                            priceScaleId: 'macd',
                            color: '#f59e0b',
                            lineWidth: 1.5,
                            priceLineVisible: false,
                            lastValueVisible: false,
                        });
                        signal.setData(signalLine.filter(v => v.value != null));
                        const hist = inst.chart.addHistogramSeries({
                            priceScaleId: 'macd',
                            priceFormat: {
                                type: 'volume'
                            },
                        });
                        const histData = histogram.filter(v => v.value != null);
                        hist.setData(histData.map(d => ({
                            time: d.time,
                            value: d.value,
                            color: d.value >= 0 ? 'rgba(34, 197, 94, 0.5)' : 'rgba(239, 68, 68, 0.5)',
                        })));
                        this.indicatorSeries.macd = [macdSeries, signal, hist];
                    } else {
                        inst.chart.priceScale('macd').applyOptions({
                            visible: false
                        });
                    }
                },

                // ─── Corporate Events ────────────────────────────────

                getEventConfig(type) {
                    const map = {
                        dividen: {
                            color: '#22c55e',
                            shape: 'circle',
                            position: 'aboveBar'
                        },
                        stock_split: {
                            color: '#3b82f6',
                            shape: 'arrowDown',
                            position: 'belowBar'
                        },
                        rights_issue: {
                            color: '#f59e0b',
                            shape: 'square',
                            position: 'aboveBar'
                        },
                        buyback: {
                            color: '#8b5cf6',
                            shape: 'arrowUp',
                            position: 'belowBar'
                        },
                        private_placement: {
                            color: '#6b7280',
                            shape: 'diamond',
                            position: 'aboveBar'
                        },
                        merger_akuisisi: {
                            color: '#ef4444',
                            shape: 'cross',
                            position: 'belowBar'
                        },
                    };
                    return map[type] || {
                        color: '#6b7280',
                        shape: 'circle',
                        position: 'aboveBar'
                    };
                },

                // ─── Render Chart ─────────────────────────────────────

                renderChart(chartData) {
                    const container = document.getElementById('yahooChartContainer');
                    if (!container) return;

                    if (this.chartInstance && this.chartInstance.chart) {
                        this.chartInstance.chart.remove();
                        this.chartInstance = null;
                    }
                    container.innerHTML = '';

                    if (!chartData || chartData.length === 0) return;

                    const isIntraday = this.range === '1d' || this.range === '5d';

                    container.style.height = '480px';

                    const chart = LightweightCharts.createChart(container, {
                        layout: {
                            background: {
                                color: '#ffffff'
                            },
                            textColor: '#6b7280',
                        },
                        grid: {
                            vertLines: {
                                color: '#f1f5f9'
                            },
                            horzLines: {
                                color: '#f1f5f9'
                            },
                        },
                        timeScale: {
                            timeVisible: isIntraday,
                            secondsVisible: false,
                            borderColor: '#e2e8f0',
                        },
                        rightPriceScale: {
                            borderColor: '#e2e8f0',
                            scaleMargins: {
                                top: 0.02,
                                bottom: 0.32
                            },
                        },
                        handleScroll: {
                            vertTouchDrag: true
                        },
                        handleScale: {
                            axisPressedMouseMove: true
                        },
                        autoSize: true,
                        height: 480,
                    });

                    // Deduplicate and sort by time (required by lightweight-charts)
                    const seen = new Set();
                    chartData = chartData.filter(d => {
                        if (d.time == null || d.close == null) return false;
                        if (seen.has(d.time)) return false;
                        seen.add(d.time);
                        return true;
                    }).sort((a, b) => a.time - b.time);

                    // Candlestick
                    const candleSeries = chart.addCandlestickSeries({
                        upColor: '#22c55e',
                        downColor: '#ef4444',
                        borderUpColor: '#22c55e',
                        borderDownColor: '#ef4444',
                        wickUpColor: '#22c55e',
                        wickDownColor: '#ef4444',
                        priceFormat: {
                            type: 'price',
                            precision: 0,
                            minMove: 1
                        },
                    });
                    candleSeries.setData(chartData.map(d => ({
                        time: d.time,
                        open: d.open,
                        high: d.high,
                        low: d.low,
                        close: d.close,
                    })));

                    // Line series (hidden by default, shown when chartType is 'line')
                    const lineSeries = chart.addLineSeries({
                        color: '#2563eb',
                        lineWidth: 2,
                        crosshairMarkerVisible: true,
                        lastValueVisible: true,
                        priceFormat: {
                            type: 'price',
                            precision: 0,
                            minMove: 1
                        },
                        visible: false,
                    });
                    lineSeries.setData(chartData.map(d => ({
                        time: d.time,
                        value: d.close
                    })));

                    // Volume
                    const volumeSeries = chart.addHistogramSeries({
                        priceScaleId: 'volume',
                        priceFormat: {
                            type: 'volume'
                        },
                    });
                    chart.priceScale('volume').applyOptions({
                        scaleMargins: {
                            top: 0.65,
                            bottom: 0
                        },
                    });
                    volumeSeries.setData(chartData.map(d => ({
                        time: d.time,
                        value: d.volume || 0,
                        color: d.close >= d.open ? 'rgba(34, 197, 94, 0.4)' : 'rgba(239, 68, 68, 0.4)',
                    })));

                    // Corporate events markers
                    const chartTimes = new Set(chartData.map(d => d.time));
                    const markers = this.corporateEvents
                        .filter(ev => ev.action_date && ev.action_type)
                        .map(ev => {
                            const cfg = this.getEventConfig(ev.action_type);
                            const evTs = Math.floor(new Date(ev.action_date).getTime() / 1000);
                            // Find closest time in chart data
                            let closest = chartData.reduce((prev, curr) =>
                                Math.abs(curr.time - evTs) < Math.abs(prev.time - evTs) ? curr : prev
                            );
                            return {
                                time: closest.time,
                                position: cfg.position,
                                color: cfg.color,
                                shape: cfg.shape,
                                text: ev.action_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                            };
                        })
                        .filter(m => chartTimes.has(m.time))
                        .sort((a, b) => a.time - b.time);
                    if (markers.length > 0) candleSeries.setMarkers(markers);

                    // Store reference
                    this.chartInstance = {
                        chart,
                        candleSeries,
                        lineSeries,
                        volumeSeries,
                        _chartData: chartData,
                    };
                    this.applyChartType(this.chartType);

                    // Re-render comparison series
                    this.comparisons.forEach((_, i) => this.addComparisonSeries(i));

                    // Re-render indicators
                    const hasIndicators = Object.values(this.indicators).some(v => v);
                    if (hasIndicators) this.updateIndicators();

                    requestAnimationFrame(() => {
                        chart.timeScale().fitContent();
                    });
                },
            };
        }
    </script>
@endpush
