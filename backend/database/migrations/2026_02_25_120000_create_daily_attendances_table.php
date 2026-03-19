<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('staff_id');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('total_hours', 5, 2)->default(0);
            $table->string('status')->default('absent'); // present, absent, half_day, leave, holiday
            $table->boolean('is_ot')->default(false);
            $table->decimal('ot_hours', 5, 2)->default(0);
            $table->decimal('base_wage', 12, 2)->default(0);
            $table->decimal('ot_wage', 12, 2)->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->unique(['staff_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_attendances');
    }
};
