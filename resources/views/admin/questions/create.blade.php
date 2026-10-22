@extends('layouts.admin', ['active' => 'ngan-hang-cau-hoi'])

@section('title', 'Thêm câu hỏi TOEIC mới')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <a href="{{ route('admin.questions.index') }}" style="color: inherit; text-decoration: none;">Ngân hàng câu hỏi</a>
    <span class="crumb-arrow">></span>
    <span style="color: var(--admin-primary); font-weight: 600;">Thêm câu hỏi TOEIC mới</span>
@endsection

@section('content')
<div class="qcreate-container">
    {{-- Form bao toàn bộ trang để có thể submit từ nút sticky hoặc nút trên top --}}
    <form action="{{ route('admin.questions.store') }}" method="POST" id="createQuestionForm">
        @csrf
        <input type="hidden" name="correct_option" id="correctOptionInput" value="{{ old('correct_option', 'B') }}">
        <input type="hidden" name="question_type_id" value="1">
        <input type="hidden" name="difficulty" id="difficultyInput" value="{{ old('difficulty', 'MEDIUM') }}">
        <input type="hidden" name="points" value="{{ old('points', '1.00') }}">

        {{-- Header & Top Action Buttons --}}
        <div class="qcreate-header">
            <div class="qcreate-title-wrap">
                <h1 class="qcreate-page-title">Thêm câu hỏi TOEIC mới</h1>
            </div>
            <div class="qcreate-header-actions">
                <button type="submit" name="action_type" value="draft" class="qcreate-btn-top" title="Lưu bản nháp câu hỏi">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Lưu bản nháp</span>
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
        @if ($errors->any())
            <div style="background-color: #fee2e2; border: 1px solid #fecaca; color: #be123c; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
                <div style="font-weight: 700; margin-bottom: 4px;">Có lỗi xảy ra khi lưu câu hỏi:</div>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
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
                <p class="qcreate-card-subtitle">Bắt buộc cấu hình trước khi xuất bản</p>
            </div>

            <div class="qcreate-config-grid">
                {{-- Kỹ năng (Skill) --}}
                <div class="qcreate-field-group">
                    <label class="qcreate-field-label">
                        Kỹ năng (Skill) <span class="required-mark">*</span>
                    </label>
                    <select name="skill_id" id="skillSelect" class="qcreate-input-control" onchange="syncSkillPart()">
                        @foreach ($skills as $skill)
                            <option value="{{ $skill->skill_id }}" {{ (old('skill_id', 2) == $skill->skill_id || strtolower($skill->skill_name) === 'reading') ? 'selected' : '' }}>
                                {{ $skill->skill_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Phần thi (TOEIC Part) --}}
                <div class="qcreate-field-group">
                    <label class="qcreate-field-label">
                        Phần thi (TOEIC Part) <span class="required-mark">*</span>
                    </label>
                    <select name="section_id" id="sectionSelect" class="qcreate-input-control" required onchange="updateSimHeader()">
                        @foreach ($sections as $sec)
                            @php
                                $isPart5 = str_contains($sec->section_name, 'Part 5') || $sec->section_id == 5;
                            @endphp
                            <option value="{{ $sec->section_id }}" {{ (old('section_id') == $sec->section_id || (!old('section_id') && $isPart5)) ? 'selected' : '' }}>
                                {{ $sec->section_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Bộ đề / Nguồn trích xuất --}}
                <div class="qcreate-field-group">
                    <label class="qcreate-field-label">Bộ đề / Nguồn trích xuất</label>
                    <div class="qcreate-input-icon-wrap">
                        <input type="text" name="source_set" class="qcreate-input-control" value="{{ old('source_set', 'ETS TOEIC 2024 - Test 03') }}">
                        <span class="qcreate-icon-suffix" title="Sao chép nguồn" onclick="navigator.clipboard.writeText('ETS TOEIC 2024 - Test 03')">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                        </span>
                    </div>
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
                        <input type="number" name="sequence_no" id="sequenceNoInput" class="qcreate-input-control" value="{{ old('sequence_no', 108) }}" min="1" max="200" oninput="updateSimHeader()">
                        <span class="qcreate-badge-suffix" id="partRangeBadge">101-130</span>
                    </div>
                </div>
            </div>

            {{-- Dòng Chủ điểm ngữ pháp --}}
            <div class="qcreate-tags-row">
                <span class="qcreate-tags-title">CHỦ ĐIỂM NGỮ PHÁP:</span>
                <span class="qcreate-grammar-tag" id="tagPill1">
                    <span>Từ loại: Adverb + Adjective</span>
                    <span class="remove-tag" onclick="removeTag('tagPill1')" title="Xóa tag">✕</span>
                </span>
                <span class="qcreate-grammar-tag" id="tagPill2">
                    <span>Cấu trúc nguyên nhân (Because)</span>
                    <span class="remove-tag" onclick="removeTag('tagPill2')" title="Xóa tag">✕</span>
                </span>
                <button type="button" class="qcreate-add-tag-btn" onclick="addNewTag()">
                    <span>+ Thêm thẻ tag</span>
                </button>
            </div>
        </div>

        {{-- MAIN 2-COLUMN SECTION --}}
        <div class="qcreate-main-grid">
            {{-- CỘT TRÁI: Form nhập liệu đề bài, đáp án và giải thích --}}
            <div class="qcreate-editor-column">
                {{-- CARD 1: Nội dung đề bài & Chỗ trống cần điền --}}
                <div class="qcreate-card">
                    <div class="qcreate-card-header">
                        <div class="qcreate-section-title">
                            <span class="qcreate-step-dot"></span>
                            <span>1. Nội dung đề bài & Chỗ trống cần điền</span>
                        </div>
                        <div class="qcreate-badge-success">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <span>Tự động nhận diện chỗ trống</span>
                        </div>
                    </div>

                    {{-- Hướng dẫn yêu cầu (Directions / Prompt) --}}
                    <div class="qcreate-field-group" style="margin-bottom: 12px;">
                        <label class="qcreate-field-label">Hướng dẫn yêu cầu (Directions / Prompt)</label>
                        <input type="text" name="prompt_direction" class="qcreate-input-control" value="{{ old('prompt_direction', 'Select the best answer to complete the sentence.') }}" placeholder="Nhập hướng dẫn câu hỏi...">
                    </div>

                    {{-- Rich Text Editor container --}}
                    <div class="qcreate-editor-wrap">
                        <div class="qcreate-editor-toolbar">
                            <div class="qcreate-toolbar-left">
                                <button type="button" class="qcreate-tool-btn" onclick="wrapText('contentInput', '**', '**')" title="In đậm (Bold)">B</button>
                                <button type="button" class="qcreate-tool-btn" onclick="wrapText('contentInput', '*', '*')" title="In nghiêng (Italic)"><em>I</em></button>
                                <button type="button" class="qcreate-tool-btn" onclick="wrapText('contentInput', '<u>', '</u>')" title="Gạch chân (Underline)"><u>U</u></button>
                                <span style="display: inline-block; width: 1px; height: 16px; background: #e2e8f0; margin: 0 4px;"></span>
                                <button type="button" class="qcreate-tool-btn btn-insert-blank" onclick="insertBlank('contentInput')" title="Chèn chỗ trống ------- tại con trỏ">
                                    ␣ Chèn chỗ trống (-------)
                                </button>
                                <button type="button" class="qcreate-tool-btn" onclick="wrapText('contentInput', '“', '”')" title="Trích dẫn">”</button>
                            </div>
                            <div class="qcreate-toolbar-right" id="contentCounter">
                                Độ dài: 22 từ / 164 ký tự
                            </div>
                        </div>

                        <textarea name="content" id="contentInput" class="qcreate-textarea" rows="4" required oninput="handleContentInput()">{{ old('content', 'Because the recent marketing campaign was extraordinarily -------, the board of directors approved an additional budget for the upcoming fiscal quarter.') }}</textarea>

                        <div class="qcreate-editor-hint-bar">
                            <div class="qcreate-hint-left">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                                <span>Vị trí từ cần điền sau phó từ: <strong>extraordinarily [adv] + [adjective]</strong></span>
                            </div>
                            <span class="qcreate-hint-ai-link" onclick="triggerAiSuggest()">Gợi ý AI cho câu này</span>
                        </div>
                    </div>

                    {{-- Đa phương tiện (Audio & Hình ảnh đính kèm) --}}
                    <div class="qcreate-media-box">
                        <div class="qcreate-media-top">
                            <div class="qcreate-media-heading">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                </svg>
                                <span>Đa phương tiện (Audio & Hình ảnh đính kèm)</span>
                            </div>
                            <div class="qcreate-media-tabs">
                                <span class="qcreate-media-tab active" id="tabAudio" onclick="switchMediaTab('audio')">Audio (LC)</span>
                                <span class="qcreate-media-tab" id="tabImage" onclick="switchMediaTab('image')">Hình ảnh (Part 1/7)</span>
                                <span class="qcreate-media-tab" id="tabPassage" onclick="switchMediaTab('passage')">Đoạn văn (Part 6/7)</span>
                            </div>
                        </div>

                        {{-- Player Bar --}}
                        <div class="qcreate-audio-player-bar">
                            <button type="button" class="qcreate-play-btn" id="playBtnToggle" onclick="togglePlayAudio()" title="Nghe thử audio">
                                <svg id="playIcon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </button>
                            <div class="qcreate-audio-info">
                                <span class="qcreate-audio-title">ETS_2024_T03_Q108_NativeUS.mp3</span>
                                <span class="qcreate-audio-meta">Giọng đọc: US English (Female) • 00:14 • 192kbps</span>
                            </div>
                            <div class="qcreate-waveform-wrap" title="Dạng sóng âm thanh">
                                @php
                                    $heights = [12, 18, 24, 15, 8, 20, 26, 14, 22, 28, 16, 10, 25, 20, 15, 27, 23, 11, 19, 14, 8, 16, 22, 18, 12];
                                @endphp
                                @foreach ($heights as $idx => $h)
                                    <div class="qcreate-wave-bar {{ $idx < 10 ? 'played' : '' }}" style="height: {{ $h }}px;"></div>
                                @endforeach
                            </div>
                            <div class="qcreate-audio-actions">
                                <button type="button" class="qcreate-audio-action-btn" title="Tải lên audio mới" onclick="alert('Tính năng tải file audio sẵn sàng!')">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                </button>
                                <button type="button" class="qcreate-audio-action-btn" title="Chỉnh sửa audio" onclick="alert('Mở công cụ cắt/sửa audio')">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                    </svg>
                                </button>
                                <button type="button" class="qcreate-audio-action-btn btn-delete" title="Gỡ file audio" onclick="confirm('Bạn có chắc muốn gỡ file âm thanh này?')">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: Thiết lập 4 phương án lựa chọn (A - B - C - D) --}}
                <div class="qcreate-card">
                    <div class="qcreate-card-header">
                        <div class="qcreate-section-title">
                            <span class="qcreate-step-dot"></span>
                            <span>2. Thiết lập 4 phương án lựa chọn (A - B - C - D)</span>
                        </div>
                        <label class="qcreate-random-shuffle-label">
                            <input type="checkbox" name="is_shuffled" value="1" style="accent-color: #4f46e5;">
                            <span>Xáo trộn vị trí ngẫu nhiên</span>
                        </label>
                    </div>

                    <div class="qcreate-options-list">
                        {{-- PHƯƠNG ÁN A --}}
                        <div class="qcreate-opt-row {{ old('correct_option', 'B') === 'A' ? 'is-correct' : '' }}" id="optRowA">
                            <div class="qcreate-opt-letter">A</div>
                            <input type="text" name="opt_a" id="optInputA" class="qcreate-opt-text-input" value="{{ old('opt_a', 'success') }}" placeholder="Nội dung phương án A" oninput="syncSimChoices()">
                            <span class="qcreate-opt-pos-badge" id="posBadgeA">Danh từ (Noun)</span>
                            <button type="button" class="qcreate-opt-select-btn" onclick="selectCorrectOption('A')">
                                <span class="qcreate-opt-radio-icon"></span>
                                <svg class="qcreate-opt-check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span class="opt-btn-text">{{ old('correct_option', 'B') === 'A' ? 'ĐÁP ÁN ĐÚNG' : 'Chọn đúng' }}</span>
                            </button>
                        </div>

                        {{-- PHƯƠNG ÁN B --}}
                        <div class="qcreate-opt-row {{ old('correct_option', 'B') === 'B' ? 'is-correct' : '' }}" id="optRowB">
                            <div class="qcreate-opt-letter">B</div>
                            <input type="text" name="opt_b" id="optInputB" class="qcreate-opt-text-input" value="{{ old('opt_b', 'successful') }}" placeholder="Nội dung phương án B" oninput="syncSimChoices()">
                            <span class="qcreate-opt-pos-badge" id="posBadgeB">Tính từ (Adjective)</span>
                            <button type="button" class="qcreate-opt-select-btn" onclick="selectCorrectOption('B')">
                                <span class="qcreate-opt-radio-icon"></span>
                                <svg class="qcreate-opt-check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span class="opt-btn-text">{{ old('correct_option', 'B') === 'B' ? 'ĐÁP ÁN ĐÚNG' : 'Chọn đúng' }}</span>
                            </button>
                        </div>

                        {{-- PHƯƠNG ÁN C --}}
                        <div class="qcreate-opt-row {{ old('correct_option', 'B') === 'C' ? 'is-correct' : '' }}" id="optRowC">
                            <div class="qcreate-opt-letter">C</div>
                            <input type="text" name="opt_c" id="optInputC" class="qcreate-opt-text-input" value="{{ old('opt_c', 'successfully') }}" placeholder="Nội dung phương án C" oninput="syncSimChoices()">
                            <span class="qcreate-opt-pos-badge" id="posBadgeC">Trạng từ (Adverb)</span>
                            <button type="button" class="qcreate-opt-select-btn" onclick="selectCorrectOption('C')">
                                <span class="qcreate-opt-radio-icon"></span>
                                <svg class="qcreate-opt-check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span class="opt-btn-text">{{ old('correct_option', 'B') === 'C' ? 'ĐÁP ÁN ĐÚNG' : 'Chọn đúng' }}</span>
                            </button>
                        </div>

                        {{-- PHƯƠNG ÁN D --}}
                        <div class="qcreate-opt-row {{ old('correct_option', 'B') === 'D' ? 'is-correct' : '' }}" id="optRowD">
                            <div class="qcreate-opt-letter">D</div>
                            <input type="text" name="opt_d" id="optInputD" class="qcreate-opt-text-input" value="{{ old('opt_d', 'succeed') }}" placeholder="Nội dung phương án D" oninput="syncSimChoices()">
                            <span class="qcreate-opt-pos-badge" id="posBadgeD">Động từ (Verb)</span>
                            <button type="button" class="qcreate-opt-select-btn" onclick="selectCorrectOption('D')">
                                <span class="qcreate-opt-radio-icon"></span>
                                <svg class="qcreate-opt-check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span class="opt-btn-text">{{ old('correct_option', 'B') === 'D' ? 'ĐÁP ÁN ĐÚNG' : 'Chọn đúng' }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: Giải thích đáp án --}}
                <div class="qcreate-card">
                    <div class="qcreate-card-header">
                        <div class="qcreate-section-title">
                            <span class="qcreate-step-dot"></span>
                            <span>3. Giải thích đáp án</span>
                        </div>
                    </div>

                    <div class="qcreate-editor-wrap">
                        <div class="qcreate-editor-toolbar">
                            <div class="qcreate-toolbar-left">
                                <button type="button" class="qcreate-tool-btn" onclick="wrapText('explanationInput', '**', '**')" title="In đậm (Bold)">B</button>
                                <button type="button" class="qcreate-tool-btn" onclick="wrapText('explanationInput', '*', '*')" title="In nghiêng (Italic)"><em>I</em></button>
                                <button type="button" class="qcreate-tool-btn" onclick="wrapText('explanationInput', '<u>', '</u>')" title="Gạch chân (Underline)"><u>U</u></button>
                                <span style="display: inline-block; width: 1px; height: 16px; background: #e2e8f0; margin: 0 4px;"></span>
                                <button type="button" class="qcreate-tool-btn btn-insert-blank" onclick="insertBlank('explanationInput')">
                                    ␣ Chèn chỗ trống (-------)
                                </button>
                                <button type="button" class="qcreate-tool-btn" onclick="wrapText('explanationInput', '“', '”')" title="Trích dẫn">”</button>
                            </div>
                            <div class="qcreate-toolbar-right" id="explanationCounter">
                                Độ dài: 22 từ / 164 ký tự
                            </div>
                        </div>

                        <textarea name="explanation" id="explanationInput" class="qcreate-textarea" rows="4" placeholder="Nhập lời giải chi tiết, phân tích ngữ pháp, dịch nghĩa câu hỏi..." oninput="handleExplanationInput()">{{ old('explanation', 'Because the recent marketing campaign was extraordinarily -------, the board of directors approved an additional budget for the upcoming fiscal quarter.') }}</textarea>

                        <div class="qcreate-editor-hint-bar">
                            <div class="qcreate-hint-left">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                                <span>Vị trí từ cần điền sau phó từ: <strong>extraordinarily [adv] + [adjective]</strong></span>
                            </div>
                            <span class="qcreate-hint-ai-link" onclick="triggerAiSuggest()">Gợi ý AI cho câu này</span>
                        </div>
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
                            <span id="simQuestionMeta">Câu 108 / Part 5</span>
                            <div class="qcreate-sim-timer">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span>00:45</span>
                            </div>
                        </div>

                        {{-- Nội dung câu hỏi mô phỏng --}}
                        <div class="qcreate-sim-content" id="simQuestionBody">
                            Because the recent marketing campaign was <span class="qcreate-sim-blank-highlight">-------</span>, the board of directors approved an additional budget for the upcoming fiscal quarter.
                        </div>

                        {{-- 4 lựa chọn A, B, C, D mô phỏng --}}
                        <div class="qcreate-sim-choices">
                            <div class="qcreate-sim-choice-pill" id="simChoiceA" onclick="selectCorrectOption('A')">
                                <span class="sim-choice-text" id="simChoiceTextA">(A) success</span>
                                <span class="sim-radio"></span>
                                <svg class="sim-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="qcreate-sim-choice-pill active" id="simChoiceB" onclick="selectCorrectOption('B')">
                                <span class="sim-choice-text" id="simChoiceTextB">(B) successful</span>
                                <span class="sim-radio"></span>
                                <svg class="sim-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="qcreate-sim-choice-pill" id="simChoiceC" onclick="selectCorrectOption('C')">
                                <span class="sim-choice-text" id="simChoiceTextC">(C) successfully</span>
                                <span class="sim-radio"></span>
                                <svg class="sim-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="qcreate-sim-choice-pill" id="simChoiceD" onclick="selectCorrectOption('D')">
                                <span class="sim-choice-text" id="simChoiceTextD">(D) succeed</span>
                                <span class="sim-radio"></span>
                                <svg class="sim-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                        </div>

                        {{-- Accordion giải thích chi tiết & bản dịch --}}
                        <div class="qcreate-sim-accordion">
                            <button type="button" class="qcreate-sim-accordion-btn" onclick="toggleSimExplanation()">
                                <span style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <span>Xem giải thích chi tiết & Bản dịch</span>
                                </span>
                                <svg id="accordionArrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                            <div class="qcreate-sim-accordion-content" id="simExplanationContent" style="display: none;">
                                <div id="simExplanationText">
                                    Vị trí cần điền đứng sau phó từ "extraordinarily" và sau tobe "was", do đó cần một tính từ bổ nghĩa cho "campaign". Đáp án (B) successful là chính xác.
                                </div>
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
                <span>Đã lưu tự động lúc <strong id="autoSaveClock">14:32:05</strong></span>
            </div>
            <div class="qcreate-bottom-actions">
                <a href="{{ route('admin.questions.index') }}" class="qcreate-btn-cancel">
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
    // 1. Cập nhật số từ / ký tự thời gian thực
    function updateCounter(inputId, counterId) {
        var el = document.getElementById(inputId);
        var counter = document.getElementById(counterId);
        if (!el || !counter) return;
        var text = el.value || '';
        var words = text.trim() ? text.trim().split(/\s+/).length : 0;
        var chars = text.length;
        counter.textContent = 'Độ dài: ' + words + ' từ / ' + chars + ' ký tự';
    }

    // 2. Chèn chỗ trống ------- tại vị trí con trỏ
    function insertBlank(textareaId) {
        var textarea = document.getElementById(textareaId);
        if (!textarea) return;
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var text = textarea.value;
        var blankStr = '-------';
        textarea.value = text.substring(0, start) + blankStr + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + blankStr.length;
        textarea.focus();
        if (textareaId === 'contentInput') {
            handleContentInput();
        } else {
            handleExplanationInput();
        }
    }

    // 3. Bao bọc text đã chọn
    function wrapText(textareaId, openTag, closeTag) {
        var textarea = document.getElementById(textareaId);
        if (!textarea) return;
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var selected = textarea.value.substring(start, end);
        var replacement = openTag + (selected || 'nội dung') + closeTag;
        textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
        textarea.focus();
        if (textareaId === 'contentInput') {
            handleContentInput();
        } else {
            handleExplanationInput();
        }
    }

    // 4. Lắng nghe thay đổi nội dung đề bài -> cập nhật Simulator
    function handleContentInput() {
        updateCounter('contentInput', 'contentCounter');
        var val = document.getElementById('contentInput').value || '';
        var simBody = document.getElementById('simQuestionBody');
        if (simBody) {
            // Thay thế ------- bằng badge tím nổi bật
            var formatted = val
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/-------/g, '<span class="qcreate-sim-blank-highlight">-------</span>');
            simBody.innerHTML = formatted || '<em>(Chưa có nội dung câu hỏi)</em>';
        }
    }

    // 5. Lắng nghe thay đổi giải thích
    function handleExplanationInput() {
        updateCounter('explanationInput', 'explanationCounter');
        var expVal = document.getElementById('explanationInput').value || '';
        var simExpText = document.getElementById('simExplanationText');
        if (simExpText) {
            simExpText.textContent = expVal || 'Chưa có giải thích.';
        }
    }

    // 6. Đồng bộ 4 phương án sang Simulator
    function syncSimChoices() {
        var opts = ['A', 'B', 'C', 'D'];
        opts.forEach(function(letter) {
            var input = document.getElementById('optInput' + letter);
            var simText = document.getElementById('simChoiceText' + letter);
            if (input && simText) {
                var text = input.value.trim();
                simText.textContent = '(' + letter + ') ' + (text || '...');
            }
        });
    }

    // 7. Chọn đáp án đúng
    function selectCorrectOption(letter) {
        document.getElementById('correctOptionInput').value = letter;
        var opts = ['A', 'B', 'C', 'D'];
        opts.forEach(function(l) {
            var row = document.getElementById('optRow' + l);
            var simPill = document.getElementById('simChoice' + l);
            var btnText = row ? row.querySelector('.opt-btn-text') : null;
            var isChosen = (l === letter);

            if (row) {
                row.classList.toggle('is-correct', isChosen);
                if (btnText) {
                    btnText.textContent = isChosen ? 'ĐÁP ÁN ĐÚNG' : 'Chọn đúng';
                }
            }
            if (simPill) {
                simPill.classList.toggle('active', isChosen);
            }
        });
    }

    // 8. Cập nhật header câu hỏi trên Simulator
    function updateSimHeader() {
        var seq = document.getElementById('sequenceNoInput').value || '108';
        var secSelect = document.getElementById('sectionSelect');
        var partName = secSelect && secSelect.options[secSelect.selectedIndex] ? secSelect.options[secSelect.selectedIndex].text : 'Part 5';

        // Tách nhãn Part ngắn gọn
        var match = partName.match(/Part\s*\d+/i);
        var shortPart = match ? match[0] : partName;

        var simHeader = document.getElementById('simQuestionMeta');
        if (simHeader) {
            simHeader.textContent = 'Câu ' + seq + ' / ' + shortPart;
        }

        // Cập nhật khoảng câu hỏi badge gợi ý
        var badge = document.getElementById('partRangeBadge');
        if (badge) {
            if (shortPart.includes('5')) badge.textContent = '101-130';
            else if (shortPart.includes('6')) badge.textContent = '131-146';
            else if (shortPart.includes('7')) badge.textContent = '147-200';
            else if (shortPart.includes('1')) badge.textContent = '001-006';
            else if (shortPart.includes('2')) badge.textContent = '007-031';
            else if (shortPart.includes('3')) badge.textContent = '032-070';
            else if (shortPart.includes('4')) badge.textContent = '071-100';
            else badge.textContent = '1-200';
        }
    }

    // 9. Đồng bộ khi đổi kỹ năng
    function syncSkillPart() {
        updateSimHeader();
    }

    // 10. Xóa tag & Thêm tag
    function removeTag(tagId) {
        var tag = document.getElementById(tagId);
        if (tag) tag.remove();
    }

    function addNewTag() {
        var newTagName = prompt('Nhập tên thẻ tag chủ điểm ngữ pháp:', 'Mệnh đề quan hệ');
        if (!newTagName || !newTagName.trim()) return;
        var tagRow = document.querySelector('.qcreate-tags-row');
        var addBtn = document.querySelector('.qcreate-add-tag-btn');
        var span = document.createElement('span');
        span.className = 'qcreate-grammar-tag';
        span.innerHTML = '<span>' + newTagName.trim() + '</span><span class="remove-tag" onclick="this.parentElement.remove()" title="Xóa tag">✕</span>';
        tagRow.insertBefore(span, addBtn);
    }

    // 11. Cuộn đến Simulator
    function scrollToPreview() {
        var sim = document.getElementById('previewSimulatorSection');
        if (sim) {
            sim.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // 12. Toggle giải thích trong Simulator
    function toggleSimExplanation() {
        var content = document.getElementById('simExplanationContent');
        var arrow = document.getElementById('accordionArrow');
        if (!content) return;
        var isHidden = content.style.display === 'none';
        content.style.display = isHidden ? 'block' : 'none';
        if (arrow) {
            arrow.style.transform = isHidden ? 'rotate(180deg)' : 'none';
        }
    }

    // 13. Tabs đa phương tiện
    function switchMediaTab(tab) {
        ['Audio', 'Image', 'Passage'].forEach(function(t) {
            var el = document.getElementById('tab' + t);
            if (el) el.classList.remove('active');
        });
        if (tab === 'audio') document.getElementById('tabAudio').classList.add('active');
        if (tab === 'image') document.getElementById('tabImage').classList.add('active');
        if (tab === 'passage') document.getElementById('tabPassage').classList.add('active');
    }

    // 14. Demo phát audio
    var isPlaying = false;
    function togglePlayAudio() {
        isPlaying = !isPlaying;
        var btn = document.getElementById('playBtnToggle');
        if (!btn) return;
        if (isPlaying) {
            btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>';
            btn.title = "Tạm dừng";
        } else {
            btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>';
            btn.title = "Nghe thử audio";
        }
    }

    // 15. Gợi ý AI
    function triggerAiSuggest() {
        alert('Trợ lý AI StudyPrep: Đã phát hiện cấu trúc "was extraordinarily [adjective]". Khuyến nghị chọn phương án Tính từ (B) successful.');
    }

    // Khởi tạo ban đầu
    document.addEventListener('DOMContentLoaded', function() {
        handleContentInput();
        handleExplanationInput();
        syncSimChoices();
        updateSimHeader();
    });
</script>
@endpush
