@extends('admin.layouts.app')
@section('page_title', 'Edit Admin')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> <a href="{{ route('admin.admin-users.index') }}">Admin Users</a> <span>/</span> <span>Edit Admin</span></div>
    <h1>Edit Admin: {{ $adminUser->name }}</h1>
    <p>Perbarui profil dan akses admin.</p>
  </div>
</div>

<div style="max-width:500px;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">✏️ Form Edit Admin</div>
    </div>

    <form method="POST" action="{{ route('admin.admin-users.update', $adminUser->id) }}">
      @csrf @method('PUT')

      <div class="form-group">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" name="name" value="{{ old('name', $adminUser->name) }}" required 
          class="form-control @error('name') border-danger @enderror">
        @error('name') <div class="form-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Email / Username</label>
        <input type="email" name="email" value="{{ old('email', $adminUser->email) }}" required 
          class="form-control @error('email') border-danger @enderror">
        @error('email') <div class="form-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Nomor WhatsApp</label>
        <input type="text" name="whatsapp" value="{{ old('whatsapp', $adminUser->whatsapp) }}" 
          class="form-control @error('whatsapp') border-danger @enderror">
        @error('whatsapp') <div class="form-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Role Akses</label>
        <select name="role" required class="form-control @error('role') border-danger @enderror">
          <option value="admin" {{ old('role', $adminUser->role) === 'admin' ? 'selected' : '' }}>Admin (Standar)</option>
          <option value="super_admin" {{ old('role', $adminUser->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
        </select>
        @error('role') <div class="form-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Password <span style="font-weight:normal;color:var(--text-muted);font-size:11px;">(kosongkan jika tidak ingin ganti)</span></label>
        <input type="password" name="password" 
          class="form-control @error('password') border-danger @enderror"
          placeholder="Minimal 6 karakter">
        @error('password') <div class="form-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" 
          class="form-control"
          placeholder="Ulangi jika password diubah">
      </div>

      <div style="display:flex;gap:8px;margin-top:20px;">
        <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
        <a href="{{ route('admin.admin-users.index') }}" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection
