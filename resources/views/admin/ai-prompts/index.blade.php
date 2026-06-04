@extends('layouts.admin')

@section('title', 'Edit Prompt AI')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">
                @if($group)
                    Prompt AI — {{ ucfirst($group) }}
                @else
                    Edit Prompt AI
                @endif
            </h1>
            <p class="page-sub">Kelola instruksi yang dikirim ke AI.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.ai-prompts.create') }}?group={{ $group }}"
                class="px-4 py-2 bg-accent text-white rounded-lg text-sm font-semibold hover:bg-accent/90 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Prompt
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Tab Group --}}
    <div class="flex gap-1 border-b border-line overflow-x-auto">
        @foreach($groups as $g)
            <a href="{{ route('admin.ai-prompts.group', $g) }}"
                class="whitespace-nowrap px-4 py-2.5 text-sm font-semibold border-b-2 transition -mb-px {{ $group === $g ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-primary' }}">
                {{ ucfirst($g) }}
            </a>
        @endforeach
    </div>

    <div class="space-y-6">
        @forelse($prompts as $prompt)
        <div class="bg-white rounded-xl border border-line p-6" x-data="{ editing: false }">
            <div class="mb-3 flex items-start justify-between">
                <div>
                    <h3 class="font-semibold text-primary">{{ $prompt->label }}</h3>
                    @if($prompt->description)
                    <p class="text-xs text-muted mt-0.5">{{ $prompt->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0 ml-4">
                    <a href="{{ route('admin.ai-prompts.edit', $prompt->key) }}"
                        class="text-xs text-muted hover:text-primary flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Detail
                    </a>
                    <form method="POST" action="{{ route('admin.ai-prompts.destroy', $prompt->key) }}"
                        onsubmit="return confirm('Hapus prompt \"{{ $prompt->label }}\"?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-muted hover:text-red-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.ai-prompts.update-value', $prompt->key) }}">
                @csrf
                @method('PUT')
                <textarea name="value" rows="6"
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
        @empty
        <div class="py-16 text-center text-muted bg-white rounded-2xl border border-line">
            <p class="font-medium">Belum ada prompt di grup ini</p>
            <p class="text-sm mt-1">
                <a href="{{ route('admin.ai-prompts.create') }}?group={{ $group }}" class="text-accent hover:underline">Tambah prompt baru</a>
            </p>
        </div>
        @endforelse
    </div>
</div>
@endsection
