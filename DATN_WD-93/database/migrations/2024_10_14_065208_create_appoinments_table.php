<?php

use App\Models\Appoinment;
use App\Models\User;
use App\Models\Doctor;
use App\Models\AvailableTimeslot;
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
        Schema::create('appoinments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(Doctor::class)->constrained();
            $table->foreignIdFor(AvailableTimeslot::class)->constrained();
            $table->date('appointment_date');
            $table->text('notes')->nullable();
            $table->string('status_appoinment')->default(Appoinment::CHO_XAC_NHAN);
            $table->string('status_payment_method')->default(Appoinment::CHUA_THANH_TOAN);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appoinments');
    }
};
