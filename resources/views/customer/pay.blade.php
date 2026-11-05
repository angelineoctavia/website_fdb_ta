@extends('layouts.app')

@section('title', 'Lanjutkan Pembayaran')

@push('styles')
    @include('components.ui_styles')
    <style>
        .pay-card {
            max-width: 460px;
            margin: 30px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow-card);
            padding: 30px;
            text-align: center
        }

        .pay-card h2 {
            font-size: 17px;
            margin-bottom: 8px
        }

        .pay-card p {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 6px
        }

        .pay-total {
            font-size: 22px;
            font-weight: 700;
            margin: 14px 0 20px;
            color: #1a1a1a
        }

        .pay-card .btn {
            padding: 12px 30px
        }
    </style>
@endpush

@section('content')
    <div class="page-wrap" style="min-height:420px">
        <div class="pay-card">
            <h2>Menunggu Pembayaran</h2>
            <p>Order <b>{{ $order->TRANSACTIONS_ID }}</b></p>
            <div class="pay-total">Rp {{ number_format($order->TRANSACTIONS_TOTAL_AMOUNT, 0, ',', '.') }}</div>
            <button type="button" id="pay-btn" class="btn btn-brand">Bayar Sekarang</button>
            <p style="margin-top:14px"><a href="{{ route('home') }}" style="color:var(--brand)">Kembali ke Home</a></p>
        </div>
    </div>
@endsection

@push('scripts')
    <script
        src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        document.getElementById('pay-btn').addEventListener('click', function() {
            var finishUrl = "{{ route('checkout.finish', $order->TRANSACTIONS_ID) }}";
            window.snap.pay("{{ $order->TRANSACTIONS_SNAP_TOKEN }}", {
                onSuccess: function() {
                    window.location = finishUrl;
                },
                onPending: function() {
                    window.location = finishUrl;
                },
                onError: function() {
                    window.location = finishUrl;
                },
                onClose: function() {}
            });
        });
    </script>
@endpush