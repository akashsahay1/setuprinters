<?php

namespace App\Console\Commands;

use App\Models\DailyAttendance;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\ScannedBarcode;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessAttendanceCommand extends Command
{
    protected $signature = 'attendance:process {month} {year}';
    protected $description = 'Process OFFICE IN/OUT scans into daily_attendances';

    public function handle()
    {
        $month = (int) $this->argument('month');
        $year  = (int) $this->argument('year');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate   = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        $staffList = Staff::active()->get();
        $holidays  = Holiday::active()->get();
        $leaves    = LeaveApplication::active()
            ->where('status', 'granted')
            ->whereBetween('leave_date', [$startDate, $endDate])
            ->get();

        $this->info("Staff: {$staffList->count()}, Holidays: {$holidays->count()}, Leaves: {$leaves->count()}, Days: {$daysInMonth}");

        // Build holiday date set
        $holidayDates = [];
        foreach ($holidays as $h) {
            if ($h->is_yearly) {
                $hDate = Carbon::parse($h->date);
                if ($hDate->month == $month) {
                    $holidayDates[sprintf('%04d-%02d-%02d', $year, $month, $hDate->day)] = true;
                }
            } else {
                $holidayDates[$h->date->format('Y-m-d')] = true;
            }
        }

        // Build leave lookup
        $leaveMap = [];
        foreach ($leaves as $l) {
            $leaveMap[$l->staff_id][$l->leave_date->format('Y-m-d')] = $l->leave_type;
        }

        // Fetch OFFICE IN/OUT scans
        $scans = ScannedBarcode::active()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where(function($q) {
                $q->where('barcode', 'OFFICE IN')
                  ->orWhere('barcode', 'LIKE', 'OFFICE OUT%');
            })
            ->orderBy('created_at')
            ->get();

        $this->info("OFFICE scans found: {$scans->count()}");

        // Group scans
        $scanMap = [];
        foreach ($scans as $scan) {
            $uid  = $scan->user_id;
            $date = $scan->created_at->format('Y-m-d');
            $type = (trim($scan->barcode) === 'OFFICE OUT') ? 'out' : 'in';
            $scanMap[$uid][$date][$type][] = $scan->created_at;
        }

        $workingDays = 26;
        $processed = 0;

        foreach ($staffList as $staff) {
            $basicSalary = (float) $staff->basic_salary;
            $dailyWage   = $workingDays > 0 ? $basicSalary / $workingDays : 0;
            $shiftHours  = (int) ($staff->shift_hours ?: 8);
            $hourlyWage  = $shiftHours > 0 ? $dailyWage / $shiftHours : 0;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $dateObj = Carbon::parse($dateStr);
                $isSunday  = ($dateObj->dayOfWeek === 0);
                $isHoliday = isset($holidayDates[$dateStr]);

                $inScans  = $scanMap[$staff->id][$dateStr]['in'] ?? [];
                $outScans = $scanMap[$staff->id][$dateStr]['out'] ?? [];
                $hasIn  = count($inScans) > 0;
                $hasOut = count($outScans) > 0;

                $checkIn = null;
                $checkOut = null;
                $totalHours = 0;
                $status = 'absent';
                $isOt = false;
                $otHours = 0;
                $baseWage = 0;
                $otWage = 0;

                if ($hasIn && $hasOut) {
                    $checkIn  = $inScans[0];
                    $checkOut = end($outScans);
                    $totalHours = round(abs($checkOut->diffInMinutes($checkIn)) / 60, 2);
                    $status = $totalHours > 4 ? 'present' : 'half_day';

                    if ($staff->wage_calc_type === 'hour_based') {
                        $regular = min($totalHours, $shiftHours);
                        $excess  = max(0, $totalHours - $shiftHours);
                        $otHours = $this->capOt($staff, $excess);
                        $baseWage = round($hourlyWage * $regular, 2);
                        $otWage   = round($hourlyWage * $otHours, 2);
                        $isOt = $otHours > 0;
                    } else {
                        $baseWage = ($status === 'half_day') ? round($dailyWage / 2, 2) : round($dailyWage, 2);
                    }
                } elseif ($hasIn || $hasOut) {
                    $checkIn  = $hasIn ? $inScans[0] : null;
                    $checkOut = $hasOut ? end($outScans) : null;
                    $status   = 'half_day';
                    $baseWage = round($dailyWage / 2, 2);
                } elseif (isset($leaveMap[$staff->id][$dateStr])) {
                    $leaveType = $leaveMap[$staff->id][$dateStr];
                    $status    = 'leave';
                    $baseWage  = ($leaveType !== 'unpaid') ? round($dailyWage, 2) : 0;
                } elseif ($isSunday || $isHoliday) {
                    $status = 'holiday';
                } else {
                    $status = 'absent';
                }

                DailyAttendance::updateOrCreate(
                    ['staff_id' => $staff->id, 'date' => $dateStr],
                    [
                        'check_in'    => $checkIn ? $checkIn->format('H:i:s') : null,
                        'check_out'   => $checkOut ? $checkOut->format('H:i:s') : null,
                        'total_hours' => $totalHours,
                        'status'      => $status,
                        'is_ot'       => $isOt,
                        'ot_hours'    => $otHours,
                        'base_wage'   => $baseWage,
                        'ot_wage'     => $otWage,
                        'is_deleted'  => false,
                    ]
                );
                $processed++;
            }
        }

        $this->info("Processed {$processed} daily attendance records.");
    }

    private function capOt($staff, float $excessHours): float
    {
        if ($staff->ot_type === 'no_ot') return 0;
        if ($staff->ot_type === 'hours') return min($excessHours, (float) ($staff->ot_max_hours ?: 0));
        if ($staff->ot_type === 'minutes') return min($excessHours, ((float) ($staff->ot_max_minutes ?: 0)) / 60);
        return $excessHours;
    }
}
