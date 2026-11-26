<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>StudyPrep - Luyện tập Part {{ $part ?? 5 }} · TOEIC Reading</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:400,600,700,800,900|instrument-sans:400,500,600,700">

    {{-- Styles --}}
    <link rel="stylesheet" href="{{ asset('css/studyprep-core.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/doc-practice.css') }}">
</head>
<body class="sp-vp-page">

    {{-- 1. HEADER THANH TRÊN CÙNG (KHỚP ẢNH MẪU 4) --}}
    <header class="sp-vp-header">
        <div class="sp-vp-header-left">
            <a href="{{ url('/luyen-tap/doc') }}" class="sp-vp-logo" title="StudyPrep">
                <img class="sp-sidebar__logo sp-vp-logo-img" src="{{ asset('images/logo.svg') }}" alt="StudyPrep">
            </a>
            <button type="button" class="sp-vp-close-btn" title="Thoát bài luyện tập" onclick="openExitConfirmModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        {{-- Thanh tiến độ làm bài --}}
        <div class="sp-vp-progress-bar-wrap" title="Tiến độ bài làm">
            <div class="sp-vp-progress-bar-fill" id="progressBarFill" style="width: 0%;"></div>
        </div>

        <div class="sp-vp-header-right">
            <button type="button" class="sp-vp-icon-btn" title="Danh sách câu hỏi" onclick="toggleQuestionDrawer()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </button>
            <button type="button" class="sp-vp-icon-btn" title="Đánh dấu câu hỏi này" id="flagBtn" onclick="toggleFlagCurrentQuestion()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                    <line x1="4" y1="22" x2="4" y2="15"></line>
                </svg>
            </button>
            <button type="button" class="sp-vp-icon-btn" title="Cài đặt">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
            </button>
            <button type="button" class="sp-vp-submit-btn" onclick="handlePreSubmit()">Nộp bài</button>
        </div>
    </header>

    {{-- 2. THANH ĐIỀU HƯỚNG CÂU VÀ TIP BANNER --}}
    @if (!empty($noticeMessage))
        <div class="sp-adjustment-notice-banner" id="adjustmentNoticeBanner">
            <div class="sp-adjustment-notice-content">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>{{ $noticeMessage }}</span>
            </div>
            <button type="button" class="sp-adjustment-notice-close" onclick="dismissAdjustmentNotice()" title="Đóng thông báo" aria-label="Đóng thông báo">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    @endif
    <div class="sp-vp-subnav">
        <div class="sp-vp-nav-controls">
            <button type="button" class="sp-vp-nav-btn" onclick="prevQuestion()" id="prevBtn">← Câu trước</button>
            <button type="button" class="sp-vp-explain-btn" id="explainToggleBtn" onclick="toggleExplanation()">
                💡 <span>Giải thích</span>
            </button>
            <button type="button" class="sp-vp-nav-btn" onclick="nextQuestion()" id="nextBtn">Câu sau →</button>
        </div>
        <div class="sp-vp-tip-banner" id="tipBanner">
            <span>💡 Chạm vào từ để tra nghĩa, bôi đen cụm để dịch</span>
            <button type="button" onclick="document.getElementById('tipBanner').style.display='none'" aria-label="Đóng banner tip">✕</button>
        </div>
    </div>

    {{-- 3. FORM GỬI BÀI LÀM LÊN SERVER --}}
    <form id="practiceExamForm" action="{{ route('luyen-tap.doc.nop-bai') }}" method="POST">
        @csrf
        <input type="hidden" name="part" value="{{ $part ?? 5 }}">
        <input type="hidden" name="question_ids" value="{{ implode(',', array_column($questions, 'id')) }}">
        <input type="hidden" name="total_count" value="{{ count($questions) }}">

        {{-- BỐ CỤC 3 CỘT (KHỚP ẢNH MẪU 4) --}}
        <main class="sp-vp-main-layout" id="mainLayout">

            {{-- CỘT 1: ĐOẠN VĂN / NGỮ CẢNH CÂU HỎI --}}
            <section class="sp-vp-panel" id="passageCol">
                <div class="sp-vp-passage-range" id="passageRange">Câu 139 - 142</div>
                <h3 class="sp-vp-passage-title" id="passageTitle">Amon Donates to Music School in Grenel City</h3>
                <div class="sp-vp-passage-text" id="passageContent">
                    A spokesperson for Brenda Amon (139)______ that the pianist made a sizeable donation toward the expansion of the Grenel City Conservatory of Music. "Without her generous support," said Marc Diaz, director of facility planning, "we would have been limited in our renovation plans going forward."<br><br>
                    (140)______. Now, a new wing will be constructed on the south end of the (141)______ conservatory. Once completed, the building will boast a 700-seat auditorium, state-of-the-art recording studios, and new faculty and administrative offices. Additionally, private practice rooms will be located (142)______ the current student lounge.
                </div>
            </section>

            {{-- CỘT 2: GIẢI THÍCH CHI TIẾT (MÀU XANH DA TRỜI THEO ẢNH MẪU 4) --}}
            <section class="sp-vp-explain-panel" id="explainCol" style="display: none;">
                {{-- Thanh kéo Bottom Sheet trên Mobile --}}
                <div class="sp-bottom-sheet-handle-bar" onclick="toggleExplanation()" aria-hidden="true">
                    <div class="sp-bottom-sheet-handle"></div>
                </div>

                {{-- Dành cho Part 5 --}}
                <div class="sp-vp-explain-tabs" id="explainTabs">
                    <button type="button" class="sp-vp-explain-tab" onclick="switchExplainTab('trans')">Dịch</button>
                    <button type="button" class="sp-vp-explain-tab is-active" id="tabExpCurrent" onclick="switchExplainTab('exp')">139</button>
                    <button type="button" class="sp-vp-explain-tab" style="margin-left: auto; opacity: 0.9;" onclick="toggleExplanation()" aria-label="Đóng giải thích">✕</button>
                </div>
                <div class="sp-vp-explain-body" id="explainBody">
                    <strong>139.</strong><br>
                    (A) xác nhận<br>
                    (B) xác nhận<br>
                    (C) đã xác nhận<br>
                    (D) sẽ xác nhận<br><br>
                    <strong>=&gt; Chọn đáp án C</strong><br><br>
                    Phân tích: Chỗ trống nằm sau một cụm danh từ và trước "that", nên cần một động từ, nên không thể chọn B. Câu có chỗ trống là một câu tường thuật lại một sự việc đã diễn ra rồi, vậy nên thì của động từ chính trong câu phải ở thì quá khứ đơn, do đó câu C là đáp án đúng.
                </div>

                {{-- Dành cho Part 6 / Nhóm đoạn văn (BE-007.4) --}}
                <div class="sp-group-explain-header" id="groupExplainHeader" style="display: none;">
                    <div class="sp-group-explain-title" id="groupExplainTitle">
                        <span>💡 Kết quả đoạn văn</span>
                    </div>
                    <button type="button" class="sp-group-explain-close" onclick="toggleExplanation()" title="Đóng giải thích">✕</button>
                </div>
                <div class="sp-group-explain-body" id="groupExplainBody" style="display: none;">
                    {{-- Rendered by JS --}}
                </div>
            </section>

            {{-- CỘT 3: CHỌN ĐÁP ÁN (A, B, C, D) --}}
            <section class="sp-vp-answers-panel" id="answersCol">
                <div class="sp-group-answers-header" id="answersHeader">
                    <h3 class="sp-vp-answers-title" style="margin: 0;">Chọn đáp án</h3>
                    <span id="groupProgressBadge" class="sp-group-progress-badge" style="display: none;"></span>
                </div>

                <div id="questionsContainer">
                    {{-- Các câu hỏi được render bằng JavaScript linh hoạt --}}
                </div>
            </section>

        </main>
    </form>

    {{-- LỚP OVERLAY CHO BOTTOM SHEET TRÊN MOBILE --}}
    <div class="sp-explain-backdrop" id="explainBackdrop" onclick="toggleExplanation()"></div>

    {{-- 4. MODAL XÁC NHẬN THOÁT BÀI LUYỆN TẬP (ẢNH 4) --}}
    @php
        $isResumedAttempt = ! empty(session('reading_practice_session.attempt_id'));
    @endphp
    <x-modal id="exitConfirmModal" title="Bạn thật sự muốn dừng lại?" maxWidth="460px">
        <p id="exitConfirmModalDesc" style="margin: 0; font-size: 15px; color: #334155; line-height: 1.6;">
            @if($isResumedAttempt)
                Nhấn "Lưu & Thoát" để giữ tiến độ mới nhất. Nhấn "Thoát" sẽ hủy các thay đổi chưa lưu kể từ lần lưu gần nhất.
            @else
                Nhấn "Lưu & Thoát" để giữ bài và làm tiếp sau. Nhấn "Thoát" sẽ không lưu bài đang làm.
            @endif
        </p>
        <x-slot:actions>
            <x-button variant="primary" type="button" id="saveAndExitBtn" onclick="handleSaveAndExit()">
                <span id="saveAndExitBtnText">Lưu & Thoát</span>
            </x-button>
            <x-button variant="danger" type="button" onclick="window.location.href='{{ url('/luyen-tap/doc?exit=1') }}'">
                Thoát
            </x-button>
            <x-button variant="neutral" type="button" data-sp-modal-close>
                Tiếp tục
            </x-button>
        </x-slot:actions>
    </x-modal>

    {{-- 5. MODAL XÁC NHẬN NỘP BÀI (ẢNH 5) --}}
    <x-modal id="submitConfirmModal" title="Nộp bài" maxWidth="440px">
        <p id="submitConfirmModalMsg" style="margin: 0; font-size: 15px; color: #334155; line-height: 1.6;">
            Bạn đang ở câu hỏi cuối cùng, bạn có muốn nộp bài và xem lại kết quả không?
        </p>
        <x-slot:actions>
            <x-button variant="neutral" type="button" data-sp-modal-close>
                Hủy
            </x-button>
            <x-button variant="primary" type="button" id="submitConfirmSubmitBtn" onclick="submitExamDirectly()">
                Nộp bài
            </x-button>
        </x-slot:actions>
    </x-modal>

    {{-- 6. MODAL DANH SÁCH CÂU HỎI (QUESTION PALETTE) --}}
    <div class="sp-submit-modal-backdrop" id="questionDrawerModal" onclick="if(event.target === this) toggleQuestionDrawer()">
        <div class="sp-submit-modal" style="max-width: 480px; padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 class="sp-submit-modal__title" style="margin: 0; font-size: 18px;">Danh sách câu hỏi</h3>
                <button type="button" onclick="toggleQuestionDrawer()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748B;">✕</button>
            </div>
            <div style="display: flex; gap: 16px; margin-bottom: 16px; font-size: 12px; color: #64748B; flex-wrap: wrap;">
                <span style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 14px; height: 14px; border-radius: 4px; background: #5B4BF5;"></span> Đã làm
                </span>
                <span style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 14px; height: 14px; border-radius: 4px; border: 1.5px solid #CBD5E1; background: #FFF;"></span> Chưa làm
                </span>
                <span style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 14px; height: 14px; border-radius: 4px; background: #FEF3C7; border: 1.5px solid #F59E0B; color: #D97706; text-align: center; line-height: 12px; font-weight: bold;">⚑</span> Đã đánh dấu
                </span>
            </div>
            <div id="drawerQuestionsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(44px, 1fr)); gap: 8px; max-height: 320px; overflow-y: auto; padding: 4px;">
                {{-- Rendered by JS --}}
            </div>
        </div>
    </div>

    {{-- Dữ liệu và logic bài thi --}}
    <script>
        const questionsData = @json($questions);
        const currentPart = {{ (int) ($part ?? 5) }};
        const isGroupMode = (currentPart === 6 || currentPart === 7);

        let currentIndex = 0; // Dùng cho Part 5
        let currentGroupIndex = 0; // Dùng cho Part 6 & 7 (Group Mode)
        let groups = [];

        // Khởi tạo nhóm câu hỏi cho Part 6 & 7
        if (isGroupMode && questionsData && questionsData.length > 0) {
            let lastSetId = null;
            let currentGrp = null;
            questionsData.forEach(q => {
                const sId = q.question_set_id || ('grp_' + q.id);
                if (sId !== lastSetId) {
                    lastSetId = sId;
                    currentGrp = {
                        setId: sId,
                        passageTitle: q.passage_title || ('Bài đọc Part ' + currentPart),
                        passage: q.passage || '',
                        rangeText: q.question_range || '',
                        questions: []
                    };
                    groups.push(currentGrp);
                }
                currentGrp.questions.push(q);
            });
        }

        // Khởi tạo trạng thái đã lưu từ Session (hỗ trợ tải lại trang mà không mất dữ liệu)
        let rawSavedAnswers = @json(!empty($savedAnswers) ? $savedAnswers : (object)[]);
        let userAnswers = {};
        if (rawSavedAnswers && typeof rawSavedAnswers === 'object') {
            for (const [k, v] of Object.entries(rawSavedAnswers)) {
                if (v) userAnswers[k] = v;
            }
        }

        let rawSavedQStates = @json(!empty($savedQuestionStates) ? $savedQuestionStates : (object)[]);
        let questionStates = {};
        if (rawSavedQStates && typeof rawSavedQStates === 'object') {
            for (const [k, v] of Object.entries(rawSavedQStates)) {
                if (v) questionStates[k] = v;
            }
        }

        let rawSavedGStates = @json(!empty($savedGroupStates) ? $savedGroupStates : (object)[]);
        let groupStates = {};
        if (rawSavedGStates && typeof rawSavedGStates === 'object') {
            for (const [k, v] of Object.entries(rawSavedGStates)) {
                if (v && v.completed && v.result) {
                    groupStates[k] = {
                        completed: true,
                        correctCount: v.result.correct_count,
                        totalQuestions: v.question_ids ? v.question_ids.length : 0,
                        results: v.result.results,
                        explainOpen: true
                    };
                }
            }
        }

        let rawSavedFlags = @json(!empty($savedFlags) ? $savedFlags : (object)[]);
        let flaggedQuestions = {};
        if (rawSavedFlags && typeof rawSavedFlags === 'object') {
            for (const [k, v] of Object.entries(rawSavedFlags)) {
                if (v) flaggedQuestions[k] = true;
            }
        }

        let requestVersions = {}; // Race condition tracking: requestVersions[qid] = int
        let pendingPromises = []; // Theo dõi các AJAX request đang xử lý để đồng bộ trước khi submit
        let isCheckingAnswer = false;

        function syncHiddenAnswerInput(qid, letter) {
            let hiddenInput = document.getElementById('answer_input_' + qid);
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'answer_input_' + qid;
                hiddenInput.name = `answers[${qid}]`;
                document.getElementById('practiceExamForm').appendChild(hiddenInput);
            }
            hiddenInput.value = letter;
        }

        // Tự động đồng bộ các câu trả lời đã lưu vào form khi khởi tạo trang
        for (const [qid, letter] of Object.entries(userAnswers)) {
            syncHiddenAnswerInput(qid, letter);
        }

        // Dọn dẹp param &new=1 khỏi URL sau khi tải trang để khi F5 tải lại trang không bốc bài mới
        if (window.history.replaceState) {
            try {
                const url = new URL(window.location);
                if (url.searchParams.has('new')) {
                    url.searchParams.delete('new');
                    window.history.replaceState({}, document.title, url.toString());
                }
            } catch (e) {}
        }

        // Đóng thanh thông báo điều chỉnh số câu nhẹ nhàng (chỉ tác động UI, không đổi câu hỏi/bài thi)
        function dismissAdjustmentNotice() {
            const banner = document.getElementById('adjustmentNoticeBanner');
            if (banner) {
                banner.style.opacity = '0';
                banner.style.transition = 'opacity 0.2s ease, max-height 0.2s ease, padding 0.2s ease';
                setTimeout(() => {
                    banner.style.display = 'none';
                }, 200);
            }
        }

        // Trợ thủ mã hóa an toàn các ký tự HTML nhưng giữ nguyên định dạng câu hỏi
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // =========================================================================
        // 1. CHẾ ĐỘ LUYỆN TẬP PART 6 (THEO NHÓM ĐOẠN VĂN - BE-007.4)
        // =========================================================================
        function renderCurrentGroup(autoScroll = false) {
            if (!groups || groups.length === 0) {
                document.getElementById('passageRange').textContent = 'Chưa có câu hỏi';
                document.getElementById('passageTitle').style.display = 'block';
                document.getElementById('passageTitle').textContent = 'Ngân hàng câu hỏi chưa có dữ liệu hợp lệ';
                document.getElementById('passageContent').innerHTML = `
                    <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 14px; padding: 24px; font-size: 15px; color: #64748B; line-height: 1.7;">
                        Hiện tại chưa có đủ nhóm đoạn văn hợp lệ cho phần luyện tập này.<br>
                        Vui lòng quay lại danh sách để chọn phần luyện tập khác hoặc thử lại sau.
                    </div>
                `;
                document.getElementById('questionsContainer').innerHTML = '<div style="padding:24px;text-align:center;color:#64748B;font-weight:600;">Không có câu hỏi để hiển thị.</div>';
                document.getElementById('prevBtn').disabled = true;
                document.getElementById('nextBtn').disabled = true;
                return;
            }

            const grp = groups[currentGroupIndex];
            const gState = groupStates[grp.setId];

            // Cột 1: Đoạn văn
            document.getElementById('passageRange').textContent = `Đoạn ${currentGroupIndex + 1} / ${groups.length} • ${grp.rangeText || ''}`;
            document.getElementById('passageTitle').style.display = grp.passageTitle ? 'block' : 'none';
            document.getElementById('passageTitle').textContent = grp.passageTitle || '';
            document.getElementById('passageContent').innerHTML = grp.passage;

            // Ẩn các thành phần giải thích của Part 5
            const explainTabs = document.getElementById('explainTabs');
            const explainBody = document.getElementById('explainBody');
            if (explainTabs) explainTabs.style.display = 'none';
            if (explainBody) explainBody.style.display = 'none';

            const mainLayout = document.getElementById('mainLayout');
            if (mainLayout) {
                mainLayout.classList.add('is-group-mode');
            }

            // Tiến độ nhóm
            const answeredInGroup = grp.questions.filter(q => !!userAnswers[q.id]).length;
            const totalInGroup = grp.questions.length;
            const progressBadge = document.getElementById('groupProgressBadge');
            if (progressBadge) {
                progressBadge.style.display = 'inline-block';
                if (gState?.completed) {
                    progressBadge.classList.add('is-completed');
                    progressBadge.textContent = `Đã hoàn thành • Đúng ${gState.correctCount}/${gState.totalQuestions} câu`;
                } else {
                    progressBadge.classList.remove('is-completed');
                    progressBadge.textContent = `Đã trả lời: ${answeredInGroup}/${totalInGroup} câu`;
                }
            }

            // Render đồng thời cả 4 câu hỏi
            let questionsHtml = '';
            grp.questions.forEach(q => {
                const qRes = gState?.results ? gState.results.find(r => r.question_id === q.id) : null;
                const isGroupCompleted = !!gState?.completed;
                const isGroupRevealed = !!gState?.revealed && !isGroupCompleted;
                const userPick = userAnswers[q.id];

                questionsHtml += `
                    <div class="sp-group-q-card" id="group_q_${q.id}">
                        <div class="sp-group-q-card-head">
                            <span class="sp-group-q-num-pill">Câu ${q.question_number}</span>
                            ${q.stem ? `<span class="sp-group-q-stem">${escapeHtml(q.stem).replace(/\n/g, '<br>')}</span>` : ''}
                        </div>
                        <div class="sp-group-q-options">
                `;

                const opts = q.options || {};
                const labels = ['A', 'B', 'C', 'D'].filter(lbl => opts[lbl] !== undefined);

                for (const letter of labels) {
                    const text = opts[letter];
                    let rowClass = 'sp-vp-opt-row';
                    let badgeHtml = '';

                    if (isGroupCompleted && qRes) {
                        if (letter === qRes.correct_answer && letter === userPick) {
                            rowClass += ' is-correct is-selected';
                            badgeHtml = '<span class="sp-vp-opt-status-badge sp-vp-opt-status-badge--correct">✓ Đúng</span>';
                        } else if (letter === userPick && !qRes.is_right) {
                            rowClass += ' is-incorrect is-selected';
                            badgeHtml = '<span class="sp-vp-opt-status-badge sp-vp-opt-status-badge--incorrect">✗ Bạn chọn</span>';
                        } else if (letter === qRes.correct_answer && !qRes.is_right) {
                            rowClass += ' is-correct-target';
                            badgeHtml = '<span class="sp-vp-opt-status-badge sp-vp-opt-status-badge--correct">✓ Đáp án đúng</span>';
                        }
                    } else if (isGroupRevealed && qRes) {
                        if (letter === qRes.correct_answer) {
                            rowClass += ' is-correct-target';
                            badgeHtml = '<span class="sp-vp-opt-status-badge sp-vp-opt-status-badge--correct">✓ Đáp án đúng</span>';
                        } else if (letter === userPick) {
                            rowClass += ' is-selected';
                        }
                    } else {
                        // Nhóm đang làm: Chỉ đánh dấu phương án đã chọn bằng màu tím chuẩn hệ thống (.is-selected)
                        if (letter === userPick) {
                            rowClass += ' is-selected';
                        }
                    }

                    const clickAction = `onclick="selectGroupAnswer(${grp.setId}, ${q.id}, '${letter}')"`;

                    questionsHtml += `
                        <div class="${rowClass}" ${clickAction}>
                            <div class="sp-vp-opt-radio">${letter}</div>
                            <div class="sp-vp-opt-text">${escapeHtml(text)}</div>
                            ${badgeHtml}
                        </div>
                    `;
                }

                questionsHtml += `
                        </div>
                    </div>
                `;
            });

            document.getElementById('questionsContainer').innerHTML = questionsHtml;

            // Cột giữa: Vùng giải thích tổng hợp cho cả nhóm (BE-007.4)
            const groupExplainHeader = document.getElementById('groupExplainHeader');
            const groupExplainBody = document.getElementById('groupExplainBody');

            if ((gState?.completed || gState?.revealed) && gState.explainOpen !== false) {
                if (groupExplainHeader) groupExplainHeader.style.display = 'flex';
                if (groupExplainBody) groupExplainBody.style.display = 'block';

                if (gState.completed) {
                    document.getElementById('groupExplainTitle').innerHTML = `
                        <span>💡 Kết quả đoạn văn: Đúng <strong>${gState.correctCount}/${gState.totalQuestions}</strong> câu</span>
                    `;
                } else {
                    document.getElementById('groupExplainTitle').innerHTML = `
                        <span>💡 Hướng dẫn & Giải thích đoạn văn (${gState.totalQuestions} câu)</span>
                    `;
                }

                let expBodyHtml = '';
                grp.questions.forEach(q => {
                    const qRes = gState.results ? gState.results.find(r => r.question_id === q.id) : null;
                    if (!qRes) return;

                    const isRight = !!qRes.is_right;
                    let badgeStatus = '';
                    if (gState.completed) {
                        badgeStatus = isRight
                            ? `<span class="sp-group-explain-opt-badge is-correct">✓ Đúng (Đáp án ${qRes.correct_answer})</span>`
                            : `<span class="sp-group-explain-opt-badge is-wrong">✗ Bạn chọn: ${qRes.user_answer} — Đáp án đúng: ${qRes.correct_answer}</span>`;
                    } else {
                        badgeStatus = `<span class="sp-group-explain-opt-badge is-correct">Đáp án đúng: ${qRes.correct_answer}</span>`;
                    }

                    expBodyHtml += `
                        <div class="sp-group-explain-item">
                            <div class="sp-group-explain-item-head">
                                <span>Câu ${q.question_number}</span>
                                ${badgeStatus}
                            </div>
                            <div class="sp-group-explain-item-text">
                                <strong>Giải thích:</strong> ${escapeHtml(qRes.explanation || 'Chưa có giải thích cho câu hỏi này.')}
                            </div>
                        </div>
                    `;
                });
                groupExplainBody.innerHTML = expBodyHtml;
                syncExplanationUI(true, autoScroll);
            } else {
                if (groupExplainHeader) groupExplainHeader.style.display = 'none';
                if (groupExplainBody) groupExplainBody.style.display = 'none';
                syncExplanationUI(false, false);
            }

            // Nút điều hướng đoạn trước / sau
            document.getElementById('prevBtn').textContent = '← Đoạn trước';
            document.getElementById('nextBtn').textContent = 'Đoạn tiếp theo →';
            document.getElementById('prevBtn').disabled = (currentGroupIndex === 0);
            document.getElementById('nextBtn').disabled = false;

            const flagBtn = document.getElementById('flagBtn');
            if (flagBtn && grp) {
                const isAnyFlagged = grp.questions.some(q => !!flaggedQuestions[q.id]);
                flagBtn.style.color = isAnyFlagged ? '#F59E0B' : '#64748B';
            }

            updateProgress();
        }

        async function selectGroupAnswer(setId, qid, letter) {
            // Ghi nhận lựa chọn của người dùng (cho phép chọn và đổi đáp án bất kỳ lúc nào)
            userAnswers[qid] = letter;
            syncHiddenAnswerInput(qid, letter);

            requestVersions[qid] = (requestVersions[qid] || 0) + 1;
            const reqVersion = requestVersions[qid];

            const grp = groups[currentGroupIndex];
            const answeredInGroup = grp.questions.filter(q => !!userAnswers[q.id]).length;
            const totalInGroup = grp.questions.length;

            renderCurrentGroup();

            if (document.getElementById('questionDrawerModal')?.classList.contains('is-open')) {
                renderDrawerQuestions();
            }

            // Lưu tiến độ dở dang vào Session nếu chưa trả lời đủ nhóm
            if (answeredInGroup < totalInGroup) {
                saveGroupProgress(grp, qid, reqVersion);
            } else {
                // Đã chọn đủ cả nhóm: Kiểm tra đáp án toàn bộ nhóm
                await submitGroupAnswers(grp);
            }
        }

        async function saveGroupProgress(grp, qid, version) {
            const groupAnswers = {};
            grp.questions.forEach(q => {
                if (userAnswers[q.id]) {
                    groupAnswers[q.id] = userAnswers[q.id];
                }
            });

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value;

                const p = fetch('{{ route("luyen-tap.doc.kiem-tra-nhom") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        question_set_id: grp.setId,
                        action: 'save_progress',
                        question_id: qid,
                        version: version,
                        answers: groupAnswers
                    })
                });
                pendingPromises.push(p);
                await p;
            } catch (err) {
                // Lưu tiến độ nền không ảnh hưởng thao tác người dùng
            }
        }

        async function submitGroupAnswers(grp) {
            if (isCheckingAnswer) return;
            isCheckingAnswer = true;

            const groupAnswers = {};
            grp.questions.forEach(q => {
                groupAnswers[q.id] = userAnswers[q.id];
            });

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value;

                const fetchPromise = fetch('{{ route("luyen-tap.doc.kiem-tra-nhom") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        question_set_id: grp.setId,
                        answers: groupAnswers
                    })
                });
                pendingPromises.push(fetchPromise);
                const response = await fetchPromise;

                const data = await response.json();
                if (data && data.success) {
                    groupStates[grp.setId] = {
                        completed: true,
                        correctCount: data.correct_count,
                        totalQuestions: data.total_questions,
                        results: data.results,
                        explainOpen: true
                    };

                    data.results.forEach(res => {
                        questionStates[res.question_id] = {
                            answered: true,
                            userPick: res.user_answer,
                            correctPick: res.correct_answer,
                            isCorrect: !!res.is_right,
                            revealed: false,
                            explanation: res.explanation
                        };
                    });

                    renderCurrentGroup(true);

                    if (document.getElementById('questionDrawerModal')?.classList.contains('is-open')) {
                        renderDrawerQuestions();
                    }
                } else {
                    alert(data?.message || 'Có lỗi khi kiểm tra nhóm câu hỏi. Vui lòng thử lại.');
                    renderCurrentGroup();
                }
            } catch (err) {
                console.error('Lỗi khi gửi đáp án nhóm:', err);
                alert('Không thể kết nối đến máy chủ. Vui lòng thử lại.');
                renderCurrentGroup();
            } finally {
                isCheckingAnswer = false;
            }
        }

        // =========================================================================
        // 2. CHẾ ĐỘ LUYỆN TẬP PART 5 (TỪNG CÂU ĐỘC LẬP - BE-007.3 GIỮ NGUYÊN)
        // =========================================================================
        function renderCurrentQuestion(autoScroll = false) {
            if (!questionsData || questionsData.length === 0) {
                document.getElementById('passageRange').textContent = 'Chưa có câu hỏi';
                document.getElementById('passageTitle').style.display = 'block';
                document.getElementById('passageTitle').textContent = 'Ngân hàng câu hỏi chưa có dữ liệu hợp lệ';
                document.getElementById('passageContent').innerHTML = `
                    <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 14px; padding: 24px; font-size: 15px; color: #64748B; line-height: 1.7;">
                        Hiện tại chưa có đủ câu hỏi hoặc nhóm bài đọc hợp lệ cho phần luyện tập này.<br>
                        Vui lòng quay lại danh sách để chọn phần luyện tập khác hoặc thử lại sau.
                    </div>
                `;
                document.getElementById('explainBody').innerHTML = '<div style="color:#64748B;font-size:14px;padding:12px 0;">Không có nội dung giải thích.</div>';
                document.getElementById('questionsContainer').innerHTML = '<div style="padding:24px;text-align:center;color:#64748B;font-weight:600;">Không có câu hỏi để hiển thị.</div>';
                document.getElementById('prevBtn').disabled = true;
                document.getElementById('nextBtn').disabled = true;
                setExplanationVisible(false);
                return;
            }

            const q = questionsData[currentIndex];
            const qState = questionStates[q.id];
            const isPart5 = (q.part_number === 5);

            // Khôi phục giao diện Part 5: Bỏ chế độ nhóm và khôi phục tab/body Part 5
            const mainLayout = document.getElementById('mainLayout');
            if (mainLayout) {
                mainLayout.classList.remove('is-group-mode');
            }
            const groupExplainHeader = document.getElementById('groupExplainHeader');
            const groupExplainBody = document.getElementById('groupExplainBody');
            if (groupExplainHeader) groupExplainHeader.style.display = 'none';
            if (groupExplainBody) groupExplainBody.style.display = 'none';

            const explainTabs = document.getElementById('explainTabs');
            const explainBody = document.getElementById('explainBody');
            if (explainTabs) explainTabs.style.display = 'flex';
            if (explainBody) explainBody.style.display = 'block';

            // Cập nhật Cột 1: Passage / Ngữ cảnh câu hỏi
            if (q.passage) {
                document.getElementById('passageRange').textContent = q.question_range || q.part;
                document.getElementById('passageTitle').style.display = q.passage_title ? 'block' : 'none';
                document.getElementById('passageTitle').textContent = q.passage_title || '';
                document.getElementById('passageContent').innerHTML = q.passage;
            } else {
                document.getElementById('passageRange').textContent = 'PART 5 · Hoàn thành câu';
                document.getElementById('passageTitle').style.display = 'none';
                document.getElementById('passageContent').innerHTML = `
                    <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 14px; padding: 20px; font-size: 16px; font-weight: 700; color: #1E293B; line-height: 1.7;">
                        <div style="font-size:12px;font-weight:600;color:#64748B;margin-bottom:10px;letter-spacing:0.8px;">CÂU HỎI ${q.question_number}</div>
                        ${escapeHtml(q.stem)}
                    </div>
                `;
            }

            // Cập nhật Cột 2: Giải thích chi tiết
            const tabExp = document.getElementById('tabExpCurrent');
            if (tabExp) {
                tabExp.textContent = q.question_number;
            }

            if (qState && (qState.answered || qState.revealed)) {
                if (qState.answered && qState.isCorrect) {
                    document.getElementById('explainBody').innerHTML = `
                        <div style="display: flex; align-items: center; gap: 8px; background: rgba(34, 197, 94, 0.25); border: 1px solid rgba(255,255,255,0.4); padding: 10px 14px; border-radius: 10px; margin-bottom: 14px; font-weight: 800; font-size: 14.5px;">
                            <span>✓ Chính xác!</span>
                            <span style="margin-left: auto; background: #22C55E; color: #FFF; padding: 2px 8px; border-radius: 6px; font-size: 12px;">Đáp án ${qState.correctPick}</span>
                        </div>
                        <strong>Phân tích chi tiết:</strong><br><br>
                        <div style="font-size: 14px; line-height: 1.7;">${qState.explanation}</div>
                    `;
                } else if (qState.answered && !qState.isCorrect) {
                    document.getElementById('explainBody').innerHTML = `
                        <div style="display: flex; align-items: center; gap: 8px; background: rgba(239, 68, 68, 0.25); border: 1px solid rgba(255,255,255,0.4); padding: 10px 14px; border-radius: 10px; margin-bottom: 14px; font-weight: 800; font-size: 14.5px;">
                            <span>✗ Chưa chính xác</span>
                            <span style="margin-left: auto; background: #22C55E; color: #FFF; padding: 2px 8px; border-radius: 6px; font-size: 12px;">Đáp án đúng: ${qState.correctPick}</span>
                        </div>
                        <div style="font-size: 13px; margin-bottom: 10px; opacity: 0.95;">Bạn đã chọn: <strong>${qState.userPick}</strong></div>
                        <strong>Phân tích chi tiết:</strong><br><br>
                        <div style="font-size: 14px; line-height: 1.7;">${qState.explanation}</div>
                    `;
                } else if (qState.revealed && !qState.answered) {
                    document.getElementById('explainBody').innerHTML = `
                        <div style="display: flex; align-items: center; gap: 8px; background: rgba(245, 158, 11, 0.25); border: 1px solid rgba(255,255,255,0.4); padding: 10px 14px; border-radius: 10px; margin-bottom: 14px; font-weight: 800; font-size: 14.5px;">
                            <span>💡 Đã mở xem đáp án (Chưa trả lời)</span>
                            <span style="margin-left: auto; background: #22C55E; color: #FFF; padding: 2px 8px; border-radius: 6px; font-size: 12px;">Đáp án: ${qState.correctPick}</span>
                        </div>
                        <strong>Phân tích chi tiết:</strong><br><br>
                        <div style="font-size: 14px; line-height: 1.7;">${qState.explanation}</div>
                    `;
                }

                syncExplanationUI(qState.explainOpen !== false, autoScroll);
            } else {
                syncExplanationUI(false, false);
                document.getElementById('explainBody').innerHTML = `
                    <div style="color: #64748B; font-size: 14px; line-height: 1.6; padding: 6px 0;">
                        🔒 <strong>Chưa mở giải thích.</strong><br><br>
                        Bấm nút <strong>"Giải thích"</strong> hoặc chọn một đáp án để hệ thống kiểm tra và mở phân tích chi tiết.
                    </div>
                `;
            }

            // Cột 3: Stem + Lựa chọn đáp án
            let stemHtml = '';
            if (q.passage && q.stem) {
                stemHtml = `
                    <div class="sp-vp-question-stem">
                        <span class="sp-vp-qnum-label">Câu ${q.question_number}</span>
                        <p class="sp-vp-stem-text">${escapeHtml(q.stem)}</p>
                    </div>
                `;
            }

            let questionsHtml = `<div class="sp-vp-question-block">`;
            if (q.passage) {
                questionsHtml += `<div class="sp-vp-qnum" style="margin-bottom:16px;">Câu ${q.question_number}</div>`;
            }

            const opts = q.options || {};
            const labels = (isPart5)
                ? ['A', 'B', 'C', 'D'].filter(lbl => opts[lbl] !== undefined)
                : Object.keys(opts).sort();

            for (const letter of labels) {
                const text = opts[letter];
                let rowClass = 'sp-vp-opt-row';
                let badgeHtml = '';

                if (qState?.answered) {
                    if (letter === qState.userPick && qState.isCorrect) {
                        rowClass += ' is-correct is-selected';
                        badgeHtml = '<span class="sp-vp-opt-status-badge sp-vp-opt-status-badge--correct">✓ Đúng</span>';
                    } else if (letter === qState.userPick && !qState.isCorrect) {
                        rowClass += ' is-incorrect is-selected';
                        badgeHtml = '<span class="sp-vp-opt-status-badge sp-vp-opt-status-badge--incorrect">✗ Bạn chọn</span>';
                    } else if (letter === qState.correctPick && !qState.isCorrect) {
                        rowClass += ' is-correct-target';
                        badgeHtml = '<span class="sp-vp-opt-status-badge sp-vp-opt-status-badge--correct">✓ Đáp án đúng</span>';
                    }
                } else if (qState?.revealed) {
                    if (letter === qState.correctPick) {
                        rowClass += ' is-correct-target';
                        badgeHtml = '<span class="sp-vp-opt-status-badge sp-vp-opt-status-badge--correct">✓ Đáp án đúng</span>';
                    } else if (letter === userAnswers[q.id]) {
                        rowClass += ' is-selected';
                    }
                } else if (letter === userAnswers[q.id]) {
                    rowClass += ' is-selected';
                }

                // Luôn cho phép người học chọn hoặc đổi đáp án trong chế độ luyện tập
                const clickAction = `onclick="selectAnswer(${q.id}, '${letter}')"`;

                questionsHtml += `
                    <div class="${rowClass}" ${clickAction}>
                        <div class="sp-vp-opt-radio">${letter}</div>
                        <div class="sp-vp-opt-text">${escapeHtml(text)}</div>
                        ${badgeHtml}
                    </div>
                `;
            }
            questionsHtml += `</div>`;

            document.getElementById('questionsContainer').innerHTML = stemHtml + questionsHtml;

            updateProgress();

            const flagBtn = document.getElementById('flagBtn');
            if (flagBtn) {
                flagBtn.style.color = flaggedQuestions[q.id] ? '#F59E0B' : '#64748B';
            }

            document.getElementById('prevBtn').textContent = '← Câu trước';
            document.getElementById('nextBtn').textContent = 'Câu sau →';
            document.getElementById('prevBtn').disabled = (currentIndex === 0);
            document.getElementById('nextBtn').disabled = false;
        }

        let savedBodyScrollY = 0;

        function isCurrentExplanationOpen() {
            if (isGroupMode) {
                const grp = groups[currentGroupIndex];
                const gState = grp ? groupStates[grp.setId] : null;
                return !!((gState?.completed || gState?.revealed) && gState.explainOpen !== false);
            } else {
                const q = questionsData ? questionsData[currentIndex] : null;
                const qState = q ? questionStates[q.id] : null;
                return !!(qState && (qState.answered || qState.revealed) && qState.explainOpen !== false);
            }
        }

        function syncExplanationUI(isOpen, autoScroll = false) {
            const explainCol = document.getElementById('explainCol');
            const mainLayout = document.getElementById('mainLayout');
            const toggleBtn = document.getElementById('explainToggleBtn');
            const backdrop = document.getElementById('explainBackdrop');
            const isMobile = (window.innerWidth < 768);
            const isTablet = (window.innerWidth >= 768 && window.innerWidth < 1200);

            if (isOpen) {
                if (toggleBtn) toggleBtn.classList.add('is-active');
                if (mainLayout) mainLayout.classList.add('with-explanation');

                if (isMobile) {
                    if (explainCol) {
                        explainCol.style.display = 'flex';
                        void explainCol.offsetHeight;
                        explainCol.classList.add('is-bottom-sheet-open');
                    }
                    if (backdrop) {
                        backdrop.classList.add('is-open');
                    }
                    if (!document.body.classList.contains('sp-bottom-sheet-open')) {
                        savedBodyScrollY = window.pageYOffset || document.documentElement.scrollTop;
                        document.body.classList.add('sp-bottom-sheet-open');
                    }
                } else {
                    // Desktop & Tablet
                    if (document.body.classList.contains('sp-bottom-sheet-open')) {
                        document.body.classList.remove('sp-bottom-sheet-open');
                        window.scrollTo(0, savedBodyScrollY);
                    }
                    if (backdrop) backdrop.classList.remove('is-open');
                    if (explainCol) {
                        explainCol.classList.remove('is-bottom-sheet-open');
                        explainCol.style.display = 'flex';
                    }
                    if (isTablet && autoScroll && explainCol) {
                        setTimeout(() => {
                            explainCol.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }, 100);
                    }
                }
            } else {
                if (toggleBtn) toggleBtn.classList.remove('is-active');
                if (mainLayout) mainLayout.classList.remove('with-explanation');

                if (backdrop) backdrop.classList.remove('is-open');
                if (document.body.classList.contains('sp-bottom-sheet-open')) {
                    document.body.classList.remove('sp-bottom-sheet-open');
                    window.scrollTo(0, savedBodyScrollY);
                }

                if (explainCol) {
                    explainCol.classList.remove('is-bottom-sheet-open');
                    if (isMobile) {
                        setTimeout(() => {
                            if (!isCurrentExplanationOpen()) {
                                explainCol.style.display = 'none';
                            }
                        }, 280);
                    } else {
                        explainCol.style.display = 'none';
                    }
                }
            }
        }

        function setExplanationVisible(visible, autoScroll = false) {
            syncExplanationUI(visible, autoScroll);
        }

        async function selectAnswer(qid, letter) {
            // Cập nhật ngay lập tức UI và form
            userAnswers[qid] = letter;
            syncHiddenAnswerInput(qid, letter);
            updateProgress();

            requestVersions[qid] = (requestVersions[qid] || 0) + 1;
            const reqVersion = requestVersions[qid];

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value;

                const fetchPromise = fetch('{{ route("luyen-tap.doc.kiem-tra-cau-hoi") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        question_id: qid,
                        selected_option: letter,
                        action: 'answer',
                        version: reqVersion
                    })
                });
                pendingPromises.push(fetchPromise);
                const response = await fetchPromise;

                const data = await response.json();

                // Kiểm tra race condition: nếu đáp án đã thay đổi hoặc có request mới hơn, bỏ qua response cũ
                if (reqVersion !== requestVersions[qid] || userAnswers[qid] !== letter) {
                    return;
                }

                if (data && data.success) {
                    questionStates[qid] = {
                        answered: true,
                        userPick: letter,
                        correctPick: data.correct_answer,
                        isCorrect: !!data.is_right,
                        revealed: false,
                        explanation: data.explanation || 'Chưa có giải thích cho câu hỏi này.',
                        explainOpen: true
                    };

                    renderCurrentQuestion(true);

                    if (document.getElementById('questionDrawerModal')?.classList.contains('is-open')) {
                        renderDrawerQuestions();
                    }
                } else {
                    alert(data?.message || 'Không thể kiểm tra câu trả lời, vui lòng thử lại.');
                }
            } catch (err) {
                console.error('Lỗi khi gửi đáp án:', err);
            }
        }

        async function toggleExplanation() {
            if (!questionsData || questionsData.length === 0) return;

            if (isGroupMode) {
                const grp = groups[currentGroupIndex];
                const gState = groupStates[grp.setId];

                if (gState && (gState.completed || gState.revealed)) {
                    gState.explainOpen = !gState.explainOpen;
                    renderCurrentGroup(gState.explainOpen);
                    return;
                }

                if (isCheckingAnswer) return;
                isCheckingAnswer = true;
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        || document.querySelector('input[name="_token"]')?.value;

                    const response = await fetch('{{ route("luyen-tap.doc.kiem-tra-nhom") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            question_set_id: grp.setId,
                            action: 'reveal'
                        })
                    });

                    const data = await response.json();
                    if (data && data.success) {
                        groupStates[grp.setId] = {
                            completed: false,
                            revealed: true,
                            totalQuestions: data.total_questions,
                            results: data.results,
                            explainOpen: true
                        };

                        renderCurrentGroup(true);

                        if (document.getElementById('questionDrawerModal')?.classList.contains('is-open')) {
                            renderDrawerQuestions();
                        }
                    } else {
                        alert(data?.message || 'Không thể tải giải thích cho đoạn văn này.');
                    }
                } catch (err) {
                    console.error('Lỗi khi tải giải thích nhóm:', err);
                } finally {
                    isCheckingAnswer = false;
                }
                return;
            }

            const q = questionsData[currentIndex];
            const qState = questionStates[q.id];

            if (qState && (qState.answered || qState.revealed)) {
                qState.explainOpen = !qState.explainOpen;
                syncExplanationUI(qState.explainOpen, true);
                return;
            }

            if (isCheckingAnswer) return;
            isCheckingAnswer = true;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value;

                const response = await fetch('{{ route("luyen-tap.doc.kiem-tra-cau-hoi") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        question_id: q.id,
                        action: 'reveal'
                    })
                });

                const data = await response.json();
                if (data && data.success) {
                    questionStates[q.id] = {
                        answered: false,
                        userPick: null,
                        correctPick: data.correct_answer,
                        isCorrect: false,
                        revealed: true,
                        explanation: data.explanation || 'Chưa có giải thích cho câu hỏi này.',
                        explainOpen: true
                    };

                    delete userAnswers[q.id];
                    const hiddenInput = document.getElementById('answer_input_' + q.id);
                    if (hiddenInput) {
                        hiddenInput.remove();
                    }

                    renderCurrentQuestion(true);

                    if (document.getElementById('questionDrawerModal')?.classList.contains('is-open')) {
                        renderDrawerQuestions();
                    }
                }
            } catch (err) {
                console.error('Lỗi khi tải giải thích:', err);
            } finally {
                isCheckingAnswer = false;
            }
        }

        function scrollToContentTopIfMobile() {
            if (window.innerWidth < 768) {
                const mainLayout = document.getElementById('mainLayout');
                if (mainLayout) {
                    mainLayout.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }

        function prevQuestion() {
            if (isGroupMode) {
                if (currentGroupIndex > 0) {
                    currentGroupIndex--;
                    renderCurrentGroup();
                    scrollToContentTopIfMobile();
                }
                return;
            }
            if (currentIndex > 0) {
                currentIndex--;
                renderCurrentQuestion();
                scrollToContentTopIfMobile();
            }
        }

        function nextQuestion() {
            if (isGroupMode) {
                if (currentGroupIndex < groups.length - 1) {
                    currentGroupIndex++;
                    renderCurrentGroup();
                    scrollToContentTopIfMobile();
                } else {
                    handlePreSubmit();
                }
                return;
            }
            if (currentIndex < questionsData.length - 1) {
                currentIndex++;
                renderCurrentQuestion();
                scrollToContentTopIfMobile();
            } else {
                handlePreSubmit();
            }
        }

        function switchExplainTab(tab) {
            if (!questionsData || questionsData.length === 0) return;
            const q = questionsData[currentIndex];
            const qState = questionStates[q.id];

            if (tab === 'trans') {
                document.getElementById('explainBody').innerHTML = `
                    <strong>Dịch nghĩa tiếng Việt:</strong><br><br>
                    ${q.translation ? escapeHtml(q.translation) : '<div style="color:#64748B;font-size:13.5px;line-height:1.6;"><em>Bản dịch chi tiết sẽ được mở khóa sau khi bạn bấm nộp bài.</em></div>'}
                `;
            } else {
                if (qState && (qState.answered || qState.revealed)) {
                    renderCurrentQuestion();
                } else {
                    document.getElementById('explainBody').innerHTML = `
                        <div style="color: #64748B; font-size: 14px; line-height: 1.6; padding: 6px 0;">
                            🔒 <strong>Chưa mở giải thích.</strong><br><br>
                            Bấm nút <strong>"Giải thích"</strong> hoặc chọn một đáp án để hệ thống kiểm tra và mở phân tích chi tiết.
                        </div>
                    `;
                }
            }
        }

        function updateProgress() {
            const answeredCount = Object.keys(userAnswers).length;
            const total = questionsData ? questionsData.length : 0;
            const pct = total > 0 ? Math.round((answeredCount / total) * 100) : 0;
            const bar = document.getElementById('progressBarFill');
            if (bar) {
                bar.style.width = pct + '%';
            }
        }

        function toggleFlagCurrentQuestion() {
            if (!questionsData || questionsData.length === 0) return;

            if (isGroupMode) {
                const grp = groups[currentGroupIndex];
                if (!grp || !grp.questions || grp.questions.length === 0) return;
                const isAnyFlagged = grp.questions.some(q => !!flaggedQuestions[q.id]);
                grp.questions.forEach(q => {
                    flaggedQuestions[q.id] = !isAnyFlagged;
                });
                renderCurrentGroup();
            } else {
                const q = questionsData[currentIndex];
                if (!q) return;
                flaggedQuestions[q.id] = !flaggedQuestions[q.id];
                renderCurrentQuestion();
            }

            if (document.getElementById('questionDrawerModal')?.classList.contains('is-open')) {
                renderDrawerQuestions();
            }
        }

        // Drawer điều hướng nhanh câu hỏi
        function toggleQuestionDrawer() {
            const modal = document.getElementById('questionDrawerModal');
            if (!modal) return;
            if (modal.classList.contains('is-open')) {
                modal.classList.remove('is-open');
            } else {
                renderDrawerQuestions();
                modal.classList.add('is-open');
            }
        }

        function renderDrawerQuestions() {
            const grid = document.getElementById('drawerQuestionsGrid');
            if (!grid) return;
            if (!questionsData || questionsData.length === 0) {
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #64748B; padding: 16px;">Không có câu hỏi.</div>';
                return;
            }

            let html = '';
            questionsData.forEach((item, idx) => {
                const state = questionStates[item.id];
                const isFlagged = !!flaggedQuestions[item.id];
                const isCurrent = isGroupMode
                    ? (groups[currentGroupIndex]?.questions.some(q => q.id === item.id))
                    : (idx === currentIndex);

                let bg = '#FFFFFF';
                let textColor = '#1E293B';
                let borderColor = isCurrent ? '#F59E0B' : '#CBD5E1';
                let borderWidth = isCurrent ? '2.5px' : '1.5px';

                const hasAnswer = !!userAnswers[item.id];
                if (hasAnswer) {
                    if (state?.answered) {
                        if (state.isCorrect) {
                            bg = '#22C55E';
                            textColor = '#FFFFFF';
                            borderColor = isCurrent ? '#F59E0B' : '#22C55E';
                        } else {
                            bg = '#EF4444';
                            textColor = '#FFFFFF';
                            borderColor = isCurrent ? '#F59E0B' : '#EF4444';
                        }
                    } else {
                        // Đã chọn trong nhóm đang làm
                        bg = '#EDE9FE';
                        textColor = '#5B4BF5';
                        borderColor = isCurrent ? '#F59E0B' : '#5B4BF5';
                    }
                } else {
                    // Chưa trả lời: Dù có xem giải thích trước (revealed) hay chưa, vẫn hiển thị là chưa làm
                    bg = '#FFFFFF';
                    textColor = '#1E293B';
                    borderColor = isCurrent ? '#F59E0B' : '#CBD5E1';
                }

                html += `
                    <button type="button" onclick="jumpToQuestion(${idx})" style="
                        position: relative;
                        height: 42px;
                        border-radius: 10px;
                        border: ${borderWidth} solid ${borderColor};
                        background: ${bg};
                        color: ${textColor};
                        font-weight: 800;
                        font-size: 13.5px;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: all 0.15s ease;
                    ">
                        ${item.question_number}
                        ${isFlagged ? '<span style="position: absolute; top: 1px; right: 3px; font-size: 10px; color: #F59E0B;">⚑</span>' : ''}
                    </button>
                `;
            });
            grid.innerHTML = html;
        }

        function jumpToQuestion(idx) {
            if (isGroupMode) {
                const targetQ = questionsData[idx];
                if (!targetQ) return;
                const targetGroupIdx = groups.findIndex(g => g.questions.some(q => q.id === targetQ.id));
                if (targetGroupIdx !== -1) {
                    currentGroupIndex = targetGroupIdx;
                    renderCurrentGroup();
                    const qElem = document.getElementById('group_q_' + targetQ.id);
                    if (qElem) {
                        qElem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                const modal = document.getElementById('questionDrawerModal');
                if (modal) modal.classList.remove('is-open');
                return;
            }

            if (idx >= 0 && idx < questionsData.length) {
                currentIndex = idx;
                renderCurrentQuestion();
                scrollToContentTopIfMobile();
                const modal = document.getElementById('questionDrawerModal');
                if (modal) modal.classList.remove('is-open');
            }
        }

        function openExitConfirmModal() {
            const modal = document.getElementById('exitConfirmModal');
            if (modal) {
                modal.hidden = false;
                document.body.style.overflow = 'hidden';
            }
        }

        let isSavingAndExiting = false;
        async function handleSaveAndExit() {
            if (isSavingAndExiting) return;
            isSavingAndExiting = true;

            const saveBtn = document.getElementById('saveAndExitBtn');
            const saveBtnText = document.getElementById('saveAndExitBtnText');
            if (saveBtn) {
                saveBtn.disabled = true;
            }
            if (saveBtnText) {
                saveBtnText.textContent = 'Đang lưu...';
            }

            // Đảm bảo mọi request cập nhật Session dở dang đã hoàn tất
            if (pendingPromises && pendingPromises.length > 0) {
                try {
                    await Promise.allSettled(pendingPromises);
                } catch (e) {}
            }

            try {
                const response = await fetch('{{ route('luyen-tap.doc.luu-va-thoat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        part: currentPart,
                        answers: userAnswers
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    window.location.href = data.redirect || '{{ route('luyen-tap.doc') }}';
                } else {
                    alert(data.message || 'Không thể lưu bài luyện tập. Vui lòng thử lại.');
                    if (saveBtn) saveBtn.disabled = false;
                    if (saveBtnText) saveBtnText.textContent = 'Lưu & Thoát';
                    isSavingAndExiting = false;
                }
            } catch (err) {
                alert('Lỗi kết nối khi lưu bài. Vui lòng thử lại.');
                if (saveBtn) saveBtn.disabled = false;
                if (saveBtnText) saveBtnText.textContent = 'Lưu & Thoát';
                isSavingAndExiting = false;
            }
        }

        // Xử lý nộp bài: Khớp logic kiểm tra Ảnh 5
        function handlePreSubmit() {
            const total = questionsData ? questionsData.length : 0;
            if (total === 0) {
                alert('Không có câu hỏi nào trong bài để nộp.');
                return;
            }
            const answeredCount = Object.keys(userAnswers).filter(k => !!userAnswers[k]).length;
            const msgEl = document.getElementById('submitConfirmModalMsg');

            if (answeredCount < total) {
                const unansCount = total - answeredCount;
                if (msgEl) {
                    msgEl.textContent = `Bạn còn ${unansCount} câu chưa trả lời. Bạn có muốn nộp bài và xem kết quả không?`;
                }
            } else {
                if (msgEl) {
                    msgEl.textContent = isGroupMode
                        ? 'Bạn đang ở đoạn văn cuối cùng, bạn có muốn nộp bài và xem lại kết quả không?'
                        : 'Bạn đang ở câu hỏi cuối cùng, bạn có muốn nộp bài và xem lại kết quả không?';
                }
            }

            const modal = document.getElementById('submitConfirmModal');
            if (modal) {
                modal.hidden = false;
                document.body.style.overflow = 'hidden';
            }
        }

        let isSubmittingExam = false;
        async function submitExamDirectly() {
            if (isSubmittingExam) return;
            isSubmittingExam = true;

            const submitBtn = document.getElementById('submitConfirmSubmitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Đang nộp...</span>';
            }

            // Đảm bảo mọi request cập nhật Session dở dang đã hoàn tất trước khi submit form
            if (pendingPromises && pendingPromises.length > 0) {
                try {
                    await Promise.allSettled(pendingPromises);
                } catch (e) {}
            }

            document.getElementById('practiceExamForm').submit();
        }

        // Khởi tạo hiển thị bài làm ban đầu
        if (isGroupMode) {
            renderCurrentGroup();
        } else {
            renderCurrentQuestion();
        }

        // Đồng bộ giao diện Responsive khi thay đổi kích thước cửa sổ mà không tải lại bài làm
        window.addEventListener('resize', function() {
            const isOpen = isCurrentExplanationOpen();
            syncExplanationUI(isOpen, false);
        });
    </script>
</body>
</html>
