@extends('admin.layouts.app')
@section('page_title', 'Dashboard')

@section('content')
@php
/** @var \App\Models\AcademicYear|null $activeYear */
/** @var float $totalIncomeThisMonth */
/** @var int $totalStudents */
/** @var int $totalArrearsCount */
/** @var float $totalCash */
/** @var float $totalTransfer */
/** @var float $totalDebit */
/** @var \Illuminate\Database\Eloquent\Collection $recentTransactions */
/** @var array $monthlyChart */
@endphp
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb"><span>SPP Universal School</span></div>
    <h1>Dashboard</h1>
    <p>Ringkasan sistem keuangan SPP — Tahun Ajaran {{ $activeYear?->name ?? 'Belum Diset' }}</p>
  </div>
  <div class="page-header-actions d-flex align-center gap-2">
    {{-- Indikator auto-refresh --}}
    <div id="refresh-indicator" style="font-size:12px;color:var(--text-muted,#94a3b8);display:flex;align-items:center;gap:6px;">
      <span id="refresh-dot" style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
      <span id="refresh-label">Live</span>
    </div>
    <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Input Transaksi
    </a>
  </div>
</div>

{{-- Stats --}}
<div class="stats-grid">
  <div class="stat-card accent">
    <div class="stat-icon accent">💰</div>
    <div class="stat-value accent" id="stat-income">Rp {{ number_format($totalIncomeThisMonth, 0, ',', '.') }}</div>
    <div class="stat-label">Pemasukan Bulan Ini</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon blue">👥</div>
    <div class="stat-value blue" id="stat-students">{{ $totalStudents }}</div>
    <div class="stat-label">Siswa Aktif 
      <a href="#" onclick="openStudentModal(event)" style="font-size:11px;color:var(--text-muted);text-decoration:underline;margin-left:4px;cursor:pointer;">Detail</a>
    </div>
  </div>
  <div class="stat-card red">
    <div class="stat-icon red">⚠️</div>
    <div class="stat-value red" id="stat-arrears">{{ $totalArrearsCount }}</div>
    <div class="stat-label">Siswa Menunggak Bulan Ini 
      <a href="#" onclick="openArrearsModal(event)" style="font-size:11px;color:var(--text-muted);text-decoration:underline;margin-left:4px;cursor:pointer;">Detail</a>
    </div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon green">📅</div>
    <div class="stat-value green">{{ date('Y') }}</div>
    <div class="stat-label">Tahun Berjalan</div>
  </div>
</div>

{{-- Total Pemasukan Harian --}}
<h3 style="margin-top:24px; font-size:16px; color:var(--text); border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom:16px;">Total Nominal Pemasukan Harian ({{ now()->isoFormat('D MMMM Y') }})</h3>
<div class="stats-grid" style="margin-bottom: 30px;">
  <div class="stat-card" style="border-left: 4px solid #10b981; background: var(--bg-secondary);">
    <div class="stat-icon" style="background:rgba(16,185,129,0.1);color:#10b981;">💵</div>
    <div class="stat-value" id="stat-cash" style="color:#10b981;">Rp {{ number_format($totalCash, 0, ',', '.') }}</div>
    <div class="stat-label">TOTAL TUNAI</div>
  </div>
  <div class="stat-card" style="border-left: 4px solid #3b82f6; background: var(--bg-secondary);">
    <div class="stat-icon" style="background:rgba(59,130,246,0.1);color:#3b82f6;">🏦</div>
    <div class="stat-value" id="stat-transfer" style="color:#3b82f6;">Rp {{ number_format($totalTransfer, 0, ',', '.') }}</div>
    <div class="stat-label">TOTAL TRANSFER</div>
  </div>
  <div class="stat-card" style="border-left: 4px solid #f59e0b; background: var(--bg-secondary);">
    <div class="stat-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;">💳</div>
    <div class="stat-value" id="stat-debit" style="color:#f59e0b;">Rp {{ number_format($totalDebit, 0, ',', '.') }}</div>
    <div class="stat-label">TOTAL DEBET</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:24px;" class="dashboard-grid">
  {{-- Monthly Chart Card --}}
  <div class="card">
    <div class="card-header">
      <div><div class="card-title">Pemasukan Bulanan</div><div class="card-subtitle">Tahun {{ date('Y') }}</div></div>
    </div>
    <canvas id="incomeChart" height="100"></canvas>
  </div>

  {{-- Quick Actions --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">Akses Cepat</div>
    </div>
    <div style="display:flex;flex-direction:column;gap:8px;">
      <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">💳 Input Transaksi</a>
      <a href="{{ route('admin.reports.daily') }}" class="btn btn-secondary">📊 Laporan Hari Ini</a>
      <a href="{{ route('admin.reports.arrears') }}" class="btn btn-secondary">⚠️ Laporan Tunggakan</a>
      <a href="{{ route('admin.reminders.index') }}" class="btn btn-secondary">🔔 Pengingat WA</a>
      <a href="{{ route('admin.students.create') }}" class="btn btn-secondary">👤 Tambah Siswa</a>
    </div>
  </div>
</div>

{{-- Recent Transactions --}}
<div class="card">
  <div class="card-header">
    <div class="card-title">Transaksi Terbaru</div>
    <div style="display:flex;align-items:center;gap:10px;">
      <span id="last-updated" style="font-size:11px;color:var(--text-muted,#94a3b8);"></span>
      <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary btn-sm">Lihat Semua →</a>
    </div>
  </div>
  @if($recentTransactions->isEmpty())
    <div class="empty-state" id="transactions-table-wrap">
      <div class="empty-icon">📭</div>
      <p>Belum ada transaksi. <a href="{{ route('admin.transactions.create') }}" style="color:var(--accent);">Input transaksi pertama →</a></p>
    </div>
  @else
    <div class="table-container" id="transactions-table-wrap">
      <table>
        <thead><tr>
          <th>No. Kwitansi</th><th>Siswa</th><th>Kelas</th><th>Tanggal Bayar</th><th>Bulan Dibayar</th><th>Total</th><th>Metode</th>
        </tr></thead>
        <tbody id="transactions-tbody">
          @foreach($recentTransactions as $tx)
          <tr>
            <td><span style="font-family:monospace;font-size:12px;color:var(--accent);">{{ $tx->receipt_number }}</span></td>
            <td><div class="student-name">{{ $tx->student->full_name }}</div></td>
            <td><span class="badge badge-info">{{ $tx->student->schoolClass?->display_name }}</span></td>
            <td>{{ $tx->payment_date->format('d M Y') }}</td>
            <td>
              @foreach($tx->details as $d)
                <span class="badge badge-success" style="margin:1px;">{{ ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'][$d->month] }}'{{ substr($d->year,2) }}</span>
              @endforeach
            </td>
            <td class="fw-bold text-accent">Rp {{ number_format($tx->grand_total, 0, ',', '.') }}</td>
            <td><span class="badge badge-secondary">{{ strtoupper($tx->spp_method) }}</span></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

{{-- Student Details Modal --}}
<div id="studentDetailModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:var(--bg-secondary);width:100%;max-width:700px;border-radius:12px;border:1px solid var(--border);display:flex;flex-direction:column;max-height:90vh;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;color:var(--text);">Detail Siswa Aktif</h3>
      <button onclick="closeStudentModal()" style="background:transparent;border:none;color:var(--text-muted);font-size:24px;cursor:pointer;">&times;</button>
    </div>
    
    <div style="padding:20px;overflow-y:auto;display:flex;flex-direction:column;gap:20px;">
      
      {{-- Section Siswa Bayar (Regular) --}}
      <div>
        <h4 style="margin:0 0 10px 0;font-size:14px;color:var(--green-light);display:flex;align-items:center;gap:6px;">
          <span>💳</span> 1. Siswa Bayar (Normal) - {{ count($bayarStudents) }} Siswa
        </h4>
        @if(count($bayarStudents) > 0)
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:10px;">
          @foreach($bayarStudents as $st)
            <div style="background:var(--bg-primary);padding:8px 12px;border-radius:6px;border:1px solid var(--border);font-size:12px;">
              <div style="font-weight:600;color:var(--text);">{{ $st->full_name }}</div>
              <div style="color:var(--text-muted);font-size:11px;">{{ $st->schoolClass?->display_name }}</div>
            </div>
          @endforeach
        </div>
        @else
        <div style="font-size:12px;color:var(--text-muted);">Tidak ada siswa dalam kategori ini.</div>
        @endif
      </div>
      
      <hr style="border-color:var(--border);margin:0;">

      {{-- Section Free SPP --}}
      <div>
        <h4 style="margin:0 0 10px 0;font-size:14px;color:var(--accent);display:flex;align-items:center;gap:6px;">
          <span>🆓</span> 2. Siswa Free SPP - {{ count($freeStudents) }} Siswa
        </h4>
        @if(count($freeStudents) > 0)
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:10px;">
          @foreach($freeStudents as $st)
            <div style="background:var(--bg-primary);padding:8px 12px;border-radius:6px;border:1px solid var(--border);font-size:12px;">
              <div style="font-weight:600;color:var(--text);">{{ $st->full_name }}</div>
              <div style="color:var(--text-muted);font-size:11px;">{{ $st->schoolClass?->display_name }}</div>
            </div>
          @endforeach
        </div>
        @else
        <div style="font-size:12px;color:var(--text-muted);">Tidak ada siswa dalam kategori ini.</div>
        @endif
      </div>
      
      <hr style="border-color:var(--border);margin:0;">

      {{-- Section Khusus (Diskon / Saudara) --}}
      <div>
        <h4 style="margin:0 0 10px 0;font-size:14px;color:var(--blue-light, #3b82f6);display:flex;align-items:center;gap:6px;">
          <span>🌟</span> 3. Siswa Khusus (Diskon / Bersaudara) - {{ count($khususStudents) }} Siswa
        </h4>
        @if(count($khususStudents) > 0)
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:10px;">
          @foreach($khususStudents as $st)
            <div style="background:var(--bg-primary);padding:8px 12px;border-radius:6px;border:1px solid var(--border);font-size:12px;">
              <div style="font-weight:600;color:var(--text);">{{ $st->full_name }}</div>
              <div style="color:var(--text-muted);font-size:11px;">{{ $st->schoolClass?->display_name }}</div>
            </div>
          @endforeach
        </div>
        @else
        <div style="font-size:12px;color:var(--text-muted);">Tidak ada siswa dalam kategori ini.</div>
        @endif
      </div>

    </div>
    <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;">
      <button onclick="closeStudentModal()" class="btn btn-secondary">Tutup</button>
    </div>
  </div>
</div>

{{-- Arrears Details Modal --}}
<div id="arrearsDetailModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:var(--bg-secondary);width:100%;max-width:700px;border-radius:12px;border:1px solid var(--border);display:flex;flex-direction:column;max-height:90vh;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;color:var(--text);">Detail Siswa Menunggak Bulan Ini</h3>
      <button onclick="closeArrearsModal()" style="background:transparent;border:none;color:var(--text-muted);font-size:24px;cursor:pointer;">&times;</button>
    </div>
    
    <div style="padding:20px;overflow-y:auto;display:flex;flex-direction:column;gap:20px;">
      
      <div>
        <h4 style="margin:0 0 10px 0;font-size:14px;color:var(--red-light, #ef4444);display:flex;align-items:center;gap:6px;">
          <span>⚠️</span> Daftar Siswa Menunggak - {{ count($arrearsStudents ?? []) }} Siswa
        </h4>
        @if(isset($arrearsStudents) && count($arrearsStudents) > 0)
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:10px;">
          @foreach($arrearsStudents as $st)
            <div style="background:var(--bg-primary);padding:8px 12px;border-radius:6px;border:1px solid var(--border);font-size:12px;">
              <div style="font-weight:600;color:var(--text);">{{ $st->full_name }}</div>
              <div style="color:var(--text-muted);font-size:11px;">{{ $st->schoolClass?->display_name }}</div>
            </div>
          @endforeach
        </div>
        @else
        <div style="font-size:12px;color:var(--text-muted);">Tidak ada siswa yang menunggak bulan ini.</div>
        @endif
      </div>
      
    </div>
    <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;">
      <button onclick="closeArrearsModal()" class="btn btn-secondary">Tutup</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Inisialisasi Chart ────────────────────────────────────────────────────
const ctx = document.getElementById('incomeChart').getContext('2d');
const monthlyData = @json($monthlyChart);
const monthLabels = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];

const incomeChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: monthlyData.map(d => monthLabels[d.month]),
    datasets: [{
      label: 'Pemasukan (Rp)',
      data: monthlyData.map(d => d.total),
      backgroundColor: 'rgba(245,158,11,0.2)',
      borderColor: '#f59e0b',
      borderWidth: 2,
      borderRadius: 6,
      hoverBackgroundColor: 'rgba(245,158,11,0.4)',
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: { callbacks: { label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID') } }
    },
    scales: {
      y: {
        grid: { color: 'rgba(255,255,255,0.05)' },
        ticks: { color: '#94a3b8', callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt' },
        border: { color: 'rgba(255,255,255,0.1)' }
      },
      x: {
        grid: { display: false },
        ticks: { color: '#94a3b8' },
        border: { color: 'rgba(255,255,255,0.1)' }
      }
    }
  }
});

// ── Auto-Refresh AJAX setiap 30 detik ────────────────────────────────────
const STATS_URL   = "{{ route('admin.dashboard.stats') }}";
const MONTH_NAMES = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];

function formatRupiah(n) {
  return 'Rp ' + Number(n).toLocaleString('id-ID');
}

function updateDashboard(data) {
  const el = id => document.getElementById(id);

  // Stat cards
  if (el('stat-income'))   el('stat-income').textContent   = formatRupiah(data.totalIncomeThisMonth);
  if (el('stat-students')) el('stat-students').textContent = data.totalStudents;
  if (el('stat-arrears'))  el('stat-arrears').textContent  = data.totalArrearsCount;

  // Harian
  if (el('stat-cash'))     el('stat-cash').textContent     = formatRupiah(data.totalCash);
  if (el('stat-transfer')) el('stat-transfer').textContent = formatRupiah(data.totalTransfer);
  if (el('stat-debit'))    el('stat-debit').textContent    = formatRupiah(data.totalDebit);

  // Tabel transaksi terbaru
  const tbody = el('transactions-tbody');
  if (tbody && data.recentTransactions && data.recentTransactions.length > 0) {
    tbody.innerHTML = data.recentTransactions.map(tx => `
      <tr>
        <td><span style="font-family:monospace;font-size:12px;color:var(--accent);">${tx.receipt_number}</span></td>
        <td><div class="student-name">${tx.student_name}</div></td>
        <td><span class="badge badge-info">${tx.class_name || '-'}</span></td>
        <td>${tx.payment_date}</td>
        <td>${tx.months.map(m => `<span class="badge badge-success" style="margin:1px;">${MONTH_NAMES[m]}</span>`).join('')}</td>
        <td class="fw-bold text-accent">${formatRupiah(tx.grand_total)}</td>
        <td><span class="badge badge-secondary">${tx.spp_method}</span></td>
      </tr>`).join('');
  }

  // Update chart (tanpa animasi agar tidak mengganggu)
  if (data.monthlyChart) {
    incomeChart.data.datasets[0].data = data.monthlyChart.map(d => d.total);
    incomeChart.update('none');
  }

  // Waktu terakhir diperbarui
  const now = new Date();
  const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  if (el('last-updated')) el('last-updated').textContent = `Diperbarui: ${timeStr}`;
}

function setDot(busy) {
  const dot   = document.getElementById('refresh-dot');
  const label = document.getElementById('refresh-label');
  if (!dot || !label) return;
  dot.style.background = busy ? '#f59e0b' : '#22c55e';
  label.textContent    = busy ? 'Memperbarui...' : 'Live';
}

function fetchStats() {
  setDot(true);
  fetch(STATS_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
    .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(data => { updateDashboard(data); setDot(false); })
    .catch(err => { console.warn('Auto-refresh error:', err); setDot(false); });
}

// Pertama kali 5 detik setelah load, lalu tiap 30 detik
setTimeout(fetchStats, 5000);
setInterval(fetchStats, 30000);

// Modal logic
function openStudentModal(e) {
  e.preventDefault();
  const modal = document.getElementById('studentDetailModal');
  modal.style.display = 'flex';
  // animation
  modal.style.opacity = '0';
  setTimeout(() => {
    modal.style.transition = 'opacity 0.2s';
    modal.style.opacity = '1';
  }, 10);
}

function closeStudentModal() {
  const modal = document.getElementById('studentDetailModal');
  modal.style.opacity = '0';
  setTimeout(() => {
    modal.style.display = 'none';
  }, 200);
}

// Close modal if clicked outside inner content
document.getElementById('studentDetailModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeStudentModal();
  }
});

function openArrearsModal(e) {
  e.preventDefault();
  const modal = document.getElementById('arrearsDetailModal');
  modal.style.display = 'flex';
  // animation
  modal.style.opacity = '0';
  setTimeout(() => {
    modal.style.transition = 'opacity 0.2s';
    modal.style.opacity = '1';
  }, 10);
}

function closeArrearsModal() {
  const modal = document.getElementById('arrearsDetailModal');
  modal.style.opacity = '0';
  setTimeout(() => {
    modal.style.display = 'none';
  }, 200);
}

document.getElementById('arrearsDetailModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeArrearsModal();
  }
});

</script>

<style>
/* ── Responsive Dashboard ──────────────────────────────────── */
@media (max-width: 900px) {
  .dashboard-grid { grid-template-columns: 1fr !important; }
}
@media (max-width: 640px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 400px) {
  .stats-grid { grid-template-columns: 1fr !important; }
}
/* Tabel scrollable di mobile */
.table-container {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
</style>
@endpush
