@extends('layouts.app')
@section('title', 'Product Detail - MADE BY PIDIZ')

@push('styles')
    <style>
        .detail-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            font-weight: 600;
            color: #374151;
            text-decoration: none;
        }

        .back-link:hover {
            color: #C20042;
        }

        .detail-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        /* Left Main Box */
        .product-main-box {
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 30px;
        }

        .product-gallery {
            width: 50%;
        }

        .main-img-preview {
            width: 100%;
            aspect-ratio: 3 / 4;
            background: #d1d5db;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .main-img-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail-list {
            display: flex;
            gap: 10px;
        }

        .thumb-box {
            width: calc(25% - 7.5px);
            aspect-ratio: 1;
            background: #d1d5db;
            border-radius: 6px;
            cursor: pointer;
            overflow: hidden;
        }

        .thumb-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-details-info {
            width: 50%;
        }

        .product-title-detail {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .product-price-detail {
            font-size: 18px;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 20px;
        }

        .section-label {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            color: #374151;
            margin-bottom: 8px;
        }

        .size-options {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .size-btn {
            width: 40px;
            height: 40px;
            border: 1px solid #d1d5db;
            background: #fff;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .size-btn:hover,
        .size-btn.active {
            border-color: #1f2937;
            background: #1f2937;
            color: #fff;
        }

        .color-options {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .color-box {
            width: 30px;
            height: 30px;
            border: 1px solid #d1d5db;
            border-radius: 50%;
            cursor: pointer;
        }

        .desc-text {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .btn-add-cart {
            width: 100%;
            background: #262626;
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-add-cart:hover {
            background: #C20042;
        }

        /* Right Sidebar Widgets */
        .sidebar-widgets {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .designer-card,
        .size-chart-card,
        .map-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .designer-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #d1d5db;
            margin: 0 auto 12px auto;
            overflow: hidden;
        }

        .designer-name {
            font-weight: 700;
            font-size: 15px;
            color: #1f2937;
        }

        .designer-ig {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 15px;
        }

        .btn-brand-catalog {
            display: block;
            background: #1f2937;
            color: #fff;
            padding: 8px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .btn-whatsapp {
            display: block;
            background: #25d366;
            color: #fff;
            padding: 8px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        /* Size Chart Table */
        .size-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        .size-table th,
        .size-table td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            text-align: center;
        }

        .size-table th {
            background: #C20042;
            color: #fff;
        }

        .map-box {
            width: 100%;
            height: 120px;
            background: #e5e7eb;
            border-radius: 8px;
            margin-top: 10px;
            overflow: hidden;
        }

        .map-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @media (max-width: 900px) {
            .detail-layout {
                grid-template-columns: 1fr;
            }

            .product-main-box {
                flex-direction: column;
            }

            .product-gallery,
            .product-details-info {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="detail-container">
        <a href="{{ route('shop.index') }}" class="back-link">&larr; Shop</a>

        <div class="detail-layout">
            <!-- Kolom Kiri: Detail Produk Utama -->
            <div class="product-main-box">
                <div class="product-gallery">
                    <div class="main-img-preview">
                        <!-- Menampilkan gambar utama produk[cite: 10] -->
                        <img src="{{ asset('images/Mockup_Pics/' . ($product->product_image ?? 'Trascend.jpg')) }}"
                            alt="Product Image" id="mainImage"
                            onerror="this.src='{{ asset('images/Mockup_Pics/Trascend.jpg') }}'">
                    </div>
                    <div class="thumbnail-list">
                        <div class="thumb-box"><img src="{{ asset('images/Mockup_Pics/Trascend.jpg') }}" alt="Thumb">
                        </div>
                        <div class="thumb-box"><img src="{{ asset('images/Mockup_Pics/Denim.jpg') }}" alt="Thumb"></div>
                        <div class="thumb-box"><img src="{{ asset('images/Mockup_Pics/Demi_Couture.jpg') }}" alt="Thumb">
                        </div>
                        <div class="thumb-box"><img src="{{ asset('images/Mockup_Pics/ABC.jpg') }}" alt="Thumb"></div>
                    </div>
                </div>

                <div class="product-details-info">
                    <h1 class="product-title-detail">{{ $product->product_name ?? 'Tiered Summer Dress' }}</h1>
                    <div class="product-price-detail">Rp {{ number_format($product->product_price ?? 100000, 0, ',', '.') }}
                    </div>

                    <div class="section-label">Size</div>
                    <div class="size-options">
                        <button class="size-btn active">S</button>
                        <button class="size-btn">M</button>
                        <button class="size-btn">L</button>
                        <button class="size-btn">XL</button>
                    </div>

                    <div class="section-label">Color</div>
                    <div class="color-options">
                        <div class="color-box" style="background: #1f2937;"></div>
                        <div class="color-box" style="background: #ffffff;"></div>
                        <div class="color-box" style="background: #C20042;"></div>
                    </div>

                    <div class="section-label">Product Description</div>
                    <p class="desc-text">
                        {{ $product->product_description ?? 'Elegantly crafted sustainable fashion piece designed by FDB students, combining modern aesthetic with traditional craftsmanship.' }}
                    </p>

                    <button class="btn-add-cart">Add To Cart</button>
                </div>
            </div>

            <!-- Kolom Kanan: Widget Desainer, Size Chart, & Maps -->
            <div class="sidebar-widgets">
                <!-- Profil Desainer/Brand[cite: 10] -->
                <div class="designer-card">
                    <div class="designer-avatar">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Aveline.png') }}" alt="Brand Logo"
                            style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div class="designer-name">{{ $product->user->USERS_NAME ?? 'Aveline Atelier' }}</div>
                    <div class="designer-ig">@aveline.official</div>
                    <a href="{{ route('shop.index') }}" class="btn-brand-catalog">Brand Catalog</a>
                    <a href="https://whatsapp.com" target="_blank" class="btn-whatsapp">WhatsApp</a>
                </div>

                <!-- Tabel Ukuran (Size Chart)[cite: 10] -->
                <div class="size-chart-card">
                    <div class="section-label" style="text-align: left;">Size Chart (cm)</div>
                    <table class="size-table">
                        <tr>
                            <th>Size</th>
                            <th>Bust</th>
                            <th>Length</th>
                            <th>Shoulder</th>
                        </tr>
                        <tr>
                            <td>S</td>
                            <td>84</td>
                            <td>90</td>
                            <td>36</td>
                        </tr>
                        <tr>
                            <td>M</td>
                            <td>88</td>
                            <td>92</td>
                            <td>38</td>
                        </tr>
                        <tr>
                            <td>L</td>
                            <td>92</td>
                            <td>94</td>
                            <td>40</td>
                        </tr>
                        <tr>
                            <td>XL</td>
                            <td>96</td>
                            <td>96</td>
                            <td>42</td>
                        </tr>
                    </table>
                </div>

                <!-- Maps Lokasi / Kampus[cite: 10] -->
                <div class="map-card">
                    <div class="section-label" style="text-align: left;">Location</div>
                    <div class="map-box">
                        <img src="https://via.placeholder.com/300x120?text=Universitas+Ciputra+Maps" alt="Location Map">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection