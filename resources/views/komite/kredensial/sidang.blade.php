@extends('layouts.app')

@section('title', 'Sidang Kredensial & Form Digital RKK - Komite Medik')

@section('content')
<div class="mb-4">
    <a href="{{ route('komite.kredensial.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Kredensial
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-gavel text-teal me-2"></i> Sidang Kredensial & Penetapan RKK
            </h4>
            <p class="text-secondary fs-sm mb-0">
                Pola tab ganda: Evaluasi logbook bersama Mitra Bestari (Peer Group) dan formulir digital Rincian Kewenangan Klinis (RKK)
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            {!! $kredensial->status_badge !!}
            @if($kredensial->status_rekomendasi === 'diterbitkan')
                <a href="{{ route('komite.kredensial.surat_rekomendasi', $kredensial->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-file-signature me-1"></i> Lihat Surat Rekomendasi
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Profil Dokter Ringkas Header -->
<div class="card shadow-xs border-0 mb-4 bg-light">
    <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar bg-teal text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 48px; height: 48px;">
                    {{ strtoupper(substr($dokter->user->nama ?? 'D', 0, 1)) }}
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">{{ $dokter->user->nama }}</h6>
                    <small class="text-secondary">
                        NIP: <strong>{{ $dokter->nip_nrp ?: '-' }}</strong> &bullet; 
                        KSM: <strong>{{ $dokter->ksm->nama_ksm ?: '-' }}</strong> &bullet; 
                        Spesialisasi: <strong>{{ $dokter->spesialisasi ?: '-' }}</strong>
                    </small>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge bg-white text-dark border fs-xs">
                    No. STR: <span class="font-monospace text-teal fw-bold">{{ $dokter->nomor_str ?: '-' }}</span>
                </span>
                <span class="badge bg-teal-subtle text-teal border border-teal-subtle fs-xs">
                    Siklus 3 Tahunan
                </span>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('komite.kredensial.simpan_sidang', $kredensial->id) }}" method="POST" id="formSidangRkk">
    @csrf

    <!-- TAB GANDA: TAB 1 (FORM RKK DIGITAL) & TAB 2 (BERITA ACARA MITRA BESTARI) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs nav-fill border-0" id="tabSidangKredensial" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold fs-sm border-0 border-bottom border-3 border-teal text-teal" id="tab-rkk-btn" data-bs-toggle="tab" data-bs-target="#pane-rkk" type="button" role="tab">
                        <i class="fa-solid fa-list-check me-2"></i>Tab 1: Formulir RKK Digital (Kewenangan Klinis)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold fs-sm border-0 text-secondary" id="tab-mitra-btn" data-bs-toggle="tab" data-bs-target="#pane-mitra" type="button" role="tab">
                        <i class="fa-solid fa-users-gear me-2"></i>Tab 2: Berita Acara Mitra Bestari (Peer Group)
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="tabSidangContent">
                <!-- ==================== TAB 1: FORMULIR RKK DIGITAL ==================== -->
                <div class="tab-pane fade show active" id="pane-rkk" role="tabpanel">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 pb-3 border-bottom">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Daftar Tindakan & Prosedur Medis</h6>
                            <small class="text-secondary fs-xs">
                                Tetapkan wewenang klinis untuk KSM {{ $dokter->ksm->nama_ksm ?? 'Umum' }}: <strong>(M) Mandiri</strong>, <strong>(S) Supervisi</strong>, atau <strong>(T) Tidak Disetujui</strong>
                            </small>
                        </div>
                        <!-- Tombol Massal Semua Mandiri & Semua Supervisi -->
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-success fw-semibold shadow-xs" onclick="setSemuaStatus('mandiri')">
                                <i class="fa-solid fa-check-double me-1"></i> Semua Mandiri (M)
                            </button>
                            <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold shadow-xs" onclick="setSemuaStatus('supervisi')">
                                <i class="fa-solid fa-user-clock me-1"></i> Semua Supervisi (S)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="tambahTindakanBaru()">
                                <i class="fa-solid fa-plus me-1"></i> Tambah Tindakan
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-sm" id="tabelRkk">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Kode</th>
                                    <th>Rincian Tindakan / Prosedur Medis</th>
                                    <th width="160">Kategori Bidang</th>
                                    <th width="240" class="text-center">Kewenangan Klinis</th>
                                    <th>Catatan / Syarat Supervisi</th>
                                    <th width="40"></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyRkk">
                                @foreach($rkkItems as $index => $item)
                                    @php
                                        $currentStatus = old("rkk.{$index}.status", $item['status'] ?? 'mandiri');
                                    @endphp
                                    <tr id="rowRkk_{{ $index }}">
                                        <td>
                                            <input type="text" name="rkk[{{ $index }}][kode]" class="form-control form-control-sm font-monospace text-center fs-xs" value="{{ $item['kode'] ?? 'MED-'.($index+1) }}" required>
                                        </td>
                                        <td>
                                            <input type="text" name="rkk[{{ $index }}][nama]" class="form-control form-control-sm fw-semibold fs-xs" value="{{ $item['nama'] ?? '' }}" required>
                                        </td>
                                        <td>
                                            <input type="text" name="rkk[{{ $index }}][kategori]" class="form-control form-control-sm fs-xs text-muted" value="{{ $item['kategori'] ?? 'Kewenangan Klinis' }}">
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm w-100" role="group">
                                                <input type="radio" class="btn-check rkk-status-mandiri" name="rkk[{{ $index }}][status]" id="status_m_{{ $index }}" value="mandiri" {{ $currentStatus === 'mandiri' ? 'checked' : '' }} autocomplete="off">
                                                <label class="btn btn-outline-success fs-xs py-1 px-2" for="status_m_{{ $index }}" title="Disetujui Mandiri (M)">
                                                    <strong>M</strong> Mandiri
                                                </label>

                                                <input type="radio" class="btn-check rkk-status-supervisi" name="rkk[{{ $index }}][status]" id="status_s_{{ $index }}" value="supervisi" {{ $currentStatus === 'supervisi' ? 'checked' : '' }} autocomplete="off">
                                                <label class="btn btn-outline-warning fs-xs py-1 px-2" for="status_s_{{ $index }}" title="Disetujui dengan Supervisi (S)">
                                                    <strong>S</strong> Supervisi
                                                </label>

                                                <input type="radio" class="btn-check" name="rkk[{{ $index }}][status]" id="status_t_{{ $index }}" value="tidak_disetujui" {{ $currentStatus === 'tidak_disetujui' ? 'checked' : '' }} autocomplete="off">
                                                <label class="btn btn-outline-danger fs-xs py-1 px-1" for="status_t_{{ $index }}" title="Tidak Disetujui (T)">
                                                    <strong>T</strong>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="rkk[{{ $index }}][catatan]" class="form-control form-control-sm fs-2xs" placeholder="Indikasi..." value="{{ $item['catatan'] ?? '' }}">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-2xs btn-outline-danger" onclick="hapusBarisRkk({{ $index }})" title="Hapus baris">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== TAB 2: BERITA ACARA MITRA BESTARI ==================== -->
                <div class="tab-pane fade" id="pane-mitra" role="tabpanel">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="fa-solid fa-clipboard-user text-teal me-2"></i>Evaluasi Portofolio & Berita Acara Mitra Bestari
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fs-xs fw-semibold text-dark">Tanggal Pelaksanaan Sidang Kredensial <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_sidang" class="form-control" value="{{ old('tanggal_sidang', $kredensial->tanggal_sidang ? $kredensial->tanggal_sidang->format('Y-m-d') : date('Y-m-d')) }}" required>
                            <small class="text-muted fs-3xs">Tanggal penyelenggaraan rapat pleno kredensialing.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fs-xs fw-semibold text-dark">Nama Tim Mitra Bestari (Peer Group) <span class="text-danger">*</span></label>
                            <input type="text" name="nama_mitra_bestari" class="form-control" placeholder="Contoh: dr. H. Fachrul Razi, Sp.B (Ketua Peer Group)" value="{{ old('nama_mitra_bestari', $kredensial->nama_mitra_bestari ?: 'dr. H. Fachrul Razi, Sp.B (Mitra Bestari)') }}" required>
                            <small class="text-muted fs-3xs">Dokter spesialis sejawat yang ditugaskan sebagai evaluator independen.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fs-xs fw-semibold text-dark">Evaluasi Logbook Tindakan Medis (3 Tahun Terakhir) <span class="text-danger">*</span></label>
                            <textarea name="evaluasi_logbook" rows="4" class="form-control fs-sm" placeholder="Ringkasan evaluasi jumlah kasus klinis, morbiditas, mortalitas..." required>{{ old('evaluasi_logbook', $kredensial->evaluasi_logbook ?: 'Logbook tindakan 3 tahun terakhir telah diverifikasi: total 342 tindakan klinis terlaksana dengan angka komplikasi <1.2%, mortalitas 0%, memenuhi standar kecakapan klinis RSUD Ansari Saleh.') }}</textarea>
                            <small class="text-muted fs-3xs">Kesesuaian volume tindakan klinis dengan standar kompetensi rumah sakit.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fs-xs fw-semibold text-dark">Evaluasi Portofolio & Sertifikasi CPD <span class="text-danger">*</span></label>
                            <textarea name="evaluasi_portofolio" rows="4" class="form-control fs-sm" placeholder="Evaluasi sertifikat kompetensi kolegium, workshop, kepatuhan etika..." required>{{ old('evaluasi_portofolio', $kredensial->evaluasi_portofolio ?: 'Portofolio lengkap: Sertifikat kompetensi kolegium aktif, telah mengikuti 4 workshop lanjutan/CME nasional, tidak pernah melanggar disiplin profesi maupun etika kedokteran.') }}</textarea>
                            <small class="text-muted fs-3xs">Kelengkapan portofolio keilmuan dan etika kedokteran.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fs-xs fw-semibold text-dark">Catatan Khusus / Rekomendasi Mitra Bestari</label>
                            <textarea name="catatan_mitra_bestari" rows="3" class="form-control fs-sm" placeholder="Catatan syarat pendampingan konsultan atau dispensasi tindakan...">{{ old('catatan_mitra_bestari', $kredensial->catatan_mitra_bestari ?: 'Direkomendasikan pemberian kewenangan mandiri untuk seluruh prosedur rutin. Prosedur minimal invasif lanjutan didampingi konsultan.') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Footer Action Buttons -->
        <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
            <a href="{{ route('komite.kredensial.index') }}" class="btn btn-outline-secondary btn-sm">
                Batal
            </a>
            <button type="submit" class="btn btn-teal text-white btn-sm px-4 fw-bold shadow-xs">
                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Penetapan Sidang Kredensial & RKK
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    // Tab switching event to update active styles
    document.querySelectorAll('#tabSidangKredensial button[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            document.querySelectorAll('#tabSidangKredensial button').forEach(b => {
                b.classList.remove('border-bottom', 'border-3', 'border-teal', 'text-teal');
                b.classList.add('text-secondary');
            });
            e.target.classList.add('border-bottom', 'border-3', 'border-teal', 'text-teal');
            e.target.classList.remove('text-secondary');
        });
    });

    // Bulk selection helper: Semua Mandiri / Semua Supervisi
    function setSemuaStatus(status) {
        if (status === 'mandiri') {
            document.querySelectorAll('.rkk-status-mandiri').forEach(rb => {
                rb.checked = true;
            });
        } else if (status === 'supervisi') {
            document.querySelectorAll('.rkk-status-supervisi').forEach(rb => {
                rb.checked = true;
            });
        }
    }

    let rkkIndex = {{ count($rkkItems) }};

    function tambahTindakanBaru() {
        const tbody = document.getElementById('tbodyRkk');
        const rowId = `rowRkk_${rkkIndex}`;
        const newRow = document.createElement('tr');
        newRow.id = rowId;
        newRow.innerHTML = `
            <td>
                <input type="text" name="rkk[${rkkIndex}][kode]" class="form-control form-control-sm font-monospace text-center fs-xs" value="MED-${rkkIndex + 1}" required>
            </td>
            <td>
                <input type="text" name="rkk[${rkkIndex}][nama]" class="form-control form-control-sm fw-semibold fs-xs" placeholder="Nama tindakan..." required>
            </td>
            <td>
                <input type="text" name="rkk[${rkkIndex}][kategori]" class="form-control form-control-sm fs-xs text-muted" value="Kewenangan Klinis">
            </td>
            <td>
                <div class="btn-group btn-group-sm w-100" role="group">
                    <input type="radio" class="btn-check rkk-status-mandiri" name="rkk[${rkkIndex}][status]" id="status_m_${rkkIndex}" value="mandiri" checked autocomplete="off">
                    <label class="btn btn-outline-success fs-xs py-1 px-2" for="status_m_${rkkIndex}">
                        <strong>M</strong> Mandiri
                    </label>

                    <input type="radio" class="btn-check rkk-status-supervisi" name="rkk[${rkkIndex}][status]" id="status_s_${rkkIndex}" value="supervisi" autocomplete="off">
                    <label class="btn btn-outline-warning fs-xs py-1 px-2" for="status_s_${rkkIndex}">
                        <strong>S</strong> Supervisi
                    </label>

                    <input type="radio" class="btn-check" name="rkk[${rkkIndex}][status]" id="status_t_${rkkIndex}" value="tidak_disetujui" autocomplete="off">
                    <label class="btn btn-outline-danger fs-xs py-1 px-1" for="status_t_${rkkIndex}">
                        <strong>T</strong>
                    </label>
                </div>
            </td>
            <td>
                <input type="text" name="rkk[${rkkIndex}][catatan]" class="form-control form-control-sm fs-2xs" placeholder="Indikasi...">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-2xs btn-outline-danger" onclick="hapusBarisRkk(${rkkIndex})" title="Hapus baris">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
        rkkIndex++;
    }

    function hapusBarisRkk(index) {
        const row = document.getElementById(`rowRkk_${index}`);
        if (row) {
            row.remove();
        }
    }
</script>
@endpush
@endsection
