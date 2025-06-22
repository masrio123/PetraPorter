@extends('layouts.createLayout')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12 card card-body">

            {{-- Header dengan tombol back --}}
            <div class="d-flex align-items-center gap-3 mb-4 border-bottom-0">
                <a href="{{ route('dashboard.porters.index') }}" class="btn btn-outline-secondary btn-sm btn-back">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h3 class="mb-0"><strong>Tambah Porter</strong></h3>
            </div>

            {{-- Alert Error --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Form Tambah Porter --}}
            <form action="{{ route('dashboard.porters.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="porter_name" class="form-label">Nama Porter</label>
                    <input type="text" name="porter_name" id="porter_name"
                        class="form-control @error('porter_name') is-invalid animate__animated animate__shakeX @enderror"
                        required value="{{ old('porter_name') }}">
                    @error('porter_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="porter_nrp" class="form-label">NRP</label>
                    <input type="text" name="porter_nrp" id="porter_nrp"
                        class="form-control @error('porter_nrp') is-invalid animate__animated animate__shakeX @enderror"
                        required value="{{ old('porter_nrp') }}">
                    @error('porter_nrp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="department_id" class="form-label">Departemen</label>
                    <select name="department_id" id="department_id"
                        class="form-select @error('department_id') is-invalid animate__animated animate__shakeX @enderror">
                        <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>-- Pilih Departemen --</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Kolom baru untuk info rekening bank --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="bank_name" class="form-label">Nama Bank</label>
                        <select name="bank_name" id="bank_name"
                            class="form-select @error('bank_name') is-invalid animate__animated animate__shakeX @enderror" required>
                            <option value="" disabled {{ old('bank_name') ? '' : 'selected' }}>-- Pilih Bank --</option>
                            @php
                                $banks = ['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB Niaga', 'Danamon', 'PermataBank', 'OCBC NISP', 'Panin Bank', 'BTN'];
                                sort($banks);
                            @endphp
                            @foreach ($banks as $bank)
                                <option value="{{ $bank }}" {{ old('bank_name') == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                            @endforeach
                        </select>
                        @error('bank_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="account_numbers" class="form-label">Nomor Rekening</label>
                        <input type="text" name="account_numbers" id="account_numbers"
                            class="form-control @error('account_numbers') is-invalid animate__animated animate__shakeX @enderror"
                            required value="{{ old('account_numbers') }}">
                        @error('account_numbers')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="username" class="form-label">a.n. Pemilik Rekening</label>
                        <input type="text" name="username" id="username"
                            class="form-control @error('username') is-invalid animate__animated animate__shakeX @enderror"
                            required value="{{ old('username') }}">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="porter_isOnline" class="form-label">Status Online</label>
                    <select name="porter_isOnline" id="porter_isOnline"
                        class="form-select @error('porter_isOnline') is-invalid animate__animated animate__shakeX @enderror"
                        required>
                        <option value="1" {{ old('porter_isOnline', '1') == '1' ? 'selected' : '' }}>Online</option>
                        <option value="0" {{ old('porter_isOnline') == '0' ? 'selected' : '' }}>Offline</option>
                    </select>
                    @error('porter_isOnline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn text-white" style="background-color: #ff7622">Tambah Porter</button>
            </form>
        </div>
    </div>
</div>
@endsection
