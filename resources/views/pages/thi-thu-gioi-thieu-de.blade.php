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

        .exam-detail-page .part-selection-panel[hidden] { display: none !important; }
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

    @php
        /*
         * Dữ liệu dưới đây là dữ liệu minh họa để hoàn thiện giao diện.
         * Khi tích hợp backend, thay bằng thông tin đề thi và lịch sử của người dùng.
         */
        $exam = [
            'code' => 'TOEIC-LR-01',
            'category' => 'TOEIC LISTENING & READING',
            'title' => 'TOEIC Listening & Reading Full Test #01',
            'description' => 'Bài thi mô phỏng TOEIC Listening & Reading giúp bạn đánh giá năng lực nghe và đọc tiếng Anh trong điều kiện gần với bài thi thực tế. Bạn có thể làm toàn bộ đề trong một lượt hoặc chọn riêng từng phần để luyện tập có mục tiêu.',
            'questions' => 200,
            'duration' => 120,
            'level' => 'Trung cấp – Nâng cao',
            'attempts' => 3,
        ];
        $examHistory = [
            ['date' => '18/10/2026 20:15', 'mode' => 'Full Test', 'duration' => '112 phút', 'score' => '785/990', 'status' => 'Đã hoàn thành'],
            ['date' => '12/10/2026 19:40', 'mode' => 'Part 5 – 6', 'duration' => '24 phút', 'score' => '82%', 'status' => 'Đã hoàn thành'],
            ['date' => '05/10/2026 21:05', 'mode' => 'Full Test', 'duration' => '120 phút', 'score' => '710/990', 'status' => 'Đã hoàn thành'],
        ];
    @endphp

    <main class="exam-detail-page">
        <a class="back-link" href="{{ url('/thi-thu') }}">← Quay lại danh sách đề thi</a>

        <section class="exam-hero">
            <div class="exam-hero-main">
                <div class="exam-eyebrow"><span aria-hidden="true">●</span> {{ $exam['category'] }}</div>
                <h1 class="exam-title">{{ $exam['title'] }}</h1>
                <p class="exam-summary">{{ $exam['description'] }}</p>
            </div>
            <div class="hero-score">
                <div class="hero-score-label">Điểm cao nhất của bạn</div>
                <div class="hero-score-value">785<span style="font-size: 13px; color: #8a94a9; font-weight: 700;"> / 990</span></div>
                <div class="hero-score-note">Dựa trên lịch sử làm đề minh họa</div>
            </div>
        </section>

        <section class="stat-grid" aria-label="Thông tin đề thi">
            <div class="stat-card">
                <div class="stat-icon" aria-hidden="true">☷</div>
                <div><div class="stat-label">Số câu hỏi</div><div class="stat-value">{{ $exam['questions'] }} câu</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" aria-hidden="true">◷</div>
                <div><div class="stat-label">Thời gian</div><div class="stat-value">{{ $exam['duration'] }} phút</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" aria-hidden="true">◎</div>
                <div><div class="stat-label">Độ khó</div><div class="stat-value">{{ $exam['level'] }}</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" aria-hidden="true">↻</div>
                <div><div class="stat-label">Lượt đã thi</div><div class="stat-value">{{ $exam['attempts'] }} lần</div></div>
            </div>
        </section>

        <div class="detail-grid">
            <div class="content-column">
                <section class="detail-card">
                    <h2 class="section-heading">Thông tin đề thi</h2>
                    <p class="section-subtitle">Nắm rõ cấu trúc và mục tiêu trước khi bắt đầu.</p>
                    <p class="description-text">{{ $exam['description'] }}</p>
                    <ul class="feature-list">
                        <li><span class="check-dot">✓</span><span>Gồm hai kỹ năng Listening và Reading theo cấu trúc TOEIC.</span></li>
                        <li><span class="check-dot">✓</span><span>Có thể làm toàn bộ đề hoặc chọn riêng từng phần phù hợp mục tiêu luyện tập.</span></li>
                        <li><span class="check-dot">✓</span><span>Lịch sử điểm số giúp bạn theo dõi tiến bộ qua từng lần thi.</span></li>
                    </ul>
                </section>

                <section class="detail-card" id="exam-history">
                    <div class="section-heading">
                        <span>Lịch sử các lần thi</span>
                        <select class="history-filter" id="historyFilter" aria-label="Lọc lịch sử thi">
                            <option value="all">Tất cả lần thi</option>
                            <option value="Full Test">Full Test</option>
                            <option value="Part">Luyện từng phần</option>
                        </select>
                    </div>
                    <p class="section-subtitle">Các lần làm đề trước đây của bạn.</p>

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
                                @foreach ($examHistory as $attempt)
                                    <tr data-mode="{{ $attempt['mode'] }}">
                                        <td>{{ $attempt['date'] }}</td>
                                        <td>{{ $attempt['mode'] }}</td>
                                        <td>{{ $attempt['duration'] }}</td>
                                        <td><span class="score-pill">{{ $attempt['score'] }}</span></td>
                                        <td>{{ $attempt['status'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="history-empty" id="historyEmpty" hidden>
                        <div class="empty-icon">◷</div>
                        Chưa có lịch sử thi phù hợp với bộ lọc.
                    </div>
                </section>
            </div>

            <aside class="content-column">
                <section class="detail-card">
                    <h2 class="section-heading">Bắt đầu thi mới</h2>
                    <p class="section-subtitle">Chọn thi toàn bộ đề hoặc một phần cụ thể.</p>

                    <form id="startExamForm">
                        <div class="part-list">
                            <label class="mode-option">
                                <input type="radio" name="exam_mode" value="fulltest" checked>
                                <span class="mode-option-copy">
                                    <span class="mode-option-title">Làm Full Test</span>
                                    <span class="mode-option-desc">Thi toàn bộ đề theo cấu trúc và thời gian quy định.</span>
                                </span>
                                <span class="part-meta">200 câu<br>120 phút</span>
                            </label>

                            <label class="mode-option">
                                <input type="radio" name="exam_mode" value="selected_parts">
                                <span class="mode-option-copy">
                                    <span class="mode-option-title">Chọn từng phần để thi</span>
                                    <span class="mode-option-desc">Tích chọn một hoặc nhiều phần bạn muốn luyện tập.</span>
                                </span>
                                <span class="part-meta">Tùy chọn</span>
                            </label>
                        </div>

                        <!-- đối với đề listening và reading -->
                        <div class="part-selection-panel" id="partSelectionPanel" >
                            <div class="selection-tools">
                                <span class="part-selection-heading">Chọn phần thi listening reading</span>
                                <button class="text-action" type="button" id="toggleAllParts">Chọn tất cả</button>
                            </div>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part1">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 1 — Photographs</span>
                                    <span class="part-checkbox-desc">Mô tả hình ảnh</span>
                                </span>
                                <span class="part-checkbox-meta">6 câu</span>
                            </label>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part2">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 2 — Question–Response</span>
                                    <span class="part-checkbox-desc">Nghe câu hỏi và chọn phản hồi phù hợp</span>
                                </span>
                                <span class="part-checkbox-meta">25 câu</span>
                            </label>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part3">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 3 — Conversations</span>
                                    <span class="part-checkbox-desc">Nghe hội thoại</span>
                                </span>
                                <span class="part-checkbox-meta">39 câu</span>
                            </label>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part4">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 4 — Talks</span>
                                    <span class="part-checkbox-desc">Nghe bài nói ngắn</span>
                                </span>
                                <span class="part-checkbox-meta">30 câu</span>
                            </label>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part5">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 5 — Incomplete Sentences</span>
                                    <span class="part-checkbox-desc">Luyện ngữ pháp và từ vựng</span>
                                </span>
                                <span class="part-checkbox-meta">30 câu</span>
                            </label>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part6">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 6 — Text Completion</span>
                                    <span class="part-checkbox-desc">Hoàn thành đoạn văn</span>
                                </span>
                                <span class="part-checkbox-meta">16 câu</span>
                            </label>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part7">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 7 — Reading Comprehension</span>
                                    <span class="part-checkbox-desc">Đọc hiểu đoạn văn đơn, kép và ba</span>
                                </span>
                                <span class="part-checkbox-meta">54 câu</span>
                            </label>
                        </div>

                        <!-- đối với đề speaking và writing -->
                        <div class="part-selection-panel" id="partSelectionPanel" >
                            <div class="selection-tools">
                                <span class="part-selection-heading">Chọn phần thi speaking writing</span>
                                <button class="text-action" type="button" id="toggleAllParts">Chọn tất cả</button>
                            </div>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part1">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 1 — Read a Text Aloud</span>
                                    <span class="part-checkbox-desc">Đọc to một đoạn văn tiếng Anh được cung cấp</span>
                                </span>
                                <span class="part-checkbox-meta">2 câu</span>
                            </label>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part2">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 2 — Describe a Picture</span>
                                    <span class="part-checkbox-desc">Quan sát một bức ảnh và mô tả những gì đang diễn ra.</span>
                                </span>
                                <span class="part-checkbox-meta">2 câu</span>
                            </label>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part3">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 3 — Respond to Questions</span>
                                    <span class="part-checkbox-desc">Trả lời các câu hỏi về một chủ đề quen thuộc, thường liên quan đến lịch trình, sở thích hoặc hoạt động hằng ngày.</span>
                                </span>
                                <span class="part-checkbox-meta">3 câu</span>
                            </label>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part4">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 4 — Respond to Questions Using Information Provided</span>
                                    <span class="part-checkbox-desc">Đọc bảng lịch trình, thông báo hoặc thông tin sự kiện, sau đó trả lời câu hỏi dựa trên dữ liệu được cho.</span>
                                </span>
                                <span class="part-checkbox-meta">3 câu</span>
                            </label>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part5">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 5 — Express an opinion</span>
                                    <span class="part-checkbox-desc">Bày tỏ quan điểm cá nhân về một chủ đề</span>
                                </span>
                                <span class="part-checkbox-meta">1 câu</span>
                            </label>

                            <!-- writing -->
                             <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part1">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 1 — Write a Sentence Based on a Picture</span>
                                    <span class="part-checkbox-desc">Viết một câu mô tả bức tranh, sử dụng hai từ hoặc cụm từ cho sẵn. Có thể thay đổi dạng từ và thứ tự sử dụng.</span>
                                </span>
                                <span class="part-checkbox-meta">5 câu</span>
                            </label>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part2">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 2 — Respond to a Written Request</span>
                                    <span class="part-checkbox-desc">Đọc email hoặc yêu cầu bằng văn bản, sau đó viết email phản hồi phù hợp với thông tin và các yêu cầu được đưa ra.</span>
                                </span>
                                <span class="part-checkbox-meta">2 câu</span>
                            </label>

                            <label class="part-checkbox">
                                <input type="checkbox" name="exam_parts[]" value="part3">
                                <span class="part-checkbox-copy">
                                    <span class="part-checkbox-title">Part 3 — Write an Opinion Essay</span>
                                    <span class="part-checkbox-desc">Trình bày quan điểm về một vấn đề, sau đó đưa ra lý do và ví dụ để bảo vệ quan điểm đó.</span>
                                </span>
                                <span class="part-checkbox-meta">1 câu</span>
                            </label>
                        </div>


                        <div class="selection-footer">
                            <div class="selection-hint" id="selectedModeHint">Đang chọn: Full Test · 200 câu · 120 phút</div>
                            <a href="{{ url('/thi-thu/giao-dien-thi') }}"><button type="button" class="primary-action">Bắt đầu thi <span aria-hidden="true">→</span></button></a>
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


    <!-- <x-modal id="confirm-test-modal" title="Xác nhận bắt đầu bài thi thử" maxWidth="520px">
        <p style="margin: 0 0 12px; font-size: 14px; color: #334155; line-height: 1.7;">
            Bạn chuẩn bị bắt đầu bài thi thử. Khi chọn <strong>“Vào thi &amp; Toàn màn hình”</strong>, hệ thống sẽ chuyển sang giao diện làm bài.
        </p>
        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 14px; margin-bottom: 16px; font-size: 13px; color: #92400e; line-height: 1.7;">
            ⚠️ Một lượt thi sẽ được sử dụng.<br>
            ⚠️ Trình duyệt sẽ yêu cầu chế độ toàn màn hình.<br>
            ⚠️ Hệ thống có thể ghi nhận khi bạn rời khỏi cửa sổ thi.
        </div>
        <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.6;">
            Đảm bảo bạn đã sẵn sàng trước khi bắt đầu. Hiện tại thao tác khởi tạo lượt thi cần được tích hợp với backend.
        </p>
        <x-slot:actions>
            <x-button variant="neutral" data-sp-modal-close>Huỷ bỏ</x-button>
            <x-button variant="success" onclick="startFullscreenExam()">Vào thi &amp; Toàn màn hình</x-button>
        </x-slot:actions>
    </x-modal> -->

    @push('scripts')
    <!-- <script>
        (function () {
            function initExamModeSelector() {
                const form = document.getElementById('startExamForm');
                const panel = document.getElementById('partSelectionPanel');
                if (!form || !panel || form.dataset.modeSelectorInitialized === 'true') return;
                form.dataset.modeSelectorInitialized = 'true';

                const modeRadios = form.querySelectorAll('input[name="exam_mode"]');
                const checkboxes = Array.from(form.querySelectorAll('input[name="exam_parts[]"]'));
                const toggleAllButton = document.getElementById('toggleAllParts');
                const hint = document.getElementById('selectedModeHint');

                function updateSelectedPartsHint() {
                    const checked = checkboxes.filter(function (checkbox) { return checkbox.checked; });

                    if (!checked.length) {
                        hint.textContent = 'Vui lòng chọn ít nhất một phần thi.';
                    } else {
                        const labels = checked.map(function (checkbox) {
                            const title = checkbox.closest('.part-checkbox')?.querySelector('.part-checkbox-title');
                            return title ? title.textContent.trim().split(' — ')[0] : checkbox.value;
                        });
                        hint.textContent = 'Đã chọn: ' + labels.join(', ') + ' · ' + checked.length + ' phần';
                    }

                    if (toggleAllButton) {
                        toggleAllButton.textContent = checkboxes.length > 0 &&
                            checkboxes.every(function (checkbox) { return checkbox.checked; })
                            ? 'Bỏ chọn tất cả' : 'Chọn tất cả';
                    }
                }

                function updateModeUI() {
                    const selectedMode = form.querySelector('input[name="exam_mode"]:checked')?.value || 'fulltest';
                    const isSelectedParts = selectedMode === 'selected_parts';

                    // Dùng hidden để ẩn/hiện panel; không phụ thuộc CSS display bên ngoài.
                    panel.hidden = !isSelectedParts;
                    panel.style.display = isSelectedParts ? 'grid' : 'none';

                    if (isSelectedParts) {
                        updateSelectedPartsHint();
                    } else {
                        hint.textContent = 'Đang chọn: Full Test · 200 câu · 120 phút';
                    }
                }

                modeRadios.forEach(function (radio) {
                    radio.addEventListener('change', updateModeUI);
                    // Hỗ trợ trường hợp click vào nhãn/card trên một số cấu trúc CSS.
                    radio.closest('.mode-option')?.addEventListener('click', function () {
                        radio.checked = true;
                        updateModeUI();
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
                        panel.hidden = false;
                        panel.style.display = 'grid';
                        hint.textContent = 'Vui lòng tích chọn ít nhất một phần đề thi trước khi bắt đầu.';
                        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });

                updateModeUI();
            }

            // Khởi tạo dù script được đưa vào trang trước hay sau sự kiện DOMContentLoaded.
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initExamModeSelector, { once: true });
            } else {
                initExamModeSelector();
            }
        })();

        function startFullscreenExam() {
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen().catch(function () {
                    console.warn('Không thể bật chế độ toàn màn hình.');
                });
            }
            alert('Giao diện xác nhận đã hoàn tất. Cần tích hợp backend để trừ lượt và khởi tạo bài thi thực tế.');
        }
    </script> -->
    @endpush
</x-layouts.app>
