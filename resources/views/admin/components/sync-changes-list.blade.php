@props([
    'changesUrl' => '',
    'detailTypes' => [],
])

<div x-data="{
    changesUrl: '{{ $changesUrl }}',
    loading: false,
    changes: [],
    pagination: null,
    entityType: '',

    init() {
        this.loadChanges();
    },

    async loadChanges(page) {
        this.loading = true;
        try {
            const params = new URLSearchParams();
            if (page) params.set('page', page);
            if (this.entityType) params.set('entity_type', this.entityType);
            const res = await fetch(this.changesUrl + '?' + params.toString(), {
                headers: { Accept: 'application/json' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();
            this.changes = json.data || [];
            this.pagination = {
                current: json.current_page,
                last: json.last_page,
                from: json.from,
                to: json.to,
                total: json.total,
                prev: json.prev_page_url,
                next: json.next_page_url,
            };
        } catch (e) {
            console.error('Failed to load sync changes', e);
        } finally {
            this.loading = false;
        }
    },

    changeBadge(type) {
        return {
            'created': 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'updated': 'bg-amber-50 text-amber-700 border-amber-200',
            'deleted': 'bg-red-50 text-red-700 border-red-200',
        }[type] || 'bg-slate-50 text-slate-600 border-slate-200';
    },

    entityLabel(type) {
        const labels = {
            'stock': 'Saham',
            'bond': 'Obligasi',
            'mi': 'Manajer Investasi',
            'rd': 'Reksa Dana',
            'rd_harian': 'Harga Harian RD',
            'relasi_mi_rd': 'Relasi MI-RD',
        };
        return labels[type] || type;
    },

    fieldLabel(field) {
        const labels = {
            'nama': 'Nama',
            'kode': 'Kode',
            'sektor': 'Sektor',
            'sub_industri': 'Sub Industri',
            'harga_terbaru': 'Harga Terbaru',
            'harga_pembukaan': 'Harga Pembukaan',
            'harga_penutupan_sebelumnya': 'Harga Sebelumnya',
            'harga_tertinggi': 'Harga Tertinggi',
            'harga_terendah': 'Harga Terendah',
            'perubahan_persen': 'Perubahan %',
            'volume': 'Volume',
            'value': 'Nilai',
            'frekuensi': 'Frekuensi',
            'jumlah_saham': 'Jumlah Saham',
            'market_capital': 'Market Cap',
            'listing_board': 'Listing Board',
            'emiten': 'Emiten',
            'denominasi': 'Denominasi',
            'kupon': 'Kupon',
            'jatuh_tempo': 'Jatuh Tempo',
            'outstanding_amount': 'Outstanding',
            'syariah': 'Syariah',
            'rating': 'Rating',
            'harga_persen': 'Harga %',
            'name': 'Nama',
            'kode_mi': 'Kode MI',
            'kode_ojk': 'Kode OJK',
            'nama_reksa_dana': 'Nama Reksa Dana',
            'kode_reksa_dana': 'Kode Reksa Dana',
            'jenis': 'Jenis',
            'jenis_reksa_dana': 'Jenis RD',
            'kategori': 'Kategori',
            'mata_uang': 'Mata Uang',
            'nama_manajer_investasi': 'Nama MI',
            'nab_per_unit': 'NAB/UP',
            'tanggal_nab': 'Tanggal NAB',
            'aum': 'AUM',
            'total_unit': 'Total Unit',
            'return_1d': 'Return 1H',
            'return_1m': 'Return 1B',
            'return_1y': 'Return 1T',
            'return_3y': 'Return 3T',
            'return_5y': 'Return 5T',
            'sharpe_ratio_1y': 'Sharpe 1T',
            'sharpe_ratio_3y': 'Sharpe 3T',
            'sharpe_ratio_5y': 'Sharpe 5T',
            'stdev_1y': 'Stdev 1T',
            'stdev_3y': 'Stdev 3T',
            'stdev_5y': 'Stdev 5T',
            'beta_1y': 'Beta 1T',
            'beta_3y': 'Beta 3T',
            'beta_5y': 'Beta 5T',
            'max_drawdown_1y': 'Max Drawdown 1T',
            'max_drawdown_3y': 'Max Drawdown 3T',
            'max_drawdown_5y': 'Max Drawdown 5T',
            'unit_participation': 'Unit Partisipasi',
            'investment_manager_id': 'MI ID',
            'pasardana_id': 'Pasardana ID',
        };
        return labels[field] || field;
    }
}">
    {{-- Filter by entity type --}}
    @if (!empty($detailTypes))
    <div class="mb-4 flex items-center gap-2">
        <span class="text-xs text-muted font-semibold">Filter:</span>
        <button @click="entityType = ''; loadChanges()"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition"
            :class="entityType === '' ? 'bg-primary text-white border-primary' : 'text-muted hover:text-primary border-line'">
            Semua
        </button>
        @foreach ($detailTypes as $value => $label)
        <button @click="entityType = '{{ $value }}'; loadChanges()"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition"
            :class="entityType === '{{ $value }}' ? 'bg-primary text-white border-primary' : 'text-muted hover:text-primary border-line'">
            {{ $label }}
        </button>
        @endforeach
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                    <th class="px-4 py-3 font-semibold">Entity</th>
                    <th class="px-4 py-3 font-semibold">Label</th>
                    <th class="px-4 py-3 font-semibold">Field</th>
                    <th class="px-4 py-3 font-semibold">Old Value</th>
                    <th class="px-4 py-3 font-semibold">New Value</th>
                    <th class="px-4 py-3 font-semibold">Tipe</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                <template x-if="loading">
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-muted text-xs">Memuat...</td>
                    </tr>
                </template>
                <template x-if="!loading && changes.length === 0">
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-muted text-xs">Belum ada perubahan tercatat untuk sync run ini.</td>
                    </tr>
                </template>
                <template x-for="c in changes" :key="c.id">
                    <tr class="hover:bg-[#f8fafc] transition-colors">
                        <td class="px-4 py-2.5">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold border"
                                :class="changeBadge(c.change_type)"
                                x-text="entityLabel(c.entity_type)"></span>
                        </td>
                        <td class="px-4 py-2.5 text-xs font-medium text-primary max-w-[200px] truncate" x-text="c.entity_label" :title="c.entity_label"></td>
                        <td class="px-4 py-2.5 text-xs text-muted" x-text="fieldLabel(c.field) || c.field"></td>
                        <td class="px-4 py-2.5 text-xs text-muted max-w-[200px] truncate font-mono" x-text="c.old_value || '—'" :title="c.old_value"></td>
                        <td class="px-4 py-2.5 text-xs max-w-[200px] truncate font-mono"
                            :class="c.change_type === 'created' ? 'text-emerald-600' : 'text-primary'"
                            x-text="c.new_value || '—'" :title="c.new_value"></td>
                        <td class="px-4 py-2.5">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold border"
                                :class="changeBadge(c.change_type)"
                                x-text="c.change_type"></span>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <template x-if="pagination && pagination.last > 1">
        <div class="px-4 py-3 border-t border-line flex items-center justify-between gap-4 text-sm">
            <p class="text-muted text-xs" x-text="`Menampilkan ${pagination.from}–${pagination.to} dari ${pagination.total} perubahan`"></p>
            <div class="flex items-center gap-1">
                <button @click="loadChanges(pagination.current - 1)" :disabled="!pagination.prev"
                    class="px-3 py-1.5 rounded-lg text-xs transition"
                    :class="pagination.prev ? 'text-muted hover:text-primary hover:bg-[#f1f5f9]' : 'text-muted/40 cursor-not-allowed'">
                    ← Prev
                </button>
                <template x-for="p in Array.from({length: pagination.last}, (_, i) => i + 1).filter(p => Math.abs(p - pagination.current) <= 2 || p === 1 || p === pagination.last)">
                    <button @click="loadChanges(p)"
                        class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition"
                        :class="p === pagination.current ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]'"
                        x-text="p"></button>
                </template>
                <button @click="loadChanges(pagination.current + 1)" :disabled="!pagination.next"
                    class="px-3 py-1.5 rounded-lg text-xs transition"
                    :class="pagination.next ? 'text-muted hover:text-primary hover:bg-[#f1f5f9]' : 'text-muted/40 cursor-not-allowed'">
                    Next →
                </button>
            </div>
        </div>
    </template>

    <div x-show="!loading && changes.length === 0" class="py-12 text-center text-muted">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
        <p class="font-medium">Belum ada riwayat perubahan</p>
        <p class="text-xs mt-1">Riwayat akan muncul setelah sync selesai dijalankan dan ada perubahan data.</p>
    </div>
</div>
