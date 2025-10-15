<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTSoft - Fatura </title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-dark: #1a1a1a;
            --primary-light: #ffffff;
            --bg-light: #f8f8f8;
            --border-light: #e5e5e5;
            --text-muted: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-light);
            color: var(--primary-dark);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
        }

        /* Navbar */
        .navbar {
            background-color: var(--primary-light) !important;
            border-bottom: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-dark) !important;
            letter-spacing: -0.5px;
        }

        .btn-outline-dark {
            color: var(--primary-dark);
            border-color: var(--border-light);
            background-color: transparent;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-outline-dark:hover {
            background-color: var(--primary-dark);
            color: var(--primary-light);
            border-color: var(--primary-dark);
        }

        .btn-dark {
            background-color: var(--primary-dark) !important;
            border-color: var(--primary-dark) !important;
            color: var(--primary-light) !important;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-dark:hover {
            background-color: #333333 !important;
            border-color: #333333 !important;
        }

        .btn-dark:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 6px;
        }

        /* Card */
        .card {
            border: none !important;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            background-color: var(--primary-light);
        }

        .card-header {
            background-color: var(--primary-light) !important;
            border-bottom: 1px solid var(--border-light) !important;
            padding: 24px !important;
        }

        .card-header h4 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.25rem !important;
        }

        .card-header p {
            color: var(--text-muted) !important;
            font-size: 0.85rem;
            margin-bottom: 0 !important;
        }

        /* Table */
        .table-responsive {
            border-radius: 12px;
        }

        .table {
            margin-bottom: 0 !important;
        }

        .table thead {
            background-color: #f5f5f5;
        }

        .table thead th {
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none !important;
            padding: 14px 12px !important;
            background-color: #f5f5f5;
        }

        .table tbody td {
            border-color: var(--border-light);
            padding: 13px 12px !important;
            color: var(--primary-dark);
            vertical-align: middle;
        }

        .table tbody tr {
            transition: background-color 0.15s ease;
        }

        .table tbody tr:hover {
            background-color: #fafafa !important;
        }

        .table-hover tbody tr:hover {
            background-color: #fafafa;
        }

        /* Status Badge */
        .badge {
            border-radius: 20px;
            padding: 5px 10px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-success {
            background-color: #10b981;
            color: white;
        }

        .badge-warning {
            background-color: #f59e0b;
            color: white;
        }

        .badge-danger {
            background-color: #ef4444;
            color: white;
        }

        .badge-info {
            background-color: #3b82f6;
            color: white;
        }

        .badge-secondary {
            background-color: #8b5cf6;
            color: white;
        }

        /* DataTables */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin: 0;
        }

        .dataTables_filter input {
            border: 1px solid var(--border-light) !important;
            border-radius: 6px !important;
            padding: 6px 12px !important;
            font-size: 0.9rem;
            color: var(--primary-dark);
        }

        .dataTables_filter input::placeholder {
            color: var(--text-muted);
        }

        .dataTables_filter input:focus {
            border-color: var(--primary-dark) !important;
            box-shadow: 0 0 0 3px rgba(26, 26, 26, 0.1) !important;
            outline: none;
        }

        .dataTables_length select {
            border: 1px solid var(--border-light) !important;
            border-radius: 6px !important;
            padding: 5px 8px !important;
            font-size: 0.9rem;
            color: var(--primary-dark);
        }

        .dataTables_length select:focus {
            border-color: var(--primary-dark) !important;
            box-shadow: 0 0 0 3px rgba(26, 26, 26, 0.1) !important;
            outline: none;
        }

        .dataTables_paginate .pagination {
            margin: 0;
        }

        .dataTables_paginate .pagination .page-link {
            color: var(--primary-dark);
            border-color: var(--border-light);
            background-color: transparent;
            padding: 5px 10px;
            font-size: 0.9rem;
        }

        .dataTables_paginate .pagination .page-item.active .page-link {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            color: var(--primary-light);
        }

        .dataTables_paginate .pagination .page-link:hover {
            background-color: #f5f5f5;
            border-color: var(--border-light);
            color: var(--primary-dark);
        }

        .dataTables_info {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        /* Utilities */
        .text-muted {
            color: var(--text-muted) !important;
        }

        .text-dark {
            color: var(--primary-dark) !important;
        }

        .text-end {
            text-align: right !important;
        }

        .fw-bold {
            font-weight: 700;
        }

        .small {
            font-size: 0.85rem;
        }

        .ps-4 {
            padding-left: 1.5rem !important;
        }

        .pe-4 {
            padding-right: 1.5rem !important;
        }

        .py-3 {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }

        .py-4 {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }

        .my-5 {
            margin-top: 2rem !important;
            margin-bottom: 2rem !important;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        .mb-1 {
            margin-bottom: 0.25rem !important;
        }

        .me-1 {
            margin-right: 0.25rem !important;
        }

        .me-2 {
            margin-right: 0.5rem !important;
        }

        .gap-2 {
            gap: 8px !important;
        }

        .d-flex {
            display: flex !important;
        }

        .justify-content-between {
            justify-content: space-between !important;
        }

        .align-items-center {
            align-items: center !important;
        }

        .border-0 {
            border: none !important;
        }

        .border-bottom {
            border-bottom: 1px solid var(--border-light) !important;
        }

        .border-top {
            border-top: 1px solid var(--border-light) !important;
        }

        /* Spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.2em;
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Main Container */
        main {
            padding: 24px 0;
        }

        .container {
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .container-fluid {
            padding-left: 16px;
            padding-right: 16px;
        }
    </style>
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">MTSoft</a>

        <div class="d-flex align-items-center gap-2">
            <a href="/invoices/in"
               class="btn btn-sm {{ request()->is('invoices/in') ? 'btn-dark' : 'btn-outline-dark' }}">
                <i class="bi bi-arrow-down-circle me-1"></i> Gelen
            </a>

            <a href="/"
               class="btn btn-sm {{ request()->is('/') ? 'btn-dark' : 'btn-outline-dark' }}">
                <i class="bi bi-arrow-up-circle me-1"></i> Giden
            </a>

            <a href="/invoices/archive"
               class="btn btn-sm {{ request()->is('invoices/archive') ? 'btn-dark' : 'btn-outline-dark' }}">
                <i class="bi bi-arrow-up-circle me-1"></i> E-Arşiv
            </a>

            <button id="globalSyncBtn" class="btn btn-sm btn-dark">
                <i class="bi bi-arrow-repeat me-1"></i> Senkronize Et
            </button>
        </div>
    </div>
</nav>

<!-- ANA İÇERİK -->
<main>
    <div class="container">
        @yield('content')
    </div>
</main>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<<script>
    const SYNC_LOCK_KEY = 'sync_lock_until';

    // Sayfa yüklenince buton kilitli mi kontrol et
    $(document).ready(function () {
        const lockUntil = localStorage.getItem(SYNC_LOCK_KEY);
        const now = Date.now();

        if (lockUntil && now < parseInt(lockUntil)) {
            lockSyncButton(parseInt(lockUntil) - now);
        }
    });

    $('#globalSyncBtn').on('click', function() {
        const btn = $(this);

        // Eğer zaten kilitliyse basılamasın
        if (btn.prop('disabled')) return;

        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat me-1 spin"></i> Senkronize Ediliyor...');

        fetch('{{ route('invoices.sync') }}')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'already_running') {
                    alert('Zaten bir senkronizasyon işlemi devam ediyor.');
                } else if (data.status === 'started') {
                    alert('Senkronizasyon başlatıldı!');
                } else {
                    alert('Bilinmeyen durum: ' + JSON.stringify(data));
                }

                // 🔸 10 dakika kilitle
                const lockTime = 10 * 60 * 1000; // 10 dakika
                const unlockAt = Date.now() + lockTime;
                localStorage.setItem(SYNC_LOCK_KEY, unlockAt);
                lockSyncButton(lockTime);
            })
            .catch(err => {
                alert('Hata: ' + err);
                btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i> Senkronize Et');
            });
    });

    // 🔸 Butonu süre dolana kadar kilitleyen yardımcı fonksiyon
    function lockSyncButton(durationMs) {
        const btn = $('#globalSyncBtn');
        btn.prop('disabled', true);

        let remaining = Math.ceil(durationMs / 1000); // saniye
        updateLabel();

        const interval = setInterval(() => {
            remaining--;
            updateLabel();

            if (remaining <= 0) {
                clearInterval(interval);
                localStorage.removeItem(SYNC_LOCK_KEY);
                btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i> Senkronize Et');
            }
        }, 1000);

        function updateLabel() {
            const min = Math.floor(remaining / 60);
            const sec = remaining % 60;
            btn.html(`<i class="bi bi-lock me-1"></i> ${min}:${sec.toString().padStart(2, '0')} sonra aktif`);
        }
    }
</script>

@yield('scripts')
</body>
</html>
