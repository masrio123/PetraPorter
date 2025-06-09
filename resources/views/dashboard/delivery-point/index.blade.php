@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4"><strong>Delivery Point Management</strong></h3>

    <div class="mb-4">
        <a href="{{ route('dashboard.delivery-points.create') }}" class="btn text-white" style="background-color: #ff7622">
            Tambah Delivery Point
        </a>
    </div>

    {{-- Alert Success --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-body shadow-sm overflow-auto">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light text-center">
                <tr>
                    <th>No</th>
                    <th>Nama Delivery Point</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($delivery_points as $key => $point)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td class="text-center">{{ $point->delivery_point_name }}</td>

                        {{-- Status --}}
                        <td class="text-center">
                            @if ($point->isActive)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">

                                {{-- Edit --}}
                                <a href="{{ route('dashboard.delivery-points.edit', $point->id) }}"
                                   class="btn btn-primary d-flex align-items-center gap-1" style="min-width: 90px;">
                                    <i class="fas fa-pen"></i><span>Edit</span>
                                </a>

                                {{-- Hapus --}}
                                <button type="button"
                                        class="btn btn-danger d-flex align-items-center gap-1"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal{{ $point->id }}"
                                        style="min-width: 90px;">
                                    <i class="fas fa-trash"></i><span>Hapus</span>
                                </button>

                                {{-- Toggle Status --}}
                                <form action="{{ route('dashboard.delivery-points.toggle-status', $point->id) }}"
                                      method="POST" style="min-width: 130px;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="btn {{ $point->isActive ? 'btn-secondary' : 'btn-success' }} d-flex align-items-center gap-1 w-100"
                                            title="{{ $point->isActive ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="fas {{ $point->isActive ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        <span>{{ $point->isActive ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                                    </button>
                                </form>
                            </div>

                            {{-- Delete Modal --}}
                            <div class="modal fade" id="deleteModal{{ $point->id }}" tabindex="-1"
                                aria-labelledby="deleteModalLabel{{ $point->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow-lg rounded-4 border-0">
                                        <div class="modal-body text-center p-4">
                                            <div class="mb-3">
                                                <i class="fas fa-triangle-exclamation fa-3x text-danger"></i>
                                            </div>
                                            <h5 class="fw-bold mb-3">Hapus Delivery Point</h5>
                                            <p class="mb-4">
                                                Apakah Anda yakin ingin menghapus delivery point <br>
                                                <strong>{{ $point->delivery_point_name }}</strong>? <br>
                                                Tindakan ini tidak dapat dibatalkan.
                                            </p>
                                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                                <button type="button" class="btn btn-outline-secondary px-4 rounded-pill"
                                                        data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('dashboard.delivery-points.destroy', $point->id) }}"
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
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Tidak ada data delivery point.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
