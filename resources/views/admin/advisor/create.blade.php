@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.advisors.index') }}" class="text-sm text-muted hover:text-primary">&larr; Kembali</a>
        <h1 class="page-title mt-2">Tambah Advisor</h1>
    </div>

    <form method="POST" action="{{ route('admin.advisors.store') }}" class="bg-white rounded-xl border border-line p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-primary mb-1">Nama Lengkap *</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-primary mb-1">Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-primary mb-1">Telepon</label>
            <input type="text" name="phone" value="{{ old('phone') }}"
                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
        </div>

        <div>
            <label class="block text-sm font-semibold text-primary mb-1">Password *</label>
            <input type="password" name="password" required
                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-primary mb-1">Konfirmasi Password *</label>
            <input type="password" name="password_confirmation" required
                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" checked id="is_active"
                class="rounded border-line text-primary focus:ring-primary">
            <label for="is_active" class="text-sm text-primary font-medium">Aktif</label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 transition">Simpan</button>
            <a href="{{ route('admin.advisors.index') }}" class="px-5 py-2.5 border border-line text-muted rounded-lg text-sm font-semibold hover:text-primary transition">Batal</a>
        </div>
    </form>
</div>
@endsection
