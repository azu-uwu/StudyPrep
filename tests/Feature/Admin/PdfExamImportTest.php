<?php

namespace Tests\Feature\Admin;

use App\Models\ExamSection;
use App\Models\PdfImportSession;
use App\Models\PdfImportStagingQuestion;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Skill;
use App\Models\TestBlueprint;
use App\Models\TestForm;
use App\Models\User;
use App\Services\DocxToStandardXmlService;
use App\Services\PdfImport\Contracts\PdfLayoutExtractorInterface;
use App\Services\PdfImport\Parsers\PdfReadingQuestionParser;
use App\Services\PdfImport\PdfExamImportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class PdfExamImportTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createStudyPrepSchema();
    }

    private function seedToeicSections(): void
    {
        $lc = Skill::firstOrCreate(['code' => 'LC'], ['skill_id' => 1, 'skill_name' => 'Listening']);
        $rc = Skill::firstOrCreate(['code' => 'RC'], ['skill_id' => 2, 'skill_name' => 'Reading']);

        foreach ([
            ['section_id' => 1, 'skill_id' => $lc->skill_id, 'code' => 'PART1', 'name' => 'Photographs', 'sequence_no' => 1],
            ['section_id' => 2, 'skill_id' => $lc->skill_id, 'code' => 'PART2', 'name' => 'Question-Response', 'sequence_no' => 2],
            ['section_id' => 3, 'skill_id' => $lc->skill_id, 'code' => 'PART3', 'name' => 'Conversations', 'sequence_no' => 3],
            ['section_id' => 4, 'skill_id' => $lc->skill_id, 'code' => 'PART4', 'name' => 'Talks', 'sequence_no' => 4],
            ['section_id' => 5, 'skill_id' => $rc->skill_id, 'code' => 'PART5', 'name' => 'Incomplete Sentences', 'sequence_no' => 5],
            ['section_id' => 6, 'skill_id' => $rc->skill_id, 'code' => 'PART6', 'name' => 'Text Completion', 'sequence_no' => 6],
            ['section_id' => 7, 'skill_id' => $rc->skill_id, 'code' => 'PART7', 'name' => 'Reading Comprehension', 'sequence_no' => 7],
        ] as $sec) {
            ExamSection::firstOrCreate(['code' => $sec['code']], $sec);
        }

        QuestionType::firstOrCreate(['question_type_id' => 1], ['name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);
    }

    public function test_full_200_lr_pdf_with_answer_script_maps_parts_accurately_and_commits_to_db(): void
    {
        $this->seedToeicSections();
        $admin = User::factory()->admin()->create();

        // Build synthetic 200-question layout (Listening 1-100, Reading 101-200)
        $listeningLines = [];
        $listeningImages = [];
        for ($i = 1; $i <= 6; $i++) {
            $y = 100.0 * $i;
            $listeningImages[] = [
                'file_name' => "p1_img{$i}.png",
                'relative_path' => "storage/staging/fake/p1_img{$i}.png",
                'page_no' => 1,
                'width' => 300,
                'height' => 200,
                'x0' => 40.0,
                'y0' => $y - 20.0,
                'x1' => 240.0,
                'y1' => $y + 10.0,
            ];
            $listeningLines[] = [
                'x0' => 40.0,
                'y0' => $y,
                'x1' => 400.0,
                'y1' => $y + 30.0,
                'column' => 'single',
                'text' => "{$i}.\n(A) Option A\n(B) Option B\n(C) Option C\n(D) Option D",
            ];
        }
        for ($i = 7; $i <= 31; $i++) {
            $listeningLines[] = [
                'x0' => 40.0,
                'y0' => 700.0 + ($i * 10),
                'x1' => 400.0,
                'y1' => 708.0 + ($i * 10),
                'column' => 'single',
                'text' => "{$i}. Listen and choose.\n(A) Yes\n(B) No\n(C) Tomorrow",
            ];
        }
        for ($start = 32; $start <= 98; $start += 3) {
            $end = $start + 2;
            $chunk = "Questions {$start}-{$end} refer to the following audio.\n";
            for ($q = $start; $q <= $end; $q++) {
                $chunk .= "{$q}. Listening question {$q}?\n(A) A{$q}\n(B) B{$q}\n(C) C{$q}\n(D) D{$q}\n";
            }
            $listeningLines[] = [
                'x0' => 40.0,
                'y0' => 1100.0 + $start,
                'x1' => 400.0,
                'y1' => 1120.0 + $start,
                'column' => 'single',
                'text' => $chunk,
            ];
        }

        $readingBlocks = [];
        for ($i = 101; $i <= 130; $i++) {
            $readingBlocks[] = [
                'x0' => 40.0,
                'y0' => 50.0 + ($i - 100) * 15,
                'x1' => 260.0,
                'y1' => 62.0 + ($i - 100) * 15,
                'column' => 'left',
                'text' => "{$i}. The manager ------- the report.\n(A) sign (B) signed (C) signing (D) signature",
            ];
        }
        for ($start = 131; $start <= 143; $start += 4) {
            $end = $start + 3;
            $chunk = "Questions {$start}-{$end} refer to the following memo.\nPlease review the updated policy.\n";
            for ($q = $start; $q <= $end; $q++) {
                $chunk .= "{$q}.\n(A) A{$q}\n(B) B{$q}\n(C) C{$q}\n(D) D{$q}\n";
            }
            $readingBlocks[] = [
                'x0' => 40.0,
                'y0' => 600.0 + $start,
                'x1' => 500.0,
                'y1' => 650.0 + $start,
                'column' => 'single',
                'text' => $chunk,
            ];
        }
        for ($start = 147; $start <= 198; $start += 3) {
            $end = $start + 2;
            $chunk = "Questions {$start}-{$end} refer to the following notice.\nNotice content for {$start}-{$end}.\n";
            for ($q = $start; $q <= $end; $q++) {
                $chunk .= "{$q}. Reading question {$q}?\n(A) A{$q}\n(B) B{$q}\n(C) C{$q}\n(D) D{$q}\n";
            }
            $readingBlocks[] = [
                'x0' => 40.0,
                'y0' => 900.0 + $start,
                'x1' => 500.0,
                'y1' => 950.0 + $start,
                'column' => 'single',
                'text' => $chunk,
            ];
        }

        $answerBlocks = [];
        for ($i = 1; $i <= 200; $i++) {
            $ans = $i <= 31 && $i >= 7 ? 'C' : 'B';
            $answerBlocks[] = [
                'x0' => 40.0,
                'y0' => (float) $i * 5,
                'x1' => 400.0,
                'y1' => (float) $i * 5 + 4,
                'column' => 'single',
                'text' => "{$i}. ({$ans}) Explanation for question {$i}.",
            ];
        }

        $this->app->instance(PdfLayoutExtractorInterface::class, new class($listeningLines, $listeningImages, $readingBlocks, $answerBlocks) implements PdfLayoutExtractorInterface
        {
            public function __construct(
                private array $listeningLines,
                private array $listeningImages,
                private array $readingBlocks,
                private array $answerBlocks
            ) {}

            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                if (str_contains($pdfAbsolutePath, 'answers.pdf')) {
                    return [
                        'page_count' => 1,
                        'pages' => [[
                            'page_no' => 1,
                            'width' => 595.0,
                            'height' => 842.0,
                            'is_two_column' => false,
                            'blocks' => $this->answerBlocks,
                            'tables' => [],
                            'images' => [],
                        ]],
                    ];
                }

                return [
                    'page_count' => 2,
                    'pages' => [
                        [
                            'page_no' => 1,
                            'width' => 595.0,
                            'height' => 842.0,
                            'is_two_column' => false,
                            'blocks' => $this->listeningLines,
                            'tables' => [],
                            'images' => $this->listeningImages,
                        ],
                        [
                            'page_no' => 2,
                            'width' => 595.0,
                            'height' => 842.0,
                            'is_two_column' => true,
                            'blocks' => $this->readingBlocks,
                            'tables' => [],
                            'images' => [],
                        ],
                    ],
                ];
            }
        });

        $qPdf = UploadedFile::fake()->create('toeic_full_questions.pdf', 100, 'application/pdf');
        $aPdf = UploadedFile::fake()->create('toeic_full_answers.pdf', 50, 'application/pdf');

        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => $qPdf,
            'answer_pdf' => $aPdf,
            'import_scope' => 'FULL_LR',
            'publish_status' => 'live',
        ]);

        $resp->assertRedirect();
        $batchId = basename((string) $resp->headers->get('Location'));

        $session = PdfImportSession::query()->find($batchId);
        $this->assertNotNull($session);
        $this->assertSame(PdfImportSession::STATUS_READY_FOR_REVIEW, $session->status);
        $this->assertSame(200, $session->summary_json['total_questions']);
        $this->assertSame(200, $session->summary_json['valid_questions']);
        $this->assertSame(0, $session->summary_json['error_questions']);
        $this->assertSame([
            'PART1' => 6,
            'PART2' => 25,
            'PART3' => 39,
            'PART4' => 30,
            'PART5' => 30,
            'PART6' => 16,
            'PART7' => 54,
        ], $session->summary_json['part_counts']);

        $this->assertSame(200, PdfImportStagingQuestion::query()->where('import_session_id', $batchId)->count());

        // Commit atomically via existing Staging Review commit route
        $commitResp = $this->actingAs($admin)->post(route('admin.questions.import.commit', ['batchId' => $batchId]), [
            'publish_status' => 'ACTIVE',
            'auto_generate_tts' => '0',
        ]);
        $commitResp->assertRedirect(route('admin.questions.index'));
        $this->assertSame(200, Question::query()->count());
        $this->assertSame(PdfImportSession::STATUS_COMMITTED, $session->fresh()->status);
    }

    public function test_single_pdf_without_answer_file_marks_missing_questions_and_needs_review(): void
    {
        $this->seedToeicSections();
        $admin = User::factory()->admin()->create();

        $this->app->instance(PdfLayoutExtractorInterface::class, new class implements PdfLayoutExtractorInterface
        {
            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                return [
                    'page_count' => 1,
                    'pages' => [[
                        'page_no' => 1,
                        'width' => 595.0,
                        'height' => 842.0,
                        'is_two_column' => false,
                        'blocks' => [[
                            'x0' => 40.0,
                            'y0' => 50.0,
                            'x1' => 500.0,
                            'y1' => 200.0,
                            'column' => 'single',
                            'text' => "101. The CEO ------- tomorrow.\n(A) arrive\n(B) arrives\n(C) arriving\n(D) arrival",
                        ]],
                        'tables' => [],
                        'images' => [],
                    ]],
                ];
            }
        });

        $qPdf = UploadedFile::fake()->create('reading_partial.pdf', 50, 'application/pdf');
        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => $qPdf,
            'import_scope' => 'READING',
            'publish_status' => 'draft',
        ]);

        $resp->assertRedirect();
        $batchId = basename((string) $resp->headers->get('Location'));
        $session = PdfImportSession::query()->findOrFail($batchId);

        $this->assertSame(1, $session->summary_json['total_questions']);
        $this->assertSame(99, $session->summary_json['missing_count']);
        $this->assertSame(1, $session->summary_json['needs_review_questions']);
        $this->assertNotEmpty($session->validation_errors_json);

        $q101 = PdfImportStagingQuestion::query()->where('import_session_id', $batchId)->firstOrFail();
        $this->assertSame(PdfImportStagingQuestion::VALIDATION_NEEDS_REVIEW, $q101->validation_status);
        $this->assertNull($q101->correct_answer);
    }

    public function test_answer_mismatch_and_two_column_table_layout_are_detected_without_off_by_one(): void
    {
        $this->seedToeicSections();
        $admin = User::factory()->admin()->create();

        $this->app->instance(PdfLayoutExtractorInterface::class, new class implements PdfLayoutExtractorInterface
        {
            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                if (str_contains($pdfAbsolutePath, 'answers.pdf')) {
                    return [
                        'page_count' => 1,
                        'pages' => [[
                            'page_no' => 1,
                            'width' => 595.0,
                            'height' => 842.0,
                            'is_two_column' => false,
                            'blocks' => [[
                                'x0' => 40.0,
                                'y0' => 40.0,
                                'x1' => 500.0,
                                'y1' => 100.0,
                                'column' => 'single',
                                'text' => '102. B  147. D  148. C  199. A',
                            ]],
                            'tables' => [],
                            'images' => [],
                        ]],
                    ];
                }

                // Reading 2-column page + table in Part 7
                return [
                    'page_count' => 1,
                    'pages' => [[
                        'page_no' => 1,
                        'width' => 595.0,
                        'height' => 842.0,
                        'is_two_column' => true,
                        'blocks' => [
                            [
                                'x0' => 40.0,
                                'y0' => 60.0,
                                'x1' => 270.0,
                                'y1' => 160.0,
                                'column' => 'left',
                                'text' => "101. Left col Q101.\n(A) A1 (B) B1 (C) C1 (D) D1\n102. Left col Q102.\n(A) A2 (B) B2 (C) C2 (D) D2",
                            ],
                            [
                                'x0' => 310.0,
                                'y0' => 60.0,
                                'x1' => 550.0,
                                'y1' => 260.0,
                                'column' => 'right',
                                'text' => "Questions 147-148 refer to the following schedule.\nAnnual Conference Schedule\n147. When is the keynote?\n(A) 9 AM (B) 10 AM (C) 11 AM (D) 1 PM\n148. Where is lunch served?\n(A) Hall A (B) Hall B (C) Hall C (D) Hall D",
                            ],
                        ],
                        'tables' => [[
                            'table_index' => 0,
                            'x0' => 310.0,
                            'y0' => 110.0,
                            'x1' => 540.0,
                            'y1' => 150.0,
                            'rows' => ['Time | Event', '09:00 | Registration'],
                            'text' => "Time | Event\n09:00 | Registration",
                        ]],
                        'images' => [],
                    ]],
                ];
            }
        });

        $qPdf = UploadedFile::fake()->create('reading_2col.pdf', 50, 'application/pdf');
        $aPdf = UploadedFile::fake()->create('reading_2col_ans.pdf', 20, 'application/pdf');

        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => $qPdf,
            'answer_pdf' => $aPdf,
            'import_scope' => 'READING',
        ]);
        $resp->assertRedirect();
        $batchId = basename((string) $resp->headers->get('Location'));
        $session = PdfImportSession::query()->findOrFail($batchId);

        // 199 is orphan answer; 101 has no answer in answer PDF -> ERROR
        $this->assertContains(199, $session->summary_json['orphan_answer_numbers']);
        $this->assertSame(1, $session->summary_json['error_questions']);

        $q101 = PdfImportStagingQuestion::query()->where('import_session_id', $batchId)->where('question_number', 101)->firstOrFail();
        $q102 = PdfImportStagingQuestion::query()->where('import_session_id', $batchId)->where('question_number', 102)->firstOrFail();
        $q147 = PdfImportStagingQuestion::query()->where('import_session_id', $batchId)->where('question_number', 147)->firstOrFail();

        $this->assertSame(PdfImportStagingQuestion::VALIDATION_ERROR, $q101->validation_status);
        $this->assertSame('B', $q102->correct_answer);
        $this->assertSame('D', $q147->correct_answer);
        $this->assertStringContainsString('09:00 | Registration', (string) $q147->stimulus_text);
    }

    public function test_reading_two_column_pdf_with_part5_blanks_part6_groups_and_part7_tables_parses_all_100_questions(): void
    {
        $this->seedToeicSections();
        $admin = User::factory()->admin()->create();

        $leftBlocks = [];
        $rightBlocks = [];

        // Part 5 (101-115 left col, 116-130 right col) with ------- blanks & wrapped C/D options
        for ($i = 101; $i <= 115; $i++) {
            $leftBlocks[] = [
                'x0' => 40.0,
                'y0' => (float) (($i - 100) * 35),
                'x1' => 260.0,
                'y1' => (float) (($i - 100) * 35 + 30),
                'column' => 'left',
                'text' => "{$i}. The committee ------- the budget proposal\nyesterday afternoon.\n(A) approve (B) approved\n(C) approving with\nconfidence (D) approval",
            ];
        }
        for ($i = 116; $i <= 130; $i++) {
            $rightBlocks[] = [
                'x0' => 310.0,
                'y0' => (float) (($i - 115) * 35),
                'x1' => 550.0,
                'y1' => (float) (($i - 115) * 35 + 30),
                'column' => 'right',
                'text' => "{$i}. Please submit your travel receipts\n_____ Friday.\nA. before\nB. during\nC. among\nD. between",
            ];
        }

        // Part 6 (131-146: 4 passages x 4 blanks, bare numbers going straight to options)
        for ($start = 131; $start <= 143; $start += 4) {
            $end = $start + 3;
            $chunk = "Questions {$start}-{$end} refer to the following e-mail.\nTo: All Staff\nFrom: HR\nSubject: Policy Update\nPlease read carefully.\n";
            for ($q = $start; $q <= $end; $q++) {
                $chunk .= "{$q}.\n(A) OptA{$q} (B) OptB{$q}\n(C) OptC{$q} (D) OptD{$q}\n";
            }
            $leftBlocks[] = [
                'x0' => 40.0,
                'y0' => 650.0 + $start,
                'x1' => 260.0,
                'y1' => 690.0 + $start,
                'column' => 'left',
                'text' => $chunk,
            ];
        }

        // Part 7 (147-200: 18 passages x 3 questions = 54 questions)
        for ($start = 147; $start <= 198; $start += 3) {
            $end = $start + 2;
            $chunk = "Questions {$start}-{$end} refer to the following notice.\nFrom: Management\nSubject: Notice {$start}\n";
            for ($q = $start; $q <= $end; $q++) {
                $chunk .= "{$q}. What is announced in notice {$start}?\n(A) A{$q}\n(B) B{$q}\n(C) C{$q}\n(D) D{$q}\n";
            }
            $rightBlocks[] = [
                'x0' => 310.0,
                'y0' => 650.0 + $start,
                'x1' => 550.0,
                'y1' => 690.0 + $start,
                'column' => 'right',
                'text' => $chunk,
            ];
        }

        $ansLines = [];
        for ($i = 101; $i <= 200; $i++) {
            $ansLines[] = "{$i}. (B) Giải thích tiếng Việt câu {$i} không được ghi đè options.";
        }

        $this->app->instance(PdfLayoutExtractorInterface::class, new class($leftBlocks, $rightBlocks, $ansLines) implements PdfLayoutExtractorInterface
        {
            public function __construct(private array $leftBlocks, private array $rightBlocks, private array $ansLines) {}

            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                if (str_contains($pdfAbsolutePath, 'answers.pdf')) {
                    return [
                        'page_count' => 1,
                        'pages' => [[
                            'page_no' => 1,
                            'width' => 595.0,
                            'height' => 842.0,
                            'is_two_column' => false,
                            'blocks' => [[
                                'x0' => 40.0,
                                'y0' => 40.0,
                                'x1' => 550.0,
                                'y1' => 800.0,
                                'column' => 'single',
                                'text' => implode("\n", $this->ansLines),
                            ]],
                            'tables' => [],
                            'images' => [],
                        ]],
                    ];
                }

                // Intentionally interleave right before left to verify buildOrderedTimeline sorts left before right
                return [
                    'page_count' => 1,
                    'pages' => [[
                        'page_no' => 1,
                        'width' => 595.0,
                        'height' => 842.0,
                        'is_two_column' => true,
                        'blocks' => array_merge($this->rightBlocks, $this->leftBlocks),
                        'tables' => [],
                        'images' => [],
                    ]],
                ];
            }
        });

        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => UploadedFile::fake()->create('de1_2.pdf', 100, 'application/pdf'),
            'answer_pdf' => UploadedFile::fake()->create('RC_1.pdf', 50, 'application/pdf'),
            'import_scope' => 'READING',
        ]);
        $resp->assertRedirect();
        $batchId = basename((string) $resp->headers->get('Location'));
        $session = PdfImportSession::query()->findOrFail($batchId);

        $this->assertSame([], $session->summary_json['missing_numbers']);
        $this->assertSame(100, $session->summary_json['total_questions']);
        $this->assertSame(100, $session->summary_json['valid_questions']);
        $this->assertSame(0, $session->summary_json['error_questions']);
        $this->assertSame(30, $session->summary_json['part_counts']['PART5']);
        $this->assertSame(16, $session->summary_json['part_counts']['PART6']);
        $this->assertSame(54, $session->summary_json['part_counts']['PART7']);

        $q101 = PdfImportStagingQuestion::query()->where('import_session_id', $batchId)->where('question_number', 101)->firstOrFail();
        $this->assertSame('approving with confidence', $q101->options_json['C']);
        $this->assertSame('approval', $q101->options_json['D']);

        $q131 = PdfImportStagingQuestion::query()->where('import_session_id', $batchId)->where('question_number', 131)->firstOrFail();
        $this->assertSame('Blank 131', $q131->content);
        $this->assertStringContainsString('To: All Staff', (string) $q131->stimulus_text);
    }

    public function test_listening_scan_pdf_with_merged_script_table_parses_all_100_questions_and_6_part1_images(): void
    {
        $this->seedToeicSections();
        $admin = User::factory()->admin()->create();

        $scanImages = [];
        for ($i = 1; $i <= 6; $i++) {
            $scanImages[] = [
                'file_name' => "scan_p1_{$i}.jpeg",
                'relative_path' => "storage/staging/fake/scan_p1_{$i}.jpeg",
                'page_no' => $i,
                'width' => 1200,
                'height' => 900,
                'x0' => 40.0,
                'y0' => 50.0,
                'x1' => 500.0,
                'y1' => 400.0,
            ];
        }

        $tableRows = [];
        for ($i = 1; $i <= 6; $i++) {
            $tableRows[] = "{$i} | (A) A{$i} (B) B{$i} (C) C{$i} (D) D{$i} | B | Dịch câu {$i}";
        }
        for ($i = 7; $i <= 31; $i++) {
            $tableRows[] = "{$i} | Question {$i}? (A) Yes (B) No (C) Maybe | C | Dịch câu {$i}";
        }
        // Part 3 & 4 with merged rows (empty STT on 2nd row!)
        for ($start = 32; $start <= 98; $start += 3) {
            $q2 = $start + 1;
            $q3 = $start + 2;
            $tableRows[] = "{$start}-{$q3} | W: Dialogue for {$start}-{$q3}. | | Dịch hội thoại {$start}-{$q3}";
            $tableRows[] = " | {$start}. Q{$start}? (A) A1 (B) B1 (C) C1 (D) D1 {$q2}. Q{$q2}? (A) A2 (B) B2 (C) C2 (D) D2 {$q3}. Q{$q3}? (A) A3 (B) B3 (C) C3 (D) D3 | {$start}. A {$q2}. B {$q3}. C | Dịch câu {$start}-{$q3}";
        }

        $this->app->instance(PdfLayoutExtractorInterface::class, new class($scanImages, $tableRows) implements PdfLayoutExtractorInterface
        {
            public function __construct(private array $scanImages, private array $tableRows) {}

            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                if (str_contains($pdfAbsolutePath, 'answers.pdf')) {
                    return [
                        'page_count' => 1,
                        'pages' => [[
                            'page_no' => 1,
                            'width' => 595.0,
                            'height' => 842.0,
                            'is_two_column' => false,
                            'blocks' => [],
                            'tables' => [[
                                'table_index' => 0,
                                'x0' => 40.0,
                                'y0' => 40.0,
                                'x1' => 550.0,
                                'y1' => 800.0,
                                'rows' => $this->tableRows,
                                'text' => implode("\n", $this->tableRows),
                            ]],
                            'images' => [],
                        ]],
                    ];
                }

                // Scan PDF: 0 text blocks, 6 Part 1 images
                return [
                    'page_count' => 1,
                    'pages' => [[
                        'page_no' => 1,
                        'width' => 595.0,
                        'height' => 842.0,
                        'is_two_column' => false,
                        'blocks' => [],
                        'tables' => [],
                        'images' => $this->scanImages,
                    ]],
                ];
            }
        });

        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => UploadedFile::fake()->create('de1-L.pdf', 100, 'application/pdf'),
            'answer_pdf' => UploadedFile::fake()->create('LC_1.pdf', 50, 'application/pdf'),
            'import_scope' => 'LISTENING',
        ]);
        $resp->assertRedirect();
        $batchId = basename((string) $resp->headers->get('Location'));
        $session = PdfImportSession::query()->findOrFail($batchId);

        $this->assertSame([], $session->summary_json['missing_numbers']);
        $this->assertSame(100, $session->summary_json['total_questions']);
        $this->assertSame(100, $session->summary_json['valid_questions']);
        $this->assertSame(0, $session->summary_json['error_questions']);
        $this->assertSame(6, $session->summary_json['part_counts']['PART1']);
        $this->assertSame(25, $session->summary_json['part_counts']['PART2']);
        $this->assertSame(39, $session->summary_json['part_counts']['PART3']);
        $this->assertSame(30, $session->summary_json['part_counts']['PART4']);

        for ($i = 1; $i <= 6; $i++) {
            $q = PdfImportStagingQuestion::query()->where('import_session_id', $batchId)->where('question_number', $i)->firstOrFail();
            $this->assertSame("storage/staging/fake/scan_p1_{$i}.jpeg", $q->media_url);
        }
    }

    public function test_commit_is_blocked_when_part5_missing_cd_options_or_missing_more_than_5_questions(): void
    {
        $this->seedToeicSections();
        $admin = User::factory()->admin()->create();

        $this->app->instance(PdfLayoutExtractorInterface::class, new class implements PdfLayoutExtractorInterface
        {
            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                if (str_contains($pdfAbsolutePath, 'answers.pdf')) {
                    return [
                        'page_count' => 1,
                        'pages' => [[
                            'page_no' => 1,
                            'width' => 595.0,
                            'height' => 842.0,
                            'is_two_column' => false,
                            'blocks' => [['x0' => 40, 'y0' => 40, 'x1' => 500, 'y1' => 100, 'column' => 'single', 'text' => '101. A']],
                            'tables' => [],
                            'images' => [],
                        ]],
                    ];
                }

                return [
                    'page_count' => 1,
                    'pages' => [[
                        'page_no' => 1,
                        'width' => 595.0,
                        'height' => 842.0,
                        'is_two_column' => false,
                        'blocks' => [[
                            'x0' => 40.0,
                            'y0' => 50.0,
                            'x1' => 500.0,
                            'y1' => 200.0,
                            'column' => 'single',
                            'text' => "101. Incomplete Part 5 missing C and D.\n(A) Only A\n(B) Only B",
                        ]],
                        'tables' => [],
                        'images' => [],
                    ]],
                ];
            }
        });

        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => UploadedFile::fake()->create('bad_reading.pdf', 50, 'application/pdf'),
            'answer_pdf' => UploadedFile::fake()->create('bad_ans.pdf', 20, 'application/pdf'),
            'import_scope' => 'READING',
        ]);
        $resp->assertRedirect();
        $batchId = basename((string) $resp->headers->get('Location'));

        $q101 = PdfImportStagingQuestion::query()->where('import_session_id', $batchId)->where('question_number', 101)->firstOrFail();
        $this->assertSame(PdfImportStagingQuestion::VALIDATION_ERROR, $q101->validation_status);
        $this->assertStringContainsString('Thiếu phương án trả lời', implode(' ', $q101->validation_issues_json ?? []));

        // Attempt commit -> must be blocked and redirect back to review with error
        $commitResp = $this->actingAs($admin)->post(route('admin.questions.import.commit', ['batchId' => $batchId]), [
            'publish_status' => 'ACTIVE',
            'auto_generate_tts' => '0',
        ]);
        $commitResp->assertRedirect(route('admin.questions.import.review', ['batchId' => $batchId]));
        $commitResp->assertSessionHasErrors('commit');
        $this->assertSame(0, Question::query()->count());
    }

    public function test_part1_directions_example_page_images_are_skipped_and_6_real_images_map_sequentially(): void
    {
        $this->seedToeicSections();
        $admin = User::factory()->admin()->create();

        $this->app->instance(PdfLayoutExtractorInterface::class, new class implements PdfLayoutExtractorInterface
        {
            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                return [
                    'page_count' => 4,
                    'pages' => [
                        [
                            'page_no' => 1,
                            'width' => 595.0,
                            'height' => 842.0,
                            'is_two_column' => false,
                            'blocks' => [[
                                'x0' => 40.0, 'y0' => 40.0, 'x1' => 550.0, 'y1' => 200.0, 'column' => 'single',
                                'text' => "PART 1\nDirections: For each question in this part...\nExample: Statement (C), \"They're sitting at a table,\" is the best description.",
                            ]],
                            'tables' => [],
                            'images' => [[
                                'file_name' => 'example_img.png',
                                'relative_path' => 'storage/staging/fake/example_img.png',
                                'page_no' => 1, 'width' => 600, 'height' => 400, 'x0' => 50, 'y0' => 250, 'x1' => 500, 'y1' => 550,
                            ]],
                        ],
                        [
                            'page_no' => 2,
                            'width' => 595.0,
                            'height' => 842.0,
                            'is_two_column' => false,
                            'blocks' => [[
                                'x0' => 40.0, 'y0' => 40.0, 'x1' => 550.0, 'y1' => 700.0, 'column' => 'single',
                                'text' => "1.\n(A) A1 (B) B1 (C) C1 (D) D1\n2.\n(A) A2 (B) B2 (C) C2 (D) D2",
                            ]],
                            'tables' => [],
                            'images' => [
                                ['file_name' => 'tiny_logo.png', 'relative_path' => 'storage/staging/fake/tiny_logo.png', 'page_no' => 2, 'width' => 50, 'height' => 50, 'x0' => 20, 'y0' => 780, 'x1' => 70, 'y1' => 830],
                                ['file_name' => 'real_2.png', 'relative_path' => 'storage/staging/fake/real_2.png', 'page_no' => 2, 'width' => 500, 'height' => 350, 'x0' => 50, 'y0' => 420, 'x1' => 500, 'y1' => 700],
                                ['file_name' => 'real_1.png', 'relative_path' => 'storage/staging/fake/real_1.png', 'page_no' => 2, 'width' => 500, 'height' => 350, 'x0' => 50, 'y0' => 80, 'x1' => 500, 'y1' => 350],
                            ],
                        ],
                        [
                            'page_no' => 3,
                            'width' => 595.0,
                            'height' => 842.0,
                            'is_two_column' => false,
                            'blocks' => [[
                                'x0' => 40.0, 'y0' => 40.0, 'x1' => 550.0, 'y1' => 700.0, 'column' => 'single',
                                'text' => "3.\n(A) A3 (B) B3 (C) C3 (D) D3\n4.\n(A) A4 (B) B4 (C) C4 (D) D4",
                            ]],
                            'tables' => [],
                            'images' => [
                                ['file_name' => 'real_3.png', 'relative_path' => 'storage/staging/fake/real_3.png', 'page_no' => 3, 'width' => 500, 'height' => 350, 'x0' => 50, 'y0' => 80, 'x1' => 500, 'y1' => 350],
                                ['file_name' => 'real_4.png', 'relative_path' => 'storage/staging/fake/real_4.png', 'page_no' => 3, 'width' => 500, 'height' => 350, 'x0' => 50, 'y0' => 420, 'x1' => 500, 'y1' => 700],
                            ],
                        ],
                        [
                            'page_no' => 4,
                            'width' => 595.0,
                            'height' => 842.0,
                            'is_two_column' => false,
                            'blocks' => [[
                                'x0' => 40.0, 'y0' => 40.0, 'x1' => 550.0, 'y1' => 700.0, 'column' => 'single',
                                'text' => "5.\n(A) A5 (B) B5 (C) C5 (D) D5\n6.\n(A) A6 (B) B6 (C) C6 (D) D6",
                            ]],
                            'tables' => [],
                            'images' => [
                                ['file_name' => 'real_5.png', 'relative_path' => 'storage/staging/fake/real_5.png', 'page_no' => 4, 'width' => 500, 'height' => 350, 'x0' => 50, 'y0' => 80, 'x1' => 500, 'y1' => 350],
                                ['file_name' => 'real_6.png', 'relative_path' => 'storage/staging/fake/real_6.png', 'page_no' => 4, 'width' => 500, 'height' => 350, 'x0' => 50, 'y0' => 420, 'x1' => 500, 'y1' => 700],
                            ],
                        ],
                    ],
                ];
            }
        });

        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => UploadedFile::fake()->create('p1_directions.pdf', 50, 'application/pdf'),
            'import_scope' => 'LISTENING',
        ]);
        $resp->assertRedirect();
        $batchId = basename((string) $resp->headers->get('Location'));

        for ($i = 1; $i <= 6; $i++) {
            $q = PdfImportStagingQuestion::query()->where('import_session_id', $batchId)->where('question_number', $i)->firstOrFail();
            $this->assertSame("storage/staging/fake/real_{$i}.png", $q->media_url);
        }
    }

    public function test_listening_100_questions_with_empty_part1_content_and_needs_review_commits_all_100_to_db_and_test_form(): void
    {
        if (! Schema::hasTable('test_form_question')) {
            Schema::create('test_form_question', function (Blueprint $table): void {
                $table->unsignedBigInteger('test_form_id');
                $table->unsignedBigInteger('question_id');
                $table->integer('sequence_no');
                $table->decimal('point', 6, 2)->default(1);
                $table->primary(['test_form_id', 'question_id']);
            });
        }

        $this->seedToeicSections();
        $admin = User::factory()->admin()->create();

        $blueprint = TestBlueprint::create([
            'name' => 'TOEIC Listening 100',
            'exam_type' => TestForm::EXAM_TYPE_LISTENING,
            'total_time' => 45,
            'status' => 'ACTIVE',
        ]);
        $testForm = TestForm::create([
            'blueprint_id' => $blueprint->blueprint_id,
            'title' => 'Listening 100 Commit Test',
            'exam_type' => TestForm::EXAM_TYPE_LISTENING,
            'status' => 'DRAFT',
        ]);

        $blocks = [];
        $images = [];
        for ($i = 1; $i <= 6; $i++) {
            $images[] = [
                'file_name' => "p1_{$i}.png",
                'relative_path' => "storage/staging/fake/p1_{$i}.png",
                'page_no' => 1, 'width' => 400, 'height' => 300, 'x0' => 40, 'y0' => (float) ($i * 100), 'x1' => 440, 'y1' => (float) ($i * 100 + 80),
            ];
            $blocks[] = [
                'x0' => 40, 'y0' => (float) ($i * 100), 'x1' => 400, 'y1' => (float) ($i * 100 + 20), 'column' => 'single',
                'text' => "{$i}.\n(A) A{$i} (B) B{$i} (C) C{$i} (D) D{$i}",
            ];
        }
        for ($i = 7; $i <= 31; $i++) {
            $blocks[] = [
                'x0' => 40, 'y0' => 800.0 + $i, 'x1' => 400, 'y1' => 805.0 + $i, 'column' => 'single',
                'text' => "{$i}. Listen.\n(A) Yes (B) No (C) Maybe",
            ];
        }
        for ($start = 32; $start <= 98; $start += 3) {
            $end = $start + 2;
            $txt = "Questions {$start}-{$end} refer to the following audio.\n";
            for ($q = $start; $q <= $end; $q++) {
                $txt .= "{$q}. Listening {$q}?\n(A) A{$q} (B) B{$q} (C) C{$q} (D) D{$q}\n";
            }
            $blocks[] = ['x0' => 40, 'y0' => 1000.0 + $start, 'x1' => 400, 'y1' => 1020.0 + $start, 'column' => 'single', 'text' => $txt];
        }

        $this->app->instance(PdfLayoutExtractorInterface::class, new class($blocks, $images) implements PdfLayoutExtractorInterface
        {
            public function __construct(private array $blocks, private array $images) {}

            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                return [
                    'page_count' => 1,
                    'pages' => [[
                        'page_no' => 1, 'width' => 595.0, 'height' => 842.0, 'is_two_column' => false,
                        'blocks' => $this->blocks, 'tables' => [], 'images' => $this->images,
                    ]],
                ];
            }
        });

        // Single PDF (no answer file -> all 100 questions are NEEDS_REVIEW, Part 1 has empty content)
        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => UploadedFile::fake()->create('listening_100_single.pdf', 100, 'application/pdf'),
            'import_scope' => 'LISTENING',
            'target_test_id' => $testForm->test_form_id,
        ]);
        $resp->assertRedirect();
        $batchId = basename((string) $resp->headers->get('Location'));

        $session = PdfImportSession::query()->findOrFail($batchId);
        $this->assertSame(100, $session->summary_json['total_questions']);
        $this->assertSame(100, $session->summary_json['needs_review_questions']);
        $this->assertSame(0, $session->summary_json['error_questions']);

        $stagingPayload = app(DocxToStandardXmlService::class)->getStagingPayload($batchId);
        $this->assertIsArray($stagingPayload);

        $commitResp = $this->actingAs($admin)->post(route('admin.questions.import.commit', ['batchId' => $batchId]), [
            'payload_json' => json_encode($stagingPayload['sets'], JSON_UNESCAPED_UNICODE),
            'publish_status' => 'ACTIVE',
            'target_test_id' => $testForm->test_form_id,
            'auto_generate_tts' => '0',
        ]);
        $commitResp->assertRedirect(route('admin.tests.show', $testForm->test_form_id));
        $commitResp->assertSessionHas('success');

        $this->assertSame(100, Question::query()->count());
        $this->assertSame(100, $testForm->questions()->count());
        $this->assertSame(PdfImportSession::STATUS_COMMITTED, $session->fresh()->status);
    }

    public function test_admin_can_replace_part1_image_via_set_images_on_staging_review_commit(): void
    {
        Storage::fake('public');
        $this->seedToeicSections();
        $admin = User::factory()->admin()->create();

        $batchId = 'test_replace_img_'.uniqid();
        $setsPayload = [[
            'part' => 'PART1',
            'title' => 'Part 1 Q1',
            'stimulus' => '',
            'transcript' => 'A woman is typing on a laptop.',
            'media_url' => 'storage/staging/import_'.$batchId.'/images/old_bad_crop.png',
            'audio_url' => '',
            'questions' => [[
                'seq' => 1,
                'difficulty' => 'EASY',
                'media_url' => 'storage/staging/import_'.$batchId.'/images/old_bad_crop.png',
                'content' => '',
                'explanation' => 'Giải thích câu 1',
                'correct' => 'A',
                'options' => [
                    'A' => 'Typing on a laptop',
                    'B' => 'Reading a book',
                    'C' => 'Drinking coffee',
                    'D' => 'Walking outside',
                ],
            ]],
        ]];

        app(DocxToStandardXmlService::class)->saveStagingPayload($batchId, [
            'batch_id' => $batchId,
            'file_name' => 'Part1_Replace.docx',
            'publish_status' => 'ACTIVE',
            'target_test_id' => null,
            'sets' => $setsPayload,
            'summary' => [
                'total_questions' => 1,
                'valid_questions' => 1,
                'error_questions' => 0,
                'image_count' => 1,
                'transcript_count' => 1,
            ],
        ]);

        $reviewResp = $this->actingAs($admin)->get(route('admin.questions.import.review', ['batchId' => $batchId]));
        $reviewResp->assertOk();
        $reviewResp->assertSee('Thay ảnh');
        $reviewResp->assertSee('Xóa ảnh');
        $reviewResp->assertSee('set_images[0]', false);

        $newUploadedImage = UploadedFile::fake()->image('manual_part1_q1.png', 640, 480);

        $commitResp = $this->actingAs($admin)->post(route('admin.questions.import.commit', ['batchId' => $batchId]), [
            'payload_json' => json_encode($setsPayload, JSON_UNESCAPED_UNICODE),
            'publish_status' => 'ACTIVE',
            'auto_generate_tts' => '0',
            'set_images' => [
                0 => $newUploadedImage,
            ],
        ]);
        $commitResp->assertRedirect(route('admin.questions.index'));

        $question = Question::query()->with('questionSet')->firstOrFail();
        $savedMediaUrl = (string) $question->questionSet?->media_url;
        $this->assertStringStartsWith('/storage/questions/images/', $savedMediaUrl);
        $this->assertStringNotContainsString('old_bad_crop.png', $savedMediaUrl);
        Storage::disk('public')->assertExists(ltrim(str_replace('/storage/', '', $savedMediaUrl), '/'));
    }

    public function test_part2_with_invalid_d_answer_flags_clear_warning_and_allows_admin_to_select_abc_on_review(): void
    {
        $this->seedToeicSections();
        $admin = User::factory()->admin()->create();

        $this->app->instance(PdfLayoutExtractorInterface::class, new class implements PdfLayoutExtractorInterface
        {
            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                if (str_contains($pdfAbsolutePath, 'answers.pdf')) {
                    return [
                        'page_count' => 1,
                        'pages' => [[
                            'page_no' => 1, 'width' => 595.0, 'height' => 842.0, 'is_two_column' => false,
                            'blocks' => [['x0' => 40, 'y0' => 40, 'x1' => 500, 'y1' => 100, 'column' => 'single', 'text' => '7. D']],
                            'tables' => [], 'images' => [],
                        ]],
                    ];
                }

                return [
                    'page_count' => 1,
                    'pages' => [[
                        'page_no' => 1, 'width' => 595.0, 'height' => 842.0, 'is_two_column' => false,
                        'blocks' => [[
                            'x0' => 40, 'y0' => 40, 'x1' => 500, 'y1' => 150, 'column' => 'single',
                            'text' => "7. Where is the conference?\n(A) At a hotel\n(B) Tomorrow\n(C) Yes, I did",
                        ]],
                        'tables' => [], 'images' => [],
                    ]],
                ];
            }
        });

        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => UploadedFile::fake()->create('part2_bad_ans.pdf', 50, 'application/pdf'),
            'answer_pdf' => UploadedFile::fake()->create('part2_bad_key.pdf', 20, 'application/pdf'),
            'import_scope' => 'LISTENING',
        ]);
        $resp->assertRedirect();
        $batchId = basename((string) $resp->headers->get('Location'));

        $q7 = PdfImportStagingQuestion::query()->where('import_session_id', $batchId)->where('question_number', 7)->firstOrFail();
        $this->assertSame(PdfImportStagingQuestion::VALIDATION_ERROR, $q7->validation_status);
        $this->assertStringContainsString('Đáp án gốc là D nhưng Part 2 chỉ có A, B, C', implode(' ', $q7->validation_issues_json ?? []));

        $reviewResp = $this->actingAs($admin)->get(route('admin.questions.import.review', ['batchId' => $batchId]));
        $reviewResp->assertOk();
        $reviewResp->assertSee('handleCorrectOptionChange', false);
        $reviewResp->assertSee('addStagingOption', false);
        $reviewResp->assertSee('max-height: 256px', false);

        $staging = app(DocxToStandardXmlService::class)->getStagingPayload($batchId);
        $this->assertStringContainsString('three responses spoken in English', (string) ($staging['part_directions']['PART2'] ?? ''));
    }

    public function test_part7_double_passage_keeps_both_passages_in_stimulus_and_stitches_cross_page_split_question(): void
    {
        $parser = app(PdfReadingQuestionParser::class);
        $parsed = $parser->parse([
            'pages' => [
                [
                    'page_no' => 1,
                    'width' => 595,
                    'is_two_column' => false,
                    'blocks' => [[
                        'x0' => 40, 'y0' => 40, 'x1' => 550, 'y1' => 700, 'column' => 'single',
                        'text' => implode("\n", [
                            'Questions 176-180 refer to the following e-mail and notice.',
                            'E-mail',
                            'From: AT&T R&D Lead',
                            'Subject: Budget <= $5,000',
                            'Please review the attached schedule.',
                            'Notice',
                            'All R&D teams must submit forms by Friday.',
                            '176. What is mentioned about the budget?',
                            '(A) It is under $5,000',
                            '(B) It was rejected',
                        ]),
                    ]],
                    'tables' => [],
                    'images' => [],
                ],
                [
                    'page_no' => 2,
                    'width' => 595,
                    'is_two_column' => false,
                    'blocks' => [[
                        'x0' => 40, 'y0' => 40, 'x1' => 550, 'y1' => 400, 'column' => 'single',
                        'text' => implode("\n", [
                            'Page 2',
                            'ACTUAL TEST 1',
                            'GO ON TO THE NEXT PAGE',
                            '(C) It requires R&D approval',
                            '(D) It starts next month',
                            '177. When are forms due?',
                            'A. Monday B. Wednesday C. Friday D. Sunday',
                        ]),
                    ]],
                    'tables' => [],
                    'images' => [],
                ],
            ],
        ]);

        $this->assertCount(2, $parsed);
        $q176 = $parsed[0];
        $this->assertSame(176, $q176['question_number']);
        $this->assertStringContainsString('E-mail', (string) $q176['stimulus_text']);
        $this->assertStringContainsString('Notice', (string) $q176['stimulus_text']);
        $this->assertCount(4, $q176['options']);
        $this->assertSame('It requires R&D approval', $q176['options']['C']);
        $this->assertSame('It starts next month', $q176['options']['D']);

        $q177 = $parsed[1];
        $this->assertSame(['A' => 'Monday', 'B' => 'Wednesday', 'C' => 'Friday', 'D' => 'Sunday'], $q177['options']);

        // Kiểm tra mergeFragmentedQuestionsBySeq gộp 2 mảnh cùng seq
        $service = app(PdfExamImportService::class);
        $merged = $service->mergeFragmentedQuestionsBySeq([
            [
                'question_number' => 178,
                'part_code' => 'PART7',
                'content' => 'Who will attend the meeting?',
                'options' => ['A' => 'Ms. Kim', 'B' => 'Mr. Park'],
                'correct_answer' => null,
                'explanation' => null,
            ],
            [
                'question_number' => 178,
                'part_code' => 'PART7',
                'content' => '',
                'options' => ['C' => 'Dr. Lee', 'D' => 'Ms. Tran'],
                'correct_answer' => 'C',
                'explanation' => 'Đoạn 2 nhắc tới Dr. Lee.',
            ],
        ]);
        $this->assertCount(1, $merged);
        $this->assertSame('Who will attend the meeting?', $merged[0]['content']);
        $this->assertCount(4, $merged[0]['options']);
        $this->assertSame('C', $merged[0]['correct_answer']);
    }

    public function test_password_protected_pdf_returns_friendly_validation_error_without_500(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->app->bind(PdfLayoutExtractorInterface::class, fn () => new class implements PdfLayoutExtractorInterface
        {
            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                throw new \RuntimeException('File PDF đang được bảo vệ bằng mật khẩu hoặc không thể trích xuất, vui lòng mở khóa PDF hoặc chuyển sang file .docx.');
            }
        });

        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => UploadedFile::fake()->create('locked.pdf', 50, 'application/pdf'),
            'import_scope' => 'READING',
        ]);

        $resp->assertRedirect(route('admin.questions.bulk-import'));
        $resp->assertSessionHasErrors('question_pdf');
    }

    public function test_scan_part1_with_cover_and_directions_example_fragments_keeps_only_6_real_photos_for_q1_to_q6(): void
    {
        $this->seedToeicSections();
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->app->bind(PdfLayoutExtractorInterface::class, fn () => new class implements PdfLayoutExtractorInterface
        {
            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                if (str_ends_with($pdfAbsolutePath, 'answers.pdf')) {
                    $rows = ['STT | Transcript | Đáp án | Dịch'];
                    for ($q = 1; $q <= 6; $q++) {
                        $rows[] = "{$q} | (A) Opt A{$q} (B) Opt B{$q} (C) Opt C{$q} (D) Opt D{$q} | B | Dịch {$q}";
                    }

                    return [
                        'pages' => [[
                            'page_no' => 1, 'width' => 595, 'is_two_column' => false,
                            'blocks' => [],
                            'tables' => [['table_index' => 0, 'x0' => 20, 'y0' => 20, 'x1' => 550, 'y1' => 700, 'rows' => $rows, 'text' => implode("\n", $rows)]],
                            'images' => [],
                        ]],
                    ];
                }

                // Scan PDF: page 1 cover, page 2 Directions/Example (2 fragments), pages 3-5 = 6 real Part 1 photos
                $makeImg = fn (int $page, string $name): array => [
                    'file_name' => $name,
                    'relative_path' => "{$relativeImagePrefix}/{$name}",
                    'page_no' => $page,
                    'width' => 600, 'height' => 400,
                    'x0' => 40, 'y0' => 50, 'x1' => 550, 'y1' => 350,
                ];

                return [
                    'pages' => [
                        ['page_no' => 1, 'width' => 595, 'is_two_column' => false, 'blocks' => [], 'tables' => [], 'images' => []],
                        ['page_no' => 2, 'width' => 595, 'is_two_column' => false, 'blocks' => [], 'tables' => [], 'images' => [$makeImg(2, 'dir_frag1.png'), $makeImg(2, 'example_frag2.png')]],
                        ['page_no' => 3, 'width' => 595, 'is_two_column' => false, 'blocks' => [], 'tables' => [], 'images' => [$makeImg(3, 'real_q1.png'), $makeImg(3, 'real_q2.png')]],
                        ['page_no' => 4, 'width' => 595, 'is_two_column' => false, 'blocks' => [], 'tables' => [], 'images' => [$makeImg(4, 'real_q3.png'), $makeImg(4, 'real_q4.png')]],
                        ['page_no' => 5, 'width' => 595, 'is_two_column' => false, 'blocks' => [], 'tables' => [], 'images' => [$makeImg(5, 'real_q5.png'), $makeImg(5, 'real_q6.png')]],
                    ],
                ];
            }
        });

        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => UploadedFile::fake()->create('scan_p1.pdf', 50, 'application/pdf'),
            'answer_pdf' => UploadedFile::fake()->create('script_p1.pdf', 20, 'application/pdf'),
            'import_scope' => 'LISTENING',
        ]);
        $resp->assertRedirect();
        $batchId = basename((string) $resp->headers->get('Location'));

        for ($q = 1; $q <= 6; $q++) {
            $row = PdfImportStagingQuestion::query()->where('import_session_id', $batchId)->where('question_number', $q)->firstOrFail();
            $this->assertStringEndsWith("real_q{$q}.png", (string) $row->media_url, "Question {$q} should receive real_q{$q}.png");
        }

        $reviewResp = $this->actingAs($admin)->get(route('admin.questions.import.review', ['batchId' => $batchId]));
        $reviewResp->assertOk();
        $reviewResp->assertSee('Có ảnh: 6');
    }

    public function test_reading_pdf_pair_parses_all_100_questions_part5_part6_part7_with_cross_page_answers_and_fallback_options(): void
    {
        $this->seedToeicSections();
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->app->bind(PdfLayoutExtractorInterface::class, fn () => new class implements PdfLayoutExtractorInterface
        {
            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                if (str_ends_with($pdfAbsolutePath, 'answers.pdf')) {
                    // Mô phỏng bảng 3 cột RC 10.pdf bị ngắt trang giữa số câu 111 -> Chọn (B) ở trang sau
                    $rowsP1 = ['STT | Nội dung đề | Đáp án và giải thích'];
                    for ($q = 101; $q <= 111; $q++) {
                        $ansText = $q === 111 ? '' : 'Chọn (B) Giải thích câu '.$q;
                        $rowsP1[] = "{$q} | Câu {$q} (A) Opt A{$q} (B) Opt B{$q} (C) Opt C{$q} (D) Opt D{$q} | {$ansText}";
                    }
                    $rowsP2 = [
                        ' | (C) Opt C111 (D) Opt D111 | Chọn (B) Giải thích tiếp câu 111',
                    ];
                    for ($q = 112; $q <= 200; $q++) {
                        $ansCol = $q >= 147 ? 'C' : '→ Chọn (C) Giải thích câu '.$q;
                        $rowsP2[] = "{$q} | Câu {$q} (A) Opt A{$q} (B) Opt B{$q} (C) Opt C{$q} (D) Opt D{$q} | {$ansCol}";
                    }

                    return [
                        'pages' => [
                            [
                                'page_no' => 1, 'width' => 595, 'is_two_column' => false,
                                'blocks' => [],
                                'tables' => [['table_index' => 0, 'x0' => 20, 'y0' => 20, 'x1' => 550, 'y1' => 780, 'rows' => $rowsP1, 'text' => implode("\n", $rowsP1)]],
                                'images' => [],
                            ],
                            [
                                'page_no' => 2, 'width' => 595, 'is_two_column' => false,
                                'blocks' => [],
                                'tables' => [['table_index' => 0, 'x0' => 20, 'y0' => 20, 'x1' => 550, 'y1' => 780, 'rows' => $rowsP2, 'text' => implode("\n", $rowsP2)]],
                                'images' => [],
                            ],
                        ],
                    ];
                }

                // Mô phỏng de10.pdf:
                // - Part 5 (101..130), trong đó câu 118 bị ngắt cột (chỉ có A, B ở cột 1; C, D ở cột 2), câu 122 chỉ có A, B ở đề (lấy bù C, D từ RC 10)
                $p5Col1 = ['PART 5'];
                for ($q = 101; $q <= 118; $q++) {
                    $p5Col1[] = "{$q}. Sample Part 5 stem {$q} -------.";
                    $p5Col1[] = "(A) Opt A{$q}";
                    $p5Col1[] = "(B) Opt B{$q}";
                    if ($q !== 118) {
                        $p5Col1[] = "(C) Opt C{$q}";
                        $p5Col1[] = "(D) Opt D{$q}";
                    }
                }
                $p5Col2 = [
                    '(C) Opt C118',
                    '(D) Opt D118',
                ];
                for ($q = 119; $q <= 130; $q++) {
                    $p5Col2[] = "{$q}. Sample Part 5 stem {$q} -------.";
                    $p5Col2[] = "(A) Opt A{$q}";
                    $p5Col2[] = "(B) Opt B{$q}";
                    if ($q !== 122) {
                        $p5Col2[] = "(C) Opt C{$q}";
                        $p5Col2[] = "(D) Opt D{$q}";
                    }
                }

                // - Part 6 (131..146): dạng "131." không có văn bản câu hỏi, chỉ có (A)..(D)
                $p6Lines = ['PART 6'];
                for ($g = 131; $g <= 146; $g += 4) {
                    $gEnd = $g + 3;
                    $p6Lines[] = "Questions {$g}-{$gEnd} refer to the following notice.";
                    $p6Lines[] = "Notice content for blanks {$g} to {$gEnd}.";
                    for ($q = $g; $q <= $gEnd; $q++) {
                        $p6Lines[] = "{$q}.";
                        $p6Lines[] = "(A) Opt A{$q} (B) Opt B{$q} (C) Opt C{$q} (D) Opt D{$q}";
                    }
                }

                // - Part 7 (147..200): các cụm Questions X-Y refer to...
                $p7Lines = ['PART 7'];
                $ranges = [
                    [147, 148], [149, 150], [151, 152], [153, 154], [155, 157],
                    [158, 160], [161, 163], [164, 167], [168, 171], [172, 175],
                    [176, 180], [181, 185], [186, 190], [191, 195], [196, 200],
                ];
                foreach ($ranges as [$rStart, $rEnd]) {
                    $p7Lines[] = "Questions {$rStart}-{$rEnd} refer to the following passage.";
                    $p7Lines[] = "Passage text for {$rStart}-{$rEnd}.";
                    for ($q = $rStart; $q <= $rEnd; $q++) {
                        $p7Lines[] = "{$q}. What is indicated about item {$q}?";
                        $p7Lines[] = "(A) Opt A{$q} (B) Opt B{$q} (C) Opt C{$q} (D) Opt D{$q}";
                    }
                }

                return [
                    'pages' => [
                        [
                            'page_no' => 1, 'width' => 595, 'is_two_column' => true,
                            'blocks' => [
                                ['x0' => 30, 'y0' => 40, 'x1' => 270, 'y1' => 760, 'column' => 'left', 'text' => implode("\n", $p5Col1)],
                                ['x0' => 300, 'y0' => 40, 'x1' => 560, 'y1' => 760, 'column' => 'right', 'text' => implode("\n", $p5Col2)],
                            ],
                            'tables' => [], 'images' => [],
                        ],
                        [
                            'page_no' => 2, 'width' => 595, 'is_two_column' => false,
                            'blocks' => [
                                ['x0' => 30, 'y0' => 40, 'x1' => 560, 'y1' => 760, 'column' => 'single', 'text' => implode("\n", $p6Lines)],
                            ],
                            'tables' => [], 'images' => [],
                        ],
                        [
                            'page_no' => 3, 'width' => 595, 'is_two_column' => false,
                            'blocks' => [
                                ['x0' => 30, 'y0' => 40, 'x1' => 560, 'y1' => 760, 'column' => 'single', 'text' => implode("\n", $p7Lines)],
                            ],
                            'tables' => [], 'images' => [],
                        ],
                    ],
                ];
            }
        });

        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => UploadedFile::fake()->create('de10.pdf', 100, 'application/pdf'),
            'answer_pdf' => UploadedFile::fake()->create('RC 10.pdf', 100, 'application/pdf'),
            'import_scope' => 'FULL_LR',
        ]);
        $resp->assertRedirect();
        $batchId = basename((string) $resp->headers->get('Location'));

        $session = PdfImportSession::query()->where('import_session_id', $batchId)->firstOrFail();
        $this->assertSame('READING', $session->import_scope);
        $this->assertSame(100, $session->summary_json['total_questions']);
        $this->assertSame(30, $session->summary_json['part_counts']['PART5']);
        $this->assertSame(16, $session->summary_json['part_counts']['PART6']);
        $this->assertSame(54, $session->summary_json['part_counts']['PART7']);
        $this->assertSame(0, $session->summary_json['error_questions']);

        $allRows = PdfImportStagingQuestion::query()
            ->where('import_session_id', $batchId)
            ->orderBy('question_number')
            ->get();
        $this->assertCount(100, $allRows);

        foreach ($allRows as $row) {
            $this->assertCount(4, $row->options_json ?? [], "Question {$row->question_number} missing options");
            $this->assertContains($row->correct_answer, ['A', 'B', 'C', 'D'], "Question {$row->question_number} missing correct_answer");
        }

        $q111 = $allRows->firstWhere('question_number', 111);
        $this->assertSame('B', $q111->correct_answer);
        $q118 = $allRows->firstWhere('question_number', 118);
        $this->assertSame('Opt C118', $q118->options_json['C']);
        $q122 = $allRows->firstWhere('question_number', 122);
        $this->assertSame('Opt D122', $q122->options_json['D']);
        $q131 = $allRows->firstWhere('question_number', 131);
        $this->assertSame('PART6', $q131->part_code);
        $this->assertSame('Blank 131', $q131->content);
    }

    public function test_reading_pdf_pair_parses_all_100_questions_part5_part6_part7_with_cross_page_answers_and_fallback_options(): void
    {
        $this->seedToeicSections();
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->app->bind(PdfLayoutExtractorInterface::class, fn () => new class implements PdfLayoutExtractorInterface
        {
            public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
            {
                if (str_ends_with($pdfAbsolutePath, 'answers.pdf')) {
                    // Mô phỏng bảng 3 cột RC 10.pdf bị ngắt trang giữa số câu 111 -> Chọn (B) ở trang sau
                    $rowsP1 = ['STT | Nội dung đề | Đáp án và giải thích'];
                    for ($q = 101; $q <= 111; $q++) {
                        $ansText = $q === 111 ? '' : 'Chọn (B) Giải thích câu '.$q;
                        $rowsP1[] = "{$q} | Câu {$q} (A) Opt A{$q} (B) Opt B{$q} (C) Opt C{$q} (D) Opt D{$q} | {$ansText}";
                    }
                    $rowsP2 = [
                        ' | (C) Opt C111 (D) Opt D111 | Chọn (B) Giải thích tiếp câu 111',
                    ];
                    for ($q = 112; $q <= 200; $q++) {
                        $ansCol = $q >= 147 ? 'C' : '→ Chọn (C) Giải thích câu '.$q;
                        $rowsP2[] = "{$q} | Câu {$q} (A) Opt A{$q} (B) Opt B{$q} (C) Opt C{$q} (D) Opt D{$q} | {$ansCol}";
                    }

                    return [
                        'pages' => [
                            [
                                'page_no' => 1, 'width' => 595, 'is_two_column' => false,
                                'blocks' => [],
                                'tables' => [['table_index' => 0, 'x0' => 20, 'y0' => 20, 'x1' => 550, 'y1' => 780, 'rows' => $rowsP1, 'text' => implode("\n", $rowsP1)]],
                                'images' => [],
                            ],
                            [
                                'page_no' => 2, 'width' => 595, 'is_two_column' => false,
                                'blocks' => [],
                                'tables' => [['table_index' => 0, 'x0' => 20, 'y0' => 20, 'x1' => 550, 'y1' => 780, 'rows' => $rowsP2, 'text' => implode("\n", $rowsP2)]],
                                'images' => [],
                            ],
                        ],
                    ];
                }

                // Mô phỏng de10.pdf:
                // - Part 5 (101..130), trong đó câu 118 bị ngắt cột (chỉ có A, B ở cột 1; C, D ở cột 2), câu 122 chỉ có A, B ở đề (lấy bù C, D từ RC 10)
                $p5Col1 = ["PART 5"];
                for ($q = 101; $q <= 118; $q++) {
                    $p5Col1[] = "{$q}. Sample Part 5 stem {$q} -------.";
                    $p5Col1[] = "(A) Opt A{$q}";
                    $p5Col1[] = "(B) Opt B{$q}";
                    if ($q !== 118) {
                        $p5Col1[] = "(C) Opt C{$q}";
                        $p5Col1[] = "(D) Opt D{$q}";
                    }
                }
                $p5Col2 = [
                    '(C) Opt C118',
                    '(D) Opt D118',
                ];
                for ($q = 119; $q <= 130; $q++) {
                    $p5Col2[] = "{$q}. Sample Part 5 stem {$q} -------.";
                    $p5Col2[] = "(A) Opt A{$q}";
                    $p5Col2[] = "(B) Opt B{$q}";
                    if ($q !== 122) {
                        $p5Col2[] = "(C) Opt C{$q}";
                        $p5Col2[] = "(D) Opt D{$q}";
                    }
                }

                // - Part 6 (131..146): dạng "131." không có văn bản câu hỏi, chỉ có (A)..(D)
                $p6Lines = ['PART 6'];
                for ($g = 131; $g <= 146; $g += 4) {
                    $gEnd = $g + 3;
                    $p6Lines[] = "Questions {$g}-{$gEnd} refer to the following notice.";
                    $p6Lines[] = "Notice content for blanks {$g} to {$gEnd}.";
                    for ($q = $g; $q <= $gEnd; $q++) {
                        $p6Lines[] = "{$q}.";
                        $p6Lines[] = "(A) Opt A{$q} (B) Opt B{$q} (C) Opt C{$q} (D) Opt D{$q}";
                    }
                }

                // - Part 7 (147..200): các cụm Questions X-Y refer to...
                $p7Lines = ['PART 7'];
                $ranges = [
                    [147, 148], [149, 150], [151, 152], [153, 154], [155, 157],
                    [158, 160], [161, 163], [164, 167], [168, 171], [172, 175],
                    [176, 180], [181, 185], [186, 190], [191, 195], [196, 200],
                ];
                foreach ($ranges as [$rStart, $rEnd]) {
                    $p7Lines[] = "Questions {$rStart}-{$rEnd} refer to the following passage.";
                    $p7Lines[] = "Passage text for {$rStart}-{$rEnd}.";
                    for ($q = $rStart; $q <= $rEnd; $q++) {
                        $p7Lines[] = "{$q}. What is indicated about item {$q}?";
                        $p7Lines[] = "(A) Opt A{$q} (B) Opt B{$q} (C) Opt C{$q} (D) Opt D{$q}";
                    }
                }

                return [
                    'pages' => [
                        [
                            'page_no' => 1, 'width' => 595, 'is_two_column' => true,
                            'blocks' => [
                                ['x0' => 30, 'y0' => 40, 'x1' => 270, 'y1' => 760, 'column' => 'left', 'text' => implode("\n", $p5Col1)],
                                ['x0' => 300, 'y0' => 40, 'x1' => 560, 'y1' => 760, 'column' => 'right', 'text' => implode("\n", $p5Col2)],
                            ],
                            'tables' => [], 'images' => [],
                        ],
                        [
                            'page_no' => 2, 'width' => 595, 'is_two_column' => false,
                            'blocks' => [
                                ['x0' => 30, 'y0' => 40, 'x1' => 560, 'y1' => 760, 'column' => 'single', 'text' => implode("\n", $p6Lines)],
                            ],
                            'tables' => [], 'images' => [],
                        ],
                        [
                            'page_no' => 3, 'width' => 595, 'is_two_column' => false,
                            'blocks' => [
                                ['x0' => 30, 'y0' => 40, 'x1' => 560, 'y1' => 760, 'column' => 'single', 'text' => implode("\n", $p7Lines)],
                            ],
                            'tables' => [], 'images' => [],
                        ],
                    ],
                ];
            }
        });

        $resp = $this->actingAs($admin)->post(route('admin.questions.import.pdf-preview'), [
            'question_pdf' => UploadedFile::fake()->create('de10.pdf', 100, 'application/pdf'),
            'answer_pdf' => UploadedFile::fake()->create('RC 10.pdf', 100, 'application/pdf'),
            'import_scope' => 'FULL_LR',
        ]);
        $resp->assertRedirect();
        $batchId = basename((string) $resp->headers->get('Location'));

        $session = PdfImportSession::query()->where('import_session_id', $batchId)->firstOrFail();
        $this->assertSame('READING', $session->import_scope);
        $this->assertSame(100, $session->summary_json['total_questions']);
        $this->assertSame(30, $session->summary_json['part_counts']['PART5']);
        $this->assertSame(16, $session->summary_json['part_counts']['PART6']);
        $this->assertSame(54, $session->summary_json['part_counts']['PART7']);
        $this->assertSame(0, $session->summary_json['error_questions']);

        $allRows = PdfImportStagingQuestion::query()
            ->where('import_session_id', $batchId)
            ->orderBy('question_number')
            ->get();
        $this->assertCount(100, $allRows);

        foreach ($allRows as $row) {
            $this->assertCount(4, $row->options_json ?? [], "Question {$row->question_number} missing options");
            $this->assertContains($row->correct_answer, ['A', 'B', 'C', 'D'], "Question {$row->question_number} missing correct_answer");
        }

        $q111 = $allRows->firstWhere('question_number', 111);
        $this->assertSame('B', $q111->correct_answer);
        $q118 = $allRows->firstWhere('question_number', 118);
        $this->assertSame('Opt C118', $q118->options_json['C']);
        $q122 = $allRows->firstWhere('question_number', 122);
        $this->assertSame('Opt D122', $q122->options_json['D']);
        $q131 = $allRows->firstWhere('question_number', 131);
        $this->assertSame('PART6', $q131->part_code);
        $this->assertSame('Blank 131', $q131->content);
    }
}
