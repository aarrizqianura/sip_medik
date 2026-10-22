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
                <i class="fa-solid fa-gavel text-teal me-2"></i> Sidang Kredensial & Formulir Digital RKK
            </h4>
            <p class="text-secondary fs-sm mb-0">
                Evaluasi logbook tindakan medis dan portofolio dokter bersama Mitra Bestari (Peer Group) serta penetapan Rincian Kewenangan Klinis (RKK)
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

<!-- Banner Alasan Rekredensial -->
@if($kredensial->alasan_rekredensial)
    <div class="alert alert-warning border-warning shadow-xs d-flex align-items-center gap-3 mb-4">
        <i class="fa-solid fa-circle-exclamation fs-3 text-warning"></i>
        <div>
            <div class="fw-bold">Pemicu Alur Rekredensialing:</div>
            <div class="fs-sm">{{ $kredensial->alasan_rekredensial }}</div>
        </div>
    </div>
@endif

<form action="{{ route('komite.kredensial.simpan_sidang', $kredensial->id) }}" method="POST" id="formSidangRkk">
    @csrf

    <div class="row g-4">
        <!-- 1. IDENTITAS DOKTER & DATA PRASYARAT -->
        <div class="col-lg-4">
            <div class="card shadow-xs mb-4">
                <div class="card-header fw-bold text-dark d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-user-doctor text-teal me-2"></i> Profil Dokter Pengaju</span>
                    <span class="badge bg-teal-subtle text-teal">{{ $dokter->ksm->kode_ksm ?? '-' }}</span>
                </div>
                <div class="card-body p-3">
                    <div class="text-center mb-3">
                        <div class="avatar-lg bg-teal text-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold fs-3 mb-2 shadow-sm" style="width: 64px; height: 64px;">
                            {{ strtoupper(substr($dokter->user->nama ?? 'D', 0, 1)) }}
                        </div>
                        <h6 class="fw-bold text-dark mb-0">{{ $dokter->user->nama }}</h6>
                        <small class="text-muted">{{ $dokter->spesialisasi ?: 'Dokter Spesialis' }}</small>
                    </div>

                    <table class="table table-sm table-borderless fs-sm mb-0">
                        <tr>
                            <td class="text-muted" width="120">NIP / NRP</td>
                            <td>: {{ $dokter->nip_nrp ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">KSM</td>
                            <td>: {{ $dokter->ksm->nama_ksm ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status STR</td>
                            <td>
                                : <span class="badge {{ $dokter->status_str === 'seumur_hidup' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $dokter->status_str === 'seumur_hidup' ? 'Seumur Hidup' : 'Berkala' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nomor STR</td>
                            <td class="font-monospace fs-xs">: {{ $dokter->nomor_str ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">SIP Terbaru</td>
                            <td>
                                : <span class="badge bg-success-subtle text-success border border-success fs-2xs">
                                    <i class="fa-solid fa-circle-check"></i> Sah Tervalidasi
                                </span>
                                @if($dokter->sipTerbaru)
                                    <div class="font-monospace fs-2xs text-secondary mt-1">{{ $dokter->sipTerbaru->nomor_sip }}</div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Siklus Evaluasi</td>
                            <td class="fw-bold text-teal">: 3 Tahun Berkala</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Panel Mitra Bestari & Jadwal Sidang -->
            <div class="card shadow-xs mb-4">
                <div class="card-header fw-bold text-dark">
                    <i class="fa-solid fa-users-gear text-teal me-2"></i> Sidang Mitra Bestari (Peer Group)
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label fs-xs fw-semibold">Tanggal Pelaksanaan Sidang <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_sidang" class="form-control form-control-sm" value="{{ old('tanggal_sidang', $kredensial->tanggal_sidang ? $kredensial->tanggal_sidang->format('Y-m-d') : date('Y-m-d')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-xs fw-semibold">Tim Mitra Bestari (Peer Group) <span class="text-danger">*</span></label>
                        <input type="text" name="nama_mitra_bestari" class="form-control form-control-sm" placeholder="Contoh: dr. H. Fachrul Razi, Sp.B (Ketua Peer Group)" value="{{ old('nama_mitra_bestari', $kredensial->nama_mitra_bestari ?: 'dr. H. Fachrul Razi, Sp.B (Mitra Bestari)') }}" required>
                        <small class="text-muted fs-2xs">Nama dokter spesialis sejenis yang menjadi evaluator independen.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-xs fw-semibold">Evaluasi Logbook Tindakan Medis <span class="text-danger">*</span></label>
                        <textarea name="evaluasi_logbook" rows="3" class="form-control fs-xs" placeholder="Ringkasan evaluasi jumlah kasus, morbiditas, mortalitas selama 3 tahun..." required>{{ old('evaluasi_logbook', $kredensial->evaluasi_logbook ?: 'Logbook tindakan 3 tahun terakhir telah diverifikasi: total 342 tindakan klinis terlaksana dengan angka komplikasi <1.2%, mortalitas 0%, memenuhi standar kecakapan klinis RSUD Ansari Saleh.') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-xs fw-semibold">Evaluasi Portofolio & Sertifikasi CPD <span class="text-danger">*</span></label>
                        <textarea name="evaluasi_portofolio" rows="3" class="form-control fs-xs" placeholder="Evaluasi sertifikat kompetensi, fellowship, dan kepatuhan etika klinis..." required>{{ old('evaluasi_portofolio', $kredensial->evaluasi_portofolio ?: 'Portofolio lengkap: Sertifikat kompetensi kolegium aktif, telah mengikuti 4 workshop lanjutan/CME nasional, tidak pernah melanggar disiplin profesi maupun etika kedokteran.') }}</textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fs-xs fw-semibold">Catatan Khusus Mitra Bestari</label>
                        <textarea name="catatan_mitra_bestari" rows="2" class="form-control fs-xs" placeholder="Catatan atau syarat supervisi untuk tindakan tertentu...">{{ old('catatan_mitra_bestari', $kredensial->catatan_mitra_bestari ?: 'Direkomendasikan pemberian kewenangan mandiri untuk seluruh prosedur rutin. Prosedur minimal invasif lanjutan didampingi konsultan.') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. FORMULIR DIGITAL RINCIAN KEWENANGAN KLINIS (RKK) -->
        <div class="col-lg-8">
            <div class="card shadow-xs mb-4">
                <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 py-3">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="fa-solid fa-list-check text-teal me-2"></i> Form Digital Rincian Kewenangan Klinis (RKK)
                        </h6>
                        <small class="text-secondary fs-xs">
                            KSM {{ $dokter->ksm->nama_ksm ?? 'Umum' }} &bull; Tetapkan status: <strong>(M) Mandiri</strong> atau <strong>(S) Supervisi</strong>
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-2xs btn-outline-success" onclick="setSemuaStatus('mandiri')">
                            <i class="fa-solid fa-check-double me-1"></i> Semua Mandiri (M)
                        </button>
                        <button type="button" class="btn btn-2xs btn-outline-warning" onclick="setSemuaStatus('supervisi')">
                            <i class="fa-solid fa-user-clock me-1"></i> Semua Supervisi (S)
                        </button>
                        <button type="button" class="btn btn-2xs btn-outline-primary" onclick="tambahTindakanBaru()">
                            <i class="fa-solid fa-plus me-1"></i> Tambah Tindakan Baru
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-sm" id="tabelRkk">
                            <thead class="table-light">
                                <tr>
                                    <th width="70">Kode</th>
                                    <th>Rincian Tindakan / Prosedur Medis</th>
                                    <th width="150">Kategori Bidang</th>
                                    <th width="220" class="text-center">Kewenangan Klinis</th>
                                    <th>Catatan / Syarat</th>
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

                <div class="card-footer bg-light p-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="fs-xs text-muted">
                        <i class="fa-solid fa-circle-info text-teal me-1"></i>
                        Status RKK: <strong>M</strong> = Mandiri Penuh &bull; <strong>S</strong> = Dengan Supervisi Konsultan/Senior &bull; <strong>T</strong> = Tidak Berwenang
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="aksi" value="simpan_saja" class="btn btn-light border btn-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Draf Sidang
                        </button>
                        <button type="submit" name="aksi" value="terbitkan_rekomendasi" class="btn btn-teal text-white btn-sm shadow-xs fw-semibold">
                            <i class="fa-solid fa-file-signature me-1"></i> Simpan & Terbitkan Rekomendasi Digital ke Direktur
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let barisIndex = {{ count($rkkItems) }};

    function setSemuaStatus(status) {
        if (status === 'mandiri') {
            document.querySelectorAll('.rkk-status-mandiri').forEach(radio => radio.checked = true);
        } else if (status === 'supervisi') {
            document.querySelectorAll('.rkk-status-supervisi').forEach(radio => radio.checked = true);
        }
    }

    function hapusBarisRkk(index) {
        const baris = document.getElementById('rowRkk_' + index);
        if (baris) {
            baris.remove();
        }
    }

    function tambahTindakanBaru() {
        const tbody = document.getElementById('tbodyRkk');
        const i = barisIndex++;
        const tr = document.createElement('tr');
        tr.id = 'rowRkk_' + i;
        tr.innerHTML = `
            <td>
                <input type="text" name="rkk[${i}][kode]" class="form-control form-control-sm font-monospace text-center fs-xs" value="PLUS-${i+1}" required>
            </td>
            <td>
                <input type="text" name="rkk[${i}][nama]" class="form-control form-control-sm fw-semibold fs-xs" placeholder="Nama tindakan baru yang diajukan..." required>
            </td>
            <td>
                <input type="text" name="rkk[${i}][kategori]" class="form-control form-control-sm fs-xs text-muted" value="Permohonan Baru">
            </td>
            <td>
                <div class="btn-group btn-group-sm w-100" role="group">
                    <input type="radio" class="btn-check rkk-status-mandiri" name="rkk[${i}][status]" id="status_m_${i}" value="mandiri" autocomplete="off">
                    <label class="btn btn-outline-success fs-xs py-1 px-2" for="status_m_${i}"><strong>M</strong> Mandiri</label>

                    <input type="radio" class="btn-check rkk-status-supervisi" name="rkk[${i}][status]" id="status_s_${i}" value="supervisi" checked autocomplete="off">
                    <label class="btn btn-outline-warning fs-xs py-1 px-2" for="status_s_${i}"><strong>S</strong> Supervisi</label>

                    <input type="radio" class="btn-check" name="rkk[${i}][status]" id="status_t_${i}" value="tidak_disetujui" autocomplete="off">
                    <label class="btn btn-outline-danger fs-xs py-1 px-1" for="status_t_${i}"><strong>T</strong></label>
                </div>
            </td>
            <td>
                <input type="text" name="rkk[${i}][catatan]" class="form-control form-control-sm fs-2xs" placeholder="Syarat supervisi...">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-2xs btn-outline-danger" onclick="hapusBarisRkk(${i})">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }
</script>
@endsection
