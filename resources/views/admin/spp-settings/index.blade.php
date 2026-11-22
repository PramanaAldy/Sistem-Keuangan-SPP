@extends('admin.layouts.app')
@section('page_title', 'Setting SPP')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <span>Setting SPP</span>
    </div>
    <h1>Pengaturan Nominal SPP</h1>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('admin.spp-settings.create') }}" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Atur Tarif Baru
    </a>
  </div>
</div>

<div class="card">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Tahun Ajaran</th>
          <th>Kelas</th>
          <th>Tingkat</th>
          <th>Nominal SPP</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($settings as $s)
        <tr>
          <td>
            <span style="font-weight:600;">{{ $s->academicYear?->name }}</span>
            @if($s->academicYear?->id == $activeYear?->id)
              <span class="badge badge-success" style="font-size:10px;margin-left:4px;">Aktif</span>
            @endif
          </td>
          <td style="font-weight:600;">{{ $s->schoolClass?->display_name }}</td>
          <td>{{ $s->schoolClass?->level }}</td>
          <td style="font-weight:600;color:var(--green-light);">Rp {{ number_format($s->amount, 0, ',', '.') }}</td>
          <td>
            <div style="display:flex;gap:4px;">
              <a href="{{ route('admin.spp-settings.edit', $s) }}" class="btn btn-sm btn-secondary" title="Edit">✏️</a>
              <form method="POST" action="{{ route('admin.spp-settings.destroy', $s) }}" onsubmit="return confirm('Hapus pengaturan tarif SPP untuk kelas ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger" title="Hapus">🗑</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="5">
          <div class="empty-state">
            <div class="empty-icon">💰</div>
            <p>Belum ada tarif SPP diatur. <a href="{{ route('admin.spp-settings.create') }}" style="color:var(--accent);">Atur sekarang →</a></p>
          </div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
