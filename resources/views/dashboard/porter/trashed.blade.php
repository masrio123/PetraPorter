@extends('layouts.app')

@section('title', 'Recently Deleted Porter')

@section('content')
<div class="container">
    {{-- Header Halaman --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('dashboard.porters.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar Porter
        </a>
    </div>

    {{-- Alert untuk Notifikasi Sukses --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Card untuk Tabel Data --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header border-0 bg-white pt-3 pb-0">
            <h5 class="card-title fw-semibold">Daftar Porter yang Dinonaktifkan</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 5%;" class="text-center">No</th>
                        <th>Nama Porter</th>
                        <th>NRP</th>
                        <th>Alasan Dinonaktifkan</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($trashedPorters as $index => $porter)
                        <tr>
                            <td class="text-center">{{ $trashedPorters->firstItem() + $index }}</td>
                            <td><strong>{{ $porter->porter_name }}</strong></td>
                            <td>{{ $porter->porter_nrp }}</td>
                            <td>{{ $porter->deletion_reason ?? 'Tidak ada alasan.' }}</td>
                            <td class="text-center">{{ $porter->deleted_at->format('d F Y') }}</td>
                            <td class="text-center">
                                {{-- Form untuk Restore Porter --}}
                                <form action="{{ route('dashboard.porters.restore', $porter->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    {{-- Method POST digunakan karena HTML form hanya mendukung GET/POST. Laravel akan menanganinya. --}}
                                    <button type="submit" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Pulihkan Porter">
                                        <i class="bx bx-revision"></i> Pulihkan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        {{-- Tampilan jika tidak ada data sama sekali --}}
                        <tr>
                            <td colspan="6" class="text-center text-muted p-5">
                                <i class="bx bx-check-double fs-2 d-block mb-2"></i>
                                No Delete History
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
         {{-- Tampilkan link paginasi jika ada --}}
        @if ($trashedPorters->hasPages())
            <div class="card-footer bg-white">
                {{ $trashedPorters->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
