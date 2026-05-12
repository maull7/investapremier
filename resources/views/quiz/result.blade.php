@extends('layouts.user')

@section('title', 'Hasil Profil Investasi')

@section('content')
@php
    $profileColors = [
        'Conservative' => ['bg' => 'bg-blue-600',  'light' => 'bg-blue-50',  'border' => 'border-blue-200',  'text' => 'text-blue-700',  'bar' => 'bg-blue-500'],
        'Tolerant'     => ['bg' => 'bg-green-600', 'light' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-700', 'bar' => 'bg-green-500'],
        'Moderate'     => ['bg' => 'bg-amber-500', 'light' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-700', 'bar' => 'bg-amber-500'],
        'Risk Taker'   => ['bg' => 'bg-red-600',   'light' => 'bg-red-50',   'border' => 'border-red-200',   'text' => 'text-red-700',   'bar' => 'bg-red-500'],
    ];
    $c = $profileColors[$result->profile] ?? $profileColors['Moderate'];

    $allProfiles = [
        ['label' => 'Conservative', 'range' => '8–12'],
        ['label' => 'Tolerant',     'range' => '13–20'],
        ['label' => 'Moderate',     'range' => '21–28'],
        ['label' => 'Risk Taker',   'range' => '29–32'],
    ];
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-primary">Hasil Profil Investasi</h1>
    <p class="text-muted text-sm mt-1">Berdasarkan jawaban kuesioner Anda</p>
</div>

{{-- Profil Utama --}}
<div class="bg-white rounded-2xl border {{ $c['border'] }} shadow-sm overflow-hidden mb-5">
    <div class="{{ $c['bg'] }} px-6 py-5 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-white/70 text-sm font-medium uppercase tracking-wide">Profil Investasi Anda</p>
                <h2 class="text-3xl font-extrabold mt-1">{{ $result->profile }}</h2>
            </div>
            <div class="text-right">
                <p class="text-white/70 text-sm">Total Skor</p>
                <p class="text-4xl font-extrabold">{{ $result->total_score }}</p>
            </div>
        </div>
    </div>

    {{-- Skala Profil --}}
    <div class="px-6 py-4 border-b {{ $c['border'] }} {{ $c['light'] }}">
        <div class="flex items-center gap-2">
            @foreach($allProfiles as $p)
            <div class="flex-1 text-center">
                <div class="text-xs font-semibold {{ $result->profile === $p['label'] ? $c['text'] : 'text-muted' }} mb-1">
                    {{ $p['label'] }}
                </div>
                <div class="h-2 rounded-full {{ $result->profile === $p['label'] ? $c['bar'] : 'bg-[#e2e8f0]' }}"></div>
                <div class="text-[10px] text-muted mt-1">{{ $p['range'] }}</div>
            </div>
            @if(!$loop->last)
            <div class="w-px h-8 bg-[#e2e8f0] shrink-0"></div>
            @endif
            @endforeach
        </div>
    </div>

    {{-- Alokasi Portofolio --}}
    <div class="px-6 py-5">
        <h3 class="font-bold text-primary mb-4">Rekomendasi Alokasi Portofolio</h3>
        <div class="space-y-3">
            @foreach($allocation as $instrument => $pct)
            <div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="font-medium text-primary">{{ $instrument }}</span>
                    <span class="font-bold {{ $c['text'] }}">{{ $pct }}%</span>
                </div>
                <div class="h-2.5 bg-[#e2e8f0] rounded-full overflow-hidden">
                    <div class="h-full {{ $c['bar'] }} rounded-full transition-all duration-500"
                         style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Semua Profil Perbandingan --}}
<div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-5">
    <div class="px-6 py-4 border-b border-line">
        <h3 class="font-bold text-primary">Tabel Profil Investasi Nasabah</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-muted text-xs uppercase tracking-wide">
                    <th class="px-5 py-3 text-left font-semibold">Instrumen</th>
                    <th class="px-5 py-3 text-center font-semibold">Conservative<br><span class="font-normal normal-case">8–12</span></th>
                    <th class="px-5 py-3 text-center font-semibold">Tolerant<br><span class="font-normal normal-case">13–20</span></th>
                    <th class="px-5 py-3 text-center font-semibold">Moderate<br><span class="font-normal normal-case">21–28</span></th>
                    <th class="px-5 py-3 text-center font-semibold">Risk Taker<br><span class="font-normal normal-case">29–32</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @php
                    $allAllocations = [
                        'Conservative' => \App\Models\QuizResult::allocationFromProfile('Conservative'),
                        'Tolerant'     => \App\Models\QuizResult::allocationFromProfile('Tolerant'),
                        'Moderate'     => \App\Models\QuizResult::allocationFromProfile('Moderate'),
                        'Risk Taker'   => \App\Models\QuizResult::allocationFromProfile('Risk Taker'),
                    ];
                    $instruments = array_keys($allAllocations['Conservative']);
                @endphp
                @foreach($instruments as $instrument)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-5 py-3.5 font-medium text-primary">{{ $instrument }}</td>
                    @foreach(['Conservative','Tolerant','Moderate','Risk Taker'] as $p)
                    <td class="px-5 py-3.5 text-center {{ $result->profile === $p ? $c['text'].' font-bold' : 'text-muted' }}">
                        {{ $allAllocations[$p][$instrument] }}%
                        @if($result->profile === $p)
                        <span class="ml-1">✓</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="flex items-center gap-3">
    <a href="{{ route('quiz.index') }}"
       class="px-5 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
        ← Isi Ulang Kuesioner
    </a>
    <a href="{{ route('user.dashboard') }}"
       class="px-5 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
        Kembali ke Dashboard
    </a>
</div>
@endsection
