<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\PayrollRecord;
use App\Models\ScannedBarcode;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\StaffGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AjaxController extends Controller
{
    public function index(Request $request)
    {

        if($request->has('userlogin')){
            $user_email = $request->input('user_email');
            $user_password = $request->input('user_password');

            if (Auth::attempt(['email' => $user_email, 'password' => $user_password, 'is_deleted' => false])) {
                $request->session()->regenerate();
                return response()->json(['status' => 1, 'message' => 'Login successful.']);
            } else {
                return response()->json(['status' => 0, 'message' => 'Invalid email or password.']);
            }
        }

        if($request->has('save_user')){
            try {
                $userData = [
                    'user_id' => 'USR' . strtoupper(Str::random(8)),
                    'employee_id' => $request->employee_id,
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'user_role' => $request->user_role,
                    'phone_number' => $request->phone_number,
                    'phone_number_2' => $request->phone_number_2,
                    'address' => $request->address,
                    'password' => Hash::make($request->password),
                    'is_deleted' => false,
                ];

                if ($request->hasFile('profile_photo')) {
                    $photo = $request->file('profile_photo');
                    $filename = 'profile_' . time() . '.' . $photo->getClientOriginalExtension();
                    $photo->move(public_path('uploads/profiles'), $filename);
                    $userData['profile_photo'] = 'uploads/profiles/' . $filename;
                }

                $user = User::create($userData);

                return response()->json([
                    'status' => true,
                    'message' => 'User created successfully',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create user',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('update_staff')){
            try {
                $staff = Staff::active()->findOrFail($request->staff_id);

                $staff->fill([
                    'full_name' => $request->full_name,
                    'phone_number' => $request->phone_number,
                    'phone_number_2' => $request->phone_number_2,
                    'email' => $request->email,
                    'address' => $request->address,
                    'qr_code' => $request->qr_code,
                    'group_id' => $request->group_id ?: null,
                    'account_name' => $request->account_name,
                    'bank_account' => $request->bank_account,
                    'ifsc_code' => $request->ifsc_code,
                    'basic_salary' => $request->basic_salary ?? 0,
                    'wage_calc_type' => $request->wage_calc_type ?? 'none',
                    'shift_hours' => $request->shift_hours ?? 8,
                    'ot_type' => $request->ot_type ?? 'no_ot',
                    'ot_max_hours' => $request->ot_max_hours,
                    'ot_max_minutes' => $request->ot_max_minutes,
                    'pf_enabled' => $request->pf_enabled === '1' || $request->pf_enabled === 'true',
                    'pf_percentage' => $request->pf_percentage,
                ]);

                if ($request->hasFile('thumbnail')) {
                    $photo = $request->file('thumbnail');
                    $filename = 'staff_' . $staff->id . '_' . time() . '.' . $photo->getClientOriginalExtension();
                    $photo->move(public_path('uploads/staff'), $filename);
                    $staff->profile_photo = 'uploads/staff/' . $filename;
                } elseif ($request->input('remove_thumbnail') === '1') {
                    $staff->profile_photo = null;
                }

                $staff->save();
                return response()->json(['status' => true, 'message' => 'Staff updated successfully']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update staff',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('create_staff')){
            try {
                $staff = new Staff();
                $staff->fill([
                    'full_name' => $request->full_name,
                    'phone_number' => $request->phone_number,
                    'phone_number_2' => $request->phone_number_2,
                    'email' => $request->email,
                    'address' => $request->address,
                    'qr_code' => $request->qr_code,
                    'group_id' => $request->group_id ?: null,
                    'account_name' => $request->account_name,
                    'bank_account' => $request->bank_account,
                    'ifsc_code' => $request->ifsc_code,
                    'basic_salary' => $request->basic_salary ?? 0,
                    'wage_calc_type' => $request->wage_calc_type ?? 'none',
                    'shift_hours' => $request->shift_hours ?? 8,
                    'ot_type' => $request->ot_type ?? 'no_ot',
                    'ot_max_hours' => $request->ot_max_hours,
                    'ot_max_minutes' => $request->ot_max_minutes,
                    'pf_enabled' => $request->pf_enabled === '1' || $request->pf_enabled === 'true',
                    'pf_percentage' => $request->pf_percentage,
                    'is_deleted' => false,
                ]);

                if ($request->hasFile('thumbnail')) {
                    $photo = $request->file('thumbnail');
                    $filename = 'staff_' . time() . '.' . $photo->getClientOriginalExtension();
                    $photo->move(public_path('uploads/staff'), $filename);
                    $staff->profile_photo = 'uploads/staff/' . $filename;
                }

                $staff->save();
                return response()->json(['status' => true, 'message' => 'Staff created successfully', 'id' => $staff->id]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create staff',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('delete_staff')){
            try {
                $user = Auth::user();
                if (!$user || !Hash::check($request->password, $user->password)) {
                    return response()->json(['status' => false, 'message' => 'Incorrect password']);
                }
                Staff::where('id', $request->staff_id)->update(['is_deleted' => true]);
                return response()->json(['status' => true, 'message' => 'Staff deleted successfully']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to delete staff',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('save_staff_group')){
            try {
                $group = StaffGroup::create([
                    'name' => $request->group_name,
                    'is_deleted' => false,
                ]);
                return response()->json(['status' => true, 'message' => 'Group created', 'group' => $group]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create group',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('update_staff_group')){
            try {
                StaffGroup::where('id', $request->group_id)->update(['name' => $request->group_name]);
                return response()->json(['status' => true, 'message' => 'Group updated']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update group',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('delete_staff_group')){
            try {
                $user = Auth::user();
                if (!$user || !Hash::check($request->password, $user->password)) {
                    return response()->json(['status' => false, 'message' => 'Incorrect password']);
                }
                StaffGroup::where('id', $request->group_id)->update(['is_deleted' => true]);
                return response()->json(['status' => true, 'message' => 'Group deleted']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to delete group',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('delete_user')){
            try {
                $user = Auth::user();
                if (!$user || !Hash::check($request->password, $user->password)) {
                    return response()->json(['status' => false, 'message' => 'Incorrect password']);
                }
                User::where('id', $request->user_id)->update(['is_deleted' => true]);
                return response()->json(['status' => true, 'message' => 'User deleted successfully']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to delete user',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('migrate_payroll_data')){
            try {
                $items = $request->input('items', []);
                $groups = StaffGroup::active()->pluck('id', 'name')->toArray();
                $migrated = 0;

                foreach ($items as $item) {
                    $staffId = $item['staff_id'] ?? null;
                    if (!$staffId) continue;

                    $staff = Staff::active()->find($staffId);
                    if (!$staff) continue;

                    $groupName = $item['group'] ?? '';
                    $groupId = $groups[ucfirst(strtolower($groupName))] ?? $groups[$groupName] ?? null;

                    $staff->update([
                        'group_id' => $groupId,
                        'bank_account' => $item['account'] ?? null,
                        'ifsc_code' => $item['ifsc'] ?? null,
                        'basic_salary' => $item['salary'] ?? 0,
                        'wage_calc_type' => $item['wageType'] ?? 'none',
                        'shift_hours' => $item['shiftHours'] ?? 8,
                        'ot_type' => $item['otType'] ?? 'no_ot',
                        'ot_max_hours' => $item['otHours'] ?? null,
                        'ot_max_minutes' => $item['otMin'] ?? null,
                        'pf_enabled' => !empty($item['pfEnabled']),
                        'pf_percentage' => $item['pfPct'] ?? null,
                    ]);
                    $migrated++;
                }

                return response()->json(['status' => true, 'message' => "Migrated $migrated staff records"]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Migration failed',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('generate_apk')){
            $apkPath = public_path('assets/apk/setuprinters.apk');
            if (!file_exists($apkPath)) {
                return response()->json(['status' => false, 'message' => 'APK file not found.'], 404);
            }
            return response()->download($apkPath, 'setuprinters.apk', [
                'Content-Type' => 'application/vnd.android.package-archive',
            ]);
        }

        if($request->has('change_password')){
            try {
                $user = Auth::user();

                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json(['status' => false, 'message' => 'Current password is incorrect.']);
                }

                if (strlen($request->new_password) < 8) {
                    return response()->json(['status' => false, 'message' => 'New password must be at least 8 characters.']);
                }

                if ($request->new_password !== $request->new_password_confirmation) {
                    return response()->json(['status' => false, 'message' => 'New password and confirmation do not match.']);
                }

                $user->password = Hash::make($request->new_password);
                $user->save();

                return response()->json(['status' => true, 'message' => 'Password changed successfully.']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to change password.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('save_holiday')){
            try {
                $holiday = Holiday::create([
                    'name' => $request->holiday_name,
                    'date' => $request->holiday_date,
                    'is_yearly' => $request->holiday_is_yearly === '1' || $request->holiday_is_yearly === 'true',
                    'financial_year' => Holiday::deriveFinancialYear($request->holiday_date),
                    'is_deleted' => false,
                ]);
                return response()->json(['status' => true, 'message' => 'Holiday added', 'holiday' => $holiday]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to add holiday',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('update_holiday')){
            try {
                Holiday::where('id', $request->holiday_id)->update([
                    'name' => $request->holiday_name,
                    'date' => $request->holiday_date,
                    'financial_year' => Holiday::deriveFinancialYear($request->holiday_date),
                ]);
                return response()->json(['status' => true, 'message' => 'Holiday updated']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update holiday',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('delete_holiday')){
            try {
                $user = Auth::user();
                if (!$user || !Hash::check($request->password, $user->password)) {
                    return response()->json(['status' => false, 'message' => 'Incorrect password']);
                }
                Holiday::where('id', $request->holiday_id)->delete();
                return response()->json(['status' => true, 'message' => 'Holiday deleted']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to delete holiday',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('purge_fy_data')){
            try {
                $user = Auth::user();
                if (!$user || !Hash::check($request->password, $user->password)) {
                    return response()->json(['status' => false, 'message' => 'Incorrect password']);
                }

                $fy = $request->financial_year;
                if ($fy === Holiday::currentFinancialYear()) {
                    return response()->json(['status' => false, 'message' => 'Cannot purge data for the current financial year']);
                }

                // Parse FY string "2024-2025" → start 2024-04-01, end 2025-03-31
                $parts = explode('-', $fy);
                if (count($parts) !== 2) {
                    return response()->json(['status' => false, 'message' => 'Invalid financial year format']);
                }
                $startYear = (int)$parts[0];
                $endYear = (int)$parts[1];
                $fyStart = "{$startYear}-04-01";
                $fyEnd = "{$endYear}-03-31";

                $deleted = DB::transaction(function() use ($fy, $fyStart, $fyEnd, $startYear, $endYear) {
                    $holidays = Holiday::where('financial_year', $fy)->delete();

                    $attendances = DailyAttendance::whereBetween('date', [$fyStart, $fyEnd])->delete();

                    $scans = ScannedBarcode::whereDate('created_at', '>=', $fyStart)
                        ->whereDate('created_at', '<=', $fyEnd)->delete();

                    $leaves = LeaveApplication::whereBetween('leave_date', [$fyStart, $fyEnd])->delete();

                    $payroll = PayrollRecord::where(function($q) use ($startYear, $endYear) {
                        $q->where(function($q2) use ($startYear) {
                            $q2->where('year', $startYear)->where('month', '>=', 4);
                        })->orWhere(function($q2) use ($endYear) {
                            $q2->where('year', $endYear)->where('month', '<=', 3);
                        });
                    })->delete();

                    return [
                        'holidays' => $holidays,
                        'attendances' => $attendances,
                        'scans' => $scans,
                        'leaves' => $leaves,
                        'payroll' => $payroll,
                    ];
                });

                return response()->json(['status' => true, 'message' => 'Data purged successfully', 'deleted' => $deleted]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to purge data',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('fetch_scans')){
            try {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                $query = ScannedBarcode::active()
                    ->select('id', 'user_id', 'barcode', 'created_at')
                    ->with('staff:id,full_name,profile_photo,group_id,qr_code')
                    ->orderBy('created_at', 'asc');

                if ($startDate && $endDate) {
                    $query->whereDate('created_at', '>=', $startDate)
                          ->whereDate('created_at', '<=', $endDate);
                } elseif ($startDate) {
                    $query->whereDate('created_at', $startDate);
                } else {
                    $query->whereDate('created_at', today());
                }

                if ($request->filled('group_id')) {
                    $query->whereHas('staff', function ($q) use ($request) {
                        $q->where('group_id', $request->group_id);
                    });
                }

                if ($request->filled('user_id')) {
                    $query->where('user_id', $request->user_id);
                }

                $scans = $query->get()->map(function ($scan) {
                    $scan->formatted_date = $scan->created_at->format('d-m-Y H:i A');
                    return $scan;
                });

                return response()->json(['status' => true, 'scans' => $scans]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to fetch scans',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('fetch_leaves')){
            try {
                $status = $request->input('status', 'pending');
                $leaves = LeaveApplication::active()
                    ->where('status', $status)
                    ->with('staff:id,full_name')
                    ->orderBy('leave_date', 'desc')
                    ->get();

                return response()->json(['status' => true, 'leaves' => $leaves]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to fetch leaves',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('bulk_approve_leave')){
            try {
                $ids = $request->input('leave_ids', []);

                // Fetch the leaves before updating so we can update daily_attendances
                $leaves = LeaveApplication::active()
                    ->where('status', 'pending')
                    ->whereIn('id', $ids)
                    ->get();

                $count = LeaveApplication::active()
                    ->where('status', 'pending')
                    ->whereIn('id', $ids)
                    ->update(['status' => 'granted']);

                // Auto-update daily_attendances for approved leaves
                foreach ($leaves as $leave) {
                    $staff = Staff::find($leave->staff_id);
                    $dailyWage = $staff ? ((float) $staff->basic_salary) / 26 : 0;
                    $baseWage = ($leave->leave_type !== 'unpaid') ? round($dailyWage, 2) : 0;

                    DailyAttendance::where('staff_id', $leave->staff_id)
                        ->where('date', $leave->leave_date->format('Y-m-d'))
                        ->where('status', 'absent')
                        ->update(['status' => 'leave', 'base_wage' => $baseWage]);
                }

                return response()->json(['status' => true, 'message' => $count . ' leave(s) approved successfully']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to bulk approve',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('bulk_reject_leave')){
            try {
                $ids = $request->input('leave_ids', []);
                $count = LeaveApplication::active()
                    ->where('status', 'pending')
                    ->whereIn('id', $ids)
                    ->update(['status' => 'rejected']);

                return response()->json(['status' => true, 'message' => $count . ' leave(s) rejected successfully']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to bulk reject',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('approve_leave')){
            try {
                $leave = LeaveApplication::active()
                    ->where('status', 'pending')
                    ->findOrFail($request->leave_id);

                $leave->update(['status' => 'granted']);

                // Auto-update daily_attendances if absent row exists for this date
                $staff = Staff::find($leave->staff_id);
                if ($staff) {
                    $dailyWage = ((float) $staff->basic_salary) / 26;
                    $baseWage = ($leave->leave_type !== 'unpaid') ? round($dailyWage, 2) : 0;

                    DailyAttendance::where('staff_id', $leave->staff_id)
                        ->where('date', $leave->leave_date->format('Y-m-d'))
                        ->where('status', 'absent')
                        ->update(['status' => 'leave', 'base_wage' => $baseWage]);
                }

                return response()->json(['status' => true, 'message' => 'Leave approved successfully']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to approve leave',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('reject_leave')){
            try {
                $leave = LeaveApplication::active()
                    ->where('status', 'pending')
                    ->findOrFail($request->leave_id);

                $leave->update(['status' => 'rejected']);

                return response()->json(['status' => true, 'message' => 'Leave rejected successfully']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to reject leave',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('process_attendance')){
            try {
                $month = (int) $request->input('month');
                $year  = (int) $request->input('year');
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $startDate = sprintf('%04d-%02d-01', $year, $month);
                $endDate   = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

                $staffList = Staff::active()->get();
                $holidays  = Holiday::active()->get();
                $leaves    = LeaveApplication::active()
                    ->where('status', 'granted')
                    ->whereBetween('leave_date', [$startDate, $endDate])
                    ->get();

                // Build holiday date set
                $holidayDates = [];
                foreach ($holidays as $h) {
                    if ($h->is_yearly) {
                        $hDate = \Carbon\Carbon::parse($h->date);
                        if ($hDate->month == $month) {
                            $holidayDates[sprintf('%04d-%02d-%02d', $year, $month, $hDate->day)] = true;
                        }
                    } else {
                        $holidayDates[$h->date->format('Y-m-d')] = true;
                    }
                }

                // Build leave lookup: [staff_id][date_string] = leave_type
                $leaveMap = [];
                foreach ($leaves as $l) {
                    $leaveMap[$l->staff_id][$l->leave_date->format('Y-m-d')] = $l->leave_type;
                }

                // Fetch all OFFICE IN/OUT scans for the month
                $scans = ScannedBarcode::active()
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->where(function($q) {
                        $q->where('barcode', 'OFFICE IN')
                          ->orWhere('barcode', 'LIKE', 'OFFICE OUT%');
                    })
                    ->orderBy('created_at')
                    ->get();

                // Group scans: [user_id][date_string] = ['in' => [...], 'out' => [...]]
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
                        $dateObj = \Carbon\Carbon::parse($dateStr);
                        $isSunday  = ($dateObj->dayOfWeek === 0);
                        $isHoliday = isset($holidayDates[$dateStr]);

                        $inScans  = $scanMap[$staff->id][$dateStr]['in'] ?? [];
                        $outScans = $scanMap[$staff->id][$dateStr]['out'] ?? [];
                        $hasIn  = count($inScans) > 0;
                        $hasOut = count($outScans) > 0;

                        $checkIn    = null;
                        $checkOut   = null;
                        $totalHours = 0;
                        $status     = 'absent';
                        $isOt       = false;
                        $otHours    = 0;
                        $baseWage   = 0;
                        $otWage     = 0;

                        if ($hasIn && $hasOut) {
                            $checkIn  = $inScans[0]; // first IN
                            $checkOut = end($outScans); // last OUT
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

                return response()->json(['status' => true, 'message' => "Processed $processed attendance records"]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to process attendance',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('fetch_payroll_data')){
            try {
                $month = (int) $request->input('month');
                $year  = (int) $request->input('year');
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $startDate = sprintf('%04d-%02d-01', $year, $month);
                $endDate   = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

                // Aggregated attendance per staff
                $attendance = DailyAttendance::active()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->select(
                        'staff_id',
                        DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 WHEN status = 'half_day' THEN 0.5 WHEN status = 'leave' AND base_wage = 0 THEN 1 ELSE 0 END) as days_absent"),
                        DB::raw("SUM(CASE WHEN ot_hours > 0 THEN 1 ELSE 0 END) as days_overtime"),
                        DB::raw("SUM(ot_wage) as total_ot"),
                        DB::raw("SUM(base_wage) as total_base")
                    )
                    ->groupBy('staff_id')
                    ->get()
                    ->keyBy('staff_id');

                // Saved payroll records
                $payrollRecords = PayrollRecord::active()
                    ->where('month', $month)
                    ->where('year', $year)
                    ->get()
                    ->keyBy('staff_id');

                return response()->json([
                    'status' => true,
                    'attendance' => $attendance,
                    'payroll_records' => $payrollRecords,
                    'days_in_month' => $daysInMonth,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to fetch payroll data',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('save_payroll_records')){
            try {
                $items = $request->input('items', []);
                $saved = 0;

                foreach ($items as $item) {
                    PayrollRecord::updateOrCreate(
                        [
                            'staff_id' => $item['staff_id'],
                            'month'    => $item['month'],
                            'year'     => $item['year'],
                        ],
                        [
                            'basic_amount'     => $item['basic_amount'] ?? 0,
                            'one_day_salary'   => $item['one_day_salary'] ?? 0,
                            'days_in_month'    => $item['days_in_month'] ?? 0,
                            'days_absent'      => $item['days_absent'] ?? 0,
                            'absent_deduction' => $item['absent_deduction'] ?? 0,
                            'days_overtime'    => $item['days_overtime'] ?? 0,
                            'overtime_amount'  => $item['overtime_amount'] ?? 0,
                            'advance_amount'   => $item['advance_amount'] ?? 0,
                            'final_pay'        => $item['final_pay'] ?? 0,
                            'paid_in_bank'     => $item['paid_in_bank'] ?? 0,
                            'paid_pf'          => $item['paid_pf'] ?? 0,
                            'paid_cash'        => $item['paid_cash'] ?? 0,
                            'is_deleted'       => false,
                        ]
                    );
                    $saved++;
                }

                return response()->json(['status' => true, 'message' => "Saved $saved payroll records"]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to save payroll records',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('save_settings')){
            try {
                if (!DB::getSchemaBuilder()->hasTable('settings')) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Settings table does not exist. Please run migrations.',
                    ]);
                }

                DB::beginTransaction();

                Setting::set('app_name', $request->input('app_name'));

                if ($request->input('remove_logo_white')) {
                    Setting::set('logo_white', '');
                } elseif ($request->hasFile('logo_white')) {
                    $file               = $request->file('logo_white');
                    $filetoupload 		= $file->getClientOriginalName();
            		$filedata 			= $file->getRealPath();
            		$abspath 			= "uploads";
            		$relpath 			= "uploads";
            		$cyear 				= date('Y', time());
            		$cmonth 			= date('m', time());
            		$fullyearp 			= $abspath."/".$cyear;
            		$fullmonthp 		= $abspath."/".$cyear."/".$cmonth;
                    $fileextension      = pathinfo($filetoupload, PATHINFO_EXTENSION);
                    $uniquefilename     = time() . '_' . uniqid() . '.' . $fileextension;
            		$fullfileabspath 	= $fullmonthp."/".$uniquefilename;
            		$fullfileurl 		= $relpath."/".$cyear."/".$cmonth."/".$uniquefilename;
                    if(file_exists($fullyearp)){
        				if(file_exists($fullmonthp)){
        					move_uploaded_file($filedata, $fullfileabspath);
        				}else{
        					mkdir($fullmonthp, 0755);
        					move_uploaded_file($filedata, $fullfileabspath);
        				}
        			}else{
        				mkdir($fullyearp, 0755);
        				if(file_exists($fullmonthp)){
        					move_uploaded_file($filedata, $fullfileabspath);
        				}else{
        				    mkdir($fullmonthp, 0755);
        					move_uploaded_file($filedata, $fullfileabspath);
        				}
        			}
                    Setting::set('logo_white', $fullfileurl);
                }

                if ($request->input('remove_logo_dark')) {
                    Setting::set('logo_dark', '');
                } elseif ($request->hasFile('logo_dark')) {
                    $file = $request->file('logo_dark');
                    $filetoupload 		= $file->getClientOriginalName();
            		$filedata 			= $file->getRealPath();
            		$abspath 			= "uploads";
            		$relpath 			= "uploads";
            		$cyear 				= date('Y', time());
            		$cmonth 			= date('m', time());
            		$fullyearp 			= $abspath."/".$cyear;
            		$fullmonthp 		= $abspath."/".$cyear."/".$cmonth;
                    $fileextension      = pathinfo($filetoupload, PATHINFO_EXTENSION);
                    $uniquefilename     = time() . '_' . uniqid() . '.' . $fileextension;
            		$fullfileabspath 	= $fullmonthp."/".$uniquefilename;
            		$fullfileurl 		= $relpath."/".$cyear."/".$cmonth."/".$uniquefilename;
                    if(file_exists($fullyearp)){
        				if(file_exists($fullmonthp)){
        					move_uploaded_file($filedata, $fullfileabspath);
        				}else{
        					mkdir($fullmonthp, 0755);
        					move_uploaded_file($filedata, $fullfileabspath);
        				}
        			}else{
        				mkdir($fullyearp, 0755);
        				if(file_exists($fullmonthp)){
        					move_uploaded_file($filedata, $fullfileabspath);
        				}else{
        				    mkdir($fullmonthp, 0755);
        					move_uploaded_file($filedata, $fullfileabspath);
        				}
        			}
                    Setting::set('logo_dark', $fullfileurl);
                }
                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Settings updated successfully',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update settings',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

    }

    private function capOt($staff, float $excessHours): float
    {
        if ($staff->ot_type === 'no_ot') {
            return 0;
        }
        if ($staff->ot_type === 'hours') {
            return min($excessHours, (float) ($staff->ot_max_hours ?: 0));
        }
        if ($staff->ot_type === 'minutes') {
            return min($excessHours, ((float) ($staff->ot_max_minutes ?: 0)) / 60);
        }
        return $excessHours;
    }
}
