@extends('layouts.user')

@section('title', isset($plan) ? 'Edit Rencana Investasi' : 'Buat Rencana Investasi')

@php
    $predefinedCategories = [
        'Pendidikan Anak',
        'Dana Pensiun',
        'Pembelian Rumah',
        'Dana Darurat',
        'Liburan / Haji / Umroh',
        'Investasi Reguler',
        'Lainnya',
    ];
    $currentCategory = old('kategori_perencanaan', $plan->kategori_perencanaan ?? '');
    $isCustomCategory = $currentCategory && !in_array($currentCategory, $predefinedCategories);
    $portofolioItems = $portofolioItems ?? collect();
    $pfRows = $portofolioItems->map(
        fn($item) => [
            'jenis' => $item->jenis ?? '',
            'nama_produk' => $item->nama_produk ?? '',
            'produk_id' => $item->produk_id ?? '',
            'produk_type' => $item->produk_type ?? '',
            'nominal' => $item->nominal ?? '',
            'harga_akuisisi' => $item->harga_akuisisi ?? '',
            'loading' => false,
            'products' => [],
            'manual_produk' => false,
            'total_nilai' => 0,
            'total_nilai_formatted' => 'Rp 0',
        ],
    );
@endphp

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-muted mb-3">
            <a href="{{ route('user.perencanaan-investasi.index') }}" class="hover:text-primary transition">Perencanaan
                Investasi</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-primary font-medium">{{ isset($plan) ? 'Edit Rencana' : 'Buat Rencana Baru' }}</span>
        </div>
        <h1 class="page-title">
            {{ isset($plan) ? 'Edit Rencana Investasi' : 'Buat Rencana Investasi Baru' }}</h1>
        <p class="page-sub">Isi data di bawah untuk memulai perencanaan. Setelah disimpan, AI akan
            menganalisis dan memberikan rekomendasi strategi.</p>
    </div>

    @if (!isset($plan))
        {{-- Template Cepat --}}
        <div class="mb-6">
            <h3 class="font-bold text-primary text-sm mb-3">Mulai Cepat dengan Template</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <button type="button" onclick="isiTemplate('pendidikan')"
                    class="text-left p-4 bg-white rounded-2xl border border-line shadow-sm hover:shadow-md hover:border-accent/30 transition-all">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 grid place-items-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <p class="font-semibold text-sm text-primary">Pendidikan Anak</p>
                    <p class="text-xs text-muted mt-0.5">Biaya S1 dalam 10 tahun</p>
                </button>
                <button type="button" onclick="isiTemplate('pensiun')"
                    class="text-left p-4 bg-white rounded-2xl border border-line shadow-sm hover:shadow-md hover:border-accent/30 transition-all">
                    <div class="w-9 h-9 rounded-xl bg-green-100 text-green-600 grid place-items-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <p class="font-semibold text-sm text-primary">Dana Pensiun</p>
                    <p class="text-xs text-muted mt-0.5">Persiapan 20 tahun lagi</p>
                </button>
                <button type="button" onclick="isiTemplate('rumah')"
                    class="text-left p-4 bg-white rounded-2xl border border-line shadow-sm hover:shadow-md hover:border-accent/30 transition-all">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 grid place-items-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <p class="font-semibold text-sm text-primary">Pembelian Rumah</p>
                    <p class="text-xs text-muted mt-0.5">DP rumah dalam 5 tahun</p>
                </button>
                <button type="button" onclick="isiTemplate('darurat')"
                    class="text-left p-4 bg-white rounded-2xl border border-line shadow-sm hover:shadow-md hover:border-accent/30 transition-all">
                    <div class="w-9 h-9 rounded-xl bg-yellow-100 text-yellow-600 grid place-items-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <p class="font-semibold text-sm text-primary">Dana Darurat</p>
                    <p class="text-xs text-muted mt-0.5">6 bulan pengeluaran</p>
                </button>
            </div>
        </div>
    @endif

    <form method="POST"
        action="{{ isset($plan) ? route('user.perencanaan-investasi.update', $plan) : route('user.perencanaan-investasi.store') }}"
        class="max-w-5xl" x-data="formPage()" x-on:submit.prevent="beforeSubmit($event)" novalidate>
        @csrf
        @if (isset($plan))
            @method('PUT')
        @endif

        <div class="space-y-6">

            {{-- Kategori Perencanaan --}}
            <div class="bg-white rounded-2xl border border-line shadow-sm p-6">
                <h3 class="font-bold text-primary text-sm mb-3">Kategori Perencanaan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Kategori" class="text-sm font-semibold mb-1.5" />
                        <select x-ref="kategoriSelect" name="kategori_perencanaan" x-model="selected"
                            x-on:change="onChange(selected)"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('kategori_perencanaan') border-red-400 @enderror">
                            <option value="">Pilih kategori...</option>
                            @foreach ($predefinedCategories as $cat)
                                <option value="{{ $cat }}"
                                    {{ !$isCustomCategory && $currentCategory == $cat ? 'selected' : '' }}>
                                    {{ $cat }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('kategori_perencanaan')" class="mt-1 text-xs" />
                    </div>
                    <div x-show="showCustom">
                        <x-input-label value="Kategori Lainnya" class="text-sm font-semibold mb-1.5" />
                        <input type="text" name="kategori_custom" x-model="customVal"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="Tulis kategori Anda..." value="{{ $isCustomCategory ? $currentCategory : '' }}">
                    </div>
                </div>
            </div>

            {{-- Informasi Keuangan --}}
            <div class="bg-white rounded-2xl border border-line shadow-sm p-6">
                <h3 class="font-bold text-primary text-sm mb-3">Informasi Keuangan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Kebutuhan Dana (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="kebutuhan_dana"
                            oninput="formatRupiahInput(this)"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('kebutuhan_dana') border-red-400 @enderror"
                            placeholder="500.000.000"
                            value="{{ old('kebutuhan_dana', isset($plan->kebutuhan_dana) ? number_format($plan->kebutuhan_dana, 0, ',', '.') : '') }}">
                        <x-input-error :messages="$errors->get('kebutuhan_dana')" class="mt-1 text-xs" />
                    </div>
                    <div>
                        <x-input-label value="Target Waktu (tahun)" class="text-sm font-semibold mb-1.5" />
                        <input type="number" name="target_waktu_tahun" min="1" max="100"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="10" value="{{ old('target_waktu_tahun', $plan->target_waktu_tahun ?? '') }}">
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <x-input-label value="Portofolio Tersedia Saat Ini (Rp)"
                                class="text-sm font-semibold mb-1.5" />
                            <button type="button" onclick="togglePortofolio()"
                                class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 
                                    text-white text-sm font-medium px-4 py-2 rounded-xl 
                                    shadow-md hover:shadow-lg transition-all duration-200 mb-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12H9m12 0A9 9 0 1112 3a9 9 0 019 9z" />
                                </svg>
                                Detail Portofolio
                            </button>
                        </div>
                        <input type="text" inputmode="decimal" name="dana_tersedia" id="dana_tersedia"
                            oninput="formatRupiahInput(this)"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="100.000.000"
                            value="{{ old('dana_tersedia', isset($plan->dana_tersedia) ? number_format($plan->dana_tersedia, 0, ',', '.') : '') }}">
                    </div>
                    <div>
                        <x-input-label value="Rencana Investasi per Bulan (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="investasi_per_bulan"
                            oninput="formatRupiahInput(this)"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="5.000.000"
                            value="{{ old('investasi_per_bulan', isset($plan->investasi_per_bulan) ? number_format($plan->investasi_per_bulan, 0, ',', '.') : '') }}">
                    </div>
                    <div>
                        <x-input-label value="Sumber Dana" class="text-sm font-semibold mb-1.5" />
                        <select name="sumber_dana"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            <option value="">Pilih sumber dana...</option>
                            <option value="Gaji"
                                {{ old('sumber_dana', $plan->sumber_dana ?? '') == 'Gaji' ? 'selected' : '' }}>Gaji
                            </option>
                            <option value="Tabungan"
                                {{ old('sumber_dana', $plan->sumber_dana ?? '') == 'Tabungan' ? 'selected' : '' }}>Tabungan
                            </option>
                            <option value="Investasi"
                                {{ old('sumber_dana', $plan->sumber_dana ?? '') == 'Investasi' ? 'selected' : '' }}>
                                Investasi</option>
                            <option value="Warisan"
                                {{ old('sumber_dana', $plan->sumber_dana ?? '') == 'Warisan' ? 'selected' : '' }}>Warisan
                            </option>
                            <option value="Pendapatan Lain"
                                {{ old('sumber_dana', $plan->sumber_dana ?? '') == 'Pendapatan Lain' ? 'selected' : '' }}>
                                Pendapatan Lain</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Profil Risiko" class="text-sm font-semibold mb-1.5" />
                        <select name="profil_risiko"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            <option value="">Pilih profil risiko...</option>
                            <option value="Konservatif"
                                {{ old('profil_risiko', $plan->profil_risiko ?? '') == 'Konservatif' ? 'selected' : '' }}>
                                Konservatif</option>
                            <option value="Moderat"
                                {{ old('profil_risiko', $plan->profil_risiko ?? '') == 'Moderat' ? 'selected' : '' }}>
                                Moderat</option>
                            <option value="Agresif"
                                {{ old('profil_risiko', $plan->profil_risiko ?? '') == 'Agresif' ? 'selected' : '' }}>
                                Agresif</option>
                            <option value="Sangat Agresif"
                                {{ old('profil_risiko', $plan->profil_risiko ?? '') == 'Sangat Agresif' ? 'selected' : '' }}>
                                Sangat Agresif</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Data Pendidikan Anak --}}
            <div class="bg-white rounded-2xl border border-line shadow-sm p-6" x-show="selected === 'Pendidikan Anak'">
                <h3 class="font-bold text-primary text-sm mb-3">Data Pendidikan Anak</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Usia Anak" class="text-sm font-semibold mb-1.5" />
                        <input type="text" name="usia_anak"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="5 tahun" value="{{ old('usia_anak', $plan->usia_anak ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Target Pendidikan" class="text-sm font-semibold mb-1.5" />
                        <select name="target_pendidikan"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            <option value="">Pilih target...</option>
                            <option value="TK"
                                {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'TK' ? 'selected' : '' }}>TK
                            </option>
                            <option value="SD"
                                {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'SD' ? 'selected' : '' }}>SD
                            </option>
                            <option value="SMP"
                                {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'SMP' ? 'selected' : '' }}>
                                SMP</option>
                            <option value="SMA"
                                {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'SMA' ? 'selected' : '' }}>
                                SMA</option>
                            <option value="S1"
                                {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'S1' ? 'selected' : '' }}>S1
                            </option>
                            <option value="S2"
                                {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'S2' ? 'selected' : '' }}>S2
                            </option>
                            <option value="S3"
                                {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'S3' ? 'selected' : '' }}>S3
                            </option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Tipe Pendidikan" class="text-sm font-semibold mb-1.5" />
                        <select name="tipe_pendidikan"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            <option value="">Pilih tipe...</option>
                            <option value="Negeri"
                                {{ old('tipe_pendidikan', $plan->tipe_pendidikan ?? '') == 'Negeri' ? 'selected' : '' }}>
                                Negeri</option>
                            <option value="Swasta"
                                {{ old('tipe_pendidikan', $plan->tipe_pendidikan ?? '') == 'Swasta' ? 'selected' : '' }}>
                                Swasta</option>
                            <option value="Internasional"
                                {{ old('tipe_pendidikan', $plan->tipe_pendidikan ?? '') == 'Internasional' ? 'selected' : '' }}>
                                Internasional</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Lokasi Pendidikan" class="text-sm font-semibold mb-1.5" />
                        <select name="lokasi_pendidikan"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            <option value="">Pilih lokasi...</option>
                            <option value="Dalam Negeri"
                                {{ old('lokasi_pendidikan', $plan->lokasi_pendidikan ?? '') == 'Dalam Negeri' ? 'selected' : '' }}>
                                Dalam Negeri</option>
                            <option value="Luar Negeri"
                                {{ old('lokasi_pendidikan', $plan->lokasi_pendidikan ?? '') == 'Luar Negeri' ? 'selected' : '' }}>
                                Luar Negeri</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Estimasi Biaya Saat Ini (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="estimasi_biaya_saat_ini"
                            oninput="formatRupiahInput(this)"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="25.000.000"
                            value="{{ old('estimasi_biaya_saat_ini', isset($plan->estimasi_biaya_saat_ini) ? number_format($plan->estimasi_biaya_saat_ini, 0, ',', '.') : '') }}">
                    </div>
                    <div>
                        <x-input-label value="Pemenuhan Dana Saat Ini (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="pemenuhan_dana"
                            oninput="formatRupiahInput(this)"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="5.000.000"
                            value="{{ old('pemenuhan_dana', isset($plan->pemenuhan_dana) ? number_format($plan->pemenuhan_dana, 0, ',', '.') : '') }}">
                    </div>
                </div>
            </div>

            {{-- PORTOFOLIO YANG SUDAH DIMILIKI --}}
            @if (isset($memberPortfolios) && $memberPortfolios->isNotEmpty())
                <div class="bg-white rounded-2xl border border-line shadow-sm p-6 mb-5">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-primary text-sm">Portofolio yang Sudah Dimiliki</h3>
                            <p class="text-xs text-muted mt-0.5">Data dari Daftar Portfolio Anda</p>
                        </div>
                        <button type="button" onclick="importMemberPortfolio()"
                            class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium px-3 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Import ke Portofolio
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                                    <th class="px-4 py-2.5 font-semibold">Jenis</th>
                                    <th class="px-4 py-2.5 font-semibold">Nama Efek</th>
                                    <th class="px-4 py-2.5 font-semibold text-right">Jumlah</th>
                                    <th class="px-4 py-2.5 font-semibold text-right">Nilai Pasar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line" id="memberPortfolioTable">
                                @foreach ($memberPortfolios as $item)
                                    <tr data-jenis="{{ $item->jenis }}" data-nama="{{ $item->nama_efek }}"
                                        data-jumlah="{{ (int) $item->jumlah }}"
                                        data-harga="{{ (int) $item->harga_saat_ini }}"
                                        data-nilai="{{ (int) $item->total_nilai }}">
                                        <td class="px-4 py-2 text-xs">{{ $item->jenis }}</td>
                                        <td class="px-4 py-2 font-medium text-primary text-xs">{{ $item->nama_efek }}</td>
                                        <td class="px-4 py-2 text-right text-xs">
                                            {{ $item->jumlah ? number_format($item->jumlah, 0, ',', '.') : '—' }}</td>
                                        <td class="px-4 py-2 text-right text-xs font-semibold">
                                            {{ $item->total_nilai ? 'Rp ' . number_format($item->total_nilai, 0, ',', '.') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- PORTOFOLIO SECTION --}}
            <div id="portofolioSection" class="bg-white rounded-2xl border border-line shadow-sm p-6"
                style="display:none;">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-primary text-sm">Daftar Portofolio</h3>
                        <p class="text-xs text-muted mt-0.5">Tambahkan portofolio investasi yang Anda miliki saat ini</p>
                    </div>
                    <button type="button" onclick="togglePortofolio()"
                        class="text-muted hover:text-primary transition p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="portofolioTable">
                        <thead>
                            <tr class="border-b border-line">
                                <th class="text-left py-2 pr-2 font-semibold text-primary w-36">Jenis</th>
                                <th class="text-left py-2 px-2 font-semibold text-primary">Produk</th>
                                <th class="text-right py-2 px-2 font-semibold text-primary w-40">Jumlah
                                    (Unit/Lembar/Satuan)</th>
                                <th class="text-right py-2 px-2 font-semibold text-primary w-40">Harga Saat Ini (T-1)</th>
                                <th class="text-right py-2 px-2 font-semibold text-primary w-44">Total Nilai</th>
                                <th class="text-center py-2 pl-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, i) in pfRows" :key="i">
                                <tr class="border-b border-line/50">
                                    <td class="py-1.5 pr-2">
                                        <select x-init="onPfRowTypeInit(row, $el)" x-model="row.jenis"
                                            x-on:change="onPfRowTypeChanged(row, $el)"
                                            :name="`portofolio_items[${i}][jenis]`"
                                            class="w-full px-2 py-1.5 border border-line rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                                            x-ref="selJenis_${i}">
                                            <option value="">Pilih...</option>
                                            <option value="Kas/Deposito">Kas/Deposito</option>
                                            <option value="Reksa Dana">Reksa Dana</option>
                                            <option value="Reksadana">Reksadana</option>
                                            <option value="Saham">Saham</option>
                                            <option value="Obligasi">Obligasi</option>
                                        </select>
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <div class="produk-dynamic" :id="'produk-dynamic-' + i"
                                            x-html="renderProdukInput(i, row)">

                                        </div>
                                        <input type="hidden" :name="`portofolio_items[${i}][produk_id]`"
                                            x-model="row.produk_id" class="produk-id">
                                        <input type="hidden" :name="`portofolio_items[${i}][produk_type]`"
                                            x-model="row.produk_type">
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <input type="number" :name="`portofolio_items[${i}][nominal]`"
                                            x-model="row.nominal" x-on:input="pfUpdateTotal(row); pfHitungAll()"
                                            placeholder="0" min="0" step="0.01"
                                            class="w-full px-2 py-1.5 border border-line rounded-lg text-xs text-right focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <input type="text" x-model="row.harga_akuisisi" disabled
                                            x-on:pfSetPrice="row.harga_akuisisi = $event.detail"
                                            class="w-full px-2 py-1.5 border border-line rounded-lg text-xs bg-gray-50 text-right text-muted cursor-not-allowed"
                                            placeholder="Otomatis">
                                        <span x-show="row.loading"
                                            class="text-xs text-muted animate-pulse">memuat...</span>
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <input type="text" x-model="row.total_nilai_formatted" disabled
                                            class="w-full border border-transparent rounded-lg text-xs text-right font-semibold cursor-not-allowed text-primary"
                                            placeholder="—">
                                    </td>
                                    <td class="py-1.5 pl-2 text-center">
                                        <button type="button" @click="pfRows.splice(i, 1); pfHitungAll()"
                                            class="text-red-400 hover:text-red-600 transition p-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="pfRows.length === 0">
                                <td colspan="6" class="px-4 py-6 text-center text-muted text-sm">
                                    Belum ada portofolio. Klik "Tambah Baris" untuk menambahkan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between mt-4 pt-4 border-t border-line">
                    <button type="button"
                        x-on:click="pfRows.push({jenis:'', nama_produk:'', produk_id:'', produk_type:'', nominal:'', harga_akuisisi:'', loading:false, numberinput:0, total_nilai:0})"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-accent/10 text-accent rounded-xl text-xs font-semibold hover:bg-accent/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 23 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Baris
                    </button>
                    <div class="text-right">
                        <span class="text-xs text-muted">Total Portofolio:</span>
                        <span class="text-lg font-bold text-primary ml-2" id="totalPortofolio">Rp 0</span>
                        <div x-text="$root.ptotalFormatted" style="display:none"></div>
                    </div>
                </div>
            </div>

        </div>

        <div class="flex items-center gap-3 mt-5">
            <button type="submit"
                class="px-5 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
                {{ isset($plan) ? 'Simpan Perubahan' : 'Simpan & Analisis' }}
            </button>
            <a href="{{ route('user.perencanaan-investasi.index') }}"
                class="px-5 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
                Batal
            </a>
        </div>
    </form>

    {{-- MODAL GRAFIK --}}
    <div id="grafikModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-black/50" onclick="tutupGrafik()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-5 border-b border-line">
                <div>
                    <h3 class="font-bold text-primary" id="grafikTitle">Grafik Kinerja</h3>
                    <p class="text-xs text-muted mt-0.5" id="grafikSubtitle">Performa 1 Tahun Terakhir</p>
                </div>
                <button type="button" onclick="tutupGrafik()" class="text-muted hover:text-primary transition p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5">
                <div id="grafikLoading" class="text-center py-12 text-muted text-sm">Memuat data grafik...</div>
                <div id="grafikContainer" style="display:none;">
                    <canvas id="grafikCanvas"></canvas>
                </div>
                <div id="grafikEmpty" class="text-center py-12 text-muted text-sm" style="display:none;">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p>Tidak ada data grafik untuk produk ini.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('formPage', () => ({
                selected: '{{ $isCustomCategory ? 'Lainnya' : $currentCategory }}',
                customVal: '{{ $isCustomCategory ? addslashes($currentCategory) : '' }}',
                showCustom: {{ $isCustomCategory ? 'true' : 'false' }},
                ptotalFormatted: 'Rp 0',
                pfTotal: 0,
                pfRows: @json($pfRows),

                init() {
                    this.pfHitungAll();
                },

                onChange(val) {
                    this.showCustom = val === 'Lainnya';
                    if (val !== 'Lainnya') this.customVal = '';
                },

                beforeSubmit(evt) {
                    if (this.selected === 'Lainnya' && this.customVal.trim()) {
                        this.$refs.kategoriSelect.value = this.customVal.trim();
                    }
                    ['dana_tersedia', 'kebutuhan_dana', 'investasi_per_bulan',
                        'estimasi_biaya_saat_ini', 'pemenuhan_dana'
                    ].forEach(name => {
                        const el = evt.target.querySelector(`[name="${name}"]`);
                        if (el) el.value = el.value.replace(/\./g, '');
                    });
                    evt.target.submit();
                },

                onPfRowTypeInit(row, $el) {
                    if (!row.jenis) return;

                    row.produk_type = row.jenis === 'Kas/Deposito' ? 'bank' :
                        (row.jenis === 'Reksa Dana' || row.jenis === 'Reksadana' ? 'reksadana' :
                            row.jenis === 'Saham' ? 'saham' : 'obligasi');

                    if (row.jenis === 'Kas/Deposito') {
                        if (!row.harga_akuisisi) row.harga_akuisisi = '1';
                        this.pfUpdateTotal(row);
                        return;
                    }

                    fetch(
                            `{{ route('user.portofolio.produk') }}?jenis=${encodeURIComponent(row.jenis)}`
                            )
                        .then(r => r.json())
                        .then(data => {
                            row.products = data;
                        })
                        .catch(() => {
                            row.products = [];
                        });
                },

                onPfRowTypeChanged(row, $el) {
                    row.produk_id = '';
                    row.nama_produk = '';
                    row.harga_akuisisi = '';
                    row.products = [];
                    row.manual_produk = false;

                    if (!row.jenis) {
                        row.produk_type = '';
                        this.pfHitungAll();
                        return;
                    }

                    row.produk_type = row.jenis === 'Kas/Deposito' ? 'bank' :
                        (row.jenis === 'Reksa Dana' || row.jenis === 'Reksadana' ? 'reksadana' :
                            row.jenis === 'Saham' ? 'saham' : 'obligasi');

                    if (row.jenis === 'Kas/Deposito') {
                        row.harga_akuisisi = '1';
                        this.pfUpdateTotal(row);
                        this.pfHitungAll();
                        return;
                    }

                    fetch(
                            `{{ route('user.portofolio.produk') }}?jenis=${encodeURIComponent(row.jenis)}`
                            )
                        .then(r => r.json())
                        .then(data => {
                            row.products = data;
                        })
                        .catch(() => {
                            row.products = [];
                        });
                },

                renderProdukInput(i, row) {
                    const cls =
                        'w-full px-2 py-1.5 border border-line rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent';

                    if (!row.jenis) {
                        return `<input type="text" disabled placeholder="Pilih jenis dulu" class="${cls}">`;
                    }

                    if (row.jenis === 'Kas/Deposito') {
                        const opts = BANK_LIST.map(b => `<option value="${b}">${b}</option>`).join('');
                        return `<input type="text" list="bank-dl-${i}" value="${row.nama_produk || ''}"
                        onchange="pfProdukChanged(${i}, this.value)"
                        placeholder="Pilih atau ketik nama bank..."
                        class="${cls}">
                        <datalist id="bank-dl-${i}">${opts}</datalist>`;
                    }

                    if (row.manual_produk) {
                        return `<input type="text" value="${row.nama_produk || ''}"
                        onchange="pfProdukChanged(${i}, this.value)"
                        placeholder="Ketik nama produk..."
                        class="${cls}">`;
                    }

                    const products = row.products || [];
                    const opts = products.map(p =>
                        `<option value="${p.nama}" data-id="${p.id}" ${p.nama === row.nama_produk ? 'selected' : ''}>${p.nama}</option>`
                    ).join('');

                    return `<select onchange="pfProdukChanged(${i}, this)" class="${cls}">
                    <option value="">${products.length ? 'Pilih produk...' : 'Memuat...'}</option>
                    ${opts}
                    <option value="__manual__">— Ketik manual —</option>
                </select>`;
                },

                pfUpdateTotal(row) {
                    const nominal = parseFloat(cleanRupiah(row.nominal)) || 0;
                    const harga = parseFloat(cleanRupiah(row.harga_akuisisi)) || 0;
                    row.total_nilai = nominal * harga;
                    row.total_nilai_formatted = 'Rp ' + formatNumber(Math.floor(row.total_nilai));
                },

                pfHitungAll() {
                    let total = 0;
                    this.pfRows.forEach(row => {
                        total += row.total_nilai || 0;
                    });
                    this.pfTotal = total;
                    this.ptotalFormatted = 'Rp ' + formatNumber(Math.floor(total));
                    document.getElementById('totalPortofolio').textContent = this.ptotalFormatted;
                    const danaInput = document.getElementById('dana_tersedia');
                    if (danaInput) danaInput.value = formatNumber(Math.floor(total));
                },

                pfFetchHarga(row) {
                    row.loading = true;
                    const params = new URLSearchParams({
                        jenis: row.jenis,
                        produk_id: row.produk_id,
                        produk_type: row.produk_type
                    });
                    fetch(`{{ route('user.portofolio.harga') }}?${params}`)
                        .then(r => r.json())
                        .then(data => {
                            row.harga_akuisisi = (data.harga !== null && data.harga !== undefined) ?
                                data.harga : '';
                            row.loading = false;
                            this.pfUpdateTotal(row);
                            this.pfHitungAll();
                        })
                        .catch(() => {
                            row.harga_akuisisi = '';
                            row.loading = false;
                            this.pfUpdateTotal(row);
                            this.pfHitungAll();
                        });
                }
            }));
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        let grafikChart = null;

        const BANK_LIST = [
            'Bank Mandiri', 'Bank BCA', 'Bank BNI', 'Bank BRI', 'Bank CIMB Niaga',
            'Bank Danamon', 'Bank Panin', 'Bank Permata', 'Bank Maybank',
            'Bank BJB', 'Bank Jatim', 'Bank Jateng', 'Bank DIY', 'Bank BPD Bali',
            'Bank Sumut', 'Bank Sumsel Babel', 'Bank Sultra', 'Bank Sulteng',
            'Bank Kalbar', 'Bank Kaltim', 'Bank Kalteng', 'Bank Kalsel',
            'Bank Lampung', 'Bank Aceh', 'Bank NTB', 'Bank NTT',
            'Bank Sulselbar', 'Bank Maluku', 'Bank Papua', 'Bank Bengkulu',
            'Bank Babel', 'Bank Riau Kepri',
        ];

        const TEMPLATES = {
            pendidikan: {
                kategori: 'Pendidikan Anak',
                kebutuhan: 250000000,
                target: 15,
                investasi: 1000000,
                sumber: 'Gaji',
                risiko: 'Moderat',
                usia_anak: '3 tahun',
                target_pendidikan: 'S1',
                tipe_pendidikan: 'Swasta',
                lokasi_pendidikan: 'Dalam Negeri',
                estimasi_biaya: 250000000,
            },
            pensiun: {
                kategori: 'Dana Pensiun',
                kebutuhan: 2000000000,
                target: 20,
                investasi: 5000000,
                sumber: 'Gaji',
                risiko: 'Moderat'
            },
            rumah: {
                kategori: 'Pembelian Rumah',
                kebutuhan: 500000000,
                target: 5,
                investasi: 7000000,
                sumber: 'Gaji',
                risiko: 'Agresif'
            },
            darurat: {
                kategori: 'Dana Darurat',
                kebutuhan: 60000000,
                target: 1,
                investasi: 5000000,
                sumber: 'Tabungan',
                risiko: 'Konservatif'
            },
        };

        function isiTemplate(nama) {
            const t = TEMPLATES[nama];
            if (!t) return;
            setVal('kebutuhan_dana', t.kebutuhan);
            setVal('target_waktu_tahun', t.target);
            setVal('investasi_per_bulan', t.investasi);
            setVal('sumber_dana', t.sumber);
            setVal('profil_risiko', t.risiko);
            if (t.usia_anak) setVal('usia_anak', t.usia_anak);
            if (t.target_pendidikan) setVal('target_pendidikan', t.target_pendidikan);
            if (t.tipe_pendidikan) setVal('tipe_pendidikan', t.tipe_pendidikan);
            if (t.lokasi_pendidikan) setVal('lokasi_pendidikan', t.lokasi_pendidikan);
            if (t.estimasi_biaya) setVal('estimasi_biaya_saat_ini', t.estimasi_biaya);
            const alpine = document.querySelector('form[x-data]');
            if (alpine) {
                Alpine.$data(alpine).selected = t.kategori;
            }
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function setVal(name, value) {
            const el = document.querySelector(`[name="${name}"]`);
            if (el) {
                el.value = value;
                el.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
                el.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            }
        }

        function togglePortofolio() {
            const section = document.getElementById('portofolioSection');
            section.style.display = section.style.display === 'none' ? 'block' : 'none';
        }

        function importMemberPortfolio() {
            const tbody = document.getElementById('memberPortfolioTable');
            const rows = tbody.querySelectorAll('tr');
            console.log('importMemberPortfolio clicked, rows:', rows.length);
            if (rows.length === 0) {
                alert('Tidak ada data portofolio untuk diimport.');
                return;
            }

            const section = document.getElementById('portofolioSection');
            section.style.display = 'block';
            console.log('portofolioSection display set to block');

            const form = document.querySelector('form[x-data]');
            const comp = form ? Alpine.$data(form) : null;
            console.log('Alpine comp:', comp, 'pfRows:', comp?.pfRows);
            if (!comp) {
                console.error('Alpine formPage tidak ditemukan');
                return;
            }

            const normalizeJenis = j => {
                if (!j) return '';
                const lower = String(j).toLowerCase();
                if (lower.includes('kas') || lower.includes('deposito')) return 'Kas/Deposito';
                if (lower.includes('reksa') || lower.includes('reksadana')) return 'Reksa Dana';
                if (lower.includes('saham')) return 'Saham';
                if (lower.includes('obligasi')) return 'Obligasi';
                return j;
            };

            const imported = [];
            rows.forEach(row => {
                const jenis = normalizeJenis(row.dataset.jenis || '');
                const nama = row.dataset.nama || '';
                const jumlah = Math.floor(parseFloat(row.dataset.jumlah) || 0);
                const harga = Math.floor(parseFloat(row.dataset.harga) || 0);
                const produk_type = jenis === 'Kas/Deposito' ? 'bank' :
                    (jenis === 'Reksa Dana' ? 'reksadana' :
                        (jenis === 'Saham' ? 'saham' : 'obligasi'));

                imported.push({
                    jenis,
                    nama_produk: nama,
                    produk_id: '',
                    produk_type,
                    nominal: jumlah,
                    harga_akuisisi: jenis === 'Kas/Deposito' ? '1' : harga,
                    loading: false,
                    products: [],
                    manual_produk: false,
                    total_nilai: 0,
                    total_nilai_formatted: 'Rp 0',
                });
            });
            console.log('imported:', imported);

            // Ganti seluruh array agar Alpine pasti re-render
            comp.pfRows = imported.map(row => {
                comp.pfUpdateTotal(row);
                return row;
            });
            comp.pfHitungAll();
            console.log('pfRows after import:', comp.pfRows);

            // Untuk baris non-kas, muat daftar produk dan cocokkan dengan nama efek
            imported.forEach(async (row, idx) => {
                if (row.jenis === 'Kas/Deposito' || !row.nama_produk) return;

                try {
                    const res = await fetch(
                        `{{ route('user.portofolio.produk') }}?jenis=${encodeURIComponent(row.jenis)}`);
                    const data = await res.json();
                    const target = comp.pfRows[idx];
                    if (!target) return;
                    target.products = data;

                    const match = data.find(p => p.nama && p.nama.toLowerCase() === row.nama_produk
                        .toLowerCase());
                    if (match) {
                        target.nama_produk = match.nama;
                        target.produk_id = match.id;
                        comp.pfFetchHarga(target);
                    } else {
                        target.manual_produk = true;
                        target.produk_id = row.nama_produk;
                        comp.pfUpdateTotal(target);
                        comp.pfHitungAll();
                    }
                } catch (e) {
                    console.error('Gagal muat produk import:', e);
                }
            });

            section.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        function pfProdukChanged(i, valueOrEl) {
            const form = document.querySelector('form[x-data]');
            const comp = form ? Alpine.$data(form) : null;
            if (!comp) return;
            const row = comp.pfRows[i];

            if (row.jenis === 'Kas/Deposito') {
                row.nama_produk = valueOrEl;
                row.produk_id = '';
                row.produk_type = 'bank';
                row.harga_akuisisi = '1';
                comp.pfUpdateTotal(row);
                comp.pfHitungAll();
                return;
            }

            if (typeof valueOrEl === 'string') {
                row.nama_produk = valueOrEl;
                row.produk_id = valueOrEl;
                comp.pfFetchHarga(row);
                return;
            }

            const sel = valueOrEl;
            if (sel.value === '__manual__') {
                row.manual_produk = true;
                row.nama_produk = '';
                row.produk_id = '';
                row.harga_akuisisi = '';
                comp.pfUpdateTotal(row);
                comp.pfHitungAll();
                return;
            }

            const opt = sel.options[sel.selectedIndex];
            row.nama_produk = sel.value;
            row.produk_id = opt?.dataset?.id || sel.value;
            comp.pfFetchHarga(row);
        }

        function formatRupiahInput(input) {
            let val = input.value.replace(/[^\d]/g, '');
            if (val === '') {
                input.value = '';
                return;
            }
            input.value = formatNumber(parseInt(val));
        }

        function cleanRupiah(val) {
            return String(val).replace(/\./g, '').replace(/,/g, '.');
        }

        function formatNumber(num) {
            return Math.floor(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function bukaGrafik(jenis, nama) {
            document.getElementById('grafikTitle').textContent = nama;
            document.getElementById('grafikSubtitle').textContent = `Performa ${jenis} - 1 Tahun Terakhir`;
            document.getElementById('grafikModal').style.display = 'flex';
            document.getElementById('grafikLoading').style.display = 'block';
            document.getElementById('grafikContainer').style.display = 'none';
            document.getElementById('grafikEmpty').style.display = 'none';
            document.body.style.overflow = 'hidden';

            let produkId = jenis === 'Saham' ? nama : '';
            let produkType = jenis === 'Kas/Deposito' ? 'bank' : (jenis === 'Reksa Dana' ? 'reksadana' : (jenis ===
                'Saham' ? 'saham' : 'obligasi'));
            const params = new URLSearchParams({
                jenis,
                produk_id: produkId,
                produk_type: produkType
            });

            fetch(`{{ route('user.portofolio.grafik') }}?${params}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('grafikLoading').style.display = 'none';
                    if (!data.labels || data.labels.length === 0) {
                        document.getElementById('grafikEmpty').style.display = 'block';
                        return;
                    }
                    document.getElementById('grafikContainer').style.display = 'block';
                    const ctx = document.getElementById('grafikCanvas').getContext('2d');
                    if (grafikChart) grafikChart.destroy();
                    grafikChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: data.label || 'Nilai',
                                data: data.values,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: 2,
                                pointHoverRadius: 5,
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => 'Rp ' + formatNumber(Math.round(ctx.parsed.y))
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        maxTicksLimit: 8,
                                        font: {
                                            size: 10
                                        }
                                    }
                                },
                                y: {
                                    grid: {
                                        color: '#f1f5f9'
                                    },
                                    ticks: {
                                        font: {
                                            size: 10
                                        },
                                        callback: v => 'Rp ' + formatNumber(Math.round(v))
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(() => {
                    document.getElementById('grafikLoading').style.display = 'none';
                    document.getElementById('grafikEmpty').style.display = 'block';
                });
        }

        function tutupGrafik() {
            document.getElementById('grafikModal').style.display = 'none';
            document.body.style.overflow = '';
            if (grafikChart) {
                grafikChart.destroy();
                grafikChart = null;
            }
        }
    </script>
@endpush
