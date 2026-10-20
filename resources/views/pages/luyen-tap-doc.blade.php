<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudyPrep - Luyện kỹ năng Đọc (Reading)</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{-- Nunito & Instrument Sans Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:400,600,700,800|instrument-sans:400,500,600,700">

    {{-- Core Design System & Responsive Framework --}}
    <link rel="stylesheet" href="{{ asset('css/studyprep-core.css') }}">

    {{-- Trang Luyện Theo Kỹ Năng CSS (Bám sát thiết kế mẫu) --}}
    <link rel="stylesheet" href="{{ asset('css/pages/ky-nang.css') }}">
</head>
<body>

    <main class="sp-skill-page">
        <div class="sp-skill-card">

            {{-- 1. Header: Nút quay lại & Tiêu đề --}}
            <div class="sp-skill-header">
                <a href="{{ url('/luyen-tap') }}" class="sp-skill-back-btn" title="Quay lại trang Luyện tập" aria-label="Quay lại Luyện tập">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M15 19l-7-7 7-7" stroke="#111C2D" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <h1 class="sp-skill-title">Luyện theo Kỹ năng</h1>
            </div>

            {{-- 2. Thanh điều hướng các kỹ năng (Skill Tabs) --}}
            <nav class="sp-skill-tabs" role="tablist" aria-label="Danh sách kỹ năng">
                {{-- Tab 1: NGHE --}}
                <button type="button" class="sp-skill-tab-item" data-tab="nghe" role="tab" aria-selected="false">
                    <div class="sp-skill-tab-icon sp-skill-tab-icon--nghe">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 4a8 8 0 0 0-8 8v3a3 3 0 0 0 3 3h1a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H5.07A7 7 0 0 1 19 12v.07A1 1 0 0 0 18 13h-1a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h1a3 3 0 0 0 3-3v-3a8 8 0 0 0-8-8Z" fill="white"/>
                        </svg>
                    </div>
                    <span>NGHE</span>
                </button>

                {{-- Tab 2: ĐỌC (Mặc định đang chọn theo yêu cầu) --}}
                <button type="button" class="sp-skill-tab-item is-active" data-tab="doc" role="tab" aria-selected="true">
                    <div class="sp-skill-tab-icon sp-skill-tab-icon--doc">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5A2.5 2.5 0 0 1 4 19.5v-15ZM6.5 4A.5.5 0 0 0 6 4.5v15a.5.5 0 0 0 .5.5H19V4H6.5Z" fill="white"/>
                            <path d="M9 8h6M9 12h6M9 16h4" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span>ĐỌC</span>
                </button>

                {{-- Tab 3: NÓI (Chuyển trực tiếp sang trang Luyện nói toàn màn hình) --}}
                <a href="{{ url('/luyen-tap/noi') }}" class="sp-skill-tab-item" role="tab" aria-selected="false" style="text-decoration: none;">
                    <div class="sp-skill-tab-icon sp-skill-tab-icon--noi">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 2a4 4 0 0 1 4 4v6a4 4 0 0 1-8 0V6a4 4 0 0 1 4-4Z" fill="white"/>
                            <path d="M6 10a6 6 0 0 0 12 0M12 18v4M8 22h8" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span>NÓI</span>
                </a>

                {{-- Tab 4: VIẾT (Chuyển sang trang Luyện theo kỹ năng - Viết) --}}
                <a href="{{ url('/luyen-tap/viet') }}" class="sp-skill-tab-item" role="tab" aria-selected="false" style="text-decoration: none;">
                    <div class="sp-skill-tab-icon sp-skill-tab-icon--viet">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M17 3a2.83 2.83 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z" fill="white"/>
                        </svg>
                    </div>
                    <span>VIẾT</span>
                </a>
            </nav>

            {{-- 3. Danh sách bài tập theo kỹ năng --}}

            {{-- PANEL: ĐỌC (Active mặc định) --}}
            <div id="panel-doc" class="sp-skill-panel is-active" role="tabpanel" aria-labelledby="tab-doc">
                <div class="sp-exercise-list">
                    {{-- Bài 1: Đọc Câu (Part 5 / Read Aloud Câu) --}}
                    <div class="sp-exercise-card" onclick="window.location.href='{{ url('/luyen-tap/doc-cau') }}'" role="button" tabindex="0">
                        <div class="sp-exercise-card__main">
                            <div class="sp-exercise-icon-wrap sp-exercise-icon-wrap--mint">
                                {{-- SVG icon bookmark & document giống thẻ 1 trong ảnh --}}
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M19 4H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M15 2v4M9 2v4M7 11h10M7 15h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="sp-exercise-text">
                                <h3 class="sp-exercise-title">Đọc Câu</h3>
                                <p class="sp-exercise-desc">Luyện ngữ pháp với câu, hoàn thành câu và chọn đáp án chính xác.</p>
                            </div>
                        </div>
                        <svg class="sp-exercise-chevron" viewBox="0 0 24 24" fill="none">
                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    {{-- Bài 2: Đọc Đoạn văn (Part 6 / Read Aloud Đoạn) --}}
                    <div class="sp-exercise-card" onclick="window.location.href='{{ url('/luyen-tap/doc-doan') }}'" role="button" tabindex="0">
                        <div class="sp-exercise-card__main">
                            <div class="sp-exercise-icon-wrap sp-exercise-icon-wrap--lavender">
                                {{-- SVG icon document & text giống thẻ 2 trong ảnh --}}
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="sp-exercise-text">
                                <h3 class="sp-exercise-title">Đọc Đoạn văn</h3>
                                <p class="sp-exercise-desc">Luyện đọc hiểu với đoạn văn ngắn, điền từ ngữ cảnh và liên kết câu.</p>
                            </div>
                        </div>
                        <svg class="sp-exercise-chevron" viewBox="0 0 24 24" fill="none">
                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    {{-- Bài 3: Đọc Hiểu Văn bản (Part 7) --}}
                    <div class="sp-exercise-card" onclick="openPracticeModal('part7')" role="button" tabindex="0">
                        <div class="sp-exercise-card__main">
                            <div class="sp-exercise-icon-wrap sp-exercise-icon-wrap--amber">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 7h6M9 11h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="sp-exercise-text">
                                <h3 class="sp-exercise-title">Đọc Hiểu Văn bản</h3>
                                <p class="sp-exercise-desc">Luyện kỹ năng Skimming & Scanning với thư từ, email, bài báo và thông báo.</p>
                            </div>
                        </div>
                        <svg class="sp-exercise-chevron" viewBox="0 0 24 24" fill="none">
                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- PANEL: NÓI (Giữ nguyên chuẩn 100% theo ảnh tham chiếu) --}}
            <div id="panel-noi" class="sp-skill-panel" role="tabpanel" aria-labelledby="tab-noi">
                <div class="sp-exercise-list">
                    {{-- Bài 1: Đọc Câu --}}
                    <div class="sp-exercise-card" onclick="window.location.href='{{ url('/luyen-tap/noi') }}'" role="button" tabindex="0">
                        <div class="sp-exercise-card__main">
                            <div class="sp-exercise-icon-wrap sp-exercise-icon-wrap--mint">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M19 4H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M15 2v4M9 2v4M7 11h10M7 15h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="sp-exercise-text">
                                <h3 class="sp-exercise-title">Đọc Câu</h3>
                                <p class="sp-exercise-desc">Luyện phát âm với câu, chấm điểm và chữa lỗi phát âm.</p>
                            </div>
                        </div>
                        <svg class="sp-exercise-chevron" viewBox="0 0 24 24" fill="none">
                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    {{-- Bài 2: Đọc Đoạn văn --}}
                    <div class="sp-exercise-card" onclick="window.location.href='{{ url('/luyen-tap/noi') }}'" role="button" tabindex="0">
                        <div class="sp-exercise-card__main">
                            <div class="sp-exercise-icon-wrap sp-exercise-icon-wrap--lavender">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="sp-exercise-text">
                                <h3 class="sp-exercise-title">Đọc Đoạn văn</h3>
                                <p class="sp-exercise-desc">Luyện phát âm với đoạn văn ngắn, chấm điểm và chữa lỗi phát âm.</p>
                            </div>
                        </div>
                        <svg class="sp-exercise-chevron" viewBox="0 0 24 24" fill="none">
                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- PANEL: NGHE --}}
            <div id="panel-nghe" class="sp-skill-panel" role="tabpanel" aria-labelledby="tab-nghe">
                <div class="sp-exercise-list">
                    <div class="sp-exercise-card" onclick="openPracticeModal('nghe-1')" role="button" tabindex="0">
                        <div class="sp-exercise-card__main">
                            <div class="sp-exercise-icon-wrap sp-exercise-icon-wrap--lavender">
                                <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/><path d="m21 15-5-5L5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </div>
                            <div class="sp-exercise-text">
                                <h3 class="sp-exercise-title">Mô tả hình ảnh (Part 1)</h3>
                                <p class="sp-exercise-desc">Luyện nghe mô tả tranh ảnh, nhận diện bẫy hành động và vị trí không gian.</p>
                            </div>
                        </div>
                        <svg class="sp-exercise-chevron" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>

                    <div class="sp-exercise-card" onclick="openPracticeModal('nghe-2')" role="button" tabindex="0">
                        <div class="sp-exercise-card__main">
                            <div class="sp-exercise-icon-wrap sp-exercise-icon-wrap--mint">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div class="sp-exercise-text">
                                <h3 class="sp-exercise-title">Hỏi &amp; Đáp (Part 2)</h3>
                                <p class="sp-exercise-desc">Luyện phản xạ nghe câu ngắn, câu hỏi Wh-, Yes/No và câu trả lời gián tiếp.</p>
                            </div>
                        </div>
                        <svg class="sp-exercise-chevron" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>

                    <div class="sp-exercise-card" onclick="openPracticeModal('nghe-3')" role="button" tabindex="0">
                        <div class="sp-exercise-card__main">
                            <div class="sp-exercise-icon-wrap sp-exercise-icon-wrap--amber">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </div>
                            <div class="sp-exercise-text">
                                <h3 class="sp-exercise-title">Hội thoại &amp; Bài nói (Part 3 &amp; 4)</h3>
                                <p class="sp-exercise-desc">Luyện nghe đoạn hội thoại công sở, bảng biểu và bài nói ngắn đa giọng đọc.</p>
                            </div>
                        </div>
                        <svg class="sp-exercise-chevron" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>
            </div>

            {{-- PANEL: VIẾT --}}
            <div id="panel-viet" class="sp-skill-panel" role="tabpanel" aria-labelledby="tab-viet">
                <div class="sp-exercise-list">
                    <div class="sp-exercise-card" onclick="window.location.href='{{ url('/luyen-tap/viet-cau') }}'" role="button" tabindex="0">
                        <div class="sp-exercise-card__main">
                            <div class="sp-exercise-icon-wrap sp-exercise-icon-wrap--amber">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div class="sp-exercise-text">
                                <h3 class="sp-exercise-title">Viết câu theo tranh</h3>
                                <p class="sp-exercise-desc">Luyện cấu trúc câu ngữ pháp, liên từ và từ loại dựa trên hình ảnh mô tả.</p>
                            </div>
                        </div>
                        <svg class="sp-exercise-chevron" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>

                    <div class="sp-exercise-card" onclick="window.location.href='{{ url('/luyen-tap/viet-doan') }}'" role="button" tabindex="0">
                        <div class="sp-exercise-card__main">
                            <div class="sp-exercise-icon-wrap sp-exercise-icon-wrap--mint">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </div>
                            <div class="sp-exercise-text">
                                <h3 class="sp-exercise-title">Phản hồi yêu cầu bằng văn bản (Email)</h3>
                                <p class="sp-exercise-desc">Luyện viết thư tín thương mại, phản hồi câu hỏi và khiếu nại của khách hàng.</p>
                            </div>
                        </div>
                        <svg class="sp-exercise-chevron" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>
            </div>

        </div>
    </main>

    {{-- 4. Modal Luyện tập trực quan (Interactive Practice Session) --}}
    <div id="practiceModal" class="sp-practice-modal-backdrop" onclick="closePracticeModal(event)" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="sp-practice-modal" onclick="event.stopPropagation()">
            <div class="sp-practice-modal__header">
                <div>
                    <span class="sp-badge sp-badge--primary" id="modalBadge">Kỹ năng Đọc • TOEIC Reading</span>
                    <h2 class="sp-practice-modal__title" id="modalTitle" style="margin-top: 6px;">Đang tải bài tập...</h2>
                </div>
                <button type="button" class="sp-practice-modal__close" onclick="closePracticeModal()" aria-label="Đóng">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="sp-practice-modal__body" id="modalContent">
                {{-- Nội dung bài tập được render bằng JavaScript --}}
            </div>

            <div class="sp-practice-modal__footer">
                <button type="button" class="sp-tab-btn" onclick="closePracticeModal()">Thoát bài làm</button>
                <button type="button" class="sp-tab-btn active" id="modalSubmitBtn" onclick="submitAnswer()">Kiểm tra đáp án</button>
            </div>
        </div>
    </div>

    {{-- JavaScript điều hướng tab & tương tác bài tập --}}
    <script>
        // Xử lý chuyển đổi Tab (NGHE, ĐỌC, NÓI, VIẾT)
        document.querySelectorAll('.sp-skill-tab-item').forEach(function(tab) {
            tab.addEventListener('click', function() {
                var targetSkill = this.getAttribute('data-tab');

                // Cập nhật trạng thái active tab
                document.querySelectorAll('.sp-skill-tab-item').forEach(function(item) {
                    item.classList.remove('is-active');
                    item.setAttribute('aria-selected', 'false');
                });
                this.classList.add('is-active');
                this.setAttribute('aria-selected', 'true');

                // Hiển thị panel tương ứng
                document.querySelectorAll('.sp-skill-panel').forEach(function(panel) {
                    panel.classList.remove('is-active');
                });
                var targetPanel = document.getElementById('panel-' + targetSkill);
                if (targetPanel) {
                    targetPanel.classList.add('is-active');
                }
            });
        });

        // Dữ liệu câu hỏi mẫu cho các bài tập Đọc
        var practiceData = {
            'part5': {
                title: 'Đọc Câu – Part 5: Hoàn thành câu',
                badge: 'Part 5 • Ngữ pháp & Từ vựng',
                content: `
                    <div style="background: #F8FAFC; border: 1px solid #EEF1F7; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                        <span style="font-size: 13px; font-weight: 700; color: #64748B;">Câu hỏi 101 / 130:</span>
                        <p style="font-size: 16px; font-weight: 700; color: #111C2D; margin: 8px 0 0; line-height: 1.5;">
                            Mr. Henderson requested that all expense reports be submitted _______ Friday afternoon.
                        </p>
                    </div>
                    <div>
                        <div class="sp-question-option" onclick="selectOption(this, 'A')"><strong>A.</strong> within</div>
                        <div class="sp-question-option" onclick="selectOption(this, 'B')"><strong>B.</strong> before</div>
                        <div class="sp-question-option" onclick="selectOption(this, 'C')"><strong>C.</strong> until</div>
                        <div class="sp-question-option" onclick="selectOption(this, 'D')"><strong>D.</strong> along</div>
                    </div>
                    <div id="answerExplanation" style="display: none; margin-top: 18px; padding: 14px; border-radius: 10px; background: #ECFDF5; border: 1px solid #BBF7D0; font-size: 14px; color: #065F46;">
                        <strong>✅ Giải thích chi tiết:</strong> Đáp án <strong>B. before</strong> là chính xác. "before Friday afternoon" nghĩa là "trước chiều thứ Sáu". Cấu trúc request that + clause (nguyên thể).
                    </div>
                `,
                correct: 'B'
            },
            'part6': {
                title: 'Đọc Đoạn văn – Part 6: Điền đoạn văn',
                badge: 'Part 6 • Đoạn văn ngắn',
                content: `
                    <div style="background: #F8FAFC; border: 1px solid #EEF1F7; border-radius: 12px; padding: 18px; margin-bottom: 20px; font-size: 14px; line-height: 1.6; color: #334155;">
                        <h4 style="margin: 0 0 8px; color: #111C2D;">To: All Marketing Personnel</h4>
                        Please be reminded that our quarterly review meeting has been rescheduled. <br>
                        The presentation room has been moved to Floor 4 to _______ the large audience expected.
                    </div>
                    <div>
                        <div class="sp-question-option" onclick="selectOption(this, 'A')"><strong>A.</strong> accommodate</div>
                        <div class="sp-question-option" onclick="selectOption(this, 'B')"><strong>B.</strong> accommodation</div>
                        <div class="sp-question-option" onclick="selectOption(this, 'C')"><strong>C.</strong> accommodating</div>
                        <div class="sp-question-option" onclick="selectOption(this, 'D')"><strong>D.</strong> accommodates</div>
                    </div>
                    <div id="answerExplanation" style="display: none; margin-top: 18px; padding: 14px; border-radius: 10px; background: #ECFDF5; border: 1px solid #BBF7D0; font-size: 14px; color: #065F46;">
                        <strong>✅ Giải thích chi tiết:</strong> Đáp án <strong>A. accommodate</strong> là chính xác. Sau "to" là động từ nguyên thể để chỉ mục đích ("to accommodate" = để đáp ứng/chứa đủ).
                    </div>
                `,
                correct: 'A'
            },
            'part7': {
                title: 'Đọc Hiểu Văn bản – Part 7: Reading Comprehension',
                badge: 'Part 7 • Đọc hiểu văn bản',
                content: `
                    <div style="background: #F8FAFC; border: 1px solid #EEF1F7; border-radius: 12px; padding: 18px; margin-bottom: 20px; font-size: 14px; line-height: 1.6; color: #334155;">
                        <div style="font-weight: 800; color: #111C2D; margin-bottom: 6px;">[Email Notice] Apex Global Logistics</div>
                        We are thrilled to announce the opening of our new distribution center in Da Nang. With over 20,000 square meters of state-of-the-art storage, we will cut delivery times to Central Vietnam by 48 hours.
                    </div>
                    <p style="font-weight: 700; color: #111C2D; margin-bottom: 12px;">What is the main purpose of the announcement?</p>
                    <div>
                        <div class="sp-question-option" onclick="selectOption(this, 'A')"><strong>A.</strong> To announce a relocation of company headquarters</div>
                        <div class="sp-question-option" onclick="selectOption(this, 'B')"><strong>B.</strong> To report on new facility opening and faster delivery</div>
                        <div class="sp-question-option" onclick="selectOption(this, 'C')"><strong>C.</strong> To advertise job vacancies in Da Nang</div>
                        <div class="sp-question-option" onclick="selectOption(this, 'D')"><strong>D.</strong> To apologize for shipping delays</div>
                    </div>
                    <div id="answerExplanation" style="display: none; margin-top: 18px; padding: 14px; border-radius: 10px; background: #ECFDF5; border: 1px solid #BBF7D0; font-size: 14px; color: #065F46;">
                        <strong>✅ Giải thích chi tiết:</strong> Đáp án <strong>B</strong> là chính xác ("opening of our new distribution center... cut delivery times by 48 hours").
                    </div>
                `,
                correct: 'B'
            }
        };

        var currentSelectedOption = null;
        var currentExerciseKey = 'part5';

        function openPracticeModal(type) {
            currentExerciseKey = type;
            var data = practiceData[type] || practiceData['part5'];
            document.getElementById('modalTitle').textContent = data.title;
            document.getElementById('modalBadge').textContent = data.badge;
            document.getElementById('modalContent').innerHTML = data.content;
            document.getElementById('practiceModal').classList.add('is-open');
            document.body.style.overflow = 'hidden';
            currentSelectedOption = null;
        }

        function closePracticeModal(event) {
            if (event && event.target !== document.getElementById('practiceModal') && !event.target.classList.contains('sp-practice-modal__close') && !event.target.classList.contains('sp-tab-btn')) {
                return;
            }
            document.getElementById('practiceModal').classList.remove('is-open');
            document.body.style.overflow = '';
        }

        function selectOption(el, opt) {
            document.querySelectorAll('.sp-question-option').forEach(function(item) {
                item.classList.remove('is-selected');
            });
            el.classList.add('is-selected');
            currentSelectedOption = opt;
        }

        function submitAnswer() {
            if (!currentSelectedOption) {
                alert('Vui lòng chọn một đáp án (A, B, C hoặc D) trước khi kiểm tra!');
                return;
            }
            var expl = document.getElementById('answerExplanation');
            if (expl) {
                expl.style.display = 'block';
                expl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        // Đóng modal bằng phím ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePracticeModal();
            }
        });

        // Hỗ trợ chọn tab theo Query Parameter ?skill=... (mặc định 'doc')
        var urlParams = new URLSearchParams(window.location.search);
        var initialSkill = urlParams.get('skill') || 'doc';
        var activeTabBtn = document.querySelector('.sp-skill-tab-item[data-tab="' + initialSkill + '"]');
        if (activeTabBtn) {
            activeTabBtn.click();
        }
    </script>
</body>
</html>
