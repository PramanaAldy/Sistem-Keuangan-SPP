@extends('admin.layouts.app')
@section('page_title', 'Pengingat Tagihan SPP')

@section('content')
@php
/** @var \Illuminate\Support\Collection $groups */
/** @var \App\Models\AcademicYear|null $activeYear */
/** @var \Illuminate\Database\Eloquent\Collection $classes */
/** @var array $monthNames */
@endphp
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <span>Pengingat</span>
    </div>
    <h1>Pengingat Tagihan Pertanggal 25</h1>
    @if($activeYear)
      <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">Tahun Ajaran Aktif: {{ $activeYear->name }}</div>
    @endif
  </div>
  <div class="page-header-actions">
    <form method="GET" action="{{ route('admin.reminders.index') }}">
      <select name="class_id" class="form-control form-select" style="padding:8px 16px;" onchange="this.form.submit()">
        <option value="">-- Semua Kelas --</option>
        @foreach($classes as $c)
          <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->display_name }}</option>
        @endforeach
      </select>
    </form>
  </div>
</div>

<div class="card" style="margin-bottom:24px;border-left:4px solid #25D366;background:rgba(37,211,102,0.05);">
  <div style="display:flex;align-items:center;gap:16px;">
    <div style="font-size:32px;color:#25D366;">📱</div>
    <div>
      <div style="font-size:16px;font-weight:600;color:var(--text-primary);margin-bottom:4px;">Kirim Pengingat (Reminder) via WhatsApp</div>
      <div style="font-size:13px;color:var(--text-secondary);">
        Siswa yang memiliki <strong>nomor WA yang sama</strong> (saudara kandung) 
        digabung dalam <strong>satu baris &amp; satu pesan</strong> otomatis — sehingga orang tua hanya menerima satu pesan berisi semua tunggakan anaknya sekaligus.
      </div>
    </div>
  </div>
</div>

<div class="card">
  @if($groups->count() > 0)
  <div style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">
    Total <strong style="color:var(--text-primary);">{{ $groups->sum(fn($g) => $g['students']->count()) }}</strong> siswa.
  </div>
  @endif

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th style="width:44px;text-align:center;">No.</th>
          <th>Nama Anak</th>
          <th>Kelas</th>
          <th>Total Bulan Tunggakan</th>
          <th style="text-align:right;">Total Tagihan</th>
          <th style="text-align:center;width:130px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($groups as $i => $group)
        <tr style="{{ $group['is_sibling'] ? 'background:rgba(245,158,11,0.04);' : '' }}">

          {{-- No. --}}
          <td style="text-align:center;font-weight:600;color:var(--text-muted);font-size:13px;vertical-align:top;padding-top:14px;">
            {{ $i + 1 }}
          </td>

          {{-- Nama --}}
          <td style="vertical-align:top;">
            @if($group['is_sibling'])
              {{-- Sibling badge --}}
              <div style="display:inline-flex;align-items:center;gap:5px;background:rgba(245,158,11,0.12);color:#b45309;border-radius:10px;padding:2px 8px;font-size:11px;font-weight:700;margin-bottom:6px;">
                🔗 Saudara Kandung ({{ $group['students']->count() }} Anak)
              </div>
            @endif
            @foreach($group['students'] as $s)
              <div style="{{ !$loop->first ? 'margin-top:8px;padding-top:8px;border-top:1px dashed var(--border);' : '' }}">
                <div style="font-weight:600;">{{ $s->full_name }}</div>
                @if($group['raw_phone'])
                  <div style="font-size:12px;color:var(--text-muted);margin-top:1px;">📱 {{ $group['raw_phone'] }}</div>
                @else
                  <div style="font-size:12px;color:var(--warning);margin-top:1px;">⚠️ Belum ada No. WA</div>
                @endif
              </div>
            @endforeach
          </td>

          {{-- Kelas --}}
          <td style="vertical-align:top;">
            @foreach($group['students'] as $s)
              <div style="{{ !$loop->first ? 'margin-top:8px;padding-top:8px;' : '' }}">
                <span style="background:var(--accent-glow);color:var(--accent);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                  {{ $s->schoolClass?->display_name }}
                </span>
              </div>
            @endforeach
          </td>

          {{-- Bulan Tunggakan --}}
          <td style="vertical-align:top;">
            @foreach($group['students'] as $s)
              <div style="{{ !$loop->first ? 'margin-top:8px;padding-top:8px;border-top:1px dashed var(--border);' : '' }}">
                <div style="font-weight:700;color:var(--red-light);font-size:14px;">
                  {{ count($s->arrear_months) }} Bulan
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px;line-height:1.5;">
                  {{ implode(', ', array_map(fn($arrear) => is_array($arrear) ? $monthNames[$arrear['month']].' '.$arrear['year'] : $monthNames[$arrear], $s->arrear_months)) }}
                </div>
              </div>
            @endforeach
          </td>

          {{-- Total Tagihan --}}
          <td style="text-align:right;vertical-align:top;">
            @foreach($group['students'] as $s)
              <div style="{{ !$loop->first ? 'margin-top:8px;padding-top:8px;border-top:1px dashed var(--border);' : '' }}">
                <div style="font-weight:700;font-size:14px;">Rp {{ number_format($s->grand_total, 0, ',', '.') }}</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:1px;">
                  SPP: Rp {{ number_format($s->total_spp, 0, ',', '.') }}
                  @if($s->discount_amount > 0)<span style="color:var(--green-light);"> &minus;Rp {{ number_format($s->discount_amount, 0, ',', '.') }}</span>@endif
                  @if($s->fine_amount > 0)<span style="color:var(--red-light);"> +Rp {{ number_format($s->fine_amount, 0, ',', '.') }}</span>@endif
                </div>
              </div>
            @endforeach

            {{-- Grand total for sibling groups --}}
            @if($group['is_sibling'])
              <div style="border-top:2px solid var(--border);margin-top:8px;padding-top:8px;">
                <div style="font-size:11px;color:var(--text-muted);">Total Gabungan</div>
                <div style="font-weight:800;font-size:16px;color:var(--accent);">
                  Rp {{ number_format($group['grand_total'], 0, ',', '.') }}
                </div>
              </div>
            @endif
          </td>

          {{-- Aksi --}}
          <td style="text-align:center;vertical-align:top;padding-top:12px;">
            @if($group['wa_url'])
              <a href="{{ $group['wa_url'] }}" target="_blank"
                class="btn btn-sm btn-success"
                style="background:#25D366;border-color:#25D366;color:white;
                       display:inline-flex;align-items:center;gap:6px;white-space:nowrap;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px;">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                  <path d="M12 0C5.373 0 0 5.373 0 12c0 2.108.549 4.088 1.512 5.808L0 24l6.336-1.484A11.955 11.955 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 110-19.636 9.818 9.818 0 010 19.636z"/>
                </svg>
                @if($group['is_sibling']) Kirim Gabungan @else Kirim @endif
              </a>
            @else
              @foreach($group['students'] as $s)
                <a href="{{ route('admin.students.edit', $s) }}"
                   class="btn btn-sm btn-secondary" style="font-size:11px;margin-bottom:4px;display:block;">
                  Update WA
                </a>
              @endforeach
            @endif
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
