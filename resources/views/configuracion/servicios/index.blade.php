<x-app-layout>
  <x-slot name="buttonPress">
    <a href="{{ route('config.servicios.create', $cliente->id) }}" class="btn btn-primary me-2">Crear configuración de servicio</a>
  </x-slot>

  <x-slot name="titulo">
    Configuración de servicios {{ $cliente->nombre }}
  </x-slot>

  <x-slot name="slot">
    <div class="row" style="font-size: smaller">
      @forelse($servicios as $servicio)
        <div class="col-md-6 mb-4">
          <div class="card shadow border-0 h-100">

            <!-- Encabezado -->
            <header class="card-header text-white" style="background-color:#003B7B">
              <div class="row">
                <div class="col-12">
                  <h6 class="mb-1 text-center fw-semibold d-inline">
                    Cliente: <p class="mb-0 text-center d-inline fw-light">{{ $cliente->nombre }}</p>
                  </h6>
                </div>
                <div class="col-12">
                  <h6 class="mb-1 text-center fw-semibold d-inline">
                    Servicio: <p class="mb-0 text-center d-inline fw-light">{{ $servicio->nombre_servicio ?? $servicio->nombre_del_servicio }}</p>
                  </h6>
                </div>
              </div>
            </header>

            <!-- Cuerpo -->
            <main class="card-body">
              <h6 class="text-secondary"><strong>Mapa del cliente</strong></h6>

              <div class="row text-center mb-1">
                @php
                  $mapa = $servicio->mapa;
                  $areas = $mapa?->mapaAreas ?? collect();
                @endphp

                @forelse($areas as $fila)
                  <div class="col-4 mb-2">
                    <div class="rounded border p-1">
                      <strong>{{ $fila->area->nombre ?? 'Área' }}:</strong> {{ (float)$fila->horas_contratadas }}
                    </div>
                  </div>
                @empty
                  <div class="col-12">
                    <div class="alert alert-light border">Sin horas configuradas. <a href="{{ route('config.servicios.mapa.show', [$cliente->id, $servicio->id]) }}">Configurar mapa</a></div>
                  </div>
                @endforelse
              </div>

              <div class="row">
                <div class="col-6">
                  <h6 class="text-muted"><strong>Modalidad</strong></h6>
                  <p class="fw-semibold">{{ $servicio->modalidad->nombre ?? '—' }}</p>
                </div>
                <div class="col-6">
                  <h6 class="text-muted"><strong>Tipo de servicio</strong></h6>
                  <p class="fw-semibold">{{ $servicio->tipo->nombre ?? '—' }}</p>
                </div>
              </div>

              <section>
                <h6 class="text-muted mb-2"><strong>Fases de servicio</strong></h6>
                <div class="d-flex flex-column">
                  @forelse($servicio->fases as $fase)
                    <span class="badge text-white px-3 py-2 rounded-pill" style="background-color:#003B7B; margin:1px 0">
                      {{ $fase->nombre }}
                    </span>
                  @empty
                    <div class="text-muted">Sin fases. <a href="{{ route('herramientas.index') }}">Gestionar fases</a></div>
                  @endforelse
                </div>
              </section>
            </main>

            <!-- Footer -->
            <footer class="card-footer d-flex justify-content-end gap-2" style="background-color:#003B7B">
              <a href="{{ route('config.servicios.edit', [$cliente->id, $servicio->id]) }}" class="btn btn-primary">Editar</a>
              <form method="POST" action="{{ route('config.servicios.destroy', [$cliente->id, $servicio->id]) }}" onsubmit="return confirm('¿Eliminar servicio?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger">Eliminar</button>
              </form>
            </footer>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="alert alert-info">Este cliente aún no tiene servicios. <a href="{{ route('config.servicios.create', $cliente->id) }}">Crear servicio</a></div>
        </div>
      @endforelse
    </div>
  </x-slot>

  @push('alert')
  <script></script>
  @endpush
</x-app-layout>
