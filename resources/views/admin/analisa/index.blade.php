@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Monitor Analisa Reksa Dana</h1>
                <p class="page-sub">Pantau seluruh data reksa dana, prospektus, dan FFS</p>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="flex gap-1 border-b border-line overflow-x-auto">
            <a href="{{ route('admin.analisa.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'analisa'])) }}"
                class="px-4 py-2.5 text-sm whitespace-nowrap transition {{ $tab === 'analisa' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary' }}">
                Analisa
            </a>
            <a href="{{ route('admin.analisa.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'prospektus'])) }}"
                class="px-4 py-2.5 text-sm whitespace-nowrap transition {{ $tab === 'prospektus' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary' }}">
                Prospectus
            </a>
            <a href="{{ route('admin.analisa.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'ffs'])) }}"
                class="px-4 py-2.5 text-sm whitespace-nowrap transition {{ $tab === 'ffs' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary' }}">
                FFS
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}</div>
        @endif

        @if ($tab === 'analisa')
            {{-- Filter Status --}}
            <div class="flex gap-2 text-sm flex-wrap items-center">
                <span class="text-xs font-semibold text-muted">Status:</span>
                @foreach (['', 'original', 'submitted', 'reviewed', 'input_manual'] as $s)
                    <a href="{{ route('admin.analisa.index', array_filter(['tab' => 'analisa', 'status' => $s ?: null, 'kategori' => request('kategori'), 'ffs_bulan' => request('ffs_bulan'), 'ffs_tahun' => request('ffs_tahun')])) }}"
                        class="px-3 py-1.5 rounded-lg border transition {{ request('status') === $s || (!request('status') && $s === '') ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                        {{ match ($s) {'' => 'Semua','original' => 'Original','submitted' => 'Menunggu Review','reviewed' => 'Sudah Direview','input_manual' => 'Input Manual'} }}
                    </a>
                @endforeach
                
                {{-- Search by Status Dropdown --}}
                <form method="GET" action="{{ route('admin.analisa.index') }}" class="inline-flex items-center gap-2 ml-4">
                    <input type="hidden" name="tab" value="analisa">
                    @if (request('kategori'))
                        <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                    @endif
                    @if (request('ffs_bulan'))
                        <input type="hidden" name="ffs_bulan" value="{{ request('ffs_bulan') }}">
                    @endif
                    @if (request('ffs_tahun'))
                        <input type="hidden" name="ffs_tahun" value="{{ request('ffs_tahun') }}">
                    @endif
                    @if (request('mode'))
                        <input type="hidden" name="mode" value="{{ request('mode') }}">
                    @endif
                    @if (request('is_published'))
                        <input type="hidden" name="is_published" value="{{ request('is_published') }}">
                    @endif
                    <select name="status" onchange="this.form.submit()"
                        class="text-xs border-gray-300 rounded-lg px-3 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">Pilih Status...</option>
                        <option value="original" {{ request('status') === 'original' ? 'selected' : '' }}>Original</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Menunggu Review</option>
                        <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Sudah Direview</option>
                        <option value="input_manual" {{ request('status') === 'input_manual' ? 'selected' : '' }}>Input Manual</option>
                    </select>
                </form>
            </div>

            {{-- Filter Kategori + Kalender FFS --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex gap-2 text-xs flex-wrap">
                    <a href="{{ route('admin.analisa.index', array_filter(['tab' => 'analisa', 'status' => request('status'), 'ffs_bulan' => request('ffs_bulan'), 'ffs_tahun' => request('ffs_tahun'), 'mode' => request('mode'), 'is_published' => request('is_published')])) }}"
                        class="px-3 py-1.5 rounded-lg border transition {{ !request('kategori') ? 'bg-accent text-white border-accent' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                        Semua Kategori
                    </a>
                    @foreach (['Konvensional', 'Syariah', 'index', 'ETF'] as $k)
                        <a href="{{ route('admin.analisa.index', array_filter(['tab' => 'analisa', 'status' => request('status'), 'kategori' => $k, 'ffs_bulan' => request('ffs_bulan'), 'ffs_tahun' => request('ffs_tahun'), 'mode' => request('mode'), 'is_published' => request('is_published')])) }}"
                            class="px-3 py-1.5 rounded-lg border transition {{ request('kategori') === $k ? 'bg-accent text-white border-accent' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                            {{ $k }}
                        </a>
                    @endforeach
                </div>

                <div class="flex items-center gap-2 ml-auto">
                    <form method="GET" action="{{ route('admin.analisa.index') }}" class="flex items-center gap-2"
                        id="ffs-filter-form">
                        <input type="hidden" name="tab" value="analisa">
                        @if (request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        @if (request('kategori'))
                            <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                        @endif
                        @if (request('mode'))
                            <input type="hidden" name="mode" value="{{ request('mode') }}">
                        @endif
                        @if (request('is_published'))
                            <input type="hidden" name="is_published" value="{{ request('is_published') }}">
                        @endif
                        <select name="ffs_bulan" onchange="document.getElementById('ffs-filter-form').submit()"
                            class="text-xs border-gray-300 rounded-lg px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                            <option value="">Semua Bulan FFS</option>
                            @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $bln)
                                <option value="{{ $i + 1 }}"
                                    {{ request('ffs_bulan') == $i + 1 ? 'selected' : '' }}>{{ $bln }}</option>
                            @endforeach
                        </select>
                        <select name="ffs_tahun" onchange="document.getElementById('ffs-filter-form').submit()"
                            class="text-xs border-gray-300 rounded-lg px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                            <option value="">Semua Tahun FFS</option>
                            @foreach ($tahunList as $thn)
                                <option value="{{ $thn }}" {{ request('ffs_tahun') == $thn ? 'selected' : '' }}>
                                    {{ $thn }}</option>
                            @endforeach
                        </select>
                        <select name="mode" onchange="document.getElementById('ffs-filter-form').submit()"
                            class="text-xs border-gray-300 rounded-lg px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                            <option value="">Semua Mode</option>
                            <option value="lengkap" {{ request('mode') === 'lengkap' ? 'selected' : '' }}>Input Lengkap</option>
                            <option value="manual" {{ request('mode') === 'manual' ? 'selected' : '' }}>Input Manual</option>
                        </select>
                        <select name="is_published" onchange="document.getElementById('ffs-filter-form').submit()"
                            class="text-xs border-gray-300 rounded-lg px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                            <option value="">Semua Publish</option>
                            <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>Published</option>
                            <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </form>
                </div>
            </div>
        @endif

        @if ($tab === 'prospektus' || $tab === 'ffs')
            <form method="GET" action="{{ route('admin.analisa.index') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="flex-1 min-w-56">
                    <label class="block text-xs font-semibold text-muted mb-1">Cari Reksa Dana</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nama atau kode reksa dana..."
                        class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                @if ($tab === 'ffs')
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1">Bulan</label>
                        <select name="ffs_bulan"
                            class="text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
                            <option value="">Semua Bulan</option>
                            @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $bln)
                                <option value="{{ $i + 1 }}"
                                    {{ request('ffs_bulan') == $i + 1 ? 'selected' : '' }}>{{ $bln }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1">Tahun</label>
                        <select name="ffs_tahun"
                            class="text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
                            <option value="">Semua Tahun</option>
                            @foreach ($tahunList ?? [] as $thn)
                                <option value="{{ $thn }}" {{ request('ffs_tahun') == $thn ? 'selected' : '' }}>
                                    {{ $thn }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button type="submit"
                    class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 transition">Filter</button>
                @if (request('search') || request('ffs_bulan') || request('ffs_tahun'))
                    <a href="{{ route('admin.analisa.index', ['tab' => $tab]) }}"
                        class="px-4 py-2 border border-line text-muted rounded-lg text-sm font-semibold hover:text-primary transition">Reset</a>
                @endif
            </form>
        @endif

        <div class="table-card">
            @if ($reksaDanas->isEmpty())
                <div class="p-12 text-center text-muted text-sm">
                    {{ $tab === 'prospektus' ? 'Belum ada reksa dana dengan Prospektus.' : ($tab === 'ffs' ? 'Belum ada reksa dana dengan FFS.' : 'Belum ada data reksa dana.') }}
                </div>
            @elseif ($tab === 'analisa')
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[900px]">
                        <thead class="bg-[#f8fafc] border-b border-line">
                            <tr>
                                <th class="text-left px-5 py-3 font-semibold text-primary">
                                    <a href="{{ route('admin.analisa.index', array_merge(request()->all(), ['sort' => 'nama_reksa_dana', 'direction' => request('sort') === 'nama_reksa_dana' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-accent">
                                        Reksa Dana
                                        @if(request('sort') === 'nama_reksa_dana')
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="{{ request('direction') === 'asc' ? 'M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z' : 'M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z' }}" clip-rule="evenodd"/></svg>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left px-5 py-3 font-semibold text-primary">
                                    <a href="{{ route('admin.analisa.index', array_merge(request()->all(), ['sort' => 'jenis', 'direction' => request('sort') === 'jenis' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-accent">
                                        Jenis
                                        @if(request('sort') === 'jenis')
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="{{ request('direction') === 'asc' ? 'M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z' : 'M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z' }}" clip-rule="evenodd"/></svg>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left px-5 py-3 font-semibold text-primary">Kategori</th>
                                <th class="text-left px-5 py-3 font-semibold text-primary">Kalender FFS</th>
                                <th class="text-left px-5 py-3 font-semibold text-primary">Status</th>
                                <th class="text-center px-5 py-3 font-semibold text-primary">Publish</th>
                                <th class="text-left px-5 py-3 font-semibold text-primary">User</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($reksaDanas as $rd)
                                @php
                                    $analisa = $rd->analisa->first();
                                    $status = $analisa ? $analisa->status : 'original';
                                    $ffsBulan = $analisa->ffs_bulan ?? null;
                                    $ffsTahun = $analisa->ffs_tahun ?? null;
                                @endphp
                                <tr class="hover:bg-[#f8fafc] transition" x-data="{ showAllFfs: false }">
                                    <td class="px-5 py-3.5 font-medium">
                                        <div class="font-medium text-primary">{{ $rd->nama_reksa_dana }}</div>
                                        @if ($rd->kode_reksa_dana)
                                            <div class="text-xs text-muted font-mono">{{ $rd->kode_reksa_dana }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-muted">
                                        {{ $analisa->jenis_reksa_dana ?? ($rd->jenis ?? '-') }}</td>
                                    <td class="px-5 py-3.5">
                                        @php
                                            $kategori = $analisa->kategori ?? $rd->kategori;
                                        @endphp
                                        @if ($kategori)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ((array) $kategori as $kat)
                                                    <span
                                                        class="px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded text-xs">{{ $kat }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-muted">
                                        <div class="flex items-center gap-2">
                                            @if ($ffsBulan && $ffsTahun)
                                                <span>{{ $bulanIndonesia[$ffsBulan - 1] }} {{ $ffsTahun }}</span>
                                            @else
                                                <span class="text-muted text-xs">—</span>
                                            @endif
                                            @if ($rd->documents->where('document_type', 'ffs')->count() > 0)
                                                <button @click="showAllFfs = !showAllFfs"
                                                    class="text-xs text-accent-dark underline hover:underline whitespace-nowrap"
                                                    x-text="showAllFfs ? 'Sembunyikan' : 'Melihat FFS Lainnya'">
                                                </button>
                                            @endif
                                        </div>
                                        @if ($rd->documents->where('document_type', 'ffs')->count() > 0)
                                            <div x-show="showAllFfs" x-cloak class="mt-1 space-y-0.5">
                                                @foreach ($rd->documents->where('document_type', 'ffs') as $doc)
                                                    <div class="text-xs text-muted">
                                                        — {{ $bulanIndonesia[$doc->ffs_month - 1] }} {{ $doc->ffs_year }}
                                                        @if ($doc->notes)
                                                            <span class="text-[10px]">({{ $doc->notes }})</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @php
                                            if ($status === 'original') {
                                                $badge = 'bg-slate-100 text-slate-600';
                                                $label = 'Original';
                                            } else {
                                                $badge = match ($status) {
                                                    'input_manual' => 'bg-gray-100 text-gray-600',
                                                    'submitted' => 'bg-yellow-100 text-yellow-700',
                                                    'reviewed' => 'bg-green-100 text-green-700',
                                                    default => 'bg-slate-100 text-slate-600',
                                                };
                                                $label = match ($status) {
                                                    'input_manual' => 'Input Manual',
                                                    'submitted' => 'Menunggu Review',
                                                    'reviewed' => 'Sudah Direview',
                                                    default => ucfirst($status ?? 'Unknown'),
                                                };
                                            }
                                        @endphp
                                        <span
                                            class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        @if ($analisa && in_array($analisa->status, ['reviewed', 'input_manual']))
                                            <form method="POST" action="{{ route('admin.analisa.publish', $analisa) }}"
                                                class="inline-flex flex-col items-center gap-1">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg transition
                                                {{ $analisa->is_published ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    {{ $analisa->is_published ? 'Published' : 'Draft' }}
                                                </button>
                                                @if ($analisa->is_published && $analisa->published_at)
                                                    <span
                                                        class="text-[10px] text-muted">{{ $analisa->published_at->format('d M Y H:i') }}</span>
                                                @endif
                                            </form>
                                        @elseif ($status !== 'original')
                                            <span class="text-xs text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if ($analisa && $analisa->user)
                                            <div class="font-medium text-primary text-xs">{{ $analisa->user->name }}</div>
                                            <div class="text-xs text-muted">{{ $analisa->user->email }}</div>
                                        @else
                                            <span class="text-muted text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                            @if ($analisa)
                                                <a href="{{ route('admin.analisa-rd.edit', $analisa) }}"
                                                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-white bg-primary rounded-lg hover:bg-primary/90 transition">
                                                    Analisa RD
                                                </a>
                                                <a href="{{ route('admin.analisa.show', $analisa) }}"
                                                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-primary border border-line rounded-lg hover:bg-[#f1f5f9] transition">
                                                    Detail
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('admin.analisa.destroy', $analisa) }}"
                                                    class="inline-flex"
                                                    onsubmit="return confirm('Hapus data analisa ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif ($tab === 'prospektus')
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Reksa Dana</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Prospektus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($reksaDanas as $rd)
                            @php
                                $prospektusDocs = $rd->documents->where('document_type', 'prospektus');
                            @endphp
                            <tr x-data="{ showAll: false }" class="hover:bg-[#f8fafc] transition">
                                <td class="px-5 py-3.5 min-w-56">
                                    <div class="font-medium text-primary">{{ $rd->nama_reksa_dana }}</div>
                                    @if ($rd->kode_reksa_dana)
                                        <div class="text-xs text-muted font-mono">{{ $rd->kode_reksa_dana }}</div>
                                    @endif
                                    @if ($prospektusDocs->count() > 1)
                                        <button @click="showAll = !showAll"
                                            class="text-xs text-accent-dark underline hover:underline mt-1 block"
                                            x-text="showAll ? 'Sembunyikan' : 'Melihat Lainnya'"></button>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    @forelse ($prospektusDocs as $i => $doc)
                                        @php
                                            $label = match (true) {
                                                $doc->ffs_month => $bulanIndonesia[$doc->ffs_month - 1] .
                                                    ' ' .
                                                    $doc->ffs_year,
                                                $doc->ffs_year => $doc->ffs_year,
                                                default => $doc->original_name,
                                            };
                                        @endphp

                                        <div class="mb-1 last:mb-0"
                                            @if ($i > 0) x-show="showAll" x-cloak @endif>
                                            <a href="{{ $doc->url }}" target="_blank"
                                                class="group inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs transition-colors hover:bg-gray-100 dark:hover:bg-gray-800">
                                                <span class="font-medium text-accent-dark group-hover:underline">
                                                    {{ $label }}
                                                </span>

                                                @if ($doc->notes)
                                                    <span
                                                        class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-muted dark:bg-gray-800">
                                                        {{ $doc->notes }}
                                                    </span>
                                                @endif

                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3.5 w-3.5 text-muted opacity-0 transition-opacity group-hover:opacity-100"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M7 17L17 7M17 7H9M17 7v8" />
                                                </svg>
                                            </a>


                                        </div>
                                    @empty
                                        <span class="text-xs text-muted">—</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif ($tab === 'ffs')
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Reksa Dana</th>
                            <th class="text-left px-5 py-3 font-semibold text-primary">Fund Fact Sheet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($reksaDanas as $rd)
                            @php
                                $ffsDocs = $rd->documents->where('document_type', 'ffs');
                            @endphp
                            <tr x-data="{ showAll: false }" class="hover:bg-[#f8fafc] transition">
                                <td class="px-5 py-3.5 min-w-56">
                                    <div class="font-medium text-primary">{{ $rd->nama_reksa_dana }}</div>
                                    @if ($rd->kode_reksa_dana)
                                        <div class="text-xs text-muted font-mono">{{ $rd->kode_reksa_dana }}</div>
                                    @endif
                                    @if ($ffsDocs->count() > 1)
                                        <button @click="showAll = !showAll"
                                            class="text-xs text-accent-dark underline hover:underline mt-1 block"
                                            x-text="showAll ? 'Sembunyikan' : 'Melihat Lainnya'"></button>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    @forelse ($ffsDocs as $i => $doc)
                                        @php
                                            $label =
                                                $doc->ffs_month && $doc->ffs_year
                                                    ? $bulanIndonesia[$doc->ffs_month - 1] . ' ' . $doc->ffs_year
                                                    : $doc->original_name;
                                        @endphp

                                        <div class="mb-2 last:mb-0"
                                            @if ($i > 0) x-show="showAll" x-cloak @endif>
                                            <a href="{{ $doc->url }}" target="_blank"
                                                class="group flex items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2 transition-all hover:border-accent hover:bg-gray-50 hover:shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div
                                                        class="flex h-8 w-8 items-center justify-center rounded-md bg-accent/10">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-4 w-4 text-accent" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9 13h6m-3-3v6m-10 5h.01" />
                                                        </svg>
                                                    </div>

                                                    <div class="min-w-0">
                                                        <div
                                                            class="truncate text-xs font-medium text-gray-900 dark:text-white">
                                                            {{ $label }}
                                                        </div>

                                                        @if ($doc->notes)
                                                            <span
                                                                class="mt-1 inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                                {{ $doc->notes }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>


                                            </a>
                                        </div>
                                    @empty
                                        <span class="text-xs text-muted">—</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            <div class="px-5 py-3 border-t border-line">
                {{ $reksaDanas->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
