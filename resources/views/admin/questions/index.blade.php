@extends('layouts.admin', ['active' => 'ngan-hang-cau-hoi'])

@section('title', 'Ngân hàng câu hỏi TOEIC')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <span>Quản lý đào tạo</span>
    <span class="crumb-arrow">></span>
    <span>Ngân hàng câu hỏi</span>
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
                <h1 class="user-page-title">Ngân hàng câu hỏi TOEIC</h1>
            </div>
            <p class="user-page-subtitle" style="margin: 4px 0 0 0; color: #64748b; font-size: 13.5px;">
                Kho Đề thi &amp; Câu hỏi Chuẩn hóa TOEIC (Listening, Reading, Speaking &amp; Writing)
            </p>
        </div>

        <div class="user-header-actions" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 8px;">
            {{-- Nhóm hành động phụ: Tải mẫu & Xuất Excel --}}
            <div style="display: inline-flex; align-items: center; gap: 6px; padding-right: 8px; border-right: 1px solid #e2e8f0;">
                <a href="{{ route('admin.questions.sample-docx') }}" class="btn-admin-action" style="padding: 8px 11px; font-size: 13px;" title="Tải file Word (.docx) mẫu chuẩn cấu trúc nhập câu hỏi">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <path d="M12 18v-6"></path>
                        <path d="M9 15l3 3 3-3"></path>
                    </svg>
                    <span>Word mẫu</span>
                </a>

                <a href="{{ route('admin.questions.export') }}" class="btn-admin-action excel" style="padding: 8px 11px; font-size: 13px;" title="Xuất danh sách câu hỏi ra Excel (CSV)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Xuất Excel</span>
                </a>
            </div>

            {{-- Nhóm hành động chính: Import, Tạo nhóm, Thêm mới --}}
            <div style="display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <a href="{{ route('admin.questions.bulk-import') }}" class="btn-admin-action import" style="padding: 8px 12px; font-size: 13px;" title="Thêm câu hỏi hàng loạt (Bulk Import Studio)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="18" x2="12" y2="12"></line>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                    <span>Import hàng loạt</span>
                </a>

                <a href="{{ route('admin.questions.group.create') }}" class="btn-admin-action" style="padding: 8px 12px; font-size: 13px; color: #4338ca; border-color: #c7d2fe; background: #f5f3ff; font-weight: 700;" title="Tạo nhóm câu hỏi TOEIC (Passage / Stimulus)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    <span>Tạo nhóm câu hỏi</span>
                </a>

                <a href="{{ route('admin.questions.create') }}" class="btn-admin-primary" style="padding: 8px 14px; font-size: 13px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    <span>Thêm câu hỏi mới</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Banner ngữ cảnh nếu đang chọn câu hỏi từ Ngân hàng để gắn vào Đề thi --}}
    @if (!empty($targetTest))
        <div style="background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%); border: 1.5px solid #c7d2fe; border-radius: 14px; padding: 14px 20px; margin-bottom: 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: #4338ca; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: #4338ca; text-transform: uppercase;">Chế độ gắn câu hỏi vào Đề thi</div>
                    <div style="font-size: 14.5px; font-weight: 700; color: #1e1b4b;">
                        Đề thi đích: <span style="color: #4f46e5;">{{ $targetTest->title }}</span> ({{ $targetTest->test_code }})
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" class="btn-admin-primary" onclick="submitBulkAction('assign')" style="padding: 8px 14px; font-size: 13px;">
                    <span>Gắn các câu đã tick vào {{ $targetTest->test_code }}</span>
                </button>
                <a href="{{ route('admin.tests.show', $targetTest->test_form_id) }}" class="btn-admin-action" style="padding: 8px 14px; font-size: 13px; text-decoration: none;">
                    &larr; Quay lại Đề thi
                </a>
            </div>
        </div>
    @endif

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
                <span class="qbank-kpi-sub">Part 1 - 7</span>
            </div>
            <div class="qbank-kpi-foot">
                <span>Đã chuẩn hóa thang điểm 990</span>
            </div>
        </div>

        {{-- Thẻ 3: TOEIC Speaking & Writing --}}
        <div class="qbank-kpi-card">
            <div class="qbank-kpi-top">
                <p class="qbank-kpi-title">TOEIC Speaking &amp; Writing</p>
                <div class="qbank-kpi-icon icon-pink">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
            </div>
            <div class="qbank-kpi-val-row">
                <span class="qbank-kpi-num">{{ number_format($stats['sw']) }}</span>
                <span class="qbank-kpi-sub">câu SW</span>
            </div>
            <div class="qbank-kpi-foot" style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                <span style="font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                    Speaking: {{ number_format($stats['speaking'] ?? 0) }}
                </span>
                <span style="font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe;">
                    Writing: {{ number_format($stats['writing'] ?? 0) }}
                </span>
            </div>
        </div>

        {{-- Thẻ 4: Cần kiểm duyệt & Báo lỗi --}}
        <div class="qbank-kpi-card">
            <div class="qbank-kpi-top">
                <p class="qbank-kpi-title">Cần kiểm duyệt &amp; Báo lỗi</p>
                <div class="qbank-kpi-icon icon-red">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>
            <div class="qbank-kpi-val-row">
                <span class="qbank-kpi-num" style="color: {{ ($stats['reported'] + ($stats['pending'] ?? 0)) > 0 ? '#dc2626' : '#0f172a' }};">
                    {{ number_format($stats['reported'] + ($stats['pending'] ?? 0)) }}
                </span>
                <span class="qbank-kpi-sub">câu hỏi</span>
            </div>
            <div class="qbank-kpi-foot">
                @if ($stats['reported'] === 0 && ($stats['pending'] ?? 0) === 0)
                    <span style="color: #047857; font-weight: 600;">Không có câu hỏi báo lỗi</span>
                @else
                    <span class="qbank-red-pill">{{ number_format($stats['reported']) }} báo lỗi</span>
                    <span>&bull; {{ number_format($stats['pending'] ?? 0) }} chờ duyệt</span>
                @endif
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
        </div>

        {{-- Hàng 2: 5 Dropdowns lọc theo tiêu chuẩn --}}
        <div class="qbank-filters-grid">
            {{-- Cột 1: KỸ NĂNG --}}
            <div class="qbank-filter-col">
                <label>KỸ NĂNG</label>
                <div class="qbank-filter-select-wrap">
                    <select name="skill" class="qbank-filter-select" onchange="this.form.submit()">
                        <option value="all" {{ $filters['skill'] === 'all' ? 'selected' : '' }}>Tất cả kỹ năng (Listening / Reading / Speaking / Writing)</option>
                        <option value="LC" {{ $filters['skill'] === 'LC' ? 'selected' : '' }}>Listening</option>
                        <option value="RC" {{ $filters['skill'] === 'RC' ? 'selected' : '' }}>Reading</option>
                        <option value="SPK" {{ $filters['skill'] === 'SPK' ? 'selected' : '' }}>Speaking</option>
                        <option value="WRT" {{ $filters['skill'] === 'WRT' ? 'selected' : '' }}>Writing</option>
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
                        <optgroup label="Listening (Part 1–4)">
                            <option value="PART1" {{ $filters['part'] === 'PART1' ? 'selected' : '' }}>Part 1: Photographs (Mô tả tranh)</option>
                            <option value="PART2" {{ $filters['part'] === 'PART2' ? 'selected' : '' }}>Part 2: Question-Response (Hỏi - Đáp)</option>
                            <option value="PART3" {{ $filters['part'] === 'PART3' ? 'selected' : '' }}>Part 3: Conversations (Hội thoại ngắn)</option>
                            <option value="PART4" {{ $filters['part'] === 'PART4' ? 'selected' : '' }}>Part 4: Short Talks (Bài nói ngắn)</option>
                        </optgroup>
                        <optgroup label="Reading (Part 5–7)">
                            <option value="PART5" {{ $filters['part'] === 'PART5' ? 'selected' : '' }}>Part 5: Incomplete Sentences (Hoàn thành câu)</option>
                            <option value="PART6" {{ $filters['part'] === 'PART6' ? 'selected' : '' }}>Part 6: Text Completion (Hoàn thành đoạn văn)</option>
                            <option value="PART7" {{ $filters['part'] === 'PART7' ? 'selected' : '' }}>Part 7: Reading Comprehension (Đọc hiểu)</option>
                        </optgroup>
                        <optgroup label="Speaking (5 phần chuẩn ETS)">
                            <option value="SPK_T1" {{ $filters['part'] === 'SPK_T1' ? 'selected' : '' }}>Speaking Part 1: Read a Text Aloud</option>
                            <option value="SPK_T2" {{ $filters['part'] === 'SPK_T2' ? 'selected' : '' }}>Speaking Part 2: Describe a Picture</option>
                            <option value="SPK_T3" {{ $filters['part'] === 'SPK_T3' ? 'selected' : '' }}>Speaking Part 3: Respond to Questions</option>
                            <option value="SPK_T4" {{ $filters['part'] === 'SPK_T4' ? 'selected' : '' }}>Speaking Part 4: Respond Using Information</option>
                            <option value="SPK_T5" {{ $filters['part'] === 'SPK_T5' ? 'selected' : '' }}>Speaking Part 5: Express an Opinion</option>
                        </optgroup>
                        <optgroup label="Writing (3 phần chuẩn ETS)">
                            <option value="WRITING_T1" {{ $filters['part'] === 'WRITING_T1' ? 'selected' : '' }}>Writing Part 1: Write a Sentence</option>
                            <option value="WRITING_T2" {{ $filters['part'] === 'WRITING_T2' ? 'selected' : '' }}>Writing Part 2: Respond to a Written Request</option>
                            <option value="WRITING_T3" {{ $filters['part'] === 'WRITING_T3' ? 'selected' : '' }}>Writing Part 3: Write an Opinion Essay</option>
                        </optgroup>
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
                        <option value="UNSET" {{ $filters['difficulty'] === 'UNSET' ? 'selected' : '' }}>Chưa phân loại</option>
                        <option value="EASY" {{ $filters['difficulty'] === 'EASY' ? 'selected' : '' }}>Cơ bản (Dưới 500)</option>
                        <option value="MEDIUM" {{ $filters['difficulty'] === 'MEDIUM' ? 'selected' : '' }}>Trung bình (500 - 750)</option>
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
                        <option value="ACTIVE" {{ $filters['status'] === 'ACTIVE' ? 'selected' : '' }}>Hoạt động (Đã duyệt)</option>
                        <option value="DRAFT" {{ $filters['status'] === 'DRAFT' ? 'selected' : '' }}>Bản nháp</option>
                        <option value="PENDING" {{ $filters['status'] === 'PENDING' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="REPORTED" {{ $filters['status'] === 'REPORTED' ? 'selected' : '' }}>Báo lỗi đáp án</option>
                        <option value="ARCHIVED" {{ $filters['status'] === 'ARCHIVED' ? 'selected' : '' }}>Đã lưu trữ</option>
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
                        @if (isset($availableTests) && $availableTests->isNotEmpty())
                            @foreach ($availableTests as $tItem)
                                <option value="{{ $tItem->test_form_id }}" {{ (string) $filters['tag'] === (string) $tItem->test_form_id ? 'selected' : '' }}>
                                    {{ $tItem->test_code }} - {{ \Illuminate\Support\Str::limit($tItem->title, 28) }}
                                </option>
                            @endforeach
                        @endif
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

    {{-- 4. Thanh tác vụ chọn hàng loạt (Selection Action Bar - Hỗ trợ chọn toàn bộ danh sách không giới hạn trang) --}}
    @php
        $filteredTotalCount = (int) $questions->total();
        $currentPageCount = (int) $questions->count();
    @endphp
    <form id="bulk-action-form" method="POST" action="{{ route('admin.questions.bulk-action') }}">
        @csrf
        <input type="hidden" name="action" id="bulk-action-type" value="">
        <input type="hidden" name="select_all" id="bulk-select-all" value="0">
        <input type="hidden" name="filter_search" value="{{ $filters['search'] }}">
        <input type="hidden" name="filter_skill" value="{{ $filters['skill'] }}">
        <input type="hidden" name="filter_part" value="{{ $filters['part'] }}">
        <input type="hidden" name="filter_difficulty" value="{{ $filters['difficulty'] }}">
        <input type="hidden" name="filter_status" value="{{ $filters['status'] }}">
        <input type="hidden" name="filter_tag" value="{{ $filters['tag'] }}">
        <input type="hidden" name="test_id" id="bulk-assign-test-id" value="{{ $targetTest?->test_form_id ?? '' }}">
        <input type="hidden" name="redirect_to_test" id="bulk-redirect-to-test" value="{{ !empty($targetTest) ? '1' : '0' }}">
        <input type="hidden" name="auto_activate_drafts" id="bulk-auto-activate-drafts" value="1">

        <div class="qbank-selection-bar">
            <div class="qbank-sel-left" style="flex-wrap: wrap;">
                <span class="qbank-sel-circle" id="selected-badge">0</span>
                <span id="selected-label">câu hỏi đã chọn</span>
                <span style="color: #cbd5e1;">|</span>
                <a href="javascript:void(0)" class="qbank-sel-link" id="select-all-matching-link" onclick="selectAllMatchingQuestions()">
                    Chọn tất cả {{ number_format($filteredTotalCount) }} câu
                </a>
                @if ($filteredTotalCount > $currentPageCount)
                    <a href="javascript:void(0)" class="qbank-sel-link" id="select-page-only-link" onclick="selectCurrentPageOnly()" style="display: none; color: #475569;">
                        Chỉ chọn trang này ({{ $currentPageCount }} câu)
                    </a>
                @endif
                <a href="javascript:void(0)" class="qbank-sel-link" id="clear-selection-link" onclick="clearAllSelectedQuestions()" style="display: none; color: #dc2626;">
                    Bỏ chọn tất cả
                </a>
            </div>

            <div class="qbank-sel-actions">
                <button type="button" class="btn-sel-action" onclick="startBatchGenerateTts()" style="color: #0369a1; border-color: #bae6fd; background: #f0f9ff;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                    </svg>
                    <span>Sinh Audio TTS</span>
                </button>

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
                            <th class="admin-actions-heading" style="width: 110px;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($questions as $q)
                            @php
                                $usage = $q->usage_stats;
                                $isReported = strtoupper((string)$q->status) === 'REPORTED';
                                $isPending = strtoupper((string)$q->status) === 'PENDING';
                                $options = $q->options;
                                $isConstructive = in_array($q->skill_key, ['speaking', 'writing'], true);
                                $groupCount = $q->group_questions_count;
                                $hasImageMedia = $q->questionSet?->resolved_media_url && (strtolower((string) $q->questionSet->media_type) === 'image' || preg_match('/\.(png|jpe?g|gif|webp|bmp)$/i', $q->questionSet->resolved_media_url));
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
                                    <div class="q-badges-row" style="flex-wrap: wrap; gap: 4px;">
                                        <span class="q-part-pill">{{ $q->part_badge }}</span>
                                        @if ($groupCount > 1)
                                            <span style="display: inline-flex; align-items: center; padding: 2px 7px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">
                                                Cụm {{ $groupCount }} câu
                                            </span>
                                        @endif
                                        <span class="q-source-pill">{{ $q->source_tag }}</span>
                                    </div>
                                </td>

                                {{-- TRÍCH ĐOẠN / NỘI DUNG CÂU HỎI --}}
                                <td>
                                    <div class="q-content-text">{{ $q->content }}</div>

                                    @if ($q->questionSet?->resolved_media_url && $hasImageMedia)
                                        <img src="{{ $q->questionSet->resolved_media_url }}" alt="Question media" style="max-width: 180px; max-height: 100px; margin-top: 8px; border-radius: 8px; border: 1px solid #e2e8f0; object-fit: contain; display: block;">
                                    @endif

                                    @if ($q->questionSet?->resolved_audio_url)
                                        <audio controls preload="none" src="{{ $q->questionSet->resolved_audio_url }}" style="max-width: 260px; height: 32px; margin-top: 8px; display: block;"></audio>
                                    @elseif ($q->skill_key === 'listening')
                                        <div style="margin-top: 6px; display: flex; align-items: center; gap: 6px;">
                                            <button type="button" class="btn-admin-action" style="padding: 3px 8px; font-size: 11px; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd;" onclick="event.stopPropagation(); window.speakWord({{ \Illuminate\Support\Js::from($q->questionSet?->transcript ?: $q->content) }}, this)" title="Nghe đọc transcript tự động bằng AI/TTS">
                                                🔊 Nghe thử (TTS)
                                            </button>
                                        </div>
                                    @endif

                                    @if ($isConstructive)
                                        <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px;">
                                            <span style="font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px; background: {{ $q->skill_key === 'speaking' ? '#fef3c7' : '#ede9fe' }}; color: {{ $q->skill_key === 'speaking' ? '#b45309' : '#6d28d9' }};">
                                                {{ $q->skill_key === 'speaking' ? 'Speaking Task (Không trắc nghiệm)' : 'Writing Task (Tự luận)' }}
                                            </span>
                                            @if ($hasImageMedia)
                                                <span class="q-opt-options">Kèm tranh minh họa</span>
                                            @endif
                                            @if ($q->questionSet?->stimulus_text)
                                                <span class="q-opt-options">Ngữ liệu: {{ \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', $q->questionSet->stimulus_text), 75) }}</span>
                                            @endif
                                        </div>
                                    @elseif ($options->isNotEmpty())
                                        <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px;">
                                            @foreach ($options as $option)
                                                <span class="{{ $option->is_correct ? 'q-ans-pill' : 'q-opt-options' }}">
                                                    {{ $option->label }}. {{ $option->content }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($q->explanation)
                                        <div class="q-opt-options" style="display: block; margin-top: 6px; white-space: pre-line;">
                                            {{ \Illuminate\Support\Str::limit($q->explanation, 160) }}
                                        </div>
                                    @endif
                                </td>

                                {{-- KỸ NĂNG & DẠNG BÀI --}}
                                <td>
                                    @php
                                        $skillStyles = [
                                            'listening' => 'background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;',
                                            'reading' => 'background:#d1fae5;color:#047857;border:1px solid #a7f3d0;',
                                            'speaking' => 'background:#fef3c7;color:#b45309;border:1px solid #fde68a;',
                                            'writing' => 'background:#ede9fe;color:#6d28d9;border:1px solid #ddd6fe;',
                                        ];
                                        $skillBadgeStyle = $skillStyles[$q->skill_key] ?? 'background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;';
                                    @endphp
                                    <div>
                                        <span style="display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 9999px; font-size: 12px; font-weight: 700; white-space: nowrap; {{ $skillBadgeStyle }}">
                                            {{ $q->skill_display }}
                                        </span>
                                    </div>
                                    <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                                        {{ $q->question_type_display }} &bull; {{ $q->difficulty_label }}
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
                                <td style="text-align: center; white-space: nowrap;">
                                    @php
                                        $stUpper = strtoupper((string) $q->status);
                                        $stConfig = match ($stUpper) {
                                            'ACTIVE' => ['bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0', 'dot' => '#10b981'],
                                            'DRAFT', 'PENDING' => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a', 'dot' => '#f59e0b'],
                                            'REPORTED' => ['bg' => '#fef2f2', 'color' => '#b91c1c', 'border' => '#fecaca', 'dot' => '#ef4444'],
                                            'ARCHIVED' => ['bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1', 'dot' => '#64748b'],
                                            default => ['bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1', 'dot' => '#64748b'],
                                        };
                                    @endphp
                                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 11px; border-radius: 9999px; font-size: 12px; font-weight: 700; white-space: nowrap; background: {{ $stConfig['bg'] }}; color: {{ $stConfig['color'] }}; border: 1px solid {{ $stConfig['border'] }};">
                                        <span style="width: 6px; height: 6px; border-radius: 9999px; background: {{ $stConfig['dot'] }}; flex-shrink: 0;"></span>
                                        <span>{{ $q->status_label }}</span>
                                    </span>
                                </td>

                                {{-- THAO TÁC --}}
                                <td class="admin-actions-cell">
                                    <div class="user-actions-cell">
                                        {{-- Nút Xem trước mô phỏng đề thi (luôn sẵn sàng cho mọi trạng thái) --}}
                                        <button type="button"
                                                class="action-icon-btn"
                                                title="Xem trước mô phỏng đề thi"
                                                onclick='openPreviewModal(@json($q->toPreviewPayload()))'>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>

                                        @if ($isPending)
                                            {{-- Nút duyệt nhanh (Check icon) --}}
                                            <button type="button"
                                                    class="action-icon-btn"
                                                    style="color: #059669;"
                                                    title="Duyệt câu hỏi này"
                                                    onclick="submitSingleQuestionAction('{{ route('admin.questions.toggle-status', $q->question_id) }}', 'PATCH', 'ACTIVE')">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
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

                                        {{-- Nút Xóa câu hỏi (Trash icon) --}}
                                        <button type="button"
                                                class="action-icon-btn danger"
                                                title="Xóa câu hỏi"
                                                aria-label="Xóa câu hỏi #Q-{{ $q->question_id }}"
                                                onclick="if (confirm('Bạn có chắc chắn muốn xóa câu hỏi #Q-{{ $q->question_id }}?')) { submitSingleQuestionAction('{{ route('admin.questions.destroy', $q->question_id) }}', 'DELETE'); }">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
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
                        Hiển thị {{ $questions->firstItem() ?? 0 }}-{{ $questions->lastItem() ?? 0 }} trong tổng số {{ number_format($questions->total()) }} câu hỏi
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
                    {{-- Nút về trang đầu (ChevronsLeft) --}}
                    @if ($questions->onFirstPage())
                        <span class="page-btn disabled" title="Trang đầu" aria-disabled="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="11 17 6 12 11 7"></polyline>
                                <polyline points="18 17 13 12 18 7"></polyline>
                            </svg>
                        </span>
                    @else
                        <a href="{{ $questions->url(1) }}" class="page-btn" title="Trang đầu">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="11 17 6 12 11 7"></polyline>
                                <polyline points="18 17 13 12 18 7"></polyline>
                            </svg>
                        </a>
                    @endif

                    {{-- Nút trang trước (ChevronLeft) --}}
                    @if ($questions->onFirstPage())
                        <span class="page-btn disabled" title="Trang trước" aria-disabled="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </span>
                    @else
                        <a href="{{ $questions->previousPageUrl() }}" class="page-btn" title="Trang trước">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </a>
                    @endif

                    {{-- Dynamic Page Numbers --}}
                    @php
                        $cur = $questions->currentPage();
                        $last = $questions->lastPage();
                    @endphp

                    @if ($last <= 7)
                        @for ($i = 1; $i <= $last; $i++)
                            @if ($i === $cur)
                                <span class="page-btn active">{{ $i }}</span>
                            @else
                                <a href="{{ $questions->url($i) }}" class="page-btn">{{ $i }}</a>
                            @endif
                        @endfor
                    @else
                        @if ($cur > 3)
                            <a href="{{ $questions->url(1) }}" class="page-btn">1</a>
                            @if ($cur > 4)
                                <span class="page-btn disabled">...</span>
                            @endif
                        @endif

                        @for ($i = max(1, $cur - 2); $i <= min($last, $cur + 2); $i++)
                            @if ($i === $cur)
                                <span class="page-btn active">{{ $i }}</span>
                            @else
                                <a href="{{ $questions->url($i) }}" class="page-btn">{{ $i }}</a>
                            @endif
                        @endfor

                        @if ($cur < $last - 2)
                            @if ($cur < $last - 3)
                                <span class="page-btn disabled">...</span>
                            @endif
                            <a href="{{ $questions->url($last) }}" class="page-btn">{{ $last }}</a>
                        @endif
                    @endif

                    {{-- Nút trang sau (ChevronRight) --}}
                    @if ($questions->hasMorePages())
                        <a href="{{ $questions->nextPageUrl() }}" class="page-btn" title="Trang sau">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    @else
                        <span class="page-btn disabled" title="Trang sau" aria-disabled="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </span>
                    @endif

                    {{-- Nút về trang cuối (ChevronsRight) --}}
                    @if ($questions->hasMorePages())
                        <a href="{{ $questions->url($questions->lastPage()) }}" class="page-btn" title="Trang cuối">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="13 17 18 12 13 7"></polyline>
                                <polyline points="6 17 11 12 6 7"></polyline>
                            </svg>
                        </a>
                    @else
                        <span class="page-btn disabled" title="Trang cuối" aria-disabled="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="13 17 18 12 13 7"></polyline>
                                <polyline points="6 17 11 12 6 7"></polyline>
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- Form ẩn độc lập để thực hiện Xóa / Duyệt nhanh từng dòng (tránh lồng form bên trong #bulk-action-form) --}}
    <form id="single-question-action-form" method="POST" action="" style="display: none;">
        @csrf
        <input type="hidden" name="_method" id="single-question-method" value="POST">
        <input type="hidden" name="status" id="single-question-status" value="">
    </form>

    {{-- 7. Hai thẻ thống kê thực tế bên dưới cùng --}}
    <div class="qbank-feature-grid">
        {{-- Thẻ 1: Trạng thái xuất bản thực tế --}}
        <div class="qbank-feature-card">
            <div class="qbank-feat-icon icon-purple">
                <span>A</span>
            </div>
            <div>
                <h3 class="qbank-feat-title">Tổng quan trạng thái Ngân hàng</h3>
                <p class="qbank-feat-desc">
                    Hiện có <strong>{{ number_format($stats['active_count'] ?? 0) }}</strong> câu hỏi đã xuất bản (ACTIVE) và <strong>{{ number_format($stats['draft_count'] ?? 0) }}</strong> bản nháp (DRAFT) trong cơ sở dữ liệu.
                </p>
            </div>
        </div>

        {{-- Thẻ 2: Kho Audio TTS thực tế --}}
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
                <h3 class="qbank-feat-title">Kho Audio Listening thực tế</h3>
                <p class="qbank-feat-desc">
                    Đã lưu trữ <strong>{{ number_format($stats['audio_ready'] ?? 0) }}</strong> tệp âm thanh MP3 thực tế trong hệ thống (hỗ trợ sinh hàng loạt qua nút Sinh Audio TTS).
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
                            <option value="UNSET">Chưa phân loại</option>
                            <option value="EASY">Cơ bản (Dưới 500)</option>
                            <option value="MEDIUM">Trung bình (500 - 750)</option>
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
                    <label>Giải thích &amp; Đáp án chuẩn</label>
                    <textarea id="edit_explanation" name="explanation" class="form-control-admin" rows="2"></textarea>
                </div>

                <div style="border-top: 1px solid #edf0f7; padding-top: 12px; margin-top: 10px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px;">
                        <label style="margin: 0;">Audio Listening (TTS MP3)</label>
                        <button type="button" id="edit_btn_gen_tts" class="btn-admin-action" style="padding: 4px 10px; font-size: 12px; color: #0369a1; border-color: #bae6fd; background: #f0f9ff;" onclick="generateTtsInEditModal()">
                            Sinh Audio TTS
                        </button>
                    </div>
                    <input type="hidden" id="edit_question_id" value="">
                    <input type="hidden" id="edit_audio_url" name="audio_url" value="">
                    <div id="edit_audio_preview_wrap" style="display: none; margin-top: 6px;">
                        <audio id="edit_audio_player" controls style="width: 100%; height: 34px;"></audio>
                    </div>
                    <div id="edit_tts_status" style="font-size: 12px; color: #64748b; margin-top: 4px;"></div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-edit-question')">Hủy</button>
                <button type="submit" class="btn-admin-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: XEM TRƯỚC MÔ PHỎNG ĐỀ THI TOEIC THẬT --}}
@include('admin.questions.partials.preview-modal')

{{-- MODAL 4: NHẬP WORD (.DOCX) / EXCEL / JSON --}}
<div id="modal-import-qbank" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 580px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Nhập dữ liệu Đề thi &amp; Câu hỏi (.docx, .csv, .json)</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-import-qbank')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.questions.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="admin-modal-body">
                <p style="font-size: 13.5px; color: #475569; margin-top: 0;">
                    Hỗ trợ tệp <strong>Word (.docx), .CSV, .TXT hoặc .JSON</strong> cho cả 4 kỹ năng TOEIC (Listening, Reading, Speaking, Writing), tự động gộp bản dịch song ngữ và trích xuất ảnh nhúng.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px;">
                    <a href="{{ route('admin.questions.sample-docx', ['skill' => 'all']) }}" class="btn-admin-action" style="display: inline-flex; align-items: center; gap: 5px; font-size: 12px; padding: 5px 10px; text-decoration: none; color: #4338ca; border-color: #c7d2fe; background: #eef2ff;">
                        <span>Mẫu Full 4 kỹ năng</span>
                    </a>
                    <a href="{{ route('admin.questions.sample-docx', ['skill' => 'listening']) }}" class="btn-admin-action" style="font-size: 12px; padding: 5px 10px; text-decoration: none; color: #0369a1; border-color: #bae6fd; background: #e0f2fe;">Listening</a>
                    <a href="{{ route('admin.questions.sample-docx', ['skill' => 'reading']) }}" class="btn-admin-action" style="font-size: 12px; padding: 5px 10px; text-decoration: none; color: #047857; border-color: #a7f3d0; background: #d1fae5;">Reading</a>
                    <a href="{{ route('admin.questions.sample-docx', ['skill' => 'speaking']) }}" class="btn-admin-action" style="font-size: 12px; padding: 5px 10px; text-decoration: none; color: #b45309; border-color: #fde68a; background: #fef3c7;">Speaking</a>
                    <a href="{{ route('admin.questions.sample-docx', ['skill' => 'writing']) }}" class="btn-admin-action" style="font-size: 12px; padding: 5px 10px; text-decoration: none; color: #6d28d9; border-color: #ddd6fe; background: #ede9fe;">Writing</a>
                </div>
                <div class="form-group-admin">
                    <label>Chọn tệp dữ liệu (.docx, .csv, .json)</label>
                    <input type="file" name="import_file" accept=".docx,.csv,.txt,.json,.xlsx" class="form-control-admin" required>
                </div>
                <div class="form-group-admin">
                    <label>Chế độ &amp; Kỹ năng nhận diện</label>
                    <select name="import_scope" class="form-control-admin">
                        @include('admin.questions.partials.import-scope-options')
                    </select>
                </div>
                <div class="form-group-admin">
                    <label>Trạng thái câu hỏi sau khi nhập</label>
                    <select name="publish_status" class="form-control-admin">
                        <option value="live" selected>Hoạt động ngay (ACTIVE - Tính ngay vào tiến độ Đề thi)</option>
                        <option value="draft">Lưu bản nháp (DRAFT - Chờ duyệt, chưa tính vào tiến độ Đề thi)</option>
                    </select>
                </div>
                <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 12px; font-size: 12.5px; color: #64748b; line-height: 1.55;">
                    <strong>Hỗ trợ đầy đủ 15 Part/Task TOEIC ETS:</strong><br>
                    <code>[Part 1–4]</code> Listening &bull; <code>[Part 5–7]</code> Reading &bull; <code>[Speaking Task 1–5]</code> &bull; <code>[Writing Task 1–3]</code>.<br>
                    Hỗ trợ đáp án dạng <code>A.</code> / <code>(A)</code> / <code>→</code> và gộp bản dịch tiếng Việt tự động.
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-import-qbank')">Hủy</button>
                <button type="submit" class="btn-admin-primary">Tải lên &amp; Đồng bộ</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 5: CHỌN ĐỀ THI ĐỂ GẮN CÂU HỎI (HƯỚNG 1) --}}
<div id="modal-assign-test" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 520px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Gắn câu hỏi vào Đề thi TOEIC</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-assign-test')">&times;</button>
        </div>
        <div class="admin-modal-body">
            <p style="font-size: 13.5px; color: #475569; margin-top: 0;">
                Bạn đã chọn <strong id="assign-count-display" style="color: #4338ca;">0</strong> câu hỏi từ Ngân hàng câu hỏi. Vui lòng chọn Đề thi đích bên dưới để gắn:
            </p>

            @if (isset($availableTests) && $availableTests->isNotEmpty())
                <div class="form-group-admin">
                    <label>Chọn Đề thi TOEIC <span style="color: #dc2626;">*</span></label>
                    @php
                        $examTypeLabels = [
                            'TOEIC_LR' => 'TOEIC L&R',
                            'TOEIC_LISTENING' => 'TOEIC Listening',
                            'TOEIC_READING' => 'TOEIC Reading',
                            'TOEIC_SPEAKING' => 'TOEIC Speaking',
                            'TOEIC_WRITING' => 'TOEIC Writing',
                            'TOEIC_SW' => 'TOEIC S&W',
                        ];
                    @endphp
                    <select id="modal-select-test-id" class="form-control-admin" style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 14px;">
                        @foreach ($availableTests as $testItem)
                            @php
                                $exTypeCode = (string) ($testItem->blueprint?->exam_type ?? 'TOEIC_LR');
                                $exTypeText = $examTypeLabels[$exTypeCode] ?? $exTypeCode;
                            @endphp
                            <option value="{{ $testItem->test_form_id }}" {{ ($targetTest?->test_form_id === $testItem->test_form_id) ? 'selected' : '' }}>
                                [{{ $exTypeText }}] {{ $testItem->test_code }} - {{ $testItem->title }} (Hiện có {{ $testItem->questions_count ?? 0 }} câu)
                            </option>
                        @endforeach
                    </select>
                </div>

                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #047857; font-weight: 600; margin-top: 12px; cursor: pointer;">
                    <input type="checkbox" id="modal-chk-auto-activate" checked>
                    <span>Tự động xuất bản (ACTIVE) các câu bản nháp khi gắn vào đề</span>
                </label>

                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; margin-top: 8px; cursor: pointer;">
                    <input type="checkbox" id="modal-chk-redirect-test" {{ !empty($targetTest) ? 'checked' : '' }}>
                    <span>Chuyển đến trang Chi tiết Đề thi ngay sau khi gắn xong</span>
                </label>
            @else
                <div style="padding: 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; color: #b45309; font-size: 13.5px;">
                    Hiện chưa có đề thi nào trong hệ thống. Vui lòng <a href="{{ route('admin.tests.index') }}" style="font-weight: 700; color: #4338ca;">Tạo đề thi mới tại Quản lý đề thi</a> trước khi gắn câu hỏi.
                </div>
            @endif
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-assign-test')">Hủy</button>
            @if (isset($availableTests) && $availableTests->isNotEmpty())
                <button type="button" class="btn-admin-primary" onclick="confirmAssignQuestionsToTest()">Xác nhận gắn vào Đề thi</button>
            @endif
        </div>
    </div>
</div>

{{-- MODAL 6: TIẾN ĐỘ SINH AUDIO TTS THEO LÔ --}}
<div id="modal-batch-tts-progress" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 480px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Đang sinh Audio TTS tự động</h3>
            <button type="button" class="admin-modal-close" id="batch-tts-close-x" onclick="closeAdminModal('modal-batch-tts-progress')">&times;</button>
        </div>
        <div class="admin-modal-body">
            <p id="batch-tts-summary" style="font-size: 13.5px; color: #334155; margin-top: 0;">
                Đang chuẩn bị danh sách câu hỏi Listening...
            </p>
            <div style="width: 100%; height: 12px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-bottom: 10px;">
                <div id="batch-tts-bar" style="width: 0%; height: 100%; background: #0284c7; border-radius: 999px; transition: width 0.3s ease;"></div>
            </div>
            <div id="batch-tts-detail" style="font-size: 12.5px; color: #64748b;"></div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" id="batch-tts-done-btn" class="btn-admin-primary" style="display: none;" onclick="location.reload()">Hoàn tất &amp; Tải lại trang</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAdminModal(id) {
        var modal = document.getElementById(id);
        if (modal) modal.classList.add('open');
    }

    function closeAdminModal(id) {
        var modal = document.getElementById(id);
        if (modal) modal.classList.remove('open');
    }

    var totalMatchingQuestions = {{ (int) $questions->total() }};
    var isAllMatchingSelected = false;

    function getSelectedCount() {
        if (isAllMatchingSelected) {
            return totalMatchingQuestions;
        }
        return document.querySelectorAll('.q-row-cb:checked').length;
    }

    function renderSelectionState() {
        var rows = document.querySelectorAll('.q-row-cb');
        var checkedCount = document.querySelectorAll('.q-row-cb:checked').length;
        var count = getSelectedCount();

        var selectAllInput = document.getElementById('bulk-select-all');
        if (selectAllInput) {
            selectAllInput.value = isAllMatchingSelected ? '1' : '0';
        }

        var badge = document.getElementById('selected-badge');
        if (badge) {
            badge.innerText = count;
        }

        var labelEl = document.getElementById('selected-label');
        if (labelEl) {
            labelEl.innerText = (isAllMatchingSelected && totalMatchingQuestions > rows.length)
                ? 'câu hỏi đã chọn (toàn bộ danh sách)'
                : 'câu hỏi đã chọn';
        }

        var selectAllLink = document.getElementById('select-all-matching-link');
        var selectPageLink = document.getElementById('select-page-only-link');
        var clearLink = document.getElementById('clear-selection-link');

        if (selectAllLink) {
            selectAllLink.style.display = (totalMatchingQuestions > 0 && !isAllMatchingSelected) ? 'inline' : 'none';
        }
        if (selectPageLink) {
            selectPageLink.style.display = (isAllMatchingSelected && totalMatchingQuestions > rows.length) ? 'inline' : 'none';
        }
        if (clearLink) {
            clearLink.style.display = (count > 0) ? 'inline' : 'none';
        }

        var checkAllEl = document.getElementById('check-all-q');
        if (checkAllEl) {
            checkAllEl.checked = rows.length > 0 && (isAllMatchingSelected || checkedCount === rows.length);
        }
    }

    // Đếm số lượng câu hỏi được chọn khi tích từng dòng
    function updateSelectionCount() {
        isAllMatchingSelected = false;
        renderSelectionState();
    }

    // Chọn toàn bộ tất cả câu hỏi khớp bộ lọc hiện tại (không bị giới hạn theo trang)
    function selectAllMatchingQuestions() {
        if (totalMatchingQuestions <= 0) {
            return;
        }
        isAllMatchingSelected = true;
        document.querySelectorAll('.q-row-cb').forEach(function(cb) {
            cb.checked = true;
        });
        renderSelectionState();
    }

    // Chuyển về chỉ chọn các câu trên trang hiện tại
    function selectCurrentPageOnly() {
        isAllMatchingSelected = false;
        document.querySelectorAll('.q-row-cb').forEach(function(cb) {
            cb.checked = true;
        });
        renderSelectionState();
    }

    // Bỏ chọn tất cả
    function clearAllSelectedQuestions() {
        isAllMatchingSelected = false;
        document.querySelectorAll('.q-row-cb').forEach(function(cb) {
            cb.checked = false;
        });
        renderSelectionState();
    }

    // Tích ô "Chọn tất cả" ở đầu bảng -> chọn toàn bộ câu hỏi trong danh sách hiện tại
    var checkAllQ = document.getElementById('check-all-q');
    if (checkAllQ) {
        checkAllQ.addEventListener('change', function() {
            if (this.checked) {
                selectAllMatchingQuestions();
            } else {
                clearAllSelectedQuestions();
            }
        });
    }

    // Xử lý gửi hành động hàng loạt
    function submitBulkAction(actionType) {
        var count = getSelectedCount();
        if (count === 0) {
            alert('Vui lòng tích chọn ít nhất một câu hỏi trong danh sách.');
            return;
        }

        if (actionType === 'assign') {
            var countEl = document.getElementById('assign-count-display');
            if (countEl) countEl.innerText = count;
            openAdminModal('modal-assign-test');
            return;
        }

        var label = actionType === 'delete' ? 'XÓA' : 'PHÊ DUYỆT';
        var scopeText = isAllMatchingSelected ? ' (toàn bộ danh sách hiện tại)' : '';
        if (confirm('Bạn có chắc chắn muốn ' + label + ' ' + count + ' câu hỏi đã chọn' + scopeText + '?')) {
            document.getElementById('bulk-action-type').value = actionType;
            document.getElementById('bulk-action-form').submit();
        }
    }

    function confirmAssignQuestionsToTest() {
        var selectEl = document.getElementById('modal-select-test-id');
        if (!selectEl || !selectEl.value) {
            alert('Vui lòng chọn một Đề thi để gắn câu hỏi.');
            return;
        }
        var redirectChk = document.getElementById('modal-chk-redirect-test');
        var autoActivateChk = document.getElementById('modal-chk-auto-activate');
        document.getElementById('bulk-assign-test-id').value = selectEl.value;
        document.getElementById('bulk-redirect-to-test').value = (redirectChk && redirectChk.checked) ? '1' : '0';
        var autoActivateInput = document.getElementById('bulk-auto-activate-drafts');
        if (autoActivateInput) {
            autoActivateInput.value = (!autoActivateChk || autoActivateChk.checked) ? '1' : '0';
        }
        document.getElementById('bulk-action-type').value = 'assign';
        document.getElementById('bulk-action-form').submit();
    }

    function submitSingleQuestionAction(actionUrl, httpMethod, statusVal) {
        var form = document.getElementById('single-question-action-form');
        var methodInput = document.getElementById('single-question-method');
        var statusInput = document.getElementById('single-question-status');
        if (!form || !methodInput) return;

        form.action = actionUrl;
        methodInput.value = httpMethod || 'POST';
        if (statusInput) {
            statusInput.value = statusVal || '';
        }
        form.submit();
    }

    // Mở modal Edit câu hỏi
    function openEditQuestionModal(q) {
        var form = document.getElementById('form-edit-question');
        form.action = '{{ url('/admin/questions') }}/' + q.question_id;
        document.getElementById('edit-modal-title').innerText = 'Cập nhật câu hỏi #Q-' + q.question_id;
        document.getElementById('edit_question_id').value = q.question_id;
        document.getElementById('edit_section_id').value = q.section_id || '5';
        document.getElementById('edit_content').value = q.content || '';
        document.getElementById('edit_difficulty').value = q.difficulty || 'MEDIUM';
        document.getElementById('edit_status').value = q.status || 'ACTIVE';
        document.getElementById('edit_explanation').value = q.explanation || '';

        var qSet = q.question_set || null;
        var existingAudio = qSet ? (qSet.resolved_audio_url || '') : '';
        document.getElementById('edit_audio_url').value = qSet ? (qSet.audio_url || '') : '';
        var audioWrap = document.getElementById('edit_audio_preview_wrap');
        var audioPlayer = document.getElementById('edit_audio_player');
        var ttsStatus = document.getElementById('edit_tts_status');
        if (audioWrap && audioPlayer) {
            if (existingAudio) {
                audioPlayer.src = existingAudio;
                audioWrap.style.display = 'block';
                if (ttsStatus) ttsStatus.textContent = 'Đã có file MP3 đính kèm.';
            } else {
                audioPlayer.removeAttribute('src');
                audioWrap.style.display = 'none';
                if (ttsStatus) ttsStatus.textContent = '';
            }
        }
        openAdminModal('modal-edit-question');
    }

    function generateTtsInEditModal() {
        var qId = document.getElementById('edit_question_id').value;
        var btn = document.getElementById('edit_btn_gen_tts');
        var statusEl = document.getElementById('edit_tts_status');
        if (!qId) return;

        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Đang sinh...';
        }
        if (statusEl) statusEl.textContent = 'Đang gọi TextToSpeechService...';

        fetch('{{ url('/api/admin/questions/generate-tts') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ question_id: Number(qId) })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Sinh Audio TTS';
            }
            if (res && res.success && res.audio_url) {
                document.getElementById('edit_audio_url').value = res.audio_url;
                var audioWrap = document.getElementById('edit_audio_preview_wrap');
                var audioPlayer = document.getElementById('edit_audio_player');
                if (audioWrap && audioPlayer) {
                    audioPlayer.src = res.audio_url;
                    audioWrap.style.display = 'block';
                }
                if (statusEl) statusEl.textContent = 'Sinh audio MP3 thành công (' + (res.duration_estimate || 5) + 's)!';
            } else {
                if (statusEl) statusEl.textContent = (res && res.message) ? res.message : 'Không thể sinh audio.';
            }
        })
        .catch(function() {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Sinh Audio TTS';
            }
            if (statusEl) statusEl.textContent = 'Lỗi kết nối khi sinh audio.';
        });
    }

    async function startBatchGenerateTts() {
        var checkedBoxes = Array.from(document.querySelectorAll('.q-row-cb:checked'));
        if (checkedBoxes.length === 0) {
            alert('Vui lòng tích chọn ít nhất một câu hỏi Listening để sinh Audio TTS.');
            return;
        }

        var selectedIds = checkedBoxes.map(function(cb) { return Number(cb.value); });
        if (!confirm('Sinh Audio MP3 tự động cho ' + selectedIds.length + ' câu hỏi đã chọn (xử lý an toàn từng lô 5 câu)?')) {
            return;
        }

        var summaryEl = document.getElementById('batch-tts-summary');
        var barEl = document.getElementById('batch-tts-bar');
        var detailEl = document.getElementById('batch-tts-detail');
        var doneBtn = document.getElementById('batch-tts-done-btn');

        if (doneBtn) doneBtn.style.display = 'none';
        if (barEl) barEl.style.width = '5%';
        if (summaryEl) summaryEl.textContent = 'Đang xử lý ' + selectedIds.length + ' câu hỏi theo từng lô 5 câu...';
        if (detailEl) detailEl.textContent = '';
        openAdminModal('modal-batch-tts-progress');

        var totalSucceeded = 0;
        var totalFailed = 0;
        var chunkSize = 5;

        for (var i = 0; i < selectedIds.length; i += chunkSize) {
            var chunk = selectedIds.slice(i, i + chunkSize);
            try {
                var resp = await fetch('{{ url('/api/admin/questions/batch-generate-tts') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ question_ids: chunk })
                });
                var data = await resp.json();
                totalSucceeded += Number(data.succeeded || 0);
                totalFailed += Number(data.failed || 0);
            } catch (e) {
                totalFailed += chunk.length;
            }

            var processedCount = Math.min(selectedIds.length, i + chunk.length);
            var pct = Math.round((processedCount / selectedIds.length) * 100);
            if (barEl) barEl.style.width = pct + '%';
            if (detailEl) {
                detailEl.textContent = 'Tiến độ: ' + processedCount + '/' + selectedIds.length + ' câu (' + totalSucceeded + ' nhóm thành công, ' + totalFailed + ' lỗi).';
            }
        }

        if (summaryEl) {
            summaryEl.textContent = 'Hoàn tất sinh Audio TTS! Thành công: ' + totalSucceeded + ' nhóm, Thất bại: ' + totalFailed + '.';
        }
        if (doneBtn) doneBtn.style.display = 'inline-flex';
    }
</script>
@endpush
