@extends('admin.layouts.app')
@section('page_title', 'Tahun Ajaran')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <span>Tahun Ajaran</span>
    </div>
    <h1>Data Tahun Ajaran</h1>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('admin.academic-years.create') }}" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Tambah Tahun Ajaran
    </a>
  </div>
</div>

<div class="card">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Nama / Label</th>
          <th>Tahun Mulai</th>
          <th>Tahun Selesai</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($years as $year)
        <tr>
          <td style="font-weight:600;">{{ $year->name }}</td>
          <td>{{ $year->start_year }}</td>
          <td>{{ $year->end_year }}</td>
          <td>
            @if($year->is_active)
              <span class="badge badge-success">Aktif</span>
            @else
              <form method="POST" action="{{ route('admin.academic-years.set-active', $year->id) }}" style="display:inline;" onsubmit="return confirm('Aktifkan tahun ajaran ini? Hal ini akan memengaruhi perhitungan denda/tagihan ke depannya.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-secondary" style="font-size:11px;padding:4px 8px;">Jadikan Aktif</button>
              </form>
            @endif
          </td>
          <td>
            <div style="display:flex;gap:4px;">
              <a href="{{ route('admin.academic-years.edit', $year) }}" class="btn btn-sm btn-secondary" title="Edit">✏️</a>
              @if(!$year->is_active)
              <form method="POST" action="{{ route('admin.academic-years.destroy', $year) }}" onsubmit="return confirm('Hapus tahun ajaran {{ $year->name }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger" title="Hapus">🗑</button>
              </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="5">
          <div class="empty-state">
            <div class="empty-icon">📅</div>
            <p>Belum ada data tahun ajaran. <a href="{{ route('admin.academic-years.create') }}" style="color:var(--accent);">Tambah sekarang →</a></p>
          </div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
