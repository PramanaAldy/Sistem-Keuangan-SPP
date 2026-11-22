@extends('admin.layouts.app')
@section('page_title', 'Detail Transaksi')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.transactions.index') }}">Transaksi</a> <span>/</span> 
      <span>Detail</span>
    </div>
    <h1>Kwitansi: {{ $transaction->receipt_number }}</h1>
  </div>
  <div class="page-header-actions" style="display:flex;gap:10px;">
    <button onclick="document.getElementById('receiptModal').style.display='flex'" class="btn btn-primary">🖨️ Lihat & Cetak Kwitansi</button>
    <a href="{{ route('admin.transactions.edit', $transaction) }}" class="btn btn-secondary">✏️ Edit</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
  {{-- Detail Siswa & Transaksi --}}
  <div class="card">
    <h3 style="font-size:16px;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:8px;">Informasi Transaksi</h3>
    
    <table style="width:100%;font-size:14px;">
      <tr>
        <td style="color:var(--text-muted);padding:8px 0;width:140px;">Tgl Pembayaran</td>
        <td style="font-weight:600;">: {{ $transaction->payment_date->format('d F Y') }}</td>
      </tr>
      <tr>
        <td style="color:var(--text-muted);padding:8px 0;">Siswa</td>
        <td style="font-weight:600;">: <a href="{{ route('admin.students.show', $transaction->student_id) }}" style="color:var(--accent);">{{ $transaction->student?->full_name }}</a></td>
      </tr>
      <tr>
        <td style="color:var(--text-muted);padding:8px 0;">Kelas</td>
        <td style="font-weight:600;">: {{ $transaction->student?->schoolClass?->display_name }}</td>
      </tr>
      <tr>
        <td style="color:var(--text-muted);padding:8px 0;">Tahun Ajaran</td>
        <td style="font-weight:600;">: {{ $transaction->academicYear?->name }}</td>
      </tr>
      <tr>
        <td style="color:var(--text-muted);padding:8px 0;">Kasir (Admin)</td>
        <td style="font-weight:600;">: {{ $transaction->admin?->name }}</td>
      </tr>
      <tr>
        <td style="color:var(--text-muted);padding:8px 0;">Metode SPP</td>
        <td style="font-weight:600;">: {{ strtoupper($transaction->spp_method) }}</td>
      </tr>
      @if($transaction->fine_amount > 0)
      <tr>
        <td style="color:var(--text-muted);padding:8px 0;">Metode Denda</td>
        <td style="font-weight:600;">: {{ strtoupper($transaction->fine_method ?? $transaction->spp_method) }}</td>
      </tr>
      @endif
      <tr>
        <td style="color:var(--text-muted);padding:8px 0;">Catatan Kasir</td>
        <td style="font-weight:600;">: {{ $transaction->notes ?? '-' }}</td>
      </tr>
    </table>
  </div>
  
  {{-- Rincian Biaya --}}
  <div class="card" style="background:linear-gradient(135deg,var(--bg-card),var(--bg-secondary));">
    <h3 style="font-size:16px;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:8px;">Rincian Pembayaran</h3>
    
    @php
      $mns = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
      $monthLabels = [];
      foreach($transaction->details as $d) {
        $monthLabels[] = $mns[$d->month] . ' ' . $transaction->academicYear?->name;
      }
    @endphp
    
    <div style="font-size:14px;margin-bottom:16px;">
      <div style="color:var(--text-muted);margin-bottom:4px;">Bulan SPP yang dibayar:</div>
      <div style="font-weight:600;">{{ implode(', ', $monthLabels) }}</div>
    </div>
    
    <div style="display:flex;flex-direction:column;gap:12px;font-size:14px;">
      <div style="display:flex;justify-content:space-between;">
        <span style="color:var(--text-muted);">Total SPP Murni ({{ count($transaction->details) }} bln)</span>
        <span style="font-weight:600;">Rp {{ number_format($transaction->spp_amount, 0, ',', '.') }}</span>
      </div>
      
      @if($transaction->discount_amount > 0)
      <div style="display:flex;justify-content:space-between;">
        <span style="color:var(--text-muted);">Total Diskon</span>
        <span style="font-weight:600;color:var(--red-light);">- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
      </div>
      @endif
      
      @if($transaction->fine_amount > 0)
      <div style="display:flex;justify-content:space-between;">
        <span style="color:var(--text-muted);">Total Denda Keterlambatan</span>
        <span style="font-weight:600;color:var(--warning);">+ Rp {{ number_format($transaction->fine_amount, 0, ',', '.') }}</span>
      </div>
      @endif
      
      <div style="border-top:1px dashed var(--border);margin:8px 0;"></div>
      
      <div style="display:flex;justify-content:space-between;font-size:18px;align-items:center;">
        <span style="font-weight:700;">TOTAL BAYAR</span>
        <span style="font-weight:800;color:var(--green-light);">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span>
      </div>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     RECEIPT MODAL POPUP
     ════════════════════════════════════════════════════════════== --}}
@php
  $waPhone = preg_replace('/[^0-9]/', '', $transaction->student?->whatsapp ?? '');
  if (str_starts_with($waPhone, '0')) $waPhone = '62' . substr($waPhone, 1);

  $monthListWa = implode(', ', $monthLabels);
  $waReceiptMsg = "*BUKTI PEMBAYARAN SPP UNIVERSAL SCHOOL*\n\n"
    . "Nama: *{$transaction->student?->full_name}*\n"
    . "NISN: {$transaction->student?->nisn}\n"
    . "Bulan: *{$monthListWa}*\n"
    . "Total: *Rp " . number_format($transaction->grand_total, 0, ',', '.') . "*\n"
    . "Metode: " . ucfirst($transaction->spp_method) . "\n"
    . "Tanggal: {$transaction->payment_date->format('d/m/Y')}\n\n"
    . "Terima kasih.";
    
  $waReceiptUrl = $waPhone ? 'https://wa.me/' . $waPhone . '?text=' . rawurlencode($waReceiptMsg) : null;
@endphp

<div id="receiptModal" style="
  display: {{ session('show_receipt') ? 'flex' : 'none' }};
  position:fixed;inset:0;z-index:9999;
  background:rgba(0,0,0,0.65);
  align-items:center;justify-content:center;
  padding:20px;
  backdrop-filter:blur(4px);
" onclick="if(event.target===this)this.style.display='none'">

  <div style="
    background:var(--bg-card);
    border-radius:16px;
    max-width:460px;width:100%;
    box-shadow:0 20px 60px rgba(0,0,0,0.4);
    overflow:hidden;
    position:relative;
    animation:slideUp 0.25s ease;
  ">
    {{-- Modal Header --}}
    <div style="background:linear-gradient(135deg,var(--accent),#f97316);padding:20px 24px;color:white;">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
          <div style="font-size:14px;opacity:0.85;">Kwitansi Pembayaran SPP</div>
          <div style="font-size:20px;font-weight:800;margin-top:2px;">{{ $transaction->receipt_number }}</div>
        </div>
        <button onclick="document.getElementById('receiptModal').style.display='none'"
          style="background:rgba(255,255,255,0.2);border:none;color:white;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;">✕</button>
      </div>
    </div>

    {{-- Modal Body --}}
    <div id="kwitansiBody" style="padding:24px;">
      <div style="text-align:center;border:1px dashed var(--border);border-radius:10px;padding:20px;margin-bottom:20px;background:var(--bg-secondary);">
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;font-weight:600;letter-spacing:1px;">KWITANSI RESMI</div>
        
        <div style="font-size:18px;font-weight:700;margin-bottom:4px;">{{ $transaction->student?->full_name }}</div>
        <div style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">{{ $transaction->student?->schoolClass?->display_name }} &bull; {{ $transaction->academicYear?->name }}</div>

        <div style="background:var(--bg-card);border-radius:8px;padding:12px;margin-bottom:12px;text-align:left;">
          <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px;">Bulan yang Dibayar</div>
          <div style="font-weight:600;font-size:13px;line-height:1.6;">
            @foreach($transaction->details as $d)
              <span style="background:var(--accent-glow);color:var(--accent);padding:2px 8px;border-radius:10px;font-size:12px;margin-right:4px;margin-bottom:4px;display:inline-block;">{{ $mns[$d->month] }} {{ $d->year }}</span>
            @endforeach
          </div>
        </div>

        <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">Tanggal Pembayaran</div>
        <div style="font-size:13px;font-weight:600;margin-bottom:16px;">{{ $transaction->payment_date->format('d F Y') }}</div>

        <div style="border-top:2px dashed var(--border);padding-top:16px;">
          <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">TOTAL DIBAYAR</div>
          <div style="font-size:28px;font-weight:800;color:var(--green-light);">
            Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}
          </div>
        </div>
      </div>

      <div style="font-size:11px;color:var(--text-muted);text-align:center;margin-bottom:20px;">
        Kwitansi ini sah sebagai bukti pembayaran resmi &bull; Dicetak: {{ now()->format('d/m/Y H:i') }}
      </div>

      {{-- Action Buttons --}}
      <div style="display:flex;gap:10px;">
        @if($waReceiptUrl)
        <a href="{{ $waReceiptUrl }}" target="_blank"
          style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;
                 background:#25D366;color:white;border:none;padding:12px;border-radius:10px;
                 font-weight:600;font-size:14px;text-decoration:none;cursor:pointer;">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.108.549 4.088 1.512 5.808L0 24l6.336-1.484A11.955 11.955 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 110-19.636 9.818 9.818 0 010 19.636z"/></svg>
          Kirim via WA
        </a>
        @endif
        <button onclick="printReceipt()"
          style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;
                 background:var(--accent);color:white;border:none;padding:12px;border-radius:10px;
                 font-weight:600;font-size:14px;cursor:pointer;">
          🖨️ Cetak
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
@keyframes slideUp {
  from { transform: translateY(30px); opacity: 0; }
  to   { transform: translateY(0); opacity: 1; }
}
@media print {
  body * { visibility: hidden !important; }
  #printArea, #printArea * { visibility: visible !important; }
  #printArea { position: fixed; inset: 0; padding: 20px; }
}
</style>
@endpush

@push('scripts')
<script>
function printReceipt() {
  // Open the server-side PDF receipt in a new tab for printing
  window.open('{{ route('admin.transactions.receipt', $transaction) }}', '_blank');
}
</script>
@endpush
