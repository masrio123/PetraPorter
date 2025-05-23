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
                <h3 class="mb-0"><strong>Edit Bank User</strong></h3>
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

            {{-- Form Edit Bank User --}}
            <form action="{{ route('dashboard.bank-users.update', $bank_user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username"
                        class="form-control @error('username') is-invalid animate__animated animate__shakeX @enderror"
                        required value="{{ old('username', $bank_user->username) }}">
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="account_number" class="form-label">Nomor Rekening</label>
                    <input type="text" name="account_number" id="account_number"
                        class="form-control @error('account_number') is-invalid animate__animated animate__shakeX @enderror"
                        required value="{{ old('account_number', $bank_user->account_number) }}">
                    @error('account_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="bank_id" class="form-label">Nama Bank</label>
                    <select name="bank_id" id="bank_id"
                        class="form-select @error('bank_id') is-invalid animate__animated animate__shakeX @enderror"
                        required>
                        <option disabled {{ empty(old('bank_id', $bank_user->bank_id ?? null)) ? 'selected' : '' }}>
                            -- Pilih Bank --
                        </option>
                        @foreach ($banks as $bank)
                            <option value="{{ $bank->id }}"
                                {{ (int) old('bank_id', $bank_user->bank_id) === $bank->id ? 'selected' : '' }}>
                                {{ $bank->bank_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('bank_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn text-white" style="background-color: #ff7622">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</div>

@if (session()->has('success'))
    <div class="modal fade show" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-modal="true"
        style="display: block; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Berhasil</h5>
                    <a href="{{ url()->current() }}" class="btn-close"></a>
                </div>
                <div class="modal-body">
                    {{ session('success') }}
                </div>
                <div class="modal-footer">
                    <a href="{{ url()->current() }}" class="btn btn-success">Tutup</a>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Modal animasi buka tutup buku full layar abu2 --}}
<div class="modal fade" id="bookModal" tabindex="-1" aria-hidden="true" style="background-color: #d9d9d9;">
  <div class="modal-dialog modal-dialog-centered justify-content-center" style="max-width: 160px;">
    <div class="modal-content border-0 bg-transparent shadow-none">
      <div class="modal-body d-flex justify-content-center align-items-center p-0">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" id="bookLogo" style="width: 160px; height: auto; backface-visibility: hidden;">
      </div>
    </div>
  </div>
</div>

@endsection
