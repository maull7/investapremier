@extends('layouts.user')

@section('title', ($alert->exists ? 'Edit' : 'Tambah') . ' Alert Harga - InvestaPremier')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-6">
            <h1 class="page-title">{{ $alert->exists ? 'Edit Alert Harga' : 'Tambah Alert Harga' }}</h1>
            <p class="page-sub">Pilih saham &amp; tentukan harga target. Sistem akan kirim notifikasi saat harga menyentuh.</p>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <ul class="list-disc list-inside text-xs">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ $alert->exists ? route('user.price-alerts.update', $alert) : route('user.price-alerts.store') }}"
              class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm space-y-5"
              x-data="{
                  stocks: @js($stocks->map(fn($s) => ['id' => $s->id, 'kode' => $s->kode, 'nama' => $s->nama, 'harga' => (float) $s->harga_terbaru])),
                  selectedId: '{{ old('stock_id', $alert->stock_id) }}',
                  get selected() { return this.stocks.find(s => String(s.id) === String(this.selectedId)) },
              }">
            @csrf
            @if ($alert->exists)
                @method('PUT')
            @endif

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Saham <span class="text-red-500">*</span></label>
                <select name="stock_id"
                        x-model="selectedId"
                        @change="$nextTick(() => { if (selected) $refs.kode.value = selected.kode })"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500">
                    <option value="">— pilih saham —</option>
                    @foreach ($stocks as $s)
                        <option value="{{ $s->id }}" {{ old('stock_id', $alert->stock_id) == $s->id ? 'selected' : '' }}>
                            {{ $s->kode }} — {{ $s->nama }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1" x-show="selected" x-cloak>
                    Harga terbaru: <span class="font-semibold text-gray-700" x-text="selected ? 'Rp ' + Number(selected.harga).toLocaleString('id-ID') : ''"></span>
                </p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kode Efek</label>
                <input type="text"
                       name="kode_efek"
                       x-ref="kode"
                       value="{{ old('kode_efek', $alert->kode_efek) }}"
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500"
                       placeholder="contoh: BBCA"
                       required>
                <p class="text-xs text-gray-400 mt-1">Otomatis terisi saat memilih saham. Bisa diketik manual jika kode tidak ada di daftar.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kondisi <span class="text-red-500">*</span></label>
                    <select name="condition"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500">
                        <option value="above" {{ old('condition', $alert->condition) === 'above' ? 'selected' : '' }}>Harga ≥ Target (naik mencapai)</option>
                        <option value="below" {{ old('condition', $alert->condition) === 'below' ? 'selected' : '' }}>Harga ≤ Target (turun mencapai)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Harga Target (Rp) <span class="text-red-500">*</span></label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="target_price"
                           value="{{ old('target_price', $alert->target_price) }}"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500"
                           placeholder="contoh: 9500"
                           required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catatan (opsional)</label>
                <input type="text"
                       name="note"
                       value="{{ old('note', $alert->note) }}"
                       maxlength="255"
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500"
                       placeholder="contoh: Target buyback untuk porto jangka panjang">
            </div>

            <div class="flex items-center gap-6">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $alert->is_active ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-500">
                    <span class="text-sm text-gray-700">Aktifkan alert</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="repeat" value="0">
                    <input type="checkbox"
                           name="repeat"
                           value="1"
                           {{ old('repeat', $alert->repeat ?? false) ? 'checked' : '' }}
                           class="w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-500">
                    <span class="text-sm text-gray-700">Trigger berulang (notifikasi tiap kali kondisi terpenuhi)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                <a href="{{ route('user.price-alerts.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $alert->exists ? 'Simpan Perubahan' : 'Buat Alert' }}
                </button>
            </div>
        </form>
    </div>
@endsection
