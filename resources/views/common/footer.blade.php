    <!-- latest jquery-->
        <script src="{{ url('assets/js/jquery.min.js') }}"></script>
        <!-- Bootstrap js-->
        <script src="{{ url('assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
        <!-- feather icon js-->
        <script src="{{ url('assets/js/icons/feather-icon/feather.min.js') }}"></script>
        <script src="{{ url('assets/js/icons/feather-icon/feather-icon.js') }}"></script>
        @if(empty($noSidebar))
        <!-- scrollbar js-->
        <script src="{{ url('assets/js/scrollbar/simplebar.min.js') }}"></script>
        <script src="{{ url('assets/js/scrollbar/custom.js') }}"></script>
        <!-- Sidebar jquery-->
        <script src="{{ url('assets/js/config.js') }}"></script>
        <!-- Plugins JS start-->
        <script src="{{ url('assets/js/sidebar-menu.js') }}"></script>
        <script src="{{ url('assets/js/sidebar-pin.js') }}"></script>
        @endif
        
        <!-- Sweet Alert -->
        <script src="{{ url('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

        @yield('js')

        <!-- Plugins JS Ends-->
        <!-- Theme js-->
        <script src="{{ url('assets/js/script.js') }}"></script>
        <script src="{{ url('assets/js/script1.js') }}"></script>
    </body>
</html>