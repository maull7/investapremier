{{--
  Partial: _permission_tree
  Requires: $menuStructure, $selected (array)

  Strategi input naming:
  - Menu tanpa children: permissions[menu_key]
  - Menu dengan children, sub tanpa tabs: permissions[menu][sub]
  - Sub dengan tabs: permissions[menu][sub][tab]
  - Parent checkbox hanya untuk UX (select/deselect all children), TIDAK submit data sendiri
    kecuali jika benar-benar tidak ada children sama sekali.
--}}
@php
if (!function_exists('permChecked')) {
    function permChecked(array $perms, string $key): bool {
        $parts = explode('.', $key);
        $cur = $perms;
        foreach ($parts as $p) {
            if (!is_array($cur) || !array_key_exists($p, $cur)) return false;
            $v = $cur[$p];
            if ($v === true || $v === 1 || $v === '1') return true;
            if ($v === false) return false;
            $cur = $v;
        }
        return $cur === true || $cur === 1 || $cur === '1' || (is_array($cur) && !empty($cur));
    }
}
@endphp

<div x-data="{
    search: '',
    openMenus: @js(array_fill_keys(array_keys($menuStructure), true)),

    toggleAll(checked) {
        document.querySelectorAll('.perm-leaf').forEach(cb => cb.checked = checked);
        document.querySelectorAll('.perm-ui').forEach(cb => {
            cb.checked = checked;
            cb.indeterminate = false;
        });
    },

    syncParent(mk, sk) {
        // Sync sub-checkbox dari tabs
        if (sk) {
            const tabs = [...document.querySelectorAll(`.perm-leaf[data-mk='${mk}'][data-sk='${sk}']`)];
            const scb = document.querySelector(`.perm-ui[data-mk='${mk}'][data-sk='${sk}']`);
            if (tabs.length && scb) {
                const n = tabs.filter(t => t.checked).length;
                scb.indeterminate = n > 0 && n < tabs.length;
                scb.checked = n === tabs.length;
                if (n === 0) scb.indeterminate = false;
            }
        }
        // Sync menu-checkbox dari subs (leaves + sub-ui-only)
        const subs = [...document.querySelectorAll(`.perm-ui[data-mk='${mk}'], .perm-leaf[data-mk='${mk}']`)];
        const mcb = document.querySelector(`.perm-ui-menu[data-mk='${mk}']`);
        if (subs.length && mcb) {
            const checked = subs.filter(c => c.checked && !c.indeterminate).length;
            const total = subs.length;
            mcb.indeterminate = checked > 0 && checked < total;
            mcb.checked = checked === total;
            if (checked === 0) mcb.indeterminate = false;
        }
    }
}" x-init="
    // Init indeterminate state on load
    document.querySelectorAll('.perm-ui[data-sk]').forEach(scb => {
        const mk = scb.dataset.mk, sk = scb.dataset.sk;
        const tabs = [...document.querySelectorAll(\`.perm-leaf[data-mk='\${mk}'][data-sk='\${sk}']\`)];
        if (tabs.length) {
            const n = tabs.filter(t => t.checked).length;
            scb.indeterminate = n > 0 && n < tabs.length;
            scb.checked = n === tabs.length;
            if (n === 0) scb.indeterminate = false;
        }
    });
    document.querySelectorAll('.perm-ui-menu').forEach(mcb => {
        const mk = mcb.dataset.mk;
        const subs = [...document.querySelectorAll(\`.perm-ui[data-mk='\${mk}'], .perm-leaf[data-mk='\${mk}']\`)];
        if (subs.length) {
            const n = subs.filter(c => c.checked && !c.indeterminate).length;
            mcb.indeterminate = n > 0 && n < subs.length;
            mcb.checked = n === subs.length;
            if (n === 0) mcb.indeterminate = false;
        }
    });
" class="space-y-3">

    {{-- Toolbar --}}
    <div class="flex flex-wrap gap-2 mb-3">
        <input type="text" x-model="search" placeholder="Cari menu..."
            class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs w-40 outline-none focus:border-green-400"/>
        <button type="button" @click="toggleAll(true)"
            class="px-3 py-1.5 text-xs rounded-lg bg-green-50 text-green-700 font-medium hover:bg-green-100">Check All</button>
        <button type="button" @click="toggleAll(false)"
            class="px-3 py-1.5 text-xs rounded-lg bg-gray-100 text-gray-600 font-medium hover:bg-gray-200">Uncheck All</button>
        <button type="button" @click="Object.keys(openMenus).forEach(k=>openMenus[k]=true)"
            class="px-3 py-1.5 text-xs rounded-lg bg-gray-100 text-gray-600 font-medium hover:bg-gray-200">Expand All</button>
        <button type="button" @click="Object.keys(openMenus).forEach(k=>openMenus[k]=false)"
            class="px-3 py-1.5 text-xs rounded-lg bg-gray-100 text-gray-600 font-medium hover:bg-gray-200">Collapse All</button>
    </div>

    @foreach($menuStructure as $mk => $menu)
        @php
            $hasChildren = !empty($menu['children']);
        @endphp
        <div class="border border-gray-100 rounded-xl overflow-hidden"
            x-show="search === '' || '{{ strtolower($menu['label']) }}'.includes(search.toLowerCase())">

            {{-- Menu header --}}
            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 cursor-pointer select-none"
                @click="openMenus['{{ $mk }}'] = !openMenus['{{ $mk }}']">

                {{-- UI-only checkbox (tidak submit data), kecuali menu tanpa children --}}
                @if($hasChildren)
                    {{-- UI-only, initial state di-handle Alpine x-init --}}
                    <input type="checkbox"
                        class="perm-ui perm-ui-menu w-4 h-4 accent-green-600"
                        data-mk="{{ $mk }}"
                        @click.stop
                        @change="
                            const chk = $event.target.checked;
                            $event.target.indeterminate = false;
                            document.querySelectorAll(`.perm-ui[data-mk='{{ $mk }}'], .perm-leaf[data-mk='{{ $mk }}']`).forEach(c => {
                                c.checked = chk;
                                c.indeterminate = false;
                            });
                        "/>
                @else
                    {{-- Menu tanpa children = leaf, submit data --}}
                    <input type="checkbox"
                        name="permissions[{{ $mk }}]" value="1"
                        class="perm-leaf perm-ui-menu w-4 h-4 accent-green-600"
                        data-mk="{{ $mk }}"
                        {{ permChecked($selected, $mk) ? 'checked' : '' }}
                        @click.stop
                        @change="syncParent('{{ $mk }}', null)"/>
                @endif

                <label class="font-semibold text-gray-800 text-sm flex-1 cursor-pointer" @click.stop>
                    {{ $menu['label'] }}
                </label>

                @if($hasChildren)
                    <svg class="w-4 h-4 text-gray-400 transition-transform"
                        :class="openMenus['{{ $mk }}'] ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                @endif
            </div>

            @if($hasChildren)
                <div x-show="openMenus['{{ $mk }}']" x-transition class="divide-y divide-gray-50 px-4 py-2">
                    @foreach($menu['children'] as $sk => $sub)
                        @php $hasTabs = !empty($sub['tabs']); @endphp
                        <div class="py-2">
                            <div class="flex items-center gap-3">
                                @if($hasTabs)
                                    {{-- UI-only, initial state di-handle Alpine x-init --}}
                                    <input type="checkbox"
                                        class="perm-ui w-4 h-4 accent-green-600"
                                        data-mk="{{ $mk }}" data-sk="{{ $sk }}"
                                        @change="
                                            const chk = $event.target.checked;
                                            $event.target.indeterminate = false;
                                            document.querySelectorAll(`.perm-leaf[data-mk='{{ $mk }}'][data-sk='{{ $sk }}']`).forEach(c => c.checked = chk);
                                            syncParent('{{ $mk }}', '{{ $sk }}');
                                        "/>
                                @else
                                    {{-- Sub tanpa tabs = leaf --}}
                                    <input type="checkbox"
                                        name="permissions[{{ $mk }}][{{ $sk }}]" value="1"
                                        class="perm-leaf perm-ui w-4 h-4 accent-green-600"
                                        data-mk="{{ $mk }}" data-sk="{{ $sk }}"
                                        {{ permChecked($selected, "$mk.$sk") ? 'checked' : '' }}
                                        @change="syncParent('{{ $mk }}', '{{ $sk }}')"/>
                                @endif
                                <label class="text-sm text-gray-700 cursor-pointer">{{ $sub['label'] }}</label>
                            </div>

                            @if($hasTabs)
                                <div class="ml-7 mt-2 grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                                    @foreach($sub['tabs'] as $tk => $tab)
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox"
                                                name="permissions[{{ $mk }}][{{ $sk }}][{{ $tk }}]" value="1"
                                                class="perm-leaf w-3.5 h-3.5 accent-green-600"
                                                data-mk="{{ $mk }}" data-sk="{{ $sk }}" data-tk="{{ $tk }}"
                                                {{ permChecked($selected, "$mk.$sk.$tk") ? 'checked' : '' }}
                                                @change="syncParent('{{ $mk }}', '{{ $sk }}')"/>
                                            <label class="text-xs text-gray-600 cursor-pointer">{{ $tab['label'] }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    @error('permissions')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
