@php
    $fmt = fn($v) => $v !== null ? number_format((float)$v, 2, ',', '.') : '—';
    $fmtPct = fn($v) => $v !== null ? number_format((float)$v, 4) . '%' : '—';
@endphp

{{-- Info Obligasi --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach([
        ['Kupon', $fmtPct($analisa->kupon)],
        ['YTM', $fmtPct($analisa->ytm)],
        ['Rating', $analisa->rating ?? '—'],
        ['Official Rating', $analisa->official_rating ?? '—'],
        ['Shadow Rating', $analisa->shadow_rating ? $analisa->shadow_rating . ' (Skor: ' . number_format((float)($analisa->shadow_score ?? 0), 2) . ')' : '—'],
        ['Rating Source', $analisa->rating_source ? ucfirst($analisa->rating_source) : '—'],
        ['YTM Normal', $fmtPct($analisa->ytm_normal)],
        ['YTM Spread', $analisa->ytm_spread !== null ? ($analisa->ytm_spread > 0 ? '+' : '') . number_format((float)$analisa->ytm_spread, 4) . '%' : '—'],
        ['Nama Emiten', $analisa->nama_emiten ?? '—'],
    ] as [$label, $value])
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted">{{ $label }}</p>
        <p class="text-base font-bold text-primary mt-1">{{ $value }}</p>
    </div>
    @endforeach
</div>

{{-- Ringkasan Keuangan --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach([
        ['Total Aset', $analisa->total_asset],
        ['Total Liabilitas', $analisa->total_liabilities],
        ['Total Ekuitas', $analisa->equity],
        ['Pendapatan Bersih', $analisa->net_revenue],
        ['Laba Kotor', $analisa->gross_income],
        ['EBIT', $analisa->ebit],
        ['EBITDA', $analisa->ebitda],
        ['Laba Bersih', $analisa->net_income],
        ['EPS', $analisa->eps],
    ] as [$label, $value])
    <div class="bg-white rounded-xl border border-line p-4">
        <p class="text-xs text-muted">{{ $label }}</p>
        <p class="text-base font-bold text-primary mt-1">{{ $fmt($value) }}</p>
    </div>
    @endforeach
</div>

{{-- Neraca --}}
<div class="table-card">
    <div class="px-5 py-3.5 bg-[#f8fafc] border-b border-line">
        <h3 class="font-semibold text-primary text-sm">Neraca (Balance Sheet)</h3>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-line">
        <div class="p-5 space-y-2">
            <h4 class="text-xs font-semibold text-muted uppercase tracking-wide mb-3">Aset</h4>
            @foreach([
                ['Kas & Setara Kas', $analisa->cash_equivalents],
                ['Piutang Usaha', $analisa->account_receivable],
                ['Persediaan', $analisa->inventories],
                ['Total Aset Lancar', $analisa->current_asset, true],
                ['Aset Tetap', $analisa->fixed_asset],
                ['Total Aset', $analisa->total_asset, true],
            ] as $row)
                @php [$rl, $rv] = $row; $bold = $row[2] ?? false; @endphp
                <div class="flex justify-between items-center text-sm {{ $bold ? 'font-semibold border-t border-line pt-2' : '' }}">
                    <span class="{{ $bold ? 'text-primary' : 'text-muted' }}">{{ $rl }}</span>
                    <span>{{ $fmt($rv) }}</span>
                </div>
            @endforeach
        </div>
        <div class="p-5 space-y-2">
            <h4 class="text-xs font-semibold text-muted uppercase tracking-wide mb-3">Liabilitas & Ekuitas</h4>
            @foreach([
                ['Total Liabilitas Lancar', $analisa->current_liabilities, true],
                ['Pinjaman Jangka Panjang', $analisa->long_term_loans],
                ['Total Liabilitas Tidak Lancar', $analisa->total_non_current_liabilities, true],
                ['Total Liabilitas', $analisa->total_liabilities, true],
                ['Saldo Laba', $analisa->retained_earning],
                ['Total Ekuitas', $analisa->equity, true],
            ] as $row)
                @php [$rl, $rv] = $row; $bold = $row[2] ?? false; @endphp
                <div class="flex justify-between items-center text-sm {{ $bold ? 'font-semibold border-t border-line pt-2' : '' }}">
                    <span class="{{ $bold ? 'text-primary' : 'text-muted' }}">{{ $rl }}</span>
                    <span>{{ $fmt($rv) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Laba Rugi --}}
<div class="table-card">
    <div class="px-5 py-3.5 bg-[#f8fafc] border-b border-line">
        <h3 class="font-semibold text-primary text-sm">Laba Rugi (Income Statement)</h3>
    </div>
    <div class="p-5 space-y-2">
        @foreach([
            ['Pendapatan Bersih', $analisa->net_revenue, true],
            ['Laba Kotor', $analisa->gross_income, true],
            ['Laba Operasional', $analisa->laba_operasional, true],
            ['Beban Bunga', $analisa->interest_expense],
            ['EBIT', $analisa->ebit, true],
            ['EBITDA', $analisa->ebitda, true],
            ['Laba Bersih', $analisa->net_income, true],
            ['EPS', $analisa->eps, true],
        ] as $row)
            @php [$rl, $rv] = $row; $bold = $row[2] ?? false; @endphp
            <div class="flex justify-between items-center text-sm {{ $bold ? 'font-semibold' : '' }}">
                <span class="{{ $bold ? 'text-primary' : 'text-muted' }}">{{ $rl }}</span>
                <span>{{ $fmt($rv) }}</span>
            </div>
        @endforeach
    </div>
</div>

{{-- Arus Kas --}}
<div class="table-card">
    <div class="px-5 py-3.5 bg-[#f8fafc] border-b border-line">
        <h3 class="font-semibold text-primary text-sm">Arus Kas (Cash Flow Statement)</h3>
    </div>
    <div class="p-5 space-y-2">
        @foreach([
            ['Arus Kas dari Operasi', $analisa->cash_flows_operating_activities],
            ['Arus Kas dari Investasi', $analisa->cash_flows_investment],
            ['Arus Kas dari Pendanaan', $analisa->cash_flows_financing],
        ] as [$rl, $rv])
        <div class="flex justify-between items-center text-sm">
            <span class="text-muted">{{ $rl }}</span>
            <span class="font-semibold {{ $rv !== null && $rv >= 0 ? 'text-green-600' : 'text-red-500' }}">{{ $fmt($rv) }}</span>
        </div>
        @endforeach
    </div>
</div>

@if($analisa->catatan)
<div class="bg-white rounded-xl border border-line p-5">
    <h3 class="font-semibold text-primary text-sm mb-2">Catatan</h3>
    <p class="text-sm text-muted whitespace-pre-wrap">{{ $analisa->catatan }}</p>
</div>
@endif

@if($analisa->catatan_admin)
<div class="bg-blue-50 rounded-xl border border-blue-200 p-5">
    <h3 class="font-semibold text-blue-700 text-sm mb-2">Catatan dari Admin</h3>
    <p class="text-sm text-blue-600 whitespace-pre-wrap">{{ $analisa->catatan_admin }}</p>
</div>
@endif
