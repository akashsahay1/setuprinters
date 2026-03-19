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

        if($request->has('update_user')){
            try {
                $user = User::active()->findOrFail($request->user_id);

                $user->fill([
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'user_role' => $request->user_role,
                    'phone_number' => $request->phone_number,
                    'phone_number_2' => $request->phone_number_2,
                    'address' => $request->address,
                ]);

                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }

                if ($request->hasFile('profile_photo')) {
                    $photo = $request->file('profile_photo');
                    $filename = 'profile_' . time() . '.' . $photo->getClientOriginalExtension();
                    $photo->move(public_path('uploads/profiles'), $filename);
                    $user->profile_photo = 'uploads/profiles/' . $filename;
                }

                $user->save();

                return response()->json([
                    'status' => true,
                    'message' => 'User updated successfully',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update user',
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
                    'pf_amount' => $request->pf_amount,
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
                    'pf_amount' => $request->pf_amount,
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
                if (!$user || $user->user_role === 'manager') {
                    return response()->json(['status' => false, 'message' => 'Managers are not allowed to delete records']);
                }
                if (!Hash::check($request->password, $user->password)) {
                    return response()->json(['status' => false, 'message' => 'Incorrect password']);
                }
                Staff::where('id', $request->staff_id)->update(['is_deleted' => true]);
                return response()->json(['status' => true, 'message' => 'Staff moved to trash']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to delete staff',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('restore_staff')){
            try {
                $user = Auth::user();
                if (!$user || $user->user_role === 'manager') {
                    return response()->json(['status' => false, 'message' => 'Managers are not allowed to restore records']);
                }
                Staff::where('id', $request->staff_id)->update(['is_deleted' => false]);
                return response()->json(['status' => true, 'message' => 'Staff restored successfully']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to restore staff',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('permanent_delete_staff')){
            try {
                $user = Auth::user();
                if (!$user || $user->user_role === 'manager') {
                    return response()->json(['status' => false, 'message' => 'Managers are not allowed to delete records']);
                }
                if (!Hash::check($request->password, $user->password)) {
                    return response()->json(['status' => false, 'message' => 'Incorrect password']);
                }
                $staffId = $request->staff_id;
                ScannedBarcode::where('user_id', $staffId)->delete();
                DailyAttendance::where('staff_id', $staffId)->delete();
                LeaveApplication::where('staff_id', $staffId)->delete();
                PayrollRecord::where('staff_id', $staffId)->delete();
                Staff::where('id', $staffId)->delete();
                return response()->json(['status' => true, 'message' => 'Staff and all related records permanently deleted']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to permanently delete staff',
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
                if (!$user || $user->user_role === 'manager') {
                    return response()->json(['status' => false, 'message' => 'Managers are not allowed to delete records']);
                }
                if (!Hash::check($request->password, $user->password)) {
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
                if (!$user || $user->user_role !== 'admin') {
                    return response()->json(['status' => false, 'message' => 'Only administrators can delete users']);
                }
                if (!Hash::check($request->password, $user->password)) {
                    return response()->json(['status' => false, 'message' => 'Incorrect password']);
                }
                $targetUser = User::find($request->user_id);
                if (!$targetUser) {
                    return response()->json(['status' => false, 'message' => 'User not found']);
                }
                if ($targetUser->user_role === 'manager') {
                    return response()->json(['status' => false, 'message' => 'Manager accounts cannot be deleted']);
                }
                $targetUser->update(['is_deleted' => true]);
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
                        'pf_amount' => $item['pfAmount'] ?? null,
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
                if (!$user || $user->user_role === 'manager') {
                    return response()->json(['status' => false, 'message' => 'Managers are not allowed to delete records']);
                }
                if (!Hash::check($request->password, $user->password)) {
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

        if($request->has('export_full_db')){
            try {
                $dbHost = config('database.connections.mysql.host');
                $dbPort = config('database.connections.mysql.port');
                $dbName = config('database.connections.mysql.database');
                $dbUser = config('database.connections.mysql.username');
                $dbPass = config('database.connections.mysql.password');

                $timestamp = now()->format('Y-m-d_His');
                $sqlFile = storage_path("app/{$dbName}_{$timestamp}.sql");
                $zipPath = storage_path("app/{$dbName}_{$timestamp}.zip");

                // Run mysqldump — check common paths
                $mysqldump = 'mysqldump';
                $possiblePaths = [
                    'C:\\Program Files\\MariaDB 12.1\\bin\\mysqldump.exe',
                    'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                    'C:\\laravel\\herd\\bin\\mysqldump.exe',
                ];
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $mysqldump = $path;
                        break;
                    }
                }

                $command = sprintf(
                    '%s --host=%s --port=%s --user=%s --password=%s --result-file=%s %s 2>&1',
                    escapeshellarg($mysqldump),
                    escapeshellarg($dbHost),
                    escapeshellarg($dbPort),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPass),
                    escapeshellarg($sqlFile),
                    escapeshellarg($dbName)
                );

                \Log::info('Export DB command', ['cmd' => $command]);
                exec($command, $output, $returnCode);
                \Log::info('Export DB result', ['rc' => $returnCode, 'output' => $output, 'file_exists' => file_exists($sqlFile), 'size' => file_exists($sqlFile) ? filesize($sqlFile) : 0]);

                if ($returnCode !== 0 || !file_exists($sqlFile) || filesize($sqlFile) === 0) {
                    @unlink($sqlFile);
                    return response()->json([
                        'status' => false,
                        'message' => 'mysqldump failed (rc=' . $returnCode . ')',
                        'error' => config('app.debug') ? implode("\n", $output) : null,
                    ]);
                }

                // Zip the SQL file
                $zip = new \ZipArchive();
                if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                    @unlink($sqlFile);
                    return response()->json(['status' => false, 'message' => 'Failed to create zip file']);
                }
                $zip->addFile($sqlFile, "{$dbName}_{$timestamp}.sql");
                $zip->close();

                // Clean up the SQL file
                @unlink($sqlFile);

                $filename = "setuprinters_backup_{$timestamp}.zip";
                return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
            } catch (\Exception $e) {
                \Log::error('Export DB exception', ['msg' => $e->getMessage()]);
                if (isset($sqlFile) && file_exists($sqlFile)) @unlink($sqlFile);
                if (isset($zipPath) && file_exists($zipPath)) @unlink($zipPath);
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to export database',
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

                // Include paid leave counts per staff for current FY
                $now = \Carbon\Carbon::now();
                $fyStart = $now->month >= 4
                    ? \Carbon\Carbon::create($now->year, 4, 1)
                    : \Carbon\Carbon::create($now->year - 1, 4, 1);
                $fyEnd = $fyStart->copy()->addYear()->subDay();

                $paidLeaveCounts = LeaveApplication::active()
                    ->where('status', 'granted')
                    ->where('leave_type', '!=', 'unpaid')
                    ->whereBetween('leave_date', [$fyStart->format('Y-m-d'), $fyEnd->format('Y-m-d')])
                    ->select('staff_id', DB::raw('COUNT(*) as cnt'))
                    ->groupBy('staff_id')
                    ->pluck('cnt', 'staff_id')
                    ->toArray();

                $fyLabel = $fyStart->year . '-' . $fyEnd->year;

                return response()->json([
                    'status' => true,
                    'leaves' => $leaves,
                    'paid_leave_counts' => $paidLeaveCounts,
                    'fy_label' => $fyLabel,
                    'paid_leave_limit' => config('services.paid_leave_limit'),
                ]);
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

                // Track paid leave counts per staff to enforce 2-day FY limit
                $paidLeaveTracker = [];
                $approved = 0;
                $skipped = 0;

                foreach ($leaves as $leave) {
                    $isPaid = $leave->leave_type !== 'unpaid';

                    if ($isPaid) {
                        $dateStr = $leave->leave_date->format('Y-m-d');
                        if (!isset($paidLeaveTracker[$leave->staff_id])) {
                            $paidLeaveTracker[$leave->staff_id] = $this->countPaidLeavesInFy($leave->staff_id, $dateStr);
                        }
                        if ($paidLeaveTracker[$leave->staff_id] >= config('services.paid_leave_limit')) {
                            $skipped++;
                            continue;
                        }
                        $paidLeaveTracker[$leave->staff_id]++;
                    }

                    $leave->update(['status' => 'granted']);
                    $approved++;

                    // Auto-update daily_attendances
                    $staff = Staff::find($leave->staff_id);
                    $dailyWage = $staff ? ((float) $staff->basic_salary) / 26 : 0;

                    if ($isPaid) {
                        $status = 'present';
                        $baseWage = round($dailyWage, 2);
                    } else {
                        $status = 'leave';
                        $baseWage = 0;
                    }

                    DailyAttendance::where('staff_id', $leave->staff_id)
                        ->where('date', $leave->leave_date->format('Y-m-d'))
                        ->where('status', 'absent')
                        ->update(['status' => $status, 'base_wage' => $baseWage]);
                }

                $msg = "$approved leave(s) approved successfully";
                if ($skipped > 0) {
                    $msg .= ". $skipped paid leave(s) skipped (2-day FY limit reached).";
                }

                return response()->json(['status' => true, 'message' => $msg]);
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

                $markAs = $request->input('mark_as', 'unpaid'); // paid or unpaid

                // Check paid leave limit per FY
                $paidLeaveLimit = config('services.paid_leave_limit');
                if ($markAs === 'paid') {
                    $paidCount = $this->countPaidLeavesInFy($leave->staff_id, $leave->leave_date->format('Y-m-d'));
                    if ($paidCount >= $paidLeaveLimit) {
                        $fy = $this->getFyLabel($leave->leave_date->format('Y-m-d'));
                        return response()->json([
                            'status' => false,
                            'message' => "Paid leave limit ({$paidLeaveLimit} days) reached for FY $fy.",
                        ]);
                    }
                }

                // Update leave type based on manager's choice and grant
                $leaveType = $markAs === 'paid' ? $leave->leave_type : 'unpaid';
                if ($leaveType === 'unpaid' && $markAs === 'paid') {
                    $leaveType = 'casual'; // If original was unpaid but manager marks paid
                }
                $leave->update(['status' => 'granted', 'leave_type' => $leaveType]);

                // Auto-update daily_attendances if absent row exists for this date
                $staff = Staff::find($leave->staff_id);
                if ($staff) {
                    $dailyWage = ((float) $staff->basic_salary) / 26;

                    if ($markAs === 'paid') {
                        // Paid leave: mark as present with wage
                        $status = 'present';
                        $baseWage = round($dailyWage, 2);
                    } else {
                        // Unpaid leave: mark as leave with zero wage
                        $status = 'leave';
                        $baseWage = 0;
                    }

                    DailyAttendance::where('staff_id', $leave->staff_id)
                        ->where('date', $leave->leave_date->format('Y-m-d'))
                        ->where('status', 'absent')
                        ->update(['status' => $status, 'base_wage' => $baseWage]);
                }

                $msg = $markAs === 'paid' ? 'Leave approved as Paid' : 'Leave approved as Unpaid';
                return response()->json(['status' => true, 'message' => $msg]);
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

                // Fetch all LOGIN/LOGOUT scans for the month
                $scans = ScannedBarcode::active()
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->where(function($q) {
                        $q->where('barcode', 'LOGIN')
                          ->orWhere('barcode', 'LOGOUT');
                    })
                    ->orderBy('created_at')
                    ->get();

                // Group scans: [user_id][date_string] = ['in' => [...], 'out' => [...]]
                $scanMap = [];
                foreach ($scans as $scan) {
                    $uid  = $scan->user_id;
                    $date = $scan->created_at->format('Y-m-d');
                    $type = (trim($scan->barcode) === 'LOGOUT') ? 'out' : 'in';
                    $scanMap[$uid][$date][$type][] = $scan->created_at;
                }

                // Pre-compute paid leave counts per staff for the FY containing this month
                $fyStart = $month >= 4
                    ? \Carbon\Carbon::create($year, 4, 1)
                    : \Carbon\Carbon::create($year - 1, 4, 1);
                $fyEnd = $fyStart->copy()->addYear()->subDay();

                $paidLeaveCounts = LeaveApplication::active()
                    ->where('status', 'granted')
                    ->where('leave_type', '!=', 'unpaid')
                    ->whereBetween('leave_date', [$fyStart->format('Y-m-d'), $fyEnd->format('Y-m-d')])
                    ->select('staff_id', DB::raw('COUNT(*) as cnt'))
                    ->groupBy('staff_id')
                    ->pluck('cnt', 'staff_id')
                    ->toArray();

                $workingDays = 26;
                $processed = 0;

                foreach ($staffList as $staff) {
                    $basicSalary = (float) $staff->basic_salary;
                    $dailyWage   = $workingDays > 0 ? $basicSalary / $workingDays : 0;
                    $shiftHours  = (int) ($staff->shift_hours ?: 8);
                    $otMaxHours  = $this->getOtMaxHours($staff);

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
                        $otCount    = 0;
                        $baseWage   = 0;
                        $otWage     = 0;

                        if ($hasIn && $hasOut) {
                            $checkIn    = $inScans[0];
                            $checkOut   = end($outScans);
                            $totalHours = round(abs($checkOut->diffInMinutes($checkIn)) / 60, 2);
                            $status     = 'present';

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
                        } elseif ($hasIn || $hasOut) {
                            // Only one scan (LOGIN or LOGOUT, not both): absent
                            $checkIn  = $hasIn ? $inScans[0] : null;
                            $checkOut = $hasOut ? end($outScans) : null;
                            $status   = 'absent';
                        } elseif (isset($leaveMap[$staff->id][$dateStr])) {
                            $leaveType = $leaveMap[$staff->id][$dateStr];
                            $isPaid = ($leaveType !== 'unpaid') && (($paidLeaveCounts[$staff->id] ?? 0) <= 2);

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
                                'ot_count'    => $otCount,
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
                        DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 WHEN status = 'leave' AND base_wage = 0 THEN 1 ELSE 0 END) as days_absent"),
                        DB::raw("SUM(CASE WHEN ot_hours > 0 THEN 1 ELSE 0 END) as days_overtime"),
                        DB::raw("SUM(ot_count) as total_ot_count"),
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

        if($request->has('fetch_yearly_payroll')){
            try {
                $staffId   = (int) $request->input('staff_id');
                $fy        = $request->input('fy'); // e.g. "2025-2026"
                $parts     = explode('-', $fy);

                if (count($parts) !== 2) {
                    return response()->json(['status' => false, 'message' => 'Invalid FY format']);
                }

                $startYear = (int) $parts[0];
                $endYear   = (int) $parts[1];

                $staff = Staff::active()->with('group')->find($staffId);
                if (!$staff) {
                    return response()->json(['status' => false, 'message' => 'Staff not found']);
                }

                $workingDays = 26;
                $basicSalary = (float) $staff->basic_salary;
                $oneDaySalary = $workingDays > 0 ? round($basicSalary / $workingDays, 2) : 0;
                $pfEnabled = (bool) $staff->pf_enabled;
                $pfAmount = (float) ($staff->pf_amount ?? 0);

                // FY months: Apr(startYear) to Mar(endYear)
                $fyMonths = [];
                for ($m = 4; $m <= 12; $m++) $fyMonths[] = ['month' => $m, 'year' => $startYear];
                for ($m = 1; $m <= 3; $m++)  $fyMonths[] = ['month' => $m, 'year' => $endYear];

                // Full FY date range for attendance query
                $fyStart = sprintf('%04d-04-01', $startYear);
                $fyEnd   = sprintf('%04d-03-31', $endYear);

                // Aggregated attendance per month
                $attendance = DailyAttendance::active()
                    ->where('staff_id', $staffId)
                    ->whereBetween('date', [$fyStart, $fyEnd])
                    ->select(
                        DB::raw("MONTH(date) as m"),
                        DB::raw("YEAR(date) as y"),
                        DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 WHEN status = 'leave' AND base_wage = 0 THEN 1 ELSE 0 END) as days_absent"),
                        DB::raw("SUM(CASE WHEN ot_hours > 0 THEN 1 ELSE 0 END) as days_overtime"),
                        DB::raw("SUM(ot_count) as total_ot_count"),
                        DB::raw("SUM(ot_wage) as total_ot"),
                        DB::raw("SUM(base_wage) as total_base")
                    )
                    ->groupBy(DB::raw("YEAR(date)"), DB::raw("MONTH(date)"))
                    ->get()
                    ->keyBy(function ($item) {
                        return $item->m . '-' . $item->y;
                    });

                // Saved payroll records (for advance, paid_cash)
                $saved = PayrollRecord::active()
                    ->where('staff_id', $staffId)
                    ->where(function ($q) use ($startYear, $endYear) {
                        $q->where(function ($q2) use ($startYear) {
                            $q2->where('year', $startYear)->where('month', '>=', 4);
                        })->orWhere(function ($q2) use ($endYear) {
                            $q2->where('year', $endYear)->where('month', '<=', 3);
                        });
                    })
                    ->get()
                    ->keyBy(function ($item) {
                        return $item->month . '-' . $item->year;
                    });

                // Build monthly records
                // Priority: saved payroll record > live computation from attendance
                $records = [];
                foreach ($fyMonths as $fm) {
                    $key = $fm['month'] . '-' . $fm['year'];
                    $att = $attendance->get($key);
                    $prev = $saved->get($key);

                    if (!$att && !$prev) {
                        continue; // no data for this month
                    }

                    // If saved record exists, use it as-is (historical snapshot)
                    if ($prev) {
                        $records[] = [
                            'month'            => $fm['month'],
                            'year'             => $fm['year'],
                            'basic_amount'     => (float) $prev->basic_amount,
                            'one_day_salary'   => (float) $prev->one_day_salary,
                            'days_in_month'    => (int) $prev->days_in_month,
                            'days_absent'      => (float) $prev->days_absent,
                            'absent_deduction' => (float) $prev->absent_deduction,
                            'days_overtime'    => (int) $prev->days_overtime,
                            'overtime_amount'  => (float) $prev->overtime_amount,
                            'advance_amount'   => (float) $prev->advance_amount,
                            'paid_pf'          => (float) $prev->paid_pf,
                            'final_pay'        => (float) $prev->final_pay,
                            'paid_in_bank'     => (float) $prev->paid_in_bank,
                            'paid_cash'        => (float) $prev->paid_cash,
                            'saved'            => true,
                        ];
                        continue;
                    }

                    // Otherwise compute live from attendance
                    $daysAbsent = (float) $att->days_absent;
                    $totalBase = round((float) $att->total_base, 2);
                    $daysOt = (int) $att->days_overtime;
                    $otAmount = round((float) $att->total_ot, 2);
                    $paidPf = $pfEnabled ? round($pfAmount, 2) : 0;
                    $finalPay = round($totalBase + $otAmount - $paidPf, 2);
                    $paidBank = round($finalPay, 2);
                    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $fm['month'], $fm['year']);

                    $records[] = [
                        'month'            => $fm['month'],
                        'year'             => $fm['year'],
                        'basic_amount'     => $basicSalary,
                        'one_day_salary'   => $oneDaySalary,
                        'total_base'       => $totalBase,
                        'days_in_month'    => $daysInMonth,
                        'days_absent'      => $daysAbsent,
                        'absent_deduction' => 0,
                        'days_overtime'    => $daysOt,
                        'overtime_amount'  => $otAmount,
                        'advance_amount'   => 0,
                        'paid_pf'          => $paidPf,
                        'final_pay'        => $finalPay,
                        'paid_in_bank'     => $paidBank,
                        'paid_cash'        => 0,
                        'saved'            => false,
                    ];
                }

                return response()->json([
                    'status'  => true,
                    'staff'   => $staff,
                    'records' => $records,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Failed to fetch yearly payroll',
                    'error'   => config('app.debug') ? $e->getMessage() : null,
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

        if($request->has('fetch_edit_attendance')){
            try {
                $staffId = (int) $request->input('staff_id');
                $month   = (int) $request->input('month', now()->month);
                $year    = (int) $request->input('year', now()->year);
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $startDate = sprintf('%04d-%02d-01', $year, $month);
                $endDate   = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

                $staff = Staff::active()->with('group:id,name')->find($staffId);
                if (!$staff) {
                    return response()->json(['status' => false, 'message' => 'Staff not found']);
                }

                $records = DailyAttendance::where('staff_id', $staffId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date')
                    ->get();

                $rows = [];
                foreach ($records as $rec) {
                    $rows[] = [
                        'id'          => $rec->id,
                        'date'        => $rec->date->format('Y-m-d'),
                        'date_label'  => $rec->date->format('d M, D'),
                        'check_in'    => $rec->check_in,
                        'check_out'   => $rec->check_out,
                        'total_hours' => (float) $rec->total_hours,
                        'status'      => $rec->status,
                        'is_ot'       => (bool) $rec->is_ot,
                        'ot_hours'    => (float) $rec->ot_hours,
                        'ot_count'    => (float) $rec->ot_count,
                        'base_wage'   => (float) $rec->base_wage,
                        'ot_wage'     => (float) $rec->ot_wage,
                    ];
                }

                // Lock editing only after the month has ended
                $lastDayOfMonth = \Carbon\Carbon::create($year, $month)->endOfMonth()->format('Y-m-d');
                $payrollLocked = now()->format('Y-m-d') > $lastDayOfMonth;

                return response()->json([
                    'status'         => true,
                    'data'           => $rows,
                    'payroll_locked' => $payrollLocked,
                    'staff'          => [
                        'id'   => $staff->id,
                        'name' => $staff->full_name,
                        'group' => $staff->group ? $staff->group->name : '-',
                    ],
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Failed to fetch attendance data',
                    'error'   => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('update_daily_attendance')){
            try {
                $id = (int) $request->input('attendance_id');
                $record = DailyAttendance::findOrFail($id);

                // Block editing after the month has ended
                $recDate = \Carbon\Carbon::parse($record->date);
                $lastDayOfMonth = $recDate->copy()->endOfMonth()->format('Y-m-d');
                if (now()->format('Y-m-d') > $lastDayOfMonth) {
                    return response()->json(['status' => false, 'message' => 'Cannot edit: the month has ended']);
                }

                $totalHours = (float) $request->input('total_hours', 0);

                $staff = Staff::active()->find($record->staff_id);
                if (!$staff) {
                    return response()->json(['status' => false, 'message' => 'Staff not found']);
                }

                $workingDays = 26;
                $basicSalary = (float) $staff->basic_salary;
                $dailyWage   = $workingDays > 0 ? $basicSalary / $workingDays : 0;
                $shiftHours  = (int) ($staff->shift_hours ?: 8);
                $otMaxHours  = $this->getOtMaxHours($staff);

                $dateObj   = \Carbon\Carbon::parse($record->date);
                $isSunday  = ($dateObj->dayOfWeek === 0);
                $isHoliday = Holiday::active()
                    ->where(function ($q) use ($dateObj) {
                        $dateStr = $dateObj->format('Y-m-d');
                        $month   = $dateObj->month;
                        $q->where(function ($q2) use ($dateStr) {
                            $q2->where('is_yearly', false)->whereDate('date', $dateStr);
                        })->orWhere(function ($q2) use ($month, $dateObj) {
                            $q2->where('is_yearly', true)
                               ->whereMonth('date', $month)
                               ->whereDay('date', $dateObj->day);
                        });
                    })
                    ->exists();

                // Auto-derive status from total_hours
                $status   = 'absent';
                $isOt     = false;
                $otHours  = 0;
                $otCount  = 0;
                $baseWage = 0;
                $otWage   = 0;

                if ($totalHours > 0) {
                    $status = $totalHours > 4 ? 'present' : 'half_day';

                    if ($isSunday || $isHoliday) {
                        // Holiday/Sunday: base pay + all hours as OT
                        $status   = 'holiday';
                        $baseWage = round($dailyWage, 2);
                        $otHours  = round($totalHours, 2);
                        $otCount  = $otMaxHours > 0 ? round($otHours / $otMaxHours, 2) : 0;
                        $otWage   = round($otCount * $dailyWage, 2);
                        $isOt     = true;
                    } elseif ($totalHours < $shiftHours) {
                        // Less than shift: proportional wage
                        $baseWage = round(($totalHours / $shiftHours) * $dailyWage, 2);
                    } else {
                        // Full shift or more: full day + OT on excess
                        $baseWage = round($dailyWage, 2);
                        $otHours  = round($totalHours - $shiftHours, 2);
                        $otCount  = $otMaxHours > 0 ? round($otHours / $otMaxHours, 2) : 0;
                        $otWage   = round($otCount * $dailyWage, 2);
                        $isOt     = $otHours > 0;
                    }
                } elseif ($isSunday || $isHoliday) {
                    $status   = 'holiday';
                    $baseWage = round($dailyWage, 2);
                }

                $record->update([
                    'status'      => $status,
                    'total_hours' => $totalHours,
                    'is_ot'       => $isOt,
                    'ot_hours'    => $otHours,
                    'ot_count'    => $otCount,
                    'base_wage'   => $baseWage,
                    'ot_wage'     => $otWage,
                ]);

                return response()->json([
                    'status'  => true,
                    'message' => 'Attendance updated successfully',
                    'data'    => [
                        'id'          => $record->id,
                        'date'        => $record->date->format('Y-m-d'),
                        'date_label'  => $record->date->format('d M, D'),
                        'check_in'    => $record->check_in,
                        'check_out'   => $record->check_out,
                        'total_hours' => (float) $record->total_hours,
                        'status'      => $record->status,
                        'is_ot'       => (bool) $record->is_ot,
                        'ot_hours'    => (float) $record->ot_hours,
                        'ot_count'    => (float) $record->ot_count,
                        'base_wage'   => (float) $record->base_wage,
                        'ot_wage'     => (float) $record->ot_wage,
                    ],
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Failed to update attendance',
                    'error'   => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

        if($request->has('fetch_attendance_calc')){
            try {
                $month = (int) $request->input('month', now()->month);
                $year  = (int) $request->input('year', now()->year);
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $startDate = sprintf('%04d-%02d-01', $year, $month);
                $endDate   = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

                $staffList = Staff::active()->with('group:id,name')->orderBy('full_name')->get();

                // Fetch scans for the month + look back for unclosed LOGIN from before
                $scans = ScannedBarcode::active()
                    ->where(function($q) {
                        $q->where('barcode', 'LOGIN')
                          ->orWhere('barcode', 'LOGOUT');
                    })
                    ->orderBy('created_at')
                    ->get();

                // Group all scans by user chronologically
                $userScans = [];
                foreach ($scans as $scan) {
                    $userScans[$scan->user_id][] = [
                        'type' => (trim($scan->barcode) === 'LOGOUT') ? 'out' : 'in',
                        'time' => $scan->created_at,
                    ];
                }

                $result = [];
                foreach ($staffList as $staff) {
                    $staffScans = $userScans[$staff->id] ?? [];
                    $dailyHours = $this->calculateDailyHours($staffScans, $startDate, $endDate);
                    $totalMonthHours = array_sum($dailyHours);
                    $totalDays = round($totalMonthHours / 8, 2);

                    $result[] = [
                        'staff_id'    => $staff->id,
                        'staff_name'  => $staff->full_name,
                        'group_name'  => $staff->group ? $staff->group->name : '-',
                        'total_hours' => round($totalMonthHours, 2),
                        'total_days'  => $totalDays,
                    ];
                }

                return response()->json(['status' => true, 'data' => $result, 'month' => $month, 'year' => $year]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to calculate attendance',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ]);
            }
        }

    }

    /**
     * Pair LOGIN→LOGOUT chronologically, split cross-day sessions at midnight,
     * and return hours per date within the given range.
     */
    private function calculateDailyHours(array $scans, string $rangeStart, string $rangeEnd): array
    {
        $dailyHours = [];
        $pendingLogin = null;

        foreach ($scans as $scan) {
            if ($scan['type'] === 'in') {
                $pendingLogin = $scan['time'];
            } elseif ($scan['type'] === 'out' && $pendingLogin !== null) {
                $this->splitSessionIntoDays($pendingLogin, $scan['time'], $rangeStart, $rangeEnd, $dailyHours);
                $pendingLogin = null;
            }
        }

        return $dailyHours;
    }

    /**
     * Split a single LOGIN→LOGOUT session across midnight boundaries
     * and add hours to the dailyHours array for dates within range.
     */
    private function splitSessionIntoDays(Carbon $login, Carbon $logout, string $rangeStart, string $rangeEnd, array &$dailyHours): void
    {
        $cursor = $login->copy();

        while ($cursor->lt($logout)) {
            $dayEnd = $cursor->copy()->endOfDay();
            $segmentEnd = $logout->lt($dayEnd) ? $logout : $dayEnd;
            $dateStr = $cursor->format('Y-m-d');

            if ($dateStr >= $rangeStart && $dateStr <= $rangeEnd) {
                $minutes = abs($segmentEnd->diffInMinutes($cursor));
                $hours = round($minutes / 60, 2);
                $dailyHours[$dateStr] = ($dailyHours[$dateStr] ?? 0) + $hours;
            }

            $cursor = $cursor->copy()->addDay()->startOfDay();
        }
    }

    private function getOtMaxHours($staff): float
    {
        if ($staff->ot_type === 'no_ot') return 0;
        if ($staff->ot_type === 'hours') return (float) ($staff->ot_max_hours ?: 0);
        if ($staff->ot_type === 'minutes') return ((float) ($staff->ot_max_minutes ?: 0)) / 60;
        return 0;
    }

    private function countPaidLeavesInFy(int $staffId, string $dateStr): int
    {
        $date = \Carbon\Carbon::parse($dateStr);
        $fyStart = $date->month >= 4
            ? \Carbon\Carbon::create($date->year, 4, 1)
            : \Carbon\Carbon::create($date->year - 1, 4, 1);
        $fyEnd = $fyStart->copy()->addYear()->subDay(); // March 31

        return LeaveApplication::active()
            ->where('staff_id', $staffId)
            ->where('status', 'granted')
            ->where('leave_type', '!=', 'unpaid')
            ->whereBetween('leave_date', [$fyStart->format('Y-m-d'), $fyEnd->format('Y-m-d')])
            ->count();
    }

    private function getFyLabel(string $dateStr): string
    {
        $date = \Carbon\Carbon::parse($dateStr);
        $startYear = $date->month >= 4 ? $date->year : $date->year - 1;
        return $startYear . '-' . ($startYear + 1);
    }
}
