@auth
    @php
        $unreadCount = auth()->user()->unreadNotifications()->count();
        $latest      = auth()->user()->notifications()->latest()->limit(5)->get();
    @endphp

    <div
        x-data="notificationBell({
            initialUnread: {{ (int) $unreadCount }},
            initialLatest: @js(
                $latest->map(fn ($n) => [
                    'id'            => $n->id,
                    'data'          => $n->data,
                    'read_at'       => optional($n->read_at)->toIso8601String(),
                    'created_human' => $n->created_at->diffForHumans(),
                ])
            ),
            urls: {
                fetch: '{{ route('user.notifications.unread') }}',
                read:  '{{ url('/user/notifications') }}',
                readAll: '{{ route('user.notifications.read-all') }}',
                index: '{{ route('user.notifications.index') }}',
            },
        })"
        x-init="start()"
        @keydown.escape.window="open = false"
        class="relative"
    >
        <button
            type="button"
            @@click="open = !open"
            class="relative p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition"
            aria-label="Notifikasi"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span
                x-cloak
                x-show="unread > 0"
                x-text="unread > 99 ? '99+' : unread"
                class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 grid place-items-center text-[10px] font-bold text-white bg-red-500 rounded-full ring-2 ring-white"
            ></span>
        </button>

        <div
            x-cloak
            x-show="open"
            @@click.outside="open = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="absolute right-0 mt-2 w-[360px] max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50"
        >
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-green-50 to-white">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-sm text-gray-900">Notifikasi</span>
                    <span
                        x-cloak
                        x-show="unread > 0"
                        x-text="unread + ' baru'"
                        class="text-[10px] font-bold uppercase tracking-wide bg-red-100 text-red-600 px-2 py-0.5 rounded-full"
                    ></span>
                </div>
                <button
                    type="button"
                    @@click="markAllRead()"
                    x-show="unread > 0"
                    class="text-xs font-semibold text-green-700 hover:text-green-900"
                >Tandai semua dibaca</button>
            </div>

            <div class="max-h-[380px] overflow-y-auto">
                <template x-if="items.length === 0">
                    <div class="px-6 py-12 text-center text-gray-400 text-sm">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        Belum ada notifikasi
                    </div>
                </template>

                <template x-for="item in items" :key="item.id">
                    <a
                        :href="urls.read + '/' + item.id + '/read'"
                        @@click.prevent="openItem(item)"
                        class="block px-4 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition cursor-pointer"
                        :class="!item.read_at ? 'bg-green-50/40' : ''"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="w-9 h-9 rounded-full grid place-items-center shrink-0 text-white"
                                :class="(item.data && item.data.condition === 'below') ? 'bg-red-500' : 'bg-green-600'"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                          x-show="(item.data && item.data.condition !== 'below')"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"
                                          x-show="(item.data && item.data.condition === 'below')"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-gray-900 truncate" x-text="(item.data && item.data.title) || 'Notifikasi'"></div>
                                <div class="text-xs text-gray-500 mt-0.5 line-clamp-2" x-text="(item.data && item.data.message) || ''"></div>
                                <div class="text-[11px] text-gray-400 mt-1" x-text="item.created_human"></div>
                            </div>
                            <span x-show="!item.read_at" class="w-2 h-2 rounded-full bg-green-500 shrink-0 mt-1.5"></span>
                        </div>
                    </a>
                </template>
            </div>

            <a
                :href="urls.index"
                class="block text-center text-xs font-semibold text-green-700 hover:text-green-900 py-3 border-t border-gray-100 bg-gray-50"
            >Lihat semua notifikasi →</a>
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                function notificationBell(config) {
                    return {
                        open: false,
                        unread: config.initialUnread || 0,
                        items: config.initialLatest || [],
                        urls: config.urls,
                        timer: null,

                        start() {
                            this.timer = setInterval(() => this.fetchLatest(), 30000);
                        },

                        async fetchLatest() {
                            try {
                                const res = await fetch(this.urls.fetch, {
                                    headers: { 'Accept': 'application/json' },
                                });
                                if (!res.ok) return;
                                const json = await res.json();
                                this.unread = json.unread_count || 0;
                                this.items  = json.latest || [];
                            } catch (e) { /* silent */ }
                        },

                        openItem(item) {
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                            fetch(this.urls.read + '/' + item.id + '/read', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json',
                                },
                            }).finally(() => {
                                const url = item.data && item.data.url;
                                if (url) {
                                    window.location.href = url;
                                } else {
                                    window.location.href = this.urls.index;
                                }
                            });
                        },

                        async markAllRead() {
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                            await fetch(this.urls.readAll, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json',
                                },
                            });
                            this.unread = 0;
                            this.items = this.items.map(i => ({ ...i, read_at: new Date().toISOString() }));
                        },
                    };
                }
            </script>
        @endpush
    @endonce
@endauth
