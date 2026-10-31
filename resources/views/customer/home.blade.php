@extends('layouts.app')
@section('title', 'Home - FASHIONOLOGY')

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
            /* Hide scrollbar for Firefox */
            padding: 10px 20px;
            max-width: 1000px;
        }

        .brands-grid::-webkit-scrollbar {
            display: none;
            /* Hide scrollbar for Chrome/Safari */
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
            /* INI KUNCINYA: Memotong bagian luar lingkaran */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
            padding: 0;
            /* Kosongkan padding agar gambar full menutup lingkaran */
        }

        .brand-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Memenuhi seluruh area lingkaran dan otomatis memotong sudut kotak */
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

        .product-box-placeholder {
            width: 100%;
            aspect-ratio: 3 / 4;
            background-color: #d1d5db;
            border-radius: 4px;
            transition: opacity 0.2s ease;
        }

        .product-box-placeholder:hover {
            opacity: 0.9;
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
                /* Sembunyikan tombol panah di HP, biarkan user swipe pakai jari */
            }
        }
    </style>
@endpush

@section('content')
    <!-- 1. Hero Banner Image -->
    <section class="hero-banner">
        <img src="{{ asset('images/Cover_FDB_Cropped.jpg') }}" alt="Fashionology Hero Banner"
            onerror="this.src='https://via.placeholder.com/1200x420?text=Hero+Banner+Fashionology'">
    </section>

    <!-- 2. FDB Student's Brands Section (Slider dengan tombol < >) -->
    <section class="section-container">
        <h2 class="section-title">FDB STUDENTS' BRANDS</h2>

        <div class="brands-slider-wrapper">
            <!-- Tombol Panah Kiri -->
            <button class="slider-btn prev" onclick="scrollBrands(-1)">&#10094;</button>

            <!-- Container List Logo Brand -->
            <div class="brands-grid" id="brandsContainer">

                <!-- Brand 1: Aveline -->
                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Aveline.png') }}" alt="Aveline">
                    </div>
                    <span class="brand-name">Aveline</span>
                </div>

                <!-- Brand 2: Liora Atelier -->
                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Liora_Atelier.png') }}" alt="Liora Atelier">
                    </div>
                    <span class="brand-name">Liora Atelier</span>
                </div>

                <!-- Brand 3: Lunelle -->
                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Lunelle.png') }}" alt="Lunelle">
                    </div>
                    <span class="brand-name">Lunelle</span>
                </div>

                <!-- Brand 4: Mora Studio -->
                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Mora_Studio.png') }}" alt="Mora Studio">
                    </div>
                    <span class="brand-name">Mora Studio</span>
                </div>

                <!-- Brand 5: NOVA -->
                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/NOVA.png') }}" alt="NOVA">
                    </div>
                    <span class="brand-name">NOVA</span>
                </div>

                <!-- Brand 6: Serein Bureau -->
                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Serein_Bureau.png') }}" alt="Serein Bureau">
                    </div>
                    <span class="brand-name">Serein Bureau</span>
                </div>

                <!-- Brand 7: Vogue Aviary -->
                <div class="brand-item">
                    <div class="brand-circle">
                        <img src="{{ asset('images/Brand_Logo_Mockup/Vogue_Aviary.png') }}" alt="Vogue Aviary">
                    </div>
                    <span class="brand-name">Vogue Aviary</span>
                </div>

            </div>

            <!-- Tombol Panah Kanan -->
            <button class="slider-btn next" onclick="scrollBrands(1)">&#10095;</button>
        </div>
    </section>

    <hr class="section-divider">

    <!-- 3. New Arrivals Section -->
    <section class="section-container">
        <h2 class="section-title">NEW ARRIVALS</h2>
        <div class="products-grid">
            <div class="product-box-placeholder"></div>
            <div class="product-box-placeholder"></div>
            <div class="product-box-placeholder"></div>
        </div>
    </section>

    <hr class="section-divider">

    <!-- 4. Top 3 Best Seller Section -->
    <section class="section-container">
        <h2 class="section-title">TOP 3 BEST SELLER OF THE MONTH</h2>
        <div class="products-grid">
            <div class="product-box-placeholder"></div>
            <div class="product-box-placeholder"></div>
            <div class="product-box-placeholder"></div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // Fungsi JavaScript untuk menggeser slider brand saat tombol < atau > diklik
        function scrollBrands(direction) {
            const container = document.getElementById('brandsContainer');
            const scrollAmount = 250; // Jarak geser dalam pixel
            container.scrollBy({
                left: direction * scrollAmount,
                behavior: 'smooth'
            });
        }
    </script>
@endpush
