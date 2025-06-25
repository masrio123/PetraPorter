@extends('layouts.app') {{-- Menyesuaikan dengan layout utama Anda --}}

@section('title', 'Tambah Tenant Baru')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12">

            {{-- Card utama untuk form --}}
            <div class="card shadow-sm border-0">
                {{-- Header Card --}}
                <div class="card-header bg-white border-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 fw-bold">
                            <i class="bx bx-store-alt me-2"></i>Tambah Tenant Baru
                        </h4>
                        <a href="{{ route('dashboard.tenants.index') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Kembali ke Daftar">
                            <i class="bx bx-left-arrow-alt"></i> Kembali
                        </a>
                    </div>
                </div>

                {{-- Body Card --}}
                <div class="card-body p-md-5 p-4">

                    {{-- Alert untuk Error Validasi --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading fw-bold">Terjadi Kesalahan!</h6>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Form Tambah --}}
                    <form action="{{ route('dashboard.tenants.store') }}" method="POST">
                        @csrf

                        {{-- Input Nama --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Tenant (Juga menjadi Nama User)</label>
                            <input type="text" name="name" id="name" class="form-control form-control-lg" required value="{{ old('name') }}" placeholder="Contoh: Kedai Kopi Senja">
                        </div>

                        {{-- Input Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Login</label>
                            <input type="email" name="email" id="email" class="form-control form-control-lg" required value="{{ old('email') }}" placeholder="contoh@email.com">
                            <div class="form-text">Password default akan dibuat secara otomatis: <strong>tenant123</strong></div>
                        </div>

                        {{-- Input Lokasi Tenant --}}
                        <div class="mb-3">
                            <label for="tenant_location_id" class="form-label">Lokasi Tenant</label>
                            <select name="tenant_location_id" id="tenant_location_id" class="form-select form-select-lg" required>
                                <option value="" disabled selected>-- Pilih Lokasi --</option>
                                @foreach ($tenantLocations as $location)
                                    <option value="{{ $location->id }}" {{ old('tenant_location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->location_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Tombol Submit --}}
                        <div class="d-grid mt-6">
                            <button type="submit" class="btn text-white btn-lg" style="background-color: #ff7622;">
                                <i class="bx bx-plus-circle me-1"></i> Tambah Tenant & User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Inisialisasi Tooltip Bootstrap jika ada
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endpush
