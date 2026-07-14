@extends('layouts.user')

@section('title', 'Detail Klien')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('user.clients.index') }}" class="text-sm text-muted hover:text-primary">&larr; Daftar Klien</a>
        <h1 class="page-title mt-2">{{ $client->name }}</h1>
        <p class="page-sub">{{ $client->email }}</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Info Klien --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        @php $profile = $client->memberProfile; @endphp
        <div class="bg-white rounded-xl border border-line p-5">
            <p class="text-xs text-muted">Usia</p>
            <p class="text-lg font-bold text-primary mt-1">{{ $profile?->tanggal_lahir?->age ? $profile->tanggal_lahir->age . ' thn' : '—' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-line p-5">
            <p class="text-xs text-muted">Pekerjaan</p>
            <p class="text-lg font-bold text-primary mt-1">{{ $profile?->pekerjaan ?? '—' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-line p-5">
            <p class="text-xs text-muted">Profil Risiko</p>
            <p class="text-lg font-bold text-primary mt-1">{{ $profile?->profil_risiko ?? '—' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-line p-5">
            <p class="text-xs text-muted">Telepon</p>
            <p class="text-lg font-bold text-primary mt-1">{{ $profile?->no_telepon ?? '—' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-line p-5">
            <p class="text-xs text-muted">Total Portfolio</p>
            <p class="text-lg font-bold text-accent mt-1">{{ $portfolioSummary['totalKekayaanFormatted'] ?? 'Rp 0' }}</p>
        </div>
    </div>

    {{-- Portfolio Summary --}}
    @if(count($portfolioSummary['alokasiAset'] ?? []) > 0 || count($portfolioSummary['goals'] ?? []) > 0)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @if(count($portfolioSummary['alokasiAset'] ?? []) > 0)
        <div class="bg-white rounded-xl border border-line p-5">
            <h3 class="font-bold text-primary text-sm mb-4">Alokasi Aset</h3>
            <div class="space-y-3">
                @foreach($portfolioSummary['alokasiAset'] as $item)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-700">{{ $item['label'] }}</span>
                        <span class="font-bold text-gray-900">{{ $item['pct'] }}%</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r {{ $item['warna'] }}" style="width:{{ $item['pct'] }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @if(count($portfolioSummary['goals'] ?? []) > 0)
        <div class="bg-white rounded-xl border border-line p-5">
            <h3 class="font-bold text-primary text-sm mb-4">Progress Goal</h3>
            <div class="space-y-4">
                @foreach($portfolioSummary['goals'] as $goal)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-700 font-medium">{{ $goal['nama'] }}</span>
                        <span class="font-bold text-green-600">{{ $goal['pct'] }}%</span>
                    </div>
                    <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-green-600 to-green-400" style="width:{{ $goal['pct'] }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                        <span>Target: {{ $goal['targetFormatted'] }}</span>
                        <span>Terkumpul: {{ $goal['terkumpulFormatted'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Perencanaan Investasi --}}
    <div class="bg-white rounded-xl border border-line overflow-hidden">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary/80">
            <h2 class="font-bold text-white text-sm">Perencanaan Investasi</h2>
        </div>

        @if ($perencanaan->isEmpty())
            <div class="p-12 text-center text-muted text-sm">Klien belum memiliki perencanaan investasi.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Kategori</th>
                            <th class="text-right px-5 py-3 font-semibold text-primary">Dana Tersedia</th>
                            <th class="text-right px-5 py-3 font-semibold text-primary">Target Dana</th>
                            <th class="text-center px-5 py-3 font-semibold text-primary">Target Waktu</th>
                            <th class="text-center px-5 py-3 font-semibold text-primary">Profil Risiko</th>
                            <th class="text-center px-5 py-3 font-semibold text-primary">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($perencanaan as $p)
                            <tr class="hover:bg-[#f8fafc] transition">
                                <td class="px-5 py-3.5 font-medium">{{ $p->kategori_perencanaan }}</td>
                                <td class="px-5 py-3.5 text-right font-semibold">Rp {{ number_format($p->dana_tersedia ?? 0, 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-right font-semibold text-accent">Rp {{ number_format($p->kebutuhan_dana ?? 0, 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-center">{{ $p->target_waktu_tahun ? $p->target_waktu_tahun . ' thn' : '—' }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ match($p->profil_risiko) {
                                            'Konservatif' => 'bg-blue-100 text-blue-700',
                                            'Moderat' => 'bg-amber-100 text-amber-700',
                                            'Agresif' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-600',
                                        } }}">
                                        {{ $p->profil_risiko ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $p->status === 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $p->status ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('user.perencanaan-investasi.show', $p) }}"
                                        class="px-3 py-1.5 text-xs font-medium text-accent border border-accent/30 rounded-lg hover:bg-accent/5 transition">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-line">{{ $perencanaan->links() }}</div>
        @endif
    </div>
</div>
@endsection
