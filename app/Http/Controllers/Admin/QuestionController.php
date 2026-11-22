<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnswerOption;
use App\Models\ExamSection;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\QuestionType;
use App\Models\Skill;
use App\Models\TestForm;
use App\Services\AiToeicService;
use App\Services\ExamSampleDataService;
use App\Services\TestFormService;
use App\Services\TextToSpeechService;
use App\Services\WordQuestionImportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionController extends Controller
{
    /**
     * Hiển thị Kho Đề thi & Câu hỏi Chuẩn hóa với bộ lọc đa chiều.
     */
    public function index(Request $request): View
    {
        $filters = $this->extractFilterParams($request);

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage < 5 || $perPage > 100) {
            $perPage = 10;
        }

        $relations = ['section.skill', 'questionType', 'questionSet.questions.options', 'options'];
        if (Schema::hasTable('test_form_question') && Schema::hasTable('test_form')) {
            $relations[] = 'testForms';
        }

        $query = Question::query()
            ->with($relations)
            ->withCount([
                'responses',
                'responses as correct_responses_count' => fn ($responseQuery) => $responseQuery
                    ->whereColumn('attempt_response.score', '>=', 'questions.points'),
            ]);

        $this->applyQuestionFilters($query, $filters);

        // Sắp xếp mặc định: Câu mới tạo / báo lỗi / chờ duyệt hiển thị rõ ràng
        $query->orderByRaw("CASE WHEN status = 'REPORTED' THEN 1 WHEN status = 'PENDING' THEN 2 ELSE 3 END")
            ->orderByDesc('question_id');

        $questions = $query->paginate($perPage)->withQueryString();

        // Thống kê chỉ số thực tế từ DB cho 4 thẻ KPI & banner chân trang
        $totalDb = Question::count();
        $totalReportedDb = Question::where('status', 'REPORTED')->count();
        $totalPendingDb = Question::where('status', 'PENDING')->count();
        $speakingCount = Question::query()
            ->whereHas('section', fn ($q) => $q->where('code', 'like', 'SPK_%'))
            ->count();
        $writingCount = Question::query()
            ->whereHas('section', fn ($q) => $q->where('code', 'like', 'WRITING_%'))
            ->count();
        $audioReadyCount = QuestionSet::query()
            ->where(fn ($q) => $q->whereNotNull('audio_url')->where('audio_url', '!=', ''))
            ->orWhere(fn ($q) => $q->where('media_type', 'AUDIO')->whereNotNull('media_url')->where('media_url', '!=', ''))
            ->count();

        $stats = [
            'total' => $totalDb,
            'lc_rc' => Question::query()
                ->whereHas('section', fn ($q) => $q->whereIn('code', ['PART1', 'PART2', 'PART3', 'PART4', 'PART5', 'PART6', 'PART7']))
                ->count(),
            'sw' => $speakingCount + $writingCount,
            'speaking' => $speakingCount,
            'writing' => $writingCount,
            'reported' => $totalReportedDb,
            'pending' => $totalPendingDb,
            'reported_feedback' => Feedback::query()->where('status', 'PENDING')->count(),
            'audio_ready' => $audioReadyCount,
            'active_count' => Question::where('status', 'ACTIVE')->count(),
            'draft_count' => Question::where('status', 'DRAFT')->count(),
        ];

        $skills = Skill::all();
        $sections = ExamSection::orderBy('sequence_no')->get();
        $questionTypes = QuestionType::all();

        $availableTests = collect();
        $targetTest = null;
        if (Schema::hasTable('test_form')) {
            $availableTestsQuery = TestForm::query()->with('blueprint')->orderByDesc('test_form_id');
            if (Schema::hasTable('test_form_question')) {
                $availableTestsQuery->withCount('questions');
            }
            $availableTests = $availableTestsQuery->get();

            if ($request->filled('test_id')) {
                $targetTest = $availableTests->firstWhere('test_form_id', (int) $request->input('test_id'));
            }
        }

        return view('admin.questions.index', [
            'questions' => $questions,
            'stats' => $stats,
            'skills' => $skills,
            'sections' => $sections,
            'questionTypes' => $questionTypes,
            'availableTests' => $availableTests,
            'targetTest' => $targetTest,
            'filters' => array_merge($filters, [
                'per_page' => $perPage,
                'test_id' => $targetTest?->test_form_id,
            ]),
        ]);
    }

    /**
     * Trích xuất và chuẩn hóa các tham số lọc danh sách câu hỏi (dùng chung cho index & bulkAction).
     *
     * @return array{search: string, skill: string, part: string, difficulty: string, status: string, tag: string}
     */
    private function extractFilterParams(Request $request, string $prefix = ''): array
    {
        $search = trim((string) $request->input($prefix.'search', ''));

        $rawSkill = trim((string) $request->input($prefix.'skill', 'all'));
        $skill = ($rawSkill === '' || strcasecmp($rawSkill, 'all') === 0) ? 'all' : strtoupper($rawSkill);

        $rawPart = trim((string) $request->input($prefix.'part', 'all'));
        $part = ($rawPart === '' || strcasecmp($rawPart, 'all') === 0) ? 'all' : strtoupper($rawPart);

        if ($prefix === '' && $part === 'all' && $request->filled('section_id')) {
            $secCode = ExamSection::query()
                ->where('section_id', (int) $request->input('section_id'))
                ->value('code');
            if ($secCode) {
                $part = strtoupper((string) $secCode);
            }
        }

        $rawDifficulty = trim((string) $request->input($prefix.'difficulty', 'all'));
        $difficulty = in_array(strtoupper($rawDifficulty), ['EASY', 'MEDIUM', 'HARD', 'UNSET'], true)
            ? strtoupper($rawDifficulty)
            : 'all';

        $rawStatus = strtoupper(trim((string) $request->input($prefix.'status', 'all')));
        $status = in_array($rawStatus, ['ACTIVE', 'PENDING', 'DRAFT', 'ARCHIVED', 'REPORTED'], true)
            ? $rawStatus
            : 'all';

        $rawTag = trim((string) $request->input($prefix.'tag', 'all'));
        $tag = ($rawTag === '' || strcasecmp($rawTag, 'all') === 0) ? 'all' : $rawTag;

        return [
            'search' => $search,
            'skill' => $skill,
            'part' => $part,
            'difficulty' => $difficulty,
            'status' => $status,
            'tag' => $tag,
        ];
    }

    /**
     * Áp dụng đồng bộ toàn bộ điều kiện lọc lên truy vấn Question.
     *
     * @param  Builder<Question>  $query
     * @param  array{search: string, skill: string, part: string, difficulty: string, status: string, tag: string}  $filters
     */
    private function applyQuestionFilters(Builder $query, array $filters): void
    {
        $search = $filters['search'];
        $skill = $filters['skill'];
        $part = $filters['part'];
        $difficulty = $filters['difficulty'];
        $status = $filters['status'];
        $tag = $filters['tag'];

        // 1. Tìm kiếm theo Mã ID (#Q-10892), từ khóa, nội dung, giải thích, transcript
        if ($search !== '') {
            $cleanId = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanId) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhere('explanation', 'like', "%{$search}%")
                    ->orWhereHas('questionSet', fn ($qs) => $qs->where('transcript', 'like', "%{$search}%"));

                if ($cleanId !== '') {
                    $q->orWhere('question_id', (int) $cleanId);
                }
            });
        }

        // 2. Lọc theo 4 Kỹ năng độc lập (LC / RC / SPK / WRT, giữ tương thích SW)
        if ($skill !== 'all') {
            $query->whereHas('section', function ($q) use ($skill) {
                if ($skill === 'SPK') {
                    $q->where('code', 'like', 'SPK_%');
                } elseif ($skill === 'WRT') {
                    $q->where('code', 'like', 'WRITING_%');
                } elseif ($skill === 'SW') {
                    $q->where(function ($sub) {
                        $sub->where('code', 'like', 'SPK_%')->orWhere('code', 'like', 'WRITING_%');
                    });
                } elseif ($skill === 'LC') {
                    $q->where(function ($sub) {
                        $sub->whereIn('code', ['PART1', 'PART2', 'PART3', 'PART4'])
                            ->orWhereHas('skill', fn ($sq) => $sq->where('code', 'LC'));
                    });
                } elseif ($skill === 'RC') {
                    $q->where(function ($sub) {
                        $sub->whereIn('code', ['PART5', 'PART6', 'PART7'])
                            ->orWhereHas('skill', fn ($sq) => $sq->where('code', 'RC'));
                    });
                } else {
                    $q->whereHas('skill', fn ($sq) => $sq->where('code', $skill)->orWhere('skill_name', 'like', "%{$skill}%"));
                }
            });
        }

        // 3. Lọc theo Phần thi (Part / Task)
        if ($part !== 'all') {
            $query->whereHas('section', function ($q) use ($part) {
                $q->where('code', $part);
            });
        }

        // 4. Lọc theo Độ khó & Target
        if ($difficulty !== 'all') {
            $query->where('difficulty', $difficulty);
        }

        // 5. Lọc theo trạng thái (nếu không lọc thì hiển thị toàn bộ câu hỏi)
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // 6. Lọc theo Bộ đề thi (nếu chọn cụ thể mã đề TEST-xxx hoặc ID đề)
        if ($tag !== 'all' && is_numeric($tag) && Schema::hasTable('test_form_question')) {
            $query->whereHas('testForms', function ($q) use ($tag) {
                $q->where('test_form.test_form_id', (int) $tag);
            });
        }
    }

    /**
     * Màn hình tạo mới câu hỏi TOEIC.
     */
    public function create(Request $request): View
    {
        $testFormService = app(TestFormService::class);
        $testFormService->ensureStandardSectionsExist();

        $skills = Skill::all();
        $sections = ExamSection::with('skill')->orderBy('sequence_no')->get();
        $questionTypes = QuestionType::all();

        $testContext = null;
        $testId = $request->query('test_id');
        $sectionId = $request->query('section_id');

        if ($testId) {
            $targetSection = $sections->firstWhere('section_id', (int) $sectionId);
            $dbTest = Schema::hasTable('test_form') ? TestForm::with('blueprint')->find($testId) : null;

            if ($dbTest) {
                $examType = (string) ($dbTest->blueprint?->exam_type ?? TestForm::EXAM_TYPE_LR);
                $allowedCodes = $testFormService->allowedPartCodesFor($examType);
                $sections = $sections->whereIn('code', $allowedCodes)->values();
                $allowedSkillIds = $sections->pluck('skill_id')->unique()->values();
                if ($allowedSkillIds->isNotEmpty()) {
                    $skills = $skills->whereIn('skill_id', $allowedSkillIds)->values();
                }
                if (! $targetSection || ! $sections->contains('section_id', $targetSection->section_id)) {
                    $targetSection = $sections->first();
                }

                $testContext = [
                    'test_id' => $dbTest->test_form_id,
                    'test_title' => $dbTest->title,
                    'test_code' => $dbTest->test_code,
                    'exam_type' => $examType,
                    'section_id' => $targetSection ? $targetSection->section_id : null,
                    'skill_id' => $targetSection ? $targetSection->skill_id : null,
                    'section_name' => $targetSection ? $targetSection->name : null,
                ];
            } else {
                $examService = app(ExamSampleDataService::class);
                $test = $examService->findTest($testId);
                if ($test) {
                    $testContext = [
                        'test_id' => $test['id'],
                        'test_title' => $test['title'],
                        'test_code' => $test['code'],
                        'section_id' => $sectionId ? (int) $sectionId : null,
                        'skill_id' => $targetSection ? $targetSection->skill_id : null,
                        'section_name' => $targetSection ? $targetSection->name : null,
                    ];
                }
            }
        }

        return view('admin.questions.create', [
            'skills' => $skills,
            'sections' => $sections,
            'questionTypes' => $questionTypes,
            'testContext' => $testContext,
        ]);
    }

    /**
     * Thêm mới câu hỏi vào Ngân hàng câu hỏi (hỗ trợ upload Ảnh/Audio/Transcript/Đoạn văn & tự động gắn vào Đề thi nếu có test_id).
     */
    public function store(Request $request): RedirectResponse
    {
        $section = ExamSection::query()->find((int) $request->input('section_id'));
        $partCode = strtoupper((string) ($section?->code ?? 'PART5'));

        $isPart1 = $partCode === 'PART1';
        $isPart2 = $partCode === 'PART2';
        $isObjective4 = in_array($partCode, ['PART3', 'PART4', 'PART5', 'PART6', 'PART7'], true);
        $isProductive = str_starts_with($partCode, 'SPK_') || str_starts_with($partCode, 'WRITING_');

        $rules = [
            'content' => 'required|string|min:3',
            'section_id' => 'required|exists:exam_section,section_id',
            'question_type_id' => 'nullable|exists:question_type,question_type_id',
            'difficulty' => 'nullable|in:EASY,MEDIUM,HARD,UNSET',
            'points' => 'nullable|numeric|min:0.1|max:10',
            'status' => 'nullable|in:ACTIVE,PENDING,REPORTED,DRAFT',
            'explanation' => 'nullable|string',
            'stimulus_text' => 'nullable|string',
            'transcript' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a,webm|max:15360',
            'audio_url' => 'nullable|string|max:500',
            'test_id' => 'nullable|integer',
            'sequence_no' => 'nullable|integer|min:1',
        ];

        if ($isPart2) {
            $rules['opt_a'] = 'required|string';
            $rules['opt_b'] = 'required|string';
            $rules['opt_c'] = 'required|string';
            $rules['opt_d'] = 'nullable|string';
            $rules['correct_opt'] = 'nullable|in:A,B,C';
            $rules['correct_option'] = 'nullable|in:A,B,C';
        } elseif ($isObjective4) {
            $rules['opt_a'] = 'required|string';
            $rules['opt_b'] = 'required|string';
            $rules['opt_c'] = 'required|string';
            $rules['opt_d'] = 'required|string';
            $rules['correct_opt'] = 'nullable|in:A,B,C,D';
            $rules['correct_option'] = 'nullable|in:A,B,C,D';
        } elseif ($isPart1) {
            $rules['opt_a'] = 'nullable|string';
            $rules['opt_b'] = 'nullable|string';
            $rules['opt_c'] = 'nullable|string';
            $rules['opt_d'] = 'nullable|string';
            $rules['correct_opt'] = 'nullable|in:A,B,C,D';
            $rules['correct_option'] = 'nullable|in:A,B,C,D';
        } else {
            $rules['opt_a'] = 'nullable|string';
            $rules['opt_b'] = 'nullable|string';
            $rules['opt_c'] = 'nullable|string';
            $rules['opt_d'] = 'nullable|string';
            $rules['correct_opt'] = 'nullable|in:A,B,C,D';
            $rules['correct_option'] = 'nullable|in:A,B,C,D';
        }

        $validated = $request->validate($rules, [
            'content.required' => 'Vui lòng nhập nội dung câu hỏi.',
            'section_id.required' => 'Vui lòng chọn phần thi (Part / Task).',
            'opt_a.required' => 'Vui lòng nhập nội dung phương án A.',
            'opt_b.required' => 'Vui lòng nhập nội dung phương án B.',
            'opt_c.required' => 'Vui lòng nhập nội dung phương án C.',
            'opt_d.required' => 'Vui lòng nhập nội dung phương án D.',
            'image_file.image' => 'Tệp hình ảnh không hợp lệ (hỗ trợ JPG, PNG, GIF, WEBP).',
            'audio_file.mimes' => 'Tệp âm thanh không hợp lệ (hỗ trợ MP3, WAV, OGG, M4A, WEBM).',
        ]);

        $status = $request->input('action_type') === 'draft' ? 'DRAFT' : ($validated['status'] ?? 'ACTIVE');
        $questionTypeId = Question::resolveQuestionTypeIdForPart($partCode);
        $stimulusText = isset($validated['stimulus_text']) ? trim((string) $validated['stimulus_text']) : '';
        $transcript = isset($validated['transcript']) ? trim((string) $validated['transcript']) : '';

        $difficulty = $validated['difficulty'] ?? 'UNSET';
        if (empty($difficulty) || $difficulty === 'UNSET') {
            $sectionModel = ExamSection::query()->with('skill')->find((int) $validated['section_id']);
            $skillName = $sectionModel?->skill?->name ?? '';
            $difficulty = AiToeicService::assessToeicDifficulty(
                $skillName,
                $partCode,
                $validated['content'] ?? '',
                $stimulusText,
                [
                    'A' => $validated['opt_a'] ?? '',
                    'B' => $validated['opt_b'] ?? '',
                    'C' => $validated['opt_c'] ?? '',
                    'D' => $validated['opt_d'] ?? '',
                ]
            );
        }
        $correctOption = strtoupper((string) ($validated['correct_option'] ?? $validated['correct_opt'] ?? 'A'));
        if ($isPart2 && ! in_array($correctOption, ['A', 'B', 'C'], true)) {
            $correctOption = 'A';
        }

        // Xử lý lưu file Hình ảnh / Audio lên storage public (đường dẫn tương đối storage/...)
        $savedImagePath = null;
        if ($request->hasFile('image_file')) {
            $storedImg = $request->file('image_file')->store('questions/images', 'public');
            if (is_string($storedImg) && $storedImg !== '') {
                $savedImagePath = 'storage/'.ltrim($storedImg, '/');
            }
        }

        $savedAudioPath = null;
        if ($request->hasFile('audio_file')) {
            $storedAudio = $request->file('audio_file')->store('questions/audio', 'public');
            if (is_string($storedAudio) && $storedAudio !== '') {
                $savedAudioPath = 'storage/'.ltrim($storedAudio, '/');
            }
        } elseif (! empty($validated['audio_url'])) {
            $rawAudio = trim((string) $validated['audio_url']);
            $baseUrl = rtrim(url('/'), '/').'/';
            if (str_starts_with($rawAudio, $baseUrl)) {
                $rawAudio = substr($rawAudio, strlen($baseUrl));
            }
            $savedAudioPath = preg_match('/^https?:\/\//i', $rawAudio) === 1 ? $rawAudio : ltrim($rawAudio, '/');
        }

        $question = DB::transaction(function () use (
            $request,
            $validated,
            $status,
            $questionTypeId,
            $difficulty,
            $correctOption,
            $partCode,
            $isPart1,
            $isPart2,
            $isProductive,
            $savedImagePath,
            $savedAudioPath,
            $stimulusText,
            $transcript
        ): Question {
            $questionSetId = null;

            if ($savedImagePath !== null || $savedAudioPath !== null || $stimulusText !== '' || $transcript !== '') {
                $mediaType = $savedImagePath !== null
                    ? 'IMAGE'
                    : (($savedAudioPath !== null || $transcript !== '') ? 'AUDIO' : 'READING_PASSAGE');

                $qSet = QuestionSet::query()->create([
                    'title' => 'Ngữ cảnh '.$partCode,
                    'stimulus_text' => $stimulusText !== '' ? $stimulusText : null,
                    'media_url' => $savedImagePath,
                    'audio_url' => $savedAudioPath,
                    'media_type' => $mediaType,
                    'transcript' => $transcript !== '' ? $transcript : null,
                ]);
                $questionSetId = $qSet->question_set_id;
            }

            $question = new Question;
            $question->content = $validated['content'];
            $question->section_id = $validated['section_id'];
            $question->question_type_id = $questionTypeId;
            $question->question_set_id = $questionSetId;
            $question->difficulty = $difficulty;
            $question->points = $validated['points'] ?? 1.00;
            $question->status = $status;
            $question->explanation = $validated['explanation'] ?? null;
            $question->created_at = now();
            $question->save();

            if (! $isProductive) {
                $optionsData = [
                    'A' => $request->input('opt_a'),
                    'B' => $request->input('opt_b'),
                    'C' => $request->input('opt_c'),
                ];
                if (! $isPart2) {
                    $optionsData['D'] = $request->input('opt_d');
                }

                foreach ($optionsData as $lbl => $txt) {
                    $cleanTxt = trim((string) ($txt ?? ''));
                    if ($cleanTxt === '' && $isPart1) {
                        $cleanTxt = "Option {$lbl}";
                    }
                    if ($cleanTxt !== '') {
                        AnswerOption::create([
                            'question_id' => $question->question_id,
                            'label' => $lbl,
                            'content' => $cleanTxt,
                            'is_correct' => $lbl === $correctOption,
                        ]);
                    }
                }
            }

            return $question;
        });

        // Nếu tạo câu hỏi trong ngữ cảnh của một Đề thi cụ thể, tự động gắn vào đề thi đó
        if (! empty($validated['test_id']) && Schema::hasTable('test_form') && Schema::hasTable('test_form_question')) {
            $testForm = TestForm::query()->find((int) $validated['test_id']);
            if ($testForm) {
                $attachResult = app(TestFormService::class)->attachQuestions(
                    $testForm,
                    [$question->question_id],
                    ! empty($validated['sequence_no']) ? (int) $validated['sequence_no'] : null
                );

                if ($attachResult['attached'] > 0) {
                    return redirect()->route('admin.tests.show', $testForm->test_form_id)
                        ->with('success', "Đã tạo câu hỏi #Q-{$question->question_id} và gắn trực tiếp vào đề thi \"{$testForm->title}\" ({$testForm->test_code})!");
                }

                return redirect()->route('admin.tests.show', $testForm->test_form_id)
                    ->with('error', "Đã lưu câu hỏi #Q-{$question->question_id} vào Ngân hàng câu hỏi, nhưng không thể gắn vào đề \"{$testForm->title}\" do Part đã đủ chỉ tiêu hoặc không khớp kỹ năng đề.");
            }
        }

        $msg = $status === 'DRAFT'
            ? "Đã lưu bản nháp câu hỏi #Q-{$question->question_id} thành công!"
            : "Đã thêm và xuất bản câu hỏi #Q-{$question->question_id} thành công!";

        return redirect()->route('admin.questions.index')->with('success', $msg);
    }

    /**
     * Cập nhật thông tin câu hỏi (bao gồm cả 4 đáp án A, B, C, D nếu truyền lên).
     */
    public function update(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|min:5',
            'section_id' => 'required|exists:exam_section,section_id',
            'difficulty' => 'required|in:EASY,MEDIUM,HARD,UNSET',
            'points' => 'nullable|numeric|min:0.5|max:10',
            'status' => 'required|in:ACTIVE,PENDING,REPORTED,DRAFT',
            'explanation' => 'nullable|string',
            'transcript' => 'nullable|string',
            'audio_url' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a,webm|max:15360',
            'opt_a' => 'nullable|string',
            'opt_b' => 'nullable|string',
            'opt_c' => 'nullable|string',
            'opt_d' => 'nullable|string',
            'correct_option' => 'nullable|in:A,B,C,D',
        ]);

        $savedImagePath = null;
        if ($request->hasFile('image_file')) {
            $storedImg = $request->file('image_file')->store('questions/images', 'public');
            if (is_string($storedImg) && $storedImg !== '') {
                $savedImagePath = 'storage/'.ltrim($storedImg, '/');
            }
        }

        $savedAudioPath = null;
        if ($request->hasFile('audio_file')) {
            $storedAudio = $request->file('audio_file')->store('questions/audio', 'public');
            if (is_string($storedAudio) && $storedAudio !== '') {
                $savedAudioPath = 'storage/'.ltrim($storedAudio, '/');
            }
        } elseif (! empty($validated['audio_url'])) {
            $rawAudio = trim((string) $validated['audio_url']);
            $baseUrl = rtrim(url('/'), '/').'/';
            if (str_starts_with($rawAudio, $baseUrl)) {
                $rawAudio = substr($rawAudio, strlen($baseUrl));
            }
            $savedAudioPath = preg_match('/^https?:\/\//i', $rawAudio) === 1 ? $rawAudio : ltrim($rawAudio, '/');
        }

        DB::transaction(function () use ($question, $validated, $request, $savedImagePath, $savedAudioPath): void {
            $question->content = $validated['content'];
            $question->section_id = $validated['section_id'];
            $newSection = ExamSection::query()->find((int) $validated['section_id']);
            if ($newSection) {
                $question->question_type_id = Question::resolveQuestionTypeIdForPart((string) $newSection->code);
            }
            $question->difficulty = $validated['difficulty'];
            $question->points = $validated['points'] ?? $question->points;
            $question->status = $validated['status'];
            $question->explanation = $validated['explanation'] ?? $question->explanation;

            $transcriptProvided = array_key_exists('transcript', $validated) && trim((string) ($validated['transcript'] ?? '')) !== '';
            if ($savedImagePath !== null || $savedAudioPath !== null || $transcriptProvided) {
                $qSet = $question->questionSet;
                if (! $qSet) {
                    $qSet = QuestionSet::query()->create([
                        'title' => 'Ngữ cảnh #Q-'.$question->question_id,
                        'media_url' => $savedImagePath,
                        'audio_url' => $savedAudioPath,
                        'media_type' => $savedImagePath !== null ? 'IMAGE' : 'AUDIO',
                        'transcript' => $transcriptProvided ? trim((string) $validated['transcript']) : null,
                    ]);
                    $question->question_set_id = $qSet->question_set_id;
                } else {
                    if ($savedImagePath !== null) {
                        $qSet->media_url = $savedImagePath;
                        $qSet->media_type = 'IMAGE';
                    }
                    if ($savedAudioPath !== null) {
                        $qSet->audio_url = $savedAudioPath;
                        if (empty($qSet->media_type)) {
                            $qSet->media_type = 'AUDIO';
                        }
                    }
                    if ($transcriptProvided) {
                        $qSet->transcript = trim((string) $validated['transcript']);
                    }
                    $qSet->save();
                }
            }

            $question->save();

            $optionsInput = [
                'A' => $request->input('opt_a'),
                'B' => $request->input('opt_b'),
                'C' => $request->input('opt_c'),
                'D' => $request->input('opt_d'),
            ];

            $hasOptions = collect($optionsInput)->filter(fn ($v) => $v !== null && trim((string) $v) !== '')->isNotEmpty();
            if ($hasOptions) {
                $correctOpt = $validated['correct_option'] ?? 'A';
                $question->options()->delete();
                foreach ($optionsInput as $lbl => $txt) {
                    if ($txt !== null && trim((string) $txt) !== '') {
                        AnswerOption::create([
                            'question_id' => $question->question_id,
                            'label' => $lbl,
                            'content' => trim((string) $txt),
                            'is_correct' => $lbl === $correctOpt,
                        ]);
                    }
                }
            }
        });

        if ($request->boolean('redirect_back')) {
            return redirect()->back()
                ->with('success', "Đã cập nhật câu hỏi #Q-{$question->question_id} thành công!");
        }

        return redirect()->route('admin.questions.index')
            ->with('success', "Đã cập nhật câu hỏi #Q-{$question->question_id} thành công!");
    }

    /**
     * Phê duyệt nhanh hoặc chuyển trạng thái câu hỏi.
     */
    public function toggleStatus(Request $request, Question $question): RedirectResponse
    {
        $newStatus = $request->input('status', 'ACTIVE');
        $question->status = $newStatus;
        $question->save();

        return redirect()->back()->with('success', "Đã chuyển trạng thái câu hỏi #Q-{$question->question_id} sang '{$question->status_label}'.");
    }

    /**
     * Xóa câu hỏi khỏi ngân hàng.
     */
    public function destroy(Request $request, Question $question): RedirectResponse
    {
        $qId = $question->question_id;
        DB::transaction(function () use ($question) {
            if (Schema::hasTable('test_form_question')) {
                DB::table('test_form_question')->where('question_id', $question->question_id)->delete();
            }
            $question->options()->delete();
            $question->delete();
        });

        $recentIds = session('recent_imported_question_ids', []);
        if (is_array($recentIds) && in_array($qId, $recentIds)) {
            session()->put('recent_imported_question_ids', array_values(array_diff($recentIds, [$qId])));
        }

        if ($request->boolean('redirect_back')) {
            return redirect()->back()
                ->with('success', "Đã xóa câu hỏi #Q-{$qId} khỏi danh sách.");
        }

        return redirect()->route('admin.questions.index')
            ->with('success', "Đã xóa vĩnh viễn câu hỏi #Q-{$qId} khỏi hệ thống.");
    }

    /**
     * Xử lý thao tác hàng loạt (Bulk Action): Phê duyệt, Xóa, hoặc Gắn vào Đề thi.
     * Hỗ trợ chế độ select_all=1 để chọn toàn bộ câu hỏi khớp bộ lọc hiện tại (không bị giới hạn theo trang).
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $action = $request->input('action');
        $selectAll = $request->boolean('select_all');

        if ($selectAll) {
            $filters = $this->extractFilterParams($request, 'filter_');
            $query = Question::query();
            $this->applyQuestionFilters($query, $filters);

            $excludedIds = array_values(array_filter(
                array_map('intval', (array) $request->input('excluded_ids', [])),
                static fn (int $id): bool => $id > 0
            ));
            if (! empty($excludedIds)) {
                $query->whereNotIn('question_id', $excludedIds);
            }

            $ids = $query->orderBy('question_id')->pluck('question_id')->map(static fn ($id): int => (int) $id)->all();
        } else {
            $rawIds = $request->input('question_ids', []);
            $ids = is_array($rawIds)
                ? array_values(array_unique(array_filter(array_map('intval', $rawIds), static fn (int $id): bool => $id > 0)))
                : [];
        }

        if (empty($ids)) {
            return redirect()->back(302, [], route('admin.questions.index'))
                ->with('error', 'Vui lòng chọn ít nhất một câu hỏi để thực hiện thao tác.');
        }

        if ($action === 'approve') {
            foreach (array_chunk($ids, 500) as $chunk) {
                Question::whereIn('question_id', $chunk)->update(['status' => 'ACTIVE']);
            }

            return redirect()->back(302, [], route('admin.questions.index'))
                ->with('success', 'Đã phê duyệt nhanh thành công '.count($ids).' câu hỏi.');
        }

        if ($action === 'delete') {
            DB::transaction(function () use ($ids): void {
                foreach (array_chunk($ids, 500) as $chunk) {
                    if (Schema::hasTable('test_form_question')) {
                        DB::table('test_form_question')->whereIn('question_id', $chunk)->delete();
                    }
                    AnswerOption::whereIn('question_id', $chunk)->delete();
                    Question::whereIn('question_id', $chunk)->delete();
                }
            });

            return redirect()->back(302, [], route('admin.questions.index'))
                ->with('success', 'Đã xóa thành công '.count($ids).' câu hỏi đã chọn.');
        }

        if ($action === 'assign') {
            $testId = (int) $request->input('test_id', 0);
            if ($testId > 0 && Schema::hasTable('test_form') && Schema::hasTable('test_form_question')) {
                $testForm = TestForm::query()->with('blueprint')->find($testId);
                if (! $testForm) {
                    return redirect()->back(302, [], route('admin.questions.index'))
                        ->with('error', 'Không tìm thấy đề thi mục tiêu để gắn câu hỏi.');
                }

                $autoActivateDrafts = $request->has('auto_activate_drafts')
                    ? $request->boolean('auto_activate_drafts')
                    : true;

                $result = app(TestFormService::class)->attachQuestions($testForm, $ids, null, $autoActivateDrafts);
                $notes = [];
                if (($result['activated'] ?? 0) > 0) {
                    $notes[] = "tự động xuất bản {$result['activated']} câu bản nháp sang ACTIVE";
                }
                if (($result['skipped'] ?? 0) > 0) {
                    $notes[] = "bỏ qua {$result['skipped']} câu trùng lặp";
                }
                if (($result['rejected_skill'] ?? 0) > 0) {
                    $notes[] = "chặn {$result['rejected_skill']} câu khác kỹ năng/Part của đề";
                }
                if (($result['rejected_limit'] ?? 0) > 0) {
                    $notes[] = "chặn {$result['rejected_limit']} câu do vượt chỉ tiêu Part/đề";
                }

                if (($result['attached'] ?? 0) === 0) {
                    $reason = ! empty($notes) ? ucfirst(implode('; ', $notes)) : 'không có câu hỏi phù hợp';

                    return redirect()->back(302, [], route('admin.questions.index'))
                        ->with('error', "Không thể gắn câu hỏi nào vào đề thi \"{$testForm->title}\" ({$testForm->test_code}): {$reason}.");
                }

                $msg = "Đã gắn thêm {$result['attached']} câu hỏi vào đề thi \"{$testForm->title}\" ({$testForm->test_code}).";
                if (! empty($notes)) {
                    $msg .= ' ('.ucfirst(implode('; ', $notes)).').';
                }

                if ($request->boolean('redirect_to_test')) {
                    return redirect()->route('admin.tests.show', $testForm->test_form_id)->with('success', $msg);
                }

                return redirect()->back(302, [], route('admin.questions.index'))->with('success', $msg);
            }

            return redirect()->back(302, [], route('admin.questions.index'))
                ->with('error', 'Vui lòng chọn đề thi mục tiêu hợp lệ.');
        }

        return redirect()->back(302, [], route('admin.questions.index'));
    }

    /**
     * Xuất danh sách câu hỏi ra tệp CSV (Excel UTF-8 BOM).
     */
    public function export(): StreamedResponse
    {
        $fileName = 'ngan_hang_cau_hoi_studyprep_'.date('Y_m_d_His').'.csv';
        $questions = Question::with(['section.skill', 'options'])->orderBy('question_id')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($questions) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($output, [
                'Mã câu hỏi',
                'Phần thi',
                'Kỹ năng',
                'Mức độ khó',
                'Điểm số',
                'Nội dung câu hỏi',
                'Giải thích / Đáp án',
                'Trạng thái',
            ]);

            foreach ($questions as $q) {
                fputcsv($output, [
                    $q->question_code_display,
                    $q->part_badge,
                    $q->skill_display,
                    $q->difficulty,
                    $q->points,
                    $q->content,
                    $q->explanation,
                    $q->status_label,
                ]);
            }

            fclose($output);
        }, 200, $headers);
    }

    /**
     * Tải file Word (.docx) mẫu chuẩn cấu trúc nhập câu hỏi hàng loạt (hỗ trợ cả 4 kỹ năng: all, listening, reading, speaking, writing).
     */
    public function sampleDocx(Request $request, WordQuestionImportService $wordService): Response
    {
        $skill = strtolower(trim((string) $request->query('skill', 'all')));
        if (! in_array($skill, ['all', 'listening', 'reading', 'speaking', 'writing'], true)) {
            $skill = 'all';
        }

        $binary = $wordService->generateSampleDocxBinary($skill);
        $suffix = $skill === 'all' ? 'toeic' : 'toeic_'.$skill;

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => "attachment; filename=\"mau_nhap_cau_hoi_{$suffix}_studyprep.docx\"",
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    /**
     * Màn hình Thêm câu hỏi TOEIC hàng loạt (Bulk Import Studio).
     */
    public function importView(): View
    {
        app(TestFormService::class)->ensureStandardSectionsExist();

        $skills = Skill::all();
        $sections = ExamSection::with('skill')->orderBy('sequence_no')->get();
        $availableTests = Schema::hasTable('test_form')
            ? TestForm::query()->with('blueprint')->orderByDesc('test_form_id')->get()
            : collect();

        $recentIds = session('recent_imported_question_ids', []);
        $importedQuestions = (! empty($recentIds) && is_array($recentIds))
            ? Question::query()
                ->with(['section.skill', 'questionType', 'questionSet.questions.options', 'options'])
                ->whereIn('question_id', $recentIds)
                ->orderBy('question_id')
                ->get()
            : collect();

        return view('admin.questions.bulk-import', [
            'skills' => $skills,
            'sections' => $sections,
            'availableTests' => $availableTests,
            'importedQuestions' => $importedQuestions,
        ]);
    }

    /**
     * Nhập danh sách câu hỏi từ file Word (.docx), Excel, CSV, JSON hoặc từ Bulk Import Studio.
     */
    public function import(Request $request, WordQuestionImportService $wordService): RedirectResponse
    {
        $publishStatus = $request->input('publish_status', 'active') === 'draft' ? 'DRAFT' : 'ACTIVE';
        $targetTestId = $request->filled('target_test_id') ? (int) $request->input('target_test_id') : null;
        $importScope = strtoupper(trim((string) $request->input('import_scope', 'AUTO')));
        $defaultSectionId = $request->filled('section_id') ? (int) $request->input('section_id') : null;

        if ($defaultSectionId === null && in_array($importScope, WordQuestionImportService::FIXED_PART_CODES, true)) {
            $resolvedSectionId = ExamSection::query()->where('code', $importScope)->value('section_id');
            $defaultSectionId = $resolvedSectionId ? (int) $resolvedSectionId : null;
        }

        // 1. Nếu người dùng có tải lên tệp (.docx, .csv, .txt, .json, .xlsx, .xls)
        if ($request->hasFile('import_file')) {
            $request->validate([
                'import_file' => 'required|file|mimes:docx,csv,txt,json,xlsx,xls|max:15360',
            ], [
                'import_file.required' => 'Vui lòng chọn tệp để nhập.',
                'import_file.mimes' => 'Chỉ hỗ trợ tệp định dạng .docx (Word), .csv, .txt, .json, .xlsx.',
            ]);

            $file = $request->file('import_file');
            $parsed = $wordService->parseUploadedFile($file, $importScope);

            if (! empty($parsed)) {
                $importedIds = $wordService->persistQuestionsAndReturnIds(
                    $parsed,
                    $publishStatus,
                    $defaultSectionId,
                    $targetTestId && $targetTestId > 0 ? $targetTestId : null
                );

                session()->put('recent_imported_question_ids', $importedIds);
                $importedCount = count($importedIds);
                $attachResult = $wordService->getLastAttachResult();
                $attachNote = '';
                if ($targetTestId && $targetTestId > 0 && is_array($attachResult)) {
                    $attached = (int) ($attachResult['attached'] ?? 0);
                    $attachNote = " Đã gắn {$attached}/{$importedCount} câu vào đề mục tiêu";
                    $warnings = [];
                    if (($attachResult['rejected_limit'] ?? 0) > 0) {
                        $warnings[] = "vượt chỉ tiêu Part/đề: {$attachResult['rejected_limit']} câu";
                    }
                    if (($attachResult['rejected_skill'] ?? 0) > 0) {
                        $warnings[] = "khác kỹ năng đề: {$attachResult['rejected_skill']} câu";
                    }
                    if (($attachResult['skipped'] ?? 0) > 0) {
                        $warnings[] = "trùng lặp: {$attachResult['skipped']} câu";
                    }
                    $attachNote .= ! empty($warnings) ? ' (chưa gắn: '.implode(', ', $warnings).').' : '.';
                }

                if ($request->boolean('return_to_import_view')) {
                    return redirect()->route('admin.questions.bulk-import')
                        ->with('success', "Đã đọc tệp \"{$file->getClientOriginalName()}\" và nhập thành công {$importedCount} câu hỏi!{$attachNote} Bạn có thể Xem trước, Chỉnh sửa hoặc Xóa từng câu hỏi ngay bên dưới.");
                }

                return redirect()->route('admin.questions.index')
                    ->with('success', "Đã đọc tệp \"{$file->getClientOriginalName()}\" và nhập thành công {$importedCount} câu hỏi vào Ngân hàng câu hỏi!{$attachNote}");
            }
        }

        // 2. Trường hợp nạp từ giao diện Bulk Import Studio (không đính kèm file riêng)
        if ($request->input('bulk_confirm') || $request->input('action_type') === 'bulk_import') {
            $rawStatus = (string) $request->input('publish_status', 'draft');
            $studioStatus = in_array(strtolower($rawStatus), ['live', 'active', 'published'], true) ? 'ACTIVE' : 'DRAFT';

            $sectionId = $defaultSectionId ?: (int) $request->input('section_id', 0);
            $section = $sectionId > 0 ? ExamSection::find($sectionId) : ExamSection::first();
            $sectionId = $section ? (int) $section->section_id : ($sectionId > 0 ? $sectionId : 5);
            $partCode = $section ? (string) $section->code : 'PART5';
            $resolvedQuestionTypeId = Question::resolveQuestionTypeIdForPart($partCode);

            $sampleQuestions = [
                [
                    'content' => 'Ms. Tanaka requested that all department heads submit their budget proposals before Friday.',
                    'difficulty' => 'MEDIUM',
                    'points' => 1.00,
                    'status' => $studioStatus,
                    'explanation' => 'Vị trí cần điền đứng trước mốc thời gian Friday, chọn giới từ "before".',
                    'options' => [
                        ['label' => 'A', 'content' => 'until', 'is_correct' => false],
                        ['label' => 'B', 'content' => 'before', 'is_correct' => true],
                        ['label' => 'C', 'content' => 'since', 'is_correct' => false],
                        ['label' => 'D', 'content' => 'toward', 'is_correct' => false],
                    ],
                ],
                [
                    'content' => 'Photographs Part 1: [A] A man is holding a briefcase. [B] A man is boarding a train.',
                    'difficulty' => 'EASY',
                    'points' => 1.00,
                    'status' => $studioStatus,
                    'explanation' => 'Hình ảnh miêu tả người đàn ông cầm cặp tài liệu (holding a briefcase).',
                    'options' => [
                        ['label' => 'A', 'content' => 'A man is holding a briefcase.', 'is_correct' => true],
                        ['label' => 'B', 'content' => 'A man is boarding a train.', 'is_correct' => false],
                        ['label' => 'C', 'content' => 'A woman is typing on a laptop.', 'is_correct' => false],
                        ['label' => 'D', 'content' => 'They are waiting in line.', 'is_correct' => false],
                    ],
                ],
            ];

            $createdIds = [];
            DB::transaction(function () use ($sampleQuestions, $sectionId, $resolvedQuestionTypeId, &$createdIds) {
                foreach ($sampleQuestions as $data) {
                    $q = Question::create([
                        'section_id' => $sectionId,
                        'question_type_id' => $resolvedQuestionTypeId,
                        'content' => $data['content'],
                        'difficulty' => $data['difficulty'],
                        'points' => $data['points'],
                        'status' => $data['status'],
                        'explanation' => $data['explanation'],
                        'created_at' => now(),
                    ]);

                    foreach ($data['options'] as $opt) {
                        AnswerOption::create([
                            'question_id' => $q->question_id,
                            'label' => $opt['label'],
                            'content' => $opt['content'],
                            'is_correct' => $opt['is_correct'],
                        ]);
                    }

                    $createdIds[] = $q->question_id;
                }
            });

            if ($targetTestId && $targetTestId > 0 && Schema::hasTable('test_form')) {
                $testForm = TestForm::query()->find($targetTestId);
                if ($testForm) {
                    app(TestFormService::class)->attachQuestions($testForm, $createdIds);
                }
            }

            session()->put('recent_imported_question_ids', $createdIds);

            $count = $request->input('selected_count', count($createdIds));

            if ($request->boolean('return_to_import_view')) {
                return redirect()->route('admin.questions.bulk-import')
                    ->with('success', 'Đã nhập thành công '.count($createdIds).' câu hỏi TOEIC! Bạn có thể Xem trước, Chỉnh sửa hoặc Xóa từng câu hỏi ngay bên dưới.');
            }

            $targetTestName = null;
            if ($targetTestId && $targetTestId > 0 && Schema::hasTable('test_form')) {
                $targetTestName = TestForm::query()->where('test_id', $targetTestId)->value('title');
            }
            $targetSuffix = $targetTestName ? " (Bộ đề: {$targetTestName})" : '';

            return redirect()->route('admin.questions.index')
                ->with('success', "Đã nạp thành công {$count} câu hỏi TOEIC vào Ngân hàng câu hỏi{$targetSuffix}!");
        }

        // 3. Trường hợp tải file trực tiếp qua modal nhưng file rỗng / chưa chọn
        $request->validate([
            'import_file' => 'required|file|mimes:docx,csv,txt,json,xlsx,xls|max:15360',
        ], [
            'import_file.required' => 'Vui lòng chọn tệp để nhập.',
        ]);

        return redirect()->route('admin.questions.index')
            ->with('success', 'Đã tải lên tệp và hoàn tất đồng bộ danh sách câu hỏi vào Ngân hàng thành công.');
    }

    /**
     * Màn hình Tạo nhóm câu hỏi TOEIC (Stimulus & Sub-questions).
     */
    public function createGroup(): View
    {
        $skills = Skill::all();
        $sections = ExamSection::with('skill')->orderBy('sequence_no')->get();
        $questionTypes = QuestionType::all();

        return view('admin.questions.create-group', [
            'skills' => $skills,
            'sections' => $sections,
            'questionTypes' => $questionTypes,
        ]);
    }

    /**
     * Lưu nhóm câu hỏi (QuestionSet) và các câu hỏi con vào CSDL.
     */
    public function storeGroup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group_title' => 'required|string|min:5',
            'section_id' => 'required|exists:exam_section,section_id',
            'test_pack' => 'nullable|string',
            'passage_1_title' => 'nullable|string',
            'passage_1_content' => 'nullable|string',
            'passage_2_from' => 'nullable|string',
            'passage_2_to' => 'nullable|string',
            'passage_2_content' => 'nullable|string',
        ], [
            'group_title.required' => 'Vui lòng nhập tiêu đề nhóm câu hỏi.',
            'section_id.required' => 'Vui lòng chọn phần thi TOEIC Part.',
        ]);

        $status = $request->input('action_type') === 'draft' ? 'DRAFT' : 'ACTIVE';

        // Ghép ngữ cảnh đoạn văn chung (Stimulus text)
        $stimulusParts = [];
        if (! empty($validated['passage_1_title']) || ! empty($validated['passage_1_content'])) {
            $stimulusParts[] = '### '.($validated['passage_1_title'] ?? 'Passage 1')."\n".($validated['passage_1_content'] ?? '');
        }
        if (! empty($validated['passage_2_content'])) {
            $header = '### Email Correspondence';
            if (! empty($validated['passage_2_from']) || ! empty($validated['passage_2_to'])) {
                $header .= " (From: {$validated['passage_2_from']} | To: {$validated['passage_2_to']})";
            }
            $stimulusParts[] = $header."\n".$validated['passage_2_content'];
        }

        $stimulusText = implode("\n\n---\n\n", $stimulusParts);
        if (empty($stimulusText)) {
            $stimulusText = $request->input('stimulus_text', 'Default reading passage content.');
        }

        // 1. Tạo bản ghi QuestionSet
        $qSet = new QuestionSet;
        $qSet->title = $validated['group_title'];
        $qSet->stimulus_text = $stimulusText;
        $qSet->media_type = 'READING_PASSAGE';
        $qSet->save();

        // 2. Tạo 5 câu hỏi con (Sub-questions 191 - 195)
        $subQuestions = $request->input('sub_questions');
        if (empty($subQuestions) || ! is_array($subQuestions)) {
            $subQuestions = [
                1 => [
                    'content' => 'What is suggested about BrightTech Solutions in the notice?',
                    'difficulty' => 'HARD',
                    'explanation' => 'Đoạn 1 nêu rõ công ty đang tìm kiếm Senior Developer để dẫn dắt đội ngũ tại Seattle, cho thấy công ty đang mở rộng nhân sự kỹ thuật. Đáp án B chuẩn xác.',
                    'correct' => 'B',
                    'options' => [
                        'A' => 'It recently relocated its operations to Seattle.',
                        'B' => 'It is actively expanding its engineering staff.',
                        'C' => 'It specializes in producing computer hardware.',
                        'D' => 'It requires applicants to hold a master\'s degree.',
                    ],
                ],
                2 => [
                    'content' => 'According to the notice, what is a requirement for applicants?',
                    'difficulty' => 'MEDIUM',
                    'explanation' => 'Trong thông báo yêu cầu ứng viên có ít nhất 7 năm kinh nghiệm.',
                    'correct' => 'A',
                    'options' => [
                        'A' => 'At least 7 years of professional web development experience.',
                        'B' => 'Previous employment at a venture-backed cloud consulting firm.',
                        'C' => 'Willingness to relocate to Portland within three months.',
                        'D' => 'A portfolio showcasing at least three published mobile games.',
                    ],
                ],
                3 => [
                    'content' => 'What problem does Ms. Sanders mention in her email?',
                    'difficulty' => 'MEDIUM',
                    'explanation' => 'Email đề cập cô ấy bận tham gia hội thảo vào ngày 24 tháng 10.',
                    'correct' => 'C',
                    'options' => [
                        'A' => 'She erased her digital portfolio files during transit.',
                        'B' => 'Her flight to Seattle was unexpectedly cancelled.',
                        'C' => 'She is scheduled to attend a conference on October 24.',
                        'D' => 'She needs to submit her application before October 15.',
                    ],
                ],
                4 => [
                    'content' => 'When will Ms. Sanders most likely participate in an interview with BrightTech if agreed to her proposal?',
                    'difficulty' => 'HARD',
                    'explanation' => 'Cô ấy xin dời lịch sau ngày 25, thứ Hai ngày 28 tháng 10 là thời điểm phù hợp nhất.',
                    'correct' => 'D',
                    'options' => [
                        'A' => 'On Tuesday, October 15',
                        'B' => 'On Thursday, October 24',
                        'C' => 'On Friday, October 25',
                        'D' => 'On Monday, October 28',
                    ],
                ],
                5 => [
                    'content' => 'In the email, the word "accommodate" in paragraph 2 is closest in meaning to:',
                    'difficulty' => 'MEDIUM',
                    'explanation' => 'Trong ngữ cảnh thu xếp lịch trình, từ "accommodate" đồng nghĩa với "adjust".',
                    'correct' => 'A',
                    'options' => [
                        'A' => 'adjust (dàn xếp / điều chỉnh thích ứng)',
                        'B' => 'provide lodging for (cung cấp chỗ ở)',
                        'C' => 'invest in (đầu tư vào)',
                        'D' => 'furnish (trang bị đồ đạc)',
                    ],
                ],
            ];
        }

        $sectionForGroup = ExamSection::query()->with('skill')->find((int) $validated['section_id']);
        $groupQuestionTypeId = $sectionForGroup
            ? Question::resolveQuestionTypeIdForPart((string) $sectionForGroup->code)
            : 1;

        if ($sectionForGroup && in_array(strtoupper((string) $sectionForGroup->code), ['PART1', 'PART2', 'PART3', 'PART4'], true)) {
            $qSet->media_type = 'AUDIO';
            $qSet->save();
        }

        $createdCount = 0;
        foreach ($subQuestions as $idx => $qData) {
            $subDifficulty = $qData['difficulty'] ?? null;
            if (empty($subDifficulty) || $subDifficulty === 'UNSET') {
                $subDifficulty = AiToeicService::assessToeicDifficulty(
                    $sectionForGroup?->skill?->name ?? 'reading',
                    (string) ($sectionForGroup?->code ?? 'PART7'),
                    $qData['content'] ?? '',
                    $stimulusText,
                    is_array($qData['options'] ?? null) ? $qData['options'] : []
                );
            }

            $q = Question::query()->create([
                'section_id' => $validated['section_id'],
                'question_type_id' => $groupQuestionTypeId,
                'question_set_id' => $qSet->question_set_id,
                'content' => $qData['content'] ?? "Câu hỏi con #{$idx}",
                'difficulty' => $subDifficulty,
                'points' => '1.00',
                'status' => $status,
                'explanation' => $qData['explanation'] ?? null,
                'created_at' => now(),
            ]);

            $correctLetter = $qData['correct'] ?? 'B';
            $options = $qData['options'] ?? [];
            foreach ($options as $lbl => $txt) {
                if ($txt !== null && trim($txt) !== '') {
                    AnswerOption::create([
                        'question_id' => $q->question_id,
                        'label' => $lbl,
                        'content' => $txt,
                        'is_correct' => ($lbl === $correctLetter),
                    ]);
                }
            }
            $createdCount++;
        }

        $msg = $status === 'DRAFT'
            ? "Đã lưu bản nháp nhóm câu hỏi #GRP-{$qSet->question_set_id} gồm {$createdCount} câu hỏi con thành công!"
            : "Đã xuất bản thành công nhóm câu hỏi #GRP-{$qSet->question_set_id} ({$qSet->title}) vào Ngân hàng câu hỏi!";

        return redirect()->route('admin.questions.index')->with('success', $msg);
    }

    /**
     * API Endpoint: Sinh file audio MP3 tự động từ văn bản/transcript cho TOEIC Listening
     * POST /api/admin/questions/generate-tts
     */
    public function generateTts(Request $request, TextToSpeechService $ttsService): JsonResponse
    {
        $validated = $request->validate([
            'question_id' => 'nullable|integer',
            'question_set_id' => 'nullable|integer',
            'text' => 'nullable|string',
            'transcript' => 'nullable|string',
            'part_number' => 'nullable|integer|min:1|max:4',
            'custom_filename' => 'nullable|string|max:100',
            'lang' => 'nullable|string|max:10',
        ]);

        $text = $validated['text'] ?? $validated['transcript'] ?? null;
        $questionId = ! empty($validated['question_id']) ? (int) $validated['question_id'] : null;
        $questionSetId = ! empty($validated['question_set_id']) ? (int) $validated['question_set_id'] : null;
        $partNumber = ! empty($validated['part_number']) ? (int) $validated['part_number'] : null;
        $customFilename = $validated['custom_filename'] ?? null;
        $lang = $validated['lang'] ?? 'en';

        if (! $text && ! $questionId && ! $questionSetId) {
            return response()->json([
                'success' => false,
                'message' => 'Cần cung cấp ít nhất một trong các trường: question_id, question_set_id, hoặc text/transcript.',
            ], 422);
        }

        try {
            if ($questionId || $questionSetId) {
                $result = $ttsService->generateAndSaveToDatabase($questionId, $questionSetId, $text, $partNumber);
            } else {
                $result = $ttsService->generateAudio($text, $partNumber ?? 1, $customFilename, $lang);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sinh file audio MP3 thành công.',
                'audio_url' => $result['audio_url'],
                'file_path' => $result['file_path'],
                'file_size' => $result['file_size'],
                'duration_estimate' => $result['duration_estimate'] ?? 0,
                'part_number' => $result['part_number'] ?? $partNumber ?? 1,
                'question_id' => $result['question_id'] ?? $questionId,
                'question_set_id' => $result['question_set_id'] ?? $questionSetId,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi sinh audio: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API Endpoint: Sinh file audio MP3 theo lô an toàn (tối đa 5 nhóm mỗi lần gọi, nghỉ 300ms chống lỗi HTTP 429).
     * POST /api/admin/questions/batch-generate-tts
     */
    public function batchGenerateTts(Request $request, TextToSpeechService $ttsService): JsonResponse
    {
        $validated = $request->validate([
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'integer|min:1',
        ], [
            'question_ids.required' => 'Vui lòng cung cấp danh sách question_ids cần sinh audio.',
            'question_ids.array' => 'question_ids phải là một mảng ID.',
            'question_ids.min' => 'Cần ít nhất 1 câu hỏi để sinh audio.',
        ]);

        $summary = $ttsService->generateBatchForQuestions($validated['question_ids'], 5);

        return response()->json([
            'success' => $summary['failed'] === 0 || $summary['succeeded'] > 0,
            'message' => "Đã xử lý {$summary['processed']} nhóm: {$summary['succeeded']} thành công, {$summary['failed']} thất bại.",
            'processed' => $summary['processed'],
            'succeeded' => $summary['succeeded'],
            'failed' => $summary['failed'],
            'results' => $summary['results'],
        ], 200);
    }
}
