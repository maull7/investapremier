@extends('layouts.admin')
@section('title', 'Edit Sub Admin')
@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.sub-admins.index') }}" class="text-gray-400 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Edit Sub Admin</h1>
    </div>

    <form method="POST" action="{{ route('admin.sub-admins.update', $subAdmin) }}" class="space-y-6">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
            <h2 class="font-semibold text-gray-800 text-sm">Informasi Akun</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name', $subAdmin->name) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-400 @error('name') border-red-400 @enderror"/>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $subAdmin->email) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-400 @error('email') border-red-400 @enderror"/>
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru <span class="text-gray-400 font-normal">(kosongkan jika tidak diganti)</span></label>
                    <input type="password" name="password" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-400 @error('password') border-red-400 @enderror"/>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-400"/>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                    {{ old('is_active', $subAdmin->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 accent-green-600"/>
                <label for="is_active" class="text-sm text-gray-700">Akun aktif</label>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 text-sm mb-4">Hak Akses Menu</h2>
            @include('admin.sub-admins._permission_tree', ['selected' => old('permissions', $subAdmin->permissions ?? [])])
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white" style="background:linear-gradient(135deg,#16a34a,#22c55e)">Simpan Perubahan</button>
            <a href="{{ route('admin.sub-admins.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
        </div>
    </form>
</div>
@endsection
