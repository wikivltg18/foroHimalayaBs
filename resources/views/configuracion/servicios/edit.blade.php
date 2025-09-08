<x-app-layout>
  {{-- Asegúrate de tener en tu layout base:
  <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
  <x-slot name="titulo">Editar configuración de servicio</x-slot>

  <x-slot name="slot">
    <div id="ctx" data-cliente="{{ $cliente->id }}" data-servicio="{{ $servicio->id }}"
      data-modalidad-inicial="{{ $modalidadDelServicioId }}" data-tipo-inicial="{{ $selectedTipoId }}">
    </div>

    <div class="row">
      {{-- Columna izquierda: Formulario --}}
      <div class="col-md-7">
        <form id="form-servicio" action="{{ route('config.servicios.update', [$cliente->id, $servicio->id]) }}"
          method="POST" class="mt-3">
          @csrf
          @method('PUT')

          {{-- Nombre --}}
          <div class="mb-3">
            <label for="nombre_servicio" class="form-label"
              style="font-size: 1.2rem; font-weight: 700; color: #003B7B;">
              Nombre del servicio<span class="text-danger">*</span>
            </label>
            <input type="text" id="nombre_servicio" name="nombre_servicio" class="form-control"
              value="{{ old('nombre_servicio', $servicio->nombre_servicio ?? '') }}" required>
          </div>

          {{-- Mapa de horas (opcional) --}}
          <div class="mb-4">
            <h4>Mapa de horas contratadas por área</h4>
            @php
              $mapa = $servicio->mapa;
              $filas = $mapa?->mapaAreas->keyBy('area_id') ?? collect();
            @endphp

            <div class="row g-2">
              @foreach($areasCatalog as $area)
                @php $valor = optional($filas->get($area->id))->horas_contratadas ?? 0; @endphp
                <div class="col-6">
                  <div class="input-group">
                    <span class="input-group-text w-75">{{ $area->nombre }}</span>
                    <input type="number" step="0.5" min="0" class="form-control" name="mapa[{{ $area->id }}]"
                      value="{{ old('mapa.' . $area->id, (float) $valor) }}" placeholder="Horas">
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Modalidad (radios) --}}
          <div class="mb-3">
            <label class="form-label d-block mb-2"
              style="font-size: 1.2rem; font-weight: 700; color: #003B7B;">Modalidad del servicio:<span
                class="text-danger">*</span></label>
            <div id="modalidades-container" class="d-flex gap-3 flex-wrap">
              @foreach($modalidades as $m)
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="modalidad_id" id="modalidad-{{ $m->id }}"
                    value="{{ $m->id }}" {{ (string) $m->id === (string) $modalidadDelServicioId ? 'checked' : '' }}>
                  <label class="form-check-label" for="modalidad-{{ $m->id }}">{{ $m->nombre }}</label>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Tipo (select dependiente) --}}
          <div class="mb-4">
            <label class="form-label" for="tipo_servicio"
              style="font-size: 1.2rem; font-weight: 700; color: #003B7B;">Tipo de servicio:<span
                class="text-danger">*</span></label>
            <select class="form-select" id="tipo_servicio" name="tipo_servicio_id" required>
              <option value="">Seleccione un tipo de servicio</option>
              @foreach($tipos as $t)
                <option value="{{ $t->id }}" @selected((string) $t->id === (string) $selectedTipoId)>
                  {{ $t->nombre }}
                </option>
              @endforeach
            </select>
            <small class="text-muted">Los tipos listados corresponden a la modalidad seleccionada.</small>
          </div>

          {{-- Fases del servicio --}}
          <div class="mb-4">
            <h5 class="mb-2" style="font-size: 1.2rem; font-weight: 700; color: #003B7B;">Fases del tipo de
              servicio:<span class="text-danger">*</span>
            </h5>
            <p id="preview-tipo-titulo" class="mb-1 text-muted">
              Selecciona un tipo para ver sus fases.
            </p>

            {{-- Formulario para agregar fase --}}
            <div class="mb-3">
              <div class="input-group">
                <input type="text" id="nueva-fase-nombre" class="form-control" placeholder="Nombre de la nueva fase">
                <input type="text" id="nueva-fase-descripcion" class="form-control"
                  placeholder="Descripción (opcional)">
                <button type="button" id="agregar-fase" class="btn btn-primary">
                  <i class="fas fa-plus"></i> Agregar
                </button>
              </div>
            </div>

            <div id="preview-fases" class="d-flex flex-column gap-2">
              <div class="sortable-list">
                <!-- Las fases se agregarán aquí -->
              </div>
            </div>
          </div>

          <!-- Template para las fases -->
          <template id="fase-template">
            <div class="badge p-2 rounded sortable-item d-flex align-items-center"
              style="color:#003B7B; background-color:#DDF7FF; cursor: move; user-select: none; margin-bottom: 5px;"
              data-fase-id="">
              <i class="fas fa-grip-vertical me-2 handle" style="cursor: grab;"></i>
              <span class="fase-nombre flex-grow-1"></span>
              <i class="fas fa-times ms-2 delete-fase" style="cursor: pointer;"></i>
            </div>
          </template>

          <style>
            .sortable-ghost {
              opacity: 0.4;
              background-color: #c8e9ff !important;
            }

            .sortable-chosen {
              background-color: #b3e0ff !important;
            }

            .sortable-drag {
              cursor: grabbing !important;
            }
          </style>

          {{-- Botones --}}
          <div class="d-flex gap-2 mb-4">
            <a href="{{ route('config.servicios.index', $cliente->id) }}" class="btn btn-light">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>

        </form>
      </div>

      {{-- Columna derecha: Imagen --}}
      <div class="col-md-5 d-flex align-items-center justify-content-center" style="background-color:#003B7B;">
        <img src="{{ asset('img/Logo_Himalaya_blanco-10.png') }}" alt="Logo Himalaya" class="img-fluid"
          style="max-width: 90%; height: auto;">
      </div>
    </div>
  </x-slot>

  {{-- Font Awesome --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script>
      $(function () {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
        const $modalidades = $('#modalidades-container'); const $tipo = $('#tipo_servicio'); const $fases = $('#preview-fases');
        const $tituloPreview = $('#preview-tipo-titulo'); const ctx = (() => {
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
          fasesPorTipo: @json(route('config.servicios.ajax.fases', ['tipo' => 'TIP_PLACE']))
        };

        const toList = (res) => Array.isArray(res) ? res : (res?.tipos ?? res?.fases ?? res?.data ?? []);

        function setTipos(items, selected) {
          $tipo.html('<option value="">Seleccione un tipo de servicio</option>');
          items.forEach(t => {
            const sel = String(t.id) === String(selected) ? 'selected' : '';
            $tipo.append(`<option value="${t.id}" ${sel}>${t.nombre}</option>`);
          });
        }

        let sortable = null;

        function initSortable() {
          if (sortable) {
            sortable.destroy();
          }

          const container = $fases.find('.sortable-list')[0];
          if (!container) return;

          sortable = new Sortable(container, {
            animation: 150,
            handle: '.handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            forceFallback: false,
            fallbackClass: 'sortable-fallback',
            onEnd: function (evt) {
              obtenerFases();
            }
          });
        }

        function renderFases(items, titulo = null) {
          const $container = $fases.find('.sortable-list');
          $container.empty();

          if (titulo) $tituloPreview.html(`<strong>${titulo}</strong>`);

          if (!items.length) {
            $container.html('<span class="text-muted">Este tipo no tiene fases de plantilla.</span>');
            return;
          }

          const template = document.getElementById('fase-template');

          items.forEach(f => {
            const clone = document.importNode(template.content, true);
            const faseElement = clone.querySelector('.sortable-item');

            // Para fases nuevas o personalizadas, usar null explícitamente
            faseElement.dataset.faseId = f.fase_servicio_id !== null ? f.fase_servicio_id : 'null';
            faseElement.dataset.nombre = f.nombre;
            faseElement.dataset.descripcion = f.descripcion || '';
            faseElement.querySelector('.fase-nombre').textContent = f.nombre;

            $container.append(faseElement);
          });

          // Inicializar Sortable después de agregar las fases
          if (items.length > 0) {
            initSortable();
          }
        }

        function obtenerFases() {
          let fases = [];

          // Obtener todas las fases en el orden actual
          $('#preview-fases .sortable-list .sortable-item').each(function (index) {
            const $fase = $(this);
            const faseId = $fase.data('fase-id');

            // Convertir el fase_servicio_id correctamente
            let fase_servicio_id;
            if (faseId === 'null' || faseId === '') {
              fase_servicio_id = null;
            } else {
              fase_servicio_id = parseInt(faseId) || null;
            }

            fases.push({
              fase_servicio_id: fase_servicio_id,
              nombre: $fase.data('nombre'),
              descripcion: $fase.data('descripcion'),
              posicion: index + 1
            });
          });          // Actualizar el campo oculto
          if (!$('#fases-hidden').length) {
            $('<input>').attr({
              type: 'hidden',
              id: 'fases-hidden',
              name: 'fases'
            }).appendTo('#form-servicio');
          }
          $('#fases-hidden').val(JSON.stringify(fases));

          return fases;
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

        // Evento submit del formulario
        $('#form-servicio').on('submit', function (e) {
          e.preventDefault();
          const fases = obtenerFases();

          if (fases.length === 0) {
            alert('Debe seleccionar al menos un tipo de servicio con fases.');
            return false;
          }

          this.submit();
        });

        // Función para agregar una nueva fase
        function agregarNuevaFase() {
          const nombre = $('#nueva-fase-nombre').val().trim();
          const descripcion = $('#nueva-fase-descripcion').val().trim();

          if (!nombre) {
            alert('El nombre de la fase es obligatorio');
            return;
          }

          // Obtener las fases actuales
          const fasesActuales = obtenerFases();

          // Agregar la nueva fase
          fasesActuales.push({
            fase_servicio_id: null,
            nombre: nombre,
            descripcion: descripcion || null,
            posicion: fasesActuales.length + 1
          });

          renderFases(fasesActuales);

          // Actualizar el campo oculto con las fases actualizadas
          $('#fases-hidden').val(JSON.stringify(fasesActuales));

          // Limpiar campos
          $('#nueva-fase-nombre').val('');
          $('#nueva-fase-descripcion').val('');
        }        // Eventos
        $(document).on('change', 'input[name="modalidad_id"]', function () {
          const modalidadId = $(this).val();
          cargarTiposPorModalidad(modalidadId, '');
        });

        $tipo.on('change', function () {
          const sel = $(this).find('option:selected');
          const tipoId = sel.val();
          const nombre = sel.text();
          cargarFasesPorTipo(tipoId, nombre);
        });

        // Evento para agregar nueva fase
        $('#agregar-fase').on('click', agregarNuevaFase);
        $('#nueva-fase-nombre, #nueva-fase-descripcion').on('keypress', function (e) {
          if (e.which === 13) { // Enter key
            e.preventDefault();
            agregarNuevaFase();
          }
        });

        // Evento para eliminar fase
        $(document).on('click', '.delete-fase', function (e) {
          e.preventDefault();
          e.stopPropagation();

          if (confirm('¿Estás seguro de que deseas eliminar esta fase?')) {
            $(this).closest('.sortable-item').remove();
            obtenerFases(); // Actualizar el campo oculto
          }
        });

        // ===== Carga inicial: mostrar fases existentes y luego las del tipo actual =====
        // Cargar fases existentes del servicio si las hay
        @if($servicio->fases->count() > 0)
          const fasesExistentes = @json($servicio->fases);
          renderFases(fasesExistentes.map(f => ({
            fase_servicio_id: f.fase_servicio_id,
            nombre: f.nombre,
            descripcion: f.descripcion,
            posicion: f.posicion
          })));
          obtenerFases(); // Para inicializar el campo oculto
        @else
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
        @endif
                      });
    </script>
  @endpush
</x-app-layout>