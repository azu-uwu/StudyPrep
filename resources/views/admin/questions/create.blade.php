@extends('layouts.admin', ['active' => 'ngan-hang-cau-hoi'])

@section('title', 'Thêm câu hỏi TOEIC mới')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    @if (!empty($testContext))
        <a href="{{ route('admin.tests.index') }}" style="color: inherit; text-decoration: none;">Quản lý đề thi</a>
        <span class="crumb-arrow">></span>
        <a href="{{ route('admin.tests.show', $testContext['test_id']) }}" style="color: inherit; text-decoration: none;">{{ $testContext['test_title'] }}</a>
        <span class="crumb-arrow">></span>
        <span style="color: var(--admin-primary); font-weight: 600;">Thêm câu hỏi vào đề</span>
    @else
        <a href="{{ route('admin.questions.index') }}" style="color: inherit; text-decoration: none;">Ngân hàng câu hỏi</a>
        <span class="crumb-arrow">></span>
        <span style="color: var(--admin-primary); font-weight: 600;">Thêm câu hỏi TOEIC mới</span>
    @endif
@endsection

@section('content')
<div class="qcreate-container">
    {{-- Form bao toàn bộ trang (hỗ trợ upload Ảnh/Audio với multipart/form-data) --}}
    <form action="{{ route('admin.questions.store') }}" method="POST" enctype="multipart/form-data" id="createQuestionForm">
        @csrf
        <input type="hidden" name="correct_option" id="correctOptionInput" value="{{ old('correct_option', 'A') }}">
        <input type="hidden" name="points" value="{{ old('points', '1.00') }}">
        <input type="hidden" name="audio_url" id="generatedAudioUrlInput" value="{{ old('audio_url', '') }}">
        @if (!empty($testContext))
            <input type="hidden" name="test_id" value="{{ $testContext['test_id'] }}">
        @endif

        {{-- Header & Top Action Buttons --}}
        <div class="qcreate-header">
            <div class="qcreate-title-wrap">
                <h1 class="qcreate-page-title">
                    @if (!empty($testContext))
                        Thêm câu hỏi vào đề: {{ $testContext['test_title'] }}
                    @else
                        Thêm câu hỏi TOEIC mới
                    @endif
                </h1>
            </div>
            <div class="qcreate-header-actions">
                @if (!empty($testContext))
                    <a href="{{ route('admin.tests.show', $testContext['test_id']) }}" class="qcreate-btn-top" style="text-decoration: none;" title="Quay lại đề thi">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Quay lại đề thi</span>
                    </a>
                @else
                    <a href="{{ route('admin.questions.index') }}" class="qcreate-btn-top" style="text-decoration: none;" title="Quay lại Ngân hàng câu hỏi">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Danh sách câu hỏi</span>
                    </a>
                @endif
                <button type="button" class="qcreate-btn-top" onclick="fillTemplateSampleForCurrentPart()" title="Điền nhanh nội dung mẫu chuẩn theo Part đang chọn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    <span>Điền mẫu Part này</span>
                </button>
                <button type="button" class="qcreate-btn-top" onclick="scrollToPreview()" title="Xem trước trên màn hình học viên">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>Xem trước</span>
                </button>
            </div>
        </div>

        {{-- Thông báo lỗi validation nếu có --}}
        @if (isset($errors) && $errors->any())
            <div style="background-color: #fee2e2; border: 1px solid #fecaca; color: #be123c; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
                <div style="font-weight: 700; margin-bottom: 4px;">Có lỗi xảy ra khi lưu câu hỏi:</div>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Banner ngữ cảnh nếu thêm từ Đề thi cụ thể --}}
        @if (!empty($testContext))
            <div style="background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%); border: 1.5px solid #c7d2fe; border-radius: 14px; padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 2px 8px rgba(79, 70, 229, 0.06);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: #4338ca; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: #4338ca; text-transform: uppercase; letter-spacing: 0.5px;">Ngữ cảnh đề thi đang thao tác</div>
                        <div style="font-size: 14.5px; font-weight: 700; color: #1e1b4b; margin-top: 2px;">
                            Đề thi: <span style="color: #4f46e5;">{{ $testContext['test_title'] }}</span> ({{ $testContext['test_code'] }})
                            @if (!empty($testContext['section_name']))
                                <span style="color: #94a3b8; margin: 0 6px;">&bull;</span>
                                Phần thi mục tiêu: <span style="color: #0f172a;">{{ $testContext['section_name'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div style="font-size: 13px; color: #64748b; background: #ffffff; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    Chế độ: <strong>Tự động gắn vào đề thi</strong>
                </div>
            </div>
        @endif

        {{-- TOP CARD: Cấu hình thông tin danh mục & Chuẩn kiểm định --}}
        <div class="qcreate-card">
            <div class="qcreate-card-header">
                <div class="qcreate-card-title">
                    <span class="icon-badge">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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
                    </span>
                    <span>Cấu hình thông tin danh mục & Chuẩn kiểm định</span>
                </div>
                <p class="qcreate-card-subtitle" id="partRuleBadge">Form tự động thay đổi theo đặc thù từng Part TOEIC</p>
            </div>

            <div class="qcreate-config-grid">
                {{-- Kỹ năng (4 kỹ năng TOEIC độc lập: Listening, Reading, Speaking, Writing) --}}
                <div class="qcreate-field-group">
                    <label class="qcreate-field-label">
                        Kỹ năng (Skill) <span class="required-mark">*</span>
                    </label>
                    @php
                        $resolveSkillKey = static function (string $secCode): string {
                            $upper = strtoupper($secCode);
                            if (in_array($upper, ['PART1', 'PART2', 'PART3', 'PART4'], true)) {
                                return 'listening';
                            }
                            if (in_array($upper, ['PART5', 'PART6', 'PART7'], true)) {
                                return 'reading';
                            }
                            if (str_starts_with($upper, 'SPK_')) {
                                return 'speaking';
                            }
                            if (str_starts_with($upper, 'WRITING_')) {
                                return 'writing';
                            }
                            return 'reading';
                        };

                        $selectedSecId = old('section_id', $testContext['section_id'] ?? null);
                        $defaultSecObj = $selectedSecId
                            ? $sections->firstWhere('section_id', (int) $selectedSecId)
                            : ($sections->firstWhere('code', 'PART5') ?? $sections->first());
                        $defaultSkillKey = old('skill_key', $defaultSecObj ? $resolveSkillKey((string) $defaultSecObj->code) : 'reading');

                        $fourSkills = [
                            'listening' => ['label' => 'Listening (Nghe - Part 1–4)', 'code' => 'LC'],
                            'reading' => ['label' => 'Reading (Đọc - Part 5–7)', 'code' => 'RC'],
                            'speaking' => ['label' => 'Speaking (Nói - Task 1–5)', 'code' => 'SPK'],
                            'writing' => ['label' => 'Writing (Viết - Task 1–3)', 'code' => 'WRT'],
                        ];
                        $availableSkillKeys = $sections->map(fn ($s) => $resolveSkillKey((string) $s->code))->unique()->all();
                    @endphp
                    <select name="skill_key" id="skillSelect" class="qcreate-input-control" onchange="syncSkillPart(false)">
                        @foreach ($fourSkills as $sKey => $sMeta)
                            @if (empty($availableSkillKeys) || in_array($sKey, $availableSkillKeys, true))
                                <option value="{{ $sKey }}" data-code="{{ $sMeta['code'] }}" {{ $defaultSkillKey === $sKey ? 'selected' : '' }}>
                                    {{ $sMeta['label'] }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- Phần thi (TOEIC Part) --}}
                <div class="qcreate-field-group">
                    <label class="qcreate-field-label">
                        Phần thi (TOEIC Part) <span class="required-mark">*</span>
                    </label>
                    <select name="section_id" id="sectionSelect" class="qcreate-input-control" required onchange="applyPartTemplate(true)">
                        @foreach ($sections as $sec)
                            @php
                                $secCode = strtoupper((string) $sec->code);
                                $secSkillKey = $resolveSkillKey($secCode);
                                $secLabel = str_replace(['—', '–'], '-', (string) ($sec->name ?? $sec->code));
                                $isDefault = $selectedSecId
                                    ? ((int) $selectedSecId === (int) $sec->section_id)
                                    : ($secCode === 'PART5');
                            @endphp
                            <option value="{{ $sec->section_id }}"
                                    data-skill-id="{{ $sec->skill_id }}"
                                    data-skill-key="{{ $secSkillKey }}"
                                    data-code="{{ $secCode }}"
                                    {{ $isDefault ? 'selected' : '' }}>
                                {{ $secLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Mức độ khó --}}
                <div class="qcreate-field-group">
                    <label class="qcreate-field-label">Độ khó (Target TOEIC)</label>
                    <select name="difficulty" id="difficultySelect" class="qcreate-input-control">
                        <option value="UNSET" {{ old('difficulty') === 'UNSET' ? 'selected' : '' }}>Tự động (AI đánh giá theo chuẩn TOEIC)</option>
                        <option value="EASY" {{ old('difficulty') === 'EASY' ? 'selected' : '' }}>EASY - Cơ bản (&lt; 500)</option>
                        <option value="MEDIUM" {{ old('difficulty', 'MEDIUM') === 'MEDIUM' ? 'selected' : '' }}>MEDIUM - Trung bình (500 - 750)</option>
                        <option value="HARD" {{ old('difficulty') === 'HARD' ? 'selected' : '' }}>HARD - Nâng cao (750+)</option>
                    </select>
                </div>

                {{-- Số thứ tự trong đề --}}
                <div class="qcreate-field-group">
                    <label class="qcreate-field-label">Số thứ tự trong đề</label>
                    <div class="qcreate-input-icon-wrap has-prefix">
                        <span class="qcreate-icon-prefix">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                        </span>
                        <input type="number" name="sequence_no" id="sequenceNoInput" class="qcreate-input-control" value="{{ old('sequence_no', 101) }}" min="1" max="200" oninput="updateSimHeader()">
                        <span class="qcreate-badge-suffix" id="partRangeBadge">101-130</span>
                    </div>
                </div>
            </div>

            {{-- Hướng dẫn cấu trúc chuẩn của Part đang chọn --}}
            <div id="partGuideBanner" style="margin-top: 14px; padding: 11px 14px; border-radius: 10px; background: #eef2ff; border: 1px solid #c7d2fe; color: #312e81; font-size: 13px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                <span id="partGuideText">Đang áp dụng cấu trúc Part 5: Incomplete Sentences (Điền từ vào chỗ trống, 4 đáp án A–D).</span>
                <span id="partFormatTag" style="font-size: 11.5px; font-weight: 700; padding: 3px 9px; border-radius: 999px; background: #ffffff; color: #4338ca; border: 1px solid #c7d2fe;">4 phương án A–D</span>
            </div>
        </div>

        {{-- MAIN 2-COLUMN SECTION --}}
        <div class="qcreate-main-grid">
            {{-- CỘT TRÁI: Form nhập liệu động theo từng Part --}}
            <div class="qcreate-editor-column">

                {{-- CARD 1: Nội dung đề bài & Tài nguyên đa phương tiện theo Part --}}
                <div class="qcreate-card">
                    <div class="qcreate-card-header">
                        <div class="qcreate-section-title">
                            <span class="qcreate-step-dot"></span>
                            <span id="card1Title">1. Nội dung câu hỏi &amp; Ngữ cảnh</span>
                        </div>
                    </div>

                    {{-- PANEL A: ĐOẠN VĂN / EMAIL / TÀI LIỆU (PART 6, 7, SPK_T1, SPK_T4, WRITING_T2) --}}
                    <div id="panelStimulus" style="display: none; margin-bottom: 16px; padding: 14px 16px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px;">
                        <label class="qcreate-field-label" id="stimulusLabel" style="margin-bottom: 6px; display: block;">
                            Đoạn văn / Email / Thông báo (Stimulus Text)
                        </label>
                        <textarea name="stimulus_text" id="stimulusInput" rows="5" class="qcreate-input-control" style="width: 100%; height: auto; padding: 10px 12px; font-size: 13.5px; line-height: 1.55;" placeholder="Nhập nội dung đoạn văn, email, bài báo hoặc bảng lịch trình..." oninput="syncSimulator()">{{ old('stimulus_text', '') }}</textarea>
                    </div>

                    {{-- PANEL B: HÌNH ẢNH MINH HỌA / TRANH / BIỂU ĐỒ (PART 1, 3, 4, 7, SPK_T2, WRITING_T1) --}}
                    <div id="panelImage" style="display: none; margin-bottom: 16px; padding: 14px 16px; background: #f8fafc; border: 1px dashed #818cf8; border-radius: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; gap: 10px; flex-wrap: wrap;">
                            <label class="qcreate-field-label" id="imagePanelLabel" style="margin: 0;">
                                Hình ảnh minh họa / Bức tranh / Biểu đồ (JPG, PNG, WEBP)
                            </label>
                            <button type="button" id="btnClearImage" onclick="clearSelectedImage()" style="display: none; font-size: 12px; color: #dc2626; background: none; border: none; cursor: pointer; font-weight: 700;">Gỡ ảnh</button>
                        </div>
                        <input type="file" name="image_file" id="imageFileInput" accept="image/png,image/jpeg,image/gif,image/webp" class="qcreate-input-control" style="padding: 8px 10px; background: #fff;" onchange="handleImageUploadChange(this)">
                        <div id="imageUploadPreviewBox" style="display: none; margin-top: 10px; text-align: center;">
                            <img id="imageUploadPreview" src="" alt="Preview" style="max-height: 190px; max-width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; object-fit: contain;">
                        </div>
                    </div>

                    {{-- PANEL C: AUDIO & TRANSCRIPT + SINH TTS (PART 1, 2, 3, 4) --}}
                    <div id="panelAudio" style="display: none; margin-bottom: 16px; padding: 14px 16px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; gap: 8px; flex-wrap: wrap;">
                            <label class="qcreate-field-label" style="margin: 0; color: #0369a1;">
                                Âm thanh Listening (Upload Audio hoặc Sinh giọng đọc TTS)
                            </label>
                            <button type="button" id="btnGenerateTts" class="btn-admin-action" style="padding: 5px 12px; font-size: 12px; font-weight: 700; color: #0369a1; border-color: #7dd3fc; background: #ffffff; display: inline-flex; align-items: center; gap: 5px;" onclick="generateTtsAudio()">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                    <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                                </svg>
                                <span id="btnGenerateTtsLabel">Sinh giọng đọc TTS</span>
                            </button>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <input type="file" name="audio_file" id="audioFileInput" accept="audio/mpeg,audio/wav,audio/ogg,audio/mp4,audio/webm" class="qcreate-input-control" style="padding: 8px 10px; background: #fff;" onchange="handleAudioUploadChange(this)">
                        </div>

                        <div id="audioPlayerPreviewWrap" style="display: none; margin-bottom: 10px; padding: 8px 10px; background: #ffffff; border-radius: 8px; border: 1px solid #bae6fd;">
                            <div id="audioStatusLabel" style="font-size: 12px; font-weight: 700; color: #0369a1; margin-bottom: 5px;">Tệp âm thanh đã sẵn sàng</div>
                            <audio id="audioPreviewPlayer" controls style="width: 100%; height: 34px;"></audio>
                        </div>

                        <div>
                            <label class="qcreate-field-label" style="margin-bottom: 4px; display: block;">
                                Transcript / Lời thoại bài nghe (dùng để sinh giọng đọc TTS &amp; hiện sau khi nộp bài)
                            </label>
                            <textarea name="transcript" id="transcriptInput" rows="3" class="qcreate-input-control" style="width: 100%; height: auto; padding: 9px 12px; font-size: 13px; background: #ffffff;" placeholder="Nhập lời thoại tiếng Anh để sinh audio tự động bằng nút 'Sinh giọng đọc TTS'..." oninput="syncSimulator()">{{ old('transcript', '') }}</textarea>
                        </div>
                    </div>

                    {{-- Nội dung câu hỏi / Đề bài chính --}}
                    <div class="qcreate-field-group" style="margin-bottom: 8px;">
                        <label class="qcreate-field-label" id="contentLabel">
                            Nội dung câu hỏi / Yêu cầu đề bài <span class="required-mark">*</span>
                        </label>
                    </div>

                    <div class="qcreate-editor-wrap">
                        <div class="qcreate-editor-toolbar">
                            <div class="qcreate-toolbar-left">
                                <button type="button" class="qcreate-tool-btn" onclick="wrapText('contentInput', '**', '**')" title="In đậm (Bold)">B</button>
                                <button type="button" class="qcreate-tool-btn" onclick="wrapText('contentInput', '*', '*')" title="In nghiêng (Italic)"><em>I</em></button>
                                <button type="button" id="btnInsertBlank" class="qcreate-tool-btn btn-insert-blank" onclick="insertBlank('contentInput')" title="Chèn chỗ trống ------- tại con trỏ">
                                    ␣ Chèn chỗ trống (-------)
                                </button>
                            </div>
                            <div class="qcreate-toolbar-right" id="contentCounter">
                                Độ dài: 0 từ / 0 ký tự
                            </div>
                        </div>

                        <textarea name="content" id="contentInput" class="qcreate-textarea" rows="4" required placeholder="Nhập nội dung câu hỏi tiếng Anh tại đây..." oninput="handleContentInput()">{{ old('content', '') }}</textarea>
                    </div>
                </div>

                {{-- CARD 2: Các phương án trắc nghiệm (Tự ẩn với Speaking/Writing, tự ẩn D với Part 2) --}}
                <div class="qcreate-card" id="mcqOptionsCard">
                    <div class="qcreate-card-header">
                        <div class="qcreate-section-title">
                            <span class="qcreate-step-dot"></span>
                            <span id="mcqCardTitle">2. Thiết lập 4 phương án lựa chọn (A - B - C - D)</span>
                        </div>
                        <span id="mcqOptionalHint" style="display: none; font-size: 12px; color: #64748b;">Part 1 cho phép để trống text phương án nếu chỉ dùng audio</span>
                    </div>

                    <div class="qcreate-options-list">
                        @foreach (['A', 'B', 'C', 'D'] as $letter)
                            @php
                                $fieldName = 'opt_'.strtolower($letter);
                                $isSelectedOpt = strtoupper((string) old('correct_option', 'A')) === $letter;
                            @endphp
                            <div class="qcreate-opt-row {{ $isSelectedOpt ? 'is-correct' : '' }}" id="optRow{{ $letter }}">
                                <div class="qcreate-opt-letter">{{ $letter }}</div>
                                <input type="text"
                                       name="{{ $fieldName }}"
                                       id="optInput{{ $letter }}"
                                       class="qcreate-opt-text-input"
                                       value="{{ old($fieldName, '') }}"
                                       placeholder="Nội dung phương án {{ $letter }}"
                                       oninput="syncSimChoices()">
                                <button type="button" class="qcreate-opt-select-btn" onclick="selectCorrectOption('{{ $letter }}')">
                                    <span class="qcreate-opt-radio-icon"></span>
                                    <svg class="qcreate-opt-check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    <span class="opt-btn-text">{{ $isSelectedOpt ? 'ĐÁP ÁN ĐÚNG' : 'Chọn đúng' }}</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- CARD 3: Giải thích đáp án / Bài mẫu gợi ý --}}
                <div class="qcreate-card">
                    <div class="qcreate-card-header">
                        <div class="qcreate-section-title">
                            <span class="qcreate-step-dot"></span>
                            <span id="explanationCardTitle">3. Giải thích đáp án &amp; Bản dịch song ngữ</span>
                        </div>
                    </div>

                    <div class="qcreate-editor-wrap">
                        <div class="qcreate-editor-toolbar">
                            <div class="qcreate-toolbar-left">
                                <button type="button" class="qcreate-tool-btn" onclick="wrapText('explanationInput', '**', '**')" title="In đậm">B</button>
                                <button type="button" class="qcreate-tool-btn" onclick="wrapText('explanationInput', '*', '*')" title="In nghiêng"><em>I</em></button>
                            </div>
                            <div class="qcreate-toolbar-right" id="explanationCounter">
                                Độ dài: 0 từ / 0 ký tự
                            </div>
                        </div>

                        <textarea name="explanation" id="explanationInput" class="qcreate-textarea" rows="4" placeholder="Nhập lời giải thích ngữ pháp, bản dịch tiếng Việt hoặc bài nói/viết mẫu tham khảo..." oninput="handleExplanationInput()">{{ old('explanation', '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- CỘT PHẢI: Mô phỏng giao diện học viên (Student Live Simulator) --}}
            <div class="qcreate-preview-column" id="previewSimulatorSection">
                <div class="qcreate-sim-card">
                    <div class="qcreate-card-header" style="margin-bottom: 0;">
                        <div class="qcreate-card-title">
                            <span class="icon-badge">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                    <line x1="8" y1="21" x2="16" y2="21"></line>
                                    <line x1="12" y1="17" x2="12" y2="21"></line>
                                </svg>
                            </span>
                            <span>Mô phỏng giao diện học viên</span>
                        </div>
                        <span class="qcreate-card-subtitle">Giao diện thi thực tế</span>
                    </div>

                    <div class="qcreate-sim-box">
                        {{-- Header câu hỏi & đồng hồ --}}
                        <div class="qcreate-sim-header">
                            <span id="simQuestionMeta">Câu 101 / Part 5</span>
                            <div class="qcreate-sim-timer">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span id="simTimerText">00:45</span>
                            </div>
                        </div>

                        {{-- Audio Simulator (cho Listening) --}}
                        <div id="simAudioBox" style="display: none; margin-bottom: 12px; padding: 10px 12px; background: #0f172a; border-radius: 10px; color: #f8fafc;">
                            <div style="font-size: 11.5px; color: #38bdf8; font-weight: 700; margin-bottom: 6px;">AUDIO LISTENING</div>
                            <audio id="simAudioPlayer" controls style="width: 100%; height: 32px; display: none;"></audio>
                            <div id="simAudioEmptyText" style="font-size: 12px; color: #94a3b8;">Chưa có file âm thanh — hãy tải file MP3 hoặc nhấn "Sinh giọng đọc TTS".</div>
                        </div>

                        {{-- Image Simulator --}}
                        <div id="simImageBox" style="display: none; margin-bottom: 12px; text-align: center;">
                            <img id="simImagePreview" src="" alt="Hình ảnh câu hỏi" style="max-width: 100%; max-height: 180px; border-radius: 8px; border: 1px solid #e2e8f0; object-fit: contain;">
                        </div>

                        {{-- Passage / Stimulus Simulator --}}
                        <div id="simStimulusBox" style="display: none; padding: 10px 12px; background: #f8fafc; border-left: 3px solid #4f46e5; border-radius: 6px; margin-bottom: 12px; font-size: 12.5px; color: #334155; white-space: pre-line; max-height: 140px; overflow-y: auto;"></div>

                        {{-- Nội dung câu hỏi mô phỏng --}}
                        <div class="qcreate-sim-content" id="simQuestionBody">
                            <em>(Nhập nội dung câu hỏi ở cột bên trái để xem trước...)</em>
                        </div>

                        {{-- Các phương án trắc nghiệm mô phỏng --}}
                        <div class="qcreate-sim-choices" id="simChoicesContainer">
                            @foreach (['A', 'B', 'C', 'D'] as $letter)
                                <div class="qcreate-sim-choice-pill {{ strtoupper((string) old('correct_option', 'A')) === $letter ? 'active' : '' }}" id="simChoice{{ $letter }}" onclick="selectCorrectOption('{{ $letter }}')">
                                    <span class="sim-choice-text" id="simChoiceText{{ $letter }}">({{ $letter }}) ...</span>
                                    <span class="sim-radio"></span>
                                    <svg class="sim-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>
                            @endforeach
                        </div>

                        {{-- Mô phỏng khung Speaking --}}
                        <div id="simSpeakingBox" style="display: none; padding: 14px; background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; text-align: center; margin-top: 10px;">
                            <div style="font-size: 13px; font-weight: 700; color: #92400e; margin-bottom: 6px;">Khu vực thu âm trả lời Speaking của Học viên</div>
                            <div style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 999px; background: #dc2626; color: #ffffff; font-size: 12.5px; font-weight: 700;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                </svg>
                                <span>Nhấn để bắt đầu thu âm</span>
                            </div>
                        </div>

                        {{-- Mô phỏng khung Writing --}}
                        <div id="simWritingBox" style="display: none; margin-top: 10px;">
                            <textarea rows="4" disabled placeholder="Học viên sẽ gõ câu/email/bài luận tiếng Anh vào khung này..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-size: 12.5px; color: #64748b;"></textarea>
                            <div style="text-align: right; font-size: 11.5px; color: #64748b; margin-top: 4px;">Bộ đếm số từ tự động (Word Count)</div>
                        </div>

                        {{-- Accordion giải thích chi tiết & bản dịch --}}
                        <div class="qcreate-sim-accordion">
                            <button type="button" class="qcreate-sim-accordion-btn" onclick="toggleSimExplanation()">
                                <span style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <span>Xem giải thích chi tiết &amp; Bản dịch</span>
                                </span>
                                <svg id="accordionArrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                            <div class="qcreate-sim-accordion-content" id="simExplanationContent" style="display: none;">
                                <div id="simExplanationText" style="white-space: pre-line;">Chưa có giải thích.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STICKY BOTTOM BAR --}}
        <div class="qcreate-bottom-bar">
            <div class="qcreate-autosave-info">
                <span class="qcreate-pulse-dot"></span>
                <span>Biên soạn chuẩn cấu trúc TOEIC ETS</span>
            </div>
            <div class="qcreate-bottom-actions">
                <a href="{{ !empty($testContext) ? route('admin.tests.show', $testContext['test_id']) : route('admin.questions.index') }}" class="qcreate-btn-cancel">
                    Hủy bỏ
                </a>
                <button type="submit" name="action_type" value="draft" class="qcreate-btn-draft">
                    Lưu nháp
                </button>
                <button type="submit" name="action_type" value="publish" class="qcreate-btn-publish">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <span>Lưu & Xuất bản câu hỏi</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    var PART_TEMPLATES = {
        PART1: {
            range: '001-006', timer: '00:25', mode: 'listening', mcq: 4, optionsRequired: false,
            showAudio: true, showImage: true, showStimulus: false, showBlankBtn: false,
            formatTag: 'Tranh + Audio + 4 phương án A–D',
            guide: 'Listening Part 1 (Photographs): Tải lên bức tranh minh họa + file Audio (hoặc nhập Transcript rồi bấm Sinh TTS). 4 phương án A–D có thể để trống nếu chỉ nghe audio.',
            imageLabel: 'Hình ảnh mô tả tranh Part 1 (Bắt buộc cho học viên quan sát)',
            contentLabel: 'Hướng dẫn / Mô tả câu hỏi Part 1',
            sample: {
                content: 'Look at the picture and listen to the four statements. Choose the statement that best describes what you see.',
                transcript: '(A) A man is typing on a laptop. (B) A man is holding a briefcase. (C) A woman is opening a window. (D) People are boarding a train.',
                options: { A: 'A man is typing on a laptop.', B: 'A man is holding a briefcase.', C: 'A woman is opening a window.', D: 'People are boarding a train.' },
                correct: 'B',
                explanation: 'Dịch phương án:\n(A) Một người đàn ông đang gõ máy tính xách tay.\n(B) Một người đàn ông đang cầm cặp tài liệu.\n(C) Một người phụ nữ đang mở cửa sổ.\n(D) Mọi người đang lên tàu.'
            }
        },
        PART2: {
            range: '007-031', timer: '00:20', mode: 'listening', mcq: 3, optionsRequired: true,
            showAudio: true, showImage: false, showStimulus: false, showBlankBtn: false,
            formatTag: 'Audio + 3 phương án A–C (Ẩn D)',
            guide: 'Listening Part 2 (Question-Response): Chỉ gồm ĐÚNG 3 phương án A, B, C (tự động ẩn D). Hỗ trợ tải file audio hoặc sinh giọng đọc TTS từ Transcript.',
            contentLabel: 'Câu hỏi nghe Part 2',
            sample: {
                content: 'Where did you leave the keys to the conference room?',
                transcript: 'Where did you leave the keys to the conference room? (A) At two o clock. (B) On the reception desk. (C) Yes, the room is booked.',
                options: { A: 'At two o clock.', B: 'On the reception desk.', C: 'Yes, the room is booked.' },
                correct: 'B',
                explanation: 'Câu hỏi Where hỏi về địa điểm -> chọn (B) On the reception desk (Trên bàn lễ tân).'
            }
        },
        PART3: {
            range: '032-070', timer: '00:35', mode: 'listening', mcq: 4, optionsRequired: true,
            showAudio: true, showImage: true, showStimulus: false, showBlankBtn: false,
            formatTag: 'Audio + Transcript + Biểu đồ (tùy chọn) + A–D',
            guide: 'Listening Part 3 (Conversations): Nhập lời thoại hội thoại để sinh Audio TTS, có thể đính kèm thêm ảnh biểu đồ/bảng biểu nếu câu hỏi yêu cầu "Look at the graphic".',
            imageLabel: 'Ảnh biểu đồ / Bảng biểu đính kèm (Tùy chọn cho câu hỏi Graphic)',
            contentLabel: 'Câu hỏi trắc nghiệm Part 3',
            sample: {
                content: 'Where does the conversation most likely take place?',
                transcript: 'Man: Excuse me, I ordered a projector last week and it has not arrived yet. Woman: Let me check your invoice number in our electronics store system.',
                options: { A: 'At an electronics store', B: 'At a railway station', C: 'At a hotel reception', D: 'At a medical clinic' },
                correct: 'A',
                explanation: 'Người phụ nữ nhắc đến "our electronics store system" -> cuộc hội thoại diễn ra tại cửa hàng điện tử.'
            }
        },
        PART4: {
            range: '071-100', timer: '00:35', mode: 'listening', mcq: 4, optionsRequired: true,
            showAudio: true, showImage: true, showStimulus: false, showBlankBtn: false,
            formatTag: 'Audio + Transcript + Biểu đồ (tùy chọn) + A–D',
            guide: 'Listening Part 4 (Short Talks): Nhập bài nói ngắn / thông báo vào Transcript để sinh Audio TTS, tùy chọn tải ảnh biểu đồ kèm theo và 4 phương án A–D.',
            imageLabel: 'Ảnh biểu đồ / Lịch trình đính kèm (Tùy chọn)',
            contentLabel: 'Câu hỏi trắc nghiệm Part 4',
            sample: {
                content: 'What does the speaker encourage the listeners to do?',
                transcript: 'Thank you for attending today\'s gardening workshop. Before you leave, please sign up for our monthly newsletter at the front desk.',
                options: { A: 'Turn off mobile phones', B: 'Have some refreshments', C: 'Purchase some seeds', D: 'Sign up for a mailing list' },
                correct: 'D',
                explanation: 'Người nói khuyến khích người nghe đăng ký nhận bản tin hàng tháng ("sign up for our monthly newsletter").'
            }
        },
        PART5: {
            range: '101-130', timer: '00:30', mode: 'reading', mcq: 4, optionsRequired: true,
            showAudio: false, showImage: false, showStimulus: false, showBlankBtn: true,
            formatTag: 'Điền chỗ trống (-------) + 4 phương án A–D',
            guide: 'Reading Part 5 (Incomplete Sentences): Nhập câu đơn có chỗ trống ------- và 4 phương án trắc nghiệm A, B, C, D.',
            contentLabel: 'Câu hỏi điền vào chỗ trống (Part 5)',
            sample: {
                content: 'Because the recent marketing campaign was extraordinarily -------, the board approved an additional budget.',
                options: { A: 'success', B: 'successful', C: 'successfully', D: 'succeed' },
                correct: 'B',
                explanation: 'Sau động từ "was" và phó từ "extraordinarily" cần một tính từ -> chọn (B) successful.'
            }
        },
        PART6: {
            range: '131-146', timer: '00:45', mode: 'reading', mcq: 4, optionsRequired: true,
            showAudio: false, showImage: true, showStimulus: true, showBlankBtn: true,
            formatTag: 'Đoạn văn + 4 phương án A–D',
            guide: 'Reading Part 6 (Text Completion): Nhập đoạn văn hoàn chỉnh vào ô Đoạn văn (Stimulus Text), câu hỏi vị trí chỗ trống và 4 phương án A–D.',
            stimulusLabel: 'Đoạn văn chứa chỗ trống (Part 6 Passage)',
            imageLabel: 'Ảnh scan tài liệu (Tùy chọn)',
            contentLabel: 'Câu hỏi vị trí chỗ trống (Part 6)',
            sample: {
                stimulus: 'To: All Staff\nSubject: Annual Office Retreat\nWe are pleased to announce that our annual retreat will be held at Lakeside Resort. -------, transportation will be provided from the main office at 8:00 AM.',
                content: 'Choose the best word or phrase to fill in the blank in the passage.',
                options: { A: 'However', B: 'In addition', C: 'Despite', D: 'Unless' },
                correct: 'B',
                explanation: 'Cần trạng từ liên kết mang nghĩa bổ sung thông tin -> chọn (B) In addition.'
            }
        },
        PART7: {
            range: '147-200', timer: '01:00', mode: 'reading', mcq: 4, optionsRequired: true,
            showAudio: false, showImage: true, showStimulus: true, showBlankBtn: false,
            formatTag: 'Bài đọc hiểu + Ảnh (tùy chọn) + 4 phương án A–D',
            guide: 'Reading Part 7 (Reading Comprehension): Nhập nội dung Email / Thông báo / Bài báo vào ô Đoạn văn (hoặc tải ảnh tài liệu) và 4 phương án A–D.',
            stimulusLabel: 'Nội dung Bài đọc / Email / Thông báo (Part 7 Passage)',
            imageLabel: 'Hình ảnh tài liệu / Bảng giá / Hóa đơn (Tùy chọn)',
            contentLabel: 'Câu hỏi đọc hiểu (Part 7)',
            sample: {
                stimulus: 'NOTICE TO TENANTS\nThe building\'s main elevator will undergo routine inspection on Friday from 9:00 AM to 11:30 AM. Please use the east staircase during this period.',
                content: 'What are tenants asked to do on Friday morning?',
                options: { A: 'Submit a maintenance form', B: 'Use the east staircase', C: 'Attend a safety meeting', D: 'Move their vehicles' },
                correct: 'B',
                explanation: 'Thông báo ghi rõ: "Please use the east staircase during this period."'
            }
        },
        SPK_T1: {
            range: '01-02', timer: '00:45', mode: 'speaking', mcq: 0, optionsRequired: false,
            showAudio: false, showImage: false, showStimulus: true, showBlankBtn: false,
            formatTag: 'Speaking - Tự luận nói (Ẩn A–D)',
            guide: 'Speaking Part 1 (Read a Text Aloud): Nhập đoạn văn bản học viên cần đọc to (45s chuẩn bị, 45s đọc). Không dùng phương án A–D.',
            stimulusLabel: 'Đoạn văn học viên cần đọc to (Reading Text)',
            contentLabel: 'Yêu cầu đề bài (Directions)',
            sample: {
                stimulus: 'Welcome aboard Flight 408 with nonstop service to Seattle. Please fasten your seat belts and place all carry-on bags securely under the seat in front of you.',
                content: 'Read the following flight announcement aloud clearly with natural intonation and stress.',
                explanation: 'Tiêu chí chấm: Phát âm rõ phụ âm cuối (-s, -ed), ngắt nghỉ sau dấu phẩy và nhấn trọng âm ở các từ khóa (nonstop, securely).'
            }
        },
        SPK_T2: {
            range: '03-04', timer: '00:45', mode: 'speaking', mcq: 0, optionsRequired: false,
            showAudio: false, showImage: true, showStimulus: false, showBlankBtn: false,
            formatTag: 'Speaking - Mô tả tranh (Ẩn A–D)',
            guide: 'Speaking Part 2 (Describe a Picture): Tải lên bức tranh để học viên quan sát và mô tả trong 45 giây. Không dùng phương án A–D.',
            imageLabel: 'Bức tranh để học viên mô tả (Bắt buộc)',
            contentLabel: 'Yêu cầu mô tả tranh',
            sample: {
                content: 'Describe the picture on your screen in as much detail as you can. You will have 45 seconds to prepare and 30 seconds to speak.',
                explanation: 'Gợi ý mẫu: This picture was taken in a busy office. In the foreground, two colleagues are reviewing a document on a laptop...'
            }
        },
        SPK_T3: {
            range: '05-07', timer: '00:30', mode: 'speaking', mcq: 0, optionsRequired: false,
            showAudio: false, showImage: false, showStimulus: false, showBlankBtn: false,
            formatTag: 'Speaking - Trả lời câu hỏi (Ẩn A–D)',
            guide: 'Speaking Part 3 (Respond to Questions): Nhập câu hỏi phỏng vấn ngắn theo chủ đề quen thuộc. Không dùng phương án A–D.',
            contentLabel: 'Câu hỏi Speaking Part 3',
            sample: {
                content: 'How often do you shop online, and what types of products do you usually purchase?',
                explanation: 'Gợi ý mẫu: I shop online about twice a month. I usually buy books, household items, and electronics because it saves time and makes price comparison easy.'
            }
        },
        SPK_T4: {
            range: '08-10', timer: '00:30', mode: 'speaking', mcq: 0, optionsRequired: false,
            showAudio: false, showImage: true, showStimulus: true, showBlankBtn: false,
            formatTag: 'Speaking - Theo bảng thông tin (Ẩn A–D)',
            guide: 'Speaking Part 4 (Respond Using Information Provided): Nhập lịch trình / bảng thông tin vào Stimulus Text (hoặc ảnh) và câu hỏi tình huống.',
            stimulusLabel: 'Bảng lịch trình / Thông tin cung cấp sẵn',
            imageLabel: 'Ảnh bảng thông tin (Tùy chọn)',
            contentLabel: 'Câu hỏi dựa trên thông tin cung cấp',
            sample: {
                stimulus: 'CONFERENCE SCHEDULE - MAY 12\n09:00 AM - Opening Keynote (Dr. Elena Vance, Main Hall)\n11:00 AM - Cloud Security Panel (Room 204)\n01:30 PM - Networking Lunch (Garden Terrace)',
                content: 'Could you tell me when the Cloud Security Panel starts and which room it is in?',
                explanation: 'Gợi ý mẫu: Sure, the Cloud Security Panel begins at 11:00 AM and will take place in Room 204.'
            }
        },
        SPK_T5: {
            range: '11', timer: '01:00', mode: 'speaking', mcq: 0, optionsRequired: false,
            showAudio: false, showImage: false, showStimulus: false, showBlankBtn: false,
            formatTag: 'Speaking - Quan điểm cá nhân (Ẩn A–D)',
            guide: 'Speaking Part 5 (Express an Opinion): Nhập chủ đề tranh luận để học viên trình bày quan điểm trong 60 giây. Không dùng phương án A–D.',
            contentLabel: 'Câu hỏi trình bày quan điểm (Opinion Prompt)',
            sample: {
                content: 'Do you agree or disagree that companies should allow employees to work remotely at least two days a week? Give specific reasons and examples to support your opinion.',
                explanation: 'Dàn ý mẫu: 1) Nêu rõ quan điểm (Strongly agree). 2) Lý do 1: Tiết kiệm thời gian di chuyển, tăng năng suất. 3) Lý do 2: Cân bằng cuộc sống – công việc. 4) Kết luận.'
            }
        },
        WRITING_T1: {
            range: '01-05', timer: '01:30', mode: 'writing', mcq: 0, optionsRequired: false,
            showAudio: false, showImage: true, showStimulus: false, showBlankBtn: false,
            formatTag: 'Writing - Viết câu theo tranh (Ẩn A–D)',
            guide: 'Writing Part 1 (Write a Sentence Based on a Picture): Tải lên bức tranh và nhập 2 từ/cụm từ bắt buộc học viên phải dùng trong câu.',
            imageLabel: 'Bức tranh minh họa cho câu viết (Bắt buộc)',
            contentLabel: 'Yêu cầu & 2 từ khóa bắt buộc (Keywords)',
            sample: {
                content: 'Write ONE sentence based on the picture using BOTH words: "wait / counter".',
                explanation: 'Câu mẫu đạt điểm tối đa: Several customers are waiting in line at the reception counter.'
            }
        },
        WRITING_T2: {
            range: '06-07', timer: '10:00', mode: 'writing', mcq: 0, optionsRequired: false,
            showAudio: false, showImage: false, showStimulus: true, showBlankBtn: false,
            formatTag: 'Writing - Phản hồi Email (Ẩn A–D)',
            guide: 'Writing Part 2 (Respond to a Written Request): Nhập nội dung Email gốc vào Stimulus Text và yêu cầu hồi đáp cụ thể.',
            stimulusLabel: 'Nội dung Email gốc học viên cần phản hồi',
            contentLabel: 'Yêu cầu nhiệm vụ phản hồi Email',
            sample: {
                stimulus: 'From: Mark Chen (Event Coordinator)\nTo: Catering Team\nSubject: Workshop Refreshments\nWe are expecting 45 attendees for Thursday\'s workshop. Could you confirm the delivery time and whether vegetarian snacks are available?',
                content: 'Respond to Mark Chen as the Catering Manager. Confirm the delivery time, explain two vegetarian options, and ask one question about the setup location.',
                explanation: 'Tiêu chí chấm: Đáp ứng đủ 3 yêu cầu, giọng văn email công sở lịch sự, cấu trúc mở – thân – kết rõ ràng.'
            }
        },
        WRITING_T3: {
            range: '08', timer: '30:00', mode: 'writing', mcq: 0, optionsRequired: false,
            showAudio: false, showImage: false, showStimulus: false, showBlankBtn: false,
            formatTag: 'Writing - Bài luận Essay (Ẩn A–D)',
            guide: 'Writing Part 3 (Write an Opinion Essay): Nhập đề bài luận tối thiểu 300 từ (30 phút). Không dùng phương án A–D.',
            contentLabel: 'Đề bài luận (Essay Prompt)',
            sample: {
                content: 'Some people prefer working for a large international corporation, while others prefer working for a small local company. Which do you prefer? Use specific reasons and examples to support your opinion (minimum 300 words).',
                explanation: 'Tiêu chí chấm: Lập luận chặt chẽ, tối thiểu 300 từ, chia đoạn mạch lạc (Introduction, 2 Body Paragraphs, Conclusion), từ vựng học thuật đa dạng.'
            }
        }
    };

    var CURRENT_IMAGE_PREVIEW_URL = '';
    var CURRENT_AUDIO_PREVIEW_URL = '';

    function getSelectedPartCode() {
        var secSelect = document.getElementById('sectionSelect');
        if (!secSelect || secSelect.selectedIndex < 0) return 'PART5';
        return (secSelect.options[secSelect.selectedIndex].getAttribute('data-code') || 'PART5').toUpperCase();
    }

    function applyPartTemplate(clearOldSample) {
        var code = getSelectedPartCode();
        var cfg = PART_TEMPLATES[code] || PART_TEMPLATES.PART5;

        document.getElementById('partGuideText').textContent = cfg.guide;
        document.getElementById('partFormatTag').textContent = cfg.formatTag;
        document.getElementById('partRangeBadge').textContent = cfg.range;
        document.getElementById('simTimerText').textContent = cfg.timer;
        document.getElementById('contentLabel').innerHTML = (cfg.contentLabel || 'Nội dung câu hỏi') + ' <span class="required-mark">*</span>';

        // Ẩn/hiện các panel theo đặc thù từng Part
        document.getElementById('panelStimulus').style.display = cfg.showStimulus ? 'block' : 'none';
        document.getElementById('panelImage').style.display = cfg.showImage ? 'block' : 'none';
        document.getElementById('panelAudio').style.display = cfg.showAudio ? 'block' : 'none';
        document.getElementById('btnInsertBlank').style.display = cfg.showBlankBtn ? 'inline-flex' : 'none';

        if (cfg.stimulusLabel) {
            document.getElementById('stimulusLabel').textContent = cfg.stimulusLabel;
        }
        if (cfg.imageLabel) {
            document.getElementById('imagePanelLabel').textContent = cfg.imageLabel;
        }

        // Card phương án A-D: Ẩn với Speaking/Writing, ẩn D với Part 2
        var mcqCard = document.getElementById('mcqOptionsCard');
        var optRowD = document.getElementById('optRowD');
        var simChoiceD = document.getElementById('simChoiceD');
        var optHint = document.getElementById('mcqOptionalHint');

        if (cfg.mcq === 0) {
            mcqCard.style.display = 'none';
            ['A', 'B', 'C', 'D'].forEach(function(l) {
                var inp = document.getElementById('optInput' + l);
                if (inp) inp.required = false;
            });
            document.getElementById('explanationCardTitle').textContent = '2. Tiêu chí chấm & Bài mẫu tham khảo';
        } else {
            mcqCard.style.display = 'block';
            document.getElementById('mcqCardTitle').textContent = cfg.mcq === 3
                ? '2. Thiết lập 3 phương án lựa chọn (A - B - C)'
                : '2. Thiết lập 4 phương án lựa chọn (A - B - C - D)';
            document.getElementById('explanationCardTitle').textContent = '3. Giải thích đáp án & Bản dịch song ngữ';

            optRowD.style.display = cfg.mcq === 3 ? 'none' : 'flex';
            simChoiceD.style.display = cfg.mcq === 3 ? 'none' : 'flex';
            optHint.style.display = code === 'PART1' ? 'inline' : 'none';

            ['A', 'B', 'C'].forEach(function(l) {
                var inp = document.getElementById('optInput' + l);
                if (inp) inp.required = !!cfg.optionsRequired;
            });
            var inpD = document.getElementById('optInputD');
            if (inpD) {
                inpD.required = (cfg.mcq === 4 && !!cfg.optionsRequired);
                if (cfg.mcq === 3) inpD.value = '';
            }
            if (cfg.mcq === 3 && document.getElementById('correctOptionInput').value === 'D') {
                selectCorrectOption('A');
            }
        }

        // Cập nhật giao diện Simulator cột phải theo mode (listening / reading / speaking / writing)
        document.getElementById('simAudioBox').style.display = cfg.showAudio ? 'block' : 'none';
        document.getElementById('simChoicesContainer').style.display = cfg.mcq > 0 ? 'flex' : 'none';
        document.getElementById('simSpeakingBox').style.display = cfg.mode === 'speaking' ? 'block' : 'none';
        document.getElementById('simWritingBox').style.display = cfg.mode === 'writing' ? 'block' : 'none';

        updateSimHeader();
        syncSimulator();
    }

    function fillTemplateSampleForCurrentPart() {
        var code = getSelectedPartCode();
        var cfg = PART_TEMPLATES[code] || PART_TEMPLATES.PART5;
        var sample = cfg.sample || {};

        document.getElementById('contentInput').value = sample.content || '';
        document.getElementById('stimulusInput').value = sample.stimulus || '';
        document.getElementById('transcriptInput').value = sample.transcript || '';
        document.getElementById('explanationInput').value = sample.explanation || '';

        ['A', 'B', 'C', 'D'].forEach(function(l) {
            var inp = document.getElementById('optInput' + l);
            if (inp) inp.value = (sample.options && sample.options[l]) ? sample.options[l] : '';
        });

        if (sample.correct) {
            selectCorrectOption(sample.correct);
        }

        handleContentInput();
        handleExplanationInput();
        syncSimChoices();
        syncSimulator();
    }

    function handleImageUploadChange(input) {
        if (input.files && input.files[0]) {
            if (CURRENT_IMAGE_PREVIEW_URL) URL.revokeObjectURL(CURRENT_IMAGE_PREVIEW_URL);
            CURRENT_IMAGE_PREVIEW_URL = URL.createObjectURL(input.files[0]);

            document.getElementById('imageUploadPreview').src = CURRENT_IMAGE_PREVIEW_URL;
            document.getElementById('imageUploadPreviewBox').style.display = 'block';
            document.getElementById('btnClearImage').style.display = 'inline';

            document.getElementById('simImagePreview').src = CURRENT_IMAGE_PREVIEW_URL;
            document.getElementById('simImageBox').style.display = 'block';
        }
    }

    function clearSelectedImage() {
        var input = document.getElementById('imageFileInput');
        if (input) input.value = '';
        if (CURRENT_IMAGE_PREVIEW_URL) {
            URL.revokeObjectURL(CURRENT_IMAGE_PREVIEW_URL);
            CURRENT_IMAGE_PREVIEW_URL = '';
        }
        document.getElementById('imageUploadPreviewBox').style.display = 'none';
        document.getElementById('btnClearImage').style.display = 'none';
        document.getElementById('simImageBox').style.display = 'none';
    }

    function handleAudioUploadChange(input) {
        if (input.files && input.files[0]) {
            if (CURRENT_AUDIO_PREVIEW_URL) URL.revokeObjectURL(CURRENT_AUDIO_PREVIEW_URL);
            CURRENT_AUDIO_PREVIEW_URL = URL.createObjectURL(input.files[0]);
            setAudioPreviewSource(CURRENT_AUDIO_PREVIEW_URL, 'Đã chọn file: ' + input.files[0].name);
        }
    }

    function setAudioPreviewSource(srcUrl, statusText) {
        var wrap = document.getElementById('audioPlayerPreviewWrap');
        var player = document.getElementById('audioPreviewPlayer');
        var lbl = document.getElementById('audioStatusLabel');
        var simPlayer = document.getElementById('simAudioPlayer');
        var simEmpty = document.getElementById('simAudioEmptyText');

        if (player && wrap) {
            player.src = srcUrl;
            wrap.style.display = 'block';
            if (lbl) lbl.textContent = statusText || 'Âm thanh đã sẵn sàng';
        }
        if (simPlayer && simEmpty) {
            simPlayer.src = srcUrl;
            simPlayer.style.display = 'block';
            simEmpty.style.display = 'none';
        }
    }

    function generateTtsAudio() {
        var transcript = (document.getElementById('transcriptInput')?.value || '').trim();
        var content = (document.getElementById('contentInput')?.value || '').trim();
        var textToSpeak = transcript || content;
        if (!textToSpeak) {
            alert('Vui lòng nhập Transcript lời thoại hoặc Nội dung câu hỏi trước khi sinh giọng đọc TTS.');
            return;
        }

        var code = getSelectedPartCode();
        var partMatch = code.match(/PART(\d)/);
        var partNum = partMatch ? parseInt(partMatch[1], 10) : 1;

        var btn = document.getElementById('btnGenerateTts');
        var lbl = document.getElementById('btnGenerateTtsLabel');
        if (btn) btn.disabled = true;
        if (lbl) lbl.textContent = 'Đang sinh audio TTS...';

        fetch('{{ route('api.admin.questions.generate-tts') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                text: textToSpeak,
                part_number: partNum
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(payload) {
            if (payload && payload.success && payload.audio_url) {
                document.getElementById('generatedAudioUrlInput').value = payload.audio_url;
                setAudioPreviewSource(payload.audio_url, 'Đã sinh file audio TTS thành công (' + (payload.duration_estimate || 5) + 's)');
            } else {
                alert((payload && payload.message) ? payload.message : 'Không thể sinh audio TTS lúc này.');
            }
        })
        .catch(function() {
            alert('Lỗi kết nối tới API sinh giọng đọc TTS.');
        })
        .finally(function() {
            if (btn) btn.disabled = false;
            if (lbl) lbl.textContent = 'Sinh giọng đọc TTS';
        });
    }

    function updateCounter(inputId, counterId) {
        var el = document.getElementById(inputId);
        var counter = document.getElementById(counterId);
        if (!el || !counter) return;
        var text = el.value || '';
        var words = text.trim() ? text.trim().split(/\s+/).length : 0;
        counter.textContent = 'Độ dài: ' + words + ' từ / ' + text.length + ' ký tự';
    }

    function insertBlank(textareaId) {
        var textarea = document.getElementById(textareaId);
        if (!textarea) return;
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var blankStr = '-------';
        textarea.value = textarea.value.substring(0, start) + blankStr + textarea.value.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + blankStr.length;
        textarea.focus();
        handleContentInput();
    }

    function wrapText(textareaId, openTag, closeTag) {
        var textarea = document.getElementById(textareaId);
        if (!textarea) return;
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var selected = textarea.value.substring(start, end);
        textarea.value = textarea.value.substring(0, start) + openTag + (selected || 'nội dung') + closeTag + textarea.value.substring(end);
        textarea.focus();
        if (textareaId === 'contentInput') handleContentInput();
        else handleExplanationInput();
    }

    function handleContentInput() {
        updateCounter('contentInput', 'contentCounter');
        var val = document.getElementById('contentInput').value || '';
        var simBody = document.getElementById('simQuestionBody');
        if (simBody) {
            var formatted = val
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/-------/g, '<span class="qcreate-sim-blank-highlight">-------</span>');
            simBody.innerHTML = formatted || '<em>(Nhập nội dung câu hỏi ở cột bên trái để xem trước...)</em>';
        }
    }

    function handleExplanationInput() {
        updateCounter('explanationInput', 'explanationCounter');
        var expVal = document.getElementById('explanationInput').value || '';
        var simExpText = document.getElementById('simExplanationText');
        if (simExpText) {
            simExpText.textContent = expVal || 'Chưa có giải thích.';
        }
    }

    function syncSimChoices() {
        ['A', 'B', 'C', 'D'].forEach(function(letter) {
            var input = document.getElementById('optInput' + letter);
            var simText = document.getElementById('simChoiceText' + letter);
            if (input && simText) {
                var text = input.value.trim();
                simText.textContent = '(' + letter + ') ' + (text || '...');
            }
        });
    }

    function syncSimulator() {
        var stimVal = (document.getElementById('stimulusInput')?.value || '').trim();
        var stimBox = document.getElementById('simStimulusBox');
        if (stimBox) {
            if (stimVal && document.getElementById('panelStimulus').style.display !== 'none') {
                stimBox.textContent = stimVal;
                stimBox.style.display = 'block';
            } else {
                stimBox.style.display = 'none';
            }
        }
    }

    function selectCorrectOption(letter) {
        document.getElementById('correctOptionInput').value = letter;
        ['A', 'B', 'C', 'D'].forEach(function(l) {
            var row = document.getElementById('optRow' + l);
            var simPill = document.getElementById('simChoice' + l);
            var btnText = row ? row.querySelector('.opt-btn-text') : null;
            var isChosen = (l === letter);

            if (row) {
                row.classList.toggle('is-correct', isChosen);
                if (btnText) btnText.textContent = isChosen ? 'ĐÁP ÁN ĐÚNG' : 'Chọn đúng';
            }
            if (simPill) simPill.classList.toggle('active', isChosen);
        });
    }

    function updateSimHeader() {
        var seq = document.getElementById('sequenceNoInput').value || '1';
        var secSelect = document.getElementById('sectionSelect');
        var partName = secSelect && secSelect.options[secSelect.selectedIndex] ? secSelect.options[secSelect.selectedIndex].text : 'Part 5';
        var shortPart = partName.split(':')[0].trim();
        var simHeader = document.getElementById('simQuestionMeta');
        if (simHeader) {
            simHeader.textContent = 'Câu ' + seq + ' / ' + shortPart;
        }
    }

    function syncSkillPart(keepSelectedIfValid) {
        var skillSelect = document.getElementById('skillSelect');
        var secSelect = document.getElementById('sectionSelect');
        if (!skillSelect || !secSelect) {
            applyPartTemplate(false);
            return;
        }

        var selectedSkillKey = String(skillSelect.value || '');
        var firstVisibleVal = null;
        var currentSelectedStillValid = false;

        Array.from(secSelect.options).forEach(function(opt) {
            var optSkillKey = String(opt.getAttribute('data-skill-key') || '');
            var isMatch = (!selectedSkillKey || !optSkillKey || optSkillKey === selectedSkillKey);
            opt.hidden = !isMatch;
            opt.disabled = !isMatch;
            opt.style.display = isMatch ? '' : 'none';
            if (isMatch) {
                if (firstVisibleVal === null) firstVisibleVal = opt.value;
                if (opt.value === secSelect.value) currentSelectedStillValid = true;
            }
        });

        if ((!keepSelectedIfValid || !currentSelectedStillValid) && firstVisibleVal !== null) {
            secSelect.value = firstVisibleVal;
        }

        applyPartTemplate(false);
    }

    function scrollToPreview() {
        var sim = document.getElementById('previewSimulatorSection');
        if (sim) sim.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function toggleSimExplanation() {
        var content = document.getElementById('simExplanationContent');
        var arrow = document.getElementById('accordionArrow');
        if (!content) return;
        var isHidden = content.style.display === 'none';
        content.style.display = isHidden ? 'block' : 'none';
        if (arrow) arrow.style.transform = isHidden ? 'rotate(180deg)' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        syncSkillPart(true);
        handleContentInput();
        handleExplanationInput();
        syncSimChoices();

        var existingAudioUrl = document.getElementById('generatedAudioUrlInput')?.value;
        if (existingAudioUrl) {
            setAudioPreviewSource(existingAudioUrl, 'Âm thanh TTS đã sinh');
        }
    });
</script>
@endpush
