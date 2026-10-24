<x-layouts.app active="muc-tieu" title="StudyPrep - Mục tiêu học tập">
@push('styles')
<style>
    * { box-sizing: border-box; }
    :root { --blue:#514cff; --blue2:#4f83ff; --ink:#1f294a; --muted:#8b96b1; --line:#e9edf7; --page:#f5f7fc; }
    body { margin:0; background:var(--page); color:var(--ink); font-family:Inter,Arial,sans-serif; font-size:12px; }
    button,input { font:inherit; }
    button,a { -webkit-tap-highlight-color:transparent; }
    button { cursor:pointer; }
    .app-shell { width:min(100% - 40px,1040px); margin:auto; }
    .topbar { height:76px; display:flex; align-items:center; justify-content:space-between; }
    .brand-mark { display:flex; align-items:center; gap:8px; color:#3c5dba; font-weight:800; font-size:15px; text-decoration:none; }
    .brand-icon { display:grid; place-items:center; width:27px; height:27px; color:white; border-radius:9px; background:linear-gradient(145deg,#75a7ff,#4d7cff); }
    .top-actions { display:flex; align-items:center; gap:12px; }
    .icon-button { border:0; background:transparent; color:#6688ff; width:20px; height:24px; font-size:15px; padding:0; }
    .help-link { display:flex; align-items:center; gap:6px; border:0; background:transparent; color:#4f79ee; font-size:11px; }
    .help-avatar { display:grid; place-items:center; width:21px; height:21px; border-radius:50%; background:#527fff; color:white; font-weight:700; }
    main:not(.setup-card) { max-width:780px; margin:15px auto 34px; }
    .page-heading { margin:0 0 22px; }
    .page-heading h1 { font-size:24px; line-height:1.25; letter-spacing:-.7px; margin:0 0 7px; font-weight:800; }
    .page-heading p { color:var(--muted); margin:0; font-size:11px; }
    .card { background:#fff; border:1px solid #edf0f8; border-radius:16px; box-shadow:0 3px 10px #2a417708; margin-bottom:16px; padding:22px 24px; }
    .card-title-row { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .card h2 { margin:0; font-size:14px; font-weight:750; }
    .status-pill { color:#4f78ed; background:#edf3ff; border-radius:5px; padding:4px 7px; font-size:9px; font-weight:600; }
    .score-row { display:flex; align-items:baseline; gap:6px; margin:12px 0 10px; }
    .score-row strong { color:#4f4cff; font-size:27px; letter-spacing:-.8px; }
    .score-row span { color:#8b96b1; font-size:10px; }
    .progress-track { width:100%; height:6px; border-radius:20px; background:#e4e8f2; overflow:hidden; }
    .progress-fill { width:61.6%; height:100%; background:#5a50ff; border-radius:inherit; }
    .current-score { color:#8792ad; font-size:10px; margin-top:8px; }
    .current-score strong { color:#5e6b89; font-weight:600; }
    .goal-dates { display:flex; justify-content:space-between; margin:16px 0 17px; }
    .goal-dates div { display:flex; flex-direction:column; gap:5px; }
    .goal-dates span { color:#939db4; font-size:9px; }
    .goal-dates strong { color:#273452; font-size:12px; }
    .primary-button { border:0; border-radius:8px; background:var(--blue); color:#fff; font-size:11px; font-weight:600; padding:11px 15px; box-shadow:0 4px 9px #514cff24; }
    .primary-button:hover { filter:brightness(.96); }
    .full { width:100%; }
    .skill-card h2 { margin-bottom:5px; }
    .section-subtitle { color:#929db5; margin:0 0 17px; font-size:10px; }
    .skill-row { margin:0 0 15px; }
    .skill-label { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:7px; color:#34405e; font-size:10px; }
    .skill-label span:last-child { color:#7f8aa5; font-size:9px; }
    .skill-track { height:5px; border-radius:20px; background:#e7ebf5; overflow:hidden; }
    .skill-fill { height:100%; background:#5a50ff; border-radius:inherit; }
    .skill-fill.listening { width:64.6%; }
    .skill-fill.reading { width:58.6%; }
    .skill-track.empty { background:#e7ebf5; }
    .note { margin:21px 0 0; color:#a1abc0; font-size:9px; line-height:1.7; }
    .page-footer { text-align:center; color:#a8b1c5; font-size:10px; padding:3px 0 18px; }
    .toast { position:fixed; bottom:22px; left:50%; transform:translate(-50%,20px); opacity:0; pointer-events:none; background:#243452; color:white; padding:12px 18px; border-radius:9px; transition:.25s; z-index:5; font-size:12px; }
    .toast.show { opacity:1; transform:translate(-50%,0); }

    /* Goal setup screen */
    body.setup-page { min-height:100vh; display:grid; place-items:center; padding:24px 14px; background:#f0f4fc; }
    .setup-card { width:min(100%,490px); padding:28px 30px 25px; background:white; border-radius:18px; box-shadow:0 10px 28px #233b7010; }
    .back-link { display:inline-block; color:#657493; font-size:20px; text-decoration:none; line-height:1; margin-bottom:10px; }
    .setup-heading { border-bottom:1px solid #f0f2f8; padding-bottom:14px; margin-bottom:17px; }
    .setup-heading h1 { margin:0 0 4px; color:#26314e; font-size:18px; letter-spacing:-.4px; }
    .setup-heading p { margin:0; color:#9aa4bb; font-size:10px; }
    fieldset { border:0; padding:0; margin:0 0 16px; }
    legend,.field-label { display:block; margin-bottom:8px; color:#293552; font-size:10px; font-weight:650; }
    .radio-option { min-height:34px; display:flex; align-items:center; gap:9px; padding:8px 10px; border:1px solid #e6ebf5; border-radius:8px; margin-bottom:6px; color:#5c6883; font-size:10px; cursor:pointer; }
    .radio-option.selected { border-color:#dfe4ff; background:#fcfcff; }
    .radio-option input { margin:0; accent-color:#514cff; width:14px; height:14px; }
    .target-input-wrap { position:relative; margin-bottom:16px; }
    .target-input-wrap input { width:100%; height:39px; border:1px solid #e3e8f3; border-radius:8px; padding:0 60px 0 12px; color:#34405d; outline:none; }
    .target-input-wrap input:focus,.field-label input:focus { border-color:#8e8bff; box-shadow:0 0 0 3px #514cff12; }
    .target-input-wrap span { position:absolute; right:12px; top:14px; color:#9ba5bd; font-size:9px; }
    .score-grid { display:grid; grid-template-columns:1fr 1fr; gap:13px 15px; margin-bottom:16px; }
    .score-grid .field-label { margin:0; }
    .field-label input { display:block; margin-top:7px; width:100%; height:37px; padding:0 11px; border:1px solid #e3e8f3; border-radius:8px; outline:none; color:#34405d; font-weight:400; }
    .field-label input::placeholder { color:#a6afc2; }
    .exam-date-label { margin-bottom:8px; }
    .date-wrap { height:39px; display:flex; align-items:center; gap:9px; border:1px solid #e3e8f3; border-radius:8px; padding:0 11px; margin-bottom:17px; color:#7c8aaa; }
    .date-wrap input { flex:1; min-width:0; border:0; outline:0; color:#44516e; background:transparent; font-size:11px; }
    .date-wrap .date-chevron { color:#7d8bac; }
    .finish-button { margin-top:0; background:#514cff; }
    .cancel-link { display:block; text-align:center; margin-top:14px; color:#7f8ba7; text-decoration:none; font-size:10px; }
    .cancel-link:hover,.back-link:hover { color:#514cff; }
    @media(max-width:620px) {
      .app-shell { width:min(100% - 24px,520px); }
      .topbar { height:62px; }
      .icon-button { display:none; }
      main:not(.setup-card) { margin-top:13px; }
      .page-heading h1 { font-size:22px; }
      .card { padding:18px 16px; border-radius:13px; margin-bottom:12px; }
      .setup-card { padding:24px 20px 22px; border-radius:15px; }
      .score-grid { gap:12px 10px; }
    }

  </style>
@endpush
  <div class="app-shell">
    
    <main>
      <section class="page-heading">
        <h1>Mục tiêu học tập</h1>
        <p>Đặt mục tiêu hôm nay, chinh phục kỳ thi sắp tới.</p>
      </section>

      <section class="card goal-overview">
        <div class="card-title-row">
          <h2>Mục tiêu TOEIC của bạn</h2><span class="status-pill">Đang thực hiện</span>
        </div>
        <div class="score-row"><strong id="targetScore">750</strong><span>/ 990 điểm mục tiêu</span></div>
        <div class="progress-track"><div class="progress-fill"></div></div>
        <div class="current-score">Điểm hiện tại ước tính: <strong>610/990</strong></div>
        <div class="goal-dates">
          <div><span>Ngày dự kiến thi</span><strong id="examDateLabel">15/12/2026</strong></div>
          <div><span>Thời gian còn lại</span><strong>63 ngày</strong></div>
        </div>
        <button class="primary-button full" id="editGoalBtn">Chỉnh sửa mục tiêu</button>
      </section>

      <section class="card skill-card">
        <h2>Mục tiêu theo kỹ năng</h2>
        <p class="section-subtitle">Vị trí hiện tại theo kỹ năng và điểm cần tiến tới</p>
        <div class="skill-row">
          <div class="skill-label"><span>Listening</span><span>320/495</span></div>
          <div class="skill-track"><div class="skill-fill listening"></div></div>
        </div>
        <div class="skill-row">
          <div class="skill-label"><span>Reading</span><span>290/495</span></div>
          <div class="skill-track"><div class="skill-fill reading"></div></div>
        </div>
        <div class="skill-row">
          <div class="skill-label"><span>Speaking</span><span>Chưa đặt mục tiêu</span></div>
          <div class="skill-track empty"></div>
        </div>
        <div class="skill-row">
          <div class="skill-label"><span>Writing</span><span>Chưa đặt mục tiêu</span></div>
          <div class="skill-track empty"></div>
        </div>
        <p class="note">Listening và Reading là hai kỹ năng tính điểm TOEIC Listening &amp; Reading. Speaking và Writing áp dụng thang điểm riêng khi bạn đăng ký bài thi tương ứng.</p>
      </section>
      <footer class="page-footer">© 2026 StudyPrep · Học tập thông minh, tiến bộ mỗi ngày.</footer>
    </main>
  </div>
  <div class="toast" id="toast" role="status"></div>

@push('scripts')
<script>
    const toast = (message) => {
      const el = document.getElementById('toast');
      el.textContent = message; el.classList.add('show');
      setTimeout(() => el.classList.remove('show'), 2500);
    };
    document.getElementById('editGoalBtn').addEventListener('click', () => location.href = 'setup-goal.html');
    document.getElementById('helpBtn').addEventListener('click', () => toast('Trung tâm hỗ trợ StudyPrep (bản demo).'));
</script>
@endpush

</x-layouts.app>
