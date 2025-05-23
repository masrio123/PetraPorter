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
                <h3 class="mb-0"><strong>Edit Porter</strong></h3>
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

            {{-- Form Edit Porter --}}
            <form action="{{ route('dashboard.porters.update', $porter->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="porter_name" class="form-label">Nama Porter</label>
                    <input type="text" name="porter_name" id="porter_name"
                        class="form-control @error('porter_name') is-invalid animate__animated animate__shakeX @enderror"
                        required value="{{ old('porter_name', $porter->porter_name) }}">
                    @error('porter_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="porter_nrp" class="form-label">NRP</label>
                    <input type="text" name="porter_nrp" id="porter_nrp"
                        class="form-control @error('porter_nrp') is-invalid animate__animated animate__shakeX @enderror"
                        required value="{{ old('porter_nrp', $porter->porter_nrp) }}">
                    @error('porter_nrp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="department_id" class="form-label">Departemen</label>
                    <select name="department_id" id="department_id"
                        class="form-select @error('department_id') is-invalid animate__animated animate__shakeX @enderror"
                        required>
                        <option disabled {{ empty(old('department_id', $porter->department_id ?? null)) ? 'selected' : '' }}>
                            -- Pilih Departemen --
                        </option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}"
                                {{ (int) old('department_id', $porter->department_id) === $department->id ? 'selected' : '' }}>
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="bank_user_id" class="form-label">Rekening Porter</label>
                    <select name="bank_user_id" id="bank_user_id"
                        class="form-select @error('bank_user_id') is-invalid animate__animated animate__shakeX @enderror"
                        required>
                        <option disabled {{ empty(old('bank_user_id', $porter->bank_user_id ?? null)) ? 'selected' : '' }}>
                            -- Pilih Rekening --
                        </option>
                        @foreach ($bankUsers as $bankUser)
                            <option value="{{ $bankUser->id }}"
                                {{ (int) old('bank_user_id', $porter->bank_user_id) === $bankUser->id ? 'selected' : '' }}>
                                {{ $bankUser->account_number }} – {{ $bankUser->bank->bank_name ?? 'Bank Tidak Diketahui' }} – {{ $bankUser->username }}
                            </option>
                        @endforeach
                    </select>
                    @error('bank_user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="porter_isOnline" class="form-label">Status Online</label>
                    <select name="porter_isOnline" id="porter_isOnline"
                        class="form-select @error('porter_isOnline') is-invalid animate__animated animate__shakeX @enderror"
                        required>
                        <option value="1" {{ old('porter_isOnline', $porter->porter_isOnline) == 1 ? 'selected' : '' }}>Online</option>
                        <option value="0" {{ old('porter_isOnline', $porter->porter_isOnline) == 0 ? 'selected' : '' }}>Offline</option>
                    </select>
                    @error('porter_isOnline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn text-white" style="background-color: #ff7622">Simpan Perubahan</button>
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
