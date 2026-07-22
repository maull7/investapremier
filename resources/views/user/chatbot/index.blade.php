@extends('layouts.user')

@section('title', 'AI Chatbot')

@section('content')
    <div class="max-w-4xl mx-auto" x-data="chatbot()">
        <div class="bg-white rounded-xl border border-line shadow-sm overflow-hidden">
            {{-- Header --}}
            <div class="px-6 py-4 bg-gradient-to-r from-accent-teal/85 to-accent-teal/95 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-white font-bold text-lg">Asisten Investasi AI</h2>
                    <p class="text-green-100 text-xs">Tanya apapun tentang portofolio Anda</p>
                </div>
            </div>

            {{-- Chat Messages --}}
            <div class="h-[500px] overflow-y-auto p-6 space-y-4" x-ref="chatbox" x-init="$nextTick(() => $refs.chatbox.scrollTop = $refs.chatbox.scrollHeight)">
                <template x-if="messages.length === 0">
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <p class="text-gray-500 text-sm">Tanyakan apapun tentang portofolio dan investasi Anda</p>
                    </div>
                </template>

                <template x-for="(msg, i) in messages" :key="i">
                    <div class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[80%] rounded-2xl px-4 py-3 text-sm leading-relaxed"
                            :class="msg.role === 'user' ?
                                'bg-accent-teal text-white rounded-br-md' :
                                'bg-cardBg-bg text-gray-800 rounded-bl-md'">
                            <p class="whitespace-pre-line" x-text="msg.content"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Input --}}
            <form @submit.prevent="sendMessage" class="border-t border-line p-4 bg-gray-50">
                <div class="flex gap-3">
                    <input type="text" x-model="message" placeholder="Tanya tentang portofolio Anda..."
                        class="flex-1 px-4 py-2.5 rounded-xl border border-line focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none text-sm"
                        :disabled="loading">
                    <button type="submit" :disabled="!message.trim() || loading"
                        class="px-6 py-2.5 bg-accent-teal text-white rounded-xl font-medium text-sm hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="!loading">Kirim</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function chatbot() {
            return {
                message: '',
                loading: false,
                messages: @json($messages),
                sendMessage() {
                    if (!this.message.trim() || this.loading) return;
                    const msg = this.message;
                    this.message = '';
                    this.loading = true;

                    fetch('{{ route('user.chatbot.ask') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                message: msg
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            this.messages = [...this.messages, {
                                role: 'user',
                                content: msg
                            }, {
                                role: 'assistant',
                                content: data.reply
                            }];
                            this.$nextTick(() => {
                                this.$refs.chatbox.scrollTop = this.$refs.chatbox.scrollHeight;
                            });
                        })
                        .catch(() => {
                            this.messages = [...this.messages, {
                                role: 'user',
                                content: msg
                            }, {
                                role: 'assistant',
                                content: 'Maaf, terjadi kesalahan. Silakan coba lagi.'
                            }];
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                }
            }
        }
    </script>
@endpush
