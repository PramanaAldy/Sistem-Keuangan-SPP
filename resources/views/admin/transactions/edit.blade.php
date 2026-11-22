@extends('admin.layouts.app')
@section('page_title', 'Edit Transaksi')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.transactions.index') }}">Transaksi</a> <span>/</span> 
      <span>Edit</span>
    </div>
    <h1>Edit Transaksi: {{ $transaction->receipt_number }}</h1>
  </div>
</div>

<div class="card" style="max-width:700px;border-left:4px solid var(--warning);">
  <div style="margin-bottom:20px;font-size:13px;color:var(--text-secondary);background:var(--bg-secondary);padding:12px;border-radius:8px;">
    ℹ️ <b>Penting:</b> Anda hanya dapat mengedit tanggal pembayaran, metode bayar, dan catatan kasir. Anda <b>tidak bisa</b> mengubah nominal dan bulan yang dibayar. Jika terjadi kesalahan perhitungan, mohon hapus transaksi ini dan buat ulang.
  </div>

  <form method="POST" action="{{ route('admin.transactions.update', $transaction) }}">
    @csrf
    @method('PUT')
    
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Tanggal Pembayaran <span style="color:var(--red-light);">*</span></label>
        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', $transaction->payment_date->format('Y-m-d')) }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">Total Grand Bayar</label>
        <input type="text" class="form-control" value="Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}" readonly style="background:var(--bg-secondary);color:var(--green-light);font-weight:bold;">
      </div>
    </div>
    
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Metode Bayar SPP <span style="color:var(--red-light);">*</span></label>
        <select name="spp_method" class="form-control form-select" required>
          <option value="cash" {{ old('spp_method', $transaction->spp_method) == 'cash' ? 'selected' : '' }}>Tunai / Cash</option>
          <option value="transfer" {{ old('spp_method', $transaction->spp_method) == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
          <option value="debit" {{ old('spp_method', $transaction->spp_method) == 'debit' ? 'selected' : '' }}>Kartu Debit / Mesin EDC</option>
        </select>
      </div>
      
      @if($transaction->fine_amount > 0)
      <div class="form-group">
        <label class="form-label">Metode Bayar Denda <span style="color:var(--red-light);">*</span></label>
        <select name="fine_method" class="form-control form-select" required>
          <option value="cash" {{ old('fine_method', $transaction->fine_method) == 'cash' ? 'selected' : '' }}>Tunai / Cash</option>
          <option value="transfer" {{ old('fine_method', $transaction->fine_method) == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
          <option value="debit" {{ old('fine_method', $transaction->fine_method) == 'debit' ? 'selected' : '' }}>Kartu Debit / Mesin EDC</option>
        </select>
      </div>
      @endif
    </div>
    
    <div class="form-group">
      <label class="form-label">Catatan Kasir</label>
      <input type="text" name="notes" class="form-control" value="{{ old('notes', $transaction->notes ?? '') }}" placeholder="Opsional">
    </div>
    
    <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('admin.transactions.show', $transaction) }}" class="btn btn-secondary">Batal</a>
      <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
    </div>
  </form>
</div>
@endsection
