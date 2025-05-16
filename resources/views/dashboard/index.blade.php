@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-6">
            <div class="card card-body">
                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                    <div class="avatar flex-shrink-0">
                        <img src="../assets/img/icons/unicons/chart-success.png" alt="chart success" class="rounded">
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                        </button>
                    </div>
                </div>
                <p class="mb-1">Profit</p>
                <h4 class="card-title mb-3">$12,628</h4>
                <small class="text-success fw-medium"><i class="icon-base bx bx-up-arrow-alt"></i> +72.80%</small>
            </div>
        </div>
        <div class="col-6">
            <div class="card card-body">
                <div class="card-title d-flex align-items-start justify-content-between mb-4">
                    <div class="avatar flex-shrink-0">
                        <img src="../assets/img/icons/unicons/chart-success.png" alt="chart success" class="rounded">
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                        </button>
                    </div>
                </div>
                <p class="mb-1">Profit</p>
                <h4 class="card-title mb-3">$12,628</h4>
                <small class="text-success fw-medium"><i class="icon-base bx bx-up-arrow-alt"></i> +72.80%</small>
            </div>
        </div>
    </div>
@endsection