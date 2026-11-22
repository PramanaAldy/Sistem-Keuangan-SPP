@extends('admin.layouts.app')
@section('page_title', 'Laporan Harian')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.reports.index') }}">Laporan</a> <span>/</span> 
      <span>Harian</span>
    </div>
    <h1>Laporan Harian: {{ $date->format('d M Y') }}</h1>
  </div>
  <div class="page-header-actions" style="display:flex;gap:10px;align-items:center;">
    <form method="GET" action="{{ route('admin.reports.daily') }}" style="display:flex;gap:8px;align-items:center;">
      <label style="font-size:13px;color:var(--text-secondary);">Pilih Tanggal:</label>
      <input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()" style="padding:6px 12px;height:auto;width:auto;">
    </form>
    <a href="{{ route('admin.reports.daily', ['date' => $date->format('Y-m-d'), 'action' => 'print']) }}" target="_blank" class="btn btn-secondary">🖨️ Cetak</a>
  </div>
</div>

{{-- Ringkasan --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:24px;">
  <div class="card" style="padding:16px;">
    <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">Total Tunai</div>
    <div style="font-size:20px;font-weight:700;color:var(--green-light);">Rp {{ number_format($totalCash, 0, ',', '.') }}</div>
  </div>
  <div class="card" style="padding:16px;">
    <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">Total Transfer</div>
    <div style="font-size:20px;font-weight:700;color:var(--blue-light);">Rp {{ number_format($totalTransfer, 0, ',', '.') }}</div>
  </div>
  <div class="card" style="padding:16px;">
    <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">Total Debit</div>
    <div style="font-size:20px;font-weight:700;color:var(--purple);">Rp {{ number_format($totalDebit, 0, ',', '.') }}</div>
  </div>
  <div class="card" style="padding:16px;background:var(--accent);color:white;border:none;">
    <div style="font-size:12px;opacity:0.8;margin-bottom:4px;">Pemasukan Hari Ini</div>
    <div style="font-size:20px;font-weight:700;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
  </div>
</div>

@php
  // Ambil data relasi saudara untuk mengelompokkan transaksi per keluarga
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
      
      // Temukan semua saudara yang terhubung dalam daftar transaksi hari ini secara rekursif/transitif
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
<div class="card">
  <div class="table-container" style="overflow-x: auto; max-width: 100%;">
    <table style="white-space: nowrap;">
      <thead>
        <tr>
          <th style="text-align:center; width:40px;">No</th>
          <th>No. Kwitansi</th>
          <th>Nama Siswa</th>
          <th style="text-align:center;">Kelas</th>
          <th>Bulan Dibayar</th>
          <th style="text-align:center;">Metode</th>
          <th style="text-align:right;">Nominal SPP</th>
          <th style="text-align:right;">Denda</th>
          <th style="text-align:right;">Nominal Bayar</th>
          <th style="text-align:right;">Nominal Transfer</th>
          <th>Hari & Tanggal</th>
          <th style="text-align:center;">Keterangan</th>
          <th style="text-align:right;">Selisih</th>
        </tr>
      </thead>
      <tbody>
        @forelse($familyClusters as $cluster)
          @php
            // Hitung total bayar gabungan untuk keluarga ini
            $clusterBayar = 0;
            $hasTransferMethod = false;
            foreach ($cluster as $cTx) {
                $cTxPaid = $cTx->paid_amount ?: $cTx->grand_total;
                $clusterBayar += $cTxPaid;
                if ($cTx->spp_method === 'transfer') {
                    $hasTransferMethod = true;
                }
            }

            // Gunakan transaksi pertama dari cluster sebagai penyimpan utama Nominal Transfer
            $firstTx = $cluster[0];
            
            // Nominal Transfer cluster: jika firstTx->transfer_nominal > 0, pakai itu. Jika 0 tapi metode transfer, pakai total clusterBayar.
            $dispTransfer = $firstTx->transfer_nominal > 0 ? $firstTx->transfer_nominal : ($hasTransferMethod ? $clusterBayar : 0);
            
            // Selisih cluster
            $calcSelisih = $dispTransfer > 0 ? abs($dispTransfer - $clusterBayar) : 0;
            $signedSelisih = $dispTransfer > 0 ? ($dispTransfer - $clusterBayar) : 0;

            // Tambahkan ke akumulator total footer SEKALI per keluarga/cluster
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
              <td style="text-align:center; font-size:12px; color:var(--text-muted);">
                {{ $globalIteration++ }}
              </td>
              <td style="font-family:monospace; font-size:12px;">
                <a href="{{ route('admin.transactions.show', $tx) }}" style="color:var(--accent); font-weight:600;">{{ $tx->receipt_number }}</a>
              </td>
              <td>
                <div style="font-weight:600; color:var(--text-primary);">{{ $tx->student?->full_name ?: '-' }}</div>
              </td>
              <td style="text-align:center;">
                <span class="badge badge-secondary">{{ $tx->student?->schoolClass?->display_name ?: '-' }}</span>
              </td>
              <td>
                <div style="display:flex; gap:3px; flex-wrap:wrap; max-width:160px;">
                    @php $mns = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']; @endphp
                    @foreach($tx->details as $d)
                      <span style="background:var(--bg-primary); padding:2px 6px; border-radius:4px; font-size:10px; font-weight:600; color:var(--text-secondary); border:1px solid var(--border);">
                        {{ $mns[$d->month] }}'{{ substr($d->year, 2) }}
                      </span>
                    @endforeach
                </div>
              </td>
              <td style="text-align:center;">
                @if($tx->spp_method === 'cash') <span class="badge badge-success">Tunai</span>
                @elseif($tx->spp_method === 'transfer') <span class="badge badge-info">Transfer</span>
                @elseif($tx->spp_method === 'debit') <span class="badge badge-purple">Debet</span>
                @else <span class="badge badge-secondary">{{ ucfirst($tx->spp_method) }}</span>
                @endif
              </td>
              <td style="text-align:right;">
                <div style="font-weight:500;">Rp {{ number_format($txSpp, 0, ',', '.') }}</div>
                @if($tx->discount_amount > 0)
                  <div style="font-size:10px; color:var(--red-light); font-weight:600; margin-top:2px;" title="Diskon / Potongan SPP">
                    -Rp {{ number_format($tx->discount_amount, 0, ',', '.') }}
                  </div>
                @endif
              </td>
              <td style="text-align:right; color:var(--red-light);">
                {{ $tx->fine_amount > 0 ? 'Rp '.number_format($tx->fine_amount, 0, ',', '.') : '-' }}
              </td>
              <td style="text-align:right; font-weight:600; color:var(--green-light);">
                Rp {{ number_format($txPaid, 0, ',', '.') }}
              </td>

              {{-- Kolom 10, 11, 12, 13 di-merge (rowspan) untuk seluruh transaksi dalam keluarga/cluster ini --}}
              @if($idx === 0)
                <td rowspan="{{ count($cluster) }}" style="vertical-align:middle; text-align:center;">
                  <input type="number" 
                         class="form-control" 
                         style="padding:4px 8px; font-size:12px; height:auto; width:110px; text-align:right; border-radius:6px; border:1px solid var(--border); background:var(--bg-primary); color:var(--text-primary); font-weight:600; margin:0 auto;"
                         value="{{ $dispTransfer > 0 ? (int)$dispTransfer : '' }}"
                         placeholder="0"
                         onchange="updateTransferNominal({{ $firstTx->id }}, this)"
                         title="Ketik Nominal Transfer (Sekaligus untuk keluarga)">
                  @if(count($cluster) > 1)
                    <div style="font-size:10px; color:var(--purple); text-align:center; margin-top:4px; font-weight:600;">Gabungan {{ count($cluster) }} Siswa</div>
                  @endif
                </td>
                <td rowspan="{{ count($cluster) }}" style="vertical-align:middle; text-align:center;">
                  <input type="date" 
                         class="form-control" 
                         style="padding:4px 8px; font-size:12px; height:auto; width:120px; text-align:center; border-radius:6px; border:1px solid var(--border); background:var(--bg-primary); color:var(--text-primary); font-weight:600; margin:0 auto; cursor:pointer;"
                         value="{{ \Carbon\Carbon::parse($firstTx->payment_date)->format('Y-m-d') }}"
                         data-method="{{ $firstTx->spp_method }}"
                         data-notes="{{ $firstTx->notes }}"
                         onchange="updateTxDate({{ $firstTx->id }}, this)"
                         title="Ubah Tanggal Bayar">
                </td>
                <td rowspan="{{ count($cluster) }}" style="vertical-align:middle; max-width:140px; overflow:hidden; text-overflow:ellipsis; text-align:center;">
                  @if($dispTransfer > 0)
                    @if($dispTransfer > $clusterBayar)
                      <span class="badge badge-purple" style="font-weight:600;">Lebih</span>
                    @elseif($dispTransfer < $clusterBayar)
                      <span class="badge badge-danger" style="background:var(--red-light); color:white; font-weight:600;">Kurang</span>
                    @else
                      <span class="badge badge-success" style="font-weight:600;">Sesuai</span>
                    @endif
                  @else
                    <span style="font-size:12px; color:var(--text-secondary);">-</span>
                  @endif
                  @if($firstTx->notes)
                    <span style="font-size:11px; color:var(--text-muted); display:block; margin-top:4px;" title="{{ $firstTx->notes }}">{{ $firstTx->notes }}</span>
                  @endif
                </td>
                <td rowspan="{{ count($cluster) }}" style="vertical-align:middle; text-align:right; font-weight:600; color:var(--text-primary);">
                  {{ $calcSelisih > 0 ? 'Rp '.number_format($calcSelisih, 0, ',', '.') : '-' }}
                </td>
              @endif
            </tr>
          @endforeach
        @empty
        <tr>
          <td colspan="13" style="text-align:center; padding:40px; color:var(--text-muted);">
            <div style="font-size:15px; font-weight:500;">Tidak ada pencatatan pada tanggal ini.</div>
            <div style="font-size:12px; margin-top:4px;">Silakan pilih tanggal lain atau tambahkan transaksi baru.</div>
          </td>
        </tr>
        @endforelse
      </tbody>
      @if($transactions->count() > 0)
      <tfoot>
        <tr style="background:var(--bg-primary); font-weight:bold; border-top:2px solid var(--border);">
          <td colspan="6" style="text-align:right; padding-right:16px; color:var(--text-primary);">TOTAL PEMASUKAN</td>
          <td style="text-align:right;">
            <div style="color:var(--text-primary);">Rp {{ number_format($sumSpp, 0, ',', '.') }}</div>
            @if($sumDiscount > 0)
              <div style="font-size:10px; color:var(--red-light); margin-top:2px;" title="Total Diskon">-Rp {{ number_format($sumDiscount, 0, ',', '.') }}</div>
            @endif
          </td>
          <td style="text-align:right; color:var(--red-light);">Rp {{ number_format($sumFine, 0, ',', '.') }}</td>
          <td style="text-align:right; color:var(--green-light);">Rp {{ number_format($sumBayar, 0, ',', '.') }}</td>
          <td style="text-align:right; color:var(--blue-light);">Rp {{ number_format($sumTransfer, 0, ',', '.') }}</td>
          <td colspan="2"></td>
          <td style="text-align:right; color:var(--purple);">{{ $sumSelisih < 0 ? '-Rp ' . number_format(abs($sumSelisih), 0, ',', '.') : 'Rp ' . number_format($sumSelisih, 0, ',', '.') }}</td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>

@push('scripts')
<script>
function updateTxDate(txId, inputEl) {
  const newDate = inputEl.value;
  if (!newDate) return;

  const method = inputEl.dataset.method || 'cash';
  const notes = inputEl.dataset.notes || '';

  inputEl.disabled = true;

  fetch(`/admin/transactions/${txId}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
      _method: 'PUT',
      payment_date: newDate,
      spp_method: method,
      notes: notes
    })
  })
  .then(res => res.json())
  .then(data => {
    inputEl.disabled = false;
    if (data.success) {
      window.location.reload();
    } else {
      alert(data.message || 'Gagal memperbarui tanggal.');
    }
  })
  .catch(err => {
    inputEl.disabled = false;
    alert('Terjadi kesalahan koneksi.');
    console.error(err);
  });
}

function updateTransferNominal(txId, inputEl) {
  const nominal = inputEl.value;
  inputEl.disabled = true;

  fetch(`/admin/transactions/${txId}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
      _method: 'PUT',
      transfer_nominal: nominal || 0
    })
  })
  .then(res => res.json())
  .then(data => {
    inputEl.disabled = false;
    if (data.success) {
      window.location.reload();
    } else {
      alert(data.message || 'Gagal menyimpan nominal transfer.');
    }
  })
  .catch(err => {
    inputEl.disabled = false;
    alert('Terjadi kesalahan koneksi.');
    console.error(err);
  });
}
</script>
@endpush

@endsection
