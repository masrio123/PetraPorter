@php
    $menus = [
        [
            "label" => "Dashboard",
            "icons" => "bx bx-home",
            "route" => "dashboard.index"
        ],
        [
            "type" => "divider",
            "label" => "Manajemen"
        ],
        [
            "label" => "Tenant Management",
            "icons" => "bx bx-store",
            "route" => "dashboard.tenants.index"
        ],
        [
            "label" => "Porter Management",
            "icons" => "bx bx-user",
            "route" => "dashboard.porters.index"
        ],
        [
            "type" => "divider",
            "label" => "Lainnya"
        ],
        [
            "label" => "Keluar",
            "icons" => "bx bx-left-arrow-alt",
            "route" => null
        ]
    ];
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard.index') }}" class="app-brand-link">
            <div class="app-brand-text demo menu-text fw-bold ms-2">Petra Porter</div>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>
    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($menus as $menu)
            @if(isset($menu['type']) && $menu['type'] === 'divider')
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ $menu['label'] }}</span>
                </li>
            @else
                <li class="menu-item">
                    <a href="{{ $menu['route'] ? route($menu['route']) : '#' }}" class="menu-link">
                        <i class="menu-icon tf-icons {{ $menu['icons'] }}"></i>
                        <div class="text-truncate">{{ $menu['label'] }}</div>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</aside>
