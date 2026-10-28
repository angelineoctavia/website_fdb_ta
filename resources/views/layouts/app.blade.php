<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MADE BY PIDIZ')</title>
    <link rel="stylesheet" href="{{ asset('css/header_footer.css') }}">
    @stack('styles')
</head>

<body>

    {{-- Panggil Header --}}
    @include('components.header')

    {{-- Main Content --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- Panggil Footer --}}
    @include('components.footer')

    @stack('scripts')
</body>
</html>