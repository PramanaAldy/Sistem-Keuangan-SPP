@extends('admin.layouts.app')
@section('page_title', 'File Kontrol')
@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.reports.index') }}">Laporan</a> <span>/</span> 
      File Kontrol
    </div>
    <h1>File Kontrol</h1>
    <p>File kontrol (grid) status pembayaran SPP siswa</p>
  </div>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:16px;">
  <form method="GET" action="{{ route('admin.reports.control-file') }}">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
      <div style="flex:1;min-width:200px;">
        <label class="form-label">Cari Siswa</label>
        <input type="text" name="search" class="form-control" placeholder="Nama siswa..." value="{{ request('search') }}">
      </div>
      <div style="min-width:160px;">
        <label class="form-label">Kelas</label>
        <select name="class_id" class="form-control form-select">
          <option value="">Semua Kelas</option>
          @foreach($classes as $cls)
            <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->display_name }}</option>
          @endforeach
        </select>
      </div>
      <div style="min-width:160px;">
        <label class="form-label">Tahun Ajaran</label>
        <select name="academic_year_id" class="form-control form-select">
          @foreach($academicYears as $yr)
            <option value="{{ $yr->id }}" {{ $selectedYearId == $yr->id ? 'selected' : '' }}>{{ $yr->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
        <a href="{{ route('admin.reports.control-file') }}" class="btn btn-secondary">Reset</a>
      </div>
    </div>
  </form>
</div>

<div class="card" style="padding:0; overflow:hidden;">
  <div style="overflow-x:auto;">
    <table style="width:100%; border-collapse: collapse; font-size: 11px; white-space: nowrap;" class="control-table">
      <thead>
        <tr style="background:#ffeb3b; font-weight:bold; text-align:center;">
          <th style="padding:8px; border:1px solid #ccc;">NO</th>
          <th style="padding:8px; border:1px solid #ccc;">NAMA</th>
          <th style="padding:8px; border:1px solid #ccc;">KELAS</th>
          @php 
            $mns = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
          @endphp
          @foreach($sequence as $s)
            <th style="padding:8px; border:1px solid #ccc; text-transform:uppercase; min-width:80px;">{{ $mns[$s['month']-1] }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @forelse($students as $idx => $student)
        @php
          $paidData = [];
          foreach($student->transactions as $tx) {
            foreach($tx->details as $d) {
              $paidData[$d->month] = [
                'date' => \Carbon\Carbon::parse($tx->payment_date)->translatedFormat('d M Y'),
                'year' => $d->year,
              ];
            }
          }
        @endphp
        <tr>
          <td style="padding:4px 8px; border:1px solid #ccc; text-align:center;">{{ $idx + 1 }}</td>
          <td style="padding:4px 8px; border:1px solid #ccc;">{{ $student->full_name }}</td>
          <td style="padding:4px 8px; border:1px solid #ccc; text-align:center;">{{ $student->schoolClass?->display_name }}</td>

          @foreach($sequence as $s)
            @php 
              $m = $s['month'];
              $y = $s['year'];
            @endphp
            @if($student->status !== 'active')
              {{-- Inactive / Kolom Kosong --}}
              <td style="padding:4px; border:1px solid #ccc; background:#f3f4f6;"></td>
            @elseif($student->is_free_spp)
              {{-- Khusus / Free SPP --}}
              <td style="padding:4px; border:1px solid #ccc; background-color:#ffeb3b; background-image: linear-gradient(to top right, transparent calc(50% - 0.5px), #666, transparent calc(50% + 0.5px)), linear-gradient(to bottom right, transparent calc(50% - 0.5px), #666, transparent calc(50% + 0.5px)); text-align:center;"></td>
            @else
              {{-- Reguler SPP --}}
              @if(isset($paidData[$m]) && $paidData[$m]['year'] == $y)
                <td style="padding:4px; border:1px solid #ccc; text-align:center;">
                  {{ $paidData[$m]['date'] }}
                </td>
              @else
                <td style="padding:4px; border:1px solid #ccc; background:#f97316; color:white; text-align:center;">
                  Belum
                </td>
              @endif
            @endif
          @endforeach
        </tr>
        @empty
        <tr>
          <td colspan="15" style="padding:16px; text-align:center; border:1px solid #ccc;">Belum ada data.</td>
        </tr>
        @endforelse
      </tbody>
      @if(count($students) > 0)
      <tfoot style="font-weight:bold;">
        <tr style="background:#ffeb3b;">
          <td colspan="3" style="padding:6px 8px; border:1px solid #ccc; text-align:left;">JUMLAH ANAK YANG BELUM BAYAR</td>
          @foreach($sequence as $s)
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">{{ $summary[$s['month']]['belum_bayar'] }}</td>
          @endforeach
        </tr>
        <tr style="background:#86efac;">
          <td colspan="3" style="padding:6px 8px; border:1px solid #ccc; text-align:left;">JUMLAH ANAK YANG KHUSUS</td>
          @foreach($sequence as $s)
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">{{ $summary[$s['month']]['khusus'] }}</td>
          @endforeach
        </tr>
        <tr style="background:#ffffff;">
          <td colspan="3" style="padding:6px 8px; border:1px solid #ccc; text-align:left;">Jumlah kolom kosong</td>
          @foreach($sequence as $s)
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">{{ $summary[$s['month']]['kolom_kosong'] }}</td>
          @endforeach
        </tr>
        <tr style="background:#f97316; color:white;">
          <td colspan="3" style="padding:6px 8px; border:1px solid #ccc; text-align:left;">JUMLAH ANAK YANG SUDAH BAYAR</td>
          @foreach($sequence as $s)
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">{{ $summary[$s['month']]['sudah_bayar'] }}</td>
          @endforeach
        </tr>
        <tr style="background:#ffffff;">
          <td colspan="3" style="padding:6px 8px; border:1px solid #ccc; text-align:left;">jumlah yang harus bayar</td>
          @foreach($sequence as $s)
            <td style="padding:6px; border:1px solid #ccc; text-align:center;">{{ $summary[$s['month']]['harus_bayar'] }}</td>
          @endforeach
        </tr>
        <tr style="background:#ffffff;">
          <td colspan="3" style="padding:6px 8px; border:1px solid #ccc; text-align:left; font-weight:normal;">bayar bulan sebelumnya</td>
          @foreach($sequence as $s)
            <td style="padding:6px; border:1px solid #ccc; text-align:right;">{{ $summary[$s['month']]['bayar_sebelumnya'] }}</td>
          @endforeach
        </tr>
        <tr style="background:#bbf7d0;">
          <td colspan="3" style="padding:6px 8px; border:1px solid #ccc; text-align:left; font-weight:normal;">bayar bulan bersangkutan</td>
          @foreach($sequence as $s)
            <td style="padding:6px; border:1px solid #ccc; text-align:right;">{{ $summary[$s['month']]['bayar_bersangkutan'] }}</td>
          @endforeach
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>

<style>
  .control-table td, .control-table th {
    color: #1f2937;
  }
</style>
@endsection
