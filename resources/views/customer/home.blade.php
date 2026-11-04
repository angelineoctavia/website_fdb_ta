@extends('layouts.app')
@section('title', 'Home')

@push('styles')
    <style>
        /* Hero Banner Section */
        .hero-banner {
            width: 100%;
            height: 420px;
            overflow: hidden;
            background-color: #e5e7eb;
        }

        .hero-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        /* Common Section Layout */
        .section-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            text-align: center;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: #374151;
            text-transform: uppercase;
            margin-bottom: 30px;
        }

        .section-divider {
            max-width: 1200px;
            margin: 0 auto;
            border: 0;
            border-top: 1px solid #e5e7eb;
        }

        /* Brands Slider Container */
        .brands-slider-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .brands-grid {
            display: flex;
            gap: 30px;
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: 10px 20px;
            max-width: 1000px;
        }

        .brands-grid::-webkit-scrollbar {
            display: none;
        }

        .brand-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .brand-item:hover {
            transform: translateY(-5px);
        }

        .brand-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
            padding: 0;
        }

        .brand-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-name {
            margin-top: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        /* Slider Navigation Buttons (< and >) */
        .slider-btn {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #374151;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            z-index: 10;
        }

        .slider-btn:hover {
            background-color: #C20042;
            color: #ffffff;
            border-color: #C20042;
        }

        .slider-btn.prev {
            margin-right: 15px;
        }

        .slider-btn.next {
            margin-left: 15px;
        }

        /* Product Grid Section (3 Columns) */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: 20px;
        }

        .home-product {
            display: block;
            text-align: left;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .home-product:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(194, 0, 66, 0.18);
            border-color: #C20042;
        }

        .home-product-img {
            width: 100%;
            aspect-ratio: 3 / 4;
            background-color: #f3f4f6;
            overflow: hidden;
        }

        .home-product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }

        .home-product:hover .home-product-img img {
            transform: scale(1.04);
        }

        .home-product-img .img-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 13px;
        }

        .home-product-info {
            padding: 12px 14px 14px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: 13px;
            border-top: 1px solid #f1f5f9;
        }

        .home-product-name {
            font-weight: 600;
            color: #1f2937;
        }

        .home-product-price {
            color: #6b7280;
        }

        .home-empty {
            padding: 50px 0;
            color: #6b7280;
            font-size: 14px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero-banner {
                height: 250px;
            }

            .slider-btn {
                display: none;
            }
        }
    </style>
@endpush

@section('content')
    <!-- 1. Hero Banner Image -->
    <section class="hero-banner">
        <img src="{{ asset('images/Cover_FDB_Cropped.jpg') }}" alt="Made By Pidiz Hero Banner"
            onerror="this.src='https://via.placeholder.com/1200x420?text=Hero+Banner+Fashionology'">
    </section>

    <!-- 2. FDB Student's Brands Section (mockup; tambah seller asli di sini nanti) -->
    <section class="section-container">
        <h2 class="section-title">FDB STUDENTS' BRANDS</h2>

        <div class="brands-slider-wrapper">
            <button class="slider-btn prev" onclick="scrollBrands(-1)">&#10094;</button>

            <div class="brands-grid" id="brandsContainer">

                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Aveline.png') }}" alt="Aveline">
                    </div>
                    <span class="brand-name">Aveline</span>
                </div>

                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Liora_Atelier.png') }}" alt="Liora Atelier">
                    </div>
                    <span class="brand-name">Liora Atelier</span>
                </div>

                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Lunelle.png') }}" alt="Lunelle">
                    </div>
                    <span class="brand-name">Lunelle</span>
                </div>

                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Mora_Studio.png') }}" alt="Mora Studio">
                    </div>
                    <span class="brand-name">Mora Studio</span>
                </div>

                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/NOVA.png') }}" alt="NOVA">
                    </div>
                    <span class="brand-name">NOVA</span>
                </div>

                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Serein_Bureau.png') }}" alt="Serein Bureau">
                    </div>
                    <span class="brand-name">Serein Bureau</span>
                </div>

                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Vogue_Aviary.png') }}" alt="Vogue Aviary">
                    </div>
                    <span class="brand-name">Vogue Aviary</span>
                </div>

            </div>

            <button class="slider-btn next" onclick="scrollBrands(1)">&#10095;</button>
        </div>
    </section>

    <hr class="section-divider">

    <!-- 3. New Arrivals Section (3 produk aktif terbaru dari database) -->
    <section class="section-container">
        <h2 class="section-title">NEW ARRIVALS</h2>

        @if ($newArrivals->isEmpty())
            <p class="home-empty">Belum ada produk.</p>
        @else
            <div class="products-grid">
                @foreach ($newArrivals as $p)
                    <a href="{{ route('shop.show', $p->PRODUCTS_ID) }}" class="home-product">
                        <div class="home-product-img">
                            @if ($p->cover_url)
                                <img src="{{ $p->cover_url }}" alt="{{ $p->PRODUCTS_NAME }}" loading="lazy">
                            @else
                                <div class="img-placeholder">No Image</div>
                            @endif
                        </div>
                        <div class="home-product-info">
                            <span class="home-product-name">{{ $p->PRODUCTS_NAME }}</span>
                            <span class="home-product-price">{{ $p->formatted_price }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <hr class="section-divider">

    <!-- 4. Top 3 Best Seller Section (terlaris bulan ini dari transaksi) -->
    <section class="section-container">
        <h2 class="section-title">TOP 3 BEST SELLER OF THE MONTH</h2>

        @if ($bestSellers->isEmpty())
            <p class="home-empty">Belum ada penjualan bulan ini.</p>
        @else
            <div class="products-grid">
                @foreach ($bestSellers as $p)
                    <a href="{{ route('shop.show', $p->PRODUCTS_ID) }}" class="home-product">
                        <div class="home-product-img">
                            @if ($p->cover_url)
                                <img src="{{ $p->cover_url }}" alt="{{ $p->PRODUCTS_NAME }}" loading="lazy">
                            @else
                                <div class="img-placeholder">No Image</div>
                            @endif
                        </div>
                        <div class="home-product-info">
                            <span class="home-product-name">{{ $p->PRODUCTS_NAME }}</span>
                            <span class="home-product-price">{{ $p->formatted_price }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        // Geser slider brand saat tombol < atau > diklik
        function scrollBrands(direction) {
            const container = document.getElementById('brandsContainer');
            const scrollAmount = 250;
            container.scrollBy({
                left: direction * scrollAmount,
                behavior: 'smooth'
            });
        }
    </script>
@endpush
