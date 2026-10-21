<x-layouts.app active="" title="StudyPrep - Cài đặt hệ thống">
    @push('styles')
    <style>
        .settings-page {
            max-width: 900px;
            margin: 0 auto;
            padding: 8px 0 36px;
            color: #17213b;
        }
        .settings-page * { box-sizing: border-box; }
        .settings-page .settings-header { margin-bottom: 22px; }
        .settings-page .settings-title {
            margin: 0;
            color: #17213b;
            font-size: clamp(23px, 3vw, 30px);
            line-height: 1.25;
            font-weight: 850;
            letter-spacing: -.03em;
        }
        .settings-page .settings-description {
            margin: 8px 0 0;
            color: #69758d;
            font-size: 13px;
            line-height: 1.65;
        }
        .settings-page .settings-stack { display: grid; gap: 18px; }
        .settings-page .settings-card {
            min-width: 0;
            padding: 21px 22px;
            border: 1px solid #e6ebf4;
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 3px 12px #24345a05;
        }
        .settings-page .settings-card-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 0 7px;
            color: #17213b;
            font-size: 15px;
            font-weight: 800;
        }
        .settings-page .settings-heading-icon {
            display: inline-grid;
            width: 29px;
            height: 29px;
            flex: 0 0 29px;
            place-items: center;
            border-radius: 9px;
            color: #514bff;
            background: #f0efff;
            font-size: 15px;
        }
        .settings-page .settings-card-description {
            margin: 0 0 15px;
            color: #8993a8;
            font-size: 11px;
            line-height: 1.6;
        }
        .settings-page .setting-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-height: 57px;
            padding: 12px 0;
            border-bottom: 1px solid #edf0f6;
        }
        .settings-page .setting-row:last-child { border-bottom: 0; }
        .settings-page .setting-info { min-width: 0; }
        .settings-page .setting-title {
            display: block;
            color: #27334c;
            font-size: 12px;
            font-weight: 750;
            line-height: 1.5;
        }
        .settings-page .setting-desc {
            margin-top: 3px;
            color: #8a95aa;
            font-size: 10px;
            line-height: 1.5;
        }
        .settings-page .switch-control {
            position: relative;
            display: inline-flex;
            flex: 0 0 auto;
            width: 37px;
            height: 21px;
            cursor: pointer;
        }
        .settings-page .switch-control input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }
        .settings-page .switch-track {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            background: #d8dfeb;
            transition: background .18s ease;
        }
        .settings-page .switch-track::after {
            content: "";
            position: absolute;
            top: 3px;
            left: 3px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 3px #17213b24;
            transition: transform .18s ease;
        }
        .settings-page .switch-control input:checked + .switch-track { background: #087cff; }
        .settings-page .switch-control input:checked + .switch-track::after { transform: translateX(16px); }
        .settings-page .switch-control input:focus-visible + .switch-track {
            outline: 3px solid #087cff35;
            outline-offset: 2px;
        }
        .settings-page .reminder-list {
            display: grid;
            gap: 0;
            margin-top: 5px;
        }
        .settings-page .reminder-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 8px 0;
        }
        .settings-page .reminder-option-title {
            display: block;
            color: #27334c;
            font-size: 11px;
            font-weight: 750;
        }
        .settings-page .reminder-option-desc {
            margin-top: 3px;
            color: #8a95aa;
            font-size: 10px;
            line-height: 1.45;
        }
        .settings-page .reminder-option input[type="checkbox"] {
            width: 15px;
            height: 15px;
            flex: 0 0 auto;
            accent-color: #087cff;
            cursor: pointer;
        }
        .settings-page .settings-select {
            min-width: 155px;
            min-height: 37px;
            padding: 0 11px;
            border: 1px solid #dfe5ef;
            border-radius: 9px;
            color: #34415b;
            background: #fff;
            font: inherit;
            font-size: 11px;
        }
        .settings-page .settings-select:focus {
            border-color: #7e85ff;
            outline: 3px solid #514bff15;
        }
        .settings-page .account-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding-top: 9px;
        }
        .settings-page .account-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .settings-page .account-icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            font-size: 20px;
        }
        .settings-page .account-email {
            margin-top: 3px;
            color: #10a675;
            font-size: 11px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }
        .settings-page .connected-badge {
            flex: 0 0 auto;
            padding: 6px 9px;
            border-radius: 8px;
            color: #087e55;
            background: #e9fbf2;
            font-size: 10px;
            font-weight: 800;
        }
        .settings-page .settings-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            padding-top: 2px;
        }
        @media (max-width: 640px) {
            .settings-page { padding: 0 0 24px; }
            .settings-page .settings-card { padding: 17px 15px; border-radius: 14px; }
            .settings-page .setting-row { align-items: flex-start; gap: 12px; }
            .settings-page .setting-row .settings-select { max-width: 48%; min-width: 125px; }
            .settings-page .settings-actions { flex-wrap: wrap; }
            .settings-page .settings-actions > * { flex: 1; justify-content: center; }
            .settings-page .account-row { align-items: flex-start; }
        }
    </style>
    @endpush

    <div class="settings-page">
        <header class="settings-header">
            <h1 class="sp-page-title">Cài đặt hệ thống &amp; Nhắc nhở</h1>
            <p class="sp-page-desc">Tùy biến lịch nhắc nhở học tập, âm thanh luyện thi và tài khoản liên kết</p>
        </header>

        <form id="studySettingsForm">
            <div class="settings-stack">
                {{-- Thông báo trong hệ thống --}}
                <section class="settings-card" aria-labelledby="system-notification-title">
                    <h2 class="settings-card-heading" id="system-notification-title">
                        <span class="settings-heading-icon" aria-hidden="true">♧</span>
                        Cài đặt thông báo
                    </h2>

                    <div class="setting-row">
                        <div class="setting-info">
                            <strong class="setting-title">Thông báo trong hệ thống</strong>
                            <div class="setting-desc">Nhận thông báo qua biểu tượng chuông trên website.</div>
                        </div>
                        <label class="switch-control" aria-label="Bật thông báo trong hệ thống">
                            <input type="checkbox" name="system_notifications" checked>
                            <span class="switch-track"></span>
                        </label>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <strong class="setting-title">Bật thông báo</strong>
                            <div class="setting-desc">Cho phép StudyPrep gửi thông báo đến tài khoản của bạn.</div>
                        </div>
                        <label class="switch-control" aria-label="Bật thông báo">
                            <input type="checkbox" name="notifications_enabled" checked>
                            <span class="switch-track"></span>
                        </label>
                    </div>
                </section>

                {{-- Nhắc nhở ngày thi mục tiêu --}}
                <section class="settings-card" aria-labelledby="exam-reminders-title">
                    <h2 class="settings-card-heading" id="exam-reminders-title">
                        <span class="settings-heading-icon" aria-hidden="true">♧</span>
                        Nhắc nhở ngày thi mục tiêu
                    </h2>
                    <p class="settings-card-description">Chọn những mốc thời gian bạn muốn được nhắc trước ngày thi.</p>

                    <div class="reminder-list">
                        <label class="reminder-option">
                            <span>
                                <span class="reminder-option-title">30 ngày trước</span>
                                <span class="reminder-option-desc">Bắt đầu tăng tốc ôn luyện</span>
                            </span>
                            <input type="checkbox" name="exam_reminder_30_days" checked>
                        </label>
                        <label class="reminder-option">
                            <span>
                                <span class="reminder-option-title">7 ngày trước</span>
                                <span class="reminder-option-desc">Rà soát kiến thức</span>
                            </span>
                            <input type="checkbox" name="exam_reminder_7_days" checked>
                        </label>
                        <label class="reminder-option">
                            <span>
                                <span class="reminder-option-title">3 ngày trước</span>
                                <span class="reminder-option-desc">Ôn lại phần còn yếu</span>
                            </span>
                            <input type="checkbox" name="exam_reminder_3_days" checked>
                        </label>
                        <label class="reminder-option">
                            <span>
                                <span class="reminder-option-title">1 ngày trước</span>
                                <span class="reminder-option-desc">Chuẩn bị cho ngày thi</span>
                            </span>
                            <input type="checkbox" name="exam_reminder_1_day" checked>
                        </label>
                    </div>
                </section>

                {{-- Thông báo học tập --}}
                <section class="settings-card" aria-labelledby="learning-notifications-title">
                    <h2 class="settings-card-heading" id="learning-notifications-title">
                        <span class="settings-heading-icon" aria-hidden="true">♧</span>
                        Thông báo học tập
                    </h2>

                    <div class="setting-row">
                        <div class="setting-info">
                            <strong class="setting-title">Nhắc nhở luyện tập</strong>
                            <div class="setting-desc">Nhắc bạn quay lại bài thi đang làm dở.</div>
                        </div>
                        <label class="switch-control" aria-label="Nhắc nhở luyện tập">
                            <input type="checkbox" name="practice_reminders" checked>
                            <span class="switch-track"></span>
                        </label>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <strong class="setting-title">Kết quả thi thử</strong>
                            <div class="setting-desc">Thông báo khi kết quả được chấm xong.</div>
                        </div>
                        <label class="switch-control" aria-label="Thông báo kết quả thi thử">
                            <input type="checkbox" name="exam_result_notifications" checked>
                            <span class="switch-track"></span>
                        </label>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <strong class="setting-title">Tiến độ mục tiêu</strong>
                            <div class="setting-desc">Thông báo khi đạt mục tiêu học tập.</div>
                        </div>
                        <label class="switch-control" aria-label="Thông báo tiến độ mục tiêu">
                            <input type="checkbox" name="goal_progress_notifications" checked>
                            <span class="switch-track"></span>
                        </label>
                    </div>
                </section>

                {{-- Cài đặt nhắc nhở học tập hằng ngày --}}
                <section class="settings-card" aria-labelledby="daily-reminders-title">
                    <h2 class="settings-card-heading" id="daily-reminders-title">
                        <span class="settings-heading-icon" aria-hidden="true">◷</span>
                        Nhắc nhở học tập hằng ngày
                    </h2>
                    <p class="settings-card-description">Duy trì chuỗi học tập đều đặn giúp bạn giữ nhịp ôn luyện.</p>

                    <div class="setting-row">
                        <div class="setting-info">
                            <strong class="setting-title">Bật thông báo nhắc học trên trình duyệt</strong>
                            <div class="setting-desc">Nhận thông báo khi đến giờ học từ vựng hoặc luyện tập.</div>
                        </div>
                        <label class="switch-control" aria-label="Bật nhắc học trên trình duyệt">
                            <input type="checkbox" name="browser_study_notifications" checked>
                            <span class="switch-track"></span>
                        </label>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <strong class="setting-title">Thời gian nhắc nhở mỗi ngày</strong>
                            <div class="setting-desc">Chọn khung giờ bạn thường rảnh để học.</div>
                        </div>
                        <select class="settings-select" name="daily_reminder_time">
                            <option value="19:00">19:00 Tối</option>
                            <option value="20:00" selected>20:00 Tối (Khuyên dùng)</option>
                            <option value="21:00">21:00 Tối</option>
                            <option value="07:00">07:00 Sáng</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <strong class="setting-title">Gửi email tóm tắt tiến độ tuần</strong>
                            <div class="setting-desc">Báo cáo số câu đúng và số từ vựng đã nhớ vào sáng thứ Hai.</div>
                        </div>
                        <label class="switch-control" aria-label="Gửi email tóm tắt tiến độ tuần">
                            <input type="checkbox" name="weekly_progress_email" checked>
                            <span class="switch-track"></span>
                        </label>
                    </div>
                </section>

                {{-- Cài đặt âm thanh và phòng thi --}}
                <section class="settings-card" aria-labelledby="exam-settings-title">
                    <h2 class="settings-card-heading" id="exam-settings-title">
                        <span class="settings-heading-icon" aria-hidden="true">♫</span>
                        Cài đặt âm thanh &amp; Phòng thi
                    </h2>

                    <div class="setting-row">
                        <div class="setting-info">
                            <strong class="setting-title">Tốc độ phát âm thanh mặc định (Listening)</strong>
                            <div class="setting-desc">Tốc độ phát mặc định khi luyện nghe.</div>
                        </div>
                        <select class="settings-select" name="listening_speed">
                            <option value="0.9">0.9x (Chậm nhẹ)</option>
                            <option value="1.0" selected>1.0x (Chuẩn đề thi)</option>
                            <option value="1.1">1.1x (Thử thách)</option>
                            <option value="1.2">1.2x (Nâng cao)</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <strong class="setting-title">Tự động chuyển câu hỏi tiếp theo</strong>
                            <div class="setting-desc">Sau khi chọn đáp án, tự động chuyển sang câu kế tiếp.</div>
                        </div>
                        <label class="switch-control" aria-label="Tự động chuyển câu hỏi">
                            <input type="checkbox" name="auto_next_question" checked>
                            <span class="switch-track"></span>
                        </label>
                    </div>
                </section>

                {{-- Tài khoản liên kết --}}
                <section class="settings-card" aria-labelledby="linked-account-title">
                    <h2 class="settings-card-heading" id="linked-account-title">
                        <span class="settings-heading-icon" aria-hidden="true">♙</span>
                        Tài khoản liên kết
                    </h2>
                    <div class="account-row">
                        <div class="account-info">
                            <div class="account-icon" aria-hidden="true">🌐</div>
                            <div>
                                <strong class="setting-title">Đăng nhập bằng tài khoản Google</strong>
                                <div class="account-email">Đã liên kết: zuzuwu2005@gmail.com</div>
                            </div>
                        </div>
                        <span class="connected-badge">Đã kết nối</span>
                    </div>
                </section>

                <div class="settings-actions">
                    <x-button variant="neutral" href="{{ url('/luyen-tap') }}">Quay lại</x-button>
                    <x-button variant="primary" type="button" onclick="saveStudySettings()">Lưu cài đặt</x-button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function saveStudySettings() {
            const form = document.getElementById('studySettingsForm');
            const settings = Object.fromEntries(new FormData(form).entries());
            form.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
                settings[input.name] = input.checked;
            });

            // Demo UI: thay bằng request gửi settings tới backend khi API sẵn sàng.
            console.log('StudyPrep settings:', settings);
            alert('Đã ghi nhận các lựa chọn cài đặt trên giao diện. Cần kết nối backend để lưu lâu dài.');
        }
    </script>
    @endpush
</x-layouts.app>
