<x-layouts.app active="" title="StudyPrep - Hồ sơ học viên">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/ho-so.css') }}">
@endpush

{{-- Tiêu đề trang đồng bộ chuẩn với Luyện tập --}}
<div class="hs-page-header">
    <h1 class="hs-page-title">Hồ sơ của bạn</h1>
    <p class="hs-page-desc">Quản lý thông tin cá nhân và theo dõi hành trình học tập của bạn.</p>
</div>

{{-- 1. Card thông tin tài khoản --}}
<section class="hs-card hs-profile-summary">
    <div class="hs-profile-main">
        <div class="hs-avatar-wrap">
            <div class="hs-avatar" id="avatarInitials">MA</div>
            <span class="hs-avatar-edit" title="Ảnh đại diện">✎</span>
        </div>
        <div class="hs-profile-identity">
            <div class="hs-name-line">
                <h2 id="summaryName">Nguyễn Minh Anh</h2>
                <span class="hs-status-pill">Đã xác thực</span>
            </div>
            <p class="hs-email-line" id="summaryEmail">minhanh.nguyen@gmail.com</p>
            <div class="hs-profile-meta">
                <span class="hs-course-pill">KHOÁ HỌC TOEIC 750+</span>
                <span class="hs-dot">•</span>
                <span>Tham gia từ tháng 8, 2025</span>
            </div>
        </div>
    </div>
    <x-button variant="neutral" size="medium" id="changeAvatar" type="button">
        <span>▧</span> &nbsp; Đổi ảnh đại diện
    </x-button>
</section>

{{-- 2. Form Thông tin cá nhân (Layout 2 cột trên desktop, reuse shared Input & Button) --}}
<section class="hs-card personal-card">
    <div class="hs-section-heading">
        <div>
            <h3 class="hs-section-title">Thông tin cá nhân</h3>
            <p class="hs-section-sub">Cập nhật thông tin để StudyPrep hiểu bạn hơn.</p>
        </div>
    </div>
    <form id="profileForm">
        <div class="hs-form-grid">
            {{-- Họ và tên (shared Input) --}}
            <x-input id="fullName" name="fullName" label="Họ và tên" value="Nguyễn Minh Anh" required />

            {{-- Ngày sinh (giữ nguyên native date picker với styling đồng bộ) --}}
            <div class="hs-field">
                <label class="hs-label" for="birthday">Ngày sinh</label>
                <div class="hs-control">
                    <input id="birthday" name="birthday" type="date" value="2002-05-15" class="hs-input" />
                </div>
            </div>

            {{-- Địa chỉ email (shared Input) --}}
            <x-input id="email" name="email" label="Địa chỉ email" type="email" value="minhanh.nguyen@gmail.com" required />

            {{-- Số điện thoại (shared Input) --}}
            <x-input id="phone" name="phone" label="Số điện thoại" type="tel" value="0912 345 678" />

            {{-- Giới tính (giữ nguyên select với styling đồng bộ) --}}
            <div class="hs-field">
                <label class="hs-label" for="gender">Giới tính</label>
                <div class="hs-control">
                    <select id="gender" name="gender" class="hs-select">
                        <option>Nữ</option>
                        <option>Nam</option>
                        <option>Khác</option>
                        <option>Không muốn tiết lộ</option>
                    </select>
                </div>
            </div>

            {{-- Tỉnh / Thành phố (giữ nguyên select với styling đồng bộ) --}}
            <div class="hs-field">
                <label class="hs-label" for="city">Tỉnh / Thành phố</label>
                <div class="hs-control">
                    <select id="city" name="city" class="hs-select">
                        <option>Hà Nội</option>
                        <option>TP. Hồ Chí Minh</option>
                        <option>Đà Nẵng</option>
                        <option>Cần Thơ</option>
                        <option>Khác</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="hs-form-footer">
            <span class="hs-muted-note" id="saveMessage">Dùng thông tin này để tối ưu trải nghiệm của bạn.</span>
            <x-button variant="primary" size="medium" type="submit">
                ✓ &nbsp; Lưu thay đổi
            </x-button>
        </div>
    </form>
</section>

{{-- 3. Card Mục tiêu của bạn --}}
<section class="hs-card goal-card">
    <div class="hs-inline-heading">
        <h3 class="hs-section-title">Mục tiêu của bạn</h3>
        <button class="hs-tiny-icon-btn" id="editGoal" aria-label="Chỉnh sửa mục tiêu" title="Chỉnh sửa mục tiêu">⚙</button>
    </div>
    <div class="hs-goal-score">
        <strong id="goalScore">750+</strong>
        <span>Điểm TOEIC</span>
    </div>
    <div class="hs-goal-details">
        <p><span class="hs-mini-icon">▦</span> 20 tối/tuần mỗi ngày</p>
        <p><span class="hs-mini-icon">▤</span> Dự kiến đạt: 30/12/2026</p>
    </div>
    <div class="hs-goal-divider"></div>
    <a href="{{ url('/ho-so/muc-tieu-thiet-lap') }}" class="hs-text-link" id="editGoalText">
        ✎ &nbsp; Điều chỉnh mục tiêu
    </a>
</section>

{{-- 4. Card Tiến độ học tập --}}
<section class="hs-card progress-card">
    <div class="hs-inline-heading">
        <h3 class="hs-section-title">Tiến độ học tập</h3>
        <span class="hs-mini-icon">▥</span>
    </div>
    <div class="hs-stats-grid">
        <div class="hs-stat-box">
            <div class="hs-stat-icon">▤</div>
            <strong>32</strong>
            <span>Bài học hoàn thành</span>
        </div>
        <div class="hs-stat-box">
            <div class="hs-stat-icon">▧</div>
            <strong>256</strong>
            <span>Từ vựng đã học</span>
        </div>
    </div>
    <div class="hs-course-progress-heading">
        <strong>TOEIC 750+</strong>
        <strong class="hs-progress-percent">64%</strong>
    </div>
    <div class="hs-progress-track">
        <div class="hs-progress-fill"></div>
    </div>
    <p class="hs-progress-caption">32 / 50 bài học đã hoàn thành</p>

    <button class="hs-text-link" id="detailsBtn" type="button">
        Xem phân tích chi tiết <span>→</span>
    </button>
</section>

{{-- 5. Card Cài đặt tài khoản --}}
<section class="hs-card settings-card">
    <h3 class="hs-section-title" style="margin-bottom: 8px;">Cài đặt tài khoản</h3>
    <button class="hs-setting-row" id="passwordBtn" type="button" aria-haspopup="dialog">
        <span class="hs-setting-icon">♙</span>
        <span class="hs-setting-copy">
            <strong>Mật khẩu</strong>
            <small>Cập nhật lần cuối: 15/08/2026</small>
        </span>
        <span class="hs-setting-action">Thay đổi <b>›</b></span>
    </button>
    <div class="hs-setting-row" style="cursor: default;">
        <span class="hs-setting-icon">♧</span>
        <span class="hs-setting-copy">
            <strong>Nhắc nhở học tập</strong>
            <small>Nhận nhắc nhở vào mỗi ngày lúc 20:00</small>
        </span>
        <label class="hs-switch" aria-label="Bật hoặc tắt nhắc nhở">
            <input id="reminderToggle" type="checkbox" checked>
            <span class="hs-slider"></span>
        </label>
    </div>
    <div class="hs-settings-footer">
        <span id="reminderStatus">Bạn đang bật nhắc nhở học tập hằng ngày.</span>

    </div>
</section>


<div class="toast" id="toast" role="status" aria-live="polite"></div>

{{-- Modal Đổi mật khẩu --}}
<div class="password-modal" id="passwordModal" aria-hidden="true">
    <div class="popup" role="dialog" aria-modal="true" aria-labelledby="popupTitle">
        <button class="close" type="button" id="closePopup" aria-label="Đóng popup">×</button>
        <section>
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
                <x-button variant="primary" size="medium" type="submit" style="width: 100%; margin-top: 20px;">
                    ĐỔI MẬT KHẨU
                </x-button>
                <p class="notice" id="changeNotice" aria-live="polite"></p>
            </form>
        </section>
    </div>
</div>

@push('scripts')
<script>
    const $ = (id) => document.getElementById(id);
    let toastTimer;
    function toast(message) {
        const el = $("toast");
        if (!el) return;
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

    if ($("editGoal")) $("editGoal").addEventListener("click", () => {
        window.location.href = @json(url('/ho-so/muc-tieu-thiet-lap'));
    });

    if ($("reminderToggle")) $("reminderToggle").addEventListener("change", (event) => {
        const enabled = event.target.checked;
        $("reminderStatus").textContent = enabled
            ? "Bạn đang bật nhắc nhở học tập hằng ngày."
            : "Bạn đã tắt nhắc nhở học tập hằng ngày.";
        toast(enabled ? "Đã bật nhắc nhở (bản demo)." : "Đã tắt nhắc nhở (bản demo).");
    });

    if ($("changeAvatar")) $("changeAvatar").addEventListener("click", () => toast("Chức năng tải ảnh cần được kết nối thêm."));
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
@endpush

</x-layouts.app>

