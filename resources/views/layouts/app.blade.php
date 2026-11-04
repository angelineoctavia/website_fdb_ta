<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shop') | Made By Pidiz</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    {{-- hapus baris ini kalau Font Awesome sudah di-load di tempat lain --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --brand: #cc0a48;
            --dark: #333333;
            --bg: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 14px;
            background: var(--bg);
            color: #1a1a1a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit
        }

        a {
            color: inherit;
            text-decoration: none
        }

        img {
            display: block;
            max-width: 100%
        }

        main {
            flex: 1
        }

        /* ================= HEADER ================= */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: #fff;
            border-bottom: 1px solid #eaeaea;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 12px
        }

        .logo-uc {
            height: 30px;
            object-fit: contain
        }

        .divider {
            color: #ccc;
            font-size: 18px
        }

        .brand-title {
            font-weight: 800;
            font-size: 18px;
            letter-spacing: 1px;
            color: #000
        }

        .navbar-center {
            display: flex;
            gap: 30px
        }

        .nav-link {
            color: #4b5563;
            font-weight: 500;
            font-size: 15px;
            transition: color .2s
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--brand);
            font-weight: 700
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px
        }

        .icon-link {
            color: #1f2937;
            font-size: 18px;
            transition: transform .2s
        }

        .icon-link:hover {
            transform: scale(1.1);
            color: var(--brand)
        }

        .icon-link.active {
            color: var(--brand)
        }

        /* ================= FOOTER ================= */
        .footer {
            background: var(--brand);
            color: #fff;
            padding: 30px 40px 15px;
            margin-top: 50px
        }

        .footer-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px
        }

        .footer-brand {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 1px
        }

        .footer-socials {
            display: flex;
            gap: 15px
        }

        .social-icon {
            color: #fff;
            width: 38px;
            height: 38px;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .4);
            border-radius: 50%;
        }

        .social-icon:hover {
            background: rgba(255, 255, 255, .15)
        }

        .footer-line {
            border: 0;
            border-top: 1px solid rgba(255, 255, 255, .2);
            margin-bottom: 15px
        }

        .footer-bottom {
            font-size: 12px;
            color: #fbcfe8
        }

        @media (max-width: 900px) {
            .navbar {
                padding: 12px 18px;
                flex-wrap: wrap;
                row-gap: 8px
            }

            .navbar-center {
                gap: 18px
            }

            .footer {
                padding: 24px 20px 14px
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    @include('components.header')

    <main class="main-content">
        @yield('content')
    </main>

    @include('components.footer')

    @stack('scripts')
</body>
</html>