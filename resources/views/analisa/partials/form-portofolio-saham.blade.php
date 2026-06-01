@php
    $inputSuffix = $inputSuffix ?? 'manual';
    $marcapReadonly = $marcapReadonly ?? false;
    $nabRequired = $nabRequired ?? false;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <x-input-label for="tanggal_data_{{ $inputSuffix }}" value="Tanggal Data" />
        <x-text-input id="tanggal_data_{{ $inputSuffix }}" type="text" placeholder="dd/mm/yyyy"
            class="mt-1 block w-full" x-model="tanggalData" />
    </div>
    <div>
        <x-input-label for="total_aum_{{ $inputSuffix }}" value="Total AUM (Rp)" />
        <x-text-input id="total_aum_{{ $inputSuffix }}" name="total_aum" type="number" step="0.01"
            class="mt-1 block w-full" x-model="totalAum" />
        <x-input-error :messages="$errors->get('total_aum')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="total_marcap_10_efek_{{ $inputSuffix }}" value="Total MarCap 10 Saham Terbesar (Rp)" />
        <x-text-input id="total_marcap_10_efek_{{ $inputSuffix }}" name="total_marcap_10_efek" type="number"
            step="0.01" class="mt-1 block w-full {{ $marcapReadonly ? 'bg-gray-50' : '' }}"
            x-model="totalMarcap10Efek" :readonly="$marcapReadonly" />
        <x-input-error :messages="$errors->get('total_marcap_10_efek')" class="mt-1" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <x-input-label for="unit_penyertaan_{{ $inputSuffix }}" value="Jumlah Unit Penyertaan" />
        <x-text-input id="unit_penyertaan_{{ $inputSuffix }}" name="unit_penyertaan" type="number"
            step="0.0001" class="mt-1 block w-full" x-model="unitPenyertaan" />
        <x-input-error :messages="$errors->get('unit_penyertaan')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="nab_per_unit_{{ $inputSuffix }}" value="NAB/UP{{ $nabRequired ? ' *' : '' }}" />
        <x-text-input id="nab_per_unit_{{ $inputSuffix }}" name="nab_per_unit" type="number" step="0.000001"
            class="mt-1 block w-full" x-model="nabPerUnit" :required="$nabRequired" />
        <x-input-error :messages="$errors->get('nab_per_unit')" class="mt-1" />
    </div>
    <div x-show="jenisLaporan === 'kalender_ffs'">
        <x-input-label value="Kalender FFS" />
        <div class="mt-1 grid grid-cols-2 gap-2">
            <select x-model="ffsBulan"
                class="border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20"
                aria-label="Bulan FFS">
                <option value="">Bulan</option>
                @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $bulan)
                    <option value="{{ $index + 1 }}">{{ $bulan }}</option>
                @endforeach
            </select>
            <input type="number" min="2000" max="2100" x-model="ffsTahun"
                class="border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20"
                placeholder="2026" />
        </div>
    </div>
</div>

@include('analisa.partials.form-alokasi-aset')

<div>
    <div class="flex items-center justify-between mb-3">
        <h4 class="font-semibold text-primary text-sm">Komposisi Sektor</h4>
        <button type="button" @click="addRow('sektor')" class="text-xs text-primary hover:underline">+
            Tambah Baris</button>
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
                <button type="button" @click="removeRow('sektor', i)"
                    class="text-red-400 hover:text-red-600 px-1">✕</button>
            </div>
        </template>
    </div>
</div>

<div>
    <div class="flex items-center justify-between mb-3">
        <h4 class="font-semibold text-primary text-sm">Daftar Efek</h4>
        <button type="button" @click="addRow('efek')" class="text-xs text-primary hover:underline">+
            Tambah Baris</button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-[#f8fafc]">
                <tr>
                    <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode</th>
                    <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Efek</th>
                    <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Sektor</th>
                    <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                    <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nilai Pasar</th>
                    <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kontribusi % IHSG</th>
                    <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 1M</th>
                    <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 3M</th>
                    <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 6M</th>
                    <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Return 1 Thn</th>
                    <th class="text-center px-2 py-2 text-xs font-semibold text-muted">Top 10</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                <template x-for="(row, i) in efek" :key="i">
                    <tr>
                        <td class="px-1 py-1"><input type="text" :name="`efek[${i}][kode_efek]`"
                                x-model="row.kode_efek" placeholder="BBCA"
                                class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                @change.debounce.500ms="lookupEfekData(i)" /></td>
                        <td class="px-1 py-1"><input type="text" :name="`efek[${i}][nama_efek]`"
                                x-model="row.nama_efek" placeholder="Nama Efek"
                                class="w-40 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                        <td class="px-1 py-1">
                            <input type="hidden" :name="`efek[${i}][effect_type]`" x-model="row.effect_type" />
                            <input type="text" :name="`efek[${i}][sektor]`" x-model="row.sektor"
                                placeholder="Sektor"
                                class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                        </td>
                        <td class="px-1 py-1"><input type="number" :name="`efek[${i}][bobot]`"
                                x-model="row.bobot" step="0.01"
                                class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                @input="hitungNilaiPasarEfek(i)" /></td>
                        <td class="px-1 py-1"><input type="number" :name="`efek[${i}][nilai_pasar]`"
                                x-model="row.nilai_pasar" step="0.01" readonly
                                class="w-28 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                        <td class="px-1 py-1"><input type="number" :name="`efek[${i}][kontribusi_kinerja]`"
                                x-model="row.kontribusi_kinerja" step="0.0001"
                                class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20"
                                @change="hitungTotalMarcap10" /></td>
                        <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_1m]`"
                                x-model="row.return_1m" step="0.0001" readonly
                                class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                        <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_3m]`"
                                x-model="row.return_3m" step="0.0001" readonly
                                class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                        <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_6m]`"
                                x-model="row.return_6m" step="0.0001" readonly
                                class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                        <td class="px-1 py-1"><input type="number" :name="`efek[${i}][return_1y]`"
                                x-model="row.return_1y" step="0.0001" readonly
                                class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 bg-gray-50 focus:border-primary focus:ring focus:ring-primary/20" /></td>
                        <td class="px-1 py-1 text-center"><input type="checkbox" :name="`efek[${i}][top_10]`"
                                x-model="row.top_10" value="1"
                                class="rounded border-gray-300 text-primary focus:ring-primary"
                                @change="hitungTotalMarcap10" /></td>
                        <td class="px-1 py-1"><button type="button" @click="removeRow('efek', i)"
                                class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
