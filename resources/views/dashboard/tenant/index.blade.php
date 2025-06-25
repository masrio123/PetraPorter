@extends('layouts.app')

@section('title', 'Manajemen Tenant')

@section('content')
<div class="container">
    {{-- Pengingat Penting untuk Controller --}}
    {{-- Pastikan di Controller Anda memuat data: 
        $tenants = Tenant::with('products', 'tenantLocation')->latest()->get();
        $tenantLocations = TenantLocation::all();
    --}}
    
    @php
        $groupedTenants = $tenants->groupBy('tenantLocation.location_name');
        $locations = $groupedTenants->keys()->filter();
    @endphp

    {{-- Baris tab lokasi dan tombol aksi utama --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        {{-- Tabs Gedung --}}
        <ul class="nav nav-pills mb-0" id="locationTabs" role="tablist" style="flex-grow: 1;">
            @foreach ($locations as $index => $location)
                <li class="nav-item me-2" role="presentation">
                    <button class="nav-link @if ($index === 0) active @endif" id="tab-{{ Str::slug($location) }}" data-bs-toggle="pill" data-bs-target="#pane-{{ Str::slug($location) }}" type="button" role="tab" aria-controls="pane-{{ Str::slug($location) }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                        {{ $location }}
                    </button>
                </li>
            @endforeach
        </ul>

        {{-- Grup Tombol Aksi --}}
        <div class="ms-3 d-flex gap-2">
            <a href="{{ route('dashboard.tenants.trashed') }}" class="btn btn-outline-secondary">
                <i class="bx bx-trash-alt me-1"></i> Delete History
            </a>
            <a href="{{ route('dashboard.tenants.create') }}" class="btn text-white" style="background-color: #ff7622;">
                <i class="bx bx-plus me-1"></i> Tambah Tenant
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
         <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <h6 class="alert-heading">Terjadi Kesalahan!</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Konten Tab --}}
    <div class="tab-content" id="locationTabsContent">
        @forelse ($groupedTenants as $location => $tenantGroup)
            <div class="tab-pane fade @if ($loop->first) show active @endif" id="pane-{{ Str::slug($location) }}" role="tabpanel" aria-labelledby="tab-{{ Str::slug($location) }}">
                
                {{-- Bagian 1: Tabel Tenant --}}
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
                                    <tr data-bs-toggle="modal" data-bs-target="#menuModal{{ $tenant->id }}" style="cursor: pointer;">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><strong>{{ $tenant->name }}</strong></td>
                                        <td class="text-center">
                                            @if ($tenant->isOpen)
                                                <span class="badge bg-label-success d-inline-flex align-items-center"><i class="bx bx-check-circle me-1"></i>Buka</span>
                                            @else
                                                <span class="badge bg-label-danger d-inline-flex align-items-center"><i class="bx bx-x-circle me-1"></i>Tutup</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-inline-flex gap-2" onclick="event.stopPropagation();">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $tenant->id }}" title="Edit Tenant"><i class="bx bx-pencil"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $tenant->id }}" title="Hapus Tenant"><i class="bx bx-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Bagian 2: Semua Modal (ditempatkan di luar tabel) --}}
                @foreach ($tenantGroup as $tenant)
                    {{-- Modal Daftar Menu --}}
                    <div class="modal fade" id="menuModal{{ $tenant->id }}" tabindex="-1" aria-labelledby="menuModalLabel{{ $tenant->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="menuModalLabel{{ $tenant->id }}"><i class="bx bx-food-menu me-2"></i>Daftar Menu: <strong>{{ $tenant->name }}</strong></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table">
                                        <thead><tr><th>Nama Produk</th><th class="text-end">Harga</th></tr></thead>
                                        <tbody>
                                            @forelse ($tenant->products as $product)
                                                <tr><td>{{ $product->name }}</td><td class="text-end">Rp {{ number_format($product->price, 0, ',', '.') }}</td></tr>
                                            @empty
                                                <tr><td colspan="2" class="text-center text-muted p-4"><i class="bx bx-info-circle fs-4 d-block mb-2"></i>Tenant ini belum memiliki produk.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Edit Tenant --}}
                    <div class="modal fade" id="editModal{{ $tenant->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $tenant->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('dashboard.tenants.update', $tenant->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header"><h5 class="modal-title" id="editModalLabel{{ $tenant->id }}">Edit Tenant</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="name_{{ $tenant->id }}" class="form-label">Nama Tenant</label>
                                            <input type="text" name="name" id="name_{{ $tenant->id }}" class="form-control" value="{{ old('name', $tenant->name) }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tenant_location_id_{{ $tenant->id }}" class="form-label">Lokasi</label>
                                            <select name="tenant_location_id" id="tenant_location_id_{{ $tenant->id }}" class="form-select" required>
                                                @foreach ($tenantLocations as $loc)
                                                    <option value="{{ $loc->id }}" @selected(old('tenant_location_id', $tenant->tenant_location_id) == $loc->id)>{{ $loc->location_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn text-white" style="background-color: #ff7622">Simpan Perubahan</button></div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Hapus Tenant --}}
                    <div class="modal fade" id="deleteModal{{ $tenant->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $tenant->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('dashboard.tenants.destroy', $tenant->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-header"><h5 class="modal-title" id="deleteModalLabel{{ $tenant->id }}">Konfirmasi Menonaktifkan Tenant</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                                    <div class="modal-body">
                                        <p>Anda akan menonaktifkan tenant <strong>{{ $tenant->name }}</strong>.</p>
                                        <div class="mb-3">
                                            <label for="deletion_reason_{{ $tenant->id }}" class="form-label">Alasan Penonaktifan (Wajib Diisi)</label>
                                            <textarea name="deletion_reason" id="deletion_reason_{{ $tenant->id }}" class="form-control" rows="3" required placeholder="Contoh: Tenant sudah tutup permanen..."></textarea>
                                        </div>  
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Ya, Nonaktifkan</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="text-center text-muted py-5"><i class="bx bx-store-alt fs-1 d-block mb-2"></i><h4>Belum Ada Data Tenant</h4><p>Silakan tambahkan tenant baru untuk memulai.</p></div>
        @endforelse
    </div>
</div>
@endsection
