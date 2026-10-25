@extends('layouts.app')

@section('title', 'Pusat Laporan & Audit Akreditasi KPS')

@section('content')
<!-- Header Halaman -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
    <div>
        <h4 class="fw-bold text-dark mb-0">Pusat Laporan & Audit Akreditasi KPS</h4>
        <p class="text-secondary fs-sm mb-0">Tata kelola audit kepatuhan legalitas SIP & wewenang klinis dokter RSUD dr. H. Moch. Ansari Saleh</p>
    </div>
    <span class="badge bg-teal-subtle text-teal border border-teal-subtle px-2 py-1 fs-xs fw-semibold">
        <i class="fa-solid fa-stamp me-1"></i> Standar Baku 10 Data / Halaman
    </span>
</div>

<!-- Bilah Pengendali Terpadu (Unified Toolbar) -->
<div class="card shadow-xs mb-3 border">
    <div class="card-body p-2 bg-light">
        <form method="GET" action="{{ route('sdm.laporan.index') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <!-- Navigasi 5 Format Laporan (Segmented Toolbar) -->
            <div class="d-flex align-items-center flex-wrap gap-1">
                <span class="fs-xs fw-bold text-secondary text-uppercase me-1"><i class="fa-solid fa-file-invoice me-1"></i> Format:</span>
                <div class="btn-group btn-group-sm" role="group" aria-label="Format Laporan">
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '1']) }}" class="btn {{ $jenis == '1' ? 'btn-teal text-white fw-bold' : 'btn-white border text-dark' }}" title="1. Data Dokter & SIP">
                        1. Dokter & SIP
                    </a>
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '2']) }}" class="btn {{ $jenis == '2' ? 'btn-teal text-white fw-bold' : 'btn-white border text-dark' }}" title="2. Monitoring Masa Berlaku (Audit KPS)">
                        2. Audit KPS
                    </a>
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '3']) }}" class="btn {{ $jenis == '3' ? 'btn-teal text-white fw-bold' : 'btn-white border text-dark' }}" title="3. Verifikasi Komite Medik">
                        3. Komite Medik
                    </a>
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '4']) }}" class="btn {{ $jenis == '4' ? 'btn-teal text-white fw-bold' : 'btn-white border text-dark' }}" title="4. Masa Berlaku SPK & RKK">
                        4. SPK & RKK
                    </a>
                    <a href="{{ route('sdm.laporan.index', ['jenis' => '5']) }}" class="btn {{ $jenis == '5' ? 'btn-teal text-white fw-bold' : 'btn-white border text-dark' }}" title="5. Log Notifikasi Pengingat">
                        5. Log Notifikasi
                    </a>
                </div>
            </div>

            <!-- Filter Parameter Kontekstual & Tombol Ekspor Sejajar -->
            <div class="d-flex align-items-center flex-wrap gap-2 ms-auto">
                <input type="hidden" name="jenis" value="{{ $jenis }}">

                @if(in_array($jenis, ['1', '2', '3', '4']))
                    <select name="id_ksm" class="form-select form-select-sm" style="width: auto; min-width: 140px;">
                        <option value="">-- Semua KSM --</option>
                        @foreach($ksmList as $ksm)
                            <option value="{{ $ksm->id }}" {{ request('id_ksm') == $ksm->id ? 'selected' : '' }}>{{ $ksm->nama_ksm }}</option>
                        @endforeach
                    </select>
                @endif

                @if($jenis == '1')
                    <select name="status_kepegawaian" class="form-select form-select-sm" style="width: auto;">
                        <option value="">-- Kepegawaian --</option>
                        <option value="PNS" {{ request('status_kepegawaian') == 'PNS' ? 'selected' : '' }}>PNS</option>
                        <option value="PPPK" {{ request('status_kepegawaian') == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                        <option value="BLUD" {{ request('status_kepegawaian') == 'BLUD' ? 'selected' : '' }}>BLUD</option>
                        <option value="Mitra" {{ request('status_kepegawaian') == 'Mitra' ? 'selected' : '' }}>Mitra</option>
                    </select>
                    <select name="status_sip" class="form-select form-select-sm" style="width: auto;">
                        <option value="">-- Status SIP --</option>
                        <option value="aktif" {{ request('status_sip') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="due_soon" {{ request('status_sip') == 'due_soon' ? 'selected' : '' }}>Due Soon</option>
                        <option value="due" {{ request('status_sip') == 'due' ? 'selected' : '' }}>Due (Kritis)</option>
                        <option value="overdue" {{ request('status_sip') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                @elseif($jenis == '2')
                    <select name="status" class="form-select form-select-sm" style="width: auto;">
                        <option value="">-- Status Legalitas --</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif (>90 Hari)</option>
                        <option value="due_soon" {{ request('status') == 'due_soon' ? 'selected' : '' }}>Due Soon (31-90 Hari)</option>
                        <option value="due" {{ request('status') == 'due' ? 'selected' : '' }}>Due / Kritis (≤30 Hari)</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue / Kedaluwarsa</option>
                    </select>
                @elseif($jenis == '3')
                    <select name="status_verifikasi" class="form-select form-select-sm" style="width: auto;">
                        <option value="">-- Verifikasi --</option>
                        <option value="disetujui" {{ request('status_verifikasi') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('status_verifikasi') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="menunggu" {{ request('status_verifikasi') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    </select>
                    <input type="date" name="tgl_mulai" class="form-control form-control-sm" style="width: auto;" value="{{ request('tgl_mulai') }}">
                    <input type="date" name="tgl_selesai" class="form-control form-control-sm" style="width: auto;" value="{{ request('tgl_selesai') }}">
                @elseif($jenis == '4')
                    <select name="status_kredensial" class="form-select form-select-sm" style="width: auto;">
                        <option value="">-- Kredensial --</option>
                        <option value="aktif" {{ request('status_kredensial') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="evaluasi" {{ request('status_kredensial') == 'evaluasi' ? 'selected' : '' }}>Evaluasi (≤ 90 Hari)</option>
                        <option value="kedaluwarsa" {{ request('status_kredensial') == 'kedaluwarsa' ? 'selected' : '' }}>Kedaluwarsa</option>
                    </select>
                @elseif($jenis == '5')
                    <select name="status_terkirim" class="form-select form-select-sm" style="width: auto;">
                        <option value="">-- Status Kirim --</option>
                        <option value="1" {{ request('status_terkirim') == '1' ? 'selected' : '' }}>Sukses</option>
                        <option value="0" {{ request('status_terkirim') == '0' ? 'selected' : '' }}>Gagal</option>
                    </select>
                    <input type="date" name="tgl_mulai" class="form-control form-control-sm" style="width: auto;" value="{{ request('tgl_mulai') }}">
                    <input type="date" name="tgl_selesai" class="form-control form-control-sm" style="width: auto;" value="{{ request('tgl_selesai') }}">
                @endif

                <button type="submit" class="btn btn-secondary btn-sm" title="Terapkan Filter">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('sdm.laporan.index', ['jenis' => $jenis]) }}" class="btn btn-light btn-sm border" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>

                <!-- Pemisah Vertikal -->
                <div class="vr mx-1 d-none d-sm-block" style="height: 24px;"></div>

                <!-- Tombol Ekspor Resmi -->
                <a href="{{ route('sdm.laporan.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" target="_blank" title="Ekspor PDF">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>PDF</span>
                </a>
                <a href="{{ route('sdm.laporan.excel', request()->query()) }}" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1" title="Ekspor Excel">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Excel</span>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bilah Peringatan Mini Overdue (Collapsible Alert) Khusus Format 2 -->
@if($jenis == '2')
    <div class="alert alert-danger border-danger p-2 mb-3 shadow-xs">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger text-white fs-3xs px-2 py-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> POKJA KPS 9</span>
                <strong class="text-danger fs-xs mb-0">Daftar Staf Medis Non-Aktif Pelayanan (Overdue & Auto-Suspend)</strong>
                <span class="badge bg-danger rounded-pill px-2 py-0 fs-3xs">{{ $stafNonAktifPelayanan->count() }} Dokter Ditangguhkan</span>
            </div>
            <button class="btn btn-2xs btn-outline-danger d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOverdue" aria-expanded="true" aria-controls="collapseOverdue">
                <i class="fa-solid fa-chevron-down"></i>
                <span>Detail Staf Overdue</span>
            </button>
        </div>
        <div class="collapse show mt-2 pt-2 border-top border-danger-subtle" id="collapseOverdue">
            <p class="fs-3xs text-danger-emphasis mb-2">
                Berdasarkan tata kelola kredensial rumah sakit dan kepatuhan standar akreditasi <strong>Pokja KPS</strong>, dokter yang masa berlaku SIP-nya telah kedaluwarsa (&le; 0 hari) tanpa dispensasi aktif secara otomatis <strong>dikunci statusnya (Overdue)</strong> dan instrumen <strong>SPK/RKK beralih menjadi DITANGGUHKAN</strong>. Dokter terblokir secara administratif dari wewenang tindakan klinis mandiri guna mencegah temuan audit eksternal serta melindungi fasyankes dari tuntutan malpraktik.
            </p>

            @if($stafNonAktifPelayanan->isNotEmpty())
                <div class="table-responsive bg-white rounded border border-danger-subtle mb-1">
                    <table class="table table-sm table-hover align-middle mb-0 fs-xs">
                        <thead class="table-danger text-danger-emphasis">
                            <tr>
                                <th width="30">#</th>
                                <th>Nama Dokter & KSM</th>
                                <th>Nomor SIP & Tgl Kedaluwarsa</th>
                                <th>Kunci Status SIP</th>
                                <th>Status SPK & RKK</th>
                                <th>Wewenang Tindakan Klinis</th>
                                <th>Mitigasi Hukum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stafNonAktifPelayanan as $i => $dok)
                                @php
                                    $sipOver = $dok->sipTerbaru;
                                    $spkOver = $dok->kredensialTerbaru ?: $dok->spkTerbaru;
                                    $sisaOver = $sipOver ? $sipOver->hitungSisaHari() : 0;
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $dok->user->nama ?? '-' }}</span><br>
                                        <span class="text-muted">{{ $dok->ksm->nama_ksm ?? '-' }} &bull; {{ $dok->spesialisasi }}</span>
                                    </td>
                                    <td>
                                        <span class="font-monospace fw-semibold text-danger">{{ $sipOver->nomor_sip ?? '-' }}</span><br>
                                        <span class="text-muted">Jatuh Tempo: {{ $sipOver && $sipOver->tanggal_kadaluarsa ? $sipOver->tanggal_kadaluarsa->format('d/m/Y') : '-' }} ({{ abs($sisaOver) }} hari lalu)</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger text-white shadow-2xs"><i class="fa-solid fa-lock me-1"></i> Overdue (Terkunci)</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger border border-danger"><i class="fa-solid fa-ban me-1"></i> DITANGGUHKAN</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger text-white"><i class="fa-solid fa-shield-xmark me-1"></i> Terblokir Administratif</span><br>
                                        <span class="text-muted fs-2xs">Bebas Pelayanan Tindakan Mandiri</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark border"><i class="fa-solid fa-scale-balanced me-1"></i> Terlindungi dr Risiko Malpraktik</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-success py-1 px-2 mb-0 fs-3xs d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-check text-success"></i>
                    <span>Nihil: Seluruh staf medis yang aktif berpraktek memiliki SIP absah atau masa tenggang legal (Kepatuhan 100% Audit KPS).</span>
                </div>
            @endif
        </div>
    </div>
@endif

<!-- Tabel Utama Laporan -->
<div class="card shadow-xs">
    <div class="table-responsive">
        @if($jenis == '1')
            {{-- LAPORAN 1: DATA STAF MEDIS & STATUS SIP --}}
            <table class="table table-hover align-middle mb-0 fs-sm">
                <thead class="table-light">
                    <tr>
                        <th width="40">No</th>
                        <th>NIP / NRP</th>
                        <th>Nama Dokter</th>
                        <th>KSM</th>
                        <th>Spesialisasi</th>
                        <th>Nomor SIP</th>
                        <th>Tempat Praktik</th>
                        <th>Tanggal Kadaluarsa</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $idx => $d)
                        @php $sip = $d->sipTerbaru; @endphp
                        <tr>
                            <td>{{ method_exists($data, 'firstItem') && $data->firstItem() ? ($data->firstItem() + $idx) : ($idx + 1) }}</td>
                            <td><span class="font-monospace fs-xs">{{ $d->nip_nrp }}</span></td>
                            <td class="fw-semibold text-dark">{{ $d->user->nama ?? '-' }}</td>
                            <td>{{ $d->ksm->nama_ksm ?? '-' }}</td>
                            <td>{{ $d->spesialisasi }}</td>
                            <td><span class="font-monospace fs-xs">{{ $sip->nomor_sip ?? '-' }}</span></td>
                            <td>{{ $sip ? 'SIP Ke-' . $sip->tempat_praktik_ke : '-' }}</td>
                            <td>{{ $sip && $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($sip)
                                    <span class="status-pill status-{{ $sip->status }} fs-xs">{{ $sip->status_label }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Belum ada SIP</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($jenis == '2')
            {{-- LAPORAN 2: MONITORING MASA BERLAKU SIP (AUDIT POKJA KPS) --}}
            <table class="table table-hover align-middle mb-0 fs-sm">
                <thead class="table-light">
                    <tr>
                        <th width="40">No</th>
                        <th>Nama Dokter & KSM</th>
                        <th>Nomor SIP</th>
                        <th>Tanggal Berakhir</th>
                        <th>Selisih Hari</th>
                        <th>Status SIP</th>
                        <th>Status SPK & RKK</th>
                        <th>Wewenang Pelayanan Klinis</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $idx => $s)
                        @php
                            $selisih = $s->hitungSisaHari();
                            $dokterObj = $s->dokter;
                            $spk = $dokterObj?->spkTerbaru ?: $dokterObj?->kredensialTerbaru;
                            $isSuspended = $dokterObj?->isSuspended();
                        @endphp
                        <tr class="{{ $isSuspended ? 'table-danger-subtle' : '' }}">
                            <td>{{ method_exists($data, 'firstItem') && $data->firstItem() ? ($data->firstItem() + $idx) : ($idx + 1) }}</td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $dokterObj->user->nama ?? '-' }}</span><br>
                                <span class="text-muted fs-xs">{{ $dokterObj->ksm->nama_ksm ?? '-' }}</span>
                            </td>
                            <td><span class="font-monospace fs-xs">{{ $s->nomor_sip }}</span></td>
                            <td>{{ $s->tanggal_kadaluarsa ? $s->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($s->isGracePeriodAktif())
                                    <span class="badge bg-teal text-white fs-xs"><i class="fa-solid fa-shield-check me-1"></i> Grace Period ({{ $s->hitungSisaHariDispensasi() }} hr)</span>
                                @else
                                    <span class="countdown-badge {{ $selisih <= 0 ? 'bg-danger text-white' : ($selisih <= 30 ? 'bg-warning text-dark' : 'bg-info text-white') }}">
                                        {{ $selisih <= 0 ? 'Kedaluwarsa ' . abs($selisih) . ' hr' : $selisih . ' Hari' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($s->status === 'overdue' || $selisih <= 0)
                                    <span class="badge bg-danger text-white shadow-2xs"><i class="fa-solid fa-lock me-1"></i> Overdue (Terkunci)</span>
                                @elseif($s->isGracePeriodAktif())
                                    <span class="badge bg-teal text-white shadow-2xs"><i class="fa-solid fa-clock-rotate-left me-1"></i> Dalam Proses</span>
                                @else
                                    <span class="status-pill status-{{ $s->status }} fs-xs">{{ $s->status_label }}</span>
                                @endif
                            </td>
                            <td>
                                @if($isSuspended || $spk?->isSuspended())
                                    <span class="badge bg-danger text-white"><i class="fa-solid fa-ban me-1"></i> DITANGGUHKAN</span>
                                @elseif($spk)
                                    <span class="badge bg-success-subtle text-success border border-success fs-xs"><i class="fa-solid fa-circle-check me-1"></i> SPK Aktif</span><br>
                                    <span class="text-muted fs-2xs">{{ $spk->nomor_spk }}</span>
                                @else
                                    <span class="text-muted fs-xs">Belum ada SPK</span>
                                @endif
                            </td>
                            <td>
                                @if($isSuspended)
                                    <span class="badge bg-danger-subtle text-danger border border-danger fs-xs"><i class="fa-solid fa-ban me-1"></i> Terblokir Mandiri</span>
                                @elseif($dokterObj?->hasActiveDispensasi())
                                    <span class="badge bg-teal-subtle text-teal border border-teal fs-xs"><i class="fa-solid fa-user-shield me-1"></i> Terproteksi Grace Period</span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success fs-xs"><i class="fa-solid fa-shield-check me-1"></i> Hak Praktik Penuh</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($jenis == '3')
            {{-- LAPORAN 3: VERIFIKASI DOKUMEN SIP KOMITE MEDIK --}}
            <table class="table table-hover align-middle mb-0 fs-sm">
                <thead class="table-light">
                    <tr>
                        <th width="40">No</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Nama Dokter</th>
                        <th>KSM</th>
                        <th>Nomor SIP</th>
                        <th>Verifikator</th>
                        <th>Tanggal Verifikasi</th>
                        <th>Status</th>
                        <th>Catatan Telaah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $idx => $v)
                        <tr>
                            <td>{{ method_exists($data, 'firstItem') && $data->firstItem() ? ($data->firstItem() + $idx) : ($idx + 1) }}</td>
                            <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
                            <td class="fw-semibold text-dark">{{ $v->dokter->user->nama ?? '-' }}</td>
                            <td>{{ $v->dokter->ksm->nama_ksm ?? '-' }}</td>
                            <td><span class="font-monospace fs-xs">{{ $v->nomor_sip }}</span></td>
                            <td>{{ $v->verifikator->nama ?? '-' }}</td>
                            <td>{{ $v->verified_at ? $v->verified_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                <span class="badge {{ $v->status_verifikasi === 'disetujui' ? 'bg-success' : ($v->status_verifikasi === 'ditolak' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                    {{ ucfirst($v->status_verifikasi) }}
                                </span>
                            </td>
                            <td class="fs-xs text-secondary">{{ $v->catatan_verifikasi ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($jenis == '4')
            {{-- LAPORAN 4: MASA BERLAKU SPK & RKK --}}
            <table class="table table-hover align-middle mb-0 fs-sm">
                <thead class="table-light">
                    <tr>
                        <th width="40">No</th>
                        <th>Nama Dokter</th>
                        <th>KSM</th>
                        <th>Nomor SPK Direktur</th>
                        <th>Tanggal Terbit SPK</th>
                        <th>Tanggal Berakhir SPK</th>
                        <th>Status Kredensial</th>
                        <th>Rekomendasi Rekredensialing</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $idx => $k)
                        @php $sisa = $k->hitungSisaHari(); @endphp
                        <tr>
                            <td>{{ method_exists($data, 'firstItem') && $data->firstItem() ? ($data->firstItem() + $idx) : ($idx + 1) }}</td>
                            <td class="fw-semibold text-dark">{{ $k->dokter->user->nama ?? '-' }}</td>
                            <td>{{ $k->dokter->ksm->nama_ksm ?? '-' }}</td>
                            <td><span class="font-monospace fs-xs">{{ $k->nomor_spk }}</span></td>
                            <td>{{ $k->tanggal_terbit_spk ? $k->tanggal_terbit_spk->format('d/m/Y') : '-' }}</td>
                            <td>{{ $k->tanggal_berakhir_spk ? $k->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="badge {{ $k->status_kredensial === 'aktif' ? 'bg-success' : ($k->status_kredensial === 'evaluasi' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ $k->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($sisa < 0)
                                    <span class="text-danger fw-semibold fs-xs">Wajib Pengajuan Rekredensial Segera</span>
                                @elseif($sisa <= 90)
                                    <span class="text-warning fw-semibold fs-xs">Memasuki Periode Evaluasi Subkomite</span>
                                @else
                                    <span class="text-success fs-xs">Kewenangan Klinis Absah Berlaku</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($jenis == '5')
            {{-- LAPORAN 5: RIWAYAT NOTIFIKASI PENGINGAT --}}
            <table class="table table-hover align-middle mb-0 fs-sm">
                <thead class="table-light">
                    <tr>
                        <th width="40">No</th>
                        <th>Waktu Pengiriman</th>
                        <th>Nama Dokter</th>
                        <th>Nomor WhatsApp</th>
                        <th>Isi Pesan Pengingat</th>
                        <th>Kanal</th>
                        <th>Status Kirim</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $idx => $n)
                        <tr>
                            <td>{{ method_exists($data, 'firstItem') && $data->firstItem() ? ($data->firstItem() + $idx) : ($idx + 1) }}</td>
                            <td>{{ $n->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="fw-semibold text-dark">{{ $n->user->nama ?? '-' }}</td>
                            <td><span class="font-monospace fs-xs">{{ $n->user->no_wa ?? '-' }}</span></td>
                            <td class="fs-xs text-secondary" style="max-width: 320px; white-space: pre-line;">{{ Str::limit($n->pesan, 120) }}</td>
                            <td><span class="badge bg-light text-dark border">{{ strtoupper($n->kanal) }}</span></td>
                            <td>
                                <span class="badge {{ $n->status_terkirim ? 'bg-success' : 'bg-danger' }}">
                                    {{ $n->status_terkirim ? 'Sukses' : 'Gagal' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>

    <!-- Paginasi Baku 10 Data per Halaman -->
    @if(method_exists($data, 'hasPages') && $data->hasPages())
        <div class="card-footer bg-white py-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <span class="fs-xs text-muted">Menampilkan {{ $data->firstItem() }} – {{ $data->lastItem() }} dari {{ $data->total() }} data (Baku: 10 per halaman)</span>
            <div>{{ $data->links() }}</div>
        </div>
    @endif
</div>
@endsection
