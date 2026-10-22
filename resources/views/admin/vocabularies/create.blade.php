@extends('layouts.admin', ['active' => 'tu-vung-flashcard'])

@section('title', 'Thêm từ vựng & Thẻ học mới')

@section('top_breadcrumb')
    <span>Hệ thống</span>
    <span class="crumb-arrow">></span>
    <span>Quản lý đào tạo</span>
    <span class="crumb-arrow">></span>
    <a href="{{ route('admin.vocabularies.index') }}" style="color: inherit; text-decoration: none;">Từ vựng & Flashcards</a>
    <span class="crumb-arrow">></span>
    <span style="color: #4f46e5; font-weight: 700;">Thêm từ vựng mới</span>
@endsection

@section('content')
<div class="vc-page">
    <form method="POST" action="{{ route('admin.vocabularies.store') }}" id="createVocabForm">
        @csrf
        <input type="hidden" name="action_type" id="formActionType" value="publish">
        <input type="hidden" name="level" id="inputTargetLevel" value="Target 800+ (High B2 / C1)">

        {{-- Top Header --}}
        <div class="vc-header">
            <div>
                <h1 class="vc-title">Thêm từ vựng & Thẻ học mới</h1>
            </div>

            <div class="vc-top-actions">
                {{-- Nhập hàng loạt --}}
                <a href="{{ route('admin.vocabularies.import-view') }}" class="vc-btn-white" title="Quay lại danh sách hoặc nhập file">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    <span>Nhập hàng loạt (Excel/CSV)</span>
                </a>

                {{-- Xem trước thẻ học --}}
                <button type="button" class="vc-btn-white" onclick="scrollToPreview()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>Xem trước thẻ học</span>
                </button>

                {{-- Lưu bản nháp --}}
                <button type="button" class="vc-btn-white" onclick="submitAsDraft()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span>Lưu bản nháp</span>
                </button>

                {{-- Lưu & Xuất bản --}}
                <button type="submit" class="vc-btn-publish">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                    </svg>
                    <span>Lưu & Xuất bản vào bộ thẻ</span>
                </button>
            </div>
        </div>

        {{-- Validation Errors Alert --}}
        @if (isset($errors) && $errors->any())
            <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 13.5px;">
                <div style="font-weight: 700; margin-bottom: 4px;">Vui lòng kiểm tra lại thông tin:</div>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main 2-Column Split Layout --}}
        <div class="vc-grid">
            {{-- Left Column: Forms --}}
            <div class="vc-left-col">
                {{-- Section 1: Thông tin cơ bản & Định danh từ vựng --}}
                <div class="vc-card">
                    <div class="vc-card-head">
                        <div class="vc-card-head-left">
                            <div class="vc-card-icon-wrap vc-icon-purple">
                                <span>A_</span>
                            </div>
                            <div class="vc-card-titles">
                                <h3>1. Thông tin cơ bản & Định danh từ vựng</h3>
                                <p>Từ khóa trọng tâm, ngữ âm quốc tế và phân nhóm phân loại học thuật</p>
                            </div>
                        </div>
                        <span class="vc-badge-required">BẮT BUỘC</span>
                    </div>

                    {{-- Row 1: Word & POS --}}
                    <div class="vc-row-2 vc-form-group">
                        <div>
                            <label class="vc-label" for="inputWord">
                                <span>Từ vựng / Cụm từ (Word / Phrase) <span style="color: #ef4444;">*</span></span>
                            </label>
                            <div class="vc-input-wrap">
                                <input
                                    type="text"
                                    id="inputWord"
                                    name="word"
                                    class="vc-input"
                                    value="{{ old('word', 'substantially') }}"
                                    required
                                    placeholder="Ví dụ: substantially"
                                    autocomplete="off"
                                    oninput="updateCharCount(this); syncLivePreview();"
                                >
                                <span class="vc-input-counter" id="wordCharCounter">13 ký tự</span>
                            </div>
                        </div>

                        <div>
                            <label class="vc-label" for="selectPos">
                                <span>Từ loại chính (Part of Speech) <span style="color: #ef4444;">*</span></span>
                            </label>
                            <select id="selectPos" name="part_of_speech" class="vc-select" onchange="syncLivePreview()">
                                <option value="Adverb (Phó từ / Trạng từ)" selected>Adverb (Phó từ / Trạng từ)</option>
                                <option value="Noun (Danh từ)">Noun (Danh từ)</option>
                                <option value="Verb (Động từ)">Verb (Động từ)</option>
                                <option value="Adjective (Tính từ)">Adjective (Tính từ)</option>
                                <option value="Collocation (Cụm từ cố định)">Collocation (Cụm từ cố định)</option>
                                <option value="Noun Phrase (Cụm danh từ)">Noun Phrase (Cụm danh từ)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Row 2: US IPA --}}
                    <div class="vc-form-group">
                        <label class="vc-label" for="inputIpa">
                            <span class="vc-label-left">
                                <span class="vc-dot-red"></span>
                                <span>Phiên âm Anh - Mỹ (US IPA)</span>
                            </span>
                        </label>
                        <div class="vc-input-wrap">
                            <input
                                type="text"
                                id="inputIpa"
                                name="pronunciation"
                                class="vc-input"
                                value="{{ old('pronunciation', '/səbˈstæn.ʃəl.i/') }}"
                                placeholder="Ví dụ: /səbˈstæn.ʃəl.i/"
                                oninput="syncLivePreview()"
                            >
                            <button type="button" class="vc-btn-audio-tag" onclick="playCurrentWordAudio()">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                </svg>
                                <span>US Audio</span>
                            </button>
                        </div>
                    </div>

                    {{-- Row 3: Target Score / Levels --}}
                    <div class="vc-form-group">
                        <label class="vc-label">
                            <span>Mục tiêu điểm thi TOEIC & Khung năng lực</span>
                            <span style="font-size: 11.5px; color: #64748b; font-weight: normal;">Gắn nhãn độ khó tự động cho thuật toán học viên</span>
                        </label>
                        <div class="vc-targets-grid">
                            <div class="vc-target-pill" onclick="selectLevel('Target 450+ (CEFR B1)', this)">Target 450+ (CEFR B1)</div>
                            <div class="vc-target-pill" onclick="selectLevel('Target 650+ (CEFR B2)', this)">Target 650+ (CEFR B2)</div>
                            <div class="vc-target-pill active" onclick="selectLevel('Target 800+ (High B2 / C1)', this)">Target 800+ (High B2 / C1)</div>
                            <div class="vc-target-pill" onclick="selectLevel('Target 900+ (Mastery C1+)', this)">Target 900+ (Mastery C1+)</div>
                        </div>
                    </div>

                    {{-- Row 4: Assign to Deck --}}
                    <div class="vc-form-group" style="margin-bottom: 0;">
                        <label class="vc-label" for="selectTopic">
                            <span>Gán vào Bộ thẻ Flashcard <span style="color: #ef4444;">*</span></span>
                        </label>
                        <div class="vc-input-wrap">
                            <select id="selectTopic" name="topic_id" class="vc-select">
                                @foreach ($topics as $t)
                                    <option value="{{ $t->topic_id }}" {{ str_contains($t->name, 'Finance') || str_contains($t->name, 'ETS') ? 'selected' : '' }}>
                                        {{ $t->name }} {{ $t->description ? ' (' . \Illuminate\Support\Str::limit($t->description, 28) . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Nghĩa, Giải thích & Ví dụ ngữ cảnh TOEIC --}}
                <div class="vc-card">
                    <div class="vc-card-head">
                        <div class="vc-card-head-left">
                            <div class="vc-card-icon-wrap vc-icon-teal">
                                <span>文</span>
                            </div>
                            <div class="vc-card-titles">
                                <h3>2. Nghĩa, Giải thích & Ví dụ ngữ cảnh TOEIC</h3>
                                <p>Định nghĩa song ngữ và trích dẫn chuẩn đề ETS Part 5 / Part 7</p>
                            </div>
                        </div>
                        <span class="vc-badge-ai">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                            </svg>
                            <span>Tự động sinh bằng AI + Sẵn sàng</span>
                        </span>
                    </div>

                    {{-- Row 1: English & Vietnamese definitions --}}
                    <div class="vc-row-2 vc-form-group">
                        <div>
                            <label class="vc-label" for="inputDefEn">
                                <span>Định nghĩa Tiếng Anh (English Definition) <span style="color: #ef4444;">*</span></span>
                            </label>
                            <textarea
                                id="inputDefEn"
                                name="definition_en"
                                rows="3"
                                class="vc-textarea"
                                oninput="syncLivePreview()"
                                placeholder="To a great or significant degree; considerably."
                            >{{ old('definition_en', 'To a great or significant degree; considerably.') }}</textarea>
                        </div>

                        <div>
                            <label class="vc-label" for="inputMeaningVi">
                                <span>Nghĩa tiếng Việt chuẩn xác (Vietnamese Meaning) <span style="color: #ef4444;">*</span></span>
                            </label>
                            <textarea
                                id="inputMeaningVi"
                                name="meaning_vi"
                                rows="3"
                                class="vc-textarea"
                                required
                                oninput="syncLivePreview()"
                                placeholder="Đáng kể, về căn bản, rất nhiều (biểu thị mức độ lớn)."
                            >{{ old('meaning_vi', 'Đáng kể, về căn bản, rất nhiều (biểu thị mức độ lớn).') }}</textarea>
                        </div>
                    </div>

                    {{-- Row 2: TOEIC Example sentence --}}
                    <div class="vc-example-card">
                        <div class="vc-example-topbar">
                            <div class="vc-example-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 18h6M10 22h4M15 2a6 6 0 0 0-6 6c0 2.22 1.21 4.16 3 5.2V17h4v-3.8c1.79-1.04 3-2.98 3-5.2a6 6 0 0 0-6-6z"></path>
                                </svg>
                                <span>Câu ví dụ thực tế trong đề thi TOEIC</span>
                            </div>

                            <div class="vc-example-actions">
                                <button type="button" class="vc-btn-tiny" onclick="boldKeyword()">
                                    <strong>B</strong>
                                    <span>Bôi đậm từ khóa</span>
                                </button>
                                <button type="button" class="vc-btn-tiny" onclick="appendAnotherExample()">
                                    <span>+ Thêm ví dụ</span>
                                </button>
                            </div>
                        </div>

                        <textarea
                            id="inputExEn"
                            name="example_en"
                            rows="2"
                            class="vc-textarea"
                            style="margin-bottom: 8px; font-weight: 500;"
                            placeholder="Nhập câu tiếng Anh trích dẫn từ đề thi..."
                            oninput="syncLivePreview()"
                        >{{ old('example_en', 'Operating profits increased substantially following the implementation of the new automated supply chain strategy.') }}</textarea>

                        <input
                            type="text"
                            id="inputExVi"
                            name="example_vi"
                            class="vc-input"
                            style="font-size: 13px; color: #475569;"
                            value="{{ old('example_vi', 'Lợi nhuận hoạt động đã tăng lên đáng kể sau khi triển khai chiến lược chuỗi cung ứng tự động mới.') }}"
                            placeholder="Dịch nghĩa câu ví dụ..."
                            oninput="syncLivePreview()"
                        >
                    </div>

                    {{-- Row 3: Synonyms & Antonyms --}}
                    <div class="vc-row-2 vc-form-group">
                        {{-- Synonyms --}}
                        <div class="vc-syn-card">
                            <div class="vc-syn-head">
                                <span class="vc-syn-label">Từ đồng nghĩa (Synonyms)</span>
                                <span class="vc-tag-badge-blue">Thường gặp ở Part 7</span>
                            </div>
                            <div class="vc-tags-wrap" id="synTagsWrap">
                                <span class="vc-tag-pill-teal">
                                    <span>significantly</span>
                                    <button type="button" class="vc-tag-close" onclick="this.parentElement.remove()">&times;</button>
                                </span>
                                <span class="vc-tag-pill-teal">
                                    <span>considerably</span>
                                    <button type="button" class="vc-tag-close" onclick="this.parentElement.remove()">&times;</button>
                                </span>
                                <span class="vc-tag-pill-teal">
                                    <span>markedly</span>
                                    <button type="button" class="vc-tag-close" onclick="this.parentElement.remove()">&times;</button>
                                </span>
                                <button type="button" class="vc-tag-add-btn" onclick="addTagPrompt('synTagsWrap', 'teal')">+ Thêm từ...</button>
                            </div>
                        </div>

                        {{-- Antonyms --}}
                        <div class="vc-syn-card">
                            <div class="vc-syn-head">
                                <span class="vc-syn-label">Từ trái nghĩa (Antonyms)</span>
                                <span class="vc-tag-badge-red">Bẫy trắc nghiệm</span>
                            </div>
                            <div class="vc-tags-wrap" id="antTagsWrap">
                                <span class="vc-tag-pill-rose">
                                    <span>slightly</span>
                                    <button type="button" class="vc-tag-close" onclick="this.parentElement.remove()">&times;</button>
                                </span>
                                <span class="vc-tag-pill-rose">
                                    <span>negligibly</span>
                                    <button type="button" class="vc-tag-close" onclick="this.parentElement.remove()">&times;</button>
                                </span>
                                <span class="vc-tag-pill-rose">
                                    <span>insignificantly</span>
                                    <button type="button" class="vc-tag-close" onclick="this.parentElement.remove()">&times;</button>
                                </span>
                                <button type="button" class="vc-tag-add-btn" onclick="addTagPrompt('antTagsWrap', 'rose')">+ Thêm từ...</button>
                            </div>
                        </div>
                    </div>

                    {{-- Row 4: Word Family Table --}}
                    <div class="vc-family-section">
                        <div class="vc-family-topbar">
                            <div class="vc-family-title">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                                    <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                                    <line x1="6" y1="6" x2="6.01" y2="6"></line>
                                    <line x1="6" y1="18" x2="6.01" y2="18"></line>
                                </svg>
                                <span>Họ từ vựng liên quan (Word Family - Trọng tâm Part 5 TOEIC)</span>
                            </div>
                            <button type="button" class="vc-family-add" onclick="addFamilyRow()">
                                <span>+ Thêm từ phái sinh</span>
                            </button>
                        </div>

                        <div class="vc-family-table-wrap">
                            <table class="vc-family-table" id="familyTable">
                                <thead>
                                    <tr>
                                        <th>TỪ PHÁI SINH</th>
                                        <th>TỪ LOẠI</th>
                                        <th>Ý NGHĨA VẮN TẮT</th>
                                        <th style="width: 60px; text-align: center;">HÀNH ĐỘNG</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-weight: 700; color: #4338ca;">substantial</td>
                                        <td><span class="vc-family-pos-badge">Adjective (adj)</span></td>
                                        <td>Lớn lao, quan trọng, có giá trị thực</td>
                                        <td style="text-align: center;">
                                            <button type="button" class="vc-btn-trash" onclick="this.closest('tr').remove()" title="Xóa dòng">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: 700; color: #4338ca;">substance</td>
                                        <td><span class="vc-family-pos-badge">Noun (n)</span></td>
                                        <td>Chất liệu, bản chất vấn đề, thực chất</td>
                                        <td style="text-align: center;">
                                            <button type="button" class="vc-btn-trash" onclick="this.closest('tr').remove()" title="Xóa dòng">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: 700; color: #4338ca;">substantiality</td>
                                        <td><span class="vc-family-pos-badge">Noun (n)</span></td>
                                        <td>Tính chất thực tế, sự vững chắc</td>
                                        <td style="text-align: center;">
                                            <button type="button" class="vc-btn-trash" onclick="this.closest('tr').remove()" title="Xóa dòng">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Preview & Intelligence Widgets --}}
            <div class="vc-right-col" id="previewCol">
                {{-- Widget 1: Mô phỏng Thẻ học (Live Preview) --}}
                <div class="vc-widget">
                    <div class="vc-widget-head">
                        <div class="vc-widget-title-row">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                <line x1="12" y1="18" x2="12.01" y2="18"></line>
                            </svg>
                            <span class="vc-widget-title">Mô phỏng Thẻ học (Live Preview)</span>
                        </div>
                        <span class="vc-widget-badge-subtle">Student Mode</span>
                    </div>

                    <div class="vc-phone-card">
                        <div class="vc-phone-tabs">
                            <button type="button" class="vc-phone-tab active" id="tabFrontPreview" onclick="setPreviewFace(false)">Mặt trước (Word)</button>
                            <button type="button" class="vc-phone-tab" id="tabBackPreview" onclick="setPreviewFace(true)">Mặt sau (Meaning)</button>
                        </div>

                        <div class="vc-phone-body" id="phonePreviewBox">
                            {{-- Topline --}}
                            <div class="vc-phone-topline">
                                <span class="vc-phone-pos-pill" id="previewPosBadge">ADV • 800+</span>
                                <button type="button" class="vcb-audio-btn" onclick="playCurrentWordAudio()" title="Nghe phát âm">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                    </svg>
                                </button>
                            </div>

                            {{-- Illustration Banner --}}
                            <img
                                id="previewBannerImg"
                                src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='320' height='120' viewBox='0 0 320 120'><rect width='320' height='120' fill='%23f8fafc'/><rect x='20' y='20' width='160' height='90' rx='4' fill='%23e2e8f0'/><rect x='30' y='30' width='140' height='65' rx='2' fill='%23334155'/><polyline points='40,75 70,55 100,65 140,40' fill='none' stroke='%2338bdf8' stroke-width='4' stroke-linecap='round'/><circle cx='230' cy='60' r='28' fill='%23dbeafe'/><path d='M220 60 L240 60 M230 50 L230 70' stroke='%233b82f6' stroke-width='3'/></svg>"
                                alt="Banner"
                                class="vc-phone-banner"
                            >

                            {{-- Front Face Content --}}
                            <div id="previewFrontFace" style="width: 100%;">
                                <div class="vc-phone-word" id="previewWord">substantially</div>
                                <div class="vc-phone-ipa" id="previewIpa">/səbˈstæn.ʃəl.i/</div>
                            </div>

                            {{-- Back Face Content --}}
                            <div id="previewBackFace" style="width: 100%; display: none; text-align: left; padding: 4px 6px;">
                                <div style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 4px;" id="previewMeaningVi">
                                    Đáng kể, về căn bản, rất nhiều (biểu thị mức độ lớn).
                                </div>
                                <div style="font-size: 11.5px; color: #64748b; font-style: italic; line-height: 1.35;" id="previewDefEn">
                                    To a great or significant degree; considerably.
                                </div>
                            </div>

                            <div class="vc-phone-foot" onclick="togglePreviewFace()">
                                <span>Chạm để lật xem nghĩa • Flip</span>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="23 4 23 10 17 10"></polyline>
                                    <polyline points="1 20 1 14 7 14"></polyline>
                                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Widget 2: AI Kiểm định chất lượng từ --}}
                <div class="vc-widget">
                    <div class="vc-widget-head">
                        <div class="vc-widget-title-row">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                <polyline points="9 12 11 14 15 10"></polyline>
                            </svg>
                            <span class="vc-widget-title">AI Kiểm định chất lượng từ</span>
                        </div>
                        <span class="vc-badge-ai" style="background: #ccfbf1; color: #0f766e;">Đạt chuẩn 98/100</span>
                    </div>

                    <div class="vc-ai-score-box">
                        <div class="vc-score-circle">
                            <span>98</span>
                        </div>
                        <div class="vc-score-details">
                            <h4>Độ hoàn thiện Flashcard xuất sắc</h4>
                            <p>Đáp ứng đầy đủ ngữ âm, audio 192kbps, ví dụ chuẩn ngữ cảnh ETS và họ từ phái sinh.</p>
                        </div>
                    </div>

                    <div class="vc-check-list">
                        <div class="vc-check-item">
                            <span>Kiểm tra chuẩn IPA & Audio:</span>
                            <span class="vc-check-val-blue">100% Khớp</span>
                        </div>
                        <div>
                            <div class="vc-check-item">
                                <span>Tần suất xuất hiện đề thi TOEIC:</span>
                                <span class="vc-check-val-blue">95% (Rất cao – Part 5/7)</span>
                            </div>
                            <div class="vc-progress-bg">
                                <div class="vc-progress-fill"></div>
                            </div>
                        </div>
                    </div>

                    <div class="vc-ai-suggestion-box">
                        <div style="font-weight: 700; margin-bottom: 3px; display: flex; align-items: center; gap: 4px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                            <span>Gợi ý tối ưu từ AI:</span>
                        </div>
                        <div>
                            Nên bổ sung Collocation hay gặp: <strong>"substantially lower/higher"</strong> và <strong>"differ substantially"</strong> để tối ưu hoá điểm cho học viên Part 5.
                        </div>
                    </div>
                </div>

                {{-- Widget 3: Cấu hình Spaced Repetition --}}
                <div class="vc-widget">
                    <div class="vc-widget-head">
                        <div class="vc-widget-title-row">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            <span class="vc-widget-title">Cấu hình Spaced Repetition</span>
                        </div>
                        <span class="vc-widget-badge-subtle">SM-2 Algorithm</span>
                    </div>

                    <div class="vc-sm2-grid">
                        <div class="vc-sm2-card">
                            <div class="vc-sm2-card-label">Độ khó khởi tạo (Ease)</div>
                            <div class="vc-sm2-card-val">2.5</div>
                        </div>
                        <div class="vc-sm2-card">
                            <div class="vc-sm2-card-label">Khoảng cách lặp (Interval)</div>
                            <div class="vc-sm2-card-val">1d → 6d → 15d</div>
                        </div>
                    </div>
                </div>

                {{-- Widget 4: Metadata box --}}
                <div class="vc-meta-box">
                    <div class="vc-meta-row">
                        <div>
                            <span class="vc-status-dot"></span>
                            <span>Đang đồng bộ nháp</span>
                        </div>
                        <span style="font-weight: 600;">14:32:05 hôm nay</span>
                    </div>
                    <div class="vc-meta-row">
                        <span>Người biên soạn:</span>
                        <span style="font-weight: 600; color: #1e293b;">ThS. Hoàng Minh Tuấn (TOEIC Academic)</span>
                    </div>
                    <div class="vc-meta-row">
                        <span>Trạng thái kiểm duyệt:</span>
                        <span style="color: #4338ca; font-weight: 600;">Đã qua thẩm định tự động</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Live Character Counter
    function updateCharCount(input) {
        var count = input.value.length;
        document.getElementById('wordCharCounter').innerText = count + ' ký tự';
    }

    // Level Target Pill selector
    function selectLevel(levelName, element) {
        document.querySelectorAll('.vc-target-pill').forEach(function(pill) {
            pill.classList.remove('active');
        });
        element.classList.add('active');
        document.getElementById('inputTargetLevel').value = levelName;

        // Extract short level tag e.g. 800+, 650+
        var match = levelName.match(/(\d+\+)/);
        var shortLvl = match ? match[1] : '800+';
        var pos = document.getElementById('selectPos').value;
        var shortPos = 'ADV';
        if (pos.includes('Noun')) shortPos = 'NOUN';
        else if (pos.includes('Verb')) shortPos = 'VERB';
        else if (pos.includes('Adjective')) shortPos = 'ADJ';
        else if (pos.includes('Collocation')) shortPos = 'COLLOC';
        document.getElementById('previewPosBadge').innerText = shortPos + ' • ' + shortLvl;
    }

    // Play word pronunciation
    function playCurrentWordAudio() {
        var word = document.getElementById('inputWord').value.trim();
        if (!word) return;
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            var utterance = new SpeechSynthesisUtterance(word);
            utterance.lang = 'en-US';
            utterance.rate = 0.88;
            window.speechSynthesis.speak(utterance);
        } else {
            alert('Trình duyệt không hỗ trợ Web Speech API.');
        }
    }

    // Synchronize form inputs to Live Preview Widget
    function syncLivePreview() {
        var word = document.getElementById('inputWord').value.trim() || 'substantially';
        var ipa = document.getElementById('inputIpa').value.trim() || '/səbˈstæn.ʃəl.i/';
        var meaningVi = document.getElementById('inputMeaningVi').value.trim() || 'Đáng kể, về căn bản, rất nhiều...';
        var defEn = document.getElementById('inputDefEn').value.trim() || 'To a great or significant degree; considerably.';

        document.getElementById('previewWord').innerText = word;
        document.getElementById('previewIpa').innerText = ipa;
        document.getElementById('previewMeaningVi').innerText = meaningVi;
        document.getElementById('previewDefEn').innerText = defEn;
    }

    // Toggle Preview card face
    var isBackFacing = false;
    function setPreviewFace(toBack) {
        isBackFacing = toBack;
        document.getElementById('tabFrontPreview').classList.toggle('active', !toBack);
        document.getElementById('tabBackPreview').classList.toggle('active', toBack);
        document.getElementById('previewFrontFace').style.display = toBack ? 'none' : 'block';
        document.getElementById('previewBackFace').style.display = toBack ? 'block' : 'none';
    }

    function togglePreviewFace() {
        setPreviewFace(!isBackFacing);
    }

    function scrollToPreview() {
        document.getElementById('previewCol').scrollIntoView({ behavior: 'smooth' });
    }

    function submitAsDraft() {
        document.getElementById('formActionType').value = 'draft';
        document.getElementById('createVocabForm').submit();
    }

    // Toolbar helper: Bôi đậm từ khóa
    function boldKeyword() {
        var textarea = document.getElementById('inputExEn');
        var word = document.getElementById('inputWord').value.trim();
        if (!word || !textarea) return;

        var regex = new RegExp('(' + word + ')', 'gi');
        if (regex.test(textarea.value)) {
            textarea.value = textarea.value.replace(regex, '**$1**');
        } else {
            alert('Từ khóa "' + word + '" không có trong câu ví dụ.');
        }
    }

    function appendAnotherExample() {
        var textarea = document.getElementById('inputExEn');
        var additional = prompt('Nhập thêm câu ví dụ tiếng Anh:');
        if (additional && additional.trim()) {
            textarea.value = textarea.value.trim() + '\n' + additional.trim();
        }
    }

    // Add Tag Prompt
    function addTagPrompt(containerId, color) {
        var val = prompt('Nhập từ mới:');
        if (!val || !val.trim()) return;

        var container = document.getElementById(containerId);
        var span = document.createElement('span');
        span.className = color === 'teal' ? 'vc-tag-pill-teal' : 'vc-tag-pill-rose';
        span.innerHTML = '<span>' + val.trim() + '</span><button type="button" class="vc-tag-close" onclick="this.parentElement.remove()">&times;</button>';

        // Insert before the "+ Thêm từ..." button
        var addBtn = container.querySelector('.vc-tag-add-btn');
        container.insertBefore(span, addBtn);
    }

    // Add Word Family Row
    function addFamilyRow() {
        var word = prompt('Nhập từ phái sinh (ví dụ: substantial):');
        if (!word || !word.trim()) return;
        var pos = prompt('Nhập loại từ (ví dụ: Adjective (adj)):', 'Adjective (adj)');
        var meaning = prompt('Nhập ý nghĩa vắn tắt:', 'Lớn lao, có giá trị thực');

        var tbody = document.getElementById('familyTable').querySelector('tbody');
        var tr = document.createElement('tr');
        tr.innerHTML = '<td style="font-weight: 700; color: #4338ca;">' + word.trim() + '</td>' +
                       '<td><span class="vc-family-pos-badge">' + (pos || 'Từ vựng') + '</span></td>' +
                       '<td>' + (meaning || '') + '</td>' +
                       '<td style="text-align: center;"><button type="button" class="vc-btn-trash" onclick="this.closest(\'tr\').remove()">&times;</button></td>';
        tbody.appendChild(tr);
    }
</script>
@endpush
@endsection
