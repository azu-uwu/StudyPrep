<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GoalController extends Controller
{
    // Hiển thị trang thiết lập mục tiêu
    public function edit(Request $request): View
    {
        $goal = Goal::where(
            'user_id',
            $request->user()->getKey()
        )
        ->orderByDesc('goal_id')
        ->first();

        return view(
            'pages.muc-tieu-thiet-lap',
            compact('goal')
        );
    }

    // Lưu hoặc cập nhật mục tiêu
    public function store(Request $request): RedirectResponse
    {
        // 1. Xác định loại bài thi
        $examType = $request->input('examType');

        $hasLR = in_array(
            $examType,
            ['lr', 'all'],
            true
        );

        $hasSW = in_array(
            $examType,
            ['sw', 'all'],
            true
        );

        // 2. Kiểm tra dữ liệu
        $rules = [
            'examType' => [
                'required',
                Rule::in(['lr', 'sw', 'all']),
            ],
        ];

        if ($hasLR) {
            $rules['listening'] = [
                'required',
                'integer',
                'between:5,495',
                'multiple_of:5',
            ];

            $rules['reading'] = [
                'required',
                'integer',
                'between:5,495',
                'multiple_of:5',
            ];

            $rules['examDateLR'] = [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ];
        }

        if ($hasSW) {
            $rules['speaking'] = [
                'required',
                'integer',
                'between:0,200',
                'multiple_of:10',
            ];

            $rules['writing'] = [
                'required',
                'integer',
                'between:0,200',
                'multiple_of:10',
            ];

            $rules['examDateSW'] = [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ];
        }

        $validated = $request->validate($rules, [
            'required' => 'Vui lòng nhập đầy đủ thông tin.',
            'integer' => 'Điểm phải là số nguyên.',
            'between' => 'Điểm nằm ngoài phạm vi cho phép.',
            'multiple_of' => 'Điểm không hợp lệ theo thang TOEIC.',
            'date_format' => 'Định dạng ngày thi không hợp lệ.',
            'after_or_equal' => 'Ngày thi không được trong quá khứ.',
        ]);

        // 3. Tìm mục tiêu thuộc tài khoản đăng nhập
        $userId = $request->user()->getKey();

        $goal = Goal::where('user_id', $userId)
            ->orderByDesc('goal_id')
            ->first();

        if (!$goal) {
            $goal = new Goal();
            $goal->user_id = $userId;
        }

        // 4. Lưu loại bài thi
        $goal->exam_type = $validated['examType'];

        // 5. Điểm Listening & Reading
        if ($hasLR) {
            $goal->target_listening =
                (int) $validated['listening'];

            $goal->target_reading =
                (int) $validated['reading'];

            $goal->target_date_lr =
                $validated['examDateLR'];
        } else {
            $goal->target_listening = null;
            $goal->target_reading = null;
            $goal->target_date_lr = null;
        }

        // 6. Điểm Speaking & Writing
        if ($hasSW) {
            $goal->target_speaking =
                (int) $validated['speaking'];

            $goal->target_writing =
                (int) $validated['writing'];

            $goal->target_date_sw =
                $validated['examDateSW'];
        } else {
            $goal->target_speaking = null;
            $goal->target_writing = null;
            $goal->target_date_sw = null;
        }

        // 7. Đồng bộ cột ngày thi cũ

        // 8. Lưu MySQL
        $goal->save();

        return redirect()
            ->route('muc-tieu-trang-chu')
            ->with(
                'success',
                'Thiết lập mục tiêu thành công!'
            );
    }

    public function index(\Illuminate\Http\Request $request)
    {
        // Lấy mục tiêu mới nhất của tài khoản đang đăng nhập
        $goal = \App\Models\Goal::where(
            'user_id',
            $request->user()->getKey()
        )
        ->orderByDesc('goal_id')
        ->first();

        // Xác định loại bài thi
        $examType = $goal?->exam_type;

        $showLR = in_array($examType, ['lr', 'all'], true);
        $showSW = in_array($examType, ['sw', 'all'], true);

        // Tổng mục tiêu Listening & Reading
        $totalLR = null;

        if (
            $showLR &&
            $goal->target_listening !== null &&
            $goal->target_reading !== null
        ) {
            $totalLR =
                $goal->target_listening +
                $goal->target_reading;
        }

        // Tổng mục tiêu Speaking & Writing
        $totalSW = null;

        if (
            $showSW &&
            $goal->target_speaking !== null &&
            $goal->target_writing !== null
        ) {
            $totalSW =
                $goal->target_speaking +
                $goal->target_writing;
        }

        // Hàm tính số ngày còn lại
        $calculateDays = function ($date) {
            if (!$date) {
                return null;
            }

            return (int) round(
                \Carbon\Carbon::today()->diffInDays(
                    \Carbon\Carbon::parse($date)->startOfDay(),
                    false
                )
            );
        };

        $daysLR = $showLR
            ? $calculateDays($goal->target_date_lr)
            : null;

        $daysSW = $showSW
            ? $calculateDays($goal->target_date_sw)
            : null;

        // Dữ liệu tổng quan mục tiêu
        $overview = [];

        if ($showLR) {
            $overview[] = [
                'name' => 'Listening & Reading',
                'total' => $totalLR,
                'max' => 990,
                'date' => $goal->target_date_lr,
                'days' => $daysLR,
            ];
        }

        if ($showSW) {
            $overview[] = [
                'name' => 'Speaking & Writing',
                'total' => $totalSW,
                'max' => 400,
                'date' => $goal->target_date_sw,
                'days' => $daysSW,
            ];
        }

        // Dữ liệu mục tiêu từng kỹ năng
        $skills = [];

        if ($showLR) {
            $skills[] = [
                'name' => 'Listening',
                'score' => $goal->target_listening,
                'max' => 495,
                'class' => 'listening',
            ];

            $skills[] = [
                'name' => 'Reading',
                'score' => $goal->target_reading,
                'max' => 495,
                'class' => 'reading',
            ];
        }

        if ($showSW) {
            $skills[] = [
                'name' => 'Speaking',
                'score' => $goal->target_speaking,
                'max' => 200,
                'class' => 'speaking',
            ];

            $skills[] = [
                'name' => 'Writing',
                'score' => $goal->target_writing,
                'max' => 200,
                'class' => 'writing',
            ];
        }

        return view('pages.muc-tieu-trang-chu', compact(
            'goal',
            'overview',
            'skills',
            'showLR',
            'showSW'
        ));
    }

}
