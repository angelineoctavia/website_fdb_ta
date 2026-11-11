<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    /** Urutan status: pending -> paid (otomatis Midtrans) -> packing -> shipping -> completed (3 terakhir diatur seller). */
    private const STEPS = ['pending', 'paid', 'packing', 'shipping', 'completed'];

    private const STEP_LABELS = [
        'pending' => 'Waiting Payment',
        'paid' => 'Paid',
        'packing' => 'Packing',
        'shipping' => 'In Shipping',
        'completed' => 'Completed',
    ];

    /** Label + keterangan + warna untuk tiap nilai TRANSACTIONS_ORDER_STATUS. */
    private const STATUS = [
        'pending'   => ['label' => 'Waiting for Payment', 'desc' => 'Menunggu pembayaran kamu.',                       'class' => 'pending'],
        'paid'      => ['label' => 'Paid',                'desc' => 'Pembayaran diterima. Menunggu diproses seller.',    'class' => 'paid'],
        'packing'   => ['label' => 'Packing',             'desc' => 'Pesananmu sedang dikemas oleh seller.',             'class' => 'paid'],
        'shipping'  => ['label' => 'In Shipping',         'desc' => 'Pesananmu sudah dikirim.',                          'class' => 'shipping'],
        'completed' => ['label' => 'Completed',           'desc' => 'Pesanan selesai.',                                  'class' => 'completed'],
        'cancelled' => ['label' => 'Cancelled',           'desc' => 'Pesanan dibatalkan.',                               'class' => 'failed'],
        'expired'   => ['label' => 'Expired',             'desc' => 'Waktu pembayaran habis, pesanan dibatalkan.',       'class' => 'failed'],
        'failed'    => ['label' => 'Failed',              'desc' => 'Pembayaran gagal.',                                 'class' => 'failed'],
    ];

    /** Kelompok tab di halaman daftar. */
    private const GROUPS = [
        'all'       => ['label' => 'All',                 'status' => null],
        'pay'       => ['label' => 'Waiting for Payment', 'status' => ['pending']],
        'process'   => ['label' => 'Processing',          'status' => ['paid', 'packing']],
        'shipping'  => ['label' => 'In Shipping',         'status' => ['shipping']],
        'done'      => ['label' => 'Completed',           'status' => ['completed']],
        'cancelled' => ['label' => 'Cancelled',           'status' => ['cancelled', 'expired', 'failed', 'deny']],
    ];

    private function toLogin()
    {
        return redirect()->route('login')->withErrors(['error' => 'Silakan login terlebih dahulu.']);
    }

    /** Item-item pesanan (termasuk produk yang sudah dinonaktifkan seller, supaya riwayat tetap utuh). */
    private function itemsFor(array $ids)
    {
        return DB::table('transactions_details as d')
            ->join('products as p', 'p.PRODUCTS_ID', '=', 'd.PRODUCTS_ID')
            ->join('users as s', 's.USERS_ID', '=', 'p.USERS_ID')
            ->whereIn('d.TRANSACTIONS_ID', $ids)
            ->where('d.TRANSACTIONS_DETAILS_STATUS_DEL', 0)
            ->select(
                'd.TRANSACTIONS_ID',
                'd.TRANSACTIONS_QTY',
                'd.TRANSACTIONS_SUBTOTAL',
                'd.TRANSACTIONS_DETAILS_SELECTED_SIZE',
                'd.TRANSACTIONS_DETAILS_CUSTOM_SIZE_NOTES',
                'p.PRODUCTS_ID',
                'p.PRODUCTS_NAME',
                's.USERS_NAME as brand'
            )
            ->selectRaw('(SELECT i.PRODUCT_IMAGES_URL FROM product_images i WHERE i.PRODUCTS_ID = p.PRODUCTS_ID AND i.PRODUCT_IMAGES_IS_PRIMARY = 1 AND i.PRODUCT_IMAGES_STATUS_DEL = 0 LIMIT 1) as image')
            ->get();
    }

    /* ===================== DAFTAR PESANAN ===================== */
    public function index(Request $r)
    {
        $uid = session('user_id');
        if (!$uid) return $this->toLogin();

        $tab = $r->query('tab', 'all');
        if (!array_key_exists($tab, self::GROUPS)) $tab = 'all';

        $orders = DB::table('transactions')
            ->where('USERS_ID', $uid)->where('TRANSACTIONS_STATUS_DEL', 0)
            ->when(self::GROUPS[$tab]['status'], fn($q, $st) => $q->whereIn('TRANSACTIONS_ORDER_STATUS', $st))
            ->orderByDesc('TRANSACTIONS_DATE')->orderByDesc('created_at')->orderByDesc('TRANSACTIONS_ID')
            ->paginate(5)->withQueryString();

        $items = $this->itemsFor($orders->pluck('TRANSACTIONS_ID')->all())->groupBy('TRANSACTIONS_ID');

        // jumlah per tab
        $byStatus = DB::table('transactions')->where('USERS_ID', $uid)->where('TRANSACTIONS_STATUS_DEL', 0)
            ->selectRaw('TRANSACTIONS_ORDER_STATUS as s, COUNT(*) as n')->groupBy('s')->pluck('n', 's');
        $tabs = [];
        foreach (self::GROUPS as $key => $g) {
            $tabs[$key] = [
                'label' => $g['label'],
                'count' => $g['status'] === null ? $byStatus->sum() : collect($g['status'])->sum(fn($s) => $byStatus[$s] ?? 0)
            ];
        }

        $statusMeta = self::STATUS;
        return view('customer.orders', compact('orders', 'items', 'tabs', 'tab', 'statusMeta'));
    }

    /* DETAIL PESANAN */
    public function show(string $id)
    {
        $uid = session('user_id');
        if (!$uid) return $this->toLogin();

        // hanya pesanan milik customer yang login
        $order = DB::table('transactions')->where('TRANSACTIONS_ID', $id)
            ->where('USERS_ID', $uid)->where('TRANSACTIONS_STATUS_DEL', 0)->first();
        abort_if(!$order, 404);

        $items = $this->itemsFor([$id]);
        $statusMeta = self::STATUS;
        $steps = self::STEPS;
        $stepLabels = self::STEP_LABELS;

        return view('customer.order_detail', compact('order', 'items', 'statusMeta', 'steps', 'stepLabels'));
    }
}