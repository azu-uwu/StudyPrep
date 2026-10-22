@extends('layouts.admin', ['active' => 'ngan-hang-cau-hoi'])

@section('title', 'Tạo nhóm câu hỏi TOEIC')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <span>Quản trị nội dung</span>
    <span class="crumb-arrow">></span>
    <a href="{{ route('admin.questions.index') }}" style="color: inherit; text-decoration: none;">Ngân hàng câu hỏi</a>
    <span class="crumb-arrow">></span>
    <span style="color: var(--admin-primary); font-weight: 600;">Tạo nhóm câu hỏi (Question Group)</span>
@endsection

@section('content')
<div class="qgrp-container">
    <form action="{{ route('admin.questions.group.store') }}" method="POST" id="questionGroupForm">
        @csrf
        <input type="hidden" name="action_type" id="actionTypeInput" value="publish">

        {{-- 1. HEADER --}}
        <div class="qgrp-header">
            <div class="qgrp-title-wrap">
                <h1 class="qgrp-page-title">Tạo nhóm câu hỏi TOEIC</h1>
                <span class="qgrp-badge-code">#GRP-2025-RC07-M2</span>
                <span class="qgrp-badge-part">Part 7: Đọc hiểu (Double Passage)</span>
            </div>
            <div class="qgrp-header-actions">
                <button type="button" class="qgrp-btn-header" onclick="scrollToSim()" title="Xem trước trên màn hình thi">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>Xem trước đề thi</span>
                </button>
                <button type="button" class="qgrp-btn-header" onclick="submitAsDraft()" title="Lưu nháp nhóm câu hỏi">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Lưu bản nháp</span>
                </button>
                <button type="submit" class="qgrp-btn-header primary" title="Lưu và đưa vào ngân hàng đề thi">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <span>Lưu & Đưa vào ngân hàng đề</span>
                </button>
            </div>
        </div>

        {{-- 2. SYNC NOTICE BANNER --}}
        <div class="qgrp-sync-banner">
            <div class="qgrp-sync-left">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                </svg>
                <span><strong>Chế độ đồng bộ chuẩn ETS 2024:</strong> Đang áp dụng ma trận 5 câu hỏi phân loại cho Passage đôi. Hệ thống tự động kiểm tra số lượng từ vựng, bẫy đáp án và tính phân hóa.</span>
            </div>
            <span class="qgrp-sync-badge">ĐÃ TỰ ĐỘNG LƯU: 15:42:58</span>
        </div>

        {{-- 3. MAIN 2-COLUMN GRID --}}
        <div class="qgrp-top-grid">
            {{-- CỘT TRÁI: Phân loại & Ngữ cảnh bài đọc --}}
            <div class="qgrp-left-col">
                {{-- CARD 1: Phân loại & Ngữ cảnh --}}
                <div class="qgrp-card">
                    <div class="qgrp-card-header">
                        <h2 class="qgrp-card-title">
                            <span class="qgrp-dot-accent"></span>
                            <span>1. Phân loại & Ngữ cảnh nhóm câu hỏi</span>
                        </h2>
                        <p class="qgrp-card-subtitle">Xác định vị trí đề thi TOEIC, độ khó và mục tiêu trong hệ thống khóa học.</p>
                    </div>

                    <div class="qgrp-field-row-2">
                        <div class="qgrp-field-group">
                            <label class="qgrp-field-label">Phần thi (TOEIC Part) <span class="required">*</span></label>
                            <select name="section_id" class="qgrp-input-control" required>
                                @foreach ($sections as $sec)
                                    @php
                                        $isPart7 = str_contains($sec->section_name, 'Part 7') || $sec->section_id == 7;
                                    @endphp
                                    <option value="{{ $sec->section_id }}" {{ $isPart7 ? 'selected' : '' }}>
                                        {{ $sec->section_name }} - Đoạn kép Double (5 câu hỏi)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="qgrp-field-group">
                            <label class="qgrp-field-label">Bộ đề / Nguồn trích xuất (Test Pack) <span class="required">*</span></label>
                            <input type="text" name="test_pack" class="qgrp-input-control" value="ETS TOEIC 2024 - Test 03 #02" required>
                        </div>
                    </div>

                    <div class="qgrp-field-group">
                        <label class="qgrp-field-label">Tiêu đề nhóm / Chủ đề ngữ cảnh bài đọc:</label>
                        <input type="text" name="group_title" class="qgrp-input-control" value="Thông báo tuyển dụng & Email trao đổi lịch phỏng vấn (Job Vacancy & Interview Schedule)" required>
                    </div>
                </div>

                {{-- CARD 2: Dữ liệu ngữ cảnh dùng chung (Stimulus) --}}
                <div class="qgrp-card">
                    <div class="qgrp-stimulus-top">
                        <div>
                            <h2 class="qgrp-card-title">
                                <span class="qgrp-dot-accent"></span>
                                <span>2. Dữ liệu ngữ cảnh dùng chung (Stimulus)</span>
                            </h2>
                            <p class="qgrp-card-subtitle">Nhập đoạn văn hoặc đoạn thoại đính kèm cho nhóm câu hỏi (Ví dụ: đoạn văn kép 2 bài đọc).</p>
                        </div>
                        <div class="qgrp-toggle-group">
                            <span class="qgrp-toggle-btn active">Format chuẩn ETS</span>
                            <span class="qgrp-toggle-btn">Chế độ song ngữ</span>
                            <span class="qgrp-toggle-btn">Đoạn 2x(Q)</span>
                        </div>
                    </div>

                    <div class="qgrp-media-tabs">
                        <button type="button" class="qgrp-media-tab-btn active">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            <span>Văn bản đọc (Reading Passage) [2 đoạn]</span>
                        </button>
                        <button type="button" class="qgrp-media-tab-btn">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                            </svg>
                            <span>Audio đàm thoại & Transcript</span>
                        </button>
                        <button type="button" class="qgrp-media-tab-btn">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <span>Hình ảnh đính kèm / Biểu đồ</span>
                        </button>
                    </div>

                    {{-- ĐOẠN 1 --}}
                    <div class="qgrp-passage-box">
                        <div class="qgrp-passage-top">
                            <span class="qgrp-passage-tag">ĐOẠN 1: Thông báo tuyển dụng (Notice)</span>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <label class="qgrp-field-label">Tiêu đề phụ / Format đoạn văn</label>
                            <input type="text" name="passage_1_title" class="qgrp-input-control" value="BrightTech Solutions - Senior Frontend Developer Opening">
                        </div>
                        <div class="qgrp-editor-toolbar">
                            <div class="qgrp-toolbar-btns">
                                <button type="button" class="qgrp-tool-btn">B</button>
                                <button type="button" class="qgrp-tool-btn"><em>I</em></button>
                                <button type="button" class="qgrp-tool-btn"><u>U</u></button>
                                <button type="button" class="qgrp-tool-btn">”</button>
                                <button type="button" class="qgrp-tool-btn">🔗</button>
                            </div>
                            <span style="color: #94a3b8;">Độ dài: 84 từ</span>
                        </div>
                        <textarea name="passage_1_content" class="qgrp-passage-textarea" rows="4">BrightTech Solutions is seeking an accomplished Senior Frontend Developer to lead our client-facing web application team in Seattle. The selected candidate will collaborate closely with UI/UX researchers and backend engineers to build resilient, accessible cloud architectures.

Requirements:
• Must hold a Bachelor's Degree in Computer Science, Software Engineering, or a related field.
• At least 7 years of professional web development experience.</textarea>
                    </div>

                    {{-- ĐOẠN 2 --}}
                    <div class="qgrp-passage-box">
                        <div class="qgrp-passage-top">
                            <span class="qgrp-passage-tag" style="background: #fee2e2; color: #be123c;">ĐOẠN 2: Email phản hồi phỏng vấn (Email Correspondence)</span>
                            <span class="qgrp-connected-badge">+ Đã nối</span>
                        </div>
                        <div class="qgrp-field-row-2">
                            <div class="qgrp-field-group">
                                <label class="qgrp-field-label">Người gửi (From):</label>
                                <input type="text" name="passage_2_from" class="qgrp-input-control" value="Clara Sanders <c.sanders@techmail.org>">
                            </div>
                            <div class="qgrp-field-group">
                                <label class="qgrp-field-label">Người nhận (To):</label>
                                <input type="text" name="passage_2_to" class="qgrp-input-control" value="BrightTech HR Department <hr@brighttech-solutions.com>">
                            </div>
                        </div>
                        <div class="qgrp-editor-toolbar">
                            <div class="qgrp-toolbar-btns">
                                <button type="button" class="qgrp-tool-btn">B</button>
                                <button type="button" class="qgrp-tool-btn"><em>I</em></button>
                                <button type="button" class="qgrp-tool-btn"><u>U</u></button>
                                <button type="button" class="qgrp-tool-btn">”</button>
                            </div>
                            <span style="color: #94a3b8;">Độ dài: 108 từ</span>
                        </div>
                        <textarea name="passage_2_content" class="qgrp-passage-textarea" rows="4">Dear Hiring Committee:

Thank you for reviewing my credentials and extending an invitation for the first-round panel interview originally slated for Thursday, October 24 at 10:00 AM.

Unfortunately, due to an unavoidable commitment at my current firm's regional symposium in Portland (from Wednesday, October 23 through Friday, October 25), I would like to request a rescheduling to Monday, October 28 if your team can accommodate this adjustment.</textarea>
                    </div>

                    <button type="button" class="qgrp-add-passage-btn" onclick="alert('Đã sẵn sàng thêm đoạn văn thứ 3 (Triple Passage)')">
                        <span>+ Thêm đoạn văn thứ 3 (Chuyển thành Triple Passage 3 đoạn)</span>
                    </button>
                </div>
            </div>

            {{-- CỘT PHẢI: Mô phỏng thi Live & Phân quyền biên tập --}}
            <div class="qgrp-right-col" id="simSection">
                {{-- MÔ PHỎNG THI LIVE (PREVIEW) --}}
                <div class="qgrp-card" style="padding: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <h2 class="qgrp-card-title" style="font-size: 14.5px; margin: 0;">
                            <span class="qgrp-dot-accent"></span>
                            <span>Mô phỏng giao diện thi Live (Preview)</span>
                        </h2>
                    </div>
                    <p class="qgrp-card-subtitle" style="margin-bottom: 12px;">Màn hình mô phỏng giao diện thi 2 màn hình chuẩn mới đề thi ETS TOEIC:</p>

                    <div class="qgrp-sim-screen">
                        <div class="qgrp-sim-topbar">
                            <span>TOEIC Online Test 2024 / Part 7</span>
                            <span class="qgrp-sim-timer">Time Left: 43:21</span>
                        </div>
                        <div class="qgrp-sim-body">
                            {{-- Cột trái Simulator: Đoạn văn --}}
                            <div class="qgrp-sim-passage">
                                <div style="font-weight: 700; color: #1e293b; margin-bottom: 6px; font-size: 11px;">
                                    Questions 191-195 refer to the following notice and email.
                                </div>
                                <div style="font-size: 11px; margin-bottom: 6px;">
                                    <strong>[Notice]</strong><br>
                                    BrightTech Solutions is seeking an accomplished Senior Frontend Developer...
                                </div>
                                <div style="font-size: 11px;">
                                    <strong>[Email]</strong><br>
                                    Dear Hiring Committee: Thank you for extending an invitation...
                                </div>
                            </div>

                            {{-- Cột phải Simulator: Câu hỏi 194 --}}
                            <div class="qgrp-sim-question">
                                <div style="font-weight: 700; color: #1e293b; font-size: 11.5px;">
                                    194. When will Ms. Sanders most likely participate in the panel interview?
                                </div>
                                <div class="qgrp-sim-option">
                                    <input type="radio" disabled>
                                    <span>(A) On October 15</span>
                                </div>
                                <div class="qgrp-sim-option">
                                    <input type="radio" disabled>
                                    <span>(B) On October 24</span>
                                </div>
                                <div class="qgrp-sim-option">
                                    <input type="radio" disabled>
                                    <span>(C) On October 25</span>
                                </div>
                                <div class="qgrp-sim-option checked">
                                    <input type="radio" checked disabled>
                                    <span>(D) On Monday, October 28</span>
                                </div>
                            </div>
                        </div>
                        <div class="qgrp-sim-footer">
                            <span>Đã hoàn thành 75%</span>
                            <span style="color: #4f46e5; font-weight: 700;">Time Reading: 100% (Q4)</span>
                        </div>
                    </div>
                </div>

                {{-- CARD: PHÂN QUYỀN & NHẬT KÝ BIÊN TẬP --}}
                <div class="qgrp-card" style="padding: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <h2 class="qgrp-card-title" style="font-size: 14px; margin: 0;">
                            <span class="qgrp-dot-accent" style="background: #10b981;"></span>
                            <span>Phân quyền & Nhật ký biên tập</span>
                        </h2>
                        <span style="background: #fef3c7; color: #b45309; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                            Bản thảo nội bộ
                        </span>
                    </div>

                    <div class="qgrp-reviewer-item">
                        <div class="qgrp-avatar-sm">HT</div>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-size: 12.5px; font-weight: 700; color: #0f172a;">ThS. Hoàng Minh Tuấn</span>
                            <span style="font-size: 11px; color: #64748b;">Người biên soạn</span>
                        </div>
                    </div>

                    <div class="qgrp-reviewer-item">
                        <div class="qgrp-avatar-sm teal">ĐT</div>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-size: 12.5px; font-weight: 700; color: #0f172a;">Đặng Thu Trang (990 TOEIC)</span>
                            <span style="font-size: 11px; color: #64748b;">Chuyên gia phản biện</span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-top: 10px;">
                        <span>Cập nhật lần cuối:</span>
                        <strong style="color: #334155;">Hôm nay, 15:42:58</strong>
                    </div>

                    <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 12px; color: #475569; margin-top: 12px; cursor: pointer;">
                        <input type="checkbox" name="notify_reviewer" value="1" checked style="accent-color: #4f46e5; margin-top: 2px;">
                        <span>Gửi email thông báo cho Chuyên viên phản biện ngay sau khi lưu</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- 4. SECTION 3: DANH SÁCH 5 CÂU HỎI CON (SUB-QUESTIONS 191 - 195) --}}
        <div class="qgrp-subq-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h2 class="qgrp-card-title">
                        <span class="qgrp-dot-accent"></span>
                        <span>3. Danh sách 5 câu hỏi con (Sub-questions 191 - 195)</span>
                    </h2>
                    <p class="qgrp-card-subtitle">Thiết lập chuẩn các câu hỏi con: kiểu câu hỏi, đáp án chính xác và phân tích ý nghĩa chi tiết.</p>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" class="qgrp-btn-header" onclick="triggerAiQuestions()">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Tự động tạo câu hỏi bằng AI</span>
                    </button>
                    <button type="button" class="qgrp-btn-header primary" onclick="addNewSubQuestion()">
                        <span>+ Thêm câu hỏi</span>
                    </button>
                </div>
            </div>

            {{-- 5 Sub-question Filter Pills --}}
            <div class="qgrp-subq-pills">
                <span class="qgrp-subq-pill active" onclick="filterSubq('all', this)">Xem tất cả (5 câu)</span>
                <span class="qgrp-subq-pill" onclick="filterSubq('191', this)">Câu 191 (Suy luận)</span>
                <span class="qgrp-subq-pill" onclick="filterSubq('192', this)">Câu 192 (Chi tiết)</span>
                <span class="qgrp-subq-pill" onclick="filterSubq('193', this)">Câu 193 (Nắm bắt ý)</span>
                <span class="qgrp-subq-pill" onclick="filterSubq('194', this)">Câu 194 (Liên kết 2 đoạn)</span>
                <span class="qgrp-subq-pill" onclick="filterSubq('195', this)">Câu 195 (Từ vựng)</span>
            </div>

            {{-- CÂU 191 (Expanded view with full options and evidence) --}}
            <div class="qgrp-qitem-box" id="subq-191">
                <div class="qgrp-qitem-header">
                    <div class="qgrp-qitem-title-wrap">
                        <div class="qgrp-qnum-badge">191</div>
                        <span class="qgrp-qtype-label">Câu hỏi suy luận / Ý chìm (Inference)</span>
                        <span class="qgrp-qpart-pill">Part 7</span>
                    </div>
                    <div style="display: flex; gap: 8px; color: #94a3b8;">
                        <button type="button" class="qgrp-tool-btn" title="Ghim câu hỏi">🔖</button>
                        <button type="button" class="qgrp-tool-btn" title="Nhân bản">📋</button>
                        <button type="button" class="qgrp-tool-btn" title="Xóa">🗑</button>
                    </div>
                </div>

                <div class="qgrp-field-group" style="margin-bottom: 12px;">
                    <label class="qgrp-field-label">Đề bài câu hỏi:</label>
                    <input type="text" name="sub_questions[1][content]" class="qgrp-input-control" value="What is suggested about BrightTech Solutions in the notice?">
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span class="qgrp-field-label">Các phương án lựa chọn (Chọn đáp án đúng B):</span>
                    <label style="font-size: 12px; color: #475569; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                        <input type="checkbox" checked style="accent-color: #4f46e5;"> Xáo trộn đáp án
                    </label>
                </div>

                {{-- Option A --}}
                <div class="qgrp-opt-row">
                    <span class="qgrp-opt-letter">A</span>
                    <input type="text" name="sub_questions[1][options][A]" class="qgrp-opt-input" value="It recently relocated its operations to Seattle.">
                    <span class="qgrp-opt-tag">Nhiễu (Distractor)</span>
                </div>

                {{-- Option B (Correct) --}}
                <div class="qgrp-opt-row correct">
                    <span class="qgrp-opt-letter" style="color: #059669;">B</span>
                    <input type="text" name="sub_questions[1][options][B]" class="qgrp-opt-input" value="It is actively expanding its engineering staff." style="font-weight: 700; color: #065f46;">
                    <span class="qgrp-opt-tag" style="background: #dcfce7; color: #15803d;">Tính từ / Ý chính</span>
                    <span class="qgrp-opt-correct-badge">✓ Đáp án đúng</span>
                </div>

                {{-- Option C --}}
                <div class="qgrp-opt-row">
                    <span class="qgrp-opt-letter">C</span>
                    <input type="text" name="sub_questions[1][options][C]" class="qgrp-opt-input" value="It specializes in producing computer hardware.">
                    <span class="qgrp-opt-tag">Nhiễu (Sai mục tiêu)</span>
                </div>

                {{-- Option D --}}
                <div class="qgrp-opt-row">
                    <span class="qgrp-opt-letter">D</span>
                    <input type="text" name="sub_questions[1][options][D]" class="qgrp-opt-input" value="It requires applicants to hold a master's degree.">
                    <span class="qgrp-opt-tag">Nhiễu (Sai dữ liệu)</span>
                </div>

                {{-- Evidence and explanation --}}
                <div class="qgrp-exp-grid">
                    <div>
                        <div class="qgrp-exp-col-title">Trích dẫn dẫn chứng từ đoạn văn (Evidence quote):</div>
                        <div class="qgrp-exp-col-text">
                            "BrightTech Solutions is seeking an accomplished Senior Frontend Developer to lead our client-facing web application team in Seattle."
                        </div>
                    </div>
                    <div>
                        <div class="qgrp-exp-col-title">Giải thích kèm ngữ cảnh (Detailed explanation):</div>
                        <div class="qgrp-exp-col-text">
                            Đoạn 1 nêu rõ công ty đang tìm kiếm (is seeking) Senior Developer để dẫn dắt đội ngũ tại Seattle, cho thấy công ty đang mở rộng nhân sự kỹ thuật. Đáp án B chuẩn xác.
                        </div>
                    </div>
                </div>
            </div>

            {{-- CÂU 192 (Detail / Requirement) --}}
            <div class="qgrp-qitem-box" id="subq-192">
                <div class="qgrp-qitem-header">
                    <div class="qgrp-qitem-title-wrap">
                        <div class="qgrp-qnum-badge">192</div>
                        <span class="qgrp-qtype-label">Câu hỏi chi tiết cụ thể (Detail / Requirement)</span>
                        <span class="qgrp-qpart-pill">Part 7</span>
                    </div>
                </div>
                <div class="qgrp-field-group">
                    <label class="qgrp-field-label">Đề bài câu hỏi:</label>
                    <input type="text" name="sub_questions[2][content]" class="qgrp-input-control" value="According to the notice, what is a requirement for applicants?">
                </div>
                <div class="qgrp-compact-opts-grid">
                    <div class="qgrp-compact-opt selected">
                        <span>✓ <strong>A.</strong> At least 7 years of professional web development experience.</span>
                    </div>
                    <div class="qgrp-compact-opt">
                        <span><strong>B.</strong> Previous employment at a venture-backed cloud consulting firm.</span>
                    </div>
                    <div class="qgrp-compact-opt">
                        <span><strong>C.</strong> Willingness to relocate to Portland within three months.</span>
                    </div>
                    <div class="qgrp-compact-opt">
                        <span><strong>D.</strong> A portfolio showcasing at least three published mobile games.</span>
                    </div>
                </div>
            </div>

            {{-- CÂU 193 (Factual / Problem) --}}
            <div class="qgrp-qitem-box" id="subq-193">
                <div class="qgrp-qitem-header">
                    <div class="qgrp-qitem-title-wrap">
                        <div class="qgrp-qnum-badge">193</div>
                        <span class="qgrp-qtype-label">Câu hỏi nắm bắt thông tin (Factual / Problem)</span>
                        <span class="qgrp-qpart-pill">Đoạn 2 (Email)</span>
                    </div>
                </div>
                <div class="qgrp-field-group">
                    <label class="qgrp-field-label">Đề bài câu hỏi:</label>
                    <input type="text" name="sub_questions[3][content]" class="qgrp-input-control" value="What problem does Ms. Sanders mention in her email?">
                </div>
                <div class="qgrp-compact-opts-grid">
                    <div class="qgrp-compact-opt">
                        <span><strong>A.</strong> She erased her digital portfolio files during transit.</span>
                    </div>
                    <div class="qgrp-compact-opt">
                        <span><strong>B.</strong> Her flight to Seattle was unexpectedly cancelled.</span>
                    </div>
                    <div class="qgrp-compact-opt selected">
                        <span>✓ <strong>C.</strong> She is scheduled to attend a conference on October 24.</span>
                    </div>
                    <div class="qgrp-compact-opt">
                        <span><strong>D.</strong> She needs to submit her application before October 15.</span>
                    </div>
                </div>
            </div>

            {{-- CÂU 194 (Cross-Passage Synthesis) --}}
            <div class="qgrp-qitem-box" id="subq-194">
                <div class="qgrp-qitem-header">
                    <div class="qgrp-qitem-title-wrap">
                        <div class="qgrp-qnum-badge red">194</div>
                        <span class="qgrp-qtype-label">Câu hỏi liên kết 2 đoạn văn (Cross-Passage Synthesis)</span>
                        <span class="qgrp-qpart-pill pink">2 đoạn văn</span>
                    </div>
                </div>
                <div class="qgrp-field-group">
                    <label class="qgrp-field-label">Đề bài câu hỏi:</label>
                    <input type="text" name="sub_questions[4][content]" class="qgrp-input-control" value="When will Ms. Sanders most likely participate in an interview with BrightTech if agreed to her proposal?">
                </div>
                <div class="qgrp-compact-opts-grid">
                    <div class="qgrp-compact-opt">
                        <span><strong>A.</strong> On Tuesday, October 15</span>
                    </div>
                    <div class="qgrp-compact-opt">
                        <span><strong>B.</strong> On Thursday, October 24</span>
                    </div>
                    <div class="qgrp-compact-opt">
                        <span><strong>C.</strong> On Friday, October 25</span>
                    </div>
                    <div class="qgrp-compact-opt selected">
                        <span>✓ <strong>D.</strong> On Monday, October 28</span>
                    </div>
                </div>
            </div>

            {{-- CÂU 195 (Vocabulary in Context) --}}
            <div class="qgrp-qitem-box" id="subq-195">
                <div class="qgrp-qitem-header">
                    <div class="qgrp-qitem-title-wrap">
                        <div class="qgrp-qnum-badge">195</div>
                        <span class="qgrp-qtype-label">Từ vựng theo ngữ cảnh (Vocabulary in Context)</span>
                        <span class="qgrp-qpart-pill">Đoạn 2 (Email)</span>
                    </div>
                </div>
                <div class="qgrp-field-group">
                    <label class="qgrp-field-label">Đề bài câu hỏi:</label>
                    <input type="text" name="sub_questions[5][content]" class="qgrp-input-control" value="In the email, the word &quot;accommodate&quot; in paragraph 2 is closest in meaning to:">
                </div>
                <div class="qgrp-compact-opts-grid">
                    <div class="qgrp-compact-opt selected">
                        <span>✓ <strong>A.</strong> adjust (dàn xếp / điều chỉnh thích ứng)</span>
                    </div>
                    <div class="qgrp-compact-opt">
                        <span><strong>B.</strong> provide lodging for (cung cấp chỗ ở)</span>
                    </div>
                    <div class="qgrp-compact-opt">
                        <span><strong>C.</strong> invest in (đầu tư vào)</span>
                    </div>
                    <div class="qgrp-compact-opt">
                        <span><strong>D.</strong> furnish (trang bị đồ đạc)</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. STICKY BOTTOM ACTION BAR --}}
        <div class="qgrp-bottom-bar">
            <div class="qgrp-bottom-status">
                <span class="qgrp-pulse-green"></span>
                <span><strong>Nhóm câu hỏi Part 7: Đủ chuẩn 5 câu</strong> • Đã hoàn thiện 5/5 đáp án, giải thích và trích dẫn dẫn chứng.</span>
            </div>
            <div class="qgrp-bottom-actions">
                <a href="{{ route('admin.questions.index') }}" class="qgrp-btn-cancel">
                    Hủy bỏ thay đổi
                </a>
                <button type="button" class="qgrp-btn-draft" onclick="submitAsDraft()">
                    Lưu bản nháp
                </button>
                <button type="submit" class="qgrp-btn-save">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Lưu & Đưa vào Ngân hàng</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function scrollToSim() {
        var sim = document.getElementById('simSection');
        if (sim) {
            sim.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function submitAsDraft() {
        document.getElementById('actionTypeInput').value = 'draft';
        document.getElementById('questionGroupForm').submit();
    }

    function filterSubq(target, pill) {
        var pills = document.querySelectorAll('.qgrp-subq-pill');
        pills.forEach(function(p) { p.classList.remove('active'); });
        pill.classList.add('active');

        var items = document.querySelectorAll('.qgrp-qitem-box');
        items.forEach(function(item) {
            if (target === 'all') {
                item.style.display = '';
            } else {
                item.style.display = item.id === 'subq-' + target ? '' : 'none';
            }
        });
    }

    function triggerAiQuestions() {
        alert('Trợ lý StudyPREP AI: Đang phân tích 2 đoạn văn và hoàn thiện các câu hỏi trích dẫn logic theo chuẩn ETS 2024!');
    }

    function addNewSubQuestion() {
        alert('Tính năng thêm câu hỏi con thứ 6 (Triple Passage) đã sẵn sàng.');
    }
</script>
@endpush
