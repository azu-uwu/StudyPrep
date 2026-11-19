<?php

namespace App\Services;

class ExamSampleDataService
{
    /**
     * Cấu hình chuẩn của các loại đề thi TOEIC (Full Test & Đề tách lẻ từng Kỹ năng).
     */
    public const BLUEPRINTS = [
        'TOEIC_LR' => [
            'key' => 'TOEIC_LR',
            'name' => 'TOEIC Listening & Reading (Full Test - 200 câu)',
            'short_name' => 'Listening & Reading',
            'total_time' => 120,
            'total_questions' => 200,
            'badge_class' => 'badge-indigo',
            'badge_bg' => '#eef2ff',
            'badge_color' => '#4338ca',
            'badge_border' => '#c7d2fe',
            'icon' => 'headphones',
            'time_note' => '120 phút (Listening 45p, Reading 75p)',
            'skills' => [
                'listening' => [
                    'name' => 'Listening',
                    'skill_code' => 'LC',
                    'time_limit' => 45,
                    'total_questions' => 100,
                    'parts' => [
                        [
                            'section_id' => 1,
                            'part_number' => 1,
                            'code' => 'PART1',
                            'name' => 'Part 1: Photographs (Mô tả tranh)',
                            'description' => 'Nghe audio và chọn câu mô tả đúng bức tranh (6 câu)',
                            'target_count' => 6,
                            'has_group' => false,
                            'mode' => 'listening',
                        ],
                        [
                            'section_id' => 2,
                            'part_number' => 2,
                            'code' => 'PART2',
                            'name' => 'Part 2: Question-Response (Hỏi - Đáp)',
                            'description' => 'Nghe câu hỏi và chọn phản hồi phù hợp A/B/C (25 câu)',
                            'target_count' => 25,
                            'has_group' => false,
                            'mode' => 'listening',
                        ],
                        [
                            'section_id' => 3,
                            'part_number' => 3,
                            'code' => 'PART3',
                            'name' => 'Part 3: Conversations (Hội thoại ngắn)',
                            'description' => 'Nghe đoạn hội thoại và trả lời nhóm 3 câu hỏi (39 câu)',
                            'target_count' => 39,
                            'has_group' => true,
                            'mode' => 'listening',
                        ],
                        [
                            'section_id' => 4,
                            'part_number' => 4,
                            'code' => 'PART4',
                            'name' => 'Part 4: Short Talks (Bài nói ngắn)',
                            'description' => 'Nghe bài nói ngắn và trả lời nhóm 3 câu hỏi (30 câu)',
                            'target_count' => 30,
                            'has_group' => true,
                            'mode' => 'listening',
                        ],
                    ],
                ],
                'reading' => [
                    'name' => 'Reading',
                    'skill_code' => 'RC',
                    'time_limit' => 75,
                    'total_questions' => 100,
                    'parts' => [
                        [
                            'section_id' => 5,
                            'part_number' => 5,
                            'code' => 'PART5',
                            'name' => 'Part 5: Incomplete Sentences (Hoàn thành câu)',
                            'description' => 'Điền từ/ngữ pháp vào chỗ trống câu đơn (30 câu)',
                            'target_count' => 30,
                            'has_group' => false,
                            'mode' => 'reading',
                        ],
                        [
                            'section_id' => 6,
                            'part_number' => 6,
                            'code' => 'PART6',
                            'name' => 'Part 6: Text Completion (Hoàn thành đoạn văn)',
                            'description' => 'Điền từ/câu vào đoạn văn nhóm 4 câu (16 câu)',
                            'target_count' => 16,
                            'has_group' => true,
                            'mode' => 'reading',
                        ],
                        [
                            'section_id' => 7,
                            'part_number' => 7,
                            'code' => 'PART7',
                            'name' => 'Part 7: Reading Comprehension (Đọc hiểu)',
                            'description' => 'Đọc hiểu đoạn đơn và đoạn ghép (54 câu)',
                            'target_count' => 54,
                            'has_group' => true,
                            'mode' => 'reading',
                        ],
                    ],
                ],
            ],
        ],
        'TOEIC_LISTENING' => [
            'key' => 'TOEIC_LISTENING',
            'name' => 'TOEIC Listening (100 câu - Part 1–4)',
            'short_name' => 'TOEIC Listening',
            'total_time' => 45,
            'total_questions' => 100,
            'badge_class' => 'badge-sky',
            'badge_bg' => '#e0f2fe',
            'badge_color' => '#0369a1',
            'badge_border' => '#bae6fd',
            'icon' => 'headphones',
            'time_note' => '45 phút (Chuyên biệt kỹ năng Nghe)',
            'skills' => [
                'listening' => [
                    'name' => 'Listening',
                    'skill_code' => 'LC',
                    'time_limit' => 45,
                    'total_questions' => 100,
                    'parts' => [
                        [
                            'section_id' => 1,
                            'part_number' => 1,
                            'code' => 'PART1',
                            'name' => 'Part 1: Photographs (Mô tả tranh)',
                            'description' => 'Nghe audio và chọn câu mô tả đúng bức tranh (6 câu)',
                            'target_count' => 6,
                            'has_group' => false,
                            'mode' => 'listening',
                        ],
                        [
                            'section_id' => 2,
                            'part_number' => 2,
                            'code' => 'PART2',
                            'name' => 'Part 2: Question-Response (Hỏi - Đáp)',
                            'description' => 'Nghe câu hỏi ngắn và chọn phản hồi phù hợp A/B/C (25 câu)',
                            'target_count' => 25,
                            'has_group' => false,
                            'mode' => 'listening',
                        ],
                        [
                            'section_id' => 3,
                            'part_number' => 3,
                            'code' => 'PART3',
                            'name' => 'Part 3: Conversations (Hội thoại ngắn)',
                            'description' => 'Nghe đoạn hội thoại và trả lời nhóm 3 câu hỏi (39 câu)',
                            'target_count' => 39,
                            'has_group' => true,
                            'mode' => 'listening',
                        ],
                        [
                            'section_id' => 4,
                            'part_number' => 4,
                            'code' => 'PART4',
                            'name' => 'Part 4: Short Talks (Bài nói ngắn)',
                            'description' => 'Nghe bài nói ngắn/thông báo và trả lời nhóm 3 câu hỏi (30 câu)',
                            'target_count' => 30,
                            'has_group' => true,
                            'mode' => 'listening',
                        ],
                    ],
                ],
            ],
        ],
        'TOEIC_READING' => [
            'key' => 'TOEIC_READING',
            'name' => 'TOEIC Reading (100 câu - Part 5–7)',
            'short_name' => 'TOEIC Reading',
            'total_time' => 75,
            'total_questions' => 100,
            'badge_class' => 'badge-emerald',
            'badge_bg' => '#ecfdf5',
            'badge_color' => '#047857',
            'badge_border' => '#a7f3d0',
            'icon' => 'book',
            'time_note' => '75 phút (Chuyên biệt kỹ năng Đọc)',
            'skills' => [
                'reading' => [
                    'name' => 'Reading',
                    'skill_code' => 'RC',
                    'time_limit' => 75,
                    'total_questions' => 100,
                    'parts' => [
                        [
                            'section_id' => 5,
                            'part_number' => 5,
                            'code' => 'PART5',
                            'name' => 'Part 5: Incomplete Sentences (Hoàn thành câu)',
                            'description' => 'Từ vựng & ngữ pháp điền vào chỗ trống câu đơn (30 câu)',
                            'target_count' => 30,
                            'has_group' => false,
                            'mode' => 'reading',
                        ],
                        [
                            'section_id' => 6,
                            'part_number' => 6,
                            'code' => 'PART6',
                            'name' => 'Part 6: Text Completion (Hoàn thành đoạn văn)',
                            'description' => 'Điền từ hoặc câu hoàn chỉnh vào đoạn văn nhóm 4 câu (16 câu)',
                            'target_count' => 16,
                            'has_group' => true,
                            'mode' => 'reading',
                        ],
                        [
                            'section_id' => 7,
                            'part_number' => 7,
                            'code' => 'PART7',
                            'name' => 'Part 7: Reading Comprehension (Đọc hiểu)',
                            'description' => 'Đọc hiểu email, thông báo, bài báo đơn và ghép (54 câu)',
                            'target_count' => 54,
                            'has_group' => true,
                            'mode' => 'reading',
                        ],
                    ],
                ],
            ],
        ],
        'TOEIC_SPEAKING' => [
            'key' => 'TOEIC_SPEAKING',
            'name' => 'TOEIC Speaking (11 câu - 5 phần chuẩn ETS)',
            'short_name' => 'TOEIC Speaking',
            'total_time' => 20,
            'total_questions' => 11,
            'badge_class' => 'badge-amber',
            'badge_bg' => '#fef3c7',
            'badge_color' => '#b45309',
            'badge_border' => '#fde68a',
            'icon' => 'mic',
            'time_note' => '20 phút (Chuyên biệt kỹ năng Nói)',
            'skills' => [
                'speaking' => [
                    'name' => 'Speaking',
                    'skill_code' => 'SW',
                    'time_limit' => 20,
                    'total_questions' => 11,
                    'parts' => [
                        [
                            'section_id' => 10,
                            'part_number' => 1,
                            'code' => 'SPK_T1',
                            'name' => 'Part 1 (Speaking): Read a Text Aloud (Đọc to văn bản)',
                            'description' => 'Đọc to đoạn văn tiếng Anh chuẩn phát âm & ngữ điệu (2 câu)',
                            'target_count' => 2,
                            'has_group' => false,
                            'mode' => 'speaking',
                        ],
                        [
                            'section_id' => 11,
                            'part_number' => 2,
                            'code' => 'SPK_T2',
                            'name' => 'Part 2 (Speaking): Describe a Picture (Mô tả tranh)',
                            'description' => 'Quan sát hình ảnh và mô tả chi tiết bằng tiếng Anh (2 câu)',
                            'target_count' => 2,
                            'has_group' => false,
                            'mode' => 'speaking',
                        ],
                        [
                            'section_id' => 12,
                            'part_number' => 3,
                            'code' => 'SPK_T3',
                            'name' => 'Part 3 (Speaking): Respond to Questions (Trả lời câu hỏi)',
                            'description' => 'Nghe và trả lời câu hỏi phỏng vấn ngắn theo chủ đề (3 câu)',
                            'target_count' => 3,
                            'has_group' => false,
                            'mode' => 'speaking',
                        ],
                        [
                            'section_id' => 13,
                            'part_number' => 4,
                            'code' => 'SPK_T4',
                            'name' => 'Part 4 (Speaking): Respond Using Information Provided (Trả lời theo thông tin)',
                            'description' => 'Đọc lịch trình/bảng thông tin và trả lời câu hỏi tình huống (3 câu)',
                            'target_count' => 3,
                            'has_group' => true,
                            'mode' => 'speaking',
                        ],
                        [
                            'section_id' => 14,
                            'part_number' => 5,
                            'code' => 'SPK_T5',
                            'name' => 'Part 5 (Speaking): Express an Opinion (Trình bày quan điểm)',
                            'description' => 'Nêu quan điểm cá nhân và lý lẽ thuyết phục về một chủ đề (1 câu)',
                            'target_count' => 1,
                            'has_group' => false,
                            'mode' => 'speaking',
                        ],
                    ],
                ],
            ],
        ],
        'TOEIC_WRITING' => [
            'key' => 'TOEIC_WRITING',
            'name' => 'TOEIC Writing (8 câu - 3 phần chuẩn ETS)',
            'short_name' => 'TOEIC Writing',
            'total_time' => 60,
            'total_questions' => 8,
            'badge_class' => 'badge-purple',
            'badge_bg' => '#f3e8ff',
            'badge_color' => '#7e22ce',
            'badge_border' => '#d8b4fe',
            'icon' => 'pencil',
            'time_note' => '60 phút (Chuyên biệt kỹ năng Viết)',
            'skills' => [
                'writing' => [
                    'name' => 'Writing',
                    'skill_code' => 'SW',
                    'time_limit' => 60,
                    'total_questions' => 8,
                    'parts' => [
                        [
                            'section_id' => 8,
                            'part_number' => 1,
                            'code' => 'WRITING_T1',
                            'name' => 'Part 1 (Writing): Write a Sentence Based on a Picture (Viết câu theo tranh)',
                            'description' => 'Viết câu hoàn chỉnh đúng ngữ pháp từ hình ảnh và 2 từ khóa gợi ý (5 câu)',
                            'target_count' => 5,
                            'has_group' => false,
                            'mode' => 'writing',
                        ],
                        [
                            'section_id' => 9,
                            'part_number' => 2,
                            'code' => 'WRITING_T2',
                            'name' => 'Part 2 (Writing): Respond to a Written Request (Viết email phản hồi)',
                            'description' => 'Đọc email công việc và viết văn bản phản hồi theo yêu cầu (2 câu)',
                            'target_count' => 2,
                            'has_group' => true,
                            'mode' => 'writing',
                        ],
                        [
                            'section_id' => 15,
                            'part_number' => 3,
                            'code' => 'WRITING_T3',
                            'name' => 'Part 3 (Writing): Write an Opinion Essay (Viết bài luận quan điểm)',
                            'description' => 'Viết bài luận tối thiểu 300 từ nêu quan điểm cá nhân (1 câu)',
                            'target_count' => 1,
                            'has_group' => false,
                            'mode' => 'writing',
                        ],
                    ],
                ],
            ],
        ],
        'TOEIC_SW' => [
            'key' => 'TOEIC_SW',
            'name' => 'TOEIC Speaking & Writing (Full Test - 19 câu)',
            'short_name' => 'Speaking & Writing',
            'total_time' => 80,
            'total_questions' => 19,
            'badge_class' => 'badge-rose',
            'badge_bg' => '#ffe4e6',
            'badge_color' => '#be123c',
            'badge_border' => '#fecdd3',
            'icon' => 'pencil',
            'time_note' => '80 phút (Speaking 20p, Writing 60p)',
            'skills' => [
                'speaking' => [
                    'name' => 'Speaking',
                    'skill_code' => 'SW',
                    'time_limit' => 20,
                    'total_questions' => 11,
                    'parts' => [
                        [
                            'section_id' => 10,
                            'part_number' => 1,
                            'code' => 'SPK_T1',
                            'name' => 'Part 1 (Speaking): Read a Text Aloud (Đọc to văn bản)',
                            'description' => 'Đọc to đoạn văn tiếng Anh chuẩn phát âm & ngữ điệu (2 câu)',
                            'target_count' => 2,
                            'has_group' => false,
                            'mode' => 'speaking',
                        ],
                        [
                            'section_id' => 11,
                            'part_number' => 2,
                            'code' => 'SPK_T2',
                            'name' => 'Part 2 (Speaking): Describe a Picture (Mô tả tranh)',
                            'description' => 'Quan sát hình ảnh và mô tả chi tiết bằng tiếng Anh (2 câu)',
                            'target_count' => 2,
                            'has_group' => false,
                            'mode' => 'speaking',
                        ],
                        [
                            'section_id' => 12,
                            'part_number' => 3,
                            'code' => 'SPK_T3',
                            'name' => 'Part 3 (Speaking): Respond to Questions (Trả lời câu hỏi)',
                            'description' => 'Nghe và trả lời câu hỏi phỏng vấn ngắn theo chủ đề (3 câu)',
                            'target_count' => 3,
                            'has_group' => false,
                            'mode' => 'speaking',
                        ],
                        [
                            'section_id' => 13,
                            'part_number' => 4,
                            'code' => 'SPK_T4',
                            'name' => 'Part 4 (Speaking): Respond Using Information Provided (Trả lời theo thông tin)',
                            'description' => 'Đọc lịch trình/bảng thông tin và trả lời câu hỏi tình huống (3 câu)',
                            'target_count' => 3,
                            'has_group' => true,
                            'mode' => 'speaking',
                        ],
                        [
                            'section_id' => 14,
                            'part_number' => 5,
                            'code' => 'SPK_T5',
                            'name' => 'Part 5 (Speaking): Express an Opinion (Trình bày quan điểm)',
                            'description' => 'Nêu quan điểm cá nhân và lý lẽ thuyết phục về một chủ đề (1 câu)',
                            'target_count' => 1,
                            'has_group' => false,
                            'mode' => 'speaking',
                        ],
                    ],
                ],
                'writing' => [
                    'name' => 'Writing',
                    'skill_code' => 'SW',
                    'time_limit' => 60,
                    'total_questions' => 8,
                    'parts' => [
                        [
                            'section_id' => 8,
                            'part_number' => 1,
                            'code' => 'WRITING_T1',
                            'name' => 'Part 1 (Writing): Write a Sentence Based on a Picture (Viết câu theo tranh)',
                            'description' => 'Viết câu hoàn chỉnh dựa trên tranh và từ gợi ý (5 câu)',
                            'target_count' => 5,
                            'has_group' => false,
                            'mode' => 'writing',
                        ],
                        [
                            'section_id' => 9,
                            'part_number' => 2,
                            'code' => 'WRITING_T2',
                            'name' => 'Part 2 (Writing): Respond to a Written Request (Viết email phản hồi)',
                            'description' => 'Viết email hồi đáp yêu cầu (2 câu)',
                            'target_count' => 2,
                            'has_group' => true,
                            'mode' => 'writing',
                        ],
                        [
                            'section_id' => 15,
                            'part_number' => 3,
                            'code' => 'WRITING_T3',
                            'name' => 'Part 3 (Writing): Write an Opinion Essay (Viết bài luận quan điểm)',
                            'description' => 'Viết bài luận nêu quan điểm tối thiểu 300 từ (1 câu)',
                            'target_count' => 1,
                            'has_group' => false,
                            'mode' => 'writing',
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * Lấy danh sách toàn bộ đề thi mẫu (kèm các đề thi mô phỏng tạo trong session).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllTests(?string $search = null, ?string $type = null, ?string $status = null): array
    {
        $baseTests = $this->getBaseTests();

        // Nạp thêm các đề thi do người dùng mô phỏng tạo trong phiên làm việc (nếu có)
        $sessionTests = session()->get('mock_admin_tests', []);
        $allTests = array_merge($sessionTests, $baseTests);

        // Lọc theo tìm kiếm
        if (! empty($search)) {
            $searchLower = mb_strtolower(trim($search));
            $allTests = array_filter($allTests, function ($test) use ($searchLower) {
                return str_contains(mb_strtolower($test['title']), $searchLower)
                    || str_contains(mb_strtolower($test['code']), $searchLower);
            });
        }

        // Lọc theo loại đề
        if (! empty($type) && $type !== 'ALL') {
            $allTests = array_filter($allTests, function ($test) use ($type) {
                return $test['exam_type'] === $type;
            });
        }

        // Lọc theo trạng thái hoàn thiện
        if (! empty($status) && $status !== 'ALL') {
            $allTests = array_filter($allTests, function ($test) use ($status) {
                return $test['status'] === $status;
            });
        }

        return array_values($allTests);
    }

    /**
     * Tìm chi tiết đề thi theo ID.
     *
     * @return array<string, mixed>|null
     */
    public function findTest(int|string $id): ?array
    {
        $allTests = $this->getAllTests();
        foreach ($allTests as $test) {
            if ((string) $test['id'] === (string) $id) {
                return $this->enrichTestDetails($test);
            }
        }

        return null;
    }

    /**
     * Tạo một đề thi mới ở mức mô phỏng giao diện (lưu tạm vào session).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createMockTest(array $data): array
    {
        $type = $data['exam_type'] ?? 'TOEIC_LR';
        $blueprint = self::BLUEPRINTS[$type] ?? self::BLUEPRINTS['TOEIC_LR'];

        $existing = session()->get('mock_admin_tests', []);
        $newId = 100 + count($existing) + 1;
        $code = 'TEST-'.str_pad((string) $newId, 3, '0', STR_PAD_LEFT);

        $newTest = [
            'id' => $newId,
            'code' => $code,
            'title' => trim($data['title']),
            'exam_type' => $type,
            'type_name' => $blueprint['name'],
            'total_time' => $blueprint['total_time'],
            'target_questions' => $blueprint['total_questions'],
            'current_questions' => 0,
            'completion_rate' => 0,
            'status' => 'EMPTY',
            'created_at' => now()->format('d/m/Y H:i'),
            'parts_progress' => [],
            'is_mock_created' => true,
        ];

        // Khởi tạo tiến độ từng Part là 0 câu
        foreach ($blueprint['skills'] as $skillKey => $skill) {
            foreach ($skill['parts'] as $part) {
                $newTest['parts_progress'][$part['code']] = [
                    'current' => 0,
                    'target' => $part['target_count'],
                    'rate' => 0,
                    'is_complete' => false,
                ];
            }
        }

        array_unshift($existing, $newTest);
        session()->put('mock_admin_tests', $existing);

        return $newTest;
    }

    /**
     * Danh sách 4 đề thi mẫu chuẩn (Đã hoàn thiện, Đang biên soạn, TOEIC SW, Mới tạo 0 câu).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getBaseTests(): array
    {
        return [
            // 1. Đề thi TOEIC Listening & Reading đã đủ 200/200 câu
            [
                'id' => 1,
                'code' => 'TEST-001',
                'title' => 'TOEIC Actual Test 01 - ETS Official 2026',
                'exam_type' => 'TOEIC_LR',
                'type_name' => self::BLUEPRINTS['TOEIC_LR']['name'],
                'total_time' => 120,
                'target_questions' => 200,
                'current_questions' => 200,
                'completion_rate' => 100,
                'status' => 'COMPLETED',
                'created_at' => '10/03/2026 09:30',
                'parts_progress' => [
                    'PART1' => ['current' => 6, 'target' => 6, 'rate' => 100, 'is_complete' => true],
                    'PART2' => ['current' => 25, 'target' => 25, 'rate' => 100, 'is_complete' => true],
                    'PART3' => ['current' => 39, 'target' => 39, 'rate' => 100, 'is_complete' => true],
                    'PART4' => ['current' => 30, 'target' => 30, 'rate' => 100, 'is_complete' => true],
                    'PART5' => ['current' => 30, 'target' => 30, 'rate' => 100, 'is_complete' => true],
                    'PART6' => ['current' => 16, 'target' => 16, 'rate' => 100, 'is_complete' => true],
                    'PART7' => ['current' => 54, 'target' => 54, 'rate' => 100, 'is_complete' => true],
                ],
            ],
            // 2. Đề thi TOEIC Listening & Reading đang biên soạn dở (145/200 câu)
            [
                'id' => 2,
                'code' => 'TEST-002',
                'title' => 'TOEIC Actual Test 02 - Định hướng Chuyên sâu',
                'exam_type' => 'TOEIC_LR',
                'type_name' => self::BLUEPRINTS['TOEIC_LR']['name'],
                'total_time' => 120,
                'target_questions' => 200,
                'current_questions' => 145,
                'completion_rate' => 72.5,
                'status' => 'IN_PROGRESS',
                'created_at' => '14/03/2026 14:15',
                'parts_progress' => [
                    'PART1' => ['current' => 6, 'target' => 6, 'rate' => 100, 'is_complete' => true],
                    'PART2' => ['current' => 25, 'target' => 25, 'rate' => 100, 'is_complete' => true],
                    'PART3' => ['current' => 30, 'target' => 39, 'rate' => 76.9, 'is_complete' => false],
                    'PART4' => ['current' => 15, 'target' => 30, 'rate' => 50.0, 'is_complete' => false],
                    'PART5' => ['current' => 30, 'target' => 30, 'rate' => 100, 'is_complete' => true],
                    'PART6' => ['current' => 12, 'target' => 16, 'rate' => 75.0, 'is_complete' => false],
                    'PART7' => ['current' => 27, 'target' => 54, 'rate' => 50.0, 'is_complete' => false],
                ],
            ],
            // 3. Đề thi TOEIC Speaking & Writing đang biên soạn (12/19 câu)
            [
                'id' => 3,
                'code' => 'TEST-003',
                'title' => 'TOEIC Speaking & Writing Practice Test 01',
                'exam_type' => 'TOEIC_SW',
                'type_name' => self::BLUEPRINTS['TOEIC_SW']['name'],
                'total_time' => 80,
                'target_questions' => 19,
                'current_questions' => 12,
                'completion_rate' => 63.2,
                'status' => 'IN_PROGRESS',
                'created_at' => '15/03/2026 11:00',
                'parts_progress' => [
                    'SPK_T1' => ['current' => 2, 'target' => 2, 'rate' => 100, 'is_complete' => true],
                    'SPK_T2' => ['current' => 2, 'target' => 2, 'rate' => 100, 'is_complete' => true],
                    'SPK_T3' => ['current' => 3, 'target' => 3, 'rate' => 100, 'is_complete' => true],
                    'SPK_T4' => ['current' => 3, 'target' => 3, 'rate' => 100, 'is_complete' => true],
                    'SPK_T5' => ['current' => 1, 'target' => 1, 'rate' => 100, 'is_complete' => true],
                    'WRITING_T1' => ['current' => 1, 'target' => 5, 'rate' => 20, 'is_complete' => false],
                    'WRITING_T2' => ['current' => 0, 'target' => 2, 'rate' => 0, 'is_complete' => false],
                    'WRITING_T3' => ['current' => 0, 'target' => 1, 'rate' => 0, 'is_complete' => false],
                ],
            ],
            // 4. Đề thi mới tạo chưa có câu hỏi (0 câu - để kiểm tra trạng thái rỗng)
            [
                'id' => 4,
                'code' => 'TEST-004',
                'title' => 'TOEIC Actual Test 03 - Bản thảo mới',
                'exam_type' => 'TOEIC_LR',
                'type_name' => self::BLUEPRINTS['TOEIC_LR']['name'],
                'total_time' => 120,
                'target_questions' => 200,
                'current_questions' => 0,
                'completion_rate' => 0,
                'status' => 'EMPTY',
                'created_at' => '16/03/2026 16:45',
                'parts_progress' => [
                    'PART1' => ['current' => 0, 'target' => 6, 'rate' => 0, 'is_complete' => false],
                    'PART2' => ['current' => 0, 'target' => 25, 'rate' => 0, 'is_complete' => false],
                    'PART3' => ['current' => 0, 'target' => 39, 'rate' => 0, 'is_complete' => false],
                    'PART4' => ['current' => 0, 'target' => 30, 'rate' => 0, 'is_complete' => false],
                    'PART5' => ['current' => 0, 'target' => 30, 'rate' => 0, 'is_complete' => false],
                    'PART6' => ['current' => 0, 'target' => 16, 'rate' => 0, 'is_complete' => false],
                    'PART7' => ['current' => 0, 'target' => 54, 'rate' => 0, 'is_complete' => false],
                ],
            ],
        ];
    }

    /**
     * Bổ sung chi tiết từng Part và các câu hỏi mẫu/QuestionSet để hiển thị tại trang chi tiết.
     *
     * @param  array<string, mixed>  $test
     * @return array<string, mixed>
     */
    protected function enrichTestDetails(array $test): array
    {
        $blueprint = self::BLUEPRINTS[$test['exam_type']] ?? self::BLUEPRINTS['TOEIC_LR'];
        $test['blueprint'] = $blueprint;

        $detailedSkills = [];

        foreach ($blueprint['skills'] as $skillKey => $skill) {
            $partsList = [];
            foreach ($skill['parts'] as $part) {
                $code = $part['code'];
                $progress = $test['parts_progress'][$code] ?? [
                    'current' => 0,
                    'target' => $part['target_count'],
                    'rate' => 0,
                    'is_complete' => false,
                ];

                // Sinh dữ liệu câu hỏi mẫu trực quan cho Part này nếu đề có câu hỏi
                $questions = $this->generateSampleQuestionsForPart($test['id'], $part, $progress['current']);

                $partsList[] = array_merge($part, [
                    'current_count' => $progress['current'],
                    'target_count' => $progress['target'],
                    'completion_rate' => $progress['rate'],
                    'is_complete' => $progress['is_complete'],
                    'questions' => $questions,
                ]);
            }

            $detailedSkills[$skillKey] = [
                'name' => $skill['name'],
                'time_limit' => $skill['time_limit'],
                'total_questions' => $skill['total_questions'],
                'parts' => $partsList,
            ];
        }

        $test['skills'] = $detailedSkills;

        return $test;
    }

    /**
     * Tạo danh sách câu hỏi minh họa thực tế cho một Part, có hỗ trợ QuestionSet nhóm câu hỏi.
     *
     * @param  array<string, mixed>  $part
     * @return array<int, array<string, mixed>>
     */
    protected function generateSampleQuestionsForPart(int|string $testId, array $part, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $code = $part['code'];
        $questions = [];

        if ($code === 'PART1') {
            // Câu hỏi đơn Part 1
            $samples = [
                ['stem' => 'Look at the picture marked No. 1 in your test book.', 'diff' => 'EASY', 'type' => 'Hình ảnh cá nhân', 'correct' => 'A'],
                ['stem' => 'Look at the picture marked No. 2 in your test book.', 'diff' => 'MEDIUM', 'type' => 'Hình ảnh nơi làm việc', 'correct' => 'B'],
                ['stem' => 'Look at the picture marked No. 3 in your test book.', 'diff' => 'MEDIUM', 'type' => 'Hình ảnh đồ vật / bối cảnh', 'correct' => 'C'],
                ['stem' => 'Look at the picture marked No. 4 in your test book.', 'diff' => 'HARD', 'type' => 'Hình ảnh phong cảnh ngoài trời', 'correct' => 'D'],
                ['stem' => 'Look at the picture marked No. 5 in your test book.', 'diff' => 'MEDIUM', 'type' => 'Hình ảnh giao thông đường phố', 'correct' => 'A'],
                ['stem' => 'Look at the picture marked No. 6 in your test book.', 'diff' => 'HARD', 'type' => 'Hình ảnh kho hàng / công xưởng', 'correct' => 'C'],
            ];

            for ($i = 0; $i < min($count, count($samples)); $i++) {
                $qNum = $i + 1;
                $questions[] = [
                    'id' => 1000 + $qNum,
                    'sequence_no' => $qNum,
                    'question_code' => '#Q-'.(1000 + $qNum),
                    'content' => $samples[$i]['stem'],
                    'difficulty' => $samples[$i]['diff'],
                    'question_type' => $samples[$i]['type'],
                    'points' => 5.0,
                    'is_grouped' => false,
                    'correct_option' => $samples[$i]['correct'],
                    'options' => [
                        ['label' => 'A', 'content' => '(A) She is typing on a computer keyboard.'],
                        ['label' => 'B', 'content' => '(B) She is drinking from a coffee mug.'],
                        ['label' => 'C', 'content' => '(C) She is adjusting her glasses.'],
                        ['label' => 'D', 'content' => '(D) She is opening a window.'],
                    ],
                ];
            }
        } elseif ($code === 'PART2') {
            // Part 2: Question-Response (câu đơn)
            for ($i = 0; $i < min($count, 25); $i++) {
                $qNum = 7 + $i;
                $questions[] = [
                    'id' => 1100 + $qNum,
                    'sequence_no' => $qNum,
                    'question_code' => '#Q-'.(1100 + $qNum),
                    'content' => 'Where did you leave the keys to the conference room?',
                    'difficulty' => ($i % 3 === 0) ? 'EASY' : (($i % 3 === 1) ? 'MEDIUM' : 'HARD'),
                    'question_type' => 'Hỏi - Đáp phản xạ (Wh- Question)',
                    'points' => 5.0,
                    'is_grouped' => false,
                    'correct_option' => 'B',
                    'options' => [
                        ['label' => 'A', 'content' => 'Yes, at two o’clock.'],
                        ['label' => 'B', 'content' => 'On the reception desk.'],
                        ['label' => 'C', 'content' => 'No, I haven’t seen him.'],
                    ],
                ];
            }
        } elseif ($code === 'PART3' || $code === 'PART4') {
            // Part 3/4: Có QuestionSet (Nhóm 3 câu)
            $totalGroups = (int) ceil($count / 3);
            $currentQNum = ($code === 'PART3') ? 32 : 71;

            for ($g = 1; $g <= $totalGroups; $g++) {
                $groupQCount = min(3, $count - ($g - 1) * 3);
                if ($groupQCount <= 0) {
                    break;
                }

                $rangeStart = $currentQNum;
                $rangeEnd = $currentQNum + $groupQCount - 1;
                $currentQNum += $groupQCount;

                $childQuestions = [];
                for ($k = 0; $k < $groupQCount; $k++) {
                    $itemNum = $rangeStart + $k;
                    $childQuestions[] = [
                        'id' => 1200 + $itemNum,
                        'sequence_no' => $itemNum,
                        'question_code' => '#Q-'.(1200 + $itemNum),
                        'content' => ($k === 0) ? 'Where does the conversation most likely take place?' : (($k === 1) ? 'What problem does the woman mention?' : 'What will the man probably do next?'),
                        'difficulty' => 'MEDIUM',
                        'question_type' => 'Hội thoại ngắn (Chi tiết & Suy luận)',
                        'points' => 5.0,
                        'correct_option' => ['A', 'C', 'B'][$k % 3],
                        'options' => [
                            ['label' => 'A', 'content' => 'At an electronics store'],
                            ['label' => 'B', 'content' => 'At a railway station'],
                            ['label' => 'C', 'content' => 'In a hospital waiting area'],
                            ['label' => 'D', 'content' => 'In an office supply warehouse'],
                        ],
                    ];
                }

                $questions[] = [
                    'is_grouped' => true,
                    'group_id' => 500 + $g,
                    'group_title' => 'Nhóm câu hỏi '.$rangeStart.' - '.$rangeEnd.': '.($code === 'PART3' ? 'Đoạn hội thoại kinh doanh' : 'Thông báo chuyến bay'),
                    'stimulus_text' => 'Questions '.$rangeStart.'-'.$rangeEnd.' refer to the following '.($code === 'PART3' ? 'conversation' : 'announcement').'.',
                    'audio_url' => 'https://studyprep.local/audio/actual-01-group-'.$g.'.mp3',
                    'transcript' => '[Man]: Good morning. I am calling to inquire about the shipment of laptops ordered last Tuesday... [Woman]: Certainly sir, let me check the tracking system...',
                    'child_questions' => $childQuestions,
                ];
            }
        } elseif ($code === 'PART5') {
            // Part 5: Incomplete Sentences (câu đơn)
            $sampleStems = [
                'Mr. Henderson ______ the financial forecast to the executive board yesterday afternoon.',
                'The human resources department has announced ______ new policies regarding remote working.',
                'All visitors must obtain a security badge ______ entering the laboratory facility.',
                'Because of high demand, the newly released smartphone was ______ sold out within two hours.',
                'Please review the attached contract carefully before ______ your final signature.',
            ];

            for ($i = 0; $i < min($count, 30); $i++) {
                $qNum = 101 + $i;
                $questions[] = [
                    'id' => 1500 + $qNum,
                    'sequence_no' => $qNum,
                    'question_code' => '#Q-'.(1500 + $qNum),
                    'content' => $sampleStems[$i % count($sampleStems)],
                    'difficulty' => ($i % 3 === 0) ? 'EASY' : (($i % 3 === 1) ? 'MEDIUM' : 'HARD'),
                    'question_type' => 'Từ vựng & Ngữ pháp (Grammar & Vocab)',
                    'points' => 5.0,
                    'is_grouped' => false,
                    'correct_option' => 'C',
                    'options' => [
                        ['label' => 'A', 'content' => 'present'],
                        ['label' => 'B', 'content' => 'presentation'],
                        ['label' => 'C', 'content' => 'presented'],
                        ['label' => 'D', 'content' => 'presenting'],
                    ],
                ];
            }
        } elseif ($code === 'PART6' || $code === 'PART7') {
            // Part 6 & 7: Nhóm bài đọc
            $questionsPerSet = ($code === 'PART6') ? 4 : 3;
            $totalSets = (int) ceil($count / $questionsPerSet);
            $currentQNum = ($code === 'PART6') ? 131 : 147;

            for ($g = 1; $g <= $totalSets; $g++) {
                $groupQCount = min($questionsPerSet, $count - ($g - 1) * $questionsPerSet);
                if ($groupQCount <= 0) {
                    break;
                }

                $rangeStart = $currentQNum;
                $rangeEnd = $currentQNum + $groupQCount - 1;
                $currentQNum += $groupQCount;

                $childQuestions = [];
                for ($k = 0; $k < $groupQCount; $k++) {
                    $itemNum = $rangeStart + $k;
                    $childQuestions[] = [
                        'id' => 1600 + $itemNum,
                        'sequence_no' => $itemNum,
                        'question_code' => '#Q-'.(1600 + $itemNum),
                        'content' => 'What is indicated about the upcoming conference in Chicago?',
                        'difficulty' => 'MEDIUM',
                        'question_type' => 'Đọc hiểu suy luận chi tiết',
                        'points' => 5.0,
                        'correct_option' => 'A',
                        'options' => [
                            ['label' => 'A', 'content' => 'It will be held at a newly renovated hotel.'],
                            ['label' => 'B', 'content' => 'Registration fees are strictly nonrefundable.'],
                            ['label' => 'C', 'content' => 'Keynote speakers have not yet been chosen.'],
                            ['label' => 'D', 'content' => 'Attendance is restricted to company employees.'],
                        ],
                    ];
                }

                $questions[] = [
                    'is_grouped' => true,
                    'group_id' => 700 + $g,
                    'group_title' => 'Đoạn văn đọc hiểu (Câu '.$rangeStart.' - '.$rangeEnd.'): Thư thông báo nội bộ công ty',
                    'stimulus_text' => "To: All Staff Members\nFrom: Facilities Management\nSubject: Scheduled Office Renovations\nDate: October 14\n\nPlease be advised that the fifth-floor meeting rooms will undergo routine painting and carpet replacement next Monday...",
                    'child_questions' => $childQuestions,
                ];
            }
        } else {
            // TOEIC Speaking & Writing
            for ($i = 0; $i < $count; $i++) {
                $qNum = $i + 1;
                $questions[] = [
                    'id' => 2000 + $qNum,
                    'sequence_no' => $qNum,
                    'question_code' => '#Q-SW-'.(2000 + $qNum),
                    'content' => 'Nhiệm vụ kiểm tra kỹ năng: '.$part['name'].' (Mục '.$qNum.')',
                    'difficulty' => 'MEDIUM',
                    'question_type' => $part['name'],
                    'points' => 10.0,
                    'is_grouped' => false,
                    'correct_option' => 'Tự luận / Ghi âm',
                    'options' => [],
                ];
            }
        }

        return $questions;
    }
}
