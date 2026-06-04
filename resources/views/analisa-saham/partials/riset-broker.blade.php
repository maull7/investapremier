@php
    $routePrefix = request()->routeIs('admin.*') ? 'admin.analisa-saham' : 'user.analisa-saham';
    $documents = $analisa->brokerResearchDocuments ?? collect();
    $formatSize = function ($bytes) {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        return number_format($bytes / 1024, 0) . ' KB';
    };
@endphp

<div class="space-y-5">
    <div class="bg-white rounded-xl border border-line p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap mb-5">
            <div>
                <h3 class="font-semibold text-primary">Upload Dokumen Broker</h3>
                <p class="text-sm text-muted mt-1">PDF atau DOCX, maksimal 5 MB per dokumen.</p>
            </div>
        </div>

        <form method="POST" action="{{ route($routePrefix . '.riset-broker.store', $analisa) }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-[1fr_1.5fr_auto] md:items-end">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Broker</label>
                <input type="text" name="broker" value="{{ old('broker') }}" placeholder="Nama sekuritas"
                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                @error('broker')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dokumen</label>
                <input type="file" name="documents[]" multiple accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                    class="block w-full border border-gray-300 rounded-lg text-sm file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                @error('documents')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
                @error('documents.*')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="inline-flex justify-center items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/90 transition">
                Upload
            </button>
        </form>
    </div>

    <div class="table-card">
        <div class="px-6 py-4 border-b border-line">
            <h3 class="font-semibold text-primary">List Dokumen</h3>
        </div>

        @if($documents->isEmpty())
            <div class="p-6 text-sm text-muted">Belum ada dokumen riset broker.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-line text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-muted">
                        <tr>
                            <th class="px-6 py-3">Nama File</th>
                            <th class="px-6 py-3">Broker</th>
                            <th class="px-6 py-3">Upload Date</th>
                            <th class="px-6 py-3">Size</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line bg-white">
                        @foreach($documents as $document)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-800 whitespace-nowrap">{{ $document->original_name }}</td>
                                <td class="px-6 py-4 text-gray-700 whitespace-nowrap">{{ $document->broker }}</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">{{ $document->created_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">{{ $formatSize($document->file_size) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end items-center gap-2">
                                        <a href="{{ route($routePrefix . '.riset-broker.view', [$analisa, $document]) }}" target="_blank"
                                           class="px-3 py-1.5 rounded-lg border border-line text-gray-700 hover:bg-gray-50 transition">View</a>
                                        <a href="{{ route($routePrefix . '.riset-broker.download', [$analisa, $document]) }}"
                                           class="px-3 py-1.5 rounded-lg border border-line text-gray-700 hover:bg-gray-50 transition">Download</a>
                                        <form method="POST" action="{{ route($routePrefix . '.riset-broker.destroy', [$analisa, $document]) }}" onsubmit="return confirm('Hapus dokumen riset broker ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
