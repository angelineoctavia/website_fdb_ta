@extends('layouts.app')

@section('title', 'Cart')

@push('styles')
    @include('components.ui_styles')
    <style>
        .cart-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow-card);
            overflow: hidden
        }

        .cart-card table {
            width: 100%;
            border-collapse: collapse
        }

        .cart-card thead th {
            background: var(--brand);
            color: #fff;
            text-align: left;
            padding: 12px 14px;
            font-size: 12px;
            font-weight: 600
        }

        .cart-card td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
            vertical-align: middle
        }

        .cart-card tr:last-child td {
            border-bottom: 0
        }

        .cart-img {
            width: 70px;
            height: 92px;
            background: #d9d9d9;
            border-radius: 4px;
            overflow: hidden
        }

        .cart-img img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .cart-name {
            font-weight: 600;
            font-size: 13px;
            display: block;
            margin-bottom: 2px
        }

        .cart-name:hover {
            color: var(--brand)
        }

        .cart-sub {
            color: var(--muted);
            line-height: 1.6
        }

        .qty {
            display: inline-flex;
            align-items: center;
            gap: 8px
        }

        .qty form {
            margin: 0
        }

        .qty button {
            width: 24px;
            height: 24px;
            border: 1px solid #ccc;
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
        }

        .qty button:hover {
            border-color: var(--brand);
            color: var(--brand)
        }

        .qty-num {
            min-width: 28px;
            text-align: center;
            background: #eee;
            border-radius: 4px;
            padding: 4px 0
        }

        .cart-bottom {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 18px;
            margin-top: 18px;
            padding-bottom: 8px
        }

        .cart-total {
            font-size: 13px
        }

        .cart-total strong {
            font-size: 15px
        }

        @media (max-width: 800px) {
            .cart-card {
                overflow-x: auto
            }

            .cart-card table {
                min-width: 680px
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-wrap">
        <h1 class="page-title">Cart</h1>

        @include('components.flash')

        @if ($lines->isEmpty())
            <div class="cart-card">
                <div class="empty-state">
                    <p>Keranjang kamu masih kosong.</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-brand">Mulai Belanja</a>
                </div>
            </div>
        @else
            <div class="cart-card">
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px"></th>
                            <th>Image</th>
                            <th>Details</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lines as $line)
                            @php $p = $line->product; @endphp
                            <tr>
                                <td>
                                    <input type="checkbox" form="checkout-form" name="items[]"
                                        value="{{ $line->CARTS_DETAILS_ID }}" class="pick"
                                        data-subtotal="{{ $line->subtotal }}" checked>
                                </td>
                                <td>
                                    <a href="{{ route('shop.show', $p->PRODUCTS_ID) }}" class="cart-img"
                                        style="display:block">
                                        @if ($p->cover_url)
                                            <img src="{{ $p->cover_url }}" alt="{{ $p->PRODUCTS_NAME }}">
                                        @endif
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('shop.show', $p->PRODUCTS_ID) }}"
                                        class="cart-name">{{ $p->PRODUCTS_NAME }}</a>
                                    <div class="cart-sub">
                                        Brand: {{ $brands[$p->USERS_ID] ?? '-' }}<br>
                                        Size: {{ $line->CARTS_DETAILS_SELECTED_SIZE ?: '-' }}
                                        @if ($p->is_preorder)
                                            <br><span style="color:var(--brand)">Pre-Order</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="qty">
                                        <form method="POST" action="{{ route('cart.update', $line->CARTS_DETAILS_ID) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="action" value="dec">
                                            <button type="submit" aria-label="Kurangi">&minus;</button>
                                        </form>
                                        <span class="qty-num">{{ $line->CARTS_DETAILS_QTY }}</span>
                                        <form method="POST" action="{{ route('cart.update', $line->CARTS_DETAILS_ID) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="action" value="inc">
                                            <button type="submit" aria-label="Tambah">+</button>
                                        </form>
                                    </div>
                                </td>
                                <td>Rp {{ number_format($line->CARTS_DETAILS_PRICE, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($line->subtotal, 0, ',', '.') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('cart.destroy', $line->CARTS_DETAILS_ID) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-red"
                                            style="padding:7px 18px;font-size:12px">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form method="POST" id="checkout-form" action="{{ route('checkout.select') }}">
                @csrf
                <div class="cart-bottom">
                    <span class="cart-total">Total dipilih: <strong id="cart-total">Rp 0</strong></span>
                    <button type="submit" class="btn btn-dark" id="checkout-btn">Checkout</button>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            var picks = document.querySelectorAll('.pick');
            var totalEl = document.getElementById('cart-total');
            if (!totalEl) return;

            function rupiah(n) {
                return 'Rp ' + Math.round(n).toLocaleString('id-ID');
            }

            function recalc() {
                var sum = 0;
                picks.forEach(function(c) {
                    if (c.checked) sum += parseFloat(c.dataset.subtotal || 0);
                });
                totalEl.textContent = rupiah(sum);
            }

            picks.forEach(function(c) {
                c.addEventListener('change', recalc);
            });
            recalc();
        })();
    </script>
@endpush
