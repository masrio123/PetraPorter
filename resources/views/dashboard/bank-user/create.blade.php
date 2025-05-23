@extends('layouts.createLayout')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12 card card-body">

            {{-- Header dengan tombol back --}}
            <div class="d-flex align-items-center gap-3 mb-4 border-bottom-0">
                <a href="{{ route('dashboard.bank-users.index') }}" class="btn btn-outline-secondary btn-sm btn-back">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h3 class="mb-0"><strong>Tambah Bank User</strong></h3>
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

            {{-- Form Tambah Bank User --}}
            <form action="{{ route('dashboard.bank-users.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username"
                        class="form-control @error('username') is-invalid animate__animated animate__shakeX @enderror"
                        required value="{{ old('username') }}">
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="account_number" class="form-label">Nomor Rekening</label>
                    <input type="text" name="account_number" id="account_number"
                        class="form-control @error('account_number') is-invalid animate__animated animate__shakeX @enderror"
                        required value="{{ old('account_number') }}">
                    @error('account_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="bank_id" class="form-label">Nama Bank</label>
                    <select name="bank_id" id="bank_id"
                        class="form-select @error('bank_id') is-invalid animate__animated animate__shakeX @enderror"
                        required>
                        <option value="" disabled {{ old('bank_id') ? '' : 'selected' }}>-- Pilih Bank --</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                                {{ $bank->bank_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('bank_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn text-white" style="background-color: #ff7622">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection
