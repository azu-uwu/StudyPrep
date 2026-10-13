<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// route demo xem trang component, mn thêm trang nào thì viết route tương ứng nhé ('/tên-url-muốn-đặt', 'tên-view-tương-ứng')
Route::view('/component-demo', 'components.component-demo');