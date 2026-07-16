@extends('layouts.user')

@section('title', 'Daftar Klien')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Daftar Klien</h1>
                <p class="page-sub">Kelola klien yang terhubung dengan Anda</p>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}</div>
        @endif

        {{-- Tab Navigation --}}
        <div class="flex gap-1 border-b border-line overflow-x-auto">
            @php
                $tabs = ['terdaftar' => 'Terdaftar', 'tertunda' => 'Permintaan Masuk', 'ditolak' => 'Ditolak'];
            @endphp
            @foreach ($tabs as $key => $label)
                <a href="{{ route('user.clients.index', ['tab' => $key]) }}"
                    class="px-4 py-2.5 text-sm whitespace-nowrap transition {{ $tab === $key ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($tab === 'terdaftar')
            <div class="table-card">
                @if ($clients->isEmpty())
                    <div class="p-12 text-center text-muted text-sm">Belum ada klien terdaftar.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-[#f8fafc] border-b border-line">
                                <tr>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Nama Klien</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Usia</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Pekerjaan</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Profil Risiko</th>
                                    <th class="text-right px-5 py-3 font-semibold text-primary">Total Aset Saat Ini</th>
                                    <th class="text-right px-5 py-3 font-semibold text-primary">Target Aset</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($clients as $c)
                                    @php
                                        $profile = $c->memberProfile;
                                        $usia = $profile?->tanggal_lahir ? $profile->tanggal_lahir->age : null;
                                        $latestPlan = $c->perencanaanInvestasi()->latest()->first();
                                    @endphp
                                    <tr class="hover:bg-[#f8fafc] transition">
                                        <td class="px-5 py-3.5 font-medium text-primary">{{ $c->name }}</td>
                                        <td class="px-5 py-3.5 text-muted">{{ $usia ? $usia . ' thn' : '—' }}</td>
                                        <td class="px-5 py-3.5 text-muted">{{ $profile?->pekerjaan ?? '—' }}</td>
                                        <td class="px-5 py-3.5">
                                            @if ($latestPlan?->profil_risiko)
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ match ($latestPlan->profil_risiko) {'Konservatif' => 'bg-blue-100 text-blue-700','Moderat' => 'bg-amber-100 text-amber-700','Agresif' => 'bg-red-100 text-red-700',default => 'bg-gray-100 text-gray-600'} }}">
                                                    {{ $latestPlan->profil_risiko }}
                                                </span>
                                            @elseif ($profile?->profil_risiko)
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $profile->profil_risiko }}</span>
                                            @else
                                                <span class="text-muted text-xs">—</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-semibold text-primary">
                                            {{ $latestPlan?->dana_tersedia ? 'Rp ' . number_format($latestPlan->dana_tersedia, 0, ',', '.') : '—' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-semibold text-accent">
                                            {{ $latestPlan?->kebutuhan_dana ? 'Rp ' . number_format($latestPlan->kebutuhan_dana, 0, ',', '.') : '—' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <a href="{{ route('user.clients.show', $c) }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-accent border border-accent/30 rounded-lg hover:bg-accent/5 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3 border-t border-line">{{ $clients->links() }}</div>
                @endif
            </div>
        @elseif ($tab === 'tertunda')
            <div class="table-card">
                @if ($pendingRequests->isEmpty())
                    <div class="p-12 text-center text-muted text-sm">Tidak ada permintaan masuk.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-[#f8fafc] border-b border-line">
                                <tr>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Nama</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Email</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Pekerjaan</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">No Telepon</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Member</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($pendingRequests as $req)
                                    @php $p = $req->client->memberProfile; @endphp
                                    <tr class="hover:bg-[#f8fafc] transition">
                                        <td class="px-5 py-3.5 font-medium text-primary">{{ $req->client->name }}</td>
                                        <td class="px-5 py-3.5 text-muted text-xs">{{ $req->client->email }}</td>
                                        <td class="px-5 py-3.5 text-muted">{{ $p?->pekerjaan ?? '—' }}</td>
                                        <td class="px-5 py-3.5 text-muted">{{ $req->client->phone ?? '—' }}</td>
                                        <td class="px-5 py-3.5">
                                            @if ($p)
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium {{ $p->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ $p->status === 'approved' ? 'Aktif' : 'Pending' }}</span>
                                            @else
                                                <span class="text-muted text-xs">—</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <div class="flex items-center gap-1 justify-end">
                                                <form method="POST" action="{{ route('user.clients.approve', $req) }}"
                                                    class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-medium text-white bg-accent rounded-lg hover:bg-accent/90 transition">Setujui</button>
                                                </form>
                                                <form method="POST" action="{{ route('user.clients.reject', $req) }}"
                                                    class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">Tolak</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3 border-t border-line">{{ $pendingRequests->links() }}</div>
                @endif
            </div>
        @elseif ($tab === 'ditolak')
            <div class="table-card">
                @if ($rejectedRequests->isEmpty())
                    <div class="p-12 text-center text-muted text-sm">Tidak ada permintaan ditolak.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-[#f8fafc] border-b border-line">
                                <tr>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Nama</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Email</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Pekerjaan</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Profil Risiko</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Status</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($rejectedRequests as $req)
                                    @php $p = $req->client->memberProfile; @endphp
                                    <tr class="hover:bg-[#f8fafc] transition">
                                        <td class="px-5 py-3.5 font-medium text-primary">{{ $req->client->name }}</td>
                                        <td class="px-5 py-3.5 text-muted text-xs">{{ $req->client->email }}</td>
                                        <td class="px-5 py-3.5 text-muted">{{ $p?->pekerjaan ?? '—' }}</td>
                                        <td class="px-5 py-3.5">{{ $p?->profil_risiko ? ucfirst($p->profil_risiko) : '—' }}
                                        </td>
                                        <td class="px-5 py-3.5"><span
                                                class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Ditolak</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <form method="POST" action="{{ route('user.clients.destroy', $req->client) }}"
                                                class="inline" onsubmit="return confirm('Hapus dari daftar?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3 border-t border-line">{{ $rejectedRequests->links() }}</div>
                @endif
            </div>
        @endif
    </div>
@endsection
