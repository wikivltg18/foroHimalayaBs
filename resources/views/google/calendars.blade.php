<x-app-layout>
    <x-slot name="titulo">
        Configuración de Google Calendar
    </x-slot>

    <x-slot name="slot">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                            <ul class="nav nav-tabs card-header-tabs" id="configTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab" aria-selected="true">
                                        <i class="bi bi-person-gear me-2"></i>Mi Configuración
                                    </button>
                                </li>
                                @if(isset($users) && count($users) > 0)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab" aria-selected="false">
                                        <i class="bi bi-people-fill me-2"></i>Gestión de Colaboradores
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content" id="configTabsContent">
                                <!-- TAB 1: MI CONFIGURACIÓN -->
                                <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                                    <h5 class="mb-3 text-dark">Mis Calendarios de Google</h5>
                                    <p class="text-muted mb-4">
                                        Selecciona el calendario donde deseas que se creen los eventos de tus tareas programadas.
                                    </p>

                                    <form action="{{ route('google.calendars.set') }}" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label for="calendar_id" class="form-label fw-bold">Calendario por defecto</label>
                                            <select name="calendar_id" id="calendar_id" class="form-select" required>
                                                <option value="">-- Seleccionar calendario --</option>
                                                @foreach ($calendars as $cal)
                                                    <option value="{{ $cal['id'] }}" {{ $selected === $cal['id'] ? 'selected' : '' }}>
                                                        {{ $cal['summary'] }}
                                                        {{ $cal['primary'] ? '(Principal)' : '' }}
                                                        {{ $cal['description'] ? '- ' . $cal['description'] : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text mt-2">
                                                @if($selected)
                                                    <i class="bi bi-check-circle text-success"></i>
                                                    Actual: <strong>{{ collect($calendars)->firstWhere('id', $selected)['summary'] ?? $selected }}</strong>
                                                @else
                                                    Selecciona un calendario para comenzar.
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 flex-wrap border-top pt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-save me-2"></i>Guardar Cambios
                                            </button>
                                            <a href="{{ route('google.team_calendar') }}" class="btn btn-outline-secondary">
                                                <i class="bi bi-calendar3 me-2"></i>Ver Calendario de Equipo
                                            </a>
                                            
                                            <div class="ms-auto d-flex gap-2">
                                                <button form="test-event-form" type="submit" class="btn btn-outline-info btn-sm">
                                                    <i class="bi bi-plus-circle me-1"></i>Test
                                                </button>
                                                <a href="{{ route('google.redirect') }}" class="btn btn-outline-secondary btn-sm">
                                                    <i class="bi bi-arrow-repeat me-1"></i>Reconectar
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <!-- Formulario oculto para el botón de test -->
                                    <form id="test-event-form" action="{{ route('google.events.test') }}" method="POST" class="d-none">@csrf</form>
                                </div>

                                <!-- TAB 2: GESTIÓN DE COLABORADORES (SUPERADMIN) -->
                                @if(isset($users) && count($users) > 0)
                                <div class="tab-pane fade" id="admin" role="tabpanel" aria-labelledby="admin-tab">
                                    <h5 class="mb-3 text-dark">Gestión de Calendarios</h5>
                                    <p class="text-muted mb-3">Asigna calendarios a los colaboradores para centralizar la agenda.</p>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Colaborador</th>
                                                    <th>Estado</th>
                                                    <th>Calendario Asignado</th>
                                                    <th class="text-end">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($users as $user)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-initial rounded-circle bg-label-primary me-2 fw-bold">
                                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold">{{ $user->name }}</div>
                                                                <small class="text-muted">{{ $user->email }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($user->googleAccount && $user->googleAccount->access_token !== 'SUB_ACCOUNT')
                                                            <span class="badge bg-success">Conectado</span>
                                                        @elseif($user->googleAccount && $user->googleAccount->access_token === 'SUB_ACCOUNT')
                                                            <span class="badge bg-warning text-dark">Delegado para Ti</span>
                                                        @else
                                                            <span class="badge bg-secondary">Sin Conexión</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <form action="{{ route('google.calendars.set') }}" method="POST" class="d-flex gap-2 align-items-center">
                                                                @csrf
                                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                                <select name="calendar_id" class="form-select form-select-sm" style="max-width: 180px;">
                                                                    <option value="">-- Sin asignar --</option>
                                                                    @foreach ($calendars as $cal)
                                                                        <option 
                                                                            value="{{ $cal['id'] }}" 
                                                                            {{ ($user->googleAccount->calendar_id ?? '') === $cal['id'] ? 'selected' : '' }}
                                                                        >
                                                                            {{ Str::limit($cal['summary'], 20) }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="submit" class="btn btn-sm btn-icon btn-primary" title="Guardar">
                                                                    <i class="bi bi-check-lg"></i>
                                                                </button>
                                                            </form>

                                                            @if($user->googleAccount)
                                                                <form action="{{ route('google.events.test') }}" method="POST">
                                                                    @csrf
                                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-info" title="Probar Creación de Evento">
                                                                        <i class="bi bi-plus-circle me-1"></i>Test
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        @if($user->googleAccount && $user->googleAccount->access_token !== 'SUB_ACCOUNT')
                                                            <small class="text-muted fst-italic">Gestionado por usuario</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>
