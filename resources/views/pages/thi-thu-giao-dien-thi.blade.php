<x-layouts.app active="ho-so" title="StudyPrep - Hồ sơ học viên">
@push('styles')
<style>
    :root{--purple:#5b50ff;--purple-dark:#493de0;--ink:#171a35;--muted:#77819a;--line:#e3e7f0;--bg:#f5f6f8;--blue:#387bff;--yellow:#e8b000;--red:#e92222}
    *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif;font-size:12px}button,input,textarea,select{font:inherit}button{cursor:pointer}
    .topbar{height:38px;display:flex;align-items:center;gap:12px;padding:0 16px;background:#fff;border-bottom:1px solid #e8ebf2;position:sticky;top:0;z-index:5}
    .logo{font-size:15px;font-weight:900;letter-spacing:-.7px;color:#273b88;white-space:nowrap}.logo .study{color:#5464e9}.logo .prep{color:#f4a323}
    .close{border:0;background:transparent;color:#8d96ad;font-size:17px;padding:2px 4px}.top-sep{height:20px;width:1px;background:#e6e9f1}.question-count{white-space:nowrap;font-size:10px;color:#202847}.timer{background:#e92323;color:#fff;border-radius:6px;padding:5px 8px;font-size:10px;font-weight:750;white-space:nowrap}
    .header-center{position:absolute;left:50%;transform:translateX(-50%);display:flex;align-items:center;gap:5px}.pill{height:23px;padding:0 9px;border:1px solid #e0e4ed;border-radius:15px;background:#fff;color:#58617a;font-size:9px;white-space:nowrap}.pill:hover,.pill.active{background:#f0efff;color:#5147ff;border-color:#d9d5ff}.right-tools{margin-left:auto;display:flex;align-items:center;gap:12px}.tool{border:0;background:transparent;color:#8a93a9;font-size:16px;padding:2px}.tool.blue{color:#3c83ff}.tool.yellow{color:#e5b000}
    .workspace{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:11px 14px 20px;min-height:calc(100vh - 38px)}
    .panel{background:#fff;border:1px solid #e7eaf1;border-radius:11px;min-width:0;min-height:calc(100vh - 54px);padding:18px 14px}
    .panel-title{margin:0 0 11px;font-size:11px;font-weight:750}
    .prompt-box{padding:12px;border:1px solid #e4e8f0;border-radius:9px;font-size:11px;line-height:1.7;color:#171a35}
    .prompt-box p{margin:0 0 8px}.prompt-box p:last-child{margin-bottom:0}
    .left-choices{display:grid;gap:11px;margin-top:15px;font-size:10px;line-height:1.5}
    .left-choices div{cursor:pointer}.left-choices div:hover{color:var(--purple)}
    .right-panel{display:flex;flex-direction:column}
    .right-content{flex:1;min-height:0}
    .answer-heading{font-size:11px;font-weight:750;margin:0 0 13px}
    .answer-options{display:grid;gap:9px;justify-items:start}
    .answer-option{width:25px;height:25px;border:1px solid #dbe1ec;border-radius:50%;display:grid;place-items:center;background:white;color:#4c5670;font-size:11px}
    .answer-option:hover{border-color:#8c85ff;color:var(--purple)}.answer-option.selected{border-color:var(--purple);background:#efedff;color:var(--purple);box-shadow:0 0 0 2px #5b50ff10}
    .footer-actions{display:flex;justify-content:space-between;gap:10px;padding-top:15px}
    .primary{border:0;background:var(--purple);color:#fff;border-radius:8px;padding:9px 15px;font-size:10px;font-weight:750;box-shadow:0 2px 0 var(--purple-dark)}
    .secondary{border:1px solid #e0e5ef;background:white;color:#59637b;border-radius:8px;padding:8px 12px;font-size:10px}
    .hidden{display:none!important}
    .question-list{display:grid;gap:9px}.question-item{padding:10px;border:1px solid #e5e9f1;border-radius:8px}.question-item p{margin:0 0 9px;font-size:10px;line-height:1.6}.question-item .mini-answers{display:flex;gap:7px;flex-wrap:wrap}.mini-answer{border:1px solid #dce2ee;border-radius:14px;padding:5px 9px;background:white;color:#566079;font-size:9px}.mini-answer.selected{border-color:var(--purple);background:#f0efff;color:var(--purple)}
    .record-box{border:1px solid #e5e9f2;border-radius:10px;padding:15px;display:grid;gap:13px}.record-status{display:flex;align-items:center;gap:8px;font-size:11px}.record-dot{width:8px;height:8px;border-radius:50%;background:#a0a8ba}.record-dot.recording{background:#ed3535;box-shadow:0 0 0 4px #ffe5e5}.record-time{font-size:27px;font-weight:750;letter-spacing:1px;text-align:center;font-variant-numeric:tabular-nums}.record-help{color:#7b849b;font-size:10px;line-height:1.6;text-align:center}.record-controls{display:flex;justify-content:center;gap:8px;flex-wrap:wrap}.record-controls button{min-width:85px}
    .writing-area{width:100%;min-height:250px;resize:vertical;border:1px solid #e3e8f1;border-radius:9px;padding:12px;outline:none;font-size:11px;line-height:1.7}.writing-area:focus{border-color:#9a93ff;box-shadow:0 0 0 3px #5b50ff12}.word-count{display:flex;justify-content:space-between;gap:10px;margin-top:8px;color:#8a93a9;font-size:10px}
    .modal-backdrop{position:fixed;inset:0;z-index:20;display:grid;place-items:center;padding:18px;background:#151b3566}.modal-backdrop[hidden]{display:none}.modal{width:min(100%,390px);padding:22px;background:#fff;border-radius:14px;box-shadow:0 18px 55px #151b3533}.modal h2{margin:0 0 9px;font-size:16px}.modal p{margin:0;color:#707a92;font-size:12px;line-height:1.6}.modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:18px}
    @media(max-width:700px){.topbar{gap:7px;padding:0 8px}.logo{font-size:13px}.header-center{position:static;transform:none;margin-left:auto}.right-tools{gap:5px;margin-left:5px}.workspace{grid-template-columns:1fr;padding:9px;gap:9px}.panel{min-height:auto;padding:15px}.left-panel{min-height:220px}.right-panel{min-height:330px}.header-center .pill{padding:0 6px;font-size:8px}.timer{font-size:9px;padding:5px}.question-count{font-size:9px}}
    @media(max-width:430px){.header-center .pill:nth-child(2){display:none}.topbar{gap:5px}.logo{font-size:12px}.tool{font-size:14px}.right-tools{gap:3px}}
</style>
@endpush
<header class="topbar">
  <button class="close" id="exitBtn" title="Thoát" aria-label="Thoát">×</button>
  <span class="top-sep"></span>
  <span class="question-count" id="questionCount">Q 101/164</span>
  <span class="timer" id="timer">◷ 01:58:53</span>
</header>

<!-- đề reading -->
<main class="workspace">
    <!--cụm đề -->
  <section class="panel left-panel">
    <h1 class="panel-title" id="leftTitle">Câu 101</h1>
    <div id="leftQuestion">
      <div class="prompt-box"><p>By measuring the ______ of the new adhesive under extreme heat, the researchers confirmed that it performs reliably in demanding environments.</p></div>
      <div class="left-choices" id="leftChoices">
        <div data-answer="A">A. endures</div><div data-answer="B">B. endure</div><div data-answer="C">C. enduring</div><div data-answer="D">D. endurance</div>
      </div>
    </div>
    <div id="structureContent" class="hidden">
      <div class="prompt-box"><strong>Cấu trúc bài thi</strong><p>Part 5 — Incomplete Sentences</p><p>Câu 101–130 · Chọn một đáp án đúng cho mỗi câu.</p><p>Thời gian còn lại được hiển thị trên thanh công cụ phía trên.</p></div>
    </div>
    <div id="explanationContent" class="hidden">
      <div class="prompt-box"><strong>Giải thích đáp án</strong><p>“By measuring the ______” cần một danh từ làm tân ngữ của động từ “measuring”.</p><p><strong>D. endurance</strong> là danh từ, nghĩa là sức bền/độ bền.</p></div>
    </div>
  </section>

  <!-- ghi nhận lựa chọn của học viên -->
  <section class="panel right-panel">
    <!-- 1 câu hỏi -->
    <div class="right-content" id="singleMode">
      <h2 class="answer-heading">Chọn đáp án</h2>
      <div class="answer-options" id="answerOptions">
        <button class="answer-option" data-answer="A">A</button><button class="answer-option" data-answer="B">B</button><button class="answer-option" data-answer="C">C</button><button class="answer-option" data-answer="D">D</button>
      </div>
    </div>

  </section>
</main>

<main class="workspace">
    <!--cụm đề -->
  <section class="panel left-panel">
    <h1 class="panel-title" id="leftTitle">Câu 101</h1>
    <div id="leftQuestion">
      <div class="prompt-box"><p>By measuring the ______ of the new adhesive under extreme heat, the researchers confirmed that it performs reliably in demanding environments.</p></div>
      <div class="left-choices" id="leftChoices">
        <div data-answer="A">A. endures</div><div data-answer="B">B. endure</div><div data-answer="C">C. enduring</div><div data-answer="D">D. endurance</div>
      </div>
    </div>
    <div id="structureContent" class="hidden">
      <div class="prompt-box"><strong>Cấu trúc bài thi</strong><p>Part 5 — Incomplete Sentences</p><p>Câu 101–130 · Chọn một đáp án đúng cho mỗi câu.</p><p>Thời gian còn lại được hiển thị trên thanh công cụ phía trên.</p></div>
    </div>
    <div id="explanationContent" class="hidden">
      <div class="prompt-box"><strong>Giải thích đáp án</strong><p>“By measuring the ______” cần một danh từ làm tân ngữ của động từ “measuring”.</p><p><strong>D. endurance</strong> là danh từ, nghĩa là sức bền/độ bền.</p></div>
    </div>

  </section>

  <!-- ghi nhận lựa chọn của học viên -->
  <section class="panel right-panel">

    <!-- nhiều câu hỏi -->
    <div class="right-content " id="multipleMode">
      <h2 class="answer-heading">Chọn đáp án cho các câu hỏi</h2>
      Câu 1
      <div class="question-list" id="questionList">
        <button class="answer-option" data-answer="A">A</button><button class="answer-option" data-answer="B">B</button><button class="answer-option" data-answer="C">C</button><button class="answer-option" data-answer="D">D</button>
      </div>
      Câu 2
      <div class="question-list" id="questionList">
        <button class="answer-option" data-answer="A">A</button><button class="answer-option" data-answer="B">B</button><button class="answer-option" data-answer="C">C</button><button class="answer-option" data-answer="D">D</button>
      </div>
      Câu 3
      <div class="question-list" id="questionList">
        <button class="answer-option" data-answer="A">A</button><button class="answer-option" data-answer="B">B</button><button class="answer-option" data-answer="C">C</button><button class="answer-option" data-answer="D">D</button>
      </div>
    </div>


  </section>
</main>

<!-- đề listening -->
<main class="workspace">
    <!--cụm đề -->
  <section class="panel left-panel">
    <h1 class="panel-title" id="leftTitle">Câu 101</h1>

    <!-- Audio nghe TOEIC -->
    <div class="audio-player" id="audioPlayer">
        <div class="audio-player__icon">🎧</div>

        <div class="audio-player__content">
            <div class="audio-player__header">
                <strong>Audio bài nghe</strong>
                <span id="audioStatus">Sẵn sàng</span>
            </div>

            <audio id="questionAudio" preload="metadata">
                <source src="{{ asset('audio/toeic-part5.mp3') }}" type="audio/mpeg">
                Trình duyệt của bạn không hỗ trợ phát audio.
            </audio>

            <div class="audio-player__controls">
                <button type="button" id="playAudio" aria-label="Phát audio">▶</button>
                <button type="button" id="restartAudio" aria-label="Phát lại">↺</button>

                <span id="audioCurrentTime">0:00</span>
                <input
                    type="range"
                    id="audioProgress"
                    min="0"
                    max="100"
                    value="0"
                    step="0.1"
                    aria-label="Tiến trình audio"
                >
                <span id="audioDuration">0:00</span>

                <button type="button" id="muteAudio" aria-label="Tắt âm">🔊</button>
            </div>
        </div>
    </div>

  </section>

  <!-- ghi nhận lựa chọn của học viên -->
  <section class="panel right-panel">
    <!-- 1 câu hỏi -->
    <div class="right-content" id="singleMode">
      <h2 class="answer-heading">Chọn đáp án</h2>
      <div class="answer-options" id="answerOptions">
        <button class="answer-option" data-answer="A">A</button><button class="answer-option" data-answer="B">B</button><button class="answer-option" data-answer="C">C</button><button class="answer-option" data-answer="D">D</button>
      </div>
    </div>

  </section>
</main>

<main class="workspace">
    <!--cụm đề -->
  <section class="panel left-panel">
    <h1 class="panel-title" id="leftTitle">Câu 101</h1>

    <!-- Audio nghe TOEIC -->
    <div class="audio-player" id="audioPlayer">
        <div class="audio-player__icon">🎧</div>

        <div class="audio-player__content">
            <div class="audio-player__header">
                <strong>Audio bài nghe</strong>
                <span id="audioStatus">Sẵn sàng</span>
            </div>

            <audio id="questionAudio" preload="metadata">
                <source src="{{ asset('audio/toeic-part5.mp3') }}" type="audio/mpeg">
                Trình duyệt của bạn không hỗ trợ phát audio.
            </audio>

            <div class="audio-player__controls">
                <button type="button" id="playAudio" aria-label="Phát audio">▶</button>
                <button type="button" id="restartAudio" aria-label="Phát lại">↺</button>

                <span id="audioCurrentTime">0:00</span>
                <input
                    type="range"
                    id="audioProgress"
                    min="0"
                    max="100"
                    value="0"
                    step="0.1"
                    aria-label="Tiến trình audio"
                >
                <span id="audioDuration">0:00</span>

                <button type="button" id="muteAudio" aria-label="Tắt âm">🔊</button>
            </div>
             71 What type of business is being advertised?<br>

            A. A restaurant <br>
            B. A clothing store<br>
            C. A fitness studio<br>
            D. A hair salon<br>
            72
            What is the purpose of the advertisement?<br>

            A. To promote a new business location<br>
            B. To advertise a job opening<br>
            C. To describe a class schedule<br>
            D. To promote a charity event<br>
            73
            What is offered to people who register during the first week?<br>

            A. A gift card<br>
            B. A discounted membership<br>
            C. A free personal training session<br>
            D. A free one-week trial<br>
        </div>
    </div>
  </section>
  <!-- ghi nhận lựa chọn của học viên -->
  <section class="panel right-panel">
    <!-- nhiều câu hỏi -->
    <div class="right-content " id="multipleMode">
      <h2 class="answer-heading">Chọn đáp án cho các câu hỏi</h2>
      Câu 71
      <div class="question-list" id="questionList">
        <button class="answer-option" data-answer="A">A</button><button class="answer-option" data-answer="B">B</button><button class="answer-option" data-answer="C">C</button><button class="answer-option" data-answer="D">D</button>
      </div>
      Câu 72
      <div class="question-list" id="questionList">
        <button class="answer-option" data-answer="A">A</button><button class="answer-option" data-answer="B">B</button><button class="answer-option" data-answer="C">C</button><button class="answer-option" data-answer="D">D</button>
      </div>
      Câu 73
      <div class="question-list" id="questionList">
        <button class="answer-option" data-answer="A">A</button><button class="answer-option" data-answer="B">B</button><button class="answer-option" data-answer="C">C</button><button class="answer-option" data-answer="D">D</button>
      </div>

    </div>
  </section>
</main>

<!-- đề speaking -->
<main class="workspace">
    <!--cụm đề -->
  <section class="panel left-panel">
    <h1 class="panel-title" id="leftTitle">Câu 101</h1>
    <div id="leftQuestion">
      <div class="prompt-box"><p>By measuring the ______ of the new adhesive under extreme heat, the researchers confirmed that it performs reliably in demanding environments.</p></div>
      <div class="left-choices" id="leftChoices">
        <div data-answer="A">A. endures</div><div data-answer="B">B. endure</div><div data-answer="C">C. enduring</div><div data-answer="D">D. endurance</div>
      </div>
    </div>
    <div id="structureContent" class="hidden">
      <div class="prompt-box"><strong>Cấu trúc bài thi</strong><p>Part 5 — Incomplete Sentences</p><p>Câu 101–130 · Chọn một đáp án đúng cho mỗi câu.</p><p>Thời gian còn lại được hiển thị trên thanh công cụ phía trên.</p></div>
    </div>

  </section>
  <!-- ghi nhận lựa chọn của học viên -->
  <section class="panel right-panel">
    <!-- ghi âm -->
    <div class="right-content " id="recordMode">
      <h2 class="answer-heading">Ghi âm phần nói</h2>
      <div class="record-box">
        <div class="record-status"><span class="record-dot" id="recordDot"></span><span id="recordStatus">Chưa bắt đầu ghi âm</span></div>
        <div class="record-time" id="recordTime">00:00</div>
        <div class="record-help" id="recordHelp">Nhấn “Bắt đầu ghi âm” để thu câu trả lời. Trình duyệt sẽ yêu cầu quyền sử dụng micro.</div>
        <div class="record-controls"><button class="primary" id="recordBtn">● Bắt đầu ghi âm</button><button class="secondary" id="stopRecordBtn" disabled>Dừng</button></div>
        <audio id="audioPlayback" controls class="hidden" style="width:100%"></audio>
      </div>
      <p class="record-help" style="margin-top:12px">Thời gian tối đa: 02:00 phút</p>
    </div>
  </section>
</main>

<!-- đề writing -->
<main class="workspace">
    <!--cụm đề -->
  <section class="panel left-panel">
    <h1 class="panel-title" id="leftTitle">Câu 101</h1>
    <div id="leftQuestion">
      <div class="prompt-box"><p>By measuring the ______ of the new adhesive under extreme heat, the researchers confirmed that it performs reliably in demanding environments.</p></div>
      <div class="left-choices" id="leftChoices">
        <div data-answer="A">A. endures</div><div data-answer="B">B. endure</div><div data-answer="C">C. enduring</div><div data-answer="D">D. endurance</div>
      </div>
    </div>
    <div id="structureContent" class="hidden">
      <div class="prompt-box"><strong>Cấu trúc bài thi</strong><p>Part 5 — Incomplete Sentences</p><p>Câu 101–130 · Chọn một đáp án đúng cho mỗi câu.</p><p>Thời gian còn lại được hiển thị trên thanh công cụ phía trên.</p></div>
    </div>
    <div id="explanationContent" class="hidden">
      <div class="prompt-box"><strong>Giải thích đáp án</strong><p>“By measuring the ______” cần một danh từ làm tân ngữ của động từ “measuring”.</p><p><strong>D. endurance</strong> là danh từ, nghĩa là sức bền/độ bền.</p></div>
    </div>
  </section>

  <!-- ghi nhận lựa chọn của học viên -->
  <section class="panel right-panel">


    <!-- bài viết -->
    <div class="right-content " id="writingMode">
      <h2 class="answer-heading">Bài viết của bạn</h2>
      <div class="prompt-box" style="margin-bottom:12px">Write an essay explaining whether you agree or disagree with the following statement. Support your opinion with reasons and examples.</div>
      <textarea id="writingArea" class="writing-area" placeholder="Nhập bài writing của bạn tại đây..."></textarea>
      <div class="word-count"><span id="wordCount">0 từ</span><span id="charCount">0 ký tự</span></div>
      <div style="margin-top:12px"><button class="secondary" id="saveWritingBtn">Lưu bản nháp</button><span id="saveStatus" style="margin-left:8px;color:#2b9b6a;font-size:10px"></span></div>
    </div>

  </section>
</main>

<div class="footer-actions">
    <button type="button" class="secondary" id="prevBtn">← Câu trước</button>
    <span id="answeredCount">Đã trả lời 0/164 câu</span>
    <button type="button" class="primary" id="nextBtn">Câu tiếp theo →</button>
    <a href="{{ url('/thi-thu/ket-qua') }}"><button type="button" class="primary" id="submitBtn">Nộp bài</button></a>
</div>


<div class="modal-backdrop" id="modal" hidden>
  <section class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <h2 id="modalTitle">Thông báo</h2>
    <p id="modalText"></p>
    <div class="modal-actions">
      <button type="button" class="secondary" id="modalCancel">Đóng</button>
      <button type="button" class="primary" id="modalConfirm">Xác nhận</button>
    </div>
  </section>
</div>

@push('scripts')

@endpush

</x-layouts.app>
