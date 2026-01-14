<x-app-layout>
    <x-slot name="titulo">Notificaciones</x-slot>

    <style>
        .status-dot {
            width: .5rem;
            height: .5rem;
            border-radius: 50%;
        }

        .list-hover:hover {
            background-color: var(--bs-light-bg-subtle, #f8f9fa);
        }

        .truncate-1 {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>

    @php
        // Helper visual según el tipo
        $metaNotif = function (?string $tipo) {
            switch ($tipo) {
                case 'tarea_asignada':
                    return ['icon' => 'bi-person-check', 'badge' => 'primary', 'label' => 'Asignación'];
                case 'comentario_tarea':
                    return ['icon' => 'bi-chat-dots', 'badge' => 'info', 'label' => 'Comentario'];
                case 'tarea_finalizada':
                    return ['icon' => 'bi-check-circle', 'badge' => 'success', 'label' => 'Finalizada'];
                default:
                    return ['icon' => 'bi-bell', 'badge' => 'warning', 'label' => 'Notificación'];
            }
        };
    @endphp

    <div class="container py-4">
        {{-- Header --}}
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-4 gap-2">
            <h1 class="mb-0 fw-bold">Notificaciones</h1>
            <form method="POST" action="{{ route('notificaciones.readAll') }}" class="ms-sm-3">
                @csrf
                <button type="submit" class="btn text-primary btn-sm">
                    <i class="bi bi-check2-all me-1"></i> Marcar todas como leídas
                </button>
            </form>
        </div>

        {{-- No leídas --}}
        <h5 class="mb-3">
            No leídas
            <span>({{ $unread->count() }})</span>
        </h5>

        @if($unread->isEmpty())
            <div class="alert alert-light d-flex align-items-center" role="alert">
                <i class="bi bi-bell-slash me-2"></i>
                <div>No tienes notificaciones no leídas.</div>
            </div>
        @else
            <ul class="list-group mb-4">
                @foreach($unread as $n)
                    @php
                        $d = $n->data ?? [];
                        $m = $metaNotif($d['tipo'] ?? null);
                        $url = $d['url'] ?? null;
                    @endphp

                    <li
                        class="list-group-item list-group-item-action d-flex align-items-start gap-3 list-hover position-relative">
                        {{-- Indicador de no leída --}}
                        <span
                            class="status-dot bg-primary position-absolute top-50 end-0 translate-middle me-3 d-none d-md-inline"></span>

                        {{-- Icono por tipo --}}
                        <div class="flex-shrink-0">
                            <span
                                class="badge rounded-pill text-bg-{{ $m['badge'] }} bg-opacity-10 border border-{{ $m['badge'] }}-subtle p-2">
                                <i class="bi {{ $m['icon'] }} text-{{ $m['badge'] }}"></i>
                            </span>
                        </div>

                        {{-- Contenido --}}
                        <div class="me-auto w-100">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-semibold truncate-1">
                                        {{ $d['tarea'] ?? 'Tarea' }}
                                        @if(!empty($d['cliente']))
                                            <span class="text-body-secondary">— {{ $d['cliente'] }}</span>
                                        @endif
                                    </div>

                                    {{-- Etiqueta del tipo + metadatos --}}
                                    <div class="small text-body-secondary mt-1 d-flex flex-wrap gap-2 align-items-center">
                                        <span class="badge rounded-pill text-bg-{{ $m['badge'] }}">{{ $m['label'] }}</span>
                                        @if(!empty($d['resumen']) && ($d['tipo'] ?? '') === 'comentario_tarea')
                                            <span class="text-truncate" style="max-width: 380px;">
                                                <i class="bi bi-chat-quote me-1"></i>{{ $d['resumen'] }}
                                            </span>
                                        @endif
                                        @if(!empty($d['fecha']))
                                            <span><i class="bi bi-calendar-event me-1"></i>
                                                {{ dtz($d['fecha'], 'd/m/Y H:i') }}
                                            </span>
                                        @endif
                                        <span><i class="bi bi-clock me-1"></i>{{ $n->created_at?->diffForHumans() }}</span>
                                    </div>

                                    <div class="mt-2 d-flex flex-wrap gap-2">
                                        @if($url)
                                            <a class="btn btn-sm btn-outline-primary" href="{{ $url }}">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                                            </a>
                                        @endif

                                        <form method="POST" action="{{ route('notificaciones.read', $n->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-check-circle me-1"></i>Marcar leída
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div>
                                    <a class="btn btn-primary" href="{{ $d['url'] ?? '#' }}">Ver</a>
                                </div>
                            </div>
                        </div>

                        {{-- Más opciones (placeholder) --}}
                        <button type="button" class="btn btn-link text-body-secondary text-decoration-none p-0 ms-2"
                            aria-label="Más opciones">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- Todas / Anteriores --}}
        <h5 class="mb-3">Todas</h5>
        <ul class="list-group mb-3">
            @foreach($all as $n)
                @php
                    $d = $n->data ?? [];
                    $isRead = (bool) $n->read_at;
                    $m = $metaNotif($d['tipo'] ?? null);
                @endphp

                <li
                    class="list-group-item d-flex align-items-start gap-3 {{ $isRead ? 'opacity-100' : 'bg-body-tertiary' }}">
                    {{-- Icono por tipo --}}
                    <div class="flex-shrink-0">
                        <span
                            class="badge rounded-pill text-bg-{{ $m['badge'] }} bg-opacity-10 border border-{{ $m['badge'] }}-subtle p-2">
                            <i class="bi {{ $m['icon'] }} text-{{ $m['badge'] }}"></i>
                        </span>
                    </div>

                    {{-- Contenido --}}
                    <div class="me-auto w-100">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="{{ $isRead ? 'text-body-secondary' : 'fw-medium' }} truncate-1">
                                    {{ $d['tarea'] ?? 'Tarea' }}
                                    @if(!empty($d['cliente']))
                                        — <span class="{{ $isRead ? '' : 'text-body-secondary' }}">{{ $d['cliente'] }}</span>
                                    @endif
                                </div>

                                <div class="small text-body-secondary d-flex flex-wrap gap-2 mt-1">
                                    <span class="badge rounded-pill text-bg-{{ $m['badge'] }}">{{ $m['label'] }}</span>
                                    @if(!empty($d['resumen']) && ($d['tipo'] ?? '') === 'comentario_tarea')
                                        <span class="text-truncate" style="max-width: 420px;">
                                            <i class="bi bi-chat-quote me-1"></i>{{ $d['resumen'] }}
                                        </span>
                                    @endif
                                    @if(!empty($d['fecha']))
                                        <span><i class="bi bi-calendar-event me-1"></i>
                                            {{ dtz($d['fecha'], 'd/m/Y H:i') }}
                                        </span>
                                    @endif
                                    <span><i class="bi bi-clock me-1"></i>{{ $n->created_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="d-flex flex-row justify-content-center align-items-center">
                                <a class="btn btn-primary mx-2" href="{{ $d['url'] ?? '#' }}">Ver</a>
                                {{-- Acciones --}}
                                @unless($isRead)
                                    <form method="POST" action="{{ route('notificaciones.read', $n->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-check-circle me-1"></i>Marcar leída
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </div>

                    </div>


                </li>
            @endforeach
        </ul>

        <div class="d-flex justify-content-end">
            {{ $all->links('pagination::bootstrap-4') }}
        </div>
    </div>
</x-app-layout>