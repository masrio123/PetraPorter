@extends('layouts.app')

@section('title', 'Manajemen Titik Pengiriman')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <button type="button" class="btn text-white" style="background-color: #ff7622;" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bx bx-plus me-1"></i> Tambah Titik Pengiriman
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading">Terjadi Kesalahan!</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 5%;">No</th>
                        <th>Nama Titik Pengiriman</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($delivery_points as $key => $point)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td><strong>{{ $point->delivery_point_name }}</strong></td>
                            <td class="text-center">
                                @if ($point->isActive)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-2">
                                    {{-- Tombol Toggle Status --}}
                                    {{-- PERBAIKAN: Tambahkan class "d-inline" di sini --}}
                                    <form action="{{ route('dashboard.delivery-points.toggle-status', $point->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $point->isActive ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $point->isActive ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="bx {{ $point->isActive ? 'bx-toggle-right' : 'bx-toggle-left' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Tombol Edit (membuka modal) --}}
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $point->id }}" title="Edit">
                                        <i class="bx bx-pencil"></i>
                                    </button>

                                    {{-- Tombol Hapus (membuka modal) --}}
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $point->id }}" title="Hapus">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="bx bx-map-pin fs-1 d-block mb-2 text-muted"></i>
                                <h5 class="text-muted">Belum Ada Titik Pengiriman</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            @if ($delivery_points instanceof \Illuminate\Pagination\LengthAwarePaginator)
                {{ $delivery_points->links() }}
            @endif
        </div>
    </div>

    {{-- MODAL CREATE --}}
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('dashboard.delivery-points.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createModalLabel">Tambah Titik Pengiriman</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="delivery_point_name_create" class="form-label">Nama Titik Pengiriman</label>
                            <input type="text" name="delivery_point_name" id="delivery_point_name_create" class="form-control" required value="{{ old('delivery_point_name') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white" style="background-color: #ff7622;">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT DAN HAPUS --}}
    @foreach ($delivery_points as $point)
        {{-- MODAL EDIT --}}
        <div class="modal fade" id="editModal{{ $point->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $point->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('dashboard.delivery-points.update', $point->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel{{ $point->id }}">Edit Titik Pengiriman</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="delivery_point_name_{{ $point->id }}" class="form-label">Nama Titik Pengiriman</label>
                                <input type="text" name="delivery_point_name" id="delivery_point_name_{{ $point->id }}" class="form-control" required value="{{ old('delivery_point_name', $point->delivery_point_name) }}">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn text-white" style="background-color: #ff7622;">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL HAPUS --}}
        <div class="modal fade" id="deleteModal{{ $point->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $point->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel{{ $point->id }}">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus titik pengiriman <strong>{{ $point->delivery_point_name }}</strong>? <br>Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <form action="{{ route('dashboard.delivery-points.destroy', $point->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection