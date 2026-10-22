@extends('layouts.admin', ['active' => 'ngan-hang-cau-hoi'])

@section('title', 'Nhập câu hỏi TOEIC hàng loạt')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <span>Quản lý đào tạo</span>
    <span class="crumb-arrow">></span>
    <a href="{{ route('admin.questions.index') }}" style="color: inherit; text-decoration: none;">Ngân hàng câu hỏi</a>
    <span class="crumb-arrow">></span>
    <span style="color: var(--admin-primary); font-weight: 600;">Nhập hàng loạt (Bulk Import)</span>
@endsection

@section('content')
<div class="qimport-container">
    {{-- Form gửi lệnh nạp hàng loạt --}}
    <form action="{{ route('admin.questions.import') }}" method="POST" id="bulkImportForm">
        @csrf
        <input type="hidden" name="bulk_confirm" value="1">
        <input type="hidden" name="action_type" value="bulk_import">
        <input type="hidden" name="publish_status" id="publishStatusInput" value="draft">
        <input type="hidden" name="selected_count" id="selectedCountInput" value="116">

        {{-- 1. HEADER --}}
        <div class="qimport-header">
            <div class="qimport-title-row">
                <h1 class="qimport-page-title">Nhập câu hỏi TOEIC hàng loạt</h1>
                <span class="qimport-badge-code">#IMPORT-2025-04</span>
            </div>
            <div class="qimport-subtext">
                Excel, CSV, JSON & ETS Zip Media
            </div>
        </div>

        {{-- 2. STEPPER COMPONENT (4 BƯỚC) --}}
        <div class="qimport-stepper">
            {{-- Bước 1: Hoàn thành --}}
            <div class="qimport-step-card completed">
                <div class="qimport-step-num">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="qimport-step-meta">
                    <span class="qimport-step-tag">BƯỚC 1</span>
                    <span class="qimport-step-name">Tải lên tệp & Media</span>
                </div>
            </div>

            {{-- Bước 2: Hoàn thành --}}
            <div class="qimport-step-card completed">
                <div class="qimport-step-num">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="qimport-step-meta">
                    <span class="qimport-step-tag">BƯỚC 2</span>
                    <span class="qimport-step-name">Ánh xạ trường dữ liệu</span>
                </div>
            </div>

            {{-- Bước 3: Đang thực hiện (Active) --}}
            <div class="qimport-step-card active">
                <div class="qimport-step-num">3</div>
                <div class="qimport-step-meta">
                    <span class="qimport-step-tag">BƯỚC 3</span>
                    <span class="qimport-step-name">Kiểm tra hợp lệ & Xem trước</span>
                </div>
            </div>

            {{-- Bước 4: Chưa thực hiện --}}
            <div class="qimport-step-card pending">
                <div class="qimport-step-num">4</div>
                <div class="qimport-step-meta">
                    <span class="qimport-step-tag">BƯỚC 4</span>
                    <span class="qimport-step-name">Hoàn thành & Lưu ngân hàng</span>
                </div>
            </div>
        </div>

        {{-- 3. TOP 2-COLUMN GRID: TỆP NGUỒN VÀ CẤU HÌNH ĐÍCH ĐẾN --}}
        <div class="qimport-top-grid">
            {{-- Cột trái: Tệp nguồn & Dữ liệu đính kèm --}}
            <div class="qimport-card">
                <h2 class="qimport-card-title">
                    <span class="qimport-bar-accent"></span>
                    <span>Tệp nguồn & Dữ liệu đính kèm</span>
                </h2>

                {{-- Hai thẻ tệp đã nạp --}}
                <div class="qimport-files-row">
                    {{-- File 1: Excel --}}
                    <div class="qimport-file-box">
                        <div class="qimport-file-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="8" y1="13" x2="16" y2="13"></line>
                                <line x1="8" y1="17" x2="16" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
                        <div class="qimport-file-body">
                            <div class="qimport-file-name-row">
                                <span class="qimport-file-name" title="ETS_2024_Test05_Batch.xlsx">ETS_2024_Test05_Batch.xlsx</span>
                                <span class="qimport-file-check" title="Đã xác thực hợp lệ">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </span>
                            </div>
                            <span class="qimport-file-meta">2.4 MB • 120 câu hỏi TOEIC</span>
                            <div class="qimport-file-badges">
                                <span class="qimport-badge-pill purple">Part 1-7</span>
                                <span class="qimport-badge-time">Đã nạp 2 phút trước</span>
                            </div>
                        </div>
                    </div>

                    {{-- File 2: ZIP Media --}}
                    <div class="qimport-file-box">
                        <div class="qimport-file-icon zip">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="21 8 21 21 3 21 3 8"></polyline>
                                <rect x="1" y="3" width="22" height="5"></rect>
                                <line x1="10" y1="12" x2="14" y2="12"></line>
                            </svg>
                        </div>
                        <div class="qimport-file-body">
                            <div class="qimport-file-name-row">
                                <span class="qimport-file-name" title="ETS_Audio_Part1_Part4.zip">ETS_Audio_Part1_Part4.zip</span>
                            </div>
                            <span class="qimport-file-meta">45.8 MB • 48 tệp MP3 & Hình ảnh</span>
                            <div class="qimport-file-badges">
                                <span class="qimport-badge-pill green">100% khớp mã ID</span>
                                <span class="qimport-badge-time">Đã giải nén tạm</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4 Checkboxes tùy chọn thông minh --}}
                <div class="qimport-checkboxes-grid">
                    <label class="qimport-chk-item">
                        <input type="checkbox" name="opt_auto_part" value="1" checked>
                        <span>Tự động phân tách Part dựa trên cấu trúc đề ETS</span>
                    </label>
                    <label class="qimport-chk-item">
                        <input type="checkbox" name="opt_ai_difficulty" value="1" checked>
                        <span>Tự động gắn nhãn độ khó bằng StudyPREP AI Engine</span>
                    </label>
                    <label class="qimport-chk-item">
                        <input type="checkbox" name="opt_skip_duplicates" value="1" checked>
                        <span>Bỏ qua câu hỏi trùng lặp theo mã Hash</span>
                    </label>
                    <label class="qimport-chk-item">
                        <input type="checkbox" name="opt_flashcards" value="1" checked>
                        <span>Khởi tạo bộ thẻ Flashcards từ từ vựng nổi bật</span>
                    </label>
                </div>
            </div>

            {{-- Cột phải: Cấu hình Đích đến --}}
            <div class="qimport-card">
                <h2 class="qimport-card-title">
                    <span class="qimport-bar-accent green"></span>
                    <span>Cấu hình Đích đến</span>
                </h2>

                <div class="qimport-dest-field">
                    <label class="qimport-dest-label">Bộ đề / Khóa học mục tiêu</label>
                    <select name="target_set" class="qimport-dest-select">
                        <option value="ETS_2024_T05" selected>ETS TOEIC 2024 - Test 05 (Mới khởi tạo)</option>
                        <option value="ETS_2024_T04">ETS TOEIC 2024 - Test 04</option>
                        <option value="ETS_2024_T03">ETS TOEIC 2024 - Test 03</option>
                    </select>
                </div>

                <div class="qimport-dest-field">
                    <label class="qimport-dest-label">Chuyên viên kiểm duyệt nội dung</label>
                    <div class="qimport-reviewer-card">
                        <div class="qimport-reviewer-avatar">HT</div>
                        <div class="qimport-reviewer-info">
                            <span class="qimport-reviewer-name">ThS. Hoàng Minh Tuấn</span>
                            <span class="qimport-reviewer-role">Lead TOEIC Specialist • ETS Master</span>
                        </div>
                    </div>
                </div>

                <div class="qimport-dest-field">
                    <label class="qimport-dest-label">Trạng thái phát hành sau khi nhập</label>
                    <div class="qimport-radio-wrap">
                        {{-- Option 1: Draft --}}
                        <label class="qimport-radio-card active" id="radioCardDraft">
                            <input type="radio" name="publish_mode" value="draft" checked onchange="switchPublishMode('draft')">
                            <div class="qimport-radio-text">
                                <span class="qimport-radio-title">Lưu bản nháp (Draft)</span>
                                <span class="qimport-radio-desc">Chờ chuyên môn kiểm tra trước khi học viên có thể làm bài</span>
                            </div>
                        </label>

                        {{-- Option 2: Live --}}
                        <label class="qimport-radio-card" id="radioCardLive">
                            <input type="radio" name="publish_mode" value="live" onchange="switchPublishMode('live')">
                            <div class="qimport-radio-text">
                                <span class="qimport-radio-title">Xuất bản trực tiếp (Live Bank)</span>
                                <span class="qimport-radio-desc">Kích hoạt ngay cho hệ thống tạo đề thi thử trực tuyến</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. SECTION 2: ÁNH XẠ TRƯỜNG DỮ LIỆU (FIELD MAPPING) --}}
        <div class="qimport-mapping-card">
            <div class="qimport-section-header">
                <div class="qimport-section-title-wrap">
                    <h2 class="qimport-section-title">
                        <span class="qimport-bar-accent"></span>
                        <span>Ánh xạ trường dữ liệu (Field Mapping)</span>
                    </h2>
                    <p class="qimport-section-desc">Tự động đối chiếu các cột trong tệp Excel với schema CSDL StudyPREP</p>
                </div>
                <div class="qimport-sync-pill">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>14/14 trường khớp 100%</span>
                </div>
            </div>

            <div class="qimport-mapping-grid">
                {{-- Card 1 --}}
                <div class="qimport-map-item">
                    <span class="qimport-map-col-label">CỘT A • MÃ ĐỀ/CÂU</span>
                    <div class="qimport-map-key-row">
                        <span class="qimport-map-key">question_code</span>
                        <span class="qimport-map-arrow">⇄</span>
                    </div>
                    <a class="qimport-map-sublink" onclick="alert('Đã map với field ID duy nhất của câu hỏi')">Mã định danh duy nhất</a>
                </div>

                {{-- Card 2 --}}
                <div class="qimport-map-item">
                    <span class="qimport-map-col-label">CỘT B • KỸ NĂNG/PART</span>
                    <div class="qimport-map-key-row">
                        <span class="qimport-map-key">toeic_part</span>
                        <span class="qimport-map-arrow">⇄</span>
                    </div>
                    <a class="qimport-map-sublink" onclick="alert('Phân loại Part 1 đến Part 7')">Part 1 đến Part 7</a>
                </div>

                {{-- Card 3 --}}
                <div class="qimport-map-item">
                    <span class="qimport-map-col-label">CỘT C • NỘI DUNG/PASSAGE</span>
                    <div class="qimport-map-key-row">
                        <span class="qimport-map-key">question_body</span>
                        <span class="qimport-map-arrow">⇄</span>
                    </div>
                    <a class="qimport-map-sublink" onclick="alert('Nội dung đề bài hoặc transcript hội thoại')">Transcript / Đề bài</a>
                </div>

                {{-- Card 4 --}}
                <div class="qimport-map-item">
                    <span class="qimport-map-col-label">CỘT D–G • LỰA CHỌN A,B,C,D</span>
                    <div class="qimport-map-key-row">
                        <span class="qimport-map-key">option_a_d</span>
                        <span class="qimport-map-arrow">⇄</span>
                    </div>
                    <a class="qimport-map-sublink" onclick="alert('4 phương án trắc nghiệm A, B, C, D')">4 Phương án trả lời</a>
                </div>

                {{-- Card 5 --}}
                <div class="qimport-map-item">
                    <span class="qimport-map-col-label">CỘT H • ĐÁP ÁN CHUẨN</span>
                    <div class="qimport-map-key-row">
                        <span class="qimport-map-key">correct_key</span>
                        <span class="qimport-map-arrow">⇄</span>
                    </div>
                    <a class="qimport-map-sublink" onclick="alert('Khóa đáp án chính xác A, B, C hoặc D')">A / B / C / D</a>
                </div>

                {{-- Card 6 --}}
                <div class="qimport-map-item">
                    <span class="qimport-map-col-label">CỘT I • GIẢI THÍCH CHI TIẾT</span>
                    <div class="qimport-map-key-row">
                        <span class="qimport-map-key">explanation</span>
                        <span class="qimport-map-arrow">⇄</span>
                    </div>
                    <a class="qimport-map-sublink" onclick="alert('Dịch nghĩa câu và phân tích ngữ pháp')">Dịch nghĩa + Ngữ pháp</a>
                </div>

                {{-- Card 7 --}}
                <div class="qimport-map-item">
                    <span class="qimport-map-col-label">CỘT J • TỆP AUDIO/ẢNH</span>
                    <div class="qimport-map-key-row">
                        <span class="qimport-map-key">media_asset</span>
                        <span class="qimport-map-arrow">⇄</span>
                    </div>
                    <a class="qimport-map-sublink" onclick="alert('Khớp tên file đính kèm trong tệp ZIP')">Tên tệp trong ZIP</a>
                </div>

                {{-- Card 8 --}}
                <div class="qimport-map-item">
                    <span class="qimport-map-col-label">CỘT K • THẺ PHÂN LOẠI</span>
                    <div class="qimport-map-key-row">
                        <span class="qimport-map-key">tag_grammar</span>
                        <span class="qimport-map-arrow">⇄</span>
                    </div>
                    <a class="qimport-map-sublink" onclick="alert('Gắn thẻ chủ điểm ngữ pháp và từ vựng')">Chủ điểm ngữ pháp</a>
                </div>
            </div>
        </div>

        {{-- 5. SECTION 3: KIỂM TRA HỢP LỆ & XEM TRƯỚC DỮ LIỆU --}}
        <div class="qimport-preview-card">
            <div class="qimport-section-header">
                <div class="qimport-section-title-wrap">
                    <h2 class="qimport-section-title">
                        <span>Kiểm tra hợp lệ & Xem trước dữ liệu</span>
                    </h2>
                    <p class="qimport-section-desc">Phát hiện tự động các điểm bất thường trước khi ghi vào kho cơ sở dữ liệu chính</p>
                </div>
                <button type="button" class="qimport-export-log-btn" onclick="exportAuditLog()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>Xuất nhật ký kiểm thử (log)</span>
                </button>
            </div>

            {{-- 4 Thẻ chỉ số tổng kết --}}
            <div class="qimport-stat-grid">
                {{-- Thẻ 1: Tổng số --}}
                <div class="qimport-stat-box stat-total">
                    <div class="qimport-stat-icon-wrap">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="9" y1="9" x2="15" y2="9"></line>
                            <line x1="9" y1="13" x2="15" y2="13"></line>
                            <line x1="9" y1="17" x2="11" y2="17"></line>
                        </svg>
                    </div>
                    <div class="qimport-stat-data">
                        <span class="qimport-stat-label">TỔNG SỐ PHÁT HIỆN</span>
                        <span class="qimport-stat-value" id="statTotalVal">120 câu</span>
                    </div>
                </div>

                {{-- Thẻ 2: Hợp lệ --}}
                <div class="qimport-stat-box stat-valid">
                    <div class="qimport-stat-icon-wrap">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="qimport-stat-data">
                        <span class="qimport-stat-label">HỢP LỆ SẴN SÀNG</span>
                        <div class="qimport-stat-value">
                            <span id="statValidVal">116 câu</span>
                            <span class="qimport-stat-pct" id="statValidPct">(96.7%)</span>
                        </div>
                    </div>
                </div>

                {{-- Thẻ 3: Cảnh báo --}}
                <div class="qimport-stat-box stat-warning">
                    <div class="qimport-stat-icon-wrap">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <div class="qimport-stat-data">
                        <span class="qimport-stat-label">CẢNH BÁO XEM XÉT</span>
                        <div class="qimport-stat-value">
                            <span id="statWarnVal">3 câu</span>
                            <span class="qimport-stat-pct" id="statWarnPct">(2.5%)</span>
                        </div>
                    </div>
                </div>

                {{-- Thẻ 4: Lỗi chặn --}}
                <div class="qimport-stat-box stat-error">
                    <div class="qimport-stat-icon-wrap">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    </div>
                    <div class="qimport-stat-data">
                        <span class="qimport-stat-label">LỖI KHÔNG THỂ NẠP</span>
                        <div class="qimport-stat-value">
                            <span id="statErrVal">1 câu</span>
                            <span class="qimport-stat-pct" id="statErrPct">(0.8%)</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bộ lọc xem trước (Filter Tabs) --}}
            <div class="qimport-filter-tabs">
                <button type="button" class="qimport-filter-tab active" id="tabAll" onclick="filterTable('all')">Tất cả (120)</button>
                <button type="button" class="qimport-filter-tab" id="tabValid" onclick="filterTable('valid')">Hợp lệ (116)</button>
                <button type="button" class="qimport-filter-tab" id="tabWarn" onclick="filterTable('warn')">Cảnh báo (3)</button>
                <button type="button" class="qimport-filter-tab" id="tabErr" onclick="filterTable('err')">Có lỗi chặn (1)</button>
            </div>

            {{-- Bảng xem trước dữ liệu (Preview Table) --}}
            <div class="qimport-table-wrap">
                <table class="qimport-table" id="previewTable">
                    <thead>
                        <tr>
                            <th style="width: 38px;">
                                <input type="checkbox" id="masterCheckbox" checked onchange="toggleMasterCheck(this)" style="accent-color: #4f46e5; cursor: pointer;">
                            </th>
                            <th style="width: 110px;">DÒNG / ID</th>
                            <th style="width: 170px;">PART / KỸ NĂNG</th>
                            <th>TRÍCH ĐOẠN NỘI DUNG CÂU HỎI</th>
                            <th style="width: 70px; text-align: center;">ĐÁP ÁN</th>
                            <th style="width: 200px;">TRẠNG THÁI XÁC THỰC</th>
                            <th style="width: 110px; text-align: center;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- HÀNG 1: Hợp lệ 100% --}}
                        <tr class="qimport-row row-valid" id="row1">
                            <td>
                                <input type="checkbox" class="qimport-row-chk" checked onchange="updateCheckedCount()" style="accent-color: #4f46e5;">
                            </td>
                            <td>
                                <span class="qimport-cell-id">#01 / Q-101</span>
                            </td>
                            <td>
                                <div class="qimport-cell-part">
                                    <span class="qimport-part-badge">Part 5</span>
                                    <span class="qimport-part-sub">Ngữ pháp / Reading</span>
                                </div>
                            </td>
                            <td>
                                <div class="qimport-content-cell">
                                    <span class="qimport-content-title">Ms. Tanaka requested that all department heads submit</span>
                                    <span class="qimport-content-choices">(A) until • (B) before • (C) since • (D) toward</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="qimport-key-badge">B</span>
                            </td>
                            <td>
                                <span class="qimport-status-pill valid">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    <span>Hợp lệ 100%</span>
                                </span>
                            </td>
                            <td>
                                <div class="qimport-row-actions" style="justify-content: center;">
                                    <button type="button" class="qimport-icon-btn" title="Chỉnh sửa dòng">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" class="qimport-icon-btn" title="Xem chi tiết">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="16" x2="12" y2="12"></line>
                                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- HÀNG 2: Hợp lệ (Đã nối Media) --}}
                        <tr class="qimport-row row-valid" id="row2">
                            <td>
                                <input type="checkbox" class="qimport-row-chk" checked onchange="updateCheckedCount()" style="accent-color: #4f46e5;">
                            </td>
                            <td>
                                <span class="qimport-cell-id">#02 / Q-001</span>
                            </td>
                            <td>
                                <div class="qimport-cell-part">
                                    <span class="qimport-part-badge">Part 1</span>
                                    <span class="qimport-part-sub">Photographs / Audio</span>
                                </div>
                            </td>
                            <td>
                                <div class="qimport-content-cell">
                                    <span class="qimport-content-title" style="display: flex; align-items: center; gap: 6px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                            <polyline points="21 15 16 10 5 21"></polyline>
                                        </svg>
                                        <span>ETS_2024_P1_01.png • Audio_01.mp3 (0:14s)</span>
                                    </span>
                                    <span class="qimport-content-choices">[A] A man is holding a briefcase. [B] A man is boarding a train.</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="qimport-key-badge">A</span>
                            </td>
                            <td>
                                <span class="qimport-status-pill valid">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    <span>Hợp lệ (Đã nối Media)</span>
                                </span>
                            </td>
                            <td>
                                <div class="qimport-row-actions" style="justify-content: center;">
                                    <button type="button" class="qimport-icon-btn" title="Nghe âm thanh" onclick="alert('Đang phát Audio_01.mp3')">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                        </svg>
                                    </button>
                                    <button type="button" class="qimport-icon-btn" title="Chỉnh sửa dòng">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- HÀNG 3: Cảnh báo (Thiếu bản dịch) --}}
                        <tr class="qimport-row row-warning" id="row3">
                            <td>
                                <input type="checkbox" class="qimport-row-chk" checked onchange="updateCheckedCount()" style="accent-color: #4f46e5;">
                            </td>
                            <td>
                                <span class="qimport-cell-id">#24 / Q-147</span>
                            </td>
                            <td>
                                <div class="qimport-cell-part">
                                    <span class="qimport-part-badge">Part 7</span>
                                    <span class="qimport-part-sub">Single Passage</span>
                                </div>
                            </td>
                            <td>
                                <div class="qimport-content-cell">
                                    <span class="qimport-content-title">What is indicated about the newly scheduled..</span>
                                    <span class="qimport-content-note-warn" id="row3WarnNote">Cảnh báo: Cột I (Giải thích chi tiết & Bản dịch) đang để trống</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="qimport-key-badge">C</span>
                            </td>
                            <td>
                                <span class="qimport-status-pill warn" id="row3StatusPill">
                                    <span>• Thiếu bản dịch + AI sẵn sàng điền</span>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="qimport-btn-fix" id="btnAiFix" onclick="fixRow3Ai()">
                                    AI Auto-Fix
                                </button>
                            </td>
                        </tr>

                        {{-- HÀNG 4: Lỗi chặn (Thiếu Key) --}}
                        <tr class="qimport-row row-error" id="row4">
                            <td>
                                <input type="checkbox" class="qimport-row-chk" id="row4Chk" onchange="updateCheckedCount()" style="accent-color: #be123c;">
                            </td>
                            <td>
                                <span class="qimport-cell-id">#42 / Q-122</span>
                            </td>
                            <td>
                                <div class="qimport-cell-part">
                                    <span class="qimport-part-badge red" id="row4BadgePart">Part 5</span>
                                    <span class="qimport-part-sub">Incomplete Sentences</span>
                                </div>
                            </td>
                            <td>
                                <div class="qimport-content-cell">
                                    <span class="qimport-content-title">Neither the director _______ the supervisors were...</span>
                                    <span class="qimport-content-note-err" id="row4ErrNote">Lỗi chặn: Cột H (Đáp án đúng) đang trống. Không thể xác thực chấm điểm.</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="qimport-key-badge err" id="row4KeyBadge">?</span>
                            </td>
                            <td>
                                <span class="qimport-status-pill err" id="row4StatusPill">
                                    <span>• Lỗi chặn (Thiếu Key)</span>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="qimport-btn-fix err" id="btnFixKey" onclick="fixRow4Key()">
                                    Bổ sung Key
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Phân trang & Thống kê hiển thị --}}
            <div class="qimport-table-footer">
                <div>
                    Hiển thị 1 - 4 của 120 câu hỏi • <a href="javascript:void(0)" onclick="filterTable('valid')" style="color: #2563eb; font-weight: 600; text-decoration: none;">Đã chọn <span id="labelCheckedCount">116</span> câu hợp lệ</a>
                </div>
                <div class="qimport-pagination">
                    <button type="button" class="qimport-page-btn" title="Trang trước">&lt;</button>
                    <button type="button" class="qimport-page-btn active">1</button>
                    <button type="button" class="qimport-page-btn">2</button>
                    <button type="button" class="qimport-page-btn">3</button>
                    <span style="color: #94a3b8; padding: 0 4px;">...</span>
                    <button type="button" class="qimport-page-btn">12</button>
                    <button type="button" class="qimport-page-btn" title="Trang sau">&gt;</button>
                </div>
            </div>
        </div>

        {{-- 6. STICKY BOTTOM ACTION BAR --}}
        <div class="qimport-bottom-bar">
            <div class="qimport-bottom-left">
                <div class="qimport-bottom-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="qimport-bottom-info">
                    <span class="qimport-bottom-headline" id="bottomHeadline">Sẵn sàng nạp 116 / 120 câu hỏi TOEIC vào Ngân hàng</span>
                    <span class="qimport-bottom-sub" id="bottomSubtext">Đã loại trừ 1 câu lỗi cấu trúc • 3 câu cảnh báo sẽ được AI tự hoàn thiện</span>
                </div>
            </div>
            <div class="qimport-bottom-actions">
                <a href="{{ route('admin.questions.index') }}" class="qimport-btn-cancel">
                    Hủy bỏ
                </a>
                <button type="button" class="qimport-btn-draft" onclick="saveDraftConfig()">
                    Lưu cấu hình nháp
                </button>
                <button type="submit" class="qimport-btn-submit" id="btnSubmitBulk">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span id="btnSubmitText">Bắt đầu Nhập 116 câu hỏi</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    var currentValidCount = 116;
    var currentWarnCount = 3;
    var currentErrCount = 1;

    // 1. Chuyển đổi chế độ phát hành (Draft vs Live)
    function switchPublishMode(mode) {
        document.getElementById('publishStatusInput').value = mode;
        var cardDraft = document.getElementById('radioCardDraft');
        var cardLive = document.getElementById('radioCardLive');
        if (cardDraft && cardLive) {
            cardDraft.classList.toggle('active', mode === 'draft');
            cardLive.classList.toggle('active', mode === 'live');
        }
    }

    // 2. Lọc bảng theo tab
    function filterTable(filter) {
        ['tabAll', 'tabValid', 'tabWarn', 'tabErr'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.remove('active');
        });

        if (filter === 'all') document.getElementById('tabAll').classList.add('active');
        if (filter === 'valid') document.getElementById('tabValid').classList.add('active');
        if (filter === 'warn') document.getElementById('tabWarn').classList.add('active');
        if (filter === 'err') document.getElementById('tabErr').classList.add('active');

        var rows = document.querySelectorAll('.qimport-row');
        rows.forEach(function(row) {
            if (filter === 'all') {
                row.style.display = '';
            } else if (filter === 'valid') {
                row.style.display = row.classList.contains('row-valid') ? '' : 'none';
            } else if (filter === 'warn') {
                row.style.display = row.classList.contains('row-warning') ? '' : 'none';
            } else if (filter === 'err') {
                row.style.display = row.classList.contains('row-error') ? '' : 'none';
            }
        });
    }

    // 3. Tự động sửa cảnh báo bằng AI (Row 3)
    function fixRow3Ai() {
        var row = document.getElementById('row3');
        var note = document.getElementById('row3WarnNote');
        var pill = document.getElementById('row3StatusPill');
        var btn = document.getElementById('btnAiFix');
        if (!row || !pill) return;

        btn.disabled = true;
        btn.textContent = 'Đang xử lý AI...';

        setTimeout(function() {
            row.classList.remove('row-warning');
            row.classList.add('row-valid');

            if (note) {
                note.textContent = 'Đã tạo bản dịch & giải thích chi tiết bởi StudyPREP AI Engine.';
                note.className = 'qimport-content-choices';
                note.style.color = '#059669';
            }

            pill.className = 'qimport-status-pill valid';
            pill.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Hợp lệ 100% (Đã AI Fix)</span>';

            btn.textContent = 'Đã hoàn tất';
            btn.style.color = '#059669';
            btn.style.borderColor = '#a7f3d0';

            currentWarnCount = Math.max(0, currentWarnCount - 1);
            currentValidCount++;
            updateStatsUI();
        }, 350);
    }

    // 4. Bổ sung Key cho dòng 4
    function fixRow4Key() {
        var key = prompt('Nhập phương án đúng (A, B, C hoặc D):', 'B');
        if (!key) return;
        key = key.trim().toUpperCase();
        if (!['A', 'B', 'C', 'D'].includes(key)) {
            alert('Phương án phải là A, B, C hoặc D');
            return;
        }

        var row = document.getElementById('row4');
        var keyBadge = document.getElementById('row4KeyBadge');
        var errNote = document.getElementById('row4ErrNote');
        var pill = document.getElementById('row4StatusPill');
        var btn = document.getElementById('btnFixKey');
        var partBadge = document.getElementById('row4BadgePart');
        var chk = document.getElementById('row4Chk');

        if (!row) return;

        row.classList.remove('row-error');
        row.classList.add('row-valid');

        if (keyBadge) {
            keyBadge.textContent = key;
            keyBadge.classList.remove('err');
        }

        if (partBadge) {
            partBadge.classList.remove('red');
        }

        if (errNote) {
            errNote.textContent = 'Đã cập nhật đáp án chuẩn: (' + key + '). Sẵn sàng chấm điểm.';
            errNote.className = 'qimport-content-choices';
            errNote.style.color = '#059669';
        }

        if (pill) {
            pill.className = 'qimport-status-pill valid';
            pill.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Hợp lệ (Đã bổ sung Key)</span>';
        }

        if (btn) {
            btn.textContent = 'Đã bổ sung';
            btn.className = 'qimport-btn-fix';
            btn.style.color = '#059669';
            btn.style.borderColor = '#a7f3d0';
            btn.disabled = true;
        }

        if (chk) {
            chk.checked = true;
        }

        currentErrCount = Math.max(0, currentErrCount - 1);
        currentValidCount++;
        updateStatsUI();
    }

    // 5. Cập nhật các chỉ số và thanh bottom bar
    function updateStatsUI() {
        var validEl = document.getElementById('statValidVal');
        var warnEl = document.getElementById('statWarnVal');
        var errEl = document.getElementById('statErrVal');
        var tabWarn = document.getElementById('tabWarn');
        var tabErr = document.getElementById('tabErr');
        var tabValid = document.getElementById('tabValid');

        if (validEl) validEl.textContent = currentValidCount + ' câu';
        if (warnEl) warnEl.textContent = currentWarnCount + ' câu';
        if (errEl) errEl.textContent = currentErrCount + ' câu';

        if (tabValid) tabValid.textContent = 'Hợp lệ (' + currentValidCount + ')';
        if (tabWarn) tabWarn.textContent = 'Cảnh báo (' + currentWarnCount + ')';
        if (tabErr) tabErr.textContent = 'Có lỗi chặn (' + currentErrCount + ')';

        var headline = document.getElementById('bottomHeadline');
        var subtext = document.getElementById('bottomSubtext');
        var btnText = document.getElementById('btnSubmitText');
        var labelChecked = document.getElementById('labelCheckedCount');
        var selectedInput = document.getElementById('selectedCountInput');

        if (headline) headline.textContent = 'Sẵn sàng nạp ' + currentValidCount + ' / 120 câu hỏi TOEIC vào Ngân hàng';
        if (subtext) {
            subtext.textContent = 'Đã loại trừ ' + currentErrCount + ' câu lỗi cấu trúc • ' + currentWarnCount + ' câu cảnh báo sẽ được AI tự hoàn thiện';
        }
        if (btnText) btnText.textContent = 'Bắt đầu Nhập ' + currentValidCount + ' câu hỏi';
        if (labelChecked) labelChecked.textContent = currentValidCount;
        if (selectedInput) selectedInput.value = currentValidCount;
    }

    // 6. Master Checkbox
    function toggleMasterCheck(master) {
        var chks = document.querySelectorAll('.qimport-row-chk');
        chks.forEach(function(chk) {
            chk.checked = master.checked;
        });
        updateCheckedCount();
    }

    function updateCheckedCount() {
        var checked = document.querySelectorAll('.qimport-row-chk:checked').length;
        var labelChecked = document.getElementById('labelCheckedCount');
        if (labelChecked) labelChecked.textContent = checked;
    }

    // 7. Xuất nhật ký kiểm thử
    function exportAuditLog() {
        var logContent = "=== NHẬT KÝ KIỂM THỬ NHẬP CÂU HỎI HÀNG LOẠT ===\n" +
            "Mã phiên nạp: #IMPORT-2025-04\n" +
            "Thời gian: " + new Date().toLocaleString() + "\n" +
            "Tệp nguồn: ETS_2024_Test05_Batch.xlsx (2.4 MB)\n" +
            "Tệp media: ETS_Audio_Part1_Part4.zip (45.8 MB)\n" +
            "Tổng số câu phát hiện: 120 câu\n" +
            "Số câu hợp lệ 100%: " + currentValidCount + " câu\n" +
            "Số câu cảnh báo: " + currentWarnCount + " câu\n" +
            "Số câu lỗi cấu trúc/thiếu đáp án: " + currentErrCount + " câu\n" +
            "Trạng thái kiểm định: Sẵn sàng đồng bộ CSDL.\n";

        var blob = new Blob([logContent], { type: 'text/plain;charset=utf-8' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'audit_log_import_2025_04.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // 8. Lưu cấu hình nháp
    function saveDraftConfig() {
        alert('Đã lưu cấu hình ánh xạ trường và danh sách câu hỏi kiểm thử dưới dạng bản nháp!');
    }
</script>
@endpush
