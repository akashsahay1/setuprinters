<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        // Seed defaults
        $now = now();
        DB::table('staff_groups')->insert([
            ['name' => 'Office', 'is_deleted' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Driver', 'is_deleted' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Machine', 'is_deleted' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Guard', 'is_deleted' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_groups');
    }
};
