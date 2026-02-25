<?php

namespace App\Console\Commands;

use App\Models\DailyAttendance;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAbsentCommand extends Command
{
    protected $signature = 'attendance:mark-absent {--date= : Date to process (Y-m-d), defaults to today}';
    protected $description = 'Mark staff without scans as absent/holiday/leave and insert into daily_attendances';

    public function handle()
    {
        $dateStr = $this->option('date') ?: Carbon::today()->format('Y-m-d');
        $dateObj = Carbon::parse($dateStr);
        $isSunday = ($dateObj->dayOfWeek === 0);

        // Check if date is a holiday
        $isHoliday = Holiday::active()
            ->where(function ($q) use ($dateStr, $dateObj) {
                $q->where(function ($q2) use ($dateStr) {
                    $q2->where('is_yearly', false)->whereDate('date', $dateStr);
                })->orWhere(function ($q2) use ($dateObj) {
                    $q2->where('is_yearly', true)
                       ->whereMonth('date', $dateObj->month)
                       ->whereDay('date', $dateObj->day);
                });
            })
            ->exists();

        // Get staff who already have a daily_attendances row for this date
        $processedStaffIds = DailyAttendance::where('date', $dateStr)
            ->pluck('staff_id')
            ->toArray();

        // Get all active staff NOT yet processed
        $unprocessedStaff = Staff::active()
            ->whereNotIn('id', $processedStaffIds)
            ->get();

        if ($unprocessedStaff->isEmpty()) {
            $this->info("All staff already processed for {$dateStr}.");
            return;
        }

        // Get approved leaves for this date
        $leaveMap = LeaveApplication::active()
            ->where('status', 'granted')
            ->whereDate('leave_date', $dateStr)
            ->get()
            ->keyBy('staff_id');

        $workingDays = 26;
        $marked = 0;

        foreach ($unprocessedStaff as $staff) {
            $dailyWage = $workingDays > 0 ? ((float) $staff->basic_salary) / $workingDays : 0;

            if ($isSunday || $isHoliday) {
                $dayStatus = 'holiday';
                $baseWage  = 0;
            } elseif (isset($leaveMap[$staff->id])) {
                $dayStatus = 'leave';
                $leaveApp  = $leaveMap[$staff->id];
                $baseWage  = ($leaveApp->leave_type !== 'unpaid') ? round($dailyWage, 2) : 0;
            } else {
                $dayStatus = 'absent';
                $baseWage  = 0;
            }

            DailyAttendance::create([
                'staff_id'    => $staff->id,
                'date'        => $dateStr,
                'check_in'    => null,
                'check_out'   => null,
                'total_hours' => 0,
                'status'      => $dayStatus,
                'is_ot'       => false,
                'ot_hours'    => 0,
                'base_wage'   => $baseWage,
                'ot_wage'     => 0,
                'is_deleted'  => false,
            ]);

            $marked++;
        }

        $this->info("Marked {$marked} staff for {$dateStr} (Sunday/Holiday: " . ($isSunday || $isHoliday ? 'Yes' : 'No') . ").");
    }
}
