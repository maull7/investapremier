@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-primary">Monitor Analisa Reksa Dana</h1>
                <p class="text-sm text-muted mt-0.5">Semua submission analisa dari user</p>
            </div>
        </div>

        {{-- Filter Status --}}
        <div class="flex gap-2 text-sm flex-wrap">
            @foreach (['', 'submitted', 'reviewed', 'draft'] as $s)
                <a href="{{ route('admin.analisa.index', array_filter(['status' => $s ?: null, 'kategori' => request('kategori')])) }}"
                    class="px-3 py-1.5 rounded-lg border transition {{ request('status') === $s || (!request('status') && $s === '') ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                    {{ match ($s) {'' => 'Semua','submitted' => 'Menunggu Review','reviewed' => 'Sudah Direview','draft' => 'Draft'} }}
                </a>
            @endforeach
        </div>

        {{-- Filter Kategori --}}
        <div class="flex gap-2 text-xs flex-wrap">
            <a href="{{ route('admin.analisa.index', array_filter(['status' => request('status')])) }}"
                class="px-3 py-1.5 rounded-lg border transition {{ !request('kategori') ? 'bg-accent text-white border-accent' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                Semua Kategori
            </a>
            @foreach (['Konvensional', 'Syariah', 'index', 'ETF'] as $k)
                <a href="{{ route('admin.analisa.index', array_filter(['status' => request('status'), 'kategori' => $k])) }}"
                    class="px-3 py-1.5 rounded-lg border transition {{ request('kategori') === $k ? 'bg-accent text-white border-accent' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                    {{ $k }}
                </a>
            @endforeach
        </div>

        <div class="bg-white rounded-xl border border-line overflow-hidden">
            @if ($analisas->isEmpty())
                <div class="p-12 text-center text-muted text-sm">Belum ada data analisa.</div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-primary">User</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Reksa Dana</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Jenis</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Kategori</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Status</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Tanggal</th>
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
                                <td class="px-5 py-3.5 text-muted">{{ $analisa->created_at->format('d M Y') }}</td>
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
