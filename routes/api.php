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
use App\Http\Controllers\PostController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AdminAuthController;


Route::group(['prefix' => 'auth'], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verifyemail', [AuthController::class, 'verifyEmail']);
    // Route::get('customers', [CustomerController::class, 'index']);
    Route::get('products', [ProductController::class, 'index']);

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
    Route::get('productsid/{id}', [ProductController::class, 'look']); // แสดงสินค้าตาม ID


    Route::get('deals', [DealController::class, 'index']); // ดู deals ทั้งหมด
    Route::get('deals/{id}', [DealController::class, 'show']); // ดู deal ตาม id
    Route::post('deals', [DealController::class, 'store']); // เพิ่ม deal ใหม่
    Route::put('deals/{id}', [DealController::class, 'update']); // แก้ไข deal
    Route::delete('deals/{id}', [DealController::class, 'destroy']); // ลบ deal
    Route::get('dealsid/{id}', [DealController::class, 'look']); // ดู deal ตาม id



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


    Route::get('posts', [PostController::class, 'index']);          // ดู Post ทั้งหมด
    Route::get('posts/{id}', [PostController::class, 'show']);      // ดู Post ตาม ID
    Route::post('posts', [PostController::class, 'store']);         // สร้าง Post
    Route::put('posts/{id}', [PostController::class, 'update']);    // อัปเดต Post
    Route::delete('posts/{id}', [PostController::class, 'destroy']); // ลบ Post
    Route::get('postsid/{id}', [PostController::class, 'look']); 


    Route::get('/tag', [TagController::class, 'index']); // ดูแท็กทั้งหมด
    Route::post('/tags', [TagController::class, 'store']); // สร้างแท็กใหม่
    Route::get('/tag/{id}', [TagController::class, 'show']); // ดูแท็กเดียว
    Route::put('/tags/{id}', [TagController::class, 'update']); // แก้ไขแท็ก
    Route::delete('/tags/{id}', [TagController::class, 'destroy']); // ลบแท็ก


    Route::get('/location', [LocationController::class, 'index']);
    Route::post('/location', [LocationController::class, 'store']);
    Route::get('/locations/{id}', [LocationController::class, 'show']);
    Route::put('/locations/{id}', [LocationController::class, 'update']);
    Route::delete('/locations/{id}', [LocationController::class, 'destroy']);


    Route::post('/uploadimage', [ImageController::class, 'store']);


    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::get('/chat/fetch/{buyer_id}/{seller_id}', [ChatController::class, 'fetchMessages']);
    Route::post('/chat', [ChatController::class, 'store']);
    Route::post('/seechat/{user_id}', [ChatController::class, 'getUsersInConversation']);


});

Route::prefix('admin')->group(function () {
    Route::post('register', [AdminAuthController::class, 'register']);
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::middleware('auth:admin_api')->get('profile', [AdminAuthController::class, 'profile']);
});

Route::middleware(['auth:admin_api'])->group(function () {

    Route::get('/admin', [AdminAuthController::class, 'getAllAdmins']);
    Route::get('/admin/{id}', [AdminAuthController::class, 'look']);
    Route::put('/admin/{id}', [AdminAuthController::class, 'updateAdmin']);


    Route::get('customer', [CustomerController::class, 'index']); // แสดงรายชื่อลูกค้าทั้งหมด
    Route::get('customer/{id}', [CustomerController::class, 'show']); // แสดงลูกค้ารายละเอียด
    Route::put('customer/{id}', [CustomerController::class, 'update']); // แก้ไขลูกค้า
    Route::delete('customer/{id}', [CustomerController::class, 'destroy']); // ลบลูกค้า


    Route::get('/tags', [TagController::class, 'index']); // ดูแท็กทั้งหมด
    Route::post('/tags', [TagController::class, 'store']); // สร้างแท็กใหม่
    Route::get('/tags/{id}', [TagController::class, 'show']); // ดูแท็กเดียว
    Route::put('/tags/{id}', [TagController::class, 'update']); // แก้ไขแท็ก
    Route::delete('/tags/{id}', [TagController::class, 'destroy']); // ลบแท็ก


    Route::get('/locations', [LocationController::class, 'index']);
    Route::post('/locations', [LocationController::class, 'store']);
    Route::get('/locations/{id}', [LocationController::class, 'show']);
    Route::put('/locations/{id}', [LocationController::class, 'update']);
    Route::delete('/locations/{id}', [LocationController::class, 'destroy']);

});
