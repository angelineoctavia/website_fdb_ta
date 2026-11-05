@extends('layouts.app')

@section('title', $product->PRODUCTS_NAME)

@push('styles')
    @include('components.ui_styles')
    <style>
        .detail-page {
            padding: 24px 40px 48px;
            background: #f5f5f5
        }

        .back-link {
            display: inline-block;
            font-size: 13px;
            margin-bottom: 14px
        }

        .back-link:hover {
            color: var(--brand)
        }

        .detail-layout {
            display: flex;
            gap: 28px;
            align-items: flex-start
        }

        .detail-card {
            flex: 1;
            display: flex;
            gap: 28px;
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow-card);
            padding: 26px;
        }

        .detail-gallery {
            width: 300px;
            flex-shrink: 0
        }

        .gallery-main {
            aspect-ratio: 3 / 4;
            background: #d9d9d9;
            border-radius: 6px;
            overflow: hidden
        }

        .gallery-main img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .gallery-thumbs {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            flex-wrap: wrap
        }

        .thumb {
            width: 48px;
            height: 64px;
            border: 2px solid transparent;
            background: #d9d9d9;
            border-radius: 4px;
            overflow: hidden;
            cursor: pointer;
            padding: 0
        }

        .thumb.active,
        .thumb:hover {
            border-color: var(--brand)
        }

        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .detail-info {
            flex: 1;
            font-size: 13px
        }

        .detail-info h1 {
            font-size: 20px;
            font-weight: 600
        }

        .detail-price {
            margin: 6px 0;
            font-size: 15px
        }

        .detail-meta {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 14px
        }

        .detail-block {
            margin-top: 16px
        }

        .detail-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px
        }

        .size-options {
            display: flex;
            gap: 8px;
            flex-wrap: wrap
        }

        .size-btn {
            min-width: 38px;
            height: 34px;
            padding: 0 10px;
            border: 1px solid #999;
            background: #fff;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer
        }

        .size-btn.selected,
        .size-btn:hover {
            background: var(--dark);
            color: #fff;
            border-color: var(--dark)
        }

        .detail-desc {
            line-height: 1.6;
            color: #444;
            margin-bottom: 6px;
            white-space: pre-line
        }

        .detail-stock {
            margin-top: 14px;
            font-size: 12px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap
        }

        .badge-ready {
            display: inline-block;
            background: #e7f6ec;
            color: #1b7a3a;
            border-radius: 99px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 600
        }

        .detail-note {
            margin-top: 10px;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.5
        }

        .detail-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 18px
        }

        .detail-actions form {
            margin: 0
        }

        .detail-actions .btn[disabled] {
            opacity: .45;
            cursor: not-allowed
        }

        .designer-card {
            width: 330px;
            flex-shrink: 0;
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow-card);
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }

        .designer-card h3 {
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 14px
        }

        .designer-avatar {
            display: block;
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: #d9d9d9;
            margin: 0 auto 10px;
            object-fit: cover
        }

        .designer-name {
            font-weight: 600
        }

        .designer-handle {
            color: var(--muted);
            margin-bottom: 14px
        }

        .designer-buttons {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            margin-bottom: 14px
        }

        .designer-buttons .btn {
            padding: 8px 6px;
            font-size: 12px
        }

        .designer-bio {
            text-align: left;
            line-height: 1.6;
            color: #444;
            white-space: pre-line;
            margin-bottom: 18px
        }

        .size-chart table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px
        }

        .size-chart th {
            background: var(--brand);
            color: #fff;
            padding: 6px 4px;
            font-weight: 600
        }

        .size-chart td {
            padding: 6px 4px;
            border-bottom: 1px solid #eee
        }

        .size-note {
            text-align: left;
            font-size: 10px;
            color: var(--muted);
            margin-top: 6px
        }

        .size-text {
            text-align: left;
            white-space: pre-line;
            line-height: 1.6
        }

        @media (max-width: 900px) {

            .detail-layout,
            .detail-card {
                flex-direction: column
            }

            .designer-card,
            .detail-gallery {
                width: 100%
            }

            .detail-page {
                padding: 18px
            }
        }
    </style>
@endpush

@section('content')
    @php
        // Pre-Order / Ready dibaca dari products.PRODUCTS_READY_TYPE lewat accessor model Product
        // (satu sumber yang sama dengan halaman shop & cart).
        $isPreorder = $product->is_preorder;
        $stock = (int) $product->PRODUCTS_STOCK;
        $loggedIn = session()->has('user_id');
        $isCustomer = session('user_role') === 'C';

        // Size chart format baru: {"columns":[...],"rows":[{"size":"S","values":[...]}]}
        $chart = json_decode($product->PRODUCTS_SIZE_CHART ?? '', true);
        $newChart = is_array($chart) && !empty($chart['rows']) && isset($chart['columns']);
        $sizes = $newChart ? collect($chart['rows'])->pluck('size')->filter()->values()->all() : $product->sizes;
    @endphp

    <div class="detail-page">
        <a href="{{ route('shop.index') }}" class="back-link">&lsaquo; Shop</a>

        @include('components.flash')

        <div class="detail-layout">

            {{-- ===== KARTU PRODUK ===== --}}
            <div class="detail-card">
                <div class="detail-gallery">
                    <div class="gallery-main">
                        @if ($product->cover_url)
                            <img id="main-image" src="{{ $product->cover_url }}" alt="{{ $product->PRODUCTS_NAME }}">
                        @else
                            <div class="img-placeholder">No Image</div>
                        @endif
                    </div>

                    @if ($product->images->count() > 1)
                        <div class="gallery-thumbs">
                            @foreach ($product->images as $img)
                                <button type="button" class="thumb" data-src="{{ $img->src }}">
                                    <img src="{{ $img->src }}" alt="">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="detail-info">
                    <h1>{{ $product->PRODUCTS_NAME }}</h1>
                    <p class="detail-price">{{ $product->formatted_price }}</p>

                    @if ($categoryName || $product->PRODUCTS_GENDER)
                        <p class="detail-meta">
                            {{ $categoryName }}@if ($categoryName && $product->PRODUCTS_GENDER)
                                &middot;
                            @endif{{ $product->PRODUCTS_GENDER }}
                        </p>
                    @endif

                    <div class="detail-block">
                        <span class="detail-label">Size</span>
                        <div class="size-options">
                            @foreach ($sizes as $i => $size)
                                <button type="button" class="size-btn {{ $i === 0 ? 'selected' : '' }}"
                                    data-size="{{ $size }}">{{ $size }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="detail-block">
                        <span class="detail-label">Product Description</span>
                        <p class="detail-desc">{{ $product->PRODUCTS_DESCRIPTION ?: 'No description yet.' }}</p>
                        @if ($product->PRODUCTS_MATERIAL)
                            <p class="detail-desc"><strong>Material:</strong> {{ $product->PRODUCTS_MATERIAL }}</p>
                        @endif
                    </div>

                    <p class="detail-stock">
                        @if ($isPreorder)
                            <span class="badge-preorder static">Pre-Order</span>
                            @if ($product->PRODUCTS_PREORDER_DURATION)
                                <span>Estimated production: {{ $product->PRODUCTS_PREORDER_DURATION }} days</span>
                            @endif
                        @else
                            <span class="badge-ready">Ready Stock</span>
                            @if ($stock > 0)
                                <span>Stock: {{ $stock }}</span>
                            @else
                                <span class="text-red">Out of stock</span>
                            @endif
                        @endif
                    </p>

                    <div class="detail-actions">
                        @if ($loggedIn && $isCustomer)
                            @if ($isPreorder)
                                {{-- Pre-Order: tidak bisa masuk keranjang, hanya wishlist --}}
                                <button type="button" class="btn btn-dark" disabled>Pre-Order</button>
                            @else
                                <form method="POST" action="{{ route('cart.store') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->PRODUCTS_ID }}">
                                    <input type="hidden" name="size" id="selected-size" value="{{ $sizes[0] ?? '' }}">
                                    <button type="submit" class="btn btn-dark" @disabled($stock <= 0)>
                                        {{ $stock <= 0 ? 'Out of Stock' : 'Add To Cart' }}
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('wishlist.toggle') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->PRODUCTS_ID }}">
                                <button type="submit" class="btn-icon {{ $inWishlist ? 'on' : '' }}" aria-label="Wishlist"
                                    title="{{ $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}">
                                    <i class="{{ $inWishlist ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                                </button>
                            </form>
                        @elseif (!$loggedIn)
                            @unless ($isPreorder)
                                <a href="{{ route('login') }}" class="btn btn-dark">Login to Add To Cart</a>
                            @endunless
                            <a href="{{ route('login') }}" class="btn-icon" aria-label="Wishlist"><i
                                    class="fa-regular fa-heart"></i></a>
                        @endif
                        {{-- seller / admin: tidak ada tombol belanja --}}
                    </div>

                    @if ($isPreorder)
                        <p class="detail-note">Pre-Order products can't be added to the cart. Save it to your wishlist or
                            contact the designer via WhatsApp.</p>
                    @endif
                </div>
            </div>

            {{-- ===== PROFIL DESIGNER + SIZE CHART ===== --}}
            <aside class="designer-card">
                <h3>Designer Profile</h3>

                @if (!empty($sellerPicture))
                    <img class="designer-avatar" src="{{ $sellerPicture }}" alt="{{ $sellerName }}">
                @else
                    <div class="designer-avatar"></div>
                @endif
                <p class="designer-name">{{ $sellerName }}</p>
                <p class="designer-handle">
                    {{ '@' . (!empty($sellerInstagram) ? ltrim($sellerInstagram, '@') : \Illuminate\Support\Str::slug($sellerName, '')) }}
                </p>

                <div class="designer-buttons">
                    @if ($waLink)
                        <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-wa">WhatsApp</a>
                    @else
                        <span class="btn btn-wa disabled">WhatsApp</span>
                    @endif
                </div>

                @if (!empty($sellerBio))
                    <p class="designer-bio">{{ $sellerBio }}</p>
                @endif

                <div class="size-chart">
                    @if ($newChart)
                        <table>
                            <thead>
                                <tr>
                                    <th>Size</th>
                                    @foreach ($chart['columns'] as $col)
                                        <th>{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($chart['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['size'] ?? '-' }}</td>
                                        @foreach ($chart['columns'] as $j => $col)
                                            <td>{{ ($row['values'][$j] ?? '') !== '' ? $row['values'][$j] : '-' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="size-note">*All measurements in cm</p>
                    @elseif ($product->size_rows)
                        <table>
                            <thead>
                                <tr>
                                    <th>Size</th>
                                    <th>Bust (cm)</th>
                                    <th>Length (cm)</th>
                                    <th>Shoulder (cm)</th>
                                    <th>Sleeve (cm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($product->size_rows as $row)
                                    <tr>
                                        <td>{{ $row['size'] ?? '-' }}</td>
                                        <td>{{ $row['bust'] ?? '-' }}</td>
                                        <td>{{ $row['length'] ?? '-' }}</td>
                                        <td>{{ $row['shoulder'] ?? '-' }}</td>
                                        <td>{{ $row['sleeve'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @elseif ($product->size_text)
                        <p class="size-text">{{ $product->size_text }}</p>
                    @else
                        <p class="size-text muted">Size chart not available.</p>
                    @endif
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.thumb').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('main-image').src = btn.dataset.src;
                document.querySelectorAll('.thumb').forEach(function(t) {
                    t.classList.remove('active');
                });
                btn.classList.add('active');
            });
        });

        document.querySelectorAll('.size-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.size-btn').forEach(function(b) {
                    b.classList.remove('selected');
                });
                btn.classList.add('selected');
                var hidden = document.getElementById('selected-size');
                if (hidden) hidden.value = btn.dataset.size;
            });
        });
    </script>
@endpush