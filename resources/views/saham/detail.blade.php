@extends($layout)

@section('title', $stock->kode . ' - Detail Saham')

@section('content')
@php
    $profile = $stock->profile;
    $activeTab = session('active_tab', request('tab', 'info'));
    $fmt = fn ($value) => filled($value) ? number_format((float) $value, 0, ',', '.') : '-';
    $actionLabels = [
        'dividen' => 'Dividen',
        'stock_split' => 'Stock Split',
        'rights_issue' => 'Rights Issue',
        'buyback' => 'Buyback',
        'private_placement' => 'Private Placement',
        'merger_akuisisi' => 'Merger Akuisisi',
    ];
@endphp

<div x-data="{ tab: @js($activeTab) }" class="space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <a href="{{ route($routePrefix . '.saham.index') }}" class="text-sm text-muted hover:text-primary">← Daftar Saham</a>
            <h1 class="text-2xl font-bold text-primary mt-2">{{ $stock->nama }}</h1>
            <p class="text-sm text-muted">{{ $stock->kode }} · {{ $stock->sektor ?: '-' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-line px-5 py-3 text-right">
            <p class="text-xs text-muted">Harga Terakhir</p>
            <p class="text-xl font-bold text-primary">{{ $stock->harga_terbaru ? 'Rp' . $fmt($stock->harga_terbaru) : '-' }}</p>
        </div>
    </div>

    @foreach (['success' => 'green', 'error' => 'red'] as $key => $color)
        @if (session($key))
            <div class="px-4 py-3 rounded-xl text-sm border {{ $color === 'green' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' }}">
                {{ session($key) }}
            </div>
        @endif
    @endforeach

    <div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
        <div class="flex overflow-x-auto border-b border-line">
            @foreach ([
                'info' => 'Informasi Perusahaan',
                'grafik' => 'Grafik Saham',
                'laporan' => 'Laporan Keuangan',
                'berita' => 'Berita Terkait',
                'riset-broker' => 'Riset Broker Terkait',
            ] as $key => $label)
                <button type="button" @click="tab='{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
                    class="px-5 py-3.5 text-sm whitespace-nowrap transition">{{ $label }}</button>
            @endforeach
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
                <p class="text-gray-700 leading-relaxed">{{ $profile->description ?? 'Deskripsi perusahaan belum tersedia.' }}</p>
            </div>

            <div>
                <h3 class="font-semibold text-primary mb-3">Aksi Korporasi</h3>
                <div class="grid md:grid-cols-2 gap-3">
                    @foreach ($actionLabels as $type => $label)
                        @php $items = $stock->corporateActions->where('action_type', $type); @endphp
                        <div class="border border-line rounded-xl p-4">
                            <p class="font-semibold text-sm text-primary">{{ $label }}</p>
                            @forelse ($items as $action)
                                <p class="text-sm mt-2">{{ $action->action_date->format('d/m/Y') }} · {{ $action->description }}</p>
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
                    @foreach (['1D','1W','1M','3M','6M','YTD','1Y'] as $tf)
                        <a href="{{ route($routePrefix . '.saham.show', [$stock, 'timeframe' => $tf, 'tab' => 'grafik']) }}"
                            class="px-3 py-1.5 rounded-lg border text-xs font-semibold {{ $timeframe === $tf ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:text-primary' }}">{{ $tf }}</a>
                    @endforeach
                </div>
                <form method="POST" action="{{ route($routePrefix . '.saham.sync-yahoo-prices', $stock) }}" class="flex items-center gap-2">
                    @csrf
                    <select name="range" class="border-line rounded-lg text-xs">
                        @foreach (['1mo' => '1 Bulan', '3mo' => '3 Bulan', '6mo' => '6 Bulan', 'ytd' => 'YTD', '1y' => '1 Tahun', '2y' => '2 Tahun', '5y' => '5 Tahun'] as $value => $label)
                            <option value="{{ $value }}" {{ $value === '1y' ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="px-3 py-2 bg-primary text-white rounded-lg text-xs font-semibold">Sync Yahoo</button>
                </form>
            </div>
            @if ($prices->isEmpty())
                <div class="p-12 text-center text-muted border border-line rounded-xl">
                    Data grafik saham belum tersedia. Klik <span class="font-semibold text-primary">Sync Yahoo</span> untuk mengambil data harga.
                </div>
            @else
                <div class="overflow-x-auto border border-line rounded-xl">
                    <table class="w-full text-sm">
                        <thead class="bg-[#f8fafc] text-muted text-xs uppercase">
                            <tr><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3 text-right">Open</th><th class="px-4 py-3 text-right">High</th><th class="px-4 py-3 text-right">Low</th><th class="px-4 py-3 text-right">Close</th><th class="px-4 py-3 text-right">Volume</th></tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($prices as $price)
                                <tr><td class="px-4 py-3">{{ $price['tanggal'] }}</td><td class="px-4 py-3 text-right">{{ $fmt($price['open']) }}</td><td class="px-4 py-3 text-right">{{ $fmt($price['high']) }}</td><td class="px-4 py-3 text-right">{{ $fmt($price['low']) }}</td><td class="px-4 py-3 text-right font-semibold">{{ $fmt($price['close']) }}</td><td class="px-4 py-3 text-right">{{ filled($price['volume']) ? number_format($price['volume'], 0, ',', '.') : '-' }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div x-show="tab==='laporan'" class="p-6">
            @forelse ($stock->financialReports as $report)
                <div class="border border-line rounded-xl p-4 mb-4">
                    <h3 class="font-semibold text-primary">{{ $report->report_year }} · {{ $report->report_period }}</h3>
                    <div class="grid md:grid-cols-3 gap-4 mt-4 text-sm">
                        <div><p class="font-semibold mb-2">Neraca</p><p>Total Asset: {{ $fmt($report->total_asset) }}</p><p>Total Liabilitas: {{ $fmt($report->total_liabilities) }}</p><p>Total Ekuitas: {{ $fmt($report->total_equity) }}</p></div>
                        <div><p class="font-semibold mb-2">Laba Rugi</p><p>Pendapatan: {{ $fmt($report->revenue) }}</p><p>Laba Operasional: {{ $fmt($report->operating_income) }}</p><p>Laba Bersih: {{ $fmt($report->net_income) }}</p></div>
                        <div><p class="font-semibold mb-2">Arus Kas</p><p>CFO: {{ $fmt($report->cfo) }}</p><p>CFI: {{ $fmt($report->cfi) }}</p><p>CFF: {{ $fmt($report->cff) }}</p></div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-muted border border-line rounded-xl">Data laporan keuangan belum tersedia.</div>
            @endforelse
        </div>

        <div x-show="tab==='berita'" class="p-6 space-y-4">
            <form method="POST" action="{{ route($routePrefix . '.saham.summarize-news', $stock) }}">@csrf
                <button class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold">Generate AI Summary</button>
            </form>
            @forelse ($stock->news as $news)
                <div class="border border-line rounded-xl p-4 text-sm">
                    <h3 class="font-semibold text-primary">{{ $news->title }}</h3>
                    <p class="text-xs text-muted mt-1">{{ $news->source ?: '-' }} · {{ optional($news->published_at)->format('d/m/Y') ?: '-' }}</p>
                    <p class="mt-3">{{ $news->summary ?: '-' }}</p>
                    @if ($news->ai_summary)<p class="mt-3 p-3 bg-blue-50 rounded-lg text-blue-800">{{ $news->ai_summary }}</p>@endif
                    @if ($news->url)<a href="{{ $news->url }}" target="_blank" class="inline-block mt-3 text-primary font-semibold">Buka Link</a>@endif
                </div>
            @empty
                <div class="p-12 text-center text-muted border border-line rounded-xl">Berita terkait belum tersedia.</div>
            @endforelse
        </div>

        <div x-show="tab==='riset-broker'" class="p-6 space-y-5">
            <div class="grid md:grid-cols-4 gap-3 text-sm">
                <div class="border border-line rounded-xl p-4"><p class="text-xs text-muted">Target Tertinggi</p><p class="font-bold text-primary">{{ $fmt($consensus['highest']) }}</p></div>
                <div class="border border-line rounded-xl p-4"><p class="text-xs text-muted">Target Terendah</p><p class="font-bold text-primary">{{ $fmt($consensus['lowest']) }}</p></div>
                <div class="border border-line rounded-xl p-4"><p class="text-xs text-muted">Rata-rata Target</p><p class="font-bold text-primary">{{ $fmt($consensus['average']) }}</p></div>
                <div class="border border-line rounded-xl p-4"><p class="text-xs text-muted">Potensi Upside</p><p class="font-bold text-primary">{{ filled($consensus['upside']) ? number_format($consensus['upside'], 2) . '%' : '-' }}</p></div>
            </div>
            <form method="POST" action="{{ route($routePrefix . '.saham.summarize-broker-research', $stock) }}">@csrf
                <button class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold">Generate AI Summary</button>
            </form>
            @forelse ($stock->brokerResearches as $research)
                <div class="border border-line rounded-xl p-4 text-sm">
                    <div class="flex flex-wrap justify-between gap-3">
                        <div><h3 class="font-semibold text-primary">{{ $research->broker_name }}</h3><p class="text-xs text-muted">{{ optional($research->research_date)->format('d/m/Y') ?: '-' }} · Rating {{ $research->rating ?: '-' }} · TP {{ $fmt($research->target_price) }}</p></div>
                        <div class="flex gap-2">
                            @if ($research->pdf_file)
                                <a target="_blank" href="{{ route($routePrefix . '.saham.broker-research.view', [$stock, $research]) }}" class="px-3 py-1.5 border border-line rounded-lg">Preview PDF</a>
                                <a href="{{ route($routePrefix . '.saham.broker-research.download', [$stock, $research]) }}" class="px-3 py-1.5 border border-line rounded-lg">Download PDF</a>
                            @endif
                        </div>
                    </div>
                    @if ($research->ai_summary)<p class="mt-3 p-3 bg-blue-50 rounded-lg text-blue-800">{{ $research->ai_summary }}</p>@endif
                </div>
            @empty
                @if ($legacyResearches->isEmpty())
                    <div class="p-12 text-center text-muted border border-line rounded-xl">Riset broker terkait belum tersedia.</div>
                @endif
            @endforelse
            @foreach ($legacyResearches as $item)
                <div class="border border-line rounded-xl p-4 text-sm">
                    <h3 class="font-semibold text-primary">{{ $item['document']->broker }}</h3>
                    <p class="text-xs text-muted">{{ $item['document']->created_at->format('d/m/Y') }} · {{ $item['document']->original_name }}</p>
                    <div class="flex gap-2 mt-3">
                        <a target="_blank" href="{{ route($routePrefix . '.analisa-saham.riset-broker.view', [$item['analysis'], $item['document']]) }}" class="px-3 py-1.5 border border-line rounded-lg">Preview PDF</a>
                        <a href="{{ route($routePrefix . '.analisa-saham.riset-broker.download', [$item['analysis'], $item['document']]) }}" class="px-3 py-1.5 border border-line rounded-lg">Download PDF</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
