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

    <div x-data="stockDetail(@js($activeTab), @js(route($routePrefix . '.saham.fetch-yahoo', $stock)), @js(route($routePrefix . '.saham.fetch-summary', $stock)))" x-init="init()" class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <a href="{{ route($routePrefix . '.saham.index') }}" class="text-sm text-muted hover:text-primary">← Daftar
                    Saham</a>
                <h1 class="text-2xl font-bold text-primary mt-2">{{ $stock->nama }}</h1>
                <p class="text-sm text-muted">{{ $stock->kode }} · {{ $stock->sektor ?: '-' }}</p>
            </div>
            <div class="bg-white rounded-xl border border-line px-5 py-3 text-right">
                <p class="text-xs text-muted">Harga Terakhir</p>
                <p class="text-xl font-bold text-primary">
                    {{ $stock->harga_terbaru ? 'Rp' . $fmt($stock->harga_terbaru) : '-' }}</p>
            </div>
        </div>

        @foreach (['success' => 'green', 'error' => 'red'] as $key => $color)
            @if (session($key))
                <div
                    class="px-4 py-3 rounded-xl text-sm border {{ $color === 'green' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' }}">
                    {{ session($key) }}
                </div>
            @endif
        @endforeach

        <div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
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

                {{-- Data dari Yahoo Finance (yfapi) --}}
                <div x-show="summary" class="space-y-4">
                    <h3 class="font-semibold text-primary">Data Yahoo Finance</h3>

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
                                x-text="summary?.stats?.trailingPE ? Number(summary.stats.trailingPE).toFixed(2) : '-'"></p>
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
            </div>

            <div x-show="tab==='grafik'" class="p-6 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap gap-2">
                        @foreach (['1D' => '1d', '5D' => '5d', '1M' => '1mo', '3M' => '3mo', '6M' => '6mo', '1Y' => '1y'] as $label => $val)
                            <button type="button" @click="changeRange('{{ $val }}')"
                                :class="range === '{{ $val }}' ? 'bg-primary text-white border-primary' :
                                    'border-line text-muted hover:text-primary'"
                                class="px-3 py-1.5 rounded-lg border text-xs font-semibold">{{ $label }}</button>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-2">
                        <span x-show="loading" class="text-xs text-muted flex items-center gap-1">
                            <span
                                class="inline-block w-3 h-3 border-2 border-primary border-t-transparent rounded-full animate-spin"></span>
                            Memuat data...
                        </span>
                        <form method="POST" action="{{ route($routePrefix . '.saham.sync-yahoo-prices', $stock) }}"
                            class="flex items-center gap-2">
                            @csrf
                            <select name="range" class="border-line rounded-lg text-xs py-1.5 px-2">
                                @foreach (['1mo' => '1 Bulan', '3mo' => '3 Bulan', '6mo' => '6 Bulan', '1y' => '1 Tahun', '2y' => '2 Tahun', '5y' => '5 Tahun'] as $v => $l)
                                    <option value="{{ $v }}" {{ $v === '1y' ? 'selected' : '' }}>
                                        {{ $l }}</option>
                                @endforeach
                            </select>
                            <button class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-semibold">Simpan ke
                                DB</button>
                        </form>
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

                {{-- Chart --}}
                <div x-show="meta && chartHasData" class="border border-line rounded-xl p-4">
                    <canvas id="yahooChartCanvas" height="100"></canvas>
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
                            <h3 class="font-semibold text-primary">Berita Yahoo Finance</h3>
                            <p class="text-xs text-muted mt-0.5">Berita terbaru dari sumber eksternal terverifikasi.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold"
                            x-text="`${(summary?.news ?? []).length} artikel`"></span>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <template x-for="news in (summary?.news ?? [])" :key="news.url || news.title">
                            <article
                                class="group relative overflow-hidden border border-line rounded-2xl bg-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition duration-200">
                                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary to-emerald-400"></div>
                                <div class="p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span
                                                class="w-9 h-9 shrink-0 rounded-xl bg-primary/10 text-primary grid place-items-center font-bold"
                                                x-text="sourceInitial(news.source)"></span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-primary truncate"
                                                    x-text="news.source || 'Sumber berita'"></p>
                                                <p class="text-[11px] text-muted" x-text="formatDate(news.publishedAt)"></p>
                                            </div>
                                        </div>
                                        <span class="shrink-0 px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-bold uppercase">
                                            Live API
                                        </span>
                                    </div>
                                    <h3 class="mt-4 text-base leading-snug font-bold text-primary">
                                        <a x-show="news.url" :href="news.url" target="_blank" rel="noopener noreferrer"
                                            class="group-hover:text-accent transition" x-text="news.title"></a>
                                        <span x-show="!news.url" x-text="news.title"></span>
                                    </h3>
                                    <div class="mt-5 pt-4 border-t border-line flex items-center justify-between">
                                        <span class="text-xs text-muted">Artikel eksternal</span>
                                        <a x-show="news.url" :href="news.url" target="_blank" rel="noopener noreferrer"
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
                        <form method="POST" action="{{ route($routePrefix . '.saham.generate-news', $stock) }}"
                            onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='Generating...'">
                            @csrf
                            <button
                                class="px-4 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition">
                                Generate AI Berita
                            </button>
                        </form>
                    </div>
                @endif
                @php
                    $newsSources = $stock->news
                        ->filter(fn($news) => filter_var($news->url, FILTER_VALIDATE_URL)
                            && in_array(parse_url($news->url, PHP_URL_SCHEME), ['http', 'https'], true))
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
                        $newsUrl = filter_var($news->url, FILTER_VALIDATE_URL)
                            && in_array(parse_url($news->url, PHP_URL_SCHEME), ['http', 'https'], true)
                            ? $news->url
                            : null;
                        $isAiGeneratedNews = str_contains($news->summary ?? '', 'Konten dibuat oleh AI.');
                    @endphp
                    <article
                        class="relative overflow-hidden border border-line rounded-2xl bg-white shadow-sm hover:shadow-md transition duration-200">
                        <div class="absolute inset-y-0 left-0 w-1 {{ $isAiGeneratedNews ? 'bg-amber-400' : 'bg-primary' }}"></div>
                        <div class="p-5 pl-6">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-9 h-9 rounded-xl {{ $isAiGeneratedNews ? 'bg-amber-50 text-amber-700' : 'bg-primary/10 text-primary' }} grid place-items-center font-bold">
                                        {{ strtoupper(mb_substr($news->source ?: 'S', 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="text-xs font-bold text-primary">{{ $news->source ?: 'Sumber berita' }}</p>
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
                            <p class="mt-3 text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $news->summary ?: '-' }}</p>
                            @if ($news->ai_summary)
                                <div class="mt-4 p-4 bg-blue-50 border border-blue-100 rounded-xl text-sm text-blue-800">
                                    <p class="text-[10px] uppercase font-bold tracking-wide text-blue-600 mb-1">AI Summary</p>
                                    <p>{{ $news->ai_summary }}</p>
                                </div>
                            @endif
                            <div class="mt-5 pt-4 border-t border-line flex items-center justify-between gap-3">
                                <span class="text-xs text-muted">{{ $isAiGeneratedNews ? 'Referensi media' : 'Sumber artikel' }}</span>
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
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        function stockDetail(activeTab, fetchUrl, summaryUrl) {
            return {
                tab: activeTab,
                loading: false,
                chartError: null,
                meta: null,
                range: '1d',
                chartInstance: null,
                chartHasData: false,
                summary: null,
                summaryLoading: false,
                summaryError: null,

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
                        const res = await fetch(fetchUrl + '?range=' + this.range, {
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
                    this.meta = null;
                    this.fetchData();
                },

                renderChart(chartData) {
                    const canvas = document.getElementById('yahooChartCanvas');
                    if (!canvas) return;
                    if (this.chartInstance) {
                        this.chartInstance.destroy();
                    }

                    const isIntraday = this.range === '1d' || this.range === '5d';
                    const labels = chartData.map(d => {
                        const dt = new Date(d.time * 1000);
                        return isIntraday ?
                            dt.toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            }) :
                            dt.toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'short',
                                year: '2-digit'
                            });
                    });
                    const closes = chartData.map(d => d.close);
                    const prevClose = this.meta?.previousClose ?? closes[0];
                    const color = (closes[closes.length - 1] ?? 0) >= prevClose ? '#22c55e' : '#ef4444';

                    this.chartInstance = new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Harga (IDR)',
                                data: closes,
                                borderColor: color,
                                backgroundColor: color + '18',
                                borderWidth: 2,
                                pointRadius: chartData.length > 60 ? 0 : 3,
                                fill: true,
                                tension: 0.1,
                            }]
                        },
                        options: {
                            responsive: true,
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => 'Rp ' + Number(ctx.parsed.y).toLocaleString('id-ID')
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: val => 'Rp ' + Number(val).toLocaleString('id-ID')
                                    }
                                }
                            }
                        }
                    });
                }
            };
        }
    </script>
@endpush
