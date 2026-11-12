<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Support\Phone;

class SellerController extends Controller
{
    // Status order di TRANSACTIONS_ORDER_STATUS:
    // pending (nunggu bayar) -> paid (otomatis dari webhook Midtrans)
    // -> packing -> shipping -> completed (3 terakhir manual oleh seller)
    const PAID = ['paid', 'packing', 'shipping', 'completed'];
    const NEXT = ['paid' => ['packing'], 'packing' => ['shipping'], 'shipping' => ['completed']];

    // Ukuran foto produk (rasio 3:4 portrait). Ubah di sini kalau mau rasio lain, mis. 1200 x 900 untuk 4:3.
    const IMG_W = 900;
    const IMG_H = 1200;

    /** ID seller yang login. Kalau login kamu pakai session manual, ganti di sini aja. */
    private function sid(): string
    {
        return (string) session('user_id');
    }

    /** Semua baris detail transaksi yang produknya milik seller ini. */
    private function lines()
    {
        return DB::table('transactions_details as d')
            ->join('transactions as t', 't.TRANSACTIONS_ID', '=', 'd.TRANSACTIONS_ID')
            ->join('products as p', 'p.PRODUCTS_ID', '=', 'd.PRODUCTS_ID')
            ->join('users as b', 'b.USERS_ID', '=', 't.USERS_ID')
            ->where('p.USERS_ID', $this->sid())
            ->where('t.TRANSACTIONS_STATUS_DEL', 0)
            ->where('d.TRANSACTIONS_DETAILS_STATUS_DEL', 0);
    }

    private function paidLines()
    {
        return $this->lines()->whereIn('t.TRANSACTIONS_ORDER_STATUS', self::PAID);
    }

    /* ===================== DASHBOARD ===================== */
    public function dashboard()
    {
        $stats = [
            'revenue'  => $this->paidLines()->sum('d.TRANSACTIONS_SUBTOTAL'),
            'monthly'  => $this->paidLines()
                ->whereMonth('t.TRANSACTIONS_DATE', now()->month)
                ->whereYear('t.TRANSACTIONS_DATE', now()->year)
                ->sum('d.TRANSACTIONS_SUBTOTAL'),
            'orders'   => $this->paidLines()->distinct()->count('t.TRANSACTIONS_ID'),
            'products' => DB::table('products')->where('USERS_ID', $this->sid())
                ->where('PRODUCTS_STATUS_DEL', 0)->where('PRODUCTS_STOCK', '>', 0)->count(),
        ];

        $trend = $this->paidLines()
            ->where('t.TRANSACTIONS_DATE', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(t.TRANSACTIONS_DATE,'%Y-%m') as ym, SUM(d.TRANSACTIONS_SUBTOTAL) as revenue, SUM(d.TRANSACTIONS_QTY) as qty")
            ->groupBy('ym')->orderBy('ym')->get();

        $byCategory = $this->paidLines()
            ->join('categories as c', 'c.CATEGORIES_ID', '=', 'p.CATEGORIES_ID')
            ->selectRaw('c.CATEGORIES_NAME as name, SUM(d.TRANSACTIONS_QTY) as qty')
            ->groupBy('c.CATEGORIES_NAME')->get();

        $recent = $this->lines()
            ->select('t.TRANSACTIONS_ID', 't.TRANSACTIONS_DATE', 't.TRANSACTIONS_ORDER_STATUS', 'b.USERS_NAME as customer')
            ->selectRaw('SUM(d.TRANSACTIONS_SUBTOTAL) as total')
            ->groupBy('t.TRANSACTIONS_ID', 't.TRANSACTIONS_DATE', 't.TRANSACTIONS_ORDER_STATUS', 'b.USERS_NAME')
            ->orderByDesc('t.TRANSACTIONS_DATE')->orderByDesc('t.TRANSACTIONS_ID')->limit(5)->get();

        $top = $this->paidLines()
            ->select('p.PRODUCTS_NAME as name')
            ->selectRaw('SUM(d.TRANSACTIONS_QTY) as qty, SUM(d.TRANSACTIONS_SUBTOTAL) as total')
            ->groupBy('p.PRODUCTS_ID', 'p.PRODUCTS_NAME')
            ->orderByDesc('qty')->limit(5)->get();

        return view('seller.dashboard', compact('stats', 'trend', 'byCategory', 'recent', 'top'));
    }

    /* ===================== UPLOAD ===================== */
    public function uploadForm()
    {
        $categories = DB::table('categories')->where('CATEGORIES_STATUS_DEL', 0)->orderBy('CATEGORIES_NAME')->get();
        $imgW = self::IMG_W;
        $imgH = self::IMG_H;
        return view('seller.upload', compact('categories', 'imgW', 'imgH'));
    }

    public function uploadStore(Request $r)
    {
        $d = $this->validateProduct($r, false);

        DB::transaction(function () use ($r, $d) {
            $last = DB::table('products')->lockForUpdate()->max('PRODUCTS_ID');
            $id   = 'PRD' . str_pad($last ? ((int) substr($last, 3)) + 1 : 1, 5, '0', STR_PAD_LEFT);
            $now  = now();

            DB::table('products')->insert([
                'PRODUCTS_ID'        => $id,
                'USERS_ID'           => $this->sid(),
                'PRODUCTS_STATUS_DEL' => 0,
                'created_at'         => $now,
                'updated_at'         => $now,
            ] + $this->productPayload($d));

            foreach ($r->file('images') as $i => $file) {
                DB::table('product_images')->insert([
                    'PRODUCTS_ID'               => $id,
                    'PRODUCT_IMAGES_URL'        => $file->store('products', 'public'),
                    'PRODUCT_IMAGES_IS_PRIMARY' => $i === 0 ? 1 : 0,
                    'PRODUCT_IMAGES_STATUS_DEL' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        return redirect()->route('seller.products')->with('success', 'Product published.');
    }

    /**
     * Validasi form produk (dipakai Upload & Edit).
     * Ready Stock (1): stok wajib, durasi diabaikan.
     * Pre-Order  (2): durasi hari wajib, stok dikunci (disimpan 0).
     */
    private function validateProduct(Request $r, bool $isEdit): array
    {
        $rules = [
            'name'            => 'required|max:100',
            'category'        => 'required|exists:categories,CATEGORIES_ID',
            'gender'          => 'required|in:Women,Men,Unisex,Kids',
            'price'           => 'required|numeric|min:0',
            'ready_type'      => 'required|in:1,2',
            'stock'           => 'exclude_if:ready_type,2|required|integer|min:0',
            'preorder_days'   => 'exclude_unless:ready_type,2|required|integer|min:1',
            'description'     => 'nullable|string',
            'material'        => 'nullable|max:100',
            'size_chart_json' => 'nullable|string',
            'images.*'        => ['image', 'max:4096', $this->ratioRule()],
        ];

        if ($isEdit) {
            $rules += [
                'images'          => 'nullable|array|max:5',
                'remove_images'   => 'nullable|array',
                'remove_images.*' => 'integer',
                'primary_image'   => 'nullable|integer',
            ];
        } else {
            $rules['images'] = 'required|array|min:1|max:5';
        }

        return $r->validate($rules);
    }

    /** Kolom tabel products dari data form yang sudah tervalidasi. */
    private function productPayload(array $d): array
    {
        $pre = (int) $d['ready_type'] === 2;

        return [
            'CATEGORIES_ID'              => $d['category'],
            'PRODUCTS_NAME'              => $d['name'],
            'PRODUCTS_PRICE'             => $d['price'],
            'PRODUCTS_STOCK'             => $pre ? 0 : (int) $d['stock'],
            'PRODUCTS_DESCRIPTION'       => $d['description'] ?? null,
            'PRODUCTS_MATERIAL'          => $d['material'] ?? null,
            'PRODUCTS_GENDER'            => $d['gender'],
            'PRODUCTS_SIZE_CHART'        => $this->cleanSizeChart($d['size_chart_json'] ?? null),
            'PRODUCTS_READY_TYPE'        => $d['ready_type'],
            'PRODUCTS_PREORDER_DURATION' => $pre ? (int) $d['preorder_days'] : null,
        ];
    }

    /* ===================== ORDERS ===================== */
    public function orders(Request $r)
    {
        $q = trim((string) $r->query('q'));
        $status = $r->query('status');

        $orders = $this->lines()
            ->select('t.TRANSACTIONS_ID', 't.TRANSACTIONS_DATE', 't.TRANSACTIONS_ORDER_STATUS', 'b.USERS_NAME as customer')
            ->selectRaw('GROUP_CONCAT(p.PRODUCTS_NAME SEPARATOR ", ") as products, SUM(d.TRANSACTIONS_QTY) as qty, SUM(d.TRANSACTIONS_SUBTOTAL) as total')
            ->when($status, fn($x) => $x->where('t.TRANSACTIONS_ORDER_STATUS', $status))
            ->when($q, fn($x) => $x->where(function ($w) use ($q) {
                $w->where('t.TRANSACTIONS_ID', 'like', "%$q%")
                    ->orWhere('b.USERS_NAME', 'like', "%$q%")
                    ->orWhere('p.PRODUCTS_NAME', 'like', "%$q%");
            }))
            ->groupBy('t.TRANSACTIONS_ID', 't.TRANSACTIONS_DATE', 't.TRANSACTIONS_ORDER_STATUS', 'b.USERS_NAME')
            ->orderByDesc('t.TRANSACTIONS_DATE')->orderByDesc('t.TRANSACTIONS_ID')
            ->paginate(10)->withQueryString();

        return view('seller.orders', compact('orders', 'q', 'status'));
    }

    public function orderShow(string $id)
    {
        $items = $this->lines()->where('t.TRANSACTIONS_ID', $id)
            ->select(
                'p.PRODUCTS_NAME',
                'd.TRANSACTIONS_QTY',
                'd.TRANSACTIONS_SUBTOTAL',
                'd.TRANSACTIONS_DETAILS_SELECTED_SIZE',
                'd.TRANSACTIONS_DETAILS_CUSTOM_SIZE_NOTES'
            )
            ->get();
        abort_if($items->isEmpty(), 404);

        $order = DB::table('transactions as t')
            ->join('users as b', 'b.USERS_ID', '=', 't.USERS_ID')
            ->where('t.TRANSACTIONS_ID', $id)
            ->select('t.*', 'b.USERS_NAME as customer', 'b.USERS_PHONE_NUMBER as customer_phone')
            ->first();

        $nextOptions = self::NEXT[$order->TRANSACTIONS_ORDER_STATUS] ?? [];
        $sellerTotal = $items->sum('TRANSACTIONS_SUBTOTAL');

        return view('seller.order_detail', compact('order', 'items', 'nextOptions', 'sellerTotal'));
    }

    public function orderUpdateStatus(Request $r, string $id)
    {
        abort_unless($this->lines()->where('t.TRANSACTIONS_ID', $id)->exists(), 404);

        $current = DB::table('transactions')->where('TRANSACTIONS_ID', $id)->value('TRANSACTIONS_ORDER_STATUS');
        $allowed = self::NEXT[$current] ?? [];

        $d = $r->validate([
            'status'          => ['required', Rule::in($allowed)],
            'courier'         => 'required_if:status,shipping|nullable|max:20',
            'tracking_number' => 'required_if:status,shipping|nullable|max:50',
        ]);

        $upd = ['TRANSACTIONS_ORDER_STATUS' => $d['status'], 'updated_at' => now()];
        if ($d['status'] === 'shipping') {
            $upd['TRANSACTIONS_COURIER'] = $d['courier'];
            $upd['TRANSACTIONS_TRACKING_NUMBER'] = $d['tracking_number'];
        }
        DB::table('transactions')->where('TRANSACTIONS_ID', $id)->update($upd);

        return back()->with('success', 'Order status updated.');
    }

    /** Rapikan size chart dari form; kembalikan null kalau tidak ada baris yang terisi. */
    private function cleanSizeChart(?string $json): ?string
    {
        $c = json_decode($json ?? '', true);
        if (!is_array($c)) return null;

        $cols = array_values(array_map(fn($x) => mb_substr(trim((string) $x), 0, 30), $c['columns'] ?? []));
        $rows = [];
        foreach (($c['rows'] ?? []) as $r) {
            $size = trim((string) ($r['size'] ?? ''));
            $vals = array_map(fn($v) => trim((string) $v), array_slice(array_values($r['values'] ?? []), 0, count($cols)));
            $vals = array_pad($vals, count($cols), '');
            if ($size === '' || !array_filter($vals, fn($v) => $v !== '')) continue;
            $rows[] = ['size' => mb_substr($size, 0, 10), 'values' => $vals];
        }
        return $rows ? json_encode(['columns' => $cols, 'rows' => $rows]) : null;
    }

    /** Semua foto produk harus berrasio sama (3:4) supaya tampil seragam di sisi customer. */
    private function ratioRule(): \Closure
    {
        return function ($attribute, $file, $fail) {
            $size = @getimagesize($file->getPathname());
            if (!$size || abs($size[0] / $size[1] - self::IMG_W / self::IMG_H) > 0.02) {
                $fail('The photo ratio must be 3:4. Use Crop, Stretch, or Fit when choosing your photo.');
            }
        };
    }

    /** Produk milik seller ini (aktif ATAU nonaktif). */
    private function ownedProduct(string $id)
    {
        $p = DB::table('products')->where('PRODUCTS_ID', $id)->where('USERS_ID', $this->sid())->first();
        abort_if(!$p, 404);
        return $p;
    }

    /* ===================== MY PRODUCTS ===================== */
    public function products(Request $r)
    {
        $q = trim((string) $r->query('q'));
        $status = in_array($r->query('status'), ['active', 'inactive'], true) ? $r->query('status') : 'all';

        $products = DB::table('products as p')
            ->join('categories as c', 'c.CATEGORIES_ID', '=', 'p.CATEGORIES_ID')
            ->where('p.USERS_ID', $this->sid())
            ->when($status === 'active', fn($x) => $x->where('p.PRODUCTS_STATUS_DEL', 0))
            ->when($status === 'inactive', fn($x) => $x->where('p.PRODUCTS_STATUS_DEL', 1))
            ->when($q, fn($x) => $x->where('p.PRODUCTS_NAME', 'like', "%$q%"))
            ->select(
                'p.PRODUCTS_ID',
                'p.PRODUCTS_NAME',
                'p.PRODUCTS_PRICE',
                'p.PRODUCTS_STOCK',
                'p.PRODUCTS_READY_TYPE',
                'p.PRODUCTS_PREORDER_DURATION',
                'p.PRODUCTS_STATUS_DEL',
                'c.CATEGORIES_NAME'
            )
            ->orderBy('p.PRODUCTS_STATUS_DEL')->orderByDesc('p.created_at')->orderByDesc('p.PRODUCTS_ID')
            ->paginate(10)->withQueryString();

        $counts = DB::table('products')->where('USERS_ID', $this->sid())
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(PRODUCTS_STATUS_DEL = 0), 0) as active, COALESCE(SUM(PRODUCTS_STATUS_DEL = 1), 0) as inactive')
            ->first();

        return view('seller.products', compact('products', 'q', 'status', 'counts'));
    }

    /** Edit = halaman Upload yang sama, sudah terisi data produk. */
    public function productEdit(string $id)
    {
        $product = $this->ownedProduct($id);

        $categories = DB::table('categories')->where('CATEGORIES_STATUS_DEL', 0)->orderBy('CATEGORIES_NAME')->get();

        $images = DB::table('product_images')->where('PRODUCTS_ID', $id)->where('PRODUCT_IMAGES_STATUS_DEL', 0)
            ->orderBy('PRODUCT_IMAGES_ID')->get()
            ->map(fn($i) => [
                'id' => (int) $i->PRODUCT_IMAGES_ID,
                'url' => asset('storage/' . $i->PRODUCT_IMAGES_URL),
                'primary' => (bool) $i->PRODUCT_IMAGES_IS_PRIMARY
            ])->values();

        $chart = json_decode($product->PRODUCTS_SIZE_CHART ?? '', true);
        $sizeChart = (is_array($chart) && isset($chart['columns'], $chart['rows'])) ? $chart : null;

        $imgW = self::IMG_W;
        $imgH = self::IMG_H;
        return view('seller.upload', compact('product', 'categories', 'images', 'sizeChart', 'imgW', 'imgH'));
    }

    public function productUpdate(Request $r, string $id)
    {
        $this->ownedProduct($id);
        $d = $this->validateProduct($r, true);

        $removeIds = array_map('intval', $d['remove_images'] ?? []);
        $newFiles  = $r->file('images') ?? [];

        DB::transaction(function () use ($id, $d, $removeIds, $newFiles) {
            $now = now();
            $current = DB::table('product_images')->where('PRODUCTS_ID', $id)->where('PRODUCT_IMAGES_STATUS_DEL', 0)->get();
            $keep = $current->reject(fn($i) => in_array((int) $i->PRODUCT_IMAGES_ID, $removeIds, true));

            $total = $keep->count() + count($newFiles);
            if ($total < 1 || $total > 5) {
                throw ValidationException::withMessages(['images' => 'A product needs 1 to 5 photos.']);
            }

            // foto yang dibuang
            foreach ($current->filter(fn($i) => in_array((int) $i->PRODUCT_IMAGES_ID, $removeIds, true)) as $img) {
                Storage::disk('public')->delete($img->PRODUCT_IMAGES_URL);
                DB::table('product_images')->where('PRODUCT_IMAGES_ID', $img->PRODUCT_IMAGES_ID)
                    ->update(['PRODUCT_IMAGES_STATUS_DEL' => 1, 'PRODUCT_IMAGES_IS_PRIMARY' => 0, 'updated_at' => $now]);
            }

            // foto baru
            foreach ($newFiles as $file) {
                DB::table('product_images')->insert([
                    'PRODUCTS_ID' => $id,
                    'PRODUCT_IMAGES_URL' => $file->store('products', 'public'),
                    'PRODUCT_IMAGES_IS_PRIMARY' => 0,
                    'PRODUCT_IMAGES_STATUS_DEL' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // foto utama: pilihan seller -> yang sudah ditandai -> foto pertama
            $ids = DB::table('product_images')->where('PRODUCTS_ID', $id)->where('PRODUCT_IMAGES_STATUS_DEL', 0)
                ->orderBy('PRODUCT_IMAGES_ID')->pluck('PRODUCT_IMAGES_ID')->map(fn($x) => (int) $x)->all();
            $flagged = $keep->first(fn($i) => (int) $i->PRODUCT_IMAGES_IS_PRIMARY === 1);
            $primary = (isset($d['primary_image']) && in_array((int) $d['primary_image'], $ids, true))
                ? (int) $d['primary_image']
                : ($flagged ? (int) $flagged->PRODUCT_IMAGES_ID : $ids[0]);

            DB::table('product_images')->where('PRODUCTS_ID', $id)->where('PRODUCT_IMAGES_STATUS_DEL', 0)
                ->update(['PRODUCT_IMAGES_IS_PRIMARY' => 0]);
            DB::table('product_images')->where('PRODUCT_IMAGES_ID', $primary)->update(['PRODUCT_IMAGES_IS_PRIMARY' => 1]);

            DB::table('products')->where('PRODUCTS_ID', $id)->update($this->productPayload($d) + ['updated_at' => $now]);
        });

        return redirect()->route('seller.products')->with('success', 'Product updated.');
    }

    /** Inactivate <-> Restore (soft: PRODUCTS_STATUS_DEL 1/0). Produk nonaktif hilang dari katalog & keranjang. */
    public function productToggle(string $id)
    {
        $p = $this->ownedProduct($id);
        $inactive = (int) $p->PRODUCTS_STATUS_DEL === 1;

        DB::table('products')->where('PRODUCTS_ID', $id)
            ->update(['PRODUCTS_STATUS_DEL' => $inactive ? 0 : 1, 'updated_at' => now()]);

        return back()->with('success', $inactive
            ? 'Product restored and visible in the shop again.'
            : 'Product inactivated. It is hidden from the shop and can be restored anytime.');
    }

    /* ===================== PROFILE ===================== */
    public function profile()
    {
        $user = DB::table('users')->where('USERS_ID', $this->sid())->first();
        return view('seller.profile', compact('user'));
    }

    public function profileUpdate(Request $r)
    {
        $d = $r->validate([
            'brand'     => 'required|max:50',
            'whatsapp'  => ['nullable', 'max:20', function ($attribute, $value, $fail) {
                if (!Phone::normalize($value)) $fail('WhatsApp number not valid. Correct example: 6281234567890.');
            }],
            'instagram' => 'nullable|max:50',
            'address'   => 'nullable|string',
            'bio'       => 'nullable|string|max:500',
            'picture'   => 'nullable|image|max:2048',
        ]);

        $upd = [
            'USERS_NAME'         => $d['brand'],
            'USERS_PHONE_NUMBER' => Phone::normalize($d['whatsapp'] ?? ''),   // disimpan sebagai 628xxx
            'USERS_INSTAGRAM'    => $d['instagram'] ?? null,
            'USERS_ADDRESS'      => $d['address'] ?? null,
            'USERS_BIO'          => $d['bio'] ?? null,
            'updated_at'         => now(),
        ];

        if ($r->hasFile('picture')) {
            $old = DB::table('users')->where('USERS_ID', $this->sid())->value('USERS_PROFILE_PICTURE');
            if ($old) Storage::disk('public')->delete($old);
            $upd['USERS_PROFILE_PICTURE'] = $r->file('picture')->store('profiles', 'public');
        }

        DB::table('users')->where('USERS_ID', $this->sid())->update($upd);
        return back()->with('success', 'Profile successfully updated.');
    }
}