<div class="py-2 first:pt-0 border-b border-line/60 last:border-b-0">
    <p class="text-xs font-semibold text-primary">{{ $label }}</p>
    <p class="text-[11px] text-muted mt-0.5">{{ $document->created_at->format('d M Y H:i') }}</p>
    <p class="text-[10px] text-muted mt-0.5">Pembaruan:
        {{ ($months[$document->updated_at->month - 1] ?? $document->updated_at->format('F')) . ' ' . $document->updated_at->year }}
    </p>
    <div class="flex flex-wrap gap-2 mt-2">
        <a target="_blank" href="{{ route('admin.daftar-reksa-dana.documents.view', $document) }}"
            class="px-2 py-1 border border-line rounded text-[11px] font-semibold text-muted hover:text-primary">Preview</a>
        <a href="{{ route('admin.daftar-reksa-dana.documents.download', $document) }}"
            class="px-2 py-1 border border-line rounded text-[11px] font-semibold text-muted hover:text-primary">Download</a>
        <button type="button" data-parse-doc="{{ $document->id }}" data-parse-name="{{ $document->original_name }}"
            data-parse-type="{{ $document->document_type }}" data-parse-count="{{ $document->parsed_pages_count ?? 0 }}"
            class="btn-parse-document px-2 py-1 border border-emerald-200 text-emerald-600 rounded text-[11px] font-semibold hover:bg-emerald-50">
            Parse Dokumen
            @if (($document->parsed_pages_count ?? 0) > 0)
                <span class="text-[10px] text-muted ml-0.5">({{ $document->parsed_pages_count }} hlm)</span>
            @endif
        </button>
        <button type="button" data-edit-doc="{{ $document->id }}" data-edit-name="{{ $document->original_name }}"
            data-edit-type="{{ $document->document_type }}" data-edit-ffs-month="{{ $document->ffs_month }}"
            data-edit-ffs-year="{{ $document->ffs_year }}" data-edit-notes="{{ $document->notes }}"
            data-edit-updated="{{ $document->updated_at?->format('Y-m-d H:i:s') }}"
            class="btn-edit-document px-2 py-1 border border-blue-200 text-blue-600 rounded text-[11px] font-semibold hover:bg-blue-50">Edit</button>
        <form method="POST" action="{{ route('admin.daftar-reksa-dana.documents.destroy', $document) }}"
            onsubmit="return confirm('Hapus dokumen ini?')">
            @csrf @method('DELETE')
            <button
                class="px-2 py-1 border border-red-200 text-red-600 rounded text-[11px] font-semibold hover:bg-red-50">Hapus</button>
        </form>
    </div>
</div>
