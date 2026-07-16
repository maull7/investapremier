@extends('layouts.user')

@section('title', 'Detail Klien')

@section('content')
    <div class="space-y-6">
        <div>
            <a href="{{ route('user.clients.index') }}" class="text-sm text-muted hover:text-primary">&larr; Daftar Klien</a>
            <h1 class="page-title mt-2">{{ $client->name }}</h1>
            <p class="page-sub">{{ $client->email }}</p>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}</div>
        @endif

        {{-- Info Klien --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @php $profile = $client->memberProfile; @endphp
            <div class="bg-white rounded-xl border border-line p-5">
                <p class="text-xs text-muted">Usia</p>
                <p class="text-lg font-bold text-primary mt-1">
                    {{ $profile?->tanggal_lahir?->age ? $profile->tanggal_lahir->age . ' thn' : '—' }}</p>
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
                <p class="text-lg font-bold text-accent mt-1">{{ $portfolioSummary['totalKekayaanFormatted'] ?? 'Rp 0' }}
                </p>
            </div>
        </div>

        {{-- Portfolio Summary --}}
        @if (count($portfolioSummary['alokasiAset'] ?? []) > 0 || count($portfolioSummary['goals'] ?? []) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                @if (count($portfolioSummary['alokasiAset'] ?? []) > 0)
                    <div class="bg-white rounded-xl border border-line p-5">
                        <h3 class="font-bold text-primary text-sm mb-4">Alokasi Aset</h3>
                        <div class="space-y-3">
                            @foreach ($portfolioSummary['alokasiAset'] as $item)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-700">{{ $item['label'] }}</span>
                                        <span class="font-bold text-gray-900">{{ $item['pct'] }}%</span>
                                    </div>
                                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r {{ $item['warna'] }}"
                                            style="width:{{ $item['pct'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (count($portfolioSummary['goals'] ?? []) > 0)
                    <div class="bg-white rounded-xl border border-line p-5">
                        <h3 class="font-bold text-primary text-sm mb-4">Progress Goal</h3>
                        <div class="space-y-4">
                            @foreach ($portfolioSummary['goals'] as $goal)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-700 font-medium">{{ $goal['nama'] }}</span>
                                        <span class="font-bold text-green-600">{{ $goal['pct'] }}%</span>
                                    </div>
                                    <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r from-green-600 to-green-400"
                                            style="width:{{ $goal['pct'] }}%"></div>
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
                                    <td class="px-5 py-3.5 text-right font-semibold">Rp
                                        {{ number_format($p->dana_tersedia ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3.5 text-right font-semibold text-accent">Rp
                                        {{ number_format($p->kebutuhan_dana ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3.5 text-center">
                                        {{ $p->target_waktu_tahun ? $p->target_waktu_tahun . ' thn' : '—' }}</td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ match ($p->profil_risiko) {
                                            'Konservatif' => 'bg-blue-100 text-blue-700',
                                            'Moderat' => 'bg-amber-100 text-amber-700',
                                            'Agresif' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-600',
                                        } }}">
                                            {{ $p->profil_risiko ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $p->status === 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
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
        <div class="bg-white rounded-xl border border-line overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary/80">
                <h2 class="font-bold text-white text-sm">Portofolio Klien</h2>
            </div>

            @if ($portfolioItems->isEmpty())
                <div class="py-12 text-center text-muted">
                    <p class="font-medium">Belum ada portofolio</p>
                    <p class="text-sm mt-1">Klien belum memiliki portofolio investasi.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                                <th class="px-4 py-3.5 font-semibold">Jenis Efek</th>
                                <th class="px-4 py-3.5 font-semibold">Nama Efek</th>
                                <th class="px-4 py-3.5 font-semibold">Penerbit</th>
                                <th class="px-4 py-3.5 font-semibold text-right">Jumlah UP/Lembar</th>
                                <th class="px-4 py-3.5 font-semibold text-right">Nilai Pasar</th>
                                <th class="px-4 py-3.5 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($portfolioItems as $item)
                                <tr class="hover:bg-[#f8fafc] transition-colors">
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-semibold
                                            {{ match ($item->jenis) {
                                                'Saham' => 'bg-blue-100 text-blue-700',
                                                'Obligasi' => 'bg-amber-100 text-amber-700',
                                                'Reksa Dana' => 'bg-purple-100 text-purple-700',
                                                default => 'bg-green-100 text-green-700',
                                            } }}">
                                            {{ $item->jenis }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-primary">{{ $item->nama_efek }}</td>
                                    <td class="px-4 py-3 text-muted text-xs">{{ $item->penerbit ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-xs">
                                        {{ $item->jumlah ? number_format((float) $item->jumlah, 2, ',', '.') : '—' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-primary text-xs">
                                        {{ $item->total_nilai ? 'Rp' . number_format((float) $item->total_nilai, 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button"
                                            class="p-2 rounded-lg text-muted hover:text-accent hover:bg-[#f1f5f9] transition"
                                            title="Detail"
                                            onclick="openDetailModal({
                                                jenis: '{{ addslashes($item->jenis) }}',
                                                nama: '{{ addslashes($item->nama_efek) }}',
                                                penerbit: '{{ addslashes($item->penerbit ?? '-') }}',
                                                jumlah: '{{ $item->jumlah ? number_format((float) $item->jumlah, 2, ',', '.') : '-' }}',
                                                nilai: '{{ $item->total_nilai ? 'Rp' . number_format((float) $item->total_nilai, 0, ',', '.') : '-' }}',
                                                harga: '{{ $item->harga_saat_ini ? 'Rp' . number_format((float) $item->harga_saat_ini, 0, ',', '.') : '-' }}'
                                            })">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-line text-xs text-muted">
                    Total portofolio: <strong class="text-primary">{{ $portfolioItems->count() }} item</strong>
                </div>
            @endif
        </div>

        {{-- Modal Detail Portofolio --}}
        <div id="detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                <div
                    class="px-6 py-5 border-b border-line bg-gradient-to-r from-primary to-primary/80 flex items-center justify-between">
                    <div>
                        <p class="text-white/70 text-xs font-medium">Detail Efek</p>
                        <h3 id="detail-nama" class="font-bold text-white text-base mt-0.5">-</h3>
                    </div>
                    <button type="button" onclick="closeDetailModal()"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-white/80 hover:bg-white/10 hover:text-white transition flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted">Jenis Efek</span>
                        <span id="detail-jenis-badge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold">-</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted">Penerbit</span>
                        <span id="detail-penerbit" class="text-sm font-medium text-primary">-</span>
                    </div>

                    <div class="h-px bg-line"></div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-[#f8fafc] rounded-xl p-4">
                            <p class="text-xs text-muted mb-1">Jumlah UP/Lembar</p>
                            <p id="detail-jumlah" class="font-mono font-semibold text-primary text-sm">-</p>
                        </div>
                        <div class="bg-[#f8fafc] rounded-xl p-4">
                            <p class="text-xs text-muted mb-1">Harga/Unit</p>
                            <p id="detail-harga" class="font-mono font-semibold text-primary text-sm">-</p>
                        </div>
                    </div>

                    <div class="bg-accent/5 border border-accent/20 rounded-xl p-4 flex items-center justify-between">
                        <span class="text-xs font-medium text-accent">Total Nilai Pasar</span>
                        <span id="detail-nilai" class="font-bold text-accent text-lg">-</span>
                    </div>
                </div>

                <div class="px-6 pb-6">
                    <button type="button" onclick="closeDetailModal()"
                        class="w-full py-2.5 rounded-lg text-sm font-semibold text-white bg-primary hover:bg-primary/90 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection
@push('scripts')
    <script>
        const detailBadgeColors = {
            'Saham': 'bg-blue-100 text-blue-700',
            'Obligasi': 'bg-amber-100 text-amber-700',
            'Reksa Dana': 'bg-purple-100 text-purple-700',
        };

        function openDetailModal(data) {
            document.getElementById('detail-nama').textContent = data.nama;
            document.getElementById('detail-penerbit').textContent = data.penerbit;
            document.getElementById('detail-jumlah').textContent = data.jumlah;
            document.getElementById('detail-harga').textContent = data.harga;
            document.getElementById('detail-nilai').textContent = data.nilai;

            const badge = document.getElementById('detail-jenis-badge');
            badge.textContent = data.jenis;
            badge.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold ' +
                (detailBadgeColors[data.jenis] || 'bg-green-100 text-green-700');

            const modal = document.getElementById('detail-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDetailModal() {
            const modal = document.getElementById('detail-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Tutup modal jika klik area luar
        document.getElementById('detail-modal').addEventListener('click', function(e) {
            if (e.target === this) closeDetailModal();
        });

        // Tutup modal dengan tombol Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeDetailModal();
        });
    </script>
@endpush
