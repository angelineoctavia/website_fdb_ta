@extends('layouts.seller')
@section('content')
    @include('seller.styles')
    @php $st = $order->TRANSACTIONS_ORDER_STATUS; @endphp
    <div class="sc-wrap">
        <a href="{{ route('seller.orders') }}" style="font-size:13px">← Kembali ke Orders</a>
        <h3 style="margin:8px 0 12px">Order {{ $order->TRANSACTIONS_ID }}
            <span class="sc-pill {{ $st }}">{{ ucfirst($st) }}</span>
        </h3>

        <div class="sc-split">
            <div class="sc-card">
                <b>Alamat Pengiriman</b>
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
            </div>

            <div class="sc-card">
                <b>Pembayaran (Midtrans)</b>
                <p style="margin:8px 0 0;font-size:13px">
                    Metode: {{ $order->TRANSACTIONS_PAYMENT_METHOD ?: '-' }}<br>
                    Status: {{ in_array($st, ['pending', 'cancelled']) ? ucfirst($st) : 'Lunas' }}<br>
                    Tanggal: {{ \Carbon\Carbon::parse($order->TRANSACTIONS_DATE)->format('d M Y') }}
                </p>
                @if ($order->TRANSACTIONS_COURIER)
                    <p style="font-size:13px">Kurir: {{ $order->TRANSACTIONS_COURIER }}<br>Resi:
                        {{ $order->TRANSACTIONS_TRACKING_NUMBER }}</p>
                @endif
            </div>
        </div>

        <div class="sc-card" style="margin-top:16px;padding:0;overflow:hidden">
            <table class="sc-table">
                <thead>
                    <tr>
                        <th>Produk</th>
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
                            <td>{{ $it->TRANSACTIONS_DETAILS_SELECTED_SIZE ?: '-' }}</td>
                            <td>Rp
                                {{ number_format($it->TRANSACTIONS_SUBTOTAL / max($it->TRANSACTIONS_QTY, 1), 0, ',', '.') }}
                            </td>
                            <td>{{ $it->TRANSACTIONS_QTY }}</td>
                            <td>Rp {{ number_format($it->TRANSACTIONS_SUBTOTAL, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" style="text-align:right"><b>Total</b></td>
                        <td><b>Rp {{ number_format($sellerTotal, 0, ',', '.') }}</b></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="sc-card" style="margin-top:16px">
            <b>Update Status Pengiriman</b>
            @if ($nextOptions)
                <form method="POST" action="{{ route('seller.orders.update', $order->TRANSACTIONS_ID) }}">
                    @csrf @method('PATCH')
                    <label class="sc-label">Ubah ke</label>
                    <select class="sc-input" style="max-width:240px" name="status" id="status">
                        @foreach ($nextOptions as $n)
                            <option value="{{ $n }}">
                                {{ ['packing' => 'Packing', 'shipping' => 'In Shipping', 'completed' => 'Completed'][$n] }}
                            </option>
                        @endforeach
                    </select>
                    <div id="ship" style="display:none;max-width:240px">
                        <label class="sc-label">Kurir</label>
                        <select class="sc-input" name="courier">
                            @foreach (['JNT', 'JNE', 'SiCepat', 'AnterAja', 'Lainnya'] as $k)
                                <option>{{ $k }}</option>
                            @endforeach
                        </select>
                        <label class="sc-label">No. Resi</label>
                        <input class="sc-input" name="tracking_number">
                    </div>
                    <div style="margin-top:12px"><button class="sc-btn">Simpan</button></div>
                </form>
                <script>
                    const s = document.getElementById('status'),
                        sh = document.getElementById('ship');
                    const t = () => sh.style.display = s.value === 'shipping' ? 'block' : 'none';
                    s.onchange = t;
                    t();
                </script>
            @else
                <p style="font-size:13px;margin:8px 0 0">
                    @if ($st === 'pending')
                        Menunggu pembayaran customer. Tombol update muncul setelah status berubah jadi Paid.
                    @elseif($st === 'completed')
                        Pesanan selesai.
                    @else
                        Tidak ada aksi tersedia.
                    @endif
                </p>
            @endif
        </div>
    </div>
@endsection
