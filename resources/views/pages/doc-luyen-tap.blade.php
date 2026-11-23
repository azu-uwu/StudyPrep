<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            <a href="{{ url('/luyen-tap/doc') }}" class="sp-vp-close-btn" title="Thoát bài luyện tập" onclick="return confirmExit(event)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </a>
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
        <div style="background: #EFF6FF; border-bottom: 1.5px solid #BFDBFE; color: #1D4ED8; padding: 10px 24px; font-size: 13.5px; font-weight: 600; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <span>{{ $noticeMessage }}</span>
        </div>
    @endif
    <div class="sp-vp-subnav">
        <div class="sp-vp-nav-controls">
            <button type="button" class="sp-vp-nav-btn" onclick="prevQuestion()" id="prevBtn">← Câu trước</button>
            <button type="button" class="sp-vp-explain-btn is-active" id="explainToggleBtn" onclick="toggleExplanation()">
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
        <input type="hidden" name="total_count" value="{{ count($questions) }}">
        <input type="hidden" name="time_spent" id="timeSpentInput" value="0">

        {{-- BỐ CỤC 3 CỘT (KHỚP ẢNH MẪU 4) --}}
        <main class="sp-vp-main-layout with-explanation" id="mainLayout">

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
            <section class="sp-vp-explain-panel" id="explainCol">
                <div class="sp-vp-explain-tabs" id="explainTabs">
                    <button type="button" class="sp-vp-explain-tab" onclick="switchExplainTab('trans')">Dịch</button>
                    <button type="button" class="sp-vp-explain-tab is-active" id="tabExpCurrent" onclick="switchExplainTab('exp')">139</button>
                    <button type="button" class="sp-vp-explain-tab" style="margin-left: auto; opacity: 0.9;" onclick="toggleExplanation()">✕</button>
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
            </section>

            {{-- CỘT 3: CHỌN ĐÁP ÁN (A, B, C, D) --}}
            <section class="sp-vp-answers-panel" id="answersCol">
                <h3 class="sp-vp-answers-title">Chọn đáp án</h3>

                <div id="questionsContainer">
                    {{-- Các câu hỏi được render bằng JavaScript linh hoạt --}}
                </div>
            </section>

        </main>
    </form>

    {{-- 4. MODAL NỘP BÀI: CẢNH BÁO CHƯA LÀM CÂU NÀO (ẢNH MẪU 2) --}}
    <div class="sp-submit-modal-backdrop" id="emptySubmitModal">
        <div class="sp-submit-modal">
            <h3 class="sp-submit-modal__title">Nộp bài</h3>
            <p class="sp-submit-modal__msg">Bạn chưa làm câu nào, hãy làm ít nhất 1 câu để nộp bài</p>
            <div class="sp-submit-modal__actions">
                <button type="button" class="sp-btn-close-warning" onclick="closeSubmitModals()">Đóng</button>
            </div>
        </div>
    </div>

    {{-- 5. MODAL NỘP BÀI: CÒN CÂU CHƯA TRẢ LỜI (ẢNH MẪU 1) --}}
    <div class="sp-submit-modal-backdrop" id="incompleteSubmitModal">
        <div class="sp-submit-modal">
            <h3 class="sp-submit-modal__title">Nộp bài</h3>
            <p class="sp-submit-modal__msg">Bạn có câu hỏi chưa trả lời, bạn có muốn xem lại trước khi nộp không?</p>
            <div class="sp-submit-modal__actions">
                <button type="button" class="sp-btn-revisit" onclick="closeSubmitModals()">Xem lại</button>
                <button type="button" class="sp-btn-confirm-submit" onclick="submitExamDirectly()">Nộp bài</button>
            </div>
        </div>
    </div>

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
        let currentIndex = 0;
        let userAnswers = {};
        let flaggedQuestions = {};
        let timerSeconds = 0;
        let isExplanationOpen = true;

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

        // Bắt đầu đếm thời gian làm bài
        const examTimer = setInterval(function() {
            timerSeconds++;
            const timeInput = document.getElementById('timeSpentInput');
            if (timeInput) {
                timeInput.value = timerSeconds;
            }
        }, 1000);

        function renderCurrentQuestion() {
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
                return;
            }
            const q = questionsData[currentIndex];

            // Cập nhật Cột 1: Passage / Context
            if (q.passage) {
                // Part 6 & 7: Hiển thị đoạn văn/passage
                document.getElementById('passageRange').textContent = q.question_range || q.part;
                document.getElementById('passageTitle').style.display = q.passage_title ? 'block' : 'none';
                document.getElementById('passageTitle').textContent = q.passage_title || '';
                document.getElementById('passageContent').innerHTML = q.passage;
            } else {
                // Part 5: Hiển thị câu hỏi trong Cột 1
                document.getElementById('passageRange').textContent = 'PART 5 · Hoàn thành câu';
                document.getElementById('passageTitle').style.display = 'none';
                document.getElementById('passageContent').innerHTML = `
                    <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 14px; padding: 20px; font-size: 16px; font-weight: 700; color: #1E293B; line-height: 1.7;">
                        <div style="font-size:12px;font-weight:600;color:#64748B;margin-bottom:10px;letter-spacing:0.8px;">CÂU HỎI ${q.question_number}</div>
                        ${escapeHtml(q.stem)}
                    </div>
                `;
            }

            // Cập nhật Cột 2: Giải thích (BẢO MẬT: nếu chưa nộp bài sẽ hiển thị thông báo bảo mật)
            const tabExp = document.getElementById('tabExpCurrent');
            if (tabExp) {
                tabExp.textContent = q.question_number;
            }

            if (q.correct) {
                document.getElementById('explainBody').innerHTML = `
                    <strong>${q.question_number}.</strong><br><br>
                    <strong>=&gt; Đáp án đúng: ${escapeHtml(q.correct)}</strong><br><br>
                    Phân tích: ${escapeHtml(q.explanation || 'Chưa có phân tích.')}
                `;
            } else {
                document.getElementById('explainBody').innerHTML = `
                    <div style="color: #64748B; font-size: 14px; line-height: 1.6; padding: 6px 0;">
                        🔒 <strong>Đáp án và phân tích chi tiết được bảo mật trong lúc làm bài.</strong><br><br>
                        Sau khi bạn bấm <strong>"Nộp bài"</strong>, hệ thống sẽ mở khóa đáp án đúng, chấm điểm và hiển thị toàn bộ lời giải chi tiết cho từng câu.
                    </div>
                `;
            }

            // Cập nhật Cột 3: Stem + Lựa chọn đáp án
            // Nếu là Part 6/7 (có passage), hiển thị stem câu hỏi trước các đáp án
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
            if (!q.passage) {
                // Part 5: chỉ hiển thị số câu hiển thị (question_number từ 1 đến N)
                questionsHtml += `<div class="sp-vp-qnum" style="margin-bottom:16px;">Câu ${q.question_number}</div>`;
            }

            const opts = q.options || {};
            // Sắp xếp thứ tự hiển thị A -> B -> C -> D, không dựa vào thứ tự lưu của CSDL
            const labels = (q.part_number === 5)
                ? ['A', 'B', 'C', 'D'].filter(lbl => opts[lbl] !== undefined)
                : Object.keys(opts).sort();

            for (const letter of labels) {
                const text = opts[letter];
                const isSelected = userAnswers[q.id] === letter;
                questionsHtml += `
                    <div class="sp-vp-opt-row ${isSelected ? 'is-selected' : ''}" onclick="selectAnswer(${q.id}, '${letter}')">
                        <div class="sp-vp-opt-radio">${letter}</div>
                        <div class="sp-vp-opt-text">${escapeHtml(text)}</div>
                    </div>
                `;
            }
            questionsHtml += `</div>`;

            document.getElementById('questionsContainer').innerHTML = stemHtml + questionsHtml;

            // Cập nhật tiến độ thanh ngang
            updateProgress();

            // Cập nhật nút Flag
            const flagBtn = document.getElementById('flagBtn');
            if (flagBtn) {
                flagBtn.style.color = flaggedQuestions[q.id] ? '#F59E0B' : '#64748B';
            }

            // Cập nhật trạng thái nút Trước / Sau
            document.getElementById('prevBtn').disabled = (currentIndex === 0);
            document.getElementById('nextBtn').disabled = (currentIndex === questionsData.length - 1);
        }

        function selectAnswer(qid, letter) {
            userAnswers[qid] = letter;

            // Tạo input hidden vào form để submit
            let hiddenInput = document.getElementById('answer_input_' + qid);
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'answer_input_' + qid;
                hiddenInput.name = `answers[${qid}]`;
                document.getElementById('practiceExamForm').appendChild(hiddenInput);
            }
            hiddenInput.value = letter;

            renderCurrentQuestion();

            // Nếu Question Palette đang mở, đồng bộ giao diện
            if (document.getElementById('questionDrawerModal')?.classList.contains('is-open')) {
                renderDrawerQuestions();
            }
        }

        function prevQuestion() {
            if (currentIndex > 0) {
                currentIndex--;
                renderCurrentQuestion();
            }
        }

        function nextQuestion() {
            if (currentIndex < questionsData.length - 1) {
                currentIndex++;
                renderCurrentQuestion();
            }
        }

        function toggleExplanation() {
            isExplanationOpen = !isExplanationOpen;
            const explainCol = document.getElementById('explainCol');
            const mainLayout = document.getElementById('mainLayout');
            const toggleBtn = document.getElementById('explainToggleBtn');

            if (isExplanationOpen) {
                explainCol.style.display = 'flex';
                mainLayout.classList.add('with-explanation');
                toggleBtn.classList.add('is-active');
            } else {
                explainCol.style.display = 'none';
                mainLayout.classList.remove('with-explanation');
                toggleBtn.classList.remove('is-active');
            }
        }

        function switchExplainTab(tab) {
            if (!questionsData || questionsData.length === 0) return;
            const q = questionsData[currentIndex];
            if (tab === 'trans') {
                document.getElementById('explainBody').innerHTML = `
                    <strong>Dịch nghĩa tiếng Việt:</strong><br><br>
                    ${q.translation ? escapeHtml(q.translation) : '<div style="color:#64748B;font-size:13.5px;line-height:1.6;"><em>Bản dịch chi tiết sẽ được mở khóa sau khi bạn bấm nộp bài.</em></div>'}
                `;
            } else {
                if (q.correct) {
                    document.getElementById('explainBody').innerHTML = `
                        <strong>${q.question_number}.</strong><br><br>
                        <strong>=&gt; Đáp án đúng: ${escapeHtml(q.correct)}</strong><br><br>
                        Phân tích: ${escapeHtml(q.explanation || 'Chưa có phân tích.')}
                    `;
                } else {
                    document.getElementById('explainBody').innerHTML = `
                        <div style="color: #64748B; font-size: 14px; line-height: 1.6; padding: 6px 0;">
                            🔒 <strong>Đáp án và phân tích chi tiết được bảo mật trong lúc làm bài.</strong><br><br>
                            Sau khi bạn bấm <strong>"Nộp bài"</strong>, hệ thống sẽ mở khóa đáp án đúng, chấm điểm và hiển thị toàn bộ lời giải chi tiết cho từng câu.
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
            const q = questionsData[currentIndex];
            if (!q) return;
            flaggedQuestions[q.id] = !flaggedQuestions[q.id];
            renderCurrentQuestion();

            // Nếu Drawer đang mở, cập nhật lại biểu tượng cờ
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
                const isAnswered = !!userAnswers[item.id];
                const isFlagged = !!flaggedQuestions[item.id];
                const isCurrent = idx === currentIndex;

                const bg = isAnswered ? '#5B4BF5' : '#FFFFFF';
                const textColor = isAnswered ? '#FFFFFF' : '#1E293B';
                const borderColor = isCurrent ? '#F59E0B' : (isAnswered ? '#5B4BF5' : '#CBD5E1');
                const borderWidth = isCurrent ? '2.5px' : '1.5px';

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
            if (idx >= 0 && idx < questionsData.length) {
                currentIndex = idx;
                renderCurrentQuestion();
                const modal = document.getElementById('questionDrawerModal');
                if (modal) modal.classList.remove('is-open');
            }
        }

        // Xử lý nộp bài: Khớp logic kiểm tra Ảnh mẫu 1 & Ảnh mẫu 2
        function handlePreSubmit() {
            const total = questionsData ? questionsData.length : 0;
            if (total === 0) {
                alert('Không có câu hỏi nào trong bài để nộp.');
                return;
            }
            const answeredCount = Object.keys(userAnswers).length;

            if (answeredCount === 0) {
                // Ảnh mẫu 2: Chưa làm câu nào
                document.getElementById('emptySubmitModal').classList.add('is-open');
            } else if (answeredCount < total) {
                // Ảnh mẫu 1: Còn câu chưa làm
                document.getElementById('incompleteSubmitModal').classList.add('is-open');
            } else {
                // Đã làm hết
                submitExamDirectly();
            }
        }

        function closeSubmitModals() {
            document.getElementById('emptySubmitModal').classList.remove('is-open');
            document.getElementById('incompleteSubmitModal').classList.remove('is-open');
            const drawer = document.getElementById('questionDrawerModal');
            if (drawer) drawer.classList.remove('is-open');
        }

        function submitExamDirectly() {
            clearInterval(examTimer);
            document.getElementById('practiceExamForm').submit();
        }

        function confirmExit(event) {
            if (Object.keys(userAnswers).length > 0) {
                if (!confirm('Bạn có chắc muốn thoát bài luyện tập? Kết quả đang làm sẽ không được lưu.')) {
                    event.preventDefault();
                    return false;
                }
            }
            return true;
        }

        // Khởi tạo hiển thị câu đầu tiên
        renderCurrentQuestion();
    </script>
</body>
</html>
