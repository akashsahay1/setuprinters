@include('common.header', ['title' => 'Dashboard'])

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
    @include('common.innerheader', ['title' => 'Dashboard'])
    <!-- Page Header Ends -->

    <div class="page-body-wrapper">

        <!-- Page Sidebar Start-->
        @include('common.sidebar')
        <!-- Page Sidebar Ends-->

        <div class="page-body">
                <div class="container-fluid mt-4">

                    <!-- Attendance Dashboard -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <!-- Filters -->
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label mb-0">Date</label>
                                            <input type="text" class="form-control form-control-sm" id="hrmsAttDate" placeholder="Select date range" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label mb-0">Group</label>
                                            <select class="form-select form-control form-select-sm" id="hrmsAttGroup">
                                                <option value="">All</option>
                                                @foreach($groups as $g)
                                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label mb-0">User</label>
                                            <select class="form-select form-control form-select-sm" id="hrmsAttUser">
                                                <option value="">All</option>
                                                @foreach($allStaff as $s)
                                                <option value="{{ $s->id }}" data-group="{{ $s->group_id }}">{{ $s->full_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-primary" id="hrmsAttLoad" style="background-color:#7366FF;border-color:#7366FF;">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Attendance Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Attendance <small class="text-muted">({{ now()->format('d M Y') }})</small></h6>
                                    <span class="badge bg-primary" style="background-color:#7366FF !important;color:#fff !important;" id="scanCount">{{ $todayScans->count() }} scans</span>
                                </div>
                                <div class="card-body pt-0 px-0">
                                    <div class="table-responsive custom-scrollbar">
                                        <table class="table mb-0">
                                            <thead>
                                                <tr>
                                                    <th style="width:10%;">Photo</th>
                                                    <th style="width:30%;">Name</th>
                                                    <th style="width:30%;">QR Code Value</th>
                                                    <th style="width:30%;">Scanned At</th>
                                                </tr>
                                            </thead>
                                            <tbody id="staffTableBody">
                                                @forelse($todayScans as $scan)
                                                    <tr data-staff-id="{{ $scan->staff ? $scan->staff->id : '' }}" data-group-id="{{ $scan->staff ? $scan->staff->group_id : '' }}">
                                                        <td>
                                                            <img src="{{ ($scan->staff && $scan->staff->profile_photo) ? asset($scan->staff->profile_photo) : asset('assets/images/test/user_profile_default.png') }}" alt="" class="scan-photo" data-name="{{ $scan->staff ? $scan->staff->full_name : 'Unknown' }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;cursor:pointer;">
                                                        </td>
                                                        <td>{{ $scan->staff ? $scan->staff->full_name : 'Unknown' }}</td>
                                                        <td>{{ $scan->barcode }}</td>
                                                        <td>{{ $scan->created_at->format('d-m-Y H:i A') }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="4" class="text-center py-4 text-muted">No attendance scans for today.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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

                    <style>
                        .card .table th,
                        .card .table td {
                            padding: 12px 15px;
                            vertical-align: middle;
                        }

                        #hrmsAttLoad,
                        #hrmsAttLoad:hover,
                        #hrmsAttLoad:focus,
                        #hrmsAttLoad:active {
                            background-color: #7366FF !important;
                            border-color: #7366FF !important;
                            color: #fff !important;
                        }
                        #hrmsAttLoad:hover {
                            background-color: #5a50e0 !important;
                            border-color: #5a50e0 !important;
                        }
                    </style>

                </div>
            </div>

        </div>
    </div>

@section('js')

<link rel="stylesheet" href="{{ url('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
<script src="{{ url('assets/js/flat-pickr/flatpickr.js') }}"></script>
<script src="{{ url('assets/js/dashboard/dashboard_9.js') }}"></script>
<script>
(function(){
"use strict";

var CSRF = '{{ csrf_token() }}';
var AJAX_URL = '{{ url("ajax") }}';
var BASE_URL = '{{ url("") }}';
var DEFAULT_PHOTO = '{{ asset("assets/images/test/user_profile_default.png") }}';

// ──────────────────────────────────────
// INIT
// ──────────────────────────────────────
jQuery(function(){
    var attFlatpickr = flatpickr('#hrmsAttDate', {
        mode: 'range',
        dateFormat: 'd/m/Y',
        defaultDate: [new Date()],
        allowInput: false,
        locale: { rangeSeparator: ' - ' }
    });

    // ══════════════════════════════════
    // USER FILTER BY GROUP
    // ══════════════════════════════════
    jQuery('#hrmsAttGroup').on('change', function(){
        var grpId = jQuery(this).val();
        jQuery('#hrmsAttUser').val('');
        jQuery('#hrmsAttUser option').each(function(){
            if(!jQuery(this).val()) return;
            var optGrp = jQuery(this).data('group');
            if(grpId && optGrp != grpId){
                jQuery(this).hide();
            } else {
                jQuery(this).show();
            }
        });
    });

    // ══════════════════════════════════
    // FETCH & RENDER SCANS
    // ══════════════════════════════════
    jQuery('#hrmsAttLoad').on('click', function(){
        fetchScans();
    });

    function parseFlatpickrDates(){
        var selected = attFlatpickr.selectedDates;
        if(!selected.length) return { start: null, end: null };
        var toYmd = function(d){ return d.getFullYear()+'-'+('0'+(d.getMonth()+1)).slice(-2)+'-'+('0'+d.getDate()).slice(-2); };
        var start = toYmd(selected[0]);
        var end = selected.length > 1 ? toYmd(selected[1]) : start;
        return { start: start, end: end };
    }

    function fetchScans(){
        var dates = parseFlatpickrDates();
        if(!dates.start){
            jQuery('#staffTableBody').html('<tr><td colspan="4" class="text-center py-4 text-muted">Please select a date range.</td></tr>');
            return;
        }

        var postData = {
            fetch_scans: 1,
            start_date: dates.start,
            end_date: dates.end,
            _token: CSRF
        };

        var filterGroup = jQuery('#hrmsAttGroup').val();
        var filterUser = jQuery('#hrmsAttUser').val();
        if(filterGroup) postData.group_id = filterGroup;
        if(filterUser) postData.user_id = filterUser;

        jQuery('#hrmsAttLoad').prop('disabled', true).text('Loading...');
        jQuery('#staffTableBody').html('<tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...</td></tr>');

        jQuery.post(AJAX_URL, postData, function(res){
            jQuery('#hrmsAttLoad').prop('disabled', false).text('Submit');
            if(res.status){
                renderScans(res.scans);
                updateHeader(dates, res.scans.length);
            } else {
                jQuery('#staffTableBody').html('<tr><td colspan="4" class="text-center py-4 text-danger">'+(res.message||'Failed to fetch data')+'</td></tr>');
            }
        }).fail(function(){
            jQuery('#hrmsAttLoad').prop('disabled', false).text('Submit');
            jQuery('#staffTableBody').html('<tr><td colspan="4" class="text-center py-4 text-danger">Network error. Please try again.</td></tr>');
        });
    }

    function renderScans(scans){
        var html = '';
        if(!scans.length){
            html = '<tr><td colspan="4" class="text-center py-4 text-muted">No scans found for the selected period.</td></tr>';
        } else {
            scans.forEach(function(scan){
                var staff = scan.staff;
                var photo = (staff && staff.profile_photo) ? (BASE_URL + '/' + staff.profile_photo) : DEFAULT_PHOTO;
                var safeName = staff ? jQuery('<span>').text(staff.full_name).html() : 'Unknown';
                var staffId = staff ? staff.id : '';
                var groupId = staff ? (staff.group_id || '') : '';

                var dateStr = scan.formatted_date || '\u2014';

                html += '<tr data-staff-id="'+staffId+'" data-group-id="'+groupId+'">';
                html += '<td><img src="'+photo+'" alt="" class="scan-photo" data-name="'+safeName+'" style="width:40px;height:40px;border-radius:50%;object-fit:cover;cursor:pointer;"></td>';
                html += '<td>'+safeName+'</td>';
                html += '<td>'+(scan.barcode||'')+'</td>';
                html += '<td>'+dateStr+'</td>';
                html += '</tr>';
            });
        }
        jQuery('#staffTableBody').html(html);
        jQuery('#scanCount').text(scans.length + ' scans');
    }

    function updateHeader(dates, count){
        var formatDate = function(ds){
            var parts = ds.split('-');
            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            return parseInt(parts[2])+' '+months[parseInt(parts[1])-1]+' '+parts[0];
        };
        var label = formatDate(dates.start);
        if(dates.end !== dates.start) label += ' - ' + formatDate(dates.end);
        jQuery('.card-header h6 small').text('(' + label + ')');
        jQuery('.card-header h6').contents().first().replaceWith('Attendance ');
    }


    // ══════════════════════════════════
    // IMAGE PREVIEW MODAL
    // ══════════════════════════════════
    jQuery('#staffTableBody').on('click', '.scan-photo', function(){
        var src = jQuery(this).attr('src');
        var name = jQuery(this).data('name') || '';
        jQuery('#imagePreviewImg').attr('src', src);
        jQuery('#imagePreviewName').text(name);
        new bootstrap.Modal(jQuery('#imagePreviewModal')[0]).show();
    });

}); // end document ready
})();
</script>

@endsection

@include('common.footer')
