<x-app-layout>
  <x-slot name="buttonPress">
    <a href="{{ route('config.servicios.create', $cliente->id) }}" class="btn btn-primary me-2">Crear configuración</a>
    <a href="{{ route('clientes.index') }}" class="btn btn-primary me-2">Volver</a>
  </x-slot>

  <x-slot name="titulo">
    Configuración de servicios
  </x-slot>

  <x-slot name="slot">
    <div class="row" style="font-size: smaller">

      <div class="col-md-12 m-md-1">
        <h6 class="text-white p-2 rounded" style="background-color:#003B7B">
          <strong>{{ $cliente->nombre }}</strong>
        </h6>
      </div>
      @forelse($servicios as $servicio)
        <div class="col-md-4 mb-3">
          <div class="card shadow border-0 h-100" data-bs-toggle="modal"
            data-bs-target="#modalServicio{{ $servicio->id }}" style="cursor: pointer;">
            <div class="card-body p-0">
              <div class="d-flex">
                <div class="d-flex flex-row justify-content-between align-items-center p-1 w-100 rounded"
                  style="color:white; background-color: #25AFDB;">
                  <h5 class="card-title mb-2 fw-bold px-3">
                    {{ $servicio->nombre_servicio ?? $servicio->nombre_del_servicio }}
                  </h5>
                  <i class="fa-solid fa-gear px-2" style="color:white; font-size: 20px;"></i>
                </div>
              </div>

              <div class="d-flex flex-column gap-1 p-3">
                <p class="card-text text-muted mb-0 fw-bold">Modalidad:
                  <span class="fw-lighter">{{ $servicio->modalidad->nombre ?? '—' }}</span>
                </p>
                <p class="card-text text-muted mb-0 fw-bold">Tipo de servicio: <span
                    class="fw-lighter">{{ $servicio->tipo->nombre ?? '—' }}</span></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal para {{ $servicio->nombre_servicio ?? $servicio->nombre_del_servicio }} -->
        <div class="modal fade" id="modalServicio{{ $servicio->id }}" tabindex="-1"
          aria-labelledby="modalLabel{{ $servicio->id }}" aria-hidden="true">
          <div class="modal-dialog ">
            <div class="modal-content">
              <div class="modal-header" style="background-color:#003B7B; color: white;">
                <h5 class="modal-title" id="modalLabel{{ $servicio->id }}">Detalles del Servicio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                  aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <!-- Información del Servicio -->
                <div class="row mb-1">
                  <div class="col-6">
                    <h6 class="text-muted"><strong style="color:#003B7B;">Cliente</strong></h6>
                    <p>{{ $cliente->nombre }}</p>
                  </div>
                  <div class="col-6">
                    <h6 class="text-muted"><strong style="color:#003B7B;">Servicio</strong></h6>
                    <p>{{ $servicio->nombre_servicio ?? $servicio->nombre_del_servicio }}</p>
                  </div>
                </div>

                <!-- Mapa del Cliente -->
                <div class="mb-1">
                  <h6 class="text-muted"><strong style="color:#003B7B;">Mapa del cliente</strong></h6>
                  <div class="row">
                    @php
                      $mapa = $servicio->mapa;
                      $areas = $mapa?->mapaAreas ?? collect();
                    @endphp

                    @forelse($areas as $fila)
                      <div class="col-4 mb-2">
                        <div class="rounded p-2">
                          <h6 style="font-size:14px !important; font-weight: 600;">{{ $fila->area->nombre ?? 'Área' }}:</h6>
                          <span>{{ (float) $fila->horas_contratadas }} horas</span>
                        </div>
                      </div>
                    @empty
                      <div class="col-12">
                        <div class="alert alert-light">Sin horas configuradas. <a
                            href="{{ route('config.servicios.mapa.show', [$cliente->id, $servicio->id]) }}">Configurar
                            mapa</a>
                        </div>
                      </div>
                    @endforelse
                  </div>
                </div>

                <!-- Detalles Adicionales -->
                <div class="row mb-1">
                  <div class="col-6">
                    <h6 class="text-muted"><strong style="color:#003B7B;">Modalidad</strong></h6>
                    <p>{{ $servicio->modalidad->nombre ?? '—' }}</p>
                  </div>
                  <div class="col-6">
                    <h6 class="text-muted"><strong style="color:#003B7B;">Tipo de servicio</strong></h6>
                    <p>{{ $servicio->tipo->nombre ?? '—' }}</p>
                  </div>
                </div>

                <!-- Fases de Servicio -->
                <div>
                  <h6 class="text-muted mb-2"><strong style="color:#003B7B;">Fases de servicio</strong></h6>
                  <div class="d-flex flex-wrap gap-1">
                    @forelse($servicio->fases as $fase)
                      <span class="px-3 py-2 rounded-pill" style="color:#003B7B; background-color:#C6F2FF;">
                        {{ $fase->nombre }}
                      </span>
                    @empty
                      <div class="text-muted">Sin fases. <a href="{{ route('herramientas.index') }}">Gestionar fases</a>
                      </div>
                    @endforelse
                  </div>
                </div>
              </div>
              <div class="modal-footer d-flex justify-content-between">
                <a href="{{ route('config.servicios.edit', [$cliente->id, $servicio->id]) }}"
                  class="btn btn-primary">Editar</a>
                <form method="POST" action="{{ route('config.servicios.destroy', [$cliente->id, $servicio->id]) }}"
                  class="form-eliminar d-inline">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger">Eliminar</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="alert alert-info">Este cliente aún no tiene configuración de servicios relacionadas. <a
              href="{{ route('config.servicios.create', $cliente->id) }}">Crear configuración</a></div>
        </div>
      @endforelse
    </div>
  </x-slot>

  <style>
    .cursor-pointer {
      cursor: pointer;
    }

    .cursor-pointer:hover {
      transform: translateY(-2px);
      transition: transform 0.2s ease-in-out;
    }
  </style>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  @section('alert')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        // Manejar formularios de eliminación
        const forms = document.querySelectorAll('.form-eliminar');

        forms.forEach(form => {
          form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const result = await Swal.fire({
              title: '¿Estás seguro?',
              text: "Esta acción eliminará el servicio y toda su configuración.",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#d33',
              cancelButtonColor: '#3085d6',
              confirmButtonText: 'Sí, eliminar',
              cancelButtonText: 'Cancelar',
              reverseButtons: true
            });

            if (result.isConfirmed) {
              // Mostrar indicador de carga
              Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                  Swal.showLoading();
                }
              });

              // Enviar el formulario
              form.submit();
            }
          });
        });

        // Mostrar alerta de éxito si hay mensaje en la sesión
        @if(session('success'))
          Swal.fire({
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonText: 'Ok'
          });
        @endif
                                                                                                                                                                                                                  });
    </script>
  @endsection
</x-app-layout>