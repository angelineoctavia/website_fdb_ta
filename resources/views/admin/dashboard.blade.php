@extends('layouts.seller')
@section('title', 'Admin Dashboard')
@section('header')@include('components.admin_header')@endsection

@section('content')
    @include('seller.styles')
    @php
        $rp = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $pct = (int) round($feeRate * 100);
        $periode = \Carbon\Carbon::parse($from)->format('d M Y') . ' – ' . \Carbon\Carbon::parse($to)->format('d M Y');
    @endphp
    <style>
        .ad-wrap {
            max-width: 1280px
        }

        .ad-grid3 {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 16px;
            margin-top: 16px
        }

        .ad-sub {
            display: block;
            font-size: 11px;
            color: #777;
            margin-top: 4px
        }

        .sc-sm {
            background: #f0508c;
            color: #fff;
            border: 0;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            cursor: pointer
        }

        .sc-sm.gray {
            background: #999
        }

        .ad-scroll {
            max-height: 300px;
            overflow: auto;
            margin-top: 8px
        }

        .ad-thumb {
            width: 34px;
            height: 34px;
            border-radius: 6px;
            object-fit: cover;
            background: #eee;
            vertical-align: middle;
            margin-right: 8px
        }

        .ad-title {
            font-size: 13px;
            font-weight: 600
        }

        .ad-filter {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-wrap: wrap
        }

        .ad-filter .f {
            display: flex;
            flex-direction: column
        }

        .ad-filter .sc-label {
            margin: 0 0 4px
        }

        .ad-chip {
            background: #f3f3f3;
            border: 0;
            border-radius: 99px;
            padding: 6px 12px;
            font-size: 12px;
            cursor: pointer
        }

        .ad-chip:hover {
            background: #ffd9e5
        }

        .ad-dl {
            background: #1d7a46;
            color: #fff;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            text-decoration: none;
            margin-left: auto;
            white-space: nowrap
        }

        .ad-dl:hover {
            background: #17623a
        }

        @media(max-width:900px) {
            .ad-grid3 {
                grid-template-columns: 1fr
            }

            .ad-dl {
                margin-left: 0
            }
        }
    </style>

    <div class="sc-wrap ad-wrap">

        {{-- FILTER TANGGAL + DOWNLOAD --}}
        <form method="GET" action="{{ route('admin.dashboard') }}" id="filterForm" class="sc-card ad-filter">
            <div class="f"><label class="sc-label">Dari</label>
                <input type="date" class="sc-input" name="from" id="from" value="{{ $from }}"
                    style="width:160px">
            </div>
            <div class="f"><label class="sc-label">Sampai</label>
                <input type="date" class="sc-input" name="to" id="to" value="{{ $to }}"
                    style="width:160px">
            </div>
            <button class="sc-btn">Terapkan</button>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
                <button type="button" class="ad-chip" data-preset="month">Bulan ini</button>
                <button type="button" class="ad-chip" data-preset="30d">30 hari</button>
                <button type="button" class="ad-chip" data-preset="3m">3 bulan</button>
                <button type="button" class="ad-chip" data-preset="6m">6 bulan</button>
                <button type="button" class="ad-chip" data-preset="year">Tahun ini</button>
            </div>
            <a class="ad-dl" href="{{ route('admin.report', ['from' => $from, 'to' => $to]) }}">⬇ Download Laporan
                (.xlsx)</a>
        </form>
        <div style="font-size:12px;color:#666;margin:8px 4px 12px">Periode: <b>{{ $periode }}</b></div>

        {{-- KARTU ANGKA --}}
        <div class="sc-grid4">
            <div class="sc-card"><small>Total Revenue (uang masuk)</small>
                <h3>{{ $rp($stats['gross']) }}</h3>
                <span class="ad-sub">Hak seller {{ 100 - $pct }}%: {{ $rp($stats['gross'] - $stats['fee']) }}</span>
            </div>
            <div class="sc-card"><small>Admin Income ({{ $pct }}%)</small>
                <h3>{{ $rp($stats['fee']) }}</h3>
                <span class="ad-sub">dari {{ $stats['orders'] }} transaksi</span>
            </div>
            <div class="sc-card"><small>Total Orders (sudah dibayar)</small>
                <h3>{{ $stats['orders'] }} orders</h3>
                <span class="ad-sub">{{ $stats['awaiting'] }} menunggu pembayaran</span>
            </div>
            <div class="sc-card"><small>Active Products (saat ini)</small>
                <h3>{{ $stats['products'] }} products</h3>
                <span class="ad-sub">dari {{ $stats['sellers'] }} seller aktif</span>
            </div>
        </div>

        <div class="ad-grid3">
            {{-- TREN --}}
            <div class="sc-card"><span class="ad-title">Monthly Revenue &amp; Sales Trend</span>
                <span class="ad-sub"
                    style="display:inline;margin-left:6px">({{ $byDay ? 'per hari' : 'per bulan' }})</span>
                <div style="height:300px;margin-top:8px"><canvas id="trend"></canvas></div>
            </div>

            {{-- SALES BY BUSINESS (tabel + search, tanpa CRUD) --}}
            <div class="sc-card"><span class="ad-title">Sales by Business Names</span>
                <input class="sc-input" id="sellerSearch" placeholder="Cari nama bisnis..." style="margin-top:8px">
                <div class="ad-scroll">
                    <table class="sc-table">
                        <thead>
                            <tr>
                                <th>Business</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sellers as $s)
                                <tr class="seller-row" data-name="{{ strtolower($s->name) }}">
                                    <td>{{ $s->name }}</td>
                                    <td>{{ $s->orders }}</td>
                                    <td>{{ $rp($s->gross) }}<span class="ad-sub">Hak seller {{ 100 - $pct }}%:
                                            {{ $rp($s->gross * (1 - $feeRate)) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">Belum ada seller.</td>
                                </tr>
                            @endforelse
                            <tr id="sellerEmpty" style="display:none">
                                <td colspan="3">Tidak ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- KATEGORI --}}
            <div class="sc-card"><span class="ad-title">Manage Product Categories</span>
                <input class="sc-input" id="catSearch" placeholder="Filter kategori..." style="margin:8px 0">
                <form method="POST" action="{{ route('admin.categories.store') }}"
                    style="display:flex;gap:6px;margin-bottom:8px">
                    @csrf
                    <input class="sc-input" name="name" placeholder="Kategori baru" maxlength="35" required>
                    <button class="sc-sm">Add</button>
                </form>
                <div class="ad-scroll" style="max-height:220px">
                    <table class="sc-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Produk</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $c)
                                <tr class="cat-row" data-name="{{ strtolower($c->CATEGORIES_NAME) }}">
                                    <td>
                                        <span class="cn">{{ $c->CATEGORIES_NAME }}</span>
                                        <form class="ce" method="POST"
                                            action="{{ route('admin.categories.update', $c->CATEGORIES_ID) }}"
                                            style="display:none;gap:4px">
                                            @csrf @method('PATCH')
                                            <input class="sc-input" name="name" value="{{ $c->CATEGORIES_NAME }}"
                                                maxlength="35" required>
                                            <button class="sc-sm">Save</button>
                                        </form>
                                    </td>
                                    <td>{{ $c->total }}</td>
                                    <td style="white-space:nowrap">
                                        <button type="button" class="sc-sm edit">Edit</button>
                                        <form method="POST"
                                            action="{{ route('admin.categories.destroy', $c->CATEGORIES_ID) }}"
                                            style="display:inline"
                                            onsubmit="return confirm('Hapus kategori {{ e($c->CATEGORIES_NAME) }}?')">
                                            @csrf @method('DELETE')
                                            <button class="sc-sm gray">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">Belum ada kategori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="sc-grid11">
            {{-- RECENT --}}
            <div class="sc-card">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span class="ad-title">Recent Transactions</span>
                    <a href="{{ route('admin.orders') }}" style="font-size:12px">Lihat semua →</a>
                </div>
                <table class="sc-table" style="margin-top:8px">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent as $o)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($o->TRANSACTIONS_DATE)->format('d M Y') }}</td>
                                <td>{{ $o->customer }}</td>
                                <td>{{ $rp($o->TRANSACTIONS_TOTAL_AMOUNT) }}</td>
                                <td><span
                                        class="sc-pill {{ $o->TRANSACTIONS_ORDER_STATUS }}">{{ ucfirst($o->TRANSACTIONS_ORDER_STATUS) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Belum ada transaksi di periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TOP SELLING --}}
            <div class="sc-card"><span class="ad-title">Top Selling Products</span>
                <table class="sc-table" style="margin-top:8px">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Business</th>
                            <th>Price</th>
                            <th>Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($top as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    @if ($p->image)
                                        <img class="ad-thumb" src="{{ asset('storage/' . $p->image) }}" alt="">
                                    @endif{{ $p->name }}
                                </td>
                                <td>{{ $p->business }}</td>
                                <td>{{ $rp($p->price) }}</td>
                                <td>{{ $p->sold }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Belum ada penjualan di periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
    <script>
        const T = @json($trend);
        const fmt = n => new Intl.NumberFormat('id-ID').format(n);
        new Chart(document.getElementById('trend'), {
            type: 'line',
            data: {
                labels: T.map(x => x.label),
                datasets: [{
                    label: 'Total Revenue',
                    data: T.map(x => x.gross),
                    borderColor: '#c8104a',
                    backgroundColor: 'rgba(200,16,74,.10)',
                    fill: true,
                    tension: .3,
                    pointRadius: T.length > 31 ? 0 : 4
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: c => 'Revenue: Rp ' + fmt(c.parsed.y),
                            afterBody: it => ['Orders: ' + T[it[0].dataIndex].orders,
                                'Admin {{ $pct }}%: Rp ' + fmt(T[it[0].dataIndex].fee)
                            ]
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => v >= 1e6 ? (v / 1e6) + ' jt' : fmt(v)
                        }
                    }
                }
            }
        });

        // ---- preset tanggal (pakai tanggal lokal browser, bukan UTC) ----
        const pad = n => String(n).padStart(2, '0');
        const ymd = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
        document.querySelectorAll('[data-preset]').forEach(b => b.onclick = () => {
            const t = new Date();
            let f = new Date(t);
            switch (b.dataset.preset) {
                case 'month':
                    f = new Date(t.getFullYear(), t.getMonth(), 1);
                    break;
                case '30d':
                    f.setDate(t.getDate() - 29);
                    break;
                case '3m':
                    f = new Date(t.getFullYear(), t.getMonth() - 2, 1);
                    break;
                case '6m':
                    f = new Date(t.getFullYear(), t.getMonth() - 5, 1);
                    break;
                case 'year':
                    f = new Date(t.getFullYear(), 0, 1);
                    break;
            }
            document.getElementById('from').value = ymd(f);
            document.getElementById('to').value = ymd(t);
            document.getElementById('filterForm').submit();
        });

        // ---- search tabel Sales by Business ----
        document.getElementById('sellerSearch').addEventListener('input', e => {
            const q = e.target.value.toLowerCase().trim();
            let shown = 0;
            document.querySelectorAll('.seller-row').forEach(r => {
                const ok = r.dataset.name.includes(q);
                r.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            document.getElementById('sellerEmpty').style.display = shown ? 'none' : '';
        });

        // ---- filter + edit inline kategori ----
        document.getElementById('catSearch').addEventListener('input', e => {
            const q = e.target.value.toLowerCase();
            document.querySelectorAll('.cat-row').forEach(r => r.style.display = r.dataset.name.includes(q) ? '' :
                'none');
        });
        document.querySelectorAll('.cat-row .edit').forEach(btn => btn.onclick = () => {
            const row = btn.closest('tr'),
                form = row.querySelector('.ce'),
                name = row.querySelector('.cn');
            const open = form.style.display === 'flex';
            form.style.display = open ? 'none' : 'flex';
            name.style.display = open ? '' : 'none';
            btn.textContent = open ? 'Edit' : 'Batal';
            if (!open) form.querySelector('input').focus();
        });
    </script>
@endsection