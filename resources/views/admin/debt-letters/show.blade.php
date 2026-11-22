<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Surat Tunggakan SPP — {{ $student->full_name }}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Times New Roman', Times, serif;
      font-size: 12pt;
      color: #111;
      background: #f5f5f5;
      padding: 20px;
    }
    .page {
      background: #fff;
      max-width: 790px;
      margin: 0 auto;
      padding: 40px 50px;
      box-shadow: 0 2px 20px rgba(0,0,0,0.12);
      min-height: 1050px;
      position: relative;
    }

    /* KOP SURAT */
    .kop {
      display: flex;
      align-items: center;
      gap: 16px;
      border-bottom: 3px double #111;
      padding-bottom: 12px;
      margin-bottom: 20px;
    }
    .kop-logo {
      width: 70px; height: 70px;
      border-radius: 6px;
      display: flex; align-items: center; justify-content: center;
      font-size: 10pt;
      color: #555;
      overflow: hidden;
    }
    .kop-logo img { width: 100%; height: 100%; object-fit: contain; }
    .kop-text { flex: 1; text-align: center; }
    .kop-text h1 { font-size: 18pt; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; }
    .kop-text h2 { font-size: 13pt; font-weight: 700; margin: 2px 0; }
    .kop-text p  { font-size: 9.5pt; color: #444; line-height: 1.5; }

    /* NOMOR & PERIHAL */
    .header-info {
      display: grid;
      grid-template-columns: 120px 10px 1fr;
      gap: 3px 0;
      font-size: 11pt;
      margin-bottom: 16px;
    }
    .header-info .label { color: #333; }
    .header-info .colon { text-align: left; padding: 0 4px; }

    /* PEMBUKA */
    .greeting { font-size: 11pt; margin-bottom: 14px; line-height: 1.7; }
    .greeting .alamat {
      margin: 6px 0 6px 30px;
      font-weight: bold;
    }

    /* TABEL TUNGGAKAN */
    .table-wrapper { margin: 18px 0; }
    .table-wrapper table {
      width: 100%;
      border-collapse: collapse;
      font-size: 10.5pt;
    }
    .table-wrapper th {
      background: #1a1a2e;
      color: white;
      padding: 8px 10px;
      text-align: center;
      font-weight: 700;
      border: 1px solid #1a1a2e;
    }
    .table-wrapper td {
      padding: 7px 10px;
      border: 1px solid #ccc;
      vertical-align: middle;
    }
    .table-wrapper tr:nth-child(even) td { background: #f9f9f9; }

    /* SUMMARY */
    .summary {
      margin: 16px 0;
      border-top: 1px solid #ccc;
      padding-top: 10px;
    }
    .summary .row {
      display: flex;
      justify-content: space-between;
      font-size: 11pt;
      padding: 4px 0;
    }
    .summary .row.total {
      border-top: 2px solid #111;
      font-weight: 900;
      font-size: 12.5pt;
      margin-top: 6px;
      padding-top: 8px;
    }

    /* PENUTUP */
    .closing { font-size: 11pt; line-height: 1.8; margin: 20px 0; }

    /* TANDA TANGAN */
    .sign-section {
      margin-top: 30px;
      display: flex;
      justify-content: space-between;
    }
    .sign-box { width: 200px; text-align: center; font-size: 11pt; }
    .sign-line {
      border-bottom: 1px solid #111;
      height: 60px;
      margin: 40px auto 6px;
      width: 160px;
    }
    .sign-name { font-weight: 700; }

    /* PRINT BUTTON (hidden when printing) */
    .print-actions {
      max-width: 790px;
      margin: 16px auto 0;
      display: flex;
      gap: 10px;
      justify-content: flex-end;
    }
    .print-btn {
      padding: 10px 24px;
      border: none;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
    }
    .btn-print  { background: #1a1a2e; color: white; }
    .btn-back   { background: #eee; color: #333; text-decoration: none; display: inline-block; padding: 10px 24px; border-radius: 8px; font-size: 13px; font-weight: 600; }

    .btn-wa     { background: #25D366; color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; }
    .btn-wa:hover { background: #1ebe5d; }
    .btn-pdf    { background: #e74c3c; color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; }
    .btn-pdf:hover { background: #c0392b; }

    /* MODAL */
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:1000; align-items:center; justify-content:center; }
    .modal-overlay.active { display:flex; }
    .modal-box { background:#fff; border-radius:16px; padding:28px 32px; max-width:440px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
    .modal-box h3 { font-size:17px; font-weight:700; margin-bottom:16px; color:#1a1a2e; display:flex; align-items:center; gap:8px; }
    .modal-info { background:#f8f9fa; border-radius:10px; padding:14px 16px; margin-bottom:16px; font-size:13px; line-height:1.8; }
    .modal-info strong { color:#1a1a2e; }
    .modal-note { font-size:12px; color:#666; margin-bottom:20px; background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:10px 14px; line-height:1.6; }
    .modal-actions { display:flex; gap:10px; justify-content:flex-end; }
    .modal-cancel { padding:9px 20px; background:#eee; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; color:#333; }
    .modal-send { padding:9px 20px; background:#25D366; border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; color:white; display:flex; align-items:center; gap:6px; }
    .modal-send:hover { background:#1ebe5d; }

    @media print {
      body { background: white; padding: 0; }
      .page { box-shadow: none; padding: 20px 30px; }
      .print-actions { display: none !important; }
    }
  </style>
</head>
<body>

<div class="print-actions">
  <a href="{{ route('admin.debt-letters.index') }}" class="btn-back">← Kembali</a>
  <button class="print-btn btn-print" onclick="window.print()">🖨️ Cetak Surat</button>
  <a href="{{ route('admin.debt-letters.download-pdf', $student) }}" class="btn-pdf" target="_blank">📄 Unduh PDF</a>
  @if($waUrl)
  <a href="{{ $waUrl }}" class="btn-wa" target="_blank">📲 Kirim via WhatsApp</a>
  @endif
</div>

<div class="page">
  {{-- ── KOP SURAT ──────────────────────────────────────────────────── --}}
  <div class="kop">
    <div class="kop-logo">
      <img src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.style.display='none';this.parentElement.innerText='LOGO'">
    </div>
    <div class="kop-text">
      <h1>Universal School</h1>
      <p>Jl. Kerta Dalem No. 15 BC, Sidakarya, Denpasar Selatan, Kota Denpasar, Bali 80224</p>
    </div>
  </div>

  {{-- ── NOMOR & PERIHAL ────────────────────────────────────────────── --}}
  @php
    $letterNo = 'USL/SPP/' . now()->format('Ymd') . '/' . str_pad($student->id, 4, '0', STR_PAD_LEFT);
    $today    = now()->locale('id')->isoFormat('D MMMM Y');
  @endphp

  <div class="header-info">
    <span class="label">Nomor</span><span class="colon">:</span><span>{{ $letterNo }}</span>
    <span class="label">Perihal</span><span class="colon">:</span><span><strong>Pemberitahuan Tunggakan Pembayaran SPP</strong></span>
    <span class="label">Lampiran</span><span class="colon">:</span><span>—</span>
    <span class="label">Tanggal</span><span class="colon">:</span><span>{{ $today }}</span>
  </div>

  {{-- ── TUJUAN ─────────────────────────────────────────────────────── --}}
  <div class="greeting">
    <p>Kepada Yth.</p>
    <div class="alamat">
      <p>Bapak/Ibu Orang Tua / Wali Murid</p>
      <p><strong>{{ $student->full_name }}</strong></p>
      @if($student->parent_name)
        <p>a.n. {{ $student->parent_name }}</p>
      @endif
      <p>{{ $student->schoolClass?->display_name }}</p>
    </div>
    <p>di Tempat</p>
  </div>

  {{-- ── SALAM ──────────────────────────────────────────────────────── --}}
  <p style="margin-bottom:12px;font-size:11pt;">
    Dengan hormat,<br>
    Sehubungan dengan administrasi keuangan sekolah,
    bersama surat ini kami sampaikan bahwa putra/putri Bapak/Ibu yang tercatat sebagai peserta didik
    di <strong>Universal School</strong> Tahun Ajaran <strong>{{ $activeYear->name }}</strong>,
    saat ini masih memiliki <strong>tunggakan pembayaran SPP</strong> dengan rincian sebagai berikut:
  </p>

  {{-- ── TABEL TUNGGAKAN ────────────────────────────────────────────── --}}
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th style="width:44px;">No.</th>
          <th>Bulan</th>
          <th style="text-align:right;">Nominal SPP / Bulan</th>
          <th style="text-align:right;">Denda</th>
          <th style="text-align:right;">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($arrearMonths as $i => $arrear)
        @php
            $monthFine = 0;
            if (isset($fineSetting) && $fineSetting && !$student->is_free_fine) {
                $m = is_array($arrear) ? $arrear['month'] : $arrear;
                $y = is_array($arrear) ? $arrear['year'] : date('Y');
                $monthFine = $fineSetting->calculateFine(\Carbon\Carbon::today(), $m, $y);
            }
            $monthTotal = $sppPerMonth + $monthFine;
        @endphp
        <tr>
          <td style="text-align:center;">{{ $i + 1 }}</td>
          <td>{{ is_array($arrear) ? $monthNames[$arrear['month']].' '.$arrear['year'] : $monthNames[$arrear] }}</td>
          <td style="text-align:right;">Rp {{ number_format($sppPerMonth, 0, ',', '.') }}</td>
          <td style="text-align:right;">Rp {{ number_format($monthFine, 0, ',', '.') }}</td>
          <td style="text-align:right;">Rp {{ number_format($monthTotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- ── SUMMARY ─────────────────────────────────────────────────────── --}}
  <div class="summary">
    <div class="row">
      <span>Total SPP ({{ count($arrearMonths) }} bulan)</span>
      <span>Rp {{ number_format($totalSpp, 0, ',', '.') }}</span>
    </div>
    @if($discountAmount > 0)
    <div class="row" style="color:#059669;">
      <span>Potongan / Diskon</span>
      <span>- Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
    </div>
    @endif
    @if($fineAmount > 0)
    <div class="row" style="color:#dc2626;">
      <span>Denda Keterlambatan Per tanggal {{ now()->format('d') }}</span>
      <span>+ Rp {{ number_format($fineAmount, 0, ',', '.') }}</span>
    </div>
    @endif
    <div class="row total">
      <span>TOTAL KEWAJIBAN</span>
      <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
    </div>
  </div>

  {{-- ── PENUTUP ─────────────────────────────────────────────────────── --}}
  <div class="closing">
    <p>
      Sehubungan dengan hal tersebut di atas, kami mohon kiranya Bapak/Ibu dapat segera menyelesaikan
      kewajiban pembayaran SPP paling lambat akhir bulan ini. Pembayaran dapat dilakukan langsung ke
      Bendahara Sekolah pada hari dan jam kerja.
    </p>
    <br>
    <p>
      Apabila Bapak/Ibu telah melakukan pembayaran sebelum surat ini Bapak/Ibu terima, harap mengabaikan surat ini.
      Untuk informasi lebih lanjut, silakan menghubungi pihak sekolah.
    </p>
    <br>
    <p>Demikian surat pemberitahuan ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.</p>
  </div>

  {{-- ── TANDA TANGAN ───────────────────────────────────────────────── --}}
  <div class="sign-section">
    <div class="sign-box" style="text-align:left;">
      <p>Hormat kami,</p>
      <div class="sign-line" style="margin-left:0;"></div>
      <p class="sign-name">Bendahara SPP</p>
      <p style="font-size:9.5pt;color:#555;">Universal School</p>
    </div>
    <div class="sign-box">
      <p>Mengetahui,</p>
      <div class="sign-line"></div>
      @php
          $level = $student->schoolClass?->level;
          $levelText = ($level == 'PG') ? 'TK' : $level;
      @endphp
      <p class="sign-name">Kepala Sekolah {{ $levelText }}</p>
      <p style="font-size:9.5pt;color:#555;">Universal School</p>
    </div>
  </div>
</div>

@if(request('print'))
<script>window.onload = () => window.print();</script>
@endif
</body>
</html>
