<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;  
use App\Models\Category;
use App\Models\User;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'user']);

        if ($request->filled('brand')) {
            $query->where('USERS_ID', $request->brand);
        }
        if ($request->filled('gender')) {
            $query->where('product_gender', $request->gender);
        }
        if ($request->filled('type')) {
            $query->whereIn('CATEGORIES_ID', $request->type);
        }
        if ($request->filled('price')) {
            $priceRange = $request->price;
            if ($priceRange == 'lt_200k') {
                $query->where('product_price', '<', 200000);
            } elseif ($priceRange == '200k_500k') {
                $query->whereBetween('product_price', [200000, 500000]);
            } elseif ($priceRange == '500k_1m') {
                $query->whereBetween('product_price', [500000, 1000000]);
            } elseif ($priceRange == 'gt_1m') {
                $query->where('product_price', '>', 1000000);
            }
        }

        $products = $query->get();
        $categories = Category::all(); // Menggunakan model Category
        $brands = User::where('USERS_ROLE', 'S')->get();

        return view('shop', compact('products', 'categories', 'brands'));
    }

    public function show($id)
    {
        $product = Product::with(['category', 'user'])->find($id);

        return view('shop_detail', compact('product'));
    }
}