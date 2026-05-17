<!DOCTYPE html>
<html lang="en">


<!-- blank.html  21 Nov 2019 03:54:41 GMT -->
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Busur</title>
  <!-- General CSS Files -->
  <link rel="stylesheet" href="{{asset("otika-assets/css/app.min.css")}}">
  <!-- Template CSS -->
  <link rel="stylesheet" href="{{asset("otika-assets/css/style.css")}}">
  <link rel="stylesheet" href="{{asset("otika-assets/css/components.css")}}">
  <!-- Custom style CSS -->
  <link rel="stylesheet" href="{{asset("otika-assets/css/custom.css")}}">
  <link rel='shortcut icon' type='image/x-icon' href='{{asset("otika-assets/img/ssdg mlg bolong.png")}}' />
  <style>
    .actions-cell {
    position: relative;
    padding-right: 80px; /* ruang aman */
    }

    .actions-space {
        position: relative;
        z-index: 1;
    }

    .actions-button {
      position: absolute;
      top: 50%;
      right: 8px;
      transform: translateY(-50%);
      display: flex;
      gap: 6px;

      opacity: 0;
      pointer-events: none;
      transition: all 0.25s ease;

      /* background: rgba(255, 255, 255, 0.55); */
      backdrop-filter: blur(6px);
      border-radius: 8px;
      padding: 4px 6px;
    }

    /* Muncul saat hover row */
    tr:hover .actions-button {
        opacity: 1;
        pointer-events: auto;
    }

    /* Tombol */
    .custom-btn-action {
        border: none;
        background: transparent;
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
        color: #555;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .custom-btn-action:hover {
        background: rgba(64, 36, 190, 0.08);
    }

    /* Mobile Navigation Styles */
    .mobile-nav {
        display: none;
        position: fixed;
        bottom: 15px;
        left: 50%;
        transform: translateX(-50%);
        width: 95%;
        max-width: 600px;
        height: 65px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        justify-content: space-around;
        align-items: center;
        padding: 0 5px;
    }

    .mobile-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #888;
        text-decoration: none;
        font-size: 8px;
        font-weight: 700;
        transition: all 0.3s ease;
        flex: 1;
        text-align: center;
        min-width: 0;
    }

    .mobile-nav-item i {
        font-size: 17px;
        margin-bottom: 4px;
        transition: all 0.3s ease;
    }

    .mobile-nav-item span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
    }

    .mobile-nav-item.active {
        color: #6777ef;
    }

    .mobile-nav-item.active i {
        transform: translateY(-2px);
    }

    @media (max-width: 1024px) {
        .mobile-nav {
            display: flex;
        }
        .main-content {
            padding-bottom: 90px !important;
        }
        .main-footer {
            display: none;
        }
        /* Optional: hide sidebar toggle on mobile to clean up header */
        /* .nav-link.collapse-btn { display: none; } */
    }

    /* Disabled sidebar menu styles when campaign is active */
    .disabled-sidebar-menu {
        opacity: 0.65;
    }
    .disabled-sidebar-menu a {
        cursor: not-allowed !important;
    }
    .disabled-sidebar-menu a span {
        color: #98a6ad !important;
    }
  </style>
  @yield('css')
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <nav class="navbar navbar-expand-lg main-navbar sticky">
        <div class="form-inline mr-auto">
          <ul class="navbar-nav mr-3 d-none d-lg-flex">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn"> <i data-feather="align-justify"></i></a></li>
            <li><a href="#" class="nav-link nav-link-lg fullscreen-btn">
                <i data-feather="maximize"></i>
              </a></li>
          </ul>
          <div class="d-lg-none">
            <a href="#" class="d-flex align-items-center" style="text-decoration: none;">
              <img alt="image" src="{{asset("otika-assets/img/ssdg mlg bolong.png")}}" style="height: 30px; background-color: #000; border-radius: 50%; padding: 4px;" />
              <span class="ml-2 font-weight-bold" style="font-size: 1.1rem; color: #6777ef;">SSDG MLG</span>
            </a>
          </div>
        </div>
        <ul class="navbar-nav navbar-right">
          <li class="dropdown"><a href="#" data-toggle="dropdown"
              class="nav-link dropdown-toggle nav-link-lg nav-link-user"> <img alt="image" src="{{asset("otika-assets/img/users/images.jpeg")}}"
                class="user-img-radious-style"> <span class="d-sm-none d-lg-inline-block"></span></a>
            <div class="dropdown-menu dropdown-menu-right pullDown">
              <div class="dropdown-title">Hello User</div>
              <a href="#" class="dropdown-item has-icon"> <i class="far
										fa-user"></i> Profil
              </a> 
              <a href="#" class="dropdown-item has-icon"> <i class="fas fa-bolt"></i>
                Aktifitas Akun
              </a>
              <div class="dropdown-divider"></div>
              <form action="#" method="POST">
                  @csrf
                  <button type="submit" class="dropdown-item has-icon text-danger">Keluar</button>
              </form>
            </div>
          </li>
        </ul>
      </nav>
      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
          <div class="sidebar-brand">
            <a href="#"> <img alt="image" src="{{asset("otika-assets/img/ssdg mlg bolong.png")}}" class="header-logo" /> <span
                class="logo-name">SSDG MLG</span>
            </a>
          </div>
          <ul class="sidebar-menu">
            @php
              $activeCampaign = \App\Models\Campaign::whereIn('status', ['queued', 'running', 'resting'])->first();
            @endphp
            <li class="dropdown {{ Route::is('dashboard') || Route::is('dashboard.*') ? 'active' : '' }}">
              <a href="{{route("dashboard")}}" class="nav-link"><i data-feather="monitor"></i><span>Dashboard</span></a>
            </li>
            <li class="dropdown {{ Route::is('pesan-tunggal.index') ? 'active' : '' }} {{ $activeCampaign ? 'disabled-sidebar-menu' : '' }}">
              @if($activeCampaign)
                <a href="javascript:void(0)" class="nav-link" title="Terkunci: Campaign '{{ $activeCampaign->nama }}' sedang berjalan">
                  <i data-feather="lock" class="text-warning"></i><span>Pesan Tunggal</span>
                </a>
              @else
                <a href="{{route("pesan-tunggal.index")}}" class="nav-link"><i data-feather="dollar-sign"></i><span>Pesan Tunggal</span></a>
              @endif
            </li>
            <li class="dropdown {{ Route::is('bulking.index') || Route::is('bulking.show') ? 'active' : '' }} {{ $activeCampaign ? 'disabled-sidebar-menu' : '' }}">
              @if($activeCampaign)
                <a href="javascript:void(0)" class="nav-link" title="Terkunci: Campaign '{{ $activeCampaign->nama }}' sedang berjalan">
                  <i data-feather="lock" class="text-warning"></i><span>Pesan Bulking</span>
                </a>
              @else
                <a href="{{route("bulking.index")}}" class="nav-link"><i data-feather="calendar"></i><span>Pesan Bulking</span></a>
              @endif
            </li>
            <li class="dropdown {{ Route::is('bulking.log') ? 'active' : '' }}">
              <a href="{{route("bulking.log")}}" class="nav-link"><i data-feather="file-text"></i><span>Log Pesan Bulking</span></a>
            </li>
            <li class="dropdown {{ Route::is('pesan-tunggal.log') ? 'active' : '' }}">
              <a href="{{route("pesan-tunggal.log")}}" class="nav-link"><i data-feather="list"></i><span>Log Pesan Tunggal</span></a>
            </li>
          </ul>
        </aside>
      </div>
      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <!-- add content here -->
            @yield('content')
          </div>
        </section>
        @yield('content2')
      </div>
      <footer class="main-footer">
        <div class="footer-left">
          <a href="#">SSDG Malang Raya</a></a>
        </div>
        <div class="footer-right">
        </div>
      </footer>
    </div>
    
    <!-- Mobile Bottom Nav -->
    <nav class="mobile-nav">
        <a href="#" class="mobile-nav-item {{ Route::is('dashboard*') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i>
            <span>Dash</span>
        </a>
        <a href="#" class="mobile-nav-item {{ Route::is('kas*') ? 'active' : '' }}">
            <i class="fas fa-wallet"></i>
            <span>Kas</span>
        </a>
        <a href="#" class="mobile-nav-item {{ Route::is('acara*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i>
            <span>Acara</span>
        </a>
        <a href="#" class="mobile-nav-item {{ Route::is('member*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Anggota</span>
        </a>
        <a href="#" class="mobile-nav-item {{ Route::is('inventaris*') ? 'active' : '' }}">
            <i class="fas fa-box"></i>
            <span>Inventaris</span>
        </a>
        <a href="#" class="mobile-nav-item {{ Route::is('laporan-cashflow*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Lap. Kas</span>
        </a>
        <a href="#" class="mobile-nav-item {{ Route::is('laporan-inventaris*') ? 'active' : '' }}">
            <i class="fas fa-file-contract"></i>
            <span>Lap. Inv</span>
        </a>
    </nav>
  </div>
  <!-- General JS Scripts -->
  <script src="{{asset("otika-assets/js/app.min.js")}}"></script>
  <script src="{{asset("otika-assets/bundles/jquery-ui/jquery-ui.min.js")}}"></script>
  <!-- JS Libraies -->
  <!-- Page Specific JS File -->
  <!-- Template JS File -->
  <script src="{{asset("otika-assets/js/scripts.js")}}"></script>
  <!-- Custom JS File -->
  @yield('javascript')
</body>


<!-- blank.html  21 Nov 2019 03:54:41 GMT -->
</html>
