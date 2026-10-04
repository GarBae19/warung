<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ url('/') }}" class="brand-link">
        <img src="{{ asset('dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">Warung</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                @foreach ($menus as $menu)
                    @php
                        // Cek apakah submenu aktif
                        $isSubMenuActive = false;
                        if (optional($menu->SubMenusModel)->count()) {
                            foreach ($menu->SubMenusModel as $submenus) {
                                if ($submenus->nama_submenu == $atribute) {
                                    $isSubMenuActive = true;
                                    break;
                                }
                            }
                        }

                        // Cek apakah menu utama aktif atau salah satu submenu aktif
                        $isActive = $menu->nama_menu == $atribute || $isSubMenuActive ? 'menu-open' : '';
                    @endphp

                    <li class="nav-item {{ $isActive }}">
                        <a href="{{ $menu->link }}"
                            class="nav-link {{ $menu->nama_menu == $atribute ? 'active' : '' }}">
                            <i class="{{ $menu->icon }}"></i>
                            <p>
                                {{ $menu->nama_menu }}
                                @if (optional($menu->SubMenusModel)->count())
                                    <i class="right fas fa-angle-left"></i>
                                @endif
                            </p>
                        </a>
                        @if (optional($menu->SubMenusModel)->count())
                            <ul class="nav nav-treeview">
                                @foreach ($menu->SubMenusModel as $submenus)
                                    <li class="nav-item">
                                        <a href="{{ url($submenus->link) }}"
                                            class="nav-link sidebar-link {{ $submenus->nama_submenu == $atribute ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>{{ $submenus->nama_submenu }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach

                {{-- <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>
                            Charts
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="pages/charts/chartjs.html" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>ChartJS</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/charts/flot.html" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Flot</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/charts/inline.html" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Inline</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/charts/uplot.html" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>uPlot</p>
                            </a>
                        </li>
                    </ul>
                </li> --}}

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
