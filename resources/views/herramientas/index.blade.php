<x-app-layout>

    {{--  Título principal --}}
    <x-slot name="titulo">
        Planeación de servicios
    </x-slot>

<x-slot name="slot">
    <div class="row mb-4" style="border-bottom: 1px solid #ddd;">

        {{-- Sección izquierda: Modalidad y tipo de servicio --}}
        <div class="col-md-6" style="border-right: 1px solid #ddd;">
            <h4>Registrar tipo de servicio</h4>

            {{--  Modalidades sin form, se llena por AJAX --}}
            <div class="mb-3">
                <label class="form-label d-block mb-2">Modalidad del servicio:</label>
                <div id="modalidades-container" class="d-flex gap-3 flex-wrap"></div>
            </div>

            {{--  Tipo de servicio sin form, se envía por AJAX --}}
            <div class="mb-3">
                <label class="form-label" for="nombre_tipo_servicio">Nombre del tipo de servicio <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombre_tipo_servicio" placeholder="Nombre del tipo de servicio" required>
                <small class="text-muted">Ej: Creación de parrilla, SEO, Pauta digital.</small>
            </div>
            <div class="mb-3">
                <label class="form-label" for="descripcion_tipo_servicio">Descripción del tipo de servicio:</label>
                <input type="text" class="form-control" id="descripcion_tipo_servicio" placeholder="Descripción del tipo de servicio">
                <small class="text-muted">Ej: Optimización para motores de búsqueda.</small>
            </div>
            <div class="mb-3">
                <button type="button" class="btn btn-primary" id="guardar_tipo_servicio">Crear tipo de servicio</button>
            </div>
        </div>

        {{--  Sección derecha: Fase de servicio --}}
        <div class="col-md-6">
            <h4>Registrar fase de servicio</h4>

            {{--  Selector de tipo de servicio --}}
            <div class="mb-3">
                <label class="form-label" for="tipo_servicio">Tipo de servicio:</label>
                <select class="form-select" id="tipo_servicio" required>
                <option value="" selected>Seleccione un tipo de servicio</option>
                </select>
            </div>

            {{--  Fase de servicio --}}
            <div class="mb-3">
                <label class="form-label" for="nombre_fase_servicio">Nombre de la fase de servicio <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombre_fase_servicio" placeholder="Nombre de la fase de servicio" required>
                <small class="text-muted">Ej: Concepto creativo / tendencia, Conceptualización parrilla.</small>
            </div>
            <div class="mb-3">
                <label class="form-label" for="descripcion_fase_servicio">Descripción de la fase de servicio:</label>
                <input type="text" class="form-control" id="descripcion_fase_servicio" placeholder="Descripción de la fase de servicio">
                <small class="text-muted">Ej: Herramienta estratégica que organiza y planifica la publicación de contenidos.</small>
            </div>
            <div class="mb-3">
                <button type="button" class="btn btn-primary" id="guardar_fase_servicio">Crear fase de servicio</button>
            </div>
        </div>
    </div>

    {{--  Tabla de fases de servicio --}}
    <h4>Fases de servicio registradas</h4>
    <p class="text-muted">Aquí puedes ver, buscar y editar las fases de servicio registradas.</p>

    {{--  Contenedor de fases de servicio --}}
    {{--  Filtros de búsqueda --}}
    
    <div class="row g-2 align-items-center mb-3 justify-content-end">
        <div class="col-md-8 d-flex justify-content-end">
            <input type="text" id="buscarModalidad" class="form-control me-2" placeholder="Buscar modalidad...">
            <input type="text" id="buscarTipoDeServicio" class="form-control me-2" placeholder="Buscar tipo de servicio...">
            <input type="text" id="buscarFaseDeServicio" class="form-control me-2" placeholder="Buscar fase de servicio...">
            <button type="submit" id="btnBuscar" class="btn btn-primary me-1">Filtrar</button>
            <button type="submit" id="btnLimpiar" class="btn btn-primary">Limpiar</button>
        </div>
    </div>

    {{--  Tabla de fases registradas con estilos aplicados --}}
    <div class="table-responsive mt-1">
    <table id="data-table-fases" class="table table-striped table-hover table-bordered text-nowrap">
        <thead class="text-center">
        <tr>
            <th>Modalidad del servicio</th>
            <th>Tipo del servicio</th>
            <th style="width:140px;">Acciones Tipo del servicio</th>
            <th>Fase del servicio</th>
            <th style="width:140px;">Acciones Fase del servicio</th>
        </tr>
        </thead>
        <tbody id="fases_servicio_list" class="text-center">
        {{--  Se llenará dinámicamente --}}
        </tbody>
    </table>
        {{--  Paginación --}}
        <div class="d-flex justify-content-end">
            <nav>
                <ul class="pagination pagination-sm mb-0" id="fases_pagination"></ul>
            </nav>
        </div>
    </div>
</x-slot>
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(function () {
        // ====== CSRF ======
        // Configuración de CSRF para AJAX
        // Asegura que las peticiones POST, PUT, DELETE tengan el token CSRF
        $.ajaxSetup({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
        });

        // ====== Helpers ======
        // Convierte la respuesta a una lista
        // Soporta respuestas [{...}] o {data:[...]}
        // Devuelve un array vacío si no hay datos
        // Parámetro: res (respuesta del servidor)
        function asList(res) {
            if (Array.isArray(res)) return res;
            if (res && Array.isArray(res.data)) return res.data;
            return [];
        }

        // Obtiene la modalidad seleccionada
        // Devuelve el ID de la modalidad seleccionada
        // Si no hay ninguna seleccionada, devuelve null
        function getModalidadSeleccionada() {
            return $('input[name="modalidad"]:checked').val() || null;
        }

        // Renderiza los radios de modalidades
        // Parámetro: list (array de objetos {id, nombre})
        // Actualiza el contenedor de modalidades
        // y muestra un mensaje si no hay modalidades
        // Devuelve una lista de objetos {id, nombre}
        // Si hay error, muestra un mensaje en el contenedor
        function renderRadiosModalidades(list) {
            const cont = $('#modalidades-container');
            cont.empty();
            list.forEach((m, i) => {
            const id = 'modalidad-' + m.id;
            cont.append(`
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="modalidad" id="${id}" value="${m.id}" ${i===0?'checked':''}>
                <label class="form-check-label" for="${id}">${m.nombre}</label>
                </div>
            `);
            });
        }

        // Cargar modalidades al inicio
        // Devuelve una promesa para encadenar
        // y manejar errores
        // Actualiza el contenedor de modalidades
        // y muestra un mensaje si no hay modalidades
        // Devuelve una lista de objetos {id, nombre}
        // Si hay error, muestra un mensaje en el contenedor
        // Parámetro: none
        // Devuelve una promesa que resuelve con la lista de modalidades
        // Actualiza el contenedor de modalidades
        // y muestra un mensaje si no hay modalidades
        function cargarModalidades() {
            return $.get('/modalidades')
            .done(function(res){
                const list = asList(res);
                renderRadiosModalidades(list);
                if (list.length) {
                cargarTiposPorModalidad(list[0].id);
                } else {
                $('#tipo_servicio').html('<option value="">No hay modalidades</option>');
                }
            })
            .fail(function(){
                $('#modalidades-container').html('<div class="text-danger">Error al cargar modalidades</div>');
            });
        }
        // Cargar tipos de servicio por modalidad
        // Devuelve una promesa para encadenar
        // y manejar errores
        // Actualiza el select de tipos de servicio
        // y muestra un mensaje si no hay tipos
        // Parámetro: modalidadId (ID de la modalidad seleccionada)
        function cargarTiposPorModalidad(modalidadId) {
            const sel = $('#tipo_servicio');
            sel.html('<option value="">Cargando...</option>');
            return $.get('/tipos-servicio', { modalidad_id: modalidadId })
            .done(function(res){
                const list = asList(res);
                const opts = ['<option value="">Seleccione un tipo de servicio</option>'];
                list.forEach(t => opts.push(`<option value="${t.id}">${t.nombre}</option>`));
                if (!list.length) opts.push('<option value="">No hay tipos disponibles</option>');
                sel.html(opts.join(''));
            })
            .fail(function(){
                sel.html('<option value="">Error al cargar</option>');
            });
        }


        // ====== Paginación y filtros ======
        // Variables globales para paginación y filtros
        let currentPage = 1;
        let currentFilters = {
        buscarModalidad: '',
        buscarTipoDeServicio: '',
        buscarFaseDeServicio: ''
        };

        // Lee filtros desde la UI
        function leerFiltrosDeLaInterfaz() {
        return {
            buscarModalidad: $('#buscarModalidad').val().trim(),
            buscarTipoDeServicio: $('#buscarTipoDeServicio').val().trim(),
            buscarFaseDeServicio: $('#buscarFaseDeServicio').val().trim()
        };
        }

        // Cargar tabla con filtros y paginación
        function cargarTablaFases(page = 1) {
        const params = Object.assign({}, currentFilters, { page });

        // (Opcional) feedback de carga
        const tbody = $('#fases_servicio_list');
        tbody.html('<tr><td colspan="4" class="text-center">Cargando...</td></tr>');
        
        // Realizar la petición AJAX
        $.get('/fases-servicio', params)
            .done(function(res){
            const rows = res.data || [];
            currentPage = res.current_page || 1;
            // Limpiar tabla
            tbody.empty();
            // Si hay filas, renderizarlas
            rows.forEach(row => {
                // Renderizar cada fila
                tbody.append(`
                <tr data-id="${row.id}" data-tipo-id="${row.tipo_id}">
                    <td>${row.modalidad ?? ''}</td>
                    <td>${row.tipo ?? ''}</td>
                    <td style="width:140px">
                    <div class="btn-g">
                        <button class="btn btn-warning editarTipo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                            </svg>
                        </button>
                        <button class="btn btn-danger eliminarTipo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                                <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                            </svg>
                        </button>
                    </div>
                    </td>
                    <td>${row.fase ?? ''}</td>
                    <td style="width:140px">
                    <div class="btn-g">
                        <button class="btn btn-warning editarFase">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                            </svg>
                        </button>
                        <button class="btn btn-danger eliminarFase">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                                <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                            </svg>
                        </button>
                    </div>
                    </td>
                </tr>
                `);
            });
            // Si no hay filas, mostrar mensaje
            if (!rows.length) {
                tbody.append('<tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>');
            }
            // Renderizar paginación
            renderPagination(res.current_page, res.last_page);
            })
            // Manejo de errores
            .fail(function(){
            tbody.html('<tr><td colspan="4" class="text-danger text-center">Error al cargar fases</td></tr>');
            $('#fases_pagination').empty();
            });
        }

        // Render de paginación (sin cambios, solo asegúrate de mantener data-page)
        function renderPagination(current, last) {
        const $p = $('#fases_pagination');
        $p.empty();

        // No renderizar si no hay páginas o es la primera
        if (!last || last <= 1) return;

        // Renderizar botones de paginación
        const prevDisabled = current === 1 ? ' disabled' : '';
        $p.append(`<li class="page-item${prevDisabled}">
            <a class="page-link" href="#" data-page="${current - 1}">Anterior</a>
        </li>`);

        // Rango de páginas a mostrar
        const start = Math.max(1, current - 2);
        const end   = Math.min(last, current + 2);
        for (let i = start; i <= end; i++) {
            const active = i === current ? ' active' : '';
            $p.append(`<li class="page-item${active}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`);
        }

        // Botón "Siguiente"
        const nextDisabled = current === last ? ' disabled' : '';
        $p.append(`<li class="page-item${nextDisabled}">
            <a class="page-link" href="#" data-page="${current + 1}">Siguiente</a>
        </li>`);
        }

        // Eventos de búsqueda
        $('#btnBuscar').on('click', function(){
        currentFilters = leerFiltrosDeLaInterfaz();
        cargarTablaFases(1); // siempre vuelve a la página 1 al buscar
        });

        // Enter en cualquiera de los inputs
        $('#buscarModalidad, #buscarTipoDeServicio, #buscarFaseDeServicio').on('keyup', function(e){
        if (e.key === 'Enter') {
            $('#btnBuscar').click();
        }
        });

        // Limpiar filtros
        $('#btnLimpiar').on('click', function(){
        $('#buscarModalidad, #buscarTipoDeServicio, #buscarFaseDeServicio').val('');
        currentFilters = { buscarModalidad: '', buscarTipoDeServicio: '', buscarFaseDeServicio: '' };
        cargarTablaFases(1);
        });

        // Click en la paginación con filtros activos
        $(document).on('click', '#fases_pagination .page-link', function(e){
        e.preventDefault();
        const page = Number($(this).data('page'));
        if (!page || page < 1) return;
        cargarTablaFases(page);
        });

        // Después de crear/eliminar/editar, refresca manteniendo filtros
        // - tras crear: cargarTablaFases(1);
        // - tras eliminar: cargarTablaFases(currentPage);

        // ====== Eventos ======
        // Cambio de modalidad -> cargar tipos
        $(document).on('change', 'input[name="modalidad"]', function(){
            const modalidadId = $(this).val();
            cargarTiposPorModalidad(modalidadId);
        });

        // Guardar Tipo de Servicio
        $('#guardar_tipo_servicio').on('click', function(e){
            e.preventDefault();
            const modalidad_id = getModalidadSeleccionada();
            const nombre = $('#nombre_tipo_servicio').val().trim();
            const descripcion = $('#descripcion_tipo_servicio').val().trim();

            // Validación básica
            if (!modalidad_id || !nombre) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Selecciona una modalidad e ingresa el nombre del tipo de servicio.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // Botón de guardar
            const btn = $(this);
            btn.prop('disabled', true).text('Guardando...');
            $.post('/tipos-servicio', { modalidad_id, nombre, descripcion })
            .done(function(){
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'El tipo de servicio fue creado exitosamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#nombre_tipo_servicio').val('');
                $('#descripcion_tipo_servicio').val('');
                cargarTiposPorModalidad(modalidad_id);
            })
            // Manejo de errores
            .fail(function(xhr){
                console.error(xhr.responseJSON || xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al crear el tipo de servicio.',
                    confirmButtonText: 'Ok'
                });
            })
            .always(function(){
                btn.prop('disabled', false).text('Guardar tipo de servicio');
            });
        });

        // Guardar Fase de Servicio
        $('#guardar_fase_servicio').on('click', function(e){
            e.preventDefault();
            const tipo_servicio_id = $('#tipo_servicio').val();
            const nombre = $('#nombre_fase_servicio').val().trim();
            const descripcion = $('#descripcion_fase_servicio').val().trim();

            // Validación básica
            if (!tipo_servicio_id) { 
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Campo requerido', 
                    text: 'Selecciona un tipo de servicio.', 
                    confirmButtonText: 'Entendido' 
                });
                return; 
            }
            // Validación de nombre
            if (!nombre) { 
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Campo requerido', 
                    text: 'Ingresa el nombre de la fase.', 
                    confirmButtonText: 'Entendido' 
                });
                return; 
            }
            // Botón de guardar
            const btn = $(this);
            btn.prop('disabled', true).text('Guardando...');
            $.post('/fases-servicio', { tipo_servicio_id, nombre, descripcion })
            .done(function(){
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'La fase de servicio fue creada exitosamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#nombre_fase_servicio').val('');
                $('#descripcion_fase_servicio').val('');
                cargarTablaFases();
            })
            // Manejo de errores
            .fail(function(xhr){
                console.error(xhr.responseJSON || xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al crear la fase de servicio.',
                    confirmButtonText: 'Ok'
                });
            })
            // Siempre vuelve al estado original del botón
            .always(function(){
                btn.prop('disabled', false).text('Guardar fase de servicio');
            });
        });

// Eliminar Fase
$(document).on('click', '.eliminarFase', async function () {
    const id = $(this).closest('tr').data('id'); // ID de la fase
    const idTipoServicio = $(this).closest('tr').data('tipo-id'); // ID del tipo de servicio asociado

    // Contar las coincidencias de `tipo-id` en la tabla de fases antes de eliminar la fase
    const coincidencias = $('#data-table-fases tr[data-tipo-id="' + idTipoServicio + '"]').length;

    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción eliminará la fase de servicio.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    // Si es la última fase del tipo de servicio, eliminamos también el tipo de servicio
    if (coincidencias === 1) {
        // Eliminar el tipo de servicio junto con la fase
        $.ajax({
            url: '/tipos-servicio/' + idTipoServicio,
            type: 'DELETE',
            success: function () {
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'El tipo de servicio y la fase han sido eliminados.',
                    icon: 'success',
                    confirmButtonText: 'Ok'
                });
                cargarTablaFases();
            },
            error: function (xhr) {
                console.error(xhr.responseJSON || xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al eliminar el tipo de servicio.',
                    confirmButtonText: 'Ok'
                });
            }
        });
    } else {
        // Si hay más de una fase, solo eliminamos la fase
        $.ajax({
            url: '/fases-servicio/' + id,
            type: 'DELETE',
            success: function () {
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'La fase de servicio ha sido eliminada.',
                    icon: 'success',
                    confirmButtonText: 'Ok'
                });
                cargarTablaFases();
            },
            error: function (xhr) {
                console.error(xhr.responseJSON || xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al eliminar la fase.',
                    confirmButtonText: 'Ok'
                });
            }
        });
    }
});

        // Editar Fase
        $(document).on('click', '.editarFase', function(){
            const tr = $(this).closest('tr');
            const idFase = tr.data('id');
            const actual = tr.find('td').eq(3).text();
        Swal.fire({
                title: 'Editar fase',
                input: 'text',
                inputLabel: 'Nuevo nombre de la fase:',
                inputValue: actual,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Debes ingresar un nombre';
                    }
                }
            // Manejo de la respuesta
            }).then((result) => {
                if (!result.isConfirmed) return;

                const nuevo = result.value;
                // Validación de nombre
                $.ajax({
                    url: '/fases-servicio/' + idFase,
                    type: 'PUT',
                    data: { nombre: nuevo }
                })
                // Actualización exitosa
                .done(function () {
                    cargarTablaFases();
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: 'La fase se actualizó correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                })
                // Manejo de errores
                .fail(function (xhr) {
                    console.error(xhr.responseJSON || xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al actualizar la fase',
                    });
                });
            });
        });

    
    
        // Editar Tipo
        $(document).on('click', '.editarTipo', function(){
            const tr = $(this).closest('tr');
            const idTipo = tr.data('tipo-id');
            const actual = tr.find('td').eq(1).text();
        Swal.fire({
                title: 'Editar tipo',
                input: 'text',
                inputLabel: 'Nuevo nombre del tipo:',
                inputValue: actual,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Debes ingresar un nombre';
                    }
                }
            // Manejo de la respuesta
            }).then((result) => {
                if (!result.isConfirmed) return;

                const nuevo = result.value;
                // Validación de nombre
                $.ajax({
                    url: '/tipos-servicio/' + idTipo,
                    type: 'PUT',
                    data: { nombre: nuevo }
                })
                // Actualización exitosa
                .done(function () {
                    cargarTablaFases();
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: 'El tipo se actualizó correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                })
                // Manejo de errores
                .fail(function (xhr) {
                    console.error(xhr.responseJSON || xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al actualizar la fase',
                    });
                });
            });
        });

        // Eliminar Tipo
        $(document).on('click', '.eliminarTipo', async function () {
            const id = $(this).closest('tr').data('tipo-id'); // Asegúrate de que el 'data-tipo-id' es correcto

            // Contar las coincidencias de `tipo_id` en la tabla antes de eliminarlo
            const coincidencias = $('#data-table-fases tr[data-tipo-id="' + id + '"]').length;

            const result = await Swal.fire({
                title: '¿Estás seguro?',
                text: `Este tipo de servicio tiene ${coincidencias} fases de servicio. Esta acción eliminará todas las fases relacionadas.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) return;

            // Realizar la petición de eliminación
            $.ajax({
                url: '/tipos-servicio/' + id,
                type: 'DELETE'
            })
            .done(function () {
                cargarTablaFases(); // Refresca la tabla después de eliminar
                    Swal.fire({
                        title: '¡Éxito!',
                        text: 'Tipo de servicio eliminado',
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    });
            })
            .fail(function (xhr) {
                console.error(xhr.responseJSON || xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al eliminar el tipo.',
                    confirmButtonText: 'Ok'
                });
            });
        });

        // ====== Carga inicial ======
        $.when(cargarModalidades()).always(function(){
            cargarTablaFases();
        });
        });
</script>
@endpush

</x-app-layout>
