@extends('layouts.admin')

@section('title', 'Ekstrak Data dengan AI - InvestaPremier')

@section('content')
@php
    $typeLabel = $type === 'obligasi' ? 'Obligasi' : 'Saham';
    $typeLabelPlural = $type === 'obligasi' ? 'obligasi/sukuk' : 'saham';
    $otherType = $type === 'obligasi' ? 'saham' : 'obligasi';
    $otherTypeLabel = $type === 'obligasi' ? 'Saham' : 'Obligasi';
    $paginatedItems = $paginated->items();
    $paginatedTotal = $paginated->total();
@endphp

<div x-data="{
    selectAll: false,
    selected: [],
    toggleAll() {
        this.selectAll = !this.selectAll;
        this.selected = this.selectAll ? @js(array_keys($paginatedItems)).map(String) : [];
    },
    toggleItem(idx) {
        if (this.selected.includes(String(idx))) {
            this.selected = this.selected.filter(i => i !== String(idx));
        } else {
            this.selected.push(String(idx));
        }
        this.selectAll = this.selected.length === @js(count($paginatedItems));
    }
}">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="page-title">Ekstrak {{ $typeLabel }} dengan AI</h1>
            <p class="page-sub">Masukkan URL website, AI akan membaca dan mengekstrak data {{ $typeLabelPlural }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.' . ($type === 'obligasi' ? 'obligasi' : 'saham') . '.index') }}"
                class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Daftar {{ $typeLabel }}
            </a>
        </div>
    </div>

    {{-- Type Switcher --}}
    <div class="mb-5">
        <div class="flex items-center gap-1 bg-[#f1f5f9] rounded-xl p-1 w-fit">
            <a href="{{ route('admin.idx-ai-extraction.index', ['type' => 'saham']) }}"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $type === 'saham' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
                Saham
            </a>
            <a href="{{ route('admin.idx-ai-extraction.index', ['type' => 'obligasi']) }}"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $type === 'obligasi' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
                Obligasi
            </a>
        </div>
    </div>

    @php
        $successMsg = $success ?? session('success');
    @endphp
    @if ($successMsg)
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $successMsg }}
        </div>
    @endif

    @php
        $errorMsg = $error ?? session('error');
    @endphp
    @if ($errorMsg)
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $errorMsg }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        {{-- Left: Main Panel --}}
        <div class="space-y-5">
            {{-- Extract Form --}}
            <div class="table-card">
                <div class="table-head">
                    <h2 class="th-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Ekstrak dari Website
                    </h2>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('admin.idx-ai-extraction.extract') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">

                        <div>
                            <label class="block text-sm font-semibold text-primary mb-1.5">URL Website</label>
                            <input type="url" name="url" value="{{ old('url', $lastUrl) }}" required
                                class="w-full border border-line rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30"
                                placeholder="https://www.idx.co.id/id/data-pasar/data-saham/daftar-saham">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary mb-1.5">
                                Atau paste konten HTML/JSON dari halaman
                                <span class="text-muted font-normal">(opsional — jika akses langsung terblokir)</span>
                            </label>
                            <textarea name="raw_content" rows="5"
                                class="w-full border border-line rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 font-mono"
                                placeholder="Buka halaman di browser, Inspect Element → copy HTML/JSON, paste di sini...">{{ old('raw_content') }}</textarea>
                        </div>

                        @if ($type === 'saham')
                            <div>
                                <label class="block text-sm font-semibold text-primary mb-1.5">
                                    URL Harga (opsional)
                                    <span class="text-muted font-normal">— ambil data harga/volume dari sini</span>
                                </label>
                                <input type="url" name="merge_url" value="{{ old('merge_url', $lastMergeUrl ?? '') }}"
                                    class="w-full border border-line rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30"
                                    placeholder="https://www.idx.co.id/id/data-pasar/ringkasan-perdagangan/ringkasan-saham">
                            </div>
                        @endif

                        <div class="flex items-center gap-3">
                            <button type="submit" class="btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Ekstrak dengan AI
                            </button>
                            <p class="text-xs text-muted">AI akan membaca konten web dan mengekstrak data {{ $typeLabelPlural }}</p>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Results --}}
            @if ($paginatedTotal > 0)
                <div class="table-card">
                    <div class="table-head">
                        <h2 class="th-title">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Hasil Ekstrak ({{ $paginatedTotal }} {{ $typeLabelPlural }})
                        </h2>
                    </div>

                    <div class="overflow-x-auto max-h-[600px]">
                        <form method="POST" action="{{ route('admin.idx-ai-extraction.save') }}" id="saveForm">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">
                            <input type="hidden" name="token" value="{{ $token ?? request('token') }}">

                            <table class="w-full text-sm">
                                @if ($type === 'obligasi')
                                    <thead>
                                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                                            <th class="px-4 py-3 w-10"><input type="checkbox" @change="toggleAll()" :checked="selectAll" class="rounded border-line text-accent focus:ring-accent/30"></th>
                                            <th class="px-4 py-3 font-semibold">Kode</th>
                                            <th class="px-4 py-3 font-semibold">Nama Obligasi</th>
                                            <th class="px-4 py-3 font-semibold">Emiten</th>
                                            <th class="px-4 py-3 font-semibold">Rating</th>
                                            <th class="px-4 py-3 font-semibold text-right">Kupon</th>
                                            <th class="px-4 py-3 font-semibold">Jatuh Tempo</th>
                                            <th class="px-4 py-3 font-semibold text-right">YTM</th>
                                            <th class="px-4 py-3 font-semibold text-center">Syariah</th>
                                            <th class="px-4 py-3 font-semibold text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        @foreach ($paginated as $idx => $item)
                                            @php
                                                $kode = strtoupper(trim($item['kode'] ?? ''));
                                                $exists = $kode ? isset($existingCodes[$kode]) : false;
                                            @endphp
                                            <tr class="hover:bg-[#f8fafc] transition-colors">
                                                <td class="px-4 py-2.5"><input type="checkbox" name="selected[]" value="{{ $idx }}" x-model="selected" class="rounded border-line text-accent focus:ring-accent/30"></td>
                                                <td class="px-4 py-2.5 font-semibold text-primary">{{ $kode ?: '?' }}</td>
                                                <td class="px-4 py-2.5 max-w-[180px] truncate" title="{{ $item['nama'] ?? '' }}">{{ $item['nama'] ?? '-' }}</td>
                                                <td class="px-4 py-2.5 text-muted">{{ $item['emiten'] ?? '-' }}</td>
                                                <td class="px-4 py-2.5">
                                                    @php
                                                        $rating = $item['rating'] ?? null;
                                                    @endphp
                                                    @if ($rating)
                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">{{ $rating }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2.5 text-right">{{ $item['kupon'] ?? '-' }}@if(!empty($item['kupon']))% @endif</td>
                                                <td class="px-4 py-2.5 text-muted whitespace-nowrap">
                                                    @php
                                                        $jt = $item['jatuh_tempo'] ?? null;
                                                    @endphp
                                                    {{ $jt ? \Carbon\Carbon::parse($jt)->format('d/m/Y') : '-' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-right">{{ $item['ytm'] ?? '-' }}@if(!empty($item['ytm']))% @endif</td>
                                                <td class="px-4 py-2.5 text-center">
                                                    @if (!empty($item['syariah']) && filter_var($item['syariah'], FILTER_VALIDATE_BOOLEAN))
                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Sukuk</span>
                                                    @else
                                                        <span class="text-muted text-[10px]">Konvensional</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2.5 text-right">
                                                    @if ($exists)
                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Sudah Ada</span>
                                                    @else
                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200">Baru</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                @else
                                    <thead>
                                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                                            <th class="px-4 py-3 w-10"><input type="checkbox" @change="toggleAll()" :checked="selectAll" class="rounded border-line text-accent focus:ring-accent/30"></th>
                                            <th class="px-4 py-3 font-semibold">Kode</th>
                                            <th class="px-4 py-3 font-semibold">Nama</th>
                                            <th class="px-4 py-3 font-semibold">Sektor</th>
                                            <th class="px-4 py-3 font-semibold">Sub Industri</th>
                                            <th class="px-4 py-3 font-semibold text-right">Harga</th>
                                            <th class="px-4 py-3 font-semibold text-right">Perubahan</th>
                                            <th class="px-4 py-3 font-semibold text-right">Volume</th>
                                            <th class="px-4 py-3 font-semibold text-right">Nilai</th>
                                            <th class="px-4 py-3 font-semibold text-right">Frekuensi</th>
                                            <th class="px-4 py-3 font-semibold text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        @foreach ($paginated as $idx => $item)
                                            @php
                                                $kode = strtoupper(trim($item['kode'] ?? ''));
                                                $exists = $kode ? isset($existingCodes[$kode]) : false;
                                                $harga = $item['harga_terbaru'] ?? null;
                                                $perubahan = $item['perubahan_persen'] ?? null;
                                                $volume = $item['volume'] ?? null;
                                                $nilai = $item['nilai'] ?? null;
                                                $frekuensi = $item['frekuensi'] ?? null;
                                            @endphp
                                            <tr class="hover:bg-[#f8fafc] transition-colors">
                                                <td class="px-4 py-2.5"><input type="checkbox" name="selected[]" value="{{ $idx }}" x-model="selected" class="rounded border-line text-accent focus:ring-accent/30"></td>
                                                <td class="px-4 py-2.5 font-semibold text-primary">{{ $kode ?: '?' }}</td>
                                                <td class="px-4 py-2.5 max-w-[180px] truncate" title="{{ $item['nama'] ?? '' }}">{{ $item['nama'] ?? '-' }}</td>
                                                <td class="px-4 py-2.5 text-muted">{{ $item['sektor'] ?? '-' }}</td>
                                                <td class="px-4 py-2.5 text-muted">{{ $item['sub_industri'] ?? '-' }}</td>
                                                <td class="px-4 py-2.5 text-right">{{ $harga !== null ? 'Rp ' . number_format((float) $harga, 0, ',', '.') : '-' }}</td>
                                                <td class="px-4 py-2.5 text-right">
                                                    @if ($perubahan !== null && $perubahan !== '')
                                                        @php
                                                            $changeVal = is_numeric($perubahan) ? (float) $perubahan : 0;
                                                        @endphp
                                                        <span class="{{ $changeVal >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                            {{ $changeVal >= 0 ? '+' : '' }}{{ number_format($changeVal, 2) }}%
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2.5 text-right">{{ $volume !== null ? number_format((float) $volume, 0, ',', '.') : '-' }}</td>
                                                <td class="px-4 py-2.5 text-right">{{ $nilai !== null ? 'Rp ' . number_format((float) $nilai, 0, ',', '.') : '-' }}</td>
                                                <td class="px-4 py-2.5 text-right">{{ $frekuensi !== null ? number_format((float) $frekuensi, 0, ',', '.') : '-' }}</td>
                                                <td class="px-4 py-2.5 text-right">
                                                    @if ($exists)
                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Sudah Ada</span>
                                                    @else
                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200">Baru</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                @endif
                            </table>
                        </form>
                    </div>

                    <div class="p-4 border-t border-line flex items-center justify-between">
                        <p class="text-sm text-muted">
                            <span x-text="selected.length"></span> dari {{ $paginatedTotal }} dipilih
                        </p>
                        <button type="submit" form="saveForm"
                            class="btn-primary"
                            :disabled="selected.length === 0"
                            :class="selected.length === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Simpan ke Database
                        </button>
                    </div>

                    @if ($paginated->hasPages())
                        <div class="p-4 border-t border-line">
                            {{ $paginated->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Right: Info Panel --}}
        <div class="space-y-4">
            <div class="table-card">
                <div class="table-head">
                    <h2 class="th-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Informasi
                    </h2>
                </div>
                <div class="p-5 space-y-3 text-sm">
                    <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-xl">
                        <svg class="w-5 h-5 shrink-0 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-semibold text-blue-800">Cara Penggunaan</p>
                            <ol class="mt-2 text-blue-700 space-y-1 list-decimal list-inside">
                                <li>Pilih jenis data: Saham atau Obligasi</li>
                                <li>Masukkan URL website yang berisi tabel data</li>
                                <li>Untuk Saham: isi URL Harga untuk ambil data harga/volume secara otomatis</li>
                                <li>Klik "Ekstrak dengan AI" untuk coba ambil data langsung</li>
                                <li>Jika terblokir, buka halaman di browser, copy HTML/JSON, paste di kolom</li>
                                <li>Centang data yang mau disimpan, klik "Simpan ke Database"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-yellow-50 rounded-xl">
                        <svg class="w-5 h-5 shrink-0 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <p class="font-semibold text-yellow-800">Catatan</p>
                            <p class="mt-1 text-yellow-700">Beberapa website menggunakan Cloudflare yang memblokir akses server. Jika akses langsung gagal, paste konten HTML/JSON secara manual dari browser.</p>
                        </div>
                    </div>

                    @if (($fetchResult['fetch_info']['blocked'] ?? false) && empty($results))
                        <div class="flex items-start gap-3 p-3 bg-red-50 rounded-xl">
                            <svg class="w-5 h-5 shrink-0 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01" />
                            </svg>
                            <div>
                                <p class="font-semibold text-red-800">Akses Langsung Terblokir</p>
                                <p class="mt-1 text-red-700">Website memblokir akses server. Buka halaman di browser manual, copy tabel/JSON, dan paste di kolom "Konten HTML/JSON".</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if (!empty($mergeStats))
                @php
                    $primaryCount = (int) ($mergeStats['primary_count'] ?? 0);
                    $secondaryCount = (int) ($mergeStats['secondary_count'] ?? 0);
                    $matchedCount = (int) ($mergeStats['matched_count'] ?? 0);
                    $filledPrice = (int) ($mergeStats['filled_price_count'] ?? 0);
                    $matchRate = (float) ($mergeStats['match_rate'] ?? 0);
                    $matchRatePct = round($matchRate * 100, 1);
                    $mergeError = $mergeStats['error'] ?? null;
                    $unmatchedPrimary = $mergeStats['unmatched_primary_sample'] ?? [];
                    $unmatchedSecondary = $mergeStats['unmatched_secondary_sample'] ?? [];
                    $statusColor = $mergeError ? 'red' : ($matchRate >= 0.8 ? 'green' : ($matchRate >= 0.5 ? 'yellow' : 'red'));
                    $bgClass = ['red' => 'bg-red-50 border-red-200', 'yellow' => 'bg-yellow-50 border-yellow-200', 'green' => 'bg-green-50 border-green-200'][$statusColor];
                    $textClass = ['red' => 'text-red-800', 'yellow' => 'text-yellow-800', 'green' => 'text-green-800'][$statusColor];
                    $subTextClass = ['red' => 'text-red-700', 'yellow' => 'text-yellow-700', 'green' => 'text-green-700'][$statusColor];
                @endphp
                <div class="table-card">
                    <div class="table-head">
                        <h2 class="th-title">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Hasil Merge URL Harga
                        </h2>
                    </div>
                    <div class="p-5 space-y-3 text-sm">
                        <div class="p-3 rounded-xl border {{ $bgClass }}">
                            @if ($mergeError)
                                <p class="font-semibold {{ $textClass }}">URL Harga gagal diambil</p>
                                <p class="mt-1 {{ $subTextClass }} text-xs break-words">{{ $mergeError }}</p>
                            @else
                                <p class="font-semibold {{ $textClass }}">
                                    {{ $filledPrice }} / {{ $primaryCount }} saham terisi harga
                                    <span class="text-xs font-normal">({{ $matchRatePct }}% matched)</span>
                                </p>
                                <p class="mt-1 {{ $subTextClass }} text-xs">
                                    Master: {{ number_format($primaryCount, 0, ',', '.') }} saham ·
                                    URL Harga: {{ number_format($secondaryCount, 0, ',', '.') }} baris ·
                                    Matched: {{ number_format($matchedCount, 0, ',', '.') }}
                                </p>
                            @endif
                        </div>

                        @if (!$mergeError && $matchRate < 0.8 && $primaryCount > 0)
                            <div class="flex items-start gap-3 p-3 bg-yellow-50 rounded-xl">
                                <svg class="w-5 h-5 shrink-0 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <div>
                                    <p class="font-semibold text-yellow-800">Match rate rendah ({{ $matchRatePct }}%)</p>
                                    <p class="mt-1 text-yellow-700 text-xs">Beberapa kode di master tidak ditemukan di URL Harga. Periksa apakah kedua URL berasal dari halaman yang sebanding.</p>
                                </div>
                            </div>
                        @endif

                        @if (!empty($unmatchedPrimary))
                            <div>
                                <p class="text-xs font-semibold text-muted mb-1">Sample kode master tanpa harga ({{ count($unmatchedPrimary) }}+)</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($unmatchedPrimary as $kodeUnm)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-red-50 text-red-700 border border-red-200">{{ $kodeUnm }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (!empty($unmatchedSecondary))
                            <div>
                                <p class="text-xs font-semibold text-muted mb-1">Sample kode di URL Harga tapi tidak di master ({{ count($unmatchedSecondary) }}+)</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($unmatchedSecondary as $kodeUnm)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-blue-50 text-blue-700 border border-blue-200">{{ $kodeUnm }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="table-card">
                <div class="table-head">
                    <h2 class="th-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        Contoh URL
                    </h2>
                </div>
                <div class="p-5 space-y-2 text-sm">
                    <p class="font-semibold text-primary">Saham:</p>
                    <a href="https://www.idx.co.id/id/data-pasar/data-saham/daftar-saham" target="_blank" class="block text-accent hover:underline text-xs truncate">https://www.idx.co.id/id/data-pasar/data-saham/daftar-saham</a>
                    <a href="https://www.idx.co.id/id/data-pasar/ringkasan-perdagangan/ringkasan-saham" target="_blank" class="block text-accent hover:underline text-xs truncate">https://www.idx.co.id/id/data-pasar/ringkasan-perdagangan/ringkasan-saham <span class="text-muted">(harga)</span></a>
                    <hr class="border-line my-2">
                    <p class="font-semibold text-primary">Obligasi:</p>
                    <a href="https://www.idx.co.id/id/data-pasar/obligasi-sukuk/obligasi-sukuk-korporasi/" target="_blank" class="block text-accent hover:underline text-xs truncate">https://www.idx.co.id/id/data-pasar/obligasi-sukuk/obligasi-sukuk-korporasi/</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
