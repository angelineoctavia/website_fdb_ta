<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\CartDetail;
use App\Models\Product;
use App\Services\MidtransService;
use App\Services\OrderPaymentService;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        private ShippingService $shipping,
        private MidtransService $midtrans,
        private OrderPaymentService $payments,
    ) {}

    private function uid(): string
    {
        return (string) session('user_id');
    }

    /** Baris cart milik user yang dipilih. */
    private function lines(array $ids)
    {
        return CartDetail::whereIn('CARTS_DETAILS_ID', $ids)
            ->where('CARTS_DETAILS_STATUS_DEL', 0)
            ->whereHas('cart', fn($q) => $q->where('USERS_ID', $this->uid())->where('CARTS_STATUS_DEL', 0))
            ->with('product.images')
            ->get()
            ->filter(fn($l) => $l->product && (int) $l->product->PRODUCTS_STATUS_DEL === 0)
            ->values();
    }

    private function unitPrice($line): int
    {
        return (int) round((float) $line->product->PRODUCTS_PRICE);
    }

    /** Hitung subtotal, ongkir, total. */
    private function summarize($lines, Address $address): array
    {
        $qty      = (int) $lines->sum('CARTS_DETAILS_QTY');
        $subtotal = (int) $lines->sum(fn($l) => $this->unitPrice($l) * (int) $l->CARTS_DETAILS_QTY);
        $province = $address->field('province');
        $city     = $address->field('city');
        $shipping = $this->shipping->cost($province, $city, $qty);

        return [
            'qty'      => $qty,
            'subtotal' => $subtotal,
            'rate'     => $this->shipping->ratePerKg($province, $city),
            'kg'       => $this->shipping->billableKg($qty),
            'shipping' => $shipping,
            'total'    => $subtotal + $shipping,
        ];
    }

    /** Cart -> simpan item yang dicentang -> halaman checkout. */
    public function select(Request $request)
    {
        $data = $request->validate([
            'items'   => ['required', 'array', 'min:1'],
            'items.*' => ['integer'],
        ], ['items.required' => 'Choose minimal one product from the cart.']);

        session(['checkout_items' => array_values($data['items'])]);

        return redirect()->route('checkout.index');
    }

    public function index()
    {
        $ids   = session('checkout_items', []);
        $lines = $ids ? $this->lines($ids) : collect();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Please choose a product from the cart.');
        }

        $address = Address::forUser($this->uid());
        if (! $address || ! $address->isComplete()) {
            return redirect()->route('profile', ['next' => 'checkout'])
                ->with('error', 'Please complete your address before checkout.');
        }

        $user   = DB::table('users')->where('USERS_ID', $this->uid())->first();
        $brands = Product::brandNames($lines->pluck('product.USERS_ID'));

        // Produk ready stock yang stoknya kurang dari jumlah di keranjang
        $problems = $lines->filter(fn($l) => ! $l->product->is_preorder
            && (int) $l->CARTS_DETAILS_QTY > (int) $l->product->PRODUCTS_STOCK)->values();

        return view('customer.checkout', [
            'lines'    => $lines,
            'brands'   => $brands,
            'address'  => $address,
            'user'     => $user,
            'sum'      => $this->summarize($lines, $address),
            'problems' => $problems,
        ]);
    }

    /** Buat order (pending) + Snap token. Dipanggil lewat fetch() dari halaman checkout. */
    public function pay(Request $request)
    {
        $ids = session('checkout_items', []);

        try {
            $result = DB::transaction(function () use ($ids) {
                $lines = $ids ? $this->lines($ids) : collect();
                if ($lines->isEmpty()) {
                    throw new \RuntimeException('Cart empty, please choose a product.');
                }

                $address = Address::forUser($this->uid());
                if (! $address || ! $address->isComplete()) {
                    throw new \RuntimeException('Shipping address is not yet complete.');
                }

                // Kunci produk, cek stok, kurangi stok (hanya ready stock)
                foreach ($lines as $line) {
                    $p = Product::where('PRODUCTS_ID', $line->PRODUCTS_ID)->lockForUpdate()->first();
                    $line->setRelation('product', $p->setRelation('images', $line->product->images));

                    if (! $p->is_preorder) {
                        if ((int) $line->CARTS_DETAILS_QTY > (int) $p->PRODUCTS_STOCK) {
                            throw new \RuntimeException("Stock not enough for {$p->PRODUCTS_NAME}. Only {$p->PRODUCTS_STOCK} left.");
                        }
                        $p->decrement('PRODUCTS_STOCK', (int) $line->CARTS_DETAILS_QTY);
                    }
                }

                $sum = $this->summarize($lines, $address);

                do {
                    $orderId = 'TRX' . now()->format('ymd') . strtoupper(Str::random(5)); // 14 karakter
                } while (DB::table('transactions')->where('TRANSACTIONS_ID', $orderId)->exists());

                $hasPreorder = $lines->contains(fn($l) => $l->product->is_preorder);

                DB::table('transactions')->insert([
                    'TRANSACTIONS_ID'               => $orderId,
                    'USERS_ID'                      => $this->uid(),
                    'TRANSACTIONS_TOTAL_AMOUNT'     => $sum['total'],
                    'TRANSACTIONS_SHIPPING_COST'    => $sum['shipping'],
                    'TRANSACTIONS_ORDER_STATUS'     => 'pending',
                    'TRANSACTIONS_FULFILLMENT_TYPE' => $hasPreorder ? 'preorder' : 'ready',
                    'ADDRESSES_ID'                  => $address->ADDRESSES_ID,
                    'TRANSACTIONS_SHIPPING_ADDRESS' => $address->toText(),
                    'TRANSACTIONS_DATE'             => now()->toDateString(),
                    'TRANSACTIONS_STATUS_DEL'       => 0,
                    'created_at'                    => now(),
                    'updated_at'                    => now(),
                ]);

                $details = [];
                $items   = [];
                foreach ($lines->values() as $i => $line) {
                    $price = $this->unitPrice($line);
                    $qty   = (int) $line->CARTS_DETAILS_QTY;

                    $details[] = [
                        'TRANSACTIONS_DETAIL_ID'                  => $orderId . strtoupper(base_convert((string) $i, 10, 36)), // 15 karakter
                        'TRANSACTIONS_ID'                         => $orderId,
                        'PRODUCTS_ID'                             => $line->PRODUCTS_ID,
                        'TRANSACTIONS_QTY'                        => $qty,
                        'TRANSACTIONS_SUBTOTAL'                   => $price * $qty,
                        'TRANSACTIONS_DETAILS_CUSTOM_SIZE_NOTES'  => $line->CARTS_DETAILS_CUSTOM_SIZE_NOTES,
                        'TRANSACTIONS_DETAILS_SELECTED_SIZE'      => $line->CARTS_DETAILS_SELECTED_SIZE,
                        'TRANSACTIONS_DETAILS_STATUS_DEL'         => 0,
                        'created_at'                              => now(),
                        'updated_at'                              => now(),
                    ];

                    $items[] = [
                        'id'       => $line->PRODUCTS_ID,
                        'price'    => $price,
                        'quantity' => $qty,
                        'name'     => Str::limit($line->product->PRODUCTS_NAME . ' (' . ($line->CARTS_DETAILS_SELECTED_SIZE ?: '-') . ')', 50, ''),
                    ];
                }
                DB::table('transactions_details')->insert($details);

                $items[] = ['id' => 'SHIPPING', 'price' => $sum['shipping'], 'quantity' => 1, 'name' => 'Shipping Cost'];

                // Barang di keranjang dianggap sudah dipesan
                CartDetail::whereIn('CARTS_DETAILS_ID', $lines->pluck('CARTS_DETAILS_ID'))
                    ->update(['CARTS_DETAILS_STATUS_DEL' => 1]);

                $user = DB::table('users')->where('USERS_ID', $this->uid())->first();

                $token = $this->midtrans->snapToken([
                    'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $sum['total']],
                    'item_details'        => $items,
                    'customer_details'    => [
                        'first_name'       => $user->USERS_NAME,
                        'phone'            => $user->USERS_PHONE_NUMBER,
                        'shipping_address' => [
                            'first_name'   => $user->USERS_NAME,
                            'phone'        => $user->USERS_PHONE_NUMBER,
                            'address'      => Str::limit((string) $address->field('street'), 200, ''),
                            'city'         => $address->field('city'),
                            'postal_code'  => $address->field('postal'),
                            'country_code' => 'IDN',
                        ],
                    ],
                    'expiry' => ['unit' => 'hours', 'duration' => 24],
                ]);

                DB::table('transactions')->where('TRANSACTIONS_ID', $orderId)->update(['TRANSACTIONS_SNAP_TOKEN' => $token]);

                return ['token' => $token, 'order_id' => $orderId];
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to make payment, please try again.',
            ], 500);
        }

        session()->forget('checkout_items');

        return response()->json($result);
    }

    /** Lanjutkan bayar order pending (misal popup Snap sempat ditutup). */
    public function resume(string $id)
    {
        $order = DB::table('transactions')
            ->where('TRANSACTIONS_ID', $id)->where('USERS_ID', $this->uid())->first();
        abort_if(! $order, 404);

        if ($order->TRANSACTIONS_ORDER_STATUS !== 'pending' || ! $order->TRANSACTIONS_SNAP_TOKEN) {
            return redirect()->route('home')->with('info', 'This order is no longer awaiting payment.');
        }

        return view('customer.pay', ['order' => $order]);
    }

    /** Customer kembali dari popup Snap: cek status ke Midtrans, lalu balik ke Home. */
    public function finish(string $id)
    {
        $order = DB::table('transactions')
            ->where('TRANSACTIONS_ID', $id)->where('USERS_ID', $this->uid())->first();
        abort_if(! $order, 404);

        try {
            $s      = $this->midtrans->status($id);
            $status = $this->payments->apply(
                $id,
                (string) ($s->transaction_status ?? 'pending'),
                $s->fraud_status ?? null,
                $s->payment_type ?? null,
            );
        } catch (\Throwable $e) {
            report($e);
            $status = $order->TRANSACTIONS_ORDER_STATUS; // belum ada pembayaran tercatat di Midtrans
        }

        return match ($status) {
            'paid'      => redirect()->route('home')->with('success', 'Payment successful! Your order will be processed by the seller shortly.'),
            'cancelled' => redirect()->route('home')->with('error', 'Payment failed or expired. Stock has been returned.'),
            default     => redirect()->route('home')->with('info', 'The order has been created and is awaiting payment. The status will be updated automatically once the payment is received.'),
        };
    }
}
