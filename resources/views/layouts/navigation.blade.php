
<nav class="navbar navbar-expand-md navbar-light" style="background-color: #003B7B; z-index: 1000; position: sticky; top: 0; height: 60px;  width: 100%;">
    <div class="container-fluid">
        <!-- Brand -->
        <!-- Navbar content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Right Side -->
            <ul class="navbar-nav ms-auto pe-5">
            <!-- Notificaciones -->
            <div class="user-notification" style="padding-top:12px;">
                <div class="dropdown">
                    <a class="dropdown-toggle no-arrow" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon-copy dw dw-notification" style="color: #fff"></i>
                        <img src="{{ asset('img/notificacion.png') }}" class="" alt="logo" style="width: 20px">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end mt-3">
                        <div class="notification-list mx-h-350 customscroll">
                            <ul class="list-unstyled mb-0">
                                <li class="dropdown-item">Notificaciones</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="user-image px-2" style="padding-top:12px;">
                        @php
                            $foto = Auth::user()->foto_perfil;
                        @endphp
                        @if ($foto)
                            <img src="{{ asset('storage/' . $foto) }}" alt="Foto de perfil" class="img-perfil-nav rounded-circle">
                        @else
                            <img src="{{ asset('images/default-profile.png') }}" alt="Foto por defecto" class="img-perfil-nav rounded-circle">
                        @endif
            </div>
            <div>
                <!-- Auth Dropdown -->
                    <li class="nav-item dropdown pt-1">
                        <a class="nav-link dropdown-toggle d-flex align-items-center text-white" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name }} <img src="{{ asset('img/angulo.png') }}" class="px-2 pt-1" alt="logo">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end mt-2" aria-labelledby="userDropdown" style="background-color: #ffff;">
                            <li>
                                <a class="dropdown-item  text-black" href="{{ route('profile.edit') }}">{{ __('Perfil') }}</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a class="dropdown-item  text-black" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                        {{ __('Cerrar sesión') }}
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </li>
                </div>
            </ul>
        </div>
    </div>
</nav>


