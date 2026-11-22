@extends('admin.layouts.app')
@section('page_title', 'Cek Saudara')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <span>Cek Saudara</span>
    </div>
    <h1>Cek Saudara & Rekap Keluarga</h1>
    <p>Lihat total pembayaran, rincian bulan, dan tunggakan per grup keluarga/siswa.</p>
  </div>
</div>



{{-- Filter Form --}}
<div class="card" style="margin-bottom:24px;">
  <form method="GET" action="{{ route('admin.sibling-check.index') }}">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
      <div style="flex:1;min-width:200px;">
        <label class="form-label">Cari Siswa</label>
        <input type="text" name="search" class="form-control" placeholder="Nama atau NISN..." value="{{ request('search') }}">
      </div>
      <div style="min-width:160px;">
        <label class="form-label">Kelas</label>
        <select name="class_id" class="form-control form-select">
          <option value="">Semua Kelas</option>
          @foreach($classes as $cls)
            <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->display_name }}</option>
          @endforeach
        </select>
      </div>
      <div style="min-width:160px;">
        <label class="form-label">Tahun Ajaran</label>
        <select name="academic_year_id" class="form-control form-select">
          <option value="">Pilih Tahun Ajaran</option>
          @foreach($academicYears as $yr)
            <option value="{{ $yr->id }}" {{ request('academic_year_id', $selectedYearId) == $yr->id ? 'selected' : '' }}>{{ $yr->name }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:flex;gap:8px;">
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
        <a href="{{ route('admin.sibling-check.index') }}" class="btn btn-secondary">Reset</a>
      </div>
    </div>
  </form>
</div>

{{-- Family Cards --}}
@forelse($paginatedGroups as $group)
  @php
    $groupPaid = $group->sum('total_paid_amount');
    $groupArrears = $group->sum('total_arrear_amount');
    $parentName = $group->first()->parent_name;
  @endphp
  <div class="card" style="margin-bottom: 24px; transition: transform 0.2s, box-shadow 0.2s; border: 1px solid var(--border);">
    
    {{-- Card Header --}}
    <div style="padding-bottom: 16px; border-bottom: 1px solid var(--border); margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
      <div>
        <h3 style="font-size: 16px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
          👨‍👩‍👧 Keluarga / Grup Saudara
          <span class="badge badge-purple" style="font-size: 11px;">{{ $group->count() }} Anak</span>
        </h3>
        @if($parentName)
          <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">Orang Tua/Wali: <strong>{{ $parentName }}</strong></div>
        @endif
      </div>
      <div style="text-align: right;">
        <div style="font-size: 12px; color: var(--text-muted);">Total Tunggakan Keluarga</div>
        <div style="font-size: 18px; font-weight: 800; color: #f59e0b;">Rp {{ number_format($groupArrears, 0, ',', '.') }}</div>
      </div>
    </div>

    {{-- Siblings Grid --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
      @foreach($group as $student)
        <div style="background: var(--bg-hover); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-light);">
          
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <div>
              <div style="font-weight: 700; font-size: 15px; color: var(--text-primary);">
                <a href="{{ route('admin.students.show', $student) }}" style="text-decoration: none; color: inherit;">
                  {{ $student->full_name }}
                </a>
              </div>
              <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                NISN: {{ $student->nisn ?? '-' }} • {{ $student->schoolClass?->display_name }}
              </div>
            </div>
            <div>
              @if($student->is_free_spp) <span class="badge badge-success">Gratis SPP</span> @endif
            </div>
          </div>
          
          {{-- Month Grid --}}
          <div style="margin-bottom: 16px;">
            <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 6px; font-weight: 600;">Status Pembayaran SPP</div>
            <div class="month-grid">
              @foreach($monthSequence as $s)
                @php 
                  $key = $s['month'].'-'.$s['year'];
                  $status = $student->month_statuses[$key] ?? 'future';
                  $cellClass = '';
                  if ($status === 'paid') $cellClass = 'paid';
                  elseif ($status === 'arrear') $cellClass = 'arrear';
                @endphp
                <div class="month-cell {{ $cellClass }}" 
                     title="{{ $monthNames[$s['month']] }} {{ $s['year'] }} - {{ ucfirst($status) }}"
                     style="{{ $status === 'arrear' ? 'background: rgba(245,158,11,0.15); border-color: rgba(245,158,11,0.4); color: #d97706;' : '' }}">
                  {{ $monthNames[$s['month']] }}
                </div>
              @endforeach
            </div>
          </div>
          
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
            <div>
              <div style="font-size: 11px; color: var(--text-muted);">Total Bayar</div>
              <div style="font-weight: 700; font-size: 14px; color: var(--green-light);">Rp {{ number_format($student->total_paid_amount, 0, ',', '.') }}</div>
            </div>
            <a href="{{ route('admin.transactions.create', ['student_id' => $student->id, 'academic_year_id' => $selectedYearId]) }}" 
               class="btn btn-primary btn-sm">
               💳 Bayar SPP
            </a>
          </div>
          
        </div>
      @endforeach
    </div>
    
  </div>
@empty
  <div class="empty-state">
    <div class="empty-icon">👨‍👩‍👧</div>
    <p>Tidak ada data siswa / grup saudara yang ditemukan.</p>
  </div>
@endforelse

@if($paginatedGroups->hasPages())
  <div style="margin-top: 16px;">
    {{ $paginatedGroups->links('pagination::bootstrap-4') }}
  </div>
@endif

@endsection

@push('styles')
<style>
  .card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
  }
</style>
@endpush
