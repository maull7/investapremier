<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'InvestaPremier'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important
        }

        body {
            font-family: 'Poppins', sans-serif !important
        }

        /* ════════════════════════════════════════
           GLOBAL UI SYSTEM — gunakan di semua halaman
        ════════════════════════════════════════ */

        /* ── Page Header ── */
        .page-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -.02em
        }

        .page-sub {
            font-size: 13.5px;
            color: #64748b;
            margin-top: 3px
        }

        /* ── Table card wrapper ── */
        .table-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05)
        }

        /* ── Table HEAD (gradient hijau gelap) ── */
        .table-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            background: linear-gradient(135deg, #0f172a 0%, #1a2744 100%);
            border-bottom: 1px solid rgba(255, 255, 255, .07);
        }

        .table-head h2,
        .table-head .th-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
        }

        .table-head h2 svg,
        .table-head .th-title svg {
            width: 17px;
            height: 17px;
            stroke: #4ade80;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }

        .table-head .th-meta {
            font-size: 12px;
            color: rgba(255, 255, 255, .45);
            font-weight: 500
        }

        /* TH inside table */
        table thead tr {
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9
        }

        table thead th {
            padding: 11px 20px;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .07em;
            text-align: left;
        }

        table tbody tr {
            border-bottom: 1px solid #f8fafc;
            transition: background .15s
        }

        table tbody tr:hover {
            background: #f8fafc
        }

        table tbody td {
            padding: 13px 20px;
            font-size: 13.5px;
            color: #334155;
            vertical-align: middle
        }

        /* ── Button variants ── */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 18px;
            border-radius: 9px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: #fff;
            box-shadow: 0 2px 10px rgba(22, 163, 74, .28);
        }

        .btn-primary:hover {
            box-shadow: 0 4px 18px rgba(22, 163, 74, .4);
            transform: translateY(-1px);
            color: #fff
        }

        .btn-primary svg {
            width: 15px;
            height: 15px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 18px;
            border-radius: 9px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
            background: #fff;
            color: #334155;
            border: 1.5px solid #e2e8f0;
        }

        .btn-secondary:hover {
            border-color: #16a34a;
            color: #16a34a;
            background: #f0fdf4
        }

        .btn-secondary svg {
            width: 15px;
            height: 15px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0
        }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 16px;
            border-radius: 9px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
            background: transparent;
            color: #16a34a;
            border: 1.5px solid #bbf7d0;
        }

        .btn-outline:hover {
            background: #f0fdf4;
            border-color: #16a34a;
            color: #15803d
        }

        .btn-outline svg {
            width: 15px;
            height: 15px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0
        }

        .btn-danger {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
            background: #fff0f0;
            color: #ef4444;
            border: 1.5px solid #fecaca;
        }

        .btn-danger:hover {
            background: #fee2e2;
            border-color: #ef4444
        }

        .btn-danger svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0
        }

        .btn-sm {
            padding: 7px 13px !important;
            font-size: 12px !important
        }

        .btn-icon {
            padding: 8px !important;
            border-radius: 8px !important
        }

        /* ── Alert / flash ── */
        .alert-success {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 10px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 18px
        }

        .alert-success svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0
        }

        .alert-error {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 10px;
            background: #fff0f0;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 18px
        }

        /* ── Sidebar Dark (User) ── */
        .sidebar-dark {
            background: #ffffff;
            border-right-color: rgba(23, 212, 105, .15) !important;
        }

        .nav-item {
            color: rgb(0, 0, 0);
            transition: all .15s ease;
            text-decoration: none;
        }

        .nav-item:hover {
            background: #035863;
            color: #F3F6F7;
        }

        .nav-item-active {
            background: #F3F6F7;
            color: #035863;
            font-weight: 600;
            border: 1px solid rgba(3, 88, 99, 0.08);
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.04),
                0 6px 16px rgba(3, 88, 99, 0.10);
        }

        .nav-item-active:hover {
            background: #035863;
            color: #F3F6F7;
        }

        /* ── Header ── */
        .header-top {
            position: relative;
        }

        .header-top::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #17D469, #14b8a6);
            z-index: 1;
        }

        /* ── Sidebar Utility ── */
        .sidebar-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .scrollbar-thin::-webkit-scrollbar {
            width: 3px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .1);
            border-radius: 999px;
        }

        /* ── Sidebar ── */
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            border-left: 3px solid transparent;
            transition: all .15s ease;
            font-size: 13px;
            color: #4b5563
        }

        .sidebar-item:hover {
            background: #f9fafb;
            color: #111827;
            transform: translateX(2px)
        }

        .sidebar-item-active,
        .sidebar-item.router-link-active {
            background: #f0fdf4;
            color: #16a34a;
            font-weight: 600;
            border-left-color: #16a34a
        }

        .sidebar-item-active:hover {
            background: #f0fdf4;
            color: #16a34a;
            transform: none
        }

        .sidebar-sub {
            font-size: 12.5px;
            padding: 7px 12px 7px 16px
        }

        .sidebar-sub:hover {
            transform: translateX(1px)
        }

        .sidebar-separator {
            height: 1px;
            background: linear-gradient(to right, transparent, #e5e7eb, transparent);
            margin: 6px 12px
        }

        .sidebar-connector {
            position: relative
        }

        .sidebar-connector::before {
            content: '';
            position: absolute;
            left: 18px;
            top: 0;
            bottom: 0;
            width: 1px;
            background: #e5e7eb;
            border-radius: 1px
        }

        .sidebar-connector>*:last-child::after {
            display: none
        }

        /* ── Sidebar Collapsed ── */
        .sidebar-collapsed .sidebar-label {
            display: none
        }

        .sidebar-collapsed .sidebar-item {
            justify-content: center;
            padding: 9px 0;
            gap: 0;
            border-left-width: 0
        }

        .sidebar-collapsed .sidebar-item-active {
            border-left-width: 3px
        }

        .sidebar-collapsed .sidebar-item>svg:last-child {
            display: none
        }

        .sidebar-collapsed .sidebar-connector {
            display: none
        }

        .sidebar-collapsed .sidebar-sub {
            display: none
        }

        .sidebar-collapsed .sidebar-separator {
            margin: 4px 0
        }

        .sidebar-collapsed nav {
            padding: 8px 4px
        }

        /* ── Empty state ── */
        .empty-state {
            padding: 56px 24px;
            text-align: center;
            color: #94a3b8
        }

        .empty-state svg {
            width: 44px;
            height: 44px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.2;
            margin: 0 auto 12px
        }

        .empty-state p {
            font-size: 14px
        }

        /* ── Delete Modal ── */
        #delete-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(0, 0, 0, .45);
            backdrop-filter: blur(4px);
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s;
        }

        #delete-modal.show {
            opacity: 1;
            pointer-events: auto
        }

        #delete-modal .dm-box {
            background: #fff;
            border-radius: 16px;
            padding: 28px 32px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, .18);
            transform: scale(.95) translateY(8px);
            transition: transform .22s;
        }

        #delete-modal.show .dm-box {
            transform: scale(1) translateY(0)
        }

        #delete-modal .dm-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            display: grid;
            place-items: center;
            color: #ef4444;
            margin-bottom: 16px;
        }

        #delete-modal .dm-icon svg {
            width: 24px;
            height: 24px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round
        }

        #delete-modal h3 {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px
        }

        #delete-modal p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 22px
        }

        #delete-modal .dm-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end
        }

        .dm-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all .18s;
        }

        .dm-btn-cancel {
            background: #f1f5f9;
            color: #334155;
            border-color: #e2e8f0
        }

        .dm-btn-cancel:hover {
            background: #e2e8f0
        }

        .dm-btn-delete {
            background: #ef4444;
            color: #fff;
            border-color: #ef4444;
            box-shadow: 0 2px 10px rgba(239, 68, 68, .25)
        }

        .dm-btn-delete:hover {
            background: #dc2626;
            border-color: #dc2626;
            box-shadow: 0 4px 16px rgba(239, 68, 68, .35)
        }
    </style>
</head>

<body class="font-sans antialiased">
    @yield('header')
    @yield('body')

    {{-- ═══ Global Delete Confirmation Modal ═══
         Usage: <button onclick="confirmDelete(this)" data-action="/route/to/delete" data-label="Nama Item">Hapus</button>
         Or with a form: data-form-id="form-delete-123"
    --}}
    <div id="delete-modal">
        <div class="dm-box">
            <div class="dm-icon">
                <svg viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                    <path d="M10 11v6" />
                    <path d="M14 11v6" />
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                </svg>
            </div>
            <h3 id="dm-title">Hapus Data?</h3>
            <p id="dm-desc">Tindakan ini tidak dapat dibatalkan. Data akan dihapus secara permanen.</p>
            <div class="dm-actions">
                <button class="dm-btn dm-btn-cancel" onclick="closeDeleteModal()">Batal</button>
                <form id="dm-form" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dm-btn dm-btn-delete">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    @stack('scripts')

    <script>
        function confirmDelete(btn, options = {}) {
            const modal = document.getElementById('delete-modal');
            const form = document.getElementById('dm-form');
            const title = document.getElementById('dm-title');
            const desc = document.getElementById('dm-desc');

            const action = btn?.dataset?.action || options.action || '#';
            const label = btn?.dataset?.label || options.label || 'item ini';

            title.textContent = options.title || 'Hapus Data?';
            desc.textContent = `Data "${label}" akan dihapus permanen dan tidak dapat dikembalikan.`;
            form.action = action;

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.remove('show');
            document.body.style.overflow = '';
        }

        // Close on backdrop click
        document.getElementById('delete-modal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeDeleteModal();
        });
    </script>
</body>

</html>
