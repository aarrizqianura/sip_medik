@extends('layouts.app')

@section('title', 'Verifikasi Master Data STR & SatuSehat SDMK')

@section('content')
<div class="mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-certificate text-teal me-2"></i>Verifikasi Master Data STR Dokter (SatuSehat SDMK)
            </h4>
            <p class="text-secondary fs-sm mb-0">
                Pemeriksaan kesesuaian data STR 16 digit dengan pangkalan data SatuSehat SDMK Kemenkes RI oleh Subkomite Kredensial Komite Medik
            </p>
        </div>
    </div>
</div>

<!-- Info Alert Fondasi Hukum -->
<div class="alert alert-info border-teal-light bg-light-teal d-flex align-items-center gap-3 mb-4 shadow-2xs">
    <div class="fs-3 text-teal">
        <i class="fa-solid fa-shield-halved"></i>
    </div>
    <div class="fs-sm text-dark">
        <strong>Syarat Mutlak (Hard Constraint):</strong> Berdasarkan tata kelola klinis RSUD dr. H. Moch. Ansari Saleh, modul pengurusan & perpanjangan SIP dokter <u>hanya dapat aktif</u> setelah nomor registrasi STR (16 digit) tervalidasi dan <strong>terkunci (read-only)</strong> oleh Subkomite Kredensial.
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-xs border-0 border-start border-4 border-warning h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-xs text-muted text-uppercase fw-bold">Menunggu Telaah</div>
                        <h3 class="fw-bold text-warning-emphasis mb-0">{{ $statistik['menunggu'] }}</h3>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                        <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-xs border-0 border-start border-4 border-success h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-xs text-muted text-uppercase fw-bold">Tervalidasi & Terkunci</div>
                        <h3 class="fw-bold text-success mb-0">{{ $statistik['tervalidasi'] }}</h3>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle">
                        <i class="fa-solid fa-lock fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-xs border-0 border-start border-4 border-danger h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-xs text-muted text-uppercase fw-bold">Ditolak / Revisi</div>
                        <h3 class="fw-bold text-danger mb-0">{{ $statistik['ditolak'] }}</h3>
                    </div>
                    <div class="bg-danger-subtle text-danger p-3 rounded-circle">
                        <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-xs border-0 border-start border-4 border-primary h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-xs text-muted text-uppercase fw-bold">Total Staf Medis</div>
                        <h3 class="fw-bold text-primary mb-0">{{ $statistik['total'] }}</h3>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                        <i class="fa-solid fa-user-doctor fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-xs mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('komite.verifikasi_str.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="cari" class="form-control" placeholder="Cari nama, NIP, atau 16 digit STR..." value="{{ request('cari') }}">
                </div>
            </div>

            <div class="col-md-3">
                <select name="status_verifikasi" class="form-select form-select-sm">
                    <option value="">-- Semua Status Validasi --</option>
                    <option value="menunggu_verifikasi" {{ request('status_verifikasi') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Telaah Kredensial</option>
                    <option value="tervalidasi" {{ request('status_verifikasi') == 'tervalidasi' ? 'selected' : '' }}>Tervalidasi & Terkunci (Read-Only)</option>
                    <option value="ditolak" {{ request('status_verifikasi') == 'ditolak' ? 'selected' : '' }}>Ditolak / Perlu Revisi</option>
                    <option value="belum_lengkap" {{ request('status_verifikasi') == 'belum_lengkap' ? 'selected' : '' }}>Belum Lengkap</option>
                </select>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-teal text-white btn-sm">
                    <i class="fa-solid fa-filter me-1"></i> Saring Data
                </button>
                <a href="{{ route('komite.verifikasi_str.index') }}" class="btn btn-light border btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card shadow-xs">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Dokter & Identitas</th>
                    <th>Nomor STR (16 Digit)</th>
                    <th>Kualifikasi Spesialisasi</th>
                    <th>Praktik di Ansari Saleh</th>
                    <th>Salinan e-STR</th>
                    <th>Status Validasi STR</th>
                    <th class="text-end pe-3">Aksi Telaah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dokterList as $d)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold text-dark">{{ $d->user->nama }}</div>
                            <div class="text-muted fs-xs font-monospace">NIP: {{ $d->nip_nrp }}</div>
                            <div class="text-teal fs-xs fw-semibold">{{ $d->ksm->nama_ksm ?? '-' }}</div>
                        </td>

                        <td>
                            @if($d->nomor_str)
                                <div class="font-monospace fw-bold text-dark fs-xs">{{ $d->nomor_str }}</div>
                                <div class="fs-xs text-muted">
                                    {{ $d->status_str === 'seumur_hidup' ? 'Masa Berlaku: Seumur Hidup' : 's.d ' . ($d->tanggal_kadaluarsa_str ? $d->tanggal_kadaluarsa_str->format('d/m/Y') : '-') }}
                                </div>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Belum Diinput SDM</span>
                            @endif
                        </td>

                        <td>
                            <div class="fw-semibold text-dark">{{ $d->spesialisasi }}</div>
                            @if($d->subspesialis)
                                <div class="fs-xs text-muted"><i class="fa-solid fa-angle-right me-1 text-teal"></i>Subspesialis: {{ $d->subspesialis }}</div>
                            @endif
                        </td>

                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                {{ $d->tempat_praktik_label }}
                            </span>
                        </td>

                        <td>
                            @if($d->file_str)
                                <a href="{{ asset('storage/' . $d->file_str) }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                    <i class="fa-solid fa-file-pdf me-1"></i> Unduh e-STR
                                </a>
                            @else
                                <span class="fs-xs text-muted">Tidak ada file</span>
                            @endif
                        </td>

                        <td>
                            {!! $d->status_verifikasi_str_badge !!}
                            @if($d->is_str_locked)
                                <div class="fs-xs text-success mt-1">
                                    <i class="fa-solid fa-lock me-1"></i>Nomor STR Terkunci
                                </div>
                            @endif
                            @if($d->verified_str_at)
                                <div class="fs-xs text-muted mt-1">
                                    {{ $d->verified_str_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </td>

                        <td class="text-end pe-3">
                            <a href="{{ route('komite.verifikasi_str.show', $d->id) }}" class="btn btn-sm {{ $d->status_verifikasi_str === 'menunggu_verifikasi' ? 'btn-teal text-white' : 'btn-outline-secondary' }}">
                                <i class="fa-solid fa-magnifying-glass-check me-1"></i> Telaah & Validasi
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-inbox fs-2 mb-2 d-block"></i>
                            Tidak ada data STR dokter yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dokterList->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $dokterList->links() }}
        </div>
    @endif
</div>
@endsection
