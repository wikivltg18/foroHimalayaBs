<x-app-layout>
  {{-- Asegúrate de tener en tu layout base: <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
  <x-slot name="titulo">Editar configuración de servicio — {{ $cliente->nombre }}</x-slot>

  <x-slot name="slot">
    <div id="ctx"
         data-cliente="{{ $cliente->id }}"
         data-servicio="{{ $servicio->id }}"
         data-modalidad-inicial="{{ $modalidadDelServicioId }}"
         data-tipo-inicial="{{ $selectedTipoId }}">
    </div>

    <div class="row">
      {{-- Columna izquierda: Formulario --}}
      <div class="col-md-7">
        <form id="form-servicio"
              action="{{ route('config.servicios.update', [$cliente->id, $servicio->id]) }}"
              method="POST"
              class="mt-3">
          @csrf
          @method('PUT')

          {{-- Nombre --}}
          <div class="mb-3">
            <label for="nombre_servicio" class="form-label">
              Nombre del servicio <span class="text-danger">*</span>
            </label>
            <input type="text"
                   id="nombre_servicio"
                   name="nombre_servicio"
                   class="form-control"
                   value="{{ old('nombre_servicio', $servicio->nombre_servicio ?? '') }}"
                   required>
          </div>

          {{-- Mapa de horas (opcional) --}}
          <div class="mb-4">
            <h4>Mapa de horas por área</h4>
            @php
              $mapa = $servicio->mapa;
              $filas = $mapa?->mapaAreas->keyBy('area_id') ?? collect();
            @endphp

            <div class="row g-2">
              @foreach($areasCatalog as $area)
                @php $valor = optional($filas->get($area->id))->horas_contratadas ?? 0; @endphp
                <div class="col-12">
                  <div class="input-group">
                    <span class="input-group-text w-50">{{ $area->nombre }}</span>
                    <input type="number"
                           step="0.5"
                           min="0"
                           class="form-control"
                           name="mapa[{{ $area->id }}]"
                           value="{{ old('mapa.'.$area->id, (float)$valor) }}"
                           placeholder="Horas">
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Configurar servicio --}}
          <h4 class="mt-4">Configurar servicio</h4>

          {{-- Modalidad (radios) --}}
          <div class="mb-3">
            <label class="form-label d-block mb-2">Modalidad del servicio:</label>
            <div id="modalidades-container" class="d-flex gap-3 flex-wrap">
              @foreach($modalidades as $m)
                <div class="form-check form-check-inline">
                  <input class="form-check-input"
                         type="radio"
                         name="modalidad_id"
                         id="modalidad-{{ $m->id }}"
                         value="{{ $m->id }}"
                         {{ (string)$m->id === (string)$modalidadDelServicioId ? 'checked' : '' }}>
                  <label class="form-check-label" for="modalidad-{{ $m->id }}">{{ $m->nombre }}</label>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Tipo (select dependiente) --}}
          <div class="mb-4">
            <label class="form-label" for="tipo_servicio">Tipo de servicio</label>
            <select class="form-select"
                    id="tipo_servicio"
                    name="tipo_servicio_id"
                    required>
              <option value="">Seleccione un tipo de servicio</option>
              @foreach($tipos as $t)
                <option value="{{ $t->id }}"
                        @selected((string)$t->id === (string)$selectedTipoId)>
                  {{ $t->nombre }}
                </option>
              @endforeach
            </select>
            <small class="text-muted">Los tipos listados corresponden a la modalidad seleccionada.</small>
          </div>

          {{-- Bloque: Tipo ACTUAL del servicio (plantilla) --}}
          <div class="mb-4">
            <h5 class="mb-2">Tipo actual del servicio</h5>
            @if($tipoActual)
              <p class="mb-1"><strong>{{ $tipoActual->nombre }}</strong></p>
              <div class="d-flex flex-column gap-1">
                @forelse($fasesTipoActual as $f)
                  <span class="badge text-white px-3 py-2 rounded-pill" style="background-color:#003B7B">
                    {{ $f->nombre }}
                  </span>
                @empty
                  <span class="text-muted">Este tipo no tiene fases de plantilla.</span>
                @endforelse
              </div>
            @else
              <p class="text-muted">Este servicio aún no tiene tipo configurado.</p>
            @endif
          </div>

          {{-- Bloque: Vista previa del TIPO SELECCIONADO (AJAX) --}}
          <div class="mb-4">
            <h5 class="mb-2">Vista previa — Fases del tipo seleccionado</h5>
            <p id="preview-tipo-titulo" class="mb-1 text-muted">
              Selecciona un tipo para ver sus fases.
            </p>
            <div id="preview-fases" class="d-flex flex-column gap-1"></div>
          </div>

          {{-- Botones --}}
          <div class="d-flex gap-2 mb-4">
            <a href="{{ route('config.servicios.index', $cliente->id) }}" class="btn btn-light">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>

        </form>
      </div>

      {{-- Columna derecha: Imagen --}}
      <div class="col-md-5 d-flex align-items-center justify-content-center" style="background-color:#003B7B;">
        <img src="{{ asset('img/Logo_Himalaya_blanco-10.png') }}" alt="Logo Himalaya" class="img-fluid" style="max-width: 90%; height: auto;">
      </div>
    </div>
  </x-slot>

  @push('scripts')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
  $(function () {
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')} });

    const $modalidades = $('#modalidades-container');
    const $tipo = $('#tipo_servicio');
    const $fases = $('#preview-fases');
    const $tituloPreview = $('#preview-tipo-titulo');

    const ctx = (() => {
      const $ctx = $('#ctx');
      return {
        clienteId: $ctx.data('cliente'),
        servicioId: $ctx.data('servicio'),
        modalidadInicial: String($ctx.data('modalidad-inicial') || ''),
        tipoInicial: String($ctx.data('tipo-inicial') || '')
      };
    })();

    const URLS = {
      tiposPorModalidad: @json(route('config.servicios.ajax.tipos', ['modalidad' => 'MOD_PLACE'])),
      fasesPorTipo:      @json(route('config.servicios.ajax.fases',  ['tipo'      => 'TIP_PLACE']))
    };

    const toList = (res) => Array.isArray(res) ? res : (res?.tipos ?? res?.fases ?? res?.data ?? []);

    function setTipos(items, selected) {
      $tipo.html('<option value="">Seleccione un tipo de servicio</option>');
      items.forEach(t => {
        const sel = String(t.id) === String(selected) ? 'selected' : '';
        $tipo.append(`<option value="${t.id}" ${sel}>${t.nombre}</option>`);
      });
    }

    function renderFases(items, titulo = null) {
      $fases.empty();
      if (titulo) $tituloPreview.html(`<strong>${titulo}</strong>`);
      if (!items.length) {
        $fases.html('<span class="text-muted">Este tipo no tiene fases de plantilla.</span>');
        return;
      }
      items.forEach(f => {
        const nombre = f.nombre ?? '(sin nombre)';
        $fases.append(`<span class="badge text-white px-3 py-2 rounded-pill" style="background-color:#0d6efd">${nombre}</span>`);
      });
    }

    function cargarFasesPorTipo(tipoId, nombreTipo = '') {
      if (!tipoId) {
        $tituloPreview.text('Selecciona un tipo para ver sus fases.');
        $fases.empty();
        return;
      }
      $tituloPreview.text('Cargando fases...');
      const url = URLS.fasesPorTipo.replace('TIP_PLACE', encodeURIComponent(tipoId));
      $.get(url)
        .done(res => {
          const list = toList(res);
          renderFases(list, nombreTipo);
        })
        .fail(() => {
          $tituloPreview.text('Error al cargar fases.');
          $fases.html('<span class="text-danger">No se pudieron cargar las fases.</span>');
        });
    }

    function cargarTiposPorModalidad(modalidadId, selectedTipoId = '') {
      $tipo.html('<option value="">Cargando tipos...</option>');
      const url = URLS.tiposPorModalidad.replace('MOD_PLACE', encodeURIComponent(modalidadId));
      $.get(url)
        .done(res => {
          const list = toList(res);
          setTipos(list, selectedTipoId);

          // Autoseleccionar primer tipo si no hay seleccionado
          let tipoId = selectedTipoId;
          let nombreTipo = '';
          if (!tipoId && list.length) {
            tipoId = list[0].id;
            nombreTipo = list[0].nombre;
            $tipo.val(String(tipoId));
          } else if (tipoId) {
            const found = list.find(x => String(x.id) === String(tipoId));
            nombreTipo = found ? found.nombre : '';
          }

          cargarFasesPorTipo(tipoId, nombreTipo);
        })
        .fail(() => {
          $tipo.html('<option value="">Error al cargar tipos</option>');
          $tituloPreview.text('—');
          $fases.html('<span class="text-danger">No se pudieron cargar los tipos.</span>');
        });
    }

    // Eventos
    $(document).on('change', 'input[name="modalidad_id"]', function() {
      const modalidadId = $(this).val();
      cargarTiposPorModalidad(modalidadId, '');
    });

    $tipo.on('change', function() {
      const sel = $(this).find('option:selected');
      const tipoId = sel.val();
      const nombre = sel.text();
      cargarFasesPorTipo(tipoId, nombre);
    });

    // ===== Carga inicial: mostrar fases del tipo actual (si existe) =====
    if (ctx.tipoInicial) {
      // El select ya viene lleno desde el servidor y con el actual seleccionado.
      const nombre = $tipo.find('option:selected').text();
      cargarFasesPorTipo(ctx.tipoInicial, nombre);
    } else if (ctx.modalidadInicial) {
      // Si no hay tipo actual, pero sí modalidad, cargamos tipos y autoseleccionamos el primero
      cargarTiposPorModalidad(ctx.modalidadInicial, '');
    } else {
      $tituloPreview.text('Selecciona modalidad y tipo para ver fases.');
    }
  });
  </script>
  @endpush
</x-app-layout>
