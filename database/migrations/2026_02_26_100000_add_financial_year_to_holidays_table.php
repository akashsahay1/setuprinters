<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->string('financial_year', 10)->nullable()->after('is_yearly');
        });

        // Backfill existing holidays
        $holidays = DB::table('holidays')->get();
        foreach ($holidays as $h) {
            $date = \Carbon\Carbon::parse($h->date);
            $fy = $this->deriveFinancialYear($date);
            DB::table('holidays')->where('id', $h->id)->update(['financial_year' => $fy]);
        }
    }

    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn('financial_year');
        });
    }

    private function deriveFinancialYear(\Carbon\Carbon $date): string
    {
        if ($date->month >= 4) {
            return $date->year . '-' . ($date->year + 1);
        }
        return ($date->year - 1) . '-' . $date->year;
    }
};
