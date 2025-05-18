@extends('layouts.createLayout')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-12 card card-body">

                {{-- Header dengan tombol back dan judul tanpa border bawah --}}
                <div class="d-flex align-items-center gap-3 mb-4 border-bottom-0">
                    <a href="{{ route('dashboard.tenants.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h3 class="mb-0">Edit Tenant</h3>
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

                {{-- Form Edit Tenant --}}
                <form action="{{ route('dashboard.tenants.update', $tenant->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="tenant_name" class="form-label">Nama Tenant</label>
                        <input type="text" name="name" id="tenant_name" class="form-control" required
                            value="{{ old('name', $tenant->name) }}">
                    </div>

                    <div class="mb-3">
                        <label for="tenant_location_id" class="form-label">Lokasi</label>
                        <select name="tenant_location_id" id="tenant_location_id" class="form-select" required>
                            <option disabled {{ empty(old('tenant_location_id', $tenant->tenant_location_id ?? null)) ? 'selected' : '' }}>
                                -- Pilih Lokasi --
                            </option>
                            @foreach ($tenantLocations as $location)
                                <option value="{{ $location->id }}" 
                                    {{ (int) old('tenant_location_id', $tenant->tenant_location_id) === $location->id ? 'selected' : '' }}>
                                    {{ $location->location_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="isOpen" class="form-label">Status</label>
                        <select name="isOpen" id="isOpen" class="form-select" required>
                            <option value="1" {{ old('isOpen', $tenant->isOpen) == 1 ? 'selected' : '' }}>Buka</option>
                            <option value="0" {{ old('isOpen', $tenant->isOpen) == 0 ? 'selected' : '' }}>Tutup</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
