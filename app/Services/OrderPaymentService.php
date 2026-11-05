<?php

namespace App\Services;

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    /**
     * Terapkan status dari Midtrans ke tabel transactions.
     * Aman dipanggil berulang (webhook + redirect balik): hanya order yang masih 'pending' yang diubah.
     *
     * @return string status order sekarang: pending | paid | cancelled | missing | (status lain)
     */
    public function apply(string $orderId, string $midtransStatus, ?string $fraud = null, ?string $paymentType = null): string
    {
        $new = match (true) {
            $midtransStatus === 'settlement'                                         => 'paid',
            $midtransStatus === 'capture'                                            => $fraud === 'challenge' ? 'pending' : 'paid',
            in_array($midtransStatus, ['deny', 'cancel', 'expire', 'failure'], true) => 'cancelled',
            default                                                                  => 'pending',
        };

        return DB::transaction(function () use ($orderId, $new, $paymentType) {
            $t = DB::table('transactions')->where('TRANSACTIONS_ID', $orderId)->lockForUpdate()->first();

            if (! $t) {
                return 'missing';
            }
            if ($t->TRANSACTIONS_ORDER_STATUS !== 'pending') {
                // Order sudah paid tapi metode bayar belum tercatat: isi sekarang
                if ($t->TRANSACTIONS_ORDER_STATUS === 'paid' && $paymentType && ! $t->TRANSACTIONS_PAYMENT_METHOD) {
                    DB::table('transactions')->where('TRANSACTIONS_ID', $orderId)
                        ->update(['TRANSACTIONS_PAYMENT_METHOD' => $paymentType]);
                }

                return $t->TRANSACTIONS_ORDER_STATUS;
            }

            if ($new === 'paid') {
                // Ongkir tidak ikut dibagi: pembagian admin/seller dihitung dari harga barang saja.
                $items = (float) $t->TRANSACTIONS_TOTAL_AMOUNT - (float) $t->TRANSACTIONS_SHIPPING_COST;
                $fee   = round($items * AdminController::FEE_RATE, 2);

                DB::table('transactions')->where('TRANSACTIONS_ID', $orderId)->update([
                    'TRANSACTIONS_ORDER_STATUS'   => 'paid',
                    'TRANSACTIONS_PAYMENT_METHOD' => $paymentType,
                    'TRANSACTIONS_PRODI_FEE'      => $fee,
                    'TRANSACTIONS_SELLER_PAY'     => $items - $fee,
                    'TRANSACTIONS_PAID_AT'        => now(),
                    'updated_at'                  => now(),
                ]);

                return 'paid';
            }

            if ($new === 'cancelled') {
                // Kembalikan stok produk ready stock (READY_TYPE 1) yang tadi dikurangi saat order dibuat.
                $rows = DB::table('transactions_details')
                    ->where('TRANSACTIONS_ID', $orderId)
                    ->where('TRANSACTIONS_DETAILS_STATUS_DEL', 0)
                    ->get(['PRODUCTS_ID', 'TRANSACTIONS_QTY']);

                foreach ($rows as $r) {
                    DB::table('products')
                        ->where('PRODUCTS_ID', $r->PRODUCTS_ID)
                        ->where('PRODUCTS_READY_TYPE', '!=', 2) // 2 = Pre-Order, stoknya tidak pernah dikurangi
                        ->increment('PRODUCTS_STOCK', (int) $r->TRANSACTIONS_QTY);
                }

                DB::table('transactions')->where('TRANSACTIONS_ID', $orderId)->update([
                    'TRANSACTIONS_ORDER_STATUS' => 'cancelled',
                    'updated_at'                => now(),
                ]);

                return 'cancelled';
            }

            return 'pending';
        });
    }
}
