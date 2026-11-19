<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Vocabulary;
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
     * Ánh xạ dữ liệu Eloquent Topic sang định dạng mảng mà View đang dùng.
     *
     * Giữ nguyên các key mà tu-vung.blade.php, tu-vung-danh-sach.blade.php
     * và các component đang tham chiếu. Những trường không có trong DB
     * (thumbnail, cefr_levels, progress) được trả về giá trị mặc định an toàn
     * để View tự fallback theo logic @if đã có sẵn.
     *
     * @return array<string, mixed>
     */
    private function mapTopicToViewData(Topic $topic): array
    {
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
            'total_vocabularies' => $topic->vocabularies_count ?? $topic->vocabularies->count(),
            // DB không lưu riêng danh sách cấp độ CEFR theo chủ đề → [] để View ẩn section
            'cefr_levels' => [],
            // Tiến độ: BE-008 sẽ tính toán thực từ user_vocabulary; giá trị mặc định = 0
            'progress' => [
                'completed_pct' => 0,
                'mastery_score' => 0.0,
                'accuracy_pct' => 0,
            ],
        ];
    }

    /**
     * Ánh xạ Eloquent Vocabulary sang định dạng mảng mà View, Component và JS đang dùng.
     *
     * Các key bắt buộc: vocabulary_id, word, meaning_vi, part_of_speech, pronunciation,
     * example_en, example_vi, cefr_level, status, image_url.
     *
     * Lưu ý về cefr_level: DB lưu trường part_of_speech theo format "POS|TOEIC_SCORE"
     * (ví dụ "Verb|650+"). Đây là mức điểm TOEIC tham khảo, không phải CEFR. Trường này
     * được trích xuất qua accessor $vocabulary->target_level nhưng KHÔNG được hiển thị
     * dưới nhãn CEFR vì không tương đương. Trả về '' để View hiển thị badge mặc định B1
     * hoặc giá trị fallback đã khai báo trong Component.
     *
     * @return array<string, mixed>
     */
    private function mapVocabularyToViewData(Vocabulary $vocabulary): array
    {
        return [
            'vocabulary_id' => $vocabulary->vocabulary_id,
            'topic_id' => $vocabulary->topic_id,
            'word' => $vocabulary->word,
            'meaning_vi' => $vocabulary->clean_meaning_vi,
            'part_of_speech' => $vocabulary->clean_pos,
            'pronunciation' => $vocabulary->pronunciation ?? '',
            'example_en' => $vocabulary->example_en ?? '',
            'example_vi' => $vocabulary->example_vi ?? '',
            // Không được ánh xạ điểm TOEIC (như '650+') sang CEFR (A1–C2):
            // Trả về '' để Component và JS dùng fallback 'B1' đã có sẵn.
            'cefr_level' => '',
            // status = 'new' mặc định cho tất cả. BE-008 sẽ tính từ user_vocabulary.is_done.
            'status' => 'new',
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

        return view('pages.tu-vung', [
            'popularTopics' => $popularTopics,
            'myTopics' => $myTopics,
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
            $vocabModels = $topic->vocabularies()->orderBy('vocabulary_id')->get();
            $vocabularies = $vocabModels->map(fn (Vocabulary $v): array => $this->mapVocabularyToViewData($v))->values()->all();
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
}
