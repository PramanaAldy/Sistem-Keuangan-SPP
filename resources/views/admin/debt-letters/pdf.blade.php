<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Times New Roman', Times, serif;
      font-size: 11pt;
      color: #111;
      padding: 0;
    }

    /* KOP SURAT */
    .kop {
      display: table;
      width: 100%;
      border-bottom: 3px double #111;
      padding-bottom: 10px;
      margin-bottom: 16px;
    }
    .kop-logo-cell {
      display: table-cell;
      width: 75px;
      vertical-align: middle;
    }
    .kop-logo-cell img {
      width: 65px;
      height: 65px;
      object-fit: contain;
    }
    .kop-text-cell {
      display: table-cell;
      text-align: center;
      vertical-align: middle;
    }
    .kop-text-cell h1 { font-size: 17pt; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; }
    .kop-text-cell p  { font-size: 9pt; color: #444; line-height: 1.5; }

    /* NOMOR & PERIHAL */
    .header-info {
      width: 100%;
      font-size: 10.5pt;
      margin-bottom: 14px;
      border-collapse: collapse;
    }
    .header-info td { padding: 2px 0; }
    .header-info .label { width: 100px; color: #333; }
    .header-info .colon { width: 14px; }

    /* TUJUAN */
    .greeting { font-size: 10.5pt; margin-bottom: 12px; line-height: 1.7; }
    .alamat { margin: 6px 0 6px 28px; font-weight: bold; }

    /* ISI SURAT */
    .isi { font-size: 10.5pt; line-height: 1.8; margin-bottom: 14px; text-align: justify; }

    /* TABEL TUNGGAKAN */
    .tabel { width: 100%; border-collapse: collapse; font-size: 10pt; margin: 14px 0; }
    .tabel th {
      background: #1a1a2e;
      color: white;
      padding: 7px 8px;
      text-align: center;
      border: 1px solid #1a1a2e;
    }
    .tabel td {
      padding: 6px 8px;
      border: 1px solid #bbb;
    }
    .tabel tr:nth-child(even) td { background: #f5f5f5; }

    /* SUMMARY */
    .summary { border-top: 1px solid #ccc; padding-top: 8px; margin: 10px 0; font-size: 10.5pt; }
    .summary-row { display: table; width: 100%; padding: 3px 0; }
    .summary-row .s-label { display: table-cell; }
    .summary-row .s-value { display: table-cell; text-align: right; }
    .summary-total {
      border-top: 2px solid #111;
      font-weight: 900;
      font-size: 11.5pt;
      margin-top: 4px;
      padding-top: 6px;
    }

    /* PENUTUP */
    .closing { font-size: 10.5pt; line-height: 1.8; margin: 14px 0; text-align: justify; }

    /* TANDA TANGAN */
    .sign-section { display: table; width: 100%; margin-top: 28px; }
    .sign-left  { display: table-cell; width: 50%; font-size: 10.5pt; }
    .sign-right { display: table-cell; width: 50%; text-align: center; font-size: 10.5pt; }
    .sign-line  { border-bottom: 1px solid #111; height: 50px; margin: 36px auto 4px; width: 150px; }
    .sign-name  { font-weight: 700; }
  </style>
</head>
<body>
  @php
    $letterNo = 'USL/SPP/' . now()->format('Ymd') . '/' . str_pad($student->id, 4, '0', STR_PAD_LEFT);
    $today    = now()->locale('id')->isoFormat('D MMMM Y');
  @endphp

  {{-- KOP SURAT --}}
  <div class="kop">
    <div class="kop-logo-cell">
      {{-- DomPDF memerlukan path absolut untuk gambar --}}
      @php $logoPath = public_path('images/logo.png'); @endphp
      @if(file_exists($logoPath))
        <img src="{{ $logoPath }}">
      @endif
    </div>
    <div class="kop-text-cell">
      <h1>Universal School</h1>
      <p>Jl. Kerta Dalem No. 15 BC, Sidakarya, Denpasar Selatan, Kota Denpasar, Bali 80224</p>
    </div>
  </div>

  {{-- NOMOR & PERIHAL --}}
  <table class="header-info">
    <tr>
      <td class="label">Nomor</td>
      <td class="colon">:</td>
      <td>{{ $letterNo }}</td>
    </tr>
    <tr>
      <td class="label">Perihal</td>
      <td class="colon">:</td>
      <td><strong>Pemberitahuan Tunggakan Pembayaran SPP</strong></td>
    </tr>
    <tr>
      <td class="label">Lampiran</td>
      <td class="colon">:</td>
      <td>—</td>
    </tr>
    <tr>
      <td class="label">Tanggal</td>
      <td class="colon">:</td>
      <td>{{ $today }}</td>
    </tr>
  </table>

  {{-- TUJUAN --}}
  <div class="greeting">
    <p>Kepada Yth.</p>
    <div class="alamat">
      <p>Bapak/Ibu Orang Tua / Wali Murid</p>
      <p><strong>{{ $student->full_name }}</strong></p>
      @if($student->parent_name)
        <p>a.n. {{ $student->parent_name }}</p>
      @endif
      <p>{{ optional($student->schoolClass)->display_name }}</p>
    </div>
    <p>di Tempat</p>
  </div>

  {{-- ISI SURAT --}}
  <p class="isi">
    Dengan hormat,<br>
    Sehubungan dengan administrasi keuangan sekolah,
    bersama surat ini kami sampaikan bahwa putra/putri Bapak/Ibu yang tercatat sebagai peserta didik
    di <strong>Universal School</strong> Tahun Ajaran <strong>{{ $activeYear->name }}</strong>,
    saat ini masih memiliki <strong>tunggakan pembayaran SPP</strong> dengan rincian sebagai berikut:
  </p>

  {{-- TABEL TUNGGAKAN --}}
  <table class="tabel">
    <thead>
      <tr>
        <th style="width:40px;">No.</th>
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

  {{-- SUMMARY --}}
  <div class="summary">
    <div class="summary-row">
      <span class="s-label">Total SPP ({{ count($arrearMonths) }} bulan)</span>
      <span class="s-value">Rp {{ number_format($totalSpp, 0, ',', '.') }}</span>
    </div>
    @if($discountAmount > 0)
    <div class="summary-row" style="color:#059669;">
      <span class="s-label">Potongan / Diskon</span>
      <span class="s-value">- Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
    </div>
    @endif
    @if($fineAmount > 0)
    <div class="summary-row" style="color:#dc2626;">
      <span class="s-label">Denda Keterlambatan Per tanggal {{ now()->format('d') }}</span>
      <span class="s-value">+ Rp {{ number_format($fineAmount, 0, ',', '.') }}</span>
    </div>
    @endif
    <div class="summary-row summary-total">
      <span class="s-label">TOTAL KEWAJIBAN</span>
      <span class="s-value">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
    </div>
  </div>

  {{-- PENUTUP --}}
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

  {{-- TANDA TANGAN --}}
  <div class="sign-section">
    <div class="sign-left">
      <p>Hormat kami,</p>
      <div class="sign-line" style="margin-left:0; width:140px;"></div>
      <p class="sign-name">Bendahara SPP</p>
      <p style="font-size:9pt;color:#555;">Universal School</p>
    </div>
    <div class="sign-right">
      <p>Mengetahui,</p>
      <div class="sign-line"></div>
      @php
          $level = $student->schoolClass?->level;
          $levelText = ($level == 'PG') ? 'TK' : $level;
      @endphp
      <p class="sign-name">Kepala Sekolah {{ $levelText }}</p>
      <p style="font-size:9pt;color:#555;">Universal School</p>
    </div>
  </div>

</body>
</html>
