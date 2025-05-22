@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4"><strong>Porter Management</strong></h3>

        <div class="mb-4">
            <a href="{{ route('dashboard.porters.create') }}" class="btn text-white" style="background-color: #ff7622;">
                Tambah Porter
            </a>
        </div>

        <div class="card card-body shadow-sm overflow-auto">
            <table class="table table-bordered table-hover align-middle">
                <thead class="text-center table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Porter</th>
                        <th>NRP</th>
                        <th>Jurusan</th>
                        <th>No. Rekening</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($porters as $key => $porter)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td class="text-center">{{ $porter->porter_name }}</td>
                            <td class="text-center">{{ $porter->porter_nrp }}</td>
                            <td class="text-center">{{ $porter->department->department_name ?? '-' }}</td>
                            <td class="text-center">{{ $porter->bankUser->account_number ?? '-' }}</td>
                            <td class="text-center">{{ $porter->porter_rating ?? '-' }}</td>
                            <td class="text-center">
                                @if ($porter->porter_isOnline)
                                    <span class="d-inline-flex align-items-center gap-2 text-success fw-semibold">
                                        <span class="rounded-circle"
                                            style="width: 10px; height: 10px; background-color: #28a745;"></span>
                                        Online
                                    </span>
                                @else
                                    <span class="d-inline-flex align-items-center gap-2 text-danger fw-semibold">
                                        <span class="rounded-circle"
                                            style="width: 10px; height: 10px; background-color: #dc3545;"></span>
                                        Offline
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- Edit --}}
                                    <a href="{{ route('dashboard.porters.edit', $porter->id) }}"
                                        class="btn text-white d-flex align-items-center justify-content-center px-3 py-3"
                                        style="height: 32px; background-color: blue;">
                                        <i class="fas fa-pen me-1" style="font-size: 0.85rem;"></i>
                                        <span style="font-size: 0.85rem;">Edit</span>
                                    </a>

                                    {{-- Hapus --}}
                                    <button type="button"
                                        class="btn text-white d-flex align-items-center justify-content-center px-3 py-3"
                                        style="height: 32px; background-color: red;" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal{{ $porter->id }}">
                                        <i class="fas fa-trash me-1" style="font-size: 0.85rem;"></i>
                                        <span style="font-size: 0.85rem;">Hapus</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Delete Modal --}}
                        <div class="modal fade" id="deleteModal{{ $porter->id }}" tabindex="-1"
                            aria-labelledby="deleteModalLabel{{ $porter->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content shadow-lg rounded-4 border-0">
                                    <div class="modal-body text-center p-4">
                                        <div class="mb-3">
                                            <i class="fas fa-triangle-exclamation fa-3x text-danger"></i>
                                        </div>
                                        <h5 class="fw-bold mb-3">Hapus Porter</h5>
                                        <p class="mb-4">
                                            Apakah Anda yakin ingin menghapus porter <br>
                                            <strong>{{ $porter->porter_name }}</strong>? <br> Tindakan ini tidak dapat
                                            dibatalkan.
                                        </p>
                                        <div class="d-flex justify-content-center gap-3">
                                            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill"
                                                data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('dashboard.porters.destroy', $porter->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-danger px-4 rounded-pill">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-user-slash fa-lg mb-2 d-block"></i>
                                Tidak ada data porter yang tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
