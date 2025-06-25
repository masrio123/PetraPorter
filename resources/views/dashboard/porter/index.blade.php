@extends('layouts.app')

@section('title', 'Manajemen Porter')

@section('content')
<div class="container">

    {{-- Pengingat: Pastikan Controller mengirimkan $porters dan $departments --}}

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex gap-2">
            <a href="{{ route('dashboard.porters.trashed') }}" class="btn btn-outline-secondary">
                <i class="bx bx-trash-alt me-1"></i> Delete History
            </a>
            <a href="{{ route('dashboard.porters.create') }}" class="btn text-white" style="background-color: #ff7622;">
                <i class="bx bx-plus me-1"></i> Tambah Porter
            </a>
        </div>
    </div>

    {{-- Alert sukses & error --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
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

    <div class="card border-0 shadow-sm">
        <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-semibold mb-0">Daftar Porter Aktif</h5>
            <form action="{{ route('dashboard.porters.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control" placeholder="Cari Porter..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-dark ms-2"><i class="bx bx-search"></i></button>
                @if (request('search'))
                    <a href="{{ route('dashboard.porters.index') }}" class="btn btn-outline-secondary ms-2">Reset</a>
                @endif
            </form>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>Nama Porter</th>
                        <th>NRP</th>
                        <th>Jurusan</th>
                        <th>Bank</th>
                        <th>No. Rek</th>
                        <th>A.N</th>
                        <th class="text-center">Rating</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($porters as $index => $porter)
                        <tr>
                            <td class="text-center">
                                {{ $porters instanceof \Illuminate\Pagination\LengthAwarePaginator ? $porters->firstItem() + $index : $index + 1 }}
                            </td>
                            <td>
                                <strong>
                                    {{ $porter->porter_name }}
                                </strong>
                            </td>
                            <td>{{ $porter->porter_nrp }}</td>
                            <td>{{ $porter->department->department_name ?? '-' }}</td>
                            <td>{{ $porter->bank_name }}</td>
                            <td>{{ $porter->account_numbers }}</td>
                            <td>{{ $porter->username }}</td>
                            <td class="text-center">
                                <!-- TOMBOL RATING -->
                                <button type="button" class="btn btn-sm btn-outline-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#reviewModal{{ $porter->id }}"
                                    title="Lihat Review Porter">
                                    ⭐ {{ $porter->porter_rating ?? '-' }}
                                </button>
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
                                    {{-- tombol-tombol aksi seperti sebelumnya --}}
                                    @if ($porter->timeout_until && \Carbon\Carbon::parse($porter->timeout_until)->isFuture())
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#cancelTimeoutModal{{ $porter->id }}" title="Cabut Timeout" @if($porter->isWorking) disabled @endif><i class="bx bx-play"></i></button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#timeoutModal{{ $porter->id }}" title="Beri Timeout" @if($porter->isWorking) disabled @endif><i class="bx bx-pause"></i></button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $porter->id }}" title="Edit Porter" @if($porter->isWorking) disabled @endif><i class="bx bx-pencil"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $porter->id }}" title="Hapus Porter" @if($porter->isWorking) disabled @endif><i class="bx bx-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5"><h5 class="text-muted">Data Porter tidak ditemukan.</h5></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($porters instanceof \Illuminate\Pagination\LengthAwarePaginator && $porters->hasPages())
        <div class="card-footer bg-white">
            {{ $porters->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>  

@foreach ($porters as $porter)
    {{-- MODAL EDIT PORTER --}}
    <div class="modal fade" id="editModal{{ $porter->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $porter->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('dashboard.porters.update', $porter->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel{{ $porter->id }}">Edit Porter</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label for="porter_name_{{ $porter->id }}" class="form-label">Nama Porter</label><input type="text" name="porter_name" id="porter_name_{{ $porter->id }}" class="form-control" required value="{{ old('porter_name', $porter->porter_name) }}"></div>
                        <div class="mb-3"><label for="porter_nrp_{{ $porter->id }}" class="form-label">NRP</label><input type="text" name="porter_nrp" id="porter_nrp_{{ $porter->id }}" class="form-control" required value="{{ old('porter_nrp', $porter->porter_nrp) }}"></div>
                        <div class="mb-3"><label for="department_id_{{ $porter->id }}" class="form-label">Departemen</label><select name="department_id" id="department_id_{{ $porter->id }}" class="form-select" required><option disabled value="">Pilih...</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id', $porter->department_id) == $department->id)>{{ $department->department_name }}</option>@endforeach</select></div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="bank_name_{{ $porter->id }}" class="form-label">Nama Bank</label>
                                <select name="bank_name" id="bank_name_{{ $porter->id }}" class="form-select" required>
                                    <option value="">Pilih...</option>
                                    @php $banks = ['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB Niaga']; sort($banks); @endphp
                                    @foreach ($banks as $bank)
                                        <option value="{{ $bank }}" @selected(old('bank_name', $porter->bank_name) == $bank)>{{ $bank }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="account_numbers_{{ $porter->id }}" class="form-label">No. Rekening</label>
                                <input type="text" name="account_numbers" id="account_numbers_{{ $porter->id }}" class="form-control" required value="{{ old('account_numbers', $porter->account_numbers) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="username_{{ $porter->id }}" class="form-label">Atas Nama</label>
                                <input type="text" name="username" id="username_{{ $porter->id }}" class="form-control" required value="{{ old('username', $porter->username) }}">
                            </div>
                        </div>                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn text-white" style="background-color: #ff7622">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL HAPUS PORTER (SOFT DELETE) --}}
    <div class="modal fade" id="deleteModal{{ $porter->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $porter->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('dashboard.porters.destroy', $porter->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header"><h5 class="modal-title" id="deleteModalLabel{{ $porter->id }}">Nonaktifkan Porter</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <p>Anda akan menonaktifkan porter <strong>{{ $porter->porter_name }}</strong>. Porter akan disembunyikan dari daftar utama.</p>
                        <div class="mb-3">
                            <label for="deletion_reason_porter_{{ $porter->id }}" class="form-label">Alasan Penonaktifan (Wajib)</label>
                            <textarea name="deletion_reason" id="deletion_reason_porter_{{ $porter->id }}" class="form-control" rows="3" required placeholder="Contoh: Sudah lulus, mengundurkan diri, dll."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Ya, Nonaktifkan</button></div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- MODAL TIMEOUT --}}
    <div class="modal fade" id="timeoutModal{{ $porter->id }}" tabindex="-1" aria-labelledby="timeoutModalLabel{{ $porter->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('dashboard.porters.update', $porter->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="timeout">
                    <input type="hidden" name="timeout_duration" value="2">
                    <div class="modal-header"><h5 class="modal-title" id="timeoutModalLabel{{ $porter->id }}">Timeout Porter</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body"><p>Berikan timeout selama 2 hari kepada <strong>{{ $porter->porter_name }}</strong>? Porter tidak akan bisa online selama durasi tersebut.</p></div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-warning">Ya, Terapkan</button></div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- MODAL CABUT TIMEOUT --}}
    <div class="modal fade" id="cancelTimeoutModal{{ $porter->id }}" tabindex="-1" aria-labelledby="cancelTimeoutModalLabel{{ $porter->id }}" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('dashboard.porters.update', $porter->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="cancel_timeout">
                    <div class="modal-header"><h5 class="modal-title" id="cancelTimeoutModalLabel{{ $porter->id }}">Cabut Timeout</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body"><p>Cabut status timeout dari <strong>{{ $porter->porter_name }}</strong>? Porter akan dapat online kembali.</p></div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success">Ya, Cabut</button></div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL REVIEW PORTER --}}
    <div class="modal fade" id="reviewModal{{ $porter->id }}" tabindex="-1" aria-labelledby="reviewModalLabel{{ $porter->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalLabel{{ $porter->id }}">Review untuk {{ $porter->porter_name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($porter->ratings->count())
                        <div class="list-group">
                            @foreach ($porter->ratings as $rating)
                                <div class="list-group-item mb-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>⭐ {{ $rating->rating }}</strong>
                                            <small class="text-muted">({{ $rating->created_at->format('d-m-Y') }})</small>
                                        </div>
                                        <div>
                                            <span class="badge bg-secondary">
                                                {{ $rating->order && $rating->order->customer ? $rating->order->customer->customer_name : 'Anonim' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        {{ $rating->review ?? '-' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">Belum ada review untuk porter ini.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
