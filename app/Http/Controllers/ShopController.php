<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    /** [label, min, max] (null = tanpa batas) */
    private const PRICE_RANGES = [
        'lt200'    => ['< Rp 200.000', null, 200000],
        '200_500'  => ['Rp 200.000 - Rp 500.000', 200000, 500000],
        '500_1000' => ['Rp 500.000 - Rp 1.000.000', 500000, 1000000],
        'gt1000'   => ['> Rp 1.000.000', 1000000, null],
    ];

    /** Dropdown "Category" di sidebar. Dicocokkan ke kolom products.PRODUCTS_GENDER (tidak peka huruf besar/kecil). */
    private const CATEGORIES = [
        'women'  => 'Women',
        'men'    => 'Men',
        'kids'   => 'Kids',
        'unisex' => 'Unisex',
    ];

    public function index(Request $request)
    {
        $q          = trim((string) $request->query('q', ''));
        $brand      = $request->query('brand');
        $category   = strtolower((string) $request->query('category', ''));
        $category   = array_key_exists($category, self::CATEGORIES) ? $category : '';
        $types      = (array) $request->query('type', []);
        $prices     = array_intersect((array) $request->query('price', []), array_keys(self::PRICE_RANGES));

        $query = Product::active()->with('images');

        // Search: nama produk, deskripsi, material, atau nama brand
        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('PRODUCTS_NAME', 'like', $like)
                    ->orWhere('PRODUCTS_DESCRIPTION', 'like', $like)
                    ->orWhere('PRODUCTS_MATERIAL', 'like', $like)
                    ->orWhereIn('USERS_ID', function ($sub) use ($like) {
                        $sub->select('USERS_ID')->from('users')
                            ->where(Product::SELLER_NAME_COLUMN, 'like', $like);
                    });
            });
        }

        if ($brand) {
            $query->where('USERS_ID', $brand);
        }

        // Category (Women / Men / Kids / Unisex)
        if ($category !== '') {
            $query->whereRaw('LOWER(PRODUCTS_GENDER) = ?', [$category]);
        }

        // Type (Tops, Dress, dll) = tabel categories
        if ($types) {
            $query->whereIn('CATEGORIES_ID', $types);
        }

        if ($prices) {
            $query->where(function ($w) use ($prices) {
                foreach ($prices as $key) {
                    [, $min, $max] = self::PRICE_RANGES[$key];
                    $w->orWhere(function ($qq) use ($min, $max) {
                        if ($min !== null) {
                            $qq->where('PRODUCTS_PRICE', '>=', $min);
                        }
                        if ($max !== null) {
                            $qq->where('PRODUCTS_PRICE', '<', $max);
                        }
                    });
                }
            });
        }

        $products = $query->latest('created_at')->paginate(12)->withQueryString();

        $brands = DB::table('users')
            ->whereIn('USERS_ID', Product::active()->select('USERS_ID'))
            ->orderBy(Product::SELLER_NAME_COLUMN)
            ->get(['USERS_ID', DB::raw(Product::SELLER_NAME_COLUMN . ' as name')]);

        $categoryList = DB::table('categories')
            ->orderBy(Product::CATEGORY_NAME_COLUMN)
            ->get(['CATEGORIES_ID', DB::raw(Product::CATEGORY_NAME_COLUMN . ' as name')]);

        // Header profil seller (hanya kalau sedang difilter per brand)
        $seller = $brand ? $this->sellerProfile((string) $brand) : null;

        return view('customer.shop', [
            'products'        => $products,
            'brands'          => $brands,
            'categoryList'    => $categoryList,        // checkbox "Type"
            'categoryOptions' => self::CATEGORIES,     // dropdown "Category"
            'priceOptions'    => array_map(fn($r) => $r[0], self::PRICE_RANGES),
            'q'               => $q,
            'seller'          => $seller,
            'selected'        => [
                'brand'    => $brand,
                'category' => $category,
                'type'     => $types,
                'price'    => $prices,
            ],
        ]);
    }

    public function show(string $id)
    {
        $product = Product::active()->with('images')->findOrFail($id);

        $seller      = DB::table('users')->where('USERS_ID', $product->USERS_ID)->first();
        $sellerName  = $seller->{Product::SELLER_NAME_COLUMN} ?? 'Designer';
        $sellerPhone = $seller->{Product::SELLER_PHONE_COLUMN} ?? null;

        $category     = DB::table('categories')->where('CATEGORIES_ID', $product->CATEGORIES_ID)->first();
        $categoryName = $category->{Product::CATEGORY_NAME_COLUMN} ?? null;

        $waLink = null;
        if ($sellerPhone) {
            $digits = preg_replace('/\D+/', '', $sellerPhone);
            if (str_starts_with($digits, '0')) {
                $digits = '62' . substr($digits, 1);
            }
            $waLink = "https://wa.me/{$digits}?text=" . rawurlencode("Halo, saya tertarik dengan produk {$product->PRODUCTS_NAME}");
        }

        $inWishlist = session()->has('user_id')
            && Wishlist::where('USERS_ID', session('user_id'))
            ->where('PRODUCTS_ID', $product->PRODUCTS_ID)
            ->where('WISHLISTS_STATUS_DEL', 0)->exists();

        return view('customer.shop_detail', [
            'product'          => $product,
            'sellerName'       => $sellerName,
            'sellerId'         => $product->USERS_ID,
            'sellerPicture'    => $this->imageUrl($seller->USERS_PROFILE_PICTURE ?? null),
            'sellerInstagram'  => $seller->USERS_INSTAGRAM ?? null,
            'categoryName'     => $categoryName,
            'waLink'           => $waLink,
            'inWishlist'       => $inWishlist,
        ]);
    }

    /** Profil seller untuk header halaman Shop (null kalau seller tidak ketemu). */
    private function sellerProfile(string $id): ?array
    {
        $u = DB::table('users')->where('USERS_ID', $id)->first();
        if (! $u) {
            return null;
        }

        $name = $u->{Product::SELLER_NAME_COLUMN} ?? 'Designer';
        $ig   = $u->USERS_INSTAGRAM ?? null;

        return [
            'id'      => $id,
            'name'    => $name,
            'handle'  => '@' . ($ig ? ltrim($ig, '@') : Str::slug($name, '')),
            'bio'     => $u->USERS_BIO ?? null,
            'picture' => $this->imageUrl($u->USERS_PROFILE_PICTURE ?? null),
            'count'   => Product::active()->where('USERS_ID', $id)->count(),
        ];
    }

    /** Path gambar di DB -> URL siap pakai. */
    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = ltrim($path, '/');
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return asset('storage/' . preg_replace('#^storage/#', '', $path));
    }
}