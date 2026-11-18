<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiToeicService;
use App\Services\TestFormService;
use App\Services\WordQuestionImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AiToeicController extends Controller
{
    /**
     * AI Parser: Đọc file Word (.docx) kèm hình ảnh/audio nhúng và trả về JSON cấu trúc đề TOEIC chuẩn.
     * POST /api/admin/questions/ai-parse-docx
     */
    public function parseDocx(
        Request $request,
        WordQuestionImportService $importService,
        TestFormService $testFormService
    ): JsonResponse {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:20480'],
            'exam_title' => ['nullable', 'string', 'max:255'],
            'save' => ['nullable', 'boolean'],
            'default_part_code' => ['nullable', 'string', 'max:32'],
            'test_form_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:ACTIVE,DRAFT'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['docx', 'json', 'csv', 'txt'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Định dạng tệp không hỗ trợ. Vui lòng chọn tệp .docx, .json, .csv hoặc .txt.',
            ], 422);
        }

        $examTitle = $validated['exam_title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        if ($extension === 'docx') {
            $structured = $importService->parseDocxToStructuredJson($file->getRealPath(), $examTitle);
        } else {
            $flat = $importService->parseUploadedFile($file);
            $structured = $importService->convertFlatQuestionsToStructuredJson($flat, $examTitle);
        }

        $response = [
            'success' => true,
            'data' => $structured,
        ];

        if ($request->boolean('save')) {
            $testFormService->ensureStandardSectionsExist();
            $flatQuestions = $importService->flattenStructuredJson($structured);
            $importResult = $importService->importQuestions(
                $flatQuestions,
                $validated['default_part_code'] ?? 'PART5',
                $validated['status'] ?? 'ACTIVE',
                isset($validated['test_form_id']) ? (int) $validated['test_form_id'] : null,
            );

            $response['saved_count'] = $importResult['created'];
            $response['saved_question_ids'] = $importResult['question_ids'];
            $response['attached_to_test'] = $importResult['attached'];
        }

        return response()->json($response);
    }

    /**
     * AI Explanation: Sinh lời giải chi tiết cho danh sách câu hỏi sau khi học sinh nộp bài thi.
     * POST /api/ai/explain-attempt
     */
    public function explainAttempt(Request $request, AiToeicService $aiService): JsonResponse
    {
        $validated = $request->validate([
            'questions' => ['required', 'array', 'min:1', 'max:200'],
            'questions.*.question_id' => ['required'],
            'questions.*.part' => ['nullable'],
            'questions.*.content' => ['required', 'string'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.user_answer' => ['nullable', 'string', 'max:20'],
            'questions.*.correct_answer' => ['nullable', 'string', 'max:20'],
        ]);

        $explanations = $aiService->generateAttemptExplanations($validated['questions']);

        return response()->json([
            'success' => true,
            'explanations' => $explanations,
        ]);
    }

    /**
     * Speaking Audio Recording + AI Speech Evaluation:
     * Lưu file âm thanh ghi âm từ Web Audio API (MediaRecorder) và chấm điểm/nhận xét bằng AI.
     * POST /api/speaking/recordings
     */
    public function storeSpeakingRecording(Request $request, AiToeicService $aiService): JsonResponse
    {
        $validated = $request->validate([
            'audio' => ['nullable', 'file', 'max:25600'],
            'transcript' => ['nullable', 'string', 'max:5000'],
            'prompt' => ['nullable', 'string', 'max:2000'],
            'question_id' => ['nullable', 'integer'],
            'attempt_id' => ['nullable', 'integer'],
        ]);

        $audioUrl = null;
        if ($request->hasFile('audio')) {
            $audioFile = $request->file('audio');
            $rawExt = strtolower($audioFile->getClientOriginalExtension() ?: 'webm');
            $ext = in_array($rawExt, ['webm', 'mp3', 'wav', 'ogg', 'm4a'], true) ? $rawExt : 'webm';
            $filename = 'spk_'.date('Ymd_His').'_'.Str::lower(Str::random(8)).'.'.$ext;

            $targetDir = public_path('storage/speaking/recordings');
            if (! is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }

            $audioFile->move($targetDir, $filename);
            $audioUrl = asset('storage/speaking/recordings/'.$filename);
        }

        $transcript = trim((string) ($validated['transcript'] ?? ''));
        $prompt = trim((string) ($validated['prompt'] ?? 'TOEIC Speaking Practice'));

        $evaluation = $aiService->evaluateSpeaking($prompt, $transcript);

        if (
            isset($validated['attempt_id'], $validated['question_id'])
            && Schema::hasTable('attempt_response')
        ) {
            DB::table('attempt_response')->updateOrInsert(
                [
                    'attempt_id' => (int) $validated['attempt_id'],
                    'question_id' => (int) $validated['question_id'],
                ],
                [
                    'text_response' => $transcript !== '' ? $transcript : null,
                    'media_url' => $audioUrl,
                    'score' => round($evaluation['overall_score'] / 40, 2),
                    'feedback' => $evaluation['feedback'],
                    'updated_at' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'audio_url' => $audioUrl,
            'transcript' => $transcript,
            'evaluation' => $evaluation,
        ]);
    }
}
