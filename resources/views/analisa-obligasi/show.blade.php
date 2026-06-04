@extends('layouts.user')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'data',
    aiReady: {{ ($analisa->ai_output ?? false) ? 'true' : 'false' }},
    aiPlusReady: {{ ($analisa->ai_output_plus ?? false) ? 'true' : 'false' }},
    aiLoading: {{ ($analisa->ai_output ?? false) ? 'false' : 'true' }},
    aiPlusLoading: {{ ($analisa->ai_output_plus ?? false) ? 'false' : 'true' }},
    aiError: {{ !empty(($analisa->ai_output ?? [])['error']) ? 'true' : 'false' }},
    aiPlusError: {{ !empty(($analisa->ai_output_plus ?? [])['error']) ? 'true' : 'false' }},
    aiNarasi: null,
    aiOutput: null,
    aiNarasiPlus: null,
    aiOutputPlus: null,
    pollCount: 0,
    maxPolls: 30,
    init() {
        if (this.aiLoading || this.aiPlusLoading) {
            this.pollAiStatus();
        }
    },
    pollAiStatus() {
        if (this.pollCount >= this.maxPolls) return;
        this.pollCount++;
        fetch('{{ route($checkAiStatusRoute, $analisa) }}')
            .then(r => r.json())
            .then(d => {
                if (d.ai_ready) {
                    this.aiReady = true;
                    this.aiLoading = false;
                    this.aiNarasi = d.ai_narasi;
                    this.aiOutput = d.ai_output;
                }
                if (d.ai_plus_ready) {
                    this.aiPlusReady = true;
                    this.aiPlusLoading = false;
                    this.aiNarasiPlus = d.ai_narasi_plus;
                    this.aiOutputPlus = d.ai_output_plus;
                }
                if (d.ai_error) {
                    this.aiLoading = false;
                    this.aiNarasi = null;
                    this.aiOutput = { error: true, message: d.ai_error };
                }
                if (d.ai_plus_error) {
                    this.aiPlusLoading = false;
                    this.aiNarasiPlus = null;
                    this.aiOutputPlus = { error: true, message: d.ai_plus_error };
                }
                if ((this.aiLoading || this.aiPlusLoading) && this.pollCount < this.maxPolls) {
                    setTimeout(() => this.pollAiStatus(), 3000);
                }
            })
            .catch(() => {
                if (this.pollCount < this.maxPolls) {
                    setTimeout(() => this.pollAiStatus(), 5000);
                }
            });
    }
}">
    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-muted mb-1">
                <a href="{{ route('user.analisa-obligasi.index') }}" class="hover:text-primary">Analisa Obligasi</a>
                <span>/</span>
                <span>{{ $analisa->nama_obligasi }}</span>
            </div>
            <h1 class="page-title">{{ $analisa->nama_obligasi }}</h1>
            <p class="page-sub">
                {{ $analisa->kode_obligasi ? $analisa->kode_obligasi . ' · ' : '' }}{{ $analisa->nama_emiten ?? '' }}
                @if($analisa->periode) &bull; {{ $analisa->periode }} @endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @php
                $badge = match($analisa->status) {
                    'submitted' => 'bg-yellow-100 text-yellow-700',
                    'reviewed'  => 'bg-green-100 text-green-700',
                    default     => 'bg-gray-100 text-gray-600',
                };
                $statusLabel = match($analisa->status) {
                    'submitted' => 'Menunggu Review',
                    'reviewed'  => 'Sudah Direview',
                    default     => ucfirst($analisa->status),
                };
            @endphp
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $statusLabel }}</span>
            @php
                $ratingBadge = fn($r) => match(true) {
                    in_array($r, ['AAA', 'AA+', 'AA', 'AA-']) => 'bg-green-100 text-green-700',
                    in_array($r, ['A+', 'A', 'A-']) => 'bg-blue-100 text-blue-700',
                    in_array($r, ['BBB+', 'BBB', 'BBB-']) => 'bg-amber-100 text-amber-700',
                    default => 'bg-red-100 text-red-700',
                };
            @endphp
            @if($analisa->shadow_rating)
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ratingBadge($analisa->shadow_rating) }}"
                    title="Shadow Rating: skor {{ $analisa->shadow_score }}, confidence {{ $analisa->shadow_confidence }}%">
                    SR: {{ $analisa->shadow_rating }}
                </span>
            @endif
            @if($analisa->official_rating)
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ratingBadge($analisa->official_rating) }}">
                    Official: {{ $analisa->official_rating }}
                </span>
            @elseif($analisa->rating)
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ratingBadge($analisa->rating) }}">
                    Rating: {{ $analisa->rating }}
                </span>
            @endif
            @if($analisa->ytm_spread !== null)
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $analisa->ytm_spread > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}"
                    title="YTM {{ $analisa->ytm }}% - Normal {{ $analisa->ytm_normal }}%">
                    Spread: {{ $analisa->ytm_spread > 0 ? '+' : '' }}{{ number_format($analisa->ytm_spread, 4) }}%
                </span>
            @endif
            @if($analisa->rating_source)
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                    {{ ucfirst($analisa->rating_source) }}
                </span>
            @endif
            @if($analisa->pdf_path)
                <a href="{{ route('user.analisa-obligasi.download-lapkeu', $analisa) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    PDF Lapkeu
                </a>
            @endif
            <a href="{{ route('user.analisa-obligasi.pdf', $analisa) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-1 border-b border-line overflow-x-auto">
        <button type="button" @click="activeTab='data'"
            :class="activeTab==='data' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
            class="px-4 py-2.5 text-sm whitespace-nowrap transition">Laporan Keuangan</button>
        <button type="button" @click="activeTab='ai'"
            :class="activeTab==='ai' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
            class="px-4 py-2.5 text-sm whitespace-nowrap transition">Analisa AI</button>
        <button type="button" @click="activeTab='ai-plus'"
            :class="activeTab==='ai-plus' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
            class="px-4 py-2.5 text-sm whitespace-nowrap transition">Analisa AI Plus</button>
    </div>

    <div x-show="activeTab==='data'" class="space-y-6">
        @include('analisa-obligasi.partials.show-lapkeu')
    </div>

    <div x-show="activeTab==='ai'">
        <template x-if="aiLoading">
            <div class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span>Memproses Analisa AI ...</span>
            </div>
        </template>
        <template x-if="!aiLoading && aiReady">
            <div>
                @include('analisa-lapkeu.partials.ai-panel', ['title' => 'Analisa AI', 'ai' => $analisa->ai_output ?? [], 'narasi' => $analisa->ai_narasi])
            </div>
        </template>
        <template x-if="!aiLoading && !aiReady && aiOutput?.error">
            <div class="bg-red-50 border border-red-200 rounded-xl p-5 text-sm text-red-700" x-text="aiOutput.message || 'Analisa AI gagal diproses.'"></div>
        </template>
        <template x-if="!aiLoading && !aiReady && !aiOutput">
            <div class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted">Narasi AI belum tersedia.</div>
        </template>
    </div>

    <div x-show="activeTab==='ai-plus'">
        <template x-if="aiPlusLoading">
            <div class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span>Memproses Analisa AI Plus ...</span>
            </div>
        </template>
        <template x-if="!aiPlusLoading && aiPlusReady">
            <div>
                @include('analisa-lapkeu.partials.ai-panel', ['title' => 'Analisa AI Plus', 'ai' => $analisa->ai_output_plus ?? [], 'narasi' => $analisa->ai_narasi_plus])
            </div>
        </template>
        <template x-if="!aiPlusLoading && !aiPlusReady && aiOutputPlus?.error">
            <div class="bg-red-50 border border-red-200 rounded-xl p-5 text-sm text-red-700" x-text="aiOutputPlus.message || 'Analisa AI Plus gagal diproses.'"></div>
        </template>
        <template x-if="!aiPlusLoading && !aiPlusReady && !aiOutputPlus">
            <div class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted">Narasi AI Plus belum tersedia.</div>
        </template>
    </div>
</div>
@endsection
