{{-- Informasi Obligasi --}}
<div class="bg-white rounded-xl border border-line p-6 space-y-4">
    <h3 class="font-semibold text-primary">Informasi Obligasi</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Obligasi</label>
            <input type="text" name="info_nama_obligasi" value="{{ old('info_nama_obligasi') }}" x-model="info_nama_obligasi"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">YTM (%)</label>
            <input type="number" name="info_ytm" value="{{ old('info_ytm') }}" x-model="info_ytm"
                step="0.0001"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
            <input type="number" name="harga_obligasi" value="{{ old('harga_obligasi') }}" x-model="harga_obligasi"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Q1</label>
            <input type="number" name="q1_obligasi" value="{{ old('q1_obligasi') }}" x-model="q1_obligasi"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Q2</label>
            <input type="number" name="q2_obligasi" value="{{ old('q2_obligasi') }}" x-model="q2_obligasi"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Q3</label>
            <input type="number" name="q3_obligasi" value="{{ old('q3_obligasi') }}" x-model="q3_obligasi"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Q4</label>
            <input type="number" name="q4_obligasi" value="{{ old('q4_obligasi') }}" x-model="q4_obligasi"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Penerbitan</label>
            <input type="number" name="info_nominal_penerbitan" value="{{ old('info_nominal_penerbitan') }}" x-model="info_nominal_penerbitan"
                step="0.01"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
    </div>
</div>
