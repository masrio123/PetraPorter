{{-- File: resources/views/layouts/navbar.blade.php --}}

<style>
    /* Style untuk ikon burger agar terlihat di latar putih */
    .layout-navbar .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(0, 0, 0, 0.55)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    /*
      ==================================================================================
      PERBAIKAN FINAL: Membuat navbar "menempel" di atas saat scroll.
      Ini adalah cara yang benar untuk navbar 'detached'.
      ==================================================================================
    */
    #layout-navbar {
        position: -webkit-sticky; /* Kompatibilitas untuk browser Safari */
        position: sticky;
        top: 1rem; /* Saat menempel, jaraknya 1rem dari atas */
        z-index: 1020; /* Pastikan navbar selalu tampil di lapisan paling atas */
        
        /* Memberi jarak awal sebelum scroll, yang sebelumnya dari inline style */
        margin-top: 1rem;
        border-radius: 8px; /* Mengembalikan sudut melengkung */
    }
</style>

{{-- 
  CATATAN: 
  Elemen <nav> di bawah ini sekarang bersih dari kelas `fixed-top` atau style inline untuk posisi.
  Semua pengaturan posisi diatur oleh CSS #layout-navbar di atas.
--}}
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar" style="background-color: #FFFFFF !important; box-shadow: 0 2px 10px rgba(71, 85, 105, 0.05);">

    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <span class="navbar-toggler-icon"></span>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <h5 class="mb-0 text-muted">@yield('title')</h5>
            </div>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="https://img.freepik.com/premium-vector/default-avatar-profile-icon-social-media-user-image-gray-avatar-icon-blank-profile-silhouette-vector-illustration_561158-3485.jpg" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('logout') }}"><i class="bx bx-power-off me-2"></i><span class="align-middle">Keluar</span></a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
