@extends('admin.layouts.app')
@section('page_title', 'Data Setor Bank')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> <span>Setor Bank</span></div>
    <h1>Riwayat Setor Bank</h1>
  </div>
  <div class="page-header-actions" style="display:flex;gap:10px;">
    <form method="GET" action="{{ route('admin.bank-deposits.index') }}" style="display:flex;gap:8px;align-items:center;">
      <select name="month" class="form-control form-select" onchange="this.form.submit()">
        <option value="">Semua Bulan</option>
        @foreach(range(1, 12) as $m)
          <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
        @endforeach
      </select>
      <select name="year" class="form-control form-select" onchange="this.form.submit()">
        <option value="">Semua Tahun</option>
        @foreach(range(date('Y')-2, date('Y')) as $y)
          <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
      </select>
    </form>
    <a href="{{ route('admin.bank-deposits.print', request()->all()) }}" target="_blank" class="btn btn-secondary">
      🖨️ Cetak
    </a>
    <a href="{{ route('admin.bank-deposits.create') }}" class="btn btn-primary">
      ➕ Tambah Setoran
    </a>
  </div>
</div>

<div class="card">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Total Nominal Tunai</th>
          <th>Rekening 2040</th>
          <th>Rekening 4831</th>
          <th>Admin</th>
          <th style="width:100px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($deposits as $dep)
        <tr>
          <td>{{ $dep->deposit_date->format('d/m/Y') }}</td>
          <td style="font-weight:bold;color:var(--green-light);">Rp {{ number_format($dep->total_amount, 0, ',', '.') }}</td>
          <td>
            <div style="font-size:12px;color:var(--text-muted);">{{ floatval($dep->bank_1_percentage) }}%</div>
            <div style="font-weight:600;">Rp {{ number_format($dep->bank_1_amount, 0, ',', '.') }}</div>
          </td>
          <td>
            <div style="font-size:12px;color:var(--text-muted);">{{ floatval($dep->bank_2_percentage) }}%</div>
            <div style="font-weight:600;">Rp {{ number_format($dep->bank_2_amount, 0, ',', '.') }}</div>
          </td>
          <td>{{ $dep->admin->name ?? '-' }}</td>
          <td>
            <form action="{{ route('admin.bank-deposits.destroy', $dep) }}" method="POST" onsubmit="return confirm('Hapus pencatatan deposit ini?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger">Hapus</button>
            </form>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted);">
            Belum ada data setoran.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($deposits->hasPages())
    <div style="padding:16px;">
      {{ $deposits->links('pagination') }}
    </div>
  @endif
</div>
@endsection
