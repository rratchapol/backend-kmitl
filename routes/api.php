<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\PreorderController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ImageController;

Route::group(['prefix' => 'auth'], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

});
Route::middleware(['auth:api'])->group(function () {
    Route::post('me', [AuthController::class, 'me']);
    Route::post('logout', action: [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);


    Route::get('customers', [CustomerController::class, 'index']); // แสดงรายชื่อลูกค้าทั้งหมด
    Route::post('customers', [CustomerController::class, 'store']); // เพิ่มลูกค้า
    Route::get('customers/{id}', [CustomerController::class, 'show']); // แสดงลูกค้ารายละเอียด
    Route::put('customers/{id}', [CustomerController::class, 'update']); // แก้ไขลูกค้า
    Route::delete('customers/{id}', [CustomerController::class, 'destroy']); // ลบลูกค้า


    Route::get('products', [ProductController::class, 'index']); // แสดงสินค้าทั้งหมด
    Route::get('products/{id}', [ProductController::class, 'show']); // แสดงสินค้าตาม ID
    Route::post('products', [ProductController::class, 'store']); // เพิ่มสินค้า
    Route::put('products/{id}', [ProductController::class, 'update']); // แก้ไขสินค้า
    Route::delete('products/{id}', [ProductController::class, 'destroy']); // ลบสินค้า


    Route::get('deals', [DealController::class, 'index']); // ดู deals ทั้งหมด
    Route::get('deals/{id}', [DealController::class, 'show']); // ดู deal ตาม id
    Route::post('deals', [DealController::class, 'store']); // เพิ่ม deal ใหม่
    Route::put('deals/{id}', [DealController::class, 'update']); // แก้ไข deal
    Route::delete('deals/{id}', [DealController::class, 'destroy']); // ลบ deal


    Route::get('preorders', [PreorderController::class, 'index']); // ดู preorders ทั้งหมด
    Route::get('preorders/{id}', [PreorderController::class, 'show']); // ดู preorder ตาม id
    Route::post('preorders', [PreorderController::class, 'store']); // เพิ่ม preorder ใหม่
    Route::put('preorders/{id}', [PreorderController::class, 'update']); // แก้ไข preorder
    Route::delete('preorders/{id}', [PreorderController::class, 'destroy']); // ลบ preorder


    Route::get('likes', [LikeController::class, 'index']); // ดู likes ทั้งหมด
    Route::get('likes/{id}', [LikeController::class, 'show']); // ดู like ตาม id
    Route::post('likes', [LikeController::class, 'store']); // เพิ่ม like ใหม่
    Route::put('likes/{id}', [LikeController::class, 'update']); // แก้ไข like
    Route::delete('likes/{id}', [LikeController::class, 'destroy']); // ลบ like
    Route::get('userslikes/{userId}', [LikeController::class, 'getLikesByUser']); // ดู likes ทั้งหมดที่ user นี้สร้าง


    Route::get('categories', [CategoryController::class, 'index']); // ดู categories ทั้งหมด
    Route::get('categories/{id}', [CategoryController::class, 'show']); // ดู category ตาม id
    Route::post('categories', [CategoryController::class, 'store']); // เพิ่ม category ใหม่
    Route::put('categories/{id}', [CategoryController::class, 'update']); // แก้ไข category
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']); // ลบ category


    Route::post('/uploadimage', [ImageController::class, 'store']);

});