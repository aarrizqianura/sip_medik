<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judulLaporan }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        .header-kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }

        .kop-provinsi {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .kop-instansi {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #044e47;
            margin-bottom: 3px;
        }

        .kop-alamat {
            font-size: 8.5pt;
            color: #475569;
            line-height: 1.3;
        }

        .judul-dokumen {
            text-align: center;
            margin-bottom: 16px;
        }

        .judul-dokumen h2 {
            font-size: 12pt;
            text-transform: uppercase;
            margin: 0 0 4px 0;
            font-weight: bold;
            text-decoration: underline;
        }

        .judul-dokumen p {
            font-size: 8.5pt;
            color: #64748b;
            margin: 0;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 20px;
        }

        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            vertical-align: middle;
        }

        table.data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            text-align: center;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .badge-status {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: bold;
            display: inline-block;
        }

        .status-aktif { background-color: #dcfce7; color: #166534; }
        .status-due_soon { background-color: #fef9c3; color: #854d0e; }
        .status-due { background-color: #ffedd5; color: #9a3412; }
        .status-overdue { background-color: #ffe4e6; color: #9f1239; }

        .signature-section {
            width: 100%;
            margin-top: 30px;
        }

        .signature-box {
            float: right;
            width: 250px;
            text-align: center;
            font-size: 9pt;
        }

        .signature-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI RSUD -->
    <div class="header-kop">
        <div class="kop-provinsi">PEMERINTAH PROVINSI KALIMANTAN SELATAN</div>
        <div class="kop-provinsi">DINAS KESEHATAN</div>
        <div class="kop-instansi">{{ $rsNama }}</div>
        <div class="kop-alamat">
            {{ $rsAlamat }}<br>
            Telepon: {{ $rsTelepon }} | Website: https://rsudansarisaleh.kalselprov.go.id
        </div>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="judul-dokumen">
        <h2>{{ $judulLaporan }}</h2>
        <p>Dicetak otomatis melalui Portal SIM-SIP Komite Medik pada tanggal: {{ date('d F Y H:i:s') }} WITA</p>
    </div>

    <!-- TABEL DATA LAPORAN BERDASARKAN JENIS -->
    @if($jenis == '1')
        <table class="data-table">
            <thead>
                <tr>
                    <th width="25">No</th>
                    <th>NIP / NRP</th>
                    <th>Nama Dokter</th>
                    <th>KSM</th>
                    <th>Spesialisasi</th>
                    <th>Nomor SIP</th>
                    <th>Tempat Ke</th>
                    <th>Jatuh Tempo</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $idx => $d)
                    @php $sip = $d->sipTerbaru; @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $d->nip_nrp }}</td>
                        <td class="font-bold">{{ $d->user->nama ?? '-' }}</td>
                        <td>{{ $d->ksm->nama_ksm ?? '-' }}</td>
                        <td>{{ $d->spesialisasi }}</td>
                        <td>{{ $sip->nomor_sip ?? '-' }}</td>
                        <td class="text-center">{{ $sip ? $sip->tempat_praktik_ke : '-' }}</td>
                        <td class="text-center">{{ $sip && $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">
                            @if($sip)
                                <span class="badge-status status-{{ $sip->status }}">{{ strtoupper($sip->status) }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($jenis == '2')
        <table class="data-table">
            <thead>
                <tr>
                    <th width="25">No</th>
                    <th>Nama Dokter</th>
                    <th>KSM</th>
                    <th>Nomor SIP</th>
                    <th>Tgl Kedaluwarsa</th>
                    <th>Sisa Hari</th>
                    <th>Status Legalitas</th>
                    <th>Histori SPK Direktur</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $idx => $s)
                    @php $selisih = $s->hitungSisaHari(); @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-bold">{{ $s->dokter->user->nama ?? '-' }}</td>
                        <td>{{ $s->dokter->ksm->nama_ksm ?? '-' }}</td>
                        <td>{{ $s->nomor_sip }}</td>
                        <td class="text-center">{{ $s->tanggal_kadaluarsa ? $s->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $selisih < 0 ? 'Kadaluarsa (' . abs($selisih) . ' hr)' : $selisih . ' hari' }}</td>
                        <td class="text-center">
                            <span class="badge-status status-{{ $s->status }}">{{ strtoupper($s->status) }}</span>
                        </td>
                        <td>{{ $s->dokter->spkTerbaru ? $s->dokter->spkTerbaru->nomor_spk : 'Belum Terbit' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($jenis == '3')
        <table class="data-table">
            <thead>
                <tr>
                    <th width="25">No</th>
                    <th>Tgl Diajukan</th>
                    <th>Nama Dokter</th>
                    <th>KSM</th>
                    <th>Nomor SIP</th>
                    <th>Verifikator</th>
                    <th>Tgl Pengesahan</th>
                    <th>Status</th>
                    <th>Catatan Telaah Klinis</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $idx => $v)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center">{{ $v->created_at->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ $v->dokter->user->nama ?? '-' }}</td>
                        <td>{{ $v->dokter->ksm->nama_ksm ?? '-' }}</td>
                        <td>{{ $v->nomor_sip }}</td>
                        <td>{{ $v->verifikator->nama ?? '-' }}</td>
                        <td class="text-center">{{ $v->verified_at ? $v->verified_at->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ strtoupper($v->status_verifikasi) }}</td>
                        <td>{{ $v->catatan_verifikasi ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($jenis == '4')
        <table class="data-table">
            <thead>
                <tr>
                    <th width="25">No</th>
                    <th>Nama Dokter</th>
                    <th>KSM</th>
                    <th>Nomor SPK Direktur</th>
                    <th>Tgl Terbit SPK</th>
                    <th>Tgl Berakhir SPK</th>
                    <th>Status Kredensial</th>
                    <th>Rekomendasi Rekredensialing</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $idx => $k)
                    @php $sisa = $k->hitungSisaHari(); @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-bold">{{ $k->dokter->user->nama ?? '-' }}</td>
                        <td>{{ $k->dokter->ksm->nama_ksm ?? '-' }}</td>
                        <td>{{ $k->nomor_spk }}</td>
                        <td class="text-center">{{ $k->tanggal_terbit_spk ? $k->tanggal_terbit_spk->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $k->tanggal_berakhir_spk ? $k->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ strtoupper($k->status_kredensial) }}</td>
                        <td>{{ $sisa < 0 ? 'Wajib Rekredensialing' : ($sisa <= 90 ? 'Evaluasi 3 Tahunan' : 'Berlaku Absah') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($jenis == '5')
        <table class="data-table">
            <thead>
                <tr>
                    <th width="25">No</th>
                    <th>Waktu Kirim</th>
                    <th>Nama Dokter</th>
                    <th>Nomor WhatsApp</th>
                    <th>Isi Pesan Peringatan</th>
                    <th>Kanal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $idx => $n)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center">{{ $n->created_at->format('d/m/Y H:i') }}</td>
                        <td class="font-bold">{{ $n->user->nama ?? '-' }}</td>
                        <td>{{ $n->user->no_wa ?? '-' }}</td>
                        <td>{{ Str::limit($n->pesan, 100) }}</td>
                        <td class="text-center">{{ strtoupper($n->kanal) }}</td>
                        <td class="text-center">{{ $n->status_terkirim ? 'SUKSES' : 'GAGAL' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <!-- TANDA TANGAN PEJABAT BERWENANG -->
    <div class="signature-section">
        <div class="signature-box">
            <div>Banjarmasin, {{ date('d F Y') }}</div>
            <div class="font-bold">Ketua Subkomite Kredensial / SDM</div>
            <div class="signature-space"></div>
            <div class="font-bold" style="text-decoration: underline;">dr. H. Verifikator Medis, Sp.B</div>
            <div>NIP. 19780101 200501 1 002</div>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
