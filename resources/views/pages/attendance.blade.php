@include('common.header', ['title' => 'Attendance'])

<!-- loader starts-->
<div class="loader-wrapper">
    <div class="loader-index"> <span></span></div>
    <svg>
        <defs></defs>
        <filter id="goo">
            <fegaussianblur in="SourceGraphic" stddeviation="11" result="blur"></fegaussianblur>
            <fecolormatrix in="blur" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo"> </fecolormatrix>
        </filter>
    </svg>
</div>
<!-- loader ends-->

<!-- tap on top starts-->
<div class="tap-top"><i data-feather="chevrons-up"></i></div>
<!-- tap on tap ends-->

<!-- page-wrapper Start-->
<div class="page-wrapper compact-wrapper" id="pageWrapper">

    <!-- Page Header Start-->
    @include('common.innerheader', ['title' => 'Attendance'])
    <!-- Page Header Ends -->

    <div class="page-body-wrapper">

        <!-- Page Sidebar Start-->
        @include('common.sidebar')
        <!-- Page Sidebar Ends-->

        <div class="page-body">

<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <label class="form-label mb-0">Select Staff</label>
                    <select class="form-select form-control form-select-sm" id="hrmsDashStaff">
                        <option value="">-- Select --</option>
                        @foreach($staffList as $s)
                        <option value="{{ $s->id }}">{{ $s->full_name }} (#{{ $s->id }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-0">Month</label>
                    <select class="form-select form-control form-select-sm" id="hrmsDashMonth">
                        @for($m=1;$m<=12;$m++)
                        <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-0">Year</label>
                    <select class="form-select form-control form-select-sm" id="hrmsDashYear">
                        @for($y=date('Y')-1;$y<=date('Y')+1;$y++)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary" id="hrmsDashLoad">Submit</button>
                </div>
            </div>

            <div id="hrmsDashContent" style="display:none;">
                <div class="row g-3 mb-3">
                    <!-- Today -->
                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-header py-2"><h6 class="mb-0">Today's Status</h6></div>
                            <div class="card-body py-2">
                                <p class="mb-1">Status: <span id="hrmsDashStatus" class="badge bg-secondary">--</span></p>
                                <p class="mb-2">In: <strong id="hrmsDashIn">--</strong> | Out: <strong id="hrmsDashOut">--</strong></p>
                                <button class="btn btn-success" id="hrmsDashCheckIn">Check In</button>
                                <button class="btn btn-danger" id="hrmsDashCheckOut">Check Out</button>
                            </div>
                        </div>
                    </div>
                    <!-- Leave Balance -->
                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-header py-2"><h6 class="mb-0">Leave Balance</h6></div>
                            <div class="card-body py-2 text-center">
                                <div class="row">
                                    <div class="col-4"><h4 id="hrmsDashLbTotal">0</h4><small class="text-muted">Total</small></div>
                                    <div class="col-4"><h4 id="hrmsDashLbUsed" class="text-danger">0</h4><small class="text-muted">Used</small></div>
                                    <div class="col-4"><h4 id="hrmsDashLbRem" class="text-success">0</h4><small class="text-muted">Remaining</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Calendar -->
                <div class="card border">
                    <div class="card-header py-2"><h6 class="mb-0">Monthly Attendance</h6></div>
                    <div class="card-body">
                        <div class="d-flex gap-2 mb-2 flex-wrap">
                            <small><span class="badge bg-success">&nbsp;</span> Present</small>
                            <small><span class="badge bg-danger">&nbsp;</span> Absent</small>
                            <small><span class="badge bg-warning">&nbsp;</span> Leave</small>
                            <small><span class="badge bg-info">&nbsp;</span> Half Day</small>
                            <small><span class="badge bg-secondary">&nbsp;</span> Holiday</small>
                        </div>
                        <div id="hrmsDashCalendar" class="d-flex flex-wrap gap-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        </div>{{-- /page-body --}}
    </div>
</div>

@section('js')

<script>
(function(){
"use strict";

// ══════════════════════════════════════
// STAFF DATA FROM DATABASE (read-only)
// ══════════════════════════════════════
var STAFF = @json($staffList);
var STAFF_MAP = {};
STAFF.forEach(function(s){ STAFF_MAP[s.id] = s; });

// ══════════════════════════════════════
// LOCAL STORAGE HELPERS
// ══════════════════════════════════════
var LS = {
    get: function(k, def){ try{ var v = localStorage.getItem('hrms_'+k); return v ? JSON.parse(v) : def; } catch(e){ return def; } },
    set: function(k, v){ localStorage.setItem('hrms_'+k, JSON.stringify(v)); },
};

// ══════════════════════════════════════
// SETTINGS & CONSTANTS
// ══════════════════════════════════════
var WORKING_DAYS = 26;

function currentFY(){
    var now = new Date();
    var y = now.getFullYear();
    return now.getMonth() >= 3 ? y+'-'+(y+1) : (y-1)+'-'+y;
}
function todayStr(){ return new Date().toISOString().slice(0,10); }

// ══════════════════════════════════════
// PAYROLL DATA (FROM DATABASE)
// ══════════════════════════════════════
function getPayroll(sid){
    var s = STAFF_MAP[sid];
    if(!s) return null;
    return {
        salary: s.basic_salary || 0,
        wageType: s.wage_calc_type || 'none',
        shiftHours: s.shift_hours || 8,
        otType: s.ot_type || 'no_ot',
        otHours: s.ot_max_hours || 1,
        otMin: s.ot_max_minutes || 30,
        pfEnabled: s.pf_enabled,
        pfPct: s.pf_percentage || 12,
        group: s.group ? s.group.name : '--',
        groupId: s.group_id,
    };
}
function getPayrollGroup(sid){ var p = getPayroll(sid); return p ? p.group : '--'; }
function getPayrollGroupId(sid){ var s = STAFF_MAP[sid]; return s ? s.group_id : null; }

// ══════════════════════════════════════
// WAGE CALCULATION ENGINE
// ══════════════════════════════════════
function calcDailyWage(p){ return p ? (parseFloat(p.salary)||0) / WORKING_DAYS : 0; }
function calcHourlyWage(p){
    if(!p || p.wageType !== 'hour_based') return 0;
    var shift = parseInt(p.shiftHours) || 8;
    return calcDailyWage(p) / shift;
}
function capOt(p, hrs){
    if(!p || p.otType === 'no_ot') return 0;
    if(p.otType === 'hours') return Math.min(hrs, parseInt(p.otHours)||1);
    if(p.otType === 'minutes') return Math.min(hrs, (parseInt(p.otMin)||30)/60);
    return hrs;
}

function calcWages(sid, status, checkIn, checkOut, isOt){
    var p = getPayroll(sid);
    if(!p) return {base:0, otWage:0, otHrs:0};

    var daily = calcDailyWage(p);
    var hourly = calcHourlyWage(p);
    var worked = 0;
    if(checkIn && checkOut){
        var a = checkIn.split(':'), b = checkOut.split(':');
        worked = (parseInt(b[0])*60+parseInt(b[1]) - parseInt(a[0])*60 - parseInt(a[1])) / 60;
        if(worked < 0) worked = 0;
    }
    var base = 0, otWage = 0, otHrs = 0;

    if(status === 'present'){
        if(isOt){
            otHrs = capOt(p, worked);
            otWage = r2(hourly * otHrs);
        } else if(p.wageType === 'hour_based' && worked > 0){
            var shift = parseInt(p.shiftHours)||8;
            base = r2(hourly * Math.min(worked, shift));
            if(worked > shift && p.otType !== 'no_ot'){
                otHrs = capOt(p, worked - shift);
                otWage = r2(hourly * otHrs);
            }
        } else {
            base = r2(daily);
        }
    } else if(status === 'half_day'){
        base = r2(daily / 2);
    } else if(status === 'leave'){
        var leaves = getLeaves();
        var hasApproved = leaves.some(function(l){ return l.staffId == sid && l.status === 'approved' && l.type !== 'unpaid'; });
        base = hasApproved ? r2(daily) : 0;
    } else if(status === 'holiday' && worked > 0){
        otHrs = capOt(p, worked);
        otWage = r2(hourly * otHrs);
    }

    return {base: base, otWage: otWage, otHrs: r2(otHrs)};
}
function r2(n){ return Math.round(n*100)/100; }

// ══════════════════════════════════════
// ATTENDANCE STORAGE
// ══════════════════════════════════════
function getAttendance(date){
    return LS.get('att_'+date, null);
}
function setAttendance(date, data){
    LS.set('att_'+date, data);
}
function ensureAttendance(date){
    var existing = getAttendance(date);
    if(existing) return existing;

    var d = new Date(date+'T00:00:00');
    var isSunday = d.getDay() === 0;
    var leaves = getLeaves();
    var approvedLeaveStaff = {};
    leaves.forEach(function(l){
        if(l.date === date && l.status === 'approved') approvedLeaveStaff[l.staffId] = true;
    });

    var records = {};
    STAFF.forEach(function(s){
        var status = 'present';
        var isOt = false;
        if(isSunday) { status = 'holiday'; }
        else if(approvedLeaveStaff[s.id]) { status = 'leave'; }

        records[s.id] = {
            staffId: s.id,
            status: status,
            checkIn: '',
            checkOut: '',
            isOt: isOt,
        };
    });

    // Calculate wages
    Object.keys(records).forEach(function(sid){
        var rec = records[sid];
        var w = calcWages(sid, rec.status, rec.checkIn, rec.checkOut, rec.isOt);
        rec.base = w.base;
        rec.otWage = w.otWage;
        rec.otHrs = w.otHrs;
    });

    setAttendance(date, records);
    return records;
}

// ══════════════════════════════════════
// LEAVES STORAGE
// ══════════════════════════════════════
function getLeaves(){ return LS.get('leaves', []); }
function getBalances(){ return LS.get('balances', {}); }

// ──────────────────────────────────────
// INIT
// ──────────────────────────────────────
jQuery(function(){

    // ══════════════════════════════════
    // STAFF DASHBOARD
    // ══════════════════════════════════
    jQuery('#hrmsDashLoad, #hrmsDashStaff').on('click change', loadStaffDash);

    function loadStaffDash(){
        var sid = jQuery('#hrmsDashStaff').val();
        if(!sid){ jQuery('#hrmsDashContent').hide(); return; }

        var today = todayStr();
        var att = ensureAttendance(today);
        var todayRec = att[sid];

        // Today's status
        if(todayRec){
            var sc = {present:'success',absent:'danger',leave:'warning',half_day:'info',holiday:'secondary'};
            jQuery('#hrmsDashStatus').removeClass().addClass('badge bg-'+(sc[todayRec.status]||'secondary')).text(todayRec.status.replace('_',' '));
            jQuery('#hrmsDashIn').text(todayRec.checkIn || '--');
            jQuery('#hrmsDashOut').text(todayRec.checkOut || '--');
        }

        // Leave balance
        var bals = getBalances();
        var fy = currentFY();
        var b = (bals[fy]||{})[sid];
        jQuery('#hrmsDashLbTotal').text(b ? b.total : 0);
        jQuery('#hrmsDashLbUsed').text(b ? b.used : 0);
        jQuery('#hrmsDashLbRem').text(b ? b.remaining : 0);

        // Calendar
        var month = parseInt(jQuery('#hrmsDashMonth').val());
        var year = parseInt(jQuery('#hrmsDashYear').val());
        var daysInMonth = new Date(year, month, 0).getDate();
        var calHtml = '';
        var sc2 = {present:'success',absent:'danger',leave:'warning',half_day:'info',holiday:'secondary'};

        for(var d=1; d<=daysInMonth; d++){
            var ds = year+'-'+('0'+month).slice(-2)+'-'+('0'+d).slice(-2);
            var recs = getAttendance(ds);
            var r = recs ? recs[sid] : null;
            var bg = r ? (sc2[r.status]||'light') : 'light';
            var textCls = (bg !== 'light' && bg !== 'warning') ? ' text-white' : '';
            var border = (r && r.isOt) ? 'border:3px solid #333;' : '';
            calHtml += '<div class="text-center rounded p-2 bg-'+bg+textCls+'" style="width:42px;height:42px;line-height:22px;'+border+'"><strong>'+d+'</strong></div>';
        }
        jQuery('#hrmsDashCalendar').html(calHtml);
        jQuery('#hrmsDashContent').show();
    }

    // Check-in
    jQuery('#hrmsDashCheckIn').on('click', function(){
        var sid = jQuery('#hrmsDashStaff').val();
        if(!sid) return;
        var today = todayStr();
        var att = ensureAttendance(today);
        if(!att[sid]) return;
        var now = new Date();
        att[sid].checkIn = ('0'+now.getHours()).slice(-2)+':'+('0'+now.getMinutes()).slice(-2);
        var w = calcWages(sid, att[sid].status, att[sid].checkIn, att[sid].checkOut, att[sid].isOt);
        att[sid].base = w.base; att[sid].otWage = w.otWage; att[sid].otHrs = w.otHrs;
        setAttendance(today, att);
        loadStaffDash();
    });

    // Check-out
    jQuery('#hrmsDashCheckOut').on('click', function(){
        var sid = jQuery('#hrmsDashStaff').val();
        if(!sid) return;
        var today = todayStr();
        var att = ensureAttendance(today);
        if(!att[sid]) return;
        var now = new Date();
        att[sid].checkOut = ('0'+now.getHours()).slice(-2)+':'+('0'+now.getMinutes()).slice(-2);
        var w = calcWages(sid, att[sid].status, att[sid].checkIn, att[sid].checkOut, att[sid].isOt);
        att[sid].base = w.base; att[sid].otWage = w.otWage; att[sid].otHrs = w.otHrs;
        setAttendance(today, att);
        loadStaffDash();
    });

}); // end document ready
})();
</script>

@endsection

@include('common.footer')
