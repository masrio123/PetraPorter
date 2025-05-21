@extends('layouts.createLayout')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-12 card card-body">
                {{-- Header --}}
                <div class="d-flex align-items-center gap-5 mb-4 border-bottom-0">
                    <a href="{{ route('dashboard.delivery-points.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h3 class="mb-0"><strong>Tambah Delivery Point</strong></h3>
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

                {{-- Form Tambah --}}
                <form action="{{ route('dashboard.delivery-points.store') }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label for="delivery_point_name" class="form-label">Nama Delivery Point</label>
                        <input type="text" name="delivery_point_name" id="delivery_point_name" class="form-control"
                               required value="{{ old('delivery_point_name') }}">
                    </div>

                    <button type="submit" class="btn text-white" style="background-color: #ff7622">Simpan</button>
                </form>
            </div>
        </div>
    </div>
@endsection
