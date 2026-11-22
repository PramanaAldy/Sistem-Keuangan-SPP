@extends('admin.layouts.app')
@section('page_title', 'Detail Siswa')

@section('content')
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  .select2-container--default .select2-selection--single {
    background-color: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: 6px;
    height: 36px;
    display: flex;
    align-items: center;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: var(--text);
    line-height: normal;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
  }
  .select2-dropdown {
    background-color: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--text);
  }
  .select2-search--dropdown .select2-search__field {
    background-color: var(--bg-primary);
    color: var(--text);
    border: 1px solid var(--border);
    border-radius: 4px;
  }
  .select2-results__option--selected {
    background-color: var(--bg-secondary) !important;
    color: var(--text) !important;
  }
  .select2-results__option--highlighted.select2-results__option--selectable {
    background-color: var(--accent) !important;
    color: #fff !important;
  }
  .select2-container--default .select2-results__option[aria-disabled=true] {
    color: var(--text-muted);
  }
</style>
@endpush
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <a href="{{ route('admin.students.index') }}">Siswa</a> <span>/</span> 
      <span>Detail</span>
    </div>
    <h1>Detail Siswa: {{ $student->full_name }}</h1>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-secondary">✏️ Edit Data</a>
    <a href="{{ route('admin.transactions.create', ['student_id' => $student->id]) }}" class="btn btn-primary">💳 Input Pembayaran</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">
  
  {{-- Kiri: Detail Siswa & Transaksi --}}
  <div style="display:flex;flex-direction:column;gap:20px;">
    
    {{-- Info Utama --}}
    <div class="card" style="background:linear-gradient(135deg, var(--bg-card), #1e2640);">
      <div style="display:flex;gap:20px;align-items:center;">
        <div style="width:80px;height:80px;background:linear-gradient(135deg,var(--accent),var(--purple));border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:36px;flex-shrink:0;">
          {{ $student->gender === 'L' ? '👦' : '👧' }}
        </div>
        <div style="flex:1;">
          <div style="font-size:24px;font-weight:700;margin-bottom:4px;">{{ $student->full_name }}</div>
          <div style="color:var(--text-secondary);font-size:14px;margin-bottom:8px;">NISN: {{ $student->nisn ?? '-' }} • {{ $student->schoolClass?->display_name }}</div>
          <div style="display:flex;gap:8px;">
            @if($student->status === 'active') <span class="badge badge-success">Aktif</span>
            @elseif($student->status === 'alumni') <span class="badge badge-purple">Alumni</span>
            @else <span class="badge badge-secondary">Nonaktif</span> @endif
            
            @if($student->is_free_spp) <span class="badge badge-success">Gratis SPP</span> @endif
            @if($student->is_free_fine) <span class="badge badge-info">Bebas Denda</span> @endif
          </div>
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:13px;">
        <div>
          <div style="color:var(--text-muted);margin-bottom:4px;">Tempat, Tgl Lahir</div>
          <div style="font-weight:500;">{{ $student->birth_place ?? '-' }}, {{ $student->birth_date?->format('d M Y') }}</div>
        </div>
        <div>
          <div style="color:var(--text-muted);margin-bottom:4px;">Agama</div>
          <div style="font-weight:500;">{{ $student->religion ?? '-' }}</div>
        </div>
        <div>
          <div style="color:var(--text-muted);margin-bottom:4px;">Tahun Ajaran</div>
          <div style="font-weight:500;">{{ $student->academicYear?->name ?? '-' }}</div>
        </div>
        <div>
          <div style="color:var(--text-muted);margin-bottom:4px;">Nama Orang Tua/Wali</div>
          <div style="font-weight:500;">{{ $student->parent_name ?? '-' }}</div>
        </div>
        <div>
          <div style="color:var(--text-muted);margin-bottom:4px;">WhatsApp</div>
          <div style="font-weight:500;">
            @if($student->whatsapp)
              <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', str_starts_with($student->whatsapp, '0') ? '62'.substr($student->whatsapp, 1) : $student->whatsapp) }}" target="_blank" style="color:var(--green-light);text-decoration:none;">📱 {{ $student->whatsapp }}</a>
            @else - @endif
          </div>
        </div>
        <div style="grid-column:1/-1;">
          <div style="color:var(--text-muted);margin-bottom:4px;">Alamat</div>
          <div style="font-weight:500;">{{ $student->address ?? '-' }}</div>
        </div>
      </div>
    </div>
    
    {{-- Riwayat Transaksi --}}
    <div class="card">
      <div class="card-header d-flex justify-between align-center">
        <h3 class="card-title">Riwayat Pembayaran</h3>
        <a href="{{ route('admin.transactions.index', ['search' => $student->nisn]) }}" style="font-size:13px;color:var(--accent);">Lihat Semua →</a>
      </div>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>No Kwitansi</th>
              <th>Bulan Dibayar</th>
              <th>Total</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($student->transactions->sortByDesc('payment_date') as $tx)
            <tr>
              <td>{{ $tx->payment_date->format('d/m/Y') }}</td>
              <td style="font-family:monospace;font-size:12px;">{{ $tx->receipt_number }}</td>
              <td>
                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                  @php $mns = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']; @endphp
                  @foreach($tx->details as $d)
                    <span style="background:var(--bg-primary);border:1px solid var(--border);padding:2px 6px;border-radius:4px;font-size:11px;">{{ $mns[$d->month] }}</span>
                  @endforeach
                </div>
              </td>
              <td style="font-weight:600;">Rp {{ number_format($tx->grand_total, 0, ',', '.') }}</td>
              <td>
                <a href="{{ route('admin.transactions.receipt', $tx) }}" target="_blank" class="btn btn-sm btn-secondary" title="Cetak Kwitansi">🖨️</a>
              </td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px;">Belum ada riwayat pembayaran.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    
  </div>
  
  {{-- Kanan: Diskon & Saudara --}}
  <div style="display:flex;flex-direction:column;gap:20px;">
    
    {{-- Diskon --}}
    <div class="card">
      <div class="card-title" style="margin-bottom:12px;">🏷️ Diskon Siswa</div>
      <div style="margin-bottom:16px;">
        @forelse($student->discounts as $disc)
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:rgba(16,185,129,0.1);border-radius:8px;margin-bottom:8px;border:1px solid rgba(16,185,129,0.2);">
            <div>
              <div style="font-weight:600;font-size:13px;color:var(--green-light);">{{ $disc->name }}</div>
              <div style="font-size:11px;color:var(--text-muted);">{{ $disc->formatted_value }} (TA: {{ $disc->pivot->academic_year_id }})</div>
            </div>
            <form method="POST" action="{{ route('admin.students.remove-discount', [$student, $disc]) }}" onsubmit="return confirm('Hapus diskon ini?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger" style="padding:4px;width:24px;height:24px;display:flex;align-items:center;justify-content:center;">×</button>
            </form>
          </div>
        @empty
          <div style="font-size:12px;color:var(--text-muted);">Tidak ada diskon aktif.</div>
        @endforelse
      </div>
      
      <form method="POST" action="{{ route('admin.students.set-discount', $student) }}" style="display:flex;flex-direction:column;gap:8px;">
        @csrf
        <div style="font-size:12px;font-weight:600;color:var(--text-secondary);">Tambah Diskon</div>
        <select name="discount_id" class="form-control form-select" required>
          <option value="">-- Pilih --</option>
          @foreach($discounts as $d)
            <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->formatted_value }})</option>
          @endforeach
        </select>
        <input type="hidden" name="academic_year_id" value="{{ $activeYear?->id }}">
        <button type="submit" class="btn btn-secondary btn-sm" style="width:100%;">+ Tambah Diskon</button>
      </form>
    </div>
    
    {{-- Saudara (Siblings) --}}
    <div class="card">
      <div class="card-title" style="margin-bottom:12px;">👨‍👩‍👧 Saudara Sekandung</div>
      <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">Penting untuk pengecekan otomatis diskon saudara.</div>
      
      <div style="margin-bottom:16px;">
        @forelse($student->siblings as $sibling)
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:var(--bg-secondary);border-radius:8px;margin-bottom:8px;">
            <div>
              <div style="font-weight:600;font-size:13px;"><a href="{{ route('admin.students.show', $sibling) }}">{{ $sibling->full_name }}</a></div>
              <div style="font-size:11px;color:var(--text-muted);">{{ $sibling->schoolClass?->display_name }}</div>
            </div>
            <form method="POST" action="{{ route('admin.students.remove-sibling', [$student, $sibling]) }}" onsubmit="return confirm('Hapus relasi saudara?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger" style="padding:4px;width:24px;height:24px;display:flex;align-items:center;justify-content:center;">×</button>
            </form>
          </div>
        @empty
          <div style="font-size:12px;color:var(--text-muted);">Belum ada data saudara.</div>
        @endforelse
      </div>
      
      <form method="POST" action="{{ route('admin.students.set-sibling', $student) }}" style="display:flex;flex-direction:column;gap:8px;">
        @csrf
        <div style="font-size:12px;font-weight:600;color:var(--text-secondary);">Tambah Saudara</div>
        <select name="sibling_id" class="form-control form-select select2-sibling" required style="width:100%;max-width:100%;">
          <option value="">-- Pilih Siswa --</option>
          @foreach($availableSiblings as $s)
            <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->schoolClass?->display_name }})</option>
          @endforeach
        </select>
        <button type="submit" class="btn btn-secondary btn-sm" style="width:100%;">+ Tambah Relasi</button>
      </form>
    </div>
    
  </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-sibling').select2({
        placeholder: "-- Pilih Siswa --",
        allowClear: true,
        width: '100%'
    });
});
</script>
@endpush
