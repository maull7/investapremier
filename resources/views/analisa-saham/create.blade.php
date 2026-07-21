@extends($layout ?? 'layouts.user')

@section('content')
    <div class="max-w-5xl" x-data="lapkeuForm('{{ $previewAiRoute }}', '{{ $previewAiPlusRoute }}', '{{ $parsePdfRoute }}', '{{ $parsePdfVisionRoute }}', '{{ $parsePdfStatusRoute }}')">
        <div class="mb-6">
            <h1 class="page-title">Submit Analisa {{ $productLabel }}</h1>
            <p class="page-sub">Isi data laporan keuangan secara manual atau upload Excel</p>
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
            <input type="hidden" name="input_mode"
                :value="['ai', 'ai-plus', 'pdf', 'riset-broker'].includes(mode) ? 'manual' : mode">
            <input type="hidden" name="ai_narasi" :value="aiResult?.raw || ''">
            <input type="hidden" name="ai_output" :value="aiResult ? JSON.stringify(aiResult.parsed || {}) : ''">
            <input type="hidden" name="ai_narasi_plus" :value="aiPlusResult?.raw || ''">
            <input type="hidden" name="ai_output_plus"
                :value="aiPlusResult ? JSON.stringify(aiPlusResult.parsed || {}) : ''">
            <input type="hidden" name="pdf_lapkeu_path" x-model="pdfPath">

            {{-- Info Dasar --}}
            <div class="bg-white rounded-xl border border-line p-6 space-y-4">
                <h3 class="font-semibold text-primary">Informasi Saham</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="relative" x-data="{ nameSearch: @json(old('nama_perusahaan', '')), nameResults: [] }" @click.outside="nameResults = []">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="nama_perusahaan" id="nama_perusahaan" x-model="nameSearch" x-ref="namaPerusahaan"
                            @input.debounce.300ms="if (nameSearch.length > 0) { let d = $data; window.lookupStock(nameSearch).then(r => d.nameResults = r) } else { nameResults = [] }"
                            @blur="if (nameSearch.length > 0) { let d = $data; window.lookupStock(nameSearch).then(list => { if (list.length > 0) { window.selectStock(list[0]); nameSearch = list[0].nama; d.nameResults = []; } }) }"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        <div x-show="nameResults.length > 0"
                            class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="s in nameResults" :key="s.kode">
                                <button type="button" @click="window.selectStock(s); nameSearch = s.nama; nameResults = []"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm border-b border-gray-100 last:border-0">
                                    <span class="font-semibold" x-text="s.nama"></span>
                                    <span class="text-gray-500" x-text="' (' + s.kode + ')'"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <div class="relative" x-data="{ stockSearch: @json(old('kode_saham', '')), stockResults: [] }" @click.outside="stockResults = []">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Saham</label>
                        <input type="text" name="kode_saham" x-model="stockSearch" placeholder="cth: BBCA"
                            @input.debounce.300ms="if (stockSearch.length > 0) { let d = $data; window.lookupStock(stockSearch).then(r => d.stockResults = r) } else { stockResults = [] }"
                            @blur="if (stockSearch.length > 0) { let d = $data; window.lookupStock(stockSearch).then(list => { if (list.length > 0) { window.selectStock(list[0]); stockSearch = list[0].kode; d.stockResults = []; } }) }"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm uppercase">
                        <div x-show="stockResults.length > 0"
                            class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="s in stockResults" :key="s.kode">
                                <button type="button"
                                    @click="window.selectStock(s); stockSearch = s.kode; stockResults = []"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm border-b border-gray-100 last:border-0">
                                    <span class="font-semibold" x-text="s.kode"></span>
                                    <span class="text-gray-500" x-text="' - ' + s.nama"></span>
                                </button>
                            </template>
                        </div>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mata Uang</label>
                        <select name="mata_uang"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                            @foreach (['IDR', 'USD', 'EUR', 'SGD'] as $c)
                                <option value="{{ $c }}"
                                    {{ old('mata_uang', 'IDR') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-row items-center gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Periode Dari Tahun</label>
                            <input type="number" name="periode_dari" value="{{ old('periode_dari') }}"
                                placeholder="cth: 2022"
                                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tahun</label>
                            <input type="number" name="periode_sampai" value="{{ old('periode_sampai') }}"
                                placeholder="cth: 2024"
                                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Label Periode</label>
                        <input type="text" name="periode" value="{{ old('periode') }}" placeholder="cth: Q4 2024"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
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
                    <button type="button" @click="mode='riset-broker'"
                        :class="mode === 'riset-broker' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition whitespace-nowrap">Riset Broker</button>
                </div>

                {{-- TAB: MANUAL --}}
                <div x-show="mode==='manual'" class="p-6 space-y-6">
                    @include('analisa-saham.partials.form-informasi-saham')
                    @include('analisa-saham.partials.form-neraca')
                    @include('analisa-saham.partials.form-laba-rugi')
                    @include('analisa-saham.partials.form-arus-kas')
                </div>

                <div x-show="mode==='lengkap'" class="p-6 space-y-6">

                    @include('analisa-saham.partials.form-informasi-saham-lengkap')

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
                                            <td
                                                class="px-3 py-1.5 {{ in_array($name, ['current_asset']) ? 'font-semibold' : '' }}">
                                                {{ $label }}</td>
                                            <td class="px-2 py-1.5"><input type="number" name="{{ $name }}"
                                                    x-model="{{ $name }}" step="0.01"
                                                    value="{{ old($name) }}"
                                                    class="w-full border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded {{ in_array($name, ['current_asset']) ? 'font-semibold' : '' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-gray-50/50">
                                        <td colspan="2" class="px-3 py-2 font-semibold text-muted">Aset Tidak Lancar
                                        </td>
                                    </tr>
                                    @foreach ([['fixed_asset', 'Aset Tetap'], ['other_non_current_asset', 'Aset Tidak Lancar Lainnya'], ['total_asset', 'Total Aset']] as [$name, $label])
                                        <tr class="hover:bg-gray-50/50">
                                            <td
                                                class="px-3 py-1.5 {{ in_array($name, ['total_asset']) ? 'font-semibold' : '' }}">
                                                {{ $label }}</td>
                                            <td class="px-2 py-1.5"><input type="number" name="{{ $name }}"
                                                    x-model="{{ $name }}" step="0.01"
                                                    value="{{ old($name) }}"
                                                    class="w-full border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded {{ in_array($name, ['total_asset']) ? 'font-semibold' : '' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-gray-50/50">
                                        <td colspan="2" class="px-3 py-2 font-semibold text-muted">Liabilitas Jangka
                                            Pendek</td>
                                    </tr>
                                    @foreach ([['account_payable', 'Utang Usaha'], ['accruals', 'Akrual'], ['short_term_loans', 'Pinjaman Jangka Pendek'], ['current_maturities_of_long_term_loans', 'Bagian Lancar Utang JK Panjang'], ['other_current_liabilities', 'Liabilitas Lancar Lainnya'], ['current_liabilities', 'Total Liabilitas Lancar']] as [$name, $label])
                                        <tr class="hover:bg-gray-50/50">
                                            <td
                                                class="px-3 py-1.5 {{ in_array($name, ['current_liabilities']) ? 'font-semibold' : '' }}">
                                                {{ $label }}</td>
                                            <td class="px-2 py-1.5"><input type="number" name="{{ $name }}"
                                                    x-model="{{ $name }}" step="0.01"
                                                    value="{{ old($name) }}"
                                                    class="w-full border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded {{ in_array($name, ['current_liabilities']) ? 'font-semibold' : '' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-gray-50/50">
                                        <td colspan="2" class="px-3 py-2 font-semibold text-muted">Liabilitas JK
                                            Panjang & Ekuitas</td>
                                    </tr>
                                    @foreach ([['long_term_loans', 'Pinjaman Jangka Panjang'], ['other_non_current_liabilities', 'Liabilitas Tidak Lancar Lainnya'], ['total_non_current_liabilities', 'Total Liabilitas Tidak Lancar'], ['total_liabilities', 'Total Liabilitas'], ['retained_earning', 'Saldo Laba'], ['equity', 'Total Ekuitas'], ['share_capital', 'Modal Saham'], ['additional_paid_in_capital', 'Tambahan Modal Disetor'], ['others', 'Komponen Ekuitas Lain'], ['non_controlling_interest', 'Kepentingan Non-Pengendali'], ['total_equity_equity_to_parent_entity', 'Ekuitas ke Entitas Induk']] as [$name, $label])
                                        <tr class="hover:bg-gray-50/50">
                                            <td
                                                class="px-3 py-1.5 {{ in_array($name, ['total_liabilities', 'equity']) ? 'font-semibold' : '' }}">
                                                {{ $label }}</td>
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
                                            <td
                                                class="px-3 py-1.5 {{ in_array($name, ['gross_income', 'ebit', 'ebitda', 'net_income', 'eps']) ? 'font-semibold' : '' }}">
                                                {{ $label }}</td>
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

                    <hr class="border-line my-2">

                    {{-- Portofolio Efek --}}
                    <div class="bg-white rounded-xl border border-line overflow-hidden">
                        <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-primary">Portofolio Efek</h4>
                                <p class="text-xs text-muted mt-0.5">Daftar efek. Bisa terisi otomatis dari PDF.</p>
                            </div>
                            <button type="button" @click="portofolio.push({kode_efek:'',nama_efek:'',sektor:'',bobot:'',nilai_pasar:'',harga_perolehan:'',persen_nab:'',ihsg_contribution:'',return_1m:'',return_3m:'',return_6m:'',return_1y:'',top_10:false})"
                                class="text-xs px-3 py-1.5 border border-line rounded-lg hover:bg-gray-50">+ Tambah</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse">
                                <thead class="bg-gray-50 text-muted text-xs">
                                    <tr>
                                        <th class="px-3 py-2 text-left whitespace-nowrap border-b border-line">Kode</th>
                                        <th class="px-3 py-2 text-left whitespace-nowrap border-b border-line">Nama Efek</th>
                                        <th class="px-3 py-2 text-left whitespace-nowrap border-b border-line">Sektor</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Bobot %</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Nilai Pasar</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Harga Perolehan</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">% thd NAB</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Kontribusi % IHSG</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Return 1M</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Return 3M</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Return 6M</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Return 1 Thn</th>
                                        <th class="px-3 py-2 text-center whitespace-nowrap border-b border-line">Top 10</th>
                                        <th class="px-3 py-2 text-center whitespace-nowrap border-b border-line"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(item, index) in portofolio" :key="index">
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-2 py-1.5"><input type="text" x-model="item.kode_efek" :name="`portofolio[${index}][kode_efek]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="text" x-model="item.nama_efek" :name="`portofolio[${index}][nama_efek]`" class="w-28 border-0 bg-transparent text-sm px-1 py-1 focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="text" x-model="item.sektor" :name="`portofolio[${index}][sektor]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.01" x-model="item.bobot" :name="`portofolio[${index}][bobot]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.01" x-model="item.nilai_pasar" :name="`portofolio[${index}][nilai_pasar]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.01" x-model="item.harga_perolehan" :name="`portofolio[${index}][harga_perolehan]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.01" x-model="item.persen_nab" :name="`portofolio[${index}][persen_nab]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.01" x-model="item.ihsg_contribution" :name="`portofolio[${index}][ihsg_contribution]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.01" x-model="item.return_1m" :name="`portofolio[${index}][return_1m]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.01" x-model="item.return_3m" :name="`portofolio[${index}][return_3m]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.01" x-model="item.return_6m" :name="`portofolio[${index}][return_6m]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.01" x-model="item.return_1y" :name="`portofolio[${index}][return_1y]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5 text-center">
                                                <input type="hidden" :name="`portofolio[${index}][top_10]`" value="0">
                                                <input type="checkbox" :name="`portofolio[${index}][top_10]`" value="1" :checked="item.top_10" @change="item.top_10 = $event.target.checked" class="rounded border-gray-300 text-primary focus:ring-primary/30">
                                            </td>
                                            <td class="px-2 py-1.5 text-center">
                                                <button type="button" @click="portofolio.splice(index, 1)" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="portofolio.length === 0">
                                        <td colspan="14" class="px-3 py-4 text-center text-muted text-sm italic">
                                            Belum ada data portofolio efek. Isi secara manual atau upload referensi.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Analisa Likuiditas --}}
                    <div class="bg-white rounded-xl border border-line overflow-hidden">
                        <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-primary">Analisa Likuiditas</h4>
                                <p class="text-xs text-muted mt-0.5">Data likuiditas. Bisa terisi otomatis dari PDF.</p>
                            </div>
                            <button type="button" @click="likuiditas.push({kode_efek:'',nama_efek:'',rata_volume_transaksi_harian:'',volume_terendah:'',volume_saham:'',skenario_20_persen_reds:'',skenario_reds_closing_10:'',rasio_likuiditas_harian:'',rasio_likuiditas:''})"
                                class="text-xs px-3 py-1.5 border border-line rounded-lg hover:bg-gray-50">+ Tambah</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse">
                                <thead class="bg-gray-50 text-muted text-xs">
                                    <tr>
                                        <th class="px-3 py-2 text-left whitespace-nowrap border-b border-line">Daftar Efek</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Rata-rata Volume Transaksi Harian</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Volume Terendah</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Volume Saham</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Skenario 20% Reds</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Skenario Reds Vol. Closing (10%)</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Rasio Likuiditas Harian</th>
                                        <th class="px-3 py-2 text-right whitespace-nowrap border-b border-line">Rasio Likuiditas</th>
                                        <th class="px-3 py-2 text-center whitespace-nowrap border-b border-line"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(item, index) in likuiditas" :key="index">
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-2 py-1.5 whitespace-nowrap">
                                                <input type="text" x-model="item.kode_efek" :name="`likuiditas[${index}][kode_efek]`" class="w-16 border-0 bg-transparent text-sm px-1 py-1 focus:outline-none focus:ring-1 focus:ring-primary/30 rounded" placeholder="Kode">
                                                <span class="text-muted mx-0.5">-</span>
                                                <input type="text" x-model="item.nama_efek" :name="`likuiditas[${index}][nama_efek]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 focus:outline-none focus:ring-1 focus:ring-primary/30 rounded" placeholder="Nama">
                                            </td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.rata_volume_transaksi_harian" :name="`likuiditas[${index}][rata_volume_transaksi_harian]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.volume_terendah" :name="`likuiditas[${index}][volume_terendah]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.volume_saham" :name="`likuiditas[${index}][volume_saham]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.skenario_20_persen_reds" :name="`likuiditas[${index}][skenario_20_persen_reds]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.skenario_reds_closing_10" :name="`likuiditas[${index}][skenario_reds_closing_10]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.rasio_likuiditas_harian" :name="`likuiditas[${index}][rasio_likuiditas_harian]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.rasio_likuiditas" :name="`likuiditas[${index}][rasio_likuiditas]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5 text-center"><button type="button" @click="likuiditas.splice(index, 1)" class="text-red-500 hover:text-red-700 text-xs">Hapus</button></td>
                                        </tr>
                                    </template>
                                    <tr x-show="likuiditas.length === 0">
                                        <td colspan="9" class="px-3 py-4 text-center text-muted text-sm italic">
                                            Belum ada data likuiditas. Isi secara manual.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Analisa Keuangan --}}
                    <div class="bg-white rounded-xl border border-line overflow-hidden">
                        <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-primary">Analisa Keuangan</h4>
                                <p class="text-xs text-muted mt-0.5">Rasio keuangan. Bisa terisi otomatis dari PDF.</p>
                            </div>
                            <button type="button" @click="keuangan.push({kode_efek:'',nama_efek:'',per:'',pbv:'',roe:'',roa:'',npm:'',ev_ebitda:'',der:'',current_ratio:'',aktivitas_lancar:'',gross_profit_margin:'',operating_profit_margin:''})"
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
                                    <template x-for="(item, index) in keuangan" :key="index">
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-2 py-1.5 whitespace-nowrap">
                                                <input type="text" x-model="item.kode_efek" :name="`keuangan[${index}][kode_efek]`" class="w-16 border-0 bg-transparent text-sm px-1 py-1 focus:outline-none focus:ring-1 focus:ring-primary/30 rounded" placeholder="Kode">
                                                <span class="text-muted mx-0.5">-</span>
                                                <input type="text" x-model="item.nama_efek" :name="`keuangan[${index}][nama_efek]`" class="w-24 border-0 bg-transparent text-sm px-1 py-1 focus:outline-none focus:ring-1 focus:ring-primary/30 rounded" placeholder="Nama">
                                            </td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.per" :name="`keuangan[${index}][per]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.pbv" :name="`keuangan[${index}][pbv]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.roe" :name="`keuangan[${index}][roe]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.roa" :name="`keuangan[${index}][roa]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.npm" :name="`keuangan[${index}][npm]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.ev_ebitda" :name="`keuangan[${index}][ev_ebitda]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.der" :name="`keuangan[${index}][der]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.current_ratio" :name="`keuangan[${index}][current_ratio]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.aktivitas_lancar" :name="`keuangan[${index}][aktivitas_lancar]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.gross_profit_margin" :name="`keuangan[${index}][gross_profit_margin]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5"><input type="number" step="0.0001" x-model="item.operating_profit_margin" :name="`keuangan[${index}][operating_profit_margin]`" class="w-20 border-0 bg-transparent text-sm px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-primary/30 rounded"></td>
                                            <td class="px-2 py-1.5 text-center"><button type="button" @click="keuangan.splice(index, 1)" class="text-red-500 hover:text-red-700 text-xs">Hapus</button></td>
                                        </tr>
                                    </template>
                                    <tr x-show="keuangan.length === 0">
                                        <td colspan="13" class="px-3 py-4 text-center text-muted text-sm italic">
                                            Belum ada data keuangan. Isi secara manual.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Saham Pembanding --}}
                    <div x-data="sahamPembandingManager(@json(old('saham_pembanding', [])))"
                        class="bg-white rounded-xl border border-line p-6 space-y-4">
                        <h4 class="font-semibold text-primary">Saham Pembanding</h4>
                        <div class="relative" @click.outside="results = []">
                            <input type="text" x-model="search" placeholder="Cari kode/nama saham..."
                                @input.debounce.300ms="if (search.length > 0) { let d = $data; window.lookupStock(search).then(r => d.results = r) } else { results = [] }"
                                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                            <div x-show="results.length > 0"
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="s in results" :key="s.kode">
                                    <button type="button" @click="addStock(s)"
                                        class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm border-b border-gray-100 last:border-0">
                                        <span class="font-semibold" x-text="s.kode"></span>
                                        <span class="text-gray-500" x-text="' - ' + s.nama"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(item, index) in list" :key="index">
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    <input type="hidden" :name="`saham_pembanding[${index}][kode]`" :value="item.kode">
                                    <input type="hidden" :name="`saham_pembanding[${index}][nama]`" :value="item.nama">
                                    <input type="hidden" :name="`saham_pembanding[${index}][sektor]`" :value="item.sektor">
                                    <div class="flex-1">
                                        <div class="font-semibold text-sm"
                                            x-text="item.kode + ' - ' + item.nama"></div>
                                        <div class="text-xs text-muted" x-text="item.sektor"></div>
                                    </div>
                                    <button type="button" @click="removeStock(index)"
                                        class="text-red-500 hover:text-red-700 text-xs font-medium">
                                        Hapus
                                    </button>
                                </div>
                            </template>
                            <div x-show="list.length === 0" class="text-sm text-muted italic">
                                Belum ada saham pembanding.
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
                    <p class="text-sm text-muted">Analisa AI Plus membutuhkan data <strong>Input Manual yang
                            lengkap</strong> (Neraca, Laba Rugi, dan Arus Kas).</p>

                    <div x-show="!isPlusManualReady()"
                        class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-900 space-y-2">
                        <p class="font-semibold">Data Input Manual belum lengkap</p>
                        <p class="text-amber-800">Lengkapi bagian berikut di tab <strong>Input Manual</strong> sebelum
                            menjalankan Analisa AI Plus:</p>
                        <ul class="list-disc list-inside space-y-1 text-amber-900">
                            <template x-for="item in plusMissingList()" :key="item">
                                <li x-text="item"></li>
                            </template>
                        </ul>
                    </div>

                    <button type="button" @click="runAiPlusPreview()" :disabled="aiPlusLoading || !isPlusManualReady()"
                        class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!aiPlusLoading">Jalankan Analisa AI Plus</span>
                        <span x-show="aiPlusLoading">Memproses...</span>
                    </button>
                    <p x-show="!isPlusManualReady()" class="text-xs text-muted">Tombol aktif setelah semua data di atas
                        terisi.</p>

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
                                <button type="button" @click="mode='ai-plus'" :disabled="!isPlusManualReady()"
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

                {{-- TAB: RISET BROKER --}}
                <div x-show="mode==='riset-broker'" class="p-6 space-y-5">
                    <div
                        class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Upload dokumen hasil riset broker sebagai referensi awal. Dokumen tambahan bisa diupload setelah
                        analisa tersimpan.
                    </div>
                    <div class="space-y-4">
                        <template x-for="(document, index) in brokerResearchDocuments" :key="document.key">
                            <div
                                class="grid gap-4 md:grid-cols-[1fr_1.5fr_auto] md:items-end border border-line rounded-lg p-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Broker</label>
                                    <input type="text" :name="`broker_research[${index}][broker]`"
                                        placeholder="Nama sekuritas"
                                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Dokumen Riset
                                        Broker</label>
                                    <input type="file" :name="`broker_research[${index}][document]`"
                                        accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                </div>
                                <button type="button" @click="removeBrokerResearchDocument(index)"
                                    x-show="brokerResearchDocuments.length > 1"
                                    class="px-3 py-2 text-sm font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">
                                    Hapus
                                </button>
                            </div>
                        </template>
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <p class="text-xs text-muted">Format PDF/DOCX. Maks 5MB per dokumen.</p>
                            <button type="button" @click="addBrokerResearchDocument()"
                                class="px-4 py-2 text-sm font-semibold text-primary border border-primary/30 rounded-lg hover:bg-primary/5 transition">
                                Tambah Dokumen
                            </button>
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
            @php
                $lapkeuFieldNames = ['current_asset', 'cash_equivalents', 'account_receivable', 'inventories', 'other_current_asset', 'fixed_asset', 'other_non_current_asset', 'total_asset', 'current_liabilities', 'account_payable', 'accruals', 'short_term_loans', 'current_maturities_of_long_term_loans', 'other_current_liabilities', 'long_term_loans', 'other_non_current_liabilities', 'total_non_current_liabilities', 'total_liabilities', 'share_capital', 'additional_paid_in_capital', 'retained_earning', 'others', 'non_controlling_interest', 'total_equity_equity_to_parent_entity', 'equity', 'net_revenue', 'cost_of_good_sold', 'gross_income', 'operational_expense', 'laba_operasional', 'other_income_expense', 'interest_expense', 'income_before_tax', 'taxes', 'ebit', 'ebitda', 'net_income_attributable_to_non_controlling_interest', 'net_income', 'eps', 'cash_flows_operating_activities', 'cash_flows_investment', 'cash_flows_financing'];
                $lapkeuData = [];
                foreach ($lapkeuFieldNames as $f) {
                    $lapkeuData[$f] = old($f, '');
                }
                $lapkeuData['nama_saham'] = old('nama_saham', '');
                $lapkeuData['jumlah_lembar_saham'] = old('jumlah_lembar_saham', '');
                $lapkeuData['harga_saham'] = old('harga_saham', '');
                $lapkeuData['q1_saham'] = old('q1_saham', '');
                $lapkeuData['q2_saham'] = old('q2_saham', '');
                $lapkeuData['q3_saham'] = old('q3_saham', '');
                $lapkeuData['q4_saham'] = old('q4_saham', '');
                $lapkeuData['kapitalisasi_pasar'] = old('kapitalisasi_pasar', '');

                $portofolioFields = ['kode_efek','nama_efek','sektor','bobot','nilai_pasar','harga_perolehan','persen_nab','ihsg_contribution','return_1m','return_3m','return_6m','return_1y','top_10'];
                $portofolioArray = collect(old('portofolio', $rdPortofolio->map(fn($p) => array_merge(array_fill_keys($portofolioFields, ''), ['top_10' => false], collect($p)->only($portofolioFields)->toArray()))->toArray()))->values();

                $likuiditasFields = ['kode_efek','nama_efek','rata_volume_transaksi_harian','volume_terendah','volume_saham','skenario_20_persen_reds','skenario_reds_closing_10','rasio_likuiditas_harian','rasio_likuiditas'];
                $likuiditasArray = collect(old('likuiditas', $rdLikuiditas->map(fn($l) => array_merge(array_fill_keys($likuiditasFields, ''), collect($l)->only($likuiditasFields)->toArray()))->toArray()))->values();

                $keuanganFields = ['kode_efek','nama_efek','per','pbv','roe','roa','npm','ev_ebitda','der','current_ratio','aktivitas_lancar','gross_profit_margin','operating_profit_margin'];
                $keuanganArray = collect(old('keuangan', $rdKeuangan->map(fn($k) => array_merge(array_fill_keys($keuanganFields, ''), collect($k)->only($keuanganFields)->toArray()))->toArray()))->values();
            @endphp

            function lapkeuForm(previewAiUrl, previewAiPlusUrl, parsePdfUrl, parsePdfVisionUrl, parsePdfStatusUrl) {
                @php
                    $plusLabels = [
                        'total_asset' => 'Total Aset',
                        'total_liabilities' => 'Total Liabilitas',
                        'equity' => 'Total Ekuitas',
                        'net_revenue' => 'Pendapatan Bersih',
                        'net_income' => 'Laba Bersih',
                    ];
                @endphp
                return {
                    mode: @json($errors->has('broker_research') ? 'riset-broker' : old('input_mode', 'manual')),
                    ...@json($lapkeuData),
                    portofolio: @json($portofolioArray),
                    likuiditas: @json($likuiditasArray),
                    keuangan: @json($keuanganArray),
                    formSubmitted: @json($errors->any()),
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
                    brokerResearchDocuments: [{
                        key: Date.now()
                    }],
                    plusRequiredLabels: @json($plusLabels),
                    parsePdfUrl: parsePdfUrl,
                    parsePdfVisionUrl: parsePdfVisionUrl,
                    parsePdfStatusUrl: parsePdfStatusUrl,

                    addBrokerResearchDocument() {
                        this.brokerResearchDocuments.push({
                            key: Date.now() + Math.random()
                        });
                    },

                    removeBrokerResearchDocument(index) {
                        if (this.brokerResearchDocuments.length > 1) {
                            this.brokerResearchDocuments.splice(index, 1);
                        }
                    },

                    onPdfSelected(event) {
                        this.aiPdfFile = event.target?.files?.[0] || null;
                        this.aiParseError = '';
                        this.aiParseSuccess = '';
                    },

                    fillLapkeuFormFromData(d) {
                        const set = (name, val) => {
                            const el = document.querySelector(`[name="${name}"]`);
                            if (el && val != null) el.value = val;
                        };
                        const setSelect = (name, val) => {
                            const sel = document.querySelector(`[name="${name}"]`);
                            if (sel && val) {
                                const sectorAliases = {
                                    financials: 'Keuangan',
                                    finance: 'Keuangan',
                                    banking: 'Perbankan',
                                    banks: 'Perbankan',
                                    energy: 'Energi',
                                    infrastructures: 'Infrastruktur',
                                    infrastructure: 'Infrastruktur',
                                    'basic materials': 'Industri Dasar',
                                    industrials: 'Industri Dasar',
                                    technology: 'Teknologi',
                                    'consumer cyclicals': 'Consumer Goods',
                                    'consumer non-cyclicals': 'Consumer Goods',
                                    'consumer goods': 'Consumer Goods',
                                    properties: 'Properti',
                                    'properties & real estate': 'Properti',
                                    transportation: 'Transportasi',
                                    'transportation & logistics': 'Transportasi',
                                    healthcare: 'Kesehatan',
                                };
                                const normalized = String(val).trim();
                                const target = sectorAliases[normalized.toLowerCase()] || normalized;
                                let option = [...sel.options].find(o => o.value.toLowerCase() === target.toLowerCase());
                                if (!option && name === 'sektor') {
                                    option = new Option(target, target, true, true);
                                    sel.add(option);
                                }
                                if (option) option.selected = true;
                            }
                        };
                        set('nama_perusahaan', d.nama_perusahaan);
                        set('kode_saham', d.kode_saham);
                        set('periode', d.periode);
                        set('periode_dari', d.periode_dari);
                        set('periode_sampai', d.periode_sampai);
                        setSelect('sektor', d.sektor);
                        setSelect('mata_uang', d.mata_uang);
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
                        numFields.forEach(f => set(f, d[f]));

                        if (Array.isArray(d.portofolio)) this.portofolio = d.portofolio;
                        if (Array.isArray(d.likuiditas)) this.likuiditas = d.likuiditas;
                        if (Array.isArray(d.keuangan)) this.keuangan = d.keuangan;
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
                        fd.append('_token', document.querySelector('meta[name=csrf-token]')?.content ||
                            '{{ csrf_token() }}');

                        const useVision = this.pdfScanMode === 'vision';
                        const url = useVision && this.parsePdfVisionUrl ? this.parsePdfVisionUrl : this.parsePdfUrl;

                        return fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json'
                                },
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
                                    return this.pollPdfExtraction(resp.poll_url || this.parsePdfStatusUrl.replace(
                                        '__UUID__', resp.extraction_id), options);
                                }

                                this.pdfStatus = resp.message || (useVision ? 'Scan AI selesai.' :
                                    'Ekstraksi PDF selesai.');
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

                        this.submitPdfExtraction(this.aiPdfFile, {
                                ai: true
                            })
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
                                this.fillLapkeuFormFromData(d);
                                this.aiParseLoading = false;
                                const nama = document.getElementById('nama_perusahaan')?.value?.trim();
                                if (!nama) {
                                    this.aiError =
                                        'Nama perusahaan tidak ditemukan di PDF. Isi manual di tab Input Manual.';
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
                                this.fillLapkeuFormFromData(d);
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
                        const nama = document.getElementById('nama_perusahaan')?.value?.trim();
                        if (!nama) {
                            this.aiError = 'Isi Nama Perusahaan di bagian Informasi Saham terlebih dahulu.';
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

                    isPlusManualReady() {
                        if (!this.previewAiPlusUrl) return false;
                        const required = ['total_asset', 'total_liabilities', 'equity', 'net_revenue', 'net_income'];
                        return required.every(f => {
                            const v = this.getFormValue(f);
                            return v !== '' && v != null && !isNaN(Number(v)) && Number(v) !== 0;
                        });
                    },

                    plusMissingList() {
                        const required = ['total_asset', 'total_liabilities', 'equity', 'net_revenue', 'net_income'];
                        return required.filter(f => {
                            const v = this.getFormValue(f);
                            return v === '' || v == null || isNaN(Number(v)) || Number(v) === 0;
                        }).map(f => this.plusRequiredLabels[f] || f);
                    },

                    runAiPlusPreview() {
                        if (!this.isPlusManualReady()) {
                            this.aiPlusError = 'Lengkapi semua data Input Manual terlebih dahulu.';
                            return;
                        }
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
                            })
                            .catch(e => {
                                this.aiPlusError = e.message || 'Gagal memproses';
                            })
                            .finally(() => {
                                this.aiPlusLoading = false;
                            });
                    },
                };
            }
        </script>

        <script>
            window.lookupStockUrl = @json($lookupStockRoute);

            window.lookupStock = async function(query) {
                if (!query || query.trim().length < 1) return [];
                try {
                    const r = await fetch(window.lookupStockUrl + '?q=' + encodeURIComponent(query.trim()), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    return await r.json();
                } catch (e) {
                    return [];
                }
            };

            window.sahamPembandingManager = function(initialData) {
                return {
                    search: '',
                    results: [],
                    list: Array.isArray(initialData) && initialData.length ? initialData : [],
                    searchStock() {
                        if (this.search.length < 1) { this.results = []; return; }
                        window.lookupStock(this.search).then(r => this.results = r);
                    },
                    addStock(stock) {
                        if (!this.list.find(s => s.kode === stock.kode)) {
                            this.list.push({ kode: stock.kode, nama: stock.nama, sektor: stock.sektor });
                        }
                        this.search = '';
                        this.results = [];
                    },
                    removeStock(index) {
                        this.list.splice(index, 1);
                    }
                };
            };

            window.selectStock = function(stock) {
                const set = (name, val) => {
                    const el = document.querySelector(`[name="${name}"]`);
                    if (el && val != null) el.value = val;
                };
                const setSelect = (name, val) => {
                    const sel = document.querySelector(`[name="${name}"]`);
                    if (sel && val) {
                        const sectorAliases = {
                            financials: 'Keuangan',
                            finance: 'Keuangan',
                            banking: 'Perbankan',
                            banks: 'Perbankan',
                            energy: 'Energi',
                            infrastructures: 'Infrastruktur',
                            infrastructure: 'Infrastruktur',
                            'basic materials': 'Industri Dasar',
                            industrials: 'Industri Dasar',
                            technology: 'Teknologi',
                            'consumer cyclicals': 'Consumer Goods',
                            'consumer non-cyclicals': 'Consumer Goods',
                            'consumer goods': 'Consumer Goods',
                            properties: 'Properti',
                            'properties & real estate': 'Properti',
                            transportation: 'Transportasi',
                            'transportation & logistics': 'Transportasi',
                            healthcare: 'Kesehatan',
                        };
                        const normalized = String(val).trim().toLowerCase();
                        const target = sectorAliases[normalized] || normalized;
                        let option = [...sel.options].find(o => o.value.toLowerCase() === target.toLowerCase());
                        if (!option && name === 'sektor') {
                            option = new Option(target, target, true, true);
                            sel.add(option);
                        }
                        if (option) option.selected = true;
                    }
                };
                set('kode_saham', stock.kode);
                set('nama_perusahaan', stock.nama);
                setSelect('sektor', stock.sektor);
                setSelect('mata_uang', 'IDR');

                if (stock.nama) set('nama_saham', stock.nama);
                if (stock.harga_terbaru) set('harga_saham', stock.harga_terbaru);
                if (stock.jumlah_saham) set('jumlah_lembar_saham', stock.jumlah_saham);
                if (stock.market_capital) set('kapitalisasi_pasar', stock.market_capital);

                // Sync Alpine x-model bindings after direct DOM updates
                ['kode_saham', 'nama_perusahaan', 'nama_saham', 'harga_saham', 'jumlah_lembar_saham', 'kapitalisasi_pasar'].forEach(name => {
                    const el = document.querySelector(`[name="${name}"]`);
                    if (el) el.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                });
            };
        </script>
    @endpush
@endsection
