<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = Admin::orderBy('name')->get();
        return view('admin.admin-users.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.admin-users.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:super_admin,admin',
            'whatsapp' => 'nullable|string|max:20',
        ]);

        Admin::create([
            ...$request->only('name', 'email', 'role', 'whatsapp'),
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin berhasil ditambahkan.');
    }

    public function edit(Admin $adminUser)
    {
        return view('admin.admin-users.edit', compact('adminUser'));
    }

    public function update(Request $request, Admin $adminUser)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => "required|email|unique:admins,email,{$adminUser->id}",
            'role' => 'required|in:super_admin,admin',
            'whatsapp' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->only('name', 'email', 'role', 'whatsapp');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $adminUser->update($data);
        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin berhasil diperbarui.');
    }

    public function destroy(Admin $adminUser)
    {
        if (Admin::count() <= 1) {
            return back()->with('error', 'Minimal harus ada 1 akun admin.');
        }
        $adminUser->delete();
        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin berhasil dihapus.');
    }

    /** Tampilkan form ganti password milik sendiri */
    public function showChangePassword()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.admin-users.change-password', compact('admin'));
    }

    /** Proses ganti password milik sendiri */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.min'              => 'Password baru minimal 8 karakter.',
            'password.confirmed'        => 'Konfirmasi password tidak cocok.',
        ]);

        $admin = Auth::guard('admin')->user();

        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $admin->update(['password' => Hash::make($request->password)]);

        AuditLog::record('updated', $admin, 'Admin mengganti password sendiri');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Password berhasil diubah. Silakan login kembali jika diperlukan.');
    }
}
