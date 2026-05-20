@extends('layouts.admin')

@section('title', 'Pendaftaran Member')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-primary">Pendaftaran Member</h1>
    <p class="text-muted text-sm mt-1">Kelola permohonan pendaftaran member dari nasabah</p>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-primary to-primary-light">
        <h2 class="font-bold text-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Daftar Permohonan
        </h2>
        <span class="text-xs text-white/60">{{ $members->total() }} total</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                    <th class="px-6 py-3.5 font-semibold">Nasabah</th>
                    <th class="px-6 py-3.5 font-semibold">Pekerjaan</th>
                    <th class="px-6 py-3.5 font-semibold">Penghasilan</th>
                    <th class="px-6 py-3.5 font-semibold">Status</th>
                    <th class="px-6 py-3.5 font-semibold">Tanggal Daftar</th>
                    <th class="px-6 py-3.5 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse($members as $m)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-accent/20 text-accent grid place-items-center text-xs font-bold uppercase shrink-0">{{ substr($m->user->name, 0, 2) }}</div>
                            <div>
                                <div class="font-semibold text-primary">{{ $m->user->name }}</div>
                                <div class="text-xs text-muted">{{ $m->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-muted">{{ $m->pekerjaan ?? '—' }}</td>
                    <td class="px-6 py-4 text-muted text-xs">{{ $m->rata_rata_penghasilan ?? '—' }}</td>
                    <td class="px-6 py-4">
                        @php $statusCls = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700'][$m->status]; @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusCls }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ ['pending'=>'Pending','approved'=>'Disetujui','rejected'=>'Ditolak'][$m->status] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-muted text-xs">{{ $m->created_at->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.members.show', $m) }}"
                               class="px-3 py-1.5 border border-line text-muted rounded-lg text-xs font-semibold hover:text-primary hover:border-primary/30 transition">
                                Detail
                            </a>
                            @if($m->status !== 'approved')
                            <form method="POST" action="{{ route('admin.members.approve', $m) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-semibold hover:bg-green-700 transition">
                                    Setujui
                                </button>
                            </form>
                            @endif
                            @if($m->status !== 'rejected')
                            <form method="POST" action="{{ route('admin.members.reject', $m) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-semibold hover:bg-red-600 transition">
                                    Tolak
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-muted">
                        <p class="font-medium">Belum ada permohonan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($members->hasPages())
    <div class="px-6 py-4 border-t border-line flex items-center justify-between text-sm">
        <p class="text-muted text-xs">Menampilkan {{ $members->firstItem() }}–{{ $members->lastItem() }} dari {{ $members->total() }}</p>
        <div class="flex items-center gap-1">
            @if(!$members->onFirstPage())
            <a href="{{ $members->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
            @endif
            @php $cur=$members->currentPage();$last=$members->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s>1)
                <a href="{{ $members->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                @if($s>2)<span class="px-1 text-muted text-xs">…</span>@endif
            @endif
            @foreach($members->getUrlRange($s,$e) as $page => $url)
            <a href="{{ $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
            @endforeach
            @if($e<$last)
                @if($e<$last-1)<span class="px-1 text-muted text-xs">…</span>@endif
                <a href="{{ $members->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
            @endif
            @if($members->hasMorePages())
            <a href="{{ $members->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
