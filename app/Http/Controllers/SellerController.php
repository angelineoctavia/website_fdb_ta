<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SellerController extends Controller
{
    // Status order di TRANSACTIONS_ORDER_STATUS:
    // pending (nunggu bayar) -> paid (otomatis dari webhook Midtrans)
    // -> packing -> shipping -> completed (3 terakhir manual oleh seller)
    const PAID = ['paid', 'packing', 'shipping', 'completed'];
    const NEXT = ['paid' => ['packing'], 'packing' => ['shipping'], 'shipping' => ['completed']];

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
        return view('seller.upload', compact('categories'));
    }

    public function uploadStore(Request $r)
    {
        $d = $r->validate([
            'name'          => 'required|max:100',
            'category'      => 'required|exists:categories,CATEGORIES_ID',
            'gender'        => 'required|in:Women,Men,Unisex',
            'price'         => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',
            'description'   => 'nullable|string',
            'material'      => 'nullable|max:100',
            'size_chart_json' => 'nullable|string',
            'ready_type'    => 'required|in:1,2',                 // 1 = ready stock, 2 = pre-order
            'preorder_days' => 'nullable|integer|min:1|required_if:ready_type,2',
            'images'        => 'required|array|min:1|max:5',
            'images.*'      => 'image|max:4096',
        ]);

        DB::transaction(function () use ($r, $d) {
            $last = DB::table('products')->lockForUpdate()->max('PRODUCTS_ID');
            $id   = 'PRD' . str_pad($last ? ((int) substr($last, 3)) + 1 : 1, 5, '0', STR_PAD_LEFT);
            $now  = now();

            DB::table('products')->insert([
                'PRODUCTS_ID'              => $id,
                'CATEGORIES_ID'            => $d['category'],
                'USERS_ID'                 => $this->sid(),
                'PRODUCTS_NAME'            => $d['name'],
                'PRODUCTS_PRICE'           => $d['price'],
                'PRODUCTS_STOCK'           => $d['stock'],
                'PRODUCTS_DESCRIPTION'     => $d['description'] ?? null,
                'PRODUCTS_MATERIAL'        => $d['material'] ?? null,
                'PRODUCTS_GENDER'          => $d['gender'],
                // opsional: null kalau seller tidak mengisi size chart
                'PRODUCTS_SIZE_CHART'      => $this->cleanSizeChart($d['size_chart_json'] ?? null),
                'PRODUCTS_READY_TYPE'      => $d['ready_type'],
                'PRODUCTS_PREORDER_DURATION'   => $d['ready_type'] == 2 ? $d['preorder_days'] : null,
                'PRODUCTS_STATUS_DEL'      => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($r->file('images') as $i => $file) {
                DB::table('product_images')->insert([
                    'PRODUCTS_ID'                => $id,
                    'PRODUCT_IMAGES_URL'         => $file->store('products', 'public'),
                    'PRODUCT_IMAGES_IS_PRIMARY'  => $i === 0 ? 1 : 0,
                    'PRODUCT_IMAGES_STATUS_DEL'  => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        return redirect()->route('seller.upload')->with('success', 'Produk berhasil dipublish!');
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

        return back()->with('success', 'Status pesanan diperbarui.');
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
            'whatsapp'  => 'nullable|max:15',
            'instagram' => 'nullable|max:50',
            'address'   => 'nullable|string',
            'picture'   => 'nullable|image|max:2048',
        ]);

        $upd = [
            'USERS_NAME'         => $d['brand'],
            'USERS_PHONE_NUMBER' => $d['whatsapp'] ?? null,
            'USERS_INSTAGRAM'    => $d['instagram'] ?? null,
            'USERS_ADDRESS'      => $d['address'] ?? null,
            'updated_at'         => now(),
        ];

        if ($r->hasFile('picture')) {
            $old = DB::table('users')->where('USERS_ID', $this->sid())->value('USERS_PROFILE_PICTURE');
            if ($old) Storage::disk('public')->delete($old);
            $upd['USERS_PROFILE_PICTURE'] = $r->file('picture')->store('profiles', 'public');
        }

        DB::table('users')->where('USERS_ID', $this->sid())->update($upd);
        return back()->with('success', 'Profil berhasil disimpan.');
    }
}