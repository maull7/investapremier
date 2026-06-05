<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Perencanaan Investasi - {{ $plan->kategori_perencanaan }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        h1 { font-size: 18px; color: #166534; margin-bottom: 4px; }
        h2 { font-size: 14px; color: #166534; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin-top: 20px; }
        h3 { font-size: 12px; color: #333; margin: 12px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px 7px; text-align: left; }
        th { background: #f0fdf4; font-weight: 600; }
        .grid-2 { display: flex; gap: 10px; flex-wrap: wrap; }
        .grid-item { flex: 1; min-width: 120px; }
        .label { font-size: 9px; color: #888; text-transform: uppercase; }
        .value { font-weight: bold; font-size: 11px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .bg-green { background: #dcfce7; color: #166534; }
        .bg-blue { background: #dbeafe; color: #1e40af; }
        .bg-yellow { background: #fef9c3; color: #854d0e; }
        .bg-red { background: #fee2e2; color: #991b1b; }
        .section { margin: 8px 0; padding: 8px; background: #fafafa; border-left: 3px solid #166534; border-radius: 2px; }
        .footer { text-align: center; color: #999; font-size: 9px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>{{ $plan->kategori_perencanaan }}</h1>
    <p style="color:#888;font-size:10px;">Dibuat: {{ $plan->created_at->format('d F Y') }} | Status: {{ $plan->status }}</p>

    <h2>Data Perencanaan</h2>
    <table>
        <tr>
            <th style="width:50%">Kategori</th>
            <td>{{ $plan->kategori_perencanaan }}</td>
        </tr>
        <tr>
            <th>Kebutuhan Dana</th>
            <td>Rp {{ number_format($plan->kebutuhan_dana ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Target Waktu</th>
            <td>{{ $plan->target_waktu_tahun ? $plan->target_waktu_tahun . ' tahun' : '-' }}</td>
        </tr>
        <tr>
            <th>Portofolio Tersedia</th>
            <td>Rp {{ number_format($plan->dana_tersedia ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Investasi per Bulan</th>
            <td>Rp {{ number_format($plan->investasi_per_bulan ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Sumber Dana</th>
            <td>{{ $plan->sumber_dana ?? '-' }}</td>
        </tr>
        <tr>
            <th>Profil Risiko</th>
            <td>{{ $plan->profil_risiko ?? '-' }}</td>
        </tr>
    </table>

    @if ($plan->kategori_perencanaan === 'Pendidikan Anak')
    <h2>Data Pendidikan Anak</h2>
    <table>
        @if ($plan->usia_anak) <tr><th style="width:50%">Usia Anak</th><td>{{ $plan->usia_anak }}</td></tr> @endif
        @if ($plan->target_pendidikan) <tr><th>Target</th><td>{{ $plan->target_pendidikan }}</td></tr> @endif
        @if ($plan->tipe_pendidikan) <tr><th>Tipe</th><td>{{ $plan->tipe_pendidikan }}</td></tr> @endif
        @if ($plan->lokasi_pendidikan) <tr><th>Lokasi</th><td>{{ $plan->lokasi_pendidikan }}</td></tr> @endif
        @if ($plan->estimasi_biaya_saat_ini) <tr><th>Estimasi Biaya</th><td>Rp {{ number_format($plan->estimasi_biaya_saat_ini, 0, ',', '.') }}</td></tr> @endif
        @if ($plan->pemenuhan_dana) <tr><th>Pemenuhan Dana</th><td>Rp {{ number_format($plan->pemenuhan_dana, 0, ',', '.') }}</td></tr> @endif
    </table>
    @endif

    @if ($plan->portofolioItems->isNotEmpty())
    <h2>Portofolio Saat Ini</h2>
    <table>
        <thead>
            <tr><th>Jenis</th><th>Produk</th><th style="text-align:right">Nominal</th><th style="text-align:right">Harga</th><th style="text-align:right">Nilai</th></tr>
        </thead>
        <tbody>
            @foreach ($plan->portofolioItems as $item)
            <tr>
                <td>{{ $item->jenis }}</td>
                <td>{{ $item->nama_produk }}</td>
                <td style="text-align:right">{{ number_format($item->nominal, 0, ',', '.') }}</td>
                <td style="text-align:right">Rp {{ number_format($item->harga_akuisisi, 0, ',', '.') }}</td>
                <td style="text-align:right"><strong>Rp {{ number_format($item->nilai, 0, ',', '.') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight:bold;background:#f0fdf4;">
                <td colspan="4" style="text-align:right">Total Portofolio</td>
                <td style="text-align:right">Rp {{ number_format($plan->portofolioItems->sum('nilai'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if (!empty($plan->ai_output) && empty($plan->ai_output['error']))
    @php $ai = $plan->ai_output; @endphp
    <h2>Analisis & Rekomendasi AI</h2>

    @if (!empty($ai['ringkasan']))
    <div class="section">
        <strong>Ringkasan:</strong><br>{{ $ai['ringkasan'] }}
    </div>
    @endif

    @if (!empty($ai['analisis_keuangan']))
    <table>
        <tr>
            <th>Total Kebutuhan</th><th>Dana Saat Ini</th><th>Defisit</th><th>Investasi Bulanan</th>
        </tr>
        <tr>
            <td>{{ $ai['analisis_keuangan']['total_kebutuhan'] ?? '-' }}</td>
            <td>{{ $ai['analisis_keuangan']['dana_saat_ini'] ?? '-' }}</td>
            <td>{{ $ai['analisis_keuangan']['defisit'] ?? '-' }}</td>
            <td>{{ $ai['analisis_keuangan']['investasi_bulanan'] ?? '-' }}</td>
        </tr>
    </table>
    @endif

    @if (!empty($ai['proyeksi']))
    <table>
        <tr><th>Nilai Terkumpul</th><th>Ketercapaian</th><th>Gap Dana</th></tr>
        <tr>
            <td>{{ $ai['proyeksi']['nilai_terkumpul'] ?? '-' }}</td>
            <td>{{ $ai['proyeksi']['ketercapaian'] ?? '-' }}</td>
            <td>{{ $ai['proyeksi']['gap_dana'] ?? '-' }}</td>
        </tr>
    </table>
    @endif

    @if (!empty($ai['asumsi']))
    <table>
        <tr><th>Asumsi Inflasi</th><th>Asumsi Return</th></tr>
        <tr>
            <td>{{ $ai['asumsi']['inflasi'] ?? '-' }}</td>
            <td>{{ $ai['asumsi']['return_investasi'] ?? '-' }}</td>
        </tr>
    </table>
    @endif

    @if (!empty($ai['rekomendasi_strategi']))
    <h3>Rekomendasi Strategi</h3>
    <ul>
        @foreach ($ai['rekomendasi_strategi'] as $s)
        <li>{{ $s }}</li>
        @endforeach
    </ul>
    @endif

    @if (!empty($ai['alokasi_aset']))
    <h3>Alokasi Aset</h3>
    <table>
        <thead><tr><th>Jenis</th><th style="text-align:right">Persentase</th><th>Keterangan</th></tr></thead>
        <tbody>
            @foreach ($ai['alokasi_aset'] as $a)
            <tr><td>{{ $a['jenis'] ?? '' }}</td><td style="text-align:right">{{ $a['persentase'] ?? '' }}</td><td>{{ $a['keterangan'] ?? '' }}</td></tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if (!empty($ai['rekomendasi_portofolio']))
    <h3>Rekomendasi Portofolio</h3>
    <table>
        <thead><tr><th>Efek</th><th>Jenis</th><th>Analisa</th><th>Rekomendasi</th><th style="text-align:center">Aksi</th></tr></thead>
        <tbody>
            @foreach ($ai['rekomendasi_portofolio'] as $e)
            <tr>
                <td>{{ $e['nama_efek'] ?? '' }}</td>
                <td>{{ $e['jenis'] ?? '' }}</td>
                <td>{{ $e['analisa'] ?? '' }}</td>
                <td>{{ $e['rekomendasi'] ?? '' }}</td>
                <td style="text-align:center">{{ $e['aksi'] ?? 'Tahan' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if (!empty($ai['rekomendasi_investor']))
    <div class="section">
        <strong>Rekomendasi untuk Investor:</strong><br>{{ $ai['rekomendasi_investor'] }}
    </div>
    @endif

    @if (!empty($ai['catatan_risiko']))
    <div class="section" style="border-left-color:#d97706;">
        <strong>Catatan Risiko:</strong><br>{{ $ai['catatan_risiko'] }}
    </div>
    @endif
    @endif

    @if ($plan->progressCheckins->isNotEmpty())
    <h2>Riwayat Progress Check-in</h2>
    <table>
        <thead><tr><th>Tanggal</th><th style="text-align:right">Dana Terkumpul</th><th>Catatan</th></tr></thead>
        <tbody>
            @foreach ($plan->progressCheckins as $c)
            <tr>
                <td>{{ $c->tanggal_checkin->format('d M Y') }}</td>
                <td style="text-align:right">Rp {{ number_format($c->dana_terkumpul, 0, ',', '.') }}</td>
                <td>{{ $c->catatan ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        Dokumen ini dihasilkan oleh InvestaPremier — WealthOS<br>
        {{ now()->format('d F Y') }}
    </div>
</body>
</html>