<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add a temporary column to hold the new staff id
        DB::statement('ALTER TABLE scanned_barcodes ADD COLUMN staff_id INT UNSIGNED NULL AFTER id');

        // Map user_id (UUID) to staff.id
        DB::statement('
            UPDATE scanned_barcodes
            INNER JOIN staff ON scanned_barcodes.user_id = staff.user_id
            SET scanned_barcodes.staff_id = staff.id
        ');

        // Drop the old user_id column
        DB::statement('ALTER TABLE scanned_barcodes DROP COLUMN user_id');

        // Rename staff_id to user_id
        DB::statement('ALTER TABLE scanned_barcodes CHANGE staff_id user_id INT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        // Add temp column for UUID
        DB::statement('ALTER TABLE scanned_barcodes ADD COLUMN old_user_id VARCHAR(255) NULL AFTER id');

        // Map back staff.id to staff.user_id (UUID)
        DB::statement('
            UPDATE scanned_barcodes
            INNER JOIN staff ON scanned_barcodes.user_id = staff.id
            SET scanned_barcodes.old_user_id = staff.user_id
        ');

        // Drop integer user_id
        DB::statement('ALTER TABLE scanned_barcodes DROP COLUMN user_id');

        // Rename back
        DB::statement('ALTER TABLE scanned_barcodes CHANGE old_user_id user_id VARCHAR(255) NOT NULL');
    }
};
