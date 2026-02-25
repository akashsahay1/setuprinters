<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\LeaveApplication;

class PendingLeaveSeeder extends Seeder
{
    public function run(): void
    {
        $staffIds = Staff::active()->pluck('id')->take(30)->toArray();
        $types = ['casual', 'sick', 'earned', 'unpaid'];
        $reasons = [
            'Family function', 'Not feeling well', 'Personal work',
            'Medical appointment', 'Travel plans', 'Festival celebration',
            'Child school event', 'Home repair work', 'Bank work',
            'Court hearing',
        ];

        foreach ($staffIds as $sid) {
            LeaveApplication::create([
                'staff_id'   => $sid,
                'leave_date' => now()->addDays(rand(1, 30))->toDateString(),
                'leave_type' => $types[array_rand($types)],
                'reason'     => $reasons[array_rand($reasons)],
                'status'     => 'pending',
                'is_deleted' => false,
            ]);
        }
    }
}
