@extends('admin.layouts.app')
@section('page_title', 'Tambah Setor Bank')

@section('content')
<div class="page-header">
  <div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
    <a href="{{ route('admin.bank-deposits.index') }}">Setor Bank</a> <span>/</span>
    <span>Tambah Setoran</span>
  </div>
  <h1>Pencatatan Setor Bank</h1>
</div>

<div class="card" style="max-width:800px;">
  <form action="{{ route('admin.bank-deposits.store') }}" method="POST">
    @csrf

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Tanggal Setoran <span style="color:var(--red-light);">*</span></label>
        <input type="date" name="deposit_date" class="form-control" 
               value="{{ old('deposit_date', $date->format('Y-m-d')) }}" 
               onchange="window.location.href='?date='+this.value" required>
        <small style="color:var(--text-muted);display:block;margin-top:4px;">Mengubah tanggal akan otomatis memuat ulang Total Tunai hari tersebut.</small>
      </div>
      <div class="form-group">
        <label class="form-label">Total Nominal (Rp) <span style="color:var(--red-light);">*</span></label>
        <input type="number" name="total_amount" id="totalAmount" class="form-control" 
               value="{{ old('total_amount', $totalCash) }}" 
               onkeyup="calculateSplit('total')" required>
      </div>
    </div>

    <div style="background:var(--bg-secondary);padding:20px;border-radius:10px;margin-top:20px;">
      <h3 style="font-size:14px;margin-bottom:15px;color:var(--text-secondary);">Proporsi Pembagian Rekening</h3>
      
      {{-- Rekening 1 --}}
      <div style="display:flex;gap:15px;align-items:center;margin-bottom:15px;">
        <div style="width:140px;font-weight:600;">Rekening 2040</div>
        <div style="width:120px;">
          <div class="input-group" style="display:flex;">
            <input type="number" step="0.01" name="bank_1_percentage" id="pct1" class="form-control" 
                   value="{{ old('bank_1_percentage', 50) }}" 
                   onkeyup="calculateSplit('pct1')" onchange="calculateSplit('pct1')" required>
            <span style="padding:10px;background:var(--border);border-radius:0 6px 6px 0;">%</span>
          </div>
        </div>
        <div style="flex:1;">
          <input type="hidden" name="bank_1_amount" id="amt1" value="0">
          <div id="displayAmt1" style="font-size:18px;font-weight:bold;color:var(--green-light);">Rp 0</div>
        </div>
      </div>

      {{-- Rekening 2 --}}
      <div style="display:flex;gap:15px;align-items:center;">
        <div style="width:140px;font-weight:600;">Rekening 4831</div>
        <div style="width:120px;">
          <div class="input-group" style="display:flex;">
            <input type="number" step="0.01" name="bank_2_percentage" id="pct2" class="form-control" 
                   value="{{ old('bank_2_percentage', 50) }}" 
                   onkeyup="calculateSplit('pct2')" onchange="calculateSplit('pct2')" required>
            <span style="padding:10px;background:var(--border);border-radius:0 6px 6px 0;">%</span>
          </div>
        </div>
        <div style="flex:1;">
          <input type="hidden" name="bank_2_amount" id="amt2" value="0">
          <div id="displayAmt2" style="font-size:18px;font-weight:bold;color:var(--blue-light);">Rp 0</div>
        </div>
      </div>

    </div>

    <div style="margin-top:30px;display:flex;justify-content:flex-end;gap:12px;">
      <a href="{{ route('admin.bank-deposits.index') }}" class="btn btn-secondary">Batal</a>
      <button type="submit" class="btn btn-primary">💾 Simpan Setoran</button>
    </div>
  </form>
</div>

@push('scripts')
<script>
  const totalAmount = document.getElementById('totalAmount');
  const pct1 = document.getElementById('pct1');
  const pct2 = document.getElementById('pct2');
  const amt1 = document.getElementById('amt1');
  const amt2 = document.getElementById('amt2');
  const displayAmt1 = document.getElementById('displayAmt1');
  const displayAmt2 = document.getElementById('displayAmt2');

  function calculateSplit(source) {
      let total = parseFloat(totalAmount.value) || 0;
      
      if(source === 'pct1') {
          let p1 = parseFloat(pct1.value) || 0;
          if(p1 > 100) p1 = 100;
          let p2 = 100 - p1;
          pct2.value = p2.toFixed(2);
      } else if (source === 'pct2') {
          let p2 = parseFloat(pct2.value) || 0;
          if(p2 > 100) p2 = 100;
          let p1 = 100 - p2;
          pct1.value = p1.toFixed(2);
      }

      let p1_val = parseFloat(pct1.value) || 0;
      let calculatedAmt1 = Math.round(total * (p1_val / 100));
      let calculatedAmt2 = total - calculatedAmt1;

      amt1.value = calculatedAmt1;
      amt2.value = calculatedAmt2;

      displayAmt1.innerText = formatRp(calculatedAmt1);
      displayAmt2.innerText = formatRp(calculatedAmt2);
  }

  // Initial Calculation
  calculateSplit('init');
</script>
@endpush
@endsection
