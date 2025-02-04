<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_type');
            $table->json('product_images')->nullable(); // สามารถเก็บเป็น array (JSON)
            $table->string('product_name');
            $table->string('product_category');
            $table->decimal('product_price', 8, 2);
            $table->text('product_description')->nullable();
            $table->date('date_exp')->nullable();
            $table->integer('product_qty');
            $table->string('product_location')->nullable();
            $table->string('product_condition')->nullable();
            $table->string('product_years')->nullable();
            $table->string('product_defect')->nullable();
            $table->string('tag')->nullable();
            $table->string('status')->nullable();


            $table->unsignedBigInteger('seller_id'); // FK กับผู้ขาย
            $table->timestamps();
            
            // สร้าง foreign key ให้ seller_id ชี้ไปที่ users table
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
