@extends('layouts.app')

@section('title', 'Manajemen Titik Pengiriman')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        {{-- PERUBAHAN: Mengganti kelas 'btn-primary' dengan style inline untuk warna oranye --}}
        <a href="{{ route('dashboard.delivery-points.create') }}" class="btn text-white" style="background-color: #ff7622; border-color: #ff7622;">
            <i class="bx bx-plus me-1"></i> Tambah Titik Pengiriman
        </a>
    </div>

    {{-- Alert Success --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header border-0 bg-white pt-3 pb-0">
            <h5 class="card-title fw-semibold">Daftar Semua Titik Pengiriman</h5>
        </div>
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
                                    {{-- Toggle Status --}}
                                    <form action="{{ route('dashboard.delivery-points.toggle-status', $point->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $point->isActive ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                                                title="{{ $point->isActive ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="bx {{ $point->isActive ? 'bx-toggle-right' : 'bx-toggle-left' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Edit --}}
                                    <a href="{{ route('dashboard.delivery-points.edit', $point->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-pencil"></i>
                                    </a>

                                    {{-- Hapus --}}
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal{{ $point->id }}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>

                                {{-- Modal Hapus --}}
                                <div class="modal fade" id="deleteModal{{ $point->id }}" tabindex="-1"
                                     aria-labelledby="deleteModalLabel{{ $point->id }}" aria-hidden="true">
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="bx bx-map-pin fs-1 d-block mb-2 text-muted"></i>
                                <h5 class="text-muted">Belum Ada Titik Pengiriman</h5>
                                <p class="text-muted">Silakan tambahkan data baru untuk memulai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
