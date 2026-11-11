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
            position: relative;
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

        /* panah < > di galeri */
        .gal-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 0;
            background: rgba(0, 0, 0, .35);
            color: #fff;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s
        }

        .gal-btn:hover {
            background: rgba(0, 0, 0, .55)
        }

        .gal-prev {
            left: 8px
        }

        .gal-next {
            right: 8px
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

        /* ===== PANEL UKURAN CUSTOM ===== */
        .custom-panel {
            display: none;
            margin-top: 12px;
            padding: 14px;
            border: 1px dashed #bbb;
            border-radius: 10px;
            background: #fafafa
        }

        .custom-panel.open {
            display: block
        }

        .custom-title {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px
        }

        .custom-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px 14px
        }

        .custom-field label {
            display: block;
            font-size: 11px;
            color: var(--muted);
            margin-bottom: 3px
        }

        .custom-field input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 12px;
            background: #fff;
            outline: none
        }

        .custom-field input:focus {
            border-color: var(--brand)
        }

        .custom-wa {
            display: inline-block;
            margin-top: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #1b9c4a
        }

        .custom-hint {
            font-size: 11px;
            color: var(--muted);
            margin-top: 8px
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

        .add-form {
            display: flex;
            gap: 10px;
            align-items: center
        }

        .detail-actions .btn[disabled] {
            opacity: .45;
            cursor: not-allowed
        }

        /* stepper - 1 + */
        .qty-box {
            display: inline-flex;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
            background: #fff
        }

        .qty-btn {
            width: 32px;
            height: 38px;
            border: 0;
            background: #fff;
            font-size: 16px;
            cursor: pointer
        }

        .qty-btn:hover {
            background: #f0f0f0
        }

        .qty-box input {
            width: 40px;
            height: 38px;
            border: 0;
            border-left: 1px solid #eee;
            border-right: 1px solid #eee;
            text-align: center;
            font-size: 13px;
            background: #fff;
            -moz-appearance: textfield
        }

        .qty-box input::-webkit-outer-spin-button,
        .qty-box input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0
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

        /* foto + nama bisa diklik -> halaman seller di Shop */
        .designer-link {
            display: block;
            color: inherit
        }

        .designer-link:hover .designer-name {
            color: var(--brand)
        }

        .designer-visit {
            display: inline-block;
            font-size: 11px;
            color: var(--brand);
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
            margin-bottom: 6px
        }

        .designer-buttons {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            margin-bottom: 18px
        }

        .designer-buttons .btn {
            padding: 8px 6px;
            font-size: 12px
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
        $isPreorder = $product->is_preorder;
        $stock = (int) $product->PRODUCTS_STOCK;
        $loggedIn = session()->has('user_id');
        $isCustomer = session('user_role') === 'C';
        $shopUrl = route('shop.index', ['brand' => $product->USERS_ID]);

        // Size chart format baru: {"columns":[...],"rows":[{"size":"S","values":[...]}]}
        $chart = json_decode($product->PRODUCTS_SIZE_CHART ?? '', true);
        $newChart = is_array($chart) && !empty($chart['rows']) && isset($chart['columns']);
        $sizes = $newChart ? collect($chart['rows'])->pluck('size')->filter()->values()->all() : $product->sizes;

        // Kolom ukuran custom: ikut kolom size chart seller, kalau tidak ada pakai default
        $customFields = $newChart ? $chart['columns'] : ['Bust', 'Length', 'Shoulder', 'Sleeve'];

        // Batas jumlah: ready stock = sisa stok, pre-order = bebas (maks 99)
        $maxQty = $isPreorder ? 99 : max(1, $stock);

        // Link WhatsApp tanpa teks bawaan (teks custom dirakit di JS)
        $waBase = $waLink ? strtok($waLink, '?') : null;
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

                        @if ($product->images->count() > 1)
                            <button type="button" class="gal-btn gal-prev" id="gal-prev" aria-label="Previous image">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button type="button" class="gal-btn gal-next" id="gal-next" aria-label="Next image">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
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

                            @if ($isPreorder)
                                <button type="button" class="size-btn" id="custom-btn" data-size="Custom">Custom</button>
                            @endif
                        </div>

                        @if ($isPreorder)
                            <div class="custom-panel" id="custom-panel">
                                <p class="custom-title">Custom size (cm)</p>
                                <div class="custom-grid">
                                    @foreach ($customFields as $field)
                                        <div class="custom-field">
                                            <label>{{ $field }}</label>
                                            {{-- hanya angka (dan satu titik desimal) --}}
                                            <input type="text" inputmode="decimal" class="custom-input"
                                                data-label="{{ $field }}" placeholder="cm" maxlength="6"
                                                autocomplete="off">
                                        </div>
                                    @endforeach
                                </div>
                                <p class="custom-hint">Isi semua ukuran dengan angka, lalu Add To Cart. Atau diskusikan
                                    dulu dengan designer.</p>
                                @if ($waBase)
                                    <a href="#" id="custom-wa" class="custom-wa" target="_blank" rel="noopener">
                                        <i class="fa-brands fa-whatsapp"></i> Continue via WhatsApp
                                    </a>
                                @endif
                            </div>
                        @endif
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
                            <form method="POST" action="{{ route('cart.store') }}" class="add-form" id="add-form">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->PRODUCTS_ID }}">
                                <input type="hidden" name="size" id="selected-size" value="{{ $sizes[0] ?? '' }}">
                                <input type="hidden" name="custom_notes" id="custom-notes" value="">

                                @if ($isPreorder || $stock > 0)
                                    <div class="qty-box">
                                        <button type="button" class="qty-btn" data-d="-1"
                                            aria-label="Decrease">&minus;</button>
                                        <input type="number" name="qty" id="qty" value="1" min="1"
                                            max="{{ $maxQty }}" readonly>
                                        <button type="button" class="qty-btn" data-d="1"
                                            aria-label="Increase">+</button>
                                    </div>
                                @endif

                                <button type="submit" class="btn btn-dark" @disabled(!$isPreorder && $stock <= 0)>
                                    {{ !$isPreorder && $stock <= 0 ? 'Out of Stock' : 'Add To Cart' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('wishlist.toggle') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->PRODUCTS_ID }}">
                                <button type="submit" class="btn-icon {{ $inWishlist ? 'on' : '' }}"
                                    aria-label="Wishlist"
                                    title="{{ $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}">
                                    <i class="{{ $inWishlist ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                                </button>
                            </form>
                        @elseif (!$loggedIn)
                            <a href="{{ route('login') }}" class="btn btn-dark">Login to Add To Cart</a>
                            <a href="{{ route('login') }}" class="btn-icon" aria-label="Wishlist"><i
                                    class="fa-regular fa-heart"></i></a>
                        @endif
                        {{-- seller / admin: tidak ada tombol belanja --}}
                    </div>

                    @if ($isPreorder)
                        <p class="detail-note">This is a Pre-Order item. It will be produced after you order, so please
                            allow the estimated production time before it ships. Need a different fit? Choose
                            <strong>Custom</strong> and enter your measurements.
                        </p>
                    @endif
                </div>
            </div>

            {{-- ===== PROFIL DESIGNER + SIZE CHART ===== --}}
            <aside class="designer-card">
                <h3>Designer Profile</h3>

                {{-- klik foto / nama -> halaman seller (Shop terfilter + bio) --}}
                <a href="{{ $shopUrl }}" class="designer-link" title="View all products from {{ $sellerName }}">
                    @if (!empty($sellerPicture))
                        <img class="designer-avatar" src="{{ $sellerPicture }}" alt="{{ $sellerName }}">
                    @else
                        <div class="designer-avatar"></div>
                    @endif
                    <p class="designer-name">{{ $sellerName }}</p>
                    <p class="designer-handle">
                        {{ '@' . (!empty($sellerInstagram) ? ltrim($sellerInstagram, '@') : \Illuminate\Support\Str::slug($sellerName, '')) }}
                    </p>
                </a>
                <a href="{{ $shopUrl }}" class="designer-visit">Visit shop &rsaquo;</a>

                <div class="designer-buttons">
                    @if ($waLink)
                        <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-wa">WhatsApp</a>
                    @else
                        <span class="btn btn-wa disabled">WhatsApp</span>
                    @endif
                </div>

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
        (function() {
            /* ===== GALERI: thumbnail + panah < > ===== */
            var main = document.getElementById('main-image');
            var thumbs = Array.prototype.slice.call(document.querySelectorAll('.thumb'));
            var cur = 0;

            function showImage(i) {
                if (!thumbs.length || !main) return;
                cur = (i + thumbs.length) % thumbs.length;
                main.src = thumbs[cur].dataset.src;
                thumbs.forEach(function(t) {
                    t.classList.remove('active');
                });
                thumbs[cur].classList.add('active');
            }

            thumbs.forEach(function(btn, i) {
                btn.addEventListener('click', function() {
                    showImage(i);
                });
                if (main && btn.dataset.src === main.src) {
                    cur = i;
                    btn.classList.add('active');
                }
            });

            var prev = document.getElementById('gal-prev');
            var next = document.getElementById('gal-next');
            if (prev) prev.addEventListener('click', function() {
                showImage(cur - 1);
            });
            if (next) next.addEventListener('click', function() {
                showImage(cur + 1);
            });

            /* ===== PILIH UKURAN + CUSTOM ===== */
            var sizeBtns = document.querySelectorAll('.size-btn');
            var hiddenSize = document.getElementById('selected-size');
            var panel = document.getElementById('custom-panel');
            var inputs = Array.prototype.slice.call(document.querySelectorAll('.custom-input'));
            var isCustom = false;

            // Input custom: hanya angka dan satu titik desimal (koma otomatis jadi titik)
            inputs.forEach(function(inp) {
                inp.addEventListener('input', function() {
                    var v = inp.value.replace(/,/g, '.').replace(/[^0-9.]/g, '');
                    var p = v.split('.');
                    if (p.length > 2) v = p[0] + '.' + p.slice(1).join('');
                    inp.value = v;
                });
            });

            sizeBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    sizeBtns.forEach(function(b) {
                        b.classList.remove('selected');
                    });
                    btn.classList.add('selected');

                    isCustom = btn.dataset.size === 'Custom';
                    if (panel) panel.classList.toggle('open', isCustom);
                    if (hiddenSize) hiddenSize.value = btn.dataset.size;
                });
            });

            function customText() {
                return inputs.map(function(i) {
                    return i.dataset.label + ': ' + i.value.trim() + ' cm';
                }).join(', ');
            }

            // Continue via WhatsApp: rakit pesan dari ukuran yang sudah diisi
            var wa = document.getElementById('custom-wa');
            if (wa) {
                wa.addEventListener('click', function() {
                    var filled = inputs.filter(function(i) {
                        return i.value.trim() !== '';
                    });
                    var msg =
                        'Halo, saya tertarik dengan produk {{ addslashes($product->PRODUCTS_NAME) }} (Pre-Order) dan ingin memesan dengan ukuran custom.';
                    if (filled.length) {
                        msg += '\nUkuran saya: ' + filled.map(function(i) {
                            return i.dataset.label + ' ' + i.value.trim() + ' cm';
                        }).join(', ') + '.';
                    }
                    wa.href = "{{ $waBase }}?text=" + encodeURIComponent(msg);
                });
            }

            /* ===== STEPPER - 1 + ===== */
            var qty = document.getElementById('qty');
            document.querySelectorAll('.qty-btn').forEach(function(b) {
                b.addEventListener('click', function() {
                    if (!qty) return;
                    var v = parseInt(qty.value, 10) + parseInt(b.dataset.d, 10);
                    var max = parseInt(qty.max, 10);
                    qty.value = Math.min(Math.max(v, 1), max);
                });
            });

            /* ===== SUBMIT: kalau Custom, semua ukuran wajib berupa angka ===== */
            var form = document.getElementById('add-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    var notes = document.getElementById('custom-notes');
                    if (!isCustom) {
                        notes.value = '';
                        return;
                    }

                    var invalid = inputs.filter(function(i) {
                        var v = i.value.trim();
                        return !/^\d+(\.\d{1,2})?$/.test(v) || parseFloat(v) <= 0;
                    });
                    if (invalid.length) {
                        e.preventDefault();
                        invalid[0].focus();
                        alert('Isi semua ukuran custom dengan angka (cm).');
                        return;
                    }
                    notes.value = customText();
                });
            }
        })();
    </script>
@endpush