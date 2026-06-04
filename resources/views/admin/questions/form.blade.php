@extends('layouts.admin')

@section('title', isset($question) ? 'Edit Soal' : 'Tambah Soal')

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-muted mb-3">
            <a href="{{ route('admin.questions.index') }}" class="hover:text-primary transition">Soal Kuis</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-primary font-medium">{{ isset($question) ? 'Edit Soal' : 'Tambah Soal' }}</span>
        </div>
        <h1 class="page-title">{{ isset($question) ? 'Edit Soal' : 'Tambah Soal Baru' }}</h1>
    </div>

    <form method="POST"
        action="{{ isset($question) ? route('admin.questions.update', $question) : route('admin.questions.store') }}"
        class="max-w-2xl">
        @csrf
        @if (isset($question))
            @method('PUT')
        @endif

        <div class="bg-white rounded-2xl border border-line shadow-sm p-6 space-y-5">

            {{-- Teks Soal --}}
            <div>
                <x-input-label value="Pertanyaan" class="text-sm font-semibold mb-1.5" />
                <textarea name="question_text" rows="3"
                    class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent resize-none @error('question_text') border-red-400 @enderror"
                    placeholder="Tulis pertanyaan di sini...">{{ old('question_text', $question->question_text ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('question_text')" class="mt-1 text-xs" />
            </div>

            {{-- Pilihan Jawaban --}}
            <div>
                <x-input-label value="Pilihan Jawaban & Poin" class="text-sm font-semibold mb-3" />
                <div class="space-y-3">
                    @php
                        $labels = ['A', 'B', 'C', 'D'];
                        $existingOptions = isset($question) ? $question->options->keyBy('label') : collect();
                    @endphp
                    @foreach ($labels as $i => $label)
                        @php
                            $opt = $existingOptions->get($label);
                            $oldOpt = old("options.$i");
                        @endphp

                        <div class="flex items-start gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-primary/10 text-primary font-bold text-sm grid place-items-center shrink-0 mt-1">
                                {{ $label }}
                            </div>

                            <input type="hidden" name="options[{{ $i }}][label]" value="{{ $label }}">

                            <div class="flex-1">
                                <input type="text" name="options[{{ $i }}][option_text]"
                                    value="{{ $oldOpt['option_text'] ?? (optional($opt)->option_text ?? '') }}"
                                    placeholder="Teks pilihan {{ $label }}"
                                    class="w-full px-3 py-2 text-sm border-line focus:border-accent focus:ring-accent rounded-xl shadow-sm @error('options.'.$i.'.option_text') border-red-400 @enderror" />
                                <x-input-error :messages="$errors->get('options.' . $i . '.option_text')" class="mt-1 text-xs" />
                            </div>

                            <div class="w-24 shrink-0">
                                <input type="number" name="options[{{ $i }}][points]" min="1"
                                    value="{{ $oldOpt['points'] ?? (optional($opt)->points ?? '') }}"
                                    placeholder="Poin"
                                    class="w-full px-3 py-2 text-sm border-line focus:border-accent focus:ring-accent rounded-xl shadow-sm @error('options.'.$i.'.points') border-red-400 @enderror" />
                                <x-input-error :messages="$errors->get('options.' . $i . '.points')" class="mt-1 text-xs" />
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-muted mt-2">Poin tiap pilihan digunakan untuk menghitung profil investasi (total skor
                    8–32)</p>
            </div>
        </div>

        <div class="flex items-center gap-3 mt-5">
            <button type="submit"
                class="px-5 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
                {{ isset($question) ? 'Simpan Perubahan' : 'Tambah Soal' }}
            </button>
            <a href="{{ route('admin.questions.index') }}"
                class="px-5 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
                Batal
            </a>
        </div>
    </form>
@endsection
