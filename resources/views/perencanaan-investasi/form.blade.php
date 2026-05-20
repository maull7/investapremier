@extends('layouts.user')

@section('title', isset($plan) ? 'Edit Rencana Investasi' : 'Buat Rencana Investasi')

@php
    $predefinedCategories = ['Pendidikan Anak', 'Dana Pensiun', 'Pembelian Rumah', 'Dana Darurat', 'Liburan / Haji / Umroh', 'Investasi Reguler', 'Lainnya'];
    $currentCategory = old('kategori_perencanaan', $plan->kategori_perencanaan ?? '');
    $isCustomCategory = $currentCategory && !in_array($currentCategory, $predefinedCategories);
@endphp

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-muted mb-3">
            <a href="{{ route('user.perencanaan-investasi.index') }}" class="hover:text-primary transition">Perencanaan Investasi</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-primary font-medium">{{ isset($plan) ? 'Edit Rencana' : 'Buat Rencana Baru' }}</span>
        </div>
        <h1 class="text-2xl font-bold text-primary">{{ isset($plan) ? 'Edit Rencana Investasi' : 'Buat Rencana Investasi Baru' }}</h1>
        <p class="text-muted text-sm mt-1">Isi data di bawah untuk memulai perencanaan. Setelah disimpan, AI akan menganalisis dan memberikan rekomendasi strategi.</p>
    </div>

    <form method="POST"
          action="{{ isset($plan) ? route('user.perencanaan-investasi.update', $plan) : route('user.perencanaan-investasi.store') }}"
          class="max-w-4xl" x-data="kategoriSelect()" x-on:submit.prevent="beforeSubmit($event)">
        @csrf
        @if (isset($plan)) @method('PUT') @endif

        <div class="space-y-6">

            {{-- Kategori Perencanaan --}}
            <div class="bg-white rounded-2xl border border-line shadow-sm p-6">
                <h3 class="font-bold text-primary text-sm mb-3">Kategori Perencanaan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Kategori" class="text-sm font-semibold mb-1.5" />
                        <select x-ref="kategoriSelect" name="kategori_perencanaan"
                                x-model="selected"
                                x-on:change="onChange(selected)"
                                class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('kategori_perencanaan') border-red-400 @enderror">
                            <option value="">Pilih kategori...</option>
                            @foreach ($predefinedCategories as $cat)
                                <option value="{{ $cat }}" {{ !$isCustomCategory && $currentCategory == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('kategori_perencanaan')" class="mt-1 text-xs" />
                    </div>
                    <div x-show="showCustom">
                        <x-input-label value="Kategori Lainnya" class="text-sm font-semibold mb-1.5" />
                        <input type="text" name="kategori_custom" x-model="customVal"
                               class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                               placeholder="Tulis kategori Anda..."
                               value="{{ $isCustomCategory ? $currentCategory : '' }}">
                    </div>
                </div>
            </div>

            {{-- Informasi Keuangan --}}
            <div class="bg-white rounded-2xl border border-line shadow-sm p-6">
                <h3 class="font-bold text-primary text-sm mb-3">Informasi Keuangan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Kebutuhan Dana (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="kebutuhan_dana"
                               class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('kebutuhan_dana') border-red-400 @enderror"
                               placeholder="500000000"
                               value="{{ old('kebutuhan_dana', $plan->kebutuhan_dana ?? '') }}">
                        <x-input-error :messages="$errors->get('kebutuhan_dana')" class="mt-1 text-xs" />
                    </div>
                    <div>
                        <x-input-label value="Target Waktu (tahun)" class="text-sm font-semibold mb-1.5" />
                        <input type="number" name="target_waktu_tahun" min="1" max="100"
                               class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                               placeholder="10"
                               value="{{ old('target_waktu_tahun', $plan->target_waktu_tahun ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Dana Tersedia Saat Ini (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="dana_tersedia"
                               class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                               placeholder="100000000"
                               value="{{ old('dana_tersedia', $plan->dana_tersedia ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Rencana Investasi per Bulan (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="investasi_per_bulan"
                               class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                               placeholder="5000000"
                               value="{{ old('investasi_per_bulan', $plan->investasi_per_bulan ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Sumber Dana" class="text-sm font-semibold mb-1.5" />
                        <select name="sumber_dana"
                                class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            <option value="">Pilih sumber dana...</option>
                            <option value="Gaji" {{ old('sumber_dana', $plan->sumber_dana ?? '') == 'Gaji' ? 'selected' : '' }}>Gaji</option>
                            <option value="Tabungan" {{ old('sumber_dana', $plan->sumber_dana ?? '') == 'Tabungan' ? 'selected' : '' }}>Tabungan</option>
                            <option value="Investasi" {{ old('sumber_dana', $plan->sumber_dana ?? '') == 'Investasi' ? 'selected' : '' }}>Investasi</option>
                            <option value="Warisan" {{ old('sumber_dana', $plan->sumber_dana ?? '') == 'Warisan' ? 'selected' : '' }}>Warisan</option>
                            <option value="Pendapatan Lain" {{ old('sumber_dana', $plan->sumber_dana ?? '') == 'Pendapatan Lain' ? 'selected' : '' }}>Pendapatan Lain</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Profil Risiko" class="text-sm font-semibold mb-1.5" />
                        <select name="profil_risiko"
                                class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            <option value="">Pilih profil risiko...</option>
                            <option value="Konservatif" {{ old('profil_risiko', $plan->profil_risiko ?? '') == 'Konservatif' ? 'selected' : '' }}>Konservatif</option>
                            <option value="Moderat" {{ old('profil_risiko', $plan->profil_risiko ?? '') == 'Moderat' ? 'selected' : '' }}>Moderat</option>
                            <option value="Agresif" {{ old('profil_risiko', $plan->profil_risiko ?? '') == 'Agresif' ? 'selected' : '' }}>Agresif</option>
                            <option value="Sangat Agresif" {{ old('profil_risiko', $plan->profil_risiko ?? '') == 'Sangat Agresif' ? 'selected' : '' }}>Sangat Agresif</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Data Pendidikan Anak --}}
            <div class="bg-white rounded-2xl border border-line shadow-sm p-6"
                 x-data="{ showPendidikan: '{{ $currentCategory }}' === 'Pendidikan Anak' }"
                 x-show="showPendidikan">
                <h3 class="font-bold text-primary text-sm mb-3">Data Pendidikan Anak</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Usia Anak" class="text-sm font-semibold mb-1.5" />
                        <input type="text" name="usia_anak"
                               class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                               placeholder="5 tahun"
                               value="{{ old('usia_anak', $plan->usia_anak ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Target Pendidikan" class="text-sm font-semibold mb-1.5" />
                        <select name="target_pendidikan"
                                class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            <option value="">Pilih target...</option>
                            <option value="TK" {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'TK' ? 'selected' : '' }}>TK</option>
                            <option value="SD" {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'SD' ? 'selected' : '' }}>SD</option>
                            <option value="SMP" {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'SMP' ? 'selected' : '' }}>SMP</option>
                            <option value="SMA" {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'SMA' ? 'selected' : '' }}>SMA</option>
                            <option value="S1" {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'S1' ? 'selected' : '' }}>S1</option>
                            <option value="S2" {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'S2' ? 'selected' : '' }}>S2</option>
                            <option value="S3" {{ old('target_pendidikan', $plan->target_pendidikan ?? '') == 'S3' ? 'selected' : '' }}>S3</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Tipe Pendidikan" class="text-sm font-semibold mb-1.5" />
                        <select name="tipe_pendidikan"
                                class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            <option value="">Pilih tipe...</option>
                            <option value="Negeri" {{ old('tipe_pendidikan', $plan->tipe_pendidikan ?? '') == 'Negeri' ? 'selected' : '' }}>Negeri</option>
                            <option value="Swasta" {{ old('tipe_pendidikan', $plan->tipe_pendidikan ?? '') == 'Swasta' ? 'selected' : '' }}>Swasta</option>
                            <option value="Internasional" {{ old('tipe_pendidikan', $plan->tipe_pendidikan ?? '') == 'Internasional' ? 'selected' : '' }}>Internasional</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Lokasi Pendidikan" class="text-sm font-semibold mb-1.5" />
                        <select name="lokasi_pendidikan"
                                class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            <option value="">Pilih lokasi...</option>
                            <option value="Dalam Negeri" {{ old('lokasi_pendidikan', $plan->lokasi_pendidikan ?? '') == 'Dalam Negeri' ? 'selected' : '' }}>Dalam Negeri</option>
                            <option value="Luar Negeri" {{ old('lokasi_pendidikan', $plan->lokasi_pendidikan ?? '') == 'Luar Negeri' ? 'selected' : '' }}>Luar Negeri</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Estimasi Biaya Saat Ini (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="estimasi_biaya_saat_ini"
                               class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                               placeholder="25000000"
                               value="{{ old('estimasi_biaya_saat_ini', $plan->estimasi_biaya_saat_ini ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Pemenuhan Dana Saat Ini (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="pemenuhan_dana"
                               class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                               placeholder="5000000"
                               value="{{ old('pemenuhan_dana', $plan->pemenuhan_dana ?? '') }}">
                    </div>
                </div>
            </div>

        </div>

        <div class="flex items-center gap-3 mt-5">
            <button type="submit"
                    class="px-5 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
                {{ isset($plan) ? 'Simpan Perubahan' : 'Simpan & Analisis' }}
            </button>
            <a href="{{ route('user.perencanaan-investasi.index') }}"
               class="px-5 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
                Batal
            </a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('kategoriSelect', () => ({
            selected: '{{ $isCustomCategory ? 'Lainnya' : $currentCategory }}',
            customVal: '{{ $isCustomCategory ? addslashes($currentCategory) : '' }}',
            showCustom: {{ $isCustomCategory ? 'true' : 'false' }},
            init() {
                this.showCustom = this.selected === 'Lainnya' || {{ $isCustomCategory ? 'true' : 'false' }};
            },
            onChange(val) {
                this.showCustom = val === 'Lainnya';
                if (val !== 'Lainnya') {
                    this.customVal = '';
                }
            },
            beforeSubmit(evt) {
                if (this.selected === 'Lainnya' && this.customVal.trim()) {
                    this.$refs.kategoriSelect.value = this.customVal.trim();
                }
                evt.target.submit();
            }
        }));
    });
</script>
@endpush
