<x-app-layout>

    <!-- Título principal -->
    <x-slot name="titulo">
        Fases de planeación de servicios
    </x-slot>

<x-slot name="slot">
    <div class="row">
        <!-- Sección izquierda: Modalidad y tipo de servicio -->
        <div class="col-md-6">
        <h4>Registrar tipo de servicio</h4>

        <!-- Modalidades sin form, se llena por AJAX -->
        <div class="mb-3">
            <label class="form-label d-block mb-2">Modalidad del servicio:</label>
            <div id="modalidades-container" class="d-flex gap-3 flex-wrap"></div>
        </div>

        <!-- Tipo de servicio sin form, se envía por AJAX -->
        <div class="mb-3">
            <label class="form-label" for="nombre_tipo_servicio">Nombre del tipo de servicio <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nombre_tipo_servicio" placeholder="Nombre del tipo de servicio" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="descripcion_tipo_servicio">Descripción del tipo de servicio:</label>
            <input type="text" class="form-control" id="descripcion_tipo_servicio" placeholder="Descripción del tipo de servicio">
        </div>
        <div class="mb-3">
            <button type="button" class="btn btn-primary" id="guardar_tipo_servicio">Guardar tipo de servicio</button>
        </div>
        </div>

        <!-- Sección derecha: Fase de servicio -->
        <div class="col-md-6">
        <h4>Registrar fase de servicio</h4>

        <!-- Selector de tipo de servicio -->
        <div class="mb-3">
            <label class="form-label" for="tipo_servicio">Tipo de servicio:</label>
            <select class="form-select" id="tipo_servicio" disabled>
            <option value="">Seleccione un tipo de servicio</option>
            </select>
        </div>

        <!-- Fase de servicio -->
        <div class="mb-3">
            <label class="form-label" for="nombre_fase_servicio">Nombre de la fase de servicio <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nombre_fase_servicio" placeholder="Nombre de la fase de servicio" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="descripcion_fase_servicio">Descripción de la fase de servicio:</label>
            <input type="text" class="form-control" id="descripcion_fase_servicio" placeholder="Descripción de la fase de servicio">
        </div>
        <div class="mb-3">
            <button type="button" class="btn btn-primary" id="guardar_fase_servicio">Guardar fase de servicio</button>
        </div>
        </div>
    </div>

    <!-- Tabla de fases registradas con estilos aplicados -->
    <div class="table-responsive mt-4">
    <h4>Listado de fases de servicio</h4>
    <table id="data-table-fases" class="table table-striped table-hover table-bordered text-nowrap">
        <thead class="text-center">
        <tr>
            <th>Modalidad del servicio</th>
            <th>Tipo del servicio</th>
            <th>Fase del servicio</th>
            <th style="width:140px;">Acciones</th>
        </tr>
        </thead>
        <tbody id="fases_servicio_list" class="text-center">
        <!-- Se llenará dinámicamente -->
        </tbody>
    </table>
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
        $.ajaxSetup({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
        });

        // ====== Helpers ======
        function asList(res) {
            // Soporta respuestas [{...}] o {data:[...]}
            if (Array.isArray(res)) return res;
            if (res && Array.isArray(res.data)) return res.data;
            return [];
        }

        function getModalidadSeleccionada() {
            return $('input[name="modalidad"]:checked').val() || null;
        }

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

        let currentPage = 1;

        function cargarTablaFases(page = 1) {
        $.get('/fases-servicio', { page })
            .done(function(res){
            const rows = res.data || [];
            currentPage = res.current_page || 1;

            const tbody = $('#fases_servicio_list');
            tbody.empty();
            rows.forEach(row => {
                tbody.append(`
                <tr data-id="${row.id}">
                    <td>${row.modalidad ?? ''}</td>
                    <td>${row.tipo ?? ''}</td>
                    <td>${row.fase ?? ''}</td>
                    <td style="width:140px">
                    <div class="btn-g">
                        <button class="btn btn-warning btn-editar me-1"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                        </svg></button>
                        <button class="btn btn-danger btn-eliminar ms-1"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                        <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                        </svg></button>
                    </div>
                    </td>
                </tr>
                `);
            });
            if (!rows.length) {
                tbody.append('<tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>');
            }

            renderPagination(res.current_page, res.last_page);
            })
            .fail(function(){
            $('#fases_servicio_list').html('<tr><td colspan="4" class="text-danger text-center">Error al cargar fases</td></tr>');
            $('#fases_pagination').empty();
            });
        }

        function renderPagination(current, last) {
            const $p = $('#fases_pagination');
            $p.empty();

            if (!last || last <= 1) return;

            const prevDisabled = current === 1 ? ' disabled' : '';
            $p.append(`<li class="page-item${prevDisabled}">
                <a class="page-link" href="#" data-page="${current - 1}">Anterior</a>
            </li>`);

            const start = Math.max(1, current - 2);
            const end   = Math.min(last, current + 2);
            for (let i = start; i <= end; i++) {
                const active = i === current ? ' active' : '';
                $p.append(`<li class="page-item${active}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`);
            }

            const nextDisabled = current === last ? ' disabled' : '';
            $p.append(`<li class="page-item${nextDisabled}">
                <a class="page-link" href="#" data-page="${current + 1}">Siguiente</a>
            </li>`);
        }

        // Click en la paginación
        $(document).on('click', '#fases_pagination .page-link', function(e){
            e.preventDefault();
            const page = Number($(this).data('page'));
            if (!page || page < 1) return;
            cargarTablaFases(page);
        });

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
            
            if (!modalidad_id || !nombre) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Selecciona una modalidad e ingresa el nombre del tipo de servicio.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }


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

            if (!tipo_servicio_id) { 
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Campo requerido', 
                    text: 'Selecciona un tipo de servicio.', 
                    confirmButtonText: 'Entendido' 
                });
                return; 
            }
            if (!nombre) { 
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Campo requerido', 
                    text: 'Ingresa el nombre de la fase.', 
                    confirmButtonText: 'Entendido' 
                });
                return; 
            }

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
            .fail(function(xhr){
                console.error(xhr.responseJSON || xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al crear la fase de servicio.',
                    confirmButtonText: 'Ok'
                });
            })
            .always(function(){
                btn.prop('disabled', false).text('Guardar fase de servicio');
            });
        });

        // Eliminar Fase
        $(document).on('click', '.btn-eliminar', function(){
            const id = $(this).closest('tr').data('id');
            if (Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará la fase de servicio.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).isConfirmed !== true
            ) return;

            $.ajax({ url: '/fases-servicio/' + id, type: 'DELETE' })
            .done(function(){ cargarTablaFases(); })
            .fail(function(xhr){
                console.error(xhr.responseJSON || xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al eliminar la fase.',
                    confirmButtonText: 'Ok'
                });
            });
        });

        // Editar Fase (rápido)
        $(document).on('click', '.btn-editar', function(){
            const tr = $(this).closest('tr');
            const id = tr.data('id');
            const actual = tr.find('td').eq(2).text();
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
            }).then((result) => {
                if (!result.isConfirmed) return;

                const nuevo = result.value;

                $.ajax({
                    url: '/fases-servicio/' + id,
                    type: 'PUT',
                    data: { nombre: nuevo }
                })
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

        // ====== Carga inicial ======
        $.when(cargarModalidades()).always(function(){
            cargarTablaFases();
        });
        });
</script>
@endpush

</x-app-layout>
