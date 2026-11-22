@extends('admin.layouts.app')
@section('page_title', 'Manajemen Admin')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> <span>Admin Users</span></div>
    <h1>Manajemen Admin</h1>
    <p>Kelola akun admin untuk akses sistem SPP (Maksimal 4 admin).</p>
  </div>
  @if($admins->count() < 4)
  <a href="{{ route('admin.admin-users.create') }}" class="btn btn-primary">
    + Tambah Admin
  </a>
  @else
  <button class="btn btn-secondary" disabled title="Maksimal 4 admin tercapai">
    + Tambah Admin (Maks)
  </button>
  @endif
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Nama Lengkap</th>
          <th>Email / Username</th>
          <th>Role</th>
          <th>WhatsApp</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($admins as $adm)
        <tr>
          <td style="font-weight:600;">{{ $adm->name }}</td>
          <td>{{ $adm->email }}</td>
          <td>
            @if($adm->role === 'super_admin')
              <span class="badge" style="background:var(--purple);color:white;">Super Admin</span>
            @else
              <span class="badge" style="background:var(--accent);color:white;">Admin</span>
            @endif
          </td>
          <td>{{ $adm->whatsapp ?? '-' }}</td>
          <td>
            <div class="d-flex align-center gap-2">
              <a href="{{ route('admin.admin-users.edit', $adm->id) }}" class="btn-icon btn-secondary" title="Edit">✏️</a>
              @if($admins->count() > 1 && $adm->id !== auth()->id())
                <form action="{{ route('admin.admin-users.destroy', $adm->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus admin ini?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-icon btn-secondary" style="color:var(--red-light);" title="Hapus">🗑</button>
                </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" style="text-align:center;padding:20px;color:var(--text-muted);">Belum ada data admin.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
