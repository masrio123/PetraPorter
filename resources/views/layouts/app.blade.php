<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>@yield('title', 'Admin Panel - Aplikasi Kantin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

    <style>
        body {
            min-height: 100vh;
            background-color: #f8f9fa;
            margin: 0;
        }

        /* Sidebar */
        #sidebar {
            height: 100vh;
            width: 220px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #d9d9d9;
            color: #333;
            padding-top: 1rem;
        }

        #sidebar .nav-link {
            color: #555;
            font-weight: 500;
        }

        #sidebar .nav-link.active,
        #sidebar .nav-link:hover {
            color: #000;
            background-color: #bfbfbf;
            border-radius: 4px;
        }

        /* Content */
        #main-content {
            margin-left: 220px;
            padding: 0; /* hilangkan padding agar navbar nempel */
        }

        /* Navbar atas */
        .navbar-custom {
            background-color: #e7e7e7;
            border-radius: 0;
            margin: 0;
            padding: 0.5rem 1rem;
        }

        .navbar-custom .navbar-text {
            color: #333;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar" class="d-flex flex-column">
        <div class="text-center mb-4">
            <h4 class="text-dark">Admin Kantin</h4>
        </div>
        <ul class="nav flex-column px-2">
            <li class="nav-item">
                <a href="{{ route('tenants.admin') }}" class="nav-link @if(request()->routeIs('tenants.admin')) active @endif">
                    Manajemen Tenant
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link disabled" tabindex="-1" aria-disabled="true">
                    Manajemen Porter (Coming Soon)
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main content -->
    <div id="main-content">
        <!-- Navbar atas -->
        <nav class="navbar navbar-expand navbar-custom">
            <div class="container-fluid justify-content-end p-1">
                <span class="navbar-text d-flex align-items-center">
                    <i class="bi bi-person-circle fs-5 me-3"></i> Admin 1
                </span>
            </div>
        </nav>

        <!-- Page content -->
        <main class="p-4">
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
