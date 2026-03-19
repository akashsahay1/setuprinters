<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scanned_barcodes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id');
            $table->text('barcode');
            $table->longText('selfie');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scanned_barcodes');
    }
};
