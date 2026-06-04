@extends('layouts.admin')

@section('title', $manager->name . ' - InvestaPremier')

@section('content')
    <div x-data="{ tab: 'detail' }">

        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-muted mb-3">
                <a href="{{ route('admin.investment-managers.index') }}" class="hover:text-primary transition">Manajer
                    Investasi</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-primary font-medium">{{ $manager->name }}</span>
            </div>
            <h1 class="page-title">{{ $manager->name }}</h1>
        </div>

        {{-- Tabs --}}
        <div class="flex items-center gap-1 mb-6 border-b border-line">
            <button @click="tab = 'detail'"
                :class="tab === 'detail' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
                class="px-5 py-3 text-sm font-semibold border-b-2 transition">
                Detail
            </button>
            <button @click="tab = 'produk'"
                :class="tab === 'produk' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
                class="px-5 py-3 text-sm font-semibold border-b-2 transition">
                Produk
            </button>
            <button @click="tab = 'grafik'"
                :class="tab === 'grafik' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
                class="px-5 py-3 text-sm font-semibold border-b-2 transition">
                Grafik
            </button>
        </div>

        {{-- Tab: Detail --}}
        <div x-show="tab === 'detail'" x-cloak x-data="{
            reksaDanaId: '',
            tahun: '',
            loading: false,
            error: null,
            success: null,
            extractUrl: '{{ route('admin.investment-managers.extract-prospektus', $manager) }}',
            saveUrl: '{{ route('admin.investment-managers.save-prospektus', $manager) }}',
            csrfToken: '{{ csrf_token() }}',
            fundsData: {{ Js::from($fundsWithProspektus->map(fn($f) => ['id' => $f->id, 'nama' => $f->nama_reksa_dana, 'years' => $f->documents->pluck('ffs_year')->filter()->unique()->values()])) }},
            get filteredYears() {
                const fund = this.fundsData.find(f => f.id == this.reksaDanaId);
                return fund ? fund.years : [];
            },
            async extract() {
                if (!this.reksaDanaId || !this.tahun) return;
                this.loading = true;
                this.error = null;
                this.success = null;
                try {
                    const res = await fetch(this.extractUrl + '?reksa_dana_id=' + this.reksaDanaId + '&tahun=' + this.tahun, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const json = await res.json();
                    if (!res.ok) { this.error = json.error || 'Gagal mengekstrak.'; return; }
                    // langsung simpan
                    const form = new FormData();
                    form.append('_token', this.csrfToken);
                    Object.entries(json.data).forEach(([k, v]) => { if (v) form.append(k, v); });
                    const saveRes = await fetch(this.saveUrl, { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                    if (!saveRes.ok) { this.error = 'Ekstrak berhasil tapi gagal menyimpan.'; return; }
                    this.success = 'Data berhasil diekstrak dan disimpan. Refresh halaman untuk melihat perubahan.';
                } catch (e) { this.error = e.message; } finally { this.loading = false; }
            }
        }">

            {{-- Ekstrak dari Prospektus --}}
            <div class="bg-white rounded-2xl border border-line shadow-sm p-6 mb-6 space-y-4">
                <h2 class="font-bold text-primary text-sm">Ekstrak & Simpan Data dari Prospektus</h2>
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs text-muted mb-1">Reksa Dana</label>
                        <select x-model="reksaDanaId" @change="tahun=''"
                            class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 min-w-[280px]">
                            <option value="">-- Pilih Reksa Dana --</option>
                            @foreach ($fundsWithProspektus as $fund)
                                <option value="{{ $fund->id }}">{{ $fund->nama_reksa_dana }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-muted mb-1">Tahun</label>
                        <select x-model="tahun" :disabled="!reksaDanaId"
                            class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 disabled:opacity-50">
                            <option value="">-- Tahun --</option>
                            <template x-for="y in filteredYears" :key="y">
                                <option :value="y" x-text="y"></option>
                            </template>
                        </select>
                    </div>
                    <button @click="extract()" :disabled="!reksaDanaId || !tahun || loading"
                        class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition disabled:opacity-50 flex items-center gap-2">
                        <span x-show="loading"
                            class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-text="loading ? 'Memproses...' : 'Ekstrak & Simpan'"></span>
                    </button>
                </div>
                @if ($fundsWithProspektus->isEmpty())
                    <p class="text-sm text-muted">Belum ada reksa dana dengan prospektus. Upload di <a
                            href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs']) }}"
                            class="text-accent hover:underline">Daftar Reksa Dana</a>.</p>
                @endif
                <div x-show="error" x-text="error"
                    class="px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700"></div>
                <div x-show="success" x-text="success"
                    class="px-4 py-3 rounded-xl text-sm bg-green-50 border border-green-200 text-green-700"></div>
            </div>
            {{-- Informasi MI --}}
            <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
                    <h2 class="font-bold text-white text-sm">Informasi Manajer Investasi</h2>
                </div>
                <div class="divide-y divide-line">
                    @foreach ([
            'kode_ojk' => 'Kode OJK',
            'kode_mi' => 'Kode MI',
            'address' => 'Alamat',
            'phone' => 'Nomor Telepon',
            'email' => 'Email',
            'website' => 'Website',
            'commissioner_president' => 'Komisaris Utama',
            'commissioners' => 'Komisaris',
            'director_president' => 'Direktur Utama',
            'directors' => 'Direktur',
            'shareholders' => 'Pemegang Saham',
            'description' => 'Deskripsi',
            'last_updated_at' => 'Tanggal Update',
        ] as $field => $label)
                        <div class="px-6 py-3.5 flex items-start gap-4">
                            <span class="text-xs font-semibold text-muted w-40 shrink-0">{{ $label }}</span>
                            <span class="text-sm whitespace-pre-line">
                                @if ($field === 'email' && $manager->$field)
                                    <a href="mailto:{{ $manager->$field }}"
                                        class="text-accent hover:underline">{{ $manager->$field }}</a>
                                @elseif($field === 'website' && $manager->$field)
                                    <a href="{{ $manager->$field }}" target="_blank"
                                        class="text-accent hover:underline">{{ $manager->$field }}</a>
                                @elseif($field === 'last_updated_at' && $manager->$field)
                                    {{ $manager->$field->format('d M Y') }}
                                @else
                                    {{ $manager->$field ?: '-' }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>


        </div>

        {{-- Tab: Produk --}}
        <div x-show="tab === 'produk'" x-cloak>
            <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
                    <h2 class="font-bold text-white text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Produk Reksa Dana
                    </h2>
                </div>
                @if ($manager->funds->isEmpty())
                    <div class="py-12 text-center text-muted text-sm">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
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
                                @foreach ($manager->funds as $fund)
                                    <tr class="hover:bg-[#f8fafc] transition-colors">
                                        <td class="px-4 py-3 text-xs font-mono">{{ $fund->kode_reksa_dana ?? '-' }}</td>
                                        <td class="px-4 py-3 text-xs font-semibold">{{ $fund->nama_reksa_dana ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs">{{ $fund->jenis ?? '-' }}</td>
                                        <td class="px-4 py-3 text-xs">{{ $fund->kategori_label ?? '-' }}</td>
                                        <td class="px-4 py-3 text-xs text-right tabular-nums">
                                            {{ $fund->nab_per_unit ? number_format($fund->nab_per_unit, 4, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-right tabular-nums text-primary font-semibold">
                                            {{ '-' }}</td>
                                        <td class="px-4 py-3 text-xs">
                                            {{ $fund->tanggal_nab ? $fund->tanggal_nab->format('d M Y') : '-' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('admin.daftar-reksa-dana.index') }}"
                                                class="p-1.5 rounded-lg text-muted hover:text-accent hover:bg-accent/5 transition inline-block"
                                                title="Lihat detail">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
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
                        <select name="chart_tahun"
                            class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                            <option value="">Semua Tahun</option>
                            @foreach ($tahunList as $th)
                                <option value="{{ $th }}"
                                    {{ request('chart_tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                            @endforeach
                        </select>
                        <select name="chart_kuartal"
                            class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                            <option value="">Semua Kuartal</option>
                            @foreach ([1, 2, 3, 4] as $q)
                                <option value="{{ $q }}"
                                    {{ request('chart_kuartal') == $q ? 'selected' : '' }}>Q{{ $q }}</option>
                            @endforeach
                        </select>
                        <select name="chart_mata_uang"
                            class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                            <option value="">Semua Mata Uang</option>
                            <option value="IDR" {{ request('chart_mata_uang') == 'IDR' ? 'selected' : '' }}>IDR
                            </option>
                            <option value="USD" {{ request('chart_mata_uang') == 'USD' ? 'selected' : '' }}>USD
                            </option>
                        </select>
                        <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Terapkan</button>
                        @if (request()->anyFilled(['chart_tahun', 'chart_kuartal', 'chart_mata_uang']))
                            <a href="{{ route('admin.investment-managers.show', $manager) }}"
                                class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            @if ($chartPeriods->isEmpty())
                <div class="py-16 text-center text-muted bg-white rounded-2xl border border-line">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
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
                    document.addEventListener('DOMContentLoaded', function() {
                        const labels = {!! json_encode($chartLabels) !!};
                        const aumData = {!! json_encode($chartAum) !!};
                        const upData = {!! json_encode($chartUp) !!};

                        function fmt(v) {
                            return v.toLocaleString('id-ID');
                        }

                        const opts = {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            return fmt(ctx.parsed.x);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: true
                                    },
                                    ticks: {
                                        callback: function(val) {
                                            return fmt(val);
                                        }
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    }
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
