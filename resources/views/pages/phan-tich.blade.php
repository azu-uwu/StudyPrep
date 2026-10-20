<x-layouts.app active="phan-tich" title="StudyPrep - Phân tích kết quả học tập">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/phan-tich.css') }}">
@endpush

{{-- Điều hướng quay lại Trang tổng quan Phân tích --}}
<div style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 600; margin-bottom: 16px;">
    <a href="{{ url('/phan-tich') }}" style="color: #64748B; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span>Tổng quan Phân tích</span>
    </a>
    <span style="color: #CBD5E1;">›</span>
    <span style="color: #1E293B; font-weight: 700;">Phân tích kết quả học tập</span>
</div>

{{-- 1. Tiêu đề trang & Huy hiệu Cập nhật liên tục --}}
<div class="pt-page-header">
    <div class="pt-header-left">
        <div class="pt-header-icon-wrap">
            {{-- Icon Phân tích / Báo cáo năng lực --}}
            <svg viewBox="0 0 24 24">
                <path d="M3 3v18h18"/>
                <path d="m19 9-5 5-4-4-3 3"/>
            </svg>
        </div>
        <div>
            <div class="pt-title-wrap">
                <h1 class="pt-page-title">Phân tích kết quả học tập</h1>
                <span class="pt-live-badge">
                    <span class="pt-live-dot"></span>
                    Cập nhật liên tục
                </span>
            </div>
            <p class="pt-page-desc">Theo dõi điểm thi, điểm yếu theo từng Part và lộ trình ôn tập lặp lại ngắt quãng</p>
        </div>
    </div>
</div>

{{-- 2. Thanh điều hướng Tabs chính --}}
<nav class="pt-tabs-nav" aria-label="Bộ lọc phân tích">
    <a href="#overview" class="pt-tab-link is-active" onclick="switchAnalyticsTab('overview', this)">
        <span>Tổng quan tất cả</span>
        <span class="pt-tab-badge">4 Mục</span>
    </a>
    <a href="#fulltest" class="pt-tab-link" onclick="switchAnalyticsTab('fulltest', this)">
        <span>Luyện đề (Full Test)</span>
    </a>
    <a href="#weakness" class="pt-tab-link" onclick="switchAnalyticsTab('weakness', this)">
        <span>Điểm yếu &amp; Cần cải thiện</span>
    </a>
    <a href="#vocab" class="pt-tab-link" onclick="switchAnalyticsTab('vocab', this)">
        <span>Từ vựng (Spaced Repetition)</span>
    </a>
    <a href="{{ url('/lich-su') }}" class="pt-tab-link">
        <span>Hoạt động &amp; Lịch sử</span>
    </a>
</nav>

{{-- 3. Hàng 1: Bốn thẻ thống kê tổng quan --}}
<div class="pt-stats-row">
    {{-- Thẻ 1: Điểm cao nhất --}}
    <div class="pt-stat-card">
        <div class="pt-stat-top">
            <span class="pt-stat-label">ĐIỂM CAO NHẤT</span>
            <svg class="pt-stat-icon pt-stat-icon--trophy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                <path d="M4 22h16"></path>
                <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
            </svg>
        </div>
        <div class="pt-stat-body">
            <div class="pt-stat-val-wrap">
                <span class="pt-stat-big-val">---</span>
            </div>
            <p class="pt-stat-desc">Chưa có bài Full Test nào</p>
        </div>
    </div>

    {{-- Thẻ 2: Tỉ lệ đúng trung bình --}}
    <div class="pt-stat-card">
        <div class="pt-stat-top">
            <span class="pt-stat-label">TỈ LỆ ĐÚNG TRUNG BÌNH</span>
            <svg class="pt-stat-icon pt-stat-icon--target" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <circle cx="12" cy="12" r="6"></circle>
                <circle cx="12" cy="12" r="2"></circle>
            </svg>
        </div>
        <div class="pt-stat-body">
            <div class="pt-stat-val-wrap">
                <span class="pt-stat-big-val pt-stat-big-val--blue">61%</span>
                <span class="pt-stat-subval">(38/62 câu)</span>
            </div>
            <div class="pt-stat-mini-bar">
                <div class="pt-stat-mini-fill" style="width: 61%;"></div>
            </div>
        </div>
    </div>

    {{-- Thẻ 3: Điểm yếu nhất (Thẻ viền vàng nổi bật) --}}
    <div class="pt-stat-card pt-stat-card--highlight">
        <div class="pt-stat-top">
            <span class="pt-stat-label">ĐIỂM YẾU NHẤT</span>
            <svg class="pt-stat-icon pt-stat-icon--warning" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <div class="pt-stat-body">
            <div class="pt-stat-highlight-text">Nghe (Listening)</div>
            <p class="pt-stat-desc">Part 4: Lời nói ngắn (Độ thành thạo 51%)</p>
        </div>
    </div>

    {{-- Thẻ 4: Từ vựng đến hạn ôn --}}
    <div class="pt-stat-card">
        <div class="pt-stat-top">
            <span class="pt-stat-label">TỪ VỰNG ĐẾN HẠN ÔN</span>
            <svg class="pt-stat-icon pt-stat-icon--clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="pt-stat-body">
            <div class="pt-stat-val-wrap">
                <span class="pt-stat-big-val pt-stat-big-val--red">4</span>
                <span class="pt-stat-subval">từ vựng hôm nay</span>
            </div>
            <p class="pt-stat-desc">Theo thuật toán Spaced Repetition</p>
        </div>
    </div>
</div>

{{-- 4. Khung 1: Điểm bài thi Full Test & Độ thành thạo --}}
<div class="pt-card-section" id="section-fulltest">
    <div class="pt-card-header">
        <div class="pt-card-title-group">
            <h2 class="pt-card-title">Điểm bài thi Full Test &amp; Độ thành thạo</h2>
            <p class="pt-card-subtitle">Đánh giá năng lực thực dựa trên các bài thi dài hoặc dự đoán theo thuật toán</p>
        </div>
        <div class="pt-period-pills" role="tablist">
            <button type="button" class="pt-period-btn is-active" onclick="switchPeriod(this)">7 ngày</button>
            <button type="button" class="pt-period-btn" onclick="switchPeriod(this)">30 ngày</button>
            <button type="button" class="pt-period-btn" onclick="switchPeriod(this)">90 ngày</button>
            <button type="button" class="pt-period-btn" onclick="switchPeriod(this)">Tất cả</button>
        </div>
    </div>

    {{-- Vùng biểu đồ rỗng ban đầu --}}
    <div class="pt-empty-chart-box">
        <svg class="pt-empty-chart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
        </svg>
        <p class="pt-empty-chart-text">Không có bài Full Test trong 7 ngày vừa qua.</p>
        <a href="{{ url('/thi-thu') }}" class="pt-cta-primary-btn">
            Làm Full Test ngay →
        </a>
    </div>

    {{-- Bảng chi tiết độ thành thạo theo từng Part --}}
    <div class="pt-parts-breakdown-wrap">
        <div class="pt-parts-section-header">
            <h3 class="pt-parts-heading">PHẦN THI (PART)</h3>
            <div class="pt-parts-legend">
                <span class="pt-legend-item"><span class="pt-legend-dot pt-legend-dot--green"></span>ĐÃ VỮNG</span>
                <span class="pt-legend-item"><span class="pt-legend-dot pt-legend-dot--orange"></span>ĐANG TIẾN BỘ</span>
                <span class="pt-legend-item"><span class="pt-legend-dot pt-legend-dot--red"></span>CẦN CẢI THIỆN</span>
            </div>
        </div>

        <div class="pt-parts-list">
            {{-- Part 1 --}}
            <div class="pt-part-row">
                <div class="pt-part-badge-col">
                    <span class="pt-part-name">Part 1</span>
                    <span class="pt-status-pill pt-status-pill--green">ĐÃ VỮNG</span>
                </div>
                <div class="pt-part-track">
                    <div class="pt-part-fill pt-part-fill--green" style="width: 75%;"></div>
                </div>
                <div class="pt-part-percent-col">
                    75%
                </div>
            </div>

            {{-- Part 2 --}}
            <div class="pt-part-row">
                <div class="pt-part-badge-col">
                    <span class="pt-part-name">Part 2</span>
                    <span class="pt-status-pill pt-status-pill--orange">TIẾN BỘ</span>
                </div>
                <div class="pt-part-track">
                    <div class="pt-part-fill pt-part-fill--striped-orange" style="width: 69%;"></div>
                </div>
                <div class="pt-part-percent-col">
                    69%
                    <span class="pt-part-subtext">Dự đoán</span>
                </div>
            </div>

            {{-- Part 3 --}}
            <div class="pt-part-row">
                <div class="pt-part-badge-col">
                    <span class="pt-part-name">Part 3</span>
                    <span class="pt-status-pill pt-status-pill--orange">TIẾN BỘ</span>
                </div>
                <div class="pt-part-track">
                    <div class="pt-part-fill pt-part-fill--striped-orange" style="width: 62%;"></div>
                </div>
                <div class="pt-part-percent-col">
                    62%
                </div>
            </div>

            {{-- Part 4 --}}
            <div class="pt-part-row">
                <div class="pt-part-badge-col">
                    <span class="pt-part-name">Part 4</span>
                    <span class="pt-status-pill pt-status-pill--red">CẦN CẢI THIỆN</span>
                </div>
                <div class="pt-part-track">
                    <div class="pt-part-fill pt-part-fill--striped-red" style="width: 53%;"></div>
                </div>
                <div class="pt-part-percent-col">
                    53%
                </div>
            </div>

            {{-- Part 5 --}}
            <div class="pt-part-row">
                <div class="pt-part-badge-col">
                    <span class="pt-part-name">Part 5</span>
                    <span class="pt-status-pill pt-status-pill--red">CẦN CẢI THIỆN</span>
                </div>
                <div class="pt-part-track">
                    <div class="pt-part-fill pt-part-fill--striped-red" style="width: 53%;"></div>
                </div>
                <div class="pt-part-percent-col">
                    53%
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 5. Khung 2: Top điểm yếu cần được cải thiện --}}
<div class="pt-card-section" id="section-weakness">
    <div class="pt-card-header">
        <div class="pt-card-title-group">
            <div class="pt-card-title-row">
                <span style="color: #F59E0B; font-size: 18px;">⚡</span>
                <h2 class="pt-card-title">Top điểm yếu cần được cải thiện</h2>
            </div>
            <p class="pt-card-subtitle">Các phần câu hỏi sai nhiều nhất làm giảm điểm tổng TOEIC</p>
        </div>
        <span class="pt-weakness-badge-count">Tổng 24 câu sai</span>
    </div>

    <div class="pt-weakness-list">
        {{-- Item 1 --}}
        <div class="pt-weakness-item">
            <div class="pt-weakness-left">
                <span class="pt-weakness-num pt-weakness-num--1">1</span>
                <div class="pt-weakness-info">
                    <span class="pt-weakness-tag pt-weakness-tag--red">NGHE • PART 4</span>
                    <h3 class="pt-weakness-name">Lời nói ngắn (Short Talks)</h3>
                    <p class="pt-weakness-sub">Đã luyện 15 câu • <strong style="color: #DC2626;">Độ thành thạo: 51%</strong></p>
                </div>
            </div>
            <a href="{{ url('/luyen-tap/noi') }}" class="pt-weakness-btn pt-weakness-btn--crimson">
                Luyện ngay →
            </a>
        </div>

        {{-- Item 2 --}}
        <div class="pt-weakness-item">
            <div class="pt-weakness-left">
                <span class="pt-weakness-num pt-weakness-num--2">2</span>
                <div class="pt-weakness-info">
                    <span class="pt-weakness-tag pt-weakness-tag--orange">NGHE • PART 1</span>
                    <h3 class="pt-weakness-name">Sự vật và bối cảnh trong ảnh</h3>
                    <p class="pt-weakness-sub">Đã luyện 8 câu • Độ thành thạo: 70%</p>
                </div>
            </div>
            <a href="{{ url('/luyen-tap/doc?skill=nghe') }}" class="pt-weakness-btn pt-weakness-btn--amber">
                Luyện
            </a>
        </div>

        {{-- Item 3 --}}
        <div class="pt-weakness-item">
            <div class="pt-weakness-left">
                <span class="pt-weakness-num pt-weakness-num--3">3</span>
                <div class="pt-weakness-info">
                    <span class="pt-weakness-tag pt-weakness-tag--orange">NGHE • PART 3</span>
                    <h3 class="pt-weakness-name">Hội thoại 2 người</h3>
                    <p class="pt-weakness-sub">Đã luyện 13 câu • Độ thành thạo: 76%</p>
                </div>
            </div>
            <a href="{{ url('/luyen-tap/doc?skill=nghe') }}" class="pt-weakness-btn pt-weakness-btn--amber">
                Luyện
            </a>
        </div>
    </div>

    {{-- Thanh thông báo nhắc ôn tập câu sai --}}
    <div class="pt-weakness-alert-bar">
        <div class="pt-weakness-alert-left">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>Bạn có <strong>24 câu sai</strong> cần làm lại để ghi nhớ kiến thức</span>
        </div>
        <a href="{{ url('/cai-thien') }}" class="pt-weakness-alert-btn">
            Xem chi tiết
        </a>
    </div>
</div>

{{-- 6. Khung 3: Tiến độ Từ vựng (Spaced Repetition) --}}
<div class="pt-card-section" id="section-vocab">
    <div class="pt-card-header">
        <div class="pt-card-title-group">
            <div class="pt-card-title-row">
                <svg class="pt-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    <polyline points="2 17 12 22 22 17"></polyline>
                    <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
                <h2 class="pt-card-title">Tiến độ Từ vựng</h2>
            </div>
            <p class="pt-card-subtitle">Học theo phương pháp lặp lại ngắt quãng (Spaced Repetition)</p>
        </div>
        <span class="pt-vocab-count-pill">4 từ đã học</span>
    </div>

    {{-- 3 Thông số nhỏ --}}
    <div class="pt-vocab-mini-stats">
        <div class="pt-vocab-mini-card">
            <div class="pt-vocab-mini-label">Nắm vững</div>
            <div class="pt-vocab-mini-val">
                3 <span class="pt-vocab-mini-unit">(75%)</span>
            </div>
        </div>
        <div class="pt-vocab-mini-card">
            <div class="pt-vocab-mini-label">Cần kiểm tra</div>
            <div class="pt-vocab-mini-val">
                4 <span class="pt-vocab-mini-unit">/12</span>
            </div>
        </div>
        <div class="pt-vocab-mini-card">
            <div class="pt-vocab-mini-label">Độ thành thạo</div>
            <div class="pt-vocab-mini-val pt-vocab-mini-val--blue">
                2.0 <span class="pt-vocab-mini-unit">/5</span>
            </div>
        </div>
    </div>

    {{-- Lịch ôn tập 7 ngày --}}
    <div class="pt-vocab-calendar-wrap">
        <div class="pt-vocab-calendar-top">
            <p class="pt-vocab-calendar-title">Sắp đến hạn ôn tập (7 ngày tới)</p>
            <p class="pt-vocab-calendar-highlight">4 từ đến hạn hôm nay</p>
        </div>
        <div class="pt-vocab-days-row">
            {{-- Hôm nay (Active) --}}
            <div class="pt-vocab-day-box pt-vocab-day-box--active">
                <span class="pt-vocab-day-circle">4</span>
                <span class="pt-vocab-day-name">Hôm nay</span>
            </div>
            {{-- Mai --}}
            <div class="pt-vocab-day-box">
                <span class="pt-vocab-day-circle">0</span>
                <span class="pt-vocab-day-name">Mai</span>
            </div>
            {{-- Th 2 --}}
            <div class="pt-vocab-day-box">
                <span class="pt-vocab-day-circle">0</span>
                <span class="pt-vocab-day-name">Th 2</span>
            </div>
            {{-- Th 3 --}}
            <div class="pt-vocab-day-box">
                <span class="pt-vocab-day-circle">0</span>
                <span class="pt-vocab-day-name">Th 3</span>
            </div>
            {{-- Th 4 --}}
            <div class="pt-vocab-day-box">
                <span class="pt-vocab-day-circle">0</span>
                <span class="pt-vocab-day-name">Th 4</span>
            </div>
            {{-- Th 5 --}}
            <div class="pt-vocab-day-box">
                <span class="pt-vocab-day-circle">0</span>
                <span class="pt-vocab-day-name">Th 5</span>
            </div>
            {{-- Th 6 --}}
            <div class="pt-vocab-day-box">
                <span class="pt-vocab-day-circle">0</span>
                <span class="pt-vocab-day-name">Th 6</span>
            </div>
        </div>
    </div>

    {{-- Banner kêu gọi ôn tập màu cam --}}
    <div class="pt-vocab-cta-banner">
        <div>
            <h3 class="pt-vocab-banner-title">Kiểm tra lại 4 từ vựng</h3>
            <p class="pt-vocab-banner-desc">Giúp tăng độ thành thạo lên Mức 3</p>
        </div>
        <a href="{{ url('/tu-vung') }}" class="pt-vocab-banner-btn">
            Bắt đầu ôn tập →
        </a>
    </div>
</div>

{{-- 7. Khung 4: Hoạt động gần đây --}}
<div class="pt-card-section" id="section-history">
    <div class="pt-card-header">
        <div class="pt-card-title-group">
            <div class="pt-card-title-row">
                <svg class="pt-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <h2 class="pt-card-title">Hoạt động gần đây</h2>
            </div>
        </div>
        <a href="{{ url('/lich-su') }}" class="pt-history-link-all">Xem tất cả</a>
    </div>

    <div class="pt-activities-list">
        {{-- Activity 1 --}}
        <div class="pt-activity-row">
            <div class="pt-activity-left">
                <div class="pt-activity-icon pt-activity-icon--pink">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                </div>
                <div class="pt-activity-details">
                    <h3 class="pt-activity-name">Part 5: Hoàn thành câu</h3>
                    <p class="pt-activity-meta">10 phút trước • <strong style="color: #DC2626;">1 câu sai</strong></p>
                </div>
            </div>
            <div class="pt-activity-right">
                <span class="pt-activity-rate pt-activity-rate--red">0%</span>
                <span class="pt-activity-rate-label">Tỉ lệ đúng</span>
            </div>
        </div>

        {{-- Activity 2 --}}
        <div class="pt-activity-row">
            <div class="pt-activity-left">
                <div class="pt-activity-icon pt-activity-icon--yellow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                        <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                    </svg>
                </div>
                <div class="pt-activity-details">
                    <h3 class="pt-activity-name">Part 4: Bài nói chuyện ngắn</h3>
                    <p class="pt-activity-meta">6 ngày trước • 7/15 câu đúng</p>
                </div>
            </div>
            <div class="pt-activity-right">
                <span class="pt-activity-rate pt-activity-rate--yellow">46%</span>
                <span class="pt-activity-rate-label">Tỉ lệ đúng</span>
            </div>
        </div>

        {{-- Activity 3 --}}
        <div class="pt-activity-row">
            <div class="pt-activity-left">
                <div class="pt-activity-icon pt-activity-icon--green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <div class="pt-activity-details">
                    <h3 class="pt-activity-name">Part 3: Hội thoại ngắn</h3>
                    <p class="pt-activity-meta">9 ngày trước • <strong style="color: #16A34A;">Hoàn thành</strong></p>
                </div>
            </div>
            <div class="pt-activity-right">
                <span class="pt-activity-rate pt-activity-rate--green">73%</span>
                <span class="pt-activity-rate-label">Tỉ lệ đúng</span>
            </div>
        </div>
    </div>

    {{-- Nút CTA chuyển sang Lịch sử dưới cùng --}}
    <a href="{{ url('/lich-su') }}" class="pt-full-history-cta">
        <span>Xem toàn bộ lịch sử luyện tập →</span>
    </a>
</div>

@push('scripts')
<script>
    function switchPeriod(btn) {
        document.querySelectorAll('.pt-period-btn').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
    }

    function switchAnalyticsTab(tabId, el) {
        document.querySelectorAll('.pt-tab-link').forEach(t => t.classList.remove('is-active'));
        el.classList.add('is-active');

        if (tabId === 'overview') {
            document.querySelectorAll('.pt-card-section').forEach(sec => sec.style.display = 'block');
        } else if (tabId === 'fulltest') {
            document.getElementById('section-fulltest').scrollIntoView({ behavior: 'smooth' });
        } else if (tabId === 'weakness') {
            document.getElementById('section-weakness').scrollIntoView({ behavior: 'smooth' });
        } else if (tabId === 'vocab') {
            document.getElementById('section-vocab').scrollIntoView({ behavior: 'smooth' });
        }
    }
</script>
@endpush

</x-layouts.app>
