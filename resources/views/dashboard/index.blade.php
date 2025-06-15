@extends('layouts.app')

@section('content')
    <div class="container mb-4">
        <div class="row">
            <h3 class="mb-4"><strong>Admin Dashboard</strong></h3>

            {{-- Card Total Order Selesai Hari Ini --}}
            <div class="col-4">
                <div class="card card-body position-relative text-white"
                    style="background: linear-gradient(to bottom, #fca311, #d65a00); border: none;">
                    <img src="{{ asset('assets/img/total-order.png') }}" alt="Total Order"
                        style="position: absolute; top: 16px; left: 25px; height: 60px;" />

                    <div class="card-title d-flex align-items-start justify-content-between mb-9">
                        <div class="avatar flex-shrink-0 fs-2 text-white">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <p class="mb-1 fw-bold text-white">Total Order Hari Ini</p>
                    <h4 class="card-title mb-3 text-white">{{ $summary['total_orders_completed'] }}</h4>
                </div>
            </div>

            {{-- Card Total Income Hari Ini --}}
            <div class="col-4">
                <div class="card card-body position-relative text-white"
                    style="background: linear-gradient(to bottom, #fca311, #d65a00); border: none;">
                    <img src="{{ asset('assets/img/total-income.png') }}" alt="Total Income"
                        style="position: absolute; top: 16px; left: 25px; height: 60px; width: 60px;" />

                    <div class="card-title d-flex align-items-start justify-content-between mb-9">
                        <div class="avatar flex-shrink-0 fs-2 text-white">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                    <p class="mb-1 fw-bold text-white">Total Income Hari Ini</p>
                    <h4 class="card-title mb-3 text-white">Rp{{ number_format($summary['total_income'], 0, ',', '.') }}</h4>
                </div>
            </div>

            {{-- Card Porter Online --}}
            <div class="col-4">
                <div class="card card-body position-relative text-white"
                    style="background: linear-gradient(to bottom, #fca311, #d65a00); border: none;">
                    <img src="{{ asset('assets/img/total-order.png') }}" alt="Total Order"
                        style="position: absolute; top: 16px; left: 25px; height: 60px;" />

                    <div class="card-title d-flex align-items-start justify-content-between mb-9">
                        <div class="avatar flex-shrink-0 fs-2 text-white">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <p class="mb-1 fw-bold text-white">Porter Online</p>
                    <h4 class="card-title mb-3 text-white">{{ $onlinePorterCount }}</h4>
                </div>
            </div>


        </div>
    @endsection
