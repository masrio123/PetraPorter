@extends('layouts.app')

@section('title', 'Recycle Bin Tenant')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('dashboard.tenants.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i>
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bx bx-error-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header border-0 bg-white pt-3 pb-0">
            <h5 class="card-title fw-semibold">Daftar Tenant yang Dinonaktifkan</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Nama Tenant</th>
                        <th>Alasan Dinonaktifkan</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($trashedTenants as $index => $tenant)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td><strong>{{ $tenant->name }}</strong></td>
                            <td>{{ $tenant->deletion_reason ?? 'Tidak ada alasan.' }}</td>
                            <td class="text-center">{{ $tenant->deleted_at->format('d F Y, H:i') }}</td>
                            <td class="text-center">
                                {{-- Tombol Restore adalah satu-satunya aksi yang tersedia --}}
                                <form action="{{ route('dashboard.tenants.restore', $tenant->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    {{-- Laravel 9+ bisa pakai @method('POST'), atau biarkan saja karena defaultnya POST --}}
                                    <button type="submit" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Pulihkan Tenant">
                                        <i class="bx bx-revision"></i> Pulihkan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted p-5">
                                <i class="bx bx-check-double fs-2 d-block mb-2"></i>
                                No Delete History
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
