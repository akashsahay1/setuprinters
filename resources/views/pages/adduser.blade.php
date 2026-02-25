@include('common.header', ['title' => 'adduser'])

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
        @include('common.innerheader', ['title' => 'adduser'])
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
                                        <h5 class="card-title mb-0">Add Profile</h5>
                                        <div class="card-options">
                                            <a class="card-options-collapse" href="#" data-bs-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
                                            <a class="card-options-remove" href="#" data-bs-toggle="card-remove"><i class="fe fe-x"></i></a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <form id="profileForm" class="custom-input" novalidate>
                                            <!-- Profile Photo Section -->
                                            <div class="row mb-4">
                                                <div class="col-12">
                                                    <div class="profile-photo-section">
                                                        <div class="photo-container">
                                                            <div class="photo-wrapper" id="photoWrapper">
                                                                <img id="profilePreview" class="profile-img" alt="Profile Photo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;" src="../assets/images/test/user_profile_default.png">
                                                                <div class="photo-overlay">
                                                                    <i data-feather="camera"></i>
                                                                </div>
                                                            </div>
                                                                <span class="photo-label" id="photoLabel">Set Profile Photo</span>
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
                                                                <label for="full_name" class="form-label">
                                                                    Full Name<span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Enter full name" autocomplete="name" aria-required="true" />
                                                            <div class="error-message" id="full_name_error"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group mb-4">
                                                            <label for="email" class="form-label">
                                                                Email<span class="text-danger">*</span>
                                                            </label>
                                                            <input type="email"
                                                               id="email"
                                                               name="email"
                                                               class="form-control"
                                                               placeholder="your-email@domain.com"
                                                               autocomplete="email"
                                                               aria-required="true" />
                                                        <div class="error-message" id="email_error"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group mb-4">
                                                        <label for="phone_number" class="form-label">
                                                            Phone Number<span class="text-danger">*</span>
                                                        </label>
                                                        <input type="tel"
                                                               id="phone_number"
                                                               name="phone_number"
                                                               class="form-control"
                                                               placeholder="Enter phone number"
                                                               autocomplete="tel"
                                                               aria-required="true" />
                                                        <div class="error-message" id="phone_number_error"></div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group mb-4">
                                                        <label for="phone_number2" class="form-label">
                                                            Phone Number 2<span class="text-danger">*</span>
                                                        </label>
                                                        <input type="tel"
                                                               id="phone_number2"
                                                               name="phone_number_2"
                                                               class="form-control"
                                                               placeholder="Enter alternate phone"
                                                               aria-required="true" />
                                                        <div class="error-message" id="phone_number2_error"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group mb-4">
                                                        <label for="password" class="form-label">
                                                            Password<span class="text-danger">*</span>
                                                        </label>
                                                        <div class="password-wrapper">
                                                            <input type="password"
                                                                   id="password"
                                                                   name="password"
                                                                   class="form-control"
                                                                   placeholder="Enter password"
                                                                   autocomplete="new-password"
                                                                   aria-required="true" />
                                                            <button type="button"
                                                                    class="password-toggle"
                                                                    id="togglePassword"
                                                                    aria-label="Toggle password visibility">
                                                                <i data-feather="eye" id="eyeIcon"></i>
                                                            </button>
                                                        </div>
                                                        <div class="error-message" id="password_error"></div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group mb-4">
                                                        <label for="user_role" class="form-label">
                                                            User Role<span class="text-danger">*</span>
                                                        </label>
                                                        <select id="user_role"
                                                                name="user_role"
                                                                class="form-control form-select"
                                                                aria-required="true">
                                                            <option value="">Select Role</option>
                                                            <option value="admin">Administrator</option>
                                                            <option value="customer">Customer</option>
                                                        </select>
                                                        <div class="error-message" id="user_role_error"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group mb-4">
                                                        <label for="address" class="form-label">
                                                            Address<span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text"
                                                               id="address"
                                                               name="address"
                                                               class="form-control"
                                                               placeholder="Enter full address"
                                                               autocomplete="street-address"
                                                               aria-required="true" />
                                                        <div class="error-message" id="address_error"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start gap-2 mt-3 mb-4">
                                    <button type="button" id="saveBtn" class="btn btn-primary">
                                        <span class="btn-text">Save</span>
                                        <span class="btn-loader d-none">
                                            <span class="spinner-border spinner-border-sm" role="status"></span>
                                            Saving...
                                        </span>
                                    </button>
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

    // Initialize Feather Icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // =====================
    // Profile Photo Handler
    // =====================
    const fileInput = jQuery('#profilepic');
    const previewImg = jQuery('#profilePreview');
    const photoWrapper = jQuery('#photoWrapper');
    const photoLabel = jQuery('#photoLabel');

    // Click handlers for photo upload
    jQuery('.photo-container').on('click', function() {
        fileInput.click();
    });

    // File change handler with validation and smooth preview
    fileInput.on('change', function() {
        const file = this.files[0];
        clearError('photo');

        if (file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                showError('photo', 'Please select a valid image (JPG, PNG, or GIF)');
                this.value = '';
                return;
            }

            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                showError('photo', 'Image size must be less than 2MB');
                this.value = '';
                return;
            }

            // Smooth preview with fade effect
            const reader = new FileReader();
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
        const passwordField = jQuery('#password');
        const eyeIcon = jQuery('#eyeIcon');

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
        const errorEl = jQuery('#' + field + '_error');
        const inputEl = jQuery('#' + field);

        errorEl.text(message).addClass('show');
        inputEl.addClass('is-invalid').removeClass('is-valid');
    }

    function clearError(field) {
        const errorEl = jQuery('#' + field + '_error');
        const inputEl = jQuery('#' + field);

        errorEl.text('').removeClass('show');
        inputEl.removeClass('is-invalid');
    }

    function setValid(field) {
        const inputEl = jQuery('#' + field);
        inputEl.removeClass('is-invalid is-valid');
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
        const value = jQuery(this).val().trim();
        if (value === '') {
            showError('full_name', 'Full name is required');
        } else if (value.length < 2) {
            showError('full_name', 'Name must be at least 2 characters');
        } else {
            setValid('full_name');
        }
    });

    jQuery('#email').on('blur input', function() {
        const value = jQuery(this).val().trim();
        if (value === '') {
            showError('email', 'Email is required');
        } else if (!isValidEmail(value)) {
            showError('email', 'Please enter a valid email address');
        } else {
            setValid('email');
        }
    });

    jQuery('#phone_number').on('blur input', function() {
        const value = jQuery(this).val().trim();
        if (value === '') {
            showError('phone_number', 'Phone number is required');
        } else if (!isValidPhone(value)) {
            showError('phone_number', 'Please enter a valid phone number');
        } else {
            setValid('phone_number');
        }
    });

    jQuery('#phone_number2').on('blur input', function() {
        const value = jQuery(this).val().trim();
        if (value === '') {
            showError('phone_number2', 'Alternate phone number is required');
        } else if (!isValidPhone(value)) {
            showError('phone_number2', 'Please enter a valid phone number');
        } else {
            setValid('phone_number2');
        }
    });

    jQuery('#password').on('blur input', function() {
        const value = jQuery(this).val();
        if (value === '') {
            showError('password', 'Password is required');
        } else if (value.length < 6) {
            showError('password', 'Password must be at least 6 characters');
        } else {
            setValid('password');
        }
    });

    jQuery('#user_role').on('blur change', function() {
        const value = jQuery(this).val();
        if (value === '') {
            showError('user_role', 'Please select a role');
        } else {
            setValid('user_role');
        }
    });

    jQuery('#address').on('blur input', function() {
        const value = jQuery(this).val().trim();
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
        let isValid = true;

        // Full Name
        const fullName = jQuery('#full_name').val().trim();
        if (fullName === '') {
            showError('full_name', 'Full name is required');
            isValid = false;
        } else if (fullName.length < 2) {
            showError('full_name', 'Name must be at least 2 characters');
            isValid = false;
        }

        // Email
        const email = jQuery('#email').val().trim();
        if (email === '') {
            showError('email', 'Email is required');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showError('email', 'Please enter a valid email address');
            isValid = false;
        }

        // Phone Number
        const phone = jQuery('#phone_number').val().trim();
        if (phone === '') {
            showError('phone_number', 'Phone number is required');
            isValid = false;
        } else if (!isValidPhone(phone)) {
            showError('phone_number', 'Please enter a valid phone number');
            isValid = false;
        }

        // Phone Number 2
        const phone2 = jQuery('#phone_number2').val().trim();
        if (phone2 === '') {
            showError('phone_number2', 'Alternate phone number is required');
            isValid = false;
        } else if (!isValidPhone(phone2)) {
            showError('phone_number2', 'Please enter a valid phone number');
            isValid = false;
        }

        // Password
        const password = jQuery('#password').val();
        if (password === '') {
            showError('password', 'Password is required');
            isValid = false;
        } else if (password.length < 6) {
            showError('password', 'Password must be at least 6 characters');
            isValid = false;
        }

        // User Role
        const role = jQuery('#user_role').val();
        if (role === '') {
            showError('user_role', 'Please select a role');
            isValid = false;
        }

        // Address
        const address = jQuery('#address').val().trim();
        if (address === '') {
            showError('address', 'Address is required');
            isValid = false;
        } else if (address.length < 5) {
            showError('address', 'Please enter a complete address');
            isValid = false;
        }

        // Scroll to first error
        if (!isValid) {
            const firstError = jQuery('.is-invalid').first();
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
        // Remove existing toasts
        jQuery('.alert-toast').remove();

        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? 'check-circle' : 'alert-circle';

        const toast = jQuery(`
            <div class="alert alert-toast ${bgClass} text-white">
                <div class="d-flex align-items-center gap-2">
                    <i data-feather="${icon}"></i>
                    <span>${message}</span>
                    <button type="button" class="btn-close btn-close-white ms-auto" aria-label="Close"></button>
                </div>
            </div>
        `);

        jQuery('body').append(toast);
        feather.replace();

        // Auto remove after 5 seconds
        setTimeout(function() {
            toast.fadeOut(300, function() {
                jQuery(this).remove();
            });
        }, 5000);

        // Close button
        toast.find('.btn-close').on('click', function() {
            toast.fadeOut(300, function() {
                jQuery(this).remove();
            });
        });
    }

    // =====================
    // Form Submission
    // =====================
    function setLoading(loading) {
        const btn = jQuery('#saveBtn');
        const btnText = btn.find('.btn-text');
        const btnLoader = btn.find('.btn-loader');

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

        // Prepare form data
        const formData = new FormData();
        formData.append('save_user', '1');
        formData.append('full_name', jQuery('#full_name').val().trim());
        formData.append('email', jQuery('#email').val().trim());
        formData.append('phone_number', jQuery('#phone_number').val().trim());
        formData.append('phone_number_2', jQuery('#phone_number2').val().trim());
        formData.append('password', jQuery('#password').val());
        formData.append('user_role', jQuery('#user_role').val());
        formData.append('address', jQuery('#address').val().trim());

        // Auto-generate employee_id (random 6-digit number)
        const employeeId = Math.floor(100000 + Math.random() * 900000);
        formData.append('employee_id', employeeId);

        // Add profile photo if selected
        const photoFile = jQuery('#profilepic')[0].files[0];
        if (photoFile) {
            formData.append('profile_photo', photoFile);
        }

        // AJAX submission
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
                    showToast('success', 'User created successfully!');

                    // Redirect after short delay
                    setTimeout(function() {
                        window.location.href = '{{ url("users") }}';
                    }, 1500);
                } else {
                    if (response.errors) {
                        Object.keys(response.errors).forEach(function(field) {
                            const mappedField = field.replace('_2', '2');
                            showError(mappedField, response.errors[field][0]);
                        });
                    }
                    showToast('error', response.message || 'Failed to create user');
                }
            },
            error: function() {
                setLoading(false);
                showToast('error', 'An error occurred. Please try again.');
            }
        });
    });

    // =====================
    // Reset Form
    // =====================
    jQuery('#resetBtn').on('click', function() {
        // Reset form
        jQuery('#profileForm')[0].reset();

        // Reset profile photo
        previewImg.attr('src', '../assets/images/test/user_profile_default.png');
        photoLabel.text('Set Profile Photo');

        // Clear all errors and validation states
        jQuery('.error-message').removeClass('show').text('');
        jQuery('.form-control').removeClass('is-invalid is-valid');

        // Focus first field
        jQuery('#full_name').focus();
    });

    // =====================
    // Enter key submission
    // =====================
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
