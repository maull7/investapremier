<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Portfolio - {{ $user->name }}</title>
    <style>
        @page { margin: 30mm 25mm 25mm 25mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #000; line-height: 1.6; }

        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 16pt; font-weight: bold; margin: 0 0 4px; text-transform: uppercase; letter-spacing: 2px; }
        .header p { font-size: 9pt; color: #555; margin: 0; }
        .header .divider { border-top: 2px solid #000; margin-top: 12px; width: 100%; }

        .date-info { text-align: right; font-size: 9pt; color: #444; margin-bottom: 25px; }

        .section { margin-bottom: 20px; }
        .section-title { font-size: 11pt; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000; padding-bottom: 4px; margin-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; font-size: 9pt; margin: 8px 0; }
        table th, table td { border: 1px solid #000; padding: 5px 8px; text-align: left; }
        table th { background: #f0f0f0; font-weight: bold; }
        table td { background: #fff; }
        .table-summary td { border: none; padding: 3px 8px; }
        .table-summary td:last-child { text-align: right; font-weight: bold; }

        .info-grid { margin-bottom: 6px; }
        .info-grid .row { display: flex; justify-content: space-between; padding: 3px 0; border-bottom: 1px dotted #ccc; font-size: 9pt; }
        .info-grid .row strong { font-weight: bold; }

        .goal-item { margin-bottom: 12px; }
        .goal-item .goal-header { display: flex; justify-content: space-between; font-size: 9pt; margin-bottom: 3px; }
        .goal-bar { height: 8px; background: #e0e0e0; border-radius: 0; overflow: hidden; }
        .goal-bar-fill { height: 100%; background: #000; border-radius: 0; }
        .goal-detail { display: flex; justify-content: space-between; font-size: 8pt; color: #555; margin-top: 2px; }

        .footer { text-align: center; font-size: 8pt; color: #888; margin-top: 35px; border-top: 1px solid #ccc; padding-top: 10px; }

        .page-break { page-break-before: always; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-muted { color: #666; font-size: 9pt; }
    </style>
</head>
<body>
    {{-- Kop Surat --}}
    <div class="header">
        <h1>Laporan Portfolio Investasi</h1>
        <p>InvestaPremier &mdash; Wealth Management Platform</p>
        <div class="divider"></div>
    </div>

    <div class="date-info">
        {{ now()->format('d F Y') }}
    </div>

    {{-- Data Nasabah --}}
    <div class="section">
        <div class="section-title">Data Nasabah</div>
        <table>
            <tr>
                <th style="width:35%">Nama</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $user->email }}</td>
            </tr>
            @if ($user->memberProfile)
            <tr>
                <th>Usia</th>
                <td>{{ $user->memberProfile->tanggal_lahir?->age ?? '—' }} tahun</td>
            </tr>
            <tr>
                <th>Pekerjaan</th>
                <td>{{ $user->memberProfile->pekerjaan ?? '—' }}</td>
            </tr>
            @endif
            <tr>
                <th>Profil Risiko</th>
                <td>{{ $portfolio['riskProfile'] ?? 'Belum diketahui' }}</td>
            </tr>
            @if ($portfolio['advisor'])
            <tr>
                <th>Advisor</th>
                <td>{{ $portfolio['advisor']['name'] }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Ringkasan Keuangan --}}
    <div class="section">
        <div class="section-title">Ringkasan Portfolio</div>
        <table>
            <tr>
                <th style="width:50%">Total Kekayaan</th>
                <td class="text-right text-bold">{{ $portfolio['totalKekayaanFormatted'] }}</td>
            </tr>
            <tr>
                <th>Aset Investasi</th>
                <td class="text-right text-bold">{{ $portfolio['asetInvestasiFormatted'] }} ({{ $portfolio['asetInvestasiPct'] }}%)</td>
            </tr>
            <tr>
                <th>Likuiditas</th>
                <td class="text-right text-bold">{{ $portfolio['likuiditasFormatted'] }} ({{ $portfolio['likuiditasPct'] }}%)</td>
            </tr>
        </table>
    </div>

    {{-- Alokasi Aset --}}
    @if (count($portfolio['alokasiAset'] ?? []) > 0)
    <div class="section">
        <div class="section-title">Alokasi Aset</div>
        <table>
            <thead>
                <tr>
                    <th>Jenis Aset</th>
                    <th class="text-right">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($portfolio['alokasiAset'] as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td class="text-right">{{ $item['pct'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Goals --}}
    @if (count($portfolio['goals'] ?? []) > 0)
    <div class="page-break"></div>
    <div class="section">
        <div class="section-title">Progress Tujuan Keuangan</div>
        @foreach ($portfolio['goals'] as $goal)
        <div class="goal-item">
            <div class="goal-header">
                <span class="text-bold">{{ $goal['nama'] }}</span>
                <span>{{ $goal['pct'] }}%</span>
            </div>
            <div class="goal-bar">
                <div class="goal-bar-fill" style="width: {{ $goal['pct'] }}%"></div>
            </div>
            <div class="goal-detail">
                <span>Target: {{ $goal['targetFormatted'] }}</span>
                <span>Terkumpul: {{ $goal['terkumpulFormatted'] }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Catatan --}}
    <div class="section">
        <div class="section-title">Catatan</div>
        <p style="font-size: 9pt; color: #555;">
            Laporan ini dibuat secara otomatis oleh sistem InvestaPremier berdasarkan data portofolio yang tersedia.
            Data yang ditampilkan adalah ringkasan dan tidak menggantikan laporan resmi dari lembaga keuangan terkait.
        </p>
    </div>

    <div class="footer">
        <p>Laporan ini digenerate pada {{ now()->format('d F Y H:i') }} &mdash; InvestaPremier WealthOS</p>
        <p>Dokumen ini sah tanpa tanda tangan mengingat sistem elektronik.</p>
    </div>
</body>
</html>
