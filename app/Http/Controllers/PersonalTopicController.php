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

    /**
     * Thêm từ vựng mới vào chủ đề cá nhân của học viên đang đăng nhập (BE-008.2.4).
     */
    public function storeVocabulary(Request $request, int|string $topic): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Vui lòng đăng nhập để thực hiện chức năng này.');
        }

        if (method_exists($user, 'isLocked') && $user->isLocked()) {
            abort(403, 'Tài khoản của bạn đã bị khóa.');
        }

        $topicModel = Topic::findOrFail((int) $topic);

        // Kiểm tra phân quyền: Không cho phép thêm từ vào chủ đề hệ thống
        if ($topicModel->user_id === null) {
            abort(403, 'Không thể thêm từ vựng vào chủ đề hệ thống.');
        }

        // Kiểm tra phân quyền: Không cho phép thêm từ vào chủ đề của học viên khác
        $authUserId = (int) ($user->user_id ?? Auth::id());
        if ((int) $topicModel->user_id !== $authUserId) {
            abort(403, 'Bạn không có quyền thêm từ vựng vào chủ đề này.');
        }

        // Chuẩn hóa dữ liệu đầu vào: trim khoảng trắng ở đầu và cuối chuỗi, chuỗi rỗng chuyển về null
        $request->merge([
            'word' => is_string($request->input('word')) ? trim($request->input('word')) : $request->input('word'),
            'meaning_vi' => is_string($request->input('meaning_vi')) ? trim($request->input('meaning_vi')) : $request->input('meaning_vi'),
            'part_of_speech' => is_string($request->input('part_of_speech')) && trim($request->input('part_of_speech')) !== '' ? trim($request->input('part_of_speech')) : null,
            'pronunciation' => is_string($request->input('pronunciation')) && trim($request->input('pronunciation')) !== '' ? trim($request->input('pronunciation')) : null,
            'example_en' => is_string($request->input('example_en')) && trim($request->input('example_en')) !== '' ? trim($request->input('example_en')) : null,
            'example_vi' => is_string($request->input('example_vi')) && trim($request->input('example_vi')) !== '' ? trim($request->input('example_vi')) : null,
            'image_url' => is_string($request->input('image_url')) && trim($request->input('image_url')) !== '' ? trim($request->input('image_url')) : null,
        ]);

        $validated = $request->validate([
            'word' => ['required', 'string', 'max:150'],
            'meaning_vi' => ['required', 'string'],
            'part_of_speech' => ['nullable', 'string', 'max:30'],
            'pronunciation' => ['nullable', 'string', 'max:150'],
            'example_en' => ['nullable', 'string'],
            'example_vi' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:500', 'url'],
        ], [
            'word.required' => 'Vui lòng nhập từ tiếng Anh.',
            'word.string' => 'Từ tiếng Anh phải là chuỗi ký tự.',
            'word.max' => 'Từ tiếng Anh không được vượt quá 150 ký tự.',
            'meaning_vi.required' => 'Vui lòng nhập nghĩa tiếng Việt.',
            'meaning_vi.string' => 'Nghĩa tiếng Việt phải là chuỗi ký tự.',
            'part_of_speech.string' => 'Loại từ phải là chuỗi ký tự.',
            'part_of_speech.max' => 'Loại từ không được vượt quá 30 ký tự.',
            'pronunciation.string' => 'Phiên âm phải là chuỗi ký tự.',
            'pronunciation.max' => 'Phiên âm không được vượt quá 150 ký tự.',
            'example_en.string' => 'Ví dụ tiếng Anh phải là chuỗi ký tự.',
            'example_vi.string' => 'Nghĩa của ví dụ phải là chuỗi ký tự.',
            'image_url.string' => 'URL hình ảnh phải là chuỗi ký tự.',
            'image_url.max' => 'URL hình ảnh không được vượt quá 500 ký tự.',
            'image_url.url' => 'URL hình ảnh không hợp lệ (phải bắt đầu bằng http:// hoặc https://).',
        ]);

        // Tạo bản ghi mới trong bảng vocabularies với topic_id từ chủ đề đã xác thực
        // Các trường tùy chọn để null nếu không nhập
        // Không tạo bản ghi trong user_vocabulary, không cập nhật tiến độ hay năng lượng
        $vocabulary = Vocabulary::create([
            'topic_id' => $topicModel->topic_id,
            'word' => $validated['word'],
            'meaning_vi' => $validated['meaning_vi'],
            'part_of_speech' => ! empty($validated['part_of_speech']) ? $validated['part_of_speech'] : null,
            'pronunciation' => ! empty($validated['pronunciation']) ? $validated['pronunciation'] : null,
            'example_en' => ! empty($validated['example_en']) ? $validated['example_en'] : null,
            'example_vi' => ! empty($validated['example_vi']) ? $validated['example_vi'] : null,
            'image_url' => ! empty($validated['image_url']) ? $validated['image_url'] : null,
        ]);

        $totalCount = $topicModel->vocabularies()->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Thêm từ vựng thành công!',
                'vocabulary' => [
                    'vocabulary_id' => $vocabulary->vocabulary_id,
                    'topic_id' => $vocabulary->topic_id,
                    'word' => $vocabulary->word,
                    'meaning_vi' => $vocabulary->clean_meaning_vi,
                    'part_of_speech' => $vocabulary->clean_pos,
                    'pronunciation' => $vocabulary->pronunciation ?? '',
                    'example_en' => $vocabulary->example_en ?? '',
                    'example_vi' => $vocabulary->example_vi ?? '',
                    'image_url' => $vocabulary->image_url,
                    'cefr_level' => '',
                    'status' => 'new',
                ],
                'total_vocabularies' => $totalCount,
            ], 201);
        }

        return redirect()->back()->with('success', 'Thêm từ vựng thành công!');
    }

    /**
     * Chỉnh sửa một từ vựng trong chủ đề cá nhân của học viên đang đăng nhập (BE-008.2.5).
     */
    public function updateVocabulary(Request $request, int|string $topic, int|string $vocabulary): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Vui lòng đăng nhập để thực hiện chức năng này.');
        }

        if (method_exists($user, 'isLocked') && $user->isLocked()) {
            abort(403, 'Tài khoản của bạn đã bị khóa.');
        }

        $topicModel = Topic::findOrFail((int) $topic);

        // Kiểm tra phân quyền: Không cho phép sửa từ thuộc chủ đề hệ thống
        if ($topicModel->user_id === null) {
            abort(403, 'Không thể chỉnh sửa từ vựng thuộc chủ đề hệ thống.');
        }

        // Kiểm tra phân quyền: Không cho phép sửa từ thuộc chủ đề của học viên khác
        $authUserId = (int) ($user->user_id ?? Auth::id());
        if ((int) $topicModel->user_id !== $authUserId) {
            abort(403, 'Bạn không có quyền chỉnh sửa từ vựng trong chủ đề này.');
        }

        // Tìm từ vựng thuộc đúng chủ đề cá nhân này (nếu không khớp hoặc không có thì 404)
        $vocabModel = Vocabulary::where('topic_id', $topicModel->topic_id)
            ->where('vocabulary_id', (int) $vocabulary)
            ->firstOrFail();

        // Chuẩn hóa dữ liệu đầu vào: trim khoảng trắng ở đầu và cuối chuỗi, chuỗi rỗng chuyển về null
        $request->merge([
            'word' => is_string($request->input('word')) ? trim($request->input('word')) : $request->input('word'),
            'meaning_vi' => is_string($request->input('meaning_vi')) ? trim($request->input('meaning_vi')) : $request->input('meaning_vi'),
            'part_of_speech' => is_string($request->input('part_of_speech')) && trim($request->input('part_of_speech')) !== '' ? trim($request->input('part_of_speech')) : null,
            'pronunciation' => is_string($request->input('pronunciation')) && trim($request->input('pronunciation')) !== '' ? trim($request->input('pronunciation')) : null,
            'example_en' => is_string($request->input('example_en')) && trim($request->input('example_en')) !== '' ? trim($request->input('example_en')) : null,
            'example_vi' => is_string($request->input('example_vi')) && trim($request->input('example_vi')) !== '' ? trim($request->input('example_vi')) : null,
            'image_url' => is_string($request->input('image_url')) && trim($request->input('image_url')) !== '' ? trim($request->input('image_url')) : null,
        ]);

        $validated = $request->validate([
            'word' => ['required', 'string', 'max:150'],
            'meaning_vi' => ['required', 'string'],
            'part_of_speech' => ['nullable', 'string', 'max:30'],
            'pronunciation' => ['nullable', 'string', 'max:150'],
            'example_en' => ['nullable', 'string'],
            'example_vi' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:500', 'url'],
        ], [
            'word.required' => 'Vui lòng nhập từ tiếng Anh.',
            'word.string' => 'Từ tiếng Anh phải là chuỗi ký tự.',
            'word.max' => 'Từ tiếng Anh không được vượt quá 150 ký tự.',
            'meaning_vi.required' => 'Vui lòng nhập nghĩa tiếng Việt.',
            'meaning_vi.string' => 'Nghĩa tiếng Việt phải là chuỗi ký tự.',
            'part_of_speech.string' => 'Loại từ phải là chuỗi ký tự.',
            'part_of_speech.max' => 'Loại từ không được vượt quá 30 ký tự.',
            'pronunciation.string' => 'Phiên âm phải là chuỗi ký tự.',
            'pronunciation.max' => 'Phiên âm không được vượt quá 150 ký tự.',
            'example_en.string' => 'Ví dụ tiếng Anh phải là chuỗi ký tự.',
            'example_vi.string' => 'Nghĩa của ví dụ phải là chuỗi ký tự.',
            'image_url.string' => 'URL hình ảnh phải là chuỗi ký tự.',
            'image_url.max' => 'URL hình ảnh không được vượt quá 500 ký tự.',
            'image_url.url' => 'URL hình ảnh không hợp lệ (phải bắt đầu bằng http:// hoặc https://).',
        ]);

        // Cập nhật bản ghi trong bảng vocabularies
        // Tuyệt đối không thay đổi vocabulary_id, topic_id hoặc user_id
        // Không sửa user_vocabulary, không sửa tiến độ hay năng lượng
        $vocabModel->update([
            'word' => $validated['word'],
            'meaning_vi' => $validated['meaning_vi'],
            'part_of_speech' => ! empty($validated['part_of_speech']) ? $validated['part_of_speech'] : null,
            'pronunciation' => ! empty($validated['pronunciation']) ? $validated['pronunciation'] : null,
            'example_en' => ! empty($validated['example_en']) ? $validated['example_en'] : null,
            'example_vi' => ! empty($validated['example_vi']) ? $validated['example_vi'] : null,
            'image_url' => ! empty($validated['image_url']) ? $validated['image_url'] : null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật từ vựng thành công!',
                'vocabulary' => [
                    'vocabulary_id' => $vocabModel->vocabulary_id,
                    'topic_id' => $vocabModel->topic_id,
                    'word' => $vocabModel->word,
                    'meaning_vi' => $vocabModel->clean_meaning_vi,
                    'part_of_speech' => $vocabModel->clean_pos,
                    'pronunciation' => $vocabModel->pronunciation ?? '',
                    'example_en' => $vocabModel->example_en ?? '',
                    'example_vi' => $vocabModel->example_vi ?? '',
                    'image_url' => $vocabModel->image_url,
                    'cefr_level' => '',
                    'status' => 'new',
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật từ vựng thành công!');
    }

    /**
     * Xóa một từ vựng khỏi chủ đề cá nhân của học viên đang đăng nhập (BE-008.2.5).
     */
    public function destroyVocabulary(Request $request, int|string $topic, int|string $vocabulary): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Vui lòng đăng nhập để thực hiện chức năng này.');
        }

        if (method_exists($user, 'isLocked') && $user->isLocked()) {
            abort(403, 'Tài khoản của bạn đã bị khóa.');
        }

        $topicModel = Topic::findOrFail((int) $topic);

        // Kiểm tra phân quyền: Không cho phép xóa từ thuộc chủ đề hệ thống
        if ($topicModel->user_id === null) {
            abort(403, 'Không thể xóa từ vựng thuộc chủ đề hệ thống.');
        }

        // Kiểm tra phân quyền: Không cho phép xóa từ thuộc chủ đề của học viên khác
        $authUserId = (int) ($user->user_id ?? Auth::id());
        if ((int) $topicModel->user_id !== $authUserId) {
            abort(403, 'Bạn không có quyền xóa từ vựng trong chủ đề này.');
        }

        // Tìm đúng từ vựng thuộc chủ đề này
        $vocabModel = Vocabulary::where('topic_id', $topicModel->topic_id)
            ->where('vocabulary_id', (int) $vocabulary)
            ->firstOrFail();

        $deletedId = $vocabModel->vocabulary_id;

        // Xóa từ vựng. MySQL ON DELETE CASCADE sẽ tự động xóa tiến độ tương ứng trong user_vocabulary
        $vocabModel->delete();

        $remainingCount = $topicModel->vocabularies()->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa từ vựng thành công!',
                'deleted_id' => $deletedId,
                'remaining_count' => $remainingCount,
            ]);
        }

        return redirect()->back()->with('success', 'Đã xóa từ vựng thành công!');
    }

    /**
     * Xóa chủ đề từ vựng cá nhân của học viên đang đăng nhập (BE-008.2.3).
     */
    public function destroy(Request $request, int|string $topic): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Vui lòng đăng nhập để thực hiện chức năng này.');
        }

        if (method_exists($user, 'isLocked') && $user->isLocked()) {
            abort(403, 'Tài khoản của bạn đã bị khóa.');
        }

        $topicModel = Topic::findOrFail((int) $topic);

        // Kiểm tra phân quyền: Không cho phép xóa chủ đề hệ thống (user_id === null)
        if ($topicModel->user_id === null) {
            abort(403, 'Không thể xóa chủ đề hệ thống.');
        }

        // Kiểm tra phân quyền: Không cho phép xóa chủ đề của học viên khác
        $authUserId = (int) ($user->user_id ?? Auth::id());
        if ((int) $topicModel->user_id !== $authUserId) {
            abort(403, 'Bạn không có quyền xóa chủ đề này.');
        }

        DB::transaction(function () use ($topicModel): void {
            // Khóa bản ghi để bảo đảm an toàn khi xóa
            $topicToDelete = Topic::where('topic_id', $topicModel->topic_id)
                ->lockForUpdate()
                ->first();

            if ($topicToDelete) {
                // Tận dụng ON DELETE CASCADE của MySQL để tự động xóa vocabularies và user_vocabulary
                // Và ON DELETE SET NULL cho parent_topic_id để không ảnh hưởng chủ đề con
                $topicToDelete->delete();
            }
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa chủ đề thành công!',
                'deleted_topic_id' => (int) $topic,
                'redirect_url' => route('tu-vung'),
            ]);
        }

        return redirect()->route('tu-vung')->with('success', 'Đã xóa chủ đề thành công!');
    }
}
