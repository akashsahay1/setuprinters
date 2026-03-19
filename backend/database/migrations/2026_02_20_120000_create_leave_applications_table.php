<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('staff_id');
            $table->date('leave_date');
            $table->string('leave_type'); // casual, sick, earned, unpaid
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, granted, rejected
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};
