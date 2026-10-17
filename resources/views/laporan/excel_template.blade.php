<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        table { border-collapse: collapse; width: 100%; }
        th { background-color: #0f766e; color: #ffffff; font-weight: bold; border: 1px solid #000; padding: 6px; }
        td { border: 1px solid #000; padding: 5px; }
        .title { font-size: 14pt; font-weight: bold; text-align: center; }
        .subtitle { font-size: 10pt; text-align: center; color: #555; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="8" class="title">{{ strtoupper(str_replace('_', ' ', $judulLaporan)) }}</td>
        </tr>
        <tr>
            <td colspan="8" class="subtitle">RSUD dr. H. Moch. Ansari Saleh Banjarmasin - Diekspor pada: {{ date('d-m-Y H:i') }} WITA</td>
        </tr>
        <tr><td colspan="8"></td></tr>
    </table>

    @if($jenis == '1')
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIP/NRP</th>
                    <th>Nama Dokter</th>
                    <th>KSM</th>
                    <th>Spesialisasi</th>
                    <th>Nomor SIP</th>
                    <th>Tempat Ke</th>
                    <th>Tanggal Kadaluarsa</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $idx => $d)
                    @php $sip = $d->sipTerbaru; @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td style="mso-number-format:'\@';">{{ $d->nip_nrp }}</td>
                        <td>{{ $d->user->nama ?? '-' }}</td>
                        <td>{{ $d->ksm->nama_ksm ?? '-' }}</td>
                        <td>{{ $d->spesialisasi }}</td>
                        <td style="mso-number-format:'\@';">{{ $sip->nomor_sip ?? '-' }}</td>
                        <td>{{ $sip ? $sip->tempat_praktik_ke : '-' }}</td>
                        <td>{{ $sip && $sip->tanggal_kadaluarsa ? $sip->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</td>
                        <td>{{ $sip ? $sip->status_label : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @elseif($jenis == '2')
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Dokter</th>
                    <th>KSM</th>
                    <th>Nomor SIP</th>
                    <th>Tanggal Kedaluwarsa</th>
                    <th>Selisih Hari</th>
                    <th>Status Legalitas</th>
                    <th>SPK Terakhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $idx => $s)
                    @php $selisih = $s->hitungSisaHari(); @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $s->dokter->user->nama ?? '-' }}</td>
                        <td>{{ $s->dokter->ksm->nama_ksm ?? '-' }}</td>
                        <td style="mso-number-format:'\@';">{{ $s->nomor_sip }}</td>
                        <td>{{ $s->tanggal_kadaluarsa ? $s->tanggal_kadaluarsa->format('d/m/Y') : '-' }}</td>
                        <td>{{ $selisih }}</td>
                        <td>{{ $s->status_label }}</td>
                        <td>{{ $s->dokter->spkTerbaru ? $s->dokter->spkTerbaru->nomor_spk : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @elseif($jenis == '3')
        <table>
            <thead>
                <tr>
                    <th>No</th>
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
                @foreach($data as $idx => $v)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $v->created_at->format('d/m/Y') }}</td>
                        <td>{{ $v->dokter->user->nama ?? '-' }}</td>
                        <td>{{ $v->dokter->ksm->nama_ksm ?? '-' }}</td>
                        <td style="mso-number-format:'\@';">{{ $v->nomor_sip }}</td>
                        <td>{{ $v->verifikator->nama ?? '-' }}</td>
                        <td>{{ $v->verified_at ? $v->verified_at->format('d/m/Y') : '-' }}</td>
                        <td>{{ strtoupper($v->status_verifikasi) }}</td>
                        <td>{{ $v->catatan_verifikasi ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @elseif($jenis == '4')
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Dokter</th>
                    <th>KSM</th>
                    <th>Nomor SPK Direktur</th>
                    <th>Tanggal Terbit SPK</th>
                    <th>Tanggal Berakhir SPK</th>
                    <th>Status Kredensial</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $idx => $k)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $k->dokter->user->nama ?? '-' }}</td>
                        <td>{{ $k->dokter->ksm->nama_ksm ?? '-' }}</td>
                        <td style="mso-number-format:'\@';">{{ $k->nomor_spk }}</td>
                        <td>{{ $k->tanggal_terbit_spk ? $k->tanggal_terbit_spk->format('d/m/Y') : '-' }}</td>
                        <td>{{ $k->tanggal_berakhir_spk ? $k->tanggal_berakhir_spk->format('d/m/Y') : '-' }}</td>
                        <td>{{ $k->status_label }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @elseif($jenis == '5')
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Waktu Kirim</th>
                    <th>Nama Dokter</th>
                    <th>Nomor WhatsApp</th>
                    <th>Isi Pesan</th>
                    <th>Kanal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $idx => $n)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $n->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $n->user->nama ?? '-' }}</td>
                        <td style="mso-number-format:'\@';">{{ $n->user->no_wa ?? '-' }}</td>
                        <td>{{ $n->pesan }}</td>
                        <td>{{ strtoupper($n->kanal) }}</td>
                        <td>{{ $n->status_terkirim ? 'SUKSES' : 'GAGAL' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
