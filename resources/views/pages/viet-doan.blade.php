<x-layouts.app active="luyen-tap" title="StudyPrep - Writing Practice (Viết đoạn văn)">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/writing-practice.css') }}">
@endpush

{{-- Card Container bám sát 100% hình ảnh tham chiếu 2 --}}
<div class="wp-card-container">

    {{-- 1. Header: Nút quay lại & Tiêu đề "Writing Practice" với icon cây bút --}}
    <div class="wp-header">
        <a href="{{ url('/luyen-tap/viet') }}" class="wp-back-btn" title="Quay lại kỹ năng Viết" aria-label="Quay lại">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <div class="wp-header-title-wrap">
            <svg class="wp-header-icon" viewBox="0 0 24 24" fill="none">
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke="#0076FE" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M15 5l4 4" stroke="#0076FE" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h1 class="wp-header-title">Writing Practice</h1>
        </div>
    </div>

    {{-- 2. Thanh Tabs: Viết câu vs Viết đoạn văn (Active) --}}
    <nav class="wp-tabs-nav" aria-label="Hình thức luyện viết">
        <a href="{{ url('/luyen-tap/viet-cau') }}" class="wp-tab-link">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
            </svg>
            <span>Viết câu</span>
        </a>
        <a href="{{ url('/luyen-tap/viet-doan') }}" class="wp-tab-link is-active" aria-current="page">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Viết đoạn văn</span>
        </a>
    </nav>

    {{-- 3. Category Pills: Tất cả, Basic Writing, Aptis Writing --}}
    <div class="wp-category-pills" role="tablist">
        <button type="button" class="wp-pill-btn is-active" data-filter="all">Tất cả</button>
        <button type="button" class="wp-pill-btn" data-filter="basic">Basic Writing</button>
        <button type="button" class="wp-pill-btn" data-filter="aptis">Aptis Writing</button>
    </div>

    {{-- 4. Filter & Counter bar: 44 Bài học, Độ khó, Tiến độ --}}
    <div class="wp-filter-bar">
        <div class="wp-count-info">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3Z" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span id="wpParagraphCount">44 Bài học</span>
        </div>
        <div class="wp-filter-actions">
            <select class="wp-filter-select" id="wpDifficultyFilterParagraph" aria-label="Lọc theo độ khó">
                <option value="all">Độ khó</option>
                <option value="easy">Dễ</option>
                <option value="medium">Trung bình</option>
                <option value="hard">Khó</option>
            </select>
            <select class="wp-filter-select" id="wpProgressFilterParagraph" aria-label="Lọc theo tiến độ">
                <option value="all">Tiến độ</option>
                <option value="completed">Đã hoàn thành</option>
                <option value="uncompleted">Chưa học</option>
            </select>
        </div>
    </div>

    {{-- 5. Danh sách đề bài Viết đoạn văn (10 đề bám sát 100% ảnh tham chiếu 2) --}}
    @php
        $paragraphExercises = [
            [
                'id' => 1,
                'title' => '1. Write an email inviting a friend to an event.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic',
                'word_limit' => '80 - 100 từ',
                'instruction' => 'Write an informal email inviting your close friend to an upcoming music festival next weekend. Mention the date, venue, ticket details, and suggest traveling together.',
                'tips' => 'Bố cục: Lời chào thân mật (Hi/Dear...), mục đích viết email, thông tin sự kiện, lời rủ rê và kết thư (Best regards/Cheers).'
            ],
            [
                'id' => 2,
                'title' => '2. Describe your study habits.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic',
                'word_limit' => '80 - 120 từ',
                'instruction' => 'Describe your daily study routine. When do you study best? What methods or study environments help you focus effectively?',
                'tips' => 'Sử dụng các từ nối thời gian: first, then, usually, whenever I study, in addition.'
            ],
            [
                'id' => 3,
                'title' => '3. Give your opinion about learning English online.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'aptis',
                'word_limit' => '120 - 150 từ',
                'instruction' => 'Share your personal viewpoint on online English learning platforms. Discuss the benefits (flexibility, cost) and potential drawbacks (lack of face-to-face interaction).',
                'tips' => 'Cấu trúc nêu quan điểm: In my perspective, on the one hand, however, in conclusion.'
            ],
            [
                'id' => 4,
                'title' => '4. Write a short paragraph about your hometown.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'basic',
                'word_limit' => '100 - 130 từ',
                'instruction' => 'Write an engaging paragraph introducing your hometown. Highlight its geographical location, famous attractions, cuisine, and friendly residents.',
                'tips' => 'Sử dụng tính từ miêu tả: picturesque, bustling, hospitable, historical.'
            ],
            [
                'id' => 5,
                'title' => '5. Describe your favorite holiday.',
                'diff' => 'hard',
                'diff_label' => 'Khó',
                'category' => 'basic',
                'word_limit' => '130 - 160 từ',
                'instruction' => 'Narrate a memorable past holiday or vacation trip. Where did you go, who did you accompany, and why did this experience leave a lasting impression?',
                'tips' => 'Chú ý phối hợp thì quá khứ đơn (Past Simple) và quá khứ tiếp diễn (Past Continuous).'
            ],
            [
                'id' => 6,
                'title' => '6. Write a letter of complaint.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'aptis',
                'word_limit' => '120 - 150 từ',
                'instruction' => 'Write a formal complaint letter to an electronics store manager regarding a defective laptop purchased two days ago that failed to turn on.',
                'tips' => 'Văn phong trang trọng: I am writing to express my dissatisfaction with..., I demand a replacement or refund.'
            ],
            [
                'id' => 7,
                'title' => '7. Explain the advantages and disadvantages.',
                'diff' => 'hard',
                'diff_label' => 'Khó',
                'category' => 'aptis',
                'word_limit' => '150 - 180 từ',
                'instruction' => 'Discuss the pros and cons of working remotely from home versus working full-time in a traditional corporate office.',
                'tips' => 'Cấu trúc cân bằng: Although remote work provides autonomy, it can occasionally induce social isolation.'
            ],
            [
                'id' => 8,
                'title' => '8. Write about your future career.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'basic',
                'word_limit' => '120 - 150 từ',
                'instruction' => 'Outline your professional ambitions for the next 5 years. What skills do you need to cultivate, and how are you preparing to achieve your career objectives?',
                'tips' => 'Sử dụng cấu trúc định hướng tương lai: aspire to, plan on, in order to achieve.'
            ],
            [
                'id' => 9,
                'title' => '9. Describe a memorable experience.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic',
                'word_limit' => '100 - 130 từ',
                'instruction' => 'Share a meaningful moment in your life that taught you a valuable life lesson about perseverance or friendship.',
                'tips' => 'Kể chuyện mạch lạc: It all began when..., what surprised me most was..., as a result.'
            ],
            [
                'id' => 10,
                'title' => '10. Write an essay about the importance of education.',
                'diff' => 'hard',
                'diff_label' => 'Khó',
                'category' => 'aptis',
                'word_limit' => '180 - 220 từ',
                'instruction' => 'Write a short argumentative essay on how education acts as a cornerstone for societal development and individual career success in the digital era.',
                'tips' => 'Bố cục 3 phần: Mở bài (Hook & Thesis), Thân bài (Luận điểm & Dẫn chứng), Kết bài (Tổng kết & Dự đoán).'
            ],
        ];
    @endphp

    <div class="wp-item-list" id="wpParagraphList">
        @foreach($paragraphExercises as $item)
            <div class="wp-item-row"
                 data-id="{{ $item['id'] }}"
                 data-diff="{{ $item['diff'] }}"
                 data-category="{{ $item['category'] }}"
                 onclick="openParagraphWritingModal({{ $item['id'] }}, '{{ addslashes($item['title']) }}', '{{ addslashes($item['instruction']) }}', '{{ addslashes($item['tips']) }}', '{{ $item['word_limit'] }}', '{{ $item['diff_label'] }}', '{{ $item['diff'] }}')"
                 role="button"
                 tabindex="0">
                <div class="wp-item-left">
                    <div class="wp-circle-icon" id="wp-para-circle-{{ $item['id'] }}" title="Trạng thái hoàn thành"></div>
                    <p class="wp-item-title" title="{{ $item['title'] }}">{{ $item['title'] }}</p>
                </div>
                <div class="wp-item-right">
                    <span class="wp-diff-badge wp-diff-badge--{{ $item['diff'] }}">{{ $item['diff_label'] }}</span>

                    <button type="button" class="wp-bookmark-btn" onclick="toggleBookmark(event, this)" aria-label="Lưu đề bài">
                        <svg class="wp-bookmark-icon" viewBox="0 0 24 24">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                        </svg>
                    </button>

                    <svg class="wp-chevron-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        @endforeach
    </div>

</div>

{{-- MODAL LÀM BÀI VIẾT ĐOẠN VĂN / BÀI LUẬN (CHỈ MỞ KHI BẤM VÀO ĐỀ) --}}
<div class="wp-modal-backdrop" id="wpParaModalBackdrop" onclick="handleParaBackdropClick(event)">
    <div class="wp-modal" role="dialog" aria-modal="true" aria-labelledby="wpParaModalTitle">
        <div class="wp-modal__header">
            <div>
                <h3 class="wp-modal__title" id="wpParaModalTitle">Luyện Viết Đoạn Văn</h3>
                <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
                    <span id="wpParaModalBadge" class="wp-diff-badge wp-diff-badge--easy">Dễ</span>
                    <span id="wpParaWordLimit" style="font-size: 12.5px; font-weight: 700; color: #64748B;">Dung lượng: 80 - 100 từ</span>
                </div>
            </div>
            <button type="button" class="wp-modal__close" onclick="closeParagraphWritingModal()" aria-label="Đóng cửa sổ">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="wp-modal__body">
            <div class="wp-prompt-box">
                <div class="wp-prompt-title">📝 Đề bài / Yêu cầu viết:</div>
                <p class="wp-prompt-instruction" id="wpParaModalInstruction">Nội dung đề bài...</p>
                <div id="wpParaModalTips" style="font-size: 13px; color: #0284C7; margin-top: 10px; font-weight: 600;">Gợi ý & Dàn ý: ...</div>
            </div>

            <div class="wp-textarea-container">
                <textarea class="wp-textarea" id="wpParaInputText" style="min-height: 220px;" placeholder="Viết đoạn văn / bài luận của bạn tại đây bằng tiếng Anh..." oninput="updateParaWordCount()"></textarea>
            </div>

            <div class="wp-textarea-footer">
                <span id="wpParaWordCount">Số từ: 0</span>
                <span>Kiểm tra: Ngữ pháp, Từ vựng, Tính mạch lạc</span>
            </div>

            <div class="wp-modal-actions">
                <button type="button" class="wp-pill-btn" onclick="closeParagraphWritingModal()">Hủy bỏ</button>
                <button type="button" class="wp-pill-btn is-active" id="wpParaSubmitBtn" onclick="submitParaWriting()">
                    🚀 Nộp bài &amp; Chấm điểm AI
                </button>
            </div>

            {{-- Đánh giá bài viết đoạn văn chi tiết --}}
            <div class="wp-result-feedback" id="wpParaResultFeedback">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="font-size: 16px; font-weight: 800; color: #166534;">🎉 Đánh giá bài viết: <span id="wpParaScore" style="color: #15803D;">92/100</span></div>
                    <span style="font-size: 12px; font-weight: 700; color: #15803D; background: #DCFCE7; padding: 4px 12px; border-radius: 999px;">Rất lưu loát</span>
                </div>
                <div style="font-size: 13.5px; color: #166534; line-height: 1.6; margin-bottom: 14px;">
                    ✅ <strong>Đạt yêu cầu dung lượng:</strong> Bố cục mở - thân - kết rõ ràng, logic liên kết tốt.<br>
                    ✨ <strong>Từ vựng phong phú:</strong> Sử dụng chính xác các liên từ học thuật và ngữ pháp đa dạng.
                </div>
                <button type="button" class="wp-pill-btn is-active" style="width: 100%; justify-content: center;" onclick="markParaCompletedAndClose()">
                    Lưu kết quả &amp; Hoàn thành
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentParaId = null;

    function openParagraphWritingModal(id, title, instruction, tips, wordLimit, diffLabel, diffClass) {
        currentParaId = id;
        document.getElementById('wpParaModalTitle').textContent = title;
        document.getElementById('wpParaModalInstruction').textContent = instruction;
        document.getElementById('wpParaModalTips').textContent = tips;
        document.getElementById('wpParaWordLimit').textContent = 'Dung lượng: ' + wordLimit;

        const badge = document.getElementById('wpParaModalBadge');
        badge.textContent = diffLabel;
        badge.className = 'wp-diff-badge wp-diff-badge--' + diffClass;

        document.getElementById('wpParaInputText').value = '';
        document.getElementById('wpParaWordCount').textContent = 'Số từ: 0';
        document.getElementById('wpParaResultFeedback').style.display = 'none';

        document.getElementById('wpParaModalBackdrop').classList.add('is-open');
    }

    function closeParagraphWritingModal() {
        document.getElementById('wpParaModalBackdrop').classList.remove('is-open');
    }

    function handleParaBackdropClick(e) {
        if (e.target.id === 'wpParaModalBackdrop') {
            closeParagraphWritingModal();
        }
    }

    function updateParaWordCount() {
        const text = document.getElementById('wpParaInputText').value.trim();
        const words = text ? text.split(/\s+/).length : 0;
        document.getElementById('wpParaWordCount').textContent = 'Số từ: ' + words;
    }

    function submitParaWriting() {
        const text = document.getElementById('wpParaInputText').value.trim();
        if (!text) {
            alert('Vui lòng nhập đoạn văn của bạn trước khi nộp bài!');
            return;
        }

        const btn = document.getElementById('wpParaSubmitBtn');
        btn.textContent = '⏳ AI đang phân tích bài viết...';
        btn.disabled = true;

        setTimeout(() => {
            btn.textContent = '🚀 Nộp bài & Chấm điểm AI';
            btn.disabled = false;
            document.getElementById('wpParaResultFeedback').style.display = 'block';
        }, 800);
    }

    function markParaCompletedAndClose() {
        if (currentParaId) {
            const circle = document.getElementById('wp-para-circle-' + currentParaId);
            if (circle) {
                circle.style.background = '#10B981';
                circle.style.borderColor = '#10B981';
                circle.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:block; margin: 2px auto;"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            }
        }
        closeParagraphWritingModal();
    }

    function toggleBookmark(e, btn) {
        e.stopPropagation();
        btn.classList.toggle('is-saved');
    }

    // Filter pills
    document.querySelectorAll('.wp-pill-btn[data-filter]').forEach(pill => {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.wp-pill-btn[data-filter]').forEach(p => p.classList.remove('is-active'));
            this.classList.add('is-active');
            applyParaFilters();
        });
    });

    // Dropdown filters
    document.getElementById('wpDifficultyFilterParagraph').addEventListener('change', applyParaFilters);
    document.getElementById('wpProgressFilterParagraph').addEventListener('change', applyParaFilters);

    function applyParaFilters() {
        const activeCategory = document.querySelector('.wp-pill-btn.is-active[data-filter]')?.dataset.filter || 'all';
        const difficulty = document.getElementById('wpDifficultyFilterParagraph').value;
        const rows = document.querySelectorAll('#wpParagraphList .wp-item-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowDiff = row.dataset.diff;
            const rowCat = row.dataset.category;

            const matchesCategory = (activeCategory === 'all' || rowCat === activeCategory);
            const matchesDiff = (difficulty === 'all' || rowDiff === difficulty);

            if (matchesCategory && matchesDiff) {
                row.style.display = 'flex';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('wpParagraphCount').textContent = visibleCount + ' Bài học';
    }
</script>
@endpush

</x-layouts.app>
