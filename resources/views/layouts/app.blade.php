<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/custom.css', 'resources/js/custom.js'])
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Fonts (opcional, puedes dejarlo si usas Figtree) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Scripts -->
    <script>
        // Asegurarse de que CSRF token esté disponible para Ajax
        window.csrfToken = '{{ csrf_token() }}';
    </script>
    @stack('styles')
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Page Heading -->
            <div class="col-md-2 mx-auto">
                <aside class="bg-white shadow-sm mt-3 rounded">
                    <div class="container-fluid mt-3">
                        <div class="col-md-12">
                            <button class="btn btn-light d-md-none m-3" id="toggleSidebar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#003B7B"
                                    viewBox="0 0 16 16">
                                    <path d="M1 3h14v2H1V3zm0 4h14v2H1V7zm0 4h14v2H1v-2z" />
                                </svg>
                            </button>
                            <div class="left-side-bar sidebar d-md-block d-none" id="sidebar">
                                <div class="menu-block customscroll">
                                    <img src="{{ asset('img/logo_azul.png') }}" class="py-3 mx-auto d-block" alt="logo">
                                    <div class="sidebar-menu">
                                        <ul id="accordion-menu">
                                            <!-- Opción: Home -->
                                            <li class="dropdown">
                                                <a href="{{ url('/dashboard') }}"
                                                    class="dropdown-toggle no-arrow text-muted text-decoration-none">
                                                    <span class="micon filter me-2 icon-home-1"></span>Inicio
                                                </a>
                                            </li>
                                            <!-- Opción: Clientes -->
                                            <li class="dropdown">
                                                <a href="{{ url('/clientes') }}"
                                                    class="dropdown-toggle no-arrow text-muted text-decoration-none">
                                                    <span class="micon me-2 icon-clientes-1"></span>Clientes
                                                </a>
                                            </li>
                                            <!-- Opción: Mi Equipo -->
                                            <li class="dropdown">
                                                <a href="{{ url('/dashboard') }}"
                                                    class="no-arrow dropdown-toggle  text-muted text-decoration-none"><span
                                                        class="micon me-2 icon-Vector"></span>Mi Equipo</a>

                                                <!-- Submenús: Usuarios, Áreas, Cargos, Roles -->
                                                <ul class="submenu">
                                                    <li><a href="{{ url('/equipo/usuarios') }}"
                                                            class="text-muted text-decoration-none">Usuarios</a></li>
                                                    <li><a href="{{ url('/equipo/areas') }}"
                                                            class="text-muted text-decoration-none">Áreas</a></li>
                                                    <li><a href="{{ url('/equipo/cargos') }}"
                                                            class="text-muted text-decoration-none">Cargos</a></li>
                                                    <li><a href="{{ url('/equipo/roles') }}"
                                                            class="text-muted text-decoration-none">Roles</a></li>
                                                </ul>
                                            </li>
                                            <!-- Opción: Foro -->
                                            <li class="dropdown">
                                                <a href="{{ url('/dashboard') }}"
                                                    class="no-arrow dropdown-toggle  text-muted text-decoration-none"><span
                                                        class="micon me-2 icon-Foro-1"></span>Foro</a>
                                                <!-- Submenús: Áreas, Servicios, Roles -->
                                                <ul class="submenu">
                                                    <li><a href="{{ url('/dashboard') }}"
                                                            class="text-muted text-decoration-none">Diseño</a></li>
                                                    <li><a href="{{ url('/dashboard') }}"
                                                            class="text-muted text-decoration-none">Contenido</a></li>
                                                    <li><a href="{{ url('/dashboard') }}"
                                                            class="text-muted text-decoration-none">Digital
                                                            Performance</a></li>
                                                    <li><a href="{{ url('/dashboard') }}"
                                                            class="text-muted text-decoration-none">Desarrollo</a></li>
                                                    <li><a href="{{ url('/dashboard') }}"
                                                            class="text-muted text-decoration-none">Creatividad</a></li>
                                                    <li><a href="{{ url('/dashboard') }}"
                                                            class="text-muted text-decoration-none">Estraregia</a></li>
                                                </ul>
                                            </li>
                                            <!-- Opción: Analitica -->
                                            <li class="dropdown">
                                                <a href="{{ url('/dashboard') }}"
                                                    class="dropdown-toggle no-arrow text-muted text-decoration-none">
                                                    <span class="micon me-2 icon-informes-1"></span>Analitica
                                                </a>
                                            </li>
                                            <!-- Opción: Tableros -->
                                            <li class="dropdown">
                                                <a href="{{ url('/configuracion/servicios/tableros') }}"
                                                    class="dropdown-toggle no-arrow text-muted text-decoration-none">
                                                    <span class="micon me-2 icon-informes-1"></span>Tableros de
                                                    servicios
                                                </a>
                                            </li>
                                            <!-- Opción: Herramientas -->
                                            <li class="dropdown">
                                                <a href="{{ url('/dashboard') }}"
                                                    class="no-arrow dropdown-toggle  text-muted text-decoration-none"><span
                                                        class="micon me-2 icon-wrench-alt-1"></span>Herramientas</a>
                                                <ul class="submenu">
                                                    <li><a href="{{ url('/herramientas') }}"
                                                            class="text-muted text-decoration-none">Planeación de
                                                            servicios</a></li>
                                                </ul>
                                            </li>
                                            <!-- Submenús: Tipos de servicios, Fases de servicios -->

                                            <!-- Opción: Calendario de equipo -->
                                            <li class="dropdown">
                                                <a href="{{ url('/google/calendars') }}"
                                                    class="dropdown-toggle no-arrow text-muted text-decoration-none">
                                                    <span
                                                        class="micon me-2 icon-wrench-alt-1"></span><span>Calendario</span>
                                                </a>
                                            </li>

                                            <!-- Opción: Permisos -->
                                            <li class="dropdown">
                                                <a href="{{ url('/permisos') }}"
                                                    class="dropdown-toggle no-arrow text-muted text-decoration-none">
                                                    <span
                                                        class="micon me-2 icon-wrench-alt-1"></span><span>Permisos</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                </aside>
            </div>
            <div class="col-md-10 g-0 barra-navegación">

                <!-- Barra de navegación -->
                @include('layouts.navigation')

                <!-- Contenido -->
                <div class="container">
                    <div class="py-4">
                        <div class="container">
                            <div class="rounded shadow-sm card page-header">

                                <!-- Sección de contenido Superior -->
                                <div class="row p-3 d-flex h-100">
                                    <div class="col-md-6 col-sm-10 ">
                                        <ol class="breadcrumb m-0">
                                            <li><a href="/dashboard" class="text-muted text-decoration-none"
                                                    style="font-weight: 500;"><span>Inicio / </a>{{ $titulo ?? '' }}
                                            </li>
                                        </ol>
                                        <div class="title">
                                            <h3 style="color: #003B7B; font-weight: 700;">
                                                {{ $titulo ?? 'Página principal' }}
                                            </h3>
                                        </div>

                                        @if(auth()->user()->googleAccount)
                                            {{-- Ya tiene cuenta de Google vinculada, no mostrar botón --}}
                                        @else
                                            <a href="{{ route('google.calendars') }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-google"></i> Configurar Google Calendar
                                            </a>
                                        @endif
                                        
                                    </div>
                                    @if(isset($buttonPress))
                                        <div class="col-md-6 col-sm-2 d-flex justify-content-end">
                                            <div class="button-press d-flex justify-content-center align-items-center ">
                                                {{ $buttonPress }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Sección de contenido Inferior -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card shadow-sm">
                                        <div class="card-body">
                                            {{ $slot }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    @yield('alert')
    @stack('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const dropdowns = document.querySelectorAll("#accordion-menu .dropdown-toggle");

            dropdowns.forEach(dropdown => {
                dropdown.addEventListener("click", function (e) {
                    const parentLi = this.parentElement;
                    const submenu = parentLi.querySelector(".submenu");

                    if (submenu) {
                        e.preventDefault(); // Evita navegación si hay submenú
                        submenu.classList.toggle("active");
                    }
                });
            });
        });
    </script>
</body>

</html>