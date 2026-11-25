<?php

namespace Tests\Feature;

use App\Models\AnswerOption;
use App\Models\ExamSection;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\QuestionType;
use App\Models\Skill;
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

        // Case 5: Request 3 questions (< 4) -> should not form a group
        $responseSmall = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 6, 'count' => 3]));
        $responseSmall->assertStatus(200);
        $responseSmall->assertViewHas('questions', fn ($q) => count($q) === 0);
        $responseSmall->assertViewHas('noticeMessage', fn ($msg) => str_contains((string) $msg, 'ít nhất 4 câu') || str_contains((string) $msg, 'tối thiểu từ 4 câu'));
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
        // Empty database for Part 5
        $response = $this->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $response->assertStatus(200);
        $response->assertViewHas('questions', fn ($q) => count($q) === 0);
        $response->assertViewHas('noticeMessage', fn ($msg) => $msg !== null && str_contains($msg, 'ACTIVE'));
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
}
