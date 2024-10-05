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
            $table->string('product_name');
            $table->json('product_images')->nullable(); // สามารถเก็บเป็น array (JSON)
            $table->integer('product_qty');
            $table->decimal('product_price', 8, 2);
            $table->text('product_description')->nullable();
            $table->string('product_category');
            $table->string('product_type');
            $table->unsignedBigInteger('seller_id'); // FK กับผู้ขาย
            $table->date('date_exp')->nullable();
            $table->string('location')->nullable();
            $table->string('condition');
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
