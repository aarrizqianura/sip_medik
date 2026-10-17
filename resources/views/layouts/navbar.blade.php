@php
    $unreadCount = auth()->user()->notifikasi()->where('status_baca', false)->count();
@endphp

<header class="main-navbar bg-white border-bottom shadow-xs sticky-top py-2 px-3 px-lg-4">
    <div class="d-flex align-items-center justify-content-between">
        <!-- Sidebar Toggle & Breadcrumb/Title -->
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light btn-sm border d-lg-none" id="sidebarToggle" type="button" aria-label="Toggle Navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div>
                <span class="badge bg-teal-subtle text-teal fw-medium d-none d-md-inline-block me-2">RSUD dr. H. Moch. Ansari Saleh</span>
                <span class="text-secondary fs-sm fw-normal d-none d-sm-inline">Komite Medik & Bagian Kepegawaian</span>
            </div>
        </div>

        <!-- Right Utilities -->
        <div class="d-flex align-items-center gap-2">
            <!-- Notifications Dropdown -->
            <div class="dropdown">
                <a href="{{ route('notifikasi.index') }}" class="btn btn-light position-relative rounded-circle p-2 border">
                    <i class="fa-regular fa-bell text-secondary"></i>
                    @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $unreadCount }}
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    @endif
                </a>
            </div>

            <!-- User Menu Dropdown -->
            <div class="dropdown ms-2">
                <button class="btn btn-light border d-flex align-items-center gap-2 py-1 px-2 rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <div class="avatar bg-teal text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-xs" style="width: 28px; height: 28px;">
                        {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                    </div>
                    <span class="fs-sm fw-medium d-none d-md-inline text-dark">{{ auth()->user()->nama }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-semibold text-dark fs-sm">{{ auth()->user()->nama }}</div>
                        <div class="text-muted fs-xs">{{ auth()->user()->email }}</div>
                        <span class="badge bg-teal-subtle text-teal mt-1">{{ auth()->user()->role_label }}</span>
                    </li>
                    @if(auth()->user()->role === 'dokter')
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('dokter.pengajuan.index') }}">
                                <i class="fa-solid fa-file-signature text-secondary me-2"></i> Pengajuan SIP Saya
                            </a>
                        </li>
                    @endif
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('notifikasi.index') }}">
                            <i class="fa-regular fa-bell text-secondary me-2"></i> Kotak Notifikasi
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger py-2">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
