@extends('layouts.app') {{-- Sesuaikan dengan layout utama Anda --}}

@section('title', 'Tambah Porter Baru')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 fw-bold">
                            <i class="bx bxs-user-detail me-2"></i>Tambah Porter Baru
                        </h4>
                        <a href="{{ route('dashboard.porters.index') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Kembali ke Daftar Porter">
                            <i class="bx bx-left-arrow-alt"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body p-md-5 p-4">
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

                    <form action="{{ route('dashboard.porters.store') }}" method="POST">
                        @csrf
                        
                        {{-- Data Diri Porter --}}
                        <h5 class="mb-3"><strong>Informasi Diri Porter</strong></h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="porter_name" class="form-label">Nama Lengkap Porter</label>
                                <input type="text" name="porter_name" id="porter_name" class="form-control form-control-lg" required value="{{ old('porter_name') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="porter_nrp" class="form-label">NRP</label>
                                <input type="text" name="porter_nrp" id="porter_nrp" class="form-control form-control-lg" required value="{{ old('porter_nrp') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Departemen</label>
                            <select name="department_id" id="department_id" class="form-select form-select-lg">
                                <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>-- Pilih Departemen (Opsional) --</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Informasi Rekening --}}
                        <h5 class="mb-3"><strong>Informasi Rekening Bank</strong></h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="bank_name" class="form-label">Nama Bank</label>
                                <select name="bank_name" id="bank_name" class="form-select form-select-lg" required>
                                    <option value="" disabled selected>-- Pilih Bank --</option>
                                    @php
                                        $banks = ['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB Niaga', 'Danamon', 'PermataBank', 'OCBC NISP', 'Panin Bank', 'BTN'];
                                        sort($banks);
                                    @endphp
                                    @foreach ($banks as $bank)
                                        <option value="{{ $bank }}" {{ old('bank_name') == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="account_numbers" class="form-label">Nomor Rekening</label>
                                <input type="text" name="account_numbers" id="account_numbers" class="form-control form-control-lg" required value="{{ old('account_numbers') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="username" class="form-label">a.n. Pemilik Rekening</label>
                                <input type="text" name="username" id="username" class="form-control form-control-lg" required value="{{ old('username') }}" placeholder="Harus sama dengan nama porter">
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn text-white btn-lg" style="background-color: #ff7622;">
                                <i class="bx bx-plus-circle me-1"></i> Tambah Porter & User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
