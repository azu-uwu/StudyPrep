<?php

namespace App\Http\Controllers;

use App\Services\VocabularyMockData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VocabularyPracticeController extends Controller
{
    /**
     * Màn hình chính danh mục Từ vựng (/tu-vung).
     */
    public function index(): View
    {
        $popularTopics = VocabularyMockData::getPopularTopics();

        return view('pages.tu-vung', [
            'popularTopics' => $popularTopics,
        ]);
    }

    /**
     * Màn hình Danh sách từ vựng theo Chủ đề (/tu-vung/{topicId}).
     */
    public function show(string|int $topicId): View
    {
        $topic = VocabularyMockData::findTopic($topicId);
        $vocabularies = $topic ? VocabularyMockData::getVocabularies($topic['topic_id']) : [];
        $filterCounts = VocabularyMockData::calculateFilterCounts(
            $vocabularies,
            $topic['total_vocabularies'] ?? 0
        );

        return view('pages.tu-vung-danh-sach', [
            'topicId' => $topicId,
            'topic' => $topic,
            'vocabularies' => $vocabularies,
            'filterCounts' => $filterCounts,
        ]);
    }

    /**
     * Màn hình Chọn từ muốn học (/tu-vung/{topicId}/chon-tu).
     */
    public function selectWords(string|int $topicId): View
    {
        $topic = VocabularyMockData::findTopic($topicId);
        $vocabularies = $topic ? VocabularyMockData::getVocabularies($topic['topic_id']) : [];

        return view('pages.tu-vung-chon-tu', [
            'topicId' => $topicId,
            'topic' => $topic,
            'vocabularies' => $vocabularies,
        ]);
    }

    /**
     * Màn hình Luyện tập Flashcard (/tu-vung/{topicId}/flashcard).
     */
    public function flashcard(Request $request, string|int $topicId): View
    {
        $topic = VocabularyMockData::findTopic($topicId);
        $allVocabularies = $topic ? VocabularyMockData::getVocabularies($topic['topic_id']) : [];

        // Lọc theo danh sách ID từ nếu có query param words (phân tách bởi dấu phẩy)
        $selectedWordsParam = $request->query('words');
        if (! empty($selectedWordsParam)) {
            $selectedIds = array_map('intval', explode(',', (string) $selectedWordsParam));
            $vocabularies = array_values(array_filter(
                $allVocabularies,
                fn (array $v): bool => in_array((int) ($v['vocabulary_id'] ?? 0), $selectedIds, true)
            ));

            // Nếu không khớp từ nào thì fallback lấy toàn bộ
            if (empty($vocabularies)) {
                $vocabularies = $allVocabularies;
            }
        } else {
            $vocabularies = $allVocabularies;
        }

        return view('pages.tu-vung-flashcard', [
            'topicId' => $topicId,
            'topic' => $topic,
            'vocabularies' => $vocabularies,
            'allVocabularies' => $allVocabularies,
        ]);
    }
}
