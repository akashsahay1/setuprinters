@include('common.header', ['title' => 'Holidays'])

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
    @include('common.innerheader', ['title' => 'Holidays'])
    <!-- Page Header Ends -->

    <div class="page-body-wrapper">

        <!-- Page Sidebar Start-->
        @include('common.sidebar')
        <!-- Page Sidebar Ends-->

        <div class="page-body">
            <div class="container-fluid mt-4">

                <!-- Action buttons + FY filter -->
                <div class="d-flex gap-2 mb-3 align-items-center">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHolidayModal">Add Holiday</button>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <label class="form-label mb-0 fw-semibold">Financial Year:</label>
                        <select class="form-select form-select-sm" id="fyFilter" style="width:auto;">
                            @foreach($availableFys as $fy)
                            <option value="{{ $fy }}" {{ $selectedFy === $fy ? 'selected' : '' }}>FY {{ $fy }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Holidays List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <ul class="list-group" id="holidaysList">
                                    @forelse($holidays as $h)
                                    <li class="list-group-item d-flex justify-content-between align-items-center" data-hid="{{ $h->id }}">
                                        <div class="holiday-display">
                                            <span class="holiday-name-display fw-semibold">{{ $h->name }}</span>
                                            <small class="text-muted ms-2">{{ $h->date->format('d M Y') }}</small>
                                            @if($h->is_yearly)
                                            <span class="badge bg-primary ms-2">Yearly</span>
                                            @endif
                                        </div>
                                        <div class="holiday-edit d-none" style="flex:1;">
                                            <div class="d-flex gap-2">
                                                <input type="text" class="form-control form-control-sm holiday-name-edit" value="{{ $h->name }}">
                                                <input type="date" class="form-control form-control-sm holiday-date-edit" value="{{ $h->date->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div class="common-align gap-2 ms-2">
                                            <button type="button" class="square-white border-0 bg-transparent p-0 holiday-edit-btn" title="Edit">
                                                <svg><use href="{{ asset('assets/svg/icon-sprite.svg#edit-content') }}"></use></svg>
                                            </button>
                                            <button type="button" class="square-white border-0 bg-transparent p-0 holiday-save-btn d-none" title="Save">
                                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" fill="none" stroke="rgba(82,82,108,0.8)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></polyline></svg>
                                            </button>
                                            <button type="button" class="square-white trash-7 border-0 bg-transparent p-0 holiday-del-btn" title="Delete">
                                                <svg><use href="{{ asset('assets/svg/icon-sprite.svg#trash1') }}"></use></svg>
                                            </button>
                                        </div>
                                    </li>
                                    @empty
                                    <li class="list-group-item text-center text-muted" id="noHolidaysMsg">No holidays added yet.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Add Holiday Modal -->
<div class="modal fade" id="addHolidayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Holiday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <span id="holidayMsg" class="d-block mb-2"></span>
                <div class="mb-3">
                    <label class="form-label">Holiday Name</label>
                    <input type="text" class="form-control" id="newHolidayName" placeholder="Holiday name...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" id="newHolidayDate">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="newHolidayYearly" value="1">
                        <label class="form-check-label" for="newHolidayYearly">Yearly (repeats every year)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addHolidayBtn">Add Holiday</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Holiday Confirmation Modal -->
<div class="modal fade" id="deleteHolidayConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteHolidayMsg" class="mb-3">Are you sure you want to delete this holiday?</p>
                <div class="mb-2">
                    <label class="form-label mb-1">Enter your password to confirm</label>
                    <input type="password" class="form-control" id="deleteHolidayPassword" placeholder="Password">
                </div>
                <div id="deleteHolidayError" class="text-danger small" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteHolidaySubmit">Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
#holidaysList .square-white {
    width: 32px;
    height: 32px;
    border-radius: 2px;
    background-color: #fff;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0px 0px 28px 6px rgba(235, 235, 235, 0.4);
}
#holidaysList .square-white svg {
    width: 16px;
    height: 16px;
    fill: rgba(82, 82, 108, 0.8);
}
</style>

@section('js')
<script>
(function(){
"use strict";

var AJAX_URL = '{{ url("ajax") }}';
var CSRF = '{{ csrf_token() }}';
var deleteHolidayId = null;

function showHolidayMsg(msg, type) {
    jQuery('#holidayMsg').html('<span class="text-' + type + '">' + msg + '</span>');
    setTimeout(function(){ jQuery('#holidayMsg').html(''); }, 3000);
}

jQuery(function(){

    // Financial year filter
    jQuery('#fyFilter').on('change', function(){
        window.location.href = '{{ url("holidays") }}?fy=' + jQuery(this).val();
    });

    // Add holiday
    jQuery('#addHolidayBtn').on('click', function(){
        var name = jQuery('#newHolidayName').val().trim();
        var date = jQuery('#newHolidayDate').val();
        if(!name || !date) {
            showHolidayMsg('Please enter both name and date.', 'danger');
            return;
        }
        var isYearly = jQuery('#newHolidayYearly').is(':checked') ? '1' : '0';
        var $btn = jQuery(this);
        $btn.prop('disabled', true).text('Adding...');
        jQuery.post(AJAX_URL, { save_holiday:1, holiday_name:name, holiday_date:date, holiday_is_yearly:isYearly, _token:CSRF }, function(res){
            $btn.prop('disabled', false).text('Add Holiday');
            if(res.status){
                var h = res.holiday;
                var dateObj = new Date(h.date);
                var dateStr = dateObj.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
                // Remove "no holidays" message if present
                jQuery('#noHolidaysMsg').remove();
                var yearlyBadge = h.is_yearly ? '<span class="badge bg-primary ms-2">Yearly</span>' : '';
                var li = '<li class="list-group-item d-flex justify-content-between align-items-center" data-hid="'+h.id+'">'
                    + '<div class="holiday-display">'
                    + '<span class="holiday-name-display fw-semibold">'+jQuery('<span>').text(h.name).html()+'</span>'
                    + '<small class="text-muted ms-2">'+dateStr+'</small>'
                    + yearlyBadge
                    + '</div>'
                    + '<div class="holiday-edit d-none" style="flex:1;">'
                    + '<div class="d-flex gap-2">'
                    + '<input type="text" class="form-control form-control-sm holiday-name-edit" value="'+jQuery('<span>').text(h.name).html()+'">'
                    + '<input type="date" class="form-control form-control-sm holiday-date-edit" value="'+h.date.substring(0,10)+'">'
                    + '</div></div>'
                    + '<div class="common-align gap-2 ms-2">'
                    + '<button type="button" class="square-white border-0 bg-transparent p-0 holiday-edit-btn" title="Edit"><svg><use href="{{ asset("assets/svg/icon-sprite.svg#edit-content") }}"></use></svg></button>'
                    + '<button type="button" class="square-white border-0 bg-transparent p-0 holiday-save-btn d-none" title="Save"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" fill="none" stroke="rgba(82,82,108,0.8)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></polyline></svg></button>'
                    + '<button type="button" class="square-white trash-7 border-0 bg-transparent p-0 holiday-del-btn" title="Delete"><svg><use href="{{ asset("assets/svg/icon-sprite.svg#trash1") }}"></use></svg></button>'
                    + '</div></li>';
                jQuery('#holidaysList').append(li);
                jQuery('#newHolidayName').val('');
                jQuery('#newHolidayDate').val('');
                jQuery('#newHolidayYearly').prop('checked', false);
                showHolidayMsg('Holiday added!', 'success');
            } else {
                showHolidayMsg(res.message||'Failed', 'danger');
            }
        }).fail(function(){
            $btn.prop('disabled', false).text('Add Holiday');
            showHolidayMsg('An error occurred.', 'danger');
        });
    });

    // Edit holiday - show inputs
    jQuery(document).on('click', '.holiday-edit-btn', function(){
        var $li = jQuery(this).closest('li');
        $li.find('.holiday-display').addClass('d-none');
        $li.find('.holiday-edit').removeClass('d-none');
        jQuery(this).addClass('d-none');
        $li.find('.holiday-save-btn').removeClass('d-none');
    });

    // Save holiday edit
    jQuery(document).on('click', '.holiday-save-btn', function(){
        var $li = jQuery(this).closest('li');
        var hid = $li.data('hid');
        var name = $li.find('.holiday-name-edit').val().trim();
        var date = $li.find('.holiday-date-edit').val();
        if(!name || !date) return;
        jQuery.post(AJAX_URL, { update_holiday:1, holiday_id:hid, holiday_name:name, holiday_date:date, _token:CSRF }, function(res){
            if(res.status){
                var dateObj = new Date(date);
                var dateStr = dateObj.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
                $li.find('.holiday-name-display').text(name);
                $li.find('.holiday-display small').text(dateStr);
                $li.find('.holiday-display').removeClass('d-none');
                $li.find('.holiday-edit').addClass('d-none');
                $li.find('.holiday-save-btn').addClass('d-none');
                $li.find('.holiday-edit-btn').removeClass('d-none');
            }
        });
    });

    // Delete holiday - show confirmation
    jQuery(document).on('click', '.holiday-del-btn', function(){
        var $li = jQuery(this).closest('li');
        deleteHolidayId = $li.data('hid');
        var name = $li.find('.holiday-name-display').text();
        jQuery('#deleteHolidayMsg').text('Are you sure you want to delete "' + name + '"?');
        jQuery('#deleteHolidayPassword').val('');
        jQuery('#deleteHolidayError').hide().text('');
        new bootstrap.Modal(document.getElementById('deleteHolidayConfirmModal')).show();
    });

    // Confirm delete holiday
    jQuery('#deleteHolidaySubmit').on('click', function(){
        var password = jQuery('#deleteHolidayPassword').val();
        if(!password) {
            jQuery('#deleteHolidayError').text('Password is required.').show();
            return;
        }
        var $btn = jQuery(this);
        $btn.prop('disabled', true).text('Deleting...');
        jQuery.post(AJAX_URL, { delete_holiday:1, holiday_id:deleteHolidayId, password:password, _token:CSRF }, function(res){
            $btn.prop('disabled', false).text('Delete');
            if(res.status){
                jQuery('#holidaysList li[data-hid="'+deleteHolidayId+'"]').fadeOut(300, function(){
                    jQuery(this).remove();
                    if (jQuery('#holidaysList li').length === 0) {
                        jQuery('#holidaysList').append('<li class="list-group-item text-center text-muted" id="noHolidaysMsg">No holidays added yet.</li>');
                    }
                });
                jQuery('#deleteHolidayConfirmModal').modal('hide');
            } else {
                jQuery('#deleteHolidayError').text(res.message||'Failed to delete.').show();
            }
        }).fail(function(){
            $btn.prop('disabled', false).text('Delete');
            jQuery('#deleteHolidayError').text('An error occurred.').show();
        });
    });

    // Reset delete modal on close
    jQuery('#deleteHolidayConfirmModal').on('hidden.bs.modal', function(){
        jQuery('#deleteHolidayPassword').val('');
        jQuery('#deleteHolidayError').hide().text('');
        deleteHolidayId = null;
    });

});
})();
</script>
@endsection

@include('common.footer')
