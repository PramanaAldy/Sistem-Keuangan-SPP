@extends('admin.layouts.app')
@section('page_title', isset($fineSetting) ? 'Edit Aturan Denda' : 'Atur Denda')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.fine-settings.index') }}">Setting Denda</a> <span>/</span> 
      <span>{{ isset($fineSetting) ? 'Edit' : 'Tambah' }}</span>
    </div>
    <h1>{{ isset($fineSetting) ? 'Edit Aturan Denda' : 'Pengaturan Denda Baru' }}</h1>
  </div>
</div>

<div class="card" style="max-width:600px;">
  <form method="POST" action="{{ isset($fineSetting) ? route('admin.fine-settings.update', $fineSetting) : route('admin.fine-settings.store') }}">
    @csrf
    @if(isset($fineSetting)) @method('PUT') @endif
    
    <div class="form-group">
      <label class="form-label">Tahun Ajaran <span style="color:var(--red-light);">*</span></label>
      @if(isset($fineSetting))
        <input type="text" class="form-control" value="{{ $fineSetting->academicYear?->name }}" readonly style="background:var(--bg-secondary);">
        <input type="hidden" name="academic_year_id" value="{{ $fineSetting->academic_year_id }}">
      @else
        <select name="academic_year_id" class="form-control form-select" required>
          @foreach($academicYears as $y)
            <option value="{{ $y->id }}">{{ $y->name }}</option>
          @endforeach
        </select>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">1 Tahun Ajaran hanya bisa memiliki 1 aturan denda yang terpusat.</div>
      @endif
    </div>
    
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Tgl Jatuh Tempo SPP <span style="color:var(--red-light);">*</span></label>
        <input type="number" name="due_date" class="form-control" value="{{ old('due_date', $fineSetting->due_date ?? 10) }}" min="1" max="28" required>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Tanggal berapa tagihan SPP dianggap telat per bulannya.</div>
      </div>
      
      <div class="form-group">
        <label class="form-label">Tipe Denda <span style="color:var(--red-light);">*</span></label>
        <select name="fine_type" class="form-control form-select" required>
          <option value="flat" {{ old('fine_type', $fineSetting->fine_type ?? 'flat') == 'flat' ? 'selected' : '' }}>Flat (Hanya 1x kena denda jika telat)</option>
          <option value="daily" {{ old('fine_type', $fineSetting->fine_type ?? '') == 'daily' ? 'selected' : '' }}>Harian (Denda dihitung per hari telat)</option>
        </select>
      </div>
    </div>
    
    <div class="form-group">
      <label class="form-label">Nominal Denda <span style="color:var(--red-light);">*</span></label>
      <input type="number" name="amount" class="form-control" value="{{ old('amount', isset($fineSetting) ? (int)$fineSetting->amount : '') }}" step="1" min="0" required placeholder="Contoh: 10000">
      <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Besaran nominal denda (dalam Rupiah).</div>
    </div>
    
    <div class="form-group">
      <label class="form-label">Keterangan Tambahan (Opsional)</label>
      <input type="text" name="description" class="form-control" value="{{ old('description', $fineSetting->description ?? '') }}">
    </div>
    
    <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('admin.fine-settings.index') }}" class="btn btn-secondary">Batal</a>
      <button type="submit" class="btn btn-primary">{{ isset($fineSetting) ? '💾 Simpan Perubahan' : '💾 Simpan' }}</button>
    </div>
  </form>
</div>
@endsection
