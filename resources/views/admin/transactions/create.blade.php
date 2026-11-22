@extends('admin.layouts.app')
@section('page_title', 'Input Transaksi SPP')

@section('content')
<div class="page-header">
  <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> <a href="{{ route('admin.transactions.index') }}">Transaksi</a> <span>/</span> <span>Input</span></div>
  <h1>Input Transaksi SPP</h1>
</div>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:20px;align-items:start;">
  {{-- Form --}}
  <div class="card">
    <form method="POST" action="{{ route('admin.transactions.store') }}" id="txForm" enctype="multipart/form-data">
      @csrf

      <div class="form-group">
        <label class="form-label">Siswa <span style="color:var(--red-light);">*</span></label>
        <select name="student_id" id="studentSelect" class="form-control form-select" required onchange="loadStudentData()">
          <option value="">-- Pilih Siswa --</option>
          @foreach($students as $s)
            <option value="{{ $s->id }}" {{ $selectedStudent?->id == $s->id ? 'selected' : '' }}>{{ $s->full_name }} — {{ $s->schoolClass?->display_name }}</option>
          @endforeach
        </select>
      </div>

      <input type="hidden" name="academic_year_id" value="{{ $activeYear?->id }}">

      <div class="form-group">
        <label class="form-label">Tanggal Bayar <span style="color:var(--red-light);">*</span></label>
        <input type="date" name="payment_date" id="paymentDate" class="form-control" value="{{ date('Y-m-d') }}" required onchange="recalculate()">
      </div>

      {{-- Hidden Year Input --}}
      <input type="hidden" name="year" id="yearHidden" value="{{ date('Y') }}">

      <div class="form-group" id="monthGridGroup">
        <label class="form-label">Bulan yang Dibayar <span style="color:var(--red-light);">*</span></label>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;" id="monthGrid">
          @php 
            $mns = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
            $seqMonths = [7,8,9,10,11,12,1,2,3,4,5,6];
            $startYear = $activeYear->start_year;
            $endYear = $activeYear->end_year;
          @endphp
          
          @foreach($seqMonths as $m)
            @php $currentY = ($m >= 7) ? $startYear : $endYear; @endphp
            <label style="display:flex;flex-direction:column;align-items:center;gap:4px;cursor:pointer;padding:12px 8px;border-radius:8px;border:1px solid var(--border);transition:all 0.2s;position:relative;" 
                   class="month-check-label" id="mlabel{{ $m }}">
              
              <input type="checkbox" name="months[]" value="{{ $m }}" data-year="{{ $currentY }}" 
                     class="month-check" style="accent-color:var(--accent);width:18px;height:18px;" onchange="recalculate()">
              
              <span style="font-size:14px;font-weight:600;">{{ $mns[$m] }}</span>
              <span style="font-size:10px;color:var(--text-muted);">{{ $currentY }}</span>
              
              <div class="lunas-badge" id="lunas{{ $m }}" style="display:none;position:absolute;top:0;right:0;background:var(--green-light);color:white;font-size:9px;padding:2px 5px;border-radius:0 7px 0 7px;font-weight:bold;">LUNAS</div>
            </label>
          @endforeach
        </div>
      </div>

      <hr class="divider">

      {{-- Nominal --}}
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nominal SPP</label>
          <input type="text" id="displaySpp" class="form-control" readonly style="background:var(--bg-secondary);color:var(--accent);font-weight:700;" placeholder="Pilih siswa & bulan...">
        </div>
        <div class="form-group">
          <label class="form-label">Metode Bayar SPP <span style="color:var(--red-light);">*</span></label>
          <select name="spp_method" id="sppMethodSelect" class="form-control form-select" required>
            <option value="cash">💵 Tunai (Cash)</option>
            <option value="transfer">🏦 Transfer</option>
            <option value="debit">💳 Debit</option>
          </select>
        </div>
      </div>

      {{-- Discount & Fine --}}
      <div class="form-group" id="discountGroup">
        <label class="form-label" style="color:var(--green-light);">🏷️ Diskon</label>
        <input type="text" id="displayDiscount" class="form-control" readonly style="background:var(--bg-secondary);color:var(--green-light);font-weight:600;">
        <div id="discountNames" style="margin-top:4px;font-size:12px;color:var(--text-muted);"></div>
      </div>

      <div class="form-row">
        <div class="form-group" id="fineGroup">
          <label class="form-label" style="color:var(--red-light);">⚠️ Denda Keterlambatan</label>
          <input type="text" id="displayFine" class="form-control" readonly style="background:var(--bg-secondary);color:var(--red-light);font-weight:600;">
        </div>
        <div class="form-group" id="fineMethodGroup">
          <label class="form-label">Metode Bayar Denda</label>
          <select name="fine_method" class="form-control form-select">
            <option value="">-- Sama dgn SPP --</option>
            <option value="cash">💵 Tunai</option>
            <option value="transfer">🏦 Transfer</option>
            <option value="debit">💳 Debit</option>
          </select>
        </div>
      </div>

      <div id="sequentialWarning" class="alert alert-danger" style="display:none;margin-bottom:16px;padding:12px;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="sequentialWarningText" style="margin-left:8px;"></span>
      </div>

      <div style="background:var(--accent-glow);border:1px solid rgba(245,158,11,0.3);border-radius:10px;padding:16px;margin-bottom:16px;display:none;" id="grandTotalBox">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:15px;font-weight:600;">Total Bayar (Lunas)</span>
          <span id="displayTotal" style="font-size:22px;font-weight:800;color:var(--accent);">Rp 0</span>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Catatan</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Opsional..."></textarea>
      </div>

      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
          💾 Simpan Transaksi
        </button>
        <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary btn-lg">Batal</a>
      </div>
    </form>
  </div>

  {{-- Summary --}}
  <div>
    <div class="card" id="studentInfoCard" style="display:none;margin-bottom:16px;">
      <div class="card-title" style="margin-bottom:12px;">👤 Info Siswa</div>
      <div id="studentInfoContent"></div>
    </div>
    <div class="card">
      <div class="card-title" style="margin-bottom:12px;">ℹ️ Panduan</div>
      <div style="font-size:13px;color:var(--text-secondary);line-height:1.8;">
        <div>1. Pilih siswa terlebih dahulu</div>
        <div>2. Tentukan tanggal bayar</div>
        <div>3. Pilih bulan yang dibayar secara berurutan</div>
        <div>4. Sistem otomatis menghitung SPP, diskon, dan denda</div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let calcTimer = null;
let currentGrandTotal = 0;
let lastPaidMonths = [];
let academicSequence = [];

function loadStudentData() {
  const studentId = document.getElementById('studentSelect').value;
  if (!studentId) {
      document.getElementById('studentInfoCard').style.display = 'none';
      return;
  }

  // Reset checkboxes
  document.querySelectorAll('.month-check').forEach(cb => {
      cb.checked = false;
      cb.disabled = false;
      cb.parentElement.style.opacity = '1';
      cb.parentElement.style.background = '';
      cb.parentElement.style.borderColor = 'var(--border)';
      document.getElementById('lunas' + cb.value).style.display = 'none';
  });

  fetch('{{ route('admin.transactions.calculate') }}?student_id=' + studentId + '&academic_year_id={{ $activeYear?->id }}&payment_date=' + document.getElementById('paymentDate').value + '&months[]=7')
    .then(r => r.json())
    .then(data => {
      document.getElementById('studentInfoCard').style.display = 'block';
      lastPaidMonths = data.paid_months || [];
      academicSequence = data.sequence || [];
      applyLockingLogic();
    });
}

function applyLockingLogic() {
  const checkboxes = [...document.querySelectorAll('.month-check')];

  // Reset semua dulu ke state default
  checkboxes.forEach(cb => {
    cb.disabled = false;
    cb.parentElement.style.opacity = '1';
    cb.parentElement.style.cursor = 'pointer';
    cb.parentElement.style.background = '';
    const badge = document.getElementById('lunas' + cb.value);
    if (badge) badge.style.display = 'none';
  });

  // 1. Kunci bulan yang sudah LUNAS & tampilkan badge
  academicSequence.forEach((seq) => {
    const isPaid = lastPaidMonths.some(p => p.month == seq.month && p.year == seq.year);
    const cb = checkboxes.find(c => parseInt(c.value) == seq.month && c.getAttribute('data-year') == String(seq.year));
    if (cb && isPaid) {
      cb.disabled = true;
      cb.checked  = false;
      cb.parentElement.style.opacity  = '0.6';
      cb.parentElement.style.background = 'rgba(0,0,0,0.05)';
      cb.parentElement.style.cursor   = 'default';
      const badge = document.getElementById('lunas' + seq.month);
      if (badge) badge.style.display = 'block';
    }
  });

  // 2. Locking berurutan: temukan bulan belum-bayar pertama dalam sequence
  //    Buka satu-per-satu: setelah yang sebelumnya di-centang, baru buka berikutnya
  let unlockNext = true; // boleh buka bulan berikutnya selama yang sebelumnya sudah di-centang
  for (const seq of academicSequence) {
    const isPaid    = lastPaidMonths.some(p => p.month == seq.month && p.year == seq.year);
    const cb        = checkboxes.find(c => parseInt(c.value) == seq.month && c.getAttribute('data-year') == String(seq.year));
    if (!cb || isPaid) continue; // lewati yang sudah LUNAS

    if (unlockNext) {
      // Bulan ini boleh dipilih
      cb.disabled = false;
      cb.parentElement.style.opacity = '1';
      cb.parentElement.style.cursor  = 'pointer';
      // Kalau bulan ini belum di-centang, stop chain → bulan setelahnya dikunci
      if (!cb.checked) unlockNext = false;
    } else {
      // Kunci & paksa uncheck
      cb.disabled = true;
      cb.checked  = false;
      cb.parentElement.style.opacity = '0.35';
      cb.parentElement.style.cursor  = 'not-allowed';
      cb.parentElement.style.background = '';
    }
  }
}

function recalculate() {
  clearTimeout(calcTimer);
  const studentId = document.getElementById('studentSelect').value;
  if (!studentId) return;

  applyLockingLogic();

  const checkedMonths = [...document.querySelectorAll('.month-check:checked')];
  if (!checkedMonths.length) {
    document.getElementById('displaySpp').value = '';
    document.getElementById('displayDiscount').value = '';
    document.getElementById('discountNames').textContent = '';
    document.getElementById('displayFine').value = '';
    document.getElementById('displayTotal').textContent = 'Rp 0';
    document.getElementById('grandTotalBox').style.display = 'none';
    document.getElementById('submitBtn').disabled = true;
    currentGrandTotal = 0;
    return;
  }

  calcTimer = setTimeout(() => {
    const params = new URLSearchParams({
      student_id: studentId,
      academic_year_id: '{{ $activeYear?->id }}',
      payment_date: document.getElementById('paymentDate').value,
      year: checkedMonths[0].getAttribute('data-year') 
    });
    checkedMonths.forEach(m => params.append('months[]', m.value));

    fetch('{{ route('admin.transactions.calculate') }}?' + params.toString())
      .then(r => r.json())
      .then(data => {
        document.getElementById('displaySpp').value = 'Rp ' + Number(data.spp_amount).toLocaleString('id-ID');

        // Discount & Fine Display
        if(data.discount_amount > 0) {
            document.getElementById('displayDiscount').value = '- Rp ' + Number(data.discount_amount).toLocaleString('id-ID');
            document.getElementById('discountNames').textContent = data.discounts.map(d => d.name + ' (' + d.value + ')').join(', ');
        } else {
            document.getElementById('displayDiscount').value = 'Rp 0';
            document.getElementById('discountNames').textContent = 'Tidak ada diskon';
        }

        if(data.fine_amount > 0) {
            document.getElementById('displayFine').value = '+ Rp ' + Number(data.fine_amount).toLocaleString('id-ID');
        } else {
            document.getElementById('displayFine').value = 'Rp 0';
        }

        // Sequential Backup Check
        const seqWarning = document.getElementById('sequentialWarning');
        const seqText = document.getElementById('sequentialWarningText');
        if (data.unpaid_needed && data.unpaid_needed.length > 0) {
            seqWarning.style.display = 'flex';
            seqText.innerHTML = '<strong>Wajib Berurutan:</strong> Selesaikan tunggakan bulan <b>' + data.unpaid_needed.join(', ') + '</b>.';
            document.getElementById('submitBtn').disabled = true;
        } else {
            seqWarning.style.display = 'none';
            document.getElementById('submitBtn').disabled = false;
        }

        document.getElementById('grandTotalBox').style.display = 'block';
        document.getElementById('displayTotal').textContent = 'Rp ' + Number(data.grand_total).toLocaleString('id-ID');
        currentGrandTotal = data.grand_total;
        document.getElementById('yearHidden').value = checkedMonths[0].getAttribute('data-year');

        // Labels Highlight
        document.querySelectorAll('.month-check-label').forEach(lbl => {
            const cb = lbl.querySelector('input');
            lbl.style.borderColor = cb.checked ? 'var(--accent)' : 'var(--border)';
            lbl.style.background = cb.checked ? 'var(--accent-glow)' : (cb.disabled ? 'rgba(0,0,0,0.05)' : '');
        });
      });
  }, 400);
}

if (document.getElementById('studentSelect').value) loadStudentData();
</script>
@endpush
