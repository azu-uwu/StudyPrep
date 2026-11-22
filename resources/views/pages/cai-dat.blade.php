<x-layouts.app active="" title="StudyPrep - Cài đặt hệ thống">
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/cai-dat.css') }}">
@endpush

<div class="settings-page">

    <header class="settings-header">
        <h1 class="sp-page-title">Cài đặt hệ thống &amp; Nhắc nhở</h1>
        <p class="sp-page-desc">
            Tùy biến lịch nhắc nhở học tập, âm thanh luyện thi và tài khoản liên kết
        </p>
    </header>

    {{-- Thông báo lưu thành công --}}
    @if(session('success'))
        <div class="settings-card" role="status">
            <p style="color: #10a675;">
                {{ session('success') }}
            </p>
        </div>
    @endif

    {{-- Hiển thị lỗi validation --}}
    @if($errors->any())
        <div class="settings-card" role="alert">
            @foreach($errors->all() as $error)
                <p style="color: #dc2626;">
                    {{ $error }}
                </p>
            @endforeach
        </div>
    @endif

    <form
        id="studySettingsForm"
        method="POST"
        action="{{ route('settings.update') }}"
    >
        @csrf
        @method('PUT')

        <div class="settings-stack">

            {{-- 1. CÀI ĐẶT THÔNG BÁO --}}
            <section class="settings-card"
                     aria-labelledby="system-notification-title">

                <h2 class="settings-card-heading"
                    id="system-notification-title">
                    <span class="settings-heading-icon">♧</span>
                    Cài đặt thông báo
                </h2>

                <div class="setting-row">
                    <div class="setting-info">
                        <strong class="setting-title">
                            Thông báo trong hệ thống
                        </strong>
                        <div class="setting-desc">
                            Nhận thông báo qua biểu tượng chuông trên website.
                        </div>
                    </div>

                    <label class="switch-control">
                        <input type="hidden"
                               name="system_notifications"
                               value="0">

                        <input type="checkbox"
                               name="system_notifications"
                               value="1"
                               @checked((bool) old(
                                   'system_notifications',
                                   $settings['system_notifications']
                               ))>

                        <span class="switch-track"></span>
                    </label>
                </div>

                <div class="setting-row">
                    <div class="setting-info">
                        <strong class="setting-title">
                            Bật thông báo
                        </strong>
                        <div class="setting-desc">
                            Cho phép StudyPrep gửi thông báo đến tài khoản của bạn.
                        </div>
                    </div>

                    <label class="switch-control">
                        <input type="hidden"
                               name="notifications_enabled"
                               value="0">

                        <input type="checkbox"
                               name="notifications_enabled"
                               value="1"
                               @checked((bool) old(
                                   'notifications_enabled',
                                   $settings['notifications_enabled']
                               ))>

                        <span class="switch-track"></span>
                    </label>
                </div>

            </section>


            {{-- 2. NHẮC NHỞ NGÀY THI --}}
            <section class="settings-card"
                     aria-labelledby="exam-reminders-title">

                <h2 class="settings-card-heading"
                    id="exam-reminders-title">
                    <span class="settings-heading-icon">♧</span>
                    Nhắc nhở ngày thi mục tiêu
                </h2>

                <p class="settings-card-description">
                    Chọn những mốc thời gian bạn muốn được nhắc trước ngày thi.
                </p>

                <div class="reminder-list">

                    @php
                        $examReminders = [
                            'exam_reminder_30_days' => [
                                '30 ngày trước',
                                'Bắt đầu tăng tốc ôn luyện'
                            ],
                            'exam_reminder_7_days' => [
                                '7 ngày trước',
                                'Rà soát kiến thức'
                            ],
                            'exam_reminder_3_days' => [
                                '3 ngày trước',
                                'Ôn lại phần còn yếu'
                            ],
                            'exam_reminder_1_day' => [
                                '1 ngày trước',
                                'Chuẩn bị cho ngày thi'
                            ],
                        ];
                    @endphp

                    @foreach($examReminders as $key => $info)
                        <label class="reminder-option">
                            <span>
                                <span class="reminder-option-title">
                                    {{ $info[0] }}
                                </span>

                                <span class="reminder-option-desc">
                                    {{ $info[1] }}
                                </span>
                            </span>

                            <input type="hidden"
                                   name="{{ $key }}"
                                   value="0">

                            <input type="checkbox"
                                   name="{{ $key }}"
                                   value="1"
                                   @checked((bool) old(
                                       $key,
                                       $settings[$key]
                                   ))>
                        </label>
                    @endforeach

                </div>
            </section>


            {{-- 3. THÔNG BÁO HỌC TẬP --}}
            <section class="settings-card"
                     aria-labelledby="learning-notifications-title">

                <h2 class="settings-card-heading"
                    id="learning-notifications-title">
                    <span class="settings-heading-icon">♧</span>
                    Thông báo học tập
                </h2>

                @php
                    $learningSettings = [
                        'practice_reminders' => [
                            'Nhắc nhở luyện tập',
                            'Nhắc bạn quay lại bài thi đang làm dở.'
                        ],
                        'exam_result_notifications' => [
                            'Kết quả thi thử',
                            'Thông báo khi kết quả được chấm xong.'
                        ],
                        'goal_progress_notifications' => [
                            'Tiến độ mục tiêu',
                            'Thông báo khi đạt mục tiêu học tập.'
                        ],
                    ];
                @endphp

                @foreach($learningSettings as $key => $info)
                    <div class="setting-row">
                        <div class="setting-info">
                            <strong class="setting-title">
                                {{ $info[0] }}
                            </strong>

                            <div class="setting-desc">
                                {{ $info[1] }}
                            </div>
                        </div>

                        <label class="switch-control">
                            <input type="hidden"
                                   name="{{ $key }}"
                                   value="0">

                            <input type="checkbox"
                                   name="{{ $key }}"
                                   value="1"
                                   @checked((bool) old(
                                       $key,
                                       $settings[$key]
                                   ))>

                            <span class="switch-track"></span>
                        </label>
                    </div>
                @endforeach

            </section>


            {{-- 4. NHẮC NHỞ HỌC TẬP HẰNG NGÀY --}}
            <section class="settings-card"
                     aria-labelledby="daily-reminders-title">

                <h2 class="settings-card-heading"
                    id="daily-reminders-title">
                    <span class="settings-heading-icon">◷</span>
                    Nhắc nhở học tập hằng ngày
                </h2>

                <p class="settings-card-description">
                    Duy trì chuỗi học tập đều đặn giúp bạn giữ nhịp ôn luyện.
                </p>

                {{-- Bật thông báo trình duyệt --}}
                <div class="setting-row">
                    <div class="setting-info">
                        <strong class="setting-title">
                            Bật thông báo nhắc học trên trình duyệt
                        </strong>

                        <div class="setting-desc">
                            Nhận thông báo khi đến giờ học từ vựng hoặc luyện tập.
                        </div>
                    </div>

                    <label class="switch-control">
                        <input type="hidden"
                               name="browser_study_notifications"
                               value="0">

                        <input type="checkbox"
                               name="browser_study_notifications"
                               value="1"
                               @checked((bool) old(
                                   'browser_study_notifications',
                                   $settings['browser_study_notifications']
                               ))>

                        <span class="switch-track"></span>
                    </label>
                </div>

                {{-- Thời gian nhắc nhở --}}
                <div class="setting-row">
                    <div class="setting-info">
                        <strong class="setting-title">
                            Thời gian nhắc nhở mỗi ngày
                        </strong>

                        <div class="setting-desc">
                            Chọn khung giờ bạn thường rảnh để học.
                        </div>
                    </div>

                    <select class="settings-select"
                            name="daily_reminder_time">

                        @php
                            $reminderTimes = [
                                '19:00' => '19:00 Tối',
                                '20:00' => '20:00 Tối (Khuyên dùng)',
                                '21:00' => '21:00 Tối',
                                '07:00' => '07:00 Sáng',
                            ];
                        @endphp

                        @foreach($reminderTimes as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(
                                    old(
                                        'daily_reminder_time',
                                        $settings['daily_reminder_time']
                                    ) === $value
                                )
                            >
                                {{ $label }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- Gửi email tóm tắt tuần --}}
                <div class="setting-row">
                    <div class="setting-info">
                        <strong class="setting-title">
                            Gửi email tóm tắt tiến độ tuần
                        </strong>

                        <div class="setting-desc">
                            Báo cáo số câu đúng và số từ vựng đã nhớ vào sáng thứ Hai.
                        </div>
                    </div>

                    <label class="switch-control">
                        <input type="hidden"
                               name="weekly_progress_email"
                               value="0">

                        <input type="checkbox"
                               name="weekly_progress_email"
                               value="1"
                               @checked((bool) old(
                                   'weekly_progress_email',
                                   $settings['weekly_progress_email']
                               ))>

                        <span class="switch-track"></span>
                    </label>
                </div>

            </section>


            {{-- 5. ÂM THANH VÀ PHÒNG THI --}}
            <section class="settings-card"
                     aria-labelledby="exam-settings-title">

                <h2 class="settings-card-heading"
                    id="exam-settings-title">
                    <span class="settings-heading-icon">♫</span>
                    Cài đặt âm thanh &amp; Phòng thi
                </h2>

                {{-- Tốc độ Listening --}}
                <div class="setting-row">
                    <div class="setting-info">
                        <strong class="setting-title">
                            Tốc độ phát âm thanh mặc định (Listening)
                        </strong>

                        <div class="setting-desc">
                            Tốc độ phát mặc định khi luyện nghe.
                        </div>
                    </div>

                    <select class="settings-select"
                            name="listening_speed">

                        @php
                            $listeningSpeeds = [
                                '0.9' => '0.9x (Chậm nhẹ)',
                                '1.0' => '1.0x (Chuẩn đề thi)',
                                '1.1' => '1.1x (Thử thách)',
                                '1.2' => '1.2x (Nâng cao)',
                            ];
                        @endphp

                        @foreach($listeningSpeeds as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(
                                    old(
                                        'listening_speed',
                                        $settings['listening_speed']
                                    ) === $value
                                )
                            >
                                {{ $label }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- Tự động chuyển câu hỏi --}}
                <div class="setting-row">
                    <div class="setting-info">
                        <strong class="setting-title">
                            Tự động chuyển câu hỏi tiếp theo
                        </strong>

                        <div class="setting-desc">
                            Sau khi chọn đáp án, tự động chuyển sang câu kế tiếp.
                        </div>
                    </div>

                    <label class="switch-control">
                        <input type="hidden"
                               name="auto_next_question"
                               value="0">

                        <input type="checkbox"
                               name="auto_next_question"
                               value="1"
                               @checked((bool) old(
                                   'auto_next_question',
                                   $settings['auto_next_question']
                               ))>

                        <span class="switch-track"></span>
                    </label>
                </div>

            </section>


            {{-- 6. TÀI KHOẢN --}}
            <section class="settings-card"
                     aria-labelledby="linked-account-title">

                <h2 class="settings-card-heading"
                    id="linked-account-title">
                    <span class="settings-heading-icon">♙</span>
                    Tài khoản
                </h2>

                <div class="account-row">
                    <div class="account-info">
                        <div class="account-icon">🌐</div>

                        <div>
                            <strong class="setting-title">
                                Tài khoản đang đăng nhập
                            </strong>

                            <div class="account-email">
                                {{ auth()->user()->email }}
                            </div>
                        </div>
                    </div>

                    <span class="connected-badge">
                        Đã đăng nhập
                    </span>
                </div>
            </section>


            {{-- 7. NÚT LƯU --}}
            <div class="settings-actions">

                <x-button
                    variant="neutral"
                    href="{{ url('/luyen-tap') }}">
                    Quay lại
                </x-button>

                <x-button
                    variant="primary"
                    type="submit">
                    Lưu cài đặt
                </x-button>

            </div>

        </div>
    </form>
</div>
</x-layouts.app>
