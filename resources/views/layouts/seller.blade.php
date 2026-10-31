<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Seller Center') | Made By Pidiz</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand: #cc0a48;
            /* merah header */
            --dark: #333333;
            /* footer */
            --bg: #ececec;
            /* background halaman */
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            margin: 0
        }

        /* ---------- FONT: semuanya Inter, yang beda cuma size & weight ---------- */
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

        main {
            flex: 1
        }

        /* ---------- HEADER ---------- */
        .sh {
            background: var(--brand);
            color: #fff;
            height: 68px;
            padding: 0 36px;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50
        }

        .sh-left {
            display: flex;
            align-items: center;
            gap: 12px
        }

        .sh-logo {
            height: 34px;
            width: auto;
            display: block;
            filter: brightness(0) invert(1);
        }

        /* hapus baris ini kalau logo mau tampil warna asli */
        .sh-divider {
            width: 1px;
            height: 34px;
            background: rgba(255, 255, 255, .7)
        }

        .sh-brand {
            font-weight: 600;
            font-size: 17px;
            letter-spacing: .2px;
            color: #fff;
            text-decoration: none
        }

        .sh-nav {
            display: flex;
            gap: 40px
        }

        .sh-nav a {
            color: rgba(255, 255, 255, .65);
            font-size: 15px;
            text-decoration: none;
            transition: color .15s
        }

        .sh-nav a:hover {
            color: #fff
        }

        .sh-nav a.active {
            color: #fff;
            font-weight: 700
        }

        .sh-right {
            justify-self: end;
            display: flex;
            align-items: center;
            gap: 18px
        }

        .sh-user {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            font-weight: 600;
            font-size: 13px
        }

        .sh-user .ico {
            width: 16px;
            height: 16px;
            border: 1.5px solid rgba(255, 255, 255, .85);
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .sh-logout {
            background: none;
            border: 0;
            padding: 0;
            color: rgba(255, 255, 255, .75);
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: color .15s
        }

        .sh-logout:hover {
            color: #fff;
            text-decoration: underline
        }

        /* ---------- FOOTER ---------- */
        .sf {
            background: var(--dark);
            color: #fff;
            padding: 32px 48px 26px;
            margin-top: 40px
        }

        .sf-title {
            font-weight: 600;
            font-size: 22px
        }

        .sf hr {
            border: 0;
            border-top: 1px solid rgba(255, 255, 255, .45);
            margin: 14px 0 12px
        }

        .sf-copy {
            font-size: 13px;
            color: rgba(255, 255, 255, .9)
        }

        @media(max-width:700px) {
            .sh {
                grid-template-columns: auto 1fr;
                height: auto;
                padding: 10px 14px;
                row-gap: 8px
            }

            .sh-nav {
                grid-column: 1 / -1;
                order: 3;
                justify-content: space-between;
                gap: 12px
            }

            .sh-brand {
                display: none
            }

            .sf {
                padding: 20px
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    @include('components.seller_header')

    <main>
        @yield('content')
    </main>

    @include('components.seller_footer')

    @stack('scripts')
</body>
</html>