@extends('layouts.admin')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">YTM Normal Curve</h1>
            <p class="page-sub">Kelola kurva YTM normal berdasarkan rating dan tenor</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('admin.ytm-normal-curve.import') }}" enctype="multipart/form-data"
                onsubmit="return confirm('Import data YTM Normal Curve dari file? Data dengan kombinasi rating+tenor yang sama akan diupdate.')">
                @csrf
                <div class="flex items-center gap-2">
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                        class="block text-sm border border-line rounded-lg file:mr-2 file:py-1.5 file:px-3 file:border-0 file:text-sm file:font-medium file:bg-[#f8fafc] hover:file:bg-gray-100">
                    <button type="submit"
                         class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">Import</button>
                    <a href="{{ route('admin.ytm-normal-curve.template') }}"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">Template</a>
                </div>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Form Tambah --}}
    <div class="bg-white rounded-xl border border-line p-6">
        <h3 class="font-semibold text-primary mb-4">Tambah Data YTM Normal</h3>
        <form method="POST" action="{{ route('admin.ytm-normal-curve.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                <select name="rating_id" required
                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                    <option value="">Pilih Rating</option>
                    @foreach ($ratings as $r)
                        <option value="{{ $r->id }}" {{ old('rating_id') == $r->id ? 'selected' : '' }}>
                            {{ $r->kode }} - {{ $r->nama }}
                        </option>
                    @endforeach
                </select>
                @error('rating_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tenor (Bulan)</label>
                <input type="number" name="tenor_bulan"
                    value="{{ old('tenor_bulan') }}" required min="1"
                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                @error('tenor_bulan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">YTM Normal (%)</label>
                <input type="number" name="ytm_normal" step="0.0001"
                    value="{{ old('ytm_normal') }}" required min="0" max="100"
                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                @error('ytm_normal') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="w-full px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/90 transition">
                    Tambah
                </button>
            </div>
        </form>
    </div>

    {{-- Grafik Rating - YTM --}}
    <div class="bg-white rounded-xl border border-line p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h3 class="font-semibold text-primary">Grafik Rating - YTM</h3>
                <p class="text-xs text-muted mt-1">Perbandingan YTM Normal berdasarkan rating dan tenor</p>
            </div>
            <div id="ytmChartStatus" class="text-xs text-muted"></div>
        </div>

        <div id="ytmChartEmpty" class="hidden min-h-[280px] grid place-items-center text-center text-muted text-sm">
            Belum tersedia data YTM Normal Curve.
        </div>
        <div id="ytmRatingChart" class="min-h-[450px]"></div>
    </div>

    {{-- Daftar YTM Normal Curve --}}
    @forelse ($grouped as $label => $curves)
        <div class="table-card">
            <div class="px-4 py-3 bg-[#f8fafc] border-b border-line font-semibold text-primary text-sm">
                {{ $label }}
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-line">
                        <th class="text-left px-4 py-2.5 font-semibold text-primary">Tenor (Bulan)</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-primary">YTM Normal (%)</th>
                        <th class="text-center px-4 py-2.5 font-semibold text-primary">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($curves as $curve)
                        <tr class="border-b border-line/50 hover:bg-[#f8fafc]">
                            <td class="px-4 py-2.5">{{ $curve->tenor_bulan }} bln ({{ round($curve->tenor_bulan / 12, 1) }} thn)</td>
                            <td class="px-4 py-2.5 font-medium">{{ number_format($curve->ytm_normal, 4) }}%</td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.ytm-normal-curve.edit', $curve) }}"
                                        class="text-xs px-3 py-1.5 border border-line rounded-lg hover:bg-[#f8fafc] transition">Edit</a>
                                    <form method="POST" action="{{ route('admin.ytm-normal-curve.destroy', $curve) }}"
                                        onsubmit="return confirm('Hapus data ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-xs px-3 py-1.5 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-line p-8 text-center text-muted text-sm">
            Belum ada data YTM Normal Curve.
        </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const chartEl = document.getElementById('ytmRatingChart');
    const emptyEl = document.getElementById('ytmChartEmpty');
    const statusEl = document.getElementById('ytmChartStatus');
    const chartDataUrl = @json(route('admin.ytm-normal-curve.chart-data'));
    let chart = null;

    const chartOptions = {
        chart: {
            type: 'line',
            height: 500,
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: false,
                    reset: true
                },
                export: {
                    png: {
                        filename: 'grafik-rating-ytm'
                    }
                }
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 500
            }
        },
        series: [],
        xaxis: {
            categories: [],
            title: {
                text: 'Tenor (Bulan)'
            },
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'YTM Normal (%)'
            },
            labels: {
                formatter: (value) => Number(value).toFixed(2) + '%',
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                }
            }
        },
        stroke: {
            curve: 'smooth',
            width: 2.5
        },
        markers: {
            size: 4,
            strokeWidth: 2,
            hover: {
                size: 6
            }
        },
        grid: {
            show: true,
            borderColor: '#e2e8f0',
            strokeDashArray: 3
        },
        legend: {
            show: true,
            position: 'bottom',
            horizontalAlign: 'center',
            markers: {
                width: 10,
                height: 10,
                radius: 2
            },
            itemMargin: {
                horizontal: 10,
                vertical: 6
            }
        },
        tooltip: {
            shared: false,
            intersect: true,
            custom: ({ series, seriesIndex, dataPointIndex, w }) => {
                const rating = w.config.series[seriesIndex].name;
                const tenor = w.config.xaxis.categories[dataPointIndex];
                const ytm = series[seriesIndex][dataPointIndex];

                return `
                    <div class="px-3 py-2 text-xs">
                        <div><span class="font-semibold">Rating</span> : ${rating}</div>
                        <div><span class="font-semibold">Tenor</span> : ${tenor} Bulan</div>
                        <div><span class="font-semibold">YTM</span> : ${Number(ytm).toFixed(2)}%</div>
                    </div>
                `;
            }
        },
        colors: [
            '#047857', '#2563eb', '#dc2626', '#9333ea', '#ea580c', '#0891b2',
            '#4f46e5', '#65a30d', '#db2777', '#ca8a04', '#0f766e', '#7c3aed',
            '#be123c', '#0284c7', '#16a34a', '#c2410c', '#334155', '#a21caf'
        ],
        noData: {
            text: 'Memuat data grafik...'
        },
        responsive: [
            {
                breakpoint: 640,
                options: {
                    chart: {
                        height: 450
                    },
                    legend: {
                        position: 'bottom',
                        fontSize: '11px'
                    },
                    markers: {
                        size: 3
                    }
                }
            }
        ]
    };

    const setEmptyState = (isEmpty) => {
        emptyEl.classList.toggle('hidden', !isEmpty);
        chartEl.classList.toggle('hidden', isEmpty);
    };

    const renderChart = async () => {
        try {
            const response = await fetch(chartDataUrl, {
                headers: {
                    Accept: 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Gagal memuat data grafik.');
            }

            const data = await response.json();
            const hasData = data.series?.some((item) => item.data?.some((value) => value !== null));
            setEmptyState(!hasData);

            if (!hasData) {
                statusEl.textContent = '';
                return;
            }

            if (!chart) {
                chart = new ApexCharts(chartEl, {
                    ...chartOptions,
                    series: data.series,
                    xaxis: {
                        ...chartOptions.xaxis,
                        categories: data.categories
                    }
                });
                await chart.render();
            } else {
                await chart.updateOptions({
                    xaxis: {
                        ...chartOptions.xaxis,
                        categories: data.categories
                    }
                }, false, false);
                await chart.updateSeries(data.series, true);
            }

            statusEl.textContent = 'Grafik diperbarui otomatis';
        } catch (error) {
            statusEl.textContent = error.message;
        }
    };

    renderChart();
    setInterval(renderChart, 15000);
});
</script>
@endpush
