@extends('admin.layouts.app')
@section('page_title', 'Data Kelas')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <span>Kelas</span>
    </div>
    <h1>Data Kelas</h1>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Tambah Kelas
    </a>
  </div>
</div>

<div class="card">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Tingkat</th>
          <th>Nama Kelas</th>
          <th>Nama Tampilan</th>
          <th>Urutan</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($classes as $c)
        <tr>
          <td><span class="badge badge-info">{{ $c->level }}</span></td>
          <td style="font-weight:600;">{{ $c->name }}</td>
          <td>{{ $c->display_name }}</td>
          <td>{{ $c->sort_order }}</td>
          <td>
            @if($c->is_active) <span class="badge badge-success">Aktif</span>
            @else <span class="badge badge-secondary">Nonaktif</span> @endif
          </td>
          <td>
            <div style="display:flex;gap:4px;">
              <a href="{{ route('admin.classes.edit', $c) }}" class="btn btn-sm btn-secondary" title="Edit">✏️</a>
              <form method="POST" action="{{ route('admin.classes.destroy', $c) }}" onsubmit="return confirm('Hapus kelas {{ $c->display_name }}? Pastikan tidak ada siswa yang terdaftar di kelas ini.')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger" title="Hapus">🗑</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6">
          <div class="empty-state">
            <div class="empty-icon">🏫</div>
            <p>Belum ada data kelas. <a href="{{ route('admin.classes.create') }}" style="color:var(--accent);">Tambah sekarang →</a></p>
          </div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
