@extends('admin.layouts.app')
@section('page_title', 'Transaksi SPP')
@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> Transaksi</div>
    <h1>Transaksi SPP</h1>
    <p>Grid pembayaran SPP seluruh siswa</p>
  </div>
  <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    Input Transaksi
  </a>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:16px;">
  <form method="GET">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
      <div style="flex:1;min-width:200px;">
        <label class="form-label">Cari Siswa</label>
        <input type="text" name="search" class="form-control" placeholder="Nama siswa..." value="{{ request('search') }}">
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
          @foreach($academicYears as $yr)
            <option value="{{ $yr->id }}" {{ $selectedYearId == $yr->id ? 'selected' : '' }}>{{ $yr->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
        <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">Reset</a>
      </div>
    </div>
  </form>
</div>

{{-- Payment Grid --}}
<div class="card">
  <div style="overflow-x:auto;">
    <table style="min-width:900px;">
      <thead>
        <tr>
          <th style="min-width:180px;">Siswa</th>
          <th style="min-width:100px;">Kelas</th>
          @php 
            $mns = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']; 
            $sequence = [7,8,9,10,11,12,1,2,3,4,5,6];
          @endphp
          @foreach($sequence as $m)<th style="text-align:center;min-width:58px;font-size:11px;text-transform:uppercase;">{{ $mns[$m-1] }}</th>@endforeach
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($students as $student)
        @php
          $mnNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
          $paidData = [];
          foreach($student->transactions as $tx) {
            foreach($tx->details as $d) {
              $paidData[$d->month] = [
                'date'       => $tx->payment_date->format('d/m'),
                'tx_id'      => $tx->id,
                'detail_id'  => $d->id,
                'month_name' => $mnNames[$d->month],
              ];
            }
          }
        @endphp
        <tr>
          <td>
            <div class="student-name">{{ $student->full_name }}</div>
            <div class="student-meta">{{ $student->nisn ?? 'NISN -' }}</div>
          </td>
          <td><span class="badge badge-info">{{ $student->schoolClass?->display_name }}</span></td>

          @foreach($sequence as $m)
          <td style="text-align:center;padding:4px 3px;position:relative;">
            @if(isset($paidData[$m]))
              @php $pd = $paidData[$m]; @endphp
              <div style="display:flex;flex-direction:column;gap:2px;align-items:center;">

                {{-- Badge Lunas / Cetak Kwitansi --}}
                <a href="{{ route('admin.transactions.receipt', $pd['tx_id']) }}" target="_blank"
                   title="Cetak Kwitansi (Tgl Bayar: {{ $pd['date'] }})"
                   style="display:block;text-decoration:none;background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.4);border-radius:4px;padding:3px 5px;font-size:10px;color:var(--green-light);cursor:pointer;transition:all 0.2s;width:100%;"
                   onmouseover="this.style.background='var(--green-light)';this.style.color='#fff';"
                   onmouseout="this.style.background='rgba(16,185,129,0.2)';this.style.color='var(--green-light)';">
                  ✓<br><span style="font-size:9px;opacity:0.9;color:inherit;">{{ $pd['date'] }}</span>
                </a>

                {{-- Tombol Dropdown Hapus --}}
                <div style="position:relative;width:100%;" class="del-wrap">
                  <button type="button"
                          onclick="toggleDelMenu(this)"
                          style="width:100%;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);border-radius:4px;padding:2px 4px;font-size:9px;color:#ef4444;cursor:pointer;transition:all 0.2s;line-height:1.5;"
                          onmouseover="this.style.background='rgba(239,68,68,0.22)'"
                          onmouseout="this.style.background='rgba(239,68,68,0.12)'">
                    🗑 ▾
                  </button>

                  {{-- Dropdown Menu --}}
                  <div class="del-menu"
                       style="display:none;position:absolute;bottom:calc(100% + 5px);left:50%;transform:translateX(-50%);z-index:1000;background:var(--bg-card);border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.5);min-width:190px;overflow:hidden;">

                    {{-- Opsi 1: Hapus bulan ini saja --}}
                    <form method="POST"
                          action="{{ route('admin.transactions.detail.destroy', $pd['detail_id']) }}"
                          onsubmit="return confirm('Hapus bulan {{ $pd['month_name'] }} saja dari kwitansi ini?\n\nSiswa: {{ addslashes($student->full_name) }}')">
                      @csrf @method('DELETE')
                      <button type="submit"
                              style="width:100%;padding:10px 14px;text-align:left;background:none;border:none;border-bottom:1px solid var(--border);font-size:12px;cursor:pointer;color:var(--text);"
                              onmouseover="this.style.background='rgba(239,68,68,0.08)'"
                              onmouseout="this.style.background='none'">
                        🗓 &nbsp;Hapus bulan ini saja
                        <div style="font-size:10px;color:var(--text-muted);margin-top:1px;">{{ $pd['month_name'] }} saja</div>
                      </button>
                    </form>

                    {{-- Opsi 2: Hapus seluruh kwitansi --}}
                    <form method="POST"
                          action="{{ route('admin.transactions.destroy', $pd['tx_id']) }}"
                          onsubmit="return confirm('Hapus SELURUH kwitansi ini?\n(Semua bulan dalam 1 nota akan terhapus)\n\nSiswa: {{ addslashes($student->full_name) }}')">
                      @csrf @method('DELETE')
                      <button type="submit"
                              style="width:100%;padding:10px 14px;text-align:left;background:none;border:none;font-size:12px;cursor:pointer;color:#ef4444;"
                              onmouseover="this.style.background='rgba(239,68,68,0.08)'"
                              onmouseout="this.style.background='none'">
                        🗑 &nbsp;Hapus seluruh kwitansi
                        <div style="font-size:10px;color:var(--text-muted);margin-top:1px;">Semua bulan dalam nota</div>
                      </button>
                    </form>

                  </div>{{-- /del-menu --}}
                </div>{{-- /del-wrap --}}

              </div>
            @else
              <div style="color:var(--border-light);font-size:14px;text-align:center;">—</div>
            @endif
          </td>
          @endforeach

          <td style="white-space:nowrap;">
            <a href="{{ route('admin.transactions.create', ['student_id' => $student->id]) }}"
               class="btn btn-sm btn-primary" title="Input Transaksi Baru">
              ➕ Input
            </a>
          </td>
        </tr>
        @empty
        <tr><td colspan="15">
          <div class="empty-state"><div class="empty-icon">📊</div><p>Belum ada data.</p></div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($students->hasPages())
    <div style="padding:16px;border-top:1px solid var(--border);">{{ $students->links('pagination') }}</div>
  @endif
</div>
@endsection

@push('scripts')
<script>
/**
 * Toggle dropdown menu hapus.
 * Menutup semua menu lain sebelum membuka yang diklik.
 */
function toggleDelMenu(btn) {
  const wrap = btn.closest('.del-wrap');
  const menu = wrap.querySelector('.del-menu');
  const isOpen = menu.style.display === 'block';

  // Tutup semua menu yang terbuka
  document.querySelectorAll('.del-menu').forEach(m => m.style.display = 'none');

  // Buka menu ini jika sebelumnya tertutup
  if (!isOpen) {
    menu.style.display = 'block';
    // Cek apakah terlalu ke kiri/kanan layar dan koreksi posisi
    const rect = menu.getBoundingClientRect();
    if (rect.left < 0) menu.style.left = '0';
    if (rect.right > window.innerWidth) menu.style.left = 'auto';
  }
}

// Klik di luar → tutup semua menu
document.addEventListener('click', function(e) {
  if (!e.target.closest('.del-wrap')) {
    document.querySelectorAll('.del-menu').forEach(m => m.style.display = 'none');
  }
});
</script>
@endpush
