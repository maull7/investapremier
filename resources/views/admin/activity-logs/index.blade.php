@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Activity Logs</h1>
                <p class="text-sm text-gray-500 mt-0.5">Monitor aktivitas yang dilakukan admin dan sub admin</p>
            </div>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                {{ session('success') }}</div>
        @endif

        <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Aksi, keterangan, atau user..."
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Aksi</label>
                    <select name="aksi"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Semua Aksi</option>
                        @foreach ($aksiList as $a)
                            <option value="{{ $a }}" {{ request('aksi') === $a ? 'selected' : '' }}>
                                {{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                    <select name="status"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Semua Status</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">User</label>
                    <select name="user_id"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Semua User</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->role }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Dari</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Sampai</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
                    style="background:linear-gradient(135deg,#16a34a,#22c55e)">
                    Filter
                </button>
                <a href="{{ route('admin.activity-logs.index') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">
                    Reset
                </a>
            </div>
        </form>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">User</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Aksi</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Keterangan</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Tanggal</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-600">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $log->user?->name ?? 'System' }}</div>
                                <div class="text-xs text-gray-400">{{ $log->user?->role ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    {{ $log->aksi }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 max-w-xs">{{ $log->keterangan }}</td>
                            <td class="px-4 py-3">
                                @if ($log->status === 'success')
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Success</span>
                                @else
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Failed</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-gray-400">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($logs->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
@endsection
