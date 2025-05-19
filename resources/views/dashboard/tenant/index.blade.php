@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Tenant Management</h3>

    @php
        $groupedTenants = $tenants->groupBy('location');
        $locations = $groupedTenants->keys();
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        {{-- Tabs Gedung --}}
        <ul class="nav nav-pills mb-0" id="locationTabs" role="tablist">
            @foreach ($locations as $index => $location)
                <li class="nav-item me-2" role="presentation">
                    <button 
                        class="nav-link @if ($index === 0) active @endif" 
                        id="tab-{{ Str::slug($location) }}" 
                        data-bs-toggle="pill" 
                        data-bs-target="#pane-{{ Str::slug($location) }}" 
                        type="button" 
                        role="tab" 
                        aria-controls="pane-{{ Str::slug($location) }}" 
                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                        {{ $location }}
                    </button>
                </li>
            @endforeach
        </ul>

        {{-- Tombol Tambah Tenant --}}
        <a href="{{ route('dashboard.tenants.create') }}" class="btn btn-warning text-white">
            Tambah Tenant
        </a>
    </div>

    {{-- Isi Tab --}}
    <div class="tab-content" id="locationTabsContent">
        @forelse ($groupedTenants as $location => $tenantGroup)
            <div class="tab-pane fade @if ($loop->first) show active @endif"
                id="pane-{{ Str::slug($location) }}" role="tabpanel" aria-labelledby="tab-{{ Str::slug($location) }}">
                
                <div class="card shadow-sm overflow-auto">
                    <div class="card-body">
                        <h5 class="fw-bold text-uppercase mb-3">{{ $location }}</h5>

                        <table class="table table-bordered table-hover align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 40%;">Nama Tenant</th>
                                    <th style="width: 20%;">Status</th>
                                    <th style="width: 35%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tenantGroup as $index => $tenant)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $tenant->name }}</td>
                                        <td>
                                            @if ($tenant->isOpen)
                                                <span class="badge bg-success rounded-pill px-3 py-2">
                                                    <i class="fas fa-door-open me-1"></i> Buka
                                                </span>
                                            @else
                                                <span class="badge bg-danger rounded-pill px-3 py-2">
                                                    <i class="fas fa-door-closed me-1"></i> Tutup
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                {{-- Edit --}}
                                                <a href="{{ route('dashboard.tenants.edit', $tenant->id) }}"
                                                    class="btn btn-warning d-flex align-items-center justify-content-center px-3 py-1 rounded-pill"
                                                    style="height: 32px;">
                                                    <i class="fas fa-pen me-1" style="font-size: 0.85rem;"></i>
                                                    <span style="font-size: 0.85rem;">Edit</span>
                                                </a>

                                                {{-- Hapus --}}
                                                <button type="button" 
                                                    class="btn btn-danger d-flex align-items-center justify-content-center px-3 py-1 rounded-pill" 
                                                    style="height: 32px;" data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal{{ $tenant->id }}">
                                                    <i class="fas fa-trash me-1" style="font-size: 0.85rem;"></i>
                                                    <span style="font-size: 0.85rem;">Hapus</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Modal Hapus --}}
                                    <div class="modal fade" id="deleteModal{{ $tenant->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $tenant->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content shadow-lg rounded-4 border-0">
                                                <div class="modal-body text-center p-4">
                                                    <div class="mb-3">
                                                        <i class="fas fa-triangle-exclamation fa-3x text-danger"></i>
                                                    </div>
                                                    <h5 class="mb-3 fw-bold">Hapus Tenant</h5>
                                                    <p class="mb-4">
                                                        Apakah Anda yakin ingin menghapus tenant
                                                        <strong>{{ $tenant->name }}</strong>? Tindakan ini tidak dapat dibatalkan.
                                                    </p>
                                                    <div class="d-flex justify-content-center gap-3">
                                                        <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Batal</button>
                                                        <form action="{{ route('dashboard.tenants.destroy', $tenant->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger px-4 rounded-pill">Hapus</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-store-slash fa-lg mb-2 d-block"></i>
                Tidak ada data tenant yang tersedia.
            </div>
        @endforelse
    </div>
</div>
@endsection
