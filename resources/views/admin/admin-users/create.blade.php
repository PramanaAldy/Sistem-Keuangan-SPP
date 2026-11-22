@extends('admin.layouts.app')
@section('page_title', 'Tambah Admin')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> <a href="{{ route('admin.admin-users.index') }}">Admin Users</a> <span>/</span> <span>Tambah Admin</span></div>
    <h1>Tambah Admin</h1>
    <p>Tambahkan akun admin baru (Maksimal 4 admin).</p>
  </div>
</div>

<div style="max-width:500px;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">📝 Form Tambah Admin</div>
    </div>

    <form method="POST" action="{{ route('admin.admin-users.store') }}">
      @csrf

      <div class="form-group">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" name="name" value="{{ old('name') }}" required 
          class="form-control @error('name') border-danger @enderror"
          placeholder="Contoh: Budi Santoso">
        @error('name') <div class="form-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Email / Username</label>
        <input type="email" name="email" value="{{ old('email') }}" required 
          class="form-control @error('email') border-danger @enderror"
          placeholder="Contoh: budi@sekolah.com">
        @error('email') <div class="form-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Nomor WhatsApp</label>
        <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" 
          class="form-control @error('whatsapp') border-danger @enderror"
          placeholder="Contoh: 08123456789">
        @error('whatsapp') <div class="form-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Role Akses</label>
        <select name="role" required class="form-control @error('role') border-danger @enderror">
          <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Standar)</option>
          <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
        </select>
        @error('role') <div class="form-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" required 
          class="form-control @error('password') border-danger @enderror"
          placeholder="Minimal 6 karakter">
        @error('password') <div class="form-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" required 
          class="form-control"
          placeholder="Ulangi password">
      </div>

      <div style="display:flex;gap:8px;margin-top:20px;">
        <button type="submit" class="btn btn-primary">💾 Simpan Admin</button>
        <a href="{{ route('admin.admin-users.index') }}" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection
