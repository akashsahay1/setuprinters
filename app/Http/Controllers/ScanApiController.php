<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\ScannedBarcode;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\Laravel\Facades\Image;

class ScanApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:staff,id',
            'barcode' => 'required|string',
            'selfie'  => 'nullable|string',
        ]);

        $staff = Staff::active()->find($request->user_id);
        if (!$staff) {
            return response()->json(['status' => false, 'message' => 'Staff not found or inactive'], 404);
        }

        // 1. Save the scan
        $scan = ScannedBarcode::create([
            'user_id'    => $request->user_id,
            'barcode'    => $request->barcode,
            'selfie'     => $request->selfie ?? '',
            'is_deleted' => false,
        ]);

        $result = ['status' => true, 'message' => 'Scan recorded', 'scan_id' => $scan->id];

        // 2. If LOGOUT, process daily attendance for all affected days
        if (trim($request->barcode) === 'LOGOUT') {
            $result['attendance'] = $this->processAttendanceOnLogout($staff, $scan->created_at);
        }

        return response()->json($result);
    }

    public function scanCode(Request $request)
    {
        $request->validate([
            'userId'      => 'required|integer',
            'selfiePhoto' => 'required|image|max:10240',
            'barcode'     => 'required|string',
        ]);

        // 1. Find staff by ID
        $staff = Staff::active()->find($request->input('userId'));
        if (!$staff) {
            return response()->json(['status' => false, 'message' => 'Staff not found or inactive'], 404);
        }

        // 2. Save selfie to public/uploads/selfies/
        $selfieFile = $request->file('selfiePhoto');
        $selfieName = 'selfie_' . $staff->id . '_' . time() . '.' . $selfieFile->getClientOriginalExtension();
        $selfieFile->move(public_path('uploads/selfies'), $selfieName);
        $selfiePath = 'uploads/selfies/' . $selfieName;

        // Generate 50x50 thumbnail
        $thumbDir = public_path('uploads/selfies/thumbs');
        if (!file_exists($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }
        $thumb = Image::read(public_path('uploads/selfies/' . $selfieName));
        $thumb->cover(50, 50);
        $thumb->save($thumbDir . '/' . $selfieName);

        // 4. Check if this barcode type already exists for this staff today
        $barcode = trim($request->input('barcode'));
        $today = now()->format('Y-m-d');

        $existingScan = ScannedBarcode::active()
            ->where('user_id', $staff->id)
            ->where('barcode', $barcode)
            ->whereDate('created_at', $today)
            ->first();

        if ($existingScan) {
            return response()->json([
                'status'     => true,
                'message'    => 'Already recorded for today',
                'scan_id'    => $existingScan->id,
                'staff_name' => $staff->full_name,
                'barcode'    => $barcode,
            ]);
        }

        // 5. Create scan record
        $scan = ScannedBarcode::create([
            'user_id'    => $staff->id,
            'barcode'    => $barcode,
            'selfie'     => $selfiePath,
            'is_deleted' => false,
        ]);

        $result = [
            'status'     => true,
            'message'    => 'Scan recorded',
            'scan_id'    => $scan->id,
            'staff_name' => $staff->full_name,
            'barcode'    => $barcode,
        ];

        // 6. If LOGOUT, process daily attendance for all affected days
        if (trim($barcode) === 'LOGOUT') {
            $result['attendance'] = $this->processAttendanceOnLogout($staff, $scan->created_at);
        }

        return response()->json($result);
    }

    /**
     * Process attendance on LOGOUT: pair all LOGIN→LOGOUT scans chronologically,
     * split cross-day sessions at midnight, and update daily_attendances.
     */
    private function processAttendanceOnLogout(Staff $staff, Carbon $logoutTime): array
    {
        // Fetch LOGIN/LOGOUT scans from last 30 days to pair chronologically
        $scans = ScannedBarcode::active()
            ->where('user_id', $staff->id)
            ->where('created_at', '>=', $logoutTime->copy()->subDays(30))
            ->where(function($q) {
                $q->where('barcode', 'LOGIN')->orWhere('barcode', 'LOGOUT');
            })
            ->orderBy('created_at')
            ->get();

        // Pair LOGIN→LOGOUT and split cross-day sessions at midnight
        $dailyData = $this->buildDailyData($scans);

        // Update daily_attendance for all days that have session data
        $results = [];
        foreach ($dailyData as $dateStr => $dayInfo) {
            $results[$dateStr] = $this->upsertDayAttendance($staff, $dateStr, $dayInfo);
        }

        $logoutDate = $logoutTime->format('Y-m-d');
        return $results[$logoutDate] ?? ['date' => $logoutDate, 'status' => 'absent'];
    }

    /**
     * Pair LOGIN→LOGOUT scans chronologically and split cross-day sessions
     * at midnight boundaries. Returns per-day hours with first_in/last_out.
     */
    private function buildDailyData($scans): array
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

                    $cursor = $cursor->copy()->addDay()->startOfDay();
                }

                $pendingLogin = null;
            }
        }

        return $dailyData;
    }

    /**
     * Upsert daily_attendance for a single day using pre-computed session data.
     */
    private function upsertDayAttendance(Staff $staff, string $dateStr, array $dayInfo): array
    {
        $dateObj   = Carbon::parse($dateStr);
        $month     = $dateObj->month;
        $isSunday  = ($dateObj->dayOfWeek === 0);

        // Check holiday
        $isHoliday = Holiday::active()
            ->where(function ($q) use ($dateStr, $month, $dateObj) {
                $q->where(function ($q2) use ($dateStr) {
                    $q2->where('is_yearly', false)->whereDate('date', $dateStr);
                })->orWhere(function ($q2) use ($month, $dateObj) {
                    $q2->where('is_yearly', true)
                       ->whereMonth('date', $month)
                       ->whereDay('date', $dateObj->day);
                });
            })
            ->exists();

        // Check approved leave
        $leave = LeaveApplication::active()
            ->where('staff_id', $staff->id)
            ->where('status', 'granted')
            ->whereDate('leave_date', $dateStr)
            ->first();

        $totalHours = round($dayInfo['hours'], 2);
        $checkIn    = $dayInfo['first_in'];
        $checkOut   = $dayInfo['last_out'];

        // Wage calculation constants
        $workingDays = 26;
        $basicSalary = (float) $staff->basic_salary;
        $dailyWage   = $workingDays > 0 ? $basicSalary / $workingDays : 0;
        $shiftHours  = (int) ($staff->shift_hours ?: 8);
        $hourlyWage  = $shiftHours > 0 ? $dailyWage / $shiftHours : 0;

        $status   = 'absent';
        $isOt     = false;
        $otHours  = 0;
        $otCount  = 0;
        $baseWage = 0;
        $otWage   = 0;

        if ($totalHours > 0) {
            $status = $totalHours > 4 ? 'present' : 'half_day';

            if ($isSunday || $isHoliday) {
                // Holiday/Sunday with attendance: holiday pay + all hours as OT
                $baseWage = round($dailyWage, 2);
                $otHours  = $this->capOt($staff, $totalHours);
                $otCount  = $this->getOtMaxHours($staff) > 0 ? round($otHours / $this->getOtMaxHours($staff), 2) : 0;
                $otWage   = round($hourlyWage * $otHours, 2);
                $isOt     = $otHours > 0;
            } elseif ($staff->wage_calc_type === 'hour_based') {
                $regular  = min($totalHours, $shiftHours);
                $excess   = max(0, $totalHours - $shiftHours);
                $otHours  = $this->capOt($staff, $excess);
                $otCount  = $this->getOtMaxHours($staff) > 0 ? round($otHours / $this->getOtMaxHours($staff), 2) : 0;
                $baseWage = round($hourlyWage * $regular, 2);
                $otWage   = round($hourlyWage * $otHours, 2);
                $isOt     = $otHours > 0;
            } else {
                $baseWage = ($status === 'half_day') ? round($dailyWage / 2, 2) : round($dailyWage, 2);
            }
        } elseif ($leave) {
            $isPaid = ($leave->leave_type !== 'unpaid') && ($this->countPaidLeavesInFy($staff->id, $dateStr) <= 2);

            if ($isPaid) {
                $status   = 'present';
                $baseWage = round($dailyWage, 2);
            } else {
                $status   = 'leave';
                $baseWage = 0;
            }
        } elseif ($isSunday || $isHoliday) {
            $status   = 'holiday';
            $baseWage = round($dailyWage, 2);
        }

        // Upsert daily attendance
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

        return [
            'date'        => $dateStr,
            'status'      => $status,
            'check_in'    => $checkIn,
            'check_out'   => $checkOut,
            'total_hours' => $totalHours,
        ];
    }

    private function capOt(Staff $staff, float $excessHours): float
    {
        if ($staff->ot_type === 'no_ot') return 0;
        if ($staff->ot_type === 'hours') return min($excessHours, (float) ($staff->ot_max_hours ?: 0));
        if ($staff->ot_type === 'minutes') return min($excessHours, ((float) ($staff->ot_max_minutes ?: 0)) / 60);
        return $excessHours;
    }

    private function getOtMaxHours(Staff $staff): float
    {
        if ($staff->ot_type === 'no_ot') return 0;
        if ($staff->ot_type === 'hours') return (float) ($staff->ot_max_hours ?: 0);
        if ($staff->ot_type === 'minutes') return ((float) ($staff->ot_max_minutes ?: 0)) / 60;
        return 0;
    }

    private function countPaidLeavesInFy(int $staffId, string $dateStr): int
    {
        $date = Carbon::parse($dateStr);
        $fyStart = $date->month >= 4
            ? Carbon::create($date->year, 4, 1)
            : Carbon::create($date->year - 1, 4, 1);
        $fyEnd = $fyStart->copy()->addYear()->subDay();

        return LeaveApplication::active()
            ->where('staff_id', $staffId)
            ->where('status', 'granted')
            ->where('leave_type', '!=', 'unpaid')
            ->whereBetween('leave_date', [$fyStart->format('Y-m-d'), $fyEnd->format('Y-m-d')])
            ->count();
    }
}
