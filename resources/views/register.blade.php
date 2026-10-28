<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MADE BY PIDIZ</title>
    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        html,
        body {
            width: 100%;
            height: 100vh;
            overflow: hidden;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
        }

        /* 1. Top Header */
        .auth-header {
            height: 56px;
            padding: 0 35px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #eaeaea;
            background: #ffffff;
            flex-shrink: 0;
        }

        .auth-header img {
            height: 26px;
            object-fit: contain;
        }

        .auth-header .divider {
            color: #d1d5db;
            font-size: 16px;
        }

        .auth-header .brand {
            font-weight: 800;
            letter-spacing: 0.8px;
            font-size: 15px;
            color: #111827;
        }

        /* 2. Body Area (2 Column) */
        .auth-container {
            display: flex;
            flex: 1;
            height: calc(100vh - 56px - 60px);
            overflow: hidden;
        }

        .auth-left {
            flex: 1;
            background: #f3f4f6;
            height: 100%;
            overflow: hidden;
        }

        .auth-left img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .auth-right {
            flex: 1;
            /* Gradasi pink disamakan persis dengan halaman Login */
            background: linear-gradient(135deg, #ffffff 40%, #fbcfe8 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            height: 100%;
        }

        /* 3. Register Card & Form */
        .register-card {
            background: #ffffff;
            width: 100%;
            max-width: 390px;
            padding: 32px 30px 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            text-align: center;
        }

        .register-card h2 {
            font-size: 24px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 16px;
            text-align: left;
        }

        .form-control {
            width: 100%;
            border: none;
            border-bottom: 1px solid #475569;
            padding: 6px 0;
            outline: none;
            font-size: 13.5px;
            color: #1e293b;
            background: transparent;
            transition: border-color 0.2s;
        }

        .form-control::placeholder {
            color: #94a3b8;
            font-size: 13px;
        }

        .form-control:focus {
            border-bottom-color: #C20042;
        }

        /* Button Register (Sama persis dengan Login) */
        .btn-register {
            width: 100%;
            background-color: #C20042;
            color: white;
            border: none;
            padding: 11px;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 18px;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-register:hover {
            background-color: #9e0035;
        }

        .btn-register:active {
            transform: scale(0.99);
        }

        .login-link {
            margin-top: 18px;
            font-size: 12.5px;
            color: #64748b;
        }

        .login-link a {
            color: #C20042;
            font-weight: 700;
            text-decoration: underline;
        }

        /* 4. Compact Footer */
        .auth-footer {
            height: 60px;
            background-color: #C20042;
            color: white;
            padding: 10px 35px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            flex-shrink: 0;
        }

        .auth-footer .brand {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .auth-footer hr {
            border: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.25);
            margin: 4px 0;
        }

        .auth-footer p {
            font-size: 10.5px;
            color: #fbcfe8;
        }

        /* Responsive HP */
        @media (max-width: 768px) {

            html,
            body {
                height: auto;
                overflow: auto;
            }

            .auth-container {
                flex-direction: column;
                height: auto;
            }

            .auth-left {
                display: none;
            }

            .auth-right {
                padding: 40px 20px;
            }

            .register-card {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

    <!-- Header Logo -->
    <div class="auth-header">
        <img src="{{ asset('images/UC_LOGO_WARNA.png') }}" alt="UC Logo"
            onerror="this.src='https://via.placeholder.com/120x30?text=UC+Logo'">
        <span class="divider">|</span>
        <span class="brand">MADE BY PIDIZ</span>
    </div>

    <!-- Main Section -->
    <div class="auth-container">
        <!-- Banner Kiri -->
        <div class="auth-left">
            <img src="{{ asset('images/Background_Login.png') }}" alt="Register Fabric Banner"
                onerror="this.src='https://via.placeholder.com/600x800?text=Fashion+Fabric+Image'">
        </div>

        <!-- Form Kanan -->
        <div class="auth-right">
            <div class="register-card">
                <h2>Register Here</h2>

                <form action="{{ route('register.process') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="username" class="form-control" placeholder="Username" required
                            autocomplete="off">
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="role" class="form-control" placeholder="Role (C / S / A)"
                            required maxlength="1">
                    </div>
                    <div class="form-group">
                        <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
                    </div>
                    <button type="submit" class="btn-register">Register</button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="{{ route('login') }}">Login</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Compact -->
    <div class="auth-footer">
        <div class="brand">MADE BY PIDIZ</div>
        <hr>
        <p>&copy; 2026 Pidiz FDB UC. All Rights Reserved.</p>
    </div>

</body>

</html>
