@extends('layouts.seller')
@section('title', 'My Products')
@section('content')
    @include('seller.styles')
    <style>
        .pr-tabs {
            display: flex;
            gap: 6px;
            flex-wrap: wrap
        }

        .pr-tab {
            padding: 6px 12px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 500;
            color: #4b5563;
            background: #e9ebee;
            text-decoration: none
        }

        .pr-tab.on {
            background: #b8004c;
            color: #fff
        }

        .pr-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap
        }

        .pr-pill.ready {
            background: #e7f6ec;
            color: #1b7a3a
        }

        .pr-pill.po {
            background: #fce7f3;
            color: #9d174d
        }

        .pr-pill.active {
            background: #e7f6ec;
            color: #1b7a3a
        }

        .pr-pill.inactive {
            background: #e5e7eb;
            color: #4b5563
        }

        .pr-actions {
            display: flex;
            gap: 6px;
            align-items: center
        }

        .pr-actions form {
            margin: 0
        }

        .pr-btn {
            border: 0;
            cursor: pointer;
            color: #fff;
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block
        }

        .pr-btn.edit {
            background: #333
        }

        .pr-btn.edit:hover {
            background: #000
        }

        .pr-btn.off {
            background: #6b7280
        }

        .pr-btn.off:hover {
            background: #4b5563
        }

        .pr-btn.on {
            background: #16a34a
        }

        .pr-btn.on:hover {
            background: #15803d
        }

        .pr-row-inactive td {
            opacity: .55
        }

        .pr-row-inactive td:last-child {
            opacity: 1
        }
    </style>

    <div class="sc-wrap">
        @include('components.flash')

        <div
            style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px">
            <h3 style="margin:0">My Products</h3>
            <a class="sc-btn-dark" href="{{ route('seller.upload') }}">+ Add Product</a>
        </div>

        <div
            style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px">
            <div class="pr-tabs">
                <a class="pr-tab {{ $status === 'all' ? 'on' : '' }}"
                    href="{{ route('seller.products', array_filter(['q' => $q])) }}">All ({{ $counts->total }})</a>
                <a class="pr-tab {{ $status === 'active' ? 'on' : '' }}"
                    href="{{ route('seller.products', array_filter(['status' => 'active', 'q' => $q])) }}">Active
                    ({{ $counts->active }})</a>
                <a class="pr-tab {{ $status === 'inactive' ? 'on' : '' }}"
                    href="{{ route('seller.products', array_filter(['status' => 'inactive', 'q' => $q])) }}">Inactive
                    ({{ $counts->inactive }})</a>
            </div>
            <form method="GET" style="display:flex;gap:8px">
                @if ($status !== 'all')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <input class="sc-input" style="width:240px" name="q" value="{{ $q }}"
                    placeholder="Search product name...">
                <button class="sc-btn">Search</button>
            </form>
        </div>

        <div class="sc-card" style="padding:0;overflow:hidden">
            <table class="sc-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $p)
                        {{-- READY_TYPE: 1 = Ready Stock, 2 = Pre-Order --}}
                        @php
                            $inactive = (int) $p->PRODUCTS_STATUS_DEL === 1;
                            $isPo = (int) $p->PRODUCTS_READY_TYPE === 2;
                        @endphp
                        <tr class="{{ $inactive ? 'pr-row-inactive' : '' }}">
                            <td><b>{{ $p->PRODUCTS_NAME }}</b></td>
                            <td>Rp {{ number_format($p->PRODUCTS_PRICE, 0, ',', '.') }}</td>
                            <td>{{ $isPo ? '–' : $p->PRODUCTS_STOCK . ' pcs' }}</td>
                            <td>
                                @if ($isPo)
                                    <span class="pr-pill po">Pre-Order
                                        @if ($p->PRODUCTS_PREORDER_DURATION)
                                            &middot; {{ $p->PRODUCTS_PREORDER_DURATION }} days
                                        @endif
                                    </span>
                                @else
                                    <span class="pr-pill ready">Ready Stock</span>
                                @endif
                            </td>
                            <td>
                                <span class="pr-pill {{ $inactive ? 'inactive' : 'active' }}">
                                    {{ $inactive ? 'Inactive' : 'Active' }}
                                </span>
                            </td>
                            <td>
                                <div class="pr-actions">
                                    <a href="{{ route('seller.products.edit', $p->PRODUCTS_ID) }}"
                                        class="pr-btn edit">Edit</a>
                                    <form method="POST" action="{{ route('seller.products.toggle', $p->PRODUCTS_ID) }}"
                                        @if (!$inactive) onsubmit="return confirm('Inactivate this product? It will be hidden from the shop and you can restore it anytime.')" @endif>
                                        @csrf
                                        @method('PATCH')
                                        @if ($inactive)
                                            <button type="submit" class="pr-btn on">Restore</button>
                                        @else
                                            <button type="submit" class="pr-btn off">Inactivate</button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:24px">No products found. Start adding your
                                products!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:12px">{{ $products->links() }}</div>
    </div>
@endsection