<?php

use App\Http\Controllers\Admin\QuestionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Endpoint sinh audio tự động từ văn bản/transcript cho TOEIC Listening
| POST /api/admin/questions/generate-tts
|
*/

Route::post('/admin/questions/generate-tts', [QuestionController::class, 'generateTts'])
    ->name('api.admin.questions.generate-tts');
