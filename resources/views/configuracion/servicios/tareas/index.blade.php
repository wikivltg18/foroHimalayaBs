@php
    $tablero = optional($tarea->columna)->tablero;
    $cliente = optional($tablero)->cliente;
    $horasReales = $tarea->timeLogs->sum('duracion_h');
@endphp

<div class="modal fade" id="modalTarea-{{ $tarea->id }}" tabindex="-1"
    aria-labelledby="modalTareaLabel-{{ $tarea->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:#003B7B;">
                <h5 class="modal-title text-white fw-bold" id="modalTareaLabel-{{ $tarea->id }}">
                    {{ $tarea->titulo }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                {{-- Información general --}}
                <div class="section-title h5 fw-bold py-2" style="color:#003B7B;">Información general</div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="mb-1">
                            <span class="text-muted">Estado:</span>
                            <span class="badge bg-secondary">{{ optional($tarea->estado)->nombre ?? '—' }}</span>
                        </div>
                        <div class="mb-1">
                            <span class="text-muted">Columna:</span>
                            {{ optional($tarea->columna)->nombre_de_la_columna ?? '—' }}
                        </div>
                        <div class="mb-1">
                            <span class="text-muted">Cliente:</span>
                            {{ optional($cliente)->nombre ?? '—' }}
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="mb-1"><span class="text-muted">Posición:</span> {{ $tarea->posicion }}</div>
                        <div class="mb-1"><span class="text-muted">Archivada:</span>
                            {{ $tarea->archivada ? 'Sí' : 'No' }}</div>
                    </div>
                </div>

                <hr>

                {{-- Asignación & Gestión de tiempo --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="section-title fw-bold mb-2" style="color:#003B7B;">Asignación</div>
                        <div class="mb-1"><span class="text-muted">Área:</span>
                            {{ optional($tarea->area)->nombre ?? '—' }}</div>
                        <div class="mb-1"><span class="text-muted">Colaborador:</span>
                            {{ optional($tarea->usuario)->name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="section-title fw-bold mb-2" style="color:#003B7B;">Gestión de tiempo</div>
                        <div class="mb-1"><span class="text-muted">Tiempo estimado (h):</span>
                            {{ number_format($tarea->tiempo_estimado_h ?? 0, 2) }}</div>
                        <div class="mb-1"><span class="text-muted">Horas registradas (h):</span>
                            {{ number_format($horasReales, 2) }}</div>
                    </div>
                </div>

                <hr>

                {{-- Cronograma --}}
                <div class="section-title fw-bold mb-2" style="color:#003B7B;">Cronograma</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-1">
                            <span class="text-muted">Fecha de creación:</span>
                            {{ dtz($tarea->created_at, 'd/m/Y H:i') }}

                        </div>
                        <div class="mb-1">
                            <span class="text-muted">Finalizada:</span>
                            {{ dtz($tarea->finalizada_at, 'd/m/Y H:i') ?? '—' }}

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-1">
                            <span class="text-muted">Fecha de entrega:</span>
                            {{ dtz($tarea->fecha_de_entrega, 'd/m/Y H:i') ?? '—' }}

                        </div>
                        <div class="mb-1">
                            <span class="text-muted">Motivo finalización:</span>
                            {{ $tarea->motivo_finalizacion ?? '—' }}
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Descripción (HTML de Quill ya sanitizado al guardar) --}}
                <div class="section-title fw-bold mb-2" style="color:#003B7B;">Descripción</div>
                <div class="quill-content">
                    {!! $tarea->descripcion !!}
                </div>
            </div>

            <div class="modal-footer">
                @if($tarea->columna && $tarea->columna->tablero)
                                <a class="btn btn-outline-primary" href="{{ route('tareas.createInColumn', [
                        'cliente' => optional($tarea->columna->tablero->cliente)->id ?? '',
                        'servicio' => $tarea->columna->tablero->servicio_id ?? '',
                        'tablero' => $tarea->columna->tablero->id ?? '',
                        'columna' => $tarea->columna->id
                    ]) }}">
                                    Crear nueva tarea en esta columna
                                </a>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>