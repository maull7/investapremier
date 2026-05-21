@php
    $ai = $ai ?? [];
    $narasi = $narasi ?? null;
@endphp

<div class="bg-white rounded-xl border border-line p-6">
    <div class="flex items-center gap-2 mb-4">
        <h3 class="font-semibold text-primary">{{ $title ?? 'Analisa AI' }}</h3>
        <span class="ml-auto text-xs text-muted bg-[#f1f5f9] px-2 py-1 rounded-full">Powered by Groq</span>
    </div>

    @if(!empty($ai['error']))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
            <p class="whitespace-pre-line">{{ $ai['message'] ?? 'Gagal memproses analisa AI.' }}</p>
        </div>
    @elseif($narasi)
        @if(!empty($ai['ringkasan_utama']))
        <div class="mb-5">
            <h4 class="text-sm font-semibold text-primary mb-2">Ringkasan Utama</h4>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $ai['ringkasan_utama'] }}</p>
        </div>
        @endif

        @if(!empty($ai['analisa_neraca']))
        <div class="mb-5">
            <h4 class="text-sm font-semibold text-primary mb-2">Analisa Neraca</h4>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $ai['analisa_neraca'] }}</p>
        </div>
        @endif

        @if(!empty($ai['analisa_laba_rugi']))
        <div class="mb-5">
            <h4 class="text-sm font-semibold text-primary mb-2">Analisa Laba Rugi</h4>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $ai['analisa_laba_rugi'] }}</p>
        </div>
        @endif

        @if(!empty($ai['analisa_arus_kas']))
        <div class="mb-5">
            <h4 class="text-sm font-semibold text-primary mb-2">Analisa Arus Kas</h4>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $ai['analisa_arus_kas'] }}</p>
        </div>
        @endif

        @if(!empty($ai['analisa_likuiditas']))
        <div class="mb-5">
            <h4 class="text-sm font-semibold text-primary mb-2">Analisa Likuiditas</h4>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $ai['analisa_likuiditas'] }}</p>
        </div>
        @endif

        @if(!empty($ai['analisa_solvabilitas']))
        <div class="mb-5">
            <h4 class="text-sm font-semibold text-primary mb-2">Analisa Solvabilitas</h4>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $ai['analisa_solvabilitas'] }}</p>
        </div>
        @endif

        @if(!empty($ai['analisa_profitabilitas']))
        <div class="mb-5">
            <h4 class="text-sm font-semibold text-primary mb-2">Analisa Profitabilitas</h4>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $ai['analisa_profitabilitas'] }}</p>
        </div>
        @endif

        @if(!empty($ai['rasio_keuangan']))
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
            @foreach(['current_ratio' => 'Current Ratio', 'debt_to_equity' => 'DER', 'net_profit_margin' => 'Net Margin', 'roe' => 'ROE'] as $mk => $ml)
                @if(isset($ai['rasio_keuangan'][$mk]) && $ai['rasio_keuangan'][$mk] !== null)
                <div class="bg-[#f8fafc] rounded-lg p-3 border border-line">
                    <p class="text-xs text-muted">{{ $ml }}</p>
                    <p class="text-lg font-bold text-primary mt-1">
                        {{ number_format($ai['rasio_keuangan'][$mk], 2) }}{{ in_array($mk, ['net_profit_margin','roe']) ? '%' : 'x' }}
                    </p>
                </div>
                @endif
            @endforeach
        </div>
        @endif

        @if(!empty($ai['rekomendasi']))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-blue-800 mb-1">Rekomendasi</h4>
            <p class="text-sm text-blue-700">{{ $ai['rekomendasi'] }}</p>
        </div>
        @endif

        @if(empty($ai))
        <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $narasi }}</div>
        @endif
    @else
        <div class="text-sm text-muted">Belum ada hasil analisa AI.</div>
    @endif
</div>
