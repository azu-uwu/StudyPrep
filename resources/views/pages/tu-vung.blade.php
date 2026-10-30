<x-layouts.app active="tu-vung" title="StudyPrep - Từ vựng">

@push('styles')
    {{-- Tái sử dụng CSS chuẩn từ Mục tiêu và Luyện tập --}}
    <link rel="stylesheet" href="{{ asset('css/pages/muc-tieu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/luyen-tap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/tu-vung.css') }}">
@endpush

{{-- ===== TIÊU ĐỀ TRANG (Đồng bộ chuẩn từ trang Mục tiêu) ===== --}}
<div class="mt-page-header">
    <h1 class="mt-page-title">Từ vựng</h1>
    <p class="mt-page-desc">Ghi nhớ từ vựng trọng tâm theo chủ đề. Hoàn thành bài học để hồi thêm lượt thi thử</p>
</div>

{{-- ===== THỐNG KÊ TỔNG QUAN ===== --}}
<div class="tv-stats-grid">
    {{-- Card lớn bên trái: Đã học / Nắm vững / Dễ quên --}}
    <div class="tv-stat-card-left-wrap">
        <div class="tv-stat-card-main">
            <div class="tv-stat-main-top">
                {{-- Icon 3 cuốn sách xếp nghiêng màu xanh --}}
                <div class="tv-book-stack-icon">
                    <svg width="44" height="42" viewBox="0 0 44 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Cuốn 1: Xanh nhạt nghiêng -->
                        <g transform="rotate(10 11 22)">
                            <rect x="6" y="7" width="8" height="26" rx="2" fill="#38BDF8" />
                            <rect x="6" y="11" width="8" height="2.2" fill="#FFFFFF" fill-opacity="0.85" />
                            <rect x="6" y="15" width="8" height="2.2" fill="#FFFFFF" fill-opacity="0.85" />
                        </g>
                        <!-- Cuốn 2: Xanh dương giữa -->
                        <g transform="rotate(10 21 22)">
                            <rect x="16" y="5" width="8" height="28" rx="2" fill="#0284C7" />
                            <rect x="16" y="10" width="8" height="2.2" fill="#FFFFFF" fill-opacity="0.85" />
                            <rect x="16" y="14" width="8" height="2.2" fill="#FFFFFF" fill-opacity="0.85" />
                        </g>
                        <!-- Cuốn 3: Xanh đậm phải -->
                        <g transform="rotate(10 31 22)">
                            <rect x="26" y="8" width="8" height="26" rx="2" fill="#0076FE" />
                            <rect x="26" y="12" width="8" height="2.2" fill="#FFFFFF" fill-opacity="0.85" />
                            <rect x="26" y="16" width="8" height="2.2" fill="#FFFFFF" fill-opacity="0.85" />
                        </g>
                    </svg>
                </div>

                <div class="tv-stat-main-number-wrap">
                    <span class="tv-stat-main-number" id="statLearnedCount">4</span>
                    <span class="tv-stat-main-label">ĐÃ HỌC</span>
                </div>
            </div>

            <div class="tv-stat-main-divider"></div>

            <div class="tv-stat-main-bottom">
                <div class="tv-stat-sub-col">
                    <span class="tv-stat-sub-label tv-stat-sub-label--green">NẮM VỮNG</span>
                    <span class="tv-stat-sub-value--green" id="statMasteredCount">3</span>
                </div>

                <div class="tv-stat-v-divider"></div>

                <div class="tv-stat-sub-col">
                    <span class="tv-stat-sub-label tv-stat-sub-label--red">DỄ QUÊN</span>
                    <span class="tv-stat-sub-value--red" id="statForgottenCount">1</span>
                </div>
            </div>
        </div>

        {{-- Link Xem chi tiết bên dưới card trái --}}
        <div class="tv-detail-link-wrap">
            <a href="javascript:void(0)" class="tv-detail-link">
                XEM CHI TIẾT <span class="tv-arrow">→</span>
            </a>
        </div>
    </div>

    {{-- 2 Card nhỏ xếp dọc bên phải --}}
    <div class="tv-stat-right-col">
        {{-- Card trên: 0 KIỂM TRA --}}
        <div class="tv-stat-card-small">
            <div class="tv-stat-small-icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    <circle cx="19" cy="5" r="2.5" fill="#94A3B8" stroke="none" />
                </svg>
            </div>
            <div class="tv-stat-small-content">
                <span class="tv-stat-small-number">0</span>
                <span class="tv-stat-small-label">KIỂM TRA</span>
            </div>
        </div>

        {{-- Card dưới: 4.4 ĐỘ THÀNH THẠO --}}
        <div class="tv-stat-card-small">
            <div class="tv-stat-small-icon">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                    <rect x="2" y="18" width="3.5" height="8" rx="1.75" fill="#F59E0B" />
                    <rect x="7.5" y="14" width="3.5" height="12" rx="1.75" fill="#F59E0B" />
                    <rect x="13" y="10" width="3.5" height="16" rx="1.75" fill="#F59E0B" />
                    <rect x="18.5" y="6" width="3.5" height="20" rx="1.75" fill="#0076FE" />
                    <rect x="24" y="2" width="3.5" height="24" rx="1.75" fill="#0076FE" />
                </svg>
            </div>
            <div class="tv-stat-small-content">
                <span class="tv-stat-small-number tv-stat-small-number--blue" id="statMasteryScore">4.4</span>
                <span class="tv-stat-small-label">ĐỘ THÀNH THẠO</span>
            </div>
        </div>
    </div>
</div>

{{-- ===== BANNER XANH DA TRỜI: ÔN LẠI TỪ VỰNG DỄ QUÊN ===== --}}
<div class="tv-review-banner">
    <div class="tv-review-brain-wrap">
        {{-- Icon não hồng kèm dây cắm điện --}}
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 12C15.8 12 14 13.8 14 16C12.3 16 11 17.3 11 19C11 19.5 11.1 20 11.3 20.4C9.9 21.3 9 22.8 9 24.5C9 26.4 10.1 28 11.7 28.7C11.3 29.5 11 30.5 11 31.5C11 34 13 36 15.5 36C16.4 36 17.2 35.7 17.9 35.2C18.8 36.9 20.6 38 22.6 38C25.4 38 27.6 35.9 27.9 33.2C28.6 33.7 29.5 34 30.5 34C32.4 34 34 32.4 34 30.5C34 29.8 33.8 29.1 33.4 28.6C34.9 27.7 36 26 36 24C36 21.8 34.6 20 32.6 19.3C32.9 18.6 33 17.8 33 17C33 14.2 30.8 12 28 12C26.9 12 25.8 12.4 25 13.1C24.1 12.4 23 12 21.8 12C20.6 12 19.5 12.4 18.6 13.1C18.4 12.5 18 12 18 12Z" fill="#F43F5E" />
            <path d="M16 22C18 20 21 21 21 23C21 26 17 26 17 28C17 30 20 31 22 30" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.9" />
            <path d="M28 20C26 21 25 23 26 25C27 27 30 26 30 28" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.9" />
            <path d="M24 38V42C24 43.1 24.9 44 26 44H34C35.1 44 36 43.1 36 42V39" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" />
            <rect x="33" y="34" width="6" height="5" rx="1.5" fill="#E0F2FE" stroke="#FFFFFF" stroke-width="1.5" />
            <line x1="34.5" y1="34" x2="34.5" y2="31" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" />
            <line x1="37.5" y1="34" x2="37.5" y2="31" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" />
        </svg>
    </div>

    <div class="tv-review-body">
        <h3 class="tv-review-title">Ôn lại 1 từ vựng dễ quên</h3>
        <x-button variant="neutral" size="small" style="color: #0284C7; font-weight: 800;">
            → BẮT ĐẦU
        </x-button>
    </div>
</div>

{{-- ===== SECTION 1: CHỦ ĐỀ CỦA TÔI (Đồng bộ cấu trúc .lt-section chuẩn) ===== --}}
<div class="lt-section">
    <div class="lt-section-header">
        <h2 class="lt-section-title">Chủ đề của tôi</h2>
        <a href="javascript:void(0)" class="lt-link-all">
            Xem tất cả
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>

    {{-- Trạng thái rỗng --}}
    <div class="tv-empty-box" id="myTopicsEmptyBox">
        <div class="tv-empty-icon">
            <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="8" y="10" width="4.5" height="24" rx="2.25" fill="#0EA5E9" />
                <rect x="15" y="12" width="4.5" height="22" rx="2.25" fill="#38BDF8" />
                <rect x="22" y="8" width="4.5" height="26" rx="2.25" fill="#0EA5E9" />
                <rect x="29" y="14" width="4.5" height="20" rx="2.25" fill="#0284C7" transform="rotate(12 29 14)" />
            </svg>
        </div>
        <p class="tv-empty-text">Bạn chưa có chủ đề nào</p>
        <x-button variant="primary" size="medium">
            + Tạo chủ đề
        </x-button>
    </div>

    {{-- Danh sách chủ đề đã tạo của tôi (ẩn khi rỗng, hiển thị khi thêm mới) --}}
    <div class="tv-my-topics-grid" id="myTopicsGrid" style="display: none;"></div>
</div>

{{-- ===== SECTION 2: CHỦ ĐỀ PHỔ BIẾN (Tái sử dụng .lt-vocab-grid từ Luyện tập) ===== --}}
<div class="lt-section">
    <div class="lt-section-header">
        <h2 class="lt-section-title">Chủ đề phổ biến</h2>
        <a href="javascript:void(0)" class="lt-link-all">
            Xem tất cả
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>

    {{-- Danh sách chủ đề phổ biến (Render từ mock data, Tactile 3D Button Interaction) --}}
    @php
        $popularList = $popularTopics ?? \App\Services\VocabularyMockData::getPopularTopics();
    @endphp
    <div class="lt-vocab-grid">
        @foreach($popularList as $topic)
            <a href="{{ route('tu-vung.show', $topic['topic_id']) }}"
               class="lt-vocab-card tv-tactile-card"
               tabindex="0"
               role="button"
               aria-label="{{ $topic['name'] }}">
                <div class="lt-vocab-cover-wrap">
                    @if(!empty($topic['thumbnail']))
                        <img src="{{ asset($topic['thumbnail']) }}"
                             alt="{{ $topic['name'] }}"
                             class="lt-vocab-cover"
                             loading="lazy">
                    @else
                        <div class="tv-default-badge-cover">
                            <span class="tv-badge-cover-title">{{ $topic['thumbnail_label'] ?? 'TOEIC' }}</span>
                        </div>
                    @endif
                </div>
                <span class="lt-vocab-name">{!! nl2br(e($topic['display_name'] ?? $topic['name'])) !!}</span>
            </a>
        @endforeach
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Giữ hiệu ứng Tactile 3D: khi click card lún xuống 1 nhịp ngắn trước khi chuyển trang
            document.querySelectorAll('.tv-tactile-card').forEach(function (card) {
                card.addEventListener('click', function (e) {
                    const targetUrl = this.getAttribute('href');
                    if (targetUrl && targetUrl !== 'javascript:void(0)' && !targetUrl.startsWith('#')) {
                        e.preventDefault();
                        this.classList.add('is-pressed');
                        setTimeout(function () {
                            window.location.href = targetUrl;
                        }, 120);
                    }
                });
            });
        });
    </script>
@endpush

</x-layouts.app>
