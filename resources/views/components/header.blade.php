@php
    $go = fn($name, $fallback) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : url($fallback);
@endphp

<header class="navbar">
    <div class="navbar-left">
        <img class="logo-uc" src="{{ asset('images/UC_LOGO_WARNA.png') }}" alt="Universitas Ciputra">
        <span class="divider">|</span>
        <span class="brand-title">MADE BY PIDIZ</span>
    </div>

    <nav class="navbar-center">
        <a href="{{ $go('home', '/') }}"
            class="nav-link {{ request()->is('/') || request()->routeIs('home') ? 'active' : '' }}">Home</a>
        <a href="{{ $go('shop.index', '/shop') }}"
            class="nav-link {{ request()->is('shop*') ? 'active' : '' }}">Shop</a>
        <a href="{{ $go('about', '/about') }}" class="nav-link {{ request()->is('about*') ? 'active' : '' }}">About
            Us</a>
    </nav>

    <div class="navbar-right">
        <a href="{{ $go('shop.index', '/shop') }}" class="icon-link" aria-label="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
        </a>
        <a href="{{ url('/wishlist') }}" class="icon-link {{ request()->is('wishlist*') ? 'active' : '' }}"
            aria-label="Wishlist">
            <i class="{{ request()->is('wishlist*') ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
        </a>
        <a href="{{ url('/cart') }}" class="icon-link {{ request()->is('cart*') ? 'active' : '' }}"
            aria-label="Cart">
            <i class="fa-solid fa-cart-shopping"></i>
        </a>
        <a href="{{ auth()->check() ? url('/profile') : $go('login', '/login') }}"
            class="icon-link {{ request()->is('profile*') ? 'active' : '' }}" aria-label="Profile">
            <i class="{{ request()->is('profile*') ? 'fa-solid' : 'fa-regular' }} fa-user"></i>
        </a>
    </div>
</header>