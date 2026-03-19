@include('common.header', ['title' => 'Staff Trash'])

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

<!-- page-wrapper Start-->
<div class="page-wrapper compact-wrapper" id="pageWrapper">

    <!-- Page Header Start-->
    @include('common.innerheader', ['title' => 'Staff Trash'])
    <!-- Page Header Ends -->

    <div class="page-body-wrapper">

        <!-- Page Sidebar Start-->
        @include('common.sidebar')
        <!-- Page Sidebar Ends-->

        <div class="page-body">
            <div class="container-fluid mt-4">

                <!-- Action buttons -->
                <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
                    <a href="{{ url('staffs') }}" class="btn btn-primary">Back to Staff List</a>
                    <select class="form-select" id="groupFilter" style="width:auto;height:auto;padding:0.625rem 2.25rem;" onchange="window.location.href = this.value ? '{{ url('staffs/trash') }}?group='+this.value : '{{ url('staffs/trash') }}'">
                        <option value="">All Groups</option>
                        @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ (isset($selectedGroup) && $selectedGroup == $g->id) ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                    <span class="text-muted">{{ $staffCount }} deleted staff</span>
                </div>

                <!-- Trash Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive custom-scrollbar">
                                    <table class="table mb-0" id="trashTable">
                                        <thead>
                                            <tr>
                                                <th style="width:60px;">Image</th>
                                                <th>Name</th>
                                                <th>Phone</th>
                                                <th>Group</th>
                                                <th style="width:140px;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($staffList as $s)
                                                <tr>
                                                    <td>
                                                        <img src="{{ $s->profile_photo ? asset($s->profile_photo) : asset('assets/images/test/user_profile_default.png') }}" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                                    </td>
                                                    <td>{{ $s->full_name }}</td>
                                                    <td>{{ $s->phone_number }}</td>
                                                    <td>{{ $s->group ? $s->group->name : '—' }}</td>
                                                    <td class="text-center">
                                                        <div class="common-align gap-2 justify-content-center">
                                                            <button type="button" class="btn btn-sm btn-success restore-btn" data-id="{{ $s->id }}" data-name="{{ $s->full_name }}" title="Restore">
                                                                <i class="fa fa-undo"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger perm-delete-btn" data-id="{{ $s->id }}" data-name="{{ $s->full_name }}" title="Permanently Delete">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center py-4">Trash is empty.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    #trashTable_wrapper .dataTables_info,
                    #trashTable_wrapper .dataTables_paginate {
                        padding: 12px 15px;
                    }
                    .card .table th,
                    .card .table td {
                        padding: 12px 15px;
                        vertical-align: middle;
                    }
                </style>

            </div>
        </div>{{-- /page-body --}}
    </div>
</div>

<!-- Permanent Delete Confirmation Modal -->
<div class="modal fade" id="permDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Permanently Delete Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="permDeleteMsg" class="mb-1"></p>
                <p class="text-danger small mb-3">This will delete the staff and ALL related records (attendance, scans, leaves, payroll). This cannot be undone.</p>
                <div class="mb-2">
                    <label class="form-label mb-1">Enter your password to confirm</label>
                    <input type="password" class="form-control" id="permDeletePassword" placeholder="Password">
                </div>
                <div id="permDeleteError" class="text-danger small" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="permDeleteSubmit">Delete Forever</button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
(function(){
"use strict";

var CSRF = '{{ csrf_token() }}';
var AJAX_URL = '{{ url("ajax") }}';

jQuery(function(){

    @if(count($staffList) > 0)
        jQuery('#trashTable').DataTable({
            pageLength: 30,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [0, 4] }
            ]
        });
    @endif

    // --- Restore Staff ---
    jQuery(document).on('click', '.restore-btn', function(){
        var $btn = jQuery(this);
        var staffId = $btn.data('id');
        var staffName = $btn.data('name');
        $btn.prop('disabled', true);
        jQuery.post(AJAX_URL, { restore_staff:1, staff_id:staffId, _token:CSRF }, function(res){
            if(res.status){
                $btn.closest('tr').fadeOut(300, function(){ jQuery(this).remove(); });
            } else {
                alert(res.message || 'Restore failed');
                $btn.prop('disabled', false);
            }
        }).fail(function(){
            alert('Request failed');
            $btn.prop('disabled', false);
        });
    });

    // --- Permanent Delete ---
    var pendingPermDelete = {};

    jQuery(document).on('click', '.perm-delete-btn', function(){
        var $btn = jQuery(this);
        pendingPermDelete = {
            id: $btn.data('id'),
            name: $btn.data('name'),
            $btn: $btn
        };
        jQuery('#permDeleteMsg').text('Permanently delete "' + $btn.data('name') + '"?');
        jQuery('#permDeletePassword').val('');
        jQuery('#permDeleteError').hide().text('');
        jQuery('#permDeleteSubmit').prop('disabled', false);
        new bootstrap.Modal(document.getElementById('permDeleteModal')).show();
    });

    jQuery('#permDeleteSubmit').on('click', function(){
        var pwd = jQuery('#permDeletePassword').val();
        if(!pwd){
            jQuery('#permDeleteError').text('Please enter your password.').show();
            return;
        }
        var $submitBtn = jQuery(this);
        $submitBtn.prop('disabled', true);
        jQuery('#permDeleteError').hide();

        jQuery.post(AJAX_URL, {
            permanent_delete_staff: 1,
            staff_id: pendingPermDelete.id,
            password: pwd,
            _token: CSRF
        }, function(res){
            if(res.status){
                bootstrap.Modal.getInstance(document.getElementById('permDeleteModal')).hide();
                pendingPermDelete.$btn.closest('tr').fadeOut(300, function(){ jQuery(this).remove(); });
            } else {
                jQuery('#permDeleteError').text(res.message || 'Delete failed').show();
                $submitBtn.prop('disabled', false);
            }
        }).fail(function(){
            jQuery('#permDeleteError').text('Request failed. Please try again.').show();
            $submitBtn.prop('disabled', false);
        });
    });

    jQuery('#permDeleteModal').on('hidden.bs.modal', function(){
        pendingPermDelete = {};
    });

});
})();
</script>
@endsection

@include('common.footer')
