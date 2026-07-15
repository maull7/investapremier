@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">Detail Pengguna</h1>
            <p class="page-sub">{{ $user->name }}</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn-secondary btn-sm">Kembali</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="table-card">
            <div class="table-head"><h2 class="th-title">Informasi Akun</h2></div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-line">
                    <tr><td class="px-5 py-3 font-semibold text-primary w-1/3">Nama</td><td class="px-5 py-3 text-muted">{{ $user->name }}</td></tr>
                    <tr><td class="px-5 py-3 font-semibold text-primary">Email</td><td class="px-5 py-3 text-muted">{{ $user->email }}</td></tr>
                    <tr><td class="px-5 py-3 font-semibold text-primary">Role</td><td class="px-5 py-3">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</td></tr>
                    <tr><td class="px-5 py-3 font-semibold text-primary">Status</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                    <tr><td class="px-5 py-3 font-semibold text-primary">Telepon</td><td class="px-5 py-3 text-muted">{{ $user->phone ?? '—' }}</td></tr>
                    <tr><td class="px-5 py-3 font-semibold text-primary">Tanggal Daftar</td><td class="px-5 py-3 text-muted">{{ $user->created_at->format('d M Y H:i') }}</td></tr>
                </tbody>
            </table>
        </div>

        @if ($user->memberProfile)
        <div class="table-card">
            <div class="table-head"><h2 class="th-title">Profil Member</h2></div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-line">
                    <tr><td class="px-5 py-3 font-semibold text-primary w-1/3">Status</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->memberProfile->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($user->memberProfile->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr><td class="px-5 py-3 font-semibold text-primary">Portfolio</td><td class="px-5 py-3 text-muted">{{ $user->memberPortfolios->count() }} item</td></tr>
                </tbody>
            </table>
        </div>
        @endif

        @if ($user->clients->count() > 0)
        <div class="table-card">
            <div class="table-head"><h2 class="th-title">Klien (Advisor)</h2></div>
            <table class="w-full text-sm">
                <thead class="bg-[#f8fafc] border-b border-line">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Nama</th>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Email</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($user->clients as $client)
                    <tr class="hover:bg-[#f8fafc] transition">
                        <td class="px-5 py-3 font-medium text-primary">{{ $client->name }}</td>
                        <td class="px-5 py-3 text-muted">{{ $client->email }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
