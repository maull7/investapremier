@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ session('active_tab', $errors->has('broker') || $errors->has('documents') || $errors->has('documents.*') ? 'riset-broker' : 'data') }}',
    aiReady: {{ ($analisa->ai_output ?? false) ? 'true' : 'false' }},
    aiPlusReady: {{ ($analisa->ai_output_plus ?? false) ? 'true' : 'false' }},
    aiLoading: {{ ($analisa->ai_output ?? false) ? 'false' : 'true' }},
    aiPlusLoading: {{ ($analisa->ai_output_plus ?? false) ? 'false' : 'true' }},
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
                if (d.ai_ready || d.ai_error) window.location.reload();
                if (d.ai_plus_ready || d.ai_plus_error) window.location.reload();
                if ((this.aiLoading || this.aiPlusLoading) && this.pollCount < this.maxPolls) {
                    setTimeout(() => this.pollAiStatus(), 3000);
                }
            })
            .catch(() => {
                if (this.pollCount < this.maxPolls) setTimeout(() => this.pollAiStatus(), 5000);
            });
    }
}">
    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-muted mb-1">
                <a href="{{ route('admin.analisa-saham.index') }}" class="hover:text-primary">Monitor Analisa Saham</a>
                <span>/</span>
                <span>{{ $analisa->nama_perusahaan }}</span>
            </div>
            <h1 class="page-title">{{ $analisa->nama_perusahaan }}</h1>
            <p class="page-sub">
                {{ $analisa->kode_saham ? $analisa->kode_saham . ' · ' : '' }}{{ $analisa->sektor ?? '' }}
                @if($analisa->periode_dari && $analisa->periode_sampai)
                    &bull; {{ $analisa->periode_dari }} - {{ $analisa->periode_sampai }}
                @elseif($analisa->periode)
                    &bull; {{ $analisa->periode }}
                @endif
                &bull; Disubmit oleh <strong>{{ $analisa->user->name }}</strong> pada {{ $analisa->created_at->format('d M Y') }}
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
                <a href="{{ route('admin.analisa-saham.download-lapkeu', $analisa) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    PDF Lapkeu
                </a>
            @endif
            <a href="{{ route('admin.analisa-saham.pdf', $analisa) }}" target="_blank"
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
        <button type="button" @click="activeTab='review'"
            :class="activeTab==='review' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
            class="px-4 py-2.5 text-sm whitespace-nowrap transition">Review</button>
    </div>

    {{-- Tab: Data --}}
    <div x-show="activeTab==='data'" class="space-y-6">
        @if(!empty($analisa->saham_pembanding_data) && is_array($analisa->saham_pembanding_data))
            <div class="bg-white rounded-xl border border-line p-6">
                <h3 class="font-semibold text-primary mb-4">Saham Pembanding</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead class="bg-gray-50 text-muted text-xs">
                            <tr>
                                <th class="px-3 py-2 text-left border-b border-line">Kode</th>
                                <th class="px-3 py-2 text-left border-b border-line">Nama Perusahaan</th>
                                <th class="px-3 py-2 text-left border-b border-line">Sektor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach($analisa->saham_pembanding_data as $item)
                                <tr>
                                    <td class="px-3 py-2 font-semibold">{{ $item['kode'] ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $item['nama'] ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $item['sektor'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

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

    {{-- Tab: AI Plus --}}
    <div x-show="activeTab==='ai-plus'">
        @php $aiPlusOut = $analisa->ai_output_plus ?? []; @endphp
        @if($analisa->ai_narasi_plus || !empty($aiPlusOut))
            @include('analisa-lapkeu.partials.ai-panel', ['title' => 'Analisa AI Plus', 'ai' => $aiPlusOut, 'narasi' => $analisa->ai_narasi_plus])
        @else
            <div class="bg-[#f8fafc] border border-dashed border-line rounded-xl p-5 text-sm text-muted">Narasi AI Plus belum tersedia.</div>
        @endif
    </div>

    {{-- Tab: Riset Broker --}}
    <div x-show="activeTab==='riset-broker'" class="space-y-6">
        @include('analisa-saham.partials.riset-broker')
    </div>

    {{-- Tab: Review --}}
    <div x-show="activeTab==='review'" class="space-y-4">
        @if($analisa->status !== 'reviewed')
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-4">Tandai Sudah Direview</h3>
            <form method="POST" action="{{ route('admin.analisa-saham.review', $analisa) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Admin (opsional)</label>
                    <textarea name="catatan_admin" rows="4" placeholder="Tuliskan catatan atau feedback untuk user..."
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">{{ $analisa->catatan_admin }}</textarea>
                </div>
                <button type="submit" class="px-5 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition">
                    Tandai Reviewed
                </button>
            </form>
        </div>
        @else
        <div class="bg-green-50 border border-green-200 rounded-xl p-5">
            <p class="text-sm text-green-700 font-medium">Data telah direview.</p>
            @if($analisa->catatan_admin)
                <p class="text-sm text-green-600 mt-2 whitespace-pre-wrap">{{ $analisa->catatan_admin }}</p>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
