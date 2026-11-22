@extends('admin.layouts.app')
@section('page_title', 'Setting Denda')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <span>Setting Denda</span>
    </div>
    <h1>Pengaturan Denda Keterlambatan SPP</h1>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('admin.fine-settings.create') }}" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Atur Denda Baru
    </a>
  </div>
</div>

<div class="card">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Tahun Ajaran</th>
          <th>Tgl Jatuh Tempo</th>
          <th>Tipe Denda</th>
          <th>Nominal Denda</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($settings as $s)
        <tr>
          <td style="font-weight:600;">
            {{ $s->academicYear?->name }}
            @if($s->academicYear?->is_active)
              <span class="badge badge-success" style="font-size:10px;margin-left:4px;">Aktif</span>
            @endif
          </td>
          <td>Tanggal {{ $s->due_date }} setiap bulan</td>
          <td>
            @if($s->fine_type === 'flat') <span class="badge badge-info">Flat (Satu Kali)</span>
            @else <span class="badge badge-warning">Per Hari Telat</span> @endif
          </td>
          <td style="font-weight:600;color:var(--red-light);">Rp {{ number_format($s->amount, 0, ',', '.') }}</td>
          <td>
            <div style="display:flex;gap:4px;">
              <a href="{{ route('admin.fine-settings.edit', $s) }}" class="btn btn-sm btn-secondary" title="Edit">✏️</a>
              <form method="POST" action="{{ route('admin.fine-settings.destroy', $s) }}" onsubmit="return confirm('Hapus pengaturan denda untuk tahun ajaran ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger" title="Hapus">🗑</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="5">
          <div class="empty-state">
            <div class="empty-icon">⚠️</div>
            <p>Belum ada aturan denda yang diatur. <a href="{{ route('admin.fine-settings.create') }}" style="color:var(--accent);">Atur sekarang →</a></p>
          </div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
