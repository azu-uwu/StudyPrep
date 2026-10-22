<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudyPrep - Luyện tập Part 5 & 6 (Hoàn thành câu)</title>
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
            <a href="{{ url('/luyen-tap/doc') }}" class="sp-vp-logo" title="StudyPrep Reading">
                voca<span>PREP</span>
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

    {{-- Dữ liệu và logic bài thi --}}
    <script>
        const questionsData = @json($questions);
        let currentIndex = 0;
        let userAnswers = {};
        let flaggedQuestions = {};
        let timerSeconds = 0;
        let isExplanationOpen = true;

        // Bắt đầu đếm thời gian làm bài
        const examTimer = setInterval(function() {
            timerSeconds++;
            document.getElementById('timeSpentInput').value = timerSeconds;
        }, 1000);

        function renderCurrentQuestion() {
            if (!questionsData || questionsData.length === 0) return;
            const q = questionsData[currentIndex];

            // Cập nhật Cột 1: Passage / Context
            if (q.passage) {
                document.getElementById('passageRange').textContent = q.question_range || ('Part ' + q.part);
                document.getElementById('passageTitle').style.display = q.passage_title ? 'block' : 'none';
                document.getElementById('passageTitle').textContent = q.passage_title || '';
                document.getElementById('passageContent').innerHTML = q.passage;
            } else if (q.part === 'Part 5') {
                document.getElementById('passageRange').textContent = 'Part 5 • Hoàn thành câu';
                document.getElementById('passageTitle').style.display = 'block';
                document.getElementById('passageTitle').textContent = 'Câu hỏi ngữ pháp & từ vựng';
                document.getElementById('passageContent').innerHTML = `
                    <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 14px; padding: 18px; font-size: 15px; font-weight: 700; color: #1E293B; line-height: 1.6;">
                        ${q.stem}
                    </div>
                `;
            }

            // Cập nhật Cột 2: Giải thích
            document.getElementById('tabExpCurrent').textContent = q.question_number;
            document.getElementById('explainBody').innerHTML = `
                <strong>${q.question_number}.</strong><br><br>
                <strong>=&gt; Đáp án đúng: ${q.correct}</strong><br><br>
                Phân tích: ${q.explanation}
            `;

            // Cập nhật Cột 3: Lựa chọn đáp án
            let questionsHtml = `
                <div class="sp-vp-question-block">
                    <div class="sp-vp-qnum">${q.question_number}.</div>
            `;

            const opts = q.options;
            for (const [letter, text] of Object.entries(opts)) {
                const isSelected = userAnswers[q.id] === letter;
                questionsHtml += `
                    <div class="sp-vp-opt-row ${isSelected ? 'is-selected' : ''}" onclick="selectAnswer(${q.id}, '${letter}')">
                        <div class="sp-vp-opt-radio">${letter}</div>
                        <div class="sp-vp-opt-text">${text}</div>
                    </div>
                `;
            }

            questionsHtml += `</div>`;
            document.getElementById('questionsContainer').innerHTML = questionsHtml;

            // Cập nhật tiến độ thanh ngang
            updateProgress();

            // Cập nhật nút Flag
            const flagBtn = document.getElementById('flagBtn');
            if (flaggedQuestions[q.id]) {
                flagBtn.style.color = '#F59E0B';
            } else {
                flagBtn.style.color = '#64748B';
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
            const q = questionsData[currentIndex];
            if (tab === 'trans') {
                document.getElementById('explainBody').innerHTML = `
                    <strong>Dịch nghĩa tiếng Việt:</strong><br><br>
                    ${q.translation || 'Dịch: ' + q.stem}
                `;
            } else {
                document.getElementById('explainBody').innerHTML = `
                    <strong>${q.question_number}.</strong><br><br>
                    <strong>=&gt; Đáp án đúng: ${q.correct}</strong><br><br>
                    Phân tích: ${q.explanation}
                `;
            }
        }

        function updateProgress() {
            const answeredCount = Object.keys(userAnswers).length;
            const total = questionsData.length;
            const pct = Math.round((answeredCount / total) * 100);
            document.getElementById('progressBarFill').style.width = pct + '%';
        }

        function toggleFlagCurrentQuestion() {
            const q = questionsData[currentIndex];
            flaggedQuestions[q.id] = !flaggedQuestions[q.id];
            renderCurrentQuestion();
        }

        // Xử lý nộp bài: Khớp logic kiểm tra Ảnh mẫu 1 & Ảnh mẫu 2
        function handlePreSubmit() {
            const answeredCount = Object.keys(userAnswers).length;
            const total = questionsData.length;

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
