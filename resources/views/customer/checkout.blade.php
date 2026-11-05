@extends('layouts.app')

@section('title', 'Checkout')

@push('styles')
    @include('components.ui_styles')
    <style>
        .co-layout {
            display: flex;
            gap: 24px;
            align-items: flex-start
        }

        .co-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px
        }

        .co-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow-card);
            padding: 22px
        }

        .co-card h3 {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .co-card h3 a {
            color: var(--brand);
            font-size: 12px;
            font-weight: 600
        }

        .co-item {
            display: flex;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            font-size: 12px
        }

        .co-item:last-child {
            border-bottom: 0;
            padding-bottom: 0
        }

        .co-item:first-of-type {
            padding-top: 0
        }

        .co-img {
            width: 64px;
            height: 84px;
            background: #d9d9d9;
            border-radius: 4px;
            overflow: hidden;
            flex-shrink: 0
        }

        .co-img img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .co-info {
            flex: 1;
            line-height: 1.6
        }

        .co-info b {
            font-size: 13px
        }

        .co-info span {
            color: var(--muted)
        }

        .co-price {
            text-align: right;
            white-space: nowrap
        }

        .co-address {
            font-size: 13px;
            line-height: 1.7
        }

        .co-address .who {
            font-weight: 600
        }

        .co-summary {
            width: 330px;
            flex-shrink: 0
        }

        .co-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            padding: 7px 0
        }

        .co-row.sub {
            font-size: 11px;
            color: var(--muted);
            padding-top: 0
        }

        .co-row.total {
            border-top: 1px solid #eee;
            margin-top: 8px;
            padding-top: 14px;
            font-size: 15px;
            font-weight: 700
        }

        .co-pay {
            width: 100%;
            margin-top: 16px;
            padding: 13px;
            font-size: 14px
        }

        .co-note {
            font-size: 11px;
            color: var(--muted);
            margin-top: 10px;
            line-height: 1.5;
            text-align: center
        }

        #pay-error {
            display: none;
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            background: #fdeaea;
            color: #a12626;
            border: 1px solid #f3c1c1;
            font-size: 12px
        }

        @media (max-width: 900px) {
            .co-layout {
                flex-direction: column
            }

            .co-summary {
                width: 100%
            }
        }
    </style>
@endpush

@section('content')
    @php $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.'); @endphp
    <div class="page-wrap">
        <h1 class="page-title">Checkout</h1>

        @include('components.flash')

        <div class="co-layout">
            <div class="co-main">

                {{-- ===== DETAIL PESANAN ===== --}}
                <div class="co-card">
                    <h3>Detail Pesanan <a href="{{ route('cart.index') }}">Ubah keranjang</a></h3>

                    @foreach ($lines as $line)
                        @php $p = $line->product; @endphp
                        <div class="co-item">
                            <div class="co-img">
                                @if ($p->cover_url)
                                    <img src="{{ $p->cover_url }}" alt="{{ $p->PRODUCTS_NAME }}">
                                @endif
                            </div>
                            <div class="co-info">
                                <b>{{ $p->PRODUCTS_NAME }}</b><br>
                                <span>Brand: {{ $brands[$p->USERS_ID] ?? '-' }}</span><br>
                                <span>Size: {{ $line->CARTS_DETAILS_SELECTED_SIZE ?: '-' }} &middot; Qty:
                                    {{ $line->CARTS_DETAILS_QTY }}</span>
                                @if ($p->is_preorder)
                                    <br><span style="color:var(--brand)">Pre-Order</span>
                                @endif
                                @if (!$p->is_preorder && $line->CARTS_DETAILS_QTY > $p->PRODUCTS_STOCK)
                                    <br><span class="text-red">Stock not enough. Only {{ $p->PRODUCTS_STOCK }} left.</span>
                                @endif
                            </div>
                            <div class="co-price">
                                {{ $rp($p->PRODUCTS_PRICE) }}<br>
                                <b>{{ $rp($p->PRODUCTS_PRICE * $line->CARTS_DETAILS_QTY) }}</b>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ===== ALAMAT ===== --}}
                <div class="co-card">
                    <h3>Alamat Pengiriman <a href="{{ route('profile', ['next' => 'checkout']) }}">Ubah</a></h3>
                    <div class="co-address">
                        <span class="who">{{ $user->USERS_NAME }}</span> ({{ $user->USERS_PHONE_NUMBER }})<br>
                        {!! nl2br(e($address->toText())) !!}
                    </div>
                </div>
            </div>

            {{-- ===== RINGKASAN & BAYAR ===== --}}
            <aside class="co-card co-summary">
                <h3>Ringkasan</h3>
                <div class="co-row"><span>Subtotal ({{ $sum['qty'] }}
                        barang)</span><span>{{ $rp($sum['subtotal']) }}</span></div>
                <div class="co-row"><span>Ongkos kirim</span><span>{{ $rp($sum['shipping']) }}</span></div>
                <div class="co-row sub"><span>Surabaya &rarr; {{ $address->field('city') }}, {{ $rp($sum['rate']) }}/kg
                        &times; {{ $sum['kg'] }} kg</span></div>
                <div class="co-row total"><span>Total</span><span>{{ $rp($sum['total']) }}</span></div>

                <button type="button" id="pay-btn" class="btn btn-brand co-pay" @disabled($problems->isNotEmpty())>Bayar
                    Sekarang</button>
                <div id="pay-error"></div>
                <p class="co-note">Pembayaran diproses aman lewat Midtrans (transfer bank, e-wallet, QRIS, kartu).</p>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script
        src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        (function() {
            var btn = document.getElementById('pay-btn');
            var err = document.getElementById('pay-error');
            var finishUrl = "{{ url('/checkout/finish') }}/";
            var resumeUrl = "{{ url('/checkout/pay') }}/";

            function showError(msg) {
                err.textContent = msg;
                err.style.display = 'block';
            }

            btn.addEventListener('click', function() {
                err.style.display = 'none';
                btn.disabled = true;
                btn.textContent = 'Memproses...';

                fetch("{{ route('checkout.pay') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: '{}'
                    })
                    .then(function(res) {
                        return res.json().then(function(data) {
                            return {
                                ok: res.ok,
                                data: data
                            };
                        });
                    })
                    .then(function(r) {
                        if (!r.ok) {
                            showError(r.data.message || 'Gagal membuat pembayaran.');
                            btn.disabled = false;
                            btn.textContent = 'Bayar Sekarang';
                            return;
                        }
                        var orderId = r.data.order_id;
                        window.snap.pay(r.data.token, {
                            onSuccess: function() {
                                window.location = finishUrl + orderId;
                            },
                            onPending: function() {
                                window.location = finishUrl + orderId;
                            },
                            onError: function() {
                                window.location = finishUrl + orderId;
                            },
                            onClose: function() {
                                window.location = resumeUrl + orderId;
                            }
                        });
                    })
                    .catch(function() {
                        showError('Koneksi bermasalah. Coba lagi.');
                        btn.disabled = false;
                        btn.textContent = 'Bayar Sekarang';
                    });
            });
        })();
    </script>
@endpush