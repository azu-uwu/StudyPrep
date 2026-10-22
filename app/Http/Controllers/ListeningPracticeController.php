<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListeningPracticeController extends Controller
{
    /**
     * Ngân hàng câu hỏi Luyện kỹ năng Nghe TOEIC (Part 1 & Part 2)
     * Khớp 100% với dữ liệu trong ảnh mẫu (Câu 6 mô tả hình, transcript, dịch nghĩa, lời giải)
     */
    private function getQuestionBank(): array
    {
        return [
            [
                'id' => 6,
                'question_number' => 6,
                'part' => 'Part 1: Mô tả hình',
                'image' => asset('images/listening/part1_supermarket.jpg'),
                'audio_duration' => '00:29',
                'audio_duration_seconds' => 29,
                'audio_text' => 'She is selecting a flowerpot.',
                'options' => [
                    'A' => 'She is at the supermarket.',
                    'B' => 'She is selecting a flowerpot.',
                    'C' => 'She is with her best friend.',
                    'D' => 'She is paying for a flowerpot.',
                ],
                'correct' => 'B',
                'transcript' => [
                    '(A) She is at the supermarket.',
                    '(B) She is selecting a flowerpot.',
                    '(C) She is with her best friend.',
                    '(D) She is paying for a flowerpot.',
                ],
                'translation' => [
                    '(A) Cô ấy đang ở siêu thị.',
                    '(B) Cô ấy đang lựa chọn một chậu hoa.',
                    '(C) Cô ấy đang đi cùng bạn thân.',
                    '(D) Cô ấy đang thanh toán tiền chậu hoa.',
                ],
                'explanation' => '(B) là đáp án đúng vì trong hình người phụ nữ đang cầm và lựa chọn một chậu hoa nhỏ trong giỏ/quầy cây.',
            ],
            [
                'id' => 1,
                'question_number' => 1,
                'part' => 'Part 1: Mô tả hình',
                'image' => asset('images/listening/part1_supermarket.jpg'),
                'audio_duration' => '00:28',
                'audio_duration_seconds' => 28,
                'audio_text' => 'A man is typing on a laptop computer.',
                'options' => [
                    'A' => 'A man is typing on a laptop computer.',
                    'B' => 'A man is putting away his notebook.',
                    'C' => 'A man is drinking from a coffee mug.',
                    'D' => 'A man is standing near the window.',
                ],
                'correct' => 'A',
                'transcript' => [
                    '(A) A man is typing on a laptop computer.',
                    '(B) A man is putting away his notebook.',
                    '(C) A man is drinking from a coffee mug.',
                    '(D) A man is standing near the window.',
                ],
                'translation' => [
                    '(A) Một người đàn ông đang gõ bàn phím trên máy tính xách tay.',
                    '(B) Một người đàn ông đang cất cuốn sổ tay.',
                    '(C) Một người đàn ông đang uống nước từ cốc cà phê.',
                    '(D) Một người đàn ông đang đứng cạnh cửa sổ.',
                ],
                'explanation' => '(A) là đáp án đúng vì trong hình người đàn ông đang ngồi thao tác gõ phím trên máy tính xách tay bên bàn làm việc.',
            ],
            [
                'id' => 2,
                'question_number' => 2,
                'part' => 'Part 1: Mô tả hình',
                'image' => asset('images/listening/part1_supermarket.jpg'),
                'audio_duration' => '00:26',
                'audio_duration_seconds' => 26,
                'audio_text' => 'Some pedestrians are crossing the intersection.',
                'options' => [
                    'A' => 'Some pedestrians are crossing the intersection.',
                    'B' => 'Some vehicles are parked along the curb.',
                    'C' => 'People are waiting in line at the bus stop.',
                    'D' => 'Bicycles are being repaired on the sidewalk.',
                ],
                'correct' => 'A',
                'transcript' => [
                    '(A) Some pedestrians are crossing the intersection.',
                    '(B) Some vehicles are parked along the curb.',
                    '(C) People are waiting in line at the bus stop.',
                    '(D) Bicycles are being repaired on the sidewalk.',
                ],
                'translation' => [
                    '(A) Một số người đi bộ đang băng qua ngã tư.',
                    '(B) Một số phương tiện đang đỗ dọc lề đường.',
                    '(C) Mọi người đang xếp hàng tại điểm dừng xe buýt.',
                    '(D) Những chiếc xe đạp đang được sửa chữa trên vỉa hè.',
                ],
                'explanation' => '(A) là đáp án đúng miêu tả chính xác nhóm người đi bộ đang di chuyển qua vạch kẻ đường ngã tư.',
            ],
            [
                'id' => 3,
                'question_number' => 3,
                'part' => 'Part 1: Mô tả hình',
                'image' => asset('images/listening/part1_supermarket.jpg'),
                'audio_duration' => '00:24',
                'audio_duration_seconds' => 24,
                'audio_text' => 'A chef is preparing food in a restaurant kitchen.',
                'options' => [
                    'A' => 'A customer is paying at the counter.',
                    'B' => 'A chef is preparing food in a kitchen.',
                    'C' => 'Tables are being wiped by a waiter.',
                    'D' => 'Dishes are being stacked in the sink.',
                ],
                'correct' => 'B',
                'transcript' => [
                    '(A) A customer is paying at the counter.',
                    '(B) A chef is preparing food in a kitchen.',
                    '(C) Tables are being wiped by a waiter.',
                    '(D) Dishes are being stacked in the sink.',
                ],
                'translation' => [
                    '(A) Khách hàng đang thanh toán tại quầy.',
                    '(B) Đầu bếp đang chuẩn bị món ăn trong gian bếp.',
                    '(C) Bàn ăn đang được người phục vụ lau dọn.',
                    '(D) Bát đĩa đang được xếp trong bồn rửa.',
                ],
                'explanation' => '(B) là đáp án đúng miêu tả hoạt động chính của đầu bếp đang sơ chế nguyên liệu nấu nướng.',
            ],
            [
                'id' => 4,
                'question_number' => 4,
                'part' => 'Part 2: Hỏi & Đáp',
                'image' => null, // Không có hình ảnh (Khớp ảnh mẫu 4 với 3 lựa chọn A, B, C)
                'audio_duration' => '00:21',
                'audio_duration_seconds' => 21,
                'audio_text' => 'Where is the marketing meeting going to be held?',
                'options' => [
                    'A' => 'At 10:00 AM.',
                    'B' => 'In Conference Room B.',
                    'C' => 'Yes, I received the report.',
                ],
                'correct' => 'B',
                'transcript' => [
                    'Where is the marketing meeting going to be held?',
                    '(A) At 10:00 AM.',
                    '(B) In Conference Room B.',
                    '(C) Yes, I received the report.',
                ],
                'translation' => [
                    'Cuộc họp tiếp thị sẽ được tổ chức ở đâu?',
                    '(A) Vào lúc 10 giờ sáng.',
                    '(B) Tại Phòng hội nghị B.',
                    '(C) Vâng, tôi đã nhận được bản báo cáo.',
                ],
                'explanation' => 'Câu hỏi bắt đầu bằng "Where" hỏi địa điểm. Phương án (B) "In Conference Room B" trả lời chính xác nơi tổ chức.',
            ],
        ];
    }

    /**
     * Màn hình làm bài nghe (Matching ảnh mẫu 3 & 4)
     */
    public function practice(Request $request): View
    {
        $count = (int) $request->query('count', 5);
        if ($count <= 0) {
            $count = 5;
        }

        $allQuestions = $this->getQuestionBank();
        $selectedQuestions = array_slice($allQuestions, 0, min($count, count($allQuestions)));

        return view('pages.nghe-lam-bai', [
            'questions' => $selectedQuestions,
            'totalCount' => count($selectedQuestions),
            'targetCount' => $count,
            'isReview' => false,
            'userAnswers' => [],
        ]);
    }

    /**
     * Xử lý nộp bài nghe
     */
    public function submit(Request $request): RedirectResponse
    {
        $submittedAnswers = $request->input('answers', []);
        $count = (int) $request->input('total_count', 5);

        $bank = $this->getQuestionBank();
        $questions = array_slice($bank, 0, min($count, count($bank)));

        $correctCount = 0;
        $incorrectCount = 0;
        $unansweredCount = 0;
        $details = [];

        foreach ($questions as $q) {
            $qid = (string) $q['id'];
            $userPick = $submittedAnswers[$qid] ?? null;

            if (empty($userPick)) {
                $status = 'unanswered';
                $unansweredCount++;
            } elseif ($userPick === $q['correct']) {
                $status = 'correct';
                $correctCount++;
            } else {
                $status = 'incorrect';
                $incorrectCount++;
            }

            $details[$qid] = [
                'user_answer' => $userPick,
                'status' => $status,
            ];
        }

        $resultData = [
            'total' => count($questions),
            'correct_count' => $correctCount,
            'incorrect_count' => $incorrectCount,
            'unanswered_count' => $unansweredCount,
            'answers' => $submittedAnswers,
            'details' => $details,
        ];

        session(['listening_practice_result' => $resultData]);

        return redirect()->route('luyen-tap.nghe.xem-dap-an');
    }

    /**
     * Màn hình xem đáp án & lời giải (Matching ảnh mẫu 1 & 2)
     */
    public function review(Request $request): View
    {
        $result = session('listening_practice_result');
        $allQuestions = $this->getQuestionBank();

        // Nếu truy cập trực tiếp hoặc chưa có session, tạo dữ liệu thực tế mẫu:
        // Câu 6: Người dùng chọn A (sai), đáp án đúng là B (khớp 100% ảnh mẫu 1 & 2)
        if (! $result) {
            $userAnswers = [
                '1' => 'A', // đúng
                '2' => 'A', // đúng
                '3' => 'B', // đúng
                '4' => 'B', // đúng
                '6' => 'A', // sai (đáp án đúng là B)
            ];

            $result = [
                'total' => count($allQuestions),
                'correct_count' => 4,
                'incorrect_count' => 1,
                'unanswered_count' => 0,
                'answers' => $userAnswers,
                'details' => [
                    '6' => ['user_answer' => 'A', 'status' => 'incorrect'],
                ],
            ];
        } else {
            $userAnswers = $result['answers'] ?? [];
        }

        $count = $result['total'] ?? 5;
        $selectedQuestions = array_slice($allQuestions, 0, min($count, count($allQuestions)));

        return view('pages.nghe-lam-bai', [
            'questions' => $selectedQuestions,
            'totalCount' => count($selectedQuestions),
            'targetCount' => $count,
            'isReview' => true,
            'result' => $result,
            'userAnswers' => $userAnswers,
        ]);
    }
}
