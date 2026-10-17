<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem - SIM-SIP & Kredensial RSUD dr. H. Moch. Ansari Saleh</title>
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
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #042f2e 0%, #0f766e 50%, #115e59 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-card {
            background: #ffffff;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            max-width: 950px;
            width: 100%;
        }

        .login-hero {
            background: linear-gradient(180deg, rgba(15, 118, 110, 0.95) 0%, rgba(4, 47, 46, 0.98) 100%);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
        }

        .login-form-area {
            padding: 3rem;
        }

        @media (max-width: 767.98px) {
            .login-hero {
                padding: 2rem;
            }
            .login-form-area {
                padding: 2rem 1.5rem;
            }
        }

        .btn-teal {
            background-color: var(--primary-teal);
            border-color: var(--primary-teal);
            color: #ffffff;
            font-weight: 600;
        }

        .btn-teal:hover {
            background-color: var(--primary-dark-teal);
            border-color: var(--primary-dark-teal);
            color: #ffffff;
        }

        .form-control:focus {
            border-color: var(--primary-light-teal);
            box-shadow: 0 0 0 0.25rem rgba(20, 184, 166, 0.25);
        }

        .role-badge-btn {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .role-badge-btn:hover {
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="row g-0">
        <!-- Left Side: Brand Visual & Info -->
        <div class="col-lg-5 login-hero d-none d-lg-flex">
            <div>
                <div class="d-inline-flex align-items-center justify-content-center bg-white text-dark rounded-circle p-3 mb-4 shadow">
                    <i class="fa-solid fa-hospital-user fs-2 text-teal" style="color: #0f766e;"></i>
                </div>
                <h4 class="fw-bold tracking-tight mb-1 text-white">RSUD dr. H. Moch. Ansari Saleh</h4>
                <p class="text-teal-200 fs-sm mb-4">Pemerintah Provinsi Kalimantan Selatan</p>

                <h5 class="fw-bold text-white mb-2">SIM-SIP & Kredensial Komite Medik</h5>
                <p class="text-light opacity-75 fs-sm">
                    Platform terpusat pemantauan masa berlaku Surat Izin Praktik (SIP) dan Instrumen Kewenangan Klinis (SPK & RKK) untuk menjamin kepatuhan akreditasi fasyankes Pokja KPS serta keselamatan pasien.
                </p>
            </div>

            <div class="border-top border-white border-opacity-25 pt-3">
                <div class="d-flex align-items-center gap-2 text-light opacity-75 fs-xs">
                    <i class="fa-solid fa-shield-halved text-warning"></i>
                    <span>Sistem Terproteksi CSRF & Hash Bcrypt</span>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="col-lg-7 login-form-area bg-white">
            <div class="mb-4">
                <h4 class="fw-bold text-dark mb-1">Masuk Portal SIM-SIP</h4>
                <p class="text-secondary fs-sm">Silakan masukkan username dan kata sandi Anda</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm py-2 px-3 fs-sm mb-4">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $errors->first() }}
                </div>
            @endif

            @if(session('sukses'))
                <div class="alert alert-success border-0 shadow-sm py-2 px-3 fs-sm mb-4">
                    <i class="fa-solid fa-circle-check me-1"></i> {{ session('sukses') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label fs-sm fw-semibold text-dark">Username atau Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-regular fa-user"></i></span>
                        <input type="text" name="username" id="username" class="form-control border-start-0 ps-0" placeholder="Masukkan username / email" value="{{ old('username') }}" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fs-sm fw-semibold text-dark">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control border-start-0 ps-0" placeholder="Masukkan kata sandi" required>
                        <button class="btn btn-light border border-start-0 text-muted" type="button" id="togglePasswordBtn">
                            <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4 fs-sm">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label text-secondary" for="remember">
                            Ingat saya di perangkat ini
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-teal w-100 py-2 mb-4 d-flex align-items-center justify-content-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Quick Demo Login Buttons -->
            <div class="border-top pt-3 mt-2">
                <div class="text-muted fs-xs fw-semibold text-uppercase mb-2">Akun Pengujian Demo (Klik Cepat):</div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-danger btn-sm role-badge-btn" onclick="quickFill('superadmin', 'password123')">
                        <i class="fa-solid fa-user-shield me-1"></i> Super Admin
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm role-badge-btn" onclick="quickFill('adminsdm', 'password123')">
                        <i class="fa-solid fa-users-gear me-1"></i> Admin SDM
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm role-badge-btn" onclick="quickFill('verifikatorkomite', 'password123')">
                        <i class="fa-solid fa-stamp me-1"></i> Verifikator Komite
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm role-badge-btn" onclick="quickFill('dokter1', 'password123')">
                        <i class="fa-solid fa-user-doctor me-1"></i> Dokter (Sp.B)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function quickFill(user, pass) {
        document.getElementById('username').value = user;
        document.getElementById('password').value = pass;
    }

    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passInput = document.getElementById('password');
    const passIcon = document.getElementById('togglePasswordIcon');

    toggleBtn.addEventListener('click', function() {
        if (passInput.type === 'password') {
            passInput.type = 'text';
            passIcon.classList.remove('fa-eye');
            passIcon.classList.add('fa-eye-slash');
        } else {
            passInput.type = 'password';
            passIcon.classList.remove('fa-eye-slash');
            passIcon.classList.add('fa-eye');
        }
    });
</script>

</body>
</html>
