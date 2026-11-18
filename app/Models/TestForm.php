<?php

namespace App\Models;

use App\Services\ExamSampleDataService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'blueprint_id',
    'title',
    'generation_seed',
    'created_at',
])]
class TestForm extends Model
{
    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';

    public const STATUS_EMPTY = 'EMPTY';

    public const STATUS_INVALID = 'INVALID';

    public const EXAM_TYPE_LR = 'TOEIC_LR';

    public const EXAM_TYPE_SW = 'TOEIC_SW';

    public const EXAM_TYPE_LISTENING = 'TOEIC_LISTENING';

    public const EXAM_TYPE_READING = 'TOEIC_READING';

    public const EXAM_TYPE_SPEAKING = 'TOEIC_SPEAKING';

    public const EXAM_TYPE_WRITING = 'TOEIC_WRITING';

    protected $table = 'test_form';

    protected $primaryKey = 'test_form_id';

    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (TestForm $testForm): void {
            if ($testForm->created_at === null) {
                $testForm->created_at = now();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return list<string>
     */
    public static function examTypes(): array
    {
        return [
            self::EXAM_TYPE_LR,
            self::EXAM_TYPE_LISTENING,
            self::EXAM_TYPE_READING,
            self::EXAM_TYPE_SPEAKING,
            self::EXAM_TYPE_WRITING,
            self::EXAM_TYPE_SW,
        ];
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_COMPLETED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_EMPTY,
            self::STATUS_INVALID,
        ];
    }

    /**
     * Số câu hỏi chuẩn của một loại đề theo chuẩn TOEIC ETS
     * (L&R: 200, Listening: 100, Reading: 100, Speaking: 11, Writing: 8, S&W: 19).
     */
    public static function targetQuestionsFor(?string $examType): int
    {
        $blueprint = ExamSampleDataService::BLUEPRINTS[$examType] ?? ExamSampleDataService::BLUEPRINTS[self::EXAM_TYPE_LR];

        return (int) $blueprint['total_questions'];
    }

    /**
     * Bản đồ chỉ tiêu từng Part của một loại đề: ['PART1' => 6, 'PART2' => 25, ...].
     *
     * @return array<string, int>
     */
    public static function partTargetsFor(?string $examType): array
    {
        $blueprint = ExamSampleDataService::BLUEPRINTS[$examType] ?? ExamSampleDataService::BLUEPRINTS[self::EXAM_TYPE_LR];
        $targets = [];

        foreach ($blueprint['skills'] as $skill) {
            foreach ($skill['parts'] as $part) {
                $targets[strtoupper((string) $part['code'])] = (int) $part['target_count'];
            }
        }

        return $targets;
    }

    /**
     * Danh sách mã Part hợp lệ của một loại đề thi.
     *
     * @return list<string>
     */
    public static function allowedPartCodesFor(?string $examType): array
    {
        return array_keys(self::partTargetsFor($examType));
    }

    /**
     * Đánh giá chi tiết tính hợp lệ & tiến độ cấu trúc từng Part cho một đề thi
     * dựa trên tập câu hỏi ACTIVE theo từng mã Part.
     *
     * @param  array<string, int>  $activeCountsByPartCode
     * @return array{
     *     valid_count: int,
     *     wrong_skill_count: int,
     *     total_active_count: int,
     *     target_questions: int,
     *     exceeded_parts: list<string>,
     *     missing_parts: list<string>,
     *     status: string,
     *     issue_reason: ?string
     * }
     */
    public static function evaluateStructure(array $activeCountsByPartCode, string $examType): array
    {
        $partTargets = self::partTargetsFor($examType);
        $targetTotal = self::targetQuestionsFor($examType);

        $validCount = 0;
        $wrongSkillCount = 0;
        $exceededParts = [];
        $missingParts = [];

        foreach ($activeCountsByPartCode as $rawCode => $count) {
            $code = strtoupper((string) $rawCode);
            $qty = (int) $count;
            if ($qty <= 0) {
                continue;
            }

            if (array_key_exists($code, $partTargets)) {
                $validCount += $qty;
            } else {
                $wrongSkillCount += $qty;
            }
        }

        foreach ($partTargets as $code => $target) {
            $qty = (int) ($activeCountsByPartCode[$code] ?? 0);
            if ($qty > $target) {
                $exceededParts[] = $code;
            } elseif ($qty < $target) {
                $missingParts[] = $code;
            }
        }

        $totalActiveCount = $validCount + $wrongSkillCount;
        $issueReasons = [];

        if ($wrongSkillCount > 0) {
            $issueReasons[] = "{$wrongSkillCount} câu sai kỹ năng";
        }
        if ($validCount > $targetTotal) {
            $issueReasons[] = "vượt chỉ tiêu ({$validCount}/{$targetTotal})";
        } elseif (! empty($exceededParts)) {
            $issueReasons[] = 'vượt chỉ tiêu '.implode(', ', $exceededParts);
        }

        if (! empty($issueReasons)) {
            $status = self::STATUS_INVALID;
        } elseif (empty($missingParts) && $validCount === $targetTotal) {
            $status = self::STATUS_COMPLETED;
        } elseif ($validCount > 0) {
            $status = self::STATUS_IN_PROGRESS;
        } else {
            $status = self::STATUS_EMPTY;
        }

        return [
            'valid_count' => $validCount,
            'wrong_skill_count' => $wrongSkillCount,
            'total_active_count' => $totalActiveCount,
            'target_questions' => $targetTotal,
            'exceeded_parts' => $exceededParts,
            'missing_parts' => $missingParts,
            'status' => $status,
            'issue_reason' => ! empty($issueReasons) ? implode('; ', $issueReasons) : null,
        ];
    }

    /**
     * Xác định trạng thái nhanh khi chỉ biết tổng số câu (giữ tương thích ngược).
     */
    public static function resolveCompletionStatus(int $currentQuestions, int $targetQuestions): string
    {
        if ($currentQuestions <= 0) {
            return self::STATUS_EMPTY;
        }

        if ($currentQuestions > $targetQuestions) {
            return self::STATUS_INVALID;
        }

        return $currentQuestions === $targetQuestions ? self::STATUS_COMPLETED : self::STATUS_IN_PROGRESS;
    }

    /**
     * @param  Builder<TestForm>  $query
     */
    #[Scope]
    protected function ofExamType(Builder $query, string $examType): void
    {
        $query->whereHas('blueprint', function (Builder $blueprintQuery) use ($examType): void {
            $blueprintQuery->where('exam_type', $examType);
        });
    }

    /**
     * Lọc theo trạng thái hoàn thiện nghiêm ngặt trên từng Part với các câu hỏi ACTIVE.
     *
     * @param  Builder<TestForm>  $query
     */
    #[Scope]
    protected function withCompletionStatus(Builder $query, string $status): void
    {
        if ($status === self::STATUS_EMPTY) {
            $query->whereDoesntHave('questions', function (Builder $q): void {
                $q->where('questions.status', Question::STATUS_ACTIVE);
            });

            return;
        }

        if ($status === self::STATUS_COMPLETED) {
            $query->where(function (Builder $statusQuery): void {
                foreach (self::examTypes() as $examType) {
                    $statusQuery->orWhere(function (Builder $typeQuery) use ($examType): void {
                        $typeQuery->ofExamType($examType);
                        self::applyNoWrongSkillConstraint($typeQuery, $examType);

                        foreach (self::partTargetsFor($examType) as $partCode => $target) {
                            $typeQuery->whereHas('questions', function (Builder $q) use ($partCode): void {
                                $q->where('questions.status', Question::STATUS_ACTIVE)
                                    ->whereHas('section', fn (Builder $s) => $s->where('code', $partCode));
                            }, '=', $target);
                        }
                    });
                }
            });

            return;
        }

        if ($status === self::STATUS_INVALID) {
            $query->where(function (Builder $statusQuery): void {
                foreach (self::examTypes() as $examType) {
                    $statusQuery->orWhere(function (Builder $typeQuery) use ($examType): void {
                        $typeQuery->ofExamType($examType)
                            ->where(function (Builder $invalidQuery) use ($examType): void {
                                $allowedCodes = self::allowedPartCodesFor($examType);

                                $invalidQuery->whereHas('questions', function (Builder $q) use ($allowedCodes): void {
                                    $q->where('questions.status', Question::STATUS_ACTIVE)
                                        ->where(function (Builder $secQ) use ($allowedCodes): void {
                                            $secQ->whereDoesntHave('section')
                                                ->orWhereHas('section', fn (Builder $s) => $s->whereNotIn('code', $allowedCodes));
                                        });
                                });

                                foreach (self::partTargetsFor($examType) as $partCode => $target) {
                                    $invalidQuery->orWhereHas('questions', function (Builder $q) use ($partCode): void {
                                        $q->where('questions.status', Question::STATUS_ACTIVE)
                                            ->whereHas('section', fn (Builder $s) => $s->where('code', $partCode));
                                    }, '>', $target);
                                }
                            });
                    });
                }
            });

            return;
        }

        if ($status === self::STATUS_IN_PROGRESS) {
            $query->whereHas('questions', function (Builder $q): void {
                $q->where('questions.status', Question::STATUS_ACTIVE);
            })->where(function (Builder $statusQuery): void {
                foreach (self::examTypes() as $examType) {
                    $statusQuery->orWhere(function (Builder $typeQuery) use ($examType): void {
                        $typeQuery->ofExamType($examType);
                        self::applyNoWrongSkillConstraint($typeQuery, $examType);

                        $partTargets = self::partTargetsFor($examType);
                        foreach ($partTargets as $partCode => $target) {
                            $typeQuery->whereHas('questions', function (Builder $q) use ($partCode): void {
                                $q->where('questions.status', Question::STATUS_ACTIVE)
                                    ->whereHas('section', fn (Builder $s) => $s->where('code', $partCode));
                            }, '<=', $target);
                        }

                        $typeQuery->where(function (Builder $missingQuery) use ($partTargets): void {
                            foreach ($partTargets as $partCode => $target) {
                                $missingQuery->orWhereHas('questions', function (Builder $q) use ($partCode): void {
                                    $q->where('questions.status', Question::STATUS_ACTIVE)
                                        ->whereHas('section', fn (Builder $s) => $s->where('code', $partCode));
                                }, '<', $target);
                            }
                        });
                    });
                }
            });
        }
    }

    /**
     * @param  Builder<TestForm>  $query
     */
    private static function applyNoWrongSkillConstraint(Builder $query, string $examType): void
    {
        $allowedCodes = self::allowedPartCodesFor($examType);

        $query->whereDoesntHave('questions', function (Builder $q) use ($allowedCodes): void {
            $q->where('questions.status', Question::STATUS_ACTIVE)
                ->where(function (Builder $secQ) use ($allowedCodes): void {
                    $secQ->whereDoesntHave('section')
                        ->orWhereHas('section', fn (Builder $s) => $s->whereNotIn('code', $allowedCodes));
                });
        });
    }

    /**
     * @return BelongsTo<TestBlueprint, $this>
     */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(TestBlueprint::class, 'blueprint_id', 'blueprint_id');
    }

    /**
     * @return BelongsToMany<Question, $this>
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'test_form_question', 'test_form_id', 'question_id')
            ->withPivot(['sequence_no', 'point']);
    }

    /**
     * @return HasMany<TestAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class, 'test_form_id', 'test_form_id');
    }

    /**
     * Mã đề hiển thị, sinh từ khóa chính nên luôn duy nhất (ví dụ: TEST-001).
     */
    public function getTestCodeAttribute(): string
    {
        return 'TEST-'.str_pad((string) $this->test_form_id, 3, '0', STR_PAD_LEFT);
    }
}
