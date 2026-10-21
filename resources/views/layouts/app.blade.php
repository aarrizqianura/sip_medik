<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIM-SIP & Kredensial') - RSUD dr. H. Moch. Ansari Saleh</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary-teal: #0f766e;
            --primary-dark-teal: #115e59;
            --primary-deep: #042f2e;
            --primary-light-teal: #14b8a6;
            --primary-surface: #f0fdfa;
            --font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            font-family: var(--font-family);
            background-color: #f8fafc;
            color: #334155;
            min-height: 100vh;
        }

        .bg-dark-teal {
            background-color: var(--primary-deep) !important;
        }

        .bg-teal {
            background-color: var(--primary-teal) !important;
        }

        .text-teal {
            color: var(--primary-teal) !important;
        }

        .text-teal-200 {
            color: #99f6e4 !important;
        }

        .text-teal-300 {
            color: #5eead4 !important;
        }

        .bg-teal-subtle {
            background-color: #ccfbf1 !important;
        }

        .btn-teal {
            background-color: var(--primary-teal) !important;
            border-color: var(--primary-teal) !important;
            color: #ffffff !important;
            font-weight: 600;
        }

        .btn-teal:hover, .btn-teal:focus, .btn-teal:active {
            background-color: var(--primary-dark-teal) !important;
            border-color: var(--primary-dark-teal) !important;
            color: #ffffff !important;
        }

        .btn-outline-teal {
            color: var(--primary-teal) !important;
            border-color: var(--primary-teal) !important;
            background-color: transparent !important;
            font-weight: 600;
        }

        .btn-outline-teal:hover, .btn-outline-teal:focus, .btn-outline-teal:active {
            color: #ffffff !important;
            background-color: var(--primary-teal) !important;
            border-color: var(--primary-teal) !important;
        }

        .bg-light-teal {
            background-color: #f0fdfa !important;
        }

        .fs-xs {
            font-size: 0.75rem;
        }

        .fs-sm {
            font-size: 0.875rem;
        }

        /* App Wrapper */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar-wrapper {
            width: 280px;
            min-width: 280px;
            transition: all 0.3s ease;
            z-index: 1040;
        }

        .brand-logo-icon {
            width: 42px;
            height: 42px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            font-size: 0.875rem;
        }

        .sidebar-menu .nav-link {
            color: #cbd5e1;
            padding: 0.65rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .sidebar-menu .nav-link:hover {
            color: #ffffff;
            background-color: rgba(20, 184, 166, 0.15);
        }

        .sidebar-menu .nav-link.active {
            color: #ffffff;
            background-color: var(--primary-teal);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(15, 118, 110, 0.4);
        }

        /* Main Content Layout */
        .main-content-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .content-body {
            flex-grow: 1;
            padding: 1.5rem 2rem;
        }

        @media (max-width: 991.98px) {
            .sidebar-wrapper {
                position: fixed;
                top: 0;
                bottom: 0;
                left: -280px;
            }
            .sidebar-wrapper.show {
                left: 0;
            }
            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1030;
            }
            .sidebar-backdrop.show {
                display: block;
            }
            .content-body {
                padding: 1rem;
            }
        }

        /* Card & UI Elevation */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1rem 1.25rem;
        }

        /* Status Pills */
        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            border: 1px solid transparent;
        }

        .status-pill.status-aktif {
            background-color: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        .status-pill.status-due_soon {
            background-color: #fef9c3;
            color: #854d0e;
            border-color: #fef08a;
        }

        .status-pill.status-due {
            background-color: #ffedd5;
            color: #9a3412;
            border-color: #fed7aa;
        }

        .status-pill.status-overdue {
            background-color: #ffe4e6;
            color: #9f1239;
            border-color: #fecdd3;
        }

        /* Countdown display */
        .countdown-badge {
            font-size: 0.85rem;
            font-weight: 700;
            padding: 0.35rem 0.65rem;
            border-radius: 0.5rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Backdrop for Mobile -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Sidebar Navigation -->
        @include('layouts.sidebar')

        <!-- Main Wrapper -->
        <div class="main-content-wrapper">
            <!-- Top Navbar -->
            @include('layouts.navbar')

            <!-- Content Area -->
            <main class="content-body">
                <!-- Flash Alerts -->
                @if(session('sukses'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
                        <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                        <div>{{ session('sukses') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
                        <i class="fa-solid fa-circle-xmark fs-5 text-danger"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
                        <i class="fa-solid fa-triangle-exclamation fs-5 text-warning"></i>
                        <div>{{ session('warning') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Perhatian: Terdapat kesalahan input</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-top py-3 px-4 text-center text-muted fs-xs">
                <div>&copy; {{ date('Y') }} <strong>RSUD dr. H. Moch. Ansari Saleh Banjarmasin</strong>. Sistem Informasi Monitoring Masa Berlaku SIP & Kredensial Dokter (Komite Medik).</div>
                <div class="text-secondary mt-1">Pokja Kualifikasi dan Pendidikan Staf (KPS) &bullet; Subkomite Kredensial &bullet; IT PDE</div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Mobile Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebarBackdrop');

            if (toggleBtn && sidebar && backdrop) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    backdrop.classList.toggle('show');
                });

                backdrop.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
