@extends('layouts.seller')
@section('content')
    @include('seller.styles')
    <div class="sc-wrap">
        <div class="sc-grid4">
            <div class="sc-card"><small>Total Revenue</small>
                <h3>Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</h3>
            </div>
            <div class="sc-card"><small>Monthly Sales</small>
                <h3>Rp {{ number_format($stats['monthly'], 0, ',', '.') }}</h3>
            </div>
            <div class="sc-card"><small>Total Orders</small>
                <h3>{{ $stats['orders'] }} orders</h3>
            </div>
            <div class="sc-card"><small>Active Products</small>
                <h3>{{ $stats['products'] }} products</h3>
            </div>
        </div>

        <div class="sc-grid21">
            <div class="sc-card"><small>Monthly Revenue &amp; Sales Trend</small>
                <div style="height:260px"><canvas id="trend"></canvas></div>
            </div>
            <div class="sc-card"><small>Sales by Category</small>
                <div style="height:260px"><canvas id="cat"></canvas></div>
            </div>
        </div>

        <div class="sc-grid11">
            <div class="sc-card"><small>Recent Transactions</small>
                <table class="sc-table">
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
                                <td>Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                                <td><span
                                        class="sc-pill {{ $o->TRANSACTIONS_ORDER_STATUS }}">{{ ucfirst($o->TRANSACTIONS_ORDER_STATUS) }}</span>
                                </td>
                            </tr>
                        @empty<tr>
                                <td colspan="4">Belum ada transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="sc-card"><small>Top Selling Products</small>
                <table class="sc-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($top as $p)
                            <tr>
                                <td>{{ $p->name }}</td>
                                <td>{{ $p->qty }}</td>
                                <td>Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                            </tr>
                        @empty<tr>
                                <td colspan="3">Belum ada penjualan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
    <script>
        new Chart(document.getElementById('trend'), {
            type: 'line',
            data: {
                labels: @json($trend->pluck('ym')),
                datasets: [{
                        label: 'Revenue (Rp)',
                        data: @json($trend->pluck('revenue')),
                        borderColor: '#c8104a',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Qty sold',
                        data: @json($trend->pluck('qty')),
                        borderColor: '#f0508c',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y1: {
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
        new Chart(document.getElementById('cat'), {
            type: 'pie',
            data: {
                labels: @json($byCategory->pluck('name')),
                datasets: [{
                    data: @json($byCategory->pluck('qty')),
                    backgroundColor: ['#c8104a', '#f0508c', '#ff9ab8', '#7a0b2e', '#e6a1b8', '#444']
                }]
            },
            options: {
                maintainAspectRatio: false
            }
        });
    </script>
@endsection