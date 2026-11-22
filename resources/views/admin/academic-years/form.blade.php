@extends('admin.layouts.app')
@section('page_title', isset($academicYear) ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.academic-years.index') }}">Tahun Ajaran</a> <span>/</span> 
      <span>{{ isset($academicYear) ? 'Edit' : 'Tambah' }}</span>
    </div>
    <h1>{{ isset($academicYear) ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran' }}</h1>
  </div>
</div>

<div class="card" style="max-width:500px;">
  <form method="POST" action="{{ isset($academicYear) ? route('admin.academic-years.update', $academicYear) : route('admin.academic-years.store') }}">
    @csrf
    @if(isset($academicYear)) @method('PUT') @endif
    
    <div class="form-group">
      <label class="form-label">Nama / Label <span style="color:var(--red-light);">*</span></label>
      <input type="text" name="name" class="form-control" value="{{ old('name', $academicYear->name ?? '') }}" placeholder="Contoh: 2024/2025" required>
    </div>
    
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Tahun Mulai <span style="color:var(--red-light);">*</span></label>
        <input type="number" name="start_year" class="form-control" value="{{ old('start_year', $academicYear->start_year ?? date('Y')) }}" min="2000" max="2099" required>
      </div>
      <div class="form-group">
        <label class="form-label">Tahun Selesai <span style="color:var(--red-light);">*</span></label>
        <input type="number" name="end_year" class="form-control" value="{{ old('end_year', $academicYear->end_year ?? date('Y')+1) }}" min="2000" max="2099" required>
      </div>
    </div>
    
    <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('admin.academic-years.index') }}" class="btn btn-secondary">Batal</a>
      <button type="submit" class="btn btn-primary">{{ isset($academicYear) ? '💾 Simpan Perubahan' : '💾 Simpan' }}</button>
    </div>
  </form>
</div>
@endsection
