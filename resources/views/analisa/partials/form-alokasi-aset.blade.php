<div>
    <div class="flex items-center justify-between mb-3">
        <h4 class="font-semibold text-primary text-sm">Alokasi Aset / % Portfolio</h4>
        <div class="flex items-center gap-3">
            <span class="text-xs" :class="alokasiAsetTotalValid() ? 'text-emerald-600' : 'text-red-600'">
                Total: <span x-text="alokasiAsetTotal().toFixed(2)"></span>%
            </span>
            <button type="button" @click="addRow('alokasi_aset')" class="text-xs text-primary hover:underline">+
                Tambah Baris</button>
        </div>
    </div>
    <div class="space-y-2">
        <template x-for="(row, i) in alokasi_aset" :key="i">
            <div class="flex gap-2 items-center">
                <input type="text" :name="`alokasi_aset[${i}][nama_aset]`" x-model="row.nama_aset"
                    placeholder="Nama aset"
                    class="flex-1 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                <input type="number" :name="`alokasi_aset[${i}][persentase]`" x-model="row.persentase"
                    placeholder="%" step="0.01"
                    class="w-28 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                <button type="button" @click="removeRow('alokasi_aset', i)"
                    class="text-red-400 hover:text-red-600 px-1">✕</button>
            </div>
        </template>
    </div>
    <x-input-error :messages="$errors->get('alokasi_aset')" class="mt-2" />
</div>
