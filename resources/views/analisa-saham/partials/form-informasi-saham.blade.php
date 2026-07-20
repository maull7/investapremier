{{-- Informasi Saham --}}
<div class="bg-white rounded-xl border border-line p-6 space-y-4">
    <h3 class="font-semibold text-primary">Informasi Saham</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Saham</label>
            <input type="text" name="nama_saham" value="{{ old('nama_saham') }}" x-model="nama_saham"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Lembar Saham</label>
            <input type="number" name="jumlah_lembar_saham" value="{{ old('jumlah_lembar_saham') }}" x-model="jumlah_lembar_saham"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
            <input type="number" name="harga_saham" value="{{ old('harga_saham') }}" x-model="harga_saham"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Q1</label>
            <input type="number" name="q1_saham" value="{{ old('q1_saham') }}" x-model="q1_saham"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Q2</label>
            <input type="number" name="q2_saham" value="{{ old('q2_saham') }}" x-model="q2_saham"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Q3</label>
            <input type="number" name="q3_saham" value="{{ old('q3_saham') }}" x-model="q3_saham"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Q4</label>
            <input type="number" name="q4_saham" value="{{ old('q4_saham') }}" x-model="q4_saham"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kapitalisasi Pasar</label>
            <input type="number" name="kapitalisasi_pasar" value="{{ old('kapitalisasi_pasar') }}" x-model="kapitalisasi_pasar"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
    </div>
</div>
