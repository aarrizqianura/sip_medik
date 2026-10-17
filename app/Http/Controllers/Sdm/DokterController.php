<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Http\Requests\DokterRequest;
use App\Models\Dokter;
use App\Models\User;
use App\Models\Ksm;
use App\Models\DokumenSip;
use App\Models\AuditLog;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DokterController extends Controller
{
    protected WhatsAppGatewayService $waService;

    public function __construct(WhatsAppGatewayService $waService)
    {
        $this->waService = $waService;
    }

    public function index(Request $request)
    {
        $query = Dokter::with(['user', 'ksm', 'sipTerbaru']);

        if ($request->filled('id_ksm')) {
            $query->where('id_ksm', $request->id_ksm);
        }

        if ($request->filled('status_kepegawaian')) {
            $query->where('status_kepegawaian', $request->status_kepegawaian);
        }

        if ($request->filled('status_str')) {
            $query->where('status_str', $request->status_str);
        }

        if ($request->filled('status_sip')) {
            $statusSip = $request->status_sip;
            $query->whereHas('dokumenSip', function ($q) use ($statusSip) {
                $q->where('status', $statusSip);
            });
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('nip_nrp', 'like', "%{$cari}%")
                  ->orWhere('spesialisasi', 'like', "%{$cari}%")
                  ->orWhereHas('user', function ($qu) use ($cari) {
                      $qu->where('nama', 'like', "%{$cari}%")
                        ->orWhere('email', 'like', "%{$cari}%")
                        ->orWhere('no_wa', 'like', "%{$cari}%");
                  });
            });
        }

        $dokters = $query->paginate(15)->withQueryString();
        $ksmList = Ksm::orderBy('nama_ksm')->get();

        return view('sdm.dokter.index', compact('dokters', 'ksmList'));
    }

    public function create()
    {
        $ksmList = Ksm::orderBy('nama_ksm')->get();
        return view('sdm.dokter.create', compact('ksmList'));
    }

    public function store(DokterRequest $request)
    {
        DB::beginTransaction();
        try {
            // 1. Buat user dokter
            $user = User::create([
                'nama' => $request->nama,
                'username' => $request->username,
                'email' => $request->email,
                'no_wa' => $request->no_wa,
                'password' => Hash::make($request->password),
                'role' => 'dokter',
                'aktif' => true,
            ]);

            // 2. Buat profil dokter
            $dokter = Dokter::create([
                'id_user' => $user->id,
                'nip_nrp' => $request->nip_nrp,
                'id_ksm' => $request->id_ksm,
                'spesialisasi' => $request->spesialisasi,
                'status_kepegawaian' => $request->status_kepegawaian,
                'status_str' => $request->status_str,
            ]);

            AuditLog::record('CREATE_DOKTER', 'Data Dokter SDM', "Mendaftarkan dokter baru: {$user->nama} (NIP: {$dokter->nip_nrp})");

            DB::commit();

            return redirect()->route('sdm.dokter.index')
                ->with('sukses', "Data dokter {$user->nama} berhasil didaftarkan ke sistem.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan data dokter: ' . $e->getMessage());
        }
    }

    public function show(Dokter $dokter)
    {
        $dokter->load(['user', 'ksm', 'dokumenSip.verifikator', 'kredensialKlinis']);
        $notifikasiList = $dokter->user->notifikasi()->latest()->take(10)->get();

        return view('sdm.dokter.show', compact('dokter', 'notifikasiList'));
    }

    public function edit(Dokter $dokter)
    {
        $dokter->load(['user', 'ksm']);
        $ksmList = Ksm::orderBy('nama_ksm')->get();

        return view('sdm.dokter.edit', compact('dokter', 'ksmList'));
    }

    public function update(DokterRequest $request, Dokter $dokter)
    {
        DB::beginTransaction();
        try {
            $user = $dokter->user;

            $userData = [
                'nama' => $request->nama,
                'username' => $request->username,
                'email' => $request->email,
                'no_wa' => $request->no_wa,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            $dokter->update([
                'nip_nrp' => $request->nip_nrp,
                'id_ksm' => $request->id_ksm,
                'spesialisasi' => $request->spesialisasi,
                'status_kepegawaian' => $request->status_kepegawaian,
                'status_str' => $request->status_str,
            ]);

            AuditLog::record('UPDATE_DOKTER', 'Data Dokter SDM', "Memperbarui data profil dokter: {$user->nama}");

            DB::commit();

            return redirect()->route('sdm.dokter.index')
                ->with('sukses', "Perubahan data dokter {$user->nama} berhasil disimpan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui data dokter: ' . $e->getMessage());
        }
    }

    public function destroy(Dokter $dokter)
    {
        $nama = $dokter->user->nama;
        $user = $dokter->user;

        $user->delete(); // CASCADE on delete dokter & dokumen

        AuditLog::record('DELETE_DOKTER', 'Data Dokter SDM', "Menghapus profil dokter: {$nama}");

        return redirect()->route('sdm.dokter.index')
            ->with('sukses', "Dokter {$nama} telah dihapus dari sistem.");
    }

    public function kirimManual(Request $request, $id)
    {
        $sip = DokumenSip::with('dokter.user')->findOrFail($id);

        $customPesan = $request->input('pesan_khusus');
        $hasil = $this->waService->kirimPeringatan($sip, $customPesan);

        AuditLog::record('REMINDER_MANUAL_WA', 'Notifikasi Pengingat', "Mengirimkan pesan reminder WhatsApp manual ke {$sip->dokter->user->nama} untuk SIP No. {$sip->nomor_sip}");

        if ($hasil['status']) {
            return back()->with('sukses', "Pesan reminder WhatsApp berhasil dikirimkan ke {$sip->dokter->user->nama} ({$sip->dokter->user->no_wa})!");
        } else {
            return back()->with('error', "Gagal mengirim pesan WhatsApp: " . ($hasil['info'] ?? 'Periksa koneksi gateway'));
        }
    }
}
