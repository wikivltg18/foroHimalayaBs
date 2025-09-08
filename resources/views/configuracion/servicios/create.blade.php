<x-app-layout>
  <x-slot name="buttonPress">
    <a href="{{ route('config.servicios.index', $cliente->id) }}" class="btn btn-secondary">Volver</a>
  </x-slot>
  <x-slot name="titulo">Crear configuración de servicios</x-slot>
  <x-slot name="slot">
    <div id="ctx" data-cliente="{{ $cliente->id }}" data-modalidad-inicial="{{ $selectedModalidadId }}"
      data-tipo-inicial="{{ $selectedTipoId }}">
    </div>

    <div class="container">
      <div class="row">
        {{-- Columna izquierda: Formulario --}}
        <div class="col-md-6">
          <form id="form-servicio" action="{{ route('config.servicios.store', $cliente->id) }}" method="POST">
            @csrf

            <div class="mb-3">
              <div class="fw-bold" style="color: #003B7B;">Cliente:</div>
              <div>{{ $cliente->nombre }}</div>
            </div>

            {{-- Nombre del servicio --}}
            <div class="mb-3">
              <label class="form-label fw-bold" for="nombre_servicio" style="color: #003B7B;">Nombre del servicio: <span
                  class="text-danger">*</span></label>
              <input type="text" id="nombre_servicio" name="nombre_servicio" class="form-control"
                value="{{ old('nombre_servicio') }}" placeholder="Servicio 2" required>
            </div>

            {{-- Mapa del cliente --}}
            <div class="mb-2">
              <div class="fw-bold" style="color: #003B7B;">Mapa del cliente:</div>
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
              <label class="form-label fw-bold" style="color: #003B7B;">
                Modalidad del servicio: <span class="text-danger">*</span>
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
              <label class="form-label fw-bold" for="tipo_servicio" style="color: #003B7B;">Tipo de servicio: <span
                  class="text-danger">*</span></label>
              <select id="tipo_servicio" name="tipo_servicio_id" class="form-select" required>
                <option value="" disabled selected>Seleccione un tipo de servicio</option>
                @foreach($tipos as $t)
                  <option value="{{ $t->id }}" @selected((string) $t->id === (string) $selectedTipoId)>{{ $t->nombre }}
                  </option>
                @endforeach
              </select>
              <small class="text-muted">Los tipos listados corresponden a la modalidad seleccionada.</small>
            </div>

            {{-- Fases del servicio --}}
            <div class="mb-3">
              <div class="fw-bold" style="color: #003B7B;">Fases del servicio: <span class="text-danger">*</span>
              </div>



              <div id="fases-preview" class="d-flex flex-column gap-2 mb-2">
                <small class="text-muted">Las fases listadas corresponden al tipo de servicio seleccionado.</small>
                <div class="sortable-list">
                  <!-- Las fases se agregarán aquí -->
                </div>
              </div>
              {{-- Formulario para agregar fase --}}
              <div class="mb-3">
                <small class="text-muted">Fases de servicio adicionales.</small>
                <div class="input-group">
                  <input type="text" id="nueva-fase-nombre" class="form-control" placeholder="Nombre de la nueva fase">
                  <input type="text" id="nueva-fase-descripcion" class="form-control"
                    placeholder="Descripción (opcional)">
                  <button type="button" id="agregar-fase" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Agregar
                  </button>
                </div>
              </div>
            </div>

            <!-- Template para las fases -->
            <template id="fase-template">
              <div class="badge p-2 rounded sortable-item d-flex align-items-center"
                style="color:#003B7B; background-color:#DDF7FF; cursor: grab; user-select: none; margin-bottom: 5px;"
                data-fase-id="">
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
            <div class="d-flex gap-2 mt-4">
              <button type="submit" class="btn btn-success w-100">Guardar</button>
            </div>
            <!-- Campo oculto para fases -->
            <input type="hidden" name="fases" id="fases-hidden">
          </form>
        </div>

        {{-- Columna derecha: Imagen --}}
        <div class="col-md-6 d-flex align-items-center justify-content-center" style="background-color:#003B7B;">
          <img src="{{ asset('img/Logo_Himalaya_blanco-10.png') }}" alt="Logo Himalaya" class="img-fluid"
            style="max-width: 90%; height: auto;">
        </div>
      </div>
    </div>
  </x-slot>

  {{-- Font Awesome --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  {{-- SweetAlert2 --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        const $tipo = $('#tipo_servicio'); const $fPrev = $('#fases-preview'); const ctx = (() => {
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

        let sortable = null;

        function initSortable() {
          if (sortable) {
            sortable.destroy();
          }

          const container = $fPrev.find('.sortable-list')[0];
          if (!container) return;

          sortable = new Sortable(container, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            forceFallback: false,
            fallbackClass: 'sortable-fallback',
            onStart: function (evt) {
              document.body.style.cursor = 'grabbing';
            },
            onEnd: function (evt) {
              document.body.style.cursor = 'auto';
              const fases = obtenerFases();
              console.log('Nuevo orden de fases:', fases);
            }
          });
        }

        function paintFases(list) {
          const $container = $fPrev.find('.sortable-list');
          $container.empty();

          if (!list.length) {
            $container.append('<div class="text-muted">Este tipo no tiene fases configuradas.</div>');
            return;
          }

          const template = document.getElementById('fase-template');

          list.forEach(f => {
            const clone = document.importNode(template.content, true);
            const faseElement = clone.querySelector('.sortable-item');

            // Handle both cases: when loading from server (id) and when adding new phases (fase_servicio_id)
            faseElement.dataset.faseId = f.id || f.fase_servicio_id || null;
            faseElement.dataset.nombre = f.nombre;
            faseElement.dataset.descripcion = f.descripcion || '';
            faseElement.querySelector('.fase-nombre').textContent = f.nombre;

            $container.append(faseElement);
          });

          // Inicializar Sortable después de agregar las fases
          if (list.length > 0) {
            initSortable();
          }
        }

        function cargarFases(tipoId) {
          if (!tipoId) {
            paintFases([]);
            return;
          }
          const url = URLS.fasesPorTipo.replace('TIP_PLACE', encodeURIComponent(tipoId));
          $.get(url)
            .done(res => paintFases(toList(res)))
            .fail(() => {
              $fPrev.html('<div class="text-muted">No se pudieron cargar las fases.</div>');
              if (sortable) {
                sortable.destroy();
                sortable = null;
              }
            });
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

        // Función para agregar una nueva fase
        async function agregarNuevaFase() {
          const nombre = $('#nueva-fase-nombre').val().trim();
          const descripcion = $('#nueva-fase-descripcion').val().trim();

          if (!nombre) {
            await Swal.fire({
              title: 'Error',
              text: 'El nombre de la fase es obligatorio',
              icon: 'error',
              confirmButtonText: 'Ok'
            });
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

          // Renderizar todas las fases
          paintFases(fasesActuales);

          // Actualizar el campo oculto
          $('#fases-hidden').val(JSON.stringify(fasesActuales));

          // Limpiar campos
          $('#nueva-fase-nombre').val('');
          $('#nueva-fase-descripcion').val('');
        }

        // Función para recopilar las fases del preview
        function obtenerFases() {
          let fases = [];

          // Obtener todas las fases en el orden actual
          $('#fases-preview .sortable-list .sortable-item').each(function (index) {
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
          });

          // Actualizar el campo oculto
          $('#fases-hidden').val(JSON.stringify(fases));

          return fases;
        }

        // Evento al hacer click en el botón Guardar
        $('#form-servicio').on('submit', async function (e) {
          e.preventDefault();

          // Validar que haya al menos una fase
          const fases = obtenerFases();
          if (fases.length === 0) {
            await Swal.fire({
              title: 'Error',
              text: 'Debe agregar al menos una fase al servicio',
              icon: 'error',
              confirmButtonText: 'Ok'
            });
            return;
          }

          // Mostrar confirmación
          const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas guardar esta configuración de servicio?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
          });

          if (result.isConfirmed) {
            this.submit();
          }
        });

        // Eventos para agregar y eliminar fases
        $('#agregar-fase').on('click', agregarNuevaFase);

        $('#nueva-fase-nombre, #nueva-fase-descripcion').on('keypress', function (e) {
          if (e.which === 13) { // Enter key
            e.preventDefault();
            agregarNuevaFase();
          }
        });

        // Evento para eliminar fase
        $(document).on('click', '.delete-fase', async function (e) {
          e.preventDefault();
          e.stopPropagation();

          const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas eliminar esta fase?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
          });

          if (result.isConfirmed) {
            $(this).closest('.sortable-item').remove();
            obtenerFases(); // Actualizar el campo oculto

            await Swal.fire({
              title: 'Eliminado',
              text: 'La fase ha sido eliminada.',
              icon: 'success',
              timer: 1500,
              showConfirmButton: false
            });
          }
        });

        // Eventos de modalidad y tipo
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