<x-layouts.app active="lich-su" title="StudyPrep - Lịch sử làm bài">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/lich-su.css') }}">
@endpush

{{-- 1. Thanh breadcrumb điều hướng & Công cụ phía trên --}}
<div class="ls-top-toolbar">
    <nav class="ls-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ url('/phan-tich') }}">Tiến độ</a>
        <span class="ls-breadcrumb-sep">›</span>
        <span class="ls-breadcrumb-current">Lịch sử làm bài</span>
    </nav>

    <div class="ls-top-actions">
        {{-- Ô tìm kiếm --}}
        <div class="ls-search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" class="ls-search-input" id="lsSearchInput" placeholder="Tìm kiếm bài tập, Part..." oninput="handleSearch(this.value)">
        </div>

        {{-- Nút chọn thời gian --}}
        <button type="button" class="ls-date-btn" id="lsDateBtn" onclick="toggleDateFilter()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span id="lsDateText">30 ngày qua</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>

        {{-- Nút xuất báo cáo --}}
        <button type="button" class="ls-export-btn" onclick="exportReport()">
            <svg viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            <span>Xuất báo cáo</span>
        </button>
    </div>
</div>

{{-- 2. Tiêu đề trang với icon đồng hồ tròn màu xanh --}}
<div class="ls-title-row">
    <div class="ls-title-icon-badge">
        <svg viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
    </div>
    <h1 class="ls-page-title">Lịch sử</h1>
</div>

{{-- 3. Bốn thẻ số liệu tổng hợp (Khớp 100% ảnh thiết kế) --}}
<div class="ls-stats-grid">
    {{-- Thẻ 1: Hoạt động ghi nhận --}}
    <div class="ls-stat-card">
        <div class="ls-stat-content">
            <p class="ls-stat-label">Hoạt động ghi nhận</p>
            <div class="ls-stat-value-wrap">
                <span class="ls-stat-number">8</span>
                <span class="ls-stat-unit">lượt làm bài</span>
            </div>
        </div>
        <div class="ls-stat-icon-wrap ls-stat-icon-wrap--blue">
            <svg viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        </div>
    </div>

    {{-- Thẻ 2: Tỉ lệ đúng TB --}}
    <div class="ls-stat-card">
        <div class="ls-stat-content">
            <p class="ls-stat-label">Tỉ lệ đúng TB</p>
            <div class="ls-stat-value-wrap">
                <span class="ls-stat-number ls-stat-number--primary">58%</span>
                <span class="ls-stat-badge">↑ +5.2%</span>
            </div>
        </div>
        <div class="ls-stat-icon-wrap ls-stat-icon-wrap--purple">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 14 14"></polyline>
            </svg>
        </div>
    </div>

    {{-- Thẻ 3: Thời gian học tập --}}
    <div class="ls-stat-card">
        <div class="ls-stat-content">
            <p class="ls-stat-label">Thời gian học tập</p>
            <div class="ls-stat-value-wrap">
                <span class="ls-stat-number">3h 45m</span>
            </div>
        </div>
        <div class="ls-stat-icon-wrap ls-stat-icon-wrap--amber">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 15 15"></polyline>
            </svg>
        </div>
    </div>

    {{-- Thẻ 4: Câu sai tích lũy --}}
    <div class="ls-stat-card ls-stat-card--danger">
        <div class="ls-stat-content">
            <p class="ls-stat-label">Câu sai tích lũy</p>
            <div class="ls-stat-value-wrap">
                <span class="ls-stat-number ls-stat-number--danger">13</span>
                <span class="ls-stat-unit">câu</span>
            </div>
        </div>
        <button type="button" class="ls-review-now-btn" onclick="reviewWeakPoints()">
            Ôn ngay
        </button>
    </div>
</div>

{{-- 4. Thanh lọc theo loại bài & Sắp xếp --}}
<div class="ls-filter-sort-row">
    <div class="ls-filter-pills" role="tablist">
        <button type="button" class="ls-pill-item is-active" data-filter="all" onclick="filterActivity('all', this)">
            <span>Tất cả</span>
            <span class="ls-pill-badge">8</span>
        </button>
        <button type="button" class="ls-pill-item" data-filter="lesson" onclick="filterActivity('lesson', this)">
            <span>Bài học</span>
            <span class="ls-pill-badge">8</span>
        </button>
        <button type="button" class="ls-pill-item" data-filter="exam" onclick="filterActivity('exam', this)">
            <span>Luyện đề</span>
            <span class="ls-pill-badge">0</span>
        </button>

        <select class="ls-filter-select-pill" id="lsPartFilter" onchange="filterByPart(this.value)" aria-label="Lọc theo Part">
            <option value="all">Part (Tất cả)</option>
            <option value="part3">Part 3: Hội thoại ngắn</option>
            <option value="part4">Part 4: Bài nói ngắn</option>
            <option value="part5">Part 5: Hoàn thành câu</option>
            <option value="flashcard">Flashcard Từ vựng</option>
        </select>
    </div>

    <div style="display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 13.5px; color: #64748B; font-weight: 600;">Sắp xếp:</span>
        <select class="ls-filter-select-pill" style="border: none; padding: 4px 8px; font-weight: 700; color: #1E293B;" onchange="sortActivities(this.value)">
            <option value="newest">Mới nhất trước</option>
            <option value="oldest">Cũ nhất trước</option>
            <option value="score_desc">Điểm cao nhất</option>
            <option value="score_asc">Điểm thấp nhất</option>
        </select>
    </div>
</div>

{{-- Dòng mô tả số lượng --}}
<p class="ls-count-subtitle" id="lsCountSubtitle">Hiển thị 8 trong 8 hoạt động</p>

{{-- 5. Danh sách các bài đã làm gom nhóm theo mốc thời gian (Timeline) --}}
<div id="lsActivitiesContainer">

    {{-- Nhóm 1: HÔM NAY --}}
    <div class="ls-timeline-group" data-group="today">
        <div class="ls-group-header">
            <svg class="ls-group-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span class="ls-group-title">HÔM NAY</span>
            <div class="ls-group-line"></div>
        </div>

        {{-- Bài 1: Part 5: Hoàn thành câu --}}
        <div class="ls-activity-card" data-part="part5" data-type="lesson" data-title="Part 5: Hoàn thành câu">
            <div class="ls-activity-left">
                <div class="ls-activity-icon ls-activity-icon--part5">
                    {{-- Icon Checklist & Bút chì --}}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                </div>
                <div class="ls-activity-details">
                    <div class="ls-activity-title-row">
                        <h3 class="ls-activity-title">Part 5: Hoàn thành câu</h3>
                        <span class="ls-badge-wrong">1 câu sai</span>
                    </div>
                    <p class="ls-activity-submeta">0/1 câu đúng</p>
                    <p class="ls-activity-timestamp-row">
                        <span>16:56 • 10/10</span>
                        <span class="ls-delta--negative">-40% so với lần trước</span>
                    </p>
                </div>
            </div>

            <div class="ls-activity-right">
                <div class="ls-rate-box">
                    <span class="ls-rate-label">TỈ LỆ ĐÚNG</span>
                    <span class="ls-rate-value">0%</span>
                </div>
                <div class="ls-action-buttons">
                    <button type="button" class="ls-btn-text" onclick="viewResultDetail('part5')">Xem Kết Quả</button>
                    <button type="button" class="ls-btn-outline" onclick="window.location.href='{{ url('/luyen-tap/doc-cau') }}'">Ôn lại</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Nhóm 2: 7 NGÀY TRƯỚC --}}
    <div class="ls-timeline-group" data-group="7days">
        <div class="ls-group-header">
            <svg class="ls-group-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span class="ls-group-title">7 NGÀY TRƯỚC</span>
            <div class="ls-group-line"></div>
        </div>

        {{-- Bài 2: Part 4: Bài nói chuyện ngắn --}}
        <div class="ls-activity-card" data-part="part4" data-type="lesson" data-title="Part 4: Bài nói chuyện ngắn">
            <div class="ls-activity-left">
                <div class="ls-activity-icon ls-activity-icon--part4">
                    {{-- Icon bục phát biểu / Bài nói --}}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="7" r="4"></circle>
                        <path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"></path>
                    </svg>
                </div>
                <div class="ls-activity-details">
                    <div class="ls-activity-title-row">
                        <h3 class="ls-activity-title">Part 4: Bài nói chuyện ngắn</h3>
                        <span class="ls-badge-wrong">8 câu sai</span>
                    </div>
                    <p class="ls-activity-submeta">7/15 câu đúng</p>
                    <p class="ls-activity-timestamp-row">
                        <span>02:27 • 03/10</span>
                        <span class="ls-delta--neutral">• Lần đầu</span>
                    </p>
                </div>
            </div>

            <div class="ls-activity-right">
                <div class="ls-rate-box">
                    <span class="ls-rate-label">TỈ LỆ ĐÚNG</span>
                    <span class="ls-rate-value">46%</span>
                </div>
                <div class="ls-action-buttons">
                    <button type="button" class="ls-btn-text" onclick="viewResultDetail('part4')">Xem Kết Quả</button>
                    <button type="button" class="ls-btn-outline" onclick="window.location.href='{{ url('/luyen-tap/noi') }}'">Ôn lại</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Nhóm 3: 8 NGÀY TRƯỚC --}}
    <div class="ls-timeline-group" data-group="8days">
        <div class="ls-group-header">
            <svg class="ls-group-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span class="ls-group-title">8 NGÀY TRƯỚC</span>
            <div class="ls-group-line"></div>
        </div>

        {{-- Bài 3: Part 3: Hội thoại ngắn --}}
        <div class="ls-activity-card" data-part="part3" data-type="lesson" data-title="Part 3: Hội thoại ngắn">
            <div class="ls-activity-left">
                <div class="ls-activity-icon ls-activity-icon--part3">
                    {{-- Icon bong bóng hội thoại --}}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <div class="ls-activity-details">
                    <div class="ls-activity-title-row">
                        <h3 class="ls-activity-title">Part 3: Hội thoại ngắn</h3>
                        <span class="ls-badge-wrong">4 câu sai</span>
                    </div>
                    <p class="ls-activity-submeta">11/15 câu đúng</p>
                    <p class="ls-activity-timestamp-row">
                        <span>19:40 • 02/10</span>
                        <span class="ls-delta--positive">+15% so với lần trước</span>
                    </p>
                </div>
            </div>

            <div class="ls-activity-right">
                <div class="ls-rate-box">
                    <span class="ls-rate-label">TỈ LỆ ĐÚNG</span>
                    <span class="ls-rate-value">73%</span>
                </div>
                <div class="ls-action-buttons">
                    <button type="button" class="ls-btn-text" onclick="viewResultDetail('part3')">Xem Kết Quả</button>
                    <button type="button" class="ls-btn-outline" onclick="window.location.href='{{ url('/luyen-tap/doc?skill=nghe') }}'">Ôn lại</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Nhóm 4: 14 NGÀY TRƯỚC --}}
    <div class="ls-timeline-group" data-group="14days">
        <div class="ls-group-header">
            <svg class="ls-group-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span class="ls-group-title">14 NGÀY TRƯỚC</span>
            <div class="ls-group-line"></div>
        </div>

        {{-- Bài 4: Flashcard từ vựng --}}
        <div class="ls-activity-card" data-part="flashcard" data-type="lesson" data-title="Flashcard: 80 từ trọng tâm ETS 2024 RC">
            <div class="ls-activity-left">
                <div class="ls-activity-icon ls-activity-icon--flashcard">
                    {{-- Icon Sách mở --}}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <div class="ls-activity-details">
                    <div class="ls-activity-title-row">
                        <h3 class="ls-activity-title">Flashcard: 80 từ trọng tâm ETS 2024 RC</h3>
                        <span class="ls-badge-success">Đã thuộc 68 từ</span>
                    </div>
                    <p class="ls-activity-submeta">Hoàn thành 100% vòng lặp Spaced Repetition</p>
                    <p class="ls-activity-timestamp-row">
                        <span>08:15 • 26/09</span>
                        <span class="ls-delta--neutral">• Ôn định kỳ</span>
                    </p>
                </div>
            </div>

            <div class="ls-activity-right">
                <div class="ls-rate-box">
                    <span class="ls-rate-label">TỈ LỆ NHỚ</span>
                    <span class="ls-rate-value ls-rate-value--green">85%</span>
                </div>
                <div class="ls-action-buttons">
                    <button type="button" class="ls-btn-text" onclick="viewVocabularyList()">Xem danh sách</button>
                    <button type="button" class="ls-btn-outline" onclick="window.location.href='{{ url('/tu-vung') }}'">Luyện thẻ nhớ</button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- MODAL XEM CHI TIẾT BÀI LÀM & ĐÁP ÁN --}}
<div class="ls-modal-backdrop" id="lsModalBackdrop" onclick="handleModalBackdropClick(event)">
    <div class="ls-modal" role="dialog" aria-modal="true" aria-labelledby="lsModalTitle">
        <div class="ls-modal__header">
            <div>
                <h3 class="ls-modal__title" id="lsModalTitle">Chi tiết bài làm</h3>
                <span id="lsModalSubtitle" style="font-size: 13px; color: #64748B; font-weight: 600;">Xem lại đáp án và giải thích</span>
            </div>
            <button type="button" class="ls-modal__close" onclick="closeResultModal()" aria-label="Đóng cửa sổ">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="ls-modal__body" id="lsModalBody">
            {{-- Nội dung chi tiết bài làm render bằng JS --}}
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Dữ liệu chi tiết bài làm
    const detailData = {
        'part5': {
            title: 'Chi tiết Part 5: Hoàn thành câu',
            subtitle: 'Thời gian nộp: 16:56 • 10/10 • Tỉ lệ đúng: 0%',
            html: `
                <div style="background: #FFF5F5; border: 1.5px solid #FEE2E2; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
                    <div style="font-size: 13px; font-weight: 800; color: #DC2626; margin-bottom: 6px;">❌ CÂU HỎI 101 (CHƯA CHÍNH XÁC)</div>
                    <p style="font-size: 15px; font-weight: 700; color: #111C2D; margin: 0 0 12px; line-height: 1.5;">
                        The board members decided to postpone the quarterly audit _______ the financial statements were verified.
                    </p>
                    <div style="font-size: 13.5px; line-height: 1.6; color: #334155;">
                        <div>• Đáp án của bạn: <strong style="color: #DC2626;">A. during</strong></div>
                        <div>• Đáp án chính xác: <strong style="color: #15803D;">C. until</strong></div>
                        <div style="margin-top: 10px; padding: 10px 14px; background: #FFFFFF; border-radius: 8px; border: 1px solid #FEE2E2;">
                            💡 <strong>Giải thích:</strong> Mệnh đề phía sau có chủ ngữ và vị ngữ ("the financial statements were verified") nên cần liên từ chỉ thời gian "until" (cho đến khi), trong khi "during" là giới từ chỉ đi với danh từ/cụm danh từ.
                        </div>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="ls-btn-outline" onclick="closeResultModal()">Đóng</button>
                    <button type="button" class="ls-review-now-btn" style="background: #0076FE;" onclick="window.location.href='{{ url('/luyen-tap/doc-cau') }}'">Luyện lại câu này</button>
                </div>
            `
        },
        'part4': {
            title: 'Chi tiết Part 4: Bài nói chuyện ngắn',
            subtitle: 'Thời gian nộp: 02:27 • 03/10 • Tỉ lệ đúng: 46% (7/15 câu đúng)',
            html: `
                <div style="background: #F8FAFC; border: 1.5px solid #EEF1F7; border-radius: 14px; padding: 18px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-size: 14px; font-weight: 800; color: #1E293B;">Đánh giá theo chủ đề bài nói</span>
                        <span style="font-size: 13px; font-weight: 700; color: #6366F1;">Audio: Thông báo công ty & Tin tức</span>
                    </div>
                    <ul style="margin: 0; padding-left: 20px; font-size: 13.5px; color: #475569; line-height: 1.8;">
                        <li>Câu hỏi mục đích bài nói (Main idea): 3/3 câu đúng (100%)</li>
                        <li>Câu hỏi chi tiết cụ thể (Specific details): 3/7 câu đúng (42% - Cần cải thiện)</li>
                        <li>Câu hỏi ngụ ý (Inference & Speaker purpose): 1/5 câu đúng (20% - Cần nghe kỹ ngữ điệu)</li>
                    </ul>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="ls-btn-outline" onclick="closeResultModal()">Đóng</button>
                    <button type="button" class="ls-review-now-btn" style="background: #0076FE;" onclick="window.location.href='{{ url('/luyen-tap/noi') }}'">Nghe lại bài nói</button>
                </div>
            `
        },
        'part3': {
            title: 'Chi tiết Part 3: Hội thoại ngắn',
            subtitle: 'Thời gian nộp: 19:40 • 02/10 • Tỉ lệ đúng: 73% (11/15 câu đúng)',
            html: `
                <div style="background: #F0FDF4; border: 1.5px solid #BBF7D0; border-radius: 14px; padding: 18px; margin-bottom: 16px;">
                    <div style="font-size: 15px; font-weight: 800; color: #166534; margin-bottom: 8px;">🎉 Tiến bộ vượt bậc: +15% so với lần làm trước!</div>
                    <p style="font-size: 13.5px; color: #166534; margin: 0 0 10px; line-height: 1.5;">
                        Bạn đã cải thiện rõ rệt ở các câu hỏi nhận diện đối tượng nói và địa điểm cuộc trò chuyện văn phòng.
                    </p>
                    <div style="font-size: 13px; color: #15803D; font-weight: 700;">• 4 câu sai tập trung ở đoạn nối liên quan đến thời gian và lịch trình chuyến bay.</div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="ls-btn-outline" onclick="closeResultModal()">Đóng</button>
                    <button type="button" class="ls-review-now-btn" style="background: #0076FE;" onclick="window.location.href='{{ url('/luyen-tap/doc?skill=nghe') }}'">Luyện tập tiếp</button>
                </div>
            `
        }
    };

    function viewResultDetail(partKey) {
        const data = detailData[partKey] || detailData['part5'];
        document.getElementById('lsModalTitle').textContent = data.title;
        document.getElementById('lsModalSubtitle').textContent = data.subtitle;
        document.getElementById('lsModalBody').innerHTML = data.html;
        document.getElementById('lsModalBackdrop').classList.add('is-open');
    }

    function viewVocabularyList() {
        document.getElementById('lsModalTitle').textContent = 'Flashcard: 80 từ trọng tâm ETS 2024 RC';
        document.getElementById('lsModalSubtitle').textContent = 'Đã thuộc 68/80 từ (Tỉ lệ nhớ 85%)';
        document.getElementById('lsModalBody').innerHTML = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div style="background: #F8FAFC; border: 1px solid #EEF1F7; border-radius: 10px; padding: 12px;">
                    <div style="font-weight: 800; color: #111C2D;">accommodate (v)</div>
                    <div style="font-size: 13px; color: #64748B;">đáp ứng, cung cấp chỗ ở</div>
                </div>
                <div style="background: #F8FAFC; border: 1px solid #EEF1F7; border-radius: 10px; padding: 12px;">
                    <div style="font-weight: 800; color: #111C2D;">comprehensive (adj)</div>
                    <div style="font-size: 13px; color: #64748B;">toàn diện, bao quát</div>
                </div>
                <div style="background: #F8FAFC; border: 1px solid #EEF1F7; border-radius: 10px; padding: 12px;">
                    <div style="font-weight: 800; color: #111C2D;">mandatory (adj)</div>
                    <div style="font-size: 13px; color: #64748B;">bắt buộc theo quy định</div>
                </div>
                <div style="background: #F8FAFC; border: 1px solid #EEF1F7; border-radius: 10px; padding: 12px;">
                    <div style="font-weight: 800; color: #111C2D;">revenue (n)</div>
                    <div style="font-size: 13px; color: #64748B;">doanh thu, lợi tức</div>
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="ls-btn-outline" onclick="closeResultModal()">Đóng</button>
                <button type="button" class="ls-review-now-btn" style="background: #0284C7;" onclick="window.location.href='{{ url('/tu-vung') }}'">Luyện ngay 12 từ còn lại</button>
            </div>
        `;
        document.getElementById('lsModalBackdrop').classList.add('is-open');
    }

    function closeResultModal() {
        document.getElementById('lsModalBackdrop').classList.remove('is-open');
    }

    function handleModalBackdropClick(e) {
        if (e.target.id === 'lsModalBackdrop') {
            closeResultModal();
        }
    }

    // Lọc theo nút Tab Pills
    function filterActivity(type, btn) {
        document.querySelectorAll('.ls-pill-item').forEach(p => p.classList.remove('is-active'));
        btn.classList.add('is-active');

        const cards = document.querySelectorAll('.ls-activity-card');
        let count = 0;

        cards.forEach(card => {
            const cardType = card.dataset.type;
            if (type === 'all' || cardType === type) {
                card.style.display = 'flex';
                count++;
            } else {
                card.style.display = 'none';
            }
        });

        updateTimelineVisibility();
        document.getElementById('lsCountSubtitle').textContent = `Hiển thị ${count} trong 8 hoạt động`;
    }

    // Lọc theo Part dropdown
    function filterByPart(part) {
        const cards = document.querySelectorAll('.ls-activity-card');
        let count = 0;

        cards.forEach(card => {
            const cardPart = card.dataset.part;
            if (part === 'all' || cardPart === part) {
                card.style.display = 'flex';
                count++;
            } else {
                card.style.display = 'none';
            }
        });

        updateTimelineVisibility();
        document.getElementById('lsCountSubtitle').textContent = `Hiển thị ${count} trong 8 hoạt động`;
    }

    // Tìm kiếm bài tập thời gian thực
    function handleSearch(keyword) {
        keyword = keyword.toLowerCase().trim();
        const cards = document.querySelectorAll('.ls-activity-card');
        let count = 0;

        cards.forEach(card => {
            const title = card.dataset.title.toLowerCase();
            if (!keyword || title.includes(keyword)) {
                card.style.display = 'flex';
                count++;
            } else {
                card.style.display = 'none';
            }
        });

        updateTimelineVisibility();
        document.getElementById('lsCountSubtitle').textContent = `Hiển thị ${count} trong 8 hoạt động`;
    }

    function updateTimelineVisibility() {
        document.querySelectorAll('.ls-timeline-group').forEach(group => {
            const visibleCards = group.querySelectorAll('.ls-activity-card:not([style*="display: none"])');
            group.style.display = (visibleCards.length > 0) ? 'block' : 'none';
        });
    }

    function sortActivities(order) {
        const container = document.getElementById('lsActivitiesContainer');
        const groups = Array.from(container.querySelectorAll('.ls-timeline-group'));

        if (order === 'oldest') {
            groups.reverse().forEach(g => container.appendChild(g));
        } else {
            // Newest: Khôi phục thứ tự ban đầu
            const orderKeys = ['today', '7days', '8days', '14days'];
            orderKeys.forEach(k => {
                const g = container.querySelector(`.ls-timeline-group[data-group="${k}"]`);
                if (g) container.appendChild(g);
            });
        }
    }

    function toggleDateFilter() {
        const currentText = document.getElementById('lsDateText').textContent;
        const options = ['30 ngày qua', '7 ngày qua', 'Hôm nay', 'Tất cả'];
        const curIdx = options.indexOf(currentText);
        const nextText = options[(curIdx + 1) % options.length];
        document.getElementById('lsDateText').textContent = nextText;
    }

    function reviewWeakPoints() {
        window.location.href = '{{ url('/cai-thien') }}';
    }

    function exportReport() {
        alert('📊 Báo cáo tiến độ và lịch sử làm bài đang được xuất ra định dạng PDF. Quá trình tải sẽ tự động bắt đầu!');
    }
</script>
@endpush

</x-layouts.app>
