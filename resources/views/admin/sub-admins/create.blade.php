@extends('layouts.admin')
@section('title', 'Tambah Sub Admin')
@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.sub-admins.index') }}" class="text-gray-400 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Tambah Sub Admin</h1>
    </div>

    <form method="POST" action="{{ route('admin.sub-admins.store') }}" class="space-y-6">
        @csrf
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
            <h2 class="font-semibold text-gray-800 text-sm">Informasi Akun</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-400 @error('name') border-red-400 @enderror"/>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-400 @error('email') border-red-400 @enderror"/>
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-400 @error('password') border-red-400 @enderror"/>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-400"/>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" id="is_active" checked class="w-4 h-4 accent-green-600"/>
                <label for="is_active" class="text-sm text-gray-700">Akun aktif</label>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 text-sm mb-4">Hak Akses Menu</h2>
            @include('admin.sub-admins._permission_tree', ['selected' => old('permissions', [])])
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:linear-gradient(135deg,#16a34a,#22c55e)">Simpan</button>
            <a href="{{ route('admin.sub-admins.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
        </div>
    </form>
</div>
@endsection
