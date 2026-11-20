<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\UserVocabulary;
use App\Models\Vocabulary;
use App\Services\EnergyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VocabularyPracticeController extends Controller
{
    /**
     * Tìm chủ đề theo ID và kiểm tra quyền truy cập:
     * - user_id IS NULL: Chủ đề hệ thống, tất cả người dùng đều có quyền xem.
     * - user_id = Auth::id(): Chủ đề cá nhân của người dùng đang đăng nhập.
     * - Ngược lại: Từ chối truy cập (HTTP 403).
     */
    private function findAccessibleTopic(string|int $topicId): ?Topic
    {
        $topic = Topic::query()
            ->withCount('vocabularies')
            ->find((int) $topicId);

        if ($topic !== null && $topic->user_id !== null && $topic->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập chủ đề này.');
        }

        return $topic;
    }

    /**
     * So sánh câu trả lời của người học với từ vựng chuẩn trong MySQL.
     * Chuẩn hóa: loại bỏ khoảng trắng thừa, chuyển thành chữ thường, chỉ giữ ký tự chữ cái và số.
     */
    private function isAnswerCorrect(string $userAnswer, string $correctWord): bool
    {
        $cleanUser = preg_replace('/[^a-z0-9]/', '', mb_strtolower(trim($userAnswer)));
        $cleanTarget = preg_replace('/[^a-z0-9]/', '', mb_strtolower(trim($correctWord)));

        return $cleanUser !== '' && $cleanUser === $cleanTarget;
    }

    /**
     * Ánh xạ dữ liệu Eloquent Topic sang định dạng mảng mà View đang dùng.
     *
     * Tiến độ học được tính thực tế từ bảng user_vocabulary:
     * Số từ có is_done = 1 của người dùng / Tổng số từ trong chủ đề * 100%.
     *
     * @return array<string, mixed>
     */
    private function mapTopicToViewData(Topic $topic): array
    {
        $totalVocabularies = (int) ($topic->vocabularies_count ?? $topic->vocabularies()->count());

        $completedCount = 0;
        $practicedCount = 0;

        if (Auth::check() && $totalVocabularies > 0) {
            $userId = Auth::id();

            $completedCount = UserVocabulary::query()
                ->where('user_id', $userId)
                ->where('is_done', 1)
                ->whereIn('vocabulary_id', function ($query) use ($topic) {
                    $query->select('vocabulary_id')
                        ->from('vocabularies')
                        ->where('topic_id', $topic->topic_id);
                })
                ->count();

            $practicedCount = UserVocabulary::query()
                ->where('user_id', $userId)
                ->whereIn('vocabulary_id', function ($query) use ($topic) {
                    $query->select('vocabulary_id')
                        ->from('vocabularies')
                        ->where('topic_id', $topic->topic_id);
                })
                ->count();
        }

        $completedPct = $totalVocabularies > 0 ? (int) round(($completedCount / $totalVocabularies) * 100) : 0;
        $accuracyPct = $practicedCount > 0 ? (int) round(($completedCount / $practicedCount) * 100) : 0;
        $masteryScore = round(($completedPct / 20), 1);

        return [
            'topic_id' => $topic->topic_id,
            'name' => $topic->name,
            'display_name' => $topic->name,
            'description' => $topic->description ?? '',
            // Thumbnail: DB không có cột này → trả null để View dùng fallback badge SVG
            'thumbnail' => null,
            'thumbnail_type' => 'badge',
            'thumbnail_label' => mb_strtoupper((string) $topic->name),
            // Số lượng từ: dùng vocabularies_count từ withCount(), hoặc total() nếu đã load
            'total_vocabularies' => $totalVocabularies,
            // DB không lưu riêng danh sách cấp độ CEFR theo chủ đề → [] để View ẩn section
            'cefr_levels' => [],
            // Tiến độ: tính từ bảng user_vocabulary của người dùng hiện tại
            'progress' => [
                'completed_pct' => $completedPct,
                'mastery_score' => $masteryScore,
                'accuracy_pct' => $accuracyPct,
            ],
        ];
    }

    /**
     * Ánh xạ Eloquent Vocabulary sang định dạng mảng mà View, Component và JS đang dùng.
     *
     * @return array<string, mixed>
     */
    private function mapVocabularyToViewData(Vocabulary $vocabulary, ?UserVocabulary $progress = null): array
    {
        $status = 'new';
        if ($progress !== null) {
            $status = $progress->is_done ? 'mastered' : 'review';
        }

        return [
            'vocabulary_id' => $vocabulary->vocabulary_id,
            'topic_id' => $vocabulary->topic_id,
            'word' => $vocabulary->word,
            'meaning_vi' => $vocabulary->clean_meaning_vi,
            'part_of_speech' => $vocabulary->clean_pos,
            'pronunciation' => $vocabulary->pronunciation ?? '',
            'example_en' => $vocabulary->example_en ?? '',
            'example_vi' => $vocabulary->example_vi ?? '',
            'cefr_level' => '',
            'status' => $status,
            'image_url' => $vocabulary->image_url ?? null,
        ];
    }

    /**
     * Tính số lượng từ theo từng tab bộ lọc dựa trên trường status trong mảng đã ánh xạ.
     *
     * @param  array<int, array<string, mixed>>  $vocabularies
     * @return array{all: int, mastered: int, review: int, skipped: int}
     */
    private function calculateFilterCounts(array $vocabularies): array
    {
        $mastered = 0;
        $review = 0;
        $skipped = 0;

        foreach ($vocabularies as $vocab) {
            $status = $vocab['status'] ?? 'new';
            if ($status === 'mastered') {
                $mastered++;
            } elseif ($status === 'review') {
                $review++;
            } elseif ($status === 'skipped') {
                $skipped++;
            }
        }

        return [
            'all' => count($vocabularies),
            'mastered' => $mastered,
            'review' => $review,
            'skipped' => $skipped,
        ];
    }

    /**
     * Màn hình chính danh mục Từ vựng (/tu-vung).
     *
     * - Chủ đề phổ biến: Chỉ hiển thị chủ đề hệ thống có user_id IS NULL.
     * - Chủ đề của tôi: Chỉ hiển thị chủ đề cá nhân có user_id = Auth::id() khi đã đăng nhập.
     */
    public function index(): View
    {
        $popularTopics = Topic::query()
            ->whereNull('user_id')
            ->withCount('vocabularies')
            ->orderBy('topic_id')
            ->get()
            ->map(fn (Topic $topic): array => $this->mapTopicToViewData($topic))
            ->values()
            ->all();

        $myTopics = [];
        if (Auth::check()) {
            $myTopics = Topic::query()
                ->where('user_id', Auth::id())
                ->withCount('vocabularies')
                ->orderBy('topic_id')
                ->get()
                ->map(fn (Topic $topic): array => $this->mapTopicToViewData($topic))
                ->values()
                ->all();
        }

        $stats = [
            'learned' => 0,
            'mastered' => 0,
            'forgotten' => 0,
            'mastery_score' => 0.0,
        ];

        if (Auth::check()) {
            $userId = Auth::id();
            $learned = UserVocabulary::query()->where('user_id', $userId)->count();
            $mastered = UserVocabulary::query()->where('user_id', $userId)->where('is_done', 1)->count();
            $forgotten = UserVocabulary::query()->where('user_id', $userId)->where('is_done', 0)->count();
            $stats = [
                'learned' => $learned,
                'mastered' => $mastered,
                'forgotten' => $forgotten,
                'mastery_score' => $learned > 0 ? round(($mastered / $learned) * 5.0, 1) : 0.0,
            ];
        }

        return view('pages.tu-vung', [
            'popularTopics' => $popularTopics,
            'myTopics' => $myTopics,
            'stats' => $stats,
        ]);
    }

    /**
     * Màn hình Danh sách từ vựng theo Chủ đề (/tu-vung/{topicId}).
     */
    public function show(string|int $topicId): View
    {
        $topic = $this->findAccessibleTopic($topicId);

        $vocabularies = [];
        $filterCounts = ['all' => 0, 'mastered' => 0, 'review' => 0, 'skipped' => 0];

        if ($topic !== null) {
            $vocabQuery = $topic->vocabularies()->orderBy('vocabulary_id');
            if (Auth::check()) {
                $vocabQuery->with(['userProgress' => function ($q) {
                    $q->where('user_id', Auth::id());
                }]);
            }
            $vocabModels = $vocabQuery->get();

            $vocabularies = $vocabModels->map(function (Vocabulary $v): array {
                $progress = Auth::check() ? $v->userProgress->first() : null;

                return $this->mapVocabularyToViewData($v, $progress);
            })->values()->all();

            $filterCounts = $this->calculateFilterCounts($vocabularies);
        }

        return view('pages.tu-vung-danh-sach', [
            'topicId' => $topicId,
            'topic' => $topic !== null ? $this->mapTopicToViewData($topic) : null,
            'vocabularies' => $vocabularies,
            'filterCounts' => $filterCounts,
        ]);
    }

    /**
     * Màn hình Chọn từ muốn học (/tu-vung/{topicId}/chon-tu).
     */
    public function selectWords(string|int $topicId): View
    {
        $topic = $this->findAccessibleTopic($topicId);

        $vocabularies = [];

        if ($topic !== null) {
            $vocabModels = $topic->vocabularies()->orderBy('vocabulary_id')->get();
            $vocabularies = $vocabModels->map(fn (Vocabulary $v): array => $this->mapVocabularyToViewData($v))->values()->all();
        }

        return view('pages.tu-vung-chon-tu', [
            'topicId' => $topicId,
            'topic' => $topic !== null ? $this->mapTopicToViewData($topic) : null,
            'vocabularies' => $vocabularies,
        ]);
    }

    /**
     * Màn hình Luyện tập Flashcard (/tu-vung/{topicId}/flashcard).
     *
     * Hỗ trợ query param ?words=id1,id2,... để luyện tập chỉ những từ đã chọn.
     * Danh sách từ được serialize qua @json($vocabularies) sang JavaScript, do đó
     * bắt buộc phải là mảng plain với đúng key mà JS đang đọc.
     */
    public function flashcard(Request $request, string|int $topicId): View
    {
        $topic = $this->findAccessibleTopic($topicId);

        $vocabularies = [];
        $allVocabularies = [];

        if ($topic !== null) {
            $allVocabModels = $topic->vocabularies()->orderBy('vocabulary_id')->get();
            $allVocabularies = $allVocabModels->map(fn (Vocabulary $v): array => $this->mapVocabularyToViewData($v))->values()->all();

            $selectedWordsParam = $request->query('words');
            if (! empty($selectedWordsParam)) {
                $selectedIds = array_map('intval', explode(',', (string) $selectedWordsParam));
                $filtered = array_values(array_filter(
                    $allVocabularies,
                    fn (array $v): bool => in_array((int) ($v['vocabulary_id'] ?? 0), $selectedIds, true)
                ));
                // Nếu không khớp từ nào (ID không hợp lệ) → fallback lấy toàn bộ
                $vocabularies = ! empty($filtered) ? $filtered : $allVocabularies;
            } else {
                $vocabularies = $allVocabularies;
            }
        }

        return view('pages.tu-vung-flashcard', [
            'topicId' => $topicId,
            'topic' => $topic !== null ? $this->mapTopicToViewData($topic) : null,
            'vocabularies' => $vocabularies,
            'allVocabularies' => $allVocabularies,
        ]);
    }

    /**
     * Tiếp nhận câu trả lời luyện tập Flashcard, kiểm tra đúng/sai và tổng hợp kết quả 2 bài vào CSDL (BE-008.1).
     *
     * Quy tắc mới:
     * - Mỗi từ có hai bài luyện tập liên tiếp: (1) Nghe và viết lại, (2) Điền từ còn trống.
     * - Chỉ chốt kết quả vào CSDL sau khi đã hoàn thành cả hai bài luyện tập của lượt học.
     * - Tổng hợp theo phép OR: Đúng ít nhất 1 trong 2 bài thì is_done = 1; sai cả 2 bài thì is_done = 0.
     * - Lượt học mới tính lại từ đầu, không lấy kết quả cũ của lượt trước.
     * - Cập nhật bản ghi cũ nếu đã tồn tại theo cặp (user_id, vocabulary_id); không tạo bản ghi trùng.
     */
    public function checkAnswer(Request $request, EnergyService $energyService): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để lưu kết quả luyện tập.',
            ], 401);
        }

        $validated = $request->validate([
            'vocabulary_id' => ['required', 'integer'],
            'practice_type' => ['required', 'string', 'in:listen,listen_write,fill,fill_blank'],
            'answer' => ['nullable', 'string', 'max:255'],
        ]);

        $vocabulary = Vocabulary::query()
            ->with('topic')
            ->find($validated['vocabulary_id']);

        if ($vocabulary === null) {
            return response()->json([
                'success' => false,
                'message' => 'Từ vựng không tồn tại.',
            ], 404);
        }

        $topic = $vocabulary->topic;
        $userId = (int) Auth::id();

        if ($topic !== null && $topic->user_id !== null && (int) $topic->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập từ vựng này.',
            ], 403);
        }

        $userAnswer = (string) ($validated['answer'] ?? '');
        $isCorrect = $this->isAnswerCorrect($userAnswer, $vocabulary->word);
        $practiceType = $validated['practice_type'];
        $sessionKey = "flashcard_practice.{$userId}.{$vocabulary->vocabulary_id}";

        // Bài 1: Nghe và viết lại (listen / listen_write)
        if (in_array($practiceType, ['listen', 'listen_write'], true)) {
            // Khởi tạo trạng thái lượt học mới cho từ này trong Session
            session()->put($sessionKey, [
                'listen_correct' => $isCorrect,
                'listen_answered_at' => now()->timestamp,
                'finalized' => false,
            ]);

            return response()->json([
                'success' => true,
                'is_correct' => $isCorrect,
                'vocabulary_id' => $vocabulary->vocabulary_id,
                'practice_type' => $practiceType,
                'finalized' => false,
                'reward_accumulated_count' => $energyService->getAccumulatedCount($userId),
                'current_energy' => (int) (Auth::user()?->energy ?? 0),
                'energy_rewarded' => false,
                'energy_awarded_amount' => 0,
            ]);
        }

        // Bài 2: Điền từ còn trống (fill / fill_blank)
        $round = session()->get($sessionKey);

        // Trường hợp request trùng khi lượt học này đã được chốt xong
        if (is_array($round) && ! empty($round['finalized'])) {
            return response()->json([
                'success' => true,
                'is_correct' => $isCorrect,
                'vocabulary_id' => $vocabulary->vocabulary_id,
                'practice_type' => $practiceType,
                'finalized' => true,
                'is_done' => (int) ($round['final_is_done'] ?? 0),
                'reward_accumulated_count' => $energyService->getAccumulatedCount($userId),
                'current_energy' => (int) (Auth::user()?->energy ?? 0),
                'energy_rewarded' => false,
                'energy_awarded_amount' => 0,
            ]);
        }

        // Kiểm tra xem lượt học hiện tại đã có kết quả Bài 1 (Nghe) hay chưa
        if (! is_array($round) || ! array_key_exists('listen_correct', $round)) {
            return response()->json([
                'success' => true,
                'is_correct' => $isCorrect,
                'vocabulary_id' => $vocabulary->vocabulary_id,
                'practice_type' => $practiceType,
                'finalized' => false,
                'message' => 'Chưa hoàn thành đủ hai bài luyện tập của lượt học.',
                'reward_accumulated_count' => $energyService->getAccumulatedCount($userId),
                'current_energy' => (int) (Auth::user()?->energy ?? 0),
                'energy_rewarded' => false,
                'energy_awarded_amount' => 0,
            ]);
        }

        // Tổng hợp kết quả: Đúng ít nhất 1 bài thì is_done = 1; sai cả hai thì is_done = 0
        $listenCorrect = (bool) $round['listen_correct'];
        $fillCorrect = $isCorrect;
        $finalIsDone = $listenCorrect || $fillCorrect;

        $userVocab = UserVocabulary::updateOrCreate(
            [
                'user_id' => $userId,
                'vocabulary_id' => $vocabulary->vocabulary_id,
            ],
            [
                'is_done' => $finalIsDone ? 1 : 0,
                'learned_at' => now(),
            ]
        );

        // Đánh dấu đã chốt trong Session cho lượt học này để ngăn request trùng
        session()->put($sessionKey, [
            'listen_correct' => $listenCorrect,
            'fill_correct' => $fillCorrect,
            'finalized' => true,
            'final_is_done' => $finalIsDone ? 1 : 0,
        ]);

        // BE-017.2: Cơ chế tích lũy phục hồi năng lượng
        // Chỉ tích lũy khi đúng ít nhất 1 trong 2 bài (is_done = 1)
        if ($finalIsDone) {
            $reward = $energyService->recordCorrectVocabulary($userId, $vocabulary->vocabulary_id);
        } else {
            $reward = [
                'accumulated_count' => $energyService->getAccumulatedCount($userId),
                'current_energy' => (int) (Auth::user()?->energy ?? 0),
                'energy_rewarded' => false,
                'energy_awarded_amount' => 0,
            ];
        }

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'vocabulary_id' => $vocabulary->vocabulary_id,
            'practice_type' => $practiceType,
            'finalized' => true,
            'is_done' => $finalIsDone ? 1 : 0,
            'learned_at' => $userVocab->learned_at?->toISOString() ?? now()->toISOString(),
            'reward_accumulated_count' => $reward['accumulated_count'],
            'current_energy' => $reward['current_energy'],
            'energy_rewarded' => $reward['energy_rewarded'],
            'energy_awarded_amount' => $reward['energy_awarded_amount'],
        ]);
    }

    /**
     * Đánh dấu trạng thái học thủ công cho từ vựng (Nắm vững / Dễ quên).
     *
     * Quy tắc nghiệp vụ:
     * - mastered: user_vocabulary.is_done = 1
     * - review: user_vocabulary.is_done = 0
     * - Tạo mới hoặc cập nhật bản ghi duy nhất theo cặp (user_id, vocabulary_id).
     * - learned_at: để NULL nếu tạo bản ghi mới; giữ nguyên nếu cập nhật; KHÔNG gán now().
     * - Tách biệt hoàn toàn: KHÔNG gọi EnergyService và KHÔNG cộng lượt tích lũy năng lượng của BE-017.2.
     */
    public function updateVocabularyStatus(Request $request, string|int $vocabulary): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện chức năng này.',
            ], 401);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:mastered,review'],
            'vocabulary_id' => ['nullable', 'integer'],
        ]);

        $vocabModel = Vocabulary::query()
            ->with('topic')
            ->find((int) $vocabulary);

        if ($vocabModel === null) {
            return response()->json([
                'success' => false,
                'message' => 'Từ vựng không tồn tại.',
            ], 404);
        }

        $userId = (int) Auth::id();
        $topic = $vocabModel->topic;

        // Phân quyền: Cho phép từ thuộc chủ đề hệ thống (user_id IS NULL) hoặc chủ đề cá nhân của chính học viên.
        // Từ chối nếu từ thuộc chủ đề cá nhân của người khác.
        if ($topic !== null && $topic->user_id !== null && (int) $topic->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thao tác trên từ vựng này.',
            ], 403);
        }

        $isDone = ($validated['status'] === 'mastered') ? 1 : 0;

        $userVocab = UserVocabulary::query()
            ->where('user_id', $userId)
            ->where('vocabulary_id', $vocabModel->vocabulary_id)
            ->first();

        if ($userVocab === null) {
            $userVocab = UserVocabulary::create([
                'user_id' => $userId,
                'vocabulary_id' => $vocabModel->vocabulary_id,
                'is_done' => $isDone,
                'learned_at' => null,
            ]);
        } else {
            $userVocab->is_done = $isDone;
            $userVocab->save();
        }

        // Tính lại tiến độ của chủ đề hiện tại để frontend cập nhật đồng bộ (nếu có topic)
        $progress = null;
        if ($topic !== null) {
            $progress = $this->mapTopicToViewData($topic)['progress'] ?? null;
        }

        return response()->json([
            'success' => true,
            'message' => $isDone === 1 ? 'Đã đánh dấu từ vựng là Nắm vững.' : 'Đã đánh dấu từ vựng là Dễ quên.',
            'vocabulary_id' => $vocabModel->vocabulary_id,
            'status' => $validated['status'],
            'is_done' => $isDone,
            'progress' => $progress,
        ]);
    }
}
