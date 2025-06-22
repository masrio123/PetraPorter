@php
    $menus = [
        ['label' => 'Dashboard', 'icons' => 'bx bxs-dashboard', 'route' => 'dashboard.index'],
        ['label' => 'Tenant Management', 'icons' => 'bx bxs-store-alt', 'route' => 'dashboard.tenants.index'],
        ['label' => 'Porter Management', 'icons' => 'bx bxs-user-account', 'route' => 'dashboard.porters.index'],
        ['label' => 'Delivery Point', 'icons' => 'bx bxs-map-pin', 'route' => 'dashboard.delivery-points.index'],
        ['label' => 'Aktivitas', 'icons' => 'bx bx-run', 'route' => 'dashboard.activity.activity'],
    ];
@endphp

<style>
    .layout-page {
        border-left: none !important;
    }

    .layout-menu { 
        background-color: #FFFFFF !important;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05) !important;
        border-right: none !important; 
    }

    /* 1. Atur transisi pada sidebar */
    .layout-menu-fixed .layout-menu {
        transition: transform 0.3s ease-in-out;
    }

    /* 2. Atur posisi default sidebar (tersembunyi) di layar kecil */
    @media (max-width: 1199.98px) {
        .layout-menu-fixed .layout-menu {
            transform: translateX(-100%);
        }
    }

    /* 3. Atur posisi sidebar saat menu-expanded aktif (terlihat) */
    .layout-menu-expanded .layout-menu {
        transform: translateX(0);
    }
    
    .menu-inner {
        overflow-y: auto;
    }

    .menu-inner::-webkit-scrollbar {
        width: 6px;
    }
    .menu-inner::-webkit-scrollbar-track {
        background: transparent;
    }
    .menu-inner::-webkit-scrollbar-thumb {
        background: #D6D6D6;
        border-radius: 6px;
    }
    .menu-inner::-webkit-scrollbar-thumb:hover {
        background: #C0C0C0;
    }

    .menu-vertical .menu-item .menu-link {
        transition: background-color 0.2s ease, color 0.2s ease;
        border-radius: 6px; margin: 0.2rem 0.5rem;
        border-left: 3px solid transparent; 
    }
    
    .menu-vertical .menu-item .menu-link,
    .menu-vertical .menu-item .menu-link div,
    .menu-vertical .menu-item .menu-link .menu-icon {
        color: var(--sidebar-text-color, #566a7f); transition: color 0.2s ease;
    }
    
    .menu-vertical .menu-item:not(.active):hover .menu-link div,
    .menu-vertical .menu-item:not(.active):hover .menu-link .menu-icon { color: var(--brand-primary, #ff7622); }
    
    .menu-vertical .menu-item.active > .menu-link::before,
    .menu-vertical .menu-item.active > .menu-link::after {
        display: none !important;
    }
    
    .menu-vertical .menu-item.active > .menu-link {
        background-color: var(--sidebar-active-bg, rgba(255, 118, 34, 0.1)) !important; 
        font-weight: 500 !important;
        border-left: 3px solid var(--brand-primary, #ff7622) !important;
        box-shadow: none !important; 
    }

    .menu-vertical .menu-item.active .menu-link div,
    .menu-vertical .menu-item.active .menu-link .menu-icon { 
        color: var(--sidebar-active-text, #ff7622) !important; 
    }
</style>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme d-flex flex-column">

    <div class="app-brand demo d-flex justify-content-center align-items-center" style="height: 160px;">
        <a href="{{ route('dashboard.index') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo Petra Porter" style="height: 150px;" />
            </span>
        </a>
    </div>

    <ul class="menu-inner py-5">
        @foreach ($menus as $menu)
            @php
                $isActive = $menu['route'] && str_starts_with(Route::currentRouteName(), $menu['route']);
            @endphp
            <li class="menu-item {{ $isActive ? 'active' : '' }}">
                <a href="{{ $menu['route'] ? route($menu['route']) : '#' }}" class="menu-link">
                    <i class="menu-icon tf-icons {{ $menu['icons'] }}"></i>
                    <div data-i18n="{{ $menu['label'] }}">{{ $menu['label'] }}</div>
                </a>
            </li>
        @endforeach
    </ul>
    
    <div class="mt-auto"></div>

    <div class="p-4 text-center">
        <img src="https://login.petra.ac.id/images/logo-ukp.png" alt="Logo PCU" class="img-fluid" style="max-height: 45px;" />
    </div>
</aside>
