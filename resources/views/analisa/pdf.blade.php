<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; }
    .page { padding: 32px; }

    /* Header */
    .header { border-bottom: 2px solid #1e3a5f; padding-bottom: 16px; margin-bottom: 20px; }
    .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .brand { font-size: 18px; font-weight: 700; color: #1e3a5f; }
    .brand-sub { font-size: 10px; color: #64748b; }
    .report-title { text-align: right; }
    .report-title h2 { font-size: 14px; font-weight: 700; color: #1e3a5f; }
    .report-title p { font-size: 10px; color: #64748b; }

    /* Info RD */
    .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; }
    .info-grid { display: flex; gap: 32px; }
    .info-item label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-item p { font-weight: 600; color: #1e3a5f; margin-top: 2px; }

    /* Status badge */
    .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 600; }
    .badge-submitted { background: #fef9c3; color: #854d0e; }
    .badge-reviewed  { background: #dcfce7; color: #166534; }

    /* Metric cards */
    .metrics { display: flex; gap: 10px; margin-bottom: 20px; }
    .metric-card { flex: 1; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; }
    .metric-card .label { font-size: 9px; color: #64748b; }
    .metric-card .value { font-size: 18px; font-weight: 700; color: #1e3a5f; margin-top: 2px; }
    .metric-card .desc { font-size: 9px; color: #94a3b8; margin-top: 2px; }

    /* Section */
    .section { margin-bottom: 20px; }
    .section-title { font-size: 11px; font-weight: 700; color: #1e3a5f; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 10px; }

    /* Table */
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    th { background: #f1f5f9; text-align: left; padding: 6px 8px; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase; }
    td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; }
    tr:last-child td { border-bottom: none; }

    /* AI Narasi */
    .ai-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 14px 16px; margin-bottom: 20px; }
    .ai-box .ai-title { font-size: 11px; font-weight: 700; color: #0369a1; margin-bottom: 8px; }
    .ai-box .ai-text { font-size: 10px; color: #1e293b; line-height: 1.7; white-space: pre-line; }

    /* Positive/negative */
    .pos { color: #16a34a; }
    .neg { color: #dc2626; }

    /* Footer */
    .footer { margin-top: 32px; border-top: 1px solid #e2e8f0; padding-top: 10px; display: flex; justify-content: space-between; font-size: 9px; color: #94a3b8; }

    /* Two column */
    .two-col { display: flex; gap: 16px; }
    .two-col > div { flex: 1; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="header-top">
            <div>
                <div class="brand">InvestaPremier</div>
                <div class="brand-sub">Wealth Management Platform</div>
            </div>
            <div class="report-title">
                <h2>Laporan Analisa Reksa Dana</h2>
                <p>Digenerate: {{ now()->format('d M Y, H:i') }} WIB</p>
            </div>
        </div>
    </div>

    {{-- Info RD --}}
    <div class="info-box">
        <div class="info-grid">
            <div class="info-item">
                <label>Nama Reksa Dana</label>
                <p>{{ $analisa->nama_reksa_dana }}</p>
            </div>
            <div class="info-item">
                <label>Jenis</label>
                <p>{{ $analisa->jenis_reksa_dana }}</p>
            </div>
            <div class="info-item">
                <label>Total AUM</label>
                <p>{{ $analisa->total_aum ? 'Rp '.number_format($analisa->total_aum, 0, ',', '.') : '-' }}</p>
            </div>
            <div class="info-item">
                <label>Status</label>
                <p><span class="badge {{ $analisa->status === 'reviewed' ? 'badge-reviewed' : 'badge-submitted' }}">
                    {{ $analisa->status === 'reviewed' ? 'Sudah Direview' : 'Submitted' }}
                </span></p>
            </div>
            <div class="info-item">
                <label>Disubmit oleh</label>
                <p>{{ $analisa->user->name }}</p>
            </div>
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="metrics">
        <div class="metric-card">
            <div class="label">Sharpe Ratio</div>
            <div class="value">{{ $analisa->sharpe_ratio ?? '-' }}</div>
            <div class="desc">Return per unit risiko</div>
        </div>
        <div class="metric-card">
            <div class="label">RAR</div>
            <div class="value">{{ $analisa->rar ?? '-' }}</div>
            <div class="desc">Risk-Adjusted Return</div>
        </div>
        <div class="metric-card">
            <div class="label">Liquidity Ratio</div>
            <div class="value">{{ $analisa->liquidity_ratio ? number_format($analisa->liquidity_ratio * 100, 2).'%' : '-' }}</div>
            <div class="desc">AUM / MarCap 10 Efek</div>
        </div>
        <div class="metric-card">
            <div class="label">Durasi Rata-rata</div>
            <div class="value">{{ $analisa->durasi_rata_rata ? $analisa->durasi_rata_rata.' thn' : '-' }}</div>
            <div class="desc">Weighted Avg Duration</div>
        </div>
    </div>

    {{-- AI Narasi / Structured Output --}}
    @if($analisa->ai_narasi)
    <div class="ai-box">
        <div class="ai-title">🤖 Analisa AI (Powered by OpenAI)</div>

        @php $ai = $analisa->ai_output; @endphp

        @if($ai)
            {{-- Ringkasan --}}
            @if(!empty($ai['ringkasan_utama']))
            <div class="ai-text" style="margin-bottom:12px">
                <strong style="color:#0369a1">Ringkasan Utama</strong><br>
                {{ $ai['ringkasan_utama'] }}
            </div>
            @endif

            {{-- Alokasi Aset --}}
            @if(!empty($ai['alokasi_aset']))
            <div style="margin-bottom:12px">
                <strong style="color:#0369a1;font-size:10px">Alokasi Aset</strong>
                <table style="margin-top:6px">
                    <thead><tr><th style="text-align:left">Kategori</th><th style="text-align:right">Persentase</th><th style="text-align:left">Keterangan</th></tr></thead>
                    <tbody>
                        @foreach($ai['alokasi_aset'] as $aa)
                        <tr>
                            <td style="text-align:left">{{ $aa['kategori'] }}</td>
                            <td style="text-align:right">{{ number_format($aa['persentase'], 2) }}%</td>
                            <td style="text-align:left">{{ $aa['keterangan'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Daftar Efek --}}
            @if(!empty($ai['daftar_efek']))
            <div style="margin-bottom:12px">
                <strong style="color:#0369a1;font-size:10px">Daftar Efek & Persentase</strong>
                <table style="margin-top:6px">
                    <thead><tr><th style="text-align:left">Kode</th><th style="text-align:left">Nama</th><th style="text-align:left">Sektor</th><th style="text-align:right">Bobot</th><th style="text-align:right">Kontribusi</th></tr></thead>
                    <tbody>
                        @foreach($ai['daftar_efek'] as $de)
                        <tr>
                            <td style="text-align:left">{{ $de['kode_efek'] }}</td>
                            <td style="text-align:left">{{ $de['nama_efek'] }}</td>
                            <td style="text-align:left">{{ $de['sektor'] ?? '-' }}</td>
                            <td style="text-align:right">{{ number_format($de['bobot'], 2) }}%</td>
                            <td style="text-align:right" class="{{ ($de['kontribusi_kinerja'] ?? 0) > 0 ? 'pos' : (($de['kontribusi_kinerja'] ?? 0) < 0 ? 'neg' : '') }}">
                                {{ isset($de['kontribusi_kinerja']) ? ($de['kontribusi_kinerja'] >= 0 ? '+' : '').$de['kontribusi_kinerja'].'%' : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Analisa Risiko --}}
            @if(!empty($ai['analisa_risiko']))
            <div class="ai-text" style="margin-bottom:12px">
                <strong style="color:#0369a1">Analisa Risiko</strong><br>
                {{ $ai['analisa_risiko'] }}
            </div>
            @endif

            {{-- Rekomendasi --}}
            @if(!empty($ai['rekomendasi_investor']))
            <div class="ai-text">
                <strong style="color:#0369a1">Rekomendasi Investor</strong><br>
                {{ $ai['rekomendasi_investor'] }}
            </div>
            @endif
        @else
            <div class="ai-text">{{ $analisa->ai_narasi }}</div>
        @endif
    </div>
    @endif

    <div class="two-col">
        {{-- Sektor --}}
        @if($analisa->sektor->isNotEmpty())
        <div class="section">
            <div class="section-title">Komposisi Sektor</div>
            <table>
                <thead><tr><th>Sektor</th><th style="text-align:right">Bobot (%)</th></tr></thead>
                <tbody>
                    @foreach($analisa->sektor->sortByDesc('bobot') as $s)
                    <tr><td>{{ $s->nama_sektor }}</td><td style="text-align:right">{{ number_format($s->bobot, 2) }}%</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Kinerja --}}
        @if($analisa->kinerja->isNotEmpty())
        <div class="section">
            <div class="section-title">Kinerja Bulanan</div>
            <table>
                <thead><tr><th>Periode</th><th style="text-align:right">Return (%)</th></tr></thead>
                <tbody>
                    @foreach($analisa->kinerja->sortBy('periode') as $k)
                    <tr>
                        <td>{{ $k->periode->format('M Y') }}</td>
                        <td style="text-align:right" class="{{ $k->return_pct >= 0 ? 'pos' : 'neg' }}">
                            {{ $k->return_pct >= 0 ? '+' : '' }}{{ $k->return_pct }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Efek --}}
    @if($analisa->efek->isNotEmpty())
    <div class="section">
        <div class="section-title">Daftar Efek — Attribution Analysis</div>
        <table>
            <thead>
                <tr>
                    <th>Kode</th><th>Nama Efek</th><th>Sektor</th>
                    <th style="text-align:right">Bobot</th>
                    <th style="text-align:right">Kontribusi</th>
                    <th style="text-align:center">Top 10</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analisa->efek->sortByDesc('bobot') as $e)
                <tr>
                    <td>{{ $e->kode_efek }}</td>
                    <td>{{ $e->nama_efek }}</td>
                    <td>{{ $e->sektor ?? '-' }}</td>
                    <td style="text-align:right">{{ number_format($e->bobot, 2) }}%</td>
                    <td style="text-align:right" class="{{ $e->kontribusi_kinerja > 0 ? 'pos' : ($e->kontribusi_kinerja < 0 ? 'neg' : '') }}">
                        {{ $e->kontribusi_kinerja !== null ? ($e->kontribusi_kinerja >= 0 ? '+' : '').$e->kontribusi_kinerja.'%' : '-' }}
                    </td>
                    <td style="text-align:center">{{ $e->top_10 ? '★' : '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="two-col">
        {{-- Obligasi --}}
        @if($analisa->obligasi->isNotEmpty())
        <div class="section">
            <div class="section-title">Obligasi — Durasi & Rating Risk</div>
            <table>
                <thead><tr><th>Obligasi</th><th style="text-align:right">Bobot</th><th style="text-align:right">Durasi</th><th style="text-align:center">Rating</th></tr></thead>
                <tbody>
                    @foreach($analisa->obligasi as $ob)
                    <tr>
                        <td>{{ $ob->nama_obligasi }}<br><span style="color:#94a3b8;font-size:9px">{{ $ob->kode_obligasi }}</span></td>
                        <td style="text-align:right">{{ number_format($ob->bobot, 2) }}%</td>
                        <td style="text-align:right">{{ $ob->durasi ? $ob->durasi.' thn' : '-' }}</td>
                        <td style="text-align:center">{{ $ob->rating ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Sukuk --}}
        @if($analisa->sukuk->isNotEmpty())
        <div class="section">
            <div class="section-title">Sukuk — Yield & Rating</div>
            <table>
                <thead><tr><th>Nama Sukuk</th><th style="text-align:right">Bobot</th><th style="text-align:right">Yield</th><th style="text-align:center">Jatuh Tempo</th><th style="text-align:center">Rating</th></tr></thead>
                <tbody>
                    @foreach($analisa->sukuk as $s)
                    <tr>
                        <td>{{ $s->nama_sukuk }}<br><span style="color:#94a3b8;font-size:9px">{{ $s->kode_sukuk }}</span></td>
                        <td style="text-align:right">{{ number_format($s->bobot, 2) }}%</td>
                        <td style="text-align:right">{{ $s->yield !== null ? number_format($s->yield, 4).'%' : '-' }}</td>
                        <td style="text-align:center">{{ $s->jatuh_tempo ?? '-' }}</td>
                        <td style="text-align:center">{{ $s->rating ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Bank --}}
        @if($analisa->bank->isNotEmpty())
        <div class="section">
            <div class="section-title">Bank Risk — CAR & NPL</div>
            <table>
                <thead><tr><th>Bank</th><th style="text-align:right">CAR</th><th style="text-align:right">NPL</th><th>Risiko</th></tr></thead>
                <tbody>
                    @foreach($analisa->bank as $b)
                    <tr>
                        <td>{{ $b->nama_bank }}</td>
                        <td style="text-align:right">{{ $b->car ? $b->car.'%' : '-' }}</td>
                        <td style="text-align:right">{{ $b->npl ? $b->npl.'%' : '-' }}</td>
                        <td>{{ $b->klasifikasi_risiko ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    @if($analisa->catatan_admin)
    <div class="section">
        <div class="section-title">Catatan Admin</div>
        <p style="font-size:10px;color:#1e293b">{{ $analisa->catatan_admin }}</p>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <span>InvestaPremier — Laporan Analisa Reksa Dana</span>
        <span>{{ $analisa->nama_reksa_dana }} · {{ now()->format('d M Y') }}</span>
    </div>

</div>
</body>
</html>
