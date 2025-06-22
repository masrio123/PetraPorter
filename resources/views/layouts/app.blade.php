<!doctype html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Petra Porter</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <script src="{{ asset('assets/vendor/js/helpers.js') }}" defer></script>
    <script src="{{ asset('assets/js/config.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <style>
        :root {
            --brand-primary: #ff7622;
            --sidebar-bg: #FFFFFF;
            --sidebar-text-color: #566a7f;
            --sidebar-active-bg: rgba(255, 118, 34, 0.1); 
            --sidebar-active-text: var(--brand-primary);
            --menu-header-color: #A1ACB8;
            --content-bg: #F5F7FA;
        }
        body { background-color: var(--content-bg) !important; }
        #book-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background-color: var(--brand-primary);
            z-index: 9999; opacity: 0; pointer-events: none; display: flex;
            justify-content: center; align-items: center;
            transition: opacity 1s cubic-bezier(0.4, 0, 0.2, 1);
            clip-path: polygon(0 100%, 100% 100%, 100% 100%, 0 100%);
        }
        #book-overlay.active { opacity: 1; pointer-events: auto; animation: curtainReveal 1s forwards cubic-bezier(0.83, 0, 0.17, 1); }
        @keyframes curtainReveal { from { clip-path: polygon(0 100%, 100% 100%, 100% 100%, 0 100%); } to { clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%); } }
        #book-overlay img#book-logo { height: 150px; user-select: none; pointer-events: none; animation: subtlePulse 1.5s ease-in-out infinite; }
        @keyframes subtlePulse { 0%, 100% { transform: scale(1); opacity: 0.9; } 50% { transform: scale(1.05); opacity: 1; } }
    </style>
</head>

<body>
    <div id="book-overlay">
        <img src="{{ asset('https://sustainability.petra.ac.id/wp-content/uploads/2023/07/PCU-LOGO-1024x247.png') }}" alt="Logo Petra Porter" id="book-logo" />
    </div>

    @if (isset($viewLogin))
        @yield('content')
    @else
        {{-- Struktur ini sudah benar. Tidak perlu ada perubahan di sini. --}}
        {{-- Navbar akan menjadi "sticky" relatif terhadap parent-nya, yaitu 'layout-page' --}}
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                @include('layouts.sidebar')
                <div class="layout-page">
                    @include('layouts.navbar')
                    <div class="content-wrapper">
                        <div class="container-xxl flex-grow-1 container-p-y">
                            @yield('content')
                        </div>
                        <div class="content-backdrop fade"></div>
                    </div>
                </div>
            </div>
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
    @endif

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('book-overlay');
            const links = document.querySelectorAll('.menu-link');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    const targetUrl = link.href;
                    const currentUrl = window.location.href.split(/[?#]/)[0];
                    if (!targetUrl || targetUrl.endsWith('#') || targetUrl === currentUrl || link.getAttribute('data-bs-toggle') === 'collapse') return;
                    e.preventDefault();
                    overlay.classList.add('active');
                    setTimeout(() => { window.location.href = targetUrl; }, 1000);
                });
            });
        });
    </script>
    @yield('scripts')
    @if (session('success') || session('error'))
        @php
            $title = session('success') ? 'success' : 'error';
            $message = session('success') ? session('success') : session('error');
            session()->forget(['success', 'error']);
        @endphp
        <script>
            Swal.fire({
                title: '{{ ucfirst($title) }}', text: "{{ $message }}",
                icon: '{{ $title }}', confirmButtonColor: '#435ebe',
                confirmButtonText: 'Done',
            }).then((result) => { if (result.isConfirmed) { /* Optional: Aksi setelah menutup alert */ } });
        </script>
    @endif
    <script>
        // Script untuk toggle menu biarkan apa adanya
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggles = document.querySelectorAll('.layout-menu-toggle');
            const htmlElement = document.querySelector('html');
            if (menuToggles.length && htmlElement) {
                menuToggles.forEach(toggle => {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        htmlElement.classList.toggle('layout-menu-expanded');
                    });
                });
            }
        });
    </script>
</body>
</html>
