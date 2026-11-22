<?php

namespace App\Http\Controllers;

use App\Models\StudySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudySettingController extends Controller
{
    // Cài đặt mặc định
    private const DEFAULTS = [
        'system_notifications' => true,
        'notifications_enabled' => true,

        'exam_reminder_30_days' => true,
        'exam_reminder_7_days' => true,
        'exam_reminder_3_days' => true,
        'exam_reminder_1_day' => true,

        'practice_reminders' => true,
        'exam_result_notifications' => true,
        'goal_progress_notifications' => true,

        'browser_study_notifications' => true,
        'daily_reminder_time' => '20:00',
        'weekly_progress_email' => true,

        'listening_speed' => '1.0',
        'auto_next_question' => true,
    ];

    // Các trường bật/tắt
    private const BOOLEAN_KEYS = [
        'system_notifications',
        'notifications_enabled',

        'exam_reminder_30_days',
        'exam_reminder_7_days',
        'exam_reminder_3_days',
        'exam_reminder_1_day',

        'practice_reminders',
        'exam_result_notifications',
        'goal_progress_notifications',

        'browser_study_notifications',
        'weekly_progress_email',

        'auto_next_question',
    ];

    // 1. Hiển thị trang cài đặt
    public function index(Request $request): View
    {
        $userId = $request->user()->getKey();

        // Lấy cài đặt đã lưu của người dùng
        $record = StudySetting::where(
            'user_id',
            $userId
        )->first();

        // Nếu chưa có thì sử dụng giá trị mặc định
        $settings = array_replace(
            self::DEFAULTS,
            $record?->preferences ?? []
        );

        return view(
            'pages.cai-dat',
            compact('settings')
        );
    }

    // 2. Lưu cài đặt
    public function update(Request $request): RedirectResponse
    {
        // Kiểm tra dữ liệu
        $validated = $request->validate([
            'daily_reminder_time' => [
                'required',
                Rule::in([
                    '07:00',
                    '19:00',
                    '20:00',
                    '21:00',
                ]),
            ],

            'listening_speed' => [
                'required',
                Rule::in([
                    '0.9',
                    '1.0',
                    '1.1',
                    '1.2',
                ]),
            ],
        ], [
            'daily_reminder_time.required' =>
                'Vui lòng chọn giờ nhắc học.',

            'daily_reminder_time.in' =>
                'Giờ nhắc học không hợp lệ.',

            'listening_speed.required' =>
                'Vui lòng chọn tốc độ nghe.',

            'listening_speed.in' =>
                'Tốc độ nghe không hợp lệ.',
        ]);

        // 3. Xử lý checkbox
        $settings = [];

        foreach (self::BOOLEAN_KEYS as $key) {
            $settings[$key] = $request->boolean($key);
        }

        // 4. Lưu giờ nhắc học
        $settings['daily_reminder_time'] =
            $validated['daily_reminder_time'];

        // 5. Lưu tốc độ nghe
        $settings['listening_speed'] =
            $validated['listening_speed'];

        // 6. Lấy ID tài khoản đang đăng nhập
        $userId = $request->user()->getKey();

        // 7. Lưu vào MySQL
        StudySetting::updateOrCreate(
            [
                'user_id' => $userId,
            ],
            [
                'preferences' => $settings,
            ]
        );

        // 8. Thông báo thành công
        return redirect()
            ->route('cai-dat')
            ->with(
                'success',
                'Đã lưu các tùy chọn cài đặt.'
            );
    }
}
