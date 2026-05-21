@extends('layouts.user')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'data' }">
    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-muted mb-1">
                <a href="{{ route('user.analisa-saham.index') }}" class="hover:text-primary">Analisa Saham</a>
                <span>/</span>
                <span>{{ $analisa->nama_perusahaan }}</span>
            </div>
            <h1 class="text-xl font-bold text-primary">{{ $analisa->nama_perusahaan }}</h1>
            <p class="text-sm text-muted mt-0.5">
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
        @else
            <div class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted">Narasi AI belum tersedia.</div>
        @endif
    </div>
</div>
@endsection
