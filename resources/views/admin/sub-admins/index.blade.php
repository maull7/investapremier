@extends('layouts.admin')

@section('title', 'Sub Admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Sub Admin</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola akun sub admin dan hak aksesnya</p>
        </div>
        <a href="{{ route('admin.sub-admins.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white"
            style="background:linear-gradient(135deg,#16a34a,#22c55e)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Sub Admin
        </a>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Nama</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Email</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Permission</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($subAdmins as $sa)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $sa->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $sa->email }}</td>
                        <td class="px-4 py-3">
                            @if($sa->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ count($sa->getPermissionsList()) }} item</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.activity-logs.index', ['user_id' => $sa->id]) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">Logs</a>
                                <a href="{{ route('admin.sub-admins.show', $sa) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">Detail</a>
                                <a href="{{ route('admin.sub-admins.edit', $sa) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100">Edit</a>
                                <form method="POST" action="{{ route('admin.sub-admins.destroy', $sa) }}" onsubmit="return confirm('Hapus sub admin ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada sub admin.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($subAdmins->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $subAdmins->links() }}</div>
        @endif
    </div>
</div>
@endsection
