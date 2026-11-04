<?php

namespace App\Http\Controllers;

use App\Models\CartDetail;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $carts) {}

    private function uid(): string
    {
        return (string) session('user_id');
    }

    /** Baris cart milik user yang sedang login (anti akses baris orang lain). */
    private function ownedLine($id): CartDetail
    {
        return CartDetail::where('CARTS_DETAILS_ID', $id)
            ->where('CARTS_DETAILS_STATUS_DEL', 0)
            ->whereHas('cart', fn($q) => $q->where('USERS_ID', $this->uid())->where('CARTS_STATUS_DEL', 0))
            ->with('product')
            ->firstOrFail();
    }

    public function index()
    {
        $cart = $this->carts->activeCart($this->uid());

        $lines = CartDetail::where('CARTS_ID', $cart->CARTS_ID)
            ->where('CARTS_DETAILS_STATUS_DEL', 0)
            ->with('product.images')
            ->orderBy('CARTS_DETAILS_ID')
            ->get()
            ->filter(fn($l) => $l->product && (int) $l->product->PRODUCTS_STATUS_DEL === 0)
            ->values();

        $brands = Product::brandNames($lines->pluck('product.USERS_ID'));

        return view('customer.cart', compact('lines', 'brands'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'string', 'max:8'],
            'size'       => ['nullable', 'string', 'max:6'],
            'qty'        => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::active()->findOrFail($data['product_id']);

        [$ok, $msg] = $this->carts->add($this->uid(), $product, $data['size'] ?? null, (int) ($data['qty'] ?? 1));

        return back()->with($ok ? 'success' : 'error', $msg);
    }

    public function update(Request $request, $id)
    {
        $line    = $this->ownedLine($id);
        $product = $line->product;
        $qty     = (int) $line->CARTS_DETAILS_QTY;

        $qty += $request->input('action') === 'inc' ? 1 : -1;

        if ($qty < 1) {
            $line->update(['CARTS_DETAILS_STATUS_DEL' => 1]);
            return back()->with('success', 'Produk dihapus dari keranjang.');
        }

        if (! $product->is_preorder && $qty > $product->PRODUCTS_STOCK) {
            return back()->with('error', "Stok tidak cukup. Sisa {$product->PRODUCTS_STOCK} pcs.");
        }

        $line->update(['CARTS_DETAILS_QTY' => $qty]);

        return back();
    }

    public function destroy($id)
    {
        $this->ownedLine($id)->update(['CARTS_DETAILS_STATUS_DEL' => 1]);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}