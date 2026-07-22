@extends('layouts.user')

@section('title', 'Dashboard — InvestaPremier')

@section('content')
    <style>
        .stat-card {
            border-radius: 16px;
            padding: 22px;
            position: relative;
            overflow: hidden;
            color: #fff
        }

        .stat-card h3 {
            font-size: 12px;
            font-weight: 600;
            opacity: .8;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 8px
        }

        .stat-card .val {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -.02em
        }

        .stat-card .sub {
            font-size: 12px;
            opacity: .75;
            margin-top: 4px
        }

        .stat-card .card-icon {
            position: absolute;
            right: -10px;
            bottom: -10px;
            opacity: .15;
            width: 80px;
            height: 80px
        }

        .stat-card .card-icon svg {
            width: 80px;
            height: 80px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.2
        }

        .g1 {
            background: linear-gradient(135deg, #035863 0%, #0a5c56 100%);
            box-shadow: 0 8px 24px rgba(22, 163, 74, .3)
        }

        .g2 {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            box-shadow: 0 8px 24px rgba(15, 23, 42, .25)
        }

        .g3 {
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            box-shadow: 0 8px 24px rgba(8, 145, 178, .25)
        }

        .g4 {
            background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
            box-shadow: 0 8px 24px rgba(124, 58, 237, .25)
        }

        .btn-ip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 18px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 13px;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none
        }

        .btn-ip svg {
            width: 15px;
            height: 15px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0
        }

        .btn-green {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 2px 10px rgba(22, 163, 74, .28)
        }

        .btn-green:hover {
            box-shadow: 0 4px 18px rgba(22, 163, 74, .4);
            transform: translateY(-1px)
        }

        .btn-outline-green {
            background: transparent;
            color: #16a34a;
            border-color: #bbf7d0
        }

        .btn-outline-green:hover {
            background: #f0fdf4;
            border-color: #16a34a
        }

        .btn-outline-dark {
            background: transparent;
            color: #334155;
            border-color: #e2e8f0
        }

        .btn-outline-dark:hover {
            background: #f8fafc;
            border-color: #cbd5e1
        }

        .prog-track {
            height: 8px;
            background: #f1f5f9;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 8px
        }

        .prog-fill {
            height: 100%;
            background: linear-gradient(90deg, #16a34a, #22c55e);
            border-radius: 999px;
            transition: width .5s
        }

        .sec-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px
        }

        .sec-title svg {
            width: 18px;
            height: 18px;
            stroke: #16a34a;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round
        }

        .widget-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #f1f5f9;
            padding: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04)
        }

        .chart-container {
            position: relative;
            height: 220px;
            width: 100%
        }

        .ticker-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0 20px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
            border-right: 1px solid #e2e8f0
        }

        .ticker-item .tv {
            color: #0f172a;
            font-weight: 700
        }

        .ticker-item .tu {
            color: #16a34a
        }

        .ticker-item .td {
            color: #ef4444
        }

        @keyframes tickerScroll {
            0% {
                transform: translateX(0)
            }

            100% {
                transform: translateX(-50%)
            }
        }

        .ticker-track {
            display: flex;
            width: max-content;
            animation: tickerScroll 30s linear infinite
        }

        .wealth-meter {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto
        }

        .wealth-meter svg {
            transform: rotate(-90deg)
        }

        .wealth-meter .wm-bg {
            fill: none;
            stroke: #f1f5f9;
            stroke-width: 10
        }

        .wealth-meter .wm-fill {
            fill: none;
            stroke: url(#wmGrad);
            stroke-width: 10;
            stroke-linecap: round;
            transition: stroke-dashoffset 1.5s ease
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>

    {{-- Welcome Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900" style="letter-spacing:-.02em">
                <span x-text="$root.greeting ? $root.greeting + ',' : 'Halo,'"></span>
                <span class="text-accent-dark">{{ Auth::user()->name }}</span>
            </h1>
            <p class="text-gray-500 text-sm mt-1">Ini ringkasan portfolio Anda hari ini.</p>
        </div>
        <a href="{{ route('user.perencanaan-investasi.create') }}"
            class="btn-ip bg-accent-teal text-line hidden sm:inline-flex">
            <svg viewBox="0 0 24 24">
                <path d="M12 5v14" />
                <path d="M5 12h14" />
            </svg>
            Buat Rencana
        </a>
    </div>



    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card g1">
            <h3>Total Kekayaan</h3>
            <div class="val">{{ $totalKekayaanFormatted ?? 'Rp 0' }}</div>
            <div class="sub flex items-center gap-1">
                <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"
                    viewBox="0 0 24 24">
                    <path d="M5 15l7-7 7 7" />
                </svg>
                +{{ $totalKekayaanGrowth ?? 0 }}% bulan ini
            </div>
            <div class="card-icon"><svg viewBox="0 0 24 24">
                    <path
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg></div>
        </div>
        <div class="stat-card g2">
            <h3>Aset Investasi</h3>
            <div class="val">{{ $asetInvestasiFormatted ?? 'Rp 0' }}</div>
            <div class="sub">{{ $asetInvestasiPct ?? 0 }}% dari kekayaan</div>
            <div class="card-icon"><svg viewBox="0 0 24 24">
                    <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg></div>
        </div>
        <div class="stat-card g3">
            <h3>Likuiditas</h3>
            <div class="val">{{ $likuiditasFormatted ?? 'Rp 0' }}</div>
            <div class="sub">{{ $likuiditasPct ?? 0 }}% dari kekayaan</div>
            <div class="card-icon"><svg viewBox="0 0 24 24">
                    <path
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg></div>
        </div>
        <div class="stat-card g4">
            <h3>Next Review</h3>
            <div class="val text-xl">{{ $nextReview ?? '—' }}</div>
            <div class="sub">{{ $nextReviewDays !== null ? "Dalam {$nextReviewDays} hari" : 'Belum ada jadwal' }}</div>
            <div class="card-icon"><svg viewBox="0 0 24 24">
                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg></div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
        <div class="widget-card">
            <div class="flex items-center justify-between mb-4">
                <div class="sec-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Portfolio Growth
                </div>
                <span class="text-xs text-gray-400">6 bulan terakhir</span>
            </div>
            <div class="chart-container">
                <canvas id="portfolioChart"></canvas>
            </div>
        </div>
        <div class="widget-card">
            <div class="flex items-center justify-between mb-4">
                <div class="sec-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                    Asset Allocation
                </div>
                <span class="text-xs text-gray-400">Portofolio</span>
            </div>
            <div class="chart-container">
                <canvas id="allocationChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
        {{-- Asset Allocation Detail (progress bars) --}}
        <div class="widget-card">
            <div class="sec-title mb-4">
                <svg viewBox="0 0 24 24">
                    <path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                    <path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                </svg>
                Alokasi Detail
            </div>
            @if (count($alokasiAset ?? []) > 0)
                <div class="space-y-4">
                    @foreach ($alokasiAset as $item)
                        <div>
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="font-medium text-gray-700">{{ $item['label'] }}</span>
                                <span class="font-bold text-gray-900">{{ $item['pct'] }}%</span>
                            </div>
                            <div class="prog-track">
                                <div class="prog-fill bg-gradient-to-r {{ $item['warna'] ?? 'from-green-500 to-emerald-400' }}"
                                    style="width:{{ $item['pct'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-sm text-gray-400">Belum ada data portfolio.</p>
                    <a href="{{ route('user.perencanaan-investasi.create') }}"
                        class="btn-ip btn-green mt-3 inline-flex">Buat Rencana Investasi</a>
                </div>
            @endif
        </div>

        {{-- Wealth Health Score + Recent Activity --}}
        <div class="widget-card">
            <div class="sec-title mb-4">
                <svg viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Wealth Health
            </div>
            <div class="flex flex-col items-center">
                <div class="wealth-meter" x-data="{
                    score: {{ $wealthHealthScore ?? 65 }},
                    circumference: 2 * Math.PI * 55,
                    init() {
                        setTimeout(() => {
                            this.$refs.fill.style.strokeDashoffset = this.circumference - (this.score / 100) * this.circumference
                        }, 300)
                    }
                }">
                    <svg width="140" height="140" viewBox="0 0 140 140">
                        <defs>
                            <linearGradient id="wmGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#f59e0b" />
                                <stop offset="50%" stop-color="#22c55e" />
                                <stop offset="100%" stop-color="#16a34a" />
                            </linearGradient>
                        </defs>
                        <circle class="wm-bg" cx="70" cy="70" r="55" />
                        <circle x-ref="fill" class="wm-fill" cx="70" cy="70" r="55"
                            stroke-dasharray="345.58" stroke-dashoffset="345.58"
                            style="transition:stroke-dashoffset 1.5s ease" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-extrabold text-gray-900" x-text="score"></span>
                        <span class="text-xs text-gray-400 font-medium">/100</span>
                    </div>
                </div>
                <div class="text-center mt-2">
                    <div class="text-sm font-semibold text-gray-800"
                        x-text="score >= 80 ? 'Sangat Sehat' : score >= 60 ? 'Cukup Sehat' : 'Perlu Perhatian'"></div>
                    <div class="text-xs text-gray-400 mt-1">Skor kesehatan portfolio Anda</div>
                </div>
            </div>
        </div>

        {{-- Alert Center --}}
        <div class="widget-card">
            <div class="sec-title mb-4">
                <svg viewBox="0 0 24 24">
                    <path d="M12 9v4" />
                    <path d="M12 17h.01" />
                    <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z" />
                </svg>
                Alert Center
            </div>
            @if (count($alerts ?? []) > 0)
                <div class="space-y-3">
                    @foreach ($alerts as $alert)
                        <div
                            class="flex items-start gap-3 p-3 rounded-xl bg-{{ $alert['bgColor'] ?? 'amber' }}-50 border border-{{ $alert['borderColor'] ?? 'amber' }}-100">
                            <svg style="width:15px;height:15px;margin-top:1px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"
                                class="text-{{ $alert['textColor'] ?? 'amber' }}-600" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8v4m0 4h.01" />
                            </svg>
                            <span
                                class="text-sm leading-relaxed text-{{ $alert['textColor'] ?? 'amber' }}-700">{{ $alert['message'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-12 h-12 rounded-full bg-green-100 text-green-500 grid place-items-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-400">Semua aman, tidak ada alert.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Goal Progress --}}
        <div class="widget-card">
            <div class="flex items-center justify-between mb-4">
                <div class="sec-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Goal Progress
                </div>
                <a href="{{ route('user.perencanaan-investasi.index') }}" class="btn-ip btn-outline-green"
                    style="padding:6px 12px;font-size:12px">Kelola</a>
            </div>
            @if (count($goals ?? []) > 0)
                <div class="space-y-5">
                    @foreach ($goals as $goal)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-semibold text-gray-800">{{ $goal['nama'] }}</span>
                                <span class="font-bold text-green-600">{{ $goal['pct'] }}%</span>
                            </div>
                            <div class="prog-track" style="height:10px">
                                <div class="prog-fill" style="width:{{ $goal['pct'] }}%;position:relative">
                                    <div
                                        style="position:absolute;right:-1px;top:50%;transform:translateY(-50%);width:14px;height:14px;background:#fff;border-radius:50%;border:2px solid #16a34a;box-shadow:0 1px 4px rgba(22,163,74,.3)">
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-400 mt-1">
                                <span>Target: {{ $goal['targetFormatted'] }}</span>
                                <span>Terkumpul: {{ $goal['terkumpulFormatted'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-sm text-gray-400">Belum ada goal investasi.</p>
                    <a href="{{ route('user.perencanaan-investasi.create') }}"
                        class="btn-ip btn-green mt-3 inline-flex">Buat Goal Baru</a>
                </div>
            @endif
        </div>

        {{-- Advisor Notes --}}
        <div class="widget-card">
            <div class="sec-title mb-4">
                <svg viewBox="0 0 24 24">
                    <path
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Advisor Notes
            </div>
            @if ($advisor ?? null)
                <div class="rounded-xl p-4 border border-green-100"
                    style="background:linear-gradient(135deg,#f0fdf4,#dcfce7)">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full flex-shrink-0 text-white text-xs font-bold grid place-items-center"
                            style="background:linear-gradient(135deg,#16a34a,#22c55e)">{{ $advisor['initial'] }}</div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $advisor['name'] }}</div>
                            <p class="text-sm text-gray-600 mt-1 leading-relaxed">Advisor Anda siap membantu. Hubungi untuk
                                konsultasi portfolio.</p>
                            <div class="text-xs text-green-600 font-semibold mt-2">Terhubung</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-xl p-4 border border-gray-100 bg-gray-50 text-center">
                    <p class="text-sm text-gray-400">Belum terhubung dengan advisor.</p>
                    <a href="{{ route('user.clients.requests.index') }}"
                        class="text-sm font-semibold text-green-600 hover:underline mt-2 inline-block">Cari Advisor</a>
                </div>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div class="widget-card">
            <div class="sec-title mb-4">
                <svg viewBox="0 0 24 24">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Quick Actions
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <a href="{{ route('user.perencanaan-investasi.create') }}"
                    class="flex flex-col items-center gap-1.5 p-3.5 rounded-xl border border-green-100 text-green-700 hover:bg-green-50 transition group"
                    style="background:linear-gradient(135deg,#f0fdf4,#dcfce7)">
                    <svg style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"
                        viewBox="0 0 24 24">
                        <path d="M12 5v14" />
                        <path d="M5 12h14" />
                    </svg>
                    <span class="text-xs font-semibold text-center leading-tight">Tambah Goal</span>
                </a>
                <a href="{{ route('user.reksa-dana.index') }}"
                    class="flex flex-col items-center gap-1.5 p-3.5 rounded-xl border border-blue-100 text-blue-700 hover:bg-blue-50 transition"
                    style="background:linear-gradient(135deg,#eff6ff,#dbeafe)">
                    <svg style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"
                        viewBox="0 0 24 24">
                        <path d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span class="text-xs font-semibold text-center leading-tight">Reksa Dana</span>
                </a>
                <a href="{{ route('user.analisa.index') }}"
                    class="flex flex-col items-center gap-1.5 p-3.5 rounded-xl border border-amber-100 text-amber-700 hover:bg-amber-50 transition"
                    style="background:linear-gradient(135deg,#fffbeb,#fef3c7)">
                    <svg style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"
                        viewBox="0 0 24 24">
                        <path
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                    </svg>
                    <span class="text-xs font-semibold text-center leading-tight">Analisa RD</span>
                </a>
                <a href="{{ route('user.analisa-saham.index') }}"
                    class="flex flex-col items-center gap-1.5 p-3.5 rounded-xl border border-purple-100 text-purple-700 hover:bg-purple-50 transition"
                    style="background:linear-gradient(135deg,#faf5ff,#ede9fe)">
                    <svg style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"
                        viewBox="0 0 24 24">
                        <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span class="text-xs font-semibold text-center leading-tight">Analisa Saham</span>
                </a>
            </div>
            <a href="{{ route('user.laporan-portfolio.pdf') }}"
                class="mt-3 flex items-center justify-center gap-2 w-full p-3 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 transition text-sm font-semibold">
                <svg style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"
                    viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                Download Laporan PDF
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Portfolio Growth Chart
            const pCtx = document.getElementById('portfolioChart');
            if (pCtx && typeof Chart !== 'undefined') {
                new Chart(pCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                        datasets: [{
                            label: 'Portfolio Value',
                            data: {{ json_encode($portfolioGrowth ?? []) }},
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22,163,74,.08)',
                            fill: true,
                            tension: .4,
                            pointBackgroundColor: '#16a34a',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                grid: {
                                    color: 'rgba(0,0,0,.04)'
                                },
                                ticks: {
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Allocation Doughnut Chart
            const aCtx = document.getElementById('allocationChart');
            if (aCtx && typeof Chart !== 'undefined') {
                const allocData = @json($alokasiAset ?? []);
                if (allocData.length) {
                new Chart(aCtx, {
                    type: 'doughnut',
                    data: {
                        labels: allocData.map(a => a.label),
                        datasets: [{
                            data: allocData.map(a => a.pct),
                            backgroundColor: ['#16a34a', '#0f766e', '#0891b2', '#7c3aed',
                                '#f59e0b'
                            ],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 12,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        }
                    }
                });
                }
            }
        });
    </script>
@endpush
