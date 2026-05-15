@extends('layouts.admin')

@section('title', 'Klasifikasi Skor Kuis')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary">Klasifikasi Skor Kuis</h1>
        <p class="text-muted text-sm mt-1">Atur rentang skor dan alokasi portofolio untuk setiap profil investasi.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('alloc'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            {{ $errors->first('alloc') }}
        </div>
    @endif

    @php
        $profileColors = [
            'Conservative' => [
                'badge' => 'bg-blue-100 text-blue-700',
                'border' => 'border-blue-200',
                'header' => 'bg-blue-50',
            ],
            'Tolerant' => [
                'badge' => 'bg-green-100 text-green-700',
                'border' => 'border-green-200',
                'header' => 'bg-green-50',
            ],
            'Moderate' => [
                'badge' => 'bg-amber-100 text-amber-700',
                'border' => 'border-amber-200',
                'header' => 'bg-amber-50',
            ],
            'Risk Taker' => [
                'badge' => 'bg-red-100 text-red-700',
                'border' => 'border-red-200',
                'header' => 'bg-red-50',
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach ($classifications as $sc)
            @php $c = $profileColors[$sc->profile_name] ?? ['badge'=>'bg-gray-100 text-gray-700','border'=>'border-line','header'=>'bg-gray-50']; @endphp
            <div class="bg-white rounded-2xl border {{ $c['border'] }} shadow-sm overflow-hidden">
                {{-- Header --}}
                <div class="{{ $c['header'] }} px-5 py-4 border-b {{ $c['border'] }} flex items-center justify-between">
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $c['badge'] }} mb-1">
                            Profil {{ $loop->iteration }}
                        </span>
                        <h2 class="text-lg font-bold text-primary">{{ $sc->profile_name }}</h2>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-muted">Rentang Skor</div>
                        <div class="text-xl font-extrabold text-primary">{{ $sc->min_score }}–{{ $sc->max_score }}</div>
                    </div>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('admin.score-classifications.update', $sc) }}"
                    class="px-5 py-4 space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Rentang Skor --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Skor Minimum</label>
                            <input type="number" name="min_score" value="{{ old('min_score', $sc->min_score) }}"
                                min="1"
                                class="w-full px-3 py-2 text-sm border border-line rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('min_score') border-red-400 @enderror" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">Skor Maksimum</label>
                            <input type="number" name="max_score" value="{{ old('max_score', $sc->max_score) }}"
                                min="1"
                                class="w-full px-3 py-2 text-sm border border-line rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('max_score') border-red-400 @enderror" />
                        </div>
                    </div>

                    {{-- Alokasi Portofolio --}}
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-2">Alokasi Portofolio <span
                                class="font-normal">(total harus 100%)</span></label>
                        <div class="space-y-2">
                            @foreach ([['field' => 'alloc_pasar_uang', 'label' => 'Pasar Uang'], ['field' => 'alloc_pendapatan_tetap', 'label' => 'Pendapatan Tetap'], ['field' => 'alloc_campuran', 'label' => 'Campuran'], ['field' => 'alloc_saham', 'label' => 'Saham']] as $alloc)
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-primary w-36 shrink-0">{{ $alloc['label'] }}</span>
                                    <input type="number" name="{{ $alloc['field'] }}"
                                        value="{{ old($alloc['field'], $sc->{$alloc['field']}) }}" min="0"
                                        max="100"
                                        class="w-20 px-3 py-1.5 text-sm border border-line rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent text-center" />
                                    <span class="text-sm text-muted">%</span>
                                    {{-- Bar preview --}}
                                    <div class="flex-1 h-2 bg-line rounded-full overflow-hidden">
                                        <div class="h-full bg-accent/60 rounded-full"
                                            style="width: {{ $sc->{$alloc['field']} }}%"></div>
                                    </div>
                                    <span class="text-xs text-muted w-8 text-right">{{ $sc->{$alloc['field']} }}%</span>
                                </div>
                            @endforeach
                        </div>
                        {{-- Total --}}
                        <div class="mt-2 flex justify-end">
                            <span class="text-xs text-muted">Total saat ini:
                                <strong
                                    class="{{ $sc->alloc_pasar_uang + $sc->alloc_pendapatan_tetap + $sc->alloc_campuran + $sc->alloc_saham === 100 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $sc->alloc_pasar_uang + $sc->alloc_pendapatan_tetap + $sc->alloc_campuran + $sc->alloc_saham }}%
                                </strong>
                            </span>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    {{-- Info --}}
    <div class="mt-5 p-4 bg-[#f8fafc] border border-line rounded-xl text-xs text-muted">
        <strong class="text-primary">Catatan:</strong>
        Perubahan klasifikasi skor akan langsung mempengaruhi hasil kuis baru. Hasil kuis yang sudah tersimpan tidak akan
        berubah secara otomatis.
    </div>
@endsection
