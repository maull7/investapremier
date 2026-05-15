@extends('layouts.admin')

@section('title', 'Detail Pendaftaran Member')

@section('content')
@php $statusCls = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700'][$member->status]; @endphp

<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-2 text-sm text-muted">
        <a href="{{ route('admin.members.index') }}" class="hover:text-primary transition">Pendaftaran Member</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary font-medium">{{ $member->user->name }}</span>
    </div>
    @if(session('success'))
    <span class="text-sm text-green-600 font-medium">{{ session('success') }}</span>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Sidebar info --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-accent/20 text-accent grid place-items-center text-sm font-bold uppercase">{{ substr($member->user->name, 0, 2) }}</div>
                <div>
                    <div class="font-bold text-primary">{{ $member->user->name }}</div>
                    <div class="text-xs text-muted">{{ $member->user->email }}</div>
                </div>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs text-muted">Status</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusCls }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ ['pending'=>'Pending','approved'=>'Disetujui','rejected'=>'Ditolak'][$member->status] }}
                </span>
            </div>
            <div class="space-y-2 text-sm border-t border-line pt-4">
                <div class="flex justify-between"><span class="text-muted">Agama</span><span class="font-medium text-primary">{{ $member->agama ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-muted">Pekerjaan</span><span class="font-medium text-primary">{{ $member->pekerjaan ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-muted">Penghasilan/Tahun</span><span class="font-medium text-primary text-xs text-right max-w-[140px]">{{ $member->rata_rata_penghasilan ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-muted">Rekening Efek</span><span class="font-medium text-primary">{{ $member->pembukaan_rekening_efek ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-muted">Daftar</span><span class="font-medium text-primary">{{ $member->created_at->format('d M Y') }}</span></div>
            </div>
        </div>

        {{-- Aksi --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm p-5 space-y-2">
            <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-3">Tindakan</p>
            @if($member->status !== 'approved')
            <form method="POST" action="{{ route('admin.members.approve', $member) }}">
                @csrf
                <button type="submit" class="w-full px-4 py-2.5 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Setujui Pendaftaran
                </button>
            </form>
            @endif
            @if($member->status !== 'rejected')
            <form method="POST" action="{{ route('admin.members.reject', $member) }}">
                @csrf
                <button type="submit" class="w-full px-4 py-2.5 bg-red-500 text-white rounded-xl text-sm font-semibold hover:bg-red-600 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Tolak Pendaftaran
                </button>
            </form>
            @endif
            <a href="{{ route('admin.members.index') }}" class="w-full px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition flex items-center justify-center gap-2">
                ← Kembali
            </a>
        </div>
    </div>

    {{-- Detail data --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-2xl border border-line shadow-sm p-6">
            <h3 class="font-bold text-primary mb-4">Data Investasi</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-2">Jenis Investasi</p>
                    <div class="flex flex-wrap gap-2">
                        @forelse($member->jenis_investasi ?? [] as $item)
                        <span class="px-3 py-1 bg-accent/10 text-accent rounded-full text-xs font-semibold">{{ $item }}</span>
                        @empty <span class="text-muted text-sm">—</span> @endforelse
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-2">Sumber Dana</p>
                    <div class="flex flex-wrap gap-2">
                        @forelse($member->sumber_dana ?? [] as $item)
                        <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-semibold">{{ $item }}</span>
                        @empty <span class="text-muted text-sm">—</span> @endforelse
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-2">Tujuan Investasi</p>
                    <div class="flex flex-wrap gap-2">
                        @forelse($member->tujuan_investasi ?? [] as $item)
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">{{ $item }}</span>
                        @empty <span class="text-muted text-sm">—</span> @endforelse
                    </div>
                </div>
                @if($member->maksud_tujuan_lain)
                <div>
                    <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-1">Maksud/Tujuan Lain</p>
                    <p class="text-sm text-primary">{{ $member->maksud_tujuan_lain }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Portofolio --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line">
                <h3 class="font-bold text-primary">Daftar Portofolio</h3>
            </div>
            @if($member->portfolios->isEmpty())
            <div class="px-6 py-8 text-center text-muted text-sm">Tidak ada data portofolio</div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                            <th class="px-6 py-3 font-semibold">Jenis</th>
                            <th class="px-6 py-3 font-semibold">Nama Efek</th>
                            <th class="px-6 py-3 font-semibold">Mulai Kepemilikan</th>
                            <th class="px-6 py-3 font-semibold text-right">Jumlah</th>
                            <th class="px-6 py-3 font-semibold text-right">Harga Saat Ini (T-1)</th>
                            <th class="px-6 py-3 font-semibold text-right">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($member->portfolios as $p)
                        @php
                            $sp = $stockPrices[strtoupper($p->nama_efek)] ?? null;
                            $harga = $sp?->harga;
                            $totalNilai = ($harga && $p->jumlah) ? $harga * $p->jumlah : null;
                        @endphp
                        <tr class="hover:bg-[#f8fafc] transition-colors">
                            <td class="px-6 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $p->jenis === 'Saham' ? 'bg-blue-100 text-blue-700' : ($p->jenis === 'Obligasi' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">
                                    {{ $p->jenis }}
                                </span>
                            </td>
                            <td class="px-6 py-3 font-medium text-primary">{{ $p->nama_efek }}</td>
                            <td class="px-6 py-3 text-muted">{{ $p->mulai_kepemilikan?->format('d M Y') ?? '—' }}</td>
                            <td class="px-6 py-3 text-muted text-right">{{ $p->jumlah ? number_format($p->jumlah, 0, ',', '.') : '—' }}</td>
                            <td class="px-6 py-3 text-right">
                                @if($harga)
                                    <span class="font-medium text-primary">Rp {{ number_format($harga, 0, ',', '.') }}</span>
                                    @if($sp->tanggal)
                                    <div class="text-xs text-muted">{{ $sp->tanggal->format('d M Y') }}</div>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                @if($totalNilai)
                                    <span class="font-semibold text-primary">Rp {{ number_format($totalNilai, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($member->portfolios->isNotEmpty())
                    @php
                        $grandTotal = $member->portfolios->sum(function($p) use ($stockPrices) {
                            $sp = $stockPrices[strtoupper($p->nama_efek)] ?? null;
                            return ($sp?->harga && $p->jumlah) ? $sp->harga * $p->jumlah : 0;
                        });
                    @endphp
                    @if($grandTotal > 0)
                    <tfoot>
                        <tr class="bg-[#f8fafc] font-semibold text-sm">
                            <td colspan="5" class="px-6 py-3 text-right text-muted">Total Nilai Portfolio</td>
                            <td class="px-6 py-3 text-right text-primary">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                    @endif
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
