<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('modul')) {
            $query->where('modul', $request->modul);
        }

        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('deskripsi', 'like', "%{$cari}%")
                    ->orWhere('ip_address', 'like', "%{$cari}%")
                    ->orWhereHas('user', function ($qu) use ($cari) {
                        $qu->where('nama', 'like', "%{$cari}%");
                    });
            });
        }

        $auditLogs = $query->latest()->paginate(25)->withQueryString();
        $modulList = AuditLog::select('modul')->distinct()->pluck('modul');

        return view('admin.audit.index', compact('auditLogs', 'modulList'));
    }
}
