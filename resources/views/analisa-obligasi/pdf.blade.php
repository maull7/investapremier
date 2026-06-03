<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 11px; color: #1a1a2e; margin: 0; padding: 24px; }
    h1 { font-size: 18px; color: #1a1a2e; margin: 0 0 4px; }
    h2 { font-size: 13px; color: #1a1a2e; margin: 16px 0 8px; border-bottom: 1.5px solid #e2e8f0; padding-bottom: 4px; }
    .meta { color: #64748b; font-size: 10px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    th { text-align: left; padding: 5px 8px; background: #f1f5f9; font-size: 10px; text-transform: uppercase; color: #64748b; }
    td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
    .bold { font-weight: bold; }
    .right { text-align: right; }
</style>
</head>
<body>
<h1>Analisa Obligasi — {{ $analisa->nama_obligasi }}</h1>
<div class="meta">
    {{ $analisa->kode_obligasi ? 'Kode: '.$analisa->kode_obligasi.' · ' : '' }}
    {{ $analisa->nama_emiten ? 'Emiten: '.$analisa->nama_emiten.' · ' : '' }}
    Rating: {{ $analisa->rating ?? '-' }} · Official: {{ $analisa->official_rating ?? '-' }} · Shadow: {{ $analisa->shadow_rating ?? '-' }} · Source: {{ $analisa->rating_source ?? '-' }} · Kupon: {{ $analisa->kupon ? number_format($analisa->kupon,4).'%' : '-' }} · YTM: {{ $analisa->ytm ? number_format($analisa->ytm,4).'%' : '-' }}<br>
    YTM Normal: {{ $analisa->ytm_normal ? number_format($analisa->ytm_normal,4).'%' : '-' }} · Spread: {{ $analisa->ytm_spread !== null ? ($analisa->ytm_spread > 0 ? '+' : '').number_format($analisa->ytm_spread,4).'%' : '-' }}<br>
    Periode: {{ $analisa->periode ?? '-' }} · Mata Uang: {{ $analisa->mata_uang ?? 'IDR' }}<br>
    Disubmit oleh: {{ $analisa->user->name ?? '-' }} pada {{ $analisa->created_at->format('d M Y') }}
</div>

@php $fmt = fn($v) => $v !== null ? number_format((float)$v, 2, ',', '.') : '—'; @endphp

<h2>Neraca (Balance Sheet)</h2>
<table>
    <tr><th>Akun</th><th class="right">Nilai</th></tr>
    @foreach([
        ['Total Aset Lancar', $analisa->current_asset, true],
        ['Total Aset', $analisa->total_asset, true],
        ['Total Liabilitas Lancar', $analisa->current_liabilities, true],
        ['Total Liabilitas Tidak Lancar', $analisa->total_non_current_liabilities, true],
        ['Total Liabilitas', $analisa->total_liabilities, true],
        ['Total Ekuitas', $analisa->equity, true],
    ] as $row)
        @php [$rl, $rv] = $row; $bold = ($row[2] ?? false) ? 'bold' : ''; @endphp
        <tr><td class="{{ $bold }}">{{ $rl }}</td><td class="right {{ $bold }}">{{ $fmt($rv) }}</td></tr>
    @endforeach
</table>

<h2>Laba Rugi</h2>
<table>
    <tr><th>Akun</th><th class="right">Nilai</th></tr>
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
        @php [$rl, $rv] = $row; $bold = ($row[2] ?? false) ? 'bold' : ''; @endphp
        <tr><td class="{{ $bold }}">{{ $rl }}</td><td class="right {{ $bold }}">{{ $fmt($rv) }}</td></tr>
    @endforeach
</table>

<h2>Arus Kas</h2>
<table>
    <tr><th>Akun</th><th class="right">Nilai</th></tr>
    @foreach([
        ['Arus Kas Operasi', $analisa->cash_flows_operating_activities],
        ['Arus Kas Investasi', $analisa->cash_flows_investment],
        ['Arus Kas Pendanaan', $analisa->cash_flows_financing],
    ] as [$rl, $rv])
    <tr><td>{{ $rl }}</td><td class="right">{{ $fmt($rv) }}</td></tr>
    @endforeach
</table>

@if($analisa->ai_narasi)
<h2>Analisa AI</h2>
<p>{{ $analisa->ai_narasi }}</p>
@endif

@if($analisa->ai_narasi_plus)
<h2>Analisa AI Plus</h2>
<p>{{ $analisa->ai_narasi_plus }}</p>
@endif

@if($analisa->catatan)
<h2>Catatan</h2>
<p>{{ $analisa->catatan }}</p>
@endif
</body>
</html>
