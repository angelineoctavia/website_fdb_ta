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

        // FDB Students' Brands: seller aktif yang punya minimal 1 produk aktif.
        // Yang sudah punya logo (foto profil) tampil duluan.
        $brands = DB::table('users as u')
            ->where('u.USERS_ROLE', 'S')
            ->where('u.USERS_STATUS_DEL', 0)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('products as p')
                    ->whereColumn('p.USERS_ID', 'u.USERS_ID')
                    ->where('p.PRODUCTS_STATUS_DEL', 0);
            })
            ->orderByRaw('u.USERS_PROFILE_PICTURE IS NULL')
            ->orderBy('u.USERS_NAME')
            ->limit(12)
            ->get(['u.USERS_ID', 'u.USERS_NAME', 'u.USERS_PROFILE_PICTURE'])
            ->map(function ($u) {
                $u->logo = $u->USERS_PROFILE_PICTURE ? asset('storage/' . $u->USERS_PROFILE_PICTURE) : null;
                return $u;
            });

        return view('customer.home', compact('newArrivals', 'bestSellers', 'brands'));
    }
}