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
            $table->string('image');          // URL หรือชื่อไฟล์รูปภาพ
            $table->text('detail');           // รายละเอียด
            $table->string('category');       // หมวดหมู่
            $table->string('tag');            // แท็ก
            $table->string('price');  // ราคา
            $table->timestamps();
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
