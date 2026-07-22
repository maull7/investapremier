@extends('layouts.user')

@section('title', 'Daftar Reksa Dana')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-accent-teal/85">Daftar Reksa Dana</h1>
        <p class="page-sub">Informasi dan harga reksa dana yang tersedia</p>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-5">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('user.reksa-dana.index') }}" class="mb-5 space-y-3">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-56">
                <label class="block text-xs font-semibold text-muted mb-1">Cari Reksa Dana</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama atau kode reksa dana..."
                    class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
            </div>
            <button type="submit"
                class="px-4 py-2 bg-accent-teal/85 text-white rounded-lg text-sm font-semibold hover:bg-primary/90 transition">Cari</button>
            @if (request('search') || request('jenis') || request('kategori'))
                <a href="{{ route('user.reksa-dana.index') }}"
                    class="px-4 py-2 border border-line text-muted rounded-lg text-sm font-semibold hover:text-primary transition">Reset</a>
            @endif
        </div>

        <div class="bg-white border border-slate-200 rounded-xl px-4 py-3 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">

                {{-- Label --}}
                <div class="w-full lg:w-28 shrink-0 border-r border-collapse border-slate-200 pr-4">
                    <h3 class="text-sm font-semibold text-muted-80">
                        Jenis
                    </h3>
                    <p class="text-xs text-muted/80 mt-0.5">
                        Pilih kategori reksadana
                    </p>
                </div>

                {{-- Filter --}}
                <div class="flex-1 flex flex-wrap gap-2">

                    <a href="{{ route('user.reksa-dana.index', array_merge(request()->except('jenis'), ['search' => request('search')])) }}"
                        class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-all duration-200
                {{ !request('jenis')
                    ? 'bg-accent text-white shadow-md'
                    : 'bg-white border border-slate-300 text-slate-600 hover:bg-accent/10 hover:border-slate-700' }}">
                        Semua
                    </a>

                    @foreach ($jenisOptions as $j)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="jenis[]" value="{{ $j }}"
                                {{ in_array($j, (array) request('jenis')) ? 'checked' : '' }} class="peer hidden"
                                onchange="this.closest('form').submit();">

                            <span
                                class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium border transition-all duration-200
                        border-slate-300 text-slate-600
                        hover:bg-accent/10 hover:border-slate-700
                        peer-checked:bg-accent
                        peer-checked:border-slate-700
                        peer-checked:text-white
                        peer-checked:shadow-md">
                                {{ $j }}
                            </span>
                        </label>
                    @endforeach

                </div>

            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4">
            <div class="flex flex-col gap-3">

                <div class="flex items-center justify-between border-b border-collapse border-slate-200 pb-2">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">
                            Kategori
                        </h3>
                        <p class="text-xs text-slate-500">
                            Pilih satu atau beberapa kategori
                        </p>
                    </div>

                    <span class="text-xs text-slate-400">
                        {{ count((array) request('kategori')) }} dipilih
                    </span>
                </div>

                <div class="flex flex-wrap gap-3">

                    <a href="{{ route('user.reksa-dana.index', array_merge(request()->except('kategori'), ['search' => request('search')])) }}"
                        class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-medium transition-all duration-200
                {{ !request('kategori')
                    ? 'bg-accent text-white border-accent shadow'
                    : 'bg-white border-slate-300 text-slate-600 hover:border-accent hover:text-accent' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M12 3v18" />
                        </svg>
                        Semua
                    </a>

                    @foreach ($kategoriOptions as $k)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="kategori[]" value="{{ $k }}"
                                {{ in_array($k, (array) request('kategori')) ? 'checked' : '' }} class="peer hidden"
                                onchange="this.closest('form').submit();">

                            <div
                                class="flex items-center gap-2 rounded-xl border bg-white px-4 py-2
                               border-slate-300 text-slate-700
                               transition-all duration-200
                               hover:border-accent hover:-translate-y-0.5 hover:shadow-sm
                               peer-checked:bg-accent
                               peer-checked:border-accent
                               peer-checked:text-white
                               peer-checked:shadow-md">

                                <div
                                    class="w-2 h-2 rounded-full bg-slate-300
                                   peer-checked:bg-white">
                                </div>

                                <span class="text-sm font-medium">
                                    {{ $k }}
                                </span>

                            </div>
                        </label>
                    @endforeach

                </div>

            </div>
        </div>
    </form>

    <div class="table-card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide border-b border-line">
                        <th class="px-4 py-3.5 font-semibold">Kode</th>
                        <th class="px-4 py-3.5 font-semibold">Nama Reksa Dana</th>
                        <th class="px-4 py-3.5 font-semibold">Manajer Investasi</th>
                        <th class="px-4 py-3.5 font-semibold">Jenis</th>
                        <th class="px-4 py-3.5 font-semibold">Kategori</th>
                        <th class="px-4 py-3.5 font-semibold">Mata Uang</th>
                        <th class="px-4 py-3.5 font-semibold text-right">NAB/UP</th>
                        <th class="px-4 py-3.5 font-semibold">Tanggal NAB</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse($reksaDanas as $rd)
                        <tr class="hover:bg-[#f8fafc] transition-colors">
                            <td class="px-4 py-3.5 font-mono text-xs text-muted">{{ $rd->kode_reksa_dana ?? '—' }}</td>
                            <td class="px-4 py-3.5 font-semibold text-primary">
                                <a href="{{ route('user.reksa-dana.show', $rd) }}"
                                    class="hover:underline">{{ $rd->nama_reksa_dana }}</a>
                            </td>
                            <td class="px-4 py-3.5 text-muted text-xs">{{ $rd->nama_manajer_investasi ?? '—' }}</td>
                            <td class="px-4 py-3.5">
                                @php
                                    $jenisColor = match ($rd->jenis) {
                                        'Saham' => 'bg-blue-100 text-blue-700',
                                        'Pendapatan Tetap' => 'bg-amber-100 text-amber-700',
                                        'Campuran' => 'bg-purple-100 text-purple-700',
                                        default => 'bg-green-100 text-green-700',
                                    };
                                @endphp
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $jenisColor }}">{{ $rd->jenis ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-muted">
                                @if (is_array($rd->kategori) && count($rd->kategori))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($rd->kategori as $kat)
                                            <span
                                                class="px-1.5 py-0.5 bg-[#f1f5f9] rounded text-[11px]">{{ $kat }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-xs text-muted">{{ $rd->display_mata_uang }}</td>
                            <td class="px-4 py-3.5 text-right text-xs font-semibold text-primary">
                                {{ $rd->nab_per_unit ? number_format($rd->nab_per_unit, 4, ',', '.') : '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-muted">
                                {{ $rd->tanggal_nab ? $rd->tanggal_nab->format('d M Y') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-muted">
                                <p class="font-medium">Belum ada data reksa dana</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reksaDanas->hasPages())
            <div class="px-6 py-4 border-t border-line flex items-center justify-between text-sm">
                <p class="text-muted text-xs">Menampilkan {{ $reksaDanas->firstItem() }}–{{ $reksaDanas->lastItem() }}
                    dari {{ $reksaDanas->total() }}</p>
                <div class="flex items-center gap-1">
                    @if (!$reksaDanas->onFirstPage())
                        <a href="{{ $reksaDanas->previousPageUrl() }}"
                            class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">←
                            Prev</a>
                    @endif
                    @php
                        $cur = $reksaDanas->currentPage();
                        $last = $reksaDanas->lastPage();
                        $s = max(1, $cur - 2);
                        $e = min($last, $cur + 2);
                    @endphp
                    @if ($s > 1)
                        <a href="{{ $reksaDanas->url(1) }}"
                            class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                        @if ($s > 2)
                            <span class="px-1 text-muted text-xs">…</span>
                        @endif
                    @endif
                    @foreach ($reksaDanas->getUrlRange($s, $e) as $page => $url)
                        <a href="{{ $url }}"
                            class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-accent-teal/85 text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
                    @endforeach
                    @if ($e < $last)
                        @if ($e < $last - 1)
                            <span class="px-1 text-muted text-xs">…</span>
                        @endif
                        <a href="{{ $reksaDanas->url($last) }}"
                            class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                    @endif
                    @if ($reksaDanas->hasMorePages())
                        <a href="{{ $reksaDanas->nextPageUrl() }}"
                            class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next
                            →</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
