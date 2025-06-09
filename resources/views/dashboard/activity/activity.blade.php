@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4"><strong>Activity</strong></h3>

        @if (empty($activities) || $activities->isEmpty())
            <div class="alert alert-warning">Tidak ada aktivitas order ditemukan.</div>
        @else
            @php
                $groupedByDate = $activities->groupBy(function ($order) {
                    return \Carbon\Carbon::parse($order['order_date'])->format('Y-m-d');
                });
            @endphp

            @foreach ($groupedByDate as $date => $orders)
                <h5 class="mt-4 mb-3 text-primary">
                    📅 {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                </h5>

                @foreach ($orders as $order)
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-1">Order ID: {{ $order['order_id'] ?? 'N/A' }}</h5>
                                    <p class="card-subtitle text-muted small">
                                        Waktu: {{ \Carbon\Carbon::parse($order['order_date'])->format('H:i:s') }}
                                    </p>
                                </div>
                                <span
                                    class="badge bg-info text-dark">{{ $order['order_status'] ?? 'Status tidak diketahui' }}</span>
                            </div>
                            <div class="mt-3">
                                <p><strong>Pembeli:</strong></p>
                                <ul class="mb-2">
                                    <li>Nama: {{ $order['customer']['name'] ?? '-' }}</li>
                                    <li>NRP: {{ $order['customer']['nrp'] ?? '-' }}</li>
                                    <li>Jurusan: {{ $order['customer']['department'] ?? '-' }}</li>
                                </ul>

                                <p><strong>Porter:</strong></p>
                                @if ($order['porter'])
                                    <ul>
                                        <li>Nama: {{ $order['porter']['name'] ?? '-' }}</li>
                                        <li>NRP: {{ $order['porter']['nrp'] ?? '-' }}</li>
                                        <li>Jurusan: {{ $order['porter']['department'] ?? '-' }}</li>
                                        <li>No Rekening: {{ $order['porter']['account_number'] ?? '-' }}</li>
                                    </ul>
                                @else
                                    <p>-</p>
                                @endif

                                <p><strong>Lokasi Tenant:</strong> {{ $order['tenant_location_name'] ?? '-' }}</p>
                            </div>


                            <button class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal"
                                data-bs-target="#detailModal{{ $order['order_id'] }}">
                                Lihat Detail
                            </button>
                        </div>
                    </div>

                    {{-- Modal Detail Order --}}
                    <div class="modal fade" id="detailModal{{ $order['order_id'] }}" tabindex="-1"
                        aria-labelledby="detailModalLabel{{ $order['order_id'] }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailModalLabel{{ $order['order_id'] }}">Detail Order
                                        #{{ $order['order_id'] }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    @foreach ($order['items'] ?? [] as $tenant)
                                        <div class="mb-3">
                                            <h6 class="text-primary">Tenant: {{ $tenant['tenant_name'] ?? '-' }}</h6>
                                            <ul class="list-group">
                                                @foreach ($tenant['items'] ?? [] as $item)
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>
                                                            {{ $item['product_name'] }} ({{ $item['quantity'] }} pcs @
                                                            Rp{{ number_format($item['price'], 0, ',', '.') }})
                                                        </span>
                                                        <strong>Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</strong>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach

                                    <div class="border-top pt-3 mt-3">
                                        <p><strong>Total Harga:</strong>
                                            Rp{{ number_format($order['total_price'], 0, ',', '.') }}</p>
                                        <p><strong>Ongkir:</strong>
                                            Rp{{ number_format($order['shipping_cost'], 0, ',', '.') }}</p>
                                        <p><strong>Grand Total:</strong> <strong
                                                class="text-success">Rp{{ number_format($order['grand_total'], 0, ',', '.') }}</strong>
                                        </p>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        @endif
    </div>
@endsection
