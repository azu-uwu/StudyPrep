<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\TestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VocabularyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ListeningPracticeController;
use App\Http\Controllers\MockTestController;
use App\Http\Controllers\PersonalTopicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReadingPracticeController;
use App\Http\Controllers\StudySettingController;
use App\Http\Controllers\VocabularyPracticeController;
use App\Models\TestAttempt;
use App\Models\TestForm;
use Illuminate\Support\Facades\Route;

// use Illuminate\Support\Facades\Route;

// Trang chủ mặc định chuyển hướng đến màn hình chính: Luyện tập
Route::get('/', function () {
    return redirect('/luyen-tap');
});

// Các màn hình chính trong Sidebar (HỌC & TIẾN ĐỘ)
Route::view('/luyen-tap', 'pages.luyen-tap')->name('luyen-tap');
Route::get('/luyen-tap/doc', [ReadingPracticeController::class, 'index'])->name('luyen-tap.doc');

// Luồng Luyện kỹ năng Nghe (Listening)
Route::view('/luyen-tap/nghe', 'pages.luyen-tap-nghe')->name('luyen-tap.nghe');
Route::get('/luyen-tap/nghe/mo-ta-hinh', function () {
    return redirect('/luyen-tap/nghe?part=1');
})->name('luyen-tap.nghe.mo-ta-hinh');
Route::get('/luyen-tap/nghe/lam-bai', [ListeningPracticeController::class, 'practice'])->name('luyen-tap.nghe.lam-bai');
Route::post('/luyen-tap/nghe/nop-bai', [ListeningPracticeController::class, 'submit'])->name('luyen-tap.nghe.nop-bai');
Route::get('/luyen-tap/nghe/ket-qua', [ListeningPracticeController::class, 'result'])->name('luyen-tap.nghe.ket-qua');
Route::get('/luyen-tap/nghe/xem-dap-an', [ListeningPracticeController::class, 'result'])->name('luyen-tap.nghe.xem-dap-an');

// Luồng Luyện kỹ năng Đọc (Part 5: Hoàn thành câu, Part 6: Hoàn thành đoạn văn & Kết quả)
Route::get('/luyen-tap/doc/hoan-thanh-cau', [ReadingPracticeController::class, 'practice'])->name('luyen-tap.doc.hoan-thanh-cau');
Route::get('/luyen-tap/doc/tiep-tuc', [ReadingPracticeController::class, 'resume'])->name('luyen-tap.doc.tiep-tuc');
Route::post('/luyen-tap/doc/luu-va-thoat', [ReadingPracticeController::class, 'saveAndExit'])->name('luyen-tap.doc.luu-va-thoat');
Route::post('/luyen-tap/doc/kiem-tra-cau-hoi', [ReadingPracticeController::class, 'checkAnswer'])->name('luyen-tap.doc.kiem-tra-cau-hoi');
Route::post('/luyen-tap/doc/kiem-tra-nhom', [ReadingPracticeController::class, 'checkGroupAnswers'])->name('luyen-tap.doc.kiem-tra-nhom');
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

// Luồng Thi Thử TOEIC (Mock Test)
Route::get('/thi-thu', [MockTestController::class, 'index'])->name('thi-thu');
Route::get('/thi-thu/gioi-thieu-de/{id}', [MockTestController::class, 'intro'])->name('thi-thu.gioi-thieu-de');
Route::get('/thi-thu/gioi-thieu-de', function () {
    $firstTestId = TestForm::query()->whereHas('questions')->value('test_form_id')
        ?? TestForm::query()->value('test_form_id');
    if (! $firstTestId) {
        return redirect('/thi-thu')->with('error', 'Chưa có đề thi nào trong hệ thống.');
    }

    return redirect()->route('thi-thu.gioi-thieu-de', $firstTestId);
})->name('thi-thu-gioi-thieu-de');

Route::post('/thi-thu/bat-dau/{id}', [MockTestController::class, 'start'])->name('thi-thu.bat-dau');

Route::get('/thi-thu/giao-dien-thi/{attemptId}', [MockTestController::class, 'exam'])->name('thi-thu.giao-dien-thi');
Route::get('/thi-thu/giao-dien-thi', function () {
    $lastAttemptId = TestAttempt::query()->latest('attempt_id')->value('attempt_id');
    if (! $lastAttemptId) {
        return redirect('/thi-thu');
    }

    return redirect()->route('thi-thu.giao-dien-thi', $lastAttemptId);
})->name('thi-thu-giao-dien-thi');

Route::post('/thi-thu/nop-bai/{attemptId}', [MockTestController::class, 'submit'])->name('thi-thu.nop-bai');

Route::get('/thi-thu/ket-qua/{attemptId}', [MockTestController::class, 'result'])->name('thi-thu.ket-qua');
Route::get('/thi-thu/ket-qua', function () {
    $lastAttemptId = TestAttempt::query()->where('status', 'COMPLETED')->latest('attempt_id')->value('attempt_id')
        ?? TestAttempt::query()->latest('attempt_id')->value('attempt_id');
    if (! $lastAttemptId) {
        return redirect('/thi-thu');
    }

    return redirect()->route('thi-thu.ket-qua', $lastAttemptId);
})->name('thi-thu-ket-qua');

Route::get('/tu-vung', [VocabularyPracticeController::class, 'index'])->name('tu-vung');
Route::post('/tu-vung/check-answer', [VocabularyPracticeController::class, 'checkAnswer'])->name('tu-vung.check-answer');
Route::post('/tu-vung/chu-de', [PersonalTopicController::class, 'store'])->middleware('auth')->name('tu-vung.topics.store');
Route::match(['put', 'patch'], '/tu-vung/chu-de/{topic}', [PersonalTopicController::class, 'update'])->middleware('auth')->name('tu-vung.topics.update');
Route::post('/tu-vung/chu-de/{topic}/tu-vung', [PersonalTopicController::class, 'storeVocabulary'])->middleware('auth')->name('tu-vung.topics.vocabularies.store');
Route::match(['put', 'patch'], '/tu-vung/chu-de/{topic}/tu-vung/{vocabulary}', [PersonalTopicController::class, 'updateVocabulary'])->middleware('auth')->name('tu-vung.topics.vocabularies.update');
Route::delete('/tu-vung/chu-de/{topic}/tu-vung/{vocabulary}', [PersonalTopicController::class, 'destroyVocabulary'])->middleware('auth')->name('tu-vung.topics.vocabularies.destroy');
Route::delete('/tu-vung/chu-de/{topic}/tu-vung', [PersonalTopicController::class, 'bulkDeleteVocabularies'])->middleware('auth')->name('tu-vung.topics.vocabularies.bulk-delete');
Route::delete('/tu-vung/chu-de/{topic}', [PersonalTopicController::class, 'destroy'])->middleware('auth')->name('tu-vung.topics.destroy');
Route::patch('/tu-vung/tu/{vocabulary}/trang-thai', [VocabularyPracticeController::class, 'updateVocabularyStatus'])->middleware('auth')->name('tu-vung.vocabularies.update-status');
Route::get('/tu-vung/{topicId}/chon-tu', [VocabularyPracticeController::class, 'selectWords'])->name('tu-vung.chon-tu');
Route::get('/tu-vung/{topicId}/flashcard', [VocabularyPracticeController::class, 'flashcard'])->name('tu-vung.flashcard');
Route::get('/tu-vung/{topicId}', [VocabularyPracticeController::class, 'show'])->name('tu-vung.show');
Route::redirect('/flashcard', '/tu-vung');
Route::redirect('/chon-tu', '/tu-vung');

Route::view('/phan-tich', 'pages.phan-tich-tong-quan')->name('phan-tich');
Route::view('/phan-tich/chi-tiet', 'pages.phan-tich')->name('phan-tich.chi-tiet');

Route::middleware('auth')->group(function () {
    Route::get('/lich-su', [HistoryController::class, 'index'])->name('lich-su');
    Route::get('/lich-su/{attemptId}', [HistoryController::class, 'show'])->name('lich-su.show')->whereNumber('attemptId');
    Route::get('/lich-su/{attemptId}/on-lai', [HistoryController::class, 'retry'])->name('lich-su.retry')->whereNumber('attemptId');
});

Route::view('/cai-thien', 'pages.cai-thien')->name('cai-thien');

// Các màn hình quản lý trong Header (Hồ sơ & Cài đặt)
Route::middleware('auth')->group(function () {
    // Hiển thị hồ sơ người dùng
    Route::get('/ho-so', [ProfileController::class, 'show'])->name('ho-so');

    // Cập nhật thông tin cá nhân
    Route::post('/ho-so', [ProfileController::class, 'update'])->name('profile.update');

    // Đổi mật khẩu
    Route::post('/ho-so/doi-mat-khau', [ProfileController::class, 'changePassword'])->middleware('throttle:5,1')->name('profile.password');

    // Cập nhật ảnh đại diện
    Route::post('/ho-so/anh-dai-dien', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
});
Route::middleware('auth')->group(function () {
    Route::get('/ho-so/muc-tieu-thiet-lap', [GoalController::class, 'edit'])->name('profile.goal.edit');
    Route::post('/ho-so/muc-tieu-thiet-lap', [GoalController::class, 'store'])->name('profile.goal.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/muc-tieu-trang-chu', [GoalController::class, 'index'])->name('muc-tieu-trang-chu');
});

Route::middleware('auth')->group(function () {
    // Hiển thị trang cài đặt
    Route::get('/cai-dat', [StudySettingController::class, 'index'])->name('cai-dat');
    // Xử lý lưu cài đặt
    Route::put('/cai-dat', [StudySettingController::class, 'update'])->name('settings.update');
});

// Trang demo component của nhóm (giữ nguyên không làm ảnh hưởng)
Route::view('/component-demo', 'components.component-demo');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

        // Quản lý người dùng
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
        Route::get('/users/sample-csv', [UserController::class, 'sampleCsv'])->name('users.sample-csv');
        Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
        Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Quản lý đề thi
        Route::get('/tests', [TestController::class, 'index'])->name('tests.index');
        Route::post('/tests', [TestController::class, 'store'])->name('tests.store');
        Route::get('/tests/{id}', [TestController::class, 'show'])->whereNumber('id')->name('tests.show');
        Route::put('/tests/{id}', [TestController::class, 'update'])->whereNumber('id')->name('tests.update');
        Route::delete('/tests/{id}', [TestController::class, 'destroy'])->whereNumber('id')->name('tests.destroy');
        Route::post('/tests/{id}/save', [TestController::class, 'saveState'])->whereNumber('id')->name('tests.save');
        Route::post('/tests/{id}/cleanup-issues', [TestController::class, 'cleanupIssues'])->whereNumber('id')->name('tests.cleanup-issues');
        Route::post('/tests/{id}/questions', [TestController::class, 'attachQuestions'])->whereNumber('id')->name('tests.questions.attach');
        Route::put('/tests/{id}/questions/{questionId}', [TestController::class, 'updateQuestion'])->whereNumber(['id', 'questionId'])->name('tests.questions.update');
        Route::delete('/tests/{id}/questions/{questionId}', [TestController::class, 'detachQuestion'])->whereNumber(['id', 'questionId'])->name('tests.questions.detach');
        Route::redirect('/de-thi', '/admin/tests');

        // Ngân hàng câu hỏi
        Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
        Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::get('/questions/group/create', [QuestionController::class, 'createGroup'])->name('questions.group.create');
        Route::post('/questions/group', [QuestionController::class, 'storeGroup'])->name('questions.group.store');
        Route::get('/questions/bulk-action', fn () => redirect()->route('admin.questions.index'));
        Route::match(['post', 'delete', 'patch'], '/questions/bulk-action', [QuestionController::class, 'bulkAction'])->name('questions.bulk-action');
        Route::get('/questions/export', [QuestionController::class, 'export'])->name('questions.export');
        Route::get('/questions/sample-docx', [QuestionController::class, 'sampleDocx'])->name('questions.sample-docx');
        Route::get('/questions/bulk-import', [QuestionController::class, 'importView'])->name('questions.bulk-import');
        Route::post('/questions/import/preview', [QuestionController::class, 'previewStagingImport'])->name('questions.import.preview');
        Route::get('/questions/import/review/{batchId}', [QuestionController::class, 'showStagingReview'])->name('questions.import.review');
        Route::get('/questions/import/review/{batchId}/xml', [QuestionController::class, 'downloadStagingXml'])->name('questions.import.xml');
        Route::post('/questions/import/review/{batchId}/commit', [QuestionController::class, 'commitStagingImport'])->name('questions.import.commit');
        Route::post('/questions/import', [QuestionController::class, 'import'])->name('questions.import');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->whereNumber('question')->name('questions.update');
        Route::patch('/questions/{question}/status', [QuestionController::class, 'toggleStatus'])->whereNumber('question')->name('questions.toggle-status');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->whereNumber('question')->name('questions.destroy');
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

// AUTHENTICATION - STUDYPREP

// Khách chưa đăng nhập
Route::middleware('guest')->group(function () {

    // Đăng ký
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');

    // Đăng nhập
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');

    // Giao diện quên mật khẩu
    Route::view('/forgot-password', 'auth.forgot-password')
        ->name('password.request');
});

// Đăng xuất người dùng
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Đường dẫn thay thế
Route::redirect('/quen-mat-khau', '/forgot-password');
Route::redirect('/admin/login', '/login')->name('admin.login');
