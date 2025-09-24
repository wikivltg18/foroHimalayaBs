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

                {{-- ===== Notificaciones ===== --}}
                @php
                    $user = Auth::user();
                    $notifCount = $user?->unreadNotifications()->count() ?? 0;
                    // Últimas 10 (mezcla leídas/no leídas)
                    $latestNotifications = $user?->notifications()->latest()->limit(10)->get() ?? collect();
                @endphp

                <li class="nav-item dropdown pt-1">
                    <a class="nav-link dropdown-toggle no-arrow position-relative" href="#" id="notifDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-bell-fill" style="color: white;" viewBox="0 0 16 16">
                            <path
                                d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901" />
                        </svg>
                        @if($notifCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $notifCount }}
                            </span>
                        @endif
                    </a>

                    <div class="dropdown-menu dropdown-menu-end mt-3 p-0" aria-labelledby="notifDropdown"
                        style="min-width:360px;">
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

                        <div class="notification-list customscroll" style="max-height:360px; overflow:auto;">
                            <ul class="list-unstyled mb-0">
                                @forelse($latestNotifications as $n)
                                    @php
                                        $data = $n->data ?? [];
                                        $isUnread = is_null($n->read_at);
                                    @endphp
                                    <li class="border-bottom">
                                        <div
                                            class="dropdown-item d-flex align-items-start gap-2 {{ $isUnread ? 'bg-light' : '' }}">
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <strong class="{{ $isUnread ? '' : 'text-muted' }}">
                                                        {{ $data['tarea'] ?? 'Tarea' }}
                                                    </strong>
                                                    <small class="text-muted">{{ $n->created_at->diffForHumans() }}</small>
                                                </div>
                                                <div class="small text-muted">
                                                    {{ $data['cliente'] ?? '' }}
                                                    @if(!empty($data['fecha']))
                                                        — vence:
                                                        {{ \Carbon\Carbon::parse($data['fecha'])->format('d/m/Y H:i') }}
                                                    @endif
                                                </div>

                                                <div class="d-flex gap-2 mt-2">
                                                    <a class="btn btn-sm btn-outline-primary"
                                                        href="{{ $data['url'] ?? '#' }}">
                                                        Abrir
                                                    </a>

                                                    @if($isUnread)
                                                        <form method="POST" action="{{ route('notificaciones.read', $n->id) }}">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-secondary">
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