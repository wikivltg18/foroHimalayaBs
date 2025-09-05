<x-app-layout>
  <x-slot name="titulo">Crear configuración de servicios</x-slot>
  <x-slot name="slot">
    <div id="ctx" data-cliente="{{ $cliente->id }}" data-modalidad-inicial="{{ $selectedModalidadId }}"
      data-tipo-inicial="{{ $selectedTipoId }}">
    </div>

    <div class="container">
      <div class="row">
        {{-- Columna izquierda: Formulario --}}
        <div class="col-md-7">
          <form id="form-servicio" action="{{ route('config.servicios.store', $cliente->id) }}" method="POST">
            @csrf

            <div class="mb-3">
              <div class="fw-bold" style="font-size: 1.2rem; font-weight: 700; color: #003B7B;">Cliente:</div>
              <div>{{ $cliente->nombre }}</div>
            </div>

            {{-- Nombre del servicio --}}
            <div class="mb-3">
              <label class="form-label" for="nombre_servicio"
                style="font-size: 1.2rem; font-weight: 700; color: #003B7B;">Nombre del servicio:</label>
              <input type="text" id="nombre_servicio" name="nombre_servicio" class="form-control"
                value="{{ old('nombre_servicio') }}" placeholder="Servicio 2" required>
            </div>

            {{-- Mapa del cliente --}}
            <div class="mb-2">
              <div class="fw-bold" style="font-size: 1.2rem; font-weight: 700; color: #003B7B;">Mapa del cliente:</div>
            </div>

            <div class="row g-3 mb-4">
              @foreach($areasCatalog as $area)
                <div class="col-md-6">
                  <label for="mapa[{{ $area->id }}]" class="form-label">{{ $area->nombre }}:</label>
                  <input type="number" step="0.5" min="0" class="form-control" name="mapa[{{ $area->id }}]"
                    value="{{ old('mapa.' . $area->id) }}" placeholder="Horas">
                </div>
              @endforeach
            </div>

            {{-- Modalidad --}}
            <div class="mb-3">
              <label class="form-label" style="font-size: 1.2rem; font-weight: 700; color: #003B7B;">
                Modalidad del servicio:
              </label>
              <div id="modalidades-container">
                @foreach($modalidades as $m)
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="modalidad_id" id="modalidad_{{ $m->id }}"
                      value="{{ $m->id }}" {{ (string) $m->id === (string) $selectedModalidadId ? 'checked' : '' }}>
                    <label class="form-check-label" for="modalidad_{{ $m->id }}">{{ $m->nombre }}</label>
                  </div>
                @endforeach
              </div>
            </div>

            {{-- Tipo de servicio --}}
            <div class="mb-4">
              <label class="form-label" for="tipo_servicio"
                style="font-size: 1.2rem; font-weight: 700; color: #003B7B;">Tipo de servicio:</label>
              <select id="tipo_servicio" name="tipo_servicio_id" class="form-select" required>
                <option value="">Seleccione un tipo</option>
                @foreach($tipos as $t)
                  <option value="{{ $t->id }}" @selected((string) $t->id === (string) $selectedTipoId)>{{ $t->nombre }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Fases del servicio --}}
            <div class="mb-3">
              <div class="fw-bold" style="font-size: 1.2rem; font-weight: 700; color: #003B7B;">Fases del servicio:
              </div>
              <div id="fases-preview" class="d-flex flex-column gap-2"></div>
            </div>

            {{-- Botones --}}
            <div class="d-flex gap-2 mt-4">
              <button type="submit" class="btn btn-primary">Guardar</button>
              <a href="{{ route('config.servicios.index', $cliente->id) }}" class="btn btn-secondary">Cerrar</a>
            </div>
          </form>
        </div>

        {{-- Columna derecha: Imagen --}}
        <div class="col-md-5 d-flex align-items-center justify-content-center" style="background-color:#003B7B;">
          <img src="{{ asset('img/Logo_Himalaya_blanco-10.png') }}" alt="Logo Himalaya" class="img-fluid"
            style="max-width: 90%; height: auto;">
        </div>
      </div>
    </div>
  </x-slot>

  @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
      $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        const $tipo = $('#tipo_servicio');
        const $fPrev = $('#fases-preview');

        const ctx = (() => {
          const $ctx = $('#ctx');
          return {
            clienteId: $ctx.data('cliente'),
            modalidadInicial: String($ctx.data('modalidad-inicial') || ''),
            tipoInicial: String($ctx.data('tipo-inicial') || '')
          };
        })();

        const URLS = {
          tiposPorModalidad: @json(route('config.servicios.ajax.tipos', ['modalidad' => 'MOD_PLACE'])),
          fasesPorTipo: @json(route('config.servicios.ajax.fases', ['tipo' => 'TIP_PLACE']))
        };

        const toList = (res) => Array.isArray(res) ? res : (res?.tipos ?? res?.fases ?? []);

        function paintFases(list) {
          $fPrev.empty();
          if (!list.length) {
            $fPrev.append('<div class="text-muted">Este tipo no tiene fases configuradas.</div>');
            return;
          }
          list.forEach(f => $fPrev.append(`<div class="badge p-2 rounded" style="color:#003B7B; background-color:#DDF7FF;">${f.nombre ?? '(sin nombre)'}</div>`));
        }

        function cargarFases(tipoId) {
          if (!tipoId) { paintFases([]); return; }
          const url = URLS.fasesPorTipo.replace('TIP_PLACE', encodeURIComponent(tipoId));
          $.get(url).done(res => paintFases(toList(res)))
            .fail(() => $fPrev.html('<div class="text-muted">No se pudieron cargar las fases.</div>'));
        }

        function setTipos(items, selected) {
          $tipo.html('<option value="">Seleccione un tipo</option>');
          items.forEach(t => $tipo.append(`<option value="${t.id}" ${String(t.id) === String(selected) ? 'selected' : ''}>${t.nombre}</option>`));
        }

        function cargarTipos(modalidadId, selectedTipoId = '') {
          if (!modalidadId) { setTipos([], ''); paintFases([]); return; }
          const url = URLS.tiposPorModalidad.replace('MOD_PLACE', encodeURIComponent(modalidadId));
          $tipo.html('<option value="">Cargando...</option>');
          $.get(url)
            .done(res => {
              const list = toList(res);
              setTipos(list, selectedTipoId);
              let tipoId = selectedTipoId;
              if (!tipoId && list.length) {
                tipoId = list[0].id;
                $tipo.val(String(tipoId));
              }
              cargarFases(tipoId);
            })
            .fail(() => {
              $tipo.html('<option value="">Error al cargar tipos</option>');
              paintFases([]);
            });
        }

        // Eventos
        $(document).on('change', 'input[name="modalidad_id"]', function () {
          cargarTipos($(this).val(), '');
        });

        $tipo.on('change', function () {
          cargarFases($(this).val());
        });

        // Carga inicial
        if (ctx.tipoInicial) {
          cargarFases(ctx.tipoInicial);
        } else if (ctx.modalidadInicial) {
          cargarTipos(ctx.modalidadInicial, '');
        }
      });
    </script>
  @endpush
</x-app-layout>