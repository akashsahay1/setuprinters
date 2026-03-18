@include('common.header', ['title' => 'Edit Attendance'])

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
    @include('common.innerheader', ['title' => 'Edit Attendance'])
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
                                <h5 class="mb-0">Edit Attendance</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">Group</label>
                                        <select class="form-select form-select-sm" id="eaGroup">
                                            <option value="">All Groups</option>
                                            @foreach($groups as $g)
                                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">Staff</label>
                                        <select class="form-select form-select-sm" id="eaStaff">
                                            <option value="">-- Select Staff --</option>
                                            @foreach($staffList as $s)
                                                <option value="{{ $s->id }}" data-group="{{ $s->group_id }}">{{ $s->full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label mb-1">Month</label>
                                        <select class="form-select form-select-sm" id="eaMonth">
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label mb-1">Year</label>
                                        <select class="form-select form-select-sm" id="eaYear">
                                            @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-purple btn-sm" id="eaLoadBtn">Load</button>
                                    </div>
                                </div>

                                <div id="eaLockedAlert" class="alert alert-warning d-flex align-items-center gap-2" style="display:none !important;">
                                    <i data-feather="lock" style="width:18px;height:18px;"></i>
                                    <span>This month has ended. Attendance is locked and cannot be edited.</span>
                                </div>

                                <div class="table-responsive" id="eaTableWrap" style="display:none;">
                                    <table class="table table-bordered table-sm" style="font-size:0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Total Hours</th>
                                                <th class="text-center">OT Hours</th>
                                                <th class="text-center">Base Wage</th>
                                                <th class="text-center">OT Wage</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="eaBody"></tbody>
                                    </table>
                                </div>
                                <div id="eaEmpty" class="text-muted text-center py-3">
                                    Select group, staff, month and year, then click Load.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Attendance Modal -->
<div class="modal fade" id="eaEditModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Attendance - <span id="eaEditDate"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="eaEditId">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label mb-1">Total Hours</label>
                        <input type="number" class="form-control form-control-sm" id="eaEditTotalHours" step="0.01" min="0">
                        <small class="text-muted">Status, OT hours and wages will be auto-calculated.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-purple btn-sm" id="eaEditSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
(function(){
"use strict";

var payrollLocked = false;

jQuery(function(){

    // Filter staff dropdown by group
    jQuery('#eaGroup').on('change', function(){
        var groupId = jQuery(this).val();
        var staffSelect = jQuery('#eaStaff');
        staffSelect.val('');
        staffSelect.find('option').each(function(){
            var opt = jQuery(this);
            if (!opt.val()) return;
            if (!groupId || opt.data('group') == groupId) {
                opt.show();
            } else {
                opt.hide();
            }
        });
    });

    // Load attendance data
    jQuery('#eaLoadBtn').on('click', function(){
        var staffId = jQuery('#eaStaff').val();
        var month = jQuery('#eaMonth').val();
        var year = jQuery('#eaYear').val();

        if (!staffId) {
            Swal.fire('Error', 'Please select a staff member', 'error');
            return;
        }

        var $btn = jQuery(this);
        $btn.prop('disabled', true).text('Loading...');

        jQuery.ajax({
            url: '{{ url("ajax") }}',
            type: 'POST',
            data: {
                fetch_edit_attendance: 1,
                staff_id: staffId,
                month: month,
                year: year,
                _token: '{{ csrf_token() }}'
            },
            success: function(response){
                $btn.prop('disabled', false).text('Load');

                if (response.status && response.data) {
                    var rows = response.data;
                    payrollLocked = response.payroll_locked || false;

                    if (payrollLocked) {
                        jQuery('#eaLockedAlert').css('display', '').show();
                    } else {
                        jQuery('#eaLockedAlert').hide();
                    }

                    var tbody = jQuery('#eaBody');
                    tbody.empty();

                    if (rows.length === 0) {
                        jQuery('#eaTableWrap').hide();
                        jQuery('#eaEmpty').text('No attendance data found for this period.').show();
                        return;
                    }

                    jQuery.each(rows, function(i, row){
                        var statusBadge = getStatusBadge(row.status);
                        var editBtn = '';
                        if (!payrollLocked) {
                            editBtn = '<button type="button" class="btn btn-outline-primary btn-sm ea-edit-btn" ' +
                                'data-id="' + row.id + '" ' +
                                'data-date="' + row.date_label + '" ' +
                                'data-totalhours="' + row.total_hours + '">' +
                                '<svg style="width:14px;height:14px;"><use href="{{ asset("assets/svg/icon-sprite.svg#edit-content") }}"></use></svg>' +
                                '</button>';
                        }

                        tbody.append(
                            '<tr data-row-id="' + row.id + '">' +
                                '<td>' + (i + 1) + '</td>' +
                                '<td>' + row.date_label + '</td>' +
                                '<td class="text-center">' + statusBadge + '</td>' +
                                '<td class="text-center">' + row.total_hours.toFixed(2) + '</td>' +
                                '<td class="text-center">' + row.ot_hours.toFixed(2) + '</td>' +
                                '<td class="text-center">' + row.base_wage.toFixed(2) + '</td>' +
                                '<td class="text-center">' + row.ot_wage.toFixed(2) + '</td>' +
                                '<td class="text-center">' + editBtn + '</td>' +
                            '</tr>'
                        );
                    });

                    jQuery('#eaEmpty').hide();
                    jQuery('#eaTableWrap').show();
                } else {
                    Swal.fire('Error', response.message || 'Failed to load attendance', 'error');
                }
            },
            error: function(){
                $btn.prop('disabled', false).text('Load');
                Swal.fire('Error', 'An error occurred. Please try again.', 'error');
            }
        });
    });

    // Open edit modal
    jQuery(document).on('click', '.ea-edit-btn', function(){
        var btn = jQuery(this);
        jQuery('#eaEditId').val(btn.data('id'));
        jQuery('#eaEditDate').text(btn.data('date'));
        jQuery('#eaEditTotalHours').val(btn.data('totalhours'));
        new bootstrap.Modal(document.getElementById('eaEditModal')).show();
    });

    // Save edit
    jQuery('#eaEditSaveBtn').on('click', function(){
        var $btn = jQuery(this);
        var id = jQuery('#eaEditId').val();

        $btn.prop('disabled', true).text('Saving...');

        jQuery.ajax({
            url: '{{ url("ajax") }}',
            type: 'POST',
            data: {
                update_daily_attendance: 1,
                attendance_id: id,
                total_hours: jQuery('#eaEditTotalHours').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response){
                $btn.prop('disabled', false).text('Save');

                if (response.status && response.data) {
                    var row = response.data;
                    var tr = jQuery('tr[data-row-id="' + row.id + '"]');
                    if (tr.length) {
                        var idx = tr.find('td:first').text();
                        var statusBadge = getStatusBadge(row.status);
                        var editBtn = '<button type="button" class="btn btn-outline-primary btn-sm ea-edit-btn" ' +
                            'data-id="' + row.id + '" ' +
                            'data-date="' + row.date_label + '" ' +
                            'data-status="' + row.status + '" ' +
                            'data-totalhours="' + row.total_hours + '" ' +
                            'data-othours="' + row.ot_hours + '">' +
                            '<svg style="width:14px;height:14px;"><use href="{{ asset("assets/svg/icon-sprite.svg#edit-content") }}"></use></svg>' +
                            '</button>';

                        tr.html(
                            '<td>' + idx + '</td>' +
                            '<td>' + row.date_label + '</td>' +
                            '<td class="text-center">' + statusBadge + '</td>' +
                            '<td class="text-center">' + row.total_hours.toFixed(2) + '</td>' +
                            '<td class="text-center">' + row.ot_hours.toFixed(2) + '</td>' +
                            '<td class="text-center">' + row.base_wage.toFixed(2) + '</td>' +
                            '<td class="text-center">' + row.ot_wage.toFixed(2) + '</td>' +
                            '<td class="text-center">' + editBtn + '</td>'
                        );
                    }

                    jQuery('#eaEditModal').modal('hide');
                    Swal.fire({icon: 'success', title: 'Updated', text: 'Attendance updated successfully', timer: 1500, showConfirmButton: false});
                } else {
                    Swal.fire('Error', response.message || 'Failed to update', 'error');
                }
            },
            error: function(){
                $btn.prop('disabled', false).text('Save');
                Swal.fire('Error', 'An error occurred. Please try again.', 'error');
            }
        });
    });

    function getStatusBadge(status) {
        var colors = {
            'present': 'success',
            'absent': 'danger',
            'half_day': 'info',
            'leave': 'warning',
            'holiday': 'secondary'
        };
        var bg = colors[status] || 'light';
        var label = status.replace('_', ' ');
        label = label.charAt(0).toUpperCase() + label.slice(1);
        return '<span class="badge bg-' + bg + '">' + label + '</span>';
    }


});
})();
</script>
@endsection

@include('common.footer')
