@extends('layouts.user')

@section('content')
<div class="max-w-5xl" x-data="analisaForm()">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary">Submit Analisa Reksa Dana</h1>
        <p class="text-sm text-muted mt-0.5">Isi data secara manual atau upload file Excel</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('user.analisa.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <input type="hidden" name="input_mode" :value="mode">

        {{-- Info Dasar --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h3 class="font-semibold text-primary">Informasi Reksa Dana</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="nama_reksa_dana" value="Nama Reksa Dana *" />
                    <x-text-input id="nama_reksa_dana" name="nama_reksa_dana" type="text" class="mt-1 block w-full" value="{{ old('nama_reksa_dana') }}" required />
                    <x-input-error :messages="$errors->get('nama_reksa_dana')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="jenis_reksa_dana" value="Jenis Reksa Dana *" />
                    <select id="jenis_reksa_dana" name="jenis_reksa_dana" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm" required>
                        <option value="">Pilih Jenis</option>
                        @foreach(['Saham','Pendapatan Tetap','Campuran','Pasar Uang'] as $j)
                        <option value="{{ $j }}" {{ old('jenis_reksa_dana') === $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('jenis_reksa_dana')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="total_aum" value="Total AUM (Rp)" />
                    <x-text-input id="total_aum" name="total_aum" type="number" step="0.01" class="mt-1 block w-full" value="{{ old('total_aum') }}" />
                </div>
                <div>
                    <x-input-label for="total_marcap_10_efek" value="Total MarCap 10 Efek Terbesar (Rp)" />
                    <x-text-input id="total_marcap_10_efek" name="total_marcap_10_efek" type="number" step="0.01" class="mt-1 block w-full" value="{{ old('total_marcap_10_efek') }}" />
                </div>
            </div>
        </div>

        {{-- Tab Pilih Mode --}}
        <div class="bg-white rounded-xl border border-line overflow-hidden">
            <div class="flex border-b border-line">
                <button type="button" @click="mode='manual'"
                    :class="mode==='manual' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
                    class="px-6 py-3.5 text-sm transition">
                     Input Manual
                </button>
                <button type="button" @click="mode='excel'"
                    :class="mode==='excel' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
                    class="px-6 py-3.5 text-sm transition">
                     Upload Excel
                </button>
            </div>

            {{-- TAB: MANUAL --}}
            <div x-show="mode==='manual'" class="p-6 space-y-8">

                {{-- Sektor --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-primary text-sm">Komposisi Sektor</h4>
                        <button type="button" @click="addRow('sektor')" class="text-xs text-primary hover:underline">+ Tambah Baris</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(row, i) in sektor" :key="i">
                            <div class="flex gap-2 items-center">
                                <input type="text" :name="`sektor[${i}][nama_sektor]`" x-model="row.nama_sektor" placeholder="Nama Sektor" class="flex-1 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                                <input type="number" :name="`sektor[${i}][bobot]`" x-model="row.bobot" placeholder="Bobot %" step="0.01" class="w-28 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                                <button type="button" @click="removeRow('sektor', i)" class="text-red-400 hover:text-red-600 px-1">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Efek --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-primary text-sm">Daftar Efek</h4>
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
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-primary text-sm">Kinerja Bulanan <span class="text-xs font-normal text-muted">(min. 2 bulan)</span></h4>
                        <button type="button" @click="addRow('kinerja')" class="text-xs text-primary hover:underline">+ Tambah Baris</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(row, i) in kinerja" :key="i">
                            <div class="flex gap-2 items-center">
                                <input type="month" :name="`kinerja[${i}][periode]`" x-model="row.periode" class="border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                                <input type="number" :name="`kinerja[${i}][return_pct]`" x-model="row.return_pct" placeholder="Return %" step="0.0001" class="w-36 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                                <button type="button" @click="removeRow('kinerja', i)" class="text-red-400 hover:text-red-600 px-1">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Obligasi --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-primary text-sm">Obligasi</h4>
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
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-primary text-sm">Bank</h4>
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
                                    <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Klasifikasi</th>
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
            </div>

            {{-- TAB: EXCEL --}}
            <div x-show="mode==='excel'" class="p-6 space-y-5">
                <div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        Download template Excel terlebih dahulu, isi data sesuai format, lalu upload kembali.
                        <a href="{{ route('user.analisa.template') }}" class="font-semibold underline ml-1">Download Template</a>
                    </div>
                </div>

                <div>
                    <x-input-label for="file_excel" value="Upload File Excel (.xlsx)" />
                    <input id="file_excel" name="file_excel" type="file" accept=".xlsx,.xls"
                        class="mt-1 block w-full text-sm text-muted border border-gray-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-primary file:text-white hover:file:bg-primary/90 cursor-pointer" />
                    <x-input-error :messages="$errors->get('file_excel')" class="mt-1" />
                    <p class="text-xs text-muted mt-1">Format: .xlsx atau .xls, maks 5MB. Sheet: Sektor, Efek, Kinerja, Obligasi, Bank.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-primary-button>Submit Analisa</x-primary-button>
            <a href="{{ route('user.analisa.index') }}" class="px-4 py-2 text-sm font-medium text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function analisaForm() {
    return {
        mode: 'manual',
        sektor:   [{ nama_sektor: '', bobot: '' }],
        efek:     [{ kode_efek: '', nama_efek: '', sektor: '', bobot: '', kontribusi_kinerja: '', market_cap: '', top_10: false }],
        kinerja:  [{ periode: '', return_pct: '' }, { periode: '', return_pct: '' }],
        obligasi: [{ kode_obligasi: '', nama_obligasi: '', bobot: '', durasi: '', rating: '' }],
        bank:     [{ nama_bank: '', bobot: '', car: '', npl: '', klasifikasi_risiko: '' }],

        addRow(type) {
            const defaults = {
                sektor:   { nama_sektor: '', bobot: '' },
                efek:     { kode_efek: '', nama_efek: '', sektor: '', bobot: '', kontribusi_kinerja: '', market_cap: '', top_10: false },
                kinerja:  { periode: '', return_pct: '' },
                obligasi: { kode_obligasi: '', nama_obligasi: '', bobot: '', durasi: '', rating: '' },
                bank:     { nama_bank: '', bobot: '', car: '', npl: '', klasifikasi_risiko: '' },
            };
            this[type].push({ ...defaults[type] });
        },

        removeRow(type, index) {
            if (this[type].length > 1) this[type].splice(index, 1);
        },
    };
}
</script>
@endpush
@endsection
