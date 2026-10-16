@props([
    // Key của mục đang chọn; khớp với 'key' trong mảng menu bên dưới.
    'active' => 'tu-vung',
])

@php
    // Menu theo ảnh thiết kế. Chưa có route thật nên href tạm là "#";
    // sau này chỉ cần thay 'href' từng mục là điều hướng được.
    $sections = [
        [
            'label' => 'HỌC',
            'items' => [
                ['key' => 'luyen-tap', 'label' => 'Luyện tập', 'href' => '#'],
                ['key' => 'thi-thu', 'label' => 'Thi thử', 'href' => '#'],
                ['key' => 'tu-vung', 'label' => 'Từ vựng', 'href' => '#'],
            ],
        ],
        [
            'label' => 'TIẾN ĐỘ',
            'items' => [
                ['key' => 'phan-tich', 'label' => 'Phân tích', 'href' => '#'],
                ['key' => 'lich-su', 'label' => 'Lịch sử', 'href' => '#'],
                ['key' => 'cai-thien', 'label' => 'Cải thiện', 'href' => '#'],
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
@endonce

<aside {{ $attributes->class(['sp-sidebar']) }}>
    {{-- ===== Brand ===== --}}
    <a class="sp-sidebar__brand" href="#" aria-label="StudyPrep - Trang chủ">
        <svg class="sp-sidebar__mark" viewBox="0 0 36 40" fill="none" aria-hidden="true" focusable="false">
            <defs>
                <linearGradient id="sp-sidebar-mark-gradient" x1="12" y1="10" x2="32" y2="36" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#63B8FF" />
                    <stop offset="0.55" stop-color="#1A86FF" />
                    <stop offset="1" stop-color="#0057BF" />
                </linearGradient>
            </defs>

            {{-- Nón tốt nghiệp --}}
            <path d="M10 3.4 1.2 7.6a.6.6 0 0 0 0 1.1l8.8 4.2a1 1 0 0 0 1 0l8.8-4.2a.6.6 0 0 0 0-1.1L11 3.4a1 1 0 0 0-1 0Z" fill="#14284F" />
            <path d="M5.6 9.8v3.9c0 1.6 2 2.9 4.5 2.9" fill="none" stroke="#14284F" stroke-width="2" stroke-linecap="round" />

            {{-- Letter S cách điệu dạng swoosh --}}
            <path
                d="M30.5 15.5c-2-4-7.5-5.5-12-4-4.5 1.5-6.5 5-4.5 8 2 2.9 7 3.3 11.6 4.5 4.7 1.2 7.4 3.7 6.4 7.2-1.1 3.8-6.5 5.8-11.5 4.8-3.8-.8-6.1-3-6.7-5.6"
                stroke="url(#sp-sidebar-mark-gradient)"
                stroke-width="5"
                stroke-linecap="round"
            />
        </svg>

        <span class="sp-sidebar__word">
            <span class="sp-sidebar__word-study">tudy</span><span class="sp-sidebar__word-prep">PREP</span>
        </span>
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

    {{-- ===== Chân sidebar: Hồ sơ ===== --}}
    <div class="sp-sidebar__footer">
        <nav aria-label="Tài khoản">
            <ul class="sp-sidebar__list">
                <li>
                    <a href="#" class="sp-sidebar__link">
                        <span class="sp-sidebar__icon sp-sidebar__icon--sm" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" focusable="false">
                                <circle cx="12" cy="8.2" r="3.7" fill="#8B5CF6" />
                                <path d="M4.8 20.4a7.2 7.2 0 0 1 14.4 0c0 .9-.7 1.6-1.6 1.6H6.4a1.6 1.6 0 0 1-1.6-1.6Z" fill="#8B5CF6" />
                            </svg>
                        </span>

                        <span class="sp-sidebar__label">Hồ sơ</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>

