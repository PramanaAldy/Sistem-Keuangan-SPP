@extends('admin.layouts.app')
@section('page_title', 'Ganti Password')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> <span>Ganti Password</span></div>
    <h1>Ganti Password</h1>
    <p>Ubah password akun <strong>{{ $admin->name }}</strong></p>
  </div>
</div>

<div style="max-width:480px;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">🔐 Form Ganti Password</div>
    </div>

    <form method="POST" action="{{ route('admin.change-password.update') }}">
      @csrf @method('PUT')

      <div class="form-group">
        <label class="form-label">Password Saat Ini</label>
        <div style="position:relative;">
          <input type="password" name="current_password" id="cur_pass"
            class="form-control @error('current_password') border-danger @enderror"
            placeholder="Masukkan password saat ini" autocomplete="current-password">
          <button type="button" onclick="togglePass('cur_pass','cur_eye')"
            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:16px;" id="cur_eye">👁</button>
        </div>
        @error('current_password')
          <div class="form-error">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Password Baru</label>
        <div style="position:relative;">
          <input type="password" name="password" id="new_pass"
            class="form-control @error('password') border-danger @enderror"
            placeholder="Minimal 8 karakter" autocomplete="new-password">
          <button type="button" onclick="togglePass('new_pass','new_eye')"
            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:16px;" id="new_eye">👁</button>
        </div>
        @error('password')
          <div class="form-error">{{ $message }}</div>
        @enderror
        {{-- Strength indicator --}}
        <div style="margin-top:6px;height:4px;background:var(--border);border-radius:99px;overflow:hidden;">
          <div id="strength-bar" style="height:100%;width:0%;transition:all 0.3s;border-radius:99px;"></div>
        </div>
        <div id="strength-label" style="font-size:11px;color:var(--text-muted);margin-top:3px;"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Konfirmasi Password Baru</label>
        <div style="position:relative;">
          <input type="password" name="password_confirmation" id="conf_pass"
            class="form-control"
            placeholder="Ulangi password baru" autocomplete="new-password">
          <button type="button" onclick="togglePass('conf_pass','conf_eye')"
            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:16px;" id="conf_eye">👁</button>
        </div>
        <div id="match-label" style="font-size:11px;margin-top:3px;"></div>
      </div>

      <div style="display:flex;gap:8px;margin-top:20px;">
        <button type="submit" class="btn btn-primary">🔐 Simpan Password Baru</button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function togglePass(inputId, btnId) {
  const input = document.getElementById(inputId);
  const btn   = document.getElementById(btnId);
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = '🙈';
  } else {
    input.type = 'password';
    btn.textContent = '👁';
  }
}

// Password strength
document.getElementById('new_pass').addEventListener('input', function () {
  const val = this.value;
  const bar = document.getElementById('strength-bar');
  const lbl = document.getElementById('strength-label');

  let score = 0;
  if (val.length >= 8)  score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const levels = [
    { label: '', color: 'transparent', width: '0%' },
    { label: 'Lemah',   color: '#ef4444', width: '25%' },
    { label: 'Sedang',  color: '#f59e0b', width: '50%' },
    { label: 'Kuat',    color: '#10b981', width: '75%' },
    { label: 'Sangat Kuat', color: '#059669', width: '100%' },
  ];

  const lvl = levels[score] ?? levels[0];
  bar.style.width       = val.length ? lvl.width : '0%';
  bar.style.background  = lvl.color;
  lbl.textContent       = val.length ? lvl.label : '';
  lbl.style.color       = lvl.color;

  checkMatch();
});

// Confirm match check
document.getElementById('conf_pass').addEventListener('input', checkMatch);

function checkMatch() {
  const np  = document.getElementById('new_pass').value;
  const cp  = document.getElementById('conf_pass').value;
  const lbl = document.getElementById('match-label');
  if (!cp) { lbl.textContent = ''; return; }
  if (np === cp) {
    lbl.textContent = '✓ Password cocok';
    lbl.style.color = 'var(--green-light)';
  } else {
    lbl.textContent = '✗ Password tidak cocok';
    lbl.style.color = 'var(--red-light)';
  }
}
</script>
@endpush
