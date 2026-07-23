@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-bold text-2xl text-accent-teal/85">{{ $pageTitle ?? 'Monitor Reksa Dana' }}</h1>
                <p class="page-sub">{{ $pageSub ?? 'Hasil analisa reksa dana yang telah dipublikasikan' }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tab Navigation --}}
        <div class="flex gap-1 border-b border-line overflow-x-auto">
            <a href="{{ route('user.analisa.index', request()->except(['tab', 'page'])) }}"
                class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px whitespace-nowrap {{ ($tab ?? 'analisa') === 'analisa' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-primary' }}">
                Analisa
            </a>
            <a href="{{ route('user.analisa.index', array_merge(request()->except(['tab', 'page', 'prospektus_year']), ['tab' => 'prospektus'])) }}"
                class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px whitespace-nowrap {{ ($tab ?? '') === 'prospektus' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-primary' }}">
                Prospektus
            </a>
            <a href="{{ route('user.analisa.index', array_merge(request()->except(['tab', 'page', 'ffs_bulan', 'ffs_tahun']), ['tab' => 'ffs'])) }}"
                class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px whitespace-nowrap {{ ($tab ?? '') === 'ffs' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-primary' }}">
                FFS
            </a>
        </div>

        {{-- TAB: PROSPEKTUS --}}
        @if (($tab ?? 'analisa') === 'prospektus')
            <form method="GET" action="{{ route('user.analisa.index') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="tab" value="prospektus">
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Tahun</label>
                    <select name="prospektus_year" onchange="this.form.submit()"
                        class="text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">Semua Tahun</option>
                        @foreach ($tahunList ?? [] as $thn)
                            <option value="{{ $thn }}" {{ request('prospektus_year') == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 transition">Filter</button>
                @if (request('prospektus_year'))
                    <a href="{{ route('user.analisa.index', ['tab' => 'prospektus']) }}" class="px-4 py-2 border border-line text-muted rounded-lg text-sm font-semibold hover:text-primary transition">Reset</a>
                @endif
            </form>

            <div class="table-card">
                @if (empty($reksaDanas) || $reksaDanas->isEmpty())
                    <div class="p-12 text-center text-muted text-sm">Belum ada reksa dana dengan Prospektus.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-[#f8fafc] border-b border-line">
                                <tr>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Reksa Dana</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Prospektus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($reksaDanas as $rd)
                                    @php $proDocs = $rd->documents->where('document_type', 'prospektus'); @endphp
                                    <tr x-data="{ showAll: false }" class="hover:bg-[#f8fafc] transition">
                                        <td class="px-5 py-3.5 min-w-56">
                                            <div class="font-medium text-primary">{{ $rd->nama_reksa_dana }}</div>
                                            @if ($rd->kode_reksa_dana)
                                                <div class="text-xs text-muted font-mono">{{ $rd->kode_reksa_dana }}</div>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5">
                                            @forelse ($proDocs as $i => $doc)
                                                <div @if ($i > 0) x-show="showAll" x-cloak @endif class="mb-1 last:mb-0">
                                                    <a href="{{ $doc->file_path ? asset('storage/' . $doc->file_path) : '#' }}" target="_blank" class="text-xs text-accent-dark hover:underline">
                                                        {{ $doc->ffs_month ? $bulanIndonesia[$doc->ffs_month - 1] . ' ' . $doc->ffs_year : ($doc->ffs_year ?: $doc->original_name) }}
                                                    </a>
                                                </div>
                                            @empty
                                                <span class="text-xs text-muted">—</span>
                                            @endforelse
                                            @if ($proDocs->count() > 1)
                                                <button @click="showAll = !showAll" class="text-xs text-accent underline mt-1" x-text="showAll ? 'Sembunyikan' : 'Lihat Semua'"></button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3 border-t border-line">{{ $reksaDanas->links() }}</div>
                @endif
            </div>

        {{-- TAB: FFS --}}
        @elseif (($tab ?? 'analisa') === 'ffs')
            <form method="GET" action="{{ route('user.analisa.index') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="tab" value="ffs">
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Bulan</label>
                    <select name="ffs_bulan" onchange="this.form.submit()"
                        class="text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">Semua Bulan</option>
                        @foreach ($bulanIndonesia as $i => $bln)
                            <option value="{{ $i + 1 }}" {{ request('ffs_bulan') == $i + 1 ? 'selected' : '' }}>{{ $bln }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Tahun</label>
                    <select name="ffs_tahun" onchange="this.form.submit()"
                        class="text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">Semua Tahun</option>
                        @foreach ($tahunList ?? [] as $thn)
                            <option value="{{ $thn }}" {{ request('ffs_tahun') == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 transition">Filter</button>
                @if (request('ffs_bulan') || request('ffs_tahun'))
                    <a href="{{ route('user.analisa.index', ['tab' => 'ffs']) }}" class="px-4 py-2 border border-line text-muted rounded-lg text-sm font-semibold hover:text-primary transition">Reset</a>
                @endif
            </form>

            <div class="table-card">
                @if (empty($reksaDanas) || $reksaDanas->isEmpty())
                    <div class="p-12 text-center text-muted text-sm">Belum ada reksa dana dengan FFS.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-[#f8fafc] border-b border-line">
                                <tr>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Reksa Dana</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Fund Fact Sheet</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($reksaDanas as $rd)
                                    @php $ffsDocs = $rd->documents->where('document_type', 'ffs'); @endphp
                                    <tr x-data="{ showAll: false }" class="hover:bg-[#f8fafc] transition">
                                        <td class="px-5 py-3.5 min-w-56">
                                            <div class="font-medium text-primary">{{ $rd->nama_reksa_dana }}</div>
                                            @if ($rd->kode_reksa_dana)
                                                <div class="text-xs text-muted font-mono">{{ $rd->kode_reksa_dana }}</div>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5">
                                            @forelse ($ffsDocs as $i => $doc)
                                                <div @if ($i > 0) x-show="showAll" x-cloak @endif class="mb-1 last:mb-0">
                                                    <a href="{{ $doc->file_path ? asset('storage/' . $doc->file_path) : '#' }}" target="_blank" class="text-xs text-accent-dark hover:underline">
                                                        {{ $doc->ffs_month ? $bulanIndonesia[$doc->ffs_month - 1] . ' ' . $doc->ffs_year : $doc->original_name }}
                                                    </a>
                                                </div>
                                            @empty
                                                <span class="text-xs text-muted">—</span>
                                            @endforelse
                                            @if ($ffsDocs->count() > 1)
                                                <button @click="showAll = !showAll" class="text-xs text-accent underline mt-1" x-text="showAll ? 'Sembunyikan' : 'Lihat Semua'"></button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3 border-t border-line">{{ $reksaDanas->links() }}</div>
                @endif
            </div>

        {{-- TAB: ANALISA (default) --}}
        @else
            @if ($publishedAnalisas->isEmpty())
                <div class="bg-white rounded-xl border border-line p-12 text-center">
                    <svg class="w-12 h-12 text-muted mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="text-muted text-sm">Belum ada analisa yang dipublikasikan.</p>
                </div>
            @else
                <div class="table-card">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-[#f8fafc] border-b border-line">
                                <tr>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Nama Reksa Dana</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Jenis</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Kategori</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Kalender FFS</th>
                                    <th class="text-left px-5 py-3 font-semibold text-primary">Dipublikasikan</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($publishedAnalisas as $pa)
                                    <tr class="hover:bg-[#f8fafc] transition">
                                        <td class="px-5 py-3.5 font-medium text-primary">{{ $pa->nama_reksa_dana }}</td>
                                        <td class="px-5 py-3.5 text-muted">{{ $pa->jenis_reksa_dana }}</td>
                                        <td class="px-5 py-3.5 text-muted">
                                            @php
                                                $kategori = $pa->kategori;
                                                if (is_string($kategori)) {
                                                    $decoded = json_decode($kategori, true);
                                                    $kategori = is_array($decoded) ? $decoded : [$kategori];
                                                }
                                                $kategori = is_array($kategori) ? $kategori : [];
                                            @endphp
                                            {{ count($kategori) ? implode(', ', $kategori) : '—' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-muted">
                                            @if ($pa->ffs_bulan && $pa->ffs_tahun)
                                                {{ $bulanIndonesia[$pa->ffs_bulan - 1] }} {{ $pa->ffs_tahun }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 text-muted text-xs">
                                            {{ $pa->published_at ? $pa->published_at->format('d M Y') : '—' }}</td>
                                        <td class="px-5 py-3.5 text-right">
                                            <a href="{{ route('user.analisa.show', $pa) }}"
                                                class="px-3 py-1.5 text-xs font-medium text-primary border border-line rounded-lg hover:bg-[#f1f5f9] transition">
                                                Lihat Hasil
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3 border-t border-line">
                        {{ $publishedAnalisas->links() }}
                    </div>
                </div>
            @endif

            @if ($analisas->isNotEmpty())
                <div class="bg-white rounded-xl border border-line overflow-hidden">
                    <div class="px-5 py-3 bg-gradient-to-r from-primary to-primary/80">
                        <h2 class="font-semibold text-white text-sm">Analisa Saya</h2>
                    </div>
                    <div class="divide-y divide-line">
                        @foreach ($analisas as $analisa)
                            <div class="px-5 py-3.5 flex items-center justify-between hover:bg-[#f8fafc] transition">
                                <div>
                                    <p class="font-medium text-primary text-sm">{{ $analisa->nama_reksa_dana }}</p>
                                    <p class="text-xs text-muted">{{ $analisa->jenis_reksa_dana }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @php
                                        $badge = match ($analisa->status) {
                                            'draft' => 'bg-gray-100 text-gray-600',
                                            'submitted' => 'bg-yellow-100 text-yellow-700',
                                            'reviewed' => 'bg-green-100 text-green-700',
                                            default => 'bg-slate-100 text-slate-600',
                                        };
                                        $label = match ($analisa->status) {
                                            'draft' => 'Draft',
                                            'submitted' => 'Menunggu Review',
                                            'reviewed' => 'Sudah Direview',
                                            default => ucfirst($analisa->status ?? 'Unknown'),
                                        };
                                    @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                                    <a href="{{ route('user.analisa.show', $analisa) }}"
                                        class="px-3 py-1.5 text-xs font-medium text-primary border border-line rounded-lg hover:bg-[#f1f5f9] transition">
                                        Lihat Hasil
                                    </a>
                                    @if ($analisa->status !== 'reviewed')
                                        <a href="{{ route('user.analisa.edit', $analisa) }}"
                                            class="px-3 py-1.5 text-xs font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
