@extends('layouts.admin')

@section('title', $manager->name . ' - InvestaPremier')

@section('content')
<div x-data="{ tab: 'detail' }">

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-muted mb-3">
        <a href="{{ route('admin.investment-managers.index') }}" class="hover:text-primary transition">Manajer Investasi</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary font-medium">{{ $manager->name }}</span>
    </div>
    <h1 class="text-2xl font-bold text-primary">{{ $manager->name }}</h1>
</div>

{{-- Tabs --}}
<div class="flex items-center gap-1 mb-6 border-b border-line">
    <button @click="tab = 'detail'" :class="tab === 'detail' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition">
        Detail
    </button>
    <button @click="tab = 'produk'" :class="tab === 'produk' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition">
        Produk
    </button>
    <button @click="tab = 'grafik'" :class="tab === 'grafik' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition">
        Grafik
    </button>
</div>

{{-- Tab: Detail --}}
<div x-show="tab === 'detail'" x-cloak>
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Informasi Manajer Investasi
            </h2>
        </div>
        <div class="divide-y divide-line">
            @if($manager->kode_ojk)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Kode OJK</span>
                <span class="text-sm">{{ $manager->kode_ojk }}</span>
            </div>
            @endif
            @if($manager->kode_mi)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Kode MI</span>
                <span class="text-sm">{{ $manager->kode_mi }}</span>
            </div>
            @endif
            @if($manager->address)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Alamat</span>
                <span class="text-sm">{{ $manager->address }}</span>
            </div>
            @endif
            @if($manager->phone)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Nomor Telepon</span>
                <span class="text-sm">{{ $manager->phone }}</span>
            </div>
            @endif
            @if($manager->email)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Email</span>
                <span class="text-sm"><a href="mailto:{{ $manager->email }}" class="text-accent hover:underline">{{ $manager->email }}</a></span>
            </div>
            @endif
            @if($manager->website)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Website</span>
                <span class="text-sm"><a href="{{ $manager->website }}" target="_blank" class="text-accent hover:underline">{{ $manager->website }}</a></span>
            </div>
            @endif
            @if($manager->commissioner_president)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Komisaris Utama</span>
                <span class="text-sm">{{ $manager->commissioner_president }}</span>
            </div>
            @endif
            @if($manager->commissioners)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Komisaris</span>
                <span class="text-sm whitespace-pre-line">{{ $manager->commissioners }}</span>
            </div>
            @endif
            @if($manager->director_president)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Direktur Utama</span>
                <span class="text-sm">{{ $manager->director_president }}</span>
            </div>
            @endif
            @if($manager->directors)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Direktur</span>
                <span class="text-sm whitespace-pre-line">{{ $manager->directors }}</span>
            </div>
            @endif
            @if($manager->shareholders)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Pemegang Saham</span>
                <span class="text-sm whitespace-pre-line">{{ $manager->shareholders }}</span>
            </div>
            @endif
            @if($manager->last_updated_at)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Tanggal Terakhir Update</span>
                <span class="text-sm">{{ $manager->last_updated_at->format('d M Y') }}</span>
            </div>
            @endif
            @if($manager->description)
            <div class="px-6 py-3.5 flex items-start gap-4">
                <span class="text-xs font-semibold text-muted w-40 shrink-0">Deskripsi</span>
                <span class="text-sm whitespace-pre-line">{{ $manager->description }}</span>
            </div>
            @endif
            @if(!$manager->kode_ojk && !$manager->address && !$manager->phone && !$manager->email && !$manager->website && !$manager->commissioner_president && !$manager->commissioners && !$manager->director_president && !$manager->directors && !$manager->shareholders && !$manager->last_updated_at && !$manager->description)
            <div class="py-12 text-center text-muted text-sm">
                <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Informasi detail manajer investasi belum tersedia.
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Tab: Produk --}}
<div x-show="tab === 'produk'" x-cloak>
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Produk Reksa Dana
            </h2>
        </div>
        @if($manager->funds->isEmpty())
        <div class="py-12 text-center text-muted text-sm">
            <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            Produk reksa dana belum tersedia.
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                        <th class="px-4 py-3 font-semibold">Kode Reksa Dana</th>
                        <th class="px-4 py-3 font-semibold">Nama Reksa Dana</th>
                        <th class="px-4 py-3 font-semibold">Jenis</th>
                        <th class="px-4 py-3 font-semibold">Kategori</th>
                        <th class="px-4 py-3 font-semibold text-right">NAB/UP</th>
                        <th class="px-4 py-3 font-semibold text-right">AUM</th>
                        <th class="px-4 py-3 font-semibold">Tanggal Data</th>
                        <th class="px-4 py-3 font-semibold text-right w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($manager->funds as $fund)
                    <tr class="hover:bg-[#f8fafc] transition-colors">
                        <td class="px-4 py-3 text-xs font-mono">{{ $fund->kode_reksa_dana ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs font-semibold">{{ $fund->nama_reksa_dana ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $fund->jenis ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $fund->kategori_label ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $fund->nab_per_unit ? number_format($fund->nab_per_unit, 4, ',', '.') : '-' }}</td>
                        <td class="px-4 py-3 text-xs text-right tabular-nums text-primary font-semibold">{{ '-' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $fund->tanggal_nab ? $fund->tanggal_nab->format('d M Y') : '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.daftar-reksa-dana.index') }}" class="p-1.5 rounded-lg text-muted hover:text-accent hover:bg-accent/5 transition inline-block" title="Lihat detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Tab: Grafik --}}
<div x-show="tab === 'grafik'" x-cloak>
    <div class="mb-4">
        <form method="GET" action="{{ route('admin.investment-managers.show', $manager) }}">
            <div class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="tab" value="grafik">
                <select name="chart_tahun" class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $th)
                    <option value="{{ $th }}" {{ request('chart_tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                    @endforeach
                </select>
                <select name="chart_kuartal" class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                    <option value="">Semua Kuartal</option>
                    @foreach([1,2,3,4] as $q)
                    <option value="{{ $q }}" {{ request('chart_kuartal') == $q ? 'selected' : '' }}>Q{{ $q }}</option>
                    @endforeach
                </select>
                <select name="chart_mata_uang" class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                    <option value="">Semua Mata Uang</option>
                    <option value="IDR" {{ request('chart_mata_uang') == 'IDR' ? 'selected' : '' }}>IDR</option>
                    <option value="USD" {{ request('chart_mata_uang') == 'USD' ? 'selected' : '' }}>USD</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Terapkan</button>
                @if(request()->anyFilled(['chart_tahun','chart_kuartal','chart_mata_uang']))
                <a href="{{ route('admin.investment-managers.show', $manager) }}" class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>
                @endif
            </div>
        </form>
    </div>

    @if($chartPeriods->isEmpty())
    <div class="py-16 text-center text-muted bg-white rounded-2xl border border-line">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        <p class="font-medium">Data grafik belum tersedia.</p>
    </div>
    @else
    <div class="space-y-6">
        {{-- Chart AUM --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
            <h3 class="font-bold text-primary text-sm mb-4">AUM (Rp)</h3>
            <div style="height: 300px;">
                <canvas id="chartAum"></canvas>
            </div>
        </div>
        {{-- Chart Unit Penyertaan --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
            <h3 class="font-bold text-primary text-sm mb-4">Unit Penyertaan</h3>
            <div style="height: 300px;">
                <canvas id="chartUp"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = {!! json_encode($chartLabels) !!};
        const aumData = {!! json_encode($chartAum) !!};
        const upData = {!! json_encode($chartUp) !!};

        function fmt(v) { return v.toLocaleString('id-ID'); }

        const opts = {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) { return fmt(ctx.parsed.x); }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: true },
                    ticks: { callback: function(val) { return fmt(val); } }
                },
                y: {
                    grid: { display: false }
                }
            }
        };

        new Chart(document.getElementById('chartAum'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: aumData,
                    backgroundColor: 'rgba(37, 99, 235, 0.8)',
                    borderColor: '#2563eb',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: opts
        });

        new Chart(document.getElementById('chartUp'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: upData,
                    backgroundColor: 'rgba(5, 150, 105, 0.8)',
                    borderColor: '#059669',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: opts
        });
    });
    </script>
    @endif
</div>

</div>
@endsection
