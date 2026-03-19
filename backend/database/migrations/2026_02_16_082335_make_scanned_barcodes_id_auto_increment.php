<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old UUID primary key
        DB::statement('ALTER TABLE scanned_barcodes DROP PRIMARY KEY');
        DB::statement('ALTER TABLE scanned_barcodes DROP COLUMN id');

        // Add new auto-increment id as primary key
        DB::statement('ALTER TABLE scanned_barcodes ADD id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE scanned_barcodes DROP COLUMN id');
        DB::statement('ALTER TABLE scanned_barcodes ADD id VARCHAR(255) NOT NULL FIRST');
    }
};
