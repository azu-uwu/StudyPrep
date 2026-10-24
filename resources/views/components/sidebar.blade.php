@props([
    // Key của mục đang chọn; khớp với 'key' trong mảng menu bên dưới.
    'active' => 'tu-vung',
])

@php
    // Menu theo ảnh thiết kế: kết nối trực tiếp đến các route màn hình tương ứng
    $sections = [
        [
            'label' => 'HỌC',
            'items' => [
                ['key' => 'luyen-tap', 'label' => 'Luyện tập', 'href' => url('/luyen-tap')],
                ['key' => 'thi-thu', 'label' => 'Thi thử', 'href' => url('/thi-thu')],
                ['key' => 'tu-vung', 'label' => 'Từ vựng', 'href' => url('/tu-vung')],
            ],
        ],
        [
            'label' => 'TIẾN ĐỘ',
            'items' => [
                ['key' => 'phan-tich', 'label' => 'Phân tích', 'href' => url('/phan-tich')],
                ['key' => 'lich-su', 'label' => 'Lịch sử', 'href' => url('/lich-su')],
                ['key' => 'cai-thien', 'label' => 'Cải thiện', 'href' => url('/cai-thien')],
                ['key' => 'muc-tieu', 'label' => 'Mục tiêu', 'href' => url('/muc-tieu-trang-chu')],
            ],
        ],
    ];

    $active = is_string($active) ? $active : '';
@endphp

@once
    @if (! file_exists(public_path('build/manifest.json')) && ! file_exists(public_path('hot')))
        {{-- Chưa build asset nên font trong vite.config.js chưa được self-host: nạp tạm Nunito từ Bunny Fonts --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:700,800">
    @endif

    {{-- CSS riêng của Sidebar (không phụ thuộc build pipeline) --}}
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    {{--
        JS riêng của Sidebar: đóng/mở menu trượt trên điện thoại (< 768px).
        Vanilla JS, không thư viện ngoài. Chay sau khi DOM san sang vi element
        (.sp-sidebar-toggle/.sp-sidebar-backdrop) van chua duoc parse khi o day.
    --}}
    <script>
        (function () {
            'use strict';

            function initSidebar() {
                var aside = document.getElementById('sp-sidebar');
                var toggle = document.querySelector('.sp-sidebar-toggle');
                var backdrop = document.querySelector('.sp-sidebar-backdrop');

                if (!aside || !toggle || !backdrop) {
                    return;
                }

                var isOpen = false;

                function setOpen(next) {
                    isOpen = next;
                    // Lop duoc CSS media query < 768px su dung de hien/anh sidebar truot
                    document.body.classList.toggle('sp-sidebar-open', isOpen);
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    toggle.setAttribute('aria-label', isOpen ? 'Đóng menu' : 'Mở menu');
                }

                toggle.addEventListener('click', function () {
                    setOpen(!isOpen);
                });

                backdrop.addEventListener('click', function () {
                    setOpen(false);
                });

                // Nhan Esc de dong menu
                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && isOpen) {
                        setOpen(false);
                    }
                });

                // Chon 1 muc menu tren dien thoai -> tu dong dong sidebar
                aside.addEventListener('click', function (event) {
                    var target = event.target;
                    var link = target && target.closest ? target.closest('.sp-sidebar__link') : null;
                    if (link && isOpen) {
                        setOpen(false);
                    }
                });

                // Mo rong man hinh len tablet/laptop: sidebar tro lai binh thuong -> reset trang thai
                var query = window.matchMedia('(min-width: 768px)');
                var handleBreakpoint = function (event) {
                    if (event.matches && isOpen) {
                        setOpen(false);
                    }
                };

                if (typeof query.addEventListener === 'function') {
                    query.addEventListener('change', handleBreakpoint);
                } else if (typeof query.addListener === 'function') {
                    query.addListener(handleBreakpoint);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSidebar);
            } else {
                initSidebar();
            }
        })();
    </script>
@endonce

{{--
    Nut mo menu (chi hien tren dien thoai). Dat TRUOC <aside> vi tri fixed de
    khong anh huong layout, va de luat bu .sp-sidebar + * van cham vao noi dung chinh.
--}}
<button type="button" class="sp-sidebar-toggle" aria-label="Mở menu" aria-controls="sp-sidebar" aria-expanded="false">
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
        <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </svg>
</button>

{{-- Lop phu nen khi menu trượt dang mo (chi tren dien thoai; bam vao de dong) --}}
<button type="button" class="sp-sidebar-backdrop" aria-label="Đóng menu" tabindex="-1"></button>

<aside id="sp-sidebar" {{ $attributes->class(['sp-sidebar']) }}>
    {{-- ===== Nut dong (chi tren dien thoai, goc phai tren cua sidebar trượt) ===== --}}
    <button type="button" class="sp-sidebar__close" aria-label="Đóng menu">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
    </button>

    {{-- ===== Brand ===== --}}
    <a class="sp-sidebar__brand" href="#" aria-label="StudyPrep - Trang chủ">
    <img
        class="sp-sidebar__logo"
        src="{{ asset('images/logo.svg') }}"
        alt="StudyPrep"
    >

    <img
        class="sp-sidebar__mark"
        src="{{ asset('favicon.svg') }}"
        alt=""
        aria-hidden="true"
    >
</a>

    {{-- ===== Các nhóm menu ===== --}}
    <nav class="sp-sidebar__nav" aria-label="Điều hướng chính">
        @foreach ($sections as $section)
            <div class="sp-sidebar__section">
                <p class="sp-sidebar__heading">{{ $section['label'] }}</p>

                <ul class="sp-sidebar__list">
                    @foreach ($section['items'] as $item)
                        @php
                            $isActive = $active !== '' && $active === $item['key'];
                        @endphp
                        <li>
                            <a
                                href="{{ $item['href'] }}"
                                class="sp-sidebar__link{{ $isActive ? ' is-active' : '' }}"
                                title="{{ $item['label'] }}"
                                aria-label="{{ $item['label'] }}"
                                @if ($isActive) aria-current="page" @endif
                            >
                                <span class="sp-sidebar__icon" aria-hidden="true">
                                    @switch ($item['key'])
                                        @case('luyen-tap')
                                            {{-- Luyện tập: ngôi nhà xanh --}}
                                            <svg viewBox="0 0 24 24" fill="none" focusable="false">
                                                <path d="M11.29 2.66a1 1 0 0 1 1.42 0l8.03 7.62A1 1 0 0 1 21 11.13V19a2 2 0 0 1-2 2h-3.4v-4.6a4.4 4.4 0 0 0-8.8 0V21H5a2 2 0 0 1-2-2v-7.87a1 1 0 0 1 .6-.91z" fill="#2B7FFF" />
                                                <circle cx="12" cy="9.4" r="2.5" fill="#FFFFFF" />
                                            </svg>
                                        @break

                                        @case('thi-thu')
                                            {{-- Thi thử: ống nhòm xanh --}}
                                            <svg viewBox="0 0 24 24" fill="none" focusable="false">
                                                <rect x="3" y="3.2" width="7.6" height="17.6" rx="3.8" fill="#2B7FFF" />
                                                <rect x="13.4" y="3.2" width="7.6" height="17.6" rx="3.8" fill="#2B7FFF" />
                                                <circle cx="6.8" cy="7.8" r="1.7" fill="#FFFFFF" />
                                                <circle cx="6.8" cy="16.2" r="1.7" fill="#FFFFFF" />
                                                <circle cx="17.2" cy="7.8" r="1.7" fill="#FFFFFF" />
                                                <circle cx="17.2" cy="16.2" r="1.7" fill="#FFFFFF" />
                                            </svg>
                                        @break

                                        @case('tu-vung')
                                            {{-- Từ vựng: cuốn sách xanh + ngôi sao vàng --}}
                                            <svg viewBox="0 0 24 24" fill="none" focusable="false">
                                                <rect x="3.6" y="4.4" width="14.4" height="16.4" rx="2.8" fill="#2B7FFF" />
                                                <path d="M7.4 4.4v16.4" stroke="#FFFFFF" stroke-opacity="0.55" stroke-width="1.6" />
                                                <path d="M10.2 9h5.2M10.2 12.2h5.2M10.2 15.4h3.2" stroke="#FFFFFF" stroke-width="1.7" stroke-linecap="round" />
                                                <path d="m18.2 2.6.8 1.8 1.9.2-1.4 1.3.4 1.9-1.7-1-1.7 1 .4-1.9-1.4-1.3 1.9-.2.8-1.8Z" fill="#FFC53D" />
                                            </svg>
                                        @break


                                        @case('phan-tich')
                                            {{-- Phân tích: biểu đồ cột tím --}}
                                            <svg viewBox="0 0 24 24" fill="none" focusable="false">
                                                <circle cx="12" cy="12" r="9.4" fill="#8B5CF6" />
                                                <rect x="6.9" y="11.2" width="2.6" height="6.2" rx="1.3" fill="#FFFFFF" />
                                                <rect x="10.7" y="7.6" width="2.6" height="9.8" rx="1.3" fill="#FFFFFF" />
                                                <rect x="14.5" y="13" width="2.6" height="4.4" rx="1.3" fill="#FFFFFF" />
                                            </svg>
                                        @break

                                        @case('lich-su')
                                            {{-- Lịch sử: vòng lịch sử xanh dương --}}
                                            <svg viewBox="0 0 24 24" fill="none" focusable="false">
                                                <circle cx="12" cy="12" r="9.4" fill="#2BA6FF" />
                                                <path d="M6.9 9.7A6 6 0 1 1 6.2 13.4" stroke="#FFFFFF" stroke-width="1.9" stroke-linecap="round" />
                                                <path d="M6.6 6.2v3.7H10.3" stroke="#FFFFFF" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M12 9.6V12l2.2 1.3" stroke="#FFFFFF" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        @break

                                        @case('cai-thien')
                                            {{-- Cải thiện: tia chớp đỏ --}}
                                            <svg viewBox="0 0 24 24" fill="none" focusable="false">
                                                <circle cx="12" cy="12" r="9.4" fill="#FF4B3E" />
                                                <path d="M13.4 5.5 7.8 13.2h3.6l-.8 5.3 5.6-7.7h-3.6z" fill="#FFFFFF" />
                                            </svg>
                                        @break

                                        @case('muc-tieu')
                                            {{-- Mục tiêu: bia ngắm / hồng tâm xanh ngọc --}}
                                            <svg viewBox="0 0 24 24" fill="none" focusable="false">
                                                <circle cx="12" cy="12" r="9.4" fill="#10B981" />
                                                <circle cx="12" cy="12" r="6" stroke="#FFFFFF" stroke-width="1.8" />
                                                <circle cx="12" cy="12" r="2.2" fill="#FFFFFF" />
                                            </svg>
                                        @break
                                    @endswitch
                                </span>

                                <span class="sp-sidebar__label">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    {{-- ===== Chân sidebar: Hồ sơ & Đăng xuất ===== --}}
    <div class="sp-sidebar__footer">
        <nav aria-label="Tài khoản">
            <ul class="sp-sidebar__list">
                <li>
                    <a
                        href="{{ url('/ho-so') }}"
                        class="sp-sidebar__link{{ $active === 'ho-so' ? ' is-active' : '' }}"
                        title="Hồ sơ"
                        aria-label="Hồ sơ"
                        @if ($active === 'ho-so') aria-current="page" @endif
                    >
                        <span class="sp-sidebar__icon sp-sidebar__icon--sm" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" focusable="false">
                                <circle cx="12" cy="8.2" r="3.7" fill="#8B5CF6" />
                                <path d="M4.8 20.4a7.2 7.2 0 0 1 14.4 0c0 .9-.7 1.6-1.6 1.6H6.4a1.6 1.6 0 0 1-1.6-1.6Z" fill="#8B5CF6" />
                            </svg>
                        </span>

                        <span class="sp-sidebar__label">Hồ sơ</span>
                    </a>
                </li>
                <li>
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}" class="sp-sidebar__logout-form">
                            @csrf
                            <button type="submit" class="sp-sidebar__link sp-sidebar__link--logout" title="Đăng xuất" aria-label="Đăng xuất">
                                <span class="sp-sidebar__icon sp-sidebar__icon--sm" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                        <polyline points="16 17 21 12 16 7" />
                                        <line x1="21" y1="12" x2="9" y2="12" />
                                    </svg>
                                </span>

                                <span class="sp-sidebar__label">Đăng xuất</span>
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ Route::has('admin.logout') ? route('admin.logout') : url('/logout') }}" class="sp-sidebar__logout-form" onsubmit="if(!this.action.includes('admin/logout')){ event.preventDefault(); window.location.href='{{ url('/login') }}'; }">
                            @csrf
                            <button type="submit" class="sp-sidebar__link sp-sidebar__link--logout" title="Đăng xuất" aria-label="Đăng xuất">
                                <span class="sp-sidebar__icon sp-sidebar__icon--sm" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                        <polyline points="16 17 21 12 16 7" />
                                        <line x1="21" y1="12" x2="9" y2="12" />
                                    </svg>
                                </span>

                                <span class="sp-sidebar__label">Đăng xuất</span>
                            </button>
                        </form>
                    @endif
                </li>
            </ul>
        </nav>
    </div>
</aside>

