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
                                value="{{ old('correo_electronico') }}"
                                onkeyup="validarEmail(this)">
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
                                value="{{ old('telefono') }}" onkeyup="validarSoloNumeros(this)">
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
                                value="{{ old('sitio_web') }}"
                                onkeyup="validarURL(this)">
                                <div class="error_sitio_web"></div>
                                <small class="text-muted">Ej: https://..., http://...,</small>
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
                                value="{{ old('url_instagram') }}"
                                onkeyup="validarURL(this)">
                                <div class="error_instagram">

                                </div>
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
                                value="{{ old('url_facebook') }}"
                                onkeyup="validarURL(this)">
                                <small class="text-muted">Ej: https://facebook.com/... o https://facebook.com/... </small>
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
                                value="{{ old('url_youtube') }}"
                                onkeyup="validarURL(this)">
                                <small class="text-muted">Ej: https://youtube.com/... o https://youtube.com/... </small>
                                @error('url_youtube') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Botón --}}
                        <div class="col-md-12 mt-4">
                            <button type="submit" class="btn btn-success w-100">Guardar cliente</button>
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
            // Validar que el campo teléfono solo acepte números
            function validarSoloNumeros(input) {
                const valor = input.value;
                if(isNaN(valor) || valor.trim() === '') {
                    input.value = valor.replace(/[^0-9]/g, '');
                }
            }

            // Validar formato de email
            function validarEmail(input){
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return regex.test(input.value);
            }

            const emailInput = document.getElementById('correo_electronico');
            emailInput.addEventListener('input', function() {
                const errorDiv = document.querySelector('.error-email');
                if (!validarEmail(emailInput)) {
                    emailInput.classList.add('is-invalid');
                    if (emailInput.classList.contains('is-invalid')) {
                        if (errorDiv) {
                            errorDiv.innerHTML =  `
                                <p class="text-danger mb-1">
                                    El formato del email no es válido.
                                </p>
                            `;
                        }
                    }
                } else {
                    emailInput.classList.remove('is-invalid');
                    if (errorDiv) {
                        errorDiv.textContent = '';
                    }
                }
            });

            // Validar formato de URL
            function validarURL(input) {
                const regex = /^https?:\/\/([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/[^\s?#]*)?(\?[^\s#]*)?(#[^\s]*)?$/;
                return regex.test(input.value);
            }
            
            const urlInputSitioWeb = document.getElementById('sitio_web');
            urlInputSitioWeb.addEventListener('input', function() {
                const errorDiv = document.querySelector('.error_sitio_web');
                if (urlInputSitioWeb.value && !validarURL(urlInputSitioWeb)) {
                    urlInputSitioWeb.classList.add('is-invalid');
                    if (urlInputSitioWeb.classList.contains('is-invalid')) {
                        if (errorDiv) {
                            errorDiv.innerHTML =  `
                                <p class="text-danger mb-1">
                                    El formato de URL no es válido.
                                </p>
                            `;
                        }
                    }
                } else {
                    urlInputSitioWeb.classList.remove('is-invalid');
                    if (errorDiv) {
                        errorDiv.textContent = '';
                    }
                }
            });
            
            
            // Validar formato de URL Instagram
            function validarUrlInstagram(input) {
                const regex = /^https?:\/\/(www\.)?instagram\.com\/[a-zA-Z0-9._-]+\/?$/;
                const errorDiv = document.getElementById('error_instagram');
                if (!errorDiv) {
                    const newErrorDiv = document.createElement('div');
                    newErrorDiv.id = 'error_instagram';
                    newErrorDiv.className = 'text-warning';
                    newErrorDiv.style.display = 'none';
                    newErrorDiv.textContent = 'Formato de URL de Instagram inválido.';
                    input.parentNode.insertBefore(newErrorDiv, input.nextSibling);
                }
                return regex.test(input.value);
            }
            const urlInputInstagram = document.getElementById('url_instagram');
            urlInputInstagram.addEventListener('input', function() {
                if (urlInputInstagram.value && !validarUrlInstagram(urlInputInstagram)) {
                    urlInputInstagram.classList.add('is-invalid');
                    errorDiv.style.display = 'block';

                } else {
                    urlInputInstagram.classList.remove('is-invalid');
                    errorDiv.style.display = 'none';

                }
            });

            // Validar formato de URL Facebook
            function validarUrlFacebook(input) {
                const regex = /^https?:\/\/(www\.)?facebook\.com\/[a-zA-Z0-9._-]+\/?$/;
                return regex.test(input.value);
            }
            const urlInputFacebook = document.getElementById('url_facebook');
            urlInputFacebook.addEventListener('input', function() {
                if (urlInputFacebook.value && !validarUrlFacebook(urlInputFacebook)) {
                    urlInputFacebook.classList.add('is-invalid');
                } else {
                    urlInputFacebook.classList.remove('is-invalid');
                }
            });

            // Validar formato de URL YouTube
            function validarUrlYouTube(input) {
                const regex = /^https?:\/\/(www\.)?youtube\.com\/[a-zA-Z0-9._-]+\/?$/;
                return regex.test(input.value);
            }
            const urlInputYouTube = document.getElementById('url_youtube');
            urlInputYouTube.addEventListener('input', function() {
                if (urlInputYouTube.value && !validarUrlYouTube(urlInputYouTube)) {
                    urlInputYouTube.classList.add('is-invalid');
                } else {
                    urlInputYouTube.classList.remove('is-invalid');
                    
                }
            });
        </script>
    @endpush
</x-app-layout>
