@extends('layouts.admin')

@section('title', 'Manajemen Soal Kuis - InvestaPremier')

@section('content')
<div x-data="{ deleteId: null, deleteText: '', showImport: false }">

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-primary">Soal Kuis Profil Investasi</h1>
        <p class="text-muted text-sm mt-1">Kelola soal pilihan ganda untuk kuesioner profil investasi nasabah</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.questions.template') }}"
           class="flex items-center gap-2 px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Template Excel
        </a>
        <button @click="showImport = true"
                class="flex items-center gap-2 px-4 py-2.5 border border-accent text-accent rounded-xl text-sm font-semibold hover:bg-accent/5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import Excel
        </button>
        <a href="{{ route('admin.questions.create') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Soal
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Profil Skor Info --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    @foreach([['Conservative','8–12','bg-blue-50 border-blue-200 text-blue-700'],['Tolerant','13–20','bg-green-50 border-green-200 text-green-700'],['Moderate','21–28','bg-amber-50 border-amber-200 text-amber-700'],['Risk Taker','29–32','bg-red-50 border-red-200 text-red-700']] as [$label,$range,$cls])
    <div class="border rounded-xl px-4 py-3 {{ $cls }}">
        <div class="font-bold text-sm">{{ $label }}</div>
        <div class="text-xs opacity-70">Skor {{ $range }}</div>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
    {{-- Table Header --}}
    <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-primary to-primary-light">
        <h2 class="font-bold text-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Daftar Soal
        </h2>
        <div class="flex items-center gap-2">
            <span class="text-xs text-white/60">Tampilkan:</span>
            <form method="GET" action="{{ route('admin.questions.index') }}">
                <select name="per_page" onchange="this.form.submit()"
                        class="text-xs bg-white/10 text-white border border-white/20 rounded-lg px-2 py-1 focus:outline-none cursor-pointer">
                    @foreach([10, 25, 50] as $n)
                    <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
            <span class="text-xs text-white/60">{{ $questions->total() }} total</span>
        </div>
    </div>

    @if($questions->isEmpty())
    <div class="py-16 text-center text-muted">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="font-medium">Belum ada soal</p>
        <p class="text-sm mt-1">Klik "Tambah Soal" untuk mulai membuat kuesioner</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                    <th class="px-6 py-3.5 font-semibold w-12">#</th>
                    <th class="px-6 py-3.5 font-semibold">Pertanyaan</th>
                    <th class="px-6 py-3.5 font-semibold">Pilihan Jawaban</th>
                    <th class="px-6 py-3.5 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach($questions as $q)
                <tr class="hover:bg-[#f8fafc] transition-colors align-top">
                    <td class="px-6 py-4">
                        <div class="w-7 h-7 rounded-lg bg-primary/10 text-primary font-bold text-xs grid place-items-center">{{ $q->order }}</div>
                    </td>
                    <td class="px-6 py-4 max-w-xs">
                        <p class="font-semibold text-primary leading-snug">{{ $q->question_text }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="space-y-1.5">
                            @foreach($q->options as $opt)
                            <div class="flex items-center gap-2 text-xs text-muted">
                                <span class="w-5 h-5 rounded bg-[#f1f5f9] text-primary font-bold grid place-items-center shrink-0">{{ $opt->label }}</span>
                                <span class="truncate max-w-[200px]">{{ $opt->option_text }}</span>
                                <span class="ml-auto shrink-0 font-semibold text-accent">+{{ $opt->points }}</span>
                            </div>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.questions.edit', $q) }}"
                               class="p-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button type="button"
                                    @click="deleteId = {{ $q->id }}; deleteText = '{{ addslashes(Str::limit($q->question_text, 80)) }}'"
                                    class="p-2 rounded-lg text-muted hover:text-red-500 hover:bg-red-50 transition" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($questions->hasPages())
    <div class="px-6 py-4 border-t border-line flex items-center justify-between gap-4 text-sm">
        <p class="text-muted text-xs">
            Menampilkan {{ $questions->firstItem() }}–{{ $questions->lastItem() }} dari {{ $questions->total() }} soal
        </p>
        <div class="flex items-center gap-1">
            {{-- Prev --}}
            @if($questions->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
            @else
            <a href="{{ $questions->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
            @endif

            @php $cur=$questions->currentPage();$last=$questions->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s>1)
                <a href="{{ $questions->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                @if($s>2)<span class="px-1 text-muted text-xs">…</span>@endif
            @endif
            @foreach($questions->getUrlRange($s,$e) as $page => $url)
            <a href="{{ $url }}"
               class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition
                      {{ $page == $cur ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">
                {{ $page }}
            </a>
            @endforeach
            @if($e<$last)
                @if($e<$last-1)<span class="px-1 text-muted text-xs">…</span>@endif
                <a href="{{ $questions->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
            @endif

            {{-- Next --}}
            @if($questions->hasMorePages())
            <a href="{{ $questions->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
            @else
            <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next →</span>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>

{{-- Modal Import Excel --}}
<div x-show="showImport" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40" @click="showImport = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <h3 class="font-bold text-primary text-base mb-1">Import Soal dari Excel</h3>
        <p class="text-muted text-sm mb-4">Upload file Excel sesuai format template. Soal akan ditambahkan ke daftar yang sudah ada.</p>

        <form method="POST" action="{{ route('admin.questions.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="border-2 border-dashed border-line rounded-xl p-6 text-center mb-4 hover:border-accent/40 transition">
                <svg class="w-8 h-8 mx-auto text-muted mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <label class="cursor-pointer">
                    <span class="text-sm font-semibold text-accent">Pilih file</span>
                    <span class="text-sm text-muted"> atau drag & drop</span>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden" required>
                </label>
                <p class="text-xs text-muted mt-1">Format: .xlsx, .xls, .csv</p>
            </div>
            @error('file')<p class="text-red-500 text-xs mb-3">{{ $message }}</p>@enderror

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.questions.template') }}" class="text-xs text-accent hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download template
                </a>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showImport = false"
                            class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition">
                        Upload & Import
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div x-show="deleteId !== null" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40" @click="deleteId = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
         x-transition:enter="transition duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-red-100 grid place-items-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-primary text-base">Hapus Soal?</h3>
                <p class="text-muted text-sm mt-1">Soal berikut akan dihapus permanen beserta semua pilihan jawabannya:</p>
                <p class="mt-2 text-sm text-primary font-medium bg-[#f8fafc] rounded-lg px-3 py-2 border border-line" x-text="deleteText"></p>
                <p class="text-xs text-red-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 mt-6">
            <button type="button" @click="deleteId = null"
                    class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
                Batal
            </button>
            <form method="POST" :action="`/admin/questions/${deleteId}`">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

</div>
@endsection
