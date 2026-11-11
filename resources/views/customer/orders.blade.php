@extends('layouts.app')
@section('title', 'My Orders')

@push('styles')
    @include('components.ui_styles')
    <style>
        .ord-page {
            padding: 24px 40px 48px;
            background: #f5f5f5;
            min-height: 60vh
        }

        .ord-wrap {
            max-width: 900px;
            margin: 0 auto
        }

        .ord-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 14px
        }

        .ord-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px
        }

        .ord-tab {
            padding: 7px 14px;
            border-radius: 99px;
            background: #e9ebee;
            font-size: 12.5px;
            color: #4b5563
        }

        .ord-tab.on {
            background: var(--brand, #C20042);
            color: #fff
        }

        .ord-tab span {
            opacity: .8;
            margin-left: 4px
        }

        .ord-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow-card, 0 2px 12px rgba(0, 0, 0, .08));
            padding: 18px 20px;
            margin-bottom: 16px;
            font-size: 13px
        }

        .ord-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            padding-bottom: 12px;
            border-bottom: 1px solid #f0f0f0
        }

        .ord-id {
            font-weight: 600
        }

        .ord-date {
            color: var(--muted, #777);
            font-size: 12px
        }

        .ord-pill {
            padding: 3px 12px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 600
        }

        .st-pending {
            background: #fff3cd;
            color: #8a6d00
        }

        .st-paid {
            background: #d1ecf1;
            color: #0c5460
        }

        .st-shipping {
            background: #e2d9f3;
            color: #4b2e83
        }

        .st-completed {
            background: #d4edda;
            color: #1b5e20
        }

        .st-failed {
            background: #f8d7da;
            color: #8a1c28
        }

        .ord-item {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f7f7f7
        }

        .ord-thumb {
            width: 44px;
            height: 58px;
            border-radius: 6px;
            background: #d9d9d9;
            object-fit: cover;
            flex-shrink: 0
        }

        .ord-item-name {
            font-weight: 600
        }

        .ord-item-sub {
            color: var(--muted, #777);
            font-size: 12px
        }

        .ord-item-price {
            margin-left: auto;
            white-space: nowrap
        }

        .ord-more {
            padding: 8px 0;
            color: var(--muted, #777);
            font-size: 12px
        }

        .ord-ship {
            background: #faf5ff;
            border: 1px solid #eadcf9;
            border-radius: 10px;
            padding: 10px 14px;
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap
        }

        .ord-ship b {
            letter-spacing: .3px
        }

        .copy-btn {
            border: 1px solid #c8b6e2;
            background: #fff;
            border-radius: 6px;
            padding: 3px 10px;
            font-size: 11px;
            cursor: pointer
        }

        .ord-foot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 14px;
            gap: 10px;
            flex-wrap: wrap
        }

        .ord-total {
            font-size: 14px
        }

        .ord-total b {
            font-size: 15px
        }

        .ord-btn {
            background: var(--dark, #333);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12.5px
        }

        .ord-empty {
            background: #fff;
            border-radius: 14px;
            padding: 50px 20px;
            text-align: center;
            color: #6b7280
        }

        @media (max-width: 700px) {
            .ord-page {
                padding: 18px
            }
        }
    </style>
@endpush

@section('content')
    @php
        $rp = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $couriers = ['JNT' => 'J&T Express', 'JNE' => 'JNE', 'SiCepat' => 'SiCepat', 'AnterAja' => 'AnterAja'];
    @endphp

    <div class="ord-page">
        <div class="ord-wrap">
            <h1 class="ord-title">My Orders</h1>

            @include('components.flash')

            <div class="ord-tabs">
                @foreach ($tabs as $key => $t)
                    <a class="ord-tab {{ $tab === $key ? 'on' : '' }}"
                        href="{{ route('customer.orders', $key === 'all' ? [] : ['tab' => $key]) }}">{{ $t['label'] }}
                        @if ($t['count'])
                            <span>{{ $t['count'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

            @forelse ($orders as $o)
                @php
                    $st = $o->TRANSACTIONS_ORDER_STATUS;
                    $meta = $statusMeta[$st] ?? ['label' => ucfirst($st), 'desc' => '', 'class' => 'pending'];
                    $list = $items[$o->TRANSACTIONS_ID] ?? collect();
                    $hasShip = $o->TRANSACTIONS_COURIER || $o->TRANSACTIONS_TRACKING_NUMBER;
                @endphp
                <div class="ord-card">
                    <div class="ord-head">
                        <div>
                            <span class="ord-id">{{ $o->TRANSACTIONS_ID }}</span>
                            <span class="ord-date">&middot;
                                {{ \Carbon\Carbon::parse($o->TRANSACTIONS_DATE)->format('d M Y') }}</span>
                        </div>
                        <span class="ord-pill st-{{ $meta['class'] }}">{{ $meta['label'] }}</span>
                    </div>

                    @foreach ($list->take(3) as $it)
                        <div class="ord-item">
                            @if ($it->image)
                                <img class="ord-thumb" src="{{ asset('storage/' . $it->image) }}" alt="">
                            @else
                                <div class="ord-thumb"></div>
                            @endif
                            <div>
                                <div class="ord-item-name">{{ $it->PRODUCTS_NAME }}</div>
                                <div class="ord-item-sub">{{ $it->brand }}
                                    @if ($it->TRANSACTIONS_DETAILS_SELECTED_SIZE)
                                        &middot; Size {{ $it->TRANSACTIONS_DETAILS_SELECTED_SIZE }}
                                    @endif
                                    &middot; x{{ $it->TRANSACTIONS_QTY }}
                                </div>
                            </div>
                            <div class="ord-item-price">{{ $rp($it->TRANSACTIONS_SUBTOTAL) }}</div>
                        </div>
                    @endforeach
                    @if ($list->count() > 3)
                        <div class="ord-more">+ {{ $list->count() - 3 }} produk lainnya</div>
                    @endif

                    {{-- Nomor resi (diisi seller saat status In Shipping) --}}
                    @if ($hasShip)
                        <div class="ord-ship">
                            <span>{{ $couriers[$o->TRANSACTIONS_COURIER] ?? ($o->TRANSACTIONS_COURIER ?: 'Courier') }}</span>
                            @if ($o->TRANSACTIONS_TRACKING_NUMBER)
                                <span>Resi: <b>{{ $o->TRANSACTIONS_TRACKING_NUMBER }}</b></span>
                                <button type="button" class="copy-btn"
                                    data-copy="{{ $o->TRANSACTIONS_TRACKING_NUMBER }}">Copy</button>
                            @endif
                        </div>
                    @elseif ($st === 'shipping')
                        <div class="ord-ship">Nomor resi belum diinput seller.</div>
                    @endif

                    <div class="ord-foot">
                        <div class="ord-total">Total: <b>{{ $rp($o->TRANSACTIONS_TOTAL_AMOUNT) }}</b></div>
                        <a class="ord-btn" href="{{ route('customer.orders.show', $o->TRANSACTIONS_ID) }}">View Details</a>
                    </div>
                </div>
            @empty
                <div class="ord-empty">
                    Belum ada pesanan di kategori ini.<br><br>
                    <a class="ord-btn" href="{{ route('shop.index') }}">Mulai belanja</a>
                </div>
            @endforelse

            <div style="margin-top:8px">{{ $orders->links() }}</div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
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