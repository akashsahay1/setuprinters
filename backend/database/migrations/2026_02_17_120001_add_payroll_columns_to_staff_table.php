<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('qr_code');
            $table->string('bank_account')->nullable()->after('group_id');
            $table->string('ifsc_code')->nullable()->after('bank_account');
            $table->decimal('basic_salary', 12, 2)->default(0)->after('ifsc_code');
            $table->string('wage_calc_type')->default('none')->after('basic_salary');
            $table->integer('shift_hours')->default(8)->after('wage_calc_type');
            $table->string('ot_type')->default('no_ot')->after('shift_hours');
            $table->integer('ot_max_hours')->nullable()->after('ot_type');
            $table->integer('ot_max_minutes')->nullable()->after('ot_max_hours');
            $table->boolean('pf_enabled')->default(false)->after('ot_max_minutes');
            $table->decimal('pf_percentage', 5, 2)->nullable()->after('pf_enabled');

            $table->foreign('group_id')->references('id')->on('staff_groups')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn([
                'group_id', 'bank_account', 'ifsc_code', 'basic_salary',
                'wage_calc_type', 'shift_hours', 'ot_type', 'ot_max_hours',
                'ot_max_minutes', 'pf_enabled', 'pf_percentage',
            ]);
        });
    }
};
