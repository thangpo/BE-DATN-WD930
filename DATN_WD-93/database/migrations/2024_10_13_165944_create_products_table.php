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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('idProduct')->unique();
            $table->string('name');
            $table->string('img')->nullable();
            $table->text('description')->nullable();
            $table->double('price');
            $table->double('discount')->nullable();
            $table->string('content')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedInteger('view')->default(0);
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null'); // Định nghĩa khóa ngoại category_id tham chiếu tới cột id của bảng categories.
            //Khi bản ghi bị xóa trong bảng categories, giá trị category_id trong bảng products sẽ được đặt về null.
            $table->boolean('is_type')->default(true);
            $table->boolean('is_new')->default(true);
            $table->boolean('is_hot')->default(true);
            $table->boolean('is_hot_deal')->default(true);
            $table->boolean('is_show_home')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
