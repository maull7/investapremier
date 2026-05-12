@extends('layouts.user')

@section('title', 'Kuesioner Profil Investasi')

@section('content')

@if(!request()->boolean('start'))
{{-- ===== HALAMAN KONFIRMASI / INTRO ===== --}}
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-primary to-primary-light px-8 py-8 text-white text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/10 grid place-items-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <h1 class="text-2xl font-extrabold">Kuesioner Profil Investasi</h1>
            <p class="text-white/70 text-sm mt-1">Temukan profil investasi yang sesuai dengan Anda</p>
        </div>

        <div class="px-8 py-6">
            {{-- Info kuis --}}
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="text-center p-4 bg-[#f8fafc] rounded-xl border border-line">
                    <div class="text-2xl font-extrabold text-primary">{{ $questions->count() }}</div>
                    <div class="text-xs text-muted mt-0.5">Pertanyaan</div>
                </div>
                <div class="text-center p-4 bg-[#f8fafc] rounded-xl border border-line">
                    <div class="text-2xl font-extrabold text-primary">~5</div>
                    <div class="text-xs text-muted mt-0.5">Menit</div>
                </div>
                <div class="text-center p-4 bg-[#f8fafc] rounded-xl border border-line">
                    <div class="text-2xl font-extrabold text-primary">4</div>
                    <div class="text-xs text-muted mt-0.5">Profil</div>
                </div>
            </div>

            {{-- Petunjuk --}}
            <div class="mb-6">
                <h3 class="font-bold text-primary text-sm mb-3">Petunjuk Pengisian</h3>
                <ul class="space-y-2 text-sm text-muted">
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-accent shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                        Pilih <strong class="text-primary">satu jawaban</strong> yang paling sesuai dengan kondisi dan preferensi Anda
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-accent shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                        Jawab dengan <strong class="text-primary">jujur</strong> agar hasil profil mencerminkan kondisi sebenarnya
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-accent shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                        Semua pertanyaan <strong class="text-primary">wajib dijawab</strong> sebelum melihat hasil
                    </li>
                    @if($result)
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        Mengisi ulang akan <strong class="text-primary">mengganti</strong> hasil profil sebelumnya (saat ini: <strong class="text-primary">{{ $result->profile }}</strong>)
                    </li>
                    @endif
                </ul>
            </div>

            {{-- Profil hasil --}}
            <div class="mb-6 p-4 bg-[#f8fafc] rounded-xl border border-line">
                <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-3">Hasil Profil yang Mungkin Anda Dapatkan</p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach([['Conservative','8–12','bg-blue-50 border-blue-200 text-blue-700'],['Tolerant','13–20','bg-green-50 border-green-200 text-green-700'],['Moderate','21–28','bg-amber-50 border-amber-200 text-amber-700'],['Risk Taker','29–32','bg-red-50 border-red-200 text-red-700']] as [$label,$range,$cls])
                    <div class="border rounded-lg px-3 py-2 {{ $cls }} {{ $result && $result->profile === $label ? 'ring-2 ring-offset-1 ring-current' : '' }}">
                        <div class="font-bold text-xs">{{ $label }}</div>
                        <div class="text-xs opacity-70">Skor {{ $range }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- CTA --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('quiz.index', ['start' => 1]) }}"
                   class="flex-1 text-center px-6 py-3 bg-accent text-white rounded-xl font-semibold text-sm hover:bg-accent/90 transition shadow-sm shadow-accent/20">
                    {{ $result ? 'Isi Ulang Kuesioner' : 'Mulai Kuesioner' }}
                </a>
                @if($result)
                <a href="{{ route('quiz.result') }}"
                   class="px-6 py-3 border border-line text-muted rounded-xl font-semibold text-sm hover:text-primary hover:border-primary/30 transition">
                    Lihat Hasil
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

@else
{{-- ===== HALAMAN SOAL ===== --}}
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-primary">Kuesioner Profil Investasi</h1>
        <p class="text-muted text-sm mt-1">Jawab semua pertanyaan berikut untuk mengetahui profil investasi Anda</p>
    </div>
    <a href="{{ route('quiz.index') }}" class="text-sm text-muted hover:text-primary transition flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
</div>

@if($questions->isEmpty())
<div class="bg-white rounded-2xl border border-line p-12 text-center text-muted shadow-sm">
    <p class="font-medium">Kuesioner belum tersedia</p>
    <p class="text-sm mt-1">Silakan hubungi admin untuk informasi lebih lanjut</p>
</div>
@else
<form method="POST" action="{{ route('quiz.submit') }}">
    @csrf
    <div class="space-y-5">
        @foreach($questions as $q)
        <div class="bg-white rounded-2xl border border-line shadow-sm p-6 @error("answers.$q->id") border-red-300 @enderror">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary font-bold text-sm grid place-items-center shrink-0">{{ $q->order }}</div>
                <p class="font-semibold text-primary pt-1">{{ $q->question_text }}</p>
            </div>
            <div class="space-y-2 ml-11">
                @foreach($q->options as $opt)
                <label class="flex items-start gap-3 p-3 rounded-xl border border-line cursor-pointer hover:border-accent/40 hover:bg-accent/5 transition has-[:checked]:border-accent has-[:checked]:bg-accent/5">
                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt->id }}"
                           class="mt-0.5 accent-accent shrink-0"
                           {{ old("answers.$q->id") == $opt->id ? 'checked' : '' }}>
                    <div class="flex-1 min-w-0">
                        <span class="font-semibold text-primary text-sm">{{ $opt->label }}.</span>
                        <span class="text-sm text-primary ml-1">{{ $opt->option_text }}</span>
                    </div>
                </label>
                @endforeach
            </div>
            @error("answers.$q->id")
            <p class="text-red-500 text-xs mt-2 ml-11">{{ $message }}</p>
            @enderror
        </div>
        @endforeach
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button type="submit"
                class="px-6 py-3 bg-accent text-white rounded-xl font-semibold text-sm hover:bg-accent/90 transition shadow-sm shadow-accent/20">
            Lihat Hasil Profil Saya
        </button>
        <a href="{{ route('quiz.index') }}" class="px-6 py-3 border border-line text-muted rounded-xl font-semibold text-sm hover:text-primary hover:border-primary/30 transition">
            Batal
        </a>
    </div>
</form>
@endif

@endif
@endsection
