@include('common.header', ['title' => 'Leave Management'])

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
    @include('common.innerheader', ['title' => 'Leave Management'])
    <!-- Page Header Ends -->

    <div class="page-body-wrapper">

        <!-- Page Sidebar Start-->
        @include('common.sidebar')
        <!-- Page Sidebar Ends-->

        <div class="page-body">
            <div class="container-fluid mt-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Leave Management</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="leaveTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pendingPane" type="button" role="tab" data-status="pending">Pending</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="granted-tab" data-bs-toggle="tab" data-bs-target="#grantedPane" type="button" role="tab" data-status="granted">Granted</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejectedPane" type="button" role="tab" data-status="rejected">Rejected</button>
                            </li>
                        </ul>

                        <div class="tab-content mt-2">
                            <!-- Pending Tab -->
                            <div class="tab-pane fade show active" id="pendingPane" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:40px;"></th>
                                                <th>Name</th>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Reason</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pendingBody">
                                            <tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Granted Tab -->
                            <div class="tab-pane fade" id="grantedPane" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Reason</th>
                                                <th>Granted On</th>
                                            </tr>
                                        </thead>
                                        <tbody id="grantedBody">
                                            <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Rejected Tab -->
                            <div class="tab-pane fade" id="rejectedPane" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Reason</th>
                                                <th>Rejected On</th>
                                            </tr>
                                        </thead>
                                        <tbody id="rejectedBody">
                                            <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
                                        </tbody>
                                    </table>
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

var CSRF = '{{ csrf_token() }}';
var paidLeaveCounts = {};
var fyLabel = '';

function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    var day = String(d.getDate()).padStart(2, '0');
    var mon = String(d.getMonth() + 1).padStart(2, '0');
    return day + '-' + mon + '-' + d.getFullYear();
}

function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    var day = String(d.getDate()).padStart(2, '0');
    var mon = String(d.getMonth() + 1).padStart(2, '0');
    var hrs = String(d.getHours()).padStart(2, '0');
    var min = String(d.getMinutes()).padStart(2, '0');
    return day + '-' + mon + '-' + d.getFullYear() + ' ' + hrs + ':' + min;
}

function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function getActiveStatus() {
    var activeTab = jQuery('#leaveTabs .nav-link.active');
    return activeTab.data('status') || 'pending';
}

function fetchLeaves(status) {
    var bodyId = '#' + status + 'Body';
    var colSpan = status === 'pending' ? 6 : 5;
    jQuery(bodyId).html('<tr><td colspan="' + colSpan + '" class="text-center text-muted">Loading...</td></tr>');

    jQuery.ajax({
        url: '/ajax',
        type: 'POST',
        data: { _token: CSRF, fetch_leaves: 1, status: status },
        success: function(res) {
            if (!res.status || !res.leaves.length) {
                jQuery(bodyId).html('<tr><td colspan="' + colSpan + '" class="text-center text-muted">No ' + status + ' leaves</td></tr>');
                return;
            }

            // Store paid leave counts and FY label
            if (res.paid_leave_counts) paidLeaveCounts = res.paid_leave_counts;
            if (res.fy_label) fyLabel = res.fy_label;

            var html = '';
            res.leaves.forEach(function(leave) {
                var staffName = leave.staff ? leave.staff.full_name : 'Unknown';
                var staffId = leave.staff ? leave.staff.id : 0;
                var usedCount = paidLeaveCounts[staffId] || 0;
                var limitReached = usedCount >= 2;

                html += '<tr>';

                if (status === 'pending') {
                    html += '<td><input type="checkbox" class="form-check-input leave-check" value="' + leave.id + '"></td>';
                }

                html += '<td>' + staffName;
                if (status === 'pending') {
                    var badgeClass = limitReached ? 'bg-danger' : 'bg-secondary';
                    html += ' <span class="badge ' + badgeClass + '" style="font-size:0.7rem;" title="Paid leaves used in FY ' + fyLabel + '">' + usedCount + '/2 PL</span>';
                }
                html += '</td>';
                html += '<td>' + formatDate(leave.leave_date) + '</td>';
                html += '<td><span class="badge bg-info">' + ucfirst(leave.leave_type) + '</span></td>';
                html += '<td>' + (leave.reason || '-') + '</td>';

                if (status === 'pending') {
                    html += '<td>';
                    if (limitReached) {
                        html += '<button class="btn btn-secondary btn-sm me-1" disabled title="Paid leave limit (2/2) reached for FY ' + fyLabel + '">Paid</button>';
                    } else {
                        html += '<button class="btn btn-success btn-sm approve-leave-btn me-1" data-id="' + leave.id + '" data-mark="paid">Paid</button>';
                    }
                    html += '<button class="btn btn-outline-secondary btn-sm approve-leave-btn me-1" data-id="' + leave.id + '" data-mark="unpaid">Unpaid</button>';
                    html += '<button class="btn btn-danger btn-sm reject-leave-btn" data-id="' + leave.id + '">Reject</button>';
                    html += '</td>';
                } else if (status === 'granted') {
                    html += '<td>' + formatDateTime(leave.updated_at) + '</td>';
                } else {
                    html += '<td>' + formatDateTime(leave.updated_at) + '</td>';
                }

                html += '</tr>';
            });

            jQuery(bodyId).html(html);
        },
        error: function() {
            jQuery(bodyId).html('<tr><td colspan="' + colSpan + '" class="text-center text-danger">Failed to load leaves</td></tr>');
        }
    });
}

jQuery(function(){

    // Load pending on page load
    fetchLeaves('pending');

    // Tab switch
    jQuery('#leaveTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
        var status = jQuery(this).data('status');
        fetchLeaves(status);
    });

    // Approve leave (Paid / Unpaid)
    jQuery(document).on('click', '.approve-leave-btn', function() {
        var btn = jQuery(this);
        var leaveId = btn.data('id');
        var markAs = btn.data('mark'); // 'paid' or 'unpaid'
        var label = markAs === 'paid' ? 'Paid' : 'Unpaid';

        Swal.fire({
            title: 'Approve as ' + label + '?',
            text: markAs === 'paid' ? 'This leave will be marked as paid (counts towards 2-day FY limit).' : 'This leave will be marked as unpaid (no wage for this day).',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0093FF',
            confirmButtonText: 'Yes, approve as ' + label
        }).then(function(result) {
            if (!result.isConfirmed) return;

            btn.prop('disabled', true);
            jQuery.ajax({
                url: '/ajax',
                type: 'POST',
                data: { _token: CSRF, approve_leave: 1, leave_id: leaveId, mark_as: markAs },
                success: function(res) {
                    btn.prop('disabled', false);
                    if (res.status) {
                        Swal.fire('Approved', res.message, 'success');
                        fetchLeaves('pending');
                        fetchLeaves('granted');
                    } else {
                        Swal.fire('Error', res.message || 'Failed to approve', 'error');
                    }
                },
                error: function() {
                    btn.prop('disabled', false);
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            });
        });
    });

    // Reject leave
    jQuery(document).on('click', '.reject-leave-btn', function() {
        var btn = jQuery(this);
        var leaveId = btn.data('id');

        Swal.fire({
            title: 'Reject this leave?',
            text: 'This will reject the leave request.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, reject it'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            btn.prop('disabled', true);
            jQuery.ajax({
                url: '/ajax',
                type: 'POST',
                data: { _token: CSRF, reject_leave: 1, leave_id: leaveId },
                success: function(res) {
                    btn.prop('disabled', false);
                    if (res.status) {
                        Swal.fire('Rejected', res.message, 'success');
                        fetchLeaves('pending');
                        fetchLeaves('rejected');
                    } else {
                        Swal.fire('Error', res.message || 'Failed to reject', 'error');
                    }
                },
                error: function() {
                    btn.prop('disabled', false);
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            });
        });
    });

}); // end document ready
})();
</script>

@endsection

@include('common.footer')
