<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng nhập | StudyPrep</title>
    <meta name="description" content="Đăng nhập tài khoản StudyPrep để tiếp tục hành trình học tập.">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:400,600,700,800">
    <style>
        :root {
            --sp-primary:      #0076FE;
            --sp-primary-dark: #0053BF;
            --sp-yellow:       #FFD700;
            --sp-bg-app:       #EEF2F7;
            --sp-bg-card:      #FFFFFF;
            --sp-text-main:    #111827;
            --sp-text-body:    #374151;
            --sp-text-muted:   #6B7280;
            --sp-border:       #E5E7EB;
            --sp-shadow:       0 20px 50px -10px rgba(0,0,0,0.13), 0 8px 16px -6px rgba(0,0,0,0.08);
            --sp-radius:       20px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', ui-sans-serif, system-ui, -apple-system, sans-serif;
            background: var(--sp-bg-app);
            color: var(--sp-text-main);
            min-height: 100vh;
            min-height: 100dvh;
        }

        /* ===== Page wrapper ===== */
        .auth-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            min-height: 100dvh;
            padding: 32px 24px;
        }

        /* ===== Card container: 60/40 split ===== */
        .auth-container {
            display: grid;
            grid-template-columns: 60% 40%;
            max-width: 960px;
            width: 100%;
            background: var(--sp-bg-card);
            border-radius: var(--sp-radius);
            box-shadow: var(--sp-shadow);
            overflow: hidden;
        }

        /* ===== LEFT COLUMN ===== */
        .auth-left {
            padding: 52px 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-logo {
            width: 152px;
            height: auto;
            margin-bottom: 28px;
        }

        .auth-title {
            font-size: 30px;
            font-weight: 800;
            color: var(--sp-text-main);
            margin: 0 0 10px;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .auth-desc {
            font-size: 15px;
            color: var(--sp-text-body);
            margin: 0 0 28px;
            line-height: 1.55;
        }

        /* ===== Google button ===== */
        .auth-google-wrap {
            display: block;
            margin-bottom: 0;
        }

        .auth-google-wrap .sp-btn.google-btn {
            width: 100%;
            height: 48px;
            background-color: #F0F3F7 !important;
            border: 1px solid #CBD5E1 !important;
            border-radius: 14px !important;
            box-shadow: 0 4.5px 0 #CBD5E1 !important;
            color: var(--sp-text-body) !important;
            font-size: 15px;
            font-weight: 700;
            gap: 10px;
            transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.1s ease, transform 0.1s ease;
        }

        .auth-google-wrap .sp-btn.google-btn:hover {
            background-color: #E8EDF3 !important;
            border-color: #94A3B8 !important;
            box-shadow: 0 4.5px 0 #94A3B8 !important;
            filter: none !important;
        }

        .auth-google-wrap .sp-btn.google-btn:active {
            transform: translateY(3.5px) !important;
            box-shadow: 0 1px 0 #CBD5E1 !important;
            background-color: #DFE5EC !important;
        }

        .auth-google-icon {
            width: 20px;
            height: 20px;
            flex: none;
            display: block;
        }

        /* ===== Divider ===== */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
            color: var(--sp-text-muted);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--sp-border);
        }

        /* ===== Input fields ===== */
        .auth-fields {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 18px;
        }

        /*
         * Bọc x-input trong .auth-input-wrap để đặt icon SVG tuyệt đối.
         * sp-field (do component sinh ra) có margin-bottom:16px mặc định —
         * override về 0 để gap của .auth-fields kiểm soát khoảng cách.
         */
        .auth-input-wrap {
            position: relative;
        }

        .auth-input-wrap .sp-field {
            margin-bottom: 0 !important;
        }

        .auth-input-wrap .sp-control {
            min-height: 48px;
            border-radius: 14px;
            background-color: #F8FAFD;
            box-shadow: 0 0 0 1.5px #E2E8F0;
        }

        .auth-input-wrap .sp-control:hover {
            box-shadow: 0 0 0 1.5px #CBD5E1;
        }

        .auth-input-wrap .sp-control:focus-within {
            background-color: #FFFFFF;
            box-shadow: 0 0 0 2px #0057BF, 0 0 0 5px rgba(0, 118, 254, 0.15);
        }

        /* Icon tuyệt đối bên trái, căn giữa chiều cao .sp-control */
        .auth-input-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 46px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            color: #727786;
            z-index: 1;
        }

        .auth-input-icon svg {
            width: 18px;
            height: 18px;
            flex: none;
        }

        /* ===== Options row: Ghi nhớ đăng nhập & Quên mật khẩu ===== */
        .auth-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            font-size: 14px;
        }

        .auth-remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--sp-text-body);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
        }

        .auth-remember input[type="checkbox"] {
            width: 17px;
            height: 17px;
            border-radius: 4px;
            border: 1.5px solid #CBD5E1;
            accent-color: var(--sp-primary);
            cursor: pointer;
        }

        .auth-forgot-link {
            color: var(--sp-primary);
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .auth-forgot-link:hover {
            text-decoration: underline;
            color: var(--sp-primary-dark);
        }

        /* ===== Submit button full-width ===== */
        .auth-submit-wrap .sp-btn {
            width: 100%;
            height: 48px;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            border-radius: 14px !important;
        }

        /* ===== RIGHT COLUMN ===== */
        .auth-right {
            background: #0076FE;
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .auth-right-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
        }

        .auth-home-link {
            position: absolute;
            top: 24px;
            right: 28px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: color 0.15s ease;
        }

        .auth-home-link:hover {
            color: #FFFFFF;
        }

        .auth-get-started {
            font-size: 38px;
            font-weight: 800;
            color: var(--sp-yellow);
            margin: 0 0 10px;
            line-height: 1.1;
            letter-spacing: -0.01em;
            text-align: center;
        }

        .auth-have-account {
            margin: 0 0 22px;
            font-size: 15px;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
            text-align: center;
        }

        /* Register button: viền màu vàng, bo góc nhẹ hơn, căn giữa */
        .auth-right .sp-btn.login-btn {
            background: transparent !important;
            color: var(--sp-yellow) !important;
            border: 2px solid var(--sp-yellow) !important;
            border-radius: 12px !important;
            box-shadow: none !important;
            height: 46px !important;
            width: 220px !important;
            max-width: 100%;
            font-size: 15px;
            font-weight: 700;
            align-self: center;
            margin: 0 auto;
            transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, transform 0.1s ease;
        }

        .auth-right .sp-btn.login-btn:hover {
            background: var(--sp-yellow) !important;
            color: #0053BF !important;
            border-color: var(--sp-yellow) !important;
            filter: none !important;
        }

        .auth-right .sp-btn.login-btn:active {
            transform: translateY(2px) !important;
        }

        /* ===== Responsive ===== */
        @media (max-width: 767px) {
            .auth-page {
                padding: 0;
                align-items: stretch;
            }

            .auth-container {
                grid-template-columns: 1fr;
                max-width: 100%;
                width: 100%;
                min-height: 100vh;
                min-height: 100dvh;
                border-radius: 0;
                box-shadow: none;
            }

            .auth-right {
                display: none;
            }

            .auth-left {
                padding: 40px 28px 36px;
                justify-content: flex-start;
                padding-top: 52px;
            }

            .auth-logo { width: 136px; margin-bottom: 24px; }
            .auth-title { font-size: 26px; }
            .auth-desc { font-size: 14px; margin-bottom: 24px; }
        }

        @media (max-width: 479px) {
            .auth-left { padding: 40px 20px 28px; }
            .auth-title { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">

            {{-- ===== Cột trái: Form đăng nhập ===== --}}
            <div class="auth-left">
                <img src="{{ asset('images/logo.svg') }}" alt="StudyPREP" class="auth-logo">

                <h1 class="auth-title">Chào mừng trở lại!</h1>
                <p class="auth-desc">Vui lòng nhập thông tin để truy cập không gian học tập của bạn.</p>

                {{-- Nút Google --}}
                <div class="auth-google-wrap">
                    <x-button variant="neutral" size="medium" type="button" class="google-btn">
                        {{-- SVG logo Google chuẩn 4 màu --}}
                        <svg class="auth-google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span>Đăng nhập bằng Google</span>
                    </x-button>
                </div>

                <div class="auth-divider">HOẶC ĐĂNG NHẬP BẰNG EMAIL</div>

                <form method="POST" action="{{ Route::has('login') ? route('login') : '#' }}">
                    @csrf
                    <div class="auth-fields">

                        {{-- Input Email --}}
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="4"/>
                                    <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"/>
                                </svg>
                            </span>
                            <x-input
                                name="email"
                                type="email"
                                placeholder="Nhập email của bạn"
                                style="padding-left: 46px;"
                                :error="($errors ?? null)?->first('email')"
                            />
                        </div>

                        {{-- Input Mật khẩu (component tự thêm toggle icon) --}}
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <x-input
                                name="password"
                                type="password"
                                placeholder="Nhập mật khẩu"
                                style="padding-left: 46px;"
                                :error="($errors ?? null)?->first('password')"
                            />
                        </div>

                    </div>

                    {{-- Ghi nhớ đăng nhập & Quên mật khẩu --}}
                    <div class="auth-options">
                        <label class="auth-remember">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Ghi nhớ đăng nhập</span>
                        </label>
                        <a href="{{ Route::has('password.request') ? route('password.request') : '#' }}" class="auth-forgot-link">Quên mật khẩu?</a>
                    </div>

                    {{-- Nút ĐĂNG NHẬP --}}
                    <div class="auth-submit-wrap">
                        <x-button variant="primary" size="medium" type="submit">ĐĂNG NHẬP</x-button>
                    </div>
                </form>
            </div>

            {{-- ===== Cột phải: Panel xanh ===== --}}
            <div class="auth-right">
                <a href="{{ Route::has('luyen-tap') ? route('luyen-tap') : url('/') }}" class="auth-home-link">← Trang chủ</a>

                <div class="auth-right-content">
                    <h2 class="auth-get-started">Welcome back!</h2>
                    <p class="auth-have-account">Chưa có tài khoản?</p>

                    <x-button
                        variant="neutral"
                        size="medium"
                        class="login-btn"
                        href="{{ Route::has('register') ? route('register') : url('/register') }}"
                    >Đăng ký</x-button>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
