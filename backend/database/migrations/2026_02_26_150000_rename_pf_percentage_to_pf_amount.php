<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->renameColumn('pf_percentage', 'pf_amount');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->decimal('pf_amount', 10, 2)->nullable()->change();
        });

        // Reset existing percentage values to 0 since they are no longer percentages
        \DB::table('staff')->update(['pf_amount' => 0]);
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->renameColumn('pf_amount', 'pf_percentage');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->decimal('pf_percentage', 5, 2)->nullable()->change();
        });
    }
};
