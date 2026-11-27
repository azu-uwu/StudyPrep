<x-layouts.app active="thi-thu" title="StudyPrep - Chi tiết đề thi">
    @push('styles')
    <style>
        .exam-detail-page {
            max-width: 1120px;
            margin: 0 auto;
            padding: 8px 0 40px;
            color: #17213b;
        }
        .exam-detail-page * { box-sizing: border-box; }
        .exam-detail-page .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 18px;
            color: #69758d;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }
        .exam-detail-page .back-link:hover { color: #514bff; }
        .exam-detail-page .exam-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 220px;
            gap: 24px;
            align-items: center;
            padding: 28px;
            border: 1px solid #e5e6ff;
            border-radius: 22px;
            background: linear-gradient(120deg, #f3f0ff 0%, #fff 60%, #eff8ff 100%);
        }
        .exam-detail-page .exam-hero::after {
            content: "";
            position: absolute;
            width: 190px;
            height: 190px;
            right: -82px;
            top: -100px;
            border: 18px solid #dedcff55;
            border-radius: 50%;
            pointer-events: none;
        }
        .exam-detail-page .exam-hero-main { position: relative; z-index: 1; }
        .exam-detail-page .exam-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 10px;
            color: #6652ff;
            font-size: 10px;
            font-weight: 850;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .exam-detail-page .exam-title {
            margin: 0;
            color: #18213e;
            font-size: clamp(24px, 3vw, 32px);
            line-height: 1.2;
            font-weight: 900;
            letter-spacing: -.035em;
        }
        .exam-detail-page .exam-summary {
            max-width: 680px;
            margin: 10px 0 0;
            color: #68748d;
            font-size: 13px;
            line-height: 1.7;
        }
        .exam-detail-page .hero-score {
            position: relative;
            z-index: 1;
            padding: 18px;
            border: 1px solid #fff;
            border-radius: 16px;
            background: #ffffffd9;
            box-shadow: 0 8px 25px #323a6a0b;
        }
        .exam-detail-page .hero-score-label {
            color: #7a849b;
            font-size: 11px;
        }
        .exam-detail-page .hero-score-value {
            margin-top: 4px;
            color: #18213e;
            font-size: 28px;
            line-height: 1.2;
            font-weight: 900;
        }
        .exam-detail-page .hero-score-note {
            margin-top: 5px;
            color: #8a94a9;
            font-size: 10px;
            line-height: 1.5;
        }
        .exam-detail-page .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 13px;
            margin: 18px 0 24px;
        }
        .exam-detail-page .stat-card {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            padding: 16px;
            border: 1px solid #e8ebf4;
            border-radius: 15px;
            background: #fff;
        }
        .exam-detail-page .stat-icon {
            display: grid;
            width: 39px;
            height: 39px;
            flex: 0 0 39px;
            place-items: center;
            border-radius: 12px;
            background: #f0efff;
            color: #5d50ff;
            font-size: 18px;
        }
        .exam-detail-page .stat-label {
            color: #8590a5;
            font-size: 10px;
            line-height: 1.4;
        }
        .exam-detail-page .stat-value {
            margin-top: 4px;
            color: #26334d;
            font-size: 14px;
            font-weight: 850;
        }
        .exam-detail-page .detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(290px, .75fr);
            gap: 20px;
            align-items: start;
        }
        .exam-detail-page .content-column { display: grid; gap: 20px; min-width: 0; }
        .exam-detail-page .detail-card {
            min-width: 0;
            padding: 22px;
            border: 1px solid #e7ebf4;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 3px 12px #24345a04;
        }
        .exam-detail-page .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 0 0 7px;
            color: #1c2843;
            font-size: 16px;
            font-weight: 850;
        }
        .exam-detail-page .section-subtitle {
            margin: 0 0 17px;
            color: #8993a8;
            font-size: 11px;
            line-height: 1.6;
        }
        .exam-detail-page .description-text {
            margin: 0;
            color: #606d85;
            font-size: 12px;
            line-height: 1.85;
        }
        .exam-detail-page .feature-list {
            display: grid;
            gap: 12px;
            margin: 17px 0 0;
            padding: 0;
            list-style: none;
        }
        .exam-detail-page .feature-list li {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            color: #526078;
            font-size: 11px;
            line-height: 1.6;
        }
        .exam-detail-page .check-dot {
            display: grid;
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            place-items: center;
            border-radius: 50%;
            background: #e7fbf2;
            color: #0d9a6b;
            font-size: 11px;
            font-weight: 900;
        }
        .exam-detail-page .part-list { display: grid; gap: 10px; }
        .exam-detail-page .part-selection-panel { display: grid; gap: 10px; margin-top: 12px; }
        .exam-detail-page .part-selection-panel[hidden] { display: none !important; }
        .exam-detail-page .mode-option {
            display: flex; align-items: flex-start; gap: 12px; padding: 15px;
            border: 1px solid #e5e9f2; border-radius: 13px; background: #fff; cursor: pointer;
            transition: border-color .15s ease, background .15s ease;
        }
        .exam-detail-page .mode-option:hover { border-color: #bdb7ff; }
        .exam-detail-page .mode-option:has(input:checked) { border-color: #8b82ff; background: #f8f7ff; }
        .exam-detail-page .mode-option input { width: 16px; height: 16px; margin-top: 2px; flex: 0 0 auto; accent-color: #5d50ff; }
        .exam-detail-page .mode-option-copy { flex: 1; min-width: 0; }
        .exam-detail-page .mode-option-title { display: block; color: #26334d; font-size: 12px; font-weight: 850; }
        .exam-detail-page .mode-option-desc { display: block; margin-top: 4px; color: #8993a8; font-size: 10px; line-height: 1.55; }
        .exam-detail-page .part-selection-heading { margin: 4px 0 2px; color: #65718a; font-size: 10px; font-weight: 850; letter-spacing: .05em; text-transform: uppercase; }
        .exam-detail-page .part-checkbox { display: flex; align-items: center; gap: 10px; padding: 11px 12px; border: 1px solid #e8ebf4; border-radius: 10px; cursor: pointer; }
        .exam-detail-page .part-checkbox:has(input:checked) { border-color: #bdb7ff; background: #faf9ff; }
        .exam-detail-page .part-checkbox input { width: 15px; height: 15px; flex: 0 0 auto; accent-color: #5d50ff; }
        .exam-detail-page .part-checkbox-copy { flex: 1; min-width: 0; }
        .exam-detail-page .part-checkbox-title { display: block; color: #26334d; font-size: 11px; font-weight: 800; }
        .exam-detail-page .part-checkbox-desc { display: block; margin-top: 3px; color: #8993a8; font-size: 10px; }
        .exam-detail-page .part-checkbox-meta { color: #6f7890; font-size: 10px; font-weight: 750; white-space: nowrap; }
        .exam-detail-page .selection-tools { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin: 2px 0; }
        .exam-detail-page .text-action { padding: 0; border: 0; background: transparent; color: #5d50ff; font-size: 10px; font-weight: 800; cursor: pointer; }
        .exam-detail-page .part-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border: 1px solid #e5e9f2;
            border-radius: 13px;
            background: #fff;
            cursor: pointer;
            transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
        }
        .exam-detail-page .part-option:hover { border-color: #bdb7ff; }
        .exam-detail-page .part-option:has(input:checked) {
            border-color: #8b82ff;
            background: #f8f7ff;
            box-shadow: 0 0 0 2px #7164ff0d;
        }
        .exam-detail-page .part-option input {
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
            accent-color: #5d50ff;
            cursor: pointer;
        }
        .exam-detail-page .part-copy { min-width: 0; flex: 1; }
        .exam-detail-page .part-name {
            display: block;
            color: #26334d;
            font-size: 12px;
            font-weight: 800;
        }
        .exam-detail-page .part-description {
            display: block;
            margin-top: 4px;
            color: #8993a8;
            font-size: 10px;
            line-height: 1.5;
        }
        .exam-detail-page .part-meta {
            flex: 0 0 auto;
            color: #6f7890;
            font-size: 10px;
            font-weight: 750;
            text-align: right;
        }
        .exam-detail-page .fulltest-option {
            background: #f7f6ff;
            border-color: #cfcaff;
        }
        .exam-detail-page .selection-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #edf0f6;
        }
        .exam-detail-page .selection-hint {
            color: #7b869c;
            font-size: 10px;
            line-height: 1.5;
        }
        .exam-detail-page .primary-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 43px;
            padding: 0 18px;
            border: 0;
            border-radius: 11px;
            color: #fff;
            background: #5d50ff;
            box-shadow: 0 3px 0 #4134d9;
            font-size: 12px;
            font-weight: 850;
            cursor: pointer;
            white-space: nowrap;
            transition: transform .15s ease, background .15s ease;
        }
        .exam-detail-page .primary-action:hover { background: #4f42f1; transform: translateY(-1px); }
        .exam-detail-page .primary-action:active { transform: translateY(1px); box-shadow: none; }
        .exam-detail-page .history-table-wrap { overflow-x: auto; }
        .exam-detail-page .history-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .exam-detail-page .history-table th {
            padding: 10px 9px;
            border-bottom: 1px solid #e8edf5;
            color: #8a94a9;
            font-size: 10px;
            font-weight: 750;
            white-space: nowrap;
        }
        .exam-detail-page .history-table td {
            padding: 13px 9px;
            border-bottom: 1px solid #edf0f6;
            color: #536078;
            font-size: 11px;
            white-space: nowrap;
        }
        .exam-detail-page .history-table tbody tr:last-child td { border-bottom: 0; }
        .exam-detail-page .history-table td:first-child { color: #27334c; font-weight: 750; }
        .exam-detail-page .score-pill {
            display: inline-block;
            padding: 5px 8px;
            border-radius: 7px;
            color: #087e55;
            background: #e9fbf2;
            font-size: 10px;
            font-weight: 850;
        }
        .exam-detail-page .history-empty {
            padding: 25px 10px;
            color: #8993a8;
            font-size: 11px;
            text-align: center;
        }
        .exam-detail-page .history-empty .empty-icon {
            margin-bottom: 8px;
            color: #a6a4ff;
            font-size: 24px;
        }
        .exam-detail-page .history-link {
            color: #5d50ff;
            font-size: 10px;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }
        .exam-detail-page .tip-box {
            padding: 14px;
            border: 1px solid #e4e3ff;
            border-radius: 12px;
            background: #f8f7ff;
            color: #656d88;
            font-size: 11px;
            line-height: 1.7;
        }
        .exam-detail-page .tip-box strong { color: #4237c9; }
        .exam-detail-page .history-filter {
            min-height: 32px;
            padding: 0 9px;
            border: 1px solid #e2e7f0;
            border-radius: 8px;
            background: #fff;
            color: #68748d;
            font-size: 10px;
        }
        @media (max-width: 850px) {
            .exam-detail-page .exam-hero { grid-template-columns: minmax(0, 1fr) 180px; padding: 22px; }
            .exam-detail-page .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .exam-detail-page .detail-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 560px) {
            .exam-detail-page .exam-hero { grid-template-columns: 1fr; padding: 20px; gap: 16px; }
            .exam-detail-page .hero-score { max-width: none; }
            .exam-detail-page .stat-grid { gap: 9px; }
            .exam-detail-page .stat-card { padding: 12px; gap: 9px; }
            .exam-detail-page .stat-icon { width: 34px; height: 34px; flex-basis: 34px; }
            .exam-detail-page .detail-card { padding: 17px 14px; }
            .exam-detail-page .part-option { align-items: flex-start; padding: 12px; }
            .exam-detail-page .part-meta { font-size: 9px; }
            .exam-detail-page .selection-footer { align-items: stretch; flex-direction: column; }
            .exam-detail-page .primary-action { width: 100%; }
            .exam-detail-page .section-heading { align-items: flex-start; }
        }

        .exam-detail-page .part-selection-panel[hidden],
        .exam-detail-page .skill-selection-panel[hidden] { display: none !important; }
        .exam-detail-page .skill-selection-panel {
            display: grid;
            gap: 10px;
            margin-top: 12px;
            padding: 14px;
            border: 1px solid #e0e7ff;
            border-radius: 12px;
            background: #f5f7ff;
        }
        .exam-detail-page .skill-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 14px;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            background: #ffffff;
            cursor: pointer;
            transition: all .15s ease;
        }
        .exam-detail-page .skill-option:hover {
            border-color: #6366f1;
            background: #faf5ff;
        }
        .exam-detail-page .skill-option.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
        .exam-confirm-overlay[hidden] { display: none !important; }
        .exam-confirm-overlay {
            position: fixed; inset: 0; z-index: 99999; display: flex;
            align-items: center; justify-content: center; padding: 20px;
            background: rgba(15, 23, 42, .58); backdrop-filter: blur(3px);
        }
        .exam-confirm-dialog {
            width: min(100%, 520px); max-height: calc(100vh - 40px); overflow-y: auto;
            padding: 24px; border: 1px solid #e2e8f0; border-radius: 18px;
            background: #fff; box-shadow: 0 24px 80px rgba(15, 23, 42, .25);
            color: #334155; outline: none;
        }
        .exam-confirm-heading { display: flex; align-items: flex-start; gap: 12px; }
        .exam-confirm-icon {
            display: grid; place-items: center; flex: 0 0 38px; height: 38px;
            border-radius: 12px; background: #fff7ed; color: #c2410c; font-weight: 900; font-size: 20px;
        }
        .exam-confirm-heading h2 { margin: 1px 0 5px; color: #17213b; font-size: 18px; line-height: 1.35; font-weight: 850; }
        .exam-confirm-heading p { margin: 0; color: #64748b; font-size: 12px; line-height: 1.5; }
        .exam-confirm-x { margin-left: auto; padding: 0 3px; border: 0; background: transparent; color: #94a3b8; font-size: 25px; line-height: 1; cursor: pointer; }
        .exam-confirm-copy { margin: 18px 0 12px; font-size: 13px; line-height: 1.75; }
        .exam-confirm-warning { margin-bottom: 14px; padding: 14px; border: 1px solid #fde68a; border-radius: 12px; background: #fffbeb; color: #92400e; font-size: 12px; line-height: 1.9; }
        .exam-confirm-note { margin: 0; color: #64748b; font-size: 11px; line-height: 1.7; }
        .exam-confirm-actions { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 10px; margin-top: 22px; padding-top: 16px; border-top: 1px solid #edf0f6; }
        .exam-confirm-cancel, .exam-confirm-start { min-height: 40px; padding: 0 15px; border-radius: 10px; font-size: 12px; font-weight: 800; cursor: pointer; }
        .exam-confirm-cancel { border: 1px solid #dbe2ec; background: #fff; color: #475569; }
        .exam-confirm-start { border: 1px solid #059669; background: #059669; color: #fff; }
        .exam-confirm-start:hover { background: #047857; }
        @media (max-width: 520px) {
            .exam-confirm-dialog { padding: 18px; }
            .exam-confirm-actions { flex-direction: column-reverse; }
            .exam-confirm-cancel, .exam-confirm-start { width: 100%; }
        }

    </style>
    @endpush

    <main class="exam-detail-page">
        <a class="back-link" href="{{ url('/thi-thu') }}">← Quay lại danh sách đề thi</a>

        @if(session('error'))
            <div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 12px; background: #fef2f2; border: 1px solid #fee2e2; color: #b91c1c; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px;">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <section class="exam-hero">
            <div class="exam-hero-main">
                <div class="exam-eyebrow"><span aria-hidden="true">●</span> {{ $definition['name'] ?? $examType }}</div>
                <h1 class="exam-title">{{ $test->title }}</h1>
                <p class="exam-summary">{{ $definition['description'] ?? 'Bài thi mô phỏng TOEIC giúp bạn đánh giá năng lực và chuẩn bị tốt nhất trước kỳ thi thật.' }}</p>
            </div>
            <div class="hero-score">
                <div class="hero-score-label">Điểm cao nhất của bạn</div>
                <div class="hero-score-value">{{ $highestScore !== null ? $highestScore : '--' }}<span style="font-size: 13px; color: #8a94a9; font-weight: 700;"> {{ $highestScore !== null ? '/ 990' : 'điểm' }}</span></div>
                <div class="hero-score-note">{{ $highestScore !== null ? 'Dựa trên kết quả các lần thi trước' : 'Chưa có lượt thi hoàn thành' }}</div>
            </div>
        </section>

        <section class="stat-grid" aria-label="Thông tin đề thi">
            <div class="stat-card">
                <div class="stat-icon" aria-hidden="true">☷</div>
                <div><div class="stat-label">Số câu hỏi có sẵn</div><div class="stat-value">{{ $totalQuestionsInTest }} câu</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" aria-hidden="true">◷</div>
                <div><div class="stat-label">Thời gian Full Test</div><div class="stat-value">{{ $totalDuration }} phút</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" aria-hidden="true">◎</div>
                <div><div class="stat-label">Độ khó</div><div class="stat-value">Chuẩn đề ETS</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" aria-hidden="true">↻</div>
                <div><div class="stat-label">Lượt đã thi</div><div class="stat-value">{{ count($examHistory) }} lần</div></div>
            </div>
        </section>

        <div class="detail-grid">
            <div class="content-column">
                <section class="detail-card">
                    <h2 class="section-heading">Thông tin đề thi</h2>
                    <p class="section-subtitle">Nắm rõ cấu trúc và mục tiêu trước khi bắt đầu.</p>
                    <p class="description-text">{{ $definition['description'] ?? 'Đề thi thực tế được lấy từ hệ thống ngân hàng đề thi StudyPrep.' }}</p>
                    <ul class="feature-list">
                        <li><span class="check-dot">✓</span><span>Cấu trúc chuẩn theo đề thi TOEIC ETS hiện hành.</span></li>
                        <li><span class="check-dot">✓</span><span>Có thể làm toàn bộ đề (Full Test) hoặc chọn riêng từng Part để luyện tập có trọng tâm.</span></li>
                        <li><span class="check-dot">✓</span><span>Hệ thống chấm điểm Server-side chính xác, lưu lịch sử và phân tích tỉ lệ đúng theo từng Part.</span></li>
                    </ul>
                </section>

                <section class="detail-card" id="exam-history">
                    <div class="section-heading">
                        <span>Lịch sử các lần thi</span>
                        <select class="history-filter" id="historyFilter" aria-label="Lọc lịch sử thi">
                            <option value="all">Tất cả lần thi</option>
                            <option value="Full Test">Full Test</option>
                            <option value="Luyện từng phần">Luyện từng phần</option>
                        </select>
                    </div>
                    <p class="section-subtitle">Các lần làm đề này của bạn.</p>

                    <div class="history-table-wrap">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Ngày thi</th>
                                    <th>Hình thức</th>
                                    <th>Thời gian</th>
                                    <th>Kết quả</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                @forelse ($examHistory as $attempt)
                                    <tr data-mode="{{ $attempt['mode'] }}">
                                        <td>{{ $attempt['date'] }}</td>
                                        <td>{{ $attempt['mode'] }}</td>
                                        <td>{{ $attempt['duration'] }}</td>
                                        <td>
                                            @if($attempt['status'] === 'Đã hoàn thành')
                                                <a href="{{ route('thi-thu.ket-qua', $attempt['id']) }}" class="score-pill" style="text-decoration:none;">{{ $attempt['score'] }}</a>
                                            @else
                                                <a href="{{ route('thi-thu.giao-dien-thi', $attempt['id']) }}" class="score-pill" style="background:#fff7ed; color:#c2410c; text-decoration:none;">Tiếp tục làm</a>
                                            @endif
                                        </td>
                                        <td>{{ $attempt['status'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding: 20px; color: #8a94a9;">
                                            Bạn chưa làm đề thi này lần nào. Hãy bấm "Bắt đầu thi" để thử sức!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <aside class="content-column">
                <section class="detail-card">
                    <h2 class="section-heading">Bắt đầu thi mới</h2>
                    <p class="section-subtitle">Chọn thi toàn bộ đề hoặc từng Part cụ thể.</p>

                    <form id="startExamForm" method="POST" action="{{ route('thi-thu.bat-dau', $test->test_form_id) }}">
                        @csrf
                        <div class="part-list">
                            <!-- Hình thức 1: Làm Full Test -->
                            <label class="mode-option">
                                <input type="radio" name="exam_mode" value="fulltest" checked>
                                <span class="mode-option-copy">
                                    <span class="mode-option-title">1. Làm Full Test</span>
                                    <span class="mode-option-desc">Thi toàn bộ đề theo cấu trúc và thời gian quy định.</span>
                                </span>
                                <span class="part-meta">{{ $totalQuestionsInTest }} câu<br>{{ $totalDuration }} phút</span>
                            </label>

                            <!-- Hình thức 2: Làm từng phần (Kỹ năng) -->
                            <label class="mode-option">
                                <input type="radio" name="exam_mode" value="skill">
                                <span class="mode-option-copy">
                                    <span class="mode-option-title">2. Làm theo kỹ năng</span>
                                    <span class="mode-option-desc">Chọn toàn bộ phần Listening (Part 1–4) hoặc Reading (Part 5–7).</span>
                                </span>
                                <span class="part-meta">Kỹ năng</span>
                            </label>

                            <!-- Hình thức 3: Luyện riêng từng Part -->
                            <label class="mode-option">
                                <input type="radio" name="exam_mode" value="selected_parts">
                                <span class="mode-option-copy">
                                    <span class="mode-option-title">3. Luyện riêng từng Part</span>
                                    <span class="mode-option-desc">Tích chọn một hoặc nhiều Part cụ thể bạn muốn làm.</span>
                                </span>
                                <span class="part-meta">Tùy chọn</span>
                            </label>
                        </div>

                        <!-- Khung chọn kỹ năng (Hình thức 2) -->
                        <div class="skill-selection-panel" id="skillSelectionPanel" hidden>
                            <span class="part-selection-heading" style="font-size: 11px; font-weight: 800; color: #1e293b;">Chọn một kỹ năng:</span>
                            
                            <label class="skill-option {{ $listeningQuestions === 0 ? 'disabled' : '' }}">
                                <span style="display: flex; align-items: center; gap: 8px;">
                                    <input type="radio" name="selected_skill" value="listening" {{ $listeningQuestions > 0 ? 'checked' : '' }} {{ $listeningQuestions === 0 ? 'disabled' : '' }}>
                                    <span style="font-weight: 750; color: #1e293b;">Listening (Part 1 – Part 4)</span>
                                </span>
                                <span style="font-size: 10px; color: #64748b; font-weight: 700;">
                                    {{ $listeningQuestions }} câu · 45 phút
                                </span>
                            </label>

                            <label class="skill-option {{ $readingQuestions === 0 ? 'disabled' : '' }}">
                                <span style="display: flex; align-items: center; gap: 8px;">
                                    <input type="radio" name="selected_skill" value="reading" {{ $listeningQuestions === 0 && $readingQuestions > 0 ? 'checked' : '' }} {{ $readingQuestions === 0 ? 'disabled' : '' }}>
                                    <span style="font-weight: 750; color: #1e293b;">Reading (Part 5 – Part 7)</span>
                                </span>
                                <span style="font-size: 10px; color: #64748b; font-weight: 700;">
                                    {{ $readingQuestions }} câu · 75 phút
                                </span>
                            </label>
                        </div>

                        <!-- Khung chọn từng Part (Hình thức 3) -->
                        <div class="part-selection-panel" id="partSelectionPanel" hidden>
                            <div class="selection-tools">
                                <span class="part-selection-heading">Chọn phần thi</span>
                                <button class="text-action" type="button" id="toggleAllParts">Chọn tất cả</button>
                            </div>

                            @foreach($parts as $part)
                                <label class="part-checkbox" style="{{ $part['actual_count'] === 0 ? 'opacity: 0.6;' : '' }}">
                                    <input type="checkbox" name="exam_parts[]" value="{{ $part['code'] }}" 
                                        data-name="{{ $part['name'] }}"
                                        data-count="{{ $part['actual_count'] }}" 
                                        data-time="{{ $part['time_minutes'] }}"
                                        {{ $part['actual_count'] > 0 ? 'checked' : '' }}>
                                    <span class="part-checkbox-copy">
                                        <span class="part-checkbox-title">{{ $part['name'] }}</span>
                                        <span class="part-checkbox-desc">{{ $part['description'] }}</span>
                                    </span>
                                    <span class="part-checkbox-meta">
                                        {{ $part['actual_count'] }} câu · {{ $part['time_minutes'] }}p
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="selection-footer">
                            <div class="selection-hint" id="selectedModeHint">Đang chọn: Full Test · {{ $totalQuestionsInTest }} câu · {{ $totalDuration }} phút</div>
                            <button type="submit" class="primary-action">Bắt đầu thi <span aria-hidden="true">→</span></button>
                        </div>
                    </form>
                </section>

                <div class="tip-box">
                    <strong>💡 Mẹo trước khi thi</strong><br>
                    Nếu muốn đo năng lực hiện tại, hãy chọn Full Test và làm trong điều kiện giống kỳ thi thật. Nếu đang ôn một kỹ năng cụ thể, hãy chọn từng phần.
                </div>
            </aside>
        </div>
    </main>

    @push('scripts')
    <script>
        (function () {
            function initExamModeSelector() {
                const form = document.getElementById('startExamForm');
                const partPanel = document.getElementById('partSelectionPanel');
                const skillPanel = document.getElementById('skillSelectionPanel');
                if (!form || !partPanel || form.dataset.modeSelectorInitialized === 'true') return;
                form.dataset.modeSelectorInitialized = 'true';

                const modeRadios = form.querySelectorAll('input[name="exam_mode"]');
                const skillRadios = form.querySelectorAll('input[name="selected_skill"]');
                const checkboxes = Array.from(form.querySelectorAll('input[name="exam_parts[]"]'));
                const toggleAllButton = document.getElementById('toggleAllParts');
                const hint = document.getElementById('selectedModeHint');

                const defaultTotalQuestions = {{ $totalQuestionsInTest }};
                const defaultTotalDuration = {{ $totalDuration }};
                const listeningQ = {{ $listeningQuestions }};
                const readingQ = {{ $readingQuestions }};

                function updateSelectedPartsHint() {
                    const checked = checkboxes.filter(function (checkbox) { return checkbox.checked; });

                    if (!checked.length) {
                        hint.textContent = 'Vui lòng chọn ít nhất một phần thi.';
                    } else {
                        let totalQ = 0;
                        let totalTime = 0;
                        const labels = checked.map(function (checkbox) {
                            totalQ += parseInt(checkbox.dataset.count || 0, 10);
                            totalTime += parseInt(checkbox.dataset.time || 15, 10);
                            return checkbox.dataset.name || checkbox.value;
                        });
                        hint.textContent = 'Đang chọn: ' + labels.join(', ') + ' · ' + totalQ + ' câu · ' + totalTime + ' phút';
                    }

                    if (toggleAllButton) {
                        toggleAllButton.textContent = checkboxes.length > 0 &&
                            checkboxes.every(function (checkbox) { return checkbox.checked; })
                            ? 'Bỏ chọn tất cả' : 'Chọn tất cả';
                    }
                }

                function updateSkillHint() {
                    const selectedSkill = form.querySelector('input[name="selected_skill"]:checked')?.value || 'listening';
                    if (selectedSkill === 'listening') {
                        hint.textContent = 'Đang chọn: Kỹ năng Listening (Part 1–4) · ' + listeningQ + ' câu · 45 phút';
                    } else {
                        hint.textContent = 'Đang chọn: Kỹ năng Reading (Part 5–7) · ' + readingQ + ' câu · 75 phút';
                    }
                }

                function updateModeUI() {
                    const selectedMode = form.querySelector('input[name="exam_mode"]:checked')?.value || 'fulltest';

                    if (selectedMode === 'selected_parts') {
                        partPanel.hidden = false;
                        partPanel.style.display = 'grid';
                        if (skillPanel) { skillPanel.hidden = true; skillPanel.style.display = 'none'; }
                        updateSelectedPartsHint();
                    } else if (selectedMode === 'skill') {
                        partPanel.hidden = true;
                        partPanel.style.display = 'none';
                        if (skillPanel) { skillPanel.hidden = false; skillPanel.style.display = 'grid'; }
                        updateSkillHint();
                    } else {
                        partPanel.hidden = true;
                        partPanel.style.display = 'none';
                        if (skillPanel) { skillPanel.hidden = true; skillPanel.style.display = 'none'; }
                        hint.textContent = 'Đang chọn: Full Test · ' + defaultTotalQuestions + ' câu · ' + defaultTotalDuration + ' phút';
                    }
                }

                modeRadios.forEach(function (radio) {
                    radio.addEventListener('change', updateModeUI);
                    radio.closest('.mode-option')?.addEventListener('click', function () {
                        radio.checked = true;
                        updateModeUI();
                    });
                });

                skillRadios.forEach(function (radio) {
                    radio.addEventListener('change', updateSkillHint);
                    radio.closest('.skill-option')?.addEventListener('click', function () {
                        if (!radio.disabled) {
                            radio.checked = true;
                            updateSkillHint();
                        }
                    });
                });

                checkboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', updateSelectedPartsHint);
                });

                toggleAllButton?.addEventListener('click', function () {
                    const allChecked = checkboxes.length > 0 &&
                        checkboxes.every(function (checkbox) { return checkbox.checked; });
                    checkboxes.forEach(function (checkbox) { checkbox.checked = !allChecked; });
                    updateSelectedPartsHint();
                });

                form.addEventListener('submit', function (event) {
                    const selectedMode = form.querySelector('input[name="exam_mode"]:checked')?.value || 'fulltest';
                    if (selectedMode === 'selected_parts' &&
                        !checkboxes.some(function (checkbox) { return checkbox.checked; })) {
                        event.preventDefault();
                        partPanel.hidden = false;
                        partPanel.style.display = 'grid';
                        hint.textContent = 'Vui lòng tích chọn ít nhất một phần đề thi trước khi bắt đầu.';
                        partPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });

                // History filter handler
                const historyFilter = document.getElementById('historyFilter');
                if (historyFilter) {
                    historyFilter.addEventListener('change', function () {
                        const val = this.value;
                        const rows = document.querySelectorAll('#historyTableBody tr[data-mode]');
                        rows.forEach(function (row) {
                            if (val === 'all' || row.getAttribute('data-mode') === val) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    });
                }

                updateModeUI();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initExamModeSelector, { once: true });
            } else {
                initExamModeSelector();
            }
        })();
    </script>
    @endpush
</x-layouts.app>
