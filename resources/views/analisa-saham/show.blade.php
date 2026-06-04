@extends('layouts.user')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ session('active_tab', $errors->has('broker') || $errors->has('documents') || $errors->has('documents.*') ? 'riset-broker' : 'data') }}',
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
                <a href="{{ route('user.analisa-saham.index') }}" class="hover:text-primary">Analisa Saham</a>
                <span>/</span>
                <span>{{ $analisa->nama_perusahaan }}</span>
            </div>
            <h1 class="page-title">{{ $analisa->nama_perusahaan }}</h1>
            <p class="page-sub">
                {{ $analisa->kode_saham ? $analisa->kode_saham . ' · ' : '' }}{{ $analisa->sektor ?? '' }}
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
            @if($analisa->pdf_path)
                <a href="{{ route('user.analisa-saham.download-lapkeu', $analisa) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    PDF Lapkeu
                </a>
            @endif
            <a href="{{ route('user.analisa-saham.pdf', $analisa) }}" target="_blank"
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
        <button type="button" @click="activeTab='riset-broker'"
            :class="activeTab==='riset-broker' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
            class="px-4 py-2.5 text-sm whitespace-nowrap transition">Riset Broker</button>
    </div>

    {{-- Tab: Data --}}
    <div x-show="activeTab==='data'" class="space-y-6">
        @include('analisa-saham.partials.show-lapkeu')
    </div>

    {{-- Tab: AI --}}
    <div x-show="activeTab==='ai'">
        @php $aiOut = $analisa->ai_output ?? []; @endphp
        @if($analisa->ai_narasi || !empty($aiOut))
            @include('analisa-lapkeu.partials.ai-panel', ['title' => 'Analisa AI', 'ai' => $aiOut, 'narasi' => $analisa->ai_narasi])
        @elseif(!empty($aiOut['error']))
            <div class="bg-red-50 border border-red-200 rounded-xl p-5 text-sm text-red-700">{{ $aiOut['message'] ?? 'Analisa AI gagal diproses.' }}</div>
        @else
            <div x-show="aiLoading" class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span>Memproses Analisa AI ...</span>
            </div>
            <div x-show="!aiLoading && !aiReady" class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted">Narasi AI belum tersedia.</div>
        @endif
    </div>

    {{-- Tab: AI Plus --}}
    <div x-show="activeTab==='ai-plus'">
        @php $aiPlusOut = $analisa->ai_output_plus ?? []; @endphp
        @if($analisa->ai_narasi_plus || !empty($aiPlusOut))
            @include('analisa-lapkeu.partials.ai-panel', ['title' => 'Analisa AI Plus', 'ai' => $aiPlusOut, 'narasi' => $analisa->ai_narasi_plus])
        @elseif(!empty($aiPlusOut['error']))
            <div class="bg-red-50 border border-red-200 rounded-xl p-5 text-sm text-red-700">{{ $aiPlusOut['message'] ?? 'Analisa AI Plus gagal diproses.' }}</div>
        @else
            <div x-show="aiPlusLoading" class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span>Memproses Analisa AI Plus ...</span>
            </div>
            <div x-show="!aiPlusLoading && !aiPlusReady" class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted">Narasi AI Plus belum tersedia.</div>
        @endif
    </div>

    {{-- Tab: Riset Broker --}}
    <div x-show="activeTab==='riset-broker'" class="space-y-6">
        @include('analisa-saham.partials.riset-broker')
    </div>
</div>
@endsection
