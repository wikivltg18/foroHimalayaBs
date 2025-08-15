<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('clientes.index') }}" class="btn btn-primary">Listado de clientes</a>
    </x-slot>

    <x-slot name="titulo">
        Crear cliente
    </x-slot>

    <x-slot name="slot">
        <div class="row">
            {{-- Columna izquierda: Formulario --}}
            <div class="col-md-6">
                <form action="{{ route('clientes.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        {{-- Logo cliente --}}
                        <div class="col-md-6 mb-3">
                            <label for="logo">Logo del cliente</label>
                            <input
                                type="file"
                                name="logo"
                                id="logo"
                                accept="image/*"
                                class="form-control @error('logo') form-control-warning @enderror">
                            <small class="text-muted">Imagen (JPG/PNG/WEBP), máx. 2MB.</small>
                            @error('logo') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Nombre cliente --}}
                        <div class="col-md-6 mb-3">
                            <label for="nombre">Nombre del cliente <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                required
                                class="form-control @error('nombre') form-control-warning @enderror"
                                value="{{ old('nombre') }}">
                            <small class="text-muted">Ej: Unicentro, Manitoba, Comfandi.</small>
                            @error('nombre') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6 mb-3">
                            <label for="correo_electronico">Email <span class="text-danger">*</span></label>
                            <input
                                type="email"
                                id="correo_electronico"
                                name="correo_electronico"
                                required
                                class="form-control @error('correo_electronico') form-control-warning @enderror"
                                value="{{ old('correo_electronico') }}">
                            <div class="error-email"></div>
                            <small class="text-muted">Ej: nombre@dominio.com</small>
                            @error('correo_electronico') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Teléfono --}}
                        <div class="col-md-6 mb-3">
                            <label for="telefono">Teléfono <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="telefono"
                                name="telefono"
                                inputmode="tel"
                                required
                                class="form-control @error('telefono') form-control-warning @enderror"
                                value="{{ old('telefono') }}">
                            <small class="text-muted">Teléfono directo del cliente.</small>
                            @error('telefono') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Sitio web (opcional) --}}
                        <div class="col-md-6 mb-3">
                            <label for="sitio_web">Sitio web</label>
                            <input
                                type="url"
                                id="sitio_web"
                                name="sitio_web"
                                class="form-control @error('sitio_web') form-control-warning @enderror"
                                value="{{ old('sitio_web') }}">
                            <div class="error_sitio_web"></div>
                            <small class="text-muted">Ej: https://www.sigma.com o http://www.sigma.com</small>
                            @error('sitio_web') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Estado cliente --}}
                        <div class="col-md-6 mb-3">
                            <label for="estadoCliente_id">Estado del cliente <span class="text-danger">*</span></label>
                            <select
                                id="estadoCliente_id"
                                name="estadoCliente_id"
                                required
                                class="form-control @error('estadoCliente_id') form-control-warning @enderror">
                                <option value="" {{ old('estadoCliente_id') ? '' : 'selected' }}>Seleccione un estado</option>
                                @foreach ($estadosClientes as $estadoCliente)
                                    <option
                                        value="{{ $estadoCliente->id }}"
                                        {{ old('estadoCliente_id') == $estadoCliente->id ? 'selected' : '' }}>
                                        {{ $estadoCliente->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Seleccionar estado del cliente.</small>
                            @error('estadoCliente_id') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Contratos --}}
                        <div class="col-md-12 mb-3">
                            <label>Contrato <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap">
                                @php
                                    $seleccionados = old('tiposDeContratos', []);
                                @endphp
                                @foreach ($tiposDeContratos as $tipoDeContrato)
                                    @php $cid = 'contrato_'.$tipoDeContrato->id; @endphp
                                    <div class="form-check d-flex align-items-center me-5 mb-2">
                                        <input
                                            type="checkbox"
                                            name="tiposDeContratos[]"
                                            value="{{ $tipoDeContrato->id }}"
                                            class="form-check-input"
                                            id="{{ $cid }}"
                                            {{ in_array($tipoDeContrato->id, $seleccionados) ? 'checked' : '' }}>
                                        <label class="form-check-label ps-2" for="{{ $cid }}">
                                            {{ $tipoDeContrato->nombre }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('tiposDeContratos') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Director ejecutivo --}}
                        <div class="col-md-6 mb-3">
                            <label for="usuario_id">Director ejecutivo <span class="text-danger">*</span></label>
                            <select
                                id="usuario_id"
                                name="usuario_id"
                                required
                                class="form-control @error('usuario_id') form-control-warning @enderror">
                                <option value="" {{ old('usuario_id') ? '' : 'selected' }}>Seleccione un ejecutivo</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" {{ old('usuario_id') == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Seleccionar ejecutivo asignado.</small>
                            @error('usuario_id') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Instagram --}}
                        <div class="col-md-6 mb-3">
                            <label for="url_instagram">Instagram</label>
                            <input
                                type="url"
                                id="url_instagram"
                                name="url_instagram"
                                class="form-control @error('url_instagram') form-control-warning @enderror"
                                value="{{ old('url_instagram') }}">
                                <div class="error_instagram"></div>
                                <small class="text-muted">Ej: https://www.instagram.com/sigma/</small>
                                @error('url_instagram') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Facebook --}}
                        <div class="col-md-6 mb-3">
                            <label for="url_facebook">Facebook</label>
                            <input
                                type="url"
                                id="url_facebook"
                                name="url_facebook"
                                class="form-control @error('url_facebook') form-control-warning @enderror"
                                value="{{ old('url_facebook') }}">
                                <div class="error_facebook"></div>
                                <small class="text-muted">Ej: https://www.facebook.com/sigmaclinicaoftalmologica/</small>
                                @error('url_facebook') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- YouTube --}}
                        <div class="col-md-6 mb-3">
                            <label for="url_youtube">YouTube</label>
                            <input
                                type="url"
                                id="url_youtube"
                                name="url_youtube"
                                class="form-control @error('url_youtube') form-control-warning @enderror"
                                value="{{ old('url_youtube') }}">
                                <div class="error_youtube"></div>
                                <small class="text-muted">Ej: https://www.youtube.com/@jorgea.holguinruiz7982</small>
                                @error('url_youtube') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Botón --}}
                        <div class="col-md-12 mt-4">
                            <button type="submit" id="btnGuardar" class="btn btn-success w-100" disabled>Guardar cliente</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Columna derecha: Imagen --}}
            <div class="col-md-6 p-0">
                <div class="d-flex align-items-center justify-content-center" style="background-color: #003B7B; height: 100%;">
                    <img src="{{ asset('img/Logo_Himalaya_blanco-10.png') }}" alt="logo_himalaya" class="img-fluid" style="max-width: 90%; height: auto;">
                </div>
            </div>
        </div>
    </x-slot>
    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            // -------- Utilidades de validación --------
            function limpiarNoDigitos(str) {
                return (str || '').replace(/\D+/g, '');
            }

            function validarEmailValor(valor) {
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return regex.test(valor);
            }

            function validarURLValor(valor) {
                // https://dominio.tld(/ruta)?(?query)?(#hash)?
                const regex = /^https?:\/\/([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/[^\s?#]*)?(\?[^\s#]*)?(#[^\s]*)?$/;
                return regex.test(valor);
            }

            function validarInstagramValor(valor) {
                // Permite con/sin www, con/sin slash final, con query params
                const regex = /^https?:\/\/(www\.)?instagram\.com\/[a-zA-Z0-9._-]+\/?(?:\?[^\s#]*)?$/;
                return regex.test(valor);
            }

            function validarFacebookValor(valor) {
                const regex = /^https?:\/\/(www\.)?facebook\.com\/[^\s]+\/?$/;
                return regex.test(valor);
            }

            function validarYouTubeValor(valor) {
                // Canal por channel|user|c|@handle
                const regex = /^https?:\/\/(www\.)?youtube\.com\/(?:(?:channel|user|c)\/|@)?[a-zA-Z0-9._-]+\/?$/;
                return regex.test(valor);
            }

            // -------- Elementos --------
            const btnGuardar = document.getElementById('btnGuardar');

            const inputTelefono = document.getElementById('telefono');
            const inputNombre = document.getElementById('nombre');
            const inputEmail = document.getElementById('correo_electronico');
            const inputSitio = document.getElementById('sitio_web');
            const selectEstado = document.getElementById('estadoCliente_id');
            const selectEjecutivo = document.getElementById('usuario_id');

            const inputInstagram = document.getElementById('url_instagram');
            const inputFacebook  = document.getElementById('url_facebook');
            const inputYouTube   = document.getElementById('url_youtube');

            const errEmail     = document.querySelector('.error-email');
            const errSitio     = document.querySelector('.error_sitio_web');
            const errInstagram = document.querySelector('.error_instagram'); // ¡ojo: clase, no id!
            const errFacebook  = document.querySelector('.error_facebook');
            const errYouTube   = document.querySelector('.error_youtube');

            const contratos = Array.from(document.querySelectorAll('input[name="tiposDeContratos[]"]'));

            // -------- Listeners --------
            // Teléfono: solo dígitos
            if (inputTelefono) {
                inputTelefono.addEventListener('input', () => {
                    const limpio = limpiarNoDigitos(inputTelefono.value);
                    if (inputTelefono.value !== limpio) {
                        inputTelefono.value = limpio;
                    }
                    verificarFormulario();
                });
            }

            // Email
            if (inputEmail) {
                inputEmail.addEventListener('input', () => {
                    const valido = validarEmailValor(inputEmail.value);
                    toggleInvalido(inputEmail, !valido);
                    setError(errEmail, valido ? '' : 'El formato del email no es válido.');
                    verificarFormulario();
                });
            }

            // Sitio web (opcional)
            if (inputSitio) {
                inputSitio.addEventListener('input', () => {
                    const v = inputSitio.value.trim();
                    const invalido = v && !validarURLValor(v);
                    toggleInvalido(inputSitio, invalido);
                    setError(errSitio, invalido ? 'El formato de URL no es válido.' : '');
                    verificarFormulario();
                });
            }

            // Instagram (opcional)
            if (inputInstagram) {
                inputInstagram.addEventListener('input', () => {
                    const v = inputInstagram.value.trim();
                    const invalido = v && !validarInstagramValor(v);
                    toggleInvalido(inputInstagram, invalido);
                    setError(errInstagram, invalido ? 'El formato de URL de Instagram no es válido.' : '');
                    verificarFormulario();
                });
            }

            // Facebook (opcional)
            if (inputFacebook) {
                inputFacebook.addEventListener('input', () => {
                    const v = inputFacebook.value.trim();
                    const invalido = v && !validarFacebookValor(v);
                    toggleInvalido(inputFacebook, invalido);
                    setError(errFacebook, invalido ? 'El formato de URL de Facebook no es válido.' : '');
                    verificarFormulario();
                });
            }

            // YouTube (opcional)
            if (inputYouTube) {
                inputYouTube.addEventListener('input', () => {
                    const v = inputYouTube.value.trim();
                    const invalido = v && !validarYouTubeValor(v);
                    toggleInvalido(inputYouTube, invalido);
                    setError(errYouTube, invalido ? 'El formato de URL de YouTube no es válido.' : '');
                    verificarFormulario();
                });
            }

            // Selects requeridos
            if (selectEstado)  selectEstado.addEventListener('change', verificarFormulario);
            if (selectEjecutivo) selectEjecutivo.addEventListener('change', verificarFormulario);

            // Contratos (marcar al menos 1 si quieres que sea obligatorio)
            contratos.forEach(chk => chk.addEventListener('change', verificarFormulario));

            // Nombre (requerido)
            if (inputNombre) inputNombre.addEventListener('input', verificarFormulario);

            // -------- Helpers de UI --------
            function toggleInvalido(el, invalido) {
                if (!el) return;
                el.classList.toggle('is-invalid', !!invalido);
            }

            function setError(container, msg) {
                if (!container) return;
                container.innerHTML = msg ? `<p class="text-danger mb-1">${msg}</p>` : '';
            }

            // -------- Habilitar / Deshabilitar submit --------
            function contratosValidos() {
                // Si quieres que sea opcional, devuelve true sin chequear
                return contratos.length === 0 ? true : contratos.some(c => c.checked);
            }

            function camposRequeridosValidos() {
                const requeridos = [
                    inputNombre,
                    inputEmail,
                    inputTelefono,
                    selectEstado,
                    selectEjecutivo
                ].filter(Boolean);

                // Ninguno debe estar vacío
                const vacio = requeridos.some(el =>
                    (el.tagName === 'SELECT')
                        ? (el.value === '' || el.value == null)
                        : (el.value.trim() === '')
                );

                // Ninguno debe tener is-invalid
                const invalidos = requeridos.some(el => el.classList.contains('is-invalid'));

                // Opcionales con error también bloquean
                const opcionalesConError = [inputSitio, inputInstagram, inputFacebook, inputYouTube]
                    .filter(Boolean)
                    .some(el => el.classList.contains('is-invalid'));

                return !vacio && !invalidos && !opcionalesConError;
            }

            function verificarFormulario() {
                const ok = camposRequeridosValidos() && contratosValidos();
                if (btnGuardar) btnGuardar.disabled = !ok;
            }

            // Estado inicial
            verificarFormulario();
        });
</script>
    @endpush
</x-app-layout>
