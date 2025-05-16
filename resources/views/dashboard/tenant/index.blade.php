@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 col-md-4 col-12 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="mb-1">Total Orderan Hari Ini</p>
                        <h4 class="card-title mb-3">12 Transaksi</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-12 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="mb-1">Total yang Harus Selesai Hari Ini</p>
                        <h4 class="card-title mb-3">0 Transaksi</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-12 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="mb-1">Total yang Harus Selesai Besok</p>
                        <h4 class="card-title mb-3">0 Transaksi</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-6">
                <a href="" class="btn btn-primary">
                    Tambah Tenant
                </a>
            </div>
        </div>
        <div class="row mt-0">
            <div class="col-12">
                <div class="card card-body overflow-auto">
                    <table class="table table-bordered table-scroll">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Tenant</th>
                                <th>Lokasi</th>
                                <th>Status Buka</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tenants as $key => $tenant)
                                <tr>
                                    <td>{{ 1 + $key }}</td>
                                    <td>{{ $tenant->name }}</td>
                                    <td>{{ $tenant->location }}</td>
                                    <td>{{ $tenant->isOpen ? 'Buka':'Tutup' }}</td>
                                    <td>
                                        <div class="wrap text-center">
                                            <a href="" class="btn btn-primary"></a>
                                            <a href="" class="btn btn-info"></a>
                                            <a href="" class="btn btn-danger"></a>
                                        </div>
                                     </td>
                                </tr>
                            @endforeach
                            {{-- @foreach ($transactions as $key => $transaction)
                                <tr>
                                    <td>{{ 1 + $key }}</td>
                                    <td>{{ $transaction->customer_name }}</td>
                                    <td>{{ $transaction->trx_code }}</td>
                                    <td>{{ formatTanggal($transaction->date_in) }}</td>
                                    <td>{{ formatRupiah($transaction->price) }}</td>
                                    <td>Belum Bayar</td>
                                    <td>
                                        <div class="wrap text-center">
                                            <a href="" class="btn btn-primary"></a>
                                            <a href="" class="btn btn-info"></a>
                                            <a href="" class="btn btn-danger"></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach --}}

                            @for ($i = 1; $i <= 10; $i++)
                               
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection