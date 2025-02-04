<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); // สร้างคอลัมน์ id
            $table->string('name'); // ชื่อ
            $table->string('pic')->nullable();
            $table->string('email')->unique(); // อีเมล
            $table->string('mobile'); // เบอร์โทรศัพท์
            $table->string('address'); // ที่อยู่
            $table->string('faculty'); // คณะ
            $table->string('department'); // แผนก
            $table->string('classyear'); // ปีการศึกษา
            $table->string('role'); // บทบาท
            $table->string('status')->nullable();

            $table->timestamps(); // คอลัมน์ created_at และ updated_at

            $table->unsignedBigInteger('user_id'); // คอลัมน์ user_id

            // สร้าง foreign key ที่เชื่อมโยงกับตาราง users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['user_id']); // ลบ foreign key
        });
        
        Schema::dropIfExists('customers'); // ลบตารางเมื่อมีการ rollback
    }
}

