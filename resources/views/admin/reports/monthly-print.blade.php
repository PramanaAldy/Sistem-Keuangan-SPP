<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cetak Laporan Landscape: {{ $monthNames[$month] }} {{ $year }}</title>
  <style>
    body { font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #000; padding: 10px; margin: 0; }
    .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
    h1 { font-size: 16px; margin: 0; text-transform: uppercase; }
    h2 { font-size: 14px; margin: 5px 0; }
    p { margin: 3px 0; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #000; padding: 4px; text-align: left; }
    th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    @media print {
      @page { size: landscape; margin: 10mm; }
      body { margin: 0; padding: 0; }
      th { background-color: #e5e7eb !important; -webkit-print-color-adjust: exact; }
      .bg-yellow { background-color: #fef08a !important; -webkit-print-color-adjust: exact; }
      .bg-green { background-color: #bbf7d0 !important; -webkit-print-color-adjust: exact; }
    }
  </style>
</head>
<body onload="window.print()">

  <div class="header">
    <h1>UNIVERSAL SCHOOL DENPASAR</h1>
    <p>Jalan Kerta Dalem No. 15 BC, Sidakarya, Denpasar Selatan, Kota Denpasar, Bali 80224</p>
    <h2>LAPORAN LANDSCAPE KEUANGAN SPP (BUKU KAS BESAR)</h2>
    <p>Periode: <strong>{{ $monthNames[$month] }} {{ $year }}</strong></p>
  </div>

  <table>
    <thead>
      <tr>
        <th colspan="7">Tabel Utama (Buku Kas)</th>
        <th colspan="12">Tabel Rekapitulasi Pembantu (Sebaran Bulan Tagihan)</th>
      </tr>
      <tr>
        <th style="width: 80px;">Hari/Tanggal</th>
        <th style="width: 40px;">Jml Anak</th>
        <th style="width: 40px;">Tot Anak</th>
        <th class="text-right">Debet (Rp)</th>
        <th class="text-right">Saldo (Rp)</th>
        <th class="text-right">Denda (Rp)</th>
        <th>Keterangan</th>
        @foreach($sequence as $m)
          <th class="text-right">{{ $shortMonthNames[$m] }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @forelse($filteredDailyData as $d)
      <tr>
        <td>{{ date('d/m/Y', strtotime($d['date'])) }}</td>
        <td class="text-center">{{ $d['jumlah_anak'] }}</td>
        <td class="text-center" style="font-weight: bold;">{{ $d['total_anak'] }}</td>
        <td class="text-right">{{ number_format($d['debet'], 0, ',', '.') }}</td>
        <td class="text-right">{{ number_format($d['saldo'], 0, ',', '.') }}</td>
        <td class="text-right">{{ $d['denda'] > 0 ? number_format($d['denda'], 0, ',', '.') : '-' }}</td>
        <td>{{ implode(', ', $d['notes']) }}</td>
        
        @foreach($sequence as $m)
          <td class="text-right">{{ $d['months_paid'][$m] > 0 ? number_format($d['months_paid'][$m], 0, ',', '.') : '-' }}</td>
        @endforeach
      </tr>
      @empty
      <tr>
        <td colspan="19" class="text-center" style="padding: 20px;">Tidak ada transaksi pada bulan ini.</td>
      </tr>
      @endforelse
    </tbody>
    
    @if(count($filteredDailyData) > 0)
    <tfoot>
      <tr>
        <td colspan="3" class="text-right" style="font-weight: bold;">TOTAL</td>
        <td class="text-right" style="font-weight: bold;">{{ number_format($summary['total_debet'], 0, ',', '.') }}</td>
        <td colspan="3" style="border-left: none;"></td>
        @foreach($sequence as $m)
          <td class="text-right" style="font-weight: bold;">{{ $summary['total_per_month'][$m] > 0 ? number_format($summary['total_per_month'][$m], 0, ',', '.') : '-' }}</td>
        @endforeach
      </tr>
      <tr>
        <td colspan="3" class="text-right" style="font-weight: bold;">DENDA</td>
        <td class="text-right" style="font-weight: bold;">{{ number_format($summary['total_denda'], 0, ',', '.') }}</td>
        <td colspan="15" style="border-left: none;"></td>
      </tr>
      <tr>
        <td colspan="3" class="text-right" style="font-weight: bold;">GRAND TOTAL</td>
        <td class="text-right" style="font-weight: bold;">{{ number_format($summary['grand_total'], 0, ',', '.') }}</td>
        <td colspan="15" style="border-left: none;"></td>
      </tr>
      <tr class="bg-yellow">
        <td colspan="3" class="text-right" style="font-weight: bold;">BALANCE</td>
        <td class="text-right" style="font-weight: bold;">{{ number_format($summary['grand_total'], 0, ',', '.') }}</td>
        <td colspan="3" style="border-left: none;"></td>
        @foreach($sequence as $m)
          <td class="text-right" style="font-weight: bold;">{{ $summary['total_per_month'][$m] > 0 ? number_format($summary['total_per_month'][$m], 0, ',', '.') : '-' }}</td>
        @endforeach
      </tr>
      <tr class="bg-green">
        <td colspan="3" class="text-right" style="font-weight: bold; text-transform: uppercase;">TO. SPP BLN {{ substr($monthNames[$month], 0, 3) }} '{{ substr($year, 2) }} (TARGET SPP)</td>
        <td class="text-right" style="font-weight: bold;">{{ number_format($summary['target_spp'], 0, ',', '.') }}</td>
        <td colspan="3" style="border-left: none;"></td>
        @foreach($sequence as $m)
          <td class="text-right" style="font-weight: bold;">{{ number_format($summary['target_spp'], 0, ',', '.') }}</td>
        @endforeach
      </tr>
      <tr style="background: #fecaca; color: #991b1b;">
        <td colspan="3" class="text-right" style="font-weight: bold;">SELISIH (TUNGGAKAN)</td>
        <td class="text-right" style="font-weight: bold;">{{ number_format($summary['selisih'], 0, ',', '.') }}</td>
        <td colspan="3" style="border-left: none;"></td>
        @foreach($sequence as $m)
          @php 
            $selisihBulan = $summary['target_spp'] - ($summary['total_per_month'][$m] ?? 0); 
          @endphp
          <td class="text-right" style="font-weight: bold;">{{ number_format($selisihBulan, 0, ',', '.') }}</td>
        @endforeach
      </tr>
    </tfoot>
    @endif
  </table>

  <div style="margin-top: 30px; text-align: right; padding-right: 30px;">
    Denpasar, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}<br><br><br><br>
    ( ......................................... )<br>
    Admin / Kasir
  </div>

</body>
</html>
