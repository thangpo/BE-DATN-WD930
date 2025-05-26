<?php

use App\Models\Doctor;
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
        Schema::create('available_timeslots', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(model: Doctor::class)->constrained();
            $table->string('dayOfWeek');
            $table->time('startTime');
            $table->time('endTime');
            $table->date('date');
            $table->boolean('isAvailable')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_timeslots');
    }
};
