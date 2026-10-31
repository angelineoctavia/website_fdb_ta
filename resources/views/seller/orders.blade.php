@extends('layouts.seller')
@section('content')
    @include('seller.styles')
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
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($o->TRANSACTIONS_DATE)->format('d M Y') }}</td>
                            <td>{{ $o->TRANSACTIONS_ID }}</td>
                            <td>{{ $o->customer }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($o->products, 50) }}</td>
                            <td>{{ $o->qty }}</td>
                            <td>Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                            <td><span
                                    class="sc-pill {{ $o->TRANSACTIONS_ORDER_STATUS }}">{{ ucfirst($o->TRANSACTIONS_ORDER_STATUS) }}</span>
                            </td>
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