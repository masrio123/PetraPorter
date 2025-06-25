@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    {{-- Bagian Header Selamat Datang --}}
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h3 class="fw-bold mb-0">Selamat Datang Admin &#x1F44B;</h3>
            <p class="text-muted">Berikut adalah ringkasan aktivitas hari ini.</p>
        </div>
        <div class="col-md-4 text-md-end">
            {{-- Menggunakan Carbon untuk format tanggal Bahasa Indonesia --}}
            <h6 class="text-muted fw-light mb-0"><i class="bx bx-calendar me-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, j F Y') }}</h6>
        </div>
    </div>
    {{-- Baris untuk Kartu Statistik --}}
    <div class="row g-4 mb-4">
        {{-- Card 1: Total Order Selesai Hari Ini --}}
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text text-muted mb-1">Total Order Hari Ini</p>
                            <h3 class="card-title fw-bold mb-0">{{ $summary['total_orders_completed'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar-lg rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center">
                            <i class="bx bx-archive-in fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Card 2: Total Income Hari Ini --}}
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text text-muted mb-1">Total Income Hari Ini</p>
                            <h3 class="card-title fw-bold mb-0">Rp{{ number_format($summary['total_income'] ?? 0, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar-lg rounded-circle bg-light-success text-success d-flex align-items-center justify-content-center">
                            <i class="bx bx-dollar-circle fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Card 3: Porter Online --}}
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text text-muted mb-1">Porter Online</p>
                            <h3 class="card-title fw-bold mb-0">{{ $onlinePorterCount ?? 0 }}</h3>
                        </div>
                        <div class="avatar-lg rounded-circle bg-light-info text-info d-flex align-items-center justify-content-center">
                            <i class="bx bx-user-check fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
{{-- Menambahkan style kustom untuk avatar (lingkaran ikon) --}}
<style>
    .avatar-lg {
        width: 60px;
        height: 60px;
    }
    .bg-light-primary { background-color: rgba(var(--bs-primary-rgb), 0.1) !important; }
    .bg-light-success { background-color: rgba(var(--bs-success-rgb), 0.1) !important; }
    .bg-light-info { background-color: rgba(var(--bs-info-rgb), 0.1) !important; }
</style>
@endpush
