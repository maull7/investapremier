<div class="grid lg:grid-cols-[minmax(0,1fr)_minmax(420px,0.9fr)] gap-5">
    <div class="table-card">
        <div class="table-head">
            <h2 class="th-title">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
                Batch Ekstraksi Obligasi
            </h2>
            <span class="th-meta">{{ $extractionBatches->total() }} total</span>
        </div>

        @if ($extractionBatches->isEmpty())
            <div class="py-16 text-center text-muted">
                <p class="font-medium">Belum ada hasil ekstrak</p>
                <p class="text-sm mt-1">Klik "Ekstrak Data" untuk membuat batch baru.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                            <th class="px-4 py-3.5 font-semibold">Batch</th>
                            <th class="px-4 py-3.5 font-semibold">Jenis</th>
                            <th class="px-4 py-3.5 font-semibold">Rentang</th>
                            <th class="px-4 py-3.5 font-semibold">Sumber</th>
                            <th class="px-4 py-3.5 font-semibold">Tanggal</th>
                            <th class="px-4 py-3.5 font-semibold">Status</th>
                            <th class="px-4 py-3.5 font-semibold text-right">Records</th>
                            <th class="px-4 py-3.5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($extractionBatches as $batch)
                            @php
                                $statusClass = match ($batch->status) {
                                    'success' => 'bg-green-50 text-green-700 border-green-200',
                                    'failed' => 'bg-red-50 text-red-700 border-red-200',
                                    'processing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    default => 'bg-slate-50 text-slate-600 border-slate-200',
                                };
                            @endphp
                            <tr class="hover:bg-[#f8fafc] transition-colors">
                                <td class="px-4 py-3 font-semibold text-primary">#{{ $batch->id }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-primary">Data Obligasi</div>
                                    <div class="text-xs text-muted">Semua obligasi</div>
                                </td>
                                <td class="px-4 py-3 text-muted">{{ $batch->range_label ?: '-' }}</td>
                                <td class="px-4 py-3 text-muted">{{ $batch->source }}</td>
                                <td class="px-4 py-3 text-muted">{{ $batch->data_date?->format('d/m/Y') ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2.5 py-1 rounded-full border text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst($batch->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">{{ number_format($batch->total_records) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.obligasi.index', ['tab' => 'hasil-ekstrak', 'detail_batch' => $batch->id]) }}"
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-primary bg-primary/10 hover:bg-primary/15 transition">
                                            Lihat Detail
                                        </a>
                                        <form method="POST" action="{{ route('admin.obligasi.extraction-batches.retry', $batch) }}">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-muted border border-line hover:text-primary transition">
                                                Retry
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($extractionBatches->hasPages())
                <div class="px-6 py-4 border-t border-line">
                    {{ $extractionBatches->links() }}
                </div>
            @endif
        @endif
    </div>

    <div class="table-card">
        <div class="table-head">
            <h2 class="th-title">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0A9 9 0 113 12a9 9 0 0118 0z" />
                </svg>
                Preview Hasil
            </h2>
            @if ($detailBatch)
                <span class="th-meta">Batch #{{ $detailBatch->id }}</span>
            @endif
        </div>

        @if (!$detailBatch)
            <div class="py-16 text-center text-muted">
                <p class="font-medium">Pilih batch untuk melihat preview</p>
            </div>
        @else
            <div class="p-4 border-b border-line">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($detailBatch->status === 'success')
                        <form method="POST" action="{{ route('admin.obligasi.extraction-batches.save', $detailBatch) }}" class="flex items-center gap-2">
                            @csrf
                            <select name="duplicate_action"
                                class="text-xs border border-line rounded-lg px-2 py-2 focus:outline-none focus:ring-2 focus:ring-accent/30">
                                <option value="skip">Skip duplicate</option>
                                <option value="update">Update duplicate</option>
                            </select>
                            <button class="px-3 py-2 bg-accent text-white rounded-lg text-xs font-semibold hover:bg-accent/90 transition">
                                Simpan ke Database
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.obligasi.extraction-batches.destroy', $detailBatch) }}">
                        @csrf @method('DELETE')
                        <button class="px-3 py-2 border border-red-200 text-red-600 rounded-lg text-xs font-semibold hover:bg-red-50 transition">
                            Hapus Hasil Ekstrak
                        </button>
                    </form>
                </div>
                @if ($detailBatch->error_message)
                    <p class="mt-3 text-xs text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2">
                        {{ $detailBatch->error_message }}
                    </p>
                @endif
            </div>

            <div class="overflow-x-auto max-h-[520px]">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted uppercase">
                            <th class="px-3 py-2">Kode</th>
                            <th class="px-3 py-2">Nama</th>
                            <th class="px-3 py-2">Issuer</th>
                            <th class="px-3 py-2">Maturity</th>
                            <th class="px-3 py-2 text-right">Coupon</th>
                            <th class="px-3 py-2 text-right">YTM</th>
                            <th class="px-3 py-2 text-right">Fair Price</th>
                            <th class="px-3 py-2">Source</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($detailBatch->bondData as $row)
                            <tr>
                                <td class="px-3 py-2 font-semibold">{{ $row->bond_code }}</td>
                                <td class="px-3 py-2">{{ $row->bond_name ?: '-' }}</td>
                                <td class="px-3 py-2">{{ $row->issuer ?: '-' }}</td>
                                <td class="px-3 py-2">{{ $row->maturity_date?->format('d/m/Y') ?: '-' }}</td>
                                <td class="px-3 py-2 text-right">{{ $row->coupon ?: '-' }}</td>
                                <td class="px-3 py-2 text-right">{{ $row->yield ?: '-' }}</td>
                                <td class="px-3 py-2 text-right">{{ $row->fair_price ?: '-' }}</td>
                                <td class="px-3 py-2">{{ $row->source }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-3 py-6 text-center text-muted" colspan="8">Tidak ada detail.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
