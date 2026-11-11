@extends('layouts.app')
@section('title', 'Order ' . $order->TRANSACTIONS_ID)

@push('styles')
    @include('components.ui_styles')
    <style>
        .od-page { padding: 24px 40px 48px; background: #f5f5f5 }
        .od-wrap { max-width: 900px; margin: 0 auto; font-size: 13px }
        .od-back { display: inline-block; font-size: 13px; margin-bottom: 14px }
        .od-back:hover { color: var(--brand, #C20042) }
        .od-card { background: #fff; border-radius: 14px; box-shadow: var(--shadow-card, 0 2px 12px rgba(0, 0, 0, .08)); padding: 20px 22px; margin-bottom: 16px }
        .od-title { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px }
        .od-title h1 { font-size: 18px; font-weight: 600 }
        .od-date { color: var(--muted, #777); font-size: 12px; margin-top: 2px }
        .ord-pill { padding: 3px 12px; border-radius: 99px; font-size: 12px; font-weight: 600 }
        .st-pending { background: #fff3cd; color: #8a6d00 }
        .st-paid { background: #d1ecf1; color: #0c5460 }
        .st-shipping { background: #e2d9f3; color: #4b2e83 }
        .st-completed { background: #d4edda; color: #1b5e20 }
        .st-failed { background: #f8d7da; color: #8a1c28 }
        .od-desc { margin-top: 12px; color: #444 }

        /* timeline status */
        .od-steps { list-style: none; display: flex; margin: 20px 0 4px; padding: 0 }
        .od-steps li { flex: 1; display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; font-size: 11.5px; color: #9ca3af }
        .od-steps li::before { content: ''; position: absolute; top: 13px; left: -50%; width: 100%; height: 2px; background: #e5e7eb; z-index: 0 }
        .od-steps li:first-child::before { display: none }
        .od-steps .dot { position: relative; z-index: 1; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: #e5e7eb; color: #6b7280; font-weight: 600; margin-bottom: 6px }
        .od-steps li.done, .od-steps li.now { color: #111 }
        .od-steps li.done .dot, .od-steps li.now .dot { background: var(--brand, #C20042); color: #fff }
        .od-steps li.done::before, .od-steps li.now::before { background: var(--brand, #C20042) }
        .od-steps li.now { font-weight: 600 }
        .od-steps .when { display: block; font-weight: 400; color: #777; font-size: 10.5px; margin-top: 2px }
        .od-banner { margin-top: 14px; padding: 12px 14px; border-radius: 10px; background: #f8d7da; color: #8a1c28 }

        .od-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px }
        .od-card h3 { font-size: 13px; font-weight: 600; margin-bottom: 10px }
        .od-kv { display: flex; justify-content: space-between; gap: 12px; padding: 4px 0 }
        .od-kv span:first-child { color: var(--muted, #777) }
        .copy-btn { border: 1px solid #c8b6e2; background: #fff; border-radius: 6px; padding: 2px 8px; font-size: 11px; cursor: pointer; margin-left: 6px }
        .od-addr { line-height: 1.6; white-space: pre-line }
        .od-item { display: flex; gap: 12px; align-items: center; padding: 12px 0; border-bottom: 1px solid #f3f3f3 }
        .od-item:last-child { border-bottom: 0 }
        .od-thumb { width: 48px; height: 64px; border-radius: 6px; background: #d9d9d9; object-fit: cover; flex-shrink: 0 }
        .od-name { font-weight: 600 }
        .od-sub { color: var(--muted, #777); font-size: 12px }
        .od-price { margin-left: auto; text-align: right; white-space: nowrap }
        .od-sum .od-kv.total { border-top: 1px solid #eee; margin-top: 6px; padding-top: 10px; font-weight: 700; font-size: 14px }

        @media (max-width: 800px) {
            .od-page { padding: 18px }
            .od-grid { grid-template-columns: 1fr }
            .od-steps li { font-size: 10px }
        }
    </style>
@endpush

@section('content')
    @php
        $rp = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $couriers = ['JNT' => 'J&T Express', 'JNE' => 'JNE', 'SiCepat' => 'SiCepat', 'AnterAja' => 'AnterAja'];
        $st = $order->TRANSACTIONS_ORDER_STATUS;
        $meta = $statusMeta[$st] ?? ['label' => ucfirst($st), 'desc' => '', 'class' => 'pending'];
        $idx = array_search($st, $steps, true); // false = dibatalkan / kedaluwarsa / gagal
        $itemsTotal = $items->sum('TRANSACTIONS_SUBTOTAL');
        $shipCost = (float) ($order->TRANSACTIONS_SHIPPING_COST ?? 0);

        // Waktu di DB tersimpan dalam timezone aplikasi. Kita kirim sebagai UTC (ISO 8601),
        // lalu browser mengubahnya ke jam lokal perangkat masing-masing (lihat script di bawah).
        $appTz = config('app.timezone');
        $iso = fn($t) => \Carbon\Carbon::parse($t, $appTz)->utc()->toIso8601String();
        $fallback = fn($t) => \Carbon\Carbon::parse($t, $appTz)->format('d M, H:i');
    @endphp

    <div class="od-page">
        <div class="od-wrap">
            <a href="{{ route('customer.orders') }}" class="od-back">&lsaquo; My Orders</a>

            @include('components.flash')

            {{-- STATUS --}}
            <div class="od-card">
                <div class="od-title">
                    <div>
                        <h1>Order {{ $order->TRANSACTIONS_ID }}</h1>
                        <div class="od-date">{{ \Carbon\Carbon::parse($order->TRANSACTIONS_DATE)->format('d M Y') }}</div>
                    </div>
                    <span class="ord-pill st-{{ $meta['class'] }}">{{ $meta['label'] }}</span>
                </div>

                @if ($idx === false)
                    <div class="od-banner">{{ $meta['desc'] ?: 'Pesanan tidak dilanjutkan.' }}</div>
                @else
                    <ol class="od-steps">
                        @foreach ($steps as $i => $s)
                            <li class="{{ $i < $idx ? 'done' : ($i === $idx ? 'now' : '') }}">
                                <span class="dot">{{ $i <= $idx ? '✓' : $i + 1 }}</span>
                                {{ $stepLabels[$s] }}
                                @if ($s === 'paid' && $order->TRANSACTIONS_PAID_AT)
                                    <time class="when js-local"
                                        datetime="{{ $iso($order->TRANSACTIONS_PAID_AT) }}">{{ $fallback($order->TRANSACTIONS_PAID_AT) }}</time>
                                @elseif ($i === $idx && $i > 1 && $order->updated_at)
                                    <time class="when js-local"
                                        datetime="{{ $iso($order->updated_at) }}">{{ $fallback($order->updated_at) }}</time>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                    <p class="od-desc">{{ $meta['desc'] }}</p>
                @endif
            </div>

            <div class="od-grid">
                {{-- PENGIRIMAN + RESI --}}
                <div class="od-card">
                    <h3>Shipping</h3>
                    <div class="od-kv"><span>Courier</span>
                        <span>{{ $order->TRANSACTIONS_COURIER ? $couriers[$order->TRANSACTIONS_COURIER] ?? $order->TRANSACTIONS_COURIER : '-' }}</span>
                    </div>
                    <div class="od-kv"><span>Tracking number</span>
                        <span>
                            @if ($order->TRANSACTIONS_TRACKING_NUMBER)
                                <b>{{ $order->TRANSACTIONS_TRACKING_NUMBER }}</b>
                                <button type="button" class="copy-btn"
                                    data-copy="{{ $order->TRANSACTIONS_TRACKING_NUMBER }}">Copy</button>
                            @elseif ($st === 'shipping')
                                Belum diinput seller
                            @else
                                Belum tersedia
                            @endif
                        </span>
                    </div>
                    <div style="margin-top:12px;font-weight:600">Alamat pengiriman</div>
                    <p class="od-addr">{{ $order->TRANSACTIONS_SHIPPING_ADDRESS ?: '-' }}</p>
                    @if ($order->TRANSACTIONS_SHIPPING_NOTES)
                        <p class="od-sub" style="margin-top:6px">Catatan: {{ $order->TRANSACTIONS_SHIPPING_NOTES }}</p>
                    @endif
                </div>

                {{-- RINGKASAN PEMBAYARAN --}}
                <div class="od-card od-sum">
                    <h3>Payment</h3>
                    <div class="od-kv"><span>Method</span><span>{{ $order->TRANSACTIONS_PAYMENT_METHOD ?: '-' }}</span>
                    </div>
                    <div class="od-kv"><span>Subtotal</span><span>{{ $rp($itemsTotal) }}</span></div>
                    <div class="od-kv"><span>Shipping cost</span><span>{{ $rp($shipCost) }}</span></div>
                    <div class="od-kv total"><span>Total</span><span>{{ $rp($order->TRANSACTIONS_TOTAL_AMOUNT) }}</span>
                    </div>
                </div>
            </div>

            {{-- ITEM --}}
            <div class="od-card">
                <h3>Items</h3>
                @foreach ($items as $it)
                    <div class="od-item">
                        @if ($it->image)
                            <img class="od-thumb" src="{{ asset('storage/' . $it->image) }}" alt="">
                        @else
                            <div class="od-thumb"></div>
                        @endif
                        <div>
                            <div class="od-name">{{ $it->PRODUCTS_NAME }}</div>
                            <div class="od-sub">{{ $it->brand }}
                                @if ($it->TRANSACTIONS_DETAILS_SELECTED_SIZE)
                                    &middot; Size {{ $it->TRANSACTIONS_DETAILS_SELECTED_SIZE }}
                                @endif
                            </div>
                            @if ($it->TRANSACTIONS_DETAILS_CUSTOM_SIZE_NOTES)
                                <div class="od-sub">Custom: {{ $it->TRANSACTIONS_DETAILS_CUSTOM_SIZE_NOTES }}</div>
                            @endif
                        </div>
                        <div class="od-price">
                            {{ $rp($it->TRANSACTIONS_SUBTOTAL / max($it->TRANSACTIONS_QTY, 1)) }} &times;
                            {{ $it->TRANSACTIONS_QTY }}<br>
                            <b>{{ $rp($it->TRANSACTIONS_SUBTOTAL) }}</b>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Ubah semua waktu ke jam lokal perangkat pengguna (WIB, WITA, WIT, atau zona lain).
        document.querySelectorAll('time.js-local').forEach(function(el) {
            var d = new Date(el.getAttribute('datetime'));
            if (isNaN(d.getTime())) return;
            el.textContent = d.toLocaleString('en-GB', {
                day: '2-digit',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            el.title = Intl.DateTimeFormat().resolvedOptions().timeZone;
        });

        document.querySelectorAll('.copy-btn').forEach(function(b) {
            b.addEventListener('click', function() {
                navigator.clipboard.writeText(b.dataset.copy).then(function() {
                    var t = b.textContent;
                    b.textContent = 'Copied!';
                    setTimeout(function() {
                        b.textContent = t;
                    }, 1500);
                });
            });
        });
    </script>
@endpush