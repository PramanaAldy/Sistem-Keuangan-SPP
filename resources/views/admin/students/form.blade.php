@php
  /** @var \App\Models\Student|null $student */
  /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\AcademicYear> $academicYears */
  /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\SchoolClass> $classes */
  $student = $student ?? null;
@endphp
@extends('admin.layouts.app')
@section('page_title', $student ? 'Edit Siswa' : 'Tambah Siswa')

@section('content')
  <div class="page-header">
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.students.index') }}">Siswa</a> <span>/</span>
      <span>{{ $student ? 'Edit' : 'Tambah' }}</span>
    </div>
    <h1>{{ $student ? 'Edit Siswa' : 'Tambah Siswa Baru' }}</h1>
  </div>

  <div class="card" style="max-width:900px;">
    <form action="{{ $student ? route('admin.students.update', $student) : route('admin.students.store') }}"
      method="POST">
      @csrf
      @if($student) @method('PUT') @endif

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        {{-- Kolom Kiri --}}
        <div>
          <h3
            style="font-size:16px;margin-bottom:16px;color:var(--text-secondary);border-bottom:1px solid var(--border);padding-bottom:8px;">
            Data Pribadi</h3>

          <div class="form-group">
            <label class="form-label">NISN (Nomor Induk Siswa Nasional)</label>
            <input type="text" name="nisn" class="form-control" value="{{ old('nisn', $student?->nisn ?? '') }}"
              placeholder="Kosongkan jika ingin generate otomatis">
          </div>

          <div class="form-group">
            <label class="form-label">Nama Lengkap <span style="color:var(--red-light);">*</span></label>
            <input type="text" name="full_name" class="form-control"
              value="{{ old('full_name', $student?->full_name ?? '') }}" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Tempat Lahir</label>
              <input type="text" name="birth_place" class="form-control"
                value="{{ old('birth_place', $student?->birth_place ?? '') }}">
            </div>
            <div class="form-group">
              <label class="form-label">Tanggal Lahir <span style="color:var(--red-light);">*</span></label>
              <input type="date" name="birth_date" class="form-control"
                value="{{ old('birth_date', $student?->birth_date?->format('Y-m-d') ?? '') }}"
                required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Jenis Kelamin <span style="color:var(--red-light);">*</span></label>
            <div style="display:flex;gap:16px;margin-top:8px;">
              <label style="display:flex;align-items:center;gap:6px;"><input type="radio" name="gender" value="L" {{ old('gender', $student?->gender ?? '') == 'L' ? 'checked' : '' }} required> Laki-laki</label>
              <label style="display:flex;align-items:center;gap:6px;"><input type="radio" name="gender" value="P" {{ old('gender', $student?->gender ?? '') == 'P' ? 'checked' : '' }} required> Perempuan</label>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Agama</label>
            <select name="religion" class="form-control form-select">
              <option value="">-- Pilih Agama --</option>
              <option value="Islam" {{ old('religion', $student?->religion ?? '') == 'Islam' ? 'selected' : '' }}>Islam</option>
              <option value="Kristen" {{ old('religion', $student?->religion ?? '') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
              <option value="Katolik" {{ old('religion', $student?->religion ?? '') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
              <option value="Hindu" {{ old('religion', $student?->religion ?? '') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
              <option value="Buddha" {{ old('religion', $student?->religion ?? '') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
              <option value="Konghucu" {{ old('religion', $student?->religion ?? '') == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
            </select>
          </div>
        </div>

        {{-- Kolom Kanan --}}
        <div>
          <h3
            style="font-size:16px;margin-bottom:16px;color:var(--text-secondary);border-bottom:1px solid var(--border);padding-bottom:8px;">
            Data Akademik & Keluarga</h3>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Tahun Ajaran <span style="color:var(--red-light);">*</span></label>
              <select name="academic_year_id" class="form-control form-select" required>
                @foreach($academicYears as $year)
                  <option value="{{ $year->id }}" {{ old('academic_year_id', $student?->academic_year_id ?? '') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Kelas <span style="color:var(--red-light);">*</span></label>
              <select name="class_id" class="form-control form-select" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach($classes as $c)
                  <option value="{{ $c->id }}" {{ old('class_id', $student?->class_id ?? '') == $c->id ? 'selected' : '' }}>
                    {{ $c->display_name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Status Siswa <span style="color:var(--red-light);">*</span></label>
            <select name="status" class="form-control form-select" required>
              <option value="active" {{ old('status', $student?->status ?? '') == 'active' ? 'selected' : '' }}>Aktif
              </option>
              <option value="inactive" {{ old('status', $student?->status ?? '') == 'inactive' ? 'selected' : '' }}>Nonaktif
                / Pindah</option>
              <option value="alumni" {{ old('status', $student?->status ?? '') == 'alumni' ? 'selected' : '' }}>Alumni
              </option>
              <option value="cuti" {{ old('status', $student?->status ?? '') == 'cuti' ? 'selected' : '' }}>Cuti
              </option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Nama Orang Tua / Wali</label>
            <input type="text" name="parent_name" class="form-control"
              value="{{ old('parent_name', $student?->parent_name ?? '') }}">
          </div>

          <div class="form-group">
            <label class="form-label">Nomor WhatsApp Orang Tua (Format: 08...)</label>
            <input type="text" name="whatsapp" class="form-control"
              value="{{ old('whatsapp', $student?->whatsapp ?? '') }}" placeholder="08123456789">
          </div>

          <div class="form-group">
            <label class="form-label">Alamat Lengkap</label>
            <textarea name="address" class="form-control"
              rows="2">{{ old('address', $student?->address ?? '') }}</textarea>
          </div>
        </div>

      </div>

      {{-- Pengaturan Biaya --}}
      <div style="margin-top:24px;">
        <h3
          style="font-size:16px;margin-bottom:16px;color:var(--text-secondary);border-bottom:1px solid var(--border);padding-bottom:8px;">
          Pengaturan Tagihan / Keringanan Biaya</h3>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
          <div style="background:var(--bg-secondary);padding:16px;border-radius:10px;border:1px solid var(--border);">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
              <input type="hidden" name="is_free_spp" value="0">
              <input type="checkbox" name="is_free_spp" value="1" {{ old('is_free_spp', $student?->is_free_spp ?? false) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:var(--accent);">
              <div>
                <div style="font-weight:600;">Siswa Gratis SPP</div>
                <div style="font-size:12px;color:var(--text-muted);">Siswa ini tidak akan dikenakan tagihan SPP bulanan
                  sama sekali.</div>
              </div>
            </label>
          </div>
          <div style="background:var(--bg-secondary);padding:16px;border-radius:10px;border:1px solid var(--border);">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
              <input type="hidden" name="is_free_fine" value="0">
              <input type="checkbox" name="is_free_fine" value="1" {{ old('is_free_fine', $student?->is_free_fine ?? false) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:var(--accent);">
              <div>
                <div style="font-weight:600;">Siswa Bebas Denda</div>
                <div style="font-size:12px;color:var(--text-muted);">Siswa ini tidak akan dikenakan denda jika terlambat
                  membayar SPP.</div>
              </div>
            </label>
          </div>
        </div>
      </div>

      <div style="margin-top:24px;display:flex;gap:12px;justify-content:flex-end;">
        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit"
          class="btn btn-primary">{{ $student ? '💾 Simpan Perubahan' : '💾 Simpan Siswa Baru' }}</button>
      </div>
    </form>
  </div>
@endsection