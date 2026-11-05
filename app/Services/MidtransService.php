<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey    = (string) config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /** Buat Snap token dari parameter transaksi. */
    public function snapToken(array $params): string
    {
        return Snap::getSnapToken($params);
    }

    /** Status transaksi langsung dari server Midtrans (dipakai saat customer kembali dari pembayaran). */
    public function status(string $orderId): object
    {
        return (object) Transaction::status($orderId);
    }

    /** Verifikasi signature notifikasi: SHA512(order_id + status_code + gross_amount + server_key). */
    public function validSignature(array $p): bool
    {
        if (! isset($p['order_id'], $p['status_code'], $p['gross_amount'], $p['signature_key'])) {
            return false;
        }

        $expected = hash('sha512', $p['order_id'] . $p['status_code'] . $p['gross_amount'] . config('midtrans.server_key'));

        return hash_equals($expected, (string) $p['signature_key']);
    }
}