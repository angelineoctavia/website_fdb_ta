<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use App\Services\OrderPaymentService;
use Illuminate\Http\Request;

class MidtransController extends Controller
{
    /** Webhook dari Midtrans (Payment Notification URL). Tanpa login & tanpa CSRF, diamankan signature. */
    public function notification(Request $request, MidtransService $midtrans, OrderPaymentService $payments)
    {
        $p = $request->all();

        if (! $midtrans->validSignature($p)) {
            return response()->json(['message' => 'invalid signature'], 403);
        }

        $payments->apply(
            (string) $p['order_id'],
            (string) ($p['transaction_status'] ?? 'pending'),
            $p['fraud_status'] ?? null,
            $p['payment_type'] ?? null,
        );

        return response()->json(['message' => 'ok']);
    }
}
