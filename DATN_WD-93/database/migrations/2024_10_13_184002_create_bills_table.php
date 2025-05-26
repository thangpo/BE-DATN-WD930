<?php

use App\Models\Bill;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('addressUser');
            $table->string('phoneUser');
            $table->string('nameUser');
            $table->string('emailUser');
            $table->double('totalPrice')->default(0);
            $table->string('status_bill')->default(Bill::CHO_XAC_NHAN);
            $table->string('status_payment_method')->default(Bill::CHUA_THANH_TOAN);
            $table->double('moneyProduct');
            $table->double('moneyShip');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
