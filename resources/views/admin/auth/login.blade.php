<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none">

<head>
    <meta charset="utf-8" />
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name='generator' content='CRUDBooster' />
    <meta name='robots' content='noindex,nofollow' />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset(\App\Helpers\AdminHelper::getSetting('favicon')) }}">

    <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/admin/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/admin/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="{{ asset('assets/admin/css/custom.min.css') }}" rel="stylesheet" type="text/css" />

</head>

<body>

    <div class="auth-page-wrapper pt-1 pt-md-5">
        <!-- auth page bg -->
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>

            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <!-- auth page content -->
        <div class="auth-page-content pt-0 pt-md-3">
            <div class="container">
                <!-- end row -->

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-2">

                            <div class="card-body">
                                <div class="text-center mt-2">
                                    @php $logo = \App\Helpers\AdminHelper::getSetting('logo'); @endphp
                                    <!-- <p class="mt-3 fs-15 fw-medium">{{ \App\Helpers\AdminHelper::getSetting('appname') }}</p> -->
                                    @if (!empty($logo))
                                        <div class="adminlogo">
                                            <img src="{{ asset(\App\Helpers\AdminHelper::getSetting('logo')) }}">
                                        </div>
                                    @else
                                        <p class="mt-3 fs-15 fw-medium">
                                            {{ \App\Helpers\AdminHelper::getSetting('appname') }}</p>
                                    @endif
                                    <h5 class="text-primary">Welcome Back !</h5>
                                    <p class="text-muted"></p>
                                </div>
                                @if (Session::get('message') != '')
                                    <div class="alert alert-borderless alert-warning text-center mb-2 mx-2"
                                        role="alert">
                                        {{ Session::get('message') }}
                                    </div>
                                @endif
                                @if (Session::has('success'))
                                    <div class="alert alert-success">
                                        {{ Session::get('success') }}
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        <strong>{{ session('error') }}</strong>
                                    </div>
                                @endif
                                <div class="p-2 mt-2">
                                    <form autocomplete='off' action="{{ route('postLogin') }}" method="post">
                                        @csrf
                                        <div class="mb-4">
                                            <input type="email" name="email" class="form-control" id="email"
                                                placeholder="Enter email" required>
                                        </div>

                                        <div class="mb-4">
                                            <!-- <div class="float-end">
                                                <a href="auth-pass-reset-basic.html" class="text-muted">Forgot password?</a>
                                            </div> -->
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" name="password" class="form-control pe-5"
                                                    placeholder="Enter password" id="password-input" required>
                                                <button
                                                    class="btn btn-link position-absolute end-0 top-0 text-decoration-none"
                                                    type="button"><i class="ri-eye-fill align-middle"
                                                        id="password-addon"></i></button>
                                            </div>
                                        </div>

                                        <!--  <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="auth-remember-check">
                                            <label class="form-check-label" for="auth-remember-check">Remember me</label>
                                        </div> -->

                                        <div class="mt-4">
                                            <button class="btn btn-success w-100 lgnbtn" type="submit">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->

                        <div class="mt-4 text-center">
                            <p class="mb-0 forgottext">Forgot your password? <a href='{{ route('getForgot') }}'
                                    class="fw-semibold text-primary text-decoration-underline"> Click here </a> </p>
                        </div>

                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">
                                Copyright &copy; <?php echo date('Y'); ?>. All Rights Reserved
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->

    <!-- JAVASCRIPT -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/admin/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/feather-icons/feather.min.js') }}"></script>
    <script type="text/javascript">
        $('#password-addon').on('click', function() {
            $(this).toggleClass('ri-eye-off-fill').toggleClass(
            'ri-eye-fill'); // toggle our classes for the eye icon
            if ($('#password-input').attr('type') == 'password')
                $('#password-input').attr('type', 'text')
            else
                $('#password-input').attr('type', 'password')
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function() {


            setTimeout(function() {
                $(".alert").fadeOut("slow");
            }, 3000);
        });
    </script>
</body>

</html>
