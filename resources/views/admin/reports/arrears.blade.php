@extends('admin.layouts.app')
@section('page_title', 'Laporan Tunggakan')

@section('content')
@php
/** @var \Illuminate\Support\Collection $students */
/** @var \App\Models\AcademicYear|null $activeYear */
/** @var \Illuminate\Database\Eloquent\Collection $classes */
/** @var array $monthNames */
@endphp
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.reports.index') }}">Laporan</a> <span>/</span> 
      <span>Tunggakan</span>
    </div>
    <h1>Data Tunggakan SPP</h1>
    @if($activeYear)
      <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">Tahun Ajaran Aktif: {{ $activeYear->name }}</div>
    @endif
  </div>
  <div class="page-header-actions" style="display:flex;gap:10px;align-items:center;">
    <form method="GET" action="{{ route('admin.reports.arrears') }}" style="display:flex;gap:8px;align-items:center;">
      <select name="class_id" class="form-control form-select" style="padding:6px 12px;height:auto;width:auto;" onchange="this.form.submit()">
        <option value="">-- Semua Kelas --</option>
        @foreach($classes as $c)
          <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->display_name }}</option>
        @endforeach
      </select>
    </form>
    <button type="button" class="btn btn-secondary" onclick="window.print()">🖨️ Cetak</button>
  </div>
</div>

<div class="card" style="margin-bottom:24px;border-left:4px solid var(--red-light);background:rgba(239,68,68,0.05);">
  <div style="display:flex;align-items:center;gap:16px;">
    <div style="font-size:32px;color:var(--red-light);">⚠️</div>
    <div>
      <div style="font-size:16px;font-weight:600;color:var(--red-light);margin-bottom:4px;">Perhatian</div>
      <div style="font-size:13px;color:var(--text-secondary);">
        Daftar berikut menampilkan siswa berstatus aktif yang belum melunasi SPP hingga bulan berjalan (<b>{{ $monthNames[now()->month] }}</b>). 
        Siswa dengan status Gratis SPP tidak ditampilkan di sini.
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Nama Siswa</th>
          <th>Kelas</th>
          <th>Jml Tunggakan</th>
          <th>Bulan Tertunggak</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($students as $s)
        <tr>
          <td>
            <div style="font-weight:600;">{{ $s->full_name }}</div>
            <div style="font-size:11px;color:var(--text-muted);">NISN: {{ $s->nisn ?? '-' }}</div>
          </td>
          <td>{{ $s->schoolClass?->display_name }}</td>
          <td><span class="badge badge-danger" style="font-weight:bold;">{{ $s->arrear_count }} Bulan</span></td>
          <td>
            <div style="display:flex;flex-wrap:wrap;gap:4px;">
              @foreach($s->arrear_months as $arrear)
                <span style="background:var(--bg-primary);border:1px solid var(--border);padding:2px 6px;border-radius:4px;font-size:11px;color:var(--red-light);">
                  {{ $monthNames[$arrear['month']] }} {{ $arrear['year'] }}
                </span>
              @endforeach
            </div>
          </td>
          <td>
            <div style="display:flex;gap:4px;">
              <a href="{{ route('admin.transactions.create', ['student_id' => $s->id]) }}" class="btn btn-sm btn-primary" title="Input Pembayaran">💳 Bayar</a>
              <a href="{{ route('admin.reminders.whatsapp', $s) }}" class="btn btn-sm btn-success" style="background:#25d366;border-color:#25d366;color:white;" title="Kirim Pengingat WA" target="_blank">📱 WA</a>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted);">
            @if(!$activeYear)
              Tidak ada Tahun Ajaran yang aktif. Silakan atur di menu Tahun Ajaran.
            @else
              Luar biasa! Tidak ada tunggakan SPP yang tercatat.
            @endif
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- CSS Print --}}
<style>
  @media print {
    body * { visibility: hidden; }
    .card, .card * { visibility: visible; }
    .card { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none !important; border: none; background: white !important; color: black !important; }
    .page-header-actions, .breadcrumb { display: none !important; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 6px; color: black !important; font-size:12px; }
  }
</style>
@endsection
