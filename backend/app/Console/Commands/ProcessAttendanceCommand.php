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
    protected $description = 'Process LOGIN/LOGOUT scans into daily_attendances';

    public function handle()
    {
        $month = (int) $this->argument('month');
        $year  = (int) $this->argument('year');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate   = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        // Don't process future dates
        $today = Carbon::today()->format('Y-m-d');
        if ($endDate > $today) {
            $endDate = $today;
        }

        $staffList = Staff::active()->get();
        $holidays  = Holiday::active()->get();
        $leaves    = LeaveApplication::active()
            ->where('status', 'granted')
            ->whereBetween('leave_date', [$startDate, $endDate])
            ->get();

        $this->info("Staff: {$staffList->count()}, Holidays: {$holidays->count()}, Leaves: {$leaves->count()}");
        $this->info("Processing: {$startDate} to {$endDate}");

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

        $workingDays = 26;
        $processed = 0;

        foreach ($staffList as $staff) {
            // Fetch ALL LOGIN/LOGOUT scans chronologically
            // Include scans before the month to catch cross-day LOGINs
            $scans = ScannedBarcode::active()
                ->where('user_id', $staff->id)
                ->whereDate('created_at', '<=', $endDate)
                ->where(function($q) {
                    $q->where('barcode', 'LOGIN')
                      ->orWhere('barcode', 'LOGOUT');
                })
                ->orderBy('created_at')
                ->get();

            // Pair LOGIN→LOGOUT chronologically, split cross-day sessions at midnight
            $dailyData = $this->buildDailyData($scans, $startDate, $endDate);

            $basicSalary = (float) $staff->basic_salary;
            $dailyWage   = $workingDays > 0 ? $basicSalary / $workingDays : 0;
            $shiftHours  = (int) ($staff->shift_hours ?: 8);
            $otMaxHours  = $this->getOtMaxHours($staff);

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                if ($dateStr > $endDate) break;

                $dateObj   = Carbon::parse($dateStr);
                $isSunday  = ($dateObj->dayOfWeek === 0);
                $isHoliday = isset($holidayDates[$dateStr]);

                $dayInfo    = $dailyData[$dateStr] ?? null;
                $totalHours = $dayInfo ? round($dayInfo['hours'], 2) : 0;
                $checkIn    = $dayInfo ? $dayInfo['first_in'] : null;
                $checkOut   = $dayInfo ? $dayInfo['last_out'] : null;

                $status   = 'absent';
                $isOt     = false;
                $otHours  = 0;
                $otCount  = 0;
                $baseWage = 0;
                $otWage   = 0;

                if ($totalHours > 0) {
                    $status = 'present';

                    if ($isSunday || $isHoliday) {
                        // Holiday/Sunday: base holiday pay + all hours as OT
                        $baseWage = round($dailyWage, 2);
                        $otHours  = $totalHours;
                        $otCount  = $otMaxHours > 0 ? round($otHours / $otMaxHours, 2) : 0;
                        $otWage   = round($otCount * $dailyWage, 2);
                        $isOt     = $otHours > 0;
                    } elseif ($totalHours < $shiftHours) {
                        // Worked less than shift: proportional wage, no OT
                        $baseWage = round(($totalHours / $shiftHours) * $dailyWage, 2);
                    } else {
                        // Worked >= shift hours: full day + OT on excess
                        $baseWage = round($dailyWage, 2);
                        $otHours  = round($totalHours - $shiftHours, 2);
                        $otCount  = $otMaxHours > 0 ? round($otHours / $otMaxHours, 2) : 0;
                        $otWage   = round($otCount * $dailyWage, 2);
                        $isOt     = $otHours > 0;
                    }
                } elseif (isset($leaveMap[$staff->id][$dateStr])) {
                    $leaveType = $leaveMap[$staff->id][$dateStr];
                    $status    = 'leave';
                    $baseWage  = ($leaveType !== 'unpaid') ? round($dailyWage, 2) : 0;
                } elseif ($isSunday || $isHoliday) {
                    $status   = 'holiday';
                    $baseWage = round($dailyWage, 2);
                }

                DailyAttendance::updateOrCreate(
                    ['staff_id' => $staff->id, 'date' => $dateStr],
                    [
                        'check_in'    => $checkIn,
                        'check_out'   => $checkOut,
                        'total_hours' => $totalHours,
                        'status'      => $status,
                        'is_ot'       => $isOt,
                        'ot_hours'    => $otHours,
                        'ot_count'    => $otCount,
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

    /**
     * Pair LOGIN→LOGOUT scans chronologically and split cross-day sessions
     * at midnight boundaries. Returns per-day data within the given range.
     */
    private function buildDailyData($scans, string $rangeStart, string $rangeEnd): array
    {
        $dailyData = [];
        $pendingLogin = null;

        foreach ($scans as $scan) {
            $type = (trim($scan->barcode) === 'LOGOUT') ? 'out' : 'in';

            if ($type === 'in') {
                $pendingLogin = $scan->created_at;
            } elseif ($type === 'out' && $pendingLogin !== null) {
                $logout = $scan->created_at;
                $cursor = $pendingLogin->copy();

                while ($cursor->lt($logout)) {
                    $dayEnd     = $cursor->copy()->endOfDay();
                    $segmentEnd = $logout->lt($dayEnd) ? $logout : $dayEnd;
                    $dateStr    = $cursor->format('Y-m-d');

                    if ($dateStr >= $rangeStart && $dateStr <= $rangeEnd) {
                        $hours = round(abs($segmentEnd->diffInMinutes($cursor)) / 60, 2);

                        if (!isset($dailyData[$dateStr])) {
                            $dailyData[$dateStr] = [
                                'hours'    => 0,
                                'first_in' => $cursor->format('H:i:s'),
                                'last_out' => $segmentEnd->format('H:i:s'),
                            ];
                        }
                        $dailyData[$dateStr]['hours'] += $hours;
                        $dailyData[$dateStr]['last_out'] = $segmentEnd->format('H:i:s');
                    }

                    $cursor = $cursor->copy()->addDay()->startOfDay();
                }

                $pendingLogin = null;
            }
        }

        return $dailyData;
    }

    private function getOtMaxHours($staff): float
    {
        if ($staff->ot_type === 'no_ot') return 0;
        if ($staff->ot_type === 'hours') return (float) ($staff->ot_max_hours ?: 0);
        if ($staff->ot_type === 'minutes') return ((float) ($staff->ot_max_minutes ?: 0)) / 60;
        return 0;
    }
}
