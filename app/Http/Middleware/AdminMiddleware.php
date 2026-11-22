<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /** Durasi timeout sesi admin dalam menit */
    protected int $timeoutMinutes = 120; // 2 jam

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Silakan login sebagai admin terlebih dahulu.');
        }

        // Cek session timeout
        $lastActivity = $request->session()->get('admin_last_activity');
        if ($lastActivity && (now()->timestamp - $lastActivity) > ($this->timeoutMinutes * 60)) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')
                ->with('error', 'Sesi Anda telah berakhir karena tidak aktif selama 2 jam. Silakan login kembali.');
        }

        // Perbarui waktu aktivitas terakhir di setiap request
        $request->session()->put('admin_last_activity', now()->timestamp);

        return $next($request);
    }
}
