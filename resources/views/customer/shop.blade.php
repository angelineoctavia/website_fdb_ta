@extends('layouts.app')
@section('title', 'Shop - MADE BY PIDIZ')

@push('styles')
    <style>
        .shop-wrapper {
            display: flex;
            min-height: 80vh;
            background-color: #f9fafb;
        }

        .filter-sidebar {
            width: 280px;
            background-color: #1f2937;
            color: #ffffff;
            padding: 30px 20px;
            flex-shrink: 0;
        }

        .filter-sidebar h2 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        .filter-group {
            margin-bottom: 20px;
        }

        .filter-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .filter-select {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #4b5563;
            background-color: #374151;
            color: #ffffff;
            font-size: 14px;
        }

        .filter-checkbox-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 14px;
            color: #d1d5db;
        }

        .filter-checkbox-list label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: normal;
        }

        .btn-clear-filter {
            width: 100%;
            background-color: #dc2626;
            color: #ffffff;
            border: none;
            padding: 10px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
            display: block;
            text-align: center;
            text-decoration: none;
        }

        .btn-clear-filter:hover {
            background-color: #b91c1c;
        }

        .shop-content {
            flex-grow: 1;
            padding: 40px;
        }

        .products-grid-shop {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .product-card {
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
        }

        .product-card:hover {
            transform: translateY(-4px);
        }

        .product-img-box {
            width: 100%;
            aspect-ratio: 3 / 4;
            background-color: #e5e7eb;
            overflow: hidden;
        }

        .product-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-name {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .product-price {
            font-size: 14px;
            color: #4b5563;
            font-weight: 500;
        }

        @media (max-width: 1024px) {
            .products-grid-shop {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endpush

@section('content')
    <div class="shop-wrapper">
        <aside class="filter-sidebar">
            <h2>Filters</h2>
            <form action="{{ route('shop.index') }}" method="GET" id="filterForm">
                <div class="filter-group">
                    <label for="brand">Brands</label>
                    <select name="brand" id="brand" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Brands</option>
                        @foreach ($brands as $b)
                            <option value="{{ $b->USERS_ID }}" {{ request('brand') == $b->USERS_ID ? 'selected' : '' }}>
                                {{ $b->USERS_NAME }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>Gender</label>
                    <div class="filter-checkbox-list">
                        <label><input type="radio" name="gender" value="Women"
                                {{ request('gender') == 'Women' ? 'checked' : '' }} onchange="this.form.submit()">
                            Women</label>
                        <label><input type="radio" name="gender" value="Men"
                                {{ request('gender') == 'Men' ? 'checked' : '' }} onchange="this.form.submit()"> Men</label>
                        <label><input type="radio" name="gender" value="Unisex"
                                {{ request('gender') == 'Unisex' ? 'checked' : '' }} onchange="this.form.submit()">
                            Unisex</label>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Type</label>
                    <div class="filter-checkbox-list">
                        @foreach ($categories as $cat)
                            <label>
                                <input type="checkbox" name="type[]" value="{{ $cat->CATEGORIES_ID }}"
                                    {{ is_array(request('type')) && in_array($cat->CATEGORIES_ID, request('type')) ? 'checked' : '' }}
                                    onchange="this.form.submit()"> {{ $cat->CATEGORIES_NAME }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="filter-group">
                    <label>Price</label>
                    <div class="filter-checkbox-list">
                        <label><input type="radio" name="price" value="lt_200k"
                                {{ request('price') == 'lt_200k' ? 'checked' : '' }} onchange="this.form.submit()"> &lt; Rp
                            200.000</label>
                        <label><input type="radio" name="price" value="200k_500k"
                                {{ request('price') == '200k_500k' ? 'checked' : '' }} onchange="this.form.submit()"> Rp
                            200.000 - Rp 500.000</label>
                        <label><input type="radio" name="price" value="500k_1m"
                                {{ request('price') == '500k_1m' ? 'checked' : '' }} onchange="this.form.submit()"> Rp
                            500.000 - Rp 1.000.000</label>
                        <label><input type="radio" name="price" value="gt_1m"
                                {{ request('price') == 'gt_1m' ? 'checked' : '' }} onchange="this.form.submit()"> &gt; Rp
                            1.000.000</label>
                    </div>
                </div>

                <a href="{{ route('shop.index') }}" class="btn-clear-filter">Clear Filter</a>
            </form>
        </aside>

        <main class="shop-content">
            <div class="products-grid-shop">
                @forelse($products as $product)
                    <!-- Mengarah ke halaman detail produk dinamis berdasarkan ID -->
                    <a href="{{ route('shop.detail', $product->PRODUCTS_ID) }}" class="product-card">
                        <div class="product-img-box">
                            <img src="{{ asset('images/Mockup_Pics/' . ($product->product_image ?? 'Trascend.jpg')) }}"
                                alt="{{ $product->product_name }}">
                        </div>
                        <div class="product-info">
                            <div class="product-name">{{ $product->product_name }}</div>
                            <div class="product-price">Rp {{ number_format($product->product_price, 0, ',', '.') }}</div>
                        </div>
                    </a>
                @empty
                    <!-- Fallback Data Mockup Visual Sesuai Request[cite: 8, 9] -->
                    @php
                        $mockups = [
                            ['id' => 1, 'name' => 'Trascend', 'price' => 850000, 'img' => 'Trascend.jpg'],
                            ['id' => 2, 'name' => 'Denim', 'price' => 360000, 'img' => 'Denim.jpg'],
                            ['id' => 3, 'name' => 'Demi Couture', 'price' => 980000, 'img' => 'Demi_Couture.jpg'],
                            ['id' => 4, 'name' => 'ABC', 'price' => 340000, 'img' => 'ABC.jpg'],
                            ['id' => 5, 'name' => 'Wastra', 'price' => 480000, 'img' => 'Wastra.jpg'],
                            ['id' => 6, 'name' => 'Hourbound', 'price' => 300000, 'img' => 'Hourbound.jpg'],
                            ['id' => 7, 'name' => 'Spera', 'price' => 275000, 'img' => 'Spera.jpg'],
                            ['id' => 8, 'name' => 'Myth of Men', 'price' => 600000, 'img' => 'Myth_Of_Men.jpg'],
                        ];
                    @endphp

                    @foreach ($mockups as $m)
                        <a href="{{ route('shop.detail', $m['id']) }}" class="product-card">
                            <div class="product-img-box">
                                <img src="{{ asset('images/Mockup_Pics/' . $m['img']) }}" alt="{{ $m['name'] }}">
                            </div>
                            <div class="product-info">
                                <div class="product-name">{{ $m['name'] }}</div>
                                <div class="product-price">Rp {{ number_format($m['price'], 0, ',', '.') }}</div>
                            </div>
                        </a>
                    @endforeach
                @endforelse
            </div>
        </main>
    </div>
@endsection