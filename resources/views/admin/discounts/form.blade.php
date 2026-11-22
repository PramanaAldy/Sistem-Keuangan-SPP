@extends('admin.layouts.app')
@section('page_title', isset($discount) ? 'Edit Diskon' : 'Tambah Diskon')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.discounts.index') }}">Diskon</a> <span>/</span> 
      <span>{{ isset($discount) ? 'Edit' : 'Tambah' }}</span>
    </div>
    <h1>{{ isset($discount) ? 'Edit Master Diskon' : 'Tambah Master Diskon' }}</h1>
  </div>
</div>

<div class="card" style="max-width:600px;">
  <form method="POST" action="{{ isset($discount) ? route('admin.discounts.update', $discount) : route('admin.discounts.store') }}">
    @csrf
    @if(isset($discount)) @method('PUT') @endif
    
    <div class="form-group">
      <label class="form-label">Nama Diskon <span style="color:var(--red-light);">*</span></label>
      <input type="text" name="name" class="form-control" value="{{ old('name', $discount->name ?? '') }}" placeholder="Contoh: Diskon Anak Kedua" required>
    </div>
    
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Tipe Diskon <span style="color:var(--red-light);">*</span></label>
        <select name="type" class="form-control form-select" required>
          <option value="">-- Pilih --</option>
          @foreach($types as $key => $label)
            <option value="{{ $key }}" {{ old('type', $discount->type ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Diskon tipe Keluarga otomatis disarankan saat menambah data Saudara pada siswa.</div>
      </div>
      
      <div class="form-group">
        <label class="form-label">Format Nilai <span style="color:var(--red-light);">*</span></label>
        <select name="is_percent" class="form-control form-select" required>
          <option value="1" {{ old('is_percent', $discount->is_percent ?? false) ? 'selected' : '' }}>% (Persentase dari SPP)</option>
          <option value="0" {{ old('is_percent', $discount->is_percent ?? false) ? '' : 'selected' }}>Rp (Nominal Tetap)</option>
        </select>
      </div>
    </div>
    
    <div class="form-group">
      <label class="form-label">Besaran Nilai <span style="color:var(--red-light);">*</span></label>
      <input type="number" name="value" class="form-control" value="{{ old('value', isset($discount) ? (float)$discount->value : '') }}" step="0.01" min="0" required>
      <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Contoh isian: 50 (untuk 50%), 150000 (untuk Rp 150.000)</div>
    </div>
    
    <div class="form-group">
      <label class="form-label">Keterangan / Deskripsi</label>
      <textarea name="description" class="form-control" rows="3">{{ old('description', $discount->description ?? '') }}</textarea>
    </div>
    
    @if(isset($discount))
    <div class="form-group">
      <label class="form-label">Status <span style="color:var(--red-light);">*</span></label>
      <select name="is_active" class="form-control form-select" required>
        <option value="1" {{ old('is_active', $discount->is_active) ? 'selected' : '' }}>Aktif</option>
        <option value="0" {{ old('is_active', $discount->is_active) ? '' : 'selected' }}>Nonaktif</option>
      </select>
    </div>
    @endif
    
    <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('admin.discounts.index') }}" class="btn btn-secondary">Batal</a>
      <button type="submit" class="btn btn-primary">{{ isset($discount) ? '💾 Simpan Perubahan' : '💾 Simpan' }}</button>
    </div>
  </form>
</div>
@endsection
