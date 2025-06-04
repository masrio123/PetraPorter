@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4"><strong>Porter Management</strong></h3>

        <div class="mb-4">
            <a href="{{ route('dashboard.porters.create') }}" class="btn text-white" style="background-color: #ff7622;">
                Tambah Porter
            </a>
        </div>

        {{-- Alert sukses --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="fas fa-check-circle fa-lg"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

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
                                @if ($porter->timeout_until && \Carbon\Carbon::parse($porter->timeout_until)->isFuture())
                                    <span class="d-inline-flex align-items-center gap-2 text-warning fw-semibold">
                                        <i class="fas fa-clock me-1"></i> Timeout sampai
                                        {{ \Carbon\Carbon::parse($porter->timeout_until)->translatedFormat('d M Y H:i') }}
                                    </span>
                                @elseif ($porter->porter_isOnline)
                                    <span class="d-inline-flex align-items-center gap-2 text-success fw-semibold">
                                        <span class="rounded-circle"
                                            style="width: 10px; height: 10px; background-color: #28a745;"></span>
                                        Online
                                    </span>
                                @elseif ($porter->isWorking)
                                    <div
                                        class="mt-1 d-flex align-items-center justify-content-center gap-2 text-primary fw-semibold">
                                        <i class="fas fa-briefcase"></i> Working
                                    </div>
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
                                    {{-- Tombol Timeout atau Cabut Timeout --}}
                                    @if ($porter->timeout_until && \Carbon\Carbon::parse($porter->timeout_until)->isFuture())
                                        <button type="button"
                                            class="btn text-white d-flex align-items-center justify-content-center px-3 py-3"
                                            style="height: 32px; background-color: #6c757d;" data-bs-toggle="modal"
                                            data-bs-target="#cancelTimeoutModal{{ $porter->id }}"
                                            @if ($porter->isWorking) disabled style="opacity: 0.65; pointer-events: none;" @endif>
                                            <i class="fas fa-times-circle me-1" style="font-size: 0.85rem;"></i>
                                            <span style="font-size: 0.85rem;">Free</span>
                                        </button>

                                        {{-- Modal Cabut Timeout --}}
                                        <div class="modal fade" id="cancelTimeoutModal{{ $porter->id }}" tabindex="-1"
                                            aria-labelledby="cancelTimeoutLabel{{ $porter->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content shadow-lg rounded-4 border-0">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="mb-3">
                                                            <i class="fas fa-clock fa-3x text-warning"></i>
                                                        </div>
                                                        <h5 class="fw-bold mb-3">Cabut Timeout Porter</h5>
                                                        <p class="mb-4">
                                                            Apakah Anda yakin ingin mencabut timeout porter <br>
                                                            <strong>{{ $porter->porter_name }}</strong>? <br>
                                                            Porter akan langsung bisa online kembali.
                                                        </p>
                                                        <div class="d-flex justify-content-center gap-3">
                                                            <button type="button"
                                                                class="btn btn-outline-secondary px-4 rounded-pill"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <form
                                                                action="{{ route('dashboard.porters.update', $porter->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="action" value="cancel_timeout">
                                                                <button type="submit"
                                                                    class="btn btn-warning px-4 rounded-pill">Cabut
                                                                    Timeout</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <button type="button"
                                            class="btn text-white d-flex align-items-center justify-content-center px-3 py-3"
                                            style="height: 32px; background-color: orange;" data-bs-toggle="modal"
                                            data-bs-target="#timeoutModal{{ $porter->id }}"
                                            @if ($porter->isWorking) disabled style="opacity: 0.65; pointer-events: none;" @endif>
                                            <i class="fas fa-clock me-1" style="font-size: 0.85rem;"></i>
                                            <span style="font-size: 0.85rem;">Timeout</span>
                                        </button>

                                        {{-- Modal Timeout --}}
                                        <div class="modal fade" id="timeoutModal{{ $porter->id }}" tabindex="-1"
                                            aria-labelledby="timeoutLabel{{ $porter->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content shadow-lg rounded-4 border-0">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="mb-3">
                                                            <i class="fas fa-clock fa-3x text-warning"></i>
                                                        </div>
                                                        <h5 class="fw-bold mb-3">Timeout Porter</h5>
                                                        <p class="mb-4">
                                                            Apakah Anda yakin ingin memberikan timeout selama 2 hari kepada
                                                            porter
                                                            <br>
                                                            <strong>{{ $porter->porter_name }}</strong>?
                                                        </p>
                                                        <div class="d-flex justify-content-center gap-3">
                                                            <button type="button"
                                                                class="btn btn-outline-secondary px-4 rounded-pill"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <form
                                                                action="{{ route('dashboard.porters.update', $porter->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="action" value="timeout">
                                                                <button type="submit"
                                                                    class="btn btn-warning px-4 rounded-pill">Timeout</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <a href="{{ route('dashboard.porters.edit', $porter->id) }}"
                                        class="btn btn-primary d-flex align-items-center justify-content-center px-3 py-3"
                                        style="height: 32px; 
        @if ($porter->isWorking) pointer-events: none; 
            opacity: 0.65; 
            cursor: default; @endif"
                                        tabindex="{{ $porter->isWorking ? '-1' : '0' }}"
                                        aria-disabled="{{ $porter->isWorking ? 'true' : 'false' }}">
                                        <i class="fas fa-edit me-1" style="font-size: 0.85rem;"></i>
                                        <span style="font-size: 0.85rem;">Edit</span>
                                    </a>



                                    {{-- Tombol Hapus --}}
                                    <button type="button"
                                        class="btn text-white d-flex align-items-center justify-content-center px-3 py-3"
                                        style="height: 32px; background-color: red;" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal{{ $porter->id }}"
                                        @if ($porter->isWorking) disabled style="opacity: 0.65; pointer-events: none;" @endif>
                                        <i class="fas fa-trash me-1" style="font-size: 0.85rem;"></i>
                                        <span style="font-size: 0.85rem;">Hapus</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Hapus --}}
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
                                            <strong>{{ $porter->porter_name }}</strong>? <br>
                                            Tindakan ini tidak dapat dibatalkan.
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
