@extends('layouts.app')

@section('title', 'Aktivitas Pesanan')

@section('content')
<div class="container">

    @if (empty($activities) || $activities->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bx bx-receipt fs-1 d-block mb-3 text-muted"></i>
                <h4 class="text-muted">Tidak Ada Aktivitas</h4>
                <p class="text-muted">Belum ada pesanan yang tercatat dalam sistem.</p>
            </div>
        </div>
    @else
        @php
            $groupedByDate = $activities->groupBy(function ($order) {
                return \Carbon\Carbon::parse($order['order_date'])->format('Y-m-d');
            });
        @endphp

        <div class="accordion accordion-flush" id="activityAccordion">
            @foreach ($groupedByDate as $date => $orders)
                {{-- PERUBAHAN: Mengubah header tanggal menjadi accordion --}}
                <div class="accordion-item mb-3 border-0 shadow-sm rounded-3">
                    <h2 class="accordion-header" id="heading-{{ $loop->index }}">
                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} rounded-top-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse-{{ $loop->index }}">
                            <i class="bx bx-calendar-event me-2 fs-5"></i>
                            <span class="fw-bold fs-6">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, j F Y') }}</span>
                        </button>
                    </h2>
                    <div id="collapse-{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading-{{ $loop->index }}">
                        <div class="accordion-body p-0">
                             {{-- List Container --}}
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID & Waktu</th>
                                            <th>Pelanggan</th>
                                            <th>Porter</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        @foreach ($orders as $order)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">Order #{{ $order['order_id'] ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($order['order_date'])->format('H:i') }} WIB</small>
                                                </td>
                                                <td>{{ $order['customer']['name'] ?? '-' }}</td>
                                                <td>{{ $order['porter']['name'] ?? '-' }}</td>
                                                <td class="text-center">
                                                    @php
                                                        $status = strtolower($order['order_status'] ?? '');
                                                        $badgeClass = 'bg-label-secondary';
                                                        if ($status == 'finished') $badgeClass = 'bg-label-success';
                                                        elseif (in_array($status, ['in progress', 'accepted'])) $badgeClass = 'bg-label-info';
                                                        elseif ($status == 'pending') $badgeClass = 'bg-label-warning';
                                                        elseif ($status == 'cancelled') $badgeClass = 'bg-label-danger';
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $order['order_status'] ?? 'N/A' }}</span>
                                                </td>
                                                <td class="text-end">
                                                    {{-- PERUBAHAN: Mengganti tombol menjadi outline --}}
                                                    <button class="btn btn-warning background-color #ff7622" data-bs-toggle="modal" data-bs-target="#detailModal{{ $order['order_id'] }}">
                                                        Lihat Detail
                                                    </button>
                                                </td>
                                            </tr>

                                            {{-- Modal Detail Order --}}
                                            <div class="modal fade" id="detailModal{{ $order['order_id'] }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $order['order_id'] }}" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-hero text-white">
                                                            <h5 class="modal-title" id="detailModalLabel{{ $order['order_id'] }}">Rincian Pesanan #{{ $order['order_id'] }}</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                            {{-- Customer & Porter Details in Modal --}}
                                                            <div class="row mb-4">
                                                                <div class="col-md-6">
                                                                    <h6 class="fw-bold">Pelanggan:</h6>
                                                                    <p class="mb-0">{{ $order['customer']['name'] ?? '-' }} ({{ $order['customer']['nrp'] ?? '-' }})</p>
                                                                    <p class="text-muted small">{{ $order['customer']['department'] ?? '-' }}</p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6 class="fw-bold">Porter:</h6>
                                                                        @if ($order['porter'])
                                                                        <p class="mb-0">{{ $order['porter']['name'] ?? '-' }} ({{ $order['porter']['nrp'] ?? '-' }})</p>
                                                                        <p class="text-muted small">No. Rek: {{ $order['porter']['account_number'] ?? '-' }}</p>
                                                                    @else
                                                                        <p class="text-muted">-</p>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            {{-- Items Details --}}
                                                            @foreach ($order['items'] ?? [] as $tenant)
                                                                <div class="mb-3">
                                                                    <p class="mb-1 fw-semibold"><i class="bx bxs-store-alt me-1"></i>Tenant: {{ $tenant['tenant_name'] ?? '-' }}</p>
                                                                    <ul class="list-group list-group-flush">
                                                                        @foreach ($tenant['items'] ?? [] as $item)
                                                                            <li class="list-group-item d-flex justify-content-between px-0">
                                                                                <div>
                                                                                    {{ $item['product_name'] }} <span class="text-muted">x {{ $item['quantity'] }}</span>
                                                                                    @if(!empty($item['notes']))
                                                                                        <small class="d-block text-muted fst-italic">"{{ $item['notes'] }}"</small>
                                                                                    @endif
                                                                                </div>
                                                                                <span class="fw-medium">Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @endforeach
                                                            
                                                            {{-- Total Calculation --}}
                                                            <div class="border-top pt-3 mt-3">
                                                                <dl class="row mb-0">
                                                                    <dt class="col-sm-8 fw-normal">Subtotal Pesanan</dt>
                                                                    <dd class="col-sm-4 text-sm-end">Rp{{ number_format($order['total_price'], 0, ',', '.') }}</dd>
                                                                    
                                                                    <dt class="col-sm-8 fw-normal">Biaya Jasa (Ongkir)</dt>
                                                                    <dd class="col-sm-4 text-sm-end">Rp{{ number_format($order['shipping_cost'], 0, ',', '.') }}</dd>
                                                                    
                                                                    <dt class="col-sm-8 fs-5 fw-bold mt-2">Grand Total</dt>
                                                                    <dd class="col-sm-4 fs-5 fw-bold text-sm-end mt-2 text-success">Rp{{ number_format($order['grand_total'], 0, ',', '.') }}</dd>
                                                                </dl>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    :root {
        --hero-color: #ff7622;
        --hero-color-rgb: 255, 118, 34;
    }
    .btn-outline-hero {
        color: var(--hero-color);
        border-color: var(--hero-color);
    }
    .btn-outline-hero:hover {
        color: #fff;
        background-color: var(--hero-color);
        border-color: var(--hero-color);
    }
    .bg-hero {
        background-color: var(--hero-color) !important;
    }
    .accordion-button:not(.collapsed) {
        color: var(--hero-color);
        background-color: rgba(var(--hero-color-rgb), 0.1);
        box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
    }
    .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(var(--hero-color-rgb), 0.25);
    }
    .accordion-flush .accordion-item {
        border-radius: 0.5rem !important;
        overflow: hidden;
    }
    .table {
        margin-bottom: 0;
    }
    .table th {
        font-weight: 600;
        color: #697a8d;
        white-space: nowrap;
        background-color: #f8f9fa;
    }
    .table > :not(caption) > * > * {
        padding: 0.85rem 1rem;
    }
    .table td {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
{{-- No custom script needed for this design --}}
@endpush
