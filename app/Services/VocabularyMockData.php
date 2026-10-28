<?php

namespace App\Services;

class VocabularyMockData
{
    /**
     * Danh sách tất cả chủ đề từ vựng (Topic) mô phỏng cấu trúc database StudyPrep.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getTopics(): array
    {
        return [
            'test-1' => [
                'topic_id' => 'test-1',
                'numeric_id' => 1,
                'name' => 'Test 1',
                'display_name' => 'Test 1',
                'description' => 'Bộ từ vựng trọng tâm xuất hiện trong đề thi TOEIC Test 1.',
                'thumbnail' => null, // Sẽ render custom SVG badge giống ảnh
                'thumbnail_type' => 'badge',
                'thumbnail_label' => 'TEST 1 RC',
                'total_vocabularies' => 80,
                'cefr_levels' => ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'],
                'progress' => [
                    'completed_pct' => 0,
                    'mastery_score' => 0.0,
                    'accuracy_pct' => 0,
                ],
            ],
            'toeic-lc-2026' => [
                'topic_id' => 'toeic-lc-2026',
                'numeric_id' => 2,
                'name' => '800 Từ vựng TOEIC LC 2026',
                'display_name' => "800 Từ vựng TOEIC LC\n2026",
                'description' => 'Trọng tâm từ vựng kỹ năng Nghe TOEIC Listening Comprehension bản cập nhật 2026.',
                'thumbnail' => 'images/vocab/vocab-toeic-2026-lc.png',
                'thumbnail_type' => 'image',
                'thumbnail_label' => 'TOEIC LC',
                'total_vocabularies' => 800,
                'cefr_levels' => ['A2', 'B1', 'B2', 'C1'],
                'progress' => [
                    'completed_pct' => 12,
                    'mastery_score' => 2.5,
                    'accuracy_pct' => 78,
                ],
            ],
            'toeic-rc-2026' => [
                'topic_id' => 'toeic-rc-2026',
                'numeric_id' => 3,
                'name' => '800 Từ vựng TOEIC RC 2026',
                'display_name' => "800 Từ vựng TOEIC RC\n2026",
                'description' => 'Trọng tâm từ vựng kỹ năng Đọc TOEIC Reading Comprehension bản cập nhật 2026.',
                'thumbnail' => 'images/vocab/vocab-toeic-2026-rc.png',
                'thumbnail_type' => 'image',
                'thumbnail_label' => 'TOEIC RC',
                'total_vocabularies' => 800,
                'cefr_levels' => ['B1', 'B2', 'C1', 'C2'],
                'progress' => [
                    'completed_pct' => 5,
                    'mastery_score' => 1.2,
                    'accuracy_pct' => 64,
                ],
            ],
            'toeic-lc-2025' => [
                'topic_id' => 'toeic-lc-2025',
                'numeric_id' => 4,
                'name' => '800 Từ vựng TOEIC LC 2025',
                'display_name' => "800 Từ vựng TOEIC LC\n2025",
                'description' => 'Bộ từ vựng trọng tâm luyện kỹ năng nghe TOEIC Listening Comprehension 2025.',
                'thumbnail' => 'images/vocab/vocab-toeic-2025-lc.png',
                'thumbnail_type' => 'image',
                'thumbnail_label' => 'TOEIC LC 2025',
                'total_vocabularies' => 800,
                'cefr_levels' => ['A1', 'A2', 'B1', 'B2'],
                'progress' => [
                    'completed_pct' => 0,
                    'mastery_score' => 0.0,
                    'accuracy_pct' => 0,
                ],
            ],
            'chu-de-rong' => [
                'topic_id' => 'chu-de-rong',
                'numeric_id' => 5,
                'name' => 'Chủ đề rỗng (Kiểm tra Empty State)',
                'display_name' => 'Chủ đề rỗng (Empty State)',
                'description' => 'Chủ đề thử nghiệm cho trạng thái chưa có từ vựng nào.',
                'thumbnail' => null,
                'thumbnail_type' => 'badge',
                'thumbnail_label' => 'TEST EMPTY',
                'total_vocabularies' => 0,
                'cefr_levels' => ['A1', 'A2'],
                'progress' => [
                    'completed_pct' => 0,
                    'mastery_score' => 0.0,
                    'accuracy_pct' => 0,
                ],
            ],
        ];
    }

    /**
     * Lấy danh sách các chủ đề phổ biến hiển thị trên trang /tu-vung.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getPopularTopics(): array
    {
        $all = self::getTopics();

        return [
            $all['toeic-lc-2026'],
            $all['toeic-rc-2026'],
            $all['toeic-lc-2025'],
        ];
    }

    /**
     * Tìm chủ đề theo ID (hỗ trợ cả string slug như 'test-1', 'toeic-lc-2026' và số nguyên '1', '2').
     *
     * @return array<string, mixed>|null
     */
    public static function findTopic(string|int $topicId): ?array
    {
        $topics = self::getTopics();
        $strId = (string) $topicId;

        // Tìm trực tiếp theo key
        if (isset($topics[$strId])) {
            return $topics[$strId];
        }

        // Tìm theo numeric_id hoặc alias
        foreach ($topics as $topic) {
            if ((string) ($topic['numeric_id'] ?? '') === $strId) {
                return $topic;
            }
        }

        // Map đặc biệt cho 1 => test-1, 2 => toeic-lc-2026, ...
        $map = [
            '1' => 'test-1',
            '2' => 'toeic-lc-2026',
            '3' => 'toeic-rc-2026',
            '4' => 'toeic-lc-2025',
            '5' => 'chu-de-rong',
        ];

        if (isset($map[$strId]) && isset($topics[$map[$strId]])) {
            return $topics[$map[$strId]];
        }

        return null;
    }

    /**
     * Danh sách từ vựng theo chủ đề.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getVocabularies(string|int $topicId): array
    {
        $topic = self::findTopic($topicId);
        if (! $topic) {
            return [];
        }

        $realId = $topic['topic_id'];

        // Nếu là chủ đề rỗng
        if ($realId === 'chu-de-rong') {
            return [];
        }

        // Danh sách từ vựng chuẩn TOEIC cho Test 1 và các bộ TOEIC (đầy đủ các trường theo schema DB)
        return [
            [
                'vocabulary_id' => 101,
                'topic_id' => $realId,
                'word' => 'combine',
                'meaning_vi' => 'kết hợp',
                'part_of_speech' => 'verb',
                'pronunciation' => '/kəmˈbaɪn/',
                'example_en' => 'combine two departments effectively',
                'example_vi' => 'kết hợp hai phòng ban một cách hiệu quả',
                'cefr_level' => 'B1',
                'status' => 'review', // 'all', 'mastered', 'review' (dễ quên), 'skipped' (bỏ qua), 'new'
                'image_url' => null,
            ],
            [
                'vocabulary_id' => 102,
                'topic_id' => $realId,
                'word' => 'administrator',
                'meaning_vi' => 'người quản trị',
                'part_of_speech' => 'noun',
                'pronunciation' => '/ədˈmɪnɪstreɪtər/',
                'example_en' => 'experienced office administrator position',
                'example_vi' => 'vị trí người quản trị văn phòng giàu kinh nghiệm',
                'cefr_level' => 'B1',
                'status' => 'mastered',
                'image_url' => null,
            ],
            [
                'vocabulary_id' => 103,
                'topic_id' => $realId,
                'word' => 'assign',
                'meaning_vi' => 'phân công, giao phó',
                'part_of_speech' => 'verb',
                'pronunciation' => '/əˈsaɪn/',
                'example_en' => 'assign important tasks to senior staff members',
                'example_vi' => 'phân công các nhiệm vụ quan trọng cho nhân sự cấp cao',
                'cefr_level' => 'B1',
                'status' => 'mastered',
                'image_url' => null,
            ],
            [
                'vocabulary_id' => 104,
                'topic_id' => $realId,
                'word' => 'negotiate',
                'meaning_vi' => 'thương lượng, đàm phán',
                'part_of_speech' => 'verb',
                'pronunciation' => '/nɪˈɡoʊʃieɪt/',
                'example_en' => 'negotiate a favorable agreement with international partners',
                'example_vi' => 'đàm phán một thỏa thuận có lợi với các đối tác quốc tế',
                'cefr_level' => 'B2',
                'status' => 'mastered',
                'image_url' => null,
            ],
            [
                'vocabulary_id' => 105,
                'topic_id' => $realId,
                'word' => 'reimburse',
                'meaning_vi' => 'hoàn trả, bồi hoàn',
                'part_of_speech' => 'verb',
                'pronunciation' => '/ˌriːɪmˈbɜːrs/',
                'example_en' => 'reimburse travel expenses within five business days',
                'example_vi' => 'hoàn trả chi phí công tác trong vòng 5 ngày làm việc',
                'cefr_level' => 'C1',
                'status' => 'review',
                'image_url' => null,
            ],
            [
                'vocabulary_id' => 106,
                'topic_id' => $realId,
                'word' => 'compliance',
                'meaning_vi' => 'sự tuân thủ',
                'part_of_speech' => 'noun',
                'pronunciation' => '/kəmˈplaɪəns/',
                'example_en' => 'ensure strict compliance with new safety regulations',
                'example_vi' => 'đảm bảo sự tuân thủ nghiêm ngặt các quy định an toàn mới',
                'cefr_level' => 'B2',
                'status' => 'skipped',
                'image_url' => null,
            ],
            [
                'vocabulary_id' => 107,
                'topic_id' => $realId,
                'word' => 'evaluate',
                'meaning_vi' => 'đánh giá',
                'part_of_speech' => 'verb',
                'pronunciation' => '/ɪˈvæljueɪt/',
                'example_en' => 'evaluate employee performance at the end of every quarter',
                'example_vi' => 'đánh giá kết quả làm việc của nhân viên vào cuối mỗi quý',
                'cefr_level' => 'B1',
                'status' => 'mastered',
                'image_url' => null,
            ],
            [
                'vocabulary_id' => 108,
                'topic_id' => $realId,
                'word' => 'mandatory',
                'meaning_vi' => 'bắt buộc',
                'part_of_speech' => 'adjective',
                'pronunciation' => '/ˈmændətɔːri/',
                'example_en' => 'attendance is mandatory for all department heads',
                'example_vi' => 'việc tham dự là bắt buộc đối với tất cả các trưởng phòng',
                'cefr_level' => 'B2',
                'status' => 'new',
                'image_url' => null,
            ],
            [
                'vocabulary_id' => 109,
                'topic_id' => $realId,
                'word' => 'renovation',
                'meaning_vi' => 'sự cải tạo, tu sửa',
                'part_of_speech' => 'noun',
                'pronunciation' => '/ˌrenəˈveɪʃn/',
                'example_en' => 'the conference center will close next week for renovation',
                'example_vi' => 'trung tâm hội nghị sẽ đóng cửa vào tuần tới để tu sửa',
                'cefr_level' => 'B2',
                'status' => 'new',
                'image_url' => null,
            ],
            [
                'vocabulary_id' => 110,
                'topic_id' => $realId,
                'word' => 'initiative',
                'meaning_vi' => 'sáng kiến, sự chủ động',
                'part_of_speech' => 'noun',
                'pronunciation' => '/ɪˈnɪʃətɪv/',
                'example_en' => 'take the initiative to streamline customer support operations',
                'example_vi' => 'chủ động khởi xướng việc tinh gọn quy trình hỗ trợ khách hàng',
                'cefr_level' => 'C1',
                'status' => 'mastered',
                'image_url' => null,
            ],
        ];
    }

    /**
     * Tính toán số lượng từ theo từng tab filter (Tất cả, Nắm vững, Dễ quên, Bỏ qua).
     *
     * @param  array<int, array<string, mixed>>  $vocabularies
     * @return array{all: int, mastered: int, review: int, skipped: int}
     */
    public static function calculateFilterCounts(array $vocabularies, int $fallbackTotal = 0): array
    {
        $allCount = count($vocabularies);
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
            'all' => $allCount > 0 ? $allCount : $fallbackTotal,
            'mastered' => $mastered,
            'review' => $review,
            'skipped' => $skipped,
        ];
    }
}
