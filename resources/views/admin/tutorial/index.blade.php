@extends('admin.layouts.app')
@section('page_title', 'Panduan Penggunaan')

@section('content')
<div class="page-header d-flex justify-between align-center flex-wrap gap-3">
  <div>
    <div class="breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> 
      <span>Tutorial</span>
    </div>
    <h1>Panduan Penggunaan Aplikasi</h1>
  </div>
</div>

<div class="card" style="margin-bottom:24px;border-left:4px solid var(--accent);background:rgba(99,102,241,0.05);">
  <div style="display:flex;align-items:center;gap:16px;">
    <div style="font-size:32px;color:var(--accent);">📘</div>
    <div>
      <div style="font-size:16px;font-weight:600;color:var(--text-primary);margin-bottom:4px;">Selamat Datang di Sistem Keuangan SPP (Update Terbaru)</div>
      <div style="font-size:13px;color:var(--text-secondary);">
        Halaman ini berisi panduan lengkap penggunaan aplikasi setelah pembaruan sistem secara struktural. Silakan pahami alur kerja berikut agar pencatatan keuangan (ledger) akurat dan meminimalisir intervensi manual.
      </div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr;gap:20px;">
  
  {{-- Modul 1: Persiapan --}}
  <div class="card">
    <h3 style="font-size:18px;color:var(--accent);margin-bottom:12px;border-bottom:1px solid var(--border);padding-bottom:8px;">1. Persiapan Data Master (Wajib Di Awal)</h3>
    <ol style="margin-left:20px;font-size:14px;color:var(--text-secondary);line-height:1.6;">
      <li><b>Tahun Ajaran:</b> Buka menu <a href="{{ route('admin.academic-years.index') }}" style="color:var(--accent);">Tahun Ajaran</a>. Buat tahun ajaran baru (contoh: 2026/2027) dengan menekan tombol "Set Aktif". Sistem <b>selalu memulai siklus tagihan dari bulan Juli</b> hingga tuntas di bulan Juni tahun berikutnya.</li>
      <li><b>Tingkat & Kelas:</b> Buka menu <a href="{{ route('admin.classes.index') }}" style="color:var(--accent);">Kelas</a>. Tambahkan semua kelas dari PG hingga SMP.</li>
      <li><b>Tarif Denda & SPP:</b> Kelola aturan tarif di "Setting Denda" & "Setting SPP". Pastikan Nominal SPP untuk tiap kelas sudah tersimpan pada Tahun Ajaran yang berstatus aktif.</li>
    </ol>
  </div>

  {{-- Modul 2: Data Siswa & Dashboard --}}
  <div class="card">
    <h3 style="font-size:18px;color:var(--accent);margin-bottom:12px;border-bottom:1px solid var(--border);padding-bottom:8px;">2. Kelola Data Siswa & Pantau Eksekutif (Dashboard)</h3>
    <ol style="margin-left:20px;font-size:14px;color:var(--text-secondary);line-height:1.6;">
      <li><b>Import NIK / Data Massal:</b> Semakin mudah mengunggah puluhan siswa sekaligus memakai fitur *Import Excel / CSV* yang ada di indeks Data Siswa.</li>
      <li><b>Relasi Spesial (Diskon/Saudara/Free):</b> Anda bisa menunjuk Status "Free SPP", mengatur Diskon khusus per individu, atau mengklik Relasi Bersaudara agar anak tercatat sebagai bagian dari KK yang sama.</li>
      <li><b>Detail Siswa Aktif:</b> Di halaman depan <a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">Dashboard</a>, pada kotak "Siswa Aktif", Anda dapat mengklik teks <b>"Detail"</b> untuk memunculkan Modal Pop-Up Segmentasi Siswa. Fitur ini secara otomatis membagi daftar: <i>Siswa Bayar (Normal)</i>, <i>Siswa Free SPP</i>, dan <i>Siswa Khusus (Diskon/Bersaudara)</i>.</li>
    </ol>
  </div>

  {{-- Modul 3: Transaksi & Pembayaran Berjenjang --}}
  <div class="card">
    <h3 style="font-size:18px;color:var(--accent);margin-bottom:12px;border-bottom:1px solid var(--border);padding-bottom:8px;">3. Melakukan Transaksi (Sistem Terkunci / Berurutan)</h3>
    <ol style="margin-left:20px;font-size:14px;color:var(--text-secondary);line-height:1.6;">
      <li><b>Pencarian Siswa Cepat:</b> Buka menu <a href="{{ route('admin.transactions.create') }}" style="color:var(--accent);">Input Transaksi</a> lalu ketikkan nama siswa tujuan.</li>
      <li><b>Aturan Pembayaran Sekuensial (Wajib Berurutan):</b> Aturan terbaru mengharuskan anak membayar tagihan secara kronologis. Contoh: Jika siswa tersebut menunggak bulan "Agustus", maka <i>checkbox</i> untuk bulan September dan seterusnya akan <b>digembok (terkunci otomatis)</b> dan sama sekali tidak bisa diklik. Mereka harus mencentang dan melunasi bulan Agustus terlebih dulu.</li>
      <li><b>Kalkulasi Tanpa Input Manual Nominal:</b> Petugas <u>tidak lagi</u> mengetikkan nominal yang ingin dibayar. Sistem mengadopsi <i>Absolut Billing</i>. Setelah bulan diceklis, sistem mengakumulasi SPP - Diskon + Denda Keterlambatan. Nilai <b>Grand Total</b> di bawah layar adalah harga mati yang harus diterima oleh Kasir (baik Tunai / Transfer). Hal ini mencegah <i>human error</i> kelebihan atau kekurangan uang kembalian.</li>
    </ol>
  </div>

  {{-- Modul 4: Laporan--}}
  <div class="card">
    <h3 style="font-size:18px;color:var(--accent);margin-bottom:12px;border-bottom:1px solid var(--border);padding-bottom:8px;">4. Buku Kas (Ledger) Pendapatan & Ekspor</h3>
    <ol style="margin-left:20px;font-size:14px;color:var(--text-secondary);line-height:1.6;">
      <li><b>Laporan Harian (Kasir):</b> Ringkasan setoran uang masuk pada hari tersebut. Dapat dicetak layaknya laporan setor bank khusus dengan memencet ikon <b>🖨️ Cetak.</b></li>
      <li><b>Laporan Landscape (Matrix - Buku Kas Besar):</b> Menu ini telah menjadi pusat akuntansi sekolah. Berformat matriks *landscape* yang bisa digeser (<i>scroll</i>) ke samping. Mengakomodasi 2 tabel; sisi kiri melacak Setoran Harian & <b>Saldo Running Balance</b> (Memasukkan akumulasi keseimbangan / pembawaan saldo dari bulan-bulan lalu secara otomatis), dan bagian kanan mendistribusikan uang tagihannya ke 12 kolom matriks bulan secara merata. Cetak menekan <b>🖨️ Cetak Landscape</b> (disarankan format A3/Folio landscape dari properties mesin pencetak).</li>
      <li><b>Pelacakan Laporan Tahunan:</b> Menghitamkan ringkasan 12 bulan secara kompak per Metode Bayar.</li>
    </ol>
  </div>

  {{-- Modul 5: Penagihan (Reminder) --}}
  <div class="card">
    <h3 style="font-size:18px;color:var(--accent);margin-bottom:12px;border-bottom:1px solid var(--border);padding-bottom:8px;">5. Sistem Tagihan Cerdas Bersaudara</h3>
    <ol style="margin-left:20px;font-size:14px;color:var(--text-secondary);line-height:1.6;">
      <li><b>Fitur Notifikasi WA Otomatis:</b> Buka menu <a href="{{ route('admin.reminders.index') }}" style="color:var(--accent);">Pengingat (WA)</a>. Sistem mendeteksi bulan apa saja yang belum lunas.</li>
      <li><b>Satu Chat Untuk Semua Kakak-Adik:</b> Jika sistem menemukan seorang Kakak dan Adik (dengan Nomor WA Orang Tua yang terhubung pada kedua profil), maka WA Gateway akan <b>Menyatukan (Group) Tagihannya ke dalam 1 pesan salam!</b> Ibu/Bapak wali murid tidak akan dispam oleh banyak pesan bot, melainkan satu pesan detil rincian Kakaknya sekian, Adiknya sekian, dan grand total berapanya di bagian bawah.</li>
    </ol>
  </div>

</div>
@endsection
