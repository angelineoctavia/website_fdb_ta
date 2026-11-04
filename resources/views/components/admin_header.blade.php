<header class="sh">
    <div class="sh-left">
        <img class="sh-logo" src="{{ asset('images/UC_LOGO_WARNA.png') }}" alt="Universitas Ciputra">
        <span class="sh-divider"></span>
        <a class="sh-brand" href="{{ route('admin.dashboard') }}">MADE BY PIDIZ</a>
    </div>

    <nav class="sh-nav">
        <a href="{{ route('admin.dashboard') }}"
            class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('admin.orders') }}"
            class="{{ request()->routeIs('admin.orders*') ? 'active' : '' }}">Orders</a>
        <a href="{{ route('admin.profile') }}"
            class="{{ request()->routeIs('admin.profile*') ? 'active' : '' }}">Profile</a>
    </nav>

    <div class="sh-right">
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="sh-logout">Logout</button>
        </form>
    </div>
</header>