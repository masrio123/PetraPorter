@extends('layouts.createLayout')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-12 card card-body">

                {{-- Header dengan tombol back dan judul tanpa border bawah --}}
                <div class="d-flex align-items-center gap-5 mb-4 border-bottom-0">
                    <a href="{{ route('dashboard.delivery-points.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h3 class="mb-0"><strong>Edit Delivery Point</strong></h3>
                </div>

                {{-- Alert Error --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Form Edit Delivery Point --}}
                <form action="{{ route('dashboard.delivery-points.update', $delivery_point->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <label for="delivery_point_name" class="form-label">Nama Delivery Point</label>
                        <input type="text" name="delivery_point_name" id="delivery_point_name" class="form-control"
                            required value="{{ old('delivery_point_name', $delivery_point->delivery_point_name) }}">
                    </div>

                    <button type="submit" class="btn text-white" style="background-color: #ff7622">Simpan
                        Perubahan</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal sukses --}}
    @if (session()->has('success'))
        <div class="modal fade show" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-modal="true"
            style="display: block; background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-success">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="successModalLabel">Berhasil</h5>
                        <a href="{{ url()->current() }}" class="btn-close"></a>
                    </div>
                    <div class="modal-body">
                        {{ session('success') }}
                    </div>
                    <div class="modal-footer">
                        <a href="{{ url()->current() }}" class="btn btn-success">Tutup</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
