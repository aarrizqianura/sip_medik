<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifikasi;

class NotifikasiController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Super Admin & SDM bisa melihat seluruh log, user biasa hanya miliknya
        if (in_array($user->role, ['super_admin', 'admin_sdm'])) {
            $notifikasi = Notifikasi::with(['user', 'dokumenSip'])->latest()->paginate(20);
        } else {
            $notifikasi = $user->notifikasi()->with('dokumenSip')->latest()->paginate(20);
        }

        return view('notifikasi.index', compact('notifikasi'));
    }

    public function markAsRead($id)
    {
        $notif = Notifikasi::where('id', $id)->firstOrFail();
        if ($notif->id_user === auth()->id() || auth()->user()->role === 'super_admin') {
            $notif->status_baca = true;
            $notif->save();
        }

        return back()->with('sukses', 'Notifikasi telah ditandai dibaca.');
    }

    public function markAllAsRead()
    {
        auth()->user()->notifikasi()->update(['status_baca' => true]);
        return back()->with('sukses', 'Semua notifikasi telah ditandai dibaca.');
    }
}
