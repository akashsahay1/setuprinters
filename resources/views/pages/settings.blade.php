@include('common.header', ['title' => 'Settings'])

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
    @include('common.innerheader', ['title' => 'Settings'])
    <!-- Page Header Ends -->

    <div class="page-body-wrapper">

        <!-- Page Sidebar Start-->
        @include('common.sidebar')
        <!-- Page Sidebar Ends-->

        <div class="page-body">
            <!-- Container-fluid starts-->
            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-2">Data Cleanup</h5>
                                <p class="text-muted mb-3">Permanently delete all data (holidays, attendance, scans, leaves, payroll) for a past financial year.</p>
                                @if($purgeableFys->count() > 0)
                                <div class="d-flex gap-3 align-items-center">
                                    <select class="form-select" id="purgeFySelect" style="width:auto;">
                                        <option value="">-- Select Financial Year --</option>
                                        @foreach($purgeableFys as $fy)
                                        <option value="{{ $fy }}">FY {{ $fy }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-danger" id="purgeDataBtn" disabled>Purge Data</button>
                                </div>
                                @else
                                <p class="text-muted mb-0">No past financial year data available to purge.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">Download Application</h5>
                                <a href="{{ asset('assets/apk/setuprinters.apk') }}" download class="btn btn-purple">
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">Change Password</h5>
                                <button class="btn btn-purple" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    Click Here
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">Holidays</h5>
                                <a href="{{ url('holidays') }}" class="btn btn-purple">
                                    Manage Holidays
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Purge Data Confirmation Modal -->
<div class="modal fade" id="purgeConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Data Purge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">You are about to <strong class="text-danger">permanently delete</strong> all data for <strong>FY <span id="purgeModalFy"></span></strong>:</p>
                <ul class="mb-3">
                    <li>Holidays</li>
                    <li>Daily Attendance Records</li>
                    <li>Scanned Barcodes (IN/OUT)</li>
                    <li>Leave Applications</li>
                    <li>Payroll Records</li>
                </ul>
                <div class="mb-2">
                    <label class="form-label mb-1">Enter your password to confirm</label>
                    <input type="password" class="form-control" id="purgePassword" placeholder="Password">
                </div>
                <div id="purgeError" class="text-danger small" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="purgeSubmitBtn">Purge Data</button>
            </div>
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
                <button type="button" class="btn btn-purple" id="cpSubmitBtn">Update Password</button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
jQuery(document).ready(function() {

    // Initialize Feather Icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // =====================
    // Download APK
    // =====================
    jQuery('#downloadApkBtn').on('click', function() {
        var $btn = jQuery(this);
        var $text = $btn.find('.btn-text');
        var $loader = $btn.find('.btn-loader');

        $btn.prop('disabled', true);
        $text.addClass('d-none');
        $loader.removeClass('d-none');

        jQuery.ajax({
            url: '{{ url("ajax") }}',
            type: 'POST',
            data: { generate_apk: 1, _token: '{{ csrf_token() }}' },
            xhrFields: { responseType: 'blob' },
            success: function(blob, status, xhr) {
                var filename = 'setuprinters.apk';
                var disposition = xhr.getResponseHeader('Content-Disposition');
                if (disposition && disposition.indexOf('filename=') !== -1) {
                    filename = disposition.split('filename=')[1].replace(/['"]/g, '').trim();
                }
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                $btn.prop('disabled', false);
                $text.removeClass('d-none');
                $loader.addClass('d-none');
                showToast('success', 'Application downloaded successfully!');
            },
            error: function() {
                $btn.prop('disabled', false);
                $text.removeClass('d-none');
                $loader.addClass('d-none');
                showToast('error', 'Failed to download application. Please try again.');
            }
        });
    });

    // =====================
    // Logo Removal Tracking
    // =====================
    var removeWhiteLogo = false;
    var removeDarkLogo = false;

    function showRemoveBtn(theme) {
        var btnId = theme === 'white' ? '#whiteLogoRemove' : '#darkLogoRemove';
        jQuery(btnId).removeClass('d-none');
        feather.replace();
    }

    function hideRemoveBtn(theme) {
        var btnId = theme === 'white' ? '#whiteLogoRemove' : '#darkLogoRemove';
        jQuery(btnId).addClass('d-none');
    }

    function resetLogo(theme) {
        var imgId = theme === 'white' ? '#whiteLogoImg' : '#darkLogoImg';
        var placeholderId = theme === 'white' ? '#whiteLogoPlaceholder' : '#darkLogoPlaceholder';
        var inputId = theme === 'white' ? '#whiteLogoInput' : '#darkLogoInput';

        jQuery(imgId).attr('src', '').addClass('d-none');
        jQuery(placeholderId).removeClass('d-none');
        jQuery(inputId).val('');
        hideRemoveBtn(theme);
    }

    jQuery('#whiteLogoRemove').on('click', function(e) {
        e.stopPropagation();
        resetLogo('white');
        removeWhiteLogo = true;
    });

    jQuery('#darkLogoRemove').on('click', function(e) {
        e.stopPropagation();
        resetLogo('dark');
        removeDarkLogo = true;
    });

    // =====================
    // Logo Upload Handlers
    // =====================
    jQuery('#whiteLogoInput').on('click', function(e) {
        e.stopPropagation();
    });

    jQuery('#darkLogoInput').on('click', function(e) {
        e.stopPropagation();
    });

    jQuery('#whiteLogoBox').on('click', function() {
        jQuery('#whiteLogoInput').click();
    });

    jQuery('#darkLogoBox').on('click', function() {
        jQuery('#darkLogoInput').click();
    });

    jQuery('#whiteLogoInput').on('change', function() {
        handleLogoChange(this, 'white');
    });

    jQuery('#darkLogoInput').on('change', function() {
        handleLogoChange(this, 'dark');
    });

    function handleLogoChange(input, theme) {
        var file = input.files[0];
        clearError(theme + '_logo');

        if (file) {
            var validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
            if (validTypes.indexOf(file.type) === -1) {
                showError(theme + '_logo', 'Please select a valid image (JPG, PNG, GIF, or SVG)');
                input.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                showError(theme + '_logo', 'Image size must be less than 2MB');
                input.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                var imgId = theme === 'white' ? '#whiteLogoImg' : '#darkLogoImg';
                var placeholderId = theme === 'white' ? '#whiteLogoPlaceholder' : '#darkLogoPlaceholder';

                jQuery(imgId).attr('src', e.target.result).removeClass('d-none');
                jQuery(placeholderId).addClass('d-none');
                showRemoveBtn(theme);

                if (theme === 'white') removeWhiteLogo = false;
                if (theme === 'dark') removeDarkLogo = false;
            };
            reader.readAsDataURL(file);
        }
    }

    // =====================
    // Validation Functions
    // =====================
    function showError(field, message) {
        var errorEl = jQuery('#' + field + '_error');
        errorEl.text(message).addClass('show');

        if (field === 'app_name') {
            jQuery('#app_name').addClass('is-invalid');
        }
    }

    function clearError(field) {
        var errorEl = jQuery('#' + field + '_error');
        errorEl.text('').removeClass('show');

        if (field === 'app_name') {
            jQuery('#app_name').removeClass('is-invalid');
        }
    }

    // Real-time validation for App Name
    jQuery('#app_name').on('blur input', function() {
        var value = jQuery(this).val().trim();
        if (value === '') {
            showError('app_name', 'App name is required');
        } else {
            clearError('app_name');
        }
    });

    // =====================
    // Toast Notification
    // =====================
    function showToast(type, message) {
        jQuery('.alert-toast').remove();

        var bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        var icon = type === 'success' ? 'check-circle' : 'alert-circle';

        var toast = jQuery(
            '<div class="alert alert-toast ' + bgClass + ' text-white">' +
                '<div class="d-flex align-items-center gap-2">' +
                    '<i data-feather="' + icon + '"></i>' +
                    '<span>' + message + '</span>' +
                    '<button type="button" class="btn-close btn-close-white ms-auto" aria-label="Close"></button>' +
                '</div>' +
            '</div>'
        );

        jQuery('body').append(toast);
        feather.replace();

        setTimeout(function() {
            toast.fadeOut(300, function() {
                jQuery(this).remove();
            });
        }, 5000);

        toast.find('.btn-close').on('click', function() {
            toast.fadeOut(300, function() {
                jQuery(this).remove();
            });
        });
    }

    // =====================
    // Save Settings
    // =====================
    function setLoading(loading) {
        var btn = jQuery('#saveSettingsBtn');
        var btnText = btn.find('.btn-text');
        var btnLoader = btn.find('.btn-loader');

        if (loading) {
            btn.prop('disabled', true);
            btnText.addClass('d-none');
            btnLoader.removeClass('d-none');
        } else {
            btn.prop('disabled', false);
            btnText.removeClass('d-none');
            btnLoader.addClass('d-none');
        }
    }

    jQuery('#saveSettingsBtn').on('click', function() {
        // Validate App Name
        var appName = jQuery('#app_name').val().trim();
        if (appName === '') {
            showError('app_name', 'App name is required');
            jQuery('#app_name').focus();
            return false;
        }

        setLoading(true);

        var formData = new FormData();
        formData.append('save_settings', '1');
        formData.append('app_name', appName);

        if (removeWhiteLogo) {
            formData.append('remove_logo_white', '1');
        }
        if (removeDarkLogo) {
            formData.append('remove_logo_dark', '1');
        }

        var whiteLogoFile = jQuery('#whiteLogoInput')[0].files[0];
        if (whiteLogoFile) {
            formData.append('logo_white', whiteLogoFile);
        }

        var darkLogoFile = jQuery('#darkLogoInput')[0].files[0];
        if (darkLogoFile) {
            formData.append('logo_dark', darkLogoFile);
        }

        jQuery.ajax({
            url: '{{ url("ajax") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                setLoading(false);

                if (response.status) {
                    removeWhiteLogo = false;
                    removeDarkLogo = false;
                    showToast('success', 'Settings saved successfully!');
                } else {
                    if (response.errors) {
                        Object.keys(response.errors).forEach(function(field) {
                            showError(field, response.errors[field][0]);
                        });
                    }
                    showToast('error', response.message || 'Failed to save settings');
                }
            },
            error: function() {
                setLoading(false);
                showToast('error', 'An error occurred. Please try again.');
            }
        });
    });

    // Enter key submission
    jQuery('#settingsForm').on('keypress', function(e) {
        if (e.which === 13 && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            jQuery('#saveSettingsBtn').click();
        }
    });

    // =====================
    // Data Cleanup (Purge FY)
    // =====================
    jQuery('#purgeFySelect').on('change', function(){
        jQuery('#purgeDataBtn').prop('disabled', !jQuery(this).val());
    });

    jQuery('#purgeDataBtn').on('click', function(){
        var fy = jQuery('#purgeFySelect').val();
        if(!fy) return;
        Swal.fire({
            title: 'Purge FY ' + fy + ' Data?',
            html: 'This will <strong>permanently delete</strong> all holidays, attendance, scans, leaves, and payroll records for this financial year.<br><br><strong>This action cannot be undone.</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel'
        }).then(function(result){
            if(result.isConfirmed){
                jQuery('#purgeModalFy').text(fy);
                jQuery('#purgePassword').val('');
                jQuery('#purgeError').hide().text('');
                new bootstrap.Modal(document.getElementById('purgeConfirmModal')).show();
            }
        });
    });

    jQuery('#purgeSubmitBtn').on('click', function(){
        var password = jQuery('#purgePassword').val();
        if(!password){
            jQuery('#purgeError').text('Password is required.').show();
            return;
        }
        var fy = jQuery('#purgeFySelect').val();
        var $btn = jQuery(this);
        $btn.prop('disabled', true).text('Purging...');
        jQuery.post('{{ url("ajax") }}', {
            purge_fy_data: 1,
            financial_year: fy,
            password: password,
            _token: '{{ csrf_token() }}'
        }, function(res){
            $btn.prop('disabled', false).text('Purge Data');
            if(res.status){
                jQuery('#purgeConfirmModal').modal('hide');
                var d = res.deleted;
                var msg = 'Deleted: ' + d.holidays + ' holidays, ' + d.attendances + ' attendance records, ' + d.scans + ' scans, ' + d.leaves + ' leaves, ' + d.payroll + ' payroll records.';
                showToast('success', msg);
                // Remove the FY from dropdown
                jQuery('#purgeFySelect option[value="'+fy+'"]').remove();
                jQuery('#purgeFySelect').val('');
                jQuery('#purgeDataBtn').prop('disabled', true);
                if(jQuery('#purgeFySelect option').length <= 1){
                    jQuery('#purgeFySelect').closest('.d-flex').replaceWith('<p class="text-muted mb-0">No past financial year data available to purge.</p>');
                }
            } else {
                jQuery('#purgeError').text(res.message || 'Failed to purge data.').show();
            }
        }).fail(function(){
            $btn.prop('disabled', false).text('Purge Data');
            jQuery('#purgeError').text('An error occurred. Please try again.').show();
        });
    });

    jQuery('#purgeConfirmModal').on('hidden.bs.modal', function(){
        jQuery('#purgePassword').val('');
        jQuery('#purgeError').hide().text('');
    });

    // =====================
    // Change Password
    // =====================
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
@endsection

@include('common.footer')
