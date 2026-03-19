@include('common.header', ['title' => 'Attendance Calculation'])

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
    @include('common.innerheader', ['title' => 'Attendance Calculation'])
    <!-- Page Header Ends -->

    <div class="page-body-wrapper">

        <!-- Page Sidebar Start-->
        @include('common.sidebar')
        <!-- Page Sidebar Ends-->

        <div class="page-body">
            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Attendance Calculation</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-3">
                                    <div class="col-md-2">
                                        <label class="form-label mb-1">Month</label>
                                        <select class="form-select form-select-sm" id="attCalcMonth">
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label mb-1">Year</label>
                                        <select class="form-select form-select-sm" id="attCalcYear">
                                            @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-purple btn-sm" id="attCalcBtn">Calculate</button>
                                    </div>
                                </div>
                                <div class="table-responsive" id="attCalcTableWrap" style="display:none;">
                                    <table class="table table-bordered table-sm" style="font-size:0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Staff Name</th>
                                                <th>Group</th>
                                                <th class="text-center">Total Hours</th>
                                                <th class="text-center">Working Days</th>
                                            </tr>
                                        </thead>
                                        <tbody id="attCalcBody"></tbody>
                                        <tfoot class="table-light fw-bold" id="attCalcFoot" style="display:none;">
                                            <tr>
                                                <td colspan="3" class="text-end">Grand Total:</td>
                                                <td class="text-center" id="attCalcTotalHours"></td>
                                                <td class="text-center" id="attCalcTotalDays"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div id="attCalcEmpty" class="text-muted text-center py-3">
                                    Select month and year, then click Calculate.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
(function(){
"use strict";

jQuery(function(){

    jQuery('#attCalcBtn').on('click', function() {
        var $btn = jQuery(this);
        var month = jQuery('#attCalcMonth').val();
        var year = jQuery('#attCalcYear').val();

        $btn.prop('disabled', true).text('Calculating...');

        jQuery.ajax({
            url: '{{ url("ajax") }}',
            type: 'POST',
            data: {
                fetch_attendance_calc: 1,
                month: month,
                year: year,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $btn.prop('disabled', false).text('Calculate');

                if (response.status && response.data) {
                    var rows = response.data;
                    var tbody = jQuery('#attCalcBody');
                    tbody.empty();

                    if (rows.length === 0) {
                        jQuery('#attCalcTableWrap').hide();
                        jQuery('#attCalcFoot').hide();
                        jQuery('#attCalcEmpty').text('No attendance data found for this period.').show();
                        return;
                    }

                    var grandHours = 0;
                    var grandDays = 0;

                    jQuery.each(rows, function(i, row) {
                        grandHours += parseFloat(row.total_hours);
                        grandDays += parseFloat(row.total_days);
                        tbody.append(
                            '<tr>' +
                                '<td>' + (i + 1) + '</td>' +
                                '<td>' + row.staff_name + '</td>' +
                                '<td>' + row.group_name + '</td>' +
                                '<td class="text-center">' + row.total_hours + '</td>' +
                                '<td class="text-center">' + row.total_days + '</td>' +
                            '</tr>'
                        );
                    });

                    jQuery('#attCalcTotalHours').text(grandHours.toFixed(2));
                    jQuery('#attCalcTotalDays').text(grandDays.toFixed(2));
                    jQuery('#attCalcFoot').show();
                    jQuery('#attCalcEmpty').hide();
                    jQuery('#attCalcTableWrap').show();
                } else {
                    Swal.fire('Error', response.message || 'Failed to calculate attendance', 'error');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Calculate');
                Swal.fire('Error', 'An error occurred. Please try again.', 'error');
            }
        });
    });

});
})();
</script>
@endsection

@include('common.footer')
