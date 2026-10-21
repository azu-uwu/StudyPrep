<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SessionController;
use Illuminate\Support\Facades\Route;

// Trang chủ mặc định chuyển hướng đến màn hình chính: Luyện tập
Route::get('/', function () {
    return redirect('/luyen-tap');
});

// Các màn hình chính trong Sidebar (HỌC & TIẾN ĐỘ)
Route::view('/luyen-tap', 'pages.luyen-tap')->name('luyen-tap');
Route::view('/luyen-tap/doc', 'pages.luyen-tap-doc')->name('luyen-tap.doc');
Route::view('/luyen-tap/doc-cau', 'pages.doc-cau')->name('luyen-tap.doc-cau');
Route::view('/luyen-tap/doc-doan', 'pages.doc-doan')->name('luyen-tap.doc-doan');
Route::view('/luyen-tap/noi', 'pages.luyen-noi')->name('luyen-tap.noi');
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

Route::redirect('/login', '/admin/login')->name('login');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [SessionController::class, 'create'])->name('login');
        Route::post('/login', [SessionController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    });
});
