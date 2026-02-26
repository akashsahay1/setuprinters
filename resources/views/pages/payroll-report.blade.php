@include('common.header', ['title' => 'Payroll Report'])

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
    @include('common.innerheader', ['title' => 'Payroll Report'])
    <!-- Page Header Ends -->

    <div class="page-body-wrapper">

        <!-- Page Sidebar Start-->
        @include('common.sidebar')
        <!-- Page Sidebar Ends-->

        <div class="page-body">
            <div class="container-fluid mt-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Payroll Report</h5>
                    </div>
                    <div class="card-body">
                        <!-- View Toggle -->
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <input type="radio" class="btn-check" name="prViewMode" id="prViewMonthly" value="monthly" checked autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm" for="prViewMonthly">Monthly (All Staff)</label>
                            <input type="radio" class="btn-check" name="prViewMode" id="prViewYearly" value="yearly" autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm" for="prViewYearly">Yearly (Single Staff)</label>
                        </div>

                        <!-- Monthly View Form -->
                        <form id="hrmsPrForm" method="POST" action="javascript:void(0);">
                            <div class="row g-2 mb-3">
                                <div class="col-md-2">
                                    <label class="form-label mb-0">Month</label>
                                    <select class="form-select form-control form-select-sm" id="hrmsPrMonth">
                                        @for($m=1;$m<=12;$m++)
                                        <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label mb-0">Year</label>
                                    <select class="form-select form-control form-select-sm" id="hrmsPrYear">
                                        @for($y=date('Y')-1;$y<=date('Y')+1;$y++)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary" id="hrmsPrGenerate">Generate</button>
                                    <button type="button" class="btn btn-primary" id="hrmsPrSave" style="display:none;">Save Payroll</button>
                                    <button type="button" class="btn btn-success" id="hrmsPrCsv" style="display:none;">CSV</button>
                                </div>
                            </div>
                        </form>

                        <!-- Yearly View Form (hidden by default) -->
                        <form id="hrmsPrYearlyForm" method="POST" action="javascript:void(0);" style="display:none;">
                            <div class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label mb-0">Financial Year</label>
                                    <select class="form-select form-control form-select-sm" id="hrmsPrFy"></select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-0">Staff</label>
                                    <select class="form-select form-control form-select-sm" id="hrmsPrStaff">
                                        <option value="">-- Select Staff --</option>
                                        @foreach($staffList as $s)
                                        <option value="{{ $s->id }}">{{ $s->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary" id="hrmsPrYearlyGenerate">Generate</button>
                                    <button type="button" class="btn btn-success" id="hrmsPrYearlyCsv" style="display:none;">CSV</button>
                                </div>
                            </div>
                        </form>

                        <!-- Monthly Table -->
                        <div class="table-responsive" id="hrmsPrMonthlyTableWrap">
                            <table class="table table-bordered table-sm" id="hrmsPrTable" style="font-size:0.85rem;width:100%;">
                                <thead class="table-light">
                                    <tr class="text-nowrap">
                                        <th style="width:30px;"></th>
                                        <th>Department</th>
                                        <th>Name</th>
                                        <th class="text-center">Days in Month</th>
                                        <th class="text-center">Absent</th>
                                        <th class="text-center">Final Pay</th>
                                        <th class="text-center">Advance</th>
                                        <th class="text-center">Paid Cash</th>
                                    </tr>
                                </thead>
                                <tbody id="hrmsPrBody">
                                </tbody>
                            </table>
                        </div>

                        <!-- Yearly Table (hidden by default) -->
                        <div class="table-responsive" id="hrmsPrYearlyTableWrap" style="display:none;">
                            <table class="table table-bordered table-sm" id="hrmsPrYearlyTable" style="font-size:0.85rem;width:100%;">
                                <thead class="table-light">
                                    <tr class="text-nowrap">
                                        <th>Month</th>
                                        <th class="text-center">Basic</th>
                                        <th class="text-center">1 Day Sal</th>
                                        <th class="text-center">Absent</th>
                                        <th class="text-center">Absent Ded</th>
                                        <th class="text-center">OT Days</th>
                                        <th class="text-center">Final Pay</th>
                                        <th class="text-center">OT Amount</th>
                                        <th class="text-center">Advance</th>
                                        <th class="text-center">PF</th>
                                        <th class="text-center">Paid Bank</th>
                                        <th class="text-center">Paid Cash</th>
                                    </tr>
                                </thead>
                                <tbody id="hrmsPrYearlyBody">
                                </tbody>
                                <tfoot class="table-light fw-bold" id="hrmsPrYearlyFoot" style="display:none;">
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- /page-body --}}
    </div>
</div>

<style>
#hrmsPrTable td.dt-control {
    cursor: pointer;
    text-align: center;
    vertical-align: middle;
}
#hrmsPrTable td.dt-control::before {
    content: '+';
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border: 1px solid var(--theme-primary);
    border-radius: 50%;
    color: var(--theme-primary);
    font-size: 14px;
    font-weight: bold;
    line-height: 1;
}
#hrmsPrTable tr.shown td.dt-control::before {
    content: '\2212';
    background-color: var(--theme-primary);
    color: #fff;
}
#hrmsPrTable tr.child-row td {
    background-color: #f8f9fa;
    padding: 10px 16px;
}
#hrmsPrTable tr.child-row .child-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px 32px;
}
#hrmsPrTable tr.child-row .child-item label {
    font-size: 0.75rem;
    color: #6c757d;
    display: block;
    margin-bottom: 0;
}
#hrmsPrTable tr.child-row .child-item span {
    font-weight: 600;
    font-size: 0.85rem;
}
</style>

@section('js')

<script>
(function(){
"use strict";

var STAFF = @json($staffList);
var CSRF  = '{{ csrf_token() }}';
var AJAX  = '{{ url("ajax") }}';
var WORKING_DAYS = 26;

function r2(n){ return Math.round(n*100)/100; }

// ──────────────────────────────────────
// PAYROLL REPORT
// ──────────────────────────────────────
jQuery(function(){

    var prData = [];
    var prMonth = 0, prYear = 0;

    jQuery('#hrmsPrForm').on('submit', function(e){
        e.preventDefault();
        prMonth = parseInt(jQuery('#hrmsPrMonth').val());
        prYear  = parseInt(jQuery('#hrmsPrYear').val());

        var btn = jQuery('#hrmsPrGenerate');
        btn.prop('disabled', true).text('Processing...');

        // Step 1: Process attendance from scanned_barcodes
        jQuery.ajax({
            url: AJAX,
            type: 'POST',
            data: { _token: CSRF, process_attendance: 1, month: prMonth, year: prYear },
            success: function(res){
                if(!res.status){
                    btn.prop('disabled', false).text('Generate');
                    Swal.fire('Error', res.message || 'Failed to process attendance', 'error');
                    return;
                }
                // Step 2: Fetch aggregated data
                fetchPayrollData(btn);
            },
            error: function(){
                btn.prop('disabled', false).text('Generate');
                Swal.fire('Error', 'Network error during attendance processing', 'error');
            }
        });
    });

    function fetchPayrollData(btn){
        jQuery.ajax({
            url: AJAX,
            type: 'POST',
            data: { _token: CSRF, fetch_payroll_data: 1, month: prMonth, year: prYear },
            success: function(res){
                btn.prop('disabled', false).text('Generate');
                if(!res.status){
                    Swal.fire('Error', res.message || 'Failed to fetch payroll data', 'error');
                    return;
                }
                renderReport(res);
            },
            error: function(){
                btn.prop('disabled', false).text('Generate');
                Swal.fire('Error', 'Network error fetching payroll data', 'error');
            }
        });
    }

    function renderReport(res){
        var attendance = res.attendance || {};
        var saved      = res.payroll_records || {};
        var daysInMonth = res.days_in_month || 30;

        prData = [];
        var html = '';

        STAFF.forEach(function(s){
            var att  = attendance[s.id] || {};
            var prev = saved[s.id] || {};

            var basicAmount  = parseFloat(s.basic_salary) || 0;
            var oneDaySalary = WORKING_DAYS > 0 ? r2(basicAmount / WORKING_DAYS) : 0;
            var daysAbsent   = parseFloat(att.days_absent) || 0;
            var absentDed    = r2(daysAbsent * oneDaySalary);
            var daysOt       = parseInt(att.days_overtime) || 0;
            var otAmount     = parseFloat(att.total_ot) || 0;
            var paidPf       = s.pf_enabled ? r2(parseFloat(s.pf_amount) || 0) : 0;
            var advance      = parseFloat(prev.advance_amount) || 0;
            var paidCash     = parseFloat(prev.paid_cash) || 0;
            var finalPay     = r2(basicAmount - absentDed + otAmount - advance - paidPf);
            var paidBank     = r2(finalPay - paidCash);

            var row = {
                staffId: s.id,
                dept: s.group ? s.group.name : '--',
                name: s.full_name,
                account: s.bank_account || '--',
                basic: basicAmount,
                dailySal: oneDaySalary,
                daysInMonth: daysInMonth,
                absent: daysAbsent,
                absentDed: absentDed,
                otDays: daysOt,
                otAmount: r2(otAmount),
                advance: advance,
                finalPay: finalPay,
                paidBank: paidBank,
                paidPf: paidPf,
                paidCash: paidCash
            };
            prData.push(row);
            var idx = prData.length - 1;

            html += '<tr data-idx="'+idx+'">';
            html += '<td class="dt-control"></td>';
            html += '<td>'+row.dept+'</td>';
            html += '<td>'+row.name+'</td>';
            html += '<td class="text-center">'+row.daysInMonth+'</td>';
            html += '<td class="text-center">'+row.absent+'</td>';
            html += '<td class="text-center fw-bold pr-final">'+row.finalPay.toFixed(2)+'</td>';
            html += '<td class="text-center"><input type="text" inputmode="decimal" class="form-control form-control-sm text-center pr-advance pr-numeric mx-auto" data-idx="'+idx+'" value="'+row.advance.toFixed(2)+'" style="width:90px;"></td>';
            html += '<td class="text-center"><input type="text" inputmode="decimal" class="form-control form-control-sm text-center pr-cash pr-numeric mx-auto" data-idx="'+idx+'" value="'+row.paidCash.toFixed(2)+'" style="width:90px;"></td>';
            html += '</tr>';
        });

        if(!html) html = '<tr><td colspan="8" class="text-center text-muted">No data</td></tr>';
        jQuery('#hrmsPrBody').html(html);
        jQuery('#hrmsPrCsv').show();
        jQuery('#hrmsPrSave').show();
    }

    function buildChildHtml(row){
        return '<tr class="child-row"><td colspan="8">'
            + '<div class="child-grid">'
            + '<div class="child-item"><label>Account No</label><span>'+row.account+'</span></div>'
            + '<div class="child-item"><label>1 Day Salary</label><span>'+row.dailySal.toFixed(2)+'</span></div>'
            + '<div class="child-item"><label>Basic</label><span>'+row.basic.toFixed(2)+'</span></div>'
            + '<div class="child-item"><label>Absent Deduction</label><span>'+row.absentDed.toFixed(2)+'</span></div>'
            + '<div class="child-item"><label>OT Days</label><span>'+row.otDays+'</span></div>'
            + '<div class="child-item"><label>OT Amount</label><span>'+row.otAmount.toFixed(2)+'</span></div>'
            + '<div class="child-item"><label>Paid in Bank</label><span class="child-bank">'+row.paidBank.toFixed(2)+'</span></div>'
            + '<div class="child-item"><label>Paid PF</label><span>'+row.paidPf.toFixed(2)+'</span></div>'
            + '</div></td></tr>';
    }

    // Toggle child row on +/- click
    jQuery(document).on('click', '#hrmsPrTable td.dt-control', function(){
        var tr = jQuery(this).closest('tr');
        var idx = parseInt(tr.data('idx'));
        var row = prData[idx];
        if(!row) return;

        if(tr.hasClass('shown')){
            tr.removeClass('shown');
            tr.next('tr.child-row').remove();
        } else {
            tr.addClass('shown');
            tr.after(buildChildHtml(row));
        }
    });

    // Allow only digits and one decimal point in numeric inputs
    jQuery(document).on('keydown', '.pr-numeric', function(e){
        var key = e.key;
        if(['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'].indexOf(key) !== -1) return;
        if((e.ctrlKey || e.metaKey) && ['a','c','v','x'].indexOf(key.toLowerCase()) !== -1) return;
        if(key === '.' && this.value.indexOf('.') === -1) return;
        if(key >= '0' && key <= '9') return;
        e.preventDefault();
    });

    // Block paste of non-numeric content
    jQuery(document).on('paste', '.pr-numeric', function(e){
        var paste = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
        if(!/^\d*\.?\d*$/.test(paste)) e.preventDefault();
    });

    // Format to 2 decimal places on blur
    jQuery(document).on('blur', '.pr-numeric', function(){
        var val = parseFloat(this.value) || 0;
        if(val < 0) val = 0;
        this.value = val.toFixed(2);
    });

    // Recalculate on advance/cash input change
    jQuery(document).on('change', '.pr-advance, .pr-cash', function(){
        var idx = parseInt(jQuery(this).data('idx'));
        var row = prData[idx];
        if(!row) return;

        var tr = jQuery('tr[data-idx="'+idx+'"]');
        row.advance  = parseFloat(tr.find('.pr-advance').val()) || 0;
        row.paidCash = parseFloat(tr.find('.pr-cash').val()) || 0;
        row.finalPay = r2(row.basic - row.absentDed + row.otAmount - row.advance - row.paidPf);
        row.paidBank = r2(row.finalPay - row.paidCash);

        tr.find('.pr-final').text(row.finalPay.toFixed(2));
        // Update child row if open
        var childRow = tr.next('tr.child-row');
        if(childRow.length){
            childRow.find('.child-bank').text(row.paidBank.toFixed(2));
        }
    });

    // Save Payroll
    jQuery('#hrmsPrSave').on('click', function(){
        if(!prData.length) return;

        var btn = jQuery(this);
        btn.prop('disabled', true).text('Saving...');

        var items = prData.map(function(row){
            return {
                staff_id: row.staffId,
                month: prMonth,
                year: prYear,
                basic_amount: row.basic,
                one_day_salary: row.dailySal,
                days_in_month: row.daysInMonth,
                days_absent: row.absent,
                absent_deduction: row.absentDed,
                days_overtime: row.otDays,
                overtime_amount: row.otAmount,
                advance_amount: row.advance,
                final_pay: row.finalPay,
                paid_in_bank: row.paidBank,
                paid_pf: row.paidPf,
                paid_cash: row.paidCash,
            };
        });

        jQuery.ajax({
            url: AJAX,
            type: 'POST',
            data: { _token: CSRF, save_payroll_records: 1, items: items },
            success: function(res){
                btn.prop('disabled', false).text('Save Payroll');
                if(res.status){
                    Swal.fire('Saved', res.message, 'success');
                } else {
                    Swal.fire('Error', res.message || 'Failed to save', 'error');
                }
            },
            error: function(){
                btn.prop('disabled', false).text('Save Payroll');
                Swal.fire('Error', 'Network error', 'error');
            }
        });
    });

    // CSV Export (all columns)
    jQuery('#hrmsPrCsv').on('click', function(){
        if(!prData.length) return;
        var csv = 'Department,Name,Account No,Basic Amount,1 Day Salary,Days in Month,Days Absent,Absent Deduction,OT Days,OT Amount,Advance,Final Pay,Paid in Bank,Paid PF,Paid Cash\n';
        prData.forEach(function(r){
            csv += '"'+r.dept+'","'+r.name+'","'+r.account+'",';
            csv += r.basic.toFixed(2)+','+r.dailySal.toFixed(2)+','+r.daysInMonth+',';
            csv += r.absent+','+r.absentDed.toFixed(2)+','+r.otDays+',';
            csv += r.otAmount.toFixed(2)+','+r.advance.toFixed(2)+','+r.finalPay.toFixed(2)+',';
            csv += r.paidBank.toFixed(2)+','+r.paidPf.toFixed(2)+','+r.paidCash.toFixed(2)+'\n';
        });
        var blob = new Blob([csv], {type:'text/csv'});
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'payroll_'+prMonth+'_'+prYear+'.csv';
        a.click();
    });

    // ──────────────────────────────────────
    // VIEW MODE TOGGLE
    // ──────────────────────────────────────
    jQuery('input[name="prViewMode"]').on('change', function(){
        var mode = jQuery(this).val();
        if(mode === 'monthly'){
            jQuery('#hrmsPrForm').show();
            jQuery('#hrmsPrYearlyForm').hide();
            jQuery('#hrmsPrMonthlyTableWrap').show();
            jQuery('#hrmsPrYearlyTableWrap').hide();
        } else {
            jQuery('#hrmsPrForm').hide();
            jQuery('#hrmsPrYearlyForm').show();
            jQuery('#hrmsPrMonthlyTableWrap').hide();
            jQuery('#hrmsPrYearlyTableWrap').show();
            jQuery('#hrmsPrSave').hide();
            jQuery('#hrmsPrCsv').hide();
        }
    });

    // ──────────────────────────────────────
    // YEARLY (SINGLE STAFF) VIEW
    // ──────────────────────────────────────
    var FY_MONTHS = [
        {month: 4, label: 'April'},
        {month: 5, label: 'May'},
        {month: 6, label: 'June'},
        {month: 7, label: 'July'},
        {month: 8, label: 'August'},
        {month: 9, label: 'September'},
        {month: 10, label: 'October'},
        {month: 11, label: 'November'},
        {month: 12, label: 'December'},
        {month: 1, label: 'January'},
        {month: 2, label: 'February'},
        {month: 3, label: 'March'}
    ];

    // Build FY dropdown options
    (function buildFyOptions(){
        var now = new Date();
        var y = now.getFullYear();
        var currentStart = now.getMonth() >= 3 ? y : y - 1;
        var html = '';
        for(var i = currentStart - 1; i <= currentStart + 1; i++){
            var fy = i + '-' + (i + 1);
            var sel = (i === currentStart) ? ' selected' : '';
            html += '<option value="' + fy + '"' + sel + '>FY ' + fy + '</option>';
        }
        jQuery('#hrmsPrFy').html(html);
    })();

    var yearlyData = [];
    var yearlyStaffName = '';

    // Yearly form submit
    jQuery('#hrmsPrYearlyForm').on('submit', function(e){
        e.preventDefault();
        var staffId = jQuery('#hrmsPrStaff').val();
        var fy = jQuery('#hrmsPrFy').val();

        if(!staffId){
            Swal.fire('Error', 'Please select a staff member', 'warning');
            return;
        }

        var btn = jQuery('#hrmsPrYearlyGenerate');
        btn.prop('disabled', true).text('Loading...');

        jQuery.ajax({
            url: AJAX,
            type: 'POST',
            data: { _token: CSRF, fetch_yearly_payroll: 1, staff_id: staffId, fy: fy },
            success: function(res){
                btn.prop('disabled', false).text('Generate');
                if(!res.status){
                    Swal.fire('Error', res.message || 'Failed to fetch data', 'error');
                    return;
                }
                renderYearlyReport(res, fy);
            },
            error: function(){
                btn.prop('disabled', false).text('Generate');
                Swal.fire('Error', 'Network error', 'error');
            }
        });
    });

    function renderYearlyReport(res, fy){
        var staff = res.staff;
        var records = res.records || [];
        var parts = fy.split('-');
        var startYear = parseInt(parts[0]);
        var endYear = parseInt(parts[1]);

        yearlyStaffName = staff.full_name || '';

        // Index records by "month-year" key
        var recordMap = {};
        records.forEach(function(r){
            recordMap[r.month + '-' + r.year] = r;
        });

        yearlyData = [];
        var html = '';
        var totals = { basic:0, absent:0, absentDed:0, otDays:0, otAmount:0, advance:0, pf:0, finalPay:0, paidBank:0, paidCash:0 };
        var dash = '<span class="text-muted">--</span>';

        FY_MONTHS.forEach(function(fm){
            var yr = fm.month >= 4 ? startYear : endYear;
            var key = fm.month + '-' + yr;
            var rec = recordMap[key] || null;

            var row = {
                monthLabel: fm.label + ' ' + yr,
                basic: rec ? parseFloat(rec.basic_amount) : 0,
                dailySal: rec ? parseFloat(rec.one_day_salary) : 0,
                absent: rec ? parseFloat(rec.days_absent) : 0,
                absentDed: rec ? parseFloat(rec.absent_deduction) : 0,
                otDays: rec ? parseInt(rec.days_overtime) : 0,
                otAmount: rec ? parseFloat(rec.overtime_amount) : 0,
                advance: rec ? parseFloat(rec.advance_amount) : 0,
                pf: rec ? parseFloat(rec.paid_pf) : 0,
                finalPay: rec ? parseFloat(rec.final_pay) : 0,
                paidBank: rec ? parseFloat(rec.paid_in_bank) : 0,
                paidCash: rec ? parseFloat(rec.paid_cash) : 0,
                hasData: !!rec
            };
            yearlyData.push(row);

            if(rec){
                totals.basic += row.basic;
                totals.absent += row.absent;
                totals.absentDed += row.absentDed;
                totals.otDays += row.otDays;
                totals.otAmount += row.otAmount;
                totals.advance += row.advance;
                totals.pf += row.pf;
                totals.finalPay += row.finalPay;
                totals.paidBank += row.paidBank;
                totals.paidCash += row.paidCash;
            }

            html += '<tr' + (rec ? '' : ' class="text-muted"') + '>';
            html += '<td>' + row.monthLabel + '</td>';
            if(rec){
                html += '<td class="text-center">' + row.basic.toFixed(2) + '</td>';
                html += '<td class="text-center">' + row.dailySal.toFixed(2) + '</td>';
                html += '<td class="text-center">' + row.absent + '</td>';
                html += '<td class="text-center">' + row.absentDed.toFixed(2) + '</td>';
                html += '<td class="text-center">' + row.otDays + '</td>';
                html += '<td class="text-center fw-bold">' + row.finalPay.toFixed(2) + '</td>';
                html += '<td class="text-center">' + row.otAmount.toFixed(2) + '</td>';
                html += '<td class="text-center">' + row.advance.toFixed(2) + '</td>';
                html += '<td class="text-center">' + row.pf.toFixed(2) + '</td>';
                html += '<td class="text-center">' + row.paidBank.toFixed(2) + '</td>';
                html += '<td class="text-center">' + row.paidCash.toFixed(2) + '</td>';
            } else {
                for(var c = 0; c < 11; c++) html += '<td class="text-center">' + dash + '</td>';
            }
            html += '</tr>';
        });

        if(!html) html = '<tr><td colspan="12" class="text-center text-muted">No data</td></tr>';
        jQuery('#hrmsPrYearlyBody').html(html);

        // Totals footer
        var foot = '<tr>';
        foot += '<td>TOTAL</td>';
        foot += '<td class="text-center">' + r2(totals.basic).toFixed(2) + '</td>';
        foot += '<td class="text-center">--</td>';
        foot += '<td class="text-center">' + r2(totals.absent) + '</td>';
        foot += '<td class="text-center">' + r2(totals.absentDed).toFixed(2) + '</td>';
        foot += '<td class="text-center">' + totals.otDays + '</td>';
        foot += '<td class="text-center fw-bold">' + r2(totals.finalPay).toFixed(2) + '</td>';
        foot += '<td class="text-center">' + r2(totals.otAmount).toFixed(2) + '</td>';
        foot += '<td class="text-center">' + r2(totals.advance).toFixed(2) + '</td>';
        foot += '<td class="text-center">' + r2(totals.pf).toFixed(2) + '</td>';
        foot += '<td class="text-center">' + r2(totals.paidBank).toFixed(2) + '</td>';
        foot += '<td class="text-center">' + r2(totals.paidCash).toFixed(2) + '</td>';
        foot += '</tr>';
        jQuery('#hrmsPrYearlyFoot').html(foot).show();

        jQuery('#hrmsPrYearlyCsv').show();
    }

    // Yearly CSV Export
    jQuery('#hrmsPrYearlyCsv').on('click', function(){
        if(!yearlyData.length) return;
        var fy = jQuery('#hrmsPrFy').val();
        var csv = 'Yearly Payroll Report - ' + yearlyStaffName + ' - FY ' + fy + '\n';
        csv += 'Month,Basic,1 Day Salary,Days Absent,Absent Ded,OT Days,Final Pay,OT Amount,Advance,PF,Paid Bank,Paid Cash\n';

        var totals = { basic:0, absent:0, absentDed:0, otDays:0, otAmount:0, advance:0, pf:0, finalPay:0, paidBank:0, paidCash:0 };

        yearlyData.forEach(function(r){
            if(r.hasData){
                csv += '"' + r.monthLabel + '",';
                csv += r.basic.toFixed(2) + ',' + r.dailySal.toFixed(2) + ',';
                csv += r.absent + ',' + r.absentDed.toFixed(2) + ',';
                csv += r.otDays + ',' + r.finalPay.toFixed(2) + ',';
                csv += r.otAmount.toFixed(2) + ',';
                csv += r.advance.toFixed(2) + ',' + r.pf.toFixed(2) + ',';
                csv += r.paidBank.toFixed(2) + ',';
                csv += r.paidCash.toFixed(2) + '\n';

                totals.basic += r.basic;
                totals.absent += r.absent;
                totals.absentDed += r.absentDed;
                totals.otDays += r.otDays;
                totals.otAmount += r.otAmount;
                totals.advance += r.advance;
                totals.pf += r.pf;
                totals.finalPay += r.finalPay;
                totals.paidBank += r.paidBank;
                totals.paidCash += r.paidCash;
            } else {
                csv += '"' + r.monthLabel + '",,,,,,,,,,,\n';
            }
        });

        csv += 'TOTAL,' + r2(totals.basic).toFixed(2) + ',--,' + r2(totals.absent) + ',';
        csv += r2(totals.absentDed).toFixed(2) + ',' + totals.otDays + ',';
        csv += r2(totals.finalPay).toFixed(2) + ',' + r2(totals.otAmount).toFixed(2) + ',';
        csv += r2(totals.advance).toFixed(2) + ',' + r2(totals.pf).toFixed(2) + ',';
        csv += r2(totals.paidBank).toFixed(2) + ',' + r2(totals.paidCash).toFixed(2) + '\n';

        var blob = new Blob([csv], {type: 'text/csv'});
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'payroll_yearly_' + yearlyStaffName.replace(/\s+/g, '_') + '_FY' + fy + '.csv';
        a.click();
    });

}); // end document ready
})();
</script>

@endsection

@include('common.footer')
