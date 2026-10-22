<x-layouts.app active="luyen-tap" title="StudyPrep - Kết quả bài luyện Reading">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/doc-ket-qua.css') }}">
@endpush

<div class="sp-kq-container">

    {{-- Nút X đóng ở góc trên bên trái --}}
    <div class="sp-kq-topbar">
        <a href="{{ url('/luyen-tap/doc') }}" class="sp-kq-close-btn" title="Đóng và quay lại danh sách bài luyện Đọc" aria-label="Đóng">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </a>
    </div>

    {{-- Khối Tiêu đề chung cho cả 2 màn hình --}}
    <div class="sp-kq-heading-block">
        <div class="sp-kq-super-title">KẾT QUẢ BÀI LUYỆN</div>
        <h1 class="sp-kq-main-title">{{ $result['part_name'] ?? 'Part 5 — Đọc' }}</h1>
        <div class="sp-kq-meta-info">
            {{ $result['total'] }} câu · {{ $result['time_spent_text'] ?? '1 phút' }}
        </div>
    </div>

    @php
        $scorePercent = (int) ($result['score_percent'] ?? 0);
        // Vòng tròn gauge 140px, r=50 -> chu vi C = 2 * pi * 50 = 314.16
        $circumference = 314.16;
        $dashoffset = $circumference - ($circumference * $scorePercent / 100);

        if ($scorePercent >= 80) {
            $scoreColor = '#22C55E';
        } elseif ($scorePercent >= 50) {
            $scoreColor = '#3B82F6';
        } else {
            $scoreColor = '#EF4444';
        }
    @endphp

    {{-- ======================================================================
         VIEW 1: MÀN HÌNH TỔNG QUAN KẾT QUẢ (KHỚP ẢNH MẪU 3)
         ====================================================================== --}}
    <div id="viewSummary" class="sp-view-summary">

        {{-- Vòng tròn tiến độ & % chính xác --}}
        <div class="sp-kq-donut-wrap">
            <svg class="sp-kq-donut-svg" viewBox="0 0 120 120" aria-hidden="true">
                <circle class="sp-kq-donut-track" cx="60" cy="60" r="50"></circle>
                <circle class="sp-kq-donut-fill" cx="60" cy="60" r="50"
                        stroke="{{ $scoreColor }}"
                        stroke-dasharray="314.16"
                        stroke-dashoffset="{{ $dashoffset }}"></circle>
            </svg>
            <div class="sp-kq-donut-inner">
                <span class="sp-kq-donut-num" style="color: {{ $scoreColor }};">{{ $scorePercent }}%</span>
                <span class="sp-kq-donut-label">chính xác</span>
            </div>
        </div>

        {{-- Tóm tắt số câu đúng & Điểm trước đó --}}
        <div class="sp-kq-summary-text">
            Đúng {{ $result['correct_count'] }}/{{ $result['total'] }} câu
        </div>
        <div class="sp-kq-prev-score">
            Trước: {{ $result['previous_percent'] ?? '40%' }}
        </div>

        {{-- Thẻ thống kê 3 cột: Đúng | Sai | Chưa trả lời --}}
        <div class="sp-kq-stats-box">
            <div class="sp-kq-stats-columns">
                {{-- Đúng --}}
                <div class="sp-kq-col-item">
                    <div class="sp-kq-col-val">{{ $result['correct_count'] }}</div>
                    <div class="sp-kq-col-lbl">Đúng</div>
                </div>

                {{-- Sai --}}
                <div class="sp-kq-col-item">
                    <div class="sp-kq-col-val">{{ $result['incorrect_count'] }}</div>
                    <div class="sp-kq-col-lbl">Sai</div>
                </div>

                {{-- Chưa trả lời --}}
                <div class="sp-kq-col-item">
                    <div class="sp-kq-col-val">{{ $result['unanswered_count'] }}</div>
                    <div class="sp-kq-col-lbl">Chưa trả lời</div>
                </div>
            </div>

            {{-- Thông báo bài làm ở dưới đáy thẻ --}}
            <div class="sp-kq-stats-notice">
                @if($result['unanswered_count'] > 0)
                    Bài làm chưa xong. {{ $result['unanswered_count'] }} câu chưa trả lời không tính là sai.
                @else
                    Bạn đã hoàn thành toàn bộ {{ $result['total'] }} câu hỏi trong bài luyện.
                @endif
            </div>
        </div>

        {{-- 2 Nút bấm hành động + Nút Tiếp tục --}}
        <div class="sp-kq-bottom-actions">
            @if($result['incorrect_count'] > 0)
                <a href="{{ route('luyen-tap.doc.hoan-thanh-cau', ['retry_wrong' => 1]) }}" class="sp-kq-btn sp-kq-btn--purple">
                    Luyện lại câu sai
                </a>
            @else
                <a href="{{ route('luyen-tap.doc.hoan-thanh-cau') }}" class="sp-kq-btn sp-kq-btn--purple">
                    Luyện tập lại
                </a>
            @endif

            <button type="button" class="sp-kq-btn sp-kq-btn--white" onclick="switchKqView('review')">
                Xem đáp án
            </button>
        </div>

        <a href="{{ url('/luyen-tap/doc') }}" class="sp-kq-continue-link">
            Tiếp tục
        </a>

    </div>


    {{-- ======================================================================
         VIEW 2: MÀN HÌNH XEM ĐÁP ÁN CHI TIẾT (KHỚP ẢNH MẪU 1 & 2)
         ====================================================================== --}}
    <div id="viewReview" class="sp-view-review">

        {{-- Thanh Tabs lọc: Tất cả | Câu đúng | Câu sai | Chưa trả lời --}}
        <div class="sp-review-tabs" role="tablist">
            <button type="button" class="sp-review-tab-btn is-active" data-filter="all" onclick="filterReviewQuestions('all', this)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
                <span>Tất cả</span>
                <span class="sp-tab-badge" id="tabBadgeAll">{{ $result['total'] }}</span>
            </button>

            <button type="button" class="sp-review-tab-btn" data-filter="correct" onclick="filterReviewQuestions('correct', this)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>Câu đúng</span>
                <span class="sp-tab-badge">{{ $result['correct_count'] }}</span>
            </button>

            <button type="button" class="sp-review-tab-btn" data-filter="incorrect" onclick="filterReviewQuestions('incorrect', this)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                <span>Câu sai</span>
                <span class="sp-tab-badge">{{ $result['incorrect_count'] }}</span>
            </button>

            <button type="button" class="sp-review-tab-btn" data-filter="unanswered" onclick="filterReviewQuestions('unanswered', this)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>Chưa trả lời</span>
                <span class="sp-tab-badge">{{ $result['unanswered_count'] }}</span>
            </button>
        </div>

        {{-- Card Accordion Part 5: Hoàn thành câu --}}
        <div class="sp-part-card">
            {{-- Header của Part --}}
            <div class="sp-part-header" onclick="togglePartAccordion('partBody')">
                <h3 class="sp-part-title">Part 5: Hoàn thành câu</h3>
                <div class="sp-part-header-right">
                    <span class="sp-part-pct-pill">{{ $scorePercent }}%</span>
                    <span class="sp-part-score-text">{{ $result['correct_count'] }}/{{ $result['total'] }} Câu đúng</span>
                    <button type="button" class="sp-part-toggle-btn" id="partToggleBtn" aria-label="Đóng mở danh sách câu">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body danh sách các câu hỏi --}}
            <div class="sp-part-body" id="partBody">
                @foreach($result['details'] as $index => $q)
                    <div class="sp-q-item-wrapper" data-status="{{ $q['status'] }}" id="qWrapper{{ $index }}">
                        {{-- Hàng hiển thị câu hỏi --}}
                        <div class="sp-q-row" onclick="toggleQuestionDetail({{ $index }})">
                            <div class="sp-q-label">Câu {{ $q['question_number'] }}</div>
                            <div class="sp-q-controls">
                                {{-- Icon trạng thái: Đỏ X, Xanh V, Xám - --}}
                                @if($q['status'] === 'incorrect')
                                    <span class="sp-q-status-icon sp-q-status-icon--incorrect" title="Câu sai">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </span>
                                @elseif($q['status'] === 'correct')
                                    <span class="sp-q-status-icon sp-q-status-icon--correct" title="Câu đúng">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </span>
                                @else
                                    <span class="sp-q-status-icon sp-q-status-icon--unanswered" title="Chưa trả lời">
                                        -
                                    </span>
                                @endif

                                {{-- 4 Vòng tròn A, B, C, D --}}
                                @foreach(['A', 'B', 'C', 'D'] as $optKey)
                                    @php
                                        $circleClass = 'sp-opt-circle--neutral';
                                        if ($optKey === $q['correct_answer']) {
                                            $circleClass = 'sp-opt-circle--correct';
                                        } elseif ($optKey === $q['user_answer'] && $q['status'] === 'incorrect') {
                                            $circleClass = 'sp-opt-circle--user-wrong';
                                        }
                                    @endphp
                                    <span class="sp-opt-circle {{ $circleClass }}">
                                        {{ $optKey }}
                                    </span>
                                @endforeach

                                {{-- Mũi tên mở rộng > --}}
                                <span class="sp-q-chevron" id="qChevron{{ $index }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        {{-- Khung chi tiết câu hỏi (Hiển thị khi click) --}}
                        <div class="sp-q-detail" id="qDetail{{ $index }}">
                            @if(!empty($q['passage']))
                                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; margin-bottom: 12px; font-size: 13.5px; line-height: 1.6;">
                                    @if(!empty($q['passage_title']))
                                        <strong style="display: block; margin-bottom: 4px; color: #0F172A;">{{ $q['passage_title'] }}</strong>
                                    @endif
                                    {!! $q['passage'] !!}
                                </div>
                            @endif

                            <div class="sp-q-detail-stem">
                                {!! $q['stem'] !!}
                            </div>

                            <div class="sp-q-detail-options">
                                @foreach($q['options'] as $key => $text)
                                    @php
                                        $optItemClass = '';
                                        if ($key === $q['correct_answer']) {
                                            $optItemClass = 'is-correct';
                                        } elseif ($key === $q['user_answer'] && $q['status'] === 'incorrect') {
                                            $optItemClass = 'is-user-wrong';
                                        }
                                    @endphp
                                    <div class="sp-q-detail-opt-item {{ $optItemClass }}">
                                        <strong>{{ $key }}.</strong>
                                        <span style="flex: 1;">{{ $text }}</span>
                                        @if($key === $q['correct_answer'])
                                            <span style="color: #16A34A; font-weight: 700; font-size: 12px;">Đáp án đúng</span>
                                        @elseif($key === $q['user_answer'] && $q['status'] === 'incorrect')
                                            <span style="color: #DC2626; font-weight: 700; font-size: 12px;">Bạn chọn</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="sp-q-detail-explanation">
                                <strong>💡 Giải thích chi tiết:</strong> {!! $q['explanation'] !!}
                                @if(!empty($q['translation']))
                                    <div class="sp-q-detail-translation">
                                        <strong>Dịch nghĩa:</strong> {{ $q['translation'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="sp-kq-empty-filter" id="emptyFilterMsg">
                    Không có câu hỏi nào thuộc phân loại này.
                </div>
            </div>
        </div>

        {{-- 2 Nút hành động ở đáy View 2 --}}
        <div class="sp-kq-bottom-actions">
            @if($result['incorrect_count'] > 0)
                <a href="{{ route('luyen-tap.doc.hoan-thanh-cau', ['retry_wrong' => 1]) }}" class="sp-kq-btn sp-kq-btn--purple">
                    Luyện lại câu sai
                </a>
            @else
                <a href="{{ route('luyen-tap.doc.hoan-thanh-cau') }}" class="sp-kq-btn sp-kq-btn--purple">
                    Luyện tập lại
                </a>
            @endif

            <button type="button" class="sp-kq-btn sp-kq-btn--white" onclick="switchKqView('summary')">
                Quay lại
            </button>
        </div>

    </div>

</div>

@push('scripts')
<script>
    // Chuyển đổi giữa View 1 (Tổng quan) và View 2 (Xem đáp án chi tiết)
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

    // Đóng mở Accordion Part 5
    function togglePartAccordion(bodyId) {
        const body = document.getElementById(bodyId);
        const toggleBtn = document.getElementById('partToggleBtn');
        if (body.style.display === 'none') {
            body.style.display = 'block';
            toggleBtn.classList.remove('is-collapsed');
        } else {
            body.style.display = 'none';
            toggleBtn.classList.add('is-collapsed');
        }
    }

    // Đóng mở xem giải thích chi tiết từng câu hỏi
    function toggleQuestionDetail(index) {
        const detail = document.getElementById('qDetail' + index);
        const chevron = document.getElementById('qChevron' + index);

        if (detail.classList.contains('is-open')) {
            detail.classList.remove('is-open');
            chevron.classList.remove('is-open');
        } else {
            detail.classList.add('is-open');
            chevron.classList.add('is-open');
        }
    }

    // Bộ lọc câu hỏi theo trạng thái (Tất cả, Câu đúng, Câu sai, Chưa trả lời)
    function filterReviewQuestions(filterType, btn) {
        document.querySelectorAll('.sp-review-tab-btn').forEach(function(b) {
            b.classList.remove('is-active');
        });
        btn.classList.add('is-active');

        const wrappers = document.querySelectorAll('.sp-q-item-wrapper');
        let visibleCount = 0;

        wrappers.forEach(function(wrap) {
            const status = wrap.getAttribute('data-status');
            if (filterType === 'all' || status === filterType) {
                wrap.style.display = 'block';
                visibleCount++;
            } else {
                wrap.style.display = 'none';
            }
        });

        const emptyMsg = document.getElementById('emptyFilterMsg');
        if (emptyMsg) {
            emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    // Kiểm tra hash ban đầu khi tải trang
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#review') {
            switchKqView('review');
        }
    });
</script>
@endpush

</x-layouts.app>
