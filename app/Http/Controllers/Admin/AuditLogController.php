<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AuditLogExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->latest();

        // Filter by event
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filter by admin
        if ($request->filled('admin')) {
            $query->where('admin_name', 'like', '%' . $request->admin . '%');
        }

        // Filter by tanggal
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(50)->withQueryString();

        $eventOptions = [
            'created'  => 'Dibuat',
            'updated'  => 'Diubah',
            'deleted'  => 'Dihapus',
            'login'    => 'Login',
            'logout'   => 'Logout',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];

        return view('admin.audit-logs.index', compact('logs', 'eventOptions'));
    }

    public function export(Request $request)
    {
        $filename = 'audit-log-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(
            new AuditLogExport(
                $request->event,
                $request->admin,
                $request->date,
            ),
            $filename
        );
    }
}
