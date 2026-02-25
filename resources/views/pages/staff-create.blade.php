@include('common.header', ['title' => 'Add Staff'])

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
    @include('common.innerheader', ['title' => 'Add Staff'])
    <!-- Page Header Ends -->

    <div class="page-body-wrapper">

        <!-- Page Sidebar Start-->
        @include('common.sidebar')
        <!-- Page Sidebar Ends-->

        <div class="page-body">
            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-12">
                        <!-- Personal Information -->
                        <div class="card">
                            <div class="card-header py-3">
                                <h6 class="fw-semibold mb-0">Personal Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row gy-3" style="--bs-gutter-x:30px;">
                                    <div class="col-md-4"><label class="form-label mb-1">Name <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" id="hrmsF_name"></div>
                                    <div class="col-md-4"><label class="form-label mb-1">Phone <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" id="hrmsF_phone"></div>
                                    <div class="col-md-4"><label class="form-label mb-1">Phone 2</label><input type="text" class="form-control form-control-sm" id="hrmsF_phone2"></div>
                                    <div class="col-md-4"><label class="form-label mb-1">Email</label><input type="email" class="form-control form-control-sm" id="hrmsF_email"></div>
                                    <div class="col-md-4"><label class="form-label mb-1">Address</label><input type="text" class="form-control form-control-sm" id="hrmsF_address"></div>
                                    <div class="col-md-4">
                                        <label class="form-label mb-1">Group</label>
                                        <select class="form-select form-control form-select-sm" id="hrmsF_group">
                                            <option value="">-- None --</option>
                                            @foreach($groups as $g)
                                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label mb-1">Thumbnail</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <img id="hrmsF_thumbPreview" src="{{ asset('assets/images/test/user_profile_default.png') }}" alt="" style="width:43px;height:43px;border-radius:50%;object-fit:cover;border:2px solid #dee2e6;">
                                            <input type="file" class="form-control form-control-sm flex-grow-1" id="hrmsF_thumbnail" accept="image/*">
                                            <button type="button" class="btn btn-outline-danger btn-sm flex-shrink-0 d-none" id="hrmsF_thumbRemove">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Account Details -->
                        <div class="card">
                            <div class="card-header py-3">
                                <h6 class="fw-semibold mb-0">Account Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row gy-3" style="--bs-gutter-x:30px;">
                                    <div class="col-md-4"><label class="form-label mb-1">Account Name</label><input type="text" class="form-control form-control-sm" id="hrmsF_accountName"></div>
                                    <div class="col-md-4"><label class="form-label mb-1">Account Number</label><input type="text" class="form-control form-control-sm" id="hrmsF_account"></div>
                                    <div class="col-md-4"><label class="form-label mb-1">IFSC Code</label><input type="text" class="form-control form-control-sm" id="hrmsF_ifsc"></div>
                                    <div class="col-md-4"><label class="form-label mb-1">Basic Monthly Salary</label><input type="number" step="0.01" class="form-control form-control-sm" id="hrmsF_salary" value="0"></div>
                                    <div class="col-md-4">
                                        <label class="form-label mb-1">PF %</label>
                                        <input type="number" step="0.01" class="form-control form-control-sm" id="hrmsF_pfPct" value="12">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Wage Calculation & OT -->
                        <div class="card">
                            <div class="card-header py-3">
                                <h6 class="fw-semibold mb-0">Wage Calculation & OT</h6>
                            </div>
                            <div class="card-body">
                                <div class="row gy-3" style="--bs-gutter-x:30px;">
                                    <div class="col-md-4">
                                        <label class="form-label mb-1">Wage Calc Type</label>
                                        <select class="form-select form-control form-select-sm" id="hrmsF_wageType">
                                            <option value="none">No Calculation (Fixed Daily)</option>
                                            <option value="hour_based">Hour-Based Calculation</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label mb-1">OT Type</label>
                                        <select class="form-select form-control form-select-sm" id="hrmsF_otType" disabled>
                                            <option value="no_ot">No OT</option>
                                            <option value="hours">Hours (1-24)</option>
                                            <option value="minutes">Minutes (1-60)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4" id="hrmsF_shiftWrap" style="display:none;">
                                        <label class="form-label mb-1">Shift Hours</label>
                                        <select class="form-select form-control form-select-sm" id="hrmsF_shiftHours">
                                            <option value="6">6 Hours</option>
                                            <option value="8" selected>8 Hours</option>
                                            <option value="12">12 Hours</option>
                                            <option value="24">24 Hours</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4" id="hrmsF_otHoursWrap" style="display:none;">
                                        <label class="form-label mb-1">Max OT Hours</label>
                                        <select class="form-select form-control form-select-sm" id="hrmsF_otHours">
                                            @for($i = 1; $i <= 24; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-4" id="hrmsF_otMinWrap" style="display:none;">
                                        <label class="form-label mb-1">Max OT Minutes</label>
                                        <input type="number" class="form-control form-control-sm" id="hrmsF_otMin" min="1" max="60" value="30">
                                    </div>
                                </div>
                                <div class="p-2 bg-light rounded mt-3">
                                    <small><strong>Daily:</strong> <span id="hrmsPreviewDaily">0.00</span> | <strong>Hourly:</strong> <span id="hrmsPreviewHourly">0.00</span></small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3" style="padding-bottom:60px;">
                            <button class="btn btn-primary" id="hrmsStaffSaveBtn">Create Staff</button>
                            <span id="hrmsStaffSaveMsg" class="ms-2"></span>
                        </div>
                    </div>{{-- /col --}}
                </div>{{-- /row --}}

            </div>
        </div>{{-- /page-body --}}
    </div>
</div>

@section('js')
<script>
(function(){
"use strict";

var WORKING_DAYS = 26;

jQuery(function(){
    // Dynamic toggles
    jQuery('#hrmsF_wageType').on('change', function(){
        var isHourBased = jQuery(this).val() === 'hour_based';
        jQuery('#hrmsF_shiftWrap').toggle(isHourBased);
        jQuery('#hrmsF_otType').prop('disabled', !isHourBased);
        if (!isHourBased) {
            jQuery('#hrmsF_otType').val('no_ot');
            jQuery('#hrmsF_otHoursWrap').hide();
            jQuery('#hrmsF_otMinWrap').hide();
        }
        updateWagePreview();
    });
    jQuery('#hrmsF_otType').on('change', function(){ jQuery('#hrmsF_otHoursWrap').toggle(jQuery(this).val()==='hours'); jQuery('#hrmsF_otMinWrap').toggle(jQuery(this).val()==='minutes'); });
    jQuery('#hrmsF_salary, #hrmsF_shiftHours, #hrmsF_wageType').on('change keyup', updateWagePreview);

    function updateWagePreview(){
        var sal = parseFloat(jQuery('#hrmsF_salary').val())||0;
        var daily = sal / WORKING_DAYS;
        var hourly = jQuery('#hrmsF_wageType').val()==='hour_based' ? daily/(parseInt(jQuery('#hrmsF_shiftHours').val())||8) : 0;
        jQuery('#hrmsPreviewDaily').text(daily.toFixed(2));
        jQuery('#hrmsPreviewHourly').text(hourly.toFixed(2));
    }

    // Thumbnail preview
    var DEFAULT_THUMB = '{{ asset("assets/images/test/user_profile_default.png") }}';

    jQuery('#hrmsF_thumbnail').on('change', function(){
        var file = this.files[0];
        if(file){
            var reader = new FileReader();
            reader.onload = function(e){ jQuery('#hrmsF_thumbPreview').attr('src', e.target.result); };
            reader.readAsDataURL(file);
            jQuery('#hrmsF_thumbRemove').removeClass('d-none');
        }
    });

    jQuery('#hrmsF_thumbRemove').on('click', function(){
        jQuery('#hrmsF_thumbPreview').attr('src', DEFAULT_THUMB);
        jQuery('#hrmsF_thumbnail').val('');
        jQuery(this).addClass('d-none');
    });

    // Save
    jQuery('#hrmsStaffSaveBtn').on('click', function(){
        var name = jQuery('#hrmsF_name').val().trim();
        var phone = jQuery('#hrmsF_phone').val().trim();
        if(!name){ alert('Name is required'); return; }
        if(!phone){ alert('Phone is required'); return; }

        var $btn = jQuery(this);
        $btn.prop('disabled', true).text('Creating...');

        var formData = new FormData();
        formData.append('create_staff', '1');
        formData.append('full_name', name);
        formData.append('phone_number', phone);
        formData.append('phone_number_2', jQuery('#hrmsF_phone2').val());
        formData.append('email', jQuery('#hrmsF_email').val());
        formData.append('address', jQuery('#hrmsF_address').val());
        formData.append('group_id', jQuery('#hrmsF_group').val());
        formData.append('account_name', jQuery('#hrmsF_accountName').val());
        formData.append('bank_account', jQuery('#hrmsF_account').val());
        formData.append('ifsc_code', jQuery('#hrmsF_ifsc').val());
        formData.append('basic_salary', jQuery('#hrmsF_salary').val());
        formData.append('wage_calc_type', jQuery('#hrmsF_wageType').val());
        formData.append('shift_hours', jQuery('#hrmsF_shiftHours').val());
        formData.append('ot_type', jQuery('#hrmsF_otType').val());
        formData.append('ot_max_hours', jQuery('#hrmsF_otHours').val());
        formData.append('ot_max_minutes', jQuery('#hrmsF_otMin').val());
        formData.append('pf_enabled', '1');
        formData.append('pf_percentage', jQuery('#hrmsF_pfPct').val());

        var thumbFile = jQuery('#hrmsF_thumbnail')[0].files[0];
        if(thumbFile){
            formData.append('thumbnail', thumbFile);
        }

        jQuery.ajax({
            url: '{{ url("ajax") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res){
                if(res.status){
                    jQuery('#hrmsStaffSaveMsg').html('<span class="text-success">Staff created! Redirecting...</span>');
                    setTimeout(function(){ window.location.href = '{{ url("staffs") }}'; }, 1000);
                } else {
                    jQuery('#hrmsStaffSaveMsg').html('<span class="text-danger">'+(res.message||'Create failed')+'</span>');
                    $btn.prop('disabled', false).text('Create Staff');
                }
            },
            error: function(){
                jQuery('#hrmsStaffSaveMsg').html('<span class="text-danger">Network error. Please try again.</span>');
                $btn.prop('disabled', false).text('Create Staff');
            }
        });
    });
});

})();
</script>
@endsection

@include('common.footer')
