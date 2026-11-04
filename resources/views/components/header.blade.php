<style>
    .nav-search {
        display: flex;
        align-items: center;
        gap: 6px
    }

    .nav-search input {
        width: 0;
        padding: 0;
        border: 0;
        outline: 0;
        opacity: 0;
        background: #f3f4f6;
        border-radius: 20px;
        font-size: 13px;
        transition: width .25s, padding .25s, opacity .2s;
    }

    .nav-search.open input {
        width: 200px;
        padding: 7px 14px;
        opacity: 1
    }

    .nav-search button {
        background: none;
        border: 0;
        cursor: pointer
    }
</style>

<header class="navbar">
    <div class="navbar-left">
        <img class="logo-uc" src="{{ asset('images/UC_LOGO_WARNA.png') }}" alt="Universitas Ciputra">
        <span class="divider">|</span>
        <span class="brand-title">MADE BY PIDIZ</span>
    </div>

    <nav class="navbar-center">
        <a href="{{ route('home') }}"
            class="nav-link {{ request()->is('/') || request()->routeIs('home') ? 'active' : '' }}">Home</a>
        <a href="{{ route('shop.index') }}" class="nav-link {{ request()->is('shop*') ? 'active' : '' }}">Shop</a>
        <a href="{{ url('/about') }}" class="nav-link {{ request()->is('about*') ? 'active' : '' }}">About Us</a>
    </nav>

    <div class="navbar-right">
        <form action="{{ route('shop.index') }}" method="GET" class="nav-search {{ request('q') ? 'open' : '' }}"
            id="nav-search">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products or brand..."
                autocomplete="off">
            <button type="submit" class="icon-link" aria-label="Search"><i
                    class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <a href="{{ route('wishlist.index') }}" class="icon-link {{ request()->is('wishlist*') ? 'active' : '' }}"
            aria-label="Wishlist">
            <i class="{{ request()->is('wishlist*') ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
        </a>

        <a href="{{ route('cart.index') }}" class="icon-link {{ request()->is('cart*') ? 'active' : '' }}"
            aria-label="Cart">
            <i class="fa-solid fa-cart-shopping"></i>
        </a>

        {{-- Dynamic Profile/Dashboard & Auth Navigation based on Session & Role --}}
        @if (session()->has('user_id'))
            @php
                $role = session('user_role');
                $dashboardRoute =
                    $role === 'S'
                        ? route('seller.dashboard')
                        : ($role === 'A'
                            ? route('admin.dashboard')
                            : url('/profile'));
            @endphp

            <a href="{{ $dashboardRoute }}"
                class="icon-link {{ request()->is('profile*') || request()->is('seller/dashboard*') || request()->is('admin/dashboard*') ? 'active' : '' }}"
                aria-label="Dashboard">
                <i class="fa-regular fa-user"></i>
            </a>

            @if ($role === 'S' && Route::has('seller.products'))
                <a href="{{ route('seller.products') }}" class="nav-link">My Products</a>
            @endif

            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="nav-link"
                    style="background:none; border:none; cursor:pointer;">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="icon-link {{ request()->is('login*') ? 'active' : '' }}"
                aria-label="Login">
                <i class="fa-regular fa-user"></i>
            </a>
        @endif
    </div>
</header>

<script>
    (function() {
        var form = document.getElementById('nav-search');
        if (!form) return;
        var input = form.querySelector('input');
        form.addEventListener('submit', function(e) {
            // klik pertama: buka kotak input. klik berikutnya: kirim kalau ada isinya.
            if (!form.classList.contains('open')) {
                e.preventDefault();
                form.classList.add('open');
                input.focus();
            } else if (input.value.trim() === '') {
                e.preventDefault();
                form.classList.remove('open');
            }
        });
    })();
</script>