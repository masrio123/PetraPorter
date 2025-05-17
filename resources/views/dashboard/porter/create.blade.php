@extends('layouts.createLayout')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-12 card card-body">
                <h3 class="mb-4">Tambah Porter</h3>

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
                        <input type="text" name="porter_name" id="porter_name" class="form-control" required
                            value="{{ old('porter_name') }}">
                    </div>

                    <div class="mb-3">
                        <label for="porter_nrp" class="form-label">NRP</label>
                        <input type="text" name="porter_nrp" id="porter_nrp" class="form-control" required
                            value="{{ old('porter_nrp') }}">
                    </div>

                    <div class="mb-3">
                        <label for="department_id" class="form-label">Departemen</label>
                        <select name="department_id" id="department_id" class="form-select" required>
                            <option disabled {{ old('department_id', $porter->department_id ?? '') ? '' : 'selected' }}>--
                                Pilih Departemen --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id', $porter->department_id ?? '') == $department->id ? 'selected' : '' }}>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="porter_account_number" class="form-label">Nomor Rekening</label>
                        <input type="text" name="porter_account_number" id="porter_account_number" class="form-control"
                            required value="{{ old('porter_account_number') }}">
                    </div>

                    <div class="mb-4">
                        <label for="porter_isOnline" class="form-label">Status Online</label>
                        <select name="porter_isOnline" id="porter_isOnline" class="form-select" required>
                            <option value="1" {{ old('porter_isOnline') == '1' ? 'selected' : '' }}>Online</option>
                            <option value="0" {{ old('porter_isOnline') == '0' ? 'selected' : '' }}>Offline</option>
                        </select>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary w-45">Tambah</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal sukses --}}
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
@endsection
