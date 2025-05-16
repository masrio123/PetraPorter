@php
    
    $menus = [
        [
            "label" => "Dashboard",
            "icons" => "bx bx-home",
            "route" => null
        ],
        [
            "type" => "divider",
            "label" => "Manajemen"
        ],
        [
            "label" => "Master",
            "icons" => "bx bx-package",
            "roles" => ["superAdmin", "admin"],
            "sub" => [
                [
                    "label" => "Pelanggan",
                    "route" => null
                ],
                [
                    "label" => "Karyawan",
                    "route" => null
                ],
                [
                    "label" => "Supplier",
                    "route" => null
                ],
                [
                    "label" => "Barang",
                    "route" => null
                ],
                [
                    "label" => "Salesmen",
                    "route" => null
                ],
            ]
        ],
        [
            "label" => "Transaksi",
            "icons" => "bx bx-wallet",
            "roles" => ["superAdmin", "admin", "karyawan"],
            "sub" => [
                [
                    "label" => "Penjualan",
                    "route" => null
                ],
                [
                    "label" => "Pembelian",
                    "route" => null
                ],
                [
                    "label" => "Return Barang",
                    "route" => null
                ], 
                [
                    "label" => "Pengeluaran",
                    "route" => null
                ],
            ]
        ],
        [
            "label" => "Laporan",
            "icons" => "bx bxs-report",
            "roles" => ["superAdmin", "admin", "karyawan"],
            "sub" => [
                [
                    "label" => "Penjualan",
                    "role" => ["superAdmin", "admin"],
                    "route" => null
                ],
                [
                    "label" => "Pembelian",
                    "route" => null
                ],
                [
                    "label" => "Laba Barang",
                    "route" => null
                ],
                [
                    "label" => "Pemindahan Barang",
                    "route" => null
                ],
                [
                    "label" => "Pembayaran Supplier",
                    "route" => null
                ],
                [
                    "label" => "Stok Opname",
                    "route" => null
                ],
            ]
        ],
        [
            "type" => "divider",
            "label" => "lainnya"
        ],
        [
            "label" => "keluar",
            "icons" => "bx bx-left-arrow-alt",
            "route" => null
        ]
    ];

@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.html" class="app-brand-link" style="display: block">
            <div class="app-brand-text demo menu-text fw-bold ms-2" style="display: block">
                AJM 
            </div>
        </a> 
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboards -->


        @foreach ($menus as $menu)
            {{-- @if(isset($menu['roles']))
                @if(!in_array( Auth::user()->getRoleNames()[0], $menu['roles']))
                    @php continue; @endphp
                @endif
            @endif
             --}}

            @if(isset($menu['sub']))
                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icon {{ $menu['icons'] }}"></i>
                        <div class="text-truncate">{{ $menu['label'] }}</div>
                    </a>
        
                    <ul class="menu-sub">
                        @foreach ($menu['sub'] as $item)
                            <li class="menu-item">
                                <a href="{{  $item['route'] ? route($item['route']) : '' }}" class="menu-link">
                                    <div class="text-truncate">{{ $item['label'] }}</div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>

            @elseif(isset($menu['type']))
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ $menu['label'] }}</span>
                </li>
            @else
                <li class="menu-item">
                    <a href="{{ $menu['route'] ? route($menu['route']) : '' }}" class="menu-link">
                        <i class="menu-icon tf-icons {{ $menu['icons'] }}"></i>
                        <div class="text-truncate">{{ $menu['label'] }}</div>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</aside>
