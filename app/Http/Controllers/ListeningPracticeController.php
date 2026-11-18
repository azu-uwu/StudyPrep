<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Services\TextToSpeechService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ListeningPracticeController extends Controller
{
    /**
     * Lấy danh sách câu hỏi Listening trạng thái ACTIVE từ Database (kèm resolved_media_url & resolved_audio_url).
     * Nếu DB chưa có dữ liệu Listening ACTIVE thì fallback về bộ mẫu để không gián đoạn giao diện.
     *
     * @param  array<int>|null  $specificIds
     * @return array<int, array<string, mixed>>
     */
    private function loadListeningQuestions(?int $partFilter = null, int $limit = 20, ?array $specificIds = null): array
    {
        if (! Schema::hasTable('questions') || ! Schema::hasTable('exam_section')) {
            return $this->getSampleQuestionBank();
        }

        $targetCodes = $partFilter && in_array($partFilter, [1, 2, 3, 4], true)
            ? ['PART'.$partFilter]
            : ['PART1', 'PART2', 'PART3', 'PART4'];

        $query = Question::query()
            ->with(['section', 'questionSet', 'options'])
            ->where('status', Question::STATUS_ACTIVE)
            ->whereHas('section', fn ($q) => $q->whereIn('code', $targetCodes));

        if (! empty($specificIds)) {
            $query->whereIn('question_id', $specificIds)->orderBy('question_id');
        } else {
            $query->orderBy('section_id')->orderBy('question_id')->limit($limit);
        }

        $dbQuestions = $query->get();
        if ($dbQuestions->isEmpty()) {
            return $this->getSampleQuestionBank();
        }

        $ttsService = app(TextToSpeechService::class);
        $mapped = [];

        foreach ($dbQuestions as $idx => $q) {
            $partCode = strtoupper((string) ($q->section?->code ?? 'PART1'));
            $partNumber = (int) preg_replace('/[^0-9]/', '', $partCode);
            if ($partNumber < 1 || $partNumber > 4) {
                $partNumber = 1;
            }

            $qSet = $q->questionSet;
            $imageUrl = null;
            $audioUrl = null;
            $rawTranscript = '';

            if ($qSet) {
                $mediaUrl = $qSet->resolved_media_url;
                $isImage = $mediaUrl !== null && (
                    strtoupper((string) $qSet->media_type) === 'IMAGE'
                    || preg_match('/\.(png|jpe?g|gif|webp|bmp)$/i', $mediaUrl) === 1
                );
                if ($isImage) {
                    $imageUrl = $mediaUrl;
                }
                $audioUrl = $qSet->resolved_audio_url;
                $rawTranscript = trim((string) ($qSet->transcript ?? ''));
            }

            $optionsMap = [];
            $correctLabel = 'A';
            foreach ($q->options->sortBy('label') as $opt) {
                $lbl = strtoupper((string) $opt->label);
                $optionsMap[$lbl] = (string) $opt->content;
                if ($opt->is_correct) {
                    $correctLabel = $lbl;
                }
            }
            if (empty($optionsMap)) {
                $optionsMap = $partNumber === 2
                    ? ['A' => 'Option A', 'B' => 'Option B', 'C' => 'Option C']
                    : ['A' => 'Option A', 'B' => 'Option B', 'C' => 'Option C', 'D' => 'Option D'];
            }

            // Kịch bản audio_text chuẩn TOEIC (cho fallback TTS nếu chưa có MP3)
            $audioText = $qSet ? $ttsService->buildToeicListeningScript($qSet) : '';
            if ($audioText === '') {
                $optLines = [];
                foreach ($optionsMap as $lbl => $txt) {
                    $optLines[] = "({$lbl}) {$txt}";
                }
                $audioText = ($partNumber === 2 ? $q->content.' ' : '').implode('. ', $optLines);
            }

            $wordCount = max(1, str_word_count($audioText));
            $durationSeconds = max(6, (int) round(($wordCount / 130) * 60));
            $durationFormatted = sprintf('%02d:%02d', intdiv($durationSeconds, 60), $durationSeconds % 60);

            // Transcript lines
            if ($rawTranscript !== '') {
                $transcriptLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $rawTranscript) ?: [])));
            } else {
                $transcriptLines = [];
                if ($partNumber >= 2) {
                    $transcriptLines[] = (string) $q->content;
                }
                foreach ($optionsMap as $lbl => $txt) {
                    $transcriptLines[] = "({$lbl}) {$txt}";
                }
            }

            $explanationText = trim((string) ($q->explanation ?? ''));
            $translationLines = $explanationText !== ''
                ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $explanationText) ?: [])))
                : ['Chưa có bản dịch chi tiết cho câu hỏi này.'];

            $partNames = [
                1 => 'Part 1: Mô tả hình',
                2 => 'Part 2: Hỏi & Đáp',
                3 => 'Part 3: Hội thoại ngắn',
                4 => 'Part 4: Bài nói ngắn',
            ];

            $mapped[] = [
                'id' => $q->question_id,
                'question_number' => $idx + 1,
                'part_code' => $partCode,
                'part_number' => $partNumber,
                'part' => $partNames[$partNumber] ?? 'Listening',
                'content' => (string) $q->content,
                'image' => $imageUrl,
                'audio_url' => $audioUrl,
                'audio_duration' => $durationFormatted,
                'audio_duration_seconds' => $durationSeconds,
                'audio_text' => $audioText,
                'options' => $optionsMap,
                'correct' => $correctLabel,
                'transcript' => $transcriptLines,
                'translation' => $translationLines,
                'explanation' => $explanationText !== '' ? nl2br(e($explanationText)) : 'Đáp án đúng là <strong>('.$correctLabel.')</strong>.',
            ];
        }

        return $mapped;
    }

    /**
     * Dữ liệu mẫu dự phòng khi Database chưa có câu hỏi Listening ACTIVE.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getSampleQuestionBank(): array
    {
        return [
            [
                'id' => 6,
                'question_number' => 6,
                'part_code' => 'PART1',
                'part_number' => 1,
                'part' => 'Part 1: Mô tả hình',
                'content' => 'Look at the picture and choose the statement that best describes what you see.',
                'image' => asset('images/listening/part1_supermarket.jpg'),
                'audio_url' => null,
                'audio_duration' => '00:29',
                'audio_duration_seconds' => 29,
                'audio_text' => '(A) She is at the supermarket. (B) She is selecting a flowerpot. (C) She is with her best friend. (D) She is paying for a flowerpot.',
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
                'id' => 4,
                'question_number' => 4,
                'part_code' => 'PART2',
                'part_number' => 2,
                'part' => 'Part 2: Hỏi & Đáp',
                'content' => 'Where is the marketing meeting going to be held?',
                'image' => null,
                'audio_url' => null,
                'audio_duration' => '00:21',
                'audio_duration_seconds' => 21,
                'audio_text' => 'Where is the marketing meeting going to be held? (A) At 10:00 AM. (B) In Conference Room B. (C) Yes, I received the report.',
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
     * Màn hình làm bài nghe (nối dữ liệu thật từ DB).
     */
    public function practice(Request $request): View
    {
        $count = (int) $request->query('count', 10);
        if ($count <= 0) {
            $count = 10;
        }

        $rawPart = (string) $request->query('part', '');
        $partFilter = null;
        if (preg_match('/(\d)/', $rawPart, $m) === 1) {
            $partFilter = (int) $m[1];
        }

        $allQuestions = $this->loadListeningQuestions($partFilter, $count);
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
     * Xử lý nộp bài nghe.
     */
    public function submit(Request $request): RedirectResponse
    {
        $submittedAnswers = $request->input('answers', []);
        $questionIds = array_values(array_filter(array_map('intval', (array) $request->input('question_ids', []))));
        $count = (int) $request->input('total_count', 10);
        $timeSpentSeconds = max(0, (int) $request->input('time_spent', 0));

        $bank = ! empty($questionIds)
            ? $this->loadListeningQuestions(null, count($questionIds), $questionIds)
            : $this->loadListeningQuestions(null, $count);
        $questions = array_slice($bank, 0, min(max(1, $count), count($bank)));

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

            $details[] = array_merge($q, [
                'user_answer' => $userPick,
                'correct_answer' => $q['correct'],
                'status' => $status,
            ]);
        }

        $total = count($details);
        $minutes = intdiv($timeSpentSeconds, 60);
        $seconds = $timeSpentSeconds % 60;
        $resultData = [
            'part_name' => 'Luyện nghe TOEIC',
            'total' => $total,
            'correct_count' => $correctCount,
            'incorrect_count' => $incorrectCount,
            'unanswered_count' => $unansweredCount,
            'score_percent' => $total > 0 ? (int) round(($correctCount / $total) * 100) : 0,
            'time_spent_seconds' => $timeSpentSeconds,
            'time_spent_formatted' => sprintf('%02d:%02d', $minutes, $seconds),
            'time_spent_text' => $minutes > 0 ? $minutes.' phút' : $seconds.' giây',
            'previous_percent' => '0%',
            'submitted_at' => now()->format('H:i • d/m/Y'),
            'answers' => $submittedAnswers,
            'details' => $details,
        ];

        session(['listening_practice_result' => $resultData]);

        return redirect()->route('luyen-tap.nghe.ket-qua');
    }

    /**
     * Màn hình tổng kết kết quả và xem đáp án luyện nghe.
     */
    public function result(Request $request): View
    {
        $result = session('listening_practice_result');
        $firstDetail = $result && ! empty($result['details']) ? reset($result['details']) : null;

        if (! is_array($firstDetail) || ! isset($firstDetail['options'])) {
            $bank = $this->loadListeningQuestions(null, 5);
            $count = min(max(1, (int) ($result['total'] ?? count($bank))), count($bank));
            $questions = array_slice($bank, 0, $count);
            $answers = $result['answers'] ?? [];

            if (! $result) {
                foreach ($questions as $index => $question) {
                    $wrongOptions = array_values(array_diff(array_keys($question['options']), [$question['correct']]));
                    $answers[$question['id']] = $index === 0
                        ? ($wrongOptions[0] ?? null)
                        : $question['correct'];
                }
            }

            $details = [];
            $correctCount = 0;
            $incorrectCount = 0;
            $unansweredCount = 0;

            foreach ($questions as $question) {
                $userPick = $answers[$question['id']] ?? null;
                if (empty($userPick)) {
                    $status = 'unanswered';
                    $unansweredCount++;
                } elseif ($userPick === $question['correct']) {
                    $status = 'correct';
                    $correctCount++;
                } else {
                    $status = 'incorrect';
                    $incorrectCount++;
                }

                $details[] = array_merge($question, [
                    'user_answer' => $userPick,
                    'correct_answer' => $question['correct'],
                    'status' => $status,
                ]);
            }

            $total = count($details);
            $timeSpentSeconds = max(0, (int) ($result['time_spent_seconds'] ?? 0));
            $minutes = intdiv($timeSpentSeconds, 60);
            $seconds = $timeSpentSeconds % 60;
            $result = [
                'part_name' => 'Luyện nghe TOEIC',
                'total' => $total,
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
                'unanswered_count' => $unansweredCount,
                'score_percent' => $total > 0 ? (int) round(($correctCount / $total) * 100) : 0,
                'time_spent_seconds' => $timeSpentSeconds,
                'time_spent_formatted' => sprintf('%02d:%02d', $minutes, $seconds),
                'time_spent_text' => $minutes > 0 ? $minutes.' phút' : $seconds.' giây',
                'previous_percent' => '0%',
                'submitted_at' => now()->format('H:i • d/m/Y'),
                'answers' => $answers,
                'details' => $details,
            ];
        }

        return view('pages.nghe-ket-qua', [
            'result' => $result,
        ]);
    }
}
