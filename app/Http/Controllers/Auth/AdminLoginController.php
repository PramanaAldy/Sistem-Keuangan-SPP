<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminLoginController extends Controller
{
    protected int $maxAttempts  = 5;
    protected int $decayMinutes = 1;

    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $this->ensureIsNotRateLimited($request);

        if (Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::clear($this->throttleKey($request));
            $request->session()->regenerate();
            $request->session()->put('admin_last_activity', now()->timestamp);

            AuditLog::record('login', null, 'Admin login dari IP ' . $request->ip());

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($this->throttleKey($request), $this->decayMinutes * 60);

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    public function logout(Request $request)
    {
        AuditLog::record('logout', null, 'Admin logout');
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login')->with('success', 'Anda telah berhasil logout.');
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), $this->maxAttempts)) {
            return;
        }
        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());
    }
}
