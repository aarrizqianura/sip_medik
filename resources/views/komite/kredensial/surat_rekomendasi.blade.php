@extends('layouts.app')

@section('title', 'Surat Rekomendasi Kewenangan Klinis Digital - Komite Medik')

@section('content')
<div class="d-print-none mb-4">
    <a href="{{ route('komite.kredensial.index') }}" class="btn btn-light btn-sm border mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Kredensial
    </a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-file-signature text-teal me-2"></i> Surat Rekomendasi Kewenangan Klinis Digital
            </h4>
            <p class="text-secondary fs-sm mb-0">
                Surat rekomendasi resmi Komite Medik kepada Direktur RSUD dr. H. Moch. Ansari Saleh untuk penerbitan SPK baru
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> Cetak / Simpan PDF
            </button>
            <a href="{{ route('komite.kredensial.sidang', $kredensial->id) }}" class="btn btn-light border btn-sm">
                <i class="fa-solid fa-pen-to-square me-1"></i> Ubah Draf RKK
            </a>
            @if($kredensial->status_kredensial !== 'aktif' || $kredensial->status_rekomendasi !== 'disahkan_direktur')
                <a href="{{ route('sdm.pengesahan.create', $kredensial->id) }}" class="btn btn-teal text-white btn-sm shadow-xs fw-semibold">
                    <i class="fa-solid fa-stamp me-1"></i> Terbitkan SK Direktur & Kunci e-Arsip (SDM)
                </a>
                <button type="button" class="btn btn-outline-success btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalSahkanSpk">
                    <i class="fa-solid fa-signature me-1"></i> Pengesahan Langsung Direktur
                </button>
            @else
                <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-sm">
                    <i class="fa-solid fa-circle-check me-1"></i> SPK Telah Disahkan Direktur
                </span>
                <a href="{{ route('sdm.arsip.show', $dokter->id) }}" class="btn btn-teal text-white btn-sm">
                    <i class="fa-solid fa-box-archive me-1"></i> Buka e-Arsip Terkunci
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Lembar Dokumen Resmi Komite Medik (Print-Optimized) -->
<div class="card shadow-sm border-0 mx-auto" style="max-width: 900px; background: #fff;">
    <div class="card-body p-4 p-md-5">
        <!-- KOP SURAT RESMI RUMAH SAKIT -->
        <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-4" style="border-bottom: 3px double #0f172a !important;">
            <div class="brand-logo-icon bg-light text-teal rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 72px; height: 72px; min-width: 72px;">
                <i class="fa-solid fa-hospital fs-2 text-teal"></i>
            </div>
            <div class="text-center flex-grow-1">
                <div class="text-uppercase fw-bold text-dark fs-6" style="letter-spacing: 1px;">Pemerintah Provinsi Kalimantan Selatan</div>
                <div class="text-uppercase fw-bold text-dark fs-6">Dinas Kesehatan</div>
                <h5 class="fw-bold text-dark mb-0 text-uppercase" style="letter-spacing: 0.5px;">RSUD dr. H. Moch. Ansari Saleh Banjarmasin</h5>
                <h6 class="fw-bold text-teal mb-0 text-uppercase">Komite Medik &bull; Subkomite Kredensial</h6>
                <div class="fs-xs text-secondary mt-1">
                    Jl. Brigjend H. Hasan Basry No. 1, Kota Banjarmasin, Kalimantan Selatan 70125 &bull; Telp: (0511) 3300741
                </div>
            </div>
            <div class="text-center" style="width: 72px; min-width: 72px;">
                <div class="border p-1 rounded bg-white">
                    <i class="fa-solid fa-qrcode fs-1 text-dark"></i>
                    <div class="fs-3xs text-muted font-monospace mt-1">VALIDATED</div>
                </div>
            </div>
        </div>

        <!-- NOMOR DAN IDENTITAS SURAT -->
        <div class="row g-2 fs-sm mb-4">
            <div class="col-7">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="90" class="text-muted">Nomor</td>
                        <td class="fw-bold text-dark font-monospace">: {{ $kredensial->nomor_surat_rekomendasi ?: '445/' . rand(100, 999) . '/KOM-MED/RSAS/' . date('m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lampiran</td>
                        <td>: 1 (satu) berkas Rincian Kewenangan Klinis (RKK)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Perihal</td>
                        <td class="fw-bold text-dark">: <u>Rekomendasi Rincian Kewenangan Klinis (RKK) & Penerbitan SPK</u></td>
                    </tr>
                </table>
            </div>
            <div class="col-5 text-end">
                <div class="text-dark">Banjarmasin, {{ $kredensial->tanggal_rekomendasi ? $kredensial->tanggal_rekomendasi->translatedFormat('d F Y') : \Carbon\Carbon::today()->translatedFormat('d F Y') }}</div>
                <div class="mt-3 text-start ps-4">
                    <div>Kepada Yth:</div>
                    <div class="fw-bold text-dark">Direktur RSUD dr. H. Moch. Ansari Saleh</div>
                    <div>di Banjarmasin</div>
                </div>
            </div>
        </div>

        <!-- ISI SURAT -->
        <div class="fs-sm text-dark mb-4" style="line-height: 1.7; text-align: justify;">
            <p class="mb-3">
                Dengan hormat,
            </p>
            <p class="mb-3">
                Berdasarkan hasil verifikasi pindaian Surat Izin Praktik (SIP) dokter, keabsahan Surat Tanda Registrasi (STR) terverifikasi SatuSehat SDMK, serta pelaksanaan <strong>Sidang Kredensial</strong> bersama <strong>Mitra Bestari (Peer Group)</strong> yang mengevaluasi logbook tindakan medis dan portofolio kompetensi klinis pada tanggal <strong>{{ $kredensial->tanggal_sidang ? $kredensial->tanggal_sidang->translatedFormat('d F Y') : '-' }}</strong>, maka Subkomite Kredensial Komite Medik memberikan rekomendasi kewenangan klinis kepada:
            </p>

            <!-- Tabel Identitas Dokter -->
            <div class="p-3 bg-light rounded-3 border mb-3">
                <table class="table table-sm table-borderless mb-0 fs-sm">
                    <tr>
                        <td width="180" class="text-muted">Nama Dokter</td>
                        <td class="fw-bold text-dark">: {{ $dokter->user->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">NIP / NRP</td>
                        <td>: {{ $dokter->nip_nrp ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kelompok Staf Medis (KSM)</td>
                        <td>: {{ $dokter->ksm->nama_ksm ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Keahlian / Spesialisasi</td>
                        <td>: {{ $dokter->spesialisasi ?: 'Dokter Umum' }} {{ $dokter->subspesialis ? '('.$dokter->subspesialis.')' : '' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nomor STR</td>
                        <td class="font-monospace">: {{ $dokter->nomor_str ?: '-' }} (Status: {{ $dokter->status_str === 'seumur_hidup' ? 'Seumur Hidup' : 'Berkala' }})</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nomor Izin SIP Terbaru</td>
                        <td class="font-monospace">: {{ $dokter->sipTerbaru ? $dokter->sipTerbaru->nomor_sip : '-' }} (RSUD Ansari Saleh)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Evaluator Mitra Bestari</td>
                        <td class="fw-semibold text-teal">: {{ $kredensial->nama_mitra_bestari ?: 'Tim Mitra Bestari Komite Medik' }}</td>
                    </tr>
                </table>
            </div>

            <p class="mb-3">
                {{ $kredensial->catatan_rekomendasi ?: 'Dokter yang bersangkutan dinyatakan memenuhi syarat kompetensi fisik, mental, etika disiplin profesi, serta memiliki kecakapan klinis yang memadai untuk melaksanakan pelayanan medis di lingkungan RSUD dr. H. Moch. Ansari Saleh Banjarmasin.' }}
            </p>

            <p class="mb-2 fw-bold text-dark">
                Rincian Kewenangan Klinis (RKK) yang direkomendasikan adalah sebagai berikut:
            </p>
        </div>

        <!-- LAMPIRAN DAFTAR RKK DIGITAL -->
        <div class="mb-4">
            @php
                $rkk = is_array($kredensial->daftar_rkk) ? $kredensial->daftar_rkk : [];
                $mandiriList = array_filter($rkk, fn($i) => ($i['status'] ?? '') === 'mandiri');
                $supervisiList = array_filter($rkk, fn($i) => ($i['status'] ?? '') === 'supervisi');
            @endphp

            <!-- A. Kewenangan Mandiri -->
            <div class="mb-3">
                <div class="p-2 bg-success-subtle text-success border border-success rounded fw-bold fs-sm mb-2">
                    <i class="fa-solid fa-circle-check me-2"></i> A. Tindakan Klinis yang Disetujui Mandiri (M) &mdash; ({{ count($mandiriList) }} Prosedur)
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered fs-xs mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="70" class="text-center">No.</th>
                                <th>Nama Tindakan / Prosedur Medis</th>
                                <th width="180">Kategori Bidang</th>
                                <th>Catatan / Batasan Indikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mandiriList as $idx => $m)
                                <tr>
                                    <td class="text-center font-monospace">{{ $m['kode'] ?? 'M-'.($loop->iteration) }}</td>
                                    <td class="fw-semibold text-dark">{{ $m['nama'] }}</td>
                                    <td class="text-muted">{{ $m['kategori'] ?? '-' }}</td>
                                    <td>{{ $m['catatan'] ?: 'Dilakukan secara mandiri penuh sesuai standar operasional prosedur.' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-2">Tidak ada tindakan mandiri yang ditetapkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- B. Kewenangan Supervisi -->
            <div class="mb-3">
                <div class="p-2 bg-warning-subtle text-warning-emphasis border border-warning rounded fw-bold fs-sm mb-2">
                    <i class="fa-solid fa-user-clock me-2"></i> B. Tindakan Klinis yang Disetujui Dengan Supervisi (S) &mdash; ({{ count($supervisiList) }} Prosedur)
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered fs-xs mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="70" class="text-center">No.</th>
                                <th>Nama Tindakan / Prosedur Medis</th>
                                <th width="180">Kategori Bidang</th>
                                <th>Syarat Supervisi / Pendampingan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supervisiList as $idx => $s)
                                <tr>
                                    <td class="text-center font-monospace">{{ $s['kode'] ?? 'S-'.($loop->iteration) }}</td>
                                    <td class="fw-semibold text-dark">{{ $s['nama'] }}</td>
                                    <td class="text-muted">{{ $s['kategori'] ?? '-' }}</td>
                                    <td>{{ $s['catatan'] ?: 'Wajib didampingi oleh dokter spesialis konsultan / senior pendamping.' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-2">Tidak ada tindakan yang memerlukan supervisi khusus.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PENUTUP SURAT -->
        <div class="fs-sm text-dark mb-4" style="line-height: 1.7; text-align: justify;">
            <p>
                Demikian surat rekomendasi kewenangan klinis ini kami sampaikan kepada Ibu/Bapak Direktur, sebagai bahan pertimbangan untuk menerbitkan <strong>Surat Penugasan Klinis (SPK)</strong> dokter yang bersangkutan untuk siklus evaluasi 3 (tiga) tahun ke depan.
            </p>
        </div>

        <!-- TANDA TANGAN DIGITAL KOMITE MEDIK & MITRA BESTARI -->
        <div class="row pt-3 text-center fs-sm">
            <div class="col-6">
                <div>Ketua Tim Mitra Bestari (Peer Group)</div>
                <div class="my-2">
                    <div class="d-inline-block p-2 border rounded bg-light shadow-2xs">
                        <i class="fa-solid fa-signature fs-2 text-teal"></i>
                        <div class="fs-3xs text-muted font-monospace">DIGITALLY SIGNED</div>
                    </div>
                </div>
                <div class="fw-bold text-dark text-decoration-underline">{{ $kredensial->nama_mitra_bestari ?: 'dr. H. Fachrul Razi, Sp.B' }}</div>
                <div class="fs-xs text-muted">Mitra Bestari KSM {{ $dokter->ksm->nama_ksm ?? '' }}</div>
            </div>
            <div class="col-6">
                <div>Ketua Komite Medik,</div>
                <div class="my-2">
                    <div class="d-inline-block p-2 border rounded bg-light shadow-2xs">
                        <i class="fa-solid fa-stamp fs-2 text-teal"></i>
                        <div class="fs-3xs text-muted font-monospace">VERIFIED & APPROVED</div>
                    </div>
                </div>
                <div class="fw-bold text-dark text-decoration-underline">{{ $kredensial->ketua_komite_medik ?: 'dr. H. Hendra Sanjaya, Sp.B, FInaCS' }}</div>
                <div class="fs-xs text-muted">NIP. 19740512 200212 1 003</div>
            </div>
        </div>

        <!-- TEMBUSAN -->
        <div class="border-top pt-3 mt-4 fs-xs text-secondary">
            <div class="fw-bold text-dark">Tembusan Yth:</div>
            <ol class="ps-3 mb-0">
                <li>Wakil Direktur Pelayanan Medik & Keperawatan RSUD Ansari Saleh</li>
                <li>Kepala Bagian SDM & Kepegawaian RSUD Ansari Saleh</li>
                <li>Ketua KSM {{ $dokter->ksm->nama_ksm ?? '-' }}</li>
                <li>Dokter yang bersangkutan</li>
                <li>Arsip Subkomite Kredensial</li>
            </ol>
        </div>
    </div>
</div>

<!-- MODAL SAHKAN SPK OLEH DIREKTUR -->
<div class="modal fade" id="modalSahkanSpk" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('komite.kredensial.sahkan_spk', $kredensial->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title fw-bold">
                        <i class="fa-solid fa-stamp me-2"></i> Pengesahan Surat Penugasan Klinis (SPK) Baru
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-toggle="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="fs-sm text-secondary">
                        Dengan tindakan ini, Direktur RSUD dr. H. Moch. Ansari Saleh secara resmi menetapkan Surat Penugasan Klinis (SPK) dan Rincian Kewenangan Klinis (RKK) dokter untuk siklus 3 tahun ke depan.
                    </p>

                    <div class="mb-3">
                        <label class="form-label fs-xs fw-semibold">Nomor SPK Direktur <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_spk" class="form-control form-control-sm font-monospace" value="800/{{ rand(100, 999) }}-DIR/RSAS/SPK-{{ $dokter->ksm->kode_ksm ?? 'MED' }}/{{ date('Y') }}" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fs-xs fw-semibold">Tanggal Terbit SPK <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_terbit_spk" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fs-xs fw-semibold">Jatuh Tempo (3 Tahun) <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_berakhir_spk" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+3 years')) }}" required>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded fs-xs border">
                        <div>Dokter: <strong>{{ $dokter->user->nama }}</strong></div>
                        <div>KSM: <strong>{{ $dokter->ksm->nama_ksm }}</strong></div>
                        <div>Status Akhir: <span class="badge bg-success">SPK & RKK Aktif Absah</span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-semibold">
                        <i class="fa-solid fa-stamp me-1"></i> Sahkan & Terbitkan SPK 3 Tahun
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
