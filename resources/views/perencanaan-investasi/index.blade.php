@extends('layouts.user')

@section('title', 'Perencanaan Investasi - InvestaPremier')

@section('content')
    <div x-data="{ deleteId: null, deleteText: '', showForm: false }">

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-accent-teal/85">Perencanaan Investasi</h1>
                <p class="page-sub">Rencanakan dan proyeksikan tujuan investasi Anda</p>
            </div>
            <a href="{{ route('user.perencanaan-investasi.create') }}"
                class="px-4 py-2 bg-accent-teal text-white rounded-lg text-sm font-semibold hover:bg-accent-teal/90 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Rencana Baru
            </a>
        </div>

        @if (session('success'))
            <div class="alert-success">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="table-card">
            <div class="table-head">
                <h2 class="th-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Rencana Investasi
                </h2>
            </div>

            @if ($plans->isEmpty())
                <div class="py-16 text-center text-muted">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="font-medium">Belum ada rencana investasi</p>
                    <p class="text-sm mt-1">Klik "Buat Rencana Baru" untuk memulai perencanaan investasi Anda</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                                <th class="px-4 py-3.5 font-semibold">Kategori</th>
                                <th class="px-4 py-3.5 font-semibold">Kebutuhan Dana</th>
                                <th class="px-4 py-3.5 font-semibold">Target</th>
                                <th class="px-4 py-3.5 font-semibold">Investasi/Bulan</th>
                                <th class="px-4 py-3.5 font-semibold">Progress</th>
                                <th class="px-4 py-3.5 font-semibold">Profil Risiko</th>
                                <th class="px-4 py-3.5 font-semibold">Status</th>
                                <th class="px-4 py-3.5 font-semibold text-right">Tanggal</th>
                                <th class="px-4 py-3.5 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($plans as $plan)
                                <tr class="hover:bg-[#f8fafc] transition-colors">
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-primary">{{ $plan->kategori_perencanaan }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs font-medium">Rp{{ number_format($plan->kebutuhan_dana ?? 0, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs text-muted">{{ $plan->target_waktu_tahun ? $plan->target_waktu_tahun . ' tahun' : '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs font-medium">Rp{{ number_format($plan->investasi_per_bulan ?? 0, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $pct =
                                                $plan->kebutuhan_dana > 0
                                                    ? min(
                                                        100,
                                                        round(($plan->dana_tersedia / $plan->kebutuhan_dana) * 100),
                                                    )
                                                    : 0;
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-100 rounded-full h-1.5">
                                                <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-green-500' : 'bg-accent' }}"
                                                    style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span
                                                class="text-xs font-medium {{ $pct >= 100 ? 'text-green-600' : 'text-muted' }}">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs text-muted">{{ $plan->profil_risiko ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColors = [
                                                'Aktif' => 'bg-green-100 text-green-700',
                                                'Selesai' => 'bg-blue-100 text-blue-700',
                                                'Ditunda' => 'bg-yellow-100 text-yellow-700',
                                            ];
                                            $color = $statusColors[$plan->status] ?? 'bg-gray-100 text-gray-600';
                                        @endphp
                                        <span
                                            class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $color }}">{{ $plan->status }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-xs text-muted">
                                        {{ $plan->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('user.perencanaan-investasi.show', $plan) }}"
                                                class="p-2 rounded-lg text-muted hover:text-accent hover:bg-[#f1f5f9] transition"
                                                title="Detail">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('user.perencanaan-investasi.edit', $plan) }}"
                                                class="p-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <button type="button"
                                                @click="deleteId = {{ $plan->id }}; deleteText = '{{ addslashes($plan->kategori_perencanaan) }}'"
                                                class="p-2 rounded-lg text-muted hover:text-red-500 hover:bg-red-50 transition"
                                                title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($plans->hasPages())
                    <div class="px-6 py-4 border-t border-line flex items-center justify-between gap-4 text-sm">
                        <p class="text-muted text-xs">Menampilkan {{ $plans->firstItem() }}–{{ $plans->lastItem() }} dari
                            {{ $plans->total() }} rencana</p>
                        <div class="flex items-center gap-1">
                            @if ($plans->onFirstPage())
                                <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
                            @else
                                <a href="{{ $plans->previousPageUrl() }}"
                                    class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">←
                                    Prev</a>
                            @endif
                            @php
                                $current = $plans->currentPage();
                                $last = $plans->lastPage();
                                $start = max(1, $current - 2);
                                $end = min($last, $current + 2);
                            @endphp
                            @if ($start > 1)
                                <a href="{{ $plans->url(1) }}"
                                    class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                                @if ($start > 2)
                                    <span class="px-1 text-muted text-xs">…</span>
                                @endif
                            @endif
                            @foreach ($plans->getUrlRange($start, $end) as $page => $url)
                                <a href="{{ $url }}"
                                    class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $current ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
                            @endforeach
                            @if ($end < $last)
                                @if ($end < $last - 1)
                                    <span class="px-1 text-muted text-xs">…</span>
                                @endif
                                <a href="{{ $plans->url($last) }}"
                                    class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                            @endif
                            @if ($plans->hasMorePages())
                                <a href="{{ $plans->nextPageUrl() }}"
                                    class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next
                                    →</a>
                            @else
                                <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next →</span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Daftar Portofolio --}}
        <div class="table-card mt-6">
            <div class="table-head">
                <h2 class="th-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Daftar Portofolio
                </h2>
                <button type="button" @click="showForm = !showForm"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span x-text="showForm ? 'Tutup' : 'Tambah Portofolio'"></span>
                </button>
            </div>

            {{-- Form Tambah Portofolio (inline) --}}
            <div x-show="showForm" x-cloak class="px-5 py-4 border-b border-line bg-green-50/50" x-data="{
                jenis: '',
                produkList: [],
                produkLoading: false,
                produkSelected: '',
                harga: '',
                produkUrl: '{{ route('user.portofolio.produk') }}',
                bankList: [
                    'Bank Mandiri', 'Bank BCA', 'Bank BNI', 'Bank BRI', 'Bank CIMB Niaga',
                    'Bank Danamon', 'Bank Panin', 'Bank Permata', 'Bank Maybank',
                    'Bank BJB', 'Bank Jatim', 'Bank Jateng', 'Bank DIY', 'Bank BPD Bali',
                    'Bank Sumut', 'Bank Sumsel Babel', 'Bank Sultra', 'Bank Sulteng',
                    'Bank Kalbar', 'Bank Kaltim', 'Bank Kalteng', 'Bank Kalsel',
                    'Bank Lampung', 'Bank Aceh', 'Bank NTB', 'Bank NTT',
                    'Bank Sulselbar', 'Bank Maluku', 'Bank Papua', 'Bank Bengkulu',
                    'Bank Babel', 'Bank Riau Kepri', 'Lainnya',
                ],
                async loadProduk() {
                    if (!this.jenis) { this.produkList = []; return; }
                    const mappedJenis = this.jenis === 'Reksadana' ? 'Reksa Dana' : this.jenis;
                    if (this.jenis === 'Kas/Deposito') {
                        this.produkList = this.bankList.map(b => ({ id: b, nama: b }));
                        this.produkLoading = false;
                        this.produkSelected = '';
                        this.harga = '';
                        return;
                    }
                    this.produkLoading = true;
                    try {
                        const res = await fetch(this.produkUrl + '?jenis=' + encodeURIComponent(mappedJenis));
                        const data = await res.json();
                        this.produkList = data;
                    } catch (e) { this.produkList = []; }
                    this.produkLoading = false;
                    this.produkSelected = '';
                    this.harga = '';
                },
                onProdukChange() {
                    const found = this.produkList.find(p => p.id == this.produkSelected || p.nama === this.produkSelected);
                    if (found && found.harga) {
                        this.harga = found.harga;
                    }
                }
            }">
                <form method="POST" action="{{ route('user.portofolio.store') }}"
                    class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1">Jenis Efek *</label>
                        <select name="jenis" x-model="jenis" @change="loadProduk()" required
                            class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
                            <option value="">Pilih</option>
                            <option value="Kas/Deposito">Kas/Deposito</option>
                            <option value="Reksadana">Reksadana</option>
                            <option value="Saham">Saham</option>
                            <option value="Obligasi">Obligasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1">Nama Efek *</label>
                        <template x-if="produkList.length > 0">
                            <select name="nama_efek" x-model="produkSelected" @change="onProdukChange()" required
                                class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
                                <option value="">Pilih</option>
                                <template x-for="p in produkList" :key="p.id">
                                    <option :value="p.nama" x-text="p.nama"></option>
                                </template>
                            </select>
                        </template>
                        <template x-if="produkList.length === 0">
                            <input type="text" name="nama_efek" x-model="produkSelected" required
                                placeholder="Ketik manual..."
                                class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
                        </template>
                        <div x-show="produkLoading" class="text-xs text-muted mt-1">Memuat...</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1">Jumlah UP/Lembar</label>
                        <input type="number" name="jumlah" step="0.01" min="0" placeholder="0"
                            class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1">Harga Saat Ini</label>
                        <input type="number" name="harga_saat_ini" step="0.01" min="0" placeholder="0"
                            class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
                    </div>
                    <div>
                        <button type="submit"
                            class="w-full px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 transition">
                            Simpan
                        </button>
                    </div>
                    <div class="text-xs text-muted flex items-end pb-2">
                        Harga otomatis terisi jika pilih dari daftar
                    </div>
                </form>
            </div>

            <div class="px-5 py-3 border-b border-line bg-[#f8fafc]">
                <form method="GET" action="{{ route('user.perencanaan-investasi.index') }}"
                    class="flex items-center gap-3">
                    <label class="text-xs font-semibold text-muted">Jenis Efek:</label>
                    <select name="jenis_portfolio" onchange="this.form.submit()"
                        class="text-xs border border-line rounded-lg px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">Semua</option>
                        @foreach (['Kas/Deposito', 'Reksadana', 'Saham', 'Obligasi'] as $j)
                            <option value="{{ $j }}" {{ $jenisFilter === $j ? 'selected' : '' }}>
                                {{ $j }}</option>
                        @endforeach
                    </select>
                    @if ($jenisFilter)
                        <a href="{{ route('user.perencanaan-investasi.index') }}"
                            class="text-xs text-muted hover:text-primary underline">Reset</a>
                    @endif
                </form>
            </div>

            @if ($portfolioItems->isEmpty())
                <div class="py-12 text-center text-muted">
                    <p class="font-medium">Belum ada portofolio</p>
                    <p class="text-sm mt-1">Tambahkan portofolio Anda melalui menu <a href="{{ route('member.create') }}"
                            class="text-accent underline">Pendaftaran Member</a></p>
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
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
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

        {{-- Modal Hapus --}}
        <div x-show="deleteId !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/40" @click="deleteId = null"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
                x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 grid place-items-center shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-primary text-base">Hapus Rencana?</h3>
                        <p class="page-sub">Rencana berikut akan dihapus permanen:</p>
                        <p class="mt-2 text-sm text-primary font-medium bg-[#f8fafc] rounded-lg px-3 py-2 border border-line"
                            x-text="deleteText"></p>
                        <p class="text-xs text-red-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" @click="deleteId = null"
                        class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">Batal</button>
                    <form method="POST" :action="`/user/perencanaan-investasi/${deleteId}`">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">Ya,
                            Hapus</button>
                    </form>
                </div>
            </div>
        </div>


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
