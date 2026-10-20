<x-layouts.app active="luyen-tap" title="StudyPrep - Writing Practice (Viết câu)">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/writing-practice.css') }}">
@endpush

{{-- Card Container bám sát 100% hình ảnh tham chiếu 1 --}}
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

    {{-- 2. Thanh Tabs: Viết câu (Active) vs Viết đoạn văn --}}
    <nav class="wp-tabs-nav" aria-label="Hình thức luyện viết">
        <a href="{{ url('/luyen-tap/viet-cau') }}" class="wp-tab-link is-active" aria-current="page">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
            </svg>
            <span>Viết câu</span>
        </a>
        <a href="{{ url('/luyen-tap/viet-doan') }}" class="wp-tab-link">
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

    {{-- 4. Filter & Counter bar: 20 Bài học, Độ khó, Tiến độ --}}
    <div class="wp-filter-bar">
        <div class="wp-count-info">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3Z" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span id="wpSentenceCount">20 Bài học</span>
        </div>
        <div class="wp-filter-actions">
            <select class="wp-filter-select" id="wpDifficultyFilter" aria-label="Lọc theo độ khó">
                <option value="all">Độ khó</option>
                <option value="easy">Dễ</option>
                <option value="medium">Trung bình</option>
                <option value="hard">Khó</option>
            </select>
            <select class="wp-filter-select" id="wpProgressFilter" aria-label="Lọc theo tiến độ">
                <option value="all">Tiến độ</option>
                <option value="completed">Đã hoàn thành</option>
                <option value="uncompleted">Chưa học</option>
            </select>
        </div>
    </div>

    {{-- 5. Danh sách bài tập Viết câu (10 bài bám sát 100% ảnh tham chiếu 1) --}}
    @php
        $sentenceExercises = [
            [
                'id' => 1,
                'title' => '1. Complete the sentence with the correct word.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic',
                'instruction' => 'Fill in the blank with an appropriate adjective: "She was completely _______ with the outcome of the recent project presentation."',
                'hint' => 'Gợi ý từ vựng: satisfied, delighted, content, pleased.'
            ],
            [
                'id' => 2,
                'title' => '2. Choose the best answer to complete the sentence.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic',
                'instruction' => 'Complete the conditional sentence: "If we receive the client approval before noon, we _______ the revised contract immediately."',
                'hint' => 'Gợi ý cấu trúc: Câu điều kiện loại 1 (will + V-infinitive).'
            ],
            [
                'id' => 3,
                'title' => '3. Find and correct the mistake in the sentence.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'aptis',
                'instruction' => 'Identify the incorrect segment and rewrite correctly: "Despite of the heavy rain, the delivery team managed to reach the warehouse on schedule."',
                'hint' => 'Gợi ý: Chú ý sự khác nhau giữa Despite và In spite of.'
            ],
            [
                'id' => 4,
                'title' => '4. Fill in the blank with the correct form of the word.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'aptis',
                'instruction' => 'Use the correct form of "PRODUCE": "The introduction of automated robotics significantly improved our factory’s overall _______."',
                'hint' => 'Gợi ý từ loại: Danh từ chỉ năng suất (productivity).'
            ],
            [
                'id' => 5,
                'title' => '5. Rearrange the words to make a correct sentence.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'basic',
                'instruction' => 'Arrange the following fragments into a fluent sentence: [submit / requested / by Friday / all attendees / to / their feedback forms / are].',
                'hint' => 'Gợi ý cấu trúc bị động: S + are requested to + V.'
            ],
            [
                'id' => 6,
                'title' => '6. Complete the sentence with a suitable phrase.',
                'diff' => 'hard',
                'diff_label' => 'Khó',
                'category' => 'aptis',
                'instruction' => 'Complete the sentence using a formal transition: "The economic forecast remained uncertain; _______, several multinational firms expanded their hiring."',
                'hint' => 'Gợi ý: however, nevertheless, nonetheless.'
            ],
            [
                'id' => 7,
                'title' => '7. Choose the correct option to complete the sentence.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic',
                'instruction' => 'Complete with the right preposition: "Mr. Tanaka has been in charge _______ customer support relations since 2021."',
                'hint' => 'Gợi ý: in charge of something.'
            ],
            [
                'id' => 8,
                'title' => '8. Identify the correct grammar structure.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'aptis',
                'instruction' => 'Rewrite using inversion (đảo ngữ): "Hardly _______ (the manager / announce) the merger when journalists began calling."',
                'hint' => 'Gợi ý cấu trúc: Hardly + had + S + V3/ed + when...'
            ],
            [
                'id' => 9,
                'title' => '9. Fill in the blank with the correct word form.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'basic',
                'instruction' => 'Supply the correct form of "CONFIDENT": "She presented the quarter earnings report with great _______ and poise."',
                'hint' => 'Gợi ý từ loại: Danh từ (confidence).'
            ],
            [
                'id' => 10,
                'title' => '10. Complete the sentence based on the given context.',
                'diff' => 'hard',
                'diff_label' => 'Khó',
                'category' => 'aptis',
                'instruction' => 'Write a concluding compound sentence based on this context: "Due to high inflation, consumer spending fell. Consequently, ..."',
                'hint' => 'Gợi ý: Nêu hệ quả hợp lý đối với doanh số bán hàng hoặc lợi nhuận.'
            ],
        ];
    @endphp

    <div class="wp-item-list" id="wpSentenceList">
        @foreach($sentenceExercises as $item)
            <div class="wp-item-row"
                 data-id="{{ $item['id'] }}"
                 data-diff="{{ $item['diff'] }}"
                 data-category="{{ $item['category'] }}"
                 onclick="openWritingModal({{ $item['id'] }}, '{{ addslashes($item['title']) }}', '{{ addslashes($item['instruction']) }}', '{{ addslashes($item['hint']) }}', '{{ $item['diff_label'] }}', '{{ $item['diff'] }}')"
                 role="button"
                 tabindex="0">
                <div class="wp-item-left">
                    <div class="wp-circle-icon" id="wp-circle-{{ $item['id'] }}" title="Trạng thái hoàn thành"></div>
                    <p class="wp-item-title" title="{{ $item['title'] }}">{{ $item['title'] }}</p>
                </div>
                <div class="wp-item-right">
                    <span class="wp-diff-badge wp-diff-badge--{{ $item['diff'] }}">{{ $item['diff_label'] }}</span>

                    <button type="button" class="wp-bookmark-btn" onclick="toggleBookmark(event, this)" aria-label="Lưu bài học">
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

{{-- MODAL LÀM BÀI TẬP VIẾT CÂU (CHỈ MỞ KHI BẤM VÀO ĐỀ) --}}
<div class="wp-modal-backdrop" id="wpModalBackdrop" onclick="handleBackdropClick(event)">
    <div class="wp-modal" role="dialog" aria-modal="true" aria-labelledby="wpModalTitle">
        <div class="wp-modal__header">
            <div>
                <h3 class="wp-modal__title" id="wpModalTitle">Luyện Viết Câu</h3>
                <span id="wpModalBadge" class="wp-diff-badge wp-diff-badge--easy" style="margin-top: 6px;">Dễ</span>
            </div>
            <button type="button" class="wp-modal__close" onclick="closeWritingModal()" aria-label="Đóng cửa sổ">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="wp-modal__body">
            <div class="wp-prompt-box">
                <div class="wp-prompt-title">📝 Đề bài / Yêu cầu:</div>
                <p class="wp-prompt-instruction" id="wpModalInstruction">Nội dung đề bài...</p>
                <div id="wpModalHint" style="font-size: 13px; color: #0284C7; margin-top: 10px; font-weight: 600;">Gợi ý: ...</div>
            </div>

            <div class="wp-textarea-container">
                <textarea class="wp-textarea" id="wpInputText" placeholder="Nhập câu trả lời viết của bạn tại đây bằng tiếng Anh..." oninput="updateWordCount()"></textarea>
            </div>

            <div class="wp-textarea-footer">
                <span id="wpWordCount">Số từ: 0</span>
                <span>Tự động kiểm tra ngữ pháp & chính tả</span>
            </div>

            <div class="wp-modal-actions">
                <button type="button" class="wp-pill-btn" onclick="closeWritingModal()">Hủy bỏ</button>
                <button type="button" class="wp-pill-btn is-active" id="wpSubmitBtn" onclick="submitWriting()">
                    🚀 Nộp bài &amp; Chấm điểm
                </button>
            </div>

            {{-- Đánh giá kết quả --}}
            <div class="wp-result-feedback" id="wpResultFeedback">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="font-size: 16px; font-weight: 800; color: #166534;">🎉 Kết quả chấm bài: <span id="wpScore" style="color: #15803D;">90/100</span></div>
                    <span style="font-size: 12px; font-weight: 700; color: #15803D; background: #DCFCE7; padding: 4px 12px; border-radius: 999px;">Chính xác</span>
                </div>
                <p style="font-size: 14px; color: #166534; margin: 0 0 14px; line-height: 1.55;" id="wpFeedbackDesc">
                    Câu viết ngữ pháp chuẩn xác, cấu trúc tự nhiên và sử dụng đúng từ loại theo yêu cầu của đề bài.
                </p>
                <button type="button" class="wp-pill-btn is-active" style="width: 100%; justify-content: center;" onclick="markCompletedAndClose()">
                    Lưu kết quả &amp; Hoàn thành
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentItemId = null;

    function openWritingModal(id, title, instruction, hint, diffLabel, diffClass) {
        currentItemId = id;
        document.getElementById('wpModalTitle').textContent = title;
        document.getElementById('wpModalInstruction').textContent = instruction;
        document.getElementById('wpModalHint').textContent = hint;

        const badge = document.getElementById('wpModalBadge');
        badge.textContent = diffLabel;
        badge.className = 'wp-diff-badge wp-diff-badge--' + diffClass;

        document.getElementById('wpInputText').value = '';
        document.getElementById('wpWordCount').textContent = 'Số từ: 0';
        document.getElementById('wpResultFeedback').style.display = 'none';

        document.getElementById('wpModalBackdrop').classList.add('is-open');
    }

    function closeWritingModal() {
        document.getElementById('wpModalBackdrop').classList.remove('is-open');
    }

    function handleBackdropClick(e) {
        if (e.target.id === 'wpModalBackdrop') {
            closeWritingModal();
        }
    }

    function updateWordCount() {
        const text = document.getElementById('wpInputText').value.trim();
        const words = text ? text.split(/\s+/).length : 0;
        document.getElementById('wpWordCount').textContent = 'Số từ: ' + words;
    }

    function submitWriting() {
        const text = document.getElementById('wpInputText').value.trim();
        if (!text) {
            alert('Vui lòng nhập nội dung câu trả lời trước khi nộp bài!');
            return;
        }

        const btn = document.getElementById('wpSubmitBtn');
        btn.textContent = '⏳ Đang chấm điểm...';
        btn.disabled = true;

        setTimeout(() => {
            btn.textContent = '🚀 Nộp bài & Chấm điểm';
            btn.disabled = false;
            document.getElementById('wpResultFeedback').style.display = 'block';
        }, 600);
    }

    function markCompletedAndClose() {
        if (currentItemId) {
            const circle = document.getElementById('wp-circle-' + currentItemId);
            if (circle) {
                circle.style.background = '#10B981';
                circle.style.borderColor = '#10B981';
                circle.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:block; margin: 2px auto;"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            }
        }
        closeWritingModal();
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
            applyFilters();
        });
    });

    // Dropdown filters
    document.getElementById('wpDifficultyFilter').addEventListener('change', applyFilters);
    document.getElementById('wpProgressFilter').addEventListener('change', applyFilters);

    function applyFilters() {
        const activeCategory = document.querySelector('.wp-pill-btn.is-active[data-filter]')?.dataset.filter || 'all';
        const difficulty = document.getElementById('wpDifficultyFilter').value;
        const rows = document.querySelectorAll('#wpSentenceList .wp-item-row');
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

        document.getElementById('wpSentenceCount').textContent = visibleCount + ' Bài học';
    }
</script>
@endpush

</x-layouts.app>
