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

    <div x-data="stockDetail(@js($activeTab), @js(route($routePrefix . '.saham.fetch-yahoo', $stock)))" x-init="init()" class="space-y-6">
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
                    :class="tab === 'info' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">Informasi Perusahaan</button>
                <button type="button" @click="tab='grafik'"
                    :class="tab === 'grafik' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">Grafik Saham</button>
                <button type="button" @click="tab='laporan'"
                    :class="tab === 'laporan' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">Laporan Keuangan</button>
                <button type="button" @click="tab='berita'"
                    :class="tab === 'berita' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">Berita Terkait</button>
                <button type="button" @click="tab='riset-broker'"
                    :class="tab === 'riset-broker' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">Riset Broker Terkait</button>
            </div>

            <div x-show="tab==='info'" class="p-6 space-y-6">
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

            <div x-show="tab==='grafik'" class="p-6 space-y-4">                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap gap-2">
                        @foreach (['1D'=>'1d','5D'=>'5d','1M'=>'1mo','3M'=>'3mo','6M'=>'6mo','1Y'=>'1y'] as $label => $val)
                            <button type="button" @click="changeRange('{{ $val }}')"
                                :class="range === '{{ $val }}' ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:text-primary'"
                                class="px-3 py-1.5 rounded-lg border text-xs font-semibold">{{ $label }}</button>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-2">
                        <span x-show="loading" class="text-xs text-muted flex items-center gap-1">
                            <span class="inline-block w-3 h-3 border-2 border-primary border-t-transparent rounded-full animate-spin"></span>
                            Memuat data...
                        </span>
                        <form method="POST" action="{{ route($routePrefix . '.saham.sync-yahoo-prices', $stock) }}" class="flex items-center gap-2">
                            @csrf
                            <select name="range" class="border-line rounded-lg text-xs py-1.5 px-2">
                                @foreach (['1mo'=>'1 Bulan','3mo'=>'3 Bulan','6mo'=>'6 Bulan','1y'=>'1 Tahun','2y'=>'2 Tahun','5y'=>'5 Tahun'] as $v => $l)
                                    <option value="{{ $v }}" {{ $v==='1y'?'selected':'' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                            <button class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-semibold">Simpan ke DB</button>
                        </form>
                    </div>
                </div>

                <div x-show="chartError" x-text="chartError" class="px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700" x-cloak></div>

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
                        <p class="font-bold text-primary text-xs" x-text="meta ? fmt(meta.regularMarketDayHigh) + ' / ' + fmt(meta.regularMarketDayLow) : '-'"></p>
                    </div>
                    <div class="border border-line rounded-xl p-3">
                        <p class="text-xs text-muted">52W High / Low</p>
                        <p class="font-bold text-primary text-xs" x-text="meta ? fmt(meta.fiftyTwoWeekHigh) + ' / ' + fmt(meta.fiftyTwoWeekLow) : '-'"></p>
                    </div>
                </div>

                {{-- Chart --}}
                <div x-show="meta && chartHasData" class="border border-line rounded-xl p-4">
                    <canvas id="yahooChartCanvas" height="100"></canvas>
                </div>
                <div x-show="meta && !chartHasData" class="px-4 py-3 rounded-xl text-sm bg-yellow-50 border border-yellow-200 text-yellow-700">
                    Data grafik tidak tersedia untuk range ini (mungkin di luar jam perdagangan).
                </div>

                <div x-show="!meta && !loading && !error" class="p-12 text-center text-muted border border-line rounded-xl">
                    Memuat grafik...
                </div>
            </div>

            <div x-show="tab==='laporan'" class="p-6">
                @forelse ($stock->financialReports as $report)
                    <div class="border border-line rounded-xl p-4 mb-4">
                        <h3 class="font-semibold text-primary">{{ $report->report_year }} · {{ $report->report_period }}
                        </h3>
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
                @empty
                    <div class="p-12 text-center text-muted border border-line rounded-xl">Data laporan keuangan belum
                        tersedia.</div>
                @endforelse
            </div>

            <div x-show="tab==='berita'" class="p-6 space-y-4">
                <form method="POST" action="{{ route($routePrefix . '.saham.summarize-news', $stock) }}">@csrf
                    <button class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold">Generate AI
                        Summary</button>
                </form>
                @forelse ($stock->news as $news)
                    <div class="border border-line rounded-xl p-4 text-sm">
                        <h3 class="font-semibold text-primary">{{ $news->title }}</h3>
                        <p class="text-xs text-muted mt-1">{{ $news->source ?: '-' }} ·
                            {{ optional($news->published_at)->format('d/m/Y') ?: '-' }}</p>
                        <p class="mt-3">{{ $news->summary ?: '-' }}</p>
                        @if ($news->ai_summary)
                            <p class="mt-3 p-3 bg-blue-50 rounded-lg text-blue-800">{{ $news->ai_summary }}</p>
                        @endif
                        @if ($news->url)
                            <a href="{{ $news->url }}" target="_blank"
                                class="inline-block mt-3 text-primary font-semibold">Buka Link</a>
                        @endif
                    </div>
                @empty
                    <div class="p-12 text-center text-muted border border-line rounded-xl">Berita terkait belum tersedia.
                    </div>
                @endforelse
            </div>

            <div x-show="tab==='riset-broker'" class="p-6 space-y-5">
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
function stockDetail(activeTab, fetchUrl) {
    return {
        tab: activeTab,
        loading: false,
        chartError: null,
        meta: null,
        range: '1d',
        chartInstance: null,
        chartHasData: false,

        init() {
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

        async fetchData() {
            this.loading = true;
            this.chartError = null;
            try {
                const res = await fetch(fetchUrl + '?range=' + this.range, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
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
            } catch(e) {
                this.chartError = e.message;
                this.loading = false;
            }
        },

        changeRange(r) {
            this.range = r;
            this.meta = null;
            this.fetchData();
        },

        renderChart(chartData) {
            const canvas = document.getElementById('yahooChartCanvas');
            if (!canvas) return;
            if (this.chartInstance) { this.chartInstance.destroy(); }

            const isIntraday = this.range === '1d' || this.range === '5d';
            const labels = chartData.map(d => {
                const dt = new Date(d.time * 1000);
                return isIntraday
                    ? dt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                    : dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: '2-digit' });
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
                    interaction: { intersect: false, mode: 'index' },
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => 'Rp ' + Number(ctx.parsed.y).toLocaleString('id-ID') } }
                    },
                    scales: {
                        y: { ticks: { callback: val => 'Rp ' + Number(val).toLocaleString('id-ID') } }
                    }
                }
            });
        }
    };
}
</script>
@endpush
