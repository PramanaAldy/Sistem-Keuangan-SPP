@extends('admin.layouts.app')
@section('page_title', 'Laporan Landscape')

@section('content')
@php
/** @var array $filteredDailyData */
/** @var array $summary */
/** @var int $month */
/** @var int $year */
/** @var array $monthNames */
/** @var array $shortMonthNames */
/** @var array $sequence */
@endphp
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.reports.index') }}">Laporan</a> <span>/</span> 
      <span>Landscape</span>
    </div>
    <h1>Laporan Landscape (Buku Kas Besar): {{ $monthNames[$month] }} {{ $year }}</h1>
  </div>
  <div class="page-header-actions" style="display:flex;gap:10px;align-items:center;">
    <form method="GET" action="{{ route('admin.reports.monthly') }}" style="display:flex;gap:8px;align-items:center;">
      <select name="month" class="form-control form-select" style="padding:6px 12px;height:auto;width:auto;" onchange="this.form.submit()">
        @foreach($monthNames as $num => $name)
          <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
      </select>
      <input type="number" name="year" class="form-control" value="{{ $year }}" style="padding:6px 12px;height:auto;width:80px;" onchange="this.form.submit()">
    </form>
    <a href="{{ route('admin.reports.monthly', ['month' => $month, 'year' => $year, 'action' => 'print']) }}" target="_blank" class="btn btn-secondary">🖨️ Cetak Landscape</a>
  </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
  <div class="table-container" style="overflow-x: auto; -webkit-overflow-scrolling: touch; padding: 20px;">
    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px;">Tabel Kas Besar: {{ $monthNames[$month] }} {{ $year }}</h3>
    <table style="width: 100%; border-collapse: collapse; min-width: 1400px; font-size: 12px;">
      <thead>
        <tr style="background: var(--bg-secondary);">
          <th colspan="7" style="border: 1px solid var(--border); padding: 10px; text-align: center; border-right: 3px solid var(--border);">Tabel Utama (Buku Kas)</th>
          <th colspan="12" style="border: 1px solid var(--border); padding: 10px; text-align: center;">Tabel Rekapitulasi Pembantu (Bulan)</th>
        </tr>
        <tr style="background: var(--bg-secondary);">
          <th style="border: 1px solid var(--border); padding: 8px;">Hari & Tanggal</th>
          <th style="border: 1px solid var(--border); padding: 8px; text-align: center;">Jumlah Anak</th>
          <th style="border: 1px solid var(--border); padding: 8px; text-align: center;">Total Anak</th>
          <th style="border: 1px solid var(--border); padding: 8px; text-align: right;">Debet</th>
          <th style="border: 1px solid var(--border); padding: 8px; text-align: right;">Saldo</th>
          <th style="border: 1px solid var(--border); padding: 8px; text-align: right;">Denda</th>
          <th style="border: 1px solid var(--border); padding: 8px; border-right: 3px solid var(--border);">Keterangan</th>
          @foreach($sequence as $m)
            <th style="border: 1px solid var(--border); padding: 8px; text-align: right;">{{ $shortMonthNames[$m] }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @forelse($filteredDailyData as $d)
        <tr>
          <td style="border: 1px solid var(--border); padding: 6px;">{{ \Carbon\Carbon::parse($d['date'])->locale('id')->isoFormat('dddd, DD-MM-YYYY') }}</td>
          <td style="border: 1px solid var(--border); padding: 6px; text-align: center;">{{ $d['jumlah_anak'] }}</td>
          <td style="border: 1px solid var(--border); padding: 6px; text-align: center; font-weight: bold;">{{ $d['total_anak'] }}</td>
          <td style="border: 1px solid var(--border); padding: 6px; text-align: right;">{{ number_format($d['debet'], 0, ',', '.') }}</td>
          <td style="border: 1px solid var(--border); padding: 6px; text-align: right;">{{ number_format($d['saldo'], 0, ',', '.') }}</td>
          <td style="border: 1px solid var(--border); padding: 6px; text-align: right; color: var(--red-light);">{{ $d['denda'] > 0 ? number_format($d['denda'], 0, ',', '.') : '-' }}</td>
          <td style="border: 1px solid var(--border); padding: 6px; border-right: 3px solid var(--border);">{{ implode(', ', $d['notes']) }}</td>
          
          @foreach($sequence as $m)
            <td style="border: 1px solid var(--border); padding: 6px; text-align: right;">{{ $d['months_paid'][$m] > 0 ? number_format($d['months_paid'][$m], 0, ',', '.') : '-' }}</td>
          @endforeach
        </tr>
        @empty
        <tr>
          <td colspan="19" style="text-align: center; padding: 20px; color: var(--text-muted); border: 1px solid var(--border);">Tidak ada transaksi pada bulan ini.</td>
        </tr>
        @endforelse
      </tbody>
      @if(count($filteredDailyData) > 0)
      <tfoot>
        <tr style="background: var(--bg-secondary);">
          <td colspan="3" style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold;">TOTAL</td>
          <td style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold;">{{ number_format($summary['total_debet'], 0, ',', '.') }}</td>
          <td colspan="3" style="border: 1px solid var(--border); border-left: none;"></td>
          @foreach($sequence as $m)
            <td style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold;">{{ $summary['total_per_month'][$m] > 0 ? number_format($summary['total_per_month'][$m], 0, ',', '.') : '-' }}</td>
          @endforeach
        </tr>
        <tr style="background: var(--bg-secondary);">
          <td colspan="3" style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold;">DENDA</td>
          <td style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold; color: var(--red-light);">{{ number_format($summary['total_denda'], 0, ',', '.') }}</td>
          <td colspan="15" style="border: 1px solid var(--border); border-left: none;"></td>
        </tr>
        <tr style="background: var(--bg-secondary);">
          <td colspan="3" style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold;">GRAND TOTAL</td>
          <td style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold;">{{ number_format($summary['grand_total'], 0, ',', '.') }}</td>
          <td colspan="15" style="border: 1px solid var(--border); border-left: none;"></td>
        </tr>
        <tr>
          <td colspan="3" style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold; background: #fef08a; color: #854d0e;">BALANCE</td>
          <td style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold; background: #fef08a; color: #854d0e;">{{ number_format($summary['grand_total'], 0, ',', '.') }}</td>
          <td colspan="3" style="border: 1px solid var(--border); border-left: none; background: #fef08a;"></td>
          @foreach($sequence as $m)
            <td style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold; background: #fef08a; color: #854d0e;">{{ $summary['total_per_month'][$m] > 0 ? number_format($summary['total_per_month'][$m], 0, ',', '.') : '-' }}</td>
          @endforeach
        </tr>
        <tr>
          <td colspan="3" style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold; background: #bbf7d0; color: #166534; text-transform: uppercase;">TO. SPP BLN {{ substr($monthNames[$month], 0, 3) }} '{{ substr($year, 2) }} (TARGET SPP)</td>
          <td style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold; background: #bbf7d0; color: #166534;">{{ number_format($summary['target_spp'], 0, ',', '.') }}</td>
          <td colspan="3" style="border: 1px solid var(--border); border-left: none; background: #bbf7d0;"></td>
          @foreach($sequence as $m)
            <td style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold; background: #bbf7d0; color: #166534;">{{ number_format($summary['target_spp'], 0, ',', '.') }}</td>
          @endforeach
        </tr>
        <tr>
          <td colspan="3" style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold; background: #fecaca; color: #991b1b;">SELISIH (TUNGGAKAN)</td>
          <td style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold; background: #fecaca; color: #991b1b;">{{ number_format($summary['selisih'], 0, ',', '.') }}</td>
          <td colspan="3" style="border: 1px solid var(--border); border-left: none; background: #fecaca;"></td>
          @foreach($sequence as $m)
            @php 
              $selisihBulan = $summary['target_spp'] - ($summary['total_per_month'][$m] ?? 0); 
            @endphp
            <td style="border: 1px solid var(--border); padding: 8px; text-align: right; font-weight: bold; background: #fecaca; color: #991b1b;">{{ number_format($selisihBulan, 0, ',', '.') }}</td>
          @endforeach
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
@endsection
