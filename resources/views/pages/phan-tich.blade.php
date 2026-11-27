<x-layouts.app active="phan-tich" title="StudyPrep - Phân tích">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/phan-tich.css') }}">
@endpush

@php
    /*
     * DỮ LIỆU MẪU GIAO DIỆN PHÂN TÍCH (FE-018.2)
     * Tập trung toàn bộ dữ liệu mẫu tại đây để Backend dễ dàng truyền biến thay thế sau này.
     */

    // 1. Dữ liệu tổng quan Phần thi
    $analyticsStats = [
        'highest_score' => 225,
        'highest_test_name' => 'Test 1 TOEIC NEW 2026',
        'highest_test_date' => '27/11',
        'avg_accuracy' => 22,
        'correct_questions' => 48,
        'total_questions' => 220,
        'weakest_skill' => 'Đọc',
        'weakest_score_info' => '110/495 ở lần thi gần nhất',
        'wrong_questions_count' => 172,
    ];

    // Biểu đồ tỉ lệ đúng theo Part (Part 1 -> Part 7)
    $partAccuracyList = [
        ['part' => 'Part 1', 'rate' => 15, 'has_data' => true],
        ['part' => 'Part 2', 'rate' => 40, 'has_data' => true],
        ['part' => 'Part 3', 'rate' => 18, 'has_data' => true],
        ['part' => 'Part 4', 'rate' => 13, 'has_data' => true],
        ['part' => 'Part 5', 'rate' => 27, 'has_data' => true],
        ['part' => 'Part 6', 'rate' => 13, 'has_data' => true],
        ['part' => 'Part 7', 'rate' => 24, 'has_data' => true],
    ];

    // 2. Dữ liệu Luyện đề (Full Test)
    $fullTestData = [
        'has_attempts' => true,
        'is_single_attempt' => true,
        'recent_test' => [
            'listening_score' => 115,
            'reading_score' => 110,
            'total_score' => 225,
            'max_skill_score' => 495,
            'date' => '27/11',
            'balance_title' => 'Hai kỹ năng khá cân bằng',
            'balance_desc' => 'Nghe 115, Đọc 110 — hãy giữ nhịp luyện đều cả hai.',
        ],
    ];

    // 3. Dữ liệu Điểm yếu & Độ thành thạo
    $masteryParts = [
        ['part' => 'Part 1', 'status' => 'CẦN CẢI THIỆN', 'status_type' => 'red', 'rate' => 23, 'has_data' => true],
        ['part' => 'Part 2', 'status' => 'CẦN CẢI THIỆN', 'status_type' => 'red', 'rate' => 37, 'has_data' => true],
        ['part' => 'Part 3', 'status' => 'CẦN CẢI THIỆN', 'status_type' => 'red', 'rate' => 25, 'has_data' => true],
        ['part' => 'Part 4', 'status' => 'CẦN CẢI THIỆN', 'status_type' => 'red', 'rate' => 20, 'has_data' => true],
        ['part' => 'Part 5', 'status' => 'CẦN CẢI THIỆN', 'status_type' => 'red', 'rate' => 37, 'has_data' => true],
        ['part' => 'Part 6', 'status' => 'CẦN CẢI THIỆN', 'status_type' => 'red', 'rate' => 26, 'has_data' => true],
        ['part' => 'Part 7', 'status' => 'CẦN CẢI THIỆN', 'status_type' => 'red', 'rate' => 30, 'has_data' => true],
    ];

    $topWeaknesses = [
        [
            'rank' => 1,
            'meta' => 'NGHE · PART 4',
            'title' => 'Lời nói',
            'practiced_count' => 28,
            'mastery_rate' => 18,
        ],
        [
            'rank' => 2,
            'meta' => 'ĐỌC · PART 6',
            'title' => 'Ngữ pháp / cấu trúc',
            'practiced_count' => 13,
            'mastery_rate' => 18,
        ],
        [
            'rank' => 3,
            'meta' => 'NGHE · PART 3',
            'title' => 'Hội thoại 2 người',
            'practiced_count' => 36,
            'mastery_rate' => 19,
        ],
    ];

    // 4. Dữ liệu Từ vựng (Cập nhật đồng bộ cơ chế StudyPrep)
    $vocabStats = [
        'total_learned' => 16,
        'mastered' => 12,
        'forgotten' => 4,
        'mastery_rate' => 75, // 12 / 16 = 75%
    ];

    // 5. Hoạt động gần đây (Luyện tập & Thi thử)
    $recentActivities = [
        [
            'type' => 'part',
            'icon' => 'photo',
            'title' => 'Part 1: Mô tả hình',
            'time' => 'một ngày trước',
            'score_label' => 'Tỉ lệ đúng',
            'score_val' => '20%',
        ],
        [
            'type' => 'fulltest',
            'icon' => 'test',
            'title' => 'Test 1 TOEIC NEW 2026',
            'time' => 'một ngày trước',
            'score_label' => 'Tổng điểm',
            'score_val' => '225',
        ],
        [
            'type' => 'part',
            'icon' => 'photo',
            'title' => 'Part 1: Mô tả hình',
            'time' => 'một ngày trước',
            'score_label' => 'Tỉ lệ đúng',
            'score_val' => '20%',
        ],
    ];
@endphp

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
                    <span class="pt-stat-big-val pt-stat-big-val--amber">{{ $analyticsStats['weakest_skill'] }}</span>
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
                                    <div class="pt-col-pillar" style="height: {{ $item['rate'] }}%;"></div>
                                @else
                                    <span class="pt-col-val pt-col-val--empty">---</span>
                                    <div class="pt-col-pillar pt-col-pillar--unrated" style="height: 4px;"></div>
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
                <button type="button" class="pt-filter-pill-btn" onclick="switchFilterPeriod(this, '7')">7 ngày</button>
                <button type="button" class="pt-filter-pill-btn" onclick="switchFilterPeriod(this, '30')">30 ngày</button>
                <button type="button" class="pt-filter-pill-btn is-active" onclick="switchFilterPeriod(this, '90')">90 ngày</button>
                <button type="button" class="pt-filter-pill-btn" onclick="switchFilterPeriod(this, 'all')">Tất cả</button>
            </div>
        </div>

        <div class="pt-card-box">
            <div class="pt-score-chart-box">
                {{-- SVG Score Chart responsive --}}
                <div class="pt-score-chart-canvas-area">
                    <svg viewBox="0 0 760 200" preserveAspectRatio="none" style="width: 100%; height: 100%;">
                        {{-- Đường lưới Y (600, 300, 0) --}}
                        <line x1="40" y1="20" x2="740" y2="20" stroke="#CBD5E1" stroke-width="1.2" stroke-dasharray="0" />
                        <text x="30" y="24" fill="#94A3B8" font-size="11" font-weight="600" text-anchor="end">600</text>

                        <line x1="40" y1="95" x2="740" y2="95" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3 3" />
                        <text x="30" y="99" fill="#94A3B8" font-size="11" font-weight="600" text-anchor="end">300</text>

                        <line x1="40" y1="170" x2="740" y2="170" stroke="#CBD5E1" stroke-width="1.2" stroke-dasharray="0" />
                        <text x="30" y="174" fill="#94A3B8" font-size="11" font-weight="600" text-anchor="end">0</text>

                        {{-- Điểm dữ liệu (Vị trí 27/11 tại giữa trục x = 390) --}}
                        <text x="390" y="190" fill="#64748B" font-size="12" font-weight="700" text-anchor="middle">27/11</text>

                        {{-- Điểm 225 Full Test (Y tương ứng khoảng 114) --}}
                        <text x="390" y="66" fill="#111C2D" font-size="13" font-weight="800" text-anchor="middle">225</text>
                        <circle cx="390" cy="76" r="4.5" fill="#FFFFFF" stroke="#22C55E" stroke-width="3" />

                        {{-- Điểm Đọc 110 (Y khoảng 142) --}}
                        <circle cx="390" cy="115" r="4.5" fill="#FFFFFF" stroke="#06B6D4" stroke-width="3" />

                        {{-- Điểm Nghe 115 --}}
                        <circle cx="390" cy="113" r="3.5" fill="#6366F1" />
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
                    <div class="pt-trend-notice-banner">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <span>Chỉ có 1 lần thi trong khoảng này — chưa đủ để thấy xu hướng.</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Card kết quả lần thi hoàn chỉnh gần nhất --}}
        <div class="pt-card-box">
            <div class="pt-recent-test-card">
                {{-- Cột trái: Tiến độ 2 kỹ năng --}}
                <div class="pt-recent-test-left">
                    <span class="pt-stat-label">Lần thi hoàn chỉnh gần nhất</span>

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

            {{-- Biểu đồ Doughnut thể hiện tỉ lệ từ nắm vững và dễ quên (Dày dặn, tròn xoe dễ thương) --}}
            <div class="pt-vocab-chart-row">
                <div class="pt-donut-container">
                    <svg viewBox="0 0 44 44" class="pt-donut-svg">
                        {{-- Vòng nền xám dày --}}
                        <circle cx="22" cy="22" r="15.91549430918954" fill="none" stroke="#F1F5F9" stroke-width="7.5"></circle>

                        {{-- Phân khúc Dễ quên (Cam, 25%) --}}
                        <circle cx="22" cy="22" r="15.91549430918954" fill="none" stroke="#F59E0B" stroke-width="7.5"
                                stroke-dasharray="25 75" stroke-dashoffset="-75"></circle>

                        {{-- Phân khúc Nắm vững (Xanh lá, 75%) --}}
                        <circle cx="22" cy="22" r="15.91549430918954" fill="none" stroke="#22C55E" stroke-width="7.5"
                                stroke-dasharray="{{ $vocabStats['mastery_rate'] }} {{ 100 - $vocabStats['mastery_rate'] }}"
                                stroke-dashoffset="0"></circle>
                    </svg>
                    <div class="pt-donut-center-info">
                        <span class="pt-donut-center-val">{{ $vocabStats['mastery_rate'] }}%</span>
                        <span class="pt-donut-center-label">Nắm vững</span>
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
            @foreach($recentActivities as $act)
                <div class="pt-activity-row">
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
                </div>
            @endforeach
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
     * Chuyển đổi bộ lọc thời gian bài thi Full Test
     */
    function switchFilterPeriod(btn, period) {
        document.querySelectorAll('.pt-filter-pill-btn').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
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
