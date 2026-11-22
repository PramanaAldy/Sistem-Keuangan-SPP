<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kwitansi {{ $transaction->receipt_number }}</title>
  <style>
    /* Reset & Base */
    body { font-family: 'Courier New', Courier, monospace; font-size: 13px; color: #000; margin: 0; padding: 15px; }
    .receipt { padding: 20px; width: 100%; box-sizing: border-box; position: relative; display: flex; flex-direction: column; }
    
    table { width: 100%; border-collapse: collapse; }
    td { vertical-align: top; }
    
    /* Header */
    .header-left h1 { font-family: 'Times New Roman', Times, serif; font-size: 18px; margin: 0 0 5px; text-transform: uppercase; font-weight: bold; }
    .header-left p { font-size: 12px; margin: 2px 0; color: #333; }
    .header-right { text-align: right; }
    .header-right h2 { font-family: 'Times New Roman', Times, serif; font-size: 20px; margin: 0 0 5px; text-transform: uppercase; font-weight: bold; }
    .header-right p { font-size: 14px; margin: 0; font-weight: bold; }

    .header-separator { display: block; height: 2px; background: #000; margin-top: 10px; margin-bottom: 15px; }

    /* Content Area */
    .content-wrapper { flex: 1; } /* Pushes footer down */
    .col-left { width: 55%; padding-right: 15px; border-right: 1px dotted #999; }
    .col-right { width: 45%; padding-left: 15px; }

    /* Key-Value Pair Format */
    .info-table { width: 100%; font-size: 14px; }
    .info-table td { padding-bottom: 8px; }
    .label { width: 120px; color: #333; }
    .separator { width: 15px; text-align: center; }
    .value { font-weight: bold; }

    /* Stamp */
    .stamp-lunas {
        font-size: 28px;
        color: #e3342f;
        border: 4px solid #e3342f;
        padding: 4px 12px;
        transform: rotate(-15deg);
        display: inline-block;
        font-weight: bold;
        letter-spacing: 2px;
        position: absolute;
        top: 15px;
        left: 20px;
        opacity: 0.8;
    }

    /* Right Column (Rincian & Total) */
    .rincian-table { width: 100%; font-size: 14px; margin-bottom: 10px; }
    .rincian-table td { padding-bottom: 8px; }
    .rincian-label { color: #333; }
    .rincian-value { font-weight: bold; text-align: right; }
    
    .total-box { background: #eaeaea; border: 2px solid #000; padding: 12px; text-align: center; margin-top: 10px; }
    .total-title { font-size: 12px; color: #333; letter-spacing: 1px; margin-bottom: 5px; text-transform: uppercase; }
    .total-amount { font-size: 20px; font-weight: bold; font-family: 'Courier New', Courier, monospace; }

    /* Footer & Signature */
    .footer-table { width: 100%; margin-top: auto; padding-top: 20px; position: relative; z-index: 1; }
    .notes { font-size: 12px; font-style: italic; color: #555; line-height: 1.5; }
    
    .sign-area { display: inline-block; text-align: center; width: 220px; position: relative; }
    .sign-title { font-size: 14px; margin-bottom: 50px; }
    .sign-line { border-bottom: 1px solid #000; width: 100%; margin-bottom: 5px; }
    .sign-name { font-size: 14px; }

    /* Watermark */
    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.1;
        z-index: 0;
        pointer-events: none;
    }
    .watermark img {
        width: 450px;
        height: auto;
    }

    /* Pengaturan Cetak (Print) Kertas */
    @media print {
      @page { 
        size: A5 landscape; /* Mengatur skala kuitansi ke ukuran asli kwitansi (A5 atau setengah A4) */
        margin: 5mm; 
      }
      body { 
        padding: 0; margin: 0; background: #fff; 
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      .receipt { 
        min-height: 98vh; /* Akan memenuhi area kertas yang tersedia secara proporsional */
      }
      /* Sembunyikan elemen browser default saat print */
    }

  </style>
</head>
<body>

@php
// Helper untuk formatting bulan SPP
$mns = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$bulanTahun = [];
foreach($transaction->details as $d) {
    if(!isset($bulanTahun[$d->year])) $bulanTahun[$d->year] = [];
    $bulanTahun[$d->year][] = $mns[$d->month];
}
$pembayaranText = [];
foreach($bulanTahun as $year => $months) {
    $pembayaranText[] = implode(', ', $months) . ' ' . $year;
}
$pembayaranString = implode(' | ', $pembayaranText);

$methodText = [
    'cash' => 'Tunai',
    'transfer' => 'Transfer',
    'debit' => 'Debet'
][$transaction->spp_method] ?? 'Lainnya';

@endphp

<div class="receipt">
  <div class="watermark">
    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png'))) }}" alt="Watermark Logo">
  </div>
  
  {{-- Header --}}
  <table style="position: relative; z-index: 1;">
    <tr>
      <td class="header-left">
        <h1>UNIVERSAL SCHOOL DENPASAR</h1>
        <p>Jalan Kerta Dalem No. 15 BC, Sidakarya, Denpasar Selatan, Kota Denpasar, Bali 80224</p>
        <p>085101507789 - 085105107789</p>
        <p>Email: universal15bc@gmail.com</p>
      </td>
      <td class="header-right">
        <h2>KWITANSI PEMBAYARAN</h2>
        <p>No: #{{ $transaction->receipt_number }}</p>
      </td>
    </tr>
  </table>

  <div class="header-separator" style="position: relative; z-index: 1;"></div>

  <div class="content-wrapper" style="position: relative; z-index: 1;">
  {{-- Content 2 Kolom --}}
  <table>
    <tr>
      {{-- Kolom Kiri: Data Siswa & Transaksi --}}
      <td class="col-left">
        <table class="info-table">
          <tr>
            <td class="label">Siswa</td>
            <td class="separator">:</td>
            <td class="value">{{ $transaction->student->full_name }}</td>
          </tr>
          <tr>
            <td class="label">Kelas</td>
            <td class="separator">:</td>
            <td class="value">{{ $transaction->student->schoolClass?->display_name ?? '-' }}</td>
          </tr>
          <tr>
            <td class="label">Pembayaran</td>
            <td class="separator">:</td>
            <td class="value">SPP {{ $pembayaranString }}</td>
          </tr>
          <tr>
            <td class="label">Tahun Ajaran</td>
            <td class="separator">:</td>
            <td class="value">{{ $transaction->academicYear->name ?? '-' }}</td>
          </tr>
          <tr>
            <td class="label">Tanggal Bayar</td>
            <td class="separator">:</td>
            <td class="value">{{ $transaction->payment_date->format('d/m/Y') }}</td>
          </tr>
          <tr>
            <td class="label">Metode</td>
            <td class="separator">:</td>
            <td class="value">{{ $methodText }}</td>
          </tr>
        </table>
      </td>

      {{-- Kolom Kanan: Rincian & Total --}}
      <td class="col-right">
        <table class="rincian-table">
          <tr>
            <td class="rincian-label">Total SPP</td>
            <td class="rincian-value">Rp {{ number_format($transaction->spp_amount, 0, ',', '.') }}</td>
          </tr>
          @if($transaction->fine_amount > 0)
          <tr>
            <td class="rincian-label">Denda Keterlambatan</td>
            <td class="rincian-value">Rp {{ number_format($transaction->fine_amount, 0, ',', '.') }}</td>
          </tr>
          @endif
          @if($transaction->discount_amount > 0)
          <tr>
            <td class="rincian-label">Diskon</td>
            <td class="rincian-value">- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td>
          </tr>
          @endif
        </table>
        <div class="total-box">
          <div class="total-title">TOTAL YANG DIBAYAR</div>
          <div class="total-amount">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</div>
        </div>
      </td>
    </tr>
  </table>
  </div>

  {{-- Footer & Tanda Tangan --}}
  <table class="footer-table">
    <tr>
      <td style="width: 50%;">
        <div class="notes">
          * Simpan bukti pembayaran ini sebagai tanda terima yang sah.<br>
          * Dicetak pada: {{ now()->timezone('Asia/Makassar')->format('d/m/Y H.i.s') }}
        </div>
      </td>
      <td style="width: 50%; text-align: right;">
        <div class="sign-area">
          <div class="stamp-lunas">LUNAS</div>
          <div class="sign-title">Penerima</div>
          <div class="sign-line"></div>
          <div class="sign-name">( Petugas TU )</div>
        </div>
      </td>
    </tr>
  </table>

</div>

</body>
</html>
