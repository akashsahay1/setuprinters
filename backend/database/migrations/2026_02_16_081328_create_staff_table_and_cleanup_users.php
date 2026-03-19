<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Create staff table
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->integer('employee_id');
            $table->string('full_name');
            $table->string('phone_number');
            $table->string('phone_number_2')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->longText('profile_photo')->nullable();
            $table->boolean('is_deleted');
            $table->timestamps();
        });

        // Step 2: Move all users except id=1 to staff table
        $users = DB::table('users')->where('id', '!=', 1)->get();

        foreach ($users as $user) {
            DB::table('staff')->insert([
                'user_id' => $user->user_id,
                'employee_id' => $user->employee_id,
                'full_name' => $user->full_name,
                'phone_number' => $user->phone_number,
                'phone_number_2' => $user->phone_number_2,
                'email' => $user->email,
                'address' => $user->address,
                'profile_photo' => $user->profile_photo,
                'is_deleted' => $user->is_deleted,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }

        // Step 3: Delete all users except id=1
        DB::table('users')->where('id', '!=', 1)->delete();

        // Step 4: Drop user_id, employee_id, user_role columns from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'employee_id', 'user_role']);
        });
    }

    public function down(): void
    {
        // Re-add dropped columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_id')->unique()->after('id');
            $table->integer('employee_id')->after('user_id');
            $table->string('user_role')->after('password');
        });

        // Move staff back to users
        $staff = DB::table('staff')->get();
        foreach ($staff as $member) {
            DB::table('users')->insert([
                'user_id' => $member->user_id,
                'employee_id' => $member->employee_id,
                'full_name' => $member->full_name,
                'phone_number' => $member->phone_number,
                'phone_number_2' => $member->phone_number_2,
                'email' => $member->email,
                'address' => $member->address,
                'profile_photo' => $member->profile_photo,
                'is_deleted' => $member->is_deleted,
                'user_role' => '',
                'password' => '',
                'created_at' => $member->created_at,
                'updated_at' => $member->updated_at,
            ]);
        }

        Schema::dropIfExists('staff');
    }
};
