<div class="py-2 first:pt-0 border-b border-line/60 last:border-b-0">
    <p class="text-xs font-semibold text-primary">{{ $label }}</p>
    <p class="text-[11px] text-muted mt-0.5">{{ $document->created_at->format('d M Y H:i') }}</p>
    <div class="flex flex-wrap gap-2 mt-2">
        <a target="_blank" href="{{ route('admin.daftar-reksa-dana.documents.view', $document) }}"
            class="px-2 py-1 border border-line rounded text-[11px] font-semibold text-muted hover:text-primary">Preview</a>
        <a href="{{ route('admin.daftar-reksa-dana.documents.download', $document) }}"
            class="px-2 py-1 border border-line rounded text-[11px] font-semibold text-muted hover:text-primary">Download</a>
        <button type="button" onclick='openEditDocument(@json($document))'
            class="px-2 py-1 border border-blue-200 text-blue-600 rounded text-[11px] font-semibold hover:bg-blue-50">Edit</button>
        <form method="POST" action="{{ route('admin.daftar-reksa-dana.documents.destroy', $document) }}"
            onsubmit="return confirm('Hapus dokumen ini?')">
            @csrf @method('DELETE')
            <button class="px-2 py-1 border border-red-200 text-red-600 rounded text-[11px] font-semibold hover:bg-red-50">Hapus</button>
        </form>
    </div>
</div>
