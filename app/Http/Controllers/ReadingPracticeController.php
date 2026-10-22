<?php

namespace App\Http\Controllers;

use App\Models\AnswerOption;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingPracticeController extends Controller
{
    /**
     * Danh sách câu hỏi chuẩn TOEIC Reading Part 5 & Part 6
     */
    protected function getQuestionBank(): array
    {
        return [
            [
                'id' => 139,
                'part' => 'Part 6',
                'passage_title' => 'Amon Donates to Music School in Grenel City',
                'passage' => 'A spokesperson for Brenda Amon (139)______ that the pianist made a sizeable donation toward the expansion of the Grenel City Conservatory of Music. "Without her generous support," said Marc Diaz, director of facility planning, "we would have been limited in our renovation plans going forward."<br><br>(140)______. Now, a new wing will be constructed on the south end of the (141)______ conservatory. Once completed, the building will boast a 700-seat auditorium, state-of-the-art recording studios, and new faculty and administrative offices. Additionally, private practice rooms will be located (142)______ the current student lounge.',
                'question_range' => 'Câu 139 - 142',
                'question_number' => 139,
                'stem' => 'A spokesperson for Brenda Amon (139)______ that the pianist made a sizeable donation toward the expansion of the Grenel City Conservatory of Music.',
                'options' => [
                    'A' => 'confirms',
                    'B' => 'confirm',
                    'C' => 'confirmed',
                    'D' => 'will confirm',
                ],
                'correct' => 'C',
                'explanation' => 'Chỗ trống nằm sau cụm danh từ chủ ngữ và trước mệnh đề "that", câu tường thuật một hành động đã diễn ra trong quá khứ nên động từ chính phải ở thì quá khứ đơn. Do đó, đáp án <strong>C. confirmed</strong> là chính xác.',
                'translation' => 'Người phát ngôn của Brenda Amon đã xác nhận rằng nghệ sĩ piano đã quyên góp một khoản tiền đáng kể...',
            ],
            [
                'id' => 140,
                'part' => 'Part 6',
                'passage_title' => 'Amon Donates to Music School in Grenel City',
                'passage' => null, // Dùng chung passage câu 139
                'question_range' => 'Câu 139 - 142',
                'question_number' => 140,
                'stem' => 'Chọn câu điền vào chỗ trống (140) phù hợp nhất với ngữ cảnh đoạn văn:',
                'options' => [
                    'A' => 'The facility is scheduled to open next month.',
                    'B' => 'Renovation work has already begun on the facility.',
                    'C' => 'Students are excited about the upcoming school year.',
                    'D' => 'The donation will be used to buy instruments.',
                ],
                'correct' => 'B',
                'explanation' => 'Câu trước đề cập đến việc "renovation plans going forward", câu tiếp theo nói về việc cánh nhà mới sẽ được xây dựng. Câu <strong>B. Renovation work has already begun on the facility</strong> tạo mạch nối tiếp logic và tự nhiên nhất.',
                'translation' => 'Công việc cải tạo đã được bắt đầu tại cơ sở này.',
            ],
            [
                'id' => 141,
                'part' => 'Part 6',
                'passage_title' => 'Amon Donates to Music School in Grenel City',
                'passage' => null,
                'question_range' => 'Câu 139 - 142',
                'question_number' => 141,
                'stem' => 'Now, a new wing will be constructed on the south end of the (141)______ conservatory.',
                'options' => [
                    'A' => 'temporary',
                    'B' => 'existing',
                    'C' => 'corrected',
                    'D' => 'proposed',
                ],
                'correct' => 'B',
                'explanation' => 'Cần một tính từ đứng trước danh từ "conservatory". "existing conservatory" nghĩa là nhạc viện hiện có / hiện hữu. Cánh mới được mở rộng trên khuôn viên có sẵn. Đáp án <strong>B. existing</strong> là chính xác.',
                'translation' => 'Giờ đây, một cánh nhà mới sẽ được xây dựng ở phía nam của nhạc viện hiện tại.',
            ],
            [
                'id' => 142,
                'part' => 'Part 6',
                'passage_title' => 'Amon Donates to Music School in Grenel City',
                'passage' => null,
                'question_range' => 'Câu 139 - 142',
                'question_number' => 142,
                'stem' => 'Additionally, private practice rooms will be located (142)______ the current student lounge.',
                'options' => [
                    'A' => 'adjacent to',
                    'B' => 'even though',
                    'C' => 'as well as',
                    'D' => 'in addition to',
                ],
                'correct' => 'A',
                'explanation' => '"adjacent to" là cụm giới từ chỉ nơi chốn (ngay cạnh, liền kề). Các phòng tập cá nhân sẽ nằm kế bên sảnh sinh hoạt sinh viên hiện tại. Đáp án <strong>A. adjacent to</strong> là chính xác.',
                'translation' => 'Ngoài ra, các phòng luyện tập riêng sẽ được bố trí liền kề khu vực sảnh sinh viên hiện tại.',
            ],
            [
                'id' => 101,
                'part' => 'Part 5',
                'passage_title' => null,
                'passage' => null,
                'question_range' => 'Part 5',
                'question_number' => 101,
                'stem' => 'Mr. Henderson requested that all expense reports be submitted _______ Friday afternoon.',
                'options' => [
                    'A' => 'within',
                    'B' => 'before',
                    'C' => 'until',
                    'D' => 'along',
                ],
                'correct' => 'B',
                'explanation' => '"before Friday afternoon" nghĩa là trước chiều thứ Sáu (chỉ mốc thời gian hoàn thành). "within" phải đi với một khoảng thời gian, "until" chỉ hành động kéo dài liên tục. Đáp án <strong>B. before</strong> là chính xác.',
                'translation' => 'Ông Henderson đã yêu cầu tất cả các báo cáo chi phí phải được nộp trước chiều thứ Sáu.',
            ],
            [
                'id' => 102,
                'part' => 'Part 5',
                'passage_title' => null,
                'passage' => null,
                'question_range' => 'Part 5',
                'question_number' => 102,
                'stem' => 'The marketing department is looking for an _______ candidate who has at least three years of experience in digital advertising.',
                'options' => [
                    'A' => 'energetic',
                    'B' => 'energetically',
                    'C' => 'energy',
                    'D' => 'energize',
                ],
                'correct' => 'A',
                'explanation' => 'Vị trí chỗ trống đứng sau mạo từ "an" và trước danh từ "candidate", do đó cần một tính từ bổ nghĩa. Đáp án <strong>A. energetic</strong> (nhiệt huyết, năng động) là chính xác.',
                'translation' => 'Phòng tiếp thị đang tìm kiếm một ứng viên năng động có ít nhất 3 năm kinh nghiệm trong lĩnh vực quảng cáo kỹ thuật số.',
            ],
            [
                'id' => 103,
                'part' => 'Part 5',
                'passage_title' => null,
                'passage' => null,
                'question_range' => 'Part 5',
                'question_number' => 103,
                'stem' => 'Customer service representatives are required to handle client inquiries _______ and professionally.',
                'options' => [
                    'A' => 'prompt',
                    'B' => 'promptly',
                    'C' => 'promptness',
                    'D' => 'prompting',
                ],
                'correct' => 'B',
                'explanation' => 'Cần một trạng từ liên kết bằng từ "and" song hành với trạng từ "professionally" để bổ nghĩa cho động từ "handle". Đáp án <strong>B. promptly</strong> (nhanh chóng, kịp thời) là chính xác.',
                'translation' => 'Các đại diện dịch vụ khách hàng được yêu cầu xử lý các thắc mắc của khách hàng một cách nhanh chóng và chuyên nghiệp.',
            ],
            [
                'id' => 104,
                'part' => 'Part 5',
                'passage_title' => null,
                'passage' => null,
                'question_range' => 'Part 5',
                'question_number' => 104,
                'stem' => 'With bicycling becoming more popular than ever, the city council plans to _______ the number of bicycle lanes along riverside avenues.',
                'options' => [
                    'A' => 'define',
                    'B' => 'inspect',
                    'C' => 'expand',
                    'D' => 'relieve',
                ],
                'correct' => 'C',
                'explanation' => 'Dựa vào ngữ cảnh "bicycling becoming more popular", hội đồng thành phố có kế hoạch "mở rộng" số lượng làn đường xe đạp. Đáp án <strong>C. expand</strong> là chính xác.',
                'translation' => 'Khi việc đi xe đạp ngày càng phổ biến hơn bao giờ hết, hội đồng thành phố dự định mở rộng số lượng làn đường xe đạp dọc theo các đại lộ ven sông.',
            ],
            [
                'id' => 105,
                'part' => 'Part 5',
                'passage_title' => null,
                'passage' => null,
                'question_range' => 'Part 5',
                'question_number' => 105,
                'stem' => 'The marketing director suggested that we _______ the product launch date until next quarter to avoid overlapping campaigns.',
                'options' => [
                    'A' => 'postpone',
                    'B' => 'postponed',
                    'C' => 'postponement',
                    'D' => 'postponing',
                ],
                'correct' => 'A',
                'explanation' => 'Cấu trúc bàng thái cách / giả định thức: <code>S + suggest + that + S + (should) + V-bare</code>. Do đó ta chọn động từ nguyên thể không "to". Đáp án <strong>A. postpone</strong> là chính xác.',
                'translation' => 'Giám đốc tiếp thị đề xuất chúng ta hoãn ngày ra mắt sản phẩm cho đến quý sau để tránh trùng lặp chiến dịch.',
            ],
            [
                'id' => 106,
                'part' => 'Part 5',
                'passage_title' => null,
                'passage' => null,
                'question_range' => 'Part 5',
                'question_number' => 106,
                'stem' => 'All passengers must present their boarding passes _______ entering the departure gate.',
                'options' => [
                    'A' => 'upon',
                    'B' => 'between',
                    'C' => 'among',
                    'D' => 'inside',
                ],
                'correct' => 'A',
                'explanation' => 'Cụm từ "upon + V-ing" mang nghĩa "ngay khi, vào lúc làm gì". Hành khách phải xuất trình thẻ lên máy bay ngay khi vào cổng khởi hành. Đáp án <strong>A. upon</strong> là chính xác.',
                'translation' => 'Tất cả hành khách phải xuất trình thẻ lên máy bay khi bước vào cửa khởi hành.',
            ],
            [
                'id' => 107,
                'part' => 'Part 5',
                'passage_title' => null,
                'passage' => null,
                'question_range' => 'Part 5',
                'question_number' => 107,
                'stem' => 'The annual budget report will be released as soon as the financial audit is _______.',
                'options' => [
                    'A' => 'complete',
                    'B' => 'completely',
                    'C' => 'completion',
                    'D' => 'completing',
                ],
                'correct' => 'A',
                'explanation' => 'Đứng sau động từ to-be "is" cần một tính từ đóng vai trò vị ngữ. "complete" mang nghĩa hoàn thành, trọn vẹn. Đáp án <strong>A. complete</strong> là chính xác.',
                'translation' => 'Báo cáo ngân sách hàng năm sẽ được công bố ngay sau khi cuộc kiểm toán tài chính hoàn tất.',
            ],
            [
                'id' => 108,
                'part' => 'Part 5',
                'passage_title' => null,
                'passage' => null,
                'question_range' => 'Part 5',
                'question_number' => 108,
                'stem' => 'Neither the sales director nor the team members _______ able to attend the regional conference yesterday.',
                'options' => [
                    'A' => 'was',
                    'B' => 'were',
                    'C' => 'is',
                    'D' => 'has been',
                ],
                'correct' => 'B',
                'explanation' => 'Quy tắc hòa hợp chủ vị với cấu trúc "Neither A nor B": Động từ được chia theo chủ ngữ gần nó nhất (B = "the team members" số nhiều). Thời điểm quá khứ "yesterday" nên chọn <strong>B. were</strong>.',
                'translation' => 'Cả giám đốc bán hàng lẫn các thành viên trong nhóm đều không thể tham dự hội nghị khu vực ngày hôm qua.',
            ],
            [
                'id' => 109,
                'part' => 'Part 5',
                'passage_title' => null,
                'passage' => null,
                'question_range' => 'Part 5',
                'question_number' => 109,
                'stem' => 'The warranty covers any defects in manufacturing for _______ of two years from the purchase date.',
                'options' => [
                    'A' => 'a period',
                    'B' => 'an interval',
                    'C' => 'a session',
                    'D' => 'an era',
                ],
                'correct' => 'A',
                'explanation' => 'Cụm từ cố định trong văn phong hợp đồng/bảo hành: "for a period of + khoảng thời gian" (trong thời hạn...). Đáp án <strong>A. a period</strong> là chính xác.',
                'translation' => 'Bảo hành bao gồm mọi lỗi do sản xuất trong thời hạn hai năm kể từ ngày mua.',
            ],
            [
                'id' => 110,
                'part' => 'Part 5',
                'passage_title' => null,
                'passage' => null,
                'question_range' => 'Part 5',
                'question_number' => 110,
                'stem' => 'Please review the attached contract carefully and return the signed copy _______ five business days.',
                'options' => [
                    'A' => 'within',
                    'B' => 'during',
                    'C' => 'among',
                    'D' => 'about',
                ],
                'correct' => 'A',
                'explanation' => '"within + khoảng thời gian" nghĩa là "trong vòng bao lâu". "during" đi với danh từ chỉ sự kiện, không đi với số đếm ngày cụ thể. Đáp án <strong>A. within</strong> là chính xác.',
                'translation' => 'Vui lòng xem kỹ hợp đồng đính kèm và gửi lại bản đã ký trong vòng 5 ngày làm việc.',
            ],
        ];
    }

    /**
     * Màn hình làm bài luyện tập Part 5 / Part 6 (Khớp ảnh mẫu vocaPREP)
     */
    public function practice(Request $request): View
    {
        $allQuestions = $this->getQuestionBank();

        if ($request->query('retry_wrong')) {
            $lastResult = session('reading_practice_result');
            if ($lastResult && !empty($lastResult['details'])) {
                $wrongIds = collect($lastResult['details'])
                    ->where('status', 'incorrect')
                    ->pluck('id')
                    ->all();

                if (!empty($wrongIds)) {
                    $selectedQuestions = array_values(array_filter($allQuestions, function ($q) use ($wrongIds) {
                        return in_array($q['id'], $wrongIds);
                    }));

                    return view('pages.doc-luyen-tap', [
                        'questions' => $selectedQuestions,
                        'totalCount' => count($selectedQuestions),
                        'targetCount' => count($selectedQuestions),
                    ]);
                }
            }
        }

        $count = (int) $request->query('count', 10);
        if ($count <= 0) {
            $count = 10;
        }

        $selectedQuestions = array_slice($allQuestions, 0, min($count, count($allQuestions)));

        return view('pages.doc-luyen-tap', [
            'questions' => $selectedQuestions,
            'totalCount' => count($selectedQuestions),
            'targetCount' => $count,
        ]);
    }

    /**
     * Xử lý nộp bài thi luyện tập Đọc
     */
    public function submit(Request $request): RedirectResponse
    {
        $submittedAnswers = $request->input('answers', []);
        $timeSpentSeconds = (int) $request->input('time_spent', 165); // Mặc định 2m45s nếu không đo
        $requestedCount = (int) $request->input('total_count', 10);

        $bank = $this->getQuestionBank();
        $questions = array_slice($bank, 0, min($requestedCount, count($bank)));

        $correctCount = 0;
        $incorrectCount = 0;
        $unansweredCount = 0;
        $details = [];

        foreach ($questions as $q) {
            $qid = $q['id'];
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

            $details[] = [
                'id' => $q['id'],
                'part' => $q['part'],
                'question_number' => $q['question_number'],
                'stem' => $q['stem'],
                'passage_title' => $q['passage_title'],
                'passage' => $q['passage'],
                'options' => $q['options'],
                'user_answer' => $userPick,
                'correct_answer' => $q['correct'],
                'status' => $status,
                'explanation' => $q['explanation'],
                'translation' => $q['translation'],
            ];
        }

        $total = count($questions);
        $scorePercent = $total > 0 ? (int) round(($correctCount / $total) * 100) : 0;

        $minutes = floor($timeSpentSeconds / 60);
        $seconds = $timeSpentSeconds % 60;
        $formattedTime = sprintf('%02d:%02d', $minutes, $seconds);
        $timeSpentText = max(1, (int) round($timeSpentSeconds / 60)) . ' phút';

        $resultData = [
            'total' => $total,
            'correct_count' => $correctCount,
            'incorrect_count' => $incorrectCount,
            'unanswered_count' => $unansweredCount,
            'score_percent' => $scorePercent,
            'time_spent_seconds' => $timeSpentSeconds,
            'time_spent_formatted' => $formattedTime,
            'time_spent_text' => $timeSpentText,
            'previous_percent' => '40%',
            'submitted_at' => now()->format('H:i • d/m/Y'),
            'details' => $details,
        ];

        session(['reading_practice_result' => $resultData]);

        return redirect()->route('luyen-tap.doc.ket-qua');
    }

    /**
     * Màn hình kết quả luyện tập Đọc
     */
    public function result(Request $request): View
    {
        $result = session('reading_practice_result');

        // Nếu người dùng vào thẳng không qua submit, tạo dữ liệu thực tế từ ngân hàng câu hỏi
        if (! $result) {
            $bank = array_slice($this->getQuestionBank(), 0, 10);
            $correctCount = 8;
            $incorrectCount = 2;
            $unansweredCount = 0;
            $details = [];

            foreach ($bank as $index => $q) {
                // Giả lập 8 câu đúng, 2 câu sai để hiển thị đầy đủ trực quan
                if ($index < 8) {
                    $status = 'correct';
                    $userPick = $q['correct'];
                } else {
                    $status = 'incorrect';
                    $wrongKeys = array_diff(array_keys($q['options']), [$q['correct']]);
                    $userPick = reset($wrongKeys);
                }

                $details[] = [
                    'id' => $q['id'],
                    'part' => $q['part'],
                    'question_number' => $q['question_number'],
                    'stem' => $q['stem'],
                    'passage_title' => $q['passage_title'],
                    'passage' => $q['passage'],
                    'options' => $q['options'],
                    'user_answer' => $userPick,
                    'correct_answer' => $q['correct'],
                    'status' => $status,
                    'explanation' => $q['explanation'],
                    'translation' => $q['translation'],
                ];
            }

            $result = [
                'total' => 10,
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
                'unanswered_count' => $unansweredCount,
                'score_percent' => 80,
                'time_spent_seconds' => 332,
                'time_spent_formatted' => '05:32',
                'time_spent_text' => '5 phút',
                'previous_percent' => '40%',
                'submitted_at' => now()->format('H:i • d/m/Y'),
                'details' => $details,
            ];
        }

        return view('pages.doc-ket-qua', [
            'result' => $result,
        ]);
    }
}
