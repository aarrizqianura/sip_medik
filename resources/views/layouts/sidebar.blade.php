@php
    $user = auth()->user();
    $role = $user->role ?? 'guest';
    $currentRoute = Route::currentRouteName();
@endphp

<aside class="sidebar-wrapper bg-dark-teal text-white shadow-lg d-flex flex-column" id="sidebar">
    <!-- Brand Header -->
    <div class="sidebar-header p-3 border-bottom border-teal-light d-flex align-items-center gap-3">
        <div class="brand-logo-icon bg-white text-emerald rounded-circle d-flex align-items-center justify-content-center shadow-sm">
            <i class="fa-solid fa-hospital-user fs-4 text-teal"></i>
        </div>
        <div class="brand-text">
            <h6 class="mb-0 fw-bold tracking-tight text-white">SIM-SIP DOKTER</h6>
            <small class="text-teal-200 fs-xs">RSUD Ansari Saleh</small>
        </div>
    </div>

    <!-- User Mini Profile -->
    <div class="user-mini-card p-3 border-bottom border-teal-light bg-dark-teal-hover">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar bg-emerald text-white rounded-circle d-flex align-items-center justify-content-center fw-bold">
                {{ strtoupper(substr($user->nama, 0, 1)) }}
            </div>
            <div class="overflow-hidden">
                <div class="fw-semibold text-truncate text-white fs-sm">{{ $user->nama }}</div>
                <span class="badge bg-teal-accent text-dark fs-xs">{{ $user->role_label }}</span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-menu flex-grow-1 overflow-auto p-2">
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ $currentRoute == 'dashboard' ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- 1. SUPER ADMIN EXCLUSIVE MENU --}}
            @if($role === 'super_admin')
                <li class="nav-header text-uppercase text-teal-300 fs-xs fw-bold px-3 pt-3 pb-1">Super Admin</li>
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'admin.users') ? 'active' : '' }}">
                        <i class="fa-solid fa-users-gear me-2"></i>
                        <span>Manajemen Pengguna</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.pengaturan.index') }}" class="nav-link {{ $currentRoute == 'admin.pengaturan.index' ? 'active' : '' }}">
                        <i class="fa-solid fa-sliders me-2"></i>
                        <span>Konfigurasi & Gateway</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.audit.index') }}" class="nav-link {{ $currentRoute == 'admin.audit.index' ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-list me-2"></i>
                        <span>Audit Log Sistem</span>
                    </a>
                </li>
            @endif

            {{-- 2. ADMIN SDM MENU --}}
            @if(in_array($role, ['super_admin', 'admin_sdm']))
                <li class="nav-header text-uppercase text-teal-300 fs-xs fw-bold px-3 pt-3 pb-1">Kepegawaian SDM</li>
                <li class="nav-item">
                    <a href="{{ route('sdm.dokter.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'sdm.dokter') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-doctor me-2"></i>
                        <span>Master Data Dokter</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sdm.pengesahan.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'sdm.pengesahan') || str_starts_with($currentRoute, 'sdm.arsip') ? 'active' : '' }}">
                        <i class="fa-solid fa-stamp me-2"></i>
                        <span>Pengesahan & e-Arsip</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sdm.dispensasi.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'sdm.dispensasi') ? 'active' : '' }}">
                        <i class="fa-solid fa-business-time me-2"></i>
                        <span>Masa Tenggang (Grace Period)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sdm.ksm.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'sdm.ksm') ? 'active' : '' }}">
                        <i class="fa-solid fa-sitemap me-2"></i>
                        <span>Kelompok Staf Medis (KSM)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sdm.reminder.index') }}" class="nav-link {{ $currentRoute == 'sdm.reminder.index' ? 'active' : '' }}">
                        <i class="fa-brands fa-whatsapp me-2"></i>
                        <span>Matriks & Blast WA</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sdm.laporan.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'sdm.laporan') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-waveform me-2"></i>
                        <span>5 Jenis Laporan (PDF/Excel)</span>
                    </a>
                </li>
            @endif

            {{-- 3. KOMITE MEDIK MENU --}}
            @if(in_array($role, ['super_admin', 'verifikator_komite']))
                <li class="nav-header text-uppercase text-teal-300 fs-xs fw-bold px-3 pt-3 pb-1">Komite Medik (Kredensial)</li>
                <li class="nav-item">
                    <a href="{{ route('komite.verifikasi_str.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'komite.verifikasi_str') ? 'active' : '' }}">
                        <i class="fa-solid fa-certificate me-2"></i>
                        <span>Verifikasi STR (SatuSehat)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('komite.verifikasi.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'komite.verifikasi.') && !str_starts_with($currentRoute, 'komite.verifikasi_str') ? 'active' : '' }}">
                        <i class="fa-solid fa-stamp me-2"></i>
                        <span>Verifikasi Berkas SIP</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('komite.kredensial.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'komite.kredensial') ? 'active' : '' }}">
                        <i class="fa-solid fa-id-card-clip me-2"></i>
                        <span>Kredensialing (SPK & RKK)</span>
                    </a>
                </li>
            @endif

            {{-- 4. DOKTER MENU --}}
            @if(in_array($role, ['dokter', 'super_admin']))
                <li class="nav-header text-uppercase text-teal-300 fs-xs fw-bold px-3 pt-3 pb-1">Portal Dokter</li>
                <li class="nav-item">
                    <a href="{{ route('dokter.pengajuan.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'dokter.pengajuan') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-arrow-up me-2"></i>
                        <span>Unggah & Riwayat SIP</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('dokter.arsip.saya') }}" class="nav-link {{ $currentRoute == 'dokter.arsip.saya' ? 'active' : '' }}">
                        <i class="fa-solid fa-box-archive me-2"></i>
                        <span>e-Arsip & Hak Praktik</span>
                    </a>
                </li>
            @endif

            <li class="nav-header text-uppercase text-teal-300 fs-xs fw-bold px-3 pt-3 pb-1">Komunikasi</li>
            <li class="nav-item">
                <a href="{{ route('notifikasi.index') }}" class="nav-link {{ $currentRoute == 'notifikasi.index' ? 'active' : '' }}">
                    <i class="fa-solid fa-bell me-2"></i>
                    <span>Notifikasi & Pengingat</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer p-3 border-top border-teal-light">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-light w-100 btn-sm d-flex align-items-center justify-content-center gap-2">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Keluar Sistem</span>
            </button>
        </form>
    </div>
</aside>
