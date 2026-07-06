@extends('layouts.admin')

@section('title', $fund->nama_reksa_dana . ' - Detail Reksa Dana')

@section('content')
<div x-data="{
    tab: {{ Js::from(request('tab', 'snapshot')) }},
    personModal: { open: false, loading: false, error: null, data: null },
    parserLocks: @js($fund->parser_locks ?? []),
    toggling: false,
    editModal: null,
    editData: {},
    editSaving: false,
    openEdit(section) {
        this.editData = {};
        if (section === 'ringkasan') {
            this.editData = { nab_per_unit: @js($fund->nab_per_unit), tanggal_nab: @js($fund->tanggal_nab?->format('Y-m-d')), aum: @js($fund->aum), total_unit: @js($fund->total_unit) };
        } else if (section === 'risiko') {
            this.editData = {
                risk_category: @js($fund->risk_category),
                sharpe_ratio_1y: @js($fund->sharpe_ratio_1y), sharpe_ratio_3y: @js($fund->sharpe_ratio_3y), sharpe_ratio_5y: @js($fund->sharpe_ratio_5y),
                stdev_1y: @js($fund->stdev_1y), stdev_3y: @js($fund->stdev_3y), stdev_5y: @js($fund->stdev_5y),
                beta_1y: @js($fund->beta_1y), beta_3y: @js($fund->beta_3y), beta_5y: @js($fund->beta_5y),
                max_drawdown_1y: @js($fund->max_drawdown_1y), max_drawdown_3y: @js($fund->max_drawdown_3y), max_drawdown_5y: @js($fund->max_drawdown_5y),
            };
        } else if (section === 'biaya') {
            this.editData = {
                subscription_fee: @js($fund->subscription_fee), redemption_fee: @js($fund->redemption_fee), switching_fee: @js($fund->switching_fee),
                management_fee: @js($fund->management_fee), custodian_fee: @js($fund->custodian_fee),
                expense_ratio: @js($fund->expense_ratio), investment_manager_fee: @js($fund->investment_manager_fee),
                minimum_subscription: @js($fund->minimum_subscription), minimum_topup: @js($fund->minimum_topup), minimum_redemption: @js($fund->minimum_redemption),
            };
        } else if (section === 'portofolio') {
            if (!this.portfolioMonth || !this.portfolioYear) return;
        }
        this.editModal = section;
    },
    async submitEdit(section) {
        if (this.editSaving) return;
        this.editSaving = true;
        try {
            const res = await fetch('{{ route('admin.daftar-reksa-dana.update-info', $fund) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify(this.editData),
            });
            const json = await res.json();
            if (json.success) location.reload();
        } catch (e) {}
        this.editSaving = false;
    },
    isLocked(section) { return this.parserLocks.includes(section) },
    async toggleLock(section) {
        if (this.toggling) return;
        this.toggling = true;
        try {
            const res = await fetch('{{ route('admin.daftar-reksa-dana.toggle-parser-lock', $fund) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ section }),
            });
            const json = await res.json();
            if (json.success) this.parserLocks = json.parser_locks;
        } catch (e) {}
        this.toggling = false;
    },
    async openPerson(name) {
        this.personModal = { open: true, loading: true, error: null, data: null };
        try {
            const url = '{{ route('admin.investment-person-roles.detail') }}?name=' + encodeURIComponent(name);
            const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            if (!res.ok) {
                this.personModal.error = json.message || 'Gagal mengambil detail.';
                return;
            }
            this.personModal.data = json;
        } catch (e) {
            this.personModal.error = e.message;
        } finally {
            this.personModal.loading = false;
        }
    },
    portfolioMonth: '',
    portfolioYear: '',
    portfolioSaving: false,
    portfolioSaham: 0,
    portfolioObligasi: 0,
    portfolioPasarUang: 0,
    portfolioKas: 0,
    portfolioTopHoldings: '',
    portfolioNabPerUnit: null,
    portfolioTanggalNab: '',
    portfolioAum: null,
    portfolioTotalUnit: null,
    portfolioReturnYtd: null,
    portfolioReturn1y: null,
    portfolioReturn1m: null,
    portfolioReturnInception: null,
    portfolioSuccess: null,
    portfolioError: null,
    loadPortfolioData() {
        if (!this.portfolioMonth || !this.portfolioYear) return;
        const period = `${this.portfolioYear}-${String(this.portfolioMonth).padStart(2,'0')}-01`;
        fetch('{{ route('admin.daftar-reksa-dana.show', $fund) }}?tab=portofolio&period=' + period, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(data => {
            if (data.aa) {
                this.portfolioSaham = data.aa.equity_percent;
                this.portfolioObligasi = data.aa.bond_percent;
                this.portfolioPasarUang = data.aa.money_market_percent;
                this.portfolioKas = data.aa.cash_percent;
            }
            this.portfolioTopHoldings = data.top_holdings_text || '';
            this.portfolioNabPerUnit = data.nab_per_unit;
            this.portfolioTanggalNab = data.tanggal_nab;
            this.portfolioAum = data.aum;
            this.portfolioTotalUnit = data.total_unit;
            this.portfolioReturnYtd = data.return_ytd;
            this.portfolioReturn1y = data.return_1y;
            this.portfolioReturn1m = data.return_1m;
            this.portfolioReturnInception = data.return_inception;
        }).catch(() => {});
    },
    async savePortfolio() {
        if (this.portfolioSaving) return;
        this.portfolioSaving = true;
        this.portfolioSuccess = null;
        this.portfolioError = null;
        try {
            const res = await fetch('{{ route('admin.daftar-reksa-dana.save-portfolio', $fund) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({
                    month: this.portfolioMonth,
                    year: this.portfolioYear,
                    saham: this.portfolioSaham,
                    obligasi: this.portfolioObligasi,
                    pasar_uang: this.portfolioPasarUang,
                    kas: this.portfolioKas,
                    top_holdings: this.portfolioTopHoldings,
                    nab_per_unit: this.portfolioNabPerUnit,
                    tanggal_nab: this.portfolioTanggalNab,
                    aum: this.portfolioAum,
                    total_unit: this.portfolioTotalUnit,
                    return_ytd: this.portfolioReturnYtd,
                    return_1y: this.portfolioReturn1y,
                    return_1m: this.portfolioReturn1m,
                    return_inception: this.portfolioReturnInception,
                }),
            });
            const json = await res.json();
            if (json.success) {
                this.portfolioSuccess = json.message || 'Data portfolio berhasil disimpan.';
                setTimeout(() => location.reload(), 1000);
            } else {
                this.portfolioError = json.message || 'Gagal menyimpan.';
            }
        } catch (e) {
            this.portfolioError = e.message;
        }
        this.portfolioSaving = false;
    },
}">

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-muted mb-3">
        <a href="{{ route('admin.daftar-reksa-dana.index') }}" class="hover:text-primary transition">Daftar Reksa Dana</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary font-medium">{{ $fund->nama_reksa_dana }}</span>
    </div>
    <h1 class="page-title">{{ $fund->nama_reksa_dana }}</h1>
    <form method="POST" action="{{ route('admin.daftar-reksa-dana.export-investment-manager', $fund) }}" class="mt-3">
        @csrf
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">
            Export ke Data Manajer Investasi
        </button>
    </form>
    <div class="flex flex-wrap gap-3 mt-2 text-sm text-muted">
        @if($fund->kode_reksa_dana)<span class="font-mono text-xs bg-[#f1f5f9] px-2 py-1 rounded">{{ $fund->kode_reksa_dana }}</span>@endif
        @if($fund->nama_manajer_investasi)<span>{{ $fund->nama_manajer_investasi }}</span>@endif
        @if($fund->jenis)<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">{{ $fund->jenis }}</span>@endif
        @if($fund->risk_category)<span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $fund->risk_category == 'Rendah' ? 'bg-green-100 text-green-700' : ($fund->risk_category == 'Sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $fund->risk_category }}</span>@endif
        @if($fund->tanggal_nab)<span>Data: {{ $fund->tanggal_nab->format('d M Y') }}</span>@endif
    </div>
    <div class="flex items-center gap-3 mt-3 flex-wrap">
        <span class="text-xs font-semibold text-muted">Data Portfolio:</span>
        <select x-model="portfolioMonth" @change="loadPortfolioData" class="border border-line rounded-lg px-3 py-1.5 text-xs text-muted">
            <option value="">Bulan</option>
            @foreach(range(1, 12) as $m)
            <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
            @endforeach
        </select>
        <select x-model="portfolioYear" @change="loadPortfolioData" class="border border-line rounded-lg px-3 py-1.5 text-xs text-muted">
            <option value="">Tahun</option>
            @foreach(range(now()->year, now()->year - 10) as $y)
            <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </select>
        <button @click="savePortfolio" :disabled="portfolioSaving"
            class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
            <span x-text="portfolioSaving ? 'Menyimpan...' : 'Simpan Portfolio'"></span>
        </button>
        <template x-if="portfolioSuccess">
            <span class="text-xs text-green-600 font-semibold" x-text="portfolioSuccess"></span>
        </template>
        <template x-if="portfolioError">
            <span class="text-xs text-red-600 font-semibold" x-text="portfolioError"></span>
        </template>
    </div>
</div>

{{-- Ringkasan --}}
<div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">NAV / NAB-UP</p>
        <p class="text-sm font-bold text-primary">{{ $latestNav ? number_format($latestNav->nab_per_unit, 4, ',', '.') : ($fund->nab_per_unit ? number_format($fund->nab_per_unit, 4, ',', '.') : '—') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">Return Harian</p>
        <p class="text-sm font-bold {{ $returnDaily !== null ? ($returnDaily >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $returnDaily !== null ? number_format($returnDaily, 2, ',', '.') . '%' : '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">Return Bulanan</p>
        <p class="text-sm font-bold {{ $returnMonthly !== null ? ($returnMonthly >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $returnMonthly !== null ? number_format($returnMonthly, 2, ',', '.') . '%' : '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">Return Tahunan</p>
        <p class="text-sm font-bold {{ $returnYearly !== null ? ($returnYearly >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $returnYearly !== null ? number_format($returnYearly, 2, ',', '.') . '%' : '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">AUM</p>
        <p class="text-sm font-bold text-primary">{{ $latestNav && $latestNav->aum ? 'Rp' . number_format($latestNav->aum, 0, ',', '.') : ($fund->aum ? 'Rp' . number_format($fund->aum, 0, ',', '.') : '—') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted mb-1">Unit Penyertaan</p>
        <p class="text-sm font-bold text-primary">{{ $latestNav && $latestNav->unit_participation ? number_format($latestNav->unit_participation, 0, ',', '.') : ($fund->total_unit ? number_format($fund->total_unit, 0, ',', '.') : '—') }}</p>
    </div>
</div>

{{-- Tab Navigation --}}
<div class="flex items-center gap-1 mb-6 border-b border-line overflow-x-auto">
    <button @click="tab = 'snapshot'" :class="tab === 'snapshot' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Snapshot</button>
    <button @click="tab = 'grafik'" :class="tab === 'grafik' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Grafik dan Data</button>
    <button @click="tab = 'risiko'" :class="tab === 'risiko' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Risiko</button>
    <button @click="tab = 'biaya'" :class="tab === 'biaya' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Biaya</button>
    <button @click="tab = 'portofolio'" :class="tab === 'portofolio' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">Portofolio</button>
    <button @click="tab = 'pdf-prospektus'" :class="tab === 'pdf-prospektus' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">PDF Prospektus</button>
    <button @click="tab = 'pdf-ffs'" :class="tab === 'pdf-ffs' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
            class="px-5 py-3 text-sm font-semibold border-b-2 transition whitespace-nowrap">PDF FFS</button>
</div>

{{-- TAB: SNAPSHOT --}}
<div x-show="tab === 'snapshot'" x-cloak>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Informasi Reksa Dana --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                <h2 class="font-bold text-white text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Informasi Reksa Dana
                </h2>
                <div class="flex items-center gap-2">
                    <button @click="toggleLock('info')" :disabled="toggling"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                        :class="isLocked('info') ? 'bg-amber-400/30 text-amber-100 hover:bg-amber-400/40' : 'bg-white/20 text-white hover:bg-white/30'">
                        <template x-if="isLocked('info')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></template>
                        <template x-if="!isLocked('info')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></template>
                        <span x-text="isLocked('info') ? 'Terkunci' : 'Lock Parser'"></span>
                    </button>
                    <a href="{{ route('admin.daftar-reksa-dana.edit', $fund) }}"
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                </div>
            </div>
            <div class="divide-y divide-line">
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Nama Reksa Dana</span><span class="text-sm">{{ $fund->nama_reksa_dana }}</span></div>
                @if($fund->kode_reksa_dana)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kode Reksa Dana</span><span class="text-sm font-mono">{{ $fund->kode_reksa_dana }}</span></div>@endif
                @if($fund->investmentManager || $fund->nama_manajer_investasi)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Manajer Investasi</span><span class="text-sm">@if($fund->investmentManager)<a href="{{ route('admin.investment-managers.show', $fund->investmentManager) }}" class="text-accent hover:underline">{{ $fund->nama_manajer_investasi }}</a>@else{{ $fund->nama_manajer_investasi }}@endif</span></div>@endif
                @if($fund->custodian_bank)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Bank Kustodian</span><span class="text-sm">{{ $fund->custodian_bank }}</span></div>@endif
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Tanggal Efektif</span><span class="text-sm">{{ $fund->launch_date?->format('d M Y') ?: '-' }}</span></div>
                @if($fund->tujuan_investasi)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Tujuan Investasi</span><span class="text-sm">{{ $fund->tujuan_investasi }}</span></div>@endif
                @if($fund->kebijakan_investasi)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kebijakan Investasi</span><span class="text-sm">{{ $fund->kebijakan_investasi }}</span></div>@endif
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Benchmark Tolak Ukur</span><span class="text-sm">{{ $fund->benchmark ?: '-' }}</span></div>
                @if($fund->display_mata_uang)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Mata Uang</span><span class="text-sm">{{ $fund->display_mata_uang }}</span></div>@endif
                @if($fund->jenis)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kategori</span><span class="text-sm">{{ $fund->kategori_label ?: $fund->jenis }}</span></div>@endif
                @if($fund->jenis)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Jenis Reksa Dana</span><span class="text-sm">{{ $fund->jenis }}</span></div>@endif
                @if($fund->kategori_produk)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kategori Produk</span><span class="text-sm">{{ $fund->kategori_produk }}</span></div>@endif
                @if($fund->display_kelas)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kelas</span><span class="text-sm">{{ $fund->display_kelas }}</span></div>@endif
                @if($fund->isin_code)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">ISIN Code</span><span class="text-sm font-mono">{{ $fund->isin_code }}</span></div>@endif
                @if($fund->is_etf !== null)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">ETF</span><span class="text-sm">{{ $fund->is_etf ? 'Ya' : 'Tidak' }}</span></div>@endif
                @if($fund->is_index !== null)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Index Fund</span><span class="text-sm">{{ $fund->is_index ? 'Ya' : 'Tidak' }}</span></div>@endif
                @if($fund->conservative_category)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Kategori Konservatif</span><span class="text-sm">{{ $fund->conservative_category }}</span></div>@endif
                @if($fund->dividend !== null)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Dividen</span><span class="text-sm">{{ $fund->dividend ? 'Ya' : 'Tidak' }}</span></div>@endif
            </div>
        </div>

        {{-- Ringkasan Kinerja --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                <h2 class="font-bold text-white text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Ringkasan Kinerja
                </h2>
                <button @click="openEdit('ringkasan')"
                    class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </button>
            </div>
            <div class="divide-y divide-line">
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">NAV / NAB-UP</span><span class="text-sm font-bold text-primary">{{ $latestNav ? number_format($latestNav->nab_per_unit, 4, ',', '.') : ($fund->nab_per_unit ? number_format($fund->nab_per_unit, 4, ',', '.') : '—') }}</span></div>
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Return Harian</span><span class="text-sm font-bold {{ $returnDaily !== null ? ($returnDaily >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $returnDaily !== null ? number_format($returnDaily, 2, ',', '.') . '%' : '—' }}</span></div>
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Return Bulanan</span><span class="text-sm font-bold {{ $returnMonthly !== null ? ($returnMonthly >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $returnMonthly !== null ? number_format($returnMonthly, 2, ',', '.') . '%' : '—' }}</span></div>
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Return Tahunan</span><span class="text-sm font-bold {{ $returnYearly !== null ? ($returnYearly >= 0 ? 'text-green-600' : 'text-red-600') : 'text-muted' }}">{{ $returnYearly !== null ? number_format($returnYearly, 2, ',', '.') . '%' : '—' }}</span></div>
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">AUM</span><span class="text-sm font-bold text-primary">{{ $latestNav && $latestNav->aum ? 'Rp' . number_format($latestNav->aum, 0, ',', '.') : ($fund->aum ? 'Rp' . number_format($fund->aum, 0, ',', '.') : '—') }}</span></div>
                <div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-36 shrink-0">Unit Penyertaan</span><span class="text-sm font-bold text-primary">{{ $latestNav && $latestNav->unit_participation ? number_format($latestNav->unit_participation, 0, ',', '.') : ($fund->total_unit ? number_format($fund->total_unit, 0, ',', '.') : '—') }}</span></div>
            </div>
        </div>
    </div>

    {{-- Deskripsi --}}
    @if($fund->description)
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm">Deskripsi Reksa Dana</h2>
        </div>
        <div class="px-6 py-4 text-sm whitespace-pre-line">{{ $fund->description }}</div>
    </div>
    @endif

    {{-- Komite Investasi & Tim Pengelola --}}
    @php
        $committees = $fund->managementTeams->where('type', 'committee');
        $investmentManagers = $fund->managementTeams->where('type', 'investment_manager');
        $mi = $fund->investmentManager;
    @endphp
    @if($committees->isNotEmpty() || $investmentManagers->isNotEmpty() || $mi)
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm">Komite Investasi & Tim Pengelola</h2>
        </div>
        @if($committees->isNotEmpty())
        <div class="px-6 py-3 border-b border-line bg-[#f8fafc]">
            <h3 class="font-semibold text-primary text-xs">Komite Investasi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach($committees as $mt)
                    <tr class="hover:bg-[#f8fafc]"><td class="px-4 py-3 text-xs"><button type="button" @click="openPerson({{ Js::from($mt->name) }})" class="text-accent hover:underline text-left font-semibold">{{ $mt->name }}</button></td><td class="px-4 py-3 text-xs text-muted">{{ $mt->position ?? '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @elseif($mi && $mi->investment_committee)
        @php $icLines = preg_split('/\n+/', trim($mi->investment_committee)); @endphp
        <div class="px-6 py-3 border-b border-line bg-[#f8fafc]">
            <h3 class="font-semibold text-primary text-xs">Komite Investasi (dari Manajer Investasi)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach($icLines as $line)
                    @php
                        $line = trim($line); if(!$line) continue;
                        $parts = preg_split('/\s*(?:-|:|–)\s*/', $line, 2);
                        $name = trim($parts[0]); $pos = trim($parts[1] ?? '');
                    @endphp
                    <tr class="hover:bg-[#f8fafc]"><td class="px-4 py-3 text-xs"><button type="button" @click="openPerson({{ Js::from($name) }})" class="text-accent hover:underline text-left font-semibold">{{ $name }}</button></td><td class="px-4 py-3 text-xs text-muted">{{ $pos ?: '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        @if($investmentManagers->isNotEmpty())
        <div class="px-6 py-3 border-b border-line bg-[#f8fafc]">
            <h3 class="font-semibold text-primary text-xs">Tim Pengelola Investasi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach($investmentManagers as $mt)
                    <tr class="hover:bg-[#f8fafc]"><td class="px-4 py-3 text-xs"><button type="button" @click="openPerson({{ Js::from($mt->name) }})" class="text-accent hover:underline text-left font-semibold">{{ $mt->name }}</button></td><td class="px-4 py-3 text-xs text-muted">{{ $mt->position ?? '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @elseif($mi && $mi->investment_management_team)
        @php $tmlLines = preg_split('/\n+/', trim($mi->investment_management_team)); @endphp
        <div class="px-6 py-3 border-b border-line bg-[#f8fafc]">
            <h3 class="font-semibold text-primary text-xs">Tim Pengelola Investasi (dari Manajer Investasi)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach($tmlLines as $line)
                    @php
                        $line = trim($line); if(!$line) continue;
                        $parts = preg_split('/\s*(?:-|:|–)\s*/', $line, 2);
                        $name = trim($parts[0]); $pos = trim($parts[1] ?? '');
                    @endphp
                    <tr class="hover:bg-[#f8fafc]"><td class="px-4 py-3 text-xs"><button type="button" @click="openPerson({{ Js::from($name) }})" class="text-accent hover:underline text-left font-semibold">{{ $name }}</button></td><td class="px-4 py-3 text-xs text-muted">{{ $pos ?: '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif
    @if($committees->isEmpty() && $investmentManagers->isEmpty() && !$mi && !$fund->description)
    <div class="py-12 text-center text-muted bg-white rounded-2xl border border-line mt-6">
        <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm">Informasi reksa dana belum tersedia.</p>
    </div>
    @endif
</div>

{{-- TAB: GRAFIK DAN DATA --}}
<div x-show="tab === 'grafik'" x-cloak>
    @php
        $rangeOptions = ['1m'=>'1 Bulan','3m'=>'3 Bulan','6m'=>'6 Bulan','ytd'=>'YTD','1y'=>'1 Tahun','3y'=>'3 Tahun','5y'=>'5 Tahun','all'=>'All'];
        $aumPointCount = collect($chartData['aum']['series'])->sum(fn($series) => count($series['data']));
        $upPointCount = collect($chartData['up']['series'])->sum(fn($series) => count($series['data']));
        $navPointCount = collect($chartData['nav']['series'])->sum(fn($series) => count($series['data']));
    @endphp

    <div class="mb-4 space-y-3">
        <div class="flex flex-wrap items-center gap-2">
            @foreach($rangeOptions as $k=>$l)
                <a href="{{ route('admin.daftar-reksa-dana.show', ['reksaDana' => $fund, 'tab' => 'grafik', 'range' => $k]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $range === $k && !request()->filled('from_date') && !request()->filled('to_date') ? 'bg-primary text-white' : 'border border-line text-muted hover:bg-[#f1f5f9]' }}">{{ $l }}</a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.daftar-reksa-dana.show', $fund) }}"
            class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="tab" value="grafik">
            <div>
                <label class="block text-xs text-muted mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                    class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            </div>
            <div>
                <label class="block text-xs text-muted mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                    class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            </div>
            <button type="submit"
                class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Terapkan</button>
            @if(request()->filled('from_date') || request()->filled('to_date'))
                <a href="{{ route('admin.daftar-reksa-dana.show', ['reksaDana' => $fund, 'tab' => 'grafik', 'range' => $range]) }}"
                    class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>
            @endif
        </form>
    </div>

    @if(!$chartData['has_data'])
    <div class="py-12 text-center text-muted bg-white rounded-2xl border border-line">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        <p class="font-medium">Belum terdapat data historis untuk ditampilkan.</p>
    </div>
    @else
    <div class="space-y-6">
        @if($aumPointCount > 0)
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">AUM Bulanan</h3>
                <div id="chartAum" class="min-h-[320px]"></div>
            </div>
        @endif
        @if($upPointCount > 0)
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">Total UP Bulanan</h3>
                <div id="chartUp" class="min-h-[320px]"></div>
            </div>
        @endif
        @if($navPointCount > 0)
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">NAB/UP Harian</h3>
                <div id="chartNav" class="min-h-[340px]"></div>
            </div>
        @endif
    </div>

    {{-- Tabel Historis --}}
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
            <h2 class="font-bold text-white text-sm">Riwayat NAV / AUM / Unit Penyertaan</h2>
            <div class="flex items-center gap-2">
                <button @click="toggleLock('ringkasan')" :disabled="toggling"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                    :class="isLocked('ringkasan') ? 'bg-amber-400/30 text-amber-100 hover:bg-amber-400/40' : 'bg-white/20 text-white hover:bg-white/30'">
                    <template x-if="isLocked('ringkasan')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></template>
                    <template x-if="!isLocked('ringkasan')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></template>
                    <span x-text="isLocked('ringkasan') ? 'Terkunci' : 'Lock Parser'"></span>
                </button>
                <button @click="openEdit('ringkasan')"
                    class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Tanggal</th><th class="px-4 py-3 font-semibold text-right">NAB/UP</th><th class="px-4 py-3 font-semibold text-right">AUM</th><th class="px-4 py-3 font-semibold text-right">Unit Penyertaan</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @forelse($navHistory as $nh)
                    <tr class="hover:bg-[#f8fafc] transition-colors">
                        <td class="px-4 py-3 text-xs text-muted">{{ $nh->tanggal->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-xs text-right font-semibold text-primary tabular-nums">{{ number_format($nh->nab_per_unit, 4, ',', '.') }}</td>
                        <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $nh->aum ? 'Rp'.number_format($nh->aum, 0, ',', '.') : '—' }}</td>
                        <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $nh->unit_participation ? number_format($nh->unit_participation, 0, ',', '.') : '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-muted"><p class="font-medium">Belum ada data historis</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartData = @json($chartData);
        const formatNumber = value => Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 4 });
        const formatRupiah = value => {
            const n = Number(value || 0);
            if (Math.abs(n) >= 1_000_000_000_000) return 'Rp ' + (n / 1_000_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' T';
            if (Math.abs(n) >= 1_000_000_000) return 'Rp ' + (n / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' M';
            return 'Rp ' + n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
        };
        const formatUnit = value => {
            const n = Number(value || 0);
            if (Math.abs(n) >= 1_000_000_000) return (n / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Miliar';
            if (Math.abs(n) >= 1_000_000) return (n / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Juta';
            return n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
        };
        const baseOptions = (series, formatter, csvName) => ({
            chart: {
                type: 'line',
                height: 320,
                toolbar: { show: true, tools: { download: true, selection: true, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true }, export: { csv: { filename: csvName }, png: { filename: csvName } } },
                zoom: { enabled: true, type: 'x' }
            },
            series,
            stroke: { curve: 'smooth', width: 2.5 },
            markers: { size: 3, hover: { size: 5 } },
            dataLabels: { enabled: false },
            legend: { show: true, position: 'top', horizontalAlign: 'left' },
            xaxis: { type: 'datetime', labels: { datetimeUTC: false, format: 'dd MMM yyyy' } },
            yaxis: { labels: { formatter } },
            tooltip: { shared: true, x: { format: 'dd MMM yyyy' }, y: { formatter } },
            grid: { borderColor: '#e2e8f0' },
            colors: ['#2563eb', '#059669'],
        });

        if (document.getElementById('chartAum')) {
            new ApexCharts(document.getElementById('chartAum'), baseOptions(chartData.aum.series, formatRupiah, 'aum-bulanan-reksa-dana')).render();
        }
        if (document.getElementById('chartUp')) {
            new ApexCharts(document.getElementById('chartUp'), baseOptions(chartData.up.series, formatUnit, 'total-up-bulanan-reksa-dana')).render();
        }
        if (document.getElementById('chartNav')) {
            new ApexCharts(document.getElementById('chartNav'), baseOptions(chartData.nav.series, formatNumber, 'nab-up-harian-reksa-dana')).render();
        }
    });
    </script>
    @endif
</div>

{{-- TAB: RISIKO --}}
<div x-show="tab === 'risiko'" x-cloak>
    <div class="space-y-6">
        {{-- Risk Category --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                <h2 class="font-bold text-white text-sm">Tingkat Risiko</h2>
                <div class="flex items-center gap-2">
                    <button @click="toggleLock('risiko')" :disabled="toggling"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                        :class="isLocked('risiko') ? 'bg-amber-400/30 text-amber-100 hover:bg-amber-400/40' : 'bg-white/20 text-white hover:bg-white/30'">
                        <template x-if="isLocked('risiko')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></template>
                        <template x-if="!isLocked('risiko')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></template>
                        <span x-text="isLocked('risiko') ? 'Terkunci' : 'Lock Parser'"></span>
                    </button>
                    <button @click="openEdit('risiko')"
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </button>
                </div>
            </div>
            @if($fund->risk_category || $fund->conservative_category)
            <div class="divide-y divide-line">
                @if($fund->risk_category)
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-40 shrink-0">Risk Category</span>
                    @php
                        $riskLabel = match($fund->risk_category) {
                            'Rendah' => 'Risiko Rendah',
                            'Sedang' => 'Risiko Menengah',
                            'Tinggi' => 'Risiko Tinggi',
                            default => $fund->risk_category,
                        };
                    @endphp
                    <span class="text-sm px-2 py-0.5 rounded-full text-xs font-semibold {{ $fund->risk_category == 'Rendah' ? 'bg-green-100 text-green-700' : ($fund->risk_category == 'Sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $riskLabel }}</span>
                </div>
                @endif
                @if($fund->conservative_category)
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-40 shrink-0">Kategori Konservatif</span>
                    <span class="text-sm">{{ $fund->conservative_category }}</span>
                </div>
                @endif
            </div>
            @else
            <div class="py-12 text-center text-muted text-sm">
                <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                Data risiko belum tersedia.
            </div>
            @endif
        </div>

        {{-- Risk Metrics (Pasardana) --}}
        @php
            $hasRiskMetrics = collect($riskMetrics)->filter()->isNotEmpty();
        @endphp
        @if($hasRiskMetrics)
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                <h2 class="font-bold text-white text-sm">Metrik Risiko</h2>
                <div class="flex items-center gap-2">
                    <button @click="toggleLock('risiko')" :disabled="toggling"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                        :class="isLocked('risiko') ? 'bg-amber-400/30 text-amber-100 hover:bg-amber-400/40' : 'bg-white/20 text-white hover:bg-white/30'">
                        <template x-if="isLocked('risiko')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></template>
                        <template x-if="!isLocked('risiko')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></template>
                        <span x-text="isLocked('risiko') ? 'Terkunci' : 'Lock Parser'"></span>
                    </button>
                    <button @click="openEdit('risiko')"
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                            <th class="px-4 py-3 font-semibold">Metrik</th>
                            <th class="px-4 py-3 font-semibold text-right">1 Tahun</th>
                            <th class="px-4 py-3 font-semibold text-right">3 Tahun</th>
                            <th class="px-4 py-3 font-semibold text-right">5 Tahun</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @if($riskMetrics['sharpe_ratio_1y'] !== null || $riskMetrics['sharpe_ratio_3y'] !== null || $riskMetrics['sharpe_ratio_5y'] !== null)
                        <tr class="hover:bg-[#f8fafc]">
                            <td class="px-4 py-3 text-xs font-semibold">Sharpe Ratio</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['sharpe_ratio_1y'] !== null ? number_format($riskMetrics['sharpe_ratio_1y'], 4, ',', '.') : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['sharpe_ratio_3y'] !== null ? number_format($riskMetrics['sharpe_ratio_3y'], 4, ',', '.') : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['sharpe_ratio_5y'] !== null ? number_format($riskMetrics['sharpe_ratio_5y'], 4, ',', '.') : '—' }}</td>
                        </tr>
                        @endif
                        @if($riskMetrics['stdev_1y'] !== null || $riskMetrics['stdev_3y'] !== null || $riskMetrics['stdev_5y'] !== null)
                        <tr class="hover:bg-[#f8fafc]">
                            <td class="px-4 py-3 text-xs font-semibold">Std. Deviasi</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['stdev_1y'] !== null ? number_format($riskMetrics['stdev_1y'] * 100, 2, ',', '.') . '%' : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['stdev_3y'] !== null ? number_format($riskMetrics['stdev_3y'] * 100, 2, ',', '.') . '%' : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['stdev_5y'] !== null ? number_format($riskMetrics['stdev_5y'] * 100, 2, ',', '.') . '%' : '—' }}</td>
                        </tr>
                        @endif
                        @if($riskMetrics['beta_1y'] !== null || $riskMetrics['beta_3y'] !== null || $riskMetrics['beta_5y'] !== null)
                        <tr class="hover:bg-[#f8fafc]">
                            <td class="px-4 py-3 text-xs font-semibold">Beta</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['beta_1y'] !== null ? number_format($riskMetrics['beta_1y'], 4, ',', '.') : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['beta_3y'] !== null ? number_format($riskMetrics['beta_3y'], 4, ',', '.') : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['beta_5y'] !== null ? number_format($riskMetrics['beta_5y'], 4, ',', '.') : '—' }}</td>
                        </tr>
                        @endif
                        @if($riskMetrics['max_drawdown_1y'] !== null || $riskMetrics['max_drawdown_3y'] !== null || $riskMetrics['max_drawdown_5y'] !== null)
                        <tr class="hover:bg-[#f8fafc]">
                            <td class="px-4 py-3 text-xs font-semibold">Max Drawdown</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['max_drawdown_1y'] !== null ? number_format($riskMetrics['max_drawdown_1y'] * 100, 2, ',', '.') . '%' : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['max_drawdown_3y'] !== null ? number_format($riskMetrics['max_drawdown_3y'] * 100, 2, ',', '.') . '%' : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right tabular-nums">{{ $riskMetrics['max_drawdown_5y'] !== null ? number_format($riskMetrics['max_drawdown_5y'] * 100, 2, ',', '.') . '%' : '—' }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- TAB: BIAYA --}}
<div x-show="tab === 'biaya'" x-cloak>
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
            <h2 class="font-bold text-white text-sm">Informasi Biaya</h2>
            <div class="flex items-center gap-2">
                <button @click="toggleLock('biaya')" :disabled="toggling"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                    :class="isLocked('biaya') ? 'bg-amber-400/30 text-amber-100 hover:bg-amber-400/40' : 'bg-white/20 text-white hover:bg-white/30'">
                    <template x-if="isLocked('biaya')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></template>
                    <template x-if="!isLocked('biaya')"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></template>
                    <span x-text="isLocked('biaya') ? 'Terkunci' : 'Lock Parser'"></span>
                </button>
                <button @click="openEdit('biaya')"
                    class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </button>
            </div>
        </div>
        @php
            $hasFeeData = $fund->subscription_fee || $fund->redemption_fee || $fund->switching_fee || $fund->management_fee || $fund->custodian_fee || $fund->minimum_subscription || $fund->minimum_topup || $fund->minimum_redemption || $fund->expense_ratio || $fund->investment_manager_fee;
        @endphp
        @if($hasFeeData)
        <div class="divide-y divide-line">
            @if($fund->subscription_fee)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Subscription Fee</span><span class="text-sm">{{ number_format($fund->subscription_fee, 2, ',', '.') }}%</span></div>@endif
            @if($fund->redemption_fee)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Redemption Fee</span><span class="text-sm">{{ number_format($fund->redemption_fee, 2, ',', '.') }}%</span></div>@endif
            @if($fund->switching_fee)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Switching Fee</span><span class="text-sm">{{ number_format($fund->switching_fee, 2, ',', '.') }}%</span></div>@endif
            @if($fund->management_fee)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Management Fee</span><span class="text-sm">{{ number_format($fund->management_fee, 2, ',', '.') }}%</span></div>@endif
            @if($fund->custodian_fee)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Custodian Fee</span><span class="text-sm">{{ number_format($fund->custodian_fee, 2, ',', '.') }}%</span></div>@endif
            @if($fund->expense_ratio)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Expense Ratio</span><span class="text-sm">{{ number_format($fund->expense_ratio, 4, ',', '.') }}%</span></div>@endif
            @if($fund->investment_manager_fee)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">IM Fee</span><span class="text-sm">{{ $fund->investment_manager_fee }}</span></div>@endif
            @if($fund->minimum_subscription)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Minimum Pembelian</span><span class="text-sm">Rp{{ number_format($fund->minimum_subscription, 0, ',', '.') }}</span></div>@endif
            @if($fund->minimum_topup)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Minimum Top Up</span><span class="text-sm">Rp{{ number_format($fund->minimum_topup, 0, ',', '.') }}</span></div>@endif
            @if($fund->minimum_redemption)<div class="px-6 py-3.5 flex items-start gap-4"><span class="text-xs font-semibold text-muted w-40 shrink-0">Minimum Redemption</span><span class="text-sm">Rp{{ number_format($fund->minimum_redemption, 0, ',', '.') }}</span></div>@endif
        </div>
        @else
        <div class="py-12 text-center text-muted text-sm">
            <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Data biaya belum tersedia.
        </div>
        @endif
    </div>
</div>

{{-- TAB: PORTOFOLIO --}}
<div x-show="tab === 'portofolio'" x-cloak x-init="portfolioMonth = portfolioMonth || new Date().getMonth() + 1; portfolioYear = portfolioYear || new Date().getFullYear(); loadPortfolioData()">
    @if($aaTimeline->isEmpty() && $topHoldings->isEmpty())
    <div class="py-12 text-center text-muted bg-white rounded-2xl border border-line">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        <p class="font-medium">Data portofolio belum tersedia.</p>
    </div>
    @else
    <div class="space-y-6">
        {{-- Alokasi Aset --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                <h2 class="font-bold text-white text-sm">Alokasi Aset</h2>
                <div class="flex items-center gap-2">
                    <button @click="openEdit('portofolio')"
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </button>
                </div>
            </div>
            @php
                $latestAa = $aaTimeline->last();
            @endphp
            @if($latestAa)
            <div class="divide-y divide-line">
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-36 shrink-0">Saham</span>
                    <span class="text-sm font-bold text-primary">{{ number_format($latestAa->equity_percent ?? 0, 2, ',', '.') }}%</span>
                </div>
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-36 shrink-0">Obligasi</span>
                    <span class="text-sm font-bold text-primary">{{ number_format($latestAa->bond_percent ?? 0, 2, ',', '.') }}%</span>
                </div>
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-36 shrink-0">Pasar Uang</span>
                    <span class="text-sm font-bold text-primary">{{ number_format($latestAa->money_market_percent ?? 0, 2, ',', '.') }}%</span>
                </div>
                <div class="px-6 py-3.5 flex items-start gap-4">
                    <span class="text-xs font-semibold text-muted w-36 shrink-0">Kas</span>
                    <span class="text-sm font-bold text-primary">{{ number_format($latestAa->cash_percent ?? 0, 2, ',', '.') }}%</span>
                </div>
            </div>
            @else
            <div class="py-8 text-center text-muted text-sm">
                <svg class="w-8 h-8 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
                Data alokasi aset belum tersedia.
            </div>
            @endif
        </div>

        {{-- Asset Allocation Pie --}}
        @if($latestAa = $aaTimeline->last())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">Asset Allocation ({{ $latestAa->period_date->format('M Y') }})</h3>
                <div style="height: 280px;"><canvas id="chartAaPie"></canvas></div>
            </div>
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                <h3 class="font-bold text-primary text-sm mb-4">Top Holdings</h3>
                @if($topHoldings->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-3 py-2 font-semibold">Efek</th><th class="px-3 py-2 font-semibold">Jenis</th><th class="px-3 py-2 font-semibold text-right">Bobot</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @foreach($topHoldings as $th)
                            <tr class="hover:bg-[#f8fafc]"><td class="px-3 py-2 text-xs font-semibold">{{ $th->security_name }}</td><td class="px-3 py-2 text-xs text-muted">{{ $th->security_type ?? '—' }}</td><td class="px-3 py-2 text-xs text-right font-semibold">{{ number_format($th->weight_percent, 2, ',', '.') }}%</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-muted text-center py-8">Belum ada data top holdings.</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Timeline Asset Allocation (Stacked Bar) --}}
        @if($aaTimeline->isNotEmpty())
        <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
            <h3 class="font-bold text-primary text-sm mb-4">Timeline Asset Allocation</h3>
            <div style="height: 300px;"><canvas id="chartAaTimeline"></canvas></div>
        </div>
        @endif

        {{-- Timeline Portfolio Composition --}}
        @if($portfolioTimeline->isNotEmpty())
        @php
            $ptLabels = $portfolioTimeline->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('M Y'));
            $allSecurities = $portfolioTimeline->flatMap(fn($items) => $items->pluck('security_name'))->unique();
            $ptColors = ['#2563eb','#059669','#d97706','#dc2626','#7c3aed','#0891b2','#db2777','#65a30d','#ca8a04','#ea580c','#4f46e5','#0d9488'];
        @endphp
        <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
            <h3 class="font-bold text-primary text-sm mb-4">Timeline Portfolio Composition</h3>
            <div style="height: 300px;"><canvas id="chartPtTimeline"></canvas></div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        {{-- Asset Allocation Pie --}}
        @if($latestAa = $aaTimeline->last())
        new Chart(document.getElementById('chartAaPie'), {
            type: 'pie',
            data: {
                labels: ['Saham', 'Obligasi', 'Pasar Uang', 'Kas'],
                datasets: [{
                    data: [{{ $latestAa->equity_percent ?? 0 }}, {{ $latestAa->bond_percent ?? 0 }}, {{ $latestAa->money_market_percent ?? 0 }}, {{ $latestAa->cash_percent ?? 0 }}],
                    backgroundColor: ['#2563eb', '#059669', '#d97706', '#6b7280'],
                    borderWidth: 1,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
        @endif

        {{-- Timeline AA --}}
        @if($aaTimeline->isNotEmpty())
        new Chart(document.getElementById('chartAaTimeline'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($aaLabels) !!},
                datasets: [
                    { label: 'Saham', data: {!! json_encode($aaTimeline->pluck('equity_percent')) !!}, backgroundColor: '#2563eb' },
                    { label: 'Obligasi', data: {!! json_encode($aaTimeline->pluck('bond_percent')) !!}, backgroundColor: '#059669' },
                    { label: 'Pasar Uang', data: {!! json_encode($aaTimeline->pluck('money_market_percent')) !!}, backgroundColor: '#d97706' },
                    { label: 'Kas', data: {!! json_encode($aaTimeline->pluck('cash_percent')) !!}, backgroundColor: '#6b7280' },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, ticks: { callback: val => val + '%' } }
                }
            }
        });
        @endif

        {{-- Timeline Portfolio Composition --}}
        @if($portfolioTimeline->isNotEmpty())
        const datasets = [];
        const securities = {!! json_encode($allSecurities->values()) !!};
        const ptLabels = {!! json_encode($ptLabels) !!};
        const ptRaw = {!! json_encode($portfolioTimeline->map(fn($items) => $items->keyBy('security_name')->map(fn($i) => $i->weight_percent))) !!};

        securities.forEach((sec, i) => {
            datasets.push({
                label: sec,
                data: ptLabels.map((_, idx) => {
                    const periodKey = Object.keys(ptRaw)[idx];
                    return ptRaw[periodKey] && ptRaw[periodKey][sec] ? ptRaw[periodKey][sec] : 0;
                }),
                backgroundColor: '{!! json_encode($ptColors) !!}'[i % 12] || '#94a3b8',
            });
        });

        new Chart(document.getElementById('chartPtTimeline'), {
            type: 'bar',
            data: { labels: ptLabels, datasets: datasets },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, ticks: { callback: val => val + '%' } }
                }
            }
        });
        @endif
    });
    </script>
    @endif
</div>

{{-- TAB: PDF PROSPEKTUS --}}
<div x-show="tab === 'pdf-prospektus'" x-cloak x-data="documentTabData('prospektus')">
    @include('admin.daftar-reksa-dana.partials.tab-pdf-document', ['fund' => $fund, 'docType' => 'prospektus'])
</div>

{{-- TAB: PDF FFS --}}
<div x-show="tab === 'pdf-ffs'" x-cloak x-data="documentTabData('ffs')">
    @include('admin.daftar-reksa-dana.partials.tab-pdf-document', ['fund' => $fund, 'docType' => 'ffs'])
</div>

<div x-show="personModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
    <div class="absolute inset-0 bg-black/40" @click="personModal.open = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-3xl max-h-[85vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <div>
                <p class="text-xs text-muted">Detail keterkaitan</p>
                <h2 class="font-bold text-primary" x-text="personModal.data?.name || 'Memuat...'"></h2>
            </div>
            <button type="button" @click="personModal.open = false" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
        </div>
        <div class="p-6 space-y-6">
            <div x-show="personModal.loading" class="text-sm text-muted">Memuat data...</div>
            <div x-show="personModal.error" x-text="personModal.error" class="px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700"></div>
            <template x-if="personModal.data">
                <div class="space-y-6">
                    <div>
                        <h3 class="font-bold text-primary text-sm mb-3">Reksa Dana yang Pernah Diikuti</h3>
                        <template x-if="personModal.data.funds.length === 0"><p class="text-sm text-muted">Belum ada data Reksa Dana terkait.</p></template>
                        <div class="overflow-x-auto" x-show="personModal.data.funds.length > 0">
                            <table class="w-full text-sm"><thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-3 py-2">Reksa Dana</th><th class="px-3 py-2">Kode</th><th class="px-3 py-2">Peran</th><th class="px-3 py-2">Jabatan</th><th class="px-3 py-2">Sumber</th></tr></thead><tbody class="divide-y divide-line"><template x-for="row in personModal.data.funds" :key="row.name + row.role + row.position"><tr><td class="px-3 py-2 font-semibold" x-text="row.name"></td><td class="px-3 py-2 font-mono text-xs" x-text="row.code || '-'"></td><td class="px-3 py-2" x-text="row.role || '-'"></td><td class="px-3 py-2 text-muted" x-text="row.position || '-'"></td><td class="px-3 py-2 text-xs text-muted" x-text="row.source || '-'"></td></tr></template></tbody></table>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-primary text-sm mb-3">Manajer Investasi Terkait</h3>
                        <template x-if="personModal.data.managers.length === 0"><p class="text-sm text-muted">Belum ada data Manajer Investasi terkait.</p></template>
                        <div class="overflow-x-auto" x-show="personModal.data.managers.length > 0">
                            <table class="w-full text-sm"><thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-3 py-2">Manajer Investasi</th><th class="px-3 py-2">Peran</th><th class="px-3 py-2">Jabatan</th><th class="px-3 py-2">Sumber</th></tr></thead><tbody class="divide-y divide-line"><template x-for="row in personModal.data.managers" :key="row.name + row.role + row.position"><tr><td class="px-3 py-2 font-semibold" x-text="row.name"></td><td class="px-3 py-2" x-text="row.role || '-'"></td><td class="px-3 py-2 text-muted" x-text="row.position || '-'"></td><td class="px-3 py-2 text-xs text-muted" x-text="row.source || '-'"></td></tr></template></tbody></table>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-primary text-sm mb-3">Berita Utama Terkait</h3>
                        <template x-if="personModal.data.news.length === 0"><p class="text-sm text-muted">Belum ada berita terkait.</p></template>
                        <div class="space-y-2" x-show="personModal.data.news.length > 0">
                            <template x-for="item in personModal.data.news" :key="item.url || item.title">
                                <a :href="item.url" target="_blank" class="block border border-line rounded-xl px-4 py-3 hover:border-accent transition"><p class="text-sm font-semibold text-primary" x-text="item.title"></p><p class="text-xs text-muted mt-1"><span x-text="item.source || '-'"></span> <span x-show="item.published_at">-</span> <span x-text="item.published_at || ''"></span></p></a>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

{{-- Modal Edit Ringkasan Kinerja --}}
<div x-show="editModal === 'ringkasan'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
    @click.self="editModal = null">
    <div class="absolute inset-0 bg-black/40" @click="editModal = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-lg max-h-[85vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <h3 class="font-bold text-primary">Edit Ringkasan Kinerja</h3>
            <button @click="editModal = null" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
        </div>
        <form @submit.prevent="submitEdit('ringkasan')" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">NAB/UP</label>
                <input type="number" step="0.0001" x-model="editData.nab_per_unit" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Tanggal NAB</label>
                <input type="date" x-model="editData.tanggal_nab" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">AUM</label>
                <input type="number" step="0.01" x-model="editData.aum" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Total Unit Penyertaan</label>
                <input type="number" step="0.01" x-model="editData.total_unit" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="editModal = null" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-emerald-700 rounded-lg hover:bg-emerald-800">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Risiko --}}
<div x-show="editModal === 'risiko'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
    @click.self="editModal = null">
    <div class="absolute inset-0 bg-black/40" @click="editModal = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-lg max-h-[85vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <h3 class="font-bold text-primary">Edit Risiko</h3>
            <button @click="editModal = null" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
        </div>
        <form @submit.prevent="submitEdit('risiko')" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Risk Category</label>
                <select x-model="editData.risk_category" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    <option value="">—</option>
                    <option value="Rendah">Rendah</option>
                    <option value="Sedang">Sedang</option>
                    <option value="Tinggi">Tinggi</option>
                </select>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Sharpe 1Th</label>
                    <input type="number" step="0.0001" x-model="editData.sharpe_ratio_1y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Sharpe 3Th</label>
                    <input type="number" step="0.0001" x-model="editData.sharpe_ratio_3y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Sharpe 5Th</label>
                    <input type="number" step="0.0001" x-model="editData.sharpe_ratio_5y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Std Dev 1Th</label>
                    <input type="number" step="0.0001" x-model="editData.stdev_1y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Std Dev 3Th</label>
                    <input type="number" step="0.0001" x-model="editData.stdev_3y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Std Dev 5Th</label>
                    <input type="number" step="0.0001" x-model="editData.stdev_5y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Beta 1Th</label>
                    <input type="number" step="0.0001" x-model="editData.beta_1y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Beta 3Th</label>
                    <input type="number" step="0.0001" x-model="editData.beta_3y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Beta 5Th</label>
                    <input type="number" step="0.0001" x-model="editData.beta_5y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Max DD 1Th</label>
                    <input type="number" step="0.0001" x-model="editData.max_drawdown_1y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Max DD 3Th</label>
                    <input type="number" step="0.0001" x-model="editData.max_drawdown_3y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Max DD 5Th</label>
                    <input type="number" step="0.0001" x-model="editData.max_drawdown_5y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="editModal = null" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-emerald-700 rounded-lg hover:bg-emerald-800">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Biaya --}}
<div x-show="editModal === 'biaya'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
    @click.self="editModal = null">
    <div class="absolute inset-0 bg-black/40" @click="editModal = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-lg max-h-[85vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <h3 class="font-bold text-primary">Edit Biaya</h3>
            <button @click="editModal = null" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
        </div>
        <form @submit.prevent="submitEdit('biaya')" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Subscription Fee (%)</label>
                    <input type="number" step="0.01" x-model="editData.subscription_fee" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Redemption Fee (%)</label>
                    <input type="number" step="0.01" x-model="editData.redemption_fee" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Switching Fee (%)</label>
                    <input type="number" step="0.01" x-model="editData.switching_fee" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Management Fee (%)</label>
                    <input type="number" step="0.01" x-model="editData.management_fee" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Custodian Fee (%)</label>
                    <input type="number" step="0.01" x-model="editData.custodian_fee" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Expense Ratio (%)</label>
                    <input type="number" step="0.0001" x-model="editData.expense_ratio" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">IM Fee</label>
                <input type="text" x-model="editData.investment_manager_fee" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Min Pembelian (Rp)</label>
                    <input type="number" step="1" x-model="editData.minimum_subscription" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Min Top Up (Rp)</label>
                    <input type="number" step="1" x-model="editData.minimum_topup" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Min Redemption (Rp)</label>
                    <input type="number" step="1" x-model="editData.minimum_redemption" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="editModal = null" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-emerald-700 rounded-lg hover:bg-emerald-800">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Portofolio --}}
<div x-show="editModal === 'portofolio'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
    @click.self="editModal = null">
    <div class="absolute inset-0 bg-black/40" @click="editModal = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-2xl max-h-[85vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <h3 class="font-bold text-primary">Edit Portofolio</h3>
            <button @click="editModal = null" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
        </div>
        <form @submit.prevent="savePortfolio()" class="p-6 space-y-4">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Saham (%)</label>
                    <input type="number" step="0.01" x-model="portfolioSaham" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Obligasi (%)</label>
                    <input type="number" step="0.01" x-model="portfolioObligasi" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Pasar Uang (%)</label>
                    <input type="number" step="0.01" x-model="portfolioPasarUang" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kas (%)</label>
                    <input type="number" step="0.01" x-model="portfolioKas" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Top Holdings <span class="text-[10px] text-muted/60">(format: NamaEfek:Bobot:Jenis, tiap baris 1 efek)</span></label>
                <textarea x-model="portfolioTopHoldings" rows="5" class="w-full border border-line rounded-lg px-3 py-2 text-sm font-mono"></textarea>
            </div>
            <div class="border-t border-line pt-4">
                <h4 class="font-bold text-primary text-xs mb-3">Ringkasan Kinerja</h4>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">NAB/UP</label>
                        <input type="number" step="0.0001" x-model="portfolioNabPerUnit" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Tanggal NAB</label>
                        <input type="date" x-model="portfolioTanggalNab" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">AUM</label>
                        <input type="number" step="0.01" x-model="portfolioAum" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Total Unit</label>
                        <input type="number" step="0.01" x-model="portfolioTotalUnit" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Return YTD</label>
                        <input type="number" step="0.0001" x-model="portfolioReturnYtd" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Return 1 Thn</label>
                        <input type="number" step="0.0001" x-model="portfolioReturn1y" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Return 1 Bln</label>
                        <input type="number" step="0.0001" x-model="portfolioReturn1m" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Return Inception</label>
                        <input type="number" step="0.0001" x-model="portfolioReturnInception" class="w-full border border-line rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <div>
                    <template x-if="portfolioSuccess">
                        <span class="text-xs text-green-600 font-semibold" x-text="portfolioSuccess"></span>
                    </template>
                    <template x-if="portfolioError">
                        <span class="text-xs text-red-600 font-semibold" x-text="portfolioError"></span>
                    </template>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="editModal = null; portfolioSuccess = null; portfolioError = null" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-gray-50">Batal</button>
                    <button type="submit" :disabled="portfolioSaving" class="px-4 py-2 text-sm text-white bg-emerald-700 rounded-lg hover:bg-emerald-800 disabled:opacity-50">
                        <span x-text="portfolioSaving ? 'Menyimpan...' : 'Simpan'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>

<script>
function documentTabData(defaultDocType) {
    return {
        docType: defaultDocType,
        selectedPartitionIds: [],
        selectedPageContent: null,
        loading: false,
        error: null,
        success: null,
        loadingFfs: {},
        ffsSuccess: {},
        ffsError: {},
        pageContentCache: {},
        partitionsByDoc: @json($fund->documents->mapWithKeys(fn($d) => [$d->id => $d->partitions->map(fn($p) => ['id' => $p->id, 'start_page' => $p->start_page, 'end_page' => $p->end_page])->keyBy('id')])),

        partitionModal: {
            open: false,
            editing: null,
            nama: '',
            start: 1,
            end: 10,
            documentId: null,
            error: null,
            saving: false,
        },

        isPageInSelectedPartition(docId, pageParse) {
            const partitions = this.partitionsByDoc[docId];
            if (!partitions) return false;
            return this.selectedPartitionIds.some(pid => {
                const p = partitions[pid];
                return p && pageParse >= p.start_page && pageParse <= p.end_page;
            });
        },

        async showPageContent(docId, pageId) {
            const cacheKey = docId + '_' + pageId;
            if (this.pageContentCache[cacheKey]) {
                this.selectedPageContent = this.pageContentCache[cacheKey];
                return;
            }

            try {
                const url = `/admin/daftar-reksa-dana/documents/${docId}/parsed-pages`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                if (!res.ok) throw new Error(json.error || 'Gagal mengambil data.');

                const page = json.pages.find(p => p.id === pageId);
                if (page) {
                    this.selectedPageContent = '<pre class="text-xs whitespace-pre-wrap">' + this.escapeHtml(page.text_content) + '</pre>';
                    this.pageContentCache[cacheKey] = this.selectedPageContent;
                }
            } catch (e) {
                this.selectedPageContent = '<span class="text-red-600">' + this.escapeHtml(e.message) + '</span>';
            }
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        openPartitionModal(docId, editingPartition = null) {
            this.partitionModal = {
                open: true,
                editing: editingPartition,
                nama: editingPartition ? editingPartition.nama_partisi : '',
                start: editingPartition ? editingPartition.start_page : 1,
                end: editingPartition ? editingPartition.end_page : 10,
                documentId: docId,
                error: null,
                saving: false,
            };
        },

        async savePartition() {
            const pm = this.partitionModal;
            if (!pm.nama || !pm.start || !pm.end) {
                pm.error = 'Semua field harus diisi.';
                return;
            }
            if (parseInt(pm.start) > parseInt(pm.end)) {
                pm.error = 'Halaman mulai harus lebih kecil atau sama dengan halaman selesai.';
                return;
            }

            pm.saving = true;
            pm.error = null;

            try {
                const url = pm.editing
                    ? `/admin/daftar-reksa-dana/partitions/${pm.editing.id}/update`
                    : `/admin/daftar-reksa-dana/partitions`;
                const method = pm.editing ? 'POST' : 'POST';

                const body = new FormData();
                body.append('_token', '{{ csrf_token() }}');
                body.append('document_id', pm.documentId);
                body.append('nama_partisi', pm.nama);
                body.append('start_page', pm.start);
                body.append('end_page', pm.end);

                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body,
                });

                const json = await res.json();
                if (!res.ok) throw new Error(json.error || json.message || 'Gagal menyimpan partisi.');

                pm.open = false;
                window.location.reload();
            } catch (e) {
                pm.error = e.message;
            } finally {
                pm.saving = false;
            }
        },

        async deletePartition(partitionId) {
            if (!confirm('Hapus partisi ini?')) return;

            try {
                const res = await fetch(`/admin/daftar-reksa-dana/partitions/${partitionId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                });
                if (!res.ok) throw new Error('Gagal menghapus partisi.');
                window.location.reload();
            } catch (e) {
                alert(e.message);
            }
        },

        async handleParseFfs(docId) {
            this.loadingFfs[docId] = true;
            this.ffsSuccess[docId] = null;
            this.ffsError[docId] = null;

            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');

                const res = await fetch(`/admin/daftar-reksa-dana/documents/${docId}/parse-ffs`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData,
                });

                const json = await res.json();
                if (!res.ok) throw new Error(json.error || 'Gagal parse FFS.');

                this.ffsSuccess[docId] = json.message;
            } catch (e) {
                this.ffsError[docId] = e.message;
            } finally {
                this.loadingFfs[docId] = false;
            }
        },

        async handleParseSimpan(docId) {
            if (this.selectedPartitionIds.length === 0) return;

            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('reksa_dana_id', '{{ $fund->id }}');
                formData.append('document_id', docId);
                this.selectedPartitionIds.forEach(pid => formData.append('partition_ids[]', pid));

                const res = await fetch('{{ route('admin.daftar-reksa-dana.extract-data') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData,
                });

                const json = await res.json();
                if (!res.ok) throw new Error(json.error || 'Gagal mengekstrak data.');

                this.success = json.message;
            } catch (e) {
                this.error = e.message;
            } finally {
                this.loading = false;
            }
        },

        uploadFfsOpen: false,
        uploadFfsFile: null,
        uploadFfsMonth: '',
        uploadFfsYear: '',
        uploadFfsLoading: false,
        uploadFfsError: null,
        uploadFfsSuccess: null,

        async uploadFfs() {
            if (!this.uploadFfsFile || !this.uploadFfsMonth || !this.uploadFfsYear) return;
            this.uploadFfsLoading = true;
            this.uploadFfsError = null;
            this.uploadFfsSuccess = null;
            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('reksa_dana_id', '{{ $fund->id }}');
                formData.append('document_type', 'ffs');
                formData.append('file', this.uploadFfsFile);
                formData.append('ffs_month', this.uploadFfsMonth);
                formData.append('ffs_year', this.uploadFfsYear);

                const res = await fetch('{{ route('admin.daftar-reksa-dana.documents.store') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData,
                });

                const json = await res.json();
                if (!res.ok) throw new Error(json.error || json.errors?.[Object.keys(json.errors || {})[0]]?.[0] || 'Gagal upload.');

                this.uploadFfsSuccess = json.message;
                this.uploadFfsFile = null;
                this.uploadFfsMonth = '';
                this.uploadFfsYear = '';
                this.uploadFfsOpen = false;
                setTimeout(() => window.location.reload(), 1500);
            } catch (e) {
                this.uploadFfsError = e.message;
            } finally {
                this.uploadFfsLoading = false;
            }
        },
    };
}
</script>
@endsection
