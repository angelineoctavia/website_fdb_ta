<header class="sh">
    <div class="sh-left">
        {{-- file logo: public/images/UC_LOGO_WARNA.png (ganti ekstensi kalau beda) --}}
        <img class="sh-logo" src="{{ asset('images/UC_LOGO_WARNA.png') }}" alt="Universitas Ciputra">
        <span class="sh-divider"></span>
        <a class="sh-brand" href="{{ route('seller.dashboard') }}">MADE BY PIDIZ</a>
    </div>

    <nav class="sh-nav">
        <a href="{{ route('seller.dashboard') }}"
            class="{{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('seller.upload') }}"
            class="{{ request()->routeIs('seller.upload*') ? 'active' : '' }}">Upload</a>
        <a href="{{ route('seller.orders') }}"
            class="{{ request()->routeIs('seller.orders*') ? 'active' : '' }}">Orders</a>
        <a href="{{ route('seller.profile') }}"
            class="{{ request()->routeIs('seller.profile*') ? 'active' : '' }}">Profile</a>
    </nav>

    <div class="sh-right">
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="sh-logout">Logout</button>
        </form>
    </div>
</header>