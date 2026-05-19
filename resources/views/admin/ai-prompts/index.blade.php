@extends('layouts.admin')

@section('title', 'Edit Prompt AI')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-primary">Edit Prompt AI</h1>
        <p class="text-sm text-muted mt-0.5">Kelola instruksi yang dikirim ke AI saat membuat analisa reksa dana.</p>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="space-y-6">
        @foreach($prompts as $prompt)
        <div class="bg-white rounded-xl border border-line p-6">
            <div class="mb-3">
                <h3 class="font-semibold text-primary">{{ $prompt->label }}</h3>
                @if($prompt->description)
                <p class="text-xs text-muted mt-0.5">{{ $prompt->description }}</p>
                @endif
            </div>
            <form method="POST" action="{{ route('admin.ai-prompts.update', $prompt->key) }}">
                @csrf
                @method('PUT')
                <textarea name="value" rows="8"
                    class="w-full border border-gray-300 rounded-lg text-sm px-3 py-2.5 font-mono focus:border-primary focus:ring focus:ring-primary/20 resize-y">{{ old('value', $prompt->value) }}</textarea>
                @error('value')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                <div class="mt-3 flex items-center justify-between">
                    <span class="text-xs text-muted font-mono bg-[#f1f5f9] px-2 py-1 rounded">key: {{ $prompt->key }}</span>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endsection
