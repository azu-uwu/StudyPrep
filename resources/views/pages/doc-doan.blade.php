<x-layouts.app active="luyen-tap" title="StudyPrep - Luyện Đọc Đoạn Văn (Read Aloud)">

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

    {{-- 2. Thanh Tabs: Đọc Câu vs Đọc Đoạn (Active) --}}
    <nav class="ra-tabs-nav" aria-label="Hình thức luyện tập">
        <a href="{{ url('/luyen-tap/doc-cau') }}" class="ra-tab-link">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
            </svg>
            <span>Đọc Câu</span>
        </a>
        <a href="{{ url('/luyen-tap/doc-doan') }}" class="ra-tab-link is-active" aria-current="page">
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

    {{-- 4. Filter & Counter bar: 44 Bài học, Độ khó, Tiến độ --}}
    <div class="ra-filter-bar">
        <div class="ra-count-info">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3Z" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span id="raParagraphCount">44 Bài học</span>
        </div>
        <div class="ra-filter-actions">
            <select class="ra-filter-select" id="raDiffFilterParagraph" aria-label="Lọc theo độ khó">
                <option value="all">Độ khó</option>
                <option value="easy">Dễ</option>
                <option value="medium">Trung bình</option>
                <option value="hard">Khó</option>
            </select>
            <select class="ra-filter-select" id="raProgressFilterParagraph" aria-label="Lọc theo tiến độ">
                <option value="all">Tiến độ</option>
                <option value="completed">Đã hoàn thành</option>
                <option value="uncompleted">Chưa học</option>
            </select>
        </div>
    </div>

    {{-- 5. Danh sách bài đọc đoạn văn (13 đoạn theo đúng ảnh mẫu 2) --}}
    @php
        $paragraphs = [
            [
                'id' => 1,
                'title' => '1. Did you all pay off your debts?',
                'full_text' => 'Did you all pay off your debts before the end of this financial quarter? Managing credit card payments and mortgage rates responsibly is crucial for long-term security.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic',
                'score' => null,
                'has_bookmark' => false
            ],
            [
                'id' => 2,
                'title' => "2. You don't need to spend all of your hard earn...",
                'full_text' => "You don't need to spend all of your hard earned savings on home renovations. Our affordable financing options and low-interest personal plans help you remodel with peace of mind.",
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic',
                'score' => null,
                'has_bookmark' => false
            ],
            [
                'id' => 3,
                'title' => '3. This is Director Gabriel Henderson and I wou...',
                'full_text' => 'This is Director Gabriel Henderson and I would personally like to welcome all new associates to our annual corporate summit. Over the next three days, you will discover our future expansion plans.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'toeic',
                'score' => '#0',
                'has_bookmark' => true
            ],
            [
                'id' => 4,
                'title' => '4. The personnel managers have decided to mal...',
                'full_text' => 'The personnel managers have decided to make several crucial updates to our remote working guidelines. All department leads are requested to review the revised compliance handbook before Friday.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'toeic',
                'score' => '#0',
                'has_bookmark' => true
            ],
            [
                'id' => 5,
                'title' => "5. Feel like it's time for a career change?",
                'full_text' => "Feel like it's time for a career change? Apex Training Institute offers accredited online certificates in software engineering, digital product design, and cloud system administration.",
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic',
                'score' => '#0',
                'has_bookmark' => true
            ],
            [
                'id' => 6,
                'title' => '6. On Watertown Radio, we talk about what you...',
                'full_text' => 'On Watertown Radio, we talk about what you care most about: local community news, traffic reports, and insightful interviews with municipal leaders across the metropolitan region.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'toeic',
                'score' => '#0',
                'has_bookmark' => true
            ],
            [
                'id' => 7,
                'title' => '7. Attention, employees. The Northview parking...',
                'full_text' => 'Attention, employees. The Northview parking lot will be temporarily closed tomorrow morning for scheduled resurfacing. Please make use of the South garage during this period.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'toeic',
                'score' => '#0',
                'has_bookmark' => true
            ],
            [
                'id' => 8,
                'title' => '8. Are you looking for a dream career with a dyn...',
                'full_text' => 'Are you looking for a dream career with a dynamic international team? Vanguard Media is recruiting ambitious bilingual content creators and marketing analysts for our regional headquarters.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'toeic',
                'score' => '#0',
                'has_bookmark' => true
            ],
            [
                'id' => 9,
                'title' => "9. Hello. You've reached Aurora Bakery. The bal...",
                'full_text' => "Hello. You've reached Aurora Bakery. The bakery will be closed on Thanksgiving Day, but our automated online ordering system remains accessible around the clock for pick-up orders.",
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic',
                'score' => '#0',
                'has_bookmark' => true
            ],
            [
                'id' => 10,
                'title' => '10. Ladies and gentlemen, thank you for visiting...',
                'full_text' => 'Ladies and gentlemen, thank you for visiting the National Science Museum today. Please note that all exhibit galleries will close promptly in fifteen minutes at five o’clock.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'toeic',
                'score' => '#0',
                'has_bookmark' => true
            ],
            [
                'id' => 11,
                'title' => '11. Welcome, everyone. Thanks for coming to th...',
                'full_text' => 'Welcome, everyone. Thanks for coming to the preliminary budget briefing. Today, we will outline our investment allocation for international logistics and customer support software.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'toeic',
                'score' => '#0',
                'has_bookmark' => true
            ],
            [
                'id' => 12,
                'title' => '12. Hi. This is Myra Peters calling about my app...',
                'full_text' => 'Hi. This is Myra Peters calling about my appointment scheduled for tomorrow at two o’clock. Due to unexpected travel delays, I would like to reschedule it to Thursday morning.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic',
                'score' => '#0',
                'has_bookmark' => true
            ],
            [
                'id' => 13,
                'title' => '13. Thank you for tuning in to our television sho...',
                'full_text' => 'Thank you for tuning in to our television show tonight. Coming up next after the commercial break, our special culinary guest will demonstrate how to prepare authentic Italian risotto.',
                'diff' => 'easy',
                'diff_label' => 'Dễ',
                'category' => 'basic',
                'score' => '#0',
                'has_bookmark' => true
            ],
        ];
    @endphp

    <div class="ra-item-list" id="raParagraphList">
        @foreach($paragraphs as $item)
            <div class="ra-item-row"
                 data-id="{{ $item['id'] }}"
                 data-diff="{{ $item['diff'] }}"
                 data-category="{{ $item['category'] }}"
                 data-text="{{ $item['full_text'] }}"
                 onclick="openParagraphModal({{ $item['id'] }}, '{{ addslashes($item['full_text']) }}', '{{ $item['diff_label'] }}', '{{ $item['diff'] }}')"
                 role="button"
                 tabindex="0">
                <div class="ra-item-left">
                    <div class="ra-circle-icon" id="circle-para-{{ $item['id'] }}" title="Trạng thái hoàn thành"></div>
                    <p class="ra-item-title" title="{{ $item['full_text'] }}">{{ $item['title'] }}</p>
                </div>
                <div class="ra-item-right">
                    <span class="ra-diff-badge ra-diff-badge--{{ $item['diff'] }}">{{ $item['diff_label'] }}</span>

                    @if($item['score'])
                        <span class="ra-score-tag">{{ $item['score'] }}</span>
                    @endif

                    @if($item['has_bookmark'])
                        <svg class="ra-bookmark-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    @endif

                    <svg class="ra-chevron-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        @endforeach
    </div>

</div>

{{-- MODAL LUYỆN ĐỌC ĐOẠN VĂN --}}
<div class="ra-modal-backdrop" id="raParaModalBackdrop" onclick="handleParaBackdropClick(event)">
    <div class="ra-modal" role="dialog" aria-modal="true" aria-labelledby="raParaModalTitle">
        <div class="ra-modal__header">
            <h3 class="ra-modal__title" id="raParaModalTitle">Luyện Đọc Đoạn Văn</h3>
            <button type="button" class="ra-modal__close" onclick="closeParagraphModal()" aria-label="Đóng cửa sổ">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="ra-modal__body">
            <div class="ra-reading-box">
                <p class="ra-reading-text" id="raParaModalText" style="font-size: 17px; line-height: 1.7; font-weight: 600; color: #1E293B;">
                    Did you all pay off your debts before the end of this financial quarter?
                </p>
                <div class="ra-audio-ctrls">
                    <button type="button" class="ra-pill-btn" onclick="playParaAudioReference()" style="background: #E0E7FF; color: #4338CA;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 6px;">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        Nghe đọc mẫu đoạn văn
                    </button>
                    <span id="raParaDiffBadge" class="ra-diff-badge ra-diff-badge--easy">Dễ</span>
                </div>
            </div>

            <button type="button" class="ra-record-btn" id="raParaRecordBtn" onclick="toggleParaRecording()">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                    <line x1="12" y1="19" x2="12" y2="23"></line>
                    <line x1="8" y1="23" x2="16" y2="23"></line>
                </svg>
                <span id="raParaRecordBtnLabel">Nhấn để bắt đầu đọc đoạn văn</span>
            </button>

            <div class="ra-result-card" id="raParaResultCard">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <div style="font-size: 15px; font-weight: 800; color: #166534;">🎉 Đánh giá đoạn văn: <span style="color: #15803D;">94/100</span></div>
                    <span style="font-size: 12px; font-weight: 700; color: #15803D; background: #DCFCE7; padding: 3px 10px; border-radius: 999px;">Lưu loát</span>
                </div>
                <p style="font-size: 13.5px; color: #166534; margin: 0 0 12px; line-height: 1.5;">Tốc độ đọc vừa phải, ngắt nghỉ đúng câu phức, phát âm chuẩn các từ vựng học thuật.</p>
                <button type="button" class="ra-pill-btn is-active" style="width: 100%; justify-content: center;" onclick="markParaCompletedAndClose()">
                    Lưu kết quả & Đóng
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentParaId = null;
    let currentParaText = '';
    let isParaRecording = false;
    let paraRecordTimer = null;

    function openParagraphModal(id, text, diffLabel, diffClass) {
        currentParaId = id;
        currentParaText = text;
        document.getElementById('raParaModalTitle').textContent = 'Luyện Đọc Đoạn Văn #' + id;
        document.getElementById('raParaModalText').textContent = text;

        const badge = document.getElementById('raParaDiffBadge');
        badge.textContent = diffLabel;
        badge.className = 'ra-diff-badge ra-diff-badge--' + diffClass;

        document.getElementById('raParaResultCard').style.display = 'none';
        resetParaRecordingState();

        document.getElementById('raParaModalBackdrop').classList.add('is-open');
    }

    function closeParagraphModal() {
        document.getElementById('raParaModalBackdrop').classList.remove('is-open');
        resetParaRecordingState();
    }

    function handleParaBackdropClick(e) {
        if (e.target.id === 'raParaModalBackdrop') {
            closeParagraphModal();
        }
    }

    function playParaAudioReference() {
        if ('speechSynthesis' in window && currentParaText) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(currentParaText);
            utterance.lang = 'en-US';
            utterance.rate = 0.9;
            window.speechSynthesis.speak(utterance);
        } else {
            alert('Trình duyệt không hỗ trợ tổng hợp giọng nói.');
        }
    }

    function toggleParaRecording() {
        const btn = document.getElementById('raParaRecordBtn');
        const label = document.getElementById('raParaRecordBtnLabel');

        if (!isParaRecording) {
            isParaRecording = true;
            btn.classList.add('is-recording');
            label.textContent = 'Đang ghi âm... Nhấn để dừng';

            paraRecordTimer = setTimeout(() => {
                if (isParaRecording) {
                    finishParaRecording();
                }
            }, 4000);
        } else {
            finishParaRecording();
        }
    }

    function finishParaRecording() {
        clearTimeout(paraRecordTimer);
        isParaRecording = false;
        const btn = document.getElementById('raParaRecordBtn');
        const label = document.getElementById('raParaRecordBtnLabel');
        btn.classList.remove('is-recording');
        label.textContent = 'Đã hoàn thành! Đang phân tích...';

        setTimeout(() => {
            document.getElementById('raParaResultCard').style.display = 'block';
            label.textContent = 'Đọc lại lần nữa';
        }, 500);
    }

    function resetParaRecordingState() {
        clearTimeout(paraRecordTimer);
        isParaRecording = false;
        const btn = document.getElementById('raParaRecordBtn');
        const label = document.getElementById('raParaRecordBtnLabel');
        btn.classList.remove('is-recording');
        label.textContent = 'Nhấn để bắt đầu đọc đoạn văn';
    }

    function markParaCompletedAndClose() {
        if (currentParaId) {
            const circle = document.getElementById('circle-para-' + currentParaId);
            if (circle) {
                circle.style.background = '#10B981';
                circle.style.borderColor = '#10B981';
                circle.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:block; margin: 2px auto;"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            }
        }
        closeParagraphModal();
    }

    // Filter pills (Tất cả, 200 Basic Words, TOEIC Speaking)
    document.querySelectorAll('.ra-pill-btn[data-filter]').forEach(pill => {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.ra-pill-btn[data-filter]').forEach(p => p.classList.remove('is-active'));
            this.classList.add('is-active');
            applyParaFilters();
        });
    });

    // Dropdown filters (Độ khó, Tiến độ)
    document.getElementById('raDiffFilterParagraph').addEventListener('change', applyParaFilters);
    document.getElementById('raProgressFilterParagraph').addEventListener('change', applyParaFilters);

    function applyParaFilters() {
        const activeCategory = document.querySelector('.ra-pill-btn.is-active[data-filter]')?.dataset.filter || 'all';
        const difficulty = document.getElementById('raDiffFilterParagraph').value;
        const rows = document.querySelectorAll('#raParagraphList .ra-item-row');
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

        document.getElementById('raParagraphCount').textContent = visibleCount + ' Bài học';
    }
</script>
@endpush

</x-layouts.app>
