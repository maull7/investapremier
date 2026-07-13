@extends('layouts.user')

@section('title', 'Detail Perencanaan Investasi - InvestaPremier')

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-muted mb-3">
            <a href="{{ route('user.perencanaan-investasi.index') }}" class="hover:text-primary transition">Perencanaan Investasi</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-primary font-medium">{{ $plan->kategori_perencanaan }}</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">{{ $plan->kategori_perencanaan }}</h1>
                <p class="page-sub">Dibuat {{ $plan->created_at->format('d F Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('user.perencanaan-investasi.pdf', $plan) }}"
                   class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    PDF
                </a>
                @if ($plan->user_id === auth()->id())
                <a href="{{ route('user.perencanaan-investasi.edit', $plan) }}"
                   class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                @endif
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Data Perencanaan --}}
    <div class="bg-white rounded-2xl border border-line shadow-sm p-6 mb-6">
        <h3 class="font-bold text-primary text-sm mb-4">Data Perencanaan</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-muted">Kategori</p>
                <p class="font-semibold text-sm">{{ $plan->kategori_perencanaan }}</p>
            </div>
            <div>
                <p class="text-xs text-muted">Kebutuhan Dana</p>
                <p class="font-semibold text-sm">Rp{{ number_format($plan->kebutuhan_dana ?? 0, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-muted">Target Waktu</p>
                <p class="font-semibold text-sm">{{ $plan->target_waktu_tahun ? $plan->target_waktu_tahun . ' tahun' : '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-muted">Portofolio Tersedia</p>
                <p class="font-semibold text-sm">Rp{{ number_format($plan->dana_tersedia ?? 0, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-muted">Investasi/Bulan</p>
                <p class="font-semibold text-sm">Rp{{ number_format($plan->investasi_per_bulan ?? 0, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-muted">Sumber Dana</p>
                <p class="font-semibold text-sm">{{ $plan->sumber_dana ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-muted">Profil Risiko</p>
                <p class="font-semibold text-sm">{{ $plan->profil_risiko ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-muted">Status</p>
                @php
                    $statusColors = ['Aktif' => 'bg-green-100 text-green-700', 'Selesai' => 'bg-blue-100 text-blue-700', 'Ditunda' => 'bg-yellow-100 text-yellow-700'];
                    $color = $statusColors[$plan->status] ?? 'bg-gray-100 text-gray-600';
                @endphp
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $color }}">{{ $plan->status }}</span>
            </div>
        </div>

        @if ($plan->kategori_perencanaan === 'Pendidikan Anak')
            <hr class="my-4 border-line">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @if ($plan->usia_anak)
                <div>
                    <p class="text-xs text-muted">Usia Anak</p>
                    <p class="font-semibold text-sm">{{ $plan->usia_anak }}</p>
                </div>
                @endif
                @if ($plan->target_pendidikan)
                <div>
                    <p class="text-xs text-muted">Target</p>
                    <p class="font-semibold text-sm">{{ $plan->target_pendidikan }}</p>
                </div>
                @endif
                @if ($plan->tipe_pendidikan)
                <div>
                    <p class="text-xs text-muted">Tipe</p>
                    <p class="font-semibold text-sm">{{ $plan->tipe_pendidikan }}</p>
                </div>
                @endif
                @if ($plan->lokasi_pendidikan)
                <div>
                    <p class="text-xs text-muted">Lokasi</p>
                    <p class="font-semibold text-sm">{{ $plan->lokasi_pendidikan }}</p>
                </div>
                @endif
                @if ($plan->estimasi_biaya_saat_ini)
                <div>
                    <p class="text-xs text-muted">Estimasi Biaya</p>
                    <p class="font-semibold text-sm">Rp{{ number_format($plan->estimasi_biaya_saat_ini, 0, ',', '.') }}</p>
                </div>
                @endif
                @if ($plan->pemenuhan_dana)
                <div>
                    <p class="text-xs text-muted">Pemenuhan Dana</p>
                    <p class="font-semibold text-sm">Rp{{ number_format($plan->pemenuhan_dana, 0, ',', '.') }}</p>
                </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Portofolio --}}
    @if ($plan->portofolioItems->isNotEmpty())
    <div class="bg-white rounded-2xl border border-line shadow-sm p-6 mb-6">
        <h3 class="font-bold text-primary text-sm mb-4">Portofolio Saat Ini</h3>
        <div class="space-y-3">
            @foreach ($plan->portofolioItems as $item)
            <div class="bg-[#f8fafc] rounded-xl border border-line">
                <div class="flex items-center justify-between p-3">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                            {{ $item->jenis === 'Kas/Deposito' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $item->jenis === 'Reksa Dana' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $item->jenis === 'Saham' ? 'bg-purple-100 text-purple-700' : '' }}
                            {{ $item->jenis === 'Obligasi' ? 'bg-orange-100 text-orange-700' : '' }}">
                            {{ $item->jenis }}
                        </span>
                        <span class="font-semibold text-sm text-primary">{{ $item->nama_produk }}</span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-muted">{{ $item->jenis === 'Reksa Dana' ? number_format($item->nominal, 0, ',', '.') . ' UP' : ($item->jenis === 'Saham' ? number_format($item->nominal, 0, ',', '.') . ' lembar' : 'Rp' . number_format($item->nominal, 0, ',', '.')) }} × Rp{{ number_format($item->harga_akuisisi, 0, ',', '.') }}</p>
                        <p class="font-bold text-sm text-primary">Rp{{ number_format($item->nilai, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="border-t border-line">
                    <div class="chart-loading text-xs text-muted text-center py-6">Memuat grafik...</div>
                    <div class="h-44 px-4 pb-4" style="display:none;">
                        <canvas class="portofolio-chart w-full h-full"
                            data-jenis="{{ $item->jenis }}"
                            data-produk-id="{{ $item->produk_id ?? '' }}"
                            data-produk-type="{{ $item->produk_type ?? '' }}"
                            data-nama="{{ $item->nama_produk }}"></canvas>
                    </div>
                </div>
            </div>
            @endforeach
            <div class="flex items-center justify-between p-3 bg-accent/5 rounded-xl border border-accent/20">
                <span class="font-semibold text-sm text-primary">Total Portofolio</span>
                <span class="font-bold text-lg text-primary">Rp{{ number_format($plan->portofolioItems->sum('nilai'), 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Progress Tracking --}}
    <div class="bg-white rounded-2xl border border-line shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-primary text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Progress Realisasi
            </h3>
            <button type="button" onclick="bukaModalCheckin()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-accent text-white rounded-lg text-xs font-semibold hover:bg-accent/90 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Check-in
            </button>
        </div>

        @php
            $kebutuhan = (float) ($plan->kebutuhan_dana ?? 0);
            $latestDana = $latestCheckin ? (float) $latestCheckin->dana_terkumpul : 0;
            $progressPct = $kebutuhan > 0 ? min(100, round(($latestDana / $kebutuhan) * 100)) : 0;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="p-3 bg-[#f8fafc] rounded-xl border border-line">
                <p class="text-xs text-muted">Target Dana</p>
                <p class="font-bold text-sm text-primary">Rp{{ number_format($kebutuhan, 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-[#f8fafc] rounded-xl border border-line">
                <p class="text-xs text-muted">Realisasi Terkini</p>
                <p class="font-bold text-sm {{ $latestDana > 0 ? 'text-green-600' : 'text-muted' }}">Rp{{ number_format($latestDana, 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-[#f8fafc] rounded-xl border border-line">
                <p class="text-xs text-muted">Gap</p>
                <p class="font-bold text-sm {{ ($kebutuhan - $latestDana) > 0 ? 'text-red-600' : 'text-green-600' }}">Rp{{ number_format(max(0, $kebutuhan - $latestDana), 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="mb-2 flex items-center justify-between text-xs">
            <span class="text-muted">Progress</span>
            <span class="font-semibold {{ $progressPct >= 100 ? 'text-green-600' : 'text-primary' }}">{{ $progressPct }}%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500 {{ $progressPct >= 100 ? 'bg-green-500' : 'bg-accent' }}" style="width: {{ $progressPct }}%"></div>
        </div>

        @if ($checkins->count() > 1)
        <div class="mt-4">
            <p class="text-xs text-muted font-semibold mb-2">Riwayat Check-in</p>
            <div class="space-y-1.5">
                @foreach ($checkins->take(5) as $c)
                <div class="flex items-center justify-between text-xs py-1.5 px-3 bg-[#f8fafc] rounded-lg border border-line">
                    <span class="text-muted">{{ $c->tanggal_checkin->format('d M Y') }}</span>
                    <span class="font-semibold text-primary">Rp{{ number_format($c->dana_terkumpul, 0, ',', '.') }}</span>
                    @if ($c->catatan)
                    <span class="text-muted truncate max-w-[150px]">{{ $c->catatan }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- AI Analysis --}}
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-accent to-accent/80">
            <h3 class="th-title">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Analisis & Rekomendasi AI
            </h3>
            <form method="POST" action="{{ route('user.perencanaan-investasi.regenerate-ai', $plan) }}" id="regenerate-form">
                @csrf
                <button type="submit" id="regenerate-btn"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 text-white rounded-lg text-xs font-semibold hover:bg-white/30 transition">
                    <svg id="regenerate-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span id="regenerate-text">Regenerate</span>
                </button>
            </form>
            <script>
                document.getElementById('regenerate-form').addEventListener('submit', function() {
                    document.getElementById('regenerate-text').textContent = 'Memproses...';
                    document.getElementById('regenerate-icon').classList.add('animate-spin');
                });
            </script>
        </div>

        @if (!empty($plan->ai_output['error']))
            <div class="p-6">
                <div class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <svg class="w-5 h-5 shrink-0 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="font-semibold text-red-700 text-sm">Gagal menganalisis</p>
                        <p class="text-red-600 text-xs mt-1">{{ $plan->ai_output['message'] ?? 'Terjadi kesalahan saat menghubungi AI.' }}</p>
                        <p class="text-xs text-muted mt-2">Klik "Regenerate" untuk mencoba lagi.</p>
                    </div>
                </div>
            </div>
        @elseif (empty($plan->ai_output) && empty($plan->ai_narasi))
            <div class="p-6">
                <div class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <svg class="w-5 h-5 shrink-0 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="font-semibold text-blue-700 text-sm">Belum ada analisis</p>
                        <p class="text-blue-600 text-xs mt-1">Klik "Regenerate" untuk meminta AI menganalisis rencana ini.</p>
                    </div>
                </div>
            </div>
        @else
            @php $ai = $plan->ai_output; @endphp
            <div class="p-6 space-y-6">

                @if (!empty($ai['ringkasan']))
                <div class="p-4 bg-[#f8fafc] rounded-xl border border-line">
                    <p class="text-xs text-muted font-semibold uppercase tracking-wide mb-1">Ringkasan</p>
                    <p class="text-sm text-primary">{{ $ai['ringkasan'] }}</p>
                </div>
                @endif

                @if (!empty($ai['analisis_keuangan']))
                <div>
                    <h4 class="font-semibold text-primary text-sm mb-3">Analisis Keuangan</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach (['total_kebutuhan' => 'Total Kebutuhan', 'dana_saat_ini' => 'Dana Saat Ini', 'defisit' => 'Defisit', 'investasi_bulanan' => 'Investasi Bulanan'] as $key => $label)
                            @if (!empty($ai['analisis_keuangan'][$key]))
                            <div class="p-3 bg-white rounded-xl border border-line">
                                <p class="text-xs text-muted">{{ $label }}</p>
                                <p class="font-bold text-sm {{ $key === 'defisit' ? 'text-red-600' : 'text-primary' }}">{{ $ai['analisis_keuangan'][$key] }}</p>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                @if (!empty($ai['asumsi']))
                <div class="flex items-center gap-4 p-3 bg-[#f8fafc] rounded-xl border border-line">
                    @if (!empty($ai['asumsi']['inflasi']))
                    <div>
                        <p class="text-xs text-muted">Asumsi Inflasi</p>
                        <p class="font-semibold text-sm text-primary">{{ $ai['asumsi']['inflasi'] }}</p>
                    </div>
                    @endif
                    @if (!empty($ai['asumsi']['return_investasi']))
                    <div>
                        <p class="text-xs text-muted">Asumsi Return</p>
                        <p class="font-semibold text-sm text-primary">{{ $ai['asumsi']['return_investasi'] }}</p>
                    </div>
                    @endif
                </div>
                @endif

                @if (!empty($ai['proyeksi']))
                <div>
                    <h4 class="font-semibold text-primary text-sm mb-3">Proyeksi</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @if (!empty($ai['proyeksi']['nilai_terkumpul']))
                        <div class="p-3 bg-green-50 rounded-xl border border-green-200">
                            <p class="text-xs text-muted">Nilai Terkumpul</p>
                            <p class="font-bold text-sm text-green-700">{{ $ai['proyeksi']['nilai_terkumpul'] }}</p>
                        </div>
                        @endif
                        @if (!empty($ai['proyeksi']['ketercapaian']))
                        <div class="p-3 bg-blue-50 rounded-xl border border-blue-200">
                            <p class="text-xs text-muted">Ketercapaian Target</p>
                            <p class="font-bold text-sm text-blue-700">{{ $ai['proyeksi']['ketercapaian'] }}</p>
                        </div>
                        @endif
                        @if (!empty($ai['proyeksi']['gap_dana']))
                        <div class="p-3 {{ str_contains($ai['proyeksi']['gap_dana'], 'kurang') || str_contains($ai['proyeksi']['gap_dana'], 'defisit') ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }} rounded-xl border">
                            <p class="text-xs text-muted">Gap Dana</p>
                            <p class="font-bold text-sm {{ str_contains($ai['proyeksi']['gap_dana'], 'kurang') || str_contains($ai['proyeksi']['gap_dana'], 'defisit') ? 'text-red-700' : 'text-green-700' }}">{{ $ai['proyeksi']['gap_dana'] }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if (!empty($ai['rekomendasi_strategi']))
                <div>
                    <h4 class="font-semibold text-primary text-sm mb-3">Rekomendasi Strategi</h4>
                    <ul class="space-y-2">
                        @foreach ($ai['rekomendasi_strategi'] as $item)
                            <li class="flex items-start gap-2 text-sm">
                                <svg class="w-4 h-4 shrink-0 text-accent mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (!empty($ai['alokasi_aset']))
                <div>
                    <h4 class="font-semibold text-primary text-sm mb-3">Alokasi Aset</h4>
                    <div class="space-y-2">
                        @foreach ($ai['alokasi_aset'] as $item)
                            <div class="flex items-center gap-3 p-3 bg-white rounded-xl border border-line">
                                <div class="w-16 text-center">
                                    <span class="inline-block px-2 py-1 rounded-lg bg-accent/10 text-accent font-bold text-xs">{{ $item['persentase'] ?? '' }}</span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-sm text-primary">{{ $item['jenis'] ?? '' }}</p>
                                    @if (!empty($item['keterangan']))
                                        <p class="text-xs text-muted">{{ $item['keterangan'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if (!empty($ai['rekomendasi_portofolio']))
                @php
                    // Map nama produk → item portofolio untuk keperluan grafik
                    $portofolioMap = $plan->portofolioItems->keyBy('nama_produk');
                @endphp
                <div>
                    <h4 class="font-semibold text-primary text-sm mb-3">Rekomendasi Portofolio</h4>
                    <div class="space-y-3">
                        @foreach ($ai['rekomendasi_portofolio'] as $efek)
                        @php
                            $item = $portofolioMap->get($efek['nama_efek'] ?? '');
                            $aksi = $efek['aksi'] ?? 'Tahan';
                            $aksiColor = match($aksi) {
                                'Beli'  => 'bg-green-100 text-green-700',
                                'Jual'  => 'bg-red-100 text-red-700',
                                default => 'bg-yellow-100 text-yellow-700',
                            };
                        @endphp
                        <div class="bg-white rounded-xl border border-line p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                        onclick="bukaGrafikEfek(this)"
                                        data-nama="{{ $efek['nama_efek'] ?? '' }}"
                                        data-jenis="{{ $efek['jenis'] ?? ($item?->jenis ?? '') }}"
                                        data-produk-id="{{ $item?->produk_id ?? '' }}"
                                        data-produk-type="{{ $item?->produk_type ?? '' }}"
                                        class="font-semibold text-sm text-accent hover:underline text-left">
                                        {{ $efek['nama_efek'] ?? '-' }}
                                    </button>
                                    @if (!empty($efek['jenis']))
                                    <span class="text-xs text-muted bg-[#f1f5f9] px-2 py-0.5 rounded-full">{{ $efek['jenis'] }}</span>
                                    @endif
                                </div>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $aksiColor }}">{{ $aksi }}</span>
                            </div>
                            @if (!empty($efek['analisa']))
                            <p class="text-xs text-muted mb-1">{{ $efek['analisa'] }}</p>
                            @endif
                            @if (!empty($efek['rekomendasi']))
                            <p class="text-xs text-primary font-medium">→ {{ $efek['rekomendasi'] }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if (!empty($ai['rekomendasi_investor']))
                <div class="p-4 bg-accent/5 rounded-xl border border-accent/20">
                    <p class="text-xs text-muted font-semibold uppercase tracking-wide mb-1">Rekomendasi untuk Investor</p>
                    <p class="text-sm text-primary">{{ $ai['rekomendasi_investor'] }}</p>
                </div>
                @endif

                @if (!empty($ai['catatan_risiko']))
                <div class="p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                    <p class="text-xs text-yellow-700 font-semibold uppercase tracking-wide mb-1">Catatan Risiko</p>
                    <p class="text-sm text-yellow-800">{{ $ai['catatan_risiko'] }}</p>
                </div>
                @endif

            </div>
        @endif
    </div>

    {{-- What-If Scenario --}}
    @php
        $whatIfBulanan = number_format($plan->investasi_per_bulan ?? 1000000, 0, '.', '');
        $whatIfTahun = $plan->target_waktu_tahun ?? 10;
        $whatIfDanaAwal = number_format($plan->dana_tersedia ?? 0, 0, '.', '');
        $whatIfKebutuhan = number_format($plan->kebutuhan_dana ?? 0, 0, '.', '');
    @endphp
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-6" x-data="{
        bulanan: {{ $whatIfBulanan }},
        returnYear: 10,
        tahun: {{ $whatIfTahun }},
        maxTahun: {{ $whatIfTahun }},
        totalInvestasi: 0,
        nilaiAkhir: 0,
        returnInvestasi: 0,
        ketercapaian: 0,
        pctClass: 'bg-green-50 rounded-xl border border-green-200',
        pctTextClass: 'font-bold text-sm text-green-700',

        init() {
            this.maxTahun = Math.max(this.tahun, 5);
            this.hitung();
        },

        hitung() {
            const r = this.returnYear / 100 / 12;
            const n = this.tahun * 12;
            const P = this.bulanan;
            const danaAwal = {{ $whatIfDanaAwal }};

            const fvInvestasi = r > 0 ? P * ((Math.pow(1 + r, n) - 1) / r) : P * n;
            const fvPortfolio = danaAwal * Math.pow(1 + this.returnYear / 100, this.tahun);

            this.totalInvestasi = P * n;
            this.nilaiAkhir = Math.round(fvInvestasi + fvPortfolio);
            this.returnInvestasi = this.nilaiAkhir - this.totalInvestasi;

            const kebutuhan = {{ $whatIfKebutuhan }};
            if (kebutuhan > 0) {
                this.ketercapaian = Math.min(100, Math.round((this.nilaiAkhir / kebutuhan) * 100));
                if (this.ketercapaian >= 100) {
                    this.pctClass = 'bg-green-50 rounded-xl border border-green-200';
                    this.pctTextClass = 'font-bold text-sm text-green-700';
                } else if (this.ketercapaian >= 50) {
                    this.pctClass = 'bg-yellow-50 rounded-xl border border-yellow-200';
                    this.pctTextClass = 'font-bold text-sm text-yellow-700';
                } else {
                    this.pctClass = 'bg-red-50 rounded-xl border border-red-200';
                    this.pctTextClass = 'font-bold text-sm text-red-700';
                }
            }

            this.gambarGrafik();
        },

        formatRp(val) {
            if (!val && val !== 0) return 'Rp 0';
            return 'Rp ' + Math.round(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        get ketercapaianText() {
            if (this.ketercapaian >= 100) return 'Tercapai';
            return this.ketercapaian + '%';
        },

        gambarGrafik() {
            this.$nextTick(() => {
                const canvas = document.getElementById('whatifChart');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                if (window.__whatifChart) { window.__whatifChart.destroy(); window.__whatifChart = null; }

                const labels = [];
                const dataSkema = [];
                const dataTarget = [];
                const r = this.returnYear / 100 / 12;
                const n = this.tahun * 12;
                const P = this.bulanan;
                const danaAwal = {{ $whatIfDanaAwal }};
                const kebutuhan = {{ $whatIfKebutuhan }};

                for (let i = 0; i <= n; i += Math.max(1, Math.floor(n / 12))) {
                    const year = Math.floor(i / 12);
                    labels.push('Thn ' + year);
                    const fv = r > 0 ? P * ((Math.pow(1 + r, i) - 1) / r) : P * i;
                    const fvp = danaAwal * Math.pow(1 + this.returnYear / 100, year);
                    dataSkema.push(Math.round(fv + fvp));
                    dataTarget.push(kebutuhan);
                }
                if (labels[labels.length - 1] !== 'Thn ' + this.tahun) {
                    labels.push('Thn ' + this.tahun);
                    const fvFinal = r > 0 ? P * ((Math.pow(1 + r, n) - 1) / r) : P * n;
                    const fvpFinal = danaAwal * Math.pow(1 + this.returnYear / 100, this.tahun);
                    dataSkema.push(Math.round(fvFinal + fvpFinal));
                    dataTarget.push(kebutuhan);
                }

                window.__whatifChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Proyeksi',
                            data: dataSkema,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,0.08)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                            borderWidth: 2,
                        }, {
                            label: 'Target',
                            data: dataTarget,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239,68,68,0.05)',
                            fill: false,
                            tension: 0,
                            pointRadius: 2,
                            borderWidth: 2,
                            borderDash: [5, 5],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { font: { size: 10 } } },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ctx.dataset.label + ': Rp ' + Math.round(ctx.parsed.y).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { size: 9 },
                                    callback: v => 'Rp' + (v / 1000000).toFixed(0) + 'jt'
                                }
                            }
                        }
                    }
                });
            });
        }
    }">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100/50">
            <h3 class="font-bold text-primary text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Simulasi What-If
            </h3>
            <span class="text-xs text-muted">Ubah parameter untuk melihat skenario alternatif</span>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="text-xs text-muted font-semibold block mb-1">Investasi per Bulan (Rp)</label>
                    <input type="range" x-model="bulanan" min="100000" max="50000000" step="100000"
                        class="w-full accent-accent"
                        @input="hitung()">
                    <div class="flex justify-between text-xs text-muted mt-1">
                        <span>Rp100rb</span>
                        <span class="font-semibold text-primary" x-text="formatRp(bulanan)"></span>
                        <span>Rp50jt</span>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-muted font-semibold block mb-1">Return per Tahun (%)</label>
                    <input type="range" x-model="returnYear" min="1" max="25" step="0.5"
                        class="w-full accent-accent"
                        @input="hitung()">
                    <div class="flex justify-between text-xs text-muted mt-1">
                        <span>1%</span>
                        <span class="font-semibold text-primary" x-text="returnYear + '%'"></span>
                        <span>25%</span>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-muted font-semibold block mb-1">Jangka Waktu (tahun)</label>
                    <input type="range" x-model="tahun" min="1" :max="maxTahun" step="1"
                        class="w-full accent-accent"
                        @input="hitung()">
                    <div class="flex justify-between text-xs text-muted mt-1">
                        <span>1 thn</span>
                        <span class="font-semibold text-primary" x-text="tahun + ' tahun'"></span>
                        <span x-text="maxTahun + ' thn'"></span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                <div class="p-3 bg-blue-50 rounded-xl border border-blue-200">
                    <p class="text-xs text-muted">Total Investasi</p>
                    <p class="font-bold text-sm text-blue-700" x-text="formatRp(totalInvestasi)"></p>
                </div>
                <div class="p-3 bg-green-50 rounded-xl border border-green-200">
                    <p class="text-xs text-muted">Nilai Akhir (estimasi)</p>
                    <p class="font-bold text-sm text-green-700" x-text="formatRp(nilaiAkhir)"></p>
                </div>
                <div class="p-3 bg-purple-50 rounded-xl border border-purple-200">
                    <p class="text-xs text-muted">Return Investasi</p>
                    <p class="font-bold text-sm text-purple-700" x-text="formatRp(returnInvestasi)"></p>
                </div>
                <div class="p-3" :class="pctClass">
                    <p class="text-xs text-muted">Ketercapaian Target</p>
                    <p class="font-bold text-sm" :class="pctTextClass" x-text="ketercapaianText"></p>
                </div>
            </div>

            <div class="relative" style="height: 200px;">
                <canvas id="whatifChart"></canvas>
            </div>

            <p class="text-xs text-muted mt-3">* Simulasi ini menggunakan bunga majemuk dan bersifat indikatif. Hasil sebenarnya dapat berbeda.</p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('user.perencanaan-investasi.index') }}"
           class="px-5 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
            Kembali ke Daftar
        </a>
    </div>

    {{-- Modal Check-in --}}
    <div id="checkinModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-bold text-primary text-sm">Check-in Progress</h4>
                <button onclick="tutupModalCheckin()" class="text-muted hover:text-primary transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('user.perencanaan-investasi.checkin', $plan) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-primary block mb-1">Dana Terkumpul Saat Ini (Rp)</label>
                        <input type="number" name="dana_terkumpul" required min="0"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="Masukkan total dana yang sudah terkumpul">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-primary block mb-1">Catatan (opsional)</label>
                        <textarea name="catatan" rows="2"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="Misalnya: 'Dari bonus tahunan'"></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" onclick="tutupModalCheckin()"
                        class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition">Simpan Check-in</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Grafik Modal --}}
    <div id="grafikModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 id="grafikTitle" class="font-bold text-primary text-sm">Grafik Kinerja</h4>
                <button onclick="tutupGrafik()" class="text-muted hover:text-primary transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="grafikLoading" class="flex items-center justify-center py-12 text-muted text-sm">Memuat grafik...</div>
            <div id="grafikEmpty" class="hidden flex items-center justify-center py-12 text-muted text-sm">Data grafik tidak tersedia.</div>
            <canvas id="grafikCanvas" class="hidden" height="200"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Check-in Modal
        function bukaModalCheckin() {
            document.getElementById('checkinModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function tutupModalCheckin() {
            document.getElementById('checkinModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function(e) {
            const modal = document.getElementById('checkinModal');
            if (e.target === modal) tutupModalCheckin();
        });

        let grafikChart = null;

        function bukaGrafikEfek(btn) {
            const nama = btn.dataset.nama;
            const jenis = btn.dataset.jenis;
            const produkId = btn.dataset.produkId;
            const produkType = btn.dataset.produkType;

            document.getElementById('grafikTitle').textContent = 'Grafik Kinerja — ' + nama;
            document.getElementById('grafikModal').classList.remove('hidden');
            document.getElementById('grafikLoading').classList.remove('hidden');
            document.getElementById('grafikEmpty').classList.add('hidden');
            document.getElementById('grafikCanvas').classList.add('hidden');
            document.body.style.overflow = 'hidden';

            if (grafikChart) { grafikChart.destroy(); grafikChart = null; }

            const url = `{{ route('user.portofolio.grafik') }}?jenis=${encodeURIComponent(jenis)}&produk_type=${encodeURIComponent(produkType)}&produk_id=${encodeURIComponent(produkId)}&nama=${encodeURIComponent(nama)}`;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('grafikLoading').classList.add('hidden');
                    if (!data.labels || !data.labels.length) {
                        document.getElementById('grafikEmpty').classList.remove('hidden');
                        return;
                    }
                    const canvas = document.getElementById('grafikCanvas');
                    canvas.classList.remove('hidden');
                    grafikChart = new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: data.label || nama,
                                data: data.values,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,0.08)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: 2,
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { grid: { display: false }, ticks: { maxTicksLimit: 6, font: { size: 10 } } },
                                y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } }
                            }
                        }
                    });
                })
                .catch(() => {
                    document.getElementById('grafikLoading').classList.add('hidden');
                    document.getElementById('grafikEmpty').classList.remove('hidden');
                });
        }

        function tutupGrafik() {
            document.getElementById('grafikModal').classList.add('hidden');
            document.body.style.overflow = '';
            if (grafikChart) { grafikChart.destroy(); grafikChart = null; }
        }

        document.getElementById('grafikModal').addEventListener('click', function(e) {
            if (e.target === this) tutupGrafik();
        });

        // Inline charts for portfolio items
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.portofolio-chart').forEach(function(canvas) {
                const jenis = canvas.dataset.jenis;
                const produkId = canvas.dataset.produkId;
                const produkType = canvas.dataset.produkType;
                const nama = canvas.dataset.nama;
                const chartWrap = canvas.closest('.h-44');
                const loading = chartWrap.parentElement.querySelector('.chart-loading');

                const url = `{{ route('user.portofolio.grafik') }}?jenis=${encodeURIComponent(jenis)}&produk_type=${encodeURIComponent(produkType)}&produk_id=${encodeURIComponent(produkId)}&nama=${encodeURIComponent(nama)}`;

                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        loading.style.display = 'none';
                        if (!data.labels || !data.labels.length) {
                            return;
                        }
                        chartWrap.style.display = 'block';
                        new Chart(canvas, {
                            type: 'line',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    label: data.label || nama,
                                    data: data.values,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59,130,246,0.08)',
                                    fill: true,
                                    tension: 0.3,
                                    pointRadius: 1,
                                    borderWidth: 1.5,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { grid: { display: false }, ticks: { maxTicksLimit: 4, font: { size: 9 } } },
                                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 9 } } }
                                }
                            }
                        });
                    })
                    .catch(() => {
                        loading.style.display = 'none';
                    });
            });
        });
    </script>
@endsection
