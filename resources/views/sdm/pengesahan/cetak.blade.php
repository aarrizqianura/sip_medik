<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Pengesahan Direksi & e-Arsip - {{ $dokter->user->nama }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #0f172a;
        }
        .page-sheet {
            background: #ffffff;
            max-width: 900px;
            margin: 20px auto;
            padding: 40px 50px;
            border-radius: 4px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        }
        .kop-border {
            border-bottom: 3px double #0f172a;
        }
        @media print {
            body {
                background: #ffffff;
                margin: 0;
            }
            .page-sheet {
                box-shadow: none;
                margin: 0;
                padding: 20px 30px;
                max-width: 100%;
            }
            .d-print-none {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="d-print-none text-center py-3 bg-dark text-white">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <i class="fa-solid fa-file-shield text-warning me-2"></i>
            Dokumen Resmi Pengesahan Direksi & Repositori e-Arsip (Tahap 4)
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-light fw-bold me-2">
                <i class="fa-solid fa-print me-1"></i> Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="btn btn-sm btn-outline-light">
                Tutup
            </button>
        </div>
    </div>
</div>

<div class="page-sheet">
    <!-- KOP RESMI RUMAH SAKIT -->
    <div class="d-flex align-items-center gap-3 kop-border pb-3 mb-4">
        <div class="text-center" style="width: 75px;">
            <i class="fa-solid fa-hospital fs-1 text-dark"></i>
        </div>
        <div class="text-center flex-grow-1">
            <div class="text-uppercase fw-bold text-dark fs-6" style="letter-spacing: 1px;">Pemerintah Provinsi Kalimantan Selatan</div>
            <div class="text-uppercase fw-bold text-dark fs-6">Dinas Kesehatan</div>
            <h5 class="fw-bold text-dark mb-0 text-uppercase" style="letter-spacing: 0.5px;">RSUD dr. H. Moch. Ansari Saleh Banjarmasin</h5>
            <div class="fw-bold text-secondary text-uppercase fs-xs">Bagian Sumber Daya Manusia (SDM) &bull; Komite Medik</div>
            <div class="fs-xs text-muted mt-1">
                Jl. Brigjend H. Hasan Basry No. 1, Kota Banjarmasin, Kalimantan Selatan 70125 &bull; Telp: (0511) 3300741
            </div>
        </div>
        <div class="text-center" style="width: 75px;">
            <div class="border p-1 rounded bg-white">
                <i class="fa-solid fa-qrcode fs-2 text-dark"></i>
                <div class="text-muted font-monospace" style="font-size: 8px;">VALIDATED</div>
            </div>
        </div>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="text-center mb-4">
        <h5 class="fw-bold text-dark mb-1 text-uppercase" style="text-decoration: underline;">
            BERITA ACARA PENGESAHAN DIREKSI & PENGARSIPAN DIGITAL TERPADU (e-Arsip)
        </h5>
        <div class="fs-xs text-secondary font-monospace">
            NOMOR REGISTRASI ARSIP: 800/ARSIP-SDM/RSAS/{{ $dokter->id }}/{{ date('Y') }}
        </div>
    </div>

    <!-- PENGANTAR -->
    <div class="fs-sm text-dark mb-3" style="text-align: justify; line-height: 1.6;">
        Pada hari ini, <strong>{{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}</strong>, bertempat di RSUD dr. H. Moch. Ansari Saleh Banjarmasin, Bagian Sumber Daya Manusia (SDM) atas otoritas Direktur Rumah Sakit secara resmi menerbitkan Surat Keputusan (SK) Direktur mengenai penetapan Surat Penugasan Klinis (SPK) dan Rincian Kewenangan Klinis (RKK) terbaru, serta mengunci seluruh berkas legalitas ke dalam <strong>Repositori Digital Kepegawaian (e-Arsip)</strong> bagi:
    </div>

    <!-- IDENTITAS DOKTER -->
    <div class="card bg-light border-0 mb-4">
        <div class="card-body p-3">
            <table class="table table-sm table-borderless mb-0 fs-sm">
                <tr>
                    <td width="200" class="text-muted">Nama Lengkap & Gelar</td>
                    <td class="fw-bold text-dark">: {{ $dokter->user->nama }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Nomor Induk Pegawai (NIP/NRP)</td>
                    <td class="font-monospace fw-semibold">: {{ $dokter->nip_nrp }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Kelompok Staf Medis (KSM)</td>
                    <td>: {{ $dokter->ksm->nama_ksm }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Kualifikasi Klinis / Spesialis</td>
                    <td>: {{ $dokter->spesialisasi }} @if($dokter->subspesialis) ({{ $dokter->subspesialis }}) @endif</td>
                </tr>
                <tr>
                    <td class="text-muted">Status Kepegawaian</td>
                    <td>: {{ $dokter->status_kepegawaian }} &bull; Tempat Praktik: {{ $dokter->tempat_praktik_label }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Status Profil Hukum</td>
                    <td>: <span class="badge bg-success text-white px-2 py-1"><i class="fa-solid fa-shield-check me-1"></i> AKTIF_LEGAL (Hak Praktik Penuh)</span></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- TABEL 4 PILAR REPOSITORI DIGITAL KEPEGAWAIAN -->
    <div class="mb-4">
        <div class="fw-bold text-dark fs-sm mb-2 text-uppercase">
            <i class="fa-solid fa-lock text-success me-1"></i> Ringkasan Instrumen 4 Pilar e-Arsip Terkunci:
        </div>
        <table class="table table-bordered table-sm align-middle fs-xs mb-0">
            <thead class="table-light text-center">
                <tr>
                    <th width="30">No</th>
                    <th>Pilar Instrumen</th>
                    <th>Nomor Identitas Dokumen</th>
                    <th>Masa Berlaku</th>
                    <th>Status Validasi & Pelacakan</th>
                    <th width="90">Status Arsip</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center fw-bold">1</td>
                    <td>
                        <div class="fw-bold text-dark">Surat Tanda Registrasi (STR)</div>
                        <small class="text-muted">Registrasi Tenaga Medis Konsil</small>
                    </td>
                    <td class="font-monospace fw-bold">{{ $dokter->nomor_str ?: '-' }}</td>
                    <td>{{ $dokter->status_str === 'seumur_hidup' ? 'Seumur Hidup (UU No. 17/2023)' : ($dokter->tanggal_kadaluarsa_str ? $dokter->tanggal_kadaluarsa_str->format('d/m/Y') : '-') }}</td>
                    <td><span class="text-success fw-semibold"><i class="fa-solid fa-check me-1"></i> Tervalidasi SatuSehat SDMK</span></td>
                    <td class="text-center"><span class="badge bg-success">TERKUNCI</span></td>
                </tr>
                <tr>
                    <td class="text-center fw-bold">2</td>
                    <td>
                        <div class="fw-bold text-dark">Surat Izin Praktik (SIP)</div>
                        <small class="text-muted">Izin Operasional DPMPTSP</small>
                    </td>
                    <td class="font-monospace fw-bold">{{ $sip?->nomor_sip ?: '-' }}</td>
                    <td>s.d {{ $sip?->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</td>
                    <td><span class="text-primary fw-semibold"><i class="fa-solid fa-radar me-1"></i> Terdaftar Mesin Pelacak Otomatis</span></td>
                    <td class="text-center"><span class="badge bg-success">TERKUNCI</span></td>
                </tr>
                <tr>
                    <td class="text-center fw-bold">3</td>
                    <td>
                        <div class="fw-bold text-dark">Surat Penugasan Klinis (SPK)</div>
                        <small class="text-muted">Keputusan Direktur RSUD</small>
                    </td>
                    <td class="font-monospace fw-bold">{{ $dokter->nomor_sk_direktur ?: ($spk?->nomor_sk_direktur ?: ($spk?->nomor_spk ?: '-')) }}</td>
                    <td>
                        {{ $spk?->tanggal_terbit_spk ? $spk->tanggal_terbit_spk->format('d/m/Y') : date('d/m/Y') }} s.d 
                        <strong>{{ $spk?->tanggal_berakhir_spk ? $spk->tanggal_berakhir_spk->format('d/m/Y') : date('d/m/Y', strtotime('+3 years')) }}</strong>
                    </td>
                    <td><span class="text-success fw-semibold"><i class="fa-solid fa-stamp me-1"></i> Disahkan Aktif (Siklus 3 Tahun)</span></td>
                    <td class="text-center"><span class="badge bg-success">TERKUNCI</span></td>
                </tr>
                <tr>
                    <td class="text-center fw-bold">4</td>
                    <td>
                        <div class="fw-bold text-dark">Rincian Kewenangan Klinis (RKK)</div>
                        <small class="text-muted">Lampiran Tindakan Medis Sah</small>
                    </td>
                    <td class="font-monospace">Lampiran SK Direktur</td>
                    <td>Berlaku mengikuti SPK</td>
                    <td>
                        <strong>{{ $spk?->rkk_mandiri_count ?? 0 }} Mandiri</strong> &bull; 
                        <strong>{{ $spk?->rkk_supervisi_count ?? 0 }} Supervisi</strong>
                    </td>
                    <td class="text-center"><span class="badge bg-success">TERKUNCI</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- PERNYATAAN HUKUM -->
    <div class="fs-xs text-secondary mb-4 p-3 bg-light rounded border" style="line-height: 1.6; text-align: justify;">
        <strong>Klausul Ketetapan:</strong> Dengan diterbitkannya Berita Acara ini, dokter yang bersangkutan dinyatakan memiliki legalitas hak praktik penuh (AKTIF_LEGAL) di lingkungan RSUD dr. H. Moch. Ansari Saleh Banjarmasin. Seluruh berkas digital telah dienkripsi dan dikunci ke dalam pangkalan data e-Arsip Kepegawaian. Tanggal jatuh tempo SIP resmi masuk ke dalam mesin pelacakan otomatis untuk peringatan perpanjangan berkala.
    </div>

    <!-- TANDA TANGAN LEMBAR PENGESAHAN -->
    <div class="row fs-sm text-center pt-3">
        <div class="col-6">
            <div>Mengetahui & Menyetujui,</div>
            <div class="fw-bold text-dark text-uppercase mt-1">Kepala Bagian SDM & Kepegawaian</div>
            <div style="height: 70px;"></div>
            <div class="fw-bold text-dark text-decoration-underline">Hj. Rusmawati, S.Kep, Ns, MM</div>
            <div class="text-muted fs-xs font-monospace">NIP. 19740510 199703 2 004</div>
        </div>
        <div class="col-6">
            <div>Banjarmasin, {{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</div>
            <div class="fw-bold text-dark text-uppercase mt-1">Direktur RSUD dr. H. Moch. Ansari Saleh</div>
            <div style="height: 70px;"></div>
            <div class="fw-bold text-dark text-decoration-underline">dr. Among Wibowo, M.Kes, Sp.S</div>
            <div class="text-muted fs-xs font-monospace">Pembina Utama Muda (IV/c) &bull; NIP. 19680325 199903 1 003</div>
        </div>
    </div>
</div>

</body>
</html>
