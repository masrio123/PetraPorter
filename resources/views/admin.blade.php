@extends('layouts.app')

@section('content')
<div class="container mt-8">

    {{-- Judul Halaman --}}
    <h3 class="mb-4">Manajemen Tenant</h3>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tampilan Awal: 4 Tombol Gedung --}}
    <div id="location-buttons" class="row text-center">
        @foreach ($locations as $location)
            <div class="col-md-6 col-lg-3 mb-4">
                <button class="btn btn-primary btn-lg w-100 py-4"
                    onclick="showLocation({{ $location->id }})">
                    {{ $location->location_name }}
                </button>
            </div>
        @endforeach
    </div>

    {{-- Tampilan Detail Tiap Gedung --}}
    @foreach ($locations as $location)
        <div id="location-{{ $location->id }}" class="location-section" style="display: none;">
            <div class="d-flex align-items-center mb-3 gap-3">
                <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center p-1" style="width: 36px; height: 36px; border-radius: 50%;" onclick="goBack()" aria-label="Kembali">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H3.707l4.147 4.146a.5.5 0 0 1-.708.708l-5-5a.5.5 0 0 1 0-.708l5-5a.5.5 0 1 1 .708.708L3.707 7.5H14.5A.5.5 0 0 1 15 8z"/>
                    </svg>
                </button>
                <h4 class="mb-0">Tenant {{ $location->location_name }}</h4>
            </div>

            {{-- Tombol Tambah --}}
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal{{ $location->id }}">
                Tambah Tenant
            </button>

            {{-- Tabel Tenant --}}
            <table class="table table-bordered table-striped align-middle" style="table-layout: fixed; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 50%; text-align: center;">Nama</th>
                        <th style="width: 20%; text-align: center;">Status</th>
                        <th style="width: 30%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($location->tenants as $tenant)
                        <tr>
                            <td class="text-truncate" title="{{ $tenant->name }}" style="text-align: left;">{{ $tenant->name }}</td>
                            <td class="text-center">
                                @if ($tenant->isOpen)
                                    <span class="badge bg-success">Buka</span>
                                @else
                                    <span class="badge bg-secondary">Tutup</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editModal{{ $tenant->id }}">Edit</button>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $tenant->id }}">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center">Belum ada tenant di gedung ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modal Tambah Tenant --}}
        <div class="modal fade" id="addModal{{ $location->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('tenants.store') }}" method="POST" class="modal-content">
                    @csrf
                    <input type="hidden" name="tenant_location_id" value="{{ $location->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Tenant - {{ $location->location_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Nama Tenant</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="isOpen" value="1" checked>
                            <label class="form-check-label">Buka</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-primary" type="submit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- Modal Edit --}}
    @foreach ($locations as $location)
        @foreach ($location->tenants as $tenant)
            <div class="modal fade" id="editModal{{ $tenant->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('tenants.update', $tenant->id) }}" method="POST" class="modal-content">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Tenant</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Nama Tenant</label>
                                <input type="text" name="name" class="form-control" value="{{ $tenant->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label>Lokasi</label>
                                <select name="tenant_location_id" class="form-control" required>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}" {{ $tenant->tenant_location_id == $loc->id ? 'selected' : '' }}>
                                            {{ $loc->location_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="isOpen" value="1" {{ $tenant->isOpen ? 'checked' : '' }}>
                                <label class="form-check-label">Buka</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-primary" type="submit">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endforeach

    {{-- Modal Delete --}}
    @foreach ($locations as $location)
        @foreach ($location->tenants as $tenant)
            <div class="modal fade" id="deleteModal{{ $tenant->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('tenants.destroy', $tenant->id) }}" method="POST" class="modal-content">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header">
                            <h5 class="modal-title text-danger">Konfirmasi Hapus</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <p>Yakin ingin menghapus tenant <strong>{{ $tenant->name }}</strong>?</p>
                            <p class="text-warning"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-danger" type="submit">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endforeach

</div>

{{-- Script --}}
<script>
    function showLocation(id) {
        document.getElementById('location-buttons').style.display = 'none';
        document.querySelectorAll('.location-section').forEach(el => el.style.display = 'none');
        document.getElementById('location-' + id).style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function goBack() {
        document.getElementById('location-buttons').style.display = 'flex';
        document.querySelectorAll('.location-section').forEach(el => el.style.display = 'none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Fungsi untuk ambil parameter dari URL
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const locationParam = getQueryParam('location');
        if(locationParam) {
            showLocation(locationParam);
        }
    });
</script>
@endsection
