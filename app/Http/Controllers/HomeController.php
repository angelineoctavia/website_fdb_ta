<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    // Status yang dihitung sebagai "sudah terjual" (sama dengan AdminController::PAID)
    private const PAID = ['paid', 'packing', 'shipping', 'completed'];

    public function index()
    {
        // 3 produk aktif terbaru
        $newArrivals = Product::active()
            ->with('images')
            ->latest('created_at')
            ->take(3)
            ->get();

        // 3 produk terlaris bulan ini
        $topIds = DB::table('transactions_details as d')
            ->join('transactions as t', 't.TRANSACTIONS_ID', '=', 'd.TRANSACTIONS_ID')
            ->join('products as p', 'p.PRODUCTS_ID', '=', 'd.PRODUCTS_ID')
            ->where('t.TRANSACTIONS_STATUS_DEL', 0)
            ->where('d.TRANSACTIONS_DETAILS_STATUS_DEL', 0)
            ->where('p.PRODUCTS_STATUS_DEL', 0)
            ->whereIn('t.TRANSACTIONS_ORDER_STATUS', self::PAID)
            ->whereBetween('t.TRANSACTIONS_DATE', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->select('d.PRODUCTS_ID')
            ->groupBy('d.PRODUCTS_ID')
            ->orderByRaw('SUM(d.TRANSACTIONS_QTY) DESC')
            ->limit(3)
            ->pluck('PRODUCTS_ID')
            ->all();

        $bestSellers = Product::active()
            ->with('images')
            ->whereIn('PRODUCTS_ID', $topIds)
            ->get()
            ->sortBy(fn($p) => array_search($p->PRODUCTS_ID, $topIds))
            ->values();

        return view('customer.home', compact('newArrivals', 'bestSellers'));
    }
}