<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use App\Services\CartService;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private CartService $carts) {}

    private function uid(): string
    {
        return (string) session('user_id');
    }

    public function index()
    {
        $items = Wishlist::where('USERS_ID', $this->uid())
            ->where('WISHLISTS_STATUS_DEL', 0)
            ->with('product.images')
            ->latest('created_at')
            ->get()
            ->filter(fn($w) => $w->product && (int) $w->product->PRODUCTS_STATUS_DEL === 0)
            ->values();

        $brands = Product::brandNames($items->pluck('product.USERS_ID'));

        return view('customer.wishlist', compact('items', 'brands'));
    }

    /** Tambah ke wishlist kalau belum ada, hapus kalau sudah ada. */
    public function toggle(Request $request)
    {
        $data = $request->validate(['product_id' => ['required', 'string', 'max:8']]);

        $product = Product::active()->findOrFail($data['product_id']);

        $row = Wishlist::where('USERS_ID', $this->uid())
            ->where('PRODUCTS_ID', $product->PRODUCTS_ID)
            ->first();

        if ($row && (int) $row->WISHLISTS_STATUS_DEL === 0) {
            $row->update(['WISHLISTS_STATUS_DEL' => 1]);
            return back()->with('success', 'Dihapus dari wishlist.');
        }

        if ($row) {
            $row->update(['WISHLISTS_STATUS_DEL' => 0]);
        } else {
            Wishlist::create([
                'USERS_ID'             => $this->uid(),
                'PRODUCTS_ID'          => $product->PRODUCTS_ID,
                'WISHLISTS_STATUS_DEL' => 0,
            ]);
        }

        return back()->with('success', "{$product->PRODUCTS_NAME} ditambahkan ke wishlist.");
    }

    /** Wishlist -> Cart (item tetap ada di wishlist sampai di-delete). */
    public function toCart(Request $request, $id)
    {
        $row = Wishlist::where('WISHLISTS_ID', $id)
            ->where('USERS_ID', $this->uid())
            ->where('WISHLISTS_STATUS_DEL', 0)
            ->firstOrFail();

        $product = Product::active()->findOrFail($row->PRODUCTS_ID);

        [$ok, $msg] = $this->carts->add($this->uid(), $product, $request->input('size'), 1);

        return back()->with($ok ? 'success' : 'error', $msg);
    }

    public function destroy($id)
    {
        Wishlist::where('WISHLISTS_ID', $id)
            ->where('USERS_ID', $this->uid())
            ->update(['WISHLISTS_STATUS_DEL' => 1]);

        return back()->with('success', 'Dihapus dari wishlist.');
    }
}