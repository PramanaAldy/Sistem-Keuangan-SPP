@extends('admin.layouts.app')
@section('page_title', 'Laporan Tahunan')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.reports.index') }}">Laporan</a> <span>/</span> 
      <span>Tahunan</span>
    </div>
    <h1>Rekapitulasi Tahunan: {{ $year }}</h1>
  </div>
  <div class="page-header-actions" style="display:flex;gap:10px;align-items:center;">
    <form method="GET" action="{{ route('admin.reports.yearly') }}" style="display:flex;gap:8px;align-items:center;">
      <label style="font-size:13px;color:var(--text-secondary);">Tahun Masehi:</label>
      <input type="number" name="year" class="form-control" value="{{ $year }}" style="padding:6px 12px;height:auto;width:100px;" onchange="this.form.submit()">
    </form>
    <button type="button" class="btn btn-secondary" onclick="window.print()">🖨️ Cetak</button>
  </div>
</div>

<div class="card" style="margin-bottom:24px;background:linear-gradient(135deg,var(--bg-card),var(--bg-secondary));border:1px solid var(--border);">
  <div style="font-size:14px;color:var(--text-muted);margin-bottom:8px;">Total Pemasukan Tahun {{ $year }}</div>
  <div style="font-size:32px;font-weight:800;color:var(--green-light);">Rp {{ number_format($yearTotal, 0, ',', '.') }}</div>
</div>

<div class="card">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Bulan</th>
          <th>Jml Transaksi</th>
          <th style="text-align:right;">Tunai</th>
          <th style="text-align:right;">Transfer Bank</th>
          <th style="text-align:right;">Debit</th>
          <th style="text-align:right;">Total Pendapatan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($monthlyData as $m => $data)
        <tr style="{{ $data['total'] > 0 ? 'font-weight:600;' : 'color:var(--text-muted);' }}">
          <td>{{ $monthNames[$m] }}</td>
          <td>{{ $data['count'] }} transaksi</td>
          <td style="text-align:right;{{ $data['cash'] > 0 ? 'color:var(--text-primary);' : '' }}">Rp {{ number_format($data['cash'], 0, ',', '.') }}</td>
          <td style="text-align:right;{{ $data['transfer'] > 0 ? 'color:var(--blue-light);' : '' }}">Rp {{ number_format($data['transfer'], 0, ',', '.') }}</td>
          <td style="text-align:right;{{ $data['debit'] > 0 ? 'color:var(--purple);' : '' }}">Rp {{ number_format($data['debit'], 0, ',', '.') }}</td>
          <td style="text-align:right;{{ $data['total'] > 0 ? 'color:var(--green-light);' : '' }}">Rp {{ number_format($data['total'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="background:var(--bg-secondary);font-weight:bold;">
          <td colspan="5" style="text-align:right;padding-right:20px;">TOTAL TAHUN {{ $year }}</td>
          <td style="text-align:right;color:var(--green-light);">Rp {{ number_format($yearTotal, 0, ',', '.') }}</td>
        </tr>
      </tfoot>
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
