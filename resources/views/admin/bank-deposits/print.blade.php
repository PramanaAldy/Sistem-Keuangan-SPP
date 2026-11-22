<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cetak Laporan Setor Bank</title>
  <style>
    body { font-family: 'Times New Roman', Times, serif; font-size: 13px; color: #000; padding: 20px; }
    .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
    h1 { font-size: 18px; margin: 0; text-transform: uppercase; }
    p { margin: 5px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #000; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; text-align: center; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    @media print {
      @page { margin: 15mm; }
      body { margin: 0; padding: 0; }
    }
  </style>
</head>
<body onload="window.print()">

  <div class="header">
    <h1>UNIVERSAL SCHOOL DENPASAR</h1>
    <p>Jalan Kerta Dalem No. 15 BC, Sidakarya, Denpasar Selatan, Kota Denpasar, Bali 80224</p>
    <h2>LAPORAN SETORAN BANK (REK 2040 & REK 4831)</h2>
    @if(request('month') && request('year'))
      <p>Periode: Bulan {{ request('month') }} Tahun {{ request('year') }}</p>
    @else
      <p>Keseluruhan Waktu</p>
    @endif
  </div>

  <table>
    <thead>
      <tr>
        <th rowspan="2" style="width: 50px;">No</th>
        <th rowspan="2">Tanggal Setor</th>
        <th rowspan="2" class="text-right">Total Setoran</th>
        <th colspan="2" class="text-center">Rekening 2040</th>
        <th colspan="2" class="text-center">Rekening 4831</th>
      </tr>
      <tr>
        <th class="text-center">%</th>
        <th class="text-right">Nominal</th>
        <th class="text-center">%</th>
        <th class="text-right">Nominal</th>
      </tr>
    </thead>
    <tbody>
      @php 
        $grandTotal = 0;
        $totalRek1 = 0;
        $totalRek2 = 0;
      @endphp
      @forelse($deposits as $index => $dep)
        @php
          $grandTotal += $dep->total_amount;
          $totalRek1 += $dep->bank_1_amount;
          $totalRek2 += $dep->bank_2_amount;
        @endphp
      <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td>{{ $dep->deposit_date->format('d/m/Y') }}</td>
        <td class="text-right" style="font-weight: bold;">Rp {{ number_format($dep->total_amount, 0, ',', '.') }}</td>
        <td class="text-center">{{ floatval($dep->bank_1_percentage) }}%</td>
        <td class="text-right">Rp {{ number_format($dep->bank_1_amount, 0, ',', '.') }}</td>
        <td class="text-center">{{ floatval($dep->bank_2_percentage) }}%</td>
        <td class="text-right">Rp {{ number_format($dep->bank_2_amount, 0, ',', '.') }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="text-center">Belum ada pencatatan pada periode ini.</td>
      </tr>
      @endforelse
    </tbody>
    @if($deposits->count() > 0)
    <tfoot>
      <tr style="font-weight: bold; background-color: #f9f9f9;">
        <td colspan="2" class="text-right">GRAND TOTAL</td>
        <td class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
        <td></td>
        <td class="text-right">Rp {{ number_format($totalRek1, 0, ',', '.') }}</td>
        <td></td>
        <td class="text-right">Rp {{ number_format($totalRek2, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
    @endif
  </table>

  <div style="margin-top: 50px; text-align: right; padding-right: 50px;">
    Denpasar, {{ date('d F Y') }}<br><br><br><br>
    ( ......................................... )<br>
    Admin / Kasir
  </div>

</body>
</html>
