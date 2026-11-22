@extends('admin.layouts.app')
@section('page_title', 'Surat Tunggakan SPP')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <span>Surat Tunggakan</span>
    </div>
    <h1>Surat Tunggakan SPP</h1>
    @if($activeYear)
      <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">Tahun Ajaran Aktif: {{ $activeYear->name }}</div>
    @endif
  </div>
  <div class="page-header-actions">
    <form method="GET" action="{{ route('admin.debt-letters.index') }}">
      <select name="class_id" class="form-control form-select" style="padding:8px 16px;" onchange="this.form.submit()">
        <option value="">-- Semua Kelas --</option>
        @foreach($classes as $c)
          <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->display_name }}</option>
        @endforeach
      </select>
    </form>
  </div>
</div>

<div class="card" style="margin-bottom:24px;border-left:4px solid var(--accent);background:var(--accent-glow);">
  <div style="display:flex;align-items:center;gap:16px;">
    <div style="font-size:32px;">📄</div>
    <div>
      <div style="font-size:16px;font-weight:600;color:var(--text-primary);margin-bottom:4px;">Cetak Surat Tunggakan Formal</div>
      <div style="font-size:13px;color:var(--text-secondary);">
        Cetak surat tunggakan SPP dalam format formal yang dapat diberikan langsung kepada orang tua/wali siswa.
        Surat berisi rincian bulan tunggakan dan total tagihan yang valid.
      </div>
    </div>
  </div>
</div>

<div class="card">
  @if($students->count() > 0)
  <div style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">
    Total <strong style="color:var(--text-primary);">{{ $students->count() }}</strong> siswa dengan tunggakan.
  </div>
  @endif
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th style="width:44px;text-align:center;">No.</th>
          <th>Nama Anak</th>
          <th>Kelas</th>
          <th>Bulan Tunggakan</th>
          <th style="text-align:right;">Total Tagihan</th>
          <th style="text-align:center;width:160px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($students as $i => $s)
        <tr>
          <td style="text-align:center;font-weight:600;color:var(--text-muted);font-size:13px;">{{ $i + 1 }}</td>
          <td>
            <div style="font-weight:600;">{{ $s->full_name }}</div>
            @if($s->parent_name)
              <div style="font-size:12px;color:var(--text-muted);">Wali: {{ $s->parent_name }}</div>
            @endif
          </td>
          <td>
            <span style="background:var(--accent-glow);color:var(--accent);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
              {{ $s->schoolClass?->display_name }}
            </span>
          </td>
          <td>
            <div style="font-weight:700;color:var(--red-light);font-size:15px;">{{ count($s->arrear_months) }} Bulan</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
              @php
                $allMns = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
              @endphp
              {{ implode(', ', array_map(fn($arrear) => is_array($arrear) ? $allMns[$arrear['month']].' '.$arrear['year'] : $allMns[$arrear], $s->arrear_months)) }}
            </div>
          </td>
          <td style="text-align:right;">
            <div style="font-weight:700;font-size:15px;">Rp {{ number_format($s->grand_total, 0, ',', '.') }}</div>
            @if($s->discount_amount > 0 || $s->fine_amount > 0)
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
              SPP: Rp {{ number_format($s->total_spp, 0, ',', '.') }}
              @if($s->discount_amount > 0)<span style="color:var(--green-light);"> &minus; Diskon Rp {{ number_format($s->discount_amount, 0, ',', '.') }}</span>@endif
              @if($s->fine_amount > 0)<span style="color:var(--red-light);"> + Denda Rp {{ number_format($s->fine_amount, 0, ',', '.') }}</span>@endif
            </div>
            @endif
          </td>
          <td style="text-align:center;">
            <div style="display:flex;gap:6px;justify-content:center;">
              <a href="{{ route('admin.debt-letters.show', $s) }}" target="_blank"
                class="btn btn-sm btn-secondary" style="white-space:nowrap;">
                👁️ Lihat
              </a>
              <a href="{{ route('admin.debt-letters.show', $s) }}?print=1" target="_blank"
                class="btn btn-sm btn-primary" style="white-space:nowrap;">
                🖨️ Cetak
              </a>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);">
            @if(!$activeYear)
              <div style="font-size:32px;margin-bottom:8px;">⚙️</div>
              Tidak ada Tahun Ajaran aktif.
            @else
              <div style="font-size:32px;margin-bottom:8px;">🎉</div>
              <div style="font-weight:600;font-size:16px;margin-bottom:4px;">Semua siswa lunas!</div>
              <div style="font-size:13px;">Tidak ada siswa yang menunggak saat ini.</div>
            @endif
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
