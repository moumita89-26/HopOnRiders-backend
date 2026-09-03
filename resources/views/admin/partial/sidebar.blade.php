<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{AdminHelper::adminPath()}}" class="logo logo-dark">
            @if(!empty(AdminHelper::getSetting('logo')))
            <span class="logo-sm">
                <!-- <img src="{{ asset(AdminHelper::getSetting('logo')) }}" alt="" height="22"> -->
                <img src="https://png.pngtree.com/element_pic/16/11/03/dda587d35b48fd01947cf38931323161.jpg" alt="company logo" height="22">
            </span>
            <span class="logo-lg">
                <!-- <img src="{{ asset(AdminHelper::getSetting('logo')) }}" alt="" height="50"> -->
                <img src="https://png.pngtree.com/element_pic/16/11/03/dda587d35b48fd01947cf38931323161.jpg" alt="company logo" height="50">
            </span>
            @else
            <span class="logo-sm">{{AdminHelper::getSetting('appname')}}</span>
            <span class="logo-lg">
                {{AdminHelper::getSetting('appname')}}
            </span>
            @endif
        </a>
        <!-- Light Logo-->
        <a href="{{AdminHelper::adminPath()}}" class="logo logo-light">
            @if(!empty(AdminHelper::getSetting('logo')))
            <span class="logo-sm">
                <img src="{{ asset(AdminHelper::getSetting('logo')) }}" alt="" > 
                <!-- <img src="https://png.pngtree.com/element_pic/16/11/03/dda587d35b48fd01947cf38931323161.jpg" alt="company logo" height="22"> -->
            </span>
            <span class="logo-lg">
                <!-- <img src="https://png.pngtree.com/element_pic/16/11/03/dda587d35b48fd01947cf38931323161.jpg" alt="company logo" height="50"> -->
                <img src="{{ asset(AdminHelper::getSetting('logo')) }}" alt="" >
            </span>
            @else
            <span class="logo-sm">{{AdminHelper::getSetting('appname')}}</span>
            <span class="logo-lg">
                {{AdminHelper::getSetting('appname')}}
            </span>
            @endif
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link {{ (Request::is('/')) ? 'active' : '' }}" href="{{AdminHelper::adminPath()}}">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>

                @if(!empty(AdminHelper::sidebarMenu()))
                    @foreach(AdminHelper::sidebarMenu() as $menu)
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ (Request::is($menu->url_path.'*'))?'active':''}}" data-path="{{$menu->url_path}}" href='{{ ($menu->is_broken)?"javascript:alert('Route Not Found')":((empty($menu->url) || $menu->url=='#')?'#sidebarAdmin'.str_replace(' ','', $menu->name):$menu->url) }}' @if(!empty($menu->children)) data-bs-toggle="collapse" role="button" aria-expanded="{{ (Request::is($menu->path.'*'))? 'true' : 'false' }}" aria-controls="sidebarAdmin{{str_replace(' ','', $menu->name)}}" @endif>
                            <i class='{{$menu->icon}}'></i> <span>{{ $menu->name }}</span>
                        </a>
                        @if(!empty($menu->children))
                        <div class="collapse {{ (Request::is($menu->url_path.'*'))? 'show' : '' }} menu-dropdown" id="sidebarAdmin{{str_replace(' ','', $menu->name)}}">
                            <ul class="nav nav-sm flex-column">
                            @foreach($menu->children as $child)                              
                                <li class="nav-item {{ (Request::is($child->url_path.'*'))?'active-child':''}}">
                                    <a href='{{ $child->url }}' class="nav-link {{ (Request::is($child->url_path.'*'))?'active':''}}">
                                        @if(!empty($child->icon))<i class='{{$child->icon}}'></i> @endif {{$child->name}}</a>
                                </li>        
                            @endforeach                  
                            </ul>
                        </div>
                        @endif
                    </li>
                    @if(strtolower(trim($menu->name)) === 'revenue report' || trim($menu->url_path, '/') === 'report')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ Request::is('settlements*') ? 'active' : '' }}" href="{{ route('admin.settlements.index') }}">
                            <i class="ri-exchange-dollar-line"></i> <span>Settlement / Reconciliation</span>
                        </a>
                    </li>
                    @endif
                    @endforeach
                @endif
              
               
               @if(AdminHelper::isSuperadmin())
                <li class="menu-title"><span data-key="t-menu">SUPERADMIN</span></li>               

                <li class="nav-item">
                    <a class="nav-link menu-link {{ (Request::path()=='admin/menu-management') ? 'active' : '' }}" href="{{route('getMenus')}}">
                        <i class="fa fa-bars"></i> <span>Menu Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link {{ (Request::path()=='admin/privileges') ? 'active' : '' }}" href="{{route('getPrivilege')}}">
                        <i class="fa fa-key"></i> <span>Privileges</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link {{ (Request::path()=='admin/admin-users') ? 'active' : '' }}" href="{{route('getAdminUsers')}}">
                        <i class="fa fa-users"></i> <span>Admin Users</span>
                    </a>
                </li>
                @endif
              
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
