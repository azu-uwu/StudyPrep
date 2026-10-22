<x-layouts.app active="luyen-tap" title="StudyPrep - Luyện Đọc Câu (Read Aloud)">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/read-aloud.css') }}">
@endpush

{{-- Card Container bám sát 100% hình ảnh tham chiếu --}}
<div class="ra-card-container">

    {{-- 1. Header: Nút quay lại & Tiêu đề "Read Aloud" với icon sách mở --}}
    <div class="ra-header">
        <a href="{{ url('/luyen-tap/noi') }}" class="ra-back-btn" title="Quay lại luyện kỹ năng Nói" aria-label="Quay lại">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <div class="ra-header-title-wrap">
            <svg class="ra-header-icon" viewBox="0 0 24 24" fill="none">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="#0076FE" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" stroke="#0076FE" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8 7h8M8 11h5" stroke="#0076FE" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            <h1 class="ra-header-title">Read Aloud</h1>
        </div>
    </div>

    {{-- 2. Thanh Tabs: Đọc Câu (Active) vs Đọc Đoạn --}}
    <nav class="ra-tabs-nav" aria-label="Hình thức luyện tập">
        <a href="{{ url('/luyen-tap/doc-cau') }}" class="ra-tab-link is-active" aria-current="page">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
            </svg>
            <span>Đọc Câu</span>
        </a>
        <a href="{{ url('/luyen-tap/doc-doan') }}" class="ra-tab-link">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Đọc Đoạn</span>
        </a>
    </nav>

    {{-- 3. Category Pills: Tất cả, 200 Basic Words, TOEIC Speaking --}}
    <div class="ra-category-pills" role="tablist">
        <button type="button" class="ra-pill-btn is-active" data-filter="all">Tất cả</button>
        <button type="button" class="ra-pill-btn" data-filter="basic">200 Basic Words</button>
        <button type="button" class="ra-pill-btn" data-filter="toeic">TOEIC Speaking</button>
    </div>

    {{-- 4. Filter & Counter bar: 208 Bài học, Độ khó, Tiến độ --}}
    <div class="ra-filter-bar">
        <div class="ra-count-info">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3Z" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span id="raSentenceCount">208 Bài học</span>
        </div>
        <div class="ra-filter-actions">
            <select class="ra-filter-select" id="raDifficultyFilter" aria-label="Lọc theo độ khó">
                <option value="all">Độ khó</option>
                <option value="easy">Dễ</option>
                <option value="medium">Trung bình</option>
                <option value="hard">Khó</option>
            </select>
            <select class="ra-filter-select" id="raProgressFilter" aria-label="Lọc theo tiến độ">
                <option value="all">Tiến độ</option>
                <option value="completed">Đã hoàn thành</option>
                <option value="uncompleted">Chưa học</option>
            </select>
        </div>
    </div>

    {{-- 5. Danh sách bài đọc câu (13 câu theo đúng ảnh mẫu) --}}
    @php
        $sentences = [
            [
                'id' => 1,
                'title' => '1. If you have a good job, think smart.',
                'full_text' => 'If you have a good job, think smart.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic'
            ],
            [
                'id' => 2,
                'title' => '2. Our affordable loans can start repairing your c...',
                'full_text' => 'Our affordable loans can start repairing your credit score and rebuilding your financial stability immediately.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'toeic'
            ],
            [
                'id' => 3,
                'title' => '3. Say goodbye to cash advances on your redit card an...',
                'full_text' => 'Say goodbye to cash advances on your credit card and high interest rates with our comprehensive debt consolidation plan.',
                'diff' => 'hard',
                'diff_label' => 'Khó',
                'category' => 'toeic'
            ],
            [
                'id' => 4,
                'title' => '4. If you own a car that is less than three years ol...',
                'full_text' => 'If you own a car that is less than three years old, you may qualify for a significant reduction on your premium policy.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'toeic'
            ],
            [
                'id' => 5,
                'title' => '5. Visit our website at www.wise-financial.com.',
                'full_text' => 'Visit our website at www.wise-financial.com.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic'
            ],
            [
                'id' => 6,
                'title' => '6. Making your own bread at home is easy with the nev...',
                'full_text' => 'Making your own bread at home is easy with the revolutionary automatic bakery maker from HomeCraft.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic'
            ],
            [
                'id' => 7,
                'title' => '7. Unlike other bread machines that can be difficu...',
                'full_text' => 'Unlike other bread machines that can be difficult to program, our simple touch screen guide makes baking seamless.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'toeic'
            ],
            [
                'id' => 8,
                'title' => '8. This stainless steel appliance will mix dough f...',
                'full_text' => 'This stainless steel appliance will mix dough for artisanal pizzas, crusty sourdough breads, and sweet brioches.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'toeic'
            ],
            [
                'id' => 9,
                'title' => '9. The automated delay feature at the beginning of th...',
                'full_text' => 'The automated delay feature at the beginning of the program lets you wake up each morning to freshly baked bread.',
                'diff' => 'hard',
                'diff_label' => 'Khó',
                'category' => 'toeic'
            ],
            [
                'id' => 10,
                'title' => '10. We guarantee that the accompanying Berring...',
                'full_text' => 'We guarantee that the accompanying Berrington cookbook contains mouthwatering recipes for every member of your family.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'toeic'
            ],
            [
                'id' => 11,
                'title' => '11. This is Director Gabriel Henderson and I wou...',
                'full_text' => 'This is Director Gabriel Henderson and I would personally like to welcome all new associates to our annual orientation banquet.',
                'diff' => 'medium',
                'diff_label' => 'Trung bình',
                'category' => 'toeic'
            ],
            [
                'id' => 12,
                'title' => '12. We have prepared barbecued beef, drinks and ice c...',
                'full_text' => 'We have prepared barbecued beef, drinks and ice cream for everyone attending the outdoor celebration in the courtyard.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic'
            ],
            [
                'id' => 13,
                'title' => '13. Your children can play on the playground equipped...',
                'full_text' => 'Your children can play on the playground equipped with swings, slides, and supervised creative craft workshops.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic'
            ],
        ];
    @endphp

    <div class="ra-item-list" id="raSentenceList">
        @foreach($sentences as $item)
            <div class="ra-item-row"
                 data-id="{{ $item['id'] }}"
                 data-diff="{{ $item['diff'] }}"
                 data-category="{{ $item['category'] }}"
                 data-text="{{ $item['full_text'] }}"
                 onclick="openReadAloudModal({{ $item['id'] }}, '{{ addslashes($item['full_text']) }}', '{{ $item['diff_label'] }}', '{{ $item['diff'] }}')"
                 role="button"
                 tabindex="0">
                <div class="ra-item-left">
                    <div class="ra-circle-icon" id="circle-check-{{ $item['id'] }}" title="Trạng thái hoàn thành"></div>
                    <p class="ra-item-title" title="{{ $item['full_text'] }}">{{ $item['title'] }}</p>
                </div>
                <div class="ra-item-right">
                    <span class="ra-diff-badge ra-diff-badge--{{ $item['diff'] }}">{{ $item['diff_label'] }}</span>
                    <svg class="ra-chevron-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        @endforeach
    </div>

</div>

{{-- MODAL LUYỆN ĐỌC PHÁT ÂM TƯƠNG TÁC --}}
<div class="ra-modal-backdrop" id="raModalBackdrop" onclick="handleBackdropClick(event)">
    <div class="ra-modal" role="dialog" aria-modal="true" aria-labelledby="raModalTitle">
        <div class="ra-modal__header">
            <h3 class="ra-modal__title" id="raModalTitle">Luyện Đọc Câu</h3>
            <button type="button" class="ra-modal__close" onclick="closeReadAloudModal()" aria-label="Đóng cửa sổ">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="ra-modal__body">
            <div class="ra-reading-box">
                <p class="ra-reading-text" id="raModalText">If you have a good job, think smart.</p>
                <div class="ra-audio-ctrls">
                    <button type="button" class="ra-pill-btn" onclick="playAudioReference()" style="background: #E0E7FF; color: #4338CA;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 6px;">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        Nghe mẫu
                    </button>
                    <span id="raDiffBadgeInModal" class="ra-diff-badge ra-diff-badge--easy">Dễ</span>
                </div>
            </div>

            <button type="button" class="ra-record-btn" id="raRecordBtn" onclick="toggleRecording()">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                    <line x1="12" y1="19" x2="12" y2="23"></line>
                    <line x1="8" y1="23" x2="16" y2="23"></line>
                </svg>
                <span id="raRecordBtnLabel">Nhấn để bắt đầu đọc</span>
            </button>

            <div class="ra-result-card" id="raResultCard">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <div style="font-size: 15px; font-weight: 800; color: #166534;">🎉 Đánh giá phát âm: <span id="raScoreNum" style="color: #15803D;">96/100</span></div>
                    <span style="font-size: 12px; font-weight: 700; color: #15803D; background: #DCFCE7; padding: 3px 10px; border-radius: 999px;">Rất xuất sắc</span>
                </div>
                <p style="font-size: 13.5px; color: #166534; margin: 0 0 12px; line-height: 1.5;">Ngữ điệu tự nhiên, ngắt nghỉ đúng cụm từ và phát âm đuôi phụ âm chuẩn xác.</p>
                <button type="button" class="ra-pill-btn is-active" style="width: 100%; justify-content: center;" onclick="markCompletedAndClose()">
                    Lưu kết quả & Đóng
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentItemId = null;
    let currentItemText = '';
    let isRecording = false;
    let recordTimer = null;

    function openReadAloudModal(id, text, diffLabel, diffClass) {
        currentItemId = id;
        currentItemText = text;
        document.getElementById('raModalTitle').textContent = 'Luyện Đọc Câu #' + id;
        document.getElementById('raModalText').textContent = text;

        const badge = document.getElementById('raDiffBadgeInModal');
        badge.textContent = diffLabel;
        badge.className = 'ra-diff-badge ra-diff-badge--' + diffClass;

        document.getElementById('raResultCard').style.display = 'none';
        resetRecordingState();

        document.getElementById('raModalBackdrop').classList.add('is-open');
    }

    function closeReadAloudModal() {
        document.getElementById('raModalBackdrop').classList.remove('is-open');
        resetRecordingState();
    }

    function handleBackdropClick(e) {
        if (e.target.id === 'raModalBackdrop') {
            closeReadAloudModal();
        }
    }

    function playAudioReference() {
        if ('speechSynthesis' in window && currentItemText) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(currentItemText);
            utterance.lang = 'en-US';
            utterance.rate = 0.9;
            window.speechSynthesis.speak(utterance);
        } else {
            alert('Trình duyệt không hỗ trợ tổng hợp giọng nói.');
        }
    }

    function toggleRecording() {
        const btn = document.getElementById('raRecordBtn');
        const label = document.getElementById('raRecordBtnLabel');

        if (!isRecording) {
            isRecording = true;
            btn.classList.add('is-recording');
            label.textContent = 'Đang ghi âm... Nhấn để dừng';

            recordTimer = setTimeout(() => {
                if (isRecording) {
                    finishRecording();
                }
            }, 3000);
        } else {
            finishRecording();
        }
    }

    function finishRecording() {
        clearTimeout(recordTimer);
        isRecording = false;
        const btn = document.getElementById('raRecordBtn');
        const label = document.getElementById('raRecordBtnLabel');
        btn.classList.remove('is-recording');
        label.textContent = 'Đã hoàn thành! Đang phân tích...';

        setTimeout(() => {
            document.getElementById('raResultCard').style.display = 'block';
            label.textContent = 'Đọc lại lần nữa';
        }, 500);
    }

    function resetRecordingState() {
        clearTimeout(recordTimer);
        isRecording = false;
        const btn = document.getElementById('raRecordBtn');
        const label = document.getElementById('raRecordBtnLabel');
        btn.classList.remove('is-recording');
        label.textContent = 'Nhấn để bắt đầu đọc';
    }

    function markCompletedAndClose() {
        if (currentItemId) {
            const circle = document.getElementById('circle-check-' + currentItemId);
            if (circle) {
                circle.style.background = '#10B981';
                circle.style.borderColor = '#10B981';
                circle.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:block; margin: 2px auto;"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            }
        }
        closeReadAloudModal();
    }

    // Filter pills (Tất cả, 200 Basic Words, TOEIC Speaking)
    document.querySelectorAll('.ra-pill-btn[data-filter]').forEach(pill => {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.ra-pill-btn[data-filter]').forEach(p => p.classList.remove('is-active'));
            this.classList.add('is-active');
            applyFilters();
        });
    });

    // Dropdown filters (Độ khó, Tiến độ)
    document.getElementById('raDifficultyFilter').addEventListener('change', applyFilters);
    document.getElementById('raProgressFilter').addEventListener('change', applyFilters);

    function applyFilters() {
        const activeCategory = document.querySelector('.ra-pill-btn.is-active[data-filter]')?.dataset.filter || 'all';
        const difficulty = document.getElementById('raDifficultyFilter').value;
        const rows = document.querySelectorAll('.ra-item-row');
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

        document.getElementById('raSentenceCount').textContent = visibleCount + ' Bài học';
    }
</script>
@endpush

</x-layouts.app>
