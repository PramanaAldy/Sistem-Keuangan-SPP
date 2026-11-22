<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin') — SPP Universal School</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  @stack('styles')
</head>

<body>
  <div class="app-layout">

    {{-- ── SIDEBAR OVERLAY (mobile) ────────────────────────────────────────── --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- ── SIDEBAR ─────────────────────────────────────────────────────────── --}}
    <nav class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        <img src="{{ asset('images/logo.png') }}" alt="Universal School" style="width:42px;height:42px;object-fit:contain;flex-shrink:0;border-radius:6px;">
        <div class="logo-text">SPP <span>Universal</span><br>School</div>
      </div>

      <div class="sidebar-nav">
        {{-- Menu Utama --}}
        <div class="nav-section">
          <div class="nav-label">Menu Utama</div>
          <a href="{{ route('admin.dashboard') }}"
            class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
          </a>
          <a href="{{ route('admin.transactions.index') }}"
            class="nav-item {{ request()->routeIs('admin.transactions*') && !request()->routeIs('admin.sibling-check*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span>Transaksi SPP</span>
          </a>
          <a href="{{ route('admin.sibling-check.index') }}"
            class="nav-item {{ request()->routeIs('admin.sibling-check*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </svg>
            <span>Cek Saudara</span>
          </a>
        </div>

        {{-- Tagihan & Laporan --}}
        <div class="nav-section">
          <div class="nav-label">Tagihan & Laporan</div>
          <a href="{{ route('admin.reports.index') }}"
            class="nav-item {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Laporan</span>
          </a>
          <a href="{{ route('admin.bank-deposits.index') }}"
            class="nav-item {{ request()->routeIs('admin.bank-deposits*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 21v-8m0 0V3m0 10h9m-9 0H3" />
            </svg>
            <span>Setor Bank</span>
          </a>
          <a href="{{ route('admin.reminders.index') }}"
            class="nav-item {{ request()->routeIs('admin.reminders*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span>Pengingat (WA)</span>
          </a>
          <a href="{{ route('admin.debt-letters.index') }}"
            class="nav-item {{ request()->routeIs('admin.debt-letters*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Surat Tunggakan</span>
          </a>

        </div>

        {{-- Data Master --}}
        <div class="nav-section">
          <div class="nav-label">Data Master</div>
          <a href="{{ route('admin.academic-years.index') }}"
            class="nav-item {{ request()->routeIs('admin.academic-years*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Tahun Ajaran</span>
          </a>
          <a href="{{ route('admin.classes.index') }}"
            class="nav-item {{ request()->routeIs('admin.classes*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span>Data Kelas</span>
          </a>
          <a href="{{ route('admin.students.index') }}"
            class="nav-item {{ request()->routeIs('admin.students*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Data Siswa</span>
          </a>
          <a href="{{ route('admin.discounts.index') }}"
            class="nav-item {{ request()->routeIs('admin.discounts*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <span>Data Diskon</span>
          </a>
        </div>

        {{-- Pengaturan Tarif --}}
        <div class="nav-section">
          <div class="nav-label">Pengaturan Tarif</div>
          <a href="{{ route('admin.spp-settings.index') }}"
            class="nav-item {{ request()->routeIs('admin.spp-settings*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Tarif SPP</span>
          </a>
          <a href="{{ route('admin.fine-settings.index') }}"
            class="nav-item {{ request()->routeIs('admin.fine-settings*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>Aturan Denda</span>
          </a>
        </div>

        {{-- Sistem --}}
        <div class="nav-section">
          <div class="nav-label">Sistem</div>
          <a href="{{ route('admin.admin-users.index') }}"
            class="nav-item {{ request()->routeIs('admin.admin-users*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Manajemen Admin</span>
          </a>
          <a href="{{ route('admin.tutorial.index') }}"
            class="nav-item {{ request()->routeIs('admin.tutorial*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Panduan Penggunaan</span>
          </a>
          <a href="{{ route('admin.audit-logs.index') }}"
            class="nav-item {{ request()->routeIs('admin.audit-logs*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span>Audit Log</span>
          </a>
        </div>
      </div>

      {{-- Logout --}}
      <div class="sidebar-footer">
        <form method="POST" action="{{ route('admin.logout') }}">
          @csrf
          <button type="submit" class="nav-item w-100"
            style="background:none; border:none; cursor:pointer; color: var(--text-secondary);">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Logout</span>
          </button>
        </form>
      </div>
    </nav>

    {{-- ── MAIN ──────────────────────────────────────────────────────────── --}}
    <div class="main-content">
      {{-- Topbar --}}
      <header class="topbar">
        <button class="btn btn-icon btn-secondary" id="mobileMenuBtn" aria-label="Toggle menu">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            style="width:18px;height:18px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <div>
          <div class="topbar-title">@yield('page_title', 'Dashboard')</div>
        </div>
        <div class="topbar-right">
          @if(\App\Models\AcademicYear::active())
            <span class="topbar-badge">{{ \App\Models\AcademicYear::active()->name }}</span>
          @endif



          <div style="text-align:right">
            <div class="topbar-user">{{ Auth::guard('admin')->user()->name }}</div>
            <div style="font-size:11px; color:var(--text-muted)">
              {{ Auth::guard('admin')->user()->role === 'super_admin' ? 'Super Admin' : 'Admin' }}</div>
          </div>
          {{-- Avatar Dropdown --}}
          <div style="position:relative;" id="avatarMenu">
            <div class="topbar-avatar" id="avatarBtn" onclick="toggleAvatarMenu()" style="cursor:pointer;" title="Menu akun">
              {{ strtoupper(substr(Auth::guard('admin')->user()->name, 0, 1)) }}
            </div>
            <div id="avatarDropdown" style="
              display:none;position:absolute;top:calc(100% + 8px);right:0;
              background:var(--bg-card);border:1px solid var(--border);
              border-radius:var(--radius);min-width:180px;box-shadow:var(--shadow);
              z-index:9999;overflow:hidden;">
              <div style="padding:12px 14px;border-bottom:1px solid var(--border);font-size:12px;">
                <div style="font-weight:600;color:var(--text-primary);">{{ Auth::guard('admin')->user()->name }}</div>
                <div style="color:var(--text-muted);">{{ Auth::guard('admin')->user()->email }}</div>
              </div>
              <a href="{{ route('admin.change-password.show') }}"
                style="display:flex;align-items:center;gap:10px;padding:10px 14px;color:var(--text-secondary);text-decoration:none;font-size:13px;transition:background 0.15s;"
                onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background=''"
              >🔐 Ganti Password</a>
              <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" style="width:100%;display:flex;align-items:center;gap:10px;padding:10px 14px;color:var(--red-light);background:none;border:none;cursor:pointer;font-size:13px;text-align:left;transition:background 0.15s;"
                  onmouseover="this.style.background='rgba(239,68,68,0.08)'" onmouseout="this.style.background=''">
                  🚪 Logout
                </button>
              </form>
            </div>
          </div>
        </div>
      </header>

      {{-- Page Content --}}
      <div class="page-content">
        {{-- Flash Messages --}}
        @if(session('success'))
          <div class="alert alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
          </div>
        @endif
        @if(session('error'))
          <div class="alert alert-danger">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('error') }}
          </div>
        @endif
        @php $errorList = $errors instanceof \Illuminate\Support\ViewErrorBag ? $errors->all() : []; @endphp
        @if(count($errorList) > 0)
          <div class="alert alert-danger">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              @foreach($errorList as $err) <div>{{ $err }}</div> @endforeach
            </div>
          </div>
        @endif

        @yield('content')
      </div>
    </div>
  </div>

  <script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const mobileBtn = document.getElementById('mobileMenuBtn');

    function openSidebar() {
      sidebar.classList.add('open');
      overlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
      document.body.style.overflow = '';
    }

    function isMobile() { return window.innerWidth <= 768; }

    // Mobile menu button
    mobileBtn.addEventListener('click', () => {
      if (sidebar.classList.contains('open')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });

    // Tap overlay to close
    overlay.addEventListener('click', closeSidebar);

    // Close sidebar when nav item clicked on mobile
    sidebar.querySelectorAll('.nav-item').forEach(item => {
      item.addEventListener('click', () => {
        if (isMobile()) closeSidebar();
      });
    });

    // Handle resize
    window.addEventListener('resize', () => {
      if (!isMobile()) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      }
    });

    // Format currency
    function formatRp(n) {
      return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    // ── Avatar dropdown ────────────────────────────────────────────────────
    function toggleAvatarMenu() {
      const dd = document.getElementById('avatarDropdown');
      dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
    }
    // Tutup dropdown jika klik di luar
    document.addEventListener('click', function (e) {
      const menu = document.getElementById('avatarMenu');
      if (menu && !menu.contains(e.target)) {
        document.getElementById('avatarDropdown').style.display = 'none';
      }
    });
  </script>
  @stack('scripts')
</body>

</html>