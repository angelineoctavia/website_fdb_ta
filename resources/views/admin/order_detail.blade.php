@extends('layouts.seller')
@section('title', 'Order ' . $order->TRANSACTIONS_ID)
@section('header')@include('components.admin_header')@endsection

@section('content')
    @include('seller.styles')
    @php
        $rp = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $st = $order->TRANSACTIONS_ORDER_STATUS;
        $pct = (int) round($feeRate * 100);
    @endphp
    <div class="sc-wrap">
        <a href="{{ route('admin.orders') }}" style="font-size:13px">← Kembali ke Orders</a>
        <h3 style="margin:8px 0 12px">Order {{ $order->TRANSACTIONS_ID }}
            <span class="sc-pill {{ $st }}">{{ ucfirst($st) }}</span>
        </h3>

        <div class="sc-split">
            <div class="sc-card">
                <b>Customer &amp; Alamat Pengiriman</b>
                <p style="margin:8px 0 0;font-size:13px">
                    {{ $order->customer }} @if ($order->customer_phone)
                        ({{ $order->customer_phone }})
                    @endif
                    <br>
                    {!! nl2br(e($order->TRANSACTIONS_SHIPPING_ADDRESS ?: '- belum diisi customer -')) !!}
                </p>
                @if ($order->TRANSACTIONS_SHIPPING_NOTES)
                    <p style="font-size:13px"><b>Catatan:</b> {{ $order->TRANSACTIONS_SHIPPING_NOTES }}</p>
                @endif
                @if ($order->TRANSACTIONS_COURIER)
                    <p style="font-size:13px">Kurir: {{ $order->TRANSACTIONS_COURIER }} &middot; Resi:
                        {{ $order->TRANSACTIONS_TRACKING_NUMBER }}</p>
                @endif
            </div>

            <div class="sc-card">
                <b>Pembayaran &amp; Pembagian</b>
                <table style="font-size:13px;margin-top:8px;width:100%">
                    <tr>
                        <td>Metode</td>
                        <td style="text-align:right">{{ $order->TRANSACTIONS_PAYMENT_METHOD ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td style="text-align:right">
                            {{ \Carbon\Carbon::parse($order->TRANSACTIONS_DATE)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td>Total dibayar customer</td>
                        <td style="text-align:right"><b>{{ $rp($order->TRANSACTIONS_TOTAL_AMOUNT) }}</b></td>
                    </tr>
                    <tr>
                        <td>Pendapatan admin ({{ $pct }}%)</td>
                        <td style="text-align:right">{{ $rp($fee) }}</td>
                    </tr>
                    <tr>
                        <td>Hak seller ({{ 100 - $pct }}%)</td>
                        <td style="text-align:right">{{ $rp($sellerPay) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="sc-card" style="margin-top:16px;padding:0;overflow-x:auto">
            <table class="sc-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Seller</th>
                        <th>Ukuran</th>
                        <th>Harga Satuan</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $it)
                        <tr>
                            <td>{{ $it->PRODUCTS_NAME }}
                                @if ($it->TRANSACTIONS_DETAILS_CUSTOM_SIZE_NOTES)
                                    <br><small>Custom: {{ $it->TRANSACTIONS_DETAILS_CUSTOM_SIZE_NOTES }}</small>
                                @endif
                            </td>
                            <td>{{ $it->seller }}</td>
                            <td>{{ $it->TRANSACTIONS_DETAILS_SELECTED_SIZE ?: '-' }}</td>
                            <td>{{ $rp($it->TRANSACTIONS_SUBTOTAL / max($it->TRANSACTIONS_QTY, 1)) }}</td>
                            <td>{{ $it->TRANSACTIONS_QTY }}</td>
                            <td>{{ $rp($it->TRANSACTIONS_SUBTOTAL) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p style="font-size:12px;color:#777;margin-top:10px">Status pengiriman (Packing → In Shipping → Completed) diubah
            oleh seller; halaman ini hanya untuk melihat.</p>
    </div>
@endsection