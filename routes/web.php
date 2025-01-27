<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/chat', function () {
    return view('chat'); // ชื่อไฟล์ใน resources/views/chat.blade.php
});