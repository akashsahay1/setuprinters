<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id'   => 'required|integer|exists:staff,id',
            'leave_type' => 'nullable|string|in:casual,sick,earned,unpaid',
            'dates'      => 'required|array|min:1',
            'dates.*'    => 'required|date_format:Y-m-d',
            'reason'     => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $staff = Staff::active()->find($request->staff_id);
        if (!$staff) {
            return response()->json([
                'status'  => false,
                'message' => 'Staff not found or inactive',
            ]);
        }

        $existingDates = LeaveApplication::active()
            ->where('staff_id', $request->staff_id)
            ->whereIn('leave_date', $request->dates)
            ->where('status', '!=', 'rejected')
            ->pluck('leave_date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();

        if (!empty($existingDates)) {
            return response()->json([
                'status'  => false,
                'message' => 'Leave already applied for: ' . implode(', ', $existingDates),
            ]);
        }

        try {
            $leaves = DB::transaction(function () use ($request) {
                $records = [];
                foreach ($request->dates as $date) {
                    $records[] = LeaveApplication::create([
                        'staff_id'   => $request->staff_id,
                        'leave_date' => $date,
                        'leave_type' => $request->input('leave_type', 'casual'),
                        'reason'     => $request->reason,
                        'status'     => 'pending',
                        'is_deleted' => false,
                    ]);
                }
                return $records;
            });

            return response()->json([
                'status'  => true,
                'message' => count($leaves) . ' leave application(s) submitted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to submit leave application',
            ]);
        }
    }

    public function checkToday(Request $request)
    {
        $staffId = $request->query('staff_id');

        if (!$staffId) {
            return response()->json([
                'status'  => false,
                'message' => 'staff_id is required',
            ]);
        }

        $today = now()->format('Y-m-d');

        $onLeave = LeaveApplication::active()
            ->where('staff_id', $staffId)
            ->where('leave_date', $today)
            ->where('status', 'granted')
            ->exists();

        return response()->json([
            'status'   => true,
            'on_leave' => $onLeave,
        ]);
    }
}
