@extends('admin.layouts.app')
@section('page_title', 'Data Diskon')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <span>Diskon</span>
    </div>
    <h1>Master Data Diskon</h1>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('admin.discounts.create') }}" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Tambah Diskon
    </a>
  </div>
</div>

<div class="card">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Nama Diskon</th>
          <th>Tipe</th>
          <th>Besaran Nilai</th>
          <th>Keterangan</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($discounts as $d)
        <tr>
          <td style="font-weight:600;">{{ $d->name }}</td>
          <td>
            @if($d->type === 'family') <span class="badge badge-info">👨‍👩‍👧 Keluarga</span>
            @elseif($d->type === 'achievement') <span class="badge badge-purple">🏆 Prestasi</span>
            @elseif($d->type === 'poor') <span class="badge badge-warning">🤝 Kurang Mampu</span>
            @else <span class="badge badge-secondary">Lainnya</span> @endif
          </td>
          <td style="font-weight:600;color:var(--green-light);">{{ $d->formatted_value }}</td>
          <td style="font-size:12px;">{{ Str::limit($d->description, 40) }}</td>
          <td>
            @if($d->is_active) <span class="badge badge-success">Aktif</span>
            @else <span class="badge badge-secondary">Nonaktif</span> @endif
          </td>
          <td>
            <div style="display:flex;gap:4px;">
              <a href="{{ route('admin.discounts.edit', $d) }}" class="btn btn-sm btn-secondary" title="Edit">✏️</a>
              <form method="POST" action="{{ route('admin.discounts.destroy', $d) }}" onsubmit="return confirm('Hapus diskon {{ $d->name }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger" title="Hapus">🗑</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6">
          <div class="empty-state">
            <div class="empty-icon">🏷️</div>
            <p>Belum ada master data diskon. <a href="{{ route('admin.discounts.create') }}" style="color:var(--accent);">Tambah sekarang →</a></p>
          </div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
