@extends('layouts.admin')
@section('title', 'Detail Sub Admin')
@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.sub-admins.index') }}" class="text-gray-400 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Detail Sub Admin</h1>
        <div class="ml-auto flex gap-2">
            <a href="{{ route('admin.sub-admins.edit', $subAdmin) }}" class="px-4 py-2 rounded-lg text-sm font-medium bg-blue-50 text-blue-700 hover:bg-blue-100">Edit</a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-3 text-sm">
        <div class="flex justify-between border-b border-gray-50 pb-3">
            <span class="text-gray-500">Nama</span>
            <span class="font-medium text-gray-900">{{ $subAdmin->name }}</span>
        </div>
        <div class="flex justify-between border-b border-gray-50 pb-3">
            <span class="text-gray-500">Email</span>
            <span class="text-gray-700">{{ $subAdmin->email }}</span>
        </div>
        <div class="flex justify-between border-b border-gray-50 pb-3">
            <span class="text-gray-500">Status</span>
            @if($subAdmin->is_active)
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
            @else
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Nonaktif</span>
            @endif
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Dibuat</span>
            <span class="text-gray-700">{{ $subAdmin->created_at->format('d M Y, H:i') }}</span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-semibold text-gray-800 text-sm mb-4">Hak Akses Menu</h2>
        <div class="space-y-3">
            @foreach($menuStructure as $menuKey => $menu)
                @php $hasPerm = $subAdmin->hasPermission($menuKey); @endphp
                <div class="border border-gray-100 rounded-xl overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="w-2 h-2 rounded-full {{ $hasPerm ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                        <span class="font-semibold text-gray-800 text-sm">{{ $menu['label'] }}</span>
                    </div>
                    @if(!empty($menu['children']))
                        <div class="px-4 py-2 space-y-1">
                            @foreach($menu['children'] as $subKey => $sub)
                                @php $subPerm = $subAdmin->hasPermission("$menuKey.$subKey"); @endphp
                                <div class="py-1">
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $subPerm ? 'bg-green-400' : 'bg-gray-200' }}"></span>
                                        <span class="{{ $subPerm ? 'text-gray-700' : 'text-gray-400' }}">{{ $sub['label'] }}</span>
                                    </div>
                                    @if(!empty($sub['tabs']))
                                        <div class="ml-5 mt-1 flex flex-wrap gap-2">
                                            @foreach($sub['tabs'] as $tabKey => $tab)
                                                @php $tabPerm = $subAdmin->hasPermission("$menuKey.$subKey.$tabKey"); @endphp
                                                <span class="px-2 py-0.5 rounded-full text-xs {{ $tabPerm ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                                                    {{ $tab['label'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
