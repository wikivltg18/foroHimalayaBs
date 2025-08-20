<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ url('/equipo/usuarios') }}" class="btn btn-primary">Listado de usuarios</a>
    </x-slot>

    <x-slot name="titulo">
        Editar usuario
    </x-slot>
    <x-slot name="slot">
        <div class="container-fluid">
            <div class="row" style="height: auto;">
                <div class="col-md-6">
                    <form action="{{ route('equipo.usuarios.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">

                            {{-- Logo cliente --}}
                        <div class="col-md-12 mb-3">
                            <label for="logo">Imagen de perfil</label>
                            <input id="foto_perfil" type="file" name="foto_perfil" id="foto_perfil" class="form-control @error('foto_perfil') form-control-warning @enderror">
                            <div id="error-img"></div>
                            <small class="text-muted">
                                Imagen del cliente (JPG, PNG, WEBP, máx. 2MB).
                            </small>
                            @error('foto_perfil') <div class="text-warning">{{ $message }}</div> @enderror
                            
                            @if($user->foto_perfil)
                                <img src="{{ asset('storage/' . $user->foto_perfil) }}" alt="Foto de perfil" class="mt-2 d-block" style="max-width: 100px;">
                            @endif
                        </div>

                            {{-- Campo: Nombre --}}
                            <div class="col-md-6 mb-3">
                                <label for="name" class="text-black">Nombre completo:<span class="text-danger">*</span></label>
                                <input id="name" name="name" type="text" class="form-control" required value="{{ old('name', $user->name) }}">
                                <div id="error-name"></div>
                                <small class="text-muted">
                                    Ej: Carlos Stiven Viveros.
                                </small>
                                @error('name') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Campo: Email --}}
                            <div class="col-md-6 mb-3">
                                <label for="email" class="text-black">Email:<span class="text-danger">*</span></label>
                                <input id="email" name="email" type="email" class="form-control" required value="{{ old('email', $user->email) }}">
                                <div id="errEmail"></div>
                                <small class="text-muted">
                                    Ej: soporte@himalaya.digital.
                                </small>
                                @error('email') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Campo: Contraseña (solo si deseas cambiarla) --}}
                            <div class="col-md-6 mb-3">
                                <label for="password" class="text-black">Cambiar contraseña:</label>
                                <input id="password" name="password" type="password" class="form-control">
                                <div id="errorPassword"></div>
                                <small class="text-muted">
                                    Mínimo 8 caracteres.
                                </small>
                                @error('password') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Selector: Cargo --}}
                            <div class="col-md-6 mb-3">
                                <label for="id_cargo" class="text-black">Cargo:<span class="text-danger">*</span></label>
                                <select id="id_cargo" name="id_cargo" class="form-select" required>
                                    <option disabled selected>Seleccione un cargo</option>
                                    @foreach ($cargos as $cargo)
                                        <option value="{{ $cargo->id }}" {{ old('id_cargo', $user->id_cargo) == $cargo->id ? 'selected' : '' }}>
                                            {{ $cargo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    Ej: Diseñador, Desarrollador, etc.
                                </small>
                                @error('id_cargo') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Teléfono --}}
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="text-black">Teléfono:<span class="text-danger">*</span></label>
                                <input id="telefono" name="telefono" type="text" class="form-control" required value="{{ old('telefono', $user->telefono) }}">
                                <small class="text-muted">
                                    Ej: 3001234567.
                                </small>
                                @error('telefono') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>
                            {{-- Fecha de nacimiento --}}
                            <div class="col-md-6 mb-3">
                                <label for="f_nacimiento" class="text-black">Fecha de nacimiento:<span class="text-danger">*</span></label>
                                <input id="f_nacimiento" name="f_nacimiento" type="date" class="form-control" required value="{{ old('f_nacimiento', $f_nacimiento_formateada) }}">
                                <small class="text-muted">
                                    Ej: 20/05/1990.
                                </small>
                                @error('f_nacimiento') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Rol --}}
                            <div class="col-md-6 mb-3">
                                <label for="role" class="text-black">Rol:<span class="text-danger">*</span></label>
                                <select id="id_role" name="role" class="form-select" required>
                                    <option disabled selected>Seleccione un rol</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role', $user->roles->first()?->id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('role') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Área --}}
                            <div class="col-md-6 mb-3">
                                <label for="id_area" class="text-black">Área:<span class="text-danger">*</span></label>
                                <select id="id_area" name="id_area" class="form-select" required>
                                    <option disabled selected>Seleccione un área</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}" {{ old('id_area', $user->id_area) == $area->id ? 'selected' : '' }}>
                                            {{ $area->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    Ej: Diseño, Contenido.
                                </small>
                                @error('id_area') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Botón de envío --}}
                            <div class="col-md-12">
                                <input type="submit" id="btnActualizar" class="btn btn-success col-12 btn-lg" value="Actualizar usuario">
                            </div>
                        </div>
                    </form>
                </div>
                {{-- Imagen decorativa --}}
                <div class="col-md-6 d-flex p-0 align-items-end justify-content-center" style="background-color: #003B7B;">
                    <img src="{{ asset('img/cee2bd3f9f.png') }}" alt="Logo Himalaya" class="img-fluid" style="max-width: 100%; height: auto;">
                </div>
            </div>
        </div>
    </x-slot>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
            // --- Validadores ---
                function validarEmailValor(valor) {
                    const rx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return rx.test(valor || '');
                }
                function validarContraseñaValor(valor) {
                    // Min 8, una mayúscula, una minúscula, un número y un carácter especial
                    const rx = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
                    return rx.test(valor || '');
                }
                function limpiarNoDigitos(str) {
                    return (str || '').replace(/\D+/g, '');
                }
                function validarTelefono(valor) {
                    // 10 dígitos (Colombia), permite solo números
                    const v = limpiarNoDigitos(valor);
                    return /^\d{10}$/.test(v);
                }

                // --- Elementos ---
                const nombreInput   = document.getElementById('name');
                const emailInput    = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                const telefonoInput = document.getElementById('telefono');
                const fNacimiento   = document.getElementById('f_nacimiento');
                const fotoPerfil    = document.getElementById('foto_perfil');
                const idCargoSelect = document.getElementById('id_cargo');
                const idRolSelect   = document.getElementById('id_role');  // <-- coincide con el select
                const idAreaSelect  = document.getElementById('id_area');

                const errImgPerf = document.getElementById('error-img');
                const errName = document.getElementById('errName');
                const errEmail = document.getElementById('errEmail');
                const errorPassword = document.getElementById('errorPassword');

                const btnActualizar = document.getElementById('btnActualizar');

                // --- Helpers UI ---
                function toggleInvalido(el, invalido) {
                    if (!el) return;
                    el.classList.toggle('is-invalid', !!invalido);
                }
                function setError(container, ...msgs) {
                    if (!container) return;
                    const texto = msgs.filter(Boolean).join('<br>');
                    container.innerHTML = texto ? `<p class="text-danger mb-1">${texto}</p>` : '';
                }

                // --- Validadores por campo (reusables) ---
                function validarNombre() {
                    if (!nombreInput) return true;
                    const invalido = nombreInput.value.trim() === '';
                    toggleInvalido(nombreInput, invalido);
                    setError(errName, invalido ? 'El nombre no puede estar vacío.' : '');
                    return !invalido;
                }

                function validarEmail() {
                    if (!emailInput) return true;
                    const v = emailInput.value.trim();
                    const valido = v !== '' && validarEmailValor(v);
                    toggleInvalido(emailInput, !valido);
                    setError(errEmail, valido ? '' : 'El formato del email no es válido.');
                    return valido;
                }

                function validarPassword() {
                    if (!passwordInput) return true;
                    const v = passwordInput.value.trim();
                    if (v === '') {
                        // En edición, contraseña es opcional: vacía no invalida
                        toggleInvalido(passwordInput, false);
                        setError(errorPassword, '');
                        return true;
                    }
                    const valido = validarContraseñaValor(v);
                    toggleInvalido(passwordInput, !valido);
                    setError(errorPassword, valido ? '' : 'Mínimo 8 caracteres, con mayúscula, minúscula, número y carácter especial.');
                    return valido;
                }

                function validarTelefonoInput() {
                    if (!telefonoInput) return true;
                    const limpio = limpiarNoDigitos(telefonoInput.value);
                    if (telefonoInput.value !== limpio) telefonoInput.value = limpio;
                    const valido = validarTelefono(telefonoInput.value);
                    toggleInvalido(telefonoInput, !valido);
                    return valido;
                }

                function validarFecha() {
                    if (!fNacimiento) return true;
                    // No sobreescribas el valor existente. Solo limita fecha máxima (18 años)
                    const hoy = new Date();
                    const fechaLimite = new Date(hoy.getFullYear() - 18, hoy.getMonth(), hoy.getDate());
                    fNacimiento.max = fechaLimite.toISOString().split('T')[0];

                    const invalido = fNacimiento.value === '';
                    toggleInvalido(fNacimiento, invalido);
                    return !invalido;
                }

                function validarSelect(el) {
                    if (!el) return true;
                    const invalido = !el.value;
                    toggleInvalido(el, invalido);
                    return !invalido;
                }

                // --- Listeners (runtime) ---
                nombreInput   && nombreInput.addEventListener('input',  () => { validarNombre(); verificarFormulario(); });
                emailInput    && emailInput.addEventListener('input',   () => { validarEmail(); verificarFormulario(); });
                passwordInput && passwordInput.addEventListener('input',() => { validarPassword(); verificarFormulario(); });
                telefonoInput && telefonoInput.addEventListener('input',() => { validarTelefonoInput(); verificarFormulario(); });
                fNacimiento   && fNacimiento.addEventListener('input',  () => { validarFecha(); verificarFormulario(); });

                idCargoSelect && idCargoSelect.addEventListener('change', () => { validarSelect(idCargoSelect); verificarFormulario(); });
                idRolSelect   && idRolSelect.addEventListener('change',   () => { validarSelect(idRolSelect);   verificarFormulario(); });
                idAreaSelect  && idAreaSelect.addEventListener('change',  () => { validarSelect(idAreaSelect);  verificarFormulario(); });

                // --- Chequeo global ---
                function camposRequeridosValidos() {
                    const okNombre   = validarNombre();
                    const okEmail    = validarEmail();
                    const okPassword = validarPassword();     // opcional; sólo invalida si se escribió mal
                    const okTel      = validarTelefonoInput();
                    const okFecha    = validarFecha();
                    const okCargo    = validarSelect(idCargoSelect);
                    const okRol      = validarSelect(idRolSelect);
                    const okArea     = validarSelect(idAreaSelect);
                    return okNombre && okEmail && okPassword && okTel && okFecha && okCargo && okRol && okArea;
                }

                function verificarFormulario() {
                    const ok = camposRequeridosValidos();
                    if (btnActualizar) btnActualizar.disabled = !ok;
                }

                // --- VALIDACIÓN INICIAL ---
                // Aplica validación a los valores precargados *sin* esperar a que el usuario teclee.
                (function runInitialValidation() {
                    validarNombre();
                    validarEmail();
                    validarPassword();
                    validarTelefonoInput();
                    validarFecha();
                    validarSelect(idCargoSelect);
                    validarSelect(idRolSelect);
                    validarSelect(idAreaSelect);
                    verificarFormulario();
                })();
            });
        </script>
    @endpush
</x-app-layout>