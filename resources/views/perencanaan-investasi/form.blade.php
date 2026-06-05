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
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <p class="font-semibold text-sm text-primary">Pendidikan Anak</p>
                <p class="text-xs text-muted mt-0.5">Biaya S1 dalam 10 tahun</p>
            </button>
            <button type="button" onclick="isiTemplate('pensiun')"
                class="text-left p-4 bg-white rounded-2xl border border-line shadow-sm hover:shadow-md hover:border-accent/30 transition-all">
                <div class="w-9 h-9 rounded-xl bg-green-100 text-green-600 grid place-items-center mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <p class="font-semibold text-sm text-primary">Dana Pensiun</p>
                <p class="text-xs text-muted mt-0.5">Persiapan 20 tahun lagi</p>
            </button>
            <button type="button" onclick="isiTemplate('rumah')"
                class="text-left p-4 bg-white rounded-2xl border border-line shadow-sm hover:shadow-md hover:border-accent/30 transition-all">
                <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 grid place-items-center mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <p class="font-semibold text-sm text-primary">Pembelian Rumah</p>
                <p class="text-xs text-muted mt-0.5">DP rumah dalam 5 tahun</p>
            </button>
            <button type="button" onclick="isiTemplate('darurat')"
                class="text-left p-4 bg-white rounded-2xl border border-line shadow-sm hover:shadow-md hover:border-accent/30 transition-all">
                <div class="w-9 h-9 rounded-xl bg-yellow-100 text-yellow-600 grid place-items-center mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <p class="font-semibold text-sm text-primary">Dana Darurat</p>
                <p class="text-xs text-muted mt-0.5">6 bulan pengeluaran</p>
            </button>
        </div>
    </div>
    @endif

    <form method="POST"
        action="{{ isset($plan) ? route('user.perencanaan-investasi.update', $plan) : route('user.perencanaan-investasi.store') }}"
        class="max-w-5xl" x-data="kategoriSelect()" x-on:submit.prevent="beforeSubmit($event)" novalidate>
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
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('kebutuhan_dana') border-red-400 @enderror"
                            placeholder="500000000" value="{{ old('kebutuhan_dana', $plan->kebutuhan_dana ?? '') }}">
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
                            <x-input-label value="Portofolio Tersedia Saat Ini (Rp)" class="text-sm font-semibold mb-1.5" />
                            <button type="button" onclick="togglePortofolio()"
                                class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 
                                    text-white text-sm font-medium px-4 py-2 rounded-xl 
                                    shadow-md hover:shadow-lg transition-all duration-200 mb-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12H9m12 0A9 9 0 1112 3a9 9 0 019 9z" />
                                </svg>
                                Detail Portofolio
                            </button>
                        </div>
                        <input type="text" inputmode="decimal" name="dana_tersedia" id="dana_tersedia"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="100000000" value="{{ old('dana_tersedia', $plan->dana_tersedia ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Rencana Investasi per Bulan (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="investasi_per_bulan"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="5000000"
                            value="{{ old('investasi_per_bulan', $plan->investasi_per_bulan ?? '') }}">
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
            <div class="bg-white rounded-2xl border border-line shadow-sm p-6"
                x-show="selected === 'Pendidikan Anak'">
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
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="25000000"
                            value="{{ old('estimasi_biaya_saat_ini', $plan->estimasi_biaya_saat_ini ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Pemenuhan Dana Saat Ini (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="pemenuhan_dana"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="5000000" value="{{ old('pemenuhan_dana', $plan->pemenuhan_dana ?? '') }}">
                    </div>
                </div>
            </div>

            {{-- PORTOFOLIO SECTION --}}
            <div id="portofolioSection" class="bg-white rounded-2xl border border-line shadow-sm p-6" style="display:none;">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-primary text-sm">Daftar Portofolio</h3>
                        <p class="text-xs text-muted mt-0.5">Tambahkan portofolio investasi yang Anda miliki saat ini</p>
                    </div>
                    <button type="button" onclick="togglePortofolio()"
                        class="text-muted hover:text-primary transition p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="portofolioTable">
                        <thead>
                            <tr class="border-b border-line">
                                <th class="text-left py-2 pr-2 font-semibold text-primary w-36">Jenis</th>
                                <th class="text-left py-2 px-2 font-semibold text-primary">Produk</th>
                                <th class="text-right py-2 px-2 font-semibold text-primary w-40">Nominal</th>
                                <th class="text-right py-2 px-2 font-semibold text-primary w-40">Harga Akuisisi</th>
                                <th class="text-right py-2 px-2 font-semibold text-primary w-44">Nilai</th>
                                <th class="text-center py-2 pl-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="portofolioBody">
                            @forelse ($portofolioItems as $item)
                            <tr class="border-b border-line/50 portofolio-row">
                                <td class="py-1.5 pr-2">
                                    <select name="portofolio_items[{{ $loop->index }}][jenis]" onchange="rowJenisChanged(this)"
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent jenis-select">
                                        <option value="">Pilih...</option>
                                        <option value="Kas/Deposito" {{ $item->jenis == 'Kas/Deposito' ? 'selected' : '' }}>Kas/Deposito</option>
                                        <option value="Reksa Dana" {{ $item->jenis == 'Reksa Dana' ? 'selected' : '' }}>Reksa Dana</option>
                                        <option value="Saham" {{ $item->jenis == 'Saham' ? 'selected' : '' }}>Saham</option>
                                        <option value="Obligasi" {{ $item->jenis == 'Obligasi' ? 'selected' : '' }}>Obligasi</option>
                                    </select>
                                </td>
                                <td class="py-1.5 px-2">
                                    <div class="produk-wrapper">
                                        <select name="portofolio_items[{{ $loop->index }}][nama_produk]" onchange="rowProdukChanged(this)"
                                            class="w-full px-2 py-1.5 border border-line rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent produk-select"
                                            data-nama="{{ $item->nama_produk }}">
                                            <option value="">{{ $item->jenis ? 'Memuat...' : 'Pilih jenis dulu' }}</option>
                                        </select>
                                        <input type="hidden" name="portofolio_items[{{ $loop->index }}][produk_id]" class="produk-id" value="{{ $item->produk_id }}">
                                        <input type="hidden" name="portofolio_items[{{ $loop->index }}][produk_type]" class="produk-type" value="{{ $item->produk_type }}">
                                    </div>
                                </td>
                                <td class="py-1.5 px-2">
                                    <input type="text" name="portofolio_items[{{ $loop->index }}][nominal]" value="{{ number_format($item->nominal, 0, ',', '.') }}"
                                        oninput="formatRupiahInput(this); rowHitung(this)"
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-xs text-right focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent nominal-input"
                                        placeholder="0">
                                </td>
                                <td class="py-1.5 px-2">
                                    <input type="text" name="portofolio_items[{{ $loop->index }}][harga_akuisisi]" value="{{ number_format($item->harga_akuisisi, 0, ',', '.') }}"
                                        readonly
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-xs text-right bg-gray-50 focus:outline-none harga-input"
                                        placeholder="Otomatis">
                                </td>
                                <td class="py-1.5 px-2">
                                    <input type="text" readonly
                                        class="w-full px-2 py-1.5 border border-transparent rounded-lg text-xs text-right font-semibold text-primary nilai-display"
                                        value="Rp {{ number_format($item->nilai, 0, ',', '.') }}">
                                </td>
                                <td class="py-1.5 pl-2 text-center">
                                    <button type="button" onclick="hapusRow(this)"
                                        class="text-red-400 hover:text-red-600 transition p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr class="border-b border-line/50 portofolio-row">
                                <td class="py-1.5 pr-2">
                                    <select name="portofolio_items[0][jenis]" onchange="rowJenisChanged(this)"
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent jenis-select">
                                        <option value="">Pilih...</option>
                                        <option value="Kas/Deposito">Kas/Deposito</option>
                                        <option value="Reksa Dana">Reksa Dana</option>
                                        <option value="Saham">Saham</option>
                                        <option value="Obligasi">Obligasi</option>
                                    </select>
                                </td>
                                <td class="py-1.5 px-2">
                                    <div class="produk-wrapper">
                                        <select name="portofolio_items[0][nama_produk]" onchange="rowProdukChanged(this)"
                                            class="w-full px-2 py-1.5 border border-line rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent produk-select">
                                            <option value="">Pilih jenis dulu</option>
                                        </select>
                                        <input type="hidden" name="portofolio_items[0][produk_id]" class="produk-id">
                                        <input type="hidden" name="portofolio_items[0][produk_type]" class="produk-type">
                                    </div>
                                </td>
                                <td class="py-1.5 px-2">
                                    <input type="text" name="portofolio_items[0][nominal]"
                                        oninput="formatRupiahInput(this); rowHitung(this)"
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-xs text-right focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent nominal-input"
                                        placeholder="0">
                                </td>
                                <td class="py-1.5 px-2">
                                    <input type="text" name="portofolio_items[0][harga_akuisisi]" readonly
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-xs text-right bg-gray-50 focus:outline-none harga-input"
                                        placeholder="Otomatis">
                                </td>
                                <td class="py-1.5 px-2">
                                    <input type="text" readonly
                                        class="w-full px-2 py-1.5 border border-transparent rounded-lg text-xs text-right font-semibold text-primary nilai-display"
                                        value="Rp 0">
                                </td>
                                <td class="py-1.5 pl-2 text-center">
                                    <button type="button" onclick="hapusRow(this)"
                                        class="text-red-400 hover:text-red-600 transition p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between mt-4 pt-4 border-t border-line">
                    <button type="button" onclick="tambahRow()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-accent/10 text-accent rounded-xl text-xs font-semibold hover:bg-accent/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Baris
                    </button>
                    <div class="text-right">
                        <span class="text-xs text-muted">Total Portofolio:</span>
                        <span class="text-lg font-bold text-primary ml-2" id="totalPortofolio">Rp 0</span>
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
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
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
        Alpine.data('kategoriSelect', () => ({
            selected: '{{ $isCustomCategory ? 'Lainnya' : $currentCategory }}',
            customVal: '{{ $isCustomCategory ? addslashes($currentCategory) : '' }}',
            showCustom: {{ $isCustomCategory ? 'true' : 'false' }},
            onChange(val) {
                this.showCustom = val === 'Lainnya';
                if (val !== 'Lainnya') this.customVal = '';
            },
            beforeSubmit(evt) {
                if (this.selected === 'Lainnya' && this.customVal.trim()) {
                    this.$refs.kategoriSelect.value = this.customVal.trim();
                }
                ['dana_tersedia', 'kebutuhan_dana', 'investasi_per_bulan', 'estimasi_biaya_saat_ini', 'pemenuhan_dana'].forEach(name => {
                    const el = evt.target.querySelector(`[name="${name}"]`);
                    if (el) el.value = el.value.replace(/\./g, '');
                });
                evt.target.submit();
            }
        }));
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    let portofolioRowIndex = {{ $portofolioItems->count() > 0 ? $portofolioItems->count() : 1 }};
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

    // Template Cepat
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
            risiko: 'Moderat',
        },
        rumah: {
            kategori: 'Pembelian Rumah',
            kebutuhan: 500000000,
            target: 5,
            investasi: 7000000,
            sumber: 'Gaji',
            risiko: 'Agresif',
        },
        darurat: {
            kategori: 'Dana Darurat',
            kebutuhan: 60000000,
            target: 1,
            investasi: 5000000,
            sumber: 'Tabungan',
            risiko: 'Konservatif',
        },
    };

    function isiTemplate(nama) {
        const t = TEMPLATES[nama];
        if (!t) return;

        // Set kategori
        const kategoriSelect = document.querySelector('[name="kategori_perencanaan"]');
        const alpine = document.querySelector('[x-data]');
        if (kategoriSelect) {
            kategoriSelect.value = t.kategori;
            kategoriSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Set fields
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

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function setVal(name, value) {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) {
            el.value = value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function togglePortofolio() {
        const section = document.getElementById('portofolioSection');
        const isOpen = section.style.display !== 'none';
        section.style.display = isOpen ? 'none' : 'block';
    }

    function tambahRow() {
        const tbody = document.getElementById('portofolioBody');
        const idx = portofolioRowIndex++;
        const tr = document.createElement('tr');
        tr.className = 'border-b border-line/50 portofolio-row';
        tr.innerHTML = `
            <td class="py-1.5 pr-2">
                <select name="portofolio_items[${idx}][jenis]" onchange="rowJenisChanged(this)"
                    class="w-full px-2 py-1.5 border border-line rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent jenis-select">
                    <option value="">Pilih...</option>
                    <option value="Kas/Deposito">Kas/Deposito</option>
                    <option value="Reksa Dana">Reksa Dana</option>
                    <option value="Saham">Saham</option>
                    <option value="Obligasi">Obligasi</option>
                </select>
            </td>
            <td class="py-1.5 px-2">
                <div class="produk-wrapper">
                    <select name="portofolio_items[${idx}][nama_produk]" onchange="rowProdukChanged(this)"
                        class="w-full px-2 py-1.5 border border-line rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent produk-select">
                        <option value="">Pilih jenis dulu</option>
                    </select>
                    <input type="hidden" name="portofolio_items[${idx}][produk_id]" class="produk-id">
                    <input type="hidden" name="portofolio_items[${idx}][produk_type]" class="produk-type">
                </div>
            </td>
            <td class="py-1.5 px-2">
                <input type="text" name="portofolio_items[${idx}][nominal]"
                    oninput="formatRupiahInput(this); rowHitung(this)"
                    class="w-full px-2 py-1.5 border border-line rounded-lg text-xs text-right focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent nominal-input"
                    placeholder="0">
            </td>
            <td class="py-1.5 px-2">
                <input type="text" name="portofolio_items[${idx}][harga_akuisisi]" readonly
                    class="w-full px-2 py-1.5 border border-line rounded-lg text-xs text-right bg-gray-50 focus:outline-none harga-input"
                    placeholder="Otomatis">
            </td>
            <td class="py-1.5 px-2">
                <input type="text" readonly
                    class="w-full px-2 py-1.5 border border-transparent rounded-lg text-xs text-right font-semibold text-primary nilai-display"
                    value="Rp 0">
            </td>
            <td class="py-1.5 pl-2 text-center">
                <button type="button" onclick="hapusRow(this)"
                    class="text-red-400 hover:text-red-600 transition p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        tr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        hitungTotal();
    }

    function hapusRow(btn) {
        const tr = btn.closest('tr');
        if (document.querySelectorAll('.portofolio-row').length <= 1) {
            const inputs = tr.querySelectorAll('input');
            inputs.forEach(inp => { if (!inp.readOnly) inp.value = ''; });
            const selects = tr.querySelectorAll('select');
            selects.forEach(sel => sel.selectedIndex = 0);
            const displays = tr.querySelectorAll('.nilai-display');
            displays.forEach(d => d.value = 'Rp 0');
            const hargaInputs = tr.querySelectorAll('.harga-input');
            hargaInputs.forEach(h => h.value = '');
            hitungTotal();
            return;
        }
        tr.remove();
        hitungTotal();
    }

    function rowJenisChanged(select) {
        const tr = select.closest('tr');
        const wrapper = tr.querySelector('.produk-wrapper');
        const produkSelect = wrapper.querySelector('.produk-select');
        const produkId = wrapper.querySelector('.produk-id');
        const produkType = wrapper.querySelector('.produk-type');
        const hargaInput = tr.querySelector('.harga-input');
        const nilaiDisplay = tr.querySelector('.nilai-display');
        const nominalInput = tr.querySelector('.nominal-input');
        const jenis = select.value;
        const existingNama = produkSelect.dataset.nama || '';

        produkId.value = '';
        produkType.value = '';
        hargaInput.value = '';
        nilaiDisplay.value = 'Rp 0';
        if (!nominalInput.dataset.keep) nominalInput.value = '';

        if (!jenis) {
            produkSelect.innerHTML = '<option value="">Pilih jenis dulu</option>';
            hitungTotal();
            return;
        }

        if (jenis === 'Kas/Deposito') {
            produkSelect.innerHTML = '';
            const opt = document.createElement('option');
            opt.value = 'Lainnya';
            opt.textContent = 'Lainnya (ketik manual)';
            produkSelect.appendChild(opt);
            BANK_LIST.forEach(b => {
                const o = document.createElement('option');
                o.value = b;
                o.textContent = b;
                produkSelect.appendChild(o);
            });

            const restoreNama = existingNama || produkSelect.dataset.nama || '';
            const isCustomBank = restoreNama && !BANK_LIST.includes(restoreNama) && restoreNama !== 'Lainnya';

            if (isCustomBank) {
                produkSelect.outerHTML = `<input type="text" name="${produkSelect.name}" class="w-full px-2 py-1.5 border border-line rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent produk-select" placeholder="Ketik nama bank..." value="${restoreNama}" onchange="rowProdukChanged(this)">`;
            } else {
                if (restoreNama && BANK_LIST.includes(restoreNama)) {
                    produkSelect.value = restoreNama;
                } else {
                    produkSelect.value = 'Lainnya';
                }
                produkSelect.onchange = function() {
                    if (this.value === 'Lainnya') {
                        const name = this.name;
                        this.outerHTML = `<input type="text" name="${name}" class="w-full px-2 py-1.5 border border-line rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent produk-select" placeholder="Ketik nama bank..." onchange="rowProdukChanged(this)">`;
                        const newInput = tr.querySelector('.produk-select');
                        if (newInput) rowProdukChanged(newInput);
                    } else {
                        rowProdukChanged(this);
                    }
                };
            }

            produkType.value = 'bank';
            hargaInput.value = '1';
            rowHitung(nominalInput);
            hitungTotal();
            return;
        }

        produkType.value = jenis === 'Reksa Dana' ? 'reksadana' : jenis === 'Saham' ? 'saham' : 'obligasi';

        produkSelect.innerHTML = '<option value="">Memuat...</option>';
        produkSelect.disabled = true;

        fetch(`{{ route('user.portofolio.produk') }}?jenis=${encodeURIComponent(jenis)}`)
            .then(r => r.json())
            .then(data => {
                produkSelect.innerHTML = '<option value="">Pilih produk...</option>';
                data.forEach(item => {
                    const o = document.createElement('option');
                    o.value = item.nama;
                    o.dataset.id = item.id;
                    o.dataset.harga = item.harga || 0;
                    o.textContent = item.nama;
                    produkSelect.appendChild(o);
                });

                // Tambah opsi ketik manual
                const optManual = document.createElement('option');
                optManual.value = '__manual__';
                optManual.textContent = '— Ketik manual —';
                produkSelect.appendChild(optManual);

                produkSelect.disabled = false;

                if (existingNama) {
                    const match = Array.from(produkSelect.options).find(o => o.value === existingNama);
                    if (match) {
                        produkSelect.value = existingNama;
                        rowProdukChanged(produkSelect);
                    } else {
                        // Nama tidak ada di list, tampilkan sebagai input manual
                        const name = produkSelect.name;
                        produkSelect.outerHTML = `<input type="text" name="${name}" class="w-full px-2 py-1.5 border border-line rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent produk-select" placeholder="Ketik nama produk..." value="${existingNama}" onchange="rowProdukChanged(this)">`;
                    }
                }

                produkSelect.onchange = function() {
                    if (this.value === '__manual__') {
                        const name = this.name;
                        this.outerHTML = `<input type="text" name="${name}" class="w-full px-2 py-1.5 border border-line rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent produk-select" placeholder="Ketik nama produk..." onchange="rowProdukChanged(this)">`;
                        tr.querySelector('.produk-id').value = '';
                        tr.querySelector('.harga-input').value = '';
                        rowHitung(tr.querySelector('.nominal-input'));
                        hitungTotal();
                    } else {
                        rowProdukChanged(this);
                    }
                };
            })
            .catch(() => {
                produkSelect.innerHTML = '<option value="">Gagal memuat data</option>';
                produkSelect.disabled = false;
            });
    }

    function rowProdukChanged(el) {
        const tr = el.closest('tr');
        const wrapper = tr.querySelector('.produk-wrapper');
        const produkId = wrapper.querySelector('.produk-id');
        const produkType = wrapper.querySelector('.produk-type');
        const hargaInput = tr.querySelector('.harga-input');
        const nominalInput = tr.querySelector('.nominal-input');
        const jenis = tr.querySelector('.jenis-select').value;
        const selectedValue = el.value || '';
        const isSelect = el.tagName === 'SELECT';
        const selectedId = isSelect ? (el.options[el.selectedIndex]?.dataset?.id || '') : '';

        if (jenis === 'Kas/Deposito') {
            produkId.value = '';
            produkType.value = 'bank';
            hargaInput.value = '1';
            rowHitung(nominalInput);
            hitungTotal();
            return;
        }

        if (!selectedValue) {
            hargaInput.value = '';
            rowHitung(nominalInput);
            hitungTotal();
            return;
        }

        if (jenis === 'Saham') {
            produkId.value = selectedValue;
            produkType.value = 'saham';
        } else {
            produkId.value = selectedId;
            produkType.value = jenis === 'Reksa Dana' ? 'reksadana' : 'obligasi';
        }

        const params = new URLSearchParams({ jenis, produk_id: produkId.value, produk_type: produkType.value });

        fetch(`{{ route('user.portofolio.harga') }}?${params}`)
            .then(r => r.json())
            .then(data => {
                if (data.harga !== null && data.harga !== undefined) {
                    hargaInput.value = data.harga;
                } else {
                    hargaInput.value = '';
                }
                rowHitung(nominalInput);
                hitungTotal();
            })
            .catch(() => {
                hargaInput.value = '';
                rowHitung(nominalInput);
                hitungTotal();
            });
    }

    function rowHitung(el) {
        const tr = el.closest('tr');
        const nominalInput = tr.querySelector('.nominal-input');
        const hargaInput = tr.querySelector('.harga-input');
        const nilaiDisplay = tr.querySelector('.nilai-display');

        const nominal = parseFloat(cleanRupiah(nominalInput.value)) || 0;
        const harga = parseFloat(cleanRupiah(hargaInput.value)) || 0;
        const nilai = nominal * harga;

        nilaiDisplay.value = 'Rp ' + formatNumber(nilai);
        hitungTotal();
    }

    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.nilai-display').forEach(el => {
            const val = cleanRupiah(el.value.replace('Rp ', ''));
            total += parseFloat(val) || 0;
        });
        document.getElementById('totalPortofolio').textContent = 'Rp ' + formatNumber(total);
        document.getElementById('dana_tersedia').value = formatNumber(total);
    }

    function formatRupiahInput(input) {
        let val = input.value.replace(/[^\d]/g, '');
        if (val === '') { input.value = ''; return; }
        input.value = formatNumber(parseInt(val));
    }

    function cleanRupiah(val) {
        return val.replace(/\./g, '').replace(/,/g, '');
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
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
        let produkType = jenis === 'Kas/Deposito' ? 'bank' : (jenis === 'Reksa Dana' ? 'reksadana' : (jenis === 'Saham' ? 'saham' : 'obligasi'));

        const params = new URLSearchParams({ jenis, produk_id: produkId, produk_type: produkType });

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
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => 'Rp ' + formatNumber(Math.round(ctx.parsed.y))
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { maxTicksLimit: 8, font: { size: 10 } }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { size: 10 },
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

    document.addEventListener('DOMContentLoaded', function() {
        hitungTotal();
        document.querySelectorAll('.jenis-select').forEach(sel => {
            if (sel.value) rowJenisChanged(sel);
        });
    });
</script>
@endpush
