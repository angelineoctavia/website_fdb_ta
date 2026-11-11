@extends('layouts.app')

@section('title', 'Shop')

@push('styles')
    @include('components.ui_styles')
    <style>
        .shop-layout {
            display: flex;
            align-items: stretch;
            min-height: 560px
        }

        .filter-panel {
            width: 230px;
            flex-shrink: 0;
            background: var(--dark);
            color: #fff;
            padding: 26px 22px
        }

        .filter-panel h2 {
            font-size: 15px;
            letter-spacing: .5px;
            margin-bottom: 18px
        }

        .filter-group {
            margin-bottom: 18px
        }

        .filter-title {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px
        }

        .filter-select {
            width: 100%;
            padding: 6px 8px;
            border-radius: 4px;
            border: 0;
            background: #d9d9d9;
            font-size: 12px
        }

        .filter-check {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            margin-bottom: 6px;
            cursor: pointer
        }

        .filter-check input {
            accent-color: var(--brand)
        }

        .shop-content {
            flex: 1;
            padding: 28px 36px;
            background: #fafafa
        }

        /* ===== HEADER PROFIL SELLER (full-width, gaya Shopee) ===== */
        .seller-head {
            display: flex;
            gap: 22px;
            align-items: center;
            background: #fff;
            /* tembus padding .shop-content supaya melebar penuh */
            margin: -28px -36px 0;
            padding: 28px 36px 22px
        }

        .seller-avatar {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: #d9d9d9;
            object-fit: cover;
            flex-shrink: 0;
            border: 3px solid #f1f1f1
        }

        .seller-meta h1 {
            font-size: 20px;
            font-weight: 600
        }

        .seller-handle {
            color: var(--muted);
            font-size: 12px;
            margin: 2px 0 8px
        }

        .seller-bio {
            font-size: 13px;
            line-height: 1.6;
            color: #444;
            white-space: pre-line;
            max-width: 640px
        }

        .seller-head .seller-clear {
            margin-left: auto;
            align-self: flex-start;
            color: var(--brand);
            font-size: 12px;
            font-weight: 600
        }

        /* tab "Products" di bawah header */
        .seller-tabs {
            background: #fff;
            margin: 0 -36px 24px;
            padding: 0 36px;
            border-bottom: 1px solid #eee
        }

        .seller-tab {
            display: inline-block;
            padding: 12px 4px;
            font-size: 13px;
            font-weight: 600;
            color: var(--brand);
            border-bottom: 2px solid var(--brand);
            margin-bottom: -1px
        }

        .search-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            margin-bottom: 16px
        }

        .search-info a {
            color: var(--brand);
            font-weight: 600
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px
        }

        .product-card {
            background: #fff;
            box-shadow: var(--shadow-card);
            border-radius: 4px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform .15s;
        }

        .product-card:hover {
            transform: translateY(-3px)
        }

        .product-thumb {
            position: relative;
            aspect-ratio: 3 / 4;
            background: #e6e6e6;
            overflow: hidden
        }

        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .product-thumb.soldout img {
            filter: grayscale(.6);
            opacity: .75
        }

        .badge-soldout {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #333;
            color: #fff;
            border-radius: 99px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 600
        }

        .product-info {
            padding: 8px 10px 10px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: 11px
        }

        .product-name {
            font-weight: 500
        }

        .product-price {
            color: var(--muted)
        }

        .pager {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 18px;
            margin-top: 28px;
            font-size: 13px
        }

        .pager a {
            color: var(--brand);
            font-weight: 600
        }

        .pager .disabled {
            color: #bbb
        }

        @media (max-width: 1100px) {
            .product-grid {
                grid-template-columns: repeat(3, 1fr)
            }
        }

        @media (max-width: 900px) {
            .shop-layout {
                flex-direction: column
            }

            .filter-panel {
                width: 100%
            }

            .product-grid {
                grid-template-columns: repeat(2, 1fr)
            }

            .shop-content {
                padding: 18px
            }

            .seller-head {
                margin: -18px -18px 0;
                padding: 20px 18px 18px
            }

            .seller-tabs {
                margin: 0 -18px 18px;
                padding: 0 18px
            }
        }

        @media (max-width: 600px) {
            .seller-head {
                flex-direction: column;
                text-align: center
            }

            .seller-head .seller-clear {
                margin: 0;
                align-self: center
            }
        }
    </style>
@endpush

@section('content')
    <div class="shop-layout">

        {{-- ===== SIDEBAR FILTER ===== --}}
        <aside class="filter-panel">
            <h2>FILTERS</h2>

            <form method="GET" action="{{ route('shop.index') }}" id="filter-form">
                @if ($q !== '')
                    <input type="hidden" name="q" value="{{ $q }}">
                @endif

                <div class="filter-group">
                    <label class="filter-title" for="brand">Brands</label>
                    <select name="brand" id="brand" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Brands</option>
                        @foreach ($brands as $b)
                            <option value="{{ $b->USERS_ID }}" @selected($selected['brand'] == $b->USERS_ID)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <span class="filter-title">Gender</span>
                    @foreach ($genderOptions as $value => $label)
                        <label class="filter-check">
                            <input type="checkbox" name="gender[]" value="{{ $value }}" @checked(in_array($value, $selected['gender']))
                                onchange="this.form.submit()">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                <div class="filter-group">
                    <span class="filter-title">Type</span>
                    @foreach ($categoryList as $c)
                        <label class="filter-check">
                            <input type="checkbox" name="type[]" value="{{ $c->CATEGORIES_ID }}"
                                @checked(in_array($c->CATEGORIES_ID, $selected['type'])) onchange="this.form.submit()">
                            {{ $c->name }}
                        </label>
                    @endforeach
                </div>

                <div class="filter-group">
                    <span class="filter-title">Price</span>
                    @foreach ($priceOptions as $value => $label)
                        <label class="filter-check">
                            <input type="checkbox" name="price[]" value="{{ $value }}" @checked(in_array($value, $selected['price']))
                                onchange="this.form.submit()">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                <a href="{{ route('shop.index') }}" class="btn btn-red btn-block">Clear Filter</a>
            </form>
        </aside>

        {{-- ===== PROFIL SELLER + GRID PRODUK ===== --}}
        <section class="shop-content">

            @if (!empty($seller))
                {{-- header seller dibuat paling atas & melebar penuh, jadi flash ditaruh di bawahnya --}}
                <div class="seller-head">
                    @if ($seller['picture'])
                        <img class="seller-avatar" src="{{ $seller['picture'] }}" alt="{{ $seller['name'] }}">
                    @else
                        <div class="seller-avatar"></div>
                    @endif

                    <div class="seller-meta">
                        <h1>{{ $seller['name'] }}</h1>
                        <p class="seller-handle">{{ $seller['handle'] }}</p>
                        @if ($seller['bio'])
                            <p class="seller-bio">{{ $seller['bio'] }}</p>
                        @endif
                    </div>

                    <a href="{{ route('shop.index') }}" class="seller-clear">See all brands</a>
                </div>

                <div class="seller-tabs">
                    <span class="seller-tab">Products ({{ $seller['count'] }})</span>
                </div>
            @endif

            @include('components.flash')

            @if ($q !== '')
                <div class="search-info">
                    <span>Search results for <strong>"{{ $q }}"</strong> ({{ $products->total() }}
                        products)</span>
                    <a href="{{ route('shop.index') }}">Clear search</a>
                </div>
            @endif

            @if ($products->isEmpty())
                <div class="empty-state">
                    <p>No matching products.</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-red">Reset</a>
                </div>
            @else
                <div class="product-grid">
                    @foreach ($products as $p)
                        {{-- is_preorder = accessor di model Product (PRODUCTS_READY_TYPE) --}}
                        @php $soldOut = !$p->is_preorder && (int) $p->PRODUCTS_STOCK <= 0; @endphp
                        <a href="{{ route('shop.show', $p->PRODUCTS_ID) }}" class="product-card">
                            <div class="product-thumb {{ $soldOut ? 'soldout' : '' }}">
                                @if ($p->cover_url)
                                    <img src="{{ $p->cover_url }}" alt="{{ $p->PRODUCTS_NAME }}" loading="lazy">
                                @else
                                    <div class="img-placeholder">No Image</div>
                                @endif

                                @if ($p->is_preorder)
                                    <span class="badge-preorder">Pre-Order</span>
                                @elseif ($soldOut)
                                    <span class="badge-soldout">Sold Out</span>
                                @endif
                            </div>
                            <div class="product-info">
                                <span class="product-name">{{ $p->PRODUCTS_NAME }}</span>
                                <span class="product-price">{{ $p->formatted_price }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($products->hasPages())
                    <div class="pager">
                        @if ($products->onFirstPage())
                            <span class="disabled">&laquo; Prev</span>
                        @else
                            <a href="{{ $products->previousPageUrl() }}">&laquo; Prev</a>
                        @endif

                        <span>{{ $products->currentPage() }} / {{ $products->lastPage() }}</span>

                        @if ($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}">Next &raquo;</a>
                        @else
                            <span class="disabled">Next &raquo;</span>
                        @endif
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection