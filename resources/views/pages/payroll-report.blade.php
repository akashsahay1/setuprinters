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
                                    <button type="submit" class="btn btn-primary" id="hrmsPrGenerate" style="background-color:#7366FF;border-color:#7366FF;">Generate</button>
                                    <button type="button" class="btn btn-success" id="hrmsPrSave" style="display:none;background-color:#7366FF;border-color:#7366FF;">Save Payroll</button>
                                    <button type="button" class="btn btn-success" id="hrmsPrCsv" style="display:none;">CSV</button>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="hrmsPrTable" style="font-size:0.85rem;width:100%;">
                                <thead class="table-light">
                                    <tr class="text-nowrap">
                                        <th style="width:30px;"></th>
                                        <th>Department</th>
                                        <th>Name</th>
                                        <th class="text-end">Basic</th>
                                        <th class="text-center">Absent</th>
                                        <th class="text-end">Advance</th>
                                        <th class="text-end">Final Pay</th>
                                        <th class="text-end">Paid Cash</th>
                                        <th class="d-none"></th>
                                        <th class="d-none"></th>
                                        <th class="d-none"></th>
                                        <th class="d-none"></th>
                                        <th class="d-none"></th>
                                        <th class="d-none"></th>
                                        <th class="d-none"></th>
                                        <th class="d-none"></th>
                                    </tr>
                                </thead>
                                <tbody id="hrmsPrBody">
                                </tbody>
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
    position: relative;
    padding-left: 26px;
}
#hrmsPrTable td.dt-control::before {
    content: '+';
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border: 1px solid #7366FF;
    border-radius: 50%;
    color: #7366FF;
    font-size: 14px;
    font-weight: bold;
    line-height: 1;
    position: absolute;
    left: 4px;
    top: 50%;
    transform: translateY(-50%);
}
#hrmsPrTable tr.shown td.dt-control::before {
    content: '\2212';
    background-color: #7366FF;
    color: #fff;
}
#hrmsPrTable tr.child-row td {
    background-color: #f8f9fa;
    padding: 10px 16px;
}
#hrmsPrTable tr.child-row .child-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 8px 24px;
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
            var paidPf       = s.pf_enabled ? r2(basicAmount * (parseFloat(s.pf_percentage) || 0) / 100) : 0;
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
            html += '<td class="text-end">'+row.basic.toFixed(2)+'</td>';
            html += '<td class="text-center">'+row.absent+'</td>';
            html += '<td><input type="number" class="form-control form-control-sm text-end pr-advance" data-idx="'+idx+'" value="'+row.advance.toFixed(2)+'" step="0.01" min="0" style="width:90px;"></td>';
            html += '<td class="text-end fw-bold pr-final">'+row.finalPay.toFixed(2)+'</td>';
            html += '<td><input type="number" class="form-control form-control-sm text-end pr-cash" data-idx="'+idx+'" value="'+row.paidCash.toFixed(2)+'" step="0.01" min="0" style="width:90px;"></td>';
            // Hidden columns (for child row data — not rendered as visible columns)
            html += '<td class="d-none">'+row.account+'</td>';
            html += '<td class="d-none">'+row.dailySal.toFixed(2)+'</td>';
            html += '<td class="d-none">'+row.daysInMonth+'</td>';
            html += '<td class="d-none">'+row.absentDed.toFixed(2)+'</td>';
            html += '<td class="d-none">'+row.otDays+'</td>';
            html += '<td class="d-none">'+row.otAmount.toFixed(2)+'</td>';
            html += '<td class="d-none">'+row.paidBank.toFixed(2)+'</td>';
            html += '<td class="d-none">'+row.paidPf.toFixed(2)+'</td>';
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
            + '<div class="child-item"><label>Days in Month</label><span>'+row.daysInMonth+'</span></div>'
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

}); // end document ready
})();
</script>

@endsection

@include('common.footer')
