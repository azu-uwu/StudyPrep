<?php

namespace App\Http\Controllers;

use App\Services\VocabularyMockData;
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
}
