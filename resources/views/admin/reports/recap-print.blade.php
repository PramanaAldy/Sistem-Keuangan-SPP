<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cetak Laporan Mutasi &amp; SPP</title>
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
    <h2>LAPORAN PERUBAHAN PESERTA DIDIK DAN VERIFIKASI PENDAPATAN SPP</h2>
    @if(request('month') && request('year'))
      <p>Periode: Bulan {{ $monthNames[$month] ?? 'Semua Bulan' }} Tahun {{ request('year') }}</p>
    @else
      <p>Keseluruhan Waktu</p>
    @endif
  </div>

  <h3 style="font-size: 14px; margin-top: 20px; margin-bottom: 8px;">A. Tabel Perubahan Peserta Didik</h3>
  <table>
    <thead>
      <tr>
        <th style="width: 50px;">No</th>
        <th>Jenjang / Tingkat Dasar</th>
        <th class="text-center">Awal</th>
        <th class="text-center">Perubahan</th>
        <th class="text-center">Akhir</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="text-center">1</td>
        <td>TK (PG, TK A, TK B)</td>
        <td class="text-center">{{ $pesdik['tk'] }}</td>
        <td class="text-center">0</td>
        <td class="text-center">{{ $pesdik['tk'] }}</td>
      </tr>
      <tr>
        <td class="text-center">2</td>
        <td>SD (Kelas 1 - 6)</td>
        <td class="text-center">{{ $pesdik['sd'] }}</td>
        <td class="text-center">0</td>
        <td class="text-center">{{ $pesdik['sd'] }}</td>
      </tr>
      <tr>
        <td class="text-center">3</td>
        <td>SMP (Kelas 7 - 9)</td>
        <td class="text-center">{{ $pesdik['smp'] }}</td>
        <td class="text-center">0</td>
        <td class="text-center">{{ $pesdik['smp'] }}</td>
      </tr>
    </tbody>
    <tfoot>
      <tr style="font-weight: bold; background-color: #f9f9f9;">
        <td colspan="2" class="text-center">TOTAL KESELURUHAN</td>
        <td class="text-center">{{ $pesdik['total'] }}</td>
        <td class="text-center">0</td>
        <td class="text-center">{{ $pesdik['total'] }}</td>
      </tr>
    </tfoot>
  </table>

  <h3 style="font-size: 14px; margin-top: 30px; margin-bottom: 8px;">B. Verifikasi Penerimaan Kasir &amp; Prediksi Tagihan Murni</h3>
  <table>
    <thead>
      <tr>
        <th class="text-center">Uraian Data Siswa</th>
        <th class="text-center">Jumlah</th>
        <th class="text-center">Cek Silang Penerimaan (Grand Total Harian)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1. Total Siswa Aktif</td>
        <td class="text-center" style="font-weight: bold;">{{ $pesdik['total'] }}</td>
        <td rowspan="4" style="text-align: center; vertical-align: middle; font-size: 16px; font-weight: bold;">
          Rp {{ number_format(floatval($totalCollected), 0, ',', '.') }}
        </td>
      </tr>
      <tr>
        <td>2. Anak Bebas SPP (Free)</td>
        <td class="text-center">- {{ $freeCount }}</td>
      </tr>
      <tr>
        <td>3. Anak Khusus (Diskon / Anak Guru / Saudara)</td>
        <td class="text-center">- {{ $khususCount }}</td>
      </tr>
      <tr style="font-weight: bold; background-color: #f9f9f9;">
        <td>4. Sisa Target Siswa (Pembayar SPP Normal/Murni)</td>
        <td class="text-center">{{ $targetPembayar }}</td>
      </tr>
    </tbody>
  </table>

  <div style="margin-top: 50px; text-align: right; padding-right: 50px;">
    Denpasar, {{ date('d F Y') }}<br><br><br><br>
    ( ......................................... )<br>
    Kepala Sekolah / Admin
  </div>

</body>
</html>
