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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('namePro');
            $table->string('imagePro');
            $table->double('pricePro'); //độ dài tổng là 8 và 2 chữ số thập phân.
            $table->unsignedInteger('quantity')->default(0);
            $table->double('total')->default(0); //độ dài tổng là 8 và 2 chữ số thập phân.
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
