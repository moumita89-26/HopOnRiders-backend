<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none">

<head>
    <meta charset="utf-8" />
    <title>Admin - {{ AdminHelper::getSetting('appname') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="icon" type="image/ico" sizes="32x32" href="{{ asset(config('settings.favicon')) }}">


    <!-- Layout config Js -->
    <!-- <script src="admin/js/layout.js"></script> -->
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="{{ asset('assets/admin/font-awesome/css') }}/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/admin/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!--select2 css-->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!--Timepicker css-->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">

    <!--datepicker css--->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <!-- App Css-->
    <link href="{{ asset('assets/admin/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="{{ asset('assets/admin/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <!--Select2 Js-->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!--datepicker js-->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>

    @stack('head')
</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('admin::partial.header')

        @include('admin::partial.sidebar')
        <div class="vertical-overlay"></div>

        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">{{ !empty($page_title) ? $page_title : Session::get('appname') }}</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a
                                                href="{{ AdminHelper::adminPath() }}">Dashboard</a></li>
                                        <li class="breadcrumb-item active">
                                            {{ !empty($page_title) ? $page_title : Session::get('appname') }}</li>
                                    </ol>
                                </div>
                                <?php /*@endif*/ ?>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    @if (Session('success'))
                        <div class="alert alert-success alert-dismissible alert-solid alert-label-icon fade show"
                            role="alert">
                            <i class="fa fa-check label-icon"></i>
                            {{ Session('success') }}
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif
                    @if (Session('error'))
                        <div class="alert alert-danger alert-dismissible alert-solid alert-label-icon fade show"
                            role="alert">
                            <i class="fa fa-exclamation-triangle label-icon"></i>
                            {{ Session('error') }}
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif
                    @yield('content')
                </div>
            </div>

            @include('admin::partial.footer')
        </div>
    </div>

    @include('admin::partial.scripts')

    @stack('bottom')
</body>

</html>
