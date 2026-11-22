@extends('admin.layouts.app')
@section('page_title', 'Audit Log')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> <span>Audit Log</span></div>
    <h1>Audit Log</h1>
    <p>Riwayat semua aktivitas admin dalam sistem</p>
  </div>
  <a href="{{ route('admin.audit-logs.export', request()->only('event','admin','date')) }}"
     class="btn btn-secondary" style="display:flex;align-items:center;gap:8px;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:16px;height:16px;">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
    </svg>
    Export Excel
  </a>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
  <form method="GET" action="{{ route('admin.audit-logs.index') }}">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
      <div style="flex:1;min-width:160px;">
        <label class="form-label">Event</label>
        <select name="event" class="form-control form-select">
          <option value="">Semua Event</option>
          @foreach($eventOptions as $val => $label)
            <option value="{{ $val }}" {{ request('event') == $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div style="flex:1;min-width:160px;">
        <label class="form-label">Admin</label>
        <input type="text" name="admin" class="form-control" placeholder="Nama admin..." value="{{ request('admin') }}">
      </div>
      <div style="min-width:150px;">
        <label class="form-label">Tanggal</label>
        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
      </div>
      <div style="display:flex;gap:8px;">
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary">Reset</a>
      </div>
    </div>
  </form>
</div>

{{-- Tabel Log --}}
<div class="card">
  <div class="table-container">
    <table>
      <thead><tr>
        <th>#</th>
        <th>Waktu</th>
        <th>Event</th>
        <th>Admin</th>
        <th>Deskripsi</th>
        <th>Target</th>
        <th>IP</th>
      </tr></thead>
      <tbody>
        @forelse($logs as $log)
        <tr>
          <td style="color:var(--text-muted);font-size:11px;">{{ $log->id }}</td>
          <td style="white-space:nowrap;font-size:12px;">
            <div>{{ $log->created_at->format('d M Y') }}</div>
            <div style="color:var(--text-muted);">{{ $log->created_at->format('H:i:s') }}</div>
          </td>
          <td>
            <span class="badge {{ $log->event_color }}">{{ $log->event_label }}</span>
          </td>
          <td>
            <div class="student-name">{{ $log->admin_name ?? '-' }}</div>
          </td>
          <td style="max-width:320px;">
            <div style="font-size:13px;">{{ $log->description }}</div>
            @if($log->old_values || $log->new_values)
              <details style="margin-top:4px;">
                <summary style="font-size:11px;color:var(--text-muted);cursor:pointer;">Lihat perubahan</summary>
                <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;">
                  @if($log->old_values)
                    <div style="font-size:11px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:6px;padding:6px 10px;">
                      <div style="font-weight:600;color:var(--red-light);margin-bottom:3px;">Sebelum</div>
                      @foreach($log->old_values as $k => $v)
                        <div><span style="color:var(--text-muted);">{{ $k }}:</span> {{ $v }}</div>
                      @endforeach
                    </div>
                  @endif
                  @if($log->new_values)
                    <div style="font-size:11px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:6px;padding:6px 10px;">
                      <div style="font-weight:600;color:var(--green-light);margin-bottom:3px;">Sesudah</div>
                      @foreach($log->new_values as $k => $v)
                        <div><span style="color:var(--text-muted);">{{ $k }}:</span> {{ $v }}</div>
                      @endforeach
                    </div>
                  @endif
                </div>
              </details>
            @endif
          </td>
          <td>
            @if($log->auditable_type)
              <span style="font-size:11px;color:var(--text-muted);">{{ $log->target_name }}</span>
              <div style="font-size:12px;font-family:monospace;">#{{ $log->auditable_id }}</div>
            @else
              <span style="color:var(--text-muted);">—</span>
            @endif
          </td>
          <td style="font-size:11px;color:var(--text-muted);font-family:monospace;">{{ $log->ip_address }}</td>
        </tr>
        @empty
        <tr><td colspan="7">
          <div class="empty-state">
            <div class="empty-icon">📋</div>
            <p>Belum ada log aktivitas.</p>
          </div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($logs->hasPages())
    <div style="padding:16px;border-top:1px solid var(--border);">
      {{ $logs->links('pagination') }}
    </div>
  @endif
</div>
@endsection
