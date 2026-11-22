@php
/**
 * @var array $pesdik
 * @var array $monthNames
 * @var int $month
 * @var int $year
 * @var float|int|string $totalCollected
 * @var int $freeCount
 * @var int $khususCount
 * @var int $targetPembayar
 */
@endphp
@extends('admin.layouts.app')
@section('page_title', 'Ringkasan Pesdik & SPP')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.reports.index') }}">Laporan</a> <span>/</span> 
      <span>Rekapitulasi</span>
    </div>
    <h1>Laporan Perubahan Pesdik & SPP: {{ $monthNames[$month] ?? 'Semua Bulan' }} {{ $year }}</h1>
  </div>
  <div class="page-header-actions" style="display:flex;gap:10px;align-items:center;">
    <form method="GET" action="{{ route('admin.reports.recap') }}" style="display:flex;gap:8px;align-items:center;">
      <select name="month" class="form-control form-select" style="padding:6px 12px;height:auto;width:auto;" onchange="this.form.submit()">
        @foreach($monthNames as $num => $name)
          <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
      </select>
      <input type="number" name="year" class="form-control" value="{{ $year }}" style="padding:6px 12px;height:auto;width:80px;" onchange="this.form.submit()">
    </form>
    <a href="{{ route('admin.reports.recap', ['month' => $month, 'year' => $year, 'action' => 'print']) }}" target="_blank" class="btn btn-secondary">🖨️ Cetak</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">

  {{-- Tabel Perubahan Pesdik --}}
  <div class="card" style="padding:0;overflow:hidden;">
    <h3 style="padding:20px;margin:0;border-bottom:1px solid var(--border);font-size:16px;">Tabel Perubahan Peserta Didik</h3>
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="background:var(--bg-secondary);text-align:left;">
          <th style="padding:12px 20px;border-bottom:1px solid var(--border);">Jenjang</th>
          <th style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">Awal</th>
          <th style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">Perubahan</th>
          <th style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">Akhir</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="padding:12px 20px;border-bottom:1px solid var(--border);font-weight:bold;">TK (PG, TK A, TK B)</td>
          <td style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">{{ $pesdik['tk'] }}</td>
          <td style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">0</td>
          <td style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">{{ $pesdik['tk'] }}</td>
        </tr>
        <tr>
          <td style="padding:12px 20px;border-bottom:1px solid var(--border);font-weight:bold;">SD (Kelas 1 - 6)</td>
          <td style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">{{ $pesdik['sd'] }}</td>
          <td style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">0</td>
          <td style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">{{ $pesdik['sd'] }}</td>
        </tr>
        <tr>
          <td style="padding:12px 20px;border-bottom:1px solid var(--border);font-weight:bold;">SMP (Kelas 7 - 9)</td>
          <td style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">{{ $pesdik['smp'] }}</td>
          <td style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">0</td>
          <td style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">{{ $pesdik['smp'] }}</td>
        </tr>
      </tbody>
      <tfoot>
        <tr style="background:#fef08a;">
          <td style="padding:12px 20px;font-weight:bold;">TOTAL</td>
          <td style="padding:12px 20px;font-weight:bold;text-align:center;">{{ $pesdik['total'] }}</td>
          <td style="padding:12px 20px;font-weight:bold;text-align:center;">0</td>
          <td style="padding:12px 20px;font-weight:bold;text-align:center;">{{ $pesdik['total'] }}</td>
        </tr>
      </tfoot>
    </table>
  </div>

  {{-- Tabel Ringkasan Pendapatan & Verifikasi --}}
  <div style="display:flex;flex-direction:column;gap:24px;">
    
    <div class="card" style="padding:0;overflow:hidden;">
      <h3 style="padding:20px;margin:0;border-bottom:1px solid var(--border);font-size:16px;">Verifikasi Pendapatan Kasir</h3>
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
          <tr style="background:var(--bg-secondary);text-align:left;">
            <th style="padding:12px 20px;border-bottom:1px solid var(--border);">Keterangan</th>
            <th style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:center;">Jumlah Anak Aktif</th>
            <th style="padding:12px 20px;border-bottom:1px solid var(--border);text-align:right;">Nominal</th>
          </tr>
        </thead>
        <tbody>
          <tr style="background:#bbf7d0;">
            <td style="padding:12px 20px;font-weight:bold;color:#166534;">Total Penerimaan Kasir (SPP + Denda) — {{ $monthNames[$month] ?? '' }} {{ $year }}</td>
            <td style="padding:12px 20px;text-align:center;font-weight:bold;">{{ $pesdik['total'] }}</td>
            <td style="padding:12px 20px;text-align:right;font-weight:bold;font-size:16px;color:#166534;">Rp {{ number_format($totalCollected, 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td style="padding:10px 20px 4px 20px;font-size:12px;color:var(--text-secondary);">↳ SPP Bersih (setelah diskon)</td>
            <td style="padding:10px 20px 4px 20px;text-align:center;"></td>
            <td style="padding:10px 20px 4px 20px;text-align:right;font-size:12px;">Rp {{ number_format($totalSppNet, 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td style="padding:4px 20px 12px 20px;font-size:12px;color:var(--red-light);">↳ Denda Keterlambatan</td>
            <td style="padding:4px 20px 12px 20px;text-align:center;"></td>
            <td style="padding:4px 20px 12px 20px;text-align:right;font-size:12px;color:var(--red-light);">Rp {{ number_format($totalFine, 0, ',', '.') }}</td>
          </tr>
        </tbody>
      </table>
      <div style="padding:16px 20px;font-size:12px;color:var(--text-muted);background:#f9fafb;">
        * Angka Penerimaan di atas ditarik langsung dari jumlah uang masuk (paid_amount) transaksi harian selama periode bulan terpilih.
      </div>
    </div>

    <div class="card" style="padding:20px;background:#eff6ff;border:1px solid #bfdbfe;">
      <h3 style="margin:0 0 15px 0;font-size:16px;color:#1e3a8a;">Analisis Anak Yang Bayar Secara Murni</h3>
      <div style="display:grid;grid-template-columns:1fr auto;gap:12px;font-size:14px;color:#1e40af;">
        <div>Total Keseluruhan Siswa Aktif:</div>
        <div style="font-weight:bold;">{{ $pesdik['total'] }} Siswa</div>
        
        <div>Dikurang Anak Free (Beasiswa Full):</div>
        <div style="font-weight:bold;color:#b91c1c;">- {{ $freeCount }} Siswa</div>
        
        <div>Dikurang Anak Khusus (Diskon/Saudara):</div>
        <div style="font-weight:bold;color:#b91c1c;">- {{ $khususCount }} Siswa</div>
      </div>
      <hr style="border-color:#bfdbfe;margin:15px 0;">
      <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:bold;color:#1d4ed8;">
        <span>Target Siswa Pembayar Penuh:</span>
        <span>{{ $targetPembayar }} Siswa</span>
      </div>
    </div>

  </div>
@endsection
