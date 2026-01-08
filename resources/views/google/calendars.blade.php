<x-app-layout>
    <x-slot name="titulo">
        Configuración de Google Calendar
    </x-slot>

    <x-slot name="slot">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar-check me-2"></i>
                                Mis Calendarios de Google
                            </h5>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if (session('info'))
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <p class="text-muted mb-4">
                                Selecciona el calendario donde deseas que se creen los eventos de tus tareas programadas.
                            </p>

                            <form action="{{ route('google.calendars.set') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="calendar_id" class="form-label fw-bold">
                                        Calendario por defecto
                                    </label>
                                    <select name="calendar_id" id="calendar_id" class="form-select" required>
                                        <option value="">-- Seleccionar calendario --</option>
                                        @foreach ($calendars as $cal)
                                            <option 
                                                value="{{ $cal['id'] }}" 
                                                {{ $selected === $cal['id'] ? 'selected' : '' }}
                                            >
                                                {{ $cal['summary'] }}
                                                @if($cal['primary'])
                                                    <span class="badge bg-info">Principal</span>
                                                @endif
                                                @if($cal['description'])
                                                    - {{ $cal['description'] }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">
                                        @if($selected)
                                            <i class="bi bi-check-circle text-success"></i>
                                            Calendar actual: <strong>{{ collect($calendars)->firstWhere('id', $selected)['summary'] ?? $selected }}</strong>
                                        @else
                                            Selecciona un calendario para comenzar a sincronizar eventos
                                        @endif
                                    </div>
                                </div>

                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Guardar selección
                                    </button>
                                    <a href="{{ route('agenda.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Ir a la agenda
                                    </a>
                                </div>
                            </form>

                            <hr class="my-4">

                            <div class="d-flex gap-2 flex-wrap">
                                <form action="{{ route('google.events.test') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-plus-circle me-2"></i>Crear evento de prueba
                                    </button>
                                </form>

                                <a href="{{ route('google.redirect') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-repeat me-2"></i>Reconectar cuenta Google
                                </a>
                            </div>

                            <div class="alert alert-light mt-3" role="alert">
                                <small class="text-muted">
                                    <i class="bi bi-lightbulb me-1"></i>
                                    <strong>Consejo:</strong> Si no ves todos tus calendarios, intenta reconectar tu cuenta Google para actualizar los permisos.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>
