<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Petra Porter')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}" defer></script>
    <script src="{{ asset('assets/js/config.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <style>
        body {
            background-color: #ffffff !important;
        }

        /* Overlay background dengan animasi gorden buka dari bawah ke atas */
        #book-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: #d9d9d9;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 1s cubic-bezier(0.4, 0, 0.2, 1);
            /* Mulai dari clip-path yang menutup bawah */
            clip-path: polygon(0 0%, 100% 0%, 100% 0%, 0 0%);
        }

        /* Saat aktif tampilkan dan buka gorden dari bawah ke atas */
        #book-overlay.active {
            opacity: 1;
            pointer-events: auto;
            animation: curtainOpen 1s forwards cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Animasi buka gorden dari bawah ke atas */
       @keyframes curtainOpen {
    from {
        clip-path: polygon(0 0%, 100% 0%, 100% 0%, 0 0%);
    }
    to {
        clip-path: polygon(0 0%, 100% 0%, 100% 100%, 0 100%);
    }
}
        /* Logo */
        #book-overlay img#book-logo {
            height: 145px;
            /* sesuaikan ukuran logo */
            user-select: none;
            pointer-events: none;
            animation: shake 0.6s ease-in-out infinite;
        }

        /* Efek geter halus (shake) */
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-4px);
            }

            50% {
                transform: translateX(4px);
            }

            75% {
                transform: translateX(-4px);
            }
        }
    </style>
</head>

<body>
    <div id="book-overlay">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo Petra Porter" id="book-logo" />
    </div>

    @if (isset($viewLogin))
        @yield('content')
    @else
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                @include('layouts.sidebar')
                <div class="layout-page">
                    @include('layouts.navbar')
                    <div class="content-wrapper">
                        <div class="container-xxl flex-grow-1 container-p-y bg-white">
                            @yield('content')
                        </div>
                        <div class="content-backdrop fade"></div>
                    </div>
                </div>
            </div>
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
    @endif

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('book-overlay');
            const links = document.querySelectorAll('.menu-link');

            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    const targetUrl = link.href;
                    const currentUrl = window.location.href.split(/[?#]/)[0];

                    // Jangan animasi jika klik link yang sama
                    if (targetUrl === currentUrl) return;

                    e.preventDefault();

                    // Aktifkan overlay & animasi
                    overlay.classList.add('active');

                    // Delay pindah halaman 1.2 detik supaya animasi selesai
                    setTimeout(() => {
                        window.location.href = targetUrl;
                    }, 1200);
                });
            });
        });
    </script>

    @yield('scripts')

    @if (session('success') || session('error'))
        @php
            $title = session('success') ? 'success' : 'error';
            $message = session('success') ?? session('error');
            session()->forget(['success', 'error']);
        @endphp
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '{{ ucfirst($title) }}',
                    text: "{{ $message }}",
                    icon: '{{ $title }}',
                    confirmButtonColor: '#435ebe',
                    confirmButtonText: 'Done',
                });
            });
        </script>
    @endif
</body>

</html>
