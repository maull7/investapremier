@php
    $ai = $ai ?? [];
    $narasi = $narasi ?? null;
    $variant = $variant ?? 'standard';
@endphp

<div class="bg-white rounded-xl border border-line p-6">
    <div class="flex items-center gap-2 mb-4">
        <span class="text-lg">🤖</span>
        <h3 class="font-semibold text-primary">{{ $title ?? 'Analisa AI' }}</h3>
        <span class="ml-auto text-xs text-muted bg-[#f1f5f9] px-2 py-1 rounded-full">Powered by OpenAI</span>
    </div>

    @if(!empty($ai['error']))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700 space-y-2">
            <p class="font-semibold">Data Input Manual belum lengkap</p>
            @if(!empty($ai['missing']) && is_array($ai['missing']))
                <p>Lengkapi bagian berikut di tab Input Manual:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($ai['missing'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @else
                <p class="whitespace-pre-line">{{ $ai['message'] ?? 'Gagal memproses analisa AI.' }}</p>
            @endif
        </div>
    @elseif($narasi)
        @if($variant === 'plus')
            @foreach([
                'ringkasan_utama' => 'Ringkasan Utama',
                'analisa_kinerja' => 'Analisa Kinerja',
                'analisa_risiko' => 'Analisa Risiko',
                'analisa_likuiditas' => 'Analisa Likuiditas',
                'rekomendasi_investor' => 'Rekomendasi Investor',
            ] as $key => $label)
                @if(!empty($ai[$key]))
                <div class="mb-5">
                    <h4 class="text-sm font-semibold text-primary mb-2">{{ $label }}</h4>
                    <div class="text-sm text-gray-700 leading-relaxed">{{ $ai[$key] }}</div>
                </div>
                @endif
            @endforeach

            @if(!empty($ai['metrik_saran']) && is_array($ai['metrik_saran']))
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
                @foreach(['sharpe_ratio' => 'Sharpe Ratio', 'rar' => 'RAR', 'liquidity_ratio' => 'Liquidity Ratio', 'durasi_rata_rata' => 'Durasi Rata-rata'] as $mk => $ml)
                <div class="bg-[#f8fafc] rounded-lg p-3 border border-line">
                    <p class="text-xs text-muted">{{ $ml }}</p>
                    <p class="text-lg font-bold text-primary mt-1">{{ $ai['metrik_saran'][$mk] ?? '—' }}</p>
                </div>
                @endforeach
            </div>
            @endif
        @elseif(!empty($ai))
            @if(!empty($ai['ringkasan_utama']))
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-primary mb-2">Ringkasan Utama</h4>
                <div class="text-sm text-gray-700 leading-relaxed">{{ $ai['ringkasan_utama'] }}</div>
            </div>
            @endif

            @if(!empty($ai['alokasi_aset']))
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-primary mb-2">Alokasi Aset</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#f8fafc] border-b border-line">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-semibold text-primary">Kategori</th>
                                <th class="text-right px-4 py-2.5 font-semibold text-primary">Persentase</th>
                                <th class="text-left px-4 py-2.5 font-semibold text-primary">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach($ai['alokasi_aset'] as $aa)
                            <tr>
                                <td class="px-4 py-2.5 font-medium">{{ $aa['kategori'] }}</td>
                                <td class="px-4 py-2.5 text-right font-mono">{{ number_format($aa['persentase'], 2) }}%</td>
                                <td class="px-4 py-2.5 text-muted">{{ $aa['keterangan'] ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if(!empty($ai['daftar_efek']))
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-primary mb-2">Daftar Efek & Persentase</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#f8fafc] border-b border-line">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-semibold text-primary">Kode</th>
                                <th class="text-left px-4 py-2.5 font-semibold text-primary">Nama Efek</th>
                                <th class="text-left px-4 py-2.5 font-semibold text-primary">Sektor</th>
                                <th class="text-right px-4 py-2.5 font-semibold text-primary">Bobot (%)</th>
                                <th class="text-right px-4 py-2.5 font-semibold text-primary">Kontribusi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach($ai['daftar_efek'] as $de)
                            <tr>
                                <td class="px-4 py-2.5 font-mono text-xs">{{ $de['kode_efek'] }}</td>
                                <td class="px-4 py-2.5">{{ $de['nama_efek'] }}</td>
                                <td class="px-4 py-2.5 text-muted">{{ $de['sektor'] ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-right font-mono">{{ number_format($de['bobot'], 2) }}%</td>
                                <td class="px-4 py-2.5 text-right font-mono">{{ isset($de['kontribusi_kinerja']) ? ($de['kontribusi_kinerja'] >= 0 ? '+' : '').$de['kontribusi_kinerja'].'%' : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if(!empty($ai['analisa_risiko']))
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-primary mb-2">Analisa Risiko</h4>
                <div class="text-sm text-gray-700 leading-relaxed">{{ $ai['analisa_risiko'] }}</div>
            </div>
            @endif

            @if(!empty($ai['rekomendasi_investor']))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-blue-800 mb-1">Rekomendasi Investor</h4>
                <div class="text-sm text-blue-700">{{ $ai['rekomendasi_investor'] }}</div>
            </div>
            @endif
        @else
            <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $narasi }}</div>
        @endif
    @else
        <div class="text-sm text-muted">{{ $emptyMessage ?? 'Belum ada hasil analisa.' }}</div>
    @endif
</div>
