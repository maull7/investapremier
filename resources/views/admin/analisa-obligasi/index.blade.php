@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">Monitor Analisa Obligasi</h1>
            <p class="page-sub">Semua submission analisa lapkeu obligasi dari user</p>
        </div>
    </div>

    {{-- Filter Status --}}
    <div class="flex gap-2 text-sm flex-wrap">
        @foreach(['', 'submitted', 'reviewed', 'draft'] as $s)
        <a href="{{ route('admin.analisa-obligasi.index', array_filter(['status' => $s ?: null])) }}"
           class="px-3 py-1.5 rounded-lg border transition {{ request('status') === $s || (!request('status') && $s === '') ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
            {{ match($s) { '' => 'Semua', 'submitted' => 'Menunggu Review', 'reviewed' => 'Sudah Direview', 'draft' => 'Draft' } }}
        </a>
        @endforeach
    </div>

    <div class="table-card">
        @if($items->isEmpty())
            <div class="p-12 text-center text-muted text-sm">Belum ada data analisa obligasi.</div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-[#f8fafc] border-b border-line">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-primary">User</th>
                    <th class="text-left px-5 py-3 font-semibold text-primary">Nama Obligasi</th>
                    <th class="text-left px-5 py-3 font-semibold text-primary">Kode</th>
                    <th class="text-left px-5 py-3 font-semibold text-primary">Emiten</th>
                    <th class="text-left px-5 py-3 font-semibold text-primary">Rating</th>
                    <th class="text-left px-5 py-3 font-semibold text-primary">Periode</th>
                    <th class="text-left px-5 py-3 font-semibold text-primary">Status</th>
                    <th class="text-left px-5 py-3 font-semibold text-primary">Tanggal</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach($items as $analisa)
                <tr class="hover:bg-[#f8fafc] transition">
                    <td class="px-5 py-3.5">
                        <div class="font-medium text-primary">{{ $analisa->user->name }}</div>
                        <div class="text-xs text-muted">{{ $analisa->user->email }}</div>
                    </td>
                    <td class="px-5 py-3.5 font-medium">{{ $analisa->nama_obligasi }}</td>
                    <td class="px-5 py-3.5 text-muted">{{ $analisa->kode_obligasi ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-muted">{{ $analisa->nama_emiten ?? '—' }}</td>
                    <td class="px-5 py-3.5">
                        @if($analisa->rating)
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-xs font-semibold">{{ $analisa->rating }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-muted">{{ $analisa->periode ?? '—' }}</td>
                    <td class="px-5 py-3.5">
                        @php
                            $badge = match($analisa->status) {
                                'draft'     => 'bg-gray-100 text-gray-600',
                                'submitted' => 'bg-yellow-100 text-yellow-700',
                                'reviewed'  => 'bg-green-100 text-green-700',
                                default     => 'bg-gray-100 text-gray-600',
                            };
                            $label = match($analisa->status) {
                                'draft'     => 'Draft',
                                'submitted' => 'Menunggu Review',
                                'reviewed'  => 'Sudah Direview',
                                default     => $analisa->status,
                            };
                        @endphp
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-muted">{{ $analisa->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.analisa-obligasi.show', $analisa) }}"
                           class="px-3 py-1.5 text-xs font-medium text-primary border border-line rounded-lg hover:bg-[#f1f5f9] transition">
                            Detail
                        </a>
                        <form method="POST" action="{{ route('admin.analisa-obligasi.destroy', $analisa) }}"
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
            {{ $items->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
