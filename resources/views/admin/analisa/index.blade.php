@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Monitor Analisa Reksa Dana</h1>
                <p class="page-sub">Semua submission analisa dari user</p>
            </div>
        </div>

        {{-- Filter Status --}}
        <div class="flex gap-2 text-sm flex-wrap">
            @foreach (['', 'submitted', 'reviewed', 'draft'] as $s)
                <a href="{{ route('admin.analisa.index', array_filter(['status' => $s ?: null, 'kategori' => request('kategori'), 'ffs_bulan' => request('ffs_bulan'), 'ffs_tahun' => request('ffs_tahun')])) }}"
                    class="px-3 py-1.5 rounded-lg border transition {{ request('status') === $s || (!request('status') && $s === '') ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                    {{ match ($s) {'' => 'Semua','submitted' => 'Menunggu Review','reviewed' => 'Sudah Direview','draft' => 'Draft'} }}
                </a>
            @endforeach
        </div>

        {{-- Filter Kategori + Kalender FFS --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex gap-2 text-xs flex-wrap">
                <a href="{{ route('admin.analisa.index', array_filter(['status' => request('status'), 'ffs_bulan' => request('ffs_bulan'), 'ffs_tahun' => request('ffs_tahun')])) }}"
                    class="px-3 py-1.5 rounded-lg border transition {{ !request('kategori') ? 'bg-accent text-white border-accent' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                    Semua Kategori
                </a>
                @foreach (['Konvensional', 'Syariah', 'index', 'ETF'] as $k)
                    <a href="{{ route('admin.analisa.index', array_filter(['status' => request('status'), 'kategori' => $k, 'ffs_bulan' => request('ffs_bulan'), 'ffs_tahun' => request('ffs_tahun')])) }}"
                        class="px-3 py-1.5 rounded-lg border transition {{ request('kategori') === $k ? 'bg-accent text-white border-accent' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                        {{ $k }}
                    </a>
                @endforeach
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <form method="GET" action="{{ route('admin.analisa.index') }}" class="flex items-center gap-2" id="ffs-filter-form">
                    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                    @if(request('kategori'))<input type="hidden" name="kategori" value="{{ request('kategori') }}">@endif
                    <select name="ffs_bulan" onchange="document.getElementById('ffs-filter-form').submit()"
                        class="text-xs border-gray-300 rounded-lg px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">Semua Bulan FFS</option>
                        @foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bln)
                            <option value="{{ $i + 1 }}" {{ request('ffs_bulan') == $i + 1 ? 'selected' : '' }}>{{ $bln }}</option>
                        @endforeach
                    </select>
                    <select name="ffs_tahun" onchange="document.getElementById('ffs-filter-form').submit()"
                        class="text-xs border-gray-300 rounded-lg px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">Semua Tahun FFS</option>
                        @foreach ($tahunList as $thn)
                            <option value="{{ $thn }}" {{ request('ffs_tahun') == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="table-card">
            @if ($analisas->isEmpty())
                <div class="p-12 text-center text-muted text-sm">Belum ada data analisa.</div>
            @else
                <table class="w-full text-sm">
                    @php
                        $bulanIndonesia = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                    @endphp
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-primary">User</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Reksa Dana</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Jenis</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Kategori</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Kalender FFS</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($analisas as $analisa)
                            <tr class="hover:bg-[#f8fafc] transition">
                                <td class="px-5 py-3.5">
                                    <div class="font-medium text-primary">{{ $analisa->user->name }}</div>
                                    <div class="text-xs text-muted">{{ $analisa->user->email }}</div>
                                </td>
                                <td class="px-5 py-3.5 font-medium">{{ $analisa->nama_reksa_dana }}</td>
                                <td class="px-5 py-3.5 text-muted">{{ $analisa->jenis_reksa_dana }}</td>
                                <td class="px-5 py-3.5">
                                    @if ($analisa->kategori)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ((array) $analisa->kategori as $kat)
                                                <span
                                                    class="px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded text-xs">{{ $kat }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-muted">
                                    @if ($analisa->ffs_bulan && $analisa->ffs_tahun)
                                        {{ $bulanIndonesia[$analisa->ffs_bulan - 1] }} {{ $analisa->ffs_tahun }}
                                    @else
                                        <span class="text-muted text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    @php
                                        $badge = match ($analisa->status) {
                                            'draft' => 'bg-gray-100 text-gray-600',
                                            'submitted' => 'bg-yellow-100 text-yellow-700',
                                            'reviewed' => 'bg-green-100 text-green-700',
                                            default => 'bg-slate-100 text-slate-600',
                                        };
                                        $label = match ($analisa->status) {
                                            'draft' => 'Draft',
                                            'submitted' => 'Menunggu Review',
                                            'reviewed' => 'Sudah Direview',
                                            default => ucfirst($analisa->status ?? 'Unknown'),
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.analisa.show', $analisa) }}"
                                        class="px-3 py-1.5 text-xs font-medium text-primary border border-line rounded-lg hover:bg-[#f1f5f9] transition">
                                        Detail
                                    </a>
                                    <form method="POST" action="{{ route('admin.analisa.destroy', $analisa) }}"
                                        class="inline" onsubmit="return confirm('Hapus data analisa ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition ml-1">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-5 py-3 border-t border-line">
                    {{ $analisas->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
