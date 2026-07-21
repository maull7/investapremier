@extends($layout ?? 'layouts.user')

@section('content')
    <div class="max-w-5xl" x-data="lapkeuForm('{{ $previewAiRoute }}', '{{ $previewAiPlusRoute }}', '{{ $parsePdfRoute }}', '{{ $parsePdfVisionRoute }}', '{{ $parsePdfStatusRoute }}', '{{ $lookupKeuanganEmitenRoute }}', '{{ $resolveAiPlusDataRoute }}')">
        <div class="mb-6">
            <h1 class="page-title">Submit Analisa {{ $productLabel }}</h1>
            <p class="page-sub">Isi data laporan keuangan obligasi secara manual atau upload Excel</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="lapkeu-form" method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" class="space-y-6" novalidate>
            @csrf
            <input type="hidden" name="input_mode" :value="(mode === 'ai' || mode === 'ai-plus' || mode === 'lengkap' || mode === 'pdf') ? 'manual' : mode">
            <input type="hidden" name="ai_narasi" :value="aiResult?.raw || ''">
            <input type="hidden" name="ai_output" :value="aiResult ? JSON.stringify(aiResult.parsed || {}) : ''">
            <input type="hidden" name="ai_narasi_plus" :value="aiPlusResult?.raw || ''">
            <input type="hidden" name="ai_output_plus"
                :value="aiPlusResult ? JSON.stringify(aiPlusResult.parsed || {}) : ''">
            <input type="hidden" name="pdf_lapkeu_path" x-model="pdfPath">
            <input type="hidden" name="jenis_analisa" :value="jenisAnalisa">
            <input type="hidden" name="periode" :value="jenisAnalisa === 'periode' ? periodeAnalisa : ''">
            <input type="hidden" name="tahun" :value="jenisAnalisa === 'tahunan' ? tahunAnalisa : ''">
            <input type="hidden" name="financial_data_sources" :value="JSON.stringify(financialDataSources)">

            {{-- Info Dasar --}}
            <div class="bg-white rounded-xl border border-line p-6 space-y-4">
                <h3 class="font-semibold text-primary">Informasi Obligasi</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Obligasi</label>
                        <input type="text" name="nama_obligasi" id="nama_obligasi" x-model="namaObligasiSearch"
                            @input.debounce.300ms="if (namaObligasiSearch.length > 0) { lookupObligasi(namaObligasiSearch).then(r => nameResults = r) } else { nameResults = [] }"
                            @blur="if (namaObligasiSearch.length > 0) { lookupObligasi(namaObligasiSearch).then(list => { if (list.length > 0) { selectObligasi(list[0]); nameResults = [] } }) }"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        @error('nama_obligasi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div x-show="nameResults.length > 0" @click.outside="nameResults = []"
                            class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="b in nameResults" :key="b.id">
                                <button type="button" @click="selectObligasi(b); nameResults = []"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm border-b border-gray-100 last:border-0">
                                    <span class="font-semibold" x-text="b.nama_obligasi"></span>
                                    <span class="text-gray-500" x-text="' (' + b.kode + ')'"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Obligasi</label>
                        <input type="text" name="kode_obligasi" x-model="kodeObligasi" placeholder="cth: FR0070"
                            @input.debounce.300ms="if (kodeObligasi.length > 0) { lookupObligasi(kodeObligasi).then(r => codeResults = r) } else { codeResults = [] }"
                            @blur="if (kodeObligasi.length > 0) { lookupObligasi(kodeObligasi).then(list => { if (list.length > 0) { selectObligasi(list[0]); codeResults = [] } }) }"
                            value="{{ old('kode_obligasi') }}"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm uppercase">
                        <div x-show="codeResults.length > 0" @click.outside="codeResults = []"
                            class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="b in codeResults" :key="b.id">
                                <button type="button" @click="selectObligasi(b); codeResults = []"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm border-b border-gray-100 last:border-0">
                                    <span class="font-semibold" x-text="b.kode"></span>
                                    <span class="text-gray-500" x-text="' - ' + b.nama_obligasi"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Emiten</label>
                        <input type="text" name="nama_emiten" x-model="namaEmiten" value="{{ old('nama_emiten') }}"
                            placeholder="cth: PT Pertamina (Persero)"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rating Pefindo</label>
                        <select name="rating"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                            <option value="">Pilih Rating</option>
                            @foreach (['AAA', 'AA+', 'AA', 'AA-', 'A+', 'A', 'A-', 'BBB+', 'BBB', 'BBB-', 'BB+', 'BB', 'BB-', 'B+', 'B', 'B-', 'CCC', 'D', 'idAAA', 'idAA+', 'idAA', 'idAA-', 'idA+', 'idA', 'idA-', 'idBBB+', 'idBBB', 'idBBB-'] as $r)
                                <option value="{{ $r }}" {{ old('rating') === $r ? 'selected' : '' }}>
                                    {{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kupon (%)</label>
                        <input type="number" name="kupon" step="0.0001" value="{{ old('kupon') }}"
                            placeholder="cth: 7.5"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">YTM (%)</label>
                        <input type="number" name="ytm" step="0.0001" value="{{ old('ytm') }}"
                            placeholder="cth: 7.2"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mata Uang</label>
                        <select name="mata_uang"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                            @foreach (['IDR', 'USD', 'EUR', 'SGD'] as $c)
                                <option value="{{ $c }}"
                                    {{ old('mata_uang', 'IDR') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sektor</label>
                        <select name="sektor"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                            <option value="">Pilih Sektor</option>
                            @foreach (['Perbankan', 'Keuangan', 'Consumer Goods', 'Energi', 'Infrastruktur', 'Industri Dasar', 'Perkebunan', 'Properti', 'Teknologi', 'Transportasi', 'Telekomunikasi', 'Pertambangan', 'Lainnya'] as $s)
                                <option value="{{ $s }}" {{ old('sektor') === $s ? 'selected' : '' }}>
                                    {{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Penerbit</label>
                        <input type="number" name="nominal_penerbit" step="0.01" value="{{ old('nominal_penerbit') }}"
                            placeholder="cth: 1000000000"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Terbit</label>
                        <input type="date" name="tanggal_terbit" value="{{ old('tanggal_terbit') }}"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Jatuh Tempo</label>
                        <input type="date" name="tanggal_jatuh_tempo" value="{{ old('tanggal_jatuh_tempo') }}"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                    </div>
                    <div class="flex items-center gap-6">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="tanpa_jaminan" value="1" {{ old('tanpa_jaminan') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary focus:ring-primary/20">
                            <span class="text-sm text-gray-700">Tanpa Jaminan</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="dengan_jaminan" value="1" {{ old('dengan_jaminan') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary focus:ring-primary/20">
                            <span class="text-sm text-gray-700">Dengan Jaminan</span>
                        </label>
                    </div>
                    <div class="flex flex-row items-center gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Periode Dari Tahun</label>
                            <input type="number" name="periode_dari" value="{{ old('periode_dari') }}"
                                placeholder="cth: 2024"
                                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tahun</label>
                            <input type="number" name="periode_sampai" value="{{ old('periode_sampai') }}"
                                placeholder="cth: 2025"
                                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rating & Tenor Information --}}
            <div class="bg-white rounded-xl border border-line p-6 space-y-4">
                <h3 class="font-semibold text-primary">Rating Information</h3>
                <p class="text-xs text-muted">Shadow Rating dihitung otomatis dari data keuangan. Jika tidak ada Official Rating, Shadow Rating digunakan sebagai dasar YTM Spread.</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Official Rating</label>
                        <select name="official_rating"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                            <option value="">Pilih Official Rating</option>
                            @php
                                $officialRatings = \App\Models\RatingObligasi::orderBy('urutan')->orderBy('kode')->get();
                            @endphp
                            @foreach ($officialRatings as $r)
                                <option value="{{ $r->kode }}" {{ old('official_rating') === $r->kode ? 'selected' : '' }}>
                                    {{ $r->kode }} - {{ $r->nama }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-muted mt-1">Rating dari lembaga pemeringkat</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tenor (Bulan)</label>
                        <input type="number" name="tenor_bulan" value="{{ old('tenor_bulan') }}"
                            min="1" placeholder="cth: 60"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        <p class="text-xs text-muted mt-1">Sisa tenor obligasi dalam bulan</p>
                    </div>
                    <div class="flex items-end pb-1">
                        <div class="text-xs text-muted space-y-1">
                            <p>Shadow Rating: <span class="font-medium text-primary">Otomatis</span></p>
                            <p>YTM Spread: <span class="font-medium text-primary">Otomatis</span></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs mode input --}}
            <div class="table-card">
                <div class="flex border-b border-line overflow-x-auto">
                    <button type="button" @click="mode='manual'"
                        :class="mode === 'manual' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition whitespace-nowrap">Input Manual</button>
                    <button type="button" @click="mode='lengkap'"
                        :class="mode === 'lengkap' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition whitespace-nowrap">Input Lengkap</button>
                    <button type="button" @click="mode='excel'"
                        :class="mode === 'excel' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition whitespace-nowrap">Upload Excel</button>
                    <button type="button" @click="mode='ai'"
                        :class="mode === 'ai' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition whitespace-nowrap">Analisa AI</button>
                    <button type="button" @click="mode='ai-plus'"
                        :class="mode === 'ai-plus' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition whitespace-nowrap">Analisa AI Plus</button>
                    <button type="button" @click="mode='pdf'"
                        :class="mode === 'pdf' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition whitespace-nowrap">PDF Lapkeu</button>
                </div>

                {{-- TAB: MANUAL --}}
                <div x-show="mode==='manual'" class="p-6 space-y-6">
                    @include('analisa-obligasi.partials.form-informasi-obligasi')
                    @include('analisa-obligasi.partials.form-neraca')
                    @include('analisa-obligasi.partials.form-laba-rugi')
                    @include('analisa-obligasi.partials.form-arus-kas')
                </div>

                {{-- TAB: LENGKAP --}}
                <div x-show="mode==='lengkap'" class="p-6 space-y-6">

                    @include('analisa-obligasi.partials.form-informasi-obligasi-lengkap')

                    {{-- Neraca (Balance Sheet) --}}
                    <div class="bg-white rounded-xl border border-line overflow-hidden">
                        <div class="px-5 py-4 border-b border-line">
                            <h4 class="font-semibold text-primary">Neraca (Balance Sheet) <span
                                    class="text-xs font-normal text-muted">— dalam juta Rupiah (atau sesuai mata
                                    uang)</span></h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse">
                                <thead class="bg-gray-50 text-muted text-xs">
                                    <tr>
                                        <th class="px-3 py-2 text-left border-b border-line w-1/2">Akun</th>
                                        <th class="px-3 py-2 text-right border-b border-line w-1/2">Nilai (dalam juta)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line text-xs">
                                    <tr class="bg-gray-50/50">
                                        <td colspan="2" class="px-3 py-2 font-semibold text-muted">Aset Lancar</td>
                                    </tr>
                                    @foreach ([['cash_equivalents', 'Kas & Setara Kas'], ['account_receivable', 'Piutang Usaha'], ['inventories', 'Persediaan'], ['other_current_asset', 'Aset Lancar Lainnya'], ['current_asset', 'Total Aset Lancar']] as [$name, $label])
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-3 py-1.5 {{ in_array($name, ['current_asset']) ? 'font-semibold' : '' }}">{{ $label }}</td>
                                            <td class="px-2 py-1.5"><input type="number" name="{{ $name }}"
                                                    x-model="{{ $name }}" step="0.01"
                                                    value="{{ old($name) }}"
                                                    class="w-full border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded {{ in_array($name, ['current_asset']) ? 'font-semibold' : '' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-gray-50/50">
                                        <td colspan="2" class="px-3 py-2 font-semibold text-muted">Aset Tidak Lancar</td>
                                    </tr>
                                    @foreach ([['fixed_asset', 'Aset Tetap'], ['other_non_current_asset', 'Aset Tidak Lancar Lainnya'], ['total_asset', 'Total Aset']] as [$name, $label])
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-3 py-1.5 {{ in_array($name, ['total_asset']) ? 'font-semibold' : '' }}">{{ $label }}</td>
                                            <td class="px-2 py-1.5"><input type="number" name="{{ $name }}"
                                                    x-model="{{ $name }}" step="0.01"
                                                    value="{{ old($name) }}"
                                                    class="w-full border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded {{ in_array($name, ['total_asset']) ? 'font-semibold' : '' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-gray-50/50">
                                        <td colspan="2" class="px-3 py-2 font-semibold text-muted">Liabilitas Jangka Pendek</td>
                                    </tr>
                                    @foreach ([['account_payable', 'Utang Usaha'], ['accruals', 'Akrual'], ['short_term_loans', 'Pinjaman Jangka Pendek'], ['current_maturities_of_long_term_loans', 'Bagian Lancar Utang JK Panjang'], ['other_current_liabilities', 'Liabilitas Lancar Lainnya'], ['current_liabilities', 'Total Liabilitas Lancar']] as [$name, $label])
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-3 py-1.5 {{ in_array($name, ['current_liabilities']) ? 'font-semibold' : '' }}">{{ $label }}</td>
                                            <td class="px-2 py-1.5"><input type="number" name="{{ $name }}"
                                                    x-model="{{ $name }}" step="0.01"
                                                    value="{{ old($name) }}"
                                                    class="w-full border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded {{ in_array($name, ['current_liabilities']) ? 'font-semibold' : '' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-gray-50/50">
                                        <td colspan="2" class="px-3 py-2 font-semibold text-muted">Liabilitas JK Panjang & Ekuitas</td>
                                    </tr>
                                    @foreach ([['long_term_loans', 'Pinjaman Jangka Panjang'], ['other_non_current_liabilities', 'Liabilitas Tidak Lancar Lainnya'], ['total_non_current_liabilities', 'Total Liabilitas Tidak Lancar'], ['total_liabilities', 'Total Liabilitas'], ['retained_earning', 'Saldo Laba'], ['equity', 'Total Ekuitas'], ['share_capital', 'Modal Saham'], ['additional_paid_in_capital', 'Tambahan Modal Disetor'], ['others', 'Komponen Ekuitas Lain'], ['non_controlling_interest', 'Kepentingan Non-Pengendali'], ['total_equity_equity_to_parent_entity', 'Ekuitas ke Entitas Induk']] as [$name, $label])
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-3 py-1.5 {{ in_array($name, ['total_liabilities', 'equity']) ? 'font-semibold' : '' }}">{{ $label }}</td>
                                            <td class="px-2 py-1.5"><input type="number" name="{{ $name }}"
                                                    x-model="{{ $name }}" step="0.01"
                                                    value="{{ old($name) }}"
                                                    class="w-full border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded {{ in_array($name, ['total_liabilities', 'equity']) ? 'font-semibold' : '' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Laba Rugi (Income Statement) --}}
                    <div class="bg-white rounded-xl border border-line overflow-hidden">
                        <div class="px-5 py-4 border-b border-line">
                            <h4 class="font-semibold text-primary">Laba Rugi (Income Statement)</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse">
                                <thead class="bg-gray-50 text-muted text-xs">
                                    <tr>
                                        <th class="px-3 py-2 text-left border-b border-line w-1/2">Akun</th>
                                        <th class="px-3 py-2 text-right border-b border-line w-1/2">Nilai (dalam juta)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line text-xs">
                                    @foreach ([['net_revenue', 'Pendapatan Bersih'], ['cost_of_good_sold', 'Beban Pokok Penjualan'], ['gross_income', 'Laba Kotor'], ['operational_expense', 'Beban Operasional'], ['laba_operasional', 'Laba Operasional'], ['interest_expense', 'Beban Bunga'], ['other_income_expense', 'Pendapatan/Beban Lain-lain'], ['income_before_tax', 'Laba Sebelum Pajak'], ['ebit', 'EBIT'], ['taxes', 'Pajak Penghasilan'], ['ebitda', 'EBITDA'], ['net_income_attributable_to_non_controlling_interest', 'NCI Net Income'], ['net_income', 'Laba Bersih'], ['eps', 'EPS / Laba per Saham']] as [$name, $label])
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-3 py-1.5 {{ in_array($name, ['gross_income', 'ebit', 'ebitda', 'net_income', 'eps']) ? 'font-semibold' : '' }}">{{ $label }}</td>
                                            <td class="px-2 py-1.5"><input type="number" name="{{ $name }}"
                                                    x-model="{{ $name }}" step="0.01"
                                                    value="{{ old($name) }}"
                                                    class="w-full border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded {{ in_array($name, ['gross_income', 'ebit', 'ebitda', 'net_income', 'eps']) ? 'font-semibold' : '' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Arus Kas (Cash Flow Statement) --}}
                    <div class="bg-white rounded-xl border border-line overflow-hidden">
                        <div class="px-5 py-4 border-b border-line">
                            <h4 class="font-semibold text-primary">Arus Kas (Cash Flow Statement)</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse">
                                <thead class="bg-gray-50 text-muted text-xs">
                                    <tr>
                                        <th class="px-3 py-2 text-left border-b border-line w-1/2">Akun</th>
                                        <th class="px-3 py-2 text-right border-b border-line w-1/2">Nilai (dalam juta)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line text-xs">
                                    @foreach ([['cash_flows_operating_activities', 'Arus Kas dari Operasi'], ['cash_flows_investment', 'Arus Kas dari Investasi'], ['cash_flows_financing', 'Arus Kas dari Pendanaan']] as [$name, $label])
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-3 py-1.5">{{ $label }}</td>
                                            <td class="px-2 py-1.5"><input type="number" name="{{ $name }}"
                                                    x-model="{{ $name }}" step="0.01"
                                                    value="{{ old($name) }}"
                                                    class="w-full border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Data Saham --}}
                    <div class="bg-white rounded-xl border border-line overflow-hidden">
                        <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-primary">Data Saham</h4>
                                <p class="text-xs text-muted mt-0.5">Rasio keuangan emiten saham pembanding.</p>
                            </div>
                            <button type="button" @click="keuanganSaham.push({kode_efek:'',nama_efek:'',per:'',pbv:'',roe:'',roa:'',npm:'',ev_ebitda:'',der:'',current_ratio:'',aktivitas_lancar:'',gross_profit_margin:'',operating_profit_margin:''})"
                                class="text-xs px-3 py-1.5 border border-line rounded-lg hover:bg-gray-50">+ Tambah</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse">
                                <thead class="bg-gray-50 text-muted text-xs">
                                    <tr>
                                        <th class="px-3 py-2 text-left whitespace-nowrap border-b border-line">Daftar Efek</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">PER</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">PBV</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">ROE</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">ROA</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">NPM</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">EV/EBITDA</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">DER</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Current Ratio</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Aktivitas Lancar</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Gross Profit Margin</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Operating Profit Margin</th>
                                        <th class="px-3 py-2 text-center whitespace-nowrap border-b border-line"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(item, index) in keuanganSaham" :key="index">
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-2 py-1.5 whitespace-nowrap">
                                                <input type="text" x-model="item.kode_efek" :name="`keuangan_saham[${index}][kode_efek]`" class="w-16 border-0 bg-transparent text-sm px-1 py-1 focus:outline-none focus:ring-1 focus:ring-primary/30 rounded" placeholder="Kode">
                                                <span class="text-muted mx-0.5">-</span>
                                                <input type="text" x-model="item.nama_efek" :name="`keuangan_saham[${index}][nama_efek]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 focus:outline-none focus:ring-1 focus:ring-primary/30 rounded" placeholder="Nama">
                                            </td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.per" :name="`keuangan_saham[${index}][per]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.pbv" :name="`keuangan_saham[${index}][pbv]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.roe" :name="`keuangan_saham[${index}][roe]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.roa" :name="`keuangan_saham[${index}][roa]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.npm" :name="`keuangan_saham[${index}][npm]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.ev_ebitda" :name="`keuangan_saham[${index}][ev_ebitda]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.der" :name="`keuangan_saham[${index}][der]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.current_ratio" :name="`keuangan_saham[${index}][current_ratio]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.aktivitas_lancar" :name="`keuangan_saham[${index}][aktivitas_lancar]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.gross_profit_margin" :name="`keuangan_saham[${index}][gross_profit_margin]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.operating_profit_margin" :name="`keuangan_saham[${index}][operating_profit_margin]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5 text-center"><button type="button" @click="keuanganSaham.splice(index, 1)" class="text-red-500 hover:text-red-700 text-xs">Hapus</button></td>
                                        </tr>
                                    </template>
                                    <tr x-show="keuanganSaham.length === 0">
                                        <td colspan="13" class="px-3 py-4 text-center text-muted text-sm italic">
                                            Tidak ada data saham. Tambahkan baris jika diperlukan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB: EXCEL --}}
                <div x-show="mode==='excel'" class="p-6 space-y-5">
                    @include('analisa-obligasi.partials.form-analisa-source')
                    <div
                        class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>Download template Excel terlebih dahulu, isi data sesuai format, lalu upload kembali.
                            <a href="{{ $templateRoute }}" class="font-semibold underline ml-1">Download Template</a>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload File Excel (.xlsx)</label>
                        <input type="file" name="file_excel" accept=".xlsx,.xls"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                        <p class="text-xs text-muted mt-1">Format: Excel (.xlsx/.xls). Gunakan template yang tersedia.</p>
                    </div>
                </div>

                {{-- TAB: AI --}}
                @include('analisa-lapkeu.partials.create-ai-tab')

                {{-- TAB: AI PLUS --}}
                <div x-show="mode==='ai-plus'" class="p-6 space-y-4">
                    <p class="text-sm text-muted">Analisa AI Plus memakai data terbaik yang tersedia dengan prioritas:
                        Input Manual, Upload Excel, PDF Lapkeu, lalu Master Obligasi.</p>

                    <div x-show="plusResolvedChecked && !plusResolvedComplete"
                        class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-900 space-y-2">
                        <p class="font-semibold">Data keuangan belum lengkap</p>
                        <p class="text-amber-800">Field berikut belum tersedia dari seluruh sumber data:</p>
                        <ul class="list-disc list-inside space-y-1 text-amber-900">
                            <template x-for="item in plusMissingList()" :key="item">
                                <li x-text="item"></li>
                            </template>
                        </ul>
                    </div>

                    <button type="button" @click="runAiPlusPreview()" :disabled="aiPlusLoading"
                        class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!aiPlusLoading">Jalankan Analisa AI Plus</span>
                        <span x-show="aiPlusLoading">Memproses...</span>
                    </button>
                    <button type="button" @click="resolvePlusData()" :disabled="plusResolveLoading"
                        class="ml-2 px-4 py-2.5 border border-line rounded-lg text-sm font-semibold hover:bg-[#f8fafc] disabled:opacity-50">
                        <span x-show="!plusResolveLoading">Cek Kelengkapan Data</span>
                        <span x-show="plusResolveLoading">Memeriksa...</span>
                    </button>

                    <div x-show="plusResolvedChecked && Object.keys(plusResolvedData).length"
                        class="border border-line rounded-lg overflow-hidden text-sm">
                        <div class="px-4 py-3 bg-[#f8fafc] font-semibold text-primary">Sumber Data AI Plus</div>
                        <template x-for="(item, key) in plusResolvedData" :key="key">
                            <div class="px-4 py-2 border-t border-line flex justify-between gap-4">
                                <span x-text="plusRequiredLabels[key] || key"></span>
                                <span class="text-right">
                                    <span x-text="formatExtractedValue(item.value)"></span>
                                    <span class="ml-2 text-xs text-muted" x-text="sourceLabel(item.source)"></span>
                                </span>
                            </div>
                        </template>
                    </div>

                    <div x-show="aiPlusError"
                        class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700 whitespace-pre-line"
                        x-text="aiPlusError"></div>
                    <template x-if="aiPlusResult">
                        <div class="space-y-3 border-t border-line pt-4 text-sm text-gray-700 leading-relaxed whitespace-pre-line"
                            x-text="aiPlusResult.raw"></div>
                    </template>
                </div>

                {{-- TAB: PDF --}}
                <div x-show="mode==='pdf'" class="p-6 space-y-5">
                    <div
                        class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Upload PDF laporan keuangan. AI akan mengekstrak data dan mengisi form otomatis.
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload PDF Laporan Keuangan</label>
                        <input type="file" id="pdf-parse-input" accept="application/pdf"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                        <p class="text-xs text-muted mt-1">Format PDF. Maks 20MB.</p>
                    </div>
                    <div class="flex flex-wrap gap-3 text-sm">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" value="text" x-model="pdfScanMode" class="text-primary focus:ring-primary/20">
                            <span>PDF parser teks</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" value="vision" x-model="pdfScanMode" class="text-primary focus:ring-primary/20">
                            <span>Scan AI Vision</span>
                        </label>
                    </div>
                    <button type="button" @click="parsePdf()" :disabled="pdfLoading"
                        class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 disabled:opacity-50">
                        <span x-show="!pdfLoading">Ekstrak & Isi Form Otomatis</span>
                        <span x-show="pdfLoading">Memproses PDF...</span>
                    </button>
                    <div x-show="pdfError" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-3"
                        x-text="pdfError"></div>
                    <div x-show="pdfSuccess"
                        class="text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg p-3"
                        x-text="pdfSuccess"></div>
                    <div x-show="pdfStatus && pdfLoading"
                        class="text-sm text-blue-700 bg-blue-50 border border-blue-200 rounded-lg p-3" x-text="pdfStatus">
                    </div>
                    <div x-show="extractedData" class="border border-line rounded-lg overflow-hidden">
                        <div
                            class="px-4 py-3 bg-[#f8fafc] border-b border-line flex items-center justify-between gap-3 flex-wrap">
                            <h3 class="font-semibold text-primary text-sm">Preview Hasil Ekstraksi</h3>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="parsePdf()"
                                    class="px-3 py-1.5 text-xs font-semibold border border-line rounded-lg hover:bg-white transition">Re-analyze</button>
                                <button type="button" @click="mode='manual'"
                                    class="px-3 py-1.5 text-xs font-semibold border border-line rounded-lg hover:bg-white transition">Review/Edit
                                    Input Manual</button>
                                <button type="button" @click="mode='ai-plus'; resolvePlusData()"
                                    class="px-3 py-1.5 text-xs font-semibold bg-primary text-white rounded-lg disabled:opacity-50 transition">Continue
                                    to AI Plus</button>
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-x-6 gap-y-2 p-4 text-sm">
                            <template x-for="field in extractionPreviewFields" :key="field.key">
                                <div class="flex justify-between gap-4 border-b border-line/70 py-2">
                                    <span class="text-muted" x-text="field.label"></span>
                                    <span class="font-medium text-right"
                                        x-text="formatExtractedValue(extractedData?.[field.key])"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upload PDF Lapkeu --}}
            <div class="bg-white rounded-xl border border-line p-6 space-y-3">
                <h3 class="font-semibold text-primary">Upload PDF Laporan Keuangan <span
                        class="text-xs font-normal text-muted">(opsional)</span></h3>
                <input type="file" name="pdf_lapkeu" accept="application/pdf"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                <p class="text-xs text-muted">File PDF laporan keuangan untuk referensi. Maks 20MB.</p>
            </div>

            {{-- Catatan --}}
            <div class="bg-white rounded-xl border border-line p-6">
                <h3 class="font-semibold text-primary mb-3">Catatan Analisa</h3>
                <textarea name="catatan" rows="4" placeholder="Tambahkan catatan atau konteks analisa..."
                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">{{ old('catatan') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ $cancelRoute }}"
                    class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</a>
                <button type="submit"
                    class="px-6 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/90 transition">
                    Submit & Analisa
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            window.lookupObligasiUrl = @json($lookupObligasiRoute ?? null);

            function lapkeuForm(previewAiUrl, previewAiPlusUrl, parsePdfUrl, parsePdfVisionUrl, parsePdfStatusUrl, lookupKeuanganEmitenUrl, resolveAiPlusDataUrl) {
                @php
                    $plusLabels = [
                        'total_asset' => 'Total Aset',
                        'total_liabilities' => 'Total Liabilitas',
                        'total_equity' => 'Total Ekuitas',
                        'revenue' => 'Pendapatan Bersih',
                        'net_income' => 'Laba Bersih',
                    ];
                @endphp
                return {
                    mode: @json(old('input_mode', 'manual')),
                    jenisAnalisa: @json(old('jenis_analisa', 'periode')),
                    kodeObligasi: @json(old('kode_obligasi')),
                    namaEmiten: @json(old('nama_emiten')),
                    namaObligasiSearch: @json(old('nama_obligasi', '')),
                    nameResults: [],
                    codeResults: [],
                    formSubmitted: @json($errors->any()),
                    periodeAnalisa: @json(old('periode')),
                    tahunAnalisa: @json(old('tahun', now()->year)),
                    info_nama_obligasi: @json(old('info_nama_obligasi', '')),
                    info_ytm: @json(old('info_ytm', '')),
                    harga_obligasi: @json(old('harga_obligasi', '')),
                    q1_obligasi: @json(old('q1_obligasi', '')),
                    q2_obligasi: @json(old('q2_obligasi', '')),
                    q3_obligasi: @json(old('q3_obligasi', '')),
                    q4_obligasi: @json(old('q4_obligasi', '')),
                    info_nominal_penerbitan: @json(old('info_nominal_penerbitan', '')),
                    current_asset: @json(old('current_asset', '')),
                    cash_equivalents: @json(old('cash_equivalents', '')),
                    account_receivable: @json(old('account_receivable', '')),
                    inventories: @json(old('inventories', '')),
                    other_current_asset: @json(old('other_current_asset', '')),
                    fixed_asset: @json(old('fixed_asset', '')),
                    other_non_current_asset: @json(old('other_non_current_asset', '')),
                    total_asset: @json(old('total_asset', '')),
                    current_liabilities: @json(old('current_liabilities', '')),
                    account_payable: @json(old('account_payable', '')),
                    accruals: @json(old('accruals', '')),
                    short_term_loans: @json(old('short_term_loans', '')),
                    current_maturities_of_long_term_loans: @json(old('current_maturities_of_long_term_loans', '')),
                    other_current_liabilities: @json(old('other_current_liabilities', '')),
                    long_term_loans: @json(old('long_term_loans', '')),
                    other_non_current_liabilities: @json(old('other_non_current_liabilities', '')),
                    total_non_current_liabilities: @json(old('total_non_current_liabilities', '')),
                    total_liabilities: @json(old('total_liabilities', '')),
                    share_capital: @json(old('share_capital', '')),
                    additional_paid_in_capital: @json(old('additional_paid_in_capital', '')),
                    retained_earning: @json(old('retained_earning', '')),
                    others: @json(old('others', '')),
                    non_controlling_interest: @json(old('non_controlling_interest', '')),
                    total_equity_equity_to_parent_entity: @json(old('total_equity_equity_to_parent_entity', '')),
                    equity: @json(old('equity', '')),
                    net_revenue: @json(old('net_revenue', '')),
                    cost_of_good_sold: @json(old('cost_of_good_sold', '')),
                    gross_income: @json(old('gross_income', '')),
                    operational_expense: @json(old('operational_expense', '')),
                    laba_operasional: @json(old('laba_operasional', '')),
                    other_income_expense: @json(old('other_income_expense', '')),
                    interest_expense: @json(old('interest_expense', '')),
                    income_before_tax: @json(old('income_before_tax', '')),
                    taxes: @json(old('taxes', '')),
                    ebit: @json(old('ebit', '')),
                    ebitda: @json(old('ebitda', '')),
                    net_income_attributable_to_non_controlling_interest: @json(old('net_income_attributable_to_non_controlling_interest', '')),
                    net_income: @json(old('net_income', '')),
                    eps: @json(old('eps', '')),
                    cash_flows_operating_activities: @json(old('cash_flows_operating_activities', '')),
                    cash_flows_investment: @json(old('cash_flows_investment', '')),
                    cash_flows_financing: @json(old('cash_flows_financing', '')),
                    sourceLoading: false,
                    sourceMessage: '',
                    sourceOk: false,
                    lookupKeuanganEmitenUrl: lookupKeuanganEmitenUrl,
                    aiLoading: false,
                    aiError: '',
                    aiResult: null,
                    previewAiUrl: previewAiUrl,
                    aiPdfFile: null,
                    aiParseLoading: false,
                    aiParseError: '',
                    aiParseSuccess: '',
                    aiPlusLoading: false,
                    aiPlusError: '',
                    aiPlusResult: null,
                    previewAiPlusUrl: previewAiPlusUrl,
                    resolveAiPlusDataUrl: resolveAiPlusDataUrl,
                    plusResolveLoading: false,
                    plusResolvedChecked: false,
                    plusResolvedComplete: false,
                    plusResolvedData: {},
                    plusResolvedMissing: [],
                    financialDataSources: {},
                    financialFieldNames: ['total_asset', 'total_liabilities', 'equity', 'net_revenue', 'net_income'],
                    keuanganSaham: @json(collect(old('keuangan_saham', []))->values()),
                    pdfLoading: false,
                    pdfError: '',
                    pdfSuccess: '',
                    pdfStatus: '',
                    pdfPath: '',
                    pdfScanMode: 'text',
                    extractedData: null,
                    extractionPreviewFields: [{
                            key: 'periode',
                            label: 'Tahun & Periode'
                        },
                        {
                            key: 'net_revenue',
                            label: 'Revenue / Pendapatan'
                        },
                        {
                            key: 'net_income',
                            label: 'Net Profit / Laba Bersih'
                        },
                        {
                            key: 'total_asset',
                            label: 'Total Asset'
                        },
                        {
                            key: 'total_liabilities',
                            label: 'Total Liability'
                        },
                        {
                            key: 'equity',
                            label: 'Equity'
                        },
                        {
                            key: 'cash_flows_operating_activities',
                            label: 'Cash Flow Operasi'
                        },
                        {
                            key: 'cash_flows_investment',
                            label: 'Cash Flow Investasi'
                        },
                        {
                            key: 'cash_flows_financing',
                            label: 'Cash Flow Pendanaan'
                        },
                        {
                            key: 'eps',
                            label: 'EPS'
                        },
                        {
                            key: 'ebitda',
                            label: 'EBITDA'
                        },
                    ],
                    plusRequiredLabels: @json($plusLabels),
                    parsePdfUrl: parsePdfUrl,
                    parsePdfVisionUrl: parsePdfVisionUrl,
                    parsePdfStatusUrl: parsePdfStatusUrl,

                    init() {
                        document.addEventListener('input', (event) => {
                            const name = event.target?.name;
                            if (this.financialFieldNames.includes(name)) {
                                this.financialDataSources[name] = 'input_manual';
                            }
                        });
                    },

                    lookupObligasi(query) {
                        if (!query || query.trim().length < 1 || !window.lookupObligasiUrl) return Promise.resolve([]);
                        return fetch(window.lookupObligasiUrl + '?q=' + encodeURIComponent(query.trim()), {
                            headers: { 'Accept': 'application/json' }
                        }).then(r => r.json()).catch(() => []);
                    },

                    selectObligasi(bond) {
                        this.namaObligasiSearch = bond.nama_obligasi || '';
                        this.kodeObligasi = bond.kode || '';
                        this.namaEmiten = bond.nama_emiten || '';

                        const set = (name, val) => {
                            const el = document.querySelector(`[name="${name}"]`);
                            if (el && val != null) el.value = val;
                        };
                        const setSelect = (name, val) => {
                            const sel = document.querySelector(`[name="${name}"]`);
                            if (sel && val) {
                                [...sel.options].forEach(o => {
                                    if (o.value === val) o.selected = true;
                                });
                            }
                        };

                        set('nama_obligasi', bond.nama_obligasi);
                        set('kode_obligasi', bond.kode);
                        set('nama_emiten', bond.nama_emiten);
                        setSelect('rating', bond.rating);
                        if (bond.kupon) set('kupon', bond.kupon);
                        if (bond.ytm) set('ytm', bond.ytm);

                        if (bond.nama_obligasi) set('info_nama_obligasi', bond.nama_obligasi);
                        if (bond.ytm) set('info_ytm', bond.ytm);
                        if (bond.harga_persen) set('harga_obligasi', bond.harga_persen);
                        if (bond.outstanding_amount) set('info_nominal_penerbitan', bond.outstanding_amount);

                        ['info_nama_obligasi', 'info_ytm', 'harga_obligasi', 'info_nominal_penerbitan'].forEach(name => {
                            const el = document.querySelector(`[name="${name}"]`);
                            if (el) el.dispatchEvent(new Event('input', { bubbles: true }));
                        });
                    },

                    onPdfSelected(event) {
                        this.aiPdfFile = event.target?.files?.[0] || null;
                        this.aiParseError = '';
                        this.aiParseSuccess = '';
                    },

                    fillLapkeuFormFromData(d, source = 'pdf_lapkeu') {
                        const set = (name, val) => {
                            const el = document.querySelector(`[name="${name}"]`);
                            if (el && val != null) el.value = val;
                        };
                        const setSelect = (name, val) => {
                            const sel = document.querySelector(`[name="${name}"]`);
                            if (sel && val) {
                                [...sel.options].forEach(o => {
                                    if (o.value === val) o.selected = true;
                                });
                            }
                        };

                        this.namaObligasiSearch = d.nama_obligasi || d.nama_perusahaan || this.namaObligasiSearch;
                        this.kodeObligasi = d.kode_obligasi || d.kode_saham || this.kodeObligasi;
                        this.namaEmiten = d.nama_emiten || this.namaEmiten;
                        this.periodeAnalisa = d.periode || this.periodeAnalisa;
                        if (d.periode && String(d.periode).length >= 4) this.tahunAnalisa = String(d.periode).slice(0, 4);

                        setSelect('rating', d.rating);
                        setSelect('mata_uang', d.mata_uang);
                        setSelect('sektor', d.sektor);
                        if (d.kupon) set('kupon', d.kupon);
                        if (d.ytm) set('ytm', d.ytm);
                        set('periode_dari', d.periode_dari);
                        set('periode_sampai', d.periode_sampai);
                        set('nominal_penerbit', d.nominal_penerbit);
                        set('tanggal_terbit', d.tanggal_terbit);
                        set('tanggal_jatuh_tempo', d.tanggal_jatuh_tempo);
                        if (d.tanpa_jaminan) {
                            const cb = document.querySelector('[name="tanpa_jaminan"]');
                            if (cb) cb.checked = true;
                        }
                        if (d.dengan_jaminan) {
                            const cb = document.querySelector('[name="dengan_jaminan"]');
                            if (cb) cb.checked = true;
                        }

                        if (Array.isArray(d.keuangan_saham)) {
                            this.keuanganSaham = d.keuangan_saham;
                        }

                        const numFields = ['total_asset', 'current_asset', 'cash_equivalents', 'account_receivable',
                            'inventories', 'other_current_asset', 'fixed_asset', 'other_non_current_asset',
                            'total_liabilities', 'current_liabilities', 'account_payable', 'accruals',
                            'short_term_loans', 'current_maturities_of_long_term_loans',
                            'other_current_liabilities', 'long_term_loans', 'other_non_current_liabilities',
                            'total_non_current_liabilities', 'share_capital', 'additional_paid_in_capital',
                            'retained_earning', 'others', 'non_controlling_interest',
                            'total_equity_equity_to_parent_entity', 'equity', 'net_revenue',
                            'cost_of_good_sold', 'gross_income', 'operational_expense', 'laba_operasional',
                            'other_income_expense', 'ebit', 'ebitda', 'interest_expense',
                            'income_before_tax', 'taxes',
                            'net_income_attributable_to_non_controlling_interest', 'net_income', 'eps',
                            'cash_flows_operating_activities', 'cash_flows_investment', 'cash_flows_financing'
                        ];
                        numFields.forEach(f => {
                            set(f, d[f]);
                            if (d[f] !== null && d[f] !== undefined && d[f] !== '') {
                                this.financialDataSources[f] = source;
                            }
                        });
                    },

                    formatExtractedValue(value) {
                        if (value === null || value === undefined || value === '') return '-';
                        if (!isNaN(Number(value)) && value !== '') return Number(value).toLocaleString('id-ID', {
                            maximumFractionDigits: 2
                        });
                        return value;
                    },

                    pollPdfExtraction(pollUrl, options = {}) {
                        return new Promise((resolve, reject) => {
                            let attempts = 0;
                            const maxAttempts = 120;
                            const poll = () => {
                                attempts++;
                                fetch(pollUrl, {
                                        headers: {
                                            'Accept': 'application/json'
                                        }
                                    })
                                    .then(async r => {
                                        const resp = await r.json();
                                        if (!r.ok || !resp.success) {
                                            reject(new Error(resp.message ||
                                                'Gagal cek status ekstraksi PDF.'));
                                            return;
                                        }

                                        this.pdfStatus = resp.message || 'Memproses PDF...';
                                        if (options.ai) this.aiParseSuccess = this.pdfStatus;

                                        if (resp.status === 'completed') {
                                            resolve(resp.data || {});
                                            return;
                                        }

                                        if (resp.status === 'failed') {
                                            reject(new Error(resp.error || resp.message ||
                                                'Ekstraksi PDF gagal.'));
                                            return;
                                        }

                                        if (attempts >= maxAttempts) {
                                            reject(new Error(
                                                'Ekstraksi PDF belum selesai. Cek kembali beberapa saat lagi.'
                                                ));
                                            return;
                                        }

                                        setTimeout(poll, 2500);
                                    })
                                    .catch(reject);
                            };

                            poll();
                        });
                    },

                    submitPdfExtraction(file, options = {}) {
                        const fd = new FormData();
                        fd.append('file_pdf', file);
                        fd.append('_token', document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}');

                        const useVision = this.pdfScanMode === 'vision';
                        const url = useVision && this.parsePdfVisionUrl ? this.parsePdfVisionUrl : this.parsePdfUrl;

                        return fetch(url, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json' },
                                body: fd
                            })
                            .then(async r => {
                                const resp = await r.json();
                                if (!r.ok || !resp.success) {
                                    throw new Error(resp.message || 'Gagal membaca PDF.');
                                }

                                if (resp.status && resp.status !== 'completed') {
                                    this.pdfStatus = resp.message || 'PDF masuk antrean ekstraksi.';
                                    if (options.ai) this.aiParseSuccess = this.pdfStatus;
                                    return this.pollPdfExtraction(resp.poll_url || this.parsePdfStatusUrl.replace('__UUID__', resp.extraction_id), options);
                                }

                                this.pdfStatus = resp.message || (useVision ? 'Scan AI selesai.' : 'Ekstraksi PDF selesai.');
                                if (options.ai) this.aiParseSuccess = this.pdfStatus;
                                return resp.data || {};
                            });
                    },

                    runAiFromPdf() {
                        if (!this.aiPdfFile) {
                            this.aiParseError = 'Pilih file PDF terlebih dahulu.';
                            return;
                        }
                        this.aiLoading = true;
                        this.aiError = '';
                        this.aiParseLoading = true;
                        this.aiParseError = '';
                        this.aiParseSuccess = '';
                        this.aiResult = null;

                        this.submitPdfExtraction(this.aiPdfFile, { ai: true })
                            .then(d => {
                                if (!d || typeof d !== 'object' || Object.keys(d).length === 0) {
                                    this.aiParseError =
                                        'Gagal mengekstrak data dari PDF. Tidak ada data keuangan yang ditemukan.';
                                    this.aiLoading = false;
                                    this.aiParseLoading = false;
                                    return;
                                }
                                if (d.pdf_lapkeu_path) this.pdfPath = d.pdf_lapkeu_path;
                                this.extractedData = d;
                                this.fillLapkeuFormFromData(d, 'pdf_lapkeu');
                                this.aiParseLoading = false;
                                const nama = document.getElementById('nama_obligasi')?.value?.trim();
                                if (!nama) {
                                    this.aiError =
                                        'Nama obligasi tidak ditemukan di PDF. Isi manual di tab Input Manual.';
                                    this.aiLoading = false;
                                    return;
                                }
                                if (!this.isAnalisaSourceReady()) {
                                    this.aiError = this.analisaSourceError();
                                    this.aiLoading = false;
                                    return;
                                }
                                const form = document.getElementById('lapkeu-form');
                                const aiFd = new FormData(form);
                                fetch(this.previewAiUrl, {
                                        method: 'POST',
                                        headers: {
                                            'Accept': 'application/json'
                                        },
                                        body: aiFd,
                                    })
                                    .then(async r2 => {
                                        const resp2 = await r2.json();
                                        if (!r2.ok || !resp2.success) {
                                            this.aiError = resp2.message || 'Gagal memproses analisa AI.';
                                            this.aiLoading = false;
                                            return;
                                        }
                                        this.aiResult = resp2.data;
                                        this.aiLoading = false;
                                        this.aiParseSuccess =
                                            'Data berhasil diekstrak dari PDF, form terisi, dan analisa AI siap. Silakan review di tab Input Manual.';
                                        this.mode = 'manual';
                                    })
                                    .catch(e => {
                                        this.aiError = e.message || 'Gagal memproses analisa AI';
                                        this.aiLoading = false;
                                    });
                            })
                            .catch(e => {
                                this.aiParseError = e.message || 'Gagal membaca PDF';
                                this.aiLoading = false;
                                this.aiParseLoading = false;
                            });
                    },

                    parsePdf() {
                        const fileInput = document.getElementById('pdf-parse-input');
                        if (!fileInput?.files[0]) {
                            this.pdfError = 'Pilih file PDF terlebih dahulu.';
                            return;
                        }
                        this.pdfLoading = true;
                        this.pdfError = '';
                        this.pdfSuccess = '';
                        this.pdfStatus = '';
                        this.extractedData = null;
                        this.submitPdfExtraction(fileInput.files[0])
                            .then(d => {
                                if (!d || typeof d !== 'object' || Object.keys(d).length === 0) {
                                    this.pdfError =
                                        'Gagal mengekstrak data dari PDF. Tidak ada data keuangan yang ditemukan.';
                                    return;
                                }
                                if (d.pdf_lapkeu_path) this.pdfPath = d.pdf_lapkeu_path;
                                this.extractedData = d;
                                this.fillLapkeuFormFromData(d, 'pdf_lapkeu');
                                this.pdfSuccess =
                                    'Data berhasil diekstrak dari PDF dan mengisi Input Manual sebagai draft. Review/edit data sebelum Save atau lanjutkan ke AI Plus.';
                            })
                            .catch(e => {
                                this.pdfError = e.message || 'Gagal';
                            })
                            .finally(() => {
                                this.pdfLoading = false;
                            });
                    },

                    runAiPreview() {
                        const form = document.getElementById('lapkeu-form');
                        const nama = document.getElementById('nama_obligasi')?.value?.trim();
                        if (!nama) {
                            this.aiError = 'Isi Nama Obligasi di bagian Informasi Obligasi terlebih dahulu.';
                            return;
                        }
                        if (!this.isAnalisaSourceReady()) {
                            this.aiError = this.analisaSourceError();
                            return;
                        }
                        this.aiLoading = true;
                        this.aiError = '';
                        const fd = new FormData(form);
                        fetch(this.previewAiUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json'
                                },
                                body: fd,
                            })
                            .then(async r => {
                                const resp = await r.json();
                                if (!r.ok || !resp.success) {
                                    this.aiError = resp.message || 'Gagal memproses';
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

                    applyAiToManual() {
                        this.mode = 'manual';
                    },

                    getFormValue(name) {
                        const el = document.querySelector(`[name="${name}"]`);
                        return el ? el.value?.trim() : '';
                    },

                    plusMissingList() {
                        return this.plusResolvedMissing.map(f => this.plusRequiredLabels[f] || f);
                    },

                    sourceLabel(source) {
                        return {
                            input_manual: 'Input Manual',
                            upload_excel: 'Upload Excel',
                            pdf_lapkeu: 'PDF Lapkeu',
                            master_obligasi: 'Master Obligasi',
                        }[source] || 'Belum tersedia';
                    },

                    isAnalisaSourceReady() {
                        if (!this.kodeObligasi?.trim()) return false;
                        if (this.jenisAnalisa === 'tahunan') return /^\d{4}$/.test(String(this.tahunAnalisa || ''));
                        return /^\d{6}$/.test(String(this.periodeAnalisa || ''));
                    },

                    analisaSourceError() {
                        if (!this.kodeObligasi?.trim()) return 'Isi Emiten di tab Upload Excel atau Analisa AI terlebih dahulu.';
                        if (this.jenisAnalisa === 'tahunan') return 'Isi Tahun dengan format YYYY.';
                        return 'Isi Periode LapKeu dengan format YYYYMM.';
                    },

                    processKeuanganEmiten() {
                        this.sourceMessage = '';
                        this.sourceOk = false;

                        if (!this.isAnalisaSourceReady()) {
                            this.sourceMessage = this.analisaSourceError();
                            return;
                        }

                        const params = new URLSearchParams({
                            kode_obligasi: this.kodeObligasi,
                            jenis_analisa: this.jenisAnalisa,
                        });

                        if (this.jenisAnalisa === 'tahunan') {
                            params.set('tahun', this.tahunAnalisa);
                        } else {
                            params.set('periode', this.periodeAnalisa);
                        }

                        this.sourceLoading = true;
                        fetch(`${this.lookupKeuanganEmitenUrl}?${params.toString()}`, {
                                headers: { 'Accept': 'application/json' }
                            })
                            .then(async r => {
                                const resp = await r.json();
                                if (!r.ok || !resp.found) {
                                    throw new Error(resp.message || 'Data Keuangan Emiten tidak ditemukan.');
                                }

                                this.fillLapkeuFormFromData(resp.data || {}, 'master_obligasi');
                                this.extractedData = resp.data || null;
                                this.sourceOk = true;
                                this.sourceMessage = resp.message || 'Data Keuangan Emiten berhasil diproses.';
                            })
                            .catch(e => {
                                this.sourceOk = false;
                                this.sourceMessage = e.message || 'Gagal memproses data Keuangan Emiten.';
                            })
                            .finally(() => {
                                this.sourceLoading = false;
                            });
                    },

                    runAiPlusPreview() {
                        this.aiPlusLoading = true;
                        this.aiPlusError = '';
                        const form = document.getElementById('lapkeu-form');
                        const fd = new FormData(form);
                        fetch(this.previewAiPlusUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json'
                                },
                                body: fd,
                            })
                            .then(async r => {
                                const resp = await r.json();
                                if (!r.ok || !resp.success) {
                                    if (resp.missing?.length) {
                                        this.aiPlusError = resp.message ||
                                            'Lengkapi data Input Manual terlebih dahulu.';
                                    } else {
                                        this.aiPlusError = resp.message || 'Gagal memproses';
                                    }
                                    return;
                                }
                                this.aiPlusResult = resp.data;
                                this.applyResolvedResponse(resp);
                            })
                            .catch(e => {
                                this.aiPlusError = e.message || 'Gagal memproses';
                            })
                            .finally(() => {
                                this.aiPlusLoading = false;
                            });
                    },

                    applyResolvedResponse(resp) {
                        if (!resp.resolved_data && !resp.data) return;
                        this.plusResolvedData = resp.resolved_data || resp.data || {};
                        this.plusResolvedMissing = resp.missing || [];
                        this.plusResolvedComplete = resp.complete ?? this.plusResolvedMissing.length === 0;
                        this.plusResolvedChecked = true;
                    },

                    resolvePlusData() {
                        if (!this.resolveAiPlusDataUrl) return;
                        this.plusResolveLoading = true;
                        const fd = new FormData(document.getElementById('lapkeu-form'));
                        fetch(this.resolveAiPlusDataUrl, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json' },
                                body: fd,
                            })
                            .then(r => r.json())
                            .then(resp => this.applyResolvedResponse(resp))
                            .catch(e => this.aiPlusError = e.message || 'Gagal memeriksa kelengkapan data.')
                            .finally(() => this.plusResolveLoading = false);
                    },
                };
            }
        </script>
    @endpush
@endsection
