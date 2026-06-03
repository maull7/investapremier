@extends($formRoutes['layout'] ?? 'layouts.user')

@section('content')
    <div class="max-w-5xl" x-data="analisaForm()">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-primary">Submit Analisa {{ $productLabel ?? 'Reksa Dana' }}</h1>
            <p class="text-sm text-muted mt-0.5">Isi data secara manual, upload Excel, atau ekstrak dari PDF FFS</p>
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

        <form id="analisa-form" method="POST" action="{{ $formRoutes['store'] }}" enctype="multipart/form-data"
            class="space-y-6"
            @submit="if (mode === 'link-website') { $event.preventDefault(); webMessage = 'Selesaikan langkah di tab Link Website: unduh file lalu klik Isi Form Otomatis. Setelah itu submit dari tab Input Manual.'; webOk = false; }">
            @csrf
            <input type="hidden" name="input_mode" :value="mode === 'link-website' ? 'manual' : mode">
            <input type="hidden" name="pdf_file" x-model="pdfFile">
            <input type="hidden" name="tanggal_data" :value="tanggalDataValue()">
            <input type="hidden" name="ffs_bulan" :value="jenisLaporan === 'kalender_ffs' ? ffsBulan : ''">
            <input type="hidden" name="ffs_tahun" :value="jenisLaporan === 'kalender_ffs' ? ffsTahun : ''">
            <input type="hidden" name="jenis_laporan" :value="jenisLaporan">

            {{-- Info Dasar --}}
            <div class="bg-white rounded-xl border border-line p-6 space-y-4" x-show="mode !== 'link-website'" x-cloak>
                <h3 class="font-semibold text-primary">Informasi Reksa Dana</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="kode_reksa_dana" value="Kode Reksa Dana" />
                        <x-text-input id="kode_reksa_dana" name="kode_reksa_dana" type="text" class="mt-1 block w-full"
                            value="{{ old('kode_reksa_dana') }}"
                            @input.debounce.500ms="lookupReksaDana($event.target.value)" />
                        <x-input-error :messages="$errors->get('kode_reksa_dana')" class="mt-1" />
                        <p class="text-xs mt-1" :class="lookupOk ? 'text-emerald-600' : 'text-muted'"
                            x-text="lookupMessage"></p>
                    </div>
                    <div>
                        <x-input-label for="nama_reksa_dana" value="Nama Reksa Dana *" />
                        <x-text-input id="nama_reksa_dana" name="nama_reksa_dana" type="text" class="mt-1 block w-full"
                            value="{{ old('nama_reksa_dana') }}" x-bind:required="mode !== 'link-website'" />
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
                            x-bind:required="mode !== 'link-website'">
                            <option value="">Pilih Jenis</option>
                            @foreach (['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'] as $j)
                                <option value="{{ $j }}" {{ old('jenis_reksa_dana') === $j ? 'selected' : '' }}>
                                    {{ $j }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('jenis_reksa_dana')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="benchmark" value="Benchmark *" />
                        <x-text-input id="benchmark" name="benchmark" type="text" class="mt-1 block w-full"
                            value="{{ old('benchmark') }}" x-bind:required="mode !== 'link-website'" />
                        <x-input-error :messages="$errors->get('benchmark')" class="mt-1" />
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                    <div>
                        <x-input-label for="tujuan_investasi" value="Tujuan Investasi *" />
                        <x-text-input id="tujuan_investasi" name="tujuan_investasi" type="text" class="mt-1 block w-full"
                            value="{{ old('tujuan_investasi') }}" x-bind:required="mode !== 'link-website'" />
                        <x-input-error :messages="$errors->get('tujuan_investasi')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="kebijakan_investasi" value="Kebijakan Investasi *" />
                        <x-text-input id="kebijakan_investasi" name="kebijakan_investasi" type="text"
                            class="mt-1 block w-full" value="{{ old('kebijakan_investasi') }}"
                            x-bind:required="mode !== 'link-website'" />
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
                            <x-text-input id="ffs_tahun_top" type="number" min="2000" max="2100"
                                placeholder="2026" class="mt-1 block w-full" x-model="ffsTahun" />
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

            {{-- Tab Pilih Mode --}}
            <div class="bg-white rounded-xl border border-line overflow-hidden">
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
                            <x-input-label for="total_aum_manual" value="Total AUM (Rp)" />
                            <x-text-input id="total_aum_manual" name="total_aum" type="number" step="0.01"
                                class="mt-1 block w-full" x-model="totalAum" />
                        </div>
                        <div>
                            <x-input-label for="total_marcap_10_efek_manual" value="Total MarCap 10 Saham Terbesar (Rp)" />
                            <x-text-input id="total_marcap_10_efek_manual" name="total_marcap_10_efek" type="number"
                                step="0.01" class="mt-1 block w-full" x-model="totalMarcap10Efek" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="unit_penyertaan_manual" value="Jumlah Unit Penyertaan" />
                            <x-text-input id="unit_penyertaan_manual" name="unit_penyertaan" type="number" step="0.0001"
                                class="mt-1 block w-full" x-model="unitPenyertaan" />
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
                    </div>

                    {{-- Informasi Umum --}}
                    <div class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Informasi Umum</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="manajer_investasi_manual" value="Manajer Investasi" />
                                <x-text-input id="manajer_investasi_manual" name="manajer_investasi" type="text" class="mt-1 block w-full" x-model="manajerInvestasi" />
                            </div>
                            <div>
                                <x-input-label for="bank_kustodian_manual" value="Bank Kustodian" />
                                <x-text-input id="bank_kustodian_manual" name="bank_kustodian" type="text" class="mt-1 block w-full" x-model="bankKustodian" />
                            </div>
                        </div>
                    </div>

                    {{-- Kinerja --}}
                    <div class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Kinerja</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="return_ytd_manual" value="Return YTD (%)" />
                                <x-text-input id="return_ytd_manual" name="return_ytd" type="number" step="0.01" class="mt-1 block w-full" x-model="returnYtd" />
                            </div>
                            <div>
                                <x-input-label for="return_1y_manual" value="Return 1 Tahun (%)" />
                                <x-text-input id="return_1y_manual" name="return_1y" type="number" step="0.01" class="mt-1 block w-full" x-model="return1y" />
                            </div>
                        </div>
                    </div>

                    {{-- Rasio Keuangan --}}
                    <div class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Rasio Keuangan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="total_return_manual" value="Total Return (%)" />
                                <x-text-input id="total_return_manual" name="total_return" type="number" step="0.01" class="mt-1 block w-full" x-model="totalReturn" />
                            </div>
                            <div>
                                <x-input-label for="biaya_operasi_manual" value="Biaya Operasi (%)" />
                                <x-text-input id="biaya_operasi_manual" name="biaya_operasi" type="number" step="0.01" class="mt-1 block w-full" x-model="biayaOperasi" />
                            </div>
                            <div>
                                <x-input-label for="portfolio_turnover_manual" value="Portfolio Turnover Ratio" />
                                <x-text-input id="portfolio_turnover_manual" name="portfolio_turnover_ratio" type="number" step="0.01" class="mt-1 block w-full" x-model="portfolioTurnover" />
                            </div>
                        </div>
                    </div>

                    {{-- Biaya --}}
                    <div class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Biaya</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="management_fee_manual" value="Management Fee (%)" />
                                <x-text-input id="management_fee_manual" name="management_fee" type="number" step="0.01" class="mt-1 block w-full" x-model="managementFee" />
                            </div>
                            <div>
                                <x-input-label for="custodian_fee_manual" value="Custodian Fee (%)" />
                                <x-text-input id="custodian_fee_manual" name="custodian_fee" type="number" step="0.01" class="mt-1 block w-full" x-model="custodianFee" />
                            </div>
                        </div>
                    </div>

                    @include('analisa.partials.form-alokasi-aset')

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-primary text-sm">Komposisi Sektor</h4>
                            <button type="button" @click="addRow('sektor')" class="text-xs text-primary hover:underline">+
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

                    <div>
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
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Sektor</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nilai Pasar</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Harga Perolehan</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">% thd NAB</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kontribusi % IHSG
                                        </th>
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
                                            <td class="px-1 py-1"><input type="text" :name="`efek[${i}][kode_efek]`"
                                                    x-model="row.kode_efek" placeholder="BBCA"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                                    @change.debounce.500ms="lookupEfekData(i)" />
                                            </td>
                                            <td class="px-1 py-1"><input type="text" :name="`efek[${i}][nama_efek]`"
                                                    x-model="row.nama_efek" placeholder="Nama Efek"
                                                    class="w-40 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1">
                                                <input type="hidden" :name="`efek[${i}][effect_type]`" x-model="row.effect_type" />
                                                <input type="text" :name="`efek[${i}][sektor]`"
                                                    x-model="row.sektor" placeholder="Sektor"
                                                    class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][bobot]`"
                                                    x-model="row.bobot" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                                    @input="hitungNilaiPasarEfek(i)" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][nilai_pasar]`"
                                                    x-model="row.nilai_pasar" step="0.01" readonly
                                                    class="w-28 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`efek[${i}][harga_perolehan]`"
                                                    x-model="row.harga_perolehan" step="0.01"
                                                    class="w-28 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`efek[${i}][persen_nab]`"
                                                    x-model="row.persen_nab" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`efek[${i}][kontribusi_kinerja]`"
                                                    x-model="row.kontribusi_kinerja" step="0.0001"
                                                    class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                                    @change="hitungTotalMarcap10" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_1m]`"
                                                    x-model="row.return_1m" step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_3m]`"
                                                    x-model="row.return_3m" step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_6m]`"
                                                    x-model="row.return_6m" step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_1y]`"
                                                    x-model="row.return_1y" step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1 text-center"><input type="checkbox"
                                                    :name="`efek[${i}][top_10]`" x-model="row.top_10" value="1"
                                                    class="rounded border-gray-300 text-primary focus:ring-primary"
                                                    @change="hitungTotalMarcap10" /></td>
                                            <td class="px-1 py-1"><button type="button" @click="removeRow('efek', i)"
                                                    class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB: INPUT LENGKAP --}}
                <div x-show="mode==='lengkap'" class="p-6 space-y-8">

                    {{-- Sektor --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="total_aum" value="Total AUM (Rp)" />
                            <x-text-input id="total_aum" name="total_aum" type="number" step="0.01"
                                class="mt-1 block w-full" x-model="totalAum" />
                        </div>
                        <div>
                            <x-input-label for="total_marcap_10_efek" value="Total MarCap 10 Saham Terbesar (Rp)" />
                            <x-text-input id="total_marcap_10_efek" name="total_marcap_10_efek" type="number"
                                step="0.01" class="mt-1 block w-full bg-gray-50" x-model="totalMarcap10Efek" readonly />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="unit_penyertaan_lengkap" value="Jumlah Unit Penyertaan" />
                            <x-text-input id="unit_penyertaan_lengkap" name="unit_penyertaan" type="number"
                                step="0.0001" class="mt-1 block w-full" x-model="unitPenyertaan" />
                        </div>
                        <div>
                            <x-input-label for="nab_per_unit_lengkap" value="NAB/UP" />
                            <x-text-input id="nab_per_unit_lengkap" name="nab_per_unit" type="number" step="0.000001"
                                class="mt-1 block w-full" x-model="nabPerUnit" />
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
                                    class="border-gray-300 rounded text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20"
                                    placeholder="2026" />
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Reksa Dana Lengkap --}}
                    <div class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Informasi Reksa Dana</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="benchmark_lengkap" value="Benchmark" />
                                <x-text-input id="benchmark_lengkap" name="benchmark" type="text" class="mt-1 block w-full" x-model="benchmark" />
                            </div>
                            <div>
                                <x-input-label for="manajer_investasi_lengkap" value="Manajer Investasi" />
                                <x-text-input id="manajer_investasi_lengkap" name="manajer_investasi" type="text" class="mt-1 block w-full" x-model="manajerInvestasi" />
                            </div>
                            <div>
                                <x-input-label for="bank_kustodian_lengkap" value="Bank Kustodian" />
                                <x-text-input id="bank_kustodian_lengkap" name="bank_kustodian" type="text" class="mt-1 block w-full" x-model="bankKustodian" />
                            </div>
                            <div>
                                <x-input-label for="tanggal_peluncuran_lengkap" value="Tanggal Peluncuran" />
                                <x-text-input id="tanggal_peluncuran_lengkap" name="tanggal_peluncuran" type="date" class="mt-1 block w-full" x-model="tanggalPeluncuran" />
                            </div>
                            <div>
                                <x-input-label for="mata_uang_lengkap" value="Mata Uang" />
                                <x-text-input id="mata_uang_lengkap" name="mata_uang" type="text" class="mt-1 block w-full" x-model="mataUang" />
                            </div>
                        </div>
                    </div>

                    {{-- Laporan Keuangan - Neraca --}}
                    <div class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Laporan Keuangan — Neraca</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <x-input-label for="total_aset" value="Total Aset (Rp)" />
                                <x-text-input id="total_aset" name="total_aset" type="number" step="0.01" class="mt-1 block w-full" x-model="totalAset" />
                            </div>
                            <div>
                                <x-input-label for="total_liabilitas" value="Total Liabilitas (Rp)" />
                                <x-text-input id="total_liabilitas" name="total_liabilitas" type="number" step="0.01" class="mt-1 block w-full" x-model="totalLiabilitas" />
                            </div>
                            <div>
                                <x-input-label for="kas_dan_bank" value="Kas dan Bank (Rp)" />
                                <x-text-input id="kas_dan_bank" name="kas_dan_bank" type="number" step="0.01" class="mt-1 block w-full" x-model="kasDanBank" />
                            </div>
                            <div>
                                <x-input-label for="piutang_bunga" value="Piutang Bunga (Rp)" />
                                <x-text-input id="piutang_bunga" name="piutang_bunga" type="number" step="0.01" class="mt-1 block w-full" x-model="piutangBunga" />
                            </div>
                            <div>
                                <x-input-label for="piutang_dividen" value="Piutang Dividen (Rp)" />
                                <x-text-input id="piutang_dividen" name="piutang_dividen" type="number" step="0.01" class="mt-1 block w-full" x-model="piutangDividen" />
                            </div>
                            <div>
                                <x-input-label for="piutang_lain" value="Piutang Lain-lain (Rp)" />
                                <x-text-input id="piutang_lain" name="piutang_lain" type="number" step="0.01" class="mt-1 block w-full" x-model="piutangLain" />
                            </div>
                            <div>
                                <x-input-label for="utang_pajak" value="Utang Pajak (Rp)" />
                                <x-text-input id="utang_pajak" name="utang_pajak" type="number" step="0.01" class="mt-1 block w-full" x-model="utangPajak" />
                            </div>
                            <div>
                                <x-input-label for="utang_lain" value="Utang Lain-lain (Rp)" />
                                <x-text-input id="utang_lain" name="utang_lain" type="number" step="0.01" class="mt-1 block w-full" x-model="utangLain" />
                            </div>
                        </div>
                    </div>

                    {{-- Laporan Keuangan - Laba Rugi --}}
                    <div class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Laporan Keuangan — Laba Rugi</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <x-input-label for="pendapatan_bunga" value="Pendapatan Bunga (Rp)" />
                                <x-text-input id="pendapatan_bunga" name="pendapatan_bunga" type="number" step="0.01" class="mt-1 block w-full" x-model="pendapatanBunga" />
                            </div>
                            <div>
                                <x-input-label for="pendapatan_dividen" value="Pendapatan Dividen (Rp)" />
                                <x-text-input id="pendapatan_dividen" name="pendapatan_dividen" type="number" step="0.01" class="mt-1 block w-full" x-model="pendapatanDividen" />
                            </div>
                            <div>
                                <x-input-label for="gain_realized" value="Gain Realized (Rp)" />
                                <x-text-input id="gain_realized" name="gain_realized" type="number" step="0.01" class="mt-1 block w-full" x-model="gainRealized" />
                            </div>
                            <div>
                                <x-input-label for="gain_unrealized" value="Gain Unrealized (Rp)" />
                                <x-text-input id="gain_unrealized" name="gain_unrealized" type="number" step="0.01" class="mt-1 block w-full" x-model="gainUnrealized" />
                            </div>
                            <div>
                                <x-input-label for="beban_mi" value="Beban Manajer Investasi (Rp)" />
                                <x-text-input id="beban_mi" name="beban_mi" type="number" step="0.01" class="mt-1 block w-full" x-model="bebanMi" />
                            </div>
                            <div>
                                <x-input-label for="beban_kustodian" value="Beban Kustodian (Rp)" />
                                <x-text-input id="beban_kustodian" name="beban_kustodian" type="number" step="0.01" class="mt-1 block w-full" x-model="bebanKustodian" />
                            </div>
                            <div>
                                <x-input-label for="beban_lain" value="Beban Lain-lain (Rp)" />
                                <x-text-input id="beban_lain" name="beban_lain" type="number" step="0.01" class="mt-1 block w-full" x-model="bebanLain" />
                            </div>
                            <div>
                                <x-input-label for="laba_bersih" value="Laba Bersih (Rp)" />
                                <x-text-input id="laba_bersih" name="laba_bersih" type="number" step="0.01" class="mt-1 block w-full" x-model="labaBersih" />
                            </div>
                        </div>
                    </div>

                    {{-- Laporan Keuangan - Arus Kas --}}
                    <div class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Laporan Keuangan — Arus Kas</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <x-input-label for="arus_kas_operasi" value="Arus Kas Operasi (Rp)" />
                                <x-text-input id="arus_kas_operasi" name="arus_kas_operasi" type="number" step="0.01" class="mt-1 block w-full" x-model="arusKasOperasi" />
                            </div>
                            <div>
                                <x-input-label for="arus_kas_pendanaan" value="Arus Kas Pendanaan (Rp)" />
                                <x-text-input id="arus_kas_pendanaan" name="arus_kas_pendanaan" type="number" step="0.01" class="mt-1 block w-full" x-model="arusKasPendanaan" />
                            </div>
                            <div>
                                <x-input-label for="kas_awal_tahun" value="Kas Awal Tahun (Rp)" />
                                <x-text-input id="kas_awal_tahun" name="kas_awal_tahun" type="number" step="0.01" class="mt-1 block w-full" x-model="kasAwalTahun" />
                            </div>
                            <div>
                                <x-input-label for="kas_akhir_tahun" value="Kas Akhir Tahun (Rp)" />
                                <x-text-input id="kas_akhir_tahun" name="kas_akhir_tahun" type="number" step="0.01" class="mt-1 block w-full" x-model="kasAkhirTahun" />
                            </div>
                        </div>
                    </div>

                    {{-- Rasio Keuangan Lengkap --}}
                    <div class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Rasio Keuangan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="total_hasil_investasi" value="Total Hasil Investasi (%)" />
                                <x-text-input id="total_hasil_investasi" name="total_hasil_investasi" type="number" step="0.01" class="mt-1 block w-full" x-model="totalHasilInvestasi" />
                            </div>
                            <div>
                                <x-input-label for="hasil_investasi_setelah_biaya" value="Hasil Investasi Setelah Biaya Pemasaran (%)" />
                                <x-text-input id="hasil_investasi_setelah_biaya" name="hasil_investasi_setelah_biaya" type="number" step="0.01" class="mt-1 block w-full" x-model="hasilInvestasiSetelahBiaya" />
                            </div>
                            <div>
                                <x-input-label for="biaya_operasi_lengkap" value="Biaya Operasi (%)" />
                                <x-text-input id="biaya_operasi_lengkap" name="biaya_operasi" type="number" step="0.01" class="mt-1 block w-full" x-model="biayaOperasi" />
                            </div>
                            <div>
                                <x-input-label for="portfolio_turnover_lengkap" value="Portfolio Turnover Ratio" />
                                <x-text-input id="portfolio_turnover_lengkap" name="portfolio_turnover_ratio" type="number" step="0.01" class="mt-1 block w-full" x-model="portfolioTurnover" />
                            </div>
                            <div>
                                <x-input-label for="persentase_pph" value="Persentase Penghasilan Kena Pajak (%)" />
                                <x-text-input id="persentase_pph" name="persentase_pph" type="number" step="0.01" class="mt-1 block w-full" x-model="persentasePph" />
                            </div>
                        </div>
                    </div>

                    {{-- Fair Value --}}
                    <div class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Fair Value / Pengukuran Nilai Wajar</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="fair_value_level_1" value="Level 1 (Rp)" />
                                <x-text-input id="fair_value_level_1" name="fair_value_level_1" type="number" step="0.01" class="mt-1 block w-full" x-model="fairValueLevel1" />
                            </div>
                            <div>
                                <x-input-label for="fair_value_level_2" value="Level 2 (Rp)" />
                                <x-text-input id="fair_value_level_2" name="fair_value_level_2" type="number" step="0.01" class="mt-1 block w-full" x-model="fairValueLevel2" />
                            </div>
                            <div>
                                <x-input-label for="fair_value_level_3" value="Level 3 (Rp)" />
                                <x-text-input id="fair_value_level_3" name="fair_value_level_3" type="number" step="0.01" class="mt-1 block w-full" x-model="fairValueLevel3" />
                            </div>
                        </div>
                    </div>

                    {{-- Unit Penyertaan --}}
                    <div class="border-t border-line pt-4">
                        <h4 class="font-semibold text-primary text-sm mb-3">Unit Penyertaan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="unit_milik_investor" value="Unit Milik Investor" />
                                <x-text-input id="unit_milik_investor" name="unit_milik_investor" type="number" step="0.0001" class="mt-1 block w-full" x-model="unitMilikInvestor" />
                            </div>
                            <div>
                                <x-input-label for="unit_milik_mi" value="Unit Milik Manajer Investasi" />
                                <x-text-input id="unit_milik_mi" name="unit_milik_mi" type="number" step="0.0001" class="mt-1 block w-full" x-model="unitMilikMi" />
                            </div>
                            <div>
                                <x-input-label for="total_unit_beredar" value="Total Unit Beredar" />
                                <x-text-input id="total_unit_beredar" name="total_unit_beredar" type="number" step="0.0001" class="mt-1 block w-full" x-model="totalUnitBeredar" />
                            </div>
                        </div>
                    </div>

                    @include('analisa.partials.form-alokasi-aset')

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

                    {{-- Efek --}}
                    <div>
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
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Sektor</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nilai Pasar</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Harga Perolehan</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">% thd NAB</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kontribusi % IHSG
                                        </th>
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
                                            <td class="px-1 py-1"><input type="text" :name="`efek[${i}][kode_efek]`"
                                                    x-model="row.kode_efek" placeholder="BBCA"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                                    @change.debounce.500ms="lookupEfekData(i)" />
                                            </td>
                                            <td class="px-1 py-1"><input type="text" :name="`efek[${i}][nama_efek]`"
                                                    x-model="row.nama_efek" placeholder="Nama Efek"
                                                    class="w-40 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1">
                                                <input type="hidden" :name="`efek[${i}][effect_type]`" x-model="row.effect_type" />
                                                <input type="text" :name="`efek[${i}][sektor]`"
                                                    x-model="row.sektor" placeholder="Sektor"
                                                    class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][bobot]`"
                                                    x-model="row.bobot" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                                    @input="hitungNilaiPasarEfek(i)" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][nilai_pasar]`"
                                                    x-model="row.nilai_pasar" step="0.01" readonly
                                                    class="w-28 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`efek[${i}][harga_perolehan]`"
                                                    x-model="row.harga_perolehan" step="0.01"
                                                    class="w-28 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`efek[${i}][persen_nab]`"
                                                    x-model="row.persen_nab" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`efek[${i}][kontribusi_kinerja]`"
                                                    x-model="row.kontribusi_kinerja" step="0.0001"
                                                    class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                                    @change="hitungTotalMarcap10" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_1m]`"
                                                    x-model="row.return_1m" step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_3m]`"
                                                    x-model="row.return_3m" step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_6m]`"
                                                    x-model="row.return_6m" step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_1y]`"
                                                    x-model="row.return_1y" step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1 text-center"><input type="checkbox"
                                                    :name="`efek[${i}][top_10]`" x-model="row.top_10" value="1"
                                                    class="rounded border-gray-300 text-primary focus:ring-primary"
                                                    @change="hitungTotalMarcap10" /></td>
                                            <td class="px-1 py-1"><button type="button" @click="removeRow('efek', i)"
                                                    class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                        </tr>
                                    </template>
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
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-primary text-sm">Obligasi</h4>
                            <button type="button" @click="addRow('obligasi')"
                                class="text-xs text-primary hover:underline">+ Tambah Baris</button>
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
                                            <td class="px-1 py-1"><input type="text"
                                                    :name="`obligasi[${i}][kode_obligasi]`" x-model="row.kode_obligasi"
                                                    placeholder="FR0091"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                                    @change.debounce.500ms="lookupObligasiData(i)" />
                                            </td>
                                            <td class="px-1 py-1"><input type="text"
                                                    :name="`obligasi[${i}][nama_obligasi]`" x-model="row.nama_obligasi"
                                                    placeholder="Nama Obligasi"
                                                    class="w-36 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][bobot]`"
                                                    x-model="row.bobot" step="0.01"
                                                    class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                                    @input="hitungNilaiPasarObligasi(i)" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`obligasi[${i}][nilai_pasar]`" x-model="row.nilai_pasar"
                                                    step="0.01" readonly
                                                    class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`obligasi[${i}][ytm]`" x-model="row.ytm" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`obligasi[${i}][kupon]`" x-model="row.kupon" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="date"
                                                    :name="`obligasi[${i}][tanggal_jatuh_tempo]`"
                                                    x-model="row.tanggal_jatuh_tempo"
                                                    class="w-28 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="text"
                                                    :name="`obligasi[${i}][penerbit]`" x-model="row.penerbit"
                                                    placeholder="Penerbit"
                                                    class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`obligasi[${i}][persen_nab]`"
                                                    x-model="row.persen_nab" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`obligasi[${i}][return_1m]`" x-model="row.return_1m"
                                                    step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`obligasi[${i}][return_3m]`" x-model="row.return_3m"
                                                    step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`obligasi[${i}][return_6m]`" x-model="row.return_6m"
                                                    step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`obligasi[${i}][return_1y]`" x-model="row.return_1y"
                                                    step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][durasi]`"
                                                    x-model="row.durasi" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1">
                                                <select :name="`obligasi[${i}][rating]`" x-model="row.rating"
                                                    class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                                                    <option value="">-</option>
                                                    @foreach (['AAA', 'AA+', 'AA', 'AA-', 'A+', 'A', 'A-', 'BBB+', 'BBB', 'BBB-', 'BB', 'B', 'CCC', 'D'] as $r)
                                                        <option value="{{ $r }}">{{ $r }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-1 py-1"><button type="button"
                                                    @click="removeRow('obligasi', i)"
                                                    class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Sukuk --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-primary text-sm">Sukuk</h4>
                            <button type="button" @click="addRow('sukuk')"
                                class="text-xs text-primary hover:underline">+ Tambah Baris</button>
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
                                            <td class="px-1 py-1"><input type="text"
                                                    :name="`sukuk[${i}][kode_sukuk]`" x-model="row.kode_sukuk"
                                                    placeholder="SR019"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="text"
                                                    :name="`sukuk[${i}][nama_sukuk]`" x-model="row.nama_sukuk"
                                                    placeholder="Nama Sukuk"
                                                    class="w-36 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1">
                                                <select :name="`sukuk[${i}][jenis_sukuk]`" x-model="row.jenis_sukuk"
                                                    class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20 w-24">
                                                    <option value="">-</option>
                                                    <option value="Negara">Negara</option>
                                                    <option value="Korporasi">Korporasi</option>
                                                </select>
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`sukuk[${i}][bobot]`"
                                                    x-model="row.bobot" step="0.01"
                                                    class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`sukuk[${i}][yield]`"
                                                    x-model="row.yield" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="text"
                                                    :name="`sukuk[${i}][jatuh_tempo]`" x-model="row.jatuh_tempo"
                                                    placeholder="2028"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1">
                                                <select :name="`sukuk[${i}][rating]`" x-model="row.rating"
                                                    class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                                                    <option value="">-</option>
                                                    @foreach (['AAA', 'AA+', 'AA', 'AA-', 'A+', 'A', 'A-', 'BBB+', 'BBB', 'BBB-', 'BB', 'B', 'CCC', 'D'] as $r)
                                                        <option value="{{ $r }}">{{ $r }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`sukuk[${i}][persen_nab]`"
                                                    x-model="row.persen_nab" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><button type="button"
                                                    @click="removeRow('sukuk', i)"
                                                    class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Bank --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-primary text-sm">Bank</h4>
                            <button type="button" @click="addRow('bank')" class="text-xs text-primary hover:underline">+
                                Tambah Baris</button>
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
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Klasifikasi KBMI
                                        </th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in bank" :key="i">
                                        <tr>
                                            <td class="px-1 py-1"><input type="text" :name="`bank[${i}][nama_bank]`"
                                                    x-model="row.nama_bank" placeholder="Nama Bank"
                                                    class="w-28 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                                    @change.debounce.500ms="lookupBankData(i)" />
                                            </td>
                                            <td class="px-1 py-1">
                                                <select :name="`bank[${i}][jenis_bank]`" x-model="row.jenis_bank"
                                                    class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20 w-28">
                                                    <option value="">-</option>
                                                    <option value="Bank Nasional">Bank Nasional</option>
                                                    <option value="Bank Asing">Bank Asing</option>
                                                    <option value="BPD">BPD</option>
                                                    <option value="BPR">BPR</option>
                                                </select>
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`bank[${i}][bobot]`"
                                                    x-model="row.bobot" step="0.01"
                                                    class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                                    @input="hitungNilaiPasarBank(i)" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`bank[${i}][nilai_pasar]`"
                                                    x-model="row.nilai_pasar" step="0.01" readonly
                                                    class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`bank[${i}][tingkat_bunga]`"
                                                    x-model="row.tingkat_bunga" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="text"
                                                    :name="`bank[${i}][jangka_waktu]`"
                                                    x-model="row.jangka_waktu" placeholder="1 bln"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`bank[${i}][persen_nab]`"
                                                    x-model="row.persen_nab" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`bank[${i}][return_1m]`"
                                                    x-model="row.return_1m" step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`bank[${i}][return_3m]`"
                                                    x-model="row.return_3m" step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`bank[${i}][return_6m]`"
                                                    x-model="row.return_6m" step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`bank[${i}][return_1y]`"
                                                    x-model="row.return_1y" step="0.0001" readonly
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`bank[${i}][car]`"
                                                    x-model="row.car" step="0.01"
                                                    class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`bank[${i}][npl]`"
                                                    x-model="row.npl" step="0.01"
                                                    class="w-16 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1">
                                                <select :name="`bank[${i}][klasifikasi_risiko]`"
                                                    x-model="row.klasifikasi_risiko"
                                                    class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                                                    <option value="">-</option>
                                                    <option value="Rendah">Rendah</option>
                                                    <option value="Sedang">Sedang</option>
                                                    <option value="Tinggi">Tinggi</option>
                                                </select>
                                            </td>
                                            <td class="px-1 py-1"><button type="button" @click="removeRow('bank', i)"
                                                    class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
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
                            Download template Excel terlebih dahulu, isi data sesuai format, lalu upload kembali.
                            <a href="{{ $formRoutes['template'] }}" class="font-semibold underline ml-1">Download
                                Template</a>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="file_excel" value="Upload File Excel (.xlsx)" />
                        <input id="file_excel" name="file_excel" type="file" accept=".xlsx,.xls"
                            class="mt-1 block w-full text-sm text-muted border border-gray-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-primary file:text-white hover:file:bg-primary/90 cursor-pointer" />
                        <x-input-error :messages="$errors->get('file_excel')" class="mt-1" />
                        <p class="text-xs text-muted mt-1">Format: .xlsx atau .xls, maks 5MB. Sheet: Sektor, Efek, Kinerja,
                            Obligasi, Sukuk, Bank.</p>
                    </div>
                </div>

                {{-- TAB: PDF FFS --}}
                <div x-show="mode==='pdf'" class="p-6 space-y-5">
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
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <h4 class="font-medium text-sm text-primary">Dokumen Tersimpan</h4>
                        </div>
                        <p class="text-xs text-muted">Dokumen Prospektus dan FFS yang tersimpan di master data Reksa Dana. Isi Kode Reksa Dana terlebih dahulu untuk melihat dokumen tersedia.</p>

                        <div x-show="existingDocsLoading" class="flex items-center gap-2 text-xs text-muted">
                            <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span>Memuat dokumen...</span>
                        </div>

                        <template x-if="existingDocs.length > 0">
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                <template x-for="doc in existingDocs" :key="doc.id">
                                    <div class="flex items-center justify-between p-2.5 bg-white rounded-lg border border-gray-200">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                :class="doc.document_type === 'prospektus' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'"
                                                x-text="doc.document_type === 'prospektus' ? 'Prospektus' : 'FFS'"></span>
                                            <span class="text-sm font-medium truncate" x-text="doc.label"></span>
                                            <span class="text-xs text-muted shrink-0" x-text="doc.uploaded_at"></span>
                                        </div>
                                        <button type="button" @click="parseExistingDocument(doc.id)"
                                            :disabled="existingDocParsing"
                                            class="shrink-0 px-3 py-1 text-xs font-medium text-white bg-primary rounded-lg hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <span x-show="!existingDocParsing">Parse</span>
                                            <span x-show="existingDocParsing">Memproses...</span>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div x-show="existingDocsLoaded && existingDocs.length === 0" class="text-xs text-muted italic">
                            Tidak ada dokumen tersimpan untuk kode reksa dana ini.
                        </div>
                    </div>

                    {{-- Separator --}}
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-line"></div></div>
                        <div class="relative flex justify-center text-xs"><span class="bg-white px-2 text-muted">ATAU</span></div>
                    </div>

                    {{-- Upload PDF Baru --}}
                    <div>
                        <x-input-label for="file_pdf" value="Upload File PDF Baru" />
                        <div class="mt-1 flex items-center gap-3">
                            <input id="file_pdf" type="file" accept=".pdf" @change="parsePdf($event)"
                                class="block w-full text-sm text-muted border border-gray-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-primary file:text-white hover:file:bg-primary/90 cursor-pointer" />
                        </div>
                        <p class="text-xs text-muted mt-1">Format: PDF, maks 10MB. Data akan diekstrak dari Fund Fact
                            Sheet.</p>
                    </div>

                    <div class="flex flex-wrap gap-3 text-sm">
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

                    <div x-show="pdfLoading" class="flex items-center gap-2 text-sm text-muted">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                            </path>
                        </svg>
                        <span>Membaca PDF... harap tunggu.</span>
                    </div>

                    <div x-show="pdfResult" class="text-sm space-y-2">
                        <div
                            :class="pdfSuccess ? 'p-3 bg-green-50 border border-green-200 rounded-lg text-green-700' :
                                'p-3 bg-red-50 border border-red-200 rounded-lg text-red-700'">
                            <span x-text="pdfResult"></span>
                        </div>
                        <p x-show="pdfSuccess" class="text-xs text-muted">Silakan cek tab <strong>Input Manual</strong>
                            untuk melihat dan
                            mengedit data yang telah diekstrak.</p>
                    </div>
                </div>

                @include('analisa.partials.create-ai-tabs')
            </div>

            <input type="hidden" name="ai_narasi" :value="aiResult?.raw || ''">
            <input type="hidden" name="ai_output" :value="aiResult ? JSON.stringify(aiResult.parsed || {}) : ''">
            <input type="hidden" name="ai_narasi_plus" :value="aiPlusResult?.raw || ''">
            <input type="hidden" name="ai_output_plus"
                :value="aiPlusResult ? JSON.stringify(aiPlusResult.parsed || {}) : ''">

            <div class="flex items-center gap-3" x-show="mode !== 'link-website'" x-cloak>
                <button type="submit" name="simpan" value="1"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600 transition">Simpan</button>
                <x-primary-button>Submit Analisa</x-primary-button>
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
            function analisaForm() {
                @php
                    $oldSektor = old('sektor', [['nama_sektor' => '', 'bobot' => '']]);
                    $oldEfek = old('efek', [['kode_efek' => '', 'nama_efek' => '', 'sektor' => '', 'bobot' => '', 'kontribusi_kinerja' => '', 'market_cap' => '', 'nilai_pasar' => '', 'harga_perolehan' => '', 'persen_nab' => '', 'return_1m' => '', 'return_3m' => '', 'return_6m' => '', 'return_1y' => '', 'top_10' => false]]);
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

                return {
                    mode: @json(old('input_mode', request('tab') === 'link-website' ? 'link-website' : 'manual')),
                    webLoading: false,
                    webFile: null,
                    webMessage: '',
                    webOk: false,
                    parseWebFileUrl: @json($formRoutes['parse_web_file']),
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
                    parsePdfVisionUrl: @json($formRoutes['parse_pdf_vision']),
                    existingDocsUrl: @json($formRoutes['existing_documents']),
                    parseExistingDocUrl: @json($formRoutes['parse_existing_document']),
                    existingDocs: [],
                    existingDocsLoading: false,
                    existingDocsLoaded: false,
                    existingDocParsing: false,
                    plusRequiredLabels: @json($plusLabels),
                    lookupMessage: '',
                    lookupOk: false,
                    tanggalData: @json(old('tanggal_data')),
                    ffsBulan: @json(old('ffs_bulan')),
                    ffsTahun: @json(old('ffs_tahun', now()->year)),
                    jenisLaporan: @json(old('jenis_laporan', 'kalender_ffs')),
                    periodeAwal: @json(old('periode_awal')),
                    periodeAkhir: @json(old('periode_akhir')),
                    tahunLaporan: @json(old('tahun_laporan', now()->year)),
                    unitPenyertaan: @json(old('unit_penyertaan')),
                    nabPerUnit: @json(old('nab_per_unit')),
                    totalAum: @json(old('total_aum')),
                    totalMarcap10Efek: @json(old('total_marcap_10_efek')),
                    sektor: @json($oldSektor),
                    efek: @json($oldEfek),
                    kinerja: @json($oldKinerja),
                    obligasi: @json($oldObligasi),
                    sukuk: @json($oldSukuk),
                    bank: @json($oldBank),
                    alokasi_aset: @json($oldAlokasiAset),

                    manajerInvestasi: @json(old('manajer_investasi')),
                    bankKustodian: @json(old('bank_kustodian')),
                    tanggalPeluncuran: @json(old('tanggal_peluncuran')),
                    mataUang: @json(old('mata_uang')),
                    benchmark: @json(old('benchmark')),
                    tujuanInvestasi: @json(old('tujuan_investasi')),
                    kebijakanInvestasi: @json(old('kebijakan_investasi')),
                    returnYtd: @json(old('return_ytd')),
                    return1y: @json(old('return_1y')),
                    totalReturn: @json(old('total_return')),
                    biayaOperasi: @json(old('biaya_operasi')),
                    portfolioTurnover: @json(old('portfolio_turnover_ratio')),
                    managementFee: @json(old('management_fee')),
                    custodianFee: @json(old('custodian_fee')),
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
                    arusKasOperasi: @json(old('arus_kas_operasi')),
                    arusKasPendanaan: @json(old('arus_kas_pendanaan')),
                    kasAwalTahun: @json(old('kas_awal_tahun')),
                    kasAkhirTahun: @json(old('kas_akhir_tahun')),
                    totalHasilInvestasi: @json(old('total_hasil_investasi')),
                    hasilInvestasiSetelahBiaya: @json(old('hasil_investasi_setelah_biaya')),
                    persentasePph: @json(old('persentase_pph')),
                    fairValueLevel1: @json(old('fair_value_level_1')),
                    fairValueLevel2: @json(old('fair_value_level_2')),
                    fairValueLevel3: @json(old('fair_value_level_3')),
                    unitMilikInvestor: @json(old('unit_milik_investor')),
                    unitMilikMi: @json(old('unit_milik_mi')),
                    totalUnitBeredar: @json(old('total_unit_beredar')),

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
                                kontribusi_kinerja: '',
                                market_cap: '',
                                nilai_pasar: '',
                                harga_perolehan: '',
                                persen_nab: '',
                                return_1m: '',
                                return_3m: '',
                                return_6m: '',
                                return_1y: '',
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
                        return this.tanggalData || this.ffsDateValue();
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
                            fetch(this.lookupSektorUrl + '?kode_efek=' + encodeURIComponent(kode) + '&tanggal=' +
                                    encodeURIComponent(tanggal), {
                                        headers: {
                                            Accept: 'application/json'
                                        }
                                    })
                                .then(r => r.json())
                                .then(resp => {
                                    if (resp.found) this.efek[i].sektor = resp.sektor;
                                })
                                .catch(() => {});
                        }

                        if (tanggal && this.lookupIhsgUrl) {
                            fetch(this.lookupIhsgUrl + '?kode_efek=' + encodeURIComponent(kode) + '&tanggal=' +
                                    encodeURIComponent(tanggal), {
                                        headers: {
                                            Accept: 'application/json'
                                        }
                                    })
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
                            fetch(this.lookupReturnUrl + '?kode_efek=' + encodeURIComponent(kode) + '&tanggal=' +
                                    encodeURIComponent(tanggal), {
                                        headers: {
                                            Accept: 'application/json'
                                        }
                                    })
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

                        fetch(this.lookupBondReturnUrl + '?kode_obligasi=' + encodeURIComponent(kode) + '&tanggal=' +
                                encodeURIComponent(tanggal), {
                                    headers: {
                                        Accept: 'application/json'
                                    }
                                })
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
                        const tanggal = this.tanggalDataValue();
                        if (!nama || !tanggal || !this.lookupBankDataUrl) return;

                        fetch(this.lookupBankDataUrl + '?nama_bank=' + encodeURIComponent(nama) + '&tanggal=' +
                                encodeURIComponent(tanggal), {
                                    headers: {
                                        Accept: 'application/json'
                                    }
                                })
                            .then(r => r.json())
                            .then(resp => {
                                if (resp.found) {
                                    if (resp.car !== null && resp.car !== undefined) this.bank[i].car = resp.car;
                                    if (resp.npl !== null && resp.npl !== undefined) this.bank[i].npl = resp.npl;
                                    if (resp.klasifikasi_risiko) this.bank[i].klasifikasi_risiko = resp.klasifikasi_risiko;
                                }
                            })
                            .catch(() => {});
                    },

                    analisaFormEl() {
                        return this.$root.querySelector('#analisa-form') || this.$root.querySelector('form');
                    },

                    buildFormPayload() {
                        const fd = new FormData(this.analisaFormEl());
                        const payload = new FormData();
                        ['kode_reksa_dana', 'nama_reksa_dana', 'jenis_reksa_dana', 'benchmark', 'tujuan_investasi',
                            'kebijakan_investasi', 'total_aum', 'unit_penyertaan', 'nab_per_unit',
                            'total_marcap_10_efek', 'tanggal_data', 'ffs_bulan', 'ffs_tahun'
                        ].forEach(
                            k => {
                                const val = fd.get(k) ?? '';
                                // Fallback ke pdfData jika form kosong
                                payload.append(k, val || (this.pdfData?.[k] ?? ''));
                            });
                        for (const [key, val] of fd.entries()) {
                            if (key === 'kategori[]' || key.startsWith('kategori[') || key.startsWith('sektor[') || key
                                .startsWith('efek[') || key.startsWith('kinerja[') || key.startsWith('obligasi[') || key
                                .startsWith('sukuk[') || key.startsWith('alokasi_aset[') || key
                                .startsWith('bank[')) {
                                payload.append(key, val);
                            }
                        }
                        if (![...payload.keys()].some(k => k === 'kategori[]' || k.startsWith('kategori[')) && this.pdfData
                            ?.kategori?.length) {
                            this.pdfData.kategori.forEach(v => payload.append('kategori[]', v));
                        }
                        // Inject pdfData untuk field yang kosong (cek nilai, bukan hanya key)
                        if (this.pdfData) {
                            const arrayFields = ['sektor', 'efek', 'kinerja', 'obligasi', 'sukuk', 'bank', 'alokasi_aset'];
                            arrayFields.forEach(field => {
                                const hasRealData = [...payload.entries()]
                                    .some(([k, v]) => k.startsWith(field + '[') && String(v).trim() !== '');
                                if (!hasRealData && this.pdfData[field]?.length) {
                                    this.pdfData[field].forEach((row, i) => {
                                        Object.entries(row).forEach(([k, v]) => {
                                            if (v !== null && v !== undefined && String(v).trim() !==
                                                '') {
                                                payload.append(`${field}[${i}][${k}]`, v);
                                            }
                                        });
                                    });
                                }
                            });
                        }
                        payload.append('_token', fd.get('_token'));
                        return payload;
                    },

                    validateAiBasics(fd) {
                        const nama = String(fd.get('nama_reksa_dana') || '').trim();
                        // Lolos jika ada nama di form ATAU ada pdfData
                        if (!nama && !this.pdfData) {
                            return 'Isi Nama Reksa Dana di bagian Informasi Reksa Dana (atas form) terlebih dahulu, atau upload PDF FFS.';
                        }
                        return null;
                    },

                    parseJsonError(resp) {
                        if (resp.errors) {
                            return Object.values(resp.errors).flat().join(' ');
                        }
                        return resp.message || 'Gagal memproses';
                    },

                    lookupReksaDana(kode) {
                        kode = String(kode || '').trim();
                        if (!this.lookupKodeUrl || kode.length < 2) {
                            this.lookupMessage = '';
                            this.lookupOk = false;
                            return;
                        }

                        fetch(`${this.lookupKodeUrl}?kode_reksa_dana=${encodeURIComponent(kode)}`, {
                                headers: {
                                    Accept: 'application/json'
                                }
                            })
                            .then(res => res.json())
                            .then(resp => {
                                if (!resp.found) {
                                    this.lookupOk = false;
                                    this.lookupMessage = 'Kode belum ditemukan di master data atau analisa sebelumnya.';
                                    return;
                                }

                                const data = {
                                    ...(resp.master || {}),
                                    ...(resp.last_analisa || {}),
                                };
                                this.applyLookupData(data);
                                this.lookupOk = true;
                                this.lookupMessage = resp.last_analisa ?
                                    'Data analisa terakhir berhasil dimuat.' :
                                    'Data master reksa dana berhasil dimuat.';
                                this.fetchExistingDocuments(kode);
                            })
                            .catch(() => {
                                this.lookupOk = false;
                                this.lookupMessage = 'Gagal mencari data kode reksa dana.';
                            });
                    },

                    applyLookupData(data) {
                        this.setFieldValue('nama_reksa_dana', data.nama_reksa_dana);
                        this.setFieldValue('jenis_reksa_dana', data.jenis_reksa_dana);
                        this.setFieldValue('benchmark', data.benchmark);
                        this.setFieldValue('tujuan_investasi', data.tujuan_investasi);
                        this.setFieldValue('kebijakan_investasi', data.kebijakan_investasi);
                        this.totalAum = data.total_aum ?? this.totalAum;
                        this.totalMarcap10Efek = data.total_marcap_10_efek ?? this.totalMarcap10Efek;
                        this.unitPenyertaan = data.unit_penyertaan ?? this.unitPenyertaan;
                        this.nabPerUnit = data.nab_per_unit ?? this.nabPerUnit;
                        this.tanggalData = data.tanggal_data ?? this.tanggalData;
                        this.ffsBulan = data.ffs_bulan ?? this.ffsBulan;
                        this.ffsTahun = data.ffs_tahun ?? this.ffsTahun;
                        this.applyKategori(data.kategori || []);
                        if (data.sektor?.length) this.sektor = data.sektor;
                        if (data.efek?.length) {
                            this.efek = data.efek.map(e => ({
                                kode_efek: e.kode_efek || '',
                                nama_efek: e.nama_efek || '',
                                sektor: e.sektor || '',
                                bobot: e.bobot ?? '',
                                kontribusi_kinerja: e.kontribusi_kinerja ?? '',
                                market_cap: e.market_cap ?? '',
                                nilai_pasar: e.nilai_pasar ?? '',
                                return_1m: e.return_1m ?? '',
                                return_3m: e.return_3m ?? '',
                                return_6m: e.return_6m ?? '',
                                return_1y: e.return_1y ?? '',
                                ihsg_contribution: e.ihsg_contribution ?? '',
                                effect_type: e.effect_type || 'Saham',
                                top_10: e.top_10 === true || e.top_10 === 'Ya',
                            }));
                            this.$nextTick(() => {
                                this.efek.forEach((_, i) => {
                                    this.hitungNilaiPasarEfek(i);
                                    setTimeout(() => this.lookupEfekData(i), i * 600);
                                });
                            });
                        }
                        if (data.kinerja?.length) this.kinerja = data.kinerja;
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
                        if (data.alokasi_aset?.length) this.alokasi_aset = data.alokasi_aset;
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
                        this.totalAum = p.total_aum || pdf.total_aum || this.totalAum;
                        this.totalMarcap10Efek = p.total_marcap_10_efek || pdf.total_marcap_10_efek || this.totalMarcap10Efek;
                        this.tanggalData = p.tanggal_data || pdf.tanggal_data || this.tanggalData;
                        this.unitPenyertaan = p.unit_penyertaan || pdf.unit_penyertaan || this.unitPenyertaan;
                        this.nabPerUnit = p.nab_per_unit || pdf.nab_per_unit || this.nabPerUnit;
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
                                    kontribusi_kinerja: e.kontribusi_kinerja ?? '',
                                    market_cap: e.market_cap ?? '',
                                    nilai_pasar: '',
                                    return_1m: '',
                                    return_3m: '',
                                    return_6m: '',
                                    return_1y: '',
                                    effect_type: 'Saham',
                                    top_10: i < 10,
                                }));
                        } else if (pdf.efek?.length) {
                            this.efek = pdf.efek.map((e, i) => ({
                                kode_efek: e.kode_efek || '',
                                nama_efek: e.nama_efek || '',
                                sektor: e.sektor || '',
                                bobot: e.bobot ?? '',
                                kontribusi_kinerja: e.kontribusi_kinerja ?? '',
                                market_cap: e.market_cap ?? '',
                                nilai_pasar: e.nilai_pasar ?? '',
                                return_1m: e.return_1m ?? '',
                                return_3m: e.return_3m ?? '',
                                return_6m: e.return_6m ?? '',
                                return_1y: e.return_1y ?? '',
                                effect_type: e.effect_type || 'Saham',
                                top_10: i < 10,
                            }));
                        }

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

                        this.mode = 'manual';
                        alert('Data telah diterapkan ke Input Manual. Silakan review sebelum submit.');
                    },

                    normalizeExtractedData(data) {
                        data = data || {};
                        if (data.tanggal_data && (!data.ffs_bulan || !data.ffs_tahun)) {
                            const d = new Date(`${data.tanggal_data}T00:00:00`);
                            if (!Number.isNaN(d.getTime())) {
                                data.ffs_bulan = data.ffs_bulan || d.getMonth() + 1;
                                data.ffs_tahun = data.ffs_tahun || d.getFullYear();
                            }
                        }

                        if (!Array.isArray(data.alokasi_aset)) {
                            data.alokasi_aset = [];
                        }
                        data.alokasi_aset = data.alokasi_aset.map(row => ({
                            nama_aset: row.nama_aset || row.nama || row.kategori || '',
                            persentase: row.persentase ?? row.bobot ?? '',
                        })).filter(row => String(row.nama_aset || '').trim() !== '' || String(row.persentase || '')
                            .trim() !== '');

                        return data;
                    },

                    hasFullInputData(data) {
                        data = this.normalizeExtractedData({
                            ...data
                        });
                        return Boolean(
                            data.total_aum ||
                            data.total_marcap_10_efek ||
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
                        this.totalAum = data.total_aum ?? this.totalAum;
                        this.totalMarcap10Efek = data.total_marcap_10_efek ?? this.totalMarcap10Efek;
                        this.tanggalData = data.tanggal_data ?? this.tanggalData;
                        this.unitPenyertaan = data.unit_penyertaan ?? this.unitPenyertaan;
                        this.nabPerUnit = data.nab_per_unit ?? this.nabPerUnit;
                        this.ffsBulan = data.ffs_bulan ?? this.ffsBulan;
                        this.ffsTahun = data.ffs_tahun ?? this.ffsTahun;
                        this.returnYtd = data.return_ytd ?? this.returnYtd;
                        this.return1y = data.return_1y ?? this.return1y;
                        this.totalReturn = data.total_return ?? this.totalReturn;
                        this.biayaOperasi = data.biaya_operasi ?? this.biayaOperasi;
                        this.portfolioTurnoverRatio = data.portfolio_turnover_ratio ?? this.portfolioTurnoverRatio;
                        this.managementFee = data.management_fee ?? this.managementFee;
                        this.custodianFee = data.custodian_fee ?? this.custodianFee;
                        this.totalAset = data.total_aset ?? this.totalAset;
                        this.totalLiabilitas = data.total_liabilitas ?? this.totalLiabilitas;
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
                        this.arusKasOperasi = data.arus_kas_operasi ?? this.arusKasOperasi;
                        this.arusKasPendanaan = data.arus_kas_pendanaan ?? this.arusKasPendanaan;
                        this.kasAwalTahun = data.kas_awal_tahun ?? this.kasAwalTahun;
                        this.kasAkhirTahun = data.kas_akhir_tahun ?? this.kasAkhirTahun;
                        this.totalHasilInvestasi = data.total_hasil_investasi ?? this.totalHasilInvestasi;
                        this.hasilInvestasiSetelahBiaya = data.hasil_investasi_setelah_biaya ?? this.hasilInvestasiSetelahBiaya;
                        this.persentasePph = data.persentase_pph ?? this.persentasePph;
                        this.fairValueLevel1 = data.fair_value_level_1 ?? this.fairValueLevel1;
                        this.fairValueLevel2 = data.fair_value_level_2 ?? this.fairValueLevel2;
                        this.fairValueLevel3 = data.fair_value_level_3 ?? this.fairValueLevel3;
                        this.unitMilikInvestor = data.unit_milik_investor ?? this.unitMilikInvestor;
                        this.unitMilikMi = data.unit_milik_mi ?? this.unitMilikMi;
                        this.totalUnitBeredar = data.total_unit_beredar ?? this.totalUnitBeredar;
                        if (data.sektor?.length) this.sektor = data.sektor;
                        if (data.efek?.length) {
                            this.efek = data.efek.map(e => ({
                                kode_efek: e.kode_efek || '',
                                nama_efek: e.nama_efek || '',
                                sektor: e.sektor || '',
                                bobot: e.bobot ?? '',
                                kontribusi_kinerja: e.kontribusi_kinerja ?? '',
                                market_cap: e.market_cap ?? '',
                                nilai_pasar: e.nilai_pasar ?? '',
                                harga_perolehan: e.harga_perolehan ?? '',
                                persen_nab: e.persen_nab ?? '',
                                return_1m: e.return_1m ?? '',
                                return_3m: e.return_3m ?? '',
                                return_6m: e.return_6m ?? '',
                                return_1y: e.return_1y ?? '',
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
                                this.webOk = true;
                                this.webMessage = body.message +
                                    ' Data sudah di tab Input Manual — lengkapi Jenis RD jika perlu, lalu Submit Analisa.';
                            })
                            .catch(() => {
                                this.webLoading = false;
                                this.webOk = false;
                                this.webMessage = 'Gagal memproses file.';
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
                                this.webOk = true;
                                this.webMessage = body.message +
                                    ' Data sudah di tab Input Manual — lengkapi Jenis RD jika perlu, lalu Submit Analisa.';
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
                                this.webOk = true;
                                this.webMessage = body.message +
                                    ' Data sudah di tab Input Manual — lengkapi Jenis RD jika perlu, lalu Submit Analisa.';
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

                        const url = this.pdfScanMode === 'vision' && this.parsePdfVisionUrl ?
                            this.parsePdfVisionUrl :
                            @json($formRoutes['parse_pdf']);

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
                                // Simpan data PDF dan isi state form sebanyak data yang tersedia.
                                const extractedData = this.normalizeExtractedData(resp.data || {});
                                this.pdfData = extractedData;
                                this.pdfFile = resp.pdf_file || '';
                                this.applyExtractedData(extractedData, this.hasFullInputData(extractedData) ? 'lengkap' :
                                    'manual');
                                this.pdfSuccess = true;
                                this.pdfResult = resp.message;
                            })
                            .catch(err => {
                                this.pdfLoading = false;
                                this.pdfSuccess = false;
                                this.pdfResult = 'Gagal: ' + err.message;
                            });
                    },

                    fetchExistingDocuments(kode) {
                        if (!this.existingDocsUrl || !kode) return;
                        this.existingDocsLoading = true;
                        this.existingDocsLoaded = false;
                        this.existingDocs = [];

                        fetch(`${this.existingDocsUrl}?kode_reksa_dana=${encodeURIComponent(kode)}`, {
                                headers: { Accept: 'application/json' }
                            })
                            .then(res => res.json())
                            .then(resp => {
                                this.existingDocsLoading = false;
                                this.existingDocsLoaded = true;
                                if (resp.found && Array.isArray(resp.documents)) {
                                    this.existingDocs = resp.documents;
                                }
                            })
                            .catch(() => {
                                this.existingDocsLoading = false;
                                this.existingDocsLoaded = true;
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
                                body: JSON.stringify({ document_id: documentId }),
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
                                this.applyExtractedData(extractedData, this.hasFullInputData(extractedData) ? 'lengkap' : 'manual');
                                this.pdfSuccess = true;
                                this.pdfResult = resp.message + ' (dari: ' + (resp.document_label || 'dokumen tersimpan') + ')';
                            })
                            .catch(err => {
                                this.existingDocParsing = false;
                                this.pdfSuccess = false;
                                this.pdfResult = 'Gagal: ' + err.message;
                            });
                    },
                };
            }
        </script>
    @endpush
@endsection
