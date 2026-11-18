<?php

use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Api\AiToeicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - StudyPrep TOEIC & AI Engine
|--------------------------------------------------------------------------
*/

// Sinh audio tự động (TTS) từ văn bản/transcript cho TOEIC Listening
Route::post('/admin/questions/generate-tts', [QuestionController::class, 'generateTts'])
    ->name('api.admin.questions.generate-tts');

// Sinh audio tự động (TTS) theo lô an toàn (tối đa 5 nhóm mỗi lần gọi)
Route::post('/admin/questions/batch-generate-tts', [QuestionController::class, 'batchGenerateTts'])
    ->name('api.admin.questions.batch-generate-tts');

// AI Parser bóc tách file Word (.docx) kèm ảnh nhúng sang JSON đề TOEIC chuẩn
Route::post('/admin/questions/ai-parse-docx', [AiToeicController::class, 'parseDocx'])
    ->name('api.admin.questions.ai-parse-docx');

// AI Explanation sinh lời giải chi tiết sau khi học viên nộp bài thi
Route::post('/ai/explain-attempt', [AiToeicController::class, 'explainAttempt'])
    ->name('api.ai.explain-attempt');

// Speaking Recording (Web Audio API / MediaRecorder) + Chấm điểm nói bằng AI
Route::post('/speaking/recordings', [AiToeicController::class, 'storeSpeakingRecording'])
    ->name('api.speaking.recordings');
