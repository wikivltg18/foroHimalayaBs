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
          Configuración de servicios
        </h6>
      </div>
      @forelse($servicios as $servicio)
        <div class="col-md-6 mb-4">
          <div class="card shadow border-0 h-100">

            <!-- Encabezado -->
            <header class="card-header border-0 bg-white">
              <div class="row">
                <div class="col-6 d-flex flex-column">
                  <h6 class="text-muted"><strong style="color:#003B7B;">
                      Cliente
                    </strong>
                  </h6>
                  <p class="mb-2 d-inline fw-light">{{ $cliente->nombre }}</p>
                </div>
                <div class="col-6 d-flex flex-column">
                  <h6 class="text-muted"><strong style="color:#003B7B;">
                      Servicio</strong>
                  </h6>
                  <p class="mb-2 d-inline fw-light">{{ $servicio->nombre_servicio ?? $servicio->nombre_del_servicio }}</p>
                </div>
              </div>
            </header>

            <!-- Cuerpo -->
            <main class="card-body">
              <h6 class="text-secondary"><strong style="color:#003B7B;">Mapa del cliente</strong></h6>

              <div class="row mb-1">
                @php
                  $mapa = $servicio->mapa;
                  $areas = $mapa?->mapaAreas ?? collect();
                @endphp

                @forelse($areas as $fila)
                  <div class="col-4 mb-2">
                    <div class="rounded p-1">
                      <h6 style="font-size:14px !important; font-weight: 600;">{{ $fila->area->nombre ?? 'Área' }}:</h6>
                      </b> <span>{{ (float) $fila->horas_contratadas }}</span>
                    </div>
                  </div>
                @empty
                  <div class="col-12">
                    <div class="alert alert-light border">Sin horas configuradas. <a
                        href="{{ route('config.servicios.mapa.show', [$cliente->id, $servicio->id]) }}">Configurar mapa</a>
                    </div>
                  </div>
                @endforelse
              </div>

              <div class="row">
                <div class="col-6">
                  <h6 class="text-muted"><strong style="color:#003B7B;">Modalidad</strong></h6>
                  <p class="fw-semibold">{{ $servicio->modalidad->nombre ?? '—' }}</p>
                </div>
                <div class="col-6">
                  <h6 class="text-muted"><strong style="color:#003B7B;">Tipo de servicio</strong></h6>
                  <p class="fw-semibold">{{ $servicio->tipo->nombre ?? '—' }}</p>
                </div>
              </div>

              <section>
                <h6 class="text-muted mb-2"><strong style="color:#003B7B;">Fases de servicio</strong></h6>
                <div class="d-flex flex-column">
                  @forelse($servicio->fases as $fase)
                    <span class="badge px-3 py-2 rounded-pill"
                      style="color:#003B7B; background-color:#DDF7FF; margin:1px 0">
                      {{ $fase->nombre }}
                    </span>
                  @empty
                    <div class="text-muted">Sin fases. <a href="{{ route('herramientas.index') }}">Gestionar fases</a></div>
                  @endforelse
                </div>
              </section>
            </main>

            <!-- Footer -->
            <footer class="card-footer d-flex justify-content-between gap-2 bg-white border-0">
              <a href="{{ route('config.servicios.edit', [$cliente->id, $servicio->id]) }}"
                class="btn btn-primary px-5">Editar</a>
              <form method="POST" action="{{ route('config.servicios.destroy', [$cliente->id, $servicio->id]) }}"
                class="form-eliminar">
                @csrf @method('DELETE')
                <button class="btn btn-danger px-5">Eliminar</button>
              </form>
            </footer>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="alert alert-info">Este cliente aún no tiene servicios. <a
              href="{{ route('config.servicios.create', $cliente->id) }}">Crear servicio</a></div>
        </div>
      @endforelse
    </div>
  </x-slot>

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
              text: "Esta acción eliminará el servicio y toda su configuración. Esta acción no se puede deshacer.",
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