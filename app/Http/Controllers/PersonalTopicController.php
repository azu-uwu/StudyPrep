<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersonalTopicController extends Controller
{
    /**
     * Tạo chủ đề từ vựng cá nhân mới cho học viên đang đăng nhập (BE-008.2.1).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Vui lòng đăng nhập để thực hiện chức năng này.');
        }

        if (method_exists($user, 'isLocked') && $user->isLocked()) {
            abort(403, 'Tài khoản của bạn đã bị khóa.');
        }

        // Chuẩn hóa dữ liệu đầu vào: trim khoảng trắng ở đầu và cuối chuỗi
        $request->merge([
            'name' => is_string($request->input('name')) ? trim($request->input('name')) : $request->input('name'),
            'description' => is_string($request->input('description')) ? trim($request->input('description')) : $request->input('description'),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Vui lòng nhập tên chủ đề.',
            'name.string' => 'Tên chủ đề phải là chuỗi ký tự.',
            'name.max' => 'Tên chủ đề không được vượt quá 100 ký tự.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',
        ]);

        // Tuyệt đối không nhận user_id, topic_id hoặc parent_topic_id từ input của request
        $topic = Topic::create([
            'name' => $validated['name'],
            'description' => ! empty($validated['description']) ? $validated['description'] : null,
            'user_id' => $user->user_id ?? Auth::id(),
            'parent_topic_id' => null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo chủ đề thành công!',
                'topic' => [
                    'topic_id' => $topic->topic_id,
                    'name' => $topic->name,
                    'description' => $topic->description,
                    'user_id' => $topic->user_id,
                    'total_vocabularies' => 0,
                    'url' => route('tu-vung.show', $topic->topic_id),
                ],
            ], 201);
        }

        return redirect()->route('tu-vung')->with('success', 'Tạo chủ đề thành công!');
    }
}
