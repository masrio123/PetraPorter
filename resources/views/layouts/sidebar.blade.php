@php
    $menus = [
        ['label' => 'Dashboard', 'icons' => 'bx bx-home', 'route' => 'dashboard.index'],
        ['label' => 'Tenant Management', 'icons' => 'bx bx-store', 'route' => 'dashboard.tenants.index'],
        ['label' => 'Porter Management', 'icons' => 'bx bx-user', 'route' => 'dashboard.porters.index'],
        ['label' => 'Delivery Point', 'icons' => 'bx bx-map', 'route' => 'dashboard.delivery-points.index'],
        ['label' => 'Bank User', 'icons' => 'bx bx-money', 'route' => 'dashboard.bank-users.index'],
        ['label' => 'Aktivitas', 'icons' => 'bx bx-run', 'route' => 'dashboard.activity.activity'],
        ['type' => 'divider', 'label' => 'Lainnya'],
        ['label' => 'Keluar', 'icons' => 'bx bx-left-arrow-alt', 'route' => 'logout'],
    ];
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme d-flex flex-column"
    style="background-color: #d9d9d9;">
    <div class="text-center mt-6" style="margin-top: 120px; padding: 0 20px 20px;">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo Petra Porter" style="height: 145px;" />
    </div>

    <ul class="menu-inner py-7 flex-grow-1" style="margin-top: -40px; margin-bottom: 1px;">
        @foreach ($menus as $menu)
            @if (isset($menu['type']) && $menu['type'] === 'divider')
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text text-dark">{{ $menu['label'] }}</span>
                </li>
            @else
                @php
                    $isActive = $menu['route'] && Route::currentRouteName() === $menu['route'];
                @endphp
                <li class="menu-item {{ $isActive ? 'active' : '' }}">
                    <a href="{{ $menu['route'] ? route($menu['route']) : '#' }}"
                        class="menu-link d-flex align-items-center">
                        <i
                            class="menu-icon tf-icons {{ $menu['icons'] }} {{ $isActive ? 'text-dark' : 'text-secondary' }}"></i>
                        <div class="text-truncate {{ $isActive ? 'text-dark' : 'text-dark' }}">
                            {{ $menu['label'] }}
                        </div>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>

    <div class="p-8 mt-auto text-center">
        <img src="{{ asset('assets/img/logopcu.png') }}" alt="Logo PCU" class="img-fluid" style="max-height: 60px;" />
    </div>
</aside>
