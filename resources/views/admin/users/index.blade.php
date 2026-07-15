@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">Pengguna & Member</h1>
            <p class="page-sub">Daftar seluruh pengguna dan member yang terdaftar</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/email..."
                    class="w-56 px-3 py-2 text-sm border border-line rounded-lg focus:outline-none focus:border-accent">
                <select name="role" class="px-3 py-2 text-sm border border-line rounded-lg focus:outline-none focus:border-accent">
                    <option value="">Semua Role</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="advisor" {{ request('role') === 'advisor' ? 'selected' : '' }}>Advisor</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="sub_admin" {{ request('role') === 'sub_admin' ? 'selected' : '' }}>Sub Admin</option>
                </select>
                <button type="submit" class="btn-primary btn-sm">Filter</button>
                @if(request()->anyFilled(['search', 'role']))
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary btn-sm">Reset</a>
                @endif
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="table-card">
        @if ($users->isEmpty())
            <div class="p-12 text-center text-muted text-sm">Belum ada pengguna.</div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-[#f8fafc] border-b border-line">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Nama</th>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Email</th>
                        <th class="text-center px-5 py-3 font-semibold text-primary">Role</th>
                        <th class="text-center px-5 py-3 font-semibold text-primary">Member</th>
                        <th class="text-center px-5 py-3 font-semibold text-primary">Status</th>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Tanggal Daftar</th>
                        <th class="text-center px-5 py-3 font-semibold text-primary">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($users as $u)
                        <tr class="hover:bg-[#f8fafc] transition">
                            <td class="px-5 py-3.5 font-medium text-primary">{{ $u->name }}</td>
                            <td class="px-5 py-3.5 text-muted">{{ $u->email }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $u->role === 'admin' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $u->role === 'sub_admin' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $u->role === 'advisor' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $u->role === 'user' ? 'bg-gray-100 text-gray-700' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $u->role)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($u->memberProfile)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $u->memberProfile->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ ucfirst($u->memberProfile->status) }}
                                    </span>
                                @else
                                    <span class="text-muted text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $u->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-muted text-xs">{{ $u->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if ($u->memberProfile)
                                        <a href="{{ route('admin.members.show', $u->memberProfile) }}" class="px-3 py-1.5 text-xs font-medium text-accent border border-accent/30 rounded-lg hover:bg-accent/5 transition">Member</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3 border-t border-line">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection
