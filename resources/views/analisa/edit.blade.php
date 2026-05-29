@extends('layouts.user')

@section('content')
<div class="max-w-5xl" x-data="editForm()">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary">Edit Analisa Reksa Dana</h1>
        <p class="text-sm text-muted mt-0.5">Perbarui informasi dan data manual analisa</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('user.analisa.update', $analisa) }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- Info Dasar --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h3 class="font-semibold text-primary">Informasi Reksa Dana</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="kode_reksa_dana" value="Kode Reksa Dana" />
                    <x-text-input id="kode_reksa_dana" name="kode_reksa_dana" type="text" class="mt-1 block w-full"
                        value="{{ old('kode_reksa_dana', $analisa->kode_reksa_dana) }}" />
                    <x-input-error :messages="$errors->get('kode_reksa_dana')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="nama_reksa_dana" value="Nama Reksa Dana *" />
                    <x-text-input id="nama_reksa_dana" name="nama_reksa_dana" type="text" class="mt-1 block w-full"
                        value="{{ old('nama_reksa_dana', $analisa->nama_reksa_dana) }}" required />
                    <x-input-error :messages="$errors->get('nama_reksa_dana')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="jenis_reksa_dana" value="Jenis Reksa Dana *" />
                    <select id="jenis_reksa_dana" name="jenis_reksa_dana" required
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        <option value="">Pilih Jenis</option>
                        @foreach(['Saham','Pendapatan Tetap','Campuran','Pasar Uang','Terproteksi','Global','DIRE-DINFRA','Penyertaan terbatas'] as $j)
                            <option value="{{ $j }}" {{ old('jenis_reksa_dana', $analisa->jenis_reksa_dana) === $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('jenis_reksa_dana')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Kategori" />
                    <div class="mt-1 flex flex-wrap gap-3">
                        @foreach (['Konvensional', 'Syariah', 'index', 'ETF'] as $k)
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                <input type="checkbox" name="kategori[]" value="{{ $k }}"
                                    {{ in_array($k, old('kategori', $analisa->kategori ?? [])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-primary focus:ring-primary/20">
                                {{ $k }}
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('kategori')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="total_aum" value="Total AUM (Rp)" />
                    <x-text-input id="total_aum" name="total_aum" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('total_aum', $analisa->total_aum) }}" />
                    <x-input-error :messages="$errors->get('total_aum')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="total_marcap_10_efek" value="Total MarCap 10 Efek Terbesar (Rp)" />
                    <x-text-input id="total_marcap_10_efek" name="total_marcap_10_efek" type="number" step="0.01" class="mt-1 block w-full"
                        value="{{ old('total_marcap_10_efek', $analisa->total_marcap_10_efek) }}" />
                    <x-input-error :messages="$errors->get('total_marcap_10_efek')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="tanggal_data" value="Tanggal Data *" />
                    <x-text-input id="tanggal_data" name="tanggal_data" type="date" class="mt-1 block w-full"
                        value="{{ old('tanggal_data', $analisa->tanggal_data?->format('Y-m-d')) }}" required />
                    <x-input-error :messages="$errors->get('tanggal_data')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="ffs_bulan" value="Bulan FFS *" />
                    <select id="ffs_bulan" name="ffs_bulan" required
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        <option value="">Pilih Bulan</option>
                        @foreach(range(1, 12) as $bulan)
                            <option value="{{ $bulan }}" {{ (int) old('ffs_bulan', $analisa->ffs_bulan) === $bulan ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('ffs_bulan')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="ffs_tahun" value="Tahun FFS *" />
                    <x-text-input id="ffs_tahun" name="ffs_tahun" type="number" min="2000" max="2100" class="mt-1 block w-full"
                        value="{{ old('ffs_tahun', $analisa->ffs_tahun) }}" required />
                    <x-input-error :messages="$errors->get('ffs_tahun')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="unit_penyertaan" value="Jumlah Unit Penyertaan" />
                    <x-text-input id="unit_penyertaan" name="unit_penyertaan" type="number" step="0.0001" class="mt-1 block w-full"
                        value="{{ old('unit_penyertaan', $analisa->unit_penyertaan) }}" />
                    <x-input-error :messages="$errors->get('unit_penyertaan')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="nab_per_unit" value="NAB/UP" />
                    <x-text-input id="nab_per_unit" name="nab_per_unit" type="number" step="0.000001" class="mt-1 block w-full"
                        value="{{ old('nab_per_unit', $analisa->nab_per_unit) }}" />
                    <x-input-error :messages="$errors->get('nab_per_unit')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="benchmark" value="Benchmark" />
                    <x-text-input id="benchmark" name="benchmark" type="text" class="mt-1 block w-full"
                        value="{{ old('benchmark', $analisa->benchmark) }}" />
                    <x-input-error :messages="$errors->get('benchmark')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="tujuan_investasi" value="Tujuan Investasi" />
                    <x-text-input id="tujuan_investasi" name="tujuan_investasi" type="text" class="mt-1 block w-full"
                        value="{{ old('tujuan_investasi', $analisa->tujuan_investasi) }}" />
                    <x-input-error :messages="$errors->get('tujuan_investasi')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="kebijakan_investasi" value="Kebijakan Investasi" />
                    <x-text-input id="kebijakan_investasi" name="kebijakan_investasi" type="text" class="mt-1 block w-full"
                        value="{{ old('kebijakan_investasi', $analisa->kebijakan_investasi) }}" />
                    <x-input-error :messages="$errors->get('kebijakan_investasi')" class="mt-1" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            @include('analisa.partials.form-alokasi-aset')
        </div>

        {{-- Sektor --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-primary text-sm">Komposisi Sektor</h3>
                <button type="button" @click="addRow('sektor')" class="text-xs text-primary hover:underline">+ Tambah Baris</button>
            </div>
            <div class="space-y-2">
                <template x-for="(row, i) in sektor" :key="i">
                    <div class="flex gap-2 items-center">
                        <input type="text" :name="`sektor[${i}][nama_sektor]`" x-model="row.nama_sektor"
                            placeholder="Nama Sektor"
                            class="flex-1 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                        <input type="number" :name="`sektor[${i}][bobot]`" x-model="row.bobot"
                            placeholder="Bobot %" step="0.01"
                            class="w-28 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                        <button type="button" @click="removeRow('sektor', i)" class="text-red-400 hover:text-red-600 px-1">✕</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Efek --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-primary text-sm">Daftar Efek</h3>
                <button type="button" @click="addRow('efek')" class="text-xs text-primary hover:underline">+ Tambah Baris</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc]">
                        <tr>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Efek</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Sektor</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kontribusi %</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Market Cap</th>
                            <th class="text-center px-2 py-2 text-xs font-semibold text-muted">Top 10</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <template x-for="(row, i) in efek" :key="i">
                            <tr>
                                <td class="px-1 py-1"><input type="text" :name="`efek[${i}][kode_efek]`" x-model="row.kode_efek" placeholder="BBCA" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1"><input type="text" :name="`efek[${i}][nama_efek]`" x-model="row.nama_efek" placeholder="Nama Efek" class="w-40 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1"><input type="text" :name="`efek[${i}][sektor]`" x-model="row.sektor" placeholder="Sektor" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1"><input type="number" :name="`efek[${i}][bobot]`" x-model="row.bobot" step="0.01" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1"><input type="number" :name="`efek[${i}][kontribusi_kinerja]`" x-model="row.kontribusi_kinerja" step="0.0001" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1"><input type="number" :name="`efek[${i}][market_cap]`" x-model="row.market_cap" step="1" class="w-32 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1 text-center"><input type="checkbox" :name="`efek[${i}][top_10]`" x-model="row.top_10" value="1" class="rounded border-gray-300 text-primary focus:ring-primary" /></td>
                                <td class="px-1 py-1"><button type="button" @click="removeRow('efek', i)" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kinerja Bulanan --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-primary text-sm">Kinerja Bulanan</h3>
                <button type="button" @click="addRow('kinerja')" class="text-xs text-primary hover:underline">+ Tambah Baris</button>
            </div>
            <div class="space-y-2">
                <template x-for="(row, i) in kinerja" :key="i">
                    <div class="flex gap-2 items-center">
                        <input type="month" :name="`kinerja[${i}][periode]`" x-model="row.periode"
                            class="border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                        <input type="number" :name="`kinerja[${i}][return_pct]`" x-model="row.return_pct"
                            placeholder="Return %" step="0.0001"
                            class="w-36 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                        <button type="button" @click="removeRow('kinerja', i)" class="text-red-400 hover:text-red-600 px-1">✕</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Obligasi --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-primary text-sm">Obligasi</h3>
                <button type="button" @click="addRow('obligasi')" class="text-xs text-primary hover:underline">+ Tambah Baris</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc]">
                        <tr>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Obligasi</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Durasi (thn)</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Rating</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <template x-for="(row, i) in obligasi" :key="i">
                            <tr>
                                <td class="px-1 py-1"><input type="text" :name="`obligasi[${i}][kode_obligasi]`" x-model="row.kode_obligasi" placeholder="FR0091" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1"><input type="text" :name="`obligasi[${i}][nama_obligasi]`" x-model="row.nama_obligasi" placeholder="Nama Obligasi" class="w-48 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][bobot]`" x-model="row.bobot" step="0.01" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][durasi]`" x-model="row.durasi" step="0.01" class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1">
                                    <select :name="`obligasi[${i}][rating]`" x-model="row.rating" class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                                        <option value="">-</option>
                                        @foreach(['AAA','AA+','AA','AA-','A+','A','A-','BBB+','BBB','BBB-','BB','B','CCC','D'] as $r)
                                            <option value="{{ $r }}">{{ $r }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-1 py-1"><button type="button" @click="removeRow('obligasi', i)" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Bank --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-primary text-sm">Bank</h3>
                <button type="button" @click="addRow('bank')" class="text-xs text-primary hover:underline">+ Tambah Baris</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc]">
                        <tr>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Bank</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">CAR %</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">NPL %</th>
                            <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Klasifikasi Risiko</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <template x-for="(row, i) in bank" :key="i">
                            <tr>
                                <td class="px-1 py-1"><input type="text" :name="`bank[${i}][nama_bank]`" x-model="row.nama_bank" placeholder="Nama Bank" class="w-36 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1"><input type="number" :name="`bank[${i}][bobot]`" x-model="row.bobot" step="0.01" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1"><input type="number" :name="`bank[${i}][car]`" x-model="row.car" step="0.01" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1"><input type="number" :name="`bank[${i}][npl]`" x-model="row.npl" step="0.01" class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                                <td class="px-1 py-1">
                                    <select :name="`bank[${i}][klasifikasi_risiko]`" x-model="row.klasifikasi_risiko" class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                                        <option value="">-</option>
                                        <option value="Rendah">Rendah</option>
                                        <option value="Sedang">Sedang</option>
                                        <option value="Tinggi">Tinggi</option>
                                    </select>
                                </td>
                                <td class="px-1 py-1"><button type="button" @click="removeRow('bank', i)" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                class="px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition">
                Simpan Perubahan
            </button>
            <a href="{{ route('user.analisa.index') }}"
               class="px-5 py-2 text-sm font-medium text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">
                Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function editForm() {
    return {
        sektor:   @json($editData['sektor']),
        efek:     @json($editData['efek']),
        kinerja:  @json($editData['kinerja']),
        obligasi: @json($editData['obligasi']),
        bank:     @json($editData['bank']),
        alokasi_aset: @json($editData['alokasi_aset']),

        addRow(type) {
            const defaults = {
                sektor:   { nama_sektor: '', bobot: '' },
                efek:     { kode_efek: '', nama_efek: '', sektor: '', bobot: '', kontribusi_kinerja: '', market_cap: '', top_10: false },
                kinerja:  { periode: '', return_pct: '' },
                obligasi: { kode_obligasi: '', nama_obligasi: '', bobot: '', durasi: '', rating: '' },
                bank:     { nama_bank: '', bobot: '', car: '', npl: '', klasifikasi_risiko: '' },
                alokasi_aset: { nama_aset: '', persentase: '' },
            };
            this[type].push({ ...defaults[type] });
        },

        removeRow(type, i) {
            this[type].splice(i, 1);
        },

        alokasiAsetTotal() {
            return this.alokasi_aset.reduce((sum, row) => sum + (parseFloat(row.persentase) || 0), 0);
        },

        alokasiAsetTotalValid() {
            const filled = this.alokasi_aset.some(row => String(row.nama_aset || '').trim() !== '' || String(row.persentase || '').trim() !== '');
            return !filled || Math.abs(this.alokasiAsetTotal() - 100) <= 0.01;
        },
    };
}
</script>
@endpush
@endsection
