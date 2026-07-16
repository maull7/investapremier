@php
    $formatRupiah = fn($val) => $val ? 'Rp ' . Number($val)->format('id') : '—';
    $formatDate = fn($val) => $val ? \Carbon\Carbon::parse($val)->translatedFormat('d F Y') : '—';
@endphp

<div class="space-y-6">
    <div class="border-b border-line pb-4">
        <p class="text-xs text-muted">Klien: <span class="font-medium text-primary">{{ $plan->user->name }}</span></p>
        <h3 class="text-lg font-bold text-primary mt-1">{{ $plan->kategori_perencanaan }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 text-sm">
            <div><span class="text-muted block">Kebutuhan Dana</span><span class="font-semibold">{{ $formatRupiah($plan->kebutuhan_dana) }}</span></div>
            <div><span class="text-muted block">Target Waktu</span><span class="font-semibold">{{ $plan->target_waktu_tahun }} Tahun</span></div>
            <div><span class="text-muted block">Dana Tersedia</span><span class="font-semibold">{{ $formatRupiah($plan->dana_tersedia) }}</span></div>
            <div><span class="text-muted block">Investasi/Bulan</span><span class="font-semibold">{{ $formatRupiah($plan->investasi_per_bulan) }}</span></div>
            <div class="md:col-span-2"><span class="text-muted block">Profil Risiko</span><span class="font-medium capitalize">{{ $plan->profil_risiko }}</span></div>
            <div class="md:col-span-2"><span class="text-muted block">Sumber Dana</span><span class="font-medium">{{ $plan->sumber_dana }}</span></div>
        </div>
    </div>

    @if ($plan->portofolioItems->count())
        <div class="space-y-3">
            <h4 class="font-semibold text-primary">Portofolio Saat Ini</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border border-line rounded-lg">
                    <thead class="bg-[#f8fafc]">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-muted">Jenis</th>
                            <th class="px-3 py-2 text-left font-semibold text-muted">Produk</th>
                            <th class="px-3 py-2 text-right font-semibold text-muted">Nominal</th>
                            <th class="px-3 py-2 text-right font-semibold text-muted">Harga Akuisisi</th>
                            <th class="px-3 py-2 text-right font-semibold text-muted">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($plan->portofolioItems as $item)
                            <tr>
                                <td class="px-3 py-2">{{ $item->jenis }}</td>
                                <td class="px-3 py-2 font-medium">{{ $item->nama_produk }}</td>
                                <td class="px-3 py-2 text-right">{{ $formatRupiah($item->nominal) }}</td>
                                <td class="px-3 py-2 text-right">{{ $formatRupiah($item->harga_akuisisi) }}</td>
                                <td class="px-3 py-2 text-right font-semibold">{{ $formatRupiah($item->nilai) }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-[#f8fafc] font-semibold">
                            <td colspan="2" class="px-3 py-2 text-right">Total</td>
                            <td class="px-3 py-2 text-right">{{ $formatRupiah($plan->portofolioItems->sum('nominal')) }}</td>
                            <td></td>
                            <td class="px-3 py-2 text-right">{{ $formatRupiah($plan->portofolioItems->sum('nilai')) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <p class="text-muted text-sm">Belum ada portofolio</p>
    @endif

    @if ($plan->progressCheckins->count())
        @php $latest = $plan->progressCheckins->first() @endphp
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
            <h4 class="font-semibold text-emerald-800 mb-2">Progress Terbaru</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-muted">Tanggal</span><br><span class="font-medium">{{ $formatDate($latest->tanggal_checkin) }}</span></div>
                <div><span class="text-muted">Dana Terkumpul</span><br><span class="font-medium text-emerald-700">{{ $formatRupiah($latest->dana_terkumpul) }}</span></div>
            </div>
            @if ($latest->catatan)
                <p class="mt-2 text-xs text-muted italic">{{ $latest->catatan }}</p>
            @endif
        </div>
    @else
        <p class="text-muted text-sm">Belum ada progress check-in</p>
    @endif

    @if ($plan->ai_narasi)
        <div class="bg-[#f8fafc] border border-line rounded-lg p-4">
            <h4 class="font-semibold text-primary mb-2">Analisa AI</h4>
            <div class="prose prose-sm max-w-none text-sm text-muted whitespace-pre-wrap">{{ $plan->ai_narasi }}</div>
        </div>
    @endif
</div>