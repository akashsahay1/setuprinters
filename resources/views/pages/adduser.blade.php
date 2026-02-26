@php
    $isEdit = isset($editUser);
    $pageTitle = $isEdit ? 'Edit User' : 'Add User';
@endphp
@include('common.header', ['title' => $pageTitle])

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
    @include('common.innerheader', ['title' => $pageTitle])
    <!-- Page Header Ends -->

    <div class="page-body-wrapper">

        <!-- Page Sidebar Start-->
        @include('common.sidebar')
        <!-- Page Sidebar Ends-->

        <div class="page-body">
            <!-- Container-fluid starts-->
            <div class="container-fluid">
                <div class="edit-profile">
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card profile-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">{{ $pageTitle }}</h5>
                                </div>
                                <div class="card-body">
                                    <form id="profileForm" class="custom-input" novalidate>
                                        @if($isEdit)
                                            <input type="hidden" id="edit_user_id" value="{{ $editUser->id }}">
                                        @endif
                                        <!-- Profile Photo Section -->
                                        <div class="row mb-4">
                                            <div class="col-12">
                                                <div class="profile-photo-section">
                                                    <div class="photo-container">
                                                        <div class="photo-wrapper" id="photoWrapper">
                                                            <img id="profilePreview" class="profile-img" alt="Profile Photo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;" src="{{ $isEdit && $editUser->profile_photo ? asset($editUser->profile_photo) : asset('assets/images/test/user_profile_default.png') }}">
                                                            <div class="photo-overlay">
                                                                <i data-feather="camera"></i>
                                                            </div>
                                                        </div>
                                                        <span class="photo-label" id="photoLabel">{{ $isEdit && $editUser->profile_photo ? 'Change Photo' : 'Set Profile Photo' }}</span>
                                                    </div>
                                                    <input id="profilepic" type="file" class="d-none" accept="image/jpeg,image/png,image/gif" aria-label="Upload profile photo" />
                                                </div>
                                                <div class="error-message" id="photo_error"></div>
                                            </div>
                                        </div>

                                        <!-- Full Name & Email Row -->
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group mb-4">
                                                    <label for="full_name" class="form-label">Full Name<span class="text-danger">*</span></label>
                                                    <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Enter full name" autocomplete="name" aria-required="true" value="{{ $isEdit ? $editUser->full_name : '' }}" />
                                                    <div class="error-message" id="full_name_error"></div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-4">
                                                    <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
                                                    <input type="email" id="email" name="email" class="form-control" placeholder="your-email@domain.com" autocomplete="email" aria-required="true" value="{{ $isEdit ? $editUser->email : '' }}" />
                                                    <div class="error-message" id="email_error"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group mb-4">
                                                    <label for="phone_number" class="form-label">Phone Number<span class="text-danger">*</span></label>
                                                    <input type="tel" id="phone_number" name="phone_number" class="form-control" placeholder="Enter phone number" autocomplete="tel" aria-required="true" value="{{ $isEdit ? $editUser->phone_number : '' }}" />
                                                    <div class="error-message" id="phone_number_error"></div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-4">
                                                    <label for="phone_number2" class="form-label">Phone Number 2</label>
                                                    <input type="tel" id="phone_number2" name="phone_number_2" class="form-control" placeholder="Enter alternate phone" value="{{ $isEdit ? $editUser->phone_number_2 : '' }}" />
                                                    <div class="error-message" id="phone_number2_error"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group mb-4">
                                                    <label for="password" class="form-label">Password</label>
                                                    <div class="password-wrapper">
                                                        <input type="password" id="password" name="password" class="form-control" placeholder="{{ $isEdit ? 'Leave blank to keep current' : 'Enter password' }}" autocomplete="off" {{ $isEdit ? '' : 'aria-required=true' }} />
                                                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                                                            <i data-feather="eye" id="eyeIcon"></i>
                                                        </button>
                                                    </div>
                                                    <div class="error-message" id="password_error"></div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-4">
                                                    <label for="user_role" class="form-label">User Role<span class="text-danger">*</span></label>
                                                    <select id="user_role" name="user_role" class="form-control form-select" aria-required="true">
                                                        <option value="">Select Role</option>
                                                        <option value="admin" {{ $isEdit && $editUser->user_role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                        <option value="manager" {{ $isEdit && $editUser->user_role === 'manager' ? 'selected' : '' }}>Manager</option>
                                                    </select>
                                                    <div class="error-message" id="user_role_error"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group mb-4">
                                                    <label for="address" class="form-label">Address<span class="text-danger">*</span></label>
                                                    <input type="text" id="address" name="address" class="form-control" placeholder="Enter full address" autocomplete="street-address" aria-required="true" value="{{ $isEdit ? $editUser->address : '' }}" />
                                                    <div class="error-message" id="address_error"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="d-flex justify-content-start gap-2 mt-3 mb-4">
                                <button type="button" id="saveBtn" class="btn btn-primary">
                                    <span class="btn-text">{{ $isEdit ? 'Update' : 'Save' }}</span>
                                    <span class="btn-loader d-none">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                        {{ $isEdit ? 'Updating...' : 'Saving...' }}
                                    </span>
                                </button>
                                <a href="{{ url('users') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
        </div>
    </div>
</div>

@section('js')
<script>
jQuery(document).ready(function() {

    var IS_EDIT = {{ $isEdit ? 'true' : 'false' }};

    // Initialize Feather Icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // =====================
    // Profile Photo Handler
    // =====================
    var fileInput = jQuery('#profilepic');
    var previewImg = jQuery('#profilePreview');
    var photoLabel = jQuery('#photoLabel');

    jQuery('.photo-container').on('click', function() {
        fileInput.click();
    });

    fileInput.on('change', function() {
        var file = this.files[0];
        clearError('photo');

        if (file) {
            var validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (validTypes.indexOf(file.type) === -1) {
                showError('photo', 'Please select a valid image (JPG, PNG, or GIF)');
                this.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                showError('photo', 'Image size must be less than 2MB');
                this.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                previewImg.css('opacity', '0.5');
                setTimeout(function() {
                    previewImg.attr('src', e.target.result);
                    previewImg.css('opacity', '1');
                    photoLabel.text('Change Photo');
                }, 150);
            };
            reader.readAsDataURL(file);
        }
    });

    // =====================
    // Password Toggle
    // =====================
    jQuery('#togglePassword').on('click', function() {
        var passwordField = jQuery('#password');
        var eyeIcon = jQuery('#eyeIcon');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            eyeIcon.replaceWith('<i data-feather="eye-off" id="eyeIcon"></i>');
        } else {
            passwordField.attr('type', 'password');
            eyeIcon.replaceWith('<i data-feather="eye" id="eyeIcon"></i>');
        }
        feather.replace();
    });

    // =====================
    // Validation Functions
    // =====================
    function showError(field, message) {
        jQuery('#' + field + '_error').text(message).addClass('show');
        jQuery('#' + field).addClass('is-invalid').removeClass('is-valid');
    }

    function clearError(field) {
        jQuery('#' + field + '_error').text('').removeClass('show');
        jQuery('#' + field).removeClass('is-invalid');
    }

    function setValid(field) {
        jQuery('#' + field).removeClass('is-invalid is-valid');
        clearError(field);
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidPhone(phone) {
        return /^[\d\s\-\+\(\)]{7,20}$/.test(phone);
    }

    // =====================
    // Real-time Validation
    // =====================
    jQuery('#full_name').on('blur input', function() {
        var value = jQuery(this).val().trim();
        if (value === '') {
            showError('full_name', 'Full name is required');
        } else if (value.length < 2) {
            showError('full_name', 'Name must be at least 2 characters');
        } else {
            setValid('full_name');
        }
    });

    jQuery('#email').on('blur input', function() {
        var value = jQuery(this).val().trim();
        if (value === '') {
            showError('email', 'Email is required');
        } else if (!isValidEmail(value)) {
            showError('email', 'Please enter a valid email address');
        } else {
            setValid('email');
        }
    });

    jQuery('#phone_number').on('blur input', function() {
        var value = jQuery(this).val().trim();
        if (value === '') {
            showError('phone_number', 'Phone number is required');
        } else if (!isValidPhone(value)) {
            showError('phone_number', 'Please enter a valid phone number');
        } else {
            setValid('phone_number');
        }
    });

    jQuery('#password').on('blur input', function() {
        var value = jQuery(this).val();
        if (!IS_EDIT && value === '') {
            showError('password', 'Password is required');
        } else if (value !== '' && value.length < 6) {
            showError('password', 'Password must be at least 6 characters');
        } else {
            setValid('password');
        }
    });

    jQuery('#user_role').on('blur change', function() {
        var value = jQuery(this).val();
        if (value === '') {
            showError('user_role', 'Please select a role');
        } else {
            setValid('user_role');
        }
    });

    jQuery('#address').on('blur input', function() {
        var value = jQuery(this).val().trim();
        if (value === '') {
            showError('address', 'Address is required');
        } else if (value.length < 5) {
            showError('address', 'Please enter a complete address');
        } else {
            setValid('address');
        }
    });

    // =====================
    // Form Validation
    // =====================
    function validateForm() {
        var isValid = true;

        var fullName = jQuery('#full_name').val().trim();
        if (fullName === '') {
            showError('full_name', 'Full name is required');
            isValid = false;
        } else if (fullName.length < 2) {
            showError('full_name', 'Name must be at least 2 characters');
            isValid = false;
        }

        var email = jQuery('#email').val().trim();
        if (email === '') {
            showError('email', 'Email is required');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showError('email', 'Please enter a valid email address');
            isValid = false;
        }

        var phone = jQuery('#phone_number').val().trim();
        if (phone === '') {
            showError('phone_number', 'Phone number is required');
            isValid = false;
        } else if (!isValidPhone(phone)) {
            showError('phone_number', 'Please enter a valid phone number');
            isValid = false;
        }

        var password = jQuery('#password').val();
        if (!IS_EDIT && password === '') {
            showError('password', 'Password is required');
            isValid = false;
        } else if (password !== '' && password.length < 6) {
            showError('password', 'Password must be at least 6 characters');
            isValid = false;
        }

        var role = jQuery('#user_role').val();
        if (role === '') {
            showError('user_role', 'Please select a role');
            isValid = false;
        }

        var address = jQuery('#address').val().trim();
        if (address === '') {
            showError('address', 'Address is required');
            isValid = false;
        } else if (address.length < 5) {
            showError('address', 'Please enter a complete address');
            isValid = false;
        }

        if (!isValid) {
            var firstError = jQuery('.is-invalid').first();
            if (firstError.length) {
                jQuery('html, body').animate({
                    scrollTop: firstError.offset().top - 120
                }, 400);
                firstError.focus();
            }
        }

        return isValid;
    }

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
            '</div></div>'
        );

        jQuery('body').append(toast);
        feather.replace();

        setTimeout(function() {
            toast.fadeOut(300, function() { jQuery(this).remove(); });
        }, 5000);

        toast.find('.btn-close').on('click', function() {
            toast.fadeOut(300, function() { jQuery(this).remove(); });
        });
    }

    // =====================
    // Form Submission
    // =====================
    function setLoading(loading) {
        var btn = jQuery('#saveBtn');
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

    jQuery('#saveBtn').on('click', function() {
        if (!validateForm()) {
            return false;
        }

        setLoading(true);

        var formData = new FormData();
        formData.append('full_name', jQuery('#full_name').val().trim());
        formData.append('email', jQuery('#email').val().trim());
        formData.append('phone_number', jQuery('#phone_number').val().trim());
        formData.append('phone_number_2', jQuery('#phone_number2').val().trim());
        formData.append('user_role', jQuery('#user_role').val());
        formData.append('address', jQuery('#address').val().trim());

        var password = jQuery('#password').val();
        if (password !== '') {
            formData.append('password', password);
        }

        var photoFile = jQuery('#profilepic')[0].files[0];
        if (photoFile) {
            formData.append('profile_photo', photoFile);
        }

        if (IS_EDIT) {
            formData.append('update_user', '1');
            formData.append('user_id', jQuery('#edit_user_id').val());
        } else {
            formData.append('save_user', '1');
            formData.append('password', jQuery('#password').val());
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
                    showToast('success', IS_EDIT ? 'User updated successfully!' : 'User created successfully!');

                    setTimeout(function() {
                        window.location.href = '{{ url("users") }}';
                    }, 1500);
                } else {
                    if (response.errors) {
                        Object.keys(response.errors).forEach(function(field) {
                            var mappedField = field.replace('_2', '2');
                            showError(mappedField, response.errors[field][0]);
                        });
                    }
                    showToast('error', response.message || 'Failed to save user');
                }
            },
            error: function() {
                setLoading(false);
                showToast('error', 'An error occurred. Please try again.');
            }
        });
    });

    // Enter key submission
    jQuery('#profileForm').on('keypress', function(e) {
        if (e.which === 13 && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            jQuery('#saveBtn').click();
        }
    });
});
</script>
@endsection

@include('common.footer')
