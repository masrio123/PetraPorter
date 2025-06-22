@extends('layouts.app')

@section('title', 'Manajemen Tenant')

@section('content')
    <div class="container">
        @php
            $groupedTenants = $tenants->groupBy('location');
            $locations = $groupedTenants->keys();
        @endphp

        {{-- Baris tab lokasi dan tombol tambah tenant --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            {{-- Tabs Gedung --}}
            <ul class="nav nav-pills mb-0" id="locationTabs" role="tablist" style="flex-grow: 1;">
                @foreach ($locations as $index => $location)
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link @if ($index === 0) active @endif"
                            id="tab-{{ Str::slug($location) }}" data-bs-toggle="pill"
                            data-bs-target="#pane-{{ Str::slug($location) }}" type="button" role="tab"
                            aria-controls="pane-{{ Str::slug($location) }}"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                            {{ $location }}
                        </button>
                    </li>
                @endforeach
            </ul>

            {{-- Tombol Tambah Tenant di kanan sejajar --}}
            <div class="ms-3">
                <a href="{{ route('dashboard.tenants.create') }}" class="btn text-white" style="background-color: #ff7622;">
                    <i class="bx bx-plus me-1"></i> Tambah Tenant
                </a>
            </div>
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bx bx-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Isi Tab --}}
        <div class="tab-content" id="locationTabsContent">
            @forelse ($groupedTenants as $location => $tenantGroup)
                <div class="tab-pane fade @if ($loop->first) show active @endif"
                    id="pane-{{ Str::slug($location) }}" role="tabpanel" aria-labelledby="tab-{{ Str::slug($location) }}">

                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-0 bg-white pt-3 pb-0">
                             <h5 class="card-title fw-semibold">Daftar Tenant di {{ $location }}</h5>
                        </div>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 5%;">No</th>
                                        <th>Nama Tenant</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @foreach ($tenantGroup as $index => $tenant)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td><strong>{{ $tenant->name }}</strong></td>
                                            <td class="text-center">
                                                @if ($tenant->isOpen)
                                                    <span class="badge bg-label-success d-inline-flex align-items-center">
                                                        <i class="bx bx-check-circle me-1"></i>Buka
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-danger d-inline-flex align-items-center">
                                                        <i class="bx bx-x-circle me-1"></i>Tutup
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-2">
                                                    {{-- Edit --}}
                                                    <a href="{{ route('dashboard.tenants.edit', $tenant->id) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-pencil"></i>
                                                    </a>

                                                    {{-- Hapus --}}
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal{{ $tenant->id }}">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Modal Hapus --}}
                                        <div class="modal fade" id="deleteModal{{ $tenant->id }}" tabindex="-1"
                                            aria-labelledby="deleteModalLabel{{ $tenant->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel{{ $tenant->id }}">Konfirmasi Hapus</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Apakah Anda yakin ingin menghapus tenant <strong>{{ $tenant->name }}</strong>? <br>Tindakan ini tidak dapat dibatalkan.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <form action="{{ route('dashboard.tenants.destroy', $tenant->id) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                                        </form>
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
                    <i class="bx bx-store-alt fs-1 d-block mb-2"></i>
                    <h4>Belum Ada Data Tenant</h4>
                    <p>Silakan tambahkan tenant baru untuk memulai.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
