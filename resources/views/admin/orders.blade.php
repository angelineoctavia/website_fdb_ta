@extends('layouts.seller')
@section('title', 'Admin Orders')
@section('header')@include('components.admin_header')@endsection

@section('content')
    @include('seller.styles')
    @php $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.'); @endphp
    <div class="sc-wrap" style="max-width:1280px">
        <div
            style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px">
            <h3 style="margin:0">Orders</h3>
            <form method="GET" style="display:flex;gap:8px">
                <input class="sc-input" style="width:260px" name="q" value="{{ $q }}"
                    placeholder="Cari ID / customer / seller / produk">
                <select class="sc-input" style="width:150px" name="status" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach (['pending' => 'Pending', 'paid' => 'Paid', 'packing' => 'Packing', 'shipping' => 'In Shipping', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $k => $v)
                        <option value="{{ $k }}" @selected($status == $k)>{{ $v }}</option>
                    @endforeach
                </select>
                <button class="sc-btn">Cari</button>
            </form>
        </div>

        <div class="sc-card" style="padding:0;overflow-x:auto">
            <table class="sc-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Seller</th>
                        <th>Products</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Admin 20%</th>
                        <th>Seller 80%</th>
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
                            <td>{{ $o->sellers }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($o->products, 40) }}</td>
                            <td>{{ $o->qty }}</td>
                            <td>{{ $rp($o->TRANSACTIONS_TOTAL_AMOUNT) }}</td>
                            <td>{{ $rp($o->fee) }}</td>
                            <td>{{ $rp($o->seller_pay) }}</td>
                            <td><span
                                    class="sc-pill {{ $o->TRANSACTIONS_ORDER_STATUS }}">{{ ucfirst($o->TRANSACTIONS_ORDER_STATUS) }}</span>
                            </td>
                            <td><a class="sc-btn-dark" href="{{ route('admin.orders.show', $o->TRANSACTIONS_ID) }}">View
                                    Details</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align:center;padding:24px">Belum ada pesanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px">{{ $orders->links() }}</div>
    </div>
@endsection