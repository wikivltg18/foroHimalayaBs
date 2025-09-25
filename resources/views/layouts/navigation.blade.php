<nav class="navbar navbar-expand-md navbar-light"
    style="background-color:#003B7B; z-index:1000; position:sticky; top:0; height:60px; width:100%;">
    <div class="container-fluid">
        {{-- Brand (si lo necesitas)
        <a class="navbar-brand text-white" href="{{ url('/') }}">Tu App</a>
        --}}

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Right Side -->
            <ul class="navbar-nav ms-auto pe-5">

                @php
                    $user = Auth::user();
                    $notifCount = $user?->unreadNotifications()->count() ?? 0;
                    $latestNotifications = $user?->notifications()->latest()->limit(10)->get() ?? collect();

                    // Mapea label + color del badge por tipo
                    $metaNotif = function (?string $tipo) {
                        return match ($tipo) {
                            'tarea_asignada' => ['badge' => 'primary', 'label' => 'Asignación'],
                            'comentario_tarea' => ['badge' => 'info', 'label' => 'Comentario'],
                            'tarea_finalizada' => ['badge' => 'success', 'label' => 'Finalizada'],
                            default => ['badge' => 'warning', 'label' => 'Notificación'],
                        };
                    };

                    // Borde lateral por tipo
                    $tipoBorderClass = function (?string $tipo) {
                        return match ($tipo) {
                            'tarea_asignada' => 'border-primary',
                            'comentario_tarea' => 'border-info',
                            'tarea_finalizada' => 'border-success',
                            default => 'border-warning',
                        };
                    };

                    // Icono SVG inline por tipo (usa currentColor)
                    $iconSvg = function (?string $tipo) {
                        return match ($tipo) {
                            'comentario_tarea' => <<<SVG
                                                                                                                                                                                                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                                                                                                                                                                                                                                                     viewBox="0 0 16 16" aria-hidden="true">
                                                                                                                                                                                                                                                                  <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4.414a1 1 0 0 0-.707.293L.854 15.146A.5.5 0 0 1 0 14.793zm3.5 1a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1zm0 2.5a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1zm0 2.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z"/>
                                                                                                                                                                                                                                                                </svg>
                                                                                                                                                                                                                                                            SVG,
                            'tarea_asignada' => <<<SVG
                                                                                                                                                                                                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                                                                                                                                                                                                                                                     viewBox="0 0 16 16" aria-hidden="true">
                                                                                                                                                                                                                                                                  <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                                                                                                                                                                                                                                                  <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
                                                                                                                                                                                                                                                                </svg>
                                                                                                                                                                                                                                                            SVG,
                            'tarea_finalizada' => <<<SVG
                                                                                                                                                                                                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                                                                                                                                                                                                                                                     viewBox="0 0 16 16" aria-hidden="true">
                                                                                                                                                                                                                                                                  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                                                                                                                                                                                                                                                </svg>
                                                                                                                                                                                                                                                            SVG,
                            default => <<<SVG
                                                                                                                                                                                                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                                                                                                                                                                                                                                                     viewBox="0 0 16 16" aria-hidden="true">
                                                                                                                                                                                                                                                                  <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901"/>
                                                                                                                                                                                                                                                                </svg>
                                                                                                                                                                                                                                                            SVG,
                        };
                    };
                @endphp

                <li class="nav-item dropdown pt-1">
                    <a class="nav-link dropdown-toggle no-arrow position-relative" href="#" id="notifDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            viewBox="0 0 16 16" aria-hidden="true" style="color:white">
                            <path
                                d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901" />
                        </svg>
                        @if($notifCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $notifCount }}
                            </span>
                        @endif
                    </a>

                    <div class="dropdown-menu dropdown-menu-end mt-2 p-0" aria-labelledby="notifDropdown"
                        style="min-width:500px;">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <strong>Notificaciones</strong>
                            <div class="d-flex gap-2">
                                <form method="POST" action="{{ route('notificaciones.readAll') }}">
                                    @csrf
                                    <button class="btn btn-link btn-sm text-decoration-none">Marcar todas</button>
                                </form>
                                <a href="{{ route('notificaciones.index') }}"
                                    class="btn btn-link btn-sm text-decoration-none">Ver todas</a>
                            </div>
                        </div>

                        <div class="notification-list" style="max-height:360px; overflow:auto;">
                            <ul class="list-unstyled mb-0">
                                @forelse($latestNotifications as $n)
                                    @php
                                        $data = $n->data ?? [];
                                        $isUnread = is_null($n->read_at);
                                        $m = $metaNotif($data['tipo'] ?? null);
                                        $border = $tipoBorderClass($data['tipo'] ?? null);
                                        $url = $data['url'] ?? '#';
                                      @endphp

                                    <li class="border-bottom">
                                        <div class="dropdown-item d-flex align-items-start gap-2 {{ $isUnread ? 'bg-light' : '' }}
                                                                                border-start border-4 {{ $border }}">
                                            {{-- Icono por tipo (SVG) --}}
                                            <div class="flex-shrink-0 mt-1">
                                                <span
                                                    class="badge rounded-pill bg-{{ $m['badge'] }}-subtle text-{{ $m['badge'] }}-emphasis
                                                                                     border border-{{ $m['badge'] }}-subtle p-2 d-inline-flex align-items-center justify-content-center">
                                                    {!! $iconSvg($data['tipo'] ?? null) !!}
                                                </span>
                                            </div>

                                            {{-- Contenido --}}
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <strong class="{{ $isUnread ? '' : 'text-muted' }}">
                                                        {{ $data['tarea'] ?? 'Tarea' }}
                                                    </strong>
                                                    <small class="text-muted">{{ $n->created_at->diffForHumans() }}</small>
                                                </div>

                                                <div
                                                    class="small text-body-secondary d-flex flex-wrap gap-2 align-items-center">
                                                    <span
                                                        class="badge rounded-pill text-bg-{{ $m['badge'] }}">{{ $m['label'] }}</span>

                                                    @if(!empty($data['cliente']))
                                                        <span>{{ $data['cliente'] }}</span>
                                                    @endif
                                                </div>
                                                @if(!empty($data['fecha']))
                                                    <span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                            fill="currentColor" class="me-1" viewBox="0 0 16 16"
                                                            aria-hidden="true">
                                                            <path
                                                                d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v1H0V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2" />
                                                        </svg>
                                                        {{ \Carbon\Carbon::parse($data['fecha'])->format('d/m/Y H:i') }}
                                                    </span>
                                                @endif
                                                @if(($data['tipo'] ?? '') === 'comentario_tarea' && !empty($data['resumen']))
                                                    <span class="text-truncate" style="max-width: 220px;">
                                                        {!! $iconSvg('comentario_tarea') !!} {{ $data['resumen'] }}
                                                    </span>
                                                @endif

                                                <div class="d-flex gap-2 mt-2">
                                                    <a class="btn btn-sm btn-outline-primary" href="{{ $url }}">
                                                        {{-- box-arrow-up-right (svg ligero) --}}
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            fill="currentColor" class="bi bi-box-arrow-up-right"
                                                            viewBox="0 0 16 16">
                                                            <path fill-rule="evenodd"
                                                                d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5" />
                                                            <path fill-rule="evenodd"
                                                                d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z" />
                                                        </svg>
                                                        Abrir
                                                    </a>

                                                    @if($isUnread)
                                                        <form method="POST" action="{{ route('notificaciones.read', $n->id) }}">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-dark">
                                                                {{-- check-circle (svg ligero) --}}
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                                    fill="currentColor" class="bi bi-check2-all"
                                                                    viewBox="0 0 16 16">
                                                                    <path
                                                                        d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0" />
                                                                    <path
                                                                        d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708" />
                                                                </svg>
                                                                Marcar leída
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li>
                                        <div class="dropdown-item text-muted">Sin notificaciones</div>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </li>



                {{-- ===== Avatar ===== --}}
                <li class="nav-item user-image px-2" style="padding-top:12px;">
                    @php $foto = Auth::user()->foto_perfil; @endphp
                    @if ($foto)
                        <img src="{{ asset('storage/' . $foto) }}" alt="Foto de perfil"
                            class="img-perfil-nav rounded-circle">
                    @else
                        <img src="{{ asset('images/default-profile.png') }}" alt="Foto por defecto"
                            class="img-perfil-nav rounded-circle">
                    @endif
                </li>

                {{-- ===== Usuario / Perfil / Logout ===== --}}
                <li class="nav-item dropdown pt-1">
                    <a class="nav-link dropdown-toggle d-flex align-items-center text-white" href="#" id="userDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                        <img src="{{ asset('img/angulo.png') }}" class="px-2 pt-1" alt="logo">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end mt-2" aria-labelledby="userDropdown"
                        style="background-color:#fff;">
                        <li>
                            <a class="dropdown-item text-black"
                                href="{{ route('profile.edit') }}">{{ __('Perfil') }}</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a class="dropdown-item text-black" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Cerrar sesión') }}
                                </a>
                            </form>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</nav>