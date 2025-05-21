@extends('layouts.app')

@section('content')
    <div class="container mb-4">
        <div class="row">
            <h3 class="mb-4"><strong>Admin Dashboard</strong></h3>

            {{-- Card Users Online --}}
            <div class="col-6">
                <div class="card card-body position-relative">
                    {{-- Gambar di pojok kanan atas --}}
                    <img src="{{ asset('assets/img/users_online.png') }}" 
                         alt="Users Online" 
                         style="position: absolute; top: 16px; left: 25px; height: 60px;" />

                    <div class="card-title d-flex align-items-start justify-content-between mb-9">
                        <div class="avatar flex-shrink-0 fs-2 text-primary">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <p class="mb-1">Users Online</p>
                    <h4 class="card-title mb-3">120</h4>
                </div>
            </div>

            {{-- Card Total Profit --}}
            <div class="col-6">
                <div class="card card-body position-relative">
                    {{-- Gambar di pojok kanan atas --}}
                    <img src="{{ asset('assets/img/total_order.png') }}" 
                         alt="Total Order" 
                         style="position: absolute; top: 16px; left: 25px; height: 60px;" />

                    <div class="card-title d-flex align-items-start justify-content-between mb-9">
                        <div class="avatar flex-shrink-0 fs-2 text-success">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                    <p class="mb-1">Total Profit</p>
                    <h4 class="card-title mb-3">Rp 7,000,000</h4>
                </div>
            </div>
        </div>
    </div>
@endsection
