<x-layouts.app active="thi-thu" title="StudyPrep - Thi thử TOEIC & Kiểm tra đầu vào">
@push('styles')
<style>
    /* Keep this page centered inside the content area provided by the app layout. */
    .sp-content-wrapper { min-width: 0; }
    .toeic-page {
        width: 100%;
        max-width: 1240px;
        margin-left: auto;
        margin-right: auto;
    }
    .toeic-page {
        --toeic-primary: #514bff;
        --toeic-primary-dark: #4038e8;
        --toeic-ink: #17213b;
        --toeic-muted: #69758d;
        --toeic-line: #e7ebf3;
        color: var(--toeic-ink);
        padding: 8px 0 36px;
    }
    .toeic-page * { box-sizing: border-box; }
    .toeic-page .toeic-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 30px;
        margin-bottom: 22px;
        border: 1px solid #e5e8ff;
        border-radius: 22px;
        background: linear-gradient(120deg, #f4f5ff 0%, #fbfcff 62%, #edf8ff 100%);
    }
    .toeic-page .toeic-hero::after {
        content: "";
        position: absolute;
        width: 210px;
        height: 210px;
        right: -68px;
        top: -105px;
        border: 28px solid #514bff0c;
        border-radius: 50%;
        pointer-events: none;
    }
    .toeic-page .hero-copy { position: relative; z-index: 1; max-width: 680px; }
    .toeic-page .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 12px;
        color: var(--toeic-primary);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .toeic-page .eyebrow-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .toeic-page .toeic-title { margin: 0; color: var(--toeic-ink); font-size: clamp(25px, 3vw, 34px); line-height: 1.2; font-weight: 850; letter-spacing: -.035em; }
    .toeic-page .toeic-description { max-width: 600px; margin: 12px 0 0; color: var(--toeic-muted); font-size: 14px; line-height: 1.7; }
    .toeic-page .hero-stat {
        position: relative;
        z-index: 1;
        flex: 0 0 190px;
        padding: 18px;
        border: 1px solid #ffffffd9;
        border-radius: 17px;
        background: #ffffffc9;
        box-shadow: 0 8px 24px #313c7610;
    }
    .toeic-page .hero-stat-label { color: var(--toeic-muted); font-size: 12px; font-weight: 700; }
    .toeic-page .hero-stat-value { margin-top: 5px; font-size: 29px; line-height: 1.2; font-weight: 850; letter-spacing: -.04em; }
    .toeic-page .hero-stat-value span { color: #8b94a9; font-size: 14px; font-weight: 700; letter-spacing: 0; }
    .toeic-page .hero-stat-note { margin: 6px 0 0; color: var(--toeic-muted); font-size: 11px; line-height: 1.5; }
    .toeic-page .info-grid { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(240px, .8fr); gap: 18px; margin-bottom: 30px; }
    .toeic-page .info-card { min-width: 0; padding: 22px; border: 1px solid var(--toeic-line); border-radius: 18px; background: #fff; }
    .toeic-page .card-heading { display: flex; align-items: center; gap: 10px; margin: 0 0 16px; font-size: 15px; font-weight: 800; }
    .toeic-page .heading-icon { display: grid; width: 35px; height: 35px; flex: 0 0 35px; place-items: center; border-radius: 11px; background: #f0efff; color: var(--toeic-primary); font-size: 17px; }
    .toeic-page .rule-list { display: grid; gap: 12px; margin: 0; padding: 0; list-style: none; }
    .toeic-page .rule-list li { display: grid; grid-template-columns: 20px minmax(0, 1fr); gap: 9px; color: #536078; font-size: 12px; line-height: 1.65; }
    .toeic-page .rule-check { display: grid; width: 19px; height: 19px; place-items: center; margin-top: 1px; border-radius: 50%; background: #eefbf4; color: #168454; font-size: 11px; font-weight: 900; }
    .toeic-page .credit-card { display: flex; flex-direction: column; justify-content: space-between; border: 0; color: #fff; background: linear-gradient(145deg, #168f70 0%, #087e68 100%); }
    .toeic-page .credit-card .card-heading { color: #fff; }
    .toeic-page .credit-card .heading-icon { color: #fff; background: #ffffff25; }
    .toeic-page .credit-label { color: #d5fff0; font-size: 12px; font-weight: 700; }
    .toeic-page .credit-number { margin: 7px 0 3px; font-size: 38px; font-weight: 850; letter-spacing: -.04em; line-height: 1.1; }
    .toeic-page .credit-number span { font-size: 15px; font-weight: 650; opacity: .8; letter-spacing: 0; }
    .toeic-page .credit-note { margin: 0 0 20px; color: #d5fff0; font-size: 11px; line-height: 1.6; }
    .toeic-page .credit-link { display: inline-flex; align-items: center; justify-content: center; min-height: 38px; padding: 0 14px; border: 1px solid #ffffff50; border-radius: 10px; color: #087e68; background: #fff; font-size: 12px; font-weight: 800; text-decoration: none; transition: transform .18s, box-shadow .18s; }
    .toeic-page .credit-link:hover { transform: translateY(-1px); box-shadow: 0 6px 14px #004c3a20; }
    .toeic-page .list-heading-row { display: flex; align-items: end; justify-content: space-between; gap: 16px; margin-bottom: 15px; }
    .toeic-page .list-title { margin: 0; color: var(--toeic-ink); font-size: 21px; font-weight: 850; letter-spacing: -.025em; }
    .toeic-page .list-subtitle { margin: 6px 0 0; color: var(--toeic-muted); font-size: 12px; line-height: 1.5; }
    .toeic-page .exam-list { display: grid; gap: 18px; }
    .toeic-page .exam-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 19px;
        border: 1px solid var(--toeic-line);
        border-radius: 16px;
        background: #fff;
        transition: border-color .18s, box-shadow .18s, transform .18s;
    }
    .toeic-page .exam-row:hover { border-color: #d4d7ff; box-shadow: 0 7px 24px #2e3a6c0b; transform: translateY(-1px); }
    .toeic-page .exam-main { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .toeic-page .exam-icon { display: grid; width: 50px; height: 50px; flex: 0 0 50px; place-items: center; border-radius: 14px; font-size: 23px; }
    .toeic-page .exam-icon--blue { color: #2470e8; background: #edf4ff; }
    .toeic-page .exam-icon--amber { color: #c47b12; background: #fff5df; }
    .toeic-page .exam-icon--rose { color: #cf4e62; background: #fff0f2; }
    .toeic-page .exam-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 6px; }
    .toeic-page .exam-tag { display: inline-flex; align-items: center; padding: 5px 8px; border-radius: 7px; background: #efefff; color: #514bff; font-size: 10px; font-weight: 850; }
    .toeic-page .exam-tag--amber { color: #a96506; background: #fff5df; }
    .toeic-page .exam-tag--rose { color: #bd4358; background: #fff0f2; }
    .toeic-page .exam-note { color: #8a94a8; font-size: 11px; font-weight: 650; }
    .toeic-page .exam-name { margin: 0; color: var(--toeic-ink); font-size: 14px; font-weight: 800; line-height: 1.45; }
    .toeic-page .exam-details { margin: 6px 0 0; color: var(--toeic-muted); font-size: 11px; line-height: 1.6; }
    .toeic-page .exam-action { flex: 0 0 auto; }
    .toeic-page .exam-action .sp-button, .toeic-page .exam-action button { white-space: nowrap; }
    .toeic-page .security-note { display: flex; align-items: flex-start; gap: 10px; margin-top: 17px; padding: 13px 15px; border: 1px solid #f4e5bd; border-radius: 12px; background: #fffbef; color: #875b12; font-size: 11px; line-height: 1.6; }
    .toeic-page .security-note strong { color: #754b08; }
    .toeic-page .exam-sublist-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 0; border-top:1px solid #edf0f6; }
    .toeic-page .exam-sublist-label { color:var(--toeic-muted); font-size:11px; font-weight:750; text-transform:uppercase; letter-spacing:.04em; }
    .toeic-page .create-exam-btn { display:inline-flex; align-items:center; justify-content:center; gap:6px; min-height:34px; padding:0 12px; border:1px solid #dcdcff; border-radius:9px; background:#f5f4ff; color:var(--toeic-primary); font-size:11px; font-weight:800; cursor:pointer; white-space:nowrap; }
    .toeic-page .create-exam-btn:hover { background:#eae9ff; border-color:#bcbaff; }
    .toeic-page .exam-sublist-toolbar + .exam-subitem { border-top:0; }
    .toeic-page .create-modal-backdrop { position:fixed; inset:0; z-index:10000; display:grid; place-items:center; padding:20px; background:#11182b80; }
    .toeic-page .create-modal-backdrop[hidden] { display:none; }
    .toeic-page .create-modal { width:min(100%,480px); padding:24px; border-radius:18px; background:#fff; box-shadow:0 24px 70px #11182b35; }
    .toeic-page .create-modal h3 { margin:0 0 6px; color:var(--toeic-ink); font-size:19px; }
    .toeic-page .create-modal p { margin:0 0 18px; color:var(--toeic-muted); font-size:12px; line-height:1.6; }
    .toeic-page .create-modal label { display:block; margin-bottom:7px; color:#47516a; font-size:12px; font-weight:750; }
    .toeic-page .create-modal input, .toeic-page .create-modal select { width:100%; min-height:42px; padding:10px 12px; border:1px solid #dfe4ef; border-radius:10px; background:#fff; color:var(--toeic-ink); font:inherit; font-size:13px; }
    .toeic-page .create-modal input:focus, .toeic-page .create-modal select:focus { outline:3px solid #514bff20; border-color:#8e89ff; }
    .toeic-page .create-modal-actions { display:flex; justify-content:flex-end; gap:9px; margin-top:20px; }
    .toeic-page .create-modal-cancel { min-height:38px; padding:0 14px; border:1px solid #dfe4ef; border-radius:9px; background:#fff; color:#59647c; font-size:12px; font-weight:750; cursor:pointer; }
    .toeic-page .create-modal-submit { min-height:38px; padding:0 14px; border:0; border-radius:9px; background:var(--toeic-primary); color:#fff; font-size:12px; font-weight:800; cursor:pointer; }
    .toeic-page .exam-category { overflow: hidden; border: 1px solid var(--toeic-line); border-radius: 16px; background: #fff; transition: border-color .18s, box-shadow .18s; }
    .toeic-page .exam-category:has(.exam-category-toggle[aria-expanded="true"]) { border-color: #c9c8ff; box-shadow: 0 8px 24px #514bff0d; }
    .toeic-page .exam-category-toggle { display: flex; width: 100%; min-height: 98px; align-items: center; justify-content: space-between; gap: 18px; padding: 20px; border: 0; background: #fff; color: inherit; text-align: left; cursor: pointer; font: inherit; }
    .toeic-page .exam-category-toggle:hover { background: #fafaff; }
    .toeic-page .exam-category-toggle .exam-main { flex: 1 1 auto; }
    .toeic-page .exam-category-toggle .exam-name { display: block; }
    .toeic-page .exam-category-toggle .exam-details { display: block; }
    .toeic-page .exam-category-toggle:focus-visible { outline: 3px solid #514bff45; outline-offset: -3px; }
    .toeic-page .exam-category-copy { display: block; min-width: 0; }
    .toeic-page .exam-category-end { display: flex; flex: 0 0 auto; align-items: center; gap: 13px; }
    .toeic-page .exam-count { padding: 6px 9px; border-radius: 8px; background: #f3f4fa; color: #66718a; font-size: 11px; font-weight: 750; white-space: nowrap; }
    .toeic-page .exam-chevron { display: grid; width: 30px; height: 30px; place-items: center; border-radius: 9px; background: #f3f4ff; color: var(--toeic-primary); font-size: 22px; line-height: 1; transition: transform .2s; }
    .toeic-page .exam-category-toggle[aria-expanded="true"] .exam-chevron { transform: rotate(180deg); }
    .toeic-page .exam-sublist { padding: 0 20px 14px 83px; background: #fff; }
    .toeic-page .exam-sublist-toolbar { margin-bottom: 2px; }
    .toeic-page .exam-subitem { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px 0; border-top:1px solid #edf0f6; }
    .toeic-page .exam-subitem-copy { display:flex; flex-direction:column; gap:6px; min-width:0; }
    .toeic-page .exam-subitem-title { color:var(--toeic-ink); font-size:13px; font-weight:800; line-height:1.5; }
    .toeic-page .exam-subitem-details { color:var(--toeic-muted); font-size:11px; line-height:1.6; }
    .toeic-page .exam-sublist[hidden] { display: none; }
    .toeic-page .exam-subitem { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 15px 0; border-top: 1px solid #edf0f6; }
    .toeic-page .exam-subitem-copy { display: grid; min-width: 0; gap: 6px; }
    .toeic-page .exam-subitem-title { color: var(--toeic-ink); font-size: 13px; font-weight: 750; line-height: 1.5; }
    .toeic-page .exam-subitem-details { color: var(--toeic-muted); font-size: 11px; line-height: 1.6; }
    .toeic-page .exam-subitem > *:last-child { flex: 0 0 auto; }

    /* Nút bắt đầu bài thi */
    .toeic-page .exam-subitem > a[href*="thi-thu/gioi-thieu-de"] {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-width: 132px;
        min-height: 42px;
        padding: 0 17px;
        border: 1px solid var(--toeic-primary);
        border-radius: 11px;
        background: linear-gradient(135deg, #625cff 0%, #4941ed 100%);
        color: #fff;
        box-shadow: 0 4px 10px #514bff2b, inset 0 1px 0 #ffffff30;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        text-decoration: none;
        white-space: nowrap;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .toeic-page .exam-subitem > a[href*="thi-thu/gioi-thieu-de"]::after {
        content: "→";
        font-size: 16px;
        line-height: 1;
        transition: transform .18s ease;
    }
    .toeic-page .exam-subitem > a[href*="thi-thu/gioi-thieu-de"]:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #716bff 0%, #5148f5 100%);
        box-shadow: 0 7px 16px #514bff3b;
    }
    .toeic-page .exam-subitem > a[href*="thi-thu/gioi-thieu-de"]:hover::after {
        transform: translateX(3px);
    }
    .toeic-page .exam-subitem > a[href*="thi-thu/gioi-thieu-de"]:focus-visible {
        outline: 3px solid #514bff40;
        outline-offset: 3px;
    }
    .toeic-page .exam-subitem > a[href*="thi-thu/gioi-thieu-de"]:active {
        transform: translateY(0);
        box-shadow: 0 2px 5px #514bff25;
    }
    @media (max-width: 850px) {
        .toeic-page .toeic-hero { padding: 24px; }
        .toeic-page .info-grid { grid-template-columns: minmax(0, 1fr) minmax(220px, .75fr); }
        .toeic-page .exam-row { align-items: flex-start; }
    }
    @media (max-width: 640px) {
        .toeic-page { padding-top: 0; }
        .toeic-page .toeic-hero { align-items: stretch; flex-direction: column; gap: 18px; padding: 22px 18px; border-radius: 17px; }
        .toeic-page .hero-stat { flex-basis: auto; width: 100%; }
        .toeic-page .info-grid { grid-template-columns: 1fr; gap: 12px; margin-bottom: 25px; }
        .toeic-page .info-card { padding: 18px; border-radius: 15px; }
        .toeic-page .list-title { font-size: 19px; }
        .toeic-page .exam-row { flex-direction: column; gap: 15px; padding: 16px; }
        .toeic-page .exam-main { align-items: flex-start; gap: 11px; }
        .toeic-page .exam-icon { width: 43px; height: 43px; flex-basis: 43px; border-radius: 12px; font-size: 20px; }
        .toeic-page .exam-name { font-size: 13px; }
        .toeic-page .exam-action { width: 100%; }
        .toeic-page .exam-action > * { width: 100%; justify-content: center; }
        .toeic-page .exam-meta { gap: 6px; }
        .toeic-page .security-note { margin-top: 13px; }
        .toeic-page .exam-category-toggle { gap: 10px; padding: 15px 13px; }
        .toeic-page .exam-category-toggle .exam-main { gap: 10px; }
        .toeic-page .exam-category-end { gap: 7px; }
        .toeic-page .exam-count { padding: 5px 7px; font-size: 10px; }
        .toeic-page .exam-chevron { width: 27px; height: 27px; flex: 0 0 27px; }
        .toeic-page .exam-sublist { padding: 0 13px 8px 13px; }
        .toeic-page .exam-subitem { align-items: stretch; flex-direction: column; gap: 11px; }
        .toeic-page .exam-subitem > *:last-child { width: 100%; justify-content: center; }
    }
</style>
@endpush

<div class="toeic-page">
    <section class="toeic-hero">
        <div class="hero-copy">
            <div class="eyebrow"><span class="eyebrow-dot"></span> StudyPrep · Exam Center</div>
            <h1 class="toeic-title">Thi thử TOEIC &amp;<br>Kiểm tra đầu vào</h1>
            <p class="toeic-description">Luyện tập trong môi trường mô phỏng phòng thi, theo dõi kết quả và khám phá kỹ năng cần cải thiện trước kỳ thi thật.</p>
        </div>
        <div class="hero-stat">
            <div class="hero-stat-label">Lượt thi khả dụng</div>
            <div class="hero-stat-value">5 <span>/ 5 lượt</span></div>
            <p class="hero-stat-note">Mỗi lượt được sử dụng khi bạn bắt đầu một bài thi.</p>
        </div>
    </section>

    <section class="info-grid" aria-label="Thông tin thi thử">
        <div class="info-card">
            <h2 class="card-heading"><span class="heading-icon">ⓘ</span> Quy chế thi thử</h2>
            <ul class="rule-list">
                <li><span class="rule-check">✓</span><span>Mỗi lần bắt đầu bài thi sẽ <strong>trừ 1 lượt</strong>; lượt thi không được hoàn lại nếu thoát giữa chừng.</span></li>
                <li><span class="rule-check">✓</span><span>Bật <strong>chế độ toàn màn hình</strong> trong suốt thời gian làm bài.</span></li>
                <li><span class="rule-check">✓</span><span>Hệ thống có thể ghi nhận cảnh báo khi bạn rời khỏi cửa sổ thi hoặc chuyển sang tab khác.</span></li>
                <li><span class="rule-check">✓</span><span>Không sao chép nội dung câu hỏi trong quá trình làm bài.</span></li>
            </ul>
        </div>
        <aside class="info-card credit-card">
            <div>
                <h2 class="card-heading"><span class="heading-icon">↗</span> Nạp lại lượt thi</h2>
                <div class="credit-label">Luyện tập để nhận thêm lượt</div>
                <div class="credit-number">+1 <span>lượt thi</span></div>
                <p class="credit-note">Hoàn thành hoạt động Flashcard để rèn từ vựng và nhận thêm lượt thi.</p>
            </div>
            <div><a class="credit-link" href="{{ url('/tu-vung') }}">Học Flashcard ngay <span aria-hidden="true">&nbsp;→</span></a></div>
        </aside>
    </section>

    <section aria-labelledby="exam-list-title">
        <div class="list-heading-row">
            <div>
                <h2 class="list-title" id="exam-list-title">Các loại đề thi</h2>
                <p class="list-subtitle">Chọn dạng bài thi phù hợp với mục tiêu luyện tập của bạn.</p>
            </div>
            <div class="exam-sublist-toolbar"><button class="create-exam-btn" type="button" data-create-exam data-exam-type="Kiểm tra đầu vào">＋ Tạo đề mới</button></div>

        </div>

        <div class="exam-list exam-category-list">
            {{-- Loại đề: Placement Test --}}
            <article class="exam-category">
                <button class="exam-category-toggle" type="button" aria-expanded="true" aria-controls="placement-tests">
                    <span class="exam-main">
                        <span class="exam-icon exam-icon--amber" aria-hidden="true">📊</span>
                        <span class="exam-category-copy">
                            <span class="exam-meta"><span class="exam-tag exam-tag--amber">PLACEMENT TEST</span><span class="exam-note">Khảo sát trình độ</span></span>
                            <span class="exam-name">Kiểm tra đầu vào</span>
                            <span class="exam-details">Đánh giá nhanh trình độ hiện tại của bạn</span>
                        </span>
                    </span>
                    <span class="exam-category-end"><span class="exam-count">1 bài thi</span><span class="exam-chevron" aria-hidden="true">⌄</span></span>
                </button>
                <div class="exam-sublist" id="placement-tests">
                    <div class="exam-subitem">
                        <div class="exam-subitem-copy">
                            <span class="exam-subitem-title">Mini Placement Test – Đánh giá năng lực nhanh</span>
                            <span class="exam-subitem-details">50 câu hỏi · 45 phút · Ước lượng trình độ hiện tại</span>
                        </div>
                        <a  href="{{ url('/thi-thu/gioi-thieu-de') }}">Bắt đầu</a>
                        <!-- <x-button variant="warning" size="medium" data-sp-modal-open="confirm-test-modal">Kiểm tra ngay →</x-button> -->
                    </div>
                </div>
            </article>

            {{-- Loại đề: TOEIC Listening & Reading --}}
            <article class="exam-category">
                <button class="exam-category-toggle" type="button" aria-expanded="true" aria-controls="listening-reading-tests">
                    <span class="exam-main">
                        <span class="exam-icon exam-icon--blue" aria-hidden="true">🏆</span>
                        <span class="exam-category-copy">
                            <span class="exam-meta"><span class="exam-tag">Luyện thi TOEIC chuẩn đề</span></span>
                            <span class="exam-name">TOEIC Listening &amp; Reading</span>
                            <span class="exam-details">Luyện đủ 2 kỹ năng Nghe và Đọc · 120 phút</span>
                        </span>
                    </span>
                    <span class="exam-category-end"><span class="exam-count">2 bài thi</span><span class="exam-chevron" aria-hidden="true">⌄</span></span>
                </button>
                <div class="exam-sublist" id="listening-reading-tests">
                    <div class="exam-subitem">
                        <div class="exam-subitem-copy">
                            <span class="exam-subitem-title">TOEIC Listening &amp; Reading #01</span>
                            <span class="exam-subitem-details">200 câu hỏi · Part 1–7 · 120 phút · Đề tuyển chọn 2026</span>
                        </div>
                        <!-- <x-button variant="primary" size="medium" data-sp-modal-open="confirm-test-modal">Bắt đầu thi →</x-button> -->
                        <a  href="{{ url('/thi-thu/gioi-thieu-de') }}">Bắt đầu</a>
                    </div>
                    <div class="exam-subitem">
                        <div class="exam-subitem-copy">
                            <span class="exam-subitem-title">TOEIC Listening &amp; Reading #02</span>
                            <span class="exam-subitem-details">200 câu hỏi · Part 1–7 · 120 phút · Bộ đề ETS</span>
                        </div>
                        <!-- <x-button variant="primary" size="medium" data-sp-modal-open="confirm-test-modal">Bắt đầu thi →</x-button> -->
                        <a  href="{{ url('/thi-thu/gioi-thieu-de') }}">Bắt đầu</a>
                    </div>
                </div>
            </article>

            {{-- Loại đề: Speaking & Writing --}}
            <article class="exam-category">
                <button class="exam-category-toggle" type="button" aria-expanded="true" aria-controls="speaking-writing-tests">
                    <span class="exam-main">
                        <span class="exam-icon exam-icon--rose" aria-hidden="true">🎙️</span>
                        <span class="exam-category-copy">
                            <span class="exam-meta"><span class="exam-tag exam-tag--rose">SPEAKING &amp; WRITING</span><span class="exam-note">Kỹ năng nâng cao</span></span>
                            <span class="exam-name">TOEIC Speaking &amp; Writing</span>
                            <span class="exam-details">Luyện nói và viết · Hỗ trợ chấm điểm AI</span>
                        </span>
                    </span>
                    <span class="exam-category-end"><span class="exam-count">1 bài thi</span><span class="exam-chevron" aria-hidden="true">⌄</span></span>
                </button>
                <div class="exam-sublist" id="speaking-writing-tests">
                    <div class="exam-subitem">
                        <div class="exam-subitem-copy">
                            <span class="exam-subitem-title">TOEIC Speaking &amp; Writing Official Test #01</span>
                            <span class="exam-subitem-details">19 câu · 11 Speaking + 8 Writing · 80 phút · Chấm điểm AI</span>
                        </div>
                        <!-- <x-button variant="neutral" size="medium" data-sp-modal-open="confirm-test-modal">Bắt đầu thi →</x-button> -->
                        <a  href="{{ url('/thi-thu/gioi-thieu-de') }}">Bắt đầu</a>
                    </div>
                </div>
            </article>
        </div>

        <div class="security-note">
            <span aria-hidden="true">⚠️</span>
            <span><strong>Lưu ý trước khi thi:</strong> Hãy chuẩn bị đủ thời gian và kết nối Internet ổn định. Việc trừ lượt, ghi nhận vi phạm và chấm điểm thực tế cần được kết nối với backend để phản ánh chính xác trạng thái tài khoản.</span>
        </div>
    </section>
</div>

<div class="toeic-page">
    <div class="create-modal-backdrop" id="createExamModal" hidden>
        <section class="create-modal" role="dialog" aria-modal="true" aria-labelledby="createExamTitle">
            <h3 id="createExamTitle">Tạo đề thi mới</h3>
            <p>Nhập thông tin đề thi. Hiện tại biểu mẫu là giao diện mẫu; cần kết nối backend để lưu đề thi vào hệ thống.</p>
            <form id="createExamForm">
                <label for="newExamType">Loại bài thi</label>
                <select id="newExamType" name="type" required>
                    <option>Kiểm tra đầu vào</option><option>TOEIC Listening &amp; Reading</option><option>TOEIC Speaking &amp; Writing</option>
                </select>
                <div style="height:14px"></div>
                <label for="newExamName">Tên đề thi</label>
                <input id="newExamName" name="name" maxlength="100" placeholder="Ví dụ: TOEIC Full Test #03" required>
                <div class="create-modal-actions"><button class="create-modal-cancel" type="button" id="cancelCreateExam">Huỷ</button><button class="create-modal-submit" type="submit">Tạo đề</button></div>
            </form>
        </section>
    </div>
</div>

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

<script>
    // Script đặt trực tiếp trong view để không phụ thuộc @stack('scripts') của layout.
    document.addEventListener('click', function (event) {
        var target = event.target instanceof Element ? event.target : event.target.parentElement;
        if (!target) return;
        var toggle = target.closest('.toeic-page .exam-category-toggle');
        if (toggle) {
            var panelId = toggle.getAttribute('aria-controls');
            var panel = panelId ? document.getElementById(panelId) : null;
            if (!panel) return;
            var willOpen = toggle.getAttribute('aria-expanded') !== 'true';
            toggle.setAttribute('aria-expanded', String(willOpen));
            panel.hidden = !willOpen;
            return;
        }

        var createButton = target.closest('[data-create-exam]');
        if (createButton) {
            var createModal = document.getElementById('createExamModal');
            var createType = document.getElementById('newExamType');
            var createName = document.getElementById('newExamName');
            if (!createModal || !createType || !createName) return;
            createType.value = createButton.getAttribute('data-exam-type') || 'TOEIC Listening & Reading';
            createName.value = '';
            createModal.hidden = false;
            window.setTimeout(function () { createName.focus(); }, 0);
            return;
        }

        if (target.closest('#cancelCreateExam')) {
            var modalToClose = document.getElementById('createExamModal');
            if (modalToClose) modalToClose.hidden = true;
            return;
        }

        if (event.target.id === 'createExamModal') {
            event.target.hidden = true;
        }
    });

    document.addEventListener('submit', function (event) {
        if (event.target.id !== 'createExamForm') return;
        event.preventDefault();
        var name = document.getElementById('newExamName');
        var type = document.getElementById('newExamType');
        if (!name || !type || !name.value.trim()) return;
        alert('Đã nhận thông tin đề “' + name.value.trim() + '” thuộc loại “' + type.value + '”. Để lưu đề thật, cần tích hợp API/backend.');
        var modal = document.getElementById('createExamModal');
        if (modal) modal.hidden = true;
    });
</script>
<script>
    function startFullscreenExam() {
        const elem = document.documentElement;
        if (elem.requestFullscreen) {
            elem.requestFullscreen().catch(function () {
                console.warn('Không thể bật chế độ toàn màn hình.');
            });
        }
        alert('Giao diện xác nhận đã hoàn tất. Cần tích hợp backend để trừ lượt và khởi tạo bài thi thực tế.');
    }
</script>
</x-layouts.app>
