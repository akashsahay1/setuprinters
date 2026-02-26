<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\ScannedBarcode;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Zxing\QrReader;

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

        // 2. If OFFICE OUT, process daily attendance for this user + today
        if (trim($request->barcode) === 'OFFICE OUT') {
            $date = $scan->created_at->format('Y-m-d');
            $result['attendance'] = $this->processSingleDay($staff, $date);
        }

        return response()->json($result);
    }

    public function scanCode(Request $request)
    {
        $request->validate([
            'selfie'   => 'required|image|max:10240',
            'qr_image' => 'required|image|max:10240',
            'barcode'  => 'required|string|in:OFFICE IN,OFFICE OUT',
        ]);

        // 1. Decode QR code from uploaded image
        $qrPath  = $request->file('qr_image')->getRealPath();
        $qrReader = new QrReader($qrPath);
        $qrValue  = $qrReader->text();

        if (!$qrValue || $qrValue === '') {
            return response()->json(['status' => false, 'message' => 'Could not read QR code from image'], 422);
        }

        // 2. Find staff by QR code value
        $staff = Staff::active()->where('qr_code', $qrValue)->first();
        if (!$staff) {
            return response()->json(['status' => false, 'message' => 'No staff found for this QR code'], 404);
        }

        // 3. Save selfie to public/uploads/selfies/
        $selfieFile = $request->file('selfie');
        $selfieName = 'selfie_' . $staff->id . '_' . time() . '.' . $selfieFile->getClientOriginalExtension();
        $selfieFile->move(public_path('uploads/selfies'), $selfieName);
        $selfiePath = 'uploads/selfies/' . $selfieName;

        // 4. Create scan record
        $barcode = trim($request->input('barcode'));
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
            'qr_value'   => $qrValue,
            'barcode'    => $barcode,
        ];

        // 5. If OFFICE OUT, process daily attendance
        if (trim($barcode) === 'OFFICE OUT') {
            $date = $scan->created_at->format('Y-m-d');
            $result['attendance'] = $this->processSingleDay($staff, $date);
        }

        return response()->json($result);
    }

    private function processSingleDay(Staff $staff, string $dateStr): array
    {
        $dateObj   = Carbon::parse($dateStr);
        $month     = $dateObj->month;
        $year      = $dateObj->year;
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

        // Fetch today's OFFICE IN/OUT scans for this user
        $scans = ScannedBarcode::active()
            ->where('user_id', $staff->id)
            ->whereDate('created_at', $dateStr)
            ->where(function ($q) {
                $q->where('barcode', 'OFFICE IN')
                  ->orWhere('barcode', 'LIKE', 'OFFICE OUT%');
            })
            ->orderBy('created_at')
            ->get();

        $inScans  = [];
        $outScans = [];
        foreach ($scans as $s) {
            if (trim($s->barcode) === 'OFFICE OUT') {
                $outScans[] = $s->created_at;
            } else {
                $inScans[] = $s->created_at;
            }
        }

        $hasIn  = count($inScans) > 0;
        $hasOut = count($outScans) > 0;

        // Wage calculation constants
        $workingDays = 26;
        $basicSalary = (float) $staff->basic_salary;
        $dailyWage   = $workingDays > 0 ? $basicSalary / $workingDays : 0;
        $shiftHours  = (int) ($staff->shift_hours ?: 8);
        $hourlyWage  = $shiftHours > 0 ? $dailyWage / $shiftHours : 0;

        $checkIn    = null;
        $checkOut   = null;
        $totalHours = 0;
        $status     = 'absent';
        $isOt       = false;
        $otHours    = 0;
        $baseWage   = 0;
        $otWage     = 0;

        if ($hasIn && $hasOut) {
            $checkIn    = $inScans[0];
            $checkOut   = end($outScans);
            $totalHours = round(abs($checkOut->diffInMinutes($checkIn)) / 60, 2);
            $status     = $totalHours > 4 ? 'present' : 'half_day';

            if ($isSunday || $isHoliday) {
                // Holiday/Sunday with attendance: holiday pay + all hours as OT
                $baseWage = round($dailyWage, 2);
                $otHours  = $this->capOt($staff, $totalHours);
                $otWage   = round($hourlyWage * $otHours, 2);
                $isOt     = $otHours > 0;
            } elseif ($staff->wage_calc_type === 'hour_based') {
                $regular  = min($totalHours, $shiftHours);
                $excess   = max(0, $totalHours - $shiftHours);
                $otHours  = $this->capOt($staff, $excess);
                $baseWage = round($hourlyWage * $regular, 2);
                $otWage   = round($hourlyWage * $otHours, 2);
                $isOt     = $otHours > 0;
            } else {
                $baseWage = ($status === 'half_day') ? round($dailyWage / 2, 2) : round($dailyWage, 2);
            }
        } elseif ($hasIn || $hasOut) {
            $checkIn  = $hasIn ? $inScans[0] : null;
            $checkOut = $hasOut ? end($outScans) : null;
            $status   = 'half_day';
            $baseWage = round($dailyWage, 2);

            if ($isSunday || $isHoliday) {
                // Holiday/Sunday with partial scan: holiday pay + partial hours as OT
                $partialHours = ($checkIn && $checkOut)
                    ? round(abs($checkOut->diffInMinutes($checkIn)) / 60, 2)
                    : round($shiftHours / 2, 2);
                $otHours = $this->capOt($staff, $partialHours);
                $otWage  = round($hourlyWage * $otHours, 2);
                $isOt    = $otHours > 0;
            }
        } elseif ($leave) {
            // Check 2-day paid leave limit per FY
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

        return [
            'date'        => $dateStr,
            'status'      => $status,
            'check_in'    => $checkIn ? $checkIn->format('H:i:s') : null,
            'check_out'   => $checkOut ? $checkOut->format('H:i:s') : null,
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
