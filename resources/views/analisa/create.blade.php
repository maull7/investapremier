@extends($formRoutes['layout'] ?? 'layouts.user')

@section('content')
    <div class="max-w-5xl" x-data='analisaForm(@json($resumeAnalisa), @json($resumeMode))'>
        <div class="mb-6">
            <h1 class="page-title">
                {{ !empty($isEditMode) ? 'Edit Analisa ' . ($productLabel ?? 'Reksa Dana') : 'Submit Analisa ' . ($productLabel ?? 'Reksa Dana') }}
            </h1>
            <p class="page-sub">
                {{ !empty($isEditMode) ? 'Perbarui data analisa reksa dana' : 'Isi data secara manual, upload Excel, atau ekstrak dari PDF FFS' }}
            </p>
        </div>

        @if ($errors->any())
            @php
                $isLinkTab = request('tab') === 'link-website' || old('input_mode') === 'link-website';
                $displayErrors = $isLinkTab
                    ? collect($errors->messages())
                        ->filter(
                            fn($_, $key) => in_array($key, [
                                'urls',
                                'nama_sumber',
                                'jenis_akses',
                                'login_username',
                                'login_password',
                                'catatan',
                            ]) || str_starts_with($key, 'urls.'),
                        )
                        ->flatten()
                    : collect($errors->all());
            @endphp
            @if ($displayErrors->isNotEmpty())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($displayErrors as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif

        <form id="analisa-form" method="POST" novalidate
            action="{{ !empty($isEditMode) ? $formRoutes['update'] : $formRoutes['store'] }}" enctype="multipart/form-data"
            class="space-y-6"
            @submit="if (mode === 'link-website') { $event.preventDefault(); webMessage = 'Selesaikan langkah di tab Link Website: unduh file lalu klik Isi Form Otomatis. Setelah itu submit dari tab Input Manual.'; webOk = false; }">
            @csrf
            @if (!empty($isEditMode))
                @method('PUT')
            @endif
            <input type="hidden" name="resume_id" :value="resumeId || ''">
            <input type="hidden" name="input_mode" :value="mode === 'link-website' ? 'manual' : mode">
            <input type="hidden" name="pdf_file" x-model="pdfFile">
            <input type="hidden" name="tanggal_data" :value="tanggalDataValue()">
            <input type="hidden" name="ffs_bulan" :value="jenisLaporan === 'kalender_ffs' ? ffsBulan : ''">
            <input type="hidden" name="ffs_tahun" :value="jenisLaporan === 'kalender_ffs' ? ffsTahun : ''">
            <input type="hidden" name="jenis_laporan" :value="jenisLaporan">
            <input type="hidden" name="data_tahunan"
                :value="(() => { const camelToSnake = s => s.replace(/[A-Z]/g, l => '_' + l.toLowerCase()); return JSON.stringify(Object.fromEntries([['years', tahunTambahan], ...Object.entries(dataTambahan).map(([year, fields]) => [year, Object.fromEntries(Object.entries(fields).map(([k, v]) => [camelToSnake(k), v]))])])); })()">

            {{-- Info Dasar --}}
            @if (empty($isEditMode) || ($formRoutes['layout'] ?? '') !== 'layouts.admin')
                <div class="bg-white rounded-xl border border-line p-6 space-y-4" x-show="mode !== 'link-website'" x-cloak>
                    <h3 class="font-semibold text-primary">Informasi Reksa Dana</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="kode_reksa_dana" value="Kode Reksa Dana" />
                            <x-text-input id="kode_reksa_dana" name="kode_reksa_dana" type="text"
                                class="mt-1 block w-full" x-model="kodeReksaDana"
                                @input.debounce.500ms="lookupReksaDana($event.target.value)" />
                            <x-input-error :messages="$errors->get('kode_reksa_dana')" class="mt-1" />
                            <p class="text-xs mt-1" :class="lookupOk ? 'text-emerald-600' : 'text-muted'"
                                x-text="lookupMessage"></p>
                        </div>
                        <div>
                            <x-input-label for="nama_reksa_dana" value="Nama Reksa Dana *" />
                            <x-text-input id="nama_reksa_dana" name="nama_reksa_dana" type="text"
                                class="mt-1 block w-full" value="{{ old('nama_reksa_dana') }}" x-model="namaReksaDana"
                                x-bind:required="mode !== 'link-website'" />
                            <x-input-error :messages="$errors->get('nama_reksa_dana')" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Kategori" />
                            <div class="mt-1 flex flex-wrap gap-3">
                                @foreach (['Konvensional', 'Syariah', 'index', 'ETF'] as $k)
                                    <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                        <input type="checkbox" name="kategori[]" value="{{ $k }}"
                                            {{ in_array($k, old('kategori', [])) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-primary focus:ring-primary/20">
                                        {{ $k }}
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('kategori')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="jenis_reksa_dana" value="Jenis Reksa Dana *" />
                            <select id="jenis_reksa_dana" name="jenis_reksa_dana"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm"
                                x-model="jenisReksaDana" x-bind:required="mode !== 'link-website'">
                                <option value="">Pilih Jenis</option>
                                @foreach (['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'] as $j)
                                    <option value="{{ $j }}"
                                        {{ old('jenis_reksa_dana') === $j ? 'selected' : '' }}>
                                        {{ $j }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('jenis_reksa_dana')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="benchmark" value="Benchmark *" />
                            <x-text-input id="benchmark" name="benchmark" type="text" class="mt-1 block w-full"
                                value="{{ old('benchmark') }}" x-model="benchmark"
                                x-bind:required="mode !== 'link-website'" />
                            <x-input-error :messages="$errors->get('benchmark')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="bank_kustodian_top" value="Bank Kustodian" />
                            <x-text-input id="bank_kustodian_top" name="bank_kustodian" type="text"
                                class="mt-1 block w-full" value="{{ old('bank_kustodian') }}" x-model="bankKustodian" />
                            <x-input-error :messages="$errors->get('bank_kustodian')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="management_fee_top" value="Management Fee (%)" />
                            <x-text-input id="management_fee_top" name="management_fee" type="number" step="0.01"
                                class="mt-1 block w-full" value="{{ old('management_fee') }}" x-model="managementFee" />
                            <x-input-error :messages="$errors->get('management_fee')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="custodian_fee_top" value="Custodian Fee (%)" />
                            <x-text-input id="custodian_fee_top" name="custodian_fee" type="number" step="0.01"
                                class="mt-1 block w-full" value="{{ old('custodian_fee') }}" x-model="custodianFee" />
                            <x-input-error :messages="$errors->get('custodian_fee')" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                        <div>
                            <x-input-label for="tujuan_investasi" value="Tujuan Investasi *" />
                            <textarea id="tujuan_investasi" name="tujuan_investasi" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm"
                                x-model="tujuanInvestasi" x-bind:required="mode !== 'link-website'">{{ old('tujuan_investasi') }}</textarea>
                            <x-input-error :messages="$errors->get('tujuan_investasi')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="kebijakan_investasi" value="Kebijakan Investasi *" />
                            <textarea id="kebijakan_investasi" name="kebijakan_investasi" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm"
                                x-model="kebijakanInvestasi" x-bind:required="mode !== 'link-website'">{{ old('kebijakan_investasi') }}</textarea>
                            <x-input-error :messages="$errors->get('kebijakan_investasi')" class="mt-1" />
                        </div>
                    </div>

                    <div class="border-t border-line pt-4">
                        <x-input-label value="Pilih" />
                        <div class="mt-2 flex flex-wrap gap-4 text-sm">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" value="kalender_ffs" x-model="jenisLaporan"
                                    class="text-primary focus:ring-primary/20">
                                <span>Kalender FFS</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" value="laporan_tahunan" x-model="jenisLaporan"
                                    class="text-primary focus:ring-primary/20">
                                <span>Laporan Tahunan</span>
                            </label>
                        </div>
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="jenisLaporan === 'kalender_ffs'">
                            <div>
                                <x-input-label for="ffs_bulan_top" value="Bulan" />
                                <select id="ffs_bulan_top" x-model="ffsBulan"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                                    <option value="">Pilih Bulan</option>
                                    @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $bulan)
                                        <option value="{{ $index + 1 }}">{{ $bulan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="ffs_tahun_top" value="Tahun" />
                                <input id="ffs_tahun_top" type="number" min="2000" max="2100"
                                    placeholder="2026" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm"
                                    x-model="ffsTahun" />
                            </div>
                        </div>
                        <div class="mt-4 max-w-xs" x-show="jenisLaporan === 'laporan_tahunan'" x-cloak>
                            <x-input-label for="tahun_laporan" value="Tahun" />
                            <x-text-input id="tahun_laporan" name="tahun_laporan" type="text" maxlength="4"
                                pattern="[0-9]{4}" placeholder="2025" class="mt-1 block w-full"
                                x-model="tahunLaporan" />
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tab Pilih Mode --}}
            <div class="table-card">
                <div class="flex border-b border-line">
                    <button type="button" @click="mode='manual'"
                        :class="mode === 'manual' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition">
                        Input Manual
                    </button>
                    <button type="button" @click="mode='lengkap'"
                        :class="mode === 'lengkap' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition">
                        Input Lengkap
                    </button>
                    <button type="button" @click="mode='excel'"
                        :class="mode === 'excel' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition">
                        Upload Excel
                    </button>
                    <button type="button" @click="mode='pdf'"
                        :class="mode === 'pdf' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition">
                        PDF FFS
                    </button>
                    <button type="button" @click="mode='ai'"
                        :class="mode === 'ai' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition">
                        Analisa AI
                    </button>
                    <button type="button" @click="mode='ai-plus'"
                        :class="mode === 'ai-plus' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition">
                        Analisa AI Plus
                    </button>
                    <button type="button" @click="mode='link-website'"
                        :class="mode === 'link-website' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition whitespace-nowrap">
                        Link Website
                    </button>
                </div>

                {{-- TAB: MANUAL --}}
                <div x-show="mode==='manual'" class="p-6 space-y-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="nama_reksa_dana_manual" value="Nama Reksa Dana" />
                            <x-text-input id="nama_reksa_dana_manual" name="nama_reksa_dana" type="text"
                                class="mt-1 block w-full" x-model="namaReksaDana" />
                        </div>
                        <div>
                            <x-input-label for="total_aum_manual" value="Total AUM (Rp)" />
                            <x-text-input id="total_aum_manual" name="total_aum" type="number" step="0.01"
                                class="mt-1 block w-full" x-model="totalAum" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="unit_penyertaan_manual" value="Jumlah Unit Penyertaan" />
                            <x-text-input id="unit_penyertaan_manual" name="unit_penyertaan" type="number"
                                step="0.0001" class="mt-1 block w-full" x-model="unitPenyertaan" />
                            <x-input-error :messages="$errors->get('unit_penyertaan')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="nab_per_unit_manual" value="NAB/UP" />
                            <x-text-input id="nab_per_unit_manual" name="nab_per_unit" type="number" step="0.000001"
                                class="mt-1 block w-full" x-model="nabPerUnit" />
                            <x-input-error :messages="$errors->get('nab_per_unit')" class="mt-1" />
                        </div>
                        <div x-show="jenisLaporan === 'kalender_ffs'">
                            <x-input-label value="Kalender FFS" />
                            <div class="mt-1 grid grid-cols-2 gap-2">
                                <select x-model="ffsBulan"
                                    class="border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20"
                                    aria-label="Bulan FFS">
                                    <option value="">Bulan</option>
                                    @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $bulan)
                                        <option value="{{ $index + 1 }}">{{ $bulan }}</option>
                                    @endforeach
                                </select>
                                <input type="number" min="2000" max="2100" x-model="ffsTahun"
                                    class="border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20"
                                    placeholder="2026" />
                            </div>
                        </div>
                        <div x-show="jenisLaporan === 'laporan_tahunan'" x-cloak>
                            <x-input-label for="tahun_laporan_manual" value="Tahun" />
                            <x-text-input id="tahun_laporan_manual" name="tahun_laporan" type="text" maxlength="4"
                                pattern="[0-9]{4}" placeholder="2025" class="mt-1 block w-full"
                                x-model="tahunLaporan" />
                        </div>
                    </div>

                    {{-- Rasio Keuangan --}}
                    <div x-show="jenisLaporan === 'laporan_tahunan'" x-cloak class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Rasio Keuangan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="total_hasil_investasi" value="Total Hasil Investasi (%)" />
                                <x-text-input id="total_hasil_investasi" name="total_hasil_investasi" type="number"
                                    step="0.01" class="mt-1 block w-full" x-model="totalHasilInvestasi" />
                            </div>
                            <div>
                                <x-input-label for="hasil_investasi_setelah_biaya"
                                    value="Hasil Investasi Setelah Biaya Pemasaran (%)" />
                                <x-text-input id="hasil_investasi_setelah_biaya" name="hasil_investasi_setelah_biaya"
                                    type="number" step="0.01" class="mt-1 block w-full"
                                    x-model="hasilInvestasiSetelahBiaya" />
                            </div>
                            <div>
                                <x-input-label for="biaya_operasi_lengkap" value="Biaya Operasi (%)" />
                                <x-text-input id="biaya_operasi_lengkap" name="biaya_operasi" type="number"
                                    step="0.01" class="mt-1 block w-full" x-model="biayaOperasi" />
                            </div>
                            <div>
                                <x-input-label for="portfolio_turnover_lengkap" value="Portfolio Turnover Ratio" />
                                <x-text-input id="portfolio_turnover_lengkap" name="portfolio_turnover_ratio"
                                    type="number" step="0.01" class="mt-1 block w-full"
                                    x-model="portfolioTurnover" />
                            </div>
                            <div>
                                <x-input-label for="persentase_pph" value="Persentase Penghasilan Kena Pajak (%)" />
                                <x-text-input id="persentase_pph" name="persentase_pph" type="number"
                                    step="0.01" class="mt-1 block w-full" x-model="persentasePph" />
                            </div>
                        </div>
                    </div>

                    {{-- Fair Value --}}
                    <div x-show="jenisLaporan === 'laporan_tahunan'" x-cloak class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Fair Value / Pengukuran Nilai Wajar</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="fair_value_level_1" value="Level 1 (Rp)" />
                                <x-text-input id="fair_value_level_1" name="fair_value_level_1" type="number"
                                    step="0.01" class="mt-1 block w-full" x-model="fairValueLevel1" />
                            </div>
                            <div>
                                <x-input-label for="fair_value_level_2" value="Level 2 (Rp)" />
                                <x-text-input id="fair_value_level_2" name="fair_value_level_2" type="number"
                                    step="0.01" class="mt-1 block w-full" x-model="fairValueLevel2" />
                            </div>
                            <div>
                                <x-input-label for="fair_value_level_3" value="Level 3 (Rp)" />
                                <x-text-input id="fair_value_level_3" name="fair_value_level_3" type="number"
                                    step="0.01" class="mt-1 block w-full" x-model="fairValueLevel3" />
                            </div>
                        </div>
                    </div>

                    {{-- Unit Penyertaan --}}
                    <div x-show="jenisLaporan === 'laporan_tahunan'" x-cloak class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Unit Penyertaan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="unit_milik_investor" value="Unit Milik Investor" />
                                <x-text-input id="unit_milik_investor" name="unit_milik_investor" type="number"
                                    step="0.0001" class="mt-1 block w-full" x-model="unitMilikInvestor" />
                            </div>
                            <div>
                                <x-input-label for="unit_milik_mi" value="Unit Milik Manajer Investasi" />
                                <x-text-input id="unit_milik_mi" name="unit_milik_mi" type="number"
                                    step="0.0001" class="mt-1 block w-full" x-model="unitMilikMi" />
                            </div>
                            <div>
                                <x-input-label for="total_unit_beredar" value="Total Unit Beredar" />
                                <x-text-input id="total_unit_beredar" name="total_unit_beredar" type="number"
                                    step="0.0001" class="mt-1 block w-full" x-model="totalUnitBeredar" />
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Reksa Dana --}}
                    <div x-show="false" class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Informasi Reksa Dana</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="benchmark_manual" value="Benchmark" />
                                <x-text-input id="benchmark_manual" name="benchmark" type="text"
                                    class="mt-1 block w-full" x-model="benchmark" />
                            </div>
                            <div>
                                <x-input-label for="manajer_investasi_manual" value="Manajer Investasi" />
                                <x-text-input id="manajer_investasi_manual" name="manajer_investasi" type="text"
                                    class="mt-1 block w-full" x-model="manajerInvestasi" />
                            </div>
                            <div>
                                <x-input-label for="bank_kustodian_manual" value="Bank Kustodian" />
                                <x-text-input id="bank_kustodian_manual" name="bank_kustodian" type="text"
                                    class="mt-1 block w-full" x-model="bankKustodian" />
                            </div>
                            <div>
                                <x-input-label for="tanggal_peluncuran_manual" value="Tanggal Peluncuran" />
                                <x-text-input id="tanggal_peluncuran_manual" name="tanggal_peluncuran" type="date"
                                    class="mt-1 block w-full" x-model="tanggalPeluncuran" />
                            </div>
                            <div>
                                <x-input-label for="mata_uang_manual" value="Mata Uang" />
                                <x-text-input id="mata_uang_manual" name="mata_uang" type="text"
                                    class="mt-1 block w-full" x-model="mataUang" />
                            </div>
                        </div>
                    </div>

                    {{-- Kinerja --}}
                    <div x-show="false" class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Kinerja</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="return_ytd_manual" value="Return YTD (%)" />
                                <x-text-input id="return_ytd_manual" name="return_ytd" type="number" step="0.01"
                                    class="mt-1 block w-full" x-model="returnYtd" />
                            </div>
                            <div>
                                <x-input-label for="return_1y_manual" value="Return 1 Tahun (%)" />
                                <x-text-input id="return_1y_manual" name="return_1y" type="number" step="0.01"
                                    class="mt-1 block w-full" x-model="return1y" />
                            </div>
                        </div>
                    </div>

                    {{-- Rasio Keuangan --}}
                    <div x-show="false" class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Rasio Keuangan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="total_return_manual" value="Total Return (%)" />
                                <x-text-input id="total_return_manual" name="total_return" type="number" step="0.01"
                                    class="mt-1 block w-full" x-model="totalReturn" />
                            </div>
                            <div>
                                <x-input-label for="biaya_operasi_manual" value="Biaya Operasi (%)" />
                                <x-text-input id="biaya_operasi_manual" name="biaya_operasi" type="number"
                                    step="0.01" class="mt-1 block w-full" x-model="biayaOperasi" />
                            </div>
                            <div>
                                <x-input-label for="portfolio_turnover_manual" value="Portfolio Turnover Ratio" />
                                <x-text-input id="portfolio_turnover_manual" name="portfolio_turnover_ratio"
                                    type="number" step="0.01" class="mt-1 block w-full"
                                    x-model="portfolioTurnover" />
                            </div>
                        </div>
                    </div>

                    {{-- Biaya --}}
                    <div x-show="false" class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Biaya</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="management_fee_manual" value="Management Fee (%)" />
                                <x-text-input id="management_fee_manual" name="management_fee" type="number"
                                    step="0.01" class="mt-1 block w-full" x-model="managementFee" />
                            </div>
                            <div>
                                <x-input-label for="custodian_fee_manual" value="Custodian Fee (%)" />
                                <x-text-input id="custodian_fee_manual" name="custodian_fee" type="number"
                                    step="0.01" class="mt-1 block w-full" x-model="custodianFee" />
                                {{-- ponytail: duplicated as BK Fee in Informasi Reksa Dana (hidden for kalender_ffs). --}}
                            </div>
                        </div>
                    </div>

                    <div x-show="jenisLaporan === 'kalender_ffs'">
                        @include('analisa.partials.form-alokasi-aset')
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-primary text-sm">Komposisi Sektor</h4>
                            <button type="button" @click="addRow('sektor')"
                                class="text-xs text-primary hover:underline">+
                                Tambah Baris</button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(row, i) in sektor" :key="i">
                                <div class="flex gap-2 items-center">
                                    <input type="text" :name="`sektor[${i}][nama_sektor]`" x-model="row.nama_sektor"
                                        placeholder="Nama Sektor"
                                        class="flex-1 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                                    <input type="number" :name="`sektor[${i}][bobot]`" x-model="row.bobot"
                                        placeholder="Bobot %" step="0.01"
                                        class="w-28 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                                    <button type="button" @click="removeRow('sektor', i)"
                                        class="text-red-400 hover:text-red-600 px-1">✕</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="jenisLaporan === 'kalender_ffs'">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-primary text-sm">Daftar Efek</h4>
                            <button type="button" @click="addRow('efek')" class="text-xs text-primary hover:underline">+
                                Tambah Baris</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Efek</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 1 Thn</th>
                                        <th class="text-center px-2 py-2 text-xs font-semibold text-muted">Top 10</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in efek" :key="i">
                                        <tr>
                                            <td class="px-1 py-1"><input type="text" :name="`efek[${i}][kode_efek]`"
                                                    x-model="row.kode_efek" placeholder="BBCA"
                                                    @blur="lookupKodeEfek(row.kode_efek, i)"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="text" :name="`efek[${i}][nama_efek]`"
                                                    x-model="row.nama_efek" placeholder="Nama Efek"
                                                    class="w-40 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][bobot]`"
                                                    x-model="row.bobot" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_1y]`"
                                                    x-model="row.return_1y" step="0.0001"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1 text-center"><input type="checkbox"
                                                    :name="`efek[${i}][top_10]`" x-model="row.top_10" value="1"
                                                    class="rounded border-gray-300 text-primary focus:ring-primary" /></td>
                                            <td class="px-1 py-1"><button type="button" @click="removeRow('efek', i)"
                                                    class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Laporan Keuangan --}}
                    <div x-show="jenisLaporan === 'laporan_tahunan'" x-cloak class="space-y-8">

                        {{-- CARD 1: Aset --}}
                        <div class="border rounded-lg p-4 bg-white shadow-sm">
                            <h4 class="font-semibold text-primary text-sm mb-3">Aset</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#f8fafc]">
                                        <tr>
                                            <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                            <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <th class="text-right px-2 py-2 text-xs font-semibold text-muted">
                                                    <div class="flex items-center gap-1 justify-end">
                                                        <span x-text="t"></span>
                                                        <button @click="removeTahun(i)" class="text-red-400 text-xs hover:text-red-600 leading-none">&times;</button>
                                                    </div>
                                                </th>
                                            </template>
                                            <th class="text-right px-2 py-2"><button @click="addTahun()" class="text-xs text-primary hover:underline whitespace-nowrap">+ Tahun Sebelumnya</button></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Portofolio Efek</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="portofolio_efek" x-model="portofolioEfek" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'portofolioEfek')" @input="setTahunData(t, 'portofolioEfek', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Instrumen Pasar Uang</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="instrumen_pasar_uang" x-model="instrumenPasarUang" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'instrumenPasarUang')" @input="setTahunData(t, 'instrumenPasarUang', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr class="bg-gray-50/50">
                                            <td class="px-3 py-2 text-gray-700">Total Portofolio Efek</td>
                                            <td class="px-3 py-2 text-right font-mono text-gray-700" x-text="formatNumber(getTotalPortofolioEfek())">0</td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTotalPortofolioEfekTahun(t))">0</td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Kas dan Bank</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="kas_dan_bank" x-model="kasDanBank" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'kasDanBank')" @input="setTahunData(t, 'kasDanBank', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Piutang Bunga</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="piutang_bunga" x-model="piutangBunga" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'piutangBunga')" @input="setTahunData(t, 'piutangBunga', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Piutang Dividen</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="piutang_dividen" x-model="piutangDividen" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'piutangDividen')" @input="setTahunData(t, 'piutangDividen', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Piutang Lain-lain</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="piutang_lain" x-model="piutangLain" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'piutangLain')" @input="setTahunData(t, 'piutangLain', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Piutang Transaksi Efek</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="piutang_transaksi_efek" x-model="piutangTransaksiEfek" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'piutangTransaksiEfek')" @input="setTahunData(t, 'piutangTransaksiEfek', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Piutang Bunga dan Dividen</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="piutang_bunga_dan_dividen" x-model="piutangBungaDanDividen" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'piutangBungaDanDividen')" @input="setTahunData(t, 'piutangBungaDanDividen', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr class="font-semibold bg-gray-50">
                                            <td class="px-3 py-2 text-gray-800">Total Aset</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="total_aset" x-model="totalAset" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'totalAset')" @input="setTahunData(t, 'totalAset', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- CARD 2: Liabilitas --}}
                        <div class="border rounded-lg p-4 bg-white shadow-sm">
                            <h4 class="font-semibold text-primary text-sm mb-3">Liabilitas</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#f8fafc]">
                                        <tr>
                                            <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                            <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <th class="text-right px-2 py-2 text-xs font-semibold text-muted">
                                                    <div class="flex items-center gap-1 justify-end">
                                                        <span x-text="t"></span>
                                                        <button @click="removeTahun(i)" class="text-red-400 text-xs hover:text-red-600 leading-none">&times;</button>
                                                    </div>
                                                </th>
                                            </template>
                                            <th class="text-right px-2 py-2"><button @click="addTahun()" class="text-xs text-primary hover:underline whitespace-nowrap">+ Tahun Sebelumnya</button></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Utang Pajak</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="utang_pajak" x-model="utangPajak" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'utangPajak')" @input="setTahunData(t, 'utangPajak', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Utang Lain-lain</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="utang_lain" x-model="utangLain" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'utangLain')" @input="setTahunData(t, 'utangLain', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Uang Muka Diterima</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="uang_muka_diterima" x-model="uangMukaDiterima" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'uangMukaDiterima')" @input="setTahunData(t, 'uangMukaDiterima', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Liabilitas Pembelian Kembali</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="liabilitas_pembelian_kembali" x-model="liabilitasPembelianKembali" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'liabilitasPembelianKembali')" @input="setTahunData(t, 'liabilitasPembelianKembali', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Beban Akrual</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="beban_akrual" x-model="bebanAkrual" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'bebanAkrual')" @input="setTahunData(t, 'bebanAkrual', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Liabilitas Atas Biaya</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="liabilitas_atas_biaya" x-model="liabilitasAtasBiaya" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'liabilitasAtasBiaya')" @input="setTahunData(t, 'liabilitasAtasBiaya', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Pembelian Kembali Unit Penyertaan</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="pembelian_kembali_unit_penyertaan" x-model="pembelianKembaliUnit" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'pembelianKembaliUnit')" @input="setTahunData(t, 'pembelianKembaliUnit', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Utang Pajak Lainnya</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="utang_pajak_lainnya" x-model="utangPajakLainnya" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'utangPajakLainnya')" @input="setTahunData(t, 'utangPajakLainnya', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr class="font-semibold bg-gray-50">
                                            <td class="px-3 py-2 text-gray-800">Total Liabilitas</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="total_liabilitas" x-model="totalLiabilitas" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'totalLiabilitas')" @input="setTahunData(t, 'totalLiabilitas', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                    <tr class="font-semibold bg-blue-50/50">
                                        <td class="px-3 py-2 text-gray-800">Nilai Aset Bersih</td>
                                        <td class="px-3 py-2"><input type="number" step="0.01" name="nilai_aset_bersih" x-model="nilaiAsetBersih" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- CARD 3: Pendapatan --}}
                        <div class="border rounded-lg p-4 bg-white shadow-sm">
                            <h4 class="font-semibold text-primary text-sm mb-3">Pendapatan</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#f8fafc]">
                                        <tr>
                                            <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                            <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <th class="text-right px-2 py-2 text-xs font-semibold text-muted">
                                                    <div class="flex items-center gap-1 justify-end">
                                                        <span x-text="t"></span>
                                                        <button @click="removeTahun(i)" class="text-red-400 text-xs hover:text-red-600 leading-none">&times;</button>
                                                    </div>
                                                </th>
                                            </template>
                                            <th class="text-right px-2 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Pendapatan Bunga</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="pendapatan_bunga" x-model="pendapatanBunga" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'pendapatanBunga')" @input="setTahunData(t, 'pendapatanBunga', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Pendapatan Dividen</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="pendapatan_dividen" x-model="pendapatanDividen" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'pendapatanDividen')" @input="setTahunData(t, 'pendapatanDividen', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Pendapatan Investasi</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="pendapatan_investasi" x-model="pendapatanInvestasi" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'pendapatanInvestasi')" @input="setTahunData(t, 'pendapatanInvestasi', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Keuntungan Terealisasi (Gain Realized)</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="gain_realized" x-model="gainRealized" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'gainRealized')" @input="setTahunData(t, 'gainRealized', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Keuntungan Belum Terealisasi (Gain Unrealized)</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="gain_unrealized" x-model="gainUnrealized" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'gainUnrealized')" @input="setTahunData(t, 'gainUnrealized', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Pendapatan Lainnya</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="pendapatan_lainnya" x-model="pendapatanLainnya" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'pendapatanLainnya')" @input="setTahunData(t, 'pendapatanLainnya', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                    <tr class="font-semibold bg-blue-50/50">
                                        <td class="px-3 py-2 text-gray-800">Total Pendapatan</td>
                                        <td class="px-3 py-2"><input type="number" step="0.01" name="total_pendapatan" x-model="totalPendapatan" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- CARD 4: Beban --}}
                        <div class="border rounded-lg p-4 bg-white shadow-sm">
                            <h4 class="font-semibold text-primary text-sm mb-3">Beban</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#f8fafc]">
                                        <tr>
                                            <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                            <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <th class="text-right px-2 py-2 text-xs font-semibold text-muted">
                                                    <div class="flex items-center gap-1 justify-end">
                                                        <span x-text="t"></span>
                                                        <button @click="removeTahun(i)" class="text-red-400 text-xs hover:text-red-600 leading-none">&times;</button>
                                                    </div>
                                                </th>
                                            </template>
                                            <th class="text-right px-2 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Beban Manajer Investasi</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="beban_mi" x-model="bebanMi" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'bebanMi')" @input="setTahunData(t, 'bebanMi', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Beban Kustodian</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="beban_kustodian" x-model="bebanKustodian" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'bebanKustodian')" @input="setTahunData(t, 'bebanKustodian', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Beban Investasi</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="beban_investasi" x-model="bebanInvestasi" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'bebanInvestasi')" @input="setTahunData(t, 'bebanInvestasi', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Beban Pengelolaan Investasi</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="beban_pengelolaan_investasi" x-model="bebanPengelolaanInvestasi" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'bebanPengelolaanInvestasi')" @input="setTahunData(t, 'bebanPengelolaanInvestasi', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Beban Lain-lain</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="beban_lain" x-model="bebanLain" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'bebanLain')" @input="setTahunData(t, 'bebanLain', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Total Beban</td>
                                        <td class="px-3 py-2"><input type="number" step="0.01" name="total_beban" x-model="totalBeban" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Laba / (Rugi) Sebelum Pajak</td>
                                        <td class="px-3 py-2"><input type="number" step="0.01" name="laba_sebelum_pajak" x-model="labaSebelumPajak" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Beban Pajak Penghasilan</td>
                                        <td class="px-3 py-2"><input type="number" step="0.01" name="beban_pajak_penghasilan" x-model="bebanPajakPenghasilan" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                    </tr>
                                    <tr class="font-semibold bg-gray-50">
                                        <td class="px-3 py-2 text-gray-800">Laba / (Rugi) Tahun Berjalan</td>
                                        <td class="px-3 py-2"><input type="number" step="0.01" name="laba_bersih_tahun_berjalan" x-model="labaBersihTahunBerjalan" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Penghasilan / (Beban) Komprehensif Lain Tahun Berjalan Setelah Pajak</td>
                                        <td class="px-3 py-2"><input type="number" step="0.01" name="penghasilan_komprehensif_lain_setelah_pajak" x-model="penghasilanKomprehensifLainSetelahPajak" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                    </tr>
                                    <tr class="font-semibold bg-blue-50/50">
                                        <td class="px-3 py-2 text-gray-800">Total Penghasilan Komprehensif Tahun Berjalan</td>
                                        <td class="px-3 py-2"><input type="number" step="0.01" name="penghasilan_komprehensif_tahun_berjalan" x-model="penghasilanKomprehensifTahunBerjalan" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- CARD 5: Arus Kas Operasi --}}
                        <div class="border rounded-lg p-4 bg-white shadow-sm">
                            <h4 class="font-semibold text-primary text-sm mb-3">Arus Kas Operasi</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#f8fafc]">
                                        <tr>
                                            <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                            <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <th class="text-right px-2 py-2 text-xs font-semibold text-muted">
                                                    <div class="flex items-center gap-1 justify-end">
                                                        <span x-text="t"></span>
                                                        <button @click="removeTahun(i)" class="text-red-400 text-xs hover:text-red-600 leading-none">&times;</button>
                                                    </div>
                                                </th>
                                            </template>
                                            <th class="text-right px-2 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Arus Kas Operasi</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="arus_kas_operasi" x-model="arusKasOperasi" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'arusKasOperasi')" @input="setTahunData(t, 'arusKasOperasi', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Pembelian Efek Ekuitas</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="pembelian_efek_ekuitas" x-model="pembelianEfekEkuitas" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'pembelianEfekEkuitas')" @input="setTahunData(t, 'pembelianEfekEkuitas', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Penjualan Efek Ekuitas</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="penjualan_efek_ekuitas" x-model="penjualanEfekEkuitas" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'penjualanEfekEkuitas')" @input="setTahunData(t, 'penjualanEfekEkuitas', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Penerimaan Bunga Deposito</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="penerimaan_bunga_deposito" x-model="penerimaanBungaDeposito" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'penerimaanBungaDeposito')" @input="setTahunData(t, 'penerimaanBungaDeposito', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Penerimaan Bunga Jasa Giro</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="penerimaan_bunga_jasa_giro" x-model="penerimaanBungaJasaGiro" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'penerimaanBungaJasaGiro')" @input="setTahunData(t, 'penerimaanBungaJasaGiro', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Penerimaan Dividen Kas</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="penerimaan_dividen_kas" x-model="penerimaanDividenKas" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'penerimaanDividenKas')" @input="setTahunData(t, 'penerimaanDividenKas', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Pembayaran Jasa Pengelolaan</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="pembayaran_jasa_pengelolaan" x-model="pembayaranJasaPengelolaan" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'pembayaranJasaPengelolaan')" @input="setTahunData(t, 'pembayaranJasaPengelolaan', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Pembayaran Jasa Kustodian</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="pembayaran_jasa_kustodian" x-model="pembayaranJasaKustodian" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'pembayaranJasaKustodian')" @input="setTahunData(t, 'pembayaranJasaKustodian', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Pembayaran Beban Lain</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="pembayaran_beban_lain_arus" x-model="pembayaranBebanLainArus" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'pembayaranBebanLainArus')" @input="setTahunData(t, 'pembayaranBebanLainArus', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr class="font-semibold bg-gray-50">
                                            <td class="px-3 py-2 text-gray-800">Kas Bersih Aktivitas Operasi</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="kas_bersih_aktivitas_operasi" x-model="kasBersihAktivitasOperasi" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'kasBersihAktivitasOperasi')" @input="setTahunData(t, 'kasBersihAktivitasOperasi', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- CARD 6: Arus Kas Pendanaan --}}
                        <div class="border rounded-lg p-4 bg-white shadow-sm">
                            <h4 class="font-semibold text-primary text-sm mb-3">Arus Kas Pendanaan</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#f8fafc]">
                                        <tr>
                                            <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                            <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <th class="text-right px-2 py-2 text-xs font-semibold text-muted">
                                                    <div class="flex items-center gap-1 justify-end">
                                                        <span x-text="t"></span>
                                                        <button @click="removeTahun(i)" class="text-red-400 text-xs hover:text-red-600 leading-none">&times;</button>
                                                    </div>
                                                </th>
                                            </template>
                                            <th class="text-right px-2 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Arus Kas Pendanaan</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="arus_kas_pendanaan" x-model="arusKasPendanaan" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'arusKasPendanaan')" @input="setTahunData(t, 'arusKasPendanaan', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Penerimaan Penjualan Unit</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="penerimaan_penjualan_unit" x-model="penerimaanPenjualanUnit" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'penerimaanPenjualanUnit')" @input="setTahunData(t, 'penerimaanPenjualanUnit', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Pembayaran Pembelian Kembali Unit</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="pembayaran_pembelian_kembali_unit" x-model="pembayaranPembelianKembaliUnit" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'pembayaranPembelianKembaliUnit')" @input="setTahunData(t, 'pembayaranPembelianKembaliUnit', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr class="font-semibold bg-gray-50">
                                            <td class="px-3 py-2 text-gray-800">Kas Bersih Aktivitas Pendanaan</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="kas_bersih_aktivitas_pendanaan" x-model="kasBersihAktivitasPendanaan" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'kasBersihAktivitasPendanaan')" @input="setTahunData(t, 'kasBersihAktivitasPendanaan', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Kenaikan Kas dan Setara Kas</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="kenaikan_kas_setara_kas" x-model="kenaikanKasSetaraKas" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'kenaikanKasSetaraKas')" @input="setTahunData(t, 'kenaikanKasSetaraKas', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Kas dan Setara Kas Awal Tahun</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="kas_awal_tahun" x-model="kasAwalTahun" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'kasAwalTahun')" @input="setTahunData(t, 'kasAwalTahun', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr class="font-semibold bg-gray-50">
                                            <td class="px-3 py-2 text-gray-800">Kas dan Setara Kas Akhir Tahun</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="kas_akhir_tahun" x-model="kasAkhirTahun" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'kasAkhirTahun')" @input="setTahunData(t, 'kasAkhirTahun', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Kas</td>
                                        <td class="px-3 py-2"><input type="number" step="0.01" name="kas" x-model="kas" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Deposito Berjangka</td>
                                        <td class="px-3 py-2"><input type="number" step="0.01" name="deposito_berjangka" x-model="depositoBerjangka" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                    </tr>
                                    <tr class="font-semibold bg-blue-50/50">
                                        <td class="px-3 py-2 text-gray-800">Total Kas dan Setara Kas</td>
                                        <td class="px-3 py-2"><input type="number" step="0.01" name="total_kas_setara_kas" x-model="totalKasSetaraKas" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- CARD 7: Informasi Reksa Dana --}}
                        <div class="border rounded-lg p-4 bg-white shadow-sm">
                            <h4 class="font-semibold text-primary text-sm mb-3">Informasi Reksa Dana</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#f8fafc]">
                                        <tr>
                                            <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                            <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <th class="text-right px-2 py-2 text-xs font-semibold text-muted">
                                                    <div class="flex items-center gap-1 justify-end">
                                                        <span x-text="t"></span>
                                                        <button @click="removeTahun(i)" class="text-red-400 text-xs hover:text-red-600 leading-none">&times;</button>
                                                    </div>
                                                </th>
                                            </template>
                                            <th class="text-right px-2 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        <tr class="font-semibold bg-gray-50">
                                            <td class="px-3 py-2 text-gray-800">Nilai Aset Bersih (NAB)</td>
                                            <td class="px-3 py-2"><input type="number" step="0.01" name="total_aum" x-model="totalAum" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.01" :value="getTahunData(t, 'totalAum')" @input="setTahunData(t, 'totalAum', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">Total Unit Penyertaan</td>
                                            <td class="px-3 py-2"><input type="number" step="0.0001" name="unit_penyertaan" x-model="unitPenyertaan" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.0001" :value="getTahunData(t, 'unitPenyertaan')" @input="setTahunData(t, 'unitPenyertaan', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700">NAB per Unit</td>
                                            <td class="px-3 py-2"><input type="number" step="0.000001" name="nab_per_unit" x-model="nabPerUnit" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            <template x-for="(t, i) in tahunTambahan" :key="i">
                                                <td class="px-3 py-2"><input type="number" step="0.000001" :value="getTahunData(t, 'nabPerUnit')" @input="setTahunData(t, 'nabPerUnit', $event.target.value)" class="w-full text-right border-gray-300 rounded text-sm px-2 py-1 focus:border-primary focus:ring focus:ring-primary/20 font-mono" /></td>
                                            </template>
                                            <td class="px-2 py-2"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- TAB: INPUT LENGKAP --}}
                <div x-show="mode==='lengkap'" class="p-6 space-y-6">

                    {{-- Informasi Reksa Dana Card (Laporan Tahunan) --}}
                    <div x-show="jenisLaporan === 'laporan_tahunan'" class="border rounded-lg p-4 bg-white shadow-sm">
                        <h4 class="font-semibold text-primary text-sm mb-3">Informasi Reksa Dana</h4>

                        <div class="grid grid-cols-2 sm:grid-cols-6 gap-4 text-sm mb-4">
                            <div>
                                <span class="block text-xs text-muted">Nama Reksa Dana</span>
                                <span class="font-semibold" x-text="namaReksaDana || '-'"></span>
                            </div>
                            <div>
                                <span class="block text-xs text-muted">Total AUM (Rp)</span>
                                <span class="font-semibold" x-text="formatNumber(totalAum)"></span>
                            </div>
                            <div>
                                <span class="block text-xs text-muted">Jumlah Unit Penyertaan</span>
                                <span class="font-semibold" x-text="formatNumber(unitPenyertaan)"></span>
                            </div>
                            <div>
                                <span class="block text-xs text-muted">NAB/UP</span>
                                <span class="font-semibold" x-text="formatNumber(nabPerUnit)"></span>
                            </div>
                            <div>
                                <span class="block text-xs text-muted">Total Unit Beredar</span>
                                <span class="font-semibold" x-text="formatNumber(totalUnitBeredar)"></span>
                            </div>
                            <div>
                                <span class="block text-xs text-muted">Tahun</span>
                                <span class="font-semibold" x-text="tahunLaporan || '-'"></span>
                            </div>
                        </div>

                        <div class="border-t border-line pt-3 mb-3">
                            <h5 class="font-semibold text-primary text-xs mb-2">Rasio Keuangan</h5>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-sm">
                                <div>
                                    <span class="block text-xs text-muted">Total Hasil Investasi (%)</span>
                                    <span class="font-semibold" x-text="totalHasilInvestasi ? totalHasilInvestasi + '%' : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-xs text-muted">Hasil Investasi Setelah Biaya Pemasaran (%)</span>
                                    <span class="font-semibold" x-text="hasilInvestasiSetelahBiaya ? hasilInvestasiSetelahBiaya + '%' : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-xs text-muted">Biaya Operasi (%)</span>
                                    <span class="font-semibold" x-text="biayaOperasi ? biayaOperasi + '%' : '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-xs text-muted">Portfolio Turnover Ratio</span>
                                    <span class="font-semibold" x-text="portfolioTurnover ?? '-'"></span>
                                </div>
                                <div>
                                    <span class="block text-xs text-muted">Persentase Penghasilan Kena Pajak (%)</span>
                                    <span class="font-semibold" x-text="persentasePph ? persentasePph + '%' : '-'"></span>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-line pt-3 mb-3">
                            <h5 class="font-semibold text-primary text-xs mb-2">Fair Value / Pengukuran Nilai Wajar</h5>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="block text-xs text-muted">Level 1 (Rp)</span>
                                    <span class="font-semibold" x-text="formatNumber(fairValueLevel1)"></span>
                                </div>
                                <div>
                                    <span class="block text-xs text-muted">Level 2 (Rp)</span>
                                    <span class="font-semibold" x-text="formatNumber(fairValueLevel2)"></span>
                                </div>
                                <div>
                                    <span class="block text-xs text-muted">Level 3 (Rp)</span>
                                    <span class="font-semibold" x-text="formatNumber(fairValueLevel3)"></span>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-line pt-3">
                            <h5 class="font-semibold text-primary text-xs mb-2">Unit Penyertaan</h5>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="block text-xs text-muted">Unit Milik Investor</span>
                                    <span class="font-semibold" x-text="formatNumber(unitMilikInvestor)"></span>
                                </div>
                                <div>
                                    <span class="block text-xs text-muted">Unit Milik Manajer Investasi</span>
                                    <span class="font-semibold" x-text="formatNumber(unitMilikMi)"></span>
                                </div>
                                <div>
                                    <span class="block text-xs text-muted">Total Unit Beredar</span>
                                    <span class="font-semibold" x-text="formatNumber(totalUnitBeredar)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Read-only Financial Statement Cards (Lengkap tab) --}}

                    <div x-show="jenisLaporan === 'laporan_tahunan'" class="border rounded-lg p-4 bg-white shadow-sm">
                        <h4 class="font-semibold text-primary text-sm mb-3">Aset</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted"><span x-text="t"></span></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Portofolio Efek</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(portofolioEfek)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'portofolioEfek'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Instrumen Pasar Uang</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(instrumenPasarUang)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'instrumenPasarUang'))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="bg-gray-50/50">
                                        <td class="px-3 py-2 text-gray-700">Total Portofolio Efek</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTotalPortofolioEfek())">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTotalPortofolioEfekTahun(t))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Kas dan Bank</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(kasDanBank)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'kasDanBank'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Piutang Bunga</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(piutangBunga)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'piutangBunga'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Piutang Dividen</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(piutangDividen)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'piutangDividen'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Piutang Lain-lain</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(piutangLain)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'piutangLain'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Piutang Transaksi Efek</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(piutangTransaksiEfek)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'piutangTransaksiEfek'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Piutang Bunga dan Dividen</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(piutangBungaDanDividen)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'piutangBungaDanDividen'))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="font-semibold bg-gray-50">
                                        <td class="px-3 py-2 text-gray-800">Total Aset</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(totalAset)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'totalAset'))">-</td>
                                        </template>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div x-show="jenisLaporan === 'laporan_tahunan'" class="border rounded-lg p-4 bg-white shadow-sm">
                        <h4 class="font-semibold text-primary text-sm mb-3">Liabilitas</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted"><span x-text="t"></span></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Utang Pajak</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(utangPajak)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'utangPajak'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Utang Lain-lain</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(utangLain)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'utangLain'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Uang Muka Diterima</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(uangMukaDiterima)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'uangMukaDiterima'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Liabilitas Pembelian Kembali</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(liabilitasPembelianKembali)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'liabilitasPembelianKembali'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Beban Akrual</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(bebanAkrual)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'bebanAkrual'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Liabilitas Atas Biaya</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(liabilitasAtasBiaya)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'liabilitasAtasBiaya'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Pembelian Kembali Unit Penyertaan</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(pembelianKembaliUnit)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'pembelianKembaliUnit'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Utang Pajak Lainnya</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(utangPajakLainnya)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'utangPajakLainnya'))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="font-semibold bg-gray-50">
                                        <td class="px-3 py-2 text-gray-800">Total Liabilitas</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(totalLiabilitas)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'totalLiabilitas'))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="font-semibold bg-blue-50/50">
                                        <td class="px-3 py-2 text-gray-800">Nilai Aset Bersih</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(nilaiAsetBersih)">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div x-show="jenisLaporan === 'laporan_tahunan'" class="border rounded-lg p-4 bg-white shadow-sm">
                        <h4 class="font-semibold text-primary text-sm mb-3">Pendapatan</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted"><span x-text="t"></span></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Pendapatan Bunga</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(pendapatanBunga)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'pendapatanBunga'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Pendapatan Dividen</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(pendapatanDividen)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'pendapatanDividen'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Pendapatan Investasi</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(pendapatanInvestasi)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'pendapatanInvestasi'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Keuntungan Terealisasi (Gain Realized)</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(gainRealized)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'gainRealized'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Keuntungan Belum Terealisasi (Gain Unrealized)</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(gainUnrealized)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'gainUnrealized'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Pendapatan Lainnya</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(pendapatanLainnya)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'pendapatanLainnya'))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="font-semibold bg-blue-50/50">
                                        <td class="px-3 py-2 text-gray-800">Total Pendapatan</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(totalPendapatan)">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div x-show="jenisLaporan === 'laporan_tahunan'" class="border rounded-lg p-4 bg-white shadow-sm">
                        <h4 class="font-semibold text-primary text-sm mb-3">Beban</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted"><span x-text="t"></span></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Beban Manajer Investasi</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(bebanMi)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'bebanMi'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Beban Kustodian</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(bebanKustodian)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'bebanKustodian'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Beban Investasi</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(bebanInvestasi)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'bebanInvestasi'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Beban Pengelolaan Investasi</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(bebanPengelolaanInvestasi)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'bebanPengelolaanInvestasi'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Beban Lain-lain</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(bebanLain)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'bebanLain'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Total Beban</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(totalBeban)">-</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Laba / (Rugi) Sebelum Pajak</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(labaSebelumPajak)">-</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Beban Pajak Penghasilan</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(bebanPajakPenghasilan)">-</td>
                                    </tr>
                                    <tr class="font-semibold bg-gray-50">
                                        <td class="px-3 py-2 text-gray-800">Laba / (Rugi) Tahun Berjalan</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(labaBersihTahunBerjalan)">-</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Penghasilan / (Beban) Komprehensif Lain Tahun Berjalan Setelah Pajak</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(penghasilanKomprehensifLainSetelahPajak)">-</td>
                                    </tr>
                                    <tr class="font-semibold bg-blue-50/50">
                                        <td class="px-3 py-2 text-gray-800">Total Penghasilan Komprehensif Tahun Berjalan</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(penghasilanKomprehensifTahunBerjalan)">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div x-show="jenisLaporan === 'laporan_tahunan'" class="border rounded-lg p-4 bg-white shadow-sm">
                        <h4 class="font-semibold text-primary text-sm mb-3">Arus Kas Operasi</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted"><span x-text="t"></span></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Arus Kas Operasi</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(arusKasOperasi)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'arusKasOperasi'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Pembelian Efek Ekuitas</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(pembelianEfekEkuitas)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'pembelianEfekEkuitas'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Penjualan Efek Ekuitas</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(penjualanEfekEkuitas)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'penjualanEfekEkuitas'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Penerimaan Bunga Deposito</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(penerimaanBungaDeposito)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'penerimaanBungaDeposito'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Penerimaan Bunga Jasa Giro</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(penerimaanBungaJasaGiro)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'penerimaanBungaJasaGiro'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Penerimaan Dividen Kas</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(penerimaanDividenKas)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'penerimaanDividenKas'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Pembayaran Jasa Pengelolaan</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(pembayaranJasaPengelolaan)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'pembayaranJasaPengelolaan'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Pembayaran Jasa Kustodian</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(pembayaranJasaKustodian)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'pembayaranJasaKustodian'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Pembayaran Beban Lain</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(pembayaranBebanLainArus)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'pembayaranBebanLainArus'))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="font-semibold bg-gray-50">
                                        <td class="px-3 py-2 text-gray-800">Kas Bersih Aktivitas Operasi</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(kasBersihAktivitasOperasi)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'kasBersihAktivitasOperasi'))">-</td>
                                        </template>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div x-show="jenisLaporan === 'laporan_tahunan'" class="border rounded-lg p-4 bg-white shadow-sm">
                        <h4 class="font-semibold text-primary text-sm mb-3">Arus Kas Pendanaan</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted"><span x-text="t"></span></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Arus Kas Pendanaan</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(arusKasPendanaan)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'arusKasPendanaan'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Penerimaan Penjualan Unit</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(penerimaanPenjualanUnit)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'penerimaanPenjualanUnit'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Pembayaran Pembelian Kembali Unit</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(pembayaranPembelianKembaliUnit)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'pembayaranPembelianKembaliUnit'))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="font-semibold bg-gray-50">
                                        <td class="px-3 py-2 text-gray-800">Kas Bersih Aktivitas Pendanaan</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(kasBersihAktivitasPendanaan)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'kasBersihAktivitasPendanaan'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Kenaikan Kas dan Setara Kas</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(kenaikanKasSetaraKas)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'kenaikanKasSetaraKas'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Kas dan Setara Kas Awal Tahun</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(kasAwalTahun)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'kasAwalTahun'))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="font-semibold bg-gray-50">
                                        <td class="px-3 py-2 text-gray-800">Kas dan Setara Kas Akhir Tahun</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(kasAkhirTahun)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'kasAkhirTahun'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Kas</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(kas)">-</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Deposito Berjangka</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(depositoBerjangka)">-</td>
                                    </tr>
                                    <tr class="font-semibold bg-blue-50/50">
                                        <td class="px-3 py-2 text-gray-800">Total Kas dan Setara Kas</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(totalKasSetaraKas)">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Ringkasan (Informasi Reksa Dana & Laba Rugi) --}}
                    <div x-show="jenisLaporan === 'laporan_tahunan'" class="border rounded-lg p-4 bg-white shadow-sm">
                        <h4 class="font-semibold text-primary text-sm mb-3">Ringkasan</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-muted w-1/2">Item</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-muted"><span x-text="jenisLaporan === 'kalender_ffs' ? (ffsTahun || 'Tahun Berjalan') : (tahunLaporan || 'Tahun Berjalan')">Tahun Berjalan</span></th>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted"><span x-text="t"></span></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <tr class="font-semibold bg-gray-50">
                                        <td class="px-3 py-2 text-gray-800">Nilai Aset Bersih (NAB)</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(totalAum)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'totalAum'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Total Unit Penyertaan</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(unitPenyertaan)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'unitPenyertaan'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">NAB per Unit</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(nabPerUnit)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'nabPerUnit'))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="bg-gray-50/50">
                                        <td class="px-3 py-2 text-gray-700">Ekuitas (Aset - Liabilitas)</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getEkuitas())">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getEkuitasTahun(t))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="bg-gray-50/50">
                                        <td class="px-3 py-2 text-gray-700">Total Pendapatan</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTotalPendapatan())">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTotalPendapatanTahun(t))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="bg-gray-50/50">
                                        <td class="px-3 py-2 text-gray-700">Total Keuntungan Investasi</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTotalKeuntunganInvestasi())">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTotalKeuntunganInvestasiTahun(t))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="bg-gray-50/50">
                                        <td class="px-3 py-2 text-gray-700">Total Beban</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTotalBeban())">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTotalBebanTahun(t))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="font-semibold bg-blue-50/50">
                                        <td class="px-3 py-2 text-gray-800">Laba Bersih (Perhitungan)</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getLabaBersihPerhitungan())">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getLabaBersihPerhitunganTahun(t))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Laba Sebelum Pajak</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(labaSebelumPajak)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'labaSebelumPajak'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Beban Pajak Penghasilan - Bersih</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(bebanPajakPenghasilan)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'bebanPajakPenghasilan'))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="font-semibold bg-gray-50">
                                        <td class="px-3 py-2 text-gray-800">Laba Bersih Tahun Berjalan</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(labaBersihTahunBerjalan)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'labaBersihTahunBerjalan'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Penghasilan Komprehensif Lain</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(penghasilanKomprehensifLain)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'penghasilanKomprehensifLain'))">-</td>
                                        </template>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Penghasilan Komprehensif Lain Tahun Berjalan Setelah Pajak</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(penghasilanKomprehensifLainSetelahPajak)">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getTahunData(t, 'penghasilanKomprehensifLainSetelahPajak'))">-</td>
                                        </template>
                                    </tr>
                                    <tr class="font-semibold bg-blue-50/50">
                                        <td class="px-3 py-2 text-gray-800">Penghasilan Komprehensif Tahun Berjalan</td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getPenghasilanKomprehensifTahunBerjalan())">-</td>
                                        <template x-for="(t, i) in tahunTambahan" :key="i">
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatNumber(getPenghasilanKomprehensifTahunBerjalanTahun(t))">-</td>
                                        </template>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Entry fields dari Input Manual --}}
                    <div x-show="jenisLaporan === 'kalender_ffs'" class="border rounded-lg p-4 bg-white shadow-sm">
                        <h4 class="font-semibold text-primary text-sm mb-3">Data Portofolio</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label value="Nama Reksa Dana" />
                                <p class="mt-1 text-sm font-mono text-gray-700" x-text="namaReksaDana || '-'"></p>
                            </div>
                            <div>
                                <x-input-label value="Total AUM (Rp)" />
                                <p class="mt-1 text-sm font-mono text-gray-700" x-text="formatNumber(totalAum)"></p>
                            </div>
                            <div>
                                <x-input-label value="Total MarCap 10 Saham Terbesar (Rp)" />
                                <p class="mt-1 text-sm font-mono text-gray-700" x-text="formatNumber(totalMarcap10Efek)"></p>
                            </div>
                            <div>
                                <x-input-label value="Jumlah Unit Penyertaan" />
                                <p class="mt-1 text-sm font-mono text-gray-700" x-text="formatNumber(unitPenyertaan)"></p>
                            </div>
                            <div>
                                <x-input-label value="NAB/UP" />
                                <p class="mt-1 text-sm font-mono text-gray-700" x-text="formatNumber(nabPerUnit)"></p>
                            </div>
                            <div>
                                <x-input-label value="Return 1m" />
                                <label class="mt-1 inline-flex items-center gap-2">
                                    <input type="checkbox" name="return_1m_checklist" x-model="return1mChecklist" value="1"
                                        class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm text-gray-600">Checklist</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div x-show="jenisLaporan === 'kalender_ffs'">
                        <div class="flex items-center mb-3">
                            <h4 class="font-semibold text-primary text-sm">Alokasi Aset / % Portfolio</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-muted">Jenis Aset</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-muted">Persentase</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in alokasi_aset" :key="i">
                                        <tr>
                                            <td class="px-3 py-1.5"><span x-text="row.nama_aset || '-'"
                                                    class="text-gray-700"></span></td>
                                            <td class="px-3 py-1.5 text-right"><span
                                                    x-text="formatNumber(row.persentase)" class="text-gray-700"></span>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="!alokasi_aset.length">
                                        <td class="px-3 py-2 text-gray-400 italic" colspan="2">Tidak ada data alokasi
                                            aset</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center mb-3">
                            <h4 class="font-semibold text-primary text-sm">Komposisi Sektor</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-muted">Sektor</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in sektor" :key="i">
                                        <tr>
                                            <td class="px-3 py-1.5"><span x-text="row.nama_sektor || '-'"
                                                    class="text-gray-700"></span></td>
                                            <td class="px-3 py-1.5 text-right"><span x-text="formatNumber(row.bobot)"
                                                    class="text-gray-700"></span></td>
                                        </tr>
                                    </template>
                                    <tr x-show="!sektor.length">
                                        <td class="px-3 py-2 text-gray-400 italic" colspan="2">Tidak ada data sektor
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- FFS Pembanding --}}
                    <div x-show="jenisLaporan === 'kalender_ffs'" class="mb-4">
                        <template x-if="ffsPembandingOptions.length > 0">
                            <div class="flex items-center gap-2 text-sm">
                                <label for="ffs_pembanding" class="font-medium text-muted">FFS Pembanding:</label>
                                <select id="ffs_pembanding" x-model="ffsPembanding"
                                    class="border-gray-300 rounded text-sm px-3 py-1.5 focus:border-primary focus:ring focus:ring-primary/20 w-64">
                                    <option value="">Pilih FFS Pembanding</option>
                                    <template x-for="opt in ffsPembandingOptions" :key="opt.id">
                                        <option :value="opt.id" x-text="opt.label"></option>
                                    </template>
                                </select>
                                <template x-if="ffsPembanding">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                        Membandingkan dengan <strong x-text="ffsPembandingOptions.find(o => o.id === ffsPembanding)?.label"></strong>
                                    </span>
                                </template>
                                <template x-if="pembandingLoading">
                                    <span class="inline-flex items-center gap-1 text-xs text-blue-600">
                                        <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Menerapkan pembanding...
                                    </span>
                                </template>
                                <template x-if="pembandingMessage">
                                    <span class="inline-flex items-center gap-1 text-xs text-green-600 font-medium" x-text="pembandingMessage"></span>
                                </template>
                            </div>
                        </template>
                        <template x-if="ffsPembandingOptions.length === 0">
                            <p class="text-sm text-gray-400 italic">tidak ada pembanding pada ffs sebelumnya</p>
                        </template>
                    </div>

                    {{-- Efek --}}
                    <div>
                        <div class="flex items-center mb-3">
                            <h4 class="font-semibold text-primary text-sm"
                                x-text="jenisLaporan === 'laporan_tahunan' ? 'Portofolio Efek' : 'Daftar Efek'"></h4>
                        </div>

                        {{-- Read-only (laporan_tahunan) --}}
                        <div x-show="jenisLaporan === 'laporan_tahunan'" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Efek</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Sektor</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Nilai Pasar</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Harga Perolehan
                                        </th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">% thd NAB</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Kontribusi %
                                            IHSG</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Return 1M</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Return 3M</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Return 6M</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Return 1 Thn
                                        </th>
                                        <th class="text-center px-2 py-2 text-xs font-semibold text-muted">Top 10</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in efek" :key="i">
                                        <tr>
                                            <td class="px-1 py-1"><span x-text="row.kode_efek || '-'"
                                                    class="text-gray-700 text-xs"></span></td>
                                            <td class="px-1 py-1"><span x-text="row.nama_efek || '-'"
                                                    class="text-gray-700 text-xs"></span></td>
                                            <td class="px-1 py-1"><span x-text="row.sektor || '-'"
                                                    class="text-gray-700 text-xs"></span></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.bobot)"
                                                    class="text-gray-700 text-xs"></span></td>
                                            <td class="px-1 py-1 text-right"><span
                                                    x-text="formatNumber(row.nilai_pasar)"
                                                    class="text-gray-700 text-xs"></span></td>
                                            <td class="px-1 py-1 text-right"><span
                                                    x-text="formatNumber(row.harga_perolehan)"
                                                    class="text-gray-700 text-xs"></span></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.persen_nab)"
                                                    class="text-gray-700 text-xs"></span></td>
                                            <td class="px-1 py-1 text-right"><span
                                                    x-text="formatNumber(row.kontribusi_kinerja)"
                                                    class="text-gray-700 text-xs"></span></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.return_1m)"
                                                    class="text-gray-700 text-xs"></span></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.return_3m)"
                                                    class="text-gray-700 text-xs"></span></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.return_6m)"
                                                    class="text-gray-700 text-xs"></span></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.return_1y)"
                                                    class="text-gray-700 text-xs"></span></td>
                                            <td class="px-1 py-1 text-center"><span x-text="row.top_10 ? '✓' : '-'"
                                                    class="text-gray-700 text-xs"></span></td>
                                        </tr>
                                    </template>
                                    <tr x-show="!efek.length">
                                        <td class="px-3 py-2 text-gray-400 italic" colspan="13">Tidak ada data efek
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Editable (kalender_ffs) --}}
                        <div x-show="jenisLaporan === 'kalender_ffs'" class="overflow-x-auto">
                            <div class="flex justify-end mb-2">
                                <button type="button" @click="addRow('efek')" class="text-xs text-primary hover:underline">+
                                    Tambah Baris</button>
                            </div>
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Efek</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Sektor</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Nilai Pasar</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Bobot Seharusnya</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Kontribusi Return</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Bobot Seharusnya<br><span class="font-normal">(Pembanding)</span></th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Kontribusi Return<br><span class="font-normal">(Pembanding)</span></th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Return 1 Thn</th>
                                        <th class="text-center px-2 py-2 text-xs font-semibold text-muted">Top 10</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in efek" :key="i">
                                        <tr>
                                            <td class="px-1 py-1"><input type="text" :name="`efek[${i}][kode_efek]`"
                                                    x-model="row.kode_efek" placeholder="BBCA"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                                    @change.debounce.500ms="lookupEfekData(i)" />
                                            </td>
                                            <td class="px-1 py-1"><input type="text" :name="`efek[${i}][nama_efek]`"
                                                    x-model="row.nama_efek" placeholder="Nama Efek"
                                                    class="w-36 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1">
                                                <input type="hidden" :name="`efek[${i}][effect_type]`"
                                                    x-model="row.effect_type" />
                                                <input type="text" :name="`efek[${i}][sektor]`" x-model="row.sektor"
                                                    placeholder="Sektor"
                                                    class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][bobot]`"
                                                    x-model="row.bobot" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                                    @input="hitungNilaiPasarEfek(i)" />
                                            </td>
                                            <td class="px-1 py-1 text-right">
                                                <span x-text="formatNumber(row.nilai_pasar)" class="text-gray-700 text-xs"></span>
                                                <input type="hidden" :name="`efek[${i}][nilai_pasar]`" :value="row.nilai_pasar" />
                                            </td>
                                            <td class="px-1 py-1">
                                                <template x-if="ffsPembandingOptions.length > 0">
                                                    <input type="number"
                                                        :name="`efek[${i}][bobot_seharusnya]`"
                                                        x-model="row.bobot_seharusnya" step="0.01"
                                                        class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                                </template>
                                                <template x-if="ffsPembandingOptions.length === 0">
                                                    <span class="text-gray-400 italic whitespace-nowrap">tidak ada pembanding pada ffs sebelumnya</span>
                                                </template>
                                            </td>
                                            <td class="px-1 py-1">
                                                <template x-if="ffsPembandingOptions.length > 0">
                                                    <input type="number"
                                                        :name="`efek[${i}][kontribusi_return]`"
                                                        x-model="row.kontribusi_return" step="0.0001"
                                                        class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                                </template>
                                                <template x-if="ffsPembandingOptions.length === 0">
                                                    <span class="text-gray-400 italic whitespace-nowrap">tidak ada pembanding pada ffs sebelumnya</span>
                                                </template>
                                            </td>
                                            <td class="px-1 py-1 text-right">
                                                <span x-text="formatNumber(pembandingEfek[row.kode_efek]?.bobot_seharusnya)" class="text-gray-500 text-xs"></span>
                                            </td>
                                            <td class="px-1 py-1 text-right">
                                                <span x-text="formatNumber(pembandingEfek[row.kode_efek]?.kontribusi_return)" class="text-gray-500 text-xs"></span>
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_1y]`"
                                                    x-model="row.return_1y" step="0.0001"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1 text-center"><input type="checkbox"
                                                    :name="`efek[${i}][top_10]`" x-model="row.top_10" value="1"
                                                    class="rounded border-gray-300 text-primary focus:ring-primary" /></td>
                                            <td class="px-1 py-1"><button type="button" @click="removeRow('efek', i)"
                                                    class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                        </tr>
                                    </template>
                                    <tr x-show="!efek.length">
                                        <td class="px-3 py-2 text-gray-400 italic" colspan="12">Tidak ada data efek
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Kinerja Bulanan --}}
                    {{-- HIDE: section tidak ditampilkan di form create/edit, data lama tetap dipertahankan --}}
                    <div x-show="false" style="display:none">
                        <template x-for="(row, i) in kinerja" :key="i">
                            <div class="flex gap-2 items-center">
                                <input type="month" :name="`kinerja[${i}][periode]`" x-model="row.periode"
                                    class="border-gray-300 rounded-lg text-sm px-3 py-2" />
                                <input type="number" :name="`kinerja[${i}][return_pct]`" x-model="row.return_pct"
                                    placeholder="Return %" step="0.0001"
                                    class="w-36 border-gray-300 rounded-lg text-sm px-3 py-2" />
                            </div>
                        </template>
                    </div>

                    {{-- Obligasi --}}
                    <div>
                        <div class="flex items-center mb-3">
                            <h4 class="font-semibold text-primary text-sm">Obligasi</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Obligasi
                                        </th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Nilai Pasar</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">YTM (%)</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Kupon (%)</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Jatuh Tempo</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Penerbit</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">% thd NAB</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Durasi (thn)
                                        </th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Rating</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in obligasi" :key="i">
                                        <tr>
                                            <td class="px-1 py-1"><span x-text="row.kode_obligasi || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`obligasi[${i}][kode_obligasi]`" :value="row.kode_obligasi">
                                            </td>
                                            <td class="px-1 py-1"><span x-text="row.nama_obligasi || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`obligasi[${i}][nama_obligasi]`" :value="row.nama_obligasi">
                                            </td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.bobot)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`obligasi[${i}][bobot]`" :value="row.bobot"></td>
                                            <td class="px-1 py-1 text-right"><span
                                                    x-text="formatNumber(row.nilai_pasar)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`obligasi[${i}][nilai_pasar]`" :value="row.nilai_pasar"></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.ytm)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`obligasi[${i}][ytm]`" :value="row.ytm"></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.kupon)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`obligasi[${i}][kupon]`" :value="row.kupon"></td>
                                            <td class="px-1 py-1"><span x-text="row.tanggal_jatuh_tempo || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`obligasi[${i}][tanggal_jatuh_tempo]`"
                                                    :value="row.tanggal_jatuh_tempo"></td>
                                            <td class="px-1 py-1"><span x-text="row.penerbit || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`obligasi[${i}][penerbit]`" :value="row.penerbit"></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.persen_nab)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`obligasi[${i}][persen_nab]`" :value="row.persen_nab"></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.durasi)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`obligasi[${i}][durasi]`" :value="row.durasi"></td>
                                            <td class="px-1 py-1"><span x-text="row.rating || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`obligasi[${i}][rating]`" :value="row.rating"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="!obligasi.length">
                                        <td class="px-3 py-2 text-gray-400 italic" colspan="11">Tidak ada data
                                            obligasi</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Sukuk --}}
                    <div>
                        <div class="flex items-center mb-3">
                            <h4 class="font-semibold text-primary text-sm">Sukuk</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode Sukuk</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Sukuk</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Jenis</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Yield %</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Jatuh Tempo</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Rating</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">% thd NAB</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in sukuk" :key="i">
                                        <tr>
                                            <td class="px-1 py-1"><span x-text="row.kode_sukuk || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`sukuk[${i}][kode_sukuk]`" :value="row.kode_sukuk"></td>
                                            <td class="px-1 py-1"><span x-text="row.nama_sukuk || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`sukuk[${i}][nama_sukuk]`" :value="row.nama_sukuk"></td>
                                            <td class="px-1 py-1"><span x-text="row.jenis_sukuk || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`sukuk[${i}][jenis_sukuk]`" :value="row.jenis_sukuk"></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.bobot)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`sukuk[${i}][bobot]`" :value="row.bobot"></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.yield)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`sukuk[${i}][yield]`" :value="row.yield"></td>
                                            <td class="px-1 py-1"><span x-text="row.jatuh_tempo || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`sukuk[${i}][jatuh_tempo]`" :value="row.jatuh_tempo"></td>
                                            <td class="px-1 py-1"><span x-text="row.rating || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`sukuk[${i}][rating]`" :value="row.rating"></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.persen_nab)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`sukuk[${i}][persen_nab]`" :value="row.persen_nab"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="!sukuk.length">
                                        <td class="px-3 py-2 text-gray-400 italic" colspan="8">Tidak ada data sukuk
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Bank --}}
                    <div x-show="jenisLaporan === 'kalender_ffs'">
                        <div class="flex items-center mb-3">
                            <h4 class="font-semibold text-primary text-sm">Bank</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Bank</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Jenis</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Nilai Pasar</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Tingkat Bunga
                                        </th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Jangka Waktu</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">% thd NAB</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">CAR %</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">NPL %</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Klasifikasi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in bank" :key="i">
                                        <tr>
                                            <td class="px-1 py-1"><span x-text="row.nama_bank || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`bank[${i}][nama_bank]`" :value="row.nama_bank"></td>
                                            <td class="px-1 py-1"><span x-text="row.jenis_bank || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`bank[${i}][jenis_bank]`" :value="row.jenis_bank"></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.bobot)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`bank[${i}][bobot]`" :value="row.bobot"></td>
                                            <td class="px-1 py-1 text-right"><span
                                                    x-text="formatNumber(row.nilai_pasar)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`bank[${i}][nilai_pasar]`" :value="row.nilai_pasar"></td>
                                            <td class="px-1 py-1 text-right"><span
                                                    x-text="formatNumber(row.tingkat_bunga)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`bank[${i}][tingkat_bunga]`" :value="row.tingkat_bunga"></td>
                                            <td class="px-1 py-1"><span x-text="row.jangka_waktu || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`bank[${i}][jangka_waktu]`" :value="row.jangka_waktu"></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.persen_nab)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`bank[${i}][persen_nab]`" :value="row.persen_nab"></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.car)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`bank[${i}][car]`" :value="row.car"></td>
                                            <td class="px-1 py-1 text-right"><span x-text="formatNumber(row.npl)"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`bank[${i}][npl]`" :value="row.npl"></td>
                                            <td class="px-1 py-1"><span x-text="row.klasifikasi_risiko || '-'"
                                                    class="text-gray-700 text-xs"></span><input type="hidden"
                                                    :name="`bank[${i}][klasifikasi_risiko]`"
                                                    :value="row.klasifikasi_risiko"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="!bank.length">
                                        <td class="px-3 py-2 text-gray-400 italic" colspan="10">Tidak ada data bank
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Analisa Likuiditas --}}
                    <div x-show="jenisLaporan === 'kalender_ffs' || jenisLaporan === 'laporan_tahunan'">
                        <div class="flex items-center mb-3">
                            <h4 class="font-semibold text-primary text-sm">Analisa Likuiditas</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Daftar Efek</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Rata-rata Volume Transaksi Harian</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Volume Terendah</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Volume Saham</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Skenario 20% Reds</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Skenario Reds Vol. Closing (10%)</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Rasio Likuiditas Harian</th>
                                        <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Rasio Likuiditas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in likuiditas" :key="i">
                                        <tr x-show="row.kategori === 'Saham'">
                                            <td class="px-1 py-1 whitespace-nowrap">
                                                <span class="text-xs text-gray-700" x-text="row.kode_efek + ' - ' + row.nama_efek"></span>
                                                <input type="hidden" :name="`likuiditas[${i}][kategori]`" value="Saham" />
                                                <input type="hidden" :name="`likuiditas[${i}][kode_efek]`" x-model="row.kode_efek" />
                                                <input type="hidden" :name="`likuiditas[${i}][nama_efek]`" x-model="row.nama_efek" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][rata_volume_transaksi_harian]`" x-model="row.rata_volume_transaksi_harian" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][volume_terendah]`" x-model="row.volume_terendah" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][volume_saham]`" x-model="row.volume_saham" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][skenario_20_persen_reds]`" x-model="row.skenario_20_persen_reds" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][skenario_reds_closing_10]`" x-model="row.skenario_reds_closing_10" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][rasio_likuiditas_harian]`" x-model="row.rasio_likuiditas_harian" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][rasio_likuiditas]`" x-model="row.rasio_likuiditas" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                        </tr>
                                    </template>
                                    <template x-for="(row, i) in likuiditas" :key="'o'+i">
                                        <tr x-show="row.kategori === 'Obligasi'">
                                            <td class="px-1 py-1 whitespace-nowrap">
                                                <span class="text-xs text-gray-700" x-text="row.kode_efek + ' - ' + row.nama_efek"></span>
                                                <input type="hidden" :name="`likuiditas[${i}][kategori]`" value="Obligasi" />
                                                <input type="hidden" :name="`likuiditas[${i}][kode_efek]`" x-model="row.kode_efek" />
                                                <input type="hidden" :name="`likuiditas[${i}][nama_efek]`" x-model="row.nama_efek" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][rata_volume_transaksi_harian]`" x-model="row.rata_volume_transaksi_harian" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][volume_terendah]`" x-model="row.volume_terendah" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][volume_saham]`" x-model="row.volume_saham" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][skenario_20_persen_reds]`" x-model="row.skenario_20_persen_reds" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][skenario_reds_closing_10]`" x-model="row.skenario_reds_closing_10" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][rasio_likuiditas_harian]`" x-model="row.rasio_likuiditas_harian" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][rasio_likuiditas]`" x-model="row.rasio_likuiditas" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                        </tr>
                                    </template>
                                    <template x-for="(row, i) in likuiditas" :key="'b'+i">
                                        <tr x-show="row.kategori === 'Bank'">
                                            <td class="px-1 py-1 whitespace-nowrap">
                                                <span class="text-xs text-gray-700" x-text="row.kode_efek + ' - ' + row.nama_efek"></span>
                                                <input type="hidden" :name="`likuiditas[${i}][kategori]`" value="Bank" />
                                                <input type="hidden" :name="`likuiditas[${i}][kode_efek]`" x-model="row.kode_efek" />
                                                <input type="hidden" :name="`likuiditas[${i}][nama_efek]`" x-model="row.nama_efek" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][rata_volume_transaksi_harian]`" x-model="row.rata_volume_transaksi_harian" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][volume_terendah]`" x-model="row.volume_terendah" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][volume_saham]`" x-model="row.volume_saham" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][skenario_20_persen_reds]`" x-model="row.skenario_20_persen_reds" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][skenario_reds_closing_10]`" x-model="row.skenario_reds_closing_10" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][rasio_likuiditas_harian]`" x-model="row.rasio_likuiditas_harian" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            <td class="px-1 py-1"><input type="number" :name="`likuiditas[${i}][rasio_likuiditas]`" x-model="row.rasio_likuiditas" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                        </tr>
                                    </template>
                                    <tr x-show="!likuiditas.filter(r => r.kategori === 'Saham').length && !likuiditas.filter(r => r.kategori === 'Obligasi').length && !likuiditas.filter(r => r.kategori === 'Bank').length">
                                        <td class="px-3 py-2 text-gray-400 italic" colspan="8">Tidak ada data likuiditas</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Analisa Keuangan --}}
                    <div x-show="jenisLaporan === 'kalender_ffs' || jenisLaporan === 'laporan_tahunan'">
                        <div class="flex items-center mb-3">
                            <h4 class="font-semibold text-primary text-sm">Analisa Keuangan</h4>
                        </div>

                        {{-- Saham --}}
                        <div class="mb-4">
                            <h5 class="text-xs font-semibold text-muted mb-2">Saham</h5>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#f8fafc]">
                                        <tr>
                                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Daftar Efek</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">PER</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">PBV</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">ROE</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">ROA</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">NPM</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">EV/EBITDA</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">DER</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Current Ratio</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Aktivitas Lancar</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Gross Profit Margin</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Operating Profit Margin</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        <template x-for="(row, i) in keuangan" :key="'ks'+i">
                                            <tr x-show="row.kategori === 'Saham'">
                                                <td class="px-1 py-1 whitespace-nowrap">
                                                    <span class="text-xs text-gray-700" x-text="row.kode_efek + ' - ' + row.nama_efek"></span>
                                                    <input type="hidden" :name="`keuangan[${i}][kategori]`" value="Saham" />
                                                    <input type="hidden" :name="`keuangan[${i}][kode_efek]`" x-model="row.kode_efek" />
                                                    <input type="hidden" :name="`keuangan[${i}][nama_efek]`" x-model="row.nama_efek" />
                                                </td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][per]`" x-model="row.per" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][pbv]`" x-model="row.pbv" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][roe]`" x-model="row.roe" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][roa]`" x-model="row.roa" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][npm]`" x-model="row.npm" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][ev_ebitda]`" x-model="row.ev_ebitda" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][der]`" x-model="row.der" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][current_ratio]`" x-model="row.current_ratio" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][aktivitas_lancar]`" x-model="row.aktivitas_lancar" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][gross_profit_margin]`" x-model="row.gross_profit_margin" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][operating_profit_margin]`" x-model="row.operating_profit_margin" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            </tr>
                                        </template>
                                        <tr x-show="!keuangan.filter(r => r.kategori === 'Saham').length">
                                            <td class="px-3 py-2 text-gray-400 italic" colspan="12">Tidak ada data saham</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Obligasi --}}
                        <div class="mb-4">
                            <h5 class="text-xs font-semibold text-muted mb-2">Obligasi</h5>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#f8fafc]">
                                        <tr>
                                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Daftar Efek</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">YTM</th>
                                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Rating</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Kupon</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Tenor</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Durasi</th>
                                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Shadow Rating</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">DER</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Current Ratio</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Aktivitas Lancar</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Gross Profit Margin</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Operating Profit Margin</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        <template x-for="(row, i) in keuangan" :key="'ko'+i">
                                            <tr x-show="row.kategori === 'Sukuk'">
                                                <td class="px-1 py-1 whitespace-nowrap">
                                                    <span class="text-xs text-gray-700" x-text="row.kode_efek + ' - ' + row.nama_efek"></span>
                                                    <input type="hidden" :name="`keuangan[${i}][kategori]`" value="Sukuk" />
                                                    <input type="hidden" :name="`keuangan[${i}][kode_efek]`" x-model="row.kode_efek" />
                                                    <input type="hidden" :name="`keuangan[${i}][nama_efek]`" x-model="row.nama_efek" />
                                                </td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][ytm]`" x-model="row.ytm" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="text" :name="`keuangan[${i}][rating]`" x-model="row.rating" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][kupon]`" x-model="row.kupon" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][tenor]`" x-model="row.tenor" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][durasi]`" x-model="row.durasi" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="text" :name="`keuangan[${i}][shadow_rating]`" x-model="row.shadow_rating" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][der]`" x-model="row.der" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][current_ratio]`" x-model="row.current_ratio" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][aktivitas_lancar]`" x-model="row.aktivitas_lancar" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][gross_profit_margin]`" x-model="row.gross_profit_margin" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][operating_profit_margin]`" x-model="row.operating_profit_margin" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            </tr>
                                        </template>
                                        <tr x-show="!keuangan.filter(r => r.kategori === 'Obligasi').length">
                                            <td class="px-3 py-2 text-gray-400 italic" colspan="12">Tidak ada data obligasi</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                          {{-- Sukuk --}}
                        <div class="mb-4">
                            <h5 class="text-xs font-semibold text-muted mb-2">Sukuk</h5>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#f8fafc]">
                                        <tr>
                                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Daftar Efek</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">YTM</th>
                                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Rating</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Kupon</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Tenor</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Durasi</th>
                                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Shadow Rating</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">DER</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Current Ratio</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Aktivitas Lancar</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Gross Profit Margin</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Operating Profit Margin</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        <template x-for="(row, i) in keuangan" :key="'ko'+i">
                                            <tr x-show="row.kategori === 'Obligasi'">
                                                <td class="px-1 py-1 whitespace-nowrap">
                                                    <span class="text-xs text-gray-700" x-text="row.kode_efek + ' - ' + row.nama_efek"></span>
                                                    <input type="hidden" :name="`keuangan[${i}][kategori]`" value="Obligasi" />
                                                    <input type="hidden" :name="`keuangan[${i}][kode_efek]`" x-model="row.kode_efek" />
                                                    <input type="hidden" :name="`keuangan[${i}][nama_efek]`" x-model="row.nama_efek" />
                                                </td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][ytm]`" x-model="row.ytm" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="text" :name="`keuangan[${i}][rating]`" x-model="row.rating" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][kupon]`" x-model="row.kupon" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][tenor]`" x-model="row.tenor" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][durasi]`" x-model="row.durasi" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="text" :name="`keuangan[${i}][shadow_rating]`" x-model="row.shadow_rating" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][der]`" x-model="row.der" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][current_ratio]`" x-model="row.current_ratio" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][aktivitas_lancar]`" x-model="row.aktivitas_lancar" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][gross_profit_margin]`" x-model="row.gross_profit_margin" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][operating_profit_margin]`" x-model="row.operating_profit_margin" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            </tr>
                                        </template>
                                        <tr x-show="!keuangan.filter(r => r.kategori === 'Obligasi').length">
                                            <td class="px-3 py-2 text-gray-400 italic" colspan="12">Tidak ada data obligasi</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Bank --}}
                        <div>
                            <h5 class="text-xs font-semibold text-muted mb-2">Bank</h5>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#f8fafc]">
                                        <tr>
                                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Daftar Efek</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">NPL</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">CAR</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">ROE</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">ROA</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">LDR</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">NIM</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">CIR</th>
                                            <th class="text-right px-2 py-2 text-xs font-semibold text-muted">Aktivitas Lancar</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        <template x-for="(row, i) in keuangan" :key="'kb'+i">
                                            <tr x-show="row.kategori === 'Bank'">
                                                <td class="px-1 py-1 whitespace-nowrap">
                                                    <span class="text-xs text-gray-700" x-text="row.kode_efek + ' - ' + row.nama_efek"></span>
                                                    <input type="hidden" :name="`keuangan[${i}][kategori]`" value="Bank" />
                                                    <input type="hidden" :name="`keuangan[${i}][kode_efek]`" x-model="row.kode_efek" />
                                                    <input type="hidden" :name="`keuangan[${i}][nama_efek]`" x-model="row.nama_efek" />
                                                </td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][npl]`" x-model="row.npl" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][car]`" x-model="row.car" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][roe]`" x-model="row.roe" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][roa]`" x-model="row.roa" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][ldr]`" x-model="row.ldr" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][nim]`" x-model="row.nim" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][cir]`" x-model="row.cir" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                                <td class="px-1 py-1"><input type="number" :name="`keuangan[${i}][aktivitas_lancar]`" x-model="row.aktivitas_lancar" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                            </tr>
                                        </template>
                                        <tr x-show="!keuangan.filter(r => r.kategori === 'Bank').length">
                                            <td class="px-3 py-2 text-gray-400 italic" colspan="9">Tidak ada data bank</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Analisa Pengelolaan Investasi / Portofolio --}}
                    <div x-show="jenisLaporan === 'laporan_tahunan'">
                        <div class="flex items-center mb-3">
                            <h4 class="font-semibold text-primary text-sm">Analisa Pengelolaan Investasi / Portofolio</h4>
                        </div>
                        <div class="border rounded-lg p-4 bg-white shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#f8fafc]">
                                        <tr>
                                            <th class="text-left px-3 py-2 text-xs font-semibold text-muted">Analisa</th>
                                            <th class="text-right px-3 py-2 text-xs font-semibold text-muted">Hasil</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="px-3 py-2 text-muted">Fee Cost to Performance</td>
                                            <td class="px-3 py-2 text-right font-mono">
                                                <span x-text="feeCostToPerformance !== null && feeCostToPerformance !== undefined ? Number(feeCostToPerformance).toFixed(4) : '-'"></span>
                                                <input type="hidden" name="fee_cost_to_performance" :value="feeCostToPerformance">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-muted">Pendapatan terhadap NAB</td>
                                            <td class="px-3 py-2 text-right font-mono">
                                                <span x-text="pendapatanTerhadapNab !== null && pendapatanTerhadapNab !== undefined ? Number(pendapatanTerhadapNab).toFixed(4) : '-'"></span>
                                                <input type="hidden" name="pendapatan_terhadap_nab" :value="pendapatanTerhadapNab">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-muted">Beban terhadap Pendapatan</td>
                                            <td class="px-3 py-2 text-right font-mono">
                                                <span x-text="bebanTerhadapPendapatan !== null && bebanTerhadapPendapatan !== undefined ? Number(bebanTerhadapPendapatan).toFixed(4) : '-'"></span>
                                                <input type="hidden" name="beban_terhadap_pendapatan" :value="bebanTerhadapPendapatan">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-muted">Pengelolaan Investasi terhadap Pendapatan</td>
                                            <td class="px-3 py-2 text-right font-mono">
                                                <span x-text="pengelolaanInvestasiTerhadapPendapatan !== null && pengelolaanInvestasiTerhadapPendapatan !== undefined ? Number(pengelolaanInvestasiTerhadapPendapatan).toFixed(4) : '-'"></span>
                                                <input type="hidden" name="pengelolaan_investasi_terhadap_pendapatan" :value="pengelolaanInvestasiTerhadapPendapatan">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 text-muted">Transaction Profit terhadap NAB</td>
                                            <td class="px-3 py-2 text-right font-mono">
                                                <span x-text="transactionProfitTerhadapNab !== null && transactionProfitTerhadapNab !== undefined ? Number(transactionProfitTerhadapNab).toFixed(4) : '-'"></span>
                                                <input type="hidden" name="transaction_profit_terhadap_nab" :value="transactionProfitTerhadapNab">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- TAB: EXCEL --}}
                <div x-show="mode==='excel'" class="p-6 space-y-5">
                    <div
                        class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            Download template Excel terlebih dahulu, isi data sesuai format, lalu upload.
                            <a href="{{ $formRoutes['template'] }}" class="font-semibold underline ml-1">Download
                                Template</a>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="file_excel" value="Upload File Excel (.xlsx)" />
                        <input id="file_excel" name="file_excel" type="file" accept=".xlsx,.xls" @change="importExcel($event)"
                            class="mt-1 block w-full text-sm text-muted border border-gray-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-primary file:text-white hover:file:bg-primary/90 cursor-pointer" />
                        <div x-show="importExcelLoading" class="flex items-center gap-2 mt-2 text-sm text-muted">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Membaca file Excel...
                        </div>
                        <div x-show="importExcelMessage" x-cloak
                            :class="{'text-green-600': importExcelOk, 'text-red-600': !importExcelOk}"
                            class="mt-2 text-sm" x-text="importExcelMessage"></div>
                        <x-input-error :messages="$errors->get('file_excel')" class="mt-1" />
                        <p class="text-xs text-muted mt-1">Format: .xlsx atau .xls, maks 5MB. Sheet: Sektor, Efek,
                            Kinerja, Obligasi, Sukuk, Bank.</p>
                    </div>
                </div>

                {{-- TAB: PDF FFS --}}
                <div x-show="mode==='pdf'" class="p-6 space-y-5">
                    @env('local')
                    <div x-show="jenisLaporan !== 'kalender_ffs'"
                        class="border-2 border-dashed border-yellow-400 rounded-lg p-4 bg-yellow-50 space-y-2">
                        <h4 class="font-semibold text-sm text-yellow-800">🧪 JSON Test Tool (Local Only)</h4>
                        <input type="file" accept=".json" @change="loadTestJson($event)"
                            class="block w-full text-xs text-muted border border-yellow-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-yellow-600 file:text-white hover:file:bg-yellow-700 cursor-pointer" />
                        <p class="text-xs text-yellow-700">Upload file JSON — tabel akan tampil, file hasil langsung terdownload.</p>
                        <div x-show="testJsonError" class="text-xs text-red-600" x-text="testJsonError"></div>
                    </div>
                    @endenv

                    <div
                        class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <div>
                            Upload PDF Fund Fact Sheet (FFS) untuk mengekstrak data secara otomatis.
                            Data akan mengisi form di tab <strong>Input Manual</strong>.
                        </div>
                    </div>

                    {{-- Dokumen Tersimpan --}}
                    <div class="border border-line rounded-lg p-4 bg-gray-50/50 space-y-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <h4 class="font-medium text-sm text-primary">Dokumen Tersimpan</h4>
                        </div>
                        <p class="text-xs text-muted">
                            Menampilkan
                            <strong
                                x-text="jenisLaporan === 'laporan_tahunan' ? 'Laporan Tahunan / Prospektus' : 'FFS'"></strong>
                            <template x-if="jenisLaporan === 'kalender_ffs' && ffsBulan && ffsTahun">
                                <span> untuk <strong><span
                                            x-text="['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][ffsBulan-1]"></span>
                                        <span x-text="ffsTahun"></span></strong></span>
                            </template>
                            <template x-if="jenisLaporan === 'laporan_tahunan' && tahunLaporan">
                                <span> untuk tahun <strong><span x-text="tahunLaporan"></span></strong></span>
                            </template>
                            dari semua Reksa Dana.
                        </p>

                        {{-- Upload PDF Baru (khusus kalender_ffs) --}}
                        <div x-show="jenisLaporan === 'kalender_ffs'" class="flex items-center gap-3">
                            <input type="file" accept=".pdf" @change="parsePdf($event)"
                                class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 file:mr-2 file:rounded file:border-0 file:bg-primary/10 file:px-2 file:py-0.5 file:text-primary file:text-xs" />
                            <span x-show="pdfLoading" class="text-xs text-muted">Parsing...</span>
                        </div>

                        <div x-show="existingDocsLoading" class="flex items-center gap-2 text-xs text-muted">
                            <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span>Memuat dokumen...</span>
                        </div>

                        <div x-show="!existingDocsLoading && !kodeReksaDana" class="text-xs text-muted italic">
                            Masukkan Kode Reksa Dana untuk melihat dokumen tersimpan
                        </div>

                        <div x-show="existingDocsLoaded && existingDocs.length === 0 && kodeReksaDana" class="text-xs text-muted italic">
                            Dokumen tidak ditemukan untuk periode yang dipilih.
                        </div>

                        <template x-if="existingDocs.length > 0">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label
                                        class="inline-flex items-center gap-1.5 text-xs text-muted cursor-pointer hover:text-foreground transition">
                                        <input type="checkbox"
                                            @change="selectedDocIds = $event.target.checked ? existingDocs.map(d => d.id) : []"
                                            :checked="selectedDocIds.length === existingDocs.length"
                                            class="rounded border-gray-300 text-primary focus:ring-primary/20">
                                        Pilih semua
                                    </label>
                                    <button type="button" @click="parseSelectedDocuments()"
                                        x-show="selectedDocIds.length > 0" :disabled="batchParsing"
                                        class="px-3 py-1 text-xs font-medium text-white bg-primary rounded-lg hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                        <span x-show="!batchParsing"
                                            x-text="'Parse ' + selectedDocIds.length + ' dokumen'"></span>
                                        <span x-show="batchParsing">Memproses <span
                                                x-text="batchParsedCount + '/' + selectedDocIds.length"></span>...</span>
                                    </button>
                                </div>

                                {{-- Layout: compact list untuk laporan_tahunan, cards untuk kalender_ffs --}}
                                <template x-if="jenisLaporan === 'laporan_tahunan'">
                                    <div class="space-y-2 max-h-48 overflow-y-auto">
                                        <template x-for="doc in existingDocs" :key="doc.id">
                                            <div class="p-2.5 bg-white rounded-lg border border-gray-200"
                                                :class="{
                                                    'border-primary/40 bg-primary/[0.03]': selectedDocIds.includes(doc
                                                        .id)
                                                }">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <input type="checkbox" :value="doc.id"
                                                            :checked="selectedDocIds.includes(doc.id)"
                                                            @change="toggleDocSelection(doc.id)"
                                                            class="shrink-0 rounded border-gray-300 text-primary focus:ring-primary/20">
                                                        <span
                                                            class="shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                            :class="{
                                                                'bg-blue-100 text-blue-700': doc
                                                                    .document_type === 'prospektus',
                                                                'bg-emerald-100 text-emerald-700': doc
                                                                    .document_type === 'ffs',
                                                                'bg-amber-100 text-amber-700': doc
                                                                    .document_type === 'laporan_tahunan',
                                                            }"
                                                            x-text="{
                                                                'prospektus': 'Prospektus',
                                                                'ffs': 'FFS',
                                                                'laporan_tahunan': 'Laporan Tahunan',
                                                            }[doc.document_type] || doc.document_type"></span>
                                                        <span class="text-sm font-medium truncate"
                                                            x-text="doc.label"></span>
                                                        <span
                                                            class="text-xs font-semibold text-muted shrink-0 hidden sm:inline"
                                                            x-text="doc.reksa_dana_nama"></span>
                                                        <span class="text-xs text-muted shrink-0 hidden sm:inline"
                                                            x-text="doc.reksa_dana_kode"></span>
                                                        <span class="text-xs text-muted shrink-0"
                                                            x-text="doc.uploaded_at"></span>
                                                    </div>
                                                    <div class="flex items-center gap-1 shrink-0" x-show="doc.url">
                                                        <a :href="doc.url" target="_blank"
                                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                            Lihat
                                                        </a>
                                                        <a :href="doc.url" download
                                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-md hover:bg-emerald-100 transition">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                            Download
                                                        </a>
                                                    </div>
                                                </div>
                                                <div x-show="doc.notes" class="mt-1.5 pl-6">
                                                    <p class="text-xs text-muted italic leading-relaxed"
                                                        x-text="doc.notes">
                                                    </p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Card layout untuk kalender_ffs --}}
                                <template x-if="jenisLaporan === 'kalender_ffs'">
                                    <div class="space-y-2 max-h-80 overflow-y-auto">
                                        <template x-for="doc in existingDocs" :key="doc.id">
                                            <div class="p-3 bg-white rounded-lg border border-gray-200"
                                                :class="{
                                                    'border-primary/40 bg-primary/[0.03]': selectedDocIds.includes(doc
                                                        .id)
                                                }">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="flex items-start gap-2 min-w-0 flex-1">
                                                        <input type="checkbox" :value="doc.id"
                                                            :checked="selectedDocIds.includes(doc.id)"
                                                            @change="toggleDocSelection(doc.id)"
                                                            class="mt-0.5 shrink-0 rounded border-gray-300 text-primary focus:ring-primary/20">
                                                        <div class="min-w-0">
                                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                                <span
                                                                    class="shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700">FFS</span>
                                                                <span class="text-sm font-semibold truncate"
                                                                    x-text="doc.label"></span>
                                                            </div>
                                                            <div
                                                                class="flex items-center gap-2 mt-0.5 text-xs text-muted">
                                                                <span x-text="doc.reksa_dana_nama"
                                                                    class="text-xs font-semibold"></span>
                                                                <span x-text="doc.reksa_dana_kode"></span>
                                                                <span class="text-[10px]">•</span>
                                                                <span x-text="doc.uploaded_at"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-1 shrink-0">
                                                        <template x-if="doc.url">
                                                            <a :href="doc.url" target="_blank"
                                                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition"
                                                                title="Lihat">
                                                                <svg class="w-3.5 h-3.5" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                </svg>
                                                            </a>
                                                        </template>
                                                        <button type="button" @click="parseSingleDocument(doc.id)"
                                                            :disabled="existingDocParsing"
                                                            class="px-2.5 py-1 text-xs font-semibold text-white bg-emerald-600 rounded-md hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                                            Parse
                                                        </button>
                                                    </div>
                                                </div>
                                                <div x-show="doc.notes" class="mt-1.5 pl-6">
                                                    <p class="text-xs text-muted italic" x-text="doc.notes"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div x-show="existingDocsLoaded && existingDocs.length === 0" class="text-xs text-muted italic">
                            Dokumen tidak ditemukan untuk periode yang dipilih.
                        </div>

                        {{-- Hasil parse (khusus kalender_ffs) --}}
                        <div x-show="jenisLaporan === 'kalender_ffs' && pdfResult" class="text-xs space-y-1">
                            <div class="px-3 py-2 rounded-lg"
                                :class="pdfSuccess ? 'bg-green-50 border border-green-200 text-green-700' :
                                    'bg-red-50 border border-red-200 text-red-700'">
                                <span x-text="pdfResult"></span>
                            </div>
                            <div x-show="pdfSuccess && pdfData" class="pt-1">
                                <button type="button" @click="parseToForms()"
                                    class="px-3 py-1.5 text-xs font-semibold text-white bg-primary rounded-lg hover:bg-primary/90 transition">
                                    Parse ke Input Manual &amp; Input Lengkap
                                </button>
                            </div>
                            <div x-show="importSummary" class="text-xs p-2 rounded-lg bg-blue-50 border border-blue-200 text-blue-700"
                                x-text="importSummary"></div>
                        </div>

                        {{-- Hasil Ekstraksi — tabel pratinjau --}}
                        <div x-show="pdfSuccess && pdfData" class="mt-4 space-y-4">
                            @include('analisa.partials.create-pdf-preview')
                        </div>
                    </div>

                    {{-- Tab Navigation: Multi-Dokumen vs Partisi Halaman --}}
                    <div x-show="jenisLaporan !== 'kalender_ffs'" class="flex border-b border-line mb-2">
                        <button type="button" @click="activeTab = 'multi'"
                            class="px-4 py-2 text-xs font-semibold transition -mb-px"
                            :class="activeTab === 'multi' ? 'text-primary border-b-2 border-primary' :
                                'text-muted hover:text-primary'">
                            <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Upload Multi-Dokumen
                        </button>
                        <button type="button" @click="activeTab = 'partition'"
                            class="px-4 py-2 text-xs font-semibold transition -mb-px"
                            :class="activeTab === 'partition' ? 'text-primary border-b-2 border-primary' :
                                'text-muted hover:text-primary'">
                            <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16m-7 6h7" />
                            </svg>
                            Partisi Halaman
                        </button>
                    </div>

                    {{-- Tab: Multi-Document Upload Panel --}}
                    <div x-show="activeTab === 'multi' && jenisLaporan !== 'kalender_ffs'"
                        class="border border-line rounded-lg p-4 space-y-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h4 class="font-medium text-sm text-primary">Upload Multi-Dokumen</h4>
                        </div>
                        <p class="text-xs text-muted">Upload beberapa dokumen sekaligus untuk mengisi form secara lebih
                            lengkap. Masing-masing dokumen akan di-parse dan hasilnya digabungkan.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="(slot, idx) in docSlots" :key="slot.type">
                                <div class="border rounded-lg p-3 space-y-2"
                                    :class="{
                                        'border-line bg-white': slot.success === null,
                                        'border-green-300 bg-green-50/50': slot.success === true,
                                        'border-red-300 bg-red-50/50': slot.success === false,
                                    }">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold"
                                            :class="{
                                                'text-primary': slot.success === null,
                                                'text-green-700': slot.success === true,
                                                'text-red-700': slot.success === false,
                                            }"
                                            x-text="slot.label"></span>
                                        <template x-if="slot.loading">
                                            <svg class="animate-spin h-3.5 w-3.5 text-primary" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="slot.success === true">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </template>
                                        <template x-if="slot.success === false">
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </template>
                                    </div>

                                    {{-- All slots: upload only --}}
                                    <input type="file" accept=".pdf"
                                        @change="slot.file = $event.target.files[0] || null"
                                        class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 file:mr-2 file:rounded file:border-0 file:bg-primary/10 file:px-2 file:py-0.5 file:text-primary file:text-xs" />

                                    <p x-show="slot.message" class="text-[11px]"
                                        :class="slot.success ? 'text-green-600' : 'text-red-600'" x-text="slot.message">
                                    </p>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button" @click="parseAllDocs()"
                                :disabled="multiParseLoading || (!docSlots.some(s => s.file) && !selectedDocIds.length)"
                                class="px-4 py-2 text-xs font-semibold text-white bg-primary rounded-lg hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                <span x-show="!multiParseLoading">Parse Semua Dokumen</span>
                                <span x-show="multiParseLoading">Memproses...</span>
                            </button>
                            <button type="button"
                                @click="docSlots.forEach(s => { s.file = null; s.success = null; s.message = ''; s.data = null; }); multiParseResult = ''; multiParseSuccess = false;"
                                x-show="docSlots.some(s => s.file || s.success !== null)"
                                class="px-3 py-2 text-xs font-medium text-muted border border-line rounded-lg hover:bg-gray-50 transition">
                                Reset
                            </button>
                        </div>

                        {{-- Summary --}}
                        <div x-show="multiParseResult" class="text-sm p-3 rounded-lg border"
                            :class="multiParseSuccess ? 'bg-green-50 border-green-200 text-green-700' :
                                'bg-amber-50 border-amber-200 text-amber-700'">
                            <span x-text="multiParseResult"></span>
                            <div x-show="multiParseSuccess" class="mt-2">
                                <button type="button" @click="parseToForms()"
                                    class="px-3 py-1 text-xs font-semibold text-white bg-primary rounded-lg hover:bg-primary/90 transition">
                                    Parse ke Input Manual &amp; Input Lengkap
                                </button>
                            </div>
                            <div x-show="importSummary" class="text-xs p-2 rounded-lg bg-blue-50 border border-blue-200 text-blue-700"
                                x-text="importSummary"></div>
                        </div>
                    </div>

                    {{-- Tab: Partisi Halaman Panel --}}
                    <div x-show="activeTab === 'partition' && jenisLaporan !== 'kalender_ffs'"
                        class="border border-line rounded-lg p-4 space-y-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16m-7 6h7" />
                            </svg>
                            <h4 class="font-medium text-sm text-primary">Partisi Halaman Dokumen</h4>
                            <span class="text-[10px] bg-primary/10 text-primary px-2 py-0.5 rounded-full">Baru</span>
                        </div>
                        <p class="text-xs text-muted">Pilih dokumen dari daftar, lalu tentukan halaman mana saja yang
                            ingin dianalisa. Setiap partisi akan diparse dengan AI prompt yang sesuai.</p>

                        {{-- Pilih Dokumen --}}
                        <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                            <label class="text-xs font-medium text-muted">Pilih Dokumen</label>
                            <div x-show="!existingDocsLoaded" class="text-xs text-muted italic">Memuat daftar dokumen...
                            </div>
                            <div x-show="existingDocsLoaded && existingDocs.length === 0"
                                class="text-xs text-muted italic">Dokumen tidak ditemukan untuk periode yang dipilih.
                            </div>
                            <div x-show="existingDocsLoaded && existingDocs.length > 0"
                                class="space-y-1.5 max-h-40 overflow-y-auto">
                                <template x-for="doc in existingDocs" :key="doc.id">
                                    <label
                                        class="flex items-center gap-2 p-2 rounded-lg cursor-pointer text-xs transition"
                                        :class="selectedDocId === doc.id ? 'bg-primary/10 border border-primary/30' :
                                            'hover:bg-gray-100 border border-transparent'">
                                        <input type="radio" name="partition_doc" :value="doc.id"
                                            @change="selectDocumentForPartition(doc.id)" class="accent-primary">
                                        <div class="flex-1 min-w-0">
                                            <span class="font-medium text-gray-800 block truncate"
                                                x-text="doc.label || doc.original_name"></span>
                                            <span class="text-muted"
                                                x-text="doc.reksa_dana_nama ? doc.reksa_dana_nama + ' - ' + doc.reksa_dana_kode : ''"></span>
                                        </div>
                                        <span class="text-muted text-[10px] whitespace-nowrap"
                                            x-text="doc.file_size ? (doc.file_size / 1024).toFixed(0) + ' KB' : ''"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- Selected Document Info --}}
                        <div x-show="selectedDocId"
                            class="text-xs text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                            Dokumen terpilih: <span
                                x-text="existingDocs.find(d => d.id === selectedDocId)?.label || existingDocs.find(d => d.id === selectedDocId)?.original_name || ''"></span>
                        </div>

                        {{-- Page Range Cards --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-medium text-muted">Partisi Halaman</label>
                                <button type="button" @click="addPageRange()"
                                    class="text-xs text-primary font-medium hover:underline flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Bagian
                                </button>
                            </div>

                            <template x-for="(range, idx) in pageRanges" :key="range.id">
                                <div class="border rounded-lg p-3 space-y-2"
                                    :class="{
                                        'border-line bg-white': range.success === null,
                                        'border-green-300 bg-green-50/50': range.success === true,
                                        'border-red-300 bg-red-50/50': range.success === false,
                                    }">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold"
                                            :class="{
                                                'text-primary': range.success === null,
                                                'text-green-700': range.success === true,
                                                'text-red-700': range.success === false,
                                            }">Bagian
                                            <span x-text="idx + 1"></span></span>
                                        <div class="flex items-center gap-2">
                                            <template x-if="range.loading">
                                                <svg class="animate-spin h-3.5 w-3.5 text-primary" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                            </template>
                                            <template x-if="range.success === true">
                                                <svg class="w-4 h-4 text-green-600" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </template>
                                            <template x-if="range.success === false">
                                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </template>
                                            <button type="button" @click="removePageRange(idx)"
                                                x-show="pageRanges.length > 1"
                                                class="text-red-400 hover:text-red-600 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-[10px] text-muted block mb-0.5">Start Page</label>
                                            <input type="number" min="1" x-model="range.start_page"
                                                class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-primary focus:border-primary"
                                                placeholder="Halaman awal">
                                        </div>
                                        <div>
                                            <label class="text-[10px] text-muted block mb-0.5">End Page</label>
                                            <input type="number" min="1" x-model="range.end_page"
                                                class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-primary focus:border-primary"
                                                placeholder="Halaman akhir">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-[10px] text-muted block mb-0.5">Tipe Konten</label>
                                        <select x-model="range.section_type"
                                            class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-primary focus:border-primary">
                                            <option value="auto">Auto-detect</option>
                                            <option value="informasi_lainnya">Informasi Lainnya</option>
                                            <option value="portofolio_efek">Portofolio Efek</option>
                                            <option value="pengukuran_nilai_wajar">Pengukuran Nilai Wajar</option>
                                            <option value="bs_is_cf_pup">BS, IS, CF, dan PUP</option>
                                        </select>
                                    </div>

                                    <p x-show="range.message" class="text-[11px]"
                                        :class="range.success ? 'text-green-600' : 'text-red-600'"
                                        x-text="range.message"></p>
                                </div>
                            </template>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-3 flex-wrap">
                            <button type="button" @click="parseAllPageRanges()"
                                :disabled="partitionLoading || !selectedDocId || !pageRanges.some(r => r.start_page && r.end_page)"
                                class="px-4 py-2 text-xs font-semibold text-white bg-primary rounded-lg hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                <span x-show="!partitionLoading">Parse Semua Partisi</span>
                                <span x-show="partitionLoading">Memproses...</span>
                            </button>
                            <button type="button" @click="resetPageRanges()"
                                x-show="pageRanges.some(r => r.start_page || r.end_page || r.success !== null)"
                                class="px-3 py-2 text-xs font-medium text-muted border border-line rounded-lg hover:bg-gray-50 transition">
                                Reset
                            </button>
                        </div>

                        {{-- Summary --}}
                        <div x-show="partitionResult" class="text-sm p-3 rounded-lg border"
                            :class="partitionSuccess ? 'bg-green-50 border-green-200 text-green-700' :
                                'bg-amber-50 border-amber-200 text-amber-700'">
                            <span x-text="partitionResult"></span>
                            <div x-show="partitionSuccess" class="mt-2">
                                <button type="button" @click="parseToForms()"
                                    class="px-3 py-1 text-xs font-semibold text-white bg-primary rounded-lg hover:bg-primary/90 transition">
                                    Parse ke Input Manual &amp; Input Lengkap
                                </button>
                            </div>
                            <div x-show="importSummary" class="text-xs p-2 rounded-lg bg-blue-50 border border-blue-200 text-blue-700"
                                x-text="importSummary"></div>
                        </div>

                        {{-- Preview Tables — Card per Section --}}
                        <div x-show="partitionSuccess && groupedTables.length > 0">
                            <hr class="border-line my-3">
                            <h4 class="text-xs font-semibold text-primary mb-3">Hasil Tabel</h4>
                            <div class="space-y-4">
                                <template x-for="(grp, gi) in groupedTables" :key="gi">
                                    <div class="border rounded-lg p-4 bg-white shadow-sm">
                                        <div class="flex items-center justify-between mb-3">
                                            <h5 class="font-semibold text-primary text-sm" x-text="grp.table_name"></h5>
                                            <button type="button" @click="(() => { const h = grp.tables[0]?.headers || []; const r = grp.tables.flatMap(t => t.rows); downloadCsv(h, r, grp.table_name); })()"
                                                class="text-[10px] text-accent hover:underline font-medium whitespace-nowrap ml-2">Download CSV</button>
                                        </div>
                                        <template x-for="(tbl, ti) in grp.tables" :key="ti">
                                            <div>
                                                <div x-show="grp.tables.length > 1" class="text-[10px] font-medium text-muted mb-1" x-text="'Tabel ' + (ti + 1)"></div>
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-xs">
                                                        <thead>
                                                            <tr class="bg-gray-50 border-b border-line">
                                                                <template x-for="(h, hi) in tbl.headers" :key="hi">
                                                                    <th class="px-3 py-2 text-left font-semibold text-primary whitespace-nowrap" x-text="h"></th>
                                                                </template>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-line">
                                                            <template x-for="(row, ri) in tbl.rows" :key="ri">
                                                                <tr class="hover:bg-gray-50/50">
                                                                    <template x-for="(cell, ci) in row" :key="ci">
                                                                        <td class="px-3 py-1.5 whitespace-nowrap"
                                                                            :class="ci === 0 ? 'font-medium text-gray-800' : 'text-right font-mono text-gray-600'"
                                                                            x-text="cell"></td>
                                                                    </template>
                                                                </tr>
                                                            </template>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Portfolio Tables — Card per Section --}}
                        <div x-show="partitionSuccess && (partitionSektor.length || partitionEfek.length || partitionObligasi.length || partitionSukuk.length || partitionBank.length)">
                            <hr class="border-line my-3">
                            <h4 class="text-xs font-semibold text-primary mb-3">📋 Portofolio</h4>
                            <div class="space-y-4">

                                {{-- Efek Card --}}
                                <template x-if="partitionEfek.length">
                                    <div class="border rounded-lg p-4 bg-white shadow-sm">
                                        <div class="flex items-center justify-between mb-3">
                                            <h5 class="font-semibold text-primary text-sm">Efek <span class="text-muted font-normal text-xs" x-text="'(' + partitionEfek.length + ')'"></span></h5>
                                            <button type="button" @click="downloadCsvFromArray(partitionEfek, 'Efek')" class="text-[10px] text-accent hover:underline font-medium">Download CSV</button>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-xs">
                                                <thead><tr class="bg-gray-50 border-b border-line">
                                                    <th class="px-3 py-2 text-left font-semibold text-primary whitespace-nowrap">Kode</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-primary whitespace-nowrap">Nama Efek</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-primary whitespace-nowrap">Sektor</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary whitespace-nowrap">Jumlah Lembar</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary whitespace-nowrap">Harga Perolehan</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary whitespace-nowrap">Bobot %</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary whitespace-nowrap">Nilai Pasar</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary whitespace-nowrap">% NAB</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary whitespace-nowrap">Return 1Y</th>
                                                </tr></thead>
                                                <tbody class="divide-y divide-line">
                                                    <template x-for="(row, ri) in partitionEfek" :key="ri">
                                                        <tr class="hover:bg-gray-50/50">
                                                            <td class="px-3 py-1.5 font-medium text-gray-800 whitespace-nowrap" x-text="row.kode_efek || '-'"></td>
                                                            <td class="px-3 py-1.5 whitespace-nowrap" x-text="row.nama_efek || '-'"></td>
                                                            <td class="px-3 py-1.5 whitespace-nowrap" x-text="row.sektor || '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.jumlah_lembar != null ? formatNumber(row.jumlah_lembar) : '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.harga_perolehan != null ? formatNumber(row.harga_perolehan) : '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.bobot != null ? row.bobot.toFixed(2) : '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.nilai_pasar != null ? formatNumber(row.nilai_pasar) : '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.persen_nab != null ? row.persen_nab.toFixed(2) : '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.return_1y != null ? row.return_1y.toFixed(2) : '-'"></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>

                                {{-- Obligasi Card --}}
                                <template x-if="partitionObligasi.length">
                                    <div class="border rounded-lg p-4 bg-white shadow-sm">
                                        <div class="flex items-center justify-between mb-3">
                                            <h5 class="font-semibold text-primary text-sm">Obligasi <span class="text-muted font-normal text-xs" x-text="'(' + partitionObligasi.length + ')'"></span></h5>
                                            <button type="button" @click="downloadCsvFromArray(partitionObligasi, 'Obligasi')" class="text-[10px] text-accent hover:underline font-medium">Download CSV</button>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-xs">
                                                <thead><tr class="bg-gray-50 border-b border-line">
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Kode</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Nama</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary">Bobot %</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary">Nilai Pasar</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary">YTM</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary">Kupon</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Jatuh Tempo</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Penerbit</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Rating</th>
                                                </tr></thead>
                                                <tbody class="divide-y divide-line">
                                                    <template x-for="(row, ri) in partitionObligasi" :key="ri">
                                                        <tr class="hover:bg-gray-50/50">
                                                            <td class="px-3 py-1.5 font-medium text-gray-800" x-text="row.kode_obligasi || '-'"></td>
                                                            <td class="px-3 py-1.5" x-text="row.nama_obligasi || '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.bobot != null ? row.bobot.toFixed(2) : '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.nilai_pasar != null ? formatNumber(row.nilai_pasar) : '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.ytm != null ? row.ytm.toFixed(2) : '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.kupon != null ? row.kupon.toFixed(2) : '-'"></td>
                                                            <td class="px-3 py-1.5 whitespace-nowrap" x-text="row.tanggal_jatuh_tempo || '-'"></td>
                                                            <td class="px-3 py-1.5" x-text="row.penerbit || '-'"></td>
                                                            <td class="px-3 py-1.5" x-text="row.rating || '-'"></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>

                                {{-- Sukuk Card --}}
                                <template x-if="partitionSukuk.length">
                                    <div class="border rounded-lg p-4 bg-white shadow-sm">
                                        <div class="flex items-center justify-between mb-3">
                                            <h5 class="font-semibold text-primary text-sm">Sukuk <span class="text-muted font-normal text-xs" x-text="'(' + partitionSukuk.length + ')'"></span></h5>
                                            <button type="button" @click="downloadCsvFromArray(partitionSukuk, 'Sukuk')" class="text-[10px] text-accent hover:underline font-medium">Download CSV</button>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-xs">
                                                <thead><tr class="bg-gray-50 border-b border-line">
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Kode</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Nama</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Jenis</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary">Bobot %</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary">Yield</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Jatuh Tempo</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Rating</th>
                                                </tr></thead>
                                                <tbody class="divide-y divide-line">
                                                    <template x-for="(row, ri) in partitionSukuk" :key="ri">
                                                        <tr class="hover:bg-gray-50/50">
                                                            <td class="px-3 py-1.5 font-medium text-gray-800" x-text="row.kode_sukuk || '-'"></td>
                                                            <td class="px-3 py-1.5" x-text="row.nama_sukuk || '-'"></td>
                                                            <td class="px-3 py-1.5" x-text="row.jenis_sukuk || '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.bobot != null ? row.bobot.toFixed(2) : '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.yield != null ? row.yield.toFixed(2) : '-'"></td>
                                                            <td class="px-3 py-1.5 whitespace-nowrap" x-text="row.jatuh_tempo || '-'"></td>
                                                            <td class="px-3 py-1.5" x-text="row.rating || '-'"></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>

                                {{-- Bank Card --}}
                                <template x-if="partitionBank.length">
                                    <div class="border rounded-lg p-4 bg-white shadow-sm">
                                        <div class="flex items-center justify-between mb-3">
                                            <h5 class="font-semibold text-primary text-sm">Bank <span class="text-muted font-normal text-xs" x-text="'(' + partitionBank.length + ')'"></span></h5>
                                            <button type="button" @click="downloadCsvFromArray(partitionBank, 'Bank')" class="text-[10px] text-accent hover:underline font-medium">Download CSV</button>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-xs">
                                                <thead><tr class="bg-gray-50 border-b border-line">
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Nama Bank</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Jenis</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary">Bobot %</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary">Nilai Pasar</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary">Bunga</th>
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Jangka Waktu</th>
                                                </tr></thead>
                                                <tbody class="divide-y divide-line">
                                                    <template x-for="(row, ri) in partitionBank" :key="ri">
                                                        <tr class="hover:bg-gray-50/50">
                                                            <td class="px-3 py-1.5 font-medium text-gray-800" x-text="row.nama_bank || '-'"></td>
                                                            <td class="px-3 py-1.5" x-text="row.jenis_bank || '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.bobot != null ? row.bobot.toFixed(2) : '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.nilai_pasar != null ? formatNumber(row.nilai_pasar) : '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.tingkat_bunga != null ? row.tingkat_bunga.toFixed(2) : '-'"></td>
                                                            <td class="px-3 py-1.5" x-text="row.jangka_waktu || '-'"></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>

                                {{-- Sektor Card --}}
                                <template x-if="partitionSektor.length">
                                    <div class="border rounded-lg p-4 bg-white shadow-sm">
                                        <div class="flex items-center justify-between mb-3">
                                            <h5 class="font-semibold text-primary text-sm">Sektor <span class="text-muted font-normal text-xs" x-text="'(' + partitionSektor.length + ')'"></span></h5>
                                            <button type="button" @click="downloadCsvFromArray(partitionSektor, 'Sektor')" class="text-[10px] text-accent hover:underline font-medium">Download CSV</button>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-xs">
                                                <thead><tr class="bg-gray-50 border-b border-line">
                                                    <th class="px-3 py-2 text-left font-semibold text-primary">Sektor</th>
                                                    <th class="px-3 py-2 text-right font-semibold text-primary">Bobot %</th>
                                                </tr></thead>
                                                <tbody class="divide-y divide-line">
                                                    <template x-for="(row, ri) in partitionSektor" :key="ri">
                                                        <tr class="hover:bg-gray-50/50">
                                                            <td class="px-3 py-1.5 font-medium text-gray-800" x-text="row.nama_sektor || '-'"></td>
                                                            <td class="px-3 py-1.5 text-right font-mono" x-text="row.bobot != null ? row.bobot.toFixed(2) : '-'"></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </div>

                        {{-- Data export --}}
                        <div x-show="partitionSuccess && groupedTables.length > 0" class="mt-2">
                            <button type="button" @click="$clipboard(JSON.stringify(groupedTables, null, 2))"
                                class="text-[10px] text-primary underline">Copy JSON</button>
                        </div>
                    </div>

                    {{-- Separator --}}
                    <div x-show="jenisLaporan !== 'kalender_ffs'" class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-line"></div>
                        </div>
                        <div class="relative flex justify-center text-xs"><span
                                class="bg-white px-2 text-muted">ATAU</span></div>
                    </div>

                    {{-- Upload PDF Baru (non-kalender) --}}
                    <div x-show="jenisLaporan !== 'kalender_ffs'">
                        <x-input-label for="file_pdf" value="Upload File PDF Baru" />
                        <div class="mt-1 flex items-center gap-3">
                            <input id="file_pdf" type="file" accept=".pdf" @change="parsePdf($event)"
                                class="block w-full text-sm text-muted border border-gray-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-primary file:text-white hover:file:bg-primary/90 cursor-pointer" />
                        </div>
                        <p class="text-xs text-muted mt-1">Format: PDF, maks 10MB. Data akan diekstrak dari Fund Fact
                            Sheet.</p>
                    </div>

                    <div x-show="jenisLaporan !== 'kalender_ffs'" class="flex flex-wrap gap-3 text-sm">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" value="text" x-model="pdfScanMode"
                                class="text-primary focus:ring-primary/20">
                            <span>PDF parser teks</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" value="vision" x-model="pdfScanMode"
                                class="text-primary focus:ring-primary/20">
                            <span>Scan AI Vision</span>
                        </label>
                    </div>

                    <div x-show="jenisLaporan !== 'kalender_ffs' && pdfLoading"
                        class="flex items-center gap-2 text-sm text-muted">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                            </path>
                        </svg>
                        <span>Membaca PDF... harap tunggu.</span>
                    </div>

                    <div x-show="jenisLaporan !== 'kalender_ffs' && pdfResult" class="text-sm space-y-2">
                        <div
                            :class="pdfSuccess ? 'p-3 bg-green-50 border border-green-200 rounded-lg text-green-700' :
                                'p-3 bg-red-50 border border-red-200 rounded-lg text-red-700'">
                            <span x-text="pdfResult"></span>
                        </div>
                        <div x-show="pdfSuccess && pdfData">
                            <button type="button" @click="parseToForms()"
                                class="px-3 py-1.5 text-xs font-semibold text-white bg-primary rounded-lg hover:bg-primary/90 transition">
                                Parse ke Input Manual &amp; Input Lengkap
                            </button>
                        </div>
                        <div x-show="importSummary" class="text-xs p-2 rounded-lg bg-blue-50 border border-blue-200 text-blue-700"
                            x-text="importSummary"></div>

                        {{-- Hasil Ekstraksi — tabel pratinjau --}}
                        <div x-show="pdfSuccess && pdfData" class="mt-4 space-y-4">
                            @include('analisa.partials.create-pdf-preview')
                        </div>
                    </div>
                </div>

                @include('analisa.partials.create-ai-tabs')
            </div>

                    {{-- Laporan Keuangan - Laba Rugi --}}
                    <div class="bg-white rounded-xl border border-line p-6 space-y-4" x-show="jenisLaporan === 'laporan_tahunan'" x-cloak>
                        <h3 class="font-semibold text-primary">Laporan Keuangan - Laba Rugi</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-muted text-xs block">Pendapatan Dividen</span>
                                <span class="font-medium text-primary" x-text="pendapatanDividen ? Number(pendapatanDividen).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2}) : '—'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Analisa Pengelolaan Investasi Portofolio --}}
                    <div class="bg-white rounded-xl border border-line p-6 space-y-4" x-show="jenisLaporan === 'laporan_tahunan'" x-cloak>
                        <h3 class="font-semibold text-primary">Analisa Pengelolaan Investasi Portofolio</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-muted text-xs block">Fee Cost to Performance</span>
                                <span class="font-medium text-primary" x-text="feeCostToPerformance != null ? Number(feeCostToPerformance).toFixed(4) : '—'"></span>
                            </div>
                            <div>
                                <span class="text-muted text-xs block">Pendapatan terhadap NAB</span>
                                <span class="font-medium text-primary" x-text="pendapatanTerhadapNab != null ? Number(pendapatanTerhadapNab).toFixed(4) : '—'"></span>
                            </div>
                            <div>
                                <span class="text-muted text-xs block">Beban terhadap Pendapatan</span>
                                <span class="font-medium text-primary" x-text="bebanTerhadapPendapatan != null ? Number(bebanTerhadapPendapatan).toFixed(4) : '—'"></span>
                            </div>
                            <div>
                                <span class="text-muted text-xs block">Pengelolaan Investasi terhadap Pendapatan</span>
                                <span class="font-medium text-primary" x-text="pengelolaanInvestasiTerhadapPendapatan != null ? Number(pengelolaanInvestasiTerhadapPendapatan).toFixed(4) : '—'"></span>
                            </div>
                            <div>
                                <span class="text-muted text-xs block">Transaction Profit terhadap NAB</span>
                                <span class="font-medium text-primary" x-text="transactionProfitTerhadapNab != null ? Number(transactionProfitTerhadapNab).toFixed(4) : '—'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Info Keuangan --}}
                    <div class="bg-white rounded-xl border border-line p-6 space-y-4" x-show="mode !== 'link-website'" x-cloak>
                        <h3 class="font-semibold text-primary">Info Keuangan</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-muted text-xs block">Total AUM</span>
                                <span class="font-medium text-primary" x-text="totalAum ? 'Rp ' + Number(totalAum).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}) : '—'"></span>
                            </div>
                            <div>
                                <span class="text-muted text-xs block">Total MarCap 10 Saham Terbesar</span>
                                <span class="font-medium text-primary" x-text="totalMarcap10Efek ? 'Rp ' + Number(totalMarcap10Efek).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}) : '—'"></span>
                            </div>
                            <div>
                                <span class="text-muted text-xs block">NAB/UP</span>
                                <span class="font-medium text-primary" x-text="nabPerUnit ? Number(nabPerUnit).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 6}) : '—'"></span>
                            </div>
                            <div>
                                <span class="text-muted text-xs block">Unit Penyertaan</span>
                                <span class="font-medium text-primary" x-text="unitPenyertaan ? Number(unitPenyertaan).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}) : '—'"></span>
                            </div>
                            <div>
                                <span class="text-muted text-xs block">Kalender FFS</span>
                                <span class="font-medium text-primary" x-text="(ffsBulan && ffsTahun) ? ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][ffsBulan-1] + ' ' + ffsTahun : (jenisLaporan === 'laporan_tahunan' && tahunLaporan ? 'Laporan Tahunan ' + tahunLaporan : '—')"></span>
                            </div>
                            <div>
                                <span class="text-muted text-xs block">Tanggal Data</span>
                                <span class="font-medium text-primary" x-text="tanggalData || '—'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Catatan Analisa --}}
                    <div class="bg-white rounded-xl border border-line p-6 space-y-4" x-show="mode !== 'link-website'" x-cloak>
                        <h3 class="font-semibold text-primary">Catatan Analisa</h3>
                        <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-lg text-xs">
                            <strong>Hanya Untuk Prospektus jenis (Laporan Keuangan).</strong> Catatan ini bersifat opsional untuk keperluan dokumentasi analisa.
                        </div>
                        <div>
                            <x-input-label for="catatan_analisa" value="Catatan / Keterangan" />
                            <textarea id="catatan_analisa" name="catatan" rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm"
                                placeholder="Tulis catatan atau keterangan mengenai analisa ini..."
                                x-model="catatanAnalisa">{{ old('catatan') }}</textarea>
                            <x-input-error :messages="$errors->get('catatan')" class="mt-1" />
                        </div>
                    </div>

            <input type="hidden" name="ai_narasi" :value="aiResult?.raw || ''">
            <input type="hidden" name="ai_output" :value="aiResult ? JSON.stringify(aiResult.parsed || {}) : ''">
            <input type="hidden" name="ai_narasi_plus" :value="aiPlusResult?.raw || ''">
            <input type="hidden" name="ai_output_plus"
                :value="aiPlusResult ? JSON.stringify(aiPlusResult.parsed || {}) : ''">

            <div class="flex items-center gap-3" x-show="mode !== 'link-website'" x-cloak>
                <template x-if="exportFileUrl">
                    <a :href="exportFileUrl" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download Excel
                    </a>
                </template>
                <button type="submit" name="simpan" value="1"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600 transition">{{ !empty($isEditMode) ? 'Simpan Draft' : 'Simpan' }}</button>
                <x-primary-button>{{ !empty($isEditMode) ? 'Simpan Perubahan' : 'Submit Analisa' }}</x-primary-button>
                <a href="{{ $formRoutes['cancel'] }}"
                    class="px-4 py-2 text-sm font-medium text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</a>
            </div>
        </form>

        {{-- Tab Link Website di luar form analisa (hindari validasi nama/jenis RD) --}}
        <div x-show="mode === 'link-website'" x-cloak
            class="bg-white rounded-xl border border-line overflow-hidden mt-6 shadow-sm">
            @include('analisa.partials.create-link-website-tab')
        </div>
    </div>

    @push('scripts')
        <script>
            function analisaForm(resumeData = null, resumeMode = null) {
                @php
                    $oldSektor = old('sektor', [['nama_sektor' => '', 'bobot' => '']]);
                    $oldEfek = old('efek', [['kode_efek' => '', 'nama_efek' => '', 'sektor' => '', 'bobot' => '', 'bobot_seharusnya' => '', 'kontribusi_kinerja' => '', 'market_cap' => '', 'nilai_pasar' => '', 'harga_perolehan' => '', 'persen_nab' => '', 'return_1m' => '', 'return_3m' => '', 'return_6m' => '', 'return_1y' => '', 'kontribusi_return' => '', 'top_10' => false]]);
                    $oldKinerja = old('kinerja', [['periode' => '', 'return_pct' => ''], ['periode' => '', 'return_pct' => '']]);
                    $oldObligasi = old('obligasi', [['kode_obligasi' => '', 'nama_obligasi' => '', 'bobot' => '', 'durasi' => '', 'rating' => '', 'ytm' => '', 'kupon' => '', 'tanggal_jatuh_tempo' => '', 'penerbit' => '', 'persen_nab' => '', 'nilai_pasar' => '', 'return_1m' => '', 'return_3m' => '', 'return_6m' => '', 'return_1y' => '']]);
                    $oldSukuk = old('sukuk', [['kode_sukuk' => '', 'nama_sukuk' => '', 'jenis_sukuk' => '', 'bobot' => '', 'yield' => '', 'jatuh_tempo' => '', 'rating' => '', 'persen_nab' => '']]);
                    $oldBank = old('bank', [['nama_bank' => '', 'jenis_bank' => '', 'bobot' => '', 'nilai_pasar' => '', 'tingkat_bunga' => '', 'jangka_waktu' => '', 'persen_nab' => '', 'return_1m' => '', 'return_3m' => '', 'return_6m' => '', 'return_1y' => '', 'car' => '', 'npl' => '', 'klasifikasi_risiko' => '']]);
                    $oldAlokasiAset = old('alokasi_aset', [['nama_aset' => '', 'persentase' => '']]);
                    // Normalize top_10 checkbox (submitted as "1" string or absent)
                    $oldEfek = array_map(function ($e) {
                        $e['top_10'] = !empty($e['top_10']);
                        return $e;
                    }, $oldEfek);

                    $plusLabels = [
                        'aum' => 'Total AUM',
                        'marcap' => 'Total MarCap 10 efek terbesar',
                        'sektor' => 'Komposisi sektor (minimal 1 baris dengan bobot %)',
                        'efek' => 'Daftar efek (minimal 1 baris: kode, nama, bobot %)',
                    ];
                @endphp

                const resumeDataTahunan = resumeData?.data_tahunan || {};
                const allYears = [...(resumeDataTahunan.years || [])].sort((a, b) => Number(b) - Number(a));
                const snakeToCamel = s => s.replace(/_([a-z])/g, (_, l) => l.toUpperCase());
                // Latest year (years[0] after desc sort) is already in scalar fields — exclude from tambahan
                const latestYear = allYears.length > 0 ? String(allYears[0]) : null;
                const initialTahunTambahan = latestYear ? allYears.filter(y => String(y) !== latestYear) : allYears;
                const initialDataTambahan = {};
                initialTahunTambahan.forEach(year => {
                    initialDataTambahan[year] = {};
                    Object.entries(resumeDataTahunan[year] || {}).forEach(([key, val]) => {
                        initialDataTambahan[year][snakeToCamel(key)] = val;
                    });
                });

                return {
                    mode: resumeMode || @json(old('input_mode', request('tab') === 'link-website' ? 'link-website' : 'manual')),
                    webLoading: false,
                    webFile: null,
                    webMessage: '',
                    webOk: false,
                    parseWebFileUrl: @json($formRoutes['parse_web_file']),
                    importExcelPreviewUrl: @json($formRoutes['import_excel_preview']),
                    importExcelLoading: false,
                    importExcelMessage: '',
                    importExcelOk: false,
                    exportFileUrl: null,
                    importSummary: '',
                    testJsonError: '',
                    scrapeWebUrl: @json($formRoutes['scrape_web']),
                    pdfLoading: false,
                    pdfResult: '',
                    pdfSuccess: false,
                    pdfFile: '',
                    pdfScanMode: 'text',
                    pdfData: null,
                    aiLoading: false,
                    aiPlusLoading: false,
                    aiError: '',
                    aiPlusError: '',
                    catatanAnalisa: @json(old('catatan')),
                    aiResult: null,
                    aiPlusResult: null,
                    previewAiUrl: @json($formRoutes['preview_ai']),
                    previewAiPlusUrl: @json($formRoutes['preview_ai_plus']),
                    lookupKodeUrl: @json($formRoutes['lookup_kode']),
                    lookupSektorUrl: @json($formRoutes['lookup_sektor']),
                    lookupIhsgUrl: @json($formRoutes['lookup_ihsg']),
                    lookupReturnUrl: @json($formRoutes['lookup_return']),
                    lookupBondReturnUrl: @json($formRoutes['lookup_bond_return']),
                    lookupBankDataUrl: @json($formRoutes['lookup_bank_data']),
                    lookupKodeEfekUrl: @json($formRoutes['lookup_kode_efek']),
                    getFinancialDataUrl: @json($formRoutes['get_financial_data']),
                    lookupNavHistoryUrl: @json($formRoutes['lookup_nav_history']),
                    parsePdfVisionUrl: @json($formRoutes['parse_pdf_vision']),
                    existingDocsUrl: @json($formRoutes['existing_documents']),
                    parseExistingDocUrl: @json($formRoutes['parse_existing_document']),
                    existingDocs: [],
                    existingDocsLoading: false,
                    existingDocsLoaded: false,
                    existingDocParsing: false,
                    selectedDocIds: [],
                    batchParsing: false,
                    batchParsedCount: 0,
                    currentKode: '',
                    _docFetchTimer: null,
                    _docFetchAbortController: null,
                    _lastFetchedKey: '',
                    resumeId: resumeData?.id || null,
                    plusRequiredLabels: @json($plusLabels),
                    lookupMessage: '',
                    lookupOk: false,
                    tanggalData: @json(old('tanggal_data')),
                    ffsBulan: @json(old('ffs_bulan')),
                    ffsTahun: @json(old('ffs_tahun')) || resumeData?.ffs_tahun || '',
                    ffsPembandingOptions: @json($ffsPembandingOptions ?? []),
                    ffsPembanding: '',
                    pembandingEfek: {},
                    pembandingLoading: false,
                    pembandingMessage: '',
                    likuiditas: [],
                    keuangan: [],
                    jenisLaporan: @json(old('jenis_laporan', 'laporan_tahunan')),
                    periodeAwal: @json(old('periode_awal')),
                    periodeAkhir: @json(old('periode_akhir')),
                    tahunLaporan: @json(old('tahun_laporan')) || resumeData?.tahun_laporan || '',
                    unitPenyertaan: @json(old('unit_penyertaan')),
                    nabPerUnit: @json(old('nab_per_unit')),
                    totalAum: @json(old('total_aum')),
                    totalMarcap10Efek: @json(old('total_marcap_10_efek')),
                    portofolioEfek: @json(old('portofolio_efek')),
                    instrumenPasarUang: @json(old('instrumen_pasar_uang')),
                    piutangTransaksiEfek: @json(old('piutang_transaksi_efek')),
                    piutangBungaDanDividen: @json(old('piutang_bunga_dan_dividen')),
                    uangMukaDiterima: @json(old('uang_muka_diterima')),
                    liabilitasPembelianKembali: @json(old('liabilitas_pembelian_kembali')),
                    bebanAkrual: @json(old('beban_akrual')),
                    liabilitasAtasBiaya: @json(old('liabilitas_atas_biaya')),
                    pembelianKembaliUnit: @json(old('pembelian_kembali_unit_penyertaan')),
                    utangPajakLainnya: @json(old('utang_pajak_lainnya')),
                    pendapatanInvestasi: @json(old('pendapatan_investasi')),
                    pendapatanLainnya: @json(old('pendapatan_lainnya')),
                    bebanInvestasi: @json(old('beban_investasi')),
                    bebanPengelolaanInvestasi: @json(old('beban_pengelolaan_investasi')),
                    pembelianEfekEkuitas: @json(old('pembelian_efek_ekuitas')),
                    penjualanEfekEkuitas: @json(old('penjualan_efek_ekuitas')),
                    penerimaanBungaDeposito: @json(old('penerimaan_bunga_deposito')),
                    penerimaanBungaJasaGiro: @json(old('penerimaan_bunga_jasa_giro')),
                    penerimaanDividenKas: @json(old('penerimaan_dividen_kas')),
                    pembayaranJasaPengelolaan: @json(old('pembayaran_jasa_pengelolaan')),
                    pembayaranJasaKustodian: @json(old('pembayaran_jasa_kustodian')),
                    pembayaranBebanLainArus: @json(old('pembayaran_beban_lain_arus')),
                    kasBersihAktivitasOperasi: @json(old('kas_bersih_aktivitas_operasi')),
                    penerimaanPenjualanUnit: @json(old('penerimaan_penjualan_unit')),
                    pembayaranPembelianKembaliUnit: @json(old('pembayaran_pembelian_kembali_unit')),
                    kasBersihAktivitasPendanaan: @json(old('kas_bersih_aktivitas_pendanaan')),
                    kenaikanKasSetaraKas: @json(old('kenaikan_kas_setara_kas')),
                    kasSetaraKasAwalTahun: @json(old('kas_setara_kas_awal_tahun')),
                    kasSetaraKasAkhirTahun: @json(old('kas_setara_kas_akhir_tahun')),
                    depositoBerjangka: @json(old('deposito_berjangka')),
                    totalKasSetaraKas: @json(old('total_kas_setara_kas')),
                    sektor: @json($oldSektor),
                    efek: @json($oldEfek),
                    kinerja: @json($oldKinerja),
                    obligasi: @json($oldObligasi),
                    sukuk: @json($oldSukuk),
                    bank: @json($oldBank),
                    alokasi_aset: @json($oldAlokasiAset),

                    kodeReksaDana: @json(old('kode_reksa_dana')),
                    namaReksaDana: @json(old('nama_reksa_dana')),
                    jenisReksaDana: @json(old('jenis_reksa_dana')),
                    manajerInvestasi: @json(old('manajer_investasi')),
                    bankKustodian: @json(old('bank_kustodian')),
                    tanggalPeluncuran: @json(old('tanggal_peluncuran')),
                    mataUang: @json(old('mata_uang')),
                    benchmark: @json(old('benchmark')),
                    tujuanInvestasi: @json(old('tujuan_investasi')),
                    kebijakanInvestasi: @json(old('kebijakan_investasi')),
                    returnYtd: @json(old('return_ytd')),
                    return1m: @json(old('return_1m')),
                    return1y: @json(old('return_1y')),
                    totalReturn: @json(old('total_return')),
                    biayaOperasi: @json(old('biaya_operasi')),
                    portfolioTurnover: @json(old('portfolio_turnover_ratio')),
                    managementFee: @json(old('management_fee')),
                    custodianFee: @json(old('custodian_fee')),
                    investmentManagerFee: @json(old('investment_manager_fee')),
                    return1mChecklist: @json(old('return_1m_checklist', false)),
                    totalAset: @json(old('total_aset')),
                    totalLiabilitas: @json(old('total_liabilitas')),
                    kasDanBank: @json(old('kas_dan_bank')),
                    piutangBunga: @json(old('piutang_bunga')),
                    piutangDividen: @json(old('piutang_dividen')),
                    piutangLain: @json(old('piutang_lain')),
                    utangPajak: @json(old('utang_pajak')),
                    utangLain: @json(old('utang_lain')),
                    pendapatanBunga: @json(old('pendapatan_bunga')),
                    pendapatanDividen: @json(old('pendapatan_dividen')),
                    gainRealized: @json(old('gain_realized')),
                    gainUnrealized: @json(old('gain_unrealized')),
                    bebanMi: @json(old('beban_mi')),
                    bebanKustodian: @json(old('beban_kustodian')),
                    bebanLain: @json(old('beban_lain')),
                    labaBersih: @json(old('laba_bersih')),
                    totalBeban: @json(old('total_beban')),
                    labaSebelumPajak: @json(old('laba_sebelum_pajak')),
                    bebanPajakPenghasilan: @json(old('beban_pajak_penghasilan')),
                    labaBersihTahunBerjalan: @json(old('laba_bersih_tahun_berjalan')),
                    penghasilanKomprehensifLain: @json(old('penghasilan_komprehensif_lain')),
                    penghasilanKomprehensifLainSetelahPajak: @json(old('penghasilan_komprehensif_lain_setelah_pajak')),
                    penghasilanKomprehensifTahunBerjalan: @json(old('penghasilan_komprehensif_tahun_berjalan')),
                    arusKasOperasi: @json(old('arus_kas_operasi')),
                    arusKasPendanaan: @json(old('arus_kas_pendanaan')),
                    kasAwalTahun: @json(old('kas_awal_tahun')),
                    kasAkhirTahun: @json(old('kas_akhir_tahun')),
                    tahunTambahan: initialTahunTambahan,
                    dataTambahan: initialDataTambahan,
                    totalHasilInvestasi: @json(old('total_hasil_investasi')),
                    hasilInvestasiSetelahBiaya: @json(old('hasil_investasi_setelah_biaya')),
                    persentasePph: @json(old('persentase_pph')),
                    fairValueLevel1: @json(old('fair_value_level_1')),
                    fairValueLevel2: @json(old('fair_value_level_2')),
                    fairValueLevel3: @json(old('fair_value_level_3')),
                    unitMilikInvestor: @json(old('unit_milik_investor')),
                    unitMilikMi: @json(old('unit_milik_mi')),
                    totalUnitBeredar: @json(old('total_unit_beredar')),
                    feeCostToPerformance: @json(old('fee_cost_to_performance')),
                    pendapatanTerhadapNab: @json(old('pendapatan_terhadap_nab')),
                    bebanTerhadapPendapatan: @json(old('beban_terhadap_pendapatan')),
                    pengelolaanInvestasiTerhadapPendapatan: @json(old('pengelolaan_investasi_terhadap_pendapatan')),
                    transactionProfitTerhadapNab: @json(old('transaction_profit_terhadap_nab')),
                    nilaiAsetBersih: @json(old('nilai_aset_bersih')),
                    totalPendapatan: @json(old('total_pendapatan')),
                    kas: @json(old('kas')),

                    // Multi-document parse slots
                    docSlots: [{
                            type: 'informasi_lainnya',
                            label: 'Informasi Lainnya',
                            file: null,
                            loading: false,
                            success: null,
                            message: '',
                            data: null
                        },
                        {
                            type: 'portofolio_efek',
                            label: 'Portofolio Efek',
                            file: null,
                            loading: false,
                            success: null,
                            message: '',
                            data: null
                        },
                        {
                            type: 'pengukuran_nilai_wajar',
                            label: 'Pengukuran Nilai Wajar',
                            file: null,
                            loading: false,
                            success: null,
                            message: '',
                            data: null
                        },
                        {
                            type: 'bs_is_cf_pup',
                            label: 'BS, IS, CF, dan PUP',
                            file: null,
                            loading: false,
                            success: null,
                            message: '',
                            data: null
                        },
                    ],
                    multiParseLoading: false,
                    multiParseResult: '',
                    multiParseSuccess: false,

                    // Page range partition mode
                    activeTab: 'multi',
                    selectedDocId: null,
                    partitionLoading: false,
                    partitionSuccess: false,
                    partitionResult: '',
                    partitionTables: [],
                    groupedTables: [],
                    activeContentTab: '',
                    activeTableTab: 0,
                    partitionSektor: [],
                    partitionEfek: [],
                    partitionObligasi: [],
                    partitionSukuk: [],
                    partitionBank: [],
                    activePortfolioTab: 'efek',
                    pageRanges: [{
                        id: 1,
                        start_page: '',
                        end_page: '',
                        section_type: 'auto',
                        loading: false,
                        success: null,
                        message: '',
                        data: null
                    }],

                    analisaFormEl() {
                        return document.getElementById('analisa-form');
                    },

                    init() {
                        if (resumeData) {
                            const el = document.getElementById('kode_reksa_dana');
                            if (el && resumeData.kode_reksa_dana) {
                                el.value = resumeData.kode_reksa_dana;
                            }
                            this.applyLookupData(resumeData);
                            this.autoCalcUnitPenyertaan();
                        } else if (this.kodeReksaDana) {
                            this.lookupReksaDana(this.kodeReksaDana);
                        }
                        this.debouncedFetchDocs();
                        this.$watch('jenisLaporan', () => this.debouncedFetchDocs());
                        this.$watch('ffsBulan', () => this.debouncedFetchDocs());
                        this.$watch('ffsTahun', () => this.debouncedFetchDocs());
                        this.$watch('ffsPembanding', (id) => this.applyPembanding(id));
                        this.$watch('tahunLaporan', () => this.debouncedFetchDocs());

                        this.$watch('unitMilikInvestor', () => this.autoCalcDerived());
                        this.$watch('unitMilikMi', () => this.autoCalcDerived());
                        this.$watch('pendapatanBunga', () => this.autoCalcDerived());
                        this.$watch('pendapatanDividen', () => this.autoCalcDerived());
                        this.$watch('gainRealized', () => this.autoCalcDerived());
                        this.$watch('gainUnrealized', () => this.autoCalcDerived());
                        this.$watch('bebanMi', () => this.autoCalcDerived());
                        this.$watch('bebanKustodian', () => this.autoCalcDerived());
                        this.$watch('bebanLain', () => this.autoCalcDerived());
                        this.$watch('totalAset', () => this.autoCalcDerived());
                        this.$watch('kasDanBank', () => this.autoCalcDerived());
                        this.$watch('kasAwalTahun', () => this.autoCalcDerived());
                        this.$watch('kasAkhirTahun', () => this.autoCalcDerived());
                        this.$watch('arusKasOperasi', () => this.autoCalcDerived());
                        this.$watch('arusKasPendanaan', () => this.autoCalcDerived());
                        this.$watch('totalAum', () => this.autoCalcUnitPenyertaan());
                        this.$watch('nabPerUnit', () => this.autoCalcUnitPenyertaan());
                        this.$watch('tanggalData', (val) => {
                            if (val && this.kodeReksaDana) this.fetchNavHistory();
                        });
                        this.$watch('kodeReksaDana', (val) => {
                            if (val && this.tanggalData) this.fetchNavHistory();
                        });
                        // Trigger initial fetch if both kode and tanggal already set (e.g., resume)
                        if (this.kodeReksaDana && this.tanggalData) {
                            this.fetchNavHistory();
                        }
                    },

                    autoCalcDerived() {
                        const inv = parseFloat(this.unitMilikInvestor);
                        const mi = parseFloat(this.unitMilikMi);
                        if (!isNaN(inv) && !isNaN(mi) && !this.totalUnitBeredar) {
                            this.totalUnitBeredar = (inv + mi).toFixed(4);
                        }

                        const pb = parseFloat(this.pendapatanBunga) || 0;
                        const pd = parseFloat(this.pendapatanDividen) || 0;
                        const gr = parseFloat(this.gainRealized) || 0;
                        const gu = parseFloat(this.gainUnrealized) || 0;
                        const bm = parseFloat(this.bebanMi) || 0;
                        const bk = parseFloat(this.bebanKustodian) || 0;
                        const bl = parseFloat(this.bebanLain) || 0;
                        const hasIncome = pb || pd || gr || gu;
                        const hasExpense = bm || bk || bl;
                        if (hasIncome && hasExpense && !this.labaBersih) {
                            this.labaBersih = ((pb + pd + gr + gu) - (bm + bk + bl)).toFixed(2);
                        }

                        const ako = parseFloat(this.arusKasOperasi) || 0;
                        const akp = parseFloat(this.arusKasPendanaan) || 0;
                        const kaw = parseFloat(this.kasAwalTahun) || 0;
                        if ((ako || akp) && kaw && !this.kasAkhirTahun) {
                            this.kasAkhirTahun = (kaw + ako + akp).toFixed(2);
                        }

                        const nab = Number(this.totalAum) || 0;
                        const totalPend = this.getTotalPendapatan();
                        const totalBeb = this.getTotalBeban();

                        this.feeCostToPerformance = nab ? bl / nab : null;
                        this.pendapatanTerhadapNab = nab ? totalPend / nab : null;
                        this.bebanTerhadapPendapatan = totalPend ? totalBeb / totalPend : null;
                        this.pengelolaanInvestasiTerhadapPendapatan = totalPend ? bm / totalPend : null;
                        this.transactionProfitTerhadapNab = nab ? (gr + gu) / nab : null;
                    },

                    autoCalcUnitPenyertaan() {
                        const aum = Number(this.totalAum);
                        const nab = Number(this.nabPerUnit);
                        if (aum && nab && nab > 0) {
                            this.unitPenyertaan = (aum / nab).toFixed(4);
                        }
                    },

                    async fetchNavHistory() {
                        if (!this.tanggalData || !this.kodeReksaDana) return;
                        try {
                            const resp = await fetch(`${this.lookupNavHistoryUrl}?kode_reksa_dana=${encodeURIComponent(this.kodeReksaDana)}&tanggal=${encodeURIComponent(this.tanggalData)}`);
                            const data = await resp.json();
                            if (data.found && data.data) {
                                const nab = data.data.nab_per_unit;
                                const aum = data.data.aum;
                                if (nab) this.nabPerUnit = nab;
                                if (aum) this.totalAum = aum;
                                if (!aum && !nab && data.data.unit_participation) {
                                    this.unitPenyertaan = data.data.unit_participation;
                                }
                                if (data.data.tanggal_nav || data.data.tanggal_snapshot) {
                                    this.tanggalData = data.data.tanggal_nav || data.data.tanggal_snapshot;
                                }
                                this.autoCalcUnitPenyertaan();
                            }
                        } catch (e) {
                            console.warn('fetchNavHistory failed', e);
                        }
                    },

                    addRow(type) {
                        const defaults = {
                            sektor: {
                                nama_sektor: '',
                                bobot: ''
                            },
                            efek: {
                                kode_efek: '',
                                nama_efek: '',
                                sektor: '',
                                bobot: '',
                                bobot_seharusnya: '',
                                kontribusi_kinerja: '',
                                market_cap: '',
                                nilai_pasar: '',
                                harga_perolehan: '',
                                persen_nab: '',
                                return_1m: '',
                                return_3m: '',
                                return_6m: '',
                                return_1y: '',
                                kontribusi_return: '',
                                effect_type: 'Saham',
                                top_10: false
                            },
                            kinerja: {
                                periode: '',
                                return_pct: ''
                            },
                            obligasi: {
                                kode_obligasi: '',
                                nama_obligasi: '',
                                bobot: '',
                                nilai_pasar: '',
                                ytm: '',
                                kupon: '',
                                tanggal_jatuh_tempo: '',
                                penerbit: '',
                                persen_nab: '',
                                return_1m: '',
                                return_3m: '',
                                return_6m: '',
                                return_1y: '',
                                durasi: '',
                                rating: ''
                            },
                            sukuk: {
                                kode_sukuk: '',
                                nama_sukuk: '',
                                jenis_sukuk: '',
                                bobot: '',
                                yield: '',
                                jatuh_tempo: '',
                                rating: '',
                                persen_nab: ''
                            },
                            bank: {
                                nama_bank: '',
                                jenis_bank: '',
                                bobot: '',
                                nilai_pasar: '',
                                tingkat_bunga: '',
                                jangka_waktu: '',
                                persen_nab: '',
                                return_1m: '',
                                return_3m: '',
                                return_6m: '',
                                return_1y: '',
                                car: '',
                                npl: '',
                                klasifikasi_risiko: ''
                            },
                            alokasi_aset: {
                                nama_aset: '',
                                persentase: ''
                            },
                        };
                        this[type].push({
                            ...defaults[type]
                        });
                    },

                    removeRow(type, index) {
                        if (this[type].length > 1) this[type].splice(index, 1);
                    },

                    async lookupKodeEfek(kode, index) {
                        if (!kode || kode.trim() === '' || !this.lookupKodeEfekUrl) return;
                        
                        try {
                            console.log('Looking up kode efek:', kode);
                            const res = await fetch(`${this.lookupKodeEfekUrl}?kode=${encodeURIComponent(kode)}`);
                            const data = await res.json();
                            
                            console.log('Lookup response:', data);
                            
                            if (data.found) {
                                this.efek[index].kode_efek = data.data.kode_efek;
                                this.efek[index].nama_efek = data.data.nama_efek;
                                if (data.data.sektor) {
                                    this.efek[index].sektor = data.data.sektor;
                                }
                                console.log('Calling populateKeuanganData for', data.data.kode_efek, 'type:', data.type);
                                await this.populateKeuanganData(data.data.kode_efek, data.data.nama_efek, data.type);
                            } else {
                                console.warn('Kode efek tidak ditemukan:', kode);
                            }
                        } catch (e) {
                            console.error('Lookup error:', e);
                        }
                    },

                    async populateKeuanganData(kode, nama, type) {
                        if (!this.getFinancialDataUrl) return;
                        const exists = this.keuangan.find(k => k.kode_efek === kode);
                        if (exists) return;

                        try {
                            const res = await fetch(`${this.getFinancialDataUrl}?kode=${encodeURIComponent(kode)}&type=${type}`);
                            const data = await res.json();
                            
                            console.log('Financial data response for', kode, ':', data);
                            
                            if (data.found) {
                                if (data.message && !data.has_financial_data) {
                                    console.info(`ℹ️ ${data.message}`);
                                } else if (data.has_financial_data) {
                                    console.info(`✓ Data keuangan ${kode} berhasil diambil dari ${data.data_source || 'database'}`);
                                }
                                
                                const newRow = {
                                    kategori: type === 'saham' ? 'Saham' : 'Obligasi',
                                    kode_efek: data.data.kode_efek,
                                    nama_efek: data.data.nama_efek,
                                    per: data.data.per || '',
                                    pbv: data.data.pbv || '',
                                    roe: data.data.roe || '',
                                    roa: data.data.roa || '',
                                    npm: data.data.npm || '',
                                    ev_ebitda: data.data.ev_ebitda || '',
                                    der: data.data.der || '',
                                    current_ratio: data.data.current_ratio || '',
                                    aktivitas_lancar: data.data.aktivitas_lancar || '',
                                    gross_profit_margin: data.data.gross_profit_margin || '',
                                    operating_profit_margin: data.data.operating_profit_margin || '',
                                    ytm: data.data.ytm || '',
                                    rating: data.data.rating || '',
                                    kupon: data.data.kupon || '',
                                    tenor: data.data.tenor || '',
                                    durasi: data.data.durasi || '',
                                    shadow_rating: data.data.shadow_rating || '',
                                };
                                
                                this.keuangan.push(newRow);
                                console.log('Keuangan array after push:', this.keuangan);
                            }
                        } catch (e) {
                            console.error('Financial data error:', e);
                        }
                    },

                    alokasiAsetTotal() {
                        return this.alokasi_aset.reduce((sum, row) => sum + (parseFloat(row.persentase) || 0), 0);
                    },

                    alokasiAsetTotalValid() {
                        const filled = this.alokasi_aset.some(row => String(row.nama_aset || '').trim() !== '' || String(row
                            .persentase || '').trim() !== '');
                        return !filled || Math.abs(this.alokasiAsetTotal() - 100) <= 0.01;
                    },

                    totalAumValue() {
                        return parseFloat(this.totalAum || document.getElementById('total_aum')?.value || 0);
                    },

                    ffsDateValue() {
                        if (!this.ffsBulan || !this.ffsTahun) return '';
                        return `${this.ffsTahun}-${String(this.ffsBulan).padStart(2, '0')}-01`;
                    },

                    tanggalDataValue() {
                        const d = this.tanggalData || this.ffsDateValue();
                        if (/^\d{2}\/\d{2}\/\d{4}$/.test(d)) {
                            const [dd, mm, yyyy] = d.split('/');
                            return `${yyyy}-${mm}-${dd}`;
                        }
                        return d;
                    },

                    hitungNilaiPasarEfek(i) {
                        const aum = this.totalAumValue();
                        const bobot = parseFloat(this.efek[i]?.bobot) || 0;
                        this.efek[i].nilai_pasar = (aum > 0 && bobot > 0) ? (bobot / 100 * aum).toFixed(2) : '';
                        this.hitungSektorFromEfek();
                    },

                    hitungNilaiPasarObligasi(i) {
                        const aum = this.totalAumValue();
                        const bobot = parseFloat(this.obligasi[i]?.bobot) || 0;
                        this.obligasi[i].nilai_pasar = (aum > 0 && bobot > 0) ? (bobot / 100 * aum).toFixed(2) : '';
                    },

                    hitungNilaiPasarBank(i) {
                        const aum = this.totalAumValue();
                        const bobot = parseFloat(this.bank[i]?.bobot) || 0;
                        this.bank[i].nilai_pasar = (aum > 0 && bobot > 0) ? (bobot / 100 * aum).toFixed(2) : '';
                    },

                    hitungSektorFromEfek() {
                        if (this.sektor.length > 0) return;
                        const sectorMap = {};
                        this.efek.forEach(e => {
                            const s = (e.sektor || '').trim();
                            const b = parseFloat(e.bobot) || 0;
                            if (!s || b <= 0) return;
                            sectorMap[s] = (sectorMap[s] || 0) + b;
                        });
                        this.sektor = Object.entries(sectorMap)
                            .map(([nama_sektor, bobot]) => ({ nama_sektor, bobot: parseFloat(bobot.toFixed(2)) }))
                            .sort((a, b) => b.bobot - a.bobot);
                    },

                    syncLikuiditasFromEfek() {
                        const existing = {};
                        this.likuiditas.forEach(r => { existing[r.kode_efek] = r; });
                        const next = [];
                        this.efek.forEach(e => {
                            const kode = (e.kode_efek || '').trim();
                            if (!kode) return;
                            if (existing[kode]) {
                                next.push(existing[kode]);
                            } else {
                                next.push({
                                    kategori: 'Saham', kode_efek: kode, nama_efek: e.nama_efek || '',
                                    rata_volume_transaksi_harian: '', volume_terendah: '', volume_saham: '',
                                    skenario_20_persen_reds: '', skenario_reds_closing_10: '',
                                    rasio_likuiditas_harian: '', rasio_likuiditas: '',
                                });
                            }
                        });
                        this.likuiditas = next;
                    },

                    syncKeuanganFromData() {
                        const existing = {};
                        this.keuangan.forEach(r => { existing[r.kode_efek] = r; });
                        const next = [];
                        const seen = new Set();

                        this.efek.forEach(e => {
                            const kode = (e.kode_efek || '').trim();
                            if (!kode || seen.has(kode)) return;
                            seen.add(kode);
                            if (existing[kode]) {
                                next.push(existing[kode]);
                            } else {
                                next.push({
                                    kategori: 'Saham', kode_efek: kode, nama_efek: e.nama_efek || '',
                                    per: '', pbv: '', roe: '', roa: '', npm: '', ev_ebitda: '', der: '',
                                    current_ratio: '', aktivitas_lancar: '', gross_profit_margin: '', operating_profit_margin: '',
                                });
                            }
                        });

                        this.obligasi.forEach(e => {
                            const kode = (e.kode_obligasi || '').trim();
                            if (!kode || seen.has(kode)) return;
                            seen.add(kode);
                            if (existing[kode]) {
                                next.push(existing[kode]);
                            } else {
                                next.push({
                                    kategori: 'Obligasi', kode_efek: kode, nama_efek: e.nama_obligasi || '',
                                    ytm: '', rating: '', kupon: '', tenor: '', durasi: '', shadow_rating: '',
                                    der: '', current_ratio: '', aktivitas_lancar: '', gross_profit_margin: '', operating_profit_margin: '',
                                });
                            }
                        });

                        this.sukuk.forEach(e => {
                            const kode = (e.kode_sukuk || '').trim();
                            if (!kode || seen.has(kode)) return;
                            seen.add(kode);
                            if (existing[kode]) {
                                next.push(existing[kode]);
                            } else {
                                next.push({
                                    kategori: 'Sukuk', kode_efek: kode, nama_efek: e.nama_sukuk || '',
                                    ytm: e.yield_sukuk || '', rating: '', kupon: '', tenor: '', durasi: '', shadow_rating: '',
                                    der: '', current_ratio: '', aktivitas_lancar: '', gross_profit_margin: '', operating_profit_margin: '',
                                });
                            }
                        });

                        this.bank.forEach(e => {
                            const kode = (e.nama_bank || '').trim();
                            if (!kode || seen.has(kode)) return;
                            seen.add(kode);
                            if (existing[kode]) {
                                next.push(existing[kode]);
                            } else {
                                next.push({
                                    kategori: 'Bank', kode_efek: kode, nama_efek: e.nama_bank || '',
                                    npl: '', car: '', roe: '', roa: '', ldr: '', nim: '', cir: '',
                                    aktivitas_lancar: '',
                                });
                            }
                        });

                        this.keuangan = next;
                        
                        // ponytail: Auto fetch financial data from Yahoo Finance after sync
                        this.autoPopulateKeuangan();
                    },

                    async autoPopulateKeuangan() {
                        if (!this.getFinancialDataUrl) return;
                        
                        // Collect unique codes from Saham and Obligasi
                        const sahamCodes = [...new Set(this.efek.map(e => e.kode_efek?.trim()).filter(Boolean))];
                        const obligasiCodes = [...new Set(this.obligasi.map(e => e.kode_obligasi?.trim()).filter(Boolean))];
                        const sukukCodes = [...new Set(this.sukuk.map(e => e.kode_sukuk?.trim()).filter(Boolean))];
                        
                        // Check which ones need data (have empty critical fields)
                        const needsFetch = [];
                        
                        this.keuangan.forEach(row => {
                            const kode = row.kode_efek;
                            const isSaham = row.kategori === 'Saham';
                            const isObligasi = row.kategori === 'Obligasi';
                            
                            if (isSaham && sahamCodes.includes(kode)) {
                                // Check if critical fields are empty
                                if (!row.per || !row.pbv || !row.roe || !row.roa || !row.npm) {
                                    needsFetch.push({ kode, type: 'saham' });
                                }
                            } else if (isObligasi && obligasiCodes.includes(kode)) {
                                if (!row.ytm || !row.rating || !row.kupon || !row.der) {
                                    needsFetch.push({ kode, type: 'obligasi' });
                                }
                            } else if (row.kategori === 'Sukuk' && sukukCodes.includes(kode)) {
                                if (!row.ytm || !row.rating) {
                                    needsFetch.push({ kode, type: 'sukuk' });
                                }
                            }
                        });
                        
                        // Fetch in parallel (batch)
                        if (needsFetch.length > 0) {
                            console.log(`🔄 Auto-fetching financial data for ${needsFetch.length} items:`, needsFetch.map(n => n.kode).join(', '));
                            
                            const promises = needsFetch.map(item => 
                                fetch(`${this.getFinancialDataUrl}?kode=${encodeURIComponent(item.kode)}&type=${item.type}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.found && data.has_financial_data) {
                                            const idx = this.keuangan.findIndex(r => r.kode_efek === item.kode && r.kategori === (item.type === 'saham' ? 'Saham' : item.type === 'obligasi' ? 'Obligasi' : 'Sukuk'));
                                            if (idx !== -1) {
                                                const updated = { ...this.keuangan[idx], ...data.data };
                                                this.keuangan.splice(idx, 1, updated);
                                                console.log(`✓ Updated ${item.kode} from ${data.data_source}`);
                                            }
                                        }
                                    })
                                    .catch(err => console.warn(`Failed to fetch ${item.kode}:`, err))
                            );
                            
                            await Promise.all(promises);
                            console.log('✓ Auto-populate keuangan completed');
                        }
                    },

                    applyPembanding(id) {
                        if (!id) {
                            this.pembandingEfek = {};
                            this.pembandingMessage = '';
                            return;
                        }
                        this.pembandingLoading = true;
                        this.pembandingMessage = '';
                        this.$nextTick(() => {
                            const opt = this.ffsPembandingOptions.find(o => o.id === id);
                            if (!opt?.efek?.length) {
                                this.pembandingEfek = {};
                                this.pembandingLoading = false;
                                this.pembandingMessage = 'Tidak ada data efek pada pembanding ini.';
                                return;
                            }
                            const lookup = {};
                            opt.efek.forEach(e => { lookup[e.kode_efek] = e; });
                            this.pembandingEfek = lookup;
                            let matched = 0;
                            this.efek.forEach(row => {
                                const m = lookup[row.kode_efek];
                                if (m) {
                                    if (m.bobot_seharusnya != null) row.bobot_seharusnya = m.bobot_seharusnya;
                                    if (m.kontribusi_return != null) row.kontribusi_return = m.kontribusi_return;
                                    matched++;
                                }
                            });
                            this.pembandingLoading = false;
                            this.pembandingMessage = `${matched} efek dicocokkan dengan pembanding ${opt.label}.`;
                            setTimeout(() => { this.pembandingMessage = ''; }, 4000);
                        });
                    },

                    setFieldValue(id, value) {
                        if (value === null || value === undefined || value === '') return;
                        const el = document.getElementById(id);
                        if (el) {
                            el.value = value;
                            el.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                            el.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                        }
                    },

                    runAiPreview() {
                        const fd = new FormData(this.analisaFormEl());
                        const basicErr = this.validateAiBasics(fd);
                        if (basicErr) {
                            this.aiError = basicErr;
                            return;
                        }
                        this.aiLoading = true;
                        this.aiError = '';
                        fetch(this.previewAiUrl, {
                                method: 'POST',
                                body: this.buildFormPayload(),
                                headers: {
                                    'Accept': 'application/json'
                                }
                            })
                            .then(async r => {
                                const resp = await r.json();
                                if (!r.ok || !resp.success) {
                                    this.aiError = this.parseJsonError(resp);
                                    return;
                                }
                                this.aiResult = resp.data;
                            })
                            .catch(e => {
                                this.aiError = e.message || 'Gagal memproses';
                            })
                            .finally(() => {
                                this.aiLoading = false;
                            });
                    },

                    msgPlusIncomplete: 'Lengkapi data sektor/efek di tab Input Lengkap terlebih dahulu.',

                    isPlusManualReady() {
                        return (this.plusRequiredLabels?.aum ? String(this.totalAum || document.getElementById('total_aum')
                                ?.value || '').trim() !== '' : true) &&
                            (this.plusRequiredLabels?.marcap ? String(this.totalMarcap10Efek || document.getElementById(
                                'total_marcap_10_efek')?.value || '').trim() !== '' : true) &&
                            this.sektor.some(r => String(r.nama_sektor || '').trim() !== '' && r.bobot !== '' && r.bobot !=
                                null) &&
                            this.efek.some(r => String(r.kode_efek || '').trim() !== '' && String(r.nama_efek || '').trim() !==
                                '' && r.bobot !== '' && r.bobot != null);
                    },

                    plusMissingList() {
                        const missing = [];
                        if (this.plusRequiredLabels?.aum) {
                            const v = String(this.totalAum || document.getElementById('total_aum')?.value || '').trim();
                            if (!v) missing.push(this.plusRequiredLabels.aum);
                        }
                        if (this.plusRequiredLabels?.marcap) {
                            const v = String(this.totalMarcap10Efek || document.getElementById('total_marcap_10_efek')
                                ?.value || '').trim();
                            if (!v) missing.push(this.plusRequiredLabels.marcap);
                        }
                        if (this.plusRequiredLabels?.sektor && !this.sektor.some(r => String(r.nama_sektor || '').trim() !==
                                '' && r.bobot !== '' && r.bobot != null)) {
                            missing.push(this.plusRequiredLabels.sektor);
                        }
                        if (this.plusRequiredLabels?.efek && !this.efek.some(r => String(r.kode_efek || '').trim() !== '' &&
                                String(r.nama_efek || '').trim() !== '' && r.bobot !== '' && r.bobot != null)) {
                            missing.push(this.plusRequiredLabels.efek);
                        }
                        return missing;
                    },

                    plusIncompleteMessage() {
                        const missing = this.plusMissingList();
                        return 'Lengkapi data berikut: ' + missing.join(', ');
                    },

                    validatePlusManualData() {
                        const hasSektor = this.sektor.some(r =>
                            String(r.nama_sektor || '').trim() !== '' && r.bobot !== '' && r.bobot != null
                        );
                        const hasEfek = this.efek.some(r =>
                            String(r.kode_efek || '').trim() !== '' &&
                            String(r.nama_efek || '').trim() !== '' &&
                            r.bobot !== '' && r.bobot != null
                        );
                        if (!hasSektor && !hasEfek) {
                            return this.msgPlusIncomplete;
                        }
                        return null;
                    },

                    runAiPlusPreview() {
                        const fd = new FormData(this.analisaFormEl());
                        const basicErr = this.validateAiBasics(fd);
                        if (basicErr) {
                            this.aiPlusError = basicErr;
                            return;
                        }
                        const plusErr = this.validatePlusManualData();
                        if (plusErr) {
                            this.aiPlusError = plusErr;
                            return;
                        }
                        this.aiPlusLoading = true;
                        this.aiPlusError = '';
                        fetch(this.previewAiPlusUrl, {
                                method: 'POST',
                                body: this.buildFormPayload(),
                                headers: {
                                    'Accept': 'application/json'
                                }
                            })
                            .then(async r => {
                                const resp = await r.json();
                                if (!r.ok || !resp.success) {
                                    if (resp.missing?.length) {
                                        this.aiPlusError = resp.message || this.plusIncompleteMessage();
                                    } else {
                                        this.aiPlusError = this.parseJsonError(resp);
                                    }
                                    return;
                                }
                                this.aiPlusResult = resp.data;
                            })
                            .catch(e => {
                                this.aiPlusError = e.message || 'Gagal memproses';
                            })
                            .finally(() => {
                                this.aiPlusLoading = false;
                            });
                    },

                    applyAiToManual() {
                        if (!this.aiResult?.parsed) {
                            alert('Jalankan Analisa AI terlebih dahulu.');
                            return;
                        }
                        const p = this.aiResult.parsed;
                        const pdf = this.pdfData || {};

                        // Nama, jenis, AUM — prioritas AI, fallback PDF
                        const setField = (id, val) => this.setFieldValue(id, val);
                        setField('nama_reksa_dana', p.nama_reksa_dana || pdf.nama_reksa_dana);
                        setField('jenis_reksa_dana', p.jenis_reksa_dana || pdf.jenis_reksa_dana);
                        // ponytail: Tidak update Informasi Reksa Dana dari hasil parse
                        // this.totalAum = p.total_aum || pdf.total_aum || this.totalAum;
                        this.totalMarcap10Efek = p.total_marcap_10_efek || pdf.total_marcap_10_efek || this.totalMarcap10Efek;
                        this.tanggalData = p.tanggal_data || pdf.tanggal_data || this.tanggalData;
                        // this.unitPenyertaan = p.unit_penyertaan || pdf.unit_penyertaan || this.unitPenyertaan;
                        // this.nabPerUnit = p.nab_per_unit || pdf.nab_per_unit || this.nabPerUnit;
                        this.applyKategori(p.kategori || pdf.kategori || []);

                        // Sektor — dari AI (alokasi_aset), fallback PDF
                        if (p.alokasi_aset?.length) {
                            this.sektor = p.alokasi_aset.map(a => ({
                                nama_sektor: a.kategori || '',
                                bobot: a.persentase ?? '',
                            }));
                        } else if (pdf.sektor?.length) {
                            this.sektor = pdf.sektor;
                        }

                        // Efek — dari AI (daftar_efek), fallback PDF
                        if (p.daftar_efek?.length) {
                            this.efek = [...p.daftar_efek]
                                .sort((a, b) => (b.bobot || 0) - (a.bobot || 0))
                                .map((e, i) => ({
                                    kode_efek: e.kode_efek || '',
                                    nama_efek: e.nama_efek || '',
                                    sektor: e.sektor || '',
                                    bobot: e.bobot ?? '',
                                    bobot_seharusnya: e.bobot_seharusnya ?? '',
                                    kontribusi_kinerja: e.kontribusi_kinerja ?? '',
                                    market_cap: e.market_cap ?? '',
                                    nilai_pasar: '',
                                    return_1m: '',
                                    return_3m: '',
                                    return_6m: '',
                                    return_1y: '',
                                    kontribusi_return: e.kontribusi_return ?? '',
                                    effect_type: 'Saham',
                                    top_10: i < 10,
                                }));
                        } else if (pdf.efek?.length) {
                            this.efek = pdf.efek.map((e, i) => ({
                                kode_efek: e.kode_efek || '',
                                nama_efek: e.nama_efek || '',
                                sektor: e.sektor || '',
                                bobot: e.bobot ?? '',
                                bobot_seharusnya: e.bobot_seharusnya ?? '',
                                kontribusi_kinerja: e.kontribusi_kinerja ?? '',
                                market_cap: e.market_cap ?? '',
                                nilai_pasar: e.nilai_pasar ?? '',
                                return_1m: e.return_1m ?? '',
                                return_3m: e.return_3m ?? '',
                                return_6m: e.return_6m ?? '',
                                return_1y: e.return_1y ?? '',
                                kontribusi_return: e.kontribusi_return ?? '',
                                effect_type: e.effect_type || 'Saham',
                                top_10: i < 10,
                            }));
                        }

                        this.hitungSektorFromEfek();
                        this.syncLikuiditasFromEfek();
                        this.syncKeuanganFromData();

                        // Kinerja — dari PDF (AI tidak generate ini)
                        if (pdf.kinerja?.length >= 2) {
                            this.kinerja = pdf.kinerja;
                        } else if (pdf.kinerja?.length === 1) {
                            this.kinerja = [...pdf.kinerja, {
                                periode: '',
                                return_pct: ''
                            }];
                        }

                        // Obligasi, sukuk & bank — dari PDF
                        if (pdf.obligasi?.length) this.obligasi = pdf.obligasi;
                        if (pdf.sukuk?.length) this.sukuk = pdf.sukuk;
                        if (pdf.bank?.length) this.bank = pdf.bank;
                        this.syncKeuanganFromData();

                        const lkFields = [
                            'total_aset', 'total_liabilitas', 'nilai_aset_bersih', 'kas_dan_bank',
                            'piutang_bunga', 'piutang_dividen', 'piutang_lain',
                            'utang_pajak', 'utang_lain',
                            'pendapatan_bunga', 'pendapatan_dividen',
                            'gain_realized', 'gain_unrealized',
                            'beban_mi', 'beban_kustodian', 'beban_lain',
                            'laba_bersih', 'total_beban', 'laba_sebelum_pajak',
                            'beban_pajak_penghasilan', 'laba_bersih_tahun_berjalan',
                            'penghasilan_komprehensif_lain',
                            'penghasilan_komprehensif_lain_setelah_pajak',
                            'penghasilan_komprehensif_tahun_berjalan',
                            'total_pendapatan',
                            'arus_kas_operasi', 'arus_kas_pendanaan',
                            'kas_awal_tahun', 'kas_akhir_tahun', 'kas',
                            'portofolio_efek', 'instrumen_pasar_uang',
                            'piutang_transaksi_efek', 'piutang_bunga_dan_dividen',
                            'uang_muka_diterima', 'liabilitas_pembelian_kembali',
                            'beban_akrual', 'liabilitas_atas_biaya',
                            'pembelian_kembali_unit_penyertaan', 'utang_pajak_lainnya',
                            'pendapatan_investasi', 'pendapatan_lainnya',
                            'beban_investasi', 'beban_pengelolaan_investasi',
                            'pembelian_efek_ekuitas', 'penjualan_efek_ekuitas',
                            'penerimaan_bunga_deposito', 'penerimaan_bunga_jasa_giro',
                            'penerimaan_dividen_kas', 'pembayaran_jasa_pengelolaan',
                            'pembayaran_jasa_kustodian', 'pembayaran_beban_lain_arus',
                            'kas_bersih_aktivitas_operasi', 'penerimaan_penjualan_unit',
                            'pembayaran_pembelian_kembali_unit',
                            'kas_bersih_aktivitas_pendanaan', 'kenaikan_kas_setara_kas',
                            'total_hasil_investasi', 'hasil_investasi_setelah_biaya',
                            'persentase_pph',
                            'fair_value_level_1', 'fair_value_level_2', 'fair_value_level_3',
                            'unit_milik_investor', 'unit_milik_mi', 'total_unit_beredar',
                            'biaya_operasi', 'portfolio_turnover_ratio', 'total_return',
                            'management_fee', 'custodian_fee',
                        ];
                        for (const f of lkFields) {
                            if (p[f] !== undefined && p[f] !== null && p[f] !== '') {
                                setField(f, p[f]);
                            }
                        }

                        this.mode = 'manual';
                        alert('Data telah diterapkan ke Input Manual. Silakan review sebelum submit.');
                    },

                    normalizeExtractedData(data) {
                        data = data || {};

                        if (!Array.isArray(data.alokasi_aset)) {
                            data.alokasi_aset = [];
                        }
                        data.alokasi_aset = data.alokasi_aset.map(row => ({
                            nama_aset: row.nama_aset || row.nama || row.kategori || '',
                            persentase: row.persentase ?? row.bobot ?? '',
                        })).filter(row => String(row.nama_aset || '').trim() !== '' || String(row.persentase || '')
                            .trim() !== '');

                        // ponytail: extract data_tahunan from _raw_tables year-columns, overriding backend if wrong
                        if (!data.data_tambahan && Array.isArray(data._raw_tables)) {
                            const yearSet = new Set();
                            const byYear = {};
                            data._raw_tables.forEach(group => {
                                if (!Array.isArray(group.tables)) return;
                                group.tables.forEach(table => {
                                    if (!Array.isArray(table.headers) || !Array.isArray(table.rows)) return;
                                    const yearCols = [];
                                    table.headers.forEach((h, i) => {
                                        if (i === 0) return;
                                        const m = String(h).match(/^(\d{4})$/);
                                        if (m) yearCols.push({ idx: i, year: m[1] });
                                    });
                                    if (!yearCols.length) return;
                                    table.rows.forEach(row => {
                                        if (!Array.isArray(row) || row.length < 2) return;
                                        const label = String(row[0] || '').trim().toLowerCase()
                                            .replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
                                        if (!label) return;
                                        yearCols.forEach(({ idx, year }) => {
                                            yearSet.add(year);
                                            if (!byYear[year]) byYear[year] = {};
                                            const val = row[idx];
                                            if (val !== null && val !== undefined && val !== '') {
                                                let parsed = val;
                                                if (typeof parsed === 'string') {
                                                    const cleaned = parsed.replace(/\./g, '').replace(',', '.').trim();
                                                    const negMatch = cleaned.match(/^\((\d+(?:\.\d+)?)\)$/);
                                                    const numStr = negMatch ? negMatch[1] : cleaned;
                                                    const num = Number(numStr);
                                                    if (!isNaN(num)) {
                                                        parsed = negMatch ? -num : num;
                                                    }
                                                }
                                                byYear[year][label] = parsed;
                                            }
                                        });
                                    });
                                });
                            });
                            if (yearSet.size > 0) {
                                const sortedYears = [...yearSet].sort((a, b) => Number(b) - Number(a));
                                data.data_tahunan = { years: sortedYears };
                                sortedYears.forEach(year => { data.data_tahunan[year] = byYear[year] || {}; });
                                data.tahun_laporan = sortedYears[0];
                                const latest = data.data_tahunan[data.tahun_laporan];
                                if (latest) {
                                    Object.entries(latest).forEach(([key, val]) => {
                                        if (val !== null && val !== undefined && val !== '') {
                                            data[key] = val;
                                        }
                                    });
                                }
                            }
                        }

                        // ponytail: convert backend data_tahunan shape to frontend dataTambahan/tahunTambahan
                        if (data.data_tahunan && !data.data_tambahan) {
                            const snakeToCamel = s => s.replace(/_([a-z])/g, (_, l) => l.toUpperCase());
                            const years = Array.isArray(data.data_tahunan.years) ? [...data.data_tahunan.years].sort((a, b) => Number(b) - Number(a)) : [];
                            // Backend flattens latest year (years[0] after desc sort) to scalar fields.
                            // Exclude it from tambahan so it doesn't duplicate in the form.
                            const latestYear = years.length > 0 ? String(years[0]) : null;
                            data.tahun_tambahan = latestYear ? years.filter(y => String(y) !== latestYear) : years;
                            data.data_tambahan = {};
                            data.tahun_tambahan.forEach(year => {
                                if (data.data_tahunan[year] && typeof data.data_tahunan[year] === 'object') {
                                    data.data_tambahan[year] = {};
                                    Object.entries(data.data_tahunan[year]).forEach(([key, val]) => {
                                        data.data_tambahan[year][snakeToCamel(key)] = val;
                                    });
                                }
                            });
                            // Flatten latest year to scalar fields (backup jika backend belum flatten)
                            if (latestYear && data.data_tahunan[latestYear]) {
                                Object.entries(data.data_tahunan[latestYear]).forEach(([key, val]) => {
                                    if (val !== null && val !== undefined && val !== '' && data[key] === undefined) {
                                        data[key] = val;
                                    }
                                });
                            }
                            // Set tahun_laporan dari latest year jika belum ada
                            if (latestYear && !data.tahun_laporan) {
                                data.tahun_laporan = latestYear;
                            }
                        }

                        return data;
                    },

                    hasFullInputData(data) {
                        data = this.normalizeExtractedData({
                            ...data
                        });
                        return Boolean(
                            data.total_aum ||
                            data.total_marcap_10_efek ||
                            data.total_aset ||
                            data.laba_bersih ||
                            data.arus_kas_operasi ||
                            data.total_hasil_investasi ||
                            data.fair_value_level_1 ||
                            data.unit_milik_investor ||
                            data.sektor?.length ||
                            data.efek?.length ||
                            data.kinerja?.length ||
                            data.obligasi?.length ||
                            data.sukuk?.length ||
                            data.bank?.length
                        );
                    },

                    applyExtractedData(data, preferredMode = 'manual') {
                        data = this.normalizeExtractedData(data);
                        const fields = {
                            nama_reksa_dana: 'nama_reksa_dana',
                            jenis_reksa_dana: 'jenis_reksa_dana',
                            manajer_investasi: 'manajer_investasi',
                            bank_kustodian: 'bank_kustodian',
                            tanggal_peluncuran: 'tanggal_peluncuran',
                            mata_uang: 'mata_uang',
                            benchmark: 'benchmark',
                            tujuan_investasi: 'tujuan_investasi',
                            kebijakan_investasi: 'kebijakan_investasi',
                        };
                        for (const [key, id] of Object.entries(fields)) {
                            this.setFieldValue(id, data[key]);
                        }
                        this.kodeReksaDana = data.kode_reksa_dana ?? this.kodeReksaDana;
                        this.namaReksaDana = data.nama_reksa_dana ?? this.namaReksaDana;
                        this.jenisReksaDana = data.jenis_reksa_dana ?? this.jenisReksaDana;
                        this.manajerInvestasi = data.manajer_investasi ?? this.manajerInvestasi;
                        this.bankKustodian = data.bank_kustodian ?? this.bankKustodian;
                        if (data.tanggal_peluncuran) this.tanggalPeluncuran = data.tanggal_peluncuran;
                        this.mataUang = data.mata_uang ?? this.mataUang;
                        this.benchmark = data.benchmark ?? this.benchmark;
                        this.tujuanInvestasi = data.tujuan_investasi ?? this.tujuanInvestasi;
                        this.kebijakanInvestasi = data.kebijakan_investasi ?? this.kebijakanInvestasi;
                        // ponytail: Tidak update Informasi Reksa Dana dari hasil parse
                        // this.totalAum = data.total_aum ?? this.totalAum;
                        this.totalMarcap10Efek = data.total_marcap_10_efek ?? this.totalMarcap10Efek;
                        this.tanggalData = data.tanggal_data ?? this.tanggalData;
                        // this.unitPenyertaan = data.unit_penyertaan ?? this.unitPenyertaan;
                        // this.nabPerUnit = data.nab_per_unit ?? this.nabPerUnit;
                        this.return1m = data.return_1m ?? this.return1m;
                        this.returnYtd = data.return_ytd ?? this.returnYtd;
                        this.return1y = data.return_1y ?? this.return1y;
                        this.totalReturn = data.total_return ?? this.totalReturn;
                        this.biayaOperasi = data.biaya_operasi ?? this.biayaOperasi;
                        this.portfolioTurnover = data.portfolio_turnover_ratio ?? this.portfolioTurnover;
                        this.managementFee = data.management_fee ?? this.managementFee;
                        this.custodianFee = data.custodian_fee ?? this.custodianFee;
                        this.totalAset = data.total_aset ?? this.totalAset;
                        this.totalLiabilitas = data.total_liabilitas ?? this.totalLiabilitas;
                        this.nilaiAsetBersih = data.nilai_aset_bersih ?? this.nilaiAsetBersih;
                        this.kasDanBank = data.kas_dan_bank ?? this.kasDanBank;
                        this.piutangBunga = data.piutang_bunga ?? this.piutangBunga;
                        this.piutangDividen = data.piutang_dividen ?? this.piutangDividen;
                        this.piutangLain = data.piutang_lain ?? this.piutangLain;
                        this.utangPajak = data.utang_pajak ?? this.utangPajak;
                        this.utangLain = data.utang_lain ?? this.utangLain;
                        this.pendapatanBunga = data.pendapatan_bunga ?? this.pendapatanBunga;
                        this.pendapatanDividen = data.pendapatan_dividen ?? this.pendapatanDividen;
                        this.gainRealized = data.gain_realized ?? this.gainRealized;
                        this.gainUnrealized = data.gain_unrealized ?? this.gainUnrealized;
                        this.bebanMi = data.beban_mi ?? this.bebanMi;
                        this.bebanKustodian = data.beban_kustodian ?? this.bebanKustodian;
                        this.bebanLain = data.beban_lain ?? this.bebanLain;
                        this.labaBersih = data.laba_bersih ?? this.labaBersih;
                        this.totalBeban = data.total_beban ?? this.totalBeban;
                        this.labaSebelumPajak = data.laba_sebelum_pajak ?? this.labaSebelumPajak;
                        this.bebanPajakPenghasilan = data.beban_pajak_penghasilan ?? this.bebanPajakPenghasilan;
                        this.labaBersihTahunBerjalan = data.laba_bersih_tahun_berjalan ?? this.labaBersihTahunBerjalan;
                        this.penghasilanKomprehensifLain = data.penghasilan_komprehensif_lain ?? this
                            .penghasilanKomprehensifLain;
                        this.penghasilanKomprehensifLainSetelahPajak = data.penghasilan_komprehensif_lain_setelah_pajak ?? this
                            .penghasilanKomprehensifLainSetelahPajak;
                        this.penghasilanKomprehensifTahunBerjalan = data.penghasilan_komprehensif_tahun_berjalan ?? this
                            .penghasilanKomprehensifTahunBerjalan;
                        this.arusKasOperasi = data.arus_kas_operasi ?? this.arusKasOperasi;
                        this.arusKasPendanaan = data.arus_kas_pendanaan ?? this.arusKasPendanaan;
                        this.kasAwalTahun = data.kas_awal_tahun ?? this.kasAwalTahun;
                        this.kasAkhirTahun = data.kas_akhir_tahun ?? this.kasAkhirTahun;
                        this.kas = data.kas ?? this.kas;
                        this.portofolioEfek = data.portofolio_efek ?? this.portofolioEfek;
                        this.instrumenPasarUang = data.instrumen_pasar_uang ?? this.instrumenPasarUang;
                        this.piutangTransaksiEfek = data.piutang_transaksi_efek ?? this.piutangTransaksiEfek;
                        this.piutangBungaDanDividen = data.piutang_bunga_dan_dividen ?? this.piutangBungaDanDividen;
                        this.uangMukaDiterima = data.uang_muka_diterima ?? this.uangMukaDiterima;
                        this.liabilitasPembelianKembali = data.liabilitas_pembelian_kembali ?? this.liabilitasPembelianKembali;
                        this.bebanAkrual = data.beban_akrual ?? this.bebanAkrual;
                        this.liabilitasAtasBiaya = data.liabilitas_atas_biaya ?? this.liabilitasAtasBiaya;
                        this.pembelianKembaliUnit = data.pembelian_kembali_unit_penyertaan ?? this.pembelianKembaliUnit;
                        this.utangPajakLainnya = data.utang_pajak_lainnya ?? this.utangPajakLainnya;
                        this.pendapatanInvestasi = data.pendapatan_investasi ?? this.pendapatanInvestasi;
                        this.pendapatanLainnya = data.pendapatan_lainnya ?? this.pendapatanLainnya;
                        this.totalPendapatan = data.total_pendapatan ?? this.totalPendapatan;
                        this.bebanInvestasi = data.beban_investasi ?? this.bebanInvestasi;
                        this.bebanPengelolaanInvestasi = data.beban_pengelolaan_investasi ?? this.bebanPengelolaanInvestasi;
                        this.pembelianEfekEkuitas = data.pembelian_efek_ekuitas ?? this.pembelianEfekEkuitas;
                        this.penjualanEfekEkuitas = data.penjualan_efek_ekuitas ?? this.penjualanEfekEkuitas;
                        this.penerimaanBungaDeposito = data.penerimaan_bunga_deposito ?? this.penerimaanBungaDeposito;
                        this.penerimaanBungaJasaGiro = data.penerimaan_bunga_jasa_giro ?? this.penerimaanBungaJasaGiro;
                        this.penerimaanDividenKas = data.penerimaan_dividen_kas ?? this.penerimaanDividenKas;
                        this.pembayaranJasaPengelolaan = data.pembayaran_jasa_pengelolaan ?? this.pembayaranJasaPengelolaan;
                        this.pembayaranJasaKustodian = data.pembayaran_jasa_kustodian ?? this.pembayaranJasaKustodian;
                        this.pembayaranBebanLainArus = data.pembayaran_beban_lain_arus ?? this.pembayaranBebanLainArus;
                        this.kasBersihAktivitasOperasi = data.kas_bersih_aktivitas_operasi ?? this.kasBersihAktivitasOperasi;
                        this.penerimaanPenjualanUnit = data.penerimaan_penjualan_unit ?? this.penerimaanPenjualanUnit;
                        this.pembayaranPembelianKembaliUnit = data.pembayaran_pembelian_kembali_unit ?? this.pembayaranPembelianKembaliUnit;
                        this.kasBersihAktivitasPendanaan = data.kas_bersih_aktivitas_pendanaan ?? this.kasBersihAktivitasPendanaan;
                        this.kenaikanKasSetaraKas = data.kenaikan_kas_setara_kas ?? this.kenaikanKasSetaraKas;
                        this.totalHasilInvestasi = data.total_hasil_investasi ?? this.totalHasilInvestasi;
                        this.hasilInvestasiSetelahBiaya = data.hasil_investasi_setelah_biaya ?? this.hasilInvestasiSetelahBiaya;
                        this.persentasePph = data.persentase_pph ?? this.persentasePph;
                        this.fairValueLevel1 = data.fair_value_level_1 ?? this.fairValueLevel1;
                        this.fairValueLevel2 = data.fair_value_level_2 ?? this.fairValueLevel2;
                        this.fairValueLevel3 = data.fair_value_level_3 ?? this.fairValueLevel3;
                        this.unitMilikInvestor = data.unit_milik_investor ?? this.unitMilikInvestor;
                        this.unitMilikMi = data.unit_milik_mi ?? this.unitMilikMi;
                        this.totalUnitBeredar = data.total_unit_beredar ?? this.totalUnitBeredar;
                        this.tahunLaporan = data.tahun_laporan ?? this.tahunLaporan;
                        if (data.tahun_tambahan?.length) {
                            this.tahunTambahan = data.tahun_tambahan;
                            this.dataTambahan = {};
                        }
                        if (data.data_tambahan) this.dataTambahan = {
                            ...this.dataTambahan,
                            ...data.data_tambahan
                        };
                        if (data.sektor?.length) this.sektor = data.sektor;
                        if (data.efek?.length) {
                            this.efek = data.efek.map(e => ({
                                kode_efek: e.kode_efek || '',
                                nama_efek: e.nama_efek || '',
                                sektor: e.sektor || '',
                                bobot: e.bobot ?? '',
                                bobot_seharusnya: e.bobot_seharusnya ?? '',
                                kontribusi_kinerja: e.kontribusi_kinerja ?? '',
                                market_cap: e.market_cap ?? '',
                                nilai_pasar: e.nilai_pasar ?? '',
                                harga_perolehan: e.harga_perolehan ?? '',
                                persen_nab: e.persen_nab ?? '',
                                return_1m: e.return_1m ?? '',
                                return_3m: e.return_3m ?? '',
                                return_6m: e.return_6m ?? '',
                                return_1y: e.return_1y ?? '',
                                kontribusi_return: e.kontribusi_return ?? '',
                                effect_type: e.effect_type || 'Saham',
                                top_10: e.top_10 === 'Ya' || e.top_10 === true,
                            }));
                            this.$nextTick(() => {
                                this.efek.forEach((_, i) => {
                                    this.hitungNilaiPasarEfek(i);
                                    setTimeout(() => this.lookupEfekData(i), i * 600);
                                });
                            });
                        }
                        this.hitungSektorFromEfek();
                        this.syncLikuiditasFromEfek();
                        this.syncKeuanganFromData();
                        if (data.kinerja?.length >= 2) {
                            this.kinerja = data.kinerja;
                        } else if (data.kinerja?.length === 1) {
                            this.kinerja = [...data.kinerja, {
                                periode: '',
                                return_pct: ''
                            }];
                        }
                        if (data.obligasi?.length) {
                            this.obligasi = data.obligasi.map(o => ({
                                kode_obligasi: o.kode_obligasi || '',
                                nama_obligasi: o.nama_obligasi || '',
                                bobot: o.bobot ?? '',
                                nilai_pasar: o.nilai_pasar ?? '',
                                ytm: o.ytm ?? '',
                                kupon: o.kupon ?? '',
                                tanggal_jatuh_tempo: o.tanggal_jatuh_tempo || '',
                                penerbit: o.penerbit || '',
                                persen_nab: o.persen_nab ?? '',
                                return_1m: o.return_1m ?? '',
                                return_3m: o.return_3m ?? '',
                                return_6m: o.return_6m ?? '',
                                return_1y: o.return_1y ?? '',
                                durasi: o.durasi ?? '',
                                rating: o.rating || '',
                            }));
                            this.$nextTick(() => {
                                this.obligasi.forEach((_, i) => {
                                    this.hitungNilaiPasarObligasi(i);
                                    setTimeout(() => this.lookupObligasiData(i), i * 600);
                                });
                            });
                        }
                        if (data.sukuk?.length) {
                            this.sukuk = data.sukuk.map(s => ({
                                kode_sukuk: s.kode_sukuk || '',
                                nama_sukuk: s.nama_sukuk || '',
                                jenis_sukuk: s.jenis_sukuk || '',
                                bobot: s.bobot ?? '',
                                yield: s.yield ?? '',
                                jatuh_tempo: s.jatuh_tempo || '',
                                persen_nab: s.persen_nab ?? '',
                                rating: s.rating || '',
                            }));
                        }
                        if (data.bank?.length) {
                            this.bank = data.bank.map(b => ({
                                nama_bank: b.nama_bank || '',
                                jenis_bank: b.jenis_bank || '',
                                bobot: b.bobot ?? '',
                                nilai_pasar: b.nilai_pasar ?? '',
                                tingkat_bunga: b.tingkat_bunga ?? '',
                                jangka_waktu: b.jangka_waktu ?? '',
                                persen_nab: b.persen_nab ?? '',
                                return_1m: b.return_1m ?? '',
                                return_3m: b.return_3m ?? '',
                                return_6m: b.return_6m ?? '',
                                return_1y: b.return_1y ?? '',
                                car: b.car ?? '',
                                npl: b.npl ?? '',
                                klasifikasi_risiko: b.klasifikasi_risiko || '',
                            }));
                            this.$nextTick(() => {
                                this.bank.forEach((_, i) => {
                                    this.hitungNilaiPasarBank(i);
                                    setTimeout(() => this.lookupBankData(i), i * 600);
                                });
                            });
                        }
                        this.syncKeuanganFromData();
                        if (data.alokasi_aset?.length) this.alokasi_aset = data.alokasi_aset;
                        this.applyKategori(data.kategori || []);
                        this.mode = preferredMode;
                    },

                    applyKategori(kategori) {
                        const values = Array.isArray(kategori) ? kategori : String(kategori || '').split(/[,;|]/);
                        const normalized = values.map(v => String(v).trim().toLowerCase()).filter(Boolean);
                        document.querySelectorAll('input[name="kategori[]"]').forEach(input => {
                            const value = String(input.value).toLowerCase();
                            input.checked = normalized.some(k => k === value || (k === 'indeks' && value === 'index'));
                        });
                    },

                    fillFromWebFile() {
                        if (!this.webFile) {
                            alert('Pilih file unduhan dari situs terlebih dahulu.');
                            return;
                        }
                        this.webLoading = true;
                        this.webMessage = '';
                        const formData = new FormData();
                        formData.append('file', this.webFile);
                        formData.append('_token', this.analisaFormEl().querySelector('input[name="_token"]').value);
                        fetch(this.parseWebFileUrl, {
                                method: 'POST',
                                headers: {
                                    Accept: 'application/json'
                                },
                                body: formData,
                            })
                            .then(res => res.json().then(body => ({
                                ok: res.ok,
                                body
                            })))
                            .then(({
                                ok,
                                body
                            }) => {
                                this.webLoading = false;
                                if (!ok || !body.success) {
                                    this.webOk = false;
                                    this.webMessage = body.message || 'Gagal membaca file.';
                                    return;
                                }
                                this.applyExtractedData(body.data);
                                this.exportFileUrl = body.export_file || null;
                                this.webOk = true;
                                this.webMessage = body.message +
                                    ' Data sudah di tab Input Manual — lengkapi Jenis RD jika perlu, lalu Submit Analisa.' +
                                    (body.export_file ? ' <a href="' + body.export_file + '" class="underline font-semibold" download>Download Excel</a>.' : '');
                            })
                            .catch(() => {
                                this.webLoading = false;
                                this.webOk = false;
                                this.webMessage = 'Gagal memproses file.';
                            });
                    },

                    importExcel(event) {
                        const file = event.target.files[0];
                        if (!file) return;
                        this.importExcelLoading = true;
                        this.importExcelMessage = '';
                        this.importExcelOk = false;
                        const formData = new FormData();
                        formData.append('file', file);
                        formData.append('_token', this.analisaFormEl().querySelector('input[name="_token"]').value);
                        fetch(this.importExcelPreviewUrl, {
                                method: 'POST',
                                headers: { Accept: 'application/json' },
                                body: formData,
                            })
                            .then(res => res.json().then(body => ({ ok: res.ok, body })))
                            .then(({ ok, body }) => {
                                this.importExcelLoading = false;
                                if (!ok || !body.success) {
                                    this.importExcelOk = false;
                                    this.importExcelMessage = body.message || 'Gagal membaca file Excel.';
                                    return;
                                }
                                const extractedData = this.normalizeExtractedData(body.data || {});
                                this.pdfData = extractedData;
                                this.applyExtractedData(extractedData, this.hasFullInputData(extractedData) ? 'lengkap' : 'manual');
                                this.exportFileUrl = body.export_file || null;
                                this.importExcelOk = true;
                                const tab = this.hasFullInputData(extractedData) ? 'Input Lengkap' : 'Input Manual';
                                this.importExcelMessage = body.message + ' Cek tab ' + tab + '.' +
                                    (body.export_file ? ' <a href="' + body.export_file + '" class="underline font-semibold" download>Download Excel</a>.' : '');
                                event.target.value = '';
                            })
                            .catch(() => {
                                this.importExcelLoading = false;
                                this.importExcelOk = false;
                                this.importExcelMessage = 'Gagal mengupload file.';
                            });
                    },

                    scrapeFromLink(linkId) {
                        this.webLoading = true;
                        this.webMessage = '';
                        const formData = new FormData();
                        formData.append('data_source_link_id', linkId);
                        formData.append('_token', this.analisaFormEl().querySelector('input[name="_token"]').value);
                        fetch(this.scrapeWebUrl, {
                                method: 'POST',
                                headers: {
                                    Accept: 'application/json'
                                },
                                body: formData,
                            })
                            .then(res => res.json().then(body => ({
                                ok: res.ok,
                                body
                            })))
                            .then(({
                                ok,
                                body
                            }) => {
                                this.webLoading = false;
                                if (!ok || !body.success) {
                                    this.webOk = false;
                                    this.webMessage = body.message ||
                                        'Unduh otomatis gagal. Unduh manual dari link, lalu pilih file di bawah.';
                                    return;
                                }
                                this.applyExtractedData(body.data);
                                this.exportFileUrl = body.export_file || null;
                                this.webOk = true;
                                this.webMessage = body.message +
                                    ' Data sudah di tab Input Manual — lengkapi Jenis RD jika perlu, lalu Submit Analisa.' +
                                    (body.export_file ? ' <a href="' + body.export_file + '" class="underline font-semibold" download>Download Excel</a>.' : '');
                            })
                            .catch(() => {
                                this.webLoading = false;
                                this.webOk = false;
                                this.webMessage = 'Unduh otomatis gagal. Gunakan link → unduh manual → Isi Form Otomatis.';
                            });
                    },

                    scrapeUrl(url) {
                        this.webLoading = true;
                        this.webMessage = '';
                        const formData = new FormData();
                        formData.append('url', url);
                        formData.append('_token', this.analisaFormEl().querySelector('input[name="_token"]').value);
                        fetch(@json($formRoutes['scrape_url']), {
                                method: 'POST',
                                headers: {
                                    Accept: 'application/json'
                                },
                                body: formData,
                            })
                            .then(res => res.json().then(body => ({
                                ok: res.ok,
                                body
                            })))
                            .then(({
                                ok,
                                body
                            }) => {
                                this.webLoading = false;
                                if (!ok || !body.success) {
                                    this.webOk = false;
                                    this.webMessage = body.message || 'Gagal scrape URL.';
                                    return;
                                }
                                this.applyExtractedData(body.data);
                                this.exportFileUrl = body.export_file || null;
                                this.webOk = true;
                                this.webMessage = body.message +
                                    ' Data sudah di tab Input Manual — lengkapi Jenis RD jika perlu, lalu Submit Analisa.' +
                                    (body.export_file ? ' <a href="' + body.export_file + '" class="underline font-semibold" download>Download Excel</a>.' : '');
                                this.mode = 'manual';
                            })
                            .catch(() => {
                                this.webLoading = false;
                                this.webOk = false;
                                this.webMessage = 'Gagal scrape URL.';
                            });
                    },

                    parsePdf(event) {
                        const file = event.target.files[0];
                        if (!file) return;

                        this.pdfLoading = true;
                        this.pdfResult = '';
                        this.pdfSuccess = false;

                        const formData = new FormData();
                        formData.append('file_pdf', file);
                        formData.append('_token', this.analisaFormEl().querySelector('input[name="_token"]').value);

                        let url;
                        if (this.pdfScanMode === 'vision' && this.parsePdfVisionUrl) {
                            url = this.parsePdfVisionUrl;
                        } else {
                            url = @json($formRoutes['parse_pdf']);
                        }

                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                },
                                body: formData,
                            })
                            .then(res => {
                                if (!res.ok) {
                                    return res.json().then(err => {
                                        throw new Error(err.message || 'Gagal parsing PDF');
                                    });
                                }
                                return res.json();
                            })
                            .then(resp => {
                                this.pdfLoading = false;
                                if (!resp.success) {
                                    this.pdfSuccess = false;
                                    this.pdfResult = resp.message;
                                    return;
                                }
                                // Simpan data hasil ekstraksi ke state untuk ditampilkan di tabel pratinjau.
                                const extractedData = this.normalizeExtractedData(resp.data || {});
                                this.pdfData = extractedData;
                                this.pdfFile = resp.pdf_file || '';
                                this.exportFileUrl = resp.export_file || null;
                                this.pdfSuccess = true;
                                this.pdfResult = resp.message +
                                    (resp.export_file ? ' <a href="' + resp.export_file + '" class="underline font-semibold" download>Download Excel</a>.' : '');
                            })
                            .catch(err => {
                                this.pdfLoading = false;
                                this.pdfSuccess = false;
                                this.pdfResult = 'Gagal: ' + err.message;
                            });
                    },

                    async parseAllDocs() {
                        const token = this.analisaFormEl().querySelector('input[name="_token"]').value;
                        const slotsToProcess = this.docSlots.filter(s => s.file);
                        const libraryDocIds = [...this.selectedDocIds];
                        if (!slotsToProcess.length && !libraryDocIds.length) return;

                        this.multiParseLoading = true;
                        this.multiParseResult = '';
                        this.multiParseSuccess = false;
                        this.docSlots.forEach(s => {
                            s.success = null;
                            s.message = '';
                            s.data = null;
                        });

                        // Parse 4 upload slots
                        const promises = slotsToProcess.map(slot => {
                            slot.loading = true;
                            const fd = new FormData();
                            fd.append('file_pdf', slot.file);
                            fd.append('document_type', slot.type);
                            fd.append('_token', token);
                            let url;
                                if (this.pdfScanMode === 'vision' && this.parsePdfVisionUrl) {
                                url = this.parsePdfVisionUrl;
                            } else {
                                url = @json($formRoutes['parse_pdf']);
                            }
                            return fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json'
                                    },
                                    body: fd
                                })
                                .then(r => r.json()).then(resp => ({
                                    slot,
                                    resp
                                })).catch(e => ({
                                    slot,
                                    error: e.message
                                }));
                        });

                        // Parse selected documents from "Dokumen Tersimpan"
                        const libraryPromises = libraryDocIds.map(docId =>
                            fetch(this.parseExistingDocUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token
                                },
                                body: JSON.stringify({
                                    document_id: docId,
                                    parse_mode: 'ai'
                                }),
                            }).then(r => r.json()).then(resp => ({
                                isLibrary: true,
                                docId,
                                resp
                            })).catch(e => ({
                                isLibrary: true,
                                docId,
                                error: e.message
                            }))
                        );

                        const results = await Promise.allSettled([...promises, ...libraryPromises]);
                        let successCount = 0;
                        let libraryData = [];

                        results.forEach(r => {
                            const val = r.value || r.reason || {};
                            if (val.isLibrary) {
                                if (!val.error && val.resp?.success) {
                                    libraryData.push(this.normalizeExtractedData(val.resp.data || {}));
                                    successCount++;
                                }
                            } else {
                                const {
                                    slot,
                                    resp,
                                    error
                                } = val;
                                if (!slot) return;
                                slot.loading = false;
                                if (error || !resp?.success) {
                                    slot.success = false;
                                    slot.message = error || resp?.message || 'Gagal parse';
                                } else {
                                    slot.success = true;
                                    slot.data = this.normalizeExtractedData(resp.data || {});
                                    const fieldCount = Object.keys(slot.data).filter(k => {
                                        const v = slot.data[k];
                                        return v !== null && v !== undefined && v !== '' && !(Array.isArray(
                                            v) && v.length === 0);
                                    }).length;
                                    slot.message = `${fieldCount} field diekstrak`;
                                    successCount++;
                                }
                            }
                        });

                        // Smart merge: library docs first (base), then 4 slots override
                        // - Scalar fields: later slot wins (more specific)
                        // - Array fields: longest array wins (most complete)
                        let mergedData = {};
                        const allData = [...libraryData, ...this.docSlots.filter(s => s.success && s.data).map(s => s
                            .data)];
                        allData.forEach(data => {
                            Object.keys(data).forEach(k => {
                                const v = data[k];
                                if (v === null || v === undefined || v === '') return;
                                if (Array.isArray(v)) {
                                    if (v.length === 0) return;
                                    if (!mergedData[k] || !Array.isArray(mergedData[k]) || v.length >
                                        mergedData[k].length) {
                                        mergedData[k] = v;
                                    }
                                } else {
                                    mergedData[k] = v;
                                }
                            });
                        });

                        const totalFields = Object.keys(mergedData).filter(k => {
                            const v = mergedData[k];
                            return v !== null && v !== undefined && v !== '' && !(Array.isArray(v) && v.length ===
                                0);
                        }).length;

                        if (totalFields > 0) {
                            this.pdfData = mergedData;
                        }

                        this.multiParseLoading = false;
                        const total = slotsToProcess.length + libraryDocIds.length;
                        this.multiParseSuccess = successCount > 0;
                        this.multiParseResult = `${successCount}/${total} dokumen berhasil. ${totalFields} field terisi.`;

                        if (successCount > 0) {
                            alert(
                                '⚠️ Data hasil ekstraksi AI bisa saja tidak akurat atau tidak lengkap. Mohon periksa dan validasi setiap field sebelum menyimpan.'
                            );
                        }
                    },

                    addPageRange() {
                        const lastId = this.pageRanges.length > 0 ? Math.max(...this.pageRanges.map(r => r.id)) : 0;
                        this.pageRanges.push({
                            id: lastId + 1,
                            start_page: '',
                            end_page: '',
                            section_type: 'auto',
                            loading: false,
                            success: null,
                            message: '',
                            data: null
                        });
                    },

                    removePageRange(idx) {
                        if (this.pageRanges.length > 1) {
                            this.pageRanges.splice(idx, 1);
                        }
                    },

                    async parseAllPageRanges() {
                        const token = this.analisaFormEl().querySelector('input[name="_token"]').value;

                        if (!this.selectedDocId) {
                            this.partitionResult = 'Pilih dokumen terlebih dahulu.';
                            this.partitionSuccess = false;
                            return;
                        }

                        if (!this.parseExistingDocUrl) {
                            this.partitionResult = 'URL parse tidak tersedia.';
                            this.partitionSuccess = false;
                            return;
                        }

                        const rangesWithValues = this.pageRanges.map((r, idx) => ({...r, idx}));
                        const validRanges = rangesWithValues.filter(r => r.start_page && r.end_page);
                        if (!validRanges.length) {
                            this.partitionResult = 'Isi minimal 1 partisi halaman (Start Page & End Page).';
                            this.partitionSuccess = false;
                            return;
                        }

                        this.partitionLoading = true;
                        this.partitionResult = '';
                        this.partitionSuccess = false;
                        this.pageRanges.forEach(r => { r.success = null; r.message = ''; r.data = null; });
                        validRanges.forEach(r => { this.pageRanges[r.idx].loading = true; });

                        try {
                            const response = await fetch(this.parseExistingDocUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token
                                },
                                body: JSON.stringify({
                                    document_id: this.selectedDocId,
                                    parse_mode: 'hybrid',
                                    page_ranges: validRanges.map(r => ({
                                        start_page: parseInt(r.start_page),
                                        end_page: parseInt(r.end_page),
                                        section_type: r.section_type
                                    }))
                                })
                            });

                            const resp = await response.json();

                            validRanges.forEach(r => {
                                const range = this.pageRanges[r.idx];
                                if (range) range.loading = false;
                            });

                            if (resp?.success) {
                                const data = resp.data || {};

                                const extracted = this.normalizeExtractedData(data);
                                const fieldCount = Object.keys(extracted).filter(k => {
                                    const v = extracted[k];
                                    return v !== null && v !== undefined && v !== '' && !(Array.isArray(v) && v.length === 0);
                                }).length;
                                this.exportFileUrl = resp.export_file || null;
                                if (resp.export_file) window.open(resp.export_file, '_blank');
                                validRanges.forEach(r => {
                                    const range = this.pageRanges[r.idx];
                                    if (range) { range.success = true; range.data = data; range.message = `${fieldCount} field`; }
                                });

                                // Collect tables from _raw_tables
                                const allTables = [];
                                const rawTablesData = data._raw_tables || [];
                                rawTablesData.forEach(p => {
                                    if (Array.isArray(p.tables)) {
                                        p.tables.forEach(t => allTables.push(t));
                                    }
                                });

                                this.partitionTables = allTables;
                                this.activeTableTab = 0;

                                // Group tables by table_name, merge identical headers, dedup rows by label
                                const groups = {};
                                allTables.forEach(t => {
                                    const name = t.table_name || 'Lainnya';
                                    const hKey = JSON.stringify(t.headers || []);
                                    if (!groups[name]) groups[name] = {};
                                    if (!groups[name][hKey]) {
                                        groups[name][hKey] = { headers: t.headers || [], rows: [] };
                                    }
                                    const seen = new Set(groups[name][hKey].rows.map(r => String(r[0] || '')));
                                    (t.rows || []).forEach(row => {
                                        const label = String(row[0] || '');
                                        if (!seen.has(label)) { groups[name][hKey].rows.push(row); seen.add(label); }
                                    });
                                });
                                const order = ['Aset','Liabilitas','Pendapatan','Beban','Arus Kas Operasi','Arus Kas Pendanaan','Pengukuran Nilai Wajar','Informasi Lainnya','Portofolio Efek','Obligasi','Sektor','Sukuk','Bank'];
                                const grouped = [];
                                Object.entries(groups).forEach(([name, byHeaders]) => {
                                    Object.values(byHeaders).forEach(({headers, rows}) => {
                                        grouped.push({ table_name: name, tables: [{ headers, rows }] });
                                    });
                                });
                                grouped.sort((a, b) => order.indexOf(a.table_name) - order.indexOf(b.table_name));
                                this.groupedTables = grouped;
                                if (grouped.length) this.activeContentTab = grouped[0].table_name;

                                // Map tables to portfolio tabs
                                this.mapPortfolioTables(allTables);

                                // Simpan data untuk tombol Parse
                                this.pdfData = { ...this.pdfData, ...extracted };

                                let resultMsg = `${validRanges.length} partisi berhasil.`;
                                if (allTables.length) resultMsg += ` ${allTables.length} tabel ditemukan.`;
                                this.partitionSuccess = true;
                                this.partitionResult = resultMsg;
                            } else {
                                const msg = resp?.message || 'Gagal parse';
                                validRanges.forEach(r => {
                                    const range = this.pageRanges[r.idx];
                                    if (range) { range.success = false; range.message = msg; }
                                });
                                this.partitionSuccess = false;
                                this.partitionResult = msg;
                            }
                        } catch (e) {
                            validRanges.forEach(r => {
                                const range = this.pageRanges[r.idx];
                                if (range) { range.loading = false; range.success = false; range.message = 'Gagal: ' + e.message; }
                            });
                            this.partitionSuccess = false;
                            this.partitionResult = 'Gagal: ' + e.message;
                        }

                        this.partitionLoading = false;
                    },

                    resetPageRanges() {
                        this.pageRanges = [{
                            id: 1,
                            start_page: '',
                            end_page: '',
                            section_type: 'auto',
                            loading: false,
                            success: null,
                            message: '',
                            data: null
                        }];
                        this.partitionResult = '';
                        this.partitionSuccess = false;
                        this.partitionTables = [];
                        this.groupedTables = [];
                        this.activeContentTab = '';
                        this.activeTableTab = 0;
                        this.partitionSektor = [];
                        this.partitionEfek = [];
                        this.partitionObligasi = [];
                        this.partitionSukuk = [];
                        this.partitionBank = [];
                        this.selectedDocId = null;
                    },

                    selectDocumentForPartition(docId) {
                        this.selectedDocId = docId;
                        this.pageRanges.forEach(r => {
                            r.success = null;
                            r.message = '';
                            r.data = null;
                        });
                        this.partitionResult = '';
                        this.partitionSuccess = false;
                        this.partitionTables = [];
                        this.groupedTables = [];
                        this.activeContentTab = '';
                        this.activeTableTab = 0;
                        this.partitionSektor = [];
                        this.partitionEfek = [];
                        this.partitionObligasi = [];
                        this.partitionSukuk = [];
                        this.partitionBank = [];
                    },

                    mapPortfolioTables(tables) {
                        this.partitionEfek = [];
                        this.partitionObligasi = [];
                        this.partitionSektor = [];
                        this.partitionSukuk = [];
                        this.partitionBank = [];

                        const headerMaps = {
                            'Portofolio Efek': {
                                'Kode': 'kode_efek',
                                'Kode Efek': 'kode_efek',
                                'Ticker': 'kode_efek',
                                'Symbol': 'kode_efek',
                                'Kode Saham': 'kode_efek',
                                'ISIN': 'kode_efek',
                                'Nama Efek': 'nama_efek',
                                'Nama': 'nama_efek',
                                'Sektor': 'sektor',
                                'Bobot %': 'bobot',
                                'Bobot': 'bobot',
                                'Persentase': 'bobot',
                                '% thd Portofolio': 'bobot',
                                'Nilai Pasar': 'nilai_pasar',
                                'Nilai Wajar': 'nilai_pasar',
                                'Jumlah Harga Pasar': 'nilai_pasar',
                                '% thd NAB': 'persen_nab',
                                '% NAB': 'persen_nab',
                                'Return 1Y': 'return_1y',
                                'Jumlah Lembar Saham': 'jumlah_lembar',
                                'Jumlah Lembar': 'jumlah_lembar',
                                'Lembar Saham': 'jumlah_lembar',
                                'Harga Perolehan': 'harga_perolehan',
                                'Harga Perolehan Rata-rata': 'harga_perolehan',
                                'Average Cost': 'harga_perolehan',
                            },
                            'Obligasi': {
                                'Kode': 'kode_obligasi',
                                'Nama Obligasi': 'nama_obligasi',
                                'Nama': 'nama_obligasi',
                                'Bobot %': 'bobot',
                                'Nilai Pasar': 'nilai_pasar',
                                'YTM': 'ytm',
                                'Kupon': 'kupon',
                                'Jatuh Tempo': 'tanggal_jatuh_tempo',
                                'Penerbit': 'penerbit',
                                'Rating': 'rating',
                            },
                            'Sektor': {
                                'Sektor': 'nama_sektor',
                                'Bobot %': 'bobot',
                            },
                            'Sukuk': {
                                'Kode': 'kode_sukuk',
                                'Nama': 'nama_sukuk',
                                'Jenis': 'jenis_sukuk',
                                'Bobot %': 'bobot',
                                'Yield': 'yield',
                                'Jatuh Tempo': 'jatuh_tempo',
                                'Rating': 'rating',
                            },
                            'Bank': {
                                'Nama Bank': 'nama_bank',
                                'Jenis': 'jenis_bank',
                                'Bobot %': 'bobot',
                                'Nilai Pasar': 'nilai_pasar',
                                'Tingkat Bunga': 'tingkat_bunga',
                                'Bunga': 'tingkat_bunga',
                                'Jangka Waktu': 'jangka_waktu',
                            },
                        };

                        const targetMap = {
                            'Portofolio Efek': 'partitionEfek',
                            'Obligasi': 'partitionObligasi',
                            'Sektor': 'partitionSektor',
                            'Sukuk': 'partitionSukuk',
                            'Bank': 'partitionBank',
                        };

                        tables.forEach(table => {
                            const name = table.table_name || '';
                            const map = headerMaps[name];
                            const target = targetMap[name];
                            if (!map || !target) return;

                            const rows = (table.rows || []).map(row => {
                                const obj = {};
                                table.headers.forEach((h, i) => {
                                    const prop = map[h];
                                    if (prop) {
                                        let val = row[i];
                                        if (val === undefined || val === null) val = '';
                                        if (['bobot', 'persen_nab', 'return_1y', 'ytm', 'kupon', 'yield', 'tingkat_bunga', 'jumlah_lembar', 'harga_perolehan', 'nilai_pasar'].includes(prop)) {
                                            val = parseFloat(String(val).replace(/[^0-9.,-]/g, '').replace(',', '.')) || val;
                                        }
                                        obj[prop] = val;
                                    }
                                });
                                return obj;
                            });

                            this[target] = rows;
                        });
                    },

                    debouncedFetchDocs() {
                        clearTimeout(this._docFetchTimer);
                        this._docFetchTimer = setTimeout(() => {
                            this.fetchExistingDocuments();
                        }, 300);
                    },

                    fetchExistingDocuments() {
                        if (!this.existingDocsUrl) return;
                        const kode = (document.getElementById('kode_reksa_dana')?.value || '').trim();
                        if (!kode) {
                            this.existingDocsLoading = false;
                            this.existingDocsLoaded = true;
                            this.existingDocs = [];
                            this.selectedDocIds = [];
                            this._lastFetchedKey = '';
                            return;
                        }
                        // Build filter key based on jenis_laporan
                        let filterKey = '';
                        if (this.jenisLaporan === 'kalender_ffs') {
                            filterKey = `${kode}|${this.jenisLaporan}|${this.ffsBulan || ''}|${this.ffsTahun || ''}`;
                        } else {
                            filterKey = `${kode}|${this.jenisLaporan}|${this.tahunLaporan || ''}`;
                        }
                        // Skip if same filter key already fetched
                        if (filterKey === this._lastFetchedKey && this.existingDocsLoaded) {
                            return;
                        }
                        // Cancel previous request
                        if (this._docFetchAbortController) {
                            this._docFetchAbortController.abort();
                        }
                        this._docFetchAbortController = new AbortController();

                        this.existingDocsLoading = true;
                        this.existingDocsLoaded = false;
                        // Keep existing docs visible while loading new ones (prevents flicker)
                        this.selectedDocIds = [];

                        const params = new URLSearchParams();

                        params.append('kode_reksa_dana', kode);
                        params.append('jenis_laporan', this.jenisLaporan);
                        if (this.jenisLaporan === 'kalender_ffs') {
                            if (this.ffsBulan) params.append('ffs_bulan', this.ffsBulan);
                            if (this.ffsTahun) params.append('ffs_tahun', this.ffsTahun);
                        } else {
                            if (this.tahunLaporan) params.append('tahun_laporan', this.tahunLaporan);
                        }

                        fetch(`${this.existingDocsUrl}?${params.toString()}`, {
                                headers: {
                                    Accept: 'application/json'
                                },
                                signal: this._docFetchAbortController.signal
                            })
                            .then(res => {
                                if (!res.ok) {
                                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                                }
                                return res.json();
                            })
                            .then(resp => {
                                this.existingDocsLoading = false;
                                this.existingDocsLoaded = true;
                                if (Array.isArray(resp.documents)) {
                                    this.existingDocs = resp.documents;
                                } else {
                                    this.existingDocs = [];
                                }
                                this._lastFetchedKey = filterKey;
                            })
                            .catch(err => {
                                if (err.name === 'AbortError') return; // Ignore cancelled requests
                                this.existingDocsLoading = false;
                                this.existingDocsLoaded = true;
                                this.existingDocs = [];
                                this._lastFetchedKey = filterKey;
                                console.error('Failed to load documents:', err);
                            });
                    },

                    parseExistingDocument(documentId) {
                        if (!this.parseExistingDocUrl || !documentId) return;
                        this.existingDocParsing = true;
                        this.pdfResult = '';
                        this.pdfSuccess = false;

                        fetch(this.parseExistingDocUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.analisaFormEl().querySelector('input[name="_token"]').value,
                                },
                                body: JSON.stringify({
                                    document_id: documentId,
                                    parse_mode: 'ai'
                                }),
                            })
                            .then(res => {
                                if (!res.ok) {
                                    return res.json().then(err => {
                                        throw new Error(err.message || 'Gagal parsing dokumen');
                                    });
                                }
                                return res.json();
                            })
                            .then(resp => {
                                this.existingDocParsing = false;
                                if (!resp.success) {
                                    this.pdfSuccess = false;
                                    this.pdfResult = resp.message;
                                    return;
                                }
                                const extractedData = this.normalizeExtractedData(resp.data || {});
                                this.pdfData = extractedData;
                                this.pdfSuccess = true;
                                this.pdfResult = resp.message + ' (dari: ' + (resp.document_label || 'dokumen tersimpan') +
                                    ')';
                            })
                            .catch(err => {
                                this.existingDocParsing = false;
                                this.pdfSuccess = false;
                                this.pdfResult = 'Gagal: ' + err.message;
                            });
                    },

                    toggleDocSelection(docId) {
                        const idx = this.selectedDocIds.indexOf(docId);
                        if (idx === -1) {
                            this.selectedDocIds.push(docId);
                        } else {
                            this.selectedDocIds.splice(idx, 1);
                        }
                    },

                    parseSelectedDocuments() {
                        if (!this.parseExistingDocUrl || this.selectedDocIds.length === 0) return;
                        this.batchParsing = true;
                        this.batchParsedCount = 0;
                        this.pdfResult = '';
                        this.pdfSuccess = false;

                        const ids = [...this.selectedDocIds];
                        const parseNext = () => {
                            if (this.batchParsedCount >= ids.length) {
                                this.batchParsing = false;
                                this.selectedDocIds = [];
                                if (!this.pdfSuccess) {
                                    this.pdfResult = 'Semua dokumen selesai diproses.';
                                }
                                return;
                            }
                            const docId = ids[this.batchParsedCount];
                            fetch(this.parseExistingDocUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': this.analisaFormEl().querySelector('input[name="_token"]')
                                            .value,
                                    },
                                    body: JSON.stringify({
                                        document_id: docId,
                                        parse_mode: 'ai'
                                    }),
                                })
                                .then(res => {
                                    if (!res.ok) {
                                        return res.json().then(err => {
                                            throw new Error(err.message || 'Gagal parsing dokumen');
                                        });
                                    }
                                    return res.json();
                                })
                                .then(resp => {
                                    this.batchParsedCount++;
                                    if (!resp.success) {
                                        this.pdfResult = 'Dokumen ' + this.batchParsedCount + ' gagal: ' + resp.message;
                                        parseNext();
                                        return;
                                    }
                                    const extractedData = this.normalizeExtractedData(resp.data || {});
                                    this.pdfData = extractedData;
                                    this.pdfSuccess = true;
                                    this.pdfResult = 'Berhasil parse dokumen ' + this.batchParsedCount + '/' + ids
                                        .length + ' (dari: ' + (resp.document_label || 'dokumen tersimpan') + ')';
                                    parseNext();
                                })
                                .catch(err => {
                                    this.batchParsedCount++;
                                    this.pdfResult = 'Dokumen ' + this.batchParsedCount + ' gagal: ' + err.message;
                                    parseNext();
                                });
                        };
                        parseNext();
                    },

                    parseSingleDocument(docId) {
                        this.parseExistingDocument(docId);
                    },

                    parseToForms() {
                        this.importSummary = '';
                        const data = this.pdfData;
                        if (!data) { this.importSummary = 'Tidak ada data hasil ekstraksi.'; return; }
                        this.applyExtractedData(data);
                        const mapped = this.countMappedFields(data);
                        this.importSummary = '✓ ' + mapped.mapped + ' field berhasil dipetakan ke Input Manual & Input Lengkap.' +
                            (mapped.skipped > 0 ? ' (' + mapped.skipped + ' field tidak dikenal).' : '');
                    },

                    countMappedFields(data) {
                        if (!data) return { mapped: 0, skipped: 0 };
                        const scalarKeys = [
                            'nama_reksa_dana', 'jenis_reksa_dana', 'manajer_investasi', 'bank_kustodian',
                            'tanggal_peluncuran', 'mata_uang', 'benchmark', 'tujuan_investasi', 'kebijakan_investasi',
                            'kode_reksa_dana', 'total_aum', 'total_marcap_10_efek', 'tanggal_data',
                            'unit_penyertaan', 'nab_per_unit', 'return_1m', 'return_ytd', 'return_1y', 'total_return',
                            'biaya_operasi', 'portfolio_turnover_ratio', 'management_fee', 'custodian_fee',
                            'total_aset', 'total_liabilitas', 'nilai_aset_bersih', 'kas_dan_bank',
                            'piutang_bunga', 'piutang_dividen', 'piutang_lain', 'utang_pajak', 'utang_lain',
                            'pendapatan_bunga', 'pendapatan_dividen', 'gain_realized', 'gain_unrealized',
                            'beban_mi', 'beban_kustodian', 'beban_lain', 'laba_bersih', 'total_beban',
                            'laba_sebelum_pajak', 'beban_pajak_penghasilan', 'laba_bersih_tahun_berjalan',
                            'penghasilan_komprehensif_lain', 'penghasilan_komprehensif_lain_setelah_pajak',
                            'penghasilan_komprehensif_tahun_berjalan', 'arus_kas_operasi', 'arus_kas_pendanaan',
                            'kas_awal_tahun', 'kas_akhir_tahun', 'kas', 'portofolio_efek', 'instrumen_pasar_uang',
                            'piutang_transaksi_efek', 'piutang_bunga_dan_dividen', 'uang_muka_diterima',
                            'liabilitas_pembelian_kembali', 'beban_akrual', 'liabilitas_atas_biaya',
                            'pembelian_kembali_unit_penyertaan', 'utang_pajak_lainnya', 'pendapatan_investasi',
                            'pendapatan_lainnya', 'total_pendapatan', 'beban_investasi', 'beban_pengelolaan_investasi',
                            'pembelian_efek_ekuitas', 'penjualan_efek_ekuitas', 'penerimaan_bunga_deposito',
                            'penerimaan_bunga_jasa_giro', 'penerimaan_dividen_kas', 'pembayaran_jasa_pengelolaan',
                            'pembayaran_jasa_kustodian', 'pembayaran_beban_lain_arus', 'kas_bersih_aktivitas_operasi',
                            'penerimaan_penjualan_unit', 'pembayaran_pembelian_kembali_unit',
                            'kas_bersih_aktivitas_pendanaan', 'kenaikan_kas_setara_kas',
                            'total_hasil_investasi', 'hasil_investasi_setelah_biaya', 'persentase_pph',
                            'fair_value_level_1', 'fair_value_level_2', 'fair_value_level_3',
                            'unit_milik_investor', 'unit_milik_mi', 'total_unit_beredar',
                        ];
                        let mapped = 0, skipped = 0;
                        scalarKeys.forEach(k => {
                            if (data[k] !== null && data[k] !== undefined && data[k] !== '') mapped++;
                        });
                        ['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasi_aset'].forEach(k => {
                            if (Array.isArray(data[k]) && data[k].length > 0) mapped++;
                        });
                        Object.keys(data).forEach(k => {
                            if (!scalarKeys.includes(k) && !['sektor','efek','kinerja','obligasi','sukuk','bank','alokasi_aset','_raw_tables','data_tahunan','data_tambahan','tahun_tambahan'].includes(k)) {
                                const v = data[k];
                                if (v !== null && v !== undefined && v !== '' && !(Array.isArray(v) && v.length === 0)) skipped++;
                            }
                        });
                        return { mapped, skipped };
                    },

                    addTahun() {
                        const t = prompt('Masukkan tahun (contoh: 2025):');
                        if (t && t.match(/^\d{4}$/)) {
                            this.tahunTambahan.push(t);
                            if (!this.dataTambahan[t]) this.dataTambahan[t] = {};
                        }
                    },
                    removeTahun(i) {
                        const t = this.tahunTambahan[i];
                        if (t && this.dataTambahan[t]) delete this.dataTambahan[t];
                        this.tahunTambahan.splice(i, 1);
                    },
                    getTahunData(tahun, field) {
                        var d = this.dataTambahan[tahun];
                        return d ? (d[field] !== undefined && d[field] !== null && d[field] !== '' ? d[field] : '') : '';
                    },
                    setTahunData(tahun, field, val) {
                        if (!this.dataTambahan[tahun]) this.dataTambahan[tahun] = {};
                        this.dataTambahan[tahun][field] = val;
                    },
                    getTotalPiutang() {
                        return (Number(this.piutangBunga) || 0) + (Number(this.piutangDividen) || 0) + (Number(this
                            .piutangLain) || 0);
                    },
                    getTotalPiutangTahun(tahun) {
                        const d = this.dataTambahan[tahun] || {};
                        return (Number(d.piutangBunga) || 0) + (Number(d.piutangDividen) || 0) + (Number(d.piutangLain) || 0);
                    },
                    getTotalPortofolioEfek() {
                        return (Number(this.portofolioEfek) || 0) + (Number(this.instrumenPasarUang) || 0);
                    },
                    getTotalPortofolioEfekTahun(tahun) {
                        const d = this.dataTambahan[tahun] || {};
                        return (Number(d.portofolioEfek) || 0) + (Number(d.instrumenPasarUang) || 0);
                    },
                    getEkuitas() {
                        return (Number(this.totalAset) || 0) - (Number(this.totalLiabilitas) || 0);
                    },
                    getEkuitasTahun(tahun) {
                        const d = this.dataTambahan[tahun] || {};
                        return (Number(d.totalAset) || 0) - (Number(d.totalLiabilitas) || 0);
                    },
                    getTotalPendapatan() {
                        return (Number(this.pendapatanBunga) || 0) + (Number(this.pendapatanDividen) || 0);
                    },
                    getTotalPendapatanTahun(tahun) {
                        const d = this.dataTambahan[tahun] || {};
                        return (Number(d.pendapatanBunga) || 0) + (Number(d.pendapatanDividen) || 0);
                    },
                    getTotalKeuntunganInvestasi() {
                        return (Number(this.gainRealized) || 0) + (Number(this.gainUnrealized) || 0);
                    },
                    getTotalKeuntunganInvestasiTahun(tahun) {
                        const d = this.dataTambahan[tahun] || {};
                        return (Number(d.gainRealized) || 0) + (Number(d.gainUnrealized) || 0);
                    },
                    getTotalBeban() {
                        return (Number(this.bebanMi) || 0) + (Number(this.bebanKustodian) || 0) + (Number(this.bebanLain) || 0);
                    },
                    getTotalBebanTahun(tahun) {
                        const d = this.dataTambahan[tahun] || {};
                        return (Number(d.bebanMi) || 0) + (Number(d.bebanKustodian) || 0) + (Number(d.bebanLain) || 0);
                    },
                    getLabaBersihPerhitungan() {
                        return this.getTotalPendapatan() + this.getTotalKeuntunganInvestasi() - this.getTotalBeban();
                    },
                    getLabaBersihPerhitunganTahun(tahun) {
                        return this.getTotalPendapatanTahun(tahun) + this.getTotalKeuntunganInvestasiTahun(tahun) - this
                            .getTotalBebanTahun(tahun);
                    },
                    getTotalArusKas() {
                        return (Number(this.arusKasOperasi) || 0) + (Number(this.arusKasPendanaan) || 0);
                    },
                    getTotalArusKasTahun(tahun) {
                        const d = this.dataTambahan[tahun] || {};
                        return (Number(d.arusKasOperasi) || 0) + (Number(d.arusKasPendanaan) || 0);
                    },
                    getKasAkhirPerhitungan() {
                        return (Number(this.kasAwalTahun) || 0) + this.getTotalArusKas();
                    },
                    getKasAkhirPerhitunganTahun(tahun) {
                        const d = this.dataTambahan[tahun] || {};
                        return (Number(d.kasAwalTahun) || 0) + this.getTotalArusKasTahun(tahun);
                    },
                    getPenghasilanKomprehensifTahunBerjalan() {
                        return (Number(this.labaBersihTahunBerjalan) || 0) + (Number(this
                            .penghasilanKomprehensifLainSetelahPajak) || 0);
                    },
                    getPenghasilanKomprehensifTahunBerjalanTahun(tahun) {
                        const d = this.dataTambahan[tahun] || {};
                        return (Number(d.labaBersihTahunBerjalan) || 0) + (Number(d.penghasilanKomprehensifLainSetelahPajak) ||
                            0);
                    },
                    formatNumber(val) {
                        if (val === null || val === undefined || val === '' || isNaN(Number(val))) return '-';
                        return Number(val).toLocaleString('id-ID');
                    },
                    downloadCsv(headers, rows, filename) {
                        if (!rows || !rows.length) return;
                        const esc = v => `"${String(v ?? '').replace(/"/g, '""')}"`;
                        const csv = [
                            headers.map(esc).join(','),
                            ...rows.map(r => r.map(c => esc(c)).join(','))
                        ].join('\n');
                        const bom = '\uFEFF';
                        const blob = new Blob([bom + csv], { type: 'text/csv;charset=utf-8;' });
                        const a = document.createElement('a');
                        a.href = URL.createObjectURL(blob);
                        a.download = filename.replace(/[^a-zA-Z0-9\-\_\. ]/g, '_') + '.csv';
                        a.click();
                        URL.revokeObjectURL(a.href);
                    },
                    downloadCsvFromArray(arr, filename) {
                        if (!arr || !arr.length) return;
                        const headers = Object.keys(arr[0]);
                        const rows = arr.map(obj => headers.map(h => obj[h]));
                        this.downloadCsv(headers, rows, filename);
                    },
                    loadTestJson(event) {
                        const file = event.target.files?.[0];
                        if (!file) return;
                        this.testJsonError = '';
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            try {
                                const data = JSON.parse(e.target.result);
                                if (typeof data !== 'object' || data === null) {
                                    this.testJsonError = 'JSON harus berupa object.';
                                    return;
                                }
                                this.generateFromTestJson(data);
                                this.$nextTick(() => this.downloadTestJson());
                            } catch (err) {
                                this.testJsonError = 'JSON tidak valid: ' + err.message;
                            }
                        };
                        reader.readAsText(file);
                        event.target.value = '';
                    },
                    generateFromTestJson(data) {
                        this.testJsonError = '';

                        const allTables = [];
                        const rawTablesData = data._raw_tables || [];
                        rawTablesData.forEach(p => {
                            if (Array.isArray(p.tables)) {
                                p.tables.forEach(t => allTables.push(t));
                            }
                        });

                        // Group tables by table_name, merge identical headers, dedup rows by label
                        const groups = {};
                        allTables.forEach(t => {
                            const name = t.table_name || 'Lainnya';
                            const hKey = JSON.stringify(t.headers || []);
                            if (!groups[name]) groups[name] = {};
                            if (!groups[name][hKey]) {
                                groups[name][hKey] = { headers: t.headers || [], rows: [] };
                            }
                            const seen = new Set(groups[name][hKey].rows.map(r => String(r[0] || '')));
                            (t.rows || []).forEach(row => {
                                const label = String(row[0] || '');
                                if (!seen.has(label)) { groups[name][hKey].rows.push(row); seen.add(label); }
                            });
                        });
                        const order = ['Aset','Liabilitas','Pendapatan','Beban','Arus Kas Operasi','Arus Kas Pendanaan','Pengukuran Nilai Wajar','Informasi Lainnya','Portofolio Efek','Obligasi','Sektor','Sukuk','Bank'];
                        const grouped = [];
                        Object.entries(groups).forEach(([name, byHeaders]) => {
                            Object.values(byHeaders).forEach(({headers, rows}) => {
                                grouped.push({ table_name: name, tables: [{ headers, rows }] });
                            });
                        });
                        grouped.sort((a, b) => order.indexOf(a.table_name) - order.indexOf(b.table_name));
                        this.groupedTables = grouped;
                        if (grouped.length) this.activeContentTab = grouped[0].table_name;

                        // Map portfolio tables
                        this.mapPortfolioTables(allTables);

                        // Set partition state
                        this.partitionSuccess = true;
                        this.partitionTables = allTables;
                        this.activeTableTab = 0;
                        this.partitionResult = allTables.length ? `${allTables.length} tabel berhasil di-generate dari JSON.` : 'JSON berhasil di-load (tidak ada tabel).';

                        // Normalize, set pdfData + component state for form
                        const extracted = this.normalizeExtractedData(data);
                        this.pdfData = { ...this.pdfData, ...extracted };
                        if (extracted.tahun_tambahan?.length) this.tahunTambahan = extracted.tahun_tambahan;
                        if (extracted.data_tambahan) this.dataTambahan = { ...this.dataTambahan, ...extracted.data_tambahan };
                        this.pdfSuccess = true;
                        this.mode = 'pdf';
                    },
                    downloadTestJson() {
                        try {
                            if (!this.pdfData) {
                                this.testJsonError = 'Tidak ada data untuk di-download.';
                                return;
                            }
                            const json = JSON.stringify(this.pdfData, null, 2);
                            const blob = new Blob([json], { type: 'application/json' });
                            const a = document.createElement('a');
                            a.href = URL.createObjectURL(blob);
                            a.download = 'test-ekstraksi.json';
                            a.click();
                            URL.revokeObjectURL(a.href);
                        } catch (e) {
                            this.testJsonError = 'Gagal download: ' + e.message;
                        }
                    },

                    async lookupReksaDana(kode) {
                        if (!kode || !this.lookupKodeUrl) return;
                        const k = String(kode).trim().toUpperCase();
                        if (k.length < 3) {
                            this.lookupMessage = 'Kode minimal 3 karakter';
                            this.lookupOk = false;
                            return;
                        }
                        try {
                            const resp = await fetch(`${this.lookupKodeUrl}?kode_reksa_dana=${encodeURIComponent(k)}`, {
                                headers: { Accept: 'application/json' }
                            });
                            const data = await resp.json();
                            if (data?.master) {
                                const m = data.master;
                                this.setFieldValue('kode_reksa_dana', m.kode_reksa_dana);
                                this.setFieldValue('nama_reksa_dana', m.nama_reksa_dana);
                                this.setFieldValue('jenis_reksa_dana', m.jenis_reksa_dana);
                                this.setFieldValue('benchmark', m.benchmark);
                                this.setFieldValue('tujuan_investasi', m.tujuan_investasi);
                                this.setFieldValue('kebijakan_investasi', m.kebijakan_investasi);
                                this.setFieldValue('manajer_investasi', m.manajer_investasi);
                                this.setFieldValue('bank_kustodian', m.bank_kustodian);
                                this.setFieldValue('tanggal_peluncuran', m.tanggal_peluncuran);
                                this.setFieldValue('mata_uang', m.mata_uang);
                                this.setFieldValue('management_fee', m.management_fee);
                                this.setFieldValue('custodian_fee', m.custodian_fee);
                                this.setFieldValue('total_aum', m.total_aum);
                                this.totalAum = m.total_aum ?? this.totalAum;
                                this.setFieldValue('nab_per_unit', m.nab_per_unit);
                                this.nabPerUnit = m.nab_per_unit ?? this.nabPerUnit;
                                this.setFieldValue('return_1m', m.return_1m);
                                this.return1m = m.return_1m ?? this.return1m;
                                this.setFieldValue('return_ytd', m.return_ytd);
                                this.returnYtd = m.return_ytd ?? this.returnYtd;
                                this.setFieldValue('return_1y', m.return_1y);
                                this.return1y = m.return_1y ?? this.return1y;
                                this.setFieldValue('expense_ratio', m.expense_ratio);
                                if (m.kategori && Array.isArray(m.kategori)) {
                                    this.applyKategori(m.kategori);
                                }
                                this.autoCalcUnitPenyertaan();
                                this.lookupMessage = 'Data reksa dana ditemukan';
                                this.lookupOk = true;
                            } else {
                                this.lookupMessage = data?.last_analisa ? 'Data dari analisa terakhir' : 'Kode reksa dana tidak ditemukan';
                                this.lookupOk = data?.last_analisa ? true : false;
                            }
                            this.debouncedFetchDocs();
                        } catch (e) {
                            this.lookupMessage = 'Gagal lookup: ' + e.message;
                            this.lookupOk = false;
                        }
                    },

                    applyLookupData(data) {
                        if (!data) return;
                        const fields = {
                            kode_reksa_dana: 'kode_reksa_dana',
                            nama_reksa_dana: 'nama_reksa_dana',
                            jenis_reksa_dana: 'jenis_reksa_dana',
                            benchmark: 'benchmark',
                            tujuan_investasi: 'tujuan_investasi',
                            kebijakan_investasi: 'kebijakan_investasi',
                            manajer_investasi: 'manajer_investasi',
                            bank_kustodian: 'bank_kustodian',
                            tanggal_peluncuran: 'tanggal_peluncuran',
                            mata_uang: 'mata_uang',
                            management_fee: 'management_fee',
                            custodian_fee: 'custodian_fee',
                            total_aum: 'total_aum',
                            unit_penyertaan: 'unit_penyertaan',
                            nab_per_unit: 'nab_per_unit',
                            return_ytd: 'return_ytd',
                            return_1y: 'return_1y',
                            total_return: 'total_return',
                            biaya_operasi: 'biaya_operasi',
                            portfolio_turnover_ratio: 'portfolio_turnover_ratio',
                            expense_ratio: 'expense_ratio',
                            total_marcap_10_efek: 'total_marcap_10_efek',
                            tanggal_data: 'tanggal_data',
                            ffs_bulan: 'ffs_bulan',
                            ffs_tahun: 'ffs_tahun',
                            jenis_laporan: 'jenis_laporan',
                            periode_awal: 'periode_awal',
                            periode_akhir: 'periode_akhir',
                            tahun_laporan: 'tahun_laporan',
                            mode: 'mode',
                        };
                        for (const [key, id] of Object.entries(fields)) {
                            this.setFieldValue(id, data[key]);
                        }
                        if (data.kategori && Array.isArray(data.kategori)) {
                            this.applyKategori(data.kategori);
                        }
                        if (data.sektor?.length) this.sektor = data.sektor;
                        if (data.efek?.length) this.efek = data.efek;
                        if (data.kinerja?.length) this.kinerja = data.kinerja;
                        if (data.obligasi?.length) this.obligasi = data.obligasi;
                        if (data.sukuk?.length) this.sukuk = data.sukuk;
                        if (data.bank?.length) this.bank = data.bank;
                        if (data.alokasi_aset?.length) this.alokasi_aset = data.alokasi_aset;
                        this.hitungSektorFromEfek();
                        this.syncLikuiditasFromEfek();
                        this.syncKeuanganFromData();
                        this.debouncedFetchDocs();
                    },
                };
            }
        </script>
    @endpush
@endsection
