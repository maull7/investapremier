@extends($formRoutes['layout'] ?? 'layouts.user')

@section('content')
<div class="max-w-5xl" x-data="editForm()">
    <div class="mb-6">
        <h1 class="page-title">Edit Analisa Reksa Dana</h1>
        <p class="page-sub">Perbarui informasi dan data manual analisa</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ $formRoutes['update'] ?? route('user.analisa.update', $analisa) }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- Info Dasar --}}
        @if(($formRoutes['layout'] ?? '') !== 'layouts.admin')
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h3 class="font-semibold text-primary">Informasi Reksa Dana</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="kode_reksa_dana" value="Kode Reksa Dana" />
                    <x-text-input id="kode_reksa_dana" name="kode_reksa_dana" type="text" class="mt-1 block w-full"
                        value="{{ old('kode_reksa_dana', $analisa->kode_reksa_dana) }}" />
                    <x-input-error :messages="$errors->get('kode_reksa_dana')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="nama_reksa_dana" value="Nama Reksa Dana *" />
                    <x-text-input id="nama_reksa_dana" name="nama_reksa_dana" type="text" class="mt-1 block w-full"
                        value="{{ old('nama_reksa_dana', $analisa->nama_reksa_dana) }}" required />
                    <x-input-error :messages="$errors->get('nama_reksa_dana')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="jenis_reksa_dana" value="Jenis Reksa Dana *" />
                    <select id="jenis_reksa_dana" name="jenis_reksa_dana" required
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        <option value="">Pilih Jenis</option>
                        @foreach(['Saham','Pendapatan Tetap','Campuran','Pasar Uang','Terproteksi','Global','DIRE-DINFRA','Penyertaan terbatas'] as $j)
                            <option value="{{ $j }}" {{ old('jenis_reksa_dana', $analisa->jenis_reksa_dana) === $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('jenis_reksa_dana')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Kategori" />
                    <div class="mt-1 flex flex-wrap gap-3">
                        @foreach (['Konvensional', 'Syariah', 'index', 'ETF'] as $k)
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                <input type="checkbox" name="kategori[]" value="{{ $k }}"
                                    {{ in_array($k, old('kategori', $analisa->kategori ?? [])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-primary focus:ring-primary/20">
                                {{ $k }}
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('kategori')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="total_aum" value="Total AUM (Rp)" />
                    <x-text-input id="total_aum" name="total_aum" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('total_aum', $analisa->total_aum) }}" x-model="totalAum"
                        @input="hitungNilaiPasarSemua()" />
                    <x-input-error :messages="$errors->get('total_aum')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="total_marcap_10_efek" value="Total MarCap 10 Saham Terbesar (Rp)" />
                    <x-text-input id="total_marcap_10_efek" name="total_marcap_10_efek" type="number" step="0.01" class="mt-1 block w-full bg-gray-50"
                        value="{{ old('total_marcap_10_efek', $analisa->total_marcap_10_efek) }}" x-model="totalMarcap10Efek" readonly />
                    <x-input-error :messages="$errors->get('total_marcap_10_efek')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="tanggal_data" value="Tanggal Data *" />
                    <x-text-input id="tanggal_data" name="tanggal_data" type="date" class="mt-1 block w-full"
                        value="{{ old('tanggal_data', $analisa->tanggal_data?->format('Y-m-d')) }}" required x-model="tanggalData" />
                    <x-input-error :messages="$errors->get('tanggal_data')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="ffs_bulan" value="Bulan FFS *" />
                    <select id="ffs_bulan" name="ffs_bulan" required
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        <option value="">Pilih Bulan</option>
                        @foreach(range(1, 12) as $bulan)
                            <option value="{{ $bulan }}" {{ (int) old('ffs_bulan', $analisa->ffs_bulan) === $bulan ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('ffs_bulan')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="ffs_tahun" value="Tahun FFS *" />
                    <x-text-input id="ffs_tahun" name="ffs_tahun" type="number" min="2000" max="2100" class="mt-1 block w-full"
                        value="{{ old('ffs_tahun', $analisa->ffs_tahun) }}" required />
                    <x-input-error :messages="$errors->get('ffs_tahun')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="unit_penyertaan" value="Jumlah Unit Penyertaan" />
                    <x-text-input id="unit_penyertaan" name="unit_penyertaan" type="number" step="0.0001" class="mt-1 block w-full"
                        value="{{ old('unit_penyertaan', $analisa->unit_penyertaan) }}" />
                    <x-input-error :messages="$errors->get('unit_penyertaan')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="nab_per_unit" value="NAB/UP" />
                    <x-text-input id="nab_per_unit" name="nab_per_unit" type="number" step="0.000001" class="mt-1 block w-full"
                        value="{{ old('nab_per_unit', $analisa->nab_per_unit) }}" />
                    <x-input-error :messages="$errors->get('nab_per_unit')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="benchmark" value="Benchmark" />
                    <x-text-input id="benchmark" name="benchmark" type="text" class="mt-1 block w-full"
                        value="{{ old('benchmark', $analisa->benchmark) }}" />
                    <x-input-error :messages="$errors->get('benchmark')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="tujuan_investasi" value="Tujuan Investasi" />
                    <x-text-input id="tujuan_investasi" name="tujuan_investasi" type="text" class="mt-1 block w-full"
                        value="{{ old('tujuan_investasi', $analisa->tujuan_investasi) }}" />
                    <x-input-error :messages="$errors->get('tujuan_investasi')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="kebijakan_investasi" value="Kebijakan Investasi" />
                    <x-text-input id="kebijakan_investasi" name="kebijakan_investasi" type="text" class="mt-1 block w-full"
                        value="{{ old('kebijakan_investasi', $analisa->kebijakan_investasi) }}" />
                    <x-input-error :messages="$errors->get('kebijakan_investasi')" class="mt-1" />
                </div>
            </div>
        </div>
        @endif

        {{-- Informasi Umum --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h4 class="font-semibold text-primary text-sm">Informasi Umum</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="manajer_investasi" value="Manajer Investasi" />
                    <x-text-input id="manajer_investasi" name="manajer_investasi" type="text" class="mt-1 block w-full"
                        value="{{ old('manajer_investasi', $analisa->manajer_investasi) }}" />
                </div>
                <div>
                    <x-input-label for="bank_kustodian" value="Bank Kustodian" />
                    <x-text-input id="bank_kustodian" name="bank_kustodian" type="text" class="mt-1 block w-full"
                        value="{{ old('bank_kustodian', $analisa->bank_kustodian) }}" />
                </div>
                <div>
                    <x-input-label for="tanggal_peluncuran" value="Tanggal Peluncuran" />
                    <x-text-input id="tanggal_peluncuran" name="tanggal_peluncuran" type="date" class="mt-1 block w-full"
                        value="{{ old('tanggal_peluncuran', $analisa->tanggal_peluncuran?->format('Y-m-d')) }}" />
                </div>
            </div>
        </div>

        {{-- Kinerja --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h4 class="font-semibold text-primary text-sm">Kinerja</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="return_ytd" value="Return YTD (%)" />
                    <x-text-input id="return_ytd" name="return_ytd" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('return_ytd', $analisa->return_ytd) }}" />
                </div>
                <div>
                    <x-input-label for="return_1y" value="Return 1 Tahun (%)" />
                    <x-text-input id="return_1y" name="return_1y" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('return_1y', $analisa->return_1y) }}" />
                </div>
            </div>
        </div>

        {{-- Rasio Keuangan --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h4 class="font-semibold text-primary text-sm">Rasio Keuangan</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <x-input-label for="total_return" value="Total Return (%)" />
                    <x-text-input id="total_return" name="total_return" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('total_return', $analisa->total_return) }}" />
                </div>
                <div>
                    <x-input-label for="biaya_operasi" value="Biaya Operasi (%)" />
                    <x-text-input id="biaya_operasi" name="biaya_operasi" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('biaya_operasi', $analisa->biaya_operasi) }}" />
                </div>
                <div>
                    <x-input-label for="portfolio_turnover_ratio" value="Portfolio Turnover Ratio" />
                    <x-text-input id="portfolio_turnover_ratio" name="portfolio_turnover_ratio" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('portfolio_turnover_ratio', $analisa->portfolio_turnover_ratio) }}" />
                </div>
            </div>
        </div>

        {{-- Biaya --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h4 class="font-semibold text-primary text-sm">Biaya</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="management_fee" value="Management Fee (%)" />
                    <x-text-input id="management_fee" name="management_fee" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('management_fee', $analisa->management_fee) }}" />
                </div>
                <div>
                    <x-input-label for="custodian_fee" value="Custodian Fee (%)" />
                    <x-text-input id="custodian_fee" name="custodian_fee" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('custodian_fee', $analisa->custodian_fee) }}" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            @include('analisa.partials.form-alokasi-aset')
        </div>

        {{-- Laporan Keuangan - Neraca --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h4 class="font-semibold text-primary text-sm">Laporan Keuangan — Neraca</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <x-input-label for="total_aset" value="Total Aset (Rp)" />
                    <x-text-input id="total_aset" name="total_aset" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('total_aset', $analisa->total_aset) }}" />
                </div>
                <div>
                    <x-input-label for="total_liabilitas" value="Total Liabilitas (Rp)" />
                    <x-text-input id="total_liabilitas" name="total_liabilitas" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('total_liabilitas', $analisa->total_liabilitas) }}" />
                </div>
                <div>
                    <x-input-label for="kas_dan_bank" value="Kas dan Bank (Rp)" />
                    <x-text-input id="kas_dan_bank" name="kas_dan_bank" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('kas_dan_bank', $analisa->kas_dan_bank) }}" />
                </div>
                <div>
                    <x-input-label for="piutang_bunga" value="Piutang Bunga (Rp)" />
                    <x-text-input id="piutang_bunga" name="piutang_bunga" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('piutang_bunga', $analisa->piutang_bunga) }}" />
                </div>
                <div>
                    <x-input-label for="piutang_dividen" value="Piutang Dividen (Rp)" />
                    <x-text-input id="piutang_dividen" name="piutang_dividen" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('piutang_dividen', $analisa->piutang_dividen) }}" />
                </div>
                <div>
                    <x-input-label for="piutang_lain" value="Piutang Lain-lain (Rp)" />
                    <x-text-input id="piutang_lain" name="piutang_lain" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('piutang_lain', $analisa->piutang_lain) }}" />
                </div>
                <div>
                    <x-input-label for="utang_pajak" value="Utang Pajak (Rp)" />
                    <x-text-input id="utang_pajak" name="utang_pajak" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('utang_pajak', $analisa->utang_pajak) }}" />
                </div>
                <div>
                    <x-input-label for="utang_lain" value="Utang Lain-lain (Rp)" />
                    <x-text-input id="utang_lain" name="utang_lain" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('utang_lain', $analisa->utang_lain) }}" />
                </div>
            </div>
        </div>

        {{-- Laporan Keuangan - Laba Rugi --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h4 class="font-semibold text-primary text-sm">Laporan Keuangan — Laba Rugi</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <x-input-label for="pendapatan_bunga" value="Pendapatan Bunga (Rp)" />
                    <x-text-input id="pendapatan_bunga" name="pendapatan_bunga" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('pendapatan_bunga', $analisa->pendapatan_bunga) }}" />
                </div>
                <div>
                    <x-input-label for="pendapatan_dividen" value="Pendapatan Dividen (Rp)" />
                    <x-text-input id="pendapatan_dividen" name="pendapatan_dividen" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('pendapatan_dividen', $analisa->pendapatan_dividen) }}" />
                </div>
                <div>
                    <x-input-label for="gain_realized" value="Gain Realized (Rp)" />
                    <x-text-input id="gain_realized" name="gain_realized" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('gain_realized', $analisa->gain_realized) }}" />
                </div>
                <div>
                    <x-input-label for="gain_unrealized" value="Gain Unrealized (Rp)" />
                    <x-text-input id="gain_unrealized" name="gain_unrealized" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('gain_unrealized', $analisa->gain_unrealized) }}" />
                </div>
                <div>
                    <x-input-label for="beban_mi" value="Beban Manajer Investasi (Rp)" />
                    <x-text-input id="beban_mi" name="beban_mi" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('beban_mi', $analisa->beban_mi) }}" />
                </div>
                <div>
                    <x-input-label for="beban_kustodian" value="Beban Kustodian (Rp)" />
                    <x-text-input id="beban_kustodian" name="beban_kustodian" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('beban_kustodian', $analisa->beban_kustodian) }}" />
                </div>
                <div>
                    <x-input-label for="beban_lain" value="Beban Lain-lain (Rp)" />
                    <x-text-input id="beban_lain" name="beban_lain" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('beban_lain', $analisa->beban_lain) }}" />
                </div>
                <div>
                    <x-input-label for="laba_bersih" value="Laba Bersih (Rp)" />
                    <x-text-input id="laba_bersih" name="laba_bersih" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('laba_bersih', $analisa->laba_bersih) }}" />
                </div>
            </div>
        </div>

        {{-- Laporan Keuangan - Arus Kas --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h4 class="font-semibold text-primary text-sm">Laporan Keuangan — Arus Kas</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <x-input-label for="arus_kas_operasi" value="Arus Kas Operasi (Rp)" />
                    <x-text-input id="arus_kas_operasi" name="arus_kas_operasi" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('arus_kas_operasi', $analisa->arus_kas_operasi) }}" />
                </div>
                <div>
                    <x-input-label for="arus_kas_pendanaan" value="Arus Kas Pendanaan (Rp)" />
                    <x-text-input id="arus_kas_pendanaan" name="arus_kas_pendanaan" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('arus_kas_pendanaan', $analisa->arus_kas_pendanaan) }}" />
                </div>
                <div>
                    <x-input-label for="kas_awal_tahun" value="Kas Awal Tahun (Rp)" />
                    <x-text-input id="kas_awal_tahun" name="kas_awal_tahun" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('kas_awal_tahun', $analisa->kas_awal_tahun) }}" />
                </div>
                <div>
                    <x-input-label for="kas_akhir_tahun" value="Kas Akhir Tahun (Rp)" />
                    <x-text-input id="kas_akhir_tahun" name="kas_akhir_tahun" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('kas_akhir_tahun', $analisa->kas_akhir_tahun) }}" />
                </div>
            </div>
        </div>

        {{-- Rasio Keuangan Lengkap --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h4 class="font-semibold text-primary text-sm">Rasio Keuangan</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <x-input-label for="total_hasil_investasi" value="Total Hasil Investasi (%)" />
                    <x-text-input id="total_hasil_investasi" name="total_hasil_investasi" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('total_hasil_investasi', $analisa->total_hasil_investasi) }}" />
                </div>
                <div>
                    <x-input-label for="hasil_investasi_setelah_biaya" value="Hasil Investasi Setelah Biaya Pemasaran (%)" />
                    <x-text-input id="hasil_investasi_setelah_biaya" name="hasil_investasi_setelah_biaya" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('hasil_investasi_setelah_biaya', $analisa->hasil_investasi_setelah_biaya) }}" />
                </div>
                <div>
                    <x-input-label for="persentase_pph" value="Persentase Penghasilan Kena Pajak (%)" />
                    <x-text-input id="persentase_pph" name="persentase_pph" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('persentase_pph', $analisa->persentase_pph) }}" />
                </div>
            </div>
        </div>

        {{-- Fair Value --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h4 class="font-semibold text-primary text-sm">Fair Value / Pengukuran Nilai Wajar</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <x-input-label for="fair_value_level_1" value="Level 1 (Rp)" />
                    <x-text-input id="fair_value_level_1" name="fair_value_level_1" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('fair_value_level_1', $analisa->fair_value_level_1) }}" />
                </div>
                <div>
                    <x-input-label for="fair_value_level_2" value="Level 2 (Rp)" />
                    <x-text-input id="fair_value_level_2" name="fair_value_level_2" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('fair_value_level_2', $analisa->fair_value_level_2) }}" />
                </div>
                <div>
                    <x-input-label for="fair_value_level_3" value="Level 3 (Rp)" />
                    <x-text-input id="fair_value_level_3" name="fair_value_level_3" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('fair_value_level_3', $analisa->fair_value_level_3) }}" />
                </div>
            </div>
        </div>

        {{-- Unit Penyertaan --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h4 class="font-semibold text-primary text-sm">Unit Penyertaan</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <x-input-label for="unit_milik_investor" value="Unit Milik Investor" />
                    <x-text-input id="unit_milik_investor" name="unit_milik_investor" type="number" step="0.0001" class="mt-1 block w-full"
                        value="{{ old('unit_milik_investor', $analisa->unit_milik_investor) }}" />
                </div>
                <div>
                    <x-input-label for="unit_milik_mi" value="Unit Milik Manajer Investasi" />
                    <x-text-input id="unit_milik_mi" name="unit_milik_mi" type="number" step="0.0001" class="mt-1 block w-full"
                        value="{{ old('unit_milik_mi', $analisa->unit_milik_mi) }}" />
                </div>
                <div>
                    <x-input-label for="total_unit_beredar" value="Total Unit Beredar" />
                    <x-text-input id="total_unit_beredar" name="total_unit_beredar" type="number" step="0.0001" class="mt-1 block w-full"
                        value="{{ old('total_unit_beredar', $analisa->total_unit_beredar) }}" />
                </div>
            </div>
        </div>

        {{-- Sektor --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-primary text-sm">Komposisi Sektor</h3>
                <button type="button" @click="addRow('sektor')" class="text-xs text-primary hover:underline">+ Tambah Baris</button>
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
                        <button type="button" @click="removeRow('sektor', i)" class="text-red-400 hover:text-red-600 px-1">✕</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Efek --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-primary text-sm">Daftar Efek</h3>
                <button type="button" @click="addRow('efek')" class="text-xs text-primary hover:underline">+ Tambah Baris</button>
            </div>
            <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#f8fafc]">
                            <tr>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Efek</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Sektor</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nilai Pasar</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Harga Perolehan</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">% thd NAB</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kontribusi %</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 1M</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 3M</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 6M</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 1 Thn</th>
                                <th class="text-center px-2 py-2 text-xs font-semibold text-muted">Top 10</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            <template x-for="(row, i) in efek" :key="i">
                                <tr>
                                    <td class="px-1 py-1"><input type="text" :name="`efek[${i}][kode_efek]`" x-model="row.kode_efek" placeholder="BBCA" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" @change.debounce.500ms="lookupEfekData(i)" /></td>
                                    <td class="px-1 py-1"><input type="text" :name="`efek[${i}][nama_efek]`" x-model="row.nama_efek" placeholder="Nama Efek" class="w-32 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1">
                                        <input type="hidden" :name="`efek[${i}][effect_type]`" x-model="row.effect_type" />
                                        <input type="text" :name="`efek[${i}][sektor]`" x-model="row.sektor" placeholder="Sektor" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                    </td>
                                    <td class="px-1 py-1"><input type="number" :name="`efek[${i}][bobot]`" x-model="row.bobot" step="0.01" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" @input="hitungNilaiPasarEfek(i)" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`efek[${i}][nilai_pasar]`" x-model="row.nilai_pasar" step="0.01" readonly class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`efek[${i}][harga_perolehan]`" x-model="row.harga_perolehan" step="0.01" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`efek[${i}][persen_nab]`" x-model="row.persen_nab" step="0.01" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`efek[${i}][kontribusi_kinerja]`" x-model="row.kontribusi_kinerja" step="0.0001" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" @change="hitungTotalMarcap10" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_1m]`" x-model="row.return_1m" step="0.0001" readonly class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_3m]`" x-model="row.return_3m" step="0.0001" readonly class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_6m]`" x-model="row.return_6m" step="0.0001" readonly class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_1y]`" x-model="row.return_1y" step="0.0001" readonly class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1 text-center"><input type="checkbox" :name="`efek[${i}][top_10]`" x-model="row.top_10" value="1" class="rounded border-gray-300 text-primary focus:ring-primary" /></td>
                                    <td class="px-1 py-1"><button type="button" @click="removeRow('efek', i)" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
            </div>
        </div>

        {{-- Kinerja Bulanan --}}
        {{-- HIDE: section tidak ditampilkan di form edit, data lama tetap dipertahankan --}}
        <div style="display:none">
            <template x-for="(row, i) in kinerja" :key="i">
                <div class="flex gap-2 items-center">
                    <input type="month" :name="`kinerja[${i}][periode]`" x-model="row.periode" class="border-gray-300 rounded-lg text-sm px-3 py-2" />
                    <input type="number" :name="`kinerja[${i}][return_pct]`" x-model="row.return_pct" placeholder="Return %" step="0.0001" class="w-36 border-gray-300 rounded-lg text-sm px-3 py-2" />
                </div>
            </template>
        </div>

        {{-- Obligasi --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-primary text-sm">Obligasi</h3>
                <button type="button" @click="addRow('obligasi')" class="text-xs text-primary hover:underline">+ Tambah Baris</button>
            </div>
            <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#f8fafc]">
                            <tr>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Obligasi</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nilai Pasar</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">YTM (%)</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kupon (%)</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Jatuh Tempo</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Penerbit</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">% thd NAB</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 1M</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 3M</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 6M</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 1 Thn</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Durasi (thn)</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Rating</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            <template x-for="(row, i) in obligasi" :key="i">
                                <tr>
                                    <td class="px-1 py-1"><input type="text" :name="`obligasi[${i}][kode_obligasi]`" x-model="row.kode_obligasi" placeholder="FR0091" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" @change.debounce.500ms="lookupObligasiData(i)" /></td>
                                    <td class="px-1 py-1"><input type="text" :name="`obligasi[${i}][nama_obligasi]`" x-model="row.nama_obligasi" placeholder="Nama Obligasi" class="w-32 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][bobot]`" x-model="row.bobot" step="0.01" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" @input="hitungNilaiPasarObligasi(i)" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][nilai_pasar]`" x-model="row.nilai_pasar" step="0.01" readonly class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][ytm]`" x-model="row.ytm" step="0.01" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][kupon]`" x-model="row.kupon" step="0.01" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="date" :name="`obligasi[${i}][tanggal_jatuh_tempo]`" x-model="row.tanggal_jatuh_tempo" class="w-28 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="text" :name="`obligasi[${i}][penerbit]`" x-model="row.penerbit" placeholder="Penerbit" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][persen_nab]`" x-model="row.persen_nab" step="0.01" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][return_1m]`" x-model="row.return_1m" step="0.0001" readonly class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][return_3m]`" x-model="row.return_3m" step="0.0001" readonly class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][return_6m]`" x-model="row.return_6m" step="0.0001" readonly class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][return_1y]`" x-model="row.return_1y" step="0.0001" readonly class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][durasi]`" x-model="row.durasi" step="0.01" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1">
                                        <select :name="`obligasi[${i}][rating]`" x-model="row.rating" class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                                            <option value="">-</option>
                                            @foreach(['AAA','AA+','AA','AA-','A+','A','A-','BBB+','BBB','BBB-','BB','B','CCC','D'] as $r)
                                                <option value="{{ $r }}">{{ $r }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-1 py-1"><button type="button" @click="removeRow('obligasi', i)" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
            </div>
        </div>

        {{-- Sukuk --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-primary text-sm">Sukuk</h3>
                <button type="button" @click="addRow('sukuk')" class="text-xs text-primary hover:underline">+ Tambah Baris</button>
            </div>
            <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#f8fafc]">
                            <tr>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode Sukuk</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Sukuk</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Jenis</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Yield %</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Jatuh Tempo</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Rating</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">% thd NAB</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            <template x-for="(row, i) in sukuk" :key="i">
                                <tr>
                                    <td class="px-1 py-1"><input type="text" :name="`sukuk[${i}][kode_sukuk]`" x-model="row.kode_sukuk" placeholder="SR019" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="text" :name="`sukuk[${i}][nama_sukuk]`" x-model="row.nama_sukuk" placeholder="Nama Sukuk" class="w-36 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1">
                                        <select :name="`sukuk[${i}][jenis_sukuk]`" x-model="row.jenis_sukuk" class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20 w-24">
                                            <option value="">-</option>
                                            <option value="Negara">Negara</option>
                                            <option value="Korporasi">Korporasi</option>
                                        </select>
                                    </td>
                                    <td class="px-1 py-1"><input type="number" :name="`sukuk[${i}][bobot]`" x-model="row.bobot" step="0.01" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`sukuk[${i}][yield]`" x-model="row.yield" step="0.01" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="text" :name="`sukuk[${i}][jatuh_tempo]`" x-model="row.jatuh_tempo" placeholder="2028" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1">
                                        <select :name="`sukuk[${i}][rating]`" x-model="row.rating" class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                                            <option value="">-</option>
                                            @foreach(['AAA','AA+','AA','AA-','A+','A','A-','BBB+','BBB','BBB-','BB','B','CCC','D'] as $r)
                                                <option value="{{ $r }}">{{ $r }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-1 py-1"><input type="number" :name="`sukuk[${i}][persen_nab]`" x-model="row.persen_nab" step="0.01" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><button type="button" @click="removeRow('sukuk', i)" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
            </div>
        </div>

        {{-- Bank --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-primary text-sm">Bank</h3>
                <button type="button" @click="addRow('bank')" class="text-xs text-primary hover:underline">+ Tambah Baris</button>
            </div>
            <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#f8fafc]">
                            <tr>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Bank</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Jenis Bank</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nilai Pasar</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Tingkat Bunga</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Jangka Waktu</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">% thd NAB</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 1M</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 3M</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 6M</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 1 Thn</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">CAR %</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">NPL %</th>
                                <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Klasifikasi Risiko</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            <template x-for="(row, i) in bank" :key="i">
                                <tr>
                                    <td class="px-1 py-1"><input type="text" :name="`bank[${i}][nama_bank]`" x-model="row.nama_bank" placeholder="Nama Bank" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" @change.debounce.500ms="lookupBankData(i)" /></td>
                                    <td class="px-1 py-1">
                                        <select :name="`bank[${i}][jenis_bank]`" x-model="row.jenis_bank" class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20 w-24">
                                            <option value="">-</option>
                                            <option value="Bank Nasional">Bank Nasional</option>
                                            <option value="Bank Asing">Bank Asing</option>
                                            <option value="BPD">BPD</option>
                                            <option value="BPR">BPR</option>
                                        </select>
                                    </td>
                                    <td class="px-1 py-1"><input type="number" :name="`bank[${i}][bobot]`" x-model="row.bobot" step="0.01" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" @input="hitungNilaiPasarBank(i)" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`bank[${i}][nilai_pasar]`" x-model="row.nilai_pasar" step="0.01" readonly class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`bank[${i}][tingkat_bunga]`" x-model="row.tingkat_bunga" step="0.01" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="text" :name="`bank[${i}][jangka_waktu]`" x-model="row.jangka_waktu" placeholder="1 bln" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`bank[${i}][persen_nab]`" x-model="row.persen_nab" step="0.01" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`bank[${i}][return_1m]`" x-model="row.return_1m" step="0.0001" readonly class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`bank[${i}][return_3m]`" x-model="row.return_3m" step="0.0001" readonly class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`bank[${i}][return_6m]`" x-model="row.return_6m" step="0.0001" readonly class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`bank[${i}][return_1y]`" x-model="row.return_1y" step="0.0001" readonly class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`bank[${i}][car]`" x-model="row.car" step="0.01" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1"><input type="number" :name="`bank[${i}][npl]`" x-model="row.npl" step="0.01" class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                    <td class="px-1 py-1">
                                        <select :name="`bank[${i}][klasifikasi_risiko]`" x-model="row.klasifikasi_risiko" class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                                            <option value="">-</option>
                                            <option value="Rendah">Rendah</option>
                                            <option value="Sedang">Sedang</option>
                                            <option value="Tinggi">Tinggi</option>
                                        </select>
                                    </td>
                                    <td class="px-1 py-1"><button type="button" @click="removeRow('bank', i)" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                class="px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition">
                Simpan Perubahan
            </button>
            <a href="{{ $formRoutes['cancel'] ?? route('user.analisa.index') }}"
               class="px-5 py-2 text-sm font-medium text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">
                Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function editForm() {
    return {
        sektor:   @json($editData['sektor']),
        efek:     @json($editData['efek']),
        kinerja:  @json($editData['kinerja']),
        obligasi: @json($editData['obligasi']),
        sukuk: @json($editData['sukuk']),
        bank:     @json($editData['bank']),
        alokasi_aset: @json($editData['alokasi_aset']),

        totalAum: @json(old('total_aum', $analisa->total_aum)),
        totalMarcap10Efek: @json(old('total_marcap_10_efek', $analisa->total_marcap_10_efek)),
        tanggalData: @json(old('tanggal_data', $analisa->tanggal_data?->format('Y-m-d'))),

        manajerInvestasi: @json(old('manajer_investasi', $analisa->manajer_investasi)),
        bankKustodian: @json(old('bank_kustodian', $analisa->bank_kustodian)),
        tanggalPeluncuran: @json(old('tanggal_peluncuran', $analisa->tanggal_peluncuran?->format('Y-m-d'))),
        mataUang: @json(old('mata_uang', $analisa->mata_uang)),
        benchmark: @json(old('benchmark', $analisa->benchmark)),
        tujuanInvestasi: @json(old('tujuan_investasi', $analisa->tujuan_investasi)),
        kebijakanInvestasi: @json(old('kebijakan_investasi', $analisa->kebijakan_investasi)),
        returnYtd: @json(old('return_ytd', $analisa->return_ytd)),
        return1y: @json(old('return_1y', $analisa->return_1y)),
        totalReturn: @json(old('total_return', $analisa->total_return)),
        biayaOperasi: @json(old('biaya_operasi', $analisa->biaya_operasi)),
        portfolioTurnover: @json(old('portfolio_turnover_ratio', $analisa->portfolio_turnover_ratio)),
        managementFee: @json(old('management_fee', $analisa->management_fee)),
        custodianFee: @json(old('custodian_fee', $analisa->custodian_fee)),
        totalAset: @json(old('total_aset', $analisa->total_aset)),
        totalLiabilitas: @json(old('total_liabilitas', $analisa->total_liabilitas)),
        kasDanBank: @json(old('kas_dan_bank', $analisa->kas_dan_bank)),
        piutangBunga: @json(old('piutang_bunga', $analisa->piutang_bunga)),
        piutangDividen: @json(old('piutang_dividen', $analisa->piutang_dividen)),
        piutangLain: @json(old('piutang_lain', $analisa->piutang_lain)),
        utangPajak: @json(old('utang_pajak', $analisa->utang_pajak)),
        utangLain: @json(old('utang_lain', $analisa->utang_lain)),
        pendapatanBunga: @json(old('pendapatan_bunga', $analisa->pendapatan_bunga)),
        pendapatanDividen: @json(old('pendapatan_dividen', $analisa->pendapatan_dividen)),
        gainRealized: @json(old('gain_realized', $analisa->gain_realized)),
        gainUnrealized: @json(old('gain_unrealized', $analisa->gain_unrealized)),
        bebanMi: @json(old('beban_mi', $analisa->beban_mi)),
        bebanKustodian: @json(old('beban_kustodian', $analisa->beban_kustodian)),
        bebanLain: @json(old('beban_lain', $analisa->beban_lain)),
        labaBersih: @json(old('laba_bersih', $analisa->laba_bersih)),
        arusKasOperasi: @json(old('arus_kas_operasi', $analisa->arus_kas_operasi)),
        arusKasPendanaan: @json(old('arus_kas_pendanaan', $analisa->arus_kas_pendanaan)),
        kasAwalTahun: @json(old('kas_awal_tahun', $analisa->kas_awal_tahun)),
        kasAkhirTahun: @json(old('kas_akhir_tahun', $analisa->kas_akhir_tahun)),
        totalHasilInvestasi: @json(old('total_hasil_investasi', $analisa->total_hasil_investasi)),
        hasilInvestasiSetelahBiaya: @json(old('hasil_investasi_setelah_biaya', $analisa->hasil_investasi_setelah_biaya)),
        persentasePph: @json(old('persentase_pph', $analisa->persentase_pph)),
        fairValueLevel1: @json(old('fair_value_level_1', $analisa->fair_value_level_1)),
        fairValueLevel2: @json(old('fair_value_level_2', $analisa->fair_value_level_2)),
        fairValueLevel3: @json(old('fair_value_level_3', $analisa->fair_value_level_3)),
        unitMilikInvestor: @json(old('unit_milik_investor', $analisa->unit_milik_investor)),
        unitMilikMi: @json(old('unit_milik_mi', $analisa->unit_milik_mi)),
        totalUnitBeredar: @json(old('total_unit_beredar', $analisa->total_unit_beredar)),

        lookupSektorUrl: @json($formRoutes['lookup_sektor'] ?? null),
        lookupIhsgUrl: @json($formRoutes['lookup_ihsg'] ?? null),
        lookupReturnUrl: @json($formRoutes['lookup_return'] ?? null),
        lookupBondReturnUrl: @json($formRoutes['lookup_bond_return'] ?? null),
        lookupBankDataUrl: @json($formRoutes['lookup_bank_data'] ?? null),

        addRow(type) {
            const defaults = {
                sektor:   { nama_sektor: '', bobot: '' },
                efek:     { kode_efek: '', nama_efek: '', sektor: '', bobot: '', kontribusi_kinerja: '', market_cap: '', nilai_pasar: '', harga_perolehan: '', persen_nab: '', return_1m: '', return_3m: '', return_6m: '', return_1y: '', effect_type: 'Saham', top_10: false },
                kinerja:  { periode: '', return_pct: '' },
                obligasi: { kode_obligasi: '', nama_obligasi: '', bobot: '', nilai_pasar: '', ytm: '', kupon: '', tanggal_jatuh_tempo: '', penerbit: '', persen_nab: '', return_1m: '', return_3m: '', return_6m: '', return_1y: '', durasi: '', rating: '' },
                sukuk: { kode_sukuk: '', nama_sukuk: '', jenis_sukuk: '', bobot: '', yield: '', jatuh_tempo: '', rating: '', persen_nab: '' },
                bank:     { nama_bank: '', jenis_bank: '', bobot: '', nilai_pasar: '', tingkat_bunga: '', jangka_waktu: '', persen_nab: '', return_1m: '', return_3m: '', return_6m: '', return_1y: '', car: '', npl: '', klasifikasi_risiko: '' },
                alokasi_aset: { nama_aset: '', persentase: '' },
            };
            this[type].push({ ...defaults[type] });
        },

        removeRow(type, i) {
            this[type].splice(i, 1);
        },

        alokasiAsetTotal() {
            return this.alokasi_aset.reduce((sum, row) => sum + (parseFloat(row.persentase) || 0), 0);
        },

        alokasiAsetTotalValid() {
            const filled = this.alokasi_aset.some(row => String(row.nama_aset || '').trim() !== '' || String(row.persentase || '').trim() !== '');
            return !filled || Math.abs(this.alokasiAsetTotal() - 100) <= 0.01;
        },

        totalAumValue() {
            return parseFloat(this.totalAum || 0);
        },

        tanggalDataValue() {
            return this.tanggalData || '';
        },

        hitungNilaiPasarEfek(i) {
            const aum = this.totalAumValue();
            const bobot = parseFloat(this.efek[i]?.bobot) || 0;
            this.efek[i].nilai_pasar = (aum > 0 && bobot > 0) ? (bobot / 100 * aum).toFixed(2) : '';
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

        hitungNilaiPasarSemua() {
            this.efek.forEach((_, i) => this.hitungNilaiPasarEfek(i));
            this.obligasi.forEach((_, i) => this.hitungNilaiPasarObligasi(i));
            this.bank.forEach((_, i) => this.hitungNilaiPasarBank(i));
        },

        hitungTotalMarcap10() {
            const total = this.efek
                .filter(e => !e.effect_type || e.effect_type === '' || e.effect_type === 'Saham')
                .reduce((sum, e) => sum + (parseFloat(e.kontribusi_kinerja) || 0), 0);
            this.totalMarcap10Efek = total > 0 ? total.toFixed(4) : '';
        },

        lookupEfekData(i) {
            const kode = String(this.efek[i]?.kode_efek || '').trim().toUpperCase();
            const tanggal = this.tanggalDataValue();
            if (!kode || kode.length < 2) return;

            this.efek[i].effect_type = 'Saham';

            if (this.lookupSektorUrl) {
                fetch(this.lookupSektorUrl + '?kode_efek=' + encodeURIComponent(kode) + '&tanggal=' + encodeURIComponent(tanggal), { headers: { Accept: 'application/json' } })
                    .then(r => r.json())
                    .then(resp => { if (resp.found) this.efek[i].sektor = resp.sektor; })
                    .catch(() => {});
            }

            if (tanggal && this.lookupIhsgUrl) {
                fetch(this.lookupIhsgUrl + '?kode_efek=' + encodeURIComponent(kode) + '&tanggal=' + encodeURIComponent(tanggal), { headers: { Accept: 'application/json' } })
                    .then(r => r.json())
                    .then(resp => {
                        if (resp.found) {
                            this.efek[i].kontribusi_kinerja = resp.kontribusi;
                            this.hitungTotalMarcap10();
                        }
                    })
                    .catch(() => {});
            }

            if (tanggal && this.lookupReturnUrl) {
                fetch(this.lookupReturnUrl + '?kode_efek=' + encodeURIComponent(kode) + '&tanggal=' + encodeURIComponent(tanggal), { headers: { Accept: 'application/json' } })
                    .then(r => r.json())
                    .then(resp => {
                        if (resp.found) {
                            this.efek[i].return_1m = resp.return_1m ?? '';
                            this.efek[i].return_3m = resp.return_3m ?? '';
                            this.efek[i].return_6m = resp.return_6m ?? '';
                            this.efek[i].return_1y = resp.return_1y ?? '';
                        }
                    })
                    .catch(() => {});
            }
        },

        lookupObligasiData(i) {
            const kode = String(this.obligasi[i]?.kode_obligasi || '').trim().toUpperCase();
            const tanggal = this.tanggalDataValue();
            if (!kode || kode.length < 2 || !tanggal || !this.lookupBondReturnUrl) return;

            fetch(this.lookupBondReturnUrl + '?kode_efek=' + encodeURIComponent(kode) + '&tanggal=' + encodeURIComponent(tanggal), { headers: { Accept: 'application/json' } })
                .then(r => r.json())
                .then(resp => {
                    if (resp.found) {
                        this.obligasi[i].return_1m = resp.return_1m ?? '';
                        this.obligasi[i].return_3m = resp.return_3m ?? '';
                        this.obligasi[i].return_6m = resp.return_6m ?? '';
                        this.obligasi[i].return_1y = resp.return_1y ?? '';
                    }
                })
                .catch(() => {});
        },

        lookupBankData(i) {
            const nama = String(this.bank[i]?.nama_bank || '').trim();
            if (!nama || nama.length < 2 || !this.lookupBankDataUrl) return;

            fetch(this.lookupBankDataUrl + '?nama_bank=' + encodeURIComponent(nama), { headers: { Accept: 'application/json' } })
                .then(r => r.json())
                .then(resp => {
                    if (resp.found) {
                        this.bank[i].jenis_bank = resp.jenis_bank ?? this.bank[i].jenis_bank;
                        this.bank[i].return_1m = resp.return_1m ?? '';
                        this.bank[i].return_3m = resp.return_3m ?? '';
                        this.bank[i].return_6m = resp.return_6m ?? '';
                        this.bank[i].return_1y = resp.return_1y ?? '';
                        this.bank[i].car = resp.car ?? '';
                        this.bank[i].npl = resp.npl ?? '';
                    }
                })
                .catch(() => {});
        },
    };
}
</script>
@endpush
@endsection
