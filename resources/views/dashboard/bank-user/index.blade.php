@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4"><strong>Bank Account Management</strong></h3>

    <div class="mb-4">
        <a href="{{ route('dashboard.bank-users.create') }}" class="btn text-white" style="background-color: #ff7622;">
            Tambah Bank User
        </a>
    </div>

    {{-- Alert Success --}}
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Alert Error --}}
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card card-body shadow-sm overflow-auto">
        <table class="table table-bordered table-hover align-middle">
            <thead class="text-center table-light">
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Nomor Rekening</th>
                    <th>Nama Bank</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bank_users as $key => $user)
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td class="text-center">{{ $user->username }}</td>
                    <td class="text-center">{{ $user->account_number }}</td>
                    <td class="text-center">{{ $user->bank?->bank_name ?? 'Tidak ada bank' }}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            {{-- Tombol Edit --}}
                            <a href="{{ route('dashboard.bank-users.edit', $user->id) }}"
                                class="btn text-white d-flex align-items-center justify-content-center px-3 py-2"
                                style="height: 32px; background-color: blue;">
                                <i class="fas fa-edit me-1" style="font-size: 0.85rem;"></i>
                                <span style="font-size: 0.85rem;">Edit</span>
                            </a>

                            {{-- Tombol Hapus dengan modal --}}
                            <button type="button"
                                class="btn text-white d-flex align-items-center justify-content-center px-3 py-2"
                                style="height: 32px; background-color: red;"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal{{ $user->id }}">
                                <i class="fas fa-trash me-1" style="font-size: 0.85rem;"></i>
                                <span style="font-size: 0.85rem;">Hapus</span>
                            </button>
                        </div>
                    </td>
                </tr>

                {{-- Modal Delete --}}
                <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1"
                    aria-labelledby="deleteModalLabel{{ $user->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg rounded-4 border-0">
                            <div class="modal-body text-center p-4">
                                <div class="mb-3">
                                    <i class="fas fa-triangle-exclamation fa-3x text-danger"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Hapus Bank User</h5>
                                <p class="mb-4">
                                    Apakah Anda yakin ingin menghapus bank user <br>
                                    <strong>{{ $user->username }}</strong>? <br> Tindakan ini tidak dapat dibatalkan.
                                </p>
                                <div class="d-flex justify-content-center gap-3">
                                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill"
                                        data-bs-dismiss="modal">Batal</button>
                                    <form action="{{ route('dashboard.bank-users.destroy', $user->id) }}" method="POST" class="d-inline">
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
                        <i class="fas fa-user-slash fa-lg mb-2 d-block"></i>
                        Tidak ada data bank user yang tersedia.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
