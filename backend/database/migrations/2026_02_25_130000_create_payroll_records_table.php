<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('staff_id');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('basic_amount', 12, 2)->default(0);
            $table->decimal('one_day_salary', 12, 2)->default(0);
            $table->unsignedTinyInteger('days_in_month')->default(0);
            $table->unsignedTinyInteger('days_absent')->default(0);
            $table->decimal('absent_deduction', 12, 2)->default(0);
            $table->unsignedTinyInteger('days_overtime')->default(0);
            $table->decimal('overtime_amount', 12, 2)->default(0);
            $table->decimal('advance_amount', 12, 2)->default(0);
            $table->decimal('final_pay', 12, 2)->default(0);
            $table->decimal('paid_in_bank', 12, 2)->default(0);
            $table->decimal('paid_pf', 12, 2)->default(0);
            $table->decimal('paid_cash', 12, 2)->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->unique(['staff_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
