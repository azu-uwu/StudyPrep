<x-layouts.app active="phan-tich" title="StudyPrep - Phân tích">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/phan-tich.css') }}">
@endpush

<div class="pt-page-wrapper">

    {{-- ===== TIÊU ĐỀ TRANG (Đồng nhất với Lịch sử & Cải thiện) ===== --}}
    <div class="pt-page-header-row">
        <h1 class="pt-page-title">Phân tích</h1>
    </div>

    {{-- ===== THANH ĐIỀU HƯỚNG 5 MỤC (STICKY NAVIGATION) ===== --}}
    <div class="pt-sticky-nav-wrapper" id="ptStickyNavWrapper">
        <nav class="pt-sticky-nav" id="ptStickyNav" aria-label="Điều hướng các phần phân tích">
            <button type="button" class="pt-nav-item is-active" data-target="section-phan-thi" onclick="scrollToSection('section-phan-thi')">
                Phần thi
            </button>
            <button type="button" class="pt-nav-item" data-target="section-luyen-de" onclick="scrollToSection('section-luyen-de')">
                Luyện đề
            </button>
            <button type="button" class="pt-nav-item" data-target="section-diem-yeu" onclick="scrollToSection('section-diem-yeu')">
                Điểm yếu
            </button>
            <button type="button" class="pt-nav-item" data-target="section-tu-vung" onclick="scrollToSection('section-tu-vung')">
                Từ vựng
            </button>
            <button type="button" class="pt-nav-item" data-target="section-hoat-dong" onclick="scrollToSection('section-hoat-dong')">
                Hoạt động
            </button>
        </nav>
    </div>

    {{-- =========================================================================
         PHẦN 1: PHẦN THI
         ========================================================================= --}}
    <section class="pt-section" id="section-phan-thi">
        {{-- Hàng 3 thẻ thống kê tổng quan --}}
        <div class="pt-stats-grid-3">
            {{-- Thẻ 1: Điểm cao nhất --}}
            <div class="pt-stat-card">
                <span class="pt-stat-label">Điểm cao nhất</span>
                <div class="pt-stat-val-row">
                    <span class="pt-stat-big-val">{{ $analyticsStats['highest_score'] ?? '---' }}</span>
                </div>
                <p class="pt-stat-desc">
                    @if(!empty($analyticsStats['highest_test_name']))
                        {{ $analyticsStats['highest_test_name'] }} · {{ $analyticsStats['highest_test_date'] }}
                    @else
                        Chưa có bài thi Full Test nào
                    @endif
                </p>
            </div>

            {{-- Thẻ 2: Tỉ lệ đúng trung bình --}}
            <div class="pt-stat-card">
                <span class="pt-stat-label">Tỉ lệ đúng trung bình</span>
                <div class="pt-stat-val-row">
                    <span class="pt-stat-big-val pt-stat-big-val--blue">{{ $analyticsStats['avg_accuracy'] }}%</span>
                    <span class="pt-stat-sub-val">({{ $analyticsStats['correct_questions'] }}/{{ $analyticsStats['total_questions'] }} câu)</span>
                </div>
                <p class="pt-stat-desc">Tính trên toàn bộ các câu hỏi đã làm</p>
            </div>

            {{-- Thẻ 3: Điểm yếu nhất (Viền vàng cam nổi bật) --}}
            <div class="pt-stat-card pt-stat-card--amber">
                <span class="pt-stat-label">Điểm yếu nhất</span>
                <div class="pt-stat-val-row">
                    <span class="pt-stat-big-val pt-stat-big-val--amber" style="font-size: 21px;">{{ $analyticsStats['weakest_skill'] }}</span>
                </div>
                <p class="pt-stat-desc">{{ $analyticsStats['weakest_score_info'] }}</p>
            </div>
        </div>

        {{-- Biểu đồ tỉ lệ đúng theo Part --}}
        <div class="pt-section-header">
            <h2 class="pt-section-title">Tỉ lệ đúng theo Part</h2>
            <p class="pt-section-desc">
                Tính trên mọi Part bạn đã làm (tối thiểu 5 câu) trên các mục: Bài học, Luyện đề từng Part và Full Test.
            </p>
        </div>

        <div class="pt-card-box">
            <div class="pt-part-chart-wrap">
                <div class="pt-chart-area">
                    {{-- Trục tung Y-Axis --}}
                    <div class="pt-y-axis">
                        <span>100</span>
                        <span>75</span>
                        <span>50</span>
                        <span>25</span>
                        <span>0</span>
                    </div>

                    {{-- Đường lưới ngang đứt nét --}}
                    <div class="pt-grid-lines">
                        <div class="pt-grid-line"></div>
                        <div class="pt-grid-line"></div>
                        <div class="pt-grid-line"></div>
                        <div class="pt-grid-line"></div>
                        <div class="pt-grid-line pt-grid-line--base"></div>
                    </div>

                    {{-- Cột biểu đồ 7 Parts --}}
                    <div class="pt-chart-columns">
                        @foreach($partAccuracyList as $item)
                            <div class="pt-col-item">
                                @if($item['has_data'])
                                    <span class="pt-col-val">{{ $item['rate'] }}%</span>
                                    <div class="pt-col-pillar" style="height: {{ $item['rate'] }}%;" title="{{ $item['part'] }}: {{ $item['correct'] }}/{{ $item['total'] }} câu đúng"></div>
                                @else
                                    <span class="pt-col-val pt-col-val--empty">---</span>
                                    <div class="pt-col-pillar pt-col-pillar--unrated" style="height: 4px;" title="{{ $item['part'] }}: Chưa đủ 5 câu"></div>
                                @endif
                                <span class="pt-col-label">{{ $item['part'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Thẻ thông báo câu sai cần ôn lại (Chỉ hiển thị khi có câu sai) --}}
        @if(($analyticsStats['wrong_questions_count'] ?? 0) > 0)
            <div class="pt-wrong-alert-banner">
                <div class="pt-wrong-alert-left">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span>Bạn có {{ $analyticsStats['wrong_questions_count'] }} câu sai cần ôn lại</span>
                </div>
                <x-button variant="primary" size="small" :href="url('/cai-thien')">
                    Xem chi tiết
                </x-button>
            </div>
        @endif
    </section>

    {{-- =========================================================================
         PHẦN 2: LUYỆN ĐỀ
         ========================================================================= --}}
    <section class="pt-section" id="section-luyen-de">
        <div class="pt-section-header-split">
            <div>
                <h2 class="pt-section-title">Điểm bài thi Full Test</h2>
                <p class="pt-section-desc">Chỉ tính các bài thi làm ở chế độ Full Test.</p>
            </div>
            <div class="pt-filter-pills" role="tablist">
                <button type="button" class="pt-filter-pill-btn {{ ($period ?? '90') === '7' ? 'is-active' : '' }}" onclick="switchFilterPeriod(this, '7')">7 ngày</button>
                <button type="button" class="pt-filter-pill-btn {{ ($period ?? '90') === '30' ? 'is-active' : '' }}" onclick="switchFilterPeriod(this, '30')">30 ngày</button>
                <button type="button" class="pt-filter-pill-btn {{ ($period ?? '90') === '90' ? 'is-active' : '' }}" onclick="switchFilterPeriod(this, '90')">90 ngày</button>
                <button type="button" class="pt-filter-pill-btn {{ ($period ?? '90') === 'all' ? 'is-active' : '' }}" onclick="switchFilterPeriod(this, 'all')">Tất cả</button>
            </div>
        </div>

        <div class="pt-card-box">
            <div class="pt-score-chart-box" id="ptScoreChartBox">
                @if(empty($fullTestData['has_attempts']))
                    {{-- Trạng thái chưa có bài thi Full Test trong khoảng thời gian --}}
                    <div class="pt-trend-empty-wrap" style="padding: 42px 16px; text-align: center;">
                        <div style="width: 52px; height: 52px; border-radius: 50%; background: #F1F5F9; color: #64748B; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 3v18h18"></path>
                                <path d="m19 9-5 5-4-4-3 3"></path>
                            </svg>
                        </div>
                        <h3 style="font-size: 16px; font-weight: 800; color: #111C2D; margin: 0 0 6px;">Chưa có bài thi Full Test nào</h3>
                        <p style="font-size: 13.5px; color: #64748B; margin: 0 0 16px; max-width: 440px; margin-left: auto; margin-right: auto;">
                            Hãy làm bài thi thử ở chế độ Full Test để bắt đầu theo dõi biểu đồ xu hướng điểm số của bạn.
                        </p>
                        <x-button variant="primary" size="small" :href="url('/thi-thu')">
                            Làm bài thi thử
                        </x-button>
                    </div>
                @else
                    {{-- SVG Score Chart responsive --}}
                    <div class="pt-score-chart-canvas-area" id="ptScoreChartCanvasArea">
                        <svg viewBox="0 0 760 200" preserveAspectRatio="none" style="width: 100%; height: 100%;">
                            {{-- Đường lưới Y (990, 495, 0) --}}
                            <line x1="40" y1="20" x2="740" y2="20" stroke="#CBD5E1" stroke-width="1.2" stroke-dasharray="0" />
                            <text x="30" y="24" fill="#94A3B8" font-size="11" font-weight="600" text-anchor="end">990</text>

                            <line x1="40" y1="95" x2="740" y2="95" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3 3" />
                            <text x="30" y="99" fill="#94A3B8" font-size="11" font-weight="600" text-anchor="end">495</text>

                            <line x1="40" y1="170" x2="740" y2="170" stroke="#CBD5E1" stroke-width="1.2" stroke-dasharray="0" />
                            <text x="30" y="174" fill="#94A3B8" font-size="11" font-weight="600" text-anchor="end">0</text>

                            {{-- Đường kẻ nối khi có từ 2 bài thi trở lên --}}
                            @if(!empty($fullTestData['listening_path']))
                                <path d="{{ $fullTestData['listening_path'] }}" fill="none" stroke="#6366F1" stroke-width="2" stroke-dasharray="4 4" />
                            @endif
                            @if(!empty($fullTestData['reading_path']))
                                <path d="{{ $fullTestData['reading_path'] }}" fill="none" stroke="#06B6D4" stroke-width="2" stroke-dasharray="4 4" />
                            @endif
                            @if(!empty($fullTestData['total_path']))
                                <path d="{{ $fullTestData['total_path'] }}" fill="none" stroke="#22C55E" stroke-width="2.5" />
                            @endif

                            {{-- Các điểm thi --}}
                            @foreach($fullTestData['points'] as $pt)
                                <text x="{{ $pt['x'] }}" y="190" fill="#64748B" font-size="12" font-weight="700" text-anchor="middle">{{ $pt['date_label'] }}</text>

                                {{-- Điểm Full Test --}}
                                <text x="{{ $pt['x'] }}" y="{{ max(14, $pt['y_total'] - 10) }}" fill="#111C2D" font-size="13" font-weight="800" text-anchor="middle">{{ $pt['total_score'] }}</text>
                                <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y_total'] }}" r="4.5" fill="#FFFFFF" stroke="#22C55E" stroke-width="3">
                                    <title>{{ $pt['test_title'] }}: {{ $pt['total_score'] }} điểm ({{ $pt['full_date'] }})</title>
                                </circle>

                                {{-- Điểm Đọc --}}
                                <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y_reading'] }}" r="4" fill="#FFFFFF" stroke="#06B6D4" stroke-width="2.5">
                                    <title>Đọc: {{ $pt['reading_score'] }} điểm</title>
                                </circle>

                                {{-- Điểm Nghe --}}
                                <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y_listening'] }}" r="3.5" fill="#6366F1">
                                    <title>Nghe: {{ $pt['listening_score'] }} điểm</title>
                                </circle>
                            @endforeach
                        </svg>
                    </div>

                    {{-- Chú thích màu sắc --}}
                    <div class="pt-chart-legends">
                        <span class="pt-legend-item">
                            <span class="pt-legend-dot pt-legend-dot--listening"></span>
                            Nghe
                        </span>
                        <span class="pt-legend-item">
                            <span class="pt-legend-dot pt-legend-dot--reading"></span>
                            Đọc
                        </span>
                        <span class="pt-legend-item">
                            <span class="pt-legend-dot pt-legend-dot--fulltest"></span>
                            Full Test
                        </span>
                    </div>

                    {{-- Thông báo khi chỉ có 1 lần thi --}}
                    @if(!empty($fullTestData['is_single_attempt']))
                        <div class="pt-trend-notice-banner" id="ptTrendNoticeBanner">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <span>Chỉ có 1 lần thi trong khoảng này — chưa đủ để thấy xu hướng.</span>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Card kết quả lần thi hoàn chỉnh gần nhất --}}
        <div class="pt-card-box">
            @if(!empty($fullTestData['recent_test']))
                <div class="pt-recent-test-card">
                    {{-- Cột trái: Tiến độ 2 kỹ năng --}}
                    <div class="pt-recent-test-left">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span class="pt-stat-label">Lần thi hoàn chỉnh gần nhất</span>
                            <span style="font-size: 12.5px; font-weight: 700; color: #64748B;">{{ $fullTestData['recent_test']['date'] }} · {{ $fullTestData['recent_test']['total_score'] }} điểm</span>
                        </div>

                        {{-- Kỹ năng Nghe --}}
                        <div class="pt-test-skill-row" style="margin-top: 10px;">
                            <div class="pt-test-skill-header">
                                <span>Nghe</span>
                                <span class="pt-test-skill-score">{{ $fullTestData['recent_test']['listening_score'] }}/{{ $fullTestData['recent_test']['max_skill_score'] }}</span>
                            </div>
                            <div class="pt-progress-track">
                                <div class="pt-progress-fill pt-progress-fill--listening" style="width: {{ round(($fullTestData['recent_test']['listening_score'] / $fullTestData['recent_test']['max_skill_score']) * 100, 1) }}%;"></div>
                            </div>
                        </div>

                        {{-- Kỹ năng Đọc --}}
                        <div class="pt-test-skill-row">
                            <div class="pt-test-skill-header">
                                <span>Đọc</span>
                                <span class="pt-test-skill-score">{{ $fullTestData['recent_test']['reading_score'] }}/{{ $fullTestData['recent_test']['max_skill_score'] }}</span>
                            </div>
                            <div class="pt-progress-track">
                                <div class="pt-progress-fill pt-progress-fill--reading" style="width: {{ round(($fullTestData['recent_test']['reading_score'] / $fullTestData['recent_test']['max_skill_score']) * 100, 1) }}%;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Cột phải: Nhận xét tương quan --}}
                    <div class="pt-recent-test-right">
                        <h3 class="pt-recent-test-status-title">{{ $fullTestData['recent_test']['balance_title'] }}</h3>
                        <p class="pt-recent-test-status-desc">{{ $fullTestData['recent_test']['balance_desc'] }}</p>
                    </div>
                </div>
            @else
                <div style="padding: 24px 16px; text-align: center; color: var(--sp-text-muted, #64748B);">
                    <p style="font-size: 14.5px; font-weight: 700; margin: 0 0 4px; color: var(--sp-text-main, #111C2D);">Chưa có kết quả bài thi Full Test nào</p>
                    <p style="font-size: 13px; margin: 0; color: var(--sp-text-light, #94A3B8);">Hãy hoàn thành một bài thi thử để xem điểm số chi tiết từng kỹ năng Nghe và Đọc.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- =========================================================================
         PHẦN 3: ĐIỂM YẾU
         ========================================================================= --}}
    <section class="pt-section" id="section-diem-yeu">
        <div class="pt-section-header">
            <div class="pt-section-title-row">
                <h2 class="pt-section-title" style="margin-bottom: 0;">Độ thành thạo</h2>
                <button type="button" class="pt-link-learn-more" onclick="openMasteryModal()" aria-label="Tìm hiểu thêm về Độ thành thạo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span>Tìm hiểu thêm</span>
                </button>
            </div>
            <p class="pt-section-desc" style="margin-top: 6px;">
                StudyPrep AI đánh giá năng lực thật của bạn trên từng Part và chỉ ra dạng câu cần luyện trước. Part chưa luyện được đánh giá dựa trên dự đoán, hiển thị bằng vạch sọc.
            </p>
        </div>

        {{-- Khung chung danh sách Độ thành thạo các Part --}}
        <div class="pt-card-box pt-card-box--flush">
            @foreach($masteryParts as $mPart)
                <div class="pt-mastery-row">
                    <div class="pt-mastery-left">
                        <span class="pt-mastery-part-title">{{ $mPart['part'] }}</span>
                        @if($mPart['has_data'])
                            <span class="pt-status-pill pt-status-pill--{{ $mPart['status_type'] }}">{{ $mPart['status'] }}</span>
                        @else
                            <span class="pt-status-pill pt-status-pill--neutral">Chưa đánh giá</span>
                        @endif
                    </div>
                    <div class="pt-mastery-mid">
                        <span class="pt-mastery-label">Độ thành thạo</span>
                        <div class="pt-mastery-track">
                            @if($mPart['has_data'])
                                <div class="pt-mastery-fill pt-mastery-fill--{{ $mPart['status_type'] }}" style="width: {{ $mPart['rate'] }}%;"></div>
                            @else
                                <div class="pt-mastery-fill pt-mastery-fill--striped" style="width: 100%;"></div>
                            @endif
                        </div>
                    </div>
                    <div class="pt-mastery-percent">
                        @if($mPart['has_data'])
                            {{ $mPart['rate'] }}%
                        @else
                            <span class="pt-mastery-percent--unrated">---</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Top 3 điểm yếu ảnh hưởng đến độ thành thạo --}}
        <div style="margin-top: 32px;">
            <h2 class="pt-section-title" style="font-size: 19px;">Top 3 điểm yếu ảnh hưởng đến Độ thành thạo của bạn</h2>

            @if(empty($topWeaknesses))
                <div class="pt-card-box pt-card-box--flush" style="margin-top: 14px; margin-bottom: 16px; padding: 28px 16px; text-align: center;">
                    <p style="font-size: 14.5px; font-weight: 700; margin: 0 0 4px; color: var(--sp-text-main, #111C2D);">Chưa có dữ liệu phân tích điểm yếu</p>
                    <p style="font-size: 13px; margin: 0; color: var(--sp-text-light, #94A3B8);">StudyPrep AI sẽ phân tích các dạng câu còn yếu sau khi bạn hoàn thành thêm bài thi thử.</p>
                </div>
            @else
                <div class="pt-card-box pt-card-box--flush" style="margin-top: 14px; margin-bottom: 16px;">
                    @foreach($topWeaknesses as $weakness)
                        <div class="pt-weakness-row">
                            <div class="pt-weakness-rank-badge">{{ $weakness['rank'] }}</div>
                            <div class="pt-weakness-info">
                                <div class="pt-weakness-meta">{{ $weakness['meta'] }}</div>
                                <div class="pt-weakness-name-row">
                                    <h3 class="pt-weakness-name">{{ $weakness['title'] }}</h3>
                                    @if(!empty($weakness['practiced_count']))
                                        <span class="pt-weakness-practiced">• Đã luyện {{ $weakness['practiced_count'] }} câu</span>
                                    @endif
                                </div>
                                <p class="pt-weakness-rate-text">Độ thành thạo {{ $weakness['mastery_rate'] }}%</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div style="margin-top: 18px;">
                <x-button variant="primary" size="medium" class="pt-btn-full-width" :href="url('/cai-thien')">
                    Xem tất cả
                </x-button>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         PHẦN 4: TỪ VỰNG (THEO CƠ CHẾ STUDYPREP: ĐÃ HỌC, NẮM VỮNG, DỄ QUÊN)
         ========================================================================= --}}
    <section class="pt-section" id="section-tu-vung">
        <div class="pt-section-header">
            <h2 class="pt-section-title">Từ vựng</h2>
            <p class="pt-section-desc">Thống kê từ vựng đã học và mức độ ghi nhớ trong khoá học.</p>
        </div>

        <div class="pt-card-box">
            {{-- 4 chỉ số thống kê từ vựng theo cơ chế hệ thống StudyPrep --}}
            <div class="pt-vocab-stats-grid-4">
                {{-- Tổng số từ đã học --}}
                <div class="pt-vocab-stat-col">
                    <span class="pt-vocab-stat-label">Đã học</span>
                    <span class="pt-vocab-stat-number">{{ $vocabStats['total_learned'] }}</span>
                    <span class="pt-vocab-stat-sub">tổng số từ</span>
                </div>

                {{-- Số từ nắm vững --}}
                <div class="pt-vocab-stat-col">
                    <span class="pt-vocab-stat-label">Nắm vững</span>
                    <span class="pt-vocab-stat-number pt-vocab-stat-number--green">{{ $vocabStats['mastered'] }}</span>
                    <span class="pt-vocab-stat-sub">{{ $vocabStats['mastery_rate'] }}% tổng số từ</span>
                </div>

                {{-- Số từ dễ quên --}}
                <div class="pt-vocab-stat-col">
                    <span class="pt-vocab-stat-label">Dễ quên</span>
                    <span class="pt-vocab-stat-number pt-vocab-stat-number--orange">{{ $vocabStats['forgotten'] }}</span>
                    <span class="pt-vocab-stat-sub">cần ôn luyện</span>
                </div>

                {{-- Tỉ lệ nắm vững --}}
                <div class="pt-vocab-stat-col">
                    <span class="pt-vocab-stat-label">Tỉ lệ nắm vững</span>
                    <span class="pt-vocab-stat-number pt-vocab-stat-number--blue">{{ $vocabStats['mastery_rate'] }}%</span>
                    <span class="pt-vocab-stat-sub">tiến độ học từ</span>
                </div>
            </div>

            {{-- Biểu đồ Doughnut thể hiện tỉ lệ từ nắm vững và dễ quên --}}
            @php
                $forgottenRate = $vocabStats['total_learned'] > 0 ? (100 - $vocabStats['mastery_rate']) : 0;
            @endphp
            <div class="pt-vocab-chart-row">
                <div class="pt-donut-container">
                    <svg viewBox="0 0 44 44" class="pt-donut-svg">
                        {{-- Vòng nền xám dày --}}
                        <circle cx="22" cy="22" r="15.91549430918954" fill="none" stroke="#F1F5F9" stroke-width="7.5"></circle>

                        @if($vocabStats['total_learned'] > 0)
                            {{-- Phân khúc Dễ quên (Cam) --}}
                            <circle cx="22" cy="22" r="15.91549430918954" fill="none" stroke="#F59E0B" stroke-width="7.5"
                                    stroke-dasharray="{{ $forgottenRate }} {{ 100 - $forgottenRate }}" stroke-dashoffset="-{{ $vocabStats['mastery_rate'] }}"></circle>

                            {{-- Phân khúc Nắm vững (Xanh lá) --}}
                            <circle cx="22" cy="22" r="15.91549430918954" fill="none" stroke="#22C55E" stroke-width="7.5"
                                    stroke-dasharray="{{ $vocabStats['mastery_rate'] }} {{ 100 - $vocabStats['mastery_rate'] }}"
                                    stroke-dashoffset="0"></circle>
                        @endif
                    </svg>
                    <div class="pt-donut-center-info">
                        <span class="pt-donut-center-val">{{ $vocabStats['mastery_rate'] }}%</span>
                        <span class="pt-donut-center-label">{{ $vocabStats['total_learned'] > 0 ? 'Nắm vững' : 'Chưa học' }}</span>
                    </div>
                </div>

                {{-- Chú giải số lượng từ --}}
                <div class="pt-vocab-chart-legends">
                    <div class="pt-vocab-legend-card">
                        <div class="pt-vocab-legend-left">
                            <span class="pt-vocab-legend-indicator pt-vocab-legend-indicator--green"></span>
                            <span class="pt-vocab-legend-title">Nắm vững</span>
                        </div>
                        <span class="pt-vocab-legend-val">{{ $vocabStats['mastered'] }} từ</span>
                    </div>

                    <div class="pt-vocab-legend-card">
                        <div class="pt-vocab-legend-left">
                            <span class="pt-vocab-legend-indicator pt-vocab-legend-indicator--orange"></span>
                            <span class="pt-vocab-legend-title">Dễ quên</span>
                        </div>
                        <span class="pt-vocab-legend-val">{{ $vocabStats['forgotten'] }} từ</span>
                    </div>
                </div>
            </div>

            {{-- Nút Xem chi tiết từ vựng --}}
            <div style="margin-top: 18px;">
                <x-button variant="primary" size="medium" class="pt-btn-full-width" :href="url('/tu-vung')">
                    Xem chi tiết từ vựng
                </x-button>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         PHẦN 5: HOẠT ĐỘNG GẦN ĐÂY
         ========================================================================= --}}
    <section class="pt-section" id="section-hoat-dong">
        <div class="pt-section-header">
            <h2 class="pt-section-title">Hoạt động gần đây</h2>
        </div>

        <div class="pt-card-box pt-card-box--flush" style="margin-bottom: 16px;">
            @forelse($recentActivities as $act)
                <a href="{{ $act['result_url'] }}" class="pt-activity-row" style="text-decoration: none; color: inherit; display: flex;">
                    <div class="pt-activity-left">
                        @if($act['icon'] === 'photo')
                            <div class="pt-activity-icon-wrap pt-activity-icon-wrap--photo">
                                {{-- Icon ảnh / tranh cho Part 1 --}}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                            </div>
                        @else
                            <div class="pt-activity-icon-wrap pt-activity-icon-wrap--test">
                                {{-- Icon bài kiểm tra / đề thi --}}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                            </div>
                        @endif

                        <div>
                            <h3 class="pt-activity-name">{{ $act['title'] }}</h3>
                            <p class="pt-activity-time">{{ $act['time'] }}</p>
                        </div>
                    </div>

                    <div class="pt-activity-right">
                        <span class="pt-activity-score-label">{{ $act['score_label'] }}</span>
                        <span class="pt-activity-score-number">{{ $act['score_val'] }}</span>
                    </div>
                </a>
            @empty
                <div style="padding: 32px 16px; text-align: center; color: var(--sp-text-muted, #64748B);">
                    <p style="font-size: 14.5px; font-weight: 700; margin: 0 0 4px; color: var(--sp-text-main, #111C2D);">Chưa có hoạt động nào</p>
                    <p style="font-size: 13px; margin: 0; color: var(--sp-text-light, #94A3B8);">Hãy bắt đầu luyện tập hoặc làm bài thi thử để theo dõi hoạt động học tập.</p>
                </div>
            @endforelse
        </div>

        <div style="margin-top: 18px;">
            <x-button variant="primary" size="medium" class="pt-btn-full-width" :href="url('/lich-su')">
                Xem tất cả
            </x-button>
        </div>
    </section>

</div>

{{-- MODAL GIẢI THÍCH ĐỘ THÀNH THẠO --}}
<div class="pt-modal-overlay" id="ptMasteryModal" onclick="closeMasteryModal(event)">
    <div class="pt-modal-content" onclick="event.stopPropagation()">
        <div class="pt-modal-header">
            <h3 class="pt-modal-title">Về chỉ số Độ thành thạo</h3>
            <button type="button" class="pt-modal-close-btn" onclick="closeMasteryModal()" aria-label="Đóng popup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="pt-modal-body">
            <p><strong>Độ thành thạo</strong> phản ánh năng lực thực tế của bạn trên từng phần thi dựa trên kết quả các lượt làm bài gần nhất kết hợp thuật toán đánh giá trọng số độ khó.</p>
            <p>Với các phần chưa thực hiện đủ số lượng câu tối thiểu, hệ thống sẽ hiển thị trạng thái <em>Chưa đánh giá</em> hoặc dự đoán tạm thời qua các câu hỏi tương quan.</p>
            <p>Hãy luyện tập thêm các câu hỏi trong mục <strong>Cải thiện</strong> để nâng cao độ thành thạo cho các Part còn yếu.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    /**
     * StudyPrep Analytics - Sticky Navigation & Scroll Spy Controller
     */
    const sectionIds = [
        'section-phan-thi',
        'section-luyen-de',
        'section-diem-yeu',
        'section-tu-vung',
        'section-hoat-dong'
    ];

    let isManualScrolling = false;
    let manualScrollTimer = null;

    /**
     * Cuộn mượt đến phần nội dung tương ứng và trừ hao Header + Sticky Nav
     */
    function scrollToSection(targetId) {
        const targetEl = document.getElementById(targetId);
        if (!targetEl) return;

        isManualScrolling = true;
        clearTimeout(manualScrollTimer);

        // Cập nhật tab active ngay lập tức trên UI
        updateActiveNav(targetId);

        // Cuộn mượt (scroll-margin-top đã định nghĩa sẵn trên CSS)
        targetEl.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // Tự động mở khoá sau khi cuộn kết thúc
        manualScrollTimer = setTimeout(() => {
            isManualScrolling = false;
        }, 800);
    }

    /**
     * Cập nhật trạng thái active trên thanh điều hướng
     */
    function updateActiveNav(activeId) {
        const navButtons = document.querySelectorAll('.pt-nav-item');
        navButtons.forEach(btn => {
            const target = btn.getAttribute('data-target');
            if (target === activeId) {
                btn.classList.add('is-active');
                // Tự động cuộn thanh nav ngang nếu bị che trên mobile
                btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                btn.classList.remove('is-active');
            }
        });
    }

    /**
     * Scroll Spy: Lắng nghe sự kiện scroll của trang
     */
    function handleScrollSpy() {
        if (isManualScrolling) return;

        const navWrapper = document.getElementById('ptStickyNavWrapper');
        const headerOffset = window.innerWidth <= 768 ? 68 : 80;
        const navHeight = navWrapper ? navWrapper.offsetHeight : 50;
        const scrollThreshold = headerOffset + navHeight + 30;

        // Xử lý hiệu ứng khi dính sticky
        if (navWrapper) {
            const rect = navWrapper.getBoundingClientRect();
            if (rect.top <= headerOffset + 1) {
                navWrapper.classList.add('pt-sticky-nav-scrolled');
            } else {
                navWrapper.classList.remove('pt-sticky-nav-scrolled');
            }
        }

        // Kiểm tra xem đã cuộn đến sát đáy trang chưa (đảm bảo mục Hoạt động active chính xác)
        const isBottom = (window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 50);
        if (isBottom) {
            updateActiveNav('section-hoat-dong');
            return;
        }

        // Duyệt qua từng section để tìm vị trí hiện tại
        let currentSectionId = sectionIds[0];
        for (let i = 0; i < sectionIds.length; i++) {
            const sectionEl = document.getElementById(sectionIds[i]);
            if (sectionEl) {
                const rect = sectionEl.getBoundingClientRect();
                if (rect.top <= scrollThreshold) {
                    currentSectionId = sectionIds[i];
                }
            }
        }

        updateActiveNav(currentSectionId);
    }

    // Gắn sự kiện cuộn với requestAnimationFrame tối ưu hiệu năng
    let scrollRafId = null;
    window.addEventListener('scroll', () => {
        if (scrollRafId) return;
        scrollRafId = requestAnimationFrame(() => {
            handleScrollSpy();
            scrollRafId = null;
        });
    }, { passive: true });

    // Khởi tạo Scroll Spy ban đầu
    document.addEventListener('DOMContentLoaded', () => {
        handleScrollSpy();
    });

    /**
     * Chuyển đổi bộ lọc thời gian bài thi Full Test (AJAX cập nhật biểu đồ không nhảy trang)
     */
    function switchFilterPeriod(btn, period) {
        document.querySelectorAll('.pt-filter-pill-btn').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');

        const box = document.getElementById('ptScoreChartBox');
        if (!box) return;

        fetch(`{{ route('phan-tich.chart-data') }}?period=${encodeURIComponent(period)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(res => {
            if (res.success && res.data) {
                renderScoreChart(res.data);
            }
        })
        .catch(err => {
            console.error('Lỗi tải dữ liệu biểu đồ:', err);
        });
    }

    /**
     * Render lại SVG biểu đồ Full Test từ dữ liệu JSON trả về
     */
    function renderScoreChart(data) {
        const box = document.getElementById('ptScoreChartBox');
        if (!box) return;

        if (!data.has_attempts || !data.points || data.points.length === 0) {
            box.innerHTML = `
                <div class="pt-trend-empty-wrap" style="padding: 42px 16px; text-align: center;">
                    <div style="width: 52px; height: 52px; border-radius: 50%; background: #F1F5F9; color: #64748B; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3v18h18"></path>
                            <path d="m19 9-5 5-4-4-3 3"></path>
                        </svg>
                    </div>
                    <h3 style="font-size: 16px; font-weight: 800; color: #111C2D; margin: 0 0 6px;">Chưa có bài thi Full Test nào trong khoảng này</h3>
                    <p style="font-size: 13.5px; color: #64748B; margin: 0 0 16px; max-width: 440px; margin-left: auto; margin-right: auto;">
                        Hãy hoàn thành bài thi thử ở chế độ Full Test để theo dõi xu hướng điểm số.
                    </p>
                    <a href="{{ url('/thi-thu') }}" class="sp-btn sp-btn-primary sp-btn-small" style="text-decoration: none; display: inline-flex;">
                        Làm bài thi thử
                    </a>
                </div>
            `;
            return;
        }

        let linesHtml = '';
        if (data.listening_path) {
            linesHtml += `<path d="${data.listening_path}" fill="none" stroke="#6366F1" stroke-width="2" stroke-dasharray="4 4" />`;
        }
        if (data.reading_path) {
            linesHtml += `<path d="${data.reading_path}" fill="none" stroke="#06B6D4" stroke-width="2" stroke-dasharray="4 4" />`;
        }
        if (data.total_path) {
            linesHtml += `<path d="${data.total_path}" fill="none" stroke="#22C55E" stroke-width="2.5" />`;
        }

        let pointsHtml = '';
        data.points.forEach(pt => {
            const safeY = Math.max(14, pt.y_total - 10);
            pointsHtml += `
                <text x="${pt.x}" y="190" fill="#64748B" font-size="12" font-weight="700" text-anchor="middle">${pt.date_label}</text>
                <text x="${pt.x}" y="${safeY}" fill="#111C2D" font-size="13" font-weight="800" text-anchor="middle">${pt.total_score}</text>
                <circle cx="${pt.x}" cy="${pt.y_total}" r="4.5" fill="#FFFFFF" stroke="#22C55E" stroke-width="3">
                    <title>${pt.test_title}: ${pt.total_score} điểm (${pt.full_date})</title>
                </circle>
                <circle cx="${pt.x}" cy="${pt.y_reading}" r="4" fill="#FFFFFF" stroke="#06B6D4" stroke-width="2.5">
                    <title>Đọc: ${pt.reading_score} điểm</title>
                </circle>
                <circle cx="${pt.x}" cy="${pt.y_listening}" r="3.5" fill="#6366F1">
                    <title>Nghe: ${pt.listening_score} điểm</title>
                </circle>
            `;
        });

        let bannerHtml = '';
        if (data.is_single_attempt) {
            bannerHtml = `
                <div class="pt-trend-notice-banner" id="ptTrendNoticeBanner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span>Chỉ có 1 lần thi trong khoảng này — chưa đủ để thấy xu hướng.</span>
                </div>
            `;
        }

        box.innerHTML = `
            <div class="pt-score-chart-canvas-area" id="ptScoreChartCanvasArea">
                <svg viewBox="0 0 760 200" preserveAspectRatio="none" style="width: 100%; height: 100%;">
                    <line x1="40" y1="20" x2="740" y2="20" stroke="#CBD5E1" stroke-width="1.2" stroke-dasharray="0" />
                    <text x="30" y="24" fill="#94A3B8" font-size="11" font-weight="600" text-anchor="end">990</text>

                    <line x1="40" y1="95" x2="740" y2="95" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3 3" />
                    <text x="30" y="99" fill="#94A3B8" font-size="11" font-weight="600" text-anchor="end">495</text>

                    <line x1="40" y1="170" x2="740" y2="170" stroke="#CBD5E1" stroke-width="1.2" stroke-dasharray="0" />
                    <text x="30" y="174" fill="#94A3B8" font-size="11" font-weight="600" text-anchor="end">0</text>

                    ${linesHtml}
                    ${pointsHtml}
                </svg>
            </div>
            <div class="pt-chart-legends">
                <span class="pt-legend-item">
                    <span class="pt-legend-dot pt-legend-dot--listening"></span>
                    Nghe
                </span>
                <span class="pt-legend-item">
                    <span class="pt-legend-dot pt-legend-dot--reading"></span>
                    Đọc
                </span>
                <span class="pt-legend-item">
                    <span class="pt-legend-dot pt-legend-dot--fulltest"></span>
                    Full Test
                </span>
            </div>
            ${bannerHtml}
        `;
    }

    /**
     * Quản lý modal Tìm hiểu thêm Độ thành thạo
     */
    function openMasteryModal() {
        const modal = document.getElementById('ptMasteryModal');
        if (modal) {
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeMasteryModal(e) {
        if (e && e.target !== e.currentTarget && !e.target.closest('.pt-modal-close-btn')) {
            return;
        }
        const modal = document.getElementById('ptMasteryModal');
        if (modal) {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    }

    // Đóng modal bằng phím Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMasteryModal();
        }
    });
</script>
@endpush

</x-layouts.app>
