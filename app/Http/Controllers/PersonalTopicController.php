<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Vocabulary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    /**
     * Đổi tên chủ đề cá nhân (BE-008.2.2).
     */
    public function update(Request $request, int|string $topic): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Vui lòng đăng nhập để thực hiện chức năng này.');
        }

        if (method_exists($user, 'isLocked') && $user->isLocked()) {
            abort(403, 'Tài khoản của bạn đã bị khóa.');
        }

        $topicModel = Topic::findOrFail((int) $topic);

        // Kiểm tra phân quyền: Không cho phép sửa chủ đề hệ thống hoặc chủ đề người khác
        if ($topicModel->user_id === null) {
            abort(403, 'Không thể chỉnh sửa chủ đề hệ thống.');
        }

        $authUserId = (int) ($user->user_id ?? Auth::id());
        if ((int) $topicModel->user_id !== $authUserId) {
            abort(403, 'Bạn không có quyền chỉnh sửa chủ đề này.');
        }

        // Chuẩn hóa dữ liệu đầu vào: trim khoảng trắng ở đầu và cuối chuỗi
        $request->merge([
            'name' => is_string($request->input('name')) ? trim($request->input('name')) : $request->input('name'),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Vui lòng nhập tên chủ đề.',
            'name.string' => 'Tên chủ đề phải là chuỗi ký tự.',
            'name.max' => 'Tên chủ đề không được vượt quá 100 ký tự.',
        ]);

        // Cập nhật tên chủ đề. Giữ nguyên user_id, parent_topic_id, description, từ vựng và tiến độ học
        $topicModel->name = $validated['name'];
        $topicModel->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật chủ đề thành công!',
                'topic' => [
                    'topic_id' => $topicModel->topic_id,
                    'name' => $topicModel->name,
                    'description' => $topicModel->description,
                    'user_id' => $topicModel->user_id,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật chủ đề thành công!');
    }

    /**
     * Xóa hàng loạt từ vựng được chọn trong chủ đề cá nhân (BE-008.2.2).
     */
    public function bulkDeleteVocabularies(Request $request, int|string $topic): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Vui lòng đăng nhập để thực hiện chức năng này.');
        }

        if (method_exists($user, 'isLocked') && $user->isLocked()) {
            abort(403, 'Tài khoản của bạn đã bị khóa.');
        }

        $topicModel = Topic::findOrFail((int) $topic);

        // Kiểm tra phân quyền: Không cho phép xóa từ thuộc chủ đề hệ thống hoặc người khác
        if ($topicModel->user_id === null) {
            abort(403, 'Không thể xóa từ vựng thuộc chủ đề hệ thống.');
        }

        $authUserId = (int) ($user->user_id ?? Auth::id());
        if ((int) $topicModel->user_id !== $authUserId) {
            abort(403, 'Bạn không có quyền thao tác trên chủ đề này.');
        }

        $validated = $request->validate([
            'vocabulary_ids' => ['required', 'array', 'min:1'],
            'vocabulary_ids.*' => ['required', 'integer', 'distinct', 'min:1'],
        ], [
            'vocabulary_ids.required' => 'Vui lòng chọn ít nhất một từ vựng để xóa.',
            'vocabulary_ids.array' => 'Danh sách từ vựng không hợp lệ.',
            'vocabulary_ids.min' => 'Vui lòng chọn ít nhất một từ vựng để xóa.',
            'vocabulary_ids.*.required' => 'Mã từ vựng không hợp lệ.',
            'vocabulary_ids.*.integer' => 'Mã từ vựng phải là số nguyên.',
            'vocabulary_ids.*.distinct' => 'Danh sách từ vựng chứa ID trùng lặp.',
            'vocabulary_ids.*.min' => 'Mã từ vựng không hợp lệ.',
        ]);

        $requestedIds = array_values(array_unique(array_map('intval', $validated['vocabulary_ids'])));

        $deletedCount = DB::transaction(function () use ($topicModel, $requestedIds): int {
            // Khóa bản ghi và kiểm tra toàn bộ ID thực sự thuộc chủ đề cá nhân này
            $foundVocabs = Vocabulary::where('topic_id', $topicModel->topic_id)
                ->whereIn('vocabulary_id', $requestedIds)
                ->lockForUpdate()
                ->get();

            // Nếu số lượng tìm thấy không khớp chính xác danh sách yêu cầu,
            // nghĩa là có ID không tồn tại hoặc thuộc chủ đề khác -> Từ chối toàn bộ request
            if ($foundVocabs->count() !== count($requestedIds)) {
                abort(422, 'Một hoặc nhiều từ vựng được chọn không hợp lệ hoặc không thuộc chủ đề này.');
            }

            // Thực hiện xóa đúng các từ vựng được chọn (không xóa các từ khác, không xóa chủ đề)
            // CSDL đã có ràng buộc FK ON DELETE CASCADE nên user_vocabulary liên quan sẽ được tự động xóa
            return Vocabulary::where('topic_id', $topicModel->topic_id)
                ->whereIn('vocabulary_id', $requestedIds)
                ->delete();
        });

        $remainingCount = $topicModel->vocabularies()->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa các từ vựng được chọn!',
                'deleted_count' => $deletedCount,
                'deleted_ids' => $requestedIds,
                'remaining_count' => $remainingCount,
            ]);
        }

        return redirect()->back()->with('success', 'Đã xóa các từ vựng được chọn!');
    }
}
