<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MADE BY PIDIZ</title>
    <!-- Google Font: Inter (sama dengan header/footer seller, admin & customer) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', system-ui, sans-serif;
        }

        html,
        body {
            width: 100%;
            height: 100vh;
            overflow: hidden;
            background-color: #ffffff;
            font-size: 14px;
            display: flex;
            flex-direction: column;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }

        /* 1. Top Header (ukuran disamakan dengan header seller/admin) */
        .auth-header {
            height: 68px;
            padding: 0 36px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #eaeaea;
            background: #ffffff;
            flex-shrink: 0;
        }

        .auth-header img {
            height: 34px;
            object-fit: contain;
        }

        .auth-header .divider {
            width: 1px;
            height: 34px;
            background: #d1d5db;
            font-size: 0;
        }

        .auth-header .brand {
            font-weight: 600;
            letter-spacing: 0.2px;
            font-size: 17px;
            color: #111827;
        }

        /* 2. Body Area (2 Column) */
        .auth-container {
            display: flex;
            flex: 1;
            min-height: 0;
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
            overflow-y: auto;
        }

        /* 3. Register Card & Form */
        .register-card {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 32px 30px 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            text-align: center;
        }

        .register-card h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 16px;
            text-align: left;
            position: relative;
        }

        /* Ikon di kanan kolom: kotak 24x24 sama persis dengan halaman login */
        .field-icon,
        .toggle-eye {
            position: absolute;
            right: 0;
            bottom: 3px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 14px;
            line-height: 1;
        }

        .field-icon {
            pointer-events: none;
        }

        .toggle-eye {
            background: none;
            border: 0;
            padding: 0;
            cursor: pointer;
        }

        .toggle-eye:hover {
            color: #C20042;
        }

        .form-control.has-icon {
            padding-right: 32px;
        }

        /* Dropdown role: sejajar dengan input lain & abu-abu seperti placeholder sebelum dipilih */
        select.form-control {
            padding-left: 0;
            padding-right: 32px;
        }

        select.form-control:invalid {
            color: #94a3b8;
        }

        select.form-control option {
            color: #1e293b;
        }

        .form-control {
            width: 100%;
            border: none;
            border-bottom: 1px solid #475569;
            padding: 6px 0;
            outline: none;
            font-size: 14px;
            color: #1e293b;
            background: transparent;
            transition: border-color 0.2s;
        }

        .form-control::placeholder {
            color: #94a3b8;
            font-size: 14px;
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
            font-weight: 600;
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
            font-size: 13px;
            color: #64748b;
        }

        .login-link a {
            color: #C20042;
            font-weight: 600;
            text-decoration: underline;
        }

        /* 4. Footer (ukuran disamakan dengan footer seller/admin) */
        .auth-footer {
            background-color: #C20042;
            color: white;
            padding: 12px 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            flex-shrink: 0;
        }

        .auth-footer .brand {
            font-size: 17px;
            font-weight: 600;
            line-height: 1.2;
        }

        .auth-footer hr {
            border: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.45);
            margin: 6px 0;
        }

        .auth-footer p {
            font-size: 13px;
            line-height: 1.2;
            color: #fbcfe8;
        }

        /* Responsive HP */
        @media (max-width: 768px) {

            html,
            body {
                height: auto;
                min-height: 100vh;
                overflow: auto;
            }

            .auth-header {
                padding: 0 16px;
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

            .auth-footer {
                padding: 12px 16px;
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
            <img src="{{ asset('images/New_BG.jpg') }}" alt="Register Fabric Banner"
                onerror="this.src='https://via.placeholder.com/600x800?text=Fashion+Fabric+Image'">
        </div>

        <!-- Form Kanan -->
        <div class="auth-right">
            <div class="register-card">
                <h2>Register Here</h2>

                @if ($errors->any())
                    <div
                        style="background: #fee2e2; color: #991b1b; padding: 8px; border-radius: 6px; font-size: 13px; margin-bottom: 12px; text-align: left;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('register.process') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="username" class="form-control has-icon" placeholder="Username"
                            value="{{ old('username') }}" required autocomplete="off">
                        <span class="field-icon"><i class="fa-regular fa-user"></i></span>
                    </div>

                    <!-- Password dengan Icon Mata -->
                    <div class="form-group">
                        <input type="password" name="password" id="passwordField" class="form-control has-icon"
                            placeholder="Password" required>
                        <button type="button" class="toggle-eye" aria-label="Tampilkan / sembunyikan password"
                            onclick="togglePasswordVisibility()">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>

                    <!-- Dropdown Role (Customer / Seller) -->
                    <div class="form-group">
                        <select name="role" class="form-control" required style="cursor: pointer;">
                            <option value="" disabled selected style="color: #94a3b8;">Select Role (Customer /
                                Seller)</option>
                            <option value="C">Customer</option>
                            <option value="S">Seller</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <input type="tel" inputmode="tel" name="phone_number" class="form-control has-icon"
                            placeholder="Phone Number Format (628XXXXXXXXXX)" value="{{ old('phone_number') }}" required>
                        <span class="field-icon"><i class="fa-solid fa-phone"></i></span>
                    </div>

                    <button type="submit" class="btn-register">Register</button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="{{ route('login') }}">Login</a>
                </div>
            </div>
        </div>

        <script>
            function togglePasswordVisibility() {
                const pwd = document.getElementById('passwordField');
                const icon = document.getElementById('eyeIcon');
                const show = pwd.type === 'password';
                pwd.type = show ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !show);
                icon.classList.toggle('fa-eye-slash', show);
            }
        </script>
    </div>

    <!-- Footer -->
    <div class="auth-footer">
        <div class="brand">MADE BY PIDIZ</div>
        <hr>
        <p>&copy; 2026 Pidiz FDB UC. All Rights Reserved.</p>
    </div>

</body>
</html>