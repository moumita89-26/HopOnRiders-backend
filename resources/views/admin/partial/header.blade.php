<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ AdminHelper::adminPath() }}" class="logo logo-dark">
                        @if (!empty(AdminHelper::getSetting('logo')))
                            <span class="logo-sm drfgsedg">
                                <img src="{{ asset(AdminHelper::getSetting('logo')) }}" alt="" height="50">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset(AdminHelper::getSetting('logo')) }}" alt="" height="17">
                            </span>
                        @else
                            <span class="logo-sm">{{ AdminHelper::getSetting('appname') }}</span>
                            <span class="logo-lg">
                                {{ AdminHelper::getSetting('appname') }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ AdminHelper::adminPath() }}" class="logo logo-light">
                        @if (!empty(AdminHelper::getSetting('logo')))
                            <span class="logo-sm dfgdg">
                                <img src="{{ asset(AdminHelper::getSetting('logo')) }}" alt="" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset(AdminHelper::getSetting('logo')) }}" alt="" height="50">
                            </span>
                        @else
                            <span class="logo-sm">{{ AdminHelper::getSetting('appname') }}</span>
                            <span class="logo-lg">
                                {{ AdminHelper::getSetting('appname') }}
                            </span>
                        @endif
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
                    id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                <!-- App Search-->
            </div>

            <div class="d-flex align-items-center">

                <!-- <div class="dropdown ms-1 topbar-head-dropdown header-item">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img id="header-lang-img" src="assets/images/flags/us.svg" alt="Header Language" height="20"
                            class="rounded">
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        
                        <a href="javascript:void(0);" class="dropdown-item notify-item language py-2" data-lang="en"
                            title="English">
                            <img src="assets/images/flags/us.svg" alt="user-image" class="me-2 rounded" height="18">
                            <span class="align-middle">English</span>
                        </a>
                    </div>
                </div>  -->


                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>

                {{-- <div class="ms-1 header-item d-sm-flex">
                    <button type="button"
                        class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode">
                        <i class='bx bx-moon fs-22'></i>
                    </button>
                </div> --}}

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">

                            @if (file_exists(public_path(AdminHelper::myPhoto())))
                                <img class="rounded-circle header-profile-user"
                                    src="{{ asset(AdminHelper::myPhoto()) }}" alt="">
                            @endif
                            <span class="text-start ms-xl-2">
                                <span
                                    class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ AdminHelper::myName() }}</span>
                                <!-- <span class="d-none d-xl-block ms-1 fs-12 text-muted user-name-sub-text">{{ AdminHelper::myPrivilegeName() }}</span> -->
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">Welcome</h6>
                        <a class="dropdown-item" href="{{ route('getProfileData') }}"><i
                                class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span
                                class="align-middle">Profile</span></a>
                        <a class="dropdown-item" href='{{ route('getLogout') }}'><i
                                class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span
                                class="align-middle" data-key="t-logout">Logout</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
