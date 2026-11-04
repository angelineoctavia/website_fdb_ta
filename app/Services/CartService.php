<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Product;
use Illuminate\Support\Str;

class CartService
{
    /** Cart aktif milik user; dibuat otomatis kalau belum ada. */
    public function activeCart(string $userId): Cart
    {
        $cart = Cart::where('USERS_ID', $userId)->where('CARTS_STATUS_DEL', 0)->first();
        if ($cart) {
            return $cart;
        }

        do {
            $id = 'CR' . strtoupper(Str::random(8)); // 10 karakter, sesuai varchar(10)
        } while (Cart::where('CARTS_ID', $id)->exists());

        return Cart::create([
            'CARTS_ID'         => $id,
            'USERS_ID'         => $userId,
            'CARTS_STATUS_DEL' => 0,
        ]);
    }

    /** @return array{0: bool, 1: string} [berhasil, pesan] */
    public function add(string $userId, Product $product, ?string $size, int $qty = 1): array
    {
        if (! $product->is_preorder && $product->PRODUCTS_STOCK < 1) {
            return [false, 'Stok produk ini habis.'];
        }

        $sizes = $product->sizes;
        $size  = ($size && in_array($size, $sizes, true)) ? $size : $sizes[0];

        $cart = $this->activeCart($userId);

        $line = CartDetail::where('CARTS_ID', $cart->CARTS_ID)
            ->where('PRODUCTS_ID', $product->PRODUCTS_ID)
            ->where('CARTS_DETAILS_SELECTED_SIZE', $size)
            ->where('CARTS_DETAILS_STATUS_DEL', 0)
            ->first();

        $newQty = ($line ? (int) $line->CARTS_DETAILS_QTY : 0) + $qty;

        if (! $product->is_preorder && $newQty > $product->PRODUCTS_STOCK) {
            return [false, "Stok tidak cukup. Sisa {$product->PRODUCTS_STOCK} pcs."];
        }

        if ($line) {
            $line->update([
                'CARTS_DETAILS_QTY'   => $newQty,
                'CARTS_DETAILS_PRICE' => $product->PRODUCTS_PRICE,
            ]);
        } else {
            CartDetail::create([
                'CARTS_ID'                    => $cart->CARTS_ID,
                'PRODUCTS_ID'                 => $product->PRODUCTS_ID,
                'CARTS_DETAILS_PRICE'         => $product->PRODUCTS_PRICE,
                'CARTS_DETAILS_QTY'           => $qty,
                'CARTS_DETAILS_SELECTED_SIZE' => $size,
                'CARTS_DETAILS_STATUS_DEL'    => 0,
            ]);
        }

        return [true, "{$product->PRODUCTS_NAME} (size {$size}) masuk keranjang."];
    }
}