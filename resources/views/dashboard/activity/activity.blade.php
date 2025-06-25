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
                    <div class="accordion-item mb-3 border-0 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="heading-{{ $loop->index }}">
                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} rounded-top-3"
                                type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $loop->index }}"
                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                aria-controls="collapse-{{ $loop->index }}">
                                <i class="bx bx-calendar-event me-2 fs-5"></i>
                                <span
                                    class="fw-bold fs-6">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, j F Y') }}</span>
                            </button>
                        </h2>
                        <div id="collapse-{{ $loop->index }}"
                            class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                            aria-labelledby="heading-{{ $loop->index }}">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Waktu</th>
                                                <th class="text-center">Pelanggan</th>
                                                <th class="text-center">Porter</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            @foreach ($orders as $order)
                                                <tr>
                                                    <td class="text-center">
                                                        <small
                                                            class="text-muted">{{ \Carbon\Carbon::parse($order['order_date'])->format('H:i') }}
                                                            WIB</small>
                                                    </td class="text-center">
                                                    <td class="text-center">{{ $order['customer']['name'] ?? '-' }}</td>
                                                    <td class="text-center">{{ $order['porter']['name'] ?? '-' }}</td>
                                                    <td class="text-center">
                                                        @php
                                                            $status = strtolower($order['order_status'] ?? '');
                                                            $badgeClass = 'bg-label-secondary';
                                                            if ($status == 'finished') {
                                                                $badgeClass = 'bg-label-success';
                                                            } elseif (
                                                                in_array($status, [
                                                                    'in progress',
                                                                    'accepted',
                                                                    'delivered',
                                                                ])
                                                            ) {
                                                                $badgeClass = 'bg-label-info';
                                                            } elseif (
                                                                in_array($status, ['pending', 'waiting_for_acceptance'])
                                                            ) {
                                                                $badgeClass = 'bg-label-warning';
                                                            } elseif ($status == 'cancelled') {
                                                                $badgeClass = 'bg-label-danger';
                                                            }
                                                        @endphp
                                                        <span
                                                            class="badge {{ $badgeClass }}">{{ ucwords(str_replace('_', ' ', $status)) }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <button class="btn btn-warning" data-bs-toggle="modal"
                                                            data-bs-target="#detailModal{{ $order['order_id'] }}">
                                                            Lihat Detail
                                                        </button>
                                                    </td>
                                                </tr>

                                                <div class="modal fade" id="detailModal{{ $order['order_id'] }}"
                                                    tabindex="-1"
                                                    aria-labelledby="detailModalLabel{{ $order['order_id'] }}"
                                                    aria-hidden="true">
                                                    <div
                                                        class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-hero text-white">
                                                                <h5 class="modal-title"
                                                                    id="detailModalLabel{{ $order['order_id'] }}">
                                                                    <i class="bx bx-receipt me-2"></i>Rincian Pesanan
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white"
                                                                    data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                            </div>
                                                            <div class="modal-body p-4">

                                                                <div class="timeline">
                                                                    <div class="timeline-item">
                                                                        <div class="timeline-icon">
                                                                            <i class="bx bxs-user-circle"></i>
                                                                        </div>
                                                                        <div class="timeline-content">
                                                                            <h6 class="fw-bold">Pelanggan</h6>
                                                                            <p class="fw-semibold mb-0">
                                                                                {{ $order['customer']['name'] ?? '-' }}</p>
                                                                            <p class="text-muted small">Identity Number:
                                                                                {{ $order['customer']['identity_number'] ?? '-' }} |
                                                                                Department:
                                                                                {{ $order['customer']['department'] ?? '-' }}
                                                                            </p>
                                                                            <p class="text-muted small">No. Rek:
                                                                                {{ $order['customer']['account_numbers'] ?? '-' }}
                                                                            </p>
                                                                        </div>
                                                                    </div>

                                                                    <div class="timeline-item">
                                                                        <div class="timeline-icon">
                                                                            <i class="bx bxs-truck"></i>
                                                                        </div>
                                                                        <div class="timeline-content">
                                                                            <h6 class="fw-bold">Porter</h6>
                                                                            @if ($order['porter'])
                                                                                <p class="fw-semibold mb-0">
                                                                                    {{ $order['porter']['name'] ?? '-' }}
                                                                                </p>
                                                                                <p class="text-muted small">NRP:
                                                                                    {{ $order['porter']['nrp'] ?? '-' }} |
                                                                                    Department:
                                                                                    {{ $order['porter']['department'] ?? '-' }}
                                                                                </p>
                                                                                <p class="text-muted small">No. Rek:
                                                                                    {{ $order['porter']['account_numbers'] ?? '-' }}
                                                                                </p>
                                                                            @else
                                                                                <p class="text-muted fst-italic">- Belum ada
                                                                                    porter -</p>
                                                                            @endif
                                                                        </div>
                                                                    </div>

                                                                    <div class="timeline-item">
                                                                        <div class="timeline-icon">
                                                                            <i class="bx bxs-cart-alt"></i>
                                                                        </div>
                                                                        <div class="timeline-content">
                                                                            <h6 class="fw-bold mb-3">Item Pesanan</h6>
                                                                            @forelse ($order['items'] ?? [] as $tenant)
                                                                                <div
                                                                                    class="border rounded p-3 mb-3 bg-light">
                                                                                    <p class="fw-bold mb-2"><i
                                                                                            class="bx bxs-store-alt me-1"></i>Tenant:
                                                                                        {{ $tenant['tenant_name'] ?? '-' }}
                                                                                    </p>
                                                                                    @forelse ($tenant['items'] ?? [] as $item)
                                                                                        <div
                                                                                            class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                                                                            <div>
                                                                                                <p class="mb-0">
                                                                                                    {{ $item['product_name'] }}
                                                                                                    <span
                                                                                                        class="text-muted">x
                                                                                                        {{ $item['quantity'] }}</span>
                                                                                                </p>
                                                                                                @if (!empty($item['notes']))
                                                                                                    <small
                                                                                                        class="d-block text-muted fst-italic">Catatan:
                                                                                                        "{{ $item['notes'] }}"</small>
                                                                                                @endif
                                                                                            </div>
                                                                                            <p class="fw-medium mb-0">
                                                                                                Rp{{ number_format($item['subtotal'], 0, ',', '.') }}
                                                                                            </p>
                                                                                        </div>
                                                                                    @empty
                                                                                        <p class="text-muted fst-italic">
                                                                                            Tidak ada item.</p>
                                                                                    @endforelse
                                                                                </div>
                                                                            @empty
                                                                                <p class="text-muted fst-italic">Tidak ada
                                                                                    item dalam pesanan ini.</p>
                                                                            @endforelse
                                                                        </div>
                                                                    </div>

                                                                  
                                                                        <div class="timeline-content">
                                                                            <h6 class="fw-bold">Total Price</h6>
                                                                            <div
                                                                                class="d-flex justify-content-between mb-1">
                                                                                <p class="text-muted">Subtotal</p>
                                                                                <p class="fw-medium">
                                                                                    Rp{{ number_format($order['total_price'], 0, ',', '.') }}
                                                                                </p>
                                                                            </div>
                                                                            <div
                                                                                class="d-flex justify-content-between mb-2">
                                                                                <p class="text-muted">Delivery Fee
                                                                                </p>
                                                                                <p class="fw-medium">
                                                                                    Rp{{ number_format($order['shipping_cost'], 0, ',', '.') }}
                                                                                </p>
                                                                            </div>
                                                                            <div
                                                                                class="d-flex justify-content-between fw-bold p-3 bg-success-subtle rounded fs-5">
                                                                                <p class="mb-0">Grand Total</p>
                                                                                <p class="mb-0">
                                                                                    Rp{{ number_format($order['grand_total'], 0, ',', '.') }}
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-outline-secondary"
                                                                    data-bs-dismiss="modal">Tutup</button>
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
            --bs-success-rgb: 25, 135, 84;
            --timeline-line-color: #e9ecef;
            --timeline-icon-bg: #fff;
            --timeline-icon-color: var(--hero-color);
        }

        .btn-warning {
            background-color: var(--hero-color) !important;
            border-color: var(--hero-color) !important;
            color: #fff !important;
        }

        .btn-warning:hover {
            background-color: #e6600c !important;
            border-color: #e6600c !important;
        }

        .bg-hero {
            background-color: var(--hero-color) !important;
        }

        .accordion-button:not(.collapsed) {
            color: var(--hero-color);
            background-color: rgba(var(--hero-color-rgb), 0.1);
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .125);
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

        .table> :not(caption)>*>* {
            padding: 0.85rem 1rem;
        }

        .table td {
            vertical-align: middle;
        }

        .bg-success-subtle {
            background-color: rgba(var(--bs-success-rgb), 0.1) !important;
            color: var(--bs-success-darker, #0f5132) !important;
        }

        /* =================================== */
        /* STYLE UNTUK TIMELINE       */
        /* =================================== */
        .timeline {
            position: relative;
            padding-left: 1rem;
        }

        /* Garis vertikal di tengah timeline */
        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            /* Posisi garis, sesuaikan dengan posisi icon */
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: var(--timeline-line-color);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 40px;
            height: 40px;
            background-color: var(--timeline-icon-bg);
            border: 2px solid var(--timeline-line-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: var(--timeline-icon-color);
            z-index: 1;
            /* Agar icon berada di atas garis */
        }

        .timeline-content {
            padding-left: 60px;
            /* Jarak dari icon ke konten */
            padding-top: 5px;
            /* Sesuaikan agar sejajar dengan icon */
        }

        .timeline-content h6 {
            margin-top: 0;
            margin-bottom: 0.25rem;
        }
    </style>
@endpush

@push('scripts')
    {{-- No custom script needed for this design --}}
@endpush
