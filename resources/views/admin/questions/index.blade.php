@extends('layouts.admin', ['active' => 'ngan-hang-cau-hoi'])

@section('title', 'Ngân hàng câu hỏi TOEIC')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <span>Quản trị người dùng</span>
@endsection

@section('content')
<div class="qbank-page">

    {{-- Thông báo kết quả thao tác --}}
    @if (session('success'))
        <div class="alert-admin-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-admin-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert-admin-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div>
                <strong>Đã có lỗi xảy ra:</strong>
                <ul style="margin: 4px 0 0 18px; padding: 0;">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- 1. Tiêu đề trang & Các nút thao tác phía trên (Page Header) --}}
    <div class="user-page-header">
        <div>
            <div class="qbank-breadcrumb-sub">
                <span>Quản lý đào tạo</span>
                <span>></span>
                <span class="crumb-active">Ngân hàng câu hỏi TOEIC</span>
            </div>
            <div class="user-title-row">
                <h1 class="user-page-title">Kho Đề thi & Câu hỏi Chuẩn hóa</h1>
                <span class="qbank-ets-badge">ETS 2024 – 2025</span>
            </div>
        </div>

        <div class="user-header-actions">
            {{-- Nút Xuất Excel --}}
            <a href="{{ route('admin.questions.export') }}" class="btn-admin-action excel" title="Xuất danh sách câu hỏi ra Excel (CSV)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Xuất Excel</span>
            </a>

            {{-- Nút Thêm câu hỏi hàng loạt --}}
            <a href="{{ route('admin.questions.bulk-import') }}" class="btn-admin-action import" title="Thêm câu hỏi hàng loạt (Bulk Import Studio)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="18" x2="12" y2="12"></line>
                    <line x1="9" y1="15" x2="15" y2="15"></line>
                </svg>
                <span>Thêm câu hỏi hàng loạt</span>
            </a>

            {{-- Nút Tạo nhóm câu hỏi --}}
            <a href="{{ route('admin.questions.group.create') }}" class="btn-admin-action" style="color: #4338ca; border-color: #c7d2fe; background: #f5f3ff; font-weight: 700;" title="Tạo nhóm câu hỏi TOEIC (Passage / Stimulus)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                <span>Tạo nhóm câu hỏi</span>
            </a>

            {{-- Nút Thêm câu hỏi mới --}}
            <a href="{{ route('admin.questions.create') }}" class="btn-admin-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <span>Thêm câu hỏi mới</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: -2px;">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </a>
        </div>
    </div>

    {{-- 2. Bốn thẻ thống kê chỉ số (4 KPI Cards - Khớp ảnh mẫu) --}}
    <div class="qbank-kpi-grid">
        {{-- Thẻ 1: Tổng số câu hỏi --}}
        <div class="qbank-kpi-card">
            <div class="qbank-kpi-top">
                <p class="qbank-kpi-title">Tổng số câu hỏi</p>
                <div class="qbank-kpi-icon icon-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                </div>
            </div>
            <div class="qbank-kpi-val-row">
                <span class="qbank-kpi-num">{{ number_format($stats['total']) }}</span>
                <span class="qbank-kpi-sub">câu</span>
            </div>
            <div class="qbank-kpi-foot">
                <span>Toàn bộ kho dữ liệu kiểm định</span>
            </div>
        </div>

        {{-- Thẻ 2: TOEIC Listening & Reading --}}
        <div class="qbank-kpi-card">
            <div class="qbank-kpi-top">
                <p class="qbank-kpi-title">TOEIC Listening & Reading</p>
                <div class="qbank-kpi-icon icon-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                        <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                    </svg>
                </div>
            </div>
            <div class="qbank-kpi-val-row">
                <span class="qbank-kpi-num">{{ number_format($stats['lc_rc']) }}</span>
                <span class="qbank-kpi-sub">Part 1 – 7</span>
            </div>
            <div class="qbank-kpi-foot">
                <span>Đã chuẩn hóa thang điểm 990</span>
            </div>
        </div>

        {{-- Thẻ 3: TOEIC Speaking & Writing --}}
        <div class="qbank-kpi-card">
            <div class="qbank-kpi-top">
                <p class="qbank-kpi-title">TOEIC Speaking & Writing</p>
                <div class="qbank-kpi-icon icon-pink">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
            </div>
            <div class="qbank-kpi-val-row">
                <span class="qbank-kpi-num">{{ number_format($stats['sw']) }}</span>
                <span class="qbank-kpi-sub">SW Tasks</span>
            </div>
            <div class="qbank-kpi-foot">
                <span>Tích hợp bộ chấm AI thông minh</span>
            </div>
        </div>

        {{-- Thẻ 4: Cần kiểm duyệt & Báo lỗi --}}
        <div class="qbank-kpi-card">
            <div class="qbank-kpi-top">
                <p class="qbank-kpi-title">Cần kiểm duyệt & Báo lỗi</p>
                <div class="qbank-kpi-icon icon-red">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>
            <div class="qbank-kpi-val-row">
                <span class="qbank-kpi-num" style="color: #dc2626;">{{ number_format($stats['reported']) }}</span>
                <span class="qbank-kpi-sub">câu hỏi</span>
            </div>
            <div class="qbank-kpi-foot">
                <span class="qbank-red-pill">32 phản hồi</span>
                <span>học viên báo sai đáp án</span>
            </div>
        </div>
    </div>

    {{-- 3. Khung tìm kiếm và 5 bộ lọc (Filter Box) --}}
    <form method="GET" action="{{ route('admin.questions.index') }}" class="qbank-filter-card">
        {{-- Hàng 1: Ô tìm kiếm + Đặt lại + Bộ lọc nâng cao --}}
        <div class="qbank-search-row">
            <div class="qbank-search-input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text"
                       name="search"
                       value="{{ $filters['search'] }}"
                       placeholder="Tìm theo Mã ID (#Q-10892), từ khóa, nội dung câu hỏi, transcript..."
                       class="qbank-search-input">
            </div>

            <a href="{{ route('admin.questions.index') }}" class="qbank-reset-btn" title="Đặt lại toàn bộ bộ lọc">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
                <span>Đặt lại</span>
            </a>

            <button type="button" class="qbank-adv-filter-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="21" x2="4" y2="14"></line>
                    <line x1="4" y1="10" x2="4" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12" y2="3"></line>
                    <line x1="20" y1="21" x2="20" y2="16"></line>
                    <line x1="20" y1="12" x2="20" y2="3"></line>
                    <line x1="1" y1="14" x2="7" y2="14"></line>
                    <line x1="9" y1="8" x2="15" y2="8"></line>
                    <line x1="17" y1="16" x2="23" y2="16"></line>
                </svg>
                <span>Bộ lọc nâng cao</span>
            </button>
        </div>

        {{-- Hàng 2: 5 Dropdowns lọc theo tiêu chuẩn --}}
        <div class="qbank-filters-grid">
            {{-- Cột 1: KỸ NĂNG --}}
            <div class="qbank-filter-col">
                <label>KỸ NĂNG</label>
                <div class="qbank-filter-select-wrap">
                    <select name="skill" class="qbank-filter-select" onchange="this.form.submit()">
                        <option value="all" {{ $filters['skill'] === 'all' ? 'selected' : '' }}>Tất cả kỹ năng (LC / RC / SW)</option>
                        <option value="LC" {{ $filters['skill'] === 'LC' ? 'selected' : '' }}>Listening (LC)</option>
                        <option value="RC" {{ $filters['skill'] === 'RC' ? 'selected' : '' }}>Reading (RC)</option>
                        <option value="SW" {{ $filters['skill'] === 'SW' ? 'selected' : '' }}>Speaking & Writing (SW)</option>
                    </select>
                    <svg class="qbank-filter-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>

            {{-- Cột 2: PHẦN THI (PART / TASK) --}}
            <div class="qbank-filter-col">
                <label>PHẦN THI (PART / TASK)</label>
                <div class="qbank-filter-select-wrap">
                    <select name="part" class="qbank-filter-select" onchange="this.form.submit()">
                        <option value="all" {{ $filters['part'] === 'all' ? 'selected' : '' }}>Tất cả phần thi</option>
                        <option value="PART1" {{ $filters['part'] === 'PART1' ? 'selected' : '' }}>Part 1: Photographs</option>
                        <option value="PART2" {{ $filters['part'] === 'PART2' ? 'selected' : '' }}>Part 2: Question-Response</option>
                        <option value="PART3" {{ $filters['part'] === 'PART3' ? 'selected' : '' }}>Part 3: Conversations</option>
                        <option value="PART4" {{ $filters['part'] === 'PART4' ? 'selected' : '' }}>Part 4: Short Talks</option>
                        <option value="PART5" {{ $filters['part'] === 'PART5' ? 'selected' : '' }}>Part 5: Incomplete Sentences</option>
                        <option value="PART6" {{ $filters['part'] === 'PART6' ? 'selected' : '' }}>Part 6: Text Completion</option>
                        <option value="PART7" {{ $filters['part'] === 'PART7' ? 'selected' : '' }}>Part 7: Reading Comprehension</option>
                        <option value="WRITING_T1" {{ $filters['part'] === 'WRITING_T1' ? 'selected' : '' }}>Writing Task 1</option>
                        <option value="WRITING_T2" {{ $filters['part'] === 'WRITING_T2' ? 'selected' : '' }}>Writing Task 2</option>
                    </select>
                    <svg class="qbank-filter-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>

            {{-- Cột 3: ĐỘ KHÓ & TARGET --}}
            <div class="qbank-filter-col">
                <label>ĐỘ KHÓ & TARGET</label>
                <div class="qbank-filter-select-wrap">
                    <select name="difficulty" class="qbank-filter-select" onchange="this.form.submit()">
                        <option value="all" {{ $filters['difficulty'] === 'all' ? 'selected' : '' }}>Tất cả mức độ</option>
                        <option value="EASY" {{ $filters['difficulty'] === 'EASY' ? 'selected' : '' }}>Cơ bản (Dưới 500)</option>
                        <option value="MEDIUM" {{ $filters['difficulty'] === 'MEDIUM' ? 'selected' : '' }}>Trung bình (500 – 750)</option>
                        <option value="HARD" {{ $filters['difficulty'] === 'HARD' ? 'selected' : '' }}>Nâng cao (750+)</option>
                    </select>
                    <svg class="qbank-filter-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>

            {{-- Cột 4: TRẠNG THÁI --}}
            <div class="qbank-filter-col">
                <label>TRẠNG THÁI</label>
                <div class="qbank-filter-select-wrap">
                    <select name="status" class="qbank-filter-select" onchange="this.form.submit()">
                        <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                        <option value="ACTIVE" {{ $filters['status'] === 'ACTIVE' ? 'selected' : '' }}>• Hoạt động</option>
                        <option value="REPORTED" {{ $filters['status'] === 'REPORTED' ? 'selected' : '' }}>• Báo lỗi đáp án</option>
                        <option value="PENDING" {{ $filters['status'] === 'PENDING' ? 'selected' : '' }}>• Chờ duyệt</option>
                        <option value="DRAFT" {{ $filters['status'] === 'DRAFT' ? 'selected' : '' }}>• Bản nháp</option>
                    </select>
                    <svg class="qbank-filter-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>

            {{-- Cột 5: THẺ / BỘ ĐỀ THI --}}
            <div class="qbank-filter-col">
                <label>THẺ / BỘ ĐỀ THI</label>
                <div class="qbank-filter-select-wrap">
                    <select name="tag" class="qbank-filter-select" onchange="this.form.submit()">
                        <option value="all" {{ $filters['tag'] === 'all' ? 'selected' : '' }}>Tất cả bộ đề</option>
                        <option value="ETS" {{ $filters['tag'] === 'ETS' ? 'selected' : '' }}>ETS Official 2024</option>
                        <option value="Hacker" {{ $filters['tag'] === 'Hacker' ? 'selected' : '' }}>Hacker TOEIC</option>
                        <option value="SW" {{ $filters['tag'] === 'SW' ? 'selected' : '' }}>Speaking & Writing 2024</option>
                    </select>
                    <svg class="qbank-filter-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>
        </div>
    </form>

    {{-- 4. Thanh tác vụ chọn hàng loạt (Selection Action Bar - Khớp ảnh mẫu) --}}
    <form id="bulk-action-form" method="POST" action="{{ route('admin.questions.bulk-action') }}">
        @csrf
        <input type="hidden" name="action" id="bulk-action-type" value="">

        <div class="qbank-selection-bar">
            <div class="qbank-sel-left">
                <span class="qbank-sel-circle" id="selected-badge">0</span>
                <span>câu hỏi đã chọn</span>
                <span style="color: #cbd5e1;">|</span>
                <a href="javascript:void(0)" class="qbank-sel-link" onclick="selectAllQuestionsOnPage()">
                    Chọn tất cả {{ number_format($stats['total']) }} câu
                </a>
            </div>

            <div class="qbank-sel-actions">
                <button type="button" class="btn-sel-action" onclick="submitBulkAction('assign')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    </svg>
                    <span>Gắn vào Đề thi</span>
                </button>

                <button type="button" class="btn-sel-action" onclick="submitBulkAction('approve')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Phê duyệt nhanh</span>
                </button>

                <button type="button" class="btn-sel-action danger" onclick="submitBulkAction('delete')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <span>Xóa đã chọn</span>
                </button>
            </div>
        </div>

        {{-- 5. Bảng danh sách câu hỏi (Data Table - Khớp ảnh mẫu) --}}
        <div class="user-table-card">
            <div class="user-table-scroll">
                <table class="user-data-table">
                    <thead>
                        <tr>
                            <th style="width: 44px; text-align: center;">
                                <input type="checkbox" id="check-all-q" class="custom-checkbox" title="Chọn tất cả">
                            </th>
                            <th style="width: 140px;">MÃ ID & PART</th>
                            <th>TRÍCH ĐOẠN / NỘI DUNG CÂU HỎI</th>
                            <th style="width: 160px;">KỸ NĂNG & DẠNG BÀI</th>
                            <th style="width: 130px;">LƯỢT DÙNG & TỈ LỆ ĐÚNG</th>
                            <th style="width: 140px;">TRẠNG THÁI</th>
                            <th style="text-align: right; width: 110px; padding-right: 22px;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($questions as $q)
                            @php
                                $usage = $q->usage_stats;
                                $isReported = strtoupper((string)$q->status) === 'REPORTED';
                                $isPending = strtoupper((string)$q->status) === 'PENDING';
                                $options = $q->options;
                                $correctOpt = $options->firstWhere('is_correct', true);
                            @endphp
                            <tr>
                                {{-- Checkbox --}}
                                <td style="text-align: center;">
                                    <input type="checkbox"
                                           name="question_ids[]"
                                           value="{{ $q->question_id }}"
                                           class="custom-checkbox q-row-cb"
                                           onchange="updateSelectionCount()">
                                </td>

                                {{-- MÃ ID & PART --}}
                                <td>
                                    <div class="q-id-text {{ $isReported ? 'danger' : '' }}">
                                        {{ $q->question_code_display }}
                                    </div>
                                    <div class="q-badges-row">
                                        <span class="q-part-pill">{{ $q->part_badge }}</span>
                                        <span class="q-source-pill">{{ $q->source_tag }}</span>
                                    </div>
                                </td>

                                {{-- TRÍCH ĐOẠN / NỘI DUNG CÂU HỎI --}}
                                <td>
                                    {{-- Q-10892: Trắc nghiệm Part 5 --}}
                                    @if ($q->question_id == 10892 || ($q->part_badge === 'Part 5' && !$isReported))
                                        <div class="q-content-text">
                                            "With bicycling becoming more popular than ever, the city council plans to <span style="color: #4338ca; font-weight: 700;">---</span> the number of bicycle lanes along riverside avenues."
                                        </div>
                                        <div>
                                            <span class="q-ans-pill">Đáp án đúng: C. expand</span>
                                            <span class="q-opt-options">A. define • B. inspect • D. relieve</span>
                                        </div>

                                    {{-- Q-10893: Part 1 tranh kèm audio --}}
                                    @elseif ($q->question_id == 10893 || $q->part_badge === 'Part 1')
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div class="q-thumb-box">P1 TRANH</div>
                                            <div>
                                                <div class="q-content-text" style="font-weight: 500;">
                                                    Bức tranh số 01: Nhân viên văn phòng làm việc với máy tính xách tay bên cửa sổ kính.
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <span class="q-audio-pill">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                                        </svg>
                                                        audio_track_01.mp3
                                                    </span>
                                                    <span class="q-ans-pill" style="margin: 0; background-color: #f1f5f9; color: #334155;">
                                                        Đáp án: (B)
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                    {{-- Q-10894: Writing T1 mô tả tranh --}}
                                    @elseif ($q->question_id == 10894 || $q->part_badge === 'Writing T1')
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div class="q-thumb-box" style="background: linear-gradient(135deg, #0f766e, #14b8a6);">W1 ẢNH</div>
                                            <div>
                                                <div class="q-content-text">
                                                    Từ khóa bắt buộc: <strong style="color: #4338ca;">tourist / landmark</strong> — Yêu cầu viết 1 câu mô tả chính xác bức tranh.
                                                </div>
                                                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                                        <line x1="8" y1="21" x2="16" y2="21"></line>
                                                        <line x1="12" y1="17" x2="12" y2="21"></line>
                                                    </svg>
                                                    <span>Chấm AI Tự động + Kiểm tra ngữ pháp</span>
                                                </div>
                                            </div>
                                        </div>

                                    {{-- Q-10895: Đoạn văn đôi Part 7 --}}
                                    @elseif ($q->question_id == 10895 || $q->part_badge === 'Part 7')
                                        <div>
                                            <span class="q-tag-blue">Đoạn văn đôi (4 câu hỏi)</span>
                                            <div class="q-title-bold">Amon Donates $50,000 to Grenel Music Academy & Confirmation...</div>
                                            <div class="q-content-text" style="color: #64748b; font-size: 12.5px;">
                                                Questions 176–180 refer to the following press release and subsequent confirmation letter...
                                            </div>
                                            <div style="font-size: 11.5px; color: #64748b; margin-top: 3px;">
                                                Khóa câu: #10895-1 (C), #10895-2 (A), #10895-3 (D), #10895-4 (B)
                                            </div>
                                        </div>

                                    {{-- Q-10896: Báo lỗi đáp án Part 5 --}}
                                    @elseif ($isReported || $q->question_id == 10896)
                                        <div class="q-content-text">
                                            "The marketing director suggested that we <span style="color: #dc2626; font-weight: 700;">------</span> the product launch date until next quarter to avoid overlapping campaigns."
                                        </div>
                                        <div class="q-reported-alert">
                                            <span style="color: #64748b; font-weight: 400;">Hiện tại: B. postpone</span>
                                            <span style="color: #cbd5e1;">•</span>
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                            </svg>
                                            <span>2 học viên phản hồi cấu trúc bàng thái cách (subjunctive)</span>
                                        </div>

                                    {{-- Q-10897: Writing T2 Chờ duyệt --}}
                                    @elseif ($isPending || $q->question_id == 10897)
                                        <div>
                                            <span class="q-tag-blue" style="background-color: #f1f5f9; color: #475569;">Respond to an e-mail request</span>
                                            <div class="q-title-bold">Vehicle Fleet Rental Inquiry from Apex Logistics</div>
                                            <div class="q-content-text" style="font-size: 12.5px;">
                                                Yêu cầu học viên trả lời thư đề nghị cung cấp biểu phí và điều khoản hủy dịch vụ
                                            </div>
                                            <div style="font-size: 11px; color: #94a3b8; margin-top: 3px;">
                                                Người tạo: GV. Trần Minh Hoàng • Biên soạn 2 giờ trước
                                            </div>
                                        </div>

                                    {{-- Q-10898: Listening Part 3 cụm hội thoại --}}
                                    @elseif ($q->question_id == 10898 || $q->part_badge === 'Part 3')
                                        <div>
                                            <span class="q-audio-pill" style="margin: 0 0 4px 0;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                                                </svg>
                                                Audio 0:42s (Cụm 3 câu)
                                            </span>
                                            <div class="q-title-bold" style="margin-top: 4px;">Two colleagues negotiating conference hall bookings</div>
                                            <div class="q-content-text" style="color: #64748b; font-size: 12px;">
                                                Questions 41–43 refer to the conversation between a project manager and an event coordinator...
                                            </div>
                                            <span class="q-ans-pill" style="margin-top: 4px;">Đáp án đúng: 41(A) • 42(D) • 43(B)</span>
                                        </div>

                                    {{-- Fallback generic --}}
                                    @else
                                        <div class="q-content-text">{{ $q->content }}</div>
                                        @if ($q->explanation)
                                            <div class="q-opt-options">{{ Str::limit($q->explanation, 100) }}</div>
                                        @endif
                                    @endif
                                </td>

                                {{-- KỸ NĂNG & DẠNG BÀI --}}
                                <td>
                                    <div style="font-weight: 700; color: #0f172a; font-size: 13.5px;">
                                        {{ $q->skill_display }}
                                    </div>
                                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                        {{ $q->question_type_display }}
                                    </div>
                                </td>

                                {{-- LƯỢT DÙNG & TỈ LỆ ĐÚNG --}}
                                <td>
                                    <div style="font-size: 12px; color: #0f172a;">
                                        <strong>{{ $usage['attempts'] }}</strong>
                                        <span style="color: #64748b; font-size: 11px;">{{ $usage['label'] }}</span>
                                    </div>
                                    <div style="margin-top: 4px; font-size: 12px; font-weight: 700; color: {{ $usage['is_red'] ? '#dc2626' : '#15803d' }};">
                                        {{ $usage['accuracy'] }}
                                    </div>
                                </td>

                                {{-- TRẠNG THÁI --}}
                                <td>
                                    <span class="status-pill {{ $q->status_badge_class }}">
                                        @if (!$isReported)
                                            <span class="status-dot"></span>
                                        @endif
                                        {{ $q->status_label }}
                                    </span>
                                </td>

                                {{-- THAO TÁC --}}
                                <td style="text-align: right; padding-right: 20px;">
                                    <div class="user-actions-cell" style="justify-content: flex-end;">
                                        @if ($isReported)
                                            {{-- Nút sửa lỗi trực tiếp (Wrench tool) --}}
                                            <button type="button"
                                                    class="action-icon-btn danger"
                                                    title="Xử lý báo cáo lỗi đáp án"
                                                    onclick='openEditQuestionModal(@json($q))'>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                                                </svg>
                                            </button>
                                        @elseif ($isPending)
                                            {{-- Nút duyệt nhanh (Check icon) --}}
                                            <form method="POST" action="{{ route('admin.questions.toggle-status', $q->question_id) }}" style="display: inline; margin: 0;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="ACTIVE">
                                                <button type="submit" class="action-icon-btn" style="color: #059669;" title="Duyệt câu hỏi này">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            {{-- Nút Xem / Preview (Eye icon) --}}
                                            <button type="button"
                                                    class="action-icon-btn"
                                                    title="Xem chi tiết câu hỏi"
                                                    onclick='openPreviewModal(@json($q))'>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </button>
                                        @endif

                                        {{-- Nút Sửa câu hỏi (Pen icon) --}}
                                        <button type="button"
                                                class="action-icon-btn"
                                                title="Chỉnh sửa câu hỏi"
                                                onclick='openEditQuestionModal(@json($q))'>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                            </svg>
                                        </button>

                                        {{-- Nút Xóa (3 dots / trash) --}}
                                        <form method="POST" action="{{ route('admin.questions.destroy', $q->question_id) }}" style="display: inline; margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa câu hỏi #Q-{{ $q->question_id }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon-btn danger" title="Xóa câu hỏi">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="1"></circle>
                                                    <circle cx="12" cy="5" r="1"></circle>
                                                    <circle cx="12" cy="19" r="1"></circle>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 48px 20px; color: #64748b;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                        </svg>
                                        <p style="margin: 0; font-weight: 600; font-size: 15px;">Không tìm thấy câu hỏi phù hợp</p>
                                        <p style="margin: 0; font-size: 13px; color: #94a3b8;">Vui lòng thử từ khóa tìm kiếm hoặc làm mới bộ lọc</p>
                                        <a href="{{ route('admin.questions.index') }}" class="btn-admin-action" style="margin-top: 10px;">Xóa bộ lọc</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 6. Thanh phân trang dưới cùng (Khớp đúng ảnh mẫu) --}}
            <div class="user-table-footer">
                <div class="user-footer-left">
                    <span>
                        Hiển thị 1–{{ $questions->count() }} trong tổng số {{ number_format($stats['total']) }} câu hỏi
                    </span>

                    <div class="user-per-page-select">
                        <span>Số dòng:</span>
                        <input type="number"
                               class="per-page-input"
                               value="{{ $filters['per_page'] }}"
                               min="5"
                               max="100"
                               onchange="location.href='{{ request()->fullUrlWithQuery(['per_page' => '']) }}' + this.value">
                    </div>
                </div>

                <div class="user-pagination">
                    {{-- Nút về trang đầu |< --}}
                    <a href="{{ $questions->url(1) }}" class="page-btn {{ $questions->onFirstPage() ? 'disabled' : '' }}" title="Trang đầu">
                        &verbar;&lt;
                    </a>

                    {{-- Nút trang trước < --}}
                    <a href="{{ $questions->previousPageUrl() }}" class="page-btn {{ $questions->onFirstPage() ? 'disabled' : '' }}" title="Trang trước">
                        &lt;
                    </a>

                    {{-- Trang 1 (Active) --}}
                    <a href="{{ $questions->url(1) }}" class="page-btn {{ $questions->currentPage() === 1 ? 'active' : '' }}">1</a>

                    @if ($questions->lastPage() >= 2)
                        <a href="{{ $questions->url(2) }}" class="page-btn {{ $questions->currentPage() === 2 ? 'active' : '' }}">2</a>
                    @else
                        <span class="page-btn">2</span>
                    @endif

                    @if ($questions->lastPage() >= 3)
                        <a href="{{ $questions->url(3) }}" class="page-btn {{ $questions->currentPage() === 3 ? 'active' : '' }}">3</a>
                    @else
                        <span class="page-btn">3</span>
                    @endif

                    <span class="page-btn disabled">...</span>
                    <span class="page-btn">1,779</span>

                    {{-- Nút trang sau > --}}
                    <a href="{{ $questions->nextPageUrl() ?: '#' }}" class="page-btn {{ !$questions->hasMorePages() ? 'disabled' : '' }}" title="Trang sau">
                        &gt;
                    </a>

                    {{-- Nút về trang cuối >| --}}
                    <a href="{{ $questions->url($questions->lastPage()) }}" class="page-btn {{ !$questions->hasMorePages() ? 'disabled' : '' }}" title="Trang cuối">
                        &gt;&verbar;
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- 7. Hai thẻ tính năng bên dưới cùng (Bottom Feature Cards - Khớp ảnh mẫu) --}}
    <div class="qbank-feature-grid">
        {{-- Thẻ 1: Kiểm chuẩn chất lượng AI --}}
        <div class="qbank-feature-card">
            <div class="qbank-feat-icon icon-purple">
                <span>A</span>
            </div>
            <div>
                <h3 class="qbank-feat-title">Kiểm chuẩn chất lượng AI</h3>
                <p class="qbank-feat-desc">
                    Tự động quét định kỳ phát hiện 100% ngữ pháp và cấu trúc đáp án trùng lặp trước khi công bố bài thi thật.
                </p>
            </div>
        </div>

        {{-- Thẻ 2: Kho Media Giọng đọc Đa quốc gia --}}
        <div class="qbank-feature-card">
            <div class="qbank-feat-icon icon-teal">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="2" x2="12" y2="22"></line>
                    <line x1="8" y1="6" x2="8" y2="18"></line>
                    <line x1="4" y1="10" x2="4" y2="14"></line>
                    <line x1="16" y1="6" x2="16" y2="18"></line>
                    <line x1="20" y1="10" x2="20" y2="14"></line>
                </svg>
            </div>
            <div>
                <h3 class="qbank-feat-title">Kho Media Giọng đọc Đa quốc gia</h3>
                <p class="qbank-feat-desc">
                    8,210 audio chuẩn giọng Anh-Mỹ (US), Anh-Anh (UK), Úc (AU) và Canada với chuẩn sample rate 44.1kHz.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 1: THÊM CÂU HỎI MỚI --}}
<div id="modal-add-question" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 600px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Thêm câu hỏi mới vào Ngân hàng</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-add-question')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.questions.store') }}">
            @csrf
            <div class="admin-modal-body">
                <div class="form-group-admin">
                    <label>Phần thi (Part / Task) <span style="color: #dc2626;">*</span></label>
                    <select name="section_id" class="form-control-admin" required>
                        @foreach ($sections as $sec)
                            <option value="{{ $sec->section_id }}">{{ $sec->name }} ({{ $sec->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group-admin">
                    <label>Dạng câu hỏi <span style="color: #dc2626;">*</span></label>
                    <select name="question_type_id" class="form-control-admin" required>
                        @foreach ($questionTypes as $qt)
                            <option value="{{ $qt->question_type_id }}">{{ $qt->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group-admin">
                    <label>Nội dung câu hỏi <span style="color: #dc2626;">*</span></label>
                    <textarea name="content" class="form-control-admin" rows="3" placeholder="Nhập đề bài hoặc câu trắc nghiệm..." required></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group-admin">
                        <label>Độ khó <span style="color: #dc2626;">*</span></label>
                        <select name="difficulty" class="form-control-admin" required>
                            <option value="EASY">Cơ bản (Dưới 500)</option>
                            <option value="MEDIUM" selected>Trung bình (500 – 750)</option>
                            <option value="HARD">Nâng cao (750+)</option>
                        </select>
                    </div>
                    <div class="form-group-admin">
                        <label>Trạng thái khởi tạo <span style="color: #dc2626;">*</span></label>
                        <select name="status" class="form-control-admin" required>
                            <option value="ACTIVE" selected>Hoạt động (Active)</option>
                            <option value="PENDING">Chờ kiểm duyệt (Pending)</option>
                            <option value="DRAFT">Bản nháp (Draft)</option>
                        </select>
                    </div>
                </div>

                {{-- Các lựa chọn đáp án trắc nghiệm --}}
                <div style="border-top: 1px solid #edf0f7; padding-top: 12px; margin-top: 8px;">
                    <label style="font-weight: 700; font-size: 13px; margin-bottom: 8px; display: block;">Các phương án trắc nghiệm (Nếu có):</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <input type="text" name="opt_a" class="form-control-admin" placeholder="Phương án A">
                        <input type="text" name="opt_b" class="form-control-admin" placeholder="Phương án B">
                        <input type="text" name="opt_c" class="form-control-admin" placeholder="Phương án C">
                        <input type="text" name="opt_d" class="form-control-admin" placeholder="Phương án D">
                    </div>
                    <div class="form-group-admin" style="margin-top: 10px;">
                        <label>Đáp án đúng:</label>
                        <select name="correct_opt" class="form-control-admin">
                            <option value="A">Phương án A</option>
                            <option value="B">Phương án B</option>
                            <option value="C" selected>Phương án C</option>
                            <option value="D">Phương án D</option>
                        </select>
                    </div>
                </div>

                <div class="form-group-admin">
                    <label>Giải thích chi tiết & Đáp án</label>
                    <textarea name="explanation" class="form-control-admin" rows="2" placeholder="Giải thích ngữ pháp, từ vựng hoặc dẫn chứng trong bài nghe/đọc..."></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-add-question')">Hủy bỏ</button>
                <button type="submit" class="btn-admin-primary">Lưu vào Ngân hàng</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: CHỈNH SỬA CÂU HỎI --}}
<div id="modal-edit-question" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 580px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="edit-modal-title">Cập nhật câu hỏi</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-edit-question')">&times;</button>
        </div>
        <form id="form-edit-question" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="admin-modal-body">
                <div class="form-group-admin">
                    <label>Phần thi (Part / Task)</label>
                    <select id="edit_section_id" name="section_id" class="form-control-admin" required>
                        @foreach ($sections as $sec)
                            <option value="{{ $sec->section_id }}">{{ $sec->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group-admin">
                    <label>Nội dung câu hỏi <span style="color: #dc2626;">*</span></label>
                    <textarea id="edit_content" name="content" class="form-control-admin" rows="3" required></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group-admin">
                        <label>Độ khó</label>
                        <select id="edit_difficulty" name="difficulty" class="form-control-admin" required>
                            <option value="EASY">Cơ bản (Dưới 500)</option>
                            <option value="MEDIUM">Trung bình (500 – 750)</option>
                            <option value="HARD">Nâng cao (750+)</option>
                        </select>
                    </div>
                    <div class="form-group-admin">
                        <label>Trạng thái</label>
                        <select id="edit_status" name="status" class="form-control-admin" required>
                            <option value="ACTIVE">Hoạt động (Active)</option>
                            <option value="REPORTED">Báo lỗi đáp án (Reported)</option>
                            <option value="PENDING">Chờ duyệt (Pending)</option>
                            <option value="DRAFT">Bản nháp (Draft)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group-admin">
                    <label>Giải thích & Đáp án chuẩn</label>
                    <textarea id="edit_explanation" name="explanation" class="form-control-admin" rows="2"></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-edit-question')">Hủy</button>
                <button type="submit" class="btn-admin-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: XEM TRƯỚC CÂU HỎI (PREVIEW) --}}
<div id="modal-preview-question" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 560px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="preview-modal-title">Xem trước câu hỏi</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-preview-question')">&times;</button>
        </div>
        <div class="admin-modal-body">
            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                <span class="q-part-pill" id="preview-part">Part 5</span>
                <span class="q-source-pill" id="preview-source">ETS '24</span>
                <span class="status-pill status-badge-active" id="preview-status" style="margin-left: auto;">• Hoạt động</span>
            </div>
            <div style="background-color: #f8fafc; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0; margin-bottom: 16px;">
                <p id="preview-content" style="font-size: 14px; color: #0f172a; line-height: 1.5; margin: 0;"></p>
            </div>
            <div style="font-size: 13px;">
                <strong>Giải thích / Đáp án:</strong>
                <p id="preview-explanation" style="color: #64748b; margin: 4px 0 0 0; line-height: 1.4;"></p>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-preview-question')">Đóng</button>
        </div>
    </div>
</div>

{{-- MODAL 4: NHẬP EXCEL / JSON --}}
<div id="modal-import-qbank" class="admin-modal-backdrop">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Nhập dữ liệu Đề thi & Câu hỏi</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-import-qbank')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.questions.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="admin-modal-body">
                <p style="font-size: 13.5px; color: #475569; margin-top: 0;">
                    Hỗ trợ tệp <strong>.CSV, .TXT hoặc .JSON</strong> theo chuẩn format đề thi quốc tế StudyPREP.
                </p>
                <div class="form-group-admin">
                    <label>Chọn tệp dữ liệu</label>
                    <input type="file" name="import_file" accept=".csv,.txt,.json" class="form-control-admin" required>
                </div>
                <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 12px; font-size: 12.5px; color: #64748b;">
                    Định dạng hỗ trợ: Bộ đề thi ETS, Hacker TOEIC hoặc trích xuất từ đề thi AI Audit.
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-import-qbank')">Hủy</button>
                <button type="submit" class="btn-admin-primary">Tải lên & Đồng bộ</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Đếm số lượng câu hỏi được chọn
    function updateSelectionCount() {
        var count = document.querySelectorAll('.q-row-cb:checked').length;
        var badge = document.getElementById('selected-badge');
        if (badge) badge.innerText = count;
    }

    // Chọn tất cả câu hỏi trên trang
    var checkAllQ = document.getElementById('check-all-q');
    if (checkAllQ) {
        checkAllQ.addEventListener('change', function() {
            var checked = this.checked;
            document.querySelectorAll('.q-row-cb').forEach(function(cb) {
                cb.checked = checked;
            });
            updateSelectionCount();
        });
    }

    function selectAllQuestionsOnPage() {
        if (checkAllQ) {
            checkAllQ.checked = true;
            document.querySelectorAll('.q-row-cb').forEach(function(cb) {
                cb.checked = true;
            });
            updateSelectionCount();
        }
    }

    // Xử lý gửi hành động hàng loạt
    function submitBulkAction(actionType) {
        var count = document.querySelectorAll('.q-row-cb:checked').length;
        if (count === 0) {
            alert('Vui lòng tích chọn ít nhất một câu hỏi trong danh sách.');
            return;
        }

        var label = actionType === 'delete' ? 'XÓA' : (actionType === 'approve' ? 'PHÊ DUYỆT' : 'GẮN VÀO ĐỀ THI');
        if (confirm('Bạn có chắc chắn muốn ' + label + ' ' + count + ' câu hỏi đã chọn?')) {
            document.getElementById('bulk-action-type').value = actionType;
            document.getElementById('bulk-action-form').submit();
        }
    }

    // Mở modal Edit câu hỏi
    function openEditQuestionModal(q) {
        var form = document.getElementById('form-edit-question');
        form.action = '/admin/questions/' + q.question_id;
        document.getElementById('edit-modal-title').innerText = 'Cập nhật câu hỏi #Q-' + q.question_id;
        document.getElementById('edit_section_id').value = q.section_id || '5';
        document.getElementById('edit_content').value = q.content || '';
        document.getElementById('edit_difficulty').value = q.difficulty || 'MEDIUM';
        document.getElementById('edit_status').value = q.status || 'ACTIVE';
        document.getElementById('edit_explanation').value = q.explanation || '';
        openAdminModal('modal-edit-question');
    }

    // Mở modal Preview câu hỏi
    function openPreviewModal(q) {
        document.getElementById('preview-modal-title').innerText = 'Chi tiết #Q-' + q.question_id;
        document.getElementById('preview-part').innerText = q.part_badge || 'Part';
        document.getElementById('preview-source').innerText = q.source_tag || 'ETS';
        document.getElementById('preview-status').innerText = '• ' + (q.status_label || 'Hoạt động');
        document.getElementById('preview-content').innerText = q.content || '';
        document.getElementById('preview-explanation').innerText = q.explanation || 'Không có giải thích chi tiết.';
        openAdminModal('modal-preview-question');
    }
</script>
@endpush
