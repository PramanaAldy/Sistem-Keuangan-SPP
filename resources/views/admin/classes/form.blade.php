@extends('admin.layouts.app')
@section('page_title', isset($class) ? 'Edit Kelas' : 'Tambah Kelas')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.classes.index') }}">Kelas</a> <span>/</span> 
      <span>{{ isset($class) ? 'Edit' : 'Tambah' }}</span>
    </div>
    <h1>{{ isset($class) ? 'Edit Kelas' : 'Tambah Kelas' }}</h1>
  </div>
</div>

<div class="card" style="max-width:600px;">
  <form method="POST" action="{{ isset($class) ? route('admin.classes.update', $class) : route('admin.classes.store') }}">
    @csrf
    @if(isset($class)) @method('PUT') @endif
    
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Tingkat / Level <span style="color:var(--red-light);">*</span></label>
        <select name="level" class="form-control form-select" required>
          <option value="">-- Pilih --</option>
          @foreach($levels as $lvl)
            <option value="{{ $lvl }}" {{ old('level', $class->level ?? '') == $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Nama Raw (Murni) <span style="color:var(--red-light);">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $class->name ?? '') }}" placeholder="Contoh: 1A" required>
      </div>
    </div>
    
    <div class="form-group">
      <label class="form-label">Nama Tampilan Display <span style="color:var(--red-light);">*</span></label>
      <input type="text" name="display_name" class="form-control" value="{{ old('display_name', $class->display_name ?? '') }}" placeholder="Contoh: SD Kelas 1A" required>
    </div>
    
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Urutan Tampilan (Sort Order)</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $class->sort_order ?? 0) }}">
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Semakin kecil nilai, semakin atas di daftar kelas</div>
      </div>
      @if(isset($class))
      <div class="form-group">
        <label class="form-label">Status <span style="color:var(--red-light);">*</span></label>
        <select name="is_active" class="form-control form-select" required>
          <option value="1" {{ old('is_active', $class->is_active) ? 'selected' : '' }}>Aktif</option>
          <option value="0" {{ old('is_active', $class->is_active) ? '' : 'selected' }}>Nonaktif</option>
        </select>
      </div>
      @endif
    </div>
    
    <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">Batal</a>
      <button type="submit" class="btn btn-primary">{{ isset($class) ? '💾 Simpan Perubahan' : '💾 Simpan' }}</button>
    </div>
  </form>
</div>
@endsection
