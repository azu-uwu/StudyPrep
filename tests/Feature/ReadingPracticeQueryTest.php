<?php

namespace Tests\Feature;

use App\Models\AnswerOption;
use App\Models\ExamSection;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\QuestionType;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class ReadingPracticeQueryTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    private ExamSection $part5Section;

    private ExamSection $part6Section;

    private ExamSection $part7Section;

    private QuestionType $mcType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createStudyPrepSchema();

        $skill = Skill::query()->create([
            'skill_id' => 2,
            'code' => 'RC',
            'skill_name' => 'Reading',
        ]);

        $this->part5Section = ExamSection::query()->create([
            'skill_id' => $skill->skill_id,
            'code' => 'PART5',
            'name' => 'Incomplete Sentences',
            'sequence_no' => 5,
        ]);

        $this->part6Section = ExamSection::query()->create([
            'skill_id' => $skill->skill_id,
            'code' => 'PART6',
            'name' => 'Text Completion',
            'sequence_no' => 6,
        ]);

        $this->part7Section = ExamSection::query()->create([
            'skill_id' => $skill->skill_id,
            'code' => 'PART7',
            'name' => 'Reading Comprehension',
            'sequence_no' => 7,
        ]);

        $this->mcType = QuestionType::query()->create([
            'name' => 'Multiple Choice',
            'response_mode' => 'SINGLE_CHOICE',
        ]);
    }

    /**
     * @param  array<int, array{label: string, content: string, is_correct: bool}>|null  $customOptions
     */
    private function createPart5Question(
        string $content,
        string $status = 'ACTIVE',
        ?array $customOptions = null,
        ?QuestionType $questionType = null,
        ?ExamSection $section = null,
        ?string $explanation = null
    ): Question {
        $q = Question::query()->create([
            'section_id' => $section?->section_id ?? $this->part5Section->section_id,
            'question_type_id' => $questionType?->question_type_id ?? $this->mcType->question_type_id,
            'content' => $content,
            'explanation' => $explanation ?? 'Sample explanation for '.$content,
            'difficulty' => 'MEDIUM',
            'status' => $status,
        ]);

        if ($customOptions !== null) {
            foreach ($customOptions as $opt) {
                AnswerOption::query()->create([
                    'question_id' => $q->question_id,
                    'label' => $opt['label'],
                    'content' => $opt['content'],
                    'is_correct' => $opt['is_correct'],
                ]);
            }
        } else {
            foreach (['A', 'B', 'C', 'D'] as $label) {
                AnswerOption::query()->create([
                    'question_id' => $q->question_id,
                    'label' => $label,
                    'content' => "Option {$label} for {$content}",
                    'is_correct' => $label === 'B',
                ]);
            }
        }

        return $q;
    }

    private function createQuestionSet(string $sectionCode, int $questionCount, string $title = 'Sample Set'): QuestionSet
    {
        $section = match ($sectionCode) {
            'PART6' => $this->part6Section,
            'PART7' => $this->part7Section,
            default => $this->part5Section,
        };

        $set = QuestionSet::query()->create([
            'title' => $title,
            'stimulus_text' => "<p>Passage for {$title}</p>",
        ]);

        for ($i = 1; $i <= $questionCount; $i++) {
            $q = Question::query()->create([
                'section_id' => $section->section_id,
                'question_type_id' => $this->mcType->question_type_id,
                'question_set_id' => $set->question_set_id,
                'content' => "Question {$i} of {$title} [{$i}]",
                'difficulty' => 'MEDIUM',
                'status' => Question::STATUS_ACTIVE,
            ]);

            foreach (['A', 'B', 'C', 'D'] as $label) {
                AnswerOption::query()->create([
                    'question_id' => $q->question_id,
                    'label' => $label,
                    'content' => "Option {$label}",
                    'is_correct' => $label === 'A',
                ]);
            }
        }

        return $set;
    }

    public function test_part5_queries_active_questions_without_revealing_answers(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->createPart5Question("Part 5 Active Question {$i}", 'ACTIVE');
        }
        $this->createPart5Question('Part 5 Draft Question', 'DRAFT');

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 3]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            if (count($questions) !== 3) {
                return false;
            }

            foreach ($questions as $q) {
                // Must not contain draft questions
                if (str_contains($q['stem'], 'Draft')) {
                    return false;
                }
                // Must not reveal answer or explanation
                if ($q['correct'] !== null || $q['explanation'] !== null) {
                    return false;
                }
                // Options must not expose is_correct
                if (isset($q['options']['is_correct'])) {
                    return false;
                }
            }

            return true;
        });
    }

    public function test_part5_only_returns_active_questions(): void
    {
        $this->createPart5Question('Active Question 1', 'ACTIVE');
        $this->createPart5Question('Active Question 2', 'ACTIVE');
        $this->createPart5Question('Draft Question', 'DRAFT');
        $this->createPart5Question('Archived Question', 'ARCHIVED');
        $this->createPart5Question('Pending Question', 'PENDING');

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            if (count($questions) !== 2) {
                return false;
            }

            foreach ($questions as $q) {
                if (! str_contains($q['stem'], 'Active')) {
                    return false;
                }
            }

            return true;
        });
    }

    public function test_part5_only_returns_part5_section_questions(): void
    {
        $this->createPart5Question('Part 5 Question', 'ACTIVE');

        // Tạo câu hỏi thuộc Part 6 nhưng không qua QuestionSet
        $this->createPart5Question(
            'Part 6 Stray Question',
            'ACTIVE',
            null,
            $this->mcType,
            $this->part6Section
        );

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            return count($questions) === 1 && str_contains($questions[0]['stem'], 'Part 5 Question');
        });
    }

    public function test_part5_only_returns_single_choice_type_questions(): void
    {
        $textType = QuestionType::query()->create([
            'name' => 'Text Response',
            'response_mode' => 'TEXT',
        ]);

        $this->createPart5Question('Single Choice Question', 'ACTIVE');
        $this->createPart5Question('Text Mode Question', 'ACTIVE', null, $textType);

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            return count($questions) === 1 && str_contains($questions[0]['stem'], 'Single Choice');
        });
    }

    public function test_part5_excludes_questions_with_fewer_or_more_than_four_options(): void
    {
        $this->createPart5Question('Valid 4 Options Question', 'ACTIVE');

        // Câu hỏi 3 đáp án
        $this->createPart5Question('Invalid 3 Options', 'ACTIVE', [
            ['label' => 'A', 'content' => 'Opt A', 'is_correct' => false],
            ['label' => 'B', 'content' => 'Opt B', 'is_correct' => true],
            ['label' => 'C', 'content' => 'Opt C', 'is_correct' => false],
        ]);

        // Câu hỏi 5 đáp án
        $this->createPart5Question('Invalid 5 Options', 'ACTIVE', [
            ['label' => 'A', 'content' => 'Opt A', 'is_correct' => false],
            ['label' => 'B', 'content' => 'Opt B', 'is_correct' => true],
            ['label' => 'C', 'content' => 'Opt C', 'is_correct' => false],
            ['label' => 'D', 'content' => 'Opt D', 'is_correct' => false],
            ['label' => 'E', 'content' => 'Opt E', 'is_correct' => false],
        ]);

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            return count($questions) === 1 && str_contains($questions[0]['stem'], 'Valid 4 Options');
        });
    }

    public function test_part5_excludes_questions_with_missing_or_duplicate_labels(): void
    {
        $this->createPart5Question('Valid ABCD Question', 'ACTIVE');

        // Thiếu D, có E
        $this->createPart5Question('Missing D Has E', 'ACTIVE', [
            ['label' => 'A', 'content' => 'Opt A', 'is_correct' => false],
            ['label' => 'B', 'content' => 'Opt B', 'is_correct' => true],
            ['label' => 'C', 'content' => 'Opt C', 'is_correct' => false],
            ['label' => 'E', 'content' => 'Opt E', 'is_correct' => false],
        ]);

        // Trùng nhãn A
        $this->createPart5Question('Duplicate Label A', 'ACTIVE', [
            ['label' => 'A', 'content' => 'Opt A1', 'is_correct' => false],
            ['label' => 'A', 'content' => 'Opt A2', 'is_correct' => true],
            ['label' => 'B', 'content' => 'Opt B', 'is_correct' => false],
            ['label' => 'C', 'content' => 'Opt C', 'is_correct' => false],
        ]);

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            return count($questions) === 1 && str_contains($questions[0]['stem'], 'Valid ABCD');
        });
    }

    public function test_part5_excludes_questions_with_no_correct_option_or_multiple_correct_options(): void
    {
        $this->createPart5Question('Valid 1 Correct Question', 'ACTIVE');

        // 0 đáp án đúng
        $this->createPart5Question('Zero Correct Options', 'ACTIVE', [
            ['label' => 'A', 'content' => 'Opt A', 'is_correct' => false],
            ['label' => 'B', 'content' => 'Opt B', 'is_correct' => false],
            ['label' => 'C', 'content' => 'Opt C', 'is_correct' => false],
            ['label' => 'D', 'content' => 'Opt D', 'is_correct' => false],
        ]);

        // 2 đáp án đúng
        $this->createPart5Question('Two Correct Options', 'ACTIVE', [
            ['label' => 'A', 'content' => 'Opt A', 'is_correct' => true],
            ['label' => 'B', 'content' => 'Opt B', 'is_correct' => true],
            ['label' => 'C', 'content' => 'Opt C', 'is_correct' => false],
            ['label' => 'D', 'content' => 'Opt D', 'is_correct' => false],
        ]);

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            return count($questions) === 1 && str_contains($questions[0]['stem'], 'Valid 1 Correct');
        });
    }

    public function test_part5_excludes_questions_with_empty_content_or_empty_option_content(): void
    {
        $this->createPart5Question('Valid Content Question', 'ACTIVE');

        // Nội dung câu hỏi rỗng hoặc chỉ có khoảng trắng
        $this->createPart5Question('', 'ACTIVE');
        $this->createPart5Question('   ', 'ACTIVE');

        // Nội dung đáp án rỗng hoặc chỉ có khoảng trắng
        $this->createPart5Question('Empty Option Content', 'ACTIVE', [
            ['label' => 'A', 'content' => 'Opt A', 'is_correct' => false],
            ['label' => 'B', 'content' => '', 'is_correct' => true],
            ['label' => 'C', 'content' => 'Opt C', 'is_correct' => false],
            ['label' => 'D', 'content' => 'Opt D', 'is_correct' => false],
        ]);

        $this->createPart5Question('Whitespace Option Content', 'ACTIVE', [
            ['label' => 'A', 'content' => 'Opt A', 'is_correct' => false],
            ['label' => 'B', 'content' => 'Opt B', 'is_correct' => true],
            ['label' => 'C', 'content' => '   ', 'is_correct' => false],
            ['label' => 'D', 'content' => 'Opt D', 'is_correct' => false],
        ]);

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            return count($questions) === 1 && str_contains($questions[0]['stem'], 'Valid Content');
        });
    }

    public function test_part5_never_returns_duplicate_question_ids(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->createPart5Question("Question {$i}", 'ACTIVE');
        }

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            $ids = collect($questions)->pluck('id');

            return $ids->count() === 10 && $ids->unique()->count() === 10;
        });
    }

    public function test_part5_payload_and_html_never_leak_correct_answer(): void
    {
        $this->createPart5Question('Secret Answer Question', 'ACTIVE', [
            ['label' => 'A', 'content' => 'Wrong Alpha', 'is_correct' => false],
            ['label' => 'B', 'content' => 'Secret Bravo Correct', 'is_correct' => true],
            ['label' => 'C', 'content' => 'Wrong Charlie', 'is_correct' => false],
            ['label' => 'D', 'content' => 'Wrong Delta', 'is_correct' => false],
        ]);

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 1]));
        $response->assertStatus(200);

        // Kiểm tra payload view
        $response->assertViewHas('questions', function (array $questions): bool {
            $q = $questions[0];

            return $q['correct'] === null
                && $q['explanation'] === null
                && $q['translation'] === null
                && ! isset($q['options']['is_correct'])
                && array_keys($q['options']) === ['A', 'B', 'C', 'D'];
        });

        // Kiểm tra HTML không chứa is_correct
        $content = $response->getContent();
        $this->assertStringNotContainsString('is_correct', $content);
        $this->assertStringNotContainsString('"correct":"B"', $content);
    }

    public function test_part5_count_handling_for_5_10_20(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->createPart5Question("Batch Question {$i}", 'ACTIVE');
        }

        // Chọn 5 câu -> trả 5, notice = null
        $resp5 = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 5]));
        $resp5->assertStatus(200);
        $resp5->assertViewHas('questions', fn ($q) => count($q) === 5);
        $resp5->assertViewHas('noticeMessage', fn ($msg) => $msg === null);

        // Chọn 10 câu -> trả 10, notice = null
        $resp10 = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $resp10->assertStatus(200);
        $resp10->assertViewHas('questions', fn ($q) => count($q) === 10);
        $resp10->assertViewHas('noticeMessage', fn ($msg) => $msg === null);

        // Chọn 20 câu -> trả 20, notice = null
        $resp20 = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 20]));
        $resp20->assertStatus(200);
        $resp20->assertViewHas('questions', fn ($q) => count($q) === 20);
        $resp20->assertViewHas('noticeMessage', fn ($msg) => $msg === null);
    }

    public function test_part5_notice_when_insufficient_valid_questions(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->createPart5Question("Available Valid {$i}", 'ACTIVE');
        }
        $this->createPart5Question('Draft Question', 'DRAFT');

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', fn ($q) => count($q) === 6);
        $response->assertViewHas('noticeMessage', function (?string $msg): bool {
            return $msg === 'Bạn đã chọn 10 câu. Hiện hệ thống có 6 câu Part 5 hợp lệ để luyện tập.';
        });
    }

    public function test_part5_view_displays_sequential_question_numbers_and_options_in_order(): void
    {
        // Tạo 3 câu hỏi với option đưa vào xáo trộn thứ tự: D, B, A, C
        for ($i = 1; $i <= 3; $i++) {
            $this->createPart5Question("Stem for question {$i}", 'ACTIVE', [
                ['label' => 'D', 'content' => "Delta {$i}", 'is_correct' => false],
                ['label' => 'B', 'content' => "Bravo {$i}", 'is_correct' => true],
                ['label' => 'A', 'content' => "Alpha {$i}", 'is_correct' => false],
                ['label' => 'C', 'content' => "Charlie {$i}", 'is_correct' => false],
            ]);
        }

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 3]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            if (count($questions) !== 3) {
                return false;
            }

            // Đánh số từ 1 đến N
            $this->assertSame(1, $questions[0]['question_number']);
            $this->assertSame(2, $questions[1]['question_number']);
            $this->assertSame(3, $questions[2]['question_number']);

            // Mỗi câu phải có options được sắp xếp A -> B -> C -> D
            foreach ($questions as $q) {
                $labels = array_keys($q['options']);
                if ($labels !== ['A', 'B', 'C', 'D']) {
                    return false;
                }
            }

            return true;
        });
    }

    public function test_part6_rounds_down_to_multiples_of_four_and_keeps_groups_intact(): void
    {
        // Create 5 valid sets (each exactly 4 questions = 20 questions total)
        for ($i = 1; $i <= 5; $i++) {
            $this->createQuestionSet('PART6', 4, "Part 6 Set {$i}");
        }
        // Create 1 invalid set with only 3 questions (should be ignored)
        $this->createQuestionSet('PART6', 3, 'Invalid 3-question set');

        // Case 1: Request 5 questions -> rounds down to 4 (1 set)
        $resp5 = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 6, 'count' => 5]));
        $resp5->assertStatus(200);
        $resp5->assertViewHas('questions', fn ($q) => count($q) === 4);

        // Case 2: Request 10 questions -> rounds down to 8 (2 sets of 4)
        $resp10 = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 6, 'count' => 10]));
        $resp10->assertStatus(200);
        $resp10->assertViewHas('questions', function (array $questions): bool {
            if (count($questions) !== 8) {
                return false;
            }

            // Must have exactly 2 distinct question sets
            $setIds = collect($questions)->pluck('question_set_id')->unique();
            if ($setIds->count() !== 2) {
                return false;
            }

            // Each set must have all 4 questions present
            foreach ($setIds as $setId) {
                $countInSet = collect($questions)->where('question_set_id', $setId)->count();
                if ($countInSet !== 4) {
                    return false;
                }
            }

            return true;
        });

        // Case 3: Request 15 questions -> rounds down to 12 (3 sets of 4)
        $resp15 = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 6, 'count' => 15]));
        $resp15->assertStatus(200);
        $resp15->assertViewHas('questions', fn ($q) => count($q) === 12);

        // Case 4: Request 20 questions -> gets all 20 questions (5 sets of 4)
        $resp20 = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 6, 'count' => 20]));
        $resp20->assertStatus(200);
        $resp20->assertViewHas('questions', fn ($q) => count($q) === 20);

        // Case 5: Request 3 questions (< 4) -> redirected to setup view with error message, not empty practice page
        $responseSmall = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 6, 'count' => 3]));
        $responseSmall->assertRedirect(url('/luyen-tap/doc?part=6&count=3'));
        $responseSmall->assertSessionHas('practice_error', 'Part 6 yêu cầu tối thiểu 4 câu (1 đoạn văn). Vui lòng chọn lại số lượng câu.');
    }

    public function test_part7_selects_optimal_subset_not_exceeding_target(): void
    {
        // Create sets with sizes 2, 3, 4, 5
        $this->createQuestionSet('PART7', 2, 'Part 7 Set 2-Q');
        $this->createQuestionSet('PART7', 3, 'Part 7 Set 3-Q');
        $this->createQuestionSet('PART7', 4, 'Part 7 Set 4-Q');
        $this->createQuestionSet('PART7', 5, 'Part 7 Set 5-Q');

        // Request 7 questions -> Knapsack optimal is 4 + 3 = 7 or 5 + 2 = 7 (exact match!)
        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 7, 'count' => 7]));
        $response->assertStatus(200);
        $response->assertViewHas('questions', function (array $questions): bool {
            return count($questions) === 7;
        });

        // Request 6 questions -> can pick 4 + 2 = 6 (exact match!)
        $response6 = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 7, 'count' => 6]));
        $response6->assertStatus(200);
        $response6->assertViewHas('questions', function (array $questions): bool {
            return count($questions) === 6;
        });

        // Request 8 questions -> can pick 5 + 3 = 8 (exact match!)
        $response8 = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 7, 'count' => 8]));
        $response8->assertStatus(200);
        $response8->assertViewHas('questions', function (array $questions): bool {
            return count($questions) === 8;
        });
    }

    public function test_part7_closest_lower_when_exact_match_impossible(): void
    {
        // Only sets of size 3 and 4 exist
        $this->createQuestionSet('PART7', 3, 'Set-3');
        $this->createQuestionSet('PART7', 4, 'Set-4');

        // Request 5 questions -> Cannot make 5. Knapsack should pick 4 (closest lower <= 5), NOT 7 (which would exceed)
        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 7, 'count' => 5]));
        $response->assertStatus(200);
        $response->assertViewHas('questions', function (array $questions): bool {
            // Must be exactly 4, never exceeding 5
            return count($questions) === 4;
        });
    }

    public function test_part6_questions_are_ordered_by_blank_number_within_group(): void
    {
        $set = QuestionSet::query()->create([
            'title' => 'Scrambled Set',
            'stimulus_text' => '<p>Passage with blanks</p>',
        ]);

        // Insert in reverse order: [134], [133], [132], [131]
        foreach ([134, 133, 132, 131] as $num) {
            $q = Question::query()->create([
                'section_id' => $this->part6Section->section_id,
                'question_type_id' => $this->mcType->question_type_id,
                'question_set_id' => $set->question_set_id,
                'content' => "Question stem for blank [{$num}]",
                'difficulty' => 'MEDIUM',
                'status' => Question::STATUS_ACTIVE,
            ]);

            foreach (['A', 'B', 'C', 'D'] as $label) {
                AnswerOption::query()->create([
                    'question_id' => $q->question_id,
                    'label' => $label,
                    'content' => "Option {$label}",
                    'is_correct' => $label === 'A',
                ]);
            }
        }

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 6, 'count' => 4]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            // Must be sorted [131] -> [132] -> [133] -> [134]
            $stems = collect($questions)->pluck('stem')->all();

            return str_contains($stems[0], '[131]')
                && str_contains($stems[1], '[132]')
                && str_contains($stems[2], '[133]')
                && str_contains($stems[3], '[134]');
        });
    }

    public function test_handles_empty_active_questions_without_mock_fallback_or_500(): void
    {
        // Empty database for Part 5 -> redirects back to setup view with error message, not empty practice page
        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $response->assertRedirect(url('/luyen-tap/doc?part=5&count=10'));
        $response->assertSessionHas('practice_error');
    }

    public function test_check_answer_api_validates_correct_and_incorrect_answer(): void
    {
        $q = $this->createPart5Question('Question for Check Answer API', 'ACTIVE', [
            ['label' => 'A', 'content' => 'Alpha', 'is_correct' => false],
            ['label' => 'B', 'content' => 'Bravo Correct', 'is_correct' => true],
            ['label' => 'C', 'content' => 'Charlie', 'is_correct' => false],
            ['label' => 'D', 'content' => 'Delta', 'is_correct' => false],
        ], null, null, 'Explanation for Bravo');

        // Bắt đầu phiên làm bài
        $this->withSession([
            'reading_practice_session' => [
                'part' => 5,
                'target_count' => 1,
                'question_ids' => [$q->question_id],
            ],
        ]);

        // Chọn B -> Đúng
        $respCorrect = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $q->question_id,
            'selected_option' => 'B',
            'action' => 'answer',
        ]);
        $respCorrect->assertStatus(200)
            ->assertJson([
                'success' => true,
                'question_id' => $q->question_id,
                'is_correct' => true,
                'is_right' => true,
                'user_answer' => 'B',
                'correct_answer' => 'B',
                'explanation' => 'Explanation for Bravo',
            ]);

        // Chọn A -> Sai
        $respWrong = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $q->question_id,
            'selected_option' => 'A',
            'action' => 'answer',
        ]);
        $respWrong->assertStatus(200)
            ->assertJson([
                'success' => true,
                'question_id' => $q->question_id,
                'is_correct' => false,
                'is_right' => false,
                'user_answer' => 'A',
                'correct_answer' => 'B',
                'explanation' => 'Explanation for Bravo',
            ]);
    }

    public function test_check_answer_api_supports_reveal_action_without_assigning_user_answer(): void
    {
        $q = $this->createPart5Question('Question for Reveal', 'ACTIVE', [
            ['label' => 'A', 'content' => 'Alpha', 'is_correct' => false],
            ['label' => 'B', 'content' => 'Bravo', 'is_correct' => false],
            ['label' => 'C', 'content' => 'Charlie Correct', 'is_correct' => true],
            ['label' => 'D', 'content' => 'Delta', 'is_correct' => false],
        ]);

        $this->withSession([
            'reading_practice_session' => [
                'part' => 5,
                'target_count' => 1,
                'question_ids' => [$q->question_id],
            ],
        ]);

        $response = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $q->question_id,
            'action' => 'reveal',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'question_id' => $q->question_id,
                'action' => 'reveal',
                'correct_answer' => 'C',
            ])
            ->assertJsonMissing(['user_answer' => 'C']);
    }

    public function test_check_answer_api_rejects_question_not_in_current_practice_session(): void
    {
        $q1 = $this->createPart5Question('Question in Session', 'ACTIVE');
        $q2 = $this->createPart5Question('Question outside Session', 'ACTIVE');

        $this->withSession([
            'reading_practice_session' => [
                'part' => 5,
                'target_count' => 1,
                'question_ids' => [$q1->question_id],
            ],
        ]);

        $response = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $q2->question_id,
            'selected_option' => 'A',
            'action' => 'answer',
        ]);

        $response->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function test_check_answer_api_returns_fallback_when_explanation_is_empty(): void
    {
        $q = $this->createPart5Question('Question without explanation', 'ACTIVE', null, null, null, '');

        $this->withSession([
            'reading_practice_session' => [
                'part' => 5,
                'target_count' => 1,
                'question_ids' => [$q->question_id],
            ],
        ]);

        $response = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $q->question_id,
            'selected_option' => 'B',
            'action' => 'answer',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'explanation' => 'Chưa có giải thích cho câu hỏi này.',
            ]);
    }

    public function test_submit_grading_five_correct_answers(): void
    {
        $questions = [];
        for ($i = 1; $i <= 5; $i++) {
            $questions[] = $this->createPart5Question("Question {$i}", 'ACTIVE');
        }
        $ids = collect($questions)->pluck('question_id')->all();

        $sessionData = [
            'part' => 5,
            'target_count' => 5,
            'question_ids' => $ids,
        ];

        // Đáp án B là đáp án đúng cho tất cả
        $answers = [];
        foreach ($ids as $id) {
            $answers[$id] = 'B';
        }

        $response = $this->withSession(['reading_practice_session' => $sessionData])
            ->post(route('luyen-tap.doc.nop-bai'), [
                'part' => 5,
                'question_ids' => implode(',', $ids),
                'total_count' => 5,
                'time_spent' => 120,
                'answers' => $answers,
            ]);

        $response->assertRedirect(route('luyen-tap.doc.ket-qua'));

        $result = session('reading_practice_result');
        $this->assertNotNull($result);
        $this->assertSame(5, $result['total']);
        $this->assertSame(5, $result['correct_count']);
        $this->assertSame(0, $result['incorrect_count']);
        $this->assertSame(0, $result['unanswered_count']);
        $this->assertSame(100, $result['score_percent']);
    }

    public function test_submit_grading_three_correct_and_two_incorrect(): void
    {
        $questions = [];
        for ($i = 1; $i <= 5; $i++) {
            $questions[] = $this->createPart5Question("Question {$i}", 'ACTIVE');
        }
        $ids = collect($questions)->pluck('question_id')->all();

        // 3 câu đầu chọn B (Đúng), 2 câu sau chọn A (Sai, vì đáp án đúng là B)
        $answers = [
            $ids[0] => 'B',
            $ids[1] => 'B',
            $ids[2] => 'B',
            $ids[3] => 'A',
            $ids[4] => 'A',
        ];

        $response = $this->withSession(['reading_practice_session' => ['part' => 5, 'question_ids' => $ids]])
            ->post(route('luyen-tap.doc.nop-bai'), [
                'part' => 5,
                'question_ids' => implode(',', $ids),
                'total_count' => 5,
                'answers' => $answers,
            ]);

        $response->assertRedirect(route('luyen-tap.doc.ket-qua'));

        $result = session('reading_practice_result');
        $this->assertSame(5, $result['total']);
        $this->assertSame(3, $result['correct_count']);
        $this->assertSame(2, $result['incorrect_count']);
        $this->assertSame(0, $result['unanswered_count']);
        $this->assertSame(60, $result['score_percent']);
    }

    public function test_submit_grading_two_correct_one_incorrect_two_unanswered(): void
    {
        $questions = [];
        for ($i = 1; $i <= 5; $i++) {
            $questions[] = $this->createPart5Question("Question {$i}", 'ACTIVE');
        }
        $ids = collect($questions)->pluck('question_id')->all();

        // 2 đúng, 1 sai, 2 bỏ qua
        $answers = [
            $ids[0] => 'B', // Đúng
            $ids[1] => 'B', // Đúng
            $ids[2] => 'C', // Sai
            // $ids[3] không có -> chưa trả lời
            // $ids[4] không có -> chưa trả lời
        ];

        $response = $this->withSession(['reading_practice_session' => ['part' => 5, 'question_ids' => $ids]])
            ->post(route('luyen-tap.doc.nop-bai'), [
                'part' => 5,
                'question_ids' => implode(',', $ids),
                'total_count' => 5,
                'answers' => $answers,
            ]);

        $response->assertRedirect(route('luyen-tap.doc.ket-qua'));

        $result = session('reading_practice_result');
        $this->assertSame(5, $result['total']);
        $this->assertSame(2, $result['correct_count']);
        $this->assertSame(1, $result['incorrect_count']);
        $this->assertSame(2, $result['unanswered_count']);
        $this->assertSame(40, $result['score_percent']);
        $this->assertSame(5, $result['correct_count'] + $result['incorrect_count'] + $result['unanswered_count']);
    }

    public function test_submit_grading_zero_answers_submitted(): void
    {
        $questions = [];
        for ($i = 1; $i <= 5; $i++) {
            $questions[] = $this->createPart5Question("Question {$i}", 'ACTIVE');
        }
        $ids = collect($questions)->pluck('question_id')->all();

        $response = $this->withSession(['reading_practice_session' => ['part' => 5, 'question_ids' => $ids]])
            ->post(route('luyen-tap.doc.nop-bai'), [
                'part' => 5,
                'question_ids' => implode(',', $ids),
                'total_count' => 5,
                'answers' => [],
            ]);

        $response->assertRedirect(route('luyen-tap.doc.ket-qua'));

        $result = session('reading_practice_result');
        $this->assertSame(5, $result['total']);
        $this->assertSame(0, $result['correct_count']);
        $this->assertSame(0, $result['incorrect_count']);
        $this->assertSame(5, $result['unanswered_count']);
        $this->assertSame(0, $result['score_percent']);
    }

    public function test_submit_preserves_exact_order_and_question_list_in_result_details(): void
    {
        $q1 = $this->createPart5Question('First Question Alpha', 'ACTIVE');
        $q2 = $this->createPart5Question('Second Question Beta', 'ACTIVE');
        $q3 = $this->createPart5Question('Third Question Gamma', 'ACTIVE');

        // Đảo ngược thứ tự trong session
        $orderedIds = [$q3->question_id, $q1->question_id, $q2->question_id];

        $response = $this->withSession(['reading_practice_session' => ['part' => 5, 'question_ids' => $orderedIds]])
            ->post(route('luyen-tap.doc.nop-bai'), [
                'part' => 5,
                'question_ids' => implode(',', $orderedIds),
                'total_count' => 3,
                'answers' => [
                    $q3->question_id => 'B',
                ],
            ]);

        $response->assertRedirect(route('luyen-tap.doc.ket-qua'));

        $result = session('reading_practice_result');
        $this->assertCount(3, $result['details']);

        // Thứ tự chi tiết phải khớp hoàn toàn với $orderedIds
        $this->assertSame($q3->question_id, $result['details'][0]['id']);
        $this->assertSame(1, $result['details'][0]['question_number']);
        $this->assertSame('Third Question Gamma', $result['details'][0]['stem']);

        $this->assertSame($q1->question_id, $result['details'][1]['id']);
        $this->assertSame(2, $result['details'][1]['question_number']);
        $this->assertSame('First Question Alpha', $result['details'][1]['stem']);

        $this->assertSame($q2->question_id, $result['details'][2]['id']);
        $this->assertSame(3, $result['details'][2]['question_number']);
        $this->assertSame('Second Question Beta', $result['details'][2]['stem']);
    }

    // =========================================================================
    // TESTS FOR BE-007.4: READING PART 6 - HOÀN THÀNH ĐOẠN VĂN
    // =========================================================================

    public function test_part6_excludes_sets_with_invalid_or_draft_questions(): void
    {
        // 1. Nhóm hợp lệ 100%: Đúng 4 câu ACTIVE, 4 options A/B/C/D, 1 is_correct
        $validSet = $this->createQuestionSet('PART6', 4, 'Valid Set Full');

        // 2. Nhóm có 3 câu ACTIVE và 1 câu DRAFT -> Loại bỏ toàn bộ nhóm
        $draftSet = $this->createQuestionSet('PART6', 3, 'Set with Draft');
        $qDraft = Question::query()->create([
            'section_id' => $this->part6Section->section_id,
            'question_type_id' => $this->mcType->question_type_id,
            'question_set_id' => $draftSet->question_set_id,
            'content' => 'Question 4 Draft',
            'difficulty' => 'MEDIUM',
            'status' => Question::STATUS_DRAFT,
        ]);
        foreach (['A', 'B', 'C', 'D'] as $lbl) {
            AnswerOption::query()->create([
                'question_id' => $qDraft->question_id,
                'label' => $lbl,
                'content' => "Option {$lbl}",
                'is_correct' => $lbl === 'A',
            ]);
        }

        // 3. Nhóm có 4 câu nhưng 1 câu chỉ có 3 options -> Loại bỏ toàn bộ nhóm
        $incompleteOptSet = $this->createQuestionSet('PART6', 3, 'Set Incomplete Opts');
        $q3Opt = Question::query()->create([
            'section_id' => $this->part6Section->section_id,
            'question_type_id' => $this->mcType->question_type_id,
            'question_set_id' => $incompleteOptSet->question_set_id,
            'content' => 'Question with 3 options',
            'difficulty' => 'MEDIUM',
            'status' => Question::STATUS_ACTIVE,
        ]);
        foreach (['A', 'B', 'C'] as $lbl) {
            AnswerOption::query()->create([
                'question_id' => $q3Opt->question_id,
                'label' => $lbl,
                'content' => "Option {$lbl}",
                'is_correct' => $lbl === 'A',
            ]);
        }

        // 4. Nhóm có 4 câu nhưng stimulus_text bị rỗng -> Loại bỏ toàn bộ nhóm
        $emptyTextSet = QuestionSet::query()->create([
            'title' => 'Empty Stimulus Set',
            'stimulus_text' => '   ',
        ]);
        for ($i = 1; $i <= 4; $i++) {
            $q = Question::query()->create([
                'section_id' => $this->part6Section->section_id,
                'question_type_id' => $this->mcType->question_type_id,
                'question_set_id' => $emptyTextSet->question_set_id,
                'content' => "Question {$i}",
                'difficulty' => 'MEDIUM',
                'status' => Question::STATUS_ACTIVE,
            ]);
            foreach (['A', 'B', 'C', 'D'] as $lbl) {
                AnswerOption::query()->create([
                    'question_id' => $q->question_id,
                    'label' => $lbl,
                    'content' => "Option {$lbl}",
                    'is_correct' => $lbl === 'A',
                ]);
            }
        }

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 6, 'count' => 4]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions) use ($validSet): bool {
            if (count($questions) !== 4) {
                return false;
            }
            foreach ($questions as $q) {
                if ($q['question_set_id'] !== $validSet->question_set_id) {
                    return false;
                }
            }

            return true;
        });
    }

    public function test_part6_payload_never_leaks_correct_answer_or_explanation(): void
    {
        $this->createQuestionSet('PART6', 4, 'Secret Set Part 6');

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 6, 'count' => 4]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            if (count($questions) !== 4) {
                return false;
            }
            foreach ($questions as $q) {
                if ($q['correct'] !== null || $q['explanation'] !== null || $q['translation'] !== null) {
                    return false;
                }
                if (isset($q['options']['is_correct'])) {
                    return false;
                }
            }

            return true;
        });

        $content = $response->getContent();
        $this->assertStringNotContainsString('is_correct', $content);
        $this->assertStringNotContainsString('"correct":"A"', $content);
    }

    public function test_part6_requesting_eight_questions_returns_two_groups_of_four(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->createQuestionSet('PART6', 4, "Set Part 6 Batch {$i}");
        }

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 6, 'count' => 8]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            if (count($questions) !== 8) {
                return false;
            }
            $setIds = collect($questions)->pluck('question_set_id')->unique();
            if ($setIds->count() !== 2) {
                return false;
            }
            foreach ($setIds as $sid) {
                if (collect($questions)->where('question_set_id', $sid)->count() !== 4) {
                    return false;
                }
            }

            return true;
        });

        $sessionData = session('reading_practice_session');
        $this->assertNotNull($sessionData);
        $this->assertSame(6, $sessionData['part']);
        $this->assertCount(8, $sessionData['question_ids']);
        $this->assertCount(2, $sessionData['groups']);
    }

    public function test_part6_individual_question_check_blocked_on_part5_endpoint(): void
    {
        $set = $this->createQuestionSet('PART6', 4, 'Block Single Check Set');
        $q = $set->questions()->first();

        // 1. Khi phiên làm việc là Part 6
        $this->withSession([
            'reading_practice_session' => [
                'part' => 6,
                'target_count' => 4,
                'question_ids' => [$q->question_id],
            ],
        ]);

        $response = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $q->question_id,
            'selected_option' => 'A',
            'action' => 'answer',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Câu hỏi Part 6 phải được kiểm tra theo nhóm đoạn văn.',
            ]);

        // 2. Thậm chí khi session trống nhưng câu hỏi thuộc Part 6
        session()->forget('reading_practice_session');
        $responseDirect = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $q->question_id,
            'selected_option' => 'A',
            'action' => 'answer',
        ]);

        $responseDirect->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Câu hỏi Part 6 phải được kiểm tra theo nhóm đoạn văn.',
            ]);
    }

    public function test_part6_group_check_api_rejects_when_not_all_questions_answered(): void
    {
        $set = $this->createQuestionSet('PART6', 4, 'Incomplete Answer Set');
        $qids = $set->questions()->pluck('question_id')->all();

        $this->withSession([
            'reading_practice_session' => [
                'part' => 6,
                'target_count' => 4,
                'question_ids' => $qids,
                'groups' => [
                    $set->question_set_id => [
                        'question_set_id' => $set->question_set_id,
                        'question_ids' => $qids,
                        'completed' => false,
                        'answers' => [],
                    ],
                ],
            ],
        ]);

        // Chỉ gửi 3 câu trả lời (thiếu câu thứ 4)
        $response = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set->question_set_id,
            'answers' => [
                $qids[0] => 'A',
                $qids[1] => 'B',
                $qids[2] => 'C',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Bạn cần trả lời đầy đủ tất cả các câu hỏi trong đoạn văn trước khi xem kết quả.',
            ]);
    }

    public function test_part6_group_check_api_rejects_when_set_not_in_session(): void
    {
        $set1 = $this->createQuestionSet('PART6', 4, 'Set In Session');
        $set2 = $this->createQuestionSet('PART6', 4, 'Set Outside Session');

        $this->withSession([
            'reading_practice_session' => [
                'part' => 6,
                'target_count' => 4,
                'question_ids' => $set1->questions()->pluck('question_id')->all(),
                'groups' => [
                    $set1->question_set_id => [
                        'question_set_id' => $set1->question_set_id,
                        'question_ids' => $set1->questions()->pluck('question_id')->all(),
                        'completed' => false,
                        'answers' => [],
                    ],
                ],
            ],
        ]);

        $qids2 = $set2->questions()->pluck('question_id')->all();
        $response = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set2->question_set_id,
            'answers' => [
                $qids2[0] => 'A',
                $qids2[1] => 'A',
                $qids2[2] => 'A',
                $qids2[3] => 'A',
            ],
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Nhóm đoạn văn không thuộc lượt luyện tập hiện tại.',
            ]);
    }

    public function test_part6_group_check_api_grades_full_group_accurately_and_locks_result(): void
    {
        $set = $this->createQuestionSet('PART6', 4, 'Full Check Set');
        $qids = $set->questions()->pluck('question_id')->all();

        $this->withSession([
            'reading_practice_session' => [
                'part' => 6,
                'target_count' => 4,
                'question_ids' => $qids,
                'groups' => [
                    $set->question_set_id => [
                        'question_set_id' => $set->question_set_id,
                        'question_ids' => $qids,
                        'completed' => false,
                        'answers' => [],
                    ],
                ],
            ],
        ]);

        // Trong createQuestionSet, đáp án đúng mặc định là A cho tất cả câu.
        // Gửi: 2 câu A (Đúng), 1 câu B (Sai), 1 câu C (Sai) -> Đúng 2/4 câu
        $response = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set->question_set_id,
            'answers' => [
                $qids[0] => 'A',
                $qids[1] => 'A',
                $qids[2] => 'B',
                $qids[3] => 'C',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'question_set_id' => $set->question_set_id,
                'correct_count' => 2,
                'total_questions' => 4,
            ]);

        $data = $response->json();
        $this->assertTrue($data['results'][0]['is_correct']);
        $this->assertTrue($data['results'][1]['is_correct']);
        $this->assertFalse($data['results'][2]['is_correct']);
        $this->assertFalse($data['results'][3]['is_correct']);

        // Gọi lại lần 2 với cùng tập đáp án -> Hệ thống trả lại kết quả đã cache (already_completed)
        $responseSame = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set->question_set_id,
            'answers' => [
                $qids[0] => 'A',
                $qids[1] => 'A',
                $qids[2] => 'B',
                $qids[3] => 'C',
            ],
        ]);

        $responseSame->assertStatus(200)
            ->assertJson([
                'success' => true,
                'already_completed' => true,
                'correct_count' => 2,
            ]);

        // Gọi lại lần 3 với đáp án đã thay đổi: Theo nghiệp vụ BE-007.7 cho phép đổi đáp án và tính lại điểm
        $responseNew = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set->question_set_id,
            'answers' => [
                $qids[0] => 'A',
                $qids[1] => 'A',
                $qids[2] => 'A',
                $qids[3] => 'A',
            ],
        ]);

        $responseNew->assertStatus(200)
            ->assertJson([
                'success' => true,
                'correct_count' => 4,
            ]);
    }

    public function test_part6_submit_grades_partially_completed_groups_correctly(): void
    {
        $set1 = $this->createQuestionSet('PART6', 4, 'Set 1');
        $set2 = $this->createQuestionSet('PART6', 4, 'Set 2');

        $qids1 = $set1->questions()->pluck('question_id')->all();
        $qids2 = $set2->questions()->pluck('question_id')->all();
        $allIds = array_merge($qids1, $qids2);

        // Giả lập người dùng chỉ làm 2 câu ở nhóm 1 (1 câu đúng A, 1 câu sai B), nhóm 2 chưa làm câu nào
        $answers = [
            $qids1[0] => 'A', // Đúng
            $qids1[1] => 'B', // Sai (đáp án đúng là A)
        ];

        $sessionData = [
            'part' => 6,
            'target_count' => 8,
            'question_ids' => $allIds,
            'groups' => [
                $set1->question_set_id => [
                    'question_set_id' => $set1->question_set_id,
                    'question_ids' => $qids1,
                    'completed' => false,
                    'answers' => [],
                ],
                $set2->question_set_id => [
                    'question_set_id' => $set2->question_set_id,
                    'question_ids' => $qids2,
                    'completed' => false,
                    'answers' => [],
                ],
            ],
        ];

        $response = $this->withSession(['reading_practice_session' => $sessionData])
            ->post(route('luyen-tap.doc.nop-bai'), [
                'part' => 6,
                'question_ids' => implode(',', $allIds),
                'total_count' => 8,
                'time_spent' => 150,
                'answers' => $answers,
            ]);

        $response->assertRedirect(route('luyen-tap.doc.ket-qua'));

        $result = session('reading_practice_result');
        $this->assertNotNull($result);
        $this->assertSame(6, $result['part']);
        $this->assertSame('Part 6 — Hoàn thành đoạn văn', $result['part_name']);
        $this->assertSame(8, $result['total']);
        $this->assertSame(1, $result['correct_count']);
        $this->assertSame(1, $result['incorrect_count']);
        $this->assertSame(6, $result['unanswered_count']);
        $this->assertSame(13, $result['score_percent']); // round(1/8 * 100) = 13%

        // Chi tiết bài làm: Mỗi câu hỏi phải có passage & passage_title đi kèm
        $this->assertCount(8, $result['details']);
        foreach ($result['details'] as $detail) {
            $this->assertNotNull($detail['passage']);
            $this->assertNotNull($detail['passage_title']);
        }
    }

    public function test_part6_group_check_logic_supports_variable_question_counts_for_part7_reuse(): void
    {
        // Kiểm tra tính linh hoạt của checkGroupAnswers khi nhóm có 3 câu (chuẩn bị tái sử dụng cho Part 7)
        $set3 = $this->createQuestionSet('PART7', 3, 'Part 7 3-Question Set');
        $qids = $set3->questions()->pluck('question_id')->all();

        $this->withSession([
            'reading_practice_session' => [
                'part' => 7,
                'target_count' => 3,
                'question_ids' => $qids,
                'groups' => [
                    $set3->question_set_id => [
                        'question_set_id' => $set3->question_set_id,
                        'question_ids' => $qids,
                        'completed' => false,
                        'answers' => [],
                    ],
                ],
            ],
        ]);

        $response = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set3->question_set_id,
            'answers' => [
                $qids[0] => 'A',
                $qids[1] => 'A',
                $qids[2] => 'A',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'question_set_id' => $set3->question_set_id,
                'correct_count' => 3,
                'total_questions' => 3,
            ]);
    }

    public function test_part7_queries_active_questions_without_revealing_answers(): void
    {
        $this->createQuestionSet('PART7', 3, 'Part 7 Set A');
        $this->createQuestionSet('PART7', 4, 'Part 7 Set B');

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 7, 'count' => 7]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            if (count($questions) !== 7) {
                return false;
            }

            // Số thứ tự đánh liên tục 1 đến 7
            foreach ($questions as $idx => $q) {
                if ($q['question_number'] !== $idx + 1) {
                    return false;
                }
                if ($q['correct'] !== null || $q['explanation'] !== null) {
                    return false;
                }
                if ($q['part_number'] !== 7) {
                    return false;
                }
                if (empty($q['stem'])) {
                    return false;
                }
                if (empty($q['passage'])) {
                    return false;
                }
                if (count($q['options']) !== 4) {
                    return false;
                }
            }

            return true;
        });

        // Kiểm tra session đã lưu groups cho Part 7
        $sessionData = session('reading_practice_session');
        $this->assertNotNull($sessionData);
        $this->assertSame(7, $sessionData['part']);
        $this->assertCount(2, $sessionData['groups']);
    }

    public function test_part7_supports_groups_of_2_3_4_5_questions(): void
    {
        $this->createQuestionSet('PART7', 2, 'Set 2 Questions');
        $this->createQuestionSet('PART7', 3, 'Set 3 Questions');
        $this->createQuestionSet('PART7', 4, 'Set 4 Questions');
        $this->createQuestionSet('PART7', 5, 'Set 5 Questions');

        // Request 14 questions -> 5 + 4 + 3 + 2 = 14 (tất cả 4 nhóm hợp lệ)
        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 7, 'count' => 14]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            return count($questions) === 14;
        });
    }

    public function test_part7_excludes_sets_with_fewer_than_two_or_more_than_five_questions(): void
    {
        // Nhóm 1 câu (không hợp lệ cho Part 7)
        $this->createQuestionSet('PART7', 1, 'Set 1 Question');
        // Nhóm 6 câu (không hợp lệ cho Part 7)
        $this->createQuestionSet('PART7', 6, 'Set 6 Questions');
        // Nhóm 3 câu (hợp lệ)
        $this->createQuestionSet('PART7', 3, 'Valid Set 3');

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 7, 'count' => 10]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            // Chỉ lấy nhóm 3 câu, bỏ qua nhóm 1 câu và nhóm 6 câu
            return count($questions) === 3;
        });
    }

    public function test_part7_excludes_entire_set_if_any_question_is_invalid_or_draft(): void
    {
        // Nhóm có 4 câu nhưng 1 câu bị DRAFT -> Loại bỏ toàn bộ nhóm
        $invalidSet = QuestionSet::query()->create([
            'title' => 'Set With One Draft Question',
            'stimulus_text' => '<p>Passage content</p>',
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $q = Question::query()->create([
                'section_id' => $this->part7Section->section_id,
                'question_type_id' => $this->mcType->question_type_id,
                'question_set_id' => $invalidSet->question_set_id,
                'content' => "Question {$i}",
                'difficulty' => 'MEDIUM',
                'status' => Question::STATUS_ACTIVE,
            ]);
            foreach (['A', 'B', 'C', 'D'] as $lbl) {
                AnswerOption::query()->create([
                    'question_id' => $q->question_id,
                    'label' => $lbl,
                    'content' => "Option {$lbl}",
                    'is_correct' => $lbl === 'A',
                ]);
            }
        }

        // Câu thứ 4 là DRAFT
        $draftQ = Question::query()->create([
            'section_id' => $this->part7Section->section_id,
            'question_type_id' => $this->mcType->question_type_id,
            'question_set_id' => $invalidSet->question_set_id,
            'content' => 'Draft Question',
            'difficulty' => 'MEDIUM',
            'status' => 'DRAFT',
        ]);
        foreach (['A', 'B', 'C', 'D'] as $lbl) {
            AnswerOption::query()->create([
                'question_id' => $draftQ->question_id,
                'label' => $lbl,
                'content' => "Option {$lbl}",
                'is_correct' => $lbl === 'A',
            ]);
        }

        // Nhóm hợp lệ 3 câu
        $this->createQuestionSet('PART7', 3, 'Valid Set');

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 7, 'count' => 10]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            return count($questions) === 3;
        });
    }

    public function test_part7_excludes_sets_with_empty_stimulus_text_and_no_media(): void
    {
        $emptyTextSet = QuestionSet::query()->create([
            'title' => 'Empty Passage Set',
            'stimulus_text' => '   ',
            'media_url' => null,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $q = Question::query()->create([
                'section_id' => $this->part7Section->section_id,
                'question_type_id' => $this->mcType->question_type_id,
                'question_set_id' => $emptyTextSet->question_set_id,
                'content' => "Question {$i}",
                'difficulty' => 'MEDIUM',
                'status' => Question::STATUS_ACTIVE,
            ]);
            foreach (['A', 'B', 'C', 'D'] as $lbl) {
                AnswerOption::query()->create([
                    'question_id' => $q->question_id,
                    'label' => $lbl,
                    'content' => "Option {$lbl}",
                    'is_correct' => $lbl === 'A',
                ]);
            }
        }

        // Nhóm có hình ảnh nhưng stimulus_text trống -> Vẫn hợp lệ
        $mediaSet = QuestionSet::query()->create([
            'title' => 'Image Only Set',
            'stimulus_text' => '',
            'media_url' => 'images/passages/part7_sample.png',
            'media_type' => 'IMAGE',
        ]);

        for ($i = 1; $i <= 2; $i++) {
            $q = Question::query()->create([
                'section_id' => $this->part7Section->section_id,
                'question_type_id' => $this->mcType->question_type_id,
                'question_set_id' => $mediaSet->question_set_id,
                'content' => "Media Question {$i}",
                'difficulty' => 'MEDIUM',
                'status' => Question::STATUS_ACTIVE,
            ]);
            foreach (['A', 'B', 'C', 'D'] as $lbl) {
                AnswerOption::query()->create([
                    'question_id' => $q->question_id,
                    'label' => $lbl,
                    'content' => "Option {$lbl}",
                    'is_correct' => $lbl === 'A',
                ]);
            }
        }

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 7, 'count' => 10]));
        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions): bool {
            // Chỉ lấy mediaSet (2 câu), bỏ emptyTextSet
            return count($questions) === 2;
        });
    }

    public function test_part7_individual_question_check_blocked_on_part5_endpoint(): void
    {
        $set = $this->createQuestionSet('PART7', 3, 'Part 7 Set');
        $q = $set->questions()->first();

        // TH1: Phiên làm việc là Part 7 -> Chặn
        $this->withSession([
            'reading_practice_session' => [
                'part' => 7,
                'question_ids' => [$q->question_id],
                'groups' => [],
            ],
        ]);

        $response = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $q->question_id,
            'selected_option' => 'A',
        ]);
        $response->assertStatus(403);

        // TH2: Câu hỏi thuộc PART7 kể cả khi session không có part -> Chặn
        $this->withSession([
            'reading_practice_session' => [
                'part' => 5,
                'question_ids' => [$q->question_id],
                'groups' => [],
            ],
        ]);

        $response2 = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $q->question_id,
            'selected_option' => 'A',
        ]);
        $response2->assertStatus(403);
    }

    public function test_part7_group_check_api_rejects_when_not_all_questions_answered(): void
    {
        $set = $this->createQuestionSet('PART7', 4, 'Part 7 4-Question Set');
        $qids = $set->questions()->pluck('question_id')->all();

        $this->withSession([
            'reading_practice_session' => [
                'part' => 7,
                'target_count' => 4,
                'question_ids' => $qids,
                'groups' => [
                    $set->question_set_id => [
                        'question_set_id' => $set->question_set_id,
                        'question_ids' => $qids,
                        'completed' => false,
                        'answers' => [],
                    ],
                ],
            ],
        ]);

        // Chỉ gửi 3 / 4 đáp án -> Phải bị từ chối với 422
        $response = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set->question_set_id,
            'answers' => [
                $qids[0] => 'A',
                $qids[1] => 'B',
                $qids[2] => 'C',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_part7_group_check_api_grades_full_group_and_locks_in_session(): void
    {
        $set = $this->createQuestionSet('PART7', 5, 'Part 7 5-Question Passage');
        $questions = $set->questions()->get();
        $qids = $questions->pluck('question_id')->all();

        $this->withSession([
            'reading_practice_session' => [
                'part' => 7,
                'target_count' => 5,
                'question_ids' => $qids,
                'groups' => [
                    $set->question_set_id => [
                        'question_set_id' => $set->question_set_id,
                        'question_ids' => $qids,
                        'completed' => false,
                        'answers' => [],
                    ],
                ],
            ],
        ]);

        // Gửi đủ 5 câu: 3 câu đúng (A) và 2 câu sai (B)
        $response = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set->question_set_id,
            'answers' => [
                $qids[0] => 'A',
                $qids[1] => 'A',
                $qids[2] => 'A',
                $qids[3] => 'B',
                $qids[4] => 'B',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'question_set_id' => $set->question_set_id,
                'correct_count' => 3,
                'total_questions' => 5,
            ]);

        // Gửi lại lần 2 với cùng đáp án -> Trả về cached result
        $repeatSameResponse = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set->question_set_id,
            'answers' => [
                $qids[0] => 'A',
                $qids[1] => 'A',
                $qids[2] => 'A',
                $qids[3] => 'B',
                $qids[4] => 'B',
            ],
        ]);

        $repeatSameResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'already_completed' => true,
                'correct_count' => 3,
                'total_questions' => 5,
            ]);

        // Gửi lại lần 3 khi người dùng thay đổi đáp án -> Tính lại điểm theo lựa chọn mới (BE-007.7)
        $repeatNewResponse = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set->question_set_id,
            'answers' => [
                $qids[0] => 'B',
                $qids[1] => 'B',
                $qids[2] => 'B',
                $qids[3] => 'B',
                $qids[4] => 'B',
            ],
        ]);

        $repeatNewResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'correct_count' => 0,
                'total_questions' => 5,
            ]);
    }

    public function test_part7_submit_protects_locked_session_answers_against_input_tampering(): void
    {
        $set = $this->createQuestionSet('PART7', 3, 'Part 7 Tampering Test');
        $qids = $set->questions()->pluck('question_id')->all();

        // Nhóm đã hoàn thành trong session với đáp án đúng 'A'
        $sessionData = [
            'part' => 7,
            'target_count' => 3,
            'question_ids' => $qids,
            'groups' => [
                $set->question_set_id => [
                    'question_set_id' => $set->question_set_id,
                    'question_ids' => $qids,
                    'completed' => true,
                    'answers' => [
                        $qids[0] => 'A',
                        $qids[1] => 'A',
                        $qids[2] => 'A',
                    ],
                ],
            ],
        ];

        // Kẻ gian sửa hidden input thành 'B' (sai)
        $tamperedAnswers = [
            $qids[0] => 'B',
            $qids[1] => 'B',
            $qids[2] => 'B',
        ];

        $response = $this->withSession(['reading_practice_session' => $sessionData])
            ->post(route('luyen-tap.doc.nop-bai'), [
                'part' => 7,
                'question_ids' => implode(',', $qids),
                'total_count' => 3,
                'time_spent' => 90,
                'answers' => $tamperedAnswers,
            ]);

        $response->assertRedirect(route('luyen-tap.doc.ket-qua'));

        $result = session('reading_practice_result');
        $this->assertNotNull($result);
        $this->assertSame(7, $result['part']);
        $this->assertSame('Part 7 — Đọc hiểu văn bản', $result['part_name']);
        // Phải được tính là 3 câu đúng (lấy từ Session đã chốt), không bị ghi đè bởi 'B'
        $this->assertSame(3, $result['correct_count']);
        $this->assertSame(0, $result['incorrect_count']);
        $this->assertSame(100, $result['score_percent']);
    }

    public function test_part7_submit_grades_partially_completed_groups_correctly(): void
    {
        $set = $this->createQuestionSet('PART7', 4, 'Part 7 Partial Group');
        $qids = $set->questions()->pluck('question_id')->all();

        $sessionData = [
            'part' => 7,
            'target_count' => 4,
            'question_ids' => $qids,
            'groups' => [
                $set->question_set_id => [
                    'question_set_id' => $set->question_set_id,
                    'question_ids' => $qids,
                    'completed' => false,
                    'answers' => [],
                ],
            ],
        ];

        // Người dùng chỉ trả lời 2/4 câu: 1 câu đúng (A), 1 câu sai (B), 2 câu chưa làm
        $answers = [
            $qids[0] => 'A',
            $qids[1] => 'B',
        ];

        $response = $this->withSession(['reading_practice_session' => $sessionData])
            ->post(route('luyen-tap.doc.nop-bai'), [
                'part' => 7,
                'question_ids' => implode(',', $qids),
                'total_count' => 4,
                'time_spent' => 120,
                'answers' => $answers,
            ]);

        $response->assertRedirect(route('luyen-tap.doc.ket-qua'));

        $result = session('reading_practice_result');
        $this->assertNotNull($result);
        $this->assertSame(7, $result['part']);
        $this->assertSame(4, $result['total']);
        $this->assertSame(1, $result['correct_count']);
        $this->assertSame(1, $result['incorrect_count']);
        $this->assertSame(2, $result['unanswered_count']);
        $this->assertSame(25, $result['score_percent']); // 1 / 4 = 25%
    }

    public function test_part7_retry_wrong_keeps_entire_group_intact(): void
    {
        $setA = $this->createQuestionSet('PART7', 3, 'Set A with Wrong Question');
        $setB = $this->createQuestionSet('PART7', 2, 'Set B All Correct');

        $qidsA = $setA->questions()->pluck('question_id')->all();
        $qidsB = $setB->questions()->pluck('question_id')->all();

        // Giả lập kết quả làm bài trước đó: câu 1 của Set A bị sai
        $lastResult = [
            'part' => 7,
            'details' => [
                ['id' => $qidsA[0], 'status' => 'incorrect'],
                ['id' => $qidsA[1], 'status' => 'correct'],
                ['id' => $qidsA[2], 'status' => 'correct'],
                ['id' => $qidsB[0], 'status' => 'correct'],
                ['id' => $qidsB[1], 'status' => 'correct'],
            ],
        ];

        $response = $this->withSession(['reading_practice_result' => $lastResult])
            ->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 7, 'retry_wrong' => 1]));

        $response->assertStatus(200);

        $response->assertViewHas('questions', function (array $questions) use ($qidsA): bool {
            // Khi luyện lại câu sai, phải lấy NGUYÊN NHÓM chứa câu sai (3 câu của Set A), không tách lẻ 1 câu
            if (count($questions) !== 3) {
                return false;
            }
            $loadedIds = array_column($questions, 'id');
            sort($loadedIds);
            sort($qidsA);

            return $loadedIds === $qidsA;
        });
    }

    public function test_part5_with_zero_count_redirects_to_setup_with_error(): void
    {
        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 0]));
        $response->assertRedirect(url('/luyen-tap/doc?part=5&count=0'));
        $response->assertSessionHas('practice_error', 'Part 5 yêu cầu tối thiểu 1 câu hỏi. Vui lòng chọn lại số lượng câu.');
    }

    public function test_part6_with_invalid_count_redirects_to_setup_with_error(): void
    {
        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 6, 'count' => 3]));
        $response->assertRedirect(url('/luyen-tap/doc?part=6&count=3'));
        $response->assertSessionHas('practice_error', 'Part 6 yêu cầu tối thiểu 4 câu (1 đoạn văn). Vui lòng chọn lại số lượng câu.');
    }

    public function test_part7_with_one_count_redirects_to_setup_with_error(): void
    {
        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 7, 'count' => 1]));
        $response->assertRedirect(url('/luyen-tap/doc?part=7&count=1'));
        $response->assertSessionHas('practice_error', 'Part 7 yêu cầu tối thiểu 2 câu để tạo một nhóm bài đọc. Vui lòng chọn lại số lượng câu.');
    }

    public function test_part6_group_check_api_supports_reveal_action_before_answering(): void
    {
        $set = $this->createQuestionSet('PART6', 4, 'Part 6 Reveal Group');
        $qids = $set->questions()->pluck('question_id')->all();

        $sessionData = [
            'part' => 6,
            'target_count' => 4,
            'question_ids' => $qids,
            'groups' => [
                $set->question_set_id => [
                    'question_ids' => $qids,
                    'completed' => false,
                ],
            ],
        ];

        $response = $this->withSession(['reading_practice_session' => $sessionData])
            ->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
                'question_set_id' => $set->question_set_id,
                'action' => 'reveal',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'action' => 'reveal',
                'question_set_id' => $set->question_set_id,
                'total_questions' => 4,
            ]);

        $data = $response->json();
        $this->assertCount(4, $data['results']);
        $this->assertSame('A', $data['results'][0]['correct_answer']);
        $this->assertNull($data['results'][0]['user_answer']);
    }

    public function test_invalid_part_redirects_to_setup_with_error(): void
    {
        // 1. Part ngoài phạm vi 5, 6, 7
        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 8, 'count' => 10]));
        $response->assertRedirect(url('/luyen-tap/doc'));
        $response->assertSessionHas('practice_error', 'Phần luyện tập không hợp lệ. Vui lòng chọn Part 5, Part 6 hoặc Part 7.');

        // 2. Part là chuỗi chữ
        $responseStr = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 'abc', 'count' => 10]));
        $responseStr->assertRedirect(url('/luyen-tap/doc'));
        $responseStr->assertSessionHas('practice_error', 'Phần luyện tập không hợp lệ. Vui lòng chọn Part 5, Part 6 hoặc Part 7.');

        // 3. Thiếu param part
        $responseMissing = $this->get(route('luyen-tap.doc.hoan-thanh-cau'));
        $responseMissing->assertRedirect(url('/luyen-tap/doc'));
        $responseMissing->assertSessionHas('practice_error', 'Phần luyện tập không hợp lệ. Vui lòng chọn Part 5, Part 6 hoặc Part 7.');
    }

    public function test_invalid_count_format_or_exceeding_limit_redirects_with_error(): void
    {
        // 1. Số thập phân
        $respDecimal = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => '3.5']));
        $respDecimal->assertRedirect(url('/luyen-tap/doc?part=5&count=3.5'));
        $respDecimal->assertSessionHas('practice_error', 'Số lượng câu hỏi không hợp lệ. Vui lòng nhập một số nguyên dương.');

        // 2. Số âm
        $respNegative = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => '-5']));
        $respNegative->assertRedirect(url('/luyen-tap/doc?part=5&count=-5'));
        $respNegative->assertSessionHas('practice_error', 'Số lượng câu hỏi không hợp lệ. Vui lòng nhập một số nguyên dương.');

        // 3. Chuỗi chữ
        $respText = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 'abc']));
        $respText->assertRedirect(url('/luyen-tap/doc?part=5&count=abc'));
        $respText->assertSessionHas('practice_error', 'Số lượng câu hỏi không hợp lệ. Vui lòng nhập một số nguyên dương.');

        // 4. Vượt quá giới hạn 100 câu của hệ thống
        $respOver = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 101]));
        $respOver->assertRedirect(url('/luyen-tap/doc?part=5&count=101'));
        $respOver->assertSessionHas('practice_error', 'Số lượng câu hỏi không được vượt quá giới hạn 100 câu của hệ thống.');
    }

    public function test_session_contains_standardized_data_ready_for_practice_attempt_service(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->createPart5Question("Part 5 Active Question {$i}", 'ACTIVE');
        }

        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 5]));
        $response->assertStatus(200);

        $sessionData = session('reading_practice_session');
        $this->assertNotNull($sessionData);
        $this->assertSame(5, $sessionData['part']);
        $this->assertSame(5, $sessionData['target_count']);
        $this->assertSame(5, $sessionData['actual_count']);
        $this->assertNotEmpty($sessionData['started_at']);
        $this->assertCount(5, $sessionData['question_ids']);
        $this->assertCount(5, $sessionData['sequence_map']);

        // Kiểm tra sequence_no tăng tuần tự từ 1 đến actual_count
        $seqValues = array_values($sessionData['sequence_map']);
        $this->assertSame([1, 2, 3, 4, 5], $seqValues);
    }

    public function test_part6_notice_when_bank_has_fewer_than_requested_groups(): void
    {
        // Tạo 4 nhóm Part 6 (tổng 16 câu)
        for ($i = 1; $i <= 4; $i++) {
            $this->createQuestionSet('PART6', 4, "Part 6 Set {$i}");
        }

        // Yêu cầu 20 câu (5 nhóm) nhưng ngân hàng chỉ có 16 câu (4 nhóm)
        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 6, 'count' => 20]));
        $response->assertStatus(200);
        $response->assertViewHas('questions', fn ($q) => count($q) === 16);
        $response->assertViewHas('noticeMessage', function (?string $msg): bool {
            return $msg === 'Bạn đã chọn 20 câu. Ngân hàng hiện chỉ có 16 câu Part 6 hợp lệ (gồm 4 đoạn văn).';
        });
    }

    public function test_part7_notice_distinguishes_bank_insufficiency_vs_group_structure(): void
    {
        // 1. Trường hợp ngân hàng thiếu dữ liệu (tổng ngân hàng có 5 câu, yêu cầu 10 câu)
        $this->createQuestionSet('PART7', 3, 'Part 7 Short Bank Set 1');
        $this->createQuestionSet('PART7', 2, 'Part 7 Short Bank Set 2');

        $responseInsufficient = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 7, 'count' => 10]));
        $responseInsufficient->assertStatus(200);
        $responseInsufficient->assertViewHas('questions', fn ($q) => count($q) === 5);
        $responseInsufficient->assertViewHas('noticeMessage', function (?string $msg): bool {
            return str_contains($msg ?? '', 'Ngân hàng hiện chỉ có 5 câu Part 7 hợp lệ');
        });
    }

    public function test_be0077_page_reload_restores_identical_session_questions_and_states(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->createPart5Question("Part 5 Reload Question {$i}", 'ACTIVE');
        }

        $initResp = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 5]));
        $initResp->assertStatus(200);

        $sessionData = session('reading_practice_session');
        $this->assertNotNull($sessionData);
        $originalQids = $sessionData['question_ids'];
        $firstQid = $originalQids[0];

        // Trả lời câu đầu tiên và gắn cờ
        $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $firstQid,
            'selected_option' => 'A',
            'action' => 'answer',
        ]);

        $sessionData = session('reading_practice_session');
        $sessionData['flagged_questions'] = [$firstQid];
        session(['reading_practice_session' => $sessionData]);

        // F5 / Tải lại trang với cùng part=5 và count=5 (không kèm &new=1)
        $reloadResp = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 5]));
        $reloadResp->assertStatus(200);
        $reloadResp->assertViewHas('questions', function ($questions) use ($originalQids) {
            $currentIds = collect($questions)->pluck('id')->all();

            return $currentIds === $originalQids;
        });
        $reloadResp->assertViewHas('savedAnswers', function ($answers) use ($firstQid) {
            return ($answers[$firstQid] ?? null) === 'A';
        });
        $reloadResp->assertViewHas('savedFlags', function ($flags) use ($firstQid) {
            return in_array($firstQid, $flags, true);
        });
    }

    public function test_be0077_part5_changing_answers_updates_session_and_recalculates_is_correct(): void
    {
        $q = $this->createPart5Question('Part 5 Changing Answer Question', 'ACTIVE');
        $qid = $q->question_id;

        $sessionData = [
            'part' => 5,
            'target_count' => 1,
            'actual_count' => 1,
            'question_ids' => [$qid],
            'answers' => [],
            'question_states' => [],
        ];
        session(['reading_practice_session' => $sessionData]);

        // Chọn đáp án B (đáp án đúng)
        $resp1 = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $qid,
            'selected_option' => 'B',
            'action' => 'answer',
        ]);
        $resp1->assertStatus(200)->assertJson(['is_correct' => true, 'user_answer' => 'B']);

        $sessAfter1 = session('reading_practice_session');
        $this->assertSame('B', $sessAfter1['answers'][$qid]);
        $this->assertTrue($sessAfter1['question_states'][$qid]['isCorrect']);
        $this->assertTrue($sessAfter1['question_states'][$qid]['answered']);

        // Đổi sang đáp án C (đáp án sai)
        $resp2 = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $qid,
            'selected_option' => 'C',
            'action' => 'answer',
        ]);
        $resp2->assertStatus(200)->assertJson(['is_correct' => false, 'user_answer' => 'C']);

        $sessAfter2 = session('reading_practice_session');
        $this->assertSame('C', $sessAfter2['answers'][$qid]);
        $this->assertFalse($sessAfter2['question_states'][$qid]['isCorrect']);
        $this->assertTrue($sessAfter2['question_states'][$qid]['answered']);
    }

    public function test_be0077_part5_reveal_action_does_not_mark_question_as_answered(): void
    {
        $q = $this->createPart5Question('Part 5 Reveal Explanation Question', 'ACTIVE');
        $qid = $q->question_id;

        $sessionData = [
            'part' => 5,
            'target_count' => 1,
            'actual_count' => 1,
            'question_ids' => [$qid],
            'answers' => [],
            'question_states' => [],
        ];
        session(['reading_practice_session' => $sessionData]);

        // Nhấn xem giải thích trước khi chọn đáp án
        $resp = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $qid,
            'selected_option' => null,
            'action' => 'reveal',
        ]);

        $resp->assertStatus(200)
            ->assertJson([
                'success' => true,
                'action' => 'reveal',
                'correct_answer' => 'B',
            ]);

        $sess = session('reading_practice_session');
        $this->assertArrayNotHasKey($qid, $sess['answers']);
        $this->assertTrue($sess['question_states'][$qid]['revealed']);
        $this->assertFalse($sess['question_states'][$qid]['answered']);
        $this->assertNull($sess['question_states'][$qid]['userPick']);
    }

    public function test_be0077_part6_part7_save_progress_saves_partial_answers_without_grading(): void
    {
        $set = $this->createQuestionSet('PART6', 4, 'Part 6 Save Progress');
        $qids = $set->questions()->pluck('question_id')->all();

        $sessionData = [
            'part' => 6,
            'target_count' => 4,
            'actual_count' => 4,
            'question_ids' => $qids,
            'answers' => [],
            'groups' => [
                $set->question_set_id => [
                    'question_set_id' => $set->question_set_id,
                    'question_ids' => $qids,
                    'completed' => false,
                    'answers' => [],
                ],
            ],
        ];
        session(['reading_practice_session' => $sessionData]);

        // Người dùng mới chọn 2/4 câu
        $resp = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set->question_set_id,
            'action' => 'save_progress',
            'answers' => [
                $qids[0] => 'A',
                $qids[1] => 'C',
            ],
        ]);

        $resp->assertStatus(200)
            ->assertJson([
                'success' => true,
                'saved' => true,
                'question_set_id' => $set->question_set_id,
                'answers' => [
                    $qids[0] => 'A',
                    $qids[1] => 'C',
                ],
            ]);

        $sess = session('reading_practice_session');
        $this->assertSame('A', $sess['answers'][$qids[0]]);
        $this->assertSame('C', $sess['answers'][$qids[1]]);
        $this->assertArrayNotHasKey($qids[2], $sess['answers']);
        $this->assertFalse($sess['groups'][$set->question_set_id]['completed']);
    }

    public function test_be0077_exit_clears_practice_session(): void
    {
        session([
            'reading_practice_session' => [
                'part' => 5,
                'target_count' => 10,
                'question_ids' => [1, 2, 3],
            ],
        ]);

        $resp = $this->get('/luyen-tap/doc?exit=1');
        $resp->assertStatus(200);
        $this->assertNull(session('reading_practice_session'));
    }

    public function test_be0077_backend_request_versioning_prevents_stale_answer_overwrite(): void
    {
        $q = $this->createPart5Question('Part 5 Backend Versioning Test', 'ACTIVE');
        $qid = $q->question_id;

        $sessionData = [
            'part' => 5,
            'target_count' => 1,
            'actual_count' => 1,
            'question_ids' => [$qid],
            'answers' => [],
            'question_states' => [],
            'question_versions' => [],
        ];
        session(['reading_practice_session' => $sessionData]);

        // Request 1: Version 2 (đáp án B, đến đích trước)
        $respNew = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $qid,
            'selected_option' => 'B',
            'action' => 'answer',
            'version' => 2,
        ]);
        $respNew->assertStatus(200)->assertJson(['is_correct' => true, 'user_answer' => 'B']);

        $sess = session('reading_practice_session');
        $this->assertSame('B', $sess['answers'][$qid]);
        $this->assertSame(2, $sess['question_versions'][$qid]);

        // Request 2: Version 1 (đáp án C, do độ trễ mạng đến sau)
        $respOld = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $qid,
            'selected_option' => 'C',
            'action' => 'answer',
            'version' => 1,
        ]);
        $respOld->assertStatus(200)->assertJson(['stale' => true]);

        // Đáp án trong Session không bị ghi đè bởi request cũ
        $sessAfter = session('reading_practice_session');
        $this->assertSame('B', $sessAfter['answers'][$qid]);
        $this->assertSame(2, $sessAfter['question_versions'][$qid]);
    }

    public function test_be0077_backend_save_progress_versioning_prevents_stale_group_overwrite(): void
    {
        $set = $this->createQuestionSet('PART6', 4, 'Part 6 Versioning Test');
        $qids = $set->questions()->pluck('question_id')->all();

        $sessionData = [
            'part' => 6,
            'target_count' => 4,
            'actual_count' => 4,
            'question_ids' => $qids,
            'answers' => [],
            'groups' => [
                $set->question_set_id => [
                    'question_set_id' => $set->question_set_id,
                    'question_ids' => $qids,
                    'completed' => false,
                    'answers' => [],
                ],
            ],
            'question_versions' => [],
        ];
        session(['reading_practice_session' => $sessionData]);

        // Version 2 cho câu qids[0] là 'A'
        $respNew = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set->question_set_id,
            'action' => 'save_progress',
            'question_id' => $qids[0],
            'version' => 2,
            'answers' => [$qids[0] => 'A'],
        ]);
        $respNew->assertStatus(200)->assertJson(['saved' => true]);

        // Request cũ Version 1 cho câu qids[0] là 'B' đến sau
        $respOld = $this->postJson(route('luyen-tap.doc.kiem-tra-nhom'), [
            'question_set_id' => $set->question_set_id,
            'action' => 'save_progress',
            'question_id' => $qids[0],
            'version' => 1,
            'answers' => [$qids[0] => 'B'],
        ]);
        $respOld->assertStatus(200)->assertJson(['stale' => true, 'saved' => false]);

        $sess = session('reading_practice_session');
        $this->assertSame('A', $sess['answers'][$qids[0]]);
    }

    public function test_be0077_expired_session_returns_403_in_check_answer(): void
    {
        $q = $this->createPart5Question('Part 5 Expired Session Test', 'ACTIVE');

        // Không có session
        session()->forget('reading_practice_session');

        $resp = $this->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $q->question_id,
            'selected_option' => 'A',
            'action' => 'answer',
        ]);

        $resp->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Lượt luyện tập không tồn tại hoặc đã hết hạn.',
            ]);
    }

    public function test_be0077_user_mismatch_returns_403(): void
    {
        $user1 = User::query()->create([
            'user_name' => 'student1',
            'full_name' => 'Student One',
            'email' => 'student1@studyprep.test',
            'password_hash' => bcrypt('secret'),
            'role' => 'student',
        ]);

        $user2 = User::query()->create([
            'user_name' => 'student2',
            'full_name' => 'Student Two',
            'email' => 'student2@studyprep.test',
            'password_hash' => bcrypt('secret'),
            'role' => 'student',
        ]);

        $q = $this->createPart5Question('Part 5 User Mismatch Test', 'ACTIVE');

        // Phiên thuộc về user 1
        $sessionData = [
            'user_id' => $user1->user_id,
            'part' => 5,
            'target_count' => 1,
            'actual_count' => 1,
            'question_ids' => [$q->question_id],
            'answers' => [],
            'question_states' => [],
        ];
        session(['reading_practice_session' => $sessionData]);

        // Đăng nhập tài khoản user 2 gửi request
        $resp = $this->actingAs($user2)->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
            'question_id' => $q->question_id,
            'selected_option' => 'A',
            'action' => 'answer',
        ]);

        $resp->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Lượt luyện tập không thuộc về tài khoản hiện tại.',
            ]);
    }
}
