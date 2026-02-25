<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the current `id` primary key column
        DB::statement('ALTER TABLE staff DROP COLUMN id');

        // Make employee_id the primary key with auto_increment
        DB::statement('ALTER TABLE staff MODIFY employee_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // Set auto_increment to start after the max existing value
        $max = DB::table('staff')->max('employee_id') ?? 0;
        DB::statement('ALTER TABLE staff AUTO_INCREMENT = ' . ($max + 1));
    }

    public function down(): void
    {
        // Remove auto_increment and primary key from employee_id
        DB::statement('ALTER TABLE staff MODIFY employee_id INT NOT NULL');
        DB::statement('ALTER TABLE staff DROP PRIMARY KEY');

        // Re-add the id column as primary key
        DB::statement('ALTER TABLE staff ADD id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
    }
};
