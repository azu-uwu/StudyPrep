<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VocabularyController;
use App\Http\Controllers\ListeningPracticeController;
use App\Http\Controllers\ReadingPracticeController;
use Illuminate\Support\Facades\Route;

// Trang chủ mặc định chuyển hướng đến màn hình chính: Luyện tập
Route::get('/', function () {
    return redirect('/luyen-tap');
});

// Các màn hình chính trong Sidebar (HỌC & TIẾN ĐỘ)
Route::view('/luyen-tap', 'pages.luyen-tap')->name('luyen-tap');
Route::get('/luyen-tap/doc', function () {
    if (request('skill') === 'nghe') {
        return redirect('/luyen-tap/nghe');
    }
    if (request('skill') === 'noi') {
        return redirect('/luyen-tap/noi');
    }
    if (request('skill') === 'viet') {
        return redirect('/luyen-tap/viet');
    }

    return view('pages.luyen-tap-doc');
})->name('luyen-tap.doc');

// Luồng Luyện kỹ năng Nghe (Listening)
Route::view('/luyen-tap/nghe', 'pages.luyen-tap-nghe')->name('luyen-tap.nghe');
Route::get('/luyen-tap/nghe/mo-ta-hinh', function () {
    return redirect('/luyen-tap/nghe?part=1');
})->name('luyen-tap.nghe.mo-ta-hinh');
Route::get('/luyen-tap/nghe/lam-bai', [ListeningPracticeController::class, 'practice'])->name('luyen-tap.nghe.lam-bai');
Route::post('/luyen-tap/nghe/nop-bai', [ListeningPracticeController::class, 'submit'])->name('luyen-tap.nghe.nop-bai');
Route::get('/luyen-tap/nghe/ket-qua', [ListeningPracticeController::class, 'result'])->name('luyen-tap.nghe.ket-qua');
Route::get('/luyen-tap/nghe/xem-dap-an', [ListeningPracticeController::class, 'result'])->name('luyen-tap.nghe.xem-dap-an');

// Luồng Luyện kỹ năng Đọc (Part 5: Hoàn thành câu & Kết quả)
Route::get('/luyen-tap/doc/hoan-thanh-cau', [ReadingPracticeController::class, 'practice'])->name('luyen-tap.doc.hoan-thanh-cau');
Route::post('/luyen-tap/doc/nop-bai', [ReadingPracticeController::class, 'submit'])->name('luyen-tap.doc.nop-bai');
Route::get('/luyen-tap/doc/ket-qua', [ReadingPracticeController::class, 'result'])->name('luyen-tap.doc.ket-qua');

Route::view('/luyen-tap/doc-cau', 'pages.doc-cau')->name('luyen-tap.doc-cau');
Route::view('/luyen-tap/doc-doan', 'pages.doc-doan')->name('luyen-tap.doc-doan');
Route::view('/luyen-tap/noi', 'pages.luyen-tap-noi')->name('luyen-tap.noi');
Route::view('/luyen-tap/noi/phat-am', 'pages.luyen-noi')->name('luyen-tap.noi.phat-am');
Route::view('/luyen-tap/phat-am', 'pages.luyen-noi')->name('luyen-tap.phat-am');
Route::view('/luyen-tap/viet', 'pages.luyen-tap-viet')->name('luyen-tap.viet');
Route::view('/luyen-tap/viet-cau', 'pages.viet-cau')->name('luyen-tap.viet-cau');
Route::view('/luyen-tap/viet-doan', 'pages.viet-doan')->name('luyen-tap.viet-doan');

Route::view('/thi-thu', 'pages.thi-thu')->name('thi-thu');
Route::view('/thi-thu/gioi-thieu-de', 'pages.thi-thu-gioi-thieu-de')->name('thi-thu-gioi-thieu-de');
Route::view('/thi-thu/giao-dien-thi', 'pages.thi-thu-giao-dien-thi')->name('thi-thu-giao-dien-thi');
Route::view('/thi-thu/ket-qua', 'pages.thi-thu-ket-qua')->name('thi-thu-ket-qua');

Route::view('/tu-vung', 'pages.tu-vung')->name('tu-vung');

Route::view('/phan-tich', 'pages.phan-tich-tong-quan')->name('phan-tich');
Route::view('/phan-tich/chi-tiet', 'pages.phan-tich')->name('phan-tich.chi-tiet');

Route::view('/lich-su', 'pages.lich-su')->name('lich-su');

Route::view('/cai-thien', 'pages.cai-thien')->name('cai-thien');

// Các màn hình quản lý trong Header (Hồ sơ & Cài đặt)
Route::view('/ho-so', 'pages.ho-so')->name('ho-so');
Route::view('/ho-so/muc-tieu-thiet-lap', 'pages.muc-tieu-thiet-lap')->name('ho-so.muc-tieu-thiet-lap');

Route::view('/cai-dat', 'pages.cai-dat')->name('cai-dat');

// Trang demo component của nhóm (giữ nguyên không làm ảnh hưởng)
Route::view('/component-demo', 'components.component-demo');
Route::view('/register', 'auth.register')->name('register');
Route::view('/login', 'auth.login')->name('login');
Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
Route::redirect('/quen-mat-khau', '/forgot-password');
Route::redirect('/admin/login', '/login')->name('admin.login');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

        // Quản lý người dùng
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
        Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
        // Ngân hàng câu hỏi
        Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
        Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::get('/questions/group/create', [QuestionController::class, 'createGroup'])->name('questions.group.create');
        Route::post('/questions/group', [QuestionController::class, 'storeGroup'])->name('questions.group.store');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::patch('/questions/{question}/status', [QuestionController::class, 'toggleStatus'])->name('questions.toggle-status');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
        Route::post('/questions/bulk-action', [QuestionController::class, 'bulkAction'])->name('questions.bulk-action');
        Route::get('/questions/export', [QuestionController::class, 'export'])->name('questions.export');
        Route::get('/questions/bulk-import', [QuestionController::class, 'importView'])->name('questions.bulk-import');
        Route::post('/questions/import', [QuestionController::class, 'import'])->name('questions.import');
        Route::redirect('/ngan-hang-cau-hoi', '/admin/questions');

        // Từ vựng & Flashcards
        Route::get('/vocabularies', [VocabularyController::class, 'index'])->name('vocabularies.index');
        Route::get('/vocabularies/create', [VocabularyController::class, 'create'])->name('vocabularies.create');
        Route::post('/vocabularies', [VocabularyController::class, 'store'])->name('vocabularies.store');
        Route::put('/vocabularies/{vocabulary}', [VocabularyController::class, 'update'])->name('vocabularies.update');
        Route::delete('/vocabularies/{vocabulary}', [VocabularyController::class, 'destroy'])->name('vocabularies.destroy');
        Route::post('/vocabularies/bulk-action', [VocabularyController::class, 'bulkAction'])->name('vocabularies.bulk-action');
        Route::get('/vocabularies/export', [VocabularyController::class, 'export'])->name('vocabularies.export');
        Route::get('/vocabularies/import', [VocabularyController::class, 'importView'])->name('vocabularies.import-view');
        Route::get('/vocabularies/sample-csv', [VocabularyController::class, 'sampleCsv'])->name('vocabularies.sample-csv');
        Route::post('/vocabularies/import', [VocabularyController::class, 'import'])->name('vocabularies.import');
        Route::post('/vocabularies/import-confirm', [VocabularyController::class, 'confirmImport'])->name('vocabularies.import-confirm');
        Route::post('/vocabularies/topics', [VocabularyController::class, 'storeTopic'])->name('vocabularies.topics.store');
        Route::put('/vocabularies/topics/{topic}', [VocabularyController::class, 'updateTopic'])->name('vocabularies.topics.update');
        Route::delete('/vocabularies/topics/{topic}', [VocabularyController::class, 'destroyTopic'])->name('vocabularies.topics.destroy');
        Route::redirect('/nhap-tu-vung-hang-loat', '/admin/vocabularies/import');
        Route::redirect('/tu-vung-flashcard', '/admin/vocabularies');
    });
});
