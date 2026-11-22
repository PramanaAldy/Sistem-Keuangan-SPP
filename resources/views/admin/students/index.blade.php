@extends('admin.layouts.app')
@section('page_title', 'Data Siswa')
@php
/** @var \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection $students */
/** @var \Illuminate\Database\Eloquent\Collection $classes */
/** @var \Illuminate\Database\Eloquent\Collection $academicYears */
/** @var \App\Models\AcademicYear|null $activeYear */
@endphp

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> <span>Siswa</span></div>
    <h1>Data Siswa</h1>
    <p>Total: <strong>{{ $students->total() }}</strong> siswa ditemukan</p>
  </div>
  <div class="page-header-actions">
    <a href="{{ route('admin.students.export') }}" class="btn btn-secondary">
      📥 Export Excel
    </a>
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Tambah Siswa
    </a>
  </div>
</div>

{{-- Filter & Search --}}
<div class="card" style="margin-bottom:16px;">
  <form method="GET" action="{{ route('admin.students.index') }}" id="filterForm">
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
      <div style="min-width:140px;">
        <label class="form-label">Status</label>
        <select name="status" class="form-control form-select">
          <option value="">Semua Status</option>
          <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
          <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
          <option value="alumni" {{ request('status') == 'alumni' ? 'selected' : '' }}>Alumni</option>
          <option value="cuti" {{ request('status') == 'cuti' ? 'selected' : '' }}>Cuti</option>
        </select>
      </div>
      <div style="min-width:160px;">
        <label class="form-label">Tahun Ajaran</label>
        <select name="academic_year_id" class="form-control form-select">
          <option value="">Aktif</option>
          @foreach($academicYears as $yr)
            <option value="{{ $yr->id }}" {{ request('academic_year_id') == $yr->id ? 'selected' : '' }}>{{ $yr->name }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:flex;gap:8px;">
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Reset</a>
      </div>
    </div>
  </form>
</div>

{{-- Import & Bulk Action --}}
<div style="display:flex;gap:10px;margin-bottom:12px;flex-wrap:wrap;align-items:center;">

  {{-- Import Panel --}}
  <div style="display:flex;align-items:center;gap:8px;background:var(--bg-secondary);border:1px solid var(--border);border-radius:10px;padding:8px 12px;flex-wrap:wrap;">
    {{-- Tombol Template --}}
    <a href="{{ route('admin.students.template') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.35);color:#10b981;font-size:13px;font-weight:600;text-decoration:none;transition:all 0.2s;"
       title="Download template Excel kosong untuk panduan import"
       onmouseover="this.style.background='rgba(16,185,129,0.22)'" onmouseout="this.style.background='rgba(16,185,129,0.12)'">
      📋 Download Template
    </a>

    <span style="color:var(--border);font-size:18px;">|</span>

    {{-- Form Import --}}
    <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data"
          style="display:flex;gap:8px;align-items:center;" id="importForm">
      @csrf
      <input type="file" name="file" accept=".xlsx,.xls,.csv" id="importFile"
             style="display:none;" onchange="updateFileName(this)">
      <label for="importFile"
             style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;background:var(--bg-card);border:1px solid var(--border);color:var(--text-secondary);font-size:13px;cursor:pointer;transition:all 0.2s;white-space:nowrap;"
             id="fileLabel">
        📁 Pilih File Excel
      </label>
      <button type="submit" class="btn btn-secondary"
              style="padding:7px 14px;font-size:13px;">
        📤 Import
      </button>
    </form>

    {{-- Info tooltip --}}
    <button type="button"
            style="background:none;border:none;cursor:pointer;padding:4px;color:var(--text-muted);"
            title="Kolom wajib: nama_lengkap, tanggal_lahir (DD/MM/YYYY), kelas (sesuai nama di sistem). Download template untuk panduan lengkap."
            onclick="document.getElementById('importInfo').style.display=document.getElementById('importInfo').style.display==='none'?'block':'none'">
      ℹ️
    </button>
  </div>

  {{-- Bulk Actions --}}
  <div id="bulkActions" style="display:none;gap:8px;" class="d-flex">
    <span id="selectedCount" style="color:var(--text-secondary);font-size:13px;align-self:center;"></span>
    <form method="POST" action="{{ route('admin.students.bulk-action') }}" id="bulkForm">
      @csrf
      <input type="hidden" name="action" id="bulkActionInput">
      <div id="bulkCheckboxes"></div>
      <div style="display:flex;gap:6px;">
        <button type="button" onclick="doBulk('activate')" class="btn btn-success btn-sm">✅ Aktif</button>
        <button type="button" onclick="doBulk('deactivate')" class="btn btn-secondary btn-sm">🚫 Nonaktif</button>
        <button type="button" onclick="doBulk('alumni')" class="btn btn-secondary btn-sm">🎓 Lulus/Alumni</button>
        <button type="button" onclick="doBulk('promote_class')" class="btn btn-primary btn-sm" title="Naik tingkat kelas dan pindah tahun ajaran aktif">⬆️ Naik Kelas &amp; Pindah Tahun</button>
        <button type="button" onclick="doBulk('update_year')" class="btn btn-primary btn-sm" title="Tetap di kelas namun pindah tahun ajaran aktif">🗓 Pindah Tahun Saja</button>
        <button type="button" onclick="openTransferModal()" class="btn btn-warning btn-sm" title="Pindahkan siswa yang dipilih ke kelas lain">🔄 Pindah Kelas</button>
        <button type="button" onclick="doBulk('delete')" class="btn btn-danger btn-sm" title="Hapus seluruh siswa yang dipilih beserta semua transaksi mereka">🗑 Hapus</button>
      </div>
    </form>
  </div>
</div>

{{-- Info panel (hidden by default) --}}
<div id="importInfo" style="display:none;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.3);border-radius:10px;padding:14px 16px;margin-bottom:12px;font-size:13px;line-height:1.9;">
  <strong style="color:var(--accent);">📌 Panduan Import Excel Siswa</strong>
  <div style="margin-top:8px;display:grid;grid-template-columns:1fr 1fr;gap:4px 24px;">
    <div>✅ <b>nama_lengkap</b> — wajib diisi</div>
    <div>✅ <b>tanggal_lahir</b> — format <code>DD/MM/YYYY</code></div>
    <div>✅ <b>kelas</b> — sesuai nama kelas di sistem</div>
    <div>⬜ <b>nisn</b> — opsional</div>
    <div>⬜ <b>jenis_kelamin</b> — <code>Laki-laki</code> atau <code>Perempuan</code></div>
    <div>⬜ <b>whatsapp</b> — format <code>628xxx</code></div>
    <div>⬜ <b>tempat_lahir</b> — opsional</div>
    <div>⬜ <b>nama_orang_tua</b> — opsional</div>
    <div>⬜ <b>alamat</b> — opsional</div>
  </div>
  <div style="margin-top:10px;color:var(--text-muted);">
    💡 Download <a href="{{ route('admin.students.template') }}" style="color:var(--accent);font-weight:600;">Template Excel</a> untuk panduan lengkap beserta contoh data dan daftar kelas yang tersedia.
  </div>
</div>


{{-- Table --}}
<div class="card">
  <div class="table-container">
    <table>
      <thead><tr>
        <th><input type="checkbox" id="selectAll" style="accent-color:var(--accent);cursor:pointer;"></th>
        <th>NISN</th>
        <th>Nama Siswa</th>
        <th>Kelas</th>
        <th>Tgl Lahir</th>
        <th>Status</th>
        <th>SPP</th>
        <th>Aksi</th>
      </tr></thead>
      <tbody>
        @forelse($students as $student)
        <tr>
          <td><input type="checkbox" class="row-check" value="{{ $student->id }}" style="accent-color:var(--accent);cursor:pointer;"></td>
          <td style="font-family:monospace;font-size:12px;color:var(--text-muted);">{{ $student->nisn ?? '-' }}</td>
          <td>
            <div class="student-name">{{ $student->full_name }}</div>
            <div class="student-meta">{{ $student->parent_name ? 'Ortu: '.$student->parent_name : '' }}</div>
          </td>
          <td><span class="badge badge-info">{{ optional($student->schoolClass)->display_name }}</span></td>
          <td style="font-size:12px;">{{ optional($student->birth_date)->format('d/m/Y') }}</td>
          <td>
            @if($student->status === 'active') <span class="badge badge-success">● Aktif</span>
            @elseif($student->status === 'alumni') <span class="badge badge-purple">🎓 Alumni</span>
            @elseif($student->status === 'cuti') <span class="badge badge-info" style="color:var(--text);border:1px solid var(--accent);">📝 Cuti</span>
            @else <span class="badge badge-secondary">Nonaktif</span> @endif
          </td>
          <td>
            @if($student->is_free_spp) <span class="badge badge-success">FREE</span>
            @else <span style="font-size:12px;color:var(--text-muted);">Normal</span> @endif
          </td>
          <td>
            <div style="display:flex;gap:4px;">
              <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-secondary" title="Lihat">👁</a>
              <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-secondary" title="Edit">✏️</a>
              <form method="POST" action="{{ route('admin.students.destroy', $student) }}"
                    onsubmit="return confirm('Hapus siswa ' + this.dataset.name + '?')"
                    data-name="{{ e($student->full_name) }}">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger" title="Hapus">🗑</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="8">
          <div class="empty-state"><div class="empty-icon">👥</div><p>Belum ada siswa. <a href="{{ route('admin.students.create') }}" style="color:var(--accent);">Tambah siswa →</a></p></div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($students->hasPages())
    <div style="padding:16px;border-top:1px solid var(--border);">
      {{ $students->links('pagination') }}
    </div>
  @endif
</div>

{{-- Transfer Class Modal --}}
<div id="transferClassModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:var(--bg-secondary);width:100%;max-width:400px;border-radius:12px;border:1px solid var(--border);display:flex;flex-direction:column;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;color:var(--text);">Pindah Kelas</h3>
      <button onclick="closeTransferModal()" style="background:transparent;border:none;color:var(--text-muted);font-size:24px;cursor:pointer;">&times;</button>
    </div>
    <div style="padding:20px;">
      <p style="margin-top:0;font-size:13px;color:var(--text-muted);">Pilih kelas tujuan untuk <strong id="transferCount"></strong> siswa terpilih:</p>
      <div style="margin-bottom:16px;">
        <label class="form-label">Kelas Tujuan</label>
        <select id="targetClassSelect" class="form-control form-select">
          <option value="">-- Pilih Kelas --</option>
          @foreach($classes as $cls)
            <option value="{{ $cls->id }}">{{ $cls->display_name }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px;">
      <button onclick="closeTransferModal()" class="btn btn-secondary">Batal</button>
      <button onclick="submitTransferClass()" class="btn btn-primary">Pindahkan</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
const selectAll = document.getElementById('selectAll');
const bulkActions = document.getElementById('bulkActions');
const selectedCount = document.getElementById('selectedCount');

function updateFileName(input) {
  const label = document.getElementById('fileLabel');
  if (input.files && input.files[0]) {
    const name = input.files[0].name;
    label.innerHTML = '📄 ' + (name.length > 28 ? name.substring(0, 25) + '...' : name);
    label.style.borderColor = 'var(--accent)';
    label.style.color = 'var(--accent)';
  } else {
    label.innerHTML = '📁 Pilih File Excel';
    label.style.borderColor = 'var(--border)';
    label.style.color = 'var(--text-secondary)';
  }
}

function updateBulkUI() {
  const checked = document.querySelectorAll('.row-check:checked');
  if (checked.length > 0) {
    bulkActions.style.display = 'flex';
    selectedCount.textContent = checked.length + ' siswa dipilih';
  } else {
    bulkActions.style.display = 'none';
  }
}

selectAll.addEventListener('change', function() {
  document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
  updateBulkUI();
});

document.querySelectorAll('.row-check').forEach(cb => {
  cb.addEventListener('change', updateBulkUI);
});

function doBulk(action) {
  const checked = document.querySelectorAll('.row-check:checked');
  if (!checked.length) return;
  if (!confirm('Lakukan aksi pada ' + checked.length + ' siswa?')) return;
  document.getElementById('bulkActionInput').value = action;
  const container = document.getElementById('bulkCheckboxes');
  container.innerHTML = '';
  checked.forEach(cb => {
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'student_ids[]'; inp.value = cb.value;
    container.appendChild(inp);
  });
  document.getElementById('bulkForm').submit();
}

function openTransferModal() {
  const checked = document.querySelectorAll('.row-check:checked');
  if (!checked.length) return;
  document.getElementById('transferCount').textContent = checked.length;
  const modal = document.getElementById('transferClassModal');
  modal.style.display = 'flex';
}

function closeTransferModal() {
  document.getElementById('transferClassModal').style.display = 'none';
}

function submitTransferClass() {
  const targetClassId = document.getElementById('targetClassSelect').value;
  if (!targetClassId) {
    alert('Pilih kelas tujuan terlebih dahulu.');
    return;
  }
  
  const checked = document.querySelectorAll('.row-check:checked');
  if (!checked.length) return;
  
  if (!confirm(`Pindahkan ${checked.length} siswa ke kelas yang dipilih?`)) return;

  document.getElementById('bulkActionInput').value = 'transfer_class';
  
  const container = document.getElementById('bulkCheckboxes');
  container.innerHTML = '';
  checked.forEach(cb => {
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'student_ids[]'; inp.value = cb.value;
    container.appendChild(inp);
  });
  
  const targetInp = document.createElement('input');
  targetInp.type = 'hidden'; targetInp.name = 'target_class_id'; targetInp.value = targetClassId;
  container.appendChild(targetInp);

  document.getElementById('bulkForm').submit();
}
</script>
@endpush
