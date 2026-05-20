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
                <h1 class="text-2xl font-bold text-primary">{{ $plan->kategori_perencanaan }}</h1>
                <p class="text-muted text-sm mt-1">Dibuat {{ $plan->created_at->format('d F Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('user.perencanaan-investasi.edit', $plan) }}"
                   class="flex items-center gap-2 px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
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
                <p class="text-xs text-muted">Dana Tersedia</p>
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

    {{-- AI Analysis --}}
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-accent to-accent/80">
            <h3 class="font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Analisis & Rekomendasi AI
            </h3>
            <form method="POST" action="{{ route('user.perencanaan-investasi.regenerate-ai', $plan) }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 text-white rounded-lg text-xs font-semibold hover:bg-white/30 transition"
                        onclick="this.innerHTML='Memproses...'; this.disabled=true;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Regenerate
                </button>
            </form>
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

    <div class="flex items-center gap-3">
        <a href="{{ route('user.perencanaan-investasi.index') }}"
           class="px-5 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
            Kembali ke Daftar
        </a>
    </div>
@endsection
