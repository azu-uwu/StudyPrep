<x-layouts.app active="" title="StudyPrep - Hồ sơ học viên">
@push('styles')
<style>
    * { box-sizing: border-box; }
    :root {
    --blue: #4f86ff; --blue-dark: #3474f4; --ink: #1f2a4d; --muted: #8995b1;
    --line: #e9eef8; --page: #f5f7fc; --soft-blue: #edf4ff; --white: #fff;
    }
    body { margin: 0; background: var(--page); color: var(--ink); font-family: 'Nunito', sans-serif; font-size: 14px; }
    button, input, select { font: inherit; }
    button { cursor: pointer; }
    .app-shell { width: min(100% - 40px, 1040px); margin: 0 auto; }
    .topbar { height: 76px; display: flex; align-items: center; justify-content: space-between; }
    .brand-mark { display: flex; align-items: center; gap: 8px; color: #3c5dba; font-weight: 800; font-size: 15px; letter-spacing: -.4px; }
    .brand-icon { display: grid; place-items: center; width: 27px; height: 27px; color: #fff; border-radius: 9px; background: linear-gradient(145deg,#75a7ff,#4d7cff); box-shadow: 0 4px 10px #4f86ff2e; }
    .top-actions { display: flex; align-items: center; gap: 12px; }
    .icon-button { border: 0; background: transparent; color: #6688ff; width: 20px; height: 24px; font-size: 15px; padding: 0; }
    .help-link { display: flex; align-items: center; gap: 6px; border: 0; background: transparent; color: #4f79ee; font-size: 11px; }
    .help-avatar { display: grid; place-items: center; width: 21px; height: 21px; border-radius: 50%; background: #527fff; color: white; font-weight: 700; }
    main {
        width: 100%;
        max-width: 1000px;
        margin: 15px 0 34px;
    }
    .page-heading { margin: 0 0 22px; }
    h1 { font-size: 24px; line-height: 1.25; letter-spacing: -.7px; margin: 0 0 7px; font-weight: 800; }
    .page-heading p, .section-heading p { color: var(--muted); margin: 0; font-size: 11px; }
    .card { background: var(--white); border: 1px solid #edf0f8; border-radius: 16px; box-shadow: 0 3px 10px rgba(42, 65, 119, .025); margin-bottom: 16px; padding: 22px 24px; }
    .profile-summary { display: flex; align-items: center; gap: 16px; min-height: 112px; }
    .avatar-wrap { position: relative; flex: 0 0 auto; }
    .avatar { display: grid; place-items: center; width: 70px; height: 70px; border-radius: 50%; color: #4e86ff; background: #edf4ff; font-size: 22px; font-weight: 600; }
    .avatar-edit { position: absolute; right: -1px; bottom: 0; display: grid; place-items: center; width: 19px; height: 19px; border: 2px solid #fff; border-radius: 50%; color: #fff; background: #4f86ff; font-size: 10px; }
    .profile-identity { min-width: 0; flex: 1; }
    .name-line { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    h2 { font-size: 16px; margin: 0; font-weight: 750; }
    .status-pill { background: #e4fff3; color: #10a879; padding: 4px 8px; border-radius: 5px; font-size: 9px; font-weight: 700; }
    .email-line { color: #8490ab; margin: 7px 0 10px; font-size: 11px; }
    .profile-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 7px; color: #9aa4bc; font-size: 10px; }
    .course-pill { color: #3d7eff; background: #edf4ff; padding: 4px 7px; border-radius: 5px; font-size: 9px; font-weight: 700; }
    .outline-button { border: 1px solid #e2e9f6; color: #263453; background: white; border-radius: 8px; padding: 10px 12px; white-space: nowrap; font-size: 10px; font-weight: 600; }
    .outline-button span { color: #5c83ef; margin-right: 5px; }
    .section-heading { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
    h3 { margin: 0; font-size: 14px; font-weight: 750; letter-spacing: -.15px; }
    .section-heading p { margin-top: 5px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 20px; }
    .form-grid label, .goal-editor label { color: #273452; font-size: 10px; font-weight: 600; display: flex; flex-direction: column; gap: 7px; }
    input:not([type="checkbox"]), select { min-width: 0; width: 100%; height: 39px; padding: 0 12px; color: #77839e; border: 1px solid #e9eef8; border-radius: 7px; background: #fff; outline: none; font-size: 11px; }
    input:focus, select:focus { border-color: #82a6ff; box-shadow: 0 0 0 3px #4f86ff16; }
    select { appearance: auto; }
    .form-footer { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 19px; }
    .muted-note { color: #a1abc0; font-size: 10px; }
    .primary-button { border: 0; border-radius: 8px; padding: 11px 17px; background: var(--blue); color: #fff; font-size: 11px; font-weight: 700; box-shadow: 0 4px 9px #4f86ff22; }
    .primary-button:hover { background: var(--blue-dark); }
    .primary-button.small { padding: 9px 12px; }
    .inline-heading { align-items: center; margin-bottom: 15px; }
    .tiny-icon-button { display: grid; place-items: center; border: 0; background: transparent; color: #5590ff; font-size: 16px; padding: 0; }
    .goal-score { display: flex; align-items: baseline; gap: 9px; margin: 2px 0 13px; }
    .goal-score strong { color: #387cff; font-size: 29px; letter-spacing: -1px; }
    .goal-score span { color: #65728e; font-size: 11px; }
    .goal-details p { margin: 8px 0; color: #7f8ba7; font-size: 10px; }
    .mini-icon { display: inline-block; width: 17px; color: #8b9cbd; }
    .goal-divider { height: 1px; background: #edf1f8; margin: 13px 0 12px; }
    .text-button { padding: 0; border: 0; color: #397dff; background: transparent; font-size: 11px; font-weight: 650; }
    .goal-editor { display: flex; align-items: end; gap: 12px; margin-top: 15px; padding-top: 14px; border-top: 1px solid var(--line); }
    .goal-editor label { flex: 1; }
    .chart-symbol { color: #4f86ff; font-size: 18px; }
    .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 19px; }
    .stat-box { background: #f5f8ff; border-radius: 9px; padding: 13px 14px; display: grid; gap: 5px; }
    .stat-icon { color: #4c86ff; font-size: 14px; }
    .stat-box strong { font-size: 20px; color: #273654; line-height: 1; }
    .stat-box span { color: #8793ad; font-size: 10px; }
    .course-progress-heading { display: flex; justify-content: space-between; margin-bottom: 9px; font-size: 11px; }
    .course-progress-heading strong:first-child { color: #293653; }
    .progress-percent { color: #3c80ff; }
    .progress-track { height: 7px; border-radius: 99px; background: #eaf0fb; overflow: hidden; }
    .progress-fill { width: 64%; height: 100%; border-radius: inherit; background: linear-gradient(90deg,#528bff,#6aa2ff); }
    .progress-caption { margin: 7px 0 16px; color: #9ba6bd; font-size: 10px; }
    .streak-box { display: flex; align-items: center; gap: 12px; padding: 13px 14px; border-radius: 8px; background: #edf4ff; }
    .flame { color: #4f8bff; font-size: 21px; }
    .streak-box strong { color: #2d3c5e; font-size: 11px; }
    .streak-box p { color: #8290ad; font-size: 10px; margin: 4px 0 0; }
    .detail-link { margin-top: 16px; }
    .detail-link span { font-size: 16px; margin-left: 4px; }
    .settings-card h3 { margin-bottom: 13px; }
    .setting-row { display: flex; width: 100%; align-items: center; gap: 12px; text-align: left; padding: 14px 0; border: 0; border-bottom: 1px solid #eff2f8; background: white; }
    .setting-icon { width: 34px; height: 34px; display: grid; place-items: center; border: 1px solid #edf1f8; border-radius: 8px; color: #7e91bc; background: #fbfcff; font-size: 14px; }
    .setting-copy { display: flex; flex-direction: column; gap: 5px; flex: 1; }
    .setting-copy strong { color: #2b3857; font-size: 11px; font-weight: 650; }
    .setting-copy small { color: #9aa5bd; font-size: 10px; }
    .setting-action { color: #4d86ff; font-size: 10px; font-weight: 600; }
    .setting-action b { font-size: 16px; margin-left: 4px; }
    .switch { position: relative; width: 35px; height: 20px; flex: 0 0 auto; }
    .switch input { width: 0; height: 0; opacity: 0; }
    .slider { position: absolute; inset: 0; background: #dce3f1; border-radius: 20px; transition: .2s; }
    .slider::before { content: ""; position: absolute; width: 14px; height: 14px; left: 3px; top: 3px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px #20335a22; transition: .2s; }
    .switch input:checked + .slider { background: #568bff; }
    .switch input:checked + .slider::before { transform: translateX(15px); }
    .settings-footer { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding-top: 15px; color: #a0aac0; font-size: 10px; }
    .logout-button { border: 0; background: transparent; color: #647595; font-size: 10px; white-space: nowrap; }
    .page-footer { text-align: center; color: #a8b1c5; font-size: 10px; padding: 3px 0 18px; }
    .toast { position: fixed; bottom: 22px; left: 50%; transform: translate(-50%, 20px); opacity: 0; pointer-events: none; background: #243452; color: white; padding: 12px 18px; border-radius: 9px; box-shadow: 0 8px 24px #1b2a4b2b; transition: .25s; z-index: 5; font-size: 12px; }
    .toast.show { opacity: 1; transform: translate(-50%, 0); }
    @media (max-width: 620px) {
    .app-shell { width: min(100% - 24px, 520px); }
    .topbar { height: 62px; }
    .brand-mark { font-size: 13px; }
    .top-actions { gap: 7px; }
    .icon-button { display: none; }
    main { margin-top: 13px; }
    h1 { font-size: 22px; }
    .card { padding: 18px 16px; border-radius: 13px; margin-bottom: 12px; }
    .profile-summary { align-items: flex-start; flex-wrap: wrap; gap: 12px; }
    .avatar { width: 57px; height: 57px; font-size: 18px; }
    .profile-identity { flex: 1 1 calc(100% - 75px); }
    .outline-button { margin-left: 69px; }
    .form-grid { grid-template-columns: 1fr; gap: 13px; }
    .form-footer { align-items: flex-end; }
    .muted-note { max-width: 58%; line-height: 1.5; }
    .stats-grid { gap: 9px; }
    .stat-box { padding: 11px; }
    .goal-editor { align-items: stretch; flex-wrap: wrap; }
    .goal-editor label { flex-basis: 100%; }
    .settings-footer { align-items: flex-start; }
    }
    /* Căn trang hồ sơ về trái trong vùng nội dung */
    .sp-content-wrapper {
        display: flex;
        justify-content: flex-start !important;
        align-items: flex-start;
        padding-left: 8px !important;
        padding-right: 24px;
    }

    .profile-page {
        width: 100%;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 16px;
        box-sizing: border-box;
    }

    .profile-page > main {
        width: 100%;
        max-width: none !important;
        margin: 15px 0 34px !important;
    }

    /* FIX: ép nội dung hồ sơ sát trái */
    .sp-main-container .sp-content-wrapper {
        display: block !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .sp-main-container .sp-content-wrapper > .profile-page {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 16px !important;
    }

    .sp-main-container .sp-content-wrapper .profile-page > main {
        width: 100% !important;
        max-width: 1000px !important;
        margin: 15px 0 34px !important;
    }


    /* Popup đổi mật khẩu */
    .password-modal {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(25, 38, 65, 0.45);
        backdrop-filter: blur(3px);
    }
    .password-modal.show { display: flex !important; }
    .password-modal .popup {
        position: relative;
        width: 100%;
        max-width: 420px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .password-modal .card {
        margin: 0;
        padding: 28px;
        border-radius: 18px;
        box-shadow: 0 20px 60px rgba(25, 38, 65, 0.18);
    }
    .password-modal .popup-title {
        margin: 12px 0 8px;
        font-size: 22px;
        color: #1f2a4d;
    }
    .password-modal .description {
        margin: 0 0 22px;
        color: #8995b1;
        font-size: 13px;
        line-height: 1.6;
    }
    .password-modal .fields { display: grid; gap: 14px; }
    .password-modal .field-wrap { position: relative; }
    .password-modal .field-wrap input {
        padding-right: 44px;
        color: #263453;
        font-size: 13px;
    }
    .password-modal .toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: 0;
        background: transparent;
        color: #8995b1;
    }
    .password-modal .primary {
        width: 100%;
        margin-top: 20px;
        padding: 13px;
        border: 0;
        border-radius: 8px;
        background: #4f86ff;
        color: white;
        font-weight: 700;
    }
    .password-modal .primary:hover { background: #3474f4; }
    .password-modal .notice {
        margin: 12px 0 0;
        font-size: 12px;
        line-height: 1.5;
    }
    .setting-row#passwordBtn { cursor: pointer; }
    .setting-row#passwordBtn:hover .setting-action { color: #3474f4; }
    @media (max-width: 480px) {
        .password-modal .card { padding: 24px 18px; }
    }

</style>
<div class="profile-page">
    <main>
      <section class="page-heading">
        <div>
          <h1>Hồ sơ của bạn</h1>
          <p>Quản lý thông tin cá nhân và theo dõi hành trình học tập của bạn.</p>
        </div>
      </section>

      <section class="card profile-summary">
        <div class="avatar-wrap">
          <div class="avatar" id="avatarInitials">MA</div>
          <span class="avatar-edit" title="Ảnh đại diện">✎</span>
        </div>
        <div class="profile-identity">
          <div class="name-line"><h2 id="summaryName">Nguyễn Minh Anh</h2><span class="status-pill">Đã xác thực</span></div>
          <p class="email-line" id="summaryEmail">minhanh.nguyen@gmail.com</p>
          <div class="profile-meta"><span class="course-pill">KHOÁ HỌC TOEIC 750+</span><span>•</span><span>Tham gia từ tháng 8, 2025</span></div>
        </div>
        <button class="outline-button" id="changeAvatar"><span>▧</span> Đổi ảnh đại diện</button>
      </section>

      <section class="card personal-card">
        <div class="section-heading">
          <div><h3>Thông tin cá nhân</h3><p>Cập nhật thông tin để StudyPrep hiểu bạn hơn.</p></div>
        </div>
        <form id="profileForm">
          <div class="form-grid">
            <label>Họ và tên<input id="fullName" name="fullName" value="Nguyễn Minh Anh" required /></label>
            <label>Ngày sinh<input id="birthday" name="birthday" type="date" value="2002-05-15" /></label>
            <label>Địa chỉ email<input id="email" name="email" type="email" value="minhanh.nguyen@gmail.com" required /></label>
            <label>Số điện thoại<input id="phone" name="phone" type="tel" value="0912 345 678" /></label>
            <label>Giới tính<select id="gender" name="gender"><option>Nữ</option><option>Nam</option><option>Khác</option><option>Không muốn tiết lộ</option></select></label>
            <label>Tỉnh / Thành phố<select id="city" name="city"><option>Hà Nội</option><option>TP. Hồ Chí Minh</option><option>Đà Nẵng</option><option>Cần Thơ</option><option>Khác</option></select></label>
          </div>
          <div class="form-footer"><span class="muted-note" id="saveMessage">Dùng thông tin này để tối ưu trải nghiệm của bạn.</span><button class="primary-button" type="submit">✓ &nbsp; Lưu thay đổi</button></div>
        </form>
      </section>

      <section class="card goal-card">
        <div class="section-heading inline-heading">
          <div><h3>Mục tiêu của bạn</h3></div>
          <button class="tiny-icon-button" id="editGoal" aria-label="Chỉnh sửa mục tiêu" title="Chỉnh sửa mục tiêu">⚙</button>
        </div>
        <div class="goal-score"><strong id="goalScore">750+</strong><span>Điểm TOEIC</span></div>
        <div class="goal-details"><p><span class="mini-icon">▦</span> 20 tối/tuần mỗi ngày</p><p><span class="mini-icon">▤</span> Dự kiến đạt: 30/12/2026</p></div>
        <div class="goal-divider"></div>
        <a href="{{ url('/ho-so/muc-tieu-thiet-lap') }}"><button class="text-button" id="editGoalText">✎ &nbsp; Điều chỉnh mục tiêu</button></a>
        <!-- <div class="goal-editor" id="goalEditor" hidden>
          <label for="goalSelect">Mục tiêu điểm TOEIC</label>
          <select id="goalSelect"><option>450+</option><option>550+</option><option>650+</option><option selected>750+</option><option>850+</option><option>900+</option></select>
          <button class="primary-button small" id="saveGoal">Lưu mục tiêu</button>
        </div> -->
      </section>

      <section class="card progress-card">
        <div class="section-heading inline-heading"><h3>Tiến độ học tập</h3><span class="chart-symbol">▥</span></div>
        <div class="stats-grid">
          <div class="stat-box"><div class="stat-icon">▤</div><strong>32</strong><span>Bài học hoàn thành</span></div>
          <div class="stat-box"><div class="stat-icon">▧</div><strong>256</strong><span>Từ vựng đã học</span></div>
        </div>
        <div class="course-progress-heading"><strong>TOEIC 750+</strong><strong class="progress-percent">64%</strong></div>
        <div class="progress-track"><div class="progress-fill"></div></div>
        <p class="progress-caption">32 / 50 bài học đã hoàn thành</p>
        <div class="streak-box"><span class="flame">♨</span><div><strong>7 ngày học liên tiếp</strong><p>Cố gắng tiếp tục nhé, Minh Anh!</p></div></div>
        <button class="text-button detail-link" id="detailsBtn">Xem phân tích chi tiết <span>→</span></button>
      </section>

      <section class="card settings-card">
        <h3>Cài đặt tài khoản</h3>
        <button class="setting-row" id="passwordBtn" type="button" aria-haspopup="dialog">
          <span class="setting-icon">♙</span>
          <span class="setting-copy">
            <strong>Mật khẩu</strong>
            <small>Cập nhật lần cuối: 15/08/2026</small>
          </span>
          <span class="setting-action">Thay đổi <b>›</b></span>
        </button>
        <div class="setting-row">
          <span class="setting-icon">♧</span><span class="setting-copy"><strong>Nhắc nhở học tập</strong><small>Nhận nhắc nhở vào mỗi ngày lúc 20:00</small></span>
          <label class="switch" aria-label="Bật hoặc tắt nhắc nhở"><input id="reminderToggle" type="checkbox" checked><span class="slider"></span></label>
        </div>
        <div class="settings-footer"><span id="reminderStatus">Bạn đang bật nhắc nhở học tập hằng ngày.</span><button class="logout-button" id="logoutBtn">⇥ &nbsp; Đăng xuất</button></div>
      </section>
      <footer class="page-footer">© 2026 StudyPrep · Học tập thông minh, tiến bộ mỗi ngày.</footer>
    </main>
  </div>
  <div class="toast" id="toast" role="status" aria-live="polite"></div>

    <div class="password-modal" id="passwordModal" aria-hidden="true">
      <div class="popup" role="dialog" aria-modal="true" aria-labelledby="popupTitle">
        <button class="close" type="button" id="closePopup" aria-label="Đóng popup">×</button>
        <section class="card">
          <h2 class="popup-title" id="popupTitle">Đổi mật khẩu</h2>
          <p class="description">Vui lòng nhập mật khẩu hiện tại và mật khẩu mới.</p>
          <form id="changeForm" novalidate>
            <div class="fields">
              <div class="field-wrap">
                <input type="password" name="oldPassword" placeholder="Mật khẩu hiện tại" autocomplete="current-password" required>
                <button class="toggle" type="button" aria-label="Hiện mật khẩu">◉</button>
              </div>
              <div class="field-wrap">
                <input type="password" name="password" placeholder="Mật khẩu mới" autocomplete="new-password" required>
                <button class="toggle" type="button" aria-label="Hiện mật khẩu">◉</button>
              </div>
              <div class="field-wrap">
                <input type="password" name="confirm" placeholder="Nhập lại mật khẩu mới" autocomplete="new-password" required>
                <button class="toggle" type="button" aria-label="Hiện mật khẩu">◉</button>
              </div>
            </div>
            <button class="primary" type="submit">ĐỔI MẬT KHẨU</button>
            <p class="notice" id="changeNotice" aria-live="polite"></p>
          </form>
        </section>
      </div>
    </div>



<script>
    const $ = (id) => document.getElementById(id);
    let toastTimer;
    function toast(message) {
    const el = $("toast");
    el.textContent = message;
    el.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove("show"), 2600);
    }
    function initials(name) {
    return name.trim().split(/\s+/).slice(-2).map(part => part[0] || "").join("").toUpperCase();
    }

    if ($("profileForm")) $("profileForm").addEventListener("submit", (event) => {
    event.preventDefault();
    const name = $("fullName").value.trim();
    const email = $("email").value.trim();
    if (!name || !email) {
        toast("Vui lòng nhập họ tên và email hợp lệ.");
        return;
    }
    $("summaryName").textContent = name;
    $("summaryEmail").textContent = email;
    $("avatarInitials").textContent = initials(name);
    $("saveMessage").textContent = "Thông tin đã được cập nhật trên giao diện.";
    toast("Đã lưu thay đổi hồ sơ (bản demo).");
    });

    // Phần chỉnh sửa mục tiêu đang được tắt trong HTML, nên không đăng ký sự kiện
    // với goalEditor/saveGoal cho đến khi bật lại phần giao diện này.
    if ($("editGoal")) $("editGoal").addEventListener("click", () => {
        window.location.href = @json(url('/ho-so/muc-tieu-cai-dat'));
    });

    if ($("reminderToggle")) $("reminderToggle").addEventListener("change", (event) => {
    const enabled = event.target.checked;
    $("reminderStatus").textContent = enabled
        ? "Bạn đang bật nhắc nhở học tập hằng ngày."
        : "Bạn đã tắt nhắc nhở học tập hằng ngày.";
    toast(enabled ? "Đã bật nhắc nhở (bản demo)." : "Đã tắt nhắc nhở (bản demo).");
    });
    if ($("changeAvatar")) $("changeAvatar").addEventListener("click", () => toast("Chức năng tải ảnh cần được kết nối thêm."));
    // Nút trợ giúp chỉ được gắn sự kiện nếu tồn tại trong layout.
    if ($("helpBtn")) {
        $("helpBtn").addEventListener("click", () => toast("Trung tâm hỗ trợ StudyPrep (bản demo)."));
    }
    if ($("detailsBtn")) $("detailsBtn").addEventListener("click", () => toast("Phân tích chi tiết sẽ hiển thị tại trang tiến độ học tập."));
    // Popup đổi mật khẩu
    const passwordModal = document.getElementById("passwordModal");
    const passwordForm = document.getElementById("changeForm");
    const passwordNotice = document.getElementById("changeNotice");
    const passwordButton = document.getElementById("passwordBtn");

    if (passwordModal && passwordForm && passwordNotice && passwordButton) {
        function openPasswordModal(event) {
            if (event) event.preventDefault();
            passwordModal.classList.add("show");
            passwordModal.setAttribute("aria-hidden", "false");
            passwordNotice.textContent = "";
            const firstInput = passwordForm.querySelector("input");
            if (firstInput) firstInput.focus();
        }

        function closePasswordModal() {
            passwordModal.classList.remove("show");
            passwordModal.setAttribute("aria-hidden", "true");
            passwordForm.reset();
            passwordNotice.textContent = "";
        }

        passwordButton.addEventListener("click", openPasswordModal);
        const closeButton = document.getElementById("closePopup");
        if (closeButton) closeButton.addEventListener("click", closePasswordModal);

        passwordModal.addEventListener("click", (event) => {
            if (event.target === passwordModal) closePasswordModal();
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && passwordModal.classList.contains("show")) {
                closePasswordModal();
            }
        });

        passwordModal.querySelectorAll(".toggle").forEach((button) => {
            button.addEventListener("click", () => {
                const input = button.parentElement.querySelector("input");
                const showPassword = input.type === "password";
                input.type = showPassword ? "text" : "password";
                button.setAttribute("aria-label", showPassword ? "Ẩn mật khẩu" : "Hiện mật khẩu");
            });
        });

        passwordForm.addEventListener("submit", (event) => {
            event.preventDefault();
            const oldPassword = passwordForm.elements.oldPassword.value;
            const newPassword = passwordForm.elements.password.value;
            const confirmPassword = passwordForm.elements.confirm.value;

            const showError = (message) => {
                passwordNotice.style.color = "#d64c5e";
                passwordNotice.textContent = message;
            };

            if (!oldPassword || !newPassword || !confirmPassword) {
                showError("Vui lòng nhập đầy đủ thông tin.");
            } else if (newPassword.length < 8) {
                showError("Mật khẩu mới phải có ít nhất 8 ký tự.");
            } else if (newPassword === oldPassword) {
                showError("Mật khẩu mới phải khác mật khẩu hiện tại.");
            } else if (newPassword !== confirmPassword) {
                showError("Mật khẩu xác nhận không khớp.");
            } else {
                passwordNotice.style.color = "#10a879";
                passwordNotice.textContent =
                    "Thông tin hợp lệ. Cần kết nối API để cập nhật mật khẩu thật.";
            }
        });
    }

    if ($("logoutBtn")) $("logoutBtn").addEventListener("click", () => {
    if (confirm("Bạn có chắc chắn muốn đăng xuất?")) toast("Đây là bản demo: chưa có phiên đăng nhập để đăng xuất.");
    });

</script>

</x-layouts.app>
