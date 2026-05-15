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
        <h1 class="text-2xl font-bold text-primary">Pendaftaran Member</h1>
        <p class="text-muted text-sm mt-1">Lengkapi data berikut untuk mendaftar sebagai member InvestaPremier</p>
    </div>

    @if (session('success'))
        <div
            class="mb-5 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
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
            <h2 class="font-bold text-primary mb-5 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary text-white text-xs grid place-items-center font-bold">1</span>
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
            <h2 class="font-bold text-primary mb-5 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary text-white text-xs grid place-items-center font-bold">2</span>
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

        {{-- SECTION 3: Portofolio --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm p-6 mb-5">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-bold text-primary flex items-center gap-2">
                    <span
                        class="w-6 h-6 rounded-full bg-primary text-white text-xs grid place-items-center font-bold">3</span>
                    Daftar Portofolio yang Dimiliki <span class="text-muted font-normal text-sm">(opsional)</span>
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
                                        @change="fetchHarga(row)"
                                        placeholder="Nama efek"
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="date" :name="`portfolios[${i}][mulai_kepemilikan]`"
                                        x-model="row.mulai_kepemilikan"
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" :name="`portfolios[${i}][jumlah]`" x-model="row.jumlah"
                                        @input="updateTotal(row)"
                                        placeholder="0" min="0" step="0.01"
                                        class="w-full px-2 py-1.5 border border-line rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                                </td>
                                <td class="px-4 py-2">
                                    <div class="relative">
                                        <input type="text" :name="`portfolios[${i}][harga_saat_ini]`" x-model="row.harga_saat_ini"
                                            readonly placeholder="—"
                                            class="w-full px-2 py-1.5 border border-line rounded-lg text-sm bg-[#f8fafc] text-muted cursor-not-allowed">
                                        <span x-show="row.loadingHarga" class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-muted animate-pulse">...</span>
                                    </div>
                                    <div x-show="row.tanggalHarga" class="text-xs text-muted mt-0.5" x-text="row.tanggalHarga"></div>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" :name="`portfolios[${i}][total_nilai]`" x-model="row.total_nilai"
                                        readonly placeholder="—"
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
                rows: @json($portfolios).map(r => ({...r, loadingHarga: false, tanggalHarga: r.harga_saat_ini ? '' : ''})),
                addRow() {
                    this.rows.push({
                        jenis: '', nama_efek: '', mulai_kepemilikan: '',
                        jumlah: '', harga_saat_ini: '', total_nilai: '',
                        loadingHarga: false, tanggalHarga: ''
                    });
                },
                async fetchHarga(row) {
                    const kode = row.nama_efek?.trim();
                    if (!kode) { row.harga_saat_ini = ''; row.total_nilai = ''; row.tanggalHarga = ''; return; }
                    row.loadingHarga = true;
                    try {
                        const res = await fetch(`{{ route('member.harga-efek') }}?kode=${encodeURIComponent(kode)}`);
                        const data = await res.json();
                        row.harga_saat_ini = data.harga ?? '';
                        row.tanggalHarga   = data.tanggal ? `T-1: ${data.tanggal}` : (data.harga ? '' : 'Harga tidak ditemukan');
                    } catch { row.harga_saat_ini = ''; row.tanggalHarga = ''; }
                    finally { row.loadingHarga = false; }
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
@endsection
