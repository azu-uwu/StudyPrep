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

    {{-- Thông báo kết quả sau khi Import / Chỉnh sửa / Xóa --}}
    @if (session('success'))
        <div class="alert-admin-success" style="display: flex; align-items: center; gap: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert-admin-error" style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
            <strong>Đã có lỗi xảy ra:</strong>
            <ul style="margin: 6px 0 0 18px; padding: 0;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1. HEADER --}}
    <div class="qimport-header" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 22px;">
        <div>
            <div class="qimport-title-row">
                <h1 class="qimport-page-title">Nhập câu hỏi TOEIC hàng loạt</h1>
                <span class="qimport-badge-code">#IMPORT-2025-04</span>
            </div>
            <div class="qimport-subtext">
                Tải lên tệp Word (.docx), CSV hoặc JSON -> Hệ thống tự động bóc tách và hiển thị danh sách câu hỏi để bạn Xem trước, Chỉnh sửa hoặc Xóa ngay bên dưới.
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('admin.questions.sample-docx') }}" class="btn-admin-action" style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 10px; background: #eef2ff; color: #4338ca; border: 1.5px solid #c7d2fe; font-weight: 700; font-size: 13.5px; text-decoration: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Tải file Word mẫu (.docx)</span>
            </a>

            <a href="{{ route('admin.questions.index') }}" class="btn-admin-action" style="text-decoration: none;">
                <span>&larr; Quay lại Ngân hàng câu hỏi</span>
            </a>
        </div>
    </div>

    {{-- 2. FORM TẢI LÊN FILE & CẤU HÌNH ĐÍCH ĐẾN (Đã bỏ thanh Stepper rườm rà) --}}
    <form action="{{ route('admin.questions.import') }}" method="POST" enctype="multipart/form-data" id="bulkImportForm">
        @csrf
        <input type="hidden" name="bulk_confirm" value="1">
        <input type="hidden" name="action_type" value="bulk_import">
        <input type="hidden" name="return_to_import_view" value="1">
        <input type="hidden" name="publish_status" id="publishStatusInput" value="live">

        <div class="qimport-top-grid" style="margin-bottom: 24px;">
            {{-- Cột trái: Chọn tệp Word (.docx) / CSV / JSON --}}
            <div class="qimport-card">
                <h2 class="qimport-card-title">
                    <span class="qimport-bar-accent"></span>
                    <span>Chọn tệp câu hỏi cần nhập (.docx, .csv, .json)</span>
                </h2>

                <div style="background: #f8fafc; border: 2px dashed #818cf8; border-radius: 14px; padding: 24px 20px; text-align: center; margin-bottom: 16px;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #eef2ff; color: #4338ca; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="12" y1="18" x2="12" y2="12"></line>
                            <line x1="9" y1="15" x2="15" y2="15"></line>
                        </svg>
                    </div>
                    <div style="font-size: 14.5px; font-weight: 700; color: #1e1b4b; margin-bottom: 6px;">
                        Tải lên tệp Word (.docx), CSV hoặc JSON của bạn
                    </div>
                    <div style="font-size: 12.5px; color: #64748b; margin-bottom: 14px;">
                        Hỗ trợ bóc tách tự động cả câu hỏi đơn và câu hỏi theo đoạn văn từ file Word
                    </div>
                    <input type="file" name="import_file" id="docxFileInput" accept=".docx,.csv,.txt,.json,.xlsx" class="form-control-admin" style="max-width: 420px; margin: 0 auto; background: #fff; padding: 9px 12px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 13.5px;" onchange="onWordFileSelected(this)">
                </div>

                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; font-size: 12.5px; color: #475569; line-height: 1.6;">
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 10px;">
                        <strong style="color: #0f172a;">Tải file Word mẫu &amp; Cấu trúc chuẩn 4 kỹ năng ETS:</strong>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <a href="{{ route('admin.questions.sample-docx', ['skill' => 'all']) }}" style="font-size: 11.5px; font-weight: 700; color: #4338ca; background: #eef2ff; border: 1px solid #c7d2fe; padding: 3px 9px; border-radius: 999px; text-decoration: none;">Full 4 kỹ năng</a>
                            <a href="{{ route('admin.questions.sample-docx', ['skill' => 'listening']) }}" style="font-size: 11.5px; font-weight: 700; color: #0369a1; background: #e0f2fe; border: 1px solid #bae6fd; padding: 3px 9px; border-radius: 999px; text-decoration: none;">Listening</a>
                            <a href="{{ route('admin.questions.sample-docx', ['skill' => 'reading']) }}" style="font-size: 11.5px; font-weight: 700; color: #047857; background: #d1fae5; border: 1px solid #a7f3d0; padding: 3px 9px; border-radius: 999px; text-decoration: none;">Reading</a>
                            <a href="{{ route('admin.questions.sample-docx', ['skill' => 'speaking']) }}" style="font-size: 11.5px; font-weight: 700; color: #b45309; background: #fef3c7; border: 1px solid #fde68a; padding: 3px 9px; border-radius: 999px; text-decoration: none;">Speaking</a>
                            <a href="{{ route('admin.questions.sample-docx', ['skill' => 'writing']) }}" style="font-size: 11.5px; font-weight: 700; color: #6d28d9; background: #ede9fe; border: 1px solid #ddd6fe; padding: 3px 9px; border-radius: 999px; text-decoration: none;">Writing</a>
                        </div>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                        <button type="button" class="tpl-skill-tab active" data-skill="listening" onclick="switchTemplateTab('listening', this)" style="padding: 4px 10px; border-radius: 7px; border: 1px solid #0284c7; background: #0284c7; color: #fff; font-size: 12px; font-weight: 700; cursor: pointer;">Listening (P1–P4)</button>
                        <button type="button" class="tpl-skill-tab" data-skill="reading" onclick="switchTemplateTab('reading', this)" style="padding: 4px 10px; border-radius: 7px; border: 1px solid #cbd5e1; background: #f8fafc; color: #334155; font-size: 12px; font-weight: 700; cursor: pointer;">Reading (P5–P7)</button>
                        <button type="button" class="tpl-skill-tab" data-skill="speaking" onclick="switchTemplateTab('speaking', this)" style="padding: 4px 10px; border-radius: 7px; border: 1px solid #cbd5e1; background: #f8fafc; color: #334155; font-size: 12px; font-weight: 700; cursor: pointer;">Speaking (T1–T5)</button>
                        <button type="button" class="tpl-skill-tab" data-skill="writing" onclick="switchTemplateTab('writing', this)" style="padding: 4px 10px; border-radius: 7px; border: 1px solid #cbd5e1; background: #f8fafc; color: #334155; font-size: 12px; font-weight: 700; cursor: pointer;">Writing (T1–T3)</button>
                    </div>

                    <div id="tpl-panel-listening" class="tpl-skill-panel" style="font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; background: #f8fafc; padding: 10px 12px; border-radius: 8px; line-height: 1.55;">
                        [Part 1] (Chèn ảnh ngay trên câu hỏi)<br>
                        Câu 1: Look at the picture and choose the correct statement.<br>
                        (A) A woman is typing on a laptop. &nbsp; (B) ... &nbsp; (C) ... &nbsp; (D) ...<br>
                        Đáp án: A | (Hỗ trợ bản dịch tiếng Việt hoặc dấu → bên dưới)<br>
                        [Part 2] — Tự động giới hạn đúng 3 phương án (A), (B), (C).<br>
                        [Part 3] / [Part 4] — Ghi Transcript: ... rồi liệt kê 3 câu hỏi cùng nhóm.
                    </div>
                    <div id="tpl-panel-reading" class="tpl-skill-panel" style="display: none; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; background: #f8fafc; padding: 10px 12px; border-radius: 8px; line-height: 1.55;">
                        [Part 5]<br>
                        Câu 101: Ms. Tanaka requested that all department heads submit ------- Friday.<br>
                        A. until &nbsp;&nbsp; B. before &nbsp;&nbsp; C. since &nbsp;&nbsp; D. toward<br>
                        Đáp án: B | Giải thích: Dùng giới từ before trước mốc thời gian.<br>
                        [Part 6] / [Part 7] — Ghi Đoạn văn: ... (nhiều dòng) rồi liệt kê các câu hỏi thuộc đoạn.
                    </div>
                    <div id="tpl-panel-speaking" class="tpl-skill-panel" style="display: none; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; background: #f8fafc; padding: 10px 12px; border-radius: 8px; line-height: 1.55;">
                        [Speaking Task 1: Read a Text Aloud] (Câu 1–2)<br>
                        Đoạn văn: Welcome to the annual Tech Innovation Expo...<br>
                        Câu 1: Read the announcement aloud clearly.<br>
                        [Speaking Task 2: Describe a Picture] (Chèn ảnh + Câu 3–4)<br>
                        [Speaking Task 3–5] — Ghi Bảng thông tin / Đề bài + Gợi ý trả lời (không tạo phương án A–D).
                    </div>
                    <div id="tpl-panel-writing" class="tpl-skill-panel" style="display: none; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; background: #f8fafc; padding: 10px 12px; border-radius: 8px; line-height: 1.55;">
                        [Writing Task 1: Write a Sentence Based on a Picture] (Chèn ảnh + 2 từ khóa)<br>
                        Câu 1: Write ONE sentence based on the picture using: (woman / laptop).<br>
                        [Writing Task 2: Respond to a Written Request] — Ghi Email: ... + Yêu cầu phản hồi.<br>
                        [Writing Task 3: Write an Opinion Essay] — Ghi Đề bài luận + Dàn ý / Bài mẫu.
                    </div>
                </div>
            </div>

            {{-- Cột phải: Cấu hình Đích đến & Nút Thực hiện Import --}}
            <div class="qimport-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h2 class="qimport-card-title">
                        <span class="qimport-bar-accent green"></span>
                        <span>Cấu hình Đích đến &amp; Xuất bản</span>
                    </h2>

                    <div class="qimport-dest-field">
                        <label class="qimport-dest-label">Gắn trực tiếp vào Đề thi (Tùy chọn)</label>
                        <select name="target_test_id" class="qimport-dest-select">
                            <option value="">- Chỉ lưu vào Ngân hàng câu hỏi chung -</option>
                            @if (isset($availableTests) && $availableTests->isNotEmpty())
                                @foreach ($availableTests as $testOption)
                                    <option value="{{ $testOption->test_form_id }}">
                                        [{{ str_replace(['—', '–'], '-', $testOption->test_code) }}] {{ $testOption->title }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="qimport-dest-field">
                        <label class="qimport-dest-label">Chế độ &amp; Kỹ năng nhận diện</label>
                        <select name="import_scope" class="qimport-dest-select">
                            @include('admin.questions.partials.import-scope-options')
                        </select>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 5px; line-height: 1.45;">
                            Mặc định <strong>AUTO</strong> tự động nhận diện đủ 4 kỹ năng &amp; 15 Part/Task TOEIC ETS theo tiêu đề trong file. Chọn nhóm cố định nếu file không ghi tiêu đề Part.
                        </div>
                    </div>

                    <div class="qimport-dest-field">
                        <label class="qimport-dest-label">Trạng thái câu hỏi sau khi nhập</label>
                        <div class="qimport-radio-wrap">
                            <label class="qimport-radio-card active" id="radioCardLive">
                                <input type="radio" name="publish_mode" value="live" checked onchange="switchPublishMode('live')">
                                <div class="qimport-radio-text">
                                    <span class="qimport-radio-title">Xuất bản ngay (ACTIVE)</span>
                                    <span class="qimport-radio-desc">Được tính ngay vào tiến độ hoàn thiện Đề thi (/admin/tests) và sẵn sàng cho học viên</span>
                                </div>
                            </label>

                            <label class="qimport-radio-card" id="radioCardDraft">
                                <input type="radio" name="publish_mode" value="draft" onchange="switchPublishMode('draft')">
                                <div class="qimport-radio-text">
                                    <span class="qimport-radio-title">Lưu bản nháp (DRAFT)</span>
                                    <span class="qimport-radio-desc">Chờ rà soát/duyệt lại — chưa được tính vào chỉ tiêu hoàn thiện đề thi cho đến khi duyệt sang ACTIVE</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div style="padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 10px;">
                    <button type="submit"
                            formaction="{{ route('admin.questions.import.preview') }}"
                            class="qimport-btn-submit"
                            id="btnPreviewStaging"
                            style="width: 100%; justify-content: center; padding: 12px 20px; font-size: 14.5px; background: linear-gradient(135deg, #4338ca, #6366f1);">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                            <line x1="12" y1="3" x2="12" y2="21"></line>
                        </svg>
                        <span>Phân tích &amp; Đối chiếu (Split-View + XML)</span>
                    </button>

                    <button type="submit" class="btn-admin-action" id="btnSubmitBulk" style="width: 100%; justify-content: center; padding: 10px 16px; font-size: 13.5px; font-weight: 700;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span id="btnSubmitText">Tải lên &amp; Nhập câu hỏi</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- 3. DANH SÁCH CÂU HỎI VỪA IMPORT (CHỈ HIỂN THỊ SAU KHI IMPORT XONG ĐỂ XEM TRƯỚC, CHỈNH SỬA, XÓA) --}}
    @if (isset($importedQuestions) && $importedQuestions->isNotEmpty())
        <div class="user-table-card" style="margin-top: 8px;">
            <div style="padding: 18px 22px; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
                <div>
                    <h2 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0;">
                        Danh sách câu hỏi vừa Import ({{ $importedQuestions->count() }} câu)
                    </h2>
                    <p style="font-size: 13px; color: #64748b; margin: 4px 0 0 0;">
                        Bạn có thể <strong>Xem trước</strong>, <strong>Chỉnh sửa</strong> lại nội dung/đáp án hoặc <strong>Xóa</strong> trực tiếp bất kỳ câu hỏi nào bên dưới.
                    </p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('admin.questions.index') }}" class="btn-admin-primary" style="text-decoration: none;">
                        <span>Hoàn tất &amp; Xem trong Ngân hàng câu hỏi &rarr;</span>
                    </a>
                </div>
            </div>

            <div class="user-table-scroll">
                <table class="user-data-table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">MÃ ID &amp; PART</th>
                            <th>NỘI DUNG CÂU HỎI &amp; ĐÁP ÁN</th>
                            <th style="width: 150px;">KỸ NĂNG</th>
                            <th style="width: 120px;">ĐỘ KHÓ</th>
                            <th style="width: 130px;">TRẠNG THÁI</th>
                            <th class="admin-actions-heading" style="width: 120px;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($importedQuestions as $q)
                            @php
                                $options = $q->options;
                                $isConstructive = in_array($q->skill_key, ['speaking', 'writing'], true);
                                $groupCount = $q->group_questions_count;
                                $hasImageMedia = $q->questionSet?->resolved_media_url && (strtolower((string) $q->questionSet->media_type) === 'image' || preg_match('/\.(png|jpe?g|gif|webp|bmp)$/i', $q->questionSet->resolved_media_url));
                            @endphp
                            <tr>
                                <td>
                                    <div class="q-id-text">{{ $q->question_code_display }}</div>
                                    <div class="q-badges-row" style="flex-wrap: wrap; gap: 4px;">
                                        <span class="q-part-pill">{{ $q->part_badge }}</span>
                                        @if ($groupCount > 1)
                                            <span style="display: inline-flex; align-items: center; padding: 2px 7px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">
                                                Cụm {{ $groupCount }} câu
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="q-content-text">{{ $q->content }}</div>
                                    @if ($q->questionSet?->resolved_media_url)
                                        @if ($hasImageMedia)
                                            <img src="{{ $q->questionSet->resolved_media_url }}" alt="Question media" style="max-width: 180px; max-height: 100px; margin-top: 8px; border-radius: 8px; border: 1px solid #e2e8f0; object-fit: contain; display: block;">
                                        @else
                                            <audio controls preload="none" src="{{ $q->questionSet->resolved_media_url }}" style="max-width: 240px; height: 32px; margin-top: 8px; display: block;"></audio>
                                        @endif
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
                                            {{ \Illuminate\Support\Str::limit($q->explanation, 180) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a; font-size: 13.5px;">
                                        {{ $q->skill_display }}
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 12px; font-weight: 700; color: #475569;">{{ $q->difficulty }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $q->status_badge_class }}">
                                        <span class="status-dot"></span>
                                        {{ $q->status_label }}
                                    </span>
                                </td>
                                <td class="admin-actions-cell">
                                    <div class="user-actions-cell">
                                        {{-- Nút Xem trước mô phỏng đề thi thật --}}
                                        <button type="button"
                                                class="action-icon-btn"
                                                title="Xem trước mô phỏng đề thi"
                                                onclick='openPreviewModal(@json($q->toPreviewPayload()))'>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>

                                        {{-- Nút Chỉnh sửa (Kế thừa từ Ngân hàng câu hỏi) --}}
                                        <button type="button"
                                                class="action-icon-btn"
                                                title="Chỉnh sửa câu hỏi"
                                                onclick='openEditQuestionModal(@json($q))'>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                            </svg>
                                        </button>

                                        {{-- Nút Xóa câu hỏi --}}
                                        <button type="button"
                                                class="action-icon-btn danger"
                                                title="Xóa câu hỏi này"
                                                onclick="if (confirm('Bạn có chắc chắn muốn xóa câu hỏi {{ $q->question_code_display }}?')) { deleteImportedQuestion('{{ route('admin.questions.destroy', $q->question_id) }}'); }">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

{{-- Form ẩn xóa câu hỏi vừa import --}}
<form id="delete-imported-q-form" method="POST" action="" style="display: none;">
    @csrf
    @method('DELETE')
    <input type="hidden" name="redirect_back" value="1">
</form>

{{-- MODAL CHỈNH SỬA CÂU HỎI (Kế thừa từ Ngân hàng câu hỏi) --}}
<div id="modal-edit-question" class="admin-modal-backdrop">
    <div class="admin-modal-box" style="max-width: 600px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="edit-modal-title">Cập nhật câu hỏi</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-edit-question')">&times;</button>
        </div>
        <form id="form-edit-question" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_back" value="1">
            <div class="admin-modal-body">
                <div class="form-group-admin">
                    <label>Phần thi (Part / Task)</label>
                    <select id="edit_section_id" name="section_id" class="form-control-admin" required>
                        @foreach ($sections as $sec)
                            <option value="{{ $sec->section_id }}">{{ str_replace(['—', '–'], '-', $sec->name) }}</option>
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
                            <option value="MEDIUM">Trung bình (500 - 750)</option>
                            <option value="HARD">Nâng cao (750+)</option>
                        </select>
                    </div>
                    <div class="form-group-admin">
                        <label>Trạng thái</label>
                        <select id="edit_status" name="status" class="form-control-admin" required>
                            <option value="ACTIVE">Hoạt động (Active)</option>
                            <option value="PENDING">Chờ duyệt (Pending)</option>
                            <option value="DRAFT">Bản nháp (Draft)</option>
                        </select>
                    </div>
                </div>

                <div style="border-top: 1px solid #edf0f7; padding-top: 12px; margin-top: 8px; margin-bottom: 12px;">
                    <label style="font-weight: 700; font-size: 13px; margin-bottom: 8px; display: block;">4 Phương án trắc nghiệm (A, B, C, D):</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <input type="text" name="opt_a" id="edit_opt_a" class="form-control-admin" placeholder="Phương án A">
                        <input type="text" name="opt_b" id="edit_opt_b" class="form-control-admin" placeholder="Phương án B">
                        <input type="text" name="opt_c" id="edit_opt_c" class="form-control-admin" placeholder="Phương án C">
                        <input type="text" name="opt_d" id="edit_opt_d" class="form-control-admin" placeholder="Phương án D">
                    </div>
                    <div class="form-group-admin" style="margin-top: 10px; margin-bottom: 0;">
                        <label>Đáp án đúng:</label>
                        <select name="correct_option" id="edit_correct_option" class="form-control-admin">
                            <option value="A">Phương án A</option>
                            <option value="B">Phương án B</option>
                            <option value="C">Phương án C</option>
                            <option value="D">Phương án D</option>
                        </select>
                    </div>
                </div>

                <div class="form-group-admin">
                    <label>Giải thích &amp; Đáp án chuẩn</label>
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

{{-- MODAL XEM TRƯỚC MÔ PHỎNG ĐỀ THI TOEIC THẬT --}}
@include('admin.questions.partials.preview-modal')
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

    function switchTemplateTab(skill, btn) {
        document.querySelectorAll('.tpl-skill-panel').forEach(function (panel) {
            panel.style.display = 'none';
        });
        var target = document.getElementById('tpl-panel-' + skill);
        if (target) target.style.display = 'block';

        var colorMap = {
            listening: '#0284c7',
            reading: '#059669',
            speaking: '#d97706',
            writing: '#7c3aed'
        };
        document.querySelectorAll('.tpl-skill-tab').forEach(function (tab) {
            tab.style.background = '#f8fafc';
            tab.style.color = '#334155';
            tab.style.borderColor = '#cbd5e1';
        });
        if (btn) {
            var activeColor = colorMap[skill] || '#4f46e5';
            btn.style.background = activeColor;
            btn.style.color = '#ffffff';
            btn.style.borderColor = activeColor;
        }
    }

    function switchPublishMode(mode) {
        document.getElementById('publishStatusInput').value = mode;
        var cardDraft = document.getElementById('radioCardDraft');
        var cardLive = document.getElementById('radioCardLive');
        if (cardDraft && cardLive) {
            cardDraft.classList.toggle('active', mode === 'draft');
            cardLive.classList.toggle('active', mode === 'live');
        }
    }

    function onWordFileSelected(input) {
        if (input.files && input.files[0]) {
            var fileName = input.files[0].name;
            var btnText = document.getElementById('btnSubmitText');
            if (btnText) {
                btnText.textContent = 'Nhập câu hỏi từ tệp ' + fileName;
            }
        }
    }

    function openEditQuestionModal(q) {
        var form = document.getElementById('form-edit-question');
        form.action = '{{ url('/admin/questions') }}/' + q.question_id;
        document.getElementById('edit-modal-title').innerText = 'Cập nhật câu hỏi #Q-' + q.question_id;
        document.getElementById('edit_section_id').value = q.section_id || '5';
        document.getElementById('edit_content').value = q.content || '';
        document.getElementById('edit_difficulty').value = q.difficulty || 'MEDIUM';
        document.getElementById('edit_status').value = q.status || 'ACTIVE';
        document.getElementById('edit_explanation').value = q.explanation || '';

        var optMap = { A: '', B: '', C: '', D: '' };
        var correctOpt = 'B';
        if (Array.isArray(q.options)) {
            q.options.forEach(function(o) {
                if (o.label && optMap.hasOwnProperty(o.label)) {
                    optMap[o.label] = o.content || '';
                    if (o.is_correct) correctOpt = o.label;
                }
            });
        }
        document.getElementById('edit_opt_a').value = optMap.A;
        document.getElementById('edit_opt_b').value = optMap.B;
        document.getElementById('edit_opt_c').value = optMap.C;
        document.getElementById('edit_opt_d').value = optMap.D;
        document.getElementById('edit_correct_option').value = correctOpt;

        openAdminModal('modal-edit-question');
    }

    function deleteImportedQuestion(actionUrl) {
        var form = document.getElementById('delete-imported-q-form');
        form.action = actionUrl;
        form.submit();
    }
</script>
@endpush
