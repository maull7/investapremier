@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">Kelola Advisor</h1>
            <p class="page-sub">Daftar advisor yang terdaftar di sistem</p>
        </div>
        <a href="{{ route('admin.advisors.create') }}" class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Advisor
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="table-card">
        @if ($advisors->isEmpty())
            <div class="p-12 text-center text-muted text-sm">Belum ada advisor.</div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-[#f8fafc] border-b border-line">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Nama</th>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Email</th>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Telepon</th>
                        <th class="text-center px-5 py-3 font-semibold text-primary">Klien</th>
                        <th class="text-center px-5 py-3 font-semibold text-primary">Status</th>
                        <th class="text-center px-5 py-3 font-semibold text-primary">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($advisors as $a)
                        <tr class="hover:bg-[#f8fafc] transition">
                            <td class="px-5 py-3.5 font-medium text-primary">{{ $a->name }}</td>
                            <td class="px-5 py-3.5 text-muted">{{ $a->email }}</td>
                            <td class="px-5 py-3.5 text-muted">{{ $a->phone ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <a href="{{ route('admin.advisors.clients', $a) }}" class="text-accent font-semibold hover:underline">{{ $a->clients_count }} klien</a>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $a->is_active ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $a->is_active ? 'Aktif' : 'Menunggu' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if (!$a->is_active)
                                        <form method="POST" action="{{ route('admin.advisors.approve', $a) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">Setujui</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.advisors.edit', $a) }}" class="px-3 py-1.5 text-xs font-medium text-primary border border-line rounded-lg hover:bg-[#f1f5f9] transition">Edit</a>
                                    <a href="{{ route('admin.advisors.clients', $a) }}" class="px-3 py-1.5 text-xs font-medium text-accent border border-accent/30 rounded-lg hover:bg-accent/5 transition">Daftar Klien</a>
                                    <form method="POST" action="{{ route('admin.advisors.destroy', $a) }}" onsubmit="return confirm('Hapus advisor ini? Semua relasi klien akan dilepas.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3 border-t border-line">{{ $advisors->links() }}</div>
        @endif
    </div>
</div>
@endsection
