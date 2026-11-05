@extends('layouts.seller')
@section('content')
    @include('seller.styles')
    <style>
        .sc-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
            background: #eee;
            color: #555
        }

        .sc-pill.pending {
            background: #fff4d6;
            color: #8a6100
        }

        .sc-pill.paid {
            background: #e0efff;
            color: #0b5cad
        }

        .sc-pill.packing {
            background: #efe3ff;
            color: #6a2cc0
        }

        .sc-pill.shipping {
            background: #d9f4f2;
            color: #0b7a73
        }

        .sc-pill.completed {
            background: #e7f6ec;
            color: #1b7a3a
        }

        .sc-pill.cancelled {
            background: #fde8e8;
            color: #b42318
        }
    </style>

    @php
        $statusLabels = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'packing' => 'Packing',
            'shipping' => 'In Shipping',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    @endphp

    <div class="sc-wrap">
        <div
            style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px">
            <h3 style="margin:0">Orders</h3>
            <form method="GET" style="display:flex;gap:8px">
                <input class="sc-input" style="width:240px" name="q" value="{{ $q }}"
                    placeholder="Cari ID order / nama / produk">
                <select class="sc-input" style="width:150px" name="status" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach (['pending' => 'Pending', 'paid' => 'Paid (perlu diproses)', 'packing' => 'Packing', 'shipping' => 'In Shipping', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $k => $v)
                        <option value="{{ $k }}" @selected($status == $k)>{{ $v }}</option>
                    @endforeach
                </select>
                <button class="sc-btn">Cari</button>
            </form>
        </div>

        <div class="sc-card" style="padding:0;overflow:hidden">
            <table class="sc-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Products</th>
                        <th>Qty</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                        @php
                            $st = $o->TRANSACTIONS_ORDER_STATUS;
                            $label = $statusLabels[$st] ?? ucfirst($st);
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($o->TRANSACTIONS_DATE)->format('d M Y') }}</td>
                            <td>{{ $o->TRANSACTIONS_ID }}</td>
                            <td>{{ $o->customer }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($o->products, 50) }}</td>
                            <td>{{ $o->qty }}</td>
                            <td>Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                            <td><span class="sc-pill {{ $st }}">{{ $label }}</span></td>
                            <td><a class="sc-btn-dark" href="{{ route('seller.orders.show', $o->TRANSACTIONS_ID) }}">View
                                    Details</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:24px">Belum ada pesanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px">{{ $orders->links() }}</div>
    </div>
@endsection