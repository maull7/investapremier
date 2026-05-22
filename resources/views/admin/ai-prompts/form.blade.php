@extends('layouts.admin')

@section('title', isset($prompt) ? 'Edit Prompt' : 'Tambah Prompt')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-2 text-sm text-muted mb-3">
        <a href="{{ route('admin.ai-prompts.index') }}" class="hover:text-primary transition">AI Prompts</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        @if(isset($prompt))
            <a href="{{ route('admin.ai-prompts.group', $prompt->group ?: 'general') }}" class="hover:text-primary transition">{{ ucfirst($prompt->group ?: 'General') }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-primary font-medium">Edit</span>
        @else
            <span class="text-primary font-medium">Tambah</span>
        @endif
    </div>

    <div>
        <h1 class="text-xl font-bold text-primary">{{ isset($prompt) ? 'Edit Prompt' : 'Tambah Prompt Baru' }}</h1>
        <p class="text-sm text-muted mt-0.5">Atur instruksi yang dikirim ke AI.</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
        action="{{ isset($prompt) ? route('admin.ai-prompts.update', $prompt->key) : route('admin.ai-prompts.store') }}"
        class="bg-white rounded-xl border border-line p-6 space-y-4">
        @csrf
        @if(isset($prompt)) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label value="Key" class="text-sm font-semibold mb-1.5"/>
                @if(isset($prompt))
                    <input type="text" value="{{ $prompt->key }}" readonly
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-muted font-mono">
                @else
                    <input type="text" name="key" value="{{ old('key') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:border-primary focus:ring focus:ring-primary/20"
                        placeholder="system_analisa, instruksi_analisa, ...">
                    <p class="text-xs text-muted mt-1">Identifier unik, gunakan snake_case.</p>
                @endif
            </div>
            <div>
                <x-input-label value="Grup" class="text-sm font-semibold mb-1.5"/>
                <input type="text" name="group" list="group-list"
                    value="{{ old('group', $prompt->group ?? request('group', '')) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring focus:ring-primary/20"
                    placeholder="reksa-dana, perencanaan-investasi, ...">
                <datalist id="group-list">
                    @foreach($groups as $g)
                        <option value="{{ $g }}">
                    @endforeach
                </datalist>
                <p class="text-xs text-muted mt-1">Kelompok prompt. Akan muncul sebagai sub-menu di sidebar.</p>
            </div>
        </div>

        <div>
            <x-input-label value="Label" class="text-sm font-semibold mb-1.5"/>
            <input type="text" name="label" value="{{ old('label', $prompt->label ?? '') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring focus:ring-primary/20"
                placeholder="System Prompt — Analisa AI">
        </div>

        <div>
            <x-input-label value="Deskripsi" class="text-sm font-semibold mb-1.5"/>
            <textarea name="description" rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring focus:ring-primary/20 resize-none"
                placeholder="Peran dan instruksi dasar AI...">{{ old('description', $prompt->description ?? '') }}</textarea>
        </div>

        <div>
            <x-input-label value="Prompt Value" class="text-sm font-semibold mb-1.5"/>
            <textarea name="value" rows="12"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:border-primary focus:ring focus:ring-primary/20 resize-y">{{ old('value', $prompt->value ?? '') }}</textarea>
        </div>

        <div>
            <x-input-label value="Sort Order" class="text-sm font-semibold mb-1.5"/>
            <input type="number" name="sort_order" value="{{ old('sort_order', $prompt->sort_order ?? 0) }}"
                class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring focus:ring-primary/20">
            <p class="text-xs text-muted mt-1">Urutan tampil dalam grup (semakin kecil semakin atas).</p>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
                {{ isset($prompt) ? 'Simpan Perubahan' : 'Tambah Prompt' }}
            </button>
            <a href="{{ route('admin.ai-prompts.index') }}"
                class="px-5 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Batal</a>
        </div>
    </form>
</div>
@endsection
