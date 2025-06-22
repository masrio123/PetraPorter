@extends('layouts.app')

@section('title', 'Manajemen Porter')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex gap-2">
            {{-- Fitur Pencarian --}}
            <form action="{{ route('dashboard.porters.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control" placeholder="Cari Porter" value="{{ request('search') }}">
                <button type="submit" class="btn btn-dark ms-2"><i class="bx bx-search"></i></button>
                @if (request('search'))
                    <a href="{{ route('dashboard.porters.index') }}" class="btn btn-outline-secondary ms-2">Reset</a>
                @endif
            </form>
            {{-- Tombol Tambah Porter --}}
            <a href="{{ route('dashboard.porters.create') }}" class="btn text-white" style="background-color: #ff7622; border-color: #ff7622;">
                <i class="bx bx-plus me-1"></i>
                Tambah Porter
            </a>
        </div>
    </div>

    {{-- Alert sukses --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Nama Porter</th>
                        <th class="text-center">NRP</th>
                        <th class="text-center">Jurusan</th>
                        <th class="text-center">Bank</th>
                        <th class="text-center">No. Rekening</th>
                        <th class="text-center">A.N</th>
                        <th class="text-center">Rating</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($porters as $key => $porter)
                        <tr>
                            {{-- DIKEMBALIKAN: Menggunakan logika penomoran awal --}}
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td><strong>{{ $porter->porter_name }}</strong></td>
                            <td class="text-center">{{ $porter->porter_nrp }}</td>
                            <td>{{ $porter->department->department_name ?? '-' }}</td>
                            <td>{{ $porter->bank_name ?? '-' }}</td>
                            <td>{{ $porter->account_numbers ?? '-' }}</td>
                            <td>{{ $porter->username ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-label-warning d-inline-flex align-items-center">
                                    {{-- PERBAIKAN: Menambahkan casting (float) untuk memastikan tipe data adalah angka --}}
                                    <i class="bx bxs-star me-1"></i> {{ number_format((float)($porter->porter_rating ?? 0), 1) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($porter->timeout_until && \Carbon\Carbon::parse($porter->timeout_until)->isFuture())
                                    <span class="badge bg-label-danger">Timeout</span>
                                @elseif ($porter->isWorking)
                                     <span class="badge bg-label-info">Bekerja</span>
                                @elseif ($porter->porter_isOnline)
                                    <span class="badge bg-label-success">Online</span>
                                @else
                                    <span class="badge bg-label-secondary">Offline</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-2">
                                    {{-- Tombol Timeout atau Cabut Timeout --}}
                                    @if ($porter->timeout_until && \Carbon\Carbon::parse($porter->timeout_until)->isFuture())
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                            data-bs-target="#cancelTimeoutModal{{ $porter->id }}" title="Cabut Timeout"
                                            @if ($porter->isWorking) disabled @endif>
                                            <i class="bx bx-play"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                            data-bs-target="#timeoutModal{{ $porter->id }}" title="Beri Timeout"
                                            @if ($porter->isWorking) disabled @endif>
                                            <i class="bx bx-pause"></i>
                                        </button>
                                    @endif
                                    
                                    {{-- Edit --}}
                                    <a href="{{ route('dashboard.porters.edit', $porter->id) }}" class="btn btn-sm btn-outline-primary @if ($porter->isWorking) disabled @endif" title="Edit Porter">
                                        <i class="bx bx-pencil"></i>
                                    </a>

                                    {{-- Hapus --}}
                                    <button type="button" class="btn btn-sm btn-outline-danger @if ($porter->isWorking) disabled @endif" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal{{ $porter->id }}" title="Hapus Porter">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Hapus --}}
                        <div class="modal fade" id="deleteModal{{ $porter->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Apakah Anda yakin ingin menghapus porter <strong>{{ $porter->porter_name }}</strong>? <br> Tindakan ini tidak dapat dibatalkan.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('dashboard.porters.destroy', $porter->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Timeout --}}
                        <div class="modal fade" id="timeoutModal{{ $porter->id }}" tabindex="-1" aria-hidden="true">
                             <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('dashboard.porters.update', $porter->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="action" value="timeout">
                                        {{-- PERUBAHAN: Menambahkan input tersembunyi untuk durasi timeout 2 hari --}}
                                        <input type="hidden" name="timeout_duration" value="2">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Timeout Porter: {{ $porter->porter_name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            {{-- PERUBAHAN: Menghapus pilihan durasi dan mengubah teks --}}
                                            <p>Apakah Anda yakin ingin memberikan timeout selama 2 hari kepada <br> porter <strong>{{ $porter->porter_name }}</strong>? <br> <br> Yang bersangkutan tidak akan bisa online sampai durasi berakhir.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-warning">Ya, Terapkan Timeout</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Modal Cabut Timeout --}}
                        <div class="modal fade" id="cancelTimeoutModal{{ $porter->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Konfirmasi Cabut Timeout</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Apakah Anda yakin ingin mencabut status timeout dari <strong>{{ $porter->porter_name }}</strong>? Porter akan dapat online kembali.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('dashboard.porters.update', $porter->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="action" value="cancel_timeout">
                                            <button type="submit" class="btn btn-success">Ya, Cabut Timeout</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bx bx-user-x fs-1 d-block mb-2 text-muted"></i>
                                <h5 class="text-muted">
                                     @if (request('search'))
                                        Porter "{{ request('search') }}" tidak ditemukan.
                                     @else
                                        Belum Ada Data Porter
                                     @endif
                                </h5>
                                <p class="text-muted">
                                    @if (request('search'))
                                        Coba kata kunci lain atau reset pencarian.
                                    @else
                                        Silakan tambahkan data baru untuk memulai.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- DIHAPUS: Menghapus logika paginasi yang salah --}}
    </div>
</div>
@endsection
