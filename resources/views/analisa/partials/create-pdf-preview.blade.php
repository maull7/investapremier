<h4 class="text-sm font-semibold text-primary mb-2">Hasil Ekstraksi</h4>

{{-- Info Keuangan --}}
<div x-show="pdfData.total_aum || pdfData.total_marcap_10_efek || pdfData.nab_per_unit || pdfData.total_aum / pdfData.nab_per_unit || pdfData.tanggal_data"
    class="border rounded-lg p-3 bg-white shadow-sm mb-3">
    <h5 class="font-semibold text-xs text-primary mb-2">Info Keuangan</h5>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
        <div>
            <span class="text-muted block">Total AUM</span>
            <span class="font-medium"
                x-text="pdfData.total_aum ? 'Rp ' + Number(pdfData.total_aum).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}) : '—'"></span>
        </div>
        <div>
            <span class="text-muted block">Total MarCap 10 Saham Terbesar</span>
            <span class="font-medium"
                x-text="pdfData.total_marcap_10_efek ? 'Rp ' + Number(pdfData.total_marcap_10_efek).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}) : '—'"></span>
        </div>
        <div>
            <span class="text-muted block">NAB/UP</span>
            <span class="font-medium"
                x-text="pdfData.nab_per_unit ? Number(pdfData.nab_per_unit).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 6}) : '—'"></span>
        </div>
        <div>
            <span class="text-muted block">Unit Penyertaan</span>
            <span class="font-medium"
                x-text="pdfData.total_aum / pdfData.nab_per_unit ? Number(pdfData.total_aum / pdfData.nab_per_unit).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}) : '—'"></span>
        </div>
        <div>
            <span class="text-muted block">Tanggal Data</span>
            <span class="font-medium" x-text="pdfData.tanggal_data || '—'"></span>
        </div>
    </div>
</div>

{{-- Alokasi Aset --}}
<div x-show="pdfData.alokasi_aset?.length" class="border rounded-lg p-3 bg-white shadow-sm mb-3">
    <h5 class="font-semibold text-xs text-primary mb-2">Alokasi Aset</h5>
    <table class="w-full text-xs">
        <thead>
            <tr class="bg-gray-50">
                <th class="text-left px-2 py-1 font-medium text-muted">Nama Aset</th>
                <th class="text-right px-2 py-1 font-medium text-muted">Persentase</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(row, i) in pdfData.alokasi_aset" :key="i">
                <tr class="border-t border-gray-100">
                    <td class="px-2 py-1" x-text="row.nama_aset || row.nama || '-'"></td>
                    <td class="px-2 py-1 text-right" x-text="row.persentase ?? '-'"></td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

{{-- Sektor --}}
<div x-show="pdfData.sektor?.length" class="border rounded-lg p-3 bg-white shadow-sm mb-3">
    <h5 class="font-semibold text-xs text-primary mb-2">Komposisi Sektor</h5>
    <table class="w-full text-xs">
        <thead>
            <tr class="bg-gray-50">
                <th class="text-left px-2 py-1 font-medium text-muted">Nama Sektor</th>
                <th class="text-right px-2 py-1 font-medium text-muted">Bobot</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(row, i) in pdfData.sektor" :key="i">
                <tr class="border-t border-gray-100">
                    <td class="px-2 py-1" x-text="row.nama_sektor || '-'"></td>
                    <td class="px-2 py-1 text-right" x-text="row.bobot ?? '-'"></td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

{{-- Efek --}}
<div x-show="pdfData.efek?.length" class="border rounded-lg p-3 bg-white shadow-sm mb-3">
    <h5 class="font-semibold text-xs text-primary mb-2">Portofolio Efek</h5>
    <table class="w-full text-xs">
        <thead>
            <tr class="bg-gray-50">
                <th class="text-left px-2 py-1 font-medium text-muted">Kode</th>
                <th class="text-left px-2 py-1 font-medium text-muted">Nama Efek</th>
                <th class="text-right px-2 py-1 font-medium text-muted">Bobot</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(row, i) in pdfData.efek" :key="i">
                <tr class="border-t border-gray-100">
                    <td class="px-2 py-1" x-text="row.kode_efek || '-'"></td>
                    <td class="px-2 py-1" x-text="row.nama_efek || '-'"></td>
                    <td class="px-2 py-1 text-right" x-text="row.bobot ?? '-'"></td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

{{-- Obligasi --}}
<div x-show="pdfData.obligasi?.length" class="border rounded-lg p-3 bg-white shadow-sm mb-3">
    <h5 class="font-semibold text-xs text-primary mb-2">Obligasi</h5>
    <table class="w-full text-xs">
        <thead>
            <tr class="bg-gray-50">
                <th class="text-left px-2 py-1 font-medium text-muted">Kode</th>
                <th class="text-left px-2 py-1 font-medium text-muted">Nama Obligasi</th>
                <th class="text-right px-2 py-1 font-medium text-muted">Bobot</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(row, i) in pdfData.obligasi" :key="i">
                <tr class="border-t border-gray-100">
                    <td class="px-2 py-1" x-text="row.kode_obligasi || '-'"></td>
                    <td class="px-2 py-1" x-text="row.nama_obligasi || '-'"></td>
                    <td class="px-2 py-1 text-right" x-text="row.bobot ?? '-'"></td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

{{-- Sukuk --}}
<div x-show="pdfData.sukuk?.length" class="border rounded-lg p-3 bg-white shadow-sm mb-3">
    <h5 class="font-semibold text-xs text-primary mb-2">Sukuk</h5>
    <table class="w-full text-xs">
        <thead>
            <tr class="bg-gray-50">
                <th class="text-left px-2 py-1 font-medium text-muted">Kode</th>
                <th class="text-left px-2 py-1 font-medium text-muted">Nama Sukuk</th>
                <th class="text-right px-2 py-1 font-medium text-muted">Bobot</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(row, i) in pdfData.sukuk" :key="i">
                <tr class="border-t border-gray-100">
                    <td class="px-2 py-1" x-text="row.kode_sukuk || '-'"></td>
                    <td class="px-2 py-1" x-text="row.nama_sukuk || '-'"></td>
                    <td class="px-2 py-1 text-right" x-text="row.bobot ?? '-'"></td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

{{-- Bank --}}
<div x-show="pdfData.bank?.length" class="border rounded-lg p-3 bg-white shadow-sm mb-3">
    <h5 class="font-semibold text-xs text-primary mb-2">Bank</h5>
    <table class="w-full text-xs">
        <thead>
            <tr class="bg-gray-50">
                <th class="text-left px-2 py-1 font-medium text-muted">Nama Bank</th>
                <th class="text-right px-2 py-1 font-medium text-muted">Bobot</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(row, i) in pdfData.bank" :key="i">
                <tr class="border-t border-gray-100">
                    <td class="px-2 py-1" x-text="row.nama_bank || '-'"></td>
                    <td class="px-2 py-1 text-right" x-text="row.bobot ?? '-'"></td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

{{-- Return --}}
<div x-show="pdfData.return_1m || pdfData.return_ytd || pdfData.return_1y"
    class="border rounded-lg p-3 bg-white shadow-sm mb-3">
    <h5 class="font-semibold text-xs text-primary mb-2">Return</h5>
    <div class="grid grid-cols-3 gap-3 text-xs">
        <div>
            <span class="text-muted block">Return 1 Bulan</span>
            <span class="font-medium"
                x-text="pdfData.return_1m ? Number(pdfData.return_1m).toFixed(2) + '%' : '—'"></span>
        </div>
        <div>
            <span class="text-muted block">Return YTD</span>
            <span class="font-medium"
                x-text="pdfData.return_ytd ? Number(pdfData.return_ytd).toFixed(2) + '%' : '—'"></span>
        </div>
        <div>
            <span class="text-muted block">Return 1 Tahun</span>
            <span class="font-medium"
                x-text="pdfData.return_1y ? Number(pdfData.return_1y).toFixed(2) + '%' : '—'"></span>
        </div>
    </div>
</div>

{{-- Data Tahunan --}}
<div x-show="pdfData.data_tambahan && Object.keys(pdfData.data_tambahan).length"
    class="border rounded-lg p-3 bg-white shadow-sm mb-3">
    <h5 class="font-semibold text-xs text-primary mb-2">Data Tahunan</h5>
    <template x-for="(tahun, ti) in (pdfData.tahun_tambahan || Object.keys(pdfData.data_tambahan))"
        :key="ti">
        <div class="mb-2">
            <h6 class="text-xs font-medium text-primary mb-1" x-text="'Tahun: ' + tahun"></h6>
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left px-2 py-1 font-medium text-muted">Item</th>
                        <th class="text-right px-2 py-1 font-medium text-muted">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(val, key) in pdfData.data_tambahan[tahun]">
                        <tr class="border-t border-gray-100">
                            <td class="px-2 py-1" x-text="key"></td>
                            <td class="px-2 py-1 text-right" x-text="val ?? '-'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>
</div>

{{-- Laporan Keuangan (scalar fields) --}}
<div x-show="pdfData.total_aset || pdfData.total_liabilitas || pdfData.nilai_aset_bersih || pdfData.pendapatan_bunga || pdfData.pendapatan_dividen || pdfData.laba_bersih || pdfData.arus_kas_operasi"
    class="border rounded-lg p-3 bg-white shadow-sm">
    <h5 class="font-semibold text-xs text-primary mb-2">Laporan Keuangan</h5>
    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
        <template
            x-for="(v, k) in {
            'Total Aset': pdfData.total_aset,
            'Total Liabilitas': pdfData.total_liabilitas,
            'Nilai Aset Bersih': pdfData.nilai_aset_bersih,
            'Kas dan Bank': pdfData.kas_dan_bank,
            'Portofolio Efek': pdfData.portofolio_efek,
            'Pendapatan Bunga': pdfData.pendapatan_bunga,
            'Pendapatan Dividen': pdfData.pendapatan_dividen,
            'Laba Bersih': pdfData.laba_bersih,
            'Arus Kas Operasi': pdfData.arus_kas_operasi,
            'Arus Kas Pendanaan': pdfData.arus_kas_pendanaan,
        }">
            <div x-show="v" class="flex justify-between py-0.5">
                <span class="text-muted" x-text="k"></span>
                <span class="font-medium" x-text="v"></span>
            </div>
        </template>
    </div>
</div>
