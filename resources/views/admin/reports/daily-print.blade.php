<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cetak Laporan Harian: {{ $date->format('d/m/Y') }}</title>
  <style>
    body { font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #000; padding: 20px; }
    .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
    h1 { font-size: 16px; margin: 0; text-transform: uppercase; }
    h2 { font-size: 14px; margin: 10px 0; }
    p { margin: 5px 0; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #000; padding: 6px; text-align: left; vertical-align: middle; }
    th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    @media print {
      @page { size: landscape; margin: 10mm; }
      body { margin: 0; padding: 0; }
    }
  </style>
</head>
<body onload="window.print()">

  <div class="header">
    <h1>UNIVERSAL SCHOOL DENPASAR</h1>
    <p>Jalan Kerta Dalem No. 15 BC, Sidakarya, Denpasar Selatan, Kota Denpasar, Bali 80224</p>
    <h2>LAPORAN HARIAN KEUANGAN SPP</h2>
    <p>Tanggal: {{ $date->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
  </div>

  <div style="margin-bottom:15px; font-weight:bold; font-size:12px;">
    Ringkasan Metode: Tunai Rp {{ number_format($totalCash, 0, ',', '.') }} | 
    Transfer Rp {{ number_format($totalTransfer, 0, ',', '.') }} | 
    Debet Rp {{ number_format($totalDebit, 0, ',', '.') }}
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 30px;">No</th>
        <th>No. Kwitansi</th>
        <th>Nama Siswa</th>
        <th>Kelas</th>
        <th>Bulan dibayar</th>
        <th>Metode</th>
        <th class="text-right">Nominal SPP</th>
        <th class="text-right">Denda</th>
        <th class="text-right">Nominal Bayar</th>
        <th class="text-right">Nominal Transfer</th>
        <th>Hari & Tanggal</th>
        <th>Keterangan</th>
        <th class="text-right">Selisih</th>
      </tr>
    </thead>
    <tbody>
      @php 
        $mns = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        
        // Pengelompokan persaudaraan per keluarga
        $studentIds = $transactions->pluck('student_id')->filter()->unique()->toArray();
        $siblingPairs = \Illuminate\Support\Facades\DB::table('student_siblings')
            ->whereIn('student_id', $studentIds)
            ->get();

        $familyClusters = [];
        $visitedIds = [];

        foreach ($transactions as $tx) {
            if (in_array($tx->id, $visitedIds)) continue;
            
            $cluster = [$tx];
            $visitedIds[] = $tx->id;
            
            $added = true;
            while ($added) {
                $added = false;
                $currentClusterStudentIds = collect($cluster)->pluck('student_id')->filter()->unique()->toArray();
                $connectedSiblingIds = $siblingPairs->whereIn('student_id', $currentClusterStudentIds)->pluck('sibling_id')->toArray();
                
                foreach ($transactions as $otherTx) {
                    if (!in_array($otherTx->id, $visitedIds)) {
                        if ($otherTx->student_id && in_array($otherTx->student_id, $connectedSiblingIds)) {
                            $cluster[] = $otherTx;
                            $visitedIds[] = $otherTx->id;
                            $added = true;
                        }
                    }
                }
            }
            $familyClusters[] = $cluster;
        }

        $sumSpp = 0;
        $sumDiscount = 0;
        $sumFine = 0;
        $sumBayar = 0;
        $sumTransfer = 0;
        $sumSelisih = 0;
        $globalIteration = 1;
      @endphp

      @forelse($familyClusters as $cluster)
        @php
          $clusterBayar = 0;
          $hasTransferMethod = false;
          foreach ($cluster as $cTx) {
              $cTxPaid = $cTx->paid_amount ?: $cTx->grand_total;
              $clusterBayar += $cTxPaid;
              if ($cTx->spp_method === 'transfer') {
                  $hasTransferMethod = true;
              }
          }

          $firstTx = $cluster[0];
          $dispTransfer = $firstTx->transfer_nominal > 0 ? $firstTx->transfer_nominal : ($hasTransferMethod ? $clusterBayar : 0);
          $calcSelisih = $dispTransfer > 0 ? abs($dispTransfer - $clusterBayar) : 0;
          $signedSelisih = $dispTransfer > 0 ? ($dispTransfer - $clusterBayar) : 0;

          $sumTransfer += $dispTransfer;
          $sumSelisih += $signedSelisih;
        @endphp

        @foreach($cluster as $idx => $tx)
          @php
            $txPaid = $tx->paid_amount ?: $tx->grand_total;
            $txSpp = $tx->spp_amount > 0 ? $tx->spp_amount : max(0, $tx->grand_total - $tx->fine_amount);
            
            $sumSpp += $txSpp;
            $sumDiscount += $tx->discount_amount;
            $sumFine += $tx->fine_amount;
            $sumBayar += $txPaid;
          @endphp
          <tr>
            <td class="text-center">{{ $globalIteration++ }}</td>
            <td>{{ $tx->receipt_number }}</td>
            <td>{{ $tx->student?->full_name ?: '-' }}</td>
            <td class="text-center">{{ $tx->student?->schoolClass?->display_name ?: '-' }}</td>
            <td>
              @foreach($tx->details as $d)
                {{ $mns[$d->month] }}'{{ substr($d->year,2) }}@if(!$loop->last), @endif
              @endforeach
            </td>
            <td class="text-center">
                @if($tx->spp_method === 'cash') Tunai
                @elseif($tx->spp_method === 'transfer') Transfer
                @elseif($tx->spp_method === 'debit') Debet
                @else {{ ucfirst($tx->spp_method) }}
                @endif
            </td>
            <td class="text-right">
              Rp {{ number_format($txSpp, 0, ',', '.') }}
              @if($tx->discount_amount > 0)
                <br><span style="font-size:9px; color:#d9534f;">-Rp {{ number_format($tx->discount_amount, 0, ',', '.') }}</span>
              @endif
            </td>
            <td class="text-right">{{ $tx->fine_amount > 0 ? 'Rp '.number_format($tx->fine_amount, 0, ',', '.') : '-' }}</td>
            <td class="text-right" style="font-weight:bold;">Rp {{ number_format($txPaid, 0, ',', '.') }}</td>
            
            @if($idx === 0)
              <td class="text-right" rowspan="{{ count($cluster) }}" style="font-weight:bold;">
                {{ $dispTransfer > 0 ? 'Rp '.number_format($dispTransfer, 0, ',', '.') : '-' }}
                @if(count($cluster) > 1)
                  <br><span style="font-size:9px; font-weight:normal; color:#555;">(Gabungan {{ count($cluster) }} Siswa)</span>
                @endif
              </td>
              <td class="text-center" rowspan="{{ count($cluster) }}">
                {{ \Carbon\Carbon::parse($firstTx->payment_date)->locale('id')->isoFormat('dddd, D MMM YYYY') }}
              </td>
              <td class="text-center" rowspan="{{ count($cluster) }}">
                @if($dispTransfer > 0)
                  @if($dispTransfer > $clusterBayar)
                    <strong>Lebih</strong>
                  @elseif($dispTransfer < $clusterBayar)
                    <strong>Kurang</strong>
                  @else
                    <strong>Sesuai</strong>
                  @endif
                @else
                  -
                @endif
                @if($firstTx->notes)
                  <br><span style="font-size:10px; color:#444;">{{ $firstTx->notes }}</span>
                @endif
              </td>
              <td class="text-right" rowspan="{{ count($cluster) }}" style="font-weight:bold;">
                {{ $calcSelisih > 0 ? 'Rp '.number_format($calcSelisih, 0, ',', '.') : '-' }}
              </td>
            @endif
          </tr>
        @endforeach
      @empty
      <tr>
        <td colspan="13" class="text-center" style="padding:20px;">Belum ada pencatatan pada tanggal ini.</td>
      </tr>
      @endforelse
    </tbody>
    @if($transactions->count() > 0)
    <tfoot>
      <tr style="font-weight: bold; background-color: #f2f2f2;">
        <td colspan="6" class="text-right">TOTAL PEMASUKAN</td>
        <td class="text-right">
          Rp {{ number_format($sumSpp, 0, ',', '.') }}
          @if($sumDiscount > 0)
            <br><span style="font-size:9px; color:#d9534f;">-Rp {{ number_format($sumDiscount, 0, ',', '.') }}</span>
          @endif
        </td>
        <td class="text-right">Rp {{ number_format($sumFine, 0, ',', '.') }}</td>
        <td class="text-right">Rp {{ number_format($sumBayar, 0, ',', '.') }}</td>
        <td class="text-right">Rp {{ number_format($sumTransfer, 0, ',', '.') }}</td>
        <td colspan="2"></td>
        <td class="text-right">{{ $sumSelisih < 0 ? '-Rp ' . number_format(abs($sumSelisih), 0, ',', '.') : 'Rp ' . number_format($sumSelisih, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
    @endif
  </table>

  <div style="margin-top: 40px; text-align: right; padding-right: 30px;">
    Denpasar, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}<br><br><br><br>
    ( ......................................... )<br>
    Admin / Kasir
  </div>

</body>
</html>
