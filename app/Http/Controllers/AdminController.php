<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminController extends Controller
{
    // === SESUAIKAN dengan kode role di tabel users ===
    const ADMIN_ROLE  = 'A';
    const SELLER_ROLE = 'S';

    // Semua uang masuk ke akun admin (Midtrans). Admin 20%, seller (mahasiswa) 80%.
    const FEE_RATE = 0.20;

    // Status yang dihitung sebagai "uang sudah masuk"
    const PAID = ['paid', 'packing', 'shipping', 'completed'];

    /* ---------- helper ---------- */

    /** Fee admin: pakai kolom TRANSACTIONS_PRODI_FEE; kalau masih NULL (data lama) hitung 20%. */
    private function feeSql(): string
    {
        return 'COALESCE(t.TRANSACTIONS_PRODI_FEE, ROUND(t.TRANSACTIONS_TOTAL_AMOUNT * ' . self::FEE_RATE . ', 2))';
    }

    /** Bagian seller: pakai kolom TRANSACTIONS_SELLER_PAY; kalau NULL = total - fee. */
    private function sellerPaySql(): string
    {
        return 'COALESCE(t.TRANSACTIONS_SELLER_PAY, t.TRANSACTIONS_TOTAL_AMOUNT - ' . $this->feeSql() . ')';
    }

    /** Rentang tanggal dari ?from=&to= (default: 6 bulan terakhir sampai hari ini). */
    private function range(Request $r): array
    {
        $v = $r->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);
        $to   = !empty($v['to'])   ? Carbon::parse($v['to'])   : Carbon::today();
        $from = !empty($v['from']) ? Carbon::parse($v['from']) : Carbon::today()->startOfMonth()->subMonths(5);
        if ($from->gt($to)) [$from, $to] = [$to, $from];
        return [$from->toDateString(), $to->toDateString()];
    }

    private function paidTx(string $from, string $to)
    {
        return DB::table('transactions as t')
            ->where('t.TRANSACTIONS_STATUS_DEL', 0)
            ->whereIn('t.TRANSACTIONS_ORDER_STATUS', self::PAID)
            ->whereBetween('t.TRANSACTIONS_DATE', [$from, $to]);
    }

    private function paidLines(string $from, string $to)
    {
        return DB::table('transactions_details as d')
            ->join('transactions as t', 't.TRANSACTIONS_ID', '=', 'd.TRANSACTIONS_ID')
            ->join('products as p', 'p.PRODUCTS_ID', '=', 'd.PRODUCTS_ID')
            ->join('users as s', 's.USERS_ID', '=', 'p.USERS_ID')
            ->where('t.TRANSACTIONS_STATUS_DEL', 0)
            ->where('d.TRANSACTIONS_DETAILS_STATUS_DEL', 0)
            ->whereIn('t.TRANSACTIONS_ORDER_STATUS', self::PAID)
            ->whereBetween('t.TRANSACTIONS_DATE', [$from, $to]);
    }

    /** Omzet kotor + jumlah order per seller (semua seller, termasuk yang 0). */
    private function sellerSales(string $from, string $to)
    {
        $sales = $this->paidLines($from, $to)
            ->selectRaw('p.USERS_ID, SUM(d.TRANSACTIONS_SUBTOTAL) as gross, COUNT(DISTINCT t.TRANSACTIONS_ID) as orders')
            ->groupBy('p.USERS_ID')->get()->keyBy('USERS_ID');

        return DB::table('users')->where('USERS_ROLE', self::SELLER_ROLE)->get(['USERS_ID', 'USERS_NAME'])
            ->map(fn($s) => (object) [
                'id'     => $s->USERS_ID,
                'name'   => $s->USERS_NAME,
                'orders' => (int) ($sales[$s->USERS_ID]->orders ?? 0),
                'gross'  => (float) ($sales[$s->USERS_ID]->gross ?? 0),
            ])->sortByDesc('gross')->values();
    }

    /** Query dasar daftar order (1 baris = 1 transaksi), dipakai halaman Orders & laporan. */
    private function ordersQuery()
    {
        return DB::table('transactions as t')
            ->join('users as b', 'b.USERS_ID', '=', 't.USERS_ID')
            ->join('transactions_details as d', 'd.TRANSACTIONS_ID', '=', 't.TRANSACTIONS_ID')
            ->join('products as p', 'p.PRODUCTS_ID', '=', 'd.PRODUCTS_ID')
            ->join('users as s', 's.USERS_ID', '=', 'p.USERS_ID')
            ->where('t.TRANSACTIONS_STATUS_DEL', 0)
            ->where('d.TRANSACTIONS_DETAILS_STATUS_DEL', 0)
            ->select(
                't.TRANSACTIONS_ID',
                't.TRANSACTIONS_DATE',
                't.TRANSACTIONS_ORDER_STATUS',
                't.TRANSACTIONS_TOTAL_AMOUNT',
                't.TRANSACTIONS_PAYMENT_METHOD',
                'b.USERS_NAME as customer'
            )
            ->selectRaw('GROUP_CONCAT(DISTINCT s.USERS_NAME SEPARATOR ", ") as sellers, GROUP_CONCAT(p.PRODUCTS_NAME SEPARATOR ", ") as products, SUM(d.TRANSACTIONS_QTY) as qty, '
                . $this->feeSql() . ' as fee, ' . $this->sellerPaySql() . ' as seller_pay')
            ->groupBy(
                't.TRANSACTIONS_ID',
                't.TRANSACTIONS_DATE',
                't.TRANSACTIONS_ORDER_STATUS',
                't.TRANSACTIONS_TOTAL_AMOUNT',
                't.TRANSACTIONS_PAYMENT_METHOD',
                't.TRANSACTIONS_PRODI_FEE',
                't.TRANSACTIONS_SELLER_PAY',
                'b.USERS_NAME'
            );
    }

    /* ===================== DASHBOARD ===================== */
    public function dashboard(Request $r)
    {
        [$from, $to] = $this->range($r);

        $stats = [
            'gross'    => $this->paidTx($from, $to)->sum('t.TRANSACTIONS_TOTAL_AMOUNT'),
            'fee'      => $this->paidTx($from, $to)->sum(DB::raw($this->feeSql())),
            'orders'   => $this->paidTx($from, $to)->count(),
            'awaiting' => DB::table('transactions')->where('TRANSACTIONS_STATUS_DEL', 0)
                ->where('TRANSACTIONS_ORDER_STATUS', 'pending')
                ->whereBetween('TRANSACTIONS_DATE', [$from, $to])->count(),
            // produk aktif = kondisi saat ini, tidak ikut filter tanggal
            'products' => DB::table('products as p')->join('users as s', 's.USERS_ID', '=', 'p.USERS_ID')
                ->where('p.PRODUCTS_STATUS_DEL', 0)->where('p.PRODUCTS_STOCK', '>', 0)
                ->where('s.USERS_STATUS_DEL', 0)->count(),
            'sellers'  => DB::table('products as p')->join('users as s', 's.USERS_ID', '=', 'p.USERS_ID')
                ->where('p.PRODUCTS_STATUS_DEL', 0)->where('p.PRODUCTS_STOCK', '>', 0)
                ->where('s.USERS_STATUS_DEL', 0)->distinct()->count('p.USERS_ID'),
        ];

        // Tren: harian kalau rentang <= 62 hari, selain itu bulanan. Periode kosong tetap tampil (0).
        $byDay = (int) Carbon::parse($from)->diffInDays(Carbon::parse($to)) <= 62;
        $fmt   = $byDay ? '%Y-%m-%d' : '%Y-%m';
        $rows  = $this->paidTx($from, $to)
            ->selectRaw("DATE_FORMAT(t.TRANSACTIONS_DATE,'$fmt') as k, SUM(t.TRANSACTIONS_TOTAL_AMOUNT) as gross, COUNT(*) as orders, SUM(" . $this->feeSql() . ") as fee")
            ->groupBy('k')->get()->keyBy('k');

        $periods = [];
        if ($byDay) {
            foreach (CarbonPeriod::create($from, $to) as $d) $periods[$d->format('Y-m-d')] = $d->format('d M');
        } else {
            $cur = Carbon::parse($from)->startOfMonth();
            $end = Carbon::parse($to)->startOfMonth();
            while ($cur->lte($end)) {
                $periods[$cur->format('Y-m')] = $cur->format('M Y');
                $cur->addMonth();
            }
        }
        $trend = collect($periods)->map(fn($label, $k) => [
            'label'  => $label,
            'gross'  => (float) ($rows[$k]->gross ?? 0),
            'orders' => (int) ($rows[$k]->orders ?? 0),
            'fee'    => (float) ($rows[$k]->fee ?? 0),
        ])->values();

        $sellers = $this->sellerSales($from, $to);

        $categories = DB::table('categories as c')
            ->leftJoin('products as p', fn($j) => $j->on('p.CATEGORIES_ID', '=', 'c.CATEGORIES_ID')->where('p.PRODUCTS_STATUS_DEL', 0))
            ->where('c.CATEGORIES_STATUS_DEL', 0)
            ->groupBy('c.CATEGORIES_ID', 'c.CATEGORIES_NAME')
            ->selectRaw('c.CATEGORIES_ID, c.CATEGORIES_NAME, COUNT(p.PRODUCTS_ID) as total')
            ->orderBy('c.CATEGORIES_NAME')->get();

        $recent = DB::table('transactions as t')
            ->join('users as b', 'b.USERS_ID', '=', 't.USERS_ID')
            ->where('t.TRANSACTIONS_STATUS_DEL', 0)
            ->whereBetween('t.TRANSACTIONS_DATE', [$from, $to])
            ->orderByDesc('t.TRANSACTIONS_DATE')->orderByDesc('t.TRANSACTIONS_ID')->limit(5)
            ->get(['t.TRANSACTIONS_ID', 't.TRANSACTIONS_DATE', 't.TRANSACTIONS_TOTAL_AMOUNT', 't.TRANSACTIONS_ORDER_STATUS', 'b.USERS_NAME as customer']);

        $top = $this->paidLines($from, $to)
            ->select('p.PRODUCTS_NAME as name', 'p.PRODUCTS_PRICE as price', 's.USERS_NAME as business')
            ->selectRaw('SUM(d.TRANSACTIONS_QTY) as sold, (SELECT i.PRODUCT_IMAGES_URL FROM product_images i WHERE i.PRODUCTS_ID = p.PRODUCTS_ID AND i.PRODUCT_IMAGES_IS_PRIMARY = 1 AND i.PRODUCT_IMAGES_STATUS_DEL = 0 LIMIT 1) as image')
            ->groupBy('p.PRODUCTS_ID', 'p.PRODUCTS_NAME', 'p.PRODUCTS_PRICE', 's.USERS_NAME')
            ->orderByDesc('sold')->limit(5)->get();

        $feeRate = self::FEE_RATE;
        return view('admin.dashboard', compact('from', 'to', 'stats', 'trend', 'byDay', 'sellers', 'categories', 'recent', 'top', 'feeRate'));
    }

    /* ===================== LAPORAN XLSX ===================== */
    public function report(Request $r)
    {
        [$from, $to] = $this->range($r);
        $rate = self::FEE_RATE;

        $gross = (float) $this->paidTx($from, $to)->sum('t.TRANSACTIONS_TOTAL_AMOUNT');
        $fee   = (float) $this->paidTx($from, $to)->sum(DB::raw($this->feeSql()));
        $count = $this->paidTx($from, $to)->count();

        $wb = new Spreadsheet();

        // --- Sheet 1: Ringkasan ---
        $ws = $wb->getActiveSheet()->setTitle('Ringkasan');
        $ws->setCellValue('A1', 'Laporan Penjualan - Made By Pidiz');
        $ws->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $info = [
            ['Periode', Carbon::parse($from)->format('d M Y') . ' s/d ' . Carbon::parse($to)->format('d M Y')],
            ['Dibuat pada', now()->format('d M Y H:i')],
            ['Total Revenue (uang masuk)', $gross],
            ['Pendapatan Admin (' . (int) round($rate * 100) . '%)', $fee],
            ['Hak Seller (' . (100 - (int) round($rate * 100)) . '%)', $gross - $fee],
            ['Jumlah Order', $count],
            ['Catatan', 'Hanya transaksi berstatus paid, packing, shipping, completed.'],
        ];
        foreach ($info as $i => [$label, $val]) {
            $row = 3 + $i;
            $ws->setCellValue("A$row", $label);
            $ws->getStyle("A$row")->getFont()->setBold(true);
            $ws->setCellValueExplicit("B$row", (string) $val, is_numeric($val) ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING);
        }
        foreach ([5, 6, 7] as $row) $ws->getStyle("B$row")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $ws->getStyle('B3:B9')->getAlignment()->setHorizontal('left');
        $ws->getColumnDimension('A')->setWidth(32);
        $ws->getColumnDimension('B')->setWidth(48);

        // --- Sheet 2: Transaksi ---
        $tx = $this->ordersQuery()
            ->whereIn('t.TRANSACTIONS_ORDER_STATUS', self::PAID)
            ->whereBetween('t.TRANSACTIONS_DATE', [$from, $to])
            ->orderBy('t.TRANSACTIONS_DATE')->orderBy('t.TRANSACTIONS_ID')->get()
            ->map(fn($o) => [
                Carbon::parse($o->TRANSACTIONS_DATE)->format('Y-m-d'),
                $o->TRANSACTIONS_ID,
                $o->customer,
                $o->sellers,
                $o->products,
                (int) $o->qty,
                (float) $o->TRANSACTIONS_TOTAL_AMOUNT,
                (float) $o->fee,
                (float) $o->seller_pay,
                $o->TRANSACTIONS_PAYMENT_METHOD ?? '-',
                $o->TRANSACTIONS_ORDER_STATUS,
            ]);
        $this->fillSheet(
            $wb->createSheet()->setTitle('Transaksi'),
            [
                'Tanggal',
                'Order ID',
                'Customer',
                'Seller',
                'Produk',
                'Qty',
                'Total',
                'Admin ' . (int) round($rate * 100) . '%',
                'Seller ' . (100 - (int) round($rate * 100)) . '%',
                'Metode Bayar',
                'Status'
            ],
            $tx,
            ['G', 'H', 'I'],
            ['B']
        );

        // --- Sheet 3: Per Seller ---
        $perSeller = $this->sellerSales($from, $to)->map(function ($s) use ($rate) {
            $admin = round($s->gross * $rate, 2);
            return [$s->name, $s->orders, $s->gross, $admin, $s->gross - $admin];
        });
        $this->fillSheet(
            $wb->createSheet()->setTitle('Per Seller'),
            ['Business', 'Jumlah Order', 'Revenue', 'Admin ' . (int) round($rate * 100) . '%', 'Hak Seller ' . (100 - (int) round($rate * 100)) . '%'],
            $perSeller,
            ['C', 'D', 'E']
        );

        // --- Sheet 4: Detail Item (untuk rekap transfer ke seller) ---
        $items = $this->paidLines($from, $to)
            ->orderBy('t.TRANSACTIONS_DATE')->orderBy('t.TRANSACTIONS_ID')
            ->get([
                't.TRANSACTIONS_DATE',
                't.TRANSACTIONS_ID',
                's.USERS_NAME as seller',
                'p.PRODUCTS_NAME',
                'd.TRANSACTIONS_DETAILS_SELECTED_SIZE',
                'd.TRANSACTIONS_QTY',
                'd.TRANSACTIONS_SUBTOTAL'
            ])
            ->map(function ($i) use ($rate) {
                $sub = (float) $i->TRANSACTIONS_SUBTOTAL;
                $admin = round($sub * $rate, 2);
                return [
                    Carbon::parse($i->TRANSACTIONS_DATE)->format('Y-m-d'),
                    $i->TRANSACTIONS_ID,
                    $i->seller,
                    $i->PRODUCTS_NAME,
                    $i->TRANSACTIONS_DETAILS_SELECTED_SIZE ?: '-',
                    (int) $i->TRANSACTIONS_QTY,
                    $sub / max((int) $i->TRANSACTIONS_QTY, 1),
                    $sub,
                    $admin,
                    $sub - $admin
                ];
            });
        $this->fillSheet(
            $wb->createSheet()->setTitle('Detail Item'),
            [
                'Tanggal',
                'Order ID',
                'Business',
                'Produk',
                'Ukuran',
                'Qty',
                'Harga Satuan',
                'Subtotal',
                'Admin ' . (int) round($rate * 100) . '%',
                'Seller ' . (100 - (int) round($rate * 100)) . '%'
            ],
            $items,
            ['G', 'H', 'I', 'J'],
            ['B']
        );

        $wb->setActiveSheetIndex(0);
        $name = "laporan-penjualan_{$from}_sd_{$to}.xlsx";

        return response()->streamDownload(
            fn() => (new Xlsx($wb))->save('php://output'),
            $name,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    /** Isi satu sheet: header merah, format Rp, kolom teks, auto-filter, freeze header. */
    private function fillSheet($ws, array $headers, $rows, array $moneyCols = [], array $textCols = []): void
    {
        $n = count($headers);
        $last = Coordinate::stringFromColumnIndex($n);

        foreach ($headers as $i => $h) $ws->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . '1', $h);
        $head = $ws->getStyle("A1:{$last}1");
        $head->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $head->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCC0A48');

        $r = 2;
        foreach ($rows as $row) {
            foreach (array_values($row) as $i => $v) {
                $col = Coordinate::stringFromColumnIndex($i + 1);
                in_array($col, $textCols, true)
                    ? $ws->setCellValueExplicit("$col$r", (string) $v, DataType::TYPE_STRING)   // Order ID jangan jadi angka
                    : $ws->setCellValue("$col$r", $v);
            }
            $r++;
        }
        $end = max($r - 1, 1);

        foreach ($moneyCols as $c) $ws->getStyle("{$c}2:{$c}{$end}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        for ($i = 1; $i <= $n; $i++) $ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        $ws->setAutoFilter("A1:{$last}{$end}");
        $ws->freezePane('A2');
    }

    /* ===================== CATEGORIES (soft delete) ===================== */
    public function categoryStore(Request $r)
    {
        $d = $r->validate([
            'name' => ['required', 'max:35', Rule::unique('categories', 'CATEGORIES_NAME')->where('CATEGORIES_STATUS_DEL', 0)],
        ]);

        // ID varchar(4): lanjutkan pola ID terakhir (mis. C001 -> C002)
        $last = DB::table('categories')->orderByDesc('CATEGORIES_ID')->value('CATEGORIES_ID');
        $id = ($last && preg_match('/^(\D*)(\d+)$/', $last, $m))
            ? $m[1] . str_pad((int) $m[2] + 1, strlen($m[2]), '0', STR_PAD_LEFT)
            : 'C001';
        if (strlen($id) > 4) return back()->withErrors(['name' => 'ID kategori sudah melebihi 4 karakter.']);

        DB::table('categories')->insert([
            'CATEGORIES_ID' => $id,
            'CATEGORIES_NAME' => $d['name'],
            'CATEGORIES_STATUS_DEL' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function categoryUpdate(Request $r, string $id)
    {
        $d = $r->validate([
            'name' => [
                'required',
                'max:35',
                Rule::unique('categories', 'CATEGORIES_NAME')->where('CATEGORIES_STATUS_DEL', 0)->ignore($id, 'CATEGORIES_ID')
            ],
        ]);
        DB::table('categories')->where('CATEGORIES_ID', $id)->update(['CATEGORIES_NAME' => $d['name'], 'updated_at' => now()]);
        return back()->with('success', 'Kategori diperbarui.');
    }

    public function categoryDestroy(string $id)
    {
        // Soft delete saja: FK products -> categories bersifat cascade, hard delete akan menghapus produk.
        $used = DB::table('products')->where('CATEGORIES_ID', $id)->where('PRODUCTS_STATUS_DEL', 0)->count();
        if ($used > 0) {
            return back()->withErrors(['name' => "Kategori masih dipakai $used produk aktif, tidak bisa dihapus."]);
        }
        DB::table('categories')->where('CATEGORIES_ID', $id)->update(['CATEGORIES_STATUS_DEL' => 1, 'updated_at' => now()]);
        return back()->with('success', 'Kategori dihapus.');
    }

    /* ===================== ORDERS (semua seller) ===================== */
    public function orders(Request $r)
    {
        $q = trim((string) $r->query('q'));
        $status = $r->query('status');

        $orders = $this->ordersQuery()
            ->when($status, fn($x) => $x->where('t.TRANSACTIONS_ORDER_STATUS', $status))
            ->when($q, fn($x) => $x->where(function ($w) use ($q) {
                $w->where('t.TRANSACTIONS_ID', 'like', "%$q%")
                    ->orWhere('b.USERS_NAME', 'like', "%$q%")
                    ->orWhere('s.USERS_NAME', 'like', "%$q%")
                    ->orWhere('p.PRODUCTS_NAME', 'like', "%$q%");
            }))
            ->orderByDesc('t.TRANSACTIONS_DATE')->orderByDesc('t.TRANSACTIONS_ID')
            ->paginate(10)->withQueryString();

        return view('admin.orders', compact('orders', 'q', 'status'));
    }

    public function orderShow(string $id)
    {
        $order = DB::table('transactions as t')
            ->join('users as b', 'b.USERS_ID', '=', 't.USERS_ID')
            ->where('t.TRANSACTIONS_ID', $id)->where('t.TRANSACTIONS_STATUS_DEL', 0)
            ->select('t.*', 'b.USERS_NAME as customer', 'b.USERS_PHONE_NUMBER as customer_phone')
            ->first();
        abort_if(!$order, 404);

        $items = DB::table('transactions_details as d')
            ->join('products as p', 'p.PRODUCTS_ID', '=', 'd.PRODUCTS_ID')
            ->join('users as s', 's.USERS_ID', '=', 'p.USERS_ID')
            ->where('d.TRANSACTIONS_ID', $id)->where('d.TRANSACTIONS_DETAILS_STATUS_DEL', 0)
            ->get([
                'p.PRODUCTS_NAME',
                's.USERS_NAME as seller',
                'd.TRANSACTIONS_QTY',
                'd.TRANSACTIONS_SUBTOTAL',
                'd.TRANSACTIONS_DETAILS_SELECTED_SIZE',
                'd.TRANSACTIONS_DETAILS_CUSTOM_SIZE_NOTES'
            ]);

        $fee = $order->TRANSACTIONS_PRODI_FEE ?? round($order->TRANSACTIONS_TOTAL_AMOUNT * self::FEE_RATE, 2);
        $sellerPay = $order->TRANSACTIONS_SELLER_PAY ?? ($order->TRANSACTIONS_TOTAL_AMOUNT - $fee);
        $feeRate = self::FEE_RATE;

        return view('admin.order_detail', compact('order', 'items', 'fee', 'sellerPay', 'feeRate'));
    }

    /* ===================== PROFILE (read-only) ===================== */
    public function profile()
    {
        $user = DB::table('users')->where('USERS_ID', session('user_id'))->first();
        abort_if(!$user, 404);
        $feeRate = self::FEE_RATE;
        return view('admin.profile', compact('user', 'feeRate'));
    }
}