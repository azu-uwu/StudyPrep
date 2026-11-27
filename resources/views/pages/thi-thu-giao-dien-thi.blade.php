<x-layouts.app active="thi-thu" title="StudyPrep - Giao diện làm bài thi">
@push('styles')
<style>
    :root {
        --primary: #4f46e5;
        --primary-dark: #4338ca;
        --blue-btn: #0284c7;
        --blue-btn-hover: #0369a1;
        --explain-bg: #0284c7;
        --ink: #0f172a;
        --muted: #64748b;
        --border-color: #e2e8f0;
        --bg-page: #f8fafc;
        --panel-bg: #ffffff;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
    }

    * { box-sizing: border-box; }
    body {
        margin: 0;
        background: var(--bg-page);
        color: var(--ink);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: 13px;
        line-height: 1.5;
    }

    /* TOP BAR */
    .exam-topbar {
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 18px;
        background: #ffffff;
        border-bottom: 1px solid var(--border-color);
        position: sticky;
        top: 0;
        z-index: 100;
    }
    .topbar-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .topbar-logo {
        font-size: 17px;
        font-weight: 900;
        letter-spacing: -0.5px;
        text-decoration: none;
        white-space: nowrap;
    }
    .topbar-logo .blue-text { color: #2563eb; }
    .topbar-logo .orange-text { color: #f59e0b; }
    .topbar-btn-close {
        border: none;
        background: transparent;
        color: #94a3b8;
        font-size: 20px;
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
        transition: color 0.15s;
    }
    .topbar-btn-close:hover { color: #ef4444; }
    .topbar-question-badge {
        font-weight: 800;
        font-size: 12px;
        color: #334155;
        white-space: nowrap;
    }
    .topbar-timer-badge {
        background: #ef4444;
        color: #ffffff;
        font-weight: 800;
        font-size: 11px;
        padding: 4px 9px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .topbar-center {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .topbar-nav-btn {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        font-size: 11.5px;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 7px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .topbar-nav-btn:hover {
        background: #f1f5f9;
        color: #1e293b;
    }
    .topbar-nav-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .topbar-explain-btn {
        border: 1px solid #0284c7;
        background: #0284c7;
        color: #ffffff;
        font-size: 11.5px;
        font-weight: 800;
        padding: 6px 14px;
        border-radius: 20px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s;
    }
    .topbar-explain-btn.active {
        background: #0369a1;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.25);
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .topbar-action-icon {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        font-size: 14px;
        padding: 7px 10px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s;
    }
    .topbar-action-icon:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: #1e293b;
    }
    .topbar-action-icon.flagged {
        background: #fef3c7;
        border-color: #f59e0b;
        color: #d97706;
    }

    /* DIRECTIONS SCREEN (Screenshots 3, 5, 7, 9) */
    .directions-container {
        max-width: 680px;
        margin: 40px auto;
        padding: 0 16px;
    }
    .directions-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        padding: 34px 30px;
        display: flex;
        flex-direction: column;
        min-height: 440px;
    }
    .directions-card-title {
        font-size: 18px;
        font-weight: 850;
        color: #0f172a;
        margin: 0 0 18px;
        letter-spacing: -0.2px;
    }
    .directions-card-body {
        flex: 1;
        font-size: 13.5px;
        line-height: 1.85;
        color: #334155;
        white-space: pre-line;
    }
    .directions-btn-start {
        margin-top: 28px;
        width: 100%;
        background: #0284c7;
        color: #ffffff;
        border: none;
        border-radius: 9px;
        padding: 13px 20px;
        font-size: 14px;
        font-weight: 850;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: background 0.15s;
    }
    .directions-btn-start:hover {
        background: #0369a1;
    }

    /* MAIN EXAM WORKSPACE */
    .exam-workspace {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
        gap: 14px;
        padding: 14px 18px 24px;
        min-height: calc(100vh - 52px);
    }
    .exam-workspace.with-explanation {
        grid-template-columns: minmax(0, 1fr) minmax(0, 0.95fr) minmax(0, 1.05fr);
    }
    @media (max-width: 1024px) {
        .exam-workspace, .exam-workspace.with-explanation {
            grid-template-columns: 1fr;
        }
    }

    .exam-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px;
        overflow-y: auto;
        min-height: calc(100vh - 84px);
    }

    /* LEFT PANEL */
    .left-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .left-panel-title {
        font-size: 14px;
        font-weight: 850;
        color: #0f172a;
        margin: 0;
    }
    .left-part-badge {
        font-size: 11px;
        font-weight: 700;
        color: #0284c7;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        padding: 2px 8px;
        border-radius: 6px;
    }

    /* AUDIO PLAYER (Screenshots 4, 6, 8, 10) */
    .exam-audio-bar {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }
    .audio-btn-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        font-size: 13px;
        display: grid;
        place-items: center;
        cursor: pointer;
        flex: 0 0 32px;
        transition: all 0.15s;
    }
    .audio-btn-circle:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }
    .audio-btn-circle.play-btn {
        background: #0284c7;
        border-color: #0284c7;
        color: #ffffff;
    }
    .audio-btn-circle.play-btn:hover {
        background: #0369a1;
    }
    .audio-time-label {
        font-size: 11px;
        font-variant-numeric: tabular-nums;
        color: #475569;
        font-weight: 600;
        white-space: nowrap;
    }
    .audio-slider {
        flex: 1;
        accent-color: #0284c7;
        cursor: pointer;
    }

    /* LEFT CONTENT BOXES */
    .image-preview-container {
        text-align: center;
        margin-bottom: 16px;
    }
    .image-preview-container img {
        max-width: 100%;
        max-height: 380px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        object-fit: contain;
    }
    .prompt-text-box {
        font-size: 13px;
        color: #334155;
        line-height: 1.7;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #ffffff;
        margin-bottom: 14px;
    }
    .stimulus-reading-box {
        background: #fdfefe;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        max-height: 520px;
        overflow-y: auto;
        font-size: 13px;
        line-height: 1.8;
        color: #1e293b;
        white-space: pre-wrap;
    }
    .stimulus-reading-title {
        font-weight: 800;
        font-size: 13px;
        color: #0f172a;
        margin-bottom: 10px;
        display: block;
    }

    /* MIDDLE PANEL (GIẢI THÍCH - Screenshots 4, 6, 8, 10) */
    .explain-panel {
        background: var(--explain-bg);
        color: #ffffff;
        border-radius: 12px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        min-height: calc(100vh - 84px);
    }
    .explain-header-tabs {
        display: flex;
        align-items: center;
        gap: 6px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        padding-bottom: 10px;
        margin-bottom: 14px;
    }
    .explain-tab-btn {
        background: transparent;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.4);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }
    .explain-tab-btn.active {
        background: #ffffff;
        color: #0284c7;
        border-color: #ffffff;
    }
    .explain-tab-close {
        margin-left: auto;
        background: transparent;
        border: none;
        color: #ffffff;
        font-size: 16px;
        cursor: pointer;
        padding: 2px 6px;
    }
    .explain-content-box {
        flex: 1;
        font-size: 12px;
        line-height: 1.7;
    }
    .explain-badge-correct {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.4);
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-block;
        font-weight: 800;
        margin-bottom: 12px;
    }
    .explain-section-title {
        font-weight: 800;
        margin-top: 10px;
        margin-bottom: 4px;
        color: #e0f2fe;
    }
    .explain-bottom-pill {
        margin-top: 14px;
        background: rgba(0, 0, 0, 0.2);
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 10.5px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* RIGHT PANEL ("CHỌN ĐÁP ÁN") */
    .right-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .right-panel-title {
        font-size: 14px;
        font-weight: 850;
        color: #0f172a;
        margin: 0;
    }
    .helper-tooltip-banner {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        color: #b45309;
        font-size: 11px;
        padding: 7px 10px;
        border-radius: 8px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* QUESTIONS IN CLUSTER */
    .sub-question-card {
        padding: 12px 0 16px;
        border-bottom: 1px solid #edf2f7;
    }
    .sub-question-card:last-child {
        border-bottom: none;
    }
    .sub-question-prompt {
        font-size: 13px;
        font-weight: 750;
        color: #1e293b;
        margin: 0 0 10px;
    }
    .choice-list {
        display: grid;
        gap: 8px;
    }
    .choice-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.15s ease;
        text-align: left;
    }
    .choice-row:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .choice-row.selected {
        border-color: var(--primary);
        background: #eef2ff;
        color: var(--primary);
        font-weight: 700;
        box-shadow: 0 0 0 1px var(--primary);
    }
    .choice-circle-badge {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 1px solid currentColor;
        display: grid;
        place-items: center;
        font-size: 11px;
        font-weight: 850;
        flex: 0 0 24px;
    }
    .choice-row.selected .choice-circle-badge {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
    }
    .choice-text {
        font-size: 12.5px;
        line-height: 1.45;
    }

    /* PART 1 CIRCLE-ONLY OPTIONS (Screenshot 9) */
    .choice-list.circle-only-mode {
        display: flex;
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
        padding-top: 8px;
    }
    .choice-list.circle-only-mode .choice-row {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        padding: 0;
        margin: 0;
        display: grid;
        place-items: center;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        transition: all 0.15s ease;
    }
    .choice-list.circle-only-mode .choice-row:hover {
        border-color: #0284c7;
        background: #f0f9ff;
        color: #0284c7;
    }
    .choice-list.circle-only-mode .choice-row.selected {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.25);
    }
    .choice-list.circle-only-mode .choice-circle-badge {
        border: none;
        width: auto;
        height: auto;
        font-size: 13.5px;
        font-weight: 700;
        color: inherit;
        background: transparent;
    }
    .choice-list.circle-only-mode .choice-row.is-correct {
        background: #22c55e !important;
        color: #ffffff !important;
        border-color: #22c55e !important;
    }
    .choice-list.circle-only-mode .choice-row.is-wrong {
        background: #ef4444 !important;
        color: #ffffff !important;
        border-color: #ef4444 !important;
    }

    /* PALETTE POPOVER DROPDOWN (Screenshot 9) */
    .palette-popover {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 290px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
        z-index: 1000;
    }
    .palette-popover-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11.5px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 12px;
    }
    .palette-popover-part {
        color: #0f172a;
        font-size: 12px;
        letter-spacing: 0.02em;
    }
    .palette-popover-progress {
        color: #64748b;
        font-size: 11px;
        font-weight: 600;
    }
    .palette-popover-caret {
        color: #94a3b8;
        font-size: 11px;
    }
    .palette-popover-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 8px;
    }
    .palette-popover-cell {
        height: 36px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        font-size: 12px;
        font-weight: 750;
        display: grid;
        place-items: center;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .palette-popover-cell:hover {
        border-color: #0284c7;
        background: #f0f9ff;
        color: #0284c7;
    }
    .palette-popover-cell.current {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25);
    }
    .palette-popover-cell.answered {
        background: #4f46e5;
        color: #ffffff;
        border-color: #4f46e5;
    }

    /* SPEAKING / WRITING */
    .speaking-box, .writing-box {
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }
    .writing-textarea {
        width: 100%;
        min-height: 260px;
        padding: 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        line-height: 1.7;
        outline: none;
    }
    .writing-textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }
    .writing-stats {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: #64748b;
        margin-top: 6px;
    }

    /* MODAL */
    .exam-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: grid;
        place-items: center;
        padding: 18px;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(2px);
    }
    .exam-modal-backdrop[hidden] { display: none !important; }
    .exam-modal-dialog {
        width: min(100%, 460px);
        background: #ffffff;
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }
    .exam-modal-title {
        margin: 0 0 10px;
        font-size: 16px;
        font-weight: 850;
        color: #0f172a;
    }
    .exam-modal-body {
        margin: 0;
        color: #475569;
        font-size: 12.5px;
        line-height: 1.65;
    }
    .exam-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 22px;
    }

    /* QUESTION PALETTE MODAL */
    .palette-dialog {
        width: min(100%, 640px);
        max-height: 85vh;
        background: #ffffff;
        border-radius: 16px;
        padding: 22px;
        overflow-y: auto;
    }
    .palette-part-section {
        margin-bottom: 16px;
    }
    .palette-part-title {
        font-size: 12px;
        font-weight: 800;
        color: #334155;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
    }
    .palette-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(38px, 1fr));
        gap: 6px;
    }
    .palette-cell {
        height: 36px;
        border-radius: 7px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        font-size: 11px;
        font-weight: 750;
        display: grid;
        place-items: center;
        cursor: pointer;
        position: relative;
        transition: all 0.15s;
    }
    .palette-cell:hover {
        border-color: var(--primary);
        background: #f8fafc;
    }
    .palette-cell.answered {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #ffffff;
    }
    .palette-cell.current {
        box-shadow: 0 0 0 2px #0284c7;
        border-color: #0284c7;
        font-weight: 900;
    }
    .palette-cell.flagged::after {
        content: "⚑";
        position: absolute;
        top: -3px;
        right: 2px;
        font-size: 9px;
        color: #f59e0b;
    }

    .hidden { display: none !important; }
</style>
@endpush
@if (! empty($examTestForm))
<header class="topbar">
  <button class="close" id="exitBtn" title="Thoát" aria-label="Thoát">×</button>
  <span class="top-sep"></span>
  <strong style="font-size: 11px; color: #171a35;">{{ $examTestForm->title }}</strong>
  <span class="top-sep"></span>
  <span class="question-count" id="questionCount">Q 1/{{ count($examQuestions ?? []) }}</span>
  <span class="timer" id="timer">◷ 01:58:53</span>
</header>
<main class="workspace">
  <section class="panel left-panel">
    @php $prevExamPartCode = null; @endphp
    @forelse ($examQuestions as $qItem)
      @if ($qItem['part_code'] !== $prevExamPartCode && ! empty($qItem['part_directions_text']))
        <div class="prompt-box" style="margin-bottom: 12px; background: #f0f9ff; border: 1px solid #bae6fd;">
          <strong style="display: block; margin-bottom: 4px; color: #0369a1;">{{ $qItem['part_code'] }} — Directions</strong>
          <p style="margin: 0 0 8px 0;">{{ $qItem['part_directions_text'] }}</p>
          @if (! empty($qItem['part_directions_audio_url']))
            <audio controls preload="none" src="{{ $qItem['part_directions_audio_url'] }}" style="width: 100%; height: 34px;"></audio>
          @endif
        </div>
      @endif
      @php $prevExamPartCode = $qItem['part_code']; @endphp
      <article class="question-item" style="margin-bottom: 12px;">
        <h2 class="panel-title">Câu {{ $qItem['sequence_no'] }} ({{ $qItem['part_code'] }})</h2>
        @if (! empty($qItem['stimulus_text']))
          <div class="prompt-box" style="margin-bottom: 8px;">{{ $qItem['stimulus_text'] }}</div>
        @endif
        <div class="prompt-box"><p>{{ $qItem['content'] }}</p></div>
        @if (! empty($qItem['options']))
          <div class="left-choices">
            @foreach ($qItem['options'] as $lbl => $optContent)
              <div data-answer="{{ $lbl }}">{{ $lbl }}. {{ $optContent }}</div>
            @endforeach
          </div>
        @endif
      </article>
    @empty
      <div class="prompt-box"><p>Đề thi này hiện chưa có câu hỏi trạng thái ACTIVE.</p></div>
    @endforelse
  </section>
  <section class="panel right-panel">
    <div class="right-content">
      <h2 class="answer-heading">Phiếu trả lời ({{ count($examQuestions) }} câu)</h2>
      <div class="question-list">
        @foreach ($examQuestions as $qItem)
          <div class="question-item">
            <p><strong>Câu {{ $qItem['sequence_no'] }}</strong></p>
            <div class="mini-answers">
              @foreach (array_keys($qItem['options']) as $lbl)
                <button type="button" class="mini-answer" data-q="{{ $qItem['id'] }}" data-answer="{{ $lbl }}">{{ $lbl }}</button>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
</main>
@endif

<!-- TOP BAR -->
<header class="exam-topbar">
    <div class="topbar-left">
        <a href="{{ url('/thi-thu') }}" class="topbar-logo">
            <span class="blue-text">Study</span><span class="orange-text">Prep</span>
        </a>
        <button type="button" class="topbar-btn-close" id="btnExit" title="Thoát bài thi" aria-label="Thoát">×</button>
        <span class="topbar-question-badge" id="topbarQuestionBadge">Q 1/{{ $totalQuestions }}</span>
        <span class="topbar-timer-badge" id="topbarTimer">◷ 00:00:00</span>
    </div>

    <div class="topbar-center">
        <button type="button" class="topbar-nav-btn" id="btnPrev">← Câu trước</button>
        <button type="button" class="topbar-explain-btn" id="btnToggleExplain">
            <span>💡</span> <span>Giải thích</span>
        </button>
        <button type="button" class="topbar-nav-btn" id="btnNext">Câu sau →</button>
    </div>

    <div class="topbar-right" style="position: relative;">
        <button type="button" class="topbar-action-icon" id="btnPalette" title="Danh sách câu hỏi">
            <span>⊞</span> <span id="paletteCountText">0/{{ $totalQuestions }}</span>
        </button>
        <button type="button" class="topbar-action-icon" id="btnBookmark" title="Đánh dấu câu hỏi này">
            <span id="bookmarkIcon">⚐</span>
        </button>
        <button type="button" class="topbar-action-icon" id="btnSubmitExam" style="background:#0284c7; color:#fff; border-color:#0284c7; font-weight:800;" title="Nộp bài">
            <span>✈</span> <span>Nộp bài</span>
        </button>

        <!-- PALETTE POPOVER DROPDOWN (Screenshot 9) -->
        <div class="palette-popover" id="palettePopover" style="display: none;">
            <div class="palette-popover-header">
                <span class="palette-popover-part" id="popoverPartName">PART 1</span>
                <span class="palette-popover-progress" id="popoverProgressText">Hoàn thành 0% (0/6)</span>
                <span class="palette-popover-caret">⌵</span>
            </div>
            <div class="palette-popover-grid" id="popoverGrid"></div>
        </div>
    </div>
</header>

<!-- KHUNG HƯỚNG DẪN PART (DIRECTIONS OVERLAY - Screenshots 3, 5, 7, 9) -->
<div class="directions-container" id="directionsView" style="display: none;">
    <div class="directions-card">
        <h2 class="directions-card-title" id="dirPartTitle">PART 1: PHOTOGRAPHS</h2>
        <div class="directions-card-body" id="dirPartBody">
            Directions: For each question, you will listen to four short statements about a picture in your test book...
        </div>
        <button type="button" class="directions-btn-start" id="btnStartPart">BẮT ĐẦU</button>
    </div>
</div>

<!-- KHUNG LÀM BÀI CHÍNH (Screenshots 4, 6, 8, 10) -->
<main class="exam-workspace" id="examWorkspace">
    <!-- CỘT 1: NỘI DUNG ĐỀ BÀI (BÊN TRÁI) -->
    <section class="exam-panel left-panel">
        <div class="left-panel-header">
            <h1 class="left-panel-title" id="leftDisplayTitle">Câu 1</h1>
            <span class="left-part-badge" id="leftPartBadge">PART 1</span>
        </div>

        <!-- Audio Player (cho Listening) -->
        <div class="exam-audio-bar" id="audioPlayerBar" style="display: none;">
            <button type="button" class="audio-btn-circle" id="audioRewind10" title="Lùi 10s">↺</button>
            <button type="button" class="audio-btn-circle play-btn" id="audioPlayBtn" title="Phát audio">▶</button>
            <button type="button" class="audio-btn-circle" id="audioForward10" title="Tiến 10s">↻</button>
            <span class="audio-time-label" id="audioCurrentTime">00:00</span>
            <input type="range" class="audio-slider" id="audioProgress" min="0" max="100" value="0" step="0.1">
            <span class="audio-time-label" id="audioDuration">00:00</span>
            <audio id="audioElement" preload="metadata"></audio>
        </div>

        <!-- Hình ảnh câu hỏi (Part 1, v.v.) -->
        <div class="image-preview-container" id="imageBox" style="display: none;">
            <img id="imageElement" src="" alt="Hình ảnh câu hỏi">
        </div>

        <!-- Stimulus Reading Comprehension (Part 6, Part 7) -->
        <div id="stimulusBox" style="display: none; margin-bottom: 14px;">
            <div class="stimulus-reading-box">
                <span class="stimulus-reading-title" id="stimulusTitle"></span>
                <div id="stimulusText"></div>
            </div>
        </div>

        <!-- Prompt box (Part 2, Part 5) -->
        <div class="prompt-text-box" id="promptBox" style="display: none;">
            <p id="promptTextContent" style="margin: 0; font-size: 13.5px;"></p>
        </div>
    </section>

    <!-- CỘT 2: GIẢI THÍCH (Ở GIỮA - TOGGLEABLE) -->
    <section class="explain-panel" id="explainPanel" style="display: none;">
        <div class="explain-header-tabs">
            <button type="button" class="explain-tab-btn active" id="tabTranscript">Transcript</button>
            <button type="button" class="explain-tab-btn" id="tabDich">Dịch</button>
            <div id="clusterQuestionTabs" style="display: flex; gap: 4px; margin-left: 6px;"></div>
            <button type="button" class="explain-tab-close" id="btnCloseExplain">✕</button>
        </div>

        <div class="explain-content-box">
            <div class="explain-badge-correct" id="explainCorrectBadge">✪ Giải thích chi tiết</div>
            <div id="explainDynamicBody" style="margin-top: 10px;">
                <div class="explain-section-title">✪ Mẹo làm bài & căn cứ:</div>
                <p id="explainHintText" style="margin: 0 0 10px;">Chú ý các từ khóa chính và ngữ cảnh câu hỏi.</p>
                <div class="explain-section-title">✪ Dịch nghĩa / Nội dung:</div>
                <div id="explainContentText" style="white-space: pre-wrap;">Đang cập nhật...</div>
            </div>
        </div>

        <div class="explain-bottom-pill">
            <span>💡 Chạm vào từ để tra nghĩa, bôi đen cụm để dịch</span>
            <span style="cursor: pointer;" onclick="this.parentElement.style.display='none'">✕</span>
        </div>
    </section>

    <!-- CỘT 3: CHỌN ĐÁP ÁN (BÊN PHẢI) -->
    <section class="exam-panel right-panel">
        <div class="right-panel-header">
            <h2 class="right-panel-title">Chọn đáp án</h2>
            <button type="button" id="btnDirectionsReview" style="border:none; background:transparent; color:#0284c7; font-size:11px; font-weight:700; cursor:pointer;">Hướng dẫn Part</button>
        </div>

        <div class="helper-tooltip-banner">
            <span>💡</span> <span>Chạm vào từ để tra nghĩa, bôi đen cụm để dịch</span>
        </div>

        <!-- Danh sách các câu hỏi trong cụm (Multiple Choice) -->
        <div id="clusterQuestionsList"></div>

        <!-- Chế độ Ghi âm Speaking -->
        <div class="speaking-box hidden" id="speakingModeBox">
            <h3 style="margin: 0 0 10px; font-size: 13px;">Ghi âm câu trả lời:</h3>
            <p style="font-size: 11px; color: #64748b;">Nhấn "Bắt đầu ghi âm" để ghi âm câu trả lời Speaking của bạn.</p>
            <div style="display: flex; gap: 8px; margin-top: 10px;">
                <button type="button" class="topbar-nav-btn" id="btnRecordStart">● Bắt đầu ghi âm</button>
                <button type="button" class="topbar-nav-btn" id="btnRecordStop" disabled>Dừng</button>
            </div>
            <audio id="audioSpeakingPlayback" controls class="hidden" style="width: 100%; margin-top: 12px;"></audio>
        </div>

        <!-- Chế độ Viết Writing -->
        <div class="writing-box hidden" id="writingModeBox">
            <h3 style="margin: 0 0 10px; font-size: 13px;">Bài viết của bạn:</h3>
            <textarea class="writing-textarea" id="writingInputArea" placeholder="Nhập bài viết của bạn tại đây..."></textarea>
            <div class="writing-stats">
                <span id="writingWordCount">0 từ</span>
                <span id="writingCharCount">0 ký tự</span>
            </div>
        </div>
    </section>
</main>

<!-- MODAL BẢNG DANH SÁCH CÂU HỎI -->
<div class="exam-modal-backdrop" id="paletteModal" hidden>
    <div class="palette-dialog" role="dialog" aria-modal="true">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 style="margin: 0; font-size: 16px; font-weight: 850; color: #0f172a;">Bảng danh sách câu hỏi</h2>
            <button type="button" class="topbar-btn-close" id="btnClosePalette" style="font-size: 24px;">×</button>
        </div>
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px; font-size: 11.5px; color: #64748b;">
            <span style="display: inline-flex; align-items: center; gap: 5px;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #4f46e5; display: inline-block;"></span> Đã làm</span>
            <span style="display: inline-flex; align-items: center; gap: 5px;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #fff; border: 1px solid #cbd5e1; display: inline-block;"></span> Chưa làm</span>
            <span style="display: inline-flex; align-items: center; gap: 5px;"><span style="width: 12px; height: 12px; border-radius: 3px; border: 2px solid #0284c7; display: inline-block;"></span> Đang xem</span>
            <span style="display: inline-flex; align-items: center; gap: 5px;"><span style="color: #f59e0b; font-weight: bold;">⚑</span> Đã đánh dấu</span>
        </div>
        <div id="palettePartContainer"></div>
    </div>
</div>

<!-- MODAL XÁC NHẬN NỘP BÀI -->
<div class="exam-modal-backdrop" id="submitConfirmModal" hidden>
    <div class="exam-modal-dialog" role="dialog" aria-modal="true">
        <h2 class="exam-modal-title">Xác nhận nộp bài thi</h2>
        <div class="exam-modal-body" id="submitConfirmBody">
            Bạn có chắc chắn muốn nộp bài thi không?
        </div>
        <div class="exam-modal-actions">
            <button type="button" class="topbar-nav-btn" id="btnCancelSubmit">Tiếp tục làm bài</button>
            <button type="button" class="topbar-explain-btn" id="btnConfirmSubmit" style="background: #10b981; border-color: #10b981;">Nộp bài ngay</button>
        </div>
    </div>
</div>

<!-- MODAL XÁC NHẬN THOÁT -->
<div class="exam-modal-backdrop" id="exitConfirmModal" hidden>
    <div class="exam-modal-dialog" role="dialog" aria-modal="true">
        <h2 class="exam-modal-title">Rời khỏi bài thi?</h2>
        <p class="exam-modal-body">Tiến trình làm bài của bạn sẽ được lưu lại. Bạn có thể quay lại tiếp tục làm sau.</p>
        <div class="exam-modal-actions">
            <button type="button" class="topbar-nav-btn" id="btnCancelExit">Ở lại</button>
            <a href="{{ url('/thi-thu') }}" class="topbar-explain-btn" style="background:#ef4444; border-color:#ef4444; text-decoration:none;">Rời khỏi</a>
        </div>
    </div>
</div>

<!-- FORM NỘP BÀI GỬI LÊN SERVER -->
<form id="realSubmitForm" method="POST" action="{{ route('thi-thu.nop-bai', $attempt->attempt_id) }}" style="display: none;">
    @csrf
    <div id="formHiddenInputs"></div>
</form>

@push('scripts')
<script>
(function () {
    const clusters = @json($clustersPayload);
    const flatQuestions = @json($flatQuestions);
    const partDirections = @json($partDirections);
    const totalQuestions = {{ $totalQuestions }};
    let remainingSeconds = {{ $remainingSeconds }};

    let currentClusterIdx = 0;
    let showExplanation = false;
    const userAnswers = {};
    const bookmarkedQuestions = {};
    const seenDirections = {};

    // Khởi tạo câu trả lời đã lưu
    flatQuestions.forEach(function (fq) {
        if (fq.saved_answer) {
            userAnswers[fq.question_id] = fq.saved_answer;
        }
    });

    // Các phần tử DOM
    const topbarQuestionBadge = document.getElementById('topbarQuestionBadge');
    const topbarTimer = document.getElementById('topbarTimer');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const btnToggleExplain = document.getElementById('btnToggleExplain');
    const btnCloseExplain = document.getElementById('btnCloseExplain');
    const btnPalette = document.getElementById('btnPalette');
    const btnBookmark = document.getElementById('btnBookmark');
    const bookmarkIcon = document.getElementById('bookmarkIcon');
    const btnSubmitExam = document.getElementById('btnSubmitExam');
    const btnExit = document.getElementById('btnExit');

    const directionsView = document.getElementById('directionsView');
    const dirPartTitle = document.getElementById('dirPartTitle');
    const dirPartBody = document.getElementById('dirPartBody');
    const btnStartPart = document.getElementById('btnStartPart');
    const btnDirectionsReview = document.getElementById('btnDirectionsReview');

    const examWorkspace = document.getElementById('examWorkspace');
    const explainPanel = document.getElementById('explainPanel');
    const leftDisplayTitle = document.getElementById('leftDisplayTitle');
    const leftPartBadge = document.getElementById('leftPartBadge');

    const audioPlayerBar = document.getElementById('audioPlayerBar');
    const audioElement = document.getElementById('audioElement');
    const audioPlayBtn = document.getElementById('audioPlayBtn');
    const audioRewind10 = document.getElementById('audioRewind10');
    const audioForward10 = document.getElementById('audioForward10');
    const audioProgress = document.getElementById('audioProgress');
    const audioCurrentTime = document.getElementById('audioCurrentTime');
    const audioDuration = document.getElementById('audioDuration');

    const imageBox = document.getElementById('imageBox');
    const imageElement = document.getElementById('imageElement');
    const stimulusBox = document.getElementById('stimulusBox');
    const stimulusTitle = document.getElementById('stimulusTitle');
    const stimulusText = document.getElementById('stimulusText');
    const promptBox = document.getElementById('promptBox');
    const promptTextContent = document.getElementById('promptTextContent');

    const clusterQuestionsList = document.getElementById('clusterQuestionsList');
    const speakingModeBox = document.getElementById('speakingModeBox');
    const writingModeBox = document.getElementById('writingModeBox');
    const writingInputArea = document.getElementById('writingInputArea');
    const writingWordCount = document.getElementById('writingWordCount');
    const writingCharCount = document.getElementById('writingCharCount');

    const paletteModal = document.getElementById('paletteModal');
    const btnClosePalette = document.getElementById('btnClosePalette');
    const palettePartContainer = document.getElementById('palettePartContainer');
    const paletteCountText = document.getElementById('paletteCountText');

    const submitConfirmModal = document.getElementById('submitConfirmModal');
    const submitConfirmBody = document.getElementById('submitConfirmBody');
    const btnCancelSubmit = document.getElementById('btnCancelSubmit');
    const btnConfirmSubmit = document.getElementById('btnConfirmSubmit');

    const exitConfirmModal = document.getElementById('exitConfirmModal');
    const btnCancelExit = document.getElementById('btnCancelExit');

    // 1. Đồng hồ đếm ngược
    function formatTime(sec) {
        const h = Math.floor(sec / 3600);
        const m = Math.floor((sec % 3600) / 60);
        const s = sec % 60;
        return [
            h > 0 ? String(h).padStart(2, '0') : null,
            String(m).padStart(2, '0'),
            String(s).padStart(2, '0')
        ].filter(Boolean).join(':');
    }

    const timerInterval = setInterval(function () {
        if (remainingSeconds <= 0) {
            clearInterval(timerInterval);
            alert('Đã hết thời gian làm bài! Hệ thống sẽ tự động nộp bài.');
            submitForm();
            return;
        }
        remainingSeconds--;
        topbarTimer.textContent = '◷ ' + formatTime(remainingSeconds);
    }, 1000);
    topbarTimer.textContent = '◷ ' + formatTime(remainingSeconds);

    // 2. Audio Player
    function formatAudioTime(seconds) {
        if (isNaN(seconds)) return '00:00';
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    let speechTimer = null;
    let simulatedSeconds = 0;

    function stopSimulatedAudio() {
        if (speechTimer) {
            clearInterval(speechTimer);
            speechTimer = null;
        }
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
        }
    }

    audioElement.addEventListener('timeupdate', function () {
        if (!isNaN(audioElement.duration) && audioElement.duration > 0) {
            const pct = (audioElement.currentTime / audioElement.duration) * 100;
            audioProgress.value = pct;
            audioCurrentTime.textContent = formatAudioTime(audioElement.currentTime);
            audioDuration.textContent = formatAudioTime(audioElement.duration);
        }
    });

    audioElement.addEventListener('ended', function () {
        audioPlayBtn.textContent = '▶';
    });

    function startSimulatedAudio(c) {
        stopSimulatedAudio();
        audioPlayBtn.textContent = '⏸';
        const totalSimDuration = 30;
        audioDuration.textContent = formatAudioTime(totalSimDuration);

        if ('speechSynthesis' in window && c && c.questions && c.questions[0]) {
            const q = c.questions[0];
            const num = q.part_q_num || q.sequence_no;
            let speakText = `Number ${num}. Look at the picture marked number ${num} in your test book. `;
            if (q.options && q.options.length) {
                q.options.forEach(opt => {
                    speakText += `Option (${opt.label}). ${opt.content}. `;
                });
            }
            const utter = new SpeechSynthesisUtterance(speakText);
            utter.lang = 'en-US';
            utter.rate = 0.95;
            utter.onend = function () {
                stopSimulatedAudio();
                audioPlayBtn.textContent = '▶';
                audioProgress.value = 100;
                audioCurrentTime.textContent = formatAudioTime(totalSimDuration);
            };
            window.speechSynthesis.speak(utter);
        }

        speechTimer = setInterval(function () {
            simulatedSeconds += 1;
            if (simulatedSeconds > totalSimDuration) {
                stopSimulatedAudio();
                audioPlayBtn.textContent = '▶';
                simulatedSeconds = 0;
                return;
            }
            audioCurrentTime.textContent = formatAudioTime(simulatedSeconds);
            audioProgress.value = (simulatedSeconds / totalSimDuration) * 100;
        }, 1000);
    }

    audioPlayBtn.addEventListener('click', function () {
        const c = clusters[currentClusterIdx];
        if (audioElement.src && !audioElement.error && !audioElement.src.endsWith('/null') && !audioElement.src.endsWith('#')) {
            if (audioElement.paused) {
                audioElement.play().then(() => {
                    audioPlayBtn.textContent = '⏸';
                }).catch(() => {
                    startSimulatedAudio(c);
                });
            } else {
                audioElement.pause();
                audioPlayBtn.textContent = '▶';
            }
        } else {
            if (speechTimer) {
                stopSimulatedAudio();
                audioPlayBtn.textContent = '▶';
            } else {
                startSimulatedAudio(c);
            }
        }
    });

    audioRewind10.addEventListener('click', function () {
        if (audioElement.src && !audioElement.paused && !isNaN(audioElement.duration)) {
            audioElement.currentTime = Math.max(0, audioElement.currentTime - 10);
        } else {
            simulatedSeconds = Math.max(0, simulatedSeconds - 10);
            audioCurrentTime.textContent = formatAudioTime(simulatedSeconds);
            audioProgress.value = (simulatedSeconds / 30) * 100;
        }
    });

    audioForward10.addEventListener('click', function () {
        if (audioElement.src && !audioElement.paused && !isNaN(audioElement.duration)) {
            audioElement.currentTime = Math.min(audioElement.duration || 30, audioElement.currentTime + 10);
        } else {
            simulatedSeconds = Math.min(30, simulatedSeconds + 10);
            audioCurrentTime.textContent = formatAudioTime(simulatedSeconds);
            audioProgress.value = (simulatedSeconds / 30) * 100;
        }
    });

    audioProgress.addEventListener('input', function () {
        if (!isNaN(audioElement.duration) && audioElement.duration > 0) {
            audioElement.currentTime = (audioProgress.value / 100) * audioElement.duration;
        } else {
            simulatedSeconds = Math.round((audioProgress.value / 100) * 30);
            audioCurrentTime.textContent = formatAudioTime(simulatedSeconds);
        }
    });

    function setupAudio(url, c) {
        stopSimulatedAudio();
        simulatedSeconds = 0;
        const isListening = (c && (c.is_listening || ['PART1','PART2','PART3','PART4'].includes(c.section_code)));

        if (url || isListening) {
            audioPlayerBar.style.display = 'flex';
            audioPlayBtn.textContent = '▶';
            audioProgress.value = 0;
            audioCurrentTime.textContent = '00:00';
            audioDuration.textContent = '00:30';

            if (url) {
                if (audioElement.src !== url) {
                    audioElement.src = url;
                    audioElement.load();
                }
            } else {
                audioElement.removeAttribute('src');
            }
        } else {
            audioPlayerBar.style.display = 'none';
            audioElement.pause();
            audioElement.removeAttribute('src');
        }
    }

    // 3. Hiển thị cụm câu hỏi (Render Cluster)
    function renderCurrentCluster() {
        if (!clusters.length) return;
        const c = clusters[currentClusterIdx];

        // Kiểm tra xem đã xem hướng dẫn của Part này chưa
        if (!seenDirections[c.section_code]) {
            showPartDirections(c.section_code);
            return;
        }

        directionsView.style.display = 'none';
        examWorkspace.style.display = 'grid';

        // Cập nhật Top bar
        topbarQuestionBadge.textContent = c.badge_label;
        btnPrev.disabled = (currentClusterIdx === 0);
        btnNext.disabled = (currentClusterIdx === clusters.length - 1);

        // Đánh dấu bookmark
        const isCurrentClusterFlagged = c.questions.some(q => bookmarkedQuestions[q.question_id]);
        if (isCurrentClusterFlagged) {
            btnBookmark.classList.add('flagged');
            bookmarkIcon.textContent = '⚑';
        } else {
            btnBookmark.classList.remove('flagged');
            bookmarkIcon.textContent = '⚐';
        }

        // Cập nhật Cột Trái
        leftDisplayTitle.textContent = c.display_label;
        leftPartBadge.textContent = c.section_code;
        if (c.section_code === 'PART1') {
            leftPartBadge.style.display = 'none';
        } else {
            leftPartBadge.style.display = 'inline-block';
        }

        // Audio
        setupAudio(c.audio_url, c);

        // Hình ảnh (Part 1, v.v.)
        if (c.media_url) {
            imageBox.style.display = 'block';
            imageElement.src = c.media_url;
        } else {
            imageBox.style.display = 'none';
        }

        // Stimulus (Part 6, Part 7)
        if (c.stimulus_text || c.title) {
            stimulusBox.style.display = 'block';
            stimulusTitle.textContent = c.title || '';
            stimulusText.textContent = c.stimulus_text || '';
        } else {
            stimulusBox.style.display = 'none';
        }

        // Prompt text cho Part 2 / Part 5 nếu không có stimulus
        if (!c.stimulus_text && !c.media_url && !c.is_group && c.questions[0]?.content) {
            promptBox.style.display = 'block';
            promptTextContent.textContent = c.questions[0].content;
        } else if (c.section_code === 'PART2') {
            promptBox.style.display = 'block';
            promptTextContent.textContent = 'Listen and choose correct answer';
        } else {
            promptBox.style.display = 'none';
        }

        // Render Cột Phải: Các câu hỏi trắc nghiệm
        clusterQuestionsList.innerHTML = '';
        speakingModeBox.classList.add('hidden');
        writingModeBox.classList.add('hidden');

        if (c.is_speaking) {
            speakingModeBox.classList.remove('hidden');
        } else if (c.is_writing) {
            writingModeBox.classList.remove('hidden');
            const q = c.questions[0];
            writingInputArea.value = userAnswers[q.question_id] || '';
            updateWordCount();
        } else {
            // Render trắc nghiệm Multiple Choice
            c.questions.forEach(function (q) {
                const qCard = document.createElement('div');
                qCard.className = 'sub-question-card';

                // Nếu là bài đọc cụm hoặc Part 3/4/6/7, hiển thị số câu + đề
                if (c.is_group || c.questions.length > 1) {
                    const promptEl = document.createElement('h3');
                    promptEl.className = 'sub-question-prompt';
                    promptEl.textContent = q.sequence_no + '. ' + (q.content || '');
                    qCard.appendChild(promptEl);
                }

                const choiceList = document.createElement('div');
                choiceList.className = 'choice-list';
                if (c.section_code === 'PART1') {
                    choiceList.classList.add('circle-only-mode');
                }

                q.options.forEach(function (opt) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'choice-row';
                    const isSelected = (userAnswers[q.question_id] === opt.label);
                    if (isSelected) btn.classList.add('selected');

                    const badge = document.createElement('span');
                    badge.className = 'choice-circle-badge';
                    badge.textContent = opt.label;

                    btn.appendChild(badge);
                    // Ở Part 1, đề thi thật chỉ nghe qua audio, không in nội dung câu lên màn hình
                    if (c.section_code !== 'PART1' && opt.content) {
                        const labelText = document.createElement('span');
                        labelText.className = 'choice-text';
                        labelText.textContent = opt.content || '';
                        btn.appendChild(labelText);
                    }

                    btn.addEventListener('click', function () {
                        userAnswers[q.question_id] = opt.label;
                        choiceList.querySelectorAll('.choice-row').forEach(el => el.classList.remove('selected'));
                        btn.classList.add('selected');
                        updateAnsweredBadge();
                        renderPopoverPalette();
                    });

                    choiceList.appendChild(btn);
                });

                qCard.appendChild(choiceList);
                clusterQuestionsList.appendChild(qCard);
            });
        }

        // Cập nhật Cột Giải thích (Middle panel)
        renderExplanationPanel(c);
        updateAnsweredBadge();
        renderPopoverPalette();
    }

    // 4. Hướng dẫn Part
    function showPartDirections(partCode) {
        const dir = partDirections[partCode] || {
            title: partCode,
            text: 'Directions: Answer the questions according to the instructions.'
        };
        dirPartTitle.textContent = dir.title;
        dirPartBody.textContent = dir.text;
        examWorkspace.style.display = 'none';
        directionsView.style.display = 'block';
    }

    btnStartPart.addEventListener('click', function () {
        const c = clusters[currentClusterIdx];
        seenDirections[c.section_code] = true;
        renderCurrentCluster();
    });

    btnDirectionsReview.addEventListener('click', function () {
        const c = clusters[currentClusterIdx];
        showPartDirections(c.section_code);
    });

    // 5. Cột giải thích
    function renderExplanationPanel(c) {
        const tabContainer = document.getElementById('clusterQuestionTabs');
        tabContainer.innerHTML = '';

        if (c.questions.length > 1) {
            c.questions.forEach(function (q, i) {
                const tab = document.createElement('button');
                tab.type = 'button';
                tab.className = 'explain-tab-btn' + (i === 0 ? ' active' : '');
                tab.textContent = q.sequence_no;
                tab.addEventListener('click', function () {
                    tabContainer.querySelectorAll('.explain-tab-btn').forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    showQuestionExplanation(q);
                });
                tabContainer.appendChild(tab);
            });
        }

        showQuestionExplanation(c.questions[0]);
    }

    function showQuestionExplanation(q) {
        const hintEl = document.getElementById('explainHintText');
        const contentEl = document.getElementById('explainContentText');
        const correctBadge = document.getElementById('explainCorrectBadge');

        correctBadge.textContent = '✪ Câu ' + q.sequence_no;
        hintEl.textContent = 'Dạng bài: ' + (q.explanation ? 'Có giải thích chi tiết' : 'Luyện tập tiêu chuẩn TOEIC');
        contentEl.textContent = q.explanation || 'Nội dung giải thích và transcript chi tiết sẽ được hiển thị đầy đủ sau khi nộp bài.';
    }

    btnToggleExplain.addEventListener('click', function () {
        showExplanation = !showExplanation;
        if (showExplanation) {
            btnToggleExplain.classList.add('active');
            explainPanel.style.display = 'flex';
            examWorkspace.classList.add('with-explanation');
        } else {
            btnToggleExplain.classList.remove('active');
            explainPanel.style.display = 'none';
            examWorkspace.classList.remove('with-explanation');
        }
    });

    btnCloseExplain.addEventListener('click', function () {
        showExplanation = false;
        btnToggleExplain.classList.remove('active');
        explainPanel.style.display = 'none';
        examWorkspace.classList.remove('with-explanation');
    });

    // 6. Điều hướng Câu trước / Câu sau
    btnPrev.addEventListener('click', function () {
        if (currentClusterIdx > 0) {
            currentClusterIdx--;
            renderCurrentCluster();
        }
    });

    btnNext.addEventListener('click', function () {
        if (currentClusterIdx < clusters.length - 1) {
            currentClusterIdx++;
            renderCurrentCluster();
        }
    });

    // 7. Bookmark / Đánh dấu
    btnBookmark.addEventListener('click', function () {
        const c = clusters[currentClusterIdx];
        const isFlagged = c.questions.some(q => bookmarkedQuestions[q.question_id]);
        c.questions.forEach(q => {
            bookmarkedQuestions[q.question_id] = !isFlagged;
        });
        if (!isFlagged) {
            btnBookmark.classList.add('flagged');
            bookmarkIcon.textContent = '⚑';
        } else {
            btnBookmark.classList.remove('flagged');
            bookmarkIcon.textContent = '⚐';
        }
    });

    // 8. Đếm số câu đã làm
    function updateAnsweredBadge() {
        const answeredCount = Object.keys(userAnswers).filter(k => userAnswers[k] && userAnswers[k].trim() !== '').length;
        paletteCountText.textContent = answeredCount + '/' + totalQuestions;
    }

    // 9. Writing textarea input
    function updateWordCount() {
        const text = writingInputArea.value.trim();
        const words = text ? text.split(/\s+/).length : 0;
        writingWordCount.textContent = words + ' từ';
        writingCharCount.textContent = text.length + ' ký tự';
    }
    writingInputArea.addEventListener('input', function () {
        updateWordCount();
        const c = clusters[currentClusterIdx];
        if (c.questions[0]) {
            userAnswers[c.questions[0].question_id] = writingInputArea.value;
            updateAnsweredBadge();
        }
    });

    // 10. Question Palette Modal & Popover (Screenshot 9)
    const palettePopover = document.getElementById('palettePopover');
    const popoverPartName = document.getElementById('popoverPartName');
    const popoverProgressText = document.getElementById('popoverProgressText');
    const popoverGrid = document.getElementById('popoverGrid');

    function renderPopoverPalette() {
        if (!palettePopover) return;
        const currentC = clusters[currentClusterIdx];
        const secCode = currentC ? currentC.section_code : 'PART1';

        // Lọc các câu hỏi thuộc Part đang làm
        const partQuestions = flatQuestions.filter(q => q.section_code === secCode);
        const answeredInPart = partQuestions.filter(q => userAnswers[q.question_id] && userAnswers[q.question_id].trim() !== '').length;
        const totalInPart = partQuestions.length;
        const pct = totalInPart > 0 ? Math.round((answeredInPart / totalInPart) * 100) : 0;

        popoverPartName.textContent = secCode;
        popoverProgressText.textContent = `Hoàn thành ${pct}% (${answeredInPart}/${totalInPart})`;

        popoverGrid.innerHTML = '';
        partQuestions.forEach(function (fq) {
            const cell = document.createElement('div');
            cell.className = 'palette-popover-cell';
            cell.textContent = fq.part_q_num || fq.sequence_no;

            const isCurrent = currentC && currentC.questions.some(q => q.question_id === fq.question_id);
            if (isCurrent) cell.classList.add('current');

            const hasAns = (userAnswers[fq.question_id] && userAnswers[fq.question_id].trim() !== '');
            if (hasAns) cell.classList.add('answered');

            cell.addEventListener('click', function (e) {
                e.stopPropagation();
                palettePopover.style.display = 'none';
                currentClusterIdx = fq.cluster_index;
                seenDirections[fq.section_code] = true;
                renderCurrentCluster();
            });

            popoverGrid.appendChild(cell);
        });
    }

    btnPalette.addEventListener('click', function (e) {
        e.stopPropagation();
        if (palettePopover && (palettePopover.style.display === 'none' || !palettePopover.style.display)) {
            renderPopoverPalette();
            palettePopover.style.display = 'block';
        } else if (palettePopover) {
            palettePopover.style.display = 'none';
        }
    });

    document.addEventListener('click', function (e) {
        if (palettePopover && !palettePopover.contains(e.target) && e.target !== btnPalette && !btnPalette.contains(e.target)) {
            palettePopover.style.display = 'none';
        }
    });

    btnClosePalette.addEventListener('click', function () {
        paletteModal.hidden = true;
    });

    function renderPalette() {
        palettePartContainer.innerHTML = '';

        // Nhóm các câu hỏi theo Part
        const groupedByPart = {};
        flatQuestions.forEach(function (fq) {
            if (!groupedByPart[fq.section_code]) {
                groupedByPart[fq.section_code] = [];
            }
            groupedByPart[fq.section_code].push(fq);
        });

        Object.keys(groupedByPart).forEach(function (secCode) {
            const list = groupedByPart[secCode];
            const partSec = document.createElement('div');
            partSec.className = 'palette-part-section';

            const partTitle = document.createElement('div');
            partTitle.className = 'palette-part-title';
            partTitle.innerHTML = '<span>' + secCode + '</span><span>' + list.length + ' câu</span>';
            partSec.appendChild(partTitle);

            const grid = document.createElement('div');
            grid.className = 'palette-grid';

            list.forEach(function (fq) {
                const cell = document.createElement('div');
                cell.className = 'palette-cell';
                cell.textContent = fq.sequence_no;

                const hasAns = (userAnswers[fq.question_id] && userAnswers[fq.question_id].trim() !== '');
                if (hasAns) cell.classList.add('answered');

                const currentC = clusters[currentClusterIdx];
                const isCurrent = currentC.questions.some(q => q.question_id === fq.question_id);
                if (isCurrent) cell.classList.add('current');

                if (bookmarkedQuestions[fq.question_id]) cell.classList.add('flagged');

                cell.addEventListener('click', function () {
                    paletteModal.hidden = true;
                    currentClusterIdx = fq.cluster_index;
                    seenDirections[fq.section_code] = true;
                    renderCurrentCluster();
                });

                grid.appendChild(cell);
            });

            partSec.appendChild(grid);
            palettePartContainer.appendChild(partSec);
        });
    }

    // 11. Nộp bài thi
    btnSubmitExam.addEventListener('click', function () {
        const answeredCount = Object.keys(userAnswers).filter(k => userAnswers[k] && userAnswers[k].trim() !== '').length;
        const unansweredCount = totalQuestions - answeredCount;

        submitConfirmBody.innerHTML = `
            <p style="margin:0 0 10px;">Bạn đã hoàn thành <strong>${answeredCount}/${totalQuestions}</strong> câu.</p>
            ${unansweredCount > 0 ? `<p style="color:#ef4444; margin:0;">Còn <strong>${unansweredCount}</strong> câu chưa có đáp án.</p>` : `<p style="color:#10b981; margin:0;">Tuyệt vời! Bạn đã hoàn thành toàn bộ bài thi.</p>`}
            <p style="margin:12px 0 0; color:#64748b; font-size:12px;">Bạn có chắc chắn muốn nộp bài để xem điểm số và kết quả chi tiết?</p>
        `;
        submitConfirmModal.hidden = false;
    });

    btnCancelSubmit.addEventListener('click', function () {
        submitConfirmModal.hidden = true;
    });

    btnConfirmSubmit.addEventListener('click', function () {
        submitForm();
    });

    function submitForm() {
        const form = document.getElementById('realSubmitForm');
        const container = document.getElementById('formHiddenInputs');
        container.innerHTML = '';

        Object.keys(userAnswers).forEach(function (qId) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'answers[' + qId + ']';
            input.value = userAnswers[qId];
            container.appendChild(input);
        });

        form.submit();
    }

    // 12. Thoát bài thi
    btnExit.addEventListener('click', function () {
        exitConfirmModal.hidden = false;
    });

    btnCancelExit.addEventListener('click', function () {
        exitConfirmModal.hidden = true;
    });

    // Khởi động
    renderCurrentCluster();
})();
</script>
@endpush
</x-layouts.app>
