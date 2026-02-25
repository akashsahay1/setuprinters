@include('common.header', ['title' => 'Staffs'])

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
    @include('common.innerheader', ['title' => 'Staffs'])
    <!-- Page Header Ends -->

    <div class="page-body-wrapper">

        <!-- Page Sidebar Start-->
        @include('common.sidebar')
        <!-- Page Sidebar Ends-->

        <div class="page-body">
            <div class="container-fluid mt-4">

                <!-- Action buttons -->
                <div class="d-flex gap-2 mb-3">
                    <a href="{{ url('staffs/create') }}" class="btn btn-primary">Add Staff</a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageGroupsModal">Manage Groups</button>
                    <button class="btn btn-success" id="staffCsvBtn">CSV</button>
                </div>

                <!-- Migration banner -->
                <div id="migrateBanner" class="alert alert-warning d-none mb-3">
                    <strong>Migrate Browser Data:</strong> We found payroll data stored in your browser. Click below to transfer it to the database.
                    <button class="btn btn-warning ms-2" id="migrateBtn">Migrate Now</button>
                    <button class="btn btn-outline-secondary ms-1" id="migrateDismiss">Dismiss</button>
                    <span id="migrateMsg" class="ms-2"></span>
                </div>

                <!-- Staff Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body pt-0 px-0">
                                <div class="table-responsive custom-scrollbar">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:60px;">Image</th>
                                                <th>Name</th>
                                                <th>Phone</th>
                                                <th>Group</th>
                                                <th style="width:100px;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($staffList as $index => $s)
                                                <tr>
                                                    <td>
                                                        <img src="{{ $s->profile_photo ? asset($s->profile_photo) : asset('assets/images/test/user_profile_default.png') }}" alt="" class="staff-photo" data-name="{{ $s->full_name }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;cursor:pointer;">
                                                    </td>
                                                    <td>{{ $s->full_name }} {{ $s->id }}</td>
                                                    <td>{{ $s->phone_number }}</td>
                                                    <td>{{ $s->group ? $s->group->name : '—' }}</td>
                                                    <td class="text-center">
                                                        <div class="common-align gap-2 justify-content-center">
                                                            <a class="square-white" href="{{ url('staffs/'.$s->id.'/edit') }}" title="Edit">
                                                                <svg><use href="{{ asset('assets/svg/icon-sprite.svg#edit-content') }}"></use></svg>
                                                            </a>
                                                            <button type="button" class="square-white trash-7 border-0 bg-transparent p-0 staff-delete-btn" data-id="{{ $s->id }}" data-name="{{ $s->full_name }}" title="Delete">
                                                                <svg><use href="{{ asset('assets/svg/icon-sprite.svg#trash1') }}"></use></svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center py-4">No staff found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                @if($staffList->hasPages())
                <div style="padding-top:15px;padding-bottom:30px;">
                    <div class="d-flex justify-content-between align-items-center px-3">
                        <small class="text-muted">Showing {{ $staffList->firstItem() }}–{{ $staffList->lastItem() }} of {{ $staffList->total() }}</small>
                        <div class="staff-pagination">{{ $staffList->links() }}</div>
                    </div>
                </div>
                <style>
                    .card .table th,
                    .card .table td {
                        padding: 12px 15px;
                        vertical-align: middle;
                    }
                    .card .table .square-white {
                        width: 32px;
                        height: 32px;
                        border-radius: 2px;
                        background-color: #fff;
                        display: inline-flex;
                        justify-content: center;
                        align-items: center;
                        box-shadow: 0px 0px 28px 6px rgba(235, 235, 235, 0.4);
                    }
                    .card .table .square-white svg {
                        width: 16px;
                        height: 16px;
                        fill: rgba(82, 82, 108, 0.8);
                    }
                    #groupsList .square-white {
                        width: 32px;
                        height: 32px;
                        border-radius: 2px;
                        background-color: #fff;
                        display: inline-flex;
                        justify-content: center;
                        align-items: center;
                        box-shadow: 0px 0px 28px 6px rgba(235, 235, 235, 0.4);
                    }
                    #groupsList .square-white svg {
                        width: 16px;
                        height: 16px;
                        fill: rgba(82, 82, 108, 0.8);
                    }
                    .staff-pagination p { display: none !important; }
                    .staff-pagination .page-link {
                        color: var(--theme-primary);
                        border: none;
                    }
                    .staff-pagination .page-link:hover {
                        color: #fff;
                        background-color: var(--theme-primary);
                        border: none;
                    }
                    .staff-pagination .page-item.active .page-link {
                        background-color: var(--theme-primary);
                        border: none;
                        color: #fff;
                    }
                    .staff-pagination .page-item.disabled .page-link {
                        color: var(--theme-primary);
                        opacity: 0.5;
                        border: none;
                    }
                </style>
                @endif

            </div>
        </div>{{-- /page-body --}}
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="imagePreviewName"></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img src="" id="imagePreviewImg" alt="" style="max-width:100%;max-height:70vh;border-radius:8px;">
            </div>
        </div>
    </div>
</div>

<!-- Manage Groups Modal -->
<div class="modal fade" id="manageGroupsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Staff Groups</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Add new group -->
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-sm" id="newGroupName" placeholder="New group name...">
                    <button class="btn btn-primary" id="addGroupBtn">Add</button>
                </div>
                <span id="groupMsg" class="d-block mb-2"></span>

                <!-- Existing groups list -->
                <ul class="list-group" id="groupsList">
                    @foreach($groups as $g)
                    <li class="list-group-item d-flex justify-content-between align-items-center" data-gid="{{ $g->id }}">
                        <span class="group-name-display">{{ $g->name }}</span>
                        <input type="text" class="form-control form-control-sm group-name-edit d-none" style="max-width:200px;" value="{{ $g->name }}">
                        <div class="common-align gap-2">
                            <button type="button" class="square-white border-0 bg-transparent p-0 group-edit-btn" title="Edit">
                                <svg><use href="{{ asset('assets/svg/icon-sprite.svg#edit-content') }}"></use></svg>
                            </button>
                            <button type="button" class="square-white border-0 bg-transparent p-0 group-save-btn d-none" title="Save">
                                <svg><use href="{{ asset('assets/svg/icon-sprite.svg#checked1') }}"></use></svg>
                            </button>
                            <button type="button" class="square-white trash-7 border-0 bg-transparent p-0 group-del-btn" title="Delete">
                                <svg><use href="{{ asset('assets/svg/icon-sprite.svg#trash1') }}"></use></svg>
                            </button>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmMsg" class="mb-3"></p>
                <div class="mb-2">
                    <label class="form-label mb-1">Enter your password to confirm</label>
                    <input type="password" class="form-control" id="deleteConfirmPassword" placeholder="Password">
                </div>
                <div id="deleteConfirmError" class="text-danger small" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteConfirmSubmit">Delete</button>
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

    // --- Image Preview ---
    jQuery('table.table').on('click', '.staff-photo', function(){
        var src = jQuery(this).attr('src');
        var name = jQuery(this).data('name') || '';
        jQuery('#imagePreviewImg').attr('src', src);
        jQuery('#imagePreviewName').text(name);
        new bootstrap.Modal(jQuery('#imagePreviewModal')[0]).show();
    });

    // --- Manage Groups ---
    jQuery('#addGroupBtn').on('click', function(){
        var name = jQuery('#newGroupName').val().trim();
        if(!name) return;
        jQuery.post(AJAX_URL, { save_staff_group:1, group_name:name, _token:CSRF }, function(res){
            if(res.status){
                var g = res.group;
                var li = '<li class="list-group-item d-flex justify-content-between align-items-center" data-gid="'+g.id+'">'
                    + '<span class="group-name-display">'+jQuery('<span>').text(g.name).html()+'</span>'
                    + '<input type="text" class="form-control form-control-sm group-name-edit d-none" style="max-width:200px;" value="'+jQuery('<span>').text(g.name).html()+'">'
                    + '<div class="common-align gap-2">'
                    + '<button type="button" class="square-white border-0 bg-transparent p-0 group-edit-btn" title="Edit"><svg><use href="{{ asset("assets/svg/icon-sprite.svg#edit-content") }}"></use></svg></button>'
                    + '<button type="button" class="square-white border-0 bg-transparent p-0 group-save-btn d-none" title="Save"><svg><use href="{{ asset("assets/svg/icon-sprite.svg#checked1") }}"></use></svg></button>'
                    + '<button type="button" class="square-white trash-7 border-0 bg-transparent p-0 group-del-btn" title="Delete"><svg><use href="{{ asset("assets/svg/icon-sprite.svg#trash1") }}"></use></svg></button>'
                    + '</div></li>';
                jQuery('#groupsList').append(li);
                jQuery('#newGroupName').val('');
                showGroupMsg('Group added!', 'success');
            } else {
                showGroupMsg(res.message||'Failed', 'danger');
            }
        });
    });

    jQuery(document).on('click', '.group-edit-btn', function(){
        var $li = jQuery(this).closest('li');
        $li.find('.group-name-display').addClass('d-none');
        $li.find('.group-name-edit').removeClass('d-none').focus();
        jQuery(this).addClass('d-none');
        $li.find('.group-save-btn').removeClass('d-none');
    });

    jQuery(document).on('click', '.group-save-btn', function(){
        var $li = jQuery(this).closest('li');
        var gid = $li.data('gid');
        var newName = $li.find('.group-name-edit').val().trim();
        if(!newName) return;
        jQuery.post(AJAX_URL, { update_staff_group:1, group_id:gid, group_name:newName, _token:CSRF }, function(res){
            if(res.status){
                $li.find('.group-name-display').text(newName).removeClass('d-none');
                $li.find('.group-name-edit').addClass('d-none');
                $li.find('.group-edit-btn').removeClass('d-none');
                $li.find('.group-save-btn').addClass('d-none');
                showGroupMsg('Group updated!', 'success');
            } else {
                showGroupMsg(res.message||'Failed', 'danger');
            }
        });
    });

    jQuery(document).on('click', '.group-del-btn', function(){
        var $li = jQuery(this).closest('li');
        var gid = $li.data('gid');
        var gname = $li.find('.group-name-display').text();
        pendingDelete = {
            type: 'group',
            id: gid,
            name: gname,
            $el: $li
        };
        jQuery('#deleteConfirmMsg').text('Delete group "'+gname+'"?');
        jQuery('#deleteConfirmPassword').val('');
        jQuery('#deleteConfirmError').hide().text('');
        jQuery('#deleteConfirmSubmit').prop('disabled', false);
        var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    });

    function showGroupMsg(msg, type){
        jQuery('#groupMsg').html('<small class="text-'+type+'">'+msg+'</small>');
        setTimeout(function(){ jQuery('#groupMsg').html(''); }, 3000);
    }

    // --- CSV Export ---
    jQuery('#staffCsvBtn').on('click', function(){
        var rows = [];
        jQuery('table.table tbody tr').each(function(){
            var cols = [];
            jQuery(this).find('td').each(function(i){
                if(i === 0 || i === 6) return; // skip thumbnail & actions
                cols.push('"' + jQuery(this).text().trim().replace(/"/g, '""') + '"');
            });
            if(cols.length) rows.push(cols.join(','));
        });
        if(!rows.length) return;
        var csv = 'Name,Phone,QR Code,Group,Salary\n' + rows.join('\n');
        var blob = new Blob([csv], {type:'text/csv'});
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'staffs_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
    });

    // --- Delete Staff / Group (with password confirmation) ---
    var pendingDelete = {};

    jQuery(document).on('click', '.staff-delete-btn', function(){
        var $btn = jQuery(this);
        pendingDelete = {
            type: 'staff',
            id: $btn.data('id'),
            name: $btn.data('name'),
            $btn: $btn
        };
        jQuery('#deleteConfirmMsg').text('Delete staff "'+$btn.data('name')+'"? This action cannot be undone.');
        jQuery('#deleteConfirmPassword').val('');
        jQuery('#deleteConfirmError').hide().text('');
        jQuery('#deleteConfirmSubmit').prop('disabled', false);
        var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    });

    jQuery('#deleteConfirmSubmit').on('click', function(){
        var pwd = jQuery('#deleteConfirmPassword').val();
        if(!pwd){
            jQuery('#deleteConfirmError').text('Please enter your password.').show();
            return;
        }
        var $submitBtn = jQuery(this);
        $submitBtn.prop('disabled', true);
        jQuery('#deleteConfirmError').hide();

        var postData = { _token: CSRF, password: pwd };
        if(pendingDelete.type === 'staff'){
            postData.delete_staff = 1;
            postData.staff_id = pendingDelete.id;
        } else if(pendingDelete.type === 'group'){
            postData.delete_staff_group = 1;
            postData.group_id = pendingDelete.id;
        }

        jQuery.post(AJAX_URL, postData, function(res){
            if(res.status){
                bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
                if(pendingDelete.type === 'staff' && pendingDelete.$btn){
                    pendingDelete.$btn.closest('tr').fadeOut(300, function(){ jQuery(this).remove(); });
                } else if(pendingDelete.type === 'group' && pendingDelete.$el){
                    pendingDelete.$el.fadeOut(300, function(){ jQuery(this).remove(); });
                    showGroupMsg('Group deleted!', 'success');
                }
            } else {
                jQuery('#deleteConfirmError').text(res.message||'Delete failed').show();
                $submitBtn.prop('disabled', false);
            }
        }).fail(function(){
            jQuery('#deleteConfirmError').text('Request failed. Please try again.').show();
            $submitBtn.prop('disabled', false);
        });
    });

    jQuery('#deleteConfirmModal').on('hidden.bs.modal', function(){
        pendingDelete = {};
    });

    // --- LocalStorage Migration Banner ---
    (function checkMigration(){
        var hasData = false;
        for(var i=0; i<localStorage.length; i++){
            if(localStorage.key(i).indexOf('hrms_payroll_')===0){
                hasData = true;
                break;
            }
        }
        if(hasData && !localStorage.getItem('hrms_migration_dismissed')){
            jQuery('#migrateBanner').removeClass('d-none');
        }
    })();

    jQuery('#migrateDismiss').on('click', function(){
        localStorage.setItem('hrms_migration_dismissed', '1');
        jQuery('#migrateBanner').addClass('d-none');
    });

    jQuery('#migrateBtn').on('click', function(){
        var $btn = jQuery(this);
        $btn.prop('disabled', true).text('Migrating...');
        var items = [];
        for(var i=0; i<localStorage.length; i++){
            var key = localStorage.key(i);
            if(key.indexOf('hrms_payroll_')===0){
                var sid = key.replace('hrms_payroll_', '');
                try {
                    var data = JSON.parse(localStorage.getItem(key));
                    data.staff_id = sid;
                    items.push(data);
                } catch(e){}
            }
        }
        jQuery.ajax({
            url: AJAX_URL,
            type: 'POST',
            data: JSON.stringify({ migrate_payroll_data:1, items:items }),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': CSRF },
            success: function(res){
                if(res.status){
                    // Clean up localStorage payroll keys
                    var keysToRemove = [];
                    for(var i=0; i<localStorage.length; i++){
                        if(localStorage.key(i).indexOf('hrms_payroll_')===0){
                            keysToRemove.push(localStorage.key(i));
                        }
                    }
                    keysToRemove.forEach(function(k){ localStorage.removeItem(k); });
                    localStorage.setItem('hrms_migration_dismissed', '1');
                    jQuery('#migrateMsg').html('<span class="text-success">'+res.message+'. Refreshing...</span>');
                    setTimeout(function(){ location.reload(); }, 1500);
                } else {
                    jQuery('#migrateMsg').html('<span class="text-danger">'+(res.message||'Failed')+'</span>');
                    $btn.prop('disabled', false).text('Migrate Now');
                }
            },
            error: function(){
                jQuery('#migrateMsg').html('<span class="text-danger">Network error</span>');
                $btn.prop('disabled', false).text('Migrate Now');
            }
        });
    });

});
})();
</script>
@endsection

@include('common.footer')
