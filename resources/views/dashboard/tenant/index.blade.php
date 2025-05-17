@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">Tenant Management</h3>

        <div class="mb-4">
            <a href="{{ route('dashboard.tenants.create') }}" class="btn btn-primary">
                Tambah Tenant
            </a>
        </div>

        <div class="card card-body shadow-sm overflow-auto">
            <table class="table table-bordered table-hover align-middle">
                <thead class="text-center table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Tenant</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenants as $key => $tenant)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td class="text-center">{{ $tenant->name }}</td>
                            <td class="text-center">{{ $tenant->location }}</td>
                            <td class="text-center">
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
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('dashboard.tenants.edit', $tenant->id) }}"
                                        class="btn btn-primary d-flex align-items-center justify-content-center px-3 py-1 rounded-pill"
                                        style="height: 32px;">
                                        <i class="fas fa-pen me-1" style="font-size: 0.85rem;"></i>
                                        <span style="font-size: 0.85rem;">Edit</span>
                                    </a>

                                    {{-- Tombol Hapus --}}
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
                        <div class="modal fade" id="deleteModal{{ $tenant->id }}" tabindex="-1"
                            aria-labelledby="deleteModalLabel{{ $tenant->id }}" aria-hidden="true">
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
                                            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill"
                                                data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('dashboard.tenants.destroy', $tenant->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger px-4 rounded-pill">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-store-slash fa-lg mb-2 d-block"></i>
                                Tidak ada data tenant yang tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
