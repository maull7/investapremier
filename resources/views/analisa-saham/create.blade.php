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

        <form id="lapkeu-form" method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" class="space-y-6">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="nama_perusahaan" id="nama_perusahaan"
                            value="{{ old('nama_perusahaan') }}" required
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Saham</label>
                        <input type="text" name="kode_saham" value="{{ old('kode_saham') }}" placeholder="cth: BBCA"
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm uppercase">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periode (cth: Q1 2025, 2024)</label>
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
                    @include('analisa-saham.partials.form-neraca')
                    @include('analisa-saham.partials.form-laba-rugi')
                    @include('analisa-saham.partials.form-arus-kas')
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
    @endpush
@endsection
