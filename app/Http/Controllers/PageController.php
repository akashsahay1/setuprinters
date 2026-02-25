<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\ScannedBarcode;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\StaffGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use stdClass;

class PageController extends Controller
{
    public function login()
    {    

        // Insert into second database
        // $oldusers = DB::connection('pgsql2')->table('setu_printers.users')->get(); 
        // echo "<pre>";
        // print_r($oldusers);
        // echo "</pre>";
        // $i=1;
        // foreach($oldusers as $olduser) {
        //     DB::connection('pgsql')->table('users')->insert([
        //         'user_id' => $olduser->user_id,
        //         'employee_id' => $olduser->employee_id,
        //         'full_name' => $olduser->full_name,
        //         'phone_number' => $olduser->phone_number, 
        //         'phone_number_2' => $olduser->phone_number_2, 
        //         'email' => 'customer'.$i.'@setuprinters.com',
        //         'password' => Hash::make($olduser->phone_number), 
        //         'user_role' => 'customer', 
        //         'address' => $olduser->address, 
        //         'profile_photo' => $olduser->profile_photo, 
        //         'is_deleted' => false,
        //         'email_verified_at' => null,
        //         'remember_token' => null,
        //         'created_at' => $olduser->created_at,
        //         'updated_at' => $olduser->updated_at,
        //     ]);
        //     $i++;
        // }

        return view('pages.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_deleted', false)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->with('error', 'Invalid email or password.')->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function users()
    {
        
        $data = new stdClass();

        $data->users = User::paginate(30);

        return view('pages.users', ['data' => $data]);
    }

    public function dashboard()
    {
        $allStaff = Staff::active()->with('group')->orderBy('full_name')->get();
        $groups = StaffGroup::active()->orderBy('name')->get();

        $todayScans = ScannedBarcode::active()
            ->select('id', 'user_id', 'barcode', 'created_at')
            ->whereDate('created_at', today())
            ->with('staff:id,full_name,profile_photo,group_id,qr_code')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('pages.dashboard', [
            'allStaff' => $allStaff,
            'groups' => $groups,
            'todayScans' => $todayScans,
        ]);
    }

    public function settings()
    {
        $defaults = [
            'app_name' => 'Setu Printers',
            'logo_white' => '',
            'logo_dark' => '',
        ];

        try {
            if (DB::getSchemaBuilder()->hasTable('settings')) {
                $settings = Setting::all()->pluck('value', 'key')->toArray();
                $settings = array_merge($defaults, $settings);
            } else {
                $settings = $defaults;
            }
        } catch (\Exception $e) {
            $settings = $defaults;
        }

        // Collect purgeable financial years from all data tables
        $currentFy = Holiday::currentFinancialYear();
        $fys = collect();

        // FYs from holidays
        $fys = $fys->merge(
            DB::table('holidays')->whereNotNull('financial_year')
                ->select('financial_year')->distinct()->pluck('financial_year')
        );

        // FYs from daily_attendances (derive from date)
        $attDates = DB::table('daily_attendances')
            ->selectRaw('MIN(date) as min_date, MAX(date) as max_date')->first();
        if ($attDates && $attDates->min_date) {
            $d = \Carbon\Carbon::parse($attDates->min_date);
            $end = \Carbon\Carbon::parse($attDates->max_date);
            while ($d->lte($end)) {
                $fys->push(Holiday::deriveFinancialYear($d));
                $d->addMonths(3);
            }
            $fys->push(Holiday::deriveFinancialYear($end));
        }

        // FYs from payroll_records (derive from month/year)
        $payrollPeriods = DB::table('payroll_records')
            ->select('month', 'year')->distinct()->get();
        foreach ($payrollPeriods as $p) {
            $fys->push(Holiday::deriveFinancialYear("{$p->year}-{$p->month}-01"));
        }

        $purgeableFys = $fys->unique()->filter(fn($fy) => $fy !== $currentFy)->sort()->values();

        return view('pages.settings', ['settings' => $settings, 'purgeableFys' => $purgeableFys]);
    }
    public function adduser()
    {
        return view('pages.adduser');
    }

    public function reporting()
    {
        return view('pages.reporting');
    }

    public function staffs()
    {
        $staffList = Staff::active()->with('group')->orderBy('full_name')->paginate(30);
        $groups = StaffGroup::active()->orderBy('name')->get();
        return view('pages.staffs', ['staffList' => $staffList, 'groups' => $groups]);
    }

    public function payrollReport()
    {
        $staffList = Staff::active()->with('group')->orderBy('full_name')->get();
        $groups = StaffGroup::active()->orderBy('name')->get();
        $holidays = Holiday::active()->get();
        return view('pages.payroll-report', ['staffList' => $staffList, 'groups' => $groups, 'holidays' => $holidays]);
    }

    public function leaveManagement()
    {
        $staffList = Staff::active()->with('group')->orderBy('full_name')->get();
        $groups = StaffGroup::active()->orderBy('name')->get();
        return view('pages.leave-management', ['staffList' => $staffList, 'groups' => $groups]);
    }

    public function attendance()
    {
        $staffList = Staff::active()->with('group')->orderBy('full_name')->get();
        $groups = StaffGroup::active()->orderBy('name')->get();
        return view('pages.attendance', ['staffList' => $staffList, 'groups' => $groups]);
    }

    public function staffCreate()
    {
        $groups = StaffGroup::active()->orderBy('name')->get();
        return view('pages.staff-create', ['groups' => $groups]);
    }

    public function holidays(Request $request)
    {
        $currentFy = Holiday::currentFinancialYear();
        $selectedFy = $request->query('fy', $currentFy);

        $holidays = Holiday::active()
            ->where('financial_year', $selectedFy)
            ->orderBy('date')
            ->get();

        $availableFys = Holiday::active()
            ->whereNotNull('financial_year')
            ->select('financial_year')
            ->distinct()
            ->orderBy('financial_year', 'desc')
            ->pluck('financial_year');

        // Ensure current FY is always in the list
        if (!$availableFys->contains($currentFy)) {
            $availableFys->prepend($currentFy);
        }

        return view('pages.holidays', [
            'holidays' => $holidays,
            'selectedFy' => $selectedFy,
            'availableFys' => $availableFys,
        ]);
    }

    public function staffEdit($id)
    {
        $staff = Staff::active()->with('group')->find($id);
        if (!$staff) {
            return redirect()->route('staffs');
        }
        $groups = StaffGroup::active()->orderBy('name')->get();
        return view('pages.staff-edit', ['staff' => $staff, 'groups' => $groups]);
    }
}
