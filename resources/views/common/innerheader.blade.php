<div class="page-header">
    <div class="header-wrapper row m-0">

        <div class="header-logo-wrapper col-auto p-0">

            <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i></div>
        </div>
        <div class="left-header col-xxl-5 col-xl-6 col-lg-5 col-md-4 col-sm-3 p-0">
            <h4>{{ $title }}</h4>
        </div>
        <div class="nav-right col-xxl-7 col-xl-6 col-md-7 col-8 pull-right right-header p-0 ms-auto">
            <ul class="nav-menus">
                <li class="profile-nav onhover-dropdown pe-0 py-0">
                    <div class="d-flex profile-media">
                        <img class="b-r-10" src="{{ asset('assets/images/dashboard/profile.png') }}" alt="">
                        <div class="flex-grow-1"><span>{{ Auth::user()->full_name }}</span>
                            <p class="mb-0">Admin <i class="middle fa-solid fa-angle-down"></i></p>
                        </div>
                    </div>
                    <ul class="profile-dropdown onhover-show-div">
                        <li><a href="{{ url('logout') }}"><i data-feather="log-in"> </i><span>Log out</span></a></li>
                        <li><a href="#" id="openChangePasswordModal"><i data-feather="lock"> </i><span>Change Password</span></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="cpMsg" class="mb-3 d-none"></div>
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="cpCurrent" placeholder="Enter current password">
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" id="cpNew" placeholder="Enter new password">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="cpConfirm" placeholder="Confirm new password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="cpSubmitBtn">Update Password</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
jQuery(document).ready(function() {
    jQuery('#openChangePasswordModal').on('click', function(e) {
        e.preventDefault();
        new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
    });

    jQuery('#changePasswordModal').on('hidden.bs.modal', function() {
        jQuery('#cpCurrent, #cpNew, #cpConfirm').val('').removeClass('is-invalid');
        jQuery('#cpMsg').addClass('d-none').html('');
    });

    jQuery('#cpSubmitBtn').on('click', function() {
        var current = jQuery('#cpCurrent').val();
        var newPass = jQuery('#cpNew').val();
        var confirm = jQuery('#cpConfirm').val();
        var $btn = jQuery(this);

        jQuery('#cpCurrent, #cpNew, #cpConfirm').removeClass('is-invalid');
        jQuery('#cpMsg').addClass('d-none').html('');

        var valid = true;
        if (!current) { jQuery('#cpCurrent').addClass('is-invalid'); valid = false; }
        if (!newPass) { jQuery('#cpNew').addClass('is-invalid'); valid = false; }
        if (!confirm) { jQuery('#cpConfirm').addClass('is-invalid'); valid = false; }
        if (!valid) return;

        if (newPass.length < 8) {
            jQuery('#cpNew').addClass('is-invalid');
            jQuery('#cpMsg').removeClass('d-none').html('<div class="alert alert-danger py-2 mb-0">New password must be at least 8 characters.</div>');
            return;
        }

        if (newPass !== confirm) {
            jQuery('#cpConfirm').addClass('is-invalid');
            jQuery('#cpMsg').removeClass('d-none').html('<div class="alert alert-danger py-2 mb-0">New password and confirmation do not match.</div>');
            return;
        }

        $btn.prop('disabled', true).text('Updating...');

        jQuery.post('{{ url("ajax") }}', {
            change_password: 1,
            current_password: current,
            new_password: newPass,
            new_password_confirmation: confirm,
            _token: '{{ csrf_token() }}'
        }, function(res) {
            $btn.prop('disabled', false).text('Update Password');
            if (res.status) {
                jQuery('#cpMsg').removeClass('d-none').html('<div class="alert alert-success py-2 mb-0">' + res.message + '</div>');
                jQuery('#cpCurrent, #cpNew, #cpConfirm').val('');
                setTimeout(function() {
                    jQuery('#changePasswordModal').modal('hide');
                }, 1500);
            } else {
                jQuery('#cpMsg').removeClass('d-none').html('<div class="alert alert-danger py-2 mb-0">' + (res.message || 'Failed to change password.') + '</div>');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('Update Password');
            jQuery('#cpMsg').removeClass('d-none').html('<div class="alert alert-danger py-2 mb-0">An error occurred. Please try again.</div>');
        });
    });
});
</script>
@endpush