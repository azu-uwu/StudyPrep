<x-layouts.app active="luyen-tap" title="StudyPrep - Kết quả bài luyện Listening">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/doc-ket-qua.css') }}">
@endpush

<div class="sp-kq-container">
    <div class="sp-kq-topbar">
        <a href="{{ route('luyen-tap.nghe') }}" class="sp-kq-close-btn" title="Quay lại danh sách bài luyện Nghe" aria-label="Đóng">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </a>
    </div>

    <div class="sp-kq-heading-block">
        <div class="sp-kq-super-title">KẾT QUẢ BÀI LUYỆN</div>
        <h1 class="sp-kq-main-title">{{ $result['part_name'] }}</h1>
        <div class="sp-kq-meta-info">{{ $result['total'] }} câu · {{ $result['time_spent_text'] }}</div>
    </div>

    @php
        $scorePercent = (int) $result['score_percent'];
        $circumference = 314.16;
        $dashoffset = $circumference - ($circumference * $scorePercent / 100);
        $scoreColor = $scorePercent >= 80 ? '#22C55E' : ($scorePercent >= 50 ? '#3B82F6' : '#EF4444');
    @endphp

    <div id="viewSummary" class="sp-view-summary">
        <div class="sp-kq-donut-wrap">
            <svg class="sp-kq-donut-svg" viewBox="0 0 120 120" aria-hidden="true">
                <circle class="sp-kq-donut-track" cx="60" cy="60" r="50"></circle>
                <circle class="sp-kq-donut-fill" cx="60" cy="60" r="50"
                        stroke="{{ $scoreColor }}" stroke-dasharray="314.16"
                        stroke-dashoffset="{{ $dashoffset }}"></circle>
            </svg>
            <div class="sp-kq-donut-inner">
                <span class="sp-kq-donut-num" style="color: {{ $scoreColor }};">{{ $scorePercent }}%</span>
                <span class="sp-kq-donut-label">chính xác</span>
            </div>
        </div>

        <div class="sp-kq-summary-text">Đúng {{ $result['correct_count'] }}/{{ $result['total'] }} câu</div>
        <div class="sp-kq-prev-score">Trước: {{ $result['previous_percent'] }}</div>

        <div class="sp-kq-stats-box">
            <div class="sp-kq-stats-columns">
                <div class="sp-kq-col-item">
                    <div class="sp-kq-col-val">{{ $result['correct_count'] }}</div>
                    <div class="sp-kq-col-lbl">Đúng</div>
                </div>
                <div class="sp-kq-col-item">
                    <div class="sp-kq-col-val">{{ $result['incorrect_count'] }}</div>
                    <div class="sp-kq-col-lbl">Sai</div>
                </div>
                <div class="sp-kq-col-item">
                    <div class="sp-kq-col-val">{{ $result['unanswered_count'] }}</div>
                    <div class="sp-kq-col-lbl">Chưa trả lời</div>
                </div>
            </div>
            <div class="sp-kq-stats-notice">
                @if($result['unanswered_count'] > 0)
                    Bài làm chưa xong. {{ $result['unanswered_count'] }} câu chưa trả lời không tính là sai.
                @else
                    Bạn đã hoàn thành toàn bộ {{ $result['total'] }} câu hỏi trong bài luyện.
                @endif
            </div>
        </div>

        <div class="sp-kq-bottom-actions">
            <a href="{{ route('luyen-tap.nghe.lam-bai', ['count' => $result['total']]) }}" class="sp-kq-btn sp-kq-btn--purple">
                Luyện tập lại
            </a>
            <button type="button" class="sp-kq-btn sp-kq-btn--white" onclick="switchKqView('review')">
                Xem đáp án
            </button>
        </div>

        <a href="{{ route('luyen-tap.nghe') }}" class="sp-kq-continue-link">Tiếp tục</a>
    </div>

    <div id="viewReview" class="sp-view-review">
        <div class="sp-review-tabs" role="tablist">
            <button type="button" class="sp-review-tab-btn is-active" data-filter="all" onclick="filterReviewQuestions('all', this)">
                <span>Tất cả</span>
                <span class="sp-tab-badge">{{ $result['total'] }}</span>
            </button>
            <button type="button" class="sp-review-tab-btn" data-filter="correct" onclick="filterReviewQuestions('correct', this)">
                <span>Câu đúng</span>
                <span class="sp-tab-badge">{{ $result['correct_count'] }}</span>
            </button>
            <button type="button" class="sp-review-tab-btn" data-filter="incorrect" onclick="filterReviewQuestions('incorrect', this)">
                <span>Câu sai</span>
                <span class="sp-tab-badge">{{ $result['incorrect_count'] }}</span>
            </button>
            <button type="button" class="sp-review-tab-btn" data-filter="unanswered" onclick="filterReviewQuestions('unanswered', this)">
                <span>Chưa trả lời</span>
                <span class="sp-tab-badge">{{ $result['unanswered_count'] }}</span>
            </button>
        </div>

        <div class="sp-part-card">
            <div class="sp-part-header" onclick="togglePartAccordion('listeningPartBody')">
                <h3 class="sp-part-title">Chi tiết câu trả lời</h3>
                <div class="sp-part-header-right">
                    <span class="sp-part-pct-pill">{{ $scorePercent }}%</span>
                    <span class="sp-part-score-text">{{ $result['correct_count'] }}/{{ $result['total'] }} câu đúng</span>
                    <button type="button" class="sp-part-toggle-btn" id="partToggleBtn" aria-label="Đóng mở danh sách câu">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="sp-part-body" id="listeningPartBody">
                @foreach($result['details'] as $index => $q)
                    <div class="sp-q-item-wrapper" data-status="{{ $q['status'] }}" id="qWrapper{{ $index }}">
                        <div class="sp-q-row" onclick="toggleQuestionDetail({{ $index }})">
                            <div class="sp-q-label">Câu {{ $q['question_number'] }}</div>
                            <div class="sp-q-controls">
                                @if($q['status'] === 'incorrect')
                                    <span class="sp-q-status-icon sp-q-status-icon--incorrect" title="Câu sai">×</span>
                                @elseif($q['status'] === 'correct')
                                    <span class="sp-q-status-icon sp-q-status-icon--correct" title="Câu đúng">✓</span>
                                @else
                                    <span class="sp-q-status-icon sp-q-status-icon--unanswered" title="Chưa trả lời">-</span>
                                @endif

                                @foreach($q['options'] as $optionKey => $optionText)
                                    @php
                                        $circleClass = $optionKey === $q['correct_answer']
                                            ? 'sp-opt-circle--correct'
                                            : ($optionKey === $q['user_answer'] && $q['status'] === 'incorrect'
                                                ? 'sp-opt-circle--user-wrong'
                                                : 'sp-opt-circle--neutral');
                                    @endphp
                                    <span class="sp-opt-circle {{ $circleClass }}">{{ $optionKey }}</span>
                                @endforeach

                                <span class="sp-q-chevron" id="qChevron{{ $index }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="sp-q-detail" id="qDetail{{ $index }}">
                            <div class="sp-q-detail-stem">
                                <strong>{{ $q['part'] }}</strong>
                            </div>

                            @if($q['image'])
                                <img src="{{ $q['image'] }}" alt="Hình minh họa câu {{ $q['question_number'] }}" style="display:block;max-width:100%;max-height:260px;border-radius:8px;margin-bottom:14px;">
                            @endif

                            <div class="sp-q-detail-options">
                                @foreach($q['options'] as $optionKey => $optionText)
                                    @php
                                        $optionClass = $optionKey === $q['correct_answer']
                                            ? 'is-correct'
                                            : ($optionKey === $q['user_answer'] && $q['status'] === 'incorrect'
                                                ? 'is-user-wrong'
                                                : '');
                                    @endphp
                                    <div class="sp-q-detail-opt-item {{ $optionClass }}">
                                        <strong>{{ $optionKey }}.</strong>
                                        <span style="flex:1;">{{ $optionText }}</span>
                                        @if($optionKey === $q['correct_answer'])
                                            <span style="color:#16A34A;font-weight:700;font-size:12px;">Đáp án đúng</span>
                                        @elseif($optionKey === $q['user_answer'] && $q['status'] === 'incorrect')
                                            <span style="color:#DC2626;font-weight:700;font-size:12px;">Bạn chọn</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="sp-q-detail-explanation">
                                <strong>💡 Giải thích chi tiết:</strong> {{ $q['explanation'] }}
                            </div>
                            <div class="sp-q-detail-translation">
                                <strong>Transcript:</strong>
                                @foreach($q['transcript'] as $line)
                                    <div>{{ $line }}</div>
                                @endforeach
                                <strong>Dịch nghĩa:</strong>
                                @foreach($q['translation'] as $line)
                                    <div>{{ $line }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="sp-kq-empty-filter" id="emptyFilterMsg">Không có câu hỏi nào thuộc phân loại này.</div>
            </div>
        </div>

        <div class="sp-kq-bottom-actions">
            <a href="{{ route('luyen-tap.nghe.lam-bai', ['count' => $result['total']]) }}" class="sp-kq-btn sp-kq-btn--purple">Luyện tập lại</a>
            <button type="button" class="sp-kq-btn sp-kq-btn--white" onclick="switchKqView('summary')">Quay lại</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchKqView(viewName) {
        const summaryView = document.getElementById('viewSummary');
        const reviewView = document.getElementById('viewReview');

        if (viewName === 'review') {
            summaryView.style.display = 'none';
            reviewView.style.display = 'block';
            window.history.replaceState(null, '', '#review');
        } else {
            reviewView.style.display = 'none';
            summaryView.style.display = 'block';
            window.history.replaceState(null, '', '#summary');
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function togglePartAccordion(bodyId) {
        const body = document.getElementById(bodyId);
        const toggleBtn = document.getElementById('partToggleBtn');
        const isCollapsed = body.style.display === 'none';
        body.style.display = isCollapsed ? 'block' : 'none';
        toggleBtn.classList.toggle('is-collapsed', !isCollapsed);
    }

    function toggleQuestionDetail(index) {
        const detail = document.getElementById('qDetail' + index);
        const chevron = document.getElementById('qChevron' + index);
        detail.classList.toggle('is-open');
        chevron.classList.toggle('is-open');
    }

    function filterReviewQuestions(filterType, btn) {
        document.querySelectorAll('.sp-review-tab-btn').forEach(function(tab) {
            tab.classList.remove('is-active');
        });
        btn.classList.add('is-active');

        let visibleCount = 0;
        document.querySelectorAll('.sp-q-item-wrapper').forEach(function(item) {
            const isVisible = filterType === 'all' || item.getAttribute('data-status') === filterType;
            item.style.display = isVisible ? 'block' : 'none';
            visibleCount += isVisible ? 1 : 0;
        });
        document.getElementById('emptyFilterMsg').style.display = visibleCount === 0 ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#review') {
            switchKqView('review');
        }
    });
</script>
@endpush

</x-layouts.app>
