@extends('admin.layouts.app')
@section('page_title', isset($sppSetting) ? 'Edit Tarif SPP' : 'Atur Tarif SPP')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.spp-settings.index') }}">Setting SPP</a> <span>/</span> 
      <span>{{ isset($sppSetting) ? 'Edit' : 'Tambah' }}</span>
    </div>
    <h1>{{ isset($sppSetting) ? 'Edit Tarif SPP' : 'Atur Tarif SPP Baru' }}</h1>
  </div>
</div>

<div class="card" style="max-width:600px;">
  <form method="POST" action="{{ isset($sppSetting) ? route('admin.spp-settings.update', $sppSetting) : route('admin.spp-settings.store') }}">
    @csrf
    @if(isset($sppSetting)) @method('PUT') @endif
    
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Tahun Ajaran <span style="color:var(--red-light);">*</span></label>
        @if(isset($sppSetting))
          <input type="text" class="form-control" value="{{ $sppSetting->academicYear?->name }}" readonly style="background:var(--bg-secondary);">
          <input type="hidden" name="academic_year_id" value="{{ $sppSetting->academic_year_id }}">
        @else
          <select name="academic_year_id" class="form-control form-select" required>
            @foreach($academicYears as $y)
              <option value="{{ $y->id }}" {{ old('academic_year_id', isset($activeYear) && $y->id == $activeYear->id ? $y->id : '') == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
            @endforeach
          </select>
        @endif
      </div>
      
      <div class="form-group">
        <label class="form-label">Kelas <span style="color:var(--red-light);">*</span></label>
        @if(isset($sppSetting))
          <input type="text" class="form-control" value="{{ $sppSetting->schoolClass?->display_name }}" readonly style="background:var(--bg-secondary);">
          <input type="hidden" name="class_id" value="{{ $sppSetting->class_id }}">
        @else
          <select name="class_id" class="form-control form-select" required>
            <option value="">-- Pilih Kelas --</option>
            @foreach($classes as $c)
              <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->display_name }} ({{ $c->level }})</option>
            @endforeach
          </select>
        @endif
      </div>
    </div>
    
    <div class="form-group">
      <label class="form-label">Nominal SPP (Bulanan) <span style="color:var(--red-light);">*</span></label>
      <input type="number" name="amount" class="form-control" value="{{ old('amount', isset($sppSetting) ? (int)$sppSetting->amount : '') }}" step="1000" min="0" required placeholder="Contoh: 150000">
    </div>
    
    <div class="form-group">
      <label class="form-label">Keterangan Tambahan</label>
      <input type="text" name="description" class="form-control" value="{{ old('description', $sppSetting->description ?? '') }}">
    </div>
    
    <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('admin.spp-settings.index') }}" class="btn btn-secondary">Batal</a>
      <button type="submit" class="btn btn-primary">{{ isset($sppSetting) ? '💾 Simpan Perubahan' : '💾 Simpan' }}</button>
    </div>
  </form>
</div>
@endsection
