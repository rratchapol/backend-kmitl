<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userpost_id');  // ผู้ที่กด like
            $table->string('image');          // URL หรือชื่อไฟล์รูปภาพ
            $table->text('detail');           // รายละเอียด
            $table->string('category');       // หมวดหมู่
            $table->string('tag');            // แท็ก
            $table->string('price');  // ราคา
            $table->string('status')->nullable();

            $table->timestamps();

            $table->foreign('userpost_id')->references('id')->on('customers')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
