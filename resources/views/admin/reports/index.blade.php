@extends('admin.layouts.app')
@section('page_title', 'Laporan Keuangan')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <span>Laporan</span>
    </div>
    <h1>Laporan Keuangan SPP</h1>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:20px;margin-top:20px;">
  
  {{-- Laporan Harian --}}
  <a href="{{ route('admin.reports.daily') }}" style="text-decoration:none;">
    <div class="card" style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;background:rgba(99,102,241,0.1);color:var(--accent);">
          📅
        </div>
        <div>
          <h3 style="margin-bottom:4px;font-size:16px;color:var(--text-primary);">Laporan Harian</h3>
          <p style="font-size:12px;color:var(--text-muted);margin:0;">Rekap penerimaan kas/transfer per hari.</p>
        </div>
      </div>
    </div>
  </a>

  {{-- Laporan Landscape --}}
  <a href="{{ route('admin.reports.monthly') }}" style="text-decoration:none;">
    <div class="card" style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;background:rgba(16,185,129,0.1);color:var(--green-light);">
          📆
        </div>
        <div>
          <h3 style="margin-bottom:4px;font-size:16px;color:var(--text-primary);">Laporan Landscape</h3>
          <p style="font-size:12px;color:var(--text-muted);margin:0;">Rincian pendapatan dalam satu bulan penuh (Kas Besar).</p>
        </div>
      </div>
    </div>
  </a>

  {{-- Laporan Tahunan --}}
  <a href="{{ route('admin.reports.yearly') }}" style="text-decoration:none;">
    <div class="card" style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;background:rgba(245,158,11,0.1);color:var(--warning);">
          📈
        </div>
        <div>
          <h3 style="margin-bottom:4px;font-size:16px;color:var(--text-primary);">Laporan Tahunan</h3>
          <p style="font-size:12px;color:var(--text-muted);margin:0;">Akumulasi pendapatan per tahun ajaran.</p>
        </div>
      </div>
    </div>
  </a>
  
  {{-- Laporan Rekapitulasi Pesdik --}}
  <a href="{{ route('admin.reports.recap') }}" style="text-decoration:none;">
    <div class="card" style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;background:rgba(14,165,233,0.1);color:#0ea5e9;">
          👥
        </div>
        <div>
          <h3 style="margin-bottom:4px;font-size:16px;color:var(--text-primary);">Rekap Pesdik & SPP</h3>
          <p style="font-size:12px;color:var(--text-muted);margin:0;">Perubahan anak & Verifikasi Kasir.</p>
        </div>
      </div>
    </div>
  </a>

  {{-- Laporan Tunggakan --}}
  <a href="{{ route('admin.reports.arrears') }}" style="text-decoration:none;">
    <div class="card" style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;background:rgba(239,68,68,0.1);color:var(--red-light);">
          ⚠️
        </div>
        <div>
          <h3 style="margin-bottom:4px;font-size:16px;color:var(--text-primary);">Data Tunggakan</h3>
          <p style="font-size:12px;color:var(--text-muted);margin:0;">Daftar siswa telat bayar beserta bulannya.</p>
        </div>
      </div>
    </div>
  </a>

  {{-- File Kontrol --}}
  <a href="{{ route('admin.reports.control-file') }}" style="text-decoration:none;">
    <div class="card" style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;background:rgba(139,92,246,0.1);color:#8b5cf6;">
          🗂️
        </div>
        <div>
          <h3 style="margin-bottom:4px;font-size:16px;color:var(--text-primary);">File Kontrol</h3>
          <p style="font-size:12px;color:var(--text-muted);margin:0;">Grid tabel status pembayaran SPP (Buku Kontrol).</p>
        </div>
      </div>
    </div>
  </a>

</div>
@endsection
