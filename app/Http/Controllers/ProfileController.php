<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // 1. Hiển thị trang hồ sơ
    public function show(Request $request): View
    {
        // Lấy tài khoản đang đăng nhập
        $user = $request->user();

        // Lấy mục tiêu L&R mới nhất của người dùng
        $lrGoal = DB::table('goals')
            ->where('user_id', $user->getKey())
            ->where(function ($query) {
                $query->where('target_listening', '>', 0)
                    ->orWhere('target_reading', '>', 0);
            })
            ->orderByDesc('goal_id')
            ->first();

        // Lấy mục tiêu S&W mới nhất của người dùng
        $swGoal = DB::table('goals')
            ->where('user_id', $user->getKey())
            ->where(function ($query) {
                $query->where('target_speaking', '>', 0)
                    ->orWhere('target_writing', '>', 0);
            })
            ->orderByDesc('goal_id')
            ->first();

        // Tính tổng điểm mục tiêu L&R
        $goalLRScore = $lrGoal
            ? (int) $lrGoal->target_listening
            + (int) $lrGoal->target_reading
            : null;

        // Tính tổng điểm mục tiêu S&W
        $goalSWScore = $swGoal
            ? (int) $swGoal->target_speaking
            + (int) $swGoal->target_writing
            : null;

        // Truyền dữ liệu sang giao diện
        return view('pages.ho-so', compact(
            'user',
            'goalLRScore',
            'goalSWScore'
        ));
    }

    // 2. Cập nhật thông tin cá nhân
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'fullName' => ['required', 'string', 'max:100'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->getKey(), 'user_id'),
            ],

            'birthday' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'phone' => ['nullable', 'string', 'max:25'],
            'gender' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        // Cập nhật trực tiếp trong bảng users
        $emailChanged = $user->email !== $data['email'];

        $user->full_name = $data['fullName'];
        $user->email = $data['email'];
        $user->birthday = $data['birthday'] ?? null;
        $user->phone = $data['phone'] ?? null;
        $user->gender = $data['gender'] ?? null;
        $user->city = $data['city'] ?? null;

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin thành công!',
            'data' => [
                'name' => $user->full_name,
                'email' => $user->email,
                'email_verified' =>
                    $user->email_verified_at !== null,
            ],
        ]);
    }



    // 3. Đổi mật khẩu
    public function changePassword(Request $request): JsonResponse
    {
        // 1. Kiểm tra dữ liệu người dùng nhập
        $data = $request->validate([
            'oldPassword' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                'different:oldPassword',
            ],
        ], [
            'oldPassword.required' =>
                'Vui lòng nhập mật khẩu hiện tại.',

            'password.required' =>
                'Vui lòng nhập mật khẩu mới.',

            'password.min' =>
                'Mật khẩu mới phải có ít nhất 8 ký tự.',

            'password.max' =>
                'Mật khẩu mới không được quá 255 ký tự.',

            'password.confirmed' =>
                'Mật khẩu xác nhận không khớp.',

            'password.different' =>
                'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ]);

        // 2. Lấy tài khoản đang đăng nhập
        $user = $request->user();

        // 3. Kiểm tra tài khoản có mật khẩu hay chưa
        if (empty($user->password_hash)) {
            throw ValidationException::withMessages([
                'oldPassword' =>
                    'Tài khoản chưa thiết lập mật khẩu.',
            ]);
        }

        // 4. Kiểm tra mật khẩu hiện tại
        if (!Hash::check(
            $data['oldPassword'],
            $user->password_hash
        )) {
            throw ValidationException::withMessages([
                'oldPassword' =>
                    'Mật khẩu hiện tại không chính xác.',
            ]);
        }

        // 5. Lưu mật khẩu mới
        $changedAt = now();

        DB::transaction(function () use ($user, $data, $changedAt) {
            $user->password_hash = Hash::make($data['password']);
            $user->password_changed_at = $changedAt;
            $user->save();
        });

        // 6. Trả kết quả thành công
        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!',
            'changed_at' => $changedAt->format('d/m/Y'),
        ]);
    }


    // 4. Upload ảnh đại diện
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048',
            ],
        ]);

        $user = $request->user();

        // Lưu ảnh vào thư mục public
        $path = $request->file('avatar')
            ->store('avatars', 'public');

        // Lấy đường dẫn ảnh
        $avatarUrl = Storage::disk('public')->url($path);

        // Cập nhật vào bảng users
        $user->avatar_url = $avatarUrl;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật ảnh đại diện thành công!',
            'avatar_url' => $avatarUrl,
        ]);
    }

}
