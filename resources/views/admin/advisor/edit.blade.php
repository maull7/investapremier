@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.advisors.index') }}" class="text-sm text-muted hover:text-primary">&larr; Kembali</a>
        <h1 class="page-title mt-2">Edit Advisor: {{ $advisor->name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.advisors.update', $advisor) }}" class="bg-white rounded-xl border border-line p-6 space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-primary mb-1">Nama Lengkap *</label>
            <input type="text" name="name" value="{{ old('name', $advisor->name) }}" required
                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-primary mb-1">Email *</label>
            <input type="email" name="email" value="{{ old('email', $advisor->email) }}" required
                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-primary mb-1">Telepon</label>
            <input type="text" name="phone" value="{{ old('phone', $advisor->phone) }}"
                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
        </div>

        <div>
            <label class="block text-sm font-semibold text-primary mb-1">Password Baru <span class="text-muted font-normal">(kosongkan jika tidak diubah)</span></label>
            <input type="password" name="password"
                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-primary mb-1">Konfirmasi Password</label>
            <input type="password" name="password_confirmation"
                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ $advisor->is_active ? 'checked' : '' }} id="is_active"
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
