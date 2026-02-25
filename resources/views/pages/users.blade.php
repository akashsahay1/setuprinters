@include('common.header', ['title' => 'Users'])

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
        @include('common.innerheader', ['title' => 'Users'])
        <!-- Page Header Ends -->

        <div class="page-body-wrapper">

            <!-- Page Sidebar Start-->
            @include('common.sidebar')
            <!-- Page Sidebar Ends-->

            <div class="page-body">
                <!-- Container-fluid starts-->
                <div class="container-fluid mt-4 user-list-wrapper">
                    <div class="card">
                        <div class="card-header">
                            <h5>Users</h5>
                        </div>
                        <div class="card-body pt-0 px-0">
                            <div class="list-product user-list-table">
                                <div class="table-responsive custom-scrollbar">
                                    <table class="table" id="roles-permission">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Creation Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(count($data->users) > 0)
                                                @foreach($data->users as $user)
                                                    <tr class="product-removes inbox-data">
                                                        <td>
                                                            <a href="user-profile.html">{{ $user->full_name }}</a>
                                                        </td>
                                                        <td>
                                                            <p>{{ $user->email }}</p>
                                                        </td>
                                                        <td>
                                                            <p>{{ date('d M Y, h:i A', strtotime($user->created_at)) }}</p>
                                                        </td>
                                                        <td><span class="badge badge-light-success">Active</span></td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="4" class="text-center">No users found.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="pagination-wrapper px-4 mt-3">
                                {{ $data->users->links() }}
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container-fluid Ends-->
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
var pendingDelete = {};

jQuery(function(){

    jQuery(document).on('click', '.user-delete-btn', function(){
        var $btn = jQuery(this);
        pendingDelete = {
            id: $btn.data('id'),
            name: $btn.data('name'),
            $btn: $btn
        };
        jQuery('#deleteConfirmMsg').text('Delete user "'+$btn.data('name')+'"? This action cannot be undone.');
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

        jQuery.post(AJAX_URL, { delete_user:1, user_id:pendingDelete.id, password:pwd, _token:CSRF }, function(res){
            if(res.status){
                bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
                pendingDelete.$btn.closest('tr').fadeOut(300, function(){ jQuery(this).remove(); });
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

});
})();
</script>
@endsection

@include('common.footer')
