@include('common.header', ['title' => 'Login', 'noSidebar' => true])
    <!-- login page start-->
    <div class="container-fluid p-0">
        <div class="row m-0">
            <div class="col-12 p-0">
                <div class="login-card login-dark">
                    <div>
                        <div class="login-main">
                            <div>
                                <a class="logo" href="{{ url('/') }}">
                                    <img class="img-fluid for-light" src="{{ asset('assets/images/logo/logo.png') }}" alt="Setu Printers">
                                    <img class="img-fluid for-dark" src="{{ asset('assets/images/logo/logo_dark.png') }}" alt="Setu Printers">
                                </a>
                            </div>
                            <form class="theme-form" id="loginForm" method="POST" action="">
                                <h4 class="my-4">Sign in to account</h4>
                                <div class="form-group">
                                    <label class="col-form-label">Email Address</label>
                                    <input class="form-control user_email" type="email" name="email" required placeholder="" value="">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Password</label>
                                    <div class="form-input position-relative">
                                        <input class="form-control user_password" type="password" name="password" required placeholder="" id="passwordInput">
                                        <div class="show-hide"><span class="show"></span></div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <div class="form-check">
                                        <input class="checkbox-primary form-check-input" id="checkbox1" type="checkbox" name="remember">
                                        <label class="text-muted form-check-label" for="checkbox1">Remember password</label>
                                    </div>
                                    <div class="text-end">
                                        <button class="btn btn-primary btn-lg btn-block w-100 mt-3" type="submit" id="loginBtn">Sign in</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- login page end-->

    @section('js')
        <script>
            jQuery(document).ready(function() {
                jQuery('.show-hide span').on('click', function(){
                    var input = jQuery('#passwordInput');
                    if(jQuery(this).hasClass('show')){
                        input.attr('type', 'text');
                        jQuery(this).removeClass('show');
                    } else {
                        input.attr('type', 'password');
                        jQuery(this).addClass('show');
                    }
                });
                jQuery.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                    }
                });                
                jQuery('#loginBtn').click(function(event) {
                    event.preventDefault();
                    jQuery('.emailerror, .passworderror').remove();
                    var user_email = jQuery('.user_email').val();
                    var user_password = jQuery('.user_password').val();  
                    if(user_email == '') {
                        jQuery(".user_email").after("<span class='emailerror text-danger'>Email is required</span>");
                        return false;
                    }
                    if(user_password == '') {
                        jQuery(".user_password").after("<span class='passworderror text-danger'>Password is required</span>");
                        return false;
                    }
                    jQuery.post("{{ url('ajax') }}", {userlogin:1, user_email: user_email, user_password: user_password}, function(response) {                        
                        if(response.status == 1) {
                            window.location.href = "{{ url('dashboard') }}";
                        }
                        if(response.status == 0) {
                            jQuery("#loginBtn").after("<span class='emailerror text-danger'>"+response.message+"</span>");    
                        }
                    });
                });
            });
        </script>
    @endsection
@include('common.footer', ['noSidebar' => true])
