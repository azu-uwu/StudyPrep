<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isReview ? 'StudyPrep - Xem đáp án bài nghe' : 'StudyPrep - Luyện kỹ năng Nghe' }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:400,600,700,800,900|instrument-sans:400,500,600,700">

    {{-- Styles --}}
    <link rel="stylesheet" href="{{ asset('css/studyprep-core.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/nghe-lam-bai.css') }}">
</head>
<body class="sp-listening-exam-page {{ $isReview ? 'is-review-mode' : '' }}">

    {{-- 1. HEADER THANH TRÊN CÙNG (KHỚP ẢNH MẪU) --}}
    <header class="sp-lexam-header">
        <div class="sp-lexam-header-left">
            <a href="{{ route('luyen-tap.nghe') }}" class="sp-lexam-logo-link" title="StudyPrep">
                <img src="{{ asset('images/logo.svg') }}" alt="StudyPrep" class="sp-lexam-logo-img">
            </a>
            <a href="{{ route('luyen-tap.nghe.mo-ta-hinh') }}" class="sp-lexam-close-btn" title="Thoát bài luyện tập" onclick="return confirmExit(event)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </a>
        </div>

        {{-- Thanh tiến độ ngang --}}
        <div class="sp-lexam-progress-bar-wrap" title="Tiến độ bài làm">
            <div class="sp-lexam-progress-bar-fill" id="progressBarFill" style="width: 20%;"></div>
        </div>

        <div class="sp-lexam-header-right">
            <button type="button" class="sp-lexam-gear-btn" title="Cài đặt">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
            </button>
        </div>
    </header>

    {{-- 2. MAIN WRAPPER --}}
    <main class="sp-lexam-main-wrap">

        {{-- Thanh điều hướng câu: [←Câu trước] [💡Giải thích] [Câu sau→] --}}
        <div class="sp-lexam-nav-row">
            <button type="button" class="sp-lexam-nav-btn" id="prevBtn" onclick="prevQuestion()" disabled>
                &larr;Câu trước
            </button>

            <button type="button" class="sp-lexam-explain-toggle-btn {{ $isReview ? 'is-active' : '' }}" id="explainToggleBtn" onclick="toggleTranscriptDrawer()">
                <span style="font-size: 15px;">💡</span>
                <span>Giải thích</span>
            </button>

            <button type="button" class="sp-lexam-nav-btn" id="nextBtn" onclick="nextQuestion()">
                Câu sau&rarr;
            </button>
        </div>

        {{-- Khung 3 cột (hoặc 2 cột): Media/Audio | Transcript Drawer | Chọn đáp án --}}
        <div class="sp-lexam-grid">

            {{-- CỘT TRÁI: Câu hỏi + Audio player + Ảnh Part 1 --}}
            <div class="sp-lexam-media-card">
                <h2 class="sp-lexam-qnum-title" id="questionTitle">Câu 6</h2>

                {{-- Trình phát Audio --}}
                <div class="sp-lexam-audio-player">
                    {{-- Tua lùi 5s --}}
                    <button type="button" class="sp-audio-ctrl-btn" title="Tua lùi 5 giây" onclick="rewindAudio(5)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="11 19 2 12 11 5"></polyline>
                            <polyline points="22 19 13 12 22 5"></polyline>
                        </svg>
                    </button>

                    {{-- Play / Pause --}}
                    <button type="button" class="sp-audio-play-btn" id="audioPlayBtn" title="Phát audio" onclick="togglePlayAudio()">
                        <svg id="playIcon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        <svg id="pauseIcon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                            <rect x="6" y="4" width="4" height="16"></rect>
                            <rect x="14" y="4" width="4" height="16"></rect>
                        </svg>
                    </button>

                    {{-- Tua tới 5s --}}
                    <button type="button" class="sp-audio-ctrl-btn" title="Tua tới 5 giây" onclick="forwardAudio(5)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="13 19 22 12 13 5"></polyline>
                            <polyline points="2 19 11 12 2 5"></polyline>
                        </svg>
                    </button>

                    {{-- Thời gian hiện tại --}}
                    <span class="sp-audio-time-txt" id="audioCurrentTime">00:14</span>

                    {{-- Slider tua nhạc --}}
                    <div class="sp-audio-slider-wrap">
                        <input type="range" class="sp-audio-slider" id="audioSlider" min="0" max="29" value="14" oninput="onAudioSliderChange(this.value)">
                    </div>

                    {{-- Tổng thời lượng --}}
                    <span class="sp-audio-time-txt" id="audioTotalDuration">00:29</span>
                </div>

                {{-- Khung hiển thị hình ảnh (Part 1) --}}
                <div class="sp-lexam-photo-wrap" id="photoWrap">
                    <img src="{{ asset('images/listening/part1_supermarket.jpg') }}" alt="Bức tranh Part 1" class="sp-lexam-photo-img" id="questionPhoto">
                </div>
            </div>

            {{-- CỘT GIỮA: Transcript & Lời dịch (Màu xanh dương nổi bật, bật mở khi bấm Giải thích hoặc chế độ xem đáp án) --}}
            <div class="sp-lexam-transcript-card {{ $isReview ? 'is-visible' : '' }}" id="transcriptCard">
                <div class="sp-transcript-header">
                    <div class="sp-transcript-tabs">
                        <button type="button" class="sp-transcript-tab-btn is-active" id="tabTranscriptBtn" onclick="switchTranscriptTab('transcript')">
                            Transcript
                        </button>
                        <button type="button" class="sp-transcript-tab-btn" id="tabTransBtn" onclick="switchTranscriptTab('trans')">
                            Dịch
                        </button>
                    </div>

                    <div class="sp-transcript-header-right">
                        <span class="sp-transcript-qnum-badge" id="transcriptBadgeNum">6</span>
                        <button type="button" class="sp-transcript-close-btn" title="Đóng bảng giải thích" onclick="toggleTranscriptDrawer()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="sp-transcript-content" id="transcriptBody">
                    <div class="sp-transcript-line">(A) She is at the supermarket.</div>
                    <div class="sp-transcript-line">(B) She is selecting a flowerpot.</div>
                    <div class="sp-transcript-line">(C) She is with her best friend.</div>
                    <div class="sp-transcript-line">(D) She is paying for a flowerpot.</div>
                </div>
            </div>

            {{-- CỘT PHẢI: Khung Chọn đáp án (A, B, C, D) --}}
            <div class="sp-lexam-answer-card">
                <h3 class="sp-answer-card-title">Chọn đáp án</h3>

                <div class="sp-answer-options-list" id="answerOptionsList">
                    {{-- Render động bằng JS --}}
                </div>

                {{-- Khung giải thích đèn vàng khi ở chế độ Review --}}
                <div class="sp-review-explanation-box" id="reviewExplanationBox" style="{{ $isReview ? 'display: block;' : 'display: none;' }}">
                    <span style="font-size: 15px; margin-right: 4px;">💡</span>
                    <span id="explanationText">
                        <strong>(B) là đáp án đúng</strong> vì trong hình người phụ nữ đang cầm và lựa chọn một chậu hoa nhỏ trong giỏ/quầy cây.
                    </span>
                </div>
            </div>

        </div>

        {{-- Thanh hoàn thành bài làm ở dưới cùng (nếu ở câu cuối) --}}
        @if(!$isReview)
        <div class="sp-lexam-submit-bar" id="submitExamBar" style="display: none;">
            <button type="button" class="sp-lexam-finish-btn" onclick="submitExam()">
                <span>Nộp bài & Xem đáp án</span>
                <span aria-hidden="true">&rarr;</span>
            </button>
        </div>
        @else
        <div class="sp-lexam-submit-bar">
            <a href="{{ route('luyen-tap.nghe.lam-bai') }}" class="sp-lexam-finish-btn" style="background: #5B4BF6;">
                <span>Làm lại bài này</span>
                <span aria-hidden="true">&circlearrowright;</span>
            </a>
        </div>
        @endif

        {{-- Form ẩn để submit --}}
        <form id="listeningExamForm" action="{{ route('luyen-tap.nghe.nop-bai') }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="total_count" value="{{ count($questions) }}">
        </form>

    </main>

    {{-- Script điều khiển --}}
    <script>
        const questionsData = {!! json_encode($questions, JSON_UNESCAPED_UNICODE) !!};
        const isReviewMode = @json($isReview);
        const initialUserAnswers = {!! json_encode($userAnswers, JSON_UNESCAPED_UNICODE) !!};

        let currentIndex = 0;
        let userAnswers = Object.assign({}, initialUserAnswers);
        let currentTranscriptTab = 'transcript';
        let isDrawerOpen = isReviewMode;

        // Audio state
        let audioTimer = null;
        let isPlaying = false;
        let currentSecond = 14;
        let totalSeconds = 29;

        function renderCurrentQuestion() {
            if (!questionsData || questionsData.length === 0) return;
            const q = questionsData[currentIndex];

            // 1. Cập nhật tiến độ
            const pct = Math.round(((currentIndex + 1) / questionsData.length) * 100);
            document.getElementById('progressBarFill').style.width = pct + '%';

            // 2. Cột trái: Tiêu đề & Ảnh
            document.getElementById('questionTitle').textContent = 'Câu ' + q.question_number;
            const photoWrap = document.getElementById('photoWrap');
            const photoImg = document.getElementById('questionPhoto');
            if (q.image) {
                photoWrap.style.display = 'block';
                photoImg.src = q.image;
            } else {
                photoWrap.style.display = 'none';
            }

            // 3. Audio Player reset
            totalSeconds = q.audio_duration_seconds || 29;
            currentSecond = Math.min(14, totalSeconds);
            document.getElementById('audioTotalDuration').textContent = q.audio_duration || '00:29';
            document.getElementById('audioSlider').max = totalSeconds;
            updateAudioDisplay();

            // 4. Cột giữa: Transcript & Lời dịch
            document.getElementById('transcriptBadgeNum').textContent = q.question_number;
            renderTranscriptContent();

            // 5. Cột phải: Các lựa chọn đáp án
            renderAnswerOptions(q);

            // 6. Lời giải vàng
            const explBox = document.getElementById('reviewExplanationBox');
            if (isReviewMode && q.explanation) {
                explBox.style.display = 'block';
                document.getElementById('explanationText').innerHTML = q.explanation;
            } else if (!isReviewMode) {
                explBox.style.display = 'none';
            }

            // 7. Cập nhật nút Trước / Sau
            document.getElementById('prevBtn').disabled = (currentIndex === 0);
            const nextBtn = document.getElementById('nextBtn');
            const submitBar = document.getElementById('submitExamBar');

            if (currentIndex === questionsData.length - 1) {
                if (!isReviewMode) {
                    nextBtn.innerHTML = `<span>Xem đáp án</span> &rarr;`;
                    if (submitBar) submitBar.style.display = 'flex';
                } else {
                    nextBtn.disabled = true;
                }
            } else {
                nextBtn.disabled = false;
                nextBtn.innerHTML = `<span>Câu sau</span> &rarr;`;
                if (submitBar && !isReviewMode) submitBar.style.display = 'none';
            }
        }

        // Render các phương án đáp án (Khớp cả mode làm bài & mode xem đáp án)
        function renderAnswerOptions(q) {
            const container = document.getElementById('answerOptionsList');
            let html = '';
            const opts = q.options;

            const userPick = userAnswers[q.id] || null;
            const isCorrect = userPick === q.correct;

            for (const [letter, text] of Object.entries(opts)) {
                let circleClass = '';
                let extraLabel = '';

                if (isReviewMode) {
                    // Xem đáp án
                    if (letter === q.correct) {
                        circleClass = 'is-review-correct';
                        extraLabel = `<span class="sp-answer-correct-label">Đáp án chính xác</span>`;
                    } else if (letter === userPick && !isCorrect) {
                        circleClass = 'is-review-wrong';
                    }
                } else {
                    // Làm bài
                    if (userPick === letter) {
                        circleClass = 'is-user-selected';
                    }
                }

                const clickAction = !isReviewMode ? `onclick="selectOption(${q.id}, '${letter}')"` : '';

                html += `
                    <div class="sp-answer-option-row" ${clickAction}>
                        <div class="sp-answer-circle ${circleClass}">${letter}</div>
                        ${extraLabel}
                    </div>
                `;
            }

            container.innerHTML = html;
        }

        function selectOption(qid, letter) {
            if (isReviewMode) return;
            userAnswers[qid] = letter;

            // Cập nhật input hidden
            let input = document.getElementById('answer_input_' + qid);
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.id = 'answer_input_' + qid;
                input.name = `answers[${qid}]`;
                document.getElementById('listeningExamForm').appendChild(input);
            }
            input.value = letter;

            const q = questionsData[currentIndex];
            renderAnswerOptions(q);
        }

        // Toggle mở / đóng Transcript Drawer
        function toggleTranscriptDrawer() {
            isDrawerOpen = !isDrawerOpen;
            const drawer = document.getElementById('transcriptCard');
            const toggleBtn = document.getElementById('explainToggleBtn');

            if (isDrawerOpen) {
                drawer.classList.add('is-visible');
                toggleBtn.classList.add('is-active');
            } else {
                drawer.classList.remove('is-visible');
                toggleBtn.classList.remove('is-active');
            }
        }

        function switchTranscriptTab(tab) {
            currentTranscriptTab = tab;
            document.getElementById('tabTranscriptBtn').classList.toggle('is-active', tab === 'transcript');
            document.getElementById('tabTransBtn').classList.toggle('is-active', tab === 'trans');
            renderTranscriptContent();
        }

        function renderTranscriptContent() {
            const q = questionsData[currentIndex];
            const body = document.getElementById('transcriptBody');
            const lines = (currentTranscriptTab === 'transcript') ? (q.transcript || []) : (q.translation || []);

            let html = '';
            lines.forEach(function(line) {
                html += `<div class="sp-transcript-line">${line}</div>`;
            });
            body.innerHTML = html;
        }

        // Điều hướng câu
        function prevQuestion() {
            if (currentIndex > 0) {
                pauseAudio();
                currentIndex--;
                renderCurrentQuestion();
            }
        }

        function nextQuestion() {
            if (currentIndex < questionsData.length - 1) {
                pauseAudio();
                currentIndex++;
                renderCurrentQuestion();
            } else if (!isReviewMode) {
                submitExam();
            }
        }

        function submitExam() {
            pauseAudio();
            document.getElementById('listeningExamForm').submit();
        }

        function confirmExit(event) {
            if (!isReviewMode && Object.keys(userAnswers).length > 0) {
                if (!confirm('Bạn có chắc muốn thoát bài luyện nghe? Kết quả đang làm sẽ không được lưu.')) {
                    event.preventDefault();
                    return false;
                }
            }
            return true;
        }

        // =====================================================================
        // AUDIO CONTROLLER SIMULATOR (Phát âm thanh & tua mượt mà)
        // =====================================================================
        function togglePlayAudio() {
            if (isPlaying) {
                pauseAudio();
            } else {
                playAudio();
            }
        }

        function playAudio() {
            isPlaying = true;
            document.getElementById('playIcon').style.display = 'none';
            document.getElementById('pauseIcon').style.display = 'block';

            // Đọc thử bằng Web Speech nếu trình duyệt hỗ trợ
            const q = questionsData[currentIndex];
            if ('speechSynthesis' in window && q.audio_text) {
                window.speechSynthesis.cancel();
                const utter = new SpeechSynthesisUtterance(q.audio_text);
                utter.lang = 'en-US';
                utter.rate = 0.9;
                window.speechSynthesis.speak(utter);
            }

            clearInterval(audioTimer);
            audioTimer = setInterval(function() {
                if (currentSecond < totalSeconds) {
                    currentSecond++;
                    updateAudioDisplay();
                } else {
                    pauseAudio();
                }
            }, 1000);
        }

        function pauseAudio() {
            isPlaying = false;
            clearInterval(audioTimer);
            document.getElementById('playIcon').style.display = 'block';
            document.getElementById('pauseIcon').style.display = 'none';
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
        }

        function rewindAudio(sec) {
            currentSecond = Math.max(0, currentSecond - sec);
            updateAudioDisplay();
        }

        function forwardAudio(sec) {
            currentSecond = Math.min(totalSeconds, currentSecond + sec);
            updateAudioDisplay();
        }

        function onAudioSliderChange(val) {
            currentSecond = parseInt(val, 10);
            updateAudioDisplay();
        }

        function updateAudioDisplay() {
            const min = Math.floor(currentSecond / 60);
            const sec = currentSecond % 60;
            const formatted = (min < 10 ? '0' : '') + min + ':' + (sec < 10 ? '0' : '') + sec;

            document.getElementById('audioCurrentTime').textContent = formatted;
            document.getElementById('audioSlider').value = currentSecond;
        }

        // Khởi tạo câu đầu tiên
        renderCurrentQuestion();
    </script>
</body>
</html>
