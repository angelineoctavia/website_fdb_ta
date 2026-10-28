<header class="navbar">
    <div class="navbar-left">
        <img src="{{ asset('images/UC_LOGO_WARNA.png') }}" alt="Universitas Ciputra Logo" class="logo-uc">
        <span class="divider">|</span>
        <span class="brand-title">FASHIONOLOGY</span>
    </div>

    <nav class="navbar-center">
        <a href="{{ route('home') }}" class="nav-link {{ request()->is('home') ? 'active' : '' }}">Home</a>
        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->is('categories*') ? 'active' : '' }}">Categories</a>
        <a href="#" class="nav-link {{ request()->is('about') ? 'active' : '' }}">About Us</a>
    </nav>

    <div class="navbar-right">
        <a href="#" class="icon-link" title="Search">&#128065;</a>
        <a href="#" class="icon-link" title="Wishlist">&#9825;</a>
        <a href="#" class="icon-link" title="Cart">&#128722;</a>
        <a href="#" class="icon-link" title="Profile">&#128100;</a>
    </div>
</header>