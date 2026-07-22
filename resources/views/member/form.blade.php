@extends('layouts.user')

@section('title', 'Pendaftaran Member')

@section('content')
    @php
        $agamaList = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];
        $pekerjaanList = [
            'Pegawai Negeri',
            'Pegawai Swasta',
            'Wiraswasta',
            'Profesional',
            'Ibu Rumah Tangga',
            'Pelajar/Mahasiswa',
            'Lainnya',
        ];
        $penghasilanList = [
            '< Rp 50 Juta',
            'Rp 50 Juta – Rp 100 Juta',
            'Rp 100 Juta – Rp 500 Juta',
            'Rp 500 Juta – Rp 1 Miliar',
            '> Rp 1 Miliar',
        ];
        $jenisInvestasiList = ['Deposito', 'Reksa Dana', 'Saham', 'Obligasi', 'Properti', 'Emas', 'Lainnya'];
        $sumberDanaList = ['Gaji/Pendapatan Rutin', 'Tabungan', 'Warisan', 'Hasil Usaha', 'Penjualan Aset', 'Lainnya'];
        $tujuanList = [
            'Pendidikan',
            'Pensiun',
            'Pembelian Properti',
            'Pernikahan',
            'Dana Darurat',
            'Pertumbuhan Kekayaan',
            'Lainnya',
        ];

        $oldJenisInvestasi = old('jenis_investasi', $profile?->jenis_investasi ?? []);
        $oldSumberDana = old('sumber_dana', $profile?->sumber_dana ?? []);
        $oldTujuan = old('tujuan_investasi', $profile?->tujuan_investasi ?? []);
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-accent-teal/85">Pendaftaran Member</h1>
        <p class="page-sub">Lengkapi data berikut untuk mendaftar sebagai member InvestaPremier</p>
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

    @if ($profile)
        <div
            class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl text-sm border
    {{ $profile->status === 'approved' ? 'bg-green-50 border-green-200 text-green-700' : ($profile->status === 'rejected' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-amber-50 border-amber-200 text-amber-700') }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Status pendaftaran:
            <strong>{{ ['pending' => 'Menunggu Persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$profile->status] }}</strong>
            @if ($profile->status !== 'approved')
                — Anda dapat memperbarui data di bawah ini.
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('member.store') }}" x-data="memberForm()">
        @csrf

        {{-- SECTION 1: Data Diri --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm p-6 mb-5">
            <h2 class="font-bold text-accent-teal mb-5 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-accent text-white text-xs grid place-items-center font-bold">1</span>
                Data Diri
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <x-input-label value="No. Telepon" required="true" class="mb-1.5 text-sm font-semibold" />
                    <x-text-input type="text" name="no_telepon" value="{{ old('no_telepon', $profile?->no_telepon) }}"
                        placeholder="Contoh: 081234567890" class="w-full px-3 py-2 text-sm" />
                    <x-input-error :messages="$errors->get('no_telepon')" class="mt-1 text-xs" />
                </div>
                <div>
                    <x-input-label value="Jenis Kelamin" required="true" class="mb-1.5 text-sm font-semibold" />
                    <x-form-radio name="jenis_kelamin" :options="['Laki-laki', 'Perempuan']" :selected="old('jenis_kelamin', $profile?->jenis_kelamin)" />
                    <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-1 text-xs" />
                </div>
                <div>
                    <x-input-label value="Kewarganegaraan" required="true" class="mb-1.5 text-sm font-semibold" />
                    <x-form-radio name="kewarganegaraan" :options="['WNI', 'WNA']" :selected="old('kewarganegaraan', $profile?->kewarganegaraan)" />
                    <x-input-error :messages="$errors->get('kewarganegaraan')" class="mt-1 text-xs" />
                </div>
                <div>
                    <x-input-label value="Agama" required="true" class="mb-1.5 text-sm font-semibold" />
                    <x-form-select name="agama" :options="$agamaList" :selected="old('agama', $profile?->agama)" placeholder="-- Pilih Agama --" />
                    <x-input-error :messages="$errors->get('agama')" class="mt-1 text-xs" />
                </div>
                <div>
                    <x-input-label value="Pekerjaan" required="true" class="mb-1.5 text-sm font-semibold" />
                    <x-form-select name="pekerjaan" :options="$pekerjaanList" :selected="old('pekerjaan', $profile?->pekerjaan)"
                        placeholder="-- Pilih Pekerjaan --" />
                    <x-input-error :messages="$errors->get('pekerjaan')" class="mt-1 text-xs" />
                </div>
                <div>
                    <x-input-label value="Rata-rata Penghasilan per Tahun" required="true"
                        class="mb-1.5 text-sm font-semibold" />
                    <x-form-select name="rata_rata_penghasilan" :options="$penghasilanList" :selected="old('rata_rata_penghasilan', $profile?->rata_rata_penghasilan)"
                        placeholder="-- Pilih Rentang --" />
                    <x-input-error :messages="$errors->get('rata_rata_penghasilan')" class="mt-1 text-xs" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label value="Pembukaan Rekening Efek dan Bertransaksi untuk Kepentingan" required="true"
                        class="mb-1.5 text-sm font-semibold" />
                    <x-form-radio name="pembukaan_rekening_efek" :options="['Pribadi', 'Pihak Lainnya']" :selected="old('pembukaan_rekening_efek', $profile?->pembukaan_rekening_efek)" />
                    <x-input-error :messages="$errors->get('pembukaan_rekening_efek')" class="mt-1 text-xs" />
                </div>
            </div>
        </div>

        {{-- SECTION 2: Investasi --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm p-6 mb-5">
            <h2 class="font-bold text-accent-teal mb-5 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-accent text-white text-xs grid place-items-center font-bold">2</span>
                Pekerjaan dan Investasi
            </h2>

            <div class="mb-5">
                <x-input-label value="Jenis Investasi yang Dimiliki" required="true" class="mb-2 text-sm font-semibold" />
                <x-form-checkbox name="jenis_investasi" :options="$jenisInvestasiList" :selected="$oldJenisInvestasi" />
                <x-input-error :messages="$errors->get('jenis_investasi')" class="mt-1 text-xs" />
            </div>

            <div class="mb-5">
                <x-input-label value="Sumber Dana" required="true" class="mb-2 text-sm font-semibold" />
                <x-form-checkbox name="sumber_dana" :options="$sumberDanaList" :selected="$oldSumberDana" />
                <x-input-error :messages="$errors->get('sumber_dana')" class="mt-1 text-xs" />
            </div>

            <div class="mb-5">
                <x-input-label value="Tujuan Investasi" required="true" class="mb-2 text-sm font-semibold" />
                <x-form-checkbox name="tujuan_investasi" :options="$tujuanList" :selected="$oldTujuan" />
                <x-input-error :messages="$errors->get('tujuan_investasi')" class="mt-1 text-xs" />
            </div>

            <div>
                <x-input-label class="mb-1.5 text-sm font-semibold">
                    Maksud / Tujuan Lain <span class="text-muted font-normal">(opsional)</span>
                </x-input-label>
                <x-text-input type="text" name="maksud_tujuan_lain"
                    value="{{ old('maksud_tujuan_lain', $profile?->maksud_tujuan_lain) }}"
                    placeholder="Tuliskan jika ada tujuan lain..." class="w-full px-3 py-2 text-sm" />
            </div>
        </div>

        {{-- SECTION: Harga Layanan --}}
        @php
            $pricingPlans = [
                [
                    'name' => 'Review Produk Investasi',
                    'price' => '100rb',
                    'popular' => false,
                    'features' => [
                        ['label' => 'Kinerja', 'on' => true],
                        ['label' => 'Benchmark (All Funds)', 'on' => true],
                        ['label' => 'Analisa Pengelolaan Investasi', 'on' => false],
                        ['label' => 'Analisa Efek Portofolio (Selected Funds)', 'on' => false],
                        ['label' => 'Pilihan produk berdasarkan profil risiko', 'on' => false],
                        ['label' => 'Kriteria underlying', 'on' => false],
                        ['label' => 'Kriteria return-risk (Recomended Funds)', 'on' => false],
                        ['label' => 'Monitoring Bulanan (Recommended Funds)', 'on' => false],
                        ['label' => 'untuk mencapai Tujuan Investasi', 'on' => false],
                    ],
                ],
                [
                    'name' => 'Analisa Produk Investasi',
                    'price' => '250rb',
                    'popular' => true,
                    'features' => [
                        ['label' => 'Kinerja', 'on' => true],
                        ['label' => 'Benchmark (All Funds)', 'on' => true],
                        ['label' => 'Analisa Pengelolaan Investasi', 'on' => true],
                        ['label' => 'Analisa Efek Portofolio (Selected Funds)', 'on' => true],
                        ['label' => 'Pilihan produk berdasarkan profil risiko', 'on' => false],
                        ['label' => 'Kriteria underlying', 'on' => false],
                        ['label' => 'Kriteria return-risk (Recomended Funds)', 'on' => false],
                        ['label' => 'Monitoring Bulanan (Recommended Funds)', 'on' => false],
                        ['label' => 'untuk mencapai Tujuan Investasi', 'on' => false],
                    ],
                ],
                [
                    'name' => 'Rekomendasi Produk Investasi',
                    'price' => '350rb',
                    'popular' => false,
                    'features' => [
                        ['label' => 'Kinerja', 'on' => true],
                        ['label' => 'Benchmark (All Funds)', 'on' => true],
                        ['label' => 'Analisa Pengelolaan Investasi', 'on' => true],
                        ['label' => 'Analisa Efek Portofolio (Selected Funds)', 'on' => true],
                        ['label' => 'Pilihan produk berdasarkan profil risiko', 'on' => true],
                        ['label' => 'Kriteria underlying', 'on' => true],
                        ['label' => 'Kriteria return-risk (Recomended Funds)', 'on' => true],
                        ['label' => 'Monitoring Bulanan (Recommended Funds)', 'on' => false],
                        ['label' => 'untuk mencapai Tujuan Investasi', 'on' => false],
                    ],
                ],
                [
                    'name' => 'Penasihat Investasi Komprehensif',
                    'price' => '1jt',
                    'popular' => true,
                    'features' => [
                        ['label' => 'Kinerja', 'on' => true],
                        ['label' => 'Benchmark (All Funds)', 'on' => true],
                        ['label' => 'Analisa Pengelolaan Investasi', 'on' => true],
                        ['label' => 'Analisa Efek Portofolio (Selected Funds)', 'on' => true],
                        ['label' => 'Pilihan produk berdasarkan profil risiko', 'on' => true],
                        ['label' => 'Kriteria underlying', 'on' => true],
                        ['label' => 'Kriteria return-risk (Recomended Funds)', 'on' => true],
                        ['label' => 'Monitoring Bulanan (Recommended Funds)', 'on' => true],
                        ['label' => 'untuk mencapai Tujuan Investasi', 'on' => true],
                    ],
                ],
            ];
        @endphp
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-white/60 shadow-lg p-6 mb-5">
            <h2 class="font-bold text-primary mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Harga Layanan
            </h2>
            <p class="text-sm text-muted mb-5">Pilih layanan investasi yang sesuai dengan kebutuhan Anda.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($pricingPlans as $plan)
                    <div
                        class="relative rounded-xl p-5 transition-all duration-300 flex flex-col hover:-translate-y-1
                        {{ $plan['popular']
                            ? 'bg-white/90 backdrop-blur-xl border-2 border-accent/40 shadow-xl shadow-accent/5'
                            : 'bg-white/70 backdrop-blur-sm border border-white/60 shadow-sm hover:shadow-lg hover:border-accent/30' }}">
                        @if ($plan['popular'])
                            <span
                                class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold text-white uppercase tracking-wider"
                                style="background:linear-gradient(135deg,#16a34a,#22c55e)">Populer</span>
                        @endif
                        <div class="font-semibold text-primary text-sm">{{ $plan['name'] }}</div>
                        <div class="text-2xl font-bold mt-1" style="color:#16a34a">{{ $plan['price'] }} <span
                                class="text-xs font-normal text-muted">/bln</span></div>
                        <hr class="my-3" style="border-color:#e2e8f0">
                        <ul class="space-y-2 text-xs flex-1">
                            @foreach ($plan['features'] as $f)
                                <li class="flex items-start gap-2">
                                    @if ($f['on'])
                                        <span
                                            class="w-4 h-4 rounded-full bg-green-100 text-green-600 grid place-items-center shrink-0 mt-0.5">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M20 6 9 17l-5-5" />
                                            </svg>
                                        </span>
                                    @else
                                        <span
                                            class="w-4 h-4 rounded-full bg-red-50 text-red-400 grid place-items-center shrink-0 mt-0.5">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M18 6 6 18" />
                                                <path d="m6 6 12 12" />
                                            </svg>
                                        </span>
                                    @endif
                                    <span
                                        class="{{ $f['on'] ? 'text-gray-700' : 'text-gray-400' }}">{{ $f['label'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-auto pt-3">
                            <button type="button" onclick="openPricingModal('{{ $plan['name'] }}')"
                                class="w-full py-2 rounded-lg text-xs font-semibold transition"
                                style="{{ $plan['popular'] ? 'background:#16a34a;color:#fff' : 'background:#f1f5f9;color:#334155' }}">
                                Langganan Sekarang
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SECTION 3: Portofolio --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm p-6 mb-5">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-bold text-accent-teal flex items-center gap-2">
                    <span
                        class="w-6 h-6 rounded-full bg-accent text-white text-xs grid place-items-center font-bold">3</span>
                    Daftar Portofolio yang Dimiliki <span class="text-accent-teal/70 font-normal text-sm">(opsional)</span>
                </h2>
                <button type="button" @click="addRow()"
                    class="flex items-center gap-1.5 px-3 py-1.5 border border-accent text-accent rounded-lg text-xs font-semibold hover:bg-accent/5 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Baris
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide border-b border-line">
                            <th class="px-4 py-3 font-semibold">Jenis</th>
                            <th class="px-4 py-3 font-semibold">Nama Efek</th>
                            <th class="px-4 py-3 font-semibold">Mulai Kepemilikan</th>
                            <th class="px-4 py-3 font-semibold">Jumlah (Lembar/Unit)</th>
                            <th class="px-4 py-3 font-semibold">Harga Saat Ini (T-1)</th>
                            <th class="px-4 py-3 font-semibold">Total Nilai</th>
                            <th class="px-4 py-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, i) in rows" :key="i">
                            <tr class="border-b border-line">
                                <td class="px-4 py-2">
                                    <select :name="`portfolios[${i}][jenis]`"
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                                        <option value="">-- Pilih --</option>
                                        <option value="Dana" :selected="row.jenis === 'Dana'">Dana</option>
                                        <option value="Saham" :selected="row.jenis === 'Saham'">Saham</option>
                                        <option value="Obligasi" :selected="row.jenis === 'Obligasi'">Obligasi</option>
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="text" :name="`portfolios[${i}][nama_efek]`" x-model="row.nama_efek"
                                        @change="fetchHarga(row)" placeholder="Nama efek"
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="date" :name="`portfolios[${i}][mulai_kepemilikan]`"
                                        x-model="row.mulai_kepemilikan"
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" :name="`portfolios[${i}][jumlah]`" x-model="row.jumlah"
                                        @input="updateTotal(row)" placeholder="0" min="0" step="0.01"
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                                </td>
                                <td class="px-4 py-2">
                                    <div class="relative">
                                        <input type="text" :name="`portfolios[${i}][harga_saat_ini]`"
                                            x-model="row.harga_saat_ini" readonly placeholder="—"
                                            class="w-full px-2 py-1.5 border border-line rounded-lg text-sm bg-[#f8fafc] text-muted cursor-not-allowed">
                                        <span x-show="row.loadingHarga"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-muted animate-pulse">...</span>
                                    </div>
                                    <div x-show="row.tanggalHarga" class="text-xs text-muted mt-0.5"
                                        x-text="row.tanggalHarga"></div>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" :name="`portfolios[${i}][total_nilai]`"
                                        x-model="row.total_nilai" readonly placeholder="—"
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-sm bg-[#f8fafc] text-muted cursor-not-allowed">
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button" @click="rows.splice(i, 1)"
                                        class="p-1 rounded text-muted hover:text-red-500 hover:bg-red-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="rows.length === 0">
                            <td colspan="7" class="px-4 py-6 text-center text-muted text-sm">
                                Belum ada portofolio. Klik "Tambah Baris" untuk menambahkan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                class="px-6 py-3 bg-accent text-white rounded-xl font-semibold text-sm hover:bg-accent/90 transition shadow-sm shadow-accent/20">
                {{ $profile ? 'Perbarui Pendaftaran' : 'Kirim Pendaftaran' }}
            </button>
            <a href="{{ route('user.dashboard') }}"
                class="px-6 py-3 border border-line text-muted rounded-xl font-semibold text-sm hover:text-primary hover:border-primary/30 transition">
                Batal
            </a>
        </div>

    </form>

    <script>
        function memberForm() {
            return {
                rows: @json($portfolios).map(r => ({
                    ...r,
                    loadingHarga: false,
                    tanggalHarga: r.harga_saat_ini ? '' : ''
                })),
                addRow() {
                    this.rows.push({
                        jenis: '',
                        nama_efek: '',
                        mulai_kepemilikan: '',
                        jumlah: '',
                        harga_saat_ini: '',
                        total_nilai: '',
                        loadingHarga: false,
                        tanggalHarga: ''
                    });
                },
                async fetchHarga(row) {
                    const kode = row.nama_efek?.trim();
                    if (!kode) {
                        row.harga_saat_ini = '';
                        row.total_nilai = '';
                        row.tanggalHarga = '';
                        return;
                    }
                    row.loadingHarga = true;
                    try {
                        const res = await fetch(`{{ route('member.harga-efek') }}?kode=${encodeURIComponent(kode)}`);
                        const data = await res.json();
                        row.harga_saat_ini = data.harga ?? '';
                        row.tanggalHarga = data.tanggal ? `T-1: ${data.tanggal}` : (data.harga ? '' :
                            'Harga tidak ditemukan');
                    } catch {
                        row.harga_saat_ini = '';
                        row.tanggalHarga = '';
                    } finally {
                        row.loadingHarga = false;
                    }
                    this.updateTotal(row);
                },
                updateTotal(row) {
                    const j = parseFloat(row.jumlah) || 0;
                    const h = parseFloat(row.harga_saat_ini) || 0;
                    row.total_nilai = (j > 0 && h > 0) ? (j * h).toFixed(2) : '';
                }
            }
        }
    </script>

    {{-- Modal Segera Hadir --}}
    <div id="pricingModalMember"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4" style="display:none"
        onclick="if(event.target===this)closePricingModalMember()">
        <div class="bg-white/90 backdrop-blur-2xl rounded-2xl border border-white/80 shadow-2xl p-8 text-center max-w-sm w-full"
            style="transform:translateY(0)">
            <div class="w-16 h-16 rounded-full bg-green-50 mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                    <circle cx="12" cy="12" r="4" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-primary mb-1">Segera Hadir</h3>
            <p class="text-sm text-muted mb-5">Fitur langganan <strong id="modal-plan-label-member"></strong> masih dalam
                tahap pengembangan. Kami akan memberitahu Anda begitu tersedia!</p>
            <button onclick="closePricingModalMember()"
                class="px-6 py-2.5 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition shadow-lg shadow-green-600/20">
                Saya Mengerti
            </button>
        </div>
    </div>
    <script>
        function openPricingModal(planName) {
            document.getElementById('modal-plan-label-member').textContent = planName;
            document.getElementById('pricingModalMember').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closePricingModalMember() {
            document.getElementById('pricingModalMember').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePricingModalMember();
        });
    </script>
@endsection
