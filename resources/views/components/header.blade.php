{{--
    StudyPrep - Header component
    Vo ung dung tren man hinh chinh (ben phai Sidebar): Setting / Ho so / Thong bao / Ho tro.
    Quy uoc dat ten: tien to "sp-" (giong sp-btn, sp-sidebar, sp-control).
    Icon: SVG inline dung lai phong cach Sidebar/Input, khong can library icon moi.
    JS: vanilla, quan ly 2 dropdown (Thong bao, Ho tro) - mo 1 luc 1 cai, click ra ngoai/Esc la dong.
--}}

@props([
    // Mang thong bao mau; truyen prop `notifications` de ghi de khi can.
    'notifications' => null,
])

@php
    // Thong bao mau (chua gan voi backend). Moi muc: tieu de + thoi gian.
    $notifications = is_array($notifications) ? $notifications : [
        ['title' => 'Bài thi thử IELTS #12 của bạn đã có kết quả', 'time' => '2 giờ trước', 'unread' => true],
        ['title' => 'Hôm nay bạn còn 15 từ vựng cần ôn tập', 'time' => '5 giờ trước', 'unread' => true],
        ['title' => 'Nhắc nhở: duy trì chuỗi học 7 ngày nhé!', 'time' => 'Hôm qua', 'unread' => false],
        ['title' => 'Tính năng Phân tích tiến độ đã được cập nhật', 'time' => '2 ngày trước', 'unread' => false],
    ];

    // Hotline / Donate: chua co du lieu that trong he thong -> khong bia so, khong bia link.
    $hotline = null;
    $donateUrl = null;
@endphp

@once
    @if (! file_exists(public_path('build/manifest.json')) && ! file_exists(public_path('hot')))
        {{-- Chua build asset nen font trong vite.config.js chua duoc self-host: nap tam Nunito tu Bunny Fonts --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:700,800">
    @endif

    <style>
        .sp-header,
        .sp-header * {
            box-sizing: border-box;
        }

        .sp-header {
            position: sticky;
            top: 0;
            z-index: 90; /* duoi sidebar (z-index: 100) nhung tren noi dung trang */
            display: flex;
            align-items: center;
            justify-content: flex-end;
            height: 80px;
            padding: 0 28px;
            background: #FFFFFF;
            border-bottom: 1px solid #EEF1F7;
            font-family: 'Nunito', 'Instrument Sans', ui-sans-serif, system-ui, 'Segoe UI', sans-serif;
        }

        .sp-header__actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sp-header__dropdown-wrap {
            position: relative;
        }

        /* ===== Nut icon tron (Setting / Ho so / Thong bao) ===== */
        .sp-header__icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: transparent;
            cursor: pointer;
            transition: background-color 0.15s ease, transform 0.1s ease;
        }

        .sp-header__icon-btn:hover {
            background: #F3F7FD;
        }

        .sp-header__icon-btn:active {
            transform: scale(0.94);
        }

        .sp-header__icon-btn:focus-visible {
            outline: 3px solid rgba(0, 118, 254, 0.45);
            outline-offset: 2px;
        }

        .sp-header__icon-btn svg {
            display: block;
            width: 22px;
            height: 22px;
        }

        /* ===== Nut Ho tro (vien xanh + chu) ===== */
        .sp-header__support-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 42px;
            margin-left: 4px;
            padding: 0 16px 0 5px;
            border: 0;
            border-radius: 999px;
            background: #E9F2FE;
            color: #0076FE;
            font-family: inherit;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
            cursor: pointer;
            transition: background-color 0.15s ease, transform 0.1s ease;
        }

        .sp-header__support-btn:hover {
            background: #D7E8FF;
        }

        .sp-header__support-btn:active {
            transform: scale(0.97);
        }

        .sp-header__support-btn:focus-visible {
            outline: 3px solid rgba(0, 118, 254, 0.45);
            outline-offset: 2px;
        }

        .sp-header__support-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0076FE;
            flex: none;
        }

        .sp-header__support-icon svg {
            display: block;
            width: 17px;
            height: 17px;
        }

        /* ===== Bang dropdown (Thong bao / Ho tro) ===== */
        .sp-header__panel {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            z-index: 95;
            width: 320px;
            max-width: min(320px, calc(100vw - 32px));
            padding: 14px;
            background: #FFFFFF;
            border: 1px solid #EEF1F7;
            border-radius: 14px;
            box-shadow: 0 12px 32px rgba(17, 28, 45, 0.12);
        }

        .sp-header__panel[hidden] {
            display: none !important;
        }

        .sp-header__panel--support {
            width: 280px;
        }

        .sp-header__panel-title {
            margin: 0 0 10px;
            font-size: 13px;
            font-weight: 800;
            color: #111C2D;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* ===== Danh sach thong bao ===== */
        .sp-header__list {
            margin: 0;
            padding: 0;
            list-style: none;
            max-height: 320px;
            overflow-y: auto;
        }

        .sp-header__item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 8px;
            border-radius: 10px;
        }

        .sp-header__item:hover {
            background: #F9F9FF;
        }

        .sp-header__item-dot {
            width: 8px;
            height: 8px;
            margin-top: 6px;
            border-radius: 50%;
            background: #0076FE;
            flex: none;
        }

        .sp-header__item-dot--read {
            background: #CFDAF2;
        }

        .sp-header__item-title {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.45;
            color: #111C2D;
        }

        .sp-header__item-time {
            display: block;
            margin-top: 2px;
            font-size: 12px;
            font-weight: 600;
            color: #9AA5B7;
        }

        .sp-header__empty {
            margin: 0;
            padding: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #9AA5B7;
        }

        /* ===== Khoi Ho tro (Hotline / Donate) ===== */
        .sp-header__support-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 8px;
            font-size: 13px;
        }

        .sp-header__support-row + .sp-header__support-row {
            border-top: 1px solid #F3F4F8;
        }

        .sp-header__support-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            color: #3A4358;
        }

        .sp-header__support-label svg {
            display: block;
            width: 16px;
            height: 16px;
            flex: none;
        }

        .sp-header__support-value {
            font-weight: 700;
            color: #0076FE;
            text-decoration: none;
            text-align: right;
        }

        .sp-header__support-value--empty {
            font-weight: 600;
            color: #9AA5B7;
        }

        .sp-header__panel-note {
            margin: 8px 8px 2px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.5;
            color: #9AA5B7;
        }

        @media (max-width: 768px) {
            .sp-header {
                padding: 0 14px;
            }

            .sp-header__actions {
                gap: 4px;
            }
        }
    </style>
@endonce

<header class="sp-header" role="banner">
    <div class="sp-header__actions">

        {{-- ===== Hien thi luot thi hien tai (FR-030: toi da 5 luot) ===== --}}
        <div class="sp-attempt-pill" title="Số lượt thi thử khả dụng hiện tại: 5/5">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z" />
            </svg>
            <span>5/5 Lượt thi</span>
        </div>

        {{-- ===== Setting: lien ket den trang Cai dat ===== --}}
        <a href="{{ url('/cai-dat') }}" class="sp-header__icon-btn" title="Cài đặt" aria-label="Cài đặt">
            <svg viewBox="0 0 24 24" fill="#0076FE" focusable="false" aria-hidden="true">
                <path d="M19.14 12.94c.04-.31.06-.62.06-.94s-.02-.63-.07-.94l2.03-1.58a.49.49 0 0 0 .12-.61l-1.92-3.32a.49.49 0 0 0-.59-.22l-2.39.96a7.2 7.2 0 0 0-1.62-.94l-.36-2.54a.48.48 0 0 0-.48-.41h-3.84a.48.48 0 0 0-.48.41l-.36 2.54c-.58.24-1.12.55-1.62.94l-2.39-.96a.48.48 0 0 0-.59.22L2.71 8.47a.48.48 0 0 0 .12.61l2.03 1.58c-.05.31-.09.63-.09.94s.02.63.07.94l-2.03 1.58a.49.49 0 0 0-.12.61l1.92 3.32c.13.24.42.34.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.48-.41l.36-2.54c.58-.24 1.12-.56 1.62-.94l2.39.96c.22.09.47 0 .59-.22l1.92-3.32a.49.49 0 0 0-.12-.61l-2.05-1.58ZM12 15.6A3.6 3.6 0 1 1 15.6 12 3.61 3.61 0 0 1 12 15.6Z"/>
            </svg>
        </a>

        {{-- ===== Ho so: lien ket den trang Ho so ===== --}}
        <a href="{{ url('/ho-so') }}" class="sp-header__icon-btn" title="Hồ sơ" aria-label="Hồ sơ">
            <svg viewBox="0 0 24 24" fill="none" focusable="false" aria-hidden="true">
                <circle cx="12" cy="8.2" r="3.7" fill="#8B5CF6" />
                <path d="M4.8 20.4a7.2 7.2 0 0 1 14.4 0c0 .9-.7 1.6-1.6 1.6H6.4a1.6 1.6 0 0 1-1.6-1.6Z" fill="#8B5CF6" />
            </svg>
        </a>

        {{-- ===== Thong bao: nut icon + dropdown danh sach ===== --}}
        <div class="sp-header__dropdown-wrap">
            <button
                type="button"
                class="sp-header__icon-btn"
                title="Thông báo"
                aria-label="Thông báo"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="sp-header-notifications"
                data-sp-header-dropdown
            >
                <svg viewBox="0 0 24 24" fill="none" focusable="false" aria-hidden="true">
                    <path d="M12 3a6 6 0 0 0-6 6v3.2L4.4 15a1 1 0 0 0 .9 1.5h13.4a1 1 0 0 0 .9-1.5L18 12.2V9a6 6 0 0 0-6-6Z" fill="#FFB800" />
                    <path d="M9.8 18.5a2.3 2.3 0 0 0 4.4 0" stroke="#FFB800" stroke-width="1.9" stroke-linecap="round" />
                </svg>
            </button>

            <div id="sp-header-notifications" class="sp-header__panel" role="dialog" aria-label="Danh sách thông báo" hidden>
                <p class="sp-header__panel-title">Thông báo</p>

                @if (count($notifications) === 0)
                    <p class="sp-header__empty">Chưa có thông báo nào.</p>
                @else
                    <ul class="sp-header__list">
                        @foreach ($notifications as $notification)
                            <li class="sp-header__item">
                                <span class="sp-header__item-dot{{ empty($notification['unread']) ? ' sp-header__item-dot--read' : '' }}" aria-hidden="true"></span>
                                <div>
                                    <p class="sp-header__item-title">{{ $notification['title'] }}</p>
                                    <span class="sp-header__item-time">{{ $notification['time'] }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- ===== Ho tro: nut co chu + dropdown Hotline / Donate ===== --}}
        <div class="sp-header__dropdown-wrap">
            <button
                type="button"
                class="sp-header__support-btn"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="sp-header-support"
                data-sp-header-dropdown
            >
                <span class="sp-header__support-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" focusable="false">
                        <path d="M4 13a8 8 0 0 1 16 0" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" />
                        <rect x="3" y="12.5" width="4" height="7" rx="2" fill="#FFFFFF" />
                        <rect x="17" y="12.5" width="4" height="7" rx="2" fill="#FFFFFF" />
                    </svg>
                </span>
                <span>Hỗ trợ</span>
            </button>

            <div id="sp-header-support" class="sp-header__panel sp-header__panel--support" role="dialog" aria-label="Hỗ trợ" hidden>
                <p class="sp-header__panel-title">Hỗ trợ</p>

                <div class="sp-header__support-row">
                    <span class="sp-header__support-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#3A4358" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.8 2.1Z" />
                        </svg>
                        Hotline
                    </span>

                    @if ($hotline)
                        <a class="sp-header__support-value" href="tel:{{ $hotline }}">{{ $hotline }}</a>
                    @else
                        {{-- Chua co so hotline that -> hien trang thai chua cap nhat, khong bia so --}}
                        <span class="sp-header__support-value sp-header__support-value--empty">Chưa cập nhật</span>
                    @endif
                </div>

                <div class="sp-header__support-row">
                    <span class="sp-header__support-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#3A4358" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                            <rect x="2" y="5" width="20" height="14" rx="2" />
                            <path d="M2 10h20" />
                        </svg>
                        Donate
                    </span>

                    @if ($donateUrl)
                        <a class="sp-header__support-value" href="{{ $donateUrl }}" target="_blank" rel="noopener noreferrer">Đóng góp</a>
                    @else
                        {{-- Chua co link donate that -> hien trang thai chua cap nhat, khong bia link --}}
                        <span class="sp-header__support-value sp-header__support-value--empty">Chưa cập nhật</span>
                    @endif
                </div>

                <p class="sp-header__panel-note">Số hotline và liên kết donate sẽ được cập nhật sau.</p>
            </div>
        </div>

    </div>
</header>

@once
    <script>
        (function () {
            'use strict';

            var SELECTOR = '[data-sp-header-dropdown]';

            // Dong tat ca dropdown, bo qua nut dang truyen vao (neu co).
            function closeAll(exceptBtn) {
                document.querySelectorAll(SELECTOR).forEach(function (btn) {
                    if (btn === exceptBtn) {
                        return;
                    }

                    var panelId = btn.getAttribute('aria-controls');
                    var panel = panelId ? document.getElementById(panelId) : null;

                    if (panel) {
                        panel.hidden = true;
                    }

                    btn.setAttribute('aria-expanded', 'false');
                });
            }

            // Click nut -> mo/doi dropdown; chi mo 1 luc 1 cai (closeAll truoc khi mo).
            document.addEventListener('click', function (event) {
                var target = event.target;

                var btn = target && target.closest ? target.closest(SELECTOR) : null;

                if (btn) {
                    var panelId = btn.getAttribute('aria-controls');
                    var panel = panelId ? document.getElementById(panelId) : null;

                    if (!panel) {
                        return;
                    }

                    var willOpen = panel.hidden;

                    closeAll(btn);

                    panel.hidden = !willOpen;
                    btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

                    return;
                }

                // Click ngoai moi dropdown -> dong tat ca.
                if (!target || !target.closest || !target.closest('.sp-header__dropdown-wrap')) {
                    closeAll(null);
                }
            });

            // Nhan Esc de dong dropdown dang mo.
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeAll(null);
                }
            });
        })();
    </script>
@endonce
