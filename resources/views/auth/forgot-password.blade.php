<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quên mật khẩu | StudyPrep</title>
    <meta name="description" content="Khôi phục và đặt lại mật khẩu tài khoản StudyPrep của bạn.">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:400,500,600,700,800">
    <style>
        :root {
            --sp-primary:      #0076FE;
            --sp-primary-dark: #0053BF;
            --sp-success:      #00B87A;
            --sp-bg-app:       #EEF2F7;
            --sp-bg-card:      #FFFFFF;
            --sp-text-main:    #111C2D;
            --sp-text-body:    #5B677A;
            --sp-text-muted:   #8A94A6;
            --sp-border:       #E2E8F0;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Nunito', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--sp-bg-app);
            color: var(--sp-text-main);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== Khung trang chứa card giữa màn hình ===== */
        .auth-page {
            width: 100%;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        /* ===== Card chính: màu trắng, bo góc lớn, đổ bóng nhẹ ===== */
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--sp-bg-card);
            border-radius: 30px;
            box-shadow: 0 20px 48px -12px rgba(15, 23, 42, 0.08), 0 8px 24px -4px rgba(15, 23, 42, 0.04);
            padding: 44px 38px 38px;
            position: relative;
            box-sizing: border-box;
            transition: all 0.25s ease;
        }

        /* ===== Icon phong bì tròn màu xanh dương đổ bóng xanh nhẹ ===== */
        .auth-icon-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 22px;
        }

        .auth-icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background-color: var(--sp-primary);
            box-shadow: 0 10px 24px -2px rgba(0, 118, 254, 0.42);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.25s ease, background-color 0.25s ease, box-shadow 0.25s ease;
        }

        .auth-icon-circle svg {
            width: 32px;
            height: 32px;
            stroke: #FFFFFF;
        }

        .auth-icon-circle--success {
            background-color: var(--sp-success);
            box-shadow: 0 10px 24px -2px rgba(0, 184, 122, 0.42);
        }

        /* ===== Tiêu đề đậm, màu xanh đen, căn giữa ===== */
        .auth-title {
            font-size: 27px;
            font-weight: 800;
            color: var(--sp-text-main);
            text-align: center;
            margin-bottom: 10px;
            line-height: 1.25;
            letter-spacing: -0.015em;
        }

        /* ===== Mô tả dưới tiêu đề ===== */
        .auth-desc {
            font-size: 14.5px;
            color: var(--sp-text-body);
            text-align: center;
            line-height: 1.55;
            margin: 0 auto 26px;
            max-width: 330px;
            font-weight: 500;
        }

        .auth-desc strong {
            color: var(--sp-text-main);
            font-weight: 700;
            word-break: break-all;
        }

        /* ===== Khoảng cách form và nút bấm ===== */
        .auth-form {
            width: 100%;
        }

        .auth-inputs-wrap {
            margin-bottom: 20px;
        }

        /* Ghi đè margin mặc định của sp-field để khoảng cách đồng bộ */
        .auth-inputs-wrap .sp-field {
            margin-bottom: 16px;
        }

        .auth-inputs-wrap .sp-field:last-child {
            margin-bottom: 0;
        }

        /* Nút chính chiếm 100% bề rộng, cao 48px, bo góc và bóng 3D */
        .auth-btn-wrap {
            margin-top: 22px;
            width: 100%;
        }

        .auth-btn-wrap .sp-btn {
            width: 100%;
            height: 48px;
            border-radius: 14px !important;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Spinner loading khi đang gửi request */
        .sp-loading-spinner {
            width: 18px;
            height: 18px;
            border: 2.5px solid rgba(255, 255, 255, 0.35);
            border-top-color: #FFFFFF;
            border-radius: 50%;
            animation: sp-spin 0.75s linear infinite;
        }

        @keyframes sp-spin {
            to { transform: rotate(360deg); }
        }

        /* ===== Dòng điều hướng phía dưới ===== */
        .auth-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 26px;
            font-size: 14px;
            font-weight: 600;
            color: #64748B;
        }

        .auth-nav-back {
            background: none;
            border: none;
            padding: 0;
            margin: 0;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            color: #64748B;
            cursor: pointer;
            transition: color 0.15s ease;
            text-decoration: none;
        }

        .auth-nav-back:hover {
            color: var(--sp-text-main);
            text-decoration: underline;
        }

        .auth-nav-link {
            color: var(--sp-primary);
            font-weight: 700;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .auth-nav-link:hover {
            color: var(--sp-primary-dark);
            text-decoration: underline;
        }

        .auth-nav-dot {
            color: #94A3B8;
            font-size: 12px;
            margin: 0 2px;
            user-select: none;
        }

        /* ===== Phía ngoài card có dòng “Cần thêm trợ giúp?” ===== */
        .auth-help {
            margin-top: 24px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--sp-text-muted);
            text-align: center;
            cursor: pointer;
            transition: color 0.15s ease;
            user-select: none;
        }

        .auth-help:hover {
            color: #475569;
        }

        /* ===== Quản lý ẩn/hiện các bước với hiệu ứng mượt mà ===== */
        .step-pane {
            display: none;
            animation: fadeIn 0.25s ease-out;
        }

        .step-pane.is-active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .auth-page {
                padding: 20px 14px;
            }

            .auth-card {
                padding: 36px 22px 28px;
                border-radius: 24px;
            }

            .auth-title {
                font-size: 24px;
            }

            .auth-desc {
                font-size: 14px;
                margin-bottom: 22px;
            }

            .auth-icon-circle {
                width: 64px;
                height: 64px;
            }

            .auth-icon-circle svg {
                width: 28px;
                height: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card" id="forgot-password-card">

            {{-- Biểu tượng phong bì tròn phía trên card --}}
            <div class="auth-icon-wrapper">
                <div class="auth-icon-circle" id="auth-main-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="20" height="16" x="2" y="4" rx="3"/>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                </div>
            </div>

            {{-- ========================================================
                 BƯỚC 1: NHẬP EMAIL
                 ======================================================== --}}
            <div class="step-pane is-active" id="step-pane-1">
                <h1 class="auth-title">Quên mật khẩu?</h1>
                <p class="auth-desc">Đừng lo lắng! Nhập email của bạn và chúng tôi sẽ gửi mã đặt lại mật khẩu.</p>

                <form class="auth-form" id="form-step-1" novalidate onsubmit="return false;">
                    <div class="auth-inputs-wrap">
                        <x-input
                            id="email"
                            name="email"
                            type="email"
                            placeholder="Nhập email của bạn"
                            autocomplete="email"
                        />
                    </div>

                    <div class="auth-btn-wrap">
                        <x-button
                            variant="primary"
                            size="medium"
                            type="submit"
                            id="btn-step-1"
                        >
                            <span class="btn-text">GỬI MÃ</span>
                        </x-button>
                    </div>
                </form>

                <div class="auth-nav">
                    <a href="{{ route('login') }}" class="auth-nav-back">Quay lại</a>
                    <a href="{{ route('login') }}" class="auth-nav-link">Đăng nhập</a>
                    <span class="auth-nav-dot">•</span>
                    <a href="{{ route('register') }}" class="auth-nav-link">Đăng ký</a>
                </div>
            </div>

            {{-- ========================================================
                 BƯỚC 2: NHẬP MÃ XÁC MINH
                 ======================================================== --}}
            <div class="step-pane" id="step-pane-2">
                <h1 class="auth-title">Nhập mã</h1>
                <p class="auth-desc">
                    Nhập mã số đã được gửi đến email<br>
                    <strong id="display-masked-email">phu**********n1565@gmailcom</strong>
                </p>

                <form class="auth-form" id="form-step-2" novalidate onsubmit="return false;">
                    <div class="auth-inputs-wrap">
                        <x-input
                            id="verification_code"
                            name="verification_code"
                            type="text"
                            placeholder="Nhập mã xác minh"
                            maxlength="10"
                            autocomplete="one-time-code"
                        />
                    </div>

                    <div class="auth-btn-wrap">
                        <x-button
                            variant="primary"
                            size="medium"
                            type="submit"
                            id="btn-step-2"
                        >
                            <span class="btn-text">XÁC MINH</span>
                        </x-button>
                    </div>
                </form>

                <div class="auth-nav">
                    <button type="button" class="auth-nav-back" id="btn-back-to-step-1">Quay lại</button>
                    <a href="{{ route('login') }}" class="auth-nav-link">Đăng nhập</a>
                    <span class="auth-nav-dot">•</span>
                    <a href="{{ route('register') }}" class="auth-nav-link">Đăng ký</a>
                </div>
            </div>

            {{-- ========================================================
                 BƯỚC 3: ĐẶT LẠI MẬT KHẨU
                 ======================================================== --}}
            <div class="step-pane" id="step-pane-3">
                <h1 class="auth-title">Đặt lại mật khẩu</h1>

                <form class="auth-form" id="form-step-3" novalidate onsubmit="return false;">
                    <div class="auth-inputs-wrap">
                        <x-input
                            id="new_password"
                            name="new_password"
                            type="password"
                            placeholder="Mật khẩu mới"
                            autocomplete="new-password"
                        />

                        <x-input
                            id="confirm_password"
                            name="confirm_password"
                            type="password"
                            placeholder="Nhập lại mật khẩu mới"
                            autocomplete="new-password"
                        />
                    </div>

                    <div class="auth-btn-wrap">
                        <x-button
                            variant="primary"
                            size="medium"
                            type="submit"
                            id="btn-step-3"
                        >
                            <span class="btn-text">ĐỔI MẬT KHẨU</span>
                        </x-button>
                    </div>
                </form>

                <div class="auth-nav">
                    <button type="button" class="auth-nav-back" id="btn-back-to-step-2">Quay lại</button>
                    <a href="{{ route('login') }}" class="auth-nav-link">Đăng nhập</a>
                    <span class="auth-nav-dot">•</span>
                    <a href="{{ route('register') }}" class="auth-nav-link">Đăng ký</a>
                </div>
            </div>

            {{-- ========================================================
                 BƯỚC HOÀN TẤT: THÔNG BÁO THÀNH CÔNG
                 ======================================================== --}}
            <div class="step-pane" id="step-pane-success">
                <h1 class="auth-title">Đổi mật khẩu thành công!</h1>
                <p class="auth-desc">Mật khẩu của bạn đã được cập nhật thành công. Vui lòng đăng nhập bằng mật khẩu mới để tiếp tục hành trình học tập.</p>

                <div class="auth-btn-wrap">
                    <x-button
                        variant="primary"
                        size="medium"
                        href="{{ route('login') }}"
                        id="btn-back-to-login"
                    >
                        QUAY LẠI ĐĂNG NHẬP
                    </x-button>
                </div>

                <div class="auth-nav">
                    <a href="{{ route('login') }}" class="auth-nav-link">Đến trang đăng nhập</a>
                    <span class="auth-nav-dot">•</span>
                    <a href="{{ url('/') }}" class="auth-nav-link">Trang chủ</a>
                </div>
            </div>

        </div>

        {{-- Dòng bên ngoài card --}}
        <div class="auth-help" role="button" tabindex="0" onclick="alert('Nếu cần hỗ trợ thêm, vui lòng liên hệ bộ phận hỗ trợ StudyPrep qua email: support@studyprep.vn');">Cần thêm trợ giúp?</div>
    </div>

    {{-- Script điều khiển logic 3 bước, validation và mock server --}}
    <script>
    (function () {
        'use strict';

        // In-memory state (không lưu mã bí mật vào localStorage/sessionStorage)
        var appState = {
            currentStep: 1,
            email: '',
            code: '',
            isSubmitting: false
        };

        // DOM elements
        var step1Pane = document.getElementById('step-pane-1');
        var step2Pane = document.getElementById('step-pane-2');
        var step3Pane = document.getElementById('step-pane-3');
        var stepSuccessPane = document.getElementById('step-pane-success');
        var mainIcon = document.getElementById('auth-main-icon');
        var displayMaskedEmail = document.getElementById('display-masked-email');

        var formStep1 = document.getElementById('form-step-1');
        var formStep2 = document.getElementById('form-step-2');
        var formStep3 = document.getElementById('form-step-3');

        var btnStep1 = document.getElementById('btn-step-1');
        var btnStep2 = document.getElementById('btn-step-2');
        var btnStep3 = document.getElementById('btn-step-3');

        var btnBackToStep1 = document.getElementById('btn-back-to-step-1');
        var btnBackToStep2 = document.getElementById('btn-back-to-step-2');

        var inputEmail = document.getElementById('email');
        var inputCode = document.getElementById('verification_code');
        var inputNewPassword = document.getElementById('new_password');
        var inputConfirmPassword = document.getElementById('confirm_password');

        /**
         * Hàm hiển thị trạng thái lỗi cho component x-input có sẵn trong dự án.
         * Tái sử dụng chính xác các class CSS (.has-error, .sp-icon--error, .sp-caption--error)
         * của component resources/views/components/input.blade.php.
         */
        function setInputError(inputId, message) {
            var input = document.getElementById(inputId);
            if (!input) return;

            var control = input.closest('.sp-control');
            var field = input.closest('.sp-field');
            if (!control || !field) return;

            // Thêm viền đỏ báo lỗi
            control.classList.add('has-error');
            input.setAttribute('aria-invalid', 'true');

            // Thêm icon chấm than đỏ bên phải nếu chưa có
            var existingErrorIcon = control.querySelector('.sp-icon--error');
            if (!existingErrorIcon) {
                var errorIcon = document.createElement('span');
                errorIcon.className = 'sp-icon sp-icon--error';
                errorIcon.setAttribute('aria-hidden', 'true');
                errorIcon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>';

                // Nếu có nút toggle password, đặt icon lỗi trước nút toggle
                var toggleBtn = control.querySelector('[data-sp-toggle]');
                if (toggleBtn) {
                    control.insertBefore(errorIcon, toggleBtn);
                } else {
                    control.appendChild(errorIcon);
                }
            }

            // Hiển thị thông báo lỗi bên dưới input
            var desc = document.getElementById(inputId + '-desc');
            if (!desc) {
                var meta = field.querySelector('.sp-meta');
                if (!meta) {
                    meta = document.createElement('div');
                    meta.className = 'sp-meta';
                    field.appendChild(meta);
                }
                desc = document.createElement('span');
                desc.id = inputId + '-desc';
                meta.appendChild(desc);
            }
            desc.className = 'sp-caption sp-caption--error';
            desc.textContent = '! ' + message;
            desc.removeAttribute('hidden');
        }

        /**
         * Xóa trạng thái lỗi khỏi component x-input khi người dùng sửa dữ liệu.
         */
        function clearInputError(inputId) {
            var input = document.getElementById(inputId);
            if (!input) return;

            var control = input.closest('.sp-control');
            if (control) {
                control.classList.remove('has-error');
                var errorIcon = control.querySelector('.sp-icon--error');
                if (errorIcon) {
                    errorIcon.remove();
                }
            }

            input.removeAttribute('aria-invalid');

            var desc = document.getElementById(inputId + '-desc');
            if (desc) {
                desc.className = 'sp-caption';
                desc.textContent = '';
                desc.setAttribute('hidden', '');
            }
        }

        /**
         * Lắng nghe sự kiện gõ phím để xóa lỗi ngay khi người dùng chỉnh sửa.
         */
        [inputEmail, inputCode, inputNewPassword, inputConfirmPassword].forEach(function (inp) {
            if (!inp) return;
            inp.addEventListener('input', function () {
                clearInputError(inp.id);
            });
        });

        /**
         * Che một phần địa chỉ email an toàn để hiển thị tại bước 2.
         * Ví dụ: phunguyen1565@gmail.com -> phu**********n1565@gmailcom
         */
        function maskEmail(email) {
            if (!email || typeof email !== 'string' || email.indexOf('@') === -1) {
                return email;
            }
            var parts = email.split('@');
            var local = parts[0];
            var domain = parts.slice(1).join('@');

            if (local.length <= 3) {
                return local.charAt(0) + '***@' + domain;
            }
            if (local.length <= 6) {
                return local.slice(0, 2) + '****' + local.slice(-1) + '@' + domain;
            }

            var prefix = local.slice(0, 3);
            var suffix = local.slice(-5);
            var starsCount = Math.max(8, local.length - 8);
            var stars = '';
            for (var i = 0; i < starsCount; i++) {
                stars += '*';
            }
            return prefix + stars + suffix + '@' + domain;
        }

        /**
         * Chuyển đổi giao diện giữa các bước
         */
        function showStep(stepNumber) {
            appState.currentStep = stepNumber;

            [step1Pane, step2Pane, step3Pane, stepSuccessPane].forEach(function (pane) {
                if (pane) pane.classList.remove('is-active');
            });

            if (stepNumber === 1) {
                step1Pane.classList.add('is-active');
                setEnvelopeIcon();
                if (inputEmail) {
                    inputEmail.value = appState.email;
                    inputEmail.focus();
                }
            } else if (stepNumber === 2) {
                step2Pane.classList.add('is-active');
                setEnvelopeIcon();
                if (displayMaskedEmail) {
                    displayMaskedEmail.textContent = maskEmail(appState.email);
                }
                if (inputCode) {
                    inputCode.value = appState.code;
                    inputCode.focus();
                }
            } else if (stepNumber === 3) {
                step3Pane.classList.add('is-active');
                setEnvelopeIcon();
                if (inputNewPassword) {
                    inputNewPassword.focus();
                }
            } else if (stepNumber === 'success') {
                stepSuccessPane.classList.add('is-active');
                setSuccessIcon();
            }
        }

        function setEnvelopeIcon() {
            if (!mainIcon) return;
            mainIcon.className = 'auth-icon-circle';
            mainIcon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="3"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>';
        }

        function setSuccessIcon() {
            if (!mainIcon) return;
            mainIcon.className = 'auth-icon-circle auth-icon-circle--success';
            mainIcon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
        }

        /**
         * Quản lý trạng thái loading của nút gửi
         */
        function setButtonLoading(button, isLoading, loadingText, defaultText) {
            if (!button) return;
            button.disabled = isLoading;
            var textEl = button.querySelector('.btn-text');
            if (isLoading) {
                button.setAttribute('aria-busy', 'true');
                if (textEl) {
                    textEl.innerHTML = '<span class="sp-loading-spinner" aria-hidden="true"></span> ' + loadingText;
                }
            } else {
                button.removeAttribute('aria-busy');
                if (textEl) {
                    textEl.textContent = defaultText;
                }
            }
        }

        // ========================================================
        // XỬ LÝ BƯỚC 1: GỬI MÃ
        // ========================================================
        formStep1.addEventListener('submit', function (e) {
            e.preventDefault();
            if (appState.isSubmitting) return;

            var emailVal = (inputEmail.value || '').trim();

            // 1. Kiểm tra rỗng
            if (!emailVal) {
                setInputError('email', 'Vui lòng nhập email của bạn.');
                inputEmail.focus();
                return;
            }

            // 2. Kiểm tra định dạng email
            var emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
            if (!emailRegex.test(emailVal)) {
                setInputError('email', 'Email không đúng định dạng. Ví dụ: name@gmail.com');
                inputEmail.focus();
                return;
            }

            // 3. Cơ chế xác thực gửi mã (Mock Server có độ trễ thực tế)
            appState.isSubmitting = true;
            setButtonLoading(btnStep1, true, 'ĐANG GỬI MÃ...', 'GỬI MÃ');

            setTimeout(function () {
                appState.isSubmitting = false;
                setButtonLoading(btnStep1, false, '', 'GỬI MÃ');

                // Trường hợp giả lập test lỗi email không tồn tại nếu nhập domain @notfound.com hoặc error@
                if (emailVal.indexOf('@notfound.com') !== -1 || emailVal.indexOf('error@') !== -1) {
                    setInputError('email', 'Email này chưa được đăng ký trong hệ thống StudyPrep.');
                    inputEmail.focus();
                    return;
                }

                // Gửi mã thành công -> chuyển sang Bước 2
                appState.email = emailVal;
                clearInputError('email');
                showStep(2);
            }, 600);
        });

        // ========================================================
        // XỬ LÝ BƯỚC 2: XÁC MINH MÃ
        // ========================================================
        formStep2.addEventListener('submit', function (e) {
            e.preventDefault();
            if (appState.isSubmitting) return;

            var codeVal = (inputCode.value || '').trim();

            // 1. Kiểm tra đã nhập mã hay chưa
            if (!codeVal) {
                setInputError('verification_code', 'Vui lòng nhập mã xác minh.');
                inputCode.focus();
                return;
            }

            // 2. Kiểm tra độ dài mã
            if (codeVal.length < 4) {
                setInputError('verification_code', 'Mã xác minh phải có ít nhất 4 ký tự.');
                inputCode.focus();
                return;
            }

            // 3. Cơ chế xác minh mã (Mock Server)
            appState.isSubmitting = true;
            setButtonLoading(btnStep2, true, 'ĐANG XÁC MINH...', 'XÁC MINH');

            setTimeout(function () {
                appState.isSubmitting = false;
                setButtonLoading(btnStep2, false, '', 'XÁC MINH');

                // Trường hợp giả lập test mã sai (ví dụ nhập 0000 hoặc 999999)
                if (codeVal === '0000' || codeVal === '000000' || codeVal === '999999') {
                    setInputError('verification_code', 'Mã xác minh không chính xác hoặc đã hết hạn.');
                    inputCode.focus();
                    return;
                }

                // Xác minh thành công -> chuyển sang Bước 3
                appState.code = codeVal;
                clearInputError('verification_code');
                showStep(3);
            }, 600);
        });

        // Nút quay lại từ Bước 2 về Bước 1
        btnBackToStep1.addEventListener('click', function () {
            showStep(1);
        });

        // ========================================================
        // XỬ LÝ BƯỚC 3: ĐẶT LẠI MẬT KHẨU
        // ========================================================
        formStep3.addEventListener('submit', function (e) {
            e.preventDefault();
            if (appState.isSubmitting) return;

            var newPass = inputNewPassword.value || '';
            var confirmPass = inputConfirmPassword.value || '';

            var hasError = false;

            // 1. Kiểm tra mật khẩu mới
            if (!newPass) {
                setInputError('new_password', 'Vui lòng nhập mật khẩu mới.');
                hasError = true;
            } else if (newPass.length < 6) {
                // Quy tắc mật khẩu thống nhất với dự án (UserController: min:6)
                setInputError('new_password', 'Mật khẩu phải chứa ít nhất 6 ký tự.');
                hasError = true;
            } else {
                clearInputError('new_password');
            }

            // 2. Kiểm tra nhập lại mật khẩu
            if (!confirmPass) {
                setInputError('confirm_password', 'Vui lòng nhập lại mật khẩu mới.');
                hasError = true;
            } else if (newPass && newPass !== confirmPass) {
                setInputError('confirm_password', 'Mật khẩu nhập lại không khớp với mật khẩu mới.');
                hasError = true;
            } else {
                clearInputError('confirm_password');
            }

            if (hasError) {
                return;
            }

            // 3. Cơ chế gọi server cập nhật mật khẩu (Mock Server)
            appState.isSubmitting = true;
            setButtonLoading(btnStep3, true, 'ĐANG ĐỔI MẬT KHẨU...', 'ĐỔI MẬT KHẨU');

            setTimeout(function () {
                appState.isSubmitting = false;
                setButtonLoading(btnStep3, false, '', 'ĐỔI MẬT KHẨU');

                // Trường hợp giả lập test lỗi máy chủ khi đặt mật khẩu là "error123"
                if (newPass === 'error123') {
                    setInputError('new_password', 'Đã có lỗi từ máy chủ khi cập nhật mật khẩu. Vui lòng thử lại.');
                    inputNewPassword.focus();
                    return;
                }

                // Đổi mật khẩu thành công -> chuyển sang màn hình hoàn tất
                clearInputError('new_password');
                clearInputError('confirm_password');
                showStep('success');
            }, 750);
        });

        // Nút quay lại từ Bước 3 về Bước 2
        btnBackToStep2.addEventListener('click', function () {
            showStep(2);
        });

    })();
    </script>
</body>
</html>
